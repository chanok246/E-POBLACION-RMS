<?php

namespace App\Http\Controllers;

use App\Models\FieldPersonnelAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class FieldPersonnelAuthController extends Controller
{
    /**
     * Display the Field Personnel login page.
     */
    public function showLogin(): View
    {
        return view('field-personnel.auth.login');
    }

    /**
     * Authenticate a Field Personnel account.
     */
    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:100',
            ],
            'passcode' => [
                'required',
                'string',
                'digits:6',
            ],
        ], [
            'username.required' => 'Please enter your username.',
            'passcode.required' => 'Please enter your six-digit passcode.',
            'passcode.digits' => 'The passcode must contain exactly six digits.',
        ]);

        $throttleKey = 'field-personnel-login:'
            . Str::lower($validated['username'])
            . '|'
            . $request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return back()
                ->withInput([
                    'username' => $validated['username'],
                ])
                ->withErrors([
                    'username' => "Too many login attempts. Please try again in {$seconds} seconds.",
                ]);
        }

        $authenticated = Auth::guard('field_personnel')->attempt([
            'username' => $validated['username'],
            'password' => $validated['passcode'],
            'account_active' => true,
        ]);

        if (! $authenticated) {
            RateLimiter::hit($throttleKey, 60);

            return back()
                ->withInput([
                    'username' => $validated['username'],
                ])
                ->withErrors([
                    'username' => 'Invalid username or passcode, or the account is inactive.',
                ]);
        }

        RateLimiter::clear($throttleKey);

        // Regenerate the session ID after successful authentication.
        $request->session()->regenerate();

        $account = Auth::guard('field_personnel')->user();

        if ($account instanceof FieldPersonnelAccount) {
            $account->last_login_at = now();
            $account->save();
        }

        // Always show the policy consent page after login.
        // Protected routes separately enforce the current policy version.
        return redirect()->route('field-personnel.consent');
    }

    /**
     * Log out only the Field Personnel guard.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('field_personnel')->logout();

        // Preserve other guard sessions.
        $request->session()->regenerateToken();

        return redirect('/field-personnel/login')
            ->with('success', 'You have been logged out successfully.');
    }
}
