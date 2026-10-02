@extends('layouts.app')

@section('title', 'Relief Assistance')

@section('content')

@php
    /*
    | RELIEF ASSISTANCE - "field board" redesign
    | Same data + routes as before. New layout:
    |   - left: sticky workflow guide (Prepare -> Give out -> Verify)
    |   - right: operations as a list, each with a distribution-date stub and a 3-stage tracker
    |   - top: one proportional status bar instead of stat cards
    */

    $operationCollection = $operations ?? collect();
    $operationItems = collect(
        method_exists($operationCollection, 'items') ? $operationCollection->items() : $operationCollection
    );
    $totalOperations = method_exists($operationCollection, 'total')
        ? $operationCollection->total()
        : $operationItems->count();

    $hasCreateRoute         = Route::has('relief-assistance.create');
    $hasMasterlistRoute     = Route::has('relief-assistance.masterlist');
    $hasVoucherRecordsRoute = Route::has('relief-assistance.vouchers');
    $hasQrVerificationRoute = Route::has('relief-vouchers.qr-verification');
    $hasOperationShowRoute  = Route::has('relief-assistance.show');

    $norm    = fn ($s) => strtolower((string) $s);
    $countBy = fn ($fn) => $operationItems->filter($fn)->count();

    $activeOperations    = $countBy(fn ($o) => !in_array($norm($o->status), ['cancelled', 'completed'], true));
    $completedOperations = $countBy(fn ($o) => $norm($o->status) === 'completed');
    $cancelledOperations = $countBy(fn ($o) => $norm($o->status) === 'cancelled');
    $pageCount           = $operationItems->count();

    // Groups the many raw statuses into 3 filter buckets
    $statusGroup = fn ($s) => in_array($norm($s), ['completed', 'cancelled'], true) ? $norm($s) : 'active';

    $statusClass = fn ($s) => match ($norm($s)) {
        'completed' => 'is-completed',
        'cancelled' => 'is-cancelled',
        'draft'     => 'is-draft',
        default     => 'is-active',
    };

    $statusLabel = fn ($s) => match ($norm($s)) {
        'draft' => 'Draft', 'active' => 'Active', 'completed' => 'Completed',
        'cancelled' => 'Cancelled', 'distribution' => 'Distribution', 'ready' => 'Ready',
        default => ucfirst(str_replace('_', ' ', (string) $s)),
    };

    // Which stage of the journey an operation is in.
    // 0 = Prepare, 1 = Give out, 2 = Verify, 3 = all done, -1 = stopped (cancelled).
    // Adjust this map if your status names mean something different.
    $stageOf = fn ($s) => match ($norm($s)) {
        'draft'           => 0,
        'ready', 'active' => 1,
        'distribution'    => 2,
        'completed'       => 3,
        'cancelled'       => -1,
        default           => 1,
    };
    $stageNames = ['Prepare', 'Give out', 'Verify'];
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">

<style>
    .ra {
        --ink: #1c1b33;
        --ink-2: #5e5c78;
        --line: #e3e1ee;
        --wash: #f5f4fa;
        --card: #ffffff;
        --ube: #5a3fd6;
        --ube-soft: #ebe7fb;
        --mango: #ffb020;
        --mango-soft: #fff1d0;
        --green: #17845a;
        --green-soft: #dcf3e8;
        --red: #cf3d3d;
        --red-soft: #fbe4e4;
        --grey: #8a889f;
        --grey-soft: #ecebf2;

        max-width: 1320px;
        margin: 0 auto;
        color: var(--ink);
        font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
    }
    .ra *, .ra *::before, .ra *::after { box-sizing: border-box; }
    .ra h1, .ra h2, .ra h3, .ra p, .ra ol, .ra ul, .ra dl, .ra dd { margin: 0; padding: 0; }
    .ra ol, .ra ul { list-style: none; }
    .ra a:focus-visible, .ra button:focus-visible, .ra input:focus-visible, .ra summary:focus-visible {
        outline: 3px solid var(--mango); outline-offset: 2px;
    }
    .ra-display { font-family: 'Unbounded', 'Figtree', system-ui, sans-serif; letter-spacing: -.02em; }

    /* ---------- Hero ---------- */
    .ra-hero { display: flex; align-items: flex-end; justify-content: space-between; gap: 24px; flex-wrap: wrap; margin-bottom: 20px; }
    .ra-hero h1 { font-size: 30px; line-height: 1.15; font-weight: 800; }
    .ra-hero p { margin-top: 10px; max-width: 520px; color: var(--ink-2); font-size: 14.5px; line-height: 1.6; }

    .ra-btn {
        display: inline-flex; align-items: center; gap: 9px; padding: 12px 20px;
        border: 0; border-radius: 999px; background: var(--ube); color: #fff;
        font-size: 14px; font-weight: 700; text-decoration: none;
        transition: background .15s, transform .15s;
    }
    .ra-btn:hover { background: var(--ink); color: #fff; }
    .ra-btn:active { transform: scale(.98); }

    /* ---------- Status bar (the one memorable moment) ---------- */
    .ra-pulse { margin-bottom: 28px; }
    .ra-pulse-bar { display: flex; gap: 4px; height: 14px; border-radius: 999px; overflow: hidden; transform-origin: left center; animation: ra-grow .9s cubic-bezier(.2, .8, .2, 1) both; }
    .ra-pulse-bar i { display: block; min-width: 10px; }
    .ra-pulse-bar .s-active { background: var(--ube); }
    .ra-pulse-bar .s-completed { background: var(--green); }
    .ra-pulse-bar .s-cancelled { background: var(--red); }
    .ra-pulse-key { display: flex; flex-wrap: wrap; gap: 6px 22px; margin-top: 10px; font-size: 13px; color: var(--ink-2); }
    .ra-pulse-key span { display: inline-flex; align-items: center; gap: 7px; }
    .ra-pulse-key b { color: var(--ink); font-weight: 700; }
    .ra-pulse-key i { width: 9px; height: 9px; border-radius: 50%; display: inline-block; }
    @keyframes ra-grow { from { transform: scaleX(0); } to { transform: scaleX(1); } }

    /* ---------- Two-column body ---------- */
    .ra-body { display: grid; grid-template-columns: 300px minmax(0, 1fr); gap: 32px; align-items: start; }

    /* ---------- Guide rail ---------- */
    .ra-rail { position: sticky; top: 16px; padding: 22px 20px 22px; border: 1px solid var(--line); border-radius: 18px; background: var(--card); }
    .ra-rail > h2 { font-size: 15px; font-weight: 700; }
    .ra-rail > p { margin: 4px 0 20px; font-size: 12.5px; color: var(--ink-2); line-height: 1.5; }

    .ra-flow { position: relative; }
    .ra-flow::before { content: ""; position: absolute; left: 17px; top: 20px; bottom: 20px; width: 2px; background: var(--line); }
    .ra-phase { position: relative; padding: 0 0 26px 52px; }
    .ra-phase:last-child { padding-bottom: 0; }
    .ra-node {
        position: absolute; left: 0; top: 0; width: 36px; height: 36px; border-radius: 50%;
        display: grid; place-items: center; font-size: 14px; font-weight: 700;
        background: var(--c); color: var(--on, #fff); box-shadow: 0 0 0 5px var(--card);
    }
    .ra-phase:nth-child(1) { --c: var(--ube); }
    .ra-phase:nth-child(2) { --c: var(--mango); --on: var(--ink); }
    .ra-phase:nth-child(3) { --c: var(--green); }
    .ra-phase h3 { font-size: 15px; font-weight: 700; line-height: 36px; }
    .ra-phase small { display: block; margin: -4px 0 9px; font-size: 12px; color: var(--ink-2); }
    .ra-phase ul { display: grid; gap: 7px; margin-bottom: 12px; }
    .ra-phase li { font-size: 12.5px; line-height: 1.45; color: var(--ink-2); padding-left: 13px; position: relative; }
    .ra-phase li::before { content: ""; position: absolute; left: 0; top: 7px; width: 5px; height: 5px; border-radius: 50%; background: var(--c); }
    .ra-phase li b { color: var(--ink); font-weight: 600; }

    .ra-links { display: flex; flex-wrap: wrap; gap: 6px; }
    .ra-link {
        display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 999px;
        border: 1px solid var(--line); background: var(--card); color: var(--ink);
        font-size: 12.5px; font-weight: 600; text-decoration: none; cursor: pointer;
        transition: border-color .15s, background .15s;
    }
    .ra-link:hover { border-color: var(--ube); background: var(--ube-soft); color: var(--ink); }

    /* ---------- Operations ---------- */
    .ra-main { min-width: 0; }
    .ra-toolbar { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 14px; margin-bottom: 14px; }
    .ra-toolbar h2 { font-size: 20px; font-weight: 800; }
    .ra-toolbar p { margin-top: 4px; font-size: 13px; color: var(--ink-2); }

    .ra-tools { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .ra-chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .ra-chip {
        display: inline-flex; align-items: center; gap: 7px; padding: 7px 13px; border-radius: 999px;
        border: 1px solid var(--line); background: var(--card); color: var(--ink-2);
        font-size: 13px; font-weight: 600; cursor: pointer; transition: border-color .15s, background .15s, color .15s;
    }
    .ra-chip:hover { border-color: var(--ube); }
    .ra-chip em { font-style: normal; font-size: 12px; color: var(--grey); }
    .ra-chip[aria-pressed="true"] { background: var(--ink); border-color: var(--ink); color: #fff; }
    .ra-chip[aria-pressed="true"] em { color: #c9c6e2; }

    .ra-search { position: relative; }
    .ra-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--grey); font-size: 13px; pointer-events: none; }
    .ra-search input {
        width: 240px; padding: 9px 14px 9px 36px; border: 1px solid var(--line); border-radius: 999px;
        font: inherit; font-size: 13px; background: var(--card); color: var(--ink);
    }
    .ra-search input::placeholder { color: var(--grey); }

    .ra-list { border: 1px solid var(--line); border-radius: 18px; background: var(--card); overflow: hidden; }
    .ra-op {
        --st: var(--ube);
        display: grid; grid-template-columns: 74px minmax(0, 1fr) 210px; gap: 20px; align-items: center;
        padding: 20px 22px 20px 26px; border-bottom: 1px solid var(--line);
        box-shadow: inset 5px 0 0 var(--st); transition: background .15s;
    }
    .ra-op:last-child { border-bottom: 0; }
    .ra-op:hover { background: #faf9fe; }
    .ra-op.is-completed { --st: var(--green); }
    .ra-op.is-cancelled { --st: var(--red); }
    .ra-op.is-draft { --st: var(--grey); }

    /* date stub */
    .ra-date { text-align: center; padding: 9px 4px 8px; border-radius: 14px; background: var(--wash); border: 1px solid var(--line); }
    .ra-date b { display: block; font-family: 'Unbounded', 'Figtree', sans-serif; font-size: 24px; font-weight: 700; line-height: 1.05; }
    .ra-date span { display: block; margin-top: 3px; font-size: 11.5px; color: var(--ink-2); font-weight: 600; }
    .ra-date.is-none { display: grid; place-items: center; min-height: 66px; color: var(--grey); }
    .ra-date.is-none i { font-size: 20px; display: block; }
    .ra-date.is-none span { font-weight: 500; line-height: 1.2; }

    .ra-titleline { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 10px; }
    .ra-name { font-size: 16px; font-weight: 700; line-height: 1.3; color: var(--ink); text-decoration: none; }
    a.ra-name:hover { color: var(--ube); text-decoration: underline; text-underline-offset: 3px; }
    .ra-code { padding: 1px 9px; border-radius: 999px; background: var(--wash); border: 1px solid var(--line); font-size: 11.5px; color: var(--ink-2); font-weight: 600; }
    .ra-desc { margin-top: 5px; font-size: 13px; line-height: 1.5; color: var(--ink-2); max-width: 520px; }

    .ra-meta { display: flex; flex-wrap: wrap; gap: 8px 26px; margin-top: 11px; }
    .ra-meta div { min-width: 0; }
    .ra-meta dt { font-size: 11.5px; color: var(--grey); font-weight: 600; }
    .ra-meta dd { margin-top: 1px; font-size: 13px; font-weight: 600; line-height: 1.35; }
    .ra-meta dd small { display: block; font-size: 12px; font-weight: 400; color: var(--ink-2); }

    .ra-side { display: grid; gap: 12px; }
    .ra-badge { justify-self: start; display: inline-flex; align-items: center; gap: 7px; padding: 4px 11px; border-radius: 999px; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .ra-badge::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .ra-badge.is-active { background: var(--ube-soft); color: var(--ube); }
    .ra-badge.is-completed { background: var(--green-soft); color: var(--green); }
    .ra-badge.is-cancelled { background: var(--red-soft); color: var(--red); }
    .ra-badge.is-draft { background: var(--grey-soft); color: #5f5d75; }

    .ra-track { display: grid; grid-template-columns: repeat(3, 1fr); gap: 5px; }
    .ra-track span { display: block; font-size: 11.5px; color: var(--grey); font-weight: 500; }
    .ra-track span::before { content: ""; display: block; height: 6px; border-radius: 3px; background: var(--line); margin-bottom: 5px; }
    .ra-track .is-done::before { background: var(--st); opacity: .4; }
    .ra-track .is-now::before { background: var(--st); }
    .ra-track .is-now { color: var(--ink); font-weight: 700; }

    .ra-open {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px; padding: 8px 14px; border-radius: 999px;
        background: var(--ink); color: #fff; font-size: 13px; font-weight: 700; text-decoration: none; transition: background .15s;
    }
    .ra-open:hover { background: var(--ube); color: #fff; }

    .ra-empty { padding: 60px 22px; text-align: center; }
    .ra-empty .ic { width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%; display: grid; place-items: center; background: var(--ube-soft); color: var(--ube); font-size: 26px; }
    .ra-empty h3 { font-size: 16px; font-weight: 700; line-height: 1.35; margin-bottom: 8px; }
    .ra-empty p { max-width: 420px; margin: 0 auto 20px; color: var(--ink-2); font-size: 13.5px; line-height: 1.6; }
    #raNoMatch { display: none; border: 1px solid var(--line); border-radius: 18px; background: var(--card); }

    .ra-pager { margin-top: 16px; }

    /* ---------- Help ---------- */
    .ra-help { margin-top: 26px; border-top: 1px solid var(--line); }
    .ra-help summary { cursor: pointer; padding: 16px 0; font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 9px; list-style: none; }
    .ra-help summary::-webkit-details-marker { display: none; }
    .ra-help summary .chev { margin-left: auto; transition: transform .2s; color: var(--grey); }
    .ra-help[open] summary .chev { transform: rotate(180deg); }
    .ra-rules { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 4px 32px; padding-bottom: 8px; }
    .ra-rule { display: flex; gap: 12px; padding: 12px 0; border-top: 1px solid var(--line); font-size: 13px; line-height: 1.5; color: var(--ink-2); }
    .ra-rule i { flex: none; width: 30px; height: 30px; border-radius: 50%; display: grid; place-items: center; background: var(--ube-soft); color: var(--ube); font-size: 14px; }
    .ra-rule b { display: block; color: var(--ink); font-size: 13.5px; margin-bottom: 1px; }

    /* ---------- Responsive ---------- */
    @media (max-width: 1099.98px) {
        .ra-body { grid-template-columns: 1fr; gap: 26px; }
        .ra-rail { position: static; order: 2; }
        .ra-flow { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 22px; }
        .ra-flow::before { display: none; }
        .ra-phase { padding-bottom: 0; }
    }
    @media (max-width: 899.98px) {
        .ra-op { grid-template-columns: 66px minmax(0, 1fr); gap: 14px 16px; }
        .ra-side { grid-column: 1 / -1; grid-template-columns: 1fr auto; align-items: center; }
        .ra-side .ra-badge { grid-column: 1; grid-row: 1; }
        .ra-side .ra-track { grid-column: 1 / -1; grid-row: 2; }
        .ra-side .ra-open { grid-column: 2; grid-row: 1; }
    }
    @media (max-width: 575.98px) {
        .ra-hero h1 { font-size: 24px; }
        .ra-btn { width: 100%; justify-content: center; }
        .ra-search, .ra-tools { width: 100%; }
        .ra-search input { width: 100%; }
        .ra-chips { flex-wrap: nowrap; overflow-x: auto; max-width: 100%; padding-bottom: 2px; }
        .ra-chip { flex: none; }
        .ra-op { padding: 18px 16px 18px 20px; }
    }
    @media (prefers-reduced-motion: reduce) {
        .ra *, .ra *::before, .ra *::after { transition: none !important; animation: none !important; }
    }
</style>

<div class="container-fluid py-3 ra">

    {{-- ============ HERO ============ --}}
    <header class="ra-hero">
        <div>
            <h1 class="ra-display">Relief Assistance</h1>
            <p>Plan a distribution, give each household a QR voucher, and record the claim on the day. Start with the step you need.</p>
        </div>
        @if ($hasCreateRoute)
            <a href="{{ route('relief-assistance.create') }}" class="ra-btn">
                <i class="bi bi-plus-lg"></i> New relief operation
            </a>
        @endif
    </header>

    {{-- ============ STATUS BAR ============ --}}
    @if ($pageCount > 0)
        <div class="ra-pulse" role="img" aria-label="{{ $activeOperations }} active, {{ $completedOperations }} completed, {{ $cancelledOperations }} cancelled">
            <div class="ra-pulse-bar">
                @if ($activeOperations > 0)    <i class="s-active"    style="flex: {{ $activeOperations }}"></i> @endif
                @if ($completedOperations > 0) <i class="s-completed" style="flex: {{ $completedOperations }}"></i> @endif
                @if ($cancelledOperations > 0) <i class="s-cancelled" style="flex: {{ $cancelledOperations }}"></i> @endif
            </div>
            <div class="ra-pulse-key">
                <span><i style="background: var(--ube)"></i><b>{{ $activeOperations }}</b> in progress</span>
                <span><i style="background: var(--green)"></i><b>{{ $completedOperations }}</b> completed</span>
                <span><i style="background: var(--red)"></i><b>{{ $cancelledOperations }}</b> cancelled</span>
            </div>
        </div>
    @endif

    <div class="ra-body">

        {{-- ============ OPERATIONS ============ --}}
        <section class="ra-main" aria-labelledby="raOpsTitle">

            <div class="ra-toolbar">
                <div>
                    <h2 id="raOpsTitle" class="ra-display">Your relief operations</h2>
                    <p>{{ number_format($totalOperations) }} in total. Open one to see its households and vouchers.</p>
                </div>

                @if ($pageCount > 0)
                    <div class="ra-tools">
                        <div class="ra-chips" role="group" aria-label="Filter by status">
                            <button type="button" class="ra-chip" data-filter="all" aria-pressed="true">All <em>{{ $pageCount }}</em></button>
                            <button type="button" class="ra-chip" data-filter="active" aria-pressed="false">Active <em>{{ $activeOperations }}</em></button>
                            <button type="button" class="ra-chip" data-filter="completed" aria-pressed="false">Completed <em>{{ $completedOperations }}</em></button>
                            <button type="button" class="ra-chip" data-filter="cancelled" aria-pressed="false">Cancelled <em>{{ $cancelledOperations }}</em></button>
                        </div>
                        <label class="ra-search">
                            <i class="bi bi-search"></i>
                            <input type="search" id="raSearch" placeholder="Search name, code, or source" aria-label="Search operations">
                        </label>
                    </div>
                @endif
            </div>

            @if ($pageCount > 0)

                <ol class="ra-list" id="raRows">
                    @foreach ($operationItems as $operation)
                        @php
                            $assistanceType = trim(str_replace('_', ' ', (string) $operation->assistance_type));
                            $sClass   = $statusClass($operation->status);
                            $stage    = $stageOf($operation->status);
                            $showUrl  = $hasOperationShowRoute ? route('relief-assistance.show', ['operation' => $operation->id]) : null;
                            $dist     = !empty($operation->distribution_date) ? \Illuminate\Support\Carbon::parse($operation->distribution_date) : null;
                            $stageText = $stage === -1 ? 'Stopped' : ($stage === 3 ? 'All steps done' : 'Now: ' . $stageNames[$stage]);
                            $searchText = strtolower(implode(' ', [
                                $operation->operation_name, $operation->operation_code,
                                $operation->source_name, $assistanceType,
                            ]));
                        @endphp

                        <li class="ra-op {{ $sClass }}" data-group="{{ $statusGroup($operation->status) }}" data-search="{{ $searchText }}">

                            {{-- Distribution date stub --}}
                            @if ($dist)
                                <div class="ra-date" title="Distribution date: {{ $dist->format('M d, Y') }}">
                                    <b>{{ $dist->format('d') }}</b>
                                    <span>{{ $dist->format('M Y') }}</span>
                                </div>
                            @else
                                <div class="ra-date is-none" title="Distribution date not set">
                                    <div><i class="bi bi-calendar-x"></i><span>No date yet</span></div>
                                </div>
                            @endif

                            {{-- Details --}}
                            <div>
                                <div class="ra-titleline">
                                    @if ($showUrl)
                                        <a href="{{ $showUrl }}" class="ra-name">{{ $operation->operation_name }}</a>
                                    @else
                                        <span class="ra-name">{{ $operation->operation_name }}</span>
                                    @endif
                                    <span class="ra-code">{{ $operation->operation_code ?: 'No code' }}</span>
                                </div>

                                @if (!empty($operation->description))
                                    <p class="ra-desc">{{ \Illuminate\Support\Str::limit($operation->description, 90) }}</p>
                                @endif

                                <dl class="ra-meta">
                                    <div>
                                        <dt>Assistance</dt>
                                        <dd>
                                            {{ $assistanceType ? ucwords($assistanceType) : 'Relief assistance' }}
                                            @if (!empty($operation->assistance_description))
                                                <small>{{ \Illuminate\Support\Str::limit($operation->assistance_description, 65) }}</small>
                                            @endif
                                        </dd>
                                    </div>
                                    <div>
                                        <dt>Source</dt>
                                        <dd>
                                            {{ $operation->source_name ?: 'Not specified' }}
                                            @if (!empty($operation->source_reference))
                                                <small>{{ \Illuminate\Support\Str::limit($operation->source_reference, 50) }}</small>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Status + progress + action --}}
                            <div class="ra-side">
                                <span class="ra-badge {{ $sClass }}">{{ $statusLabel($operation->status) }}</span>

                                <div class="ra-track" role="img" aria-label="Progress. {{ $stageText }}">
                                    @foreach ($stageNames as $i => $name)
                                        @php
                                            $state = $stage === -1 ? '' : ($i < $stage ? 'is-done' : ($i === $stage ? 'is-now' : ''));
                                        @endphp
                                        <span class="{{ $state }}">{{ $name }}</span>
                                    @endforeach
                                </div>

                                @if ($showUrl)
                                    <a href="{{ $showUrl }}" class="ra-open" aria-label="Open {{ $operation->operation_name }}">
                                        Open <i class="bi bi-arrow-right-short"></i>
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>

                {{-- Shown by the filter script when nothing matches --}}
                <div class="ra-empty" id="raNoMatch">
                    <div class="ic"><i class="bi bi-search"></i></div>
                    <h3 class="ra-display">No operations match</h3>
                    <p>Try a different status or clear the search box.</p>
                    <button type="button" class="ra-link" id="raReset"><i class="bi bi-x-circle"></i> Clear filters</button>
                </div>

                @if (method_exists($operationCollection, 'links'))
                    <div class="ra-pager">{{ $operationCollection->links() }}</div>
                @endif

            @else

                <div class="ra-list">
                    <div class="ra-empty">
                        <div class="ic"><i class="bi bi-box-seam"></i></div>
                        <h3 class="ra-display">No relief operations yet</h3>
                        <p>Create your first operation to set what is being given out. Then you can print the masterlist and start issuing QR vouchers.</p>
                        @if ($hasCreateRoute)
                            <a href="{{ route('relief-assistance.create') }}" class="ra-btn"><i class="bi bi-plus-lg"></i> Create the first operation</a>
                        @endif
                    </div>
                </div>

            @endif

            {{-- ============ HELP ============ --}}
            <details class="ra-help">
                <summary>
                    <i class="bi bi-question-circle"></i> How records stay accurate
                    <i class="bi bi-chevron-down chev"></i>
                </summary>
                <div class="ra-rules">
                    <div class="ra-rule"><i class="bi bi-house"></i><div><b>Household-based</b>Assistance is recorded against the household, not a separate beneficiary list.</div></div>
                    <div class="ra-rule"><i class="bi bi-qr-code"></i><div><b>One voucher, one QR</b>Every voucher has its own reference code and QR code.</div></div>
                    <div class="ra-rule"><i class="bi bi-shield-lock"></i><div><b>Claimed once</b>After a voucher is marked as claimed, it cannot be used again.</div></div>
                    <div class="ra-rule"><i class="bi bi-person-check"></i><div><b>Staff decide</b>The system records the transaction. Barangay staff decide who receives assistance.</div></div>
                    <div class="ra-rule"><i class="bi bi-clock-history"></i><div><b>Full history</b>Operations, vouchers, scans, and claims stay available for reports.</div></div>
                </div>
            </details>
        </section>

        {{-- ============ WORKFLOW GUIDE ============ --}}
        <aside class="ra-rail" aria-label="How a relief distribution works">
            <h2>How a distribution works</h2>
            <p>Three phases, in order. Jump in wherever you are.</p>

            <ol class="ra-flow">
                <li class="ra-phase">
                    <span class="ra-node ra-display" aria-hidden="true">1</span>
                    <h3>Prepare</h3>
                    <small>Before distribution day</small>
                    <ul>
                        <li><b>Create the operation.</b> What is given, from whom, and when.</li>
                        <li><b>Print the masterlist.</b> For the house-to-house visit.</li>
                    </ul>
                    <div class="ra-links">
                        @if ($hasCreateRoute)
                            <a class="ra-link" href="{{ route('relief-assistance.create') }}"><i class="bi bi-plus-circle"></i> New operation</a>
                        @endif
                        @if ($hasMasterlistRoute)
                            <a class="ra-link" href="{{ route('relief-assistance.masterlist') }}"><i class="bi bi-printer"></i> Masterlist</a>
                        @endif
                    </div>
                </li>

                <li class="ra-phase">
                    <span class="ra-node ra-display" aria-hidden="true">2</span>
                    <h3>Give out vouchers</h3>
                    <small>During the household visit</small>
                    <ul>
                        <li><b>Find the household.</b> Search the masterlist.</li>
                        <li><b>Enter the assistance.</b> What the household receives.</li>
                        <li><b>Generate the voucher.</b> A unique QR code is created.</li>
                    </ul>
                    <div class="ra-links">
                        @if ($hasMasterlistRoute)
                            <a class="ra-link" href="{{ route('relief-assistance.masterlist') }}"><i class="bi bi-people"></i> Select households</a>
                        @endif
                        @if ($hasVoucherRecordsRoute)
                            <a class="ra-link" href="{{ route('relief-assistance.vouchers') }}"><i class="bi bi-ticket-perforated"></i> Voucher records</a>
                        @endif
                    </div>
                </li>

                <li class="ra-phase">
                    <span class="ra-node ra-display" aria-hidden="true">3</span>
                    <h3>Verify and claim</h3>
                    <small>At the distribution site</small>
                    <ul>
                        <li><b>Scan the QR voucher.</b> Confirm it is valid.</li>
                        <li><b>Mark as claimed.</b> Each voucher works only once.</li>
                    </ul>
                    <div class="ra-links">
                        @if ($hasQrVerificationRoute)
                            <a class="ra-link" href="{{ route('relief-vouchers.qr-verification') }}"><i class="bi bi-upc-scan"></i> Scan a voucher</a>
                        @endif
                    </div>
                </li>
            </ol>
        </aside>

    </div>
</div>

<script>
    (function () {
        var list = document.getElementById('raRows');
        if (!list) return;

        var rows = list.querySelectorAll('li.ra-op');
        var tabs = document.querySelectorAll('.ra-chip');
        var search = document.getElementById('raSearch');
        var noMatch = document.getElementById('raNoMatch');
        var reset = document.getElementById('raReset');
        var filter = 'all';

        function apply() {
            var q = search ? (search.value || '').trim().toLowerCase() : '';
            var shown = 0;
            rows.forEach(function (row) {
                var ok = (filter === 'all' || row.dataset.group === filter) &&
                         (!q || row.dataset.search.indexOf(q) !== -1);
                row.style.display = ok ? '' : 'none';
                if (ok) shown++;
            });
            noMatch.style.display = shown ? 'none' : 'block';
            list.style.display = shown ? '' : 'none';
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                filter = tab.dataset.filter;
                tabs.forEach(function (t) { t.setAttribute('aria-pressed', t === tab ? 'true' : 'false'); });
                apply();
            });
        });

        if (search) search.addEventListener('input', apply);

        if (reset) reset.addEventListener('click', function () {
            if (search) search.value = '';
            filter = 'all';
            tabs.forEach(function (t) { t.setAttribute('aria-pressed', t.dataset.filter === 'all' ? 'true' : 'false'); });
            apply();
        });
    })();
</script>

@endsection
