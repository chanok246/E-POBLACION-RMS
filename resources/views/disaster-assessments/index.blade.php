@extends('layouts.app')

@section('content')
<div class="container">

    {{-- =========================================================
         HEADER
         ========================================================= --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h2 class="mb-1">
                House-to-House Disaster Assessments
            </h2>

            <small class="text-muted">
                Post-disaster household assessment and verification records
            </small>
        </div>

        <a
            href="{{ route('disaster-assessments.create') }}"
            class="btn btn-primary"
        >
            + Encode Field Assessment
        </a>

    </div>


    {{-- =========================================================
         FLASH MESSAGES
         ========================================================= --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>
        </div>
    @endif


    {{-- =========================================================
         FILTERS
         ========================================================= --}}
    <div class="card shadow-sm mb-4">

        <div class="card-body">

            <form
                action="{{ route('disaster-assessments.index') }}"
                method="GET"
            >

                <div class="row">

                    {{-- Disaster Search --}}
                    <div class="col-md-4 mb-3">

                        <label
                            for="disaster"
                            class="form-label"
                        >
                            Disaster Event
                        </label>

                        <input
                            type="text"
                            name="disaster"
                            id="disaster"
                            class="form-control"
                            value="{{ request('disaster') }}"
                            placeholder="Search disaster name or event code"
                        >

                    </div>


                    {{-- Verification Status --}}
                    <div class="col-md-3 mb-3">

                        <label
                            for="verification_status"
                            class="form-label"
                        >
                            Verification Status
                        </label>

                        <select
                            name="verification_status"
                            id="verification_status"
                            class="form-select"
                        >

                            <option value="">
                                All Statuses
                            </option>

                            <option
                                value="Pending"
                                {{ request('verification_status') === 'Pending' ? 'selected' : '' }}
                            >
                                Pending
                            </option>

                            <option
                                value="Verified"
                                {{ request('verification_status') === 'Verified' ? 'selected' : '' }}
                            >
                                Verified
                            </option>

                            <option
                                value="Rejected"
                                {{ request('verification_status') === 'Rejected' ? 'selected' : '' }}
                            >
                                Rejected
                            </option>

                            <option
                                value="Archived"
                                {{ request('verification_status') === 'Archived' ? 'selected' : '' }}
                            >
                                Archived
                            </option>

                        </select>

                    </div>


                    {{-- Damage Level --}}
                    <div class="col-md-3 mb-3">

                        <label
                            for="damage_level"
                            class="form-label"
                        >
                            Damage Level
                        </label>

                        <select
                            name="damage_level"
                            id="damage_level"
                            class="form-select"
                        >

                            <option value="">
                                All Damage Levels
                            </option>

                            <option
                                value="none"
                                {{ request('damage_level') === 'none' ? 'selected' : '' }}
                            >
                                No Damage
                            </option>

                            <option
                                value="minor"
                                {{ request('damage_level') === 'minor' ? 'selected' : '' }}
                            >
                                Minor
                            </option>

                            <option
                                value="moderate"
                                {{ request('damage_level') === 'moderate' ? 'selected' : '' }}
                            >
                                Moderate
                            </option>

                            <option
                                value="severe"
                                {{ request('damage_level') === 'severe' ? 'selected' : '' }}
                            >
                                Severe
                            </option>

                            <option
                                value="destroyed"
                                {{ request('damage_level') === 'destroyed' ? 'selected' : '' }}
                            >
                                Destroyed
                            </option>

                        </select>

                    </div>


                    {{-- Filter Buttons --}}
                    <div class="col-md-2 mb-3 d-flex align-items-end">

                        <div class="d-flex gap-2 w-100">

                            <button
                                type="submit"
                                class="btn btn-primary w-100"
                            >
                                Filter
                            </button>

                            <a
                                href="{{ route('disaster-assessments.index') }}"
                                class="btn btn-outline-secondary"
                            >
                                Clear
                            </a>

                        </div>

                    </div>

                </div>

            </form>

        </div>

    </div>


    {{-- =========================================================
         WORKFLOW INFORMATION
         ========================================================= --}}
    <div class="alert alert-info shadow-sm">

        <strong>Assessment Workflow:</strong>

        <span class="ms-1">
            Field Assessment
            →
            Verification
            →
            Eligibility
            →
            Relief Voucher
            →
            Issued
            →
            Claimed
        </span>

    </div>


    {{-- =========================================================
         ASSESSMENT TABLE
         ========================================================= --}}
    <div class="card shadow-sm">

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>
                                Household Head
                            </th>

                            <th>
                                Purok
                            </th>

                            <th>
                                Disaster
                            </th>

                            <th>
                                Assessment Date
                            </th>

                            <th>
                                Damage
                            </th>

                            <th>
                                Livability
                            </th>

                            <th>
                                Verification
                            </th>

                            <th>
                                Voucher
                            </th>

                            <th>
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @forelse($assessments as $item)

                            @php
                                $household = $item->household;
                                $head = $household?->householdHead;

                                /*
                                |--------------------------------------------------------------------------
                                | Household Head Name
                                |--------------------------------------------------------------------------
                                */
                                $headName = 'N/A';

                                if ($head) {

                                    $headName = trim(
                                        ($head->first_name ?? '') . ' ' .
                                        ($head->middle_name ?? '') . ' ' .
                                        ($head->last_name ?? '') . ' ' .
                                        ($head->suffix ?? '')
                                    );

                                    if ($headName === '') {
                                        $headName = 'N/A';
                                    }
                                }


                                /*
                                |--------------------------------------------------------------------------
                                | Damage Label
                                |--------------------------------------------------------------------------
                                */
                                $damageLabel = match($item->damage_level) {

                                    'none' => 'No Damage',

                                    'minor' => 'Minor',

                                    'moderate' => 'Moderate',

                                    'severe' => 'Severe',

                                    'destroyed' => 'Destroyed',

                                    default => ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $item->damage_level ?? 'N/A'
                                        )
                                    ),

                                };


                                /*
                                |--------------------------------------------------------------------------
                                | Livability Label
                                |--------------------------------------------------------------------------
                                */
                                $livabilityLabel = match($item->house_livability) {

                                    'livable' => 'Livable',

                                    'partially_livable' => 'Partially Livable',

                                    'unlivable' => 'Unlivable',

                                    default => ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $item->house_livability ?? 'N/A'
                                        )
                                    ),

                                };


                                /*
                                |--------------------------------------------------------------------------
                                | Verification Status
                                |--------------------------------------------------------------------------
                                */
                                $verificationStatus = strtolower(
                                    (string) $item->verification_status
                                );


                                /*
                                |--------------------------------------------------------------------------
                                | Related Voucher
                                |--------------------------------------------------------------------------
                                */
                                $voucher = $item->reliefVoucher;

                                $hasVoucher = $voucher !== null;

                                $voucherStatus = $hasVoucher
                                    ? strtolower((string) $voucher->status)
                                    : null;

                            @endphp


                            <tr>

                                {{-- =====================================================
                                     HOUSEHOLD HEAD
                                     ===================================================== --}}
                                <td>

                                    <strong>
                                        {{ $headName }}
                                    </strong>

                                    @if($household)

                                        <br>

                                        <small class="text-muted">
                                            Household ID:
                                            {{ $household->id }}
                                        </small>

                                    @endif

                                </td>


                                {{-- =====================================================
                                     PUROK
                                     ===================================================== --}}
                                <td>

                                    {{ $household?->purok
                                        ? 'Purok ' . $household->purok
                                        : 'N/A'
                                    }}

                                </td>


                                {{-- =====================================================
                                     DISASTER
                                     ===================================================== --}}
                                <td>

                                    @if($item->disasterEvent)

                                        <strong>
                                            {{ $item->disasterEvent->name }}
                                        </strong>

                                        <br>

                                        <small class="text-muted">
                                            {{ $item->disasterEvent->event_code }}
                                        </small>

                                    @else

                                        N/A

                                    @endif

                                </td>


                                {{-- =====================================================
                                     ASSESSMENT DATE
                                     ===================================================== --}}
                                <td>

                                    @if($item->assessment_date)

                                        {{ $item->assessment_date->format('M d, Y') }}

                                    @else

                                        N/A

                                    @endif

                                </td>


                                {{-- =====================================================
                                     DAMAGE
                                     ===================================================== --}}
                                <td>

                                    @if($item->damage_level === 'destroyed')

                                        <span class="badge bg-danger">
                                            {{ $damageLabel }}
                                        </span>

                                    @elseif(
                                        $item->damage_level === 'severe' ||
                                        $item->damage_level === 'moderate'
                                    )

                                        <span class="badge bg-warning text-dark">
                                            {{ $damageLabel }}
                                        </span>

                                    @elseif($item->damage_level === 'minor')

                                        <span class="badge bg-info text-dark">
                                            {{ $damageLabel }}
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            {{ $damageLabel }}
                                        </span>

                                    @endif

                                </td>


                                {{-- =====================================================
                                     LIVABILITY
                                     ===================================================== --}}
                                <td>

                                    @if($item->house_livability === 'unlivable')

                                        <span class="badge bg-danger">
                                            {{ $livabilityLabel }}
                                        </span>

                                    @elseif(
                                        $item->house_livability === 'partially_livable'
                                    )

                                        <span class="badge bg-warning text-dark">
                                            {{ $livabilityLabel }}
                                        </span>

                                    @else

                                        <span class="badge bg-success">
                                            {{ $livabilityLabel }}
                                        </span>

                                    @endif

                                </td>


                                {{-- =====================================================
                                     VERIFICATION
                                     ===================================================== --}}
                                <td>

                                    @if($verificationStatus === 'verified')

                                        <span class="badge bg-success">
                                            Verified
                                        </span>

                                    @elseif($verificationStatus === 'rejected')

                                        <span class="badge bg-danger">
                                            Rejected
                                        </span>

                                    @elseif($verificationStatus === 'archived')

                                        <span class="badge bg-secondary">
                                            Archived
                                        </span>

                                    @else

                                        <span class="badge bg-warning text-dark">
                                            Pending
                                        </span>

                                    @endif

                                </td>


                                {{-- =====================================================
                                     VOUCHER
                                     ===================================================== --}}
                                <td>

                                    @if($hasVoucher)

                                        {{-- Claimed --}}
                                        @if($voucherStatus === 'claimed')

                                            <span class="badge bg-success">
                                                CLAIMED
                                            </span>

                                            @if($voucher->claimed_at)

                                                <br>

                                                <small class="text-muted">
                                                    {{ $voucher->claimed_at->format('M d, Y h:i A') }}
                                                </small>

                                            @endif


                                        {{-- Issued --}}
                                        @elseif($voucherStatus === 'issued')

                                            <span class="badge bg-primary">
                                                ISSUED
                                            </span>


                                        {{-- Legacy Verified --}}
                                        @elseif($voucherStatus === 'verified')

                                            <span class="badge bg-info text-dark">
                                                VERIFIED
                                            </span>


                                        {{-- Other --}}
                                        @else

                                            <span class="badge bg-secondary">
                                                {{ strtoupper($voucher->status ?? 'N/A') }}
                                            </span>

                                        @endif


                                        <br>

                                        <small class="text-muted">
                                            {{ $voucher->voucher_code }}
                                        </small>

                                    @elseif($item->voucher_eligible)

                                        <span class="badge bg-success">
                                            ELIGIBLE
                                        </span>

                                        <br>

                                        <small class="text-muted">
                                            Not Generated
                                        </small>

                                    @elseif($verificationStatus === 'verified')

                                        <span class="badge bg-danger">
                                            NOT ELIGIBLE
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            PENDING
                                        </span>

                                    @endif

                                </td>


                                {{-- =====================================================
                                     ACTIONS
                                     ===================================================== --}}
                                <td>

                                    <div class="d-flex flex-column gap-1">

                                        {{-- View --}}
                                        <a
                                            href="{{ route(
                                                'disaster-assessments.show',
                                                $item->id
                                            ) }}"
                                            class="btn btn-sm btn-info text-white"
                                        >
                                            View
                                        </a>


                                        {{-- Edit --}}
                                        @if($verificationStatus !== 'archived')

                                            <a
                                                href="{{ route(
                                                    'disaster-assessments.edit',
                                                    $item->id
                                                ) }}"
                                                class="btn btn-sm btn-warning"
                                            >
                                                Edit
                                            </a>

                                        @endif


                                        {{-- Generate Voucher --}}
                                        @if(
                                            $verificationStatus === 'verified' &&
                                            $item->voucher_eligible &&
                                            !$hasVoucher
                                        )

                                            <a
                                                href="{{ route(
                                                    'relief-vouchers.create',
                                                    [
                                                        'disasterAssessment' => $item->id
                                                    ]
                                                ) }}"
                                                class="btn btn-sm btn-success"
                                            >
                                                Generate Voucher
                                            </a>

                                        @endif


                                        {{-- View Existing Voucher --}}
                                        @if($hasVoucher)

                                            <a
                                                href="{{ route(
                                                    'relief-vouchers.show',
                                                    $voucher->id
                                                ) }}"
                                                class="btn btn-sm btn-outline-primary"
                                            >
                                                View Voucher
                                            </a>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="9"
                                    class="text-center py-5"
                                >

                                    <div class="text-muted">

                                        <h5>
                                            No disaster assessments found.
                                        </h5>

                                        <p class="mb-0">
                                            Encode a house-to-house field
                                            assessment to begin.
                                        </p>

                                    </div>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- =========================================================
         PAGINATION
         ========================================================= --}}
    @if($assessments->hasPages())

        <div class="mt-3">

            {{ $assessments->withQueryString()->links() }}

        </div>

    @endif

</div>
@endsection
