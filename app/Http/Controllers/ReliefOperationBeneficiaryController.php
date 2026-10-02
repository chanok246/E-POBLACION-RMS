<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\ReliefAssistanceOperation;
use App\Models\ReliefOperationBeneficiary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReliefOperationBeneficiaryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Relief Operation Beneficiaries
    |--------------------------------------------------------------------------
    |
    | This controller manages the human verification process for households
    | included in a specific relief assistance operation.
    |
    | The system does NOT automatically decide humanitarian eligibility.
    |
    | BHW / Staff:
    |   Field verification
    |
    | Secretary / Encoder:
    |   Encoder validation
    |
    | Authorized barangay decision:
    |   Confirmation / removal
    |
    */

    /**
     * Display the beneficiary masterlist for an operation.
     */
    public function index(
        Request $request,
        ReliefAssistanceOperation $operation
    ): View {
        /*
        |--------------------------------------------------------------------------
        | Beneficiary Records
        |--------------------------------------------------------------------------
        */

        $query = $operation->beneficiaries()
            ->with([
                'household.householdHead',
                'validator',
                'allocator',
            ])
            ->orderBy('id');

        /*
        |--------------------------------------------------------------------------
        | Beneficiary Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = trim(
                $request->input('search')
            );

            $query->where(function ($q) use ($search) {

                $q->where(
                    'field_reference',
                    'like',
                    '%' . $search . '%'
                )

                ->orWhereHas(
                    'household',
                    function ($householdQuery) use ($search) {

                        $householdQuery->where(
                            'household_number',
                            'like',
                            '%' . $search . '%'
                        )

                        ->orWhere(
                            'purok',
                            '=',
                            $search
                        )

                        ->orWhereHas(
                            'householdHead',
                            function ($headQuery) use ($search) {

                                $headQuery->where(
                                    'id',
                                    $search
                                )

                                ->orWhere(
                                    'first_name',
                                    'like',
                                    $search . '%'
                                )

                                ->orWhere(
                                    'middle_name',
                                    'like',
                                    $search . '%'
                                )

                                ->orWhere(
                                    'last_name',
                                    'like',
                                    $search . '%'
                                )

                                ->orWhere(
                                    'suffix',
                                    'like',
                                    $search . '%'
                                )

                                ->orWhereRaw(
                                    "TRIM(CONCAT_WS(' ', first_name, middle_name, last_name, suffix)) LIKE ?",
                                    [
                                        '%' . $search . '%',
                                    ]
                                );
                            }
                        );
                    }
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Beneficiary Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('beneficiary_status')) {
            $query->where(
                'beneficiary_status',
                $request->input('beneficiary_status')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Assistance Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('assistance_status')) {
            $query->where(
                'assistance_status',
                $request->input('assistance_status')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Beneficiary Purok
        |--------------------------------------------------------------------------
        */

        if ($request->filled('purok')) {
            $purok = trim(
                $request->input('purok')
            );

            $query->whereHas(
                'household',
                function ($householdQuery) use ($purok) {
                    $householdQuery->where(
                        'purok',
                        '=',
                        $purok
                    );
                }
            );
        }

        $beneficiaries = $query
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Available Households for Encoding
        |--------------------------------------------------------------------------
        */

        $availableHouseholdsQuery = Household::query()
            ->with('householdHead')
            ->where(
                'status',
                'Active'
            )
            ->whereNotIn(
                'id',
                $operation->beneficiaries()
                    ->select('household_id')
            )
            ->orderBy('purok')
            ->orderBy('household_number');

        /*
        |--------------------------------------------------------------------------
        | Available Household Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('household_search')) {
            $householdSearch = trim(
                $request->input('household_search')
            );

            $availableHouseholdsQuery->where(function ($q) use ($householdSearch) {

                $q->where(
                    'household_number',
                    'like',
                    '%' . $householdSearch . '%'
                )

                ->orWhere(
                    'address',
                    'like',
                    '%' . $householdSearch . '%'
                )

                ->orWhereHas(
                    'householdHead',
                    function ($headQuery) use ($householdSearch) {

                        $headQuery->where(
                            'id',
                            $householdSearch
                        )

                        ->orWhere(
                            'first_name',
                            'like',
                            $householdSearch . '%'
                        )

                        ->orWhere(
                            'middle_name',
                            'like',
                            $householdSearch . '%'
                        )

                        ->orWhere(
                            'last_name',
                            'like',
                            $householdSearch . '%'
                        )

                        ->orWhere(
                            'suffix',
                            'like',
                            $householdSearch . '%'
                        )

                        ->orWhereRaw(
                            "TRIM(CONCAT_WS(' ', first_name, middle_name, last_name, suffix)) LIKE ?",
                            [
                                '%' . $householdSearch . '%',
                            ]
                        );
                    }
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Available Household Purok Filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('household_purok')) {
            $householdPurok = trim(
                $request->input('household_purok')
            );

            $availableHouseholdsQuery->where(
                'purok',
                '=',
                $householdPurok
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Paginate Available Households
        |--------------------------------------------------------------------------
        */

        $availableHouseholds = $availableHouseholdsQuery
            ->paginate(
                20,
                ['*'],
                'household_page'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Summary Counts
        |--------------------------------------------------------------------------
        */

        $totalBeneficiaries = $operation
            ->beneficiaries()
            ->count();

        $fieldVerifiedCount = $operation
            ->beneficiaries()
            ->where(
                'beneficiary_status',
                'field_verified'
            )
            ->count();

        $encoderValidatedCount = $operation
            ->beneficiaries()
            ->where(
                'beneficiary_status',
                'encoder_validated'
            )
            ->count();

        $confirmedCount = $operation
            ->beneficiaries()
            ->where(
                'beneficiary_status',
                'confirmed'
            )
            ->count();

        $removedCount = $operation
            ->beneficiaries()
            ->where(
                'beneficiary_status',
                'removed'
            )
            ->count();

        $awaitingAssistanceCount = $operation
            ->beneficiaries()
            ->where(
                'assistance_status',
                'awaiting_assistance'
            )
            ->count();

        $allocatedCount = $operation
            ->beneficiaries()
            ->where(
                'assistance_status',
                'allocated'
            )
            ->count();

        $voucherGeneratedCount = $operation
            ->beneficiaries()
            ->where(
                'assistance_status',
                'voucher_generated'
            )
            ->count();

        $distributedCount = $operation
            ->beneficiaries()
            ->where(
                'assistance_status',
                'distributed'
            )
            ->count();

        $claimedCount = $operation
            ->beneficiaries()
            ->where(
                'assistance_status',
                'claimed'
            )
            ->count();

        return view(
            'assistance-campaigns.beneficiaries',
            compact(
                'operation',
                'beneficiaries',
                'availableHouseholds',
                'totalBeneficiaries',
                'fieldVerifiedCount',
                'encoderValidatedCount',
                'confirmedCount',
                'removedCount',
                'awaitingAssistanceCount',
                'allocatedCount',
                'voucherGeneratedCount',
                'distributedCount',
                'claimedCount'
            )
        );
    }


    /**
     * Live AJAX search for the beneficiary masterlist.
     *
     * This endpoint only returns the beneficiary table rows
     * and pagination. The complete page is not reloaded.
     */
    public function liveSearch(
        Request $request,
        ReliefAssistanceOperation $operation
    ): JsonResponse {
        $query = $operation->beneficiaries()
            ->with([
                'household.householdHead',
                'validator',
                'allocator',
            ])
            ->orderBy('id');

        $search = trim(
            $request->input(
                'search',
                ''
            )
        );

        if ($search !== '') {

            $query->where(function ($q) use ($search) {

                $q->where(
                    'field_reference',
                    'like',
                    '%' . $search . '%'
                )

                ->orWhereHas(
                    'household',
                    function ($householdQuery) use ($search) {

                        $householdQuery->where(
                            'household_number',
                            'like',
                            '%' . $search . '%'
                        )

                        ->orWhere(
                            'purok',
                            '=',
                            $search
                        )

                        ->orWhereHas(
                            'householdHead',
                            function ($headQuery) use ($search) {

                                $headQuery->where(
                                    'id',
                                    $search
                                )

                                ->orWhere(
                                    'first_name',
                                    'like',
                                    $search . '%'
                                )

                                ->orWhere(
                                    'middle_name',
                                    'like',
                                    $search . '%'
                                )

                                ->orWhere(
                                    'last_name',
                                    'like',
                                    $search . '%'
                                )

                                ->orWhere(
                                    'suffix',
                                    'like',
                                    $search . '%'
                                )

                                ->orWhereRaw(
                                    "TRIM(CONCAT_WS(' ', first_name, middle_name, last_name, suffix)) LIKE ?",
                                    [
                                        '%' . $search . '%',
                                    ]
                                );
                            }
                        );
                    }
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Beneficiary Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('beneficiary_status')) {
            $query->where(
                'beneficiary_status',
                $request->input('beneficiary_status')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Assistance Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('assistance_status')) {
            $query->where(
                'assistance_status',
                $request->input('assistance_status')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Purok
        |--------------------------------------------------------------------------
        */

        if ($request->filled('purok')) {
            $purok = trim(
                $request->input('purok')
            );

            $query->whereHas(
                'household',
                function ($householdQuery) use ($purok) {
                    $householdQuery->where(
                        'purok',
                        '=',
                        $purok
                    );
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $beneficiaries = $query
            ->paginate(15)
            ->withQueryString();

        $html = view(
            'assistance-campaigns.partials.beneficiary-rows',
            compact(
                'operation',
                'beneficiaries'
            )
        )->render();

        $paginationHtml = $beneficiaries
            ->links('pagination::bootstrap-5')
            ->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'pagination' => $paginationHtml,
            'count' => $beneficiaries->total(),
        ]);
    }


    /**
     * Live AJAX search for available households.
     *
     * This endpoint is used by the household encoding section.
     *
     * Only the household table rows and pagination are returned.
     * The complete beneficiary page is NOT reloaded.
     *
     * Therefore:
     *
     * - no page reload
     * - no scroll jump
     * - search can happen while typing
     * - current page interface remains in place
     */
    public function liveHouseholdSearch(
        Request $request,
        ReliefAssistanceOperation $operation
    ): JsonResponse {
        /*
        |--------------------------------------------------------------------------
        | Available Households
        |--------------------------------------------------------------------------
        */

        $query = Household::query()
            ->with('householdHead')
            ->where(
                'status',
                'Active'
            )
            ->whereNotIn(
                'id',
                $operation->beneficiaries()
                    ->select('household_id')
            )
            ->orderBy('purok')
            ->orderBy('household_number');

        /*
        |--------------------------------------------------------------------------
        | Household Search
        |--------------------------------------------------------------------------
        */

        $search = trim(
            $request->input(
                'household_search',
                ''
            )
        );

        if ($search !== '') {

            $query->where(function ($q) use ($search) {

                /*
                | Household Number
                */

                $q->where(
                    'household_number',
                    'like',
                    '%' . $search . '%'
                )

                /*
                | Address
                */

                ->orWhere(
                    'address',
                    'like',
                    '%' . $search . '%'
                )

                /*
                | Household Head
                */

                ->orWhereHas(
                    'householdHead',
                    function ($headQuery) use ($search) {

                        /*
                        | Resident ID
                        */

                        $headQuery->where(
                            'id',
                            $search
                        )

                        /*
                        | First Name
                        */

                        ->orWhere(
                            'first_name',
                            'like',
                            $search . '%'
                        )

                        /*
                        | Middle Name
                        */

                        ->orWhere(
                            'middle_name',
                            'like',
                            $search . '%'
                        )

                        /*
                        | Last Name
                        */

                        ->orWhere(
                            'last_name',
                            'like',
                            $search . '%'
                        )

                        /*
                        | Suffix
                        */

                        ->orWhere(
                            'suffix',
                            'like',
                            $search . '%'
                        )

                        /*
                        | Complete Name
                        */

                        ->orWhereRaw(
                            "TRIM(CONCAT_WS(' ', first_name, middle_name, last_name, suffix)) LIKE ?",
                            [
                                '%' . $search . '%',
                            ]
                        );
                    }
                );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Household Purok
        |--------------------------------------------------------------------------
        */

        if ($request->filled('household_purok')) {

            $householdPurok = trim(
                $request->input(
                    'household_purok'
                )
            );

            $query->where(
                'purok',
                '=',
                $householdPurok
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        |
        | A new search always begins from page 1.
        |
        */

        $availableHouseholds = $query
            ->paginate(
                20,
                ['*'],
                'household_page'
            )
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Render Household Rows
        |--------------------------------------------------------------------------
        */

        $html = view(
            'assistance-campaigns.partials.household-rows',
            compact(
                'operation',
                'availableHouseholds'
            )
        )->render();

        /*
        |--------------------------------------------------------------------------
        | Render Household Pagination
        |--------------------------------------------------------------------------
        */

        $paginationHtml = $availableHouseholds
            ->links('pagination::bootstrap-5')
            ->render();

        return response()->json([
            'success' => true,
            'html' => $html,
            'pagination' => $paginationHtml,
            'count' => $availableHouseholds->total(),
        ]);
    }


    /**
     * Add households from the current household masterlist
     * as field-verified beneficiaries.
     *
     * This records the BHW / Staff field verification result.
     */
    public function validate(
        Request $request,
        ReliefAssistanceOperation $operation
    ): RedirectResponse {
        $validated = $request->validate([
            'household_ids' => [
                'required',
                'array',
                'min:1',
            ],

            'household_ids.*' => [
                'integer',
                'exists:households,id',
            ],

            'field_reference' => [
                'nullable',
                'string',
                'max:255',
            ],

            'verification_notes' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Operation protection
        |--------------------------------------------------------------------------
        */

        if (
            $operation->isCompleted()
            || $operation->isCancelled()
        ) {
            return back()->with(
                'error',
                'This relief assistance operation is no longer accepting beneficiary records.'
            );
        }

        $createdCount = 0;
        $existingCount = 0;

        foreach ($validated['household_ids'] as $householdId) {

            /*
            |--------------------------------------------------------------------------
            | Only active households can be included.
            |--------------------------------------------------------------------------
            */

            $householdExists = Household::where(
                'id',
                $householdId
            )
                ->where(
                    'status',
                    'Active'
                )
                ->exists();

            if (! $householdExists) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Prevent duplicate household inclusion.
            |--------------------------------------------------------------------------
            */

            $existing = ReliefOperationBeneficiary::where(
                'operation_id',
                $operation->id
            )
                ->where(
                    'household_id',
                    $householdId
                )
                ->first();

            if ($existing) {
                $existingCount++;

                continue;
            }

            ReliefOperationBeneficiary::create([
                'operation_id' =>
                    $operation->id,

                'household_id' =>
                    $householdId,

                'beneficiary_status' =>
                    'field_verified',

                'field_reference' =>
                    $validated['field_reference'] ?? null,

                'verification_notes' =>
                    $validated['verification_notes'] ?? null,

                'created_by' =>
                    Auth::id(),

                'updated_by' =>
                    Auth::id(),
            ]);

            $createdCount++;
        }

        /*
        |--------------------------------------------------------------------------
        | Move operation into verification stage when necessary.
        |--------------------------------------------------------------------------
        */

        if (
            $createdCount > 0
            && $operation->status === 'draft'
        ) {
            $operation->update([
                'status' => 'verification',
                'updated_by' => Auth::id(),
            ]);
        }

        $message = $createdCount
            . ' household(s) added to the field-verified beneficiary masterlist.';

        if ($existingCount > 0) {
            $message .= ' '
                . $existingCount
                . ' household(s) were already included and were not duplicated.';
        }

        return back()->with(
            'success',
            $message
        );
    }


    /**
     * Confirm a beneficiary only after encoder validation.
     */
    public function confirm(
        ReliefOperationBeneficiary $beneficiary
    ): RedirectResponse {
        $beneficiary->load('operation');

        $operation = $beneficiary->operation;

        if (! $operation) {
            return back()->with(
                'error',
                'The relief assistance operation could not be found.'
            );
        }

        if (
            $operation->isCompleted()
            || $operation->isCancelled()
        ) {
            return back()->with(
                'error',
                'This relief assistance operation is already closed.'
            );
        }

        if (
            $beneficiary->beneficiary_status === 'removed'
        ) {
            return back()->with(
                'error',
                'A removed beneficiary cannot be confirmed directly.'
            );
        }

        if (
            $beneficiary->beneficiary_status !== 'encoder_validated'
        ) {
            return back()->with(
                'error',
                'This beneficiary must be encoder validated before confirmation.'
            );
        }

        $beneficiary->update([
            'beneficiary_status' => 'confirmed',
            'validated_by' => Auth::id(),
            'validated_at' => now(),
            'updated_by' => Auth::id(),
        ]);

        return back()->with(
            'success',
            'Household beneficiary confirmed successfully.'
        );
    }


    /**
     * Remove a beneficiary from the current operation.
     */
    public function remove(
        ReliefOperationBeneficiary $beneficiary
    ): RedirectResponse {
        $beneficiary->load('operation');

        $operation = $beneficiary->operation;

        if (! $operation) {
            return back()->with(
                'error',
                'The relief assistance operation could not be found.'
            );
        }

        if ($operation->isCompleted()) {
            return back()->with(
                'error',
                'A completed relief assistance operation cannot be modified.'
            );
        }

        if (
            in_array(
                $beneficiary->assistance_status,
                [
                    'voucher_generated',
                    'distributed',
                    'claimed',
                ],
                true
            )
        ) {
            return back()->with(
                'error',
                'This beneficiary cannot be removed because assistance processing has already started.'
            );
        }

        $beneficiary->update([
            'beneficiary_status' => 'removed',
            'updated_by' => Auth::id(),
        ]);

        return back()->with(
            'success',
            'Household was removed from the beneficiary masterlist for this operation.'
        );
    }


    /**
     * Mark a field-verified beneficiary as encoder validated.
     */
    public function encoderValidate(
        ReliefOperationBeneficiary $beneficiary
    ): RedirectResponse {
        $beneficiary->load('operation');

        $operation = $beneficiary->operation;

        if (! $operation) {
            return back()->with(
                'error',
                'The relief assistance operation could not be found.'
            );
        }

        if (
            $operation->isCompleted()
            || $operation->isCancelled()
        ) {
            return back()->with(
                'error',
                'This relief assistance operation is already closed.'
            );
        }

        if (
            $beneficiary->beneficiary_status !== 'field_verified'
        ) {
            return back()->with(
                'error',
                'Only field-verified beneficiaries can be encoder validated.'
            );
        }

        $beneficiary->update([
            'beneficiary_status' =>
                'encoder_validated',

            'validated_by' =>
                Auth::id(),

            'validated_at' =>
                now(),

            'updated_by' =>
                Auth::id(),
        ]);

        return back()->with(
            'success',
            'Beneficiary record validated by encoder.'
        );
    }


    /**
     * Print the beneficiary masterlist.
     */
    public function print(
        Request $request,
        ReliefAssistanceOperation $operation
    ): View {
        $query = $operation->beneficiaries()
            ->with([
                'household.householdHead',
            ])
            ->orderBy(
                'id'
            );

        if ($request->filled('beneficiary_status')) {
            $query->where(
                'beneficiary_status',
                $request->input('beneficiary_status')
            );
        }

        if ($request->filled('assistance_status')) {
            $query->where(
                'assistance_status',
                $request->input('assistance_status')
            );
        }

        if ($request->filled('purok')) {
            $purok = trim(
                $request->input('purok')
            );

            $query->whereHas(
                'household',
                function ($householdQuery) use ($purok) {
                    $householdQuery->where(
                        'purok',
                        '=',
                        $purok
                    );
                }
            );
        }

        $beneficiaries = $query->get();

        return view(
            'assistance-campaigns.beneficiaries-print',
            compact(
                'operation',
                'beneficiaries'
            )
        );
    }


    /**
     * Prepare allocation data for the operation.
     */
    public function allocation(
        ReliefAssistanceOperation $operation
    ): View {
        $operation->load([
            'beneficiaries.household.householdHead',
        ]);

        $confirmedBeneficiaries = $operation
            ->beneficiaries()
            ->with([
                'household.householdHead',
            ])
            ->where(
                'beneficiary_status',
                'confirmed'
            )
            ->where(
                'assistance_status',
                'awaiting_assistance'
            )
            ->orderBy(
                'id'
            )
            ->get();

        $confirmedCount = $operation
            ->beneficiaries()
            ->where(
                'beneficiary_status',
                'confirmed'
            )
            ->count();

        $allocatedCount = $operation
            ->beneficiaries()
            ->where(
                'assistance_status',
                'allocated'
            )
            ->count();

        $requiredQuantity = $confirmedCount;

        $availableQuantity =
            (int) ($operation->quantity_received ?? 0)
            - (int) ($operation->quantity_allocated ?? 0);

        $shortage = max(
            0,
            $requiredQuantity - $availableQuantity
        );

        return view(
            'assistance-campaigns.allocation',
            compact(
                'operation',
                'confirmedBeneficiaries',
                'confirmedCount',
                'allocatedCount',
                'requiredQuantity',
                'availableQuantity',
                'shortage'
            )
        );
    }


    /**
     * Save authorized allocation.
     */
    public function saveAllocation(
        Request $request,
        ReliefAssistanceOperation $operation
    ): RedirectResponse {
        $validated = $request->validate([
            'allocations' => [
                'required',
                'array',
                'min:1',
            ],

            'allocations.*.beneficiary_id' => [
                'required',
                'integer',
                'exists:relief_operation_beneficiaries,id',
            ],

            'allocations.*.quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'allocation_basis' => [
                'required',
                'string',
                'max:255',
            ],

            'allocation_notes' => [
                'nullable',
                'string',
            ],
        ]);

        if (
            $operation->isCompleted()
            || $operation->isCancelled()
        ) {
            return back()->with(
                'error',
                'This relief assistance operation can no longer be allocated.'
            );
        }

        DB::transaction(function () use (
            $validated,
            $operation
        ) {
            $additionalAllocation = 0;

            foreach (
                $validated['allocations']
                as $allocation
            ) {
                $beneficiary =
                    ReliefOperationBeneficiary::query()
                        ->lockForUpdate()
                        ->where(
                            'id',
                            $allocation['beneficiary_id']
                        )
                        ->where(
                            'operation_id',
                            $operation->id
                        )
                        ->firstOrFail();

                if (
                    ! $beneficiary->isConfirmed()
                    || ! $beneficiary->isAwaitingAssistance()
                ) {
                    continue;
                }

                $quantity =
                    (int) $allocation['quantity'];

                $beneficiary->update([
                    'assistance_status' =>
                        'allocated',

                    'allocated_quantity' =>
                        $quantity,

                    'allocation_notes' =>
                        $validated['allocation_notes'] ?? null,

                    'allocated_by' =>
                        Auth::id(),

                    'allocated_at' =>
                        now(),

                    'updated_by' =>
                        Auth::id(),
                ]);

                $additionalAllocation += $quantity;
            }

            $newAllocatedTotal =
                (int) $operation->quantity_allocated
                + $additionalAllocation;

            if (
                $newAllocatedTotal
                > (int) $operation->quantity_received
            ) {
                throw new \RuntimeException(
                    'The allocation exceeds the available assistance quantity.'
                );
            }

            $operation->update([
                'quantity_allocated' =>
                    $newAllocatedTotal,

                'allocation_basis' =>
                    $validated['allocation_basis'],

                'allocation_notes' =>
                    $validated['allocation_notes'] ?? null,

                'status' =>
                    'allocated',

                'updated_by' =>
                    Auth::id(),
            ]);
        });

        return redirect()
            ->route(
                'relief-assistance.allocation',
                $operation
            )
            ->with(
                'success',
                'Authorized relief assistance allocation recorded successfully.'
            );
    }
}