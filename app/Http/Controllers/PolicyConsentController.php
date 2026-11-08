<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PolicyConsentController extends Controller
{
    /**
     * Current Terms, Privacy Policy, and Data Privacy Notice version.
     */
    public const CURRENT_POLICY_VERSION = 'EP-RMS-2026-01';

    /**
     * Display the required policy consent page.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        abort_unless($user, 401);

        abort_unless(
            $user->isActive()
            && $user->hasValidSystemRole()
            && $user->hasTwoFactorEnabled()
            && $request->session()->get('two_factor_passed') === true
            && (int) $request->session()->get('two_factor_pending_user_id') === (int) $user->id,
            403,
            'Complete your account and two-factor authentication requirements first.'
        );

        return view('auth.two-factor-recovery-codes', [
            'policyVersion' => self::CURRENT_POLICY_VERSION,
        ]);
    }

    /**
     * Record the user's acceptance of the current policies.
     */
    public function accept(Request $request)
    {
        $user = $request->user();

        abort_unless($user, 401);

        abort_unless(
            $user->isActive()
            && $user->hasValidSystemRole()
            && $user->hasTwoFactorEnabled()
            && $request->session()->get('two_factor_passed') === true
            && (int) $request->session()->get('two_factor_pending_user_id') === (int) $user->id,
            403,
            'Complete your account and two-factor authentication requirements first.'
        );

        $validated = $request->validate([
            'accept_policies' => ['required', 'accepted'],
        ], [
            'accept_policies.required' => 'You must accept the policies before continuing.',
            'accept_policies.accepted' => 'You must accept the policies before continuing.',
        ]);

        $now = now();

        DB::table('policy_acceptances')->insertOrIgnore([
            'user_id' => $user->id,
            'policy_version' => self::CURRENT_POLICY_VERSION,
            'accepted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return redirect()
            ->intended(route('dashboard'))
            ->with('success', 'Your policy acceptance has been recorded.');
    }
}
