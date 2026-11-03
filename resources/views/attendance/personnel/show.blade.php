
@extends('layouts.app')

@section('title', 'Personnel Profile | Attendance Monitoring')

@section('content')
@php
    $currentUser = auth()->user();

    $isCaptain = $currentUser && method_exists($currentUser, 'isCaptain')
        ? $currentUser->isCaptain()
        : false;

    $fullName = trim(implode(' ', array_filter([
        $personnel->first_name,
        $personnel->middle_name,
        $personnel->last_name,
        $personnel->suffix,
    ])));

    $isActive = $personnel->status === 'active';

    $attendanceRecords = $personnel->attendanceRecords()
        ->orderByDesc('attendance_date')
        ->orderByDesc('time_in')
        ->paginate(10);
@endphp

<div class="container-fluid py-3">

    {{-- Page header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="{{ route('attendance.personnel.index') }}"
               class="text-decoration-none text-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Back to Personnel Management
            </a>

            <h4 class="fw-bold mt-2 mb-1">Personnel Profile</h4>
            <p class="text-secondary mb-0">
                View personnel information and attendance history.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('attendance.personnel.edit', $personnel) }}"
               class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i>
                Edit Profile
            </a>

            @if($isActive)
                <form method="POST"
                      action="{{ route('attendance.personnel.qr.issue', $personnel) }}"
                      onsubmit="return confirm('Generate a new QR ID? Any previously issued QR ID will be revoked.');">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-qr-code me-1"></i>
                        Generate QR ID
                    </button>
                </form>
            @endif
        </div>
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
            {{ session('error') }}
            <button type="button" class="btn-close"
                    data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Personnel summary --}}
    <div class="row g-4">

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center py-4">

                    <div class="rounded-circle bg-primary-subtle text-primary
                                d-inline-flex align-items-center justify-content-center
                                mb-3"
                         style="width:80px;height:80px;">
                        <i class="bi bi-person-fill" style="font-size:2.5rem;"></i>
                    </div>

                    <h5 class="fw-bold mb-1">
                        {{ $fullName ?: 'Unnamed Personnel' }}
                    </h5>

                    <div class="text-secondary mb-3">
                        {{ $personnel->position }}
                    </div>

                    <div class="mb-3">
                        <span class="badge bg-light text-dark border px-3 py-2">
                            {{ $personnel->personnel_id }}
                        </span>
                    </div>

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

                    <hr class="my-4">

                    <div class="text-start">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">QR ID Status</span>

                            @if($personnel->qr_token_hash && !$personnel->qr_revoked_at)
                                <span class="text-success fw-semibold">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Issued
                                </span>
                            @else
                                <span class="text-secondary fw-semibold">
                                    Not issued
                                </span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">PIN Status</span>

                            @if($personnel->pin_hash)
                                <span class="text-success fw-semibold">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Set
                                </span>
                            @else
                                <span class="text-danger fw-semibold">
                                    Not set
                                </span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between">
                            <span class="text-secondary">Registered</span>
                            <span class="fw-semibold">
                                {{ $personnel->created_at?->format('M d, Y') ?? '—' }}
                            </span>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Personal information --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent py-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-person-vcard me-2"></i>
                        Personal Information
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row g-4">

                        <div class="col-md-6">
                            <small class="text-secondary d-block mb-1">First Name</small>
                            <div class="fw-semibold">
                                {{ $personnel->first_name }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary d-block mb-1">Middle Name</small>
                            <div class="fw-semibold">
                                {{ $personnel->middle_name ?: '—' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary d-block mb-1">Last Name</small>
                            <div class="fw-semibold">
                                {{ $personnel->last_name }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary d-block mb-1">Suffix</small>
                            <div class="fw-semibold">
                                {{ $personnel->suffix ?: '—' }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary d-block mb-1">Personnel ID</small>
                            <div class="fw-semibold">
                                {{ $personnel->personnel_id }}
                            </div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary d-block mb-1">Position</small>
                            <div class="fw-semibold">
                                {{ $personnel->position }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- RMS account --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent py-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-link-45deg me-2"></i>
                        RMS Account
                    </h5>
                </div>

                <div class="card-body">
                    @if($personnel->user)
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary-subtle text-primary
                                        d-flex align-items-center justify-content-center"
                                 style="width:46px;height:46px;">
                                <i class="bi bi-person-lock fs-5"></i>
                            </div>

                            <div>
                                <div class="fw-semibold">
                                    {{ $personnel->user->name }}
                                </div>
                                <small class="text-secondary">
                                    {{ ucfirst($personnel->user->role ?? 'RMS User') }}
                                </small>
                            </div>

                            <span class="badge bg-success-subtle text-success ms-auto">
                                Linked
                            </span>
                        </div>
                    @else
                        <div class="text-secondary">
                            <i class="bi bi-info-circle me-1"></i>
                            This personnel does not have a linked RMS account.
                            An RMS account is not required for attendance.
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>

    {{-- Attendance history --}}
    <div class="card border-0 shadow-sm mt-4">

        <div class="card-header bg-transparent py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-semibold mb-1">
                        <i class="bi bi-calendar-check me-2"></i>
                        Attendance History
                    </h5>
                    <small class="text-secondary">
                        Recorded Time In and Time Out transactions.
                    </small>
                </div>

                <a href="{{ route('attendance.records', ['personnel_id' => $personnel->id]) }}"
                   class="btn btn-sm btn-outline-primary">
                    View Attendance Records
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="card-body p-0">

            @if($attendanceRecords->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Date</th>
                                <th>Time In</th>
                                <th>Time Out</th>
                                <th>Time In Method</th>
                                <th>Time Out Method</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($attendanceRecords as $record)
                                <tr>
                                    <td class="ps-3 fw-semibold">
                                        {{ $record->attendance_date?->format('M d, Y') ?? '—' }}
                                    </td>

                                    <td>
                                        @if($record->time_in)
                                            {{ $record->time_in->format('h:i A') }}
                                        @else
                                            <span class="text-secondary">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($record->time_out)
                                            {{ $record->time_out->format('h:i A') }}
                                        @else
                                            <span class="text-danger small">
                                                No Time Out
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $record->time_in_method ? ucfirst($record->time_in_method) : '—' }}
                                    </td>

                                    <td>
                                        {{ $record->time_out_method ? ucfirst($record->time_out_method) : '—' }}
                                    </td>

                                    <td>
                                        @if($record->time_in && $record->time_out)
                                            <span class="badge bg-success-subtle text-success">
                                                Complete
                                            </span>
                                        @elseif($record->time_in)
                                            <span class="badge bg-warning-subtle text-warning-emphasis">
                                                Incomplete
                                            </span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                {{ ucfirst($record->status) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($attendanceRecords->hasPages())
                    <div class="p-3 border-top">
                        {{ $attendanceRecords->links() }}
                    </div>
                @endif

            @else
                <div class="text-center py-5 px-3">
                    <i class="bi bi-calendar2-x display-4 text-secondary"></i>
                    <h6 class="fw-semibold mt-3">No attendance records yet</h6>
                    <p class="text-secondary mb-0">
                        Attendance transactions will appear here after this personnel
                        uses the kiosk.
                    </p>
                </div>
            @endif

        </div>
    </div>

    {{-- Captain-only deactivation --}}
    @if($isCaptain && $isActive)
        <div class="card border border-danger-subtle shadow-sm mt-4">
            <div class="card-body d-flex flex-wrap justify-content-between
                        align-items-center gap-3">
                <div>
                    <h6 class="fw-semibold text-danger mb-1">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Deactivate Personnel
                    </h6>
                    <p class="text-secondary small mb-0">
                        Deactivation prevents future kiosk attendance transactions.
                        Existing attendance history will be preserved.
                    </p>
                </div>

                <form method="POST"
                      action="{{ route('attendance.personnel.deactivate', $personnel) }}"
                      onsubmit="return confirm('Are you sure you want to deactivate this personnel? Their attendance history will be preserved.');">
                    @csrf
                    @method('PATCH')

                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-person-x me-1"></i>
                        Deactivate Personnel
                    </button>
                </form>
            </div>
        </div>
    @endif

</div>
@endsection
