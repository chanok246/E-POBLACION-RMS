@extends('layouts.app')

@section('title', 'File Report / Complaint | e-Poblacion')

@section('content')

<div class="container-fluid py-4">

    {{-- ================================================================
         PAGE HEADER
         ================================================================ --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">

        <div>
            <h2 class="fw-bold mb-1">
                <i class="bi bi-file-earmark-plus me-2"></i>
                File Report / Complaint
            </h2>

            <p class="text-muted mb-0">
                Record a community concern or a person-related complaint.
            </p>
        </div>

        <a href="{{ route('reports-complaints.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Reports & Complaints
        </a>

    </div>


    {{-- ================================================================
         VALIDATION ERRORS
         ================================================================ --}}
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0">
            <div class="fw-semibold mb-2">
                <i class="bi bi-exclamation-triangle me-1"></i>
                Please correct the following:
            </div>

            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- ================================================================
         MAIN FORM
         ================================================================ --}}
    <form method="POST"
          action="{{ route('reports-complaints.store') }}"
          id="reportComplaintForm">

        @csrf

        {{-- ============================================================
             REPORT TYPE
             ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-transparent border-0 pt-4 px-4">
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-ui-checks-grid me-2"></i>
                    Report Type
                </h5>

                <p class="text-muted small mb-0">
                    Select the type of record you are filing.
                </p>
            </div>

            <div class="card-body px-4 pb-4">

                <div class="row g-3">

                    {{-- Community --}}
                    <div class="col-md-6">

                        <input
                            type="radio"
                            class="btn-check"
                            name="report_type"
                            id="reportTypeCommunity"
                            value="community"
                            autocomplete="off"
                            {{ old('report_type', 'community') === 'community' ? 'checked' : '' }}
                        >

                        <label
                            for="reportTypeCommunity"
                            class="report-type-option w-100"
                        >
                            <div class="d-flex align-items-start gap-3">

                                <div class="report-type-icon">
                                    <i class="bi bi-buildings"></i>
                                </div>

                                <div>
                                    <div class="fw-bold">
                                        Community Report
                                    </div>

                                    <div class="small text-muted mt-1">
                                        For concerns affecting the community,
                                        public facilities, roads, drainage,
                                        garbage, flooding, and similar issues.
                                    </div>
                                </div>

                            </div>
                        </label>

                    </div>


                    {{-- Person --}}
                    <div class="col-md-6">

                        <input
                            type="radio"
                            class="btn-check"
                            name="report_type"
                            id="reportTypePerson"
                            value="person"
                            autocomplete="off"
                            {{ old('report_type') === 'person' ? 'checked' : '' }}
                        >

                        <label
                            for="reportTypePerson"
                            class="report-type-option w-100"
                        >
                            <div class="d-flex align-items-start gap-3">

                                <div class="report-type-icon">
                                    <i class="bi bi-person-exclamation"></i>
                                </div>

                                <div>
                                    <div class="fw-bold">
                                        Person Complaint
                                    </div>

                                    <div class="small text-muted mt-1">
                                        For complaints or disputes involving
                                        a complainant and another person.
                                    </div>
                                </div>

                            </div>
                        </label>

                    </div>

                </div>

            </div>
        </div>


        {{-- ============================================================
             COMPLAINANT
             ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-transparent border-0 pt-4 px-4">

                <div class="d-flex align-items-center gap-2">

                    <div class="section-icon">
                        <i class="bi bi-person-raised-hand"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-1">
                            Complainant / Reporting Person
                        </h5>

                        <p class="text-muted small mb-0">
                            Identify the person who submitted the report or complaint.
                        </p>
                    </div>

                </div>

            </div>

            <div class="card-body px-4 pb-4">

                {{-- Participant source --}}
                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Person Source
                        <span class="text-danger">*</span>
                    </label>

                    <div class="row g-2">

                        <div class="col-md-6">

                            <input
                                type="radio"
                                class="btn-check participant-source"
                                name="complainant_source"
                                id="complainantResidentSource"
                                value="resident"
                                autocomplete="off"
                                {{ old('complainant_source', old('complainant_id') ? 'resident' : 'manual') === 'resident' ? 'checked' : '' }}
                            >

                            <label
                                for="complainantResidentSource"
                                class="participant-source-option"
                            >
                                <i class="bi bi-person-check me-2"></i>
                                Poblacion Resident
                            </label>

                        </div>

                        <div class="col-md-6">

                            <input
                                type="radio"
                                class="btn-check participant-source"
                                name="complainant_source"
                                id="complainantManualSource"
                                value="manual"
                                autocomplete="off"
                                {{ old('complainant_source', old('complainant_id') ? 'resident' : 'manual') === 'manual' ? 'checked' : '' }}
                            >

                            <label
                                for="complainantManualSource"
                                class="participant-source-option"
                            >
                                <i class="bi bi-person-vcard me-2"></i>
                                Non-resident / Not Found
                            </label>

                        </div>

                    </div>

                </div>


                {{-- Resident complainant --}}
                <div id="complainantResidentSection">

                    <label for="complainant_id" class="form-label fw-semibold">
                        Select Poblacion Resident
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="complainant_id"
                        id="complainant_id"
                        class="form-select"
                    >
                        <option value="">-- Select Resident --</option>

                        @foreach ($residents as $resident)

                            @php
                                $residentName = trim(
                                    $resident->first_name . ' ' .
                                    ($resident->middle_name ? $resident->middle_name . ' ' : '') .
                                    $resident->last_name
                                );
                            @endphp

                            <option
                                value="{{ $resident->id }}"
                                {{ (string) old('complainant_id') === (string) $resident->id ? 'selected' : '' }}
                            >
                                {{ $residentName }}
                                @if ($resident->address)
                                    — {{ $resident->address }}
                                @endif
                            </option>

                        @endforeach

                    </select>

                    <div class="form-text">
                        Select the person from the official Poblacion resident records.
                    </div>

                </div>


                {{-- Manual complainant --}}
                <div id="complainantManualSection" class="d-none">

                    <div class="alert alert-info border-0 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>

                        Use this option when the complainant is not a registered
                        Poblacion resident or cannot be found in the resident records.
                        The person will be recorded only as a participant in this case.
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label
                                for="complainant_name"
                                class="form-label fw-semibold"
                            >
                                Full Name
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="complainant_name"
                                id="complainant_name"
                                class="form-control"
                                value="{{ old('complainant_name') }}"
                                placeholder="Enter full name"
                            >

                        </div>

                        <div class="col-md-6">

                            <label
                                for="complainant_contact_number"
                                class="form-label fw-semibold"
                            >
                                Contact Number
                            </label>

                            <input
                                type="text"
                                name="complainant_contact_number"
                                id="complainant_contact_number"
                                class="form-control"
                                value="{{ old('complainant_contact_number') }}"
                                placeholder="09XXXXXXXXX"
                            >

                        </div>

                        <div class="col-md-12">

                            <label
                                for="complainant_address"
                                class="form-label fw-semibold"
                            >
                                Complete Address
                            </label>

                            <input
                                type="text"
                                name="complainant_address"
                                id="complainant_address"
                                class="form-control"
                                value="{{ old('complainant_address') }}"
                                placeholder="House/Street or complete address"
                            >

                        </div>

                        <div class="col-md-4">

                            <label
                                for="complainant_barangay"
                                class="form-label fw-semibold"
                            >
                                Barangay
                            </label>

                            <input
                                type="text"
                                name="complainant_barangay"
                                id="complainant_barangay"
                                class="form-control"
                                value="{{ old('complainant_barangay') }}"
                                placeholder="Barangay"
                            >

                        </div>

                        <div class="col-md-4">

                            <label
                                for="complainant_municipality"
                                class="form-label fw-semibold"
                            >
                                Municipality / City
                            </label>

                            <input
                                type="text"
                                name="complainant_municipality"
                                id="complainant_municipality"
                                class="form-control"
                                value="{{ old('complainant_municipality') }}"
                                placeholder="Municipality / City"
                            >

                        </div>

                        <div class="col-md-4">

                            <label
                                for="complainant_province"
                                class="form-label fw-semibold"
                            >
                                Province
                            </label>

                            <input
                                type="text"
                                name="complainant_province"
                                id="complainant_province"
                                class="form-control"
                                value="{{ old('complainant_province') }}"
                                placeholder="Province"
                            >

                        </div>

                    </div>

                </div>

            </div>
        </div>


        {{-- ============================================================
             RESPONDENT
             Only visible for person complaints
             ============================================================ --}}
        <div
            class="card border-0 shadow-sm mb-4"
            id="respondentCard"
        >

            <div class="card-header bg-transparent border-0 pt-4 px-4">

                <div class="d-flex align-items-center gap-2">

                    <div class="section-icon">
                        <i class="bi bi-person-x"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-1">
                            Respondent / Subject
                        </h5>

                        <p class="text-muted small mb-0">
                            Identify the person involved in the complaint.
                        </p>
                    </div>

                </div>

            </div>

            <div class="card-body px-4 pb-4">

                <div class="mb-3">

                    <label class="form-label fw-semibold">
                        Person Source
                        <span class="text-danger">*</span>
                    </label>

                    <div class="row g-2">

                        <div class="col-md-6">

                            <input
                                type="radio"
                                class="btn-check"
                                name="subject_source"
                                id="subjectResidentSource"
                                value="resident"
                                autocomplete="off"
                                {{ old('subject_source', old('subject_resident_id') ? 'resident' : 'manual') === 'resident' ? 'checked' : '' }}
                            >

                            <label
                                for="subjectResidentSource"
                                class="participant-source-option"
                            >
                                <i class="bi bi-person-check me-2"></i>
                                Poblacion Resident
                            </label>

                        </div>

                        <div class="col-md-6">

                            <input
                                type="radio"
                                class="btn-check"
                                name="subject_source"
                                id="subjectManualSource"
                                value="manual"
                                autocomplete="off"
                                {{ old('subject_source', old('subject_resident_id') ? 'resident' : 'manual') === 'manual' ? 'checked' : '' }}
                            >

                            <label
                                for="subjectManualSource"
                                class="participant-source-option"
                            >
                                <i class="bi bi-person-vcard me-2"></i>
                                Non-resident / Not Found
                            </label>

                        </div>

                    </div>

                </div>


                {{-- Resident respondent --}}
                <div id="subjectResidentSection">

                    <label
                        for="subject_resident_id"
                        class="form-label fw-semibold"
                    >
                        Select Poblacion Resident
                        <span class="text-danger">*</span>
                    </label>

                    <select
                        name="subject_resident_id"
                        id="subject_resident_id"
                        class="form-select"
                    >
                        <option value="">-- Select Resident --</option>

                        @foreach ($residents as $resident)

                            @php
                                $residentName = trim(
                                    $resident->first_name . ' ' .
                                    ($resident->middle_name ? $resident->middle_name . ' ' : '') .
                                    $resident->last_name
                                );
                            @endphp

                            <option
                                value="{{ $resident->id }}"
                                {{ (string) old('subject_resident_id') === (string) $resident->id ? 'selected' : '' }}
                            >
                                {{ $residentName }}
                                @if ($resident->address)
                                    — {{ $resident->address }}
                                @endif
                            </option>

                        @endforeach

                    </select>

                    <div class="form-text">
                        Select the respondent from the official Poblacion resident records.
                    </div>

                </div>


                {{-- Manual respondent --}}
                <div id="subjectManualSection" class="d-none">

                    <div class="alert alert-info border-0 small mb-3">
                        <i class="bi bi-info-circle me-1"></i>

                        Use this option when the respondent is not a registered
                        Poblacion resident or cannot be found in the resident records.
                        The respondent will not be added to the Residents module.
                    </div>

                    <div class="row g-3">

                        <div class="col-md-6">

                            <label
                                for="subject_name"
                                class="form-label fw-semibold"
                            >
                                Full Name
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="subject_name"
                                id="subject_name"
                                class="form-control"
                                value="{{ old('subject_name') }}"
                                placeholder="Enter respondent's full name"
                            >

                        </div>

                        <div class="col-md-6">

                            <label
                                for="subject_contact_number"
                                class="form-label fw-semibold"
                            >
                                Contact Number
                            </label>

                            <input
                                type="text"
                                name="subject_contact_number"
                                id="subject_contact_number"
                                class="form-control"
                                value="{{ old('subject_contact_number') }}"
                                placeholder="09XXXXXXXXX"
                            >

                        </div>

                        <div class="col-md-12">

                            <label
                                for="subject_address"
                                class="form-label fw-semibold"
                            >
                                Complete Address
                            </label>

                            <input
                                type="text"
                                name="subject_address"
                                id="subject_address"
                                class="form-control"
                                value="{{ old('subject_address') }}"
                                placeholder="House/Street or complete address"
                            >

                        </div>

                        <div class="col-md-4">

                            <label
                                for="subject_barangay"
                                class="form-label fw-semibold"
                            >
                                Barangay
                            </label>

                            <input
                                type="text"
                                name="subject_barangay"
                                id="subject_barangay"
                                class="form-control"
                                value="{{ old('subject_barangay') }}"
                                placeholder="Barangay"
                            >

                        </div>

                        <div class="col-md-4">

                            <label
                                for="subject_municipality"
                                class="form-label fw-semibold"
                            >
                                Municipality / City
                            </label>

                            <input
                                type="text"
                                name="subject_municipality"
                                id="subject_municipality"
                                class="form-control"
                                value="{{ old('subject_municipality') }}"
                                placeholder="Municipality / City"
                            >

                        </div>

                        <div class="col-md-4">

                            <label
                                for="subject_province"
                                class="form-label fw-semibold"
                            >
                                Province
                            </label>

                            <input
                                type="text"
                                name="subject_province"
                                id="subject_province"
                                class="form-control"
                                value="{{ old('subject_province') }}"
                                placeholder="Province"
                            >

                        </div>

                    </div>

                </div>


                {{-- Relationship --}}
                <div class="mt-4">

                    <label
                        for="relationship_to_complainant"
                        class="form-label fw-semibold"
                    >
                        Relationship to Complainant
                    </label>

                    <input
                        type="text"
                        name="relationship_to_complainant"
                        id="relationship_to_complainant"
                        class="form-control"
                        value="{{ old('relationship_to_complainant') }}"
                        placeholder="e.g. Neighbor, Relative, Former Partner, Acquaintance"
                    >

                </div>


                {{-- Summons --}}
                <div class="border rounded-3 p-3 mt-4">

                    <div class="form-check form-switch">

                        <input
                            class="form-check-input"
                            type="checkbox"
                            role="switch"
                            name="summons_requested"
                            id="summons_requested"
                            value="1"
                            {{ old('summons_requested') ? 'checked' : '' }}
                        >

                        <label
                            class="form-check-label fw-semibold"
                            for="summons_requested"
                        >
                            Request Subpoena / Summons
                        </label>

                    </div>

                    <div class="small text-muted mt-2">
                        Use this only when a person-related complaint requires
                        the respondent or other involved persons to be formally
                        called for barangay proceedings.
                    </div>

                </div>


                {{-- ====================================================
                     WITNESSES FOR SUBPOENA
                     Manually entered by staff.
                     ==================================================== --}}
                <div
                    class="border rounded-3 p-3 mt-3 d-none"
                    id="witnessesSection"
                >

                    <div class="d-flex align-items-start gap-2 mb-3">

                        <div class="text-primary mt-1">
                            <i class="bi bi-people-fill"></i>
                        </div>

                        <div>
                            <div class="fw-semibold">
                                Witness/es for Subpoena
                            </div>

                            <div class="small text-muted">
                                Enter the witness names manually. Witnesses do not
                                need to be registered in the Residents module.
                            </div>
                        </div>

                    </div>

                    <label
                        for="witnesses"
                        class="form-label fw-semibold"
                    >
                        Witness Name/s
                    </label>

                    <textarea
                        name="witnesses"
                        id="witnesses"
                        rows="4"
                        maxlength="5000"
                        class="form-control"
                        placeholder="Enter one witness name per line&#10;&#10;Example:&#10;Juan Dela Cruz&#10;Maria Santos&#10;Pedro Garcia"
                    >{{ old('witnesses') }}</textarea>

                    <div class="form-text">
                        Enter each witness on a separate line. These names will
                        be used for the subpoena / summons document.
                    </div>

                </div>

            </div>
        </div>


        {{-- ============================================================
             HEARING / MEETING SCHEDULE
             Only applicable to person complaints
             ============================================================ --}}
        <div
            class="card border-0 shadow-sm mb-4 d-none"
            id="hearingScheduleCard"
        >

            <div class="card-header bg-transparent border-0 pt-4 px-4">

                <div class="d-flex align-items-center gap-2">

                    <div class="section-icon">
                        <i class="bi bi-calendar2-check"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-1">
                            Hearing / Meeting Schedule
                        </h5>

                        <p class="text-muted small mb-0">
                            Optional schedule for barangay proceedings involving a person complaint.
                        </p>
                    </div>

                </div>

            </div>

            <div class="card-body px-4 pb-4">

                <div class="alert alert-info border-0 small mb-3">
                    <i class="bi bi-info-circle me-1"></i>
                    If the hearing or meeting schedule is already known when filing
                    the complaint, you may record it here. You can still update the
                    schedule later if it needs to be rescheduled.
                </div>

                <div class="row g-3">

                    <div class="col-md-4">
                        <label for="hearing_date" class="form-label fw-semibold">
                            Hearing Date
                        </label>
                        <input type="date" name="hearing_date" id="hearing_date"
                               class="form-control" value="{{ old('hearing_date') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="hearing_time" class="form-label fw-semibold">
                            Hearing Time
                        </label>
                        <input type="time" name="hearing_time" id="hearing_time"
                               class="form-control" value="{{ old('hearing_time') }}">
                    </div>

                    <div class="col-md-4">
                        <label for="hearing_location" class="form-label fw-semibold">
                            Hearing / Meeting Location
                        </label>
                        <input type="text" name="hearing_location" id="hearing_location"
                               class="form-control" value="{{ old('hearing_location') }}"
                               placeholder="e.g. Barangay Hall">
                    </div>

                </div>

            </div>
        </div>


        {{-- ============================================================
             INCIDENT INFORMATION
             ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-transparent border-0 pt-4 px-4">

                <div class="d-flex align-items-center gap-2">

                    <div class="section-icon">
                        <i class="bi bi-calendar-event"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-1">
                            Incident Information
                        </h5>

                        <p class="text-muted small mb-0">
                            Record when and where the incident occurred.
                        </p>
                    </div>

                </div>

            </div>

            <div class="card-body px-4 pb-4">

                <div class="row g-3">

                    {{-- Category --}}
                    <div class="col-md-6">

                        <label
                            for="category"
                            class="form-label fw-semibold"
                        >
                            Category
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="category"
                            id="category"
                            class="form-select"
                            required
                        >

                            <option value="">
                                -- Select Category --
                            </option>

                            <optgroup
                                label="Community Report"
                                id="communityCategoryGroup"
                            >

                                @foreach ($communityCategories as $category)

                                    <option
                                        value="{{ $category }}"
                                        data-report-type="community"
                                        {{ old('category') === $category ? 'selected' : '' }}
                                    >
                                        {{ $category }}
                                    </option>

                                @endforeach

                            </optgroup>

                            <optgroup
                                label="Person Complaint"
                                id="personCategoryGroup"
                            >

                                @foreach ($personCategories as $category)

                                    <option
                                        value="{{ $category }}"
                                        data-report-type="person"
                                        {{ old('category') === $category ? 'selected' : '' }}
                                    >
                                        {{ $category }}
                                    </option>

                                @endforeach

                            </optgroup>

                        </select>

                    </div>


                    {{-- Title --}}
                    <div class="col-md-6">

                        <label
                            for="title"
                            class="form-label fw-semibold"
                        >
                            Title
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="title"
                            id="title"
                            class="form-control"
                            value="{{ old('title') }}"
                            placeholder="Short title of the report or complaint"
                            required
                        >

                    </div>


                    {{-- Date reported --}}
                    <div class="col-md-4">

                        <label
                            for="date_reported"
                            class="form-label fw-semibold"
                        >
                            Date Reported
                        </label>

                        <input
                            type="date"
                            name="date_reported"
                            id="date_reported"
                            class="form-control"
                            value="{{ old('date_reported', now()->format('Y-m-d')) }}"
                        >

                    </div>


                    {{-- Time reported --}}
                    <div class="col-md-4">

                        <label
                            for="time_reported"
                            class="form-label fw-semibold"
                        >
                            Time Reported
                        </label>

                        <input
                            type="time"
                            name="time_reported"
                            id="time_reported"
                            class="form-control"
                            value="{{ old('time_reported', now()->format('H:i')) }}"
                        >

                    </div>


                    {{-- Incident date --}}
                    <div class="col-md-4">

                        <label
                            for="incident_date"
                            class="form-label fw-semibold"
                        >
                            Incident Date
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            name="incident_date"
                            id="incident_date"
                            class="form-control"
                            value="{{ old('incident_date') }}"
                            required
                        >

                    </div>


                    {{-- Incident time --}}
                    <div class="col-md-4">

                        <label
                            for="incident_time"
                            class="form-label fw-semibold"
                        >
                            Incident Time
                        </label>

                        <input
                            type="time"
                            name="incident_time"
                            id="incident_time"
                            class="form-control"
                            value="{{ old('incident_time') }}"
                        >

                    </div>

                </div>

            </div>
        </div>


        {{-- ============================================================
             LOCATION
             ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-transparent border-0 pt-4 px-4">

                <div class="d-flex align-items-center gap-2">

                    <div class="section-icon">
                        <i class="bi bi-geo-alt"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-1">
                            Incident Location
                        </h5>

                        <p class="text-muted small mb-0">
                            Record the barangay location where the incident occurred.
                        </p>
                    </div>

                </div>

            </div>

            <div class="card-body px-4 pb-4">

                <div class="row g-3">

                    {{-- Purok --}}
                    <div class="col-md-4">

                        <label
                            for="purok"
                            class="form-label fw-semibold"
                        >
                            Purok
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="purok"
                            id="purok"
                            class="form-select"
                            required
                        >

                            <option value="">
                                -- Select Purok --
                            </option>

                            @foreach ($puroks as $purok)

                                <option
                                    value="{{ $purok }}"
                                    {{ old('purok') === $purok ? 'selected' : '' }}
                                >
                                    {{ $purok }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Barangay --}}
                    <div class="col-md-4">

                        <label
                            for="barangay"
                            class="form-label fw-semibold"
                        >
                            Barangay
                        </label>

                        <select
                            name="barangay"
                            id="barangay"
                            class="form-select"
                            required
                        >

                            @foreach ($barangays as $barangay)

                                <option
                                    value="{{ $barangay }}"
                                    {{ old('barangay', 'Poblacion') === $barangay ? 'selected' : '' }}
                                >
                                    {{ $barangay }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Municipality --}}
                    <div class="col-md-4">

                        <label
                            for="municipality"
                            class="form-label fw-semibold"
                        >
                            Municipality
                        </label>

                        <select
                            name="municipality"
                            id="municipality"
                            class="form-select"
                            required
                        >

                            @foreach ($municipalities as $municipality)

                                <option
                                    value="{{ $municipality }}"
                                    {{ old('municipality', 'Bani') === $municipality ? 'selected' : '' }}
                                >
                                    {{ $municipality }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Province --}}
                    <div class="col-md-4">

                        <label
                            for="province"
                            class="form-label fw-semibold"
                        >
                            Province
                        </label>

                        <select
                            name="province"
                            id="province"
                            class="form-select"
                            required
                        >

                            @foreach ($provinces as $province)

                                <option
                                    value="{{ $province }}"
                                    {{ old('province', 'Pangasinan') === $province ? 'selected' : '' }}
                                >
                                    {{ $province }}
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Region --}}
                    <div class="col-md-4">

                        <label
                            for="region"
                            class="form-label fw-semibold"
                        >
                            Region
                        </label>

                        <select
                            name="region"
                            id="region"
                            class="form-select"
                            required
                        >

                            @foreach ($regions as $region)

                                <option
                                    value="{{ $region }}"
                                    {{ old('region', 'Region I - Ilocos Region') === $region ? 'selected' : '' }}
                                >
                                    {{ $region }}
                                </option>

                            @endforeach

                        </select>

                    </div>

                </div>

            </div>
        </div>


        {{-- ============================================================
             DETAILS
             ============================================================ --}}
        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header bg-transparent border-0 pt-4 px-4">

                <div class="d-flex align-items-center gap-2">

                    <div class="section-icon">
                        <i class="bi bi-card-text"></i>
                    </div>

                    <div>
                        <h5 class="fw-bold mb-1">
                            Report Details
                        </h5>

                        <p class="text-muted small mb-0">
                            Provide the details necessary for investigation and action.
                        </p>
                    </div>

                </div>

            </div>

            <div class="card-body px-4 pb-4">

                <div class="mb-3">

                    <label
                        for="description"
                        class="form-label fw-semibold"
                    >
                        Description
                        <span class="text-danger">*</span>
                    </label>

                    <textarea
                        name="description"
                        id="description"
                        rows="6"
                        class="form-control"
                        placeholder="Describe the incident, concern, complaint, or circumstances..."
                        required
                    >{{ old('description') }}</textarea>

                </div>


                <div class="row g-3">

                    {{-- Assigned official --}}
                    <div class="col-md-6">

                        <label
                            for="assigned_to"
                            class="form-label fw-semibold"
                        >
                            Assigned To
                        </label>

                        <select
                            name="assigned_to"
                            id="assigned_to"
                            class="form-select"
                        >

                            <option value="">
                                -- Unassigned --
                            </option>

                            @foreach ($officials as $official)

                                <option
                                    value="{{ $official->id }}"
                                    {{ (string) old('assigned_to') === (string) $official->id ? 'selected' : '' }}
                                >
                                    {{ $official->name }}
                                    @if ($official->role)
                                        — {{ ucfirst($official->role) }}
                                    @endif
                                </option>

                            @endforeach

                        </select>

                    </div>


                    {{-- Remarks --}}
                    <div class="col-md-6">

                        <label
                            for="remarks"
                            class="form-label fw-semibold"
                        >
                            Initial Remarks
                        </label>

                        <textarea
                            name="remarks"
                            id="remarks"
                            rows="3"
                            class="form-control"
                            placeholder="Optional notes or initial remarks..."
                        >{{ old('remarks') }}</textarea>

                    </div>

                </div>

            </div>
        </div>


        {{-- ============================================================
             FORM ACTIONS
             ============================================================ --}}
        <div class="d-flex flex-wrap justify-content-end gap-2 mb-5">

            <a
                href="{{ route('reports-complaints.index') }}"
                class="btn btn-outline-secondary px-4"
            >
                <i class="bi bi-x-circle me-1"></i>
                Cancel
            </a>

            <button
                type="submit"
                class="btn btn-primary px-4"
                id="submitButton"
            >
                <i class="bi bi-save me-1"></i>
                File Report / Complaint
            </button>

        </div>

    </form>

</div>


{{-- ================================================================
     PAGE STYLES
     ================================================================ --}}
<style>

    .report-type-option {
        display: block;
        padding: 1.25rem;
        border: 1px solid #dee2e6;
        border-radius: 0.85rem;
        cursor: pointer;
        transition: all 0.2s ease;
        background: var(--bs-body-bg);
    }

    .report-type-option:hover {
        border-color: var(--bs-primary);
        transform: translateY(-1px);
    }

    .btn-check:checked + .report-type-option {
        border-color: var(--bs-primary);
        box-shadow: 0 0 0 2px rgba(var(--bs-primary-rgb), 0.12);
        background: rgba(var(--bs-primary-rgb), 0.04);
    }

    .report-type-icon {
        width: 46px;
        height: 46px;
        min-width: 46px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--bs-primary-rgb), 0.10);
        color: var(--bs-primary);
        font-size: 1.35rem;
    }

    .section-icon {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 0.7rem;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(var(--bs-primary-rgb), 0.10);
        color: var(--bs-primary);
        font-size: 1.15rem;
    }

    .participant-source-option {
        display: block;
        width: 100%;
        padding: 0.8rem 1rem;
        border: 1px solid #dee2e6;
        border-radius: 0.65rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        font-weight: 600;
        background: var(--bs-body-bg);
    }

    .participant-source-option:hover {
        border-color: var(--bs-primary);
    }

    .btn-check:checked + .participant-source-option {
        border-color: var(--bs-primary);
        background: rgba(var(--bs-primary-rgb), 0.07);
        color: var(--bs-primary);
    }

    .card {
        border-radius: 0.9rem;
    }

    .form-control,
    .form-select {
        min-height: 44px;
    }

    textarea.form-control {
        min-height: auto;
    }

</style>


{{-- ================================================================
     PAGE SCRIPT
     ================================================================ --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
     * ---------------------------------------------------------------
     * Elements
     * ---------------------------------------------------------------
     */

    const communityRadio =
        document.getElementById('reportTypeCommunity');

    const personRadio =
        document.getElementById('reportTypePerson');

    const respondentCard =
        document.getElementById('respondentCard');

    const hearingScheduleCard =
        document.getElementById('hearingScheduleCard');

    const hearingDate =
        document.getElementById('hearing_date');

    const hearingTime =
        document.getElementById('hearing_time');

    const hearingLocation =
        document.getElementById('hearing_location');

    const complainantResidentSource =
        document.getElementById('complainantResidentSource');

    const complainantManualSource =
        document.getElementById('complainantManualSource');

    const complainantResidentSection =
        document.getElementById('complainantResidentSection');

    const complainantManualSection =
        document.getElementById('complainantManualSection');

    const subjectResidentSource =
        document.getElementById('subjectResidentSource');

    const subjectManualSource =
        document.getElementById('subjectManualSource');

    const subjectResidentSection =
        document.getElementById('subjectResidentSection');

    const subjectManualSection =
        document.getElementById('subjectManualSection');

    const complainantId =
        document.getElementById('complainant_id');

    const complainantName =
        document.getElementById('complainant_name');

    const subjectResidentId =
        document.getElementById('subject_resident_id');

    const subjectName =
        document.getElementById('subject_name');

    const summonsRequested =
        document.getElementById('summons_requested');

    const witnessesSection =
        document.getElementById('witnessesSection');

    const witnesses =
        document.getElementById('witnesses');

    const category =
        document.getElementById('category');

    const communityCategoryGroup =
        document.getElementById('communityCategoryGroup');

    const personCategoryGroup =
        document.getElementById('personCategoryGroup');


    /*
     * ---------------------------------------------------------------
     * Report Type
     * ---------------------------------------------------------------
     */

    function getReportType() {

        if (personRadio && personRadio.checked) {
            return 'person';
        }

        return 'community';
    }


    function updateHearingScheduleUI() {

        if (!hearingScheduleCard) {
            return;
        }

        if (getReportType() === 'person') {
            hearingScheduleCard.classList.remove('d-none');
        } else {
            hearingScheduleCard.classList.add('d-none');

            if (hearingDate) {
                hearingDate.value = '';
            }

            if (hearingTime) {
                hearingTime.value = '';
            }

            if (hearingLocation) {
                hearingLocation.value = '';
            }
        }
    }


    /*
     * ---------------------------------------------------------------
     * Witness UI
     * ---------------------------------------------------------------
     *
     * Witnesses are applicable only when:
     * 1. The record is a person complaint, and
     * 2. Subpoena / summons has been requested.
     *
     * Witness names are manually entered and are not connected
     * to the Residents module.
     * ---------------------------------------------------------------
     */

    function updateWitnessesUI() {

        if (!witnessesSection) {
            return;
        }

        if (
            getReportType() === 'person'
            && summonsRequested
            && summonsRequested.checked
        ) {

            witnessesSection.classList.remove('d-none');

        } else {

            witnessesSection.classList.add('d-none');

            /*
             * Do not submit witness information for community reports
             * or when subpoena / summons is not requested.
             */
            if (witnesses) {
                witnesses.value = '';
            }
        }
    }


    function updateReportTypeUI() {

        const type = getReportType();

        /*
         * Person complaint:
         * show respondent and person categories.
         */
        if (type === 'person') {

            respondentCard.classList.remove('d-none');

            updateSubjectSourceUI();

            /*
             * Enable person categories.
             */
            Array.from(
                personCategoryGroup.querySelectorAll('option')
            ).forEach(function (option) {
                option.hidden = false;
            });

            /*
             * Hide community category options.
             */
            Array.from(
                communityCategoryGroup.querySelectorAll('option')
            ).forEach(function (option) {
                option.hidden = true;
            });

            /*
             * If current category belongs to community,
             * reset it.
             */
            const selectedOption =
                category.options[category.selectedIndex];

            if (
                selectedOption &&
                selectedOption.dataset.reportType === 'community'
            ) {
                category.value = '';
            }

        } else {

            /*
             * Community report:
             * respondent is not applicable.
             */
            respondentCard.classList.add('d-none');

            /*
             * Disable summons.
             */
            if (summonsRequested) {
                summonsRequested.checked = false;
            }

            /*
             * Show community categories.
             */
            Array.from(
                communityCategoryGroup.querySelectorAll('option')
            ).forEach(function (option) {
                option.hidden = false;
            });

            /*
             * Hide person categories.
             */
            Array.from(
                personCategoryGroup.querySelectorAll('option')
            ).forEach(function (option) {
                option.hidden = true;
            });

            /*
             * If current category belongs to person complaint,
             * reset it.
             */
            const selectedOption =
                category.options[category.selectedIndex];

            if (
                selectedOption &&
                selectedOption.dataset.reportType === 'person'
            ) {
                category.value = '';
            }
        }

        /*
         * Update witness visibility after report type changes.
         */
        updateWitnessesUI();
    }


    /*
     * ---------------------------------------------------------------
     * Complainant Source
     * ---------------------------------------------------------------
     */

    function updateComplainantSourceUI() {

        if (
            complainantResidentSource &&
            complainantResidentSource.checked
        ) {

            complainantResidentSection.classList.remove('d-none');
            complainantManualSection.classList.add('d-none');

            /*
             * Clear manual complainant fields when resident mode
             * is selected.
             */
            if (complainantName) {
                complainantName.value = '';
            }

            document.getElementById(
                'complainant_address'
            ).value = '';

            document.getElementById(
                'complainant_contact_number'
            ).value = '';

            document.getElementById(
                'complainant_barangay'
            ).value = '';

            document.getElementById(
                'complainant_municipality'
            ).value = '';

            document.getElementById(
                'complainant_province'
            ).value = '';

        } else {

            complainantResidentSection.classList.add('d-none');
            complainantManualSection.classList.remove('d-none');

            /*
             * Clear resident selection when manual mode
             * is selected.
             */
            if (complainantId) {
                complainantId.value = '';
            }
        }
    }


    /*
     * ---------------------------------------------------------------
     * Subject / Respondent Source
     * ---------------------------------------------------------------
     */

    function updateSubjectSourceUI() {

        if (
            subjectResidentSource &&
            subjectResidentSource.checked
        ) {

            subjectResidentSection.classList.remove('d-none');
            subjectManualSection.classList.add('d-none');

            /*
             * Clear manual respondent fields.
             */
            if (subjectName) {
                subjectName.value = '';
            }

            document.getElementById(
                'subject_address'
            ).value = '';

            document.getElementById(
                'subject_contact_number'
            ).value = '';

            document.getElementById(
                'subject_barangay'
            ).value = '';

            document.getElementById(
                'subject_municipality'
            ).value = '';

            document.getElementById(
                'subject_province'
            ).value = '';

        } else {

            subjectResidentSection.classList.add('d-none');
            subjectManualSection.classList.remove('d-none');

            /*
             * Clear resident selection.
             */
            if (subjectResidentId) {
                subjectResidentId.value = '';
            }
        }
    }


    /*
     * ---------------------------------------------------------------
     * Report Type Listeners
     * ---------------------------------------------------------------
     */

    if (communityRadio) {
        communityRadio.addEventListener(
            'change',
            updateReportTypeUI
        );

        communityRadio.addEventListener(
            'change',
            updateHearingScheduleUI
        );
    }

    if (personRadio) {
        personRadio.addEventListener(
            'change',
            updateReportTypeUI
        );

        personRadio.addEventListener(
            'change',
            updateHearingScheduleUI
        );
    }


    /*
     * ---------------------------------------------------------------
     * Summons Listener
     * ---------------------------------------------------------------
     */

    if (summonsRequested) {
        summonsRequested.addEventListener(
            'change',
            updateWitnessesUI
        );
    }


    /*
     * ---------------------------------------------------------------
     * Participant Source Listeners
     * ---------------------------------------------------------------
     */

    if (complainantResidentSource) {
        complainantResidentSource.addEventListener(
            'change',
            updateComplainantSourceUI
        );
    }

    if (complainantManualSource) {
        complainantManualSource.addEventListener(
            'change',
            updateComplainantSourceUI
        );
    }

    if (subjectResidentSource) {
        subjectResidentSource.addEventListener(
            'change',
            updateSubjectSourceUI
        );
    }

    if (subjectManualSource) {
        subjectManualSource.addEventListener(
            'change',
            updateSubjectSourceUI
        );
    }


    /*
     * ---------------------------------------------------------------
     * Prevent same resident
     * ---------------------------------------------------------------
     */

    function validateDifferentResidents() {

        if (
            getReportType() !== 'person'
            || !complainantId
            || !subjectResidentId
            || !complainantResidentSource.checked
            || !subjectResidentSource.checked
        ) {
            return true;
        }

        if (
            complainantId.value !== ''
            && subjectResidentId.value !== ''
            && complainantId.value === subjectResidentId.value
        ) {

            alert(
                'The complainant and respondent cannot be the same resident.'
            );

            subjectResidentId.value = '';

            return false;
        }

        return true;
    }


    if (complainantId) {
        complainantId.addEventListener(
            'change',
            validateDifferentResidents
        );
    }

    if (subjectResidentId) {
        subjectResidentId.addEventListener(
            'change',
            validateDifferentResidents
        );
    }


    /*
     * ---------------------------------------------------------------
     * Form Submit
     * ---------------------------------------------------------------
     */

    const form =
        document.getElementById('reportComplaintForm');

    if (form) {

        form.addEventListener('submit', function (event) {

            const type = getReportType();

            /*
             * Validate complainant.
             */
            if (
                complainantResidentSource.checked
                && !complainantId.value
            ) {

                event.preventDefault();

                alert(
                    'Please select the complainant from the Poblacion resident records.'
                );

                complainantId.focus();

                return;
            }

            if (
                complainantManualSource.checked
                && !complainantName.value.trim()
            ) {

                event.preventDefault();

                alert(
                    'Please enter the complainant\'s full name.'
                );

                complainantName.focus();

                return;
            }


            /*
             * Person complaint requires a respondent.
             */
            if (type === 'person') {

                if (
                    subjectResidentSource.checked
                    && !subjectResidentId.value
                ) {

                    event.preventDefault();

                    alert(
                        'Please select the respondent from the Poblacion resident records.'
                    );

                    subjectResidentId.focus();

                    return;
                }

                if (
                    subjectManualSource.checked
                    && !subjectName.value.trim()
                ) {

                    event.preventDefault();

                    alert(
                        'Please enter the respondent\'s full name.'
                    );

                    subjectName.focus();

                    return;
                }

                /*
                 * Prevent same resident.
                 */
                if (!validateDifferentResidents()) {
                    event.preventDefault();
                    return;
                }

            } else {

                /*
                 * Community reports must never submit
                 * respondent information.
                 */
                if (subjectResidentId) {
                    subjectResidentId.value = '';
                }

                if (subjectName) {
                    subjectName.value = '';
                }

                document.getElementById(
                    'subject_address'
                ).value = '';

                document.getElementById(
                    'subject_contact_number'
                ).value = '';

                document.getElementById(
                    'subject_barangay'
                ).value = '';

                document.getElementById(
                    'subject_municipality'
                ).value = '';

                document.getElementById(
                    'subject_province'
                ).value = '';

                /*
                 * Community reports must never submit
                 * witness information.
                 */
                if (witnesses) {
                    witnesses.value = '';
                }
            }

        });
    }


    /*
     * ---------------------------------------------------------------
     * Initial UI state
     * ---------------------------------------------------------------
     */

    updateComplainantSourceUI();
    updateSubjectSourceUI();
    updateReportTypeUI();
    updateHearingScheduleUI();
    updateWitnessesUI();

});
</script>

@endsection