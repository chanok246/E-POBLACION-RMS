<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\Household;
use App\Models\ReliefVoucher;
use App\Models\ReportComplaint;
use App\Models\Resident;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SummaryReportController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Available Summary Reports
    |--------------------------------------------------------------------------
    */

    private function availableReports(): array
    {
        return [
            'population' => 'Population Summary',
            'households' => 'Household Summary',
            'documents' => 'Barangay Documents Summary',
            'complaints' => 'Reports & Complaints Summary',
            'relief' => 'Relief Assistance Summary',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Main Summary Reports Page
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): View|RedirectResponse
    {
        $today = Carbon::today();

        /*
        |--------------------------------------------------------------------------
        | Reporting Period
        |--------------------------------------------------------------------------
        */

        try {
            $from = $request->filled('from')
                ? Carbon::parse($request->input('from'))->startOfDay()
                : $today->copy()->startOfMonth();

            $to = $request->filled('to')
                ? Carbon::parse($request->input('to'))->endOfDay()
                : $today->copy()->endOfDay();
        } catch (\Throwable $e) {
            $from = $today->copy()->startOfMonth();
            $to = $today->copy()->endOfDay();
        }


        /*
        |--------------------------------------------------------------------------
        | Selected Reports
        |--------------------------------------------------------------------------
        */

        $selectedReports = $request->input(
            'reports',
            []
        );

        if (!is_array($selectedReports)) {
            $selectedReports = [$selectedReports];
        }

        $selectedReports = array_values(
            array_intersect(
                $selectedReports,
                array_keys($this->availableReports())
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Generate / Preview State
        |--------------------------------------------------------------------------
        */

        $preview = $request->boolean('preview')
            || $request->boolean('generate');

        $generated = $request->boolean('generate');


        /*
        |--------------------------------------------------------------------------
        | Validate Reporting Period
        |--------------------------------------------------------------------------
        */

        if ($from->greaterThan($to)) {

            return redirect()
                ->route('summary-reports.index')
                ->withErrors([
                    'period' => 'The From date cannot be later than the To date.',
                ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Build Selected Report Previews
        |--------------------------------------------------------------------------
        */

        $reports = [];

        if ($preview && !empty($selectedReports)) {

            foreach ($selectedReports as $report) {

                $reports[$report] = $this->buildReport(
                    $report,
                    $from,
                    $to
                );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view('summary-reports.index', [
            'reportTypes' => $this->availableReports(),

            'selectedReports' => $selectedReports,

            'from' => $from,

            'to' => $to,

            'reports' => $reports,

            'generated' => $generated,

            'preview' => $preview,

            'printMode' => false,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Print Selected Reports
    |--------------------------------------------------------------------------
    */

    public function print(Request $request): Response
    {
        $today = Carbon::today();

        try {
            $from = $request->filled('from')
                ? Carbon::parse($request->input('from'))->startOfDay()
                : $today->copy()->startOfMonth();

            $to = $request->filled('to')
                ? Carbon::parse($request->input('to'))->endOfDay()
                : $today->copy()->endOfDay();
        } catch (\Throwable $e) {
            abort(
                422,
                'Invalid reporting period.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Selected Reports
        |--------------------------------------------------------------------------
        */

        $selectedReports = $request->input(
            'reports',
            []
        );

        if (!is_array($selectedReports)) {
            $selectedReports = [$selectedReports];
        }

        $selectedReports = array_values(
            array_intersect(
                $selectedReports,
                array_keys($this->availableReports())
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */

        $this->validatePeriod(
            $from,
            $to
        );

        if (empty($selectedReports)) {

            abort(
                422,
                'No report type was selected.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Build Reports
        |--------------------------------------------------------------------------
        */

        $reports = [];

        foreach ($selectedReports as $report) {

            $reports[$report] = $this->buildReport(
                $report,
                $from,
                $to
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Print View
        |--------------------------------------------------------------------------
        */

        return response()->view(
            'summary-reports.index',
            [
                'reportTypes' => $this->availableReports(),

                'selectedReports' => $selectedReports,

                'from' => $from,

                'to' => $to,

                'reports' => $reports,

                'generated' => true,

                'preview' => true,

                'printMode' => true,
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Reporting Period
    |--------------------------------------------------------------------------
    */

    private function validatePeriod(
        Carbon $from,
        Carbon $to
    ): void {

        if ($from->greaterThan($to)) {

            abort(
                422,
                'The From date cannot be later than the To date.'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Build Individual Report
    |--------------------------------------------------------------------------
    */

    private function buildReport(
        string $report,
        Carbon $from,
        Carbon $to
    ): array {

        return match ($report) {

            'population' =>
                $this->populationSummary(
                    $from,
                    $to
                ),

            'households' =>
                $this->householdSummary(
                    $from,
                    $to
                ),

            'documents' =>
                $this->documentSummary(
                    $from,
                    $to
                ),

            'complaints' =>
                $this->complaintSummary(
                    $from,
                    $to
                ),

            'relief' =>
                $this->reliefSummary(
                    $from,
                    $to
                ),

            default => [],
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Population Summary
    |--------------------------------------------------------------------------
    */

    private function populationSummary(
        Carbon $from,
        Carbon $to
    ): array {

        $records = Resident::query()
            ->whereBetween(
                'created_at',
                [
                    $from,
                    $to,
                ]
            )
            ->get();


        $sexCounts = [
            'male' => 0,
            'female' => 0,
            'other' => 0,
        ];


        $ageGroups = [
            '0–12' => 0,
            '13–17' => 0,
            '18–59' => 0,
            '60+' => 0,
            'Unknown' => 0,
        ];


        /*
        |--------------------------------------------------------------------------
        | Age Reference Date
        |--------------------------------------------------------------------------
        |
        | Age is not stored as a database column in the Resident table.
        | It is calculated from birth_date.
        |
        | We use the report's To date as the reference date so the
        | age distribution corresponds to the selected reporting period.
        |
        */

        $ageReferenceDate = $to->copy();


        foreach ($records as $resident) {

            $sex = strtolower(
                trim(
                    (string) $resident->sex
                )
            );


            if (
                in_array(
                    $sex,
                    [
                        'male',
                        'm',
                    ],
                    true
                )
            ) {

                $sexCounts['male']++;

            } elseif (
                in_array(
                    $sex,
                    [
                        'female',
                        'f',
                    ],
                    true
                )
            ) {

                $sexCounts['female']++;

            } else {

                $sexCounts['other']++;
            }


            /*
            |--------------------------------------------------------------------------
            | Calculate Age From Birth Date
            |--------------------------------------------------------------------------
            */

            if (empty($resident->birth_date)) {

                $ageGroups['Unknown']++;

                continue;
            }


            try {

                $birthDate = Carbon::parse(
                    $resident->birth_date
                );

                $age = $birthDate->age;

                /*
                |--------------------------------------------------------------------------
                | Use the report period's end date instead of today's date.
                |--------------------------------------------------------------------------
                */

                $age = $birthDate->diffInYears(
                    $ageReferenceDate
                );

            } catch (\Throwable $e) {

                $ageGroups['Unknown']++;

                continue;
            }


            if ($age <= 12) {

                $ageGroups['0–12']++;

            } elseif ($age <= 17) {

                $ageGroups['13–17']++;

            } elseif ($age <= 59) {

                $ageGroups['18–59']++;

            } else {

                $ageGroups['60+']++;
            }
        }


        return [

            'total' =>
                $records->count(),

            'active' =>
                $records
                    ->where(
                        'status',
                        'Active'
                    )
                    ->count(),

            'archived' =>
                $records
                    ->where(
                        'status',
                        'Archived'
                    )
                    ->count(),

            'male' =>
                $sexCounts['male'],

            'female' =>
                $sexCounts['female'],

            'other' =>
                $sexCounts['other'],

            'age_groups' =>
                $ageGroups,

            'current_total' =>
                Resident::count(),

            'current_active' =>
                Resident::where(
                    'status',
                    'Active'
                )->count(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Household Summary
    |--------------------------------------------------------------------------
    */

    private function householdSummary(
        Carbon $from,
        Carbon $to
    ): array {

        $records = Household::query()
            ->whereBetween(
                'created_at',
                [
                    $from,
                    $to,
                ]
            )
            ->get();


        $byPurok = $records
            ->groupBy(
                fn ($household) =>
                    $household->purok
                    ?: 'Unassigned'
            )
            ->map(
                fn ($items) =>
                    $items->count()
            )
            ->sortKeys()
            ->all();


        $mapped = $records
            ->filter(
                fn ($household) =>
                    $household->latitude !== null
                    && $household->longitude !== null
            )
            ->count();


        return [

            'total' =>
                $records->count(),

            'active' =>
                $records
                    ->where(
                        'status',
                        'Active'
                    )
                    ->count(),

            'archived' =>
                $records
                    ->where(
                        'status',
                        'Archived'
                    )
                    ->count(),

            'mapped' =>
                $mapped,

            'unmapped' =>
                max(
                    0,
                    $records->count() - $mapped
                ),

            'by_purok' =>
                $byPurok,

            'current_total' =>
                Household::count(),

            'current_active' =>
                Household::where(
                    'status',
                    'Active'
                )->count(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Barangay Documents Summary
    |--------------------------------------------------------------------------
    */

    private function documentSummary(
        Carbon $from,
        Carbon $to
    ): array {

        $records = DocumentRequest::query()
            ->whereBetween(
                'requested_at',
                [
                    $from,
                    $to,
                ]
            )
            ->get();


        $byType = $records
            ->groupBy(
                fn ($record) =>
                    $record->document_type
                    ?: 'Unspecified'
            )
            ->map(
                fn ($items) =>
                    $items->count()
            )
            ->sortKeys()
            ->all();


        $byStatus = $records
            ->groupBy(
                fn ($record) =>
                    $record->status
                    ?: 'Unspecified'
            )
            ->map(
                fn ($items) =>
                    $items->count()
            )
            ->sortKeys()
            ->all();


        $byWorkflow = $records
            ->groupBy(
                fn ($record) =>
                    $record->workflow_stage
                    ?: 'Unspecified'
            )
            ->map(
                fn ($items) =>
                    $items->count()
            )
            ->sortKeys()
            ->all();


        return [

            'total' =>
                $records->count(),

            'processed' =>
                $records
                    ->filter(
                        fn ($record) =>
                            $record->processed_at !== null
                    )
                    ->count(),

            'printed' =>
                $records
                    ->filter(
                        fn ($record) =>
                            $record->printed_at !== null
                    )
                    ->count(),

            'signed_sealed' =>
                $records
                    ->filter(
                        fn ($record) =>
                            $record->signed_sealed_at !== null
                    )
                    ->count(),

            'released' =>
                $records
                    ->filter(
                        fn ($record) =>
                            $record->released_at !== null
                    )
                    ->count(),

            'by_type' =>
                $byType,

            'by_status' =>
                $byStatus,

            'by_workflow' =>
                $byWorkflow,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Reports & Complaints Summary
    |--------------------------------------------------------------------------
    */

    private function complaintSummary(
        Carbon $from,
        Carbon $to
    ): array {

        $records = ReportComplaint::query()
            ->whereBetween(
                'date_reported',
                [
                    $from->toDateString(),
                    $to->toDateString(),
                ]
            )
            ->get();


        $byStatus = $records
            ->groupBy(
                fn ($record) =>
                    $record->status
                    ?: 'Unspecified'
            )
            ->map(
                fn ($items) =>
                    $items->count()
            )
            ->sortKeys()
            ->all();


        $byType = $records
            ->groupBy(
                fn ($record) =>
                    $record->report_type
                    ?: 'Unspecified'
            )
            ->map(
                fn ($items) =>
                    $items->count()
            )
            ->sortKeys()
            ->all();


        $byCategory = $records
            ->groupBy(
                fn ($record) =>
                    $record->category
                    ?: 'Unspecified'
            )
            ->map(
                fn ($items) =>
                    $items->count()
            )
            ->sortKeys()
            ->all();


        $byPurok = $records
            ->groupBy(
                fn ($record) =>
                    $record->purok
                    ?: 'Unassigned'
            )
            ->map(
                fn ($items) =>
                    $items->count()
            )
            ->sortKeys()
            ->all();


        return [

            'total' =>
                $records->count(),

            'active' =>
                $records
                    ->whereNotIn(
                        'status',
                        [
                            'Resolved',
                            'Closed',
                            'Disapproved',
                        ]
                    )
                    ->count(),

            'resolved' =>
                $records
                    ->where(
                        'status',
                        'Resolved'
                    )
                    ->count(),

            'closed' =>
                $records
                    ->where(
                        'status',
                        'Closed'
                    )
                    ->count(),

            'confidential' =>
                $records
                    ->where(
                        'is_confidential',
                        true
                    )
                    ->count(),

            'archived' =>
                $records
                    ->where(
                        'is_archived',
                        true
                    )
                    ->count(),

            'by_status' =>
                $byStatus,

            'by_type' =>
                $byType,

            'by_category' =>
                $byCategory,

            'by_purok' =>
                $byPurok,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Relief Assistance Summary
    |--------------------------------------------------------------------------
    */

    private function reliefSummary(
        Carbon $from,
        Carbon $to
    ): array {

        $records = ReliefVoucher::query()
            ->whereBetween(
                'distribution_schedule',
                [
                    $from,
                    $to,
                ]
            )
            ->get();


        $byAssistanceType = $records
            ->groupBy(
                fn ($record) =>
                    $record->assistance_type
                    ?: 'Unspecified'
            )
            ->map(
                fn ($items) =>
                    $items->count()
            )
            ->sortKeys()
            ->all();


        $byPurok = $records
            ->groupBy(
                fn ($record) =>
                    $record->purok_name
                    ?: 'Unassigned'
            )
            ->map(
                fn ($items) =>
                    $items->count()
            )
            ->sortKeys()
            ->all();


        return [

            'total' =>
                $records->count(),

            'pending' =>
                $records
                    ->filter(
                        fn ($record) =>
                            strtolower(
                                (string) $record->status
                            ) === 'pending'
                    )
                    ->count(),

            'issued' =>
                $records
                    ->filter(
                        fn ($record) =>
                            strtolower(
                                (string) $record->status
                            ) === 'issued'
                    )
                    ->count(),

            'claimed' =>
                $records
                    ->filter(
                        fn ($record) =>
                            strtolower(
                                (string) $record->status
                            ) === 'claimed'
                    )
                    ->count(),

            'cancelled' =>
                $records
                    ->filter(
                        fn ($record) =>
                            strtolower(
                                (string) $record->status
                            ) === 'cancelled'
                    )
                    ->count(),

            'requirements_verified' =>
                $records
                    ->where(
                        'requirements_verified',
                        true
                    )
                    ->count(),

            /*
            |--------------------------------------------------------------------------
            | These fields are intentionally not read from the database.
            |--------------------------------------------------------------------------
            |
            | The currently confirmed ReliefVoucher structure does not establish
            | is_id_verified, is_proof_verified, qr_verified_at, or released_at.
            |
            | Returning zero keeps the existing Blade structure compatible
            | without pretending those verification fields exist in the database.
            |
            */

            'id_verified' => 0,

            'proof_verified' => 0,

            'qr_verified' => 0,

            'released' => 0,

            'by_assistance_type' =>
                $byAssistanceType,

            'by_purok' =>
                $byPurok,
        ];
    }
}
