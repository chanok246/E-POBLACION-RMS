
@extends('layouts.app')

@section('title', 'Create Relief Operation | e-Poblacion')

@section('content')

@php
    $assistanceModes = [
        'in_kind' => [
            'label' => 'In-Kind Relief Distribution',
            'description' => 'Physical goods such as family food packs, rice, hygiene kits, and water.',
            'icon' => 'bi-box-seam',
            'badge' => 'QR Voucher',
        ],
        'cash' => [
            'label' => 'Emergency Cash / Financial Assistance',
            'description' => 'Cash aid, educational assistance, or other monetary support.',
            'icon' => 'bi-cash-coin',
            'badge' => 'QR + Payout',
        ],
        'priority' => [
            'label' => 'Priority Sector Social Assistance',
            'description' => 'Targeted assistance for groups such as senior citizens, PWDs, students, or indigent families.',
            'icon' => 'bi-people',
            'badge' => 'Targeted',
        ],
        'service' => [
            'label' => 'Service / Social Assistance',
            'description' => 'Medical missions, dental missions, vaccination, or consultation.',
            'icon' => 'bi-heart-pulse',
            'badge' => 'No Voucher',
        ],
    ];

    $targetGroups = [
        'all' => [
            'label' => 'All Residents',
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
            'description' => 'Choose one or more school levels below.',
            'icon' => 'bi-mortarboard',
        ],
        'senior_citizens' => [
            'label' => 'Senior Citizens',
            'description' => 'Residents aged 60 and above based on birth date.',
            'icon' => 'bi-person-wheelchair',
        ],
        'pwd' => [
            'label' => 'Persons with Disabilities (PWD)',
            'description' => 'Residents recorded as persons with disabilities.',
            'icon' => 'bi-universal-access',
        ],
        'solo_parent' => [
            'label' => 'Solo Parents',
            'description' => 'Residents recorded as solo parents.',
            'icon' => 'bi-person-hearts',
        ],
        'four_ps' => [
            'label' => '4Ps Beneficiaries',
            'description' => 'Households recorded as 4Ps beneficiaries.',
            'icon' => 'bi-house-heart',
        ],
        'indigent' => [
            'label' => 'Indigent / Low-Income',
            'description' => 'Households recorded as indigent or low-income.',
            'icon' => 'bi-house-exclamation',
        ],
    ];

    $selectedTargetGroup = old('target_group', 'all');
    $selectedStudentLevels = (array) old('student_levels', []);
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">

<style>
.rf {
    /* Neutral + green color system */
    --ink:#26352c;
    --ink-2:#637268;
    --faint:#929d95;

    --line:#dce5de;
    --line-strong:#c5d3c8;
    --wash:#f3f7f4;

    --card:#fff;
    --field:#fff;

    --ube:#32804e;
    --ube-soft:rgba(50,128,78,.10);
    --on-brand:#fff;

    --mango:#d6a83c;
    --mango-soft:#fff6df;
    --mango-ink:#806018;

    /* Red is reserved for actual validation errors */
    --red:#c9302c;
    --red-soft:#fde3e3;
    --red-ink:#8f2320;

    --green:#32804e;
    --green-soft:#e8f4eb;
    --green-ink:#27643b;

    --grey:#7c8980;

    --shadow:0 2px 4px rgba(35,65,45,.035),0 10px 24px rgba(35,65,45,.045);

    width:100%;
    max-width:1680px;
    margin:0 auto;
    color:var(--ink);
    font-family:'Figtree',system-ui,-apple-system,'Segoe UI',sans-serif;
}

html[data-theme="dark"] .rf {
    --ink:#edf5ef;
    --ink-2:#b7c6ba;
    --faint:#91a196;

    --line:#35483b;
    --line-strong:#49614f;
    --wash:#202c24;

    --card:#1b261f;
    --field:#151e18;

    --ube:#82c997;
    --ube-soft:rgba(130,201,151,.13);
    --on-brand:#14251a;

    --mango:#e2bd63;
    --mango-soft:#39321e;
    --mango-ink:#e8ca82;

    --red:#ff8a95;
    --red-soft:#401a1d;
    --red-ink:#ffc2c8;

    --green:#82c997;
    --green-soft:#1f3a27;
    --green-ink:#b9efc1;

    --grey:#a8b8aa;
    --shadow:none;
}

.rf *, .rf *::before, .rf *::after {
    box-sizing:border-box;
}

.rf h1,.rf h2,.rf h3,.rf p,.rf ol,.rf ul,.rf dl,.rf dd {
    margin:0;
    padding:0;
}

.rf a:focus-visible,
.rf button:focus-visible {
    outline:3px solid var(--green);
    outline-offset:3px;
}

.rf-display {
    font-family:'Unbounded','Figtree',system-ui,sans-serif;
    letter-spacing:-.02em;
}

.rf-crumb {
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:8px;
    margin-bottom:12px;
    font-size:13px;
    color:var(--ink-2);
}

.rf-crumb a {
    color:var(--ink-2);
    text-decoration:none;
    font-weight:700;
}

.rf-crumb a:hover {
    color:var(--green);
    text-decoration:underline;
    text-underline-offset:3px;
}

.rf-hero {
    margin-bottom:26px;
}

.rf-hero h1 {
    font-size:clamp(22px,2.4vw,30px);
    line-height:1.2;
    font-weight:800;
    color:var(--ink);
}

.rf-hero p {
    margin-top:10px;
    max-width:760px;
    color:var(--ink-2);
    font-size:14.5px;
    line-height:1.6;
}

.rf-grid {
    display:grid;
    grid-template-columns:minmax(0,2fr) minmax(300px,1fr);
    gap:24px;
    align-items:start;
}

.rf-main {
    min-width:0;
}

.rf-aside {
    position:sticky;
    top:16px;
    display:grid;
    gap:16px;
    min-width:0;
}

/* Validation errors */
.rf-errors {
    margin-bottom:22px;
    padding:16px 18px;
    border-radius:14px;
    border:1px solid var(--red);
    background:var(--red-soft);
    color:var(--red-ink);
    font-size:13.5px;
}

.rf-errors b {
    display:flex;
    align-items:center;
    gap:8px;
    margin-bottom:6px;
    color:var(--red);
    font-size:14px;
}

.rf-errors ul {
    padding-left:22px;
    list-style:disc;
    display:grid;
    gap:3px;
}

/* Sections */
.rf-sec {
    padding:26px 28px 28px;
    border:1px solid var(--line);
    border-radius:18px;
    background:var(--card);
    box-shadow:var(--shadow);
    margin-bottom:18px;
}

.rf-sec-head {
    display:flex;
    align-items:center;
    gap:13px;
    margin-bottom:22px;
}

.rf-sec-ic {
    flex:none;
    width:42px;
    height:42px;
    border-radius:13px;
    display:grid;
    place-items:center;
    background:var(--green-soft);
    color:var(--green);
    font-size:18px;
}

.rf-sec-head h2 {
    font-size:16px;
    font-weight:800;
    color:var(--ink);
}

.rf-sec-head p {
    margin-top:3px;
    font-size:12.5px;
    color:var(--ink-2);
}

.rf-hint {
    margin:-6px 0 16px;
    font-size:13px;
    line-height:1.55;
    color:var(--ink-2);
}

.rf fieldset {
    border:0;
    padding:0;
    margin:0;
    min-width:0;
}

.rf legend {
    float:none;
    width:auto;
    padding:0;
    margin:0 0 10px;
    font-size:13.5px;
    font-weight:700;
    color:var(--ink);
}

.rf .visually-hidden {
    position:absolute!important;
    width:1px;
    height:1px;
    padding:0;
    margin:-1px;
    overflow:hidden;
    clip:rect(0,0,0,0);
    white-space:nowrap;
    border:0;
}

/* Form fields */
.rf-fields {
    display:grid;
    grid-template-columns:repeat(12,minmax(0,1fr));
    gap:18px 16px;
}

.rf-f {
    grid-column:span 12;
    min-width:0;
}

.rf-label {
    display:flex;
    flex-wrap:wrap;
    align-items:center;
    gap:8px;
    margin-bottom:7px;
    font-size:13.5px;
    font-weight:700;
    color:var(--ink);
}

.rf-req,.rf-opt {
    padding:2px 8px;
    border-radius:999px;
    font-size:11px;
    font-weight:700;
    line-height:1.5;
}

.rf-req {
    background:var(--green-soft);
    color:var(--green-ink);
}

.rf-opt {
    background:var(--wash);
    color:var(--ink-2);
    border:1px solid var(--line);
}

/* Neutral gray default borders */
.rf .form-control,
.rf .form-select {
    width:100%;
    padding:11px 14px;
    border:1.5px solid var(--line-strong);
    border-radius:12px;
    background-color:var(--field);
    color:var(--ink);
    font-size:14px;
    font-family:inherit;
    box-shadow:none;
    min-height:46px;
    transition:border-color .18s ease,box-shadow .18s ease,background-color .18s ease;
}

.rf textarea.form-control {
    resize:vertical;
    min-height:100px;
    line-height:1.5;
}

.rf .form-control::placeholder {
    color:var(--faint);
    opacity:1;
}

/* Green focus border and ring */
.rf .form-control:focus,
.rf .form-select:focus {
    background-color:var(--field);
    color:var(--ink);
    border-color:var(--green);
    box-shadow:0 0 0 4px var(--green-soft);
    outline:0;
}

/* Red only when invalid */
.rf .form-control.is-invalid,
.rf .form-select.is-invalid {
    border-color:var(--red);
    background-image:none;
}

.rf .form-control.is-invalid:focus,
.rf .form-select.is-invalid:focus {
    border-color:var(--red);
    box-shadow:0 0 0 4px var(--red-soft);
}

.rf .invalid-feedback,
.rf-msg {
    display:block;
    margin-top:6px;
    font-size:12.5px;
    font-weight:600;
    color:var(--red);
}

.rf-msg[hidden],
.rf .invalid-feedback[hidden] {
    display:none!important;
}

.rf .form-text {
    margin-top:7px;
    font-size:12.5px;
    color:var(--ink-2);
}

html[data-theme="dark"] .rf .form-control,
html[data-theme="dark"] .rf .form-select {
    color-scheme:dark;
}

/* Assistance mode cards */
.rf-mode-grid {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:14px;
}

.rf-tiles {
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:12px;
}

.rf-mode,
.rf-tile {
    position:relative;
    display:block;
    cursor:pointer;
    margin:0;
    min-width:0;
}

.rf-mode input,
.rf-tile input {
    position:absolute;
    inset:0;
    width:100%;
    height:100%;
    margin:0;
    opacity:0;
    cursor:pointer;
    z-index:1;
}

.rf-mode-body {
    position:relative;
    display:flex;
    align-items:flex-start;
    gap:13px;
    height:100%;
    min-height:138px;
    padding:17px 42px 17px 16px;
    border:1.5px solid var(--line-strong);
    border-radius:15px;
    background:var(--card);
    transition:border-color .15s,background .15s,transform .15s;
}

.rf-mode:hover .rf-mode-body {
    border-color:var(--green);
    transform:translateY(-1px);
}

.rf-mode input:checked+.rf-mode-body,
.rf-tile input:checked+.rf-tile-body {
    border-color:var(--green);
    background:var(--green-soft);
    box-shadow:inset 0 0 0 1px var(--green);
}

.rf-mode input:focus-visible+.rf-mode-body,
.rf-tile input:focus-visible+.rf-tile-body {
    outline:3px solid var(--green);
    outline-offset:3px;
}

.rf-mode-icon {
    flex:none;
    width:42px;
    height:42px;
    border-radius:12px;
    display:grid;
    place-items:center;
    background:var(--wash);
    color:var(--green);
    font-size:19px;
}

.rf-mode input:checked+.rf-mode-body .rf-mode-icon,
.rf-tile input:checked+.rf-tile-body .rf-tile-ic {
    background:var(--green);
    color:#fff;
}

.rf-mode-content {
    min-width:0;
    flex:1;
}

.rf-mode-title,
.rf-mode-description,
.rf-mode-badge,
.rf-tile-t,
.rf-tile-d {
    display:block;
}

.rf-mode-title {
    font-size:14px;
    font-weight:800;
    line-height:1.35;
    color:var(--ink);
}

.rf-mode-description {
    margin-top:5px;
    font-size:12.5px;
    line-height:1.45;
    color:var(--ink-2);
}

.rf-mode-badge {
    display:inline-block;
    margin-top:10px;
    padding:4px 10px;
    border-radius:999px;
    background:var(--green-soft);
    color:var(--green-ink);
    border:1px solid var(--line);
    font-size:11px;
    font-weight:700;
}

.rf-mode-tick {
    position:absolute;
    top:14px;
    right:14px;
    margin:0;
}

/* Target group cards */
.rf-tile-body {
    display:flex;
    align-items:flex-start;
    gap:11px;
    height:100%;
    padding:14px;
    border:1.5px solid var(--line-strong);
    border-radius:14px;
    background:var(--card);
    transition:border-color .15s,background .15s;
}

.rf-tile:hover .rf-tile-body {
    border-color:var(--green);
}

.rf-tile-ic {
    flex:none;
    width:36px;
    height:36px;
    border-radius:10px;
    display:grid;
    place-items:center;
    background:var(--wash);
    color:var(--green);
    font-size:16px;
}

.rf-tile-text {
    min-width:0;
    flex:1;
}

.rf-tile-t {
    font-size:13.5px;
    font-weight:800;
    line-height:1.3;
    color:var(--ink);
}

.rf-tile-d {
    margin-top:4px;
    font-size:12px;
    line-height:1.45;
    color:var(--ink-2);
}

/* Selection check indicator */
.rf-tick {
    flex:none;
    margin-left:auto;
    width:20px;
    height:20px;
    border-radius:50%;
    border:1.5px solid var(--line-strong);
    background:var(--card);
    position:relative;
}

.rf-mode input:checked+.rf-mode-body .rf-tick,
.rf-tile input:checked+.rf-tile-body .rf-tick {
    background:var(--green);
    border-color:var(--green);
}

.rf-mode input:checked+.rf-mode-body .rf-tick::after,
.rf-tile input:checked+.rf-tile-body .rf-tick::after {
    content:'';
    position:absolute;
    left:6px;
    top:2.5px;
    width:5px;
    height:9px;
    border:solid #fff;
    border-width:0 2px 2px 0;
    transform:rotate(45deg);
}

/* Student level options */
.rf-student-levels {
    margin-top:15px;
    padding:17px;
    border:1px solid var(--line);
    border-radius:14px;
    background:var(--wash);
}

.rf-student-levels[hidden] {
    display:none!important;
}

.rf-student-levels-title {
    display:block;
    margin-bottom:4px;
    color:var(--ink);
    font-size:13.5px;
    font-weight:800;
}

.rf-student-levels-help {
    margin:0 0 12px;
    color:var(--ink-2);
    font-size:12.5px;
    line-height:1.45;
}

.rf-level-options {
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:9px;
}

.rf-level-option {
    display:flex;
    align-items:center;
    gap:9px;
    min-height:46px;
    padding:10px 12px;
    border:1px solid var(--line-strong);
    border-radius:10px;
    background:var(--card);
    color:var(--ink);
    font-size:13px;
    font-weight:600;
    cursor:pointer;
    transition:border-color .15s,background .15s;
}

.rf-level-option input {
    flex:none;
    accent-color:var(--green);
    width:17px;
    height:17px;
    margin:0;
    cursor:pointer;
}

.rf-level-option:has(input:checked) {
    border-color:var(--green);
    background:var(--green-soft);
}

.rf-level-option:has(input:focus-visible) {
    outline:3px solid var(--green);
    outline-offset:3px;
}

/* Mode information notice */
.rf-mode-notice {
    display:flex;
    gap:12px;
    padding:14px 16px;
    margin-top:16px;
    border-radius:14px;
    border:1px solid var(--line);
    background:var(--green-soft);
    color:var(--green-ink);
    font-size:12.5px;
    line-height:1.55;
}

.rf-mode-notice>i {
    flex:none;
    font-size:17px;
    margin-top:1px;
    color:var(--green);
}

.rf-mode-notice b {
    display:block;
    font-size:13px;
    margin-bottom:2px;
}

/* Summary preview */
.rf-pv {
    border:1px solid var(--line);
    border-radius:18px;
    background:var(--card);
    box-shadow:var(--shadow);
    overflow:hidden;
}

.rf-pv-head {
    padding:15px 19px;
    background:var(--wash);
    border-bottom:1px dashed var(--line-strong);
    font-size:12.5px;
    color:var(--ink-2);
    display:flex;
    flex-wrap:wrap;
    justify-content:space-between;
    gap:6px 10px;
}

.rf-pv-head b {
    color:var(--ink);
    font-size:13.5px;
}

.rf-pv-top {
    display:flex;
    gap:14px;
    align-items:center;
    padding:20px 19px 16px;
}

.rf-pv-top>div:last-child {
    min-width:0;
}

.rf-date {
    flex:none;
    width:66px;
    padding:9px 4px 8px;
    text-align:center;
    border-radius:14px;
    background:var(--wash);
    border:1px solid var(--line);
}

.rf-date b {
    display:block;
    font-family:'Unbounded','Figtree',sans-serif;
    font-size:22px;
    font-weight:700;
    line-height:1.05;
    color:var(--ink);
}

.rf-date span {
    display:block;
    margin-top:4px;
    font-size:11.5px;
    font-weight:600;
    color:var(--ink-2);
}

.rf-date.is-none b,
.rf-pv-name.is-empty,
.rf-pv-row dd.is-empty {
    color:var(--faint);
}

.rf-pv-name {
    font-size:16px;
    font-weight:800;
    line-height:1.35;
    color:var(--ink);
    overflow-wrap:anywhere;
}

.rf-pv-type {
    display:inline-block;
    margin-top:7px;
    padding:4px 11px;
    border-radius:999px;
    background:var(--green-soft);
    color:var(--green-ink);
    font-size:12px;
    font-weight:700;
}

.rf-pv-type[hidden] {
    display:none!important;
}

.rf-pv-rows {
    padding:4px 19px 19px;
}

.rf-pv-row {
    padding:11px 0;
    border-top:1px solid var(--line);
}

.rf-pv-row dt {
    font-size:12px;
    font-weight:600;
    color:var(--grey);
}

.rf-pv-row dd {
    margin-top:4px;
    font-size:13.5px;
    font-weight:700;
    line-height:1.4;
    color:var(--ink);
    overflow-wrap:anywhere;
}

/* Green create button */
.rf-submit {
    display:flex;
    align-items:center;
    justify-content:center;
    gap:9px;
    width:100%;
    min-height:50px;
    padding:14px 20px;
    border:1px solid #267442;
    border-radius:12px;
    background:#2f8550;
    color:#fff;
    font:inherit;
    font-size:15px;
    font-weight:800;
    cursor:pointer;
    box-shadow:0 5px 14px rgba(47,133,80,.18);
    transition:background .18s ease,border-color .18s ease,box-shadow .18s ease,transform .18s ease;
}

.rf-submit:hover {
    background:#267442;
    border-color:#1f6337;
    color:#fff;
    box-shadow:0 7px 17px rgba(47,133,80,.24);
}

.rf-submit:active {
    background:#205f37;
    transform:scale(.99);
}

.rf-submit:focus-visible {
    outline:3px solid #86c99b;
    outline-offset:3px;
}

.rf-submit.is-busy {
    opacity:.75;
    cursor:progress;
    pointer-events:none;
    box-shadow:none;
}

html[data-theme="dark"] .rf-submit {
    background:#276b40;
    border-color:#398454;
    color:#fff;
    box-shadow:none;
}

html[data-theme="dark"] .rf-submit:hover {
    background:#32804e;
    border-color:#55a46c;
    color:#fff;
}

html[data-theme="dark"] .rf-submit:active {
    background:#205b36;
}

/* Cancel and note */
.rf-cancel {
    display:flex;
    align-items:center;
    justify-content:center;
    min-height:46px;
    margin-top:9px;
    padding:11px;
    border:1.5px solid var(--line-strong);
    border-radius:12px;
    text-align:center;
    font-size:14px;
    font-weight:700;
    color:var(--ink-2);
    text-decoration:none;
    transition:background .15s,color .15s,border-color .15s;
}

.rf-cancel:hover {
    background:var(--wash);
    border-color:var(--green);
    color:var(--green);
}

.rf-req-note {
    margin-bottom:10px;
    text-align:center;
    font-size:12px;
    color:var(--ink-2);
}

.rf-note {
    display:flex;
    gap:12px;
    padding:14px 16px;
    border-radius:14px;
    border:1px solid var(--line);
    background:var(--mango-soft);
    color:var(--ink);
    font-size:12.5px;
    line-height:1.55;
}

.rf-note>i {
    flex:none;
    font-size:17px;
    color:var(--mango-ink);
    margin-top:1px;
}

.rf-note b {
    display:block;
    font-size:13px;
    margin-bottom:2px;
    color:var(--ink);
}

/* Responsive */
@media(min-width:768px) {
    .rf-f.s4 {
        grid-column:span 4;
    }

    .rf-f.s6 {
        grid-column:span 6;
    }
}

@media(max-width:1100px) {
    .rf-grid {
        grid-template-columns:minmax(0,1fr);
        gap:8px;
    }

    .rf-aside {
        position:static;
    }
}

@media(max-width:767.98px) {
    .rf-sec {
        padding:21px 17px 23px;
    }

    .rf-mode-grid,
    .rf-tiles {
        grid-template-columns:minmax(0,1fr);
    }

    .rf-mode-body {
        min-height:0;
    }

    .rf-level-options {
        grid-template-columns:minmax(0,1fr);
    }
}

@media(max-width:575.98px) {
    .rf-fields {
        gap:15px;
    }

    .rf-pv-top {
        padding:16px;
    }

    .rf-pv-rows {
        padding:4px 16px 16px;
    }
}

@media(prefers-reduced-motion:reduce) {
    .rf *,
    .rf *::before,
    .rf *::after {
        transition:none!important;
    }
}
</style>

<div class="container-fluid py-4 rf">
    <header class="rf-hero">
        <nav class="rf-crumb" aria-label="Breadcrumb">
            <a href="{{ route('relief-vouchers.index') }}"><i class="bi bi-arrow-left"></i> Relief Assistance</a>
            <span aria-hidden="true">/</span>
            <span>New operation</span>
        </nav>

        <h1 class="rf-display">Create Relief / Assistance Operation</h1>
        <p>Name the operation and choose the assistance mode. The mode determines the workflow for vouchers, payouts, or service participants after the operation is created.</p>
    </header>

    @if ($errors->any())
        <div class="rf-errors" role="alert">
            <b><i class="bi bi-exclamation-circle"></i> Please correct the following before creating the operation:</b>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('relief-assistance.store') }}" id="reliefOperationForm" novalidate>
        @csrf

        <div class="rf-grid">
            <div class="rf-main">

                <section class="rf-sec" aria-labelledby="secDetails">
                    <div class="rf-sec-head">
                        <div class="rf-sec-ic"><i class="bi bi-clipboard-plus"></i></div>
                        <div>
                            <h2 id="secDetails">Operation details</h2>
                            <p>Identify what the barangay is providing</p>
                        </div>
                    </div>

                    <div class="rf-fields">
                        <div class="rf-f">
                            <label for="operation_name" class="rf-label">
                                Operation name <span class="rf-req">Required</span>
                            </label>

                            <input type="text"
                                   class="form-control @error('operation_name') is-invalid @enderror"
                                   id="operation_name"
                                   name="operation_name"
                                   value="{{ old('operation_name') }}"
                                   maxlength="255"
                                   placeholder="Example: Post-Typhoon Family Food Pack Distribution"
                                   autocomplete="off"
                                   aria-describedby="operationNameHelp operationNameError"
                                   required
                                   autofocus>

                            @error('operation_name')
                                <div class="invalid-feedback" id="operationNameServerError">{{ $message }}</div>
                            @enderror

                            <div class="invalid-feedback" id="operationNameError" role="alert" hidden>Please enter an operation name.</div>
                            <div class="form-text" id="operationNameHelp">Use a clear name that staff can recognize later.</div>
                        </div>

                        <div class="rf-f">
                            <fieldset id="assistanceModeFieldset">
                                <legend>Assistance mode <span class="rf-req">Required</span></legend>

                                <div class="rf-mode-grid">
                                    @foreach ($assistanceModes as $modeKey => $mode)
                                        <label class="rf-mode" for="assistance_type_{{ $modeKey }}">
                                            <input type="radio"
                                                   name="assistance_type"
                                                   id="assistance_type_{{ $modeKey }}"
                                                   value="{{ $modeKey }}"
                                                   @checked(old('assistance_type') === $modeKey)>

                                            <span class="rf-mode-body">
                                                <span class="rf-mode-icon"><i class="bi {{ $mode['icon'] }}"></i></span>

                                                <span class="rf-mode-content">
                                                    <span class="rf-mode-title">{{ $mode['label'] }}</span>
                                                    <span class="rf-mode-description">{{ $mode['description'] }}</span>
                                                    <span class="rf-mode-badge">{{ $mode['badge'] }}</span>
                                                </span>

                                                <span class="rf-tick rf-mode-tick" aria-hidden="true"></span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>

                                @error('assistance_type')
                                    <div class="rf-msg" id="assistanceTypeServerError"><i class="bi bi-exclamation-circle me-1"></i>{{ $message }}</div>
                                @enderror

                                <div class="rf-msg" id="assistanceTypeError" role="alert" hidden>
                                    <i class="bi bi-exclamation-circle me-1"></i>Please choose an assistance mode.
                                </div>
                            </fieldset>

                            <div class="rf-mode-notice" id="modeNotice" aria-live="polite">
                                <i class="bi bi-info-circle-fill"></i>
                                <div>
                                    <b id="modeNoticeTitle">Select an assistance mode</b>
                                    <span id="modeNoticeText">The selected mode determines the appropriate workflow after the operation is created.</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="rf-sec" aria-labelledby="secTarget">
                    <div class="rf-sec-head">
                        <div class="rf-sec-ic"><i class="bi bi-funnel"></i></div>
                        <div>
                            <h2 id="secTarget">Masterlist starting group</h2>
                            <p>Choose the initial group of records to review</p>
                        </div>
                    </div>

                    <p class="rf-hint" id="targetHint">
                        This filter only identifies the initial pool of records. It does not decide eligibility or approve anyone. Staff can change the filter later and use Purok/search during masterlist preparation.
                    </p>

                    <fieldset>
                        <legend class="visually-hidden">Starting group</legend>

                        <div class="rf-tiles">
                            @foreach ($targetGroups as $key => $group)
                                <label class="rf-tile" for="target_group_{{ $key }}">
                                    <input type="radio"
                                           name="target_group"
                                           id="target_group_{{ $key }}"
                                           value="{{ $key }}"
                                           @checked($selectedTargetGroup === $key)>

                                    <span class="rf-tile-body">
                                        <span class="rf-tile-ic"><i class="bi {{ $group['icon'] }}"></i></span>

                                        <span class="rf-tile-text">
                                            <span class="rf-tile-t">{{ $group['label'] }}</span>
                                            <span class="rf-tile-d">{{ $group['description'] }}</span>
                                        </span>

                                        <span class="rf-tick" aria-hidden="true"></span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @error('target_group')
                            <div class="rf-msg" role="alert">{{ $message }}</div>
                        @enderror
                    </fieldset>

                    <div class="rf-student-levels" id="studentLevelsPanel" @if($selectedTargetGroup !== 'students') hidden @endif>
                        <span class="rf-student-levels-title">Student education level</span>
                        <p class="rf-student-levels-help">Select one or more levels to include in the starting student pool.</p>

                        <div class="rf-level-options">
                            <label class="rf-level-option" for="student_level_elementary">
                                <input type="checkbox"
                                       name="student_levels[]"
                                       id="student_level_elementary"
                                       value="elementary"
                                       @checked(in_array('elementary', $selectedStudentLevels, true))
                                       @disabled($selectedTargetGroup !== 'students')>
                                <span>Elementary</span>
                            </label>

                            <label class="rf-level-option" for="student_level_high_school_shs">
                                <input type="checkbox"
                                       name="student_levels[]"
                                       id="student_level_high_school_shs"
                                       value="high_school_shs"
                                       @checked(in_array('high_school_shs', $selectedStudentLevels, true))
                                       @disabled($selectedTargetGroup !== 'students')>
                                <span>High School / SHS</span>
                            </label>

                            <label class="rf-level-option" for="student_level_college">
                                <input type="checkbox"
                                       name="student_levels[]"
                                       id="student_level_college"
                                       value="college"
                                       @checked(in_array('college', $selectedStudentLevels, true))
                                       @disabled($selectedTargetGroup !== 'students')>
                                <span>College</span>
                            </label>
                        </div>

                        <div class="rf-msg" id="studentLevelsError" role="alert" hidden>
                            Please select at least one student education level.
                        </div>

                        @error('student_levels')
                            <div class="rf-msg" role="alert">{{ $message }}</div>
                        @enderror
                    </div>
                </section>

                <section class="rf-sec" aria-labelledby="secDist">
                    <div class="rf-sec-head">
                        <div class="rf-sec-ic"><i class="bi bi-calendar-event"></i></div>
                        <div>
                            <h2 id="secDist">Schedule and location</h2>
                            <p>Planned distribution, payout, or service details (all optional)</p>
                        </div>
                    </div>

                    <div class="rf-fields">
                        <div class="rf-f s4">
                            <label for="distribution_date" class="rf-label">Date <span class="rf-opt">Optional</span></label>

                            <input type="date"
                                   class="form-control @error('distribution_date') is-invalid @enderror"
                                   id="distribution_date"
                                   name="distribution_date"
                                   value="{{ old('distribution_date') }}">

                            @error('distribution_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="rf-f s4">
                            <label for="distribution_time" class="rf-label">Time <span class="rf-opt">Optional</span></label>

                            <input type="time"
                                   class="form-control @error('distribution_time') is-invalid @enderror"
                                   id="distribution_time"
                                   name="distribution_time"
                                   value="{{ old('distribution_time') }}">

                            @error('distribution_time')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="rf-f s4">
                            <label for="distribution_location" class="rf-label">Location <span class="rf-opt">Optional</span></label>

                            <input type="text"
                                   class="form-control @error('distribution_location') is-invalid @enderror"
                                   id="distribution_location"
                                   name="distribution_location"
                                   value="{{ old('distribution_location') }}"
                                   maxlength="255"
                                   placeholder="Example: Barangay Hall">

                            @error('distribution_location')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="rf-f">
                            <label for="allocation_notes" class="rf-label">Additional notes <span class="rf-opt">Optional</span></label>

                            <textarea class="form-control @error('allocation_notes') is-invalid @enderror"
                                      id="allocation_notes"
                                      name="allocation_notes"
                                      rows="3"
                                      maxlength="2000"
                                      placeholder="Add any important instructions for staff...">{{ old('allocation_notes') }}</textarea>

                            @error('allocation_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </section>
            </div>

            <aside class="rf-aside" aria-label="Summary and actions">
                <div class="rf-pv" aria-live="polite">
                    <div class="rf-pv-head">
                        <b>Operation summary</b>
                        <span>Updates as you type</span>
                    </div>

                    <div class="rf-pv-top">
                        <div class="rf-date is-none" id="pvDate">
                            <b id="pvDay">--</b>
                            <span id="pvMonth">No date</span>
                        </div>

                        <div>
                            <div class="rf-pv-name is-empty" id="pvName">Untitled operation</div>
                            <span class="rf-pv-type" id="pvType" hidden></span>
                        </div>
                    </div>

                    <dl class="rf-pv-rows">
                        <div class="rf-pv-row"><dt>Assistance mode</dt><dd id="pvMode" class="is-empty">Not selected</dd></div>
                        <div class="rf-pv-row"><dt>Masterlist starts with</dt><dd id="pvTarget">All Residents</dd></div>
                        <div class="rf-pv-row"><dt>Time</dt><dd id="pvTime" class="is-empty">Not set</dd></div>
                        <div class="rf-pv-row"><dt>Location</dt><dd id="pvPlace" class="is-empty">Not set</dd></div>
                    </dl>
                </div>

                <div>
                    <div class="rf-req-note">Operation name and assistance mode are required.</div>

                    <button type="submit" class="rf-submit" id="createOperationButton">
                        <i class="bi bi-plus-lg"></i>
                        <span>Create operation</span>
                    </button>

                    <a href="{{ route('relief-vouchers.index') }}" class="rf-cancel">Cancel</a>
                </div>

                <div class="rf-note">
                    <i class="bi bi-person-check-fill"></i>
                    <div>
                        <b>Human verification remains required</b>
                        Filters only find potential records. Authorized barangay staff still verify and confirm the actual beneficiaries or participants.
                    </div>
                </div>
            </aside>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('reliefOperationForm');
    if (!form) return;

    const get = (id) => document.getElementById(id);
    const value = (id) => get(id) ? get(id).value.trim() : '';
    const createButton = get('createOperationButton');
    const buttonIdleHtml = createButton ? createButton.innerHTML : '';

    let isSubmitting = false;
    let busyTimer = null;

    const targetLabels = {
        all: 'All Residents',
        household_heads: 'Household Heads',
        students: 'Students',
        senior_citizens: 'Senior Citizens',
        pwd: 'PWD',
        solo_parent: 'Solo Parents',
        four_ps: '4Ps Beneficiaries',
        indigent: 'Indigent / Low-Income'
    };

    const studentLevelLabels = {
        elementary: 'Elementary',
        high_school_shs: 'High School / SHS',
        college: 'College'
    };

    const modeLabels = {
        in_kind: 'In-Kind Relief Distribution',
        cash: 'Emergency Cash / Financial Assistance',
        priority: 'Priority Sector Social Assistance',
        service: 'Service / Social Assistance'
    };

    const modeBadges = {
        in_kind: 'QR Voucher',
        cash: 'QR + Payout',
        priority: 'Targeted',
        service: 'No Voucher'
    };

    const modeNotices = {
        in_kind: {
            title: 'QR voucher workflow will be available',
            text: 'This operation is for physical goods such as Family Food Packs. Staff must confirm recipients before unique QR vouchers are used for claiming.'
        },
        cash: {
            title: 'QR payout workflow will be available',
            text: 'This operation is for monetary assistance. Confirmed beneficiaries can be linked to a unique QR claim ticket and payout record.'
        },
        priority: {
            title: 'Targeted assistance workflow',
            text: 'Use the masterlist filters to find potential priority-sector beneficiaries. Staff must verify and confirm recipients before assistance is released.'
        },
        service: {
            title: 'Service workflow — no voucher by default',
            text: 'This is intended for medical missions, dental missions, or vaccination drives. The workflow tracks participants and services instead of relief vouchers.'
        }
    };

    const targetHints = {
        default: 'This filter only identifies the initial pool of records. It does not decide eligibility or approve anyone. Staff can change the filter later and use Purok/search during masterlist preparation.',
        service: 'This filter only identifies the initial pool of potential participants. It does not decide eligibility or register anyone. Staff can change the filter later and use Purok/search during masterlist preparation.'
    };

    const studentLevelsPanel = get('studentLevelsPanel');
    const studentLevelsError = get('studentLevelsError');

    function checkedValue(name) {
        const checked = form.querySelector('input[name="' + name + '"]:checked');
        return checked ? checked.value : '';
    }

    function showMessage(element, show) {
        if (element) element.hidden = !show;
    }

    function clearServerError(id) {
        const element = get(id);
        if (element) element.hidden = true;
    }

    function updateStudentLevelVisibility() {
        const isStudents = checkedValue('target_group') === 'students';

        if (studentLevelsPanel) {
            studentLevelsPanel.hidden = !isStudents;
        }

        form.querySelectorAll('input[name="student_levels[]"]').forEach(function (input) {
            input.disabled = !isStudents;
        });

        if (!isStudents) {
            showMessage(studentLevelsError, false);
        }

        return isStudents;
    }

    function selectedStudentLevels() {
        return Array.from(form.querySelectorAll('input[name="student_levels[]"]:checked'))
            .map(function (input) {
                return studentLevelLabels[input.value] || input.value;
            });
    }

    function setRow(id, text, fallback) {
        const element = get(id);
        if (!element) return;

        element.textContent = text || fallback;
        element.classList.toggle('is-empty', !text);
    }

    function updateModeNotice() {
        const mode = checkedValue('assistance_type');
        const title = get('modeNoticeTitle');
        const text = get('modeNoticeText');
        const hint = get('targetHint');

        if (hint) {
            hint.textContent = mode === 'service'
                ? targetHints.service
                : targetHints.default;
        }

        if (!title || !text) return;

        if (!mode || !modeNotices[mode]) {
            title.textContent = 'Select an assistance mode';
            text.textContent = 'The selected mode determines the appropriate workflow after the operation is created.';
            return;
        }

        title.textContent = modeNotices[mode].title;
        text.textContent = modeNotices[mode].text;
    }

    function updateSummary() {
        const name = value('operation_name');
        const nameElement = get('pvName');

        if (nameElement) {
            nameElement.textContent = name || 'Untitled operation';
            nameElement.classList.toggle('is-empty', !name);
        }

        const mode = checkedValue('assistance_type');
        const typeElement = get('pvType');

        if (typeElement) {
            typeElement.hidden = !mode;

            if (mode) {
                typeElement.textContent = modeBadges[mode] || mode;
            }
        }

        setRow(
            'pvMode',
            mode ? (modeLabels[mode] || mode) : '',
            'Not selected'
        );

        const target = checkedValue('target_group') || 'all';
        let targetSummary = targetLabels[target] || target;

        if (target === 'students') {
            const levels = selectedStudentLevels();
            targetSummary += levels.length
                ? ' — ' + levels.join(', ')
                : ' — choose level';
        }

        const targetElement = get('pvTarget');
        if (targetElement) {
            targetElement.textContent = targetSummary;
        }

        const dateValue = value('distribution_date');
        const dateBox = get('pvDate');
        const dateDay = get('pvDay');
        const dateMonth = get('pvMonth');
        const dateParts = dateValue.split('-').map(Number);

        if (
            dateBox &&
            dateDay &&
            dateMonth &&
            dateValue &&
            dateParts.length === 3 &&
            !dateParts.some(Number.isNaN)
        ) {
            const date = new Date(
                dateParts[0],
                dateParts[1] - 1,
                dateParts[2]
            );

            dateDay.textContent = String(date.getDate()).padStart(2, '0');
            dateMonth.textContent = date.toLocaleDateString('en-US', {
                month: 'short',
                year: 'numeric'
            });

            dateBox.classList.remove('is-none');
        } else if (dateBox && dateDay && dateMonth) {
            dateDay.textContent = '--';
            dateMonth.textContent = 'No date';
            dateBox.classList.add('is-none');
        }

        const timeValue = value('distribution_time');
        let timeText = '';
        const timeParts = timeValue.split(':').map(Number);

        if (
            timeValue &&
            timeParts.length >= 2 &&
            !timeParts.some(Number.isNaN)
        ) {
            timeText = new Date(
                2000,
                0,
                1,
                timeParts[0],
                timeParts[1]
            ).toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit'
            });
        }

        setRow('pvTime', timeText, 'Not set');
        setRow('pvPlace', value('distribution_location'), 'Not set');
    }

    function setBusy(busy) {
        isSubmitting = busy;

        if (!createButton) return;

        if (busy) {
            createButton.classList.add('is-busy');
            createButton.setAttribute('aria-disabled', 'true');
            createButton.innerHTML =
                '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span><span>Creating operation...</span>';

            clearTimeout(busyTimer);

            busyTimer = setTimeout(function () {
                setBusy(false);
            }, 20000);
        } else {
            clearTimeout(busyTimer);
            createButton.classList.remove('is-busy');
            createButton.removeAttribute('aria-disabled');
            createButton.innerHTML = buttonIdleHtml;
        }
    }

    function validateForm() {
        let firstInvalid = null;

        const nameInput = get('operation_name');
        const nameEmpty = value('operation_name') === '';

        if (nameInput) {
            nameInput.classList.toggle('is-invalid', nameEmpty);
            showMessage(get('operationNameError'), nameEmpty);
        }

        if (nameEmpty) {
            clearServerError('operationNameServerError');
            firstInvalid = firstInvalid || nameInput;
        }

        const modeMissing = checkedValue('assistance_type') === '';

        showMessage(get('assistanceTypeError'), modeMissing);

        if (modeMissing) {
            clearServerError('assistanceTypeServerError');
            firstInvalid = firstInvalid ||
                form.querySelector('input[name="assistance_type"]');
        }

        const studentsSelected = updateStudentLevelVisibility();
        const levelsMissing =
            studentsSelected && selectedStudentLevels().length === 0;

        showMessage(studentLevelsError, levelsMissing);

        if (levelsMissing) {
            firstInvalid = firstInvalid ||
                form.querySelector('input[name="student_levels[]"]');
        }

        return firstInvalid;
    }

    form.addEventListener('input', function (event) {
        updateSummary();

        if (
            event.target &&
            event.target.id === 'operation_name' &&
            value('operation_name') !== ''
        ) {
            event.target.classList.remove('is-invalid');
            showMessage(get('operationNameError'), false);
            clearServerError('operationNameServerError');
        }
    });

    form.addEventListener('change', function (event) {
        const name = event.target ? event.target.name : '';

        updateStudentLevelVisibility();
        updateSummary();
        updateModeNotice();

        if (name === 'assistance_type') {
            showMessage(get('assistanceTypeError'), false);
            clearServerError('assistanceTypeServerError');
        }

        if (selectedStudentLevels().length > 0) {
            showMessage(studentLevelsError, false);
        }
    });

    form.addEventListener('submit', function (event) {
        if (isSubmitting) {
            event.preventDefault();
            return;
        }

        const firstInvalid = validateForm();

        if (firstInvalid) {
            event.preventDefault();

            firstInvalid.scrollIntoView({
                behavior: 'smooth',
                block: 'center'
            });

            firstInvalid.focus({ preventScroll: true });
            return;
        }

        setBusy(true);
    });

    window.addEventListener('pageshow', function (event) {
        if (event.persisted) {
            setBusy(false);
        }
    });

    updateStudentLevelVisibility();
    updateSummary();
    updateModeNotice();
});
</script>

@endsection
