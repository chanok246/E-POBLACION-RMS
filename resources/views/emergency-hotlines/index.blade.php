@extends('layouts.app')

@section('title', 'Emergency Hotlines')

@push('styles')
{{-- Stop phones from auto-turning numbers into call links --}}
<meta name="format-detection" content="telephone=no">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Familjen+Grotesk:wght@500;600;700&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       EMERGENCY HOTLINES - reference board
       Numbers are READ / COPY only. No tel: links anywhere.
       ========================================================= */
    .hot {
        --ink: #0f1a2b; --muted: #58667b; --paper: #ffffff; --wash: #f2f5f9; --line: #e1e7ef;
        --brand: #2749c9; --brand-soft: #e8edff; --sun: #ffc933; --hover: #f6f8fc;
        --ok: #1e8e4a; --ok-bg: #d9f5e4; --off: #6b778a; --off-bg: #eaeef4; --bad: #c92a2a; --bad-bg: #fde3e3;

        --c-barangay: #2749c9; --b-barangay: #e8edff;
        --c-police:   #4a3fd6; --b-police:   #ebe9ff;
        --c-fire:     #e03131; --b-fire:     #ffe4e1;
        --c-medical:  #d6336c; --b-medical:  #ffe3ee;
        --c-disaster: #c46500; --b-disaster: #fff0d3;
        --c-rescue:   #0b7285; --b-rescue:   #d6f4f8;
        --c-other:    #58667b; --b-other:    #eaeef4;

        font-family: 'Figtree', system-ui, sans-serif; color: var(--ink); max-width: 1500px; margin: 0 auto;
    }
    html[data-theme="dark"] .hot {
        --ink: #eef2f8; --muted: #9aa7bb; --paper: #182030; --wash: #111722; --line: #2b3549;
        --brand: #8aa8ff; --brand-soft: #222f57; --hover: #1e2740;
        --ok: #6fd48a; --ok-bg: #143222; --off: #aab4c3; --off-bg: #273043; --bad: #ff8f8f; --bad-bg: #401a1d;

        --c-barangay: #8aa8ff; --b-barangay: #222f57;
        --c-police:   #a59cff; --b-police:   #2a2757;
        --c-fire:     #ff8787; --b-fire:     #442024;
        --c-medical:  #faa2c1; --b-medical:  #43202f;
        --c-disaster: #ffc078; --b-disaster: #3d2e10;
        --c-rescue:   #5ccfe0; --b-rescue:   #0f3038;
        --c-other:    #b0b8c5; --b-other:    #273043;
    }
    .hot * { box-sizing: border-box; }
    .hot .display { font-family: 'Familjen Grotesk', 'Figtree', sans-serif; letter-spacing: -.025em; }
    .hot a:focus-visible, .hot button:focus-visible, .hot input:focus-visible, .hot .num:focus-visible { outline: 3px solid var(--sun); outline-offset: 2px; }

    .cat-barangay { --c: var(--c-barangay); --cb: var(--b-barangay); }
    .cat-police   { --c: var(--c-police);   --cb: var(--b-police); }
    .cat-fire     { --c: var(--c-fire);     --cb: var(--b-fire); }
    .cat-medical  { --c: var(--c-medical);  --cb: var(--b-medical); }
    .cat-disaster { --c: var(--c-disaster); --cb: var(--b-disaster); }
    .cat-rescue   { --c: var(--c-rescue);   --cb: var(--b-rescue); }
    .cat-other    { --c: var(--c-other);    --cb: var(--b-other); }

    /* ---------- header ---------- */
    .head { display: flex; flex-wrap: wrap; gap: 1rem 2rem; justify-content: space-between; align-items: flex-end; margin-bottom: 1.25rem; }
    .head h1 { font-size: clamp(2rem, 4.4vw, 3.1rem); font-weight: 700; margin: 0; line-height: 1; }
    .head p { margin: .55rem 0 0; color: var(--muted); max-width: 52ch; }
    .cta { display: inline-flex; align-items: center; gap: .5rem; background: var(--ink); color: var(--paper); font-weight: 600; text-decoration: none; padding: .75rem 1.25rem; border-radius: 14px; transition: background .15s, color .15s; }
    .cta:hover { background: var(--brand); color: #fff; }
    html[data-theme="dark"] .cta:hover { color: #0f1620; }

    .readonly-note { display: flex; align-items: center; gap: .6rem; padding: .65rem .9rem; margin-bottom: 1.25rem; border-radius: 12px; background: var(--brand-soft); color: var(--ink); font-size: .9rem; }
    .readonly-note i { color: var(--brand); font-size: 1.05rem; }

    .note { display: flex; gap: .75rem; padding: .85rem 1rem; border-radius: 14px; margin-bottom: 1rem; border: 1px solid var(--bad); background: var(--bad-bg); color: var(--bad); }
    .note ul { margin: .35rem 0 0; padding-left: 1.1rem; }
    .note div { color: var(--ink); }
    .note strong, .note i { color: var(--bad); }
    .note .x { margin-left: auto; background: none; border: 0; color: var(--bad); font-size: 1.2rem; line-height: 1; }

    /* ---------- layout: rail + board ---------- */
    .shell { display: grid; grid-template-columns: 250px minmax(0, 1fr); gap: 1.75rem; align-items: start; }
    @media (max-width: 991.98px) { .shell { grid-template-columns: 1fr; gap: 1rem; } }

    .rail { position: sticky; top: 1rem; display: flex; flex-direction: column; gap: .2rem; }
    @media (max-width: 991.98px) { .rail { position: static; flex-direction: row; overflow-x: auto; gap: .5rem; padding-bottom: .4rem; scrollbar-width: thin; } }
    .rail-title { font-weight: 700; font-size: .95rem; margin: 0 0 .5rem .4rem; color: var(--muted); }
    @media (max-width: 991.98px) { .rail-title { display: none; } }
    .ritem { flex: none; display: flex; align-items: center; gap: .7rem; padding: .6rem .75rem; border-radius: 14px; text-decoration: none; color: var(--ink); font-weight: 600; font-size: .95rem; border: 1.5px solid transparent; transition: background .15s; }
    .ritem .dot { width: 2rem; height: 2rem; border-radius: 10px; display: grid; place-items: center; background: var(--cb, var(--brand-soft)); color: var(--c, var(--brand)); font-size: 1rem; flex: none; }
    .ritem:hover { background: var(--hover); color: var(--ink); }
    .ritem.active { background: var(--paper); border-color: var(--c, var(--brand)); }
    @media (max-width: 991.98px) { .ritem { border-color: var(--line); background: var(--paper); padding: .4rem .8rem .4rem .45rem; border-radius: 999px; } .ritem .dot { width: 1.6rem; height: 1.6rem; border-radius: 50%; font-size: .85rem; } }

    /* ---------- toolbar ---------- */
    .toolbar { display: flex; flex-wrap: wrap; gap: .75rem; align-items: center; margin-bottom: .9rem; }
    .search { position: relative; flex: 1 1 300px; }
    .search i { position: absolute; left: 1.1rem; top: 50%; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
    .search input { width: 100%; min-height: 52px; padding: .6rem 1rem .6rem 2.8rem; border-radius: 16px; border: 1.5px solid var(--line); background: var(--paper); color: var(--ink); font: inherit; }
    .search input::placeholder { color: var(--muted); }
    .search input:focus { border-color: var(--brand); outline: none; box-shadow: 0 0 0 4px var(--brand-soft); }

    .seg { display: inline-flex; padding: .25rem; border-radius: 16px; background: var(--wash); border: 1px solid var(--line); }
    .seg a { display: inline-flex; align-items: center; gap: .45rem; padding: .5rem .9rem; border-radius: 12px; text-decoration: none; color: var(--muted); font-weight: 600; font-size: .9rem; }
    .seg a b { font-family: 'Familjen Grotesk', sans-serif; font-size: .95rem; color: var(--ink); }
    .seg a:hover { color: var(--ink); }
    .seg a.on { background: var(--paper); color: var(--ink); box-shadow: 0 1px 3px rgba(10,20,40,.14); }
    .reset { display: inline-flex; align-items: center; gap: .4rem; min-height: 52px; padding: 0 1rem; border-radius: 16px; color: var(--muted); text-decoration: none; font-weight: 600; }
    .reset:hover { color: var(--brand); }

    .result { color: var(--muted); font-size: .9rem; margin: 0 0 .9rem .2rem; }
    .result b { color: var(--ink); }

    /* ---------- cards ---------- */
    .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    @media (max-width: 767.98px) { .grid { grid-template-columns: 1fr; } }
    .hcard { display: flex; flex-direction: column; background: var(--paper); border: 1px solid var(--line); border-radius: 22px; padding: 1rem; }
    .h-top { display: flex; gap: .75rem; align-items: center; padding: .1rem .1rem 0; }
    .h-ic { width: 2.6rem; height: 2.6rem; border-radius: 50%; display: grid; place-items: center; font-size: 1.2rem; background: var(--cb); color: var(--c); flex: none; }
    .h-id { min-width: 0; flex: 1; }
    .h-name { font-weight: 700; font-size: 1.05rem; line-height: 1.2; overflow-wrap: anywhere; }
    .h-cat { font-size: .84rem; color: var(--muted); }

    /* the number plate - the memorable element */
    .plate { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-top: .9rem; padding: .85rem .9rem .85rem 1.1rem; border-radius: 16px; background: var(--cb); }
    .num { flex: 1; min-width: 0; font-family: 'Familjen Grotesk', sans-serif; font-weight: 700; font-size: clamp(1.5rem, 2.4vw, 2rem); line-height: 1.1; letter-spacing: .01em; font-variant-numeric: tabular-nums; color: var(--c); overflow-wrap: anywhere; cursor: copy; user-select: all; -webkit-user-select: all; border-radius: 8px; }
    .copy-btn { flex: none; display: inline-flex; align-items: center; gap: .4rem; height: 2.5rem; padding: 0 .9rem; border-radius: 12px; border: 0; background: var(--paper); color: var(--ink); font: inherit; font-weight: 600; font-size: .86rem; }
    .copy-btn:hover { color: var(--c); }
    .copy-btn.done { background: var(--c); color: #fff; }
    html[data-theme="dark"] .copy-btn.done { color: #0f1620; }

    .h-desc { color: var(--muted); font-size: .92rem; margin: .8rem .15rem 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

    .meta { display: grid; gap: .45rem; margin: .8rem .15rem 0; font-size: .9rem; }
    .meta > div { display: flex; gap: .6rem; align-items: baseline; }
    .meta i { color: var(--muted); }
    .meta span { overflow-wrap: anywhere; }
    .alt { display: inline-flex; align-items: center; gap: .5rem; flex-wrap: wrap; }
    .alt .num-sm { font-weight: 600; font-variant-numeric: tabular-nums; user-select: all; -webkit-user-select: all; cursor: copy; }
    .alt small { color: var(--muted); }

    .h-foot { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-top: auto; padding: 1rem .15rem 0; }
    .pill { display: inline-flex; align-items: center; gap: .4rem; padding: .25rem .7rem; border-radius: 999px; font-size: .8rem; font-weight: 600; }
    .pill::before { content: ''; width: .5rem; height: .5rem; border-radius: 50%; background: currentColor; }
    .pill.on { color: var(--ok); background: var(--ok-bg); }
    .pill.off { color: var(--off); background: var(--off-bg); }
    .view-btn { display: inline-flex; align-items: center; gap: .4rem; border: 1.5px solid var(--line); color: var(--ink); text-decoration: none; padding: .35rem .85rem; border-radius: 12px; font-size: .86rem; font-weight: 600; background: var(--paper); }
    .view-btn:hover { border-color: var(--brand); color: var(--brand); }

    .hcard.off .plate { background: var(--off-bg); }
    .hcard.off .num { color: var(--off); }
    .hcard.off .h-ic { background: var(--off-bg); color: var(--off); }
    .hcard.off .h-desc, .hcard.off .meta { opacity: .65; }

    .kebab { width: 2.2rem; height: 2.2rem; border-radius: 50%; border: 0; background: transparent; color: var(--muted); display: grid; place-items: center; }
    .kebab:hover, .kebab[aria-expanded="true"] { background: var(--wash); color: var(--ink); }
    .hot .dropdown-menu { background: var(--paper); border: 1px solid var(--line); border-radius: 14px; padding: .35rem; box-shadow: 0 14px 34px rgba(10,20,40,.2); min-width: 210px; }
    .hot .dropdown-item { display: flex; align-items: center; gap: .6rem; color: var(--ink); border-radius: 10px; padding: .5rem .7rem; font-weight: 500; }
    .hot .dropdown-item:hover, .hot .dropdown-item:focus { background: var(--brand-soft); color: var(--ink); }
    .hot .dropdown-item.ok { color: var(--ok); }
    .hot .dropdown-item.warn { color: var(--c-disaster); }
    .hot .dropdown-item.danger { color: var(--bad); }
    .hot .dropdown-divider { border-color: var(--line); margin: .3rem 0; }
    .hot .dropdown-menu form { margin: 0; }

    .empty { text-align: center; padding: 3.5rem 1rem; background: var(--paper); border: 1px dashed var(--line); border-radius: 22px; }
    .empty i.big { font-size: 2.4rem; color: var(--muted); }
    .empty h3 { font-size: 1.2rem; font-weight: 700; margin: .75rem 0 .25rem; }
    .empty p { color: var(--muted); margin: 0 auto 1.1rem; max-width: 38ch; }
    .empty .view-btn { margin-right: .4rem; }
    .pager { margin-top: 1.25rem; }
    .pager .pagination { margin: 0; justify-content: center; }

    .toast-copy { position: fixed; left: 50%; bottom: 1.5rem; transform: translate(-50%, 20px); opacity: 0; pointer-events: none; padding: .65rem 1.15rem; border-radius: 14px; background: var(--ink); color: var(--paper); font-weight: 600; font-size: .9rem; transition: opacity .2s, transform .2s; z-index: 2000; }
    .toast-copy.show { opacity: 1; transform: translate(-50%, 0); }
    @media (prefers-reduced-motion: reduce) { .hot *, .toast-copy { transition: none !important; } }
</style>
@endpush

@section('content')

@php
    $params = array_filter(['search' => $search, 'category' => $category, 'status' => $status], fn ($v) => filled($v));
    $hasFilters = count($params) > 0;
    $url = fn (array $override = []) => route('emergency-hotlines.index', array_filter(array_merge($params, $override), fn ($v) => filled($v)));

    $catMeta = [
        'Barangay'             => ['bi-building',             'cat-barangay'],
        'Police'               => ['bi-shield-fill',          'cat-police'],
        'Fire'                 => ['bi-fire',                 'cat-fire'],
        'Medical / Ambulance'  => ['bi-heart-pulse',          'cat-medical'],
        'Disaster / Emergency' => ['bi-exclamation-triangle', 'cat-disaster'],
        'Rescue'               => ['bi-life-preserver',       'cat-rescue'],
    ];
    $meta = fn ($c) => $catMeta[$c] ?? ['bi-telephone', 'cat-other'];
@endphp

<div class="hot">
<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="head">
        <div>
            <h1 class="display">Emergency hotlines</h1>
            <p>Numbers for Barangay Poblacion and partner agencies. Select a number to copy it, then dial from your phone.</p>
        </div>
        <a href="{{ route('emergency-hotlines.create') }}" class="cta"><i class="bi bi-plus-lg"></i> Add hotline</a>
    </div>

    <div class="readonly-note">
        <i class="bi bi-info-circle"></i>
        <span>Numbers on this page are for reading and copying only. They do not place a call.</span>
    </div>

    {{-- MESSAGES --}}
    @if (session('error'))
        <div class="note" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="x" onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="note" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Please fix the following:</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        </div>
    @endif

    <div class="shell">

        {{-- CATEGORY RAIL --}}
        <nav class="rail" aria-label="Filter by category">
            <p class="rail-title">Agencies</p>
            <a href="{{ $url(['category' => null]) }}" class="ritem {{ $category ? '' : 'active' }}">
                <span class="dot"><i class="bi bi-grid"></i></span> All agencies
            </a>
            @foreach ($categories as $item)
                @php [$icon, $cls] = $meta($item); @endphp
                <a href="{{ $url(['category' => $item]) }}" class="ritem {{ $cls }} {{ $category === $item ? 'active' : '' }}">
                    <span class="dot"><i class="bi {{ $icon }}"></i></span> {{ $item }}
                </a>
            @endforeach
        </nav>

        <main>
            {{-- SEARCH + STATUS --}}
            <form method="GET" action="{{ route('emergency-hotlines.index') }}" id="filterForm" role="search">
                @if ($category) <input type="hidden" name="category" value="{{ $category }}"> @endif
                @if ($status)   <input type="hidden" name="status" value="{{ $status }}"> @endif

                <div class="toolbar">
                    <div class="search">
                        <i class="bi bi-search"></i>
                        <label for="search" class="visually-hidden">Search hotlines</label>
                        <input type="search" name="search" id="search" value="{{ $search }}" autocomplete="off"
                               placeholder="Search by name, agency, or number">
                    </div>

                    <nav class="seg" aria-label="Filter by status">
                        <a href="{{ $url(['status' => null]) }}" class="{{ $status ? '' : 'on' }}">All <b>{{ $totalHotlines }}</b></a>
                        <a href="{{ $url(['status' => 'active']) }}" class="{{ $status === 'active' ? 'on' : '' }}">Active <b>{{ $activeHotlines }}</b></a>
                        <a href="{{ $url(['status' => 'inactive']) }}" class="{{ $status === 'inactive' ? 'on' : '' }}">Inactive <b>{{ $inactiveHotlines }}</b></a>
                    </nav>

                    @if ($hasFilters)
                        <a href="{{ route('emergency-hotlines.index') }}" class="reset"><i class="bi bi-arrow-counterclockwise"></i> Reset</a>
                    @endif
                </div>
            </form>

            <p class="result">
                <b>{{ $hotlines->total() }}</b> {{ $hotlines->total() === 1 ? 'hotline' : 'hotlines' }}{{ $hasFilters ? ' match your filters' : '' }}
            </p>

            {{-- HOTLINES --}}
            @if ($hotlines->count())

                <div class="grid">
                @foreach ($hotlines as $hotline)
                    @php
                        [$icon, $cls] = $meta($hotline->category);
                        $active = ! $hotline->isInactive();
                    @endphp

                    <article class="hcard {{ $cls }} {{ $active ? '' : 'off' }}">

                        <div class="h-top">
                            <span class="h-ic"><i class="bi {{ $icon }}"></i></span>
                            <div class="h-id">
                                <div class="h-name">{{ $hotline->name }}</div>
                                <div class="h-cat">{{ $hotline->category_label }}</div>
                            </div>

                            <div class="dropdown">
                                <button type="button" class="kebab" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions">
                                    <i class="bi bi-three-dots-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('emergency-hotlines.edit', $hotline) }}">
                                            <i class="bi bi-pencil"></i> Edit
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>

                                    @if ($hotline->isInactive())
                                        <li>
                                            <form method="POST" action="{{ route('emergency-hotlines.activate', $hotline) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item ok"><i class="bi bi-check-circle"></i> Activate</button>
                                            </form>
                                        </li>
                                    @else
                                        <li>
                                            <form method="POST" action="{{ route('emergency-hotlines.deactivate', $hotline) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="dropdown-item warn"
                                                        onclick="return confirm('Deactivate this emergency hotline?')">
                                                    <i class="bi bi-pause-circle"></i> Deactivate
                                                </button>
                                            </form>
                                        </li>
                                    @endif

                                    <li>
                                        <form method="POST" action="{{ route('emergency-hotlines.destroy', $hotline) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item danger"
                                                    onclick="return confirm('This will deactivate the hotline instead of permanently deleting it. Continue?')">
                                                <i class="bi bi-archive"></i> Deactivate / archive
                                            </button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        {{-- Number plate: plain text, selectable, click copies. NOT a tel: link. --}}
                        <div class="plate">
                            <span class="num" translate="no" tabindex="0" role="button"
                                  data-copy="{{ $hotline->phone_number }}"
                                  title="Click to copy"
                                  aria-label="Copy number {{ $hotline->phone_number }}">{{ $hotline->phone_number }}</span>
                            <button type="button" class="copy-btn" data-copy="{{ $hotline->phone_number }}" aria-label="Copy {{ $hotline->phone_number }}">
                                <i class="bi bi-clipboard"></i> <span>Copy</span>
                            </button>
                        </div>

                        @if ($hotline->description)
                            <p class="h-desc">{{ $hotline->description }}</p>
                        @endif

                        @if ($hotline->alternate_number || $hotline->contact_person || $hotline->address)
                            <div class="meta">
                                @if ($hotline->alternate_number)
                                    <div><i class="bi bi-telephone"></i>
                                        <span class="alt">
                                            <span class="num-sm" translate="no" data-copy="{{ $hotline->alternate_number }}" title="Click to copy">{{ $hotline->alternate_number }}</span>
                                            <small>alternate</small>
                                        </span>
                                    </div>
                                @endif
                                @if ($hotline->contact_person)
                                    <div><i class="bi bi-person"></i><span>{{ $hotline->contact_person }}</span></div>
                                @endif
                                @if ($hotline->address)
                                    <div><i class="bi bi-geo-alt"></i><span>{{ $hotline->address }}</span></div>
                                @endif
                            </div>
                        @endif

                        <div class="h-foot">
                            <span class="pill {{ $active ? 'on' : 'off' }}">{{ $active ? 'Active' : 'Inactive' }}</span>
                            <a href="{{ route('emergency-hotlines.show', $hotline) }}" class="view-btn"><i class="bi bi-eye"></i> View details</a>
                        </div>

                    </article>
                @endforeach
                </div>

                @if ($hotlines->hasPages())
                    <div class="pager">{{ $hotlines->withQueryString()->links() }}</div>
                @endif

            @else

                <div class="empty">
                    <i class="bi bi-telephone-x big"></i>
                    <h3 class="display">No hotlines found</h3>
                    <p>
                        @if ($hasFilters)
                            Nothing matches your search or filters. Clear them to see every hotline.
                        @else
                            No hotlines yet. Add the first one so residents know who to reach.
                        @endif
                    </p>
                    @if ($hasFilters)
                        <a href="{{ route('emergency-hotlines.index') }}" class="view-btn"><i class="bi bi-arrow-counterclockwise"></i> Clear filters</a>
                    @endif
                    <a href="{{ route('emergency-hotlines.create') }}" class="cta"><i class="bi bi-plus-lg"></i> Add hotline</a>
                </div>

            @endif
        </main>
    </div>

</div>
</div>

<div class="toast-copy" id="copyToast" role="status" aria-live="polite">Number copied</div>

@endsection

@push('scripts')
<script>
(function () {
    // Search applies shortly after you stop typing.
    const box = document.getElementById('search');
    if (box) {
        let timer;
        box.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                try { sessionStorage.setItem('hot_refocus', '1'); } catch (e) {}
                box.form.submit();
            }, 400);
        });
        try {
            if (sessionStorage.getItem('hot_refocus')) {
                sessionStorage.removeItem('hot_refocus');
                box.focus();
                box.setSelectionRange(box.value.length, box.value.length);
            }
        } catch (e) {}
    }

    // Copy a number. Numbers are plain text; nothing here starts a call.
    const toast = document.getElementById('copyToast');
    let hide;
    function flash(msg) {
        toast.textContent = msg; toast.classList.add('show');
        clearTimeout(hide); hide = setTimeout(function () { toast.classList.remove('show'); }, 1800);
    }
    function fallbackCopy(text) {
        const t = document.createElement('textarea');
        t.value = text; t.style.position = 'fixed'; t.style.opacity = '0';
        document.body.appendChild(t); t.select();
        try { document.execCommand('copy'); flash('Number copied'); } catch (e) { flash('Could not copy'); }
        document.body.removeChild(t);
    }
    function copy(text, trigger) {
        const ok = function () {
            flash('Copied ' + text);
            const card = trigger.closest('.plate');
            const btn = card && card.querySelector('.copy-btn');
            if (btn) {
                const label = btn.querySelector('span');
                btn.classList.add('done'); label.textContent = 'Copied';
                setTimeout(function () { btn.classList.remove('done'); label.textContent = 'Copy'; }, 1600);
            }
        };
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(ok, function () { fallbackCopy(text); });
        } else { fallbackCopy(text); }
    }
    document.querySelectorAll('[data-copy]').forEach(function (el) {
        el.addEventListener('click', function () { copy(el.dataset.copy, el); });
        el.addEventListener('keydown', function (e) {
            if (el.tagName !== 'BUTTON' && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); copy(el.dataset.copy, el); }
        });
    });
})();
</script>
@endpush