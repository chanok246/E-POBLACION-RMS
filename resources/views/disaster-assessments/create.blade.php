@extends('layouts.app')

@section('content')
<div class="container">

    {{-- =========================================================
         PAGE HEADER
         ========================================================= --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h2 class="mb-1">
                Encode House-to-House Disaster Assessment
            </h2>

            <small class="text-muted">
                Encode the information collected by the BHW / Barangay Staff
                from the field assessment paper.
            </small>
        </div>

        <a
            href="{{ route('disaster-assessments.index') }}"
            class="btn btn-outline-secondary"
        >
            ← Back to Assessments
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
         MAIN FORM
         ========================================================= --}}
    <div class="card shadow-sm">

        {{-- =====================================================
             HEADER
             ===================================================== --}}
        <div class="card-header">

            <h4 class="mb-1">
                House-to-House Disaster Assessment
            </h4>

            <small class="text-muted">
                Field assessment encoding
            </small>

        </div>


        <div class="card-body">

            <form
                action="{{ route('disaster-assessments.store') }}"
                method="POST"
            >

                @csrf


                {{-- =========================================================
                     1. DISASTER INFORMATION
                     ========================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        1. Disaster Information
                    </h5>

                    <div class="mb-3">

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
                                        $selectedDisasterEventId ?? ''
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
                            Select the disaster event related to this field assessment.
                        </small>

                    </div>

                </div>


                {{-- =========================================================
                     2. HOUSEHOLD INFORMATION
                     ========================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        2. Household Information
                    </h5>

                    <div class="mb-3">

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
                                -- Choose Household --
                            </option>

                            @foreach($households as $household)

                                @php

                                    $headName = 'No household head recorded';

                                    if ($household->householdHead) {

                                        $head = $household->householdHead;

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
                                        $selectedHouseholdId ?? ''
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
                            Select the household identified during the field visit.
                        </small>

                    </div>

                </div>


                {{-- =========================================================
                     3. ASSESSMENT DETAILS
                     ========================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        3. Assessment Details
                    </h5>

                    <div class="row">

                        {{-- Assessment Date --}}
                        <div class="col-md-6 mb-3">

                            <label
                                for="assessment_date"
                                class="form-label"
                            >
                                Date of Assessment
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="date"
                                name="assessment_date"
                                id="assessment_date"
                                class="form-control @error('assessment_date') is-invalid @enderror"
                                value="{{ old(
                                    'assessment_date',
                                    date('Y-m-d')
                                ) }}"
                                required
                            >

                            @error('assessment_date')

                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    </div>

                </div>


                {{-- =========================================================
                     4. HOUSE CONDITION
                     ========================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        4. House Condition
                    </h5>

                    <div class="row">

                        {{-- Damage Level --}}
                        <div class="col-md-6 mb-3">

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
                                    'none'
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

                            <small class="text-muted">
                                Record the level of physical damage observed during the field assessment.
                            </small>

                        </div>


                        {{-- House Livability --}}
                        <div class="col-md-6 mb-3">

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
                                    'livable'
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
                                '0.00'
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


                {{-- =========================================================
                     5. HOUSEHOLD IMPACT
                     ========================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        5. Household Impact
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
                                    '1'
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

                            <small class="text-muted">
                                This should reflect the result of the field assessment.
                            </small>

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
                                name="affected_members"
                                id="affected_members"
                                min="0"
                                class="form-control @error('affected_members') is-invalid @enderror"
                                value="{{ old(
                                    'affected_members',
                                    '0'
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


                    {{-- Evacuation --}}
                    <div class="mb-3">

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
                                '0'
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
                    <div class="mb-3">

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
                            value="{{ old('evacuation_center') }}"
                            placeholder="Enter evacuation center if applicable"
                        >

                        @error('evacuation_center')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                        <small class="text-muted">
                            Required only when the household was evacuated.
                        </small>

                    </div>

                </div>


                {{-- =========================================================
                     6. LIVELIHOOD IMPACT
                     ========================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        6. Livelihood Impact
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
                        >{{ old('livelihood_impact') }}</textarea>

                        @error('livelihood_impact')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>


                {{-- =========================================================
                     7. HOUSEHOLD INTERVIEW
                     ========================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        7. Household Interview
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
                            placeholder="Record important information provided by the household during the interview..."
                        >{{ old('interview_notes') }}</textarea>

                        @error('interview_notes')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>


                {{-- =========================================================
                     8. REMARKS
                     ========================================================= --}}
                <div class="mb-4">

                    <h5 class="mb-3">
                        8. Remarks
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
                        >{{ old('remarks') }}</textarea>

                        @error('remarks')

                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>

                        @enderror

                    </div>

                </div>


                {{-- =========================================================
                     VERIFICATION NOTICE
                     ========================================================= --}}
                <div class="alert alert-info">

                    <strong>
                        Assessment Verification
                    </strong>

                    <p class="mb-0 mt-1">

                        After saving, this assessment will initially have
                        <strong>Pending Verification</strong> status.

                        A designated barangay staff member must review and
                        verify the assessment before the household can become
                        eligible for a relief voucher.

                    </p>

                </div>


                {{-- =========================================================
                     VOUCHER ELIGIBILITY NOTICE
                     ========================================================= --}}
                <div class="alert alert-warning">

                    <strong>
                        Important:
                    </strong>

                    <span>
                        Saving this assessment does <strong>not</strong>
                        automatically generate a relief voucher.
                        Voucher eligibility is determined during the
                        verification process.
                    </span>

                </div>


                {{-- =========================================================
                     FORM BUTTONS
                     ========================================================= --}}
                <div class="d-flex gap-2">

                    <button
                        type="submit"
                        class="btn btn-success"
                    >
                        Save Assessment
                    </button>

                    <a
                        href="{{ route('disaster-assessments.index') }}"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>


{{-- =============================================================
     EVACUATION CENTER BEHAVIOR
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

    function updateEvacuationCenter() {

        if (evacuatedSelect.value === '1') {

            evacuationCenter.disabled = false;

        } else {

            evacuationCenter.disabled = true;
            evacuationCenter.value = '';

        }
    }

    updateEvacuationCenter();

    evacuatedSelect.addEventListener(
        'change',
        updateEvacuationCenter
    );

});
</script>

@endsection
