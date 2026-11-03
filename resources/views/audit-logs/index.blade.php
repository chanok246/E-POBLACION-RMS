@extends('layouts.app')

@section('title', 'Audit Trail')

@section('content')

<style>
    /* =========================================================
       AUDIT TRAIL — balanced light & dark mode
       All colors live in tokens on .audit-page, so text,
       backgrounds, borders, badges and inputs keep strong
       contrast in BOTH themes (html[data-theme="dark"]).
       ========================================================= */

    .audit-page {
        /* surfaces */
        --au-card: #ffffff;
        --au-soft: #f1f5f9;
        --au-soft-2: #e2e8f0;
        --au-hover: #eef3fb;
        --au-line: #d5dce5;
        --au-line-strong: #98a6b5;

        /* text */
        --au-text: #14202c;
        --au-text-2: #2d3b49;
        --au-muted: #4b5a69;

        /* accent */
        --au-accent: #1d4ed8;
        --au-accent-fg: #ffffff;
        --au-accent-hover: #1639a8;
        --au-accent-soft: #e3ebfd;
        --au-focus: #b45309;

        /* action badges */
        --au-create-bg: #dcf3e6;  --au-create-ink: #0f5f3a;
        --au-update-bg: #dfe9fd;  --au-update-ink: #1b45b5;
        --au-delete-bg: #fde3e6;  --au-delete-ink: #a1202f;
        --au-view-bg: #e6eaef;    --au-view-ink: #3b4856;
        --au-auth-bg: #fdefc7;    --au-auth-ink: #6b4700;
        --au-module-bg: #e6eaef;  --au-module-ink: #2d3b49;

        --au-mark-bg: #fde68a;
        --au-mark-ink: #14202c;

        --au-shadow: 0 1px 2px rgba(15, 30, 50, .06), 0 8px 22px rgba(15, 30, 50, .07);
        --au-select-arrow: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2314202c' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");

        color: var(--au-text);
        color-scheme: light;
    }

    html[data-theme="dark"] .audit-page {
        --au-card: #1b2430;
        --au-soft: #141c26;
        --au-soft-2: #263344;
        --au-hover: #243142;
        --au-line: #33425a;
        --au-line-strong: #5a7087;

        --au-text: #f1f5f9;
        --au-text-2: #d6dfe8;
        --au-muted: #b4c2d0;

        --au-accent: #8db4ff;
        --au-accent-fg: #0a1a3d;
        --au-accent-hover: #b3ceff;
        --au-accent-soft: #22345c;
        --au-focus: #f2c14e;

        --au-create-bg: #173a2b;  --au-create-ink: #86efb8;
        --au-update-bg: #1e3262;  --au-update-ink: #a9c6ff;
        --au-delete-bg: #452029;  --au-delete-ink: #ffb4bd;
        --au-view-bg: #2a3646;    --au-view-ink: #d3dce6;
        --au-auth-bg: #3d3213;    --au-auth-ink: #fde68a;
        --au-module-bg: #2a3646;  --au-module-ink: #d3dce6;

        --au-mark-bg: #6b5410;
        --au-mark-ink: #ffffff;

        --au-shadow: none;
        --au-select-arrow: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23f1f5f9' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");

        color-scheme: dark;
    }

    .audit-page .text-secondary {
        color: var(--au-muted) !important;
    }

    .audit-page :focus-visible {
        outline: 3px solid var(--au-focus);
        outline-offset: 2px;
    }

    /* ---------- Header ---------- */

    .audit-header {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    .audit-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .8rem;
        font-weight: 700;
        color: var(--au-muted);
    }

    .audit-title {
        font-size: 1.7rem;
        font-weight: 800;
        margin: .25rem 0 .35rem;
        color: var(--au-text);
    }

    .audit-subtitle {
        color: var(--au-text-2);
        margin-bottom: 0;
        max-width: 60ch;
    }

    .audit-header-side {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: .6rem;
    }

    .audit-readonly {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .45rem .75rem;
        border-radius: 999px;
        background-color: var(--au-soft-2);
        border: 1px solid var(--au-line);
        color: var(--au-text-2);
        font-size: .8rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .audit-back-link {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .88rem;
        font-weight: 700;
        color: var(--au-accent);
        text-decoration: none;
    }

    .audit-back-link:hover {
        color: var(--au-accent-hover);
        text-decoration: underline;
    }

    /* ---------- Stat strip ---------- */

    .audit-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: .85rem;
        margin-bottom: 1.25rem;
    }

    .audit-stat {
        padding: .95rem 1.1rem;
        border-radius: 12px;
        background-color: var(--au-card);
        border: 1px solid var(--au-line);
        box-shadow: var(--au-shadow);
    }

    .audit-stat-label {
        font-size: .75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--au-muted);
    }

    .audit-stat-value {
        margin-top: .3rem;
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1;
        font-variant-numeric: tabular-nums;
        color: var(--au-text);
    }

    /* ---------- Filter bar (sticky, instant) ---------- */

    .audit-filter-bar {
        position: sticky;
        top: .5rem;
        z-index: 5;
        margin-bottom: 1.25rem;
        padding: 1rem;
        border-radius: 18px;
        background-color: var(--au-card);
        border: 1px solid var(--au-line);
        box-shadow: var(--au-shadow);
    }

    /* ---------- Search + filters layout (grid) ---------- */

    .audit-search-row {
        display: grid;
        grid-template-columns: minmax(280px, 1.2fr) 2fr;
        gap: .75rem;
        align-items: start;
    }

    .audit-search-field {
        position: relative;
        align-self: start;
    }

    /* Search icon sits INSIDE the input, vertically centered */
    .audit-search-field > .bi-search {
        position: absolute;
        left: 1rem;
        top: 23px;                       /* half of the 46px input height */
        transform: translateY(-50%);
        font-size: 1rem;
        line-height: 1;
        color: var(--au-accent);
        pointer-events: none;
        z-index: 2;
        transition: transform .2s ease, color .2s ease;
    }

    .audit-search-field:focus-within > .bi-search {
        transform: translateY(-50%) scale(1.12);
    }

    .audit-search-clear {
        position: absolute;
        right: .7rem;
        top: 23px;
        transform: translateY(-50%);
        width: 24px;
        height: 24px;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 50%;
        background: var(--au-soft-2);
        color: var(--au-muted);
        padding: 0;
        line-height: 1;
        display: none;
        z-index: 2;
        transition: background-color .15s ease, color .15s ease;
    }

    .audit-search-clear:hover {
        background: var(--au-accent);
        color: var(--au-accent-fg);
    }

    .audit-search-field.has-value .audit-search-clear {
        display: inline-flex;
    }

    .audit-select-group {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: .6rem;
    }

    /* Form fields (explicit so they never inherit mismatched Bootstrap colors) */

    .audit-filter-bar .form-control,
    .audit-filter-bar .form-select {
        min-height: 46px;
        border-radius: 12px;
        background-color: var(--au-soft);
        color: var(--au-text);
        border: 1.5px solid var(--au-line);
        font-size: .92rem;
        font-weight: 600;
        box-shadow: none;
        transition: border-color .2s ease, box-shadow .2s ease, background-color .2s ease;
    }

    .audit-filter-bar .form-control::placeholder {
        color: var(--au-muted);
        opacity: .85;
        font-weight: 500;
    }

    .audit-filter-bar .form-select {
        appearance: none;
        -webkit-appearance: none;
        background-image: var(--au-select-arrow);
        background-repeat: no-repeat;
        background-position: right .85rem center;
        background-size: 16px;
        padding-right: 2.3rem;
    }

    .audit-filter-bar .form-select option {
        background: var(--au-card);
        color: var(--au-text);
    }

    .audit-filter-bar .form-control:hover,
    .audit-filter-bar .form-select:hover {
        border-color: var(--au-line-strong);
    }

    .audit-filter-bar .form-control:focus,
    .audit-filter-bar .form-select:focus {
        background-color: var(--au-card);
        color: var(--au-text);
        border-color: var(--au-accent);
        box-shadow: 0 0 0 4px var(--au-accent-soft);
    }

    /* Search input = modern pill, icon inside */
    .audit-filter-bar .audit-search-field input {
        width: 100%;
        padding-left: 2.8rem;
        padding-right: 2.6rem;
        border-radius: 999px;
    }

    /* Buttons */

    .audit-page .btn {
        font-weight: 700;
        border-radius: .6rem;
    }

    .audit-page .btn-primary {
        background: var(--au-accent);
        border: 1.5px solid var(--au-accent);
        color: var(--au-accent-fg);
    }

    .audit-page .btn-primary:hover,
    .audit-page .btn-primary:focus-visible {
        background: var(--au-accent-hover);
        border-color: var(--au-accent-hover);
        color: var(--au-accent-fg);
    }

    .audit-page .btn-outline-secondary {
        background: var(--au-card);
        border: 1.5px solid var(--au-line-strong);
        color: var(--au-text);
    }

    .audit-page .btn-outline-secondary:hover,
    .audit-page .btn-outline-secondary:focus-visible {
        background: var(--au-soft-2);
        border-color: var(--au-accent);
        color: var(--au-text);
    }

    /* Fallback button — hidden once JS confirms auto-filtering works */

    .audit-apply-fallback {
        display: none;
    }

    .no-js .audit-apply-fallback {
        display: inline-flex;
    }

    /* Slim progress indicator while a filter reloads the page */

    .audit-loading .audit-filter-bar::after {
        content: '';
        position: absolute;
        left: 12px;
        right: 12px;
        bottom: -2px;
        height: 2px;
        border-radius: 2px;
        background: linear-gradient(90deg, transparent, var(--au-accent), transparent);
        background-size: 200% 100%;
        animation: audit-scan 1s linear infinite;
    }

    @keyframes audit-scan {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* ---------- Active filter chips ---------- */

    .audit-chips {
        display: flex;
        flex-wrap: wrap;
        gap: .45rem;
        margin-top: .85rem;
        padding-top: .85rem;
        border-top: 1px dashed var(--au-line);
    }

    .audit-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .6rem .3rem .75rem;
        border-radius: 999px;
        background-color: var(--au-accent-soft);
        border: 1px solid var(--au-accent);
        color: var(--au-text);
        font-size: .8rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        transition: background-color .15s ease;
    }

    .audit-chip i {
        font-size: .8rem;
        color: var(--au-accent);
    }

    .audit-chip:hover {
        background-color: var(--au-soft-2);
        color: var(--au-text);
    }

    .audit-chip-clear {
        color: var(--au-text-2);
        font-size: .8rem;
        font-weight: 700;
        text-decoration: underline;
        align-self: center;
    }

    .audit-chip-clear:hover {
        color: var(--au-accent);
    }

    /* ---------- Table card ---------- */

    .audit-card {
        background-color: var(--au-card);
        color: var(--au-text);
        border: 1px solid var(--au-line);
        border-radius: 14px;
        box-shadow: var(--au-shadow);
        overflow: hidden;
    }

    .audit-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--au-line);
    }

    .audit-card-title {
        font-weight: 800;
        margin: 0;
        color: var(--au-text);
    }

    .audit-info {
        color: var(--au-muted);
        font-size: .85rem;
    }

    /* ---------- Table ---------- */

    .audit-table-wrapper {
        overflow-x: auto;
    }

    .audit-table {
        --bs-table-bg: transparent;
        --bs-table-color: var(--au-text);
        --bs-table-border-color: var(--au-line);
        --bs-table-striped-bg: transparent;
        --bs-table-hover-bg: var(--au-hover);
        --bs-table-hover-color: var(--au-text);

        width: 100%;
        min-width: 980px;
        margin-bottom: 0;
        vertical-align: middle;
        color: var(--au-text);
    }

    .audit-table th {
        padding: .8rem .85rem;
        font-size: .75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .045em;
        white-space: nowrap;
        background-color: var(--au-soft);
        color: var(--au-text-2);
        border-bottom: 1px solid var(--au-line);
    }

    .audit-table td {
        padding: .85rem .85rem;
        background-color: transparent;
        border-color: var(--au-line);
        color: var(--au-text);
    }

    .audit-table tbody tr:hover td {
        background-color: var(--au-hover);
    }

    .audit-date {
        width: 125px;
        white-space: nowrap;
    }

    .audit-date-main {
        font-weight: 700;
    }

    .audit-date-time {
        margin-top: .15rem;
        font-size: .78rem;
        color: var(--au-muted);
    }

    .audit-user {
        min-width: 135px;
    }

    .audit-user-name {
        font-weight: 700;
        white-space: nowrap;
    }

    .audit-user-role {
        margin-top: .15rem;
        font-size: .78rem;
        color: var(--au-muted);
        text-transform: capitalize;
    }

    /* ---------- Action badges (color-coded by verb) ---------- */

    .audit-action-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border-radius: 999px;
        padding: .32rem .65rem;
        font-size: .75rem;
        font-weight: 800;
        line-height: 1.2;
        white-space: nowrap;
    }

    .audit-action-badge::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        flex: none;
    }

    .audit-action-badge.is-create { background-color: var(--au-create-bg); color: var(--au-create-ink); }
    .audit-action-badge.is-update { background-color: var(--au-update-bg); color: var(--au-update-ink); }
    .audit-action-badge.is-delete { background-color: var(--au-delete-bg); color: var(--au-delete-ink); }
    .audit-action-badge.is-view   { background-color: var(--au-view-bg);   color: var(--au-view-ink); }
    .audit-action-badge.is-auth   { background-color: var(--au-auth-bg);   color: var(--au-auth-ink); }

    .audit-module-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .32rem .65rem;
        font-size: .75rem;
        font-weight: 800;
        line-height: 1.2;
        white-space: nowrap;
        background-color: var(--au-module-bg);
        color: var(--au-module-ink);
    }

    .audit-record {
        font-family: Consolas, Monaco, monospace;
        font-size: .82rem;
        font-weight: 700;
        word-break: break-word;
        color: var(--au-text);
    }

    .audit-record-id {
        display: block;
        margin-top: .2rem;
        font-size: .75rem;
        color: var(--au-muted);
    }

    .audit-description {
        min-width: 260px;
        max-width: 460px;
        line-height: 1.5;
        word-break: break-word;
        color: var(--au-text);
    }

    .audit-description mark {
        padding: 0 .12em;
        border-radius: 3px;
        background-color: var(--au-mark-bg);
        color: var(--au-mark-ink);
    }

    /* ---------- Empty state ---------- */

    .audit-empty {
        padding: 3.5rem 1rem;
        text-align: center;
        color: var(--au-text-2);
    }

    .audit-empty h5 {
        color: var(--au-text);
    }

    .audit-empty-icon {
        font-size: 2.5rem;
        margin-bottom: .75rem;
        color: var(--au-accent);
    }

    .audit-empty .btn {
        margin-top: .75rem;
    }

    /* ---------- Pagination ---------- */

    .audit-pagination {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--au-line);
    }

    .audit-pagination .pagination {
        margin-bottom: 0;
        gap: 4px;
    }

    .audit-pagination .page-link {
        background-color: var(--au-card);
        border: 1.5px solid var(--au-line-strong);
        color: var(--au-text);
        font-weight: 700;
        border-radius: 8px !important;
    }

    .audit-pagination .page-link:hover {
        background-color: var(--au-soft-2);
        border-color: var(--au-accent);
        color: var(--au-text);
    }

    .audit-pagination .page-item.active .page-link {
        background-color: var(--au-accent);
        border-color: var(--au-accent);
        color: var(--au-accent-fg);
    }

    .audit-pagination .page-item.disabled .page-link {
        background-color: var(--au-soft);
        border-color: var(--au-line);
        color: var(--au-muted);
        opacity: .7;
    }

    .audit-pagination p,
    .audit-pagination .small,
    .audit-pagination .text-muted {
        color: var(--au-muted) !important;
    }

    /* ---------- Print-only header ---------- */

    .audit-print-header {
        display: none;
    }

    /* ---------- Responsive ---------- */

    @media (max-width: 992px) {

        .audit-search-row {
            grid-template-columns: 1fr;
        }

        .audit-description {
            max-width: 360px;
        }
    }

    @media (max-width: 768px) {

        .audit-title {
            font-size: 1.5rem;
        }

        .audit-header-side {
            align-items: flex-start;
            width: 100%;
        }

        .audit-readonly {
            width: 100%;
            justify-content: center;
        }

        .audit-select-group {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 700px) {

        .audit-table-wrapper {
            overflow-x: visible;
        }

        .audit-table,
        .audit-table thead,
        .audit-table tbody,
        .audit-table th,
        .audit-table td,
        .audit-table tr {
            display: block;
            width: 100%;
        }

        .audit-table {
            min-width: 0;
        }

        .audit-table thead {
            position: absolute;
            top: -9999px;
            left: -9999px;
        }

        .audit-table tbody tr {
            margin: 0 0 .85rem;
            padding: .85rem .9rem;
            border: 1px solid var(--au-line);
            border-radius: 12px;
            background-color: var(--au-card);
        }

        .audit-table td {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: .75rem;
            padding: .4rem 0;
            border: 0;
            text-align: right;
        }

        .audit-table td::before {
            content: attr(data-label);
            flex: none;
            padding-top: .1rem;
            font-size: .74rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--au-muted);
            text-align: left;
        }

        .audit-description,
        .audit-record {
            max-width: 100%;
            text-align: right;
        }
    }

    @media (max-width: 480px) {

        .audit-select-group {
            grid-template-columns: 1fr;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .audit-loading .audit-filter-bar::after {
            animation: none;
        }

        .audit-search-field > .bi-search,
        .audit-filter-bar .form-control,
        .audit-filter-bar .form-select {
            transition: none;
        }
    }

    /* =========================================================
       PRINT STYLES — always light, even if the screen is dark
       ========================================================= */

    @media print {

        @page {
            size: A4 landscape;
            margin: 14mm 12mm 14mm 12mm;
        }

        html,
        body {
            background: #fff !important;
            color: #000 !important;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
        }

        .sidebar,
        .navbar,
        .audit-filter-bar,
        .audit-stats,
        .audit-back-link,
        .audit-readonly,
        .btn,
        .audit-pagination {
            display: none !important;
        }

        .audit-page,
        html[data-theme="dark"] .audit-page {
            --au-card: #ffffff;
            --au-soft: #e9ecef;
            --au-line: #999999;
            --au-text: #000000;
            --au-text-2: #222222;
            --au-muted: #444444;
        }

        .audit-page {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            color: #000 !important;
        }

        .audit-print-header {
            display: block !important;
            margin-bottom: 12px;
            padding-bottom: 9px;
            border-bottom: 2px solid #000;
            color: #000 !important;
        }

        .audit-print-barangay {
            font-size: 15pt;
            font-weight: 700;
            text-align: center;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .audit-print-system {
            margin-top: 2px;
            font-size: 10pt;
            font-weight: 600;
            text-align: center;
            text-transform: uppercase;
        }

        .audit-print-title {
            margin-top: 7px;
            font-size: 14pt;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .audit-print-date {
            margin-top: 3px;
            font-size: 8.5pt;
            text-align: center;
            color: #444 !important;
        }

        .audit-card {
            width: 100% !important;
            border: 0 !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            background: #fff !important;
            overflow: visible !important;
            color: #000 !important;
        }

        .audit-card-header {
            display: flex !important;
            padding: 0 0 7px 0 !important;
            border: 0 !important;
            background: #fff !important;
            color: #000 !important;
        }

        .audit-card-header .audit-card-title {
            font-size: 10pt;
            color: #000 !important;
        }

        .audit-card-header .audit-info {
            color: #333 !important;
            font-size: 8pt !important;
        }

        .audit-table-wrapper {
            width: 100% !important;
            overflow: visible !important;
        }

        .audit-table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
            font-size: 7.5pt !important;
            color: #000 !important;
        }

        .audit-table,
        .audit-table thead,
        .audit-table tbody,
        .audit-table th,
        .audit-table td,
        .audit-table tr {
            display: revert !important;
        }

        .audit-table thead {
            display: table-header-group !important;
        }

        .audit-table tbody {
            display: table-row-group !important;
        }

        .audit-table tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .audit-table th {
            padding: 6px 5px !important;
            background: #e9ecef !important;
            color: #000 !important;
            border: 1px solid #777 !important;
            font-size: 7pt !important;
            font-weight: 700 !important;
            text-transform: uppercase;
        }

        .audit-table td {
            padding: 6px 5px !important;
            background: #fff !important;
            color: #000 !important;
            border: 1px solid #999 !important;
            font-size: 7.5pt !important;
            vertical-align: top !important;
            text-align: left !important;
        }

        .audit-table td::before {
            content: none !important;
        }

        .audit-table th:nth-child(1),
        .audit-table td:nth-child(1) { width: 12%; }

        .audit-table th:nth-child(2),
        .audit-table td:nth-child(2) { width: 13%; }

        .audit-table th:nth-child(3),
        .audit-table td:nth-child(3) { width: 11%; }

        .audit-table th:nth-child(4),
        .audit-table td:nth-child(4) { width: 13%; }

        .audit-table th:nth-child(5),
        .audit-table td:nth-child(5) { width: 15%; }

        .audit-table th:nth-child(6),
        .audit-table td:nth-child(6) { width: 36%; }

        .audit-date { width: auto !important; white-space: normal !important; }
        .audit-date-main { font-weight: 700 !important; }
        .audit-date-time { color: #444 !important; font-size: 7pt !important; }
        .audit-user-name { white-space: normal !important; font-weight: 700 !important; }
        .audit-user-role { color: #444 !important; font-size: 7pt !important; }

        .audit-action-badge,
        .audit-module-badge {
            display: inline !important;
            padding: 0 !important;
            border-radius: 0 !important;
            background: transparent !important;
            color: #000 !important;
            font-size: 7.5pt !important;
            font-weight: 700 !important;
            white-space: normal !important;
        }

        .audit-action-badge::before {
            content: none !important;
        }

        .audit-record {
            font-family: Consolas, Monaco, monospace !important;
            font-size: 7.2pt !important;
            font-weight: 700 !important;
            overflow-wrap: anywhere !important;
            color: #000 !important;
        }

        .audit-record-id {
            margin-top: 1px !important;
            color: #444 !important;
            font-size: 6.8pt !important;
        }

        .audit-description {
            min-width: 0 !important;
            max-width: none !important;
            line-height: 1.35 !important;
            overflow-wrap: anywhere !important;
            color: #000 !important;
        }

        .audit-description mark {
            background: transparent !important;
            color: #000 !important;
            font-weight: 700 !important;
        }

        .audit-empty {
            padding: 25px 10px !important;
            color: #333 !important;
            border: 1px solid #999 !important;
        }

        .audit-empty-icon {
            display: none !important;
        }

        a {
            color: #000 !important;
            text-decoration: none !important;
        }
    }
</style>

<div class="container-fluid audit-page">

{{-- =====================================================
     PRINT-ONLY REPORT HEADER
     ===================================================== --}}

<div class="audit-print-header">

    <div class="audit-print-barangay">
        Barangay Poblacion
    </div>

    <div class="audit-print-system">
        Record Management System
    </div>

    <div class="audit-print-title">
        Audit Trail Report
    </div>

    <div class="audit-print-date">
        Printed on {{ now()->format('F d, Y h:i A') }}
    </div>

</div>


{{-- =====================================================
     PAGE HEADER
     ===================================================== --}}

<div class="audit-header">

    <div>

        <span class="audit-eyebrow">
            <i class="bi bi-shield-check"></i>
            System activity
        </span>

        <h1 class="audit-title">
            Audit Trail
        </h1>

        <p class="audit-subtitle">
            A record of who did what, and when — every create, update,
            delete, and access event performed by an authorized user.
        </p>

    </div>


    <div class="audit-header-side">

        <div class="audit-readonly">
            <i class="bi bi-lock-fill"></i>
            Read-only records
        </div>

        <a href="{{ route('reports-analytics.index') }}" class="audit-back-link">
            <i class="bi bi-arrow-left"></i>
            Back to Reports &amp; Analytics
        </a>

    </div>

</div>


{{-- =====================================================
     STAT STRIP
     Uses only data already available on this page — safe to
     drop in without controller changes. If your controller
     later passes $todayCount / $failedLoginCount / etc, this
     section will pick them up automatically via @isset.
     ===================================================== --}}

<div class="audit-stats">

    <div class="audit-stat">
        <div class="audit-stat-label">Matching records</div>
        <div class="audit-stat-value">{{ number_format($auditLogs->total()) }}</div>
    </div>

    @isset($todayCount)
        <div class="audit-stat">
            <div class="audit-stat-label">Today</div>
            <div class="audit-stat-value">{{ number_format($todayCount) }}</div>
        </div>
    @endisset

    @isset($failedLoginCount)
        <div class="audit-stat">
            <div class="audit-stat-label">Failed logins (7d)</div>
            <div class="audit-stat-value">{{ number_format($failedLoginCount) }}</div>
        </div>
    @endisset

    @isset($activeUserCount)
        <div class="audit-stat">
            <div class="audit-stat-label">Active users</div>
            <div class="audit-stat-value">{{ number_format($activeUserCount) }}</div>
        </div>
    @endisset

    <div class="audit-stat">
        <div class="audit-stat-label">Page</div>
        <div class="audit-stat-value">{{ $auditLogs->currentPage() }} / {{ max($auditLogs->lastPage(), 1) }}</div>
    </div>

</div>


{{-- =====================================================
     INSTANT FILTER BAR
     No "Apply" click needed: selects and date inputs submit
     on change, and the search box auto-submits ~450ms after
     the user stops typing. See the script at the bottom.
     ===================================================== --}}

<div class="audit-filter-bar">

    <form
        method="GET"
        action="{{ route('audit-logs.index') }}"
        class="audit-filter-form"
        id="auditFilterForm"
    >

        <div class="audit-search-row">

            <div class="audit-search-field" id="auditSearchField">

                <i class="bi bi-search"></i>

                <input
                    type="text"
                    name="search"
                    id="search"
                    class="form-control"
                    value="{{ $search }}"
                    placeholder="Search user, action, module, record, or description"
                    autocomplete="off"
                >

                <button type="button" class="audit-search-clear" id="auditSearchClear" aria-label="Clear search">
                    <i class="bi bi-x"></i>
                </button>

            </div>

            <div class="audit-select-group">

                <select name="module" id="module" class="form-select" aria-label="Filter by module">
                    <option value="">All modules</option>
                    @foreach($modules as $moduleOption)
                        <option value="{{ $moduleOption }}" @selected($module === $moduleOption)>
                            {{ $moduleOption }}
                        </option>
                    @endforeach
                </select>

                <select name="action" id="action" class="form-select" aria-label="Filter by action">
                    <option value="">All actions</option>
                    @foreach($actions as $actionOption)
                        <option value="{{ $actionOption }}" @selected($action === $actionOption)>
                            {{ $actionOption }}
                        </option>
                    @endforeach
                </select>

                <select name="user_id" id="user_id" class="form-select" aria-label="Filter by user">
                    <option value="">All users</option>
                    @foreach($users as $userOption)
                        <option value="{{ $userOption->id }}" @selected((string) $userId === (string) $userOption->id)>
                            {{ $userOption->display_name }}
                        </option>
                    @endforeach
                </select>

                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}" title="From date" aria-label="From date">
                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}" title="To date" aria-label="To date">

                {{-- No-JS fallback only --}}
                <button type="submit" class="btn btn-primary audit-apply-fallback">
                    <i class="bi bi-search me-1"></i>
                    Apply filters
                </button>

            </div>

        </div>


        {{-- ---------- Active filter chips ---------- --}}

        @php
            $hasActiveFilters = $search || $module || $action || $userId || $dateFrom || $dateTo;
        @endphp

        @if($hasActiveFilters)

            <div class="audit-chips">

                @if($search)
                    <a href="{{ route('audit-logs.index', array_merge(request()->except('search', 'page'), [])) }}" class="audit-chip">
                        <i class="bi bi-search"></i>
                        "{{ $search }}"
                        <i class="bi bi-x"></i>
                    </a>
                @endif

                @if($module)
                    <a href="{{ route('audit-logs.index', array_merge(request()->except('module', 'page'), [])) }}" class="audit-chip">
                        <i class="bi bi-folder"></i>
                        {{ $module }}
                        <i class="bi bi-x"></i>
                    </a>
                @endif

                @if($action)
                    <a href="{{ route('audit-logs.index', array_merge(request()->except('action', 'page'), [])) }}" class="audit-chip">
                        <i class="bi bi-lightning"></i>
                        {{ $action }}
                        <i class="bi bi-x"></i>
                    </a>
                @endif

                @if($userId)
                    <a href="{{ route('audit-logs.index', array_merge(request()->except('user_id', 'page'), [])) }}" class="audit-chip">
                        <i class="bi bi-person"></i>
                        {{ optional($users->firstWhere('id', $userId))->display_name ?? 'User' }}
                        <i class="bi bi-x"></i>
                    </a>
                @endif

                @if($dateFrom || $dateTo)
                    <a href="{{ route('audit-logs.index', array_merge(request()->except(['date_from', 'date_to', 'page']), [])) }}" class="audit-chip">
                        <i class="bi bi-calendar-range"></i>
                        {{ $dateFrom ?: '…' }} – {{ $dateTo ?: '…' }}
                        <i class="bi bi-x"></i>
                    </a>
                @endif

                <a href="{{ route('audit-logs.index') }}" class="audit-chip-clear">
                    Clear all
                </a>

            </div>

        @endif

    </form>

</div>


{{-- =====================================================
     AUDIT LOG TABLE
     ===================================================== --}}

<div class="audit-card">

    <div class="audit-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">

        <div>

            <h5 class="audit-card-title">
                <i class="bi bi-clock-history me-2"></i>
                Activity history
            </h5>

            <div class="audit-info mt-1">
                @if($auditLogs->total() > 0)
                    Showing {{ $auditLogs->firstItem() }}–{{ $auditLogs->lastItem() }} of {{ number_format($auditLogs->total()) }} records, most recent first.
                @else
                    No records to show.
                @endif
            </div>

        </div>

    </div>


    <div class="audit-table-wrapper">

        @if($auditLogs->count() > 0)

            <table class="table audit-table">

                <thead>
                    <tr>
                        <th>Date &amp; time</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Module</th>
                        <th>Record</th>
                        <th>Description</th>
                    </tr>
                </thead>


                <tbody>

                    @foreach($auditLogs as $log)

                        @php
                            $actionLabel = (string) $log->action;
                            $actionKey = strtolower($actionLabel);

                            $actionClass = match (true) {
                                str_contains($actionKey, 'creat') => 'is-create',
                                str_contains($actionKey, 'updat') => 'is-update',
                                str_contains($actionKey, 'delet') => 'is-delete',
                                str_contains($actionKey, 'view')  => 'is-view',
                                str_contains($actionKey, 'login') || str_contains($actionKey, 'auth') => 'is-auth',
                                default => 'is-view',
                            };

                            $description = $log->description ?: '—';

                            // Highlight the matched search term for quick scanning.
                            if ($search && $log->description) {
                                $description = preg_replace(
                                    '/(' . preg_quote(e($search), '/') . ')/i',
                                    '<mark>$1</mark>',
                                    e($log->description)
                                );
                            } else {
                                $description = e($description);
                            }
                        @endphp

                        <tr>

                            <td class="audit-date" data-label="Date & time">
                                <div class="audit-date-main">
                                    {{ $log->created_at?->format('M d, Y') ?? '—' }}
                                </div>
                                <div class="audit-date-time">
                                    {{ $log->created_at?->format('h:i:s A') ?? '—' }}
                                </div>
                            </td>

                            <td class="audit-user" data-label="User">
                                @if($log->user)
                                    <div class="audit-user-name">{{ $log->user->display_name }}</div>
                                    <div class="audit-user-role">{{ ucfirst($log->user->role) }}</div>
                                @else
                                    <span class="text-secondary">System</span>
                                @endif
                            </td>

                            <td data-label="Action">
                                <span class="audit-action-badge {{ $actionClass }}">
                                    {{ $actionLabel }}
                                </span>
                            </td>

                            <td data-label="Module">
                                <span class="audit-module-badge">
                                    {{ $log->module }}
                                </span>
                            </td>

                            <td data-label="Record">
                                @if($log->record_reference)
                                    <div class="audit-record">{{ $log->record_reference }}</div>
                                    @if($log->record_id)
                                        <span class="audit-record-id">ID: {{ $log->record_id }}</span>
                                    @endif
                                @elseif($log->record_id)
                                    <div class="audit-record">ID: {{ $log->record_id }}</div>
                                @else
                                    <span class="text-secondary">—</span>
                                @endif
                            </td>

                            <td data-label="Description">
                                <div class="audit-description">
                                    {!! $description !!}
                                </div>
                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        @else

            <div class="audit-empty">

                <div class="audit-empty-icon">
                    <i class="bi bi-journal-x"></i>
                </div>

                <h5 class="fw-semibold">
                    No audit records found
                </h5>

                <p class="mb-0">
                    @if($search)
                        No activity matches "{{ $search }}" or the selected filters.
                    @else
                        No activity matches the selected filters.
                    @endif
                </p>

                <a href="{{ route('audit-logs.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>
                    Clear filters
                </a>

            </div>

        @endif

    </div>


    @if($auditLogs->hasPages())

        <div class="audit-pagination">
            {{ $auditLogs->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>

    @endif

</div>

</div>


{{-- =============================================================
     INSTANT / AUTO-APPLY FILTERING
     - Selects and date inputs submit immediately on change.
     - The search box debounces ~450ms after typing stops, so the
       page isn't reloaded on every keystroke.
     - Scroll position is preserved across the reload so the table
       doesn't jump back to the top while someone is reading it.
     - If JavaScript is unavailable, the page falls back to the
       manual "Apply filters" button (progressive enhancement).
============================================================= --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    document.documentElement.classList.remove('no-js');

    const form = document.getElementById('auditFilterForm');

    if (!form) {
        return;
    }

    const searchField = document.getElementById('auditSearchField');
    const searchInput = document.getElementById('search');
    const searchClear = document.getElementById('auditSearchClear');

    const SCROLL_KEY = 'auditTrailScrollY';
    let debounceTimer;

    function toggleClearButton() {
        if (searchInput.value.trim().length > 0) {
            searchField.classList.add('has-value');
        } else {
            searchField.classList.remove('has-value');
        }
    }

    function submitFiltered() {
        sessionStorage.setItem(SCROLL_KEY, String(window.scrollY));
        document.body.classList.add('audit-loading');

        if (form.requestSubmit) {
            form.requestSubmit();
        } else {
            form.submit();
        }
    }

    if (searchInput) {

        toggleClearButton();

        searchInput.addEventListener('input', function () {
            toggleClearButton();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(submitFiltered, 450);
        });

        // Enter key skips the debounce and searches immediately.
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(debounceTimer);
                submitFiltered();
            }
        });
    }

    if (searchClear) {
        searchClear.addEventListener('click', function () {
            searchInput.value = '';
            toggleClearButton();
            submitFiltered();
        });
    }

    form.querySelectorAll('select, input[type="date"]').forEach(function (el) {
        el.addEventListener('change', submitFiltered);
    });

    // Restore scroll position after an auto-filtered reload.
    const savedScroll = sessionStorage.getItem(SCROLL_KEY);

    if (savedScroll !== null) {
        window.scrollTo(0, parseInt(savedScroll, 10) || 0);
        sessionStorage.removeItem(SCROLL_KEY);
    }

});
</script>

@endsection
