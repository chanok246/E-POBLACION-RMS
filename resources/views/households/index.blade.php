@extends('layouts.app')

@section('title', 'Household Management | e-Poblacion')

@push('styles')
<style>
    /* ---------- Theme tokens ---------- */
    .households-page {
        --household-surface:#ffffff; --household-surface-soft:#f6f7fb; --household-text:#252b43; --household-muted:#7b8198;
        --household-border:#e7e9f0; --household-shadow:0 6px 18px rgba(31,38,72,.06);
        --hp-accent:#5b3df0; --hp-accent-soft:#efecff; --hp-ok:#17915f; --hp-ok-soft:#e4f6ed;
        --hp-off:#7b8198; --hp-off-soft:#eceef4; --hp-danger:#d6394b; --hp-danger-soft:#fdecee;
    }
    html[data-theme="dark"] .households-page {
        --household-surface:#1b1d2a; --household-surface-soft:#232636; --household-text:#f1f3f8; --household-muted:#aeb4c5;
        --household-border:rgba(255,255,255,.09); --household-shadow:0 6px 18px rgba(0,0,0,.2);
        --hp-accent:#a99aff; --hp-accent-soft:rgba(169,154,255,.16); --hp-ok:#65d69b; --hp-ok-soft:rgba(101,214,155,.14);
        --hp-off:#c4c9d6; --hp-off-soft:rgba(196,201,214,.12); --hp-danger:#ff8f9b; --hp-danger-soft:rgba(255,143,155,.12);
    }
    .households-page .breadcrumb-text { color:var(--household-muted); font-size:12px; font-weight:700; letter-spacing:.35px; margin-bottom:6px; }
    .households-page .page-title { color:var(--household-text); }
    .households-page .page-subtitle { color:var(--household-muted); max-width:800px; }
    .households-page .text-muted { color:var(--household-muted) !important; }
    .households-page .btn { font-weight:600; }
    .households-page .card { background:var(--household-surface); color:var(--household-text); border:1px solid var(--household-border) !important; border-radius:14px; box-shadow:var(--household-shadow); overflow:hidden; }

    /* Purok color coding: each purok gets one of six tints */
    .pk-0{--pk-bg:#e7f0ff;--pk-fg:#0d6efd} .pk-1{--pk-bg:#efecff;--pk-fg:#5b3df0} .pk-2{--pk-bg:#e4f6ed;--pk-fg:#17915f}
    .pk-3{--pk-bg:#fff1e0;--pk-fg:#c76a00} .pk-4{--pk-bg:#fde8ee;--pk-fg:#d6336c} .pk-5{--pk-bg:#e2f5f7;--pk-fg:#0b8a9c}
    html[data-theme="dark"] .pk-0{--pk-bg:rgba(13,110,253,.18);--pk-fg:#75aaff} html[data-theme="dark"] .pk-1{--pk-bg:rgba(169,154,255,.16);--pk-fg:#a99aff}
    html[data-theme="dark"] .pk-2{--pk-bg:rgba(101,214,155,.14);--pk-fg:#65d69b} html[data-theme="dark"] .pk-3{--pk-bg:rgba(255,166,64,.16);--pk-fg:#ffb866}
    html[data-theme="dark"] .pk-4{--pk-bg:rgba(255,120,160,.16);--pk-fg:#ff8fb3} html[data-theme="dark"] .pk-5{--pk-bg:rgba(60,200,220,.15);--pk-fg:#6fd6e4}

    /* Header dropdown */
    .households-page .dropdown-menu { background:var(--household-surface); border:1px solid var(--household-border); border-radius:12px; padding:6px; }
    .households-page .dropdown-item { color:var(--household-text); border-radius:8px; font-size:.9rem; }
    .households-page .dropdown-item:hover, .households-page .dropdown-item:focus { background:var(--household-surface-soft); color:var(--household-text); }
    .households-page .dropdown-item-text, .households-page .dropdown-header { color:var(--household-muted); }
    .households-page .dropdown-divider { border-top-color:var(--household-border); }
    html[data-theme="dark"] .households-page .btn-outline-primary { color:#8fb8ff; border-color:#5d8fe8; }
    html[data-theme="dark"] .households-page .btn-outline-primary:hover { background:#4d7fd6; border-color:#4d7fd6; color:#fff; }

    /* Alert */
    .hp-alert { display:flex; gap:10px; align-items:flex-start; padding:14px 16px; border-radius:12px; margin-bottom:16px; background:var(--hp-danger-soft); color:var(--hp-danger); border:1px solid var(--hp-danger); }
    .hp-alert .btn-close { margin-left:auto; }
    html[data-theme="dark"] .hp-alert .btn-close { filter:invert(1) grayscale(100%) brightness(200%); }

    /* ---------- Tabs (double as summary counts) ---------- */
    .hp-tabs { display:flex; align-items:center; gap:4px; padding:8px 14px 0; border-bottom:1px solid var(--household-border); overflow-x:auto; }
    .hp-tab { display:flex; align-items:baseline; gap:8px; padding:12px 14px; white-space:nowrap; color:var(--household-muted); text-decoration:none; font-weight:600; border-bottom:2px solid transparent; margin-bottom:-1px; }
    .hp-tab b { font-size:1.2rem; font-weight:700; color:var(--household-text); font-variant-numeric:tabular-nums; }
    .hp-tab:hover { color:var(--household-text); }
    .hp-tab.is-active, .hp-tab.is-active b { color:var(--hp-accent); }
    .hp-tab.is-active { border-bottom-color:var(--hp-accent); }
    .hp-members { margin-left:auto; padding:0 6px 8px 20px; white-space:nowrap; color:var(--household-muted); font-size:.875rem; }
    .hp-members strong { color:var(--household-text); }

    /* ---------- Toolbar ---------- */
    .hp-tools { display:flex; gap:10px; flex-wrap:wrap; padding:14px 16px; border-bottom:1px solid var(--household-border); align-items:center; }
    .hp-search { position:relative; flex:1 1 300px; }
    .hp-search > i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--household-muted); }
    .hp-search kbd { position:absolute; right:10px; top:50%; transform:translateY(-50%); background:var(--household-surface); color:var(--household-muted); border:1px solid var(--household-border); font-size:.75rem; padding:1px 7px; border-radius:6px; }
    .households-page .form-control, .households-page .form-select { background-color:var(--household-surface-soft); color:var(--household-text); border:1px solid var(--household-border); border-radius:10px; }
    .households-page .hp-search .form-control { padding-left:36px; padding-right:40px; }
    .households-page .form-control::placeholder { color:var(--household-muted); opacity:.9; }
    .households-page .form-control:focus, .households-page .form-select:focus { background-color:var(--household-surface); color:var(--household-text); border-color:var(--hp-accent); box-shadow:0 0 0 .2rem var(--hp-accent-soft); }
    html[data-theme="dark"] .households-page .form-select option { background:#1b1d2a; color:#f1f3f8; }
    .hp-tools .form-select { flex:0 0 160px; }
    .hp-live { color:var(--household-muted); font-size:.82rem; white-space:nowrap; }
    .hp-live i { color:var(--hp-accent); }
    .hp-clear { color:var(--household-muted); font-size:.875rem; text-decoration:none; white-space:nowrap; }
    .hp-clear:hover { color:var(--hp-danger); }
    .hp-toggle { display:inline-flex; border:1px solid var(--household-border); border-radius:10px; padding:3px; background:var(--household-surface-soft); margin-left:auto; }
    .hp-toggle button { border:0; background:transparent; color:var(--household-muted); width:34px; height:30px; border-radius:7px; }
    .hp-toggle button[aria-pressed="true"] { background:var(--household-surface); color:var(--hp-accent); box-shadow:0 1px 3px rgba(0,0,0,.12); }

    /* ---------- Table ---------- */
    .households-page .table { --bs-table-bg:transparent; --bs-table-color:var(--household-text); --bs-table-border-color:var(--household-border); margin:0; }
    .households-page .table thead th { background:var(--household-surface-soft); color:var(--household-muted); font-size:.8rem; font-weight:600; border-bottom:1px solid var(--household-border); padding:12px 16px; white-space:nowrap; }
    .households-page .table tbody td { padding:14px 16px; vertical-align:middle; border-color:var(--household-border); }
    .households-page .table-hover > tbody > tr:hover > * { background:var(--household-surface-soft); color:var(--household-text); }

    .hp-home { display:flex; align-items:center; gap:12px; min-width:0; }
    .hp-tile { width:42px; height:42px; flex:0 0 42px; border-radius:13px; display:grid; place-items:center; background:var(--pk-bg); color:var(--pk-fg); font-size:1.1rem; }
    .hp-number { font-weight:700; color:var(--household-text); text-decoration:none; font-variant-numeric:tabular-nums; }
    .hp-number:hover { color:var(--hp-accent); }
    .hp-head { font-size:.82rem; color:var(--household-muted); }
    .hp-purok { display:inline-block; padding:3px 10px; border-radius:8px; background:var(--pk-bg); color:var(--pk-fg); font-size:.8rem; font-weight:600; }
    .hp-count { display:inline-flex; align-items:center; gap:6px; font-weight:600; font-variant-numeric:tabular-nums; }
    .hp-count i { color:var(--household-muted); }

    .hp-pill { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; font-size:.8rem; font-weight:600; }
    .hp-pill::before { content:""; width:7px; height:7px; border-radius:50%; background:currentColor; }
    .hp-pill.ok { background:var(--hp-ok-soft); color:var(--hp-ok); }
    .hp-pill.off { background:var(--hp-off-soft); color:var(--hp-off); }

    .hp-actions { display:inline-flex; gap:4px; }
    .hp-act { width:34px; height:34px; display:inline-grid; place-items:center; border-radius:9px; border:1px solid transparent; background:transparent; color:var(--household-muted); text-decoration:none; }
    .hp-act:hover, .hp-act:focus-visible { background:var(--household-surface-soft); border-color:var(--household-border); color:var(--hp-accent); }
    .hp-act.ok:hover, .hp-act.ok:focus-visible { color:var(--hp-ok); background:var(--hp-ok-soft); border-color:transparent; }
    .hp-act.danger:hover, .hp-act.danger:focus-visible { color:var(--hp-danger); background:var(--hp-danger-soft); border-color:transparent; }

    /* ---------- Card view (same markup, restyled) ---------- */
    .hh-table.is-grid thead { display:none; }
    .hh-table.is-grid, .hh-table.is-grid tbody { display:block; }
    .hh-table.is-grid tbody { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:14px; padding:16px; }
    .hh-table.is-grid tr { display:block; position:relative; padding:16px; border:1px solid var(--household-border); border-radius:14px; background:var(--household-surface); }
    .hh-table.is-grid tr:hover > * { background:transparent !important; }
    .hh-table.is-grid td { display:block; padding:3px 0; border:0; background:transparent !important; text-align:left !important; }
    .hh-table.is-grid td.c-num { display:none; }
    .hh-table.is-grid td.c-meta::before { content:attr(data-label) ": "; color:var(--household-muted); }
    .hh-table.is-grid td.c-status { position:absolute; top:16px; right:16px; width:auto; padding:0; }
    .hh-table.is-grid td.c-actions { margin-top:12px; padding-top:12px; border-top:1px solid var(--household-border); }
    .hh-table.is-grid .hp-home { margin-bottom:10px; padding-right:80px; }

    .hp-empty { text-align:center; padding:56px 16px; }
    .hp-empty i { font-size:2.75rem; color:var(--household-muted); }
    .hp-empty h5 { margin:12px 0 4px; font-weight:700; }
    .hp-foot { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:14px 16px; border-top:1px solid var(--household-border); font-size:.875rem; }
    .households-page .pagination { margin:0; }
    html[data-theme="dark"] .households-page .page-link { background:var(--household-surface); color:var(--hp-accent); border-color:var(--household-border); }
    html[data-theme="dark"] .households-page .page-item.active .page-link { background:#5b3df0; border-color:#5b3df0; color:#fff; }
    html[data-theme="dark"] .households-page .page-item.disabled .page-link { background:#181a25; color:#686e80; }
    html[data-theme="dark"] .households-page .btn-outline-secondary { color:#c5cad7; border-color:#697084; }
    html[data-theme="dark"] .households-page .btn-outline-secondary:hover { background:#697084; color:#fff; }

    @media (max-width: 767.98px) {
        .households-page .page-title { font-size:1.7rem; }
        .hp-toggle, .hp-search kbd, .hp-live, .hp-members { display:none; }
        .hp-tools .form-select { flex:1 1 45%; }
        .households-page .page-header-actions { width:100%; }
    }
    @media print { .hp-tools, .hp-tabs, .hp-actions, .page-header-actions { display:none !important; } }
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

    {{-- Page header --}}
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
        <div>
            <div class="breadcrumb-text">E-POBLACION / HOUSEHOLDS</div>
            <h1 class="page-title mb-1">Household Management</h1>
            <p class="page-subtitle mb-0">Manage household records, members, status, and masterlists of Barangay Poblacion.</p>
        </div>

        <div class="d-flex gap-2 page-header-actions">
            <div class="dropdown">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-printer me-1"></i> Print Masterlist
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
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
                <a href="{{ route('households.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i> Add Household
                </a>
            @endif
        </div>
    </div>

    @if(session('error'))
        <div class="hp-alert" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">

        {{-- Status tabs (also the summary counts) --}}
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

        {{-- Filters (update automatically) --}}
        <form action="{{ route('households.index') }}" method="GET" id="householdFilterForm" class="hp-tools">
            @if($currentStatus !== '')
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif

            <div class="hp-search">
                <i class="bi bi-search"></i>
                <input type="search" name="search" id="search" class="form-control"
                       value="{{ request('search') }}" autocomplete="off"
                       placeholder="Household ID, address, purok, or head" aria-label="Search households">
                <kbd>/</kbd>
            </div>

            <select name="purok" id="purok" class="form-select" aria-label="Filter by purok">
                <option value="">All Puroks</option>
                @foreach($purokList as $purok)
                    <option value="{{ $purok }}" {{ request('purok') == $purok ? 'selected' : '' }}>{{ $purok }}</option>
                @endforeach
            </select>

            @if($hasFilters)
                <a href="{{ route('households.index') }}" class="hp-clear">Clear filters</a>
            @endif

            <span class="hp-live"><i class="bi bi-lightning-charge-fill me-1"></i>Updates as you type</span>

            <div class="hp-toggle" role="group" aria-label="Change view">
                <button type="button" data-view="table" title="Table view" aria-pressed="true"><i class="bi bi-list-ul"></i></button>
                <button type="button" data-view="grid" title="Card view" aria-pressed="false"><i class="bi bi-grid-3x3-gap-fill"></i></button>
            </div>
        </form>

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
                                        <a href="{{ route('households.show', $household) }}" class="hp-number">{{ $household->household_number }}</a>
                                        <div class="hp-head">
                                            @if($headName)Head: {{ $headName }}@else<span class="fst-italic">No household head set</span>@endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="c-meta pk-{{ $purokIdx }}" data-label="Purok">
                                @if($household->purok)
                                    <span class="hp-purok">{{ $household->purok }}</span>
                                @else
                                    <span class="text-muted">Not specified</span>
                                @endif
                            </td>

                            <td class="c-meta" data-label="Address">{{ $household->address ? $household->address : 'Not specified' }}</td>

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
                <span class="text-muted">
                    Showing <strong>{{ $households->firstItem() }}</strong>–<strong>{{ $households->lastItem() }}</strong>
                    of <strong>{{ $households->total() }}</strong> households
                </span>
                @if($households->hasPages())
                    {{ $households->appends(request()->query())->links() }}
                @endif
            </div>

        @else

            <div class="hp-empty">
                <i class="bi bi-houses"></i>
                <h5>{{ $hasFilters ? 'No households match your filters' : 'No households yet' }}</h5>
                <p class="text-muted mb-3">
                    {{ $hasFilters ? 'Try a different search or clear the filters.' : 'Add the first household record to get started.' }}
                </p>
                <div class="d-flex justify-content-center gap-2 flex-wrap">
                    @if($hasFilters)
                        <a href="{{ route('households.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Clear filters
                        </a>
                    @endif
                    @if($canManageHouseholds)
                        <a href="{{ route('households.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Add Household
                        </a>
                    @endif
                </div>
            </div>

        @endif
    </div>
</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    var form   = document.getElementById('householdFilterForm');
    var search = document.getElementById('search');
    var purok  = document.getElementById('purok');
    if (!form) { return; }

    /* Automatic filtering: search after 400ms of silence, purok on change */
    var timer = null;
    if (search) {
        search.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () { form.submit(); }, 400);
        });

        // The page reloads after each search, so put the cursor back at the end.
        if (search.value) {
            search.focus();
            var end = search.value.length;
            try { search.setSelectionRange(end, end); } catch (e) {}
        }
    }
    if (purok) { purok.addEventListener('change', function () { form.submit(); }); }

    /* Table / card view toggle (cards are used automatically on phones) */
    var table   = document.getElementById('hh-table');
    var buttons = document.querySelectorAll('.hp-toggle button');
    var mq      = window.matchMedia('(max-width: 767.98px)');

    function saved() { try { return localStorage.getItem('household_view') || 'table'; } catch (e) { return 'table'; } }
    function apply() {
        if (!table) { return; }
        var view = mq.matches ? 'grid' : saved();
        table.classList.toggle('is-grid', view === 'grid');
        buttons.forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.view === view ? 'true' : 'false'); });
    }
    buttons.forEach(function (b) {
        b.addEventListener('click', function () {
            try { localStorage.setItem('household_view', b.dataset.view); } catch (e) {}
            apply();
        });
    });
    if (mq.addEventListener) { mq.addEventListener('change', apply); }
    apply();

    /* Press "/" to jump to search */
    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && search && !/^(input|textarea|select)$/i.test(document.activeElement.tagName)) {
            e.preventDefault();
            search.focus();
        }
    });
});
</script>
@endpush
