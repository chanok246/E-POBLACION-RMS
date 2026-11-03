<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Ensure the authenticated user has an active account
     * and a valid system role.
     *
     * Immediately terminate the session if the account is
     * inactive or no longer authorized.
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
                    'username' => 'Your account is inactive or is no longer authorized to access the system.',
                ]);
        }

        return $next($request);
    }
}
