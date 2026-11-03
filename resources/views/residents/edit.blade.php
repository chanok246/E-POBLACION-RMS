@extends('layouts.app')

@section('title', 'Edit Resident | e-Poblacion')

@section('content')

@php
    /*
    |--------------------------------------------------------------------------
    | Resident edit form
    |--------------------------------------------------------------------------
    | Senior Citizen is derived from birth_date (age 60+).
    | PWD and Solo Parent belong to the individual household-member record.
    | 4Ps and Indigent/Low-Income belong to the household record.
    */

    $memberRecord = $householdMember ?? null;

    $residentName = trim(implode(' ', array_filter([
        $resident->first_name ?? '',
        $resident->middle_name ?? '',
        $resident->last_name ?? '',
        $resident->suffix ?? '',
    ])));

    $birthDateValue = old(
        'birth_date',
        $resident->birth_date
            ? \Illuminate\Support\Carbon::parse($resident->birth_date)->format('Y-m-d')
            : ''
    );

    $age = null;

    if ($birthDateValue) {
        try {
            $birthDate = \Illuminate\Support\Carbon::parse($birthDateValue);

            if ($birthDate->lte(now()->startOfDay())) {
                $age = $birthDate->age;
            }
        } catch (\Throwable $e) {
            $age = null;
        }
    }

    $isSeniorCitizen = $age !== null && $age >= 60;
    $statusValue = old('status', $resident->status ?? 'Active');
    $currentYear = now()->year;

    $residencyYearValue = old(
        'residency_start_year',
        $resident->residency_start_year ?? ''
    );

    $fullAddress = old('address', $resident->address ?? '');
    $phoneValue = old('contact_number', $resident->contact_number ?? '');
    $emailValue = old('email', $resident->email ?? '');
    $occupationValue = old('occupation', $resident->occupation ?? '');

    $pwdValue = (string) old(
        'is_pwd',
        $memberRecord ? (int) ($memberRecord->is_pwd ?? 0) : 0
    );

    $soloParentValue = (string) old(
        'is_solo_parent',
        $memberRecord ? (int) ($memberRecord->is_solo_parent ?? 0) : 0
    );
@endphp

<style>
    /*
    |--------------------------------------------------------------------------
    | Resident Edit - Theme Variables
    |--------------------------------------------------------------------------
    | Light theme is the default.
    | Dark theme works when data-theme="dark" is set on a parent element
    | or directly on .resident-edit-page.
    */

    .resident-edit-page {
        --re-bg: #f3f5f9;
        --re-card: #ffffff;
        --re-input: #ffffff;
        --re-text: #253047;
        --re-muted: #647084;
        --re-border: #d9e0ea;
        --re-primary: #405bc5;
        --re-primary-hover: #344da9;
        --re-primary-soft: #edf1ff;
        --re-focus: rgba(64, 91, 197, .18);
        --re-shadow: 0 8px 24px rgba(30, 41, 59, .045);
        --re-notice: #f7f9fc;
        --re-notice-text: #354158;
        --re-preview-cover: linear-gradient(135deg, #3349a8, #536bd5);

        color: var(--re-text);
        background: transparent;
        color-scheme: light;
    }

    [data-theme="dark"] .resident-edit-page,
    .resident-edit-page[data-theme="dark"] {
        --re-bg: #202938;
        --re-card: #283344;
        --re-input: #202a39;
        --re-text: #e8edf5;
        --re-muted: #b0bdcf;
        --re-border: #435066;
        --re-primary: #a9bbff;
        --re-primary-hover: #c1ceff;
        --re-primary-soft: #35425e;
        --re-focus: rgba(169, 187, 255, .2);
        --re-shadow: 0 8px 24px rgba(0, 0, 0, .16);
        --re-notice: #222e3e;
        --re-notice-text: #dce5f2;
        --re-preview-cover: linear-gradient(135deg, #35466f, #495e91);

        color: var(--re-text);
        color-scheme: dark;
    }

    .resident-edit-page *,
    .resident-edit-page *::before,
    .resident-edit-page *::after {
        box-sizing: border-box;
    }

    .resident-edit-page .page-heading {
        color: var(--re-text);
        font-weight: 800;
        letter-spacing: -.035em;
        margin-bottom: .4rem;
    }

    .resident-edit-page .page-subtitle {
        color: var(--re-muted);
        max-width: 720px;
        margin-bottom: 0;
        line-height: 1.6;
    }

    .resident-edit-page .breadcrumb-text {
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--re-muted);
        margin-bottom: .5rem;
    }

    .resident-edit-page .edit-card,
    .resident-edit-page .preview-card {
        background: var(--re-card);
        color: var(--re-text);
        border: 1px solid var(--re-border);
        border-radius: 16px;
        box-shadow: var(--re-shadow);
        overflow: hidden;
    }

    .resident-edit-page .card-heading {
        padding: 1.15rem 1.35rem;
        border-bottom: 1px solid var(--re-border);
        display: flex;
        align-items: center;
        gap: .85rem;
        background: var(--re-card);
    }

    .resident-edit-page .heading-icon {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        background: var(--re-primary-soft);
        color: var(--re-primary);
        font-size: 1.15rem;
    }

    .resident-edit-page .card-heading h2 {
        color: var(--re-text);
        font-size: 1rem;
        font-weight: 800;
        margin: 0;
    }

    .resident-edit-page .card-heading p {
        color: var(--re-muted);
        font-size: .82rem;
        line-height: 1.5;
        margin: .2rem 0 0;
    }

    .resident-edit-page .card-content {
        padding: 1.35rem;
    }

    .resident-edit-page .section-label {
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .075em;
        text-transform: uppercase;
        color: var(--re-muted);
        margin: .15rem 0 1rem;
    }

    .resident-edit-page .form-label {
        color: var(--re-text);
        font-size: .86rem;
        font-weight: 700;
        margin-bottom: .45rem;
    }

    .resident-edit-page .form-control,
    .resident-edit-page .form-select {
        min-height: 44px;
        border: 1px solid var(--re-border);
        border-radius: 10px;
        background-color: var(--re-input) !important;
        color: var(--re-text) !important;
        box-shadow: none;
    }

    .resident-edit-page textarea.form-control {
        min-height: 100px;
        resize: vertical;
    }

    .resident-edit-page .form-control::placeholder {
        color: var(--re-muted);
        opacity: .85;
    }

    .resident-edit-page .form-control:focus,
    .resident-edit-page .form-select:focus {
        border-color: var(--re-primary);
        box-shadow: 0 0 0 .2rem var(--re-focus);
        outline: none;
    }

    .resident-edit-page .form-control:disabled,
    .resident-edit-page .form-select:disabled {
        background: var(--re-bg) !important;
        color: var(--re-muted) !important;
        opacity: .8;
    }

    .resident-edit-page .field-help {
        display: block;
        color: var(--re-muted);
        font-size: .77rem;
        line-height: 1.5;
        margin-top: .35rem;
    }

    .resident-edit-page .required-mark {
        color: #dc3545;
    }

    .resident-edit-page .section-divider {
        border: 0;
        border-top: 1px solid var(--re-border);
        margin: 1.5rem 0;
        opacity: 1;
    }

    /* Classification cards */

    .resident-edit-page .classification-option {
        display: flex;
        gap: .85rem;
        align-items: flex-start;
        padding: 1rem;
        height: 100%;
        border: 1px solid var(--re-border);
        border-radius: 12px;
        background: var(--re-bg);
        color: var(--re-text);
        cursor: pointer;
        transition: border-color .15s ease, background-color .15s ease;
    }

    .resident-edit-page .classification-option:hover {
        border-color: var(--re-primary);
    }

    .resident-edit-page .classification-option:has(input[type="checkbox"]:checked) {
        border-color: var(--re-primary);
        background: var(--re-primary-soft);
    }

    .resident-edit-page .classification-option input[type="checkbox"] {
        width: 18px;
        height: 18px;
        margin-top: .15rem;
        flex-shrink: 0;
        accent-color: var(--re-primary);
        cursor: pointer;
    }

    .resident-edit-page .classification-title {
        display: block;
        color: var(--re-text);
        font-weight: 800;
        font-size: .9rem;
        margin-bottom: .25rem;
    }

    .resident-edit-page .classification-description {
        display: block;
        color: var(--re-muted);
        font-size: .79rem;
        line-height: 1.55;
    }

    /* Status options */

    .resident-edit-page .status-options {
        display: flex;
        flex-wrap: wrap;
        gap: .65rem;
    }

    .resident-edit-page .status-option {
        position: relative;
    }

    .resident-edit-page .status-option input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        overflow: hidden;
    }

    .resident-edit-page .status-option label {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .7rem 1rem;
        border: 1px solid var(--re-border);
        border-radius: 10px;
        background: var(--re-card);
        color: var(--re-text);
        cursor: pointer;
        font-weight: 700;
        font-size: .85rem;
        transition: border-color .15s ease, background-color .15s ease;
    }

    .resident-edit-page .status-option input:checked + label {
        border-color: var(--re-primary);
        background: var(--re-primary-soft);
        color: var(--re-primary);
    }

    .resident-edit-page .status-option input:focus-visible + label {
        outline: 3px solid var(--re-focus);
        outline-offset: 2px;
    }

    /* Live preview */

    .resident-edit-page .preview-card {
        position: sticky;
        top: 1rem;
    }

    .resident-edit-page .preview-cover {
        padding: 1.4rem;
        color: #ffffff;
        background: var(--re-preview-cover);
    }

    .resident-edit-page .preview-kicker {
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: .1em;
        font-size: .7rem;
        opacity: .88;
        font-weight: 800;
    }

    .resident-edit-page .preview-name {
        color: #ffffff;
        font-size: 1.25rem;
        font-weight: 800;
        line-height: 1.4;
        overflow-wrap: anywhere;
        margin: .65rem 0 .55rem;
    }

    .resident-edit-page .preview-status {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        color: #ffffff;
        font-size: .75rem;
        font-weight: 800;
        padding: .35rem .65rem;
        border: 1px solid rgba(255, 255, 255, .25);
        border-radius: 99px;
        background: rgba(255, 255, 255, .13);
    }

    .resident-edit-page .preview-body {
        padding: 1.25rem;
        background: var(--re-card);
    }

    .resident-edit-page .preview-label {
        color: var(--re-muted);
        font-size: .73rem;
        margin-bottom: .25rem;
    }

    .resident-edit-page .preview-value {
        color: var(--re-text);
        font-size: .88rem;
        font-weight: 700;
        line-height: 1.5;
        overflow-wrap: anywhere;
    }

    .resident-edit-page .preview-item {
        padding: .85rem 0;
        border-bottom: 1px solid var(--re-border);
    }

    .resident-edit-page .preview-item:last-child {
        border-bottom: 0;
    }

    .resident-edit-page .completion-track {
        height: 7px;
        background: var(--re-border);
        border-radius: 99px;
        overflow: hidden;
    }

    .resident-edit-page .completion-fill {
        height: 100%;
        width: 0;
        border-radius: inherit;
        background: var(--re-primary);
        transition: width .2s ease;
    }

    /* Notices and actions */

    .resident-edit-page .notice-box {
        border: 1px solid var(--re-border);
        border-radius: 12px;
        padding: 1rem;
        background: var(--re-notice);
        color: var(--re-notice-text);
        font-size: .84rem;
        line-height: 1.6;
    }

    .resident-edit-page .notice-box strong,
    .resident-edit-page .notice-box .fw-bold {
        color: var(--re-text);
    }

    .resident-edit-page .action-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        flex-wrap: wrap;
        padding: 1.1rem 1.35rem;
        border-top: 1px solid var(--re-border);
        background: var(--re-bg);
    }

    .resident-edit-page .action-bar .text-secondary {
        color: var(--re-muted) !important;
    }

    .resident-edit-page .btn {
        border-radius: 10px;
        font-weight: 700;
        padding: .7rem 1rem;
        transition: background-color .15s ease, border-color .15s ease;
    }

    .resident-edit-page .btn-primary {
        background: var(--re-primary);
        border-color: var(--re-primary);
        color: #ffffff;
    }

    .resident-edit-page .btn-primary:hover,
    .resident-edit-page .btn-primary:focus {
        background: var(--re-primary-hover);
        border-color: var(--re-primary-hover);
        color: #ffffff;
    }

    .resident-edit-page .btn-light {
        background: var(--re-card);
        border-color: var(--re-border) !important;
        color: var(--re-text);
    }

    .resident-edit-page .btn-light:hover,
    .resident-edit-page .btn-light:focus {
        background: var(--re-bg);
        border-color: var(--re-primary) !important;
        color: var(--re-text);
    }

    .resident-edit-page .alert {
        border-radius: 12px;
    }

    .resident-edit-page .invalid-feedback {
        font-size: .8rem;
    }

    @media (max-width: 991.98px) {
        .resident-edit-page .preview-card {
            position: static;
        }
    }

    @media (max-width: 575.98px) {
        .resident-edit-page .card-content,
        .resident-edit-page .card-heading {
            padding: 1rem;
        }

        .resident-edit-page .action-bar {
            padding: 1rem;
        }

        .resident-edit-page .action-bar > .d-flex {
            width: 100%;
        }

        .resident-edit-page .action-bar .btn {
            flex: 1 1 auto;
        }

        .resident-edit-page .preview-cover,
        .resident-edit-page .preview-body {
            padding: 1rem;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .resident-edit-page *,
        .resident-edit-page *::before,
        .resident-edit-page *::after {
            transition: none !important;
            scroll-behavior: auto !important;
        }
    }
</style>

<div class="container-fluid resident-edit-page py-2">

    {{-- PAGE HEADER --}}
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <div class="breadcrumb-text">Residents / Edit Record</div>

            <h1 class="page-heading h3">Edit Resident Record</h1>

            <p class="page-subtitle">
                Update the resident's personal information and individual classifications.
                Household classifications are managed separately.
            </p>
        </div>

        <a href="{{ route('residents.index') }}" class="btn btn-light border">
            <i class="bi bi-arrow-left me-1"></i>
            Back to Residents
        </a>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <div class="fw-bold mb-2">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                Please review the following errors:
            </div>

            <ul class="mb-0 ps-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        id="residentEditForm"
        action="{{ route('residents.update', $resident->id) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        <div class="row g-4">

            {{-- MAIN FORM --}}
            <div class="col-12 col-xl-8">

                {{-- PERSONAL INFORMATION --}}
                <section class="edit-card mb-4">

                    <div class="card-heading">
                        <div class="heading-icon">
                            <i class="bi bi-person-vcard"></i>
                        </div>

                        <div>
                            <h2>Personal Information</h2>
                            <p>Keep the resident's identity details accurate and up to date.</p>
                        </div>
                    </div>

                    <div class="card-content">

                        <div class="section-label">Name</div>

                        <div class="row g-3">

                            <div class="col-12 col-md-6">
                                <label for="first_name" class="form-label">
                                    First Name <span class="required-mark">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="first_name"
                                    name="first_name"
                                    class="form-control @error('first_name') is-invalid @enderror"
                                    value="{{ old('first_name', $resident->first_name) }}"
                                    maxlength="100"
                                    autocomplete="given-name"
                                    required
                                >

                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="middle_name" class="form-label">Middle Name</label>

                                <input
                                    type="text"
                                    id="middle_name"
                                    name="middle_name"
                                    class="form-control @error('middle_name') is-invalid @enderror"
                                    value="{{ old('middle_name', $resident->middle_name) }}"
                                    maxlength="100"
                                    autocomplete="additional-name"
                                >

                                @error('middle_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="last_name" class="form-label">
                                    Last Name <span class="required-mark">*</span>
                                </label>

                                <input
                                    type="text"
                                    id="last_name"
                                    name="last_name"
                                    class="form-control @error('last_name') is-invalid @enderror"
                                    value="{{ old('last_name', $resident->last_name) }}"
                                    maxlength="100"
                                    autocomplete="family-name"
                                    required
                                >

                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="suffix" class="form-label">Suffix</label>

                                <select
                                    id="suffix"
                                    name="suffix"
                                    class="form-select @error('suffix') is-invalid @enderror"
                                >
                                    <option value="">None</option>

                                    @foreach (['Jr.', 'Sr.', 'II', 'III', 'IV', 'V'] as $suffixOption)
                                        <option
                                            value="{{ $suffixOption }}"
                                            @selected(old('suffix', $resident->suffix) === $suffixOption)
                                        >
                                            {{ $suffixOption }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('suffix')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>

                        <hr class="section-divider">

                        <div class="section-label">Personal Details</div>

                        <div class="row g-3">

                            <div class="col-12 col-md-6">
                                <label for="birth_date" class="form-label">
                                    Date of Birth <span class="required-mark">*</span>
                                </label>

                                <input
                                    type="date"
                                    id="birth_date"
                                    name="birth_date"
                                    class="form-control @error('birth_date') is-invalid @enderror"
                                    value="{{ $birthDateValue }}"
                                    max="{{ now()->toDateString() }}"
                                    required
                                >

                                <small class="field-help">
                                    Age and Senior Citizen filtering are derived from this date.
                                </small>

                                @error('birth_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="sex" class="form-label">
                                    Sex <span class="required-mark">*</span>
                                </label>

                                <select
                                    id="sex"
                                    name="sex"
                                    class="form-select @error('sex') is-invalid @enderror"
                                    required
                                >
                                    <option value="">Select sex</option>
                                    <option value="Male" @selected(old('sex', $resident->sex) === 'Male')>
                                        Male
                                    </option>
                                    <option value="Female" @selected(old('sex', $resident->sex) === 'Female')>
                                        Female
                                    </option>
                                </select>

                                @error('sex')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="civil_status" class="form-label">
                                    Civil Status <span class="required-mark">*</span>
                                </label>

                                <select
                                    id="civil_status"
                                    name="civil_status"
                                    class="form-select @error('civil_status') is-invalid @enderror"
                                    required
                                >
                                    <option value="">Select civil status</option>

                                    @foreach (['Single', 'Married', 'Widowed', 'Divorced', 'Separated'] as $civilStatusOption)
                                        <option
                                            value="{{ $civilStatusOption }}"
                                            @selected(old('civil_status', $resident->civil_status) === $civilStatusOption)
                                        >
                                            {{ $civilStatusOption }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('civil_status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="residency_start_year" class="form-label">
                                    Residency Start Year
                                </label>

                                <input
                                    type="number"
                                    id="residency_start_year"
                                    name="residency_start_year"
                                    class="form-control @error('residency_start_year') is-invalid @enderror"
                                    value="{{ $residencyYearValue }}"
                                    min="1900"
                                    max="{{ $currentYear }}"
                                    step="1"
                                >

                                <small class="field-help">
                                    Optional. Enter the year the resident started living in the barangay.
                                </small>

                                @error('residency_start_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </section>

                {{-- CONTACT INFORMATION --}}
                <section class="edit-card mb-4">

                    <div class="card-heading">
                        <div class="heading-icon">
                            <i class="bi bi-telephone"></i>
                        </div>

                        <div>
                            <h2>Contact Information</h2>
                            <p>Maintain the resident's current contact details and address.</p>
                        </div>
                    </div>

                    <div class="card-content">

                        <div class="row g-3">

                            {{--
                                Contact Number and Email are OPTIONAL.
                                Both inputs are type="text" with no required/pattern so the
                                browser never blocks blank, N/A, or None. The server turns
                                blank/N/A/None into NULL and still validates real values.
                            --}}
                            <div class="col-12 col-md-6">
                                <label for="contact_number" class="form-label">
                                    Contact Number
                                </label>

                                <input
                                    type="text"
                                    inputmode="tel"
                                    id="contact_number"
                                    name="contact_number"
                                    class="form-control @error('contact_number') is-invalid @enderror"
                                    value="{{ $phoneValue }}"
                                    placeholder="Optional — 09XXXXXXXXX or N/A"
                                    maxlength="20"
                                    autocomplete="tel"
                                >

                                <small class="field-help">
                                    Optional. Use a Philippine mobile number, such as 09123456789 or +639123456789.
                                    You may leave it blank or enter N/A or None.
                                </small>

                                @error('contact_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label">Email Address</label>

                                <input
                                    type="text"
                                    inputmode="email"
                                    id="email"
                                    name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ $emailValue }}"
                                    placeholder="Optional — name@email.com or N/A"
                                    maxlength="255"
                                    autocomplete="email"
                                >

                                <small class="field-help">
                                    Optional. You may leave it blank or enter N/A or None.
                                </small>

                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">
                                    Complete Address <span class="required-mark">*</span>
                                </label>

                                <textarea
                                    id="address"
                                    name="address"
                                    class="form-control @error('address') is-invalid @enderror"
                                    rows="3"
                                    maxlength="1000"
                                    autocomplete="street-address"
                                    required
                                >{{ $fullAddress }}</textarea>

                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="occupation" class="form-label">Occupation</label>

                                <input
                                    type="text"
                                    id="occupation"
                                    name="occupation"
                                    class="form-control @error('occupation') is-invalid @enderror"
                                    value="{{ $occupationValue }}"
                                    maxlength="150"
                                >

                                @error('occupation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </div>
                </section>

                {{-- INDIVIDUAL CLASSIFICATIONS --}}
                <section class="edit-card mb-4">

                    <div class="card-heading">
                        <div class="heading-icon">
                            <i class="bi bi-person-check"></i>
                        </div>

                        <div>
                            <h2>Individual Classifications</h2>
                            <p>These classifications apply to the individual resident, not the entire household.</p>
                        </div>
                    </div>

                    <div class="card-content">

                        @if ($memberRecord)

                            <div class="notice-box mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Update the individual's PWD or Solo Parent classification when the information has
                                been verified by authorized barangay personnel. These selections do not replace
                                any required supporting documents or verification.
                            </div>

                            <div class="row g-3">

                                <div class="col-12 col-md-6">
                                    <label class="classification-option" for="is_pwd">
                                        <input type="hidden" name="is_pwd" value="0">

                                        <input
                                            type="checkbox"
                                            id="is_pwd"
                                            name="is_pwd"
                                            value="1"
                                            @checked($pwdValue === '1')
                                        >

                                        <span>
                                            <span class="classification-title">
                                                Person with Disability (PWD)
                                            </span>
                                            <span class="classification-description">
                                                Mark this resident as PWD when the classification has been verified.
                                            </span>
                                        </span>
                                    </label>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="classification-option" for="is_solo_parent">
                                        <input type="hidden" name="is_solo_parent" value="0">

                                        <input
                                            type="checkbox"
                                            id="is_solo_parent"
                                            name="is_solo_parent"
                                            value="1"
                                            @checked($soloParentValue === '1')
                                        >

                                        <span>
                                            <span class="classification-title">Solo Parent</span>
                                            <span class="classification-description">
                                                Mark this resident as a Solo Parent when the classification has been verified.
                                            </span>
                                        </span>
                                    </label>
                                </div>

                            </div>

                            @error('is_pwd')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror

                            @error('is_solo_parent')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror

                        @else

                            <div class="alert alert-warning mb-0">
                                <div class="fw-bold mb-1">
                                    <i class="bi bi-link-45deg me-1"></i>
                                    No household-member record is available
                                </div>

                                <p class="mb-0 small">
                                    This resident must be linked to a household before their PWD or Solo Parent
                                    classification can be edited here. The personal information form remains available.
                                    This page will not automatically create a household-member record.
                                </p>
                            </div>

                        @endif

                        <hr class="section-divider">

                        <div class="notice-box">
                            <div class="fw-bold mb-1">
                                <i class="bi bi-house-door me-2"></i>
                                Household classifications are managed separately
                            </div>

                            <div>
                                <strong>4Ps Beneficiary</strong> and
                                <strong>Indigent / Low-Income</strong>
                                belong to the household record. Update them through the Household module,
                                rather than saving them as individual classifications here.
                            </div>
                        </div>

                        <div class="notice-box mt-3">
                            <div class="fw-bold mb-1">
                                <i class="bi bi-calendar-check me-2"></i>
                                Senior Citizen is based on age
                            </div>

                            <div>
                                @if ($age !== null)
                                    This resident is currently {{ $age }} years old.

                                    @if ($isSeniorCitizen)
                                        The resident meets the system's age-based threshold of 60 years or older.
                                    @else
                                        The resident has not yet reached the age-based threshold of 60.
                                    @endif
                                @else
                                    Enter a valid date of birth to determine the resident's current age.
                                @endif

                                No manual Senior Citizen checkbox is used.
                            </div>
                        </div>

                    </div>
                </section>

                {{-- RECORD STATUS --}}
                <section class="edit-card mb-4">

                    <div class="card-heading">
                        <div class="heading-icon">
                            <i class="bi bi-clipboard-check"></i>
                        </div>

                        <div>
                            <h2>Record Status</h2>
                            <p>Choose the current administrative status of this resident record.</p>
                        </div>
                    </div>

                    <div class="card-content">

                        <label class="form-label d-block">Resident Status</label>

                        <div class="status-options">

                            <div class="status-option">
                                <input
                                    type="radio"
                                    id="status_active"
                                    name="status"
                                    value="Active"
                                    @checked($statusValue === 'Active')
                                >

                                <label for="status_active">
                                    <i class="bi bi-check-circle"></i>
                                    Active
                                </label>
                            </div>

                            <div class="status-option">
                                <input
                                    type="radio"
                                    id="status_archived"
                                    name="status"
                                    value="Archived"
                                    @checked($statusValue === 'Archived')
                                >

                                <label for="status_archived">
                                    <i class="bi bi-archive"></i>
                                    Archived
                                </label>
                            </div>

                        </div>

                        <small class="field-help">
                            Archiving a record should follow the barangay's record-management procedure.
                            This form supports Active and Archived status only.
                        </small>

                        @error('status')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror

                    </div>
                </section>

                {{-- FORM ACTIONS --}}
                <div class="edit-card">
                    <div class="action-bar">
                        <div class="small" style="color: var(--re-muted);">
                            <i class="bi bi-shield-check me-1"></i>
                            Review the changes before saving the resident record.
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('residents.index') }}" class="btn btn-light border">
                                Cancel
                            </a>

                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i>
                                Save Changes
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            {{-- LIVE PREVIEW --}}
            <div class="col-12 col-xl-4">

                <aside class="preview-card">

                    <div class="preview-cover">
                        <div class="preview-kicker">Resident record preview</div>

                        <div class="preview-name" id="previewName">
                            {{ $residentName !== '' ? $residentName : 'Resident Name' }}
                        </div>

                        <div class="preview-status" id="previewStatus">
                            <i class="bi bi-circle-fill"></i>
                            <span>{{ $statusValue }}</span>
                        </div>
                    </div>

                    <div class="preview-body">

                        <div class="section-label">Personal Summary</div>

                        <div class="preview-item">
                            <div class="preview-label">Age</div>
                            <div class="preview-value" id="previewAge">
                                {{ $age !== null ? $age . ' years old' : 'Not available' }}
                            </div>
                        </div>

                        <div class="preview-item">
                            <div class="preview-label">Sex and Civil Status</div>
                            <div class="preview-value" id="previewPersonalDetails">
                                {{ old('sex', $resident->sex) ?: 'Not specified' }}
                                ·
                                {{ old('civil_status', $resident->civil_status) ?: 'Not specified' }}
                            </div>
                        </div>

                        <div class="preview-item">
                            <div class="preview-label">Residency Start Year</div>
                            <div class="preview-value" id="previewResidency">
                                {{ $residencyYearValue ?: 'Not specified' }}
                            </div>
                        </div>

                        <div class="preview-item">
                            <div class="preview-label">Contact Number</div>
                            <div class="preview-value" id="previewPhone">
                                {{ $phoneValue ?: 'Not specified' }}
                            </div>
                        </div>

                        <div class="preview-item">
                            <div class="preview-label">Email Address</div>
                            <div class="preview-value" id="previewEmail">
                                {{ $emailValue ?: 'Not specified' }}
                            </div>
                        </div>

                        <div class="preview-item">
                            <div class="preview-label">Address</div>
                            <div class="preview-value" id="previewAddress">
                                {{ $fullAddress ?: 'Not specified' }}
                            </div>
                        </div>

                        <div class="preview-item">
                            <div class="preview-label">Individual Classifications</div>

                            <div class="preview-value" id="previewClassifications">
                                @php
                                    $initialClassifications = [];

                                    if ($memberRecord && $pwdValue === '1') {
                                        $initialClassifications[] = 'PWD';
                                    }

                                    if ($memberRecord && $soloParentValue === '1') {
                                        $initialClassifications[] = 'Solo Parent';
                                    }
                                @endphp

                                {{ count($initialClassifications) ? implode(', ', $initialClassifications) : 'None selected' }}
                            </div>
                        </div>

                        <hr class="section-divider">

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold small">Form completion</span>
                            <span class="small fw-bold" id="completionText">0%</span>
                        </div>

                        <div
                            class="completion-track"
                            role="progressbar"
                            aria-label="Form completion"
                            aria-valuemin="0"
                            aria-valuemax="100"
                            aria-valuenow="0"
                            id="completionTrack"
                        >
                            <div class="completion-fill" id="completionFill"></div>
                        </div>

                        <small class="field-help mt-2">
                            This is a guide to the required personal information, not a measure of classification eligibility.
                        </small>

                    </div>
                </aside>

            </div>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('residentEditForm');

    if (!form) {
        return;
    }

    const get = (id) => document.getElementById(id);

    const firstName = get('first_name');
    const middleName = get('middle_name');
    const lastName = get('last_name');
    const suffix = get('suffix');
    const birthDate = get('birth_date');
    const sex = get('sex');
    const civilStatus = get('civil_status');
    const residencyYear = get('residency_start_year');
    const phone = get('contact_number');
    const email = get('email');
    const address = get('address');
    const pwd = get('is_pwd');
    const soloParent = get('is_solo_parent');

    function valueOf(element, fallback = 'Not specified') {
        const value = element ? String(element.value || '').trim() : '';
        return value || fallback;
    }

    function updateName() {
        const parts = [
            firstName?.value,
            middleName?.value,
            lastName?.value,
            suffix?.value
        ]
            .map(value => String(value || '').trim())
            .filter(Boolean);

        get('previewName').textContent = parts.join(' ') || 'Resident Name';
    }

    function updateAge() {
        if (!birthDate || !birthDate.value) {
            get('previewAge').textContent = 'Not available';
            return;
        }

        const dob = new Date(birthDate.value + 'T00:00:00');

        if (Number.isNaN(dob.getTime())) {
            get('previewAge').textContent = 'Not available';
            return;
        }

        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (dob > today) {
            get('previewAge').textContent = 'Invalid date';
            return;
        }

        let age = today.getFullYear() - dob.getFullYear();
        const monthDifference = today.getMonth() - dob.getMonth();

        if (
            monthDifference < 0 ||
            (monthDifference === 0 && today.getDate() < dob.getDate())
        ) {
            age--;
        }

        const seniorMessage = age >= 60 ? ' · Senior Citizen' : '';

        get('previewAge').textContent = age + ' years old' + seniorMessage;
    }

    function updatePersonalDetails() {
        get('previewPersonalDetails').textContent =
            valueOf(sex) + ' · ' + valueOf(civilStatus);

        get('previewResidency').textContent = valueOf(residencyYear);
        get('previewPhone').textContent = valueOf(phone);
        get('previewEmail').textContent = valueOf(email);
        get('previewAddress').textContent = valueOf(address);
    }

    function updateStatus() {
        const selected = form.querySelector('input[name="status"]:checked');
        const status = selected ? selected.value : 'Active';

        get('previewStatus').querySelector('span').textContent = status;
    }

    function updateClassifications() {
        const labels = [];

        if (pwd && pwd.checked) {
            labels.push('PWD');
        }

        if (soloParent && soloParent.checked) {
            labels.push('Solo Parent');
        }

        get('previewClassifications').textContent =
            labels.length ? labels.join(', ') : 'None selected';
    }

    function updateCompletion() {
        // Contact number is optional, so it is not counted as required.
        const requiredFields = [
            firstName,
            lastName,
            birthDate,
            sex,
            civilStatus,
            address
        ];

        const complete = requiredFields.filter(function (field) {
            return field && String(field.value || '').trim() !== '';
        }).length;

        const percent = Math.round((complete / requiredFields.length) * 100);

        get('completionText').textContent = percent + '%';
        get('completionFill').style.width = percent + '%';
        get('completionTrack').setAttribute('aria-valuenow', String(percent));
    }

    function updatePreview() {
        updateName();
        updateAge();
        updatePersonalDetails();
        updateStatus();
        updateClassifications();
        updateCompletion();
    }

    form.querySelectorAll('input, select, textarea').forEach(function (field) {
        field.addEventListener('input', updatePreview);
        field.addEventListener('change', updatePreview);
    });

    updatePreview();
});
</script>

@endsection