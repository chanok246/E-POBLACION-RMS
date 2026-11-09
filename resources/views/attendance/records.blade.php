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
    :root,
    [data-bs-theme="light"],
    .ep-page[data-ep="light"] {
        --ep-ok-fg: #0b6b3a;
        --ep-ok-bg: rgba(25, 135, 84, .13);
        --ep-ok-bd: rgba(25, 135, 84, .40);

        --ep-warn-fg: #7a4f00;
        --ep-warn-bg: rgba(255, 193, 7, .22);
        --ep-warn-bd: rgba(176, 126, 0, .45);

        --ep-bad-fg: #a4202e;
        --ep-bad-bg: rgba(164, 32, 46, .09);
        --ep-bad-bd: rgba(164, 32, 46, .35);

        --ep-mute-fg: #56615a;
        --ep-mute-bg: rgba(60, 80, 68, .08);
        --ep-mute-bd: rgba(60, 80, 68, .30);

        --ep-head-fg: #3f4a44;
        --ep-note-bg: rgba(25, 135, 84, .07);
        --ep-field-bg: #ffffff;
        --ep-field-bd: #aebab3;

        --ep-text: #1c2822;
        --ep-panel-bg: #ffffff;
        --ep-border: #d3dbd6;
        --ep-th-bg: #eef3f0;
    }

    [data-bs-theme="dark"],
    .ep-page[data-ep="dark"] {
        --ep-ok-fg: #7ee0a3;
        --ep-ok-bg: rgba(126, 224, 163, .14);
        --ep-ok-bd: rgba(126, 224, 163, .42);

        --ep-warn-fg: #ffd666;
        --ep-warn-bg: rgba(255, 214, 102, .14);
        --ep-warn-bd: rgba(255, 214, 102, .42);

        --ep-bad-fg: #ff9a9a;
        --ep-bad-bg: rgba(255, 154, 154, .12);
        --ep-bad-bd: rgba(255, 154, 154, .40);

        --ep-mute-fg: #a9b9b0;
        --ep-mute-bg: rgba(255, 255, 255, .07);
        --ep-mute-bd: rgba(255, 255, 255, .24);

        --ep-head-fg: #c6d4cc;
        --ep-note-bg: rgba(126, 224, 163, .07);
        --ep-field-bg: rgba(255, 255, 255, .05);
        --ep-field-bd: rgba(255, 255, 255, .22);

        --ep-text: #e8f1ec;
        --ep-panel-bg: #13261b;
        --ep-border: rgba(126, 224, 163, .16);
        --ep-th-bg: rgba(255, 255, 255, .06);
    }

    /* ---------- Text helpers ---------- */
    .ep-page .text-secondary,
    .ep-page .text-muted,
    .ep-page .form-text {
        color: var(--ep-mute-fg) !important;
    }

    .ep-page .ep-text-ok   { color: var(--ep-ok-fg) !important; }
    .ep-page .ep-text-warn { color: var(--ep-warn-fg) !important; }
    .ep-page .ep-text-bad  { color: var(--ep-bad-fg) !important; }
    .ep-page .ep-text-mute { color: var(--ep-mute-fg) !important; }

    .ep-page .text-primary { color: var(--ep-ok-fg) !important; }
    .ep-page .text-danger  { color: var(--ep-bad-fg) !important; }

    /* ---------- Badges / status pills ---------- */
    .ep-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: .32rem .75rem;
        border: 1px solid transparent;
        border-radius: 999px;
        font-size: .75rem;
        font-weight: 700;
        line-height: 1.1;
        white-space: nowrap;
    }

    .ep-badge-ok   { color: var(--ep-ok-fg);   background: var(--ep-ok-bg);   border-color: var(--ep-ok-bd); }
    .ep-badge-warn { color: var(--ep-warn-fg); background: var(--ep-warn-bg); border-color: var(--ep-warn-bd); }
    .ep-badge-bad  { color: var(--ep-bad-fg);  background: var(--ep-bad-bg);  border-color: var(--ep-bad-bd); }
    .ep-badge-mute { color: var(--ep-mute-fg); background: var(--ep-mute-bg); border-color: var(--ep-mute-bd); }

    .ep-chip {
        display: inline-block;
        padding: .4rem .9rem;
        border: 1px solid var(--ep-border);
        border-radius: 8px;
        background: var(--ep-th-bg);
        color: var(--ep-text);
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .02em;
    }

    /* ---------- Panels / notes ---------- */
    .ep-page { color: var(--ep-text); }
    .ep-page[data-ep="dark"] { color-scheme: dark; }

    .ep-page h1, .ep-page h2, .ep-page h3,
    .ep-page h4, .ep-page h5, .ep-page h6 {
        color: var(--ep-text);
    }

    .ep-page .card,
    .ep-page .ar-panel,
    .ep-page .modal-content {
        background: var(--ep-panel-bg);
        border: 1px solid var(--ep-border) !important;
        color: var(--ep-text);
    }

    .ep-page .card-header,
    .ep-page .modal-header,
    .ep-page .modal-footer {
        background: transparent;
        border-color: var(--ep-border);
        color: var(--ep-text);
    }

    .ep-page .form-select option {
        background: var(--ep-panel-bg);
        color: var(--ep-text);
    }

    .ep-note {
        padding: 14px 16px;
        border: 1px solid var(--ep-ok-bd);
        border-left: 4px solid var(--ep-ok-fg);
        border-radius: 10px;
        background: var(--ep-note-bg);
        color: var(--ep-text);
    }

    .ep-note .ep-note-icon { color: var(--ep-ok-fg); }

    .ep-subpanel {
        background: var(--ep-th-bg);
        border-top: 1px solid var(--ep-border);
        border-bottom: 1px solid var(--ep-border);
        color: var(--ep-text);
    }

    .ep-avatar {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid var(--ep-ok-bd);
        background: var(--ep-ok-bg);
        color: var(--ep-ok-fg);
    }

    .ep-code {
        display: inline-block;
        padding: .5rem 1rem;
        border: 1px solid var(--ep-ok-bd);
        border-radius: 10px;
        background: var(--ep-th-bg);
        color: var(--ep-ok-fg);
    }

    /* ---------- Tables ---------- */
    .ep-page .table {
        --bs-table-bg: transparent;
        --bs-table-color: var(--ep-text);
        --bs-table-border-color: var(--ep-border);
        --bs-table-hover-bg: var(--ep-ok-bg);
        --bs-table-hover-color: var(--ep-text);
    }

    .ep-page .table > thead > tr > th {
        background: var(--ep-th-bg);
        color: var(--ep-head-fg);
        border-bottom: 1px solid var(--ep-border);
        font-weight: 700;
    }

    /* ---------- Form controls ---------- */
    .ep-page .form-label {
        color: var(--ep-text);
        font-weight: 600;
    }

    .ep-page .form-control,
    .ep-page .form-select {
        background-color: var(--ep-field-bg);
        border-color: var(--ep-field-bd);
        color: var(--ep-text);
    }

    .ep-page .form-control::placeholder {
        color: var(--ep-mute-fg);
        opacity: .85;
    }

    .ep-page .form-control:focus,
    .ep-page .form-select:focus {
        background-color: var(--ep-field-bg);
        color: var(--ep-text);
        border-color: var(--ep-ok-fg);
        box-shadow: 0 0 0 .2rem var(--ep-ok-bd);
    }

    .ep-page .form-control:disabled,
    .ep-page .form-control[readonly] {
        opacity: .8;
    }

    .ep-page .form-control.is-invalid { border-color: var(--ep-bad-fg); }

    .ep-page .invalid-feedback { color: var(--ep-bad-fg); }

    /* ---------- Buttons (colors come through Bootstrap variables) ---------- */
    .ep-page .btn-outline-secondary {
        --bs-btn-color: var(--ep-head-fg);
        --bs-btn-border-color: var(--ep-field-bd);
        --bs-btn-hover-color: var(--ep-panel-bg);
        --bs-btn-hover-bg: var(--ep-head-fg);
        --bs-btn-hover-border-color: var(--ep-head-fg);
    }

    .ep-page .btn-outline-primary,
    .ep-page .btn-outline-success {
        --bs-btn-color: var(--ep-ok-fg);
        --bs-btn-border-color: var(--ep-ok-fg);
        --bs-btn-hover-color: #ffffff;
        --bs-btn-hover-bg: var(--ep-ok-fg);
        --bs-btn-hover-border-color: var(--ep-ok-fg);
        --bs-btn-active-color: #ffffff;
        --bs-btn-active-bg: var(--ep-ok-fg);
    }

    [data-bs-theme="dark"] .ep-page .btn-outline-primary,
    [data-bs-theme="dark"] .ep-page .btn-outline-success,
    .ep-page[data-ep="dark"] .btn-outline-primary,
    .ep-page[data-ep="dark"] .btn-outline-success {
        --bs-btn-hover-color: #06261a;
        --bs-btn-active-color: #06261a;
    }

    .ep-page .btn-outline-warning {
        --bs-btn-color: var(--ep-warn-fg);
        --bs-btn-border-color: var(--ep-warn-fg);
        --bs-btn-hover-color: #1f1500;
        --bs-btn-hover-bg: #ffc107;
        --bs-btn-hover-border-color: #ffc107;
    }

    .ep-page .btn-outline-danger {
        --bs-btn-color: var(--ep-bad-fg);
        --bs-btn-border-color: var(--ep-bad-fg);
        --bs-btn-hover-color: #ffffff;
        --bs-btn-hover-bg: #b02a37;
        --bs-btn-hover-border-color: #b02a37;
    }

    .ep-page .btn:focus-visible,
    .ep-page a:focus-visible {
        outline: 2px solid var(--ep-ok-fg);
        outline-offset: 2px;
    }

    .ep-page .btn-close { opacity: .75; }
    [data-bs-theme="dark"] .ep-page .btn-close,
    .ep-page[data-ep="dark"] .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }

    /* ---------- Back links ---------- */
    .ep-back {
        color: var(--ep-mute-fg);
        text-decoration: none;
        font-size: .88rem;
    }

    .ep-back:hover { color: var(--ep-ok-fg); }
</style>

<script>
    /* Detect the app's real light/dark mode so the colors above always pair
       correctly. The layout's own text color is the most reliable signal:
       light text => the page is dark, dark text => the page is light. */
    (function () {
        function lum(c) {
            var m = (c || '').match(/[\d.]+/g);
            if (!m || m.length < 3) return null;
            if (m.length > 3 && parseFloat(m[3]) === 0) return null;
            var r = m.slice(0, 3).map(function (v) {
                v = v / 255;
                return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
            });
            return 0.2126 * r[0] + 0.7152 * r[1] + 0.0722 * r[2];
        }

        function backgroundLum(el) {
            while (el) {
                var l = lum(getComputedStyle(el).backgroundColor);
                if (l !== null) return l;
                el = el.parentElement;
            }
            return null;
        }

        function isDark(page) {
            var root = document.documentElement;
            var explicit = root.getAttribute('data-bs-theme') || root.getAttribute('data-theme');
            if (explicit === 'dark') return true;
            if (explicit === 'light') return false;

            var host = page.parentElement || document.body;
            var t = lum(getComputedStyle(host).color);
            if (t !== null && (t > 0.55 || t < 0.25)) return t > 0.55;

            var b = backgroundLum(host);
            if (b !== null) return b < 0.4;
            return false;
        }

        function apply() {
            document.querySelectorAll('.ep-page').forEach(function (el) {
                el.setAttribute('data-ep', isDark(el) ? 'dark' : 'light');
            });
        }

        function applyLater() {
            apply();
            setTimeout(apply, 60);
            setTimeout(apply, 450);
        }

        var opts = { attributes: true, attributeFilter: ['class', 'data-bs-theme', 'data-theme', 'style'] };
        apply();
        document.addEventListener('DOMContentLoaded', function () {
            applyLater();
            new MutationObserver(applyLater).observe(document.documentElement, opts);
            new MutationObserver(applyLater).observe(document.body, opts);
            document.addEventListener('click', function () { setTimeout(applyLater, 30); });
        });
        window.addEventListener('storage', applyLater);
    })();
</script>

<style>
    .attendance-records .ar-heading {
        font-weight: 800;
        letter-spacing: -.03em;
    }

    .attendance-records .ar-panel {
        background: var(--ep-panel-bg);
        border: 1px solid var(--ep-border);
        border-radius: 16px;
        overflow: hidden;
    }

    .attendance-records .ar-panel-header {
        padding: 18px 21px;
        border-bottom: 1px solid var(--ep-border);
    }

    .attendance-records .ar-filter { padding: 20px; }

    .attendance-records .form-label {
        font-size: .8rem;
        margin-bottom: 7px;
    }

    .attendance-records .form-control,
    .attendance-records .form-select {
        min-height: 42px;
        border-radius: 9px;
        font-size: .87rem;
    }

    .attendance-records .ar-table { margin-bottom: 0; }

    .attendance-records .ar-table thead th {
        padding: 13px 16px;
        font-size: .72rem;
        letter-spacing: .045em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .attendance-records .ar-table tbody td {
        padding: 15px 16px;
        vertical-align: middle;
        font-size: .86rem;
    }

    .attendance-records .ar-person-name { font-weight: 700; }

    .attendance-records .ar-person-id {
        font-size: .76rem;
        color: var(--ep-mute-fg);
        margin-top: 3px;
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
        border: 1px solid var(--ep-ok-bd);
        background: var(--ep-ok-bg);
        color: var(--ep-ok-fg);
        font-size: 1.5rem;
    }

    .attendance-records .ar-pagination {
        padding: 16px 20px;
        border-top: 1px solid var(--ep-border);
    }

    .attendance-records .ar-action-btn {
        font-size: .76rem;
        font-weight: 700;
    }

    .attendance-records .ar-info {
        border: 1px solid var(--ep-border);
        border-radius: 11px;
        padding: 13px 15px;
        background: var(--ep-th-bg);
        color: var(--ep-text);
        font-size: .82rem;
    }

    @media print {
        body { background: #fff !important; color: #000 !important; }
        .ep-page, .ep-page .ar-panel, .ep-page .ar-panel * { background: #fff !important; color: #000 !important; }

        .sidebar,
        .app-sidebar,
        .topbar,
        .ar-no-print,
        .attendance-records .ar-filter,
        .attendance-records .ar-actions,
        .attendance-records .ar-pagination {
            display: none !important;
        }

        .attendance-records .ar-panel { border: 0; box-shadow: none; }
        .attendance-records .ar-table { font-size: 10px; color: #000; }
        .attendance-records .ar-table th,
        .attendance-records .ar-table td { padding: 7px !important; color: #000 !important; }
    }
</style>

<div class="attendance-records ep-page">

    {{-- Page heading --}}
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-2">
                <span class="ep-text-ok"><i class="bi bi-calendar2-check"></i></span>
                <span class="small fw-bold text-uppercase ep-text-mute">Attendance Monitoring</span>
            </div>

            <h2 class="ar-heading mb-1">Attendance Records</h2>

            <p class="text-secondary small mb-0">
                Review and filter personnel daily attendance transactions.
            </p>
        </div>

        <div class="d-flex flex-wrap gap-2 ar-no-print">
            <a href="{{ route('attendance.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>
                Overview
            </a>

            <button type="button" class="btn btn-outline-primary btn-sm" onclick="window.print()">
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
                <i class="bi bi-funnel me-2 ep-text-ok"></i>
                Filter Attendance
            </h5>
            <p class="text-secondary small mb-0">
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
                            <option value="{{ $value }}" @selected(request('status') === $value)>
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
                <div class="text-secondary small">
                    {{ \Carbon\Carbon::parse($selectedDate)->format('F d, Y') }}
                </div>
            </div>

            <div class="ep-badge ep-badge-mute">
                <i class="bi bi-list-ul"></i>
                {{ $records->total() }} record(s) found
            </div>
        </div>

        @if ($records->isEmpty())
            <div class="ar-empty">
                <div class="ar-empty-icon">
                    <i class="bi bi-calendar2-x"></i>
                </div>

                <h6 class="fw-bold mb-2">No Attendance Records Found</h6>

                <p class="text-secondary small mb-3">
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

                                // Semantic colors: green = good, amber = needs attention,
                                // red = absent (negative), gray = neutral/unknown
                                $statusClass = match ($recordStatus) {
                                    'present' => 'ok',
                                    'late' => 'warn',
                                    'absent' => 'bad',
                                    default => 'mute',
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
                                        <span class="fw-bold ep-text-ok">
                                            {{ $record->time_in->format('h:i A') }}
                                        </span>
                                    @else
                                        <span class="ep-text-mute">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($hasTimeOut)
                                        <span class="fw-bold">
                                            {{ $record->time_out->format('h:i A') }}
                                        </span>
                                    @elseif ($departureAuthorization)
                                        <span class="ep-text-warn fw-semibold small">
                                            Authorized exception
                                        </span>
                                    @else
                                        <span class="ep-text-mute">Not recorded</span>
                                    @endif
                                </td>

                                <td>
                                    <div class="small">
                                        <span class="ep-text-mute">In:</span>
                                        {{ strtoupper($record->time_in_method ?? '—') }}
                                    </div>
                                    <div class="small">
                                        <span class="ep-text-mute">Out:</span>
                                        {{ strtoupper($record->time_out_method ?? '—') }}
                                    </div>
                                </td>

                                <td>
                                    <span class="ep-badge ep-badge-{{ $statusClass }}">
                                        <i class="bi bi-circle-fill" style="font-size:6px"></i>
                                        {{ $statusLabel }}
                                    </span>

                                    @if ($departureAuthorization)
                                        <div class="small ep-text-warn mt-1">
                                            <i class="bi bi-shield-check me-1"></i>
                                            Departure authorized
                                        </div>
                                    @elseif ($hasTimeIn && ! $hasTimeOut)
                                        <div class="small ep-text-mute mt-1">
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
                                            <span class="small ep-text-ok fw-semibold">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Authorized
                                            </span>
                                        @else
                                            <span class="small ep-text-mute">No action</span>
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
                                        <i class="bi bi-shield-check ep-text-warn me-2"></i>
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
                                        <div class="ep-text-mute">
                                            Personnel ID:
                                            {{ $record->personnel?->personnel_id ?? '—' }}
                                        </div>
                                        <div class="ep-text-mute">
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
                                            <span class="ep-text-bad">*</span>
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

    <div class="ep-note mt-3 small">
        <i class="bi bi-info-circle ep-note-icon me-1"></i>
        <strong>Record integrity:</strong>
        Emergency departure authorization preserves the original Time In.
        It does not fabricate or automatically fill a missing Time Out.
        Only the Barangay Captain can authorize this exception.
    </div>

</div>
@endsection