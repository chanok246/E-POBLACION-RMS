<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\Household;
use App\Models\Resident;
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
        /*
         * The "released" filter powers the combined "Released / Completed"
         * summary card, so it matches both database statuses. The database
         * values themselves are NOT changed. "completed" is kept so old
         * links / bookmarks still work.
         */
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
                            ->where(
                                'request_code',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'document_type',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'resident',
                                function ($residentQuery) use ($search) {
                                    $residentQuery
                                        ->where(
                                            'first_name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'middle_name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'last_name',
                                            'like',
                                            "%{$search}%"
                                        );
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

        /*
         * Summary card counts (not affected by search / status filter).
         * "released" = Released + Completed.
         */
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
        | Solo Parent Household Members
        |--------------------------------------------------------------------------
        */

        $soloParentHouseholdMembers = [];

        foreach ($residents as $resident) {
            $members = collect();

            /*
             * Household memberships
             */
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
                    'resident_id' =>
                        $child->id,

                    'full_name' =>
                        $child->full_name,

                    'sex' =>
                        $child->sex,

                    'birth_date' =>
                        $child->birth_date
                            ? $child->birth_date->format('Y-m-d')
                            : null,

                    'birth_date_display' =>
                        $child->birth_date
                            ? $child->birth_date->format('F j, Y')
                            : null,

                    'relationship' =>
                        $membership->relationship,
                ]);
            }

            /*
             * Headed households
             */
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
                        'resident_id' =>
                            $child->id,

                        'full_name' =>
                            $child->full_name,

                        'sex' =>
                            $child->sex,

                        'birth_date' =>
                            $child->birth_date
                                ? $child->birth_date->format('Y-m-d')
                                : null,

                        'birth_date_display' =>
                            $child->birth_date
                                ? $child->birth_date->format('F j, Y')
                                : null,

                        'relationship' =>
                            $membership->relationship,
                    ]);
                }
            }

            $soloParentHouseholdMembers[$resident->id] =
                $members
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
                'soloParentHouseholdMembers'
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
                Rule::in(
                    self::documentTypes()
                ),
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

            /*
             * Certificate of Late Register fields.
             *
             * Laravel's validate() only returns keys that have a rule.
             * Without these rules, birthplace / father_name / mother_name
             * were stripped from $validated['document_details'], so the
             * Late Register check below always saw empty values.
             * "nullable" is used here so the existing, friendlier
             * "required" messages further down are still the ones shown.
             */
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

            /*
             * Cutting Trees fields.
             *
             * Same reason as Late Register above: validate() only keeps
             * keys that have a rule, so every Cutting Trees field was being
             * stripped. "nullable" keeps the friendlier "required" messages
             * in the Cutting Trees block below as the ones shown.
             */
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
                return $value !== null &&
                    $value !== '';
            })
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Barangay Clearance CTC Number
        |--------------------------------------------------------------------------
        |
        | Keep only the CTC number actually entered by the staff.
        | If it was left blank, nothing is saved and the line stays empty.
        */

        if (
            $validated['document_type'] ===
            'Barangay Clearance'
        ) {
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

        if (
            $validated['document_type'] ===
            'Solo Parent'
        ) {
            $childResidentIds =
                $documentDetails['child_resident_ids'] ?? [];

            if (! is_array($childResidentIds)) {
                $childResidentIds = [
                    $childResidentIds,
                ];
            }

            $childResidentIds = collect(
                $childResidentIds
            )
                ->filter(function ($id) {
                    return $id !== null &&
                        $id !== '';
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

            /*
             * Household members.
             */
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

            /*
             * Members from households where resident
             * is household head.
             */
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

            $validHouseholdMemberIds =
                $validHouseholdMemberIds
                    ->unique()
                    ->values();

            /*
             * Verify selected family members.
             */
            $invalidResidentIds = collect(
                $childResidentIds
            )
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

            /*
             * Verify active residents.
             */
            $activeChildrenCount = Resident::query()
                ->whereIn(
                    'id',
                    $childResidentIds
                )
                ->where(
                    'status',
                    'Active'
                )
                ->count();

            if (
                $activeChildrenCount !==
                count($childResidentIds)
            ) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.child_resident_ids' =>
                            'One or more selected family members are no longer active resident records.',
                    ]);
            }

            $documentDetails = [
                'child_resident_ids' =>
                    array_values(
                        $childResidentIds
                    ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Certificate of Late Register Validation
        |--------------------------------------------------------------------------
        |
        | Keep the three values entered in the Late Register form.
        | These values are later consumed by the official template
        | service when replacing the sample birthplace and parent names.
        */

        if (
            $validated['document_type'] ===
            'Certificate of Late Register'
        ) {
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

            /*
             * Save only the fields used by the Late Register template.
             * Do not allow unrelated document_details values to affect
             * the official document.
             */
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
        |
        | These values come from the actual permit/supporting record.
        | Location context is intentionally free-text so the Captain or
        | Secretary can enter the wording that matches the actual permit.
        */

        if (
            $validated['document_type'] ===
            'Cutting Trees'
        ) {
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

            /*
             * The form field is "location_description" (landmark / exact
             * location). It was previously checked as "inside_of", which the
             * form never sends.
             */
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
            'request_code' =>
                $this->generateRequestCode(),

            'resident_id' =>
                $validated['resident_id'],

            'document_type' =>
                $validated['document_type'],

            'purpose' =>
                $validated['purpose'] ?? null,

            'remarks' =>
                $validated['remarks'] ?? null,

            'document_details' =>
                ! empty($documentDetails)
                    ? $documentDetails
                    : null,

            'status' =>
                'Pending',

            'workflow_stage' =>
                DocumentRequest::STAGE_REQUESTED,

            'requested_at' =>
                now(),

            'generated_pdf_path' =>
                null,
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
                'request_code' =>
                    $document->request_code,

                'resident_id' =>
                    $document->resident_id,

                'document_type' =>
                    $document->document_type,

                'purpose' =>
                    $document->purpose,

                'document_details' =>
                    $document->document_details,

                'status' =>
                    $document->status,

                'workflow_stage' =>
                    $document->workflow_stage,
            ],
            description:
                'Created a new barangay document request.'
        );

        return redirect()
            ->route(
                'documents.show',
                $document
            )
            ->with(
                'success',
                'Document request successfully created. You may now process the document.'
            );
    }

    public function show(
        DocumentRequest $document
    ): View {
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

        return view(
            'documents.show',
            compact('document')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Preview Official Document
    |--------------------------------------------------------------------------
    |
    | Preview DOES NOT mark the document as printed.
    |
    | If a personalized PDF has already been generated, it is reused.
    | Otherwise, the official DOCX template is generated and converted once.
    |
    */

    public function preview(
        Request $request,
        DocumentRequest $document,
        OfficialDocumentTemplateService $templateService,
        DocumentConversionService $conversionService
    ): View|RedirectResponse {
        $user = $request->user();

        if (
            ! $user ||
            ! $user->canProcessDocuments()
        ) {
            abort(
                403,
                'You are not authorized to preview document requests.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Preview Allowed Stages
        |--------------------------------------------------------------------------
        */

        $allowedStages = [
            DocumentRequest::STAGE_APPROVED,
            DocumentRequest::STAGE_PRINTED,
            DocumentRequest::STAGE_SIGNED_SEALED,
            DocumentRequest::STAGE_RELEASED,
        ];

        if (
            ! in_array(
                $document->workflow_stage,
                $allowedStages,
                true
            )
        ) {
            return redirect()
                ->route(
                    'documents.show',
                    $document
                )
                ->withErrors([
                    'workflow' =>
                        'The document must be approved before it can be previewed.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Use Existing Generated PDF
        |--------------------------------------------------------------------------
        |
        | This is the main performance improvement.
        |
        | If the PDF already exists, DO NOT call PHPWord or Gotenberg again.
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | Final PDF Validation
        |--------------------------------------------------------------------------
        */

        if (
            ! $generatedPdfPath ||
            ! is_file($generatedPdfPath)
        ) {
            return redirect()
                ->route(
                    'documents.show',
                    $document
                )
                ->withErrors([
                    'workflow' =>
                        'The generated PDF could not be found.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Display PDF Inline
        |--------------------------------------------------------------------------
        */

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

        if (
            ! $user ||
            ! $user->canProcessDocuments()
        ) {
            abort(
                403,
                'You are not authorized to process document requests.'
            );
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

        /*
        |--------------------------------------------------------------------------
        | Third Party
        |--------------------------------------------------------------------------
        */

        if (
            $validated['action'] ===
            'decline_third_party'
        ) {
            return $this->declineThirdPartyRequest(
                $request,
                $document,
                $user->id,
                $auditLog
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Workflow Transitions
        |--------------------------------------------------------------------------
        */

        $transitions = [

            /*
            |----------------------------------------------------------------------
            | Visible Journey 1 → 2
            |----------------------------------------------------------------------
            |
            | Request Created → Verified
            |
            | The detailed inspection and ID verification are still recorded in
            | their own audit fields, but they are NOT separate visible journey
            | slots. One button produces exactly one visible journey movement.
            |
            */
            'process_request' => [
                'from' =>
                    DocumentRequest::STAGE_REQUESTED,

                'to' =>
                    DocumentRequest::STAGE_ID_VERIFIED,

                'status' =>
                    'Processing',

                'by' =>
                    'id_verified_by',

                'at' =>
                    'id_verified_at',

                'message' =>
                    'Request verified. Inspection and ID verification were recorded.',

                'audit_action' =>
                    'Verified',

                'audit_description' =>
                    'Document request was inspected and the resident identity and presented ID were verified. The request advanced one visible journey step to Verified.',
            ],

            /*
            |----------------------------------------------------------------------
            | Visible Journey 2 → 3
            |----------------------------------------------------------------------
            |
            | Verified → Approved
            |
            | Eligibility remains a detailed internal check. It is recorded at
            | the same time as the approval decision, so it does not create an
            | extra visible journey slot or an extra button click.
            |
            */
            'approve_release' => [
                'from' =>
                    DocumentRequest::STAGE_ID_VERIFIED,

                'to' =>
                    DocumentRequest::STAGE_APPROVED,

                'status' =>
                    'Approved',

                'by' =>
                    'approved_by',

                'at' =>
                    'approved_at',

                'message' =>
                    'Request verified, marked eligible, and approved for release.',

                'audit_action' =>
                    'Approved',

                'audit_description' =>
                    'The verified resident was confirmed eligible and the document request was approved for release. Eligibility remains recorded as a detailed internal check while the visible journey advanced one step to Approved.',
            ],

            /*
            |----------------------------------------------------------------------
            | Visible Journey 3 → 4
            |----------------------------------------------------------------------
            */
            'print_document' => [
                'from' =>
                    DocumentRequest::STAGE_APPROVED,

                'to' =>
                    DocumentRequest::STAGE_PRINTED,

                'status' =>
                    'Completed',

                'by' =>
                    'printed_by',

                'at' =>
                    'printed_at',

                'message' =>
                    'Official document generated and printing recorded.',

                'audit_action' =>
                    'Printed',

                'audit_description' =>
                    'Official document was generated from the registered barangay template and printing was recorded.',
            ],

            /*
            |----------------------------------------------------------------------
            | Visible Journey 4 → 5
            |----------------------------------------------------------------------
            */
            'confirm_signed_sealed' => [
                'from' =>
                    DocumentRequest::STAGE_PRINTED,

                'to' =>
                    DocumentRequest::STAGE_SIGNED_SEALED,

                'status' =>
                    'Completed',

                'by' =>
                    'signed_sealed_by',

                'at' =>
                    'signed_sealed_at',

                'message' =>
                    'Physical signature and barangay seal confirmed.',

                'audit_action' =>
                    'Signed and Sealed',

                'audit_description' =>
                    'Physical signature and barangay seal were confirmed.',
            ],

            /*
            |----------------------------------------------------------------------
            | Visible Journey 5 → 6
            |----------------------------------------------------------------------
            */
            'release_document' => [
                'from' =>
                    DocumentRequest::STAGE_SIGNED_SEALED,

                'to' =>
                    DocumentRequest::STAGE_RELEASED,

                'status' =>
                    'Released',

                'by' =>
                    'released_by',

                'at' =>
                    'released_at',

                'message' =>
                    'Document successfully released to the resident.',

                'audit_action' =>
                    'Released',

                'audit_description' =>
                    'Document was released to the resident.',
            ],
        ];

        $transition =
            $transitions[$validated['action']];

        /*
        |--------------------------------------------------------------------------
        | Current Stage Check
        |--------------------------------------------------------------------------
        */

        if (
            $document->workflow_stage !==
            $transition['from']
        ) {
            return back()->withErrors([
                'workflow' =>
                    'This action is not available at the current processing stage.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Generate / Reuse Document During Print Workflow
        |--------------------------------------------------------------------------
        */

        $generatedPdfPath = null;

        if (
            $validated['action'] ===
            'print_document'
        ) {
            try {
                $generatedPdfPath =
                    $this->getOrGeneratePdf(
                        $document,
                        $templateService,
                        $conversionService
                    );
            } catch (RuntimeException $exception) {
                return back()->withErrors([
                    'workflow' => $exception->getMessage(),
                ]);
            }

            if (
                ! $generatedPdfPath ||
                ! is_file($generatedPdfPath)
            ) {
                return back()->withErrors([
                    'workflow' =>
                        'The official PDF document was not generated successfully. The document was not marked as printed.',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Old Values
        |--------------------------------------------------------------------------
        */

        $oldValues = [
            'status' =>
                $document->status,

            'workflow_stage' =>
                $document->workflow_stage,

            'print_count' =>
                $document->print_count,
        ];

        /*
        |--------------------------------------------------------------------------
        | Prepare Update
        |--------------------------------------------------------------------------
        */

        $updates = [
            'workflow_stage' =>
                $transition['to'],

            'status' =>
                $transition['status'],

            $transition['by'] =>
                $user->id,

            $transition['at'] =>
                now(),
        ];

        /*
        |----------------------------------------------------------------------
        | Detailed Verification Records
        |----------------------------------------------------------------------
        |
        | These fields preserve the detailed internal checks without turning
        | each check into another visible Processing Journey slot.
        |
        | process_request:
        |   Request Created → Verified
        |   Records inspection + ID verification.
        |
        | approve_release:
        |   Verified → Approved
        |   Records eligibility + approval.
        |
        */
        if (
            $validated['action'] ===
            'process_request'
        ) {
            $updates['inspection_by'] =
                $user->id;

            $updates['inspection_at'] =
                now();

            /*
             * ID verification is the visible "Verified" milestone.
             * The same processing action records both the inspection
             * and the ID verification details.
             */
            $updates['id_verified_by'] =
                $user->id;

            $updates['id_verified_at'] =
                now();
        }

        if (
            $validated['action'] ===
            'approve_release'
        ) {
            /*
             * Eligibility is intentionally an internal verification
             * record, not a separate visible journey slot.
             */
            $updates['eligible_by'] =
                $user->id;

            $updates['eligible_at'] =
                now();
        }

        if (
            $validated['action'] ===
            'approve_release'
        ) {
            $updates['processed_at'] =
                now();
        }

        /*
        |--------------------------------------------------------------------------
        | Print Count
        |--------------------------------------------------------------------------
        */

        if (
            $validated['action'] ===
            'print_document'
        ) {
            $updates['print_count'] =
                ((int) $document->print_count) + 1;
        }

        $document->update(
            $updates
        );

        /*
        |--------------------------------------------------------------------------
        | New Values
        |--------------------------------------------------------------------------
        */

        $newValues = [
            'status' =>
                $document->status,

            'workflow_stage' =>
                $document->workflow_stage,

            'print_count' =>
                $document->print_count,
        ];

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

        $auditLog->log(
            action: $transition['audit_action'],
            module: 'Barangay Documents',
            record: $document,
            oldValues: $oldValues,
            newValues: $newValues,
            description:
                $transition['audit_description']
        );

        /*
        |--------------------------------------------------------------------------
        | PERFORMANCE: pre-generate the PDF right after approval
        |--------------------------------------------------------------------------
        |
        | Runs AFTER the response is sent, so the staff is not kept waiting and
        | the later Preview / Print click finds a ready PDF instead of paying
        | for the Gotenberg conversion. Failures are only logged; Preview /
        | Print will simply generate on demand as before.
        |
        */

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

        /*
        |--------------------------------------------------------------------------
        | Open Modern Print / Preview Page
        |--------------------------------------------------------------------------
        |
        | The PDF is passed to documents.print as base64 so the modern
        | print viewer can display it without returning a raw PDF response.
        |
        */

        if (
            $validated['action'] ===
            'print_document'
        ) {
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
        | Return to Document Process Journey
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route(
                'documents.show',
                $document
            )
            ->with(
                'success',
                $transition['message']
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Print Official Document
    |--------------------------------------------------------------------------
    |
    | This endpoint performs:
    |
    | APPROVED → PRINTED
    |
    | The PDF is generated only once.
    |
    | If a generated PDF already exists, it is reused.
    |
    */

    public function print(
        Request $request,
        DocumentRequest $document,
        AuditLogService $auditLog,
        OfficialDocumentTemplateService $templateService,
        DocumentConversionService $conversionService
    ): View|RedirectResponse {
        $user = $request->user();

        if (
            ! $user ||
            ! $user->canProcessDocuments()
        ) {
            abort(
                403,
                'You are not authorized to print document requests.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Printing is allowed from Approved onward
        |--------------------------------------------------------------------------
        */

        $allowedStages = [
            DocumentRequest::STAGE_APPROVED,
            DocumentRequest::STAGE_PRINTED,
            DocumentRequest::STAGE_SIGNED_SEALED,
            DocumentRequest::STAGE_RELEASED,
        ];

        if (
            ! in_array(
                $document->workflow_stage,
                $allowedStages,
                true
            )
        ) {
            return redirect()
                ->route(
                    'documents.show',
                    $document
                )
                ->withErrors([
                    'workflow' =>
                        'This document must be approved before it can be printed.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Get Existing PDF or Generate It
        |--------------------------------------------------------------------------
        |
        | This is the second major performance improvement.
        |
        | Once the PDF exists, printing again does NOT invoke Gotenberg.
        |
        */

        $generatedPdfPath =
            $this->getOrGeneratePdf(
                $document,
                $templateService,
                $conversionService
            );

        /*
        |--------------------------------------------------------------------------
        | Validate PDF BEFORE Recording Print
        |--------------------------------------------------------------------------
        */

        if (
            ! $generatedPdfPath ||
            ! is_file($generatedPdfPath)
        ) {
            return redirect()
                ->route(
                    'documents.show',
                    $document
                )
                ->withErrors([
                    'workflow' =>
                        'The official PDF document could not be generated successfully. The document was not marked as printed.',
                ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Approved → Printed
        |--------------------------------------------------------------------------
        |
        | Only the first actual print action changes the workflow stage.
        |
        */

        if (
            $document->workflow_stage ===
            DocumentRequest::STAGE_APPROVED
        ) {
            $oldValues = [
                'status' =>
                    $document->status,

                'workflow_stage' =>
                    $document->workflow_stage,

                'printed_by' =>
                    $document->printed_by,

                'printed_at' =>
                    $document->printed_at,

                'print_count' =>
                    $document->print_count,
            ];

            $document->update([
                'workflow_stage' =>
                    DocumentRequest::STAGE_PRINTED,

                'status' =>
                    'Completed',

                'printed_by' =>
                    $user->id,

                'printed_at' =>
                    now(),

                'print_count' =>
                    ((int) $document->print_count) + 1,
            ]);

            $document->refresh();

            $newValues = [
                'status' =>
                    $document->status,

                'workflow_stage' =>
                    $document->workflow_stage,

                'printed_by' =>
                    $document->printed_by,

                'printed_at' =>
                    $document->printed_at,

                'print_count' =>
                    $document->print_count,
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

        /*
        |--------------------------------------------------------------------------
        | Return Cached / Generated PDF Inline
        |--------------------------------------------------------------------------
        */

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
    |
    | Returns an existing PDF when available.
    |
    | Otherwise:
    |
    | DOCX template
    |      ↓
    | Personalized DOCX
    |      ↓
    | Gotenberg
    |      ↓
    | PDF
    |      ↓
    | Save path in document_requests
    |
    */

    private function getOrGeneratePdf(
        DocumentRequest $document,
        OfficialDocumentTemplateService $templateService,
        DocumentConversionService $conversionService
    ): ?string {
        if (
            $document->generatedPdfExists() &&
            ! $this->clearancePdfNeedsRegeneration($document)
        ) {
            return $document->getGeneratedPdfAbsolutePath();
        }

        $requestedAt = time();

        /*
         * A lock stops a double-click (or the background pre-generation
         * racing with a manual Preview) from running the slow conversion
         * twice for the same document. The second caller waits, then
         * simply reuses the PDF the first one produced.
         */
        $generate = function () use (
            $document,
            $templateService,
            $conversionService,
            $requestedAt
        ): ?string {
            $document->refresh();

            // Another request finished generating while we waited for the lock.
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

            if (! is_file($generatedPdfPath)) {
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
    |
    | A Barangay Clearance PDF generated before the one-page fix may still
    | be stored with two pages. Only that case is regenerated; every other
    | existing PDF is reused exactly as before.
    |
    */

    private function clearancePdfNeedsRegeneration(
        DocumentRequest $document
    ): bool {
        if ($document->document_type !== 'Barangay Clearance') {
            return false;
        }

        $path = $document->getGeneratedPdfAbsolutePath();

        if (! $path || ! is_file($path)) {
            return false;
        }

        $pdf = (string) file_get_contents($path);

        $isMultiPage = preg_match_all(
            '/\/Type\s*\/Page\b/',
            $pdf
        ) > 1;

        if (! $isMultiPage) {
            return false;
        }

        /*
         * Regenerate at most ONCE per document. Without this guard a
         * clearance that still cannot fit one page would be rebuilt
         * (up to 4 slow conversions) on every single Preview / Print.
         */
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
    |
    | Example:
    |
    | Absolute:
    | C:\Users\Administrator\BIS - SMART\storage\app\private\
    | generated-documents\BR-2026-ABC123.pdf
    |
    | Stored in database:
    | generated-documents/BR-2026-ABC123.pdf
    |
    */

    private function relativePrivateStoragePath(
        string $absolutePath
    ): string {
        $privateStoragePath = storage_path(
            'app/private'
        );

        $normalizedAbsolutePath =
            str_replace(
                '\\',
                '/',
                $absolutePath
            );

        $normalizedPrivateStoragePath =
            str_replace(
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

        /*
        |--------------------------------------------------------------------------
        | Fallback
        |--------------------------------------------------------------------------
        |
        | This protects the application if the conversion service returns
        | a path that is already relative.
        |
        */

        return ltrim(
            $normalizedAbsolutePath,
            '/'
        );
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

        if (
            ! in_array(
                $document->workflow_stage,
                $allowedStages,
                true
            )
        ) {
            return back()->withErrors([
                'workflow' =>
                    'This request can no longer be declined as a third-party request.',
            ]);
        }

        $reason = $request->input(
            'decline_reason',
            'Resident must personally appear and present a valid ID. Third-party release is not allowed.'
        );

        /*
        |--------------------------------------------------------------------------
        | Old Values
        |--------------------------------------------------------------------------
        */

        $oldValues = [
            'status' =>
                $document->status,

            'workflow_stage' =>
                $document->workflow_stage,

            'decline_reason' =>
                $document->decline_reason,
        ];

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $document->update([
            'workflow_stage' =>
                DocumentRequest::STAGE_THIRD_PARTY_DECLINED,

            'status' =>
                'Disapproved',

            'decline_reason' =>
                $reason,

            'remarks' =>
                $reason,

            'processed_at' =>
                now(),

            'inspection_by' =>
                $userId,

            'inspection_at' =>
                now(),
        ]);

        /*
        |--------------------------------------------------------------------------
        | New Values
        |--------------------------------------------------------------------------
        */

        $newValues = [
            'status' =>
                $document->status,

            'workflow_stage' =>
                $document->workflow_stage,

            'decline_reason' =>
                $document->decline_reason,
        ];

        /*
        |--------------------------------------------------------------------------
        | Audit
        |--------------------------------------------------------------------------
        */

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
            ->route(
                'documents.show',
                $document
            )
            ->with(
                'success',
                'Request marked as not released because the resident was not personally present.'
            );
    }

    private function generateRequestCode(): string
    {
        do {
            $code =
                'BR-' .
                now()->format('Y') .
                '-' .
                Str::upper(
                    Str::random(6)
                );
        } while (
            DocumentRequest::where(
                'request_code',
                $code
            )->exists()
        );

        return $code;
    }
}
