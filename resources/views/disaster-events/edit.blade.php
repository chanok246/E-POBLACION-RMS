```blade
@extends('layouts.app')

@section('title', 'Edit Disaster Event')

@section('content')

<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex align-items-center mb-4">

        <a
            href="{{ route('disaster-events.show', $disasterEvent) }}"
            class="btn btn-outline-secondary me-3"
            title="Back to Disaster Event"
        >
            <i class="bi bi-arrow-left"></i>
        </a>

        <div>
            <h2 class="fw-bold mb-1">
                Edit Disaster Event
            </h2>

            <p class="text-muted mb-0">
                {{ $disasterEvent->event_code ?? 'No Event Code' }}
                —
                {{ $disasterEvent->name ?? 'Unnamed Event' }}
            </p>
        </div>

    </div>


    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">

            <i class="bi bi-check-circle me-1"></i>

            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>
    @endif


    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">

            <i class="bi bi-exclamation-triangle me-1"></i>

            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close"
            ></button>

        </div>
    @endif


    {{-- Validation Errors --}}
    @if($errors->any())

        <div class="alert alert-danger">

            <h6 class="fw-bold">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Please correct the following:
            </h6>

            <ul class="mb-0">

                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach

            </ul>

        </div>

    @endif


    {{-- Workflow Warning --}}
    <div class="alert alert-warning border-0 shadow-sm mb-4">

        <div class="d-flex">

            <div class="me-3">
                <i class="bi bi-info-circle-fill fs-4"></i>
            </div>

            <div>

                <h6 class="fw-bold mb-1">
                    Important
                </h6>

                <p class="mb-1">
                    Editing this disaster event changes the campaign/event information
                    used by the assessment and relief assistance workflow.
                </p>

                <small>
                    Existing disaster assessments and issued vouchers are not automatically
                    deleted when this event is edited.
                </small>

            </div>

        </div>

    </div>


    <form
        method="POST"
        action="{{ route('disaster-events.update', $disasterEvent) }}"
    >

        @csrf
        @method('PUT')


        {{-- Basic Information --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">
                    <i class="bi bi-info-circle me-2"></i>
                    Disaster / Campaign Information
                </h5>

            </div>


            <div class="card-body">

                <div class="row g-3">

                    {{-- Event Code --}}
                    <div class="col-12 col-md-4">

                        <label class="form-label fw-semibold">
                            Event Code
                        </label>

                        <input
                            type="text"
                            class="form-control bg-light"
                            value="{{ $disasterEvent->event_code ?? '' }}"
                            readonly
                        >

                        <div class="form-text">
                            Event codes cannot be changed.
                        </div>

                    </div>


                    {{-- Event Name --}}
                    <div class="col-12 col-md-8">

                        <label class="form-label fw-semibold">
                            Disaster / Event Name
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name', $disasterEvent->name) }}"
                            maxlength="150"
                            required
                        >

                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Disaster Type --}}
                    <div class="col-12 col-md-6">

                        <label class="form-label fw-semibold">
                            Disaster / Program Type
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="type"
                            class="form-select @error('type') is-invalid @enderror"
                            required
                        >

                            @foreach($disasterTypes as $disasterType)

                                <option
                                    value="{{ $disasterType }}"
                                    @selected(
                                        old('type', $disasterEvent->type) === $disasterType
                                    )
                                >
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


                    {{-- Status --}}
                    <div class="col-12 col-md-6">

                        <label class="form-label fw-semibold">
                            Status
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="status"
                            class="form-select @error('status') is-invalid @enderror"
                            required
                        >

                            @foreach($statuses as $eventStatus)

                                <option
                                    value="{{ $eventStatus }}"
                                    @selected(
                                        old('status', $disasterEvent->status) === $eventStatus
                                    )
                                >
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


                    {{-- Description --}}
                    <div class="col-12">

                        <label class="form-label fw-semibold">
                            Description
                        </label>

                        <textarea
                            name="description"
                            class="form-control @error('description') is-invalid @enderror"
                            rows="4"
                            maxlength="1000"
                        >{{ old('description', $disasterEvent->description) }}</textarea>

                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>


        {{-- Schedule --}}
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
                    <div class="col-12 col-md-6">

                        <label class="form-label fw-semibold">
                            Start Date
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="start_date"
                            class="form-control @error('start_date') is-invalid @enderror"
                            value="{{
                                old(
                                    'start_date',
                                    $disasterEvent->start_date
                                        ? $disasterEvent->start_date->format('Y-m-d')
                                        : ''
                                )
                            }}"
                            required
                        >

                        @error('start_date')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- End Date --}}
                    <div class="col-12 col-md-6">

                        <label class="form-label fw-semibold">
                            End Date
                        </label>

                        <input
                            type="date"
                            name="end_date"
                            class="form-control @error('end_date') is-invalid @enderror"
                            value="{{
                                old(
                                    'end_date',
                                    $disasterEvent->end_date
                                        ? $disasterEvent->end_date->format('Y-m-d')
                                        : ''
                                )
                            }}"
                        >

                        @error('end_date')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>


        {{-- Assistance --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">
                    <i class="bi bi-box-seam me-2"></i>
                    Assistance Information
                </h5>

            </div>


            <div class="card-body">

                <div class="alert alert-light border mb-3">

                    <small class="text-muted">
                        <i class="bi bi-lightbulb me-1"></i>

                        This assistance information identifies the type of support
                        associated with this campaign. It does not automatically
                        generate a relief voucher.
                    </small>

                </div>


                <div class="row g-3">

                    {{-- Assistance Type --}}
                    <div class="col-12 col-md-6">

                        <label class="form-label fw-semibold">
                            Assistance Type
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="assistance_type"
                            class="form-select @error('assistance_type') is-invalid @enderror"
                            required
                        >

                            @foreach($assistanceTypes as $assistanceType)

                                <option
                                    value="{{ $assistanceType }}"
                                    @selected(
                                        old(
                                            'assistance_type',
                                            $disasterEvent->assistance_type
                                        ) === $assistanceType
                                    )
                                >
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


                    {{-- Amount --}}
                    <div class="col-12 col-md-6">

                        <label class="form-label fw-semibold">
                            Assistance Amount
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                ₱
                            </span>

                            <input
                                type="number"
                                name="assistance_amount"
                                class="form-control @error('assistance_amount') is-invalid @enderror"
                                value="{{ old('assistance_amount', $disasterEvent->assistance_amount) }}"
                                min="0"
                                step="0.01"
                            >

                        </div>

                        @error('assistance_amount')
                            <div class="text-danger small mt-1">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>


        {{-- Location --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">
                    <i class="bi bi-geo-alt me-2"></i>
                    Affected Location
                </h5>

            </div>


            <div class="card-body">

                <div class="alert alert-info border-0 mb-3">

                    <i class="bi bi-geo-alt-fill me-1"></i>

                    The current project scope is
                    <strong>Barangay Poblacion, Bani, Pangasinan</strong>.
                    Keep the location accurate because it identifies the area
                    covered by this disaster or assistance campaign.

                </div>


                <div class="row g-3">

                    {{-- Barangay --}}
                    <div class="col-12 col-md-4">

                        <label class="form-label fw-semibold">
                            Barangay
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="barangay"
                            class="form-control @error('barangay') is-invalid @enderror"
                            value="{{ old('barangay', $disasterEvent->barangay) }}"
                            maxlength="150"
                            required
                        >

                        @error('barangay')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Municipality --}}
                    <div class="col-12 col-md-4">

                        <label class="form-label fw-semibold">
                            Municipality
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="municipality"
                            class="form-control @error('municipality') is-invalid @enderror"
                            value="{{ old('municipality', $disasterEvent->municipality) }}"
                            maxlength="150"
                            required
                        >

                        @error('municipality')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    {{-- Province --}}
                    <div class="col-12 col-md-4">

                        <label class="form-label fw-semibold">
                            Province
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="province"
                            class="form-control @error('province') is-invalid @enderror"
                            value="{{ old('province', $disasterEvent->province) }}"
                            maxlength="150"
                            required
                        >

                        @error('province')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>


        {{-- Workflow Information --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-white py-3">

                <h5 class="fw-bold mb-0">
                    <i class="bi bi-diagram-3 me-2"></i>
                    Relief Assistance Workflow
                </h5>

            </div>

            <div class="card-body">

                <div class="row g-3">

                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 h-100">

                            <div class="fw-bold mb-1">
                                <span class="badge bg-secondary me-1">1</span>
                                Disaster / Campaign
                            </div>

                            <small class="text-muted">
                                This event identifies the disaster or assistance
                                campaign.
                            </small>

                        </div>
                    </div>


                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 h-100">

                            <div class="fw-bold mb-1">
                                <span class="badge bg-secondary me-1">2</span>
                                Field Assessment
                            </div>

                            <small class="text-muted">
                                Households are assessed and their damage and
                                affected status are recorded.
                            </small>

                        </div>
                    </div>


                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 h-100">

                            <div class="fw-bold mb-1">
                                <span class="badge bg-secondary me-1">3</span>
                                Verification
                            </div>

                            <small class="text-muted">
                                Verified eligible assessments can proceed to
                                relief voucher generation.
                            </small>

                        </div>
                    </div>


                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">

                            <div class="fw-bold mb-1">
                                <span class="badge bg-secondary me-1">4</span>
                                QR Voucher
                            </div>

                            <small class="text-muted">
                                Staff generates a unique voucher with a tokenized
                                QR code for the eligible household.
                            </small>

                        </div>
                    </div>


                    <div class="col-12 col-md-6">
                        <div class="border rounded p-3 h-100">

                            <div class="fw-bold mb-1">
                                <span class="badge bg-secondary me-1">5</span>
                                Distribution & One-Time Claim
                            </div>

                            <small class="text-muted">
                                The first valid QR scan records the claim.
                                Subsequent scans are blocked and identified as
                                <strong>ALREADY CLAIMED</strong>.
                            </small>

                        </div>
                    </div>

                </div>

            </div>

        </div>


        {{-- Buttons --}}
        <div class="d-flex justify-content-end gap-2 mb-5">

            <a
                href="{{ route('disaster-events.show', $disasterEvent) }}"
                class="btn btn-outline-secondary"
            >
                <i class="bi bi-x-circle me-1"></i>
                Cancel
            </a>

            <button
                type="submit"
                class="btn btn-primary"
            >
                <i class="bi bi-check-circle me-1"></i>
                Save Changes
            </button>

        </div>

    </form>

</div>

@endsection
