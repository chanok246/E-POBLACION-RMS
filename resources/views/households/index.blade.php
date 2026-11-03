@extends('layouts.app')

@section('title', 'Household Management | e-Poblacion')

@push('styles')
<style>
    /* =========================================================
       Household Management — modern, theme-balanced, live search
       Every text/background pair is chosen for strong contrast in
       BOTH light and dark mode.
       ========================================================= */
    .households-page {
        --hp-bg:#ffffff;
        --hp-soft:#f7f2f3;
        --hp-soft-2:#efe7e9;
        --hp-text:#2b2023;
        --hp-muted:#5f4f54;
        --hp-border:#e3d6d9;
        --hp-accent:#d63e4c;
        --hp-accent-2:#ef6b78;
        --hp-on-accent:#ffffff;
        --hp-accent-soft:#fdeaec;
        --hp-green:#2f8a3a;
        --hp-green-soft:#e6f4e4;
        --hp-off:#5f4f54;
        --hp-off-soft:#ece4e6;
        --hp-danger:#b4232f;
        --hp-danger-soft:#fdeaec;
        --hp-shadow:0 1px 2px rgba(60,20,30,.05),0 14px 30px rgba(60,20,30,.07);
        --hp-select-arrow:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%232b2023' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        color:var(--hp-text);
    }
    html[data-theme="dark"] .households-page {
        --hp-bg:#1a2a21;
        --hp-soft:#14211a;
        --hp-soft-2:#223529;
        --hp-text:#f6eff1;
        --hp-muted:#c3d0c7;
        --hp-border:#34503d;
        --hp-accent:#ff8793;
        --hp-accent-2:#ffa9b1;
        --hp-on-accent:#2a0c12;
        --hp-accent-soft:#3a1f26;
        --hp-green:#7fdc8a;
        --hp-green-soft:#1f3a26;
        --hp-off:#c3d0c7;
        --hp-off-soft:#223529;
        --hp-danger:#ffb3ba;
        --hp-danger-soft:#43202a;
        --hp-shadow:none;
        --hp-select-arrow:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23f6eff1' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    }
    .households-page, .households-page * { box-sizing:border-box; }
    .households-page :focus-visible { outline:3px solid var(--hp-green); outline-offset:2px; }
    .households-page .text-muted { color:var(--hp-muted) !important; }

    /* ---------- Hero ---------- */
    .hp-hero {
        position:relative; border-radius:24px; padding:26px 28px; margin-bottom:22px; color:#fff;
        background:linear-gradient(120deg,#d63e4c 0%,#ef6b78 60%,#f59aa2 100%);
        box-shadow:0 16px 32px rgba(214,62,76,.25);
        display:flex; align-items:center; justify-content:space-between; gap:18px; flex-wrap:wrap;
    }
    html[data-theme="dark"] .hp-hero { background:linear-gradient(120deg,#8f2f40,#b84557); box-shadow:none; }
    /* decorative circles live in their own clipped layer so the dropdown menu is never cut off */
    .hp-hero-bg { position:absolute; inset:0; border-radius:inherit; overflow:hidden; pointer-events:none; }
    .hp-hero-bg::before { content:''; position:absolute; width:240px; height:240px; right:-60px; top:-110px; border-radius:50%; background:rgba(255,255,255,.13); }
    .hp-hero-bg::after  { content:''; position:absolute; width:150px; height:150px; right:200px; bottom:-100px; border-radius:50%; background:rgba(105,187,104,.38); }
    .hp-hero > .hp-hero-in { position:relative; z-index:2; }
    .hp-crumb { font-size:12px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:#fff; opacity:.92; margin-bottom:6px; }
    .hp-hero h1 { margin:0; font-size:clamp(1.6rem,3vw,2.1rem); font-weight:800; letter-spacing:-.02em; color:#fff; }
    .hp-hero p { margin:6px 0 0; font-size:.95rem; color:#fff; opacity:.96; max-width:620px; }
    .hp-hero-actions { display:flex; gap:10px; flex-wrap:wrap; }
    .hp-btn-ghost, .hp-btn-solid {
        display:inline-flex; align-items:center; gap:8px; min-height:46px; padding:.6rem 1.25rem; border-radius:14px;
        font-weight:800; text-decoration:none; cursor:pointer; transition:transform .15s, box-shadow .15s, background .15s;
    }
    .hp-btn-ghost { background:rgba(255,255,255,.2); color:#fff !important; border:1.5px solid rgba(255,255,255,.55); }
    .hp-btn-ghost:hover, .hp-btn-ghost:focus { background:rgba(255,255,255,.3); }
    .hp-btn-solid { background:#fff; color:#b4232f !important; border:1.5px solid #fff; box-shadow:0 8px 18px rgba(60,10,20,.2); }
    .hp-btn-solid:hover { transform:translateY(-2px); box-shadow:0 12px 22px rgba(60,10,20,.26); }
    html[data-theme="dark"] .hp-btn-solid { background:#fff4f5; }

    /* dropdown (Print Masterlist) */
    .households-page .dropdown-menu { background:var(--hp-bg); border:1.5px solid var(--hp-border); border-radius:14px; padding:8px; box-shadow:0 18px 40px rgba(0,0,0,.2); min-width:220px; }
    .households-page .dropdown-item { color:var(--hp-text); border-radius:10px; font-size:.92rem; font-weight:600; padding:9px 12px; }
    .households-page .dropdown-item:hover, .households-page .dropdown-item:focus { background:var(--hp-accent-soft); color:var(--hp-accent); }
    .households-page .dropdown-header { color:var(--hp-muted); font-weight:800; font-size:.72rem; letter-spacing:.08em; text-transform:uppercase; }
    .households-page .dropdown-divider { border-top-color:var(--hp-border); }

    /* ---------- Alert ---------- */
    .hp-alert { display:flex; gap:10px; align-items:flex-start; padding:14px 16px; border-radius:14px; margin-bottom:16px; background:var(--hp-danger-soft); color:var(--hp-danger); border:1.5px solid var(--hp-danger); font-weight:600; }
    .hp-alert .btn-close { margin-left:auto; }
    html[data-theme="dark"] .hp-alert .btn-close { filter:invert(1) grayscale(100%) brightness(200%); }

    /* ---------- Card ---------- */
    .households-page .card {
        position:relative; background:var(--hp-bg); color:var(--hp-text);
        border:1px solid var(--hp-border) !important; border-radius:22px; box-shadow:var(--hp-shadow); overflow:hidden;
    }
    .hp-loadbar { position:absolute; left:0; top:0; height:3px; width:100%; z-index:6; overflow:hidden; opacity:0; transition:opacity .15s; pointer-events:none; }
    .hp-loadbar::after { content:''; position:absolute; inset:0; width:40%; background:linear-gradient(90deg,var(--hp-accent),var(--hp-green)); animation:hpslide 1s infinite ease-in-out; }
    .card.is-loading .hp-loadbar { opacity:1; }
    @keyframes hpslide { 0% { transform:translateX(-100%); } 100% { transform:translateX(260%); } }

    /* ---------- Tabs ---------- */
    .hp-tabs { display:flex; align-items:center; gap:8px; padding:16px 18px 0; overflow-x:auto; }
    .hp-tab {
        display:inline-flex; align-items:center; gap:10px; padding:10px 16px; margin-bottom:14px; white-space:nowrap;
        border-radius:999px; text-decoration:none; font-weight:700; font-size:.9rem;
        color:var(--hp-muted); background:var(--hp-soft); border:1.5px solid var(--hp-border);
        transition:background .15s,color .15s,border-color .15s;
    }
    .hp-tab b { font-size:.85rem; font-weight:800; padding:2px 9px; border-radius:99px; background:var(--hp-bg); color:var(--hp-text); font-variant-numeric:tabular-nums; }
    .hp-tab:hover { color:var(--hp-text); border-color:var(--hp-accent-2); }
    .hp-tab.is-active { background:var(--hp-accent); border-color:var(--hp-accent); color:var(--hp-on-accent); }
    .hp-tab.is-active b { background:rgba(255,255,255,.28); color:var(--hp-on-accent); }
    html[data-theme="dark"] .hp-tab.is-active b { background:rgba(42,12,18,.22); }
    .hp-members { margin-left:auto; margin-bottom:14px; padding:9px 14px; white-space:nowrap; border-radius:999px; background:var(--hp-green-soft); color:var(--hp-green); font-size:.85rem; font-weight:700; }
    .hp-members strong { font-weight:800; }

    /* ---------- Toolbar ---------- */
    .hp-tools { display:flex; gap:10px; flex-wrap:wrap; padding:0 18px 16px; align-items:center; border-bottom:1px solid var(--hp-border); }
    .hp-search { position:relative; flex:1 1 320px; }
    .hp-search > i.lead { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--hp-muted); pointer-events:none; }
    .hp-search kbd { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:var(--hp-bg); color:var(--hp-muted); border:1px solid var(--hp-border); font-size:.75rem; font-weight:700; padding:1px 8px; border-radius:6px; }
    .hp-search.has-text kbd { display:none; }
    .hp-x { position:absolute; right:8px; top:50%; transform:translateY(-50%); width:30px; height:30px; display:none; place-items:center; border:0; border-radius:8px; background:var(--hp-soft-2); color:var(--hp-text); cursor:pointer; }
    .hp-search.has-text .hp-x { display:grid; }
    .hp-x:hover { background:var(--hp-danger-soft); color:var(--hp-danger); }

    .households-page .form-control, .households-page .form-select {
        min-height:46px; background-color:var(--hp-soft); color:var(--hp-text);
        border:1.5px solid var(--hp-border); border-radius:14px; font-size:.95rem; font-weight:600;
        transition:border-color .15s, box-shadow .15s, background-color .15s;
    }
    .households-page .hp-search .form-control { padding-left:42px; padding-right:48px; }
    .households-page .form-control::placeholder { color:var(--hp-muted); opacity:.8; font-weight:500; }
    .households-page .form-control:hover, .households-page .form-select:hover { border-color:var(--hp-accent-2); }
    .households-page .form-control:focus, .households-page .form-select:focus {
        background-color:var(--hp-bg); color:var(--hp-text); border-color:var(--hp-accent); box-shadow:0 0 0 .22rem var(--hp-accent-soft);
    }
    .households-page .form-select {
        appearance:none; -webkit-appearance:none; flex:0 0 170px;
        background-image:var(--hp-select-arrow); background-repeat:no-repeat; background-position:right .9rem center; background-size:16px; padding-right:2.4rem;
    }
    .households-page .form-select option { background:var(--hp-bg); color:var(--hp-text); }
    .households-page input[type="search"]::-webkit-search-cancel-button { display:none; }

    .hp-clear { display:none; align-items:center; gap:6px; min-height:46px; padding:0 14px; border:1.5px dashed var(--hp-border); border-radius:14px; background:transparent; color:var(--hp-muted); font-size:.875rem; font-weight:700; cursor:pointer; }
    .hp-clear.show { display:inline-flex; }
    .hp-clear:hover { color:var(--hp-danger); border-color:var(--hp-danger); background:var(--hp-danger-soft); }
    .hp-toggle { display:inline-flex; gap:3px; border:1.5px solid var(--hp-border); border-radius:14px; padding:3px; background:var(--hp-soft); margin-left:auto; }
    .hp-toggle button { border:0; background:transparent; color:var(--hp-muted); width:38px; height:36px; border-radius:10px; }
    .hp-toggle button:hover { color:var(--hp-text); }
    .hp-toggle button[aria-pressed="true"] { background:var(--hp-accent); color:var(--hp-on-accent); }

    .hp-status { padding:12px 20px 0; font-size:.85rem; font-weight:700; color:var(--hp-muted); min-height:30px; }
    .hp-status strong { color:var(--hp-text); }

    /* ---------- Table ---------- */
    #hp-results { transition:opacity .15s; }
    .card.is-loading #hp-results { opacity:.55; }
    .households-page .table {
        --bs-table-bg:transparent; --bs-table-color:var(--hp-text); --bs-table-border-color:var(--hp-border);
        --bs-table-hover-bg:var(--hp-soft); --bs-table-hover-color:var(--hp-text); margin:0; color:var(--hp-text);
    }
    .households-page .table thead th {
        background:var(--hp-soft); color:var(--hp-muted); font-size:.75rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
        border-bottom:1px solid var(--hp-border); padding:13px 18px; white-space:nowrap;
    }
    .households-page .table tbody td { padding:15px 18px; vertical-align:middle; border-color:var(--hp-border); color:var(--hp-text); font-weight:500; }
    .households-page .table-hover > tbody > tr:hover > * { background:var(--hp-soft); color:var(--hp-text); }

    /* purok tints (accessible in both themes) */
    .pk-0{--pk-bg:#fdeaec;--pk-fg:#b4232f} .pk-1{--pk-bg:#e6f4e4;--pk-fg:#256f2f} .pk-2{--pk-bg:#fff1d6;--pk-fg:#8a5200}
    .pk-3{--pk-bg:#fde6f0;--pk-fg:#a3124f} .pk-4{--pk-bg:#e3f1f3;--pk-fg:#0b6a79} .pk-5{--pk-bg:#efe8fb;--pk-fg:#5a34b3}
    html[data-theme="dark"] .pk-0{--pk-bg:#43202a;--pk-fg:#ffb3ba} html[data-theme="dark"] .pk-1{--pk-bg:#1f3a26;--pk-fg:#8fe39a}
    html[data-theme="dark"] .pk-2{--pk-bg:#3a2f1a;--pk-fg:#ffd98a} html[data-theme="dark"] .pk-3{--pk-bg:#43202f;--pk-fg:#ffb3d0}
    html[data-theme="dark"] .pk-4{--pk-bg:#1b3438;--pk-fg:#8fdbe8} html[data-theme="dark"] .pk-5{--pk-bg:#2c2542;--pk-fg:#cdb9ff}

    .hp-home { display:flex; align-items:center; gap:13px; min-width:0; }
    .hp-tile { width:44px; height:44px; flex:0 0 44px; border-radius:14px; display:grid; place-items:center; background:var(--pk-bg); color:var(--pk-fg); font-size:1.15rem; }
    .hp-number { font-weight:800; color:var(--hp-text); text-decoration:none; font-variant-numeric:tabular-nums; }
    .hp-number:hover { color:var(--hp-accent); text-decoration:underline; }
    .hp-head { font-size:.82rem; color:var(--hp-muted); font-weight:600; }
    .hp-purok { display:inline-block; padding:4px 11px; border-radius:9px; background:var(--pk-bg); color:var(--pk-fg); font-size:.8rem; font-weight:800; }
    .hp-count { display:inline-flex; align-items:center; gap:6px; font-weight:800; font-variant-numeric:tabular-nums; }
    .hp-count i { color:var(--hp-accent); }
    .households-page mark { background:var(--hp-accent-soft); color:var(--hp-accent); padding:0 2px; border-radius:4px; font-weight:800; }

    .hp-pill { display:inline-flex; align-items:center; gap:7px; padding:5px 12px; border-radius:999px; font-size:.8rem; font-weight:800; }
    .hp-pill::before { content:""; width:7px; height:7px; border-radius:50%; background:currentColor; }
    .hp-pill.ok  { background:var(--hp-green-soft); color:var(--hp-green); }
    .hp-pill.off { background:var(--hp-off-soft);   color:var(--hp-off); }

    .hp-actions { display:inline-flex; gap:4px; }
    .hp-act { width:36px; height:36px; display:inline-grid; place-items:center; border-radius:10px; border:1.5px solid transparent; background:transparent; color:var(--hp-muted); text-decoration:none; cursor:pointer; }
    .hp-act:hover, .hp-act:focus-visible { background:var(--hp-soft-2); border-color:var(--hp-border); color:var(--hp-accent); }
    .hp-act.ok:hover, .hp-act.ok:focus-visible { color:var(--hp-green); background:var(--hp-green-soft); border-color:transparent; }
    .hp-act.danger:hover, .hp-act.danger:focus-visible { color:var(--hp-danger); background:var(--hp-danger-soft); border-color:transparent; }

    /* ---------- Card view ---------- */
    .hh-table.is-grid thead { display:none; }
    .hh-table.is-grid, .hh-table.is-grid tbody { display:block; }
    .hh-table.is-grid tbody { display:grid; grid-template-columns:repeat(auto-fill, minmax(290px, 1fr)); gap:16px; padding:18px; }
    .hh-table.is-grid tr { display:block; position:relative; padding:18px; border:1.5px solid var(--hp-border); border-radius:18px; background:var(--hp-bg); transition:transform .15s,border-color .15s; }
    .hh-table.is-grid tr:hover { transform:translateY(-3px); border-color:var(--hp-accent-2); }
    .hh-table.is-grid tr:hover > * { background:transparent !important; }
    .hh-table.is-grid td { display:block; padding:3px 0; border:0; background:transparent !important; text-align:left !important; }
    .hh-table.is-grid td.c-num { display:none; }
    .hh-table.is-grid td.c-meta::before { content:attr(data-label) ": "; color:var(--hp-muted); font-weight:700; }
    .hh-table.is-grid td.c-status { position:absolute; top:18px; right:18px; width:auto; padding:0; }
    .hh-table.is-grid td.c-actions { margin-top:12px; padding-top:12px; border-top:1px solid var(--hp-border); }
    .hh-table.is-grid .hp-home { margin-bottom:10px; padding-right:86px; }

    /* ---------- Empty state ---------- */
    .hp-empty { display:flex; flex-direction:column; align-items:center; text-align:center; padding:56px 20px 60px; }
    /* only the big house icon is large; icons inside buttons are NOT affected */
    .hp-empty-icon {
        width:84px; height:84px; margin-bottom:16px; border-radius:50%;
        display:grid; place-items:center;
        background:var(--hp-accent-soft); color:var(--hp-accent); font-size:2.4rem;
    }
    .hp-empty h5 { margin:0 0 6px; font-weight:800; font-size:1.15rem; color:var(--hp-text); }
    .hp-empty p { max-width:420px; margin:0 0 20px; color:var(--hp-muted); font-size:.95rem; line-height:1.55; }
    .hp-empty-actions { display:flex; justify-content:center; align-items:center; gap:12px; flex-wrap:wrap; }

    /* ---------- Buttons ---------- */
    .households-page .btn {
        display:inline-flex; align-items:center; justify-content:center; gap:8px;
        min-height:46px; padding:.55rem 1.3rem; border-radius:14px; font-weight:800; font-size:.95rem; line-height:1.2;
        text-decoration:none; cursor:pointer; white-space:nowrap;
        transition:transform .15s, box-shadow .15s, background-color .15s, border-color .15s, color .15s;
    }
    .households-page .btn i { font-size:1.05em; line-height:1; margin:0 !important; color:inherit; }
    .households-page .btn:active { transform:translateY(0) scale(.98); }
    .households-page .btn-primary { background:var(--hp-accent); border:1.5px solid var(--hp-accent); color:var(--hp-on-accent); box-shadow:0 8px 18px rgba(214,62,76,.25); }
    html[data-theme="dark"] .households-page .btn-primary { box-shadow:none; }
    .households-page .btn-primary:hover, .households-page .btn-primary:focus-visible { background:var(--hp-accent-2); border-color:var(--hp-accent-2); color:var(--hp-on-accent); transform:translateY(-2px); }
    .households-page .btn-outline-secondary { color:var(--hp-text); border:1.5px solid var(--hp-border); background:var(--hp-bg); }
    .households-page .btn-outline-secondary:hover, .households-page .btn-outline-secondary:focus-visible { background:var(--hp-soft-2); color:var(--hp-text); border-color:var(--hp-accent-2); transform:translateY(-2px); }

    /* ---------- Footer / pagination ---------- */
    .hp-foot { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:16px 20px; border-top:1px solid var(--hp-border); font-size:.875rem; font-weight:600; color:var(--hp-muted); }
    .hp-foot strong { color:var(--hp-text); }
    .households-page .pagination { margin:0; gap:4px; }
    .households-page .page-link { min-width:38px; min-height:38px; display:grid; place-items:center; background:var(--hp-bg); color:var(--hp-text); border:1.5px solid var(--hp-border); border-radius:10px !important; font-weight:700; }
    .households-page .page-link:hover { background:var(--hp-soft-2); color:var(--hp-accent); border-color:var(--hp-accent-2); }
    .households-page .page-item.active .page-link { background:var(--hp-accent); border-color:var(--hp-accent); color:var(--hp-on-accent); }
    .households-page .page-item.disabled .page-link { background:var(--hp-soft); color:var(--hp-muted); opacity:.6; }

    @media (max-width:767.98px) {
        .hp-hero { padding:22px; }
        .hp-hero-actions { width:100%; }
        .hp-toggle, .hp-search kbd { display:none; }
        .hp-members { display:none; }
        .households-page .form-select { flex:1 1 45%; }
        .hp-empty-actions .btn { width:100%; }
    }
    @media (prefers-reduced-motion:reduce) { .households-page *, .hp-loadbar::after { transition:none !important; animation:none !important; } }
    @media print { .hp-tools, .hp-tabs, .hp-actions, .hp-hero-actions { display:none !important; } }
</style>
@endpush


@section('content')

@php
    // Role-based permissions: Captain and Secretary can manage households.
    $currentUser         = Auth::user();
    $isCaptain           = $currentUser && $currentUser->role === 'captain';
    $isSecretary         = $currentUser && $currentUser->role === 'secretary';
    $canManageHouseholds = $isCaptain || $isSecretary;

    $purokList = ['Purok 1', 'Purok 2', 'Purok 3', 'Purok 4', 'Purok 5', 'Purok 6', 'Purok 7'];

    // Counts across ALL households (not just the current page).
    $counts = \App\Models\Household::selectRaw("
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'Archived' THEN 1 ELSE 0 END) as archived
    ")->first();

    $tabs = [
        ''         => ['All households', (int) $counts->total],
        'Active'   => ['Active',         (int) $counts->active],
        'Archived' => ['Archived',       (int) $counts->archived],
    ];

    $currentStatus = (string) request('status', '');
    $hasFilters    = request()->filled('search') || request()->filled('purok') || $currentStatus !== '';
@endphp

<div class="container-fluid households-page">

    {{-- HEADER --}}
    <header class="hp-hero">
        <div class="hp-hero-bg" aria-hidden="true"></div>

        <div class="hp-hero-in">
            <div class="hp-crumb">e-Poblacion / Households</div>
            <h1>Household Management</h1>
            <p>Manage household records, members, status, and masterlists of Barangay Poblacion.</p>
        </div>

        <div class="hp-hero-in hp-hero-actions">
            <div class="dropdown">
                <button class="hp-btn-ghost dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-printer"></i> Print Masterlist
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('households.masterlist') }}">
                            <i class="bi bi-printer me-2"></i>All Puroks
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li><h6 class="dropdown-header">Print by Purok</h6></li>
                    @foreach($purokList as $purok)
                        <li>
                            <a class="dropdown-item" href="{{ route('households.masterlist', ['purok' => $purok]) }}">
                                <i class="bi bi-geo-alt me-2"></i>{{ $purok }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if($canManageHouseholds)
                <a href="{{ route('households.create') }}" class="hp-btn-solid">
                    <i class="bi bi-plus-lg"></i> Add Household
                </a>
            @endif
        </div>
    </header>

    @if(session('error'))
        <div class="hp-alert" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card" id="hp-card">
        <div class="hp-loadbar" aria-hidden="true"></div>

        {{-- STATUS TABS (replaced live after every search) --}}
        <div id="hp-tabs-wrap">
            <nav class="hp-tabs" aria-label="Filter by status">
                @foreach($tabs as $value => $tab)
                    @php
                        $query = request()->except('page', 'status');
                        if ($value !== '') { $query['status'] = $value; }
                    @endphp
                    <a href="{{ route('households.index', $query) }}"
                       class="hp-tab {{ $currentStatus === (string) $value ? 'is-active' : '' }}">
                        {{ $tab[0] }} <b>{{ number_format($tab[1]) }}</b>
                    </a>
                @endforeach

                <div class="hp-members">
                    <i class="bi bi-people-fill me-1"></i><strong>{{ number_format($totalMembers ?? 0) }}</strong> household members
                </div>
            </nav>
        </div>

        {{-- LIVE SEARCH + FILTERS (no reload, no button) --}}
        <form action="{{ route('households.index') }}" method="GET" id="householdFilterForm" class="hp-tools" role="search" autocomplete="off">
            @if($currentStatus !== '')
                <input type="hidden" name="status" id="hp-status-input" value="{{ $currentStatus }}">
            @else
                <input type="hidden" name="status" id="hp-status-input" value="" disabled>
            @endif

            <div class="hp-search {{ request('search') ? 'has-text' : '' }}" id="hp-search-box">
                <i class="bi bi-search lead"></i>
                <input type="search" name="search" id="search" class="form-control"
                       value="{{ request('search') }}" autocomplete="off"
                       placeholder="Household ID, address, purok, or head" aria-label="Search households">
                <kbd>/</kbd>
                <button type="button" class="hp-x" id="hp-x" aria-label="Clear search"><i class="bi bi-x-lg"></i></button>
            </div>

            <select name="purok" id="purok" class="form-select" aria-label="Filter by purok">
                <option value="">All Puroks</option>
                @foreach($purokList as $purok)
                    <option value="{{ $purok }}" {{ request('purok') == $purok ? 'selected' : '' }}>{{ $purok }}</option>
                @endforeach
            </select>

            <button type="button" class="hp-clear {{ $hasFilters ? 'show' : '' }}" id="hp-clear">
                <i class="bi bi-x-circle"></i> Clear filters
            </button>

            {{-- Only shown if JavaScript is turned off --}}
            <noscript><button type="submit" class="btn btn-primary">Filter</button></noscript>

            <div class="hp-toggle" role="group" aria-label="Change view">
                <button type="button" data-view="table" title="Table view" aria-pressed="true"><i class="bi bi-list-ul"></i></button>
                <button type="button" data-view="grid" title="Card view" aria-pressed="false"><i class="bi bi-grid-3x3-gap-fill"></i></button>
            </div>
        </form>

        <div class="hp-status" id="hp-live" role="status" aria-live="polite">
            @if($households->count() > 0)
                Showing <strong>{{ number_format($households->total()) }}</strong>
                {{ \Illuminate\Support\Str::plural('household', $households->total()) }}
                @if($hasFilters) matching your filters @endif
            @endif
        </div>

        {{-- RESULTS (replaced live) --}}
        <div id="hp-results">

            @if($households->count() > 0)

                <div class="table-responsive">
                    <table class="table table-hover align-middle hh-table" id="hh-table">
                        <thead>
                            <tr>
                                <th class="text-center" style="width:56px">#</th>
                                <th>Household</th>
                                <th>Purok</th>
                                <th>Address</th>
                                <th class="text-center">Members</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                        @foreach($households as $household)
                            @php
                                $purokIdx = ($household->purok && preg_match('/\d+/', $household->purok, $pm)) ? ((int) $pm[0]) % 6 : 0;
                                $headName = $household->householdHead ? $household->householdHead->full_name : null;
                            @endphp
                            <tr>
                                <td class="c-num text-center text-muted">{{ $households->firstItem() + $loop->index }}</td>

                                <td>
                                    <div class="hp-home">
                                        <div class="hp-tile pk-{{ $purokIdx }}" aria-hidden="true"><i class="bi bi-house-door-fill"></i></div>
                                        <div class="text-truncate">
                                            <a href="{{ route('households.show', $household) }}" class="hp-number" data-hl>{{ $household->household_number }}</a>
                                            <div class="hp-head">
                                                @if($headName)Head: <span data-hl>{{ $headName }}</span>@else<span class="fst-italic">No household head set</span>@endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <td class="c-meta pk-{{ $purokIdx }}" data-label="Purok">
                                    @if($household->purok)
                                        <span class="hp-purok" data-hl>{{ $household->purok }}</span>
                                    @else
                                        <span class="text-muted">Not specified</span>
                                    @endif
                                </td>

                                <td class="c-meta" data-label="Address">
                                    @if($household->address)<span data-hl>{{ $household->address }}</span>@else Not specified @endif
                                </td>

                                <td class="c-meta text-center" data-label="Members">
                                    <span class="hp-count"><i class="bi bi-people"></i>{{ $household->members->count() }}</span>
                                </td>

                                <td class="c-status text-center">
                                    @if($household->status === 'Active')
                                        <span class="hp-pill ok">Active</span>
                                    @else
                                        <span class="hp-pill off">Archived</span>
                                    @endif
                                </td>

                                <td class="c-actions text-center">
                                    <div class="hp-actions">

                                        <a href="{{ route('households.show', $household) }}" class="hp-act" title="View Household" aria-label="View household {{ $household->household_number }}">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        @if($canManageHouseholds)

                                            @if($household->status === 'Active')
                                                <a href="{{ route('households.edit', $household) }}" class="hp-act" title="Edit Household" aria-label="Edit household {{ $household->household_number }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>

                                                <form action="{{ route('households.archive', $household) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to archive this household?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="hp-act" title="Archive Household" aria-label="Archive household {{ $household->household_number }}">
                                                        <i class="bi bi-archive"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($household->status === 'Archived')
                                                <form action="{{ route('households.restore', $household) }}" method="POST" class="d-inline"
                                                      onsubmit="return confirm('Restore this household?');">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit" class="hp-act ok" title="Restore Household" aria-label="Restore household {{ $household->household_number }}">
                                                        <i class="bi bi-arrow-counterclockwise"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('households.destroy', $household) }}" method="POST" class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to permanently delete this household? This action cannot be undone.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="hp-act danger" title="Delete Household" aria-label="Delete household {{ $household->household_number }}">
                                                    <i class="bi bi-trash"></i>
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

                <div class="hp-foot">
                    <span>
                        Showing <strong>{{ $households->firstItem() }}</strong>–<strong>{{ $households->lastItem() }}</strong>
                        of <strong>{{ $households->total() }}</strong> households
                    </span>
                    @if($households->hasPages())
                        {{ $households->appends(request()->query())->links() }}
                    @endif
                </div>

            @else

                <div class="hp-empty">
                    <div class="hp-empty-icon" aria-hidden="true"><i class="bi bi-houses"></i></div>
                    <h5>{{ $hasFilters ? 'No households match your search' : 'No households yet' }}</h5>
                    <p>
                        {{ $hasFilters ? 'Try a different search or clear the filters.' : 'Add the first household record to get started.' }}
                    </p>
                    <div class="hp-empty-actions">
                        @if($hasFilters)
                            <a href="{{ route('households.index') }}" class="btn btn-outline-secondary js-clear">
                                <i class="bi bi-arrow-counterclockwise"></i> Clear filters
                            </a>
                        @endif
                        @if($canManageHouseholds)
                            <a href="{{ route('households.create') }}" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> Add Household
                            </a>
                        @endif
                    </div>
                </div>

            @endif

        </div>
    </div>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    var card     = document.getElementById('hp-card');
    var form     = document.getElementById('householdFilterForm');
    var search   = document.getElementById('search');
    var box      = document.getElementById('hp-search-box');
    var xbtn     = document.getElementById('hp-x');
    var purok    = document.getElementById('purok');
    var statusIn = document.getElementById('hp-status-input');
    var clearBtn = document.getElementById('hp-clear');
    var tabsWrap = document.getElementById('hp-tabs-wrap');
    var results  = document.getElementById('hp-results');
    var live     = document.getElementById('hp-live');
    var toggles  = document.querySelectorAll('.hp-toggle button');
    var mq       = window.matchMedia('(max-width: 767.98px)');
    if (!form || !card) { return; }

    var base = form.getAttribute('action');
    var timer = null, controller = null, page = '';

    /* ---------- table / card view ---------- */
    function savedView() { try { return localStorage.getItem('household_view') || 'table'; } catch (e) { return 'table'; } }
    function applyView() {
        var table = document.getElementById('hh-table');
        if (!table) { return; }
        var view = mq.matches ? 'grid' : savedView();
        table.classList.toggle('is-grid', view === 'grid');
        toggles.forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.view === view ? 'true' : 'false'); });
    }
    toggles.forEach(function (b) {
        b.addEventListener('click', function () {
            try { localStorage.setItem('household_view', b.dataset.view); } catch (e) {}
            applyView();
        });
    });
    if (mq.addEventListener) { mq.addEventListener('change', applyView); }

    /* ---------- helpers ---------- */
    function hasFilters() {
        return search.value.trim() !== '' || purok.value !== '' || (statusIn && !statusIn.disabled && statusIn.value !== '');
    }
    function syncUi() {
        box.classList.toggle('has-text', search.value !== '');
        clearBtn.classList.toggle('show', hasFilters());
    }
    function setStatus(value) {
        if (!statusIn) { return; }
        statusIn.value = value || '';
        statusIn.disabled = !value;
    }
    function buildUrl() {
        var p = new URLSearchParams();
        if (search.value.trim() !== '') { p.set('search', search.value.trim()); }
        if (purok.value !== '') { p.set('purok', purok.value); }
        if (statusIn && !statusIn.disabled && statusIn.value !== '') { p.set('status', statusIn.value); }
        if (page) { p.set('page', page); }
        var qs = p.toString();
        return base + (qs ? '?' + qs : '');
    }

    /* highlight searched words (safe: text nodes only) */
    function highlight() {
        var term = search.value.trim();
        if (!term) { return; }
        var words = term.split(/\s+/).filter(Boolean).map(function (w) { return w.toLowerCase(); });
        results.querySelectorAll('[data-hl]').forEach(function (el) {
            var text = el.textContent, lower = text.toLowerCase(), out = [], i = 0, found = false;
            while (i < text.length) {
                var hit = null;
                for (var w = 0; w < words.length; w++) {
                    if (lower.substr(i, words[w].length) === words[w] && (!hit || words[w].length > hit.length)) { hit = words[w]; }
                }
                if (hit) {
                    var m = document.createElement('mark');
                    m.textContent = text.substr(i, hit.length);
                    out.push(m); i += hit.length; found = true;
                } else {
                    var j = i + 1;
                    while (j < text.length) {
                        var starts = words.some(function (x) { return lower.substr(j, x.length) === x; });
                        if (starts) { break; }
                        j++;
                    }
                    out.push(document.createTextNode(text.substring(i, j)));
                    i = j;
                }
            }
            if (!found) { return; }
            el.textContent = '';
            out.forEach(function (n) { el.appendChild(n); });
        });
    }

    /* ---------- live load (server-side, so results are always accurate) ---------- */
    function load(url) {
        if (controller) { controller.abort(); }
        controller = ('AbortController' in window) ? new AbortController() : null;
        card.classList.add('is-loading');
        syncUi();

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            credentials: 'same-origin',
            signal: controller ? controller.signal : undefined
        })
        .then(function (r) {
            if (r.redirected && r.url.indexOf('login') !== -1) { window.location.href = r.url; throw new Error('redirect'); }
            if (!r.ok) { throw new Error('http ' + r.status); }
            return r.text();
        })
        .then(function (html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var newTabs = doc.getElementById('hp-tabs-wrap');
            var newRes  = doc.getElementById('hp-results');
            var newLive = doc.getElementById('hp-live');
            if (!newRes) { throw new Error('bad response'); }
            if (newTabs) { tabsWrap.innerHTML = newTabs.innerHTML; }
            results.innerHTML = newRes.innerHTML;
            if (newLive) { live.innerHTML = newLive.innerHTML; }
            history.replaceState(null, '', url);
            applyView();
            highlight();
        })
        .catch(function (err) {
            if (err && (err.name === 'AbortError' || err.message === 'redirect')) { return; }
            window.location.href = url;   // safe fallback: normal page load
        })
        .then(function () {
            if (!controller || !controller.signal.aborted) { card.classList.remove('is-loading'); }
        });
    }

    function run(delay) {
        clearTimeout(timer);
        syncUi();
        timer = setTimeout(function () { load(buildUrl()); }, delay);
    }

    /* typing → search automatically (waits 300 ms after the last key) */
    search.addEventListener('input', function () { page = ''; run(300); });
    /* choosing a purok → filters immediately */
    purok.addEventListener('change', function () { page = ''; run(0); });
    /* Enter never reloads the page */
    form.addEventListener('submit', function (e) { e.preventDefault(); page = ''; run(0); });

    function clearAll() {
        search.value = ''; purok.value = ''; setStatus(''); page = '';
        syncUi(); run(0); search.focus();
    }
    xbtn.addEventListener('click', function () { search.value = ''; page = ''; run(0); search.focus(); });
    clearBtn.addEventListener('click', clearAll);

    /* status tabs, pagination, and "clear filters" in the empty state: no full reload */
    document.addEventListener('click', function (e) {
        var clearLink = e.target.closest('.js-clear');
        if (clearLink && results.contains(clearLink)) { e.preventDefault(); clearAll(); return; }

        var tab = e.target.closest('.hp-tab');
        if (tab && tabsWrap.contains(tab)) {
            e.preventDefault();
            var u = new URL(tab.href, window.location.origin);
            setStatus(u.searchParams.get('status') || '');
            page = '';
            run(0);
            return;
        }

        var pg = e.target.closest('.pagination a');
        if (pg && results.contains(pg)) {
            e.preventDefault();
            var pu = new URL(pg.href, window.location.origin);
            page = pu.searchParams.get('page') || '';
            run(0);
            card.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    /* keyboard: "/" focuses search, Esc clears it */
    document.addEventListener('keydown', function (e) {
        var tag = (document.activeElement.tagName || '').toLowerCase();
        if (e.key === '/' && !/^(input|textarea|select)$/.test(tag)) { e.preventDefault(); search.focus(); }
        if (e.key === 'Escape' && document.activeElement === search && search.value !== '') {
            search.value = ''; page = ''; run(0);
        }
    });

    window.addEventListener('pageshow', syncUi);
    syncUi();
    applyView();
    highlight();
});
</script>
@endpush