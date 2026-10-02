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
        $totalVouchers
        - $claimedVouchers
        - $issuedVouchers
        - $cancelledVouchers
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
        'ready' => 'is-ube',
        'distribution' => 'is-mango',
        'completed' => 'is-green',
        'closed' => 'is-green',
        'cancelled' => 'is-red',
    ];

    $statusGroups = [
        'draft' => 'active',
        'ready' => 'active',
        'distribution' => 'active',
        'completed' => 'completed',
        'closed' => 'completed',
        'cancelled' => 'cancelled',
    ];

    $voucherTones = [
        'pending' => 'is-grey',
        'generated' => 'is-ube',
        'issued' => 'is-ube',
        'distributed' => 'is-mango',
        'claimed' => 'is-green',
        'cancelled' => 'is-red',
    ];

    if (!$hasOperations) {
        $next = [
            'Create your first operation',
            'Set the assistance type, stock, schedule and location.',
            route('relief-assistance.create'),
            'bi-plus-circle',
            1
        ];
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
            'Generate and print vouchers for ' . ($latestOperation->operation_name ?? 'the latest operation') . '.',
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
            'Start a new operation',
            'The latest operation is finished. Prepare the next distribution.',
            route('relief-assistance.create'),
            'bi-plus-circle',
            1
        ];
    }

    $nowStep = $next[4];
@endphp

<style>
    /* ============================================================
       RELIEF VOUCHERS
       LIGHT / DARK MODE BALANCE
       ============================================================ */

    .rv-page {
        /* Light mode */
        --rv-ink: #172033;
        --rv-ink2: #5b6472;
        --rv-card: #ffffff;
        --rv-wash: #f7f8fb;
        --rv-line: #e2e6ec;

        --rv-grey: #6b7280;
        --rv-grey-soft: #eef1f4;

        --rv-ube: #6841d9;
        --rv-ube-soft: #f0ebff;

        --rv-mango: #f6c453;
        --rv-mango-soft: #fff7df;
        --rv-mango-ink: #8a5b00;

        --rv-green: #198754;
        --rv-green-soft: #e8f6ef;

        --rv-red: #dc3545;

        --rv-btn: #5632c7;

        color: var(--rv-ink);
    }

    /*
     * Dark-mode support.
     *
     * The first selector matches the theme system already commonly
     * used by the application. The additional selectors make this
     * page safer if the theme class is applied to html/body instead.
     */
    [data-theme="dark"] .rv-page,
    html.dark .rv-page,
    body.dark .rv-page,
    .dark .rv-page {
        --rv-ink: #f1f4f8;
        --rv-ink2: #b7c0cc;
        --rv-card: #1b2029;
        --rv-wash: #151a22;
        --rv-line: #343b47;

        --rv-grey: #a8b0bc;
        --rv-grey-soft: #303743;

        --rv-ube: #a992ff;
        --rv-ube-soft: #292341;

        --rv-mango: #f6c453;
        --rv-mango-soft: #3a301b;
        --rv-mango-ink: #ffd875;

        --rv-green: #55c98a;
        --rv-green-soft: #1d382b;

        --rv-red: #ff7180;

        --rv-btn: #7654e8;

        color: var(--rv-ink);
    }

    /* ============================================================
       GENERAL TEXT
       ============================================================ */

    .rv-page h1,
    .rv-page h2,
    .rv-page h3,
    .rv-page h4,
    .rv-page h5,
    .rv-page h6 {
        color: var(--rv-ink);
    }

    .rv-page p,
    .rv-page label,
    .rv-page td,
    .rv-page th {
        color: var(--rv-ink);
    }

    .rv-page .text-muted,
    .rv-page .rv-muted,
    .rv-page small,
    .rv-page .lead {
        color: var(--rv-ink2) !important;
    }

    /* ============================================================
       PAGE / CARD SURFACES
       ============================================================ */

    .rv-page .rv-panel,
    .rv-page .rv-card,
    .rv-page .rvi-step,
    .rv-page .rvi-next,
    .rv-page .rvi-tile,
    .rv-page .rvi-bar,
    .rv-page .rvi-note,
    .rv-page .rvi-empty,
    .rv-page .rvi-help {
        background-color: var(--rv-card);
        border-color: var(--rv-line);
        color: var(--rv-ink);
    }

    /* ============================================================
       HERO
       ============================================================ */

    .rv-page .rv-head h1,
    .rv-page .rvi-hero h1,
    .rv-page .rvi-hero h2,
    .rv-page .rvi-hero h3 {
        color: var(--rv-ink);
    }

    .rv-page .rv-head p,
    .rv-page .rvi-hero p {
        color: var(--rv-ink2);
    }

    /* ============================================================
       NEXT STEP
       ============================================================ */

    .rv-page .rvi-next h3,
    .rv-page .rvi-next strong {
        color: var(--rv-ink);
    }

    .rv-page .rvi-next span,
    .rv-page .rvi-next p {
        color: var(--rv-ink2);
    }

    /* ============================================================
       SUMMARY TILES
       ============================================================ */

    .rv-page .rvi-tile strong,
    .rv-page .rvi-tile h3 {
        color: var(--rv-ink);
    }

    .rv-page .rvi-tile small {
        color: var(--rv-ink2);
    }

    /* ============================================================
       SEGMENTED BAR
       ============================================================ */

    .rv-page .rvi-bar-top,
    .rv-page .rvi-legend,
    .rv-page .rvi-count {
        color: var(--rv-ink2);
    }

    .rv-page .rvi-bar-top strong {
        color: var(--rv-ink);
    }

    /* ============================================================
       WORKFLOW STEPS
       ============================================================ */

    .rv-page .rvi-step h3,
    .rv-page .rvi-step strong {
        color: var(--rv-ink);
    }

    .rv-page .rvi-step p {
        color: var(--rv-ink2);
    }

    .rv-page .rvi-step.is-active {
        background-color: var(--rv-ube-soft);
        border-color: var(--rv-ube);
    }

    /* ============================================================
       TABLES
       ============================================================ */

    .rv-page .rv-table {
        background-color: var(--rv-card);
        color: var(--rv-ink);
    }

    .rv-page .rv-table thead th {
        color: var(--rv-ink2);
        background-color: var(--rv-wash);
        border-color: var(--rv-line);
    }

    .rv-page .rv-table tbody td {
        color: var(--rv-ink);
        border-color: var(--rv-line);
    }

    .rv-page .rv-table tbody tr {
        background-color: var(--rv-card);
    }

    .rv-page .rv-table tbody tr:hover {
        background-color: var(--rv-wash);
    }

    .rv-page .rv-table .text-muted {
        color: var(--rv-ink2) !important;
    }

    /* ============================================================
       SEARCH / FILTER
       ============================================================ */

    .rv-page .rv-search input,
    .rv-page .rv-search select,
    .rv-page .form-control,
    .rv-page .form-select {
        color: var(--rv-ink);
        background-color: var(--rv-card);
        border-color: var(--rv-line);
    }

    .rv-page .rv-search input::placeholder,
    .rv-page .form-control::placeholder {
        color: var(--rv-ink2);
        opacity: .8;
    }

    .rv-page .rv-search input:focus,
    .rv-page .rv-search select:focus,
    .rv-page .form-control:focus,
    .rv-page .form-select:focus {
        color: var(--rv-ink);
        background-color: var(--rv-card);
        border-color: var(--rv-ube);
        box-shadow: 0 0 0 .2rem rgba(104, 65, 217, .15);
    }

    [data-theme="dark"] .rv-page .form-control,
    [data-theme="dark"] .rv-page .form-select,
    html.dark .rv-page .form-control,
    html.dark .rv-page .form-select,
    body.dark .rv-page .form-control,
    body.dark .rv-page .form-select,
    .dark .rv-page .form-control,
    .dark .rv-page .form-select {
        color-scheme: dark;
    }

    /* ============================================================
       LINKS
       ============================================================ */

    .rv-page a {
        color: var(--rv-ube);
    }

    .rv-page a:hover {
        color: var(--rv-btn);
    }

    .rv-page .rv-link {
        color: var(--rv-ube);
    }

    /* ============================================================
       STATUS CHIPS
       ============================================================ */

    .rv-page .rv-chip {
        border-color: var(--rv-line);
    }

    .rv-page .rv-chip.is-grey {
        color: var(--rv-grey);
        background-color: var(--rv-grey-soft);
    }

    .rv-page .rv-chip.is-ube {
        color: var(--rv-ube);
        background-color: var(--rv-ube-soft);
    }

    .rv-page .rv-chip.is-mango {
        color: var(--rv-mango-ink);
        background-color: var(--rv-mango-soft);
    }

    .rv-page .rv-chip.is-green {
        color: var(--rv-green);
        background-color: var(--rv-green-soft);
    }

    .rv-page .rv-chip.is-red {
        color: var(--rv-red);
        background-color: rgba(220, 53, 69, .10);
    }

    /* ============================================================
       BADGES
       ============================================================ */

    .rv-page .rv-badge {
        color: var(--rv-ink);
        border-color: var(--rv-line);
    }

    [data-theme="dark"] .rv-page .rv-badge,
    html.dark .rv-page .rv-badge,
    body.dark .rv-page .rv-badge,
    .dark .rv-page .rv-badge {
        background-color: var(--rv-grey-soft);
    }

    /* ============================================================
       NOTE / INFORMATION BOX
       ============================================================ */

    .rv-page .rvi-note {
        color: var(--rv-ink);
        background-color: var(--rv-mango-soft);
        border-color: var(--rv-mango);
    }

    .rv-page .rvi-note strong {
        color: var(--rv-ink);
    }

    .rv-page .rvi-note span,
    .rv-page .rvi-note p {
        color: var(--rv-ink2);
    }

    /* ============================================================
       EMPTY STATE
       ============================================================ */

    .rv-page .rvi-empty h3,
    .rv-page .rvi-empty strong {
        color: var(--rv-ink);
    }

    .rv-page .rvi-empty p,
    .rv-page .rvi-empty span {
        color: var(--rv-ink2);
    }

    /* ============================================================
       HELP
       ============================================================ */

    .rv-page .rvi-help {
        color: var(--rv-ink);
    }

    .rv-page .rvi-help summary {
        color: var(--rv-ink);
    }

    .rv-page .rvi-help-item {
        color: var(--rv-ink2);
    }

    .rv-page .rvi-help-item strong {
        color: var(--rv-ink);
    }

    /* ============================================================
       BUTTONS
       ============================================================ */

    .rv-page .btn-primary {
        color: #ffffff;
        background-color: var(--rv-btn);
        border-color: var(--rv-btn);
    }

    .rv-page .btn-outline-primary {
        color: var(--rv-ube);
        border-color: var(--rv-ube);
    }

    .rv-page .btn-outline-primary:hover {
        color: #ffffff;
        background-color: var(--rv-btn);
        border-color: var(--rv-btn);
    }

    .rv-page .btn-outline-secondary {
        color: var(--rv-ink2);
        border-color: var(--rv-line);
    }

    .rv-page .btn-outline-secondary:hover {
        color: var(--rv-ink);
        background-color: var(--rv-wash);
        border-color: var(--rv-line);
    }

    /* ============================================================
       COPY BUTTON
       ============================================================ */

    .rv-page .rv-copy {
        color: var(--rv-ink2);
        background-color: var(--rv-wash);
        border-color: var(--rv-line);
    }

    .rv-page .rv-copy:hover {
        color: var(--rv-ink);
        background-color: var(--rv-grey-soft);
    }

    /* ============================================================
       ICONS
       ============================================================ */

    .rv-page .bi {
        color: inherit;
    }

    /* ============================================================
       DARK MODE TABLE / BOOTSTRAP OVERRIDES
       ============================================================ */

    [data-theme="dark"] .rv-page .table,
    [data-theme="dark"] .rv-page .table > :not(caption) > * > *,
    html.dark .rv-page .table,
    html.dark .rv-page .table > :not(caption) > * > *,
    body.dark .rv-page .table,
    body.dark .rv-page .table > :not(caption) > * > *,
    .dark .rv-page .table,
    .dark .rv-page .table > :not(caption) > * > * {
        color: var(--rv-ink);
        border-color: var(--rv-line);
    }

    [data-theme="dark"] .rv-page .table-light,
    html.dark .rv-page .table-light,
    body.dark .rv-page .table-light,
    .dark .rv-page .table-light {
        --bs-table-bg: var(--rv-wash);
        --bs-table-color: var(--rv-ink);
    }

    /* ============================================================
       DARK MODE DROPDOWNS
       ============================================================ */

    [data-theme="dark"] .rv-page .dropdown-menu,
    html.dark .rv-page .dropdown-menu,
    body.dark .rv-page .dropdown-menu,
    .dark .rv-page .dropdown-menu {
        background-color: var(--rv-card);
        border-color: var(--rv-line);
    }

    [data-theme="dark"] .rv-page .dropdown-item,
    html.dark .rv-page .dropdown-item,
    body.dark .rv-page .dropdown-item,
    .dark .rv-page .dropdown-item {
        color: var(--rv-ink);
    }

    [data-theme="dark"] .rv-page .dropdown-item:hover,
    html.dark .rv-page .dropdown-item:hover,
    body.dark .rv-page .dropdown-item:hover,
    .dark .rv-page .dropdown-item:hover {
        color: var(--rv-ink);
        background-color: var(--rv-wash);
    }

    /* ============================================================
       DARK MODE MODAL
       ============================================================ */

    [data-theme="dark"] .rv-page .modal-content,
    html.dark .rv-page .modal-content,
    body.dark .rv-page .modal-content,
    .dark .rv-page .modal-content {
        color: var(--rv-ink);
        background-color: var(--rv-card);
        border-color: var(--rv-line);
    }

    [data-theme="dark"] .rv-page .modal-header,
    [data-theme="dark"] .rv-page .modal-footer,
    html.dark .rv-page .modal-header,
    html.dark .rv-page .modal-footer,
    body.dark .rv-page .modal-header,
    body.dark .rv-page .modal-footer,
    .dark .rv-page .modal-header,
    .dark .rv-page .modal-footer {
        border-color: var(--rv-line);
    }

    /* ============================================================
       EXISTING PAGE LAYOUT
       ============================================================ */

    .rv-page {
        min-width: 0;
    }

    .rv-page *,
    .rv-page *::before,
    .rv-page *::after {
        box-sizing: border-box;
    }

    /* ------------------------------------------------------------
       HERO
       ------------------------------------------------------------ */

    .rvi-hero {
        position: relative;
        padding: 1.5rem;
        border: 1px solid var(--rv-line);
        border-radius: 18px;
        background: var(--rv-card);
        overflow: hidden;
    }

    .rvi-hero::after {
        content: "";
        position: absolute;
        width: 180px;
        height: 180px;
        right: -70px;
        top: -80px;
        border-radius: 50%;
        background: var(--rv-ube-soft);
        pointer-events: none;
    }

    .rvi-hero h1 {
        margin: 0;
        font-size: clamp(1.55rem, 2.3vw, 2.15rem);
        font-weight: 800;
        letter-spacing: -.03em;
    }

    .rvi-hero .lead {
        margin: .45rem 0 0;
        max-width: 760px;
        line-height: 1.55;
    }

    /* ------------------------------------------------------------
       NEXT STEP
       ------------------------------------------------------------ */

    .rvi-next {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.15rem;
        margin-top: 1rem;
        border: 1px solid var(--rv-line);
        border-radius: 16px;
    }

    .rvi-next-left {
        display: flex;
        align-items: center;
        gap: .9rem;
        min-width: 0;
    }

    .rvi-next-icon {
        width: 44px;
        height: 44px;
        flex: 0 0 44px;
        display: grid;
        place-items: center;
        border-radius: 12px;
        background: var(--rv-ube-soft);
        color: var(--rv-ube);
        font-size: 1.15rem;
    }

    .rvi-next h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
    }

    .rvi-next span {
        display: block;
        margin-top: .15rem;
        font-size: .86rem;
        line-height: 1.4;
    }

    /* ------------------------------------------------------------
       SUMMARY TILES
       ------------------------------------------------------------ */

    .rvi-tiles {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .85rem;
        margin-top: 1rem;
    }

    .rvi-tile {
        min-width: 0;
        padding: 1rem;
        border: 1px solid var(--rv-line);
        border-radius: 15px;
    }

    .rvi-tile small {
        display: block;
        font-size: .76rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .rvi-tile strong {
        display: block;
        margin-top: .2rem;
        font-size: 1.65rem;
        line-height: 1;
    }

    /* ------------------------------------------------------------
       BAR
       ------------------------------------------------------------ */

    .rvi-bar {
        margin-top: 1rem;
        padding: 1rem;
        border: 1px solid var(--rv-line);
        border-radius: 15px;
    }

    .rvi-bar-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-bottom: .65rem;
        font-size: .84rem;
    }

    .rvi-bar-track {
        width: 100%;
        height: 12px;
        overflow: hidden;
        display: flex;
        border-radius: 999px;
        background: var(--rv-grey-soft);
    }

    .rvi-bar-segment {
        height: 100%;
        min-width: 0;
    }

    .rvi-bar-issued {
        background: var(--rv-ube);
    }

    .rvi-bar-claimed {
        background: var(--rv-green);
    }

    .rvi-bar-cancelled {
        background: var(--rv-red);
    }

    .rvi-bar-waiting {
        background: var(--rv-mango);
    }

    .rvi-legend {
        display: flex;
        flex-wrap: wrap;
        gap: .7rem 1rem;
        margin-top: .65rem;
        font-size: .78rem;
    }

    .rvi-legend-item {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
    }

    .rvi-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }

    /* ------------------------------------------------------------
       WORKFLOW
       ------------------------------------------------------------ */

    .rvi-steps {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: .7rem;
        margin-top: 1rem;
    }

    .rvi-step {
        position: relative;
        padding: .95rem;
        min-height: 130px;
        border: 1px solid var(--rv-line);
        border-radius: 15px;
    }

    .rvi-step-number {
        width: 30px;
        height: 30px;
        display: grid;
        place-items: center;
        margin-bottom: .7rem;
        border-radius: 50%;
        background: var(--rv-grey-soft);
        color: var(--rv-ink);
        font-size: .78rem;
        font-weight: 800;
    }

    .rvi-step.is-active .rvi-step-number {
        background: var(--rv-ube);
        color: #fff;
    }

    .rvi-step h3 {
        margin: 0;
        font-size: .92rem;
        font-weight: 800;
    }

    .rvi-step p {
        margin: .3rem 0 0;
        font-size: .77rem;
        line-height: 1.45;
    }

    /* ------------------------------------------------------------
       SECTION BAR
       ------------------------------------------------------------ */

    .rv-section-head {
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 1rem;
        margin-top: 1.6rem;
        margin-bottom: .7rem;
    }

    .rv-section-head h2 {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 800;
    }

    .rv-section-head p {
        margin: .2rem 0 0;
        font-size: .82rem;
    }

    /* ------------------------------------------------------------
       TOOLS
       ------------------------------------------------------------ */

    .rvi-tools {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: .75rem;
    }

    .rv-search {
        position: relative;
        flex: 1 1 auto;
        min-width: 0;
    }

    .rv-search .bi {
        position: absolute;
        left: .8rem;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
        color: var(--rv-ink2);
    }

    .rv-search input {
        width: 100%;
        min-height: 40px;
        padding: .5rem .75rem .5rem 2.25rem;
        border: 1px solid var(--rv-line);
        border-radius: 10px;
        outline: none;
    }

    /* ------------------------------------------------------------
       TABLE PANEL
       ------------------------------------------------------------ */

    .rv-panel {
        border: 1px solid var(--rv-line);
        border-radius: 16px;
        overflow: hidden;
    }

    .rv-table-wrap {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .rv-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
    }

    .rv-table th,
    .rv-table td {
        padding: .85rem .9rem;
        vertical-align: middle;
        white-space: nowrap;
    }

    .rv-table th {
        font-size: .73rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 800;
    }

    .rv-table td {
        font-size: .84rem;
    }

    .rv-table tbody tr:last-child td {
        border-bottom: 0;
    }

    /* ------------------------------------------------------------
       CHIPS
       ------------------------------------------------------------ */

    .rv-chip {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        padding: .32rem .58rem;
        border-radius: 999px;
        border: 1px solid transparent;
        font-size: .72rem;
        font-weight: 800;
        line-height: 1;
        white-space: nowrap;
    }

    .rv-chip.is-grey {
        color: var(--rv-grey);
        background: var(--rv-grey-soft);
    }

    .rv-chip.is-ube {
        color: var(--rv-ube);
        background: var(--rv-ube-soft);
    }

    .rv-chip.is-mango {
        color: var(--rv-mango-ink);
        background: var(--rv-mango-soft);
    }

    .rv-chip.is-green {
        color: var(--rv-green);
        background: var(--rv-green-soft);
    }

    .rv-chip.is-red {
        color: var(--rv-red);
        background: rgba(220, 53, 69, .10);
    }

    /* ------------------------------------------------------------
       EMPTY
       ------------------------------------------------------------ */

    .rvi-empty {
        padding: 2.3rem 1.25rem;
        text-align: center;
        border: 1px dashed var(--rv-line);
        border-radius: 16px;
    }

    .rvi-empty-icon {
        width: 48px;
        height: 48px;
        display: grid;
        place-items: center;
        margin: 0 auto .8rem;
        border-radius: 14px;
        background: var(--rv-grey-soft);
        color: var(--rv-ink2);
        font-size: 1.25rem;
    }

    .rvi-empty h3 {
        margin: 0;
        font-size: 1rem;
        font-weight: 800;
    }

    .rvi-empty p {
        max-width: 560px;
        margin: .35rem auto 1rem;
        font-size: .83rem;
        line-height: 1.5;
    }

    /* ------------------------------------------------------------
       HELP
       ------------------------------------------------------------ */

    .rvi-help {
        margin-top: 1rem;
        border: 1px solid var(--rv-line);
        border-radius: 15px;
        overflow: hidden;
    }

    .rvi-help summary {
        cursor: pointer;
        padding: .95rem 1rem;
        font-weight: 800;
        list-style: none;
    }

    .rvi-help summary::-webkit-details-marker {
        display: none;
    }

    .rvi-help summary::after {
        content: "+";
        float: right;
        font-size: 1.1rem;
    }

    .rvi-help[open] summary::after {
        content: "−";
    }

    .rvi-help-body {
        padding: 0 1rem 1rem;
    }

    .rvi-help-item {
        padding: .65rem 0;
        border-top: 1px solid var(--rv-line);
        font-size: .82rem;
        line-height: 1.5;
    }

    .rvi-help-item strong {
        display: block;
        margin-bottom: .15rem;
    }

    /* ------------------------------------------------------------
       COPY
       ------------------------------------------------------------ */

    .rv-copy {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        padding: 0;
        border: 1px solid var(--rv-line);
        border-radius: 8px;
        background: var(--rv-wash);
    }

    /* ------------------------------------------------------------
       RESPONSIVE
       ------------------------------------------------------------ */

    @media (max-width: 1100px) {
        .rvi-tiles {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .rvi-steps {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 800px) {
        .rvi-next {
            align-items: flex-start;
            flex-direction: column;
        }

        .rvi-steps {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .rvi-tools {
            align-items: stretch;
            flex-direction: column;
        }
    }

    @media (max-width: 576px) {
        .rvi-tiles {
            grid-template-columns: 1fr;
        }

        .rvi-steps {
            grid-template-columns: 1fr;
        }

        .rvi-hero {
            padding: 1.1rem;
        }
    }
</style>

<div class="container-fluid py-4 rv-page">

    {{-- ============================================================
         RELIEF NAVIGATION
         ============================================================ --}}
    @include('relief-vouchers.partials.nav', ['active' => 'overview'])


    {{-- ============================================================
         HERO
         ============================================================ --}}
    <div class="rvi-hero">

        <div class="position-relative" style="z-index:2;">

            <div class="d-flex align-items-center gap-2 mb-2">
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

            <h1>Relief Operations & Vouchers</h1>

            <p class="lead">
                Manage relief operations, encode beneficiaries, generate vouchers,
                and monitor distribution and claiming in one workflow.
            </p>

        </div>

    </div>


    {{-- ============================================================
         NEXT STEP
         ============================================================ --}}
    <div class="rvi-next">

        <div class="rvi-next-left">

            <div class="rvi-next-icon">
                <i class="bi {{ $next[3] }}"></i>
            </div>

            <div>
                <h3>{{ $next[0] }}</h3>
                <span>{{ $next[1] }}</span>
            </div>

        </div>

        <a href="{{ $next[2] }}" class="btn btn-primary btn-sm">
            Continue
            <i class="bi bi-arrow-right ms-1"></i>
        </a>

    </div>


    {{-- ============================================================
         SUMMARY
         ============================================================ --}}
    <div class="rvi-tiles">

        <div class="rvi-tile">
            <small>Operations</small>
            <strong>{{ number_format($totalOperations) }}</strong>
        </div>

        <div class="rvi-tile">
            <small>Active Operations</small>
            <strong>{{ number_format($activeOperations) }}</strong>
        </div>

        <div class="rvi-tile">
            <small>Completed</small>
            <strong>{{ number_format($completedOperations) }}</strong>
        </div>

        <div class="rvi-tile">
            <small>Total Vouchers</small>
            <strong>{{ number_format($totalVouchers) }}</strong>
        </div>

    </div>


    {{-- ============================================================
         VOUCHER PROGRESS
         ============================================================ --}}
    <div class="rvi-bar">

        <div class="rvi-bar-top">

            <span>
                Voucher distribution progress
            </span>

            <strong>
                {{ $claimPercent }}% claimed
            </strong>

        </div>

        <div class="rvi-bar-track">

            @if($issuedVouchers > 0)
                <div
                    class="rvi-bar-segment rvi-bar-issued"
                    style="width: {{ $seg($issuedVouchers) }}%;"
                    title="Issued: {{ number_format($issuedVouchers) }}"
                ></div>
            @endif

            @if($claimedVouchers > 0)
                <div
                    class="rvi-bar-segment rvi-bar-claimed"
                    style="width: {{ $seg($claimedVouchers) }}%;"
                    title="Claimed: {{ number_format($claimedVouchers) }}"
                ></div>
            @endif

            @if($cancelledVouchers > 0)
                <div
                    class="rvi-bar-segment rvi-bar-cancelled"
                    style="width: {{ $seg($cancelledVouchers) }}%;"
                    title="Cancelled: {{ number_format($cancelledVouchers) }}"
                ></div>
            @endif

            @if($waiting > 0)
                <div
                    class="rvi-bar-segment rvi-bar-waiting"
                    style="width: {{ $seg($waiting) }}%;"
                    title="Waiting: {{ number_format($waiting) }}"
                ></div>
            @endif

        </div>

        <div class="rvi-legend">

            <span class="rvi-legend-item">
                <span
                    class="rvi-dot"
                    style="background: var(--rv-ube);"
                ></span>
                Issued: {{ number_format($issuedVouchers) }}
            </span>

            <span class="rvi-legend-item">
                <span
                    class="rvi-dot"
                    style="background: var(--rv-green);"
                ></span>
                Claimed: {{ number_format($claimedVouchers) }}
            </span>

            <span class="rvi-legend-item">
                <span
                    class="rvi-dot"
                    style="background: var(--rv-mango);"
                ></span>
                Waiting: {{ number_format($waiting) }}
            </span>

            <span class="rvi-legend-item">
                <span
                    class="rvi-dot"
                    style="background: var(--rv-red);"
                ></span>
                Cancelled: {{ number_format($cancelledVouchers) }}
            </span>

        </div>

    </div>


    {{-- ============================================================
         WORKFLOW
         ============================================================ --}}
    <div class="rvi-steps">

        <div class="rvi-step {{ $nowStep >= 1 ? 'is-active' : '' }}">
            <div class="rvi-step-number">1</div>

            <h3>Create operation</h3>

            <p>
                Define the relief activity, assistance type,
                schedule and location.
            </p>
        </div>


        <div class="rvi-step {{ $nowStep >= 2 ? 'is-active' : '' }}">
            <div class="rvi-step-number">2</div>

            <h3>Assess households</h3>

            <p>
                Record household impact and beneficiary
                eligibility.
            </p>
        </div>


        <div class="rvi-step {{ $nowStep >= 3 ? 'is-active' : '' }}">
            <div class="rvi-step-number">3</div>

            <h3>Encode beneficiaries</h3>

            <p>
                Verify household information and supporting
                requirements.
            </p>
        </div>


        <div class="rvi-step {{ $nowStep >= 4 ? 'is-active' : '' }}">
            <div class="rvi-step-number">4</div>

            <h3>Generate vouchers</h3>

            <p>
                Generate unique vouchers and prepare the
                printable distribution list.
            </p>
        </div>


        <div class="rvi-step {{ $nowStep >= 5 ? 'is-active' : '' }}">
            <div class="rvi-step-number">5</div>

            <h3>Scan & claim</h3>

            <p>
                Verify the QR voucher and record the
                one-time claim.
            </p>
        </div>

    </div>


    {{-- ============================================================
         RELIEF OPERATIONS
         ============================================================ --}}
    <div class="rv-section-head">

        <div>
            <h2>Relief Operations</h2>

            <p>
                Create and monitor relief activities.
            </p>
        </div>

        <a
            href="{{ route('relief-assistance.create') }}"
            class="btn btn-primary btn-sm"
        >
            <i class="bi bi-plus-circle me-1"></i>
            New Operation
        </a>

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
                                $operationStatus = strtolower(
                                    (string) ($operation->status ?? '')
                                );

                                $statusLabel =
                                    $statusLabels[$operationStatus]
                                    ?? ucfirst($operationStatus ?: 'Unknown');

                                $statusTone =
                                    $statusTones[$operationStatus]
                                    ?? 'is-grey';
                            @endphp

                            <tr data-operation-row>

                                <td>
                                    <strong>
                                        {{ $operation->operation_name ?? 'Unnamed Operation' }}
                                    </strong>

                                    @if(!empty($operation->operation_code))
                                        <div class="small text-muted">
                                            {{ $operation->operation_code }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    {{ $operation->assistance_type ?? '—' }}
                                </td>

                                <td>
                                    {{ $operation->distribution_date
                                        ? \Illuminate\Support\Carbon::parse($operation->distribution_date)->format('M d, Y')
                                        : '—'
                                    }}
                                </td>

                                <td>
                                    {{ $operation->location ?? '—' }}
                                </td>

                                <td>
                                    <span class="rv-chip {{ $statusTone }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td class="text-end">

                                    <a
                                        href="{{ route('relief-assistance.show', ['operation' => $operation->id]) }}"
                                        class="btn btn-sm btn-outline-primary"
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
                Create a relief operation first. You can then assess
                households, encode beneficiaries, generate vouchers,
                and process claims.
            </p>

            <a
                href="{{ route('relief-assistance.create') }}"
                class="btn btn-primary"
            >
                <i class="bi bi-plus-circle me-1"></i>
                Create Relief Operation
            </a>

        </div>

    @endif


    {{-- ============================================================
         RECENT VOUCHERS
         ============================================================ --}}
    <div class="rv-section-head">

        <div>
            <h2>Recent Vouchers</h2>

            <p>
                Recently generated and distributed relief vouchers.
            </p>
        </div>

        <a
            href="{{ route('relief-vouchers.qr-verification') }}"
            class="btn btn-outline-primary btn-sm"
        >
            <i class="bi bi-qr-code-scan me-1"></i>
            QR Verification
        </a>

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
                                $voucherStatus = strtolower(
                                    (string) ($voucher->status ?? '')
                                );

                                $voucherTone =
                                    $voucherTones[$voucherStatus]
                                    ?? 'is-grey';
                            @endphp

                            <tr data-voucher-row>

                                <td>
                                    <strong>
                                        {{ $voucher->voucher_code ?? '—' }}
                                    </strong>
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

                                <td>
                                    {{ $voucher->purok_name ?? '—' }}
                                </td>

                                <td>
                                    {{ $voucher->assistance_type ?? '—' }}
                                </td>

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
                                        class="btn btn-sm btn-outline-primary"
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


    {{-- ============================================================
         HELP
         ============================================================ --}}
    <details class="rvi-help">

        <summary>
            How the relief assistance workflow works
        </summary>

        <div class="rvi-help-body">

            <div class="rvi-help-item">
                <strong>1. Create a relief operation</strong>

                Define the assistance activity, schedule,
                location and other operational details.
            </div>


            <div class="rvi-help-item">
                <strong>2. Assess affected households</strong>

                Use household and disaster assessment information
                to identify households that may qualify for assistance.
            </div>


            <div class="rvi-help-item">
                <strong>3. Verify beneficiaries</strong>

                Staff verify the household information and required
                supporting documents before voucher generation.
            </div>


            <div class="rvi-help-item">
                <strong>4. Generate and print vouchers</strong>

                The system generates a unique voucher code and QR
                voucher that can be printed for distribution.
            </div>


            <div class="rvi-help-item">
                <strong>5. Scan and claim</strong>

                During distribution, authorized staff scan the QR
                voucher. A voucher can only be claimed once.
            </div>

        </div>

    </details>

</div>


{{-- ================================================================
     SEARCH SCRIPT
     ================================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
     * Relief Operations Search
     */
    const operationSearch = document.getElementById('operationSearch');

    if (operationSearch) {

        operationSearch.addEventListener('input', function () {

            const query = this.value
                .toLowerCase()
                .trim();

            document
                .querySelectorAll('[data-operation-row]')
                .forEach(function (row) {

                    const text = row.textContent
                        .toLowerCase();

                    row.style.display =
                        text.includes(query)
                            ? ''
                            : 'none';

                });

        });

    }


    /*
     * Voucher Search
     */
    const voucherSearch = document.getElementById('voucherSearch');

    if (voucherSearch) {

        voucherSearch.addEventListener('input', function () {

            const query = this.value
                .toLowerCase()
                .trim();

            document
                .querySelectorAll('[data-voucher-row]')
                .forEach(function (row) {

                    const text = row.textContent
                        .toLowerCase();

                    row.style.display =
                        text.includes(query)
                            ? ''
                            : 'none';

                });

        });

    }

});
</script>

@endsection