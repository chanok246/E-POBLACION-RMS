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

@endphp

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
    }

    /* ============================================================
       PRINT
    ============================================================ */

    @media print {
        .household-edit-page .household-edit-header,
        .household-edit-page .edit-form-actions,
        .household-edit-page .alert {
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
                        TEMPORARY GPS EDITING
                    ===================================================== --}}

                    <div class="col-12">

                        <div class="gps-section">

                            <div class="d-flex align-items-center mb-2">

                                <i class="bi bi-geo-alt-fill me-2 text-primary"></i>

                                <div class="gps-section-title">
                                    GPS Coordinates
                                </div>

                            </div>

                            <div class="gps-coordinate-note mb-3">

                                Temporary editing is enabled so you can
                                replace the dummy coordinates with the actual
                                location from Google Maps.

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


                            <div class="mt-3">

                                <div class="alert alert-info mb-0">

                                    <i class="bi bi-info-circle me-2"></i>

                                    <strong>Google Maps:</strong>

                                    Open Google Maps, right-click the actual
                                    household location, copy the latitude and
                                    longitude, then paste the values above.

                                </div>

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

@endsection
