<?php

namespace App\Http\Controllers;

use App\Models\FieldPersonnelAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

        $guard = Auth::guard('field_personnel');

        $authenticated = $guard->attempt([
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

        // Regenerate the session ID after successful authentication.
        $request->session()->regenerate();

        $account = $guard->user();

        // Defense in depth: the account must also be allowed into Field Collection.
        if (! $account instanceof FieldPersonnelAccount || ! $account->canAccessFieldCollection()) {
            $guard->logout();

            // Only the Field Personnel guard is logged out; keep any Captain/Secretary session.
            $request->session()->regenerate();

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

        $account->last_login_at = now();
        $account->save();

        // Consent is recorded per account and per policy version. Send the user to the
        // consent page only when the current version has not been accepted yet.
        // The fieldpersonnel.policy middleware still enforces this on every protected route.
        if (! $this->hasAcceptedCurrentPolicy($account)) {
            return redirect()->route('field-personnel.consent');
        }

        return redirect()->route('field-personnel.home');
    }

    /**
     * Log out only the Field Personnel guard.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('field_personnel')->logout();

        // The Captain/Secretary guard shares the same session cookie.
        // Rotate the session ID if that guard is still signed in; otherwise
        // destroy the session completely.
        if (Auth::guard('web')->check()) {
            $request->session()->regenerate();
        } else {
            $request->session()->invalidate();
        }

        $request->session()->regenerateToken();

        return redirect()
            ->route('field-personnel.login')
            ->with('success', 'You have been logged out successfully.');
    }

    /**
     * Whether this account has accepted the current policy version.
     */
    private function hasAcceptedCurrentPolicy(FieldPersonnelAccount $account): bool
    {
        return DB::table('field_personnel_policy_acceptances')
            ->where('field_personnel_account_id', $account->getKey())
            ->where('policy_version', FieldPersonnelPolicyConsentController::CURRENT_POLICY_VERSION)
            ->exists();
    }
}