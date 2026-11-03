@extends('layouts.app')

@section('title', 'Report / Complaint Details')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       REPORT / COMPLAINT DETAILS - same design system as the other pages
       ========================================================= */
    .rc {
        --ink: #14213d; --muted: #5f6b7d; --paper: #ffffff; --wash: #eef2f7; --line: #dbe2ec;
        --brand: #1f4e9c; --brand-soft: #e4edfb; --sun: #f2b705; --ok: #2b8a3e;
        --community: #0b7285; --community-bg: #d8f3f8;
        --person: #c2410c;    --person-bg: #ffe8d9;
        --s-filed: #5f6b7d;      --s-filed-bg: #e9edf3;
        --s-review: #b26a00;     --s-review-bg: #fff1d6;
        --s-captain: #c2410c;    --s-captain-bg: #ffe8d9;
        --s-hearing: #6741d9;    --s-hearing-bg: #ece5fd;
        --s-resolution: #0b7285; --s-resolution-bg: #d8f3f8;
        --s-resolved: #2b8a3e;   --s-resolved-bg: #dcf5e3;
        --s-closed: #495057;     --s-closed-bg: #e3e7ec;
        --s-disapproved: #c92a2a;--s-disapproved-bg: #fde3e3;
        font-family: 'Public Sans', system-ui, sans-serif; color: var(--ink);
    }

    html[data-theme="dark"] .rc {
        --ink: #eef2f8; --muted: #9aa7bb; --paper: #1b2230; --wash: #141a25; --line: #303a4d;
        --brand: #7aa7f0; --brand-soft: #23324f; --ok: #6fd48a;
        --community: #5ccfe0; --community-bg: #0f3038;
        --person: #ffa26b;    --person-bg: #40230f;
        --s-filed: #b7c0cf;      --s-filed-bg: #2a3242;
        --s-review: #f5b942;     --s-review-bg: #3a2c10;
        --s-captain: #ffa26b;    --s-captain-bg: #40230f;
        --s-hearing: #b7a0f5;    --s-hearing-bg: #2a2150;
        --s-resolution: #5ccfe0; --s-resolution-bg: #0f3038;
        --s-resolved: #6fd48a;   --s-resolved-bg: #143222;
        --s-closed: #b0b8c5;     --s-closed-bg: #2a3242;
        --s-disapproved: #ff8f8f;--s-disapproved-bg: #401a1d;
    }

    .rc * { box-sizing: border-box; }

    .rc .display {
        font-family: 'Bricolage Grotesque', 'Public Sans', sans-serif;
        letter-spacing: -.02em;
    }

    .rc a:focus-visible,
    .rc button:focus-visible {
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

    /* status colours */
    .s-filed { --sc: var(--s-filed); --sb: var(--s-filed-bg); }
    .s-review { --sc: var(--s-review); --sb: var(--s-review-bg); }
    .s-captain { --sc: var(--s-captain); --sb: var(--s-captain-bg); }
    .s-hearing { --sc: var(--s-hearing); --sb: var(--s-hearing-bg); }
    .s-resolution { --sc: var(--s-resolution); --sb: var(--s-resolution-bg); }
    .s-resolved { --sc: var(--s-resolved); --sb: var(--s-resolved-bg); }
    .s-closed { --sc: var(--s-closed); --sb: var(--s-closed-bg); }
    .s-disapproved { --sc: var(--s-disapproved); --sb: var(--s-disapproved-bg); }

    .pill {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .35rem .85rem;
        border-radius: 999px;
        font-size: .9rem;
        font-weight: 600;
        color: var(--sc);
        background: var(--sb);
        white-space: nowrap;
    }

    .pill::before {
        content: '';
        width: .55rem;
        height: .55rem;
        border-radius: 50%;
        background: var(--sc);
    }

    .ttype {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .3rem .8rem;
        border-radius: 999px;
        background: var(--cbg);
        color: var(--c);
        font-size: .85rem;
        font-weight: 700;
    }

    .t-community {
        --c: var(--community);
        --cbg: var(--community-bg);
    }

    .t-person {
        --c: var(--person);
        --cbg: var(--person-bg);
    }

    /* alerts */
    .flash {
        display: flex;
        gap: .75rem;
        align-items: flex-start;
        padding: .9rem 1.1rem;
        border-radius: .9rem;
        margin: 1rem 0;
        border: 1px solid var(--sc);
        background: var(--sb);
    }

    .flash i {
        color: var(--sc);
        font-size: 1.2rem;
    }

    .flash b {
        display: block;
        color: var(--sc);
    }

    .flash span {
        color: var(--ink);
        font-size: .92rem;
    }

    .flash .x {
        margin-left: auto;
        background: none;
        border: 0;
        color: var(--sc);
        font-size: 1.2rem;
        line-height: 1;
    }

    /* hero */
    .hero {
        margin: 1rem 0 1.25rem;
    }

    .hero-top {
        display: flex;
        flex-wrap: wrap;
        gap: .6rem;
        align-items: center;
        margin-bottom: .7rem;
    }

    .hero h1 {
        font-size: clamp(1.6rem, 3vw, 2.2rem);
        font-weight: 800;
        line-height: 1.15;
        margin: 0 0 .5rem;
        overflow-wrap: anywhere;
    }

    .facts {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem 1.4rem;
        color: var(--muted);
        font-size: .92rem;
    }

    .facts span {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
    }

    .facts i {
        color: var(--brand);
    }

    .facts b {
        color: var(--ink);
        font-weight: 600;
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

        .side {
            order: -1;
        }
    }

    .card2 {
        background: var(--paper);
        border: 1px solid var(--line);
        border-radius: 1.1rem;
        margin-bottom: 1rem;
        overflow: hidden;
    }

    .card2-head {
        padding: .9rem 1.25rem;
        border-bottom: 1px solid var(--line);
    }

    .card2-head h2 {
        font-size: 1.05rem;
        font-weight: 800;
        margin: 0;
    }

    .card2-body {
        padding: 1.25rem;
    }

    .text {
        white-space: pre-line;
        overflow-wrap: anywhere;
        line-height: 1.65;
    }

    .text.empty {
        color: var(--muted);
    }

    /* people */
    .people {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    @media (max-width: 767.98px) {
        .people {
            grid-template-columns: 1fr;
        }
    }

    .person {
        padding: 1rem;
        border-radius: .95rem;
        border: 1px solid var(--line);
        background: var(--wash);
        border-top: 4px solid var(--brand);
    }

    .person.resp {
        border-top-color: var(--person);
    }

    .role {
        font-size: .8rem;
        font-weight: 600;
        color: var(--muted);
        margin-bottom: .6rem;
    }

    .who {
        display: flex;
        gap: .75rem;
        align-items: center;
        margin-bottom: .75rem;
    }

    .avatar {
        width: 2.7rem;
        height: 2.7rem;
        border-radius: 50%;
        display: grid;
        place-items: center;
        flex: none;
        font-weight: 700;
        background: var(--brand-soft);
        color: var(--brand);
    }

    .resp .avatar {
        background: var(--person-bg);
        color: var(--person);
    }

    .who b {
        font-weight: 700;
        overflow-wrap: anywhere;
    }

    .kv {
        display: flex;
        gap: .55rem;
        font-size: .9rem;
        margin-top: .35rem;
    }

    .kv i {
        color: var(--muted);
        margin-top: .15rem;
    }

    .muted {
        color: var(--muted);
    }

    .infobox {
        display: flex;
        gap: .8rem;
        padding: 1rem;
        border-radius: .95rem;
        background: var(--community-bg);
        margin-top: 1rem;
        font-size: .92rem;
    }

    .infobox i {
        color: var(--community);
        font-size: 1.2rem;
    }

    .infobox b {
        display: block;
        color: var(--community);
    }

    /*
     * Subpoena information box.
     * This class is only rendered for person complaints.
     */
    .subp {
        display: flex;
        gap: .8rem;
        align-items: center;
        padding: 1rem;
        border-radius: .95rem;
        margin-top: 1rem;
        background: var(--wash);
        border: 1px solid var(--line);
    }

    .subp.on {
        background: var(--s-review-bg);
        border-color: var(--s-review);
    }

    .subp i {
        font-size: 1.4rem;
        color: var(--muted);
    }

    .subp.on i {
        color: var(--s-review);
    }

    .subp small {
        display: block;
        color: var(--muted);
    }

    /* timeline */
    .tl {
        list-style: none;
        margin: 0;
        padding: 1.25rem 1.25rem .4rem;
    }

    .tl li {
        position: relative;
        display: flex;
        gap: .9rem;
        padding-bottom: 1.15rem;
    }

    .tl li:not(:last-child)::before {
        content: '';
        position: absolute;
        left: .7rem;
        top: 1.6rem;
        bottom: .1rem;
        width: 2px;
        background: var(--line);
    }

    .tl .dot {
        width: 1.45rem;
        height: 1.45rem;
        border-radius: 50%;
        flex: none;
        display: grid;
        place-items: center;
        font-size: .75rem;
        border: 2px solid var(--line);
        background: var(--paper);
        color: transparent;
    }

    .tl li.done .dot {
        background: var(--ok);
        border-color: var(--ok);
        color: #fff;
    }

    html[data-theme="dark"] .tl li.done .dot {
        color: #0f1620;
    }

    .tl b {
        font-weight: 600;
        display: block;
    }

    .tl li:not(.done) b {
        color: var(--muted);
        font-weight: 500;
    }

    .tl small {
        color: var(--muted);
    }

    /* side */
    .side {
        position: sticky;
        top: 1rem;
    }

    @media (max-width: 991.98px) {
        .side {
            position: static;
        }
    }

    .stack {
        display: grid;
        gap: .6rem;
    }

    .stack form {
        margin: 0;
    }

    .act {
        display: flex;
        width: 100%;
        justify-content: center;
        align-items: center;
        gap: .5rem;
        min-height: 46px;
        border-radius: 999px;
        font-weight: 700;
        border: 1.5px solid transparent;
        text-decoration: none;
        padding: .55rem 1rem;
    }

    .act-brand {
        background: var(--brand);
        color: #fff;
    }

    .act-green {
        background: var(--ok);
        color: #fff;
    }

    html[data-theme="dark"] .act-brand,
    html[data-theme="dark"] .act-green {
        color: #0f1620;
    }

    .act-brand:hover,
    .act-green:hover {
        filter: brightness(1.08);
        color: #fff;
    }

    .act-line {
        background: transparent;
        border-color: var(--line);
        color: var(--ink);
    }

    .act-line:hover {
        border-color: var(--brand);
        color: var(--brand);
    }

    .locked {
        display: flex;
        gap: .7rem;
        padding: .85rem 1rem;
        border-radius: .9rem;
        background: var(--wash);
        color: var(--muted);
        font-size: .9rem;
    }

    .kvs {
        display: grid;
        gap: .85rem;
        margin: 0;
    }

    .kvs dt {
        color: var(--muted);
        font-size: .8rem;
    }

    .kvs dd {
        margin: .1rem 0 0;
        font-weight: 600;
        overflow-wrap: anywhere;
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
    $r = $reportsComplaint;

    /*
    |--------------------------------------------------------------------------
    | Status mapping
    |--------------------------------------------------------------------------
    */
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

    $statusClass = $statusMap[$r->status] ?? 's-filed';

    /*
    |--------------------------------------------------------------------------
    | Report type
    |--------------------------------------------------------------------------
    | Only PERSON complaints may use subpoena functionality.
    | COMMUNITY reports never show subpoena information/actions.
    |--------------------------------------------------------------------------
    */
    $isPerson = strtolower((string) $r->report_type) === 'person';

    $isCommunity = strtolower((string) $r->report_type) === 'community';

    /*
    |--------------------------------------------------------------------------
    | Subpoena
    |--------------------------------------------------------------------------
    | Keep the subpoena state only for person complaints.
    | Even if an old community record accidentally contains a subpoena flag,
    | it will NOT be shown or printable from this page.
    |--------------------------------------------------------------------------
    */
    $hasSubpoena = false;

    if ($isPerson) {
        $hasSubpoena = method_exists($r, 'hasSubpoenaRequest')
            ? $r->hasSubpoenaRequest()
            : ! empty($r->subpoena_requested);
    }

    /*
    |--------------------------------------------------------------------------
    | Available actions
    |--------------------------------------------------------------------------
    */
    $canEdit = ! in_array($r->status, [
        'Resolved',
        'Closed',
    ]);

    $canResolve = ! in_array($r->status, [
        'Resolved',
        'Closed',
        'Disapproved',
    ]);

    /*
     * Printing a subpoena is ONLY possible when:
     * 1. This is a person complaint
     * 2. A subpoena/notice was requested
     */
    $canPrint = $isPerson && $hasSubpoena;

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */
    $editUrl = route(
        'reports-complaints.edit',
        ['reports_complaint' => $r->id]
    );

    $printUrl = $isPerson && $hasSubpoena
        ? route(
            'reports-complaints.subpoena.print',
            ['reports_complaint' => $r->id]
        )
        : null;

    $resolveUrl = route(
        'reports-complaints.resolve',
        ['reports_complaint' => $r->id]
    );

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    $fullName = fn ($p) => $p
        ? trim(
            collect([
                $p->first_name,
                $p->middle_name,
                $p->last_name,
                $p->suffix
            ])->filter()->implode(' ')
        )
        : null;

    $initials = function ($p) {
        if (! $p) {
            return '?';
        }

        return strtoupper(
            mb_substr((string) $p->first_name, 0, 1) .
            mb_substr((string) $p->last_name, 0, 1)
        ) ?: '?';
    };

    /*
    |--------------------------------------------------------------------------
    | Manually entered (non-resident) participants
    |--------------------------------------------------------------------------
    | Non-residents are stored in the report's own columns
    | (complainant_* / subject_*), not in the Residents table.
    | Returns null when no manual name was saved.
    |--------------------------------------------------------------------------
    */
    $manualParty = function (string $prefix) use ($r) {
        $name = trim((string) $r->{$prefix . '_name'});

        if ($name === '') {
            return null;
        }

        $contact = trim((string) $r->{$prefix . '_contact_number'});

        $address = collect([
            $r->{$prefix . '_address'},
            $r->{$prefix . '_barangay'},
            $r->{$prefix . '_municipality'},
            $r->{$prefix . '_province'},
        ])->map(fn ($v) => trim((string) $v))->filter()->implode(', ');

        return [
            'name' => $name,
            'contact' => $contact !== '' ? $contact : null,
            'address' => $address !== '' ? $address : null,
        ];
    };

    $initialsFromName = function (?string $name) {
        $words = preg_split('/\s+/', trim((string) $name), -1, PREG_SPLIT_NO_EMPTY);

        if (! $words) {
            return '?';
        }

        $first = mb_substr($words[0], 0, 1);
        $last = count($words) > 1 ? mb_substr(end($words), 0, 1) : '';

        return strtoupper($first . $last) ?: '?';
    };

    $complainantManual = $manualParty('complainant');
    $subjectManual = $manualParty('subject');

    $complainantName = $fullName($r->complainant)
        ?: ($complainantManual['name'] ?? null);

    $relationship = trim((string) $r->relationship_to_complainant);

    /*
    |--------------------------------------------------------------------------
    | Timeline
    |--------------------------------------------------------------------------
    */
    $timeline = [
        ['Recorded', $r->created_at],
        ['Last updated', $r->updated_at],
        ['Resolved', $r->resolved_at],
        ['Closed', $r->closed_at],
    ];
@endphp

<div class="rc">
    <div class="container-fluid py-4">

        <a href="{{ route('reports-complaints.index') }}" class="back">
            <i class="bi bi-arrow-left"></i>
            Back to records
        </a>

        @if (session('error'))
            <div class="flash s-disapproved" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>

                <span>{{ session('error') }}</span>

                <button
                    type="button"
                    class="x"
                    onclick="this.parentElement.remove()"
                    aria-label="Dismiss"
                >
                    &times;
                </button>
            </div>
        @endif

        {{-- =========================================================
             STATUS BANNERS
             ========================================================= --}}

        @if ($r->status === 'Resolved')
            <div class="flash s-resolved">
                <i class="bi bi-check-circle-fill"></i>

                <div>
                    <b>Report resolved</b>

                    <span>
                        This report or complaint has already been marked as resolved.

                        @if ($r->resolved_at)
                            Resolved on
                            {{ $r->resolved_at->format('F d, Y h:i A') }}.
                        @endif
                    </span>
                </div>
            </div>

        @elseif ($r->status === 'Closed')

            <div class="flash s-closed">
                <i class="bi bi-lock-fill"></i>

                <div>
                    <b>Report closed</b>

                    <span>
                        This report or complaint has been closed and no longer
                        requires active processing.
                    </span>
                </div>
            </div>

        @elseif ($r->status === 'Disapproved')

            <div class="flash s-disapproved">
                <i class="bi bi-x-circle-fill"></i>

                <div>
                    <b>Report disapproved</b>

                    <span>
                        This report or complaint has been disapproved.
                    </span>
                </div>
            </div>

        @endif

        {{-- =========================================================
             HERO
             ========================================================= --}}

        <div class="hero">

            <div class="hero-top">

                <span class="pill {{ $statusClass }}">
                    {{ $r->status }}
                </span>

                @if ($isPerson)

                    <span class="ttype t-person">
                        <i class="bi bi-person-exclamation"></i>
                        Person complaint
                    </span>

                @elseif ($isCommunity)

                    <span class="ttype t-community">
                        <i class="bi bi-buildings"></i>
                        Community / environment report
                    </span>

                @else

                    <span
                        class="ttype"
                        style="--c: var(--muted); --cbg: var(--wash)"
                    >
                        {{ ucfirst($r->report_type ?? 'N/A') }}
                    </span>

                @endif

            </div>

            <h1 class="display">
                {{ $r->title }}
            </h1>

            <div class="facts">

                <span>
                    <i class="bi bi-hash"></i>
                    <b>{{ $r->record_code }}</b>
                </span>

                <span>
                    <i class="bi bi-tag"></i>
                    {{ $r->category ?: 'No category' }}
                </span>

                <span>
                    <i class="bi bi-calendar-event"></i>

                    {{
                        $r->incident_date
                            ? \Carbon\Carbon::parse($r->incident_date)->format('F d, Y')
                            : 'No incident date'
                    }}

                    @if ($r->incident_time)
                        at
                        {{ \Carbon\Carbon::parse($r->incident_time)->format('h:i A') }}
                    @endif
                </span>

                <span>
                    <i class="bi bi-geo-alt"></i>
                    {{ $r->location ?: 'No location' }}
                </span>

            </div>
        </div>

        <div class="layout">

            {{-- =====================================================
                 LEFT CONTENT
                 ===================================================== --}}

            <div>

                {{-- WHAT HAPPENED --}}
                <section class="card2">

                    <div class="card2-head">
                        <h2 class="display">
                            What happened
                        </h2>
                    </div>

                    <div class="card2-body">

                        <div class="text {{ $r->description ? '' : 'empty' }}">
                            {{ $r->description ?: 'No description provided.' }}
                        </div>

                    </div>

                </section>

                {{-- PEOPLE INVOLVED / REPORTED BY --}}
                <section class="card2">

                    <div class="card2-head">
                        <h2 class="display">
                            {{ $isPerson ? 'People involved' : 'Reported by' }}
                        </h2>
                    </div>

                    <div class="card2-body">

                        <div class="people">

                            {{-- COMPLAINANT / REPORTER --}}
                            <div class="person">

                                <div class="role">
                                    Complainant / reporter
                                    @if (! $r->complainant && $complainantManual)
                                        &middot; Non-resident
                                    @endif
                                </div>

                                <div class="who">

                                    <span
                                        class="avatar"
                                        aria-hidden="true"
                                    >
                                        {{ $r->complainant ? $initials($r->complainant) : $initialsFromName($complainantName) }}
                                    </span>

                                    <b>
                                        {{ $complainantName ?: 'N/A' }}
                                    </b>

                                </div>

                                <div class="kv">
                                    <i class="bi bi-telephone"></i>

                                    <span>
                                        {{ $r->complainant?->contact_number ?? ($complainantManual['contact'] ?? 'N/A') }}
                                    </span>
                                </div>

                                <div class="kv">
                                    <i class="bi bi-house"></i>

                                    <span>
                                        {{ $r->complainant?->address ?? ($complainantManual['address'] ?? 'N/A') }}
                                    </span>
                                </div>

                            </div>

                            {{-- SUBJECT --}}
                            @if ($isPerson)

                                <div class="person resp">

                                    <div class="role">
                                        Subject of complaint
                                        @if (! $r->subjectResident && $subjectManual)
                                            &middot; Non-resident
                                        @endif
                                    </div>

                                    @if ($r->subjectResident)

                                        <div class="who">

                                            <span
                                                class="avatar"
                                                aria-hidden="true"
                                            >
                                                {{ $initials($r->subjectResident) }}
                                            </span>

                                            <b>
                                                {{ $fullName($r->subjectResident) }}
                                            </b>

                                        </div>

                                        <div class="kv">
                                            <i class="bi bi-telephone"></i>

                                            <span>
                                                {{ $r->subjectResident->contact_number ?? 'N/A' }}
                                            </span>
                                        </div>

                                        <div class="kv">
                                            <i class="bi bi-house"></i>

                                            <span>
                                                {{ $r->subjectResident->address ?? 'N/A' }}
                                            </span>
                                        </div>

                                    @elseif ($subjectManual)

                                        <div class="who">

                                            <span
                                                class="avatar"
                                                aria-hidden="true"
                                            >
                                                {{ $initialsFromName($subjectManual['name']) }}
                                            </span>

                                            <b>
                                                {{ $subjectManual['name'] }}
                                            </b>

                                        </div>

                                        <div class="kv">
                                            <i class="bi bi-telephone"></i>

                                            <span>
                                                {{ $subjectManual['contact'] ?? 'N/A' }}
                                            </span>
                                        </div>

                                        <div class="kv">
                                            <i class="bi bi-house"></i>

                                            <span>
                                                {{ $subjectManual['address'] ?? 'N/A' }}
                                            </span>
                                        </div>

                                    @else

                                        <div class="muted">
                                            No respondent recorded.
                                        </div>

                                    @endif

                                    @if ($relationship !== '')

                                        <div class="kv">
                                            <i class="bi bi-people"></i>

                                            <span>
                                                Relationship to complainant:
                                                {{ $relationship }}
                                            </span>
                                        </div>

                                    @endif

                                </div>

                            @endif

                        </div>

                        {{-- =================================================
                             PERSON COMPLAINT ONLY — SUBPOENA INFORMATION
                             ================================================= --}}
                        @if ($isPerson)

                            <div class="subp {{ $hasSubpoena ? 'on' : '' }}">

                                <i class="bi bi-file-earmark-text"></i>

                                <div>

                                    <b>
                                        Subpoena / notice to appear
                                    </b>

                                    <small>
                                        @if ($hasSubpoena)
                                            A subpoena / notice to appear
                                            has been requested for this
                                            personal dispute.
                                        @else
                                            No subpoena / notice to appear
                                            has been requested.
                                        @endif
                                    </small>

                                </div>

                            </div>

                        @endif

                        {{-- =================================================
                             COMMUNITY REPORT ONLY
                             No subpoena section is rendered here.
                             ================================================= --}}
                        @if ($isCommunity)

                            <div class="infobox">

                                <i class="bi bi-info-circle"></i>

                                <div>

                                    <b>
                                        Community / environment report
                                    </b>

                                    This is a community or environmental
                                    concern. A subject resident is not
                                    required for this type of report.

                                </div>

                            </div>

                        @endif

                    </div>

                </section>

                {{-- REMARKS --}}
                <section class="card2">

                    <div class="card2-head">
                        <h2 class="display">
                            Remarks
                        </h2>
                    </div>

                    <div class="card2-body">

                        <div class="text {{ $r->remarks ? '' : 'empty' }}">
                            {{ $r->remarks ?: 'No remarks provided.' }}
                        </div>

                    </div>

                </section>

                {{-- RECORD TIMELINE --}}
                <section class="card2">

                    <div class="card2-head">
                        <h2 class="display">
                            Record timeline
                        </h2>
                    </div>

                    <ol class="tl">

                        @foreach ($timeline as [$label, $date])

                            <li class="{{ $date ? 'done' : '' }}">

                                <span class="dot">
                                    <i class="bi bi-check-lg"></i>
                                </span>

                                <div>

                                    <b>
                                        {{ $label }}
                                    </b>

                                    <small>
                                        {{
                                            $date?->format('F d, Y h:i A')
                                            ?? 'Not yet'
                                        }}
                                    </small>

                                </div>

                            </li>

                        @endforeach

                    </ol>

                </section>

            </div>

            {{-- =====================================================
                 RIGHT SIDEBAR
                 ===================================================== --}}

            <aside class="side">

                {{-- ACTIONS --}}
                <section class="card2">

                    <div class="card2-head">
                        <h2 class="display">
                            Actions
                        </h2>
                    </div>

                    <div class="card2-body">

                        <div class="stack">

                            {{-- EDIT --}}
                            @if ($canEdit)

                                <a
                                    href="{{ $editUrl }}"
                                    class="act act-brand"
                                >
                                    <i class="bi bi-pencil-square"></i>
                                    Edit report
                                </a>

                            @endif

                            {{-- =================================================
                                 PRINT SUBPOENA
                                 PERSON COMPLAINT ONLY
                                 ================================================= --}}
                            @if ($isPerson && $canPrint && $printUrl)

                                <a
                                    href="{{ $printUrl }}"
                                    target="_blank"
                                    class="act act-line"
                                >
                                    <i class="bi bi-printer"></i>
                                    Print subpoena
                                </a>

                            @endif

                            {{-- RESOLVE --}}
                            @if ($canResolve)

                                <form
                                    action="{{ $resolveUrl }}"
                                    method="POST"
                                    onsubmit="return confirm('Are you sure you want to mark this report or complaint as resolved?');"
                                >

                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="act act-green"
                                    >
                                        <i class="bi bi-check-circle"></i>
                                        Save Case
                                    </button>

                                </form>

                            @endif

                            {{-- NO ACTIONS --}}
                            @if (
                                ! $canEdit &&
                                ! ($isPerson && $canPrint) &&
                                ! $canResolve
                            )

                                <div class="locked">

                                    <i class="bi bi-lock-fill"></i>

                                    <span>
                                        No further actions are available
                                        for this record.
                                    </span>

                                </div>

                            @endif

                        </div>

                    </div>

                </section>

                {{-- HANDLING --}}
                <section class="card2">

                    <div class="card2-head">
                        <h2 class="display">
                            Handling
                        </h2>
                    </div>

                    <div class="card2-body">

                        <dl class="kvs">

                            <div>

                                <dt>
                                    Assigned official / staff
                                </dt>

                                <dd>

                                    @if ($r->assignedUser)

                                        {{ $r->assignedUser->name }}

                                    @else

                                        <span
                                            class="muted"
                                            style="font-weight:400"
                                        >
                                            Not assigned
                                        </span>

                                    @endif

                                </dd>

                            </div>

                            <div>

                                <dt>
                                    Recorded by
                                </dt>

                                <dd>
                                    {{ $r->creator?->name ?? 'N/A' }}
                                </dd>

                            </div>

                        </dl>

                    </div>

                </section>

            </aside>

        </div>
    </div>
</div>

@endsection
