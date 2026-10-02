<?php

namespace App\Http\Controllers;

use App\Models\DisasterAssessment;
use App\Models\DisasterEvent;
use App\Models\Household;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DisasterAssessmentController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Disaster Assessment List
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
    {
        $query = DisasterAssessment::with([
            'disasterEvent',
            'household',
            'assessor',
            'verifier',
            'reliefVoucher',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Search by Disaster Event
        |--------------------------------------------------------------------------
        */

        if ($request->filled('disaster')) {
            $search = $request->disaster;

            $query->whereHas('disasterEvent', function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('event_code', 'like', '%' . $search . '%');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Verification Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('verification_status')) {
            $query->where(
                'verification_status',
                $request->verification_status
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Voucher Eligibility
        |--------------------------------------------------------------------------
        */

        if ($request->filled('voucher_eligible')) {
            $query->where(
                'voucher_eligible',
                $request->voucher_eligible
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter by Damage Level
        |--------------------------------------------------------------------------
        */

        if ($request->filled('damage_level')) {
            $query->where(
                'damage_level',
                $request->damage_level
            );
        }

        $assessments = $query
            ->latest('assessment_date')
            ->paginate(15)
            ->withQueryString();

        return view(
            'disaster-assessments.index',
            compact('assessments')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create Assessment
    |--------------------------------------------------------------------------
    */

    public function create(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Disaster Events
        |--------------------------------------------------------------------------
        */

        $disasterEvents = DisasterEvent::where('status', 'Active')
            ->orderByDesc('start_date')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Households
        |--------------------------------------------------------------------------
        */

        $households = Household::with([
            'householdHead',
            'members',
        ])
            ->orderBy('purok')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Optional Preselected Household
        |--------------------------------------------------------------------------
        */

        $selectedHouseholdId = $request->get('household_id');

        /*
        |--------------------------------------------------------------------------
        | Optional Preselected Disaster Event
        |--------------------------------------------------------------------------
        */

        $selectedDisasterEventId = $request->get('disaster_event_id');

        return view(
            'disaster-assessments.create',
            compact(
                'disasterEvents',
                'households',
                'selectedHouseholdId',
                'selectedDisasterEventId'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store Assessment
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'disaster_event_id' => [
                'required',
                'exists:disaster_events,id',
            ],

            'household_id' => [
                'required',
                'exists:households,id',
            ],

            'assessment_date' => [
                'required',
                'date',
            ],

            'damage_level' => [
                'required',
                'in:none,minor,moderate,severe,destroyed',
            ],

            'house_livability' => [
                'required',
                'in:livable,partially_livable,unlivable',
            ],

            'estimated_property_loss' => [
                'required',
                'numeric',
                'min:0',
            ],

            'livelihood_impact' => [
                'nullable',
                'string',
            ],

            'affected_members' => [
                'required',
                'integer',
                'min:0',
            ],

            'household_affected' => [
                'required',
                'boolean',
            ],

            'evacuated' => [
                'required',
                'boolean',
            ],

            'evacuation_center' => [
                'nullable',
                'string',
                'max:255',
            ],

            'interview_notes' => [
                'nullable',
                'string',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Assessor
        |--------------------------------------------------------------------------
        */

        $validated['assessor_id'] = Auth::id();

        /*
        |--------------------------------------------------------------------------
        | Initial Verification Status
        |--------------------------------------------------------------------------
        */

        $validated['verification_status'] = 'Pending';

        /*
        |--------------------------------------------------------------------------
        | Initial Voucher Eligibility
        |--------------------------------------------------------------------------
        |
        | Assessments must be verified before becoming eligible for
        | relief voucher generation.
        |--------------------------------------------------------------------------
        */

        $validated['voucher_eligible'] = false;
        $validated['eligibility_reason'] = null;

        /*
        |--------------------------------------------------------------------------
        | Verification Fields
        |--------------------------------------------------------------------------
        */

        $validated['verified_by'] = null;
        $validated['verified_at'] = null;

        /*
        |--------------------------------------------------------------------------
        | Evacuation Center Cleanup
        |--------------------------------------------------------------------------
        */

        if (! $validated['evacuated']) {
            $validated['evacuation_center'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Create Assessment
        |--------------------------------------------------------------------------
        */

        DisasterAssessment::create($validated);

        return redirect()
            ->route('disaster-assessments.index')
            ->with(
                'success',
                'House-to-house disaster assessment recorded successfully and is now pending verification.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Show Assessment
    |--------------------------------------------------------------------------
    */

    public function show(DisasterAssessment $disasterAssessment)
    {
        $disasterAssessment->load([
            'disasterEvent',
            'household.householdHead',
            'household.members',
            'assessor',
            'verifier',
            'reliefVoucher',
        ]);

        return view(
            'disaster-assessments.show',
            compact('disasterAssessment')
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit Assessment
    |--------------------------------------------------------------------------
    */

    public function edit(DisasterAssessment $disasterAssessment)
    {
        $disasterEvents = DisasterEvent::orderByDesc('start_date')
            ->get();

        $households = Household::with([
            'householdHead',
            'members',
        ])
            ->orderBy('purok')
            ->get();

        return view(
            'disaster-assessments.edit',
            compact(
                'disasterAssessment',
                'disasterEvents',
                'households'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update Assessment
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        DisasterAssessment $disasterAssessment
    ) {
        $validated = $request->validate([
            'disaster_event_id' => [
                'required',
                'exists:disaster_events,id',
            ],

            'household_id' => [
                'required',
                'exists:households,id',
            ],

            'assessment_date' => [
                'required',
                'date',
            ],

            'damage_level' => [
                'required',
                'in:none,minor,moderate,severe,destroyed',
            ],

            'house_livability' => [
                'required',
                'in:livable,partially_livable,unlivable',
            ],

            'estimated_property_loss' => [
                'required',
                'numeric',
                'min:0',
            ],

            'livelihood_impact' => [
                'nullable',
                'string',
            ],

            'affected_members' => [
                'required',
                'integer',
                'min:0',
            ],

            'household_affected' => [
                'required',
                'boolean',
            ],

            'evacuated' => [
                'required',
                'boolean',
            ],

            'evacuation_center' => [
                'nullable',
                'string',
                'max:255',
            ],

            'interview_notes' => [
                'nullable',
                'string',
            ],

            'remarks' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Evacuation Center Cleanup
        |--------------------------------------------------------------------------
        */

        if (! $validated['evacuated']) {
            $validated['evacuation_center'] = null;
        }

        /*
        |--------------------------------------------------------------------------
        | Return Assessment for Verification
        |--------------------------------------------------------------------------
        |
        | Any important modification requires verification again.
        |--------------------------------------------------------------------------
        */

        $validated['verification_status'] = 'Pending';
        $validated['voucher_eligible'] = false;
        $validated['eligibility_reason'] = null;
        $validated['verified_by'] = null;
        $validated['verified_at'] = null;

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $disasterAssessment->update($validated);

        return redirect()
            ->route(
                'disaster-assessments.show',
                $disasterAssessment
            )
            ->with(
                'success',
                'Disaster assessment updated successfully and returned for verification.'
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Verify Assessment
    |--------------------------------------------------------------------------
    |
    | This is the important transition:
    |
    | Pending
    |    ↓
    | Verified
    |    ↓
    | Voucher Eligible
    |
    |--------------------------------------------------------------------------
    */

    public function verify(DisasterAssessment $disasterAssessment)
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent Verification of Archived Assessment
        |--------------------------------------------------------------------------
        */

        if ($disasterAssessment->verification_status === 'Archived') {
            return back()->with(
                'error',
                'An archived disaster assessment cannot be verified.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Determine Voucher Eligibility
        |--------------------------------------------------------------------------
        |
        | A household is considered eligible when the assessment indicates
        | that the household was affected and the damage is at least minor.
        |--------------------------------------------------------------------------
        */

        $eligible = (
            (bool) $disasterAssessment->household_affected
            && in_array(
                strtolower((string) $disasterAssessment->damage_level),
                ['minor', 'moderate', 'severe', 'destroyed'],
                true
            )
        );

        /*
        |--------------------------------------------------------------------------
        | Eligibility Reason
        |--------------------------------------------------------------------------
        */

        if ($eligible) {
            $eligibilityReason =
                'Household was affected by the disaster and the assessed damage level qualifies for assistance.';
        } else {
            $eligibilityReason =
                'Assessment verified, but the household does not meet the current relief voucher eligibility criteria.';
        }

        /*
        |--------------------------------------------------------------------------
        | Save Verification
        |--------------------------------------------------------------------------
        */

        $disasterAssessment->update([
            'verification_status' => 'Verified',
            'voucher_eligible' => $eligible,
            'eligibility_reason' => $eligibilityReason,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        if ($eligible) {
            return back()->with(
                'success',
                'Disaster assessment verified successfully. The household is eligible for a relief voucher.'
            );
        }

        return back()->with(
            'success',
            'Disaster assessment verified successfully. The household is not eligible for a relief voucher based on the current assessment.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reject Assessment
    |--------------------------------------------------------------------------
    */

    public function reject(
        Request $request,
        DisasterAssessment $disasterAssessment
    ) {
        $validated = $request->validate([
            'remarks' => [
                'nullable',
                'string',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Do Not Reject an Already Claimed Voucher
        |--------------------------------------------------------------------------
        */

        if (
            $disasterAssessment->reliefVoucher
            && $disasterAssessment->reliefVoucher->isClaimed()
        ) {
            return back()->with(
                'error',
                'This assessment cannot be rejected because its relief voucher has already been claimed.'
            );
        }

        $remarks = $validated['remarks']
            ?? $disasterAssessment->remarks;

        $disasterAssessment->update([
            'verification_status' => 'Rejected',
            'voucher_eligible' => false,
            'eligibility_reason' => 'Assessment was rejected during verification.',
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'remarks' => $remarks,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Remove Existing Unclaimed Voucher Eligibility
        |--------------------------------------------------------------------------
        |
        | We do not delete a voucher automatically. The assessment is simply
        | made ineligible so the existing voucher record remains auditable.
        |--------------------------------------------------------------------------
        */

        return back()->with(
            'success',
            'Disaster assessment rejected successfully. The household is no longer eligible for a relief voucher.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Archive Assessment
    |--------------------------------------------------------------------------
    */

    public function archive(DisasterAssessment $disasterAssessment)
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent Archiving Claimed Voucher Assessment
        |--------------------------------------------------------------------------
        */

        if (
            $disasterAssessment->reliefVoucher
            && $disasterAssessment->reliefVoucher->isClaimed()
        ) {
            return back()->with(
                'error',
                'This assessment cannot be archived because its relief voucher has already been claimed.'
            );
        }

        $disasterAssessment->update([
            'verification_status' => 'Archived',
            'voucher_eligible' => false,
            'eligibility_reason' => null,
        ]);

        return back()->with(
            'success',
            'Disaster assessment archived successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Restore Assessment
    |--------------------------------------------------------------------------
    */

    public function restore(DisasterAssessment $disasterAssessment)
    {
        /*
        |--------------------------------------------------------------------------
        | Restore to Pending
        |--------------------------------------------------------------------------
        |
        | Restored assessments must be reviewed again. We intentionally do
        | not automatically restore voucher eligibility.
        |--------------------------------------------------------------------------
        */

        $disasterAssessment->update([
            'verification_status' => 'Pending',
            'voucher_eligible' => false,
            'eligibility_reason' => null,
            'verified_by' => null,
            'verified_at' => null,
        ]);

        return back()->with(
            'success',
            'Disaster assessment restored successfully and returned to pending verification.'
        );
    }
}
