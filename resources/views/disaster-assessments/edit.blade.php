@extends('layouts.app')

@section('content')
<div class="container">

    {{-- =========================================================
         HEADER
         ========================================================= --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h2 class="mb-1">
                Edit Disaster Assessment
            </h2>

            <small class="text-muted">
                Update the house-to-house post-disaster assessment record.
            </small>
        </div>

        <a
            href="{{ route('disaster-assessments.show', $disasterAssessment->id) }}"
            class="btn btn-outline-secondary"
        >
            ← Back to Assessment
        </a>

    </div>


    {{-- =========================================================
         SUCCESS MESSAGE
         ========================================================= --}}
    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    {{-- =========================================================
         ERROR MESSAGE
         ========================================================= --}}
    @if(session('error'))

        <div class="alert alert-danger alert-dismissible fade show">

            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    @endif


    {{-- =========================================================
         VALIDATION ERRORS
         ========================================================= --}}
    @if($errors->any())

        <div class="alert alert-danger">

            <strong>
                Please correct the following:
            </strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- =========================================================
         CURRENT STATUS NOTICE
         ========================================================= --}}
    <div class="alert alert-warning">

        <div class="d-flex">

            <div class="me-3">
                <strong>⚠</strong>
            </div>

            <div>

                <strong>
                    Editing this assessment requires re-verification.
                </strong>

                <div class="mt-1">

                    After you save the changes, the assessment will return to
                    <strong>Pending Verification</strong>.

                    Any previous verification and voucher eligibility decision
                    will need to be reviewed again.

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         MAIN FORM
         ========================================================= --}}
    <div class="card shadow-sm">

        {{-- =====================================================
             CARD HEADER
             ===================================================== --}}
        <div class="card-header">

            <h5 class="mb-1">
                Assessment Information
            </h5>

            <small class="text-muted">
                Update information based on the field assessment record.
            </small>

        </div>


        <div class="card-body">

            <form
                action="{{ route('disaster-assessments.update', $disasterAssessment->id) }}"
                method="POST"
            >

                @csrf
                @method('PUT')


                {{-- =================================================
                     1. HOUSEHOLD AND DISASTER EVENT
                     ================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        1. Household and Disaster Event
                    </h5>

                    <div class="row">

                        {{-- Disaster Event --}}
                        <div class="col-md-6 mb-3">

                            <label
                                for="disaster_event_id"
                                class="form-label"
                            >
                                Disaster Event
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="disaster_event_id"
                                id="disaster_event_id"
                                class="form-select @error('disaster_event_id') is-invalid @enderror"
                                required
                            >

                                <option value="">
                                    -- Select Disaster Event --
                                </option>

                                @foreach($disasterEvents as $event)

                                    <option
                                        value="{{ $event->id }}"
                                        {{ old(
                                            'disaster_event_id',
                                            $disasterAssessment->disaster_event_id
                                        ) == $event->id ? 'selected' : '' }}
                                    >
                                        {{ $event->event_code }}
                                        -
                                        {{ $event->name }}
                                    </option>

                                @endforeach

                            </select>

                            @error('disaster_event_id')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                            <small class="text-muted">
                                Select the disaster event connected to this assessment.
                            </small>

                        </div>


                        {{-- Household --}}
                        <div class="col-md-6 mb-3">

                            <label
                                for="household_id"
                                class="form-label"
                            >
                                Household
                                <span class="text-danger">*</span>
                            </label>

                            <select
                                name="household_id"
                                id="household_id"
                                class="form-select @error('household_id') is-invalid @enderror"
                                required
                            >

                                <option value="">
                                    -- Select Household --
                                </option>

                                @foreach($households as $household)

                                    @php

                                        $head = $household->householdHead;

                                        $headName = 'No household head recorded';

                                        if ($head) {

                                            $headName = trim(
                                                ($head->first_name ?? '') . ' ' .
                                                ($head->middle_name ?? '') . ' ' .
                                                ($head->last_name ?? '') . ' ' .
                                                ($head->suffix ?? '')
                                            );

                                            if ($headName === '') {
                                                $headName = 'No household head recorded';
                                            }

                                        }

                                    @endphp

                                    <option
                                        value="{{ $household->id }}"
                                        {{ old(
                                            'household_id',
                                            $disasterAssessment->household_id
                                        ) == $household->id ? 'selected' : '' }}
                                    >
                                        Household #{{ $household->id }}
                                        -
                                        Purok {{ $household->purok ?? 'N/A' }}
                                        -
                                        {{ $headName }}
                                    </option>

                                @endforeach

                            </select>

                            @error('household_id')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                            <small class="text-muted">
                                Select the household that was assessed.
                            </small>

                        </div>

                    </div>

                </div>


                <hr>


                {{-- =================================================
                     2. FIELD ASSESSMENT DETAILS
                     ================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        2. Field Assessment Details
                    </h5>

                    <div class="row">

                        {{-- Assessment Date --}}
                        <div class="col-md-4 mb-3">

                            <label
                                for="assessment_date"
                                class="form-label"
                            >
                                Assessment Date
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="assessment_date"
                                id="assessment_date"
                                class="form-control @error('assessment_date') is-invalid @enderror"
                                value="{{ old(
                                    'assessment_date',
                                    $disasterAssessment->assessment_date
                                        ? $disasterAssessment->assessment_date->format('Y-m-d')
                                        : ''
                                ) }}"
                                required
                            >

                            @error('assessment_date')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        {{-- Damage Level --}}
                        <div class="col-md-4 mb-3">

                            <label
                                for="damage_level"
                                class="form-label"
                            >
                                Damage Level
                                <span class="text-danger">*</span>
                            </label>

                            @php

                                $selectedDamageLevel = old(
                                    'damage_level',
                                    $disasterAssessment->damage_level ?? 'none'
                                );

                            @endphp

                            <select
                                name="damage_level"
                                id="damage_level"
                                class="form-select @error('damage_level') is-invalid @enderror"
                                required
                            >

                                <option
                                    value="none"
                                    {{ $selectedDamageLevel === 'none' ? 'selected' : '' }}
                                >
                                    No Damage
                                </option>

                                <option
                                    value="minor"
                                    {{ $selectedDamageLevel === 'minor' ? 'selected' : '' }}
                                >
                                    Minor Damage
                                </option>

                                <option
                                    value="moderate"
                                    {{ $selectedDamageLevel === 'moderate' ? 'selected' : '' }}
                                >
                                    Moderate Damage
                                </option>

                                <option
                                    value="severe"
                                    {{ $selectedDamageLevel === 'severe' ? 'selected' : '' }}
                                >
                                    Severe Damage
                                </option>

                                <option
                                    value="destroyed"
                                    {{ $selectedDamageLevel === 'destroyed' ? 'selected' : '' }}
                                >
                                    Totally Destroyed
                                </option>

                            </select>

                            @error('damage_level')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        {{-- House Livability --}}
                        <div class="col-md-4 mb-3">

                            <label
                                for="house_livability"
                                class="form-label"
                            >
                                House Livability
                                <span class="text-danger">*</span>
                            </label>

                            @php

                                $selectedLivability = old(
                                    'house_livability',
                                    $disasterAssessment->house_livability ?? 'livable'
                                );

                            @endphp

                            <select
                                name="house_livability"
                                id="house_livability"
                                class="form-select @error('house_livability') is-invalid @enderror"
                                required
                            >

                                <option
                                    value="livable"
                                    {{ $selectedLivability === 'livable' ? 'selected' : '' }}
                                >
                                    Livable
                                </option>

                                <option
                                    value="partially_livable"
                                    {{ $selectedLivability === 'partially_livable' ? 'selected' : '' }}
                                >
                                    Partially Livable
                                </option>

                                <option
                                    value="unlivable"
                                    {{ $selectedLivability === 'unlivable' ? 'selected' : '' }}
                                >
                                    Unlivable
                                </option>

                            </select>

                            @error('house_livability')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    </div>


                    {{-- Estimated Property Loss --}}
                    <div class="mb-3">

                        <label
                            for="estimated_property_loss"
                            class="form-label"
                        >
                            Estimated Property / Asset Loss (₱)
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="estimated_property_loss"
                            id="estimated_property_loss"
                            class="form-control @error('estimated_property_loss') is-invalid @enderror"
                            value="{{ old(
                                'estimated_property_loss',
                                $disasterAssessment->estimated_property_loss ?? '0.00'
                            ) }}"
                            placeholder="0.00"
                            required
                        >

                        @error('estimated_property_loss')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                        <small class="text-muted">
                            Enter the estimated value of damaged or lost property/assets.
                        </small>

                    </div>

                </div>


                {{-- =================================================
                     3. HOUSEHOLD IMPACT
                     ================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        3. Household Impact
                    </h5>

                    <div class="row">

                        {{-- Household Affected --}}
                        <div class="col-md-6 mb-3">

                            <label
                                for="household_affected"
                                class="form-label"
                            >
                                Was the Household Affected?
                                <span class="text-danger">*</span>
                            </label>

                            @php

                                $selectedHouseholdAffected = old(
                                    'household_affected',
                                    $disasterAssessment->household_affected ? '1' : '0'
                                );

                            @endphp

                            <select
                                name="household_affected"
                                id="household_affected"
                                class="form-select @error('household_affected') is-invalid @enderror"
                                required
                            >

                                <option
                                    value="1"
                                    {{ $selectedHouseholdAffected === '1' ? 'selected' : '' }}
                                >
                                    Yes
                                </option>

                                <option
                                    value="0"
                                    {{ $selectedHouseholdAffected === '0' ? 'selected' : '' }}
                                >
                                    No
                                </option>

                            </select>

                            @error('household_affected')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        {{-- Affected Members --}}
                        <div class="col-md-6 mb-3">

                            <label
                                for="affected_members"
                                class="form-label"
                            >
                                Number of Affected Household Members
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                min="0"
                                name="affected_members"
                                id="affected_members"
                                class="form-control @error('affected_members') is-invalid @enderror"
                                value="{{ old(
                                    'affected_members',
                                    $disasterAssessment->affected_members ?? 0
                                ) }}"
                                required
                            >

                            @error('affected_members')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     4. EVACUATION INFORMATION
                     ================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        4. Evacuation Information
                    </h5>

                    <div class="row">

                        {{-- Evacuated --}}
                        <div class="col-md-4 mb-3">

                            <label
                                for="evacuated"
                                class="form-label"
                            >
                                Was the Household Evacuated?
                                <span class="text-danger">*</span>
                            </label>

                            @php

                                $selectedEvacuated = old(
                                    'evacuated',
                                    $disasterAssessment->evacuated ? '1' : '0'
                                );

                            @endphp

                            <select
                                name="evacuated"
                                id="evacuated"
                                class="form-select @error('evacuated') is-invalid @enderror"
                                required
                            >

                                <option
                                    value="0"
                                    {{ $selectedEvacuated === '0' ? 'selected' : '' }}
                                >
                                    No
                                </option>

                                <option
                                    value="1"
                                    {{ $selectedEvacuated === '1' ? 'selected' : '' }}
                                >
                                    Yes
                                </option>

                            </select>

                            @error('evacuated')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        {{-- Evacuation Center --}}
                        <div class="col-md-8 mb-3">

                            <label
                                for="evacuation_center"
                                class="form-label"
                            >
                                Evacuation Center
                            </label>

                            <input
                                type="text"
                                name="evacuation_center"
                                id="evacuation_center"
                                class="form-control @error('evacuation_center') is-invalid @enderror"
                                value="{{ old(
                                    'evacuation_center',
                                    $disasterAssessment->evacuation_center
                                ) }}"
                                placeholder="Enter evacuation center if applicable"
                            >

                            @error('evacuation_center')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                            <small class="text-muted">
                                Enter the evacuation center only when the household was evacuated.
                            </small>

                        </div>

                    </div>

                </div>


                <hr>


                {{-- =================================================
                     5. LIVELIHOOD IMPACT
                     ================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        5. Livelihood Impact
                    </h5>

                    <div class="mb-3">

                        <label
                            for="livelihood_impact"
                            class="form-label"
                        >
                            Livelihood / Source of Income Impact
                        </label>

                        <textarea
                            name="livelihood_impact"
                            id="livelihood_impact"
                            rows="4"
                            class="form-control @error('livelihood_impact') is-invalid @enderror"
                            placeholder="Describe the effect of the disaster on the household's work, livelihood, business, or source of income..."
                        >{{ old(
                            'livelihood_impact',
                            $disasterAssessment->livelihood_impact
                        ) }}</textarea>

                        @error('livelihood_impact')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>


                {{-- =================================================
                     6. HOUSEHOLD INTERVIEW
                     ================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        6. Household Interview
                    </h5>

                    <div class="mb-3">

                        <label
                            for="interview_notes"
                            class="form-label"
                        >
                            Interview Notes
                        </label>

                        <textarea
                            name="interview_notes"
                            id="interview_notes"
                            rows="4"
                            class="form-control @error('interview_notes') is-invalid @enderror"
                            placeholder="Record important information obtained during the household interview..."
                        >{{ old(
                            'interview_notes',
                            $disasterAssessment->interview_notes
                        ) }}</textarea>

                        @error('interview_notes')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>


                {{-- =================================================
                     7. REMARKS
                     ================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        7. Remarks
                    </h5>

                    <div class="mb-3">

                        <label
                            for="remarks"
                            class="form-label"
                        >
                            Additional Remarks
                        </label>

                        <textarea
                            name="remarks"
                            id="remarks"
                            rows="4"
                            class="form-control @error('remarks') is-invalid @enderror"
                            placeholder="Additional observations or remarks..."
                        >{{ old(
                            'remarks',
                            $disasterAssessment->remarks
                        ) }}</textarea>

                        @error('remarks')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>


                {{-- =================================================
                     VERIFICATION WORKFLOW
                     ================================================= --}}
                <div class="alert alert-info">

                    <strong>
                        Assessment Verification Workflow
                    </strong>

                    <p class="mb-0 mt-1">

                        After this assessment is updated, it will be returned
                        to <strong>Pending Verification</strong>.

                        A barangay staff member must review the updated
                        information before the assessment can become eligible
                        for a relief voucher.

                    </p>

                </div>


                {{-- =================================================
                     FORM ACTIONS
                     ================================================= --}}
                <div class="d-flex justify-content-between mt-4">

                    <a
                        href="{{ route('disaster-assessments.show', $disasterAssessment->id) }}"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Update Assessment
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


{{-- =============================================================
     EVACUATION CENTER JAVASCRIPT
     ============================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const evacuatedSelect =
        document.getElementById('evacuated');

    const evacuationCenter =
        document.getElementById('evacuation_center');

    if (!evacuatedSelect || !evacuationCenter) {
        return;
    }

    function updateEvacuationCenter(clearWhenDisabled = false) {

        if (evacuatedSelect.value === '1') {

            evacuationCenter.disabled = false;

        } else {

            evacuationCenter.disabled = true;

            if (clearWhenDisabled) {
                evacuationCenter.value = '';
            }

        }

    }

    // Do not erase an existing saved evacuation center on initial page load.
    updateEvacuationCenter(false);

    evacuatedSelect.addEventListener('change', function () {

        updateEvacuationCenter(true);

    });

});
</script>

@endsection
