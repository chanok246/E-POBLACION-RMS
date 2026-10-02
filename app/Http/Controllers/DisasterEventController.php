<?php

namespace App\Http\Controllers;

use App\Models\DisasterEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DisasterEventController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Disaster Types
    |--------------------------------------------------------------------------
    */

    private function disasterTypes(): array
    {
        return [
            'Typhoon',
            'Flood',
            'Earthquake',
            'Fire',
            'Landslide',
            'Storm Surge',
            'Drought',
            'Other Disaster',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Assistance Types
    |--------------------------------------------------------------------------
    */

    private function assistanceTypes(): array
    {
        return [
            'Cash Assistance',
            'Food Assistance',
            'Relief Goods',
            'Emergency Supplies',
            'Medical Assistance',
            'Other Assistance',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Statuses
    |--------------------------------------------------------------------------
    |
    | These values MUST match the disaster_events.status database enum:
    |
    | draft
    | active
    | closed
    | cancelled
    |
    */

    private function statuses(): array
    {
        return [
            'draft',
            'active',
            'closed',
            'cancelled',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        $search = trim(
            (string) $request->input('search', '')
        );

        $status = trim(
            (string) $request->input('status', '')
        );

        $type = trim(
            (string) $request->input('type', '')
        );


        $disasterEvents = DisasterEvent::query()

            ->when(
                $search !== '',
                function ($query) use ($search) {

                    $query->where(function ($searchQuery) use ($search) {

                        $searchQuery
                            ->where(
                                'event_code',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'name',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'type',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'barangay',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'municipality',
                                'like',
                                '%' . $search . '%'
                            )
                            ->orWhere(
                                'province',
                                'like',
                                '%' . $search . '%'
                            );
                    });
                }
            )

            ->when(
                $status !== '',
                function ($query) use ($status) {

                    $query->where(
                        'status',
                        $status
                    );
                }
            )

            ->when(
                $type !== '',
                function ($query) use ($type) {

                    $query->where(
                        'type',
                        $type
                    );
                }
            )

            ->withCount('assessments')

            ->latest('id')

            ->paginate(10)

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalEvents = DisasterEvent::count();

        $activeEvents = DisasterEvent::where(
            'status',
            'active'
        )->count();

        $completedEvents = DisasterEvent::where(
            'status',
            'closed'
        )->count();

        $cancelledEvents = DisasterEvent::where(
            'status',
            'cancelled'
        )->count();


        /*
        |--------------------------------------------------------------------------
        | Available Filter Options
        |--------------------------------------------------------------------------
        */

        $disasterTypes = $this->disasterTypes();

        $statuses = $this->statuses();


        return view(
            'disaster-events.index',
            compact(
                'disasterEvents',
                'totalEvents',
                'activeEvents',
                'completedEvents',
                'cancelledEvents',
                'search',
                'status',
                'type',
                'disasterTypes',
                'statuses'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): View
    {
        return view(
            'disaster-events.create',
            [
                'disasterTypes' =>
                    $this->disasterTypes(),

                'assistanceTypes' =>
                    $this->assistanceTypes(),

                'statuses' =>
                    $this->statuses(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'type' => [
                'required',
                'string',
                'max:100',
                'in:' . implode(
                    ',',
                    $this->disasterTypes()
                ),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'status' => [
                'required',
                'in:' . implode(
                    ',',
                    $this->statuses()
                ),
            ],

            'assistance_type' => [
                'required',
                'string',
                'max:100',
                'in:' . implode(
                    ',',
                    $this->assistanceTypes()
                ),
            ],

            'assistance_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'barangay' => [
                'required',
                'string',
                'max:150',
            ],

            'municipality' => [
                'required',
                'string',
                'max:150',
            ],

            'province' => [
                'required',
                'string',
                'max:150',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Generate Event Code
        |--------------------------------------------------------------------------
        */

        $validated['event_code'] =
            $this->generateEventCode();


        /*
        |--------------------------------------------------------------------------
        | Creator / Updater
        |--------------------------------------------------------------------------
        */

        $userId = $request
            ->user()
            ?->getAuthIdentifier();


        $validated['created_by'] = $userId;

        $validated['updated_by'] = $userId;


        /*
        |--------------------------------------------------------------------------
        | Create Disaster Event
        |--------------------------------------------------------------------------
        */

        $disasterEvent =
            DisasterEvent::create(
                $validated
            );


        return redirect()
            ->route(
                'disaster-events.index'
            )
            ->with(
                'success',
                'Disaster event "' .
                $disasterEvent->name .
                '" was successfully created.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    |
    | Main disaster event dashboard.
    |
    */

    public function show(
        DisasterEvent $disasterEvent
    ): View {

        /*
        |--------------------------------------------------------------------------
        | Load Event Relationships
        |--------------------------------------------------------------------------
        */

        $disasterEvent->load([
            'assessments.household.householdHead',
            'assessments.household.members',
            'assessments.assessor',
            'assessments.verifier',
            'assessments.reliefVoucher',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Total Assessments
        |--------------------------------------------------------------------------
        */

        $totalAssessments =
            $disasterEvent->assessments->count();


        /*
        |--------------------------------------------------------------------------
        | Verification Statistics
        |--------------------------------------------------------------------------
        */

        $verifiedAssessments =
            $disasterEvent->assessments
                ->where(
                    'verification_status',
                    'Verified'
                )
                ->count();


        $pendingAssessments =
            $disasterEvent->assessments
                ->where(
                    'verification_status',
                    'Pending'
                )
                ->count();


        $rejectedAssessments =
            $disasterEvent->assessments
                ->where(
                    'verification_status',
                    'Rejected'
                )
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Damage Statistics
        |--------------------------------------------------------------------------
        */

        $unaffectedAssessments =
            $disasterEvent->assessments
                ->filter(function ($assessment) {

                    return in_array(
                        strtolower(
                            trim(
                                (string) $assessment->damage_level
                            )
                        ),
                        [
                            'none',
                            'no damage',
                            'unaffected',
                        ],
                        true
                    );

                })
                ->count();


        $moderateAssessments =
            $disasterEvent->assessments
                ->filter(function ($assessment) {

                    return strtolower(
                        trim(
                            (string) $assessment->damage_level
                        )
                    ) === 'moderate';

                })
                ->count();


        $severeAssessments =
            $disasterEvent->assessments
                ->filter(function ($assessment) {

                    return in_array(
                        strtolower(
                            trim(
                                (string) $assessment->damage_level
                            )
                        ),
                        [
                            'severe',
                            'major',
                            'destroyed',
                        ],
                        true
                    );

                })
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Voucher Eligibility
        |--------------------------------------------------------------------------
        |
        | Official eligibility requires:
        |
        | 1. Assessment is Verified.
        | 2. voucher_eligible is true.
        |
        */

        $voucherEligibleAssessments =
            $disasterEvent->assessments
                ->filter(function ($assessment) {

                    return $assessment->verification_status === 'Verified'
                        && (bool) $assessment->voucher_eligible === true;

                })
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Voucher Statistics
        |--------------------------------------------------------------------------
        |
        | Current voucher workflow:
        |
        | ISSUED -> CLAIMED
        |
        */

        $voucherIssued =
            $disasterEvent->assessments
                ->filter(function ($assessment) {

                    return $assessment->reliefVoucher
                        && strtolower(
                            (string) $assessment->reliefVoucher->status
                        ) === 'issued';

                })
                ->count();


        $voucherClaimed =
            $disasterEvent->assessments
                ->filter(function ($assessment) {

                    return $assessment->reliefVoucher
                        && strtolower(
                            (string) $assessment->reliefVoucher->status
                        ) === 'claimed';

                })
                ->count();


        /*
        |--------------------------------------------------------------------------
        | Legacy Voucher Statistics
        |--------------------------------------------------------------------------
        |
        | Existing Blade views may still reference these variables.
        |
        | The current voucher workflow does not use Expired or Cancelled
        | voucher statuses.
        |
        */

        $voucherExpired = 0;

        $voucherCancelled = 0;


        /*
        |--------------------------------------------------------------------------
        | Assessment Coverage
        |--------------------------------------------------------------------------
        |
        | We do not calculate a false barangay-wide percentage because
        | this event does not necessarily contain every household.
        |
        */

        $assessmentCoverage = null;


        /*
        |--------------------------------------------------------------------------
        | Candidate Beneficiaries
        |--------------------------------------------------------------------------
        */

        $beneficiaryAssessments =
            $disasterEvent->assessments
                ->filter(function ($assessment) {

                    return $assessment->verification_status === 'Verified'
                        && (bool) $assessment->voucher_eligible === true;

                })
                ->sortByDesc('id')
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Assessments Requiring Verification
        |--------------------------------------------------------------------------
        */

        $forVerificationAssessments =
            $disasterEvent->assessments
                ->filter(function ($assessment) {

                    return $assessment->verification_status === 'Pending';

                })
                ->sortByDesc('id')
                ->values();


        /*
        |--------------------------------------------------------------------------
        | All Assessments
        |--------------------------------------------------------------------------
        */

        $assessments =
            $disasterEvent->assessments
                ->sortByDesc('id')
                ->values();


        /*
        |--------------------------------------------------------------------------
        | Return Disaster Event Dashboard
        |--------------------------------------------------------------------------
        */

        return view(
            'disaster-events.show',
            compact(
                'disasterEvent',

                'assessments',

                'totalAssessments',

                'verifiedAssessments',

                'pendingAssessments',

                'rejectedAssessments',

                'unaffectedAssessments',

                'moderateAssessments',

                'severeAssessments',

                'voucherEligibleAssessments',

                'voucherIssued',

                'voucherClaimed',

                'voucherExpired',

                'voucherCancelled',

                'assessmentCoverage',

                'beneficiaryAssessments',

                'forVerificationAssessments'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        DisasterEvent $disasterEvent
    ): View {

        return view(
            'disaster-events.edit',
            [
                'disasterEvent' =>
                    $disasterEvent,

                'disasterTypes' =>
                    $this->disasterTypes(),

                'assistanceTypes' =>
                    $this->assistanceTypes(),

                'statuses' =>
                    $this->statuses(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        DisasterEvent $disasterEvent
    ): RedirectResponse {

        $validated = $request->validate([

            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'type' => [
                'required',
                'string',
                'max:100',
                'in:' . implode(
                    ',',
                    $this->disasterTypes()
                ),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'start_date' => [
                'required',
                'date',
            ],

            'end_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'status' => [
                'required',
                'in:' . implode(
                    ',',
                    $this->statuses()
                ),
            ],

            'assistance_type' => [
                'required',
                'string',
                'max:100',
                'in:' . implode(
                    ',',
                    $this->assistanceTypes()
                ),
            ],

            'assistance_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:9999999999.99',
            ],

            'barangay' => [
                'required',
                'string',
                'max:150',
            ],

            'municipality' => [
                'required',
                'string',
                'max:150',
            ],

            'province' => [
                'required',
                'string',
                'max:150',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Updated By
        |--------------------------------------------------------------------------
        */

        $validated['updated_by'] =
            $request
                ->user()
                ?->getAuthIdentifier();


        /*
        |--------------------------------------------------------------------------
        | Update Disaster Event
        |--------------------------------------------------------------------------
        */

        $disasterEvent->update(
            $validated
        );


        return redirect()
            ->route(
                'disaster-events.index'
            )
            ->with(
                'success',
                'Disaster event "' .
                $disasterEvent->name .
                '" was successfully updated.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    |
    | Disaster events are not permanently deleted.
    |
    | The record is retained and marked as cancelled.
    |
    */

    public function destroy(
        Request $request,
        DisasterEvent $disasterEvent
    ): RedirectResponse {

        /*
        |--------------------------------------------------------------------------
        | Do Not Cancel Again
        |--------------------------------------------------------------------------
        */

        if (
            strtolower(
                (string) $disasterEvent->status
            ) === 'cancelled'
        ) {
            return back()->with(
                'error',
                'This disaster event is already cancelled.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Cancel Event
        |--------------------------------------------------------------------------
        */

        $disasterEvent->update([
            'status' => 'cancelled',

            'updated_by' =>
                $request
                    ->user()
                    ?->getAuthIdentifier(),
        ]);


        return back()->with(
            'success',
            'Disaster event "' .
            $disasterEvent->name .
            '" was cancelled. The historical record was retained.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Generate Event Code
    |--------------------------------------------------------------------------
    |
    | Format:
    |
    | DIS-2026-000001
    |
    */

    private function generateEventCode(): string
    {
        $year = now()->year;


        $lastEvent = DisasterEvent::query()
            ->where(
                'event_code',
                'like',
                'DIS-' . $year . '-%'
            )
            ->latest('id')
            ->first();


        if ($lastEvent) {

            $lastCode = (string) $lastEvent->event_code;

            $lastNumber = (int) substr(
                $lastCode,
                -6
            );

            $nextNumber = $lastNumber + 1;

        } else {

            $nextNumber = 1;
        }


        /*
        |--------------------------------------------------------------------------
        | Ensure Uniqueness
        |--------------------------------------------------------------------------
        */

        do {

            $eventCode =
                'DIS-' .
                $year .
                '-' .
                str_pad(
                    (string) $nextNumber,
                    6,
                    '0',
                    STR_PAD_LEFT
                );

            $nextNumber++;

        } while (
            DisasterEvent::where(
                'event_code',
                $eventCode
            )->exists()
        );


        return $eventCode;
    }
}
