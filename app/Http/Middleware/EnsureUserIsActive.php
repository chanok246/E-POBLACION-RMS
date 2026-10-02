<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * This middleware ensures that an authenticated user:
     * - still has an active account
     * - still has a valid system role
     *
     * If the account was deactivated while the user was logged in,
     * the current session is immediately terminated.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            ! $user ||
            ! $user->isActive() ||
            ! $user->hasValidSystemRole()
        ) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors([
                    'email' => 'Your account is inactive or is no longer authorized to access the system.',
                ]);
        }

        return $next($request);
    }
}