<?php

namespace App\Http\Middleware;

use App\Http\Controllers\FieldPersonnelPolicyConsentController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureFieldPersonnelHasAcceptedPolicies
{
    public function handle(Request $request, Closure $next): Response
    {
        $personnel = $request->user('field_personnel');

        abort_unless($personnel, 401, 'Unauthorized Field Collection access.');

        $hasAcceptedCurrentPolicies = DB::table(
            'field_personnel_policy_acceptances'
        )
            ->where('field_personnel_account_id', $personnel->id)
            ->where(
                'policy_version',
                FieldPersonnelPolicyConsentController::CURRENT_POLICY_VERSION
            )
            ->exists();

        if (!$hasAcceptedCurrentPolicies) {
            return redirect()
                ->route('field-personnel.consent')
                ->with(
                    'warning',
                    'Please review and accept the current Terms & Conditions, Privacy Policy, and Data Privacy Notice before continuing.'
                );
        }

        return $next($request);
    }
}
