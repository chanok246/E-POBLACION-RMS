
@extends('layouts.app')

@section('title', 'Register Personnel | Attendance Monitoring')

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="mb-4">
        <a href="{{ route('attendance.personnel.index') }}"
           class="text-decoration-none text-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Personnel Management
        </a>

        <div class="mt-2">
            <h4 class="fw-bold mb-1">Register Personnel</h4>
            <p class="text-secondary mb-0">
                Add a barangay personnel record for Attendance Monitoring.
            </p>
        </div>
    </div>

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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('attendance.personnel.store') }}">
        @csrf

        <div class="row g-4">

            {{-- Personnel information --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-person-vcard me-2"></i>
                            Personnel Information
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-6">
                                <label for="personnel_id" class="form-label fw-semibold">
                                    Personnel ID <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="personnel_id"
                                    id="personnel_id"
                                    class="form-control @error('personnel_id') is-invalid @enderror"
                                    value="{{ old('personnel_id') }}"
                                    maxlength="50"
                                    placeholder="e.g. KAWANI-001"
                                    required
                                >
                                @error('personnel_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-secondary">
                                    Manually assign a unique ID. This cannot be duplicated.
                                </small>
                            </div>

                            <div class="col-md-6">
                                <label for="position" class="form-label fw-semibold">
                                    Position <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="position"
                                    id="position"
                                    class="form-control @error('position') is-invalid @enderror"
                                    value="{{ old('position') }}"
                                    maxlength="100"
                                    placeholder="e.g. Barangay Health Worker"
                                    required
                                >
                                @error('position')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="first_name" class="form-label fw-semibold">
                                    First Name <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="first_name"
                                    id="first_name"
                                    class="form-control @error('first_name') is-invalid @enderror"
                                    value="{{ old('first_name') }}"
                                    maxlength="100"
                                    required
                                >
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="middle_name" class="form-label fw-semibold">
                                    Middle Name
                                </label>
                                <input
                                    type="text"
                                    name="middle_name"
                                    id="middle_name"
                                    class="form-control @error('middle_name') is-invalid @enderror"
                                    value="{{ old('middle_name') }}"
                                    maxlength="100"
                                >
                                @error('middle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="last_name" class="form-label fw-semibold">
                                    Last Name <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="last_name"
                                    id="last_name"
                                    class="form-control @error('last_name') is-invalid @enderror"
                                    value="{{ old('last_name') }}"
                                    maxlength="100"
                                    required
                                >
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="suffix" class="form-label fw-semibold">
                                    Suffix
                                </label>
                                <input
                                    type="text"
                                    name="suffix"
                                    id="suffix"
                                    class="form-control @error('suffix') is-invalid @enderror"
                                    value="{{ old('suffix') }}"
                                    maxlength="20"
                                    placeholder="e.g. Jr., Sr., III"
                                >
                                @error('suffix')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Optional RMS account linking --}}
                <div class="card border-0 shadow-sm mt-4">
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
                            Only an existing active Barangay Captain or Barangay
                            Secretary RMS account may be linked. Ordinary personnel
                            do not need an RMS account.
                        </p>

                        <label for="user_id" class="form-label fw-semibold">
                            Existing RMS Account
                        </label>

                        <select
                            name="user_id"
                            id="user_id"
                            class="form-select @error('user_id') is-invalid @enderror"
                        >
                            <option value="">No RMS account linked</option>

                            @foreach($eligibleUsers as $eligibleUser)
                                <option
                                    value="{{ $eligibleUser->id }}"
                                    @selected((string) old('user_id') === (string) $eligibleUser->id)
                                >
                                    {{ $eligibleUser->name }}
                                    ({{ ucfirst($eligibleUser->role) }})
                                </option>
                            @endforeach
                        </select>

                        @error('user_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                        @if($eligibleUsers->isEmpty())
                            <small class="text-secondary d-block mt-2">
                                No eligible active Captain or Secretary accounts are
                                currently available for linking.
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            {{-- PIN setup --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3">
                        <h5 class="fw-semibold mb-0">
                            <i class="bi bi-shield-lock me-2"></i>
                            Attendance PIN
                        </h5>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-warning small">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            The initial PIN is required before the personnel can use
                            the attendance kiosk.
                        </div>

                        <div class="mb-3">
                            <label for="pin" class="form-label fw-semibold">
                                Initial 6-Digit PIN
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <input
                                    type="password"
                                    name="pin"
                                    id="pin"
                                    class="form-control @error('pin') is-invalid @enderror"
                                    inputmode="numeric"
                                    pattern="[0-9]{6}"
                                    minlength="6"
                                    maxlength="6"
                                    autocomplete="new-password"
                                    placeholder="Enter 6 digits"
                                    required
                                >
                                <button class="btn btn-outline-secondary"
                                        type="button"
                                        data-toggle-password="pin"
                                        aria-label="Show PIN">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>

                            @error('pin')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="pin_confirmation" class="form-label fw-semibold">
                                Confirm PIN
                                <span class="text-danger">*</span>
                            </label>

                            <div class="input-group">
                                <input
                                    type="password"
                                    name="pin_confirmation"
                                    id="pin_confirmation"
                                    class="form-control"
                                    inputmode="numeric"
                                    pattern="[0-9]{6}"
                                    minlength="6"
                                    maxlength="6"
                                    autocomplete="new-password"
                                    placeholder="Re-enter 6 digits"
                                    required
                                >
                                <button class="btn btn-outline-secondary"
                                        type="button"
                                        data-toggle-password="pin_confirmation"
                                        aria-label="Show PIN confirmation">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <ul class="small text-secondary ps-3 mb-0">
                            <li>Exactly 6 numeric digits.</li>
                            <li>PIN is stored as a secure hash.</li>
                            <li>Only the Barangay Captain may reset a PIN.</li>
                            <li>Personnel cannot change their own PIN.</li>
                        </ul>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-person-check me-1"></i>
                            Register Personnel
                        </button>

                        <a href="{{ route('attendance.personnel.index') }}"
                           class="btn btn-outline-secondary w-100">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            const inputId = button.getAttribute('data-toggle-password');
            const input = document.getElementById(inputId);

            if (!input) {
                return;
            }

            const icon = button.querySelector('i');
            const showPassword = input.type === 'password';

            input.type = showPassword ? 'text' : 'password';

            if (icon) {
                icon.classList.toggle('bi-eye', !showPassword);
                icon.classList.toggle('bi-eye-slash', showPassword);
            }

            button.setAttribute(
                'aria-label',
                showPassword ? 'Hide PIN' : 'Show PIN'
            );
        });
    });

    ['pin', 'pin_confirmation'].forEach(function (id) {
        const input = document.getElementById(id);

        if (!input) {
            return;
        }

        input.addEventListener('input', function () {
            input.value = input.value.replace(/[^0-9]/g, '').slice(0, 6);
        });
    });
});
</script>
@endpush
@endsection
