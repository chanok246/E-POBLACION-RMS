
@extends('layouts.app')

@section('title', 'Add Emergency Hotline | e-Poblacion')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Unbounded:wght@500;600;700&display=swap" rel="stylesheet">

<style>
    /* ============================================================
       EMERGENCY HOTLINES — CREATE
       Form on the left, live contact preview on the right.
       ============================================================ */

    .hp-page {
        --hp-ube: #6b4fbb;
        --hp-ube-soft: rgba(107, 79, 187, .10);
        --hp-mango: #ffb627;
        --hp-mango-ink: #4a3300;

        --hp-surface: #ffffff;
        --hp-surface-2: #f6f4fb;
        --hp-line: #e4e0ee;
        --hp-text: #1e1b2e;
        --hp-muted: #6b6880;
        --hp-danger: #c92a3a;

        --hp-success: #16834a;
        --hp-success-soft: #e7f7ee;
        --hp-neutral: #687080;
        --hp-neutral-soft: #eceef2;

        --hp-tone: var(--hp-ube);
        --hp-shadow: 0 1px 2px rgba(30, 27, 46, .05),
                     0 8px 24px rgba(30, 27, 46, .06);

        font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
        color: var(--hp-text);
    }

    [data-theme="dark"] .hp-page {
        --hp-ube: #a48cf0;
        --hp-ube-soft: rgba(164, 140, 240, .14);
        --hp-surface: #1c1a2b;
        --hp-surface-2: #252238;
        --hp-line: #38344f;
        --hp-text: #f1eff9;
        --hp-muted: #b3afc9;
        --hp-danger: #ff8593;

        --hp-success: #86efac;
        --hp-success-soft: #193b2c;
        --hp-neutral: #cbd5e1;
        --hp-neutral-soft: #35394a;

        --hp-shadow: 0 1px 2px rgba(0, 0, 0, .3),
                     0 8px 24px rgba(0, 0, 0, .28);
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .hp-page {
            --hp-ube: #a48cf0;
            --hp-ube-soft: rgba(164, 140, 240, .14);
            --hp-surface: #1c1a2b;
            --hp-surface-2: #252238;
            --hp-line: #38344f;
            --hp-text: #f1eff9;
            --hp-muted: #b3afc9;
            --hp-danger: #ff8593;

            --hp-success: #86efac;
            --hp-success-soft: #193b2c;
            --hp-neutral: #cbd5e1;
            --hp-neutral-soft: #35394a;

            --hp-shadow: 0 1px 2px rgba(0, 0, 0, .3),
                         0 8px 24px rgba(0, 0, 0, .28);
        }
    }

    .hp-page *,
    .hp-page *::before,
    .hp-page *::after {
        box-sizing: border-box;
    }

    /* ---------- Header ---------- */

    .hp-head {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 28px;
    }

    .hp-crumb {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        font-size: 14px;
        color: var(--hp-muted);
    }

    .hp-crumb a {
        color: var(--hp-muted);
        text-decoration: none;
        font-weight: 600;
    }

    .hp-crumb a:hover {
        color: var(--hp-ube);
    }

    .hp-title {
        margin: 0;
        font-family: 'Unbounded', 'Figtree', sans-serif;
        font-weight: 600;
        font-size: clamp(1.5rem, 1.1rem + 1.6vw, 2.15rem);
        line-height: 1.15;
        letter-spacing: -.02em;
        color: var(--hp-text);
    }

    .hp-sub {
        margin: 8px 0 0;
        max-width: 52ch;
        color: var(--hp-muted);
        font-size: 15px;
        line-height: 1.55;
    }

    /* ---------- Buttons ---------- */

    .hp-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 0 22px;
        border-radius: 999px;
        border: 1.5px solid transparent;
        font: 700 15px/1 'Figtree', sans-serif;
        text-decoration: none;
        cursor: pointer;
        transition: background-color .15s,
                    border-color .15s,
                    color .15s,
                    transform .15s;
    }

    .hp-btn:focus-visible,
    .hp-chip input:focus-visible + .hp-chip-face,
    .hp-seg input:focus-visible + label {
        outline: 3px solid var(--hp-mango);
        outline-offset: 2px;
    }

    /* Secondary actions: Back and Cancel */

    .hp-btn-ghost {
        background: var(--hp-surface);
        border-color: var(--hp-line);
        color: var(--hp-text);
    }

    .hp-btn-ghost:hover {
        background: var(--hp-surface-2);
        border-color: var(--hp-neutral);
        color: var(--hp-text);
    }

    /* Primary action: Save */

    .hp-btn-main {
        background: var(--hp-ube);
        border-color: var(--hp-ube);
        color: #ffffff;
    }

    [data-theme="dark"] .hp-btn-main {
        color: #17132a;
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .hp-btn-main {
            color: #17132a;
        }
    }

    .hp-btn-main:hover {
        filter: brightness(1.08);
    }

    .hp-btn-main:active {
        transform: translateY(1px);
    }

    .hp-btn-block {
        width: 100%;
    }

    /* ---------- Error summary ---------- */

    .hp-errors {
        display: flex;
        gap: 14px;
        margin-bottom: 24px;
        padding: 16px 20px;
        border-radius: 16px;
        border: 1.5px solid var(--hp-danger);
        background: color-mix(in srgb, var(--hp-danger) 8%, var(--hp-surface));
    }

    .hp-errors i {
        font-size: 20px;
        color: var(--hp-danger);
        line-height: 1.4;
    }

    .hp-errors strong {
        display: block;
        margin-bottom: 4px;
    }

    .hp-errors ul {
        margin: 0;
        padding-left: 18px;
        color: var(--hp-text);
    }

    /* ---------- Layout ---------- */

    .hp-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 360px;
        gap: 28px;
        align-items: start;
    }

    .hp-main {
        display: grid;
        gap: 20px;
        min-width: 0;
    }

    .hp-aside {
        position: sticky;
        top: 88px;
        display: grid;
        gap: 16px;
    }

    /* ---------- Panels ---------- */

    .hp-panel {
        padding: 26px;
        border-radius: 24px;
        background: var(--hp-surface);
        border: 1px solid var(--hp-line);
        box-shadow: var(--hp-shadow);
    }

    .hp-panel-head {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 22px;
    }

    .hp-tile {
        flex: none;
        display: grid;
        place-items: center;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        background: var(--hp-ube-soft);
        color: var(--hp-ube);
        font-size: 19px;
    }

    .hp-panel-title {
        margin: 0;
        font-family: 'Unbounded', 'Figtree', sans-serif;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: -.01em;
        color: var(--hp-text);
    }

    .hp-panel-note {
        margin: 2px 0 0;
        font-size: 13.5px;
        color: var(--hp-muted);
    }

    .hp-fields {
        display: grid;
        gap: 20px;
    }

    .hp-fields.two {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    /* ---------- Fields ---------- */

    .hp-label {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 8px;
        margin-bottom: 8px;
        font-size: 14px;
        font-weight: 700;
        color: var(--hp-text);
    }

    .hp-req {
        color: var(--hp-danger);
        margin-left: 2px;
    }

    .hp-opt {
        font-size: 12.5px;
        font-weight: 500;
        color: var(--hp-muted);
    }

    .hp-input {
        display: block;
        width: 100%;
        min-height: 50px;
        padding: 0 16px;
        border-radius: 14px;
        border: 1.5px solid var(--hp-line);
        background: var(--hp-surface-2);
        color: var(--hp-text);
        font: 500 15.5px/1.4 'Figtree', sans-serif;
        transition: border-color .15s,
                    background-color .15s,
                    box-shadow .15s;
    }

    .hp-input::placeholder {
        color: var(--hp-muted);
        opacity: .75;
    }

    .hp-input:hover {
        border-color: color-mix(in srgb, var(--hp-ube) 45%, var(--hp-line));
    }

    .hp-input:focus {
        outline: none;
        background: var(--hp-surface);
        border-color: var(--hp-ube);
        box-shadow: 0 0 0 4px var(--hp-ube-soft);
    }

    textarea.hp-input {
        min-height: 130px;
        padding: 14px 16px;
        resize: vertical;
    }

    .hp-input.is-invalid {
        border-color: var(--hp-danger);
    }

    .hp-input-icon {
        position: relative;
    }

    .hp-input-icon > i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--hp-muted);
        font-size: 17px;
        pointer-events: none;
    }

    .hp-input-icon .hp-input {
        padding-left: 46px;
    }

    .hp-input-icon:focus-within > i {
        color: var(--hp-ube);
    }

    .hp-phone {
        min-height: 60px;
        font-family: 'Unbounded', 'Figtree', sans-serif;
        font-size: 18px;
        font-weight: 500;
        letter-spacing: .01em;
    }

    .hp-help {
        margin: 7px 0 0;
        font-size: 13px;
        line-height: 1.5;
        color: var(--hp-muted);
    }

    .hp-error {
        margin: 7px 0 0;
        font-size: 13.5px;
        font-weight: 600;
        color: var(--hp-danger);
    }

    .hp-counter {
        margin-top: 6px;
        text-align: right;
        font-size: 12.5px;
        color: var(--hp-muted);
    }

    /* ---------- Category chips ---------- */

    .hp-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .hp-chip {
        position: relative;
    }

    .hp-chip input {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        margin: 0;
    }

    .hp-chip-face {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        min-height: 46px;
        padding: 0 18px 0 14px;
        border-radius: 999px;
        border: 1.5px solid var(--hp-line);
        background: var(--hp-surface-2);
        color: var(--hp-text);
        font-size: 14.5px;
        font-weight: 600;
        transition: background-color .15s,
                    border-color .15s,
                    color .15s;
    }

    .hp-chip-face i {
        font-size: 17px;
        color: var(--hp-muted);
    }

    .hp-chip:hover .hp-chip-face {
        border-color: var(--hp-ube);
    }

    .hp-chip input:checked + .hp-chip-face {
        background: var(--hp-ube);
        border-color: var(--hp-ube);
        color: #ffffff;
    }

    .hp-chip input:checked + .hp-chip-face i {
        color: inherit;
    }

    [data-theme="dark"] .hp-chip input:checked + .hp-chip-face {
        color: #17132a;
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .hp-chip input:checked + .hp-chip-face {
            color: #17132a;
        }
    }

    /* ---------- Segmented status ---------- */

    .hp-seg {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(0, 1fr));
        gap: 4px;
        padding: 4px;
        border-radius: 999px;
        background: var(--hp-surface-2);
        border: 1.5px solid var(--hp-line);
    }

    .hp-seg input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .hp-seg label {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 42px;
        margin: 0;
        border-radius: 999px;
        font-size: 14.5px;
        font-weight: 700;
        color: var(--hp-muted);
        cursor: pointer;
        transition: background-color .15s, color .15s;
    }

    .hp-seg input:checked + label {
        background: var(--hp-surface);
        color: var(--hp-text);
        box-shadow: 0 1px 4px rgba(30, 27, 46, .18);
    }

    /* Green means Active */

    .hp-seg input[value="active"]:checked + label {
        background: var(--hp-success-soft);
        color: var(--hp-success);
    }

    .hp-seg input[value="active"]:checked + label .hp-dot {
        background: var(--hp-success);
    }

    /* Gray means Inactive */

    .hp-seg input[value="inactive"]:checked + label {
        background: var(--hp-neutral-soft);
        color: var(--hp-neutral);
    }

    .hp-seg input[value="inactive"]:checked + label .hp-dot {
        background: var(--hp-neutral);
    }

    .hp-dot {
        width: 9px;
        height: 9px;
        border-radius: 50%;
        background: var(--hp-muted);
        flex: none;
    }

    /* ---------- Live contact preview ---------- */

    .hp-card {
        overflow: hidden;
        border-radius: 28px;
        background: var(--hp-surface);
        border: 1px solid var(--hp-line);
        box-shadow: var(--hp-shadow);
    }

    .hp-card-top {
        position: relative;
        padding: 22px 24px 26px;
        background: var(--hp-tone);
        color: #ffffff;
        transition: background-color .25s;
    }

    .hp-card[data-tone="police"] {
        --hp-tone: #2f5fd0;
    }

    .hp-card[data-tone="fire"] {
        --hp-tone: #d63b3b;
    }

    .hp-card[data-tone="medical"] {
        --hp-tone: #0f8f7d;
    }

    .hp-card[data-tone="disaster"] {
        --hp-tone: #d98200;
    }

    .hp-card[data-tone="barangay"] {
        --hp-tone: #6b4fbb;
    }

    .hp-card-tag {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 13px 6px 10px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .2);
        font-size: 13px;
        font-weight: 700;
    }

    .hp-card-name {
        margin: 16px 0 0;
        font-family: 'Unbounded', 'Figtree', sans-serif;
        font-size: 19px;
        font-weight: 600;
        line-height: 1.3;
        letter-spacing: -.01em;
        overflow-wrap: anywhere;
        color: #ffffff;
    }

    .hp-card-name.is-empty {
        opacity: .6;
    }

    .hp-card-num {
        margin: 14px 0 0;
        font-family: 'Unbounded', 'Figtree', sans-serif;
        font-size: 26px;
        font-weight: 600;
        letter-spacing: .01em;
        overflow-wrap: anywhere;
        color: #ffffff;
    }

    .hp-card-num.is-empty {
        opacity: .55;
        font-size: 20px;
    }

    .hp-card-body {
        padding: 18px 24px 22px;
        display: grid;
        gap: 14px;
    }

    .hp-row {
        display: flex;
        gap: 12px;
        font-size: 14px;
        line-height: 1.5;
        color: var(--hp-text);
    }

    .hp-row i {
        flex: none;
        margin-top: 2px;
        color: var(--hp-muted);
    }

    .hp-row span {
        overflow-wrap: anywhere;
    }

    .hp-row.is-empty span {
        color: var(--hp-muted);
    }

    .hp-card-state {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 700;
        color: var(--hp-muted);
    }

    .hp-card-state .hp-dot {
        background: #1fa971;
    }

    .hp-card[data-status="inactive"] .hp-card-state .hp-dot {
        background: var(--hp-neutral);
        animation: none;
    }

    .hp-card[data-status="inactive"] .hp-card-state {
        color: var(--hp-neutral);
    }

    .hp-card[data-status="active"] .hp-card-state .hp-dot {
        animation: hp-pulse 2.4s ease-out infinite;
    }

    @keyframes hp-pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(31, 169, 113, .55);
        }

        70% {
            box-shadow: 0 0 0 8px rgba(31, 169, 113, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(31, 169, 113, 0);
        }
    }

    .hp-preview-label {
        margin: 0 0 10px 6px;
        font-size: 13px;
        font-weight: 700;
        color: var(--hp-muted);
    }

    /* ---------- Action panel ---------- */

    .hp-actions {
        padding: 20px;
        border-radius: 24px;
        background: var(--hp-surface);
        border: 1px solid var(--hp-line);
        box-shadow: var(--hp-shadow);
        display: grid;
        gap: 16px;
    }

    .hp-actions .hp-label {
        margin-bottom: 0;
    }

    .hp-actions .hp-help {
        margin-top: -8px;
    }

    .hp-action-btns {
        display: grid;
        gap: 10px;
    }

    /* ---------- Responsive ---------- */

    @media (max-width: 991.98px) {
        .hp-grid {
            grid-template-columns: 1fr;
        }

        .hp-aside {
            position: static;
        }
    }

    @media (max-width: 575.98px) {
        .hp-panel {
            padding: 20px;
            border-radius: 20px;
        }

        .hp-fields.two {
            grid-template-columns: 1fr;
        }

        .hp-head .hp-btn {
            width: 100%;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .hp-page * {
            animation: none !important;
            transition: none !important;
        }
    }
</style>
@endpush


@section('content')

@php
    // Pick an icon + colour tone for each category by keyword.
    $toneFor = function (string $label): array {
        $l = strtolower($label);

        return match (true) {
            str_contains($l, 'police') ||
            str_contains($l, 'peace') ||
            str_contains($l, 'pnp') ||
            str_contains($l, 'tanod')
                => ['police', 'bi-shield-check'],

            str_contains($l, 'fire') ||
            str_contains($l, 'bfp')
                => ['fire', 'bi-fire'],

            str_contains($l, 'medical') ||
            str_contains($l, 'health') ||
            str_contains($l, 'hospital') ||
            str_contains($l, 'ambulance') ||
            str_contains($l, 'rescue')
                => ['medical', 'bi-heart-pulse'],

            str_contains($l, 'disaster') ||
            str_contains($l, 'drrm') ||
            str_contains($l, 'calamity') ||
            str_contains($l, 'flood')
                => ['disaster', 'bi-cloud-lightning-rain'],

            str_contains($l, 'barangay') ||
            str_contains($l, 'municipal') ||
            str_contains($l, 'government') ||
            str_contains($l, 'lgu')
                => ['barangay', 'bi-bank2'],

            default => ['barangay', 'bi-telephone-fill'],
        };
    };
@endphp

<div class="container-fluid py-4 hp-page">

    {{-- ===== Header ===== --}}
    <header class="hp-head">
        <div>
            <nav class="hp-crumb" aria-label="Breadcrumb">
                <a href="{{ route('emergency-hotlines.index') }}">Emergency hotlines</a>
                <i class="bi bi-chevron-right" aria-hidden="true"></i>
                <span>New hotline</span>
            </nav>

            <h1 class="hp-title">Add emergency hotline</h1>

            <p class="hp-sub">
                Barangay staff will see this contact when they need help fast.
                Fill in the essentials and check the card on the right.
            </p>
        </div>

        <a href="{{ route('emergency-hotlines.index') }}" class="hp-btn hp-btn-ghost">
            <i class="bi bi-arrow-left" aria-hidden="true"></i>
            Back to hotlines
        </a>
    </header>


    {{-- ===== Errors ===== --}}
    @if ($errors->any())
        <div class="hp-errors" role="alert">
            <i class="bi bi-exclamation-octagon-fill" aria-hidden="true"></i>

            <div>
                <strong>Fix these before saving:</strong>

                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif


    <form
        method="POST"
        action="{{ route('emergency-hotlines.store') }}"
        class="hp-grid"
        id="hotlineForm"
    >
        @csrf

        {{-- =====================================================
             MAIN COLUMN
        ====================================================== --}}
        <div class="hp-main">

            {{-- ---------- Who ---------- --}}
            <section class="hp-panel">
                <div class="hp-panel-head">
                    <span class="hp-tile">
                        <i class="bi bi-building" aria-hidden="true"></i>
                    </span>

                    <div>
                        <h2 class="hp-panel-title">Who to call</h2>
                        <p class="hp-panel-note">
                            The office or responder and what kind of help they give.
                        </p>
                    </div>
                </div>

                <div class="hp-fields">

                    {{-- Name --}}
                    <div>
                        <label for="name" class="hp-label">
                            <span>
                                Hotline or organization name
                                <span class="hp-req" aria-hidden="true">*</span>
                            </span>
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="hp-input @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="Bani Municipal Police Station"
                            maxlength="150"
                            required
                            autofocus
                        >

                        @error('name')
                            <p class="hp-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Category --}}
                    <div>
                        <div class="hp-label" id="categoryLabel">
                            <span>
                                Category
                                <span class="hp-req" aria-hidden="true">*</span>
                            </span>
                        </div>

                        <div class="hp-chips" role="radiogroup" aria-labelledby="categoryLabel">
                            @foreach ($categories as $categoryOption)
                                @php
                                    [$tone, $icon] = $toneFor((string) $categoryOption);
                                @endphp

                                <label class="hp-chip">
                                    <input
                                        type="radio"
                                        name="category"
                                        value="{{ $categoryOption }}"
                                        data-tone="{{ $tone }}"
                                        data-icon="{{ $icon }}"
                                        @checked(old('category') === $categoryOption)
                                        @if ($loop->first) required @endif
                                    >

                                    <span class="hp-chip-face">
                                        <i class="bi {{ $icon }}" aria-hidden="true"></i>
                                        {{ $categoryOption }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @error('category')
                            <p class="hp-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Contact person --}}
                    <div>
                        <label for="contact_person" class="hp-label">
                            <span>Contact person</span>
                            <span class="hp-opt">Optional</span>
                        </label>

                        <div class="hp-input-icon">
                            <i class="bi bi-person" aria-hidden="true"></i>

                            <input
                                type="text"
                                id="contact_person"
                                name="contact_person"
                                class="hp-input @error('contact_person') is-invalid @enderror"
                                value="{{ old('contact_person') }}"
                                placeholder="Officer Juan Dela Cruz"
                                maxlength="150"
                            >
                        </div>

                        <p class="hp-help">
                            The person residents can ask for when they call.
                        </p>

                        @error('contact_person')
                            <p class="hp-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </section>


            {{-- ---------- Numbers ---------- --}}
            <section class="hp-panel">
                <div class="hp-panel-head">
                    <span class="hp-tile">
                        <i class="bi bi-telephone-outbound" aria-hidden="true"></i>
                    </span>

                    <div>
                        <h2 class="hp-panel-title">Phone numbers</h2>
                        <p class="hp-panel-note">
                            Double-check the digits. Someone may dial this in an emergency.
                        </p>
                    </div>
                </div>

                <div class="hp-fields">

                    {{-- Primary --}}
                    <div>
                        <label for="phone_number" class="hp-label">
                            <span>
                                Primary number
                                <span class="hp-req" aria-hidden="true">*</span>
                            </span>
                        </label>

                        <input
                            type="tel"
                            inputmode="tel"
                            id="phone_number"
                            name="phone_number"
                            class="hp-input hp-phone @error('phone_number') is-invalid @enderror"
                            value="{{ old('phone_number') }}"
                            placeholder="0912 345 6789"
                            maxlength="50"
                            required
                        >

                        <p class="hp-help">
                            This is the number shown to residents.
                        </p>

                        @error('phone_number')
                            <p class="hp-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Alternate --}}
                    <div>
                        <label for="alternate_number" class="hp-label">
                            <span>Backup number</span>
                            <span class="hp-opt">Optional</span>
                        </label>

                        <div class="hp-input-icon">
                            <i class="bi bi-telephone" aria-hidden="true"></i>

                            <input
                                type="tel"
                                inputmode="tel"
                                id="alternate_number"
                                name="alternate_number"
                                class="hp-input @error('alternate_number') is-invalid @enderror"
                                value="{{ old('alternate_number') }}"
                                placeholder="Landline or second mobile"
                                maxlength="50"
                            >
                        </div>

                        <p class="hp-help">
                            Used when the primary number is busy or unreachable.
                        </p>

                        @error('alternate_number')
                            <p class="hp-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </section>


            {{-- ---------- Where & when ---------- --}}
            <section class="hp-panel">
                <div class="hp-panel-head">
                    <span class="hp-tile">
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                    </span>

                    <div>
                        <h2 class="hp-panel-title">Location and instructions</h2>
                        <p class="hp-panel-note">
                            Where to find them and when residents should call.
                        </p>
                    </div>
                </div>

                <div class="hp-fields">

                    {{-- Address --}}
                    <div>
                        <label for="address" class="hp-label">
                            <span>Office or station address</span>
                            <span class="hp-opt">Optional</span>
                        </label>

                        <div class="hp-input-icon">
                            <i class="bi bi-pin-map" aria-hidden="true"></i>

                            <input
                                type="text"
                                id="address"
                                name="address"
                                class="hp-input @error('address') is-invalid @enderror"
                                value="{{ old('address') }}"
                                placeholder="Barangay Poblacion, Bani, Pangasinan"
                                maxlength="255"
                            >
                        </div>

                        @error('address')
                            <p class="hp-error">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Description --}}
                    <div>
                        <label for="description" class="hp-label">
                            <span>When to call</span>
                            <span class="hp-opt">Optional</span>
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            class="hp-input @error('description') is-invalid @enderror"
                            placeholder="Call for police help, theft, or any urgent security concern."
                        >{{ old('description') }}</textarea>

                        <div class="hp-counter">
                            <span id="descCount">0</span> characters
                        </div>

                        @error('description')
                            <p class="hp-error">{{ $message }}</p>
                        @enderror
                    </div>

                </div>
            </section>

        </div>


        {{-- =====================================================
             SIDE COLUMN — LIVE PREVIEW + SAVE
        ====================================================== --}}
        <aside class="hp-aside">

            {{-- Live preview --}}
            <div>
                <p class="hp-preview-label">Live preview</p>

                <div class="hp-card" id="pvCard" data-tone="barangay" data-status="active">

                    <div class="hp-card-top">
                        <span class="hp-card-tag">
                            <i class="bi bi-telephone-fill" id="pvIcon" aria-hidden="true"></i>
                            <span id="pvCategory">Choose a category</span>
                        </span>

                        <h3 class="hp-card-name is-empty" id="pvName">
                            Hotline name
                        </h3>

                        <p class="hp-card-num is-empty" id="pvPhone">
                            Phone number
                        </p>
                    </div>

                    <div class="hp-card-body">

                        <div class="hp-row is-empty" id="pvPersonRow">
                            <i class="bi bi-person" aria-hidden="true"></i>
                            <span id="pvPerson">No contact person</span>
                        </div>

                        <div class="hp-row is-empty" id="pvAddressRow">
                            <i class="bi bi-pin-map" aria-hidden="true"></i>
                            <span id="pvAddress">No address yet</span>
                        </div>

                        <div class="hp-row is-empty" id="pvAltRow">
                            <i class="bi bi-telephone" aria-hidden="true"></i>
                            <span id="pvAlt">No backup number</span>
                        </div>

                        {{-- No Call Now button: this is a contact-information preview only. --}}

                        <div class="hp-card-state">
                            <span class="hp-dot"></span>
                            <span id="pvStatus">Available to residents</span>
                        </div>

                    </div>
                </div>
            </div>


            {{-- Availability + actions --}}
            <div class="hp-actions">

                <div>
                    <div class="hp-label" id="statusLabel" style="margin-bottom:10px;">
                        <span>
                            Availability
                            <span class="hp-req" aria-hidden="true">*</span>
                        </span>
                    </div>

                    <div class="hp-seg" role="radiogroup" aria-labelledby="statusLabel">
                        @foreach ($statuses as $statusOption)
                            <div style="position:relative; display:flex;">
                                <input
                                    type="radio"
                                    name="status"
                                    id="status_{{ $statusOption }}"
                                    value="{{ $statusOption }}"
                                    @checked(old('status', 'active') === $statusOption)
                                    required
                                >

                                <label for="status_{{ $statusOption }}" style="flex:1;">
                                    <span class="hp-dot"></span>
                                    {{ ucfirst($statusOption) }}
                                </label>
                            </div>
                        @endforeach
                    </div>

                    @error('status')
                        <p class="hp-error">{{ $message }}</p>
                    @enderror
                </div>

                <p class="hp-help">
                    Only active hotlines are shown to residents.
                </p>

                <div class="hp-action-btns">

                    <button type="submit" class="hp-btn hp-btn-main hp-btn-block">
                        <i class="bi bi-check2-circle" aria-hidden="true"></i>
                        Save hotline
                    </button>

                    <a
                        href="{{ route('emergency-hotlines.index') }}"
                        class="hp-btn hp-btn-ghost hp-btn-block"
                    >
                        Cancel
                    </a>

                </div>
            </div>

        </aside>

    </form>
</div>

@endsection


@push('scripts')
<script>
    (function () {
        'use strict';

        const $ = (id) => document.getElementById(id);

        const form = $('hotlineForm');
        const card = $('pvCard');

        if (!form || !card) {
            return;
        }

        const inputs = {
            name: $('name'),
            phone: $('phone_number'),
            alt: $('alternate_number'),
            person: $('contact_person'),
            address: $('address'),
            desc: $('description'),
        };

        function setText(textEl, rowOrEl, value, fallback) {
            const has = value.trim() !== '';

            textEl.textContent = has ? value.trim() : fallback;
            rowOrEl.classList.toggle('is-empty', !has);
        }

        function render() {
            // Hotline name
            const name = inputs.name.value.trim();

            $('pvName').textContent = name || 'Hotline name';
            $('pvName').classList.toggle('is-empty', !name);

            // Primary number
            const phone = inputs.phone.value.trim();

            $('pvPhone').textContent = phone || 'Phone number';
            $('pvPhone').classList.toggle('is-empty', !phone);

            // Contact person
            setText(
                $('pvPerson'),
                $('pvPersonRow'),
                inputs.person.value,
                'No contact person'
            );

            // Address
            setText(
                $('pvAddress'),
                $('pvAddressRow'),
                inputs.address.value,
                'No address yet'
            );

            // Backup number
            const alt = inputs.alt.value.trim();

            $('pvAlt').textContent = alt
                ? 'Backup: ' + alt
                : 'No backup number';

            $('pvAltRow').classList.toggle('is-empty', !alt);

            // Description counter
            $('descCount').textContent = inputs.desc.value.length;

            // Category
            const checkedCat = document.querySelector(
                'input[name="category"]:checked'
            );

            if (checkedCat) {
                card.dataset.tone = checkedCat.dataset.tone;

                $('pvIcon').className =
                    'bi ' + checkedCat.dataset.icon;

                $('pvCategory').textContent = checkedCat.value;
            } else {
                card.dataset.tone = 'barangay';

                $('pvIcon').className = 'bi bi-telephone-fill';

                $('pvCategory').textContent = 'Choose a category';
            }

            // Availability
            const checkedStatus = document.querySelector(
                'input[name="status"]:checked'
            );

            const isActive = !checkedStatus ||
                checkedStatus.value === 'active';

            card.dataset.status = isActive ? 'active' : 'inactive';

            $('pvStatus').textContent = isActive
                ? 'Available to residents'
                : 'Hidden from residents';
        }

        form.addEventListener('input', render);
        form.addEventListener('change', render);

        render();
    })();
</script>
@endpush
