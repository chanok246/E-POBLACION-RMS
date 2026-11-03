@extends('layouts.app')

@section('title', 'Masterlist Workspace | e-Poblacion')

@section('content')

@php
    $operation = $operation ?? null;

    if (!$operation) {
        abort(404);
    }

    $operationCode = $operation->operation_code
        ?? ('RA-' . str_pad($operation->id, 4, '0', STR_PAD_LEFT));

    $operationName = $operation->operation_name
        ?? 'Relief Assistance Operation';

    // Cast to string: Laravel converts empty query values to null,
    // which would otherwise break the "!== ''" checks below.
    $currentSearch = (string) ($search ?? request('search', ''));
    $currentPurok  = (string) ($purok ?? request('purok', ''));
    $currentFilter = (string) ($filter ?? request('filter', 'all'));

    if ($currentFilter === '') {
        $currentFilter = 'all';
    }

    $filterOptions = $filterOptions ?? [
        'all' => 'All',
        'household_heads' => 'Household Heads',
        'students' => 'Students',
        'senior_citizens' => 'Senior Citizens',
    ];

    $filterIcons = [
        'all' => 'bi-people',
        'household_heads' => 'bi-person-badge',
        'students' => 'bi-mortarboard',
        'senior_citizens' => 'bi-person-heart',
    ];

    $filterHints = [
        'all' => 'All matching active residents.',
        'household_heads' => 'Residents recorded as household heads.',
        'students' => 'Residents with occupation recorded as Student.',
        'senior_citizens' => 'Residents aged 60 and above.',
    ];

    $masterlistRecords = $masterlistRecords ?? collect();
    $households = $households ?? collect();
    $purokOptions = $purokOptions ?? [];
    $selectedHouseholdIds = $selectedHouseholdIds ?? [];

    if (!($masterlistRecords instanceof \Illuminate\Support\Collection)) {
        $masterlistRecords = collect($masterlistRecords);
    }

    $recordCount = $masterlistRecords->count();

    $householdCount = $masterlistRecords
        ->pluck('household_id')
        ->filter()
        ->unique()
        ->count();

    $filterLabel = $filterOptions[$currentFilter]
        ?? ucfirst(str_replace('_', ' ', $currentFilter));

    $masterlistUrl = route('relief-assistance.masterlist', [
        'operation_id' => $operation->id,
    ]);

    $masterlistPreviewUrl = route('relief-assistance.masterlist.print', [
        'operation_id' => $operation->id,
        'search' => $currentSearch,
        'purok' => $currentPurok,
        'filter' => $currentFilter,
    ]);

    $joinName = fn ($person) => trim(implode(' ', array_filter([
        data_get($person, 'first_name'),
        data_get($person, 'middle_name'),
        data_get($person, 'last_name'),
        data_get($person, 'suffix'),
    ])));

    $initials = function ($person) {
        $first = (string) data_get($person, 'first_name');
        $last  = (string) data_get($person, 'last_name');
        $out = mb_strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1));

        return $out !== '' ? $out : '?';
    };
@endphp

<style>
    /* =========================================================
       THEME TOKENS
       Light is the default. Dark follows the site toggle
       (data-theme / data-bs-theme) or, if the site sets none,
       the operating-system preference.
       ========================================================= */
    .ml {
        color-scheme: light;

        --bg:          #f5f4fb;
        --card:        #ffffff;
        --raise:       #faf9ff;
        --ink:         #1a1830;
        --ink-2:       #4a4764;
        --ink-3:       #6a6785;
        --line:        #e2dff0;
        --line-strong: #cfcbe3;

        --accent:      #5a3fd8;
        --accent-ink:  #ffffff;
        --accent-soft: #eeeaff;
        --accent-text: #4a30c4;

        --warm:        #f5a524;
        --warm-soft:   #fff4de;
        --warm-ink:    #5c3d00;
        --warm-line:   #f3d9a4;

        --shadow:      0 1px 2px rgba(26, 24, 48, .05), 0 8px 24px rgba(26, 24, 48, .05);
        --shadow-lift: 0 2px 4px rgba(26, 24, 48, .06), 0 14px 32px rgba(90, 63, 216, .12);
        --ring:        rgba(90, 63, 216, .28);
        --glow:        rgba(90, 63, 216, .16);

        max-width: 1320px;
        margin: 0 auto;
        color: var(--ink);
        font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
        -webkit-font-smoothing: antialiased;
    }

    html[data-theme="dark"] .ml,
    html[data-bs-theme="dark"] .ml,
    html.dark .ml {
        color-scheme: dark;

        --bg:          #14121f;
        --card:        #1d1a2e;
        --raise:       #242038;
        --ink:         #f4f2ff;
        --ink-2:       #cbc7e3;
        --ink-3:       #a19dbd;
        --line:        #3a3654;
        --line-strong: #4d4870;

        --accent:      #b3a4ff;
        --accent-ink:  #16122b;
        --accent-soft: #332c58;
        --accent-text: #cfc6ff;

        --warm:        #ffc857;
        --warm-soft:   #3b3019;
        --warm-ink:    #ffeab8;
        --warm-line:   #5d4a22;

        --shadow:      0 1px 2px rgba(0, 0, 0, .35), 0 8px 24px rgba(0, 0, 0, .28);
        --shadow-lift: 0 2px 4px rgba(0, 0, 0, .4), 0 14px 32px rgba(0, 0, 0, .4);
        --ring:        rgba(179, 164, 255, .38);
        --glow:        rgba(179, 164, 255, .14);
    }

    @media (prefers-color-scheme: dark) {
        html:not([data-theme]):not([data-bs-theme]):not(.dark) .ml {
            color-scheme: dark;

            --bg:          #14121f;
            --card:        #1d1a2e;
            --raise:       #242038;
            --ink:         #f4f2ff;
            --ink-2:       #cbc7e3;
            --ink-3:       #a19dbd;
            --line:        #3a3654;
            --line-strong: #4d4870;

            --accent:      #b3a4ff;
            --accent-ink:  #16122b;
            --accent-soft: #332c58;
            --accent-text: #cfc6ff;

            --warm:        #ffc857;
            --warm-soft:   #3b3019;
            --warm-ink:    #ffeab8;
            --warm-line:   #5d4a22;

            --shadow:      0 1px 2px rgba(0, 0, 0, .35), 0 8px 24px rgba(0, 0, 0, .28);
            --shadow-lift: 0 2px 4px rgba(0, 0, 0, .4), 0 14px 32px rgba(0, 0, 0, .4);
            --ring:        rgba(179, 164, 255, .38);
            --glow:        rgba(179, 164, 255, .14);
        }
    }

    /* =========================================================
       BASE
       ========================================================= */
    .ml *, .ml *::before, .ml *::after { box-sizing: border-box; }
    .ml h1, .ml h2, .ml h3, .ml p, .ml ol { margin: 0; }

    .ml a:focus-visible,
    .ml button:focus-visible,
    .ml summary:focus-visible,
    .ml input:focus-visible,
    .ml select:focus-visible {
        outline: 3px solid var(--ring);
        outline-offset: 2px;
    }

    .ml-display {
        font-family: 'Unbounded', 'Figtree', system-ui, sans-serif;
        letter-spacing: -.03em;
    }

    /* =========================================================
       HERO
       ========================================================= */
    .ml-hero {
        position: relative;
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 18px;
        padding: 26px 28px;
        overflow: hidden;
        border: 1px solid var(--line);
        border-radius: 24px;
        background:
            radial-gradient(520px 220px at 100% 0%, var(--glow), transparent 70%),
            linear-gradient(135deg, var(--accent-soft), var(--card) 62%);
        box-shadow: var(--shadow);
    }

    .ml-crumb {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 14px;
        color: var(--ink-3);
        font-size: 13px;
    }

    .ml-crumb a {
        color: var(--ink-2);
        font-weight: 700;
        text-decoration: none;
    }

    .ml-crumb a:hover { color: var(--accent-text); text-decoration: underline; }
    .ml-crumb span:last-child { color: var(--ink); font-weight: 700; }

    .ml-hero h1 {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        color: var(--ink);
        font-size: clamp(22px, 3vw, 30px);
        line-height: 1.3;
        font-weight: 800;
    }

    .ml-hero p {
        margin-top: 8px;
        color: var(--ink-2);
        font-size: 14.5px;
    }

    .ml-code {
        padding: 6px 13px;
        border: 1px solid var(--line-strong);
        border-radius: 999px;
        background: var(--card);
        color: var(--accent-text);
        font: 800 12px 'Figtree', sans-serif;
        letter-spacing: .02em;
        overflow-wrap: anywhere;
    }

    /* =========================================================
       BUTTONS
       ========================================================= */
    .ml-ghost,
    .ml-clear {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 44px;
        padding: 10px 18px;
        border: 1px solid var(--line-strong);
        border-radius: 999px;
        background: var(--card);
        color: var(--ink);
        font: inherit;
        font-size: 13.5px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: border-color .15s, background .15s, transform .15s;
    }

    .ml-ghost:hover,
    .ml-clear:hover {
        border-color: var(--accent);
        background: var(--accent-soft);
        color: var(--ink);
        transform: translateY(-1px);
    }

    .ml-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-height: 46px;
        padding: 0 22px;
        border: 1px solid var(--accent);
        border-radius: 999px;
        background: var(--accent);
        color: var(--accent-ink);
        font: inherit;
        font-size: 14px;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        box-shadow: 0 6px 16px var(--glow);
        transition: filter .15s, transform .15s;
    }

    .ml-btn:hover {
        filter: brightness(1.07);
        color: var(--accent-ink);
        transform: translateY(-1px);
    }

    .ml-btn[disabled] {
        opacity: .5;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
        filter: none;
    }

    /* =========================================================
       NOTE
       ========================================================= */
    .ml-note {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        margin-bottom: 18px;
        padding: 15px 18px;
        border: 1px solid var(--warm-line);
        border-left: 5px solid var(--warm);
        border-radius: 16px;
        background: var(--warm-soft);
        color: var(--warm-ink);
        font-size: 14px;
        line-height: 1.6;
    }

    .ml-note i { flex: none; margin-top: 1px; font-size: 19px; color: var(--warm); }
    .ml-note strong { color: var(--warm-ink); font-weight: 800; }

    /* =========================================================
       PANELS
       ========================================================= */
    .ml-panel {
        margin-bottom: 18px;
        overflow: hidden;
        border: 1px solid var(--line);
        border-radius: 22px;
        background: var(--card);
        color: var(--ink);
        box-shadow: var(--shadow);
    }

    .ml-filter { padding: 24px 26px; }

    .ml-label {
        display: block;
        margin-bottom: 10px;
        color: var(--ink);
        font-size: 13.5px;
        font-weight: 800;
    }

    /* Target-group chips */
    .ml-groups {
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
    }

    .ml-group {
        position: relative;
        display: inline-flex;
        margin: 0;
        cursor: pointer;
    }

    .ml-group input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        margin: 0;
        opacity: 0;
        cursor: pointer;
    }

    .ml-group span {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 44px;
        padding: 9px 17px;
        border: 1px solid var(--line-strong);
        border-radius: 999px;
        background: var(--raise);
        color: var(--ink-2);
        font-size: 13.5px;
        font-weight: 700;
        transition: background .15s, border-color .15s, color .15s;
    }

    .ml-group:hover span { border-color: var(--accent); color: var(--ink); }

    .ml-group input:checked + span {
        border-color: var(--accent);
        background: var(--accent);
        color: var(--accent-ink);
        box-shadow: 0 6px 14px var(--glow);
    }

    .ml-group input:focus-visible + span {
        outline: 3px solid var(--ring);
        outline-offset: 2px;
    }

    .ml-hint {
        min-height: 18px;
        margin-top: 11px;
        color: var(--ink-3);
        font-size: 12.5px;
    }

    /* Purok + search row */
    .ml-row {
        display: grid;
        grid-template-columns: 220px minmax(220px, 1fr) auto;
        align-items: start;
        gap: 16px;
        margin-top: 22px;
    }

    .ml .form-control,
    .ml .form-select {
        display: block;
        width: 100%;
        min-height: 46px;
        padding: 10px 14px;
        border: 1px solid var(--line-strong);
        border-radius: 14px;
        background-color: var(--raise);
        color: var(--ink);
        font-family: inherit;
        font-size: 14.5px;
        box-shadow: none;
        transition: border-color .15s, box-shadow .15s, background-color .15s;
    }

    .ml .form-control::placeholder { color: var(--ink-3); opacity: 1; }

    .ml .form-control:focus,
    .ml .form-select:focus {
        border-color: var(--accent);
        background-color: var(--card);
        color: var(--ink);
        box-shadow: 0 0 0 4px var(--ring);
    }

    .ml .form-select option { background: var(--card); color: var(--ink); }

    .ml-search { position: relative; }

    .ml-search > i {
        position: absolute;
        top: 50%;
        left: 15px;
        transform: translateY(-50%);
        color: var(--ink-3);
        pointer-events: none;
    }

    .ml-search .form-control { padding-left: 42px; padding-right: 42px; }

    /* hide native clear icon, we draw our own */
    .ml-search .form-control::-webkit-search-cancel-button { -webkit-appearance: none; appearance: none; }

    .ml-search-x {
        position: absolute;
        top: 50%;
        right: 8px;
        display: none;
        place-items: center;
        width: 30px;
        height: 30px;
        padding: 0;
        border: 0;
        border-radius: 50%;
        background: var(--accent-soft);
        color: var(--accent-text);
        cursor: pointer;
        transform: translateY(-50%);
    }

    .ml-search-x.is-visible { display: grid; }
    .ml-search-x:hover { filter: brightness(.95); }

    .ml-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        padding-top: 31px;
    }

    .ml-loading {
        display: none;
        align-items: center;
        gap: 7px;
        color: var(--ink-3);
        font-size: 12.5px;
        font-weight: 700;
    }

    .ml-loading.is-visible { display: inline-flex; }
    .ml-loading i { animation: ml-spin .8s linear infinite; }

    @keyframes ml-spin { to { transform: rotate(360deg); } }

    /* =========================================================
       LIVE REGION (swapped on every filter change)
       ========================================================= */
    #mlLive { position: relative; transition: opacity .15s; }
    #mlLive.is-busy { opacity: .55; pointer-events: none; }

    /* Summary */
    .ml-result {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        padding: 22px 26px;
    }

    .ml-stats {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .ml-stat {
        min-width: 150px;
        padding: 14px 18px;
        border: 1px solid var(--line);
        border-radius: 16px;
        background: var(--raise);
    }

    .ml-stat small {
        display: block;
        color: var(--ink-3);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .02em;
    }

    .ml-stat b {
        display: block;
        margin-top: 4px;
        color: var(--ink);
        font-family: 'Unbounded', sans-serif;
        font-size: 26px;
        line-height: 1.1;
        letter-spacing: -.03em;
    }

    .ml-stat b.is-text {
        font-family: 'Figtree', sans-serif;
        font-size: 17px;
        letter-spacing: 0;
    }

    .ml-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 14px;
        width: 100%;
    }

    .ml-tag {
        display: inline-flex;
        gap: 5px;
        padding: 5px 12px;
        border: 1px solid var(--line-strong);
        border-radius: 999px;
        background: var(--accent-soft);
        color: var(--ink-2);
        font-size: 12.5px;
        overflow-wrap: anywhere;
    }

    .ml-tag b { color: var(--accent-text); }

    .ml-cta { text-align: right; }

    .ml-preview-hint {
        max-width: 280px;
        margin: 9px 0 0 auto;
        color: var(--ink-3);
        font-size: 12px;
        line-height: 1.5;
    }

    /* Table panel */
    .ml-panel-head { padding: 22px 26px 18px; }

    .ml-panel-head h2 {
        color: var(--ink);
        font-size: 17px;
        font-weight: 800;
    }

    .ml-panel-head p {
        max-width: 680px;
        margin-top: 6px;
        color: var(--ink-2);
        font-size: 13.5px;
        line-height: 1.65;
    }

    .ml-scroll {
        max-height: 72vh;
        overflow: auto;
        border-top: 1px solid var(--line);
        -webkit-overflow-scrolling: touch;
    }

    .ml-table {
        width: 100%;
        min-width: 900px;
        border-collapse: separate;
        border-spacing: 0;
        background: var(--card);
        color: var(--ink);
    }

    .ml-table th {
        position: sticky;
        top: 0;
        z-index: 2;
        padding: 14px 16px;
        border-bottom: 1px solid var(--line-strong);
        background: var(--raise);
        color: var(--ink-2);
        font-size: 12px;
        font-weight: 800;
        letter-spacing: .03em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .ml-table td {
        padding: 14px 16px;
        border-bottom: 1px solid var(--line);
        background: var(--card);
        color: var(--ink);
        font-size: 14px;
        line-height: 1.55;
        vertical-align: middle;
    }

    .ml-table tbody tr { transition: background .12s; }
    .ml-table tbody tr:hover td { background: var(--raise); }
    .ml-table tbody tr:last-child td { border-bottom: 0; }

    .ml-table .n {
        width: 56px;
        padding-left: 26px;
        color: var(--ink-3);
        font-variant-numeric: tabular-nums;
    }

    .ml-person {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 210px;
    }

    .ml-avatar {
        display: grid;
        flex: none;
        place-items: center;
        width: 38px;
        height: 38px;
        border-radius: 12px;
        background: var(--accent-soft);
        color: var(--accent-text);
        font-size: 13px;
        font-weight: 800;
    }

    .ml-strong {
        color: var(--ink);
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .ml-rel {
        display: inline-block;
        padding: 4px 11px;
        border: 1px solid var(--line-strong);
        border-radius: 999px;
        background: var(--raise);
        color: var(--ink-2);
        font-size: 12.5px;
        font-weight: 600;
        overflow-wrap: anywhere;
    }

    .ml-hh {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 8px;
        background: var(--accent-soft);
        color: var(--accent-text);
        font-size: 13px;
        font-weight: 800;
    }

    .ml-muted {
        color: var(--ink-2);
        font-size: 13.5px;
        overflow-wrap: anywhere;
    }

    .ml-foot {
        padding: 15px 26px;
        border-top: 1px solid var(--line);
        background: var(--raise);
        color: var(--ink-2);
        font-size: 13.5px;
        line-height: 1.6;
    }

    .ml-foot b { color: var(--ink); }

    .ml-empty {
        padding: 60px 24px;
        border-top: 1px solid var(--line);
        background: var(--card);
        color: var(--ink);
        text-align: center;
    }

    .ml-empty .ic {
        display: grid;
        place-items: center;
        width: 66px;
        height: 66px;
        margin: 0 auto 16px;
        border-radius: 22px;
        background: var(--accent-soft);
        color: var(--accent-text);
        font-size: 26px;
    }

    .ml-empty h3 {
        margin-bottom: 10px;
        color: var(--ink);
        font-size: 17px;
        font-weight: 800;
    }

    .ml-empty p {
        max-width: 460px;
        margin: 0 auto 22px;
        color: var(--ink-2);
        font-size: 14px;
        line-height: 1.7;
    }

    /* Help */
    .ml-help {
        padding: 0 24px;
        border: 1px solid var(--line);
        border-radius: 18px;
        background: var(--card);
        color: var(--ink);
        box-shadow: var(--shadow);
    }

    .ml-help summary {
        display: flex;
        align-items: center;
        gap: 10px;
        min-height: 58px;
        cursor: pointer;
        list-style: none;
        color: var(--ink);
        font-size: 14.5px;
        font-weight: 800;
    }

    .ml-help summary::-webkit-details-marker { display: none; }
    .ml-help summary > i:first-child { color: var(--accent-text); }

    .ml-help summary .chev {
        margin-left: auto;
        color: var(--ink-2);
        transition: transform .18s;
    }

    .ml-help[open] summary .chev { transform: rotate(180deg); }

    .ml-help ol {
        display: grid;
        gap: 10px;
        padding: 0 0 22px 24px;
        color: var(--ink-2);
        font-size: 14px;
        line-height: 1.65;
    }

    .ml-help ol b { color: var(--ink); }

    /* =========================================================
       RESPONSIVE
       ========================================================= */
    @media (max-width: 991.98px) {
        .ml-row { grid-template-columns: 1fr 1fr; }
        .ml-actions { grid-column: 1 / -1; padding-top: 0; }
        .ml-cta { text-align: left; }
        .ml-preview-hint { margin-left: 0; }
    }

    @media (max-width: 767.98px) {
        /* table becomes a stack of cards */
        .ml-scroll { max-height: none; }
        .ml-table { min-width: 0; display: block; background: transparent; }
        .ml-table thead { display: none; }
        .ml-table tbody { display: grid; gap: 12px; padding: 14px; }

        .ml-table tbody tr {
            display: grid;
            gap: 2px;
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-radius: 16px;
            background: var(--card);
        }

        .ml-table td,
        .ml-table tbody tr:last-child td {
            display: flex;
            justify-content: space-between;
            gap: 14px;
            padding: 7px 0;
            border-bottom: 1px dashed var(--line);
            background: transparent !important;
            text-align: right;
        }

        .ml-table td:last-child { border-bottom: 0; }

        .ml-table td::before {
            content: attr(data-label);
            flex: none;
            color: var(--ink-3);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .03em;
            text-align: left;
            text-transform: uppercase;
        }

        .ml-table td.n { display: none; }

        .ml-table td.who {
            padding-top: 0;
            border-bottom: 1px solid var(--line);
        }

        .ml-table td.who::before { display: none; }
        .ml-person { min-width: 0; text-align: left; }
    }

    @media (max-width: 575.98px) {
        .ml-hero { padding: 20px 18px; border-radius: 20px; }
        .ml-hero > a { width: 100%; }
        .ml-row { grid-template-columns: 1fr; }
        .ml-filter, .ml-result { padding: 18px 16px; }
        .ml-panel-head { padding: 18px 16px 14px; }
        .ml-groups { flex-wrap: nowrap; overflow-x: auto; padding: 2px 2px 8px; }
        .ml-group { flex: 0 0 auto; }
        .ml-stat { flex: 1 1 140px; }
        .ml-cta { width: 100%; }
        .ml-cta .ml-btn { width: 100%; }
        .ml-preview-hint { max-width: none; }
        .ml-foot { padding: 14px 16px; }
        .ml-help { padding: 0 16px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .ml *, .ml *::before, .ml *::after {
            transition: none !important;
            animation: none !important;
        }
    }
</style>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">

<div class="container-fluid py-4 ml">

    {{-- HEADER --}}
    <header class="ml-hero">
        <div>
            <nav class="ml-crumb" aria-label="Breadcrumb">
                <a href="{{ route('relief-vouchers.index') }}">Relief Assistance</a>
                <span>/</span>
                <a href="{{ route('relief-assistance.show', $operation) }}">Operation Details</a>
                <span>/</span>
                <span>Masterlist Workspace</span>
            </nav>

            <h1 class="ml-display">
                Masterlist Workspace
                <span class="ml-code">{{ $operationCode }}</span>
            </h1>

            <p>{{ $operationName }}</p>
        </div>

        <a href="{{ route('relief-assistance.show', $operation) }}" class="ml-ghost">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Back to operation
        </a>
    </header>

    <div class="ml-note" role="note">
        <i class="bi bi-person-check-fill" aria-hidden="true"></i>
        <div>
            Search residents or choose a Purok and the list updates instantly.
            <strong>A matching record is not an approval.</strong>
            Beneficiary selection still requires authorized staff review.
        </div>
    </div>

    {{-- LIVE FILTERS --}}
    <section class="ml-panel ml-filter" aria-label="Find resident records">

        <form
            method="GET"
            action="{{ $masterlistUrl }}"
            id="masterlistFilterForm"
            autocomplete="off"
        >
            <input type="hidden" name="operation_id" value="{{ $operation->id }}">

            <fieldset style="border:0;padding:0;margin:0;min-width:0">
                <legend class="ml-label" style="float:none;padding:0">
                    Target group
                </legend>

                <div class="ml-groups">
                    @foreach ($filterOptions as $value => $label)
                        <label class="ml-group">
                            <input
                                type="radio"
                                name="filter"
                                value="{{ $value }}"
                                data-hint="{{ $filterHints[$value] ?? 'Available records.' }}"
                                {{ (string) $currentFilter === (string) $value ? 'checked' : '' }}
                            >
                            <span>
                                <i class="bi {{ $filterIcons[$value] ?? 'bi-funnel' }}" aria-hidden="true"></i>
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="ml-hint" id="mlHint" aria-live="polite">
                    {{ $filterHints[$currentFilter] ?? '' }}
                </div>
            </fieldset>

            <div class="ml-row">
                <div>
                    <label for="purok" class="ml-label">Purok</label>

                    <select name="purok" id="purok" class="form-select">
                        <option value="">All Puroks</option>

                        @foreach ($purokOptions as $purokValue => $purokLabel)
                            @php
                                // Supports ['Purok 1', 'Purok 2']
                                // and ['value' => 'label'] option arrays.
                                $optionValue = is_int($purokValue)
                                    ? $purokLabel
                                    : $purokValue;
                            @endphp

                            <option
                                value="{{ $optionValue }}"
                                {{ (string) $currentPurok === (string) $optionValue ? 'selected' : '' }}
                            >
                                {{ $purokLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="search" class="ml-label">Search records</label>

                    <div class="ml-search">
                        <i class="bi bi-search" aria-hidden="true"></i>
                        <input
                            type="search"
                            name="search"
                            id="search"
                            class="form-control"
                            value="{{ $currentSearch }}"
                            placeholder="Household no., resident, head, address..."
                            autocomplete="off"
                            spellcheck="false"
                            aria-describedby="searchHelp"
                        >
                        <button
                            type="button"
                            class="ml-search-x {{ $currentSearch !== '' ? 'is-visible' : '' }}"
                            id="mlSearchX"
                            aria-label="Clear search"
                        >
                            <i class="bi bi-x-lg" aria-hidden="true"></i>
                        </button>
                    </div>

                    <div class="ml-hint" id="searchHelp">
                        Filters as you type. No need to press Enter.
                    </div>
                </div>

                <div class="ml-actions">
                    <a href="{{ $masterlistUrl }}" class="ml-clear" id="mlClear" title="Clear search and filters">
                        <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                        Clear
                    </a>

                    <span class="ml-loading" id="mlLoading" role="status" aria-live="polite">
                        <i class="bi bi-arrow-repeat" aria-hidden="true"></i>
                        Updating…
                    </span>
                </div>
            </div>
        </form>
    </section>

    {{-- EVERYTHING BELOW IS REPLACED LIVE --}}
    <div id="mlLive" aria-live="polite">

        {{-- RESULTS SUMMARY --}}
        <section class="ml-panel ml-result" aria-label="Current result summary">
            <div style="flex:1 1 360px">
                <div class="ml-stats">
                    <div class="ml-stat">
                        <small>Resident records</small>
                        <b>{{ number_format($recordCount) }}</b>
                    </div>
                    <div class="ml-stat">
                        <small>Households</small>
                        <b>{{ number_format($householdCount) }}</b>
                    </div>
                    <div class="ml-stat">
                        <small>Target group</small>
                        <b class="is-text">{{ $filterLabel }}</b>
                    </div>
                </div>

                @if ($currentPurok !== '' || $currentSearch !== '')
                    <div class="ml-tags">
                        @if ($currentPurok !== '')
                            <span class="ml-tag">Purok: <b>{{ $currentPurok }}</b></span>
                        @endif

                        @if ($currentSearch !== '')
                            <span class="ml-tag">Search: <b>{{ $currentSearch }}</b></span>
                        @endif
                    </div>
                @endif
            </div>

            <div class="ml-cta">
                @if ($recordCount > 0)
                    <a
                        href="{{ $masterlistPreviewUrl }}"
                        target="_blank"
                        rel="noopener"
                        class="ml-btn"
                    >
                        <i class="bi bi-eye" aria-hidden="true"></i>
                        Preview masterlist
                    </a>
                @else
                    <button type="button" class="ml-btn" disabled>
                        <i class="bi bi-eye" aria-hidden="true"></i>
                        Preview masterlist
                    </button>
                @endif

                <div class="ml-preview-hint">
                    Review the generated list first. Printing starts from the preview page.
                </div>
            </div>
        </section>

        {{-- RESULTS TABLE --}}
        <section class="ml-panel" aria-labelledby="mlTableTitle">
            <div class="ml-panel-head">
                <h2 id="mlTableTitle" class="ml-display">Matching resident records</h2>
                <p>
                    One row is one resident. Household number, household head, Purok,
                    and address help authorized staff locate the correct household.
                </p>
            </div>

            @if ($recordCount > 0)
                <div class="ml-scroll">
                    <table class="ml-table">
                        <thead>
                            <tr>
                                <th class="n" scope="col">#</th>
                                <th scope="col">Target resident</th>
                                <th scope="col">Age</th>
                                <th scope="col">Relationship</th>
                                <th scope="col">Household no.</th>
                                <th scope="col">Household head</th>
                                <th scope="col">Purok</th>
                                <th scope="col">Address</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($masterlistRecords as $index => $record)
                                @php
                                    $resident = data_get($record, 'resident');
                                    $household = data_get($record, 'household');
                                    $householdHead = data_get($record, 'household_head');

                                    $residentName = $joinName($resident);
                                    $headName = $joinName($householdHead);

                                    $householdNumber = data_get($record, 'household_number')
                                        ?? data_get($household, 'household_number');

                                    $purokName = data_get($record, 'purok')
                                        ?? data_get($household, 'purok');

                                    $address = data_get($record, 'address')
                                        ?? data_get($household, 'address');

                                    $relationship = data_get($record, 'relationship') ?? '—';
                                    $age = data_get($resident, 'age');

                                    if ($age === null && data_get($resident, 'birth_date')) {
                                        try {
                                            $age = \Illuminate\Support\Carbon::parse(
                                                data_get($resident, 'birth_date')
                                            )->age;
                                        } catch (\Throwable $exception) {
                                            $age = null;
                                        }
                                    }
                                @endphp

                                <tr>
                                    <td class="n" data-label="#">{{ $index + 1 }}</td>
                                    <td class="who" data-label="Resident">
                                        <div class="ml-person">
                                            <span class="ml-avatar" aria-hidden="true">{{ $initials($resident) }}</span>
                                            <span class="ml-strong">{{ $residentName ?: '—' }}</span>
                                        </div>
                                    </td>
                                    <td data-label="Age">{{ $age !== null ? $age : '—' }}</td>
                                    <td data-label="Relationship"><span class="ml-rel">{{ $relationship }}</span></td>
                                    <td data-label="Household no."><span class="ml-hh">{{ $householdNumber ?: '—' }}</span></td>
                                    <td data-label="Household head">{{ $headName ?: '—' }}</td>
                                    <td data-label="Purok">{{ $purokName ?: '—' }}</td>
                                    <td data-label="Address"><span class="ml-muted">{{ $address ?: '—' }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="ml-foot">
                    Showing <b>{{ number_format($recordCount) }}</b>
                    matching resident {{ $recordCount === 1 ? 'record' : 'records' }}
                    across <b>{{ number_format($householdCount) }}</b>
                    {{ $householdCount === 1 ? 'household' : 'households' }}.
                </div>
            @else
                <div class="ml-empty">
                    <div class="ic">
                        <i class="bi bi-search" aria-hidden="true"></i>
                    </div>

                    <h3 class="ml-display">No matching records found</h3>

                    <p>
                        No resident records matched your current search, target group,
                        or Purok. Try a different search or clear the filters.
                    </p>

                    <a href="{{ $masterlistUrl }}" class="ml-ghost" data-ml-clear>
                        <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                        Clear search and filters
                    </a>
                </div>
            @endif
        </section>
    </div>

    {{-- WORKFLOW HELP --}}
    <details class="ml-help">
        <summary>
            <i class="bi bi-question-circle" aria-hidden="true"></i>
            What happens after this list?
            <i class="bi bi-chevron-down chev" aria-hidden="true"></i>
        </summary>

        <ol>
            <li><b>Preview and print.</b> Review the list before printing it.</li>
            <li><b>Field assessment.</b> BHWs or authorized staff verify household conditions.</li>
            <li><b>Return the list.</b> Submit the completed field list to the Barangay Hall.</li>
            <li><b>Encode beneficiaries.</b> Authorized staff review the assessment and select actual beneficiaries. Voucher generation follows the existing authorized workflow.</li>
        </ol>
    </details>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form     = document.getElementById('masterlistFilterForm');
    var search   = document.getElementById('search');
    var purok    = document.getElementById('purok');
    var radios   = document.querySelectorAll('.ml-group input[type="radio"]');
    var hint     = document.getElementById('mlHint');
    var loading  = document.getElementById('mlLoading');
    var live     = document.getElementById('mlLive');
    var clearBtn = document.getElementById('mlClear');
    var searchX  = document.getElementById('mlSearchX');

    if (!form || !live) {
        return;
    }

    var DEBOUNCE_MS = 120;   // small delay so fast typing sends one request, not ten
    var timer = null;
    var controller = null;
    var composing = false;   // true while an IME (e.g. mobile keyboard) is mid-composition

    function buildUrl() {
        var params = new URLSearchParams(new FormData(form));

        // keep the address bar clean: drop empty values
        Array.from(params.keys()).forEach(function (key) {
            if (params.get(key) === '') {
                params.delete(key);
            }
        });

        var url = new URL(form.action, window.location.href);
        url.search = params.toString();

        return url;
    }

    function syncSearchX() {
        if (searchX && search) {
            searchX.classList.toggle('is-visible', search.value !== '');
        }
    }

    function setBusy(isBusy) {
        live.classList.toggle('is-busy', isBusy);
        live.setAttribute('aria-busy', isBusy ? 'true' : 'false');

        if (loading) {
            loading.classList.toggle('is-visible', isBusy);
        }
    }

    function refresh() {
        window.clearTimeout(timer);
        syncSearchX();

        // cancel the previous request so a slow old response can never
        // overwrite a newer one (this is what keeps results accurate)
        if (controller) {
            controller.abort();
        }

        controller = new AbortController();
        var myController = controller;
        var url = buildUrl();

        setBusy(true);

        fetch(url.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'text/html' },
            signal: myController.signal
        })
        .then(function (response) {
            if (!response.ok) {
                throw new Error('Request failed: ' + response.status);
            }

            return response.text();
        })
        .then(function (html) {
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var fresh = doc.getElementById('mlLive');

            if (!fresh) {
                throw new Error('Live region missing from response');
            }

            live.innerHTML = fresh.innerHTML;
            window.history.replaceState(null, '', url.toString());
            setBusy(false);
        })
        .catch(function (error) {
            if (error && error.name === 'AbortError') {
                return; // superseded by a newer request, ignore
            }

            // fall back to a normal page load so the user still gets results
            window.location.assign(url.toString());
        });
    }

    function refreshSoon() {
        window.clearTimeout(timer);
        timer = window.setTimeout(refresh, DEBOUNCE_MS);
    }

    // Every keystroke filters the list.
    if (search) {
        search.addEventListener('compositionstart', function () { composing = true; });
        search.addEventListener('compositionend', function () {
            composing = false;
            refreshSoon();
        });

        search.addEventListener('input', function () {
            syncSearchX();

            if (!composing) {
                refreshSoon();
            }
        });
    }

    // Purok dropdown applies immediately, together with the current search text.
    if (purok) {
        purok.addEventListener('change', refresh);
    }

    // Target group applies immediately as well.
    radios.forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (hint) {
                hint.textContent = radio.dataset.hint || '';
            }

            refresh();
        });
    });

    // Pressing Enter should not reload the page; just run the filter now.
    form.addEventListener('submit', function (event) {
        event.preventDefault();
        refresh();
    });

    function resetAll(event) {
        event.preventDefault();

        if (search) { search.value = ''; }
        if (purok)  { purok.value = ''; }

        var first = null;

        radios.forEach(function (radio) {
            if (radio.value === 'all') {
                first = radio;
            }
        });

        if (!first && radios.length) {
            first = radios[0];
        }

        radios.forEach(function (radio) {
            radio.checked = (radio === first);
        });

        if (hint && first) {
            hint.textContent = first.dataset.hint || '';
        }

        refresh();

        if (search) { search.focus(); }
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', resetAll);
    }

    // The empty-state "Clear" link lives inside the swapped region.
    live.addEventListener('click', function (event) {
        var target = event.target.closest('[data-ml-clear]');

        if (target) {
            resetAll(event);
        }
    });

    // The small "x" inside the search box clears only the text.
    if (searchX) {
        searchX.addEventListener('click', function () {
            search.value = '';
            search.focus();
            refresh();
        });
    }

    syncSearchX();
});
</script>

@endsection
