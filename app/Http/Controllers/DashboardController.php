<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\Household;
use App\Models\Resident;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    public function index(): View
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $residentCount = Resident::count();

        $activeResidentCount = Resident::query()
            ->where('status', 'Active')
            ->count();

        $householdCount = Household::count();

        $documentsAwaitingAction = DocumentRequest::query()
            ->whereNotIn('workflow_stage', [
                DocumentRequest::STAGE_RELEASED,
                DocumentRequest::STAGE_THIRD_PARTY_DECLINED,
            ])
            ->count();

        $documentsReleasedThisMonth = DocumentRequest::query()
            ->where('workflow_stage', DocumentRequest::STAGE_RELEASED)
            ->whereBetween('released_at', [
                $startOfMonth,
                $endOfMonth,
            ])
            ->count();

        /*
        |--------------------------------------------------------------------------
        | MONTHLY COMPLETION RATE
        |--------------------------------------------------------------------------
        |
        | Released requests ÷ all requests created this month.
        |
        */

        $documentsCreatedThisMonth = DocumentRequest::query()
            ->whereBetween('requested_at', [
                $startOfMonth,
                $endOfMonth,
            ])
            ->count();

        $documentsCompletedFromThisMonth = DocumentRequest::query()
            ->whereBetween('requested_at', [
                $startOfMonth,
                $endOfMonth,
            ])
            ->where(
                'workflow_stage',
                DocumentRequest::STAGE_RELEASED
            )
            ->count();

        $monthlyCompletionRate = $documentsCreatedThisMonth > 0
            ? (int) round(
                ($documentsCompletedFromThisMonth / $documentsCreatedThisMonth) * 100
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | AVERAGE PROCESSING TIME
        |--------------------------------------------------------------------------
        |
        | Average time between request creation and document release.
        |
        */

        $releasedDocumentsThisMonth = DocumentRequest::query()
            ->where(
                'workflow_stage',
                DocumentRequest::STAGE_RELEASED
            )
            ->whereBetween('released_at', [
                $startOfMonth,
                $endOfMonth,
            ])
            ->whereNotNull('requested_at')
            ->whereNotNull('released_at')
            ->get([
                'requested_at',
                'released_at',
            ]);

        $averageProcessingMinutes = $releasedDocumentsThisMonth->avg(
            fn (DocumentRequest $document) => $document->requested_at
                ->diffInMinutes($document->released_at)
        );

        $averageProcessingHours = $averageProcessingMinutes
            ? round($averageProcessingMinutes / 60, 1)
            : null;

        /*
        |--------------------------------------------------------------------------
        | DOCUMENT WORKFLOW COUNTS
        |--------------------------------------------------------------------------
        */

        $workflowCounts = [
            'forInspection' => DocumentRequest::query()
                ->whereIn('workflow_stage', [
                    DocumentRequest::STAGE_REQUESTED,
                    DocumentRequest::STAGE_INSPECTION,
                ])
                ->count(),

            'forApproval' => DocumentRequest::query()
                ->whereIn('workflow_stage', [
                    DocumentRequest::STAGE_ID_VERIFIED,
                    DocumentRequest::STAGE_ELIGIBLE,
                ])
                ->count(),

            'readyForPrinting' => DocumentRequest::query()
                ->where(
                    'workflow_stage',
                    DocumentRequest::STAGE_APPROVED
                )
                ->count(),

            'readyForRelease' => DocumentRequest::query()
                ->whereIn('workflow_stage', [
                    DocumentRequest::STAGE_PRINTED,
                    DocumentRequest::STAGE_SIGNED_SEALED,
                ])
                ->count(),

            'thirdPartyDeclined' => DocumentRequest::query()
                ->where(
                    'workflow_stage',
                    DocumentRequest::STAGE_THIRD_PARTY_DECLINED
                )
                ->whereBetween('processed_at', [
                    $startOfMonth,
                    $endOfMonth,
                ])
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | LATEST DOCUMENTS
        |--------------------------------------------------------------------------
        */

        $latestDocuments = DocumentRequest::with('resident')
            ->latest('requested_at')
            ->take(6)
            ->get();

        return view('dashboard', compact(
            'residentCount',
            'activeResidentCount',
            'householdCount',
            'documentsAwaitingAction',
            'documentsReleasedThisMonth',
            'monthlyCompletionRate',
            'averageProcessingHours',
            'workflowCounts',
            'latestDocuments'
        ));
    }


    /*
    |--------------------------------------------------------------------------
    | REPORTS & ANALYTICS
    |--------------------------------------------------------------------------
    |
    | This method is required by:
    |
    | reports-analytics.index
    |
    | It currently uses the same dashboard analytics data so that the
    | Reports & Analytics route works without changing the existing
    | dashboard functionality.
    |
    */

    public function reports(): View
    {
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        /*
        |--------------------------------------------------------------------------
        | RESIDENT & HOUSEHOLD STATISTICS
        |--------------------------------------------------------------------------
        */

        $residentCount = Resident::count();

        $activeResidentCount = Resident::query()
            ->where('status', 'Active')
            ->count();

        $householdCount = Household::count();


        /*
        |--------------------------------------------------------------------------
        | DOCUMENT STATISTICS
        |--------------------------------------------------------------------------
        */

        $documentsAwaitingAction = DocumentRequest::query()
            ->whereNotIn('workflow_stage', [
                DocumentRequest::STAGE_RELEASED,
                DocumentRequest::STAGE_THIRD_PARTY_DECLINED,
            ])
            ->count();

        $documentsReleasedThisMonth = DocumentRequest::query()
            ->where(
                'workflow_stage',
                DocumentRequest::STAGE_RELEASED
            )
            ->whereBetween('released_at', [
                $startOfMonth,
                $endOfMonth,
            ])
            ->count();


        /*
        |--------------------------------------------------------------------------
        | MONTHLY COMPLETION RATE
        |--------------------------------------------------------------------------
        */

        $documentsCreatedThisMonth = DocumentRequest::query()
            ->whereBetween('requested_at', [
                $startOfMonth,
                $endOfMonth,
            ])
            ->count();

        $documentsCompletedFromThisMonth = DocumentRequest::query()
            ->whereBetween('requested_at', [
                $startOfMonth,
                $endOfMonth,
            ])
            ->where(
                'workflow_stage',
                DocumentRequest::STAGE_RELEASED
            )
            ->count();

        $monthlyCompletionRate = $documentsCreatedThisMonth > 0
            ? (int) round(
                ($documentsCompletedFromThisMonth / $documentsCreatedThisMonth) * 100
            )
            : 0;


        /*
        |--------------------------------------------------------------------------
        | AVERAGE PROCESSING TIME
        |--------------------------------------------------------------------------
        */

        $releasedDocumentsThisMonth = DocumentRequest::query()
            ->where(
                'workflow_stage',
                DocumentRequest::STAGE_RELEASED
            )
            ->whereBetween('released_at', [
                $startOfMonth,
                $endOfMonth,
            ])
            ->whereNotNull('requested_at')
            ->whereNotNull('released_at')
            ->get([
                'requested_at',
                'released_at',
            ]);

        $averageProcessingMinutes = $releasedDocumentsThisMonth->avg(
            fn (DocumentRequest $document) => $document->requested_at
                ->diffInMinutes($document->released_at)
        );

        $averageProcessingHours = $averageProcessingMinutes
            ? round($averageProcessingMinutes / 60, 1)
            : null;


        /*
        |--------------------------------------------------------------------------
        | WORKFLOW STATISTICS
        |--------------------------------------------------------------------------
        */

        $workflowCounts = [
            'forInspection' => DocumentRequest::query()
                ->whereIn('workflow_stage', [
                    DocumentRequest::STAGE_REQUESTED,
                    DocumentRequest::STAGE_INSPECTION,
                ])
                ->count(),

            'forApproval' => DocumentRequest::query()
                ->whereIn('workflow_stage', [
                    DocumentRequest::STAGE_ID_VERIFIED,
                    DocumentRequest::STAGE_ELIGIBLE,
                ])
                ->count(),

            'readyForPrinting' => DocumentRequest::query()
                ->where(
                    'workflow_stage',
                    DocumentRequest::STAGE_APPROVED
                )
                ->count(),

            'readyForRelease' => DocumentRequest::query()
                ->whereIn('workflow_stage', [
                    DocumentRequest::STAGE_PRINTED,
                    DocumentRequest::STAGE_SIGNED_SEALED,
                ])
                ->count(),

            'thirdPartyDeclined' => DocumentRequest::query()
                ->where(
                    'workflow_stage',
                    DocumentRequest::STAGE_THIRD_PARTY_DECLINED
                )
                ->whereBetween('processed_at', [
                    $startOfMonth,
                    $endOfMonth,
                ])
                ->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | DOCUMENT TOTALS
        |--------------------------------------------------------------------------
        */

        $totalDocuments = DocumentRequest::count();

        $releasedDocuments = DocumentRequest::query()
            ->where(
                'workflow_stage',
                DocumentRequest::STAGE_RELEASED
            )
            ->count();

        $pendingDocuments = DocumentRequest::query()
            ->whereNotIn('workflow_stage', [
                DocumentRequest::STAGE_RELEASED,
                DocumentRequest::STAGE_THIRD_PARTY_DECLINED,
            ])
            ->count();

        $declinedDocuments = DocumentRequest::query()
            ->where(
                'workflow_stage',
                DocumentRequest::STAGE_THIRD_PARTY_DECLINED
            )
            ->count();


        /*
        |--------------------------------------------------------------------------
        | REPORTS & ANALYTICS VIEW
        |--------------------------------------------------------------------------
        |
        | This view should be:
        |
        | resources/views/reports-analytics.blade.php
        |
        */

        return view('reports-analytics', compact(
            'residentCount',
            'activeResidentCount',
            'householdCount',
            'documentsAwaitingAction',
            'documentsReleasedThisMonth',
            'monthlyCompletionRate',
            'averageProcessingHours',
            'workflowCounts',
            'totalDocuments',
            'releasedDocuments',
            'pendingDocuments',
            'declinedDocuments'
        ));
    }
}