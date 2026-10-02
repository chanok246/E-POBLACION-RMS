@extends('layouts.app')

@section('title', 'View User | e-Poblacion')

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 fw-bold mb-1">User Details</h1>
            <p class="text-muted mb-0">
                View system personnel account information.
            </p>
        </div>

        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Users
        </a>
    </div>


    {{-- Success Message --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close">
            </button>
        </div>
    @endif


    {{-- Validation Errors --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <div class="fw-semibold mb-1">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Please correct the following:
            </div>

            <ul class="mb-0 ps-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
                aria-label="Close">
            </button>
        </div>
    @endif


    <div class="row g-4">

        {{-- User Profile --}}
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-person-badge me-2"></i>
                        Personnel Account
                    </h5>
                </div>

                <div class="card-body p-4">

                    {{-- Name --}}
                    <div class="row mb-4">
                        <div class="col-sm-4 text-muted">
                            Full Name
                        </div>

                        <div class="col-sm-8 fw-semibold">
                            {{ $user->name }}

                            @if ($user->id === auth()->id())
                                <span class="badge text-bg-primary ms-2">
                                    You
                                </span>
                            @endif
                        </div>
                    </div>


                    {{-- Email --}}
                    <div class="row mb-4">
                        <div class="col-sm-4 text-muted">
                            Email Address
                        </div>

                        <div class="col-sm-8">
                            {{ $user->email }}
                        </div>
                    </div>


                    {{-- Role --}}
                    <div class="row mb-4">
                        <div class="col-sm-4 text-muted">
                            System Role
                        </div>

                        <div class="col-sm-8">

                            @if ($user->isCaptain())

                                <span class="badge text-bg-primary">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Captain
                                </span>

                            @elseif ($user->isSecretary())

                                <span class="badge text-bg-info">
                                    <i class="bi bi-person-badge me-1"></i>
                                    Secretary
                                </span>

                            @elseif (method_exists($user, 'isTreasurer') && $user->isTreasurer())

                                <span class="badge text-bg-warning">
                                    <i class="bi bi-cash-stack me-1"></i>
                                    Treasurer
                                </span>

                            @else

                                <span class="badge text-bg-secondary">
                                    {{ ucfirst($user->role) }}
                                </span>

                            @endif

                        </div>
                    </div>


                    {{-- Status --}}
                    <div class="row mb-4">
                        <div class="col-sm-4 text-muted">
                            Account Status
                        </div>

                        <div class="col-sm-8">

                            @if ($user->isActive())

                                <span class="badge text-bg-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Active
                                </span>

                            @else

                                <span class="badge text-bg-secondary">
                                    <i class="bi bi-x-circle me-1"></i>
                                    Inactive
                                </span>

                            @endif

                        </div>
                    </div>


                    {{-- Document Processor --}}
                    @if (isset($user->document_processor))

                        <div class="row mb-4">
                            <div class="col-sm-4 text-muted">
                                Document Processing
                            </div>

                            <div class="col-sm-8">

                                @if ($user->document_processor)

                                    <span class="badge text-bg-success">
                                        Enabled
                                    </span>

                                @else

                                    <span class="badge text-bg-secondary">
                                        Not Enabled
                                    </span>

                                @endif

                            </div>
                        </div>

                    @endif


                    {{-- Created --}}
                    <div class="row mb-4">
                        <div class="col-sm-4 text-muted">
                            Account Created
                        </div>

                        <div class="col-sm-8">
                            {{ $user->created_at?->format('F d, Y h:i A') ?? 'N/A' }}
                        </div>
                    </div>


                    {{-- Last Updated --}}
                    <div class="row">
                        <div class="col-sm-4 text-muted">
                            Last Updated
                        </div>

                        <div class="col-sm-8">
                            {{ $user->updated_at?->format('F d, Y h:i A') ?? 'N/A' }}
                        </div>
                    </div>

                </div>

            </div>

        </div>


        {{-- Actions --}}
        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-header bg-white border-0 py-3">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-gear me-2"></i>
                        Account Actions
                    </h5>
                </div>

                <div class="card-body p-4">

                    {{-- Edit --}}
                    <a
                        href="{{ route('users.edit', $user) }}"
                        class="btn btn-primary w-100 mb-2"
                    >
                        <i class="bi bi-pencil me-1"></i>
                        Edit Account
                    </a>


                    {{-- Change Password --}}
                    <button
                        type="button"
                        class="btn btn-outline-secondary w-100 mb-3"
                        data-bs-toggle="modal"
                        data-bs-target="#changePasswordModal"
                    >
                        <i class="bi bi-key me-1"></i>
                        Change Password
                    </button>


                    <hr>


                    {{-- Activate --}}
                    @if (! $user->isActive())

                        <form
                            action="{{ route('users.activate', $user) }}"
                            method="POST"
                            class="mb-2"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="btn btn-outline-success w-100"
                                onclick="return confirm('Activate this user account?')"
                            >
                                <i class="bi bi-person-check me-1"></i>
                                Activate Account
                            </button>

                        </form>

                    @endif


                    {{-- Deactivate --}}
                    @if ($user->isActive() && $user->id !== auth()->id())

                        <form
                            action="{{ route('users.deactivate', $user) }}"
                            method="POST"
                        >
                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="btn btn-outline-danger w-100"
                                onclick="return confirm('Deactivate this user account?')"
                            >
                                <i class="bi bi-person-x me-1"></i>
                                Deactivate Account
                            </button>

                        </form>

                    @elseif ($user->id === auth()->id())

                        <div class="alert alert-light border small mb-0">
                            <i class="bi bi-info-circle me-1"></i>
                            You cannot deactivate your own account.
                        </div>

                    @endif

                </div>

            </div>


            {{-- Access Information --}}
            <div class="card border-0 shadow-sm mt-4">

                <div class="card-body p-4">

                    <h6 class="fw-bold mb-3">
                        <i class="bi bi-shield-lock me-2"></i>
                        Access Information
                    </h6>

                    @if ($user->isCaptain())

                        <p class="small text-muted mb-0">
                            Captain has full access to all system features,
                            including User Management and Settings.
                        </p>

                    @elseif ($user->isSecretary())

                        <p class="small text-muted mb-0">
                            Secretary has access to operational barangay
                            management and service modules according to
                            assigned responsibilities.
                        </p>

                    @elseif (method_exists($user, 'isTreasurer') && $user->isTreasurer())

                        <p class="small text-muted mb-0">
                            Treasurer has limited access focused on viewing
                            and checking assigned information and relief
                            voucher records.
                        </p>

                    @else

                        <p class="small text-muted mb-0">
                            This account does not have a recognized system role.
                        </p>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- ================================================================
         CHANGE PASSWORD MODAL
         ================================================================ --}}
    <div
        class="modal fade"
        id="changePasswordModal"
        tabindex="-1"
        aria-labelledby="changePasswordModalLabel"
        aria-hidden="true"
    >

        <div class="modal-dialog modal-dialog-centered">

            <div class="modal-content">

                <div class="modal-header">

                    <h5
                        class="modal-title fw-bold"
                        id="changePasswordModalLabel"
                    >
                        <i class="bi bi-key me-2"></i>
                        Change Password
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>

                </div>


                {{-- IMPORTANT:
                     The route uses PATCH, so this form MUST submit PATCH.
                --}}
                <form
                    action="{{ route('users.password.update', $user) }}"
                    method="POST"
                >

                    @csrf
                    @method('PATCH')


                    <div class="modal-body">

                        <div class="alert alert-light border small">
                            <i class="bi bi-shield-lock me-1"></i>
                            The new password must contain at least 8 characters.
                        </div>


                        {{-- New Password --}}
                        <div class="mb-3">

                            <label
                                for="password"
                                class="form-label fw-semibold"
                            >
                                New Password
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >

                        </div>


                        {{-- Confirm Password --}}
                        <div class="mb-2">

                            <label
                                for="password_confirmation"
                                class="form-label fw-semibold"
                            >
                                Confirm New Password
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="form-control"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >

                        </div>

                    </div>


                    <div class="modal-footer">

                        <button
                            type="button"
                            class="btn btn-outline-secondary"
                            data-bs-dismiss="modal"
                        >
                            Cancel
                        </button>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-check-lg me-1"></i>
                            Update Password
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

@endsection
