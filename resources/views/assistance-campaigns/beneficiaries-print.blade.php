@extends('layouts.app')

@section('title', 'Bulk Voucher Preview | e-Poblacion')

@php
    /*
    |--------------------------------------------------------------------------
    | SAFE OPERATION DEFAULTS (unchanged behavior)
    |--------------------------------------------------------------------------
    */

    $operationName = $operation->operation_name
        ?? 'Relief Assistance Operation';

    $operationCode = $operation->operation_code
        ?? '—';

    $assistanceType = $operation->assistance_type
        ?? 'Relief Assistance';

    $distributionDate = !empty($operation->distribution_date)
        ? \Carbon\Carbon::parse($operation->distribution_date)->format('F d, Y')
        : 'To be announced';

    $distributionTime = !empty($operation->distribution_time)
        ? \Carbon\Carbon::parse($operation->distribution_time)->format('h:i A')
        : 'To be announced';

    $distributionLocation = $operation->distribution_location
        ?? 'Barangay Hall';

    $quantityUnit = $operation->quantity_unit
        ?? 'unit';

    $operationStatus = strtolower((string) ($operation->status ?? 'draft'));
    $operationStatusLabel = ucwords(str_replace('_', ' ', $operationStatus));


    /*
    |--------------------------------------------------------------------------
    | BUILD GENERATED VOUCHER COLLECTION (unchanged)
    |--------------------------------------------------------------------------
    | Resident-level: beneficiary id -> voucher beneficiary_id.
    | Read-only: nothing is generated or modified here.
    */

    $generatedVouchers = collect();

    foreach ($beneficiaries as $beneficiary) {

        $voucher = null;

        if (
            isset($existingVouchers)
            && $existingVouchers instanceof \Illuminate\Support\Collection
        ) {
            $voucher = $existingVouchers->get($beneficiary->id);
        } elseif (
            isset($existingVouchers)
            && is_array($existingVouchers)
        ) {
            $voucher = $existingVouchers[$beneficiary->id] ?? null;
        }

        if (!$voucher) {
            continue;
        }

        if (strtolower((string) ($voucher->status ?? '')) === 'cancelled') {
            continue;
        }

        $generatedVouchers->push([
            'beneficiary' => $beneficiary,
            'voucher' => $voucher,
        ]);
    }

    $voucherCount = $generatedVouchers->count();

    $classification = $operation->beneficiary_filter ?? 'all';
    $classificationLabel = ucwords(str_replace('_', ' ', $classification));

    /*
    | Full-name helper (first middle last suffix), skips empty parts.
    */
    $fullName = function ($person) {
        if (!$person) {
            return '—';
        }

        $name = trim(
            collect([
                $person->first_name ?? null,
                $person->middle_name ?? null,
                $person->last_name ?? null,
                $person->suffix ?? null,
            ])
                ->filter(fn ($part) => filled($part))
                ->implode(' ')
        );

        return $name !== '' ? $name : '—';
    };

    $scheduleText = $distributionDate . ' · ' . $distributionTime;
@endphp


@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">


{{-- ============================================================
     PAGE SIZE FOR THE BROWSER'S OWN PRINT PATH
     Updated by changePaperSize() so the @page size always matches
     the selected paper, even if the user prints from the browser menu.
     (The Print button uses an isolated frame with its own @page.)
============================================================ --}}
<style id="voucherPageStyle">
    @page {
        size: A4 portrait;
        margin: 8mm 8mm 12mm 8mm;
    }
</style>


{{-- ============================================================
     SCREEN-ONLY STYLES (app interface; theme aware)
============================================================ --}}
<style>

    .voucher-preview-page {
        --vp-card: #ffffff;
        --vp-soft: #f3f6f9;
        --vp-line: #d3dae1;
        --vp-line-strong: #98a4af;
        --vp-text: #18222e;
        --vp-muted: #4a5866;

        --vp-primary: #0038a8;
        --vp-primary-fg: #ffffff;
        --vp-primary-hover: #002a80;
        --vp-link: #0038a8;

        --vp-info-bg: #e5edfb;
        --vp-info-ink: #0d2c6b;
        --vp-info-line: #a9c1ef;
        --vp-warn-bg: #fff2cf;
        --vp-warn-ink: #5a4208;
        --vp-warn-line: #e8cf86;
        --vp-ok-bg: #e8f6ee;
        --vp-ok-line: #a8d8bd;

        --vp-shadow: 0 1px 2px rgba(10, 30, 60, 0.06), 0 10px 24px rgba(10, 30, 60, 0.08);
        --vp-paper-shadow: 0 6px 24px rgba(10, 30, 60, 0.14);

        max-width: 1450px;
        margin: 0 auto;
        color: var(--vp-text);
    }

    html[data-theme="dark"] .voucher-preview-page,
    html[data-bs-theme="dark"] .voucher-preview-page {
        --vp-card: #1b2530;
        --vp-soft: #141c25;
        --vp-line: #32414f;
        --vp-line-strong: #587083;
        --vp-text: #eef3f8;
        --vp-muted: #b6c4d1;

        --vp-primary: #8db1ff;
        --vp-primary-fg: #08173d;
        --vp-primary-hover: #b3cbff;
        --vp-link: #9dbcff;

        --vp-info-bg: #1b2c52;
        --vp-info-ink: #d3e0ff;
        --vp-info-line: #3d5aa0;
        --vp-warn-bg: #3b2f12;
        --vp-warn-ink: #f6dc96;
        --vp-warn-line: #6e5a22;
        --vp-ok-bg: #1b3a2d;
        --vp-ok-line: #2f6b50;

        --vp-shadow: none;
        --vp-paper-shadow: 0 6px 24px rgba(0, 0, 0, 0.55);

        color-scheme: dark;
    }

    .voucher-preview-page .text-muted { color: var(--vp-muted) !important; }
    .voucher-preview-page :focus-visible { outline: 3px solid #f2c14e; outline-offset: 2px; }

    /* ---------- buttons ---------- */

    .voucher-preview-page .btn { font-weight: 700; border-radius: 0.6rem; }

    .voucher-preview-page .btn-primary {
        background: var(--vp-primary);
        border: 1.5px solid var(--vp-primary);
        color: var(--vp-primary-fg);
    }
    .voucher-preview-page .btn-primary:hover,
    .voucher-preview-page .btn-primary:focus-visible {
        background: var(--vp-primary-hover);
        border-color: var(--vp-primary-hover);
        color: var(--vp-primary-fg);
    }

    .voucher-preview-page .btn-outline-secondary {
        background: var(--vp-card);
        border: 1.5px solid var(--vp-line-strong);
        color: var(--vp-text);
    }
    .voucher-preview-page .btn-outline-secondary:hover,
    .voucher-preview-page .btn-outline-secondary:focus-visible {
        background: var(--vp-soft);
        border-color: var(--vp-link);
        color: var(--vp-text);
    }

    .voucher-preview-page .btn-outline-primary {
        background: var(--vp-card);
        border: 1.5px solid var(--vp-link);
        color: var(--vp-link);
    }
    .voucher-preview-page .btn-outline-primary:hover,
    .voucher-preview-page .btn-outline-primary:focus-visible {
        background: var(--vp-primary);
        border-color: var(--vp-primary);
        color: var(--vp-primary-fg);
    }

    /* ---------- navigation / cards / alerts ---------- */

    .voucher-navigation {
        background: var(--vp-card);
        border: 1px solid var(--vp-line);
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 18px;
        box-shadow: var(--vp-shadow);
    }
    .voucher-navigation .btn { white-space: nowrap; }

    .voucher-preview-page .card {
        background: var(--vp-card);
        color: var(--vp-text);
        border: 1px solid var(--vp-line);
    }

    .page-header-card {
        border: 1px solid var(--vp-line) !important;
        border-radius: 18px;
        box-shadow: var(--vp-shadow);
    }

    .voucher-preview-page .alert-info {
        background: var(--vp-info-bg);
        color: var(--vp-info-ink);
        border: 1px solid var(--vp-info-line);
    }
    .voucher-preview-page .alert-warning {
        background: var(--vp-warn-bg);
        color: var(--vp-warn-ink);
        border: 1px solid var(--vp-warn-line);
    }
    .workflow-card { border-radius: 16px; }

    .summary-label {
        font-size: 0.74rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: var(--vp-muted);
        font-weight: 700;
        margin-bottom: 5px;
    }

    .generated-summary {
        border: 1px solid var(--vp-ok-line);
        border-radius: 14px;
        background: var(--vp-ok-bg);
        color: var(--vp-text);
        padding: 16px;
    }
    .generated-summary-number { font-size: 1.7rem; font-weight: 800; line-height: 1; }

    .empty-preview {
        border: 2px dashed var(--vp-line-strong);
        border-radius: 14px;
        padding: 55px 20px;
        text-align: center;
        color: var(--vp-muted);
        background: var(--vp-soft);
    }

    /* ---------- print settings row ---------- */

    .vp-settings {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }
    .vp-settings label { font-size: 0.8rem; font-weight: 700; color: var(--vp-muted); margin: 0; }

    .vp-paper-select {
        min-width: 210px;
        padding: 9px 36px 9px 12px;
        border: 1.5px solid var(--vp-line-strong);
        border-radius: 0.6rem;
        background-color: var(--vp-card);
        color: var(--vp-text);
        font: inherit;
        font-size: 0.85rem;
        font-weight: 600;
        cursor: pointer;
    }
    .vp-paper-select option { background: var(--vp-card); color: var(--vp-text); }

    .vp-hint { font-size: 0.82rem; color: var(--vp-muted); margin: 0 0 12px; }

    /* ---------- stage: dark/light app surface that holds the white paper ---------- */

    .vp-stage {
        padding: 24px;
        border-radius: 16px;
        background: var(--vp-soft);
        border: 1px solid var(--vp-line);
        overflow-x: auto;
    }

    /*
     * The sheet is the FULL paper width. Its 8mm padding stands in for the
     * @page margin, so the content width on screen equals the content width
     * on paper (#voucherDoc is border-box in the document styles below).
     */
    .vp-stage #voucherDoc {
        margin: 0 auto;
        padding: 8mm;
        width: 210mm;
        box-shadow: var(--vp-paper-shadow);
    }
    .vp-stage #voucherDoc.paper-a4    { width: 210mm; }
    .vp-stage #voucherDoc.paper-short { width: 8.5in; }
    .vp-stage #voucherDoc.paper-long  { width: 8.5in; }

    @media (max-width: 767.98px) {
        .vp-stage { padding: 10px; }
        .vp-paper-select { flex: 1; min-width: 0; }
    }

    /* ============================================================
       PRINT (browser menu / any print that is not the Print button)

       Cause of the "two sheets + app header over the preview" bug:
       only elements marked .no-print were hidden, so the layout's
       sidebar / navbar / search bar and its fixed-height wrappers
       still took up the first sheet and pushed the vouchers to the
       second one.

       Fix: hide every element that is neither the voucher sheet nor
       an ancestor of it, then flatten the ancestors (no flex, fixed
       or sticky positioning, heights, padding, scroll containers) so
       the sheet flows from the top of page 1.
       ============================================================ */
    @media print {

        html, body {
            height: auto !important;
            min-height: 0 !important;
            overflow: visible !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }

        /* hide everything that is not the sheet or an ancestor of it */
        body *:not(#voucherDoc):not(#voucherDoc *):not(:has(#voucherDoc)) {
            display: none !important;
        }

        /* flatten every ancestor of the sheet (layout wrappers, stage, page) */
        body *:has(#voucherDoc) {
            display: block !important;
            position: static !important;
            float: none !important;
            width: auto !important;
            max-width: none !important;
            height: auto !important;
            min-height: 0 !important;
            max-height: none !important;
            margin: 0 !important;
            padding: 0 !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: #fff !important;
            box-shadow: none !important;
            overflow: visible !important;
            transform: none !important;
        }

        #voucherDoc {
            width: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            box-shadow: none !important;
        }
    }

</style>


{{-- ============================================================
     DOCUMENT STYLES
     Used by BOTH the on-screen paper and the print frame.
     Every rule is anchored to #voucherDoc and sets explicit colors,
     so neither the app's dark theme nor Bootstrap can change them.
============================================================ --}}
<style id="voucherDocStyles">

    #voucherDoc {
        box-sizing: border-box;
        background: #fff;
        color: #111;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 8pt;
        line-height: 1.3;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    #voucherDoc *,
    #voucherDoc *::before,
    #voucherDoc *::after {
        box-sizing: border-box;
        color: inherit;
        background-color: transparent;
    }

    #voucherDoc img { filter: none; opacity: 1; }

    /* ---------- sheet header (once, first page; kept with the first vouchers) ---------- */

    #voucherDoc .vd-head {
        display: grid;
        grid-template-columns: 14mm minmax(0, 1fr) 14mm;
        gap: 3mm;
        align-items: center;
        text-align: center;
        break-inside: avoid;
        break-after: avoid;
    }

    #voucherDoc .vd-head img {
        display: block;
        width: 13mm;
        height: 13mm;
        margin: 0 auto;
        object-fit: contain;
    }

    #voucherDoc .vd-gov   { font-size: 6.6pt; font-weight: 600; line-height: 1.2; }
    #voucherDoc .vd-brgy  { font-size: 10.5pt; font-weight: 800; letter-spacing: .4pt; margin-top: .6mm; }
    #voucherDoc .vd-title { font-size: 9.5pt; font-weight: 800; letter-spacing: .3pt; margin-top: .8mm; }

    #voucherDoc .vd-rule {
        margin: 2mm 0 2.4mm;
        border-bottom: 3px double #111;
        break-after: avoid;
    }

    #voucherDoc .vd-op {
        display: grid;
        grid-template-columns: 1.5fr 1fr 1.3fr 1.2fr;
        margin-bottom: 3mm;
        border: 1px solid #333;
        break-inside: avoid;
        break-after: avoid;
    }

    #voucherDoc .vd-op > div {
        min-width: 0;
        padding: 1.2mm 2mm;
        border-right: 1px solid #bbb;
    }
    #voucherDoc .vd-op > div:last-child { border-right: 0; }

    #voucherDoc .vd-oplabel {
        display: block;
        margin-bottom: .5mm;
        font-size: 5.5pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3pt;
        color: #4a5866;
    }

    #voucherDoc .vd-opvalue {
        display: block;
        font-size: 7.4pt;
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    /* ---------- voucher flow (inline-blocks fragment reliably across pages) ---------- */

    #voucherDoc .vd-grid { font-size: 0; line-height: 0; }

    #voucherDoc .vd-cell {
        display: inline-block;
        vertical-align: top;
        width: 48.6%;
        margin: 0 .6% 2.6mm;
        font-size: 8pt;
        line-height: 1.3;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    /*
     * overflow is visible on purpose: long names, addresses or codes must
     * wrap (overflow-wrap below) and grow the card, never be clipped.
     */
    #voucherDoc .vd-voucher {
        border: 1px dashed #555;
        border-radius: 1.5mm;
        background: #fff;
        break-inside: avoid;
        page-break-inside: avoid;
    }

    #voucherDoc .vd-strip {
        height: 1.4mm;
        border-radius: 1.5mm 1.5mm 0 0;
        background: linear-gradient(90deg, #0038a8 0 50%, #ce1126 50% 100%);
    }

    #voucherDoc .vd-vhead {
        display: grid;
        grid-template-columns: 9mm minmax(0, 1fr) auto;
        gap: 2mm;
        align-items: center;
        padding: 1.6mm 2.4mm;
        border-bottom: 1px solid #bbb;
    }

    #voucherDoc .vd-vhead img {
        display: block;
        width: 8.5mm;
        height: 8.5mm;
        object-fit: contain;
    }

    #voucherDoc .vd-vbrgy  { font-size: 5.8pt; font-weight: 700; letter-spacing: .3pt; color: #444; text-transform: uppercase; }
    #voucherDoc .vd-vtitle { font-size: 8.2pt; font-weight: 800; color: #0b2a78; text-transform: uppercase; line-height: 1.15; }

    #voucherDoc .vd-vno {
        padding: .4mm 1.8mm;
        border: 1px solid #555;
        border-radius: 99px;
        font-size: 6.8pt;
        font-weight: 700;
        white-space: nowrap;
    }

    #voucherDoc .vd-vbody {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 27mm;
        gap: 2.5mm;
        padding: 2mm 2.4mm;
    }

    #voucherDoc .vd-main { min-width: 0; }

    #voucherDoc .vd-lbl {
        font-size: 5.5pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3pt;
        color: #4a5866;
    }

    #voucherDoc .vd-name {
        margin: .3mm 0 1.4mm;
        font-size: 9.5pt;
        font-weight: 800;
        line-height: 1.2;
        overflow-wrap: anywhere;
    }

    #voucherDoc .vd-row {
        display: grid;
        grid-template-columns: 15.5mm minmax(0, 1fr);
        gap: 1.5mm;
        padding: .45mm 0;
        border-top: 1px dotted #aaa;
        font-size: 6.8pt;
        line-height: 1.3;
    }

    #voucherDoc .vd-row .k { font-weight: 700; color: #4a5866; }
    #voucherDoc .vd-row .v { font-weight: 600; overflow-wrap: anywhere; }

    #voucherDoc .vd-qr {
        align-self: start;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 1mm;
        border: 1px solid #888;
        background: #fff;
    }

    #voucherDoc .vd-qr svg {
        display: block;
        width: 23mm;
        height: 23mm;
        shape-rendering: crispEdges;
    }

    #voucherDoc .vd-scan {
        margin-bottom: .6mm;
        font-size: 5.2pt;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3pt;
        color: #4a5866;
    }

    #voucherDoc .vd-code {
        margin-top: .8mm;
        width: 100%;
        font-family: Consolas, 'Courier New', monospace;
        font-size: 5.4pt;
        font-weight: 700;
        line-height: 1.15;
        text-align: center;
        word-break: break-all;
    }

    #voucherDoc .vd-noqr {
        font-size: 6pt;
        font-weight: 700;
        text-align: center;
        padding: 3mm 1mm;
    }

    #voucherDoc .vd-assist {
        display: grid;
        grid-template-columns: 1.2fr .8fr 1.5fr;
        border-top: 1px solid #bbb;
    }

    #voucherDoc .vd-assist > div {
        min-width: 0;
        padding: 1.2mm 2.4mm;
        border-right: 1px solid #ccc;
    }
    #voucherDoc .vd-assist > div:last-child { border-right: 0; }

    #voucherDoc .vd-val { font-size: 7.2pt; font-weight: 700; overflow-wrap: anywhere; }

    #voucherDoc .vd-vfoot {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        gap: .6mm 2mm;
        padding: 1.2mm 2.4mm;
        border-top: 1px solid #bbb;
        font-size: 5.8pt;
        color: #444;
    }
    #voucherDoc .vd-vfoot .loc { overflow-wrap: anywhere; }

    /* ---------- screen-only controls under each voucher (stripped when printing) ---------- */

    #voucherDoc .vd-screenbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 6px;
        margin-top: 4px;
        font-size: 11px;
        line-height: 1.3;
    }

    #voucherDoc .vd-status {
        padding: 2px 9px;
        border-radius: 99px;
        font-weight: 700;
        border: 1px solid #999;
        color: #222;
        background: #eee;
    }
    #voucherDoc .vd-status.is-generated   { background: #fff2cf; color: #5a4208; border-color: #e8cf86; }
    #voucherDoc .vd-status.is-issued      { background: #e8f6ee; color: #14532d; border-color: #a8d8bd; }
    #voucherDoc .vd-status.is-distributed { background: #e5edfb; color: #0d2c6b; border-color: #a9c1ef; }
    #voucherDoc .vd-status.is-claimed     { background: #e9ecef; color: #333;    border-color: #bbb; }
    #voucherDoc .vd-status.is-cancelled   { background: #fde7ea; color: #8a1224; border-color: #f0b3bb; }

    #voucherDoc .vd-sbtn {
        padding: 2px 9px;
        border: 1px solid #0038a8;
        border-radius: 6px;
        background: #fff;
        color: #0038a8;
        font-weight: 700;
        text-decoration: none;
    }
    #voucherDoc .vd-sbtn:hover { background: #0038a8; color: #fff; }

</style>


<div class="container-fluid py-4 voucher-preview-page">

    {{-- =====================================================================
         TOP NAVIGATION
         ===================================================================== --}}

    <div class="voucher-navigation no-print">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

            <div class="d-flex flex-wrap gap-2">

                <a href="{{ route('relief-vouchers.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Relief Operations
                </a>

                <a href="{{ route('relief-assistance.show', $operation) }}" class="btn btn-outline-primary">
                    <i class="bi bi-speedometer2 me-1"></i>
                    Operation Dashboard
                </a>

                <a href="{{ route('relief-assistance.beneficiaries', $operation) }}" class="btn btn-outline-primary">
                    <i class="bi bi-people-fill me-1"></i>
                    Beneficiary Encoding
                </a>

            </div>

            @if ($voucherCount > 0)

                <div class="vp-settings">

                    <label for="paperSize">Paper size</label>

                    <select id="paperSize" class="vp-paper-select" onchange="changePaperSize(this.value)">
                        <option value="a4">A4 — 210 × 297 mm</option>
                        <option value="short">Short Bond — 8.5 × 11 in</option>
                        <option value="long">Long Bond — 8.5 × 13 in</option>
                    </select>

                    <button type="button" class="btn btn-primary" onclick="printVouchers()">
                        <i class="bi bi-printer-fill me-1"></i>
                        Print Bulk Vouchers
                    </button>

                </div>

            @endif

        </div>

    </div>


    {{-- =====================================================================
         PAGE HEADER
         ===================================================================== --}}

    <div class="card page-header-card mb-4 no-print">

        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">

                <div>
                    <div class="text-uppercase small text-muted fw-semibold">Voucher Generation</div>

                    <h1 class="h3 fw-bold mb-1">Preview &amp; Print Bulk Vouchers</h1>

                    <p class="text-muted mb-0">
                        Review the generated vouchers before printing and distributing them.
                    </p>
                </div>

                <div class="text-md-end">

                    <div class="mb-2">
                        <span class="badge text-bg-primary fs-6 px-3 py-2">
                            {{ $voucherCount }}
                            {{ $voucherCount === 1 ? 'Generated Voucher' : 'Generated Vouchers' }}
                        </span>
                    </div>

                    <span class="badge border" style="background: var(--vp-soft); color: var(--vp-text);">
                        Operation: {{ $operationCode }}
                    </span>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================================
         WORKFLOW NOTICE
         ===================================================================== --}}

    <div class="alert alert-info workflow-card no-print mb-4">

        <div class="d-flex gap-3">

            <div class="fs-4"><i class="bi bi-qr-code"></i></div>

            <div>
                <div class="fw-bold mb-1">Voucher Generation Complete</div>

                <div class="small">
                    The vouchers shown below were generated for the confirmed
                    beneficiaries of this relief operation. Each resident
                    beneficiary has a unique voucher and unique QR code.
                </div>

                <div class="small mt-2">
                    <strong>Normal workflow:</strong>
                    Generate → Preview → Print → Scan QR → Automatic Claim
                </div>
            </div>

        </div>

    </div>


    {{-- =====================================================================
         GENERATED VOUCHER SUMMARY
         ===================================================================== --}}

    <div class="generated-summary no-print mb-4">

        <div class="row g-3 align-items-center">

            <div class="col-md-3">
                <div class="summary-label">Generated Vouchers</div>
                <div class="generated-summary-number">{{ $voucherCount }}</div>
            </div>

            <div class="col-md-3">
                <div class="summary-label">Classification</div>
                <div class="fw-semibold">{{ $classificationLabel }}</div>
            </div>

            <div class="col-md-3">
                <div class="summary-label">Assistance</div>
                <div class="fw-semibold">{{ $assistanceType }}</div>
            </div>

            <div class="col-md-3">
                <div class="summary-label">Operation Status</div>
                <div>
                    <span class="badge text-bg-secondary">{{ $operationStatusLabel }}</span>
                </div>
            </div>

        </div>

    </div>


    @if ($voucherCount === 0)

        {{-- =================================================================
             EMPTY STATE
             ================================================================= --}}

        <div class="empty-preview no-print">

            <div class="display-5 mb-3"><i class="bi bi-qr-code"></i></div>

            <h5 class="fw-bold">No Generated Vouchers Found</h5>

            <p class="mb-4">
                There are currently no active generated vouchers for the
                confirmed beneficiaries of this operation.
            </p>

            <div class="d-flex flex-wrap justify-content-center gap-2">

                <a href="{{ route('relief-assistance.beneficiaries', $operation) }}" class="btn btn-primary">
                    <i class="bi bi-people-fill me-1"></i>
                    Go to Beneficiary Encoding
                </a>

                <a href="{{ route('relief-assistance.show', $operation) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-speedometer2 me-1"></i>
                    Operation Dashboard
                </a>

            </div>

        </div>

    @else

        <p class="vp-hint no-print">
            The paper below is shown at the real portrait width of the selected size.
            Cards flow two across and continue onto new pages only when needed.
            In the print dialog keep <strong>Margins: Default</strong> and
            <strong>Scale: 100%</strong>.
        </p>


        {{-- =================================================================
             PRINTABLE DOCUMENT
             ================================================================= --}}

        <div class="vp-stage">

            <div id="voucherDoc" class="paper-a4">

                {{-- Sheet header --}}
                <div class="vd-head">

                    <div><img src="{{ asset('icon.png') }}" alt="Barangay Poblacion Logo"></div>

                    <div>
                        <div class="vd-gov">REPUBLIC OF THE PHILIPPINES</div>
                        <div class="vd-gov">PROVINCE OF PANGASINAN · MUNICIPALITY OF BANI</div>
                        <div class="vd-brgy">BARANGAY POBLACION</div>
                        <div class="vd-title">RELIEF ASSISTANCE VOUCHERS</div>
                    </div>

                    <div><img src="{{ asset('Bani.png') }}" alt="Municipality of Bani Logo"></div>

                </div>

                <div class="vd-rule"></div>

                <div class="vd-op">

                    <div>
                        <span class="vd-oplabel">Operation</span>
                        <span class="vd-opvalue">{{ $operationName }}</span>
                        <span class="vd-opvalue">{{ $operationCode }} · {{ number_format($voucherCount) }} {{ $voucherCount === 1 ? 'voucher' : 'vouchers' }}</span>
                    </div>

                    <div>
                        <span class="vd-oplabel">Assistance Type</span>
                        <span class="vd-opvalue">{{ $assistanceType }}</span>
                    </div>

                    <div>
                        <span class="vd-oplabel">Distribution</span>
                        <span class="vd-opvalue">{{ $distributionDate }}</span>
                        <span class="vd-opvalue">{{ $distributionTime }}</span>
                    </div>

                    <div>
                        <span class="vd-oplabel">Location</span>
                        <span class="vd-opvalue">{{ $distributionLocation }}</span>
                    </div>

                </div>


                {{-- Vouchers --}}
                <div class="vd-grid">

                    @foreach ($generatedVouchers as $index => $item)

                        @php
                            $beneficiary = $item['beneficiary'];
                            $voucher = $item['voucher'];

                            $resident = $beneficiary->resident ?? null;
                            $household = $beneficiary->household ?? null;
                            $householdHead = $household?->householdHead;

                            $residentName = $fullName($resident);
                            $headName = $fullName($householdHead);

                            $householdNumber = $household?->household_number ?? '—';
                            $purok = $household?->purok ?? '—';
                            $address = $household?->address ?? '—';

                            $relationship = $beneficiary->relationship ?? 'Beneficiary';

                            $assistanceQuantity = $voucher->allocated_quantity
                                ?? $beneficiary->allocated_quantity
                                ?? 1;

                            $voucherStatus = strtolower((string) ($voucher->status ?? 'generated'));
                            $voucherStatusLabel = ucwords(str_replace('_', ' ', $voucherStatus));

                            $statusClass = match ($voucherStatus) {
                                'generated'   => 'is-generated',
                                'issued'      => 'is-issued',
                                'distributed' => 'is-distributed',
                                'claimed'     => 'is-claimed',
                                'cancelled'   => 'is-cancelled',
                                default       => '',
                            };

                            $voucherCode = trim((string) ($voucher->voucher_code ?? ''));
                        @endphp

                        <div class="vd-cell">

                            <div class="vd-voucher">

                                <div class="vd-strip"></div>

                                <div class="vd-vhead">
                                    <img src="{{ asset('icon.png') }}" alt="">
                                    <div>
                                        <div class="vd-vbrgy">Barangay Poblacion</div>
                                        <div class="vd-vtitle">Relief Assistance Voucher</div>
                                    </div>
                                    <div class="vd-vno">No. {{ $index + 1 }}</div>
                                </div>

                                <div class="vd-vbody">

                                    <div class="vd-main">

                                        <div class="vd-lbl">Beneficiary</div>
                                        <div class="vd-name">{{ $residentName }}</div>

                                        <div class="vd-row"><span class="k">Classification</span><span class="v">{{ $classificationLabel }}</span></div>
                                        <div class="vd-row"><span class="k">Relationship</span><span class="v">{{ $relationship }}</span></div>
                                        <div class="vd-row"><span class="k">Household</span><span class="v">{{ $householdNumber }}</span></div>
                                        <div class="vd-row"><span class="k">Head</span><span class="v">{{ $headName }}</span></div>
                                        <div class="vd-row"><span class="k">Purok</span><span class="v">{{ $purok }}</span></div>
                                        <div class="vd-row"><span class="k">Address</span><span class="v">{{ $address }}</span></div>

                                    </div>

                                    <div class="vd-qr">

                                        @if ($voucherCode !== '')

                                            <div class="vd-scan">Scan QR</div>

                                            {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                                                ->size(90)
                                                ->margin(1)
                                                ->errorCorrection('M')
                                                ->generate($voucherCode) !!}

                                            <div class="vd-code">{{ $voucherCode }}</div>

                                        @else

                                            <div class="vd-noqr">QR / voucher code unavailable</div>

                                        @endif

                                    </div>

                                </div>

                                <div class="vd-assist">
                                    <div>
                                        <div class="vd-lbl">Assistance</div>
                                        <div class="vd-val">{{ $assistanceType }}</div>
                                    </div>
                                    <div>
                                        <div class="vd-lbl">Quantity</div>
                                        <div class="vd-val">{{ $assistanceQuantity }} {{ $quantityUnit }}</div>
                                    </div>
                                    <div>
                                        <div class="vd-lbl">Distribution</div>
                                        <div class="vd-val">{{ $scheduleText }}</div>
                                    </div>
                                </div>

                                <div class="vd-vfoot">
                                    <span class="loc"><strong>{{ $operationCode }}</strong> · {{ $distributionLocation }}</span>
                                    <span>For official barangay distribution use</span>
                                </div>

                            </div>

                            {{-- screen only: removed from the printed copy --}}
                            <div class="vd-screenbar no-print">

                                <span class="vd-status {{ $statusClass }}">{{ $voucherStatusLabel }}</span>

                                @if ($voucher->id)
                                    <a href="{{ route('relief-vouchers.show', $voucher) }}" class="vd-sbtn">
                                        View Voucher
                                    </a>
                                @endif

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        </div>


        <div class="alert alert-warning no-print mt-4 mb-0">

            <div class="d-flex gap-2">

                <i class="bi bi-printer-fill fs-5"></i>

                <div class="small">
                    <strong>Before printing:</strong>
                    verify every voucher's beneficiary, household information,
                    assistance details, voucher code, and QR code.
                    <br><br>
                    The system does not print automatically. Click
                    <strong>Print Bulk Vouchers</strong> when everything is correct.
                    Opening or printing this preview does not create or change any voucher.
                </div>

            </div>

        </div>


        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 no-print">

            <div class="d-flex flex-wrap gap-2">

                <a href="{{ route('relief-assistance.beneficiaries', $operation) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Back to Beneficiaries
                </a>

                <a href="{{ route('relief-assistance.show', $operation) }}" class="btn btn-outline-primary">
                    <i class="bi bi-speedometer2 me-1"></i>
                    Operation Dashboard
                </a>

            </div>

            <button type="button" class="btn btn-primary" onclick="printVouchers()">
                <i class="bi bi-printer-fill me-1"></i>
                Print Bulk Vouchers
            </button>

        </div>

    @endif

</div>


<script>

    const VOUCHER_PAPER_SIZES = {
        a4:    { className: 'paper-a4',    pageSize: 'A4 portrait' },
        short: { className: 'paper-short', pageSize: '8.5in 11in' },
        long:  { className: 'paper-long',  pageSize: '8.5in 13in' }
    };

    const VOUCHER_PAGE_MARGIN = '8mm 8mm 12mm 8mm';

    const VOUCHER_PRINT_TITLE = @json('Bulk Vouchers - ' . $operationCode);

    /*
     * Updates the on-screen paper width AND the page's own @page size, so
     * the preview and the browser's print output use the same paper.
     */
    function changePaperSize(size) {

        const doc = document.getElementById('voucherDoc');

        if (!doc) {
            return;
        }

        const paper = VOUCHER_PAPER_SIZES[size] || VOUCHER_PAPER_SIZES.a4;

        Object.values(VOUCHER_PAPER_SIZES).forEach(function (item) {
            doc.classList.remove(item.className);
        });

        doc.classList.add(paper.className);

        const pageStyle = document.getElementById('voucherPageStyle');

        if (pageStyle) {
            pageStyle.textContent =
                '@page { size: ' + paper.pageSize + '; margin: ' + VOUCHER_PAGE_MARGIN + '; }';
        }
    }

    /*
     * Prints from an isolated frame that contains only the voucher sheet and
     * its styles, so the app navbar, sidebar, buttons and alerts can never
     * appear, and dark-theme CSS cannot reach the paper.
     */
    function printVouchers() {

        const selector = document.getElementById('paperSize');
        const size = selector ? selector.value : 'a4';
        const paper = VOUCHER_PAPER_SIZES[size] || VOUCHER_PAPER_SIZES.a4;

        const source = document.getElementById('voucherDoc');
        const styles = document.getElementById('voucherDocStyles');

        if (!source || !styles) {
            return;
        }

        const clone = source.cloneNode(true);

        clone.removeAttribute('class');
        clone.querySelectorAll('.no-print').forEach(function (node) {
            node.remove();
        });

        const html =
            '<!DOCTYPE html><html><head><meta charset="utf-8">' +
            '<base href="' + document.baseURI + '">' +
            '<title>' + VOUCHER_PRINT_TITLE + '</title>' +
            '<style>' + styles.textContent + '</style>' +
            '<style>' +
                '@page {' +
                    'size: ' + paper.pageSize + ';' +
                    'margin: ' + VOUCHER_PAGE_MARGIN + ';' +
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

        const oldFrame = document.getElementById('voucherPrintFrame');

        if (oldFrame) {
            oldFrame.remove();
        }

        const frame = document.createElement('iframe');

        frame.id = 'voucherPrintFrame';
        frame.setAttribute('aria-hidden', 'true');
        frame.style.cssText =
            'position:fixed;right:0;bottom:0;width:1px;height:1px;' +
            'border:0;opacity:0;pointer-events:none;';

        document.body.appendChild(frame);

        const frameDocument = frame.contentWindow.document;

        frameDocument.open();
        frameDocument.write(html);
        frameDocument.close();

        const waiters = Array.from(frameDocument.images).map(function (image) {

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

    /* Ctrl/Cmd+P uses the same isolated print so the app layout never prints. */
    document.addEventListener('keydown', function (event) {

        if ((event.ctrlKey || event.metaKey) && !event.shiftKey && !event.altKey
            && String(event.key).toLowerCase() === 'p'
            && document.getElementById('voucherDoc')) {

            event.preventDefault();
            printVouchers();
        }
    });

</script>

@endsection
