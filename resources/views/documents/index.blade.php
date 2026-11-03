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
       (Bootstrap's [data-bs-theme="dark"] is supported too)
       Every text/background pair is picked for strong contrast
       in BOTH modes.
       ========================================================= */
    .docs {
        --ink: #14213d;
        --muted: #4f5b6e;
        --paper: #ffffff;
        --wash: #e9eef5;
        --line: #cdd6e3;
        --brand: #1a469a;
        --brand-soft: #e0eafb;
        --on-brand: #ffffff;
        --sun: #f2b705;
        --on-sun: #14213d;
        --hover: #f1f5fb;
        --shadow: 0 10px 30px -18px rgba(20, 33, 61, .45);

        --pending: #8f5200;     --pending-bg: #ffeccb;
        --processing: #07606f;  --processing-bg: #d2f0f6;
        --approved: #1f7a32;    --approved-bg: #d6f3de;
        --completed: #5434b8;   --completed-bg: #e6defa;
        --released: #1a469a;    --released-bg: #d9e6fb;
        --disapproved: #b42323; --disapproved-bg: #fcdede;

        font-family: 'Public Sans', system-ui, sans-serif;
        color: var(--ink);
    }
    html[data-theme="dark"] .docs,
    [data-bs-theme="dark"] .docs {
        --ink: #f1f5fb;
        --muted: #aab6c9;
        --paper: #1b2433;
        --wash: #111823;
        --line: #38455c;
        --brand: #8db6f7;
        --brand-soft: #243556;
        --on-brand: #0e1624;
        --sun: #f5c23a;
        --on-sun: #14213d;
        --hover: #232e42;
        --shadow: 0 10px 30px -18px rgba(0, 0, 0, .9);

        --pending: #ffc660;     --pending-bg: #3d2d0e;
        --processing: #6fdbeb;  --processing-bg: #0e333c;
        --approved: #7fe099;    --approved-bg: #123525;
        --completed: #c4b0fa;   --completed-bg: #2b2352;
        --released: #9cc2fa;    --released-bg: #182f52;
        --disapproved: #ff9c9c; --disapproved-bg: #461b1f;
    }
    .docs * { box-sizing: border-box; }
    .docs .display { font-family: 'Bricolage Grotesque', 'Public Sans', sans-serif; letter-spacing: -.02em; }
    .docs a:focus-visible, .docs button:focus-visible, .docs input:focus-visible, .docs tr:focus-visible {
        outline: 3px solid var(--sun); outline-offset: 2px;
    }

    /* ---------- Header ---------- */
    .docs-head { display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: flex-end; margin-bottom: 1.5rem; }
    .docs-head h1 { font-size: clamp(1.7rem, 3.2vw, 2.3rem); font-weight: 800; margin: 0; line-height: 1.1; color: var(--ink); }
    .docs-head p { margin: .35rem 0 0; color: var(--muted); }
    .docs-cta {
        display: inline-flex; align-items: center; gap: .5rem;
        background: var(--sun); color: var(--on-sun); font-weight: 700; text-decoration: none;
        padding: .7rem 1.15rem; border-radius: 999px; box-shadow: 0 2px 0 rgba(0,0,0,.25);
        transition: transform .15s, box-shadow .15s;
    }
    .docs-cta:hover { color: var(--on-sun); transform: translateY(-1px); box-shadow: 0 4px 0 rgba(0,0,0,.25); }

    /* ---------- Stage strip: the request pipeline ---------- */
    .stages {
        display: flex; gap: .5rem; overflow-x: auto; padding: .4rem;
        background: var(--wash); border: 1px solid var(--line); border-radius: 1.1rem; margin-bottom: 1rem;
        scrollbar-width: thin; transition: opacity .15s;
    }
    .stage {
        flex: 1 0 auto; min-width: 118px; display: flex; flex-direction: column; gap: .15rem;
        padding: .65rem .9rem; border-radius: .8rem; text-decoration: none; color: var(--muted);
        border: 1px solid transparent; transition: background .15s, color .15s;
    }
    .stage:hover { background: var(--paper); color: var(--ink); }
    .stage .s-top { display: flex; align-items: center; gap: .45rem; font-weight: 600; font-size: .9rem; }
    .stage .s-dot { width: .65rem; height: .65rem; border-radius: 50%; background: var(--c, var(--muted)); flex: none; }
    .stage .s-count { font-family: 'Bricolage Grotesque', sans-serif; font-size: 1.5rem; font-weight: 800; line-height: 1; color: var(--ink); min-height: 1.5rem; }
    .stage.active { background: var(--paper); color: var(--ink); border-color: var(--line); box-shadow: var(--shadow), inset 0 -3px 0 var(--c, var(--brand)); }

    /* ---------- Search (live) ---------- */
    .docs-search { position: relative; margin-bottom: 1rem; }
    .docs-search i.bi-search { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
    .docs-search input {
        width: 100%; padding: .85rem 3rem .85rem 2.6rem; border-radius: 999px;
        border: 1.5px solid var(--line); background: var(--paper); color: var(--ink); font-size: 1rem;
        transition: border-color .15s, box-shadow .15s;
    }
    .docs-search input::placeholder { color: var(--muted); opacity: .9; }
    .docs-search input::-webkit-search-cancel-button { display: none; }
    .docs-search input:hover { border-color: var(--brand); }
    .docs-search input:focus { border-color: var(--brand); outline: none; box-shadow: 0 0 0 4px var(--brand-soft); }
    .docs-search .clear {
        position: absolute; right: .55rem; top: 50%; transform: translateY(-50%);
        width: 2rem; height: 2rem; border: 0; border-radius: 50%; display: grid; place-items: center;
        background: var(--wash); color: var(--ink); cursor: pointer;
    }
    .docs-search .clear:hover { background: var(--brand-soft); color: var(--brand); }
    .docs-search .clear[hidden] { display: none; }
    .docs-search .spin {
        position: absolute; right: 3rem; top: 50%; width: 1rem; height: 1rem; margin-top: -.5rem;
        border: 2px solid var(--line); border-top-color: var(--brand); border-radius: 50%;
        animation: docs-spin .7s linear infinite; opacity: 0; pointer-events: none; transition: opacity .15s;
    }
    .docs-search.is-busy .spin { opacity: 1; }
    @keyframes docs-spin { to { transform: rotate(360deg); } }

    .filters-on { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-bottom: 1rem; color: var(--muted); font-size: .9rem; }
    .chip { display: inline-flex; align-items: center; gap: .4rem; background: var(--brand-soft); color: var(--brand); padding: .25rem .7rem; border-radius: 999px; font-weight: 600; }
    .filters-on a { color: var(--brand); font-weight: 700; text-decoration: none; }
    .filters-on a:hover { text-decoration: underline; }

    /* results fade while a live search loads */
    #docsResults, #docsStages { transition: opacity .15s; }
    #docsResults.is-loading, #docsStages.is-loading { opacity: .55; }

    /* ---------- Alerts ---------- */
    .note { display: flex; gap: .75rem; align-items: flex-start; padding: .85rem 1rem; border-radius: .9rem; margin-bottom: 1rem; border: 1px solid; }
    .note.ok  { background: var(--approved-bg); color: var(--approved); border-color: var(--approved); }
    .note.bad { background: var(--disapproved-bg); color: var(--disapproved); border-color: var(--disapproved); }
    .note ul { margin: .35rem 0 0; padding-left: 1.1rem; }
    .note button { margin-left: auto; background: none; border: 0; color: inherit; font-size: 1.1rem; line-height: 1; }

    /* ---------- List ---------- */
    .board { background: var(--paper); border: 1px solid var(--line); border-radius: 1.1rem; overflow: hidden; box-shadow: var(--shadow); }
    .board-head { display: flex; justify-content: space-between; align-items: center; padding: .9rem 1.25rem; border-bottom: 1px solid var(--line); }
    .board-head h2 { font-size: 1.05rem; font-weight: 800; margin: 0; color: var(--ink); }
    .board-head small { color: var(--muted); }
    .docs table { width: 100%; border-collapse: collapse; background: transparent; color: var(--ink); }
    .docs th { text-align: left; font-size: .82rem; font-weight: 700; color: var(--muted); padding: .7rem 1rem; background: var(--wash); border-bottom: 1px solid var(--line); }
    .docs td { padding: .9rem 1rem; border-bottom: 1px solid var(--line); vertical-align: middle; color: var(--ink); background: transparent; }
    .docs tbody tr:last-child td { border-bottom: 0; }
    .docs tbody tr.req { cursor: pointer; transition: background .12s; }
    .docs tbody tr.req:hover { background: var(--hover); }
    .docs td small, .muted { color: var(--muted); font-size: .82rem; }
    .code { font-weight: 700; color: var(--brand); text-decoration: none; white-space: nowrap; }
    .code:hover { text-decoration: underline; }

    .who { display: flex; align-items: center; gap: .75rem; }
    .avatar { width: 2.4rem; height: 2.4rem; border-radius: 50%; display: grid; place-items: center; flex: none; font-weight: 700; font-size: .85rem; background: var(--brand-soft); color: var(--brand); }
    .who b { display: block; font-weight: 600; color: var(--ink); }

    .pill { display: inline-flex; align-items: center; gap: .4rem; padding: .28rem .7rem; border-radius: 999px; font-size: .82rem; font-weight: 700; color: var(--c); background: var(--bg); border: 1px solid color-mix(in srgb, var(--c) 35%, transparent); }
    .pill::before { content: ''; width: .5rem; height: .5rem; border-radius: 50%; background: var(--c); }
    .track { display: flex; gap: 3px; margin-top: .45rem; width: 5.5rem; }
    .track i { flex: 1; height: 4px; border-radius: 2px; background: var(--line); }
    .track i.on { background: var(--c); }

    .view-btn { display: inline-flex; align-items: center; gap: .4rem; border: 1.5px solid var(--line); color: var(--ink); text-decoration: none; padding: .4rem .85rem; border-radius: 999px; font-size: .88rem; font-weight: 600; background: transparent; transition: border-color .15s, color .15s, background .15s; }
    .view-btn:hover { border-color: var(--brand); color: var(--brand); background: var(--brand-soft); }

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
    .empty h3 { font-size: 1.15rem; font-weight: 800; margin: .75rem 0 .25rem; color: var(--ink); }
    .empty p { color: var(--muted); margin: 0 auto 1.1rem; max-width: 34ch; }

    /* ---------- Pagination (Bootstrap or Tailwind markup, both themed) ---------- */
    .board-foot { padding: .9rem 1.25rem; border-top: 1px solid var(--line); color: var(--muted); }
    .board-foot .pagination { margin: 0; }
    .board-foot .page-link { background: var(--paper); border-color: var(--line); color: var(--brand); }
    .board-foot .page-link:hover { background: var(--brand-soft); color: var(--brand); }
    .board-foot .page-item.active .page-link { background: var(--brand); border-color: var(--brand); color: var(--on-brand); }
    .board-foot .page-item.disabled .page-link { background: var(--wash); border-color: var(--line); color: var(--muted); }
    .board-foot [class*="bg-white"] { background: var(--paper) !important; color: var(--ink) !important; border-color: var(--line) !important; }
    .board-foot [class*="text-gray"] { color: var(--muted) !important; }
    .board-foot [class*="border-gray"] { border-color: var(--line) !important; }
    .board-foot [class*="bg-gray"] { background: var(--wash) !important; }
    .board-foot a[class*="hover"]:hover { background: var(--brand-soft) !important; }

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
    @media (prefers-reduced-motion: reduce) {
        .docs * { transition: none !important; }
        .docs-search .spin { animation-duration: 1.6s; }
    }
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
    /*
     * Progress lines = the 6 visible Process Journey steps on documents.show:
     * 0 Request Created, 1 Verified, 2 Approved, 3 Printed, 4 Signed & Sealed, 5 Released.
     * Driven by the SAVED workflow_stage (same mapping as show.blade.php).
     * Inspection / ID Verified / Eligible are internal checks inside "Verified".
     */
    $stageSlot = [
        \App\Models\DocumentRequest::STAGE_REQUESTED     => 0,
        \App\Models\DocumentRequest::STAGE_INSPECTION    => 1,
        \App\Models\DocumentRequest::STAGE_ID_VERIFIED   => 1,
        \App\Models\DocumentRequest::STAGE_ELIGIBLE      => 1,
        \App\Models\DocumentRequest::STAGE_APPROVED      => 2,
        \App\Models\DocumentRequest::STAGE_PRINTED       => 3,
        \App\Models\DocumentRequest::STAGE_SIGNED_SEALED => 4,
        \App\Models\DocumentRequest::STAGE_RELEASED      => 5,
    ];
    /* Fallback only for legacy rows that have no workflow_stage saved. */
    $statusSlot  = ['pending' => 0, 'processing' => 1, 'approved' => 2, 'completed' => 3, 'released' => 5];
    $journeySlots = max($stageSlot) + 1;   // = 6
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


    {{-- STAGE STRIP (click a stage to filter) : All, Pending, Approved, Released / Completed, Disapproved --}}
    <div id="docsStages">
        <nav class="stages" aria-label="Filter by status">
            <a href="{{ $tabUrl(null) }}" class="stage st-all {{ $current ? '' : 'active' }}" @if(!$current) aria-current="page" @endif>
                <span class="s-top"><span class="s-dot"></span>All requests</span>
                <span class="s-count">{{ $counts['all'] ?? ($current ? '' : $total) }}</span>
            </a>

            @foreach ($stages as $key => [$label, $hint])
                @continue(! in_array($key, ['pending', 'approved'], true))
                <a href="{{ $tabUrl($key) }}" title="{{ $hint }}"
                   class="stage st-{{ $key }} {{ $current === $key ? 'active' : '' }}"
                   @if($current === $key) aria-current="page" @endif>
                    <span class="s-top"><span class="s-dot"></span>{{ $label }}</span>
                    <span class="s-count">{{ $counts[$key] ?? ($current === $key ? $total : '') }}</span>
                </a>
            @endforeach

            {{-- Released + Completed are shown as ONE card (filter key: released) --}}
            <a href="{{ $tabUrl('released') }}" title="Completed or given to resident"
               class="stage st-released {{ in_array($current, ['released', 'completed'], true) ? 'active' : '' }}"
               @if(in_array($current, ['released', 'completed'], true)) aria-current="page" @endif>
                <span class="s-top"><span class="s-dot"></span>Released / Completed</span>
                <span class="s-count">{{ $counts['released'] ?? (in_array($current, ['released', 'completed'], true) ? $total : '') }}</span>
            </a>

            <a href="{{ $tabUrl('disapproved') }}" title="Request was turned down"
               class="stage st-disapproved {{ $current === 'disapproved' ? 'active' : '' }}"
               @if($current === 'disapproved') aria-current="page" @endif>
                <span class="s-top"><span class="s-dot"></span>Disapproved</span>
                <span class="s-count">{{ $counts['disapproved'] ?? ($current === 'disapproved' ? $total : '') }}</span>
            </a>
        </nav>
    </div>

    {{-- SEARCH: filters automatically while typing (no button needed) --}}
    <form method="GET" action="{{ route('documents.index') }}" class="docs-search" id="docsSearchForm" role="search" autocomplete="off">
        @if ($current) <input type="hidden" name="status" value="{{ $current }}"> @endif
        <i class="bi bi-search"></i>
        <label for="search" class="visually-hidden">Search requests</label>
        <input type="search" id="search" name="search" value="{{ $search }}"
               placeholder="Type a resident name, request code, or document type">
        <span class="spin" aria-hidden="true"></span>
        <button type="button" class="clear" id="docsSearchClear" aria-label="Clear search" @unless($search) hidden @endunless>
            <i class="bi bi-x-lg"></i>
        </button>
        <noscript><button type="submit" class="view-btn mt-2">Search</button></noscript>
    </form>

    {{-- FILTERS + REQUESTS (replaced live as you type) --}}
    <div id="docsResults" aria-live="polite">

        @if ($search || $current)
            <div class="filters-on">
                <span>Showing {{ $total }} {{ \Illuminate\Support\Str::plural('request', $total) }}</span>
                @if ($current) <span class="chip">Status: {{ $current === 'released' ? 'Released / Completed' : ucfirst($current) }}</span> @endif
                @if ($search)  <span class="chip">“{{ $search }}”</span> @endif
                <a href="{{ route('documents.index') }}">Clear filters</a>
            </div>
        @endif

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
                        $slot   = $stageSlot[$document->workflow_stage] ?? ($statusSlot[$status] ?? null);
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
                                    @for ($i = 0; $i < $journeySlots; $i++)
                                        <i class="{{ $status === 'disapproved' || ($slot !== null && $i <= $slot) ? 'on' : '' }}"></i>
                                    @endfor
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

</div>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var form     = document.getElementById('docsSearchForm');
    var input    = document.getElementById('search');
    var clearBtn = document.getElementById('docsSearchClear');
    if (!form || !input) return;

    var LIVE_IDS = ['docsStages', 'docsResults'];   // parts swapped on every search
    var DELAY = 300;                                // ms to wait after the last keystroke
    var timer = null, controller = null, lastQuery = input.value.trim();

    function searchUrl() {
        var url = new URL(form.getAttribute('action') || window.location.pathname, window.location.origin);
        var status = form.querySelector('input[name="status"]');
        if (status && status.value) url.searchParams.set('status', status.value);
        var q = input.value.trim();
        if (q) url.searchParams.set('search', q);
        return url.toString();
    }

    function setBusy(on) {
        form.classList.toggle('is-busy', on);
        LIVE_IDS.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.classList.toggle('is-loading', on);
        });
    }

    function load(url) {
        if (controller) controller.abort();
        controller = new AbortController();
        setBusy(true);

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            credentials: 'same-origin',
            signal: controller.signal
        })
        .then(function (res) {
            if (!res.ok || res.redirected && res.url.indexOf('login') !== -1) throw new Error('bad response');
            return res.text();
        })
        .then(function (html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var swapped = LIVE_IDS.every(function (id) { return doc.getElementById(id) && document.getElementById(id); });
            if (!swapped) { window.location.href = url; return; }   // unexpected page: do a normal visit
            LIVE_IDS.forEach(function (id) {
                document.getElementById(id).replaceWith(doc.getElementById(id));
            });
            history.replaceState(null, '', url);
            setBusy(false);
        })
        .catch(function (err) {
            if (err && err.name === 'AbortError') return;           // a newer search replaced this one
            window.location.href = url;                             // network/server problem: normal visit
        });
    }

    function syncClear() { if (clearBtn) clearBtn.hidden = input.value === ''; }

    function run() {
        var q = input.value.trim();
        if (q === lastQuery) return;
        lastQuery = q;
        load(searchUrl());
    }

    // Filter automatically while typing
    input.addEventListener('input', function () {
        syncClear();
        clearTimeout(timer);
        timer = setTimeout(run, DELAY);
    });

    // Enter searches immediately (no page reload)
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(timer);
        lastQuery = input.value.trim();
        load(searchUrl());
    });

    // Esc clears the box
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && input.value !== '') {
            input.value = '';
            syncClear();
            clearTimeout(timer);
            run();
        }
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            input.value = '';
            syncClear();
            clearTimeout(timer);
            run();
            input.focus();
        });
    }

    // Whole row opens the request, and it keeps working after live updates.
    document.addEventListener('click', function (e) {
        var row = e.target.closest && e.target.closest('.docs tr.req');
        if (row && !e.target.closest('a, button')) {
            window.location = row.dataset.href;
            return;
        }

        // Pagination stays on the page and keeps the current search.
        var page = e.target.closest && e.target.closest('.docs .board-foot a[href]');
        if (page && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
            e.preventDefault();
            load(page.href);
        }
    });

    // Browser Back/Forward after typing: reload to the matching results.
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) window.location.reload();
    });
})();
</script>
@endpush
