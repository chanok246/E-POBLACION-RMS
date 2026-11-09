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
    /* =====================================================================
       ATTENDANCE MONITORING
       Uses the layout's theme variables, so it follows the watermelon
       light theme and the dark theme automatically:
         - light cards / tables  -> dark text
         - dark theme            -> light text
       ===================================================================== */
    .attendance-page {
        --att-text: var(--text-dark);
        --att-soft: color-mix(in srgb, var(--text-dark) 74%, var(--surface));
        --att-surface: var(--surface);
        --att-surface-2: var(--surface-secondary);
        --att-surface-3: var(--surface-tertiary);
        --att-border: var(--border-color);
        --att-border-strong: var(--border-soft);
        --att-hover: var(--table-hover);
        --att-shadow: var(--shadow);
        --att-accent: var(--primary-d);
        --att-accent-soft: var(--accent-soft);
        --att-on-accent: var(--on-accent);

        --att-teal: #0f766e;
        --att-teal-soft: rgba(15, 118, 110, .13);

        color: var(--att-text);
    }

    html[data-theme="dark"] .attendance-page {
        --att-teal: #5eead4;
        --att-teal-soft: rgba(94, 234, 212, .14);
    }

    /* ---------- Heading ---------- */
    .attendance-page .att-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
        color: var(--att-accent);
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .attendance-page .att-heading {
        margin-bottom: 4px;
        color: var(--att-text);
        font-weight: 800;
        letter-spacing: -.03em;
    }

    .attendance-page .att-subtitle {
        margin: 0;
        color: var(--att-soft);
        font-size: .9rem;
    }

    .attendance-page .att-date-chip {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 16px;
        box-shadow: var(--att-shadow);
    }

    .attendance-page .att-date-chip > i {
        color: var(--att-accent);
        font-size: 1.3rem;
    }

    .attendance-page .att-date-label {
        color: var(--att-soft);
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
    }

    .attendance-page .att-date-value {
        color: var(--att-text);
        font-size: .9rem;
        font-weight: 800;
    }

    /* ---------- Navigation ---------- */
    .attendance-page .att-navigation {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
        padding: 8px;
        margin-bottom: 22px;
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 16px;
        box-shadow: var(--att-shadow);
    }

    .attendance-page .att-nav-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 14px;
        border-radius: 10px;
        color: var(--att-text);
        font-size: .83rem;
        font-weight: 600;
        text-decoration: none;
        transition: background-color .15s, color .15s;
    }

    .attendance-page .att-nav-link i {
        font-size: 1rem;
        color: var(--att-soft);
        transition: color .15s;
    }

    .attendance-page .att-nav-link:hover {
        background: var(--att-surface-3);
        color: var(--att-text);
    }

    .attendance-page .att-nav-link.active,
    .attendance-page .att-nav-link.active:hover {
        background: var(--att-accent-soft);
        color: var(--att-accent);
        font-weight: 800;
        box-shadow: inset 0 0 0 1px var(--att-accent);
    }

    .attendance-page .att-nav-link.active i {
        color: var(--att-accent);
    }

    .attendance-page .att-nav-kiosk {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-left: auto;
        min-height: 40px;
        padding: 0 18px;
        border-radius: 10px;
        background: var(--att-accent);
        color: var(--att-on-accent);
        font-size: .83rem;
        font-weight: 800;
        text-decoration: none;
        transition: filter .15s, transform .15s;
    }

    .attendance-page .att-nav-kiosk:hover {
        color: var(--att-on-accent);
        filter: brightness(1.1);
        transform: translateY(-1px);
    }

    /* ---------- Summary cards ---------- */
    .attendance-page .att-card {
        position: relative;
        height: 100%;
        padding: 20px;
        overflow: hidden;
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 20px;
        box-shadow: var(--att-shadow);
        transition: transform .2s, box-shadow .2s;
    }

    .attendance-page .att-card::before {
        content: "";
        position: absolute;
        inset: 0 0 auto 0;
        height: 4px;
        background: var(--tone-bar);
    }

    .attendance-page .att-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px var(--shadow-color);
    }

    .attendance-page .tone-primary { --tone-fg: var(--info);        --tone-bg: var(--info-soft);    --tone-bar: var(--info); }
    .attendance-page .tone-success { --tone-fg: var(--primary-d);   --tone-bg: var(--accent-soft);  --tone-bar: var(--primary); }
    .attendance-page .tone-info    { --tone-fg: var(--att-teal);    --tone-bg: var(--att-teal-soft); --tone-bar: var(--att-teal); }
    .attendance-page .tone-warning { --tone-fg: var(--warning-text); --tone-bg: var(--warning-soft); --tone-bar: var(--warning); }
    .attendance-page .tone-neutral { --tone-fg: var(--att-soft);    --tone-bg: var(--att-surface-3); --tone-bar: var(--att-border-strong); }

    .attendance-page .att-stat-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }

    .attendance-page .att-stat-eyebrow {
        color: var(--att-soft);
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .03em;
        text-transform: uppercase;
    }

    .attendance-page .att-stat-icon {
        display: grid;
        place-items: center;
        flex: 0 0 44px;
        width: 44px;
        height: 44px;
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
        color: var(--att-soft);
        font-size: .8rem;
    }

    /* ---------- Today's attendance ---------- */
    .attendance-page .att-section {
        overflow: hidden;
        background: var(--att-surface);
        border: 1px solid var(--att-border);
        border-radius: 20px;
        box-shadow: var(--att-shadow);
    }

    .attendance-page .att-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        padding: 18px 22px;
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
        padding: 8px 15px;
        border: 1.5px solid var(--att-accent);
        border-radius: 10px;
        color: var(--att-accent);
        font-size: .8rem;
        font-weight: 800;
        text-decoration: none;
        transition: background-color .15s, color .15s;
    }

    .attendance-page .att-btn-outline:hover {
        background: var(--att-accent);
        color: var(--att-on-accent);
    }

    .attendance-page .att-table {
        --bs-table-bg: transparent;
        --bs-table-color: var(--att-text);
        --bs-table-border-color: var(--att-border);
        --bs-table-hover-bg: var(--att-hover);
        --bs-table-hover-color: var(--att-text);
        margin: 0;
        color: var(--att-text);
    }

    .attendance-page .att-table th {
        padding: 13px 20px;
        background: var(--att-surface-2);
        border-bottom: 1px solid var(--att-border);
        color: var(--att-soft);
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .05em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .attendance-page .att-table td {
        padding: 14px 20px;
        background: transparent;
        border-color: var(--att-border);
        color: var(--att-text);
        font-size: .87rem;
        vertical-align: middle;
    }

    .attendance-page .att-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .attendance-page .att-avatar {
        display: grid;
        place-items: center;
        flex: 0 0 38px;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: var(--att-accent-soft);
        color: var(--att-accent);
        font-size: .8rem;
        font-weight: 800;
    }

    .attendance-page .att-person-name {
        color: var(--att-text);
        font-weight: 800;
        line-height: 1.3;
    }

    .attendance-page .att-person-id {
        margin-top: 2px;
        color: var(--att-soft);
        font-size: .75rem;
    }

    .attendance-page .att-time {
        color: var(--att-text);
        font-weight: 800;
        font-variant-numeric: tabular-nums;
    }

    .attendance-page .att-dash {
        color: var(--att-soft);
    }

    .attendance-page .att-method {
        display: inline-block;
        padding: 3px 9px;
        background: var(--att-surface-2);
        border: 1px solid var(--att-border-strong);
        border-radius: 8px;
        color: var(--att-text);
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

    /* ---------- Empty state ---------- */
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
        background: var(--att-accent-soft);
        color: var(--att-accent);
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
        color: var(--att-soft);
        font-size: .85rem;
    }

    .attendance-page .att-btn-solid {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 17px;
        background: var(--att-accent);
        border-radius: 10px;
        color: var(--att-on-accent);
        font-size: .82rem;
        font-weight: 800;
        text-decoration: none;
        transition: filter .15s;
    }

    .attendance-page .att-btn-solid:hover {
        color: var(--att-on-accent);
        filter: brightness(1.1);
    }

    .attendance-page .att-footnote {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 16px;
        color: var(--att-soft);
        font-size: .78rem;
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 767.98px) {
        .attendance-page .att-navigation {
            align-items: stretch;
        }

        .attendance-page .att-nav-link {
            flex: 1 1 calc(50% - 6px);
        }

        .attendance-page .att-nav-kiosk {
            flex: 1 1 100%;
            margin-left: 0;
        }
    }

    @media (max-width: 575.98px) {
        .attendance-page .att-nav-link {
            flex: 1 1 100%;
        }

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

        <a href="{{ route('attendance.records') }}"
           class="att-nav-link">
            <i class="bi bi-clock-history"></i>
            Attendance Records
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
