@extends('layouts.app')

@section('title', 'Attendance Personnel | Attendance Monitoring')

@section('content')
@php
    $currentUser = auth()->user();

    $isCaptain = $currentUser && method_exists($currentUser, 'isCaptain')
        ? $currentUser->isCaptain()
        : false;
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

    {{-- Page heading --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="mb-1">
                <a href="{{ route('attendance.index') }}" class="ep-back">
                    <i class="bi bi-arrow-left"></i>
                    Attendance Monitoring
                </a>
            </div>

            <h4 class="fw-bold mb-1">Attendance Personnel</h4>

            <p class="text-secondary mb-0">
                Register barangay personnel and manage their attendance identification.
            </p>
        </div>

        <a href="{{ route('attendance.personnel.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i>
            Register Personnel
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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

            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Attendance identification policy --}}
    <div class="ep-note mb-4">
        <div class="d-flex gap-2">
            <i class="bi bi-info-circle fs-5 ep-note-icon"></i>

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
    <div class="card shadow-sm">

        <div class="card-header py-3">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

                <div>
                    <h5 class="fw-semibold mb-1">
                        Registered Attendance Personnel
                    </h5>

                    <small class="text-secondary">
                        Manage personnel records used by the Attendance Monitoring System.
                    </small>
                </div>

                <span class="ep-badge ep-badge-mute">
                    <i class="bi bi-people"></i>
                    {{ $personnel->total() }} personnel
                </span>

            </div>
        </div>

        <div class="card-body p-0">

            @if($personnel->count())

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead>
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
                                    <td>{{ $person->position }}</td>

                                    {{-- Personnel status --}}
                                    <td>
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
                                    </td>

                                    {{-- Attendance actions --}}
                                    <td class="text-end pe-3">

                                        <div class="d-inline-flex flex-wrap justify-content-end gap-1">

                                            {{-- View personnel --}}
                                            <a href="{{ route('attendance.personnel.show', $person) }}"
                                               class="btn btn-sm btn-outline-secondary"
                                               title="View personnel"
                                               aria-label="View personnel">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            {{-- Edit personnel --}}
                                            <a href="{{ route('attendance.personnel.edit', $person) }}"
                                               class="btn btn-sm btn-outline-primary"
                                               title="Edit personnel"
                                               aria-label="Edit personnel">
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
                                                            title="Generate or replace QR ID"
                                                            aria-label="Generate or replace QR ID">
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
                                                            title="Deactivate personnel record"
                                                            aria-label="Deactivate personnel record">
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
                        <i class="bi bi-people display-4 ep-text-mute"></i>
                    </div>

                    <h5 class="fw-semibold">No personnel registered yet</h5>

                    <p class="text-secondary mb-3">
                        Register barangay personnel to start recording their attendance.
                    </p>

                    <a href="{{ route('attendance.personnel.create') }}" class="btn btn-primary">
                        <i class="bi bi-person-plus me-1"></i>
                        Register First Personnel
                    </a>

                </div>

            @endif

        </div>
    </div>

</div>
@endsection
