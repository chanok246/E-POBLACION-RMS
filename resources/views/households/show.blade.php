@extends('layouts.app')

@section('title', 'Household Details | e-Poblacion')

@section('content')

@php
$currentUser = Auth::user();


$isCaptain = $currentUser && $currentUser->role === 'captain';
$isSecretary = $currentUser && $currentUser->role === 'secretary';

$canManageHouseholds = $isCaptain || $isSecretary;


@endphp

<style>
    /* ============================================================
       HOUSEHOLD DETAILS - THEME VARIABLES
    ============================================================ */

    .household-show-page {
        --hs-surface: #ffffff;
        --hs-surface-soft: #f8f9fc;
        --hs-text: #252b43;
        --hs-heading: #243746;
        --hs-muted: #7b8994;
        --hs-border: #dce3e8;
        --hs-table-head: #f7f9fb;
        --hs-table-border: #edf0f2;
        --hs-input-bg: #ffffff;
        --hs-input-text: #252b43;
        --hs-placeholder: #8a98a3;
        --hs-header-bg: #f8f9fc;
        --hs-header-text: #243746;
        --hs-code-bg: #f4f6f8;
        --hs-code-text: #43515d;
        --hs-secondary-bg: #f8f9fa;
        --hs-secondary-border: #d8dee3;
        --hs-secondary-text: #495057;
        --hs-warning-bg: #fff8e6;
        --hs-warning-border: #f4d58d;
        --hs-warning-text: #664d03;
        --hs-info-bg: #f1f7fb;
        --hs-info-border: #dcebf3;
        --hs-info-text: #31566d;
        --hs-success-bg: #f1fbf4;
        --hs-success-border: #b9dfc5;
        --hs-success-text: #1f6333;
        --hs-danger-bg: #fff1f1;
        --hs-danger-border: #f1b8b8;
        --hs-danger-text: #842029;
        --hs-shadow: 0 2px 8px rgba(32, 46, 60, 0.06);
    }

    /* ============================================================
       PAGE TEXT
    ============================================================ */

    .household-show-page {
        color: var(--hs-text);
    }

    .household-show-page h1,
    .household-show-page h2,
    .household-show-page h3,
    .household-show-page h4,
    .household-show-page h5,
    .household-show-page h6 {
        color: var(--hs-heading);
    }

    .household-show-page .text-muted {
        color: var(--hs-muted) !important;
    }

    /* ============================================================
       CARDS
    ============================================================ */

    .household-show-page .card {
        background-color: var(--hs-surface);
        border-color: var(--hs-border);
        color: var(--hs-text);
        box-shadow: var(--hs-shadow);
    }

    .household-show-page .card-header {
        background-color: var(--hs-header-bg);
        border-bottom-color: var(--hs-border);
        color: var(--hs-header-text);
    }

    .household-show-page .card-body {
        color: var(--hs-text);
    }

    /* ============================================================
       FORM CONTROLS
    ============================================================ */

    .household-show-page .form-label {
        color: var(--hs-heading);
    }

    .household-show-page .form-control,
    .household-show-page .form-select {
        background-color: var(--hs-input-bg);
        border-color: var(--hs-border);
        color: var(--hs-input-text);
    }

    .household-show-page .form-control:focus,
    .household-show-page .form-select:focus {
        background-color: var(--hs-input-bg);
        color: var(--hs-input-text);
    }

    .household-show-page .form-control[readonly],
    .household-show-page textarea.form-control[readonly] {
        background-color: var(--hs-code-bg);
        color: var(--hs-input-text);
    }

    .household-show-page .form-control::placeholder {
        color: var(--hs-placeholder);
    }

    .household-show-page .form-select option {
        background-color: var(--hs-input-bg);
        color: var(--hs-input-text);
    }

    /* ============================================================
       TABLE
    ============================================================ */

    .household-show-page .table {
        --bs-table-bg: var(--hs-surface);
        --bs-table-color: var(--hs-text);
        --bs-table-border-color: var(--hs-table-border);
        color: var(--hs-text);
        margin-bottom: 0;
    }

    .household-show-page .table thead th {
        background-color: var(--hs-table-head);
        color: var(--hs-heading);
        border-bottom-color: var(--hs-border);
        white-space: nowrap;
    }

    .household-show-page .table tbody td {
        color: var(--hs-text);
        border-color: var(--hs-table-border);
        vertical-align: middle;
    }

    .household-show-page .table-hover > tbody > tr:hover > * {
        --bs-table-accent-bg: var(--hs-table-head);
        color: var(--hs-text);
    }

    /* ============================================================
       ALERTS
    ============================================================ */

    .household-show-page .alert {
        border-width: 1px;
    }

    .household-show-page .alert-info {
        background-color: var(--hs-info-bg);
        border-color: var(--hs-info-border);
        color: var(--hs-info-text);
    }

    .household-show-page .alert-success {
        background-color: var(--hs-success-bg);
        border-color: var(--hs-success-border);
        color: var(--hs-success-text);
    }

    .household-show-page .alert-warning {
        background-color: var(--hs-warning-bg);
        border-color: var(--hs-warning-border);
        color: var(--hs-warning-text);
    }

    .household-show-page .alert-danger {
        background-color: var(--hs-danger-bg);
        border-color: var(--hs-danger-border);
        color: var(--hs-danger-text);
    }

    .household-show-page .alert ul,
    .household-show-page .alert li {
        color: inherit;
    }

    /* ============================================================
       BADGES
    ============================================================ */

    .household-show-page .badge.bg-light {
        background-color: var(--hs-secondary-bg) !important;
        color: var(--hs-secondary-text) !important;
        border-color: var(--hs-secondary-border) !important;
    }

    /* ============================================================
       RESPONSIVE ACTION AREA
    ============================================================ */

    .household-show-page .household-page-header-actions {
        flex-wrap: wrap;
    }

    @media (max-width: 768px) {
        .household-show-page .household-page-header {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 1rem;
        }

        .household-show-page .household-page-header-actions {
            width: 100%;
        }

        .household-show-page .household-page-header-actions > a,
        .household-show-page .household-page-header-actions > form {
            flex: 1 1 auto;
        }

        .household-show-page .household-page-header-actions > form button,
        .household-show-page .household-page-header-actions > a {
            width: 100%;
        }
    }

    /* ============================================================
       DARK MODE
    ============================================================ */

    html[data-theme="dark"] .household-show-page {
        --hs-surface: #1b1d2a;
        --hs-surface-soft: #20232f;
        --hs-text: #f1f3f8;
        --hs-heading: #f1f3f8;
        --hs-muted: #aeb4c5;
        --hs-border: rgba(255, 255, 255, 0.10);
        --hs-table-head: #20232f;
        --hs-table-border: rgba(255, 255, 255, 0.08);
        --hs-input-bg: #20232f;
        --hs-input-text: #f1f3f8;
        --hs-placeholder: #858b9d;
        --hs-header-bg: #20232f;
        --hs-header-text: #f1f3f8;
        --hs-code-bg: #20232f;
        --hs-code-text: #f1f3f8;
        --hs-secondary-bg: #292d3a;
        --hs-secondary-border: rgba(255, 255, 255, 0.12);
        --hs-secondary-text: #e3e6ed;
        --hs-warning-bg: #352d1d;
        --hs-warning-border: rgba(255, 193, 7, 0.30);
        --hs-warning-text: #f5d98a;
        --hs-info-bg: #202a36;
        --hs-info-border: rgba(91, 170, 220, 0.25);
        --hs-info-text: #d6e8f4;
        --hs-success-bg: #20352a;
        --hs-success-border: rgba(67, 160, 91, 0.45);
        --hs-success-text: #b9e6c4;
        --hs-danger-bg: #351f23;
        --hs-danger-border: rgba(220, 53, 69, 0.35);
        --hs-danger-text: #f2b8bf;
        --hs-shadow: 0 2px 10px rgba(0, 0, 0, 0.18);
    }

    html[data-theme="dark"] .household-show-page .form-control,
    html[data-theme="dark"] .household-show-page .form-select {
        color-scheme: dark;
    }

    html[data-theme="dark"] .household-show-page .table-hover > tbody > tr:hover > * {
        --bs-table-accent-bg: #272a37;
        color: var(--hs-text);
    }

    html[data-theme="dark"] .household-show-page .btn-close {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    /* ============================================================
       PRINT
    ============================================================ */

    @media print {
        .household-show-page .household-page-header-actions,
        .household-show-page .alert,
        .household-show-page form {
            display: none !important;
        }

        .household-show-page .card {
            box-shadow: none !important;
            break-inside: avoid;
        }
    }
</style>

<div class="container-fluid household-show-page">

{{-- ============================================================
    HEADER
============================================================= --}}

<div class="d-flex justify-content-between align-items-center mb-4 household-page-header">

    <div>

        <div class="text-muted small">
            E-POBLACION / HOUSEHOLDS / VIEW HOUSEHOLD
        </div>

        <h1 class="mt-1">
            Household Details
        </h1>

        <p class="text-muted">
            View household information and manage household members.
        </p>

    </div>

    <div class="d-flex gap-2 household-page-header-actions">

        {{-- ====================================================
            MANAGEMENT ACTIONS
            Captain + Secretary only
        ===================================================== --}}

        @if($canManageHouseholds)

            {{-- Edit --}}

            <a
                href="{{ route('households.edit', $household) }}"
                class="btn btn-primary"
            >
                <i class="bi bi-pencil me-1"></i>
                Edit
            </a>


            {{-- Archive / Restore --}}

            @if($household->status === 'Active')

                <form
                    action="{{ route('households.archive', $household) }}"
                    method="POST"
                    onsubmit="return confirm('Are you sure you want to archive this household? Use this when the household has moved out or is no longer active.');"
                >

                    @csrf

                    @method('PATCH')

                    <button
                        type="submit"
                        class="btn btn-warning"
                    >
                        <i class="bi bi-archive me-1"></i>
                        Archive
                    </button>

                </form>

            @else

                {{-- Restore --}}

                <form
                    action="{{ route('households.restore', $household) }}"
                    method="POST"
                    onsubmit="return confirm('Restore this household to Active status?');"
                >

                    @csrf

                    @method('PATCH')

                    <button
                        type="submit"
                        class="btn btn-success"
                    >
                        <i class="bi bi-arrow-counterclockwise me-1"></i>
                        Restore
                    </button>

                </form>

            @endif

        @endif


        {{-- ====================================================
            BACK
            All authorized roles
        ===================================================== --}}

        <a
            href="{{ route('households.index') }}"
            class="btn btn-secondary"
        >
            <i class="bi bi-arrow-left me-1"></i>
            Back
        </a>

    </div>

</div>


{{-- ============================================================
    ERROR MESSAGE
============================================================= --}}

@if(session('error'))

    <div class="alert alert-danger">

        <i class="bi bi-exclamation-triangle me-2"></i>

        {{ session('error') }}

    </div>

@endif


{{-- ============================================================
    VALIDATION ERRORS
============================================================= --}}

@if($errors->any())

    <div class="alert alert-danger">

        <strong>Please check the following:</strong>

        <ul class="mb-0 mt-2">

            @foreach($errors->all() as $error)

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

        <strong>Archived Household</strong>

        <br>

        This household is no longer active. Historical records
        are retained for tracking and documentation purposes.

    </div>

@endif


{{-- ============================================================
    HOUSEHOLD INFORMATION
============================================================= --}}

<div class="card mb-4">

    <div class="card-header">

        <h4 class="mb-0">
            Household Information
        </h4>

    </div>

    <div class="card-body">

        <div class="row">

            {{-- Household Number --}}

            <div class="col-md-4 mb-3">

                <label class="form-label fw-bold">
                    Household Number
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="{{ $household->household_number }}"
                    readonly
                >

            </div>


            {{-- Status --}}

            <div class="col-md-4 mb-3">

                <label class="form-label fw-bold">
                    Status
                </label>

                <div>

                    @if($household->status === 'Active')

                        <span class="badge bg-success fs-6">
                            Active
                        </span>

                    @else

                        <span class="badge bg-secondary fs-6">
                            Archived
                        </span>

                    @endif

                </div>

            </div>


            {{-- Purok --}}

            <div class="col-md-4 mb-3">

                <label class="form-label fw-bold">
                    Purok
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="{{ $household->purok ?: 'Not specified' }}"
                    readonly
                >

            </div>


            {{-- Address --}}

            <div class="col-md-12 mb-3">

                <label class="form-label fw-bold">
                    Address
                </label>

                <textarea
                    class="form-control"
                    rows="3"
                    readonly
                >{{ $household->address }}</textarea>

            </div>

        </div>

    </div>

</div>


{{-- ============================================================
    GPS COORDINATES
============================================================= --}}

<div class="card mb-4">

    <div class="card-header">

        <h4 class="mb-0">
            GPS Coordinates
        </h4>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6 mb-3">

                <label class="form-label fw-bold">
                    Latitude
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="{{ $household->latitude ?? 'Not specified' }}"
                    readonly
                >

            </div>

            <div class="col-md-6 mb-3">

                <label class="form-label fw-bold">
                    Longitude
                </label>

                <input
                    type="text"
                    class="form-control"
                    value="{{ $household->longitude ?? 'Not specified' }}"
                    readonly
                >

            </div>

        </div>

        <small class="text-muted">
            Coordinates are used by the separate Household Map feature.
        </small>

    </div>

</div>


{{-- ============================================================
    HOUSEHOLD HEAD
============================================================= --}}

<div class="card mb-4">

    <div class="card-header">

        <h4 class="mb-0">
            Household Head
        </h4>

    </div>

    <div class="card-body">

        @if($household->householdHead)

            <div class="row">

                {{-- Full Name --}}

                <div class="col-md-6">

                    <label class="form-label fw-bold">
                        Full Name
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="{{ $household->householdHead->full_name }}"
                        readonly
                    >

                </div>


                {{-- Sex --}}

                <div class="col-md-3">

                    <label class="form-label fw-bold">
                        Sex
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="{{ $household->householdHead->sex }}"
                        readonly
                    >

                </div>


                {{-- Age --}}

                <div class="col-md-3">

                    <label class="form-label fw-bold">
                        Age
                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="{{ $household->householdHead->age ?? 'N/A' }}"
                        readonly
                    >

                </div>

            </div>

        @else

            <div class="alert alert-warning mb-0">

                No household head assigned.

            </div>

        @endif

    </div>

</div>


{{-- ============================================================
    ADD HOUSEHOLD MEMBER
    Captain + Secretary only
============================================================= --}}

@if($canManageHouseholds && $household->status === 'Active')

    @php
        $residents = \App\Models\Resident::query()
            ->where('status', 'Active')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();
    @endphp

    <div class="card mb-4">

        <div class="card-header">

            <h4 class="mb-1">
                Add Household Member
            </h4>

            <small class="text-muted">
                Select an existing active resident and assign
                their relationship.
            </small>

        </div>

        <div class="card-body">

            <form
                action="{{ route('households.members.add', $household) }}"
                method="POST"
            >

                @csrf

                <div class="row">

                    {{-- Resident --}}

                    <div class="col-md-6 mb-3">

                        <label
                            class="form-label fw-bold"
                        >

                            Select Resident

                            <span class="text-danger">
                                *
                            </span>

                        </label>

                        <select
                            name="resident_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                -- Select Existing Resident --
                            </option>

                            @foreach($residents as $resident)

                                {{-- Do not show household head --}}

                                @if(
                                    (int) $resident->id
                                    !==
                                    (int) $household->household_head_id
                                )

                                    <option
                                        value="{{ $resident->id }}"
                                        {{ old('resident_id') == $resident->id ? 'selected' : '' }}
                                    >

                                        {{ $resident->last_name }},
                                        {{ $resident->first_name }}

                                        @if($resident->middle_name)
                                            {{ $resident->middle_name }}
                                        @endif

                                        @if($resident->suffix)
                                            {{ $resident->suffix }}
                                        @endif

                                        —

                                        Age:
                                        {{ $resident->age ?? 'N/A' }}

                                    </option>

                                @endif

                            @endforeach

                        </select>

                        <small class="text-muted">

                            Only active residents are available.

                        </small>

                    </div>


                    {{-- Relationship --}}

                    <div class="col-md-4 mb-3">

                        <label
                            class="form-label fw-bold"
                        >

                            Relationship

                            <span class="text-danger">
                                *
                            </span>

                        </label>

                        <select
                            name="relationship"
                            class="form-select"
                            required
                        >

                            <option value="">
                                -- Select Relationship --
                            </option>

                            <option
                                value="Spouse"
                                {{ old('relationship') === 'Spouse' ? 'selected' : '' }}
                            >
                                Spouse
                            </option>

                            <option
                                value="Child"
                                {{ old('relationship') === 'Child' ? 'selected' : '' }}
                            >
                                Child
                            </option>

                            <option
                                value="Parent"
                                {{ old('relationship') === 'Parent' ? 'selected' : '' }}
                            >
                                Parent
                            </option>

                            <option
                                value="Sibling"
                                {{ old('relationship') === 'Sibling' ? 'selected' : '' }}
                            >
                                Sibling
                            </option>

                            <option
                                value="Grandparent"
                                {{ old('relationship') === 'Grandparent' ? 'selected' : '' }}
                            >
                                Grandparent
                            </option>

                            <option
                                value="Grandchild"
                                {{ old('relationship') === 'Grandchild' ? 'selected' : '' }}
                            >
                                Grandchild
                            </option>

                            <option
                                value="Other Relative"
                                {{ old('relationship') === 'Other Relative' ? 'selected' : '' }}
                            >
                                Other Relative
                            </option>

                            <option
                                value="Non-Relative"
                                {{ old('relationship') === 'Non-Relative' ? 'selected' : '' }}
                            >
                                Non-Relative
                            </option>

                        </select>

                    </div>


                    {{-- Add Button --}}

                    <div class="col-md-2 mb-3 d-flex align-items-end">

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i class="bi bi-person-plus me-1"></i>

                            Add Member

                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>

@endif


{{-- ============================================================
    HOUSEHOLD MEMBERS
============================================================= --}}

<div class="card">

    <div class="card-header d-flex justify-content-between align-items-center">

        <div>

            <h4 class="mb-1">
                Household Members
            </h4>

            <small class="text-muted">

                Residents currently registered under this household.

            </small>

        </div>

        <span class="badge bg-primary">

            {{ $household->members->count() }}

            Members

        </span>

    </div>


    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover mb-0">

                <thead>

                    <tr>

                        <th>
                            #
                        </th>

                        <th>
                            Resident
                        </th>

                        <th>
                            Age
                        </th>

                        <th>
                            Relationship
                        </th>

                        <th>
                            Status
                        </th>

                        <th class="text-end">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>


                    {{-- =================================================
                        HOUSEHOLD HEAD FIRST
                    ================================================== --}}

                    @if($household->householdHead)

                        <tr>

                            <td>
                                1
                            </td>

                            <td>

                                <strong>

                                    {{ $household->householdHead->full_name }}

                                </strong>

                            </td>

                            <td>

                                {{ $household->householdHead->age ?? 'N/A' }}

                            </td>

                            <td>

                                <span class="badge bg-primary">

                                    Head

                                </span>

                            </td>

                            <td>

                                @if($household->householdHead->status === 'Active')

                                    <span class="badge bg-success">
                                        Active
                                    </span>

                                @else

                                    <span class="badge bg-secondary">
                                        Archived
                                    </span>

                                @endif

                            </td>

                            <td class="text-end">

                                <span class="badge bg-secondary">

                                    Household Head

                                </span>

                            </td>

                        </tr>

                    @endif


                    {{-- =================================================
                        OTHER MEMBERS
                    ================================================== --}}

                    @forelse(
                        $household->members->where(
                            'relationship',
                            '!=',
                            'Head'
                        )
                        as $member
                    )

                        <tr>

                            <td>

                                {{
                                    $loop->iteration
                                    +
                                    (
                                        $household->householdHead
                                        ? 1
                                        : 0
                                    )
                                }}

                            </td>


                            <td>

                                @if($member->resident)

                                    <strong>

                                        {{ $member->resident->full_name }}

                                    </strong>

                                @else

                                    <span class="text-danger">
                                        Resident not found
                                    </span>

                                @endif

                            </td>


                            <td>

                                @if($member->resident)

                                    {{ $member->resident->age ?? 'N/A' }}

                                @else

                                    N/A

                                @endif

                            </td>


                            <td>

                                {{ $member->relationship }}

                            </td>


                            <td>

                                @if($member->resident)

                                    @if($member->resident->status === 'Active')

                                        <span class="badge bg-success">
                                            Active
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            Archived
                                        </span>

                                    @endif

                                @else

                                    N/A

                                @endif

                            </td>


                            {{-- =================================================
                                MEMBER ACTION
                                Captain + Secretary only
                            ================================================== --}}

                            <td class="text-end">

                                @if($canManageHouseholds && $household->status === 'Active')

                                    <form
                                        action="{{ route(
                                            'households.members.remove',
                                            [
                                                $household,
                                                $member->resident_id
                                            ]
                                        ) }}"
                                        method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Are you sure you want to remove this resident from this household? The resident record itself will NOT be deleted.');"
                                    >

                                        @csrf

                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-danger"
                                        >

                                            <i class="bi bi-person-dash me-1"></i>

                                            Remove

                                        </button>

                                    </form>

                                @else

                                    <span class="text-muted">

                                        Archived household

                                    </span>

                                @endif

                            </td>

                        </tr>


                    @empty

                        @if(!$household->householdHead)

                            <tr>

                                <td
                                    colspan="6"
                                    class="text-center py-4 text-muted"
                                >

                                    No household members found.

                                </td>

                            </tr>

                        @endif

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

</div>

@endsection
