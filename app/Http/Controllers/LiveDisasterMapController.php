<?php

namespace App\Http\Controllers;

use App\Models\DisasterAssessment;
use App\Models\DisasterEvent;
use App\Models\Household;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveDisasterMapController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Live Disaster Map
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Disaster Events
        |--------------------------------------------------------------------------
        */

        $disasterEvents = DisasterEvent::query()
            ->where('status', '!=', 'Cancelled')
            ->latest('start_date')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Selected Disaster Event
        |--------------------------------------------------------------------------
        */

        $selectedEventId = $request->integer(
            'disaster_event_id'
        );


        /*
        |--------------------------------------------------------------------------
        | If no event was selected, use the latest event
        |--------------------------------------------------------------------------
        */

        if (
            !$selectedEventId &&
            $disasterEvents->isNotEmpty()
        ) {
            $selectedEventId =
                $disasterEvents->first()->id;
        }


        /*
        |--------------------------------------------------------------------------
        | Selected Event
        |--------------------------------------------------------------------------
        */

        $selectedEvent = null;

        if ($selectedEventId) {

            $selectedEvent =
                $disasterEvents->firstWhere(
                    'id',
                    $selectedEventId
                );
        }


        /*
        |--------------------------------------------------------------------------
        | Households With Coordinates
        |--------------------------------------------------------------------------
        */

        $households = Household::query()
            ->with([
                'householdHead',
                'members.resident',
            ])
            ->where('status', 'Active')
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->orderBy('household_number')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Assessments For Selected Disaster
        |--------------------------------------------------------------------------
        */

        $assessments = collect();

        if ($selectedEvent) {

            $assessments =
                DisasterAssessment::query()
                    ->with([
                        'household.householdHead',
                        'disasterEvent',
                        'assessor',
                        'verifier',
                        'reliefVoucher',
                    ])
                    ->where(
                        'disaster_event_id',
                        $selectedEvent->id
                    )
                    ->get()
                    ->keyBy('household_id');
        }


        /*
        |--------------------------------------------------------------------------
        | Map Data
        |--------------------------------------------------------------------------
        */

        $mapHouseholds = $households->map(
            function ($household) use ($assessments) {

                $assessment =
                    $assessments->get(
                        $household->id
                    );


                /*
                |--------------------------------------------------------------------------
                | Default Status
                |--------------------------------------------------------------------------
                */

                $mapStatus = 'Not Assessed';

                $statusClass = 'not-assessed';


                /*
                |--------------------------------------------------------------------------
                | Assessment Status
                |--------------------------------------------------------------------------
                */

                if ($assessment) {

                    if (
                        $assessment->verification_status ===
                        'Verified'
                    ) {

                        if (
                            $assessment->voucher_eligible
                        ) {

                            $mapStatus =
                                'Verified & Voucher Eligible';

                            $statusClass =
                                'verified-eligible';

                        } else {

                            $mapStatus =
                                'Verified';

                            $statusClass =
                                'verified';
                        }

                    } elseif (
                        $assessment->verification_status ===
                        'Rejected'
                    ) {

                        $mapStatus =
                            'Rejected';

                        $statusClass =
                            'rejected';

                    } elseif (
                        $assessment->damage_level ===
                        'Severe' ||
                        $assessment->damage_level ===
                        'Total'
                    ) {

                        $mapStatus =
                            'Severe / Total Damage';

                        $statusClass =
                            'severe';

                    } elseif (
                        $assessment->damage_level ===
                        'Moderate'
                    ) {

                        $mapStatus =
                            'Moderate Damage';

                        $statusClass =
                            'moderate';

                    } elseif (
                        $assessment->damage_level ===
                        'Minor'
                    ) {

                        $mapStatus =
                            'Minor Damage';

                        $statusClass =
                            'minor';

                    } else {

                        $mapStatus =
                            'Assessed - No Damage';

                        $statusClass =
                            'no-damage';
                    }
                }


                /*
                |--------------------------------------------------------------------------
                | Household Head
                |--------------------------------------------------------------------------
                */

                $headName =
                    $household->householdHead
                        ? $household->householdHead->full_name
                        : 'No household head';


                /*
                |--------------------------------------------------------------------------
                | Member Count
                |--------------------------------------------------------------------------
                */

                $memberCount =
                    $household->members->count();


                return [

                    'id' =>
                        $household->id,

                    'household_number' =>
                        $household->household_number,

                    'head_name' =>
                        $headName,

                    'address' =>
                        $household->full_address,

                    'purok' =>
                        $household->purok,

                    'member_count' =>
                        $memberCount,

                    'latitude' =>
                        (float) $household->latitude,

                    'longitude' =>
                        (float) $household->longitude,

                    'status' =>
                        $mapStatus,

                    'status_class' =>
                        $statusClass,

                    'assessment' =>
                        $assessment
                            ? [
                                'id' =>
                                    $assessment->id,

                                'damage_level' =>
                                    $assessment->damage_level,

                                'verification_status' =>
                                    $assessment->verification_status,

                                'potential_eligibility' =>
                                    $assessment->potential_eligibility,

                                'eligibility_reason' =>
                                    $assessment->eligibility_reason,

                                'voucher_eligible' =>
                                    (bool) $assessment->voucher_eligible,

                                'house_damaged' =>
                                    (bool) $assessment->house_damaged,

                                'house_uninhabitable' =>
                                    (bool) $assessment->house_uninhabitable,

                                'livelihood_affected' =>
                                    (bool) $assessment->livelihood_affected,

                                'evacuated' =>
                                    (bool) $assessment->evacuated,

                                'assessment_notes' =>
                                    $assessment->assessment_notes,
                            ]
                            : null,
                ];
            }
        )->values();


        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $totalHouseholds =
            Household::where(
                'status',
                'Active'
            )->count();


        $mappedHouseholds =
            Household::where(
                'status',
                'Active'
            )
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->count();


        $unmappedHouseholds =
            $totalHouseholds -
            $mappedHouseholds;


        $assessedCount =
            $assessments->count();


        $verifiedCount =
            $assessments
                ->where(
                    'verification_status',
                    'Verified'
                )
                ->count();


        $voucherEligibleCount =
            $assessments
                ->where(
                    'verification_status',
                    'Verified'
                )
                ->where(
                    'voucher_eligible',
                    true
                )
                ->count();


        $severeCount =
            $assessments
                ->whereIn(
                    'damage_level',
                    [
                        'Severe',
                        'Total',
                    ]
                )
                ->count();


        return view(
            'live-disaster-map.index',
            compact(
                'disasterEvents',
                'selectedEvent',
                'selectedEventId',
                'mapHouseholds',
                'totalHouseholds',
                'mappedHouseholds',
                'unmappedHouseholds',
                'assessedCount',
                'verifiedCount',
                'voucherEligibleCount',
                'severeCount'
            )
        );
    }
}
