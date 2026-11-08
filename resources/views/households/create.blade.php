@extends('layouts.app')

@section('title', 'Add Household | e-Poblacion')

@push('styles')
<style>
    .household-create-page {
        max-width: 1500px;
        margin: 0 auto;
    }

    .breadcrumb-text {
        font-size: .78rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .page-title {
        margin: 0;
        font-size: 2rem;
        font-weight: 800;
        color: #1f2937;
    }

    .page-subtitle {
        margin: 8px 0 0;
        color: #6b7280;
        line-height: 1.6;
    }

    .workflow-card,
    .section-card {
        border: 1px solid rgba(15, 23, 42, .08);
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 6px 20px rgba(15, 23, 42, .05);
    }

    .workflow-card {
        padding: 20px;
    }

    .workflow-step {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 58px;
    }

    .workflow-number {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #eef2ff;
        color: #2563eb;
        font-weight: 800;
        flex-shrink: 0;
    }

    .workflow-step.active .workflow-number {
        background: #2563eb;
        color: #fff;
    }

    .workflow-step.complete .workflow-number {
        background: #16a34a;
        color: #fff;
    }

    .workflow-title {
        font-weight: 800;
        color: #1f2937;
        font-size: .9rem;
    }

    .workflow-text {
        color: #6b7280;
        font-size: .76rem;
    }

    .workflow-line {
        height: 2px;
        background: #e5e7eb;
        flex: 1;
        min-width: 25px;
    }

    .section-card {
        padding: 24px;
    }

    .section-header {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        margin-bottom: 22px;
    }

    .section-icon {
        width: 48px;
        height: 48px;
        min-width: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, .1);
        color: #2563eb;
        font-size: 1.25rem;
    }

    .section-header h4 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 800;
        color: #1f2937;
    }

    .section-header p {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: .84rem;
        line-height: 1.5;
    }

    .form-label {
        color: #374151;
        font-size: .84rem;
        font-weight: 700;
    }

    .form-control,
    .form-select {
        min-height: 46px;
        border-radius: 10px;
    }

    textarea.form-control {
        min-height: 100px;
    }

    .head-panel {
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        padding: 18px;
        background: #f8fafc;
    }

    .head-panel.has-head {
        border-style: solid;
        border-color: rgba(37, 99, 235, .35);
        background: rgba(37, 99, 235, .04);
    }

    .person-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 14px;
        background: #fff;
    }

    .person-name {
        font-weight: 800;
        color: #1f2937;
    }

    .person-meta {
        color: #6b7280;
        font-size: .78rem;
    }

    .search-results {
        position: relative;
        z-index: 30;
        margin-top: 6px;
        max-height: 280px;
        overflow: auto;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        box-shadow: 0 12px 28px rgba(15, 23, 42, .12);
    }

    .search-result {
        display: block;
        width: 100%;
        border: 0;
        border-bottom: 1px solid #f1f5f9;
        background: #fff;
        text-align: left;
        padding: 12px 14px;
        cursor: pointer;
    }

    .search-result:last-child {
        border-bottom: 0;
    }

    .search-result:hover {
        background: #f8fbff;
    }

    .member-table-wrap {
        overflow-x: auto;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
    }

    .member-table {
        min-width: 1150px;
        margin: 0;
    }

    .member-table th {
        background: #f8fafc;
        color: #4b5563;
        font-size: .75rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .member-table td {
        vertical-align: middle;
        font-size: .8rem;
    }

    .member-table > :not(caption) > * > * {
        padding: 11px 12px;
    }

    .head-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 999px;
        background: #dbeafe;
        color: #1d4ed8;
        font-size: .7rem;
        font-weight: 800;
    }

    .new-badge {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 999px;
        background: #dcfce7;
        color: #166534;
        font-size: .7rem;
        font-weight: 800;
    }

    .existing-badge {
        display: inline-flex;
        padding: 4px 8px;
        border-radius: 999px;
        background: #e0e7ff;
        color: #3730a3;
        font-size: .7rem;
        font-weight: 800;
    }

    .status-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #166534;
        font-size: .8rem;
        font-weight: 700;
    }

    .status-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: #22c55e;
    }

    .notice-box {
        display: flex;
        gap: 10px;
        padding: 14px 16px;
        border-radius: 12px;
        background: #eff6ff;
        color: #475569;
        font-size: .82rem;
        line-height: 1.55;
    }

    .notice-box i {
        color: #2563eb;
        margin-top: 2px;
    }

    .modal-content {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 18px 60px rgba(15, 23, 42, .2);
    }

    .modal-header {
        border-bottom: 1px solid #e5e7eb;
        padding: 18px 20px;
    }

    .modal-body {
        padding: 20px;
    }

    .modal-footer {
        border-top: 1px solid #e5e7eb;
        padding: 14px 20px;
    }

    .selection-banner {
        border-radius: 12px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px solid #e5e7eb;
    }

    .selection-banner strong {
        color: #1f2937;
    }

    .required-note {
        color: #64748b;
        font-size: .75rem;
    }

    /* Philippine contact number: fixed +63 prefix + 10 digits */
    .contact-prefix {
        min-width: 56px;
        justify-content: center;
        font-weight: 800;
        letter-spacing: .02em;
        color: #1f2937;
        background: #f1f5f9;
    }

    .contact-group > .form-control {
        font-variant-numeric: tabular-nums;
        letter-spacing: .06em;
    }

    html[data-theme="dark"] .contact-prefix {
        color: #e5e7eb;
        background: #1f2937;
        border-color: rgba(148, 163, 184, .18);
    }

    .empty-members {
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
        padding: 30px 18px;
        text-align: center;
        color: #64748b;
    }

    .gps-box {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 16px;
        background: #f8fafc;
    }

    .readonly-address-box {
        border: 1px solid #dbeafe;
        border-radius: 12px;
        padding: 12px 14px;
        background: #eff6ff;
        color: #475569;
        font-size: .82rem;
        line-height: 1.5;
    }

    .readonly-address-box strong {
        color: #1d4ed8;
    }

    .occupation-other-wrap {
        display: none;
    }

    .occupation-other-wrap.show {
        display: block;
    }

    .student-level-wrap {
        display: none;
    }

    .student-level-wrap.show {
        display: block;
    }

    /* ============================================================
       GPS CAPTURE + MAP PICKER
    ============================================================ */

    .geo-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .geo-actions .btn {
        min-height: 44px;
        border-radius: 10px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .geo-feedback {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-top: 14px;
        padding: 12px 14px;
        border: 1px solid transparent;
        border-radius: 12px;
        font-size: .82rem;
        line-height: 1.5;
    }

    .geo-feedback > i,
    .geo-feedback > .spinner-border {
        flex-shrink: 0;
        margin-top: 2px;
    }

    .geo-feedback.is-info {
        background: #eff6ff;
        border-color: #bfdbfe;
        color: #1e3a8a;
    }

    .geo-feedback.is-success {
        background: #f0fdf4;
        border-color: #bbf7d0;
        color: #166534;
    }

    .geo-feedback.is-warn {
        background: #fffbeb;
        border-color: #fde68a;
        color: #92400e;
    }

    .geo-feedback.is-error {
        background: #fef2f2;
        border-color: #fecaca;
        color: #991b1b;
    }

    .geo-inline-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-top: 8px;
    }

    .geo-summary {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-top: 16px;
        padding: 14px 16px;
        border: 1px dashed #cbd5e1;
        border-radius: 12px;
        background: #ffffff;
    }

    .geo-summary-icon {
        width: 40px;
        height: 40px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        background: #e2e8f0;
        color: #475569;
        font-size: 1.1rem;
    }

    .geo-summary[data-state="ok"] {
        border-style: solid;
        border-color: #86efac;
        background: #f0fdf4;
    }

    .geo-summary[data-state="ok"] .geo-summary-icon {
        background: #16a34a;
        color: #ffffff;
    }

    .geo-summary[data-state="warn"] {
        border-style: solid;
        border-color: #fcd34d;
        background: #fffbeb;
    }

    .geo-summary[data-state="warn"] .geo-summary-icon {
        background: #d97706;
        color: #ffffff;
    }

    .geo-summary[data-state="invalid"] {
        border-style: solid;
        border-color: #fca5a5;
        background: #fef2f2;
    }

    .geo-summary[data-state="invalid"] .geo-summary-icon {
        background: #dc2626;
        color: #ffffff;
    }

    .geo-summary-title {
        font-weight: 800;
        font-size: .86rem;
        color: #1f2937;
    }

    .geo-summary-coords {
        margin-top: 2px;
        font-family: Consolas, "Courier New", monospace;
        font-size: 1rem;
        font-weight: 700;
        color: #111827;
        overflow-wrap: anywhere;
    }

    .geo-summary-meta {
        margin-top: 4px;
        font-size: .78rem;
        line-height: 1.5;
        color: #64748b;
    }

    .geo-input {
        font-family: Consolas, "Courier New", monospace;
    }

    .picker-wrap {
        position: relative;
        overflow: hidden;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
    }

    .picker-map {
        height: min(62vh, 560px);
        min-height: 380px;
        background: #dfe5ea;
    }

    .picker-tools {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 1100;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .picker-tools button {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #cbd5e1;
        border-radius: 11px;
        background: #ffffff;
        color: #1f2937;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .25);
        cursor: pointer;
        transition: background .15s, border-color .15s, color .15s, transform .15s;
    }

    .picker-tools button:hover {
        background: #eff6ff;
        border-color: #2563eb;
        color: #2563eb;
        transform: translateY(-1px);
    }

    .picker-tools button:disabled {
        opacity: .45;
        cursor: not-allowed;
        transform: none;
    }

    .picker-hint {
        position: absolute;
        left: 12px;
        bottom: 12px;
        z-index: 1100;
        max-width: calc(100% - 90px);
        padding: 7px 12px;
        border-radius: 999px;
        background: rgba(15, 23, 42, .85);
        color: #ffffff;
        font-size: .76rem;
        font-weight: 600;
        pointer-events: none;
    }

    .picker-error {
        padding: 40px 20px;
        text-align: center;
        color: #991b1b;
    }

    .picker-readout {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 28px;
        margin-top: 14px;
        padding: 12px 16px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #f8fafc;
    }

    .picker-readout span {
        display: block;
        color: #64748b;
        font-size: .72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .picker-readout b {
        font-family: Consolas, "Courier New", monospace;
        font-size: .95rem;
        color: #111827;
    }

    .picker-status {
        flex: 1 1 100%;
        font-size: .78rem;
        color: #64748b;
    }

    .geo-pin-wrap {
        background: none;
        border: 0;
    }

    .geo-pin {
        position: relative;
        display: block;
        width: 30px;
        height: 30px;
        border: 3px solid #ffffff;
        border-radius: 50% 50% 50% 0;
        background: #dc2626;
        transform: rotate(-45deg);
        box-shadow: 0 2px 9px rgba(0, 0, 0, .55), 0 0 0 2px #dc2626;
    }

    .geo-pin::after {
        content: "";
        position: absolute;
        width: 8px;
        height: 8px;
        left: 8px;
        top: 8px;
        border-radius: 50%;
        background: #ffffff;
    }

    .picker-label {
        display: inline-block;
        transform: translate(-50%, -50%);
        white-space: nowrap;
        padding: 3px 10px;
        border: 1.5px solid #dc2626;
        border-radius: 8px;
        background: rgba(255, 255, 255, .92);
        color: #b91c1c;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
    }

    [data-theme="dark"] .breadcrumb-text {
        color: #94a3b8;
    }

    [data-theme="dark"] .page-title,
    [data-theme="dark"] .workflow-title,
    [data-theme="dark"] .section-header h4,
    [data-theme="dark"] .person-name,
    [data-theme="dark"] .selection-banner strong {
        color: #f1f5f9;
    }

    [data-theme="dark"] .page-subtitle,
    [data-theme="dark"] .workflow-text,
    [data-theme="dark"] .section-header p,
    [data-theme="dark"] .person-meta {
        color: #94a3b8;
    }

    [data-theme="dark"] .workflow-card,
    [data-theme="dark"] .section-card,
    [data-theme="dark"] .modal-content,
    [data-theme="dark"] .person-card {
        background: #111827;
        border-color: rgba(148, 163, 184, .14);
    }

    [data-theme="dark"] .head-panel,
    [data-theme="dark"] .gps-box,
    [data-theme="dark"] .selection-banner,
    [data-theme="dark"] .readonly-address-box {
        background: #0f172a;
        border-color: rgba(148, 163, 184, .18);
    }

    [data-theme="dark"] .search-results,
    [data-theme="dark"] .search-result {
        background: #111827;
        border-color: rgba(148, 163, 184, .14);
        color: #e5e7eb;
    }

    [data-theme="dark"] .search-result:hover {
        background: #172033;
    }

    [data-theme="dark"] .member-table th {
        background: #0f172a;
        color: #cbd5e1;
    }

    [data-theme="dark"] .modal-header,
    [data-theme="dark"] .modal-footer {
        border-color: rgba(148, 163, 184, .14);
    }

    [data-theme="dark"] .form-label {
        color: #cbd5e1;
    }

    [data-theme="dark"] .form-control,
    [data-theme="dark"] .form-select {
        background: #0f172a;
        border-color: rgba(148, 163, 184, .18);
        color: #f1f5f9;
    }

    [data-theme="dark"] .readonly-address-box {
        color: #cbd5e1;
    }

    /* dark: GPS capture + map picker */

    [data-theme="dark"] .geo-feedback.is-info {
        background: rgba(59, 130, 246, .14);
        border-color: rgba(96, 165, 250, .4);
        color: #bfdbfe;
    }

    [data-theme="dark"] .geo-feedback.is-success {
        background: rgba(34, 197, 94, .14);
        border-color: rgba(74, 222, 128, .4);
        color: #bbf7d0;
    }

    [data-theme="dark"] .geo-feedback.is-warn {
        background: rgba(245, 158, 11, .14);
        border-color: rgba(251, 191, 36, .4);
        color: #fde68a;
    }

    [data-theme="dark"] .geo-feedback.is-error {
        background: rgba(239, 68, 68, .14);
        border-color: rgba(248, 113, 113, .4);
        color: #fecaca;
    }

    [data-theme="dark"] .geo-summary {
        background: #111827;
        border-color: rgba(148, 163, 184, .3);
    }

    [data-theme="dark"] .geo-summary-icon {
        background: #1f2937;
        color: #cbd5e1;
    }

    [data-theme="dark"] .geo-summary[data-state="ok"] {
        background: rgba(34, 197, 94, .12);
        border-color: rgba(74, 222, 128, .5);
    }

    [data-theme="dark"] .geo-summary[data-state="warn"] {
        background: rgba(245, 158, 11, .12);
        border-color: rgba(251, 191, 36, .5);
    }

    [data-theme="dark"] .geo-summary[data-state="invalid"] {
        background: rgba(239, 68, 68, .12);
        border-color: rgba(248, 113, 113, .5);
    }

    [data-theme="dark"] .geo-summary[data-state="ok"] .geo-summary-icon {
        background: #22c55e;
        color: #052e16;
    }

    [data-theme="dark"] .geo-summary[data-state="warn"] .geo-summary-icon {
        background: #f59e0b;
        color: #451a03;
    }

    [data-theme="dark"] .geo-summary[data-state="invalid"] .geo-summary-icon {
        background: #f87171;
        color: #450a0a;
    }

    [data-theme="dark"] .geo-summary-title,
    [data-theme="dark"] .geo-summary-coords,
    [data-theme="dark"] .picker-readout b {
        color: #f1f5f9;
    }

    [data-theme="dark"] .geo-summary-meta,
    [data-theme="dark"] .picker-readout span,
    [data-theme="dark"] .picker-status {
        color: #94a3b8;
    }

    [data-theme="dark"] .picker-wrap {
        border-color: rgba(148, 163, 184, .2);
    }

    [data-theme="dark"] .picker-readout {
        background: #0f172a;
        border-color: rgba(148, 163, 184, .2);
    }

    [data-theme="dark"] .picker-tools button {
        background: #1f2937;
        border-color: rgba(148, 163, 184, .35);
        color: #f1f5f9;
    }

    [data-theme="dark"] .picker-tools button:hover {
        background: #1e3a8a;
        border-color: #60a5fa;
        color: #ffffff;
    }

    [data-theme="dark"] .picker-error {
        color: #fecaca;
    }

    @media(max-width: 768px) {

        .page-title {
            font-size: 1.55rem;
        }

        .workflow-line {
            display: none;
        }

        .workflow-step {
            margin-bottom: 8px;
        }

        .section-card {
            padding: 18px;
        }

        .member-table {
            min-width: 1100px;
        }

        .geo-actions .btn {
            width: 100%;
        }

        .picker-map {
            min-height: 320px;
        }
    }
</style>
@endpush

@section('content')

@php
    $residentCollection = collect($residents ?? []);
@endphp

<div class="container-fluid py-4 household-create-page">

    {{-- PAGE HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">

        <div>

            <div class="breadcrumb-text">
                E-POBLACION / HOUSEHOLDS / ADD HOUSEHOLD
            </div>

            <h1 class="page-title">
                Add Household
            </h1>

            <p class="page-subtitle">
                Household-first registration based on BHW house-to-house collection.
            </p>

        </div>

        <a href="{{ route('households.index') }}"
           class="btn btn-outline-secondary">

            <i class="bi bi-arrow-left me-1"></i>
            Back to Households

        </a>

    </div>


    {{-- WORKFLOW --}}
    <div class="workflow-card mb-4">

        <div class="row g-2 align-items-center">

            <div class="col-12 col-lg-4">

                <div class="workflow-step active"
                     id="workflowHeadStep">

                    <div class="workflow-number">1</div>

                    <div>
                        <div class="workflow-title">Household Head</div>
                        <div class="workflow-text">
                            Select an existing resident or register a new head.
                        </div>
                    </div>

                </div>

            </div>

            <div class="col-lg-1 d-none d-lg-flex">
                <div class="workflow-line"></div>
            </div>

            <div class="col-12 col-lg-3">

                <div class="workflow-step"
                     id="workflowLocationStep">

                    <div class="workflow-number">2</div>

                    <div>
                        <div class="workflow-title">Location & GPS</div>
                        <div class="workflow-text">
                            Record the household address, purok, and house location.
                        </div>
                    </div>

                </div>

            </div>

            <div class="col-lg-1 d-none d-lg-flex">
                <div class="workflow-line"></div>
            </div>

            <div class="col-12 col-lg-3">

                <div class="workflow-step"
                     id="workflowMembersStep">

                    <div class="workflow-number">3</div>

                    <div>
                        <div class="workflow-title">Add Members</div>
                        <div class="workflow-text">
                            Add every other person in the household.
                        </div>
                    </div>

                </div>

            </div>

        </div>

    </div>


    <form method="POST"
          action="{{ route('households.store') }}"
          id="householdForm">

        @csrf


        {{-- VALIDATION ERROR SUMMARY --}}
        @if ($errors->any())

            <div class="alert alert-danger mb-4"
                 role="alert">

                <div class="fw-bold mb-1">

                    <i class="bi bi-exclamation-triangle-fill me-1"></i>

                    Please correct the following before registering:

                </div>

                <ul class="mb-0 small">

                    @foreach ($errors->all() as $error)

                        <li>{{ $error }}</li>

                    @endforeach

                </ul>

            </div>

        @endif


        {{-- 1. HOUSEHOLD HEAD --}}
        <div class="section-card mb-4">

            <div class="section-header">

                <div class="section-icon">
                    <i class="bi bi-person-badge-fill"></i>
                </div>

                <div>
                    <h4>1. Household Head</h4>
                    <p>
                        The household head must be identified first.
                        Existing residents are reused instead of creating duplicates.
                    </p>
                </div>

            </div>


            <div id="headPanel"
                 class="head-panel">

                <div id="headEmptyState">

                    <div class="fw-bold mb-1">
                        No household head selected
                    </div>

                    <div class="text-muted small">
                        Search for an existing resident below,
                        or register a new household head.
                    </div>

                </div>

                <div id="selectedHeadCard"
                     class="d-none">
                </div>

            </div>


            <div class="row g-3 mt-1">

                <div class="col-12">

                    <label for="headResidentSearch"
                           class="form-label">

                        Search Existing Resident as Household Head

                    </label>

                    <div class="input-group">

                        <span class="input-group-text">
                            <i class="bi bi-search"></i>
                        </span>

                        <input type="text"
                               id="headResidentSearch"
                               class="form-control"
                               placeholder="Search by first name or last name..."
                               autocomplete="off">

                        <button type="button"
                                class="btn btn-outline-secondary"
                                id="clearHeadSearch">

                            <i class="bi bi-x-lg"></i>

                        </button>

                    </div>

                    <div id="headSearchResults"
                         class="search-results d-none">
                    </div>

                    <div class="required-note mt-2">
                        Only active residents are available for selection.
                    </div>

                </div>


                <div class="col-12 d-flex flex-wrap gap-2">

                    <button type="button"
                            class="btn btn-primary"
                            id="registerNewHeadButton">

                        <i class="bi bi-person-plus-fill me-1"></i>

                        Register New Household Head

                    </button>

                    <button type="button"
                            class="btn btn-outline-secondary d-none"
                            id="changeHeadButton">

                        <i class="bi bi-arrow-repeat me-1"></i>

                        Change Head

                    </button>

                </div>

            </div>


            <div id="headHiddenFields"></div>

        </div>


        {{-- 2. LOCATION & GPS --}}
        <div class="section-card mb-4">

            <div class="section-header">

                <div class="section-icon">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>

                <div>
                    <h4>2. Location & GPS</h4>
                    <p>
                        Record the household address, purok, and the
                        house location collected during the BHW home visit.
                    </p>
                </div>

            </div>


            <div class="row g-4 mb-4">

                <div class="col-md-6">

                    <label class="form-label">Household Number</label>

                    <input type="text"
                           class="form-control"
                           value="AUTO GENERATED"
                           disabled>

                </div>

                <div class="col-md-6">

                    <label class="form-label">Status</label>

                    <input type="text"
                           class="form-control"
                           value="Active"
                           disabled>

                </div>

                <div class="col-12 col-md-8">

                    <label for="address"
                           class="form-label">

                        Complete Household Address

                        <span class="text-danger">*</span>

                    </label>

                    <textarea name="address"
                              id="address"
                              rows="3"
                              class="form-control @error('address') is-invalid @enderror"
                              placeholder="Enter the complete household address..."
                              required>{{ old('address') }}</textarea>

                    @error('address')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

                <div class="col-12 col-md-4">

                    <label for="purok"
                           class="form-label">

                        Purok

                        <span class="text-danger">*</span>

                    </label>

                    <select name="purok"
                            id="purok"
                            class="form-select @error('purok') is-invalid @enderror"
                            required>

                        <option value="">Select Purok</option>

                        @for($p = 1; $p <= 7; $p++)
                            <option value="Purok {{ $p }}"
                                {{ old('purok') === 'Purok '.$p ? 'selected' : '' }}>
                                Purok {{ $p }}
                            </option>
                        @endfor

                    </select>

                    @error('purok')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

            </div>


            {{-- HOUSEHOLD CLASSIFICATIONS --}}
            <div class="gps-box mb-4">

                <div class="mb-3">
                    <div class="fw-bold">Household Classification</div>
                    <div class="text-muted small mt-1">
                        Select the classifications that apply to the household as a whole.
                        Both may be selected if applicable.
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-4">

                    <div class="form-check">
                        <input type="hidden" name="is_low_income" value="0">
                        <input class="form-check-input"
                               type="checkbox"
                               id="is_low_income"
                               name="is_low_income"
                               value="1"
                               {{ old('is_low_income') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_low_income">
                            Indigent / Low-Income Family
                        </label>
                    </div>

                    <div class="form-check">
                        <input type="hidden" name="is_4ps_beneficiary" value="0">
                        <input class="form-check-input"
                               type="checkbox"
                               id="is_4ps_beneficiary"
                               name="is_4ps_beneficiary"
                               value="1"
                               {{ old('is_4ps_beneficiary') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_4ps_beneficiary">
                            4Ps Beneficiary Family
                        </label>
                    </div>

                </div>

                <div class="required-note mt-2">
                    These are household-level classifications. Staff must verify supporting information before using them for assistance decisions.
                </div>

            </div>


            {{-- GPS --}}
            <div class="gps-box"
                 id="gpsLocationBox">

                <div class="mb-3">

                    <div class="fw-bold">
                        BHW Home Visit & House Location
                    </div>

                    <div class="text-muted small mt-1">
                        Record the coordinates collected during the
                        house-to-house visit. Stand at the house and use
                        your laptop's location, or pick the exact spot on
                        the map.
                    </div>

                </div>


                <div class="geo-actions">

                    <button type="button"
                            class="btn btn-primary"
                            id="locateCurrentButton">

                        <i class="bi bi-crosshair me-2"></i>

                        <span>Locate Current Position</span>

                    </button>

                    <button type="button"
                            class="btn btn-outline-primary"
                            id="pickOnMapButton">

                        <i class="bi bi-map me-2"></i>

                        Pick Location on Map

                    </button>

                    <button type="button"
                            class="btn btn-outline-secondary"
                            id="clearCoordinatesButton">

                        <i class="bi bi-x-circle me-2"></i>

                        Clear

                    </button>

                </div>


                <div id="geoSecureNotice"
                     class="geo-feedback is-warn d-none">

                    <i class="bi bi-shield-exclamation"></i>

                    <div>
                        <strong>Secure connection needed for GPS.</strong>
                        Browsers only allow location access on HTTPS or
                        localhost. This page is opened over an insecure
                        address, so <em>Locate Current Position</em> may be
                        blocked. You can still use
                        <em>Pick Location on Map</em> or type the
                        coordinates manually.
                    </div>

                </div>


                <div id="geoFeedback"
                     class="geo-feedback d-none"
                     role="status"
                     aria-live="polite">
                </div>


                <div class="row g-3 mt-1">

                    <div class="col-md-6">

                        <label for="latitude"
                               class="form-label">
                            Latitude
                        </label>

                        <input type="text"
                               name="latitude"
                               id="latitude"
                               class="form-control geo-input @error('latitude') is-invalid @enderror"
                               value="{{ old('latitude') }}"
                               inputmode="decimal"
                               autocomplete="off"
                               placeholder="e.g. 16.123456">

                        @error('latitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                    </div>

                    <div class="col-md-6">

                        <label for="longitude"
                               class="form-label">
                            Longitude
                        </label>

                        <input type="text"
                               name="longitude"
                               id="longitude"
                               class="form-control geo-input @error('longitude') is-invalid @enderror"
                               value="{{ old('longitude') }}"
                               inputmode="decimal"
                               autocomplete="off"
                               placeholder="e.g. 120.123456">

                        @error('longitude')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror

                    </div>

                </div>


                <div class="geo-summary"
                     id="geoSummary"
                     data-state="empty">

                    <div class="geo-summary-icon">
                        <i class="bi bi-geo-alt"
                           id="geoSummaryIcon"></i>
                    </div>

                    <div>

                        <div class="geo-summary-title"
                             id="geoSummaryTitle">
                            No coordinates captured yet
                        </div>

                        <div class="geo-summary-coords"
                             id="geoSummaryCoords">
                            &mdash;
                        </div>

                        <div class="geo-summary-meta"
                             id="geoSummaryMeta">
                            Use one of the buttons above, or type the
                            coordinates manually.
                        </div>

                    </div>

                </div>


                <div class="notice-box mt-3">

                    <i class="bi bi-info-circle-fill"></i>

                    <div>

                        <strong>Human-collected location.</strong>

                        GPS coordinates identify the household
                        location for the barangay map.
                        They do not automatically determine
                        eligibility or any disaster/relief decision.
                        A laptop location is only an estimate, so verify the
                        pin on the map before registering. Nothing is
                        submitted until you press
                        <strong>Register Household</strong>.

                    </div>

                </div>

            </div>

        </div>


        {{-- 3. ADD HOUSEHOLD MEMBERS --}}
        <div class="section-card mb-4">

            <div class="section-header">

                <div class="section-icon">
                    <i class="bi bi-people-fill"></i>
                </div>

                <div>
                    <h4>3. Add Household Members</h4>
                    <p>
                        Add all other people identified during the
                        BHW house-to-house visit.
                    </p>
                </div>

            </div>


            <div id="memberLockedNotice"
                 class="notice-box mb-3">

                <i class="bi bi-lock-fill"></i>

                <div>
                    <strong>Household Head First.</strong>
                    Select or register the household head before
                    adding other members.
                </div>

            </div>


            <div id="memberSection"
                 class="d-none">

                <div class="row g-3 mb-3">

                    <div class="col-12 col-lg-8">

                        <label for="memberResidentSearch"
                               class="form-label">
                            Add Existing Resident as Member
                        </label>

                        <div class="input-group">

                            <span class="input-group-text">
                                <i class="bi bi-search"></i>
                            </span>

                            <input type="text"
                                   id="memberResidentSearch"
                                   class="form-control"
                                   placeholder="Search an existing resident..."
                                   autocomplete="off">

                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    id="clearMemberSearch">

                                <i class="bi bi-x-lg"></i>

                            </button>

                        </div>

                        <div id="memberSearchResults"
                             class="search-results d-none">
                        </div>

                    </div>

                    <div class="col-12 col-lg-4 d-flex align-items-end">

                        <button type="button"
                                class="btn btn-outline-primary w-100"
                                id="addMemberButton">

                            <i class="bi bi-person-plus-fill me-1"></i>

                            Register New Member

                        </button>

                    </div>

                </div>


                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">

                    <div>

                        <div class="fw-bold">
                            Family / Household Members
                        </div>

                        <div class="text-muted small">
                            The head is automatically included in household membership.
                        </div>

                    </div>

                </div>


                <div id="emptyMembers"
                     class="empty-members">

                    <i class="bi bi-people fs-2 d-block mb-2"></i>

                    No additional household members have been added yet.

                </div>


                <div id="memberTableWrap"
                     class="member-table-wrap d-none">

                    <table class="table member-table">

                        <thead>

                            <tr>
                                <th>#</th>
                                <th>Full Name</th>
                                <th>Birth Date</th>
                                <th>Age</th>
                                <th>Sex</th>
                                <th>Relationship</th>
                                <th>Residency</th>
                                <th>Household Head</th>
                                <th>Classification</th>
                                <th>Record</th>
                                <th>Action</th>
                            </tr>

                        </thead>

                        <tbody id="membersTableBody"></tbody>

                    </table>

                </div>

            </div>


            <div id="memberHiddenFields"></div>

        </div>


        {{-- FINAL CONFIRMATION --}}
        <div class="section-card">

            <div class="notice-box mb-4">

                <i class="bi bi-shield-check"></i>

                <div>

                    <strong>Before registering:</strong>

                    confirm that the household head is correct,
                    all household members collected by the BHW are listed,
                    and the household address/purok are accurate.

                    Existing residents will be linked by their resident ID
                    to prevent duplicate records.

                </div>

            </div>


            <div class="d-flex flex-wrap justify-content-end gap-2">

                <a href="{{ route('households.index') }}"
                   class="btn btn-outline-secondary">
                    Cancel
                </a>

                <button type="submit"
                        id="submitHouseholdButton"
                        class="btn btn-primary">

                    <i class="bi bi-check2-circle me-1"></i>

                    Register Household

                </button>

            </div>

        </div>

    </form>

</div>


{{-- RESIDENT FORM MODAL --}}
<div class="modal fade"
     id="residentFormModal"
     tabindex="-1"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content">

            <div class="modal-header">

                <div>

                    <h5 class="modal-title fw-bold"
                        id="residentModalTitle">
                        Register Resident
                    </h5>

                    <div class="text-muted small"
                         id="residentModalSubtitle">
                        Enter the person's details.
                    </div>

                </div>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>


            <div class="modal-body">

                <div id="modalExistingNotice"
                     class="selection-banner mb-3 d-none">
                </div>


                <div class="row g-3">

                    {{-- NAME --}}
                    <div class="col-md-4">

                        <label class="form-label">
                            First Name
                            <span class="text-danger">*</span>
                        </label>

                        <input id="resident_first_name"
                               class="form-control">

                    </div>

                    <div class="col-md-4">

                        <label class="form-label">Middle Name</label>

                        <input id="resident_middle_name"
                               class="form-control">

                    </div>

                    <div class="col-md-4">

                        <label class="form-label">
                            Last Name
                            <span class="text-danger">*</span>
                        </label>

                        <input id="resident_last_name"
                               class="form-control">

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">Suffix</label>

                        <input id="resident_suffix"
                               class="form-control"
                               placeholder="Jr., Sr., III">

                    </div>

                    {{-- BIRTH / SEX --}}
                    <div class="col-md-3">

                        <label class="form-label">
                            Birth Date
                            <span class="text-danger">*</span>
                        </label>

                        <input id="resident_birth_date"
                               type="date"
                               class="form-control">

                    </div>

                    <div class="col-md-3">

                        <label class="form-label">
                            Sex
                            <span class="text-danger">*</span>
                        </label>

                        <select id="resident_sex"
                                class="form-select">

                            <option value="">Select</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>

                        </select>

                    </div>

                    {{-- CIVIL STATUS --}}
                    <div class="col-md-3">

                        <label class="form-label">
                            Civil Status
                            <span class="text-danger">*</span>
                        </label>

                        <select id="resident_civil_status"
                                class="form-select">

                            <option value="">Select</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Widowed">Widowed</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Separated">Separated</option>
                            <option value="Live-in / Cohabiting">Live-in / Cohabiting</option>
                            <option value="Annulled">Annulled</option>
                            <option value="Not Applicable">Not Applicable</option>
                            <option value="Prefer not to say">Prefer not to say</option>

                        </select>

                    </div>

                    {{-- RELATIONSHIP --}}
                    <div class="col-md-6"
                         id="relationshipField">

                        <label class="form-label">
                            Relationship to Household Head
                            <span class="text-danger">*</span>
                        </label>

                        <select id="resident_relationship"
                                class="form-select">

                            <option value="">Select relationship</option>
                            <option value="Spouse">Spouse</option>
                            <option value="Son">Son</option>
                            <option value="Daughter">Daughter</option>
                            <option value="Parent">Parent</option>
                            <option value="Sibling">Sibling</option>
                            <option value="Grandparent">Grandparent</option>
                            <option value="Grandchild">Grandchild</option>
                            <option value="Relative">Relative</option>
                            <option value="Other">Other</option>

                        </select>

                    </div>

                    {{-- RESIDENCY TYPE --}}
                    <div class="col-md-6">

                        <label class="form-label">
                            Residency Type
                            <span class="text-danger">*</span>
                        </label>

                        <select id="resident_residency_type"
                                class="form-select">

                            <option value="">Select residency type</option>
                            <option value="Original Resident">Original Resident</option>
                            <option value="Migrant Resident">Migrant Resident</option>

                        </select>

                    </div>

                    {{-- RESIDENCY YEAR --}}
                    <div class="col-md-6">

                        <label class="form-label">Residency Start Year</label>

                        <input id="resident_residency_start_year"
                               type="number"
                               class="form-control"
                               min="1900"
                               max="{{ date('Y') }}"
                               placeholder="e.g. 2018">

                        <div class="required-note mt-1">
                            Enter the year the person started residing
                            in the barangay, if known.
                        </div>

                    </div>

                    {{-- ESTIMATED YEAR --}}
                    <div class="col-md-6 d-flex align-items-center">

                        <div class="form-check mt-4">

                            <input class="form-check-input"
                                   type="checkbox"
                                   id="resident_residency_year_estimated">

                            <label class="form-check-label"
                                   for="resident_residency_year_estimated">
                                Residency year is estimated
                            </label>

                        </div>

                    </div>

                    {{-- CONTACT (Philippine mobile: fixed +63 + 10 digits, first digit 9) --}}
                    <div class="col-md-6">

                        <label class="form-label"
                               for="resident_contact_number">
                            Contact Number
                        </label>

                        <div class="input-group contact-group">

                            <span class="input-group-text contact-prefix"
                                  id="resident_contact_prefix">+63</span>

                            <input id="resident_contact_number"
                                   type="tel"
                                   class="form-control"
                                   inputmode="numeric"
                                   pattern="9[0-9]{9}"
                                   maxlength="10"
                                   autocomplete="off"
                                   placeholder="9XXXXXXXXX"
                                   aria-describedby="resident_contact_prefix resident_contact_help resident_contact_error">

                        </div>

                        <div class="invalid-feedback d-none"
                             id="resident_contact_error"
                             role="alert"></div>

                        <div class="required-note mt-1"
                             id="resident_contact_help">
                            Format: <strong>+63 9XXXXXXXXX</strong>
                            (10 digits after +63, starting with 9).
                            Optional &mdash; leave blank if there is no contact number.
                        </div>

                    </div>

                    {{-- EMAIL --}}
                    <div class="col-md-6">

                        <label class="form-label">Email</label>

                        <input id="resident_email"
                               type="text"
                               class="form-control"
                               placeholder="Optional or N/A">

                        <div class="required-note mt-1">
                            Enter a valid email address, N/A, or leave blank.
                        </div>

                    </div>

                    {{-- OCCUPATION --}}
                    <div class="col-md-6">

                        <label class="form-label">
                            Primary Occupation / Status
                        </label>

                        <select id="resident_occupation"
                                class="form-select">

                            <option value="">Select occupation / status</option>
                            <option value="Student">Student</option>
                            <option value="Employed">Employed</option>
                            <option value="Self-Employed">Self-Employed</option>
                            <option value="Business Owner">Business Owner</option>
                            <option value="Farmer">Farmer</option>
                            <option value="Fisherman">Fisherman</option>
                            <option value="Homemaker">Homemaker</option>
                            <option value="Unemployed">Unemployed</option>
                            <option value="Retired">Retired</option>
                            <option value="Senior Citizen">Senior Citizen</option>
                            <option value="Child / Not Yet School-Age">Child / Not Yet School-Age</option>
                            <option value="Infant">Infant</option>
                            <option value="Other">Other</option>

                        </select>

                    </div>

                    {{-- OCCUPATION OTHER --}}
                    <div class="col-md-6 occupation-other-wrap"
                         id="occupationOtherWrap">

                        <label class="form-label">
                            Specify Other Occupation / Status
                        </label>

                        <input id="resident_occupation_other"
                               class="form-control"
                               placeholder="Specify occupation or status">

                    </div>

                    {{-- INDIVIDUAL CLASSIFICATIONS --}}
                    <div class="col-12">

                        <label class="form-label d-block mb-2">Individual Classification</label>

                        <div class="d-flex flex-wrap gap-4">

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="resident_is_pwd">
                                <label class="form-check-label" for="resident_is_pwd">PWD</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="resident_is_senior_citizen">
                                <label class="form-check-label" for="resident_is_senior_citizen">Senior Citizen</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="resident_is_solo_parent">
                                <label class="form-check-label" for="resident_is_solo_parent">Solo Parent</label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="resident_is_student">
                                <label class="form-check-label" for="resident_is_student">Student</label>
                            </div>

                        </div>

                        <div class="student-level-wrap mt-3"
                             id="studentEducationWrap">

                            <label class="form-label"
                                   for="resident_student_education_level">
                                Education Level
                                <span class="text-danger">*</span>
                            </label>

                            <select id="resident_student_education_level"
                                    class="form-select">

                                <option value="">Select education level</option>
                                <option value="Elementary">Elementary</option>
                                <option value="High School">High School</option>
                                <option value="College">College</option>

                            </select>

                        </div>

                        <div class="required-note mt-2">
                            Multiple classifications may be selected for one person.
                            These apply only to this person, not to the rest of the household.
                            A Student must have an education level.
                        </div>

                    </div>

                    {{-- INHERITED ADDRESS --}}
                    <div class="col-12">

                        <label class="form-label">Resident Address</label>

                        <div class="readonly-address-box">

                            <i class="bi bi-house-fill me-1"></i>

                            <strong>Address inherited from household:</strong>

                            <span id="modalHouseholdAddress">
                                Please enter the household address above.
                            </span>

                        </div>

                        <div class="required-note mt-2">
                            Individual members do not enter a separate address.
                            Their resident address is synchronized with the household address.
                        </div>

                    </div>

                </div>


                <div id="residentModalError"
                     class="alert alert-danger mt-3 d-none mb-0">
                </div>

            </div>


            <div class="modal-footer">

                <button type="button"
                        class="btn btn-outline-secondary"
                        data-bs-dismiss="modal">
                    Cancel
                </button>

                <button type="button"
                        class="btn btn-primary"
                        id="saveResidentButton">

                    <i class="bi bi-check2 me-1"></i>

                    Save Person

                </button>

            </div>

        </div>

    </div>

</div>


{{-- LOCATION PICKER MODAL (embedded Leaflet map) --}}
<div class="modal fade"
     id="locationPickerModal"
     tabindex="-1"
     aria-labelledby="locationPickerTitle"
     aria-hidden="true">

    <div class="modal-dialog modal-xl modal-dialog-centered">

        <div class="modal-content">

            <div class="modal-header">

                <div>

                    <h5 class="modal-title fw-bold"
                        id="locationPickerTitle">
                        Pick Household Location
                    </h5>

                    <div class="text-muted small">
                        Click the map or drag the pin onto the exact house.
                        The Latitude and Longitude fields update as you go.
                    </div>

                </div>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close">
                </button>

            </div>


            <div class="modal-body">

                <div class="picker-wrap">

                    <div id="locationPickerMap"
                         class="picker-map"
                         aria-label="Map for picking the household location"></div>

                    <div class="picker-tools">

                        <button type="button"
                                id="pickerLayerButton"
                                title="Switch to street map"
                                aria-label="Switch base map">
                            <i class="bi bi-layers"></i>
                        </button>

                        <button type="button"
                                id="pickerBorderButton"
                                title="Zoom to Barangay Poblacion border"
                                aria-label="Zoom to Barangay Poblacion border">
                            <i class="bi bi-pentagon"></i>
                        </button>

                        <button type="button"
                                id="pickerCenterPinButton"
                                title="Center on the pin"
                                aria-label="Center on the pin"
                                disabled>
                            <i class="bi bi-geo-alt-fill"></i>
                        </button>

                    </div>

                    <div class="picker-hint">
                        Click anywhere to place the pin
                    </div>

                </div>


                <div class="picker-readout">

                    <div>
                        <span>Latitude</span>
                        <b id="pickerLatitude">&mdash;</b>
                    </div>

                    <div>
                        <span>Longitude</span>
                        <b id="pickerLongitude">&mdash;</b>
                    </div>

                    <div class="picker-status"
                         id="pickerStatus">
                        No pin placed yet.
                    </div>

                </div>

            </div>


            <div class="modal-footer">

                <button type="button"
                        class="btn btn-outline-secondary"
                        id="pickerCancelButton">
                    Cancel
                </button>

                <button type="button"
                        class="btn btn-primary"
                        id="pickerDoneButton"
                        disabled>

                    <i class="bi bi-check2 me-1"></i>

                    Use This Location

                </button>

            </div>

        </div>

    </div>

</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    /* =========================================================
       DATA
    ========================================================== */

    const residents =
        @json($residentCollection->values());

    const oldMembers =
        @json(old('members', []));

    const oldHead =
        @json(old('head', []));


    /* =========================================================
       ELEMENTS
    ========================================================== */

    const householdForm = document.getElementById('householdForm');
    const headPanel = document.getElementById('headPanel');
    const headEmptyState = document.getElementById('headEmptyState');
    const selectedHeadCard = document.getElementById('selectedHeadCard');
    const headSearch = document.getElementById('headResidentSearch');
    const headResults = document.getElementById('headSearchResults');
    const clearHeadSearch = document.getElementById('clearHeadSearch');
    const registerNewHeadButton = document.getElementById('registerNewHeadButton');
    const changeHeadButton = document.getElementById('changeHeadButton');
    const memberSection = document.getElementById('memberSection');
    const memberLockedNotice = document.getElementById('memberLockedNotice');
    const addMemberButton = document.getElementById('addMemberButton');
    const memberSearch = document.getElementById('memberResidentSearch');
    const memberResults = document.getElementById('memberSearchResults');
    const clearMemberSearch = document.getElementById('clearMemberSearch');
    const emptyMembers = document.getElementById('emptyMembers');
    const memberTableWrap = document.getElementById('memberTableWrap');
    const membersTableBody = document.getElementById('membersTableBody');
    const headHiddenFields = document.getElementById('headHiddenFields');
    const memberHiddenFields = document.getElementById('memberHiddenFields');

    const modalElement = document.getElementById('residentFormModal');
    const residentModal = new bootstrap.Modal(modalElement);
    const modalTitle = document.getElementById('residentModalTitle');
    const modalSubtitle = document.getElementById('residentModalSubtitle');
    const modalExistingNotice = document.getElementById('modalExistingNotice');
    const modalError = document.getElementById('residentModalError');
    const saveResidentButton = document.getElementById('saveResidentButton');
    const modalHouseholdAddress = document.getElementById('modalHouseholdAddress');
    const occupationOtherWrap = document.getElementById('occupationOtherWrap');
    const studentEducationWrap = document.getElementById('studentEducationWrap');

    const workflowHeadStep = document.getElementById('workflowHeadStep');
    const workflowLocationStep = document.getElementById('workflowLocationStep');
    const workflowMembersStep = document.getElementById('workflowMembersStep');


    /* =========================================================
       MODAL FIELDS
    ========================================================== */

    const fields = {
        first_name: document.getElementById('resident_first_name'),
        middle_name: document.getElementById('resident_middle_name'),
        last_name: document.getElementById('resident_last_name'),
        suffix: document.getElementById('resident_suffix'),
        birth_date: document.getElementById('resident_birth_date'),
        sex: document.getElementById('resident_sex'),
        civil_status: document.getElementById('resident_civil_status'),
        relationship: document.getElementById('resident_relationship'),
        residency_type: document.getElementById('resident_residency_type'),
        residency_start_year: document.getElementById('resident_residency_start_year'),
        residency_year_estimated: document.getElementById('resident_residency_year_estimated'),
        contact_number: document.getElementById('resident_contact_number'),
        email: document.getElementById('resident_email'),
        occupation: document.getElementById('resident_occupation'),
        occupation_other: document.getElementById('resident_occupation_other'),
        is_pwd: document.getElementById('resident_is_pwd'),
        is_senior_citizen: document.getElementById('resident_is_senior_citizen'),
        is_solo_parent: document.getElementById('resident_is_solo_parent'),
        is_student: document.getElementById('resident_is_student'),
        student_education_level: document.getElementById('resident_student_education_level')
    };

    /* =========================================================
       PHILIPPINE CONTACT NUMBER  (+63 9XXXXXXXXX)

       The modal input holds ONLY the 10 digits after the fixed +63
       prefix, so the country code can never be duplicated. The value
       kept in state and submitted to the server is the full
       +639XXXXXXXXX, or '' when the number is left blank.

       The same single modal input is used for the household head and
       for every member, so these listeners are attached exactly once.
    ========================================================== */

    const PH_CONTACT_FULL = /^\+639\d{9}$/;
    const PH_CONTACT_LOCAL = /^9\d{9}$/;

    const CONTACT_MESSAGE =
        'Enter a valid Philippine mobile number: +63 followed by 10 digits starting with 9 (e.g. +63 9171234567).';

    const contactInput = fields.contact_number;
    const contactError = document.getElementById('resident_contact_error');

    // Any supported representation -> the 10 local digits, or '' if blank/invalid.
    function contactToLocalDigits(value) {

        let digits = String(value ?? '').replace(/\D/g, '');

        if (digits.length === 12 && digits.startsWith('63')) {
            digits = digits.slice(2);
        } else if (digits.length === 11 && digits.startsWith('0')) {
            digits = digits.slice(1);
        }

        return PH_CONTACT_LOCAL.test(digits) ? digits : '';

    }

    // Any supported representation -> +639XXXXXXXXX, or '' if blank/invalid.
    function contactToFull(value) {

        const local = contactToLocalDigits(value);

        return local ? '+63' + local : '';

    }

    // Value currently typed in the modal -> +63 + typed digits, or ''.
    function contactFromInput() {

        const typed = contactInput ? contactInput.value.trim() : '';

        return typed === '' ? '' : '+63' + typed;

    }

    // Lenient cleaner for typed / pasted text (partial numbers allowed).
    function sanitizeContactDigits(raw) {

        let digits = String(raw ?? '').replace(/\D/g, '');

        if (digits.startsWith('63')) {
            digits = digits.slice(2);
        } else if (digits.startsWith('0')) {
            digits = digits.slice(1);
        }

        if (digits !== '' && digits[0] !== '9') {
            digits = '';
        }

        return digits.slice(0, 10);

    }

    function showContactError(message) {

        if (!contactInput || !contactError) {
            return;
        }

        contactInput.classList.add('is-invalid');
        contactError.textContent = message;
        contactError.classList.remove('d-none');
        contactError.classList.add('d-block');

    }

    function hideContactError() {

        if (!contactInput || !contactError) {
            return;
        }

        contactInput.classList.remove('is-invalid');
        contactError.textContent = '';
        contactError.classList.add('d-none');
        contactError.classList.remove('d-block');

    }

    if (contactInput) {

        // Typing: digits only, first digit must be 9, never more than 10.
        contactInput.addEventListener('beforeinput', function (event) {

            const type = event.inputType || '';

            if (
                !type.startsWith('insert') ||
                type === 'insertFromPaste' ||
                type === 'insertFromDrop' ||
                event.data === null ||
                event.data === undefined
            ) {
                return;
            }

            const data = String(event.data);

            if (!/^\d+$/.test(data)) {
                event.preventDefault();
                return;
            }

            const start = contactInput.selectionStart ?? 0;
            const end = contactInput.selectionEnd ?? start;

            if (start === 0 && data[0] !== '9') {
                event.preventDefault();
                return;
            }

            if (contactInput.value.length - (end - start) + data.length > 10) {
                event.preventDefault();
            }

        });

        // Safety net (autofill, IME, drag/drop): clean whatever got in.
        contactInput.addEventListener('input', function () {

            const cleaned = sanitizeContactDigits(contactInput.value);

            if (cleaned !== contactInput.value) {
                contactInput.value = cleaned;
            }

            if (cleaned === '' || PH_CONTACT_LOCAL.test(cleaned)) {
                hideContactError();
            }

        });

        // Paste: accepts 9XXXXXXXXX, 09XXXXXXXXX, 639XXXXXXXXX or +639XXXXXXXXX.
        contactInput.addEventListener('paste', function (event) {

            event.preventDefault();

            const text =
                (event.clipboardData || window.clipboardData).getData('text');

            const cleaned = sanitizeContactDigits(text);

            if (cleaned === '' && String(text).replace(/\D/g, '') !== '') {
                showContactError(CONTACT_MESSAGE);
                return;
            }

            contactInput.value = cleaned;
            contactInput.setSelectionRange(cleaned.length, cleaned.length);

            if (cleaned === '' || PH_CONTACT_LOCAL.test(cleaned)) {
                hideContactError();
            }

        });

        contactInput.addEventListener('drop', function (event) {
            event.preventDefault();
        });

        // Incomplete numbers are flagged when the user leaves the field.
        contactInput.addEventListener('blur', function () {

            const typed = contactInput.value;

            if (typed !== '' && !PH_CONTACT_LOCAL.test(typed)) {
                showContactError(CONTACT_MESSAGE);
            }

        });

    }



    /* =========================================================
       STATE
    ========================================================== */

    let householdHead = null;
    let members = [];
    let modalMode = 'head-new';
    let editingIndex = null;
    let selectedExistingResidentId = null;


    /* =========================================================
       CONSTANTS
    ========================================================== */

    const validResidencyTypes = [
        'Original Resident',
        'Migrant Resident'
    ];

    const educationLevels = [
        'Elementary',
        'High School',
        'College'
    ];

    const occupationOptions = [
        'Student',
        'Employed',
        'Self-Employed',
        'Business Owner',
        'Farmer',
        'Fisherman',
        'Homemaker',
        'Unemployed',
        'Retired',
        'Senior Citizen',
        'Child / Not Yet School-Age',
        'Infant',
        'Other'
    ];


    /* =========================================================
       HELPERS
    ========================================================== */

    function esc(value) {

        return String(value ?? '').replace(
            /[&<>'"]/g,
            function (c) {

                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    "'": '&#039;',
                    '"': '&quot;'
                }[c];

            }
        );

    }


    /*
     * Old input restored from the server can arrive as "1"/"0"
     * strings rather than real booleans.
     */
    function toBool(value) {

        return (
            value === true ||
            value === 1 ||
            value === '1' ||
            value === 'true'
        );

    }


    function fullName(person) {

        return [
            person.first_name,
            person.middle_name,
            person.last_name,
            person.suffix
        ]
        .filter(Boolean)
        .join(' ');

    }


    function ageOf(birthDate) {

        if (!birthDate) {
            return '';
        }

        const birth = new Date(birthDate + 'T00:00:00');

        if (Number.isNaN(birth.getTime())) {
            return '';
        }

        const today = new Date();

        let age = today.getFullYear() - birth.getFullYear();

        const monthDifference = today.getMonth() - birth.getMonth();

        if (
            monthDifference < 0 ||
            (
                monthDifference === 0 &&
                today.getDate() < birth.getDate()
            )
        ) {
            age--;
        }

        return age >= 0 ? age : '';

    }


    function normalize(value) {

        return String(value ?? '')
            .trim()
            .toLowerCase();

    }


    /*
     * Current system values: Original Resident / Migrant Resident.
     * Legacy values are normalized so old records stay valid.
     */
    function normalizeResidencyType(value) {

        const normalized = normalize(value);

        if (
            normalized === 'original resident' ||
            normalized === 'permanent'
        ) {
            return 'Original Resident';
        }

        if (
            normalized === 'migrant resident' ||
            normalized === 'migrant' ||
            normalized === 'temporary'
        ) {
            return 'Migrant Resident';
        }

        if (!normalized) {
            return 'Original Resident';
        }

        return '';

    }


    function normalizeOccupation(person) {

        const occupation = person.occupation || '';

        if (occupationOptions.includes(occupation)) {

            return {
                occupation: occupation,
                occupation_other: person.occupation_other || ''
            };

        }

        if (occupation) {

            return {
                occupation: 'Other',
                occupation_other:
                    person.occupation_other || occupation
            };

        }

        return {
            occupation: '',
            occupation_other: ''
        };

    }


    /*
     * Individual classification normalizer.
     *
     * Each person is normalized on their own. The legacy
     * `is_high_school` flag is DERIVED from the education level
     * (Student + High School) and is only read as input when an old
     * record has no education level yet.
     */
    function normalizeClassification(person) {

        const legacyHighSchool = toBool(person.is_high_school);

        const isStudent =
            toBool(person.is_student) || legacyHighSchool;

        let level = '';

        if (isStudent) {

            level = String(
                person.student_education_level ||
                person.education_level ||
                ''
            ).trim();

            if (!educationLevels.includes(level)) {
                level = '';
            }

            if (!level && legacyHighSchool) {
                level = 'High School';
            }

        }

        return {
            is_pwd: toBool(person.is_pwd),
            is_senior_citizen: toBool(person.is_senior_citizen),
            is_solo_parent: toBool(person.is_solo_parent),
            is_student: isStudent,
            student_education_level: level,
            is_high_school: isStudent && level === 'High School'
        };

    }


    /*
     * Badges shown in the member table so staff can see each
     * person's own classification(s) before submitting.
     */
    function classificationBadges(person) {

        const badges = [];

        if (toBool(person.is_pwd)) {
            badges.push('<span class="badge bg-danger">PWD</span>');
        }

        if (toBool(person.is_senior_citizen)) {
            badges.push('<span class="badge bg-secondary">Senior Citizen</span>');
        }

        if (toBool(person.is_solo_parent)) {
            badges.push('<span class="badge bg-warning text-dark">Solo Parent</span>');
        }

        if (toBool(person.is_student)) {

            const level = person.student_education_level || '';

            badges.push(
                '<span class="badge bg-primary">Student' +
                (level ? ' – ' + esc(level) : ' – level needed') +
                '</span>'
            );

        }

        if (!badges.length) {
            return '<span class="text-muted">None</span>';
        }

        return badges.join(' ');

    }


    /*
     * Classification controls on the household head card, so an
     * EXISTING resident chosen as head can also be classified
     * (no modal is opened for existing heads).
     */
    const headFlagOptions = [
        { key: 'is_pwd', label: 'PWD' },
        { key: 'is_senior_citizen', label: 'Senior Citizen' },
        { key: 'is_solo_parent', label: 'Solo Parent' },
        { key: 'is_student', label: 'Student' }
    ];


    function headClassificationControls(person) {

        const isStudent = toBool(person.is_student);

        return `

            <div class="mt-3">

                <div class="form-label mb-1">
                    Head Classification
                </div>

                <div class="d-flex flex-wrap gap-3">

                    ${headFlagOptions.map(
                        function (option) {

                            return `

                                <div class="form-check">

                                    <input class="form-check-input"
                                           type="checkbox"
                                           id="head_flag_${option.key}"
                                           data-head-flag="${option.key}"
                                           ${toBool(person[option.key]) ? 'checked' : ''}>

                                    <label class="form-check-label"
                                           for="head_flag_${option.key}">
                                        ${option.label}
                                    </label>

                                </div>

                            `;

                        }
                    ).join('')}

                </div>

                <div class="mt-2 ${isStudent ? '' : 'd-none'}"
                     style="max-width: 260px;">

                    <label class="form-label mb-1"
                           for="head_education_level">
                        Education Level
                        <span class="text-danger">*</span>
                    </label>

                    <select class="form-select form-select-sm"
                            id="head_education_level"
                            data-head-education>

                        <option value="">Select education level</option>

                        ${educationLevels.map(
                            function (level) {

                                return `
                                    <option value="${level}"
                                        ${person.student_education_level === level ? 'selected' : ''}>
                                        ${level}
                                    </option>
                                `;

                            }
                        ).join('')}

                    </select>

                </div>

                <div class="required-note mt-1">
                    Optional. A Student must have an education level.
                    Staff must verify before these are used for assistance decisions.
                </div>

            </div>

        `;

    }


    function identityKey(person) {

        const id = person.resident_id || person.id;

        if (id) {
            return 'id:' + Number(id);
        }

        return [
            person.first_name,
            person.middle_name,
            person.last_name,
            person.suffix,
            person.birth_date
        ]
        .map(normalize)
        .join('|');

    }


    function householdAddress() {

        const address = document.getElementById('address');

        return address
            ? address.value.trim()
            : '';

    }


    function updateModalHouseholdAddress() {

        modalHouseholdAddress.textContent =
            householdAddress() ||
            'Please enter the household address above.';

    }


    function updateOccupationOtherVisibility() {

        const isOther = fields.occupation.value === 'Other';

        occupationOtherWrap.classList.toggle('show', isOther);

        if (!isOther) {
            fields.occupation_other.value = '';
        }

    }


    function updateStudentEducationVisibility() {

        const isStudent = fields.is_student.checked;

        studentEducationWrap.classList.toggle('show', isStudent);

        if (!isStudent) {
            fields.student_education_level.value = '';
        }

    }


    function updateWorkflow() {

        const hasHead = !!householdHead;
        const hasAddress = !!householdAddress();

        workflowHeadStep.classList.remove('active', 'complete');
        workflowLocationStep.classList.remove('active', 'complete');
        workflowMembersStep.classList.remove('active', 'complete');

        if (!hasHead) {
            workflowHeadStep.classList.add('active');
            return;
        }

        workflowHeadStep.classList.add('complete');

        if (!hasAddress) {
            workflowLocationStep.classList.add('active');
            return;
        }

        workflowLocationStep.classList.add('complete');
        workflowMembersStep.classList.add('active');

    }


    function resetModal() {

        Object.values(fields).forEach(
            function (field) {

                if (!field) {
                    return;
                }

                if (field.type === 'checkbox') {
                    field.checked = false;
                } else {
                    field.value = '';
                }

            }
        );

        modalError.classList.add('d-none');
        modalError.textContent = '';

        hideContactError();

        modalExistingNotice.classList.add('d-none');
        modalExistingNotice.innerHTML = '';

        editingIndex = null;
        selectedExistingResidentId = null;

        updateOccupationOtherVisibility();
        updateStudentEducationVisibility();
        updateModalHouseholdAddress();

    }


    /* =========================================================
       MODAL OPENING
    ========================================================== */

    function openNewHeadModal() {

        resetModal();

        modalMode = 'head-new';

        modalTitle.textContent = 'Register New Household Head';

        modalSubtitle.textContent =
            'This person will be saved as an individual resident record and assigned as the household head.';

        document.getElementById('relationshipField').classList.add('d-none');

        updateModalHouseholdAddress();

        residentModal.show();

    }


    function openNewMemberModal() {

        resetModal();

        modalMode = 'member-new';

        modalTitle.textContent = 'Register New Household Member';

        modalSubtitle.textContent =
            'This person will be saved as an individual resident record and linked to this household.';

        document.getElementById('relationshipField').classList.remove('d-none');

        updateModalHouseholdAddress();

        residentModal.show();

    }


    function openExistingMemberModal(person, index = null) {

        resetModal();

        modalMode = 'member-existing';

        editingIndex = index;

        selectedExistingResidentId =
            Number(person.resident_id || person.id);

        modalTitle.textContent =
            index === null
                ? 'Add Existing Household Member'
                : 'Edit Household Member';

        modalSubtitle.textContent =
            'The existing resident record will be linked; a duplicate resident record will not be created.';

        modalExistingNotice.classList.remove('d-none');

        modalExistingNotice.innerHTML =
            '<strong>' +
            '<i class="bi bi-person-check me-1"></i>' +
            ' Existing resident selected:' +
            '</strong> ' +
            esc(fullName(person));


        fields.first_name.value = person.first_name || '';
        fields.middle_name.value = person.middle_name || '';
        fields.last_name.value = person.last_name || '';
        fields.suffix.value = person.suffix || '';
        fields.birth_date.value = person.birth_date || '';
        fields.sex.value = person.sex || '';
        fields.civil_status.value = person.civil_status || '';
        fields.relationship.value = person.relationship || '';

        fields.residency_type.value =
            normalizeResidencyType(person.residency_type);

        fields.residency_start_year.value =
            person.residency_start_year || '';

        fields.residency_year_estimated.checked =
            toBool(person.residency_year_estimated);

        fields.contact_number.value = contactToLocalDigits(person.contact_number);
        fields.email.value = person.email || '';

        const occupationData = normalizeOccupation(person);

        fields.occupation.value = occupationData.occupation;
        fields.occupation_other.value = occupationData.occupation_other;


        /*
         * This person's OWN classification only.
         */
        const classification = normalizeClassification(person);

        fields.is_pwd.checked = classification.is_pwd;
        fields.is_senior_citizen.checked = classification.is_senior_citizen;
        fields.is_solo_parent.checked = classification.is_solo_parent;
        fields.is_student.checked = classification.is_student;
        fields.student_education_level.value =
            classification.student_education_level;


        updateOccupationOtherVisibility();
        updateStudentEducationVisibility();

        // updateStudentEducationVisibility clears the level when unchecked;
        // restore it for students.
        if (classification.is_student) {
            fields.student_education_level.value =
                classification.student_education_level;
        }

        document.getElementById('relationshipField').classList.remove('d-none');

        updateModalHouseholdAddress();

        residentModal.show();

    }


    function openEditMemberModal(index) {

        const person = members[index];

        if (!person || person.isHead) {
            return;
        }

        openExistingMemberModal(person, index);

    }


    /* =========================================================
       RESIDENT SEARCH
    ========================================================== */

    function findResident(id) {

        return residents.find(
            function (resident) {
                return Number(resident.id) === Number(id);
            }
        );

    }


    function selectExistingHead(resident) {

        householdHead = {

            ...resident,

            resident_id: resident.id,

            residency_type:
                normalizeResidencyType(resident.residency_type),

            mode: 'existing',

            isHead: true,

            relationship: 'Head',

            ...normalizeClassification(resident)

        };


        members = members.filter(
            function (member) {
                return !member.isHead;
            }
        );

        members.unshift({ ...householdHead });

        renderHead();
        renderMembers();
        hideHeadResults();
        updateWorkflow();

    }


    function renderHead() {

        const hasHead = !!householdHead;

        headPanel.classList.toggle('has-head', hasHead);
        headEmptyState.classList.toggle('d-none', hasHead);
        selectedHeadCard.classList.toggle('d-none', !hasHead);
        changeHeadButton.classList.toggle('d-none', !hasHead);
        registerNewHeadButton.classList.toggle('d-none', hasHead);
        memberSection.classList.toggle('d-none', !hasHead);
        memberLockedNotice.classList.toggle('d-none', hasHead);


        if (!hasHead) {

            selectedHeadCard.innerHTML = '';

            updateWorkflow();

            return;

        }


        selectedHeadCard.innerHTML = `

            <div class="person-card">

                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">

                    <div>

                        <div class="person-name">
                            ${esc(fullName(householdHead))}
                        </div>

                        <div class="person-meta mt-1">

                            ${esc(householdHead.birth_date || 'No birth date')}

                            ${
                                householdHead.sex
                                    ? ' • ' + esc(householdHead.sex)
                                    : ''
                            }

                        </div>

                        <div class="mt-2">

                            <span class="head-badge">

                                <i class="bi bi-star-fill"></i>

                                Household Head

                            </span>

                            <span class="${
                                householdHead.mode === 'existing'
                                    ? 'existing-badge'
                                    : 'new-badge'
                            }">

                                ${
                                    householdHead.mode === 'existing'
                                        ? 'Existing Resident'
                                        : 'New Resident'
                                }

                            </span>

                            ${classificationBadges(householdHead)}

                        </div>

                        ${headClassificationControls(householdHead)}

                    </div>


                    <div class="text-end">

                        <div class="status-indicator">

                            <span class="status-dot"></span>

                            Active

                        </div>

                        <div class="text-muted small mt-1">

                            Age:
                            ${esc(ageOf(householdHead.birth_date))}

                        </div>

                    </div>

                </div>

            </div>

        `;


        updateWorkflow();

    }


    function renderMembers() {

        const additional = members.filter(
            function (member) {
                return !member.isHead;
            }
        );

        emptyMembers.classList.toggle('d-none', additional.length > 0);

        memberTableWrap.classList.toggle('d-none', members.length === 0);

        membersTableBody.innerHTML = '';


        members.forEach(
            function (member, index) {

                const row = document.createElement('tr');

                row.innerHTML = `

                    <td>${index + 1}</td>

                    <td>
                        <strong>${esc(fullName(member))}</strong>
                    </td>

                    <td>${esc(member.birth_date || '—')}</td>

                    <td>${esc(ageOf(member.birth_date) || '—')}</td>

                    <td>${esc(member.sex || '—')}</td>

                    <td>
                        ${
                            member.isHead
                                ? '<span class="head-badge">Head</span>'
                                : esc(member.relationship || '—')
                        }
                    </td>

                    <td>
                        ${esc(normalizeResidencyType(member.residency_type) || '—')}
                    </td>

                    <td>
                        ${
                            member.isHead
                                ? '<span class="head-badge"><i class="bi bi-check2"></i> Yes</span>'
                                : '<span class="text-muted">No</span>'
                        }
                    </td>

                    <td>${classificationBadges(member)}</td>

                    <td>
                        ${
                            member.mode === 'existing'
                                ? '<span class="existing-badge">Existing</span>'
                                : '<span class="new-badge">New</span>'
                        }
                    </td>

                    <td>

                        ${
                            member.isHead

                                ? '<span class="text-muted small">Automatic</span>'

                                : `
                                    <div class="btn-group btn-group-sm">

                                        <button type="button"
                                                class="btn btn-outline-primary edit-member"
                                                data-index="${index}">

                                            <i class="bi bi-pencil"></i>

                                        </button>

                                        <button type="button"
                                                class="btn btn-outline-danger remove-member"
                                                data-index="${index}">

                                            <i class="bi bi-trash"></i>

                                        </button>

                                    </div>
                                `
                        }

                    </td>

                `;

                membersTableBody.appendChild(row);

            }
        );

    }


    function hideHeadResults() {

        headResults.classList.add('d-none');
        headResults.innerHTML = '';

    }


    function residentSearchHaystack(resident) {

        return normalize(
            [
                resident.first_name,
                resident.middle_name,
                resident.last_name,
                resident.suffix
            ]
            .filter(Boolean)
            .join(' ')
        );

    }


    function renderHeadResults(term) {

        const query = normalize(term);

        if (!query) {
            hideHeadResults();
            return;
        }

        const results =
            residents
                .filter(
                    function (resident) {
                        return residentSearchHaystack(resident).includes(query);
                    }
                )
                .slice(0, 20);

        headResults.classList.remove('d-none');

        if (!results.length) {

            headResults.innerHTML =
                '<div class="p-3 text-muted small">' +
                'No active resident found. ' +
                'You may register a new household head.' +
                '</div>';

            return;

        }

        headResults.innerHTML =
            results.map(
                function (resident) {

                    return `

                        <button type="button"
                                class="search-result"
                                data-resident-id="${resident.id}">

                            <strong>${esc(fullName(resident))}</strong>

                            <div class="small text-muted">

                                ${esc(resident.birth_date || 'No birth date')}

                                ${
                                    resident.sex
                                        ? ' • ' + esc(resident.sex)
                                        : ''
                                }

                            </div>

                        </button>

                    `;

                }
            ).join('');

    }


    function hideMemberResults() {

        memberResults.classList.add('d-none');
        memberResults.innerHTML = '';

    }


    function renderMemberResults(term) {

        const query = normalize(term);

        if (!query) {
            hideMemberResults();
            return;
        }

        const results =
            residents
                .filter(
                    function (resident) {

                        const alreadyAdded =
                            members.some(
                                function (member) {

                                    return Number(
                                        member.resident_id || member.id
                                    ) === Number(resident.id);

                                }
                            );

                        return (
                            !alreadyAdded &&
                            residentSearchHaystack(resident).includes(query)
                        );

                    }
                )
                .slice(0, 20);

        memberResults.classList.remove('d-none');

        if (!results.length) {

            memberResults.innerHTML =
                '<div class="p-3 text-muted small">' +
                'No available active resident found.' +
                '</div>';

            return;

        }

        memberResults.innerHTML =
            results.map(
                function (resident) {

                    return `

                        <button type="button"
                                class="search-result"
                                data-member-resident-id="${resident.id}">

                            <strong>${esc(fullName(resident))}</strong>

                            <div class="small text-muted">

                                ${esc(resident.birth_date || 'No birth date')}

                                ${
                                    resident.sex
                                        ? ' • ' + esc(resident.sex)
                                        : ''
                                }

                            </div>

                        </button>

                    `;

                }
            ).join('');

    }


    /* =========================================================
       PERSON VALIDATION
    ========================================================== */

    function validatePerson(person, requireRelationship) {

        const errors = [];

        if (!person.first_name) {
            errors.push('First name is required.');
        }

        if (!person.last_name) {
            errors.push('Last name is required.');
        }

        if (!person.birth_date) {
            errors.push('Birth date is required.');
        }

        if (!person.sex) {
            errors.push('Sex is required.');
        }

        if (!person.civil_status) {
            errors.push('Civil status is required.');
        }

        const normalizedResidency =
            normalizeResidencyType(person.residency_type);

        if (!validResidencyTypes.includes(normalizedResidency)) {
            errors.push('Please select a valid residency type.');
        }

        if (requireRelationship && !person.relationship) {
            errors.push('Relationship to the household head is required.');
        }

        if (
            person.residency_start_year &&
            (
                Number(person.residency_start_year) < 1900 ||
                Number(person.residency_start_year) > new Date().getFullYear()
            )
        ) {
            errors.push('Residency start year must be a valid year.');
        }

        if (
            person.contact_number &&
            !PH_CONTACT_FULL.test(String(person.contact_number))
        ) {
            errors.push(CONTACT_MESSAGE);
            showContactError(CONTACT_MESSAGE);
        }

        const age = ageOf(person.birth_date);

        if (
            modalMode === 'head-new' &&
            (age === '' || Number(age) < 18)
        ) {
            errors.push('The household head must be at least 18 years old.');
        }

        if (
            person.occupation === 'Other' &&
            !person.occupation_other
        ) {
            errors.push('Please specify the other occupation/status.');
        }

        if (
            toBool(person.is_student) &&
            !educationLevels.includes(person.student_education_level)
        ) {
            errors.push(
                'Please select the education level (Elementary, High School, or College) for a student.'
            );
        }

        return errors;

    }


    /* =========================================================
       READ MODAL DATA
    ========================================================== */

    function readModalPerson() {

        const occupationData =
            normalizeOccupation({
                occupation: fields.occupation.value,
                occupation_other: fields.occupation_other.value.trim()
            });

        const isStudent = fields.is_student.checked;

        const level = isStudent
            ? fields.student_education_level.value
            : '';

        return {

            resident_id: selectedExistingResidentId || null,

            first_name: fields.first_name.value.trim(),
            middle_name: fields.middle_name.value.trim(),
            last_name: fields.last_name.value.trim(),
            suffix: fields.suffix.value.trim(),
            birth_date: fields.birth_date.value,
            sex: fields.sex.value,
            civil_status: fields.civil_status.value,
            relationship: fields.relationship.value,

            residency_type:
                normalizeResidencyType(fields.residency_type.value),

            residency_start_year: fields.residency_start_year.value,
            residency_year_estimated: fields.residency_year_estimated.checked,
            contact_number: contactFromInput(),
            email: fields.email.value.trim(),

            occupation: occupationData.occupation,
            occupation_other: occupationData.occupation_other,

            is_pwd: fields.is_pwd.checked,
            is_senior_citizen: fields.is_senior_citizen.checked,
            is_solo_parent: fields.is_solo_parent.checked,
            is_student: isStudent,
            student_education_level: level,
            is_high_school: isStudent && level === 'High School',

            address: householdAddress()

        };

    }


    function duplicateInHousehold(person, ignoreIndex = null) {

        const key = identityKey(person);

        const duplicateMember =
            members.some(
                function (member, index) {

                    return (
                        index !== ignoreIndex &&
                        identityKey(member) === key
                    );

                }
            );

        const duplicateHead =
            householdHead &&
            identityKey(householdHead) === key &&
            ignoreIndex === null;

        return duplicateMember || duplicateHead;

    }


    /* =========================================================
       SAVE PERSON
    ========================================================== */

    function savePerson() {

        const person = readModalPerson();

        const errors =
            validatePerson(person, modalMode !== 'head-new');

        if (errors.length) {

            modalError.textContent = errors.join(' ');

            modalError.classList.remove('d-none');

            return;

        }


        /* NEW HEAD */
        if (modalMode === 'head-new') {

            householdHead = {

                ...person,

                mode: 'new',

                isHead: true,

                relationship: 'Head'

            };

            members = members.filter(
                function (member) {
                    return !member.isHead;
                }
            );

            members.unshift({ ...householdHead });

            residentModal.hide();

            renderHead();
            renderMembers();
            updateWorkflow();

            return;

        }


        /* EDIT EXISTING MEMBER */
        if (
            modalMode === 'member-existing' &&
            editingIndex !== null
        ) {

            const original = members[editingIndex];

            members[editingIndex] = {

                ...original,

                ...person,

                resident_id:
                    original.resident_id || person.resident_id,

                residency_type:
                    normalizeResidencyType(person.residency_type),

                mode: 'existing',

                isHead: false

            };

            residentModal.hide();

            renderMembers();

            return;

        }


        /* NEW / EXISTING MEMBER */
        const newPerson = {

            ...person,

            residency_type:
                normalizeResidencyType(person.residency_type),

            mode:
                modalMode === 'member-existing'
                    ? 'existing'
                    : 'new',

            isHead: false

        };

        if (
            modalMode === 'member-existing' &&
            selectedExistingResidentId
        ) {
            newPerson.resident_id = selectedExistingResidentId;
        }

        if (duplicateInHousehold(newPerson)) {

            modalError.textContent =
                'This resident is already included in the household.';

            modalError.classList.remove('d-none');

            return;

        }

        members.push(newPerson);

        residentModal.hide();

        renderMembers();

    }


    function addExistingMember(resident) {

        const residentId = Number(resident.id);

        if (
            members.some(
                function (member) {

                    return Number(
                        member.resident_id || member.id
                    ) === residentId;

                }
            )
        ) {

            alert('This resident is already included in the household.');

            return;

        }

        if (
            householdHead &&
            Number(
                householdHead.resident_id || householdHead.id
            ) === residentId
        ) {

            alert('This resident is already the household head.');

            return;

        }

        const person = {

            ...resident,

            resident_id: resident.id,

            residency_type:
                normalizeResidencyType(resident.residency_type),

            mode: 'existing',

            isHead: false,

            relationship: '',

            ...normalizeClassification(resident)

        };

        openExistingMemberModal(person, null);

    }


    function addMemberChooser() {

        if (!householdHead) {

            alert('Please select or register the household head first.');

            return;

        }

        openNewMemberModal();

    }


    /* =========================================================
       HIDDEN INPUTS
    ========================================================== */

    function appendHidden(container, name, value) {

        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = name;
        input.value = value ?? '';

        container.appendChild(input);

    }


    function appendPersonFields(container, prefix, person) {

        appendHidden(container, `${prefix}[first_name]`, person.first_name);
        appendHidden(container, `${prefix}[middle_name]`, person.middle_name);
        appendHidden(container, `${prefix}[last_name]`, person.last_name);
        appendHidden(container, `${prefix}[suffix]`, person.suffix);
        appendHidden(container, `${prefix}[birth_date]`, person.birth_date);
        appendHidden(container, `${prefix}[sex]`, person.sex);
        appendHidden(container, `${prefix}[civil_status]`, person.civil_status);

        appendHidden(
            container,
            `${prefix}[residency_type]`,
            normalizeResidencyType(person.residency_type)
        );

        appendHidden(
            container,
            `${prefix}[residency_start_year]`,
            person.residency_start_year
        );

        appendHidden(
            container,
            `${prefix}[residency_year_estimated]`,
            person.residency_year_estimated ? '1' : '0'
        );

        appendHidden(container, `${prefix}[contact_number]`, contactToFull(person.contact_number));
        appendHidden(container, `${prefix}[email]`, person.email);
        appendHidden(container, `${prefix}[occupation]`, person.occupation);
        appendHidden(container, `${prefix}[occupation_other]`, person.occupation_other);


        /*
         * Individual classification (this person only).
         * Always submitted, checked or not, so unchecked values reach
         * the controller as 0. `is_high_school` is DERIVED from the
         * education level for legacy compatibility.
         */
        const classification = normalizeClassification(person);

        appendHidden(container, `${prefix}[is_pwd]`, classification.is_pwd ? '1' : '0');

        appendHidden(
            container,
            `${prefix}[is_senior_citizen]`,
            classification.is_senior_citizen ? '1' : '0'
        );

        appendHidden(
            container,
            `${prefix}[is_solo_parent]`,
            classification.is_solo_parent ? '1' : '0'
        );

        appendHidden(
            container,
            `${prefix}[is_student]`,
            classification.is_student ? '1' : '0'
        );

        appendHidden(
            container,
            `${prefix}[student_education_level]`,
            classification.student_education_level
        );

        appendHidden(
            container,
            `${prefix}[is_high_school]`,
            classification.is_high_school ? '1' : '0'
        );

    }


    function buildHiddenFields() {

        headHiddenFields.innerHTML = '';
        memberHiddenFields.innerHTML = '';

        if (!householdHead) {
            return;
        }

        /* HOUSEHOLD HEAD */
        const headPrefix = 'head';

        if (householdHead.mode === 'existing') {

            appendHidden(
                headHiddenFields,
                `${headPrefix}[resident_id]`,
                householdHead.resident_id || householdHead.id
            );

        }

        appendPersonFields(headHiddenFields, headPrefix, householdHead);


        /* MEMBERS */
        members
            .filter(
                function (member) {
                    return !member.isHead;
                }
            )
            .forEach(
                function (member, index) {

                    const prefix = `members[${index}]`;

                    appendHidden(
                        memberHiddenFields,
                        `${prefix}[mode]`,
                        member.mode
                    );

                    if (member.mode === 'existing') {

                        appendHidden(
                            memberHiddenFields,
                            `${prefix}[resident_id]`,
                            member.resident_id || member.id
                        );

                    }

                    appendHidden(
                        memberHiddenFields,
                        `${prefix}[relationship]`,
                        member.relationship
                    );

                    appendPersonFields(memberHiddenFields, prefix, member);

                }
            );

    }


    /* =========================================================
       EVENTS
    ========================================================== */

    headSearch.addEventListener(
        'input',
        function () {
            renderHeadResults(this.value);
        }
    );


    clearHeadSearch.addEventListener(
        'click',
        function () {

            headSearch.value = '';

            hideHeadResults();

        }
    );


    document.addEventListener(
        'click',
        function (event) {

            if (
                !headResults.contains(event.target) &&
                event.target !== headSearch
            ) {
                hideHeadResults();
            }

            if (
                !memberResults.contains(event.target) &&
                event.target !== memberSearch
            ) {
                hideMemberResults();
            }

        }
    );


    headResults.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest('[data-resident-id]');

            if (!button) {
                return;
            }

            const resident = findResident(button.dataset.residentId);

            if (resident) {
                selectExistingHead(resident);
            }

        }
    );


    registerNewHeadButton.addEventListener('click', openNewHeadModal);


    changeHeadButton.addEventListener(
        'click',
        function () {

            householdHead = null;

            members = [];

            renderHead();
            renderMembers();

            headSearch.focus();

            updateWorkflow();

        }
    );


    /*
     * Head classification controls (inside the head card).
     * Updates the head state AND the head entry in the members list,
     * then re-renders so badges and hidden fields stay in sync.
     */
    function syncHeadMember() {

        const headMember = members.find(
            function (member) {
                return member.isHead;
            }
        );

        if (!headMember) {
            return;
        }

        headMember.is_pwd = householdHead.is_pwd;
        headMember.is_senior_citizen = householdHead.is_senior_citizen;
        headMember.is_solo_parent = householdHead.is_solo_parent;
        headMember.is_student = householdHead.is_student;
        headMember.student_education_level = householdHead.student_education_level;
        headMember.is_high_school = householdHead.is_high_school;

    }


    selectedHeadCard.addEventListener(
        'change',
        function (event) {

            if (!householdHead) {
                return;
            }

            const flag = event.target.closest('[data-head-flag]');
            const education = event.target.closest('[data-head-education]');

            if (!flag && !education) {
                return;
            }

            if (flag) {

                householdHead[flag.dataset.headFlag] = flag.checked;

                if (
                    flag.dataset.headFlag === 'is_student' &&
                    !flag.checked
                ) {
                    householdHead.student_education_level = '';
                }

            }

            if (education) {

                householdHead.student_education_level = education.value;

            }

            Object.assign(
                householdHead,
                normalizeClassification(householdHead)
            );

            syncHeadMember();

            renderHead();
            renderMembers();

        }
    );


    addMemberButton.addEventListener('click', addMemberChooser);


    memberSearch.addEventListener(
        'input',
        function () {
            renderMemberResults(this.value);
        }
    );


    clearMemberSearch.addEventListener(
        'click',
        function () {

            memberSearch.value = '';

            hideMemberResults();

        }
    );


    memberResults.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest('[data-member-resident-id]');

            if (!button) {
                return;
            }

            const resident = findResident(button.dataset.memberResidentId);

            if (resident) {

                addExistingMember(resident);

                memberSearch.value = '';

                hideMemberResults();

            }

        }
    );


    membersTableBody.addEventListener(
        'click',
        function (event) {

            const edit = event.target.closest('.edit-member');
            const remove = event.target.closest('.remove-member');

            if (edit) {
                openEditMemberModal(Number(edit.dataset.index));
            }

            if (remove) {

                const index = Number(remove.dataset.index);

                if (
                    confirm(
                        'Remove this household member from the registration list?'
                    )
                ) {

                    members.splice(index, 1);

                    renderMembers();

                }

            }

        }
    );


    saveResidentButton.addEventListener('click', savePerson);


    fields.occupation.addEventListener(
        'change',
        updateOccupationOtherVisibility
    );


    fields.is_student.addEventListener(
        'change',
        updateStudentEducationVisibility
    );


    document
        .getElementById('address')
        .addEventListener(
            'input',
            function () {

                updateModalHouseholdAddress();

                updateWorkflow();

            }
        );


    householdForm.addEventListener(
        'submit',
        function (event) {

            if (!householdHead) {

                event.preventDefault();

                alert('Please select or register the household head first.');

                return;

            }

            const headAge = ageOf(householdHead.birth_date);

            if (headAge === '' || Number(headAge) < 18) {

                event.preventDefault();

                alert('The household head must be at least 18 years old.');

                return;

            }

            if (!householdAddress()) {

                event.preventDefault();

                alert('Please enter the complete household address.');

                return;

            }

            // Every student (head included) needs an education level.
            const studentWithoutLevel = members.find(
                function (member) {

                    const classification = normalizeClassification(member);

                    return (
                        classification.is_student &&
                        !classification.student_education_level
                    );

                }
            );

            if (studentWithoutLevel) {

                event.preventDefault();

                alert(
                    'Please select the education level for ' +
                    fullName(studentWithoutLevel) +
                    ' (Elementary, High School, or College).'
                );

                return;

            }

            buildHiddenFields();

            const submitButton =
                document.getElementById('submitHouseholdButton');

            submitButton.disabled = true;

            submitButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-2"></span>' +
                'Registering...';

        }
    );


    /* =========================================================
       RESTORE OLD INPUT AFTER VALIDATION ERROR
    ========================================================== */

    if (
        oldHead &&
        Object.keys(oldHead).length
    ) {

        if (oldHead.resident_id) {

            const restoredHead = findResident(oldHead.resident_id);

            if (restoredHead) {

                selectExistingHead({

                    ...restoredHead,

                    ...oldHead,

                    id: restoredHead.id,

                    resident_id: restoredHead.id,

                    residency_type:
                        normalizeResidencyType(
                            oldHead.residency_type ||
                            restoredHead.residency_type
                        )

                });

            }

        } else if (
            oldHead.first_name ||
            oldHead.last_name
        ) {

            householdHead = {

                ...oldHead,

                residency_type:
                    normalizeResidencyType(oldHead.residency_type),

                mode: 'new',

                isHead: true,

                relationship: 'Head',

                ...normalizeClassification(oldHead)

            };

            members.unshift(householdHead);

        }

    }


    if (
        Array.isArray(oldMembers) &&
        oldMembers.length
    ) {

        oldMembers.forEach(
            function (member) {

                const restored = {

                    ...member,

                    residency_type:
                        normalizeResidencyType(member.residency_type),

                    isHead: false,

                    mode:
                        member.mode ||
                        (
                            member.resident_id
                                ? 'existing'
                                : 'new'
                        ),

                    ...normalizeClassification(member)

                };

                if (
                    !members.some(
                        function (existingMember) {

                            return (
                                identityKey(existingMember) ===
                                identityKey(restored)
                            );

                        }
                    )
                ) {

                    members.push(restored);

                }

            }
        );

    }


    /* =========================================================
       INITIAL UI STATE
    ========================================================== */

    updateModalHouseholdAddress();
    updateOccupationOtherVisibility();
    updateStudentEducationVisibility();
    renderHead();
    renderMembers();
    updateWorkflow();

});
</script>


{{-- ============================================================
     HOUSEHOLD LOCATION: GPS CAPTURE + MAP PICKER
     (separate block so the household/member logic above is untouched)
============================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    if (!latInput || !lngInput) {
        return;
    }

    /* =========================================================
       CONSTANTS
    ========================================================== */

    const BANI_CENTER = [16.187656924442535, 119.84779277322872];

    /* rough Bani area, used only to warn about typos / swapped values */
    const AREA = { south: 15.95, west: 119.6, north: 16.45, east: 120.1 };

    /* Barangay Poblacion border (same outline as the Household Map) */
    const BOUNDARY = [
        [16.19481, 119.83521],
        [16.19942, 119.84490],
        [16.19875, 119.85327],
        [16.20142, 119.85488],
        [16.19742, 119.85788],
        [16.19419, 119.86123],
        [16.19381, 119.87144],
        [16.19375, 119.87421],
        [16.19147, 119.87664],
        [16.19030, 119.87964],
        [16.18547, 119.87952],
        [16.18508, 119.87866],
        [16.18369, 119.87808],
        [16.18224, 119.87721],
        [16.18046, 119.87675],
        [16.17907, 119.87733],
        [16.17646, 119.87738],
        [16.17735, 119.86336],
        [16.18241, 119.83867],
        [16.18880, 119.83682]
    ];

    const BOUNDARY_LABEL_AT = [16.18975, 119.85315];

    const LEAFLET_CSS = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css';
    const LEAFLET_JS = 'https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js';

    /* =========================================================
       ELEMENTS
    ========================================================== */

    const locateButton = document.getElementById('locateCurrentButton');
    const pickButton = document.getElementById('pickOnMapButton');
    const clearButton = document.getElementById('clearCoordinatesButton');
    const feedback = document.getElementById('geoFeedback');
    const secureNotice = document.getElementById('geoSecureNotice');

    const summary = document.getElementById('geoSummary');
    const summaryIcon = document.getElementById('geoSummaryIcon');
    const summaryTitle = document.getElementById('geoSummaryTitle');
    const summaryCoords = document.getElementById('geoSummaryCoords');
    const summaryMeta = document.getElementById('geoSummaryMeta');

    const pickerElement = document.getElementById('locationPickerModal');
    const pickerModal = new bootstrap.Modal(pickerElement);
    const pickerMapElement = document.getElementById('locationPickerMap');
    const pickerLatitude = document.getElementById('pickerLatitude');
    const pickerLongitude = document.getElementById('pickerLongitude');
    const pickerStatus = document.getElementById('pickerStatus');
    const pickerDone = document.getElementById('pickerDoneButton');
    const pickerCancel = document.getElementById('pickerCancelButton');
    const pickerLayerButton = document.getElementById('pickerLayerButton');
    const pickerBorderButton = document.getElementById('pickerBorderButton');
    const pickerCenterPinButton = document.getElementById('pickerCenterPinButton');

    /* =========================================================
       STATE
    ========================================================== */

    let source = null;          // 'gps' | 'map' | 'manual' | null
    let accuracy = null;        // meters (GPS only)
    let programmatic = false;   // true while the script itself fills the inputs
    let locating = false;

    /* =========================================================
       COORDINATE HELPERS
    ========================================================== */

    function readPair() {

        const a = latInput.value.trim();
        const b = lngInput.value.trim();

        if (a === '' && b === '') {
            return { state: 'empty' };
        }

        if (a === '' || b === '') {
            return { state: 'incomplete' };
        }

        const lat = Number(a);
        const lng = Number(b);

        if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
            return { state: 'invalid' };
        }

        if (Math.abs(lat) > 90 || Math.abs(lng) > 180) {
            return { state: 'range' };
        }

        return { state: 'ok', lat: lat, lng: lng };

    }


    function formatCoord(value) {
        return Number(value).toFixed(7);
    }


    function inBani(lat, lng) {

        return (
            lat >= AREA.south &&
            lat <= AREA.north &&
            lng >= AREA.west &&
            lng <= AREA.east
        );

    }


    /* ray casting: is the point inside the Poblacion outline? */
    function inPoblacion(lat, lng) {

        let inside = false;

        for (let i = 0, j = BOUNDARY.length - 1; i < BOUNDARY.length; j = i++) {

            const yi = BOUNDARY[i][0];
            const xi = BOUNDARY[i][1];
            const yj = BOUNDARY[j][0];
            const xj = BOUNDARY[j][1];

            const crosses =
                ((yi > lat) !== (yj > lat)) &&
                (lng < ((xj - xi) * (lat - yi)) / (yj - yi) + xi);

            if (crosses) {
                inside = !inside;
            }

        }

        return inside;

    }


    function setCoords(lat, lng, newSource, newAccuracy) {

        programmatic = true;

        latInput.value = formatCoord(lat);
        lngInput.value = formatCoord(lng);

        programmatic = false;

        source = newSource;
        accuracy = (typeof newAccuracy === 'number') ? newAccuracy : null;

        refreshSummary();

    }


    /* =========================================================
       SUMMARY (always shows what will be submitted)
    ========================================================== */

    function setSummary(state, icon, title, coords, meta) {

        summary.dataset.state = state;
        summaryIcon.className = 'bi ' + icon;
        summaryTitle.textContent = title;
        summaryCoords.textContent = coords;
        summaryMeta.textContent = meta;

    }


    function refreshSummary() {

        const pair = readPair();

        if (pair.state === 'empty') {

            setSummary(
                'empty',
                'bi-geo-alt',
                'No coordinates captured yet',
                '\u2014',
                'Use one of the buttons above, or type the coordinates manually.'
            );

            return;

        }

        if (pair.state === 'incomplete') {

            setSummary(
                'invalid',
                'bi-exclamation-triangle-fill',
                'Incomplete coordinates',
                (latInput.value.trim() || '?') + ', ' + (lngInput.value.trim() || '?'),
                'Fill in both Latitude and Longitude, or clear both.'
            );

            return;

        }

        if (pair.state === 'invalid') {

            setSummary(
                'invalid',
                'bi-exclamation-triangle-fill',
                'Coordinates are not valid numbers',
                latInput.value.trim() + ', ' + lngInput.value.trim(),
                'Use decimal numbers only, for example 16.187657 and 119.847793.'
            );

            return;

        }

        if (pair.state === 'range') {

            setSummary(
                'invalid',
                'bi-exclamation-triangle-fill',
                'Coordinates are out of range',
                latInput.value.trim() + ', ' + lngInput.value.trim(),
                'Latitude must be between -90 and 90, and Longitude between -180 and 180.'
            );

            return;

        }

        const coords = latInput.value.trim() + ', ' + lngInput.value.trim();

        let title = 'Coordinates ready';
        let meta = '';

        if (source === 'gps') {

            title = 'Captured from this laptop\u2019s location';

            meta = 'Estimated accuracy about ' + Math.round(accuracy) + ' m.';

        } else if (source === 'map') {

            title = 'Selected on the map';

            meta = 'Pin placed manually on the map.';

        } else if (source === 'manual') {

            title = 'Entered manually';

            meta = 'Typed in by the user.';

        } else {

            title = 'Coordinates in the form';

            meta = 'Review them on the map before registering.';

        }

        let warn = false;

        if (!inBani(pair.lat, pair.lng)) {

            warn = true;

            meta += ' These coordinates are outside Bani. Check for a typo or swapped latitude and longitude.';

        } else if (!inPoblacion(pair.lat, pair.lng)) {

            warn = true;

            meta += ' This point is outside the Barangay Poblacion border. Make sure it is the correct house.';

        }

        if (source === 'gps' && accuracy > 100) {

            warn = true;

            meta += ' Accuracy is low, so please verify the pin on the map.';

        }

        setSummary(
            warn ? 'warn' : 'ok',
            warn ? 'bi-exclamation-triangle-fill' : 'bi-check-lg',
            title,
            coords,
            meta
        );

    }


    /* typing in the fields = manual entry */
    [latInput, lngInput].forEach(function (input) {

        input.addEventListener('input', function () {

            if (programmatic) {
                return;
            }

            source = 'manual';
            accuracy = null;

            refreshSummary();

        });

    });


    clearButton.addEventListener('click', function () {

        programmatic = true;

        latInput.value = '';
        lngInput.value = '';

        programmatic = false;

        source = null;
        accuracy = null;

        hideFeedback();
        refreshSummary();

    });


    /* =========================================================
       FEEDBACK
    ========================================================== */

    const ACTION_RETRY =
        '<button type="button" class="btn btn-sm btn-outline-primary" data-geo-action="retry">' +
        '<i class="bi bi-arrow-repeat me-1"></i>Try again</button>';

    const ACTION_MAP =
        '<button type="button" class="btn btn-sm btn-outline-secondary" data-geo-action="map">' +
        '<i class="bi bi-map me-1"></i>Pick on map instead</button>';

    const ACTION_VERIFY =
        '<button type="button" class="btn btn-sm btn-outline-secondary" data-geo-action="map">' +
        '<i class="bi bi-map me-1"></i>Verify on map</button>';


    function showFeedback(type, message, actions) {

        const icons = {
            success: 'bi-check-circle-fill',
            warn: 'bi-exclamation-triangle-fill',
            error: 'bi-x-octagon-fill',
            info: 'bi-info-circle-fill'
        };

        const lead = (type === 'loading')
            ? '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
            : '<i class="bi ' + icons[type] + '"></i>';

        feedback.className = 'geo-feedback is-' + (type === 'loading' ? 'info' : type);

        feedback.innerHTML =
            lead +
            '<div><div>' + message + '</div>' +
            (actions ? '<div class="geo-inline-actions">' + actions + '</div>' : '') +
            '</div>';

    }


    function hideFeedback() {

        feedback.className = 'geo-feedback d-none';
        feedback.innerHTML = '';

    }


    feedback.addEventListener('click', function (event) {

        const button = event.target.closest('[data-geo-action]');

        if (!button) {
            return;
        }

        if (button.dataset.geoAction === 'retry') {
            locateCurrentPosition();
        }

        if (button.dataset.geoAction === 'map') {
            pickerModal.show();
        }

    });


    /* =========================================================
       GEOLOCATION (one-time request, no continuous tracking)
    ========================================================== */

    function setLocating(state) {

        locating = state;

        locateButton.disabled = state;

        locateButton.querySelector('span').textContent =
            state ? 'Locating\u2026' : 'Locate Current Position';

        locateButton.querySelector('i').className =
            state ? 'bi bi-hourglass-split me-2' : 'bi bi-crosshair me-2';

    }


    function onLocateSuccess(position) {

        setLocating(false);

        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const acc = position.coords.accuracy;

        if (
            !Number.isFinite(lat) ||
            !Number.isFinite(lng) ||
            !Number.isFinite(acc)
        ) {

            showFeedback(
                'error',
                'The browser returned invalid location data. Please try again.',
                ACTION_RETRY + ACTION_MAP
            );

            return;

        }

        setCoords(lat, lng, 'gps', acc);

        const meters = Math.round(acc);

        if (acc <= 20) {

            showFeedback(
                'success',
                '<strong>Location captured.</strong> Accuracy is about ' + meters +
                ' m (good). The Latitude and Longitude fields were filled in. ' +
                'Please confirm the point is the actual house.',
                ACTION_VERIFY
            );

        } else if (acc <= 50) {

            showFeedback(
                'success',
                '<strong>Location captured.</strong> Accuracy is about ' + meters +
                ' m (fair). Verify the pin on the map before registering.',
                ACTION_VERIFY
            );

        } else if (acc <= 100) {

            showFeedback(
                'warn',
                '<strong>Moderate accuracy (about ' + meters + ' m).</strong> ' +
                'The coordinates were filled in, but the pin may be a few houses away. ' +
                'Verify or adjust it on the map.',
                ACTION_VERIFY + ACTION_RETRY
            );

        } else {

            showFeedback(
                'warn',
                '<strong>Poor accuracy (about ' + meters + ' m).</strong> ' +
                'Laptops often estimate location from Wi-Fi or the network, which can be far off. ' +
                'The coordinates were filled in, but please move the pin to the actual house on the map, or try again.',
                ACTION_VERIFY + ACTION_RETRY
            );

        }

    }


    function onLocateError(error) {

        setLocating(false);

        if (error && error.code === 1) {

            let message =
                '<strong>Location permission denied.</strong> ' +
                'Allow location access for this site in your browser (the lock icon beside the address bar), then try again.';

            if (!window.isSecureContext) {

                message +=
                    ' This page is not on HTTPS or localhost, and browsers block location access on insecure addresses.';

            }

            showFeedback('error', message, ACTION_RETRY + ACTION_MAP);

            return;

        }

        if (error && error.code === 2) {

            showFeedback(
                'error',
                '<strong>Location unavailable.</strong> ' +
                'Your device could not determine its position. Check that Windows or macOS location services are on, then try again.',
                ACTION_RETRY + ACTION_MAP
            );

            return;

        }

        if (error && error.code === 3) {

            showFeedback(
                'error',
                '<strong>The location request timed out.</strong> ' +
                'It took too long to get a position. Please try again.',
                ACTION_RETRY + ACTION_MAP
            );

            return;

        }

        showFeedback(
            'error',
            '<strong>Unable to get your location.</strong> Please try again or use the map.',
            ACTION_RETRY + ACTION_MAP
        );

    }


    function locateCurrentPosition() {

        if (locating) {
            return;
        }

        if (!('geolocation' in navigator)) {

            showFeedback(
                'error',
                '<strong>Geolocation is not supported</strong> by this browser. Use <em>Pick Location on Map</em> or type the coordinates.',
                ACTION_MAP
            );

            return;

        }

        setLocating(true);

        showFeedback(
            'loading',
            'Requesting your location\u2026 If the browser asks, choose <strong>Allow</strong>. This can take up to 20 seconds.'
        );

        navigator.geolocation.getCurrentPosition(
            onLocateSuccess,
            onLocateError,
            {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 0
            }
        );

    }


    locateButton.addEventListener('click', locateCurrentPosition);

    if (!window.isSecureContext) {
        secureNotice.classList.remove('d-none');
    }

    /* =========================================================
       LEAFLET LOADER (only when the picker is first opened)
    ========================================================== */

    let leafletPromise = null;

    function loadLeaflet() {

        if (window.L && typeof L.map === 'function') {
            return Promise.resolve();
        }

        if (leafletPromise) {
            return leafletPromise;
        }

        leafletPromise = new Promise(function (resolve, reject) {

            if (!document.querySelector('link[data-geo-leaflet]')) {

                const css = document.createElement('link');

                css.rel = 'stylesheet';
                css.href = LEAFLET_CSS;
                css.setAttribute('data-geo-leaflet', '1');

                document.head.appendChild(css);

            }

            const script = document.createElement('script');

            script.src = LEAFLET_JS;

            script.onload = function () {
                resolve();
            };

            script.onerror = function () {
                leafletPromise = null;
                reject(new Error('Leaflet failed to load'));
            };

            document.head.appendChild(script);

        });

        return leafletPromise;

    }

    /* =========================================================
       MAP PICKER
    ========================================================== */

    let map = null;
    let marker = null;
    let street = null;
    let satellite = null;
    let satelliteOn = true;
    let boundaryBounds = null;

    let snapshot = null;      // field values when the picker opened
    let confirmed = false;    // true only when "Use This Location" is pressed


    function pinIcon() {

        return L.divIcon({
            className: 'geo-pin-wrap',
            html: '<span class="geo-pin"></span>',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });

    }


    function updateReadout() {

        const pair = readPair();

        if (pair.state !== 'ok' || !marker) {

            pickerLatitude.textContent = '\u2014';
            pickerLongitude.textContent = '\u2014';
            pickerStatus.textContent = 'No pin placed yet. Click the map to place one.';

            pickerDone.disabled = true;
            pickerCenterPinButton.disabled = true;

            return;

        }

        pickerLatitude.textContent = formatCoord(pair.lat);
        pickerLongitude.textContent = formatCoord(pair.lng);

        if (!inBani(pair.lat, pair.lng)) {

            pickerStatus.textContent = 'This point is outside Bani. Check the pin.';

        } else if (!inPoblacion(pair.lat, pair.lng)) {

            pickerStatus.textContent = 'This point is outside the Barangay Poblacion border (red dashed line).';

        } else {

            pickerStatus.textContent = 'Inside the Barangay Poblacion border. Drag the pin to fine-tune it.';

        }

        pickerDone.disabled = false;
        pickerCenterPinButton.disabled = false;

    }


    function onPick(latlng) {

        setCoords(latlng.lat, latlng.lng, 'map', null);

        updateReadout();

    }


    function placeMarker(latlng) {

        if (!marker) {

            marker = L.marker(latlng, {
                draggable: true,
                keyboard: true,
                icon: pinIcon()
            }).addTo(map);

            marker.on('drag', function () {
                onPick(marker.getLatLng());
            });

            marker.on('dragend', function () {
                onPick(marker.getLatLng());
            });

        } else {

            marker.setLatLng(latlng);

        }

    }


    function initMap() {

        if (map) {
            return;
        }

        map = L.map(pickerMapElement, {
            center: BANI_CENTER,
            zoom: 16,
            maxZoom: 20,
            zoomControl: false
        });

        L.control.zoom({ position: 'bottomright' }).addTo(map);
        L.control.scale({ imperial: false, position: 'bottomleft' }).addTo(map);

        const tileOptions = {
            detectRetina: true,
            keepBuffer: 4,
            updateWhenZooming: false,
            minZoom: 10,
            maxZoom: 21
        };

        street = L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            Object.assign({}, tileOptions, {
                maxNativeZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            })
        );

        satellite = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            Object.assign({}, tileOptions, {
                maxNativeZoom: 18,
                attribution: 'Tiles &copy; Esri'
            })
        );

        satellite.addTo(map);

        /* Barangay Poblacion border (visual guide only) */
        L.polygon(BOUNDARY, {
            interactive: false,
            color: '#ffffff',
            weight: 6,
            opacity: .95,
            fill: false,
            lineJoin: 'round'
        }).addTo(map);

        const border = L.polygon(BOUNDARY, {
            interactive: false,
            color: '#dc2626',
            weight: 3,
            opacity: 1,
            dashArray: '8 6',
            lineJoin: 'round',
            fillColor: '#dc2626',
            fillOpacity: .06
        }).addTo(map);

        L.marker(BOUNDARY_LABEL_AT, {
            interactive: false,
            keyboard: false,
            icon: L.divIcon({
                className: 'geo-pin-wrap',
                html: '<span class="picker-label">Poblacion</span>',
                iconSize: [0, 0]
            })
        }).addTo(map);

        boundaryBounds = border.getBounds();

        map.on('click', function (event) {

            placeMarker(event.latlng);
            onPick(event.latlng);

        });

    }


    /* shows the pin from the current field values, or the barangay overview */
    function syncMapWithFields() {

        map.invalidateSize();

        const pair = readPair();

        if (pair.state === 'ok') {

            placeMarker([pair.lat, pair.lng]);

            map.setView([pair.lat, pair.lng], 18);

        } else {

            if (marker) {
                map.removeLayer(marker);
                marker = null;
            }

            map.fitBounds(boundaryBounds, { padding: [24, 24] });

        }

        updateReadout();

    }


    pickButton.addEventListener('click', function () {
        pickerModal.show();
    });


    pickerElement.addEventListener('show.bs.modal', function () {

        snapshot = {
            lat: latInput.value,
            lng: lngInput.value,
            source: source,
            accuracy: accuracy
        };

        confirmed = false;

    });


    pickerElement.addEventListener('shown.bs.modal', function () {

        loadLeaflet()
            .then(function () {

                initMap();
                syncMapWithFields();

            })
            .catch(function () {

                pickerMapElement.innerHTML =
                    '<div class="picker-error">' +
                    '<i class="bi bi-wifi-off fs-1 d-block mb-2"></i>' +
                    '<strong>The map could not load.</strong><br>' +
                    'The map library comes from the internet. Check your connection, then close this window and try again. ' +
                    'You can also type the coordinates manually.' +
                    '</div>';

            });

    });


    /* anything other than "Use This Location" restores the previous values */
    pickerElement.addEventListener('hidden.bs.modal', function () {

        if (!confirmed && snapshot) {

            programmatic = true;

            latInput.value = snapshot.lat;
            lngInput.value = snapshot.lng;

            programmatic = false;

            source = snapshot.source;
            accuracy = snapshot.accuracy;

            refreshSummary();

        }

    });


    pickerCancel.addEventListener('click', function () {
        pickerModal.hide();
    });


    pickerDone.addEventListener('click', function () {

        confirmed = true;

        pickerModal.hide();

        showFeedback(
            'success',
            '<strong>Location selected on the map.</strong> ' +
            'Review the coordinates below. Nothing is saved until you press Register Household.'
        );

    });


    pickerLayerButton.addEventListener('click', function () {

        if (!map) {
            return;
        }

        satelliteOn = !satelliteOn;

        if (satelliteOn) {

            map.removeLayer(street);
            satellite.addTo(map);

        } else {

            map.removeLayer(satellite);
            street.addTo(map);

        }

        pickerLayerButton.title = satelliteOn
            ? 'Switch to street map'
            : 'Switch to satellite map';

    });


    pickerBorderButton.addEventListener('click', function () {

        if (map && boundaryBounds) {
            map.fitBounds(boundaryBounds, { padding: [24, 24] });
        }

    });


    pickerCenterPinButton.addEventListener('click', function () {

        if (map && marker) {
            map.setView(marker.getLatLng(), Math.max(map.getZoom(), 18));
        }

    });


    /* =========================================================
       INITIAL STATE
    ========================================================== */

    refreshSummary();

});
</script>
@endpush

@endsection