
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
            'tone' => 'primary',
        ],
        [
            'label' => 'Present Today',
            'value' => $summary['present'] ?? 0,
            'icon' => 'bi-person-check',
            'description' => 'Personnel with recorded Time In',
            'tone' => 'success',
        ],
        [
            'label' => 'Timed Out',
            'value' => $summary['timed_out'] ?? 0,
            'icon' => 'bi-box-arrow-right',
            'description' => 'Completed attendance for today',
            'tone' => 'info',
        ],
        [
            'label' => 'Still Present',
            'value' => $summary['still_present'] ?? 0,
            'icon' => 'bi-clock-history',
            'description' => 'No recorded Time Out yet',
            'tone' => 'warning',
        ],
        [
            'label' => 'Not Recorded',
            'value' => $summary['not_recorded'] ?? 0,
            'icon' => 'bi-person-dash',
            'description' => 'Active personnel without attendance',
            'tone' => 'neutral',
        ],
    ];
@endphp

<style>
    .attendance-page {
        --att-text: #0f172a;
        --att-text-soft: #475569;
        --att-muted: #64748b;
        --att-surface: #ffffff;
        --att-surface-2: #f8fafc;
        --att-border: #e2e8f0;
        --att-hover: rgba(15, 23, 42, .04);
        --att-shadow: 0 4px 16px rgba(15, 23, 42, .06);
        --att-shadow-hover: 0 10px 26px rgba(15, 23, 42, .10);
        --att-accent: #2563eb;

        --att-primary-fg: #1d4ed8;
        --att-primary-bg: rgba(37, 99, 235, .10);
        --att-success-fg: #15803d;
        --att-success-bg: rgba(22, 163, 74, .12);
        --att-info-fg: #0e7490;
        --att-info-bg: rgba(6, 182, 212, .13);
        --att-warning-fg: #a16207;
        --att-warning-bg: rgba(234, 179, 8, .18);
        --att-neutral-fg: #475569;
        --att-neutral-bg: rgba(100, 116, 139, .14);

        color: var(--att-text);
    }

    html[data-theme="dark"] .attendance-page,
    html[data-bs-theme="dark"] .attendance-page,
    body[data-theme="dark"] .attendance-page,
    body.dark .attendance-page,
    body.dark-mode .attendance-page {
        --att-text: #f1f5f9;
        --att-text-soft: #cbd5e1;
        --att-muted: #94a3b8;
        --att-surface: #1e293b;
        --att-surface-2: #172033;
        --att-border: #334155;
        --att-hover: rgba(255, 255, 255, .05);
        --att-shadow: 0 4px 18px rgba(0, 0, 0, .35);
        --att-shadow-hover: 0 12px 30px rgba(0, 0, 0, .50);
        --att-accent: #60a5fa;

        --att-primary-fg: #93c5fd;
        --att-primary-bg: rgba(96, 165, 250, .16);
        --att-success-fg: #4ade80;
        --att-success-bg: rgba(74, 222, 128, .15);
        --att-info-fg: #22d3ee;
        --att-info-bg: rgba(34, 211, 238, .15);
        --att-warning-fg: #fbbf24;
        --att-warning-bg: rgba(251, 191, 36, .16);
        --att-neutral-fg: #cbd5e1;
        --att-neutral-bg: rgba(148, 163, 184, .18);
    }

    .attendance-page .att-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--att-accent);
        margin-bottom: 8px;
    }

    .attendance-page .att-heading {
        font-weight: 800;
        letter-spacing: -.035em;
        color: var(--att-text);
        margin-bottom: 4px;
    }

    .attendance-page .att-subtitle {
        color: var(--att-muted);
        font-size: .9rem;
        margin: 0;
    }

    .attendance-page .att-date-chip {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 14px;
        box-shadow: var(--att-shadow);
    }

    .attendance-page .att-date-chip > i {
        font-size: 1.3rem;
        color: var(--att-accent);
    }

    .attendance-page .att-date-label {
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: var(--att-muted);
    }

    .attendance-page .att-date-value {
        font-size: .9rem;
        font-weight: 700;
        color: var(--att-text);
    }

    /* Upper navigation */
    .attendance-page .att-navigation {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
        padding: 10px;
        margin-bottom: 22px;
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 15px;
        box-shadow: var(--att-shadow);
    }

    .attendance-page .att-nav-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 15px;
        border-radius: 10px;
        color: var(--att-text-soft);
        font-size: .83rem;
        font-weight: 700;
        text-decoration: none;
        transition: .18s ease;
    }

    .attendance-page .att-nav-link:hover {
        background: var(--att-hover);
        color: var(--att-accent);
    }

    .attendance-page .att-nav-link.active {
        background: var(--att-primary-bg);
        color: var(--att-primary-fg);
    }

    .attendance-page .att-nav-kiosk {
        margin-left: auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 17px;
        border-radius: 10px;
        background: #15803d;
        color: #fff;
        font-size: .83rem;
        font-weight: 800;
        text-decoration: none;
        transition: .18s ease;
    }

    .attendance-page .att-nav-kiosk:hover {
        background: #166534;
        color: #fff;
        transform: translateY(-1px);
    }

    /* Summary cards */
    .attendance-page .att-card {
        position: relative;
        height: 100%;
        padding: 20px;
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 18px;
        box-shadow: var(--att-shadow);
        overflow: hidden;
        transition: .2s ease;
    }

    .attendance-page .att-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 3px;
        background: var(--tone-fg);
    }

    .attendance-page .att-card:hover {
        transform: translateY(-3px);
        box-shadow: var(--att-shadow-hover);
    }

    .attendance-page .tone-primary {
        --tone-fg: var(--att-primary-fg);
        --tone-bg: var(--att-primary-bg);
    }

    .attendance-page .tone-success {
        --tone-fg: var(--att-success-fg);
        --tone-bg: var(--att-success-bg);
    }

    .attendance-page .tone-info {
        --tone-fg: var(--att-info-fg);
        --tone-bg: var(--att-info-bg);
    }

    .attendance-page .tone-warning {
        --tone-fg: var(--att-warning-fg);
        --tone-bg: var(--att-warning-bg);
    }

    .attendance-page .tone-neutral {
        --tone-fg: var(--att-neutral-fg);
        --tone-bg: var(--att-neutral-bg);
    }

    .attendance-page .att-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .attendance-page .att-stat-eyebrow {
        font-size: .74rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--att-muted);
    }

    .attendance-page .att-stat-icon {
        display: grid;
        place-items: center;
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        border-radius: 14px;
        background: var(--tone-bg);
        color: var(--tone-fg);
        font-size: 1.2rem;
    }

    .attendance-page .att-stat-value {
        margin-top: 16px;
        color: var(--att-text);
        font-size: clamp(1.9rem, 3vw, 2.4rem);
        font-weight: 800;
        line-height: 1.1;
        font-variant-numeric: tabular-nums;
    }

    .attendance-page .att-stat-description {
        margin-top: 6px;
        color: var(--att-muted);
        font-size: .8rem;
    }

    /* Attendance section */
    .attendance-page .att-section {
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 18px;
        box-shadow: var(--att-shadow);
        overflow: hidden;
    }

    .attendance-page .att-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 19px 22px;
        border-bottom: 1px solid var(--att-border);
    }

    .attendance-page .att-section-title {
        margin: 0;
        color: var(--att-text);
        font-size: 1.05rem;
        font-weight: 800;
    }

    .attendance-page .att-btn-outline {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 14px;
        border: 1px solid var(--att-accent);
        border-radius: 10px;
        color: var(--att-accent);
        font-size: .8rem;
        font-weight: 700;
        text-decoration: none;
    }

    .attendance-page .att-btn-outline:hover {
        background: var(--att-accent);
        color: #fff;
    }

    .attendance-page .att-table {
        --bs-table-bg: transparent;
        --bs-table-color: var(--att-text);
        --bs-table-border-color: var(--att-border);
        --bs-table-hover-bg: var(--att-hover);
        --bs-table-hover-color: var(--att-text);
        color: var(--att-text);
        margin: 0;
    }

    .attendance-page .att-table th {
        padding: 13px 20px;
        background: var(--att-surface-2);
        color: var(--att-muted);
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
        border-bottom: 1px solid var(--att-border);
    }

    .attendance-page .att-table td {
        padding: 14px 20px;
        color: var(--att-text);
        font-size: .87rem;
        vertical-align: middle;
        border-color: var(--att-border);
        background: transparent;
    }

    .attendance-page .att-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .attendance-page .att-avatar {
        display: grid;
        place-items: center;
        width: 38px;
        height: 38px;
        flex: 0 0 38px;
        border-radius: 12px;
        background: var(--att-primary-bg);
        color: var(--att-primary-fg);
        font-size: .8rem;
        font-weight: 800;
    }

    .attendance-page .att-person-name {
        color: var(--att-text);
        font-weight: 700;
        line-height: 1.3;
    }

    .attendance-page .att-person-id {
        margin-top: 2px;
        color: var(--att-muted);
        font-size: .75rem;
    }

    .attendance-page .att-time {
        color: var(--att-text);
        font-weight: 700;
        font-variant-numeric: tabular-nums;
    }

    .attendance-page .att-dash {
        color: var(--att-muted);
    }

    .attendance-page .att-method {
        display: inline-block;
        padding: 3px 9px;
        border: 1px solid var(--att-border);
        border-radius: 8px;
        background: var(--att-surface-2);
        color: var(--att-text-soft);
        font-size: .7rem;
        font-weight: 800;
    }

    .attendance-page .att-status {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        border-radius: 999px;
        background: var(--tone-bg);
        color: var(--tone-fg);
        font-size: .73rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .attendance-page .att-status .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .attendance-page .att-empty {
        padding: 52px 20px;
        text-align: center;
    }

    .attendance-page .att-empty-icon {
        display: grid;
        place-items: center;
        width: 62px;
        height: 62px;
        margin: 0 auto 14px;
        border-radius: 20px;
        background: var(--att-primary-bg);
        color: var(--att-primary-fg);
        font-size: 1.6rem;
    }

    .attendance-page .att-empty h6 {
        margin-bottom: 8px;
        color: var(--att-text);
        font-weight: 800;
    }

    .attendance-page .att-empty p {
        max-width: 460px;
        margin: 0 auto;
        color: var(--att-muted);
        font-size: .85rem;
    }

    .attendance-page .att-btn-solid {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border: 1px solid #2563eb;
        border-radius: 10px;
        background: #2563eb;
        color: #fff;
        font-size: .82rem;
        font-weight: 700;
        text-decoration: none;
    }

    .attendance-page .att-btn-solid:hover {
        background: #1d4ed8;
        color: #fff;
    }

    .attendance-page .att-footnote {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 16px;
        color: var(--att-muted);
        font-size: .78rem;
    }

    @media (max-width: 767.98px) {
        .attendance-page .att-navigation {
            align-items: stretch;
        }

        .attendance-page .att-nav-link {
            flex: 1 1 calc(50% - 8px);
        }

        .attendance-page .att-nav-kiosk {
            flex: 1 1 100%;
            margin-left: 0;
        }
    }

    @media (max-width: 575.98px) {
        .attendance-page .att-card {
            padding: 16px;
        }

        .attendance-page .att-section-header {
            padding: 16px;
        }

        .attendance-page .att-table th,
        .attendance-page .att-table td {
            padding: 12px 14px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .attendance-page * {
            transition: none !important;
        }
    }
</style>

<div class="attendance-page">

    {{-- Dashboard heading and date --}}
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <div class="att-eyebrow">
                <i class="bi bi-calendar2-check"></i>
                Personnel Administration
            </div>

            <h2 class="att-heading">Attendance Monitoring</h2>

            <p class="att-subtitle">
                Monitor daily attendance and manage barangay personnel.
            </p>
        </div>

        <div class="att-date-chip">
            <i class="bi bi-calendar3"></i>
            <div>
                <div class="att-date-label">Today's Date</div>
                <div class="att-date-value">
                    {{ now()->format('l, F d, Y') }}
                </div>
            </div>
        </div>
    </div>

    {{-- Main navigation --}}
    <nav class="att-navigation" aria-label="Attendance Monitoring Navigation">

        <a href="{{ route('attendance.index') }}"
           class="att-nav-link active"
           aria-current="page">
            <i class="bi bi-grid-1x2"></i>
            Attendance Dashboard
        </a>

        <a href="{{ route('attendance.personnel.index') }}"
           class="att-nav-link">
            <i class="bi bi-people"></i>
            Attendance Personnel
        </a>

        @if (strtolower((string) auth()->user()?->role) === 'secretary')
            <a href="{{ route('attendance.personnel-accounts.index') }}"
               class="att-nav-link">
                <i class="bi bi-person-lock"></i>
                Personnel Account Management
            </a>
        @endif

        <a href="{{ route('attendance.records') }}"
           class="att-nav-link">
            <i class="bi bi-clock-history"></i>
            Attendance Records
        </a>

        <a href="{{ route('attendance.reports') }}"
           class="att-nav-link">
            <i class="bi bi-file-earmark-bar-graph"></i>
            Attendance Reports
        </a>

        <a href="{{ url('/attendance-kiosk') }}"
           class="att-nav-kiosk"
           target="_blank"
           rel="noopener">
            <i class="bi bi-fingerprint"></i>
            Open Attendance Kiosk
            <i class="bi bi-box-arrow-up-right"></i>
        </a>

    </nav>

    {{-- Summary cards --}}
    <div class="row g-3 mb-4">
        @foreach ($cards as $card)
            <div class="col-12 col-sm-6 col-xl">
                <div class="att-card tone-{{ $card['tone'] }}">
                    <div class="att-stat-top">
                        <span class="att-stat-eyebrow">
                            {{ $card['label'] }}
                        </span>

                        <span class="att-stat-icon">
                            <i class="bi {{ $card['icon'] }}"></i>
                        </span>
                    </div>

                    <div class="att-stat-value">
                        {{ number_format((int) $card['value']) }}
                    </div>

                    <div class="att-stat-description">
                        {{ $card['description'] }}
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Today's attendance --}}
    <div class="att-section">

        <div class="att-section-header">
            <div>
                <h5 class="att-section-title">Today's Attendance</h5>

                <div class="att-subtitle mt-1">
                    Attendance transactions recorded for
                    {{ now()->format('F d, Y') }}
                </div>
            </div>

            <a href="{{ route('attendance.records', ['date' => today()->toDateString()]) }}"
               class="att-btn-outline">
                View All Records
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>

        @if ($todayRecords->isEmpty())

            <div class="att-empty">
                <div class="att-empty-icon">
                    <i class="bi bi-calendar2-x"></i>
                </div>

                <h6>No Attendance Records Yet</h6>

                <p>
                    No personnel attendance transactions have been recorded today.
                    Personnel can use the Attendance Kiosk to record their
                    Time In and Time Out.
                </p>

                @if ((int) ($summary['personnel'] ?? 0) === 0)
                    <div class="mt-3">
                        <a href="{{ route('attendance.personnel.create') }}"
                           class="att-btn-solid">
                            <i class="bi bi-person-plus"></i>
                            Register First Personnel
                        </a>
                    </div>
                @endif
            </div>

        @else

            <div class="table-responsive">
                <table class="table table-hover att-table">

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

                                $statusTone = $record->hasTimedOut()
                                    ? 'info'
                                    : ($record->hasTimedIn() ? 'success' : 'warning');

                                $statusLabel = $record->hasTimedOut()
                                    ? 'Timed Out'
                                    : ($record->hasTimedIn() ? 'Still Present' : 'Incomplete');

                                $fullName = $person?->full_name ?? 'Unknown Personnel';

                                $initials = collect(
                                    preg_split('/\s+/', trim($fullName))
                                )
                                    ->filter()
                                    ->take(2)
                                    ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
                                    ->implode('');
                            @endphp

                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="att-avatar">
                                            {{ $initials ?: '?' }}
                                        </span>

                                        <div>
                                            <div class="att-person-name">
                                                {{ $fullName }}
                                            </div>

                                            <div class="att-person-id">
                                                {{ $person?->personnel_id ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td>
                                    {{ $person?->position ?? '—' }}
                                </td>

                                <td>
                                    @if ($record->time_in)
                                        <span class="att-time">
                                            {{ $record->time_in->format('h:i A') }}
                                        </span>
                                    @else
                                        <span class="att-dash">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($record->time_out)
                                        <span class="att-time">
                                            {{ $record->time_out->format('h:i A') }}
                                        </span>
                                    @else
                                        <span class="att-dash">—</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="att-method">
                                        {{ strtoupper($record->time_in_method ?? '—') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="att-status tone-{{ $statusTone }}">
                                        <span class="dot"></span>
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

    <div class="att-footnote">
        <i class="bi bi-shield-check"></i>
        Attendance records are maintained separately from the system audit log.
    </div>

</div>
@endsection
