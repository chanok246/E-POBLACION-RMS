<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FieldPersonnelPolicyConsentController extends Controller
{
    public const CURRENT_POLICY_VERSION = 'EP-RMS-2026-01';

    /**
     * Display the Field Personnel policy consent page.
     */
    public function show(Request $request): View
    {
        $user = $request->user('field_personnel');

        abort_unless($user && $user->canAccessFieldCollection(), 403);

        $alreadyAccepted = DB::table('field_personnel_policy_acceptances')
            ->where('field_personnel_account_id', $user->id)
            ->where('policy_version', self::CURRENT_POLICY_VERSION)
            ->exists();

        return view('field-personnel.auth.policy-consent', [
            'policyVersion' => self::CURRENT_POLICY_VERSION,
            'alreadyAccepted' => $alreadyAccepted,
        ]);
    }

    /**
     * Record the Field Personnel account's policy acceptance.
     */
    public function accept(Request $request): RedirectResponse
    {
        $user = $request->user('field_personnel');

        abort_unless($user && $user->canAccessFieldCollection(), 403);

        $request->validate([
            'accept_policies' => ['required', 'accepted'],
        ]);

        $now = now();

        DB::table('field_personnel_policy_acceptances')->insertOrIgnore([
            'field_personnel_account_id' => $user->id,
            'policy_version' => self::CURRENT_POLICY_VERSION,
            'accepted_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return redirect()
            ->route('field-personnel.home')
            ->with('success', 'Your policy acceptance has been recorded.');
    }
}
