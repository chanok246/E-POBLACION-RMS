@extends('layouts.app')

@section('title', 'Residents | e-Poblacion')

@push('styles')
<style>
    /* ---------- Theme tokens (same names as your other pages) ---------- */
    .residents-page {
        --resident-surface:#ffffff; --resident-surface-soft:#f6f7fb; --resident-text:#252b43; --resident-muted:#7b8198;
        --resident-border:#e7e9f0; --resident-shadow:0 6px 18px rgba(31,38,72,.06);
        --rp-accent:#5b3df0; --rp-accent-soft:#efecff; --rp-ok:#17915f; --rp-ok-soft:#e4f6ed;
        --rp-off:#7b8198; --rp-off-soft:#eceef4; --rp-danger:#d6394b; --rp-danger-soft:#fdecee;
    }
    html[data-theme="dark"] .residents-page {
        --resident-surface:#1b1d2a; --resident-surface-soft:#232636; --resident-text:#f1f3f8; --resident-muted:#aeb4c5;
        --resident-border:rgba(255,255,255,.09); --resident-shadow:0 6px 18px rgba(0,0,0,.2);
        --rp-accent:#a99aff; --rp-accent-soft:rgba(169,154,255,.16); --rp-ok:#65d69b; --rp-ok-soft:rgba(101,214,155,.14);
        --rp-off:#c4c9d6; --rp-off-soft:rgba(196,201,214,.12); --rp-danger:#ff8f9b; --rp-danger-soft:rgba(255,143,155,.12);
    }
    .residents-page .breadcrumb-text { color:var(--resident-muted); font-size:12px; font-weight:700; letter-spacing:.35px; margin-bottom:6px; }
    .residents-page .page-title { color:var(--resident-text); }
    .residents-page .page-subtitle, .residents-page .text-muted { color:var(--resident-muted) !important; }
    .residents-page .card { background:var(--resident-surface); color:var(--resident-text); border:1px solid var(--resident-border) !important; border-radius:14px; box-shadow:var(--resident-shadow); overflow:hidden; }

    /* Avatar palette: each resident gets one of six tints */
    .av-0{--av-bg:#e7f0ff;--av-fg:#0d6efd} .av-1{--av-bg:#efecff;--av-fg:#5b3df0} .av-2{--av-bg:#e4f6ed;--av-fg:#17915f}
    .av-3{--av-bg:#fff1e0;--av-fg:#c76a00} .av-4{--av-bg:#fde8ee;--av-fg:#d6336c} .av-5{--av-bg:#e2f5f7;--av-fg:#0b8a9c}
    html[data-theme="dark"] .av-0{--av-bg:rgba(13,110,253,.18);--av-fg:#75aaff} html[data-theme="dark"] .av-1{--av-bg:rgba(169,154,255,.16);--av-fg:#a99aff}
    html[data-theme="dark"] .av-2{--av-bg:rgba(101,214,155,.14);--av-fg:#65d69b} html[data-theme="dark"] .av-3{--av-bg:rgba(255,166,64,.16);--av-fg:#ffb866}
    html[data-theme="dark"] .av-4{--av-bg:rgba(255,120,160,.16);--av-fg:#ff8fb3} html[data-theme="dark"] .av-5{--av-bg:rgba(60,200,220,.15);--av-fg:#6fd6e4}

    /* ---------- Tabs (double as summary counts) ---------- */
    .rp-tabs { display:flex; gap:4px; padding:8px 14px 0; border-bottom:1px solid var(--resident-border); overflow-x:auto; }
    .rp-tab { display:flex; align-items:baseline; gap:8px; padding:12px 14px; white-space:nowrap; color:var(--resident-muted); text-decoration:none; font-weight:600; border-bottom:2px solid transparent; margin-bottom:-1px; }
    .rp-tab b { font-size:1.2rem; font-weight:700; color:var(--resident-text); font-variant-numeric:tabular-nums; }
    .rp-tab:hover { color:var(--resident-text); }
    .rp-tab.is-active, .rp-tab.is-active b { color:var(--rp-accent); }
    .rp-tab.is-active { border-bottom-color:var(--rp-accent); }

    /* ---------- Toolbar ---------- */
    .rp-tools { display:flex; gap:10px; flex-wrap:wrap; padding:14px 16px; border-bottom:1px solid var(--resident-border); align-items:center; }
    .rp-search { position:relative; flex:1 1 260px; }
    .rp-search > i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--resident-muted); }
    .rp-search kbd { position:absolute; right:10px; top:50%; transform:translateY(-50%); background:var(--resident-surface); color:var(--resident-muted); border:1px solid var(--resident-border); font-size:.75rem; padding:1px 7px; border-radius:6px; }
    .residents-page .form-control, .residents-page .form-select { background-color:var(--resident-surface-soft); color:var(--resident-text); border:1px solid var(--resident-border); border-radius:10px; }
    .residents-page .rp-search .form-control { padding-left:36px; padding-right:40px; }
    .residents-page .form-control::placeholder { color:var(--resident-muted); }
    .residents-page .form-control:focus, .residents-page .form-select:focus { background-color:var(--resident-surface); color:var(--resident-text); border-color:var(--rp-accent); box-shadow:0 0 0 .2rem var(--rp-accent-soft); }
    html[data-theme="dark"] .residents-page .form-select option { background:#1b1d2a; color:#f1f3f8; }
    .rp-tools .form-select { flex:0 0 150px; }
    .rp-clear { color:var(--resident-muted); font-size:.875rem; text-decoration:none; }
    .rp-clear:hover { color:var(--rp-danger); }
    .rp-toggle { display:inline-flex; border:1px solid var(--resident-border); border-radius:10px; padding:3px; background:var(--resident-surface-soft); margin-left:auto; }
    .rp-toggle button { border:0; background:transparent; color:var(--resident-muted); width:34px; height:30px; border-radius:7px; }
    .rp-toggle button[aria-pressed="true"] { background:var(--resident-surface); color:var(--rp-accent); box-shadow:0 1px 3px rgba(0,0,0,.12); }

    /* ---------- Table ---------- */
    .residents-page .table { --bs-table-bg:transparent; --bs-table-color:var(--resident-text); --bs-table-border-color:var(--resident-border); margin:0; }
    .residents-page .table thead th { background:var(--resident-surface-soft); color:var(--resident-muted); font-size:.8rem; font-weight:600; border-bottom:1px solid var(--resident-border); padding:12px 16px; white-space:nowrap; }
    .residents-page .table tbody td { padding:14px 16px; vertical-align:middle; border-color:var(--resident-border); }
    .residents-page .table-hover > tbody > tr:hover > * { background:var(--resident-surface-soft); color:var(--resident-text); }

    .rp-person { display:flex; align-items:center; gap:12px; min-width:0; }
    .rp-avatar { width:42px; height:42px; flex:0 0 42px; border-radius:13px; display:grid; place-items:center; background:var(--av-bg); color:var(--av-fg); font-weight:700; font-size:.85rem; }
    .rp-name { font-weight:600; color:var(--resident-text); text-decoration:none; }
    .rp-name:hover { color:var(--rp-accent); }
    .rp-id { font-size:.8rem; color:var(--resident-muted); font-variant-numeric:tabular-nums; }

    .rp-pill { display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:999px; font-size:.8rem; font-weight:600; }
    .rp-pill::before { content:""; width:7px; height:7px; border-radius:50%; background:currentColor; }
    .rp-pill.ok { background:var(--rp-ok-soft); color:var(--rp-ok); }
    .rp-pill.off { background:var(--rp-off-soft); color:var(--rp-off); }

    .rp-actions { display:inline-flex; gap:4px; }
    .rp-act { width:34px; height:34px; display:inline-grid; place-items:center; border-radius:9px; border:1px solid transparent; background:transparent; color:var(--resident-muted); text-decoration:none; }
    .rp-act:hover, .rp-act:focus-visible { background:var(--resident-surface-soft); border-color:var(--resident-border); color:var(--rp-accent); }
    .rp-act.danger:hover, .rp-act.danger:focus-visible { color:var(--rp-danger); background:var(--rp-danger-soft); border-color:transparent; }

    /* ---------- Card view (same markup, restyled) ---------- */
    .rec-table.is-grid thead { display:none; }
    .rec-table.is-grid, .rec-table.is-grid tbody { display:block; }
    .rec-table.is-grid tbody { display:grid; grid-template-columns:repeat(auto-fill, minmax(270px, 1fr)); gap:14px; padding:16px; }
    .rec-table.is-grid tr { display:block; position:relative; padding:16px; border:1px solid var(--resident-border); border-radius:14px; background:var(--resident-surface); }
    .rec-table.is-grid tr:hover > * { background:transparent !important; }
    .rec-table.is-grid td { display:block; padding:3px 0; border:0; background:transparent !important; }
    .rec-table.is-grid td.c-num { display:none; }
    .rec-table.is-grid td.c-meta::before { content:attr(data-label) ": "; color:var(--resident-muted); }
    .rec-table.is-grid td.c-status { position:absolute; top:16px; right:16px; width:auto; padding:0; }
    .rec-table.is-grid td.c-actions { margin-top:12px; padding-top:12px; border-top:1px solid var(--resident-border); text-align:left !important; }
    .rec-table.is-grid .rp-person { margin-bottom:10px; padding-right:80px; }
    .rec-table.is-grid tr.is-empty { grid-column:1 / -1; border:0; }

    .rp-empty { text-align:center; padding:56px 16px; }
    .rp-empty i { font-size:2.5rem; color:var(--resident-muted); }
    .rp-empty h5 { margin:12px 0 4px; font-weight:700; }
    .rp-foot { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; padding:14px 16px; border-top:1px solid var(--resident-border); font-size:.875rem; }
    .residents-page .pagination { margin:0; }
    html[data-theme="dark"] .residents-page .page-link { background:var(--resident-surface); color:var(--rp-accent); border-color:var(--resident-border); }
    html[data-theme="dark"] .residents-page .page-item.active .page-link { background:#5b3df0; border-color:#5b3df0; color:#fff; }
    html[data-theme="dark"] .residents-page .page-item.disabled .page-link { background:#181a25; color:#686e80; }
    html[data-theme="dark"] .residents-page .btn-dark { background:#e9ebf2; border-color:#e9ebf2; color:#171923; }

    @media (max-width: 767.98px) {
        .residents-page .page-title { font-size:1.7rem; }
        .rp-toggle, .rp-search kbd { display:none; }
        .rp-tools .form-select { flex:1 1 45%; }
    }
    @media print { .rp-tools, .rp-tabs, .rp-actions, .btn { display:none !important; } }
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
    $hasFilters    = request()->filled('search') || request()->filled('sex') || $currentStatus !== '';
@endphp

<div class="container-fluid residents-page">

    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
        <div>
            <div class="breadcrumb-text">E-POBLACION / MANAGEMENT / RESIDENTS</div>
            <h1 class="page-title mb-1">Resident Records</h1>
            <p class="page-subtitle mb-0">Manage and maintain registered residents of Barangay Poblacion.</p>
        </div>
        <a href="{{ route('residents.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i> Add Resident
        </a>
    </div>

    <div class="card">

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

        <form method="GET" action="{{ route('residents.index') }}" class="rp-tools">
            @if($currentStatus !== '')
                <input type="hidden" name="status" value="{{ $currentStatus }}">
            @endif

            <div class="rp-search">
                <i class="bi bi-search"></i>
                <input type="search" id="rp-search" name="search" class="form-control"
                       placeholder="Search by first or last name" value="{{ request('search') }}" aria-label="Search residents">
                <kbd>/</kbd>
            </div>

            <select name="sex" class="form-select" aria-label="Filter by sex">
                <option value="">Any sex</option>
                <option value="Male" {{ request('sex') === 'Male' ? 'selected' : '' }}>Male</option>
                <option value="Female" {{ request('sex') === 'Female' ? 'selected' : '' }}>Female</option>
            </select>

            <button type="submit" class="btn btn-dark"><i class="bi bi-funnel-fill me-1"></i> Filter</button>

            @if($hasFilters)
                <a href="{{ route('residents.index') }}" class="rp-clear">Clear filters</a>
            @endif

            <div class="rp-toggle" role="group" aria-label="Change view">
                <button type="button" data-view="table" title="Table view" aria-pressed="true"><i class="bi bi-list-ul"></i></button>
                <button type="button" data-view="grid" title="Card view" aria-pressed="false"><i class="bi bi-grid-3x3-gap-fill"></i></button>
            </div>
        </form>

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
                    @endphp
                    <tr>
                        <td class="c-num text-muted">{{ $residents->firstItem() + $loop->index }}</td>

                        <td>
                            <div class="rp-person">
                                <div class="rp-avatar av-{{ $resident->id % 6 }}" aria-hidden="true">{{ $initials }}</div>
                                <div class="text-truncate">
                                    <a href="{{ route('residents.show', $resident->id) }}" class="rp-name">{{ $fullName }}</a>
                                    <div class="rp-id">ID {{ str_pad($resident->id, 5, '0', STR_PAD_LEFT) }}</div>
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
                                <h5>{{ $hasFilters ? 'No residents match your filters' : 'No residents yet' }}</h5>
                                <p class="text-muted mb-3">
                                    {{ $hasFilters ? 'Try a different name or clear the filters.' : 'Add the first resident record to get started.' }}
                                </p>
                                @if($hasFilters)
                                    <a href="{{ route('residents.index') }}" class="btn btn-outline-secondary">Clear filters</a>
                                @else
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
                <span class="text-muted">
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

<script>
(function () {
    var table = document.getElementById('rec-table');
    if (!table) return;
    var buttons = document.querySelectorAll('.rp-toggle button');
    var mq = window.matchMedia('(max-width: 767.98px)');

    function saved() { try { return localStorage.getItem('resident_view') || 'table'; } catch (e) { return 'table'; } }
    function apply() {
        var view = mq.matches ? 'grid' : saved();
        table.classList.toggle('is-grid', view === 'grid');
        buttons.forEach(function (b) { b.setAttribute('aria-pressed', b.dataset.view === view ? 'true' : 'false'); });
    }
    buttons.forEach(function (b) {
        b.addEventListener('click', function () {
            try { localStorage.setItem('resident_view', b.dataset.view); } catch (e) {}
            apply();
        });
    });
    if (mq.addEventListener) { mq.addEventListener('change', apply); }
    apply();

    // Press "/" to jump to search
    var search = document.getElementById('rp-search');
    document.addEventListener('keydown', function (e) {
        if (e.key === '/' && !/^(input|textarea|select)$/i.test(document.activeElement.tagName)) {
            e.preventDefault();
            search.focus();
        }
    });
})();
</script>
@endsection
