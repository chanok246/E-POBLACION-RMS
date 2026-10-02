@extends('layouts.app')

@section('title', 'Create Disaster Event')

@section('content')

<div class="container-fluid py-4">

```
{{-- ================================================================
     PAGE HEADER
================================================================= --}}
<div class="d-flex flex-column flex-md-row align-items-md-center mb-4">

    <a href="{{ route('disaster-events.index') }}"
       class="btn btn-outline-secondary me-md-3 mb-3 mb-md-0">

        <i class="bi bi-arrow-left"></i>

    </a>

    <div>
        <h2 class="fw-bold mb-1">
            Create Disaster Event
        </h2>

        <p class="text-muted mb-0">
            Record a disaster incident or assistance campaign for Barangay Poblacion.
        </p>
    </div>

</div>


{{-- ================================================================
     FLASH MESSAGES
================================================================= --}}
@if(session('success'))

    <div class="alert alert-success alert-dismissible fade show shadow-sm"
         role="alert">

        <i class="bi bi-check-circle-fill me-2"></i>

        {{ session('success') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>

    </div>

@endif


@if(session('error'))

    <div class="alert alert-danger alert-dismissible fade show shadow-sm"
         role="alert">

        <i class="bi bi-exclamation-triangle-fill me-2"></i>

        {{ session('error') }}

        <button type="button"
                class="btn-close"
                data-bs-dismiss="alert">
        </button>

    </div>

@endif


{{-- ================================================================
     VALIDATION ERRORS
================================================================= --}}
@if($errors->any())

    <div class="alert alert-danger shadow-sm">

        <div class="d-flex">

            <div class="me-3">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
            </div>

            <div>

                <h6 class="fw-bold mb-2">
                    Please correct the following errors:
                </h6>

                <ul class="mb-0">

                    @foreach($errors->all() as $error)

                        <li>
                            {{ $error }}
                        </li>

                    @endforeach

                </ul>

            </div>

        </div>

    </div>

@endif


{{-- ================================================================
     WORKFLOW NOTICE
================================================================= --}}
<div class="alert alert-info border-0 shadow-sm mb-4">

    <div class="d-flex">

        <div class="me-3">
            <i class="bi bi-info-circle-fill fs-4"></i>
        </div>

        <div>

            <h6 class="fw-bold mb-1">
                Disaster Response Workflow
            </h6>

            <p class="mb-0 small">

                Creating this event only records the disaster or assistance
                campaign. Household assessments, official verification,
                beneficiary eligibility, and QR relief voucher generation
                are performed in the following stages.

            </p>

        </div>

    </div>

</div>


<form method="POST"
      action="{{ route('disaster-events.store') }}">

    @csrf


    {{-- ============================================================
         DISASTER INFORMATION
    ============================================================= --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">

            <h5 class="fw-bold mb-0">

                <i class="bi bi-cloud-lightning-rain me-2"></i>

                Disaster / Campaign Information

            </h5>

        </div>


        <div class="card-body">

            <div class="row g-3">

                {{-- Event Name --}}
                <div class="col-12 col-lg-8">

                    <label for="name"
                           class="form-label fw-semibold">

                        Disaster / Event Name

                        <span class="text-danger">*</span>

                    </label>

                    <input type="text"
                           id="name"
                           name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}"
                           maxlength="150"
                           placeholder="Example: Typhoon Ulysses Relief Assistance"
                           required>

                    @error('name')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                    <div class="form-text">
                        Use a clear name that identifies the disaster,
                        emergency, or assistance campaign.
                    </div>

                </div>


                {{-- Disaster Type --}}
                <div class="col-12 col-lg-4">

                    <label for="type"
                           class="form-label fw-semibold">

                        Disaster / Event Type

                        <span class="text-danger">*</span>

                    </label>

                    <select id="type"
                            name="type"
                            class="form-select @error('type') is-invalid @enderror"
                            required>

                        <option value="">
                            Select disaster type
                        </option>

                        @foreach($disasterTypes as $disasterType)

                            <option value="{{ $disasterType }}"
                                @selected(old('type') === $disasterType)>

                                {{ $disasterType }}

                            </option>

                        @endforeach

                    </select>

                    @error('type')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- Description --}}
                <div class="col-12">

                    <label for="description"
                           class="form-label fw-semibold">

                        Description

                    </label>

                    <textarea id="description"
                              name="description"
                              rows="4"
                              maxlength="5000"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Describe the incident, affected areas, assistance purpose, or other important information...">{{ old('description') }}</textarea>

                    @error('description')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         EVENT SCHEDULE
    ============================================================= --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">

            <h5 class="fw-bold mb-0">

                <i class="bi bi-calendar-event me-2"></i>

                Event Schedule

            </h5>

        </div>


        <div class="card-body">

            <div class="row g-3">

                {{-- Start Date --}}
                <div class="col-12 col-md-4">

                    <label for="start_date"
                           class="form-label fw-semibold">

                        Start Date

                        <span class="text-danger">*</span>

                    </label>

                    <input type="date"
                           id="start_date"
                           name="start_date"
                           class="form-control @error('start_date') is-invalid @enderror"
                           value="{{ old('start_date') }}"
                           required>

                    @error('start_date')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- End Date --}}
                <div class="col-12 col-md-4">

                    <label for="end_date"
                           class="form-label fw-semibold">

                        End Date

                    </label>

                    <input type="date"
                           id="end_date"
                           name="end_date"
                           class="form-control @error('end_date') is-invalid @enderror"
                           value="{{ old('end_date') }}">

                    @error('end_date')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                    <div class="form-text">
                        Leave blank if the event is still ongoing.
                    </div>

                </div>


                {{-- Status --}}
                <div class="col-12 col-md-4">

                    <label for="status"
                           class="form-label fw-semibold">

                        Status

                        <span class="text-danger">*</span>

                    </label>

                    <select id="status"
                            name="status"
                            class="form-select @error('status') is-invalid @enderror"
                            required>

                        @foreach($statuses as $eventStatus)

                            <option value="{{ $eventStatus }}"
                                @selected(old('status', 'Active') === $eventStatus)>

                                {{ $eventStatus }}

                            </option>

                        @endforeach

                    </select>

                    @error('status')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         ASSISTANCE INFORMATION
    ============================================================= --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">

            <h5 class="fw-bold mb-0">

                <i class="bi bi-box-seam me-2"></i>

                Assistance Program

            </h5>

        </div>


        <div class="card-body">

            <div class="alert alert-light border mb-3">

                <div class="d-flex">

                    <i class="bi bi-info-circle text-primary me-2"></i>

                    <small class="text-muted">

                        The assistance program defines what type of support
                        may be distributed under this event. Individual
                        household eligibility is determined later through
                        the assessment and verification process.

                    </small>

                </div>

            </div>


            <div class="row g-3">

                {{-- Assistance Type --}}
                <div class="col-12 col-md-6">

                    <label for="assistance_type"
                           class="form-label fw-semibold">

                        Assistance Type

                        <span class="text-danger">*</span>

                    </label>

                    <select id="assistance_type"
                            name="assistance_type"
                            class="form-select @error('assistance_type') is-invalid @enderror"
                            required>

                        <option value="">
                            Select assistance type
                        </option>

                        @foreach($assistanceTypes as $assistanceType)

                            <option value="{{ $assistanceType }}"
                                @selected(old('assistance_type') === $assistanceType)>

                                {{ $assistanceType }}

                            </option>

                        @endforeach

                    </select>

                    @error('assistance_type')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- Assistance Amount --}}
                <div class="col-12 col-md-6">

                    <label for="assistance_amount"
                           class="form-label fw-semibold">

                        Assistance Amount

                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            ₱
                        </span>

                        <input type="number"
                               id="assistance_amount"
                               name="assistance_amount"
                               class="form-control @error('assistance_amount') is-invalid @enderror"
                               value="{{ old('assistance_amount') }}"
                               min="0"
                               step="0.01"
                               placeholder="0.00">

                    </div>

                    @error('assistance_amount')

                        <div class="text-danger small mt-1">
                            {{ $message }}
                        </div>

                    @enderror

                    <div class="form-text">
                        Enter the amount only when the assistance program
                        has a fixed monetary value.
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         AFFECTED LOCATION
    ============================================================= --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white py-3">

            <h5 class="fw-bold mb-0">

                <i class="bi bi-geo-alt me-2"></i>

                Affected Location

            </h5>

        </div>


        <div class="card-body">

            <div class="alert alert-warning border-0 mb-3">

                <div class="d-flex">

                    <i class="bi bi-pin-map-fill me-2"></i>

                    <small>

                        This event is currently scoped to
                        <strong>Barangay Poblacion, Bani, Pangasinan</strong>.
                        Keep the location information accurate because it
                        is used when viewing disaster records and related
                        household assessments.

                    </small>

                </div>

            </div>


            <div class="row g-3">

                {{-- Barangay --}}
                <div class="col-12 col-md-4">

                    <label for="barangay"
                           class="form-label fw-semibold">

                        Barangay

                        <span class="text-danger">*</span>

                    </label>

                    <input type="text"
                           id="barangay"
                           name="barangay"
                           class="form-control @error('barangay') is-invalid @enderror"
                           value="{{ old('barangay', 'Poblacion') }}"
                           maxlength="150"
                           required>

                    @error('barangay')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- Municipality --}}
                <div class="col-12 col-md-4">

                    <label for="municipality"
                           class="form-label fw-semibold">

                        Municipality

                        <span class="text-danger">*</span>

                    </label>

                    <input type="text"
                           id="municipality"
                           name="municipality"
                           class="form-control @error('municipality') is-invalid @enderror"
                           value="{{ old('municipality', 'Bani') }}"
                           maxlength="150"
                           required>

                    @error('municipality')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                {{-- Province --}}
                <div class="col-12 col-md-4">

                    <label for="province"
                           class="form-label fw-semibold">

                        Province

                        <span class="text-danger">*</span>

                    </label>

                    <input type="text"
                           id="province"
                           name="province"
                           class="form-control @error('province') is-invalid @enderror"
                           value="{{ old('province', 'Pangasinan') }}"
                           maxlength="150"
                           required>

                    @error('province')

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         PROCESS INFORMATION
    ============================================================= --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-body">

            <h6 class="fw-bold mb-3">

                <i class="bi bi-diagram-3 me-2"></i>

                What happens after this event is created?

            </h6>


            <div class="row g-3">

                <div class="col-12 col-md">

                    <div class="border rounded p-3 h-100">

                        <div class="fw-bold small mb-1">
                            1. Create Event
                        </div>

                        <small class="text-muted">
                            Record the disaster or assistance campaign.
                        </small>

                    </div>

                </div>


                <div class="col-12 col-md">

                    <div class="border rounded p-3 h-100">

                        <div class="fw-bold small mb-1">
                            2. Household Assessment
                        </div>

                        <small class="text-muted">
                            Record the impact on affected households.
                        </small>

                    </div>

                </div>


                <div class="col-12 col-md">

                    <div class="border rounded p-3 h-100">

                        <div class="fw-bold small mb-1">
                            3. Official Verification
                        </div>

                        <small class="text-muted">
                            Review the assessment and determine eligibility.
                        </small>

                    </div>

                </div>


                <div class="col-12 col-md">

                    <div class="border rounded p-3 h-100">

                        <div class="fw-bold small mb-1">
                            4. QR Voucher
                        </div>

                        <small class="text-muted">
                            Generate a voucher for an approved beneficiary.
                        </small>

                    </div>

                </div>


                <div class="col-12 col-md">

                    <div class="border rounded p-3 h-100">

                        <div class="fw-bold small mb-1">
                            5. Distribution
                        </div>

                        <small class="text-muted">
                            Scan the QR voucher and record the claim.
                        </small>

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
         FORM ACTIONS
    ============================================================= --}}
    <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-5">

        <a href="{{ route('disaster-events.index') }}"
           class="btn btn-outline-secondary">

            <i class="bi bi-x-circle me-1"></i>

            Cancel

        </a>


        <button type="submit"
                class="btn btn-primary">

            <i class="bi bi-check-circle me-1"></i>

            Create Disaster Event

        </button>

    </div>

</form>

</div>

@endsection
