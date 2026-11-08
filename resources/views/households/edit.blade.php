@extends('layouts.app')

@section('title', 'Edit Household | e-Poblacion')

@section('content')

@php
/*
|--------------------------------------------------------------------------
| Role-Based Household Permissions
|--------------------------------------------------------------------------
|
| Captain and Secretary can edit household records.
|
*/

$currentUser = Auth::user();

$isCaptain = $currentUser && $currentUser->role === 'captain';
$isSecretary = $currentUser && $currentUser->role === 'secretary';

$canManageHouseholds = $isCaptain || $isSecretary;

/*
|--------------------------------------------------------------------------
| Household Classification Column Check
|--------------------------------------------------------------------------
|
| The classification checkboxes are only rendered (and submitted) when the
| matching column really exists in the households table. This prevents the
| form from sending fields the database cannot store.
|
*/

$hasLowIncomeColumn = false;
$hasFourPsColumn = false;

try {
    $hasLowIncomeColumn = \Illuminate\Support\Facades\Schema::hasColumn('households', 'is_low_income');
    $hasFourPsColumn = \Illuminate\Support\Facades\Schema::hasColumn('households', 'is_4ps_beneficiary');
} catch (\Throwable $exception) {
    $hasLowIncomeColumn = false;
    $hasFourPsColumn = false;
}

$hasHouseholdClassificationColumns = $hasLowIncomeColumn || $hasFourPsColumn;

/*
 * old() returns strings such as "0" and "1". "0" must NOT be treated as
 * true, therefore the value is converted with FILTER_VALIDATE_BOOLEAN.
 */
$isLowIncomeChecked = filter_var(
    old('is_low_income', $household->is_low_income ?? false),
    FILTER_VALIDATE_BOOLEAN
);

$isFourPsChecked = filter_var(
    old('is_4ps_beneficiary', $household->is_4ps_beneficiary ?? false),
    FILTER_VALIDATE_BOOLEAN
);

/*
|--------------------------------------------------------------------------
| Saved (database) coordinates
|--------------------------------------------------------------------------
|
| These are the coordinates currently stored for the household. They are
| used for display and for the "Restore saved location" button. They are
| never changed unless the user intentionally captures or picks a new
| location and then submits the form.
|
*/

$savedLatitude = $household->latitude;
$savedLongitude = $household->longitude;

$hasSavedCoordinates = is_numeric($savedLatitude)
    && is_numeric($savedLongitude)
    && abs((float) $savedLatitude) <= 90
    && abs((float) $savedLongitude) <= 180;

@endphp

@if($canManageHouseholds)
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
@endif

<style>
    /* ============================================================
       HOUSEHOLD EDIT - THEME VARIABLES
    ============================================================ */

    .household-edit-page {
        --he-surface: #ffffff;
        --he-surface-soft: #f8f9fc;
        --he-text: #252b43;
        --he-heading: #243746;
        --he-muted: #7b8994;
        --he-border: #dce3e8;
        --he-input-bg: #ffffff;
        --he-input-text: #252b43;
        --he-placeholder: #8a98a3;
        --he-header-bg: #f8f9fc;
        --he-readonly-bg: #f4f6f8;
        --he-readonly-text: #43515d;
        --he-shadow: 0 2px 8px rgba(32, 46, 60, 0.06);
    }

    /* ============================================================
       PAGE
    ============================================================ */

    .household-edit-page {
        color: var(--he-text);
    }

    .household-edit-page h1,
    .household-edit-page h2,
    .household-edit-page h3,
    .household-edit-page h4,
    .household-edit-page h5,
    .household-edit-page h6 {
        color: var(--he-heading);
    }

    .household-edit-page .text-muted {
        color: var(--he-muted) !important;
    }

    /* ============================================================
       CARDS
    ============================================================ */

    .household-edit-page .card {
        background-color: var(--he-surface);
        border-color: var(--he-border);
        color: var(--he-text);
        border-radius: 14px !important;
        box-shadow: var(--he-shadow);
    }

    .household-edit-page .card-header {
        background-color: var(--he-header-bg);
        border-bottom-color: var(--he-border);
        color: var(--he-heading);
    }

    .household-edit-page .card-body {
        color: var(--he-text);
    }

    /* ============================================================
       FORM LABELS
    ============================================================ */

    .household-edit-page .form-label {
        color: var(--he-heading);
    }

    /* ============================================================
       FORM CONTROLS
    ============================================================ */

    .household-edit-page .form-control,
    .household-edit-page .form-select {
        min-height: 44px;
        border-radius: 8px;
        background-color: var(--he-input-bg);
        border-color: var(--he-border);
        color: var(--he-input-text);
    }

    .household-edit-page textarea.form-control {
        min-height: 100px;
    }

    .household-edit-page .form-control:focus,
    .household-edit-page .form-select:focus {
        background-color: var(--he-input-bg);
        color: var(--he-input-text);
    }

    .household-edit-page .form-control::placeholder {
        color: var(--he-placeholder);
    }

    .household-edit-page .form-select option {
        background-color: var(--he-input-bg);
        color: var(--he-input-text);
    }

    /* Read-only household number */

    .household-edit-page .form-control.bg-light {
        background-color: var(--he-readonly-bg) !important;
        color: var(--he-readonly-text);
    }

    /* ============================================================
       GPS SECTION
    ============================================================ */

    .household-edit-page .gps-section {
        margin-top: 1rem;
        padding: 1rem;
        border: 1px solid var(--he-border);
        border-radius: 10px;
        background-color: var(--he-surface-soft);
    }

    .household-edit-page .gps-section-title {
        color: var(--he-heading);
        font-weight: 700;
    }

    .household-edit-page .gps-coordinate-note {
        font-size: 0.82rem;
        color: var(--he-muted);
        margin-top: 0.35rem;
    }

    .household-edit-page .gps-input {
        font-family: Consolas, "Courier New", monospace;
    }

    .household-edit-page .gps-saved-box {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem 1.25rem;
        padding: 0.6rem 0.85rem;
        border: 1px dashed var(--he-border);
        border-radius: 8px;
        background-color: var(--he-surface);
        font-size: 0.85rem;
    }

    .household-edit-page .gps-saved-box .gps-saved-label {
        color: var(--he-muted);
        font-weight: 600;
    }

    .household-edit-page .gps-saved-box .gps-saved-value {
        font-family: Consolas, "Courier New", monospace;
        color: var(--he-heading);
        font-weight: 700;
    }

    .household-edit-page .gps-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .household-edit-page .gps-accuracy-line {
        font-size: 0.85rem;
        color: var(--he-muted);
    }

    .household-edit-page .gps-accuracy-line strong {
        color: var(--he-heading);
    }

    .household-edit-page .gps-feedback {
        margin-bottom: 0;
        font-size: 0.9rem;
    }

    .household-edit-page .gps-changed-note {
        font-size: 0.85rem;
        color: #b45309;
        font-weight: 600;
    }

    html[data-theme="dark"] .household-edit-page .gps-changed-note {
        color: #fbbf24;
    }

    /* Embedded map picker.
       isolation + z-index 0 keeps Leaflet's internal z-indexes inside the
       map box so the map never covers the navbar, sidebar or dropdowns. */

    .household-edit-page .gps-map-panel {
        margin-top: 0.25rem;
    }

    .household-edit-page .gps-map-wrap {
        position: relative;
        z-index: 0;
        isolation: isolate;
        border: 1px solid var(--he-border);
        border-radius: 10px;
        overflow: hidden;
    }

    .household-edit-page #householdPickerMap {
        width: 100%;
        height: 380px;
        background: #dfe5ea;
    }

    .household-edit-page #householdPickerMap.is-picking,
    .household-edit-page #householdPickerMap.is-picking.leaflet-grab {
        cursor: crosshair;
    }

    .household-edit-page .gps-map-hint {
        font-size: 0.82rem;
        color: var(--he-muted);
    }

    .household-edit-page .gps-pin-wrap,
    .household-edit-page .gps-dot-wrap {
        background: none;
        border: 0;
    }

    .household-edit-page .gps-pin {
        position: relative;
        display: block;
        width: 28px;
        height: 28px;
        background: #0d6efd;
        border: 3px solid #fff;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        box-shadow: 0 2px 9px rgba(0, 0, 0, 0.55), 0 0 0 2px #0d6efd;
    }

    .household-edit-page .gps-pin::after {
        content: "";
        position: absolute;
        width: 8px;
        height: 8px;
        left: 7px;
        top: 7px;
        border-radius: 50%;
        background: #fff;
    }

    .household-edit-page .gps-dot {
        display: block;
        width: 18px;
        height: 18px;
        border: 3px solid #fff;
        border-radius: 50%;
        background: #198754;
        box-shadow: 0 0 0 2px #198754, 0 2px 8px rgba(0, 0, 0, 0.45);
    }

    /* ============================================================
       HOUSEHOLD CLASSIFICATION
    ============================================================ */

    .household-edit-page .classification-option {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        height: 100%;
        padding: 1rem;
        border: 1px solid var(--he-border);
        border-radius: 10px;
        background-color: var(--he-surface-soft);
        cursor: pointer;
    }

    .household-edit-page .classification-option .form-check-input {
        flex-shrink: 0;
        width: 1.2rem;
        height: 1.2rem;
        margin-top: 0.15rem;
        cursor: pointer;
    }

    .household-edit-page .classification-option-title {
        color: var(--he-heading);
        font-weight: 700;
        line-height: 1.3;
    }

    .household-edit-page .classification-option-note {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.82rem;
        color: var(--he-muted);
    }

    /* ============================================================
       ALERTS
    ============================================================ */

    .household-edit-page .alert {
        border-width: 1px;
    }

    /* ============================================================
       BUTTONS
    ============================================================ */

    .household-edit-page .btn-light {
        background-color: var(--he-surface);
        border-color: var(--he-border);
        color: var(--he-heading);
    }

    .household-edit-page .btn-light:hover {
        background-color: var(--he-surface-soft);
        border-color: var(--he-border);
        color: var(--he-heading);
    }

    /* ============================================================
       DARK MODE
    ============================================================ */

    html[data-theme="dark"] .household-edit-page {
        --he-surface: #1b1d2a;
        --he-surface-soft: #20232f;
        --he-text: #f1f3f8;
        --he-heading: #f1f3f8;
        --he-muted: #aeb4c5;
        --he-border: rgba(255, 255, 255, 0.10);
        --he-input-bg: #20232f;
        --he-input-text: #f1f3f8;
        --he-placeholder: #858b9d;
        --he-header-bg: #20232f;
        --he-readonly-bg: #292d3a;
        --he-readonly-text: #e3e6ed;
        --he-shadow: 0 2px 10px rgba(0, 0, 0, 0.18);
    }

    html[data-theme="dark"] .household-edit-page .form-control,
    html[data-theme="dark"] .household-edit-page .form-select {
        color-scheme: dark;
    }

    html[data-theme="dark"] .household-edit-page .form-control.bg-light {
        background-color: var(--he-readonly-bg) !important;
        color: var(--he-readonly-text) !important;
        border-color: var(--he-border);
    }

    html[data-theme="dark"] .household-edit-page .btn-light {
        background-color: #292d3a;
        border-color: rgba(255, 255, 255, 0.12);
        color: #f1f3f8;
    }

    html[data-theme="dark"] .household-edit-page .btn-light:hover {
        background-color: #323645;
        border-color: rgba(255, 255, 255, 0.16);
        color: #ffffff;
    }

    /* ============================================================
       MOBILE
    ============================================================ */

    @media (max-width: 768px) {
        .household-edit-page .household-edit-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
        }

        .household-edit-page .household-edit-header .btn {
            width: 100%;
        }

        .household-edit-page .edit-form-actions {
            flex-direction: column;
        }

        .household-edit-page .edit-form-actions .btn {
            width: 100%;
        }

        .household-edit-page .gps-actions .btn {
            width: 100%;
        }

        .household-edit-page #householdPickerMap {
            height: 320px;
        }
    }

    /* ============================================================
       PRINT
    ============================================================ */

    @media print {
        .household-edit-page .household-edit-header,
        .household-edit-page .edit-form-actions,
        .household-edit-page .alert,
        .household-edit-page .gps-actions,
        .household-edit-page .gps-map-panel {
            display: none !important;
        }

        .household-edit-page .card {
            box-shadow: none !important;
            break-inside: avoid;
        }
    }
</style>

<div class="container-fluid household-edit-page">

{{-- ============================================================
    HEADER
============================================================= --}}

<div class="d-flex justify-content-between align-items-center mb-4 household-edit-header">

    <div>

        <div class="text-muted small mb-1">
            HOUSEHOLDS / EDIT
        </div>

        <h2 class="fw-bold mb-1">
            Edit Household
        </h2>

        <p class="text-muted mb-0">
            Update the household information, household head, and household classifications.
        </p>

    </div>


    <a
        href="{{ route('households.show', $household->id) }}"
        class="btn btn-light border"
    >

        <i class="bi bi-arrow-left me-1"></i>

        Back to Household

    </a>

</div>


{{-- ============================================================
    ACCESS PROTECTION
============================================================= --}}

@if(!$canManageHouseholds)

    <div class="alert alert-danger">

        <i class="bi bi-shield-lock me-2"></i>

        <strong>Access Restricted</strong>

        <div class="mt-1">

            Your account has view-only access to household records.
            You are not authorized to edit household information.

        </div>

        <div class="mt-3">

            <a
                href="{{ route('households.show', $household->id) }}"
                class="btn btn-secondary"
            >

                <i class="bi bi-arrow-left me-1"></i>

                Return to Household

            </a>

        </div>

    </div>

@else


    {{-- ============================================================
        VALIDATION ERRORS
    ============================================================= --}}

    @if ($errors->any())

        <div class="alert alert-danger">

            <strong>Please check the following:</strong>

            <ul class="mb-0 mt-2">

                @foreach ($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif



    {{-- ============================================================
        ARCHIVED NOTICE
    ============================================================= --}}

    @if($household->status === 'Archived')

        <div class="alert alert-warning">

            <i class="bi bi-archive me-2"></i>

            <strong>This household is archived.</strong>

            Historical information is retained in the system.

        </div>

    @endif


    {{-- ============================================================
        EDIT HOUSEHOLD FORM
    ============================================================= --}}

    <form
        action="{{ route('households.update', $household->id) }}"
        method="POST"
        id="householdEditForm"
    >

        @csrf

        @method('PUT')


        {{-- ========================================================
            HOUSEHOLD INFORMATION
        ========================================================= --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header py-3">

                <h5 class="mb-0 fw-bold">

                    <i class="bi bi-house-door me-2"></i>

                    Household Information

                </h5>

            </div>


            <div class="card-body">

                <div class="row g-4">


                    {{-- HOUSEHOLD NUMBER --}}

                    <div class="col-md-6">

                        <label class="form-label fw-semibold">

                            Household Number

                        </label>

                        <input
                            type="text"
                            class="form-control bg-light"
                            value="{{ $household->household_number }}"
                            readonly
                        >

                        <small class="text-muted">

                            Automatically generated by the system.

                        </small>

                    </div>


                    {{-- STATUS --}}

                    <div class="col-md-6">

                        <label
                            for="status"
                            class="form-label fw-semibold"
                        >

                            Status

                        </label>

                        <select
                            name="status"
                            id="status"
                            class="form-select"
                        >

                            <option
                                value="Active"
                                {{ old(
                                    'status',
                                    $household->status
                                ) === 'Active'
                                    ? 'selected'
                                    : ''
                                }}
                            >

                                Active

                            </option>


                            <option
                                value="Archived"
                                {{ old(
                                    'status',
                                    $household->status
                                ) === 'Archived'
                                    ? 'selected'
                                    : ''
                                }}
                            >

                                Archived

                            </option>

                        </select>

                        <small class="text-muted">

                            Use Archived when the household is no longer
                            active or has migrated.

                        </small>

                    </div>


                    {{-- CURRENT ADDRESS --}}

                    <div class="col-md-8">

                        <label
                            for="address"
                            class="form-label fw-semibold"
                        >

                            Current Address

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <textarea
                            name="address"
                            id="address"
                            rows="3"
                            class="form-control"
                            required
                        >{{ old(
                            'address',
                            $household->address
                        ) }}</textarea>


                        <small class="text-muted">

                            Current residential address of the household.

                        </small>

                    </div>


                    {{-- PUROK --}}

                    <div class="col-md-4">

                        <label
                            for="purok"
                            class="form-label fw-semibold"
                        >

                            Purok

                        </label>


                        <select
                            name="purok"
                            id="purok"
                            class="form-select"
                        >

                            <option value="">
                                Select Purok
                            </option>


                            @for ($i = 1; $i <= 7; $i++)

                                <option
                                    value="Purok {{ $i }}"
                                    {{
                                        old(
                                            'purok',
                                            $household->purok
                                        )
                                        ===
                                        'Purok '.$i
                                            ? 'selected'
                                            : ''
                                    }}
                                >

                                    Purok {{ $i }}

                                </option>

                            @endfor

                        </select>

                    </div>


                    {{-- ====================================================
                        HOUSEHOLD LOCATION (GPS + MAP PICKER)
                    ===================================================== --}}

                    <div class="col-12">

                        <div
                            class="gps-section"
                            id="gpsSection"
                            data-saved-latitude="{{ $hasSavedCoordinates ? $savedLatitude : '' }}"
                            data-saved-longitude="{{ $hasSavedCoordinates ? $savedLongitude : '' }}"
                        >

                            <div class="d-flex align-items-center mb-2">

                                <i class="bi bi-geo-alt-fill me-2 text-primary"></i>

                                <div class="gps-section-title">
                                    Household Location
                                </div>

                            </div>

                            <div class="gps-coordinate-note mb-3">

                                Capture your current position or pick the
                                household on the map. The coordinates below
                                are only saved when you click the save button
                                at the bottom of this page.

                            </div>


                            {{-- CURRENTLY SAVED COORDINATES --}}

                            <div class="gps-saved-box mb-3" id="savedCoordinatesBox">

                                <span>
                                    <span class="gps-saved-label">Currently saved Latitude:</span>
                                    <span class="gps-saved-value">{{ $hasSavedCoordinates ? $savedLatitude : 'Not set' }}</span>
                                </span>

                                <span>
                                    <span class="gps-saved-label">Currently saved Longitude:</span>
                                    <span class="gps-saved-value">{{ $hasSavedCoordinates ? $savedLongitude : 'Not set' }}</span>
                                </span>

                            </div>


                            <div class="row g-3">

                                {{-- LATITUDE --}}

                                <div class="col-md-6">

                                    <label
                                        for="latitude"
                                        class="form-label fw-semibold"
                                    >

                                        Latitude

                                    </label>

                                    <input
                                        type="number"
                                        name="latitude"
                                        id="latitude"
                                        class="form-control gps-input"
                                        value="{{ old(
                                            'latitude',
                                            $household->latitude
                                        ) }}"
                                        step="any"
                                        min="-90"
                                        max="90"
                                        placeholder="Example: 16.187656"
                                    >

                                    <small class="text-muted">

                                        Range: -90 to 90

                                    </small>

                                </div>


                                {{-- LONGITUDE --}}

                                <div class="col-md-6">

                                    <label
                                        for="longitude"
                                        class="form-label fw-semibold"
                                    >

                                        Longitude

                                    </label>

                                    <input
                                        type="number"
                                        name="longitude"
                                        id="longitude"
                                        class="form-control gps-input"
                                        value="{{ old(
                                            'longitude',
                                            $household->longitude
                                        ) }}"
                                        step="any"
                                        min="-180"
                                        max="180"
                                        placeholder="Example: 119.847793"
                                    >

                                    <small class="text-muted">

                                        Range: -180 to 180

                                    </small>

                                </div>

                            </div>


                            {{-- ACTION BUTTONS --}}

                            <div class="gps-actions mt-3">

                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    id="locateCurrentPositionBtn"
                                >

                                    <i class="bi bi-crosshair me-1"></i>

                                    <span id="locateCurrentPositionLabel">Locate Current Position</span>

                                </button>

                                <button
                                    type="button"
                                    class="btn btn-outline-primary"
                                    id="togglePickerMapBtn"
                                    aria-expanded="false"
                                    aria-controls="pickerMapPanel"
                                >

                                    <i class="bi bi-map me-1"></i>

                                    <span id="togglePickerMapLabel">Pick Location on Map</span>

                                </button>

                                @if($hasSavedCoordinates)

                                    <button
                                        type="button"
                                        class="btn btn-light border"
                                        id="restoreSavedLocationBtn"
                                    >

                                        <i class="bi bi-arrow-counterclockwise me-1"></i>

                                        Restore Saved Location

                                    </button>

                                @endif

                            </div>


                            {{-- GPS ACCURACY + FEEDBACK --}}

                            <div class="gps-accuracy-line mt-3" id="gpsAccuracyLine" hidden>

                                GPS accuracy:

                                <strong id="gpsAccuracyValue">—</strong>

                            </div>

                            <div
                                class="alert gps-feedback mt-2"
                                id="gpsFeedback"
                                role="status"
                                aria-live="polite"
                                hidden
                            ></div>

                            <div class="gps-changed-note mt-2" id="gpsChangedNote" hidden>

                                <i class="bi bi-exclamation-circle me-1"></i>

                                The coordinates were changed but are not saved yet.
                                Verify them, then click the save button below.

                            </div>


                            {{-- EMBEDDED MAP PICKER --}}

                            <div class="gps-map-panel" id="pickerMapPanel" hidden>

                                <div class="gps-map-hint mt-3 mb-2">

                                    <i class="bi bi-cursor-fill me-1"></i>

                                    Click the map to place the pin, or drag the pin
                                    to fine-tune the household location. The Latitude
                                    and Longitude fields update automatically.

                                </div>

                                <div class="gps-map-wrap">

                                    <div id="householdPickerMap" aria-label="Household location picker map"></div>

                                </div>

                            </div>


                            <div class="gps-coordinate-note mt-3">

                                Browser location requires HTTPS or localhost, and a
                                laptop's location is usually less accurate than
                                dedicated GPS hardware. Always verify the pin against
                                the actual house.

                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>


        {{-- ========================================================
            HOUSEHOLD CLASSIFICATION
        ========================================================= --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header py-3">

                <h5 class="mb-0 fw-bold">

                    <i class="bi bi-tags me-2"></i>

                    Household Classification

                </h5>

            </div>


            <div class="card-body">

                @if($hasHouseholdClassificationColumns)

                    <p class="text-muted mb-3">

                        These classifications apply to the whole household.
                        They are used only to filter potential relief
                        beneficiaries. Staff still verify eligibility
                        manually before assistance is finalized.

                    </p>


                    <div class="row g-3">

                        {{-- LOW-INCOME / INDIGENT --}}

                        @if($hasLowIncomeColumn)

                            <div class="col-md-6">

                                {{-- Hidden 0 makes sure an unchecked box is saved as false. --}}
                                <input
                                    type="hidden"
                                    name="is_low_income"
                                    value="0"
                                >

                                <label
                                    for="is_low_income"
                                    class="classification-option"
                                >

                                    <input
                                        type="checkbox"
                                        name="is_low_income"
                                        id="is_low_income"
                                        value="1"
                                        class="form-check-input"
                                        {{ $isLowIncomeChecked ? 'checked' : '' }}
                                    >

                                    <span>

                                        <span class="classification-option-title">
                                            Low-Income / Indigent
                                        </span>

                                        <span class="classification-option-note">
                                            The household is considered low-income or indigent.
                                        </span>

                                    </span>

                                </label>

                            </div>

                        @endif


                        {{-- 4Ps BENEFICIARY --}}

                        @if($hasFourPsColumn)

                            <div class="col-md-6">

                                <input
                                    type="hidden"
                                    name="is_4ps_beneficiary"
                                    value="0"
                                >

                                <label
                                    for="is_4ps_beneficiary"
                                    class="classification-option"
                                >

                                    <input
                                        type="checkbox"
                                        name="is_4ps_beneficiary"
                                        id="is_4ps_beneficiary"
                                        value="1"
                                        class="form-check-input"
                                        {{ $isFourPsChecked ? 'checked' : '' }}
                                    >

                                    <span>

                                        <span class="classification-option-title">
                                            4Ps Beneficiary
                                        </span>

                                        <span class="classification-option-note">
                                            The household is a Pantawid Pamilyang Pilipino Program beneficiary.
                                        </span>

                                    </span>

                                </label>

                            </div>

                        @endif

                    </div>


                    @if(!($hasLowIncomeColumn && $hasFourPsColumn))

                        <div class="alert alert-warning mt-3 mb-0">

                            <i class="bi bi-exclamation-triangle me-2"></i>

                            Not all household classification columns exist in
                            the database yet, so only the available option is
                            shown.

                        </div>

                    @endif

                @else

                    <div class="alert alert-warning mb-0">

                        <i class="bi bi-exclamation-triangle me-2"></i>

                        <strong>Household classification is not available yet.</strong>

                        <div class="mt-1">

                            The database does not have the household
                            classification columns (is_low_income and
                            is_4ps_beneficiary). Ask the system administrator
                            to apply the required schema update first.

                        </div>

                    </div>

                @endif


                <div class="mt-3">

                    <small class="text-muted">

                        Individual classifications (PWD, Senior Citizen,
                        Solo Parent, Student) belong to each household member
                        and are not edited on this page. The household head
                        is identified by the Household Head selection below.

                    </small>

                </div>

            </div>

        </div>


        {{-- ========================================================
            HOUSEHOLD HEAD
        ========================================================= --}}

        <div class="card border-0 shadow-sm mb-4">

            <div class="card-header py-3">

                <h5 class="mb-0 fw-bold">

                    <i class="bi bi-person-badge me-2"></i>

                    Household Head

                </h5>

            </div>


            <div class="card-body">

                <label
                    for="household_head_id"
                    class="form-label fw-semibold"
                >

                    Select Household Head

                    <span class="text-danger">
                        *
                    </span>

                </label>


                <select
                    name="household_head_id"
                    id="household_head_id"
                    class="form-select"
                    required
                >

                    <option value="">
                        Select resident
                    </option>


                    @foreach ($residents as $resident)

                        <option
                            value="{{ $resident->id }}"
                            {{
                                old(
                                    'household_head_id',
                                    $household->household_head_id
                                )
                                ==
                                $resident->id
                                    ? 'selected'
                                    : ''
                            }}
                        >

                            {{ $resident->full_name }}

                            — Age:
                            {{ $resident->age ?? 'N/A' }}

                            @if($resident->address)

                                — {{ $resident->address }}

                            @endif

                        </option>

                    @endforeach

                </select>


                <div class="mt-2">

                    <small class="text-muted">

                        Only active residents are available for selection.

                    </small>

                </div>

            </div>

        </div>


        {{-- ========================================================
            BUTTONS
        ========================================================= --}}

        <div class="d-flex justify-content-end gap-2 edit-form-actions">

            <a
                href="{{ route(
                    'households.show',
                    $household->id
                ) }}"
                class="btn btn-light border"
            >

                Cancel

            </a>


            <button
                type="submit"
                class="btn btn-primary px-4"
            >

                <i class="bi bi-check2-circle me-1"></i>

            Edit Details
            </button>

        </div>

    </form>

@endif

</div>

@if($canManageHouseholds)

<script src="https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    const $ = id => document.getElementById(id);

    const latInput = $('latitude');
    const lngInput = $('longitude');
    const section = $('gpsSection');

    if (!latInput || !lngInput || !section) return;

    /* ------------------------------------------------------------
       Constants and saved (database) coordinates
    ------------------------------------------------------------ */

    const DEFAULT_CENTER = [16.187656, 119.847793];
    const DEFAULT_ZOOM = 15;
    const LOCATED_ZOOM = 19;

    const parseNumber = value => {
        if (value === null || value === undefined || String(value).trim() === '') return null;
        const n = Number(value);
        return Number.isFinite(n) ? n : null;
    };

    const validCoords = (lat, lng) =>
        lat !== null && lng !== null &&
        Math.abs(lat) <= 90 && Math.abs(lng) <= 180;

    const savedLat = parseNumber(section.dataset.savedLatitude);
    const savedLng = parseNumber(section.dataset.savedLongitude);
    const hasSaved = validCoords(savedLat, savedLng);

    /* The value the fields had when the page opened (may include old() input). */
    const initialLat = latInput.value;
    const initialLng = lngInput.value;

    const readInputs = () => {
        const lat = parseNumber(latInput.value);
        const lng = parseNumber(lngInput.value);
        return validCoords(lat, lng) ? { lat, lng } : null;
    };

    /* ------------------------------------------------------------
       Feedback helpers
    ------------------------------------------------------------ */

    const feedback = $('gpsFeedback');
    const accuracyLine = $('gpsAccuracyLine');
    const accuracyValue = $('gpsAccuracyValue');
    const changedNote = $('gpsChangedNote');

    function showFeedback(type, message) {
        feedback.className = 'alert gps-feedback mt-2 alert-' + type;
        feedback.textContent = message;
        feedback.hidden = false;
    }

    function hideFeedback() {
        feedback.hidden = true;
        feedback.textContent = '';
    }

    function showAccuracy(meters) {
        if (meters === null) {
            accuracyLine.hidden = true;
            accuracyValue.textContent = '—';
            return;
        }

        accuracyValue.textContent = Math.round(meters) + ' meters';
        accuracyLine.hidden = false;
    }

    function refreshChangedNote() {
        const savedLatText = hasSaved ? String(savedLat) : '';
        const savedLngText = hasSaved ? String(savedLng) : '';

        const changed =
            String(latInput.value).trim() !== savedLatText ||
            String(lngInput.value).trim() !== savedLngText;

        changedNote.hidden = !changed;
    }

    /* ------------------------------------------------------------
       Map picker (Leaflet, created the first time it is opened)
    ------------------------------------------------------------ */

    const panel = $('pickerMapPanel');
    const toggleBtn = $('togglePickerMapBtn');
    const toggleLabel = $('togglePickerMapLabel');

    const leafletReady = typeof L !== 'undefined';

    let map = null;
    let pickMarker = null;
    let accuracyCircle = null;
    let gpsDot = null;

    function pinIcon() {
        return L.divIcon({
            className: 'gps-pin-wrap',
            html: '<span class="gps-pin"></span>',
            iconSize: [28, 28],
            iconAnchor: [14, 28]
        });
    }

    function ensureMap() {
        if (map || !leafletReady) return;

        const current = readInputs();

        map = L.map('householdPickerMap', {
            center: current ? [current.lat, current.lng] : DEFAULT_CENTER,
            zoom: current ? 18 : DEFAULT_ZOOM,
            maxZoom: 20,
            zoomControl: true
        });

        const street = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 20,
            maxNativeZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        });

        const satellite = L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            {
                maxZoom: 20,
                maxNativeZoom: 18,
                attribution: 'Tiles &copy; Esri &mdash; Maxar, Earthstar Geographics'
            }
        );

        satellite.addTo(map);

        L.control.layers({ 'Satellite': satellite, 'Street': street }, null, { position: 'topright' }).addTo(map);
        L.control.scale({ imperial: false, position: 'bottomleft' }).addTo(map);

        $('householdPickerMap').classList.add('is-picking');

        map.on('click', function (e) {
            applyCoords(e.latlng.lat, e.latlng.lng, 'map');
        });

        if (current) placeMarker(current.lat, current.lng);
    }

    function placeMarker(lat, lng) {
        if (!map) return;

        if (!pickMarker) {
            pickMarker = L.marker([lat, lng], {
                draggable: true,
                keyboard: true,
                icon: pinIcon(),
                zIndexOffset: 1000
            }).addTo(map);

            pickMarker.on('drag', function (e) {
                const p = e.target.getLatLng();
                writeInputs(p.lat, p.lng);
            });

            pickMarker.on('dragend', function (e) {
                const p = e.target.getLatLng();
                writeInputs(p.lat, p.lng);
                showAccuracy(null);
                clearGpsOverlay();
                showFeedback('info', 'Pin moved. The Latitude and Longitude fields were updated. Verify them before saving.');
            });
        } else {
            pickMarker.setLatLng([lat, lng]);
        }
    }

    function removeMarker() {
        if (pickMarker && map) {
            map.removeLayer(pickMarker);
        }
        pickMarker = null;
    }

    function clearGpsOverlay() {
        if (!map) return;

        if (accuracyCircle) {
            map.removeLayer(accuracyCircle);
            accuracyCircle = null;
        }

        if (gpsDot) {
            map.removeLayer(gpsDot);
            gpsDot = null;
        }
    }

    function openPanel() {
        if (!leafletReady) {
            showFeedback(
                'danger',
                'The map library could not be loaded. Check your internet connection or firewall, then reload the page. You can still type the coordinates manually.'
            );
            return;
        }

        panel.hidden = false;
        toggleBtn.setAttribute('aria-expanded', 'true');
        toggleLabel.textContent = 'Hide Map';

        ensureMap();

        /* The container was hidden when Leaflet measured it. */
        setTimeout(function () {
            if (!map) return;

            map.invalidateSize();

            const current = readInputs();

            if (current) {
                placeMarker(current.lat, current.lng);
                map.setView([current.lat, current.lng], Math.max(map.getZoom(), 18));
            }
        }, 60);
    }

    function closePanel() {
        panel.hidden = true;
        toggleBtn.setAttribute('aria-expanded', 'false');
        toggleLabel.textContent = 'Pick Location on Map';
    }

    toggleBtn.addEventListener('click', function () {
        panel.hidden ? openPanel() : closePanel();
    });

    /* ------------------------------------------------------------
       Writing coordinates (never submits the form)
    ------------------------------------------------------------ */

    function writeInputs(lat, lng) {
        latInput.value = Number(lat).toFixed(7);
        lngInput.value = Number(lng).toFixed(7);
        refreshChangedNote();
    }

    function applyCoords(lat, lng, source) {
        writeInputs(lat, lng);
        placeMarker(lat, lng);

        if (source === 'map') {
            showAccuracy(null);
            clearGpsOverlay();
            showFeedback('success', 'Location selected on the map. The Latitude and Longitude fields were updated. Verify them before saving.');
        }
    }

    /* Manual typing keeps the pin and the note in sync. */
    function onManualInput() {
        refreshChangedNote();

        const current = readInputs();

        if (current && map) {
            placeMarker(current.lat, current.lng);
        }
    }

    latInput.addEventListener('input', onManualInput);
    lngInput.addEventListener('input', onManualInput);

    /* ------------------------------------------------------------
       Locate Current Position (one-time browser geolocation)
    ------------------------------------------------------------ */

    const locateBtn = $('locateCurrentPositionBtn');
    const locateLabel = $('locateCurrentPositionLabel');

    function setLocating(on) {
        locateBtn.disabled = on;
        locateLabel.textContent = on ? 'Locating…' : 'Locate Current Position';
    }

    function afterAttemptLabel() {
        locateLabel.textContent = 'Try Locating Again';
    }

    locateBtn.addEventListener('click', function () {
        if (!('geolocation' in navigator)) {
            showFeedback('danger', 'This browser does not support location capture. Use "Pick Location on Map" or type the coordinates manually.');
            return;
        }

        if (window.isSecureContext === false) {
            showFeedback(
                'warning',
                'Location capture needs a secure connection (HTTPS) or localhost. This page is opened over an ordinary HTTP address, so the browser will likely block it. Use "Pick Location on Map" instead.'
            );
        } else {
            showFeedback('info', 'Requesting your current position. Please allow the location prompt in your browser…');
        }

        setLocating(true);

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                setLocating(false);
                afterAttemptLabel();

                if (!Number.isFinite(lat) || !Number.isFinite(lng) || Math.abs(lat) > 90 || Math.abs(lng) > 180) {
                    showAccuracy(null);
                    showFeedback('danger', 'The browser returned invalid location data. Please try again.');
                    return;
                }

                writeInputs(lat, lng);
                placeMarker(lat, lng);

                const hasAccuracy = Number.isFinite(accuracy) && accuracy >= 0;
                showAccuracy(hasAccuracy ? accuracy : null);

                /* Show the accuracy circle if the map is already open. */
                if (map) {
                    clearGpsOverlay();

                    if (hasAccuracy) {
                        accuracyCircle = L.circle([lat, lng], {
                            radius: accuracy,
                            color: '#0d6efd',
                            weight: 2,
                            fillColor: '#0d6efd',
                            fillOpacity: 0.12,
                            interactive: false
                        }).addTo(map);
                    }

                    map.setView([lat, lng], Math.max(map.getZoom(), LOCATED_ZOOM));
                }

                if (!hasAccuracy) {
                    showFeedback('warning', 'Location captured, but the browser did not report its accuracy. Verify the pin against the actual house before saving.');
                } else if (accuracy > 100) {
                    showFeedback(
                        'warning',
                        'Location captured, but accuracy is poor (about ' + Math.round(accuracy) +
                        ' meters). The coordinates may be far from the house. Try again, move to an open area, or fine-tune the pin on the map.'
                    );
                } else if (accuracy > 50) {
                    showFeedback(
                        'warning',
                        'Location captured with moderate accuracy (about ' + Math.round(accuracy) +
                        ' meters). Verify the pin against the actual house before saving.'
                    );
                } else {
                    showFeedback(
                        'success',
                        'Location captured (accuracy about ' + Math.round(accuracy) +
                        ' meters). The Latitude and Longitude fields were updated. Verify them before saving.'
                    );
                }
            },
            function (error) {
                setLocating(false);
                afterAttemptLabel();
                showAccuracy(null);

                const messages = {
                    1: ['danger', 'Location permission was denied. Allow location access for this site in your browser settings, then click "Try Locating Again". You can also use "Pick Location on Map".'],
                    2: ['danger', 'Your location is unavailable. Check that location services are turned on for this device, then try again.'],
                    3: ['warning', 'The location request timed out. Click "Try Locating Again", or use "Pick Location on Map".']
                };

                const m = messages[error.code] || ['danger', 'Unable to capture your current position. Please try again.'];
                showFeedback(m[0], m[1]);
            },
            {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 0
            }
        );
    });

    /* ------------------------------------------------------------
       Restore saved location
    ------------------------------------------------------------ */

    const restoreBtn = $('restoreSavedLocationBtn');

    if (restoreBtn) {
        restoreBtn.addEventListener('click', function () {
            if (!hasSaved) return;

            latInput.value = String(savedLat);
            lngInput.value = String(savedLng);

            clearGpsOverlay();
            showAccuracy(null);
            refreshChangedNote();

            if (map) {
                placeMarker(savedLat, savedLng);
                map.setView([savedLat, savedLng], Math.max(map.getZoom(), 18));
            }

            showFeedback('info', 'The saved location was restored in the fields.');
        });
    }

    /* ------------------------------------------------------------
       Initial state
    ------------------------------------------------------------ */

    refreshChangedNote();

    /* Unused-value guards so the initial values stay referenced for debugging. */
    void initialLat;
    void initialLng;

    if (window.isSecureContext === false) {
        showFeedback(
            'warning',
            'This page is not using HTTPS or localhost, so "Locate Current Position" may be blocked by the browser. "Pick Location on Map" will still work.'
        );
    }
});
</script>

@endif

@endsection
