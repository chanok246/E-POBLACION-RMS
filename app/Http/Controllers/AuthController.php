<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('login');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'role' => [
                'required',
                Rule::in([
                    User::ROLE_CAPTAIN,
                    User::ROLE_SECRETARY,
                ]),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | AUTHENTICATION
        |--------------------------------------------------------------------------
        |
        | Only active accounts with one of the two authorized system roles
        | can successfully authenticate:
        |
        | Captain
        | Secretary
        |
        | Treasurer and Staff accounts are no longer accepted.
        |
        */

        if (! Auth::attempt([
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'status' => User::STATUS_ACTIVE,
        ], $request->boolean('remember'))) {
            return back()
                ->withErrors([
                    'email' => 'The email, password, role, or account status is invalid.',
                ])
                ->onlyInput('email');
        }

        /*
        |--------------------------------------------------------------------------
        | SESSION SECURITY
        |--------------------------------------------------------------------------
        |
        | Regenerate the session after successful authentication to help
        | prevent session fixation.
        |
        */

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        /*
        |--------------------------------------------------------------------------
        | SESSION SECURITY
        |--------------------------------------------------------------------------
        */

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
