<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * Final system roles:
     * - Captain
     * - Secretary
     *
     * Captain:
     * - Full access to all protected system features.
     *
     * Secretary:
     * - Access to operational system features according to
     *   the role assigned to the route.
     *
     * Treasurer:
     * - No system access.
     *
     * Staff:
     * - No system login/access.
     */
    public function handle(
        Request $request,
        Closure $next,
        string ...$roles
    ): Response {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Authentication Check
        |--------------------------------------------------------------------------
        |
        | The user must be logged in.
        |
        */
        if (! $user) {
            abort(403, 'You must be logged in to access this feature.');
        }

        /*
        |--------------------------------------------------------------------------
        | Active Account Check
        |--------------------------------------------------------------------------
        |
        | Only active accounts can access the system.
        |
        */
        if (! $user->isActive()) {
            abort(403, 'Your account is inactive.');
        }

        /*
        |--------------------------------------------------------------------------
        | Final Valid System Roles
        |--------------------------------------------------------------------------
        |
        | Only Captain and Secretary are valid system roles.
        |
        | Treasurer and Staff are intentionally excluded.
        |
        */
        $userRole = strtolower(trim((string) $user->role));

        if (! in_array($userRole, [
            'captain',
            'secretary',
        ], true)) {
            abort(403, 'Your account does not have a valid system role.');
        }

        /*
        |--------------------------------------------------------------------------
        | Captain / Chairman
        |--------------------------------------------------------------------------
        |
        | The Captain is the system administrator and has full access
        | to all protected system features.
        |
        | This includes User Management and System Settings.
        |
        */
        if ($userRole === 'captain') {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Secretary
        |--------------------------------------------------------------------------
        |
        | The Secretary can access a feature only when "secretary"
        | is included in the route's role middleware.
        |
        | Example:
        |
        | Route::middleware('role:captain,secretary')->group(...)
        |
        */
        if (in_array($userRole, $roles, true)) {
            return $next($request);
        }

        /*
        |--------------------------------------------------------------------------
        | Unauthorized Access
        |--------------------------------------------------------------------------
        */
        abort(403, 'You do not have permission to access this feature.');
    }
}
