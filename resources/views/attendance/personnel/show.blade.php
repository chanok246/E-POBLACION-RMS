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

<div class="container-fluid py-3 ep-page">

    {{-- Page header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="{{ route('attendance.personnel.index') }}" class="ep-back">
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
                    <button type="submit" class="btn btn-primary">
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
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Personnel summary --}}
    <div class="row g-4">

        <div class="col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center py-4">

                    <div class="ep-avatar mb-3" style="width:80px;height:80px;">
                        <i class="bi bi-person-fill" style="font-size:2.5rem;"></i>
                    </div>

                    <h5 class="fw-bold mb-1">
                        {{ $fullName ?: 'Unnamed Personnel' }}
                    </h5>

                    <div class="text-secondary mb-3">
                        {{ $personnel->position }}
                    </div>

                    <div class="mb-3">
                        <span class="ep-chip">{{ $personnel->personnel_id }}</span>
                    </div>

                    @if($isActive)
                        <span class="ep-badge ep-badge-ok">
                            <i class="bi bi-check-circle"></i>
                            Active
                        </span>
                    @else
                        <span class="ep-badge ep-badge-mute">
                            <i class="bi bi-person-slash"></i>
                            Inactive
                        </span>
                    @endif

                    <hr class="my-4">

                    <div class="text-start">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">QR ID Status</span>

                            @if($personnel->qr_token_hash && !$personnel->qr_revoked_at)
                                <span class="ep-text-ok fw-semibold">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Issued
                                </span>
                            @else
                                <span class="ep-text-mute fw-semibold">
                                    Not issued
                                </span>
                            @endif
                        </div>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">PIN Status</span>

                            @if($personnel->pin_hash)
                                <span class="ep-text-ok fw-semibold">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Set
                                </span>
                            @else
                                <span class="ep-text-bad fw-semibold">
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
            <div class="card shadow-sm mb-4">
                <div class="card-header py-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-person-vcard me-2 ep-text-ok"></i>
                        Personal Information
                    </h5>
                </div>

                <div class="card-body">
                    <div class="row g-4">

                        <div class="col-md-6">
                            <small class="text-secondary d-block mb-1">First Name</small>
                            <div class="fw-semibold">{{ $personnel->first_name }}</div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary d-block mb-1">Middle Name</small>
                            <div class="fw-semibold">{{ $personnel->middle_name ?: '—' }}</div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary d-block mb-1">Last Name</small>
                            <div class="fw-semibold">{{ $personnel->last_name }}</div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary d-block mb-1">Suffix</small>
                            <div class="fw-semibold">{{ $personnel->suffix ?: '—' }}</div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary d-block mb-1">Personnel ID</small>
                            <div class="fw-semibold">{{ $personnel->personnel_id }}</div>
                        </div>

                        <div class="col-md-6">
                            <small class="text-secondary d-block mb-1">Position</small>
                            <div class="fw-semibold">{{ $personnel->position }}</div>
                        </div>

                    </div>
                </div>
            </div>

            {{-- RMS account --}}
            <div class="card shadow-sm">
                <div class="card-header py-3">
                    <h5 class="fw-semibold mb-0">
                        <i class="bi bi-link-45deg me-2 ep-text-ok"></i>
                        RMS Account
                    </h5>
                </div>

                <div class="card-body">
                    @if($personnel->user)
                        <div class="d-flex align-items-center gap-3">
                            <div class="ep-avatar" style="width:46px;height:46px;">
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

                            <span class="ep-badge ep-badge-ok ms-auto">Linked</span>
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
    <div class="card shadow-sm mt-4">

        <div class="card-header py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-semibold mb-1">
                        <i class="bi bi-calendar-check me-2 ep-text-ok"></i>
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
                        <thead>
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
                                            <span class="ep-text-mute">—</span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($record->time_out)
                                            {{ $record->time_out->format('h:i A') }}
                                        @else
                                            <span class="ep-text-warn small fw-semibold">
                                                No Time Out
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $record->time_in_method ? strtoupper($record->time_in_method) : '—' }}
                                    </td>

                                    <td>
                                        {{ $record->time_out_method ? strtoupper($record->time_out_method) : '—' }}
                                    </td>

                                    <td>
                                        @if($record->time_in && $record->time_out)
                                            <span class="ep-badge ep-badge-ok">Complete</span>
                                        @elseif($record->time_in)
                                            <span class="ep-badge ep-badge-warn">Incomplete</span>
                                        @else
                                            <span class="ep-badge ep-badge-mute">
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
                    <i class="bi bi-calendar2-x display-4 ep-text-mute"></i>
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
        <div class="card shadow-sm mt-4" style="border-color: var(--ep-bad-bd) !important;">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h6 class="fw-semibold ep-text-bad mb-1">
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
