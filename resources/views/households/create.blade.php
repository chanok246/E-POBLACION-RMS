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



    {{-- =========================================================
        WORKFLOW
    ========================================================== --}}
    <div class="workflow-card mb-4">

        <div class="row g-2 align-items-center">

            {{-- STEP 1 --}}
            <div class="col-12 col-lg-4">

                <div class="workflow-step active"
                     id="workflowHeadStep">

                    <div class="workflow-number">
                        1
                    </div>

                    <div>

                        <div class="workflow-title">
                            Household Head
                        </div>

                        <div class="workflow-text">
                            Select an existing resident or register a new head.
                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-1 d-none d-lg-flex">

                <div class="workflow-line"></div>

            </div>


            {{-- STEP 2 --}}
            <div class="col-12 col-lg-3">

                <div class="workflow-step"
                     id="workflowLocationStep">

                    <div class="workflow-number">
                        2
                    </div>

                    <div>

                        <div class="workflow-title">
                            Location & GPS
                        </div>

                        <div class="workflow-text">
                            Record the household address, purok, and house location.
                        </div>

                    </div>

                </div>

            </div>


            <div class="col-lg-1 d-none d-lg-flex">

                <div class="workflow-line"></div>

            </div>


            {{-- STEP 3 --}}
            <div class="col-12 col-lg-3">

                <div class="workflow-step"
                     id="workflowMembersStep">

                    <div class="workflow-number">
                        3
                    </div>

                    <div>

                        <div class="workflow-title">
                            Add Members
                        </div>

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


        {{-- =====================================================
            1. HOUSEHOLD HEAD
        ====================================================== --}}
        <div class="section-card mb-4">

            <div class="section-header">

                <div class="section-icon">
                    <i class="bi bi-person-badge-fill"></i>
                </div>

                <div>

                    <h4>
                        1. Household Head
                    </h4>

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


        {{-- =====================================================
            2. LOCATION & GPS
        ====================================================== --}}
        <div class="section-card mb-4">

            <div class="section-header">

                <div class="section-icon">
                    <i class="bi bi-geo-alt-fill"></i>
                </div>

                <div>

                    <h4>
                        2. Location & GPS
                    </h4>

                    <p>
                        Record the household address, purok, and the
                        house location collected during the BHW home visit.
                    </p>

                </div>

            </div>


            {{-- HOUSEHOLD INFORMATION --}}
            <div class="row g-4 mb-4">

                <div class="col-md-6">

                    <label class="form-label">
                        Household Number
                    </label>

                    <input type="text"
                           class="form-control"
                           value="AUTO GENERATED"
                           disabled>

                </div>


                <div class="col-md-6">

                    <label class="form-label">
                        Status
                    </label>

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

                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>

                    @enderror

                </div>


                <div class="col-12 col-md-4">

                    <label for="purok"
                           class="form-label">

                        Purok

                    </label>

                    <select name="purok"
                            id="purok"
                            class="form-select">

                        <option value="">
                            Select Purok
                        </option>

                        @for($p = 1; $p <= 7; $p++)

                            <option value="Purok {{ $p }}"
                                {{ old('purok') === 'Purok '.$p ? 'selected' : '' }}>

                                Purok {{ $p }}

                            </option>

                        @endfor

                    </select>

                </div>

            </div>


            {{-- GPS --}}
            <div class="gps-box">

                <div class="mb-3">

                    <div class="fw-bold">
                        BHW Home Visit & House Location
                    </div>

                    <div class="text-muted small mt-1">
                        Record the coordinates collected during the
                        house-to-house visit.
                    </div>

                </div>


                <div class="row g-3">

                    <div class="col-md-6">

                        <label for="latitude"
                               class="form-label">

                            Latitude

                        </label>

                        <input type="text"
                               name="latitude"
                               id="latitude"
                               class="form-control"
                               value="{{ old('latitude') }}"
                               placeholder="e.g. 16.123456">

                    </div>


                    <div class="col-md-6">

                        <label for="longitude"
                               class="form-label">

                            Longitude

                        </label>

                        <input type="text"
                               name="longitude"
                               id="longitude"
                               class="form-control"
                               value="{{ old('longitude') }}"
                               placeholder="e.g. 120.123456">

                    </div>


                    <div class="col-12">

                        <div class="notice-box">

                            <i class="bi bi-info-circle-fill"></i>

                            <div>

                                <strong>
                                    Human-collected location.
                                </strong>

                                GPS coordinates identify the household
                                location for the barangay map.
                                They do not automatically determine
                                eligibility or any disaster/relief decision.

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- =====================================================
            3. ADD HOUSEHOLD MEMBERS
        ====================================================== --}}
        <div class="section-card mb-4">

            <div class="section-header">

                <div class="section-icon">
                    <i class="bi bi-people-fill"></i>
                </div>

                <div>

                    <h4>
                        3. Add Household Members
                    </h4>

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

                    <strong>
                        Household Head First.
                    </strong>

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


        {{-- =====================================================
            FINAL CONFIRMATION
        ====================================================== --}}
        <div class="section-card">

            <div class="notice-box mb-4">

                <i class="bi bi-shield-check"></i>

                <div>

                    <strong>
                        Before registering:
                    </strong>

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


{{-- =============================================================
    RESIDENT FORM MODAL
============================================================= --}}
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

                        <label class="form-label">
                            Middle Name
                        </label>

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

                        <label class="form-label">
                            Suffix
                        </label>

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

                            <option value="">
                                Select
                            </option>

                            <option value="Male">
                                Male
                            </option>

                            <option value="Female">
                                Female
                            </option>

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

                            <option value="">
                                Select
                            </option>

                            <option value="Single">
                                Single
                            </option>

                            <option value="Married">
                                Married
                            </option>

                            <option value="Widowed">
                                Widowed
                            </option>

                            <option value="Divorced">
                                Divorced
                            </option>

                            <option value="Separated">
                                Separated
                            </option>

                            <option value="Live-in / Cohabiting">
                                Live-in / Cohabiting
                            </option>

                            <option value="Annulled">
                                Annulled
                            </option>

                            <option value="Not Applicable">
                                Not Applicable
                            </option>

                            <option value="Prefer not to say">
                                Prefer not to say
                            </option>

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

                            <option value="">
                                Select relationship
                            </option>

                            <option value="Spouse">
                                Spouse
                            </option>

                            <option value="Son">
                                Son
                            </option>

                            <option value="Daughter">
                                Daughter
                            </option>

                            <option value="Parent">
                                Parent
                            </option>

                            <option value="Sibling">
                                Sibling
                            </option>

                            <option value="Grandparent">
                                Grandparent
                            </option>

                            <option value="Grandchild">
                                Grandchild
                            </option>

                            <option value="Relative">
                                Relative
                            </option>

                            <option value="Other">
                                Other
                            </option>

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

                            <option value="">
                                Select residency type
                            </option>

                            <option value="Original Resident">
                                Original Resident
                            </option>

                            <option value="Migrant Resident">
                                Migrant Resident
                            </option>

                        </select>

                    </div>


                    {{-- RESIDENCY YEAR --}}
                    <div class="col-md-6">

                        <label class="form-label">
                            Residency Start Year
                        </label>

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


                    {{-- CONTACT --}}
                    <div class="col-md-6">

                        <label class="form-label">
                            Contact Number
                        </label>

                        <input id="resident_contact_number"
                               class="form-control"
                               placeholder="Optional">

                        <div class="required-note mt-1">

                            Leave blank if the resident has no contact number.

                        </div>

                    </div>


                    {{-- EMAIL --}}
                    <div class="col-md-6">

                        <label class="form-label">
                            Email
                        </label>

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

                            <option value="">
                                Select occupation / status
                            </option>

                            <option value="Student">
                                Student
                            </option>

                            <option value="Employed">
                                Employed
                            </option>

                            <option value="Self-Employed">
                                Self-Employed
                            </option>

                            <option value="Business Owner">
                                Business Owner
                            </option>

                            <option value="Farmer">
                                Farmer
                            </option>

                            <option value="Fisherman">
                                Fisherman
                            </option>

                            <option value="Homemaker">
                                Homemaker
                            </option>

                            <option value="Unemployed">
                                Unemployed
                            </option>

                            <option value="Retired">
                                Retired
                            </option>

                            <option value="Senior Citizen">
                                Senior Citizen
                            </option>

                            <option value="Child / Not Yet School-Age">
                                Child / Not Yet School-Age
                            </option>

                            <option value="Infant">
                                Infant
                            </option>

                            <option value="Other">
                                Other
                            </option>

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


                    {{-- HOUSEHOLD CLASSIFICATION --}}
                    <div class="col-12">

                        <label class="form-label d-block mb-2">
                            Household Classification
                        </label>

                        <div class="d-flex flex-wrap gap-4">

                            <div class="form-check">

                                <input class="form-check-input"
                                       type="checkbox"
                                       id="resident_is_pwd">

                                <label class="form-check-label"
                                       for="resident_is_pwd">

                                    PWD

                                </label>

                            </div>


                            <div class="form-check">

                                <input class="form-check-input"
                                       type="checkbox"
                                       id="resident_is_solo_parent">

                                <label class="form-check-label"
                                       for="resident_is_solo_parent">

                                    Solo Parent

                                </label>

                            </div>


                            <div class="form-check">

                                <input class="form-check-input"
                                       type="checkbox"
                                       id="resident_is_high_school">

                                <label class="form-check-label"
                                       for="resident_is_high_school">

                                    High School Student

                                </label>

                            </div>

                        </div>

                        <div class="required-note mt-2">

                            A person may have more than one classification, or none.
                            This does not affect the resident's record status.

                        </div>

                    </div>


                    {{-- INHERITED ADDRESS --}}
                    <div class="col-12">

                        <label class="form-label">
                            Resident Address
                        </label>

                        <div class="readonly-address-box">

                            <i class="bi bi-house-fill me-1"></i>

                            <strong>
                                Address inherited from household:
                            </strong>

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

    const oldHeadId =
        @json(old('head.resident_id'));


    /* =========================================================
       ELEMENTS
    ========================================================== */

    const householdForm =
        document.getElementById('householdForm');

    const headPanel =
        document.getElementById('headPanel');

    const headEmptyState =
        document.getElementById('headEmptyState');

    const selectedHeadCard =
        document.getElementById('selectedHeadCard');

    const headSearch =
        document.getElementById('headResidentSearch');

    const headResults =
        document.getElementById('headSearchResults');

    const clearHeadSearch =
        document.getElementById('clearHeadSearch');

    const registerNewHeadButton =
        document.getElementById('registerNewHeadButton');

    const changeHeadButton =
        document.getElementById('changeHeadButton');

    const memberSection =
        document.getElementById('memberSection');

    const memberLockedNotice =
        document.getElementById('memberLockedNotice');

    const addMemberButton =
        document.getElementById('addMemberButton');

    const memberSearch =
        document.getElementById('memberResidentSearch');

    const memberResults =
        document.getElementById('memberSearchResults');

    const clearMemberSearch =
        document.getElementById('clearMemberSearch');

    const emptyMembers =
        document.getElementById('emptyMembers');

    const memberTableWrap =
        document.getElementById('memberTableWrap');

    const membersTableBody =
        document.getElementById('membersTableBody');

    const headHiddenFields =
        document.getElementById('headHiddenFields');

    const memberHiddenFields =
        document.getElementById('memberHiddenFields');

    const modalElement =
        document.getElementById('residentFormModal');

    const residentModal =
        new bootstrap.Modal(modalElement);

    const modalTitle =
        document.getElementById('residentModalTitle');

    const modalSubtitle =
        document.getElementById('residentModalSubtitle');

    const modalExistingNotice =
        document.getElementById('modalExistingNotice');

    const modalError =
        document.getElementById('residentModalError');

    const saveResidentButton =
        document.getElementById('saveResidentButton');

    const modalHouseholdAddress =
        document.getElementById('modalHouseholdAddress');

    const occupationOtherWrap =
        document.getElementById('occupationOtherWrap');

    const workflowHeadStep =
        document.getElementById('workflowHeadStep');

    const workflowLocationStep =
        document.getElementById('workflowLocationStep');

    const workflowMembersStep =
        document.getElementById('workflowMembersStep');


    /* =========================================================
       MODAL FIELDS
    ========================================================== */

    const fields = {

        first_name:
            document.getElementById('resident_first_name'),

        middle_name:
            document.getElementById('resident_middle_name'),

        last_name:
            document.getElementById('resident_last_name'),

        suffix:
            document.getElementById('resident_suffix'),

        birth_date:
            document.getElementById('resident_birth_date'),

        sex:
            document.getElementById('resident_sex'),

        civil_status:
            document.getElementById('resident_civil_status'),

        relationship:
            document.getElementById('resident_relationship'),

        residency_type:
            document.getElementById('resident_residency_type'),

        residency_start_year:
            document.getElementById('resident_residency_start_year'),

        residency_year_estimated:
            document.getElementById('resident_residency_year_estimated'),

        contact_number:
            document.getElementById('resident_contact_number'),

        email:
            document.getElementById('resident_email'),

        occupation:
            document.getElementById('resident_occupation'),

        occupation_other:
            document.getElementById('resident_occupation_other'),

        is_pwd:
            document.getElementById('resident_is_pwd'),

        is_solo_parent:
            document.getElementById('resident_is_solo_parent'),

        is_high_school:
            document.getElementById('resident_is_high_school')

    };


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
     * Old input restored from the server (and hidden fields we
     * generate ourselves) can arrive as the string "1"/"0" rather
     * than a real boolean. This keeps every classification check
     * (badges, hidden-field output, checkbox state) consistent.
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

        const birth =
            new Date(birthDate + 'T00:00:00');

        if (Number.isNaN(birth.getTime())) {
            return '';
        }

        const today =
            new Date();

        let age =
            today.getFullYear() -
            birth.getFullYear();

        const monthDifference =
            today.getMonth() -
            birth.getMonth();

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
     * Current system values:
     * Original Resident
     * Migrant Resident
     *
     * Legacy values are normalized here so old resident
     * records/form data do not trigger "selected residency_type
     * is invalid".
     */
    function normalizeResidencyType(value) {

        const normalized =
            normalize(value);

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

        /*
         * Database default is Original Resident.
         * This also keeps existing records with an empty value
         * valid when they are reused.
         */
        if (!normalized) {
            return 'Original Resident';
        }

        return '';

    }


    function normalizeOccupation(person) {

        const occupation =
            person.occupation || '';

        if (
            occupationOptions.includes(occupation)
        ) {

            return {
                occupation: occupation,
                occupation_other:
                    person.occupation_other || ''
            };

        }

        if (occupation) {

            return {
                occupation: 'Other',
                occupation_other:
                    person.occupation_other ||
                    occupation
            };

        }

        return {
            occupation: '',
            occupation_other: ''
        };

    }


    /*
     * Bootstrap badges shown in the member table so staff can
     * see each person's classification(s) before submitting.
     * A person may have any combination of PWD / Solo Parent /
     * High School Student, or none at all.
     */
    function classificationBadges(person) {

        const badges = [];

        if (toBool(person.is_pwd)) {

            badges.push(
                '<span class="badge bg-danger">PWD</span>'
            );

        }

        if (toBool(person.is_solo_parent)) {

            badges.push(
                '<span class="badge bg-warning text-dark">Solo Parent</span>'
            );

        }

        if (toBool(person.is_high_school)) {

            badges.push(
                '<span class="badge bg-info text-dark">High School</span>'
            );

        }

        if (!badges.length) {

            return '<span class="text-muted">None</span>';

        }

        return badges.join(' ');

    }


    function identityKey(person) {

        const id =
            person.resident_id ||
            person.id;

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

        const address =
            document.getElementById('address');

        return address
            ? address.value.trim()
            : '';

    }


    function updateModalHouseholdAddress() {

        const address =
            householdAddress();

        modalHouseholdAddress.textContent =
            address ||
            'Please enter the household address above.';

    }


    function updateOccupationOtherVisibility() {

        const isOther =
            fields.occupation.value === 'Other';

        occupationOtherWrap.classList.toggle(
            'show',
            isOther
        );

        if (!isOther) {

            fields.occupation_other.value = '';

        }

    }


    function updateWorkflow() {

        const hasHead =
            !!householdHead;

        const hasAddress =
            !!householdAddress();

        workflowHeadStep.classList.remove(
            'active',
            'complete'
        );

        workflowLocationStep.classList.remove(
            'active',
            'complete'
        );

        workflowMembersStep.classList.remove(
            'active',
            'complete'
        );


        if (!hasHead) {

            workflowHeadStep.classList.add(
                'active'
            );

            return;

        }


        workflowHeadStep.classList.add(
            'complete'
        );


        if (!hasAddress) {

            workflowLocationStep.classList.add(
                'active'
            );

            return;

        }


        workflowLocationStep.classList.add(
            'complete'
        );

        workflowMembersStep.classList.add(
            'active'
        );

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

        modalError.classList.add(
            'd-none'
        );

        modalError.textContent = '';

        modalExistingNotice.classList.add(
            'd-none'
        );

        modalExistingNotice.innerHTML = '';

        editingIndex = null;

        selectedExistingResidentId = null;

        updateOccupationOtherVisibility();

        updateModalHouseholdAddress();

    }


    /* =========================================================
       MODAL OPENING
    ========================================================== */

    function openNewHeadModal() {

        resetModal();

        modalMode = 'head-new';

        modalTitle.textContent =
            'Register New Household Head';

        modalSubtitle.textContent =
            'This person will be saved as an individual resident record and assigned as the household head.';

        document
            .getElementById('relationshipField')
            .classList.add('d-none');

        updateModalHouseholdAddress();

        residentModal.show();

    }


    function openNewMemberModal() {

        resetModal();

        modalMode = 'member-new';

        modalTitle.textContent =
            'Register New Household Member';

        modalSubtitle.textContent =
            'This person will be saved as an individual resident record and linked to this household.';

        document
            .getElementById('relationshipField')
            .classList.remove('d-none');

        updateModalHouseholdAddress();

        residentModal.show();

    }


    function openExistingMemberModal(
        person,
        index = null
    ) {

        resetModal();

        modalMode = 'member-existing';

        editingIndex = index;

        selectedExistingResidentId =
            Number(
                person.resident_id ||
                person.id
            );

        modalTitle.textContent =
            index === null
                ? 'Add Existing Household Member'
                : 'Edit Household Member';

        modalSubtitle.textContent =
            'The existing resident record will be linked; a duplicate resident record will not be created.';

        modalExistingNotice.classList.remove(
            'd-none'
        );

        modalExistingNotice.innerHTML =
            '<strong>' +
            '<i class="bi bi-person-check me-1"></i>' +
            ' Existing resident selected:' +
            '</strong> ' +
            esc(fullName(person));


        fields.first_name.value =
            person.first_name || '';

        fields.middle_name.value =
            person.middle_name || '';

        fields.last_name.value =
            person.last_name || '';

        fields.suffix.value =
            person.suffix || '';

        fields.birth_date.value =
            person.birth_date || '';

        fields.sex.value =
            person.sex || '';

        fields.civil_status.value =
            person.civil_status || '';

        fields.relationship.value =
            person.relationship || '';


        /*
         * IMPORTANT:
         * Normalize old residency values here.
         */
        fields.residency_type.value =
            normalizeResidencyType(
                person.residency_type
            );


        fields.residency_start_year.value =
            person.residency_start_year || '';

        fields.residency_year_estimated.checked =
            Boolean(
                person.residency_year_estimated
            );

        fields.contact_number.value =
            person.contact_number || '';

        fields.email.value =
            person.email || '';


        const occupationData =
            normalizeOccupation(person);

        fields.occupation.value =
            occupationData.occupation;

        fields.occupation_other.value =
            occupationData.occupation_other;


        /*
         * Household classification.
         * Loads existing values when editing/re-adding a person
         * that already carries classification data; defaults to
         * unchecked otherwise (handled by resetModal above).
         */
        fields.is_pwd.checked =
            toBool(person.is_pwd);

        fields.is_solo_parent.checked =
            toBool(person.is_solo_parent);

        fields.is_high_school.checked =
            toBool(person.is_high_school);


        updateOccupationOtherVisibility();

        document
            .getElementById('relationshipField')
            .classList.remove('d-none');

        updateModalHouseholdAddress();

        residentModal.show();

    }


    function openEditMemberModal(index) {

        const person =
            members[index];

        if (!person || person.isHead) {
            return;
        }

        openExistingMemberModal(
            person,
            index
        );

    }


    /* =========================================================
       RESIDENT SEARCH
    ========================================================== */

    function findResident(id) {

        return residents.find(
            function (resident) {

                return Number(resident.id) ===
                    Number(id);

            }
        );

    }


    function selectExistingHead(resident) {

        householdHead = {

            ...resident,

            resident_id:
                resident.id,

            residency_type:
                normalizeResidencyType(
                    resident.residency_type
                ),

            mode:
                'existing',

            isHead:
                true,

            relationship:
                'Head',

            is_pwd:
                toBool(resident.is_pwd),

            is_solo_parent:
                toBool(resident.is_solo_parent),

            is_high_school:
                toBool(resident.is_high_school)

        };


        members =
            members.filter(
                function (member) {

                    return !member.isHead;

                }
            );


        members.unshift({
            ...householdHead
        });


        renderHead();

        renderMembers();

        hideHeadResults();

        updateWorkflow();

    }


    function renderHead() {

        const hasHead =
            !!householdHead;

        headPanel.classList.toggle(
            'has-head',
            hasHead
        );

        headEmptyState.classList.toggle(
            'd-none',
            hasHead
        );

        selectedHeadCard.classList.toggle(
            'd-none',
            !hasHead
        );

        changeHeadButton.classList.toggle(
            'd-none',
            !hasHead
        );

        registerNewHeadButton.classList.toggle(
            'd-none',
            hasHead
        );

        memberSection.classList.toggle(
            'd-none',
            !hasHead
        );

        memberLockedNotice.classList.toggle(
            'd-none',
            hasHead
        );


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

                            ${esc(
                                householdHead.birth_date ||
                                'No birth date'
                            )}

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

                    </div>


                    <div class="text-end">

                        <div class="status-indicator">

                            <span class="status-dot"></span>

                            Active

                        </div>

                        <div class="text-muted small mt-1">

                            Age:
                            ${esc(
                                ageOf(
                                    householdHead.birth_date
                                )
                            )}

                        </div>

                    </div>

                </div>

            </div>

        `;


        updateWorkflow();

    }


    function renderMembers() {

        const additional =
            members.filter(
                function (member) {

                    return !member.isHead;

                }
            );


        emptyMembers.classList.toggle(
            'd-none',
            additional.length > 0
        );


        memberTableWrap.classList.toggle(
            'd-none',
            members.length === 0
        );


        membersTableBody.innerHTML = '';


        members.forEach(
            function (member, index) {

                const row =
                    document.createElement('tr');


                row.innerHTML = `

                    <td>
                        ${index + 1}
                    </td>

                    <td>
                        <strong>
                            ${esc(fullName(member))}
                        </strong>
                    </td>

                    <td>
                        ${esc(
                            member.birth_date || '—'
                        )}
                    </td>

                    <td>
                        ${esc(
                            ageOf(member.birth_date) || '—'
                        )}
                    </td>

                    <td>
                        ${esc(member.sex || '—')}
                    </td>

                    <td>
                        ${
                            member.isHead
                                ? '<span class="head-badge">Head</span>'
                                : esc(member.relationship || '—')
                        }
                    </td>

                    <td>
                        ${esc(
                            normalizeResidencyType(
                                member.residency_type
                            ) || '—'
                        )}
                    </td>

                    <td>
                        ${
                            member.isHead
                                ? '<span class="head-badge"><i class="bi bi-check2"></i> Yes</span>'
                                : '<span class="text-muted">No</span>'
                        }
                    </td>

                    <td>
                        ${classificationBadges(member)}
                    </td>

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

        headResults.classList.add(
            'd-none'
        );

        headResults.innerHTML = '';

    }


    function renderHeadResults(term) {

        const query =
            normalize(term);

        if (!query) {

            hideHeadResults();

            return;

        }


        const results =
            residents
                .filter(
                    function (resident) {

                        const haystack =
                            normalize(
                                [
                                    resident.first_name,
                                    resident.middle_name,
                                    resident.last_name,
                                    resident.suffix
                                ]
                                .filter(Boolean)
                                .join(' ')
                            );

                        return haystack.includes(query);

                    }
                )
                .slice(0, 20);


        if (!results.length) {

            headResults.classList.remove(
                'd-none'
            );

            headResults.innerHTML =
                '<div class="p-3 text-muted small">' +
                'No active resident found. ' +
                'You may register a new household head.' +
                '</div>';

            return;

        }


        headResults.classList.remove(
            'd-none'
        );


        headResults.innerHTML =
            results.map(
                function (resident) {

                    return `

                        <button type="button"
                                class="search-result"
                                data-resident-id="${resident.id}">

                            <strong>
                                ${esc(fullName(resident))}
                            </strong>

                            <div class="small text-muted">

                                ${esc(
                                    resident.birth_date ||
                                    'No birth date'
                                )}

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

        memberResults.classList.add(
            'd-none'
        );

        memberResults.innerHTML = '';

    }


    function renderMemberResults(term) {

        const query =
            normalize(term);

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
                                        member.resident_id ||
                                        member.id
                                    ) === Number(resident.id);

                                }
                            );


                        const haystack =
                            normalize(
                                [
                                    resident.first_name,
                                    resident.middle_name,
                                    resident.last_name,
                                    resident.suffix
                                ]
                                .filter(Boolean)
                                .join(' ')
                            );


                        return (
                            !alreadyAdded &&
                            haystack.includes(query)
                        );

                    }
                )
                .slice(0, 20);


        memberResults.classList.remove(
            'd-none'
        );


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

                            <strong>
                                ${esc(fullName(resident))}
                            </strong>

                            <div class="small text-muted">

                                ${esc(
                                    resident.birth_date ||
                                    'No birth date'
                                )}

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

    function validatePerson(
        person,
        requireRelationship
    ) {

        const errors = [];


        if (!person.first_name) {

            errors.push(
                'First name is required.'
            );

        }


        if (!person.last_name) {

            errors.push(
                'Last name is required.'
            );

        }


        if (!person.birth_date) {

            errors.push(
                'Birth date is required.'
            );

        }


        if (!person.sex) {

            errors.push(
                'Sex is required.'
            );

        }


        if (!person.civil_status) {

            errors.push(
                'Civil status is required.'
            );

        }


        const normalizedResidency =
            normalizeResidencyType(
                person.residency_type
            );


        if (
            !validResidencyTypes.includes(
                normalizedResidency
            )
        ) {

            errors.push(
                'Please select a valid residency type.'
            );

        }


        if (requireRelationship && !person.relationship) {

            errors.push(
                'Relationship to the household head is required.'
            );

        }


        if (
            person.residency_start_year &&
            (
                Number(person.residency_start_year) < 1900 ||
                Number(person.residency_start_year) >
                new Date().getFullYear()
            )
        ) {

            errors.push(
                'Residency start year must be a valid year.'
            );

        }


        const age =
            ageOf(person.birth_date);


        if (
            modalMode === 'head-new' &&
            (
                age === '' ||
                Number(age) < 18
            )
        ) {

            errors.push(
                'The household head must be at least 18 years old.'
            );

        }


        if (
            person.occupation === 'Other' &&
            !person.occupation_other
        ) {

            errors.push(
                'Please specify the other occupation/status.'
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
                occupation:
                    fields.occupation.value,

                occupation_other:
                    fields.occupation_other.value.trim()
            });


        return {

            resident_id:
                selectedExistingResidentId || null,

            first_name:
                fields.first_name.value.trim(),

            middle_name:
                fields.middle_name.value.trim(),

            last_name:
                fields.last_name.value.trim(),

            suffix:
                fields.suffix.value.trim(),

            birth_date:
                fields.birth_date.value,

            sex:
                fields.sex.value,

            civil_status:
                fields.civil_status.value,

            relationship:
                fields.relationship.value,

            residency_type:
                normalizeResidencyType(
                    fields.residency_type.value
                ),

            residency_start_year:
                fields.residency_start_year.value,

            residency_year_estimated:
                fields.residency_year_estimated.checked,

            contact_number:
                fields.contact_number.value.trim(),

            email:
                fields.email.value.trim(),

            occupation:
                occupationData.occupation,

            occupation_other:
                occupationData.occupation_other,

            is_pwd:
                fields.is_pwd.checked,

            is_solo_parent:
                fields.is_solo_parent.checked,

            is_high_school:
                fields.is_high_school.checked,

            address:
                householdAddress()

        };

    }


    function duplicateInHousehold(
        person,
        ignoreIndex = null
    ) {

        const key =
            identityKey(person);


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


        return duplicateMember ||
            duplicateHead;

    }


    /* =========================================================
       SAVE PERSON
    ========================================================== */

    function savePerson() {

        const person =
            readModalPerson();


        const errors =
            validatePerson(
                person,
                modalMode !== 'head-new'
            );


        if (errors.length) {

            modalError.textContent =
                errors.join(' ');

            modalError.classList.remove(
                'd-none'
            );

            return;

        }


        /* -----------------------------------------------------
           NEW HEAD
        ------------------------------------------------------ */

        if (modalMode === 'head-new') {

            householdHead = {

                ...person,

                mode:
                    'new',

                isHead:
                    true,

                relationship:
                    'Head'

            };


            members =
                members.filter(
                    function (member) {

                        return !member.isHead;

                    }
                );


            members.unshift({
                ...householdHead
            });


            residentModal.hide();

            renderHead();

            renderMembers();

            updateWorkflow();

            return;

        }


        /* -----------------------------------------------------
           EDIT EXISTING MEMBER
        ------------------------------------------------------ */

        if (
            modalMode === 'member-existing' &&
            editingIndex !== null
        ) {

            const original =
                members[editingIndex];


            members[editingIndex] = {

                ...original,

                ...person,

                resident_id:
                    original.resident_id ||
                    person.resident_id,

                residency_type:
                    normalizeResidencyType(
                        person.residency_type
                    ),

                mode:
                    'existing',

                isHead:
                    false

            };


            residentModal.hide();

            renderMembers();

            return;

        }


        /* -----------------------------------------------------
           NEW / EXISTING MEMBER
        ------------------------------------------------------ */

        const newPerson = {

            ...person,

            residency_type:
                normalizeResidencyType(
                    person.residency_type
                ),

            mode:
                modalMode === 'member-existing'
                    ? 'existing'
                    : 'new',

            isHead:
                false

        };


        if (
            modalMode === 'member-existing' &&
            selectedExistingResidentId
        ) {

            newPerson.resident_id =
                selectedExistingResidentId;

        }


        if (
            duplicateInHousehold(newPerson)
        ) {

            modalError.textContent =
                'This resident is already included in the household.';

            modalError.classList.remove(
                'd-none'
            );

            return;

        }


        members.push(
            newPerson
        );


        residentModal.hide();

        renderMembers();

    }


    function addExistingMember(resident) {

        const residentId =
            Number(resident.id);


        if (
            members.some(
                function (member) {

                    return Number(
                        member.resident_id ||
                        member.id
                    ) === residentId;

                }
            )
        ) {

            alert(
                'This resident is already included in the household.'
            );

            return;

        }


        if (
            householdHead &&
            Number(
                householdHead.resident_id ||
                householdHead.id
            ) === residentId
        ) {

            alert(
                'This resident is already the household head.'
            );

            return;

        }


        const person = {

            ...resident,

            resident_id:
                resident.id,

            residency_type:
                normalizeResidencyType(
                    resident.residency_type
                ),

            mode:
                'existing',

            isHead:
                false,

            relationship:
                '',

            is_pwd:
                toBool(resident.is_pwd),

            is_solo_parent:
                toBool(resident.is_solo_parent),

            is_high_school:
                toBool(resident.is_high_school)

        };


        openExistingMemberModal(
            person,
            null
        );

    }


    function addMemberChooser() {

        if (!householdHead) {

            alert(
                'Please select or register the household head first.'
            );

            return;

        }


        openNewMemberModal();

    }


    /* =========================================================
       HIDDEN INPUTS
    ========================================================== */

    function appendHidden(
        container,
        name,
        value
    ) {

        const input =
            document.createElement('input');

        input.type =
            'hidden';

        input.name =
            name;

        input.value =
            value ?? '';

        container.appendChild(input);

    }


    function appendPersonFields(
        container,
        prefix,
        person
    ) {

        appendHidden(
            container,
            `${prefix}[first_name]`,
            person.first_name
        );

        appendHidden(
            container,
            `${prefix}[middle_name]`,
            person.middle_name
        );

        appendHidden(
            container,
            `${prefix}[last_name]`,
            person.last_name
        );

        appendHidden(
            container,
            `${prefix}[suffix]`,
            person.suffix
        );

        appendHidden(
            container,
            `${prefix}[birth_date]`,
            person.birth_date
        );

        appendHidden(
            container,
            `${prefix}[sex]`,
            person.sex
        );

        appendHidden(
            container,
            `${prefix}[civil_status]`,
            person.civil_status
        );

        appendHidden(
            container,
            `${prefix}[residency_type]`,
            normalizeResidencyType(
                person.residency_type
            )
        );

        appendHidden(
            container,
            `${prefix}[residency_start_year]`,
            person.residency_start_year
        );

        appendHidden(
            container,
            `${prefix}[residency_year_estimated]`,
            person.residency_year_estimated
                ? '1'
                : '0'
        );

        appendHidden(
            container,
            `${prefix}[contact_number]`,
            person.contact_number
        );

        appendHidden(
            container,
            `${prefix}[email]`,
            person.email
        );

        appendHidden(
            container,
            `${prefix}[occupation]`,
            person.occupation
        );

        appendHidden(
            container,
            `${prefix}[occupation_other]`,
            person.occupation_other
        );


        /*
         * Household classification — belongs to the
         * HouseholdMember record, never to the Resident record.
         * Always submitted (checked or not) so unchecked boxes
         * still reach the controller as 0.
         */
        appendHidden(
            container,
            `${prefix}[is_pwd]`,
            toBool(person.is_pwd)
                ? '1'
                : '0'
        );

        appendHidden(
            container,
            `${prefix}[is_solo_parent]`,
            toBool(person.is_solo_parent)
                ? '1'
                : '0'
        );

        appendHidden(
            container,
            `${prefix}[is_high_school]`,
            toBool(person.is_high_school)
                ? '1'
                : '0'
        );

    }


    function buildHiddenFields() {

        headHiddenFields.innerHTML = '';

        memberHiddenFields.innerHTML = '';


        if (!householdHead) {
            return;
        }


        /* -----------------------------------------------------
           HOUSEHOLD HEAD
        ------------------------------------------------------ */

        const headPrefix =
            'head';


        /*
         * Existing heads also send their complete data.
         * resident_id is still included so the controller can
         * reuse the existing resident record.
         */
        if (
            householdHead.mode === 'existing'
        ) {

            appendHidden(
                headHiddenFields,
                `${headPrefix}[resident_id]`,
                householdHead.resident_id ||
                householdHead.id
            );

        }


        appendPersonFields(
            headHiddenFields,
            headPrefix,
            householdHead
        );


        /* -----------------------------------------------------
           MEMBERS
        ------------------------------------------------------ */

        members
            .filter(
                function (member) {

                    return !member.isHead;

                }
            )
            .forEach(
                function (member, index) {

                    const prefix =
                        `members[${index}]`;


                    appendHidden(
                        memberHiddenFields,
                        `${prefix}[mode]`,
                        member.mode
                    );


                    if (
                        member.mode === 'existing'
                    ) {

                        appendHidden(
                            memberHiddenFields,
                            `${prefix}[resident_id]`,
                            member.resident_id ||
                            member.id
                        );

                    }


                    appendHidden(
                        memberHiddenFields,
                        `${prefix}[relationship]`,
                        member.relationship
                    );


                    appendPersonFields(
                        memberHiddenFields,
                        prefix,
                        member
                    );

                }
            );

    }


    /* =========================================================
       EVENTS
    ========================================================== */

    headSearch.addEventListener(
        'input',
        function () {

            renderHeadResults(
                this.value
            );

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
                event.target.closest(
                    '[data-resident-id]'
                );


            if (!button) {
                return;
            }


            const resident =
                findResident(
                    button.dataset.residentId
                );


            if (resident) {

                selectExistingHead(
                    resident
                );

            }

        }
    );


    registerNewHeadButton.addEventListener(
        'click',
        openNewHeadModal
    );


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


    addMemberButton.addEventListener(
        'click',
        addMemberChooser
    );


    memberSearch.addEventListener(
        'input',
        function () {

            renderMemberResults(
                this.value
            );

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
                event.target.closest(
                    '[data-member-resident-id]'
                );


            if (!button) {
                return;
            }


            const resident =
                findResident(
                    button.dataset.memberResidentId
                );


            if (resident) {

                addExistingMember(
                    resident
                );

                memberSearch.value = '';

                hideMemberResults();

            }

        }
    );


    membersTableBody.addEventListener(
        'click',
        function (event) {

            const edit =
                event.target.closest(
                    '.edit-member'
                );

            const remove =
                event.target.closest(
                    '.remove-member'
                );


            if (edit) {

                openEditMemberModal(
                    Number(
                        edit.dataset.index
                    )
                );

            }


            if (remove) {

                const index =
                    Number(
                        remove.dataset.index
                    );


                if (
                    confirm(
                        'Remove this household member from the registration list?'
                    )
                ) {

                    members.splice(
                        index,
                        1
                    );

                    renderMembers();

                }

            }

        }
    );


    saveResidentButton.addEventListener(
        'click',
        savePerson
    );


    fields.occupation.addEventListener(
        'change',
        updateOccupationOtherVisibility
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

                alert(
                    'Please select or register the household head first.'
                );

                return;

            }


            const headAge =
                ageOf(
                    householdHead.birth_date
                );


            if (
                headAge === '' ||
                Number(headAge) < 18
            ) {

                event.preventDefault();

                alert(
                    'The household head must be at least 18 years old.'
                );

                return;

            }


            if (!householdAddress()) {

                event.preventDefault();

                alert(
                    'Please enter the complete household address.'
                );

                return;

            }


            buildHiddenFields();


            const submitButton =
                document.getElementById(
                    'submitHouseholdButton'
                );


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

            const restoredHead =
                findResident(
                    oldHead.resident_id
                );


            if (restoredHead) {

                selectExistingHead({

                    ...restoredHead,

                    ...oldHead,

                    id:
                        restoredHead.id,

                    resident_id:
                        restoredHead.id,

                    residency_type:
                        normalizeResidencyType(
                            oldHead.residency_type ||
                            restoredHead.residency_type
                        ),

                    is_pwd:
                        toBool(oldHead.is_pwd),

                    is_solo_parent:
                        toBool(oldHead.is_solo_parent),

                    is_high_school:
                        toBool(oldHead.is_high_school)

                });

            }

        } else if (
            oldHead.first_name ||
            oldHead.last_name
        ) {

            householdHead = {

                ...oldHead,

                residency_type:
                    normalizeResidencyType(
                        oldHead.residency_type
                    ),

                mode:
                    'new',

                isHead:
                    true,

                relationship:
                    'Head',

                is_pwd:
                    toBool(oldHead.is_pwd),

                is_solo_parent:
                    toBool(oldHead.is_solo_parent),

                is_high_school:
                    toBool(oldHead.is_high_school)

            };


            members.unshift(
                householdHead
            );

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
                        normalizeResidencyType(
                            member.residency_type
                        ),

                    isHead:
                        false,

                    mode:
                        member.mode ||
                        (
                            member.resident_id
                                ? 'existing'
                                : 'new'
                        ),

                    is_pwd:
                        toBool(member.is_pwd),

                    is_solo_parent:
                        toBool(member.is_solo_parent),

                    is_high_school:
                        toBool(member.is_high_school)

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

                    members.push(
                        restored
                    );

                }

            }
        );

    }


    /* =========================================================
       INITIAL UI STATE
    ========================================================== */

    updateModalHouseholdAddress();

    updateOccupationOtherVisibility();

    renderHead();

    renderMembers();

    updateWorkflow();

});
</script>
@endpush

@endsection