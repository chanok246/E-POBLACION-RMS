
@extends('layouts.app')

@section('title', 'Attendance Monitoring | e-Poblacion')

@section('content')
@php
    $cards = [
        [
            'label' => 'Active Personnel',
            'value' => $summary['personnel'] ?? 0,
            'icon' => 'bi-people',
            'description' => 'Registered active personnel',
            'color' => 'primary',
        ],
        [
            'label' => 'Present Today',
            'value' => $summary['present'] ?? 0,
            'icon' => 'bi-person-check',
            'description' => 'Personnel with recorded Time In',
            'color' => 'success',
        ],
        [
            'label' => 'Timed Out',
            'value' => $summary['timed_out'] ?? 0,
            'icon' => 'bi-box-arrow-right',
            'description' => 'Completed attendance for today',
            'color' => 'info',
        ],
        [
            'label' => 'Still Present',
            'value' => $summary['still_present'] ?? 0,
            'icon' => 'bi-clock-history',
            'description' => 'No recorded Time Out yet',
            'color' => 'warning',
        ],
        [
            'label' => 'Not Recorded',
            'value' => $summary['not_recorded'] ?? 0,
            'icon' => 'bi-person-dash',
            'description' => 'Active personnel without attendance',
            'color' => 'secondary',
        ],
    ];
@endphp

<style>
    .attendance-page {
        --att-border: var(--bs-border-color, #e5e7eb);
        --att-muted: var(--bs-secondary-color, #6b7280);
        --att-surface: var(--bs-body-bg, #fff);
        color: var(--bs-body-color, #212529);
    }

    .attendance-page .att-heading {
        font-weight: 750;
        letter-spacing: -.035em;
    }

    .attendance-page .att-subtitle {
        color: var(--att-muted);
        font-size: .9rem;
    }

    .attendance-page .att-card {
        height: 100%;
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 16px;
        padding: 20px;
        box-shadow: 0 3px 12px rgba(15, 23, 42, .035);
    }

    .attendance-page .att-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .attendance-page .att-stat-icon {
        display: grid;
        place-items: center;
        width: 43px;
        height: 43px;
        border-radius: 13px;
        font-size: 1.2rem;
    }

    .attendance-page .att-stat-value {
        font-size: clamp(1.8rem, 3vw, 2.25rem);
        font-weight: 800;
        line-height: 1.15;
        margin-top: 18px;
        font-variant-numeric: tabular-nums;
    }

    .attendance-page .att-stat-label {
        font-size: .91rem;
        font-weight: 700;
        margin-top: 5px;
    }

    .attendance-page .att-stat-description {
        color: var(--att-muted);
        font-size: .78rem;
        margin-top: 4px;
    }

    .attendance-page .att-section {
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 16px;
        overflow: hidden;
    }

    .attendance-page .att-section-header {
        padding: 19px 21px;
        border-bottom: 1px solid var(--att-border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .attendance-page .att-section-title {
        font-size: 1rem;
        font-weight: 750;
        margin: 0;
    }

    .attendance-page .att-table th {
        color: var(--att-muted);
        font-size: .72rem;
        font-weight: 750;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
        padding: 13px 18px;
        background: color-mix(in srgb, var(--att-surface) 94%, var(--bs-secondary-color) 6%);
    }

    .attendance-page .att-table td {
        padding: 14px 18px;
        vertical-align: middle;
        font-size: .86rem;
    }

    .attendance-page .att-person-name {
        font-weight: 700;
    }

    .attendance-page .att-person-id {
        color: var(--att-muted);
        font-size: .75rem;
        margin-top: 3px;
    }

    .attendance-page .att-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 10px;
        border-radius: 999px;
        font-size: .73rem;
        font-weight: 750;
        white-space: nowrap;
    }

    .attendance-page .att-status.present {
        background: rgba(25, 135, 84, .11);
        color: #198754;
    }

    .attendance-page .att-status.completed {
        background: rgba(13, 110, 253, .11);
        color: #0d6efd;
    }

    .attendance-page .att-status.incomplete {
        background: rgba(255, 193, 7, .16);
        color: #9a7000;
    }

    .attendance-page .att-empty {
        padding: 48px 20px;
        text-align: center;
    }

    .attendance-page .att-empty-icon {
        width: 58px;
        height: 58px;
        margin: 0 auto 14px;
        border-radius: 18px;
        display: grid;
        place-items: center;
        background: rgba(13, 110, 253, .08);
        color: #0d6efd;
        font-size: 1.55rem;
    }

    .attendance-page .att-action {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 15px;
        height: 100%;
        border: 1px solid var(--att-border);
        border-radius: 13px;
        color: inherit;
        text-decoration: none;
        transition: border-color .18s ease, transform .18s ease;
    }

    .attendance-page .att-action:hover {
        border-color: var(--bs-primary, #0d6efd);
        transform: translateY(-2px);
        color: inherit;
    }

    .attendance-page .att-action-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: rgba(13, 110, 253, .09);
        color: var(--bs-primary, #0d6efd);
        font-size: 1.15rem;
    }

    .attendance-page .att-action-title {
        font-weight: 750;
        font-size: .86rem;
    }

    .attendance-page .att-action-description {
        color: var(--att-muted);
        font-size: .74rem;
        margin-top: 3px;
    }

    @media (max-width: 575.98px) {
        .attendance-page .att-card {
            padding: 16px;
        }

        .attendance-page .att-section-header {
            padding: 16px;
        }
    }
</style>

<div class="attendance-page">

    {{-- Page heading --}}
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="text-primary">
                    <i class="bi bi-calendar2-check"></i>
                </span>
                <span class="small fw-bold text-uppercase text-secondary">
                    Personnel Administration
                </span>
            </div>

            <h2 class="att-heading mb-1">Attendance Monitoring</h2>

            <p class="att-subtitle mb-0">
                Monitor personnel attendance, daily time records, and attendance activity.
            </p>
        </div>

        <div class="text-sm-end">
            <div class="small text-secondary">Today's Date</div>
            <div class="fw-bold">
                {{ now()->format('l, F d, Y') }}
            </div>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        @foreach ($cards as $card)
            <div class="col-12 col-sm-6 col-xl">
                <div class="att-card">
                    <div class="att-stat-top">
                        <span class="small fw-semibold text-secondary">
                            {{ $card['label'] }}
                        </span>

                        <span class="att-stat-icon bg-{{ $card['color'] }} bg-opacity-10 text-{{ $card['color'] }}">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </span>
                    </div>

                    <div class="att-stat-value">
                        {{ number_format((int) $card['value']) }}
                    </div>

                    <div class="att-stat-label">
                        {{ $card['label'] }}
                    </div>

                    <div class="att-stat-description">
                        {{ $card['description'] }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Quick navigation --}}
    <div class="mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h5 class="fw-bold mb-1">Attendance Management</h5>
                <p class="att-subtitle mb-0">Manage personnel and review attendance information.</p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-4">
                <a href="{{ route('attendance.personnel.index') }}" class="att-action">
                    <span class="att-action-icon">
                        <i class="bi bi-people"></i>
                    </span>
                    <span class="flex-grow-1">
                        <span class="att-action-title d-block">Personnel Management</span>
                        <span class="att-action-description d-block">Register and manage personnel profiles</span>
                    </span>
                    <i class="bi bi-chevron-right text-secondary"></i>
                </a>
            </div>

            <div class="col-12 col-md-4">
                <a href="{{ route('attendance.records') }}" class="att-action">
                    <span class="att-action-icon">
                        <i class="bi bi-clock-history"></i>
                    </span>
                    <span class="att-action-title-wrap flex-grow-1">
                        <span class="att-action-title d-block">Attendance Records</span>
                        <span class="att-action-description d-block">Review daily Time In and Time Out</span>
                    </span>
                    <i class="bi bi-chevron-right text-secondary"></i>
                </a>
            </div>

            <div class="col-12 col-md-4">
                <a href="{{ route('attendance.reports') }}" class="att-action">
                    <span class="att-action-icon">
                        <i class="bi bi-file-earmark-bar-graph"></i>
                    </span>
                    <span class="flex-grow-1">
                        <span class="att-action-title d-block">Attendance Reports</span>
                        <span class="att-action-description d-block">View attendance summaries by date range</span>
                    </span>
                    <i class="bi bi-chevron-right text-secondary"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Today's attendance --}}
    <div class="att-section">
        <div class="att-section-header">
            <div>
                <h5 class="att-section-title">Today's Attendance</h5>
                <div class="att-subtitle mt-1">
                    Attendance transactions recorded for {{ now()->format('F d, Y') }}
                </div>
            </div>

            <a href="{{ route('attendance.records', ['date' => today()->toDateString()]) }}"
               class="btn btn-outline-primary btn-sm">
                View All Records
                <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>

        @if ($todayRecords->isEmpty())
            <div class="att-empty">
                <div class="att-empty-icon">
                    <i class="bi bi-calendar2-x"></i>
                </div>

                <h6 class="fw-bold mb-2">No Attendance Records Yet</h6>

                <p class="text-secondary small mb-0">
                    No personnel attendance transactions have been recorded today.
                    Once personnel use the Attendance Kiosk, their Time In and Time Out
                    records will appear here.
                </p>

                @if (($summary['personnel'] ?? 0) === 0)
                    <div class="mt-3">
                        <a href="{{ route('attendance.personnel.create') }}"
                           class="btn btn-primary btn-sm">
                            <i class="bi bi-person-plus me-1"></i>
                            Register First Personnel
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover att-table mb-0">
                    <thead>
                        <tr>
                            <th>Personnel</th>
                            <th>Position</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Method</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($todayRecords as $record)
                            @php
                                $person = $record->personnel;
                                $statusClass = $record->hasTimedOut()
                                    ? 'completed'
                                    : ($record->hasTimedIn() ? 'present' : 'incomplete');

                                $statusLabel = $record->hasTimedOut()
                                    ? 'Timed Out'
                                    : ($record->hasTimedIn() ? 'Still Present' : 'Incomplete');
                            @endphp

                            <tr>
                                <td>
                                    <div class="att-person-name">
                                        {{ $person?->full_name ?? 'Unknown Personnel' }}
                                    </div>
                                    <div class="att-person-id">
                                        {{ $person?->personnel_id ?? '—' }}
                                    </div>
                                </td>

                                <td>{{ $person?->position ?? '—' }}</td>

                                <td class="fw-semibold">
                                    {{ $record->time_in?->format('h:i A') ?? '—' }}
                                </td>

                                <td class="fw-semibold">
                                    {{ $record->time_out?->format('h:i A') ?? '—' }}
                                </td>

                                <td>
                                    <span class="text-secondary small">
                                        {{ strtoupper($record->time_in_method ?? '—') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="att-status {{ $statusClass }}">
                                        <i class="bi bi-circle-fill" style="font-size:6px"></i>
                                        {{ $statusLabel }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="small text-secondary mt-3">
        <i class="bi bi-shield-check me-1"></i>
        Attendance records are maintained separately from the system audit log.
    </div>

</div>
@endsection
