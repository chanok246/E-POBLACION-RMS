```blade
@extends('layouts.app')

@section('content')

<div class="container-fluid py-4">

    {{-- ============================================================
        FLASH MESSAGES
    ============================================================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif


    {{-- ============================================================
        HEADER
    ============================================================= --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <a href="{{ route('disaster-events.index') }}"
                   class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i>
                </a>

                <h3 class="fw-bold mb-0">
                    Disaster Event Details
                </h3>
            </div>

            <p class="text-muted mb-0">
                Disaster monitoring, household assessment, verification and relief management.
            </p>
        </div>

        <div class="d-flex gap-2 mt-3 mt-md-0">

            @if(
                isset($disasterEvent->status) &&
                $disasterEvent->status !== 'Cancelled'
            )
                <a href="{{ route('disaster-assessments.create', ['disaster_event_id' => $disasterEvent->id]) }}"
                   class="btn btn-primary">
                    <i class="bi bi-clipboard-plus me-1"></i>
                    New Assessment
                </a>
            @endif

            <a href="{{ route('disaster-events.edit', $disasterEvent) }}"
               class="btn btn-outline-primary">
                <i class="bi bi-pencil me-1"></i>
                Edit Event
            </a>
        </div>
    </div>


    {{-- ============================================================
        DISASTER EVENT INFORMATION
    ============================================================= --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">

            <div class="row g-4">

                <div class="col-lg-8">

                    <div class="d-flex align-items-start gap-3">

                        <div class="bg-danger bg-opacity-10 rounded-3 p-3">
                            <i class="bi bi-cloud-lightning-rain-fill text-danger fs-2"></i>
                        </div>

                        <div>
                            <h4 class="fw-bold mb-1">
                                {{ $disasterEvent->name ?? 'Disaster Event' }}
                            </h4>

                            @if(!empty($disasterEvent->event_code))
                                <span class="badge bg-dark">
                                    {{ $disasterEvent->event_code }}
                                </span>
                            @endif

                            @if(!empty($disasterEvent->disaster_type))
                                <span class="badge bg-info text-dark">
                                    {{ $disasterEvent->disaster_type }}
                                </span>
                            @endif

                            @if(!empty($disasterEvent->status))
                                @php
                                    $statusClass = match($disasterEvent->status) {
                                        'Active' => 'bg-success',
                                        'Completed' => 'bg-secondary',
                                        'Cancelled' => 'bg-danger',
                                        default => 'bg-warning text-dark',
                                    };
                                @endphp

                                <span class="badge {{ $statusClass }}">
                                    {{ $disasterEvent->status }}
                                </span>
                            @endif

                            @if(!empty($disasterEvent->description))
                                <p class="text-muted mt-3 mb-0">
                                    {{ $disasterEvent->description }}
                                </p>
                            @endif
                        </div>

                    </div>
                </div>


                <div class="col-lg-4">

                    <div class="bg-light rounded-3 p-3">

                        <div class="row g-3">

                            <div class="col-6">
                                <small class="text-muted d-block">
                                    Start Date
                                </small>

                                <strong>
                                    {{ $disasterEvent->start_date
                                        ? \Carbon\Carbon::parse($disasterEvent->start_date)->format('M d, Y')
                                        : '—' }}
                                </strong>
                            </div>

                            <div class="col-6">
                                <small class="text-muted d-block">
                                    End Date
                                </small>

                                <strong>
                                    {{ $disasterEvent->end_date
                                        ? \Carbon\Carbon::parse($disasterEvent->end_date)->format('M d, Y')
                                        : '—' }}
                                </strong>
                            </div>

                            <div class="col-12">
                                <small class="text-muted d-block">
                                    Location
                                </small>

                                <strong>
                                    {{ $disasterEvent->location ?? 'Barangay Poblacion, Bani, Pangasinan' }}
                                </strong>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>
    </div>


    {{-- ============================================================
        STATISTICS
    =============================================================

        These values are calculated safely from the loaded
        disaster assessments.

    ============================================================= --}}

    @php

        $assessments = $disasterEvent->assessments ?? collect();

        $totalAssessments = $assessments->count();

        $verifiedCount = $assessments
            ->where('verification_status', 'Verified')
            ->count();

        $pendingVerificationCount = $assessments
            ->where('verification_status', 'Pending')
            ->count();

        $rejectedCount = $assessments
            ->where('verification_status', 'Rejected')
            ->count();

        $voucherEligibleCount = $assessments
            ->where('verification_status', 'Verified')
            ->where('voucher_eligible', true)
            ->count();

        $noneMinorCount = $assessments
            ->filter(function ($assessment) {
                return in_array(
                    $assessment->damage_level,
                    ['None', 'Minor']
                );
            })
            ->count();

        $moderateCount = $assessments
            ->where('damage_level', 'Moderate')
            ->count();

        $severeCount = $assessments
            ->filter(function ($assessment) {
                return in_array(
                    $assessment->damage_level,
                    ['Severe', 'Total']
                );
            })
            ->count();

        /*
        |--------------------------------------------------------------
        | Total active households
        |--------------------------------------------------------------
        */

        $totalHouseholds = \App\Models\Household::where(
            'status',
            'Active'
        )->count();

        $coverage = $totalHouseholds > 0
            ? round(($totalAssessments / $totalHouseholds) * 100, 1)
            : 0;

        if ($coverage > 100) {
            $coverage = 100;
        }

    @endphp


    {{-- ============================================================
        KPI CARDS
    ============================================================= --}}
    <div class="row g-3 mb-4">

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted">
                                Assessment Coverage
                            </small>

                            <h3 class="fw-bold mb-0 mt-1">
                                {{ $coverage }}%
                            </h3>
                        </div>

                        <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-bar-chart-fill text-primary fs-4"></i>
                        </div>

                    </div>

                    <div class="progress mt-3" style="height: 7px;">
                        <div class="progress-bar"
                             role="progressbar"
                             style="width: {{ $coverage }}%;">
                        </div>
                    </div>

                    <small class="text-muted mt-2 d-block">
                        {{ $totalAssessments }} assessed /
                        {{ $totalHouseholds }} active households
                    </small>

                </div>
            </div>
        </div>


        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted">
                                Pending Verification
                            </small>

                            <h3 class="fw-bold mb-0 mt-1">
                                {{ $pendingVerificationCount }}
                            </h3>
                        </div>

                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-hourglass-split text-warning fs-4"></i>
                        </div>

                    </div>

                    <small class="text-muted mt-3 d-block">
                        Assessments requiring official review
                    </small>

                </div>
            </div>
        </div>


        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted">
                                Verified
                            </small>

                            <h3 class="fw-bold mb-0 mt-1">
                                {{ $verifiedCount }}
                            </h3>
                        </div>

                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-patch-check-fill text-success fs-4"></i>
                        </div>

                    </div>

                    <small class="text-muted mt-3 d-block">
                        Officially verified assessments
                    </small>

                </div>
            </div>
        </div>


        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">

                    <div class="d-flex justify-content-between align-items-center">

                        <div>
                            <small class="text-muted">
                                Voucher Eligible
                            </small>

                            <h3 class="fw-bold mb-0 mt-1">
                                {{ $voucherEligibleCount }}
                            </h3>
                        </div>

                        <div class="bg-success bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-ticket-perforated-fill text-success fs-4"></i>
                        </div>

                    </div>

                    <small class="text-muted mt-3 d-block">
                        Verified and approved for relief
                    </small>

                </div>
            </div>
        </div>

    </div>


    {{-- ============================================================
        MAP + LEGEND
    ============================================================= --}}
    <div class="card border-0 shadow-sm mb-4">

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex flex-wrap justify-content-between align-items-center">

                <div>
                    <h5 class="fw-bold mb-1">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>
                        Live Disaster Assessment Map
                    </h5>

                    <small class="text-muted">
                        Assessment coverage and verified impact visualization
                    </small>
                </div>

                <div class="alert alert-info mb-0 py-2 px-3 small">
                    <i class="bi bi-info-circle me-1"></i>
                    Unmapped households are <strong>not automatically unaffected</strong>.
                </div>

            </div>

        </div>


        <div class="card-body">

            {{-- LEGEND --}}
            <div class="d-flex flex-wrap gap-3 mb-3">

                <div>
                    <span class="badge rounded-pill bg-secondary">
                        ⚪ Not Assessed
                    </span>
                </div>

                <div>
                    <span class="badge rounded-pill bg-warning text-dark">
                        🟡 No/Minor Damage
                    </span>
                </div>

                <div>
                    <span class="badge rounded-pill bg-orange"
                          style="background:#fd7e14;">
                        🟠 Moderate Impact
                    </span>
                </div>

                <div>
                    <span class="badge rounded-pill bg-danger">
                        🔴 Severe Impact
                    </span>
                </div>

                <div>
                    <span class="badge rounded-pill bg-primary">
                        🔵 For Verification
                    </span>
                </div>

                <div>
                    <span class="badge rounded-pill bg-success">
                        🟢 Verified Beneficiary
                    </span>
                </div>

            </div>


            {{-- MAP CONTAINER --}}
            <div id="disasterMap"
                 style="
                    width:100%;
                    height:500px;
                    border-radius:12px;
                    overflow:hidden;
                    background:#f1f3f5;
                 ">

                <div class="d-flex justify-content-center align-items-center h-100 text-muted">
                    <div class="text-center">
                        <i class="bi bi-map fs-1 d-block mb-2"></i>
                        <strong>Loading assessment map...</strong>
                        <div class="small">
                            Household coordinates will appear here when available.
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>


    {{-- ============================================================
        DAMAGE SUMMARY
    ============================================================= --}}
    <div class="row g-3 mb-4">

        <div class="col-lg-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h6 class="fw-bold mb-3">
                        Damage Distribution
                    </h6>

                    <div class="d-flex justify-content-between mb-2">
                        <span>
                            🟡 None / Minor
                        </span>

                        <strong>
                            {{ $noneMinorCount }}
                        </strong>
                    </div>

                    <div class="progress mb-3" style="height:8px;">
                        <div class="progress-bar bg-warning"
                             style="width:
                                {{ $totalAssessments > 0
                                    ? ($noneMinorCount / $totalAssessments) * 100
                                    : 0 }}%;">
                        </div>
                    </div>


                    <div class="d-flex justify-content-between mb-2">
                        <span>
                            🟠 Moderate
                        </span>

                        <strong>
                            {{ $moderateCount }}
                        </strong>
                    </div>

                    <div class="progress mb-3" style="height:8px;">
                        <div class="progress-bar"
                             style="
                                background:#fd7e14;
                                width:
                                {{ $totalAssessments > 0
                                    ? ($moderateCount / $totalAssessments) * 100
                                    : 0 }}%;
                             ">
                        </div>
                    </div>


                    <div class="d-flex justify-content-between mb-2">
                        <span>
                            🔴 Severe / Total
                        </span>

                        <strong>
                            {{ $severeCount }}
                        </strong>
                    </div>

                    <div class="progress" style="height:8px;">
                        <div class="progress-bar bg-danger"
                             style="width:
                                {{ $totalAssessments > 0
                                    ? ($severeCount / $totalAssessments) * 100
                                    : 0 }}%;">
                        </div>
                    </div>

                </div>

            </div>

        </div>


        <div class="col-lg-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h6 class="fw-bold mb-3">
                        Verification Status
                    </h6>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>🔵 Pending</span>
                        <strong>{{ $pendingVerificationCount }}</strong>
                    </div>

                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span>🟢 Verified</span>
                        <strong>{{ $verifiedCount }}</strong>
                    </div>

                    <div class="d-flex justify-content-between py-2">
                        <span>🔴 Rejected</span>
                        <strong>{{ $rejectedCount }}</strong>
                    </div>

                </div>

            </div>

        </div>


        <div class="col-lg-4">

            <div class="card border-0 shadow-sm h-100">

                <div class="card-body">

                    <h6 class="fw-bold mb-3">
                        Human-in-the-Loop Decision
                    </h6>

                    <div class="alert alert-warning small mb-0">

                        <i class="bi bi-shield-check me-1"></i>

                        <strong>Important:</strong>

                        The system only provides a potential eligibility recommendation.

                        <hr>

                        Final beneficiary approval remains with authorized
                        Barangay personnel after assessment verification.

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- ============================================================
        ASSESSMENTS TABLE
    ============================================================= --}}
    <div class="card border-0 shadow-sm">

        <div class="card-header bg-white border-0 py-3">

            <div class="d-flex flex-wrap justify-content-between align-items-center">

                <div>
                    <h5 class="fw-bold mb-1">
                        Household Assessments
                    </h5>

                    <small class="text-muted">
                        Assessment records for this disaster event
                    </small>
                </div>

                @if(
                    isset($disasterEvent->status) &&
                    $disasterEvent->status !== 'Cancelled'
                )
                    <a href="{{ route('disaster-assessments.create', ['disaster_event_id' => $disasterEvent->id]) }}"
                       class="btn btn-primary btn-sm">
                        <i class="bi bi-plus-lg me-1"></i>
                        Add Assessment
                    </a>
                @endif

            </div>

        </div>


        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>
                        <th>Household</th>
                        <th>Damage</th>
                        <th>Potential Eligibility</th>
                        <th>Verification</th>
                        <th>Voucher</th>
                        <th class="text-end">Action</th>
                    </tr>

                </thead>


                <tbody>

                @forelse($assessments->sortByDesc('id') as $assessment)

                    <tr>

                        {{-- HOUSEHOLD --}}
                        <td>

                            @php
                                $household = $assessment->household;
                            @endphp

                            <div class="fw-semibold">
                                {{ $household->household_number ?? 'Household #' . $assessment->household_id }}
                            </div>

                            @if($household?->householdHead)

                                <small class="text-muted">
                                    Head:
                                    {{ $household->householdHead->first_name ?? '' }}
                                    {{ $household->householdHead->last_name ?? '' }}
                                </small>

                            @endif

                        </td>


                        {{-- DAMAGE --}}
                        <td>

                            @php
                                $damageClass = match($assessment->damage_level) {
                                    'None', 'Minor' => 'bg-warning text-dark',
                                    'Moderate' => 'bg-orange text-white',
                                    'Severe', 'Total' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp

                            <span class="badge {{ $damageClass }}"
                                  @if($assessment->damage_level === 'Moderate')
                                      style="background:#fd7e14;"
                                  @endif>

                                {{ $assessment->damage_level ?? 'Not Recorded' }}

                            </span>

                        </td>


                        {{-- POTENTIAL ELIGIBILITY --}}
                        <td>

                            @php
                                $eligibilityClass = match($assessment->potential_eligibility) {
                                    'High' => 'bg-danger',
                                    'Moderate' => 'bg-warning text-dark',
                                    'Low' => 'bg-secondary',
                                    default => 'bg-light text-dark',
                                };
                            @endphp

                            <span class="badge {{ $eligibilityClass }}">
                                {{ $assessment->potential_eligibility ?? '—' }}
                            </span>

                        </td>


                        {{-- VERIFICATION --}}
                        <td>

                            @php
                                $verificationClass = match($assessment->verification_status) {
                                    'Verified' => 'bg-success',
                                    'Rejected' => 'bg-danger',
                                    'Pending' => 'bg-primary',
                                    default => 'bg-secondary',
                                };
                            @endphp

                            <span class="badge {{ $verificationClass }}">
                                {{ $assessment->verification_status ?? 'Pending' }}
                            </span>

                        </td>


                        {{-- VOUCHER --}}
                        <td>

                            @if(
                                $assessment->verification_status === 'Verified' &&
                                $assessment->voucher_eligible
                            )

                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>
                                    Eligible
                                </span>

                            @else

                                <span class="badge bg-light text-dark border">
                                    Not Approved
                                </span>

                            @endif

                        </td>


                        {{-- ACTION --}}
                        <td class="text-end">

                            <div class="btn-group">

                                <a href="{{ route('disaster-assessments.show', $assessment) }}"
                                   class="btn btn-sm btn-outline-primary"
                                   title="View Assessment">

                                    <i class="bi bi-eye"></i>

                                </a>


                                @if(
                                    $assessment->verification_status === 'Verified' &&
                                    $assessment->voucher_eligible
                                )

                                    <a href="{{ route('relief-vouchers.create', $assessment) }}"
                                       class="btn btn-sm btn-outline-success"
                                       title="Generate Relief Voucher">

                                        <i class="bi bi-ticket-perforated"></i>

                                    </a>

                                @endif

                            </div>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6" class="text-center py-5">

                            <div class="text-muted">

                                <i class="bi bi-clipboard-x fs-1 d-block mb-3"></i>

                                <h6 class="fw-bold">
                                    No assessments yet
                                </h6>

                                <p class="mb-3">
                                    No household has been assessed for this disaster event.
                                </p>

                                @if(
                                    isset($disasterEvent->status) &&
                                    $disasterEvent->status !== 'Cancelled'
                                )

                                    <a href="{{ route('disaster-assessments.create', ['disaster_event_id' => $disasterEvent->id]) }}"
                                       class="btn btn-primary btn-sm">

                                        <i class="bi bi-plus-lg me-1"></i>
                                        Create First Assessment

                                    </a>

                                @endif

                            </div>

                        </td>

                    </tr>

                @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- ============================================================
        LEAFLET MAP
    ============================================================= --}}

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    />

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js">
    </script>


    <script>

        document.addEventListener('DOMContentLoaded', function () {

            const mapElement = document.getElementById('disasterMap');

            if (!mapElement) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Barangay Poblacion / Bani fallback coordinates
            |--------------------------------------------------------------------------
            |
            | These are only a fallback center.
            | Household coordinates should eventually come from your database.
            |
            */

            const defaultLatitude = 16.185;
            const defaultLongitude = 120.385;

            const map = L.map('disasterMap').setView(
                [defaultLatitude, defaultLongitude],
                15
            );


            /*
            |--------------------------------------------------------------------------
            | OpenStreetMap
            |--------------------------------------------------------------------------
            */

            L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                {
                    maxZoom: 19,
                    attribution:
                        '&copy; OpenStreetMap contributors'
                }
            ).addTo(map);


            /*
            |--------------------------------------------------------------------------
            | Assessment Data
            |--------------------------------------------------------------------------
            */

            const assessments = @json(
                $assessments->map(function ($assessment) {

                    $household = $assessment->household;

                    return [

                        'id' =>
                            $assessment->id,

                        'household_number' =>
                            $household->household_number ?? 'Unknown',

                        'damage_level' =>
                            $assessment->damage_level ?? 'None',

                        'verification_status' =>
                            $assessment->verification_status ?? 'Pending',

                        'voucher_eligible' =>
                            (bool) $assessment->voucher_eligible,

                        /*
                        |--------------------------------------------------------------------------
                        | Coordinates
                        |--------------------------------------------------------------------------
                        |
                        | Change these property names if your household table uses
                        | different coordinate column names.
                        |
                        */

                        'latitude' =>
                            $household->latitude ?? null,

                        'longitude' =>
                            $household->longitude ?? null,

                    ];

                })->values()
            );


            /*
            |--------------------------------------------------------------------------
            | Determine Marker Status
            |--------------------------------------------------------------------------
            */

            function getMarkerColor(assessment) {

                if (
                    assessment.verification_status === 'Verified' &&
                    assessment.voucher_eligible === true
                ) {
                    return '#198754';
                }

                if (
                    assessment.verification_status === 'Pending'
                ) {
                    return '#0d6efd';
                }

                switch (assessment.damage_level) {

                    case 'Severe':
                    case 'Total':
                        return '#dc3545';

                    case 'Moderate':
                        return '#fd7e14';

                    case 'None':
                    case 'Minor':
                        return '#ffc107';

                    default:
                        return '#6c757d';
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Add Markers
            |--------------------------------------------------------------------------
            */

            let markers = [];

            assessments.forEach(function (assessment) {

                if (
                    assessment.latitude === null ||
                    assessment.longitude === null
                ) {
                    return;
                }


                const color = getMarkerColor(assessment);


                const marker = L.circleMarker(
                    [
                        parseFloat(assessment.latitude),
                        parseFloat(assessment.longitude)
                    ],
                    {
                        radius: 9,
                        fillColor: color,
                        color: '#ffffff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.9
                    }
                );


                marker.bindPopup(`

                    <div style="min-width:220px;">

                        <h6 class="fw-bold mb-2">
                            Household ${assessment.household_number}
                        </h6>

                        <div class="mb-1">
                            <strong>Damage:</strong>
                            ${assessment.damage_level}
                        </div>

                        <div class="mb-1">
                            <strong>Verification:</strong>
                            ${assessment.verification_status}
                        </div>

                        <div class="mb-3">
                            <strong>Voucher:</strong>
                            ${
                                assessment.voucher_eligible
                                    ? 'Eligible'
                                    : 'Not Approved'
                            }
                        </div>

                        <a
                            href="/disaster-assessments/${assessment.id}"
                            class="btn btn-sm btn-primary w-100"
                        >
                            View Assessment
                        </a>

                    </div>

                `);


                marker.addTo(map);

                markers.push(marker);

            });


            /*
            |--------------------------------------------------------------------------
            | Automatically Fit Map
            |--------------------------------------------------------------------------
            */

            if (markers.length > 0) {

                const group = L.featureGroup(markers);

                map.fitBounds(
                    group.getBounds().pad(0.15)
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Fix Leaflet rendering when inside Bootstrap cards
            |--------------------------------------------------------------------------
            */

            setTimeout(function () {
                map.invalidateSize();
            }, 300);

        });

    </script>


    {{-- ============================================================
        CUSTOM STYLING
    ============================================================= --}}
    <style>

        .card {
            border-radius: 12px;
        }

        .table > :not(caption) > * > * {
            padding: 0.85rem 0.75rem;
        }

        .progress {
            border-radius: 10px;
        }

        .progress-bar {
            border-radius: 10px;
        }

        .badge {
            font-weight: 500;
        }

        #disasterMap {
            z-index: 1;
        }

        .leaflet-popup-content-wrapper {
            border-radius: 10px;
        }

    </style>

</div>

@endsection
