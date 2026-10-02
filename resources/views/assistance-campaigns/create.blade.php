@extends('layouts.app')

@section('title', 'Create Relief Operation | e-Poblacion')

@section('content')

@php
    $disasterEvents = $disasterEvents ?? collect();

    /*
    |--------------------------------------------------------------------------
    | ASSISTANCE MODES
    |--------------------------------------------------------------------------
    |
    | These describe HOW the assistance is provided.
    |
    | in_kind  = physical goods
    | cash      = money / financial assistance
    | priority  = targeted social assistance
    | service   = medical/dental/vaccination/etc.
    |
    | The mode does NOT automatically approve beneficiaries.
    |
    */

    $assistanceModes = [
        'in_kind' => [
            'label' => 'In-Kind Relief Distribution',
            'description' => 'Physical goods such as family food packs, rice, hygiene kits, water, or relief goods.',
            'icon' => 'bi-box-seam',
            'badge' => 'QR Voucher',
        ],

        'cash' => [
            'label' => 'Emergency Cash / Financial Assistance',
            'description' => 'Cash aid, educational assistance, disaster financial assistance, or other monetary support.',
            'icon' => 'bi-cash-coin',
            'badge' => 'QR + Payout',
        ],

        'priority' => [
            'label' => 'Priority Sector Social Assistance',
            'description' => 'Assistance intended for groups such as senior citizens, PWDs, students, or indigent families.',
            'icon' => 'bi-people',
            'badge' => 'Targeted',
        ],

        'service' => [
            'label' => 'Service / Social Assistance',
            'description' => 'Services such as medical missions, dental missions, vaccination, or consultation.',
            'icon' => 'bi-heart-pulse',
            'badge' => 'No Voucher',
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | STARTING MASTERLIST FILTERS
    |--------------------------------------------------------------------------
    |
    | These only identify the initial pool of records.
    | They NEVER automatically approve beneficiaries.
    |
    */

    $targetGroups = [
        'all' => [
            'label' => 'All',
            'description' => 'Start with all active residents connected to active households.',
            'icon' => 'bi-people',
        ],

        'household_heads' => [
            'label' => 'Household Heads',
            'description' => 'Focus on household representatives for household-based assistance.',
            'icon' => 'bi-person-badge',
        ],

        'students' => [
            'label' => 'Students',
            'description' => 'Residents whose current occupation is recorded as Student.',
            'icon' => 'bi-mortarboard',
        ],

        'senior_citizens' => [
            'label' => 'Senior Citizens',
            'description' => 'Residents aged 60 and above based on birth date.',
            'icon' => 'bi-person-wheelchair',
        ],
    ];
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">

<style>
    .rf {
        --ink: #1c1b33;
        --ink-2: #5e5c78;
        --line: #e3e1ee;
        --wash: #f5f4fa;
        --card: #fff;
        --ube: #5a3fd6;
        --ube-soft: #ebe7fb;
        --mango: #ffb020;
        --mango-soft: #fff1d0;
        --red: #cf3d3d;
        --red-soft: #fbe4e4;
        --green: #16845b;
        --green-soft: #e3f5ed;
        --grey: #8a889f;

        max-width: 1280px;
        margin: 0 auto;
        color: var(--ink);
        font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
    }

    .rf *,
    .rf *::before,
    .rf *::after {
        box-sizing: border-box;
    }

    .rf h1,
    .rf h2,
    .rf h3,
    .rf p,
    .rf ol,
    .rf ul,
    .rf dl,
    .rf dd {
        margin: 0;
        padding: 0;
    }

    .rf a:focus-visible,
    .rf button:focus-visible {
        outline: 3px solid var(--mango);
        outline-offset: 2px;
    }

    .rf-display {
        font-family: 'Unbounded', 'Figtree', system-ui, sans-serif;
        letter-spacing: -.02em;
    }

    .rf-crumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 12px;
        font-size: 13px;
        color: var(--ink-2);
    }

    .rf-crumb a {
        color: var(--ink-2);
        text-decoration: none;
        font-weight: 600;
    }

    .rf-crumb a:hover {
        color: var(--ube);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    .rf-hero {
        margin-bottom: 26px;
    }

    .rf-hero h1 {
        font-size: 30px;
        line-height: 1.15;
        font-weight: 800;
    }

    .rf-hero p {
        margin-top: 10px;
        max-width: 700px;
        color: var(--ink-2);
        font-size: 14.5px;
        line-height: 1.6;
    }

    .rf-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 32px;
        align-items: start;
    }

    .rf-aside {
        position: sticky;
        top: 16px;
        display: grid;
        gap: 18px;
    }

    .rf-errors {
        margin-bottom: 22px;
        padding: 16px 18px;
        border-radius: 14px;
        background: var(--red-soft);
        color: #7d2020;
        font-size: 13.5px;
    }

    .rf-errors b {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
        color: var(--red);
        font-size: 14px;
    }

    .rf-errors ul {
        padding-left: 22px;
        list-style: disc;
        display: grid;
        gap: 2px;
    }

    .rf-sec {
        padding: 26px 26px 28px;
        border: 1px solid var(--line);
        border-radius: 18px;
        background: var(--card);
        margin-bottom: 20px;
    }

    .rf-sec-head {
        display: flex;
        align-items: center;
        gap: 13px;
        margin-bottom: 22px;
    }

    .rf-sec-ic {
        flex: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: var(--ube-soft);
        color: var(--ube);
        font-size: 17px;
    }

    .rf-sec-head h2 {
        font-size: 16px;
        font-weight: 800;
    }

    .rf-sec-head p {
        margin-top: 2px;
        font-size: 12.5px;
        color: var(--ink-2);
    }

    .rf-hint {
        margin: -6px 0 16px;
        font-size: 13px;
        line-height: 1.55;
        color: var(--ink-2);
    }

    .rf fieldset {
        border: 0;
        padding: 0;
        margin: 0;
        min-width: 0;
    }

    .rf legend {
        float: none;
        width: auto;
        padding: 0;
        margin: 0 0 10px;
        font-size: 13.5px;
        font-weight: 700;
    }

    .rf-fields {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 18px 16px;
    }

    .rf-f {
        grid-column: span 12;
        min-width: 0;
    }

    .rf-label {
        display: block;
        margin-bottom: 6px;
        font-size: 13.5px;
        font-weight: 600;
    }

    .rf-req {
        margin-left: 6px;
        font-size: 11.5px;
        font-weight: 600;
        color: var(--ube);
    }

    .rf .form-control,
    .rf .form-select {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid var(--line);
        border-radius: 12px;
        background-color: #fff;
        color: var(--ink);
        font-size: 14px;
        font-family: inherit;
        box-shadow: none;
        min-height: 44px;
    }

    .rf textarea.form-control {
        resize: vertical;
        min-height: 84px;
        line-height: 1.5;
    }

    .rf .form-control::placeholder {
        color: #a9a7bd;
    }

    .rf .form-control:focus,
    .rf .form-select:focus {
        border-color: var(--ube);
        box-shadow: 0 0 0 3px rgba(90, 63, 214, .15);
        outline: 0;
    }

    .rf .form-control.is-invalid,
    .rf .form-select.is-invalid {
        border-color: var(--red);
        background-image: none;
    }

    .rf .invalid-feedback {
        margin-top: 5px;
        font-size: 12.5px;
        color: var(--red);
    }

    .rf .form-text {
        margin-top: 7px;
        font-size: 12.5px;
        color: var(--ink-2);
    }

    .rf-msg {
        margin-top: 8px;
        font-size: 12.5px;
        color: var(--red);
    }

    /*
    |--------------------------------------------------------------------------
    | ASSISTANCE MODE CARDS
    |--------------------------------------------------------------------------
    */

    .rf-mode-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .rf-mode {
        position: relative;
        display: block;
        cursor: pointer;
        margin: 0;
    }

    .rf-mode input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        margin: 0;
        opacity: 0;
        cursor: pointer;
    }

    .rf-mode-body {
        display: flex;
        align-items: flex-start;
        gap: 13px;
        min-height: 128px;
        padding: 16px;
        border: 1.5px solid var(--line);
        border-radius: 16px;
        background: #fff;
        transition: border-color .15s, background .15s, transform .15s;
    }

    .rf-mode:hover .rf-mode-body {
        border-color: var(--ube);
        transform: translateY(-1px);
    }

    .rf-mode input:checked + .rf-mode-body {
        border-color: var(--ube);
        background: var(--ube-soft);
    }

    .rf-mode input:focus-visible + .rf-mode-body {
        outline: 3px solid var(--mango);
        outline-offset: 2px;
    }

    .rf-mode-icon {
        flex: none;
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: grid;
        place-items: center;
        background: var(--wash);
        color: var(--ink-2);
        font-size: 19px;
    }

    .rf-mode input:checked + .rf-mode-body .rf-mode-icon {
        background: var(--ube);
        color: #fff;
    }

    .rf-mode-content {
        min-width: 0;
        flex: 1;
    }

    .rf-mode-title {
        display: block;
        font-size: 14px;
        font-weight: 800;
        line-height: 1.35;
    }

    .rf-mode-description {
        display: block;
        margin-top: 5px;
        font-size: 12.5px;
        line-height: 1.45;
        color: var(--ink-2);
    }

    .rf-mode-badge {
        display: inline-block;
        margin-top: 9px;
        padding: 4px 9px;
        border-radius: 999px;
        background: #fff;
        color: var(--ube);
        border: 1px solid var(--line);
        font-size: 11px;
        font-weight: 700;
    }

    .rf-mode input:checked + .rf-mode-body .rf-mode-badge {
        border-color: var(--ube);
    }

    /*
    |--------------------------------------------------------------------------
    | ASSISTANCE NAME / DETAILS
    |--------------------------------------------------------------------------
    */

    .rf-quick-examples {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
        margin-top: 10px;
    }

    .rf-example {
        border: 1px solid var(--line);
        background: var(--wash);
        color: var(--ink-2);
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 11.5px;
        font-weight: 600;
    }

    /*
    |--------------------------------------------------------------------------
    | TARGET TILES
    |--------------------------------------------------------------------------
    */

    .rf-tiles {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .rf-tile {
        position: relative;
        display: block;
        cursor: pointer;
        margin: 0;
    }

    .rf-tile input {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        margin: 0;
        opacity: 0;
        cursor: pointer;
    }

    .rf-tile-body {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        height: 100%;
        padding: 14px 15px;
        border: 1.5px solid var(--line);
        border-radius: 14px;
        background: #fff;
        transition: border-color .15s, background .15s;
    }

    .rf-tile:hover .rf-tile-body {
        border-color: var(--ube);
    }

    .rf-tile input:checked + .rf-tile-body {
        border-color: var(--ube);
        background: var(--ube-soft);
    }

    .rf-tile input:focus-visible + .rf-tile-body {
        outline: 3px solid var(--mango);
        outline-offset: 2px;
    }

    .rf-tile-ic {
        flex: none;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: var(--wash);
        color: var(--ink-2);
        font-size: 16px;
    }

    .rf-tile input:checked + .rf-tile-body .rf-tile-ic {
        background: var(--ube);
        color: #fff;
    }

    .rf-tile-t {
        display: block;
        font-size: 14px;
        font-weight: 700;
        line-height: 1.3;
    }

    .rf-tile-d {
        display: block;
        margin-top: 2px;
        font-size: 12.5px;
        line-height: 1.45;
        color: var(--ink-2);
    }

    .rf-tick {
        flex: none;
        margin-left: auto;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        border: 1.5px solid var(--line);
        background: #fff;
        position: relative;
    }

    .rf-tile input:checked + .rf-tile-body .rf-tick {
        background: var(--ube);
        border-color: var(--ube);
    }

    .rf-tile input:checked + .rf-tile-body .rf-tick::after {
        content: "";
        position: absolute;
        left: 6px;
        top: 2.5px;
        width: 5px;
        height: 9px;
        border: solid #fff;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    /*
    |--------------------------------------------------------------------------
    | OPERATION MODE NOTICE
    |--------------------------------------------------------------------------
    */

    .rf-mode-notice {
        display: flex;
        gap: 12px;
        padding: 14px 16px;
        margin-top: 16px;
        border-radius: 14px;
        background: var(--green-soft);
        color: #155b43;
        font-size: 12.5px;
        line-height: 1.55;
    }

    .rf-mode-notice i {
        flex: none;
        font-size: 17px;
        margin-top: 1px;
    }

    .rf-mode-notice b {
        display: block;
        font-size: 13px;
        margin-bottom: 2px;
    }

    /*
    |--------------------------------------------------------------------------
    | PREVIEW
    |--------------------------------------------------------------------------
    */

    .rf-pv {
        border: 1px solid var(--line);
        border-radius: 18px;
        background: var(--card);
        overflow: hidden;
    }

    .rf-pv-head {
        padding: 14px 20px;
        background: var(--wash);
        border-bottom: 1px dashed #cfcce0;
        font-size: 12.5px;
        color: var(--ink-2);
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }

    .rf-pv-head b {
        color: var(--ink);
        font-size: 13.5px;
    }

    .rf-pv-top {
        display: flex;
        gap: 14px;
        align-items: center;
        padding: 20px 20px 16px;
    }

    .rf-date {
        flex: none;
        width: 66px;
        padding: 8px 4px 7px;
        text-align: center;
        border-radius: 14px;
        background: var(--wash);
        border: 1px solid var(--line);
    }

    .rf-date b {
        display: block;
        font-family: 'Unbounded', 'Figtree', sans-serif;
        font-size: 22px;
        font-weight: 700;
        line-height: 1.05;
    }

    .rf-date span {
        display: block;
        margin-top: 3px;
        font-size: 11.5px;
        font-weight: 600;
        color: var(--ink-2);
    }

    .rf-date.is-none b {
        color: #c4c2d6;
    }

    .rf-pv-name {
        font-size: 16px;
        font-weight: 700;
        line-height: 1.3;
        overflow-wrap: anywhere;
    }

    .rf-pv-name.is-empty {
        color: #a9a7bd;
    }

    .rf-pv-type {
        display: inline-block;
        margin-top: 6px;
        padding: 3px 11px;
        border-radius: 999px;
        background: var(--ube-soft);
        color: var(--ube);
        font-size: 12px;
        font-weight: 700;
    }

    .rf-pv-rows {
        padding: 4px 20px 20px;
    }

    .rf-pv-row {
        padding: 11px 0;
        border-top: 1px solid var(--line);
    }

    .rf-pv-row dt {
        font-size: 12px;
        font-weight: 600;
        color: var(--grey);
    }

    .rf-pv-row dd {
        margin-top: 3px;
        font-size: 13.5px;
        font-weight: 600;
        line-height: 1.4;
        overflow-wrap: anywhere;
    }

    .rf-pv-row dd.is-empty {
        color: #a9a7bd;
        font-weight: 500;
    }

    .rf-submit {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        width: 100%;
        padding: 14px 20px;
        border: 0;
        border-radius: 999px;
        background: var(--ube);
        color: #fff;
        font: inherit;
        font-size: 15px;
        font-weight: 700;
        cursor: pointer;
        transition: background .15s, transform .15s;
    }

    .rf-submit:hover {
        background: var(--ink);
    }

    .rf-submit:active {
        transform: scale(.98);
    }

    .rf-submit:disabled {
        opacity: .7;
        cursor: wait;
    }

    .rf-cancel {
        display: block;
        margin-top: 8px;
        padding: 11px;
        border-radius: 999px;
        text-align: center;
        font-size: 14px;
        font-weight: 600;
        color: var(--ink-2);
        text-decoration: none;
    }

    .rf-cancel:hover {
        background: var(--wash);
        color: var(--ink);
    }

    .rf-req-note {
        margin-bottom: 10px;
        text-align: center;
        font-size: 12px;
        color: var(--ink-2);
    }

    .rf-note {
        display: flex;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 14px;
        background: var(--mango-soft);
        font-size: 12.5px;
        line-height: 1.55;
    }

    .rf-note i {
        flex: none;
        font-size: 17px;
        color: #9a6a00;
        margin-top: 1px;
    }

    .rf-note b {
        display: block;
        font-size: 13px;
        margin-bottom: 2px;
    }

    @media (min-width: 768px) {
        .rf-f.s4 {
            grid-column: span 4;
        }

        .rf-f.s6 {
            grid-column: span 6;
        }
    }

    @media (max-width: 1099.98px) {
        .rf-grid {
            grid-template-columns: 1fr;
            gap: 8px;
        }

        .rf-aside {
            position: static;
        }
    }

    @media (max-width: 767.98px) {
        .rf-mode-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 575.98px) {
        .rf-hero h1 {
            font-size: 24px;
        }

        .rf-sec {
            padding: 20px 16px 22px;
        }

        .rf-tiles {
            grid-template-columns: 1fr;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .rf *,
        .rf *::before,
        .rf *::after {
            transition: none !important;
        }
    }
</style>

<div class="container-fluid py-4 rf">

    {{-- ================================================================
         HEADER
    ================================================================= --}}
    <header class="rf-hero">

        <nav class="rf-crumb" aria-label="Breadcrumb">
            <a href="{{ route('relief-vouchers.index') }}">
                <i class="bi bi-arrow-left"></i> Relief Assistance
            </a>

            <span aria-hidden="true">/</span>

            <span>New operation</span>
        </nav>

        <h1 class="rf-display">Create Relief / Assistance Operation</h1>

        <p>
            Create the operation first. The system will use the operation type
            to determine whether the activity uses QR vouchers, financial
            payout tracking, or service/participant records.
        </p>

    </header>

    {{-- ================================================================
         VALIDATION ERRORS
    ================================================================= --}}
    @if ($errors->any())

        <div class="rf-errors" role="alert">

            <b>
                <i class="bi bi-exclamation-circle"></i>
                Please correct the following before creating the operation:
            </b>

            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>

        </div>

    @endif

    <form
        method="POST"
        action="{{ route('relief-assistance.store') }}"
        id="reliefOperationForm"
    >

        @csrf

        <div class="rf-grid">

            {{-- ========================================================
                 MAIN FORM
            ========================================================= --}}

            <div>

                {{-- ====================================================
                     OPERATION DETAILS
                ===================================================== --}}

                <section class="rf-sec" aria-labelledby="secDetails">

                    <div class="rf-sec-head">

                        <div class="rf-sec-ic">
                            <i class="bi bi-clipboard-plus"></i>
                        </div>

                        <div>

                            <h2 id="secDetails">
                                Operation details
                            </h2>

                            <p>
                                Identify what the barangay is providing
                            </p>

                        </div>

                    </div>

                    <div class="rf-fields">

                        {{-- Operation name --}}
                        <div class="rf-f">

                            <label
                                for="operation_name"
                                class="rf-label"
                            >
                                Operation name
                                <span class="rf-req">required</span>
                            </label>

                            <input
                                type="text"
                                class="form-control form-control-lg @error('operation_name') is-invalid @enderror"
                                id="operation_name"
                                name="operation_name"
                                value="{{ old('operation_name') }}"
                                maxlength="255"
                                placeholder="Example: Post-Typhoon Family Food Pack Distribution"
                                required
                                autofocus
                            >

                            @error('operation_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="form-text">
                                Use a clear name that staff can recognize later.
                            </div>

                        </div>

                        {{-- Assistance mode --}}
                        <div class="rf-f">

                            <fieldset>

                                <legend>
                                    Assistance mode
                                    <span class="rf-req">required</span>
                                </legend>

                                <div class="rf-mode-grid">

                                    @foreach ($assistanceModes as $modeKey => $mode)

                                        <label
                                            class="rf-mode"
                                            for="assistance_type_{{ $modeKey }}"
                                        >

                                            <input
                                                type="radio"
                                                name="assistance_type"
                                                id="assistance_type_{{ $modeKey }}"
                                                value="{{ $modeKey }}"
                                                @checked(old('assistance_type') === $modeKey)
                                                required
                                            >

                                            <span class="rf-mode-body">

                                                <span class="rf-mode-icon">
                                                    <i class="bi {{ $mode['icon'] }}"></i>
                                                </span>

                                                <span class="rf-mode-content">

                                                    <span class="rf-mode-title">
                                                        {{ $mode['label'] }}
                                                    </span>

                                                    <span class="rf-mode-description">
                                                        {{ $mode['description'] }}
                                                    </span>

                                                    <span class="rf-mode-badge">
                                                        {{ $mode['badge'] }}
                                                    </span>

                                                </span>

                                            </span>

                                        </label>

                                    @endforeach

                                </div>

                                @error('assistance_type')
                                    <div class="rf-msg">
                                        <i class="bi bi-exclamation-circle me-1"></i>
                                        {{ $message }}
                                    </div>
                                @enderror

                            </fieldset>

                            <div
                                class="rf-mode-notice"
                                id="modeNotice"
                            >

                                <i class="bi bi-info-circle-fill"></i>

                                <div>

                                    <b id="modeNoticeTitle">
                                        Select an assistance mode
                                    </b>

                                    <span id="modeNoticeText">
                                        The selected mode will determine the
                                        appropriate workflow after the operation
                                        is created.
                                    </span>

                                </div>

                            </div>

                        </div>

                        {{-- Description --}}
                        <div class="rf-f">

                            <label
                                for="description"
                                class="rf-label"
                            >
                                Operation description
                            </label>

                            <textarea
                                class="form-control @error('description') is-invalid @enderror"
                                id="description"
                                name="description"
                                rows="3"
                                maxlength="2000"
                                placeholder="Briefly describe why this operation is being conducted..."
                            >{{ old('description') }}</textarea>

                            @error('description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Assistance details --}}
                        <div class="rf-f">

                            <label
                                for="assistance_description"
                                class="rf-label"
                            >
                                What is being provided?
                            </label>

                            <textarea
                                class="form-control @error('assistance_description') is-invalid @enderror"
                                id="assistance_description"
                                name="assistance_description"
                                rows="3"
                                maxlength="2000"
                                placeholder="Example: One Family Food Pack per confirmed household."
                            >{{ old('assistance_description') }}</textarea>

                            @error('assistance_description')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="form-text">
                                Describe the actual goods, money, or service.
                            </div>

                            <div class="rf-quick-examples">

                                <span class="rf-example">
                                    Family Food Pack
                                </span>

                                <span class="rf-example">
                                    ₱1,000 Cash Aid
                                </span>

                                <span class="rf-example">
                                    Educational Assistance
                                </span>

                                <span class="rf-example">
                                    Medical Consultation
                                </span>

                            </div>

                        </div>

                    </div>

                </section>

                {{-- ====================================================
                     MASTERLIST TARGET
                ===================================================== --}}

                <section
                    class="rf-sec"
                    aria-labelledby="secTarget"
                >

                    <div class="rf-sec-head">

                        <div class="rf-sec-ic">
                            <i class="bi bi-funnel"></i>
                        </div>

                        <div>

                            <h2 id="secTarget">
                                Where should the masterlist start?
                            </h2>

                            <p>
                                Starting filter for the Masterlist Workspace
                            </p>

                        </div>

                    </div>

                    <p class="rf-hint">
                        This only identifies the initial pool of records.
                        It does not decide eligibility or approve anyone.
                        Staff can change the filter later and use Purok/search
                        during masterlist preparation.
                    </p>

                    <fieldset>

                        <legend class="visually-hidden">
                            Starting group
                        </legend>

                        <div class="rf-tiles">

                            @foreach ($targetGroups as $key => $group)

                                <label
                                    class="rf-tile"
                                    for="target_group_{{ $key }}"
                                >

                                    <input
                                        type="radio"
                                        name="target_group"
                                        id="target_group_{{ $key }}"
                                        value="{{ $key }}"
                                        @checked(old('target_group', 'all') === $key)
                                    >

                                    <span class="rf-tile-body">

                                        <span class="rf-tile-ic">
                                            <i class="bi {{ $group['icon'] }}"></i>
                                        </span>

                                        <span>

                                            <span class="rf-tile-t">
                                                {{ $group['label'] }}
                                            </span>

                                            <span class="rf-tile-d">
                                                {{ $group['description'] }}
                                            </span>

                                        </span>

                                        <span class="rf-tick"></span>

                                    </span>

                                </label>

                            @endforeach

                        </div>

                    </fieldset>

                </section>

                {{-- ====================================================
                     ASSISTANCE STOCK / RESOURCE
                ===================================================== --}}

                <section
                    class="rf-sec"
                    aria-labelledby="secStock"
                >

                    <div class="rf-sec-head">

                        <div class="rf-sec-ic">
                            <i class="bi bi-box-seam"></i>
                        </div>

                        <div>

                            <h2 id="secStock">
                                Available assistance
                            </h2>

                            <p>
                                Record the overall amount, quantity, or resource
                            </p>

                        </div>

                    </div>

                    <div class="rf-fields">

                        {{-- Source --}}
                        <div class="rf-f s6">

                            <label
                                for="source_name"
                                class="rf-label"
                            >
                                Source / Donor
                            </label>

                            <input
                                type="text"
                                class="form-control @error('source_name') is-invalid @enderror"
                                id="source_name"
                                name="source_name"
                                value="{{ old('source_name') }}"
                                maxlength="255"
                                placeholder="Example: LGU / DSWD / Barangay"
                            >

                            @error('source_name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Source reference --}}
                        <div class="rf-f s6">

                            <label
                                for="source_reference"
                                class="rf-label"
                            >
                                Source reference
                            </label>

                            <input
                                type="text"
                                class="form-control @error('source_reference') is-invalid @enderror"
                                id="source_reference"
                                name="source_reference"
                                value="{{ old('source_reference') }}"
                                maxlength="255"
                                placeholder="Reference number, memo, delivery record, etc."
                            >

                            @error('source_reference')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Date received --}}
                        <div class="rf-f s4">

                            <label
                                for="assistance_received_date"
                                class="rf-label"
                            >
                                Date received
                            </label>

                            <input
                                type="date"
                                class="form-control @error('assistance_received_date') is-invalid @enderror"
                                id="assistance_received_date"
                                name="assistance_received_date"
                                value="{{ old('assistance_received_date') }}"
                            >

                            @error('assistance_received_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Quantity --}}
                        <div class="rf-f s4">

                            <label
                                for="quantity_received"
                                class="rf-label"
                            >
                                Amount / Quantity
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                class="form-control @error('quantity_received') is-invalid @enderror"
                                id="quantity_received"
                                name="quantity_received"
                                value="{{ old('quantity_received') }}"
                                placeholder="Example: 500"
                            >

                            @error('quantity_received')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                            <div class="form-text">
                                Example: 500 food packs or 100000 cash.
                            </div>

                        </div>

                        {{-- Unit --}}
                        <div class="rf-f s4">

                            <label
                                for="quantity_unit"
                                class="rf-label"
                            >
                                Unit
                            </label>

                            <input
                                type="text"
                                class="form-control @error('quantity_unit') is-invalid @enderror"
                                id="quantity_unit"
                                name="quantity_unit"
                                value="{{ old('quantity_unit') }}"
                                maxlength="100"
                                placeholder="packs, PHP, kits, etc."
                            >

                            @error('quantity_unit')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                    <p class="form-text mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        This records the total available assistance. It does not
                        automatically determine who receives it.
                    </p>

                </section>

                {{-- ====================================================
                     DISTRIBUTION / SERVICE SCHEDULE
                ===================================================== --}}

                <section
                    class="rf-sec"
                    aria-labelledby="secDist"
                >

                    <div class="rf-sec-head">

                        <div class="rf-sec-ic">
                            <i class="bi bi-calendar-event"></i>
                        </div>

                        <div>

                            <h2 id="secDist">
                                Schedule and location
                            </h2>

                            <p>
                                Planned distribution, payout, or service details
                            </p>

                        </div>

                    </div>

                    <div class="rf-fields">

                        {{-- Date --}}
                        <div class="rf-f s4">

                            <label
                                for="distribution_date"
                                class="rf-label"
                            >
                                Date
                            </label>

                            <input
                                type="date"
                                class="form-control @error('distribution_date') is-invalid @enderror"
                                id="distribution_date"
                                name="distribution_date"
                                value="{{ old('distribution_date') }}"
                            >

                            @error('distribution_date')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Time --}}
                        <div class="rf-f s4">

                            <label
                                for="distribution_time"
                                class="rf-label"
                            >
                                Time
                            </label>

                            <input
                                type="time"
                                class="form-control @error('distribution_time') is-invalid @enderror"
                                id="distribution_time"
                                name="distribution_time"
                                value="{{ old('distribution_time') }}"
                            >

                            @error('distribution_time')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Location --}}
                        <div class="rf-f s4">

                            <label
                                for="distribution_location"
                                class="rf-label"
                            >
                                Location
                            </label>

                            <input
                                type="text"
                                class="form-control @error('distribution_location') is-invalid @enderror"
                                id="distribution_location"
                                name="distribution_location"
                                value="{{ old('distribution_location') }}"
                                maxlength="255"
                                placeholder="Example: Barangay Hall"
                            >

                            @error('distribution_location')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                        {{-- Notes --}}
                        <div class="rf-f">

                            <label
                                for="allocation_notes"
                                class="rf-label"
                            >
                                Additional notes
                            </label>

                            <textarea
                                class="form-control @error('allocation_notes') is-invalid @enderror"
                                id="allocation_notes"
                                name="allocation_notes"
                                rows="3"
                                maxlength="2000"
                                placeholder="Add operational notes that staff may need later..."
                            >{{ old('allocation_notes') }}</textarea>

                            @error('allocation_notes')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror

                        </div>

                    </div>

                </section>

                {{-- ====================================================
                     DISASTER REFERENCE
                ===================================================== --}}

                <section
                    class="rf-sec"
                    aria-labelledby="secDisaster"
                >

                    <div class="rf-sec-head">

                        <div class="rf-sec-ic">
                            <i class="bi bi-cloud-lightning-rain"></i>
                        </div>

                        <div>

                            <h2 id="secDisaster">
                                Disaster reference
                            </h2>

                            <p>
                                Optional. For disaster-related assistance
                            </p>

                        </div>

                    </div>

                    <label
                        for="disaster_event_id"
                        class="rf-label"
                    >
                        Disaster event
                    </label>

                    <select
                        class="form-select @error('disaster_event_id') is-invalid @enderror"
                        id="disaster_event_id"
                        name="disaster_event_id"
                    >

                        <option value="">
                            No disaster event / Not applicable
                        </option>

                        @foreach ($disasterEvents as $disasterEvent)

                            <option
                                value="{{ $disasterEvent->id }}"
                                @selected((string) old('disaster_event_id') === (string) $disasterEvent->id)
                            >
                                {{
                                    $disasterEvent->name
                                    ?? $disasterEvent->event_name
                                    ?? $disasterEvent->disaster_name
                                    ?? ('Disaster Event #' . $disasterEvent->id)
                                }}
                            </option>

                        @endforeach

                    </select>

                    @error('disaster_event_id')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                    <div class="form-text">
                        This only links the operation to an existing disaster
                        event. It does not automatically make anyone eligible.
                    </div>

                </section>

            </div>

            {{-- ========================================================
                 RIGHT ASIDE
            ========================================================= --}}

            <aside
                class="rf-aside"
                aria-label="Summary and actions"
            >

                <div
                    class="rf-pv"
                    aria-live="polite"
                >

                    <div class="rf-pv-head">
                        <b>Before you create</b>
                        <span>Updates as you type</span>
                    </div>

                    <div class="rf-pv-top">

                        <div
                            class="rf-date is-none"
                            id="pvDate"
                        >

                            <b id="pvDay">--</b>

                            <span id="pvMonth">
                                No date
                            </span>

                        </div>

                        <div>

                            <div
                                class="rf-pv-name is-empty"
                                id="pvName"
                            >
                                Untitled operation
                            </div>

                            <span
                                class="rf-pv-type"
                                id="pvType"
                                hidden
                            ></span>

                        </div>

                    </div>

                    <dl class="rf-pv-rows">

                        <div class="rf-pv-row">

                            <dt>Assistance mode</dt>

                            <dd
                                id="pvMode"
                                class="is-empty"
                            >
                                Not selected
                            </dd>

                        </div>

                        <div class="rf-pv-row">

                            <dt>Masterlist starts with</dt>

                            <dd id="pvTarget">
                                All
                            </dd>

                        </div>

                        <div class="rf-pv-row">

                            <dt>Time</dt>

                            <dd
                                id="pvTime"
                                class="is-empty"
                            >
                                Not set
                            </dd>

                        </div>

                        <div class="rf-pv-row">

                            <dt>Location</dt>

                            <dd
                                id="pvPlace"
                                class="is-empty"
                            >
                                Not set
                            </dd>

                        </div>

                        <div class="rf-pv-row">

                            <dt>Amount / Quantity</dt>

                            <dd
                                id="pvQty"
                                class="is-empty"
                            >
                                Not set
                            </dd>

                        </div>

                        <div class="rf-pv-row">

                            <dt>Disaster reference</dt>

                            <dd
                                id="pvDisaster"
                                class="is-empty"
                            >
                                None
                            </dd>

                        </div>

                    </dl>

                </div>

                <div>

                    <div class="rf-req-note">
                        Operation name and assistance mode are required.
                    </div>

                    <button
                        type="submit"
                        class="rf-submit"
                        id="createOperationButton"
                    >
                        <i class="bi bi-plus-lg"></i>
                        Create operation
                    </button>

                    <a
                        href="{{ route('relief-vouchers.index') }}"
                        class="rf-cancel"
                    >
                        Cancel
                    </a>

                </div>

                <div class="rf-note">

                    <i class="bi bi-person-check-fill"></i>

                    <div>

                        <b>Human verification remains required</b>

                        Filters only find potential records.
                        Authorized barangay staff still verify and confirm
                        the actual beneficiaries or participants.

                    </div>

                </div>

            </aside>

        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    var form = document.getElementById('reliefOperationForm');

    if (!form) {
        return;
    }

    var $ = function (id) {
        return document.getElementById(id);
    };

    var createButton = $('createOperationButton');

    /*
    |--------------------------------------------------------------------------
    | DISPLAY LABELS
    |--------------------------------------------------------------------------
    */

    var targetLabels = {
        all: 'All',
        household_heads: 'Household Heads',
        students: 'Students',
        senior_citizens: 'Senior Citizens'
    };

    var modeLabels = {
        in_kind: 'In-Kind Relief Distribution',
        cash: 'Emergency Cash / Financial Assistance',
        priority: 'Priority Sector Social Assistance',
        service: 'Service / Social Assistance'
    };

    var modeNotices = {

        in_kind: {
            title: 'QR voucher workflow will be available',
            text: 'This operation is for physical goods such as Family Food Packs. Confirmed households or beneficiaries can proceed to unique QR vouchers for controlled claiming.'
        },

        cash: {
            title: 'QR payout workflow will be available',
            text: 'This operation is for monetary assistance. Confirmed beneficiaries can receive a unique QR claim ticket linked to the payout record.'
        },

        priority: {
            title: 'Targeted assistance workflow',
            text: 'Use the masterlist filters to identify potential priority-sector beneficiaries. Staff must verify and confirm the actual recipients before assistance is released.'
        },

        service: {
            title: 'Service workflow — no voucher by default',
            text: 'This is intended for activities such as Medical Missions, Dental Missions, or Vaccination Drives. The system will track participants and services instead of generating relief vouchers.'
        }

    };

    /*
    |--------------------------------------------------------------------------
    | BASIC VALUE HELPER
    |--------------------------------------------------------------------------
    */

    function val(id) {

        var element = $(id);

        return element
            ? element.value.trim()
            : '';

    }

    /*
    |--------------------------------------------------------------------------
    | PREVIEW ROW HELPER
    |--------------------------------------------------------------------------
    */

    function setRow(id, text, fallback) {

        var element = $(id);

        if (!element) {
            return;
        }

        element.textContent = text || fallback;

        element.classList.toggle(
            'is-empty',
            !text
        );

    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE MODE NOTICE
    |--------------------------------------------------------------------------
    */

    function updateModeNotice() {

        var selected = form.querySelector(
            'input[name="assistance_type"]:checked'
        );

        var title = $('modeNoticeTitle');
        var text = $('modeNoticeText');

        if (!selected) {

            title.textContent =
                'Select an assistance mode';

            text.textContent =
                'The selected mode will determine the appropriate workflow after the operation is created.';

            return;
        }

        var notice =
            modeNotices[selected.value];

        if (!notice) {
            return;
        }

        title.textContent =
            notice.title;

        text.textContent =
            notice.text;

    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE SUMMARY
    |--------------------------------------------------------------------------
    */

    function updateSummary() {

        /*
        | Operation name
        */

        var name = val('operation_name');

        var nameEl = $('pvName');

        nameEl.textContent =
            name || 'Untitled operation';

        nameEl.classList.toggle(
            'is-empty',
            !name
        );

        /*
        | Assistance mode
        */

        var typeInput =
            form.querySelector(
                'input[name="assistance_type"]:checked'
            );

        var typeEl = $('pvType');

        typeEl.hidden = !typeInput;

        if (typeInput) {

            typeEl.textContent =
                typeInput.value === 'in_kind'
                    ? 'QR Voucher'
                    : typeInput.value === 'cash'
                        ? 'QR + Payout'
                        : typeInput.value === 'service'
                            ? 'No Voucher'
                            : 'Targeted';

        }

        setRow(
            'pvMode',
            typeInput
                ? (
                    modeLabels[typeInput.value]
                    || typeInput.value
                )
                : '',
            'Not selected'
        );

        /*
        | Target
        */

        var targetInput =
            form.querySelector(
                'input[name="target_group"]:checked'
            );

        $('pvTarget').textContent =
            targetInput
                ? (
                    targetLabels[targetInput.value]
                    || targetInput.value
                )
                : 'All';

        /*
        | Distribution date
        */

        var distributionDate =
            val('distribution_date');

        var dateBox =
            $('pvDate');

        if (distributionDate) {

            var parts =
                distributionDate.split('-');

            var date =
                new Date(
                    +parts[0],
                    +parts[1] - 1,
                    +parts[2]
                );

            $('pvDay').textContent =
                ('0' + date.getDate()).slice(-2);

            $('pvMonth').textContent =
                date.toLocaleDateString(
                    'en-US',
                    {
                        month: 'short',
                        year: 'numeric'
                    }
                );

            dateBox.classList.remove(
                'is-none'
            );

        } else {

            $('pvDay').textContent = '--';

            $('pvMonth').textContent =
                'No date';

            dateBox.classList.add(
                'is-none'
            );

        }

        /*
        | Time
        */

        var timeValue =
            val('distribution_time');

        var timeText = '';

        if (timeValue) {

            var timeParts =
                timeValue.split(':');

            timeText =
                new Date(
                    2000,
                    0,
                    1,
                    +timeParts[0],
                    +timeParts[1]
                ).toLocaleTimeString(
                    'en-US',
                    {
                        hour: 'numeric',
                        minute: '2-digit'
                    }
                );

        }

        setRow(
            'pvTime',
            timeText,
            'Not set'
        );

        /*
        | Location
        */

        setRow(
            'pvPlace',
            val('distribution_location'),
            'Not set'
        );

        /*
        | Quantity
        */

        var quantity =
            val('quantity_received');

        var unit =
            val('quantity_unit');

        setRow(
            'pvQty',
            quantity
                ? quantity + (
                    unit
                        ? ' ' + unit
                        : ''
                )
                : '',
            'Not set'
        );

        /*
        | Disaster
        */

        var disaster =
            $('disaster_event_id');

        var disasterText = '';

        if (
            disaster &&
            disaster.value !== ''
        ) {

            var option =
                disaster.options[
                    disaster.selectedIndex
                ];

            disasterText =
                option
                    ? option.text
                        .replace(/\s+/g, ' ')
                        .trim()
                    : 'Selected';

        }

        setRow(
            'pvDisaster',
            disasterText,
            'None'
        );

    }

    /*
    |--------------------------------------------------------------------------
    | EVENTS
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'input',
        function () {

            updateSummary();

        }
    );

    form.addEventListener(
        'change',
        function () {

            updateSummary();

            updateModeNotice();

        }
    );

    /*
    |--------------------------------------------------------------------------
    | PREVENT DOUBLE SUBMISSION
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        function () {

            if (!form.checkValidity()) {
                return;
            }

            if (createButton) {

                createButton.disabled = true;

                createButton.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>' +
                    'Creating operation...';

            }

        }
    );

    /*
    |--------------------------------------------------------------------------
    | INITIALIZE
    |--------------------------------------------------------------------------
    */

    updateSummary();

    updateModeNotice();

});
</script>

@endsection
