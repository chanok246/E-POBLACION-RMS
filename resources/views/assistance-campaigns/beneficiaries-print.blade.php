@extends('layouts.app')

@section('title', 'Bulk Voucher Preview | e-Poblacion')

@php
    /*
    |--------------------------------------------------------------------------
    | SAFE OPERATION DEFAULTS
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


    /*
    |--------------------------------------------------------------------------
    | OPERATION STATUS
    |--------------------------------------------------------------------------
    */

    $operationStatus = strtolower(
        (string) ($operation->status ?? 'draft')
    );

    $operationStatusLabel = ucwords(
        str_replace('_', ' ', $operationStatus)
    );


    /*
    |--------------------------------------------------------------------------
    | BUILD GENERATED VOUCHER COLLECTION
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    |
    | This workflow is RESIDENT-LEVEL.
    |
    | Therefore:
    |
    | beneficiary ID
    |       ↓
    | voucher beneficiary_id
    |
    | NOT household_id.
    |
    | This allows multiple residents from one household to have
    | separate vouchers.
    |
    */

    $generatedVouchers = collect();

    foreach ($beneficiaries as $beneficiary) {

        $voucher = null;


        /*
        |--------------------------------------------------------------------------
        | Collection
        |--------------------------------------------------------------------------
        */

        if (
            isset($existingVouchers)
            && $existingVouchers instanceof \Illuminate\Support\Collection
        ) {
            $voucher = $existingVouchers->get(
                $beneficiary->id
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Array
        |--------------------------------------------------------------------------
        */

        elseif (
            isset($existingVouchers)
            && is_array($existingVouchers)
        ) {
            $voucher = $existingVouchers[
                $beneficiary->id
            ] ?? null;
        }


        /*
        |--------------------------------------------------------------------------
        | Skip if no voucher exists
        |--------------------------------------------------------------------------
        */

        if (!$voucher) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | Never display cancelled vouchers in the active bulk preview
        |--------------------------------------------------------------------------
        */

        if (
            strtolower(
                (string) ($voucher->status ?? '')
            ) === 'cancelled'
        ) {
            continue;
        }


        $generatedVouchers->push([
            'beneficiary' => $beneficiary,
            'voucher' => $voucher,
        ]);
    }


    $voucherCount = $generatedVouchers->count();


    /*
    |--------------------------------------------------------------------------
    | CLASSIFICATION LABEL
    |--------------------------------------------------------------------------
    */

    $classification = $operation->beneficiary_filter
        ?? 'all';

    $classificationLabel = ucwords(
        str_replace(
            '_',
            ' ',
            $classification
        )
    );

@endphp


@section('content')

<style>

    /*
    |--------------------------------------------------------------------------
    | MAIN PAGE
    |--------------------------------------------------------------------------
    */

    .voucher-preview-page {
        max-width: 1450px;
        margin: 0 auto;
    }


    /*
    |--------------------------------------------------------------------------
    | TOP NAVIGATION
    |--------------------------------------------------------------------------
    */

    .voucher-navigation {
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 14px;
        padding: 12px 14px;
        margin-bottom: 18px;

        box-shadow:
            0 4px 14px rgba(0, 0, 0, 0.06);
    }


    .voucher-navigation .btn {
        white-space: nowrap;
    }


    /*
    |--------------------------------------------------------------------------
    | HEADER
    |--------------------------------------------------------------------------
    */

    .page-header-card {
        border: 0;
        border-radius: 18px;

        box-shadow:
            0 8px 25px rgba(0, 0, 0, 0.08);
    }


    /*
    |--------------------------------------------------------------------------
    | WORKFLOW CARD
    |--------------------------------------------------------------------------
    */

    .workflow-card {
        border-radius: 16px;
        border: 1px solid #b6d4fe;
    }


    /*
    |--------------------------------------------------------------------------
    | SUMMARY CARDS
    |--------------------------------------------------------------------------
    */

    .summary-card {
        border: 1px solid #dee2e6;
        border-radius: 12px;

        height: 100%;
        padding: 14px;

        background: #ffffff;
    }


    .summary-label {
        font-size: 0.72rem;

        text-transform: uppercase;
        letter-spacing: 0.4px;

        color: #6c757d;

        font-weight: 700;

        margin-bottom: 5px;
    }


    .summary-value {
        font-size: 0.95rem;
        font-weight: 600;
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATED VOUCHER SUMMARY
    |--------------------------------------------------------------------------
    */

    .generated-summary {
        border: 1px solid #d1e7dd;
        border-radius: 14px;

        background: #f8fffb;

        padding: 16px;
    }


    .generated-summary-number {
        font-size: 1.7rem;
        font-weight: 800;
        line-height: 1;
    }


    /*
    |--------------------------------------------------------------------------
    | OFFICIAL HEADER
    |--------------------------------------------------------------------------
    */

    .official-header {
        text-align: center;

        padding: 10px 0 18px;

        border-bottom: 2px solid #212529;

        margin-bottom: 20px;
    }


    .official-header .barangay-name {
        font-size: 1.25rem;
        font-weight: 700;

        text-transform: uppercase;
        letter-spacing: 0.4px;
    }


    .official-header .municipality {
        font-size: 0.95rem;
        font-weight: 600;
    }


    .official-header .province {
        font-size: 0.9rem;
    }


    .operation-title {
        font-size: 1.5rem;
        font-weight: 700;

        margin-top: 12px;
        margin-bottom: 4px;
    }


    .operation-code {
        font-size: 0.9rem;
        color: #6c757d;
    }


    /*
    |--------------------------------------------------------------------------
    | VOUCHER GRID
    |--------------------------------------------------------------------------
    */

    .voucher-preview-grid {
        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 16px;

        margin-top: 20px;
    }


    /*
    |--------------------------------------------------------------------------
    | VOUCHER CARD
    |--------------------------------------------------------------------------
    */

    .voucher-coupon {
        border: 2px dashed #6c757d;

        border-radius: 12px;

        padding: 16px;

        background: #ffffff;

        position: relative;

        break-inside: avoid;
        page-break-inside: avoid;

        transition:
            box-shadow 0.15s ease,
            transform 0.15s ease;
    }


    .voucher-coupon:hover {
        box-shadow:
            0 8px 24px rgba(0, 0, 0, 0.08);

        transform: translateY(-1px);
    }


    /*
    |--------------------------------------------------------------------------
    | VOUCHER HEADER
    |--------------------------------------------------------------------------
    */

    .voucher-coupon-header {
        display: flex;

        justify-content: space-between;

        align-items: flex-start;

        gap: 10px;

        border-bottom: 1px solid #dee2e6;

        padding-bottom: 9px;

        margin-bottom: 12px;
    }


    .voucher-title {
        font-weight: 800;
        font-size: 0.95rem;

        text-transform: uppercase;
    }


    .voucher-number {
        font-size: 0.72rem;

        color: #6c757d;

        font-weight: 600;

        margin-top: 2px;
    }


    /*
    |--------------------------------------------------------------------------
    | BENEFICIARY
    |--------------------------------------------------------------------------
    */

    .beneficiary-name {
        font-size: 1.08rem;

        font-weight: 800;

        margin-bottom: 3px;
    }


    .beneficiary-label {
        font-size: 0.69rem;

        color: #6c757d;

        text-transform: uppercase;

        font-weight: 700;
    }


    .beneficiary-detail {
        font-size: 0.82rem;

        margin-bottom: 6px;
    }


    /*
    |--------------------------------------------------------------------------
    | INFO GRID
    |--------------------------------------------------------------------------
    */

    .voucher-info-grid {
        display: grid;

        grid-template-columns:
            repeat(2, minmax(0, 1fr));

        gap: 8px;

        margin-top: 12px;
    }


    .voucher-info-box {
        border: 1px solid #dee2e6;

        border-radius: 7px;

        padding: 8px;

        min-width: 0;

        background: #fff;
    }


    /*
    |--------------------------------------------------------------------------
    | QR AREA
    |--------------------------------------------------------------------------
    */

    .voucher-qr-container {
        display: flex;

        flex-direction: column;

        align-items: center;

        justify-content: center;

        min-width: 105px;
    }


    .voucher-qr-container svg {
        width: 90px;
        height: 90px;

        display: block;
    }


    .voucher-code {
        font-size: 0.68rem;

        font-weight: 700;

        word-break: break-all;

        margin-top: 4px;
    }


    /*
    |--------------------------------------------------------------------------
    | FOOTER
    |--------------------------------------------------------------------------
    */

    .voucher-footer {
        border-top: 1px solid #dee2e6;

        margin-top: 12px;

        padding-top: 8px;

        display: flex;

        justify-content: space-between;

        gap: 10px;

        font-size: 0.7rem;

        color: #6c757d;
    }


    /*
    |--------------------------------------------------------------------------
    | EMPTY STATE
    |--------------------------------------------------------------------------
    */

    .empty-preview {
        border: 2px dashed #ced4da;

        border-radius: 14px;

        padding: 55px 20px;

        text-align: center;

        color: #6c757d;

        background: #f8f9fa;
    }


    /*
    |--------------------------------------------------------------------------
    | RESPONSIVE
    |--------------------------------------------------------------------------
    */

    @media (max-width: 1000px) {

        .voucher-preview-grid {
            grid-template-columns: 1fr;
        }

    }


    @media (max-width: 768px) {

        .voucher-info-grid {
            grid-template-columns: 1fr;
        }

    }


    /*
    |--------------------------------------------------------------------------
    | PRINT
    |--------------------------------------------------------------------------
    */

    @media print {

        @page {
            size: A4 portrait;
            margin: 8mm;
        }


        html,
        body {
            background: #ffffff !important;
        }


        /*
        |----------------------------------------------------------------------
        | Hide application interface
        |----------------------------------------------------------------------
        */

        .sidebar,
        .navbar,
        .app-sidebar,
        .no-print,
        .voucher-navigation,
        footer {
            display: none !important;
        }


        /*
        |----------------------------------------------------------------------
        | Page sizing
        |----------------------------------------------------------------------
        */

        .voucher-preview-page {
            max-width: none !important;

            width: 100% !important;

            margin: 0 !important;

            padding: 0 !important;
        }


        /*
        |----------------------------------------------------------------------
        | Remove application card decoration
        |----------------------------------------------------------------------
        */

        .card,
        .page-header-card {
            box-shadow: none !important;

            border: 0 !important;

            border-radius: 0 !important;
        }


        /*
        |----------------------------------------------------------------------
        | Voucher grid
        |----------------------------------------------------------------------
        */

        .voucher-preview-grid {
            display: grid !important;

            grid-template-columns:
                repeat(2, minmax(0, 1fr));

            gap: 7px;

            margin-top: 10px;
        }


        /*
        |----------------------------------------------------------------------
        | Voucher card
        |----------------------------------------------------------------------
        */

        .voucher-coupon {
            min-height: 0;

            padding: 9px;

            border-width: 1.5px;

            box-shadow: none !important;

            transform: none !important;
        }


        /*
        |----------------------------------------------------------------------
        | QR
        |----------------------------------------------------------------------
        */

        .voucher-qr-container {
            min-width: 86px;
        }


        .voucher-qr-container svg {
            width: 78px;
            height: 78px;
        }


        /*
        |----------------------------------------------------------------------
        | Header
        |----------------------------------------------------------------------
        */

        .official-header {
            margin-bottom: 10px;

            padding-bottom: 10px;
        }


        .operation-title {
            font-size: 1.2rem;
        }


        /*
        |----------------------------------------------------------------------
        | Summary
        |----------------------------------------------------------------------
        */

        .summary-card {
            padding: 8px;
        }


        /*
        |----------------------------------------------------------------------
        | Avoid page splitting
        |----------------------------------------------------------------------
        */

        .voucher-coupon,
        .voucher-info-box {
            break-inside: avoid;
            page-break-inside: avoid;
        }

    }

</style>


<div class="container-fluid py-4 voucher-preview-page">


    {{-- =====================================================================
         TOP NAVIGATION
         ===================================================================== --}}

    <div class="voucher-navigation no-print">

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">

            <div class="d-flex flex-wrap gap-2">

                {{-- Relief Operations --}}

                <a
                    href="{{ route('relief-vouchers.index') }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="bi bi-arrow-left me-1"></i>

                    Relief Operations
                </a>


                {{-- Operation Dashboard --}}

                <a
                    href="{{ route('relief-assistance.show', $operation) }}"
                    class="btn btn-outline-primary"
                >
                    <i class="bi bi-speedometer2 me-1"></i>

                    Operation Dashboard
                </a>


                {{-- Beneficiary Encoding --}}

                <a
                    href="{{ route('relief-assistance.beneficiaries', $operation) }}"
                    class="btn btn-outline-primary"
                >
                    <i class="bi bi-people-fill me-1"></i>

                    Beneficiary Encoding
                </a>

            </div>


            {{-- Print --}}

            @if($voucherCount > 0)

                <button
                    type="button"
                    class="btn btn-primary"
                    onclick="window.print()"
                >
                    <i class="bi bi-printer-fill me-1"></i>

                    Print Bulk Vouchers
                </button>

            @endif

        </div>

    </div>


    {{-- =====================================================================
         PAGE HEADER
         ===================================================================== --}}

    <div class="card page-header-card mb-4">

        <div class="card-body">

            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">

                <div>

                    <div class="text-uppercase small text-muted fw-semibold">
                        Voucher Generation
                    </div>


                    <h1 class="h3 fw-bold mb-1">
                        Preview & Print Bulk Vouchers
                    </h1>


                    <p class="text-muted mb-0">
                        Review the generated vouchers before printing and
                        distributing them.
                    </p>

                </div>


                <div class="text-md-end">

                    <div class="mb-2">

                        <span class="badge text-bg-primary fs-6 px-3 py-2">

                            {{ $voucherCount }}

                            {{
                                $voucherCount === 1
                                    ? 'Generated Voucher'
                                    : 'Generated Vouchers'
                            }}

                        </span>

                    </div>


                    <span class="badge text-bg-light border text-dark">

                        Operation:
                        {{ $operationCode }}

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

            <div class="fs-4">
                <i class="bi bi-qr-code"></i>
            </div>


            <div>

                <div class="fw-bold mb-1">
                    Voucher Generation Complete
                </div>


                <div class="small">

                    The vouchers shown below were generated for the
                    confirmed beneficiaries of this relief operation.

                    Each resident beneficiary has a unique voucher and
                    unique QR code.

                </div>


                <div class="small mt-2">

                    <strong>
                        Normal workflow:
                    </strong>

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

                <div class="summary-label">
                    Generated Vouchers
                </div>

                <div class="generated-summary-number">
                    {{ $voucherCount }}
                </div>

            </div>


            <div class="col-md-3">

                <div class="summary-label">
                    Classification
                </div>

                <div class="fw-semibold">
                    {{ $classificationLabel }}
                </div>

            </div>


            <div class="col-md-3">

                <div class="summary-label">
                    Assistance
                </div>

                <div class="fw-semibold">
                    {{ $assistanceType }}
                </div>

            </div>


            <div class="col-md-3">

                <div class="summary-label">
                    Operation Status
                </div>

                <div>

                    <span class="badge text-bg-secondary">

                        {{ $operationStatusLabel }}

                    </span>

                </div>

            </div>

        </div>

    </div>


    {{-- =====================================================================
         OFFICIAL BARANGAY DOCUMENT AREA
         ===================================================================== --}}

    <div class="card border-0 shadow-sm">

        <div class="card-body p-4">


            {{-- =================================================================
                 OFFICIAL HEADER
                 ================================================================= --}}

            <div class="official-header">

                <div class="barangay-name">
                    Barangay Poblacion
                </div>


                <div class="municipality">
                    Municipality of Bani
                </div>


                <div class="province">
                    Province of Pangasinan
                </div>


                <div class="operation-title">
                    {{ $operationName }}
                </div>


                <div class="operation-code">

                    Operation Code:

                    <strong>
                        {{ $operationCode }}
                    </strong>

                </div>

            </div>


            {{-- =================================================================
                 OPERATION INFORMATION
                 ================================================================= --}}

            <div class="row g-3 mb-4">


                {{-- Assistance Type --}}

                <div class="col-md-3">

                    <div class="summary-card">

                        <div class="summary-label">
                            Assistance Type
                        </div>


                        <div class="summary-value">
                            {{ $assistanceType }}
                        </div>

                    </div>

                </div>


                {{-- Distribution Date --}}

                <div class="col-md-3">

                    <div class="summary-card">

                        <div class="summary-label">
                            Distribution Date
                        </div>


                        <div class="summary-value">
                            {{ $distributionDate }}
                        </div>

                    </div>

                </div>


                {{-- Distribution Time --}}

                <div class="col-md-3">

                    <div class="summary-card">

                        <div class="summary-label">
                            Distribution Time
                        </div>


                        <div class="summary-value">
                            {{ $distributionTime }}
                        </div>

                    </div>

                </div>


                {{-- Distribution Location --}}

                <div class="col-md-3">

                    <div class="summary-card">

                        <div class="summary-label">
                            Distribution Location
                        </div>


                        <div class="summary-value">
                            {{ $distributionLocation }}
                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================================
                 EMPTY STATE
                 ================================================================= --}}

            @if($voucherCount === 0)

                <div class="empty-preview">

                    <div class="display-5 mb-3">
                        <i class="bi bi-qr-code"></i>
                    </div>


                    <h5 class="fw-bold">
                        No Generated Vouchers Found
                    </h5>


                    <p class="mb-4">

                        There are currently no active generated vouchers
                        for the confirmed beneficiaries of this operation.

                    </p>


                    <div class="d-flex flex-wrap justify-content-center gap-2">

                        <a
                            href="{{ route('relief-assistance.beneficiaries', $operation) }}"
                            class="btn btn-primary"
                        >
                            <i class="bi bi-people-fill me-1"></i>

                            Go to Beneficiary Encoding
                        </a>


                        <a
                            href="{{ route('relief-assistance.show', $operation) }}"
                            class="btn btn-outline-secondary"
                        >
                            <i class="bi bi-speedometer2 me-1"></i>

                            Operation Dashboard
                        </a>

                    </div>

                </div>


            @else


                {{-- =============================================================
                     PREVIEW SECTION HEADER
                     ============================================================= --}}

                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">

                    <div>

                        <h2 class="h5 fw-bold mb-1">
                            Generated Relief Vouchers
                        </h2>


                        <p class="text-muted small mb-0">

                            Each card represents one resident-level
                            beneficiary voucher.

                        </p>

                    </div>


                    <div class="small text-muted">

                        Total Generated:

                        <strong>
                            {{ $voucherCount }}
                        </strong>

                    </div>

                </div>


                {{-- =============================================================
                     VOUCHER GRID
                     ============================================================= --}}

                <div class="voucher-preview-grid">


                    @foreach($generatedVouchers as $index => $item)

                        @php

                            /*
                            |--------------------------------------------------------------------------
                            | CURRENT RECORDS
                            |--------------------------------------------------------------------------
                            */

                            $beneficiary = $item['beneficiary'];

                            $voucher = $item['voucher'];


                            /*
                            |--------------------------------------------------------------------------
                            | RESIDENT
                            |--------------------------------------------------------------------------
                            */

                            $resident = $beneficiary->resident
                                ?? null;


                            /*
                            |--------------------------------------------------------------------------
                            | HOUSEHOLD
                            |--------------------------------------------------------------------------
                            */

                            $household = $beneficiary->household
                                ?? null;

                            $householdHead = $household?->householdHead;


                            /*
                            |--------------------------------------------------------------------------
                            | RESIDENT NAME
                            |--------------------------------------------------------------------------
                            */

                            $residentName = $resident
                                ? trim(
                                    $resident->first_name
                                    . ' '
                                    . (
                                        $resident->middle_name
                                            ? $resident->middle_name . ' '
                                            : ''
                                    )
                                    . $resident->last_name
                                    . (
                                        $resident->suffix
                                            ? ' ' . $resident->suffix
                                            : ''
                                    )
                                )
                                : '—';


                            /*
                            |--------------------------------------------------------------------------
                            | HOUSEHOLD HEAD
                            |--------------------------------------------------------------------------
                            */

                            $headName = $householdHead
                                ? trim(
                                    $householdHead->first_name
                                    . ' '
                                    . (
                                        $householdHead->middle_name
                                            ? $householdHead->middle_name . ' '
                                            : ''
                                    )
                                    . $householdHead->last_name
                                    . (
                                        $householdHead->suffix
                                            ? ' ' . $householdHead->suffix
                                            : ''
                                    )
                                )
                                : '—';


                            /*
                            |--------------------------------------------------------------------------
                            | HOUSEHOLD DETAILS
                            |--------------------------------------------------------------------------
                            */

                            $householdNumber =
                                $household?->household_number
                                ?? '—';

                            $purok =
                                $household?->purok
                                ?? '—';

                            $address =
                                $household?->address
                                ?? '—';


                            /*
                            |--------------------------------------------------------------------------
                            | RELATIONSHIP
                            |--------------------------------------------------------------------------
                            */

                            $relationship =
                                $beneficiary->relationship
                                ?? 'Beneficiary';


                            /*
                            |--------------------------------------------------------------------------
                            | QUANTITY
                            |--------------------------------------------------------------------------
                            */

                            $assistanceQuantity =
                                $voucher->allocated_quantity
                                ?? $beneficiary->allocated_quantity
                                ?? 1;


                            /*
                            |--------------------------------------------------------------------------
                            | VOUCHER STATUS
                            |--------------------------------------------------------------------------
                            */

                            $voucherStatus = strtolower(
                                (string) (
                                    $voucher->status
                                    ?? 'generated'
                                )
                            );


                            $voucherStatusLabel = ucwords(
                                str_replace(
                                    '_',
                                    ' ',
                                    $voucherStatus
                                )
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | STATUS BADGE
                            |--------------------------------------------------------------------------
                            */

                            $statusClass = match ($voucherStatus) {

                                'generated' =>
                                    'text-bg-warning',

                                'issued' =>
                                    'text-bg-success',

                                'distributed' =>
                                    'text-bg-primary',

                                'claimed' =>
                                    'text-bg-secondary',

                                'cancelled' =>
                                    'text-bg-danger',

                                default =>
                                    'text-bg-dark',
                            };


                            /*
                            |--------------------------------------------------------------------------
                            | VOUCHER CODE
                            |--------------------------------------------------------------------------
                            */

                            $voucherCode = trim(
                                (string) (
                                    $voucher->voucher_code
                                    ?? ''
                                )
                            );

                        @endphp


                        {{-- =====================================================
                             SINGLE VOUCHER
                             ===================================================== --}}

                        <div class="voucher-coupon">


                            {{-- =================================================
                                 VOUCHER HEADER
                                 ================================================= --}}

                            <div class="voucher-coupon-header">

                                <div>

                                    <div class="voucher-title">
                                        Relief Assistance Voucher
                                    </div>


                                    <div class="voucher-number">
                                        Voucher #{{ $index + 1 }}
                                    </div>

                                </div>


                                <span
                                    class="badge {{ $statusClass }} align-self-start"
                                >
                                    {{ $voucherStatusLabel }}
                                </span>

                            </div>


                            {{-- =================================================
                                 BENEFICIARY
                                 ================================================= --}}

                            <div class="beneficiary-label">
                                Beneficiary
                            </div>


                            <div class="beneficiary-name">
                                {{ $residentName }}
                            </div>


                            <div class="beneficiary-detail">

                                <strong>
                                    Classification:
                                </strong>

                                {{ $classificationLabel }}

                            </div>


                            <div class="beneficiary-detail">

                                <strong>
                                    Relationship:
                                </strong>

                                {{ $relationship }}

                            </div>


                            {{-- =================================================
                                 HOUSEHOLD
                                 ================================================= --}}

                            <div class="beneficiary-detail">

                                <strong>
                                    Household:
                                </strong>

                                {{ $householdNumber }}

                            </div>


                            <div class="beneficiary-detail">

                                <strong>
                                    Household Head:
                                </strong>

                                {{ $headName }}

                            </div>


                            <div class="beneficiary-detail">

                                <strong>
                                    Purok:
                                </strong>

                                {{ $purok }}

                            </div>


                            <div class="beneficiary-detail">

                                <strong>
                                    Address:
                                </strong>

                                {{ $address }}

                            </div>


                            {{-- =================================================
                                 VOUCHER INFORMATION
                                 ================================================= --}}

                            <div class="voucher-info-grid">


                                {{-- Assistance --}}

                                <div class="voucher-info-box">

                                    <div class="beneficiary-label">
                                        Assistance
                                    </div>


                                    <div class="fw-semibold small">
                                        {{ $assistanceType }}
                                    </div>

                                </div>


                                {{-- Quantity --}}

                                <div class="voucher-info-box">

                                    <div class="beneficiary-label">
                                        Quantity
                                    </div>


                                    <div class="fw-semibold small">

                                        {{ $assistanceQuantity }}

                                        {{ $quantityUnit }}

                                    </div>

                                </div>


                                {{-- Distribution Date --}}

                                <div class="voucher-info-box">

                                    <div class="beneficiary-label">
                                        Distribution
                                    </div>


                                    <div class="fw-semibold small">
                                        {{ $distributionDate }}
                                    </div>

                                </div>


                                {{-- Time --}}

                                <div class="voucher-info-box">

                                    <div class="beneficiary-label">
                                        Time
                                    </div>


                                    <div class="fw-semibold small">
                                        {{ $distributionTime }}
                                    </div>

                                </div>

                            </div>


                            {{-- =================================================
                                 QR + CODE
                                 ================================================= --}}

                            <div class="d-flex justify-content-between align-items-end gap-3 mt-3">


                                {{-- Voucher Details --}}

                                <div class="small text-muted flex-grow-1">

                                    <div>

                                        <strong>
                                            Operation:
                                        </strong>

                                        {{ $operationCode }}

                                    </div>


                                    <div>

                                        <strong>
                                            Location:
                                        </strong>

                                        {{ $distributionLocation }}

                                    </div>


                                    <div class="mt-2">

                                        <strong>
                                            Voucher Code:
                                        </strong>


                                        @if($voucherCode !== '')

                                            <div class="voucher-code">
                                                {{ $voucherCode }}
                                            </div>

                                        @else

                                            <div class="text-danger small">
                                                Voucher code unavailable.
                                            </div>

                                        @endif

                                    </div>

                                </div>


                                {{-- QR CODE --}}

                                <div class="voucher-qr-container text-center">


                                    @if($voucherCode !== '')

                                        <div class="beneficiary-label mb-1">
                                            Scan QR
                                        </div>


                                        {!! \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                                            ->size(90)
                                            ->margin(1)
                                            ->errorCorrection('M')
                                            ->generate($voucherCode) !!}


                                    @else

                                        <div class="border rounded p-2 text-danger small">
                                            QR unavailable
                                        </div>

                                    @endif

                                </div>

                            </div>


                            {{-- =================================================
                                 VOUCHER ACTIONS
                                 ================================================= --}}

                            <div class="d-flex justify-content-between align-items-center gap-2 mt-3 no-print">

                                <span class="small text-muted">

                                    <i class="bi bi-shield-check me-1"></i>

                                    Unique voucher

                                </span>


                                @if($voucher->id)

                                    <a
                                        href="{{ route('relief-vouchers.show', $voucher) }}"
                                        class="btn btn-sm btn-outline-primary"
                                    >

                                        <i class="bi bi-eye me-1"></i>

                                        View Voucher

                                    </a>

                                @endif

                            </div>


                            {{-- =================================================
                                 FOOTER
                                 ================================================= --}}

                            <div class="voucher-footer">

                                <span>
                                    For official barangay distribution use
                                </span>


                                <span>
                                    Poblacion RMS
                                </span>

                            </div>

                        </div>

                    @endforeach

                </div>


                {{-- =============================================================
                     PRINT NOTICE
                     ============================================================= --}}

                <div class="alert alert-warning no-print mt-4 mb-0">

                    <div class="d-flex gap-2">

                        <i class="bi bi-printer-fill fs-5"></i>


                        <div class="small">

                            <strong>
                                Before printing:
                            </strong>

                            Verify every generated voucher's:

                            beneficiary,
                            household information,
                            assistance details,
                            voucher code,
                            and QR code.

                            <br><br>

                            The system does not automatically print.
                            Click

                            <strong>
                                Print Bulk Vouchers
                            </strong>

                            when everything is correct.

                        </div>

                    </div>

                </div>


                {{-- =============================================================
                     BOTTOM NAVIGATION
                     ============================================================= --}}

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-4 no-print">


                    <div class="d-flex flex-wrap gap-2">

                        <a
                            href="{{ route('relief-assistance.beneficiaries', $operation) }}"
                            class="btn btn-outline-secondary"
                        >

                            <i class="bi bi-arrow-left me-1"></i>

                            Back to Beneficiaries

                        </a>


                        <a
                            href="{{ route('relief-assistance.show', $operation) }}"
                            class="btn btn-outline-primary"
                        >

                            <i class="bi bi-speedometer2 me-1"></i>

                            Operation Dashboard

                        </a>

                    </div>


                    <button
                        type="button"
                        class="btn btn-primary"
                        onclick="window.print()"
                    >

                        <i class="bi bi-printer-fill me-1"></i>

                        Print Bulk Vouchers

                    </button>

                </div>

            @endif

        </div>

    </div>

</div>

@endsection
