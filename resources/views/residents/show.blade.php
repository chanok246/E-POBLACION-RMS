@extends('layouts.app')

@section('title', 'View Resident | e-Poblacion')

@push('styles')
<style>
    .residents-page {
        --resident-surface:#ffffff; --resident-surface-soft:#f6f7fb; --resident-text:#252b43; --resident-muted:#7b8198;
        --resident-border:#e7e9f0; --resident-shadow:0 6px 18px rgba(31,38,72,.06);
        --rp-accent:#5b3df0; --rp-accent-soft:#efecff; --rp-ok:#17915f; --rp-ok-soft:#e4f6ed;
        --rp-off:#7b8198; --rp-off-soft:#eceef4; --rp-danger:#d6394b; --rp-danger-soft:#fdecee;
    }

    html[data-theme="dark"] .residents-page {
        --resident-surface:#1b1d2a; --resident-surface-soft:#232636; --resident-text:#f1f3f8; --resident-muted:#aeb4c5;
        --resident-border:rgba(255,255,255,.09); --resident-shadow:0 6px 18px rgba(0,0,0,.2);
        --rp-accent:#a99aff; --rp-accent-soft:rgba(169,154,255,.16); --rp-ok:#65d69b; --rp-ok-soft:rgba(101,214,155,.14);
        --rp-off:#c4c9d6; --rp-off-soft:rgba(196,201,214,.12); --rp-danger:#ff8f9b; --rp-danger-soft:rgba(255,143,155,.12);
    }

    .residents-page .breadcrumb-text {
        color:var(--resident-muted);
        font-size:12px;
        font-weight:700;
        letter-spacing:.35px;
        margin-bottom:6px;
    }

    .residents-page .page-title {
        color:var(--resident-text);
    }

    .residents-page .page-subtitle,
    .residents-page .text-muted {
        color:var(--resident-muted) !important;
    }

    .residents-page .card {
        background:var(--resident-surface);
        color:var(--resident-text);
        border:1px solid var(--resident-border) !important;
        border-radius:14px;
        box-shadow:var(--resident-shadow);
    }

    .residents-page .btn-soft {
        background:var(--resident-surface);
        border:1px solid var(--resident-border);
        color:var(--resident-text);
        font-weight:600;
    }

    .residents-page .btn-soft:hover {
        background:var(--resident-surface-soft);
        color:var(--resident-text);
    }

    .residents-page .btn-soft.danger:hover {
        background:var(--rp-danger-soft);
        color:var(--rp-danger);
        border-color:var(--rp-danger);
    }

    .av-0{--av-bg:#e7f0ff;--av-fg:#0d6efd}
    .av-1{--av-bg:#efecff;--av-fg:#5b3df0}
    .av-2{--av-bg:#e4f6ed;--av-fg:#17915f}
    .av-3{--av-bg:#fff1e0;--av-fg:#c76a00}
    .av-4{--av-bg:#fde8ee;--av-fg:#d6336c}
    .av-5{--av-bg:#e2f5f7;--av-fg:#0b8a9c}

    html[data-theme="dark"] .av-0{--av-bg:rgba(13,110,253,.18);--av-fg:#75aaff}
    html[data-theme="dark"] .av-1{--av-bg:rgba(169,154,255,.16);--av-fg:#a99aff}
    html[data-theme="dark"] .av-2{--av-bg:rgba(101,214,155,.14);--av-fg:#65d69b}
    html[data-theme="dark"] .av-3{--av-bg:rgba(255,166,64,.16);--av-fg:#ffb866}
    html[data-theme="dark"] .av-4{--av-bg:rgba(255,120,160,.16);--av-fg:#ff8fb3}
    html[data-theme="dark"] .av-5{--av-bg:rgba(60,200,220,.15);--av-fg:#6fd6e4}

    /* Hero */
    .rs-top {
        display:flex;
        align-items:center;
        gap:20px;
        flex-wrap:wrap;
        padding:26px 28px;
    }

    .rs-avatar {
        width:88px;
        height:88px;
        flex:0 0 88px;
        border-radius:26px;
        display:grid;
        place-items:center;
        background:var(--av-bg);
        color:var(--av-fg);
        font-size:1.8rem;
        font-weight:700;
    }

    .rs-who {
        flex:1 1 260px;
        min-width:0;
    }

    .rs-who h2 {
        font-size:1.55rem;
        font-weight:700;
        margin:0 0 8px;
        letter-spacing:-.01em;
        overflow-wrap:anywhere;
    }

    .rs-chip {
        display:inline-block;
        padding:4px 10px;
        border-radius:999px;
        background:var(--resident-surface-soft);
        border:1px solid var(--resident-border);
        color:var(--resident-muted);
        font-size:.8rem;
        font-weight:600;
        font-variant-numeric:tabular-nums;
    }

    .rp-pill {
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding:4px 10px;
        border-radius:999px;
        font-size:.8rem;
        font-weight:600;
    }

    .rp-pill::before {
        content:"";
        width:7px;
        height:7px;
        border-radius:50%;
        background:currentColor;
    }

    .rp-pill.ok {
        background:var(--rp-ok-soft);
        color:var(--rp-ok);
    }

    .rp-pill.off {
        background:var(--rp-off-soft);
        color:var(--rp-off);
    }

    .rs-actions {
        display:flex;
        gap:8px;
        flex-wrap:wrap;
    }

    .rs-facts {
        display:grid;
        grid-template-columns:repeat(4, 1fr);
        border-top:1px solid var(--resident-border);
        background:var(--resident-surface-soft);
    }

    .rs-fact {
        padding:16px 28px;
        border-left:1px solid var(--resident-border);
    }

    .rs-fact:first-child {
        border-left:0;
    }

    .rs-fact b {
        display:block;
        font-size:1.15rem;
        font-weight:700;
    }

    .rs-fact span {
        font-size:.82rem;
        color:var(--resident-muted);
    }

    /* Detail cards */
    .rs-card-head {
        display:flex;
        align-items:center;
        gap:12px;
        padding:18px 24px 6px;
    }

    .rs-ico {
        width:36px;
        height:36px;
        border-radius:11px;
        display:grid;
        place-items:center;
        background:var(--rp-accent-soft);
        color:var(--rp-accent);
    }

    .rs-card-head h3 {
        font-size:1rem;
        font-weight:700;
        margin:0;
    }

    .rs-list {
        margin:0;
        padding:6px 24px 14px;
    }

    .rs-list > div {
        display:grid;
        grid-template-columns:180px 1fr;
        gap:14px;
        padding:12px 0;
        border-top:1px solid var(--resident-border);
    }

    .rs-list > div:first-child {
        border-top:0;
    }

    .rs-list dt {
        color:var(--resident-muted);
        font-weight:500;
    }

    .rs-list dd {
        margin:0;
        font-weight:500;
        overflow-wrap:anywhere;
    }

    .rs-list a {
        color:var(--rp-accent);
        text-decoration:none;
    }

    .rs-list a:hover {
        text-decoration:underline;
    }

    /* Residency */
    .rs-residency-highlight {
        display:flex;
        align-items:center;
        gap:14px;
        margin:8px 24px 14px;
        padding:14px 16px;
        border-radius:12px;
        background:var(--rp-accent-soft);
    }

    .rs-residency-highlight i {
        color:var(--rp-accent);
        font-size:1.25rem;
    }

    .rs-residency-highlight strong {
        display:block;
        color:var(--resident-text);
        font-size:1.05rem;
    }

    .rs-residency-highlight span {
        display:block;
        color:var(--resident-muted);
        font-size:.82rem;
        margin-top:2px;
    }

    .rs-estimated {
        display:inline-flex;
        align-items:center;
        gap:5px;
        padding:3px 8px;
        border-radius:999px;
        background:var(--resident-surface-soft);
        border:1px solid var(--resident-border);
        color:var(--resident-muted);
        font-size:.75rem;
        font-weight:600;
    }

    @media (max-width: 767.98px) {
        .residents-page .page-title {
            font-size:1.7rem;
        }

        .rs-top {
            padding:20px;
        }

        .rs-facts {
            grid-template-columns:1fr 1fr;
        }

        .rs-fact {
            padding:14px 20px;
        }

        .rs-fact:nth-child(3) {
            border-left:0;
        }

        .rs-fact:nth-child(n+3) {
            border-top:1px solid var(--resident-border);
        }

        .rs-list > div {
            grid-template-columns:1fr;
            gap:2px;
        }
    }
</style>
@endpush


@section('content')

@php
    $fullName = trim(implode(' ', array_filter([
        $resident->first_name,
        $resident->middle_name,
        $resident->last_name,
        $resident->suffix,
    ])));

    $initials = strtoupper(
        mb_substr($resident->first_name, 0, 1) .
        mb_substr($resident->last_name, 0, 1)
    );

    $birth = $resident->birth_date
        ? \Carbon\Carbon::parse($resident->birth_date)
        : null;

    /*
     * Residency information belongs to this individual resident.
     *
     * years_of_residency is calculated by the Resident model accessor
     * and is therefore not stored as a database column.
     */
    $residencyStartYear = $resident->residency_start_year;
    $yearsOfResidency   = $resident->years_of_residency;
    $residencyEstimated = (bool) $resident->residency_year_estimated;
@endphp


<div class="container-fluid residents-page">

    <div class="mb-4">
        <div class="breadcrumb-text">
            E-POBLACION / RESIDENTS / VIEW
        </div>

        <h1 class="page-title mb-1">
            Resident Information
        </h1>

        <p class="page-subtitle mb-0">
            View the complete information of this resident.
        </p>
    </div>


    {{-- ============================================================
         HERO
         ============================================================ --}}
    <div class="card mb-3">

        <div class="rs-top">

            <div
                class="rs-avatar av-{{ $resident->id % 6 }}"
                aria-hidden="true"
            >
                {{ $initials }}
            </div>


            <div class="rs-who">

                <h2>{{ $fullName }}</h2>

                <div class="d-flex align-items-center gap-2 flex-wrap">

                    <span class="rs-chip">
                        ID {{ str_pad($resident->id, 5, '0', STR_PAD_LEFT) }}
                    </span>

                    @if($resident->status === 'Active')

                        <span class="rp-pill ok">
                            Active
                        </span>

                    @else

                        <span class="rp-pill off">
                            {{ $resident->status ? $resident->status : 'Archived' }}
                        </span>

                    @endif

                </div>

            </div>


            <div class="rs-actions">

                <a
                    href="{{ route('residents.index') }}"
                    class="btn btn-soft"
                >
                    <i class="bi bi-arrow-left me-1"></i>
                    Back
                </a>

                <a
                    href="{{ route('residents.edit', $resident->id) }}"
                    class="btn btn-primary"
                >
                    <i class="bi bi-pencil me-1"></i>
                    Edit
                </a>

                <form
                    action="{{ route('residents.destroy', $resident->id) }}"
                    method="POST"
                    class="d-inline"
                    onsubmit="return confirm('Delete this resident record? This cannot be undone.');"
                >
                    @csrf
                    @method('DELETE')

                    <button
                        type="submit"
                        class="btn btn-soft danger"
                        title="Delete resident"
                        aria-label="Delete resident"
                    >
                        <i class="bi bi-trash"></i>
                    </button>
                </form>

            </div>

        </div>


        {{-- ========================================================
             BASIC FACTS
             ======================================================== --}}
        <div class="rs-facts">

            <div class="rs-fact">
                <b>
                    {{ $birth ? $birth->age . ' years' : '—' }}
                </b>
                <span>Age</span>
            </div>

            <div class="rs-fact">
                <b>
                    {{ $resident->sex ? $resident->sex : '—' }}
                </b>
                <span>Sex</span>
            </div>

            <div class="rs-fact">
                <b>
                    {{ $resident->civil_status ? $resident->civil_status : '—' }}
                </b>
                <span>Civil status</span>
            </div>

            <div class="rs-fact">
                <b>
                    {{ $birth ? $birth->format('M d, Y') : '—' }}
                </b>
                <span>Birth date</span>
            </div>

        </div>

    </div>


    <div class="row g-3">

        {{-- ========================================================
             CONTACT
             ======================================================== --}}
        <div class="col-lg-6">

            <div class="card h-100">

                <div class="rs-card-head">
                    <span class="rs-ico">
                        <i class="bi bi-telephone"></i>
                    </span>

                    <h3>Contact</h3>
                </div>

                <dl class="rs-list">

                    <div>

                        <dt>Mobile number</dt>

                        <dd>

                            @if($resident->contact_number)

                                <a href="tel:{{ $resident->contact_number }}">
                                    {{ $resident->contact_number }}
                                </a>

                            @else

                                <span class="text-muted">
                                    Not provided
                                </span>

                            @endif

                        </dd>

                    </div>


                    <div>

                        <dt>Email address</dt>

                        <dd>

                            @if($resident->email)

                                <a href="mailto:{{ $resident->email }}">
                                    {{ $resident->email }}
                                </a>

                            @else

                                <span class="text-muted">
                                    Not provided
                                </span>

                            @endif

                        </dd>

                    </div>

                </dl>

            </div>

        </div>


        {{-- ========================================================
             ADDRESS AND WORK
             ======================================================== --}}
        <div class="col-lg-6">

            <div class="card h-100">

                <div class="rs-card-head">

                    <span class="rs-ico">
                        <i class="bi bi-geo-alt"></i>
                    </span>

                    <h3>Address and work</h3>

                </div>

                <dl class="rs-list">

                    <div>

                        <dt>Address</dt>

                        <dd>

                            @if($resident->address)

                                {{ $resident->address }}

                            @else

                                <span class="text-muted">
                                    Not provided
                                </span>

                            @endif

                        </dd>

                    </div>


                    <div>

                        <dt>Occupation</dt>

                        <dd>

                            @if($resident->occupation)

                                {{ $resident->occupation }}

                            @else

                                <span class="text-muted">
                                    Not provided
                                </span>

                            @endif

                        </dd>

                    </div>

                </dl>

            </div>

        </div>


        {{-- ========================================================
             INDIVIDUAL RESIDENCY
             ======================================================== --}}
        <div class="col-12">

            <div class="card h-100">

                <div class="rs-card-head">

                    <span class="rs-ico">
                        <i class="bi bi-house-door"></i>
                    </span>

                    <h3>Residency Information</h3>

                </div>


                @if($residencyStartYear)

                    <div class="rs-residency-highlight">

                        <i class="bi bi-calendar-check"></i>

                        <div>

                            <strong>
                                {{ $yearsOfResidency }}
                                {{ $yearsOfResidency == 1 ? 'year' : 'years' }}
                                of residency
                            </strong>

                            <span>
                                Estimated start of residency:
                                {{ $residencyStartYear }}
                            </span>

                        </div>

                    </div>

                @endif


                <dl class="rs-list">

                    <div>

                        <dt>Residency start year</dt>

                        <dd>

                            @if($residencyStartYear)

                                {{ $residencyStartYear }}

                            @else

                                <span class="text-muted">
                                    Not provided
                                </span>

                            @endif

                        </dd>

                    </div>


                    <div>

                        <dt>Years of residency</dt>

                        <dd>

                            @if($yearsOfResidency !== null)

                                {{ $yearsOfResidency }}
                                {{ $yearsOfResidency == 1 ? 'year' : 'years' }}

                            @else

                                <span class="text-muted">
                                    Not available
                                </span>

                            @endif

                        </dd>

                    </div>


                    <div>

                        <dt>Record basis</dt>

                        <dd>

                            @if($residencyStartYear)

                                @if($residencyEstimated)

                                    <span class="rs-estimated">
                                        <i class="bi bi-info-circle"></i>
                                        Estimated
                                    </span>

                                @else

                                    <span class="rs-estimated">
                                        <i class="bi bi-check-circle"></i>
                                        Recorded
                                    </span>

                                @endif

                            @else

                                <span class="text-muted">
                                    Not provided
                                </span>

                            @endif

                        </dd>

                    </div>

                </dl>


                @if($residencyStartYear)

                    <div class="px-4 pb-3">

                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Residency is recorded for this individual resident.
                            The displayed number of years is calculated from the
                            recorded start year and the current year.
                        </small>

                    </div>

                @endif

            </div>

        </div>


        {{-- ========================================================
             RECORD HISTORY
             ======================================================== --}}
        @if($resident->created_at)

            <div class="col-12">

                <div class="card">

                    <div class="rs-card-head">

                        <span class="rs-ico">
                            <i class="bi bi-clock-history"></i>
                        </span>

                        <h3>Record history</h3>

                    </div>

                    <dl class="rs-list">

                        <div>
                            <dt>Registered</dt>

                            <dd>
                                {{ $resident->created_at->format('F d, Y') }}
                            </dd>
                        </div>


                        @if($resident->updated_at)

                            <div>

                                <dt>Last updated</dt>

                                <dd>
                                    {{ $resident->updated_at->format('F d, Y') }}

                                    <span class="text-muted">
                                        ({{ $resident->updated_at->diffForHumans() }})
                                    </span>
                                </dd>

                            </div>

                        @endif

                    </dl>

                </div>

            </div>

        @endif

    </div>

</div>

@endsection
