
@extends('layouts.app')

@section('title', 'Edit Personnel | Attendance Monitoring')

@section('content')
@php
    $fullName = trim(implode(' ', array_filter([
        $personnel->first_name,
        $personnel->middle_name,
        $personnel->last_name,
        $personnel->suffix,
    ])));

    $isActive = $personnel->status === 'active';
@endphp

<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('attendance.personnel.show', $personnel) }}"
           class="text-decoration-none text-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Personnel Profile
        </a>

        <h4 class="fw-bold mt-2 mb-1">Edit Personnel</h4>
        <p class="text-secondary mb-0">
            Update the personnel's basic information and optional RMS account link.
        </p>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Validation errors --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Please correct the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">

        {{-- Main edit form --}}
        <div class="col-lg-8">
            <form method="POST"
                  action="{{ route('attendance.personnel.update', $personnel) }}"
                  onsubmit="return confirm('Save the updated personnel information?');">
                @csrf
                @method('PUT')

                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-transparent py-3">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-person-vcard me-2"></i>
                            Personnel Information
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            {{-- Personnel ID: read only --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Personnel ID
                                </label>

                                <input type="text"
                                       class="form-control bg-light"
                                       value="{{ $personnel->personnel_id }}"
                                       readonly>

                                <small class="text-secondary">
                                    Personnel ID cannot be changed after registration.
                                </small>
                            </div>

                            {{-- Position --}}
                            <div class="col-md-6">
                                <label for="position" class="form-label fw-semibold">
                                    Position <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="position"
                                       id="position"
                                       class="form-control @error('position') is-invalid @enderror"
                                       value="{{ old('position', $personnel->position) }}"
                                       maxlength="100"
                                       required>

                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- First name --}}
                            <div class="col-md-6">
                                <label for="first_name" class="form-label fw-semibold">
                                    First Name <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="first_name"
                                       id="first_name"
                                       class="form-control @error('first_name') is-invalid @enderror"
                                       value="{{ old('first_name', $personnel->first_name) }}"
                                       maxlength="100"
                                       required>

                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Middle name --}}
                            <div class="col-md-6">
                                <label for="middle_name" class="form-label fw-semibold">
                                    Middle Name
                                </label>

                                <input type="text"
                                       name="middle_name"
                                       id="middle_name"
                                       class="form-control @error('middle_name') is-invalid @enderror"
                                       value="{{ old('middle_name', $personnel->middle_name) }}"
                                       maxlength="100">

                                @error('middle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Last name --}}
                            <div class="col-md-6">
                                <label for="last_name" class="form-label fw-semibold">
                                    Last Name <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="last_name"
                                       id="last_name"
                                       class="form-control @error('last_name') is-invalid @enderror"
                                       value="{{ old('last_name', $personnel->last_name) }}"
                                       maxlength="100"
                                       required>

                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Suffix --}}
                            <div class="col-md-6">
                                <label for="suffix" class="form-label fw-semibold">
                                    Suffix
                                </label>

                                <input type="text"
                                       name="suffix"
                                       id="suffix"
                                       class="form-control @error('suffix') is-invalid @enderror"
                                       value="{{ old('suffix', $personnel->suffix) }}"
                                       maxlength="20"
                                       placeholder="Jr., Sr., III">

                                @error('suffix')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- RMS account linking --}}
                <div class="card border-0 shadow-sm mb-4">

                    <div class="card-header bg-transparent py-3">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-link-45deg me-2"></i>
                            RMS Account Linking
                            <span class="badge bg-secondary-subtle text-secondary ms-1">
                                Optional
                            </span>
                        </h5>
                    </div>

                    <div class="card-body">

                        <p class="text-secondary small">
                            Only existing active Barangay Captain or Barangay
                            Secretary accounts may be linked. Ordinary personnel
                            do not need an RMS account.
                        </p>

                        <label for="user_id" class="form-label fw-semibold">
                            Linked RMS Account
                        </label>

                        <select name="user_id"
                                id="user_id"
                                class="form-select @error('user_id') is-invalid @enderror">

                            <option value="">
                                No RMS account linked
                            </option>

                            @foreach($eligibleUsers as $eligibleUser)
                                <option value="{{ $eligibleUser->id }}"
                                    @selected(
                                        (string) old('user_id', $personnel->user_id)
                                        === (string) $eligibleUser->id
                                    )>
                                    {{ $eligibleUser->name }}
                                    ({{ ucfirst($eligibleUser->role) }})
                                </option>
                            @endforeach

                        </select>

                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        <small class="text-secondary d-block mt-2">
                            Select the existing account belonging to this personnel,
                            or leave unlinked.
                        </small>

                    </div>
                </div>

                {{-- Form actions --}}
                <div class="d-flex flex-wrap justify-content-end gap-2">

                    <a href="{{ route('attendance.personnel.show', $personnel) }}"
                       class="btn btn-outline-secondary">
                        Cancel
                    </a>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>
                        Save Changes
                    </button>

                </div>

            </form>
        </div>

        {{-- Personnel status and security information --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent py-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Personnel Summary
                    </h5>
                </div>

                <div class="card-body">

                    <div class="mb-3">
                        <small class="text-secondary d-block mb-1">
                            Full Name
                        </small>
                        <div class="fw-semibold">
                            {{ $fullName ?: 'Unnamed Personnel' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-secondary d-block mb-1">
                            Personnel ID
                        </small>
                        <div class="fw-semibold">
                            {{ $personnel->personnel_id }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <small class="text-secondary d-block mb-1">
                            Current Status
                        </small>

                        @if($isActive)
                            <span class="badge bg-success-subtle text-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Active
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">
                                <i class="bi bi-person-slash me-1"></i>
                                Inactive
                            </span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <small class="text-secondary d-block mb-1">
                            QR ID
                        </small>

                        @if($personnel->qr_token_hash && !$personnel->qr_revoked_at)
                            <span class="text-success fw-semibold">
                                <i class="bi bi-check-circle me-1"></i>
                                Issued
                            </span>
                        @else
                            <span class="text-secondary">
                                Not issued
                            </span>
                        @endif
                    </div>

                    <div>
                        <small class="text-secondary d-block mb-1">
                            PIN
                        </small>

                        @if($personnel->pin_hash)
                            <span class="text-success fw-semibold">
                                <i class="bi bi-shield-check me-1"></i>
                                Configured
                            </span>
                        @else
                            <span class="text-danger fw-semibold">
                                Not configured
                            </span>
                        @endif
                    </div>

                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body">

                    <h6 class="fw-semibold">
                        <i class="bi bi-shield-lock me-2"></i>
                        Security Reminder
                    </h6>

                    <ul class="small text-secondary ps-3 mb-0">
                        <li class="mb-2">
                            Personnel ID cannot be edited here.
                        </li>
                        <li class="mb-2">
                            Existing PIN is preserved when editing the profile.
                        </li>
                        <li class="mb-2">
                            QR credentials are not changed by this form.
                        </li>
                        <li>
                            Only the Barangay Captain may reset a personnel PIN.
                        </li>
                    </ul>

                </div>
            </div>

        </div>

    </div>

</div>
@endsection
