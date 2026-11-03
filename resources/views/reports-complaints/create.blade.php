@extends('layouts.app')

@section('title', 'File Report / Complaint | e-Poblacion')

@section('content')

<div class="container-fluid py-4 rc-create">

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
                        Type a name or address to search, or choose from the list of official Poblacion resident records.
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
                        Type a name or address to search, or choose from the list of official Poblacion resident records.
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

                        <input
                            type="text"
                            name="category"
                            id="category"
                            class="form-control"
                            value="{{ old('category') }}"
                            placeholder="Choose from the list or type your own category"
                            maxlength="100"
                            autocomplete="off"
                            required
                        >

                        <div class="form-text">
                            Pick a suggested category or type your own.
                        </div>

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

                @php
                    /*
                     * Purok list clean-up (display only):
                     *  - a stand-alone "Centro" entry is merged into Purok 1
                     *    and shown as "Purok 1 - Centro"
                     *  - Barangay Poblacion has 7 puroks, so Purok 1 to 7 are
                     *    always listed. Any purok missing from $puroks is added
                     *    using the same value format as the existing ones.
                     *  - options are listed in order: Purok 1, 2, 3 ... 7
                     * The submitted value is still the value that comes from
                     * $puroks (or the generated one for a missing purok).
                     */
                    $totalPuroks = 7;

                    $isCentro = fn ($p) => (bool) preg_match('/^\s*(centro|sentro)\s*$/i', (string) $p);

                    $isPurokOne = fn ($p) => (bool) preg_match('/^\s*(purok\s*)?(1|uno)\s*([-,]?\s*(centro|sentro))?\s*$/i', (string) $p);

                    $hasPurokOne = collect($puroks)->contains(fn ($p) => $isPurokOne($p));

                    $purokOptions = [];

                    foreach ($puroks as $p) {

                        if ($isCentro($p)) {

                            if ($hasPurokOne) {
                                continue;
                            }

                            $purokOptions[] = ['value' => $p, 'label' => 'Purok 1 - Centro', 'order' => 1];

                        } elseif ($isPurokOne($p)) {

                            $purokOptions[] = ['value' => $p, 'label' => 'Purok 1 - Centro', 'order' => 1];

                        } else {

                            preg_match('/\d+/', (string) $p, $purokNumber);

                            $purokOptions[] = [
                                'value' => $p,
                                'label' => $p,
                                'order' => isset($purokNumber[0]) ? (int) $purokNumber[0] : 999,
                            ];
                        }
                    }

                    /*
                     * Make sure Purok 1 to Purok 7 all exist.
                     */
                    $presentNumbers = collect($purokOptions)->pluck('order')->all();

                    $purokPrefix = 'Purok ';
                    $purokSuffix = '';

                    $samplePurok = collect($puroks)->first(
                        fn ($p) => ! $isCentro($p) && preg_match('/\d+/', (string) $p)
                    );

                    if ($samplePurok && preg_match('/^(\D*?)\d+(\D*)$/', trim((string) $samplePurok), $sampleParts)) {
                        $purokPrefix = $sampleParts[1];
                        $purokSuffix = $sampleParts[2];
                    }

                    for ($n = 1; $n <= $totalPuroks; $n++) {

                        if (! in_array($n, $presentNumbers, true)) {

                            $purokOptions[] = [
                                'value' => $purokPrefix . $n . $purokSuffix,
                                'label' => $n === 1 ? 'Purok 1 - Centro' : 'Purok ' . $n,
                                'order' => $n,
                            ];
                        }
                    }

                    $purokOptions = collect($purokOptions)->sortBy('order')->values()->all();
                @endphp

                {{-- Row 1: Purok (editable) --}}
                <div class="row g-3 mb-3">

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

                            @foreach ($purokOptions as $purokOption)

                                <option
                                    value="{{ $purokOption['value'] }}"
                                    {{ old('purok') === $purokOption['value'] ? 'selected' : '' }}
                                >
                                    {{ $purokOption['label'] }}
                                </option>

                            @endforeach

                        </select>

                        <div class="form-text">
                            Choose where in Poblacion the incident happened.
                        </div>

                    </div>

                </div>


                {{-- Row 2: Fixed location (read-only) --}}
                <div class="rc-fixed-group">

                    <div class="rc-fixed-title">
                        <i class="bi bi-lock-fill me-1"></i>
                        Fixed location
                        <span class="rc-fixed-note">
                            Set automatically, this system covers one barangay only.
                        </span>
                    </div>

                    <div class="row g-3">

                        {{-- Barangay (fixed) --}}
                        <div class="col-md-4">

                            <label
                                for="barangay"
                                class="form-label fw-semibold"
                            >
                                Barangay
                            </label>

                            <input
                                type="text"
                                name="barangay"
                                id="barangay"
                                class="form-control rc-fixed"
                                value="Poblacion"
                                readonly
                                tabindex="-1"
                                required
                            >

                        </div>


                        {{-- Municipality (fixed) --}}
                        <div class="col-md-4">

                            <label
                                for="municipality"
                                class="form-label fw-semibold"
                            >
                                Municipality
                            </label>

                            <input
                                type="text"
                                name="municipality"
                                id="municipality"
                                class="form-control rc-fixed"
                                value="Bani"
                                readonly
                                tabindex="-1"
                                required
                            >

                        </div>


                        {{-- Province (fixed) --}}
                        <div class="col-md-4">

                            <label
                                for="province"
                                class="form-label fw-semibold"
                            >
                                Province
                            </label>

                            <input
                                type="text"
                                name="province"
                                id="province"
                                class="form-control rc-fixed"
                                value="Pangasinan"
                                readonly
                                tabindex="-1"
                                required
                            >

                        </div>


                        {{-- Region (fixed) --}}
                        <div class="col-md-12">

                            <label
                                for="region"
                                class="form-label fw-semibold"
                            >
                                Region
                            </label>

                            <input
                                type="text"
                                name="region"
                                id="region"
                                class="form-control rc-fixed"
                                value="Region I - Ilocos Region"
                                readonly
                                tabindex="-1"
                                required
                            >

                        </div>

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


    /* =====================================================================
       THEME-AWARE COLOURS (Light + Dark)
       Scoped to .rc-create so no other page is affected.
       The project switches themes with html[data-theme="dark"]
       (Bootstrap's data-bs-theme="dark" is also supported).
       Every surface sets its OWN text colour, so text always follows the
       background it sits on.
       ===================================================================== */
    .rc-create {
        --rc-text: #212529;
        --rc-muted: #6c757d;
        --rc-surface: #ffffff;
        --rc-border: #dee2e6;
        --rc-accent: var(--bs-primary);
        --rc-accent-soft: rgba(var(--bs-primary-rgb), 0.07);
        --rc-input-bg: #ffffff;
        --rc-input-text: #212529;
        --rc-placeholder: #6c757d;
        --rc-disabled-bg: #e9ecef;
        --rc-required: #dc3545;
        --rc-info-bg: #cff4fc;
        --rc-info-text: #055160;
        --rc-info-border: #9eeaf9;
        --rc-danger-bg: #f8d7da;
        --rc-danger-text: #58151c;
        --rc-danger-border: #f1aeb5;
        color: var(--rc-text);
    }

    html[data-theme="dark"] .rc-create,
    html[data-bs-theme="dark"] .rc-create {
        --rc-text: #eef2f8;
        --rc-muted: #a9b4c6;
        --rc-surface: #1b2230;
        --rc-border: #3a4660;
        --rc-accent: #7aa7f0;
        --rc-accent-soft: rgba(122, 167, 240, 0.16);
        --rc-input-bg: #141a25;
        --rc-input-text: #eef2f8;
        --rc-placeholder: #8c98ac;
        --rc-disabled-bg: #222b3b;
        --rc-required: #ff8f8f;
        --rc-info-bg: #0f3038;
        --rc-info-text: #a8e6f1;
        --rc-info-border: #1d5663;
        --rc-danger-bg: #401a1d;
        --rc-danger-text: #ffc2c2;
        --rc-danger-border: #7a3036;
        color-scheme: dark;
    }

    /* ---- Text ---- */
    .rc-create h2,
    .rc-create h5,
    .rc-create .fw-bold,
    .rc-create .fw-semibold,
    .rc-create .form-label,
    .rc-create .form-check-label {
        color: var(--rc-text);
    }

    .rc-create .text-muted,
    .rc-create .form-text {
        color: var(--rc-muted) !important;
    }

    .rc-create .text-danger,
    .rc-create .text-danger.fw-semibold {
        color: var(--rc-required) !important;
    }

    .rc-create .text-primary {
        color: var(--rc-accent) !important;
    }

    /* ---- Cards ---- */
    .rc-create .card {
        background-color: var(--rc-surface);
        color: var(--rc-text);
        --bs-card-bg: var(--rc-surface);
        --bs-card-color: var(--rc-text);
        --bs-card-border-color: var(--rc-border);
    }

    .rc-create .border {
        border-color: var(--rc-border) !important;
    }

    /* ---- Report type + participant source selection ---- */
    .rc-create .report-type-option,
    .rc-create .participant-source-option {
        background: var(--rc-surface);
        color: var(--rc-text);
        border-color: var(--rc-border);
    }

    .rc-create .report-type-option .text-muted {
        color: var(--rc-muted) !important;
    }

    .rc-create .report-type-option:hover,
    .rc-create .participant-source-option:hover {
        border-color: var(--rc-accent);
    }

    .rc-create .btn-check:checked + .report-type-option {
        border-color: var(--rc-accent);
        background: var(--rc-accent-soft);
        color: var(--rc-text);
        box-shadow: 0 0 0 2px var(--rc-accent-soft);
    }

    .rc-create .btn-check:checked + .participant-source-option {
        border-color: var(--rc-accent);
        background: var(--rc-accent-soft);
        color: var(--rc-accent);
    }

    .rc-create .btn-check:focus-visible + .report-type-option,
    .rc-create .btn-check:focus-visible + .participant-source-option {
        outline: 2px solid var(--rc-accent);
        outline-offset: 2px;
    }

    .rc-create .report-type-icon,
    .rc-create .section-icon {
        background: var(--rc-accent-soft);
        color: var(--rc-accent);
    }

    /* ---- Form fields ---- */
    .rc-create .form-control,
    .rc-create .form-select {
        background-color: var(--rc-input-bg);
        color: var(--rc-input-text);
        border-color: var(--rc-border);
    }

    .rc-create .form-control::placeholder,
    .rc-create textarea.form-control::placeholder {
        color: var(--rc-placeholder);
        opacity: 1;
    }

    .rc-create .form-control:focus,
    .rc-create .form-select:focus {
        background-color: var(--rc-input-bg);
        color: var(--rc-input-text);
        border-color: var(--rc-accent);
        box-shadow: 0 0 0 0.2rem var(--rc-accent-soft);
    }

    .rc-create .form-control:disabled,
    .rc-create .form-control[readonly],
    .rc-create .form-select:disabled {
        background-color: var(--rc-disabled-bg);
        color: var(--rc-muted);
        opacity: 1;
    }

    .rc-create .form-select option,
    .rc-create .form-select optgroup {
        background-color: var(--rc-input-bg);
        color: var(--rc-input-text);
    }

    .rc-create .form-control.is-invalid,
    .rc-create .form-select.is-invalid {
        border-color: var(--rc-required);
    }

    .rc-create .invalid-feedback {
        color: var(--rc-required);
    }

    html[data-theme="dark"] .rc-create .form-select,
    html[data-bs-theme="dark"] .rc-create .form-select {
        --bs-form-select-bg-img: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23c5cedd' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e");
    }

    .rc-create .form-check-input {
        background-color: var(--rc-input-bg);
        border-color: var(--rc-border);
    }

    .rc-create .form-check-input:checked {
        background-color: var(--rc-accent);
        border-color: var(--rc-accent);
    }

    /* ---- Alerts ---- */
    .rc-create .alert-info {
        --bs-alert-bg: var(--rc-info-bg);
        --bs-alert-color: var(--rc-info-text);
        --bs-alert-border-color: var(--rc-info-border);
        background-color: var(--rc-info-bg);
        color: var(--rc-info-text);
    }

    .rc-create .alert-danger {
        --bs-alert-bg: var(--rc-danger-bg);
        --bs-alert-color: var(--rc-danger-text);
        --bs-alert-border-color: var(--rc-danger-border);
        background-color: var(--rc-danger-bg);
        color: var(--rc-danger-text);
    }

    /* ---- Buttons ---- */
    html[data-theme="dark"] .rc-create .btn-outline-secondary,
    html[data-bs-theme="dark"] .rc-create .btn-outline-secondary {
        --bs-btn-color: #c9d2e0;
        --bs-btn-border-color: #55617c;
        --bs-btn-hover-color: #ffffff;
        --bs-btn-hover-bg: #2a3447;
        --bs-btn-hover-border-color: #7a869f;
        --bs-btn-active-color: #ffffff;
        --bs-btn-active-bg: #2a3447;
        --bs-btn-active-border-color: #7a869f;
    }


    /* =====================================================================
       SEARCHABLE RESIDENT PICKER
       Replaces the long native dropdown. The original <select> stays in the
       form (hidden) so submission, old() values and validation still work.
       ===================================================================== */
    .rc-create .rc-combo {
        position: relative;
    }

    .rc-create .rc-combo-input {
        padding-right: 2.4rem;
    }

    .rc-create .rc-combo-clear {
        position: absolute;
        top: 50%;
        right: 0.5rem;
        transform: translateY(-50%);
        width: 1.9rem;
        height: 1.9rem;
        display: none;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 50%;
        background: transparent;
        color: var(--rc-muted);
        cursor: pointer;
    }

    .rc-create .rc-combo-clear:hover {
        background: var(--rc-accent-soft);
        color: var(--rc-text);
    }

    .rc-create .rc-combo.has-value .rc-combo-clear {
        display: flex;
    }

    .rc-create .rc-combo-list {
        position: absolute;
        z-index: 1055;
        top: calc(100% + 4px);
        left: 0;
        right: 0;
        max-height: 280px;
        overflow-y: auto;
        margin: 0;
        padding: 0.25rem;
        list-style: none;
        background: var(--rc-input-bg);
        color: var(--rc-input-text);
        border: 1px solid var(--rc-border);
        border-radius: 0.65rem;
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.22);
    }

    .rc-create .rc-combo-list[hidden] {
        display: none;
    }

    .rc-create .rc-combo-item {
        padding: 0.55rem 0.75rem;
        border-radius: 0.45rem;
        cursor: pointer;
        font-size: 0.92rem;
        color: var(--rc-input-text);
    }

    .rc-create .rc-combo-item small {
        display: block;
        color: var(--rc-muted);
    }

    .rc-create .rc-combo-item.is-active,
    .rc-create .rc-combo-item:hover {
        background: var(--rc-accent-soft);
    }

    .rc-create .rc-combo-item.is-selected {
        font-weight: 600;
        color: var(--rc-accent);
    }

    .rc-create .rc-combo-empty,
    .rc-create .rc-combo-more {
        padding: 0.6rem 0.75rem;
        font-size: 0.85rem;
        color: var(--rc-muted);
    }

    .rc-create .rc-combo-custom {
        margin-top: 0.25rem;
        padding: 0.6rem 0.75rem;
        border-top: 1px dashed var(--rc-border);
        font-size: 0.85rem;
        color: var(--rc-accent);
        cursor: default;
    }

    /* =====================================================================
       FIXED LOCATION FIELDS
       Read-only, but they must NOT show the "not allowed" (block) cursor.
       pointer-events: none removes any special cursor and click/focus
       behaviour, so the fields simply look like plain information.
       ===================================================================== */
    .rc-create .rc-fixed-group {
        padding: 1rem 1rem 1.1rem;
        border: 1px dashed var(--rc-border);
        border-radius: 0.75rem;
        background: var(--rc-accent-soft);
    }

    .rc-create .rc-fixed-title {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
        margin-bottom: 0.85rem;
        font-size: 0.82rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        text-transform: uppercase;
        color: var(--rc-accent);
    }

    .rc-create .rc-fixed-note {
        font-weight: 400;
        text-transform: none;
        letter-spacing: 0;
        color: var(--rc-muted);
    }

    .rc-create .form-control.rc-fixed,
    .rc-create .form-control.rc-fixed[readonly] {
        color: var(--rc-text);
        cursor: default;
        pointer-events: none;
        user-select: none;
        -webkit-user-select: none;
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

    /*
     * Category suggestions per report type. Category is a
     * type-or-choose field: staff may pick a suggestion or type their own.
     */
    const categoryData = @json(['community' => collect($communityCategories)->values()->all(), 'person' => collect($personCategories)->values()->all()]);

    function clearCategoryIfOtherType(otherType) {

        if (!category) {
            return;
        }

        const value = normalizeText(category.value).trim();

        if (!value) {
            return;
        }

        function isPreset(type) {
            return (categoryData[type] || []).some(function (item) {
                return normalizeText(item).trim() === value;
            });
        }

        if (isPreset(otherType) && !isPreset(getReportType())) {

            category.value = '';

            const wrap = category.closest('.rc-combo');

            if (wrap) {
                wrap.classList.remove('has-value');
            }
        }
    }


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
             * If the current category is a preset that belongs to community
             * reports, reset it. A category the user typed is kept.
             */
            clearCategoryIfOtherType('community');

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
             * If the current category is a preset that belongs to person
             * complaints, reset it. A category the user typed is kept.
             */
            clearCategoryIfOtherType('person');
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
             * Category is a type-or-choose field, so make sure it is
             * not empty or only spaces.
             */
            if (category) {

                category.value = category.value.trim();

                if (!category.value) {

                    event.preventDefault();

                    alert('Please choose or type a category.');

                    category.focus();

                    return;
                }
            }

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
     * Searchable resident picker
     * ---------------------------------------------------------------
     *
     * Turns a long resident <select> into a type-to-search field.
     * The original <select> is kept (hidden) and remains the field that
     * is submitted, so the controller, old() values, and the existing
     * validation code keep working. Any code that sets select.value
     * (for example when switching to "Non-resident") also refreshes
     * the visible search box.
     * ---------------------------------------------------------------
     */

    function normalizeText(text) {
        return (text || '')
            .toString()
            .normalize('NFD')
            .replace(/[̀-ͯ]/g, '')
            .toLowerCase();
    }

    function makeSearchableSelect(select, placeholder) {

        if (!select) {
            return;
        }

        const MAX_RESULTS = 100;

        const items = Array.from(select.options)
            .filter(function (option) {
                return option.value !== '';
            })
            .map(function (option) {
                const label =
                    option.textContent.replace(/\s+/g, ' ').trim();

                const parts = label.split(' — ');

                return {
                    value: option.value,
                    label: label,
                    name: parts[0],
                    address: parts.slice(1).join(' — '),
                    search: normalizeText(label)
                };
            });

        const nativeValue = Object.getOwnPropertyDescriptor(
            HTMLSelectElement.prototype,
            'value'
        );

        const wrap = document.createElement('div');
        wrap.className = 'rc-combo';

        const input = document.createElement('input');
        input.type = 'text';
        input.id = select.id + '_search';
        input.className = 'form-control rc-combo-input';
        input.placeholder = placeholder;
        input.autocomplete = 'off';
        input.spellcheck = false;
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');

        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'rc-combo-clear';
        clearButton.setAttribute('aria-label', 'Clear selected resident');
        clearButton.innerHTML = '<i class="bi bi-x-lg"></i>';

        const list = document.createElement('ul');
        list.className = 'rc-combo-list';
        list.setAttribute('role', 'listbox');
        list.hidden = true;

        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(input);
        wrap.appendChild(clearButton);
        wrap.appendChild(list);

        select.style.display = 'none';
        select.tabIndex = -1;

        /* Clicking the <label> should focus the search box. */
        const label = document.querySelector(
            'label[for="' + select.id + '"]'
        );

        if (label) {
            label.setAttribute('for', input.id);
        }

        /* Existing code calls select.focus() on validation errors. */
        select.focus = function () {
            input.focus();
        };

        let activeIndex = -1;
        let visible = [];

        function selectedItem() {
            const current = nativeValue.get.call(select);

            return items.find(function (item) {
                return item.value === current;
            }) || null;
        }

        function syncFromSelect() {
            const item = selectedItem();

            input.value = item ? item.label : '';
            wrap.classList.toggle('has-value', !!item);
        }

        function close() {
            list.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        }

        function setActive(index) {

            const nodes = list.querySelectorAll('.rc-combo-item');

            if (!nodes.length) {
                activeIndex = -1;
                return;
            }

            activeIndex = (index + nodes.length) % nodes.length;

            nodes.forEach(function (node, i) {
                node.classList.toggle('is-active', i === activeIndex);
            });

            nodes[activeIndex].scrollIntoView({ block: 'nearest' });
        }

        function render(query) {

            const tokens = normalizeText(query)
                .split(/\s+/)
                .filter(Boolean);

            const matches = items.filter(function (item) {
                return tokens.every(function (token) {
                    return item.search.indexOf(token) !== -1;
                });
            });

            visible = matches.slice(0, MAX_RESULTS);

            const current = nativeValue.get.call(select);

            list.innerHTML = '';

            if (!visible.length) {
                const empty = document.createElement('li');
                empty.className = 'rc-combo-empty';
                empty.textContent =
                    'No matching resident found. Use "Non-resident / Not Found" if the person is not registered.';
                list.appendChild(empty);
            }

            visible.forEach(function (item, index) {

                const row = document.createElement('li');
                row.className = 'rc-combo-item';
                row.setAttribute('role', 'option');
                row.dataset.index = index;

                if (item.value === current) {
                    row.classList.add('is-selected');
                }

                row.appendChild(document.createTextNode(item.name));

                if (item.address) {
                    const small = document.createElement('small');
                    small.textContent = item.address;
                    row.appendChild(small);
                }

                list.appendChild(row);
            });

            if (matches.length > MAX_RESULTS) {
                const more = document.createElement('li');
                more.className = 'rc-combo-more';
                more.textContent =
                    'Showing the first ' + MAX_RESULTS + ' of ' +
                    matches.length + ' matches. Keep typing to narrow the list.';
                list.appendChild(more);
            }

            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');

            setActive(visible.length ? 0 : -1);
        }

        function choose(item) {

            nativeValue.set.call(select, item.value);

            syncFromSelect();
            close();

            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function clearSelection() {

            nativeValue.set.call(select, '');

            syncFromSelect();

            select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        /*
         * Keep the visible box in sync when other code assigns
         * select.value (e.g. clearing it when the person source changes).
         */
        Object.defineProperty(select, 'value', {
            configurable: true,
            get: function () {
                return nativeValue.get.call(select);
            },
            set: function (newValue) {
                nativeValue.set.call(select, newValue);
                syncFromSelect();
            }
        });

        input.addEventListener('focus', function () {
            input.select();
            render('');
        });

        input.addEventListener('click', function () {
            if (list.hidden) {
                render('');
            }
        });

        input.addEventListener('input', function () {
            render(input.value);
        });

        input.addEventListener('keydown', function (event) {

            if (event.key === 'ArrowDown') {
                event.preventDefault();

                if (list.hidden) {
                    render(input.value);
                } else {
                    setActive(activeIndex + 1);
                }

            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                setActive(activeIndex - 1);

            } else if (event.key === 'Enter') {

                /* Never submit the form from this field. */
                event.preventDefault();

                if (!list.hidden && visible[activeIndex]) {
                    choose(visible[activeIndex]);
                }

            } else if (event.key === 'Escape') {
                close();
                syncFromSelect();

            } else if (event.key === 'Tab') {
                close();
                syncFromSelect();
            }
        });

        input.addEventListener('blur', function () {
            close();
            syncFromSelect();
        });

        /* mousedown (not click) so the input does not lose focus first. */
        list.addEventListener('mousedown', function (event) {

            event.preventDefault();

            const row = event.target.closest('.rc-combo-item');

            if (row && visible[row.dataset.index]) {
                choose(visible[row.dataset.index]);
            }
        });

        clearButton.addEventListener('mousedown', function (event) {
            event.preventDefault();
            clearSelection();
            input.focus();
        });

        syncFromSelect();
    }

    makeSearchableSelect(
        complainantId,
        'Type a name or address to search residents…'
    );

    makeSearchableSelect(
        subjectResidentId,
        'Type a name or address to search residents…'
    );


    /*
     * ---------------------------------------------------------------
     * Category: choose a suggestion OR type your own
     * ---------------------------------------------------------------
     *
     * Suggestions follow the selected report type (community / person).
     * Whatever is in the box is what gets submitted, so staff can type
     * a category that is not on the list.
     * ---------------------------------------------------------------
     */

    function makeCategoryCombo(input) {

        if (!input) {
            return;
        }

        const MAX_RESULTS = 100;

        const wrap = document.createElement('div');
        wrap.className = 'rc-combo';

        input.classList.add('rc-combo-input');
        input.setAttribute('role', 'combobox');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');

        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'rc-combo-clear';
        clearButton.setAttribute('aria-label', 'Clear category');
        clearButton.innerHTML = '<i class="bi bi-x-lg"></i>';

        const list = document.createElement('ul');
        list.className = 'rc-combo-list';
        list.setAttribute('role', 'listbox');
        list.hidden = true;

        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        wrap.appendChild(clearButton);
        wrap.appendChild(list);

        let activeIndex = -1;
        let visible = [];

        function updateClear() {
            wrap.classList.toggle('has-value', input.value.trim() !== '');
        }

        function close() {
            list.hidden = true;
            input.setAttribute('aria-expanded', 'false');
            activeIndex = -1;
        }

        function setActive(index) {

            const nodes = list.querySelectorAll('.rc-combo-item');

            if (!nodes.length) {
                activeIndex = -1;
                return;
            }

            activeIndex = (index + nodes.length) % nodes.length;

            nodes.forEach(function (node, i) {
                node.classList.toggle('is-active', i === activeIndex);
            });

            nodes[activeIndex].scrollIntoView({ block: 'nearest' });
        }

        function render(query) {

            const items = categoryData[getReportType()] || [];

            const tokens = normalizeText(query)
                .split(/\s+/)
                .filter(Boolean);

            const matches = items.filter(function (item) {
                const text = normalizeText(item);

                return tokens.every(function (token) {
                    return text.indexOf(token) !== -1;
                });
            });

            visible = matches.slice(0, MAX_RESULTS);

            const typed = input.value.trim();
            const typedKey = normalizeText(typed).trim();

            const exact = items.some(function (item) {
                return normalizeText(item).trim() === typedKey;
            });

            list.innerHTML = '';

            visible.forEach(function (item, index) {

                const row = document.createElement('li');
                row.className = 'rc-combo-item';
                row.setAttribute('role', 'option');
                row.dataset.index = index;
                row.textContent = item;

                if (normalizeText(item).trim() === typedKey) {
                    row.classList.add('is-selected');
                }

                list.appendChild(row);
            });

            if (typed && !exact) {
                const custom = document.createElement('li');
                custom.className = 'rc-combo-custom';
                custom.textContent =
                    'Press Enter or click away to use “' + typed + '” as your own category';
                list.appendChild(custom);
            }

            if (!list.children.length) {
                close();
                return;
            }

            list.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            activeIndex = -1;
        }

        function choose(item) {
            input.value = item;
            updateClear();
            close();
        }

        input.addEventListener('focus', function () {
            render('');
        });

        input.addEventListener('click', function () {
            if (list.hidden) {
                render('');
            }
        });

        input.addEventListener('input', function () {
            updateClear();
            render(input.value);
        });

        input.addEventListener('keydown', function (event) {

            if (event.key === 'ArrowDown') {
                event.preventDefault();

                if (list.hidden) {
                    render(input.value);
                }

                setActive(activeIndex + 1);

            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                setActive(activeIndex - 1);

            } else if (event.key === 'Enter') {

                /* Never submit the form from this field. */
                event.preventDefault();

                if (!list.hidden && activeIndex >= 0 && visible[activeIndex]) {
                    choose(visible[activeIndex]);
                } else {
                    input.value = input.value.trim();
                    updateClear();
                    close();
                }

            } else if (event.key === 'Escape' || event.key === 'Tab') {
                close();
            }
        });

        input.addEventListener('blur', function () {
            input.value = input.value.trim();
            updateClear();
            close();
        });

        /* mousedown (not click) so the input does not lose focus first. */
        list.addEventListener('mousedown', function (event) {

            event.preventDefault();

            const row = event.target.closest('.rc-combo-item');

            if (row && visible[row.dataset.index]) {
                choose(visible[row.dataset.index]);
                return;
            }

            if (event.target.closest('.rc-combo-custom')) {
                input.value = input.value.trim();
                close();
            }
        });

        clearButton.addEventListener('mousedown', function (event) {
            event.preventDefault();
            input.value = '';
            updateClear();
            input.focus();
            render('');
        });

        updateClear();
    }

    makeCategoryCombo(category);


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