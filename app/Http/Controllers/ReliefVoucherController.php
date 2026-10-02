<?php

namespace App\Http\Controllers;

use App\Models\DisasterEvent;
use App\Models\Household;
use App\Models\ReliefAssistanceOperation;
use App\Models\ReliefOperationBeneficiary;
use App\Models\ReliefVoucher;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReliefVoucherController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | RELIEF ASSISTANCE WORKFLOW
    |--------------------------------------------------------------------------
    |
    | PHASE 1
    |
    | 1. Create Relief Assistance Operation
    | 2. Prepare Masterlist
    | 3. Filter resident records
    | 4. Preview and Print Masterlist
    | 5. BHW / Staff conducts physical verification
    | 6. Encoder opens the same operation
    | 7. Matching resident records appear automatically
    | 8. Encoder selects actual beneficiaries individually
    | 9. Confirm actual beneficiaries
    |
    | PHASE 2 - VOUCHER-BASED ASSISTANCE
    |
    | 10. Preview confirmed beneficiaries
    | 11. Generate unique vouchers
    | 12. Generate unique QR codes through voucher data
    | 13. Print vouchers
    | 14. Distribution
    | 15. Scan QR
    | 16. QR automatically verifies and claims
    |
    | NON-VOUCHER ASSISTANCE
    |
    | Service / Social Assistance activities such as medical missions,
    | dental missions, vaccination, and consultation use the resident
    | participant/beneficiary workflow but do NOT generate vouchers.
    |
    |--------------------------------------------------------------------------
    */


    /*
    |--------------------------------------------------------------------------
    | CURRENT ASSISTANCE MODES
    |--------------------------------------------------------------------------
    |
    | These values MUST match the current create.blade.php.
    |
    */

    private function assistanceTypes(): array
    {
        return [
            'in_kind' =>
                'Physical relief goods such as Family Food Packs, rice, hygiene kits, water, or other relief goods.',

            'cash' =>
                'Emergency cash aid, educational assistance, disaster financial assistance, or other monetary support.',

            'priority' =>
                'Targeted assistance for groups such as senior citizens, PWDs, students, or indigent families.',

            'service' =>
                'Services such as medical missions, dental missions, vaccination, consultation, or similar activities.',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | LEGACY ASSISTANCE TYPES
    |--------------------------------------------------------------------------
    |
    | Existing database records may still contain the old assistance_type
    | values. They are preserved so existing operations do not suddenly
    | stop working.
    |
    */

    private function legacyAssistanceTypes(): array
    {
        return [
            'In-Kind Relief Distribution' =>
                'Physical relief goods such as Family Food Packs or other goods.',

            'Emergency Cash Aid' =>
                'Emergency financial assistance distributed as cash.',

            'Financial Assistance' =>
                'Financial assistance for qualified residents or families.',

            'Senior Citizen Assistance' =>
                'Assistance intended for qualified senior citizens.',

            'PWD Assistance' =>
                'Assistance intended for qualified persons with disabilities.',

            'Indigent Family Assistance' =>
                'Assistance intended for qualified indigent families.',

            'Medical Mission' =>
                'Medical service or consultation activity without voucher generation.',

            'Other Assistance' =>
                'Other barangay assistance activity.',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | VOUCHER-BASED ASSISTANCE MODES
    |--------------------------------------------------------------------------
    |
    | Current modes that may generate ReliefVoucher records.
    |
    */

    private function voucherAssistanceTypes(): array
    {
        return [
            'in_kind',
            'cash',
            'priority',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | LEGACY VOUCHER-BASED ASSISTANCE TYPES
    |--------------------------------------------------------------------------
    |
    | Existing operations created under the previous workflow may still
    | contain these values.
    |
    */

    private function legacyVoucherAssistanceTypes(): array
    {
        return [
            'In-Kind Relief Distribution',
            'Emergency Cash Aid',
            'Financial Assistance',
            'Senior Citizen Assistance',
            'PWD Assistance',
            'Indigent Family Assistance',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | NON-VOUCHER ASSISTANCE
    |--------------------------------------------------------------------------
    */

    private function nonVoucherAssistanceTypes(): array
    {
        return [
            'service',
            'Medical Mission',
            'Other Assistance',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK IF OPERATION USES VOUCHERS
    |--------------------------------------------------------------------------
    */

    private function operationUsesVouchers(
        ReliefAssistanceOperation $operation
    ): bool {
        $assistanceType =
            (string) $operation->assistance_type;

        return in_array(
            $assistanceType,
            $this->voucherAssistanceTypes(),
            true
        )
        ||
        in_array(
            $assistanceType,
            $this->legacyVoucherAssistanceTypes(),
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK IF CURRENT ASSISTANCE TYPE IS VALID
    |--------------------------------------------------------------------------
    */

    private function isCurrentAssistanceType(
        string $assistanceType
    ): bool {
        return array_key_exists(
            $assistanceType,
            $this->assistanceTypes()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK IF LEGACY ASSISTANCE TYPE IS VALID
    |--------------------------------------------------------------------------
    */

    private function isLegacyAssistanceType(
        string $assistanceType
    ): bool {
        return array_key_exists(
            $assistanceType,
            $this->legacyAssistanceTypes()
        );
    }


    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $operations =
            ReliefAssistanceOperation::query()
                ->with('disasterEvent')
                ->withCount([
                    'vouchers',
                ])
                ->latest()
                ->paginate(10);

        $recentVouchers =
            ReliefVoucher::query()
                ->with([
                    'household.householdHead',
                    'operation',
                    'beneficiary.resident',
                ])
                ->latest()
                ->take(10)
                ->get();

        return view(
            'relief-vouchers.index',
            compact(
                'operations',
                'recentVouchers'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE RELIEF OPERATION
    |--------------------------------------------------------------------------
    */

    public function createOperation(): View
    {
        $disasterEvents =
            DisasterEvent::query()
                ->latest()
                ->get();

        $assistanceTypes =
            $this->assistanceTypes();

        return view(
            'assistance-campaigns.create',
            compact(
                'disasterEvents',
                'assistanceTypes'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STORE RELIEF OPERATION
    |--------------------------------------------------------------------------
    */

    public function storeOperation(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'operation_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                /*
                 * IMPORTANT:
                 *
                 * These values match create.blade.php:
                 *
                 * in_kind
                 * cash
                 * priority
                 * service
                 */
                'assistance_type' => [
                    'required',
                    'string',
                    'max:100',
                    function (
                        string $attribute,
                        mixed $value,
                        \Closure $fail
                    ) {
                        if (
                            !$this->isCurrentAssistanceType(
                                (string) $value
                            )
                        ) {
                            $fail(
                                'Please select a valid assistance type.'
                            );
                        }
                    },
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'assistance_description' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                /*
                 * IMPORTANT:
                 *
                 * create.blade.php sends target_group.
                 */
                'target_group' => [
                    'required',
                    'string',
                    'in:all,household_heads,students,senior_citizens',
                ],

                'source_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'source_reference' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'assistance_received_date' => [
                    'nullable',
                    'date',
                ],

                /*
                 * Blade allows decimal quantities.
                 */
                'quantity_received' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'quantity_unit' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'distribution_date' => [
                    'nullable',
                    'date',
                ],

                'distribution_time' => [
                    'nullable',
                    'date_format:H:i',
                ],

                'distribution_location' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'allocation_notes' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'disaster_event_id' => [
                    'nullable',
                    'exists:disaster_events,id',
                ],
            ]);

        $quantityReceived =
            (float) (
                $validated['quantity_received']
                ?? 0
            );

        $operation =
            ReliefAssistanceOperation::create([
                'operation_code' =>
                    $this->generateOperationCode(),

                'operation_name' =>
                    $validated['operation_name'],

                'description' =>
                    $validated['description']
                    ?? null,

                'assistance_type' =>
                    $validated['assistance_type'],

                'assistance_description' =>
                    $validated['assistance_description']
                    ?? null,

                'disaster_event_id' =>
                    $validated['disaster_event_id']
                    ?? null,

                'source_name' =>
                    $validated['source_name']
                    ?? null,

                'source_reference' =>
                    $validated['source_reference']
                    ?? null,

                'assistance_received_date' =>
                    $validated['assistance_received_date']
                    ?? null,

                'quantity_received' =>
                    $quantityReceived,

                'quantity_unit' =>
                    $validated['quantity_unit']
                    ?? null,

                'quantity_allocated' =>
                    0,

                'allocation_basis' =>
                    null,

                'allocation_notes' =>
                    $validated['allocation_notes']
                    ?? null,

                'distribution_date' =>
                    $validated['distribution_date']
                    ?? null,

                'distribution_time' =>
                    $validated['distribution_time']
                    ?? null,

                'distribution_location' =>
                    $validated['distribution_location']
                    ?? null,

                /*
                 * Database column:
                 * beneficiary_filter
                 *
                 * Form field:
                 * target_group
                 */
                'beneficiary_filter' =>
                    $validated['target_group'],

                'purok_filter' =>
                    null,

                'status' =>
                    'draft',

                'created_by' =>
                    Auth::id(),

                'updated_by' =>
                    Auth::id(),
            ]);

        return redirect()
            ->route(
                'relief-assistance.masterlist',
                [
                    'operation_id' =>
                        $operation->id,
                ]
            )
            ->with(
                'success',
                'Relief assistance operation created successfully. You can now prepare the masterlist.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE OPERATION CODE
    |--------------------------------------------------------------------------
    */

    private function generateOperationCode(): string
    {
        do {
            $code =
                'RA-'
                . now()->format('Ymd')
                . '-'
                . strtoupper(
                    Str::random(5)
                );
        } while (
            ReliefAssistanceOperation::query()
                ->where(
                    'operation_code',
                    $code
                )
                ->exists()
        );

        return $code;
    }


    /*
    |--------------------------------------------------------------------------
    | OPERATION SHOW
    |--------------------------------------------------------------------------
    */

    public function operationShow(
        ReliefAssistanceOperation $operation
    ): View {
        $operation->load([
            'disasterEvent',
            'vouchers.household.householdHead',
            'vouchers.beneficiary.resident',
        ]);

        $generatedCount =
            $operation
                ->vouchers()
                ->where(
                    'status',
                    'generated'
                )
                ->count();

        $issuedCount =
            $operation
                ->vouchers()
                ->where(
                    'status',
                    'issued'
                )
                ->count();

        $distributedCount =
            $operation
                ->vouchers()
                ->where(
                    'status',
                    'distributed'
                )
                ->count();

        $claimedCount =
            $operation
                ->vouchers()
                ->where(
                    'status',
                    'claimed'
                )
                ->count();

        $cancelledCount =
            $operation
                ->vouchers()
                ->where(
                    'status',
                    'cancelled'
                )
                ->count();

        $remainingQuantity =
            max(
                0,
                (
                    (float) (
                        $operation->quantity_received
                        ?? 0
                    )
                    -
                    (float) (
                        $operation->quantity_allocated
                        ?? 0
                    )
                )
            );

        /*
         * beneficiary_status describes whether the resident was
         * confirmed/removed from the operation.
         */
        $confirmedCount =
            $operation
                ->beneficiaries()
                ->where(
                    'beneficiary_status',
                    'confirmed'
                )
                ->count();

        $encoderValidatedCount =
            $operation
                ->beneficiaries()
                ->whereNotNull(
                    'validated_at'
                )
                ->count();

        /*
         * assistance_status describes the assistance lifecycle.
         */
        $allocatedCount =
            $operation
                ->beneficiaries()
                ->where(
                    'assistance_status',
                    'allocated'
                )
                ->count();

        $voucherGeneratedCount =
            $operation
                ->beneficiaries()
                ->where(
                    'assistance_status',
                    'voucher_generated'
                )
                ->count();

        $usesVouchers =
            $this->operationUsesVouchers(
                $operation
            );

        $isNonVoucherOperation =
            !$usesVouchers;

        return view(
            'assistance-campaigns.show',
            compact(
                'operation',
                'generatedCount',
                'issuedCount',
                'distributedCount',
                'claimedCount',
                'cancelledCount',
                'remainingQuantity',
                'confirmedCount',
                'encoderValidatedCount',
                'allocatedCount',
                'voucherGeneratedCount',
                'usesVouchers',
                'isNonVoucherOperation'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT OPERATION
    |--------------------------------------------------------------------------
    */

    public function operationEdit(
        ReliefAssistanceOperation $operation
    ): View {
        $disasterEvents =
            DisasterEvent::query()
                ->latest()
                ->get();

        /*
         * Current options are supplied to the new workflow.
         *
         * Legacy operation values are also added so existing records
         * remain editable.
         */
        $assistanceTypes =
            $this->assistanceTypes()
            +
            $this->legacyAssistanceTypes();

        return view(
            'assistance-campaigns.edit',
            compact(
                'operation',
                'disasterEvents',
                'assistanceTypes'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE OPERATION
    |--------------------------------------------------------------------------
    */

    public function operationUpdate(
        Request $request,
        ReliefAssistanceOperation $operation
    ): RedirectResponse {
        $allowedAssistanceTypes =
            array_unique(
                array_merge(
                    array_keys(
                        $this->assistanceTypes()
                    ),
                    array_keys(
                        $this->legacyAssistanceTypes()
                    )
                )
            );

        $validated =
            $request->validate([
                'operation_name' => [
                    'required',
                    'string',
                    'max:255',
                ],

                /*
                 * Update remains compatible with existing operations
                 * that were created using the previous assistance_type
                 * values.
                 */
                'assistance_type' => [
                    'required',
                    'string',
                    'max:100',
                    function (
                        string $attribute,
                        mixed $value,
                        \Closure $fail
                    ) use (
                        $allowedAssistanceTypes
                    ) {
                        if (
                            !in_array(
                                (string) $value,
                                $allowedAssistanceTypes,
                                true
                            )
                        ) {
                            $fail(
                                'Please select a valid assistance type.'
                            );
                        }
                    },
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'assistance_description' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                /*
                 * The new edit form should use target_group.
                 *
                 * Legacy edit forms may not send it, so it is nullable
                 * here and the existing beneficiary_filter is preserved.
                 */
                'target_group' => [
                    'nullable',
                    'string',
                    'in:all,household_heads,students,senior_citizens',
                ],

                /*
                 * Compatibility if an older edit form still submits
                 * beneficiary_filter.
                 */
                'beneficiary_filter' => [
                    'nullable',
                    'string',
                    'in:all,household_heads,students,senior_citizens',
                ],

                'source_name' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'source_reference' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'assistance_received_date' => [
                    'nullable',
                    'date',
                ],

                'quantity_received' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'quantity_unit' => [
                    'nullable',
                    'string',
                    'max:100',
                ],

                'distribution_date' => [
                    'nullable',
                    'date',
                ],

                'distribution_time' => [
                    'nullable',
                    'date_format:H:i',
                ],

                'distribution_location' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'allocation_notes' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],

                'disaster_event_id' => [
                    'nullable',
                    'exists:disaster_events,id',
                ],
            ]);

        $newQuantityReceived =
            (float) (
                $validated['quantity_received']
                ?? 0
            );

        $activeVoucherQuantity =
            (float) $operation
                ->vouchers()
                ->where(
                    'status',
                    '!=',
                    'cancelled'
                )
                ->sum(
                    'allocated_quantity'
                );

        if (
            $newQuantityReceived > 0
            &&
            $activeVoucherQuantity >
                $newQuantityReceived
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'quantity_received' =>
                        'The new available quantity cannot be lower than the quantity already allocated to active vouchers.',
                ]);
        }

        /*
         * Prevent changing an existing voucher operation into a
         * non-voucher activity while active vouchers still exist.
         */
        $newAssistanceType =
            $validated['assistance_type'];

        if (
            !$this->isVoucherAssistanceType(
                $newAssistanceType
            )
            &&
            $activeVoucherQuantity > 0
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'assistance_type' =>
                        'This operation already has active vouchers. Its assistance type cannot be changed to a non-voucher activity until those vouchers are resolved.',
                ]);
        }

        /*
         * New form:
         * target_group
         *
         * Legacy form:
         * beneficiary_filter
         *
         * Existing saved filter is the final fallback.
         */
        $beneficiaryFilter =
            $validated['target_group']
            ??
            $validated['beneficiary_filter']
            ??
            $operation->beneficiary_filter
            ??
            'all';

        $operation->update([
            'operation_name' =>
                $validated['operation_name'],

            'description' =>
                $validated['description']
                ?? null,

            'assistance_type' =>
                $newAssistanceType,

            'assistance_description' =>
                $validated['assistance_description']
                ?? null,

            'disaster_event_id' =>
                $validated['disaster_event_id']
                ?? null,

            'source_name' =>
                $validated['source_name']
                ?? null,

            'source_reference' =>
                $validated['source_reference']
                ?? null,

            'assistance_received_date' =>
                $validated['assistance_received_date']
                ?? null,

            'quantity_received' =>
                $newQuantityReceived,

            'quantity_allocated' =>
                $activeVoucherQuantity,

            'quantity_unit' =>
                $validated['quantity_unit']
                ?? null,

            'allocation_notes' =>
                $validated['allocation_notes']
                ??
                $operation->allocation_notes,

            'distribution_date' =>
                $validated['distribution_date']
                ?? null,

            'distribution_time' =>
                $validated['distribution_time']
                ?? null,

            'distribution_location' =>
                $validated['distribution_location']
                ?? null,

            'beneficiary_filter' =>
                $beneficiaryFilter,

            'updated_by' =>
                Auth::id(),
        ]);

        return redirect()
            ->route(
                'relief-assistance.show',
                $operation
            )
            ->with(
                'success',
                'Relief assistance operation updated successfully.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK ASSISTANCE TYPE
    |--------------------------------------------------------------------------
    */

    private function isVoucherAssistanceType(
        string $assistanceType
    ): bool {
        return in_array(
            $assistanceType,
            $this->voucherAssistanceTypes(),
            true
        )
        ||
        in_array(
            $assistanceType,
            $this->legacyVoucherAssistanceTypes(),
            true
        );
    }


    /*
    |--------------------------------------------------------------------------
    | BENEFICIARY FILTER OPTIONS
    |--------------------------------------------------------------------------
    |
    | Only filters supported by the current Resident/Household data are
    | exposed:
    |
    | all
    | household_heads
    | students
    | senior_citizens
    |
    */

    private function beneficiaryFilterOptions(): array
    {
        return [
            'all' =>
                'All',

            'household_heads' =>
                'Household Heads',

            'students' =>
                'Students',

            'senior_citizens' =>
                'Senior Citizens',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD LEGACY HOUSEHOLD QUERY
    |--------------------------------------------------------------------------
    */

    private function buildBeneficiaryQuery(
        Request $request
    ): Builder {
        $search =
            trim(
                (string) (
                    $request->input(
                        'search'
                    )
                    ?? ''
                )
            );

        $purok =
            trim(
                (string) (
                    $request->input(
                        'purok'
                    )
                    ?? ''
                )
            );

        $filter =
            $request->input(
                'filter',
                'all'
            );

        $query =
            Household::query()
                ->where(
                    'status',
                    'Active'
                )
                ->with([
                    'householdHead',
                    'members.resident',
                ]);

        if ($search !== '') {
            $query->where(
                function (
                    Builder $builder
                ) use (
                    $search
                ) {
                    $builder
                        ->where(
                            'household_number',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'address',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhere(
                            'purok',
                            'like',
                            '%' . $search . '%'
                        )
                        ->orWhereHas(
                            'householdHead',
                            function (
                                Builder $headQuery
                            ) use (
                                $search
                            ) {
                                $headQuery->where(
                                    function (
                                        Builder $nameQuery
                                    ) use (
                                        $search
                                    ) {
                                        $nameQuery
                                            ->where(
                                                'first_name',
                                                'like',
                                                '%' . $search . '%'
                                            )
                                            ->orWhere(
                                                'middle_name',
                                                'like',
                                                '%' . $search . '%'
                                            )
                                            ->orWhere(
                                                'last_name',
                                                'like',
                                                '%' . $search . '%'
                                            );
                                    }
                                );
                            }
                        );
                }
            );
        }

        if ($purok !== '') {
            $query->where(
                'purok',
                $purok
            );
        }

        if (
            $filter ===
            'household_heads'
        ) {
            $query->whereNotNull(
                'household_head_id'
            );
        }

        if (
            $filter ===
            'students'
        ) {
            $query->whereHas(
                'members.resident',
                function (
                    Builder $residentQuery
                ) {
                    $residentQuery
                        ->where(
                            'status',
                            'Active'
                        )
                        ->where(
                            'occupation',
                            'Student'
                        );
                }
            );
        }

        if (
            $filter ===
            'senior_citizens'
        ) {
            $cutoff =
                now()->subYears(60);

            $query->whereHas(
                'members.resident',
                function (
                    Builder $residentQuery
                ) use (
                    $cutoff
                ) {
                    $residentQuery
                        ->where(
                            'status',
                            'Active'
                        )
                        ->whereDate(
                            'birth_date',
                            '<=',
                            $cutoff
                        );
                }
            );
        }

        return $query
            ->orderBy('purok')
            ->orderBy('household_number');
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD RESIDENT-LEVEL MASTERLIST RECORDS
    |--------------------------------------------------------------------------
    */

    private function buildMasterlistRecords(
        Request $request
    ) {
        $search =
            trim(
                (string) (
                    $request->input(
                        'search'
                    )
                    ?? ''
                )
            );

        $purok =
            trim(
                (string) (
                    $request->input(
                        'purok'
                    )
                    ?? ''
                )
            );

        $filter =
            $request->input(
                'filter',
                'all'
            );

        $filterOptions =
            $this->beneficiaryFilterOptions();

        if (
            !array_key_exists(
                $filter,
                $filterOptions
            )
        ) {
            $filter = 'all';
        }

        $households =
            Household::query()
                ->where(
                    'status',
                    'Active'
                )
                ->with([
                    'householdHead',
                    'members.resident',
                ])
                ->when(
                    $purok !== '',
                    function (
                        Builder $query
                    ) use (
                        $purok
                    ) {
                        $query->where(
                            'purok',
                            $purok
                        );
                    }
                )
                ->get();

        $records =
            collect();

        foreach (
            $households as $household
        ) {
            $residents =
                collect();

            foreach (
                $household->members
                as $member
            ) {
                if (
                    !$member->resident
                ) {
                    continue;
                }

                if (
                    $member->resident->status !==
                    'Active'
                ) {
                    continue;
                }

                $residents->push([
                    'resident' =>
                        $member->resident,

                    'relationship' =>
                        $member->relationship
                        ?? null,
                ]);
            }

            /*
             * Make sure the household head is included even if the
             * household_members table does not contain a separate
             * membership record for the head.
             */
            if (
                $household->householdHead
                &&
                $household->householdHead->status ===
                'Active'
            ) {
                $headAlreadyIncluded =
                    $residents->contains(
                        function (
                            array $record
                        ) use (
                            $household
                        ) {
                            return (int) (
                                $record['resident']->id
                                ?? 0
                            )
                            ===
                            (int) (
                                $household->household_head_id
                                ?? 0
                            );
                        }
                    );

                if (
                    !$headAlreadyIncluded
                ) {
                    $residents->push([
                        'resident' =>
                            $household->householdHead,

                        'relationship' =>
                            'Household Head',
                    ]);
                }
            }

            foreach (
                $residents as $residentRecord
            ) {
                $resident =
                    $residentRecord['resident'];

                $matchesFilter =
                    match ($filter) {
                        'household_heads' =>
                            (int) $resident->id
                            ===
                            (int) (
                                $household
                                    ->household_head_id
                                ?? 0
                            ),

                        'students' =>
                            strcasecmp(
                                trim(
                                    (string) (
                                        $resident->occupation
                                        ?? ''
                                    )
                                ),
                                'Student'
                            ) === 0,

                        'senior_citizens' =>
                            $resident->birth_date
                            &&
                            Carbon::parse(
                                $resident->birth_date
                            )->lte(
                                now()->subYears(60)
                            ),

                        default =>
                            true,
                    };

                if (
                    !$matchesFilter
                ) {
                    continue;
                }

                if (
                    $search !== ''
                ) {
                    $residentName =
                        trim(
                            implode(
                                ' ',
                                array_filter([
                                    $resident->first_name
                                        ?? null,

                                    $resident->middle_name
                                        ?? null,

                                    $resident->last_name
                                        ?? null,

                                    $resident->suffix
                                        ?? null,
                                ])
                            )
                        );

                    $householdHeadName =
                        '';

                    if (
                        $household->householdHead
                    ) {
                        $householdHeadName =
                            trim(
                                implode(
                                    ' ',
                                    array_filter([
                                        $household
                                            ->householdHead
                                            ->first_name
                                            ?? null,

                                        $household
                                            ->householdHead
                                            ->middle_name
                                            ?? null,

                                        $household
                                            ->householdHead
                                            ->last_name
                                            ?? null,

                                        $household
                                            ->householdHead
                                            ->suffix
                                            ?? null,
                                    ])
                                )
                            );
                    }

                    $searchable =
                        strtolower(
                            implode(
                                ' | ',
                                [
                                    $residentName,

                                    $householdHeadName,

                                    (string) (
                                        $household
                                            ->household_number
                                        ?? ''
                                    ),

                                    (string) (
                                        $household
                                            ->address
                                        ?? ''
                                    ),

                                    (string) (
                                        $household
                                            ->purok
                                        ?? ''
                                    ),
                                ]
                            )
                        );

                    if (
                        !str_contains(
                            $searchable,
                            strtolower($search)
                        )
                    ) {
                        continue;
                    }
                }

                $records->push([
                    'resident' =>
                        $resident,

                    'relationship' =>
                        $residentRecord[
                            'relationship'
                        ]
                        ?? null,

                    'household' =>
                        $household,

                    'household_id' =>
                        $household->id,

                    'household_number' =>
                        $household->household_number,

                    'household_head' =>
                        $household->householdHead,

                    'purok' =>
                        $household->purok,

                    'address' =>
                        $household->address,

                    'filter' =>
                        $filter,
                ]);
            }
        }

        return $records
            ->sortBy([
                [
                    function (
                        $record
                    ) {
                        return strtolower(
                            (string) (
                                $record['purok']
                                ?? ''
                            )
                        );
                    },
                    'asc',
                ],

                [
                    function (
                        $record
                    ) {
                        return strtolower(
                            (string) (
                                $record[
                                    'household_number'
                                ]
                                ?? ''
                            )
                        );
                    },
                    'asc',
                ],

                [
                    function (
                        $record
                    ) {
                        return strtolower(
                            (string) (
                                $record['resident']
                                    ->last_name
                                ?? ''
                            )
                        );
                    },
                    'asc',
                ],

                [
                    function (
                        $record
                    ) {
                        return strtolower(
                            (string) (
                                $record['resident']
                                    ->first_name
                                ?? ''
                            )
                        );
                    },
                    'asc',
                ],
            ])
            ->values();
    }


    /*
    |--------------------------------------------------------------------------
    | MASTERLIST
    |--------------------------------------------------------------------------
    */

    public function masterlist(
        Request $request
    ): View {
        $operationId =
            $request->input(
                'operation_id'
            );

        $operation =
            $operationId
            ? ReliefAssistanceOperation::query()
                ->find($operationId)
            : null;

        /*
         * Internally the masterlist continues using "filter".
         *
         * The operation stores the selected value in
         * beneficiary_filter.
         *
         * target_group is also accepted for compatibility with
         * the new create/edit workflow.
         */
        $filter =
            $request->has('filter')
            ? $request->input('filter')
            : (
                $request->input(
                    'target_group'
                )
                ??
                $operation?->beneficiary_filter
                ??
                'all'
            );

        $purok =
            $request->has('purok')
            ? $request->input('purok')
            : (
                $operation?->purok_filter
                ?? ''
            );

        $filterRequest =
            Request::create(
                '',
                'GET',
                array_merge(
                    $request->query(),
                    [
                        'filter' =>
                            $filter,

                        'purok' =>
                            $purok,
                    ]
                )
            );

        $masterlistRecords =
            $this->buildMasterlistRecords(
                $filterRequest
            );

        if (
            $operation
        ) {
            $operation->update([
                'beneficiary_filter' =>
                    $filter,

                'purok_filter' =>
                    $purok !== ''
                    ? $purok
                    : null,

                'updated_by' =>
                    Auth::id(),
            ]);
        }

        $households =
            $this->buildBeneficiaryQuery(
                $filterRequest
            )->get();

        $selectedHouseholdIds =
            collect(
                $request->input(
                    'household_ids',
                    []
                )
            )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->filter(
                    fn ($id) =>
                        $id > 0
                )
                ->unique()
                ->values()
                ->all();

        $existingVoucherHouseholdIds =
            collect();

        if (
            $operation
        ) {
            $existingVoucherHouseholdIds =
                $operation
                    ->vouchers()
                    ->where(
                        'status',
                        '!=',
                        'cancelled'
                    )
                    ->pluck(
                        'household_id'
                    )
                    ->map(
                        fn ($id) =>
                            (int) $id
                    )
                    ->unique()
                    ->values();
        }

        $purokOptions =
            Household::query()
                ->where(
                    'status',
                    'Active'
                )
                ->whereNotNull(
                    'purok'
                )
                ->where(
                    'purok',
                    '!=',
                    ''
                )
                ->distinct()
                ->orderBy(
                    'purok'
                )
                ->pluck(
                    'purok'
                );

        $filterOptions =
            $this->beneficiaryFilterOptions();

        $filterLabel =
            $filterOptions[$filter]
            ?? 'All';

        return view(
            'assistance-campaigns.masterlist',
            compact(
                'operation',
                'households',
                'masterlistRecords',
                'selectedHouseholdIds',
                'existingVoucherHouseholdIds',
                'purokOptions',
                'filterOptions',
                'filter',
                'filterLabel'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT MASTERLIST
    |--------------------------------------------------------------------------
    */

    public function masterlistPrint(
        Request $request
    ): View|RedirectResponse {
        $operationId =
            $request->input(
                'operation_id'
            );

        $operation =
            $operationId
            ? ReliefAssistanceOperation::query()
                ->find($operationId)
            : null;

        $filter =
            $request->has('filter')
            ? $request->input('filter')
            : (
                $request->input(
                    'target_group'
                )
                ??
                $operation?->beneficiary_filter
                ??
                'all'
            );

        $purok =
            $request->has('purok')
            ? $request->input('purok')
            : (
                $operation?->purok_filter
                ?? ''
            );

        $filterRequest =
            Request::create(
                '',
                'GET',
                array_merge(
                    $request->query(),
                    [
                        'filter' =>
                            $filter,

                        'purok' =>
                            $purok,
                    ]
                )
            );

        $masterlistRecords =
            $this->buildMasterlistRecords(
                $filterRequest
            );

        if (
            $masterlistRecords->isEmpty()
        ) {
            return redirect()
                ->route(
                    'relief-assistance.masterlist',
                    [
                        'operation_id' =>
                            $operation?->id,

                        'filter' =>
                            $filter,

                        'purok' =>
                            $purok,
                    ]
                )
                ->with(
                    'error',
                    'No matching resident records were found for the selected masterlist filters.'
                );
        }

        if (
            $operation
        ) {
            $operation->update([
                'beneficiary_filter' =>
                    $filter,

                'purok_filter' =>
                    $purok !== ''
                    ? $purok
                    : null,

                'updated_by' =>
                    Auth::id(),
            ]);
        }

        $households =
            $this->buildBeneficiaryQuery(
                $filterRequest
            )->get();

        $selectedHouseholdIds =
            collect(
                $request->input(
                    'household_ids',
                    []
                )
            )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->filter(
                    fn ($id) =>
                        $id > 0
                )
                ->unique()
                ->values()
                ->all();

        $filterOptions =
            $this->beneficiaryFilterOptions();

        $filterLabel =
            $filterOptions[$filter]
            ?? 'All';

        return view(
            'assistance-campaigns.masterlist-print',
            compact(
                'operation',
                'households',
                'masterlistRecords',
                'selectedHouseholdIds',
                'filter',
                'filterLabel'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | BENEFICIARIES
    |--------------------------------------------------------------------------
    */

    public function beneficiaries(
        Request $request,
        ReliefAssistanceOperation $operation
    ): View {
        $filter =
            $request->has('filter')
            ? $request->input('filter')
            : (
                $request->input(
                    'target_group'
                )
                ??
                $operation->beneficiary_filter
                ??
                'all'
            );

        $purok =
            $request->has('purok')
            ? $request->input('purok')
            : (
                $operation->purok_filter
                ?? ''
            );

        $filterRequest =
            Request::create(
                '',
                'GET',
                array_merge(
                    $request->query(),
                    [
                        'filter' =>
                            $filter,

                        'purok' =>
                            $purok,
                    ]
                )
            );

        $masterlistRecords =
            $this->buildMasterlistRecords(
                $filterRequest
            );

        $encodedBeneficiaries =
            $operation
                ->beneficiaries()
                ->with([
                    'resident',
                    'household.householdHead',
                ])
                ->latest()
                ->get();

        $encodedResidentIds =
            $encodedBeneficiaries
                ->whereNotNull(
                    'resident_id'
                )
                ->pluck(
                    'resident_id'
                )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->unique()
                ->values();

        $existingVoucherResidentIds =
            $operation
                ->vouchers()
                ->where(
                    'status',
                    '!=',
                    'cancelled'
                )
                ->with(
                    'beneficiary'
                )
                ->get()
                ->pluck(
                    'beneficiary.resident_id'
                )
                ->filter()
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->unique()
                ->values();

        /*
         * beneficiary_status is intentionally retained here.
         * It tracks confirmation/removal of the resident record.
         */
        $confirmedCount =
            $encodedBeneficiaries
                ->where(
                    'beneficiary_status',
                    'confirmed'
                )
                ->count();

        $removedCount =
            $encodedBeneficiaries
                ->where(
                    'beneficiary_status',
                    'removed'
                )
                ->count();

        $pendingCount =
            $encodedBeneficiaries
                ->where(
                    'beneficiary_status',
                    'pending'
                )
                ->count();

        $remainingAssistanceQuantity =
            max(
                0,
                (
                    (float) (
                        $operation->quantity_received
                        ?? 0
                    )
                    -
                    (float) (
                        $operation->quantity_allocated
                        ?? 0
                    )
                )
            );

        $selectableResidentCount =
            $masterlistRecords
                ->pluck(
                    'resident.id'
                )
                ->filter()
                ->unique()
                ->count();

        $purokOptions =
            Household::query()
                ->where(
                    'status',
                    'Active'
                )
                ->whereNotNull(
                    'purok'
                )
                ->where(
                    'purok',
                    '!=',
                    ''
                )
                ->distinct()
                ->orderBy(
                    'purok'
                )
                ->pluck(
                    'purok'
                );

        $filterOptions =
            $this->beneficiaryFilterOptions();

        $filterLabel =
            $filterOptions[$filter]
            ?? 'All';

        $operation->update([
            'beneficiary_filter' =>
                $filter,

            'purok_filter' =>
                $purok !== ''
                ? $purok
                : null,

            'updated_by' =>
                Auth::id(),
        ]);

        $usesVouchers =
            $this->operationUsesVouchers(
                $operation
            );

        $isNonVoucherOperation =
            !$usesVouchers;

        return view(
            'assistance-campaigns.beneficiaries',
            compact(
                'operation',
                'masterlistRecords',
                'encodedBeneficiaries',
                'encodedResidentIds',
                'existingVoucherResidentIds',
                'purokOptions',
                'filterOptions',
                'filter',
                'filterLabel',
                'confirmedCount',
                'removedCount',
                'pendingCount',
                'remainingAssistanceQuantity',
                'selectableResidentCount',
                'usesVouchers',
                'isNonVoucherOperation'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CONFIRM BENEFICIARIES
    |--------------------------------------------------------------------------
    */

    public function confirmBeneficiaries(
        Request $request,
        ReliefAssistanceOperation $operation
    ): RedirectResponse {
        $validated =
            $request->validate([
                'resident_ids' => [
                    'required',
                    'array',
                    'min:1',
                ],

                'resident_ids.*' => [
                    'integer',
                    'distinct',
                    'exists:residents,id',
                ],
            ]);

        $residentIds =
            collect(
                $validated['resident_ids']
            )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->filter(
                    fn ($id) =>
                        $id > 0
                )
                ->unique()
                ->values()
                ->all();

        if (
            empty($residentIds)
        ) {
            return back()
                ->withErrors([
                    'resident_ids' =>
                        'Please select at least one resident.',
                ]);
        }

        $createdCount =
            0;

        $reconfirmedCount =
            0;

        $skippedCount =
            0;

        try {
            DB::transaction(
                function () use (
                    $operation,
                    $residentIds,
                    &$createdCount,
                    &$reconfirmedCount,
                    &$skippedCount
                ) {
                    $lockedOperation =
                        ReliefAssistanceOperation::query()
                            ->where(
                                'id',
                                $operation->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                    $residents =
                        Resident::query()
                            ->where(
                                'status',
                                'Active'
                            )
                            ->whereIn(
                                'id',
                                $residentIds
                            )
                            ->get();

                    if (
                        $residents->count()
                        !==
                        count($residentIds)
                    ) {
                        throw new \RuntimeException(
                            'One or more selected residents are no longer active.'
                        );
                    }

                    $households =
                        Household::query()
                            ->where(
                                'status',
                                'Active'
                            )
                            ->with(
                                'members'
                            )
                            ->get();

                    foreach (
                        $residents as $resident
                    ) {
                        $household =
                            $households->first(
                                function (
                                    Household $household
                                ) use (
                                    $resident
                                ) {
                                    if (
                                        (int) (
                                            $household
                                                ->household_head_id
                                            ?? 0
                                        )
                                        ===
                                        (int) $resident->id
                                    ) {
                                        return true;
                                    }

                                    return $household
                                        ->members
                                        ->contains(
                                            'resident_id',
                                            $resident->id
                                        );
                                }
                            );

                        if (
                            !$household
                        ) {
                            $skippedCount++;
                            continue;
                        }

                        $beneficiary =
                            ReliefOperationBeneficiary::query()
                                ->where(
                                    'operation_id',
                                    $lockedOperation->id
                                )
                                ->where(
                                    'resident_id',
                                    $resident->id
                                )
                                ->lockForUpdate()
                                ->first();

                        if (
                            $beneficiary
                        ) {
                            if (
                                $beneficiary
                                    ->beneficiary_status
                                ===
                                'removed'
                            ) {
                                $beneficiary->household_id =
                                    $household->id;

                                $beneficiary->beneficiary_status =
                                    'confirmed';

                                $beneficiary->validated_by =
                                    Auth::id();

                                $beneficiary->validated_at =
                                    now();

                                /*
                                 * Correct initial assistance status.
                                 */
                                $beneficiary->assistance_status =
                                    'awaiting_assistance';

                                $beneficiary->updated_by =
                                    Auth::id();

                                $beneficiary->save();

                                $reconfirmedCount++;
                            } else {
                                $skippedCount++;
                            }

                            continue;
                        }

                        ReliefOperationBeneficiary::create([
                            'operation_id' =>
                                $lockedOperation->id,

                            'resident_id' =>
                                $resident->id,

                            'household_id' =>
                                $household->id,

                            'beneficiary_status' =>
                                'confirmed',

                            'field_reference' =>
                                null,

                            'verification_notes' =>
                                null,

                            'validated_by' =>
                                Auth::id(),

                            'validated_at' =>
                                now(),

                            /*
                             * IMPORTANT:
                             *
                             * The database enum does not contain
                             * "confirmed" for assistance_status.
                             */
                            'assistance_status' =>
                                'awaiting_assistance',

                            'allocated_quantity' =>
                                0,

                            'allocation_notes' =>
                                null,

                            'allocated_by' =>
                                null,

                            'allocated_at' =>
                                null,

                            'distributed_at' =>
                                null,

                            'claimed_at' =>
                                null,

                            'created_by' =>
                                Auth::id(),

                            'updated_by' =>
                                Auth::id(),
                        ]);

                        $createdCount++;
                    }

                    if (
                        (
                            $createdCount
                            +
                            $reconfirmedCount
                        ) > 0
                    ) {
                        if (
                            $lockedOperation->status ===
                            'draft'
                            ||
                            $lockedOperation->status ===
                            'ready'
                        ) {
                            $lockedOperation->status =
                                'ready';
                        }

                        $lockedOperation->updated_by =
                            Auth::id();

                        $lockedOperation->save();
                    }
                }
            );
        } catch (
            \RuntimeException $exception
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'resident_ids' =>
                        $exception->getMessage(),
                ]);
        }

        $message =
            $createdCount
            . ' resident beneficiary'
            . (
                $createdCount !== 1
                    ? ' records'
                    : ' record'
            )
            . ' confirmed successfully.';

        if (
            $reconfirmedCount > 0
        ) {
            $message .=
                ' '
                . $reconfirmedCount
                . ' previously removed resident'
                . (
                    $reconfirmedCount !== 1
                        ? 's were'
                        : ' was'
                )
                . ' re-confirmed.';
        }

        if (
            $skippedCount > 0
        ) {
            $message .=
                ' '
                . $skippedCount
                . ' selected resident'
                . (
                    $skippedCount !== 1
                        ? 's were'
                        : ' was'
                )
                . ' already encoded or could not be matched to an active household.';
        }

        return redirect()
            ->route(
                'relief-assistance.beneficiaries',
                $operation
            )
            ->with(
                'success',
                $message
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE FIELD VERIFICATION
    |--------------------------------------------------------------------------
    */

    public function saveVerification(
        Request $request
    ): RedirectResponse {
        return redirect()
            ->route(
                'relief-assistance.masterlist',
                [
                    'operation_id' =>
                        $request->input(
                            'operation_id'
                        ),
                ]
            )
            ->with(
                'info',
                'The masterlist is intended for physical field verification by BHW or authorized barangay staff before actual beneficiaries are encoded.'
            );
    }


    /*
|--------------------------------------------------------------------------
| PRINT / PREVIEW CONFIRMED BENEFICIARIES
|--------------------------------------------------------------------------
*/

public function beneficiariesPrint(
    Request $request,
    ReliefAssistanceOperation $operation
): View|RedirectResponse {
    if (
        !$this->operationUsesVouchers(
            $operation
        )
    ) {
        return redirect()
            ->route(
                'relief-assistance.beneficiaries',
                $operation
            )
            ->with(
                'info',
                'This assistance activity does not use relief vouchers. Confirmed residents can be managed from the beneficiary workspace.'
            );
    }

    $search =
        trim(
            (string) (
                $request->input(
                    'search'
                )
                ?? ''
            )
        );

    $purok =
        trim(
            (string) (
                $request->input(
                    'purok'
                )
                ?? ''
            )
        );

    /*
     * Get confirmed resident beneficiaries for this operation.
     *
     * Multiple residents may belong to the same household,
     * therefore every beneficiary is handled individually.
     */
    $beneficiaries =
        $operation
            ->beneficiaries()
            ->with([
                'resident',
                'household.householdHead',
            ])
            ->where(
                'beneficiary_status',
                'confirmed'
            )
            ->get()
            ->filter(
                function (
                    ReliefOperationBeneficiary $beneficiary
                ) use (
                    $search,
                    $purok
                ) {
                    $household =
                        $beneficiary->household;

                    $resident =
                        $beneficiary->resident;

                    if (
                        !$household
                        ||
                        !$resident
                    ) {
                        return false;
                    }

                    if (
                        $purok !== ''
                        &&
                        (string) (
                            $household->purok
                            ?? ''
                        )
                        !==
                        $purok
                    ) {
                        return false;
                    }

                    if (
                        $search === ''
                    ) {
                        return true;
                    }

                    $residentName =
                        strtolower(
                            trim(
                                implode(
                                    ' ',
                                    array_filter([
                                        $resident->first_name
                                            ?? null,

                                        $resident->middle_name
                                            ?? null,

                                        $resident->last_name
                                            ?? null,

                                        $resident->suffix
                                            ?? null,
                                    ])
                                )
                            )
                        );

                    $householdHeadName =
                        strtolower(
                            trim(
                                implode(
                                    ' ',
                                    array_filter([
                                        $household
                                            ->householdHead
                                            ?->first_name,

                                        $household
                                            ->householdHead
                                            ?->middle_name,

                                        $household
                                            ->householdHead
                                            ?->last_name,
                                    ])
                                )
                            )
                        );

                    $searchable =
                        implode(
                            ' | ',
                            [
                                $residentName,

                                $householdHeadName,

                                strtolower(
                                    (string) (
                                        $household
                                            ->household_number
                                        ?? ''
                                    )
                                ),

                                strtolower(
                                    (string) (
                                        $household
                                            ->address
                                        ?? ''
                                    )
                                ),

                                strtolower(
                                    (string) (
                                        $household
                                            ->purok
                                        ?? ''
                                    )
                                ),
                            ]
                        );

                    return str_contains(
                        $searchable,
                        strtolower(
                            $search
                        )
                    );
                }
            )
            ->sortBy([
                [
                    function (
                        $beneficiary
                    ) {
                        return strtolower(
                            (string) (
                                $beneficiary
                                    ->household
                                    ->purok
                                ??
                                ''
                            )
                        );
                    },
                    'asc',
                ],

                [
                    function (
                        $beneficiary
                    ) {
                        return strtolower(
                            (string) (
                                $beneficiary
                                    ->household
                                    ->household_number
                                ??
                                ''
                            )
                        );
                    },
                    'asc',
                ],

                [
                    function (
                        $beneficiary
                    ) {
                        return strtolower(
                            (string) (
                                $beneficiary
                                    ->resident
                                    ->last_name
                                ??
                                ''
                            )
                        );
                    },
                    'asc',
                ],

                [
                    function (
                        $beneficiary
                    ) {
                        return strtolower(
                            (string) (
                                $beneficiary
                                    ->resident
                                    ->first_name
                                ??
                                ''
                            )
                        );
                    },
                    'asc',
                ],
            ])
            ->values();

    /*
     * Get all active vouchers for this operation.
     *
     * Multiple residents may belong to the same household,
     * therefore vouchers MUST be keyed by beneficiary_id,
     * not household_id.
     */
    $vouchers =
        $operation
            ->vouchers()
            ->with([
                'beneficiary.resident',
                'household.householdHead',
            ])
            ->where(
                'status',
                '!=',
                'cancelled'
            )
            ->get()
            ->filter(
                function (
                    ReliefVoucher $voucher
                ) {
                    return !empty(
                        $voucher->beneficiary_id
                    );
                }
            )
            ->keyBy(
                'beneficiary_id'
            );

    /*
     * IMPORTANT FIX:
     *
     * beneficiaries-print.blade.php expects the variable
     * $existingVouchers.
     *
     * The controller previously passed only $vouchers,
     * causing the Blade to think that there were no
     * generated vouchers even though voucher records
     * already existed in the database.
     *
     * Keep $vouchers for the controller's internal counting,
     * while providing $existingVouchers for the Blade.
     */
    $existingVouchers =
        $vouchers;

    $beneficiaryCount =
        $beneficiaries->count();

    $existingVoucherCount =
        $beneficiaries
            ->filter(
                function (
                    $beneficiary
                ) use (
                    $existingVouchers
                ) {
                    return $existingVouchers->has(
                        $beneficiary->id
                    );
                }
            )
            ->count();

    $pendingVoucherCount =
        max(
            0,
            $beneficiaryCount
            -
            $existingVoucherCount
        );

    $generatedVoucherCount =
        $existingVouchers
            ->filter(
                function (
                    ReliefVoucher $voucher
                ) {
                    return $voucher->status ===
                        'generated';
                }
            )
            ->count();

    $issuedVoucherCount =
        $existingVouchers
            ->filter(
                function (
                    ReliefVoucher $voucher
                ) {
                    return $voucher->status ===
                        'issued';
                }
            )
            ->count();

    $distributedVoucherCount =
        $existingVouchers
            ->filter(
                function (
                    ReliefVoucher $voucher
                ) {
                    return $voucher->status ===
                        'distributed';
                }
            )
            ->count();

    $claimedVoucherCount =
        $existingVouchers
            ->filter(
                function (
                    ReliefVoucher $voucher
                ) {
                    return $voucher->status ===
                        'claimed';
                }
            )
            ->count();

    return view(
        'assistance-campaigns.beneficiaries-print',
        compact(
            'operation',
            'beneficiaries',
            'existingVouchers',
            'beneficiaryCount',
            'existingVoucherCount',
            'pendingVoucherCount',
            'generatedVoucherCount',
            'issuedVoucherCount',
            'distributedVoucherCount',
            'claimedVoucherCount',
            'search',
            'purok'
        )
    );
}

    /*
    |--------------------------------------------------------------------------
    | ALLOCATION
    |--------------------------------------------------------------------------
    */

    public function allocation(
        ReliefAssistanceOperation $operation
    ): RedirectResponse {
        return redirect()
            ->route(
                'relief-assistance.beneficiaries',
                $operation
            )
            ->with(
                'info',
                'Beneficiary confirmation is resident-level. Allocation is handled during voucher generation for voucher-based assistance.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SAVE ALLOCATION
    |--------------------------------------------------------------------------
    */

    public function saveAllocation(
        Request $request,
        ReliefAssistanceOperation $operation
    ): RedirectResponse {
        return redirect()
            ->route(
                'relief-assistance.beneficiaries',
                $operation
            )
            ->with(
                'info',
                'Beneficiary confirmation is resident-level. Allocation is handled during voucher generation for voucher-based assistance.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | VOUCHERS COMPATIBILITY PAGE
    |--------------------------------------------------------------------------
    */

    public function vouchers(): RedirectResponse
    {
        return redirect()
            ->route(
                'relief-vouchers.index'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE VOUCHERS
    |--------------------------------------------------------------------------
    |
    | Only voucher-based assistance modes may reach this method.
    |
    */

    public function generate(
        Request $request
    ): RedirectResponse {
        $validated =
            $request->validate([
                'operation_id' => [
                    'required',
                    'exists:relief_assistance_operations,id',
                ],

                'resident_ids' => [
                    'nullable',
                    'array',
                ],

                'resident_ids.*' => [
                    'integer',
                    'exists:residents,id',
                ],

                'beneficiary_ids' => [
                    'nullable',
                    'array',
                ],

                'beneficiary_ids.*' => [
                    'integer',
                    'exists:relief_operation_beneficiaries,id',
                ],

                'household_ids' => [
                    'nullable',
                    'array',
                ],

                'household_ids.*' => [
                    'integer',
                    'exists:households,id',
                ],

                'allocated_quantity' => [
                    'nullable',
                    'array',
                ],

                'allocated_quantity.*' => [
                    'numeric',
                    'min:0.01',
                ],
            ]);

        $operation =
            ReliefAssistanceOperation::query()
                ->findOrFail(
                    $validated['operation_id']
                );

        /*
         * Hard protection:
         *
         * Service / Social Assistance must never create vouchers.
         */
        if (
            !$this->operationUsesVouchers(
                $operation
            )
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'beneficiary_ids' =>
                        'Voucher generation is not available for this assistance type. This operation uses a participant/beneficiary service workflow instead of relief vouchers.',
                ]);
        }

        $residentIds =
            collect(
                $validated['resident_ids']
                ?? []
            )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->filter(
                    fn ($id) =>
                        $id > 0
                )
                ->unique()
                ->values()
                ->all();

        $beneficiaryIds =
            collect(
                $validated['beneficiary_ids']
                ?? []
            )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->filter(
                    fn ($id) =>
                        $id > 0
                )
                ->unique()
                ->values()
                ->all();

        $householdIds =
            collect(
                $validated['household_ids']
                ?? []
            )
                ->map(
                    fn ($id) =>
                        (int) $id
                )
                ->filter(
                    fn ($id) =>
                        $id > 0
                )
                ->unique()
                ->values()
                ->all();

        $selectedBeneficiaries =
            collect();

        /*
         * Priority 1:
         * Explicit beneficiary IDs.
         */
        if (
            !empty(
                $beneficiaryIds
            )
        ) {
            $selectedBeneficiaries =
                ReliefOperationBeneficiary::query()
                    ->where(
                        'operation_id',
                        $operation->id
                    )
                    ->where(
                        'beneficiary_status',
                        'confirmed'
                    )
                    ->whereIn(
                        'id',
                        $beneficiaryIds
                    )
                    ->get();
        }

        /*
         * Priority 2:
         * Resident IDs.
         */
        if (
            $selectedBeneficiaries->isEmpty()
            &&
            !empty(
                $residentIds
            )
        ) {
            $selectedBeneficiaries =
                ReliefOperationBeneficiary::query()
                    ->where(
                        'operation_id',
                        $operation->id
                    )
                    ->where(
                        'beneficiary_status',
                        'confirmed'
                    )
                    ->whereIn(
                        'resident_id',
                        $residentIds
                    )
                    ->get();
        }

        /*
         * Priority 3:
         * Legacy household IDs.
         */
        if (
            $selectedBeneficiaries->isEmpty()
            &&
            !empty(
                $householdIds
            )
        ) {
            $selectedBeneficiaries =
                ReliefOperationBeneficiary::query()
                    ->where(
                        'operation_id',
                        $operation->id
                    )
                    ->where(
                        'beneficiary_status',
                        'confirmed'
                    )
                    ->whereIn(
                        'household_id',
                        $householdIds
                    )
                    ->get();
        }

        if (
            $selectedBeneficiaries->isEmpty()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'beneficiary_ids' =>
                        'None of the selected records are confirmed beneficiaries for this relief operation.',
                ]);
        }

        $quantities =
            $validated['allocated_quantity']
            ?? [];

        $generatedCount =
            0;

        $skippedExistingVoucherCount =
            0;

        $skippedInvalidCount =
            0;

        try {
            DB::transaction(
                function () use (
                    $operation,
                    $selectedBeneficiaries,
                    $quantities,
                    &$generatedCount,
                    &$skippedExistingVoucherCount,
                    &$skippedInvalidCount
                ) {
                    $lockedOperation =
                        ReliefAssistanceOperation::query()
                            ->where(
                                'id',
                                $operation->id
                            )
                            ->lockForUpdate()
                            ->firstOrFail();

                    /*
                     * Re-check inside transaction.
                     */
                    if (
                        !$this->operationUsesVouchers(
                            $lockedOperation
                        )
                    ) {
                        throw new \RuntimeException(
                            'Voucher generation is not available for this assistance type.'
                        );
                    }

                    $quantityReceived =
                        (float) (
                            $lockedOperation
                                ->quantity_received
                            ?? 0
                        );

                    $existingAllocatedQuantity =
                        (float) $lockedOperation
                            ->vouchers()
                            ->where(
                                'status',
                                '!=',
                                'cancelled'
                            )
                            ->sum(
                                'allocated_quantity'
                            );

                    $newQuantityTotal =
                        0;

                    $selectedBeneficiaryIds =
                        $selectedBeneficiaries
                            ->pluck('id')
                            ->map(
                                fn ($id) =>
                                    (int) $id
                            )
                            ->unique()
                            ->values()
                            ->all();

                    /*
                     * IMPORTANT:
                     *
                     * beneficiary_status = confirmed
                     * means the resident is confirmed for the operation.
                     *
                     * assistance_status is the assistance lifecycle.
                     */
                    $lockedBeneficiaries =
                        ReliefOperationBeneficiary::query()
                            ->where(
                                'operation_id',
                                $lockedOperation->id
                            )
                            ->where(
                                'beneficiary_status',
                                'confirmed'
                            )
                            ->whereIn(
                                'id',
                                $selectedBeneficiaryIds
                            )
                            ->lockForUpdate()
                            ->get();

                    foreach (
                        $lockedBeneficiaries
                        as $beneficiary
                    ) {
                        $residentId =
                            (int) (
                                $beneficiary
                                    ->resident_id
                                ?? 0
                            );

                        $householdId =
                            (int) (
                                $beneficiary
                                    ->household_id
                                ?? 0
                            );

                        if (
                            $residentId <= 0
                            ||
                            $householdId <= 0
                        ) {
                            $skippedInvalidCount++;
                            continue;
                        }

                        $resident =
                            Resident::query()
                                ->where(
                                    'id',
                                    $residentId
                                )
                                ->where(
                                    'status',
                                    'Active'
                                )
                                ->lockForUpdate()
                                ->first();

                        if (
                            !$resident
                        ) {
                            $skippedInvalidCount++;
                            continue;
                        }

                        $household =
                            Household::query()
                                ->where(
                                    'id',
                                    $householdId
                                )
                                ->where(
                                    'status',
                                    'Active'
                                )
                                ->lockForUpdate()
                                ->first();

                        if (
                            !$household
                        ) {
                            $skippedInvalidCount++;
                            continue;
                        }

                        /*
                         * Verify resident still belongs to the
                         * stored household.
                         */
                        $residentBelongsToHousehold =
                            (int) (
                                $household
                                    ->household_head_id
                                ?? 0
                            )
                            ===
                            $residentId;

                        if (
                            !$residentBelongsToHousehold
                        ) {
                            $residentBelongsToHousehold =
                                $household
                                    ->members()
                                    ->where(
                                        'resident_id',
                                        $residentId
                                    )
                                    ->exists();
                        }

                        if (
                            !$residentBelongsToHousehold
                        ) {
                            $skippedInvalidCount++;
                            continue;
                        }

                        /*
                         * One active voucher per BENEFICIARY.
                         *
                         * NOT one voucher per household.
                         */
                        $existingVoucher =
                            ReliefVoucher::query()
                                ->where(
                                    'operation_id',
                                    $lockedOperation->id
                                )
                                ->where(
                                    'beneficiary_id',
                                    $beneficiary->id
                                )
                                ->where(
                                    'status',
                                    '!=',
                                    'cancelled'
                                )
                                ->lockForUpdate()
                                ->first();

                        if (
                            $existingVoucher
                        ) {
                            $skippedExistingVoucherCount++;
                            continue;
                        }

                        /*
                         * Quantity:
                         *
                         * 1. beneficiary ID
                         * 2. household ID compatibility
                         * 3. default 1
                         */
                        $quantity =
                            (float) (
                                $quantities[
                                    $beneficiary->id
                                ]
                                ??
                                $quantities[
                                    $household->id
                                ]
                                ??
                                1
                            );

                        if (
                            $quantity <= 0
                        ) {
                            $quantity = 1;
                        }

                        if (
                            $quantityReceived > 0
                            &&
                            (
                                $existingAllocatedQuantity
                                +
                                $newQuantityTotal
                                +
                                $quantity
                            )
                            >
                            $quantityReceived
                        ) {
                            throw new \RuntimeException(
                                'The selected assistance quantity exceeds the available quantity for this relief operation.'
                            );
                        }

                        $newQuantityTotal +=
                            $quantity;

                        /*
                         * Generate unique voucher code.
                         */
                        do {
                            $voucherCode =
                                'RV-'
                                . strtoupper(
                                    (string) (
                                        $lockedOperation
                                            ->operation_code
                                        ??
                                        'RELIEF'
                                    )
                                )
                                . '-'
                                . strtoupper(
                                    Str::random(8)
                                );
                        } while (
                            ReliefVoucher::query()
                                ->where(
                                    'voucher_code',
                                    $voucherCode
                                )
                                ->exists()
                        );

                        ReliefVoucher::create([
                            'operation_id' =>
                                $lockedOperation->id,

                            'beneficiary_id' =>
                                $beneficiary->id,

                            'household_id' =>
                                $household->id,

                            'voucher_code' =>
                                $voucherCode,

                            'assistance_type' =>
                                $lockedOperation
                                    ->assistance_type,

                            'allocated_quantity' =>
                                $quantity,

                            'distribution_schedule' =>
                                $lockedOperation
                                    ->distribution_date,

                            'release_date' =>
                                $lockedOperation
                                    ->distribution_date,

                            'release_time' =>
                                $lockedOperation
                                    ->distribution_time,

                            'release_location' =>
                                $lockedOperation
                                    ->distribution_location,

                            'status' =>
                                'generated',
                        ]);

                        /*
                         * Synchronize beneficiary assistance status.
                         */
                        $beneficiary->assistance_status =
                            'voucher_generated';

                        $beneficiary->allocated_quantity =
                            $quantity;

                        $beneficiary->allocated_by =
                            Auth::id();

                        $beneficiary->allocated_at =
                            now();

                        $beneficiary->updated_by =
                            Auth::id();

                        $beneficiary->save();

                        $generatedCount++;
                    }

                    /*
                     * Recalculate allocation from active vouchers.
                     */
                    $totalAllocated =
                        (float) $lockedOperation
                            ->vouchers()
                            ->where(
                                'status',
                                '!=',
                                'cancelled'
                            )
                            ->sum(
                                'allocated_quantity'
                            );

                    $lockedOperation->quantity_allocated =
                        $totalAllocated;

                    if (
                        $generatedCount > 0
                    ) {
                        $lockedOperation->status =
                            'distribution';
                    }

                    $lockedOperation->updated_by =
                        Auth::id();

                    $lockedOperation->save();
                }
            );
        } catch (
            \RuntimeException $exception
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'beneficiary_ids' =>
                        $exception->getMessage(),
                ]);
        }

        /*
         * Nothing new was generated.
         */
        if (
            $generatedCount === 0
        ) {
            $existingVoucherCount =
                ReliefVoucher::query()
                    ->where(
                        'operation_id',
                        $operation->id
                    )
                    ->where(
                        'status',
                        '!=',
                        'cancelled'
                    )
                    ->count();

            if (
                $existingVoucherCount > 0
                &&
                $skippedExistingVoucherCount > 0
            ) {
                return redirect()
                    ->route(
                        'relief-assistance.beneficiaries.print',
                        $operation
                    )
                    ->with(
                        'info',
                        'The selected resident beneficiary records already have active vouchers. The bulk voucher preview is ready.'
                    );
            }

            return back()
                ->withInput()
                ->withErrors([
                    'beneficiary_ids' =>
                        'No new voucher was generated. Make sure the selected residents are confirmed beneficiaries, belong to this operation, have active household records, and do not already have active vouchers.',
                ]);
        }

        $message =
            $generatedCount
            . ' relief voucher'
            . (
                $generatedCount !== 1
                    ? 's'
                    : ''
            )
            . ' generated successfully. The bulk voucher preview is ready for printing.';

        if (
            $skippedExistingVoucherCount > 0
        ) {
            $message .=
                ' '
                . $skippedExistingVoucherCount
                . ' selected resident beneficiary'
                . (
                    $skippedExistingVoucherCount !== 1
                        ? ' records already had active vouchers and were skipped.'
                        : ' record already had an active voucher and was skipped.'
                );
        }

        if (
            $skippedInvalidCount > 0
        ) {
            $message .=
                ' '
                . $skippedInvalidCount
                . ' selected record'
                . (
                    $skippedInvalidCount !== 1
                        ? 's were'
                        : ' was'
                )
                . ' skipped because the resident or household was no longer active or valid.';
        }

        return redirect()
            ->route(
                'relief-assistance.beneficiaries.print',
                $operation
            )
            ->with(
                'success',
                $message
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW VOUCHER
    |--------------------------------------------------------------------------
    */

    public function show(
        ReliefVoucher $reliefVoucher
    ): View {
        $reliefVoucher->load([
            'operation',
            'household.householdHead',
            'beneficiary.resident',
            'qrVerifiedBy',
            'releasedBy',
        ]);

        return view(
            'relief-vouchers.show',
            compact(
                'reliefVoucher'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PRINT SINGLE VOUCHER
    |--------------------------------------------------------------------------
    */

    public function print(
        ReliefVoucher $reliefVoucher
    ): View {
        $reliefVoucher->load([
            'operation',
            'household.householdHead',
            'beneficiary.resident',
            'qrVerifiedBy',
            'releasedBy',
        ]);

        return view(
            'relief-vouchers.print',
            compact(
                'reliefVoucher'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | QR VERIFICATION PAGE
    |--------------------------------------------------------------------------
    */

    public function qrVerification(): View
    {
        return view(
            'relief-vouchers.verify'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | VERIFY QR VOUCHER
    |--------------------------------------------------------------------------
    |
    | FINAL NORMAL WORKFLOW:
    |
    | Scan QR
    |     ↓
    | Find voucher
    |     ↓
    | Validate
    |     ↓
    | Record QR verification
    |     ↓
    | Automatically claim
    |
    */

    public function verifyQr(
    Request $request
): RedirectResponse|\Illuminate\Http\JsonResponse {
    $validated =
        $request->validate([
            'voucher_code' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

    try {
        $voucher =
            DB::transaction(
                function () use (
                    $validated
                ) {
                    $voucher =
                        ReliefVoucher::query()
                            ->where(
                                'voucher_code',
                                trim(
                                    $validated[
                                        'voucher_code'
                                    ]
                                )
                            )
                            ->lockForUpdate()
                            ->first();

                    if (
                        !$voucher
                    ) {
                        throw new \RuntimeException(
                            'Voucher code was not found.'
                        );
                    }

                    if (
                        $voucher->isClaimed()
                    ) {
                        throw new \RuntimeException(
                            'This voucher has already been claimed.'
                        );
                    }

                    if (
                        $voucher->isCancelled()
                    ) {
                        throw new \RuntimeException(
                            'This voucher has been cancelled.'
                        );
                    }

                    if (
                        !$voucher->isGenerated()
                        &&
                        !$voucher->isIssued()
                        &&
                        !$voucher->isDistributed()
                    ) {
                        throw new \RuntimeException(
                            'This voucher is not ready for QR verification.'
                        );
                    }

                    /*
                     * Record QR verification.
                     */
                    $voucher->qr_verified_at =
                        $voucher->qr_verified_at
                        ??
                        now();

                    $voucher->qr_verified_by =
                        $voucher->qr_verified_by
                        ??
                        Auth::id();

                    /*
                     * QR scan is the actual release/claim event.
                     */
                    $voucher->status =
                        'claimed';

                    $voucher->released_at =
                        $voucher->released_at
                        ??
                        now();

                    $voucher->released_by =
                        $voucher->released_by
                        ??
                        Auth::id();

                    $voucher->claimed_at =
                        now();

                    $voucher->save();

                    /*
                     * Synchronize beneficiary.
                     */
                    if (
                        $voucher->beneficiary_id
                    ) {
                        $beneficiary =
                            ReliefOperationBeneficiary::query()
                                ->where(
                                    'id',
                                    $voucher->beneficiary_id
                                )
                                ->lockForUpdate()
                                ->first();

                        if (
                            $beneficiary
                        ) {
                            $beneficiary->assistance_status =
                                'claimed';

                            $beneficiary->distributed_at =
                                $beneficiary
                                    ->distributed_at
                                ??
                                now();

                            $beneficiary->claimed_at =
                                now();

                            $beneficiary->updated_by =
                                Auth::id();

                            $beneficiary->save();
                        }
                    }

                    /*
                     * Update operation status.
                     */
                    $operation =
                        ReliefAssistanceOperation::query()
                            ->where(
                                'id',
                                $voucher->operation_id
                            )
                            ->lockForUpdate()
                            ->first();

                    if (
                        $operation
                    ) {
                        $remainingActiveVouchers =
                            $operation
                                ->vouchers()
                                ->whereIn(
                                    'status',
                                    [
                                        'generated',
                                        'issued',
                                        'distributed',
                                    ]
                                )
                                ->count();

                        if (
                            $remainingActiveVouchers === 0
                        ) {
                            $operation->status =
                                'completed';
                        } else {
                            $operation->status =
                                'distribution';
                        }

                        $operation->updated_by =
                            Auth::id();

                        $operation->save();
                    }

                    return $voucher;
                }
            );

        /*
         * Load the relationships needed by the
         * continuous QR scanner.
         */
        $voucher->load([
            'operation',
            'household.householdHead',
            'beneficiary.resident',
        ]);

        /*
         * AJAX / continuous QR scanner response.
         *
         * The scanner will send:
         *
         * Accept: application/json
         *
         * Therefore, return JSON instead of redirecting
         * to the voucher details page.
         */
        if (
            $request->expectsJson()
        ) {
            $claimant =
                $voucher->beneficiary?->resident
                ??
                $voucher->household?->householdHead;

            /*
             * Build claimant full name.
             */
            $claimantName =
                'Unknown Claimant';

            if (
                $claimant
            ) {
                $nameParts = [
                    $claimant->first_name ?? null,
                    $claimant->middle_name ?? null,
                    $claimant->last_name ?? null,
                    $claimant->suffix ?? null,
                ];

                $claimantName =
                    trim(
                        implode(
                            ' ',
                            array_filter(
                                $nameParts,
                                fn ($value) =>
                                    filled($value)
                            )
                        )
                    );

                if (
                    $claimantName === ''
                ) {
                    $claimantName =
                        'Unknown Claimant';
                }
            }

            return response()->json([
                'success' =>
                    true,

                'message' =>
                    'Voucher claimed successfully.',

                'voucher' => [
                    'id' =>
                        $voucher->id,

                    'voucher_code' =>
                        $voucher->voucher_code,

                    'status' =>
                        $voucher->status,

                    'claimant_name' =>
                        $claimantName,

                    'household_number' =>
                        $voucher->household?->household_number
                        ??
                        '—',

                    'purok' =>
                        $voucher->purok_name
                        ??
                        $voucher->household?->purok
                        ??
                        '—',

                    'assistance_type' =>
                        $voucher->assistance_type
                        ??
                        'Relief Assistance',

                    'operation' =>
                        $voucher->operation?->operation_code
                        ??
                        '—',

                    'claimed_at' =>
                        optional(
                            $voucher->claimed_at
                        )->format(
                            'M d, Y h:i A'
                        ),

                    'details_url' =>
                        route(
                            'relief-vouchers.show',
                            $voucher
                        ),
                ],
            ]);
        }

        /*
         * Preserve the existing manual verification
         * behavior.
         *
         * If the request is a normal browser form
         * submission, it will still redirect to the
         * voucher details page.
         */
        return redirect()
            ->route(
                'relief-vouchers.show',
                $voucher
            )
            ->with(
                'success',
                'QR voucher verified successfully. The assistance has been automatically claimed and recorded.'
            );
    } catch (
        \RuntimeException $exception
    ) {
        /*
         * AJAX / continuous scanner error.
         *
         * Return JSON instead of redirecting so the
         * scanner remains open.
         */
        if (
            $request->expectsJson()
        ) {
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    $exception->getMessage(),
            ], 422);
        }

        /*
         * Preserve normal form submission behavior.
         */
        return back()
            ->withInput()
            ->withErrors([
                'voucher_code' =>
                    $exception->getMessage(),
            ]);
    }
}
}
