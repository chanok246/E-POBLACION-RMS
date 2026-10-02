@extends('layouts.app')

@section('title', 'Household Map')

@section('content')

@php

/*
|--------------------------------------------------------------------------
| HOUSEHOLD DATA SAFETY
|--------------------------------------------------------------------------
*/

$mapData = collect($mapHouseholds ?? $households ?? []);

/*
|--------------------------------------------------------------------------
| USER ROLE
|--------------------------------------------------------------------------
*/

$currentUser = Auth::user();

$isCaptain = $currentUser
    && $currentUser->role === 'captain';

$isSecretary = $currentUser
    && $currentUser->role === 'secretary';

$isTreasurer = $currentUser
    && $currentUser->role === 'treasurer';


@endphp

<div class="container-fluid">

{{-- ============================================================
     PAGE HEADER
============================================================= --}}

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4">

    <div>

        <div class="text-muted small fw-semibold mb-1">
            E-POBLACION / HOUSEHOLD MANAGEMENT
        </div>

        <h2 class="fw-bold mb-1">

            <i class="bi bi-map-fill me-2"></i>

            Household Map

        </h2>

        <p class="text-muted mb-0">

            Interactive household location map with disaster
            assessment and relief voucher monitoring.

        </p>

    </div>


    <div class="d-flex flex-wrap gap-2 mt-3 mt-md-0">

        <a
            href="{{ route('households.index') }}"
            class="btn btn-outline-primary"
        >

            <i class="bi bi-people-fill me-1"></i>

            Household Records

        </a>


        <button
            type="button"
            class="btn btn-primary"
            id="resetMapButton"
        >

            <i class="bi bi-buildings me-1"></i>

            Bani

        </button>

    </div>

</div>


{{-- ============================================================
     TREASURER VIEW-ONLY NOTICE
============================================================= --}}

@if($isTreasurer)

    <div class="alert alert-info border-0 shadow-sm">

        <i class="bi bi-eye me-2"></i>

        <strong>View-Only Access:</strong>

        You can view household locations and related information,
        but household records cannot be added, edited, archived,
        restored, or deleted from this account.

    </div>

@endif


{{-- ============================================================
     MAP STATISTICS
============================================================= --}}

<div class="row g-3 mb-4">

    <div class="col-md-6 col-xl-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="small text-muted mb-1">
                            Total Households
                        </div>

                        <h3 class="fw-bold mb-0">
                            {{ $mapData->count() }}
                        </h3>

                    </div>

                    <div class="map-stat-icon">

                        <i class="bi bi-house-fill"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="col-md-6 col-xl-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="small text-muted mb-1">
                            With Coordinates
                        </div>

                        <h3
                            class="fw-bold mb-0"
                            id="mappedHouseholdCount"
                        >
                            0
                        </h3>

                    </div>

                    <div class="map-stat-icon">

                        <i class="bi bi-geo-alt-fill"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="col-md-6 col-xl-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="small text-muted mb-1">
                            Disaster Affected
                        </div>

                        <h3
                            class="fw-bold mb-0"
                            id="affectedHouseholdCount"
                        >
                            0
                        </h3>

                    </div>

                    <div class="map-stat-icon">

                        <i class="bi bi-exclamation-triangle-fill"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="col-md-6 col-xl-3">

        <div class="card border-0 shadow-sm h-100">

            <div class="card-body">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <div class="small text-muted mb-1">
                            Voucher Eligible
                        </div>

                        <h3
                            class="fw-bold mb-0"
                            id="eligibleHouseholdCount"
                        >
                            0
                        </h3>

                    </div>

                    <div class="map-stat-icon">

                        <i class="bi bi-ticket-perforated-fill"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


{{-- ============================================================
     MAP CARD
============================================================= --}}

<div class="card border-0 shadow-sm mb-4">

    <div class="card-header bg-white border-0 py-3">

        <div class="row align-items-center g-3">

            <div class="col-lg-5">

                <h5 class="fw-bold mb-1">

                    <i class="bi bi-geo-alt-fill me-2"></i>

                    Household Location Map

                </h5>

                <small class="text-muted">

                    Showing the Bani, Pangasinan
                    administrative boundary.

                </small>

            </div>


            <div class="col-lg-7">

                <div class="d-flex flex-wrap justify-content-lg-end gap-2">

                    <div class="input-group map-search-box">

                        <span class="input-group-text bg-white">

                            <i class="bi bi-search"></i>

                        </span>

                        <input
                            type="text"
                            id="householdSearch"
                            class="form-control"
                            placeholder="Search household, head, address..."
                        >

                    </div>


                    <select
                        id="mapFilter"
                        class="form-select map-filter"
                    >

                        <option value="all">
                            All Households
                        </option>

                        <option value="mapped">
                            With Coordinates
                        </option>

                        <option value="affected">
                            Disaster Affected
                        </option>

                        <option value="eligible">
                            Voucher Eligible
                        </option>

                        <option value="voucher-issued">
                            Voucher Issued
                        </option>

                        <option value="severe">
                            Severe / Total Damage
                        </option>

                    </select>

                </div>

            </div>

        </div>

    </div>


    <div class="card-body p-0">

        {{-- ====================================================
             MAP
        ===================================================== --}}

        <div
            id="householdMap"
            class="household-map"
        ></div>


        {{-- ====================================================
             TOOLBAR
        ===================================================== --}}

        <div class="map-toolbar border-top">

            <div class="d-flex flex-wrap align-items-center gap-2">

                <button
                    type="button"
                    class="btn btn-sm btn-outline-primary"
                    id="fitHouseholdsButton"
                >

                    <i class="bi bi-arrows-fullscreen me-1"></i>

                    Fit Households

                </button>


                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    id="zoomBaniButton"
                >

                    <i class="bi bi-buildings me-1"></i>

                    Bani

                </button>


                <span
                    class="small text-muted ms-md-2"
                    id="mapStatus"
                >

                    Loading map...

                </span>

            </div>

        </div>


        {{-- ====================================================
             LEGEND
        ===================================================== --}}

        <div class="map-legend border-top">

            <div class="fw-bold small mb-2">
                Map Legend
            </div>


            <div class="d-flex flex-wrap gap-3">

                <span class="legend-item">

                    <span class="legend-dot dot-blue"></span>

                    Normal Household

                </span>


                <span class="legend-item">

                    <span class="legend-dot dot-orange"></span>

                    Disaster Affected

                </span>


                <span class="legend-item">

                    <span class="legend-dot dot-red"></span>

                    Severe / Total Damage

                </span>


                <span class="legend-item">

                    <span class="legend-dot dot-green"></span>

                    Relief Voucher Issued

                </span>


                <span class="legend-item">

                    <span class="legend-dot dot-purple"></span>

                    Voucher Eligible

                </span>


                <span class="legend-item">

                    <span class="legend-boundary"></span>

                    Bani Municipal Boundary

                </span>

            </div>

        </div>

    </div>

</div>


{{-- ============================================================
     HOUSEHOLD TABLE
============================================================= --}}

<div class="card border-0 shadow-sm">

    <div class="card-header bg-white border-0 py-3">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h5 class="fw-bold mb-1">
                    Household Locations
                </h5>

                <small class="text-muted">
                    Households currently available to the map.
                </small>

            </div>


            <span
                class="badge bg-light text-dark"
                id="visibleHouseholdBadge"
            >
                0 visible
            </span>

        </div>

    </div>


    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover align-middle mb-0">

                <thead class="table-light">

                    <tr>

                        <th class="px-4">
                            Household
                        </th>

                        <th>
                            Household Head
                        </th>

                        <th>
                            Address
                        </th>

                        <th>
                            Purok
                        </th>

                        <th>
                            Coordinates
                        </th>

                        <th>
                            Disaster
                        </th>

                        <th>
                            Voucher
                        </th>

                        <th class="text-end px-4">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody id="householdTableBody">

                    <tr>

                        <td
                            colspan="8"
                            class="text-center text-muted py-5"
                        >

                            Loading household records...

                        </td>

                    </tr>

                </tbody>

            </table>

        </div>

    </div>

</div>

</div>

{{-- ================================================================
LEAFLET CSS
================================================================= --}}

@push('styles')

<link
    rel="stylesheet"
    href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
    crossorigin=""
>

<style>

.map-stat-icon {

    width: 48px;
    height: 48px;

    display: flex;
    align-items: center;
    justify-content: center;

    border-radius: 50%;

    background: #eef5f9;

    color: #123f5b;

    font-size: 20px;

}


.household-map {

    width: 100% !important;

    height: 650px !important;

    min-height: 500px;

    position: relative;

    z-index: 1;

    background: #dbe7ed;

    overflow: hidden;

}


.household-map .leaflet-container {

    width: 100%;

    height: 100%;

}


.household-map .leaflet-tile {

    max-width: none !important;

    max-height: none !important;

    width: 256px !important;

    height: 256px !important;

}


.household-map img {

    max-width: none !important;

}


.map-search-box {

    width: 300px;

}


.map-filter {

    width: 190px;

}


.map-toolbar {

    padding: 12px 16px;

    background: #ffffff;

}


.map-legend {

    padding: 15px 18px;

    background: #ffffff;

}


.legend-item {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    font-size: 12px;

    color: #52636f;

}


.legend-dot {

    width: 13px;

    height: 13px;

    display: inline-block;

    border-radius: 50%;

    border: 2px solid #ffffff;

    box-shadow: 0 0 0 1px #adb5bd;

}


.dot-blue {
    background: #0d6efd;
}


.dot-orange {
    background: #fd7e14;
}


.dot-red {
    background: #dc3545;
}


.dot-green {
    background: #198754;
}


.dot-purple {
    background: #6f42c1;
}


.legend-boundary {

    width: 28px;

    height: 13px;

    display: inline-block;

    border: 3px solid #0d6efd;

    border-radius: 3px;

    background: rgba(13, 110, 253, 0.08);

}


.household-popup {

    min-width: 260px;

    max-width: 320px;

    font-size: 13px;

    line-height: 1.5;

}


.household-popup-title {

    font-size: 15px;

    font-weight: 700;

    margin-bottom: 2px;

}


.household-popup-head {

    font-weight: 600;

    margin-bottom: 8px;

}


.household-popup-row {

    margin-bottom: 4px;

}


.household-popup-actions {

    margin-top: 10px;

}


.bani-label {

    background: rgba(255, 255, 255, 0.96);

    border: 2px solid #0d6efd;

    color: #084298;

    font-weight: 700;

    font-size: 12px;

    padding: 5px 9px;

    border-radius: 6px;

    box-shadow: 0 2px 7px rgba(0, 0, 0, 0.20);

    white-space: nowrap;

}


.bani-label::before {

    display: none;

}


.bani-boundary {

    fill-opacity: 0.08;

    stroke-opacity: 1;

}


.map-location-info {

    background: rgba(255, 255, 255, 0.96);

    padding: 10px 13px;

    border-radius: 8px;

    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.20);

    font-size: 12px;

}


.leaflet-container {

    font-family: inherit;

}


.leaflet-popup-content-wrapper {

    border-radius: 10px;

}


.leaflet-popup-content {

    margin: 13px 15px;

}


.coordinate-text {

    font-family: monospace;

    font-size: 11px;

}


@media (max-width: 768px) {

    .household-map {

        height: 500px !important;

    }


    .map-search-box {

        width: 100%;

    }


    .map-filter {

        width: 100%;

    }

}

</style>

@endpush

{{-- ================================================================
LEAFLET JS
================================================================= --}}

@push('scripts')

<script
    src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
    crossorigin=""
></script>

<script>

document.addEventListener('DOMContentLoaded', function () {

    'use strict';


    /* =========================================================
       MAP ELEMENT
    ========================================================== */

    const mapElement =
        document.getElementById('householdMap');


    if (!mapElement) {

        console.error(
            'Household map element was not found.'
        );

        return;

    }


    /* =========================================================
       HOUSEHOLD DATA
    ========================================================== */

    const rawHouseholds =
        @json($mapData->values());


    const households =
        Array.isArray(rawHouseholds)
            ? rawHouseholds
            : [];


    console.log(
        'Household map data:',
        households
    );


    /* =========================================================
       BANI FALLBACK CENTER
    ========================================================== */

    const BANI_CENTER = [
        16.1857,
        120.0566
    ];


    /*
    |--------------------------------------------------------------------------
    | Fallback view only.
    |
    | The actual Bani municipal boundary is obtained from
    | OpenStreetMap through Nominatim below.
    |--------------------------------------------------------------------------
    */

    const BANI_FALLBACK_ZOOM = 13;


    /* =========================================================
       CREATE MAP
    ========================================================== */

    const map =
        L.map(
            'householdMap',
            {
                center: BANI_CENTER,

                zoom: BANI_FALLBACK_ZOOM,

                zoomControl: true,

                attributionControl: true,

                preferCanvas: true
            }
        );


    /* =========================================================
       OPENSTREETMAP TILE LAYER
    ========================================================== */

    const osmLayer =
        L.tileLayer(
            'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
            {
                maxZoom: 19,

                minZoom: 9,

                tileSize: 256,

                zoomOffset: 0,

                updateWhenZooming: true,

                updateWhenIdle: true,

                keepBuffer: 3,

                attribution:
                    '&copy; OpenStreetMap contributors'
            }
        );


    osmLayer.addTo(map);


    /* =========================================================
       LAYERS
    ========================================================== */

    const householdLayer =
        L.layerGroup().addTo(map);


    let baniBoundaryLayer = null;

    let baniBounds = null;


    /* =========================================================
       MARKER STORAGE
    ========================================================== */

    const markerRecords = [];


    /* =========================================================
       HELPER FUNCTIONS
    ========================================================== */

    function safeString(
        value,
        fallback = 'N/A'
    ) {

        if (
            value === null
            ||
            value === undefined
            ||
            String(value).trim() === ''
        ) {

            return fallback;

        }

        return String(value);

    }


    function escapeHtml(value) {

        const text =
            safeString(
                value,
                ''
            );

        return text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }


    function getValue(
        object,
        keys,
        fallback = null
    ) {

        for (
            const key of keys
        ) {

            if (
                object
                &&
                object[key] !== undefined
                &&
                object[key] !== null
            ) {

                return object[key];

            }

        }

        return fallback;

    }


    function getHouseholdId(
        household
    ) {

        return getValue(
            household,
            [
                'id',
                'household_id'
            ],
            ''
        );

    }


    function getHouseholdNumber(
        household
    ) {

        return getValue(
            household,
            [
                'household_number',
                'household_no',
                'household_code',
                'code',
                'name'
            ],
            'Household'
        );

    }


    function getAddress(
        household
    ) {

        return getValue(
            household,
            [
                'address',
                'full_address',
                'location'
            ],
            'No address'
        );

    }


    function getPurok(
        household
    ) {

        return getValue(
            household,
            [
                'purok',
                'purok_name'
            ],
            'N/A'
        );

    }


    function getLatitude(
        household
    ) {

        const value =
            getValue(
                household,
                [
                    'latitude',
                    'lat'
                ],
                null
            );


        const number =
            Number(value);


        return Number.isFinite(number)
            ? number
            : null;

    }


    function getLongitude(
        household
    ) {

        const value =
            getValue(
                household,
                [
                    'longitude',
                    'lng',
                    'lon'
                ],
                null
            );


        const number =
            Number(value);


        return Number.isFinite(number)
            ? number
            : null;

    }


    function getHouseholdHead(
        household
    ) {

        const head =
            getValue(
                household,
                [
                    'household_head',
                    'householdHead',
                    'head',
                    'head_name'
                ],
                null
            );


        if (
            typeof head === 'string'
            &&
            head.trim() !== ''
        ) {

            return head;

        }


        if (
            head
            &&
            typeof head === 'object'
        ) {

            if (
                head.full_name
            ) {

                return head.full_name;

            }


            if (
                head.name
            ) {

                return head.name;

            }


            const fullName =
                [
                    head.first_name,
                    head.middle_name,
                    head.last_name,
                    head.suffix
                ]
                .filter(
                    value =>
                        value
                        &&
                        String(value).trim() !== ''
                )
                .join(' ');


            if (
                fullName.trim() !== ''
            ) {

                return fullName;

            }

        }


        const directName =
            [
                household.first_name,
                household.middle_name,
                household.last_name
            ]
            .filter(
                value =>
                    value
                    &&
                    String(value).trim() !== ''
            )
            .join(' ');


        return directName || 'Household Head';

    }


    function getAssessment(
        household
    ) {

        return getValue(
            household,
            [
                'assessment',
                'disaster_assessment'
            ],
            null
        );

    }


    function getVoucher(
        household
    ) {

        return getValue(
            household,
            [
                'voucher',
                'relief_voucher'
            ],
            null
        );

    }


    function getDamageLevel(
        household
    ) {

        return getValue(
            household,
            [
                'damage_level'
            ],
            null
        );

    }


    function getVerificationStatus(
        household
    ) {

        return getValue(
            household,
            [
                'verification_status'
            ],
            null
        );

    }


    function getVoucherEligible(
        household
    ) {

        const direct =
            getValue(
                household,
                [
                    'voucher_eligible'
                ],
                null
            );


        if (
            direct !== null
        ) {

            return (
                direct === true
                ||
                direct === 1
                ||
                direct === '1'
                ||
                direct === 'true'
                ||
                direct === 'True'
            );

        }


        const assessment =
            getAssessment(
                household
            );


        if (
            assessment
            &&
            typeof assessment === 'object'
        ) {

            return (
                assessment.voucher_eligible === true
                ||
                assessment.voucher_eligible === 1
                ||
                assessment.voucher_eligible === '1'
            );

        }


        return false;

    }


    function getHasAssessment(
        household
    ) {

        const direct =
            getValue(
                household,
                [
                    'has_assessment'
                ],
                null
            );


        if (
            direct !== null
        ) {

            return (
                direct === true
                ||
                direct === 1
                ||
                direct === '1'
                ||
                direct === 'true'
                ||
                direct === 'True'
            );

        }


        return !!getAssessment(
            household
        );

    }


    function getDisasterName(
        household
    ) {

        const direct =
            getValue(
                household,
                [
                    'disaster_event',
                    'disaster_event_name'
                ],
                null
            );


        if (
            direct
        ) {

            return direct;

        }


        const assessment =
            getAssessment(
                household
            );


        if (
            assessment
            &&
            typeof assessment === 'object'
        ) {

            const event =
                assessment.disaster_event
                ??
                assessment.disasterEvent;


            if (
                event
                &&
                typeof event === 'object'
            ) {

                return (
                    event.name
                    ??
                    event.title
                    ??
                    'Disaster Event'
                );

            }

        }


        return 'No disaster assessment';

    }


    function getVoucherStatus(
        household
    ) {

        const direct =
            getValue(
                household,
                [
                    'voucher_status'
                ],
                null
            );


        if (
            direct
        ) {

            return direct;

        }


        const voucher =
            getVoucher(
                household
            );


        if (
            voucher
            &&
            typeof voucher === 'object'
        ) {

            return (
                voucher.status
                ??
                'Issued'
            );

        }


        return null;

    }


    function getVoucherCode(
        household
    ) {

        const direct =
            getValue(
                household,
                [
                    'voucher_code'
                ],
                null
            );


        if (
            direct
        ) {

            return direct;

        }


        const voucher =
            getVoucher(
                household
            );


        if (
            voucher
            &&
            typeof voucher === 'object'
        ) {

            return (
                voucher.voucher_code
                ??
                null
            );

        }


        return null;

    }


    function getAssessmentUrl(
        household
    ) {

        return getValue(
            household,
            [
                'assessment_url'
            ],
            null
        );

    }


    function getVoucherUrl(
        household
    ) {

        return getValue(
            household,
            [
                'voucher_url'
            ],
            null
        );

    }


    /* =========================================================
       MARKER COLOR
    ========================================================== */

    function getMarkerColor(
        household
    ) {

        const voucherStatus =
            getVoucherStatus(
                household
            );


        const damage =
            String(
                getDamageLevel(
                    household
                )
                ??
                ''
            )
            .toLowerCase();


        const eligible =
            getVoucherEligible(
                household
            );


        const assessed =
            getHasAssessment(
                household
            );


        if (
            voucherStatus
            &&
            String(voucherStatus).toLowerCase()
                === 'issued'
        ) {

            return '#198754';

        }


        if (
            damage === 'severe'
            ||
            damage === 'total'
        ) {

            return '#dc3545';

        }


        if (
            eligible
        ) {

            return '#6f42c1';

        }


        if (
            assessed
            &&
            damage !== ''
            &&
            damage !== 'none'
        ) {

            return '#fd7e14';

        }


        return '#0d6efd';

    }


    /* =========================================================
       CREATE MARKER
    ========================================================== */

    function createMarker(
        household,
        latitude,
        longitude
    ) {

        const markerColor =
            getMarkerColor(
                household
            );


        const marker =
            L.circleMarker(
                [
                    latitude,
                    longitude
                ],
                {
                    radius: 9,

                    fillColor:
                        markerColor,

                    color:
                        '#ffffff',

                    weight: 2,

                    opacity: 1,

                    fillOpacity: 0.92
                }
            );


        const householdNumber =
            getHouseholdNumber(
                household
            );


        const headName =
            getHouseholdHead(
                household
            );


        const address =
            getAddress(
                household
            );


        const purok =
            getPurok(
                household
            );


        const damage =
            getDamageLevel(
                household
            )
            ??
            'None';


        const verification =
            getVerificationStatus(
                household
            )
            ??
            'Not Assessed';


        const disaster =
            getDisasterName(
                household
            );


        const eligible =
            getVoucherEligible(
                household
            );


        const voucherStatus =
            getVoucherStatus(
                household
            );


        const voucherCode =
            getVoucherCode(
                household
            );


        const assessmentUrl =
            getAssessmentUrl(
                household
            );


        const voucherUrl =
            getVoucherUrl(
                household
            );


        let eligibilityHtml =
            '<span class="badge bg-secondary">No</span>';


        if (
            eligible
        ) {

            eligibilityHtml =
                '<span class="badge bg-success">Eligible</span>';

        }


        let voucherHtml =
            '<span class="text-muted">No voucher</span>';


        if (
            voucherStatus
        ) {

            let badge =
                'bg-primary';


            const status =
                String(
                    voucherStatus
                )
                .toLowerCase();


            if (
                status === 'claimed'
            ) {

                badge =
                    'bg-success';

            }


            if (
                status === 'cancelled'
            ) {

                badge =
                    'bg-danger';

            }


            if (
                status === 'expired'
            ) {

                badge =
                    'bg-secondary';

            }


            voucherHtml =
                `
                <span class="badge ${badge}">
                    ${escapeHtml(voucherStatus)}
                </span>
                `;

        }


        let assessmentButton = '';


        if (
            assessmentUrl
        ) {

            assessmentButton =
                `
                <a
                    href="${escapeHtml(assessmentUrl)}"
                    class="btn btn-sm btn-outline-primary w-100 mb-2"
                >
                    <i class="bi bi-clipboard2-pulse me-1"></i>
                    View Assessment
                </a>
                `;

        }


        let voucherButton = '';


        if (
            voucherUrl
        ) {

            voucherButton =
                `
                <a
                    href="${escapeHtml(voucherUrl)}"
                    class="btn btn-sm btn-outline-success w-100"
                >
                    <i class="bi bi-ticket-perforated me-1"></i>
                    View Relief Voucher
                </a>
                `;

        }


        let voucherCodeHtml = '';


        if (
            voucherCode
        ) {

            voucherCodeHtml =
                `
                <div class="household-popup-row">

                    <strong>Voucher Code:</strong>

                    ${escapeHtml(voucherCode)}

                </div>
                `;

        }


        const popup =
            `
            <div class="household-popup">

                <div class="household-popup-title">
                    ${escapeHtml(householdNumber)}
                </div>

                <div class="household-popup-head">
                    ${escapeHtml(headName)}
                </div>

                <div class="household-popup-row">

                    <i class="bi bi-geo-alt me-1"></i>

                    ${escapeHtml(address)}

                </div>

                <div class="household-popup-row">

                    <strong>Purok:</strong>

                    ${escapeHtml(purok)}

                </div>

                <hr class="my-2">

                <div class="household-popup-row">

                    <strong>Disaster:</strong>

                    ${escapeHtml(disaster)}

                </div>

                <div class="household-popup-row">

                    <strong>Damage:</strong>

                    ${escapeHtml(damage)}

                </div>

                <div class="household-popup-row">

                    <strong>Verification:</strong>

                    ${escapeHtml(verification)}

                </div>

                <div class="household-popup-row">

                    <strong>Voucher Eligible:</strong>

                    ${eligibilityHtml}

                </div>

                <div class="household-popup-row">

                    <strong>Voucher:</strong>

                    ${voucherHtml}

                </div>

                ${voucherCodeHtml}

                <div class="household-popup-actions">

                    ${assessmentButton}

                    ${voucherButton}

                </div>

            </div>
            `;


        marker.bindPopup(
            popup,
            {
                maxWidth: 330
            }
        );


        marker.addTo(
            householdLayer
        );


        markerRecords.push(
            {
                marker:
                    marker,

                household:
                    household,

                latitude:
                    latitude,

                longitude:
                    longitude,

                searchText:
                    [
                        householdNumber,
                        headName,
                        address,
                        purok,
                        disaster,
                        voucherCode
                    ]
                    .join(' ')
                    .toLowerCase()
            }
        );


        return marker;

    }


    /* =========================================================
       HOUSEHOLD MARKERS
    ========================================================== */

    let mappedCount = 0;

    let affectedCount = 0;

    let eligibleCount = 0;


    households.forEach(
        function (household) {

            const latitude =
                getLatitude(
                    household
                );


            const longitude =
                getLongitude(
                    household
                );


            if (
                latitude === null
                ||
                longitude === null
            ) {

                return;

            }


            if (
                latitude < -90
                ||
                latitude > 90
                ||
                longitude < -180
                ||
                longitude > 180
            ) {

                return;

            }


            mappedCount++;


            const damage =
                String(
                    getDamageLevel(
                        household
                    )
                    ??
                    ''
                )
                .toLowerCase();


            const affected =
                getHasAssessment(
                    household
                )
                &&
                damage !== ''
                &&
                damage !== 'none';


            if (
                affected
            ) {

                affectedCount++;

            }


            if (
                getVoucherEligible(
                    household
                )
            ) {

                eligibleCount++;

            }


            createMarker(
                household,
                latitude,
                longitude
            );

        }
    );


    /* =========================================================
       UPDATE STATISTICS
    ========================================================== */

    const mappedCountElement =
        document.getElementById(
            'mappedHouseholdCount'
        );


    if (
        mappedCountElement
    ) {

        mappedCountElement.textContent =
            mappedCount;

    }


    const affectedCountElement =
        document.getElementById(
            'affectedHouseholdCount'
        );


    if (
        affectedCountElement
    ) {

        affectedCountElement.textContent =
            affectedCount;

    }


    const eligibleCountElement =
        document.getElementById(
            'eligibleHouseholdCount'
        );


    if (
        eligibleCountElement
    ) {

        eligibleCountElement.textContent =
            eligibleCount;

    }


    /* =========================================================
       BANI CITY/MUNICIPALITY LABEL
    ========================================================== */

    const baniLabel =
        L.marker(
            BANI_CENTER,
            {
                interactive: false,

                icon:
                    L.divIcon(
                        {
                            className:
                                'bani-label',

                            html:
                                `
                                BANI
                                <br>
                                <small>PANGASINAN</small>
                                `,

                            iconSize:
                                [
                                    150,
                                    42
                                ],

                            iconAnchor:
                                [
                                    75,
                                    21
                                ]
                        }
                    )
            }
        );


    baniLabel.addTo(
        map
    );


    /* =========================================================
       LOCATION INFO
    ========================================================== */

    const locationInfo =
        L.control(
            {
                position: 'topright'
            }
        );


    locationInfo.onAdd =
        function () {

            const div =
                L.DomUtil.create(
                    'div',
                    'map-location-info'
                );


            div.innerHTML =
                `
                <div class="fw-bold">

                    <i class="bi bi-pin-map-fill me-1"></i>

                    Bani

                </div>

                <div class="text-muted">

                    Pangasinan, Philippines

                </div>

                <div
                    class="small mt-1"
                    id="boundaryStatus"
                >

                    Loading municipal boundary...

                </div>

                <div class="small mt-1">

                    ${mappedCount}
                    mapped household(s)

                </div>
                `;


            return div;

        };


    locationInfo.addTo(
        map
    );


    /* =========================================================
       SCALE
    ========================================================== */

    L.control.scale(
        {
            imperial: false
        }
    ).addTo(
        map
    );


    /* =========================================================
       LOAD ACTUAL BANI MUNICIPAL BOUNDARY
       FROM OPENSTREETMAP / NOMINATIM
    ========================================================== */

    async function loadBaniBoundary() {

        const boundaryStatus =
            document.getElementById(
                'boundaryStatus'
            );


        try {

            if (
                boundaryStatus
            ) {

                boundaryStatus.innerHTML =
                    `
                    <i class="bi bi-arrow-repeat me-1"></i>
                    Loading official map boundary...
                    `;

            }


            const url =
                'https://nominatim.openstreetmap.org/search' +
                '?format=json' +
                '&polygon_geojson=1' +
                '&limit=1' +
                '&q=' +
                encodeURIComponent(
                    'Bani, Pangasinan, Philippines'
                );


            const response =
                await fetch(
                    url,
                    {
                        method: 'GET',

                        headers: {
                            'Accept':
                                'application/json'
                        }
                    }
                );


            if (
                !response.ok
            ) {

                throw new Error(
                    'Boundary request failed.'
                );

            }


            const results =
                await response.json();


            if (
                !Array.isArray(results)
                ||
                results.length === 0
            ) {

                throw new Error(
                    'Bani municipal boundary was not found.'
                );

            }


            const result =
                results[0];


            if (
                !result.geojson
            ) {

                throw new Error(
                    'Boundary geometry was not returned.'
                );

            }


            /* ------------------------------------------------------
               DRAW ACTUAL BANI MUNICIPAL BOUNDARY
            ------------------------------------------------------ */

            baniBoundaryLayer =
                L.geoJSON(
                    result.geojson,
                    {
                        style:
                            function () {

                                return {

                                    color:
                                        '#0d6efd',

                                    weight:
                                        4,

                                    opacity:
                                        0.95,

                                    fillColor:
                                        '#0d6efd',

                                    fillOpacity:
                                        0.07,

                                    lineJoin:
                                        'round',

                                    lineCap:
                                        'round',

                                    className:
                                        'bani-boundary'
                                };

                            },

                        interactive:
                            false
                    }
                );


            baniBoundaryLayer.addTo(
                map
            );


            /* ------------------------------------------------------
               GET ACTUAL BANI BOUNDARY BOUNDS
            ------------------------------------------------------ */

            baniBounds =
                baniBoundaryLayer.getBounds();


            if (
                baniBounds
                &&
                baniBounds.isValid()
            ) {

                map.fitBounds(
                    baniBounds,
                    {
                        padding:
                            [
                                40,
                                40
                            ],

                        maxZoom:
                            14,

                        animate:
                            false
                    }
                );

            }


            if (
                boundaryStatus
            ) {

                boundaryStatus.innerHTML =
                    `
                    <i class="bi bi-check-circle-fill me-1 text-success"></i>

                    Bani municipal boundary loaded

                    `;

            }


            console.log(
                'Bani municipal boundary loaded:',
                result
            );

        }
        catch (error) {

            console.error(
                'Unable to load Bani municipal boundary:',
                error
            );


            if (
                boundaryStatus
            ) {

                boundaryStatus.innerHTML =
                    `
                    <i class="bi bi-exclamation-circle me-1 text-warning"></i>

                    Municipal boundary unavailable
                    `;

            }


            /* ------------------------------------------------------
               SAFE FALLBACK
            ------------------------------------------------------ */

            map.setView(
                BANI_CENTER,
                BANI_FALLBACK_ZOOM
            );

        }

    }


    /* =========================================================
       FIT HOUSEHOLDS
    ========================================================== */

    function fitHouseholds() {

        const visibleMarkers =
            markerRecords.filter(
                function (record) {

                    return map.hasLayer(
                        record.marker
                    );

                }
            );


        if (
            visibleMarkers.length === 0
        ) {

            if (
                baniBounds
                &&
                baniBounds.isValid()
            ) {

                map.fitBounds(
                    baniBounds,
                    {
                        padding:
                            [
                                40,
                                40
                            ],

                        maxZoom:
                            14,

                        animate:
                            true
                    }
                );

            }
            else {

                map.setView(
                    BANI_CENTER,
                    BANI_FALLBACK_ZOOM,
                    {
                        animate:
                            true
                    }
                );

            }

            return;

        }


        const bounds =
            L.latLngBounds(
                visibleMarkers.map(
                    function (record) {

                        return [
                            record.latitude,
                            record.longitude
                        ];

                    }
                )
            );


        if (
            bounds.isValid()
        ) {

            map.fitBounds(
                bounds,
                {
                    padding:
                        [
                            50,
                            50
                        ],

                    maxZoom:
                        17,

                    animate:
                        true
                }
            );

        }

    }


    /* =========================================================
       SHOW ENTIRE BANI
    ========================================================== */

    function resetToBani() {

        if (
            baniBounds
            &&
            baniBounds.isValid()
        ) {

            map.fitBounds(
                baniBounds,
                {
                    padding:
                        [
                            40,
                            40
                        ],

                    maxZoom:
                        14,

                    animate:
                        true
                }
            );

            return;

        }


        map.setView(
            BANI_CENTER,
            BANI_FALLBACK_ZOOM,
            {
                animate:
                    true
            }
        );

    }


    /* =========================================================
       BUTTON EVENTS
    ========================================================== */

    const resetMapButton =
        document.getElementById(
            'resetMapButton'
        );


    if (
        resetMapButton
    ) {

        resetMapButton.addEventListener(
            'click',
            resetToBani
        );

    }


    const zoomBaniButton =
        document.getElementById(
            'zoomBaniButton'
        );


    if (
        zoomBaniButton
    ) {

        zoomBaniButton.addEventListener(
            'click',
            resetToBani
        );

    }


    const fitHouseholdsButton =
        document.getElementById(
            'fitHouseholdsButton'
        );


    if (
        fitHouseholdsButton
    ) {

        fitHouseholdsButton.addEventListener(
            'click',
            fitHouseholds
        );

    }


    /* =========================================================
       SEARCH
    ========================================================== */

    const searchInput =
        document.getElementById(
            'householdSearch'
        );


    const filterSelect =
        document.getElementById(
            'mapFilter'
        );


    function matchesFilter(
        household,
        filter
    ) {

        const damage =
            String(
                getDamageLevel(
                    household
                )
                ??
                ''
            )
            .toLowerCase();


        const voucherStatus =
            String(
                getVoucherStatus(
                    household
                )
                ??
                ''
            )
            .toLowerCase();


        switch (
            filter
        ) {

            case 'mapped':

                return (
                    getLatitude(
                        household
                    ) !== null
                    &&
                    getLongitude(
                        household
                    ) !== null
                );


            case 'affected':

                return (
                    getHasAssessment(
                        household
                    )
                    &&
                    damage !== ''
                    &&
                    damage !== 'none'
                );


            case 'eligible':

                return getVoucherEligible(
                    household
                );


            case 'voucher-issued':

                return (
                    voucherStatus === 'issued'
                );


            case 'severe':

                return (
                    damage === 'severe'
                    ||
                    damage === 'total'
                );


            case 'all':

            default:

                return true;

        }

    }


    function applyFilters() {

        const searchTerm =
            searchInput
            &&
            searchInput.value
                ? searchInput.value
                    .trim()
                    .toLowerCase()
                : '';


        const filter =
            filterSelect
                ? filterSelect.value
                : 'all';


        let visibleCount = 0;


        markerRecords.forEach(
            function (record) {

                const searchMatch =
                    searchTerm === ''
                    ||
                    record.searchText.includes(
                        searchTerm
                    );


                const filterMatch =
                    matchesFilter(
                        record.household,
                        filter
                    );


                const visible =
                    searchMatch
                    &&
                    filterMatch;


                if (
                    visible
                ) {

                    if (
                        !map.hasLayer(
                            record.marker
                        )
                    ) {

                        record.marker.addTo(
                            householdLayer
                        );

                    }


                    visibleCount++;

                }
                else {

                    if (
                        map.hasLayer(
                            record.marker
                        )
                    ) {

                        householdLayer.removeLayer(
                            record.marker
                        );

                    }

                }

            }
        );


        const badge =
            document.getElementById(
                'visibleHouseholdBadge'
            );


        if (
            badge
        ) {

            badge.textContent =
                visibleCount
                +
                ' visible';

        }


        updateHouseholdTable(
            searchTerm,
            filter
        );

    }


    if (
        searchInput
    ) {

        searchInput.addEventListener(
            'input',
            applyFilters
        );

    }


    if (
        filterSelect
    ) {

        filterSelect.addEventListener(
            'change',
            applyFilters
        );

    }


    /* =========================================================
       HOUSEHOLD TABLE
    ========================================================== */

    function updateHouseholdTable(
        searchTerm,
        filter
    ) {

        const tableBody =
            document.getElementById(
                'householdTableBody'
            );


        if (
            !tableBody
        ) {

            return;

        }


        const rows = [];


        households.forEach(
            function (household) {

                const householdNumber =
                    getHouseholdNumber(
                        household
                    );


                const headName =
                    getHouseholdHead(
                        household
                    );


                const address =
                    getAddress(
                        household
                    );


                const purok =
                    getPurok(
                        household
                    );


                const latitude =
                    getLatitude(
                        household
                    );


                const longitude =
                    getLongitude(
                        household
                    );


                const damage =
                    getDamageLevel(
                        household
                    )
                    ??
                    'None';


                const disaster =
                    getDisasterName(
                        household
                    );


                const voucherStatus =
                    getVoucherStatus(
                        household
                    );


                const voucherEligible =
                    getVoucherEligible(
                        household
                    );


                const searchText =
                    [
                        householdNumber,
                        headName,
                        address,
                        purok,
                        disaster,
                        getVoucherCode(
                            household
                        )
                    ]
                    .join(' ')
                    .toLowerCase();


                const searchMatch =
                    searchTerm === ''
                    ||
                    searchText.includes(
                        searchTerm
                    );


                const filterMatch =
                    matchesFilter(
                        household,
                        filter
                    );


                if (
                    !searchMatch
                    ||
                    !filterMatch
                ) {

                    return;

                }


                const mapped =
                    latitude !== null
                    &&
                    longitude !== null;


                let coordinateHtml =
                    '<span class="text-muted">Not mapped</span>';


                if (
                    mapped
                ) {

                    coordinateHtml =
                        `
                        <span class="coordinate-text">

                            ${latitude.toFixed(6)},
                            ${longitude.toFixed(6)}

                        </span>
                        `;

                }


                let disasterHtml =
                    '<span class="text-muted">None</span>';


                if (
                    getHasAssessment(
                        household
                    )
                ) {

                    disasterHtml =
                        `
                        <span class="badge bg-warning text-dark">

                            ${escapeHtml(damage)}

                        </span>
                        `;

                }


                let voucherHtml =
                    '<span class="text-muted">None</span>';


                if (
                    voucherEligible
                ) {

                    voucherHtml =
                        `
                        <span class="badge bg-success">

                            Eligible

                        </span>
                        `;

                }


                if (
                    voucherStatus
                ) {

                    voucherHtml =
                        `
                        <span class="badge bg-primary">

                            ${escapeHtml(voucherStatus)}

                        </span>
                        `;

                }


                const id =
                    getHouseholdId(
                        household
                    );


                const markerRecord =
                    markerRecords.find(
                        function (record) {

                            return String(
                                getHouseholdId(
                                    record.household
                                )
                            )
                            ===
                            String(id);

                        }
                    );


                const assessmentUrl =
                    getAssessmentUrl(
                        household
                    );


                const voucherUrl =
                    getVoucherUrl(
                        household
                    );


                let actionHtml = '';


                if (
                    mapped
                    &&
                    markerRecord
                ) {

                    actionHtml +=
                        `
                        <button
                            type="button"
                            class="btn btn-sm btn-outline-primary table-map-button"
                            data-household-id="${escapeHtml(id)}"
                            title="Show on Map"
                        >

                            <i class="bi bi-geo-alt"></i>

                        </button>
                        `;

                }


                if (
                    assessmentUrl
                ) {

                    actionHtml +=
                        `
                        <a
                            href="${escapeHtml(assessmentUrl)}"
                            class="btn btn-sm btn-outline-primary"
                            title="View Assessment"
                        >

                            <i class="bi bi-clipboard2-pulse"></i>

                        </a>
                        `;

                }


                if (
                    voucherUrl
                ) {

                    actionHtml +=
                        `
                        <a
                            href="${escapeHtml(voucherUrl)}"
                            class="btn btn-sm btn-outline-success"
                            title="View Voucher"
                        >

                            <i class="bi bi-ticket-perforated"></i>

                        </a>
                        `;

                }


                rows.push(
                    `
                    <tr>

                        <td class="px-4">

                            <strong>
                                ${escapeHtml(householdNumber)}
                            </strong>

                        </td>


                        <td>
                            ${escapeHtml(headName)}
                        </td>


                        <td>
                            ${escapeHtml(address)}
                        </td>


                        <td>
                            ${escapeHtml(purok)}
                        </td>


                        <td>
                            ${coordinateHtml}
                        </td>


                        <td>
                            ${disasterHtml}
                        </td>


                        <td>
                            ${voucherHtml}
                        </td>


                        <td class="text-end px-4">

                            <div class="d-flex justify-content-end gap-1">

                                ${actionHtml}

                            </div>

                        </td>

                    </tr>
                    `
                );

            }
        );


        if (
            rows.length === 0
        ) {

            tableBody.innerHTML =
                `
                <tr>

                    <td
                        colspan="8"
                        class="text-center text-muted py-5"
                    >

                        <i
                            class="bi bi-search"
                            style="font-size: 2rem;"
                        ></i>

                        <div class="mt-2">

                            No matching household records.

                        </div>

                    </td>

                </tr>
                `;

            return;

        }


        tableBody.innerHTML =
            rows.join('');


        /* =====================================================
           TABLE MAP BUTTONS
        ====================================================== */

        tableBody
            .querySelectorAll(
                '.table-map-button'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            const id =
                                button.dataset
                                    .householdId;


                            const record =
                                markerRecords.find(
                                    function (item) {

                                        return String(
                                            getHouseholdId(
                                                item.household
                                            )
                                        )
                                        ===
                                        String(id);

                                    }
                                );


                            if (
                                !record
                            ) {

                                return;

                            }


                            map.setView(
                                [
                                    record.latitude,
                                    record.longitude
                                ],
                                18,
                                {
                                    animate:
                                        true
                                }
                            );


                            record.marker.openPopup();

                        }
                    );

                }
            );

    }


    /* =========================================================
       INITIAL TABLE
    ========================================================== */

    applyFilters();


    /* =========================================================
       MAP STATUS
    ========================================================== */

    const mapStatus =
        document.getElementById(
            'mapStatus'
        );


    if (
        mapStatus
    ) {

        if (
            mappedCount === 0
        ) {

            mapStatus.innerHTML =
                `
                <i class="bi bi-info-circle me-1"></i>

                No valid household coordinates found.

                `;

        }
        else {

            mapStatus.innerHTML =
                `
                <i class="bi bi-check-circle me-1 text-success"></i>

                ${mappedCount}
                household location(s) mapped.

                `;

        }

    }


    /* =========================================================
       MAP SIZE FIX
    ========================================================== */

    function refreshMap() {

        map.invalidateSize(
            true
        );

    }


    setTimeout(
        function () {

            refreshMap();

        },
        100
    );


    setTimeout(
        function () {

            refreshMap();

        },
        500
    );


    setTimeout(
        function () {

            refreshMap();

        },
        1000
    );


    window.addEventListener(
        'resize',
        function () {

            setTimeout(
                refreshMap,
                150
            );

        }
    );


    /* =========================================================
       LOAD REAL BANI MUNICIPAL BOUNDARY
    ========================================================== */

    loadBaniBoundary();


});

</script>

@endpush

@endsection
