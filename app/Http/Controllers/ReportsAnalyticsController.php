<?php

namespace App\Http\Controllers;

use App\Models\Household;
use App\Models\ReliefVoucher;
use App\Models\ReportComplaint;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportsAnalyticsController extends Controller
{
    /**
     * Display Reports & Analytics.
     *
     * The Analytics page supports an optional reporting period.
     *
     * IMPORTANT:
     * - Resident and household master statistics represent the
     *   current records in the system.
     * - Transaction/activity statistics can be filtered by date.
     * - If no date range is supplied, all available records are used.
     */
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | REPORTING PERIOD
        |--------------------------------------------------------------------------
        |
        | The date filter is optional.
        |
        | If the Analytics page sends:
        | ?from=2026-09-01&to=2026-09-30
        |
        | the activity-based statistics will use that period.
        |
        */

        $from = null;
        $to = null;

        if ($request->filled('from')) {
            try {
                $from = Carbon::parse(
                    $request->input('from')
                )->startOfDay();
            } catch (\Throwable $exception) {
                $from = null;
            }
        }

        if ($request->filled('to')) {
            try {
                $to = Carbon::parse(
                    $request->input('to')
                )->endOfDay();
            } catch (\Throwable $exception) {
                $to = null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Prevent an invalid reversed date range
        |--------------------------------------------------------------------------
        */

        if ($from && $to && $from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }


        /*
        |--------------------------------------------------------------------------
        | RESIDENT STATISTICS
        |--------------------------------------------------------------------------
        |
        | Residents are master records.
        |
        | These values represent the current resident registry and therefore
        | are NOT restricted by the reporting period.
        |
        */

        $residentStats = [
            'total' => Resident::count(),

            'active' => Resident::where(
                'status',
                'Active'
            )->count(),

            'archived' => Resident::where(
                'status',
                'Archived'
            )->count(),

            'male' => Resident::where(
                'sex',
                'Male'
            )->count(),

            'female' => Resident::where(
                'sex',
                'Female'
            )->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | HOUSEHOLD STATISTICS
        |--------------------------------------------------------------------------
        |
        | Households are also master records.
        |
        | These values represent the current household registry.
        |
        */

        $householdStats = [
            'total' => Household::count(),

            'active' => Household::where(
                'status',
                'Active'
            )->count(),

            'inactive' => Household::where(
                'status',
                '!=',
                'Active'
            )->count(),

            'mapped' => Household::whereNotNull(
                'latitude'
            )
                ->whereNotNull('longitude')
                ->count(),

            'unmapped' => Household::where(function ($query) {
                $query
                    ->whereNull('latitude')
                    ->orWhereNull('longitude');
            })->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | SEX DISTRIBUTION
        |--------------------------------------------------------------------------
        |
        | Uses the current resident registry.
        |
        */

        $sexDistribution = Resident::select(
            'sex',
            DB::raw('COUNT(*) as total')
        )
            ->whereNotNull('sex')
            ->where('sex', '!=', '')
            ->groupBy('sex')
            ->orderBy('sex')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | AGE DISTRIBUTION
        |--------------------------------------------------------------------------
        |
        | Age is calculated from birth_date.
        |
        | The categories are intentionally aligned with the Analytics
        | classification used throughout the system.
        |
        */

        $ageDistribution = [
            [
                'label' => 'Children (0-12)',
                'total' => 0,
            ],

            [
                'label' => 'Teenagers (13-17)',
                'total' => 0,
            ],

            [
                'label' => 'Youth (18-24)',
                'total' => 0,
            ],

            [
                'label' => 'Adults (25-59)',
                'total' => 0,
            ],

            [
                'label' => 'Senior Citizens (60+)',
                'total' => 0,
            ],
        ];


        $residentsWithBirthDate = Resident::query()
            ->whereNotNull('birth_date')
            ->get([
                'birth_date',
            ]);


        foreach ($residentsWithBirthDate as $resident) {
            if (! $resident->birth_date) {
                continue;
            }

            try {
                /*
                 * Carbon::today() is used by the age accessor so that
                 * the age represents the resident's current age.
                 */
                $birthDate = Carbon::parse(
                    $resident->birth_date
                );

                $age = $birthDate->age;
            } catch (\Throwable $exception) {
                continue;
            }


            if ($age <= 12) {
                $ageDistribution[0]['total']++;
            } elseif ($age <= 17) {
                $ageDistribution[1]['total']++;
            } elseif ($age <= 24) {
                $ageDistribution[2]['total']++;
            } elseif ($age <= 59) {
                $ageDistribution[3]['total']++;
            } else {
                $ageDistribution[4]['total']++;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | HOUSEHOLDS BY PUROK
        |--------------------------------------------------------------------------
        |
        | Uses the current household registry.
        |
        */

        $householdsByPurok = Household::select(
            'purok',
            DB::raw('COUNT(*) as total')
        )
            ->whereNotNull('purok')
            ->where('purok', '!=', '')
            ->groupBy('purok')
            ->orderBy('purok')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | POPULATION BY PUROK
        |--------------------------------------------------------------------------
        |
        | Counts unique residents currently assigned to households.
        |
        */

        $populationByPurok = DB::table(
            'household_members'
        )
            ->join(
                'households',
                'household_members.household_id',
                '=',
                'households.id'
            )
            ->join(
                'residents',
                'household_members.resident_id',
                '=',
                'residents.id'
            )
            ->select(
                'households.purok',
                DB::raw(
                    'COUNT(DISTINCT residents.id) as total'
                )
            )
            ->whereNotNull('households.purok')
            ->where('households.purok', '!=', '')
            ->groupBy('households.purok')
            ->orderBy('households.purok')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | REPORTS & COMPLAINTS STATISTICS
        |--------------------------------------------------------------------------
        |
        | Complaints are activity/case records.
        |
        | When a date range is selected, date_reported is used.
        |
        */

        $complaintQuery = ReportComplaint::query();

        if ($from && $to) {
            $complaintQuery->whereBetween(
                'date_reported',
                [
                    $from->toDateString(),
                    $to->toDateString(),
                ]
            );
        }


        $complaintRecords = $complaintQuery->get();


        $complaintStats = [
            'total' => $complaintRecords->count(),

            'active' => $complaintRecords
                ->where('is_archived', false)
                ->whereNotIn(
                    'status',
                    [
                        'Resolved',
                        'Closed',
                    ]
                )
                ->count(),

            'resolved' => $complaintRecords
                ->where('status', 'Resolved')
                ->count(),

            'closed' => $complaintRecords
                ->where('status', 'Closed')
                ->count(),

            'confidential' => $complaintRecords
                ->where('is_confidential', true)
                ->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | COMPLAINTS BY TYPE
        |--------------------------------------------------------------------------
        */

        $complaintsByTypeQuery = ReportComplaint::select(
            'report_type',
            DB::raw('COUNT(*) as total')
        )
            ->whereNotNull('report_type')
            ->where('report_type', '!=', '');


        if ($from && $to) {
            $complaintsByTypeQuery->whereBetween(
                'date_reported',
                [
                    $from->toDateString(),
                    $to->toDateString(),
                ]
            );
        }


        $complaintsByType = $complaintsByTypeQuery
            ->groupBy('report_type')
            ->orderByDesc('total')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | COMPLAINTS BY CATEGORY
        |--------------------------------------------------------------------------
        */

        $complaintsByCategoryQuery = ReportComplaint::select(
            'category',
            DB::raw('COUNT(*) as total')
        )
            ->whereNotNull('category')
            ->where('category', '!=', '');


        if ($from && $to) {
            $complaintsByCategoryQuery->whereBetween(
                'date_reported',
                [
                    $from->toDateString(),
                    $to->toDateString(),
                ]
            );
        }


        $complaintsByCategory = $complaintsByCategoryQuery
            ->groupBy('category')
            ->orderByDesc('total')
            ->limit(10)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RELIEF VOUCHER STATISTICS
        |--------------------------------------------------------------------------
        |
        | Relief vouchers use distribution_schedule as the reporting date.
        |
        | Existing workflow statuses:
        |
        | issued
        | claimed
        | cancelled
        |
        */

        $voucherQuery = ReliefVoucher::query();

        if ($from && $to) {
            $voucherQuery->whereBetween(
                'distribution_schedule',
                [
                    $from->toDateString(),
                    $to->toDateString(),
                ]
            );
        }


        $voucherRecords = $voucherQuery->get();


        $voucherStats = [
            'total' => $voucherRecords->count(),

            'issued' => $voucherRecords
                ->where('status', 'issued')
                ->count(),

            'claimed' => $voucherRecords
                ->where('status', 'claimed')
                ->count(),

            'cancelled' => $voucherRecords
                ->where('status', 'cancelled')
                ->count(),

            'pending' => $voucherRecords
                ->whereNotIn(
                    'status',
                    [
                        'issued',
                        'claimed',
                        'cancelled',
                    ]
                )
                ->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | VOUCHERS BY STATUS
        |--------------------------------------------------------------------------
        */

        $vouchersByStatusQuery = ReliefVoucher::select(
            'status',
            DB::raw('COUNT(*) as total')
        )
            ->whereNotNull('status')
            ->where('status', '!=', '');


        if ($from && $to) {
            $vouchersByStatusQuery->whereBetween(
                'distribution_schedule',
                [
                    $from->toDateString(),
                    $to->toDateString(),
                ]
            );
        }


        $vouchersByStatus = $vouchersByStatusQuery
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RECENT RELIEF VOUCHERS
        |--------------------------------------------------------------------------
        |
        | Shows the latest five vouchers.
        |
        | If a reporting period is selected, only vouchers within that
        | period are shown.
        |
        */

        $recentVouchersQuery = ReliefVoucher::with(
            'household'
        );


        if ($from && $to) {
            $recentVouchersQuery->whereBetween(
                'distribution_schedule',
                [
                    $from->toDateString(),
                    $to->toDateString(),
                ]
            );
        }


        $recentVouchers = $recentVouchersQuery
            ->latest('created_at')
            ->limit(5)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | RETURN ANALYTICS VIEW
        |--------------------------------------------------------------------------
        |
        | The selected dates are also passed to the Blade view so that
        | the filter interface can preserve the user's selection.
        |
        */

        return view(
            'reports-analytics.index',
            compact(
                'residentStats',
                'householdStats',
                'sexDistribution',
                'ageDistribution',
                'householdsByPurok',
                'populationByPurok',
                'complaintStats',
                'complaintsByType',
                'complaintsByCategory',
                'voucherStats',
                'vouchersByStatus',
                'recentVouchers',
                'from',
                'to'
            )
        );
    }
}
