@extends('layouts.app')

@section('title', 'Masterlist Preview | e-Poblacion')

@section('content')

@php
    // ============================================================
    // SAFE DATA
    // ============================================================

    $operation = $operation ?? null;

    if (!$operation) {
        abort(404);
    }

    $masterlistRecords = $masterlistRecords ?? collect();
    $filter = $filter ?? 'all';
    $purok = $purok ?? '';
    $search = $search ?? '';

    $filterOptions = $filterOptions ?? [
        'all' => 'All',
        'household_heads' => 'Household Heads',
        'students' => 'Students',
        'senior_citizens' => 'Senior Citizens',
    ];

    $filterLabel = $filterLabel ?? ($filterOptions[$filter] ?? 'All');

    $operationCode = $operation->operation_code
        ?? ('RA-' . str_pad($operation->id, 4, '0', STR_PAD_LEFT));

    $operationName = $operation->operation_name
        ?? 'Relief Assistance Operation';

    $recordCount = $masterlistRecords->count();

    $targetGroupDescription = match ($filter) {
        'household_heads' => 'Residents recorded as household heads',
        'students' => 'Residents with occupation recorded as Student',
        'senior_citizens' => 'Residents aged 60 and above',
        default => 'All matching active resident records',
    };

    $masterlistUrl = route(
        'relief-assistance.masterlist',
        array_filter(
            [
                'operation_id' => $operation->id,
                'filter' => $filter,
                'purok' => $purok,
                'search' => $search,
            ],
            fn ($value) => $value !== null && $value !== ''
        )
    );

    $joinName = function ($person) {
        return trim(
            collect([
                data_get($person, 'first_name'),
                data_get($person, 'middle_name'),
                data_get($person, 'last_name'),
                data_get($person, 'suffix'),
            ])
            ->filter(fn ($value) => filled($value))
            ->implode(' ')
        );
    };
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">


{{-- ============================================================
     SCREEN-ONLY STYLES (toolbar + paper stage)
     Theme aware: all colors come from variables that are
     redefined for dark mode (same selectors the rest of the
     project uses: html[data-theme="dark"]).
============================================================ --}}
<style>

    .mp {
        --ink: #1c1b33;
        --ink-2: #5e5c78;
        --card: #ffffff;
        --line: #d9d6e8;
        --wash: #f5f4fa;
        --ube: #5a3fd6;
        --ube-soft: #ebe7fb;
        --mango: #ffb020;

        --btn-bg: #5a3fd6;
        --btn-fg: #ffffff;
        --btn-hover: #1c1b33;
        --btn-off: #cfcce0;
        --btn-off-fg: #5e5c78;

        --paper-shadow: 0 6px 24px rgba(28, 27, 51, .12);

        max-width: 1500px;
        margin: 0 auto;
        color: var(--ink);
        font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
    }

    html[data-theme="dark"] .mp,
    html[data-bs-theme="dark"] .mp {
        --ink: #eef0fa;
        --ink-2: #b9bcd6;
        --card: #232338;
        --line: #3d3d5a;
        --wash: #171724;
        --ube: #b3a2ff;
        --ube-soft: #2d2852;
        --mango: #ffc247;

        --btn-bg: #b3a2ff;
        --btn-fg: #17112f;
        --btn-hover: #d6ccff;
        --btn-off: #33334a;
        --btn-off-fg: #9a9db8;

        --paper-shadow: 0 6px 24px rgba(0, 0, 0, .55);

        color-scheme: dark;
    }

    .mp-bar { margin-bottom: 20px; }

    .mp-back {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
        font-size: 13px;
        font-weight: 600;
        color: var(--ink-2);
        text-decoration: none;
    }

    .mp-back:hover {
        color: var(--ube);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .mp-bar-row {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 18px;
    }

    .mp-title {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px 12px;
        margin: 0;
        color: var(--ink);
        font-family: 'Unbounded', 'Figtree', sans-serif;
        letter-spacing: -.02em;
        font-size: 26px;
        font-weight: 800;
        line-height: 1.15;
    }

    .mp-tag {
        padding: 4px 12px;
        border-radius: 999px;
        background: var(--ube-soft);
        color: var(--ube);
        font-family: 'Figtree', sans-serif;
        letter-spacing: 0;
        font-size: 12px;
        font-weight: 700;
    }

    .mp-sub {
        margin: 10px 0 0;
        max-width: 700px;
        font-size: 14px;
        line-height: 1.6;
        color: var(--ink-2);
    }

    .mp-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        margin-top: 14px;
    }

    .mp-chip {
        padding: 5px 12px;
        border: 1px solid var(--line);
        border-radius: 999px;
        background: var(--card);
        color: var(--ink);
        font-size: 12.5px;
        font-weight: 600;
    }

    .mp-chip b { font-weight: 700; color: var(--ink); }

    .mp-print-settings {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: 10px;
    }

    .mp-paper-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--ink-2);
    }

    .mp-paper-select {
        min-width: 200px;
        padding: 11px 38px 11px 13px;
        border: 1px solid var(--line);
        border-radius: 12px;
        background-color: var(--card);
        color: var(--ink);
        font: inherit;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .mp-paper-select option {
        background: var(--card);
        color: var(--ink);
    }

    .mp-paper-select:focus {
        outline: 3px solid rgba(138, 116, 255, .35);
        border-color: var(--ube);
    }

    .mp-print {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 13px 24px;
        border: 0;
        border-radius: 999px;
        background: var(--btn-bg);
        color: var(--btn-fg);
        font: inherit;
        font-size: 14.5px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s, transform .15s;
    }

    .mp-print:hover { background: var(--btn-hover); }
    .mp-print:active { transform: scale(.98); }

    .mp-print:disabled {
        background: var(--btn-off);
        color: var(--btn-off-fg);
        cursor: not-allowed;
    }

    .mp-print:focus-visible,
    .mp-back:focus-visible {
        outline: 3px solid var(--mango);
        outline-offset: 2px;
    }

    .mp-hint {
        margin: 0 0 12px;
        font-size: 12.5px;
        color: var(--ink-2);
    }

    .mp-hint strong { color: var(--ink); }

    .mp-stage {
        padding: 28px;
        border-radius: 20px;
        background: var(--wash);
        border: 1px solid var(--line);
        overflow-x: auto;
    }

    /*
     * Screen preview = real paper width (portrait), so what you see is
     * what prints. The paper itself is always white (see document styles).
     */
    .mp-stage #masterlistPrintDocument {
        margin: 0 auto;
        padding: 8mm;
        width: 210mm;
        box-shadow: var(--paper-shadow);
    }

    .mp-stage #masterlistPrintDocument.paper-a4    { width: 210mm; }
    .mp-stage #masterlistPrintDocument.paper-short { width: 8.5in; }
    .mp-stage #masterlistPrintDocument.paper-long  { width: 8.5in; }

    @media (max-width: 767.98px) {
        .mp-title { font-size: 21px; }
        .mp-print { width: 100%; }
        .mp-print-settings { width: 100%; justify-content: stretch; }
        .mp-paper-select { flex: 1; min-width: 0; }
        .mp-stage { padding: 12px; }
    }

    /*
     * The real printing is done from an isolated iframe (see script), so
     * the app layout can never leak into the printout. This is only a
     * safety net in case someone uses the browser's own Print menu.
     */
    @media print {
        .mp-bar,
        .mp-hint { display: none !important; }

        .mp-stage {
            padding: 0 !important;
            border: 0 !important;
            background: #fff !important;
            overflow: visible !important;
        }

        .mp-stage #masterlistPrintDocument {
            width: auto !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
    }

</style>


{{-- ============================================================
     DOCUMENT STYLES
     Used by BOTH the on-screen preview and the print iframe.
     Everything is anchored to #masterlistPrintDocument and sets
     explicit colors, so neither the dark theme nor Bootstrap can
     change the paper. Sized in pt/mm so it scales on A4, Short
     and Long bond paper.
============================================================ --}}
<style id="masterlistDocStyles">

    #masterlistPrintDocument {
        color: #111;
        background: #fff;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        line-height: 1.3;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* Every descendant inherits the paper's colors; app/theme rules cannot override this. */
    #masterlistPrintDocument *,
    #masterlistPrintDocument *::before,
    #masterlistPrintDocument *::after {
        box-sizing: border-box;
        color: inherit;
        background-color: transparent;
    }

    #masterlistPrintDocument img {
        filter: none;
        opacity: 1;
    }

    /* ---------- OFFICIAL HEADER ---------- */

    #masterlistPrintDocument .official-header {
        display: grid;
        grid-template-columns: 18mm 1fr 18mm;
        align-items: center;
        gap: 4mm;
        text-align: center;
        break-inside: avoid;
    }

    #masterlistPrintDocument .header-logo {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #masterlistPrintDocument .header-logo img {
        width: 16mm;
        height: 16mm;
        object-fit: contain;
    }

    #masterlistPrintDocument .header-center { line-height: 1.2; }

    #masterlistPrintDocument .republic-text,
    #masterlistPrintDocument .province-text,
    #masterlistPrintDocument .municipality-text {
        font-size: 7.5pt;
        font-weight: 600;
    }

    #masterlistPrintDocument .barangay-text {
        margin-top: 1mm;
        font-size: 11pt;
        font-weight: 800;
        letter-spacing: .5pt;
    }

    #masterlistPrintDocument .document-title {
        margin-top: 1.5mm;
        font-size: 11.5pt;
        font-weight: 800;
        letter-spacing: .4pt;
    }

    #masterlistPrintDocument .header-line {
        margin: 2.5mm 0 3mm;
        border-bottom: 3px double #111;
    }

    /* ---------- OPERATION DETAILS ---------- */

    #masterlistPrintDocument .operation-box {
        margin-bottom: 3mm;
        border: 1px solid #333;
        break-inside: avoid;
    }

    #masterlistPrintDocument .operation-heading {
        padding: 1.2mm 2.5mm;
        background: #ececec;
        border-bottom: 1px solid #333;
        font-size: 7.5pt;
        font-weight: 800;
        letter-spacing: .4pt;
    }

    #masterlistPrintDocument .operation-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
    }

    #masterlistPrintDocument .operation-field {
        padding: 1.5mm 2.5mm;
        border-right: 1px solid #bbb;
        min-width: 0;
    }

    #masterlistPrintDocument .operation-field:nth-child(3n) { border-right: 0; }
    #masterlistPrintDocument .operation-field:nth-child(-n+3) { border-bottom: 1px solid #bbb; }

    #masterlistPrintDocument .field-label {
        display: block;
        margin-bottom: .6mm;
        font-size: 5.8pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3pt;
        color: #555;
    }

    #masterlistPrintDocument .field-value {
        display: block;
        font-size: 8pt;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    /* ---------- PURPOSE + INSTRUCTIONS ---------- */

    #masterlistPrintDocument .info-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 3mm;
        margin-bottom: 3mm;
    }

    #masterlistPrintDocument .purpose-box,
    #masterlistPrintDocument .instruction-box {
        border: 1px solid #555;
        padding: 2mm 2.6mm;
        font-size: 6.6pt;
        line-height: 1.35;
        break-inside: avoid;
    }

    #masterlistPrintDocument .purpose-title,
    #masterlistPrintDocument .instruction-title {
        margin-bottom: 1mm;
        font-size: 7pt;
        font-weight: 800;
        text-decoration: underline;
    }

    #masterlistPrintDocument .purpose-box p { margin: 0 0 1.2mm; }
    #masterlistPrintDocument .purpose-box p:last-child { margin-bottom: 0; }

    #masterlistPrintDocument .instruction-box ol {
        margin: 0;
        padding-left: 4.2mm;
    }

    #masterlistPrintDocument .instruction-box li { margin-bottom: .3mm; }

    /* ---------- FILTER SUMMARY ---------- */

    #masterlistPrintDocument .filter-summary {
        display: flex;
        flex-wrap: wrap;
        gap: 1mm 6mm;
        margin-bottom: 3mm;
        padding: 1.6mm 2.6mm;
        border: 1px solid #777;
        background: #fafafa;
        font-size: 7pt;
        break-inside: avoid;
    }

    /* ---------- MASTERLIST TABLE ---------- */

    #masterlistPrintDocument .masterlist-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 6.6pt;
    }

    #masterlistPrintDocument .masterlist-table th,
    #masterlistPrintDocument .masterlist-table td {
        padding: 1.2mm 1.2mm;
        border: 1px solid #333;
        vertical-align: top;
        overflow-wrap: anywhere;
        background: #fff;
    }

    #masterlistPrintDocument .masterlist-table thead { display: table-header-group; }

    #masterlistPrintDocument .masterlist-table thead th {
        background: #e6e6e6;
        text-align: center;
        vertical-align: middle;
        font-size: 6pt;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0;
    }

    #masterlistPrintDocument .masterlist-table tr {
        break-inside: avoid;
        page-break-inside: avoid;
    }

    #masterlistPrintDocument .masterlist-table tbody tr:nth-child(even) td {
        background: #fbfbfb;
    }

    #masterlistPrintDocument .number-column       { width: 4%; }
    #masterlistPrintDocument .resident-column     { width: 15%; }
    #masterlistPrintDocument .age-column          { width: 4%; }
    #masterlistPrintDocument .relationship-column { width: 8%; }
    #masterlistPrintDocument .household-column    { width: 9%; }
    #masterlistPrintDocument .head-column         { width: 12%; }
    #masterlistPrintDocument .purok-column        { width: 6%; }
    #masterlistPrintDocument .address-column      { width: 12%; }
    #masterlistPrintDocument .assessment-column   { width: 19%; }
    #masterlistPrintDocument .remarks-column      { width: 11%; }

    #masterlistPrintDocument .number-cell,
    #masterlistPrintDocument .age-cell,
    #masterlistPrintDocument .purok-cell {
        vertical-align: middle !important;
        text-align: center;
    }

    #masterlistPrintDocument .resident-name {
        font-size: 7pt;
        font-weight: 800;
        line-height: 1.2;
    }

    #masterlistPrintDocument .resident-category {
        margin-top: .5mm;
        font-size: 5.8pt;
        font-style: italic;
        color: #555;
    }

    #masterlistPrintDocument .household-id {
        margin-top: .4mm;
        font-size: 5.8pt;
        color: #555;
    }

    #masterlistPrintDocument .assessment-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.2mm 1.5mm;
    }

    #masterlistPrintDocument .assessment-option {
        display: flex;
        align-items: center;
        gap: 1mm;
        font-size: 5.6pt;
        line-height: 1.15;
    }

    #masterlistPrintDocument .checkbox {
        flex: 0 0 auto;
        display: inline-block;
        width: 2.4mm;
        height: 2.4mm;
        border: 1px solid #111;
        background: #fff;
    }

    #masterlistPrintDocument .remarks-lines {
        min-height: 11mm;
        background: repeating-linear-gradient(
            to bottom,
            transparent 0,
            transparent 3.4mm,
            #aaa 3.4mm,
            #aaa 3.5mm
        );
    }

    /* ---------- EMPTY ---------- */

    #masterlistPrintDocument .doc-empty {
        padding: 8mm;
        border: 1px solid #555;
        text-align: center;
        font-weight: 700;
    }

    #masterlistPrintDocument .doc-empty-title {
        margin-bottom: 1.5mm;
        font-size: 10pt;
    }

    #masterlistPrintDocument .doc-empty-text {
        max-width: 160mm;
        margin: 0 auto;
        font-size: 8pt;
        font-weight: 400;
        line-height: 1.5;
    }

    /* ---------- CLOSING BLOCK (kept together on one page) ---------- */

    #masterlistPrintDocument .closing {
        margin-top: 4mm;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    #masterlistPrintDocument .summary-box {
        border: 1px solid #333;
    }

    #masterlistPrintDocument .summary-title {
        padding: 1.2mm 2.5mm;
        background: #ececec;
        border-bottom: 1px solid #333;
        font-size: 7.5pt;
        font-weight: 800;
    }

    #masterlistPrintDocument .summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
    }

    #masterlistPrintDocument .summary-item {
        padding: 1.6mm 2.5mm;
        border-right: 1px solid #bbb;
    }

    #masterlistPrintDocument .summary-item:last-child { border-right: 0; }

    #masterlistPrintDocument .summary-label {
        display: block;
        margin-bottom: 1mm;
        font-size: 5.8pt;
        font-weight: 700;
        text-transform: uppercase;
        color: #555;
    }

    #masterlistPrintDocument .summary-line {
        display: block;
        min-height: 4.5mm;
        border-bottom: 1px solid #222;
        font-size: 8.5pt;
        font-weight: 700;
    }

    #masterlistPrintDocument .certification-box {
        margin-top: 3mm;
        padding: 2.2mm 2.8mm;
        border: 1px solid #333;
    }

    #masterlistPrintDocument .certification-title {
        margin-bottom: 1.2mm;
        font-size: 7.5pt;
        font-weight: 800;
    }

    #masterlistPrintDocument .certification-box p {
        margin: 0 0 1.5mm;
        font-size: 6.6pt;
        line-height: 1.4;
        text-align: justify;
    }

    #masterlistPrintDocument .signature-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10mm;
        margin-top: 2mm;
    }

    #masterlistPrintDocument .signature-block { text-align: center; }

    #masterlistPrintDocument .signature-space { height: 8mm; }

    #masterlistPrintDocument .signature-line {
        margin-bottom: 1mm;
        border-bottom: 1px solid #111;
    }

    #masterlistPrintDocument .signature-label {
        font-size: 7pt;
        font-weight: 800;
    }

    #masterlistPrintDocument .signature-date {
        margin-top: 1.4mm;
        font-size: 6.6pt;
        text-align: left;
    }

    #masterlistPrintDocument .final-notice {
        margin-top: 3mm;
        padding: 1.8mm 2.6mm;
        border: 1px solid #555;
        font-size: 6.2pt;
        line-height: 1.4;
    }

    #masterlistPrintDocument .document-footer {
        display: flex;
        justify-content: space-between;
        gap: 4mm;
        margin-top: 3mm;
        padding-top: 1.5mm;
        border-top: 1px solid #555;
        font-size: 6pt;
        color: #444;
    }

</style>


<div class="container-fluid py-4 mp">

    {{-- =========================================================
         SCREEN TOOLBAR
    ========================================================== --}}

    <div class="mp-bar d-print-none">

        <a href="{{ $masterlistUrl }}" class="mp-back">
            <i class="bi bi-arrow-left"></i>
            Back to masterlist
        </a>

        <div class="mp-bar-row">

            <div>

                <h1 class="mp-title">
                    Masterlist preview
                    <span class="mp-tag">Review copy</span>
                </h1>

                <p class="mp-sub">
                    Review the field assessment masterlist before printing.
                    The document prints in portrait and continues across as
                    many pages as needed on A4, short bond, or long bond paper.
                </p>

                <div class="mp-chips">

                    <span class="mp-chip"><b>{{ $operationCode }}</b></span>

                    <span class="mp-chip">
                        Group: <b>{{ $filterLabel }}</b>
                    </span>

                    <span class="mp-chip">
                        Purok: <b>{{ $purok !== '' ? $purok : 'All' }}</b>
                    </span>

                    @if ($search !== '')
                        <span class="mp-chip">
                            Search: <b>{{ $search }}</b>
                        </span>
                    @endif

                    <span class="mp-chip">
                        <b>{{ number_format($recordCount) }}</b>
                        {{ $recordCount === 1 ? 'record' : 'records' }}
                    </span>

                </div>

            </div>


            <div class="mp-print-settings">

                <label for="paperSize" class="mp-paper-label">
                    Paper size
                </label>

                <select
                    id="paperSize"
                    class="mp-paper-select"
                    onchange="changePaperSize(this.value)"
                >
                    <option value="a4">A4 — 210 × 297 mm</option>
                    <option value="short">Short Bond — 8.5 × 11 in</option>
                    <option value="long">Long Bond — 8.5 × 13 in</option>
                </select>

                <button
                    type="button"
                    class="mp-print"
                    onclick="printMasterlist()"
                    {{ $recordCount > 0 ? '' : 'disabled' }}
                >
                    <i class="bi bi-printer-fill"></i>
                    Print masterlist
                </button>

            </div>

        </div>

    </div>


    <p class="mp-hint d-print-none">
        The preview below is shown at the real portrait paper width for the
        selected size. The paper stays white in both light and dark mode, and
        prints the same way. In the print dialog, keep <strong>Margins: Default</strong>
        and <strong>Scale: 100%</strong>.
    </p>


    {{-- =========================================================
         OFFICIAL PRINTABLE DOCUMENT
    ========================================================== --}}

    <div class="mp-stage">

        <div id="masterlistPrintDocument" class="print-document paper-a4">

            {{-- HEADER --}}
            <div class="official-header">

                <div class="header-logo">
                    <img src="{{ asset('icon.png') }}" alt="Barangay Poblacion Logo">
                </div>

                <div class="header-center">
                    <div class="republic-text">REPUBLIC OF THE PHILIPPINES</div>
                    <div class="province-text">PROVINCE OF PANGASINAN</div>
                    <div class="municipality-text">MUNICIPALITY OF BANI</div>
                    <div class="barangay-text">BARANGAY POBLACION</div>
                    <div class="document-title">
                        RELIEF ASSISTANCE FIELD ASSESSMENT MASTERLIST
                    </div>
                </div>

                <div class="header-logo">
                    <img src="{{ asset('Bani.png') }}" alt="Municipality of Bani Logo">
                </div>

            </div>

            <div class="header-line"></div>


            {{-- OPERATION DETAILS --}}
            <div class="operation-box">

                <div class="operation-heading">RELIEF OPERATION DETAILS</div>

                <div class="operation-grid">

                    <div class="operation-field">
                        <span class="field-label">Operation Name</span>
                        <span class="field-value">{{ $operationName }}</span>
                    </div>

                    <div class="operation-field">
                        <span class="field-label">Operation Code</span>
                        <span class="field-value">{{ $operationCode }}</span>
                    </div>

                    <div class="operation-field">
                        <span class="field-label">Assistance Type</span>
                        <span class="field-value">
                            {{ $operation->assistance_type
                                ? \Illuminate\Support\Str::headline($operation->assistance_type)
                                : '—' }}
                        </span>
                    </div>

                    <div class="operation-field">
                        <span class="field-label">Target Group</span>
                        <span class="field-value">{{ $filterLabel }}</span>
                    </div>

                    <div class="operation-field">
                        <span class="field-label">Purok</span>
                        <span class="field-value">{{ $purok !== '' ? $purok : 'All Puroks' }}</span>
                    </div>

                    <div class="operation-field">
                        <span class="field-label">Records Listed</span>
                        <span class="field-value">{{ number_format($recordCount) }}</span>
                    </div>

                </div>

            </div>


            {{-- PURPOSE + INSTRUCTIONS --}}
            <div class="info-row">

                <div class="purpose-box">

                    <div class="purpose-title">PURPOSE OF THIS MASTERLIST</div>

                    <p>
                        This printed masterlist is a field-assessment reference
                        generated from existing Barangay Poblacion records. It
                        identifies residents matching the selected database filter
                        together with their household information so that
                        BHW/Staff can locate and verify the appropriate household
                        during the house-to-house assessment.
                    </p>

                    <p>
                        <strong>This list is for field verification only.</strong>
                        Inclusion in this list does not constitute automatic
                        eligibility, approval, or entitlement to relief assistance.
                        Final beneficiary selection will be based on the completed
                        field assessment and authorized barangay review.
                    </p>

                </div>

                <div class="instruction-box">

                    <div class="instruction-title">FIELD ASSESSMENT INSTRUCTIONS</div>

                    <ol>
                        <li>Use the resident and household information on this masterlist to locate the corresponding household.</li>
                        <li>Conduct the actual house-to-house verification and assessment.</li>
                        <li>Confirm the resident and household information during the field visit.</li>
                        <li>Mark the appropriate result in the <strong>Field Assessment</strong> column.</li>
                        <li>Use the <strong>Remarks</strong> column for corrections, observations, or information requiring follow-up.</li>
                        <li>Return the completed masterlist to the Secretary or other authorized barangay personnel.</li>
                        <li>After review, authorized personnel will open <strong>Beneficiary Encoding</strong> in the system and select the actual recipients based on the completed field assessment.</li>
                    </ol>

                </div>

            </div>


            {{-- FILTER SUMMARY --}}
            <div class="filter-summary">

                <div><strong>Target Group:</strong> {{ $filterLabel }}</div>
                <div><strong>Criteria:</strong> {{ $targetGroupDescription }}</div>
                <div><strong>Purok:</strong> {{ $purok !== '' ? $purok : 'All Puroks' }}</div>

                @if ($search !== '')
                    <div><strong>Search:</strong> {{ $search }}</div>
                @endif

                <div><strong>Total Records:</strong> {{ number_format($recordCount) }}</div>
                <div><strong>Generated:</strong> {{ now()->format('F d, Y h:i A') }}</div>

            </div>


            {{-- MASTERLIST --}}
            @if ($masterlistRecords->isNotEmpty())

                <table class="masterlist-table">

                    <thead>
                        <tr>
                            <th class="number-column">No.</th>
                            <th class="resident-column">Target Resident</th>
                            <th class="age-column">Age</th>
                            <th class="relationship-column">Relationship</th>
                            <th class="household-column">Household No.</th>
                            <th class="head-column">Household Head</th>
                            <th class="purok-column">Purok</th>
                            <th class="address-column">Address</th>
                            <th class="assessment-column">Field Assessment</th>
                            <th class="remarks-column">Remarks</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($masterlistRecords as $index => $record)

                            @php
                                $resident = data_get($record, 'resident');
                                $household = data_get($record, 'household');
                                $householdHead = data_get($record, 'household_head');

                                $residentName = $joinName($resident);

                                $householdHeadName = $householdHead
                                    ? $joinName($householdHead)
                                    : 'No household head recorded';

                                $householdNumber = data_get($record, 'household_number')
                                    ?? data_get($household, 'household_number');

                                $householdId = data_get($record, 'household_id')
                                    ?? data_get($household, 'id');

                                $purokName = data_get($record, 'purok')
                                    ?? data_get($household, 'purok');

                                $address = data_get($record, 'address')
                                    ?? data_get($household, 'address');

                                $relationship = data_get($record, 'relationship') ?: '—';

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

                                <td class="number-cell">{{ $index + 1 }}</td>

                                <td>
                                    <div class="resident-name">{{ $residentName ?: '—' }}</div>
                                    <div class="resident-category">{{ $filterLabel }}</div>
                                </td>

                                <td class="age-cell">{{ $age !== null ? $age : '—' }}</td>

                                <td>{{ $relationship }}</td>

                                <td>
                                    <strong>{{ $householdNumber ?: '—' }}</strong>
                                    @if ($householdId)
                                        <div class="household-id">ID: {{ $householdId }}</div>
                                    @endif
                                </td>

                                <td>{{ $householdHeadName }}</td>

                                <td class="purok-cell">{{ $purokName ?: '—' }}</td>

                                <td>{{ $address ?: '—' }}</td>

                                <td>
                                    <div class="assessment-grid">
                                        <div class="assessment-option"><span class="checkbox"></span> Verified / Found</div>
                                        <div class="assessment-option"><span class="checkbox"></span> Selected</div>
                                        <div class="assessment-option"><span class="checkbox"></span> Not Found</div>
                                        <div class="assessment-option"><span class="checkbox"></span> Not Selected</div>
                                        <div class="assessment-option"><span class="checkbox"></span> For Review</div>
                                    </div>
                                </td>

                                <td><div class="remarks-lines"></div></td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            @else

                <div class="doc-empty">
                    <div class="doc-empty-title">No Matching Records</div>
                    <div class="doc-empty-text">
                        No active resident records matched the selected target group,
                        Purok, or search criteria. Return to the Masterlist Workspace
                        and adjust the filters before printing.
                    </div>
                </div>

            @endif


            {{-- CLOSING BLOCK: summary + certification + notice + footer --}}
            <div class="closing">

                <div class="summary-box">

                    <div class="summary-title">FIELD ASSESSMENT SUMMARY</div>

                    <div class="summary-grid">

                        <div class="summary-item">
                            <span class="summary-label">Total Records Listed</span>
                            <span class="summary-line">{{ number_format($recordCount) }}</span>
                        </div>

                        <div class="summary-item">
                            <span class="summary-label">Verified / Found</span>
                            <span class="summary-line"></span>
                        </div>

                        <div class="summary-item">
                            <span class="summary-label">Selected for Assistance</span>
                            <span class="summary-line"></span>
                        </div>

                        <div class="summary-item">
                            <span class="summary-label">For Review / Follow-up</span>
                            <span class="summary-line"></span>
                        </div>

                    </div>

                </div>


                <div class="certification-box">

                    <div class="certification-title">FIELD ASSESSMENT CERTIFICATION</div>

                    <p>
                        I certify that the entries marked on this masterlist are based on
                        the actual field verification/assessment conducted by the assigned
                        BHW/Staff. The information was checked against the corresponding
                        resident and household details available during the field visit.
                        I understand that the field assessment does not by itself constitute
                        final approval. The completed masterlist shall be submitted to the
                        Secretary or other authorized barangay personnel for review and
                        encoding of the actual selected beneficiaries.
                    </p>

                    <div class="signature-grid">

                        <div class="signature-block">
                            <div class="signature-space"></div>
                            <div class="signature-line"></div>
                            <div class="signature-label">BHW / FIELD ASSESSOR</div>
                            <div class="signature-date">Name: ______________________________________</div>
                            <div class="signature-date">Date: ______________________________________</div>
                        </div>

                        <div class="signature-block">
                            <div class="signature-space"></div>
                            <div class="signature-line"></div>
                            <div class="signature-label">RECEIVED / REVIEWED BY</div>
                            <div class="signature-date">Name: ______________________________________</div>
                            <div class="signature-date">Date: ______________________________________</div>
                        </div>

                    </div>

                </div>


                <div class="final-notice">
                    <strong>IMPORTANT NOTICE:</strong>
                    This masterlist is generated from existing barangay records using the
                    selected target group, Purok, and/or search criteria. The filters are
                    used only to identify records for field assessment. They do not
                    automatically determine eligibility or approval for relief assistance.
                    Final beneficiary selection remains subject to actual field verification
                    and authorized barangay review.
                </div>


                <div class="document-footer">
                    <div>Barangay Poblacion, Municipality of Bani, Pangasinan</div>
                    <div>Relief Assistance Field Assessment Masterlist &mdash; {{ $operationCode }}</div>
                    <div>Generated: {{ now()->format('M d, Y') }}</div>
                </div>

            </div>

        </div>

    </div>

</div>


<script>

    /*
     |--------------------------------------------------------------------------
     | PAPER DEFINITIONS (all portrait)
     |--------------------------------------------------------------------------
     */

    const PAPER_SIZES = {
        a4:    { className: 'paper-a4',    pageSize: 'A4 portrait', label: 'A4' },
        short: { className: 'paper-short', pageSize: '8.5in 11in',   label: 'Short Bond' },
        long:  { className: 'paper-long',  pageSize: '8.5in 13in',   label: 'Long Bond' }
    };

    const PRINT_TITLE = @json($operationCode . ' - Field Assessment Masterlist');


    /*
     |--------------------------------------------------------------------------
     | CHANGE PAPER SIZE (updates the on-screen preview width)
     |--------------------------------------------------------------------------
     */

    function changePaperSize(size) {

        const documentElement =
            document.getElementById('masterlistPrintDocument');

        if (!documentElement) {
            return;
        }

        const paper = PAPER_SIZES[size] || PAPER_SIZES.a4;

        Object.values(PAPER_SIZES).forEach(function (item) {
            documentElement.classList.remove(item.className);
        });

        documentElement.classList.add(paper.className);
    }


    /*
     |--------------------------------------------------------------------------
     | PRINT MASTERLIST
     |--------------------------------------------------------------------------
     |
     | The document is copied into a hidden iframe that has ONLY the document
     | styles plus one @page rule for the chosen paper. This means:
     |   - the app sidebar / topbar can never appear in the printout,
     |   - the app's dark theme can never reach the paper,
     |   - Chrome switches to the correct portrait paper automatically,
     |   - the table flows across as many pages as needed,
     |     with the column headings repeated on every page.
     */

    function printMasterlist() {

        const selector = document.getElementById('paperSize');
        const size = selector ? selector.value : 'a4';
        const paper = PAPER_SIZES[size] || PAPER_SIZES.a4;

        const source = document.getElementById('masterlistPrintDocument');
        const styles = document.getElementById('masterlistDocStyles');

        if (!source || !styles) {
            return;
        }

        const clone = source.cloneNode(true);
        clone.removeAttribute('class');

        const html =
            '<!DOCTYPE html><html><head><meta charset="utf-8">' +
            '<base href="' + document.baseURI + '">' +
            '<title>' + PRINT_TITLE + '</title>' +
            '<style>' + styles.textContent + '</style>' +
            '<style>' +
                '@page {' +
                    'size: ' + paper.pageSize + ';' +
                    'margin: 8mm 8mm 12mm 8mm;' +
                    '@bottom-center {' +
                        'content: "Page " counter(page) " of " counter(pages);' +
                        'font-family: Arial, Helvetica, sans-serif;' +
                        'font-size: 7pt;' +
                        'color: #555;' +
                    '}' +
                '}' +
                'html, body { margin: 0; padding: 0; background: #fff; }' +
            '</style>' +
            '</head><body>' + clone.outerHTML + '</body></html>';

        const oldFrame = document.getElementById('masterlistPrintFrame');

        if (oldFrame) {
            oldFrame.remove();
        }

        const frame = document.createElement('iframe');

        frame.id = 'masterlistPrintFrame';
        frame.setAttribute('aria-hidden', 'true');
        frame.style.cssText =
            'position:fixed;right:0;bottom:0;width:1px;height:1px;' +
            'border:0;opacity:0;pointer-events:none;';

        document.body.appendChild(frame);

        const frameDocument = frame.contentWindow.document;

        frameDocument.open();
        frameDocument.write(html);
        frameDocument.close();

        /*
         * Wait for both logos to load before opening the print dialog.
         */
        const images = Array.from(frameDocument.images);

        const waiters = images.map(function (image) {

            if (image.complete) {
                return Promise.resolve();
            }

            return new Promise(function (resolve) {
                image.addEventListener('load', resolve);
                image.addEventListener('error', resolve);
            });
        });

        const timeout = new Promise(function (resolve) {
            window.setTimeout(resolve, 3000);
        });

        Promise.race([Promise.all(waiters), timeout]).then(function () {

            frame.contentWindow.focus();
            frame.contentWindow.print();

        });
    }


    /*
     * Ctrl/Cmd+P uses the same isolated print so the app layout never prints.
     */
    document.addEventListener('keydown', function (event) {

        if ((event.ctrlKey || event.metaKey) && !event.shiftKey && !event.altKey
            && String(event.key).toLowerCase() === 'p'
            && document.getElementById('masterlistPrintDocument')
            && {{ $recordCount > 0 ? 'true' : 'false' }}) {

            event.preventDefault();
            printMasterlist();
        }
    });

</script>

@endsection
