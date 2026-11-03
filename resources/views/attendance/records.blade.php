
@extends('layouts.app')

@section('title', 'Attendance Records | e-Poblacion')

@section('content')
@php
    $currentUser = auth()->user();
    $isCaptain = $currentUser && $currentUser->isCaptain();

    $selectedDate = $date ?? today()->toDateString();
    $personnelOptions = $personnel ?? collect();

    $statusLabels = [
        'present' => 'Present',
        'absent' => 'Absent',
        'late' => 'Late',
        'incomplete' => 'Incomplete',
    ];
@endphp

<style>
    .attendance-records {
        color: var(--bs-body-color);
    }

    .attendance-records .ar-muted {
        color: var(--bs-secondary-color);
    }

    .attendance-records .ar-heading {
        font-weight: 800;
        letter-spacing: -.035em;
    }

    .attendance-records .ar-panel {
        background: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 16px;
        overflow: hidden;
    }

    .attendance-records .ar-panel-header {
        padding: 18px 21px;
        border-bottom: 1px solid var(--bs-border-color);
    }

    .attendance-records .ar-filter {
        padding: 20px;
    }

    .attendance-records .form-label {
        font-size: .8rem;
        font-weight: 700;
        margin-bottom: 7px;
    }

    .attendance-records .form-control,
    .attendance-records .form-select {
        min-height: 42px;
        border-radius: 9px;
        font-size: .87rem;
    }

    .attendance-records .ar-table {
        margin-bottom: 0;
    }

    .attendance-records .ar-table thead th {
        padding: 13px 16px;
        background: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        font-size: .7rem;
        font-weight: 800;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .attendance-records .ar-table tbody td {
        padding: 15px 16px;
        vertical-align: middle;
        font-size: .84rem;
    }

    .attendance-records .ar-person-name {
        font-weight: 750;
    }

    .attendance-records .ar-person-id {
        font-size: .74rem;
        color: var(--bs-secondary-color);
        margin-top: 3px;
    }

    .attendance-records .ar-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 30px;
        padding: 5px 10px;
        font-size: .72rem;
        font-weight: 750;
        white-space: nowrap;
    }

    .attendance-records .ar-status-present {
        color: #198754;
        background: rgba(25, 135, 84, .12);
    }

    .attendance-records .ar-status-late {
        color: #997000;
        background: rgba(255, 193, 7, .17);
    }

    .attendance-records .ar-status-absent {
        color: #dc3545;
        background: rgba(220, 53, 69, .1);
    }

    .attendance-records .ar-status-incomplete {
        color: #b7791f;
        background: rgba(255, 193, 7, .15);
    }

    .attendance-records .ar-status-completed {
        color: #0d6efd;
        background: rgba(13, 110, 253, .11);
    }

    .attendance-records .ar-empty {
        padding: 55px 20px;
        text-align: center;
    }

    .attendance-records .ar-empty-icon {
        display: grid;
        place-items: center;
        width: 58px;
        height: 58px;
        margin: 0 auto 14px;
        border-radius: 17px;
        background: rgba(13, 110, 253, .09);
        color: #0d6efd;
        font-size: 1.5rem;
    }

    .attendance-records .ar-pagination {
        padding: 16px 20px;
        border-top: 1px solid var(--bs-border-color);
    }

    .attendance-records .ar-pagination nav p {
        font-size: .8rem;
        color: var(--bs-secondary-color);
    }

    .attendance-records .ar-action-btn {
        font-size: .76rem;
        font-weight: 700;
    }

    .attendance-records .ar-info {
        border-radius: 11px;
        padding: 13px 15px;
        background: var(--bs-tertiary-bg);
        font-size: .8rem;
    }

    @media print {
        body {
            background: #fff !important;
        }

        .sidebar,
        .app-sidebar,
        .topbar,
        .ar-no-print,
        .attendance-records .ar-filter,
        .attendance-records .ar-actions,
        .attendance-records .ar-pagination {
            display: none !important;
        }

        .attendance-records .ar-panel {
            border: 0;
            box-shadow: none;
        }

        .attendance-records .ar-table {
            font-size: 10px;
        }

        .attendance-records .ar-table th,
        .attendance-records .ar-table td {
            padding: 7px !important;
        }
    }
</style>

<div class="attendance-records">

    {{-- Page heading --}}
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="text-primary">
                    <i class="bi bi-calendar2-check"></i>
                </span>
                <span class="small fw-bold text-uppercase text-secondary">
                    Attendance Monitoring
                </span>
            </div>

            <h2 class="ar-heading mb-1">Attendance Records</h2>

            <p class="ar-muted small mb-0">
                Review and filter personnel daily attendance transactions.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2 ar-no-print">
            <a href="{{ route('attendance.index') }}"
               class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Overview
            </a>

            <button type="button"
                    class="btn btn-outline-primary btn-sm"
                    onclick="window.print()">
                <i class="bi bi-printer me-1"></i>
                Print
            </button>
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-bold mb-1">
                <i class="bi bi-exclamation-circle me-1"></i>
                Please review the following:
            </div>
            <ul class="mb-0 ps-4 small">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Filters --}}
    <div class="ar-panel mb-4 ar-no-print">
        <div class="ar-panel-header">
            <h5 class="fw-bold mb-1">
                <i class="bi bi-funnel me-2 text-primary"></i>
                Filter Attendance
            </h5>
            <p class="ar-muted small mb-0">
                Select a date, personnel member, status, or search term.
            </p>
        </div>

        <form method="GET" action="{{ route('attendance.records') }}" class="ar-filter">
            <div class="row g-3 align-items-end">

                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="attendanceDate" class="form-label">Attendance Date</label>
                    <input type="date"
                           id="attendanceDate"
                           name="date"
                           class="form-control"
                           value="{{ $selectedDate }}">
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <label for="personnelFilter" class="form-label">Personnel</label>
                    <select id="personnelFilter" name="personnel_id" class="form-select">
                        <option value="">All Personnel</option>
                        @foreach ($personnelOptions as $person)
                            <option value="{{ $person->id }}"
                                @selected((string) request('personnel_id') === (string) $person->id)>
                                {{ $person->personnel_id }} — {{ $person->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-2">
                    <label for="attendanceStatus" class="form-label">Status</label>
                    <select id="attendanceStatus" name="status" class="form-select">
                        <option value="">All Statuses</option>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}"
                                @selected(request('status') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-sm-6 col-lg-2">
                    <label for="attendanceSearch" class="form-label">Search Personnel</label>
                    <input type="search"
                           id="attendanceSearch"
                           name="search"
                           class="form-control"
                           placeholder="ID or name..."
                           maxlength="100"
                           value="{{ request('search') }}">
                </div>

                <div class="col-12 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-search me-1"></i>
                        Filter
                    </button>

                    <a href="{{ route('attendance.records') }}"
                       class="btn btn-outline-secondary"
                       title="Clear filters"
                       aria-label="Clear filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>

            </div>
        </form>
    </div>

    {{-- Records table --}}
    <div class="ar-panel">

        <div class="ar-panel-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold mb-1">Daily Attendance Transactions</h5>
                <div class="ar-muted small">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}
                </div>
            </div>

            <div class="small ar-muted">
                <i class="bi bi-list-ul me-1"></i>
                {{ $records->total() }} record(s) found
            </div>
        </div>

        @if ($records->isEmpty())
            <div class="ar-empty">
                <div class="ar-empty-icon">
                    <i class="bi bi-calendar2-x"></i>
                </div>

                <h6 class="fw-bold mb-2">No Attendance Records Found</h6>

                <p class="ar-muted small mb-3">
                    There are no attendance records matching the selected date and filters.
                </p>

                <a href="{{ route('attendance.records') }}"
                   class="btn btn-outline-primary btn-sm ar-no-print">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                    Clear Filters
                </a>
            </div>
        @else

            <div class="table-responsive">
                <table class="table table-hover ar-table">
                    <thead>
                        <tr>
                            <th>Personnel</th>
                            <th>Position</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Method</th>
                            <th>Record Status</th>
                            @if ($isCaptain)
                                <th class="ar-actions">Action</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($records as $record)
                            @php
                                $person = $record->personnel;

                                $recordStatus = strtolower($record->status ?? 'present');

                                $statusClass = match ($recordStatus) {
                                    'present' => 'present',
                                    'late' => 'late',
                                    'absent' => 'absent',
                                    'incomplete' => 'incomplete',
                                    default => 'incomplete',
                                };

                                $statusLabel = $statusLabels[$recordStatus] ?? ucfirst($recordStatus);

                                $hasTimeIn = $record->hasTimedIn();
                                $hasTimeOut = $record->hasTimedOut();
                                $departureAuthorization = $record->departureAuthorization;
                            @endphp

                            <tr>
                                <td>
                                    <div class="ar-person-name">
                                        {{ $person?->full_name ?? 'Unknown Personnel' }}
                                    </div>
                                    <div class="ar-person-id">
                                        {{ $person?->personnel_id ?? 'Personnel record unavailable' }}
                                    </div>
                                </td>

                                <td>{{ $person?->position ?? '—' }}</td>

                                <td>
                                    @if ($hasTimeIn)
                                        <span class="fw-bold text-success">
                                            {{ $record->time_in->format('h:i A') }}
                                        </span>
                                    @else
                                        <span class="ar-muted">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($hasTimeOut)
                                        <span class="fw-bold">
                                            {{ $record->time_out->format('h:i A') }}
                                        </span>
                                    @elseif ($departureAuthorization)
                                        <span class="text-warning fw-semibold small">
                                            Authorized exception
                                        </span>
                                    @else
                                        <span class="ar-muted">Not recorded</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="small">
                                        <span class="ar-muted">In:</span>
                                        {{ strtoupper($record->time_in_method ?? '—') }}
                                    </div>
                                    <div class="small">
                                        <span class="ar-muted">Out:</span>
                                        {{ strtoupper($record->time_out_method ?? '—') }}
                                    </div>
                                </td>

                                <td>
                                    <span class="ar-status ar-status-{{ $statusClass }}">
                                        <i class="bi bi-circle-fill" style="font-size:6px"></i>
                                        {{ $statusLabel }}
                                    </span>

                                    @if ($departureAuthorization)
                                        <div class="small text-warning mt-1">
                                            <i class="bi bi-shield-check me-1"></i>
                                            Departure authorized
                                        </div>
                                    @elseif ($hasTimeIn && ! $hasTimeOut)
                                        <div class="small ar-muted mt-1">
                                            Still present / no Time Out
                                        </div>
                                    @endif
                                </td>

                                @if ($isCaptain)
                                    <td class="ar-actions">
                                        @if ($hasTimeIn && ! $hasTimeOut && ! $departureAuthorization)
                                            <button type="button"
                                                    class="btn btn-outline-warning btn-sm ar-action-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#departureModal{{ $record->id }}">
                                                <i class="bi bi-shield-check me-1"></i>
                                                Authorize Departure
                                            </button>
                                        @elseif ($departureAuthorization)
                                            <span class="small text-success fw-semibold">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Authorized
                                            </span>
                                        @else
                                            <span class="small ar-muted">No action</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="ar-pagination">
                {{ $records->links() }}
            </div>

        @endif
    </div>

    {{-- Emergency departure authorization modals: Captain only --}}
    @if ($isCaptain && $records->isNotEmpty())
        @foreach ($records as $record)
            @if ($record->hasTimedIn() && ! $record->hasTimedOut() && ! $record->departureAuthorization)
                <div class="modal fade"
                     id="departureModal{{ $record->id }}"
                     tabindex="-1"
                     aria-labelledby="departureModalLabel{{ $record->id }}"
                     aria-hidden="true">

                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">

                            <form method="POST"
                                  action="{{ route('attendance.records.authorize-departure', $record) }}">
                                @csrf

                                <div class="modal-header">
                                    <h5 class="modal-title fw-bold"
                                        id="departureModalLabel{{ $record->id }}">
                                        <i class="bi bi-shield-check text-warning me-2"></i>
                                        Emergency Departure Authorization
                                    </h5>

                                    <button type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                </div>

                                <div class="modal-body">

                                    <div class="ar-info mb-3">
                                        <div class="fw-bold">
                                            {{ $record->personnel?->full_name ?? 'Unknown Personnel' }}
                                        </div>
                                        <div class="ar-muted">
                                            Personnel ID:
                                            {{ $record->personnel?->personnel_id ?? '—' }}
                                        </div>
                                        <div class="ar-muted">
                                            Time In:
                                            {{ $record->time_in?->format('F d, Y h:i A') ?? '—' }}
                                        </div>
                                    </div>

                                    <div class="alert alert-warning small">
                                        <i class="bi bi-exclamation-triangle me-1"></i>
                                        This action does not create a Time Out. The original Time In
                                        and missing Time Out will remain unchanged.
                                    </div>

                                    <div class="mb-3">
                                        <label for="reason{{ $record->id }}" class="form-label">
                                            Reason for Emergency Departure
                                            <span class="text-danger">*</span>
                                        </label>

                                        <textarea id="reason{{ $record->id }}"
                                                  name="reason"
                                                  class="form-control"
                                                  rows="3"
                                                  maxlength="1000"
                                                  required
                                                  placeholder="Explain why the personnel had to leave without recording Time Out."></textarea>
                                    </div>

                                    <div class="mb-3">
                                        <label for="departureTime{{ $record->id }}" class="form-label">
                                            Actual Departure Time
                                        </label>

                                        <input type="datetime-local"
                                               id="departureTime{{ $record->id }}"
                                               name="actual_departure_at"
                                               class="form-control">

                                        <div class="form-text">
                                            Leave blank if the actual departure time is unknown.
                                        </div>
                                    </div>

                                    <div class="mb-0">
                                        <label for="remarks{{ $record->id }}" class="form-label">
                                            Additional Remarks
                                        </label>

                                        <textarea id="remarks{{ $record->id }}"
                                                  name="remarks"
                                                  class="form-control"
                                                  rows="2"
                                                  maxlength="2000"
                                                  placeholder="Optional additional information"></textarea>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button type="button"
                                            class="btn btn-outline-secondary"
                                            data-bs-dismiss="modal">
                                        Cancel
                                    </button>

                                    <button type="submit" class="btn btn-warning">
                                        <i class="bi bi-shield-check me-1"></i>
                                        Confirm Authorization
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    @endif

    <div class="ar-info mt-3">
        <i class="bi bi-info-circle text-primary me-1"></i>
        <strong>Record integrity:</strong>
        Emergency departure authorization preserves the original Time In.
        It does not fabricate or automatically fill a missing Time Out.
        Only the Barangay Captain can authorize this exception.
    </div>

</div>
@endsection
