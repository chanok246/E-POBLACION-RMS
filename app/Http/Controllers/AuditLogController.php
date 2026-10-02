<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Display the Audit Trail.
     *
     * The Audit Trail reads from the existing immutable
     * audit_logs records created by the system.
     */
    public function index(Request $request): View
    {
        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $module = trim((string) $request->input('module', ''));
        $action = trim((string) $request->input('action', ''));
        $userId = $request->input('user_id');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $search = trim((string) $request->input('search', ''));

        /*
        |--------------------------------------------------------------------------
        | Audit Log Query
        |--------------------------------------------------------------------------
        */

        $query = AuditLog::query()
            ->with('user')
            ->latest('created_at');

        /*
        |--------------------------------------------------------------------------
        | Module Filter
        |--------------------------------------------------------------------------
        */

        if ($module !== '') {
            $query->where('module', $module);
        }

        /*
        |--------------------------------------------------------------------------
        | Action Filter
        |--------------------------------------------------------------------------
        */

        if ($action !== '') {
            $query->where('action', $action);
        }

        /*
        |--------------------------------------------------------------------------
        | User Filter
        |--------------------------------------------------------------------------
        */

        if ($userId !== null && $userId !== '') {
            $query->where('user_id', $userId);
        }

        /*
        |--------------------------------------------------------------------------
        | Date From Filter
        |--------------------------------------------------------------------------
        */

        if (!empty($dateFrom)) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        /*
        |--------------------------------------------------------------------------
        | Date To Filter
        |--------------------------------------------------------------------------
        */

        if (!empty($dateTo)) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        |
        | Search across the fields that are useful to barangay personnel
        | when locating a specific processing activity.
        |
        */

        if ($search !== '') {
            $query->where(function ($searchQuery) use ($search) {
                $searchQuery
                    ->where('description', 'like', '%' . $search . '%')
                    ->orWhere('record_reference', 'like', '%' . $search . '%')
                    ->orWhere('action', 'like', '%' . $search . '%')
                    ->orWhere('module', 'like', '%' . $search . '%');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Paginated Results
        |--------------------------------------------------------------------------
        |
        | Keep the current filters when moving between pages.
        |
        */

        $auditLogs = $query
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Filter Options
        |--------------------------------------------------------------------------
        |
        | These are generated from the existing audit log records rather
        | than hard-coding module/action names that may not match the
        | current system.
        |
        */

        $modules = AuditLog::query()
            ->whereNotNull('module')
            ->where('module', '!=', '')
            ->distinct()
            ->orderBy('module')
            ->pluck('module');

        $actions = AuditLog::query()
            ->whereNotNull('action')
            ->where('action', '!=', '')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        $users = User::query()
            ->whereIn(
                'id',
                AuditLog::query()
                    ->whereNotNull('user_id')
                    ->distinct()
                    ->pluck('user_id')
            )
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Summary Statistics
        |--------------------------------------------------------------------------
        */

        $totalActivities = AuditLog::count();

        $todayActivities = AuditLog::query()
            ->whereDate('created_at', today())
            ->count();

        $uniqueUsers = AuditLog::query()
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count('user_id');

        $modulesUsed = AuditLog::query()
            ->whereNotNull('module')
            ->where('module', '!=', '')
            ->distinct('module')
            ->count('module');

        /*
        |--------------------------------------------------------------------------
        | Return View
        |--------------------------------------------------------------------------
        */

        return view('audit-logs.index', compact(
            'auditLogs',
            'modules',
            'actions',
            'users',
            'module',
            'action',
            'userId',
            'dateFrom',
            'dateTo',
            'search',
            'totalActivities',
            'todayActivities',
            'uniqueUsers',
            'modulesUsed'
        ));
    }
}