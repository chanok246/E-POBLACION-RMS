@extends('layouts.app')

@section('title', 'New Document Request | e-Poblacion')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       NEW DOCUMENT REQUEST
       ========================================================= */

    .docs {
        --ink: #14213d;
        --muted: #5f6b7d;
        --paper: #ffffff;
        --wash: #eef2f7;
        --line: #dbe2ec;
        --brand: #1f4e9c;
        --brand-soft: #e4edfb;
        --sun: #f2b705;
        --hover: #f5f8fc;
        --pending: #b26a00;
        --pending-bg: #fff1d6;
        --bad: #c92a2a;
        --bad-bg: #fde3e3;
        --good: #198754;
        --good-bg: #e7f6ed;

        font-family: 'Public Sans', system-ui, sans-serif;
        color: var(--ink);
    }

    html[data-theme="dark"] .docs {
        --ink: #eef2f8;
        --muted: #9aa7bb;
        --paper: #1b2230;
        --wash: #141a25;
        --line: #303a4d;
        --brand: #7aa7f0;
        --brand-soft: #23324f;
        --hover: #212a3b;
        --pending: #f5b942;
        --pending-bg: #3a2c10;
        --bad: #ff8f8f;
        --bad-bg: #401a1d;
        --good: #75d39a;
        --good-bg: #153424;
    }

    .docs * {
        box-sizing: border-box;
    }

    .docs .display {
        font-family: 'Bricolage Grotesque', 'Public Sans', sans-serif;
        letter-spacing: -.02em;
    }

    .docs a:focus-visible,
    .docs button:focus-visible,
    .docs input:focus-visible,
    .docs textarea:focus-visible,
    .docs select:focus-visible,
    .docs .type input:focus-visible + .box {
        outline: 3px solid var(--sun);
        outline-offset: 2px;
    }

    .back {
        display: inline-flex;
        gap: .4rem;
        align-items: center;
        color: var(--brand);
        text-decoration: none;
        font-weight: 600;
        font-size: .92rem;
    }

    .back:hover {
        text-decoration: underline;
    }

    .docs-head {
        margin: .9rem 0 1.5rem;
    }

    .docs-head h1 {
        font-size: clamp(1.7rem, 3.2vw, 2.3rem);
        font-weight: 800;
        margin: 0;
        line-height: 1.1;
    }

    .docs-head p {
        margin: .35rem 0 0;
        color: var(--muted);
    }

    .note {
        display: flex;
        gap: .75rem;
        padding: .85rem 1rem;
        border-radius: .9rem;
        margin-bottom: 1rem;
        border: 1px solid var(--bad);
        background: var(--bad-bg);
        color: var(--bad);
    }

    .note ul {
        margin: .35rem 0 0;
        padding-left: 1.1rem;
    }

    .layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 340px;
        gap: 1.5rem;
        align-items: start;
    }

    @media (max-width: 991.98px) {
        .layout {
            grid-template-columns: 1fr;
        }
    }

    /* ---------- Steps ---------- */

    .step {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 1.1rem;
        padding: 1.25rem 1.35rem 1.4rem;
        margin-bottom: 1rem;
    }

    .step-title {
        display: flex;
        align-items: center;
        gap: .7rem;
        margin-bottom: .2rem;
    }

    .step-title .n {
        width: 1.9rem;
        height: 1.9rem;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: var(--brand);
        color: #fff;
        font-weight: 800;
        font-size: .9rem;
        flex: none;
    }

    html[data-theme="dark"] .step-title .n {
        color: #0f1620;
    }

    .step-title h2 {
        font-size: 1.15rem;
        font-weight: 800;
        margin: 0;
    }

    .step-hint {
        color: var(--muted);
        font-size: .9rem;
        margin: 0 0 1rem 2.6rem;
    }

    .req-mark {
        color: var(--bad);
    }

    .err {
        color: var(--bad);
        font-size: .88rem;
        margin-top: .5rem;
    }

    /* ---------- Resident combobox ---------- */

    .combo {
        position: relative;
    }

    .combo .bi-search {
        position: absolute;
        left: 1rem;
        top: 1.05rem;
        color: var(--muted);
        pointer-events: none;
    }

    .combo input.q {
        width: 100%;
        padding: .85rem 2.6rem .85rem 2.6rem;
        border-radius: .9rem;
        font-size: 1rem;
        border: 1.5px solid var(--line);
        background: var(--paper);
        color: var(--ink);
    }

    .combo input.q::placeholder {
        color: var(--muted);
    }

    .combo input.q:focus {
        border-color: var(--brand);
        outline: none;
        box-shadow: 0 0 0 4px var(--brand-soft);
    }

    .combo.invalid input.q {
        border-color: var(--bad);
    }

    .list {
        position: absolute;
        z-index: 30;
        left: 0;
        right: 0;
        top: calc(100% + .35rem);
        margin: 0;
        padding: .3rem;
        list-style: none;
        max-height: 300px;
        overflow-y: auto;
        border-radius: .9rem;
        background: var(--paper);
        border: 1px solid var(--line);
        box-shadow: 0 12px 30px rgba(10, 20, 40, .18);
    }

    .list[hidden] {
        display: none;
    }

    .opt {
        display: flex;
        align-items: center;
        gap: .75rem;
        padding: .55rem .65rem;
        border-radius: .65rem;
        cursor: pointer;
    }

    .opt[aria-selected="true"],
    .opt:hover {
        background: var(--brand-soft);
    }

    .opt b {
        display: block;
        font-weight: 600;
    }

    .opt small {
        color: var(--muted);
    }

    .opt mark {
        background: var(--sun);
        color: #14213d;
        border-radius: 3px;
        padding: 0 1px;
    }

    .avatar {
        width: 2.4rem;
        height: 2.4rem;
        border-radius: 50%;
        display: grid;
        place-items: center;
        flex: none;
        font-weight: 700;
        font-size: .85rem;
        background: var(--brand-soft);
        color: var(--brand);
    }

    .list-empty {
        padding: 1rem;
        text-align: center;
        color: var(--muted);
    }

    .list-foot {
        padding: .45rem .65rem;
        color: var(--muted);
        font-size: .8rem;
        text-align: center;
    }

    .picked {
        display: flex;
        align-items: center;
        gap: .8rem;
        padding: .75rem .9rem;
        border-radius: .9rem;
        border: 1.5px solid var(--brand);
        background: var(--brand-soft);
    }

    .picked[hidden] {
        display: none;
    }

    .picked .grow {
        flex: 1;
        min-width: 0;
    }

    .picked b {
        display: block;
    }

    .picked small {
        color: var(--muted);
    }

    .link-btn {
        background: none;
        border: 0;
        color: var(--brand);
        font-weight: 600;
        padding: .25rem .5rem;
        border-radius: .5rem;
    }

    .link-btn:hover {
        text-decoration: underline;
    }

    /* ---------- Document type cards ---------- */

    .types {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .75rem;
    }

    @media (max-width: 575.98px) {
        .types {
            grid-template-columns: 1fr;
        }
    }

    .type {
        position: relative;
        display: block;
        cursor: pointer;
    }

    .type input {
        position: absolute;
        opacity: 0;
        inset: 0;
    }

    .type .box {
        display: flex;
        gap: .8rem;
        align-items: flex-start;
        height: 100%;
        padding: .9rem 1rem;
        border-radius: .9rem;
        border: 1.5px solid var(--line);
        background: var(--paper);
        transition: border-color .15s, background .15s;
    }

    .type:hover .box {
        border-color: var(--brand);
    }

    .type i.ic {
        font-size: 1.35rem;
        color: var(--brand);
    }

    .type b {
        display: block;
        font-weight: 600;
    }

    .type small {
        color: var(--muted);
    }

    .type input:checked + .box {
        border-color: var(--brand);
        background: var(--brand-soft);
        box-shadow: inset 0 0 0 1px var(--brand);
    }

    .type .tick {
        margin-left: auto;
        color: var(--brand);
        opacity: 0;
    }

    .type input:checked + .box .tick {
        opacity: 1;
    }

    /* ---------- Text fields ---------- */

    .field {
        margin-bottom: 1.15rem;
    }

    .field:last-child {
        margin-bottom: 0;
    }

    .field label {
        display: block;
        font-weight: 600;
        margin-bottom: .4rem;
    }

    .field label span {
        color: var(--muted);
        font-weight: 400;
    }

    .field textarea,
    .field input[type="text"],
    .field input[type="date"],
    .field input[type="number"],
    .field select {
        width: 100%;
        padding: .75rem .9rem;
        border-radius: .9rem;
        font: inherit;
        border: 1.5px solid var(--line);
        background: var(--paper);
        color: var(--ink);
    }

    .field textarea {
        resize: vertical;
    }

    .field textarea::placeholder,
    .field input::placeholder {
        color: var(--muted);
    }

    .field textarea:focus,
    .field input[type="text"]:focus,
    .field input[type="date"]:focus,
    .field input[type="number"]:focus,
    .field select:focus {
        border-color: var(--brand);
        outline: none;
        box-shadow: 0 0 0 4px var(--brand-soft);
    }

    .field textarea.bad,
    .field input.bad,
    .field select.bad {
        border-color: var(--bad);
    }

    /* ---------- Document-specific details ---------- */

    .document-details {
        display: none;
        margin: 0 0 1.15rem;
        padding: 1rem;
        border: 1px solid var(--line);
        border-radius: .95rem;
        background: var(--wash);
    }

    .document-details.show {
        display: block;
    }

    .document-details-head {
        display: flex;
        align-items: flex-start;
        gap: .7rem;
        margin-bottom: 1rem;
    }

    .document-details-head i {
        color: var(--brand);
        font-size: 1.15rem;
        margin-top: .1rem;
    }

    .document-details-head b {
        display: block;
        font-weight: 700;
        color: var(--ink);
    }

    .document-details-head small {
        display: block;
        margin-top: .15rem;
        color: var(--muted);
        line-height: 1.45;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    @media (max-width: 767.98px) {
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }

    .detail-field {
        margin: 0;
    }

    .detail-field.full {
        grid-column: 1 / -1;
    }

    .detail-field label {
        margin-bottom: .4rem;
    }

    .detail-field .helper {
        display: block;
        margin-top: .35rem;
        color: var(--muted);
        font-size: .8rem;
        line-height: 1.4;
    }

    .detail-field .required {
        color: var(--bad);
    }

    .detail-field input:disabled,
    .detail-field select:disabled {
        opacity: .6;
        cursor: not-allowed;
        background: var(--paper);
    }

    /* ---------- Solo Parent ---------- */

    .solo-parent-note {
        display: flex;
        gap: .7rem;
        align-items: flex-start;
        margin-bottom: 1rem;
        padding: .75rem .85rem;
        border-radius: .8rem;
        background: var(--brand-soft);
        color: var(--ink);
        font-size: .86rem;
        line-height: 1.45;
    }

    .solo-parent-note i {
        color: var(--brand);
        font-size: 1.1rem;
        margin-top: .1rem;
    }

    .solo-parent-note p {
        margin: 0;
    }

    .automatic-field {
        background: var(--wash) !important;
        cursor: default;
    }

    /* ---------- Solo Parent resident picker ---------- */

    .solo-picker {
        position: relative;
    }

    .solo-picker-search {
        position: relative;
    }

    .solo-picker-search .bi-search {
        position: absolute;
        left: 1rem;
        top: 1rem;
        color: var(--muted);
        pointer-events: none;
    }

    .solo-picker-search input {
        width: 100%;
        padding: .8rem 2.6rem .8rem 2.6rem;
        border-radius: .85rem;
        border: 1.5px solid var(--line);
        background: var(--paper);
        color: var(--ink);
        font: inherit;
    }

    .solo-picker-search input:focus {
        border-color: var(--brand);
        outline: none;
        box-shadow: 0 0 0 4px var(--brand-soft);
    }

    .solo-picker-search input::placeholder {
        color: var(--muted);
    }

    .solo-picker-list {
        position: absolute;
        z-index: 40;
        left: 0;
        right: 0;
        top: calc(100% + .35rem);
        margin: 0;
        padding: .3rem;
        list-style: none;
        max-height: 310px;
        overflow-y: auto;
        border-radius: .9rem;
        background: var(--paper);
        border: 1px solid var(--line);
        box-shadow: 0 14px 35px rgba(10, 20, 40, .18);
    }

    .solo-picker-list[hidden] {
        display: none;
    }

    .solo-result {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .65rem;
        border-radius: .7rem;
    }

    .solo-result:hover {
        background: var(--hover);
    }

    .solo-result .result-main {
        flex: 1;
        min-width: 0;
    }

    .solo-result b {
        display: block;
        font-weight: 600;
        word-break: break-word;
    }

    .solo-result small {
        display: block;
        color: var(--muted);
        margin-top: .1rem;
        line-height: 1.35;
    }

    .solo-result .result-status {
        color: var(--bad);
        font-size: .78rem;
        margin-top: .2rem;
    }

    .solo-add-btn {
        flex: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .3rem;
        border: 1px solid var(--brand);
        background: var(--brand);
        color: #fff;
        border-radius: .65rem;
        padding: .45rem .7rem;
        font-size: .82rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .solo-add-btn:hover {
        filter: brightness(.95);
    }

    .solo-add-btn:disabled {
        border-color: var(--line);
        background: var(--wash);
        color: var(--muted);
        cursor: not-allowed;
    }

    .solo-result-added {
        color: var(--good);
        font-size: .8rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .solo-search-empty {
        padding: 1rem;
        text-align: center;
        color: var(--muted);
        font-size: .85rem;
    }

    .solo-household-warning {
        display: none;
        margin-top: .55rem;
        padding: .65rem .75rem;
        border-radius: .7rem;
        background: var(--bad-bg);
        color: var(--bad);
        font-size: .8rem;
        line-height: 1.4;
    }

    .solo-household-warning.show {
        display: block;
    }

    /* ---------- Selected children ---------- */

    .selected-members {
        margin-top: 1rem;
    }

    .selected-members-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        margin-bottom: .6rem;
    }

    .selected-members-head strong {
        font-size: .9rem;
    }

    .selected-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.6rem;
        height: 1.6rem;
        padding: 0 .45rem;
        border-radius: 999px;
        background: var(--brand-soft);
        color: var(--brand);
        font-size: .78rem;
        font-weight: 700;
    }

    .selected-members-list {
        display: grid;
        gap: .6rem;
    }

    .selected-members-empty {
        padding: .9rem;
        border: 1px dashed var(--line);
        border-radius: .8rem;
        color: var(--muted);
        text-align: center;
        font-size: .84rem;
        background: var(--paper);
    }

    .selected-member {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .7rem .75rem;
        border: 1px solid var(--line);
        border-radius: .8rem;
        background: var(--paper);
    }

    .selected-member .avatar {
        width: 2.25rem;
        height: 2.25rem;
    }

    .selected-member-main {
        flex: 1;
        min-width: 0;
    }

    .selected-member-name {
        display: block;
        font-weight: 700;
        word-break: break-word;
    }

    .selected-member-meta {
        display: flex;
        flex-wrap: wrap;
        gap: .35rem .8rem;
        margin-top: .15rem;
        color: var(--muted);
        font-size: .78rem;
    }

    .selected-member-meta span {
        display: inline-flex;
        align-items: center;
        gap: .25rem;
    }

    .remove-member {
        flex: none;
        border: 1px solid var(--line);
        background: transparent;
        color: var(--bad);
        border-radius: .55rem;
        width: 2rem;
        height: 2rem;
        display: grid;
        place-items: center;
    }

    .remove-member:hover {
        background: var(--bad-bg);
        border-color: var(--bad);
    }

    .selected-members-error {
        margin-top: .55rem;
        color: var(--bad);
        font-size: .84rem;
    }

    /* ---------- Purpose ---------- */

    .purpose-required-note {
        display: none;
        margin-top: .5rem;
        color: var(--muted);
        font-size: .82rem;
    }

    .purpose-required-note.show {
        display: block;
    }

    /* ---------- Summary ---------- */

    .side {
        position: sticky;
        top: 1rem;
    }

    @media (max-width: 991.98px) {
        .side {
            position: static;
        }
    }

    .summary {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 1.1rem;
        overflow: hidden;
        margin-bottom: 1rem;
    }

    .summary-head {
        padding: .9rem 1.25rem;
        border-bottom: 1px solid var(--line);
        font-weight: 800;
        font-size: 1.05rem;
    }

    .summary dl {
        margin: 0;
        padding: 1rem 1.25rem;
        display: grid;
        gap: .9rem;
    }

    .summary dt {
        color: var(--muted);
        font-size: .82rem;
        font-weight: 500;
    }

    .summary dd {
        margin: .1rem 0 0;
        font-weight: 600;
    }

    .summary dd.empty {
        color: var(--muted);
        font-weight: 400;
    }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .28rem .7rem;
        border-radius: 999px;
        font-size: .82rem;
        font-weight: 600;
        color: var(--pending);
        background: var(--pending-bg);
    }

    .pill::before {
        content: '';
        width: .5rem;
        height: .5rem;
        border-radius: 50%;
        background: var(--pending);
    }

    .summary-note {
        padding: 0 1.25rem 1rem;
        color: var(--muted);
        font-size: .85rem;
        margin: 0;
    }

    .actions {
        padding: 1rem 1.25rem;
        border-top: 1px solid var(--line);
        display: grid;
        gap: .6rem;
    }

    .btn-go {
        display: inline-flex;
        justify-content: center;
        align-items: center;
        gap: .5rem;
        border: 0;
        border-radius: 999px;
        padding: .8rem 1.2rem;
        font-weight: 700;
        background: var(--sun);
        color: #14213d;
        box-shadow: 0 2px 0 rgba(0,0,0,.15);
    }

    .btn-go:disabled {
        background: var(--wash);
        color: var(--muted);
        box-shadow: none;
        cursor: not-allowed;
    }

    .btn-cancel {
        text-align: center;
        padding: .6rem;
        border-radius: 999px;
        color: var(--muted);
        text-decoration: none;
        font-weight: 600;
    }

    .btn-cancel:hover {
        color: var(--ink);
        background: var(--wash);
    }

    .missing {
        color: var(--muted);
        font-size: .82rem;
        text-align: center;
        margin: 0;
    }

    .verify {
        display: flex;
        gap: .8rem;
        padding: 1rem 1.25rem;
        border-radius: 1.1rem;
        background: var(--wash);
        border: 1px dashed var(--line);
        font-size: .88rem;
        color: var(--muted);
    }

    .verify i {
        font-size: 1.3rem;
        color: var(--brand);
    }

    .verify b {
        display: block;
        color: var(--ink);
        margin-bottom: .25rem;
    }

    .verify p {
        margin: 0;
    }

    @media (prefers-reduced-motion: reduce) {
        .docs * {
            transition: none !important;
        }
    }
</style>
@endpush


@section('content')

@php
    /*
     * ============================================================
     * RESIDENT DATA FOR FRONT-END SEARCH
     * ============================================================
     *
     * We include sex and birth date because the Solo Parent
     * resident picker now needs to display these automatically.
     */
    $residentData = $residents->map(fn ($r) => [
        'id'                  => $r->id,
        'name'                => (string) $r->full_name,
        'address'             => (string) ($r->address ?? ''),
        'sex'                 => (string) ($r->sex ?? ''),
        'birth_date'          => $r->birth_date
            ? $r->birth_date->format('Y-m-d')
            : '',
        'birth_date_display'  => $r->birth_date
            ? $r->birth_date->format('F j, Y')
            : '',
    ])->values();

    $types = [
        'Barangay Clearance' => [
            'bi-shield-check',
            'Proof of good standing in the barangay',
        ],

        'Certificate of Residency' => [
            'bi-house-door',
            'Confirms the resident’s place of residence',
        ],

        'Certificate of Indigency' => [
            'bi-heart',
            'For financial assistance, scholarships, or fee waivers',
        ],

        'Certificate of Late Register' => [
            'bi-file-earmark-text',
            'For certification related to late registration',
        ],

        'Cutting Trees' => [
            'bi-tree',
            'Certificate related to tree cutting authorization',
        ],

        'First Time Job Seeker' => [
            'bi-briefcase',
            'For qualified first-time job seekers',
        ],

        'Oath of Undertaking' => [
            'bi-pen',
            'Official barangay oath or undertaking document',
        ],

        'Solo Parent' => [
            'bi-person-heart',
            'Certificate related to solo parent status',
        ],
    ];
@endphp


<div class="docs">

    <div class="container-fluid py-4">

        <a href="{{ route('documents.index') }}" class="back">
            <i class="bi bi-arrow-left"></i>
            Back to Barangay Documents
        </a>


        <div class="docs-head">

            <h1 class="display">
                New document request
            </h1>

            <p>
                File a request for a resident who is already registered.
            </p>

        </div>


        @if ($errors->any())

            <div class="note" role="alert">

                <i class="bi bi-exclamation-triangle-fill"></i>

                <div>

                    <strong>
                        Please fix the following:
                    </strong>

                    <ul>

                        @foreach ($errors->all() as $error)

                            <li>
                                {{ $error }}
                            </li>

                        @endforeach

                    </ul>

                </div>

            </div>

        @endif


        <form
            method="POST"
            action="{{ route('documents.store') }}"
            id="requestForm"
            novalidate
        >

            @csrf


            <div class="layout">


                {{-- =====================================================
                     LEFT
                     ===================================================== --}}

                <div>


                    {{-- =================================================
                         STEP 1
                         ================================================= --}}

                    <section class="step">

                        <div class="step-title">

                            <span class="n">
                                1
                            </span>

                            <h2 class="display">
                                Who is this for?
                                <span class="req-mark">*</span>
                            </h2>

                        </div>


                        <p class="step-hint">
                            Type a name or address. Matches appear as you type.
                        </p>


                        <div
                            class="combo @error('resident_id') invalid @enderror"
                            id="combo"
                        >

                            <input
                                type="hidden"
                                name="resident_id"
                                id="resident_id"
                                value="{{ old('resident_id') }}"
                            >


                            <div
                                class="picked"
                                id="picked"
                                hidden
                            >

                                <span
                                    class="avatar"
                                    id="pickedAvatar"
                                    aria-hidden="true"
                                ></span>


                                <div class="grow">

                                    <b id="pickedName"></b>

                                    <small id="pickedAddr"></small>

                                </div>


                                <button
                                    type="button"
                                    class="link-btn"
                                    id="change"
                                >
                                    Change
                                </button>

                            </div>


                            <div id="searchWrap">

                                <i class="bi bi-search"></i>


                                <label
                                    for="resident_search"
                                    class="visually-hidden"
                                >
                                    Search residents
                                </label>


                                <input
                                    type="text"
                                    class="q"
                                    id="resident_search"
                                    autocomplete="off"
                                    placeholder="Start typing a resident's name…"
                                    role="combobox"
                                    aria-expanded="false"
                                    aria-controls="resident_list"
                                    aria-autocomplete="list"
                                >


                                <ul
                                    class="list"
                                    id="resident_list"
                                    role="listbox"
                                    hidden
                                ></ul>

                            </div>

                        </div>


                        @error('resident_id')

                            <div class="err">
                                {{ $message }}
                            </div>

                        @enderror

                    </section>


                    {{-- =================================================
                         STEP 2
                         ================================================= --}}

                    <section class="step">

                        <div class="step-title">

                            <span class="n">
                                2
                            </span>

                            <h2 class="display">
                                Which document?
                                <span class="req-mark">*</span>
                            </h2>

                        </div>


                        <p class="step-hint">
                            Pick one.
                        </p>


                        <div
                            class="types"
                            role="radiogroup"
                            aria-label="Document type"
                        >

                            @foreach ($types as $value => [$icon, $desc])

                                <label class="type">

                                    <input
                                        type="radio"
                                        name="document_type"
                                        value="{{ $value }}"
                                        {{ old('document_type') === $value ? 'checked' : '' }}
                                    >


                                    <span class="box">

                                        <i class="bi {{ $icon }} ic"></i>


                                        <span>

                                            <b>
                                                {{ $value }}
                                            </b>

                                            <small>
                                                {{ $desc }}
                                            </small>

                                        </span>


                                        <i class="bi bi-check-circle-fill tick"></i>

                                    </span>

                                </label>

                            @endforeach

                        </div>


                        @error('document_type')

                            <div class="err">
                                {{ $message }}
                            </div>

                        @enderror

                    </section>


                    {{-- =================================================
                         STEP 3
                         ================================================= --}}

                    <section class="step">

                        <div class="step-title">

                            <span class="n">
                                3
                            </span>

                            <h2 class="display">
                                Details
                            </h2>

                        </div>


                        <p
                            class="step-hint"
                            id="detailsHint"
                        >
                            Optional, but a clear purpose helps staff process the request faster.
                        </p>


                        {{-- =================================================
                             PURPOSE
                             ================================================= --}}

                        <div class="field">

                            <label for="purpose">

                                Purpose

                                <span id="purposeOptionalLabel">
                                    (optional)
                                </span>

                                <span
                                    id="purposeRequiredLabel"
                                    class="req-mark"
                                    style="display:none;"
                                >
                                    *
                                </span>

                            </label>


                            <textarea
                                name="purpose"
                                id="purpose"
                                rows="3"
                                maxlength="1000"
                                class="@error('purpose') bad @enderror"
                                placeholder="Why does the resident need this document?"
                            >{{ old('purpose') }}</textarea>


                            <div
                                class="purpose-required-note"
                                id="purposeRequiredNote"
                            >
                                Purpose requirements will be applied only when the selected official document template requires specific information.
                            </div>


                            <div
                                class="chips"
                                id="purposeChips"
                            >

                                <small>
                                    Quick fill:
                                </small>


                                @foreach ([
                                    'Employment',
                                    'Scholarship',
                                    'Travel',
                                    'ID application',
                                    'Business'
                                ] as $p)

                                    <button
                                        type="button"
                                        data-purpose="{{ $p }}"
                                    >
                                        {{ $p }}
                                    </button>

                                @endforeach

                            </div>


                            @error('purpose')

                                <div class="err">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>


                        {{-- =================================================
                             LATE REGISTER
                             ================================================= --}}

                        <div
                            class="document-details"
                            id="lateRegisterDetails"
                            hidden
                        >

                            <div class="document-details-head">

                                <i class="bi bi-file-earmark-text"></i>

                                <div>

                                    <b>
                                        Certificate of Late Register details
                                    </b>

                                    <small>
                                        Enter the information that belongs specifically to this resident's late registration record.
                                    </small>

                                </div>

                            </div>


                            <div class="detail-grid">


                                <div class="field detail-field full">

                                    <label for="detail_birthplace">

                                        Place of Birth

                                        <span class="required">*</span>

                                    </label>


                                    <input
                                        type="text"
                                        name="document_details[birthplace]"
                                        id="detail_birthplace"
                                        value="{{ old('document_details.birthplace') }}"
                                        maxlength="1000"
                                        placeholder="Example: Candon, Ilocos Sur"
                                        disabled
                                    >


                                    <small class="helper">
                                        Enter the resident's actual place of birth.
                                    </small>


                                    @error('document_details.birthplace')

                                        <div class="err">
                                            {{ $message }}
                                        </div>

                                    @enderror

                                </div>


                                <div class="field detail-field">

                                    <label for="detail_father_name">

                                        Father's Name

                                        <span class="required">*</span>

                                    </label>


                                    <input
                                        type="text"
                                        name="document_details[father_name]"
                                        id="detail_father_name"
                                        value="{{ old('document_details.father_name') }}"
                                        maxlength="1000"
                                        placeholder="Enter father's full name"
                                        disabled
                                    >


                                    <small class="helper">
                                        Use the name shown in the resident's supporting record.
                                    </small>


                                    @error('document_details.father_name')

                                        <div class="err">
                                            {{ $message }}
                                        </div>

                                    @enderror

                                </div>


                                <div class="field detail-field">

                                    <label for="detail_mother_name">

                                        Mother's Name

                                        <span class="required">*</span>

                                    </label>


                                    <input
                                        type="text"
                                        name="document_details[mother_name]"
                                        id="detail_mother_name"
                                        value="{{ old('document_details.mother_name') }}"
                                        maxlength="1000"
                                        placeholder="Enter mother's full name"
                                        disabled
                                    >


                                    <small class="helper">
                                        Use the name shown in the resident's supporting record.
                                    </small>


                                    @error('document_details.mother_name')

                                        <div class="err">
                                            {{ $message }}
                                        </div>

                                    @enderror

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                             CUTTING TREES
                             ================================================= --}}

                        <div class="document-details" id="cuttingTreesDetails" hidden>
                            <div class="document-details-head">
                                <i class="bi bi-tree-fill"></i>
                                <div>
                                    <b>Cutting Trees Permit details</b>
                                    <small>Enter the permit and tree information exactly as stated in the supporting permit. Location context fields are free-text for the Captain or Secretary.</small>
                                </div>
                            </div>

                            <div class="detail-grid">
                                <div class="field detail-field">
                                    <label for="detail_permit_to_cut_no">Permit to Cut No. <span class="required">*</span></label>
                                    <input type="text" name="document_details[permit_to_cut_no]" id="detail_permit_to_cut_no" value="{{ old('document_details.permit_to_cut_no') }}" maxlength="100" placeholder="Example: 003204">
                                    @error('document_details.permit_to_cut_no') <div class="err">{{ $message }}</div> @enderror
                                </div>

                                <div class="field detail-field">
                                    <label for="detail_permit_to_cut_date">Permit to Cut Date <span class="required">*</span></label>
                                    <input type="date" name="document_details[permit_to_cut_date]" id="detail_permit_to_cut_date" value="{{ old('document_details.permit_to_cut_date') }}">
                                    @error('document_details.permit_to_cut_date') <div class="err">{{ $message }}</div> @enderror
                                </div>

                                <div class="field detail-field full">
                                    <label for="detail_permit_issued_by">Permit Issued By <span class="required">*</span></label>
                                    <input type="text" name="document_details[permit_issued_by]" id="detail_permit_issued_by" value="{{ old('document_details.permit_issued_by', 'DENR') }}" maxlength="255" placeholder="Example: DENR">
                                    <small class="helper">Enter the issuing office shown on the permit.</small>
                                    @error('document_details.permit_issued_by') <div class="err">{{ $message }}</div> @enderror
                                </div>

                                <div class="field detail-field">
                                    <label for="detail_tree_count">Number of Trees <span class="required">*</span></label>
                                    <input type="number" name="document_details[tree_count]" id="detail_tree_count" value="{{ old('document_details.tree_count', 1) }}" min="1" step="1">
                                    @error('document_details.tree_count') <div class="err">{{ $message }}</div> @enderror
                                </div>

                                <div class="field detail-field">
                                    <label for="detail_tree_type">Type of Tree / Trees <span class="required">*</span></label>
                                    <input type="text" name="document_details[tree_type]" id="detail_tree_type" value="{{ old('document_details.tree_type') }}" maxlength="1000" placeholder="Example: Acacia Tree">
                                    @error('document_details.tree_type') <div class="err">{{ $message }}</div> @enderror
                                </div>

                                <div class="field detail-field full">
                                    <label for="detail_tree_location">Location <span class="required">*</span></label>
                                    <input type="text" name="document_details[location]" id="detail_tree_location" value="{{ old('document_details.location') }}" maxlength="1000" placeholder="Example: Zone 7 Barangay Poblacion, Bani, Pangasinan">
                                    @error('document_details.location') <div class="err">{{ $message }}</div> @enderror
                                </div>

                                <div class="field detail-field full">
                                    <label for="detail_location_description">Location Description / Landmark <span class="required">*</span></label>
                                    <input type="text" name="document_details[location_description]" id="detail_location_description" value="{{ old('document_details.location_description') }}" maxlength="1000" placeholder="Example: Along the barangay road near the covered court">
                                    <small class="helper">Describe the exact location, landmark, or surrounding area. Do not assume the tree is inside a private compound.</small>
                                    @error('document_details.location_description') <div class="err">{{ $message }}</div> @enderror
                                </div>

                                <div class="field detail-field">
                                    <label for="detail_tree_owner">Tree Owner / Property Owner</label>
                                    <input type="text" name="document_details[tree_owner]" id="detail_tree_owner" value="{{ old('document_details.tree_owner') }}" maxlength="1000" placeholder="Optional">
                                    <small class="helper">Enter the owner only when applicable or stated in the supporting permit.</small>
                                    @error('document_details.tree_owner') <div class="err">{{ $message }}</div> @enderror
                                </div>

                                <div class="field detail-field">
                                    <label for="detail_cutter_name">Cutter Man <span class="required">*</span></label>
                                    <input type="text" name="document_details[cutter_name]" id="detail_cutter_name" value="{{ old('document_details.cutter_name') }}" maxlength="1000" placeholder="Example: John Carl Devera">
                                    @error('document_details.cutter_name') <div class="err">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>


                        {{-- =================================================
                             SOLO PARENT
                             ================================================= --}}

                        <div
                            class="document-details"
                            id="soloParentDetails"
                            hidden
                        >

                            <div class="document-details-head">

                                <i class="bi bi-person-heart"></i>

                                <div>

                                    <b>
                                        Solo Parent Certificate details
                                    </b>

                                    <small>
                                        Search registered residents and add the child or children/dependents who belong to the selected Solo Parent's registered household.
                                    </small>

                                </div>

                            </div>


                            <div class="solo-parent-note">

                                <i class="bi bi-database-check"></i>

                                <p>
                                    <strong>Automatic resident information:</strong>
                                    Search the Residents record, then add the appropriate child or dependent. The system automatically displays the registered name, sex, and birth date. Multiple children can be added.
                                </p>

                            </div>


                            <div class="detail-grid">


                                {{-- =================================================
                                     SEARCH RESIDENTS
                                     ================================================= --}}

                                <div class="field detail-field full">

                                    <label for="solo_parent_resident_search">

                                        Child / Family Member

                                        <span class="required">*</span>

                                    </label>


                                    <div class="solo-picker">

                                        <div class="solo-picker-search">

                                            <i class="bi bi-search"></i>

                                            <input
                                                type="text"
                                                id="solo_parent_resident_search"
                                                autocomplete="off"
                                                placeholder="Search registered resident by name…"
                                                aria-expanded="false"
                                                aria-controls="solo_parent_resident_list"
                                            >

                                        </div>


                                        <ul
                                            class="solo-picker-list"
                                            id="solo_parent_resident_list"
                                            hidden
                                        ></ul>

                                    </div>


                                    <div
                                        class="solo-household-warning"
                                        id="soloHouseholdWarning"
                                    >
                                        This resident is registered in the Residents database, but is not registered as a member of the selected Solo Parent's household. Only valid household members can be added.
                                    </div>


                                    <small class="helper">
                                        Search all registered residents. A resident can only be added when the system confirms that the person belongs to the selected Solo Parent's registered household.
                                    </small>


                                    @error('document_details.child_resident_ids')

                                        <div class="err">
                                            {{ $message }}
                                        </div>

                                    @enderror


                                    @error('document_details.child_resident_ids.*')

                                        <div class="err">
                                            {{ $message }}
                                        </div>

                                    @enderror

                                </div>


                                {{-- =================================================
                                     SELECTED MEMBERS
                                     ================================================= --}}

                                <div class="field detail-field full">

                                    <div class="selected-members">

                                        <div class="selected-members-head">

                                            <strong>
                                                Selected Children / Family Members
                                            </strong>

                                            <span
                                                class="selected-count"
                                                id="selectedMemberCount"
                                            >
                                                0
                                            </span>

                                        </div>


                                        <div
                                            class="selected-members-list"
                                            id="selectedMembersList"
                                        >

                                            <div
                                                class="selected-members-empty"
                                                id="selectedMembersEmpty"
                                            >
                                                No child or family member has been added yet.
                                            </div>

                                        </div>


                                        <div
                                            class="selected-members-error"
                                            id="selectedMembersError"
                                            hidden
                                        >
                                            Please add at least one registered child or family member.
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                             REMARKS
                             ================================================= --}}

                        <div class="field">

                            <label for="remarks">

                                Remarks

                                <span>
                                    (optional)
                                </span>

                            </label>


                            <textarea
                                name="remarks"
                                id="remarks"
                                rows="3"
                                maxlength="1000"
                                class="@error('remarks') bad @enderror"
                                placeholder="Notes for the staff, if any"
                            >{{ old('remarks') }}</textarea>


                            @error('remarks')

                                <div class="err">
                                    {{ $message }}
                                </div>

                            @enderror

                        </div>

                    </section>

                </div>


                {{-- =====================================================
                     RIGHT SUMMARY
                     ===================================================== --}}

                <aside class="side">

                    <div class="summary">

                        <div class="summary-head display">
                            Request summary
                        </div>


                        <dl>

                            <div>

                                <dt>
                                    Resident
                                </dt>

                                <dd
                                    id="sumResident"
                                    class="empty"
                                >
                                    Not chosen yet
                                </dd>

                            </div>


                            <div>

                                <dt>
                                    Document
                                </dt>

                                <dd
                                    id="sumDoc"
                                    class="empty"
                                >
                                    Not chosen yet
                                </dd>

                            </div>


                            <div>

                                <dt>
                                    Starting status
                                </dt>

                                <dd>

                                    <span class="pill">
                                        Pending
                                    </span>

                                </dd>

                            </div>

                        </dl>


                        <p class="summary-note">
                            Staff update the status after reviewing the request.
                        </p>


                        <div class="actions">

                            <button
                                type="submit"
                                class="btn-go"
                                id="submitBtn"
                                disabled
                            >

                                <i class="bi bi-check-circle"></i>

                                Create request

                            </button>


                            <p
                                class="missing"
                                id="missing"
                            >
                                Choose a resident and a document to continue.
                            </p>


                            <a
                                href="{{ route('documents.index') }}"
                                class="btn-cancel"
                            >
                                Cancel
                            </a>

                        </div>

                    </div>


                    <div class="verify">

                        <i class="bi bi-person-vcard"></i>

                        <div>

                            <b>
                                Check the ID in person
                            </b>

                            <p>
                                Staff verify the resident's physical ID at the office.
                                IDs are not uploaded or stored in this request.
                            </p>

                        </div>

                    </div>

                </aside>

            </div>

        </form>

    </div>

</div>

@endsection


@push('scripts')
<script>
(function () {

    'use strict';


    /* =========================================================
       DATA FROM LARAVEL
       ========================================================= */

    const residents = @json($residentData);

    const soloParentHouseholdMembers =
        @json($soloParentHouseholdMembers ?? []);


    /*
     * Previous Solo Parent selections after validation.
     *
     * New format:
     *
     * document_details.child_resident_ids[]
     *
     * We also defensively read the old single-value field so
     * an older validation state does not break the page.
     */
    const oldSoloChildIds =
        @json(old('document_details.child_resident_ids', []));

    const oldSoloChildId =
        @json(old('document_details.child_resident_id'));


    /* =========================================================
       DOM HELPER
       ========================================================= */

    const $ = (id) =>
        document.getElementById(id);


    const form =
        $('requestForm');

    const hidden =
        $('resident_id');

    const input =
        $('resident_search');

    const list =
        $('resident_list');

    const picked =
        $('picked');

    const searchWrap =
        $('searchWrap');


    let matches = [];

    let active = -1;


    /* =========================================================
       SOLO PARENT STATE
       ========================================================= */

    /*
     * Selected children/dependents are stored in memory while
     * the user is filling out the request.
     *
     * Example:
     *
     * [
     *     15,
     *     27,
     *     31
     * ]
     */
    let selectedSoloMembers = [];


    /* =========================================================
       HELPERS
       ========================================================= */

    const esc = (s) =>
        String(s ?? '').replace(
            /[&<>"']/g,
            (c) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[c])
        );


    const initials = (n) =>
        (
            String(n ?? '')
                .trim()
                .split(/\s+/)
                .filter(Boolean)
                .map(
                    (w, i, a) =>
                        (
                            i === 0 ||
                            i === a.length - 1
                        )
                            ? w[0]
                            : ''
                )
                .join('') || '?'
        ).toUpperCase();


    const words = (s) =>
        String(s ?? '')
            .toLowerCase()
            .split(/[\s,.\-\/]+/)
            .filter(Boolean);


    function normalizeSex(sex) {

        const value =
            String(sex ?? '')
                .trim()
                .toLowerCase();


        if (value === 'male') {
            return 'Male';
        }


        if (value === 'female') {
            return 'Female';
        }


        return value
            ? value.charAt(0).toUpperCase() +
              value.slice(1)
            : 'Sex not recorded';
    }


    function highlight(text, tokens) {

        let out = esc(text);


        tokens.forEach((t) => {

            const re = new RegExp(
                '(^|[\\s,.\\-\\/])(' +
                t.replace(
                    /[.*+?^${}()|[\]\\]/g,
                    '\\$&'
                ) +
                ')',
                'gi'
            );


            out = out.replace(
                re,
                '$1<mark>$2</mark>'
            );

        });


        return out;
    }


    /* =========================================================
       RESIDENT SEARCH
       ========================================================= */

    function filter(q) {

        const tokens = words(q);


        if (!tokens.length) {

            return {
                tokens,
                rows: residents.slice(0, 50),
                total: residents.length
            };

        }


        const rows =
            residents.filter((r) => {

                const w =
                    words(
                        r.name +
                        ' ' +
                        r.address
                    );


                return tokens.every(
                    (t) =>
                        w.some(
                            (x) =>
                                x.startsWith(t)
                        )
                );

            });


        const first =
            q.trim().toLowerCase();


        rows.sort(
            (a, b) =>
                (
                    b.name
                        .toLowerCase()
                        .startsWith(first)
                        ? 1
                        : 0
                )
                -
                (
                    a.name
                        .toLowerCase()
                        .startsWith(first)
                        ? 1
                        : 0
                )
        );


        return {
            tokens,
            rows: rows.slice(0, 50),
            total: rows.length
        };
    }


    function render() {

        const {
            tokens,
            rows,
            total
        } = filter(input.value);


        matches = rows;

        active =
            rows.length
                ? 0
                : -1;


        if (!rows.length) {

            list.innerHTML =
                '<li class="list-empty">' +
                'No resident found for “' +
                esc(input.value.trim()) +
                '”. Check the spelling or try the address.' +
                '</li>';

        } else {

            list.innerHTML =
                rows.map(
                    (r, i) =>

                        '<li class="opt" ' +
                        'role="option" ' +
                        'id="opt' + i + '" ' +
                        'data-i="' + i + '" ' +
                        'aria-selected="' +
                        (i === 0) +
                        '">' +

                        '<span class="avatar" ' +
                        'aria-hidden="true">' +
                        esc(initials(r.name)) +
                        '</span>' +

                        '<span>' +

                        '<b>' +
                        highlight(
                            r.name,
                            tokens
                        ) +
                        '</b>' +

                        (
                            r.address
                                ? '<small>' +
                                  highlight(
                                      r.address,
                                      tokens
                                  ) +
                                  '</small>'
                                : ''
                        ) +

                        '</span>' +

                        '</li>'
                ).join('') +

                (
                    total > rows.length
                        ? '<li class="list-foot">' +
                          'Showing ' +
                          rows.length +
                          ' of ' +
                          total +
                          '. Keep typing to narrow it down.' +
                          '</li>'
                        : ''
                );

        }


        open(true);
    }


    function open(state) {

        list.hidden =
            !state;


        input.setAttribute(
            'aria-expanded',
            String(state)
        );
    }


    function setActive(i) {

        const opts =
            list.querySelectorAll('.opt');


        if (!opts.length) {
            return;
        }


        active =
            (i + opts.length) %
            opts.length;


        opts.forEach(
            (o, k) =>
                o.setAttribute(
                    'aria-selected',
                    String(k === active)
                )
        );


        opts[active].scrollIntoView({
            block: 'nearest'
        });


        input.setAttribute(
            'aria-activedescendant',
            opts[active].id
        );
    }


    function choose(r) {

        if (!r) {
            return;
        }


        hidden.value =
            String(r.id);


        $('pickedName').textContent =
            r.name;


        $('pickedAddr').textContent =
            r.address ||
            'No address on file';


        $('pickedAvatar').textContent =
            initials(r.name);


        picked.hidden =
            false;


        searchWrap.hidden =
            true;


        $('combo').classList.remove(
            'invalid'
        );


        open(false);


        /*
         * A different parent means a completely different
         * household-member list.
         *
         * Therefore previously selected children must be
         * cleared when changing the parent resident.
         */
        resetSelectedSoloMembers();


        updateSoloParentDetails();


        summary();
    }


    function clear() {

        hidden.value = '';

        input.value = '';


        picked.hidden =
            true;


        searchWrap.hidden =
            false;


        resetSoloParentDetails();


        input.focus();

        render();

        summary();
    }


    input.addEventListener(
        'input',
        render
    );


    input.addEventListener(
        'focus',
        render
    );


    input.addEventListener(
        'blur',
        () =>
            setTimeout(
                () => open(false),
                120
            )
    );


    input.addEventListener(
        'keydown',
        (e) => {

            if (e.key === 'ArrowDown') {

                e.preventDefault();

                list.hidden
                    ? render()
                    : setActive(
                        active + 1
                    );

            } else if (e.key === 'ArrowUp') {

                e.preventDefault();

                setActive(
                    active - 1
                );

            } else if (e.key === 'Enter') {

                if (
                    !list.hidden &&
                    matches[active]
                ) {

                    e.preventDefault();

                    choose(
                        matches[active]
                    );

                } else {

                    e.preventDefault();

                }

            } else if (e.key === 'Escape') {

                open(false);

            }

        }
    );


    list.addEventListener(
        'mousedown',
        (e) => {

            const li =
                e.target.closest('.opt');


            if (!li) {
                return;
            }


            e.preventDefault();


            choose(
                matches[
                    Number(li.dataset.i)
                ]
            );

        }
    );


    $('change').addEventListener(
        'click',
        clear
    );


    /* =========================================================
       CERTIFICATE OF LATE REGISTER
       ========================================================= */

    function updateLateRegisterDetails() {

        const selected =
            document.querySelector(
                'input[name="document_type"]:checked'
            );


        const lateRegister =
            selected &&
            selected.value ===
                'Certificate of Late Register';


        const container =
            $('lateRegisterDetails');


        const fields = [
            $('detail_birthplace'),
            $('detail_father_name'),
            $('detail_mother_name')
        ];


        if (lateRegister) {

            container.hidden =
                false;


            container.classList.add(
                'show'
            );


            fields.forEach((field) => {

                field.disabled =
                    false;

                field.required =
                    true;

            });

        } else {

            container.hidden =
                true;


            container.classList.remove(
                'show'
            );


            fields.forEach((field) => {

                field.disabled =
                    true;

                field.required =
                    false;

            });

        }
    }


    /* =========================================================
       SOLO PARENT
       ========================================================= */

    function getSelectedSoloParentChildren() {

        const residentId =
            String(
                hidden.value || ''
            );


        if (!residentId) {
            return [];
        }


        const members =
            soloParentHouseholdMembers[
                residentId
            ];


        if (!members) {
            return [];
        }


        if (Array.isArray(members)) {
            return members;
        }


        return [];
    }


    function getHouseholdMemberById(residentId) {

        const id =
            String(residentId);


        return getSelectedSoloParentChildren()
            .find(
                (member) =>
                    String(
                        member.resident_id
                    ) === id
            ) || null;
    }


    function isHouseholdMember(residentId) {

        return !!getHouseholdMemberById(
            residentId
        );
    }


    function isSelectedSoloMember(residentId) {

        return selectedSoloMembers.includes(
            String(residentId)
        );
    }


    function addSoloMember(residentId) {

        const id =
            String(residentId);


        /*
         * Do not allow the selected parent to become
         * their own child/dependent.
         */
        if (
            !id ||
            id === String(hidden.value)
        ) {
            showSoloHouseholdWarning(
                'The selected Solo Parent cannot be added as their own child or dependent.'
            );

            return;
        }


        /*
         * Front-end household validation.
         */
        if (!isHouseholdMember(id)) {

            showSoloHouseholdWarning(
                'This resident is registered in the Residents database, but is not registered as a member of the selected Solo Parent\'s household.'
            );

            return;
        }


        /*
         * Prevent duplicates.
         */
        if (isSelectedSoloMember(id)) {

            hideSoloHouseholdWarning();

            return;
        }


        selectedSoloMembers.push(id);


        hideSoloHouseholdWarning();


        renderSelectedSoloMembers();


        renderSoloResidentSearch();


        $('solo_parent_resident_search').focus();


        updateSoloParentValidation();
    }


    function removeSoloMember(residentId) {

        const id =
            String(residentId);


        selectedSoloMembers =
            selectedSoloMembers.filter(
                (item) =>
                    String(item) !== id
            );


        renderSelectedSoloMembers();

        renderSoloResidentSearch();

        updateSoloParentValidation();
    }


    function resetSelectedSoloMembers() {

        selectedSoloMembers = [];


        renderSelectedSoloMembers();


        if ($('solo_parent_resident_search')) {
            $('solo_parent_resident_search').value = '';
        }


        hideSoloHouseholdWarning();


        renderSoloResidentSearch();


        updateSoloParentValidation();
    }


    function showSoloHouseholdWarning(message) {

        const warning =
            $('soloHouseholdWarning');


        warning.textContent =
            message;


        warning.classList.add(
            'show'
        );
    }


    function hideSoloHouseholdWarning() {

        const warning =
            $('soloHouseholdWarning');


        warning.textContent =
            'This resident is registered in the Residents database, but is not registered as a member of the selected Solo Parent\'s household. Only valid household members can be added.';


        warning.classList.remove(
            'show'
        );
    }


    function renderSelectedSoloMembers() {

        const container =
            $('selectedMembersList');


        const empty =
            $('selectedMembersEmpty');


        const count =
            $('selectedMemberCount');


        if (!container) {
            return;
        }


        /*
         * Remove all generated selected-member cards.
         */
        container
            .querySelectorAll(
                '.selected-member'
            )
            .forEach(
                (element) =>
                    element.remove()
            );


        count.textContent =
            String(
                selectedSoloMembers.length
            );


        empty.hidden =
            selectedSoloMembers.length > 0;


        selectedSoloMembers.forEach(
            (residentId) => {

                const member =
                    getHouseholdMemberById(
                        residentId
                    );


                if (!member) {
                    return;
                }


                const card =
                    document.createElement(
                        'div'
                    );


                card.className =
                    'selected-member';


                const name =
                    member.full_name ||
                    'Unnamed resident';


                const sex =
                    normalizeSex(
                        member.sex
                    );


                const birth =
                    member.birth_date_display ||
                    'Birth date not recorded';


                card.innerHTML =
                    '<span class="avatar" aria-hidden="true">' +
                        esc(initials(name)) +
                    '</span>' +

                    '<span class="selected-member-main">' +

                        '<span class="selected-member-name">' +
                            esc(name) +
                        '</span>' +

                        '<span class="selected-member-meta">' +

                            '<span>' +
                                '<i class="bi bi-person"></i>' +
                                esc(sex) +
                            '</span>' +

                            '<span>' +
                                '<i class="bi bi-calendar3"></i>' +
                                esc(birth) +
                            '</span>' +

                        '</span>' +

                    '</span>' +

                    '<button ' +
                        'type="button" ' +
                        'class="remove-member" ' +
                        'data-remove-member="' +
                            esc(residentId) +
                        '" ' +
                        'title="Remove selected member" ' +
                        'aria-label="Remove ' +
                            esc(name) +
                        '">' +

                        '<i class="bi bi-x-lg"></i>' +

                    '</button>';


                container.appendChild(
                    card
                );

            }
        );


        /*
         * Add hidden inputs for the backend.
         *
         * This creates:
         *
         * document_details[child_resident_ids][]
         */
        rebuildSoloParentHiddenInputs();
    }


    function rebuildSoloParentHiddenInputs() {

        /*
         * Remove previously generated hidden inputs.
         */
        form
            .querySelectorAll(
                'input[data-solo-child-input="true"]'
            )
            .forEach(
                (input) =>
                    input.remove()
            );


        /*
         * Do not add hidden values unless Solo Parent
         * is currently selected.
         */
        const selectedDocument =
            document.querySelector(
                'input[name="document_type"]:checked'
            );


        if (
            !selectedDocument ||
            selectedDocument.value !==
                'Solo Parent'
        ) {
            return;
        }


        selectedSoloMembers.forEach(
            (residentId) => {

                const input =
                    document.createElement(
                        'input'
                    );


                input.type =
                    'hidden';


                input.name =
                    'document_details[child_resident_ids][]';


                input.value =
                    String(residentId);


                input.dataset.soloChildInput =
                    'true';


                form.appendChild(
                    input
                );

            }
        );
    }


    function renderSoloResidentSearch() {

        const search =
            $('solo_parent_resident_search');


        const resultList =
            $('solo_parent_resident_list');


        if (!search || !resultList) {
            return;
        }


        const query =
            search.value
                .trim()
                .toLowerCase();


        /*
         * The requirement is to search the Residents database,
         * so the search list is based on ALL active residents
         * supplied by the controller.
         */
        let rows =
            residents.filter(
                (resident) => {

                    if (!query) {
                        return true;
                    }


                    const haystack =
                        (
                            resident.name +
                            ' ' +
                            resident.address
                        )
                            .toLowerCase();


                    return haystack.includes(
                        query
                    );

                }
            );


        rows =
            rows.slice(
                0,
                50
            );


        if (!rows.length) {

            resultList.innerHTML =
                '<li class="solo-search-empty">' +
                'No registered resident found.' +
                '</li>';


            resultList.hidden =
                false;


            return;
        }


        resultList.innerHTML =
            rows.map(
                (resident) => {

                    const id =
                        String(
                            resident.id
                        );


                    const isParent =
                        id ===
                        String(
                            hidden.value
                        );


                    const alreadySelected =
                        isSelectedSoloMember(
                            id
                        );


                    const validMember =
                        isHouseholdMember(
                            id
                        );


                    let actionHtml = '';


                    if (isParent) {

                        actionHtml =
                            '<span class="result-status">' +
                            'Selected Solo Parent' +
                            '</span>';

                    } else if (alreadySelected) {

                        actionHtml =
                            '<span class="solo-result-added">' +
                            '<i class="bi bi-check-circle-fill"></i> Added' +
                            '</span>';

                    } else if (validMember) {

                        actionHtml =
                            '<button ' +
                                'type="button" ' +
                                'class="solo-add-btn" ' +
                                'data-add-member="' +
                                    esc(id) +
                                '">' +

                                '<i class="bi bi-plus-lg"></i>' +
                                ' Add' +

                            '</button>';

                    } else {

                        actionHtml =
                            '<span class="result-status">' +
                            'Not in selected household' +
                            '</span>';

                    }


                    const sex =
                        normalizeSex(
                            resident.sex
                        );


                    const birth =
                        resident.birth_date_display ||
                        'Birth date not recorded';


                    return (

                        '<li class="solo-result">' +

                            '<span class="avatar" aria-hidden="true">' +
                                esc(
                                    initials(
                                        resident.name
                                    )
                                ) +
                            '</span>' +

                            '<span class="result-main">' +

                                '<b>' +
                                    esc(
                                        resident.name
                                    ) +
                                '</b>' +

                                '<small>' +
                                    esc(sex) +
                                    ' • ' +
                                    esc(birth) +
                                '</small>' +

                                (
                                    resident.address
                                        ? '<small>' +
                                            '<i class="bi bi-geo-alt"></i> ' +
                                            esc(
                                                resident.address
                                            ) +
                                          '</small>'
                                        : ''
                                ) +

                            '</span>' +

                            actionHtml +

                        '</li>'

                    );

                }
            ).join('');


        resultList.hidden =
            false;
    }


    function openSoloResidentSearch() {

        const search =
            $('solo_parent_resident_search');


        const resultList =
            $('solo_parent_resident_list');


        if (!search || !resultList) {
            return;
        }


        renderSoloResidentSearch();


        search.setAttribute(
            'aria-expanded',
            'true'
        );
    }


    function closeSoloResidentSearch() {

        const resultList =
            $('solo_parent_resident_list');


        const search =
            $('solo_parent_resident_search');


        if (!resultList || !search) {
            return;
        }


        resultList.hidden =
            true;


        search.setAttribute(
            'aria-expanded',
            'false'
        );
    }


    function updateSoloParentValidation() {

        const selectedDocument =
            document.querySelector(
                'input[name="document_type"]:checked'
            );


        const error =
            $('selectedMembersError');


        if (
            selectedDocument &&
            selectedDocument.value ===
                'Solo Parent' &&
            selectedSoloMembers.length === 0
        ) {

            error.hidden =
                false;

        } else {

            error.hidden =
                true;

        }


        rebuildSoloParentHiddenInputs();
    }


    function restoreOldSoloMembers() {

        let ids = [];


        /*
         * New array format.
         */
        if (Array.isArray(oldSoloChildIds)) {

            ids =
                oldSoloChildIds
                    .map(
                        (id) =>
                            String(id)
                    )
                    .filter(Boolean);

        }


        /*
         * Defensive compatibility with the old single
         * child_resident_id field.
         */
        if (
            !ids.length &&
            oldSoloChildId !== null &&
            oldSoloChildId !== undefined &&
            String(oldSoloChildId).trim() !== ''
        ) {

            ids = [
                String(oldSoloChildId)
            ];

        }


        /*
         * Remove duplicates.
         */
        ids =
            [...new Set(ids)];


        /*
         * Only restore members that are actually valid
         * household members for the selected parent.
         */
        selectedSoloMembers =
            ids.filter(
                (id) =>
                    isHouseholdMember(id) &&
                    id !== String(hidden.value)
            );


        renderSelectedSoloMembers();

        renderSoloResidentSearch();

        updateSoloParentValidation();
    }


    function resetSoloParentDetails() {

        selectedSoloMembers =
            [];


        const search =
            $('solo_parent_resident_search');


        const resultList =
            $('solo_parent_resident_list');


        if (search) {
            search.value = '';
        }


        if (resultList) {

            resultList.innerHTML =
                '';

            resultList.hidden =
                true;

            resultList.setAttribute(
                'aria-expanded',
                'false'
            );

        }


        hideSoloHouseholdWarning();


        renderSelectedSoloMembers();


        updateSoloParentValidation();
    }


    /* =========================================================
       CUTTING TREES
       ========================================================= */

    function prepareCuttingTreesSubmission() {
        const selected = document.querySelector('input[name="document_type"]:checked');

        if (!selected || selected.value !== 'Cutting Trees') {
            return;
        }

        const fields = [
            ['detail_permit_to_cut_no', 'document_details[permit_to_cut_no]'],
            ['detail_permit_to_cut_date', 'document_details[permit_to_cut_date]'],
            ['detail_permit_issued_by', 'document_details[permit_issued_by]'],
            ['detail_tree_count', 'document_details[tree_count]'],
            ['detail_tree_type', 'document_details[tree_type]'],
            ['detail_tree_location', 'document_details[location]'],
            ['detail_location_description', 'document_details[location_description]'],
            ['detail_tree_owner', 'document_details[tree_owner]'],
            ['detail_cutter_name', 'document_details[cutter_name]']
        ];

        form.querySelectorAll('input[data-cutting-tree-mirror="true"]').forEach((element) => element.remove());

        fields.forEach(([id, name]) => {
            const field = $(id);

            if (!field) {
                return;
            }

            const mirror = document.createElement('input');
            mirror.type = 'hidden';
            mirror.name = name;
            mirror.value = String(field.value ?? '').trim();
            mirror.dataset.cuttingTreeMirror = 'true';

            form.appendChild(mirror);

            /*
             * The hidden mirror becomes the only submitted copy of this
             * field. This avoids duplicate nested values in the POST body.
             */
            field.disabled = true;
        });
    }


    function updateCuttingTreesDetails() {
        const selected = document.querySelector('input[name="document_type"]:checked');
        const isCuttingTrees = selected && selected.value === 'Cutting Trees';
        const container = $('cuttingTreesDetails');

        const fields = [
            $('detail_permit_to_cut_no'), $('detail_permit_to_cut_date'),
            $('detail_permit_issued_by'), $('detail_tree_count'),
            $('detail_tree_type'), $('detail_tree_location'),
            $('detail_location_description'), $('detail_tree_owner'),
            $('detail_cutter_name')
        ];

        const requiredFields = [
            $('detail_permit_to_cut_no'), $('detail_permit_to_cut_date'),
            $('detail_permit_issued_by'), $('detail_tree_count'),
            $('detail_tree_type'), $('detail_tree_location'),
            $('detail_location_description'), $('detail_cutter_name')
        ];

        container.hidden = !isCuttingTrees;
        container.classList.toggle('show', isCuttingTrees);

        /*
         * IMPORTANT:
         * Do not disable the Cutting Trees inputs.
         *
         * Disabled HTML inputs are excluded from the submitted form data.
         * This caused the Permit to Cut number to reach Laravel as empty
         * even when the user had already typed a value.
         *
         * The controller already keeps Cutting Trees details isolated to
         * the selected document type, so the fields can safely remain
         * enabled while the panel is hidden.
         */
        fields.forEach((field) => {
            if (!field) {
                return;
            }

            field.disabled = !isCuttingTrees;
            field.required = false;

            if (!isCuttingTrees) {
                field.classList.remove('bad');
            }
        });

        if (isCuttingTrees) {
            requiredFields.forEach((field) => {
                if (!field) {
                    return;
                }

                field.disabled = false;
                field.required = true;
            });
        }
    }


    function updateSoloParentDetails() {

        const selected =
            document.querySelector(
                'input[name="document_type"]:checked'
            );


        const isSoloParent =
            selected &&
            selected.value ===
                'Solo Parent';


        const container =
            $('soloParentDetails');


        if (!container) {
            return;
        }


        if (isSoloParent) {

            container.hidden =
                false;


            container.classList.add(
                'show'
            );


            rebuildSoloParentHiddenInputs();


            renderSelectedSoloMembers();


            renderSoloResidentSearch();


            updateSoloParentValidation();

        } else {

            container.hidden =
                true;


            container.classList.remove(
                'show'
            );


            resetSoloParentDetails();

        }
    }


    /* =========================================================
       SOLO PARENT SEARCH EVENTS
       ========================================================= */

    $('solo_parent_resident_search').addEventListener(
        'focus',
        function () {

            openSoloResidentSearch();

        }
    );


    $('solo_parent_resident_search').addEventListener(
        'input',
        function () {

            renderSoloResidentSearch();

        }
    );


    $('solo_parent_resident_search').addEventListener(
        'keydown',
        function (e) {

            if (e.key === 'Escape') {

                closeSoloResidentSearch();

                return;
            }


            if (e.key === 'Enter') {

                e.preventDefault();

            }

        }
    );


    $('solo_parent_resident_list').addEventListener(
        'mousedown',
        function (e) {

            const button =
                e.target.closest(
                    '[data-add-member]'
                );


            if (!button) {
                return;
            }


            e.preventDefault();


            addSoloMember(
                button.dataset.addMember
            );

        }
    );


    $('selectedMembersList').addEventListener(
        'click',
        function (e) {

            const button =
                e.target.closest(
                    '[data-remove-member]'
                );


            if (!button) {
                return;
            }


            removeSoloMember(
                button.dataset.removeMember
            );

        }
    );


    document.addEventListener(
        'click',
        function (e) {

            const picker =
                e.target.closest(
                    '.solo-picker'
                );


            if (!picker) {

                closeSoloResidentSearch();

            }

        }
    );


    /* =========================================================
       PURPOSE
       ========================================================= */

    function updatePurposeRequirement() {

        const purpose =
            $('purpose');

        const optionalLabel =
            $('purposeOptionalLabel');

        const requiredLabel =
            $('purposeRequiredLabel');

        const requiredNote =
            $('purposeRequiredNote');

        const hint =
            $('detailsHint');


        purpose.required =
            false;


        optionalLabel.style.display =
            'inline';


        requiredLabel.style.display =
            'none';


        requiredNote.classList.remove(
            'show'
        );


        hint.textContent =
            'Optional, but a clear purpose helps staff process the request faster.';
    }


    /* =========================================================
       SUMMARY
       ========================================================= */

    function summary() {

        const r =
            residents.find(
                (x) =>
                    String(x.id) ===
                    String(hidden.value)
            );


        const t =
            document.querySelector(
                'input[name="document_type"]:checked'
            );


        const sr =
            $('sumResident');

        const sd =
            $('sumDoc');


        sr.textContent =
            r
                ? r.name
                : 'Not chosen yet';


        sr.classList.toggle(
            'empty',
            !r
        );


        sd.textContent =
            t
                ? t.value
                : 'Not chosen yet';


        sd.classList.toggle(
            'empty',
            !t
        );


        const submitReady =
            !!(r && t);


        $('submitBtn').disabled =
            !submitReady;


        $('missing').hidden =
            submitReady;


        $('missing').textContent =
            !r && !t
                ? 'Choose a resident and a document to continue.'
                : !r
                    ? 'Choose a resident to continue.'
                    : 'Choose a document to continue.';


        updatePurposeRequirement();

        updateLateRegisterDetails();

        updateCuttingTreesDetails();

        updateSoloParentDetails();
    }


    /* =========================================================
       DOCUMENT TYPE CHANGE
       ========================================================= */

    document
        .querySelectorAll(
            'input[name="document_type"]'
        )
        .forEach(
            (el) =>
                el.addEventListener(
                    'change',
                    () => {

                        updateLateRegisterDetails();

                        updateCuttingTreesDetails();

                        updateSoloParentDetails();

                        summary();

                    }
                )
        );


    /* =========================================================
       PURPOSE QUICK FILL
       ========================================================= */

    $('purposeChips').addEventListener(
        'click',
        (e) => {

            const b =
                e.target.closest(
                    'button[data-purpose]'
                );


            if (!b) {
                return;
            }


            const ta =
                $('purpose');


            ta.value =
                ta.value.trim()
                    ? ta.value.replace(
                        /[\s,]+$/,
                        ''
                    ) +
                      ', ' +
                      b.dataset
                        .purpose
                        .toLowerCase()

                    : b.dataset.purpose;


            ta.classList.remove(
                'bad'
            );


            ta.focus();
        }
    );


    /* =========================================================
       FORM SUBMIT VALIDATION
       ========================================================= */

    form.addEventListener(
        'submit',
        (e) => {

            const selectedResident =
                residents.find(
                    (r) =>
                        String(r.id) ===
                        String(hidden.value)
                );


            const selectedDocument =
                document.querySelector(
                    'input[name="document_type"]:checked'
                );


            if (
                !selectedResident ||
                !selectedDocument
            ) {

                e.preventDefault();


                if (!selectedResident) {

                    $('combo').classList.add(
                        'invalid'
                    );

                    input.focus();

                }


                if (!selectedDocument) {

                    const firstType =
                        document.querySelector(
                            'input[name="document_type"]'
                        );


                    if (firstType) {
                        firstType.focus();
                    }

                }


                summary();

                return;
            }


            /* =================================================
               LATE REGISTER
               ================================================= */

            if (
                selectedDocument.value ===
                'Certificate of Late Register'
            ) {

                const birthplace =
                    $('detail_birthplace');

                const fatherName =
                    $('detail_father_name');

                const motherName =
                    $('detail_mother_name');


                /*
                 * IMPORTANT:
                 *
                 * Disabled HTML form controls are NOT submitted
                 * to Laravel. The Late Register fields start as
                 * disabled because they are document-specific.
                 *
                 * Enable them one final time immediately before
                 * the form is submitted so Laravel receives:
                 *
                 * document_details[birthplace]
                 * document_details[father_name]
                 * document_details[mother_name]
                 */
                [
                    birthplace,
                    fatherName,
                    motherName
                ].forEach(
                    (field) => {

                        if (!field) {
                            return;
                        }

                        field.disabled = false;
                        field.required = true;

                    }
                );


                const detailFields = [
                    {
                        element:
                            birthplace,
                        label:
                            'Place of Birth'
                    },

                    {
                        element:
                            fatherName,
                        label:
                            "Father's Name"
                    },

                    {
                        element:
                            motherName,
                        label:
                            "Mother's Name"
                    }
                ];


                let firstInvalid =
                    null;


                detailFields.forEach(
                    (item) => {

                        const field =
                            item.element;


                        if (!field) {
                            return;
                        }


                        field.classList.remove(
                            'bad'
                        );


                        const value =
                            field.value.trim();


                        if (!value) {

                            field.classList.add(
                                'bad'
                            );


                            if (!firstInvalid) {
                                firstInvalid =
                                    field;
                            }

                        }

                    }
                );


                if (firstInvalid) {

                    e.preventDefault();

                    firstInvalid.focus();

                    return;
                }


                /*
                 * Remove accidental leading/trailing whitespace
                 * before the values are sent to Laravel.
                 */
                birthplace.value =
                    birthplace.value.trim();

                fatherName.value =
                    fatherName.value.trim();

                motherName.value =
                    motherName.value.trim();


                /*
                 * Final client-side check of the actual FormData.
                 * This confirms that the three values are really
                 * part of the request and not merely visible on
                 * the page.
                 */
                const formData =
                    new FormData(form);


                const submittedBirthplace =
                    String(
                        formData.get(
                            'document_details[birthplace]'
                        ) || ''
                    ).trim();


                const submittedFather =
                    String(
                        formData.get(
                            'document_details[father_name]'
                        ) || ''
                    ).trim();


                const submittedMother =
                    String(
                        formData.get(
                            'document_details[mother_name]'
                        ) || ''
                    ).trim();


                if (
                    !submittedBirthplace ||
                    !submittedFather ||
                    !submittedMother
                ) {

                    e.preventDefault();


                    alert(
                        'Please enter the Place of Birth, Father\'s Name, and Mother\'s Name before submitting the Certificate of Late Register.'
                    );


                    if (!submittedBirthplace) {

                        birthplace.focus();

                    } else if (!submittedFather) {

                        fatherName.focus();

                    } else {

                        motherName.focus();

                    }


                    return;
                }

            }


            /* =================================================
               CUTTING TREES
               ================================================= */

            if (selectedDocument.value === 'Cutting Trees') {
                const cuttingFields = [
                    { element: $('detail_permit_to_cut_no'), label: 'Permit to Cut No.' },
                    { element: $('detail_permit_to_cut_date'), label: 'Permit to Cut Date' },
                    { element: $('detail_permit_issued_by'), label: 'Permit Issued By' },
                    { element: $('detail_tree_count'), label: 'Number of Trees' },
                    { element: $('detail_tree_type'), label: 'Type of Tree / Trees' },
                    { element: $('detail_tree_location'), label: 'Location' },
                    { element: $('detail_location_description'), label: 'Location Description / Landmark' },
                    { element: $('detail_cutter_name'), label: 'Cutter Man' }
                ];

                let firstInvalid = null;

                cuttingFields.forEach((item) => {
                    const field = item.element;
                    if (!field) return;
                    field.disabled = false;
                    field.required = true;
                    field.classList.remove('bad');

                    if (!String(field.value || '').trim()) {
                        field.classList.add('bad');
                        if (!firstInvalid) firstInvalid = field;
                    }
                });

                $('detail_tree_owner').disabled = false;

                if (firstInvalid) {
                    e.preventDefault();
                    firstInvalid.focus();
                    return;
                }

                cuttingFields.forEach((item) => {
                    if (item.element) {
                        item.element.value = String(item.element.value || '').trim();
                    }
                });

                $('detail_location_description').value = String($('detail_location_description').value || '').trim();
                $('detail_tree_owner').value = String($('detail_tree_owner').value || '').trim();

                /* Final server-bound mirror for Cutting Trees details. */
                prepareCuttingTreesSubmission();
            }


            /* =================================================
               SOLO PARENT
               ================================================= */

            if (
                selectedDocument.value ===
                'Solo Parent'
            ) {

                /*
                 * At least one child/dependent is required.
                 */
                if (
                    !selectedSoloMembers.length
                ) {

                    e.preventDefault();


                    const error =
                        $('selectedMembersError');


                    error.hidden =
                        false;


                    const search =
                        $('solo_parent_resident_search');


                    if (search) {
                        search.focus();
                    }


                    return;
                }


                /*
                 * Make sure every selected member is still
                 * a valid member of the selected household.
                 */
                const invalidMember =
                    selectedSoloMembers.find(
                        (residentId) =>
                            !isHouseholdMember(
                                residentId
                            )
                    );


                if (invalidMember) {

                    e.preventDefault();


                    selectedSoloMembers =
                        selectedSoloMembers.filter(
                            (residentId) =>
                                isHouseholdMember(
                                    residentId
                                )
                        );


                    renderSelectedSoloMembers();

                    renderSoloResidentSearch();

                    updateSoloParentValidation();


                    const search =
                        $('solo_parent_resident_search');


                    if (search) {
                        search.focus();
                    }


                    return;
                }


                /*
                 * Rebuild the hidden inputs one final time
                 * before submitting.
                 */
                rebuildSoloParentHiddenInputs();
            }


            $('purpose').classList.remove(
                'bad'
            );
        }
    );


    /* =========================================================
       RESTORE PREVIOUS RESIDENT
       ========================================================= */

    const oldResident =
        residents.find(
            (x) =>
                String(x.id) ===
                String(hidden.value)
        );


    if (oldResident) {

        /*
         * choose() resets selected members, which is correct
         * before we restore old validation values below.
         */
        choose(oldResident);

    }


    /* =========================================================
       RESTORE PREVIOUS SOLO PARENT MEMBERS
       ========================================================= */

    /*
     * This is intentionally done AFTER choose(oldResident),
     * because choose() resets the selected-member list.
     */
    if (
        oldResident &&
        document.querySelector(
            'input[name="document_type"]:checked'
        )?.value === 'Solo Parent'
    ) {

        restoreOldSoloMembers();

    }


    /* =========================================================
       INITIAL PAGE STATE
       ========================================================= */

    updateLateRegisterDetails();

    updateSoloParentDetails();

    summary();


})();
</script>
@endpush
