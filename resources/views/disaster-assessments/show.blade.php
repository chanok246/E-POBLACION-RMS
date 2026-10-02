@extends('layouts.app')

@section('content')
<div class="container">

    {{-- =========================================================
         HEADER
         ========================================================= --}}
    <div class="d-flex justify-content-between align-items-center mb-3">

        <div>
            <h2 class="mb-1">
                Disaster Assessment Details
            </h2>

            <small class="text-muted">
                House-to-house post-disaster assessment record
            </small>
        </div>

        <div class="d-flex gap-2">

            @if($disasterAssessment->verification_status !== 'Archived')
                <a
                    href="{{ route('disaster-assessments.edit', $disasterAssessment->id) }}"
                    class="btn btn-warning"
                >
                    Edit Assessment
                </a>
            @endif

            <a
                href="{{ route('disaster-assessments.index') }}"
                class="btn btn-secondary"
            >
                Back
            </a>

        </div>

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
         VERIFICATION ACTION PANEL
         ========================================================= --}}
    @php
        $verificationStatus = strtolower(
            (string) $disasterAssessment->verification_status
        );

        $isPending = $verificationStatus === 'pending';
        $isVerified = $verificationStatus === 'verified';
        $isRejected = $verificationStatus === 'rejected';
        $isArchived = $verificationStatus === 'archived';

        $voucher = $disasterAssessment->reliefVoucher;
        $hasVoucher = $voucher !== null;
        $voucherClaimed = $voucher && $voucher->isClaimed();
    @endphp

    <div class="card shadow-sm mb-4">

        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">
                Assessment Verification
            </h5>
        </div>

        <div class="card-body">

            {{-- Pending --}}
            @if($isPending)

                <div class="alert alert-warning">

                    <h6 class="alert-heading fw-bold">
                        Assessment Pending Verification
                    </h6>

                    <p class="mb-0">
                        Review the field assessment details below before
                        approving or rejecting this assessment.
                    </p>

                </div>

                <div class="d-flex flex-wrap gap-2">

                    {{-- VERIFY --}}
                    <form
                        method="POST"
                        action="{{ route('disaster-assessments.verify', $disasterAssessment->id) }}"
                        onsubmit="return confirm('Verify this disaster assessment? The system will determine relief voucher eligibility based on the assessment.');"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            ✓ Verify Assessment
                        </button>
                    </form>


                    {{-- REJECT --}}
                    <form
                        method="POST"
                        action="{{ route('disaster-assessments.reject', $disasterAssessment->id) }}"
                        onsubmit="return confirm('Reject this disaster assessment?');"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-danger"
                        >
                            ✕ Reject Assessment
                        </button>
                    </form>

                </div>

            {{-- Verified --}}
            @elseif($isVerified)

                <div class="alert alert-success">

                    <h6 class="alert-heading fw-bold">
                        Assessment Verified
                    </h6>

                    <p class="mb-0">
                        This assessment has already been verified.
                    </p>

                </div>

                @if($disasterAssessment->voucher_eligible)

                    <div class="alert alert-primary">

                        <strong>
                            ✓ This household is eligible for relief assistance.
                        </strong>

                        <div class="mt-1">
                            A relief voucher may now be generated.
                        </div>

                    </div>

                @else

                    <div class="alert alert-secondary mb-0">

                        <strong>
                            This household is not eligible for a relief voucher.
                        </strong>

                        <div class="mt-1">
                            The assessment was verified, but the current
                            eligibility criteria were not satisfied.
                        </div>

                    </div>

                @endif

            {{-- Rejected --}}
            @elseif($isRejected)

                <div class="alert alert-danger">

                    <h6 class="alert-heading fw-bold">
                        Assessment Rejected
                    </h6>

                    <p class="mb-0">
                        This assessment was rejected during verification.
                    </p>

                </div>

            {{-- Archived --}}
            @elseif($isArchived)

                <div class="alert alert-secondary">

                    <h6 class="alert-heading fw-bold">
                        Assessment Archived
                    </h6>

                    <p class="mb-0">
                        This assessment is currently archived.
                    </p>

                </div>

            @else

                <div class="alert alert-secondary mb-0">

                    Current verification status:

                    <strong>
                        {{ $disasterAssessment->verification_status ?? 'N/A' }}
                    </strong>

                </div>

            @endif


            {{-- =====================================================
                 ARCHIVE / RESTORE ACTIONS
                 ===================================================== --}}

            <hr>

            <div class="d-flex flex-wrap gap-2">

                @if(!$isArchived && !$voucherClaimed)

                    <form
                        method="POST"
                        action="{{ route('disaster-assessments.archive', $disasterAssessment->id) }}"
                        onsubmit="return confirm('Archive this assessment?');"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-outline-secondary"
                        >
                            Archive Assessment
                        </button>
                    </form>

                @endif


                @if($isArchived)

                    <form
                        method="POST"
                        action="{{ route('disaster-assessments.restore', $disasterAssessment->id) }}"
                        onsubmit="return confirm('Restore this assessment and return it to pending verification?');"
                    >
                        @csrf

                        <button
                            type="submit"
                            class="btn btn-outline-primary"
                        >
                            Restore Assessment
                        </button>
                    </form>

                @endif

            </div>

        </div>

    </div>


    {{-- =========================================================
         HOUSEHOLD INFORMATION
         ========================================================= --}}
    <div class="card shadow-sm mb-4">

        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                Household Information
            </h5>
        </div>

        <div class="card-body">

            @php
                $household = $disasterAssessment->household;
                $head = $household?->householdHead;

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
            @endphp

            <div class="row">

                <div class="col-md-6 mb-3">
                    <strong>Household Head:</strong>

                    <div>
                        {{ $headName }}
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <strong>Purok:</strong>

                    <div>
                        {{ $household->purok ?? 'N/A' }}
                    </div>
                </div>

                <div class="col-md-3 mb-3">
                    <strong>Household ID:</strong>

                    <div>
                        {{ $household->id ?? 'N/A' }}
                    </div>
                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         DISASTER EVENT
         ========================================================= --}}
    <div class="card shadow-sm mb-4">

        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">
                Disaster Event
            </h5>
        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-6 mb-3">

                    <strong>Disaster Name:</strong>

                    <div>
                        {{ $disasterAssessment->disasterEvent->name ?? 'N/A' }}
                    </div>

                </div>

                <div class="col-md-3 mb-3">

                    <strong>Event Code:</strong>

                    <div>
                        {{ $disasterAssessment->disasterEvent->event_code ?? 'N/A' }}
                    </div>

                </div>

                <div class="col-md-3 mb-3">

                    <strong>Event Type:</strong>

                    <div>
                        {{ $disasterAssessment->disasterEvent->type ?? 'N/A' }}
                    </div>

                </div>

                <div class="col-md-6 mb-3">

                    <strong>Disaster Start:</strong>

                    <div>
                        @if($disasterAssessment->disasterEvent?->start_date)

                            {{ \Carbon\Carbon::parse(
                                $disasterAssessment->disasterEvent->start_date
                            )->format('M d, Y') }}

                        @else
                            N/A
                        @endif
                    </div>

                </div>

                <div class="col-md-6 mb-3">

                    <strong>Disaster End:</strong>

                    <div>
                        @if($disasterAssessment->disasterEvent?->end_date)

                            {{ \Carbon\Carbon::parse(
                                $disasterAssessment->disasterEvent->end_date
                            )->format('M d, Y') }}

                        @else
                            N/A
                        @endif
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         FIELD ASSESSMENT
         ========================================================= --}}
    <div class="card shadow-sm mb-4">

        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">
                Field Assessment
            </h5>
        </div>

        <div class="card-body">

            @php
                $damageLabel = match($disasterAssessment->damage_level) {
                    'none' => 'No Damage',
                    'minor' => 'Minor',
                    'moderate' => 'Moderate',
                    'severe' => 'Severe',
                    'destroyed' => 'Destroyed',
                    default => ucfirst(
                        str_replace(
                            '_',
                            ' ',
                            $disasterAssessment->damage_level ?? 'N/A'
                        )
                    ),
                };

                $livabilityLabel = match($disasterAssessment->house_livability) {
                    'livable' => 'Livable',
                    'partially_livable' => 'Partially Livable',
                    'unlivable' => 'Unlivable',
                    default => ucfirst(
                        str_replace(
                            '_',
                            ' ',
                            $disasterAssessment->house_livability ?? 'N/A'
                        )
                    ),
                };
            @endphp

            <div class="row">

                <div class="col-md-4 mb-3">

                    <strong>Assessment Date:</strong>

                    <div>
                        @if($disasterAssessment->assessment_date)
                            {{ $disasterAssessment->assessment_date->format('M d, Y') }}
                        @else
                            N/A
                        @endif
                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Damage Level:</strong>

                    <div class="mt-1">

                        @if($disasterAssessment->damage_level === 'destroyed')

                            <span class="badge bg-danger">
                                {{ $damageLabel }}
                            </span>

                        @elseif(
                            $disasterAssessment->damage_level === 'severe' ||
                            $disasterAssessment->damage_level === 'moderate'
                        )

                            <span class="badge bg-warning text-dark">
                                {{ $damageLabel }}
                            </span>

                        @else

                            <span class="badge bg-secondary">
                                {{ $damageLabel }}
                            </span>

                        @endif

                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>House Livability:</strong>

                    <div class="mt-1">

                        @if($disasterAssessment->house_livability === 'unlivable')

                            <span class="badge bg-danger">
                                {{ $livabilityLabel }}
                            </span>

                        @elseif(
                            $disasterAssessment->house_livability === 'partially_livable'
                        )

                            <span class="badge bg-warning text-dark">
                                {{ $livabilityLabel }}
                            </span>

                        @else

                            <span class="badge bg-success">
                                {{ $livabilityLabel }}
                            </span>

                        @endif

                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Estimated Property Loss:</strong>

                    <div>
                        ₱{{ number_format(
                            (float) $disasterAssessment->estimated_property_loss,
                            2
                        ) }}
                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Household Affected:</strong>

                    <div>

                        @if($disasterAssessment->household_affected)

                            <span class="badge bg-danger">
                                Yes
                            </span>

                        @else

                            <span class="badge bg-secondary">
                                No
                            </span>

                        @endif

                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Affected Members:</strong>

                    <div>
                        {{ $disasterAssessment->affected_members ?? 0 }}
                    </div>

                </div>


                <div class="col-md-4 mb-3">

                    <strong>Evacuated:</strong>

                    <div>

                        @if($disasterAssessment->evacuated)

                            <span class="badge bg-warning text-dark">
                                Yes
                            </span>

                        @else

                            <span class="badge bg-secondary">
                                No
                            </span>

                        @endif

                    </div>

                </div>


                <div class="col-md-8 mb-3">

                    <strong>Evacuation Center:</strong>

                    <div>
                        {{ $disasterAssessment->evacuation_center ?? 'N/A' }}
                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         INTERVIEW / FIELD NOTES
         ========================================================= --}}
    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <h5 class="mb-0">
                Interview and Field Notes
            </h5>
        </div>

        <div class="card-body">

            <div class="mb-3">

                <strong>Livelihood Impact:</strong>

                <div class="border rounded p-3 mt-1 bg-light">

                    @if($disasterAssessment->livelihood_impact)

                        {!! nl2br(e(
                            $disasterAssessment->livelihood_impact
                        )) !!}

                    @else

                        <span class="text-muted">
                            None reported.
                        </span>

                    @endif

                </div>

            </div>


            <div class="mb-3">

                <strong>Interview Notes:</strong>

                <div class="border rounded p-3 mt-1 bg-light">

                    @if($disasterAssessment->interview_notes)

                        {!! nl2br(e(
                            $disasterAssessment->interview_notes
                        )) !!}

                    @else

                        <span class="text-muted">
                            No interview notes recorded.
                        </span>

                    @endif

                </div>

            </div>


            <div>

                <strong>Remarks:</strong>

                <div class="border rounded p-3 mt-1 bg-light">

                    @if($disasterAssessment->remarks)

                        {!! nl2br(e(
                            $disasterAssessment->remarks
                        )) !!}

                    @else

                        <span class="text-muted">
                            No remarks recorded.
                        </span>

                    @endif

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         ASSESSOR / VERIFICATION
         ========================================================= --}}
    <div class="card shadow-sm mb-4">

        <div class="card-header">
            <h5 class="mb-0">
                Verification and Eligibility
            </h5>
        </div>

        <div class="card-body">

            <div class="row">

                {{-- Assessor --}}
                <div class="col-md-4 mb-3">

                    <strong>Assessed By:</strong>

                    <div>

                        @if($disasterAssessment->assessor)
                            {{ $disasterAssessment->assessor->name }}
                        @else
                            N/A
                        @endif

                    </div>

                </div>


                {{-- Verification Status --}}
                <div class="col-md-4 mb-3">

                    <strong>Verification Status:</strong>

                    <div class="mt-1">

                        @if($isVerified)

                            <span class="badge bg-success">
                                Verified
                            </span>

                        @elseif($isRejected)

                            <span class="badge bg-danger">
                                Rejected
                            </span>

                        @elseif($isArchived)

                            <span class="badge bg-secondary">
                                Archived
                            </span>

                        @else

                            <span class="badge bg-warning text-dark">
                                Pending Verification
                            </span>

                        @endif

                    </div>

                </div>


                {{-- Verified By --}}
                <div class="col-md-4 mb-3">

                    <strong>Verified By:</strong>

                    <div>

                        @if($disasterAssessment->verifier)
                            {{ $disasterAssessment->verifier->name }}
                        @else
                            Not yet verified
                        @endif

                    </div>

                </div>


                {{-- Verified At --}}
                <div class="col-md-4 mb-3">

                    <strong>Verified At:</strong>

                    <div>

                        @if($disasterAssessment->verified_at)
                            {{ $disasterAssessment->verified_at->format('M d, Y h:i A') }}
                        @else
                            Not yet verified
                        @endif

                    </div>

                </div>


                {{-- Voucher Eligibility --}}
                <div class="col-md-4 mb-3">

                    <strong>Relief Voucher Eligibility:</strong>

                    <div class="mt-1">

                        @if($disasterAssessment->voucher_eligible)

                            <span class="badge bg-success">
                                Eligible
                            </span>

                        @elseif($isVerified)

                            <span class="badge bg-danger">
                                Not Eligible
                            </span>

                        @else

                            <span class="badge bg-secondary">
                                Pending Verification
                            </span>

                        @endif

                    </div>

                </div>


                {{-- Eligibility Reason --}}
                <div class="col-md-12 mb-3">

                    <strong>Eligibility Basis / Reason:</strong>

                    <div class="border rounded p-3 mt-1 bg-light">

                        @if($disasterAssessment->eligibility_reason)

                            {!! nl2br(e(
                                $disasterAssessment->eligibility_reason
                            )) !!}

                        @else

                            <span class="text-muted">
                                No eligibility decision has been recorded yet.
                            </span>

                        @endif

                    </div>

                </div>

            </div>

        </div>

    </div>


    {{-- =========================================================
         RELIEF VOUCHER
         ========================================================= --}}
    <div class="card shadow-sm mb-4">

        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                Relief Voucher
            </h5>
        </div>

        <div class="card-body">

            @if($hasVoucher)

                <div class="alert alert-success">

                    <strong>
                        A relief voucher has already been generated
                        for this assessment.
                    </strong>

                </div>

                <div class="row">

                    <div class="col-md-4 mb-3">

                        <strong>Voucher Code:</strong>

                        <div>
                            {{ $voucher->voucher_code }}
                        </div>

                    </div>

                    <div class="col-md-4 mb-3">

                        <strong>Purok:</strong>

                        <div>
                            {{ $voucher->purok_name ?? 'N/A' }}
                        </div>

                    </div>

                    <div class="col-md-4 mb-3">

                        <strong>Assistance Type:</strong>

                        <div>
                            {{ $voucher->assistance_type ?? 'N/A' }}
                        </div>

                    </div>

                    <div class="col-md-4 mb-3">

                        <strong>Distribution Schedule:</strong>

                        <div>

                            @if($voucher->distribution_schedule)

                                {{ \Carbon\Carbon::parse(
                                    $voucher->distribution_schedule
                                )->format('M d, Y h:i A') }}

                            @else

                                N/A

                            @endif

                        </div>

                    </div>

                    <div class="col-md-4 mb-3">

                        <strong>Voucher Status:</strong>

                        <div class="mt-1">

                            @if($voucher->isClaimed())

                                <span class="badge bg-success">
                                    CLAIMED
                                </span>

                            @elseif($voucher->isIssued())

                                <span class="badge bg-primary">
                                    ISSUED
                                </span>

                            @else

                                <span class="badge bg-secondary">
                                    {{ strtoupper($voucher->status ?? 'N/A') }}
                                </span>

                            @endif

                        </div>

                    </div>

                    <div class="col-md-4 mb-3">

                        <strong>Requirements:</strong>

                        <div class="mt-1">

                            @if($voucher->requirements_verified)

                                <span class="badge bg-success">
                                    Verified
                                </span>

                            @else

                                <span class="badge bg-warning text-dark">
                                    Not Yet Verified
                                </span>

                            @endif

                        </div>

                    </div>

                </div>


                <div class="d-flex flex-wrap gap-2 mt-2">

                    <a
                        href="{{ route('relief-vouchers.show', $voucher->id) }}"
                        class="btn btn-primary"
                    >
                        View Voucher
                    </a>

                    <a
                        href="{{ route('relief-vouchers.show', $voucher->id) }}"
                        class="btn btn-outline-secondary"
                    >
                        Print Voucher
                    </a>

                </div>


                @if($voucherClaimed)

                    <div class="alert alert-danger mt-3 mb-0">

                        <strong>
                            ALREADY CLAIMED
                        </strong>

                        <div>
                            This voucher has already been used for
                            assistance distribution.

                            @if($voucher->claimed_at)
                                Claim recorded on
                                {{ $voucher->claimed_at->format('M d, Y h:i A') }}.
                            @endif
                        </div>

                    </div>

                @endif

            @else

                <div class="alert alert-secondary">

                    <strong>
                        No relief voucher has been generated for this assessment.
                    </strong>

                    @if($isVerified && $disasterAssessment->voucher_eligible)

                        <div class="mt-2">
                            This household is eligible for relief assistance.
                        </div>

                    @elseif(!$isVerified)

                        <div class="mt-2">
                            Voucher generation is unavailable until the
                            assessment has been verified.
                        </div>

                    @else

                        <div class="mt-2">
                            This household is currently not eligible for
                            a relief voucher.
                        </div>

                    @endif

                </div>


                {{-- GENERATE VOUCHER --}}
                @if(
                    $isVerified &&
                    $disasterAssessment->voucher_eligible
                )

                    <a
                        href="{{ route(
                            'relief-vouchers.create',
                            ['disasterAssessment' => $disasterAssessment->id]
                        ) }}"
                        class="btn btn-success"
                    >
                        Generate Relief Voucher
                    </a>

                @endif

            @endif

        </div>

    </div>


    {{-- =========================================================
         FOOTER ACTIONS
         ========================================================= --}}
    <div class="d-flex justify-content-between mb-4">

        <a
            href="{{ route('disaster-assessments.index') }}"
            class="btn btn-secondary"
        >
            ← Back to Assessments
        </a>

        @if(!$isArchived)

            <a
                href="{{ route(
                    'disaster-assessments.edit',
                    $disasterAssessment->id
                ) }}"
                class="btn btn-warning"
            >
                Edit Assessment
            </a>

        @endif

    </div>

</div>
@endsection
