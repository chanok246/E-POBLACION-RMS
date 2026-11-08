<?php

namespace App\Http\Middleware;

use App\Http\Controllers\PolicyConsentController;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorIsVerified
{
    /**
     * Require authenticated users to complete 2FA and accept the
     * current policies before accessing protected system pages.
     *
     * Authenticator-replacement and email-recovery routes must remain
     * outside the 'twofactor' middleware group. Their controller actions
     * must validate the restricted recovery session independently.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /*
         * Allow logout before 2FA completion so users can safely
         * abandon an incomplete authentication session.
         */
        if ($request->routeIs('logout')) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        /*
         * Recheck account status and system role even if the active-account
         * middleware is also applied to the route.
         */
        if (! $user->isActive() || ! $user->hasValidSystemRole()) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'username' => 'Your account is inactive or is not authorized to access the system.',
                ]);
        }

        /*
         * The pending 2FA session must belong to the authenticated account.
         * A successful 2FA flag alone must never grant access.
         */
        $pendingUserId = $request->session()->get(
            'two_factor_pending_user_id'
        );

        if (
            $pendingUserId === null
            || (string) $pendingUserId !==
                (string) $user->getAuthIdentifier()
        ) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'username' => 'Please sign in again to verify your account.',
                ]);
        }

        /*
         * Allow only the named two-factor routes to execute before 2FA
         * is marked as complete. The authenticated account and pending
         * session have already been validated above.
         *
         * Each controller action must still enforce its own requirements.
         */
        if ($request->routeIs('two-factor.*')) {
            return $next($request);
        }

        /*
         * Protected system pages require configured and confirmed 2FA.
         */
        if (! $user->hasTwoFactorEnabled()) {
            $request->session()->forget('two_factor_passed');

            return redirect()->route('two-factor.setup');
        }

        /*
         * Require successful verification for the current pending account.
         * Do not accept truthy strings or other values in place of boolean
         * true.
         */
        if ($request->session()->get('two_factor_passed') !== true) {
            return redirect()->route('two-factor.challenge');
        }

        /*
         * Require acceptance of the current policy version after 2FA.
         *
         * The consent routes are exempt from this database check so the
         * user can open the consent page and submit their acceptance.
         * All account and 2FA checks above still apply to those routes.
         *
         * A future policy-version change automatically requires users
         * to accept the new version before accessing protected pages.
         */
        if (! $request->routeIs('policy-consent.*')) {
            $hasAcceptedCurrentPolicy = DB::table('policy_acceptances')
                ->where('user_id', $user->id)
                ->where(
                    'policy_version',
                    PolicyConsentController::CURRENT_POLICY_VERSION
                )
                ->exists();

            if (! $hasAcceptedCurrentPolicy) {
                return redirect()->route('policy-consent.show');
            }
        }

        return $next($request);
    }
}
