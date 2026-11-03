
@extends('layouts.app')

@section('title', 'Attendance Reports | e-Poblacion')

@section('content')
<div class="container-fluid py-3">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <h4 class="fw-bold mb-1">Attendance Reports</h4>
            <p class="text-secondary mb-0">
                Review and print personnel attendance records.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('attendance.index') }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>
                Attendance Overview
            </a>

            <button type="button"
                    class="btn btn-primary"
                    onclick="window.print()">
                <i class="bi bi-printer me-1"></i>
                Print Report
            </button>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-secondary small mb-2">
                        Total Records
                    </div>
                    <div class="fs-3 fw-bold">
                        {{ number_format($summary['total_records'] ?? 0) }}
                    </div>
                    <div class="small text-secondary mt-1">
                        Attendance entries
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-secondary small mb-2">
                        Timed In
                    </div>
                    <div class="fs-3 fw-bold text-success">
                        {{ number_format($summary['timed_in'] ?? 0) }}
                    </div>
                    <div class="small text-secondary mt-1">
                        With recorded time in
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-secondary small mb-2">
                        Timed Out
                    </div>
                    <div class="fs-3 fw-bold text-primary">
                        {{ number_format($summary['timed_out'] ?? 0) }}
                    </div>
                    <div class="small text-secondary mt-1">
                        With recorded time out
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-secondary small mb-2">
                        Incomplete
                    </div>
                    <div class="fs-3 fw-bold text-warning">
                        {{ number_format($summary['incomplete'] ?? 0) }}
                    </div>
                    <div class="small text-secondary mt-1">
                        Missing time out
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm mb-4" id="report-filters">

        <div class="card-header bg-transparent py-3">
            <h5 class="fw-semibold mb-0">
                <i class="bi bi-funnel me-2"></i>
                Report Filters
            </h5>
        </div>

        <div class="card-body">
            <form method="GET"
                  action="{{ route('attendance.reports') }}">

                <div class="row g-3 align-items-end">

                    <div class="col-12 col-md-4">
                        <label for="from" class="form-label fw-semibold">
                            From Date
                        </label>

                        <input type="date"
                               name="from"
                               id="from"
                               class="form-control @error('from') is-invalid @enderror"
                               value="{{ old('from', $from) }}"
                               required>

                        @error('from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="to" class="form-label fw-semibold">
                            To Date
                        </label>

                        <input type="date"
                               name="to"
                               id="to"
                               class="form-control @error('to') is-invalid @enderror"
                               value="{{ old('to', $to) }}"
                               required>

                        @error('to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="personnel_id" class="form-label fw-semibold">
                            Personnel
                        </label>

                        <select name="personnel_id"
                                id="personnel_id"
                                class="form-select @error('personnel_id') is-invalid @enderror">

                            <option value="">All Personnel</option>

                            @foreach($personnel as $person)
                                <option value="{{ $person->id }}"
                                    @selected(
                                        (string) request('personnel_id')
                                        === (string) $person->id
                                    )>
                                    {{ $person->last_name }},
                                    {{ $person->first_name }}
                                    — {{ $person->personnel_id }}
                                </option>
                            @endforeach

                        </select>

                        @error('personnel_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12 d-flex flex-wrap gap-2 justify-content-end">
                        <a href="{{ route('attendance.reports') }}"
                           class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Reset
                        </a>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search me-1"></i>
                            Generate Report
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="card border-0 shadow-sm" id="attendance-report">

        <div class="card-header bg-transparent py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-semibold mb-1">
                        Attendance Records
                    </h5>
                    <div class="small text-secondary">
                        Period:
                        {{ \Carbon\Carbon::parse($from)->format('M d, Y') }}
                        –
                        {{ \Carbon\Carbon::parse($to)->format('M d, Y') }}
                    </div>
                </div>

                <span class="badge bg-secondary-subtle text-secondary">
                    {{ number_format($records->total()) }} record(s)
                </span>
            </div>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Date</th>
                            <th>Personnel</th>
                            <th>Personnel ID</th>
                            <th>Position</th>
                            <th>Time In</th>
                            <th>Method</th>
                            <th>Time Out</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($records as $record)
                            @php
                                $recordPersonnel = $record->personnel;

                                $timeInMethod = $record->time_in_method;
                                $timeOutMethod = $record->time_out_method;

                                $recordStatus = strtolower(
                                    (string) $record->status
                                );
                            @endphp

                            <tr>
                                <td class="ps-3 text-nowrap">
                                    {{ \Carbon\Carbon::parse($record->attendance_date)->format('M d, Y') }}
                                </td>

                                <td>
                                    <div class="fw-semibold">
                                        {{ $recordPersonnel
                                            ? trim($recordPersonnel->first_name . ' ' . $recordPersonnel->last_name)
                                            : 'Personnel unavailable' }}
                                    </div>
                                </td>

                                <td class="text-nowrap">
                                    {{ $recordPersonnel?->personnel_id ?? '—' }}
                                </td>

                                <td>
                                    {{ $recordPersonnel?->position ?? '—' }}
                                </td>

                                <td class="text-nowrap">
                                    @if($record->time_in)
                                        {{ \Carbon\Carbon::parse($record->time_in)->format('h:i A') }}
                                    @else
                                        <span class="text-secondary">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if($timeInMethod)
                                        <span class="badge bg-light text-dark border">
                                            {{ strtoupper($timeInMethod) }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td class="text-nowrap">
                                    @if($record->time_out)
                                        {{ \Carbon\Carbon::parse($record->time_out)->format('h:i A') }}
                                    @else
                                        <span class="text-warning fw-semibold">
                                            No Time Out
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($timeOutMethod)
                                        <span class="badge bg-light text-dark border">
                                            {{ strtoupper($timeOutMethod) }}
                                        </span>
                                    @else
                                        —
                                    @endif
                                </td>

                                <td>
                                    @if($recordStatus === 'present')
                                        <span class="badge bg-success-subtle text-success">
                                            Present
                                        </span>
                                    @elseif($recordStatus === 'incomplete')
                                        <span class="badge bg-warning-subtle text-warning">
                                            Incomplete
                                        </span>
                                    @elseif($recordStatus === 'absent')
                                        <span class="badge bg-danger-subtle text-danger">
                                            Absent
                                        </span>
                                    @elseif($recordStatus === 'late')
                                        <span class="badge bg-info-subtle text-info">
                                            Late
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">
                                            {{ ucfirst($recordStatus ?: 'Unknown') }}
                                        </span>
                                    @endif
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="bi bi-calendar-x fs-2 text-secondary"></i>

                                    <div class="fw-semibold mt-2">
                                        No attendance records found
                                    </div>

                                    <div class="small text-secondary">
                                        Try adjusting the selected date range
                                        or personnel filter.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>

        {{-- Pagination --}}
        @if($records->hasPages())
            <div class="card-footer bg-transparent">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                    <small class="text-secondary">
                        Showing {{ $records->firstItem() ?? 0 }}
                        to {{ $records->lastItem() ?? 0 }}
                        of {{ $records->total() }} records
                    </small>

                    {{ $records->withQueryString()->links() }}

                </div>
            </div>
        @endif

    </div>

    <div class="text-secondary small mt-3">
        <i class="bi bi-info-circle me-1"></i>
        Reports display recorded attendance transactions. Missing Time Out
        entries remain incomplete and are not automatically assigned a
        departure time.
    </div>

</div>

<style>
    @media print {
        @page {
            size: landscape;
            margin: 10mm;
        }

        body {
            background: #fff !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        #report-filters,
        .toolbar,
        .btn,
        .pagination,
        nav[aria-label="Pagination"] {
            display: none !important;
        }

        .card {
            border: 0 !important;
            box-shadow: none !important;
        }

        .card-header,
        .card-footer {
            background: #fff !important;
        }

        .table-responsive {
            overflow: visible !important;
        }

        table {
            width: 100% !important;
            font-size: 9px !important;
        }

        th, td {
            padding: 5px !important;
        }

        tr {
            break-inside: avoid;
        }
    }
</style>
@endsection
