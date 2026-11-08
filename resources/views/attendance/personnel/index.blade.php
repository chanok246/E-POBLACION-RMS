
@extends('layouts.app')

@section('title', 'Attendance Personnel | Attendance Monitoring')

@section('content')
@php
    $currentUser = auth()->user();

    $isCaptain = $currentUser && method_exists($currentUser, 'isCaptain')
        ? $currentUser->isCaptain()
        : false;
@endphp

<div class="container-fluid py-3">

    {{-- Page heading --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('attendance.index') }}"
                   class="text-decoration-none text-secondary">
                    <i class="bi bi-arrow-left"></i>
                    Attendance Monitoring
                </a>
            </div>

            <h4 class="fw-bold mb-1">Attendance Personnel</h4>

            <p class="text-secondary mb-0">
                Register barangay personnel and manage their attendance identification.
            </p>
        </div>

        <a href="{{ route('attendance.personnel.create') }}"
           class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i>
            Register Personnel
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Please check the following:</strong>

            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

            <button type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                    aria-label="Close"></button>
        </div>
    @endif

    {{-- Attendance identification policy --}}
    <div class="alert alert-info border-0 mb-4">
        <div class="d-flex gap-2">
            <i class="bi bi-info-circle fs-5"></i>

            <div>
                <strong>Attendance Identification</strong>

                <div class="small mt-1">
                    Registered personnel use their attendance identification
                    to record Time In and Time Out.
                </div>

                <div class="small mt-2">
                    <strong>Manual attendance:</strong>
                    Personnel identification and attendance PIN are used
                    for attendance verification.
                </div>

                <div class="small mt-1">
                    <strong>QR attendance:</strong>
                    Personnel may use their issued QR code as an alternative
                    identification method.
                </div>

                <div class="small mt-1">
                    <strong>PIN security:</strong>
                    Attendance PINs are stored securely as hashes.
                    Only the Barangay Captain may reset a personnel attendance PIN.
                </div>

                <div class="small mt-1">
                    Attendance credentials are separate from Field Collection
                    website login credentials.
                </div>
            </div>
        </div>
    </div>

    {{-- Registered personnel --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-transparent py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                <div>
                    <h5 class="fw-semibold mb-1">
                        Registered Attendance Personnel
                    </h5>

                    <small class="text-secondary">
                        Manage personnel records used by the Attendance Monitoring System.
                    </small>
                </div>

                <span class="badge bg-light text-dark border">
                    <i class="bi bi-people me-1"></i>
                    {{ $personnel->total() }} personnel
                </span>

            </div>
        </div>

        <div class="card-body p-0">

            @if($personnel->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Personnel ID</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Personnel Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($personnel as $person)

                                @php
                                    $fullName = trim(implode(' ', array_filter([
                                        $person->first_name,
                                        $person->middle_name,
                                        $person->last_name,
                                        $person->suffix,
                                    ])));

                                    $isActive = $person->status === 'active';
                                @endphp

                                <tr>

                                    {{-- Personnel ID --}}
                                    <td class="ps-3">
                                        <span class="fw-semibold">
                                            {{ $person->personnel_id }}
                                        </span>
                                    </td>

                                    {{-- Name --}}
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $fullName ?: 'Unnamed Personnel' }}
                                        </div>

                                        @if($person->user)
                                            <small class="text-secondary">
                                                Linked to {{ $person->user->role ?? 'RMS user' }}
                                            </small>
                                        @endif
                                    </td>

                                    {{-- Position --}}
                                    <td>
                                        {{ $person->position }}
                                    </td>

                                    {{-- Personnel status --}}
                                    <td>
                                        @if($isActive)
                                            <span class="badge rounded-pill bg-success-subtle text-success">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Active
                                            </span>
                                        @else
                                            <span class="badge rounded-pill bg-secondary-subtle text-secondary">
                                                <i class="bi bi-person-slash me-1"></i>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Attendance actions --}}
                                    <td class="text-end pe-3">

                                        <div class="d-inline-flex flex-wrap justify-content-end gap-1">

                                            {{-- View personnel --}}
                                            <a href="{{ route('attendance.personnel.show', $person) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="View personnel">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            {{-- Edit personnel --}}
                                            <a href="{{ route('attendance.personnel.edit', $person) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Edit personnel">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            {{-- Generate or replace QR --}}
                                            @if($isActive)

                                                <form method="POST"
                                                      action="{{ route('attendance.personnel.qr.issue', $person) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Generate a new QR ID for this personnel? Any previously issued QR ID will be revoked.');">

                                                    @csrf

                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-success"
                                                            title="Generate or replace QR ID">
                                                        <i class="bi bi-qr-code"></i>
                                                    </button>

                                                </form>

                                            @endif

                                            {{-- Captain-only personnel deactivation --}}
                                            @if($isCaptain && $isActive)

                                                <form method="POST"
                                                      action="{{ route('attendance.personnel.deactivate', $person) }}"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Deactivate this personnel record? Attendance history will be preserved.');">

                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Deactivate personnel record">
                                                        <i class="bi bi-person-x"></i>
                                                    </button>

                                                </form>

                                            @endif

                                        </div>

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                {{-- Pagination --}}
                @if($personnel->hasPages())

                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 border-top">

                        <small class="text-secondary">
                            Showing {{ $personnel->firstItem() }}
                            to {{ $personnel->lastItem() }}
                            of {{ $personnel->total() }} personnel
                        </small>

                        {{ $personnel->links() }}

                    </div>

                @endif

            @else

                <div class="text-center py-5 px-3">

                    <div class="mb-3">
                        <i class="bi bi-people display-4 text-secondary"></i>
                    </div>

                    <h5 class="fw-semibold">
                        No personnel registered yet
                    </h5>

                    <p class="text-secondary mb-3">
                        Register barangay personnel to start recording their attendance.
                    </p>

                    <a href="{{ route('attendance.personnel.create') }}"
                       class="btn btn-primary">

                        <i class="bi bi-person-plus me-1"></i>
                        Register First Personnel

                    </a>

                </div>

            @endif

        </div>
    </div>

</div>
@endsection
