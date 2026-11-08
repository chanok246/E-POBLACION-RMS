@extends('layouts.app')

@section('title', 'Residents | e-Poblacion')

@push('styles')
<style>
    /* =========================================================
       Residents list — modern, theme-balanced, live search
       SEMANTIC COLORS
         Green  = primary action / success / selected
         Blue   = info
         Red    = danger / destructive only
         Gray   = archived / inactive
       Pink + green hero = brand decoration only.
       ========================================================= */
    .residents-page {
        --rp-bg:#ffffff;
        --rp-soft:#f7f2f3;
        --rp-soft-2:#efe7e9;
        --rp-text:#2b2023;
        --rp-muted:#5f4f54;
        --rp-border:#e3d6d9;
        --rp-accent:#237a35;
        --rp-accent-2:#2f9e44;
        --rp-on-accent:#ffffff;
        --rp-accent-soft:#e6f4e4;
        --rp-green:#237a35;
        --rp-green-soft:#e6f4e4;
        --rp-info:#1c7ed6;
        --rp-info-soft:#e3effb;
        --rp-off:#5f4f54;
        --rp-off-soft:#ece4e6;
        --rp-danger:#c92a3b;
        --rp-danger-soft:#fdeaec;
        --rp-shadow:0 1px 2px rgba(60,20,30,.05),0 14px 30px rgba(60,20,30,.07);
        --rp-select-arrow:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%232b2023' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
        color:var(--rp-text);
    }
    html[data-theme="dark"] .residents-page {
        --rp-bg:#1a2a21;
        --rp-soft:#14211a;
        --rp-soft-2:#223529;
        --rp-text:#f6eff1;
        --rp-muted:#c3d0c7;
        --rp-border:#34503d;
        --rp-accent:#5fc062;
        --rp-accent-2:#8fe29a;
        --rp-on-accent:#0b2314;
        --rp-accent-soft:#1f3a26;
        --rp-green:#7fdc8a;
        --rp-green-soft:#1f3a26;
        --rp-info:#6cb2ff;
        --rp-info-soft:#1b2f45;
        --rp-off:#c3d0c7;
        --rp-off-soft:#223529;
        --rp-danger:#ff7b87;
        --rp-danger-soft:#43202a;
        --rp-shadow:none;
        --rp-select-arrow:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23f6eff1' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
    }
    .residents-page, .residents-page * { box-sizing:border-box; }
    .residents-page :focus-visible { outline:3px solid var(--rp-info); outline-offset:2px; }
    .residents-page .text-muted { color:var(--rp-muted) !important; }

    /* ---------- Hero (brand decoration) ---------- */
    .rp-hero {
        position:relative; overflow:hidden; border-radius:24px; padding:26px 28px; margin-bottom:22px; color:#fff;
        background:linear-gradient(120deg,#d63e4c 0%,#ef6b78 60%,#f59aa2 100%);
        box-shadow:0 16px 32px rgba(214,62,76,.25);
        display:flex; align-items:center; justify-content:space-between; gap:18px; flex-wrap:wrap;
    }
    html[data-theme="dark"] .rp-hero { background:linear-gradient(120deg,#8f2f40,#b84557); box-shadow:none; }
    .rp-hero::before { content:''; position:absolute; width:240px; height:240px; right:-60px; top:-110px; border-radius:50%; background:rgba(255,255,255,.13); pointer-events:none; }
    .rp-hero::after  { content:''; position:absolute; width:150px; height:150px; right:170px; bottom:-100px; border-radius:50%; background:rgba(105,187,104,.38); pointer-events:none; }
    .rp-hero > * { position:relative; z-index:1; }
    .rp-crumb { font-size:12px; font-weight:800; letter-spacing:.12em; text-transform:uppercase; color:#fff; opacity:.92; margin-bottom:6px; }
    .rp-hero h1 { margin:0; font-size:clamp(1.6rem,3vw,2.1rem); font-weight:800; letter-spacing:-.02em; color:#fff; }
    .rp-hero p { margin:6px 0 0; font-size:.95rem; color:#fff; opacity:.96; }
    /* Add button = primary action = green text (red is reserved for danger) */
    .rp-addbtn {
        display:inline-flex; align-items:center; gap:8px; min-height:46px; padding:.6rem 1.3rem; border-radius:14px;
        background:#fff; color:#1d6a2d !important; font-weight:800; text-decoration:none; border:0;
        box-shadow:0 8px 18px rgba(60,10,20,.2); transition:transform .15s, box-shadow .15s;
    }
    .rp-addbtn:hover { transform:translateY(-2px); box-shadow:0 12px 22px rgba(60,10,20,.26); }
    html[data-theme="dark"] .rp-addbtn { background:#f1fbf2; }

    /* ---------- Card ---------- */
    .residents-page .card {
        position:relative; background:var(--rp-bg); color:var(--rp-text);
        border:1px solid var(--rp-border) !important; border-radius:22px; box-shadow:var(--rp-shadow); overflow:hidden;
    }
    .rp-loadbar { position:absolute; left:0; top:0; height:3px; width:100%; z-index:6; overflow:hidden; opacity:0; transition:opacity .15s; pointer-events:none; }
    .rp-loadbar::after { content:''; position:absolute; inset:0; width:40%; background:linear-gradient(90deg,var(--rp-accent),var(--rp-info)); animation:rpslide 1s infinite ease-in-out; }
    .card.is-loading .rp-loadbar { opacity:1; }
    @keyframes rpslide { 0% { transform:translateX(-100%); } 100% { transform:translateX(260%); } }

    /* ---------- Tabs ---------- */
    .rp-tabs { display:flex; gap:8px; padding:16px 18px 0; overflow-x:auto; }
    .rp-tab {
        display:inline-flex; align-items:center; gap:10px; padding:10px 16px; margin-bottom:14px; white-space:nowrap;
        border-radius:999px; text-decoration:none; font-weight:700; font-size:.9rem;
        color:var(--rp-muted); background:var(--rp-soft); border:1.5px solid var(--rp-border);
        transition:background .15s,color .15s,border-color .15s;
    }
    .rp-tab b { font-size:.85rem; font-weight:800; padding:2px 9px; border-radius:99px; background:var(--rp-bg); color:var(--rp-text); font-variant-numeric:tabular-nums; }
    .rp-tab:hover { color:var(--rp-text); border-color:var(--rp-accent-2); }
    .rp-tab.is-active { background:var(--rp-accent); border-color:var(--rp-accent); color:var(--rp-on-accent); }
    .rp-tab.is-active b { background:rgba(255,255,255,.28); color:var(--rp-on-accent); }
    html[data-theme="dark"] .rp-tab.is-active b { background:rgba(11,35,20,.22); }

    /* ---------- Toolbar ---------- */
    .rp-tools { display:flex; gap:10px; flex-wrap:wrap; padding:0 18px 16px; align-items:center; border-bottom:1px solid var(--rp-border); }
    .rp-search { position:relative; flex:1 1 300px; }
    .rp-search > i.lead { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--rp-muted); pointer-events:none; }
    .rp-search kbd { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:var(--rp-bg); color:var(--rp-muted); border:1px solid var(--rp-border); font-size:.75rem; font-weight:700; padding:1px 8px; border-radius:6px; }
    .rp-search.has-text kbd { display:none; }
    .rp-x { position:absolute; right:8px; top:50%; transform:translateY(-50%); width:30px; height:30px; display:none; place-items:center; border:0; border-radius:8px; background:var(--rp-soft-2); color:var(--rp-text); cursor:pointer; }
    .rp-search.has-text .rp-x { display:grid; }
    .rp-x:hover { background:var(--rp-soft); color:var(--rp-accent); }

    .residents-page .form-control, .residents-page .form-select {
        min-height:46px; background-color:var(--rp-soft); color:var(--rp-text);
        border:1.5px solid var(--rp-border); border-radius:14px; font-size:.95rem; font-weight:600;
        transition:border-color .15s, box-shadow .15s, background-color .15s;
    }
    .residents-page .rp-search .form-control { padding-left:42px; padding-right:48px; }
    .residents-page .form-control::placeholder { color:var(--rp-muted); opacity:.8; font-weight:500; }
    .residents-page .form-control:hover, .residents-page .form-select:hover { border-color:var(--rp-accent-2); }
    .residents-page .form-control:focus, .residents-page .form-select:focus {
        background-color:var(--rp-bg); color:var(--rp-text); border-color:var(--rp-accent); box-shadow:0 0 0 .22rem var(--rp-accent-soft);
    }
    .residents-page .form-select {
        appearance:none; -webkit-appearance:none; flex:0 0 160px;
        background-image:var(--rp-select-arrow); background-repeat:no-repeat; background-position:right .9rem center; background-size:16px; padding-right:2.4rem;
    }
    .residents-page .form-select option { background:var(--rp-bg); color:var(--rp-text); }
    .residents-page input[type="search"]::-webkit-search-cancel-button { display:none; }

    /* "Clear filters" is a neutral reset, not a destructive action */
    .rp-clear { display:none; align-items:center; gap:6px; min-height:46px; padding:0 14px; border:1.5px dashed var(--rp-border); border-radius:14px; background:transparent; color:var(--rp-muted); font-size:.875rem; font-weight:700; cursor:pointer; }
    .rp-clear.show { display:inline-flex; }
    .rp-clear:hover { color:var(--rp-accent); border-color:var(--rp-accent); background:var(--rp-accent-soft); }
    .rp-toggle { display:inline-flex; gap:3px; border:1.5px solid var(--rp-border); border-radius:14px; padding:3px; background:var(--rp-soft); margin-left:auto; }
    .rp-toggle button { border:0; background:transparent; color:var(--rp-muted); width:38px; height:36px; border-radius:10px; }
    .rp-toggle button:hover { color:var(--rp-text); }
    .rp-toggle button[aria-pressed="true"] { background:var(--rp-accent); color:var(--rp-on-accent); }

    /* ---------- Quick status filters ---------- */
    .rp-chips { display:flex; gap:8px; flex-wrap:wrap; align-items:center; padding:12px 18px 0; }
    .rp-chips-label { font-size:.8rem; font-weight:800; color:var(--rp-muted); margin-right:2px; }
    .rp-chip {
        display:inline-flex; align-items:center; min-height:34px; padding:0 14px; border-radius:999px; cursor:pointer;
        background:var(--rp-soft); color:var(--rp-text); border:1.5px solid var(--rp-border);
        font-size:.82rem; font-weight:800; transition:background .15s,color .15s,border-color .15s;
    }
    .rp-chip:hover { border-color:var(--rp-accent-2); background:var(--rp-soft-2); }
    .rp-chip.is-active { background:var(--rp-accent); border-color:var(--rp-accent); color:var(--rp-on-accent); }

    .rp-status { padding:12px 20px 0; font-size:.85rem; font-weight:700; color:var(--rp-muted); min-height:30px; }
    .rp-status strong { color:var(--rp-text); }

    /* ---------- Table ---------- */
    #rp-results { transition:opacity .15s; }
    .card.is-loading #rp-results { opacity:.55; }
    .residents-page .table {
        --bs-table-bg:transparent; --bs-table-color:var(--rp-text); --bs-table-border-color:var(--rp-border);
        --bs-table-hover-bg:var(--rp-soft); --bs-table-hover-color:var(--rp-text); margin:0; color:var(--rp-text);
    }
    .residents-page .table thead th {
        background:var(--rp-soft); color:var(--rp-muted); font-size:.75rem; font-weight:800; letter-spacing:.06em; text-transform:uppercase;
        border-bottom:1px solid var(--rp-border); padding:13px 18px; white-space:nowrap;
    }
    .residents-page .table tbody td { padding:15px 18px; vertical-align:middle; border-color:var(--rp-border); color:var(--rp-text); font-weight:500; }
    .residents-page .table-hover > tbody > tr:hover > * { background:var(--rp-soft); color:var(--rp-text); }

    .rp-person { display:flex; align-items:center; gap:13px; min-width:0; }
    .rp-avatar { width:44px; height:44px; flex:0 0 44px; border-radius:14px; display:grid; place-items:center; background:var(--av-bg); color:var(--av-fg); font-weight:800; font-size:.85rem; }
    /* avatar tints are decorative identity colors, not status colors */
    .av-0{--av-bg:#fdeaec;--av-fg:#b4232f} .av-1{--av-bg:#e6f4e4;--av-fg:#256f2f} .av-2{--av-bg:#fff1d6;--av-fg:#8a5200}
    .av-3{--av-bg:#fde6f0;--av-fg:#a3124f} .av-4{--av-bg:#e3f1f3;--av-fg:#0b6a79} .av-5{--av-bg:#efe8fb;--av-fg:#5a34b3}
    html[data-theme="dark"] .av-0{--av-bg:#43202a;--av-fg:#ffb3ba} html[data-theme="dark"] .av-1{--av-bg:#1f3a26;--av-fg:#8fe39a}
    html[data-theme="dark"] .av-2{--av-bg:#3a2f1a;--av-fg:#ffd98a} html[data-theme="dark"] .av-3{--av-bg:#43202f;--av-fg:#ffb3d0}
    html[data-theme="dark"] .av-4{--av-bg:#1b3438;--av-fg:#8fdbe8} html[data-theme="dark"] .av-5{--av-bg:#2c2542;--av-fg:#cdb9ff}
    .rp-name { font-weight:800; color:var(--rp-text); text-decoration:none; }
    .rp-name:hover { color:var(--rp-accent); text-decoration:underline; }
    .rp-name mark { background:var(--rp-accent-soft); color:var(--rp-accent); padding:0 2px; border-radius:4px; font-weight:800; }
    .rp-id { font-size:.8rem; color:var(--rp-muted); font-variant-numeric:tabular-nums; font-weight:600; }
    .rp-tags { display:flex; flex-wrap:wrap; gap:4px; margin-top:5px; }
    /* sector tags (PWD, Senior…) = info */
    .rp-tag { display:inline-block; padding:2px 8px; border-radius:999px; font-size:.7rem; font-weight:800; line-height:1.4; background:var(--rp-info-soft); color:var(--rp-info); border:1px solid transparent; }

    .rp-pill { display:inline-flex; align-items:center; gap:7px; padding:5px 12px; border-radius:999px; font-size:.8rem; font-weight:800; }
    .rp-pill::before { content:""; width:7px; height:7px; border-radius:50%; background:currentColor; }
    .rp-pill.ok  { background:var(--rp-green-soft); color:var(--rp-green); }
    .rp-pill.off { background:var(--rp-off-soft);   color:var(--rp-off); }

    .rp-actions { display:inline-flex; gap:4px; }
    .rp-act { width:36px; height:36px; display:inline-grid; place-items:center; border-radius:10px; border:1.5px solid transparent; background:transparent; color:var(--rp-muted); text-decoration:none; cursor:pointer; }
    .rp-act:hover, .rp-act:focus-visible { background:var(--rp-soft-2); border-color:var(--rp-border); color:var(--rp-accent); }
    .rp-act.danger:hover, .rp-act.danger:focus-visible { color:var(--rp-danger); background:var(--rp-danger-soft); border-color:transparent; }

    /* ---------- Card view ---------- */
    .rec-table.is-grid thead { display:none; }
    .rec-table.is-grid, .rec-table.is-grid tbody { display:block; }
    .rec-table.is-grid tbody { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:16px; padding:18px; }
    .rec-table.is-grid tr { display:block; position:relative; padding:18px; border:1.5px solid var(--rp-border); border-radius:18px; background:var(--rp-bg); transition:transform .15s,border-color .15s; }
    .rec-table.is-grid tr:hover { transform:translateY(-3px); border-color:var(--rp-accent-2); }
    .rec-table.is-grid tr:hover > * { background:transparent !important; }
    .rec-table.is-grid td { display:block; padding:3px 0; border:0; background:transparent !important; }
    .rec-table.is-grid td.c-num { display:none; }
    .rec-table.is-grid td.c-meta::before { content:attr(data-label) ": "; color:var(--rp-muted); font-weight:700; }
    .rec-table.is-grid td.c-status { position:absolute; top:18px; right:18px; width:auto; padding:0; }
    .rec-table.is-grid td.c-actions { margin-top:12px; padding-top:12px; border-top:1px solid var(--rp-border); text-align:left !important; }
    .rec-table.is-grid .rp-person { margin-bottom:10px; padding-right:86px; }
    .rec-table.is-grid tr.is-empty { grid-column:1 / -1; border:0; }
    .rec-table.is-grid tr.is-empty:hover { transform:none; }

    /* ---------- Empty / footer ---------- */
    .rp-empty { text-align:center; padding:60px 16px; }
    .rp-empty i { font-size:2.6rem; color:var(--rp-accent); }
    .rp-empty h5 { margin:12px 0 4px; font-weight:800; color:var(--rp-text); }
    .residents-page .btn { min-height:44px; padding:.5rem 1.15rem; border-radius:12px; font-weight:800; }
    .residents-page .btn-primary { background:var(--rp-accent); border:1.5px solid var(--rp-accent); color:var(--rp-on-accent); }
    .residents-page .btn-primary:hover { background:var(--rp-accent); border-color:var(--rp-accent); color:var(--rp-on-accent); filter:brightness(1.12); }
    .residents-page .btn-outline-secondary { color:var(--rp-text); border:1.5px solid var(--rp-border); background:var(--rp-bg); }
    .residents-page .btn-outline-secondary:hover { background:var(--rp-soft-2); color:var(--rp-text); border-color:var(--rp-accent-2); }
    .rp-foot { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:16px 20px; border-top:1px solid var(--rp-border); font-size:.875rem; font-weight:600; color:var(--rp-muted); }
    .rp-foot strong { color:var(--rp-text); }
    .residents-page .pagination { margin:0; gap:4px; }
    .residents-page .page-link { min-width:38px; min-height:38px; display:grid; place-items:center; background:var(--rp-bg); color:var(--rp-text); border:1.5px solid var(--rp-border); border-radius:10px !important; font-weight:700; }
    .residents-page .page-link:hover { background:var(--rp-soft-2); color:var(--rp-accent); border-color:var(--rp-accent-2); }
    .residents-page .page-item.active .page-link { background:var(--rp-accent); border-color:var(--rp-accent); color:var(--rp-on-accent); }
    .residents-page .page-item.disabled .page-link { background:var(--rp-soft); color:var(--rp-muted); opacity:.6; }

    @media (max-width:767.98px) {
        .rp-hero { padding:22px; }
        .rp-toggle, .rp-search kbd { display:none; }
        .residents-page .form-select { flex:1 1 45%; }
    }
    @media (prefers-reduced-motion:reduce) { .residents-page *, .rp-loadbar::after { transition:none !important; animation:none !important; } }
    @media print { .rp-tools, .rp-tabs, .rp-chips, .rp-actions, .btn, .rp-addbtn { display:none !important; } }
</style>
@endpush


@section('content')

@php
    $counts = \App\Models\Resident::selectRaw("
        COUNT(*) as total,
        SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) as active,
        SUM(CASE WHEN status = 'Archived' THEN 1 ELSE 0 END) as archived
    ")->first();

    $tabs = [
        ''         => ['All residents', (int) $counts->total],
        'Active'   => ['Active',        (int) $counts->active],
        'Archived' => ['Archived',      (int) $counts->archived],
    ];

    $currentStatus = (string) request('status', '');
    $currentClassification = (string) request('classification', '');
    $currentPurok = (string) request('purok', '');
    $currentSort = (string) request('sort', 'newest');

    $hasFilters = request()->filled('search')
        || request()->filled('classification')
        || request()->filled('purok')
        || request()->filled('sex')
        || $currentStatus !== ''
        || ($currentSort !== '' && $currentSort !== 'newest');

    $quickFilters = ['PWD', 'Senior Citizen', 'Solo Parent', 'Student'];
@endphp

<div class="container-fluid residents-page">

    {{-- HEADER --}}
    <header class="rp-hero">
        <div>
            <div class="rp-crumb">e-Poblacion / Management / Residents</div>
            <h1>Resident Records</h1>
            <p>Manage and maintain registered residents of Barangay Poblacion.</p>
        </div>
        <a href="{{ route('residents.create') }}" class="rp-addbtn">
            <i class="bi bi-person-plus-fill"></i> Add Resident
        </a>
    </header>

    <div class="card" id="rp-card">
        <div class="rp-loadbar" aria-hidden="true"></div>

        {{-- STATUS TABS (replaced live after every search) --}}
        <div id="rp-tabs-wrap">
            <nav class="rp-tabs" aria-label="Filter by status">
                @foreach($tabs as $value => $tab)
                    @php
                        $query = request()->except('page', 'status');
                        if ($value !== '') { $query['status'] = $value; }
                    @endphp
                    <a href="{{ route('residents.index', $query) }}"
                       class="rp-tab {{ $currentStatus === (string) $value ? 'is-active' : '' }}">
                        {{ $tab[0] }} <b>{{ number_format($tab[1]) }}</b>
                    </a>
                @endforeach
            </nav>
        </div>

        {{-- LIVE SEARCH + FILTERS (no Filter button needed) --}}
        <form method="GET" action="{{ route('residents.index') }}" class="rp-tools" id="rp-form" role="search" autocomplete="off">
            @if($currentStatus !== '')
                <input type="hidden" name="status" id="rp-status-input" value="{{ $currentStatus }}">
            @else
                <input type="hidden" name="status" id="rp-status-input" value="" disabled>
            @endif

            <input type="hidden" name="classification" id="rp-classification-input" value="{{ $currentClassification }}">

            <div class="rp-search {{ request('search') ? 'has-text' : '' }}" id="rp-search-box">
                <i class="bi bi-search lead"></i>
                <input type="search" id="rp-search" name="search" class="form-control"
                       placeholder="Search resident name…" value="{{ request('search') }}" aria-label="Search residents by name only">
                <kbd>/</kbd>
                <button type="button" class="rp-x" id="rp-x" aria-label="Clear search"><i class="bi bi-x-lg"></i></button>
            </div>

            <select name="purok" id="rp-purok" class="form-select" aria-label="Filter by Purok">
                <option value="">All Puroks</option>
                @foreach($puroks as $purok)
                    <option value="{{ $purok }}" {{ $currentPurok === (string) $purok ? 'selected' : '' }}>{{ $purok }}</option>
                @endforeach
            </select>

            <select name="sex" id="rp-sex" class="form-select" aria-label="Filter by sex">
                <option value="">Any sex</option>
                <option value="Male" {{ request('sex') === 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ request('sex') === 'Female' ? 'selected' : '' }}>Female</option>
            </select>

            <select name="sort" id="rp-sort" class="form-select" aria-label="Sort residents">
                <option value="newest" {{ $currentSort === 'newest' ? 'selected' : '' }}>Newest first</option>
                <option value="oldest" {{ $currentSort === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                <option value="name_asc" {{ $currentSort === 'name_asc' ? 'selected' : '' }}>Name A–Z</option>
                <option value="name_desc" {{ $currentSort === 'name_desc' ? 'selected' : '' }}>Name Z–A</option>
            </select>

            {{-- Only shown if JavaScript is turned off --}}
            <noscript><button type="submit" class="btn btn-primary">Filter</button></noscript>

            <div class="rp-toggle" role="group" aria-label="Change view">
                <button type="button" data-view="table" title="Table view" aria-pressed="true"><i class="bi bi-list-ul"></i></button>
                <button type="button" data-view="grid" title="Card view" aria-pressed="false"><i class="bi bi-grid-3x3-gap-fill"></i></button>
            </div>
        </form>

        {{-- QUICK STATUS FILTERS (fill the search bar) --}}
        <div class="rp-chips" id="rp-chips" role="group" aria-label="Quick status filters">
            <span class="rp-chips-label">Quick filter:</span>
            @foreach($quickFilters as $chip)
                <button type="button" class="rp-chip {{ strtolower($currentClassification) === strtolower($chip) ? 'is-active' : '' }}" data-q="{{ $chip }}">{{ $chip }}</button>
            @endforeach
        </div>

        <div class="rp-status" id="rp-live" role="status" aria-live="polite">
            @if($residents->count())
                Showing <strong>{{ number_format($residents->total()) }}</strong>
                {{ \Illuminate\Support\Str::plural('resident', $residents->total()) }}
                @if($hasFilters) matching your filters @endif
            @endif
        </div>

        {{-- RESULTS (replaced live) --}}
        <div id="rp-results">
            <div class="table-responsive">
                <table class="table table-hover align-middle rec-table" id="rec-table">
                    <thead>
                        <tr>
                            <th style="width:56px">#</th>
                            <th>Resident</th>
                            <th>Sex</th>
                            <th>Birth date</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($residents as $resident)
                        @php
                            $fullName = trim($resident->last_name . ', ' . $resident->first_name
                                . ($resident->middle_name ? ' ' . $resident->middle_name : '')
                                . ($resident->suffix ? ' ' . $resident->suffix : ''));
                            $initials = strtoupper(mb_substr($resident->first_name, 0, 1) . mb_substr($resident->last_name, 0, 1));
                            $birth    = $resident->birth_date ? \Carbon\Carbon::parse($resident->birth_date) : null;
                            $rowTags  = ($statusLabels ?? [])[$resident->id] ?? [];
                        @endphp
                        <tr>
                            <td class="c-num text-muted">{{ $residents->firstItem() + $loop->index }}</td>

                            <td>
                                <div class="rp-person">
                                    <div class="rp-avatar av-{{ $resident->id % 6 }}" aria-hidden="true">{{ $initials }}</div>
                                    <div class="text-truncate">
                                        <span class="rp-name">{{ $fullName }}</span>
                                        <div class="rp-id">ID {{ str_pad($resident->id, 5, '0', STR_PAD_LEFT) }}</div>
                                        @if(count($rowTags))
                                            <div class="rp-tags">
                                                @foreach($rowTags as $tag)
                                                    <span class="rp-tag">{{ $tag }}</span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="c-meta" data-label="Sex">{{ $resident->sex ? $resident->sex : '—' }}</td>

                            <td class="c-meta" data-label="Born">
                                @if($birth)
                                    {{ $birth->format('M d, Y') }} <span class="text-muted">({{ $birth->age }} yrs)</span>
                                @else
                                    —
                                @endif
                            </td>

                            <td class="c-meta" data-label="Contact">{{ $resident->contact_number ? $resident->contact_number : '—' }}</td>

                            <td class="c-status">
                                @if($resident->status === 'Active')
                                    <span class="rp-pill ok">Active</span>
                                @else
                                    <span class="rp-pill off">Archived</span>
                                @endif
                            </td>

                            <td class="c-actions text-end">
                                <div class="rp-actions">
                                    <a href="{{ route('residents.show', $resident->id) }}" class="rp-act" title="View" aria-label="View {{ $fullName }}">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('residents.edit', $resident->id) }}" class="rp-act" title="Edit" aria-label="Edit {{ $fullName }}">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('residents.destroy', $resident->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Delete this resident record? This cannot be undone.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rp-act danger" title="Delete" aria-label="Delete {{ $fullName }}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="is-empty">
                            <td colspan="7">
                                <div class="rp-empty">
                                    <i class="bi bi-people"></i>
                                    <h5>{{ $hasFilters ? 'No residents match your search' : 'No residents yet' }}</h5>
                                    <p class="text-muted mb-3">
                                        {{ $hasFilters ? 'Try a different name or adjust the filters above.' : 'Add the first resident record to get started.' }}
                                    </p>
                                    @if(!$hasFilters)
                                        <a href="{{ route('residents.create') }}" class="btn btn-primary">
                                            <i class="bi bi-person-plus-fill me-1"></i> Add Resident
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($residents->count())
                <div class="rp-foot">
                    <span>
                        Showing <strong>{{ $residents->firstItem() }}</strong>–<strong>{{ $residents->lastItem() }}</strong>
                        of <strong>{{ $residents->total() }}</strong> residents
                    </span>
                    @if($residents->hasPages())
                        {{ $residents->withQueryString()->links() }}
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    var card     = document.getElementById('rp-card');
    var form     = document.getElementById('rp-form');
    var search   = document.getElementById('rp-search');
    var box      = document.getElementById('rp-search-box');
    var xbtn     = document.getElementById('rp-x');
    var sex      = document.getElementById('rp-sex');
    var purok    = document.getElementById('rp-purok');
    var sort     = document.getElementById('rp-sort');
    var classIn  = document.getElementById('rp-classification-input');
    var statusIn = document.getElementById('rp-status-input');
    var tabsWrap = document.getElementById('rp-tabs-wrap');
    var results  = document.getElementById('rp-results');
    var live     = document.getElementById('rp-live');
    var chips    = document.querySelectorAll('.rp-chip');
    var toggles  = document.querySelectorAll('.rp-toggle button');
    var mq       = window.matchMedia('(max-width: 767.98px)');
    var base     = form.getAttribute('action');
    var timer = null, controller = null, page = '';

    /* ---------- table / card view ---------- */
    function savedView() { try { return localStorage.getItem('resident_view') || 'table'; } catch (e) { return 'table'; } }
    function applyView() {
        var table = document.getElementById('rec-table');
        if (!table) return;
        var view = mq.matches ? 'grid' : savedView();
        table.classList.toggle('is-grid', view === 'grid');
        toggles.forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.view === view ? 'true' : 'false'); });
    }
    toggles.forEach(function (b) {
        b.addEventListener('click', function () {
            try { localStorage.setItem('resident_view', b.dataset.view); } catch (e) {}
            applyView();
        });
    });
    if (mq.addEventListener) mq.addEventListener('change', applyView);

    /* ---------- helpers ---------- */
    function hasFilters() {
        return search.value.trim() !== ''
            || sex.value !== ''
            || purok.value !== ''
            || (sort.value !== '' && sort.value !== 'newest')
            || (classIn && classIn.value !== '')
            || (statusIn && !statusIn.disabled && statusIn.value !== '');
    }
    function syncUi() {
        box.classList.toggle('has-text', search.value !== '');
        var current = classIn ? classIn.value.trim().toLowerCase() : '';
        chips.forEach(function (c) {
            c.classList.toggle('is-active', current !== '' && c.dataset.q.toLowerCase() === current);
        });
    }
    function buildUrl() {
        var p = new URLSearchParams();
        if (search.value.trim() !== '') p.set('search', search.value.trim());
        if (classIn && classIn.value !== '') p.set('classification', classIn.value);
        if (purok.value !== '') p.set('purok', purok.value);
        if (sex.value !== '') p.set('sex', sex.value);
        if (sort.value !== '') p.set('sort', sort.value);
        if (statusIn && !statusIn.disabled && statusIn.value !== '') p.set('status', statusIn.value);
        if (page) p.set('page', page);
        var qs = p.toString();
        return base + (qs ? '?' + qs : '');
    }
    function setStatus(value) {
        if (!statusIn) return;
        statusIn.value = value || '';
        statusIn.disabled = !value;
    }

    /* highlight the searched words inside names (safe: text nodes only) */
    function highlight() {
        var term = search.value.trim();
        if (!term) return;
        var words = term.split(/\s+/).filter(Boolean).map(function (w) { return w.toLowerCase(); });
        results.querySelectorAll('.rp-name').forEach(function (el) {
            var text = el.textContent, lower = text.toLowerCase(), out = [], i = 0;
            while (i < text.length) {
                var hit = null;
                for (var w = 0; w < words.length; w++) {
                    if (lower.substr(i, words[w].length) === words[w] && (!hit || words[w].length > hit.length)) hit = words[w];
                }
                if (hit) {
                    var m = document.createElement('mark');
                    m.textContent = text.substr(i, hit.length);
                    out.push(m);
                    i += hit.length;
                } else {
                    var j = i + 1;
                    while (j < text.length) {
                        var starts = words.some(function (x) { return lower.substr(j, x.length) === x; });
                        if (starts) break;
                        j++;
                    }
                    out.push(document.createTextNode(text.substring(i, j)));
                    i = j;
                }
            }
            el.textContent = '';
            out.forEach(function (n) { el.appendChild(n); });
        });
    }

    /* ---------- live load (server-side, so results are always accurate) ---------- */
    function load(url) {
        if (controller) controller.abort();
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
            if (!r.ok) throw new Error('http ' + r.status);
            return r.text();
        })
        .then(function (html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var newTabs = doc.getElementById('rp-tabs-wrap');
            var newRes  = doc.getElementById('rp-results');
            var newLive = doc.getElementById('rp-live');
            if (!newRes) throw new Error('bad response');
            if (newTabs) tabsWrap.innerHTML = newTabs.innerHTML;
            results.innerHTML = newRes.innerHTML;
            if (newLive) live.innerHTML = newLive.innerHTML;
            history.replaceState(null, '', url);
            applyView();
            highlight();
        })
        .catch(function (err) {
            if (err && (err.name === 'AbortError' || err.message === 'redirect')) return;
            window.location.href = url;   // safe fallback: normal page load
        })
        .then(function () {
            if (!controller || !controller.signal.aborted) card.classList.remove('is-loading');
        });
    }

    function run(delay) {
        clearTimeout(timer);
        syncUi();
        timer = setTimeout(function () { load(buildUrl()); }, delay);
    }

    /* typing → search automatically (waits 300 ms after the last key) */
    search.addEventListener('input', function () { page = ''; run(300); });
    /* choosing a dropdown value → filters immediately */
    sex.addEventListener('change', function () { page = ''; run(0); });
    purok.addEventListener('change', function () { page = ''; run(0); });
    sort.addEventListener('change', function () { page = ''; run(0); });
    /* Enter key never reloads the page */
    form.addEventListener('submit', function (e) { e.preventDefault(); page = ''; run(0); });

    function clearAll() {
        search.value = '';
        sex.value = '';
        purok.value = '';
        sort.value = 'newest';
        if (classIn) classIn.value = '';
        setStatus('');
        page = '';
        syncUi();
        run(0);
        search.focus();
    }
    xbtn.addEventListener('click', function () { search.value = ''; page = ''; run(0); search.focus(); });

    /* Quick classification chips remain separate from the name search. */
    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            var same = classIn.value.trim().toLowerCase() === chip.dataset.q.toLowerCase();
            classIn.value = same ? '' : chip.dataset.q;
            page = '';
            run(0);
        });
    });

    /* status tabs and pagination update results without a full reload */
    document.addEventListener('click', function (e) {
        var tab = e.target.closest('.rp-tab');
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

    window.addEventListener('pageshow', function () { syncUi(); });
    syncUi();
    applyView();
    highlight();
})();
</script>
@endsection
