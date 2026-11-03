@extends('layouts.app')

@section('title', 'Relief Assistance')

@section('content')

@php
    /*
    | RELIEF ASSISTANCE - "mission control" redesign
    | Same data + routes as before. Works in light and dark mode.
    |   - hero panel with a status ring (donut) + key numbers
    |   - operations as cards with a glowing 3-step tracker
    |   - sticky "route" guide (Prepare -> Give out -> Verify)
    |
    | Theme switching (first match wins):
    |   1. Explicit:  [data-bs-theme], [data-theme], .dark / .dark-mode on <html> or <body>
    |   2. Otherwise follows the visitor's OS (prefers-color-scheme)
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

    // Donut ring math (percent stops for conic-gradient)
    $pctActive = $pageCount > 0 ? round($activeOperations / $pageCount * 100, 2) : 0;
    $pctDone   = $pageCount > 0 ? round(($activeOperations + $completedOperations) / $pageCount * 100, 2) : 0;
    $donePercent = $pageCount > 0 ? round($completedOperations / $pageCount * 100) : 0;

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
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    /* =====================================================
       THEME TOKENS
       ===================================================== */
    .ra {
        color-scheme: light;
        --bg: #f3f2fb;
        --surface: #ffffff;
        --surface-2: #f7f6fd;
        --line: #e4e2f1;
        --ink: #17152e;
        --ink-2: #5d5a7a;
        --ink-3: #8c89a8;

        --acc: #5b3df5;
        --acc-2: #8b6bff;
        --acc-soft: #ece8ff;
        --ok: #0d8f64;
        --ok-soft: #d9f5ea;
        --bad: #d03a4f;
        --bad-soft: #fde3e7;
        --warn: #e89200;
        --warn-soft: #fff0cf;
        --idle: #7d7a96;
        --idle-soft: #ebeaf3;

        --hero-a: #5b3df5;
        --hero-b: #9a5cff;
        --hero-c: #ff9d4d;
        --on-hero: #ffffff;

        --shadow: 0 1px 2px rgba(23, 21, 46, .05), 0 10px 30px -12px rgba(63, 42, 160, .18);
        --shadow-hover: 0 2px 4px rgba(23, 21, 46, .06), 0 18px 40px -14px rgba(63, 42, 160, .32);
        --ring-hole: var(--surface);
    }

    /* Dark tokens: explicit toggles */
    [data-bs-theme="dark"] .ra,
    [data-theme="dark"] .ra,
    html.dark .ra, body.dark .ra,
    html.dark-mode .ra, body.dark-mode .ra,
    html.theme-dark .ra, body.theme-dark .ra {
        color-scheme: dark;
        --bg: #0c0b17;
        --surface: #15142a;
        --surface-2: #1b1a35;
        --line: #2a2849;
        --ink: #f0eefc;
        --ink-2: #aaa6cc;
        --ink-3: #7a769e;

        --acc: #8f78ff;
        --acc-2: #b29cff;
        --acc-soft: rgba(143, 120, 255, .16);
        --ok: #3ee0a1;
        --ok-soft: rgba(62, 224, 161, .14);
        --bad: #ff6b81;
        --bad-soft: rgba(255, 107, 129, .15);
        --warn: #ffb938;
        --warn-soft: rgba(255, 185, 56, .15);
        --idle: #9a96bd;
        --idle-soft: rgba(154, 150, 189, .15);

        --hero-a: #3a24b8;
        --hero-b: #6a3fd9;
        --hero-c: #d1622b;

        --shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 12px 32px -14px rgba(0, 0, 0, .7);
        --shadow-hover: 0 2px 4px rgba(0, 0, 0, .5), 0 20px 44px -14px rgba(0, 0, 0, .85), 0 0 0 1px rgba(143, 120, 255, .35);
    }

    /* Dark tokens: follow the OS unless the page is explicitly forced to light */
    @media (prefers-color-scheme: dark) {
        html:not([data-bs-theme="light"]):not([data-theme="light"]):not(.light):not(.theme-light) body:not(.light):not(.light-mode):not(.theme-light) .ra {
            color-scheme: dark;
            --bg: #0c0b17;
            --surface: #15142a;
            --surface-2: #1b1a35;
            --line: #2a2849;
            --ink: #f0eefc;
            --ink-2: #aaa6cc;
            --ink-3: #7a769e;

            --acc: #8f78ff;
            --acc-2: #b29cff;
            --acc-soft: rgba(143, 120, 255, .16);
            --ok: #3ee0a1;
            --ok-soft: rgba(62, 224, 161, .14);
            --bad: #ff6b81;
            --bad-soft: rgba(255, 107, 129, .15);
            --warn: #ffb938;
            --warn-soft: rgba(255, 185, 56, .15);
            --idle: #9a96bd;
            --idle-soft: rgba(154, 150, 189, .15);

            --hero-a: #3a24b8;
            --hero-b: #6a3fd9;
            --hero-c: #d1622b;

            --shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 12px 32px -14px rgba(0, 0, 0, .7);
            --shadow-hover: 0 2px 4px rgba(0, 0, 0, .5), 0 20px 44px -14px rgba(0, 0, 0, .85), 0 0 0 1px rgba(143, 120, 255, .35);
        }
    }

    /* =====================================================
       BASE
       ===================================================== */
    .ra {
        max-width: 1360px;
        margin: 0 auto;
        padding: 22px;
        border-radius: 28px;
        background: var(--bg);
        color: var(--ink);
        font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
        transition: background .25s, color .25s;
    }
    .ra *, .ra *::before, .ra *::after { box-sizing: border-box; }
    .ra h1, .ra h2, .ra h3, .ra p, .ra ol, .ra ul, .ra dl, .ra dd { margin: 0; padding: 0; }
    .ra ol, .ra ul { list-style: none; }
    .ra a { color: inherit; }
    .ra a:focus-visible, .ra button:focus-visible, .ra input:focus-visible, .ra summary:focus-visible {
        outline: 3px solid var(--warn); outline-offset: 2px;
    }
    .ra-display { font-family: 'Sora', 'Figtree', system-ui, sans-serif; letter-spacing: -.025em; }

    /* =====================================================
       HERO
       ===================================================== */
    .ra-hero {
        position: relative; overflow: hidden; isolation: isolate;
        display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 32px; align-items: center;
        padding: 34px 38px; border-radius: 24px; margin-bottom: 22px;
        color: var(--on-hero);
        background:
            radial-gradient(900px 380px at 108% -20%, var(--hero-c) 0%, transparent 60%),
            radial-gradient(700px 420px at -10% 120%, var(--hero-b) 0%, transparent 62%),
            linear-gradient(135deg, var(--hero-a), var(--hero-b));
        box-shadow: var(--shadow);
    }
    /* subtle dotted texture */
    .ra-hero::before {
        content: ""; position: absolute; inset: 0; z-index: -1; opacity: .28;
        background-image: radial-gradient(rgba(255, 255, 255, .55) 1px, transparent 1.4px);
        background-size: 22px 22px;
        -webkit-mask-image: linear-gradient(120deg, #000 0%, transparent 70%);
                mask-image: linear-gradient(120deg, #000 0%, transparent 70%);
    }
    .ra-eyebrow {
        display: inline-flex; align-items: center; gap: 8px; padding: 5px 12px; border-radius: 999px;
        background: rgba(255, 255, 255, .16); border: 1px solid rgba(255, 255, 255, .28);
        font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase;
        backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px);
    }
    .ra-eyebrow::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: #7dffc8; box-shadow: 0 0 0 4px rgba(125, 255, 200, .25); }
    .ra-hero h1 { margin-top: 14px; font-size: 40px; line-height: 1.05; font-weight: 800; }
    .ra-hero p { margin-top: 12px; max-width: 520px; font-size: 15px; line-height: 1.65; color: rgba(255, 255, 255, .86); }

    .ra-btn {
        display: inline-flex; align-items: center; gap: 9px; margin-top: 22px; padding: 13px 22px;
        border: 0; border-radius: 14px; background: #fff; color: #231a6b !important;
        font-size: 14px; font-weight: 700; text-decoration: none;
        box-shadow: 0 10px 24px -10px rgba(0, 0, 0, .5);
        transition: transform .15s, box-shadow .15s;
    }
    .ra-btn:hover { transform: translateY(-2px); box-shadow: 0 16px 30px -10px rgba(0, 0, 0, .55); }
    .ra-btn:active { transform: scale(.98); }
    .ra-btn.is-solid { background: var(--acc); color: #fff !important; }

    /* status ring */
    .ra-ring-wrap {
        display: flex; align-items: center; gap: 22px; padding: 18px 22px 18px 18px; border-radius: 20px;
        background: rgba(255, 255, 255, .12); border: 1px solid rgba(255, 255, 255, .25);
        backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px);
    }
    .ra-ring {
        --a: 0%; --b: 0%;
        position: relative; width: 132px; height: 132px; border-radius: 50%; flex: none;
        background: conic-gradient(#b9a8ff 0 var(--a), #5df0b2 var(--a) var(--b), #ff8da0 var(--b) 100%);
        animation: ra-spin .9s cubic-bezier(.2, .8, .2, 1) both;
    }
    .ra-ring::after {
        content: ""; position: absolute; inset: 15px; border-radius: 50%;
        background: color-mix(in srgb, var(--hero-a) 88%, #000);
    }
    .ra-ring-center { position: absolute; inset: 0; z-index: 1; display: grid; place-content: center; text-align: center; }
    .ra-ring-center b { font-family: 'Sora', sans-serif; font-size: 28px; line-height: 1; font-weight: 800; }
    .ra-ring-center span { margin-top: 4px; font-size: 11px; font-weight: 600; opacity: .8; }
    .ra-ring.is-empty { background: rgba(255, 255, 255, .25); }
    .ra-legend { display: grid; gap: 10px; font-size: 13px; }
    .ra-legend li { display: flex; align-items: center; gap: 10px; color: rgba(255, 255, 255, .85); }
    .ra-legend b { font-family: 'Sora', sans-serif; font-size: 18px; color: #fff; min-width: 26px; }
    .ra-legend i { width: 10px; height: 10px; border-radius: 3px; flex: none; }
    @keyframes ra-spin { from { opacity: 0; transform: rotate(-90deg) scale(.9); } to { opacity: 1; transform: none; } }

    /* =====================================================
       BODY GRID
       ===================================================== */
    .ra-body { display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 22px; align-items: start; }

    /* ---------- toolbar ---------- */
    .ra-main { min-width: 0; }
    .ra-toolbar {
        display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px;
        padding: 16px 18px; margin-bottom: 16px; border-radius: 18px;
        background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow);
    }
    .ra-toolbar h2 { font-size: 19px; font-weight: 700; }
    .ra-toolbar p { margin-top: 3px; font-size: 13px; color: var(--ink-2); }
    .ra-tools { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }

    .ra-chips { display: inline-flex; gap: 2px; padding: 4px; border-radius: 12px; background: var(--surface-2); border: 1px solid var(--line); }
    .ra-chip {
        display: inline-flex; align-items: center; gap: 7px; padding: 7px 13px; border-radius: 9px;
        border: 0; background: transparent; color: var(--ink-2);
        font: inherit; font-size: 13px; font-weight: 600; cursor: pointer; transition: background .15s, color .15s, box-shadow .15s;
    }
    .ra-chip:hover { color: var(--ink); }
    .ra-chip em { font-style: normal; font-size: 11.5px; padding: 1px 7px; border-radius: 999px; background: var(--line); color: var(--ink-2); }
    .ra-chip[aria-pressed="true"] { background: var(--acc); color: #fff; box-shadow: 0 6px 16px -6px var(--acc); }
    .ra-chip[aria-pressed="true"] em { background: rgba(255, 255, 255, .25); color: #fff; }

    .ra-search { position: relative; }
    .ra-search i { position: absolute; left: 13px; top: 50%; transform: translateY(-50%); color: var(--ink-3); font-size: 13px; pointer-events: none; }
    .ra-search input {
        width: 250px; padding: 10px 14px 10px 37px; border: 1px solid var(--line); border-radius: 12px;
        font: inherit; font-size: 13px; background: var(--surface-2); color: var(--ink);
        transition: border-color .15s, box-shadow .15s;
    }
    .ra-search input:focus { border-color: var(--acc); box-shadow: 0 0 0 4px var(--acc-soft); outline: none; }
    .ra-search input::placeholder { color: var(--ink-3); }

    /* ---------- operation cards ---------- */
    .ra-list { display: grid; grid-template-columns: repeat(auto-fill, minmax(380px, 1fr)); gap: 16px; }

    .ra-op {
        --st: var(--acc); --st-soft: var(--acc-soft);
        position: relative; display: flex; flex-direction: column; gap: 16px; padding: 20px 20px 18px; overflow: hidden;
        border-radius: 20px; background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow);
        transition: transform .2s, box-shadow .2s, border-color .2s;
    }
    .ra-op::before { /* coloured accent edge */
        content: ""; position: absolute; left: 0; right: 0; top: 0; height: 4px;
        background: linear-gradient(90deg, var(--st), transparent 85%);
    }
    .ra-op::after { /* soft glow in the corner */
        content: ""; position: absolute; width: 180px; height: 180px; right: -70px; top: -90px; border-radius: 50%;
        background: var(--st); opacity: .09; filter: blur(30px); pointer-events: none;
    }
    .ra-op:hover { transform: translateY(-3px); box-shadow: var(--shadow-hover); border-color: color-mix(in srgb, var(--st) 40%, var(--line)); }
    .ra-op.is-completed { --st: var(--ok); --st-soft: var(--ok-soft); }
    .ra-op.is-cancelled { --st: var(--bad); --st-soft: var(--bad-soft); }
    .ra-op.is-draft { --st: var(--idle); --st-soft: var(--idle-soft); }

    .ra-op-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; position: relative; z-index: 1; }

    .ra-date {
        display: flex; align-items: center; gap: 10px; padding: 6px 12px 6px 6px; border-radius: 14px;
        background: var(--surface-2); border: 1px solid var(--line);
    }
    .ra-date b {
        display: grid; place-items: center; width: 40px; height: 40px; border-radius: 10px;
        background: var(--st-soft); color: var(--st);
        font-family: 'Sora', sans-serif; font-size: 18px; font-weight: 800;
    }
    .ra-date span { font-size: 12px; line-height: 1.25; font-weight: 600; color: var(--ink-2); }
    .ra-date span small { display: block; font-size: 10.5px; font-weight: 500; color: var(--ink-3); text-transform: uppercase; letter-spacing: .05em; }
    .ra-date.is-none b { background: var(--idle-soft); color: var(--idle); font-size: 16px; }

    .ra-badge {
        display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px; border-radius: 999px;
        font-size: 12px; font-weight: 700; white-space: nowrap; background: var(--st-soft); color: var(--st);
    }
    .ra-badge::before { content: ""; width: 7px; height: 7px; border-radius: 50%; background: currentColor; box-shadow: 0 0 0 3px color-mix(in srgb, currentColor 25%, transparent); }

    .ra-op-body { position: relative; z-index: 1; }
    .ra-name { display: inline; font-size: 17px; font-weight: 700; line-height: 1.3; color: var(--ink); text-decoration: none; }
    a.ra-name:hover { color: var(--acc); text-decoration: underline; text-underline-offset: 3px; }
    .ra-code {
        display: inline-block; margin-left: 6px; padding: 2px 9px; border-radius: 8px; vertical-align: 2px;
        background: var(--surface-2); border: 1px dashed var(--line);
        font-size: 11.5px; color: var(--ink-2); font-weight: 600; letter-spacing: .02em;
    }
    .ra-desc { margin-top: 6px; font-size: 13px; line-height: 1.55; color: var(--ink-2); }

    .ra-meta { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 14px; }
    .ra-meta > div { min-width: 0; padding: 10px 12px; border-radius: 12px; background: var(--surface-2); border: 1px solid var(--line); }
    .ra-meta dt { font-size: 10.5px; color: var(--ink-3); font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
    .ra-meta dd { margin-top: 3px; font-size: 13px; font-weight: 600; line-height: 1.35; overflow-wrap: anywhere; }
    .ra-meta dd small { display: block; margin-top: 1px; font-size: 12px; font-weight: 400; color: var(--ink-2); }

    /* 3-step tracker */
    .ra-track { position: relative; z-index: 1; display: grid; grid-template-columns: repeat(3, 1fr); margin-top: auto; }
    .ra-track span {
        position: relative; display: flex; flex-direction: column; align-items: center; gap: 7px;
        font-size: 11.5px; color: var(--ink-3); font-weight: 600; text-align: center;
    }
    .ra-track span::before { /* connector line */
        content: ""; position: absolute; top: 10px; left: -50%; width: 100%; height: 3px; border-radius: 2px;
        background: var(--line); z-index: 0;
    }
    .ra-track span:first-child::before { display: none; }
    .ra-track span::after { /* node */
        content: ""; position: relative; z-index: 1; width: 22px; height: 22px; border-radius: 50%;
        background: var(--surface); border: 3px solid var(--line);
    }
    .ra-track .is-done { color: var(--ink-2); }
    .ra-track .is-done::before, .ra-track .is-now::before { background: var(--st); }
    .ra-track .is-done::after {
        background: var(--st) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath d='M4 8.5l2.5 2.5L12 5.5' fill='none' stroke='white' stroke-width='2.2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") center / 100% no-repeat;
        border-color: var(--st);
    }
    .ra-track .is-now { color: var(--ink); font-weight: 800; }
    .ra-track .is-now::after { border-color: var(--st); background: var(--surface); animation: ra-pulse 1.8s ease-out infinite; }
    @keyframes ra-pulse {
        0%   { box-shadow: 0 0 0 0 color-mix(in srgb, var(--st) 55%, transparent); }
        100% { box-shadow: 0 0 0 10px transparent; }
    }

    .ra-open {
        position: relative; z-index: 1; display: flex; align-items: center; justify-content: space-between; gap: 8px;
        padding: 11px 14px; border-radius: 12px; background: var(--surface-2); border: 1px solid var(--line);
        color: var(--ink); font-size: 13px; font-weight: 700; text-decoration: none;
        transition: background .15s, color .15s, border-color .15s;
    }
    .ra-open i { font-size: 18px; transition: transform .15s; }
    .ra-open:hover { background: var(--acc); border-color: var(--acc); color: #fff; }
    .ra-open:hover i { transform: translateX(4px); }

    /* ---------- empty states ---------- */
    .ra-empty { padding: 60px 22px; text-align: center; border-radius: 20px; background: var(--surface); border: 1px dashed var(--line); }
    .ra-empty .ic {
        width: 68px; height: 68px; margin: 0 auto 16px; border-radius: 22px; display: grid; place-items: center;
        background: var(--acc-soft); color: var(--acc); font-size: 28px; transform: rotate(-6deg);
    }
    .ra-empty h3 { font-size: 17px; font-weight: 700; line-height: 1.35; margin-bottom: 8px; }
    .ra-empty p { max-width: 420px; margin: 0 auto 20px; color: var(--ink-2); font-size: 13.5px; line-height: 1.6; }
    .ra-empty .ra-btn { margin-top: 0; }
    #raNoMatch { display: none; }

    .ra-pager { margin-top: 18px; }

    /* =====================================================
       GUIDE RAIL
       ===================================================== */
    .ra-rail {
        position: sticky; top: 16px; padding: 22px 20px; border-radius: 20px;
        background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow);
    }
    .ra-rail > h2 { font-size: 16px; font-weight: 700; }
    .ra-rail > p { margin: 4px 0 20px; font-size: 12.5px; color: var(--ink-2); line-height: 1.5; }

    .ra-flow { position: relative; }
    .ra-flow::before {
        content: ""; position: absolute; left: 17px; top: 22px; bottom: 22px; width: 2px;
        background: repeating-linear-gradient(to bottom, var(--line) 0 6px, transparent 6px 11px);
    }
    .ra-phase { position: relative; padding: 0 0 26px 54px; }
    .ra-phase:last-child { padding-bottom: 0; }
    .ra-phase:nth-child(1) { --c: var(--acc);  --c-soft: var(--acc-soft); }
    .ra-phase:nth-child(2) { --c: var(--warn); --c-soft: var(--warn-soft); }
    .ra-phase:nth-child(3) { --c: var(--ok);   --c-soft: var(--ok-soft); }
    .ra-node {
        position: absolute; left: 0; top: 0; width: 36px; height: 36px; border-radius: 12px;
        display: grid; place-items: center; font-size: 15px; font-weight: 800;
        background: var(--c-soft); color: var(--c); border: 1.5px solid var(--c);
        box-shadow: 0 0 0 5px var(--surface);
    }
    .ra-phase h3 { font-size: 15px; font-weight: 700; line-height: 36px; }
    .ra-phase small { display: block; margin: -4px 0 9px; font-size: 12px; color: var(--ink-3); font-weight: 500; }
    .ra-phase ul { display: grid; gap: 7px; margin-bottom: 12px; }
    .ra-phase li { font-size: 12.5px; line-height: 1.45; color: var(--ink-2); padding-left: 14px; position: relative; }
    .ra-phase li::before { content: ""; position: absolute; left: 0; top: 7px; width: 5px; height: 5px; border-radius: 2px; background: var(--c); }
    .ra-phase li b { color: var(--ink); font-weight: 600; }

    .ra-links { display: flex; flex-wrap: wrap; gap: 6px; }
    .ra-link {
        display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 10px;
        border: 1px solid var(--line); background: var(--surface-2); color: var(--ink);
        font: inherit; font-size: 12.5px; font-weight: 600; text-decoration: none; cursor: pointer;
        transition: border-color .15s, background .15s, transform .15s;
    }
    .ra-link i { color: var(--c, var(--acc)); }
    .ra-link:hover { border-color: var(--c, var(--acc)); background: var(--c-soft, var(--acc-soft)); color: var(--ink); transform: translateY(-1px); }

    /* =====================================================
       HELP
       ===================================================== */
    .ra-help { margin-top: 20px; border-radius: 18px; background: var(--surface); border: 1px solid var(--line); box-shadow: var(--shadow); }
    .ra-help summary { cursor: pointer; padding: 16px 20px; font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 10px; list-style: none; }
    .ra-help summary::-webkit-details-marker { display: none; }
    .ra-help summary > i:first-child { color: var(--acc); }
    .ra-help summary .chev { margin-left: auto; transition: transform .2s; color: var(--ink-3); }
    .ra-help[open] summary .chev { transform: rotate(180deg); }
    .ra-rules { display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 0 28px; padding: 0 20px 10px; }
    .ra-rule { display: flex; gap: 12px; padding: 13px 0; border-top: 1px solid var(--line); font-size: 13px; line-height: 1.5; color: var(--ink-2); }
    .ra-rule > i { flex: none; width: 32px; height: 32px; border-radius: 10px; display: grid; place-items: center; background: var(--acc-soft); color: var(--acc); font-size: 15px; }
    .ra-rule b { display: block; color: var(--ink); font-size: 13.5px; margin-bottom: 1px; }

    /* =====================================================
       RESPONSIVE
       ===================================================== */
    @media (max-width: 1199.98px) {
        .ra-body { grid-template-columns: 1fr; }
        .ra-rail { position: static; order: 2; }
        .ra-flow { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 24px; }
        .ra-flow::before { display: none; }
        .ra-phase { padding-bottom: 0; }
    }
    @media (max-width: 899.98px) {
        .ra-hero { grid-template-columns: 1fr; padding: 28px 24px; }
        .ra-ring-wrap { justify-self: start; }
    }
    @media (max-width: 575.98px) {
        .ra { padding: 12px; border-radius: 20px; }
        .ra-hero h1 { font-size: 30px; }
        .ra-ring-wrap { width: 100%; justify-content: space-between; padding: 14px; }
        .ra-ring { width: 108px; height: 108px; }
        .ra-btn { width: 100%; justify-content: center; }
        .ra-search, .ra-tools { width: 100%; }
        .ra-search input { width: 100%; }
        .ra-chips { max-width: 100%; overflow-x: auto; }
        .ra-chip { flex: none; }
        .ra-list { grid-template-columns: 1fr; }
        .ra-meta { grid-template-columns: 1fr; }
    }
    @media (prefers-reduced-motion: reduce) {
        .ra *, .ra *::before, .ra *::after { transition: none !important; animation: none !important; }
    }
</style>

<div class="container-fluid py-3">
<div class="ra">

    {{-- ============ HERO ============ --}}
    <header class="ra-hero">
        <div>
            <span class="ra-eyebrow">Barangay relief desk</span>
            <h1 class="ra-display">Relief Assistance</h1>
            <p>Plan a distribution, give each household a QR voucher, and record the claim on the day. Start with the step you need.</p>
            @if ($hasCreateRoute)
                <a href="{{ route('relief-assistance.create') }}" class="ra-btn">
                    <i class="bi bi-plus-lg"></i> New relief operation
                </a>
            @endif
        </div>

        @if ($pageCount > 0)
            <div class="ra-ring-wrap" role="img" aria-label="{{ $activeOperations }} active, {{ $completedOperations }} completed, {{ $cancelledOperations }} cancelled">
                <div class="ra-ring" style="--a: {{ $pctActive }}%; --b: {{ $pctDone }}%;">
                    <div class="ra-ring-center">
                        <b>{{ $donePercent }}%</b>
                        <span>completed</span>
                    </div>
                </div>
                <ul class="ra-legend">
                    <li><i style="background:#b9a8ff"></i><b>{{ $activeOperations }}</b> in progress</li>
                    <li><i style="background:#5df0b2"></i><b>{{ $completedOperations }}</b> completed</li>
                    <li><i style="background:#ff8da0"></i><b>{{ $cancelledOperations }}</b> cancelled</li>
                </ul>
            </div>
        @endif
    </header>

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

                            <div class="ra-op-top">
                                {{-- Distribution date --}}
                                @if ($dist)
                                    <div class="ra-date" title="Distribution date: {{ $dist->format('M d, Y') }}">
                                        <b>{{ $dist->format('d') }}</b>
                                        <span><small>Distribution</small>{{ $dist->format('M Y') }}</span>
                                    </div>
                                @else
                                    <div class="ra-date is-none" title="Distribution date not set">
                                        <b><i class="bi bi-calendar-x"></i></b>
                                        <span><small>Distribution</small>No date yet</span>
                                    </div>
                                @endif

                                <span class="ra-badge">{{ $statusLabel($operation->status) }}</span>
                            </div>

                            {{-- Details --}}
                            <div class="ra-op-body">
                                <div>
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

                            {{-- Progress --}}
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
                                    <span>Open operation</span> <i class="bi bi-arrow-right-short"></i>
                                </a>
                            @endif
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

                <div class="ra-empty">
                    <div class="ic"><i class="bi bi-box-seam"></i></div>
                    <h3 class="ra-display">No relief operations yet</h3>
                    <p>Create your first operation to set what is being given out. Then you can print the masterlist and start issuing QR vouchers.</p>
                    @if ($hasCreateRoute)
                        <a href="{{ route('relief-assistance.create') }}" class="ra-btn is-solid"><i class="bi bi-plus-lg"></i> Create the first operation</a>
                    @endif
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
            <h2 class="ra-display">How a distribution works</h2>
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
