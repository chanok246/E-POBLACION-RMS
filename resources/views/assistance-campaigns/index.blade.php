{{-- resources/views/relief-vouchers/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Relief Assistance | e-Poblacion')

@section('content')

@php
    $operations = $operations ?? collect();
    $recentVouchers = $recentVouchers ?? collect();

    $totalOperations = $totalOperations ?? $operations->count();

    $activeOperations = $activeOperations
        ?? $operations->whereIn('status', ['draft', 'ready', 'distribution'])->count();

    $completedOperations = $completedOperations
        ?? $operations->whereIn('status', ['completed', 'closed'])->count();

    $totalVouchers = $totalVouchers ?? 0;
    $issuedVouchers = $issuedVouchers ?? 0;
    $claimedVouchers = $claimedVouchers ?? 0;
    $cancelledVouchers = $cancelledVouchers ?? 0;

    $hasOperations = $operations->count() > 0;
    $latestOperation = $hasOperations ? $operations->first() : null;
    $latestStatus = strtolower((string) ($latestOperation->status ?? ''));

    $claimPercent = $totalVouchers > 0
        ? (int) round(($claimedVouchers / $totalVouchers) * 100)
        : 0;

    $seg = fn ($n) => $totalVouchers > 0
        ? round(($n / $totalVouchers) * 100, 2)
        : 0;

    $waiting = max(
        0,
        $totalVouchers - $claimedVouchers - $issuedVouchers - $cancelledVouchers
    );

    $statusLabels = [
        'draft' => 'Draft',
        'ready' => 'Ready',
        'distribution' => 'For distribution',
        'completed' => 'Completed',
        'closed' => 'Closed',
        'cancelled' => 'Cancelled',
    ];

    $statusTones = [
        'draft' => 'is-grey',
        'ready' => 'is-blue',
        'distribution' => 'is-mango',
        'completed' => 'is-green',
        'closed' => 'is-green',
        'cancelled' => 'is-red',
    ];

    $voucherTones = [
        'pending' => 'is-grey',
        'generated' => 'is-blue',
        'issued' => 'is-blue',
        'distributed' => 'is-mango',
        'claimed' => 'is-green',
        'cancelled' => 'is-red',
    ];

    $showNextAction = true;

    if (!$hasOperations) {
        $next = [
            'Create your first operation',
            'Set the assistance type, stock, schedule and location. Use the + New operation button above to get started.',
            null,
            'bi-plus-circle',
            1
        ];

        $showNextAction = false;
    } elseif ($latestStatus === 'draft') {
        $next = [
            'Encode the beneficiaries',
            'Finish the field assessment for ' . ($latestOperation->operation_name ?? 'the latest operation') . '.',
            route('relief-assistance.beneficiaries', ['operation' => $latestOperation->id]),
            'bi-people',
            3
        ];
    } elseif ($latestStatus === 'ready') {
        $next = [
            'Issue the vouchers',
            'Generate and prepare vouchers for ' . ($latestOperation->operation_name ?? 'the latest operation') . '.',
            route('relief-assistance.show', ['operation' => $latestOperation->id]),
            'bi-ticket-perforated',
            4
        ];
    } elseif ($latestStatus === 'distribution') {
        $next = [
            'Scan and claim',
            'Distribution is open. Scan QR vouchers as households arrive.',
            route('relief-vouchers.qr-verification'),
            'bi-qr-code-scan',
            5
        ];
    } else {
        $next = [
            'Operation completed',
            'The latest operation is finished. To prepare another distribution, use the + New operation button above.',
            null,
            'bi-check-circle',
            1
        ];

        $showNextAction = false;
    }

    $nowStep = $next[4];

    $workflow = [
        ['Create operation', 'Define the relief activity, assistance type, schedule and location.', 'bi-plus-circle'],
        ['Assess households', 'Record household impact and beneficiary eligibility.', 'bi-clipboard-check'],
        ['Encode beneficiaries', 'Verify household information and supporting requirements.', 'bi-people'],
        ['Generate vouchers', 'Generate unique vouchers and prepare the printable distribution list.', 'bi-ticket-perforated'],
        ['Scan & claim', 'Verify the QR voucher and record the one-time claim.', 'bi-qr-code-scan'],
    ];
@endphp

<style>
/* ============================================================
   RELIEF VOUCHERS — THEME COLORS

   Color meaning (kept consistent in light and dark):
   - Green  = positive / completed / claimed / primary action
   - Blue   = informational / ready / issued
   - Mango  = in progress / waiting / for distribution
   - Red    = cancelled / destructive
   - Grey   = neutral / draft / pending
   - Ube    = Relief Assistance module label
   ============================================================ */

.rv-page,
.rv-page[data-rv-theme="light"] {
    color-scheme: light;

    --rv-bg: #edf0ee;
    --rv-card: #f8f9f8;
    --rv-wash: #e7ebe8;
    --rv-line: #d5ddd7;

    --rv-ink: #26332b;
    --rv-ink2: #4f5d54;
    --rv-ink3: #5f6d64;
    --rv-hint: #7d8a82;

    --rv-accent: #1f7a45;
    --rv-accent-ink: #17623a;
    --rv-accent-soft: #dceee2;
    --rv-on-accent: #ffffff;

    /* Buttons — primary */
    --rv-btn-bg: #1f7a45;
    --rv-btn-hover: #17623a;
    --rv-btn-active: #124d2e;
    --rv-btn-text: #ffffff;
    --rv-btn-shadow: 0 1px 2px rgba(23, 98, 58, .28);
    --rv-btn-shadow-hover: 0 6px 14px -6px rgba(23, 98, 58, .55);

    /* Buttons — ghost / outline */
    --rv-ghost-border: #a9b6ad;
    --rv-ghost-hover-bg: #dceee2;
    --rv-ghost-active-bg: #c9e4d3;

    --rv-grey: #4f5d54;
    --rv-grey-soft: #e8ece9;
    --rv-grey-line: #d0d8d2;

    --rv-blue: #2f6fb5;
    --rv-blue-soft: #e5effb;
    --rv-blue-ink: #24578f;
    --rv-blue-line: #c5d9ef;

    --rv-ube: #a63a62;
    --rv-ube-soft: #f8e8ee;
    --rv-ube-line: #edc6d5;

    --rv-mango: #d49a19;
    --rv-mango-soft: #fff3d7;
    --rv-mango-ink: #805b0c;
    --rv-mango-line: #efd99f;

    --rv-green: #1d7340;
    --rv-green-soft: #e2f1e7;
    --rv-green-line: #b9ddc4;

    --rv-red: #b3402f;
    --rv-red-soft: #fae9e6;
    --rv-red-line: #edc3bc;

    --rv-radius: 14px;
    --rv-shadow: 0 1px 3px rgba(30, 45, 35, .05);
    --rv-shadow-hover: 0 4px 14px -6px rgba(30, 45, 35, .14);
}

.rv-page[data-rv-theme="dark"] {
    color-scheme: dark;

    --rv-bg: #111a15;
    --rv-card: #19251e;
    --rv-wash: #223128;
    --rv-line: #34463a;

    --rv-ink: #e8f0ea;
    --rv-ink2: #bccabf;
    --rv-ink3: #a0b1a6;
    --rv-hint: #73857a;

    --rv-accent: #65ce87;
    --rv-accent-ink: #8be0a8;
    --rv-accent-soft: rgba(101, 206, 135, .15);
    --rv-on-accent: #102117;

    /* Buttons — primary */
    --rv-btn-bg: #65ce87;
    --rv-btn-hover: #86dba1;
    --rv-btn-active: #4fb673;
    --rv-btn-text: #0c1a11;
    --rv-btn-shadow: none;
    --rv-btn-shadow-hover: 0 6px 16px -6px rgba(101, 206, 135, .45);

    /* Buttons — ghost / outline */
    --rv-ghost-border: #51695a;
    --rv-ghost-hover-bg: rgba(101, 206, 135, .16);
    --rv-ghost-active-bg: rgba(101, 206, 135, .26);

    --rv-grey: #bccabf;
    --rv-grey-soft: #2a382f;
    --rv-grey-line: #3d4f43;

    --rv-blue: #78aee0;
    --rv-blue-soft: rgba(120, 174, 224, .15);
    --rv-blue-ink: #a8cef0;
    --rv-blue-line: rgba(120, 174, 224, .35);

    --rv-ube: #ee91ae;
    --rv-ube-soft: rgba(238, 145, 174, .14);
    --rv-ube-line: rgba(238, 145, 174, .35);

    --rv-mango: #efbd4b;
    --rv-mango-soft: rgba(239, 189, 75, .14);
    --rv-mango-ink: #f5d58b;
    --rv-mango-line: rgba(239, 189, 75, .35);

    --rv-green: #65ce87;
    --rv-green-soft: rgba(101, 206, 135, .14);
    --rv-green-line: rgba(101, 206, 135, .35);

    --rv-red: #f08d7d;
    --rv-red-soft: rgba(240, 141, 125, .14);
    --rv-red-line: rgba(240, 141, 125, .35);

    --rv-shadow: none;
    --rv-shadow-hover: 0 6px 18px -8px rgba(0, 0, 0, .5);
}

/* PAGE CONTAINER */

:is(main, section, article, div):has(> .rv-page),
:is(.container, .container-fluid, .container-xxl, .container-xl,
    .container-lg, .content, .content-wrapper, .main-content,
    .page-content, .app-content, .page-body, .main, main):has(.rv-page) {
    max-width: none !important;
}

.rv-page {
    min-width: 0;
    width: auto;
    max-width: none !important;
    margin: 0 !important;
    padding: 16px !important;

    border: 1px solid var(--rv-line);
    border-radius: 18px;

    background: var(--rv-bg) !important;
    color: var(--rv-ink) !important;

    font-family: inherit;

    display: flex;
    flex-direction: column;
    gap: 16px;

    transition: background-color .2s ease, color .2s ease;
}

.rv-page *,
.rv-page *::before,
.rv-page *::after {
    box-sizing: border-box;
}

.rv-page h1,
.rv-page h2,
.rv-page h3,
.rv-page p {
    margin: 0;
    color: var(--rv-ink);
}

.rv-page h1,
.rv-page h2 {
    letter-spacing: -.02em;
}

.rv-page .text-muted,
.rv-page small {
    color: var(--rv-ink2) !important;
}

.rv-page a:focus-visible,
.rv-page button:focus-visible,
.rv-page input:focus-visible {
    outline: 2px solid var(--rv-accent);
    outline-offset: 2px;
}

/* ============================================================
   BUTTONS
   Primary  = solid green. Hover darkens in light mode and
              lightens in dark mode so the change is always visible.
   Ghost    = outlined. Hover fills with a green tint and turns the
              border and text green.
   ============================================================ */

.rv-page .rv-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    min-height: 38px;
    padding: 9px 18px;

    border: 1px solid var(--rv-btn-bg);
    border-radius: 999px;

    background: var(--rv-btn-bg);
    color: var(--rv-btn-text) !important;
    box-shadow: var(--rv-btn-shadow);

    font: inherit;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.2;

    text-decoration: none !important;
    white-space: nowrap;
    cursor: pointer;
    user-select: none;

    transition:
        background-color .15s ease,
        border-color .15s ease,
        color .15s ease,
        box-shadow .15s ease,
        transform .15s ease;
}

.rv-page .rv-btn .bi {
    color: inherit;
    transition: transform .15s ease;
}

.rv-page .rv-btn:hover {
    background: var(--rv-btn-hover);
    border-color: var(--rv-btn-hover);
    color: var(--rv-btn-text) !important;
    box-shadow: var(--rv-btn-shadow-hover);
    transform: translateY(-1px);
}

.rv-page .rv-btn:hover .bi-arrow-right {
    transform: translateX(3px);
}

.rv-page .rv-btn:active {
    background: var(--rv-btn-active);
    border-color: var(--rv-btn-active);
    box-shadow: none;
    transform: translateY(0) scale(.98);
}

.rv-page .rv-btn:focus-visible {
    outline: 2px solid var(--rv-accent);
    outline-offset: 3px;
}

.rv-page .rv-btn.disabled,
.rv-page .rv-btn[disabled],
.rv-page .rv-btn[aria-disabled="true"] {
    opacity: .55;
    cursor: not-allowed;
    pointer-events: none;
    box-shadow: none;
}

/* Small size */

.rv-page .rv-btn--sm {
    min-height: 32px;
    padding: 6px 14px;
    font-size: 12.5px;
}

/* Ghost / outline */

.rv-page .rv-btn--ghost {
    background: transparent;
    border-color: var(--rv-ghost-border);
    color: var(--rv-ink) !important;
    box-shadow: none;
}

.rv-page .rv-btn--ghost:hover {
    background: var(--rv-ghost-hover-bg);
    border-color: var(--rv-accent);
    color: var(--rv-accent-ink) !important;
    box-shadow: none;
    transform: translateY(-1px);
}

.rv-page .rv-btn--ghost:active {
    background: var(--rv-ghost-active-bg);
    border-color: var(--rv-accent);
    color: var(--rv-accent-ink) !important;
    transform: translateY(0) scale(.98);
}

/* HERO */

.rvi-hero {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(320px, 440px);
    gap: 24px;
    align-items: stretch;

    padding: 24px 26px;

    border-radius: var(--rv-radius);
    background: var(--rv-card);
    border: 1px solid var(--rv-line);
    box-shadow: var(--rv-shadow);
}

.rvi-hero-main {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 0;
}

.rvi-hero h1 {
    margin-top: 12px;
    font-size: clamp(1.4rem, 2.2vw, 1.9rem);
    line-height: 1.15;
    font-weight: 700;
}

.rvi-hero .lead {
    margin-top: 8px;
    max-width: 520px;
    font-size: 14px;
    line-height: 1.6;
    color: var(--rv-ink2) !important;
}

.rvi-next {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 14px;

    padding: 18px;

    border-radius: 12px;
    background: var(--rv-wash);
    border: 1px solid var(--rv-line);
}

.rvi-next-tag {
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .09em;
    text-transform: uppercase;
    color: var(--rv-ink3);
}

.rvi-next-left {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    min-width: 0;
}

.rvi-next-icon {
    flex: none;
    width: 40px;
    height: 40px;

    display: grid;
    place-items: center;

    border-radius: 10px;
    background: var(--rv-accent-soft);
    color: var(--rv-accent);
    font-size: 1.05rem;
}

.rvi-next h3 {
    font-size: .98rem;
    font-weight: 700;
}

.rvi-next span {
    display: block;
    margin-top: 3px;
    font-size: 12.5px;
    line-height: 1.5;
    color: var(--rv-ink2);
}

.rvi-next-note {
    padding-top: 12px;
    border-top: 1px solid var(--rv-line);
    font-size: 12px;
    line-height: 1.5;
    color: var(--rv-ink2);
}

/* SUMMARY TILES */

.rvi-tiles {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));

    border-radius: var(--rv-radius);
    background: var(--rv-card);
    border: 1px solid var(--rv-line);
    box-shadow: var(--rv-shadow);
    overflow: hidden;
}

.rvi-tile {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;

    padding: 18px 22px;
    border-right: 1px solid var(--rv-line);
}

.rvi-tile:last-child {
    border-right: 0;
}

.rvi-tile-ic {
    flex: none;
    width: 38px;
    height: 38px;

    display: grid;
    place-items: center;

    border-radius: 10px;
    background: var(--rv-wash);
    color: var(--rv-accent);
    font-size: 1rem;
}

.rvi-tile small {
    display: block;
    font-size: 10.5px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .08em;
    color: var(--rv-ink2) !important;
}

.rvi-tile strong {
    display: block;
    margin-top: 2px;
    font-size: 1.6rem;
    line-height: 1.1;
    font-weight: 700;
    color: var(--rv-ink);
}

/* VOUCHER PROGRESS */

.rvi-bar {
    padding: 18px 22px;

    border-radius: var(--rv-radius);
    background: var(--rv-card);
    border: 1px solid var(--rv-line);
    box-shadow: var(--rv-shadow);
}

.rvi-bar-top {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 16px;

    margin-bottom: 12px;

    font-size: 13px;
    color: var(--rv-ink2);
    font-weight: 600;
}

.rvi-bar-top strong {
    font-size: 1.15rem;
    line-height: 1;
    font-weight: 700;
    color: var(--rv-accent);
}

.rvi-bar-track {
    display: flex;
    gap: 2px;
    width: 100%;
    height: 8px;
    overflow: hidden;

    border-radius: 999px;
    background: var(--rv-grey-soft);
}

.rvi-bar-segment {
    height: 100%;
    min-width: 0;
    border-radius: 999px;
    transform-origin: left center;
    animation: rv-grow .8s cubic-bezier(.2, .8, .2, 1) both;
}

.rvi-bar-issued { background: var(--rv-blue); }
.rvi-bar-claimed { background: var(--rv-green); }
.rvi-bar-cancelled { background: var(--rv-red); }
.rvi-bar-waiting { background: var(--rv-mango); }

@keyframes rv-grow {
    from { transform: scaleX(0); }
    to { transform: scaleX(1); }
}

.rvi-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 8px 22px;
    margin-top: 14px;
    font-size: 12.5px;
    color: var(--rv-ink2);
}

.rvi-legend-item {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    font-weight: 500;
}

.rvi-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

/* WORKFLOW */

.rvi-steps {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 0;

    border-radius: var(--rv-radius);
    background: var(--rv-card);
    border: 1px solid var(--rv-line);
    box-shadow: var(--rv-shadow);
    overflow: hidden;
}

.rvi-step {
    position: relative;
    padding: 18px 20px;
    border-right: 1px solid var(--rv-line);
    transition: background .15s;
}

.rvi-step:last-child {
    border-right: 0;
}

.rvi-step-number {
    width: 28px;
    height: 28px;

    display: grid;
    place-items: center;

    margin-bottom: 12px;

    border-radius: 50%;
    background: var(--rv-grey-soft);
    color: var(--rv-ink2);

    font-size: .78rem;
    font-weight: 700;
}

.rvi-step h3 {
    font-size: .9rem;
    font-weight: 600;
}

.rvi-step p {
    margin-top: 5px;
    font-size: 12px;
    line-height: 1.5;
    color: var(--rv-ink2);
}

.rvi-step.is-done .rvi-step-number {
    background: var(--rv-accent);
    color: var(--rv-on-accent);
}

.rvi-step.is-now {
    background: var(--rv-accent-soft);
}

.rvi-step.is-now::before {
    content: "";
    position: absolute;
    left: 0;
    right: 0;
    top: 0;
    height: 2px;
    background: var(--rv-accent);
}

.rvi-step.is-now .rvi-step-number {
    background: var(--rv-accent);
    color: var(--rv-on-accent);
    animation: rv-pulse 1.8s ease-out infinite;
}

@keyframes rv-pulse {
    0% {
        box-shadow: 0 0 0 0 color-mix(in srgb, var(--rv-accent) 50%, transparent);
    }
    100% {
        box-shadow: 0 0 0 8px transparent;
    }
}

/* SECTION HEADERS */

.rv-section-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    flex-wrap: wrap;
    gap: 12px;
    margin: 14px 0 -4px;
}

.rv-section-head h2 {
    font-size: 1.1rem;
    font-weight: 700;
}

.rv-section-head p {
    margin-top: 2px;
    font-size: 12.5px;
    color: var(--rv-ink2);
}

.rvi-tools {
    display: flex;
    align-items: center;
    gap: 12px;
}

.rv-search {
    position: relative;
    flex: 1 1 auto;
    min-width: 0;
    max-width: 420px;
}

.rv-search .bi {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: var(--rv-hint);
    font-size: 13px;
}

.rv-search input {
    width: 100%;
    min-height: 40px;

    padding: 8px 14px 8px 38px;

    border: 1px solid var(--rv-ghost-border);
    border-radius: 999px;

    background: var(--rv-card);
    color: var(--rv-ink);

    font: inherit;
    font-size: 13px;
    font-weight: 500;

    outline: none;
    box-shadow: none;

    transition: border-color .15s, box-shadow .15s;
}

.rv-search input:hover {
    border-color: var(--rv-accent);
}

.rv-search input::placeholder {
    color: var(--rv-hint) !important;
    font-weight: 400;
    opacity: 1;
}

.rv-search input:focus::placeholder {
    opacity: .55;
}

.rv-search input:focus {
    border-color: var(--rv-accent);
    box-shadow: 0 0 0 3px var(--rv-accent-soft);
    background: var(--rv-card);
    color: var(--rv-ink);
}

/* TABLES */

.rv-panel {
    border-radius: var(--rv-radius);
    overflow: hidden;
    background: var(--rv-card);
    border: 1px solid var(--rv-line);
    box-shadow: var(--rv-shadow);
}

.rv-table-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.rv-table {
    width: 100%;
    margin: 0;
    border-collapse: separate;
    border-spacing: 0;
    background: transparent;
    color: var(--rv-ink);
}

.rv-table th,
.rv-table td {
    padding: 13px 20px;
    vertical-align: middle;
    white-space: nowrap;

    border: 0;
    border-bottom: 1px solid var(--rv-line);

    background: transparent;
    color: var(--rv-ink);
}

.rv-table thead th {
    padding-top: 11px;
    padding-bottom: 11px;

    background: var(--rv-wash);
    color: var(--rv-ink3);

    font-size: 10.5px;
    text-transform: uppercase;
    letter-spacing: .08em;
    font-weight: 700;
}

.rv-table td {
    font-size: 13px;
}

.rv-table td strong {
    font-weight: 600;
    color: var(--rv-ink);
}

.rv-page .rv-table > :not(caption) > * > * {
    background-color: transparent !important;
    box-shadow: none !important;
    color: var(--rv-ink) !important;
}

.rv-page .rv-table > thead > tr > th {
    background-color: var(--rv-wash) !important;
    color: var(--rv-ink3) !important;
}

.rv-table tbody tr {
    transition: background .12s;
}

.rv-page .rv-table > tbody > tr:hover > td {
    background-color: var(--rv-wash) !important;
}

.rv-table tbody tr:last-child td {
    border-bottom: 0;
}

.rv-table .text-muted {
    font-size: 11.5px;
}

/* ============================================================
   STATUS CHIPS
   Chips are labels, not buttons, so they never change on hover.
   Border colors come from theme tokens, so they stay correct in
   both light and dark mode.
   ============================================================ */

.rv-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;

    padding: 4px 10px;

    border-radius: 999px;
    border: 1px solid transparent;

    font-size: 11.5px;
    font-weight: 600;
    line-height: 1.2;
    white-space: nowrap;
    cursor: default;
}

.rv-chip::before {
    content: "";
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
}

.rv-chip .bi {
    font-size: 12px;
}

.rv-chip:has(.bi)::before {
    display: none;
}

/* DRAFT / PENDING — neutral */
.rv-page .rv-chip.is-grey,
.rv-page .rv-chip.is-grey:hover {
    color: var(--rv-grey);
    background: var(--rv-grey-soft);
    border-color: var(--rv-grey-line);
}

/* READY / GENERATED / ISSUED — informational */
.rv-page .rv-chip.is-blue,
.rv-page .rv-chip.is-blue:hover {
    color: var(--rv-blue-ink);
    background: var(--rv-blue-soft);
    border-color: var(--rv-blue-line);
}

/* RELIEF ASSISTANCE module label */
.rv-page .rv-chip.is-ube,
.rv-page .rv-chip.is-ube:hover {
    color: var(--rv-ube);
    background: var(--rv-ube-soft);
    border-color: var(--rv-ube-line);
}

/* FOR DISTRIBUTION / DISTRIBUTED — in progress */
.rv-page .rv-chip.is-mango,
.rv-page .rv-chip.is-mango:hover {
    color: var(--rv-mango-ink);
    background: var(--rv-mango-soft);
    border-color: var(--rv-mango-line);
}

/* COMPLETED / CLAIMED — success */
.rv-page .rv-chip.is-green,
.rv-page .rv-chip.is-green:hover {
    color: var(--rv-green);
    background: var(--rv-green-soft);
    border-color: var(--rv-green-line);
}

/* CANCELLED — danger */
.rv-page .rv-chip.is-red,
.rv-page .rv-chip.is-red:hover {
    color: var(--rv-red);
    background: var(--rv-red-soft);
    border-color: var(--rv-red-line);
}

/* EMPTY STATE */

.rvi-empty {
    padding: 36px 22px;
    text-align: center;

    border-radius: var(--rv-radius);
    background: var(--rv-card);
    border: 1px dashed var(--rv-ghost-border);
}

.rvi-empty-icon {
    width: 52px;
    height: 52px;

    display: grid;
    place-items: center;

    margin: 0 auto 12px;

    border-radius: 50%;
    background: var(--rv-wash);
    color: var(--rv-accent);
    font-size: 1.3rem;
}

.rvi-empty h3 {
    font-size: 1rem;
    font-weight: 700;
}

.rvi-empty p {
    max-width: 520px;
    margin: 6px auto 0;
    font-size: 13px;
    line-height: 1.6;
    color: var(--rv-ink2);
}

/* RESPONSIVE */

@media (max-width: 1100px) {
    .rvi-tiles {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .rvi-tile:nth-child(2) {
        border-right: 0;
    }

    .rvi-tile:nth-child(-n+2) {
        border-bottom: 1px solid var(--rv-line);
    }

    .rvi-steps {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .rvi-step {
        border-bottom: 1px solid var(--rv-line);
    }
}

@media (max-width: 899.98px) {
    .rvi-hero {
        grid-template-columns: 1fr;
        padding: 20px;
    }
}

@media (max-width: 800px) {
    .rvi-steps {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

@media (max-width: 576px) {
    .rv-page {
        padding: 10px !important;
        border-radius: 12px;
    }

    .rvi-tiles,
    .rvi-steps {
        grid-template-columns: 1fr;
    }

    .rvi-tile {
        border-right: 0;
        border-bottom: 1px solid var(--rv-line);
    }

    .rvi-tile:last-child {
        border-bottom: 0;
    }

    .rvi-next .rv-btn {
        width: 100%;
    }

    .rv-search {
        max-width: none;
    }

    .rvi-step {
        border-right: 0;
    }
}

@media (prefers-reduced-motion: reduce) {
    .rv-page *,
    .rv-page *::before,
    .rv-page *::after {
        transition: none !important;
        animation: none !important;
    }
}
</style>

<div class="container-fluid rv-page" data-rv-themed>

    {{-- THEME ENGINE --}}
    <script>
    (function () {
        var KEY = 'rv-theme';

        var ICON_CLASSES = [
            'bi-sun',
            'bi-sun-fill',
            'bi-moon',
            'bi-moon-fill',
            'bi-moon-stars',
            'bi-moon-stars-fill',
            'bi-brightness-high',
            'bi-brightness-high-fill',
            'bi-brightness-low'
        ];

        var ICON_SELECTOR = ICON_CLASSES.map(function (name) {
            return '.' + name;
        }).join(', ');

        function explicitSiteTheme() {
            var root = document.documentElement;
            var body = document.body;

            var values = [
                root ? root.getAttribute('data-bs-theme') : null,
                root ? root.getAttribute('data-theme') : null,
                body ? body.getAttribute('data-bs-theme') : null,
                body ? body.getAttribute('data-theme') : null
            ];

            for (var i = 0; i < values.length; i++) {
                var value = String(values[i] || '').toLowerCase().trim();

                if (value === 'light') return 'light';
                if (value === 'dark') return 'dark';
            }

            var elements = [root, body];

            for (var j = 0; j < elements.length; j++) {
                var el = elements[j];

                if (!el || !el.classList) continue;

                if (
                    el.classList.contains('theme-light') ||
                    el.classList.contains('light-mode')
                ) {
                    return 'light';
                }

                if (
                    el.classList.contains('theme-dark') ||
                    el.classList.contains('dark-mode')
                ) {
                    return 'dark';
                }
            }

            return null;
        }

        function savedTheme() {
            try {
                var value = localStorage.getItem(KEY);

                return value === 'light' || value === 'dark'
                    ? value
                    : null;
            } catch (error) {
                return null;
            }
        }

        var systemQuery = window.matchMedia
            ? window.matchMedia('(prefers-color-scheme: dark)')
            : null;

        function systemTheme() {
            return systemQuery && systemQuery.matches ? 'dark' : 'light';
        }

        function currentTheme() {
            return explicitSiteTheme() || savedTheme() || systemTheme();
        }

        function isThemeToggle(element) {
            if (!element) return false;

            return element.hasAttribute('data-rv-theme-toggle') ||
                (
                    !!element.closest('[data-rv-themed]') &&
                    !!element.querySelector(ICON_SELECTOR)
                );
        }

        function applyTheme(theme) {
            var selected = theme || currentTheme();

            document.querySelectorAll('[data-rv-themed]').forEach(function (element) {
                element.setAttribute('data-rv-theme', selected);
            });

            document.querySelectorAll(
                '[data-rv-theme-toggle], [data-rv-themed] button, [data-rv-themed] a'
            ).forEach(function (element) {
                if (!isThemeToggle(element)) return;

                var icon = element.querySelector(ICON_SELECTOR);

                if (icon) {
                    ICON_CLASSES.forEach(function (className) {
                        icon.classList.remove(className);
                    });

                    icon.classList.add(
                        selected === 'dark' ? 'bi-moon-stars' : 'bi-sun'
                    );
                }

                var label = selected === 'dark'
                    ? 'Switch to light mode'
                    : 'Switch to dark mode';

                element.setAttribute('aria-label', label);
                element.setAttribute('title', label);
                element.setAttribute(
                    'aria-pressed',
                    selected === 'dark' ? 'true' : 'false'
                );
            });
        }

        function setTheme(theme) {
            if (theme !== 'light' && theme !== 'dark') return;

            try {
                localStorage.setItem(KEY, theme);
            } catch (error) {}

            applyTheme(theme);
        }

        document.addEventListener('click', function (event) {
            var element = event.target.closest
                ? event.target.closest('button, a')
                : null;

            if (!element || !isThemeToggle(element)) return;

            event.preventDefault();

            setTheme(
                currentTheme() === 'dark' ? 'light' : 'dark'
            );
        });

        if (systemQuery) {
            var onSystemChange = function () {
                applyTheme(currentTheme());
            };

            if (systemQuery.addEventListener) {
                systemQuery.addEventListener('change', onSystemChange);
            } else if (systemQuery.addListener) {
                systemQuery.addListener(onSystemChange);
            }
        }

        if (window.MutationObserver) {
            var themeObserver = new MutationObserver(function () {
                applyTheme(currentTheme());
            });

            [document.documentElement, document.body].forEach(function (node) {
                if (node) {
                    themeObserver.observe(node, {
                        attributes: true,
                        attributeFilter: ['data-theme', 'data-bs-theme', 'class']
                    });
                }
            });
        }

        window.addEventListener('storage', function (event) {
            if (event.key === KEY) {
                applyTheme(currentTheme());
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            applyTheme(currentTheme());
        });

        window.__rvTheme = {
            apply: function () {
                applyTheme(currentTheme());
            },
            set: setTheme,
            current: currentTheme
        };

        applyTheme(currentTheme());
    })();
    </script>

    {{-- RELIEF NAVIGATION --}}
    @include('relief-vouchers.partials.nav', ['active' => 'overview'])

    {{-- HERO --}}
    <div class="rvi-hero">

        <div class="rvi-hero-main">
            <div class="d-flex align-items-center flex-wrap gap-2">
                <span class="rv-chip is-ube">
                    <i class="bi bi-box-seam"></i>
                    Relief Assistance
                </span>

                @if($hasOperations)
                    <span class="rv-chip is-grey">
                        {{ $totalOperations }}
                        {{ \Illuminate\Support\Str::plural('operation', $totalOperations) }}
                    </span>
                @endif
            </div>

            <h1>Relief Operations &amp; Vouchers</h1>

            <p class="lead">
                One place to manage relief operations, beneficiaries and voucher distribution.
            </p>
        </div>

        <div class="rvi-next">
            <div class="rvi-next-tag">
                {{ $showNextAction ? 'Next step' : 'Overview' }}
            </div>

            <div class="rvi-next-left">
                <div class="rvi-next-icon">
                    <i class="bi {{ $next[3] }}"></i>
                </div>

                <div>
                    <h3>{{ $next[0] }}</h3>
                    <span>{{ $next[1] }}</span>
                </div>
            </div>

            @if($showNextAction && !empty($next[2]))
                <a href="{{ $next[2] }}" class="rv-btn">
                    Continue
                    <i class="bi bi-arrow-right"></i>
                </a>
            @else
                <div class="rvi-next-note">
                    <i class="bi bi-info-circle me-1"></i>
                    Use the <strong>+ New operation</strong> button in the navigation above when you're ready to begin another operation.
                </div>
            @endif
        </div>

    </div>

    {{-- SUMMARY --}}
    <div class="rvi-tiles">

        <div class="rvi-tile">
            <div class="rvi-tile-ic"><i class="bi bi-box-seam"></i></div>
            <div>
                <small>Operations</small>
                <strong>{{ number_format($totalOperations) }}</strong>
            </div>
        </div>

        <div class="rvi-tile">
            <div class="rvi-tile-ic"><i class="bi bi-lightning-charge"></i></div>
            <div>
                <small>Active</small>
                <strong>{{ number_format($activeOperations) }}</strong>
            </div>
        </div>

        <div class="rvi-tile">
            <div class="rvi-tile-ic"><i class="bi bi-check2-circle"></i></div>
            <div>
                <small>Completed</small>
                <strong>{{ number_format($completedOperations) }}</strong>
            </div>
        </div>

        <div class="rvi-tile">
            <div class="rvi-tile-ic"><i class="bi bi-ticket-perforated"></i></div>
            <div>
                <small>Total Vouchers</small>
                <strong>{{ number_format($totalVouchers) }}</strong>
            </div>
        </div>

    </div>

    {{-- VOUCHER PROGRESS --}}
    <div class="rvi-bar">

        <div class="rvi-bar-top">
            <span>Voucher distribution progress</span>
            <strong>{{ $claimPercent }}% claimed</strong>
        </div>

        <div class="rvi-bar-track">

            @if($issuedVouchers > 0)
                <div class="rvi-bar-segment rvi-bar-issued"
                     style="width: {{ $seg($issuedVouchers) }}%;"
                     title="Issued: {{ number_format($issuedVouchers) }}"></div>
            @endif

            @if($claimedVouchers > 0)
                <div class="rvi-bar-segment rvi-bar-claimed"
                     style="width: {{ $seg($claimedVouchers) }}%;"
                     title="Claimed: {{ number_format($claimedVouchers) }}"></div>
            @endif

            @if($cancelledVouchers > 0)
                <div class="rvi-bar-segment rvi-bar-cancelled"
                     style="width: {{ $seg($cancelledVouchers) }}%;"
                     title="Cancelled: {{ number_format($cancelledVouchers) }}"></div>
            @endif

            @if($waiting > 0)
                <div class="rvi-bar-segment rvi-bar-waiting"
                     style="width: {{ $seg($waiting) }}%;"
                     title="Waiting: {{ number_format($waiting) }}"></div>
            @endif

        </div>

        <div class="rvi-legend">
            <span class="rvi-legend-item">
                <span class="rvi-dot" style="background: var(--rv-blue);"></span>
                Issued: {{ number_format($issuedVouchers) }}
            </span>

            <span class="rvi-legend-item">
                <span class="rvi-dot" style="background: var(--rv-green);"></span>
                Claimed: {{ number_format($claimedVouchers) }}
            </span>

            <span class="rvi-legend-item">
                <span class="rvi-dot" style="background: var(--rv-mango);"></span>
                Waiting: {{ number_format($waiting) }}
            </span>

            <span class="rvi-legend-item">
                <span class="rvi-dot" style="background: var(--rv-red);"></span>
                Cancelled: {{ number_format($cancelledVouchers) }}
            </span>
        </div>

    </div>

    {{-- WORKFLOW --}}
    <div class="rvi-steps">

        @foreach($workflow as $i => $step)
            @php
                $n = $i + 1;
                $stepClass = ($nowStep > $n ? 'is-done ' : '')
                           . ($nowStep === $n ? 'is-now' : '');
            @endphp

            <div class="rvi-step {{ trim($stepClass) }}">
                <div class="rvi-step-number">
                    @if($nowStep > $n)
                        <i class="bi bi-check-lg"></i>
                    @else
                        {{ $n }}
                    @endif
                </div>

                <h3>{{ $step[0] }}</h3>
                <p>{{ $step[1] }}</p>
            </div>
        @endforeach

    </div>

    {{-- RELIEF OPERATIONS --}}
    <div class="rv-section-head">
        <div>
            <h2>Relief Operations</h2>
            <p>Create and monitor relief activities.</p>
        </div>
    </div>

    @if($hasOperations)

        <div class="rvi-tools">
            <div class="rv-search">
                <i class="bi bi-search"></i>
                <input
                    type="search"
                    id="operationSearch"
                    class="form-control"
                    placeholder="Search relief operations..."
                    autocomplete="off"
                >
            </div>
        </div>

        <div class="rv-panel">
            <div class="rv-table-wrap">
                <table class="rv-table" id="operationsTable">

                    <thead>
                        <tr>
                            <th>Operation</th>
                            <th>Assistance</th>
                            <th>Schedule</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($operations as $operation)

                            @php
                                $operationStatus = strtolower((string) ($operation->status ?? ''));
                                $statusLabel = $statusLabels[$operationStatus] ?? ucfirst($operationStatus ?: 'Unknown');
                                $statusTone = $statusTones[$operationStatus] ?? 'is-grey';
                            @endphp

                            <tr data-operation-row>

                                <td>
                                    <strong>{{ $operation->operation_name ?? 'Unnamed Operation' }}</strong>

                                    @if(!empty($operation->operation_code))
                                        <div class="small text-muted">{{ $operation->operation_code }}</div>
                                    @endif
                                </td>

                                <td>{{ $operation->assistance_type ?? '—' }}</td>

                                <td>
                                    {{ $operation->distribution_date
                                        ? \Illuminate\Support\Carbon::parse($operation->distribution_date)->format('M d, Y')
                                        : '—'
                                    }}
                                </td>

                                <td>{{ $operation->location ?? '—' }}</td>

                                <td>
                                    <span class="rv-chip {{ $statusTone }}">{{ $statusLabel }}</span>
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('relief-assistance.show', ['operation' => $operation->id]) }}"
                                        class="rv-btn rv-btn--sm rv-btn--ghost"
                                    >
                                        View
                                    </a>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>
            </div>
        </div>

    @else

        <div class="rvi-empty">
            <div class="rvi-empty-icon">
                <i class="bi bi-box-seam"></i>
            </div>

            <h3>No relief operations yet</h3>

            <p>
                Use the <strong>+ New operation</strong> button in the navigation above to create your first operation.
                Your operations will appear here once created.
            </p>
        </div>

    @endif

    {{-- RECENT VOUCHERS --}}
    <div class="rv-section-head">
        <div>
            <h2>Recent Vouchers</h2>
            <p>Recently generated and distributed relief vouchers.</p>
        </div>
    </div>

    @if($recentVouchers->count())

        <div class="rvi-tools">
            <div class="rv-search">
                <i class="bi bi-search"></i>
                <input
                    type="search"
                    id="voucherSearch"
                    class="form-control"
                    placeholder="Search voucher, household or purok..."
                    autocomplete="off"
                >
            </div>
        </div>

        <div class="rv-panel">
            <div class="rv-table-wrap">
                <table class="rv-table" id="vouchersTable">

                    <thead>
                        <tr>
                            <th>Voucher</th>
                            <th>Household</th>
                            <th>Purok</th>
                            <th>Assistance</th>
                            <th>Schedule</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($recentVouchers as $voucher)

                            @php
                                $voucherStatus = strtolower((string) ($voucher->status ?? ''));
                                $voucherTone = $voucherTones[$voucherStatus] ?? 'is-grey';
                            @endphp

                            <tr data-voucher-row>

                                <td>
                                    <strong>{{ $voucher->voucher_code ?? '—' }}</strong>
                                </td>

                                <td>
                                    @if(isset($voucher->household))
                                        {{ $voucher->household->household_number
                                            ?? $voucher->household->id
                                            ?? '—'
                                        }}
                                    @else
                                        {{ $voucher->household_id ?? '—' }}
                                    @endif
                                </td>

                                <td>{{ $voucher->purok_name ?? '—' }}</td>

                                <td>{{ $voucher->assistance_type ?? '—' }}</td>

                                <td>
                                    {{ $voucher->distribution_schedule
                                        ? \Illuminate\Support\Carbon::parse($voucher->distribution_schedule)->format('M d, Y')
                                        : '—'
                                    }}
                                </td>

                                <td>
                                    <span class="rv-chip {{ $voucherTone }}">
                                        {{ ucfirst($voucherStatus ?: 'Unknown') }}
                                    </span>
                                </td>

                                <td class="text-end">
                                    <a
                                        href="{{ route('relief-vouchers.show', ['reliefVoucher' => $voucher->id]) }}"
                                        class="rv-btn rv-btn--sm rv-btn--ghost"
                                    >
                                        View
                                    </a>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>
            </div>
        </div>

    @else

        <div class="rvi-empty">
            <div class="rvi-empty-icon">
                <i class="bi bi-ticket-perforated"></i>
            </div>

            <h3>No vouchers yet</h3>

            <p>
                Vouchers will appear here after beneficiaries have been
                verified and vouchers are generated for a relief operation.
            </p>
        </div>

    @endif

</div>

{{-- SEARCH SCRIPT --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    function bindSearch(inputId, rowSelector) {
        const input = document.getElementById(inputId);

        if (!input) return;

        input.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();

            document.querySelectorAll(rowSelector).forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().includes(query)
                    ? ''
                    : 'none';
            });
        });
    }

    bindSearch('operationSearch', '[data-operation-row]');
    bindSearch('voucherSearch', '[data-voucher-row]');

});
</script>

@endsection
