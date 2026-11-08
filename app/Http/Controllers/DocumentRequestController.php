<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\Household;
use App\Models\Resident;
use App\Models\ReportComplaint;
use App\Services\AuditLogService;
use App\Services\DocumentConversionService;
use App\Services\OfficialDocumentTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class DocumentRequestController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Official Barangay Document Services
    |--------------------------------------------------------------------------
    */

    public static function documentTypes(): array
    {
        return [
            'Barangay Clearance',
            'Certificate of Residency',
            'Certificate of Indigency',
            'Certificate of Late Register',
            'Cutting Trees',
            'First Time Job Seeker',
            'Oath of Undertaking',
            'Solo Parent',
        ];
    }

    public function index(Request $request): View
    {
        $statusMap = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'approved' => 'Approved',
            'completed' => 'Completed',
            'released' => ['Released', 'Completed'],
            'disapproved' => 'Disapproved',
        ];

        $documents = DocumentRequest::with('resident')
            ->when(
                $request->filled('search'),
                function ($query) use ($request) {
                    $search = $request->search;

                    $query->where(function ($documentQuery) use ($search) {
                        $documentQuery
                            ->where('request_code', 'like', "%{$search}%")
                            ->orWhere('document_type', 'like', "%{$search}%")
                            ->orWhereHas(
                                'resident',
                                function ($residentQuery) use ($search) {
                                    $residentQuery
                                        ->where('first_name', 'like', "%{$search}%")
                                        ->orWhere('middle_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%");
                                }
                            );
                    });
                }
            )
            ->when(
                $request->filled('status') &&
                isset($statusMap[$request->status]),
                function ($query) use ($request, $statusMap) {
                    $query->whereIn(
                        'status',
                        (array) $statusMap[$request->status]
                    );
                }
            )
            ->latest('requested_at')
            ->get();

        $rawCounts = DocumentRequest::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusCounts = [
            'all' => (int) $rawCounts->sum(),
            'pending' => (int) $rawCounts->get('Pending', 0),
            'approved' => (int) $rawCounts->get('Approved', 0),
            'released' => (int) $rawCounts->get('Released', 0)
                + (int) $rawCounts->get('Completed', 0),
            'disapproved' => (int) $rawCounts->get('Disapproved', 0),
        ];

        return view(
            'documents.index',
            compact('documents', 'statusCounts')
        );
    }

    public function create(): View
    {
        $residents = Resident::query()
            ->where('status', 'Active')
            ->with([
                'householdMemberships.resident',
                'headedHouseholds.members.resident',
            ])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Active Respondent Cases
        |--------------------------------------------------------------------------
        |
        | Only subject_resident_id is checked.
        | Complainants are not restricted merely for filing a complaint.
        | Archived unresolved cases remain restrictive.
        |
        */

        $activeRespondentCasesByResident = ReportComplaint::query()
            ->whereIn(
                'subject_resident_id',
                $residents->pluck('id')
            )
            ->whereNotIn('status', [
                'Resolved',
                'Closed',
                'Disapproved',
            ])
            ->orderByDesc('created_at')
            ->get([
                'id',
                'subject_resident_id',
                'record_code',
                'title',
                'description',
                'status',
                'created_at',
            ])
            ->groupBy('subject_resident_id')
            ->map(function ($cases) {
                return $cases->map(function ($case) {
                    return [
                        'case_number' => $case->record_code,
                        'title' => $case->title,
                        'description' => $case->description,
                        'status' => $case->status,
                        'date_filed' => $case->created_at
                            ? $case->created_at->format('F j, Y')
                            : null,
                    ];
                })->values()->all();
            })
            ->all();

        $soloParentHouseholdMembers = [];

        foreach ($residents as $resident) {
            $members = collect();

            foreach ($resident->householdMemberships as $membership) {
                $child = $membership->resident;

                if (
                    ! $child ||
                    $child->status !== 'Active' ||
                    $child->id === $resident->id
                ) {
                    continue;
                }

                $members->push([
                    'resident_id' => $child->id,
                    'full_name' => $child->full_name,
                    'sex' => $child->sex,
                    'birth_date' => $child->birth_date
                        ? $child->birth_date->format('Y-m-d')
                        : null,
                    'birth_date_display' => $child->birth_date
                        ? $child->birth_date->format('F j, Y')
                        : null,
                    'relationship' => $membership->relationship,
                ]);
            }

            foreach ($resident->headedHouseholds as $household) {
                $householdMembers = $household->members;

                foreach ($householdMembers as $membership) {
                    $child = $membership->resident;

                    if (
                        ! $child ||
                        $child->status !== 'Active' ||
                        $child->id === $resident->id
                    ) {
                        continue;
                    }

                    $members->push([
                        'resident_id' => $child->id,
                        'full_name' => $child->full_name,
                        'sex' => $child->sex,
                        'birth_date' => $child->birth_date
                            ? $child->birth_date->format('Y-m-d')
                            : null,
                        'birth_date_display' => $child->birth_date
                            ? $child->birth_date->format('F j, Y')
                            : null,
                        'relationship' => $membership->relationship,
                    ]);
                }
            }

            $soloParentHouseholdMembers[$resident->id] = $members
                ->unique('resident_id')
                ->values()
                ->all();
        }

        $documentTypes = self::documentTypes();

        return view(
            'documents.create',
            compact(
                'residents',
                'documentTypes',
                'soloParentHouseholdMembers',
                'activeRespondentCasesByResident'
            )
        );
    }

    public function store(
        Request $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        $validated = $request->validate([
            'resident_id' => [
                'required',
                'exists:residents,id',
            ],

            'document_type' => [
                'required',
                Rule::in(self::documentTypes()),
            ],

            'purpose' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'document_details' => [
                'nullable',
                'array',
            ],

            'document_details.ctc_number' => [
                'nullable',
                'string',
                'max:50',
            ],

            'document_details.birthplace' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'document_details.father_name' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'document_details.mother_name' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'document_details.permit_to_cut_no' => [
                'nullable',
                'string',
                'max:100',
            ],

            'document_details.permit_to_cut_date' => [
                'nullable',
                'date',
            ],

            'document_details.permit_issued_by' => [
                'nullable',
                'string',
                'max:255',
            ],

            'document_details.tree_count' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'document_details.tree_type' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'document_details.location' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'document_details.location_description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'document_details.tree_owner' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'document_details.cutter_name' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'document_details.child_resident_ids' => [
                'nullable',
                'array',
            ],

            'document_details.child_resident_ids.*' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Active Resident Validation
        |--------------------------------------------------------------------------
        */

        $resident = Resident::query()
            ->whereKey($validated['resident_id'])
            ->where('status', 'Active')
            ->with([
                'householdMemberships.resident',
                'headedHouseholds',
            ])
            ->first();

        if (! $resident) {
            return back()
                ->withInput()
                ->withErrors([
                    'resident_id' =>
                        'Only active registered residents can request documents.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | RESPONDENT ACTIVE CASE RESTRICTION
        |--------------------------------------------------------------------------
        */

        $hasActiveRespondentCase = ReportComplaint::query()
            ->where('subject_resident_id', $resident->id)
            ->whereNotIn('status', [
                'Resolved',
                'Closed',
                'Disapproved',
            ])
            ->exists();

        if ($hasActiveRespondentCase) {
            return back()
                ->withInput()
                ->withErrors([
                    'resident_id' =>
                        'Document requests are not allowed while this resident has an active or unresolved complaint case as a respondent. The request may proceed once all applicable cases have been resolved or closed.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Clean Document Details
        |--------------------------------------------------------------------------
        */

        $documentDetails = collect(
            $validated['document_details'] ?? []
        )
            ->map(function ($value) {
                return is_string($value)
                    ? trim($value)
                    : $value;
            })
            ->filter(function ($value) {
                return $value !== null && $value !== '';
            })
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Barangay Clearance CTC Number
        |--------------------------------------------------------------------------
        */

        if ($validated['document_type'] === 'Barangay Clearance') {
            $ctcNumber = trim((string) (
                $documentDetails['ctc_number'] ?? ''
            ));

            $documentDetails = $ctcNumber !== ''
                ? ['ctc_number' => $ctcNumber]
                : [];
        }

        /*
        |--------------------------------------------------------------------------
        | Solo Parent Validation
        |--------------------------------------------------------------------------
        */

        if ($validated['document_type'] === 'Solo Parent') {
            $childResidentIds =
                $documentDetails['child_resident_ids'] ?? [];

            if (! is_array($childResidentIds)) {
                $childResidentIds = [$childResidentIds];
            }

            $childResidentIds = collect($childResidentIds)
                ->filter(function ($id) {
                    return $id !== null && $id !== '';
                })
                ->map(function ($id) {
                    return (string) $id;
                })
                ->unique()
                ->values()
                ->all();

            if (empty($childResidentIds)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.child_resident_ids' =>
                            'Please add at least one child or family member from the registered household.',
                    ]);
            }

            $validHouseholdMemberIds = collect();

            foreach ($resident->householdMemberships as $membership) {
                if (
                    $membership->resident &&
                    $membership->resident->status === 'Active' &&
                    $membership->resident->id !== $resident->id
                ) {
                    $validHouseholdMemberIds->push(
                        (string) $membership->resident_id
                    );
                }
            }

            foreach ($resident->headedHouseholds as $household) {
                $householdMembers = $household
                    ->members()
                    ->with('resident')
                    ->get();

                foreach ($householdMembers as $membership) {
                    if (
                        $membership->resident &&
                        $membership->resident->status === 'Active' &&
                        $membership->resident->id !== $resident->id
                    ) {
                        $validHouseholdMemberIds->push(
                            (string) $membership->resident_id
                        );
                    }
                }
            }

            $validHouseholdMemberIds = $validHouseholdMemberIds
                ->unique()
                ->values();

            $invalidResidentIds = collect($childResidentIds)
                ->reject(function ($residentId) use (
                    $validHouseholdMemberIds
                ) {
                    return $validHouseholdMemberIds
                        ->contains((string) $residentId);
                })
                ->values()
                ->all();

            if (! empty($invalidResidentIds)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.child_resident_ids' =>
                            'One or more selected family members are not registered members of the selected resident\'s household.',
                    ]);
            }

            $activeChildrenCount = Resident::query()
                ->whereIn('id', $childResidentIds)
                ->where('status', 'Active')
                ->count();

            if ($activeChildrenCount !== count($childResidentIds)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.child_resident_ids' =>
                            'One or more selected family members are no longer active resident records.',
                    ]);
            }

            $documentDetails = [
                'child_resident_ids' => array_values($childResidentIds),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Certificate of Late Register Validation
        |--------------------------------------------------------------------------
        */

        if ($validated['document_type'] === 'Certificate of Late Register') {
            $birthplace = trim((string) (
                $documentDetails['birthplace'] ?? ''
            ));

            $fatherName = trim((string) (
                $documentDetails['father_name'] ?? ''
            ));

            $motherName = trim((string) (
                $documentDetails['mother_name'] ?? ''
            ));

            if ($birthplace === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.birthplace' =>
                            'The place of birth is required for a Certificate of Late Register.',
                    ]);
            }

            if ($fatherName === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.father_name' =>
                            "The father's name is required for a Certificate of Late Register.",
                    ]);
            }

            if ($motherName === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.mother_name' =>
                            "The mother's name is required for a Certificate of Late Register.",
                    ]);
            }

            $documentDetails = [
                'birthplace' => $birthplace,
                'father_name' => $fatherName,
                'mother_name' => $motherName,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Cutting Trees Validation
        |--------------------------------------------------------------------------
        */

        if ($validated['document_type'] === 'Cutting Trees') {
            $permitNumber = trim((string) (
                $documentDetails['permit_to_cut_no'] ?? ''
            ));

            $permitDate = trim((string) (
                $documentDetails['permit_to_cut_date'] ?? ''
            ));

            $permitIssuedBy = trim((string) (
                $documentDetails['permit_issued_by'] ?? ''
            ));

            $treeCount = $documentDetails['tree_count'] ?? null;

            $treeType = trim((string) (
                $documentDetails['tree_type'] ?? ''
            ));

            $location = trim((string) (
                $documentDetails['location'] ?? ''
            ));

            $locationDescription = trim((string) (
                $documentDetails['location_description'] ?? ''
            ));

            $treeOwner = trim((string) (
                $documentDetails['tree_owner'] ?? ''
            ));

            $aroundOf = trim((string) (
                $documentDetails['around_of'] ?? ''
            ));

            $nearbyThe = trim((string) (
                $documentDetails['nearby_the'] ?? ''
            ));

            $cutterName = trim((string) (
                $documentDetails['cutter_name'] ?? ''
            ));

            if ($permitNumber === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.permit_to_cut_no' =>
                            'The Permit to Cut number is required.',
                    ]);
            }

            if ($permitDate === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.permit_to_cut_date' =>
                            'The Permit to Cut date is required.',
                    ]);
            }

            if (! strtotime($permitDate)) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.permit_to_cut_date' =>
                            'Please enter a valid Permit to Cut date.',
                    ]);
            }

            if ($permitIssuedBy === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.permit_issued_by' =>
                            'The permit issuing office is required.',
                    ]);
            }

            if (! is_numeric($treeCount) || (int) $treeCount < 1) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.tree_count' =>
                            'The number of trees must be at least 1.',
                    ]);
            }

            if ($treeType === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.tree_type' =>
                            'The type of tree or trees is required.',
                    ]);
            }

            if ($location === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.location' =>
                            'The tree location is required.',
                    ]);
            }

            if ($locationDescription === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.location_description' =>
                            'The location description / landmark is required.',
                    ]);
            }

            if ($cutterName === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.cutter_name' =>
                            'The cutter name is required.',
                    ]);
            }

            $documentDetails = [
                'permit_to_cut_no' => $permitNumber,
                'permit_to_cut_date' => date('Y-m-d', strtotime($permitDate)),
                'permit_issued_by' => $permitIssuedBy,
                'tree_count' => (int) $treeCount,
                'tree_type' => $treeType,
                'location' => $location,
                'location_description' => $locationDescription,
                'tree_owner' => $treeOwner,
                'around_of' => $aroundOf,
                'nearby_the' => $nearbyThe,
                'cutter_name' => $cutterName,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Create Document Request
        |--------------------------------------------------------------------------
        */

        $document = DocumentRequest::create([
            'request_code' => $this->generateRequestCode(),
            'resident_id' => $validated['resident_id'],
            'document_type' => $validated['document_type'],
            'purpose' => $validated['purpose'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'document_details' => ! empty($documentDetails)
                ? $documentDetails
                : null,
            'status' => 'Pending',
            'workflow_stage' => DocumentRequest::STAGE_REQUESTED,
            'requested_at' => now(),
            'generated_pdf_path' => null,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Audit Trail
        |--------------------------------------------------------------------------
        */

        $auditLog->log(
            action: 'Created',
            module: 'Barangay Documents',
            record: $document,
            newValues: [
                'request_code' => $document->request_code,
                'resident_id' => $document->resident_id,
                'document_type' => $document->document_type,
                'purpose' => $document->purpose,
                'document_details' => $document->document_details,
                'status' => $document->status,
                'workflow_stage' => $document->workflow_stage,
            ],
            description: 'Created a new barangay document request.'
        );

        return redirect()
            ->route('documents.show', $document)
            ->with(
                'success',
                'Document request successfully created. You may now process the document.'
            );
    }

    public function show(DocumentRequest $document): View
    {
        $document->load([
            'resident',
            'inspectionOfficer',
            'idVerifiedOfficer',
            'eligibleOfficer',
            'approvedOfficer',
            'printedOfficer',
            'signedSealedOfficer',
            'releasedOfficer',
        ]);

        return view('documents.show', compact('document'));
    }

    /*
    |--------------------------------------------------------------------------
    | Preview Official Document
    |--------------------------------------------------------------------------
    */

    public function preview(
        Request $request,
        DocumentRequest $document,
        OfficialDocumentTemplateService $templateService,
        DocumentConversionService $conversionService
    ): View|RedirectResponse {
        $user = $request->user();

        if (! $user || ! $user->canProcessDocuments()) {
            abort(403, 'You are not authorized to preview document requests.');
        }

        $allowedStages = [
            DocumentRequest::STAGE_APPROVED,
            DocumentRequest::STAGE_PRINTED,
            DocumentRequest::STAGE_SIGNED_SEALED,
            DocumentRequest::STAGE_RELEASED,
        ];

        if (! in_array($document->workflow_stage, $allowedStages, true)) {
            return redirect()
                ->route('documents.show', $document)
                ->withErrors([
                    'workflow' =>
                        'The document must be approved before it can be previewed.',
                ]);
        }

        try {
            $generatedPdfPath = $this->getOrGeneratePdf(
                $document,
                $templateService,
                $conversionService
            );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route('documents.show', $document)
                ->withErrors([
                    'workflow' => $exception->getMessage(),
                ]);
        }

        if (! $generatedPdfPath || ! is_file($generatedPdfPath)) {
            return redirect()
                ->route('documents.show', $document)
                ->withErrors([
                    'workflow' => 'The generated PDF could not be found.',
                ]);
        }

        return view(
            'documents.print',
            [
                'document' => $document,
                'pdfData' => base64_encode(file_get_contents($generatedPdfPath)),
                'mode' => 'preview',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Advance Document Workflow
    |--------------------------------------------------------------------------
    */

    public function advance(
        Request $request,
        DocumentRequest $document,
        AuditLogService $auditLog,
        OfficialDocumentTemplateService $templateService,
        DocumentConversionService $conversionService
    ): RedirectResponse|View {
        $user = $request->user();

        if (! $user || ! $user->canProcessDocuments()) {
            abort(403, 'You are not authorized to process document requests.');
        }

        $validated = $request->validate([
            'action' => [
                'required',
                Rule::in([
                    'process_request',
                    'approve_release',
                    'print_document',
                    'confirm_signed_sealed',
                    'release_document',
                    'decline_third_party',
                ]),
            ],

            'decline_reason' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        if ($validated['action'] === 'decline_third_party') {
            return $this->declineThirdPartyRequest(
                $request,
                $document,
                $user->id,
                $auditLog
            );
        }

        $transitions = [
            'process_request' => [
                'from' => DocumentRequest::STAGE_REQUESTED,
                'to' => DocumentRequest::STAGE_ID_VERIFIED,
                'status' => 'Processing',
                'by' => 'id_verified_by',
                'at' => 'id_verified_at',
                'message' =>
                    'Request verified. Inspection and ID verification were recorded.',
                'audit_action' => 'Verified',
                'audit_description' =>
                    'Document request was inspected and the resident identity and presented ID were verified. The request advanced one visible journey step to Verified.',
            ],

            'approve_release' => [
                'from' => DocumentRequest::STAGE_ID_VERIFIED,
                'to' => DocumentRequest::STAGE_APPROVED,
                'status' => 'Approved',
                'by' => 'approved_by',
                'at' => 'approved_at',
                'message' =>
                    'Request verified, marked eligible, and approved for release.',
                'audit_action' => 'Approved',
                'audit_description' =>
                    'The verified resident was confirmed eligible and the document request was approved for release. Eligibility remains recorded as a detailed internal check while the visible journey advanced one step to Approved.',
            ],

            'print_document' => [
                'from' => DocumentRequest::STAGE_APPROVED,
                'to' => DocumentRequest::STAGE_PRINTED,
                'status' => 'Completed',
                'by' => 'printed_by',
                'at' => 'printed_at',
                'message' =>
                    'Official document generated and printing recorded.',
                'audit_action' => 'Printed',
                'audit_description' =>
                    'Official document was generated from the registered barangay template and printing was recorded.',
            ],

            'confirm_signed_sealed' => [
                'from' => DocumentRequest::STAGE_PRINTED,
                'to' => DocumentRequest::STAGE_SIGNED_SEALED,
                'status' => 'Completed',
                'by' => 'signed_sealed_by',
                'at' => 'signed_sealed_at',
                'message' =>
                    'Physical signature and barangay seal confirmed.',
                'audit_action' => 'Signed and Sealed',
                'audit_description' =>
                    'Physical signature and barangay seal were confirmed.',
            ],

            'release_document' => [
                'from' => DocumentRequest::STAGE_SIGNED_SEALED,
                'to' => DocumentRequest::STAGE_RELEASED,
                'status' => 'Released',
                'by' => 'released_by',
                'at' => 'released_at',
                'message' =>
                    'Document successfully released to the resident.',
                'audit_action' => 'Released',
                'audit_description' =>
                    'Document was released to the resident.',
            ],
        ];

        $transition = $transitions[$validated['action']];

        if ($document->workflow_stage !== $transition['from']) {
            return back()->withErrors([
                'workflow' =>
                    'This action is not available at the current processing stage.',
            ]);
        }

        $generatedPdfPath = null;

        if ($validated['action'] === 'print_document') {
            try {
                $generatedPdfPath = $this->getOrGeneratePdf(
                    $document,
                    $templateService,
                    $conversionService
                );
            } catch (RuntimeException $exception) {
                return back()->withErrors([
                    'workflow' => $exception->getMessage(),
                ]);
            }

            if (!$generatedPdfPath || !is_file($generatedPdfPath)) {
                return back()->withErrors([
                    'workflow' =>
                        'The official PDF document was not generated successfully. The document was not marked as printed.',
                ]);
            }
        }

        $oldValues = [
            'status' => $document->status,
            'workflow_stage' => $document->workflow_stage,
            'print_count' => $document->print_count,
        ];

        $updates = [
            'workflow_stage' => $transition['to'],
            'status' => $transition['status'],
            $transition['by'] => $user->id,
            $transition['at'] => now(),
        ];

        if ($validated['action'] === 'process_request') {
            $updates['inspection_by'] = $user->id;
            $updates['inspection_at'] = now();
            $updates['id_verified_by'] = $user->id;
            $updates['id_verified_at'] = now();
        }

        if ($validated['action'] === 'approve_release') {
            $updates['eligible_by'] = $user->id;
            $updates['eligible_at'] = now();
            $updates['processed_at'] = now();
        }

        if ($validated['action'] === 'print_document') {
            $updates['print_count'] = ((int) $document->print_count) + 1;
        }

        $document->update($updates);

        $newValues = [
            'status' => $document->status,
            'workflow_stage' => $document->workflow_stage,
            'print_count' => $document->print_count,
        ];

        $auditLog->log(
            action: $transition['audit_action'],
            module: 'Barangay Documents',
            record: $document,
            oldValues: $oldValues,
            newValues: $newValues,
            description: $transition['audit_description']
        );

        if ($validated['action'] === 'approve_release') {
            $documentId = $document->id;

            dispatch(function () use ($documentId) {
                try {
                    $fresh = DocumentRequest::find($documentId);

                    if ($fresh) {
                        $this->getOrGeneratePdf(
                            $fresh,
                            app(OfficialDocumentTemplateService::class),
                            app(DocumentConversionService::class)
                        );
                    }
                } catch (\Throwable $e) {
                    Log::warning('PDF pre-generation failed.', [
                        'document_id' => $documentId,
                        'error' => $e->getMessage(),
                    ]);
                }
            })->afterResponse();
        }

        if ($validated['action'] === 'print_document') {
            return view(
                'documents.print',
                [
                    'document' => $document,
                    'pdfData' => base64_encode(file_get_contents($generatedPdfPath)),
                    'mode' => 'print',
                ]
            );
        }

        return redirect()
            ->route('documents.show', $document)
            ->with('success', $transition['message']);
    }

    /*
    |--------------------------------------------------------------------------
    | Print Official Document
    |--------------------------------------------------------------------------
    */

    public function print(
        Request $request,
        DocumentRequest $document,
        AuditLogService $auditLog,
        OfficialDocumentTemplateService $templateService,
        DocumentConversionService $conversionService
    ): View|RedirectResponse {
        $user = $request->user();

        if (!$user || !$user->canProcessDocuments()) {
            abort(403, 'You are not authorized to print document requests.');
        }

        $allowedStages = [
            DocumentRequest::STAGE_APPROVED,
            DocumentRequest::STAGE_PRINTED,
            DocumentRequest::STAGE_SIGNED_SEALED,
            DocumentRequest::STAGE_RELEASED,
        ];

        if (!in_array($document->workflow_stage, $allowedStages, true)) {
            return redirect()
                ->route('documents.show', $document)
                ->withErrors([
                    'workflow' =>
                        'This document must be approved before it can be printed.',
                ]);
        }

        $generatedPdfPath = $this->getOrGeneratePdf(
            $document,
            $templateService,
            $conversionService
        );

        if (!$generatedPdfPath || !is_file($generatedPdfPath)) {
            return redirect()
                ->route('documents.show', $document)
                ->withErrors([
                    'workflow' =>
                        'The official PDF document could not be generated successfully. The document was not marked as printed.',
                ]);
        }

        if ($document->workflow_stage === DocumentRequest::STAGE_APPROVED) {
            $oldValues = [
                'status' => $document->status,
                'workflow_stage' => $document->workflow_stage,
                'printed_by' => $document->printed_by,
                'printed_at' => $document->printed_at,
                'print_count' => $document->print_count,
            ];

            $document->update([
                'workflow_stage' => DocumentRequest::STAGE_PRINTED,
                'status' => 'Completed',
                'printed_by' => $user->id,
                'printed_at' => now(),
                'print_count' => ((int) $document->print_count) + 1,
            ]);

            $document->refresh();

            $newValues = [
                'status' => $document->status,
                'workflow_stage' => $document->workflow_stage,
                'printed_by' => $document->printed_by,
                'printed_at' => $document->printed_at,
                'print_count' => $document->print_count,
            ];

            $auditLog->log(
                action: 'Printed',
                module: 'Barangay Documents',
                record: $document,
                oldValues: $oldValues,
                newValues: $newValues,
                description:
                    'Official document was generated from the registered barangay template and the print action was recorded. The document is now marked as Printed.'
            );
        }

        return view(
            'documents.print',
            [
                'document' => $document,
                'pdfData' => base64_encode(file_get_contents($generatedPdfPath)),
                'mode' => 'print',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generated PDF Helper
    |--------------------------------------------------------------------------
    */

    private function getOrGeneratePdf(
        DocumentRequest $document,
        OfficialDocumentTemplateService $templateService,
        DocumentConversionService $conversionService
    ): ?string {
        if (
            $document->generatedPdfExists() &&
            !$this->clearancePdfNeedsRegeneration($document)
        ) {
            return $document->getGeneratedPdfAbsolutePath();
        }

        $requestedAt = time();

        $generate = function () use (
            $document,
            $templateService,
            $conversionService,
            $requestedAt
        ): ?string {
            $document->refresh();

            if (
                $document->generatedPdfExists() &&
                @filemtime($document->getGeneratedPdfAbsolutePath()) >= $requestedAt
            ) {
                return $document->getGeneratedPdfAbsolutePath();
            }

            $generatedDocumentPath = $templateService->generate($document);

            $generatedPdfPath = $conversionService->convertDocxToPdf(
                $generatedDocumentPath,
                $document->document_type
            );

            if (!is_file($generatedPdfPath)) {
                return null;
            }

            $document->update([
                'generated_pdf_path' =>
                    $this->relativePrivateStoragePath($generatedPdfPath),
            ]);

            $document->refresh();

            return $document->getGeneratedPdfAbsolutePath();
        };

        try {
            return Cache::lock('document-pdf:' . $document->id, 120)
                ->block(60, $generate);
        } catch (\Illuminate\Contracts\Cache\LockTimeoutException $e) {
            throw new RuntimeException(
                'The document is still being generated. Please try again in a few seconds.'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Old Multi-Page Barangay Clearance PDF
    |--------------------------------------------------------------------------
    */

    private function clearancePdfNeedsRegeneration(
        DocumentRequest $document
    ): bool {
        if ($document->document_type !== 'Barangay Clearance') {
            return false;
        }

        $path = $document->getGeneratedPdfAbsolutePath();

        if (!$path || !is_file($path)) {
            return false;
        }

        $pdf = (string) file_get_contents($path);

        $isMultiPage = preg_match_all(
            '/\/Type\s*\/Page\b/',
            $pdf
        ) > 1;

        if (!$isMultiPage) {
            return false;
        }

        return Cache::add(
            'clearance-regenerated:' . $document->id,
            true,
            now()->addYears(5)
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Convert Absolute Private Storage Path to Relative Path
    |--------------------------------------------------------------------------
    */

    private function relativePrivateStoragePath(
        string $absolutePath
    ): string {
        $privateStoragePath = storage_path('app/private');

        $normalizedAbsolutePath = str_replace('\\', '/', $absolutePath);

        $normalizedPrivateStoragePath = str_replace(
            '\\',
            '/',
            $privateStoragePath
        );

        if (
            Str::startsWith(
                $normalizedAbsolutePath,
                $normalizedPrivateStoragePath . '/'
            )
        ) {
            return ltrim(
                Str::after(
                    $normalizedAbsolutePath,
                    $normalizedPrivateStoragePath
                ),
                '/'
            );
        }

        return ltrim($normalizedAbsolutePath, '/');
    }

    /*
    |--------------------------------------------------------------------------
    | Decline Third-Party Request
    |--------------------------------------------------------------------------
    */

    private function declineThirdPartyRequest(
        Request $request,
        DocumentRequest $document,
        int $userId,
        AuditLogService $auditLog
    ): RedirectResponse {
        $allowedStages = [
            DocumentRequest::STAGE_REQUESTED,
            DocumentRequest::STAGE_INSPECTION,
            DocumentRequest::STAGE_ID_VERIFIED,
            DocumentRequest::STAGE_ELIGIBLE,
        ];

        if (!in_array($document->workflow_stage, $allowedStages, true)) {
            return back()->withErrors([
                'workflow' =>
                    'This request can no longer be declined as a third-party request.',
            ]);
        }

        $reason = $request->input(
            'decline_reason',
            'Resident must personally appear and present a valid ID. Third-party release is not allowed.'
        );

        $oldValues = [
            'status' => $document->status,
            'workflow_stage' => $document->workflow_stage,
            'decline_reason' => $document->decline_reason,
        ];

        $document->update([
            'workflow_stage' => DocumentRequest::STAGE_THIRD_PARTY_DECLINED,
            'status' => 'Disapproved',
            'decline_reason' => $reason,
            'remarks' => $reason,
            'processed_at' => now(),
            'inspection_by' => $userId,
            'inspection_at' => now(),
        ]);

        $newValues = [
            'status' => $document->status,
            'workflow_stage' => $document->workflow_stage,
            'decline_reason' => $document->decline_reason,
        ];

        $auditLog->log(
            action: 'Third-Party Request Declined',
            module: 'Barangay Documents',
            record: $document,
            oldValues: $oldValues,
            newValues: $newValues,
            description:
                'Document request was declined because the resident was not personally present for release.'
        );

        return redirect()
            ->route('documents.show', $document)
            ->with(
                'success',
                'Request marked as not released because the resident was not personally present.'
            );
    }

    private function generateRequestCode(): string
    {
        do {
            $code = 'BR-' .
                now()->format('Y') .
                '-' .
                Str::upper(Str::random(6));
        } while (
            DocumentRequest::where('request_code', $code)->exists()
        );

        return $code;
    }
}
