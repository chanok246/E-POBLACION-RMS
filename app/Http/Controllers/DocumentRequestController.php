<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateOfficialDocumentPdfJob;
use App\Models\DocumentRequest;
use App\Models\Resident;
use App\Services\AuditLogService;
use App\Services\DocumentConversionService;
use App\Services\OfficialDocumentTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    /*
    |--------------------------------------------------------------------------
    | Barangay Documents Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $statusMap = [
            'pending' => 'Pending',
            'processing' => 'Processing',
            'approved' => 'Approved',
            'completed' => 'Completed',
            'released' => 'Released',
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
                    $query->where(
                        'status',
                        $statusMap[$request->status]
                    );
                }
            )
            ->latest('requested_at')
            ->get();

        return view(
            'documents.index',
            compact('documents')
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        $residents = Resident::query()
            ->where('status', 'Active')
            ->with([
                'householdMemberships.resident',
                'headedHouseholds',
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
                $householdMembers = $household
                    ->members()
                    ->with('resident')
                    ->get();

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

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request,
        AuditLogService $auditLog
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Base Validation
        |--------------------------------------------------------------------------
        */

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

            /*
            |--------------------------------------------------------------------------
            | Solo Parent Fields
            |--------------------------------------------------------------------------
            */

            'document_details.child_resident_ids' => [
                'nullable',
                'array',
            ],

            'document_details.child_resident_ids.*' => [
                'required',
                'string',
                'max:100',
            ],

            /*
            |--------------------------------------------------------------------------
            | Cutting Trees Fields
            |--------------------------------------------------------------------------
            |
            | These fields are explicitly validated so Laravel will preserve
            | them inside document_details.
            |
            */

            'document_details.permit_to_cut_no' => [
                'required_if:document_type,Cutting Trees',
                'nullable',
                'string',
                'max:100',
            ],

            'document_details.permit_to_cut_date' => [
                'required_if:document_type,Cutting Trees',
                'nullable',
                'date',
            ],

            'document_details.permit_issued_by' => [
                'required_if:document_type,Cutting Trees',
                'nullable',
                'string',
                'max:255',
            ],

            'document_details.tree_count' => [
                'required_if:document_type,Cutting Trees',
                'nullable',
                'integer',
                'min:1',
            ],

            'document_details.tree_type' => [
                'required_if:document_type,Cutting Trees',
                'nullable',
                'string',
                'max:1000',
            ],

            'document_details.location' => [
                'required_if:document_type,Cutting Trees',
                'nullable',
                'string',
                'max:1000',
            ],

            'document_details.location_description' => [
                'required_if:document_type,Cutting Trees',
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
                'required_if:document_type,Cutting Trees',
                'nullable',
                'string',
                'max:1000',
            ],

            /*
            |--------------------------------------------------------------------------
            | Late Register Fields
            |--------------------------------------------------------------------------
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
                /*
                 * Trim normal string values.
                 *
                 * Arrays such as child_resident_ids are preserved.
                 */
                return is_string($value)
                    ? trim($value)
                    : $value;
            })
            ->filter(function ($value) {
                /*
                 * Remove empty scalar values but preserve arrays.
                 */
                if (is_array($value)) {
                    return ! empty($value);
                }

                return $value !== null &&
                    $value !== '';
            })
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Cutting Trees Validation and Normalization
        |--------------------------------------------------------------------------
        |
        | The Cutting Trees request is kept as its own structured document
        | details set.
        |
        | This supports:
        |
        |   tree_count = 1
        |   tree_count = 2
        |   tree_count = 3
        |   etc.
        |
        | Multiple tree types may also be entered, for example:
        |
        |   Acacia, Mahogany
        |
        | The actual wording and placement of these values in the official
        | certificate will be handled by OfficialDocumentTemplateService.
        |
        */

        if (
            $validated['document_type'] ===
            'Cutting Trees'
        ) {
            $permitToCutNo = trim(
                (string) (
                    $documentDetails['permit_to_cut_no'] ?? ''
                )
            );

            $permitToCutDate = trim(
                (string) (
                    $documentDetails['permit_to_cut_date'] ?? ''
                )
            );

            $permitIssuedBy = trim(
                (string) (
                    $documentDetails['permit_issued_by'] ?? ''
                )
            );

            $treeCount = $documentDetails['tree_count'] ?? null;

            $treeType = trim(
                (string) (
                    $documentDetails['tree_type'] ?? ''
                )
            );

            $location = trim(
                (string) (
                    $documentDetails['location'] ?? ''
                )
            );

            $locationDescription = trim(
                (string) (
                    $documentDetails['location_description'] ?? ''
                )
            );

            $treeOwner = trim(
                (string) (
                    $documentDetails['tree_owner'] ?? ''
                )
            );

            $cutterName = trim(
                (string) (
                    $documentDetails['cutter_name'] ?? ''
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Additional Safety Validation
            |--------------------------------------------------------------------------
            */

            if ($permitToCutNo === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.permit_to_cut_no' =>
                            'The Permit to Cut number is required.',
                    ]);
            }

            if ($permitToCutDate === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.permit_to_cut_date' =>
                            'The Permit to Cut date is required.',
                    ]);
            }

            if ($permitIssuedBy === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.permit_issued_by' =>
                            'The office that issued the Permit to Cut is required.',
                    ]);
            }

            if (
                $treeCount === null ||
                $treeCount === '' ||
                ! is_numeric($treeCount) ||
                (int) $treeCount < 1
            ) {
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
                            'The location description or landmark is required.',
                    ]);
            }

            if ($cutterName === '') {
                return back()
                    ->withInput()
                    ->withErrors([
                        'document_details.cutter_name' =>
                            'The name of the person who cut the tree is required.',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Rebuild Cutting Trees Details
            |--------------------------------------------------------------------------
            |
            | Only Cutting Trees fields are stored here.
            |
            | The selected resident remains the DocumentRequest resident_id.
            | OfficialDocumentTemplateService will use that resident as the
            | requester/registered resident when generating the certificate.
            |
            */

            $documentDetails = [
                'permit_to_cut_no' =>
                    $permitToCutNo,

                'permit_to_cut_date' =>
                    $permitToCutDate,

                'permit_issued_by' =>
                    $permitIssuedBy,

                'tree_count' =>
                    (int) $treeCount,

                'tree_type' =>
                    $treeType,

                'location' =>
                    $location,

                'location_description' =>
                    $locationDescription,

                'tree_owner' =>
                    $treeOwner !== ''
                        ? $treeOwner
                        : null,

                'cutter_name' =>
                    $cutterName,
            ];
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
            |--------------------------------------------------------------------------
            | Collect Household Members
            |--------------------------------------------------------------------------
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
            |--------------------------------------------------------------------------
            | Collect Members from Headed Households
            |--------------------------------------------------------------------------
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
            |--------------------------------------------------------------------------
            | Verify Selected Family Members
            |--------------------------------------------------------------------------
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
            |--------------------------------------------------------------------------
            | Verify Active Family Members
            |--------------------------------------------------------------------------
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

            /*
            |--------------------------------------------------------------------------
            | Save Only Validated Child IDs
            |--------------------------------------------------------------------------
            */

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
        */

        if (
            $validated['document_type'] ===
            'Certificate of Late Register'
        ) {
            $birthplace = trim(
                (string) (
                    $documentDetails['birthplace'] ?? ''
                )
            );

            $fatherName = trim(
                (string) (
                    $documentDetails['father_name'] ?? ''
                )
            );

            $motherName = trim(
                (string) (
                    $documentDetails['mother_name'] ?? ''
                )
            );

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
            |--------------------------------------------------------------------------
            | Save Only Late Register Fields
            |--------------------------------------------------------------------------
            */

            $documentDetails = [
                'birthplace' =>
                    $birthplace,

                'father_name' =>
                    $fatherName,

                'mother_name' =>
                    $motherName,
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

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

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
    | Preview does NOT change the workflow.
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

        try {
            $generatedPdfPath =
                $this->getOrGeneratePdf(
                    $document,
                    $templateService,
                    $conversionService
                );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route(
                    'documents.show',
                    $document
                )
                ->withErrors([
                    'workflow' =>
                        $exception->getMessage(),
                ]);
        }

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
                        'The official PDF document could not be generated successfully.',
                ]);
        }

        return view(
            'documents.print',
            [
                'document' =>
                    $document,

                'pdfData' =>
                    base64_encode(
                        (string) file_get_contents(
                            $generatedPdfPath
                        )
                    ),

                'mode' =>
                    'preview',
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Advance Document Workflow
    |--------------------------------------------------------------------------
    |
    | SIMPLIFIED USER JOURNEY
    |
    | 1. Process Request
    | 2. Print Document
    | 3. Confirm Signature & Seal
    | 4. Release Document
    |
    | Internal records for inspection, ID verification,
    | eligibility and approval are still saved.
    |--------------------------------------------------------------------------
    */

    public function advance(
        Request $request,
        DocumentRequest $document,
        AuditLogService $auditLog,
        OfficialDocumentTemplateService $templateService,
        DocumentConversionService $conversionService
    ): RedirectResponse {
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

        /*
        |--------------------------------------------------------------------------
        | IMPORTANT
        |--------------------------------------------------------------------------
        | Only the simplified actions are exposed to the UI.
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'action' => [
                'required',
                Rule::in([
                    'process_request',
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
        | Third-Party Decline
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
        | ACTION 1 - Process Request
        |--------------------------------------------------------------------------
        */

        if (
            $validated['action'] ===
            'process_request'
        ) {
            if (
                $document->workflow_stage !==
                DocumentRequest::STAGE_REQUESTED
            ) {
                return back()
                    ->withErrors([
                        'workflow' =>
                            'This request has already been processed or is no longer at the initial stage.',
                    ]);
            }

            $now = now();

            $oldValues = [
                'status' =>
                    $document->status,

                'workflow_stage' =>
                    $document->workflow_stage,

                'inspection_by' =>
                    $document->inspection_by,

                'inspection_at' =>
                    $document->inspection_at,

                'id_verified_by' =>
                    $document->id_verified_by,

                'id_verified_at' =>
                    $document->id_verified_at,

                'eligible_by' =>
                    $document->eligible_by,

                'eligible_at' =>
                    $document->eligible_at,

                'approved_by' =>
                    $document->approved_by,

                'approved_at' =>
                    $document->approved_at,
            ];

            $document->update([
                /*
                |--------------------------------------------------------------------------
                | Internal Processing Records
                |--------------------------------------------------------------------------
                */

                'inspection_by' =>
                    $user->id,

                'inspection_at' =>
                    $now,

                'id_verified_by' =>
                    $user->id,

                'id_verified_at' =>
                    $now,

                'eligible_by' =>
                    $user->id,

                'eligible_at' =>
                    $now,

                'approved_by' =>
                    $user->id,

                'approved_at' =>
                    $now,

                /*
                |--------------------------------------------------------------------------
                | Visible Workflow
                |--------------------------------------------------------------------------
                */

                'workflow_stage' =>
                    DocumentRequest::STAGE_APPROVED,

                'status' =>
                    'Approved',

                'processed_at' =>
                    $now,
            ]);

            $newValues = [
                'status' =>
                    $document->status,

                'workflow_stage' =>
                    $document->workflow_stage,

                'inspection_by' =>
                    $document->inspection_by,

                'inspection_at' =>
                    $document->inspection_at,

                'id_verified_by' =>
                    $document->id_verified_by,

                'id_verified_at' =>
                    $document->id_verified_at,

                'eligible_by' =>
                    $document->eligible_by,

                'eligible_at' =>
                    $document->eligible_at,

                'approved_by' =>
                    $document->approved_by,

                'approved_at' =>
                    $document->approved_at,
            ];

            $auditLog->log(
                action: 'Processed & Approved',
                module: 'Barangay Documents',
                record: $document,
                oldValues: $oldValues,
                newValues: $newValues,
                description:
                    'Document request was processed in one workflow action. Inspection, resident ID verification, eligibility, and approval were recorded.'
            );

            /*
            |--------------------------------------------------------------------------
            | Warm Up Official PDF
            |--------------------------------------------------------------------------
            */

            GenerateOfficialDocumentPdfJob::dispatch(
                $document->id
            )->afterResponse();

            return redirect()
                ->route(
                    'documents.show',
                    $document
                )
                ->with(
                    'success',
                    'Document processed and approved successfully. The official document is now ready for printing.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | ACTION 2 - Print Document
        |--------------------------------------------------------------------------
        */

        if (
            $validated['action'] ===
            'print_document'
        ) {
            if (
                $document->workflow_stage !==
                DocumentRequest::STAGE_APPROVED
            ) {
                return back()
                    ->withErrors([
                        'workflow' =>
                            'The document must be approved before it can be printed.',
                    ]);
            }

            try {
                $generatedPdfPath =
                    $this->getOrGeneratePdf(
                        $document,
                        $templateService,
                        $conversionService
                    );
            } catch (RuntimeException $exception) {
                return back()
                    ->withErrors([
                        'workflow' =>
                            $exception->getMessage(),
                    ]);
            }

            if (
                ! $generatedPdfPath ||
                ! is_file($generatedPdfPath)
            ) {
                return back()
                    ->withErrors([
                        'workflow' =>
                            'The official PDF document was not generated successfully. The document was not marked as printed.',
                    ]);
            }

            $oldValues = [
                'status' =>
                    $document->status,

                'workflow_stage' =>
                    $document->workflow_stage,

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

            $newValues = [
                'status' =>
                    $document->status,

                'workflow_stage' =>
                    $document->workflow_stage,

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
                    'Official document was generated from the registered barangay template and printing was recorded.'
            );

            return redirect()
                ->route(
                    'documents.print',
                    $document
                )
                ->with(
                    'success',
                    'Official document generated successfully. The request is now marked as printed.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | ACTION 3 - Confirm Signature & Seal
        |--------------------------------------------------------------------------
        */

        if (
            $validated['action'] ===
            'confirm_signed_sealed'
        ) {
            if (
                $document->workflow_stage !==
                DocumentRequest::STAGE_PRINTED
            ) {
                return back()
                    ->withErrors([
                        'workflow' =>
                            'The document must be printed before the signature and barangay seal can be confirmed.',
                    ]);
            }

            $oldValues = [
                'status' =>
                    $document->status,

                'workflow_stage' =>
                    $document->workflow_stage,
            ];

            $document->update([
                'workflow_stage' =>
                    DocumentRequest::STAGE_SIGNED_SEALED,

                'status' =>
                    'Completed',

                'signed_sealed_by' =>
                    $user->id,

                'signed_sealed_at' =>
                    now(),
            ]);

            $newValues = [
                'status' =>
                    $document->status,

                'workflow_stage' =>
                    $document->workflow_stage,
            ];

            $auditLog->log(
                action: 'Signed and Sealed',
                module: 'Barangay Documents',
                record: $document,
                oldValues: $oldValues,
                newValues: $newValues,
                description:
                    'Physical signature and barangay seal were confirmed.'
            );

            return redirect()
                ->route(
                    'documents.show',
                    $document
                )
                ->with(
                    'success',
                    'Signature and barangay seal confirmed successfully. The document is ready for release.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | ACTION 4 - Release Document
        |--------------------------------------------------------------------------
        */

        if (
            $validated['action'] ===
            'release_document'
        ) {
            if (
                $document->workflow_stage !==
                DocumentRequest::STAGE_SIGNED_SEALED
            ) {
                return back()
                    ->withErrors([
                        'workflow' =>
                            'The document must have its signature and barangay seal confirmed before release.',
                    ]);
            }

            $oldValues = [
                'status' =>
                    $document->status,

                'workflow_stage' =>
                    $document->workflow_stage,
            ];

            $document->update([
                'workflow_stage' =>
                    DocumentRequest::STAGE_RELEASED,

                'status' =>
                    'Released',

                'released_by' =>
                    $user->id,

                'released_at' =>
                    now(),
            ]);

            $newValues = [
                'status' =>
                    $document->status,

                'workflow_stage' =>
                    $document->workflow_stage,
            ];

            $auditLog->log(
                action: 'Released',
                module: 'Barangay Documents',
                record: $document,
                oldValues: $oldValues,
                newValues: $newValues,
                description:
                    'Document was released to the resident.'
            );

            return redirect()
                ->route(
                    'documents.show',
                    $document
                )
                ->with(
                    'success',
                    'Document successfully released to the resident.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Safety Fallback
        |--------------------------------------------------------------------------
        */

        return back()
            ->withErrors([
                'workflow' =>
                    'The requested workflow action could not be completed.',
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Print Official Document
    |--------------------------------------------------------------------------
    |
    | This method only displays the already-generated PDF.
    |
    | The workflow is marked PRINTED inside advance()
    | before this page is opened.
    |--------------------------------------------------------------------------
    */

    public function print(
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
                'You are not authorized to print document requests.'
            );
        }

        $allowedStages = [
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
                        'This document must be approved and processed before it can be printed.',
                ]);
        }

        try {
            $generatedPdfPath =
                $this->getOrGeneratePdf(
                    $document,
                    $templateService,
                    $conversionService
                );
        } catch (RuntimeException $exception) {
            return redirect()
                ->route(
                    'documents.show',
                    $document
                )
                ->withErrors([
                    'workflow' =>
                        $exception->getMessage(),
                ]);
        }

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
                        'The official PDF document could not be generated successfully.',
                ]);
        }

        return view(
            'documents.print',
            [
                'document' =>
                    $document,

                'pdfData' =>
                    base64_encode(
                        (string) file_get_contents(
                            $generatedPdfPath
                        )
                    ),

                'mode' =>
                    'print',
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
        /*
        |--------------------------------------------------------------------------
        | Existing PDF
        |--------------------------------------------------------------------------
        */

        if (
            $document->generatedPdfExists()
        ) {
            return $document->getGeneratedPdfAbsolutePath();
        }

        /*
        |--------------------------------------------------------------------------
        | Generate Personalized DOCX
        |--------------------------------------------------------------------------
        */

        $generatedDocumentPath =
            $templateService->generate(
                $document
            );

        /*
        |--------------------------------------------------------------------------
        | Convert DOCX -> PDF
        |--------------------------------------------------------------------------
        */

        $generatedPdfPath =
            $conversionService->convertDocxToPdf(
                $generatedDocumentPath
            );

        /*
        |--------------------------------------------------------------------------
        | Validate Generated PDF
        |--------------------------------------------------------------------------
        */

        if (
            ! is_file(
                $generatedPdfPath
            )
        ) {
            return null;
        }

        /*
        |--------------------------------------------------------------------------
        | Save Relative Private Storage Path
        |--------------------------------------------------------------------------
        */

        $document->update([
            'generated_pdf_path' =>
                $this->relativePrivateStoragePath(
                    $generatedPdfPath
                ),
        ]);

        $document->refresh();

        return $document->getGeneratedPdfAbsolutePath();
    }

    /*
    |--------------------------------------------------------------------------
    | Relative Private Storage Path
    |--------------------------------------------------------------------------
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
            return back()
                ->withErrors([
                    'workflow' =>
                        'This request can no longer be declined as a third-party request.',
                ]);
        }

        $reason = trim(
            (string) $request->input(
                'decline_reason',
                ''
            )
        );

        if ($reason === '') {
            $reason =
                'Resident must personally appear and present a valid ID. Third-party release is not allowed.';
        }

        $oldValues = [
            'status' =>
                $document->status,

            'workflow_stage' =>
                $document->workflow_stage,

            'decline_reason' =>
                $document->decline_reason,
        ];

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

        $newValues = [
            'status' =>
                $document->status,

            'workflow_stage' =>
                $document->workflow_stage,

            'decline_reason' =>
                $document->decline_reason,
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
            ->route(
                'documents.show',
                $document
            )
            ->with(
                'success',
                'Request marked as not released because the resident was not personally present.'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate Request Code
    |--------------------------------------------------------------------------
    */

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
