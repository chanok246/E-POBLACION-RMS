@extends('layouts.app')

@section('title', 'Document Processing | e-Poblacion')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,600;12..96,800&family=Public+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       DOCUMENT PROCESSING
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

        --pending: #b26a00;
        --pending-bg: #fff1d6;

        --processing: #0b7285;
        --processing-bg: #d8f3f8;

        --approved: #2b8a3e;
        --approved-bg: #dcf5e3;

        --completed: #5f3dc4;
        --completed-bg: #e8e1fb;

        --released: #1f4e9c;
        --released-bg: #dbe8fb;

        --disapproved: #c92a2a;
        --disapproved-bg: #fde3e3;

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

        --pending: #f5b942;
        --pending-bg: #3a2c10;

        --processing: #5ccfe0;
        --processing-bg: #0f3038;

        --approved: #6fd48a;
        --approved-bg: #143222;

        --completed: #b7a0f5;
        --completed-bg: #2a2150;

        --released: #8db6f7;
        --released-bg: #182c4d;

        --disapproved: #ff8f8f;
        --disapproved-bg: #401a1d;
    }

    .docs * {
        box-sizing: border-box;
    }

    .docs .display {
        font-family: 'Bricolage Grotesque', 'Public Sans', sans-serif;
        letter-spacing: -.02em;
    }

    .docs a:focus-visible,
    .docs button:focus-visible {
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
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: space-between;
        align-items: flex-end;
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

    .pill {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .35rem .85rem;
        border-radius: 999px;
        font-size: .9rem;
        font-weight: 600;
        color: var(--c);
        background: var(--bg);
    }

    .pill::before {
        content: '';
        width: .55rem;
        height: .55rem;
        border-radius: 50%;
        background: var(--c);
    }

    .st-pending {
        --c: var(--pending);
        --bg: var(--pending-bg);
    }

    .st-processing {
        --c: var(--processing);
        --bg: var(--processing-bg);
    }

    .st-approved {
        --c: var(--approved);
        --bg: var(--approved-bg);
    }

    .st-completed {
        --c: var(--completed);
        --bg: var(--completed-bg);
    }

    .st-released {
        --c: var(--released);
        --bg: var(--released-bg);
    }

    .st-disapproved {
        --c: var(--disapproved);
        --bg: var(--disapproved-bg);
    }

    .note {
        display: flex;
        gap: .75rem;
        align-items: flex-start;
        padding: .9rem 1.1rem;
        border-radius: .9rem;
        margin-bottom: 1rem;
        border: 1px solid var(--c);
        background: var(--bg);
        color: var(--c);
    }

    .note b {
        display: block;
    }

    .note div {
        color: var(--ink);
    }

    .note b,
    .note i {
        color: var(--c);
    }

    .note .x {
        margin-left: auto;
        background: none;
        border: 0;
        color: var(--c);
        font-size: 1.2rem;
        line-height: 1;
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
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .card2-head h2 {
        font-size: 1.05rem;
        font-weight: 800;
        margin: 0;
    }

    .card2-head small {
        color: var(--muted);
    }

    .card2-body {
        padding: 1.25rem;
    }

    /* =========================================================
       SIMPLIFIED PROCESSING JOURNEY
       Detailed verification stages remain in the backend/audit
       record, but the main UI shows only major milestones.
       ========================================================= */
    .journey {
        overflow-x: auto;
        padding: .65rem .25rem 1rem;
        scrollbar-width: thin;
    }

    .journey ol {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        min-width: 650px;
        width: 100%;
    }

    .jstep {
        flex: 1 0 108px;
        text-align: center;
        position: relative;
    }

    .jstep:not(:last-child)::after {
        content: '';
        position: absolute;
        top: 20px;
        left: calc(50% + 23px);
        width: calc(100% - 46px);
        height: 4px;
        border-radius: 4px;
        background: var(--line);
    }

    .jstep.done:not(:last-child)::after {
        background: var(--approved);
    }

    .jdot {
        width: 42px;
        height: 42px;
        margin: 0 auto .55rem;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-weight: 700;
        background: var(--paper);
        border: 3px solid var(--line);
        color: var(--muted);
        position: relative;
        z-index: 1;
    }

    .jstep.done .jdot {
        background: var(--approved);
        border-color: var(--approved);
        color: #fff;
    }

    html[data-theme="dark"] .jstep.done .jdot {
        color: #0f1620;
    }

    .jstep.now .jdot {
        background: var(--brand);
        border-color: var(--brand);
        color: #fff;
        box-shadow:
            0 0 0 5px var(--brand-soft),
            0 0 0 8px var(--sun);
    }

    html[data-theme="dark"] .jstep.now .jdot {
        color: #0f1620;
    }

    .jname {
        font-size: .82rem;
        font-weight: 600;
        color: var(--muted);
        padding: 0 .3rem;
        line-height: 1.25;
    }

    .jstep.done .jname {
        color: var(--approved);
    }

    .jstep.now .jname {
        color: var(--ink);
    }

    .stopped {
        display: flex;
        gap: .7rem;
        align-items: center;
        padding: 1rem;
        border-radius: .9rem;
        background: var(--disapproved-bg);
        color: var(--ink);
    }

    .stopped i {
        color: var(--disapproved);
        font-size: 1.3rem;
    }

    /* ---------- Info ---------- */

    .grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem 1.5rem;
    }

    @media (max-width: 575.98px) {
        .grid {
            grid-template-columns: 1fr;
        }
    }

    .grid .full {
        grid-column: 1 / -1;
    }

    .k {
        font-size: .82rem;
        color: var(--muted);
        margin-bottom: .2rem;
    }

    .v {
        font-weight: 600;
    }

    .v small {
        display: block;
        font-weight: 400;
        color: var(--muted);
        font-size: .85rem;
    }

    .who {
        display: flex;
        gap: .75rem;
        align-items: center;
    }

    .avatar {
        width: 2.6rem;
        height: 2.6rem;
        border-radius: 50%;
        display: grid;
        place-items: center;
        flex: none;
        font-weight: 700;
        background: var(--brand-soft);
        color: var(--brand);
    }

    /* ---------- Audit timeline ---------- */

    .tl {
        list-style: none;
        margin: 0;
        padding: 1.25rem 1.25rem .5rem;
    }

    .tl li {
        position: relative;
        display: flex;
        gap: .9rem;
        padding-bottom: 1.25rem;
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
        background: var(--approved);
        border-color: var(--approved);
        color: #fff;
    }

    html[data-theme="dark"] .tl li.done .dot {
        color: #0f1620;
    }

    .tl .row {
        flex: 1;
        display: flex;
        justify-content: space-between;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .tl b {
        font-weight: 600;
    }

    .tl li:not(.done) b {
        color: var(--muted);
        font-weight: 500;
    }

    .tl small {
        display: block;
        color: var(--muted);
    }

    .tl .when {
        text-align: right;
        color: var(--muted);
        font-size: .88rem;
    }

    @media (max-width: 575.98px) {
        .tl .when {
            text-align: left;
        }
    }

    /* ---------- Action panel ---------- */

    .side {
        position: sticky;
        top: 1rem;
    }

    @media (max-width: 991.98px) {
        .side {
            position: static;
        }
    }

    .side .hint {
        color: var(--muted);
        font-size: .92rem;
        margin: 0 0 1rem;
    }

    .side .stage-now {
        font-size: 1.1rem;
        font-weight: 800;
        margin: 0 0 .35rem;
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
        background: var(--approved);
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
        border-color: var(--brand);
        color: var(--brand);
    }

    .act-line:hover {
        background: var(--brand-soft);
        color: var(--brand);
    }

    .act-danger {
        background: transparent;
        border-color: var(--disapproved);
        color: var(--disapproved);
    }

    .act-danger:hover {
        background: var(--disapproved-bg);
    }

    .stack {
        display: grid;
        gap: .6rem;
    }

    .stack form {
        margin: 0;
    }

    .divider {
        text-align: center;
        color: var(--muted);
        font-size: .8rem;
        margin: .4rem 0;
    }

    .state {
        display: flex;
        gap: .7rem;
        padding: .9rem 1rem;
        border-radius: .9rem;
        font-size: .92rem;
        background: var(--bg);
        color: var(--ink);
    }

    .state i {
        color: var(--c);
        font-size: 1.15rem;
    }

    .state.muted {
        --c: var(--muted);
        --bg: var(--wash);
    }

    @media (prefers-reduced-motion: reduce) {
        .docs * {
            transition: none !important;
            scroll-behavior: auto !important;
        }
    }
</style>
@endpush

@section('content')

@php
    $resident = $document->resident;

    /*
    |--------------------------------------------------------------------------
    | Detailed backend workflow
    |--------------------------------------------------------------------------
    | These stages remain untouched. They are still used for the actual
    | processing logic and audit trail.
    */
    $steps    = \App\Models\DocumentRequest::workflowSteps();
    $stepKeys = array_keys($steps);

    $currentIndex = array_search(
        $document->workflow_stage,
        $stepKeys,
        true
    );

    if ($currentIndex === false) {
        $currentIndex = 0;
    }

    $isDeclined = $document->isThirdPartyDeclined();
    $isReleased = $document->workflow_stage === \App\Models\DocumentRequest::STAGE_RELEASED;

    /*
    |--------------------------------------------------------------------------
    | Simplified UI journey
    |--------------------------------------------------------------------------
    | Several internal verification stages are represented by one major
    | milestone called "Verified".
    */
    $journeySteps = [
        'request' => 'Request Created',
        'verified' => 'Verified',
        'approved' => 'Approved',
        'printed' => 'Printed',
        'signed' => 'Signed & Sealed',
        'released' => 'Released',
    ];

    /*
    |--------------------------------------------------------------------------
    | Determine the simplified journey position
    |--------------------------------------------------------------------------
    */
    $stage = $document->workflow_stage;

    $verifiedStages = [
        \App\Models\DocumentRequest::STAGE_INSPECTION,
        \App\Models\DocumentRequest::STAGE_ID_VERIFIED,
        \App\Models\DocumentRequest::STAGE_ELIGIBLE,
    ];

    $journeyCurrent = match (true) {
        $stage === \App\Models\DocumentRequest::STAGE_REQUESTED
            => 'request',

        in_array($stage, $verifiedStages, true)
            => 'verified',

        $stage === \App\Models\DocumentRequest::STAGE_APPROVED
            => 'approved',

        $stage === \App\Models\DocumentRequest::STAGE_PRINTED
            => 'printed',

        $stage === \App\Models\DocumentRequest::STAGE_SIGNED_SEALED
            => 'signed',

        $stage === \App\Models\DocumentRequest::STAGE_RELEASED
            => 'released',

        default => 'request',
    };

    $journeyOrder = array_keys($journeySteps);
    $journeyIndex = array_search($journeyCurrent, $journeyOrder, true);

    if ($journeyIndex === false) {
        $journeyIndex = 0;
    }

    $statusClass = match ($document->status) {
        'Released'    => 'st-released',
        'Approved'    => 'st-approved',
        'Completed'   => 'st-completed',
        'Disapproved' => 'st-disapproved',
        'Processing'  => 'st-processing',
        default       => 'st-pending',
    };

    $name = $resident?->full_name ?? 'Resident not found';

    $nameParts = array_values(
        array_filter(
            preg_split('/\s+/', trim((string) $name))
        )
    );

    $initials = $resident && $nameParts
        ? strtoupper(
            mb_substr($nameParts[0], 0, 1) .
            (
                count($nameParts) > 1
                    ? mb_substr(end($nameParts), 0, 1)
                    : ''
            )
        )
        : '?';

    /*
    |--------------------------------------------------------------------------
    | Detailed audit record
    |--------------------------------------------------------------------------
    | These remain visible so staff can still see the exact internal checks.
    */
    $auditRows = [
        [
            'label'   => 'Inspection',
            'at'      => $document->inspection_at,
            'officer' => $document->inspectionOfficer
        ],
        [
            'label'   => 'ID verified',
            'at'      => $document->id_verified_at,
            'officer' => $document->idVerifiedOfficer
        ],
        [
            'label'   => 'Marked eligible',
            'at'      => $document->eligible_at,
            'officer' => $document->eligibleOfficer
        ],
        [
            'label'   => 'Approved for release',
            'at'      => $document->approved_at,
            'officer' => $document->approvedOfficer
        ],
        [
            'label'   => 'Printed',
            'at'      => $document->printed_at,
            'officer' => $document->printedOfficer
        ],
        [
            'label'   => 'Physical signature and seal',
            'at'      => $document->signed_sealed_at,
            'officer' => $document->signedSealedOfficer
        ],
        [
            'label'   => 'Released',
            'at'      => $document->released_at,
            'officer' => $document->releasedOfficer
        ],
    ];

    /*
    |--------------------------------------------------------------------------
    | Existing processing actions
    |--------------------------------------------------------------------------
    | UNCHANGED.
    */
    $actions = [
        \App\Models\DocumentRequest::STAGE_REQUESTED => [
            'The system will record inspection, ID verification, eligibility, and approval in one action.',
            'process_request',
            'Process & prepare document',
            'bi-check2-all',
            'brand'
        ],

        \App\Models\DocumentRequest::STAGE_INSPECTION => [
            "Compare the resident's physical appearance and valid ID against the registered record.",
            'verify_id',
            'Verify valid ID',
            'bi-person-vcard',
            'brand'
        ],

        \App\Models\DocumentRequest::STAGE_ID_VERIFIED => [
            'Confirm that the resident is active and eligible based on the official record.',
            'mark_eligible',
            'Mark eligible',
            'bi-check2-circle',
            'brand'
        ],

        \App\Models\DocumentRequest::STAGE_ELIGIBLE => [
            'Authorize this verified and eligible request for release.',
            'approve_release',
            'Approve for release',
            'bi-shield-check',
            'green'
        ],

        \App\Models\DocumentRequest::STAGE_APPROVED => [
            'Printing is recorded under your account before the official copy opens.',
            'print_document',
            'Preview and print document',
            'bi-printer-fill',
            'brand'
        ],

        \App\Models\DocumentRequest::STAGE_PRINTED => [
            'After the assigned official physically signs the printed copy and applies the barangay seal, confirm it here.',
            'confirm_signed_sealed',
            'Confirm signature and seal',
            'bi-patch-check',
            'green'
        ],

        \App\Models\DocumentRequest::STAGE_SIGNED_SEALED => [
            'Give the completed document directly to the verified resident.',
            'release_document',
            'Mark as released',
            'bi-box-arrow-right',
            'green',
            'Confirm that the document was released directly to the verified resident?'
        ],
    ];

    $next = $actions[$document->workflow_stage] ?? null;
@endphp

<div class="docs">

    {{-- =========================================================
         HEADER
         ========================================================= --}}

    <a href="{{ route('documents.index') }}" class="back">
        <i class="bi bi-arrow-left"></i>
        Back to Barangay Documents
    </a>

    <div class="head">
        <div>
            <h1 class="display">Document processing</h1>

            <p>
                Request code:
                <b>{{ $document->request_code }}</b>
            </p>
        </div>

        <span class="pill {{ $statusClass }}">
            {{ $document->status }}
        </span>
    </div>

    {{-- =========================================================
         WORKFLOW ERROR
         ========================================================= --}}

    @if ($errors->has('workflow'))
        <div class="note st-disapproved" role="alert">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <div>
                {{ $errors->first('workflow') }}
            </div>

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
         THIRD-PARTY DECLINED
         ========================================================= --}}

    @if ($isDeclined)
        <div class="note st-disapproved" role="alert">

            <i class="bi bi-x-circle-fill"></i>

            <div>
                <b>Third-party requester: document not released</b>

                {{ $document->decline_reason }}
            </div>

        </div>
    @endif

    {{-- =========================================================
         SIMPLIFIED PROCESSING JOURNEY
         ========================================================= --}}

    <section class="card2">

        <div class="card2-head">

            <div>

                <h2 class="display">
                    Processing journey
                </h2>

                <small>
                    Major processing stages are shown here.
                    Detailed verification checks are recorded in the audit record.
                </small>

            </div>

            @if ($isReleased)
                <span class="pill st-approved">
                    <i class="bi bi-check-circle-fill"></i>
                    Successfully released
                </span>
            @endif

        </div>

        <div class="card2-body">

            @if ($isDeclined)

                <div class="stopped">

                    <i class="bi bi-person-x-fill"></i>

                    <span>
                        Processing stopped because the registered resident
                        was not personally present.
                    </span>

                </div>

            @else

                <div class="journey">

                    <ol>

                        @foreach ($journeySteps as $journeyKey => $journeyLabel)

                            @php
                                $stepIndex = array_search(
                                    $journeyKey,
                                    $journeyOrder,
                                    true
                                );

                                $isComplete = $stepIndex < $journeyIndex
                                    || ($isReleased && $stepIndex <= $journeyIndex);

                                $isCurrent = $stepIndex === $journeyIndex
                                    && ! $isReleased;
                            @endphp

                            <li
                                @class([
                                    'jstep',
                                    'done' => $isComplete,
                                    'now'  => $isCurrent,
                                ])

                                @if ($isCurrent)
                                    aria-current="step"
                                @endif
                            >

                                <div class="jdot">

                                    @if ($isComplete)

                                        <i class="bi bi-check-lg"></i>

                                    @else

                                        {{ $stepIndex + 1 }}

                                    @endif

                                </div>

                                <div class="jname">
                                    {{ $journeyLabel }}
                                </div>

                            </li>

                        @endforeach

                    </ol>

                </div>

            @endif

        </div>

    </section>

    <div class="row g-4">

        {{-- =====================================================
             LEFT COLUMN
             ===================================================== --}}

        <div class="col-lg-8">

            {{-- =================================================
                 REQUEST INFORMATION
                 ================================================= --}}

            <section class="card2">

                <div class="card2-head">
                    <h2 class="display">
                        Request information
                    </h2>
                </div>

                <div class="card2-body">

                    <div class="grid">

                        <div>

                            <div class="k">
                                Resident
                            </div>

                            <div class="who">

                                <span class="avatar" aria-hidden="true">
                                    {{ $initials }}
                                </span>

                                <div class="v">

                                    {{ $name }}

                                    @if ($resident)
                                        <small>
                                            {{ $resident->address }}
                                        </small>
                                    @endif

                                </div>

                            </div>

                        </div>

                        <div>

                            <div class="k">
                                Document type
                            </div>

                            <div class="v">
                                {{ $document->document_type }}
                            </div>

                        </div>

                        <div>

                            <div class="k">
                                Purpose
                            </div>

                            <div class="v">
                                {{ $document->purpose ?: 'Not specified' }}
                            </div>

                        </div>

                        <div>

                            <div class="k">
                                Requested
                            </div>

                            <div class="v">

                                {{ $document->requested_at?->format('F d, Y') }}

                                <small>
                                    {{ $document->requested_at?->format('h:i A') }}
                                </small>

                            </div>

                        </div>

                        @if ($document->remarks)

                            <div class="full">

                                <div class="k">
                                    Remarks
                                </div>

                                <div>
                                    {{ $document->remarks }}
                                </div>

                            </div>

                        @endif

                        @if (! empty($document->document_details))

                            <div class="full">

                                <div class="k">
                                    Document-specific details
                                </div>

                                <div
                                    class="grid"
                                    style="margin-top:.75rem;"
                                >

                                    @foreach ($document->document_details as $detailKey => $detailValue)

                                        @if (
                                            ! is_array($detailValue)
                                            && $detailValue !== null
                                            && $detailValue !== ''
                                        )

                                            <div>

                                                <div class="k">
                                                    {{ ucwords(str_replace('_', ' ', $detailKey)) }}
                                                </div>

                                                <div class="v">
                                                    {{ $detailValue }}
                                                </div>

                                            </div>

                                        @endif

                                    @endforeach

                                </div>

                            </div>

                        @endif

                    </div>

                </div>

            </section>

            {{-- =================================================
                 PROCESSING AUDIT RECORD
                 ================================================= --}}

            <section class="card2">

                <div class="card2-head">

                    <h2 class="display">
                        Processing audit record
                    </h2>

                </div>

                <ol class="tl">

                    @foreach ($auditRows as $audit)

                        <li @class([
                            'done' => (bool) $audit['at']
                        ])>

                            <span class="dot">
                                <i class="bi bi-check-lg"></i>
                            </span>

                            <div class="row">

                                <div>

                                    <b>
                                        {{ $audit['label'] }}
                                    </b>

                                    <small>
                                        {{ $audit['officer']?->name ?? 'Not yet processed' }}
                                    </small>

                                </div>

                                <div class="when">

                                    {{
                                        $audit['at']?->format('M d, Y, h:i A')
                                        ?? 'Waiting'
                                    }}

                                </div>

                            </div>

                        </li>

                    @endforeach

                </ol>

            </section>

        </div>

        {{-- =====================================================
             RIGHT COLUMN: ACTIONS
             ===================================================== --}}

        <div class="col-lg-4">

            <aside class="card2 side">

                <div class="card2-head">

                    <h2 class="display">
                        Processing actions
                    </h2>

                </div>

                <div class="card2-body">

                    @if (! auth()->user()->canProcessDocuments())

                        <div class="state muted">

                            <i class="bi bi-lock-fill"></i>

                            <span>
                                You can view this request, but you are not
                                authorized to process or release documents.
                            </span>

                        </div>

                    @elseif ($isDeclined)

                        <div class="state st-disapproved">

                            <i class="bi bi-person-x-fill"></i>

                            <span>
                                This request has been marked as not released.
                            </span>

                        </div>

                    @elseif ($isReleased)

                        <div class="state st-approved">

                            <i class="bi bi-check-circle-fill"></i>

                            <span>
                                This document was released to the resident.
                            </span>

                        </div>

                    @elseif ($next)

                        @php
                            [
                                $hint,
                                $action,
                                $btnLabel,
                                $icon,
                                $style
                            ] = $next;

                            $confirm = $next[5] ?? null;
                        @endphp

                        <p class="stage-now display">

                            Current step:

                            {{ $steps[$document->workflow_stage] ?? '' }}

                        </p>

                        <p class="hint">
                            {{ $hint }}
                        </p>

                        <div class="stack">

                            <form
                                method="POST"
                                action="{{ route('documents.advance', $document) }}"

                                @if ($action === 'print_document')
                                    target="docPrintWindow"
                                    onsubmit="return openDocPrintWindow();"
                                @endif
                            >

                                @csrf

                                <input
                                    type="hidden"
                                    name="action"
                                    value="{{ $action }}"
                                >

                                <button
                                    type="submit"
                                    class="act act-{{ $style }}"

                                    @if ($confirm)
                                        onclick="return confirm('{{ e($confirm) }}')"
                                    @endif
                                >

                                    <i class="bi {{ $icon }}"></i>

                                    {{ $btnLabel }}

                                </button>

                            </form>

                            {{-- Approved stage: preview without changing workflow state --}}
                            @if (
                                $document->workflow_stage ===
                                \App\Models\DocumentRequest::STAGE_APPROVED
                            )

                                <a
                                    href="{{ route('documents.preview', $document) }}"
                                    class="act act-line"
                                    target="docPrintWindow"
                                    onclick="return openDocPrintWindow();"
                                >

                                    <i class="bi bi-eye"></i>

                                    Preview Only

                                </a>

                            @endif

                            {{-- Printed stage: reopen printable copy --}}
                            @if (
                                $document->workflow_stage ===
                                \App\Models\DocumentRequest::STAGE_PRINTED
                            )

                                <a
                                    href="{{ route('documents.print', $document) }}"
                                    class="act act-line"
                                    target="docPrintWindow"
                                    onclick="return openDocPrintWindow();"
                                >

                                    <i class="bi bi-eye"></i>

                                    Open Printable Copy

                                </a>

                            @endif

                            {{-- Inspection stage: requester is not the resident --}}
                            @if (
                                $document->workflow_stage ===
                                \App\Models\DocumentRequest::STAGE_INSPECTION
                            )

                                <div class="divider">
                                    or, if the requester is not the resident
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('documents.advance', $document) }}"
                                >

                                    @csrf

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="decline_third_party"
                                    >

                                    <input
                                        type="hidden"
                                        name="decline_reason"
                                        value="Resident must personally appear and present a valid ID. Third-party release is not allowed."
                                    >

                                    <button
                                        type="submit"
                                        class="act act-danger"
                                        onclick="return confirm('Confirm that the requester is not the registered resident?')"
                                    >

                                        <i class="bi bi-person-x"></i>

                                        Third-party requester

                                    </button>

                                </form>

                            @endif

                        </div>

                    @endif

                </div>

            </aside>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>

    /*
     * On small screens, bring the current step into view.
     */
    (function () {

        const now = document.querySelector('.jstep.now');

        if (now) {
            now.scrollIntoView({
                inline: 'center',
                block: 'nearest'
            });
        }

    })();


    /**
     * Opens or reuses a named window immediately.
     *
     * This allows the print workflow to open a loading window before
     * server-side DOCX/PDF generation finishes.
     */
    function openDocPrintWindow() {

        var win = window.open('', 'docPrintWindow');

        if (!win) {
            return true;
        }

        var isDark =
            document.documentElement.getAttribute('data-theme') === 'dark';

        var bg = isDark
            ? '#141a25'
            : '#eef2f7';

        var fg = isDark
            ? '#eef2f8'
            : '#14213d';

        var muted = isDark
            ? '#9aa7bb'
            : '#5f6b7d';

        var brand = isDark
            ? '#7aa7f0'
            : '#1f4e9c';

        var track = isDark
            ? 'rgba(255,255,255,.12)'
            : 'rgba(20,33,61,.12)';

        win.document.open();

        win.document.write(

            '<!DOCTYPE html>' +
            '<html>' +
            '<head>' +
            '<meta charset="utf-8">' +
            '<meta name="viewport" content="width=device-width, initial-scale=1">' +
            '<title>Opening document…</title>' +

            '<style>' +

            'html,body{height:100%;margin:0;}' +

            'body{' +
            'display:flex;' +
            'align-items:center;' +
            'justify-content:center;' +
            'background:' + bg + ';' +
            'color:' + fg + ';' +
            'font-family:"Public Sans",system-ui,-apple-system,sans-serif;' +
            '}' +

            '.box{text-align:center;}' +

            '.spinner{' +
            'width:44px;' +
            'height:44px;' +
            'margin:0 auto 1.1rem;' +
            'border-radius:50%;' +
            'border:4px solid ' + track + ';' +
            'border-top-color:' + brand + ';' +
            'animation:spin .8s linear infinite;' +
            '}' +

            '@keyframes spin{' +
            'to{transform:rotate(360deg);}' +
            '}' +

            'p{' +
            'margin:0;' +
            'font-size:.95rem;' +
            'color:' + muted + ';' +
            '}' +

            '</style>' +

            '</head>' +

            '<body>' +

            '<div class="box">' +

            '<div class="spinner"></div>' +

            '<p id="docPrintMsg">Opening document…</p>' +

            '</div>' +

            '<script>' +

            'var m=[' +
            '"Opening document…",' +
            '"Generating official copy…",' +
            '"Converting to PDF…",' +
            '"Almost ready…"' +
            '];' +

            'var i=0;' +

            'setInterval(function(){' +

            'i=(i+1)%m.length;' +

            'var e=document.getElementById("docPrintMsg");' +

            'if(e){e.textContent=m[i];}' +

            '},1600);' +

            '<' + '/script>' +

            '</body>' +

            '</html>'

        );

        win.document.close();

        return true;
    }

</script>
@endpush