@extends('layouts.app')

@section('title', 'Audit Trail')

@section('content')

<style>
    /* =========================================================
       AUDIT TRAIL — screen styles
       Design notes:
       - Filters auto-apply (debounced search, instant on
         select/date change). The "Apply" button is kept only
         as a no-JS fallback and is visually de-emphasized.
       - Action badges are color-coded by verb so a reviewer can
         scan the "shape" of activity without reading every row.
       - Table collapses into a stacked card list under 700px.
       ========================================================= */

    .audit-page {
        color: var(--bs-body-color);
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
        font-size: .78rem;
        font-weight: 600;
        color: var(--bs-secondary-color);
    }

    .audit-title {
        font-size: 1.7rem;
        font-weight: 700;
        margin: .25rem 0 .35rem;
    }

    .audit-subtitle {
        color: var(--bs-secondary-color);
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
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-secondary-color);
        font-size: .78rem;
        font-weight: 600;
        white-space: nowrap;
    }

    [data-theme="dark"] .audit-readonly {
        background-color: #111827 !important;
        color: #9ca3af !important;
    }

    .audit-back-link {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        font-size: .85rem;
        font-weight: 600;
        color: var(--bs-secondary-color);
        text-decoration: none;
    }

    .audit-back-link:hover {
        color: var(--bs-body-color);
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
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
    }

    [data-theme="dark"] .audit-stat {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
    }

    .audit-stat-label {
        font-size: .74rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: var(--bs-secondary-color);
    }

    [data-theme="dark"] .audit-stat-label {
        color: #9ca3af !important;
    }

    .audit-stat-value {
        margin-top: .3rem;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }

    /* ---------- Filter bar (sticky, instant) ---------- */

    .audit-filter-bar {
        position: sticky;
        top: .5rem;
        z-index: 5;
        margin-bottom: 1.25rem;
        padding: .9rem 1rem;
        border-radius: 14px;
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        box-shadow: 0 4px 16px rgba(0, 0, 0, .05);
    }

    [data-theme="dark"] .audit-filter-bar {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .25);
    }

    .audit-search-row {
        display: flex;
        gap: .6rem;
        flex-wrap: wrap;
    }

    .audit-search-field {
        position: relative;
        flex: 1 1 260px;
        min-width: 220px;
    }

    .audit-search-field i.bi-search {
        position: absolute;
        left: .75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--bs-secondary-color);
        font-size: .9rem;
        pointer-events: none;
    }

    .audit-search-field input {
        width: 100%;
        min-height: 42px;
        padding-left: 2.1rem;
        padding-right: 2.1rem;
        border-radius: 9px;
    }

    .audit-search-clear {
        position: absolute;
        right: .5rem;
        top: 50%;
        transform: translateY(-50%);
        border: 0;
        background: transparent;
        color: var(--bs-secondary-color);
        line-height: 1;
        padding: .25rem;
        display: none;
    }

    .audit-search-field.has-value .audit-search-clear {
        display: inline-flex;
    }

    .audit-select-group {
        display: flex;
        gap: .6rem;
        flex-wrap: wrap;
    }

    .audit-select-group select,
    .audit-select-group input[type="date"] {
        min-height: 42px;
        border-radius: 9px;
    }

    [data-theme="dark"] .audit-filter-bar .form-control,
    [data-theme="dark"] .audit-filter-bar .form-select {
        background-color: #111827 !important;
        color: #f9fafb !important;
        border-color: #4b5563 !important;
    }

    [data-theme="dark"] .audit-filter-bar .form-control::placeholder {
        color: #9ca3af !important;
    }

    /* Fallback button — hidden once JS confirms auto-filtering works */

    .audit-apply-fallback {
        display: none;
    }

    .no-js .audit-apply-fallback {
        display: inline-flex;
    }

    /* Slim top-of-bar progress indicator while a filter reloads the page */

    .audit-loading .audit-filter-bar::after {
        content: '';
        position: absolute;
        left: 0;
        right: 0;
        bottom: -2px;
        height: 2px;
        border-radius: 2px;
        background: linear-gradient(90deg, transparent, #0d6efd, transparent);
        background-size: 200% 100%;
        animation: audit-scan 1s linear infinite;
    }

    .audit-filter-bar {
        position: sticky;
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
        margin-top: .75rem;
    }

    .audit-chip {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        padding: .3rem .6rem .3rem .75rem;
        border-radius: 999px;
        background-color: rgba(13, 110, 253, .1);
        color: #0d6efd;
        font-size: .78rem;
        font-weight: 600;
        text-decoration: none;
        white-space: nowrap;
    }

    .audit-chip i {
        font-size: .8rem;
    }

    [data-theme="dark"] .audit-chip {
        background-color: rgba(96, 165, 250, .15) !important;
        color: #93c5fd !important;
    }

    .audit-chip-clear {
        color: var(--bs-secondary-color);
        font-size: .78rem;
        font-weight: 600;
        text-decoration: underline;
    }

    /* ---------- Table card ---------- */

    .audit-card {
        background-color: var(--bs-body-bg);
        border: 1px solid var(--bs-border-color);
        border-radius: 14px;
        box-shadow: 0 4px 16px rgba(0, 0, 0, .05);
        overflow: hidden;
    }

    [data-theme="dark"] .audit-card {
        background-color: #1f2937 !important;
        border-color: #374151 !important;
        color: #f3f4f6 !important;
        box-shadow: 0 4px 18px rgba(0, 0, 0, .25);
    }

    .audit-card-header {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--bs-border-color);
    }

    [data-theme="dark"] .audit-card-header {
        border-bottom-color: #374151 !important;
    }

    .audit-card-title {
        font-weight: 700;
        margin: 0;
    }

    .audit-info {
        color: var(--bs-secondary-color);
        font-size: .83rem;
    }

    [data-theme="dark"] .audit-info {
        color: #9ca3af !important;
    }

    /* ---------- Table ---------- */

    .audit-table-wrapper {
        overflow-x: auto;
    }

    .audit-table {
        width: 100%;
        min-width: 980px;
        margin-bottom: 0;
        vertical-align: middle;
    }

    .audit-table th {
        padding: .8rem .85rem;
        font-size: .74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .045em;
        white-space: nowrap;
        background-color: var(--bs-tertiary-bg);
        color: var(--bs-body-color);
        border-bottom: 1px solid var(--bs-border-color);
    }

    .audit-table td {
        padding: .85rem .85rem;
        border-color: var(--bs-border-color);
        color: var(--bs-body-color);
    }

    .audit-table tbody tr:hover td {
        background-color: rgba(13, 110, 253, .035);
    }

    [data-theme="dark"] .audit-table th {
        background-color: #111827 !important;
        color: #f9fafb !important;
        border-color: #374151 !important;
    }

    [data-theme="dark"] .audit-table td {
        background-color: #1f2937 !important;
        color: #e5e7eb !important;
        border-color: #374151 !important;
    }

    [data-theme="dark"] .audit-table tbody tr:hover td {
        background-color: #263244 !important;
    }

    .audit-date {
        width: 125px;
        white-space: nowrap;
    }

    .audit-date-main {
        font-weight: 600;
    }

    .audit-date-time {
        margin-top: .15rem;
        font-size: .75rem;
        color: var(--bs-secondary-color);
    }

    [data-theme="dark"] .audit-date-time {
        color: #9ca3af !important;
    }

    .audit-user {
        min-width: 135px;
    }

    .audit-user-name {
        font-weight: 600;
        white-space: nowrap;
    }

    .audit-user-role {
        margin-top: .15rem;
        font-size: .75rem;
        color: var(--bs-secondary-color);
        text-transform: capitalize;
    }

    [data-theme="dark"] .audit-user-role {
        color: #9ca3af !important;
    }

    /* ---------- Action badges (color-coded by verb) ---------- */

    .audit-action-badge {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        border-radius: 999px;
        padding: .32rem .65rem;
        font-size: .73rem;
        font-weight: 700;
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

    .audit-action-badge.is-create {
        background-color: rgba(25, 135, 84, .12);
        color: #198754;
    }

    .audit-action-badge.is-update {
        background-color: rgba(13, 110, 253, .1);
        color: #0d6efd;
    }

    .audit-action-badge.is-delete {
        background-color: rgba(220, 53, 69, .12);
        color: #dc3545;
    }

    .audit-action-badge.is-view {
        background-color: rgba(108, 117, 125, .14);
        color: #6c757d;
    }

    .audit-action-badge.is-auth {
        background-color: rgba(255, 193, 7, .18);
        color: #a1670a;
    }

    [data-theme="dark"] .audit-action-badge.is-create {
        background-color: rgba(52, 211, 153, .15) !important;
        color: #6ee7b7 !important;
    }

    [data-theme="dark"] .audit-action-badge.is-update {
        background-color: rgba(96, 165, 250, .15) !important;
        color: #93c5fd !important;
    }

    [data-theme="dark"] .audit-action-badge.is-delete {
        background-color: rgba(248, 113, 113, .15) !important;
        color: #fca5a5 !important;
    }

    [data-theme="dark"] .audit-action-badge.is-view {
        background-color: rgba(156, 163, 175, .15) !important;
        color: #d1d5db !important;
    }

    [data-theme="dark"] .audit-action-badge.is-auth {
        background-color: rgba(250, 204, 21, .18) !important;
        color: #fde68a !important;
    }

    .audit-module-badge {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .32rem .65rem;
        font-size: .73rem;
        font-weight: 700;
        line-height: 1.2;
        white-space: nowrap;
        background-color: rgba(108, 117, 125, .12);
        color: #495057;
    }

    [data-theme="dark"] .audit-module-badge {
        background-color: rgba(156, 163, 175, .15) !important;
        color: #d1d5db !important;
    }

    .audit-record {
        font-family: Consolas, Monaco, monospace;
        font-size: .8rem;
        font-weight: 600;
        word-break: break-word;
    }

    .audit-record-id {
        display: block;
        margin-top: .2rem;
        font-size: .72rem;
        color: var(--bs-secondary-color);
    }

    [data-theme="dark"] .audit-record-id {
        color: #9ca3af !important;
    }

    .audit-description {
        min-width: 260px;
        max-width: 460px;
        line-height: 1.5;
        word-break: break-word;
    }

    .audit-description mark {
        padding: 0 .12em;
        border-radius: 3px;
        background-color: rgba(255, 193, 7, .35);
        color: inherit;
    }

    [data-theme="dark"] .audit-description mark {
        background-color: rgba(250, 204, 21, .3);
    }

    /* ---------- Empty state ---------- */

    .audit-empty {
        padding: 3.5rem 1rem;
        text-align: center;
        color: var(--bs-secondary-color);
    }

    [data-theme="dark"] .audit-empty {
        color: #9ca3af !important;
    }

    .audit-empty-icon {
        font-size: 2.5rem;
        margin-bottom: .75rem;
        opacity: .65;
    }

    .audit-empty .btn {
        margin-top: .5rem;
    }

    /* ---------- Pagination ---------- */

    .audit-pagination {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--bs-border-color);
    }

    [data-theme="dark"] .audit-pagination {
        border-top-color: #374151 !important;
    }

    .audit-pagination .pagination {
        margin-bottom: 0;
    }

    [data-theme="dark"] .audit-pagination .page-link {
        background-color: #111827 !important;
        border-color: #374151 !important;
        color: #d1d5db !important;
    }

    [data-theme="dark"] .audit-pagination .page-item.active .page-link {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        color: #fff !important;
    }

    [data-theme="dark"] .audit-pagination .page-item.disabled .page-link {
        background-color: #1f2937 !important;
        color: #6b7280 !important;
    }

    /* ---------- Print-only header ---------- */

    .audit-print-header {
        display: none;
    }

    /* ---------- Responsive: stacked cards under 700px ---------- */

    @media (max-width: 992px) {

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
            border: 1px solid var(--bs-border-color);
            border-radius: 12px;
        }

        [data-theme="dark"] .audit-table tbody tr {
            border-color: #374151 !important;
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
            font-size: .72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            color: var(--bs-secondary-color);
            text-align: left;
        }

        .audit-description,
        .audit-record {
            max-width: 100%;
            text-align: right;
        }
    }

    /* =========================================================
       PRINT STYLES
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
        }

        .audit-description mark {
            background: transparent !important;
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

        [data-theme="dark"] .audit-card,
        [data-theme="dark"] .audit-table td,
        [data-theme="dark"] .audit-table th {
            background: #fff !important;
            color: #000 !important;
            border-color: #999 !important;
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
                    <i class="bi bi-x-circle-fill"></i>
                </button>

            </div>

            <div class="audit-select-group">

                <select name="module" id="module" class="form-select" style="min-width: 150px">
                    <option value="">All modules</option>
                    @foreach($modules as $moduleOption)
                        <option value="{{ $moduleOption }}" @selected($module === $moduleOption)>
                            {{ $moduleOption }}
                        </option>
                    @endforeach
                </select>

                <select name="action" id="action" class="form-select" style="min-width: 140px">
                    <option value="">All actions</option>
                    @foreach($actions as $actionOption)
                        <option value="{{ $actionOption }}" @selected($action === $actionOption)>
                            {{ $actionOption }}
                        </option>
                    @endforeach
                </select>

                <select name="user_id" id="user_id" class="form-select" style="min-width: 150px">
                    <option value="">All users</option>
                    @foreach($users as $userOption)
                        <option value="{{ $userOption->id }}" @selected((string) $userId === (string) $userOption->id)>
                            {{ $userOption->display_name }}
                        </option>
                    @endforeach
                </select>

                <input type="date" name="date_from" id="date_from" class="form-control" value="{{ $dateFrom }}" title="From date">
                <input type="date" name="date_to" id="date_to" class="form-control" value="{{ $dateTo }}" title="To date">

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
                                    '/(' . preg_quote($search, '/') . ')/i',
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
                    No activity matches "{{ $search }}" or the selected filters.
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
