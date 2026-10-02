@extends('layouts.app')

@section('title', 'Edit Report / Complaint')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       EDIT REPORT / COMPLAINT - same design system as the other pages
       ========================================================= */
    .rc {
        --ink: #14213d;
        --muted: #5f6b7d;
        --paper: #ffffff;
        --wash: #eef2f7;
        --line: #dbe2ec;
        --brand: #1f4e9c;
        --brand-soft: #e4edfb;
        --sun: #f2b705;
        --bad: #c92a2a;
        --bad-bg: #fde3e3;
        --ok: #2b8a3e;

        --community: #0b7285;
        --community-bg: #d8f3f8;

        --person: #c2410c;
        --person-bg: #ffe8d9;

        --s-filed: #5f6b7d;
        --s-filed-bg: #e9edf3;

        --s-review: #b26a00;
        --s-review-bg: #fff1d6;

        --s-captain: #c2410c;
        --s-captain-bg: #ffe8d9;

        --s-hearing: #6741d9;
        --s-hearing-bg: #ece5fd;

        --s-resolution: #0b7285;
        --s-resolution-bg: #d8f3f8;

        --s-resolved: #2b8a3e;
        --s-resolved-bg: #dcf5e3;

        --s-closed: #495057;
        --s-closed-bg: #e3e7ec;

        --s-disapproved: #c92a2a;
        --s-disapproved-bg: #fde3e3;

        font-family: 'Public Sans', system-ui, sans-serif;
        color: var(--ink);
    }

    html[data-theme="dark"] .rc {
        --ink: #eef2f8;
        --muted: #9aa7bb;
        --paper: #1b2230;
        --wash: #141a25;
        --line: #303a4d;
        --brand: #7aa7f0;
        --brand-soft: #23324f;
        --bad: #ff8f8f;
        --bad-bg: #401a1d;
        --ok: #6fd48a;

        --community: #5ccfe0;
        --community-bg: #0f3038;

        --person: #ffa26b;
        --person-bg: #40230f;

        --s-filed: #b7c0cf;
        --s-filed-bg: #2a3242;

        --s-review: #f5b942;
        --s-review-bg: #3a2c10;

        --s-captain: #ffa26b;
        --s-captain-bg: #40230f;

        --s-hearing: #b7a0f5;
        --s-hearing-bg: #2a2150;

        --s-resolution: #5ccfe0;
        --s-resolution-bg: #0f3038;

        --s-resolved: #6fd48a;
        --s-resolved-bg: #143222;

        --s-closed: #b0b8c5;
        --s-closed-bg: #2a3242;

        --s-disapproved: #ff8f8f;
        --s-disapproved-bg: #401a1d;
    }

    .rc * {
        box-sizing: border-box;
    }

    .rc .display {
        font-family: 'Bricolage Grotesque', 'Public Sans', sans-serif;
        letter-spacing: -.02em;
    }

    .rc a:focus-visible,
    .rc button:focus-visible,
    .rc input:focus-visible,
    .rc select:focus-visible,
    .rc textarea:focus-visible,
    .rc .tcard input:focus-visible + .box,
    .rc summary:focus-visible {
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

    .head {
        margin: .9rem 0 1.5rem;
    }

    .head h1 {
        font-size: clamp(1.7rem, 3.2vw, 2.3rem);
        font-weight: 800;
        margin: 0;
        line-height: 1.1;
    }

    .head p {
        margin: .35rem 0 0;
        color: var(--muted);
    }

    .head p b {
        color: var(--ink);
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

    .req {
        color: var(--bad);
    }

    .err {
        color: var(--bad);
        font-size: .88rem;
        margin-top: .4rem;
    }

    .err[hidden],
    .person-box[hidden],
    .sw[hidden],
    .picked[hidden],
    .list[hidden],
    [hidden].hd {
        display: none;
    }

    .grid {
        display: grid;
        gap: 1rem;
    }

    .g2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    @media (max-width: 575.98px) {
        .g2 {
            grid-template-columns: 1fr;
        }
    }

    .full {
        grid-column: 1 / -1;
    }

    .field label.lbl {
        display: block;
        font-weight: 600;
        margin-bottom: .4rem;
        font-size: .92rem;
    }

    .field label.lbl .opt {
        color: var(--muted);
        font-weight: 400;
    }

    .field.changed label.lbl::after {
        content: 'edited';
        margin-left: .5rem;
        padding: .05rem .5rem;
        border-radius: 999px;
        background: var(--brand-soft);
        color: var(--brand);
        font-size: .72rem;
        font-weight: 600;
    }

    .rc input[type="text"],
    .rc input[type="date"],
    .rc input[type="time"],
    .rc select,
    .rc textarea {
        width: 100%;
        min-height: 46px;
        padding: .6rem .9rem;
        border-radius: .85rem;
        font: inherit;
        font-size: .95rem;
        border: 1.5px solid var(--line);
        background: var(--paper);
        color: var(--ink);
    }

    .rc textarea {
        resize: vertical;
        min-height: 130px;
    }

    .rc input::placeholder,
    .rc textarea::placeholder {
        color: var(--muted);
    }

    .rc input:focus,
    .rc select:focus,
    .rc textarea:focus {
        border-color: var(--brand);
        outline: none;
        box-shadow: 0 0 0 4px var(--brand-soft);
    }

    .rc .bad {
        border-color: var(--bad) !important;
    }

    .rc input[readonly] {
        background: var(--wash);
        color: var(--muted);
    }

    html[data-theme="dark"] .rc select,
    html[data-theme="dark"] .rc input[type="date"],
    html[data-theme="dark"] .rc input[type="time"] {
        color-scheme: dark;
    }

    /* type cards */
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

    .tcard {
        position: relative;
        display: block;
        cursor: pointer;
        margin: 0;
    }

    .tcard input {
        position: absolute;
        opacity: 0;
        inset: 0;
    }

    .tcard .box {
        display: flex;
        gap: .8rem;
        align-items: flex-start;
        height: 100%;
        padding: 1rem;
        border-radius: .95rem;
        border: 1.5px solid var(--line);
        background: var(--paper);
        transition: border-color .15s, background .15s;
    }

    .tcard:hover .box {
        border-color: var(--c);
    }

    .tcard .ic {
        width: 2.6rem;
        height: 2.6rem;
        border-radius: .75rem;
        display: grid;
        place-items: center;
        font-size: 1.25rem;
        background: var(--cbg);
        color: var(--c);
        flex: none;
    }

    .tcard b {
        display: block;
        font-weight: 700;
    }

    .tcard small {
        color: var(--muted);
    }

    .tcard .tick {
        margin-left: auto;
        color: var(--c);
        opacity: 0;
    }

    .tcard input:checked + .box {
        border-color: var(--c);
        background: var(--cbg);
        box-shadow: inset 0 0 0 1px var(--c);
    }

    .tcard input:checked + .box .tick {
        opacity: 1;
    }

    .tc-community {
        --c: var(--community);
        --cbg: var(--community-bg);
    }

    .tc-person {
        --c: var(--person);
        --cbg: var(--person-bg);
    }

    /* resident combobox */
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
        padding-left: 2.6rem;
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
        max-height: 290px;
        overflow-y: auto;
        border-radius: .9rem;
        background: var(--paper);
        border: 1px solid var(--line);
        box-shadow: 0 12px 30px rgba(10, 20, 40, .18);
    }

    .opt {
        display: flex;
        align-items: center;
        gap: .7rem;
        padding: .5rem .6rem;
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

    .opt small,
    .picked small {
        color: var(--muted);
    }

    .opt mark {
        background: var(--sun);
        color: #14213d;
        border-radius: 3px;
        padding: 0 1px;
    }

    .avatar {
        width: 2.3rem;
        height: 2.3rem;
        border-radius: 50%;
        display: grid;
        place-items: center;
        flex: none;
        font-weight: 700;
        font-size: .82rem;
        background: var(--brand-soft);
        color: var(--brand);
    }

    .list-empty,
    .list-foot {
        padding: .9rem;
        text-align: center;
        color: var(--muted);
        font-size: .9rem;
    }

    .picked {
        display: flex;
        align-items: center;
        gap: .8rem;
        padding: .7rem .9rem;
        border-radius: .9rem;
        border: 1.5px solid var(--brand);
        background: var(--brand-soft);
    }

    .picked .grow {
        flex: 1;
        min-width: 0;
    }

    .picked b {
        display: block;
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

    .person-box {
        margin-top: 1rem;
        padding: 1rem;
        border-radius: .95rem;
        background: var(--person-bg);
        border: 1px dashed var(--person);
    }

    /* location */
    .locprev {
        display: flex;
        gap: .7rem;
        align-items: flex-start;
        margin-top: 1rem;
        padding: .8rem 1rem;
        border-radius: .9rem;
        background: var(--wash);
    }

    .locprev i {
        color: var(--brand);
        font-size: 1.2rem;
    }

    .locprev small {
        display: block;
        color: var(--muted);
    }

    details.more {
        margin-top: 1rem;
        border: 1px solid var(--line);
        border-radius: .9rem;
    }

    details.more summary {
        cursor: pointer;
        padding: .7rem 1rem;
        font-weight: 600;
        color: var(--brand);
        list-style: none;
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    details.more summary::-webkit-details-marker {
        display: none;
    }

    details.more[open] summary {
        border-bottom: 1px solid var(--line);
    }

    details.more .inner {
        padding: 1rem;
    }

    details.more summary .chev {
        margin-left: auto;
        transition: transform .15s;
    }

    details.more[open] summary .chev {
        transform: rotate(180deg);
    }

    /* switch */
    .sw {
        display: flex;
        gap: 1rem;
        align-items: center;
        padding: 1rem;
        border-radius: .95rem;
        border: 1.5px solid var(--line);
        background: var(--paper);
        cursor: pointer;
        margin: 1rem 0 0;
    }

    .sw:has(input:checked) {
        border-color: var(--brand);
        background: var(--brand-soft);
    }

    .sw input {
        width: 1.25rem;
        height: 1.25rem;
        accent-color: var(--brand);
        flex: none;
    }

    .sw b {
        display: block;
    }

    .sw small {
        color: var(--muted);
    }

    /* status */
    .pill {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .3rem .8rem;
        border-radius: 999px;
        font-size: .88rem;
        font-weight: 600;
        color: var(--sc);
        background: var(--sb);
    }

    .pill::before {
        content: '';
        width: .55rem;
        height: .55rem;
        border-radius: 50%;
        background: var(--sc);
    }

    .s-filed {
        --sc: var(--s-filed);
        --sb: var(--s-filed-bg);
    }

    .s-review {
        --sc: var(--s-review);
        --sb: var(--s-review-bg);
    }

    .s-captain {
        --sc: var(--s-captain);
        --sb: var(--s-captain-bg);
    }

    .s-hearing {
        --sc: var(--s-hearing);
        --sb: var(--s-hearing-bg);
    }

    .s-resolution {
        --sc: var(--s-resolution);
        --sb: var(--s-resolution-bg);
    }

    .s-resolved {
        --sc: var(--s-resolved);
        --sb: var(--s-resolved-bg);
    }

    .s-closed {
        --sc: var(--s-closed);
        --sb: var(--s-closed-bg);
    }

    .s-disapproved {
        --sc: var(--s-disapproved);
        --sb: var(--s-disapproved-bg);
    }

    .status-row {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .status-row select {
        flex: 1;
        min-width: 220px;
        width: auto;
    }

    .status-note {
        display: flex;
        gap: .6rem;
        margin-top: .9rem;
        padding: .75rem .9rem;
        border-radius: .8rem;
        background: var(--wash);
        color: var(--muted);
        font-size: .9rem;
    }

    .status-note i {
        color: var(--brand);
    }

    .status-note.warn {
        background: var(--s-review-bg);
        color: var(--ink);
    }

    .status-note.warn i {
        color: var(--s-review);
    }

    /* side panel */
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
        gap: .85rem;
    }

    .summary dt {
        color: var(--muted);
        font-size: .8rem;
    }

    .summary dd {
        margin: .1rem 0 0;
        font-weight: 600;
    }

    .changes {
        padding: 0 1.25rem 1rem;
    }

    .changes .count {
        font-size: .92rem;
        font-weight: 600;
    }

    .changes .count.clean {
        color: var(--muted);
        font-weight: 400;
    }

    .changes ul {
        margin: .5rem 0 0;
        padding: 0;
        list-style: none;
        display: flex;
        flex-wrap: wrap;
        gap: .35rem;
    }

    .changes li {
        padding: .15rem .6rem;
        border-radius: 999px;
        background: var(--brand-soft);
        color: var(--brand);
        font-size: .8rem;
        font-weight: 600;
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

    @media (prefers-reduced-motion: reduce) {
        .rc * {
            transition: none !important;
        }
    }
</style>
@endpush

@section('content')

@php
    /* ---- Existing location values (old records may only have the combined "location") ---- */
    $savedLocation     = old('location', $reportsComplaint->location);
    $savedPurok        = old('purok', $reportsComplaint->purok);
    $savedBarangay     = old('barangay', $reportsComplaint->barangay);
    $savedMunicipality = old('municipality', $reportsComplaint->municipality);
    $savedProvince     = old('province', $reportsComplaint->province);
    $savedRegion       = old('region', $reportsComplaint->region);

    if (empty($savedPurok) && !empty($savedLocation)) {
        $locationParts = array_map('trim', explode(',', $savedLocation));

        if (
            count($locationParts) >= 1
            && (
                str_starts_with(strtolower($locationParts[0]), 'purok')
                || strtolower($locationParts[0]) === 'centro'
            )
        ) {
            $savedPurok = $locationParts[0];
        }

        foreach ($locationParts as $part) {
            if (str_contains(strtolower($part), 'barangay')) {
                $savedBarangay = trim(
                    preg_replace('/^barangay\s*/i', '', $part)
                );
            }
        }

        if (empty($savedMunicipality) && isset($locationParts[2])) {
            $savedMunicipality = $locationParts[2];
        }

        if (empty($savedProvince) && isset($locationParts[3])) {
            $savedProvince = $locationParts[3];
        }

        if (empty($savedRegion) && isset($locationParts[4])) {
            $savedRegion = $locationParts[4];
        }
    }

    $puroks = [
        'Centro',
        'Purok 1',
        'Purok 2',
        'Purok 3',
        'Purok 4',
        'Purok 5',
        'Purok 6'
    ];

    $communityCategories = [
        'Street Light',
        'Drainage',
        'Road / Infrastructure',
        'Garbage / Waste',
        'Flooding',
        'Water Supply',
        'Environmental Incident',
        'Noise / Disturbance',
        'Public Facility',
        'Other'
    ];

    $personCategories = [
        'Verbal Abuse / Harassment',
        'Physical Altercation',
        'Threat',
        'Property Dispute',
        'Boundary Dispute',
        'Debt / Financial Dispute',
        'Family / Domestic Dispute',
        'Noise / Disturbance',
        'Damage to Property',
        'Other'
    ];

    $statuses = [
        'Filed',
        'Under Review',
        'Awaiting Captain Approval',
        'Awaiting Captain Availability',
        'Hearing Scheduled',
        'For Further Information',
        'Under Resolution',
        'Resolved',
        'Closed',
        'Disapproved'
    ];

    $statusMap = [
        'Filed' => 's-filed',
        'Under Review' => 's-review',
        'Awaiting Captain Approval' => 's-captain',
        'Awaiting Captain Availability' => 's-captain',
        'Hearing Scheduled' => 's-hearing',
        'For Further Information' => 's-hearing',
        'Under Resolution' => 's-resolution',
        'Resolved' => 's-resolved',
        'Closed' => 's-closed',
        'Disapproved' => 's-disapproved',
    ];

    $currentType   = old('report_type', $reportsComplaint->report_type);
    $currentStatus = old('status', $reportsComplaint->status);

    $showRoute = route(
        'reports-complaints.show',
        ['reports_complaint' => $reportsComplaint->id]
    );

    // Keep the saved category available even if it is not on the standard list.
    $savedCategory = old('category', $reportsComplaint->category);

    $residentData = $residents->map(fn ($r) => [
        'id'      => $r->id,
        'name'    => trim(
            collect([
                $r->first_name,
                $r->middle_name,
                $r->last_name,
                $r->suffix
            ])->filter()->implode(' ')
        ),
        'address' => (string) ($r->address ?? ''),
    ])->values();

    // Open the "area" panel when saved values do not match the standard Poblacion set.
    $areaStandard =
        $savedBarangay === 'Poblacion'
        && $savedMunicipality === 'Bani'
        && $savedProvince === 'Pangasinan'
        && !empty($savedRegion);

    $areaErrors =
        $errors->has('barangay')
        || $errors->has('municipality')
        || $errors->has('province')
        || $errors->has('region');
@endphp

<div class="rc">
    <div class="container-fluid py-4">

        <a href="{{ $showRoute }}" class="back">
            <i class="bi bi-arrow-left"></i>
            Back to record
        </a>

        <div class="head">
            <h1 class="display">Edit report or complaint</h1>
            <p>
                Updating
                <b>{{ $reportsComplaint->record_code }}</b>
            </p>
        </div>

        @if ($errors->any())
            <div class="note" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>

                <div>
                    <strong>Please fix the following:</strong>

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
            action="{{ route('reports-complaints.update', ['reports_complaint' => $reportsComplaint->id]) }}"
            id="editForm"
            novalidate
        >
            @csrf
            @method('PUT')

            <div class="layout">

                <div>

                    {{-- =====================================================
                         1. TYPE AND CATEGORY
                         ===================================================== --}}
                    <section class="step">

                        <div class="step-title">
                            <span class="n">1</span>
                            <h2 class="display">Type and category</h2>
                        </div>

                        <p class="step-hint">
                            Changing the type also changes the category list.
                        </p>

                        <div
                            class="types"
                            role="radiogroup"
                            aria-label="Report type"
                        >

                            <label
                                class="tcard tc-community field"
                                data-name="report_type"
                            >
                                <input
                                    type="radio"
                                    name="report_type"
                                    value="community"
                                    id="typeCommunity"
                                    {{ $currentType === 'community' ? 'checked' : '' }}
                                >

                                <span class="box">

                                    <span class="ic">
                                        <i class="bi bi-buildings"></i>
                                    </span>

                                    <span>
                                        <b>Community or environmental</b>
                                        <small>
                                            Facilities, roads, drainage, environment, utilities.
                                        </small>
                                    </span>

                                    <i class="bi bi-check-circle-fill tick"></i>

                                </span>
                            </label>

                            <label
                                class="tcard tc-person"
                                data-name="report_type"
                            >
                                <input
                                    type="radio"
                                    name="report_type"
                                    value="person"
                                    id="typePerson"
                                    {{ $currentType === 'person' ? 'checked' : '' }}
                                >

                                <span class="box">

                                    <span class="ic">
                                        <i class="bi bi-person-exclamation"></i>
                                    </span>

                                    <span>
                                        <b>Complaint against a person</b>
                                        <small>
                                            A dispute or conflict with another resident.
                                        </small>
                                    </span>

                                    <i class="bi bi-check-circle-fill tick"></i>

                                </span>
                            </label>

                        </div>

                        @error('report_type')
                            <div class="err">{{ $message }}</div>
                        @enderror

                        <div
                            class="grid g2"
                            style="margin-top:1rem"
                        >

                            <div
                                class="field"
                                data-name="category"
                            >
                                <label
                                    class="lbl"
                                    for="category"
                                >
                                    Category
                                    <span class="req">*</span>
                                </label>

                                <select
                                    name="category"
                                    id="category"
                                    required
                                    class="@error('category') bad @enderror"
                                ></select>

                                @error('category')
                                    <div class="err">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="field">

                                <label
                                    class="lbl"
                                    for="record_code"
                                >
                                    Record code
                                </label>

                                <input
                                    type="text"
                                    id="record_code"
                                    value="{{ $reportsComplaint->record_code }}"
                                    readonly
                                >

                            </div>

                            <div
                                class="field full"
                                data-name="title"
                            >

                                <label
                                    class="lbl"
                                    for="title"
                                >
                                    Title
                                    <span class="req">*</span>
                                </label>

                                <input
                                    type="text"
                                    name="title"
                                    id="title"
                                    value="{{ old('title', $reportsComplaint->title) }}"
                                    required
                                    class="@error('title') bad @enderror"
                                >

                                @error('title')
                                    <div class="err">{{ $message }}</div>
                                @enderror

                            </div>

                        </div>
                    </section>


                    {{-- =====================================================
                         2. PEOPLE
                         ===================================================== --}}
                    <section class="step">

                        <div class="step-title">
                            <span class="n">2</span>
                            <h2 class="display">Who is involved?</h2>
                        </div>

                        <p class="step-hint">
                            Type a name or address to search. Matches appear as you type.
                        </p>

                        <div
                            class="field"
                            data-name="complainant_id"
                        >

                            <label
                                class="lbl"
                                for="complainant_id_search"
                            >
                                Complainant or reporter
                                <span class="req">*</span>
                            </label>

                            <div
                                class="combo"
                                data-field="complainant_id"
                                data-old="{{ old('complainant_id', $reportsComplaint->complainant_id) }}"
                                data-exclude="subject_resident_id"
                                data-placeholder="Search the resident who filed…"
                            ></div>

                            <div
                                class="err cerr"
                                hidden
                            ></div>

                            @error('complainant_id')
                                <div class="err">{{ $message }}</div>
                            @enderror

                        </div>


                        <div
                            class="person-box"
                            id="personComplaintSection"
                            hidden
                        >

                            <div
                                class="field"
                                data-name="subject_resident_id"
                            >

                                <label
                                    class="lbl"
                                    for="subject_resident_id_search"
                                >
                                    Person being complained against
                                    <span class="req">*</span>
                                </label>

                                <div
                                    class="combo"
                                    data-field="subject_resident_id"
                                    data-old="{{ old('subject_resident_id', $reportsComplaint->subject_resident_id) }}"
                                    data-exclude="complainant_id"
                                    data-placeholder="Search the respondent…"
                                ></div>

                                <div
                                    class="err cerr"
                                    hidden
                                ></div>

                                @error('subject_resident_id')
                                    <div class="err">{{ $message }}</div>
                                @enderror

                            </div>


                            <label class="sw">

                                <input
                                    type="checkbox"
                                    name="subpoena_requested"
                                    value="1"
                                    id="subpoena_requested"
                                    @checked(
                                        old(
                                            'subpoena_requested',
                                            $reportsComplaint->summons_requested
                                        )
                                    )
                                >

                                <span>
                                    <b>Complainant is requesting a subpoena</b>

                                    <small>
                                        Printing follows the barangay's authorized workflow.
                                    </small>
                                </span>

                            </label>

                        </div>

                    </section>


                    {{-- =====================================================
                         3. INCIDENT
                         ===================================================== --}}
                    <section class="step">

                        <div class="step-title">
                            <span class="n">3</span>
                            <h2 class="display">Incident details</h2>
                        </div>

                        <p class="step-hint">
                            Update when and where it happened, and what happened.
                        </p>

                        <div class="grid g2">

                            <div
                                class="field"
                                data-name="incident_date"
                            >

                                <label
                                    class="lbl"
                                    for="incident_date"
                                >
                                    Incident date
                                    <span class="req">*</span>
                                </label>

                                <input
                                    type="date"
                                    name="incident_date"
                                    id="incident_date"
                                    required
                                    value="{{ old('incident_date', optional($reportsComplaint->incident_date)->format('Y-m-d')) }}"
                                    class="@error('incident_date') bad @enderror"
                                >

                                @error('incident_date')
                                    <div class="err">{{ $message }}</div>
                                @enderror

                            </div>


                            <div
                                class="field"
                                data-name="incident_time"
                            >

                                <label
                                    class="lbl"
                                    for="incident_time"
                                >
                                    Incident time
                                    <span class="opt">(optional)</span>
                                </label>

                                <input
                                    type="time"
                                    name="incident_time"
                                    id="incident_time"
                                    value="{{ old('incident_time', $reportsComplaint->incident_time ? $reportsComplaint->incident_time->format('H:i') : '') }}"
                                    class="@error('incident_time') bad @enderror"
                                >

                                @error('incident_time')
                                    <div class="err">{{ $message }}</div>
                                @enderror

                            </div>


                            <div
                                class="field full"
                                data-name="purok"
                            >

                                <label
                                    class="lbl"
                                    for="purok"
                                >
                                    Purok
                                    <span class="req">*</span>
                                </label>

                                <select
                                    name="purok"
                                    id="purok"
                                    required
                                    class="@error('purok') bad @enderror"
                                >

                                    <option value="">
                                        Select Purok
                                    </option>

                                    @foreach ($puroks as $purok)
                                        <option
                                            value="{{ $purok }}"
                                            @selected($savedPurok === $purok)
                                        >
                                            {{ $purok }}
                                        </option>
                                    @endforeach

                                </select>

                                @error('purok')
                                    <div class="err">{{ $message }}</div>
                                @enderror

                            </div>

                        </div>


                        <div
                            class="locprev"
                            aria-live="polite"
                        >

                            <i class="bi bi-geo-alt-fill"></i>

                            <div>
                                <small>Complete location</small>

                                <b id="locationPreviewText">
                                    {{ $savedLocation ?: 'Select a Purok' }}
                                </b>
                            </div>

                        </div>


                        <details
                            class="more"
                            {{ (!$areaStandard || $areaErrors) ? 'open' : '' }}
                        >

                            <summary>
                                <i class="bi bi-sliders"></i>
                                Barangay, municipality, province and region
                                <i class="bi bi-chevron-down chev"></i>
                            </summary>

                            <div class="inner grid g2">

                                <div
                                    class="field"
                                    data-name="barangay"
                                >

                                    <label
                                        class="lbl"
                                        for="barangay"
                                    >
                                        Barangay
                                        <span class="req">*</span>
                                    </label>

                                    <select
                                        name="barangay"
                                        id="barangay"
                                        required
                                        class="@error('barangay') bad @enderror"
                                    >

                                        <option value="">
                                            Select Barangay
                                        </option>

                                        <option
                                            value="Poblacion"
                                            @selected($savedBarangay === 'Poblacion')
                                        >
                                            Poblacion
                                        </option>

                                    </select>

                                    @error('barangay')
                                        <div class="err">{{ $message }}</div>
                                    @enderror

                                </div>


                                <div
                                    class="field"
                                    data-name="municipality"
                                >

                                    <label
                                        class="lbl"
                                        for="municipality"
                                    >
                                        Municipality / city
                                        <span class="req">*</span>
                                    </label>

                                    <select
                                        name="municipality"
                                        id="municipality"
                                        required
                                        class="@error('municipality') bad @enderror"
                                    >

                                        <option value="">
                                            Select Municipality / City
                                        </option>

                                        <option
                                            value="Bani"
                                            @selected($savedMunicipality === 'Bani')
                                        >
                                            Bani
                                        </option>

                                    </select>

                                    @error('municipality')
                                        <div class="err">{{ $message }}</div>
                                    @enderror

                                </div>


                                <div
                                    class="field"
                                    data-name="province"
                                >

                                    <label
                                        class="lbl"
                                        for="province"
                                    >
                                        Province
                                        <span class="req">*</span>
                                    </label>

                                    <select
                                        name="province"
                                        id="province"
                                        required
                                        class="@error('province') bad @enderror"
                                    >

                                        <option value="">
                                            Select Province
                                        </option>

                                        <option
                                            value="Pangasinan"
                                            @selected($savedProvince === 'Pangasinan')
                                        >
                                            Pangasinan
                                        </option>

                                    </select>

                                    @error('province')
                                        <div class="err">{{ $message }}</div>
                                    @enderror

                                </div>


                                <div
                                    class="field"
                                    data-name="region"
                                >

                                    <label
                                        class="lbl"
                                        for="region"
                                    >
                                        Region
                                        <span class="req">*</span>
                                    </label>

                                    <select
                                        name="region"
                                        id="region"
                                        required
                                        class="@error('region') bad @enderror"
                                    >

                                        <option value="">
                                            Select Region
                                        </option>

                                        <option
                                            value="Region I - Ilocos Region"
                                            @selected($savedRegion === 'Region I - Ilocos Region')
                                        >
                                            Region I - Ilocos Region
                                        </option>

                                        @if (
                                            !empty($savedRegion)
                                            && $savedRegion !== 'Region I - Ilocos Region'
                                        )
                                            <option
                                                value="{{ $savedRegion }}"
                                                selected
                                            >
                                                {{ $savedRegion }}
                                            </option>
                                        @endif

                                    </select>

                                    @error('region')
                                        <div class="err">{{ $message }}</div>
                                    @enderror

                                </div>

                            </div>
                        </details>


                        {{-- Old combined location kept for compatibility --}}
                        <input
                            type="hidden"
                            name="location"
                            id="location"
                            value="{{ $savedLocation }}"
                        >


                        <div
                            class="field"
                            data-name="description"
                            style="margin-top:1rem"
                        >

                            <label
                                class="lbl"
                                for="description"
                            >
                                Detailed description
                                <span class="req">*</span>
                            </label>

                            <textarea
                                name="description"
                                id="description"
                                rows="6"
                                required
                                class="@error('description') bad @enderror"
                            >{{ old('description', $reportsComplaint->description) }}</textarea>

                            @error('description')
                                <div class="err">{{ $message }}</div>
                            @enderror

                        </div>

                    </section>


                    {{-- =====================================================
                         4. STATUS
                         ===================================================== --}}
                    <section class="step">

                        <div class="step-title">
                            <span class="n">4</span>
                            <h2 class="display">Status</h2>
                        </div>

                        <p class="step-hint">
                            Change this only when the case has really moved to another stage.
                        </p>

                        <div
                            class="field"
                            data-name="status"
                        >

                            <div class="status-row">

                                <select
                                    name="status"
                                    id="status"
                                    required
                                    class="@error('status') bad @enderror"
                                    aria-label="Current status"
                                >

                                    @foreach ($statuses as $status)
                                        <option
                                            value="{{ $status }}"
                                            @selected($currentStatus === $status)
                                        >
                                            {{ $status }}
                                        </option>
                                    @endforeach

                                </select>

                                <span
                                    class="pill {{ $statusMap[$currentStatus] ?? 's-filed' }}"
                                    id="statusPill"
                                >
                                    {{ $currentStatus }}
                                </span>

                            </div>

                            @error('status')
                                <div class="err">{{ $message }}</div>
                            @enderror

                            <div
                                class="status-note"
                                id="statusNote"
                            >
                                <i class="bi bi-info-circle"></i>

                                <span>
                                    The status stays
                                    <b>{{ $reportsComplaint->status }}</b>
                                    unless you choose another one before saving.
                                </span>
                            </div>

                        </div>

                    </section>


                    {{-- =====================================================
                         5. ASSIGNMENT
                         ===================================================== --}}
                    <section class="step">

                        <div class="step-title">
                            <span class="n">5</span>
                            <h2 class="display">Assignment and remarks</h2>
                        </div>

                        <p class="step-hint">
                            Who is handling this, and any internal notes.
                        </p>

                        <div class="grid g2">

                            <div
                                class="field"
                                data-name="assigned_to"
                            >

                                <label
                                    class="lbl"
                                    for="assigned_to"
                                >
                                    Assigned personnel
                                </label>

                                <select
                                    name="assigned_to"
                                    id="assigned_to"
                                    class="@error('assigned_to') bad @enderror"
                                >

                                    <option value="">
                                        Select personnel
                                    </option>

                                    @foreach ($officials as $person)
                                        <option
                                            value="{{ $person->id }}"
                                            @selected(
                                                old(
                                                    'assigned_to',
                                                    $reportsComplaint->assigned_to
                                                ) == $person->id
                                            )
                                        >
                                            {{ $person->name }}
                                            ({{ ucfirst($person->role) }})
                                        </option>
                                    @endforeach

                                </select>

                                @error('assigned_to')
                                    <div class="err">{{ $message }}</div>
                                @enderror

                            </div>


                            <div
                                class="field full"
                                data-name="remarks"
                            >

                                <label
                                    class="lbl"
                                    for="remarks"
                                >
                                    Remarks
                                </label>

                                <textarea
                                    name="remarks"
                                    id="remarks"
                                    rows="4"
                                    class="@error('remarks') bad @enderror"
                                    placeholder="Add internal remarks or processing notes…"
                                >{{ old('remarks', $reportsComplaint->remarks) }}</textarea>

                                @error('remarks')
                                    <div class="err">{{ $message }}</div>
                                @enderror

                            </div>

                        </div>

                    </section>

                </div>


                {{-- =========================================================
                     SIDE PANEL
                     ========================================================= --}}
                <aside class="side">

                    <div class="summary">

                        <div class="summary-head display">
                            This record
                        </div>

                        <dl>

                            <div>
                                <dt>Record code</dt>
                                <dd>{{ $reportsComplaint->record_code }}</dd>
                            </div>

                            <div>
                                <dt>Saved status</dt>

                                <dd>
                                    <span
                                        class="pill {{ $statusMap[$reportsComplaint->status] ?? 's-filed' }}"
                                    >
                                        {{ $reportsComplaint->status }}
                                    </span>
                                </dd>
                            </div>

                            @if ($reportsComplaint->created_at)
                                <div>
                                    <dt>Filed</dt>
                                    <dd>
                                        {{ $reportsComplaint->created_at->format('M d, Y') }}
                                    </dd>
                                </div>
                            @endif

                        </dl>


                        <div
                            class="changes"
                            aria-live="polite"
                        >

                            <div
                                class="count clean"
                                id="changeCount"
                            >
                                No changes yet
                            </div>

                            <ul id="changeList"></ul>

                        </div>


                        <div class="actions">

                            <button
                                type="submit"
                                class="btn-go"
                            >
                                <i class="bi bi-save"></i>
                                Save changes
                            </button>

                            <a
                                href="{{ $showRoute }}"
                                class="btn-cancel"
                            >
                                Cancel
                            </a>

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
document.addEventListener('DOMContentLoaded', function () {

    const residents = @json($residentData);

    const categories = {
        community: @json($communityCategories),
        person: @json($personCategories)
    };

    const savedCategory = @json($savedCategory);

    const originalStatus = @json($reportsComplaint->status);

    const statusMap = @json($statusMap);

    const $ = (id) => document.getElementById(id);

    const form = $('editForm');

    const isPerson = () => $('typePerson').checked;


    /* =========================================================
       Helpers
       ========================================================= */

    const esc = (s) =>
        String(s).replace(
            /[&<>"']/g,
            (c) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            }[c])
        );

    const words = (s) =>
        s
            .toLowerCase()
            .split(/[\s,.\-\/]+/)
            .filter(Boolean);

    const initials = (n) => {
        const w = n
            .trim()
            .split(/\s+/)
            .filter(Boolean);

        return (
            (w[0] || '?')[0]
            +
            (w.length > 1 ? w[w.length - 1][0] : '')
        ).toUpperCase();
    };

    function highlight(text, tokens) {

        let out = esc(text);

        tokens.forEach((t) => {

            out = out.replace(
                new RegExp(
                    '(^|[\\s,.\\-\\/])(' +
                    t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') +
                    ')',
                    'gi'
                ),
                '$1<mark>$2</mark>'
            );

        });

        return out;
    }


    /* =========================================================
       Resident combobox
       ========================================================= */

    const combos = {};

    document.querySelectorAll('.combo').forEach(function (root) {

        const name = root.dataset.field;

        root.innerHTML =
            '<input type="hidden" name="' + name + '" value="">' +

            '<div class="picked" hidden>' +
                '<span class="avatar" aria-hidden="true"></span>' +
                '<div class="grow">' +
                    '<b></b>' +
                    '<small></small>' +
                '</div>' +
                '<button type="button" class="link-btn">Change</button>' +
            '</div>' +

            '<div class="search-wrap">' +
                '<i class="bi bi-search"></i>' +

                '<input ' +
                    'type="text" ' +
                    'class="q" ' +
                    'id="' + name + '_search" ' +
                    'autocomplete="off" ' +
                    'placeholder="' + esc(root.dataset.placeholder) + '" ' +
                    'role="combobox" ' +
                    'aria-expanded="false" ' +
                    'aria-controls="' + name + '_list" ' +
                    'aria-autocomplete="list">' +

                '<ul ' +
                    'class="list" ' +
                    'id="' + name + '_list" ' +
                    'role="listbox" ' +
                    'hidden>' +
                '</ul>' +

            '</div>';


        const hidden =
            root.querySelector('input[type=hidden]');

        const input =
            root.querySelector('.q');

        const list =
            root.querySelector('.list');

        const picked =
            root.querySelector('.picked');

        const wrap =
            root.querySelector('.search-wrap');

        let matches = [];

        let active = 0;


        const otherId = () => {
            const o = combos[root.dataset.exclude];

            return o ? o.value() : '';
        };


        function filter(q) {

            const tokens = words(q);

            const ex = String(otherId());

            let rows = residents.filter(
                (r) => String(r.id) !== ex
            );


            if (tokens.length) {

                rows = rows.filter((r) => {

                    const w =
                        words(r.name + ' ' + r.address);

                    return tokens.every(
                        (t) =>
                            w.some(
                                (x) => x.startsWith(t)
                            )
                    );

                });


                const f =
                    q.trim().toLowerCase();

                rows.sort(
                    (a, b) =>
                        (
                            b.name
                                .toLowerCase()
                                .startsWith(f)
                                ? 1
                                : 0
                        )
                        -
                        (
                            a.name
                                .toLowerCase()
                                .startsWith(f)
                                ? 1
                                : 0
                        )
                );
            }

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

            active = 0;


            list.innerHTML = rows.length
                ? rows
                    .map(
                        (r, i) =>
                            '<li ' +
                                'class="opt" ' +
                                'role="option" ' +
                                'data-i="' + i + '" ' +
                                'aria-selected="' +
                                    (i === 0) +
                                '">' +

                                '<span class="avatar" aria-hidden="true">' +
                                    esc(initials(r.name)) +
                                '</span>' +

                                '<span>' +

                                    '<b>' +
                                        highlight(r.name, tokens) +
                                    '</b>' +

                                    (
                                        r.address
                                            ? '<small>' +
                                                highlight(r.address, tokens) +
                                              '</small>'
                                            : ''
                                    ) +

                                '</span>' +

                            '</li>'
                    )
                    .join('')
                +
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
                )
                : '<li class="list-empty">' +
                    'No resident found. Check the spelling or try the address.' +
                  '</li>';


            toggle(true);
        }


        function toggle(open) {

            list.hidden = !open;

            input.setAttribute(
                'aria-expanded',
                open
            );
        }


        function setActive(i) {

            const o =
                list.querySelectorAll('.opt');

            if (!o.length) {
                return;
            }

            active =
                (i + o.length) % o.length;

            o.forEach(
                (el, k) =>
                    el.setAttribute(
                        'aria-selected',
                        k === active
                    )
            );

            o[active].scrollIntoView({
                block: 'nearest'
            });
        }


        function choose(r, silent) {

            hidden.value = r.id;

            picked.querySelector('b').textContent =
                r.name;

            picked.querySelector('small').textContent =
                r.address || 'No address on file';

            picked.querySelector('.avatar').textContent =
                initials(r.name);

            picked.hidden = false;

            wrap.hidden = true;

            root.parentElement
                .querySelector('.cerr')
                .hidden = true;

            toggle(false);

            if (!silent) {
                track();
            }
        }


        function clear() {

            hidden.value = '';

            input.value = '';

            picked.hidden = true;

            wrap.hidden = false;

            input.focus();

            render();

            track();
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
                    () => toggle(false),
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
                        : setActive(active + 1);

                } else if (e.key === 'ArrowUp') {

                    e.preventDefault();

                    setActive(active - 1);

                } else if (e.key === 'Enter') {

                    e.preventDefault();

                    if (
                        !list.hidden
                        && matches[active]
                    ) {
                        choose(matches[active]);
                    }

                } else if (e.key === 'Escape') {

                    toggle(false);

                }

            }
        );


        list.addEventListener(
            'mousedown',
            (e) => {

                const li =
                    e.target.closest('.opt');

                if (li) {

                    e.preventDefault();

                    choose(
                        matches[
                            +li.dataset.i
                        ]
                    );
                }
            }
        );


        picked
            .querySelector('button')
            .addEventListener(
                'click',
                clear
            );


        combos[name] = {

            root,

            input,

            value: () =>
                hidden.value,

            reset: () => {

                hidden.value = '';

                input.value = '';

                picked.hidden = true;

                wrap.hidden = false;
            },

            set: (id) => {

                const r =
                    residents.find(
                        (x) =>
                            String(x.id) === String(id)
                    );

                if (r) {
                    choose(r, true);
                }
            }

        };

    });


    /* =========================================================
       Category list follows the type
       ========================================================= */

    function updateCategories(keep) {

        const cat = $('category');

        const type =
            isPerson()
                ? 'person'
                : 'community';

        const list =
            categories[type].slice();

        const wanted =
            keep !== undefined
                ? keep
                : cat.value;


        // Keep an existing, non-standard category available
        // so it is never lost silently.
        if (
            keep !== undefined
            && wanted
            && !list.includes(wanted)
            && !categories.community.includes(wanted)
            && !categories.person.includes(wanted)
        ) {
            list.push(wanted);
        }


        cat.innerHTML =
            '<option value="">Select category</option>' +

            list
                .map(
                    (i) =>
                        '<option value="' +
                        esc(i) +
                        '"' +
                        (
                            i === wanted
                                ? ' selected'
                                : ''
                        ) +
                        '>' +
                        esc(i) +
                        '</option>'
                )
                .join('');
    }


    function updatePersonSection(initial) {

        const person =
            isPerson();

        $('personComplaintSection').hidden =
            !person;


        if (!person && !initial) {

            combos.subject_resident_id.reset();

            $('subpoena_requested').checked =
                false;
        }


        if (!person && initial) {
            /*
             * Keep values until the user switches type.
             */
        }
    }


    /* =========================================================
       Location
       ========================================================= */

    function updateLocation() {

        const v = (id) =>
            $(id).value || '';

        const text = [
            v('purok'),

            v('barangay')
                && 'Barangay ' + v('barangay'),

            v('municipality'),

            v('province'),

            v('region')
        ]
            .filter(Boolean)
            .join(', ');


        $('location').value =
            text;

        $('locationPreviewText').textContent =
            text || 'Select a Purok';
    }


    [
        'purok',
        'barangay',
        'municipality',
        'province',
        'region'
    ].forEach(
        (id) =>
            $(id).addEventListener(
                'change',
                updateLocation
            )
    );


    /* =========================================================
       Status preview
       ========================================================= */

    function updateStatus() {

        const s =
            $('status').value;

        const pill =
            $('statusPill');

        const note =
            $('statusNote');


        pill.className =
            'pill ' +
            (statusMap[s] || 's-filed');

        pill.textContent =
            s;


        if (s === originalStatus) {

            note.className =
                'status-note';

            note.innerHTML =
                '<i class="bi bi-info-circle"></i>' +
                '<span>' +
                    'The status stays <b>' +
                        esc(originalStatus) +
                    '</b> unless you choose another one before saving.' +
                '</span>';

        } else {

            note.className =
                'status-note warn';

            note.innerHTML =
                '<i class="bi bi-exclamation-circle"></i>' +
                '<span>' +
                    'The status will change from <b>' +
                        esc(originalStatus) +
                    '</b> to <b>' +
                        esc(s) +
                    '</b> when you save.' +
                '</span>';
        }
    }


    $('status').addEventListener(
        'change',
        updateStatus
    );


    /* =========================================================
       Change tracking
       ========================================================= */

    const labels = {

        report_type: 'Type',

        category: 'Category',

        title: 'Title',

        complainant_id: 'Complainant',

        subject_resident_id: 'Respondent',

        subpoena_requested: 'Subpoena',

        incident_date: 'Incident date',

        incident_time: 'Incident time',

        purok: 'Purok',

        barangay: 'Barangay',

        municipality: 'Municipality',

        province: 'Province',

        region: 'Region',

        description: 'Description',

        status: 'Status',

        assigned_to: 'Assigned to',

        remarks: 'Remarks'
    };


    const snap = () => {

        const o = {};

        new FormData(form).forEach(
            (v, k) => {

                if (labels[k]) {
                    o[k] = String(v);
                }

            }
        );

        return o;
    };


    let baseline = {};

    let submitting = false;


    function track() {

        const now =
            snap();

        const changed = [];


        Object.keys(labels).forEach(
            (k) => {

                if (
                    (now[k] || '') !==
                    (baseline[k] || '')
                ) {
                    changed.push(k);
                }

            }
        );


        form
            .querySelectorAll(
                '.field[data-name], label.tcard[data-name]'
            )
            .forEach(
                (f) =>
                    f.classList.toggle(
                        'changed',
                        changed.includes(
                            f.dataset.name
                        )
                    )
            );


        const c =
            $('changeCount');


        c.textContent =
            changed.length
                ? changed.length +
                    (
                        changed.length === 1
                            ? ' field changed'
                            : ' fields changed'
                    )
                : 'No changes yet';


        c.classList.toggle(
            'clean',
            !changed.length
        );


        $('changeList').innerHTML =
            changed
                .map(
                    (k) =>
                        '<li>' +
                        esc(labels[k]) +
                        '</li>'
                )
                .join('');


        return changed.length;
    }


    form.addEventListener(
        'input',
        track
    );

    form.addEventListener(
        'change',
        track
    );


    window.addEventListener(
        'beforeunload',
        (e) => {

            if (
                !submitting
                && track()
            ) {

                e.preventDefault();

                e.returnValue = '';
            }

        }
    );


    /* =========================================================
       Report type changes
       ========================================================= */

    $('typeCommunity').addEventListener(
        'change',
        () => {

            updateCategories();

            updatePersonSection();

            track();
        }
    );


    $('typePerson').addEventListener(
        'change',
        () => {

            updateCategories();

            updatePersonSection();

            track();
        }
    );


    /* =========================================================
       Submit checks
       ========================================================= */

    form.addEventListener(
        'submit',
        function (e) {

            for (
                const [
                    name,
                    required
                ]
                of [
                    ['complainant_id', true],
                    ['subject_resident_id', isPerson()]
                ]
            ) {

                const c =
                    combos[name];


                if (
                    required
                    && !c.value()
                ) {

                    e.preventDefault();


                    const msg =
                        c.root.parentElement
                            .querySelector('.cerr');


                    msg.textContent =
                        'Please choose a resident from the list.';

                    msg.hidden =
                        false;


                    c.input.focus();

                    return;
                }
            }


            if (!form.checkValidity()) {

                e.preventDefault();

                form.reportValidity();

                return;
            }


            submitting = true;
        }
    );


    /* =========================================================
       Initial state
       ========================================================= */

    [
        'complainant_id',
        'subject_resident_id'
    ].forEach(
        (n) => {

            const v =
                combos[n].root.dataset.old;

            if (v) {
                combos[n].set(v);
            }

        }
    );


    updateCategories(
        savedCategory
    );

    updatePersonSection(
        true
    );

    updateStatus();

    baseline =
        snap();

    track();

});
</script>
@endpush