@extends('layouts.app')

@section('title', 'Emergency Hotline Details | e-Poblacion')

@push('styles')

<style>
    /* =========================================================
       EMERGENCY HOTLINE DETAILS - THEME VARIABLES
    ========================================================== */

    .hotline-show-page {
        --hotline-bg: #ffffff;
        --hotline-surface: #ffffff;
        --hotline-surface-soft: #f8fafc;
        --hotline-border: #dee2e6;
        --hotline-text: #212529;
        --hotline-heading: #111827;
        --hotline-muted: #6c757d;
        --hotline-label: #6b7280;
        --hotline-primary: #5b3df0;
        --hotline-primary-soft: rgba(91, 61, 240, .12);
        --hotline-danger: #dc3545;
        --hotline-success: #198754;
        --hotline-secondary: #6c757d;
        --hotline-shadow: 0 8px 24px rgba(31, 38, 72, .06),
                          0 2px 8px rgba(31, 38, 72, .03);

        color: var(--hotline-text);
    }

    /* =========================================================
       DARK THEME
    ========================================================== */

    html[data-theme="dark"] .hotline-show-page,
    body[data-theme="dark"] .hotline-show-page {
        --hotline-bg: #0f172a;
        --hotline-surface: #1e293b;
        --hotline-surface-soft: #111827;
        --hotline-border: #475569;
        --hotline-text: #f1f5f9;
        --hotline-heading: #ffffff;
        --hotline-muted: #cbd5e1;
        --hotline-label: #cbd5e1;
        --hotline-primary: #a78bfa;
        --hotline-primary-soft: rgba(167, 139, 250, .16);
        --hotline-danger: #f87171;
        --hotline-success: #4ade80;
        --hotline-secondary: #cbd5e1;
        --hotline-shadow: 0 10px 30px rgba(0, 0, 0, .25);
    }

    /* =========================================================
       PAGE HEADER
    ========================================================== */

    .hotline-page-header {
        margin-bottom: 24px;
    }

    .hotline-page-header .small,
    .hotline-page-header p {
        color: var(--hotline-muted) !important;
    }

    .hotline-page-header h1 {
        color: var(--hotline-heading) !important;
    }

    /* =========================================================
       CARDS
    ========================================================== */

    .hotline-card {
        border: 1px solid var(--hotline-border) !important;
        border-radius: 16px;
        background: var(--hotline-surface) !important;
        color: var(--hotline-text) !important;
        box-shadow: var(--hotline-shadow);
        overflow: hidden;
    }

    .hotline-card-header {
        padding: 22px 24px;
        border-bottom: 1px solid var(--hotline-border) !important;
        background: var(--hotline-surface) !important;
        color: var(--hotline-text) !important;
    }

    .hotline-card-body {
        padding: 24px;
        background: var(--hotline-surface) !important;
        color: var(--hotline-text) !important;
    }

    /* =========================================================
       HOTLINE HEADER
    ========================================================== */

    .hotline-icon {
        width: 58px;
        height: 58px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 15px;
        background: var(--hotline-primary-soft);
        color: var(--hotline-primary);
        font-size: 25px;
        flex-shrink: 0;
    }

    .hotline-name {
        color: var(--hotline-heading) !important;
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .hotline-category {
        color: var(--hotline-muted) !important;
        font-size: 13px;
    }

    /* =========================================================
       INFORMATION ITEMS
    ========================================================== */

    .info-item {
        padding: 15px 0;
        border-bottom: 1px solid var(--hotline-border);
    }

    .info-item:last-child {
        border-bottom: 0;
    }

    .info-label {
        color: var(--hotline-label) !important;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .5px;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .info-value {
        color: var(--hotline-text) !important;
        font-size: 14px;
        font-weight: 600;
        word-break: break-word;
    }

    /* =========================================================
       PHONE NUMBER
    ========================================================== */

    .phone-number {
        color: var(--hotline-primary) !important;
        font-size: 18px;
        font-weight: 800;
        text-decoration: none;
    }

    .phone-number:hover {
        color: var(--hotline-primary) !important;
        text-decoration: underline;
    }

    /* =========================================================
       DESCRIPTION
    ========================================================== */

    .description-box {
        padding: 16px;
        border: 1px solid var(--hotline-border);
        border-radius: 12px;
        background: var(--hotline-surface-soft);
        color: var(--hotline-text) !important;
        font-size: 14px;
        line-height: 1.7;
        white-space: pre-line;
    }

    /* =========================================================
       RECORD INFORMATION
    ========================================================== */

    .hotline-card-header h2 {
        color: var(--hotline-heading) !important;
    }

    .audit-item {
        padding: 12px 0;
        border-bottom: 1px solid var(--hotline-border);
    }

    .audit-item:last-child {
        border-bottom: 0;
    }

    .audit-label {
        color: var(--hotline-label) !important;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .audit-value {
        color: var(--hotline-text) !important;
        font-size: 13px;
        font-weight: 600;
        margin-top: 3px;
    }

    /* =========================================================
       QUICK ACTION TEXT
    ========================================================== */

    .hotline-card h3 {
        color: var(--hotline-heading) !important;
    }

    .hotline-card p.text-muted {
        color: var(--hotline-muted) !important;
    }

    /* =========================================================
       BOOTSTRAP TEXT OVERRIDES
    ========================================================== */

    .hotline-show-page .text-primary {
        color: var(--hotline-primary) !important;
    }

    .hotline-show-page .text-muted {
        color: var(--hotline-muted) !important;
    }

    .hotline-show-page .text-secondary {
        color: var(--hotline-secondary) !important;
    }

    .hotline-show-page .text-danger {
        color: var(--hotline-danger) !important;
    }

    .hotline-show-page .text-success {
        color: var(--hotline-success) !important;
    }

    /* Prevent Bootstrap dark text utilities from becoming invisible */
    .hotline-show-page .text-dark {
        color: var(--hotline-text) !important;
    }

    .hotline-show-page .text-body {
        color: var(--hotline-text) !important;
    }

    /* =========================================================
       OUTLINE SECONDARY BUTTON
    ========================================================== */

    .hotline-show-page .btn-outline-secondary {
        --bs-btn-color: var(--hotline-muted);
        --bs-btn-border-color: var(--hotline-border);
        --bs-btn-hover-color: var(--hotline-heading);
        --bs-btn-hover-bg: var(--hotline-surface-soft);
        --bs-btn-hover-border-color: var(--hotline-muted);
    }

    /* =========================================================
       PRIMARY BUTTON
    ========================================================== */

    .hotline-show-page .btn-primary {
        --bs-btn-bg: #5b3df0;
        --bs-btn-border-color: #5b3df0;
        --bs-btn-hover-bg: #4930d0;
        --bs-btn-hover-border-color: #4930d0;
        --bs-btn-color: #ffffff;
        --bs-btn-hover-color: #ffffff;
    }

    /* =========================================================
       SUCCESS BUTTONS
    ========================================================== */

    .hotline-show-page .btn-success {
        --bs-btn-color: #ffffff;
    }

    /* =========================================================
       BADGES
    ========================================================== */

    .hotline-show-page .badge.text-bg-success {
        color: #ffffff !important;
    }

    .hotline-show-page .badge.text-bg-secondary {
        color: #ffffff !important;
    }

    /* =========================================================
       ACTION BUTTON
    ========================================================== */

    .action-button {
        min-width: 110px;
    }

    /* =========================================================
       RESPONSIVE
    ========================================================== */

    @media (max-width: 576px) {

        .hotline-card-header,
        .hotline-card-body {
            padding: 18px;
        }

        .hotline-name {
            font-size: 19px;
        }

        .action-button {
            width: 100%;
        }

        .hotline-card-header .d-flex {
            align-items: flex-start !important;
        }

        .hotline-card-header .badge {
            margin-left: auto;
        }
    }
</style>

@endpush

@section('content')

<div class="hotline-show-page">

{{-- =========================================================
     PAGE HEADER
========================================================== --}}

<div class="hotline-page-header">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">

        <div>

            <div class="text-muted small mb-1">
                Emergency Hotline Directory
            </div>

            <h1 class="h3 fw-bold mb-1">
                Hotline Details
            </h1>

            <p class="text-muted mb-0">
                View the complete information and contact details of this emergency service.
            </p>

        </div>

        <div class="d-flex flex-wrap gap-2">

            <a
                href="{{ route('emergency-hotlines.index') }}"
                class="btn btn-outline-secondary action-button"
            >
                <i class="bi bi-arrow-left me-1"></i>
                Back
            </a>

            <a
                href="{{ route('emergency-hotlines.edit', $emergencyHotline) }}"
                class="btn btn-primary action-button"
            >
                <i class="bi bi-pencil-square me-1"></i>
                Edit
            </a>

        </div>

    </div>

</div>


{{-- =========================================================
     MAIN CONTENT
========================================================== --}}

<div class="row g-4">

    {{-- =====================================================
         HOTLINE INFORMATION
    ====================================================== --}}

    <div class="col-lg-8">

        <div class="card hotline-card">

            <div class="hotline-card-header">

                <div class="d-flex align-items-center gap-3">

                    <div class="hotline-icon">
                        <i class="bi bi-telephone-fill"></i>
                    </div>

                    <div class="flex-grow-1">

                        <div class="hotline-name">
                            {{ $emergencyHotline->name }}
                        </div>

                        <div class="hotline-category">
                            <i class="bi bi-tag-fill me-1"></i>
                            {{ $emergencyHotline->category_label }}
                        </div>

                    </div>

                    @if ($emergencyHotline->isActive())

                        <span class="badge text-bg-success px-3 py-2">
                            <i class="bi bi-check-circle-fill me-1"></i>
                            Active
                        </span>

                    @else

                        <span class="badge text-bg-secondary px-3 py-2">
                            <i class="bi bi-pause-circle-fill me-1"></i>
                            Inactive
                        </span>

                    @endif

                </div>

            </div>


            <div class="hotline-card-body">

                {{-- PRIMARY NUMBER --}}

                <div class="info-item">

                    <div class="info-label">
                        Primary Contact Number
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-3">

                        <a
                            href="tel:{{ $emergencyHotline->phone_number }}"
                            class="phone-number"
                        >
                            <i class="bi bi-telephone-fill me-2"></i>
                            {{ $emergencyHotline->phone_number }}
                        </a>

                        @if ($emergencyHotline->isActive())

                            <a
                                href="tel:{{ $emergencyHotline->phone_number }}"
                                class="btn btn-success btn-sm"
                            >
                                <i class="bi bi-telephone-outbound-fill me-1"></i>
                                Call
                            </a>

                        @endif

                    </div>

                </div>


                {{-- ALTERNATE NUMBER --}}

                @if ($emergencyHotline->alternate_number)

                    <div class="info-item">

                        <div class="info-label">
                            Alternate Contact Number
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-3">

                            <a
                                href="tel:{{ $emergencyHotline->alternate_number }}"
                                class="phone-number"
                            >
                                <i class="bi bi-telephone me-2"></i>
                                {{ $emergencyHotline->alternate_number }}
                            </a>

                            @if ($emergencyHotline->isActive())

                                <a
                                    href="tel:{{ $emergencyHotline->alternate_number }}"
                                    class="btn btn-outline-success btn-sm"
                                >
                                    <i class="bi bi-telephone-outbound me-1"></i>
                                    Call
                                </a>

                            @endif

                        </div>

                    </div>

                @endif


                {{-- CONTACT PERSON --}}

                @if ($emergencyHotline->contact_person)

                    <div class="info-item">

                        <div class="info-label">
                            Contact Person
                        </div>

                        <div class="info-value">

                            <i class="bi bi-person-fill me-2 text-primary"></i>

                            {{ $emergencyHotline->contact_person }}

                        </div>

                    </div>

                @endif


                {{-- ADDRESS --}}

                @if ($emergencyHotline->address)

                    <div class="info-item">

                        <div class="info-label">
                            Office / Station Address
                        </div>

                        <div class="info-value">

                            <i class="bi bi-geo-alt-fill me-2 text-danger"></i>

                            {{ $emergencyHotline->address }}

                        </div>

                    </div>

                @endif


                {{-- DESCRIPTION --}}

                @if ($emergencyHotline->description)

                    <div class="info-item">

                        <div class="info-label">
                            Additional Information
                        </div>

                        <div class="description-box">
                            {{ $emergencyHotline->description }}
                        </div>

                    </div>

                @endif

            </div>

        </div>

    </div>


    {{-- =====================================================
         RECORD INFORMATION
    ====================================================== --}}

    <div class="col-lg-4">

        <div class="card hotline-card">

            <div class="hotline-card-header">

                <h2 class="h6 fw-bold mb-0">

                    <i class="bi bi-info-circle-fill text-primary me-2"></i>

                    Record Information

                </h2>

            </div>


            <div class="hotline-card-body">

                {{-- STATUS --}}

                <div class="audit-item">

                    <div class="audit-label">
                        Status
                    </div>

                    <div class="audit-value">

                        @if ($emergencyHotline->isActive())

                            <span class="badge text-bg-success">
                                Active
                            </span>

                        @else

                            <span class="badge text-bg-secondary">
                                Inactive
                            </span>

                        @endif

                    </div>

                </div>


                {{-- CREATED BY --}}

                <div class="audit-item">

                    <div class="audit-label">
                        Created By
                    </div>

                    <div class="audit-value">

                        @if ($emergencyHotline->creator)

                            {{ $emergencyHotline->creator->name }}

                        @else

                            System

                        @endif

                    </div>

                </div>


                {{-- CREATED DATE --}}

                <div class="audit-item">

                    <div class="audit-label">
                        Date Created
                    </div>

                    <div class="audit-value">

                        {{ $emergencyHotline->created_at?->format('F d, Y h:i A') ?? 'N/A' }}

                    </div>

                </div>


                {{-- UPDATED BY --}}

                <div class="audit-item">

                    <div class="audit-label">
                        Last Updated By
                    </div>

                    <div class="audit-value">

                        @if ($emergencyHotline->updater)

                            {{ $emergencyHotline->updater->name }}

                        @else

                            No updates recorded

                        @endif

                    </div>

                </div>


                {{-- UPDATED DATE --}}

                <div class="audit-item">

                    <div class="audit-label">
                        Last Updated
                    </div>

                    <div class="audit-value">

                        {{ $emergencyHotline->updated_at?->format('F d, Y h:i A') ?? 'N/A' }}

                    </div>

                </div>

            </div>

        </div>


        {{-- =================================================
             QUICK ACTION
        ================================================== --}}

        @if ($emergencyHotline->isActive())

            <div class="card hotline-card mt-4">

                <div class="hotline-card-body">

                    <div class="d-flex align-items-start gap-3">

                        <div class="text-success fs-4">
                            <i class="bi bi-shield-check"></i>
                        </div>

                        <div>

                            <h3 class="h6 fw-bold mb-1">
                                Active Emergency Contact
                            </h3>

                            <p class="small text-muted mb-3">
                                This hotline is currently available in the
                                emergency directory.
                            </p>

                            <a
                                href="tel:{{ $emergencyHotline->phone_number }}"
                                class="btn btn-success btn-sm"
                            >
                                <i class="bi bi-telephone-fill me-1"></i>
                                Call {{ $emergencyHotline->name }}
                            </a>

                        </div>

                    </div>

                </div>

            </div>

        @else

            <div class="card hotline-card mt-4">

                <div class="hotline-card-body">

                    <div class="d-flex align-items-start gap-3">

                        <div class="text-secondary fs-4">
                            <i class="bi bi-pause-circle-fill"></i>
                        </div>

                        <div>

                            <h3 class="h6 fw-bold mb-1">
                                Hotline Inactive
                            </h3>

                            <p class="small text-muted mb-0">
                                This emergency contact is currently
                                unavailable in the active directory.
                            </p>

                        </div>

                    </div>

                </div>

            </div>

        @endif

    </div>

</div>

</div>

@endsection
