@extends('layouts.app')

@section('title', 'Barangay Documents | e-Poblacion')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       BARANGAY DOCUMENTS - request desk
       Scoped to .docs ; light + dark via html[data-theme]
       ========================================================= */
    .docs {
        --ink: #14213d;
        --muted: #5f6b7d;
        --paper: #ffffff;
        --wash: #eef2f7;
        --line: #dbe2ec;
        --brand: #1f4e9c;
        --brand-soft: #e4edfb;
        --sun: #f2b705;
        --hover: #f5f8fc;

        --pending: #b26a00;     --pending-bg: #fff1d6;
        --processing: #0b7285;  --processing-bg: #d8f3f8;
        --approved: #2b8a3e;    --approved-bg: #dcf5e3;
        --completed: #5f3dc4;   --completed-bg: #e8e1fb;
        --released: #1f4e9c;    --released-bg: #dbe8fb;
        --disapproved: #c92a2a; --disapproved-bg: #fde3e3;

        font-family: 'Public Sans', system-ui, sans-serif;
        color: var(--ink);
    }
    html[data-theme="dark"] .docs {
        --ink: #eef2f8;
        --muted: #9aa7bb;
        --paper: #1b2230;
        --wash: #141a25;
        --line: #303a4d;
        --brand: #7aa7f0;
        --brand-soft: #23324f;
        --hover: #212a3b;

        --pending: #f5b942;     --pending-bg: #3a2c10;
        --processing: #5ccfe0;  --processing-bg: #0f3038;
        --approved: #6fd48a;    --approved-bg: #143222;
        --completed: #b7a0f5;   --completed-bg: #2a2150;
        --released: #8db6f7;    --released-bg: #182c4d;
        --disapproved: #ff8f8f; --disapproved-bg: #401a1d;
    }
    .docs * { box-sizing: border-box; }
    .docs .display { font-family: 'Bricolage Grotesque', 'Public Sans', sans-serif; letter-spacing: -.02em; }
    .docs a:focus-visible, .docs button:focus-visible, .docs input:focus-visible, .docs tr:focus-visible {
        outline: 3px solid var(--sun); outline-offset: 2px;
    }

    /* ---------- Header ---------- */
    .docs-head { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; }
    .docs-head h1 { font-size: clamp(1.7rem, 3.2vw, 2.3rem); font-weight: 800; margin: 0; line-height: 1.1; }
    .docs-head p { margin: .35rem 0 0; color: var(--muted); }
    .docs-cta {
        display: inline-flex; align-items: center; gap: .5rem;
        background: var(--sun); color: #14213d; font-weight: 600; text-decoration: none;
        padding: .7rem 1.15rem; border-radius: 999px; box-shadow: 0 2px 0 rgba(0,0,0,.15);
        transition: transform .15s, box-shadow .15s;
    }
    .docs-cta:hover { color: #14213d; transform: translateY(-1px); box-shadow: 0 4px 0 rgba(0,0,0,.15); }

    /* ---------- Stage strip: the request pipeline ---------- */
    .stages {
        display: flex; gap: .5rem; overflow-x: auto; padding: .35rem;
        background: var(--wash); border-radius: 1.1rem; margin-bottom: 1rem;
        scrollbar-width: thin;
    }
    .stage {
        flex: 1 0 auto; min-width: 118px; display: flex; flex-direction: column; gap: .15rem;
        padding: .65rem .9rem; border-radius: .8rem; text-decoration: none; color: var(--muted);
        border: 1px solid transparent; transition: background .15s, color .15s;
    }
    .stage:hover { background: var(--paper); color: var(--ink); }
    .stage .s-top { display: flex; align-items: center; gap: .45rem; font-weight: 600; font-size: .9rem; }
    .stage .s-dot { width: .6rem; height: .6rem; border-radius: 50%; background: var(--c, var(--muted)); flex: none; }
    .stage .s-count { font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.5rem; font-weight: 800; line-height: 1; color: var(--ink); min-height: 1.5rem; }
    .stage.active { background: var(--paper); color: var(--ink); border-color: var(--line); box-shadow: 0 1px 0 var(--line), inset 0 -3px 0 var(--c, var(--brand)); }

    /* ---------- Search ---------- */
    .docs-search { position: relative; margin-bottom: 1rem; }
    .docs-search i.bi-search { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
    .docs-search input {
        width: 100%; padding: .85rem 7.5rem .85rem 2.6rem; border-radius: 999px;
        border: 1.5px solid var(--line); background: var(--paper); color: var(--ink); font-size: 1rem;
    }
    .docs-search input::placeholder { color: var(--muted); }
    .docs-search input:focus { border-color: var(--brand); outline: none; box-shadow: 0 0 0 4px var(--brand-soft); }
    .docs-search .go {
        position: absolute; right: .35rem; top: 50%; transform: translateY(-50%);
        background: var(--brand); color: #fff; border: 0; border-radius: 999px; padding: .5rem 1.1rem; font-weight: 600;
    }
    html[data-theme="dark"] .docs-search .go { color: #0f1620; }
    .filters-on { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-bottom: 1rem; color: var(--muted); font-size: .9rem; }
    .chip { display: inline-flex; align-items: center; gap: .4rem; background: var(--brand-soft); color: var(--brand); padding: .25rem .7rem; border-radius: 999px; font-weight: 500; }
    .filters-on a { color: var(--brand); font-weight: 600; text-decoration: none; }
    .filters-on a:hover { text-decoration: underline; }

    /* ---------- Alerts ---------- */
    .note { display: flex; gap: .75rem; align-items: flex-start; padding: .85rem 1rem; border-radius: .9rem; margin-bottom: 1rem; border: 1px solid; }
    .note.ok  { background: var(--approved-bg); color: var(--approved); border-color: var(--approved); }
    .note.bad { background: var(--disapproved-bg); color: var(--disapproved); border-color: var(--disapproved); }
    .note ul { margin: .35rem 0 0; padding-left: 1.1rem; }
    .note button { margin-left: auto; background: none; border: 0; color: inherit; font-size: 1.1rem; line-height: 1; }

    /* ---------- List ---------- */
    .board { background: var(--paper); border: 1px solid var(--line); border-radius: 1.1rem; overflow: hidden; }
    .board-head { display: flex; justify-content: space-between; align-items: center; padding: .9rem 1.25rem; border-bottom: 1px solid var(--line); }
    .board-head h2 { font-size: 1.05rem; font-weight: 800; margin: 0; }
    .board-head small { color: var(--muted); }
    .docs table { width: 100%; border-collapse: collapse; }
    .docs th { text-align: left; font-size: .82rem; font-weight: 600; color: var(--muted); padding: .7rem 1rem; background: var(--wash); border-bottom: 1px solid var(--line); }
    .docs td { padding: .9rem 1rem; border-bottom: 1px solid var(--line); vertical-align: middle; color: var(--ink); }
    .docs tbody tr:last-child td { border-bottom: 0; }
    .docs tbody tr.req { cursor: pointer; transition: background .12s; }
    .docs tbody tr.req:hover { background: var(--hover); }
    .docs td small, .muted { color: var(--muted); font-size: .82rem; }
    .code { font-weight: 600; color: var(--brand); text-decoration: none; white-space: nowrap; }
    .code:hover { text-decoration: underline; }

    .who { display: flex; align-items: center; gap: .75rem; }
    .avatar { width: 2.4rem; height: 2.4rem; border-radius: 50%; display: grid; place-items: center; flex: none; font-weight: 700; font-size: .85rem; background: var(--brand-soft); color: var(--brand); }
    .who b { display: block; font-weight: 600; }

    .pill { display: inline-flex; align-items: center; gap: .4rem; padding: .28rem .7rem; border-radius: 999px; font-size: .82rem; font-weight: 600; color: var(--c); background: var(--bg); }
    .pill::before { content: ''; width: .5rem; height: .5rem; border-radius: 50%; background: var(--c); }
    .track { display: flex; gap: 3px; margin-top: .45rem; width: 5.5rem; }
    .track i { flex: 1; height: 4px; border-radius: 2px; background: var(--line); }
    .track i.on { background: var(--c); }

    .view-btn { display: inline-flex; align-items: center; gap: .4rem; border: 1.5px solid var(--line); color: var(--ink); text-decoration: none; padding: .4rem .85rem; border-radius: 999px; font-size: .88rem; font-weight: 600; background: transparent; }
    .view-btn:hover { border-color: var(--brand); color: var(--brand); }

    /* per-status colour hooks */
    .st-pending { --c: var(--pending); --bg: var(--pending-bg); }
    .st-processing { --c: var(--processing); --bg: var(--processing-bg); }
    .st-approved { --c: var(--approved); --bg: var(--approved-bg); }
    .st-completed { --c: var(--completed); --bg: var(--completed-bg); }
    .st-released { --c: var(--released); --bg: var(--released-bg); }
    .st-disapproved { --c: var(--disapproved); --bg: var(--disapproved-bg); }
    .st-all { --c: var(--brand); }

    /* ---------- Empty ---------- */
    .empty { text-align: center; padding: 3.5rem 1rem; }
    .empty i { font-size: 2.4rem; color: var(--muted); }
    .empty h3 { font-size: 1.15rem; font-weight: 800; margin: .75rem 0 .25rem; }
    .empty p { color: var(--muted); margin: 0 auto 1.1rem; max-width: 34ch; }

    .board-foot { padding: .9rem 1.25rem; border-top: 1px solid var(--line); }
    .board-foot .pagination { margin: 0; }

    /* ---------- Mobile: rows become cards ---------- */
    @media (max-width: 767.98px) {
        .docs thead { display: none; }
        .docs table, .docs tbody, .docs tr, .docs td { display: block; width: 100%; }
        .docs tbody tr.req { padding: .9rem 1rem; border-bottom: 1px solid var(--line); }
        .docs tbody tr:last-child { border-bottom: 0; }
        .docs td { padding: .25rem 0; border: 0; }
        .docs td.c-action { padding-top: .6rem; }
        .docs td.c-action .view-btn { width: 100%; justify-content: center; }
        .docs-cta { width: 100%; justify-content: center; }
    }
    @media (prefers-reduced-motion: reduce) { .docs * { transition: none !important; } }
</style>
@endpush

@section('content')

@php
    $stages = [
        'pending'    => ['Pending',    'Waiting for review'],
        'processing' => ['Processing', 'Being prepared'],
        'approved'   => ['Approved',   'Ready to finalize'],
        'completed'  => ['Completed',  'Ready for pickup'],
        'released'   => ['Released',   'Given to resident'],
    ];
    $order      = array_keys($stages);
    $current    = request('status');
    $search     = trim((string) request('search'));
    $counts     = $statusCounts ?? null;   // optional: ['all'=>12,'pending'=>3,...] from the controller
    $isPaginator = method_exists($documents, 'total');
    $total      = $isPaginator ? $documents->total() : $documents->count();
    $tabUrl     = fn ($s) => route('documents.index', array_filter(['status' => $s, 'search' => $search ?: null]));
@endphp

<div class="docs">

    {{-- HEADER --}}
    <div class="docs-head">
        <div>
            <h1 class="display">Barangay Documents</h1>
            <p>Track each resident request from filing to release.</p>
        </div>
        <a href="{{ route('documents.create') }}" class="docs-cta">
            <i class="bi bi-plus-lg"></i> New request
        </a>
    </div>


    {{-- STAGE STRIP (click a stage to filter) --}}
    <nav class="stages" aria-label="Filter by status">
        <a href="{{ $tabUrl(null) }}" class="stage st-all {{ $current ? '' : 'active' }}" @if(!$current) aria-current="page" @endif>
            <span class="s-top"><span class="s-dot"></span>All requests</span>
            <span class="s-count">{{ $counts['all'] ?? ($current ? '' : $total) }}</span>
        </a>

        @foreach ($stages as $key => [$label, $hint])
            <a href="{{ $tabUrl($key) }}" title="{{ $hint }}"
               class="stage st-{{ $key }} {{ $current === $key ? 'active' : '' }}"
               @if($current === $key) aria-current="page" @endif>
                <span class="s-top"><span class="s-dot"></span>{{ $label }}</span>
                <span class="s-count">{{ $counts[$key] ?? ($current === $key ? $total : '') }}</span>
            </a>
        @endforeach

        <a href="{{ $tabUrl('disapproved') }}" title="Request was turned down"
           class="stage st-disapproved {{ $current === 'disapproved' ? 'active' : '' }}"
           @if($current === 'disapproved') aria-current="page" @endif>
            <span class="s-top"><span class="s-dot"></span>Disapproved</span>
            <span class="s-count">{{ $counts['disapproved'] ?? ($current === 'disapproved' ? $total : '') }}</span>
        </a>
    </nav>

    {{-- SEARCH --}}
    <form method="GET" action="{{ route('documents.index') }}" class="docs-search" role="search">
        @if ($current) <input type="hidden" name="status" value="{{ $current }}"> @endif
        <i class="bi bi-search"></i>
        <label for="search" class="visually-hidden">Search requests</label>
        <input type="search" id="search" name="search" value="{{ $search }}"
               placeholder="Search by resident name, request code, or document type">
        <button type="submit" class="go">Search</button>
    </form>

    @if ($search || $current)
        <div class="filters-on">
            <span>Showing {{ $total }} {{ \Illuminate\Support\Str::plural('request', $total) }}</span>
            @if ($current) <span class="chip">Status: {{ ucfirst($current) }}</span> @endif
            @if ($search)  <span class="chip">“{{ $search }}”</span> @endif
            <a href="{{ route('documents.index') }}">Clear filters</a>
        </div>
    @endif

    {{-- REQUESTS --}}
    <div class="board">
        <div class="board-head">
            <h2 class="display">Document requests</h2>
            <small>{{ $total }} {{ \Illuminate\Support\Str::plural('request', $total) }}</small>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Request</th>
                    <th>Resident</th>
                    <th>Document</th>
                    <th>Requested</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($documents as $document)
                @php
                    $resident = $document->resident;
                    $name = trim(preg_replace('/\s+/', ' ',
                        ($resident->first_name ?? '') . ' ' . ($resident->middle_name ?? '') . ' ' . ($resident->last_name ?? '')));
                    $initials = $resident
                        ? strtoupper(mb_substr($resident->first_name ?? '', 0, 1) . mb_substr($resident->last_name ?? '', 0, 1))
                        : '?';
                    $status = strtolower((string) $document->status);
                    $known  = array_key_exists($status, $stages) || $status === 'disapproved';
                    $idx    = array_search($status, $order);
                    $when   = $document->requested_at ?? $document->created_at;
                    $url    = route('documents.show', $document);
                @endphp

                <tr class="req" data-href="{{ $url }}">
                    <td class="c-code">
                        <a href="{{ $url }}" class="code">{{ $document->request_code }}</a>
                    </td>

                    <td>
                        <div class="who">
                            <span class="avatar" aria-hidden="true">{{ $initials }}</span>
                            <div>
                                <b>{{ $name ?: 'No resident assigned' }}</b>
                                @if ($resident)
                                    <small>Resident #{{ str_pad($resident->id, 5, '0', STR_PAD_LEFT) }}</small>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td>
                        <div>{{ $document->document_type }}</div>
                        @if ($document->purpose)
                            <small>For: {{ \Illuminate\Support\Str::limit($document->purpose, 40) }}</small>
                        @endif
                    </td>

                    <td>
                        @if ($when)
                            <div title="{{ $when->format('M d, Y h:i A') }}">{{ $when->diffForHumans() }}</div>
                            <small>{{ $when->format('M d, Y') }}</small>
                        @else
                            <small>No date</small>
                        @endif
                    </td>

                    <td>
                        <span class="pill {{ $known ? 'st-'.$status : '' }}" @unless($known) style="--c:var(--muted);--bg:var(--wash)" @endunless>
                            {{ $known ? ($stages[$status][0] ?? 'Disapproved') : ($document->status ?: 'Unknown') }}
                        </span>
                        @if ($known)
                            <div class="track st-{{ $status }}" aria-hidden="true">
                                @foreach ($order as $i => $k)
                                    <i class="{{ $status === 'disapproved' || ($idx !== false && $i <= $idx) ? 'on' : '' }}"></i>
                                @endforeach
                            </div>
                        @endif
                    </td>

                    <td class="text-center c-action">
                        <a href="{{ $url }}" class="view-btn">
                            <i class="bi bi-eye"></i> Preview
                        </a>
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="6">
                        <div class="empty">
                            <i class="bi bi-inbox"></i>
                            @if ($search || $current)
                                <h3 class="display">No matching requests</h3>
                                <p>Try a different name or code, or clear the filters to see everything.</p>
                                <a href="{{ route('documents.index') }}" class="view-btn">Clear filters</a>
                            @else
                                <h3 class="display">No requests yet</h3>
                                <p>Requests from residents will show up here once they are filed.</p>
                                <a href="{{ route('documents.create') }}" class="docs-cta">
                                    <i class="bi bi-plus-lg"></i> New request
                                </a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        @if (method_exists($documents, 'links') && $documents->hasPages())
            <div class="board-foot">
                {{ $documents->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>

@endsection

@push('scripts')
<script>
    // Make the whole row clickable without breaking real links/buttons.
    document.querySelectorAll('.docs tr.req').forEach(function (row) {
        row.addEventListener('click', function (e) {
            if (e.target.closest('a, button')) return;
            window.location = row.dataset.href;
        });
    });
</script>
@endpush
