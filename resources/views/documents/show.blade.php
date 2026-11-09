@extends('layouts.app')

@section('title', 'Document Processing | e-Poblacion')

@push('styles')
<style>
    /* =========================================================
       DOCUMENT PROCESSING — watermelon theme
       ========================================================= */
    .docs {
        --ink: #3a2a2e; --muted: #8a6f75; --paper: #ffffff; --wash: #fdf1f3; --line: #f0d0d6;
        --brand: #237a35; --brand-soft: rgba(35,122,53,.11);
        --green: #2f9e44; --green-d: #237a35; --green-bg: rgba(47,158,68,.14); --sun: #ffc933;
        --info: #1c7ed6; --info-soft: rgba(28,126,214,.12);
        --pending: #b26a00;     --pending-bg: #fff1d6;
        --processing: #0b7285;  --processing-bg: #d8f3f8;
        --approved: #2b8a3e;    --approved-bg: #dcf5e3;
        --completed: #5f3dc4;   --completed-bg: #e8e1fb;
        --released: #1f4e9c;    --released-bg: #dbe8fb;
        --disapproved: #c92a3b; --disapproved-bg: #fde3e3;
        font-family: var(--font); color: var(--ink);
    }

    html[data-theme="dark"] .docs {
        --ink: #f6eff1; --muted: #a8b8ad; --paper: #17251d; --wash: #1c2d23; --line: #2b4033;
        --brand: #5fc062; --brand-soft: rgba(95,192,98,.16);
        --green: #5fc062; --green-d: #8fe29a; --green-bg: rgba(95,192,98,.16);
        --info: #6cb2ff; --info-soft: rgba(108,178,255,.15);
        --pending: #f5b942;     --pending-bg: #3a2c10;
        --processing: #5ccfe0;  --processing-bg: #0f3038;
        --approved: #6fd48a;   --approved-bg: #143222;
        --completed: #b7a0f5;  --completed-bg: #2a2150;
        --released: #8db6f7;   --released-bg: #182c4d;
        --disapproved: #ff7b87; --disapproved-bg: #401a1d;
    }

    .docs * { box-sizing: border-box; }
    .docs [hidden] { display: none !important; }
    .docs .display { letter-spacing: -.02em; }
    .docs a:focus-visible, .docs button:focus-visible {
        outline: 3px solid var(--info);
        outline-offset: 2px;
    }

    .back {
        display: inline-flex; gap: .4rem; align-items: center;
        color: var(--brand); text-decoration: none; font-weight: 700; font-size: .92rem;
    }
    .back:hover { text-decoration: underline; color: var(--brand); }

    .head {
        display: flex; flex-wrap: wrap; gap: 1rem;
        justify-content: space-between; align-items: flex-end; margin: .9rem 0 1.5rem;
    }
    .head h1 { font-size: clamp(1.7rem, 3.2vw, 2.3rem); font-weight: 800; margin: 0; line-height: 1.1; }
    .head p { margin: .35rem 0 0; color: var(--muted); }
    .head p b { color: var(--ink); }

    .pill {
        display: inline-flex; align-items: center; gap: .45rem;
        padding: .35rem .9rem; border-radius: 999px; font-size: .9rem;
        font-weight: 700; color: var(--c); background: var(--bg);
    }
    .pill::before { content: ''; width: .55rem; height: .55rem; border-radius: 50%; background: var(--c); }
    .st-pending { --c: var(--pending); --bg: var(--pending-bg); }
    .st-processing { --c: var(--processing); --bg: var(--processing-bg); }
    .st-approved { --c: var(--approved); --bg: var(--approved-bg); }
    .st-completed { --c: var(--completed); --bg: var(--completed-bg); }
    .st-released { --c: var(--released); --bg: var(--released-bg); }
    .st-disapproved { --c: var(--disapproved); --bg: var(--disapproved-bg); }

    .note {
        display: flex; gap: .75rem; align-items: flex-start;
        padding: .9rem 1.1rem; border-radius: 1rem; margin-bottom: 1rem;
        border: 1px solid var(--c); background: var(--bg); color: var(--ink);
    }
    .note b, .note i { color: var(--c); }
    .note b { display: block; }
    .note .x {
        margin-left: auto; background: none; border: 0;
        color: var(--c); font-size: 1.2rem; line-height: 1;
    }

    .card2 {
        background: var(--paper); border: 1px solid var(--line);
        border-radius: 1.4rem; margin-bottom: 1rem; overflow: hidden;
        box-shadow: 0 2px 4px rgba(120,40,55,.04), 0 10px 24px rgba(120,40,55,.05);
    }
    .card2-head {
        padding: .95rem 1.3rem; border-bottom: 1px solid var(--line);
        display: flex; justify-content: space-between; align-items: center;
        gap: .75rem; flex-wrap: wrap;
    }
    .card2-head h2 { font-size: 1.05rem; font-weight: 800; margin: 0; color: var(--brand); }
    .card2-head small { color: var(--muted); }
    .card2-body { padding: 1.3rem; }

    /* THE JOURNEY */
    .journey { overflow-x: auto; padding: .75rem .25rem 1rem; scrollbar-width: thin; }
    .journey ol {
        list-style: none; margin: 0; padding: 0;
        display: flex; min-width: 650px; width: 100%;
    }
    .jstep { flex: 1 0 108px; text-align: center; position: relative; }
    .jstep:not(:last-child)::after {
        content: ''; position: absolute; top: 22px;
        left: calc(50% + 25px); width: calc(100% - 50px);
        height: 5px; border-radius: 5px; background: var(--line);
    }
    .jstep.done:not(:last-child)::after { background: var(--green); }
    .jbtn {
        display: block; width: 100%; padding: 0 .2rem;
        border: 0; background: none; color: inherit; font: inherit; cursor: pointer;
    }
    .jdot {
        width: 46px; height: 46px; margin: 0 auto .6rem; border-radius: 50%;
        display: grid; place-items: center; font-weight: 800;
        background: var(--paper); border: 3px solid var(--line);
        color: var(--muted); position: relative; z-index: 1;
        transition: transform .15s, box-shadow .15s;
    }
    .jbtn:hover .jdot { transform: translateY(-2px) scale(1.04); }
    .jstep.done .jdot { background: var(--green); border-color: var(--green); color: #fff; }
    .jstep.now .jdot {
        background: var(--info); border-color: var(--info); color: #fff;
        box-shadow: 0 0 0 5px var(--info-soft), 0 8px 18px rgba(28,126,214,.3);
    }
    html[data-theme="dark"] .jstep.done .jdot,
    html[data-theme="dark"] .jstep.now .jdot { color: #0f1a14; }
    .jstep.sel .jdot {
        box-shadow: 0 0 0 4px var(--paper), 0 0 0 7px var(--c-sel, var(--line));
    }
    .jstep.done { --c-sel: var(--green); }
    .jstep.now { --c-sel: var(--info); }
    .jname { font-size: .83rem; font-weight: 700; color: var(--muted); line-height: 1.25; }
    .jstep.done .jname { color: var(--green-d); }
    .jstep.now .jname { color: var(--info); }
    .jstate { display: block; margin-top: .15rem; font-size: .72rem; font-weight: 600; color: var(--muted); }

    .jd {
        margin-top: .5rem; padding: 1.1rem 1.2rem; border-radius: 1.2rem;
        border: 1.5px solid var(--cd, var(--line)); background: var(--bgd, var(--wash));
    }
    .jd.done { --cd: var(--green); --bgd: var(--green-bg); }
    .jd.now { --cd: var(--info); --bgd: var(--info-soft); }
    .jd-head {
        display: flex; align-items: center; justify-content: space-between;
        gap: .75rem; flex-wrap: wrap; margin-bottom: .8rem;
    }
    .jd-head b { font-size: 1.02rem; font-weight: 800; }
    .jd-tag {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .22rem .7rem; border-radius: 999px; font-size: .78rem;
        font-weight: 800; background: var(--paper); color: var(--muted);
    }
    .jd.done .jd-tag { color: var(--green-d); }
    .jd.now .jd-tag { color: var(--info); }
    .jd ul { list-style: none; margin: 0; padding: 0; display: grid; gap: .55rem; }
    .jd li {
        display: flex; gap: .7rem; align-items: flex-start;
        padding: .65rem .8rem; border-radius: .9rem; background: var(--paper);
    }
    .jd li .ck {
        width: 1.45rem; height: 1.45rem; border-radius: 50%; flex: none;
        display: grid; place-items: center; font-size: .75rem;
        border: 2px solid var(--line); color: transparent;
    }
    .jd li.ok .ck { background: var(--green); border-color: var(--green); color: #fff; }
    html[data-theme="dark"] .jd li.ok .ck { color: #0f1a14; }
    .jd li .tx { flex: 1; min-width: 0; }
    .jd li b { display: block; font-weight: 700; }
    .jd li:not(.ok) b { color: var(--muted); font-weight: 600; }
    .jd li small { color: var(--muted); }
    .jd li .when { text-align: right; color: var(--muted); font-size: .85rem; white-space: nowrap; }
    @media (max-width: 575.98px) {
        .jd li { flex-wrap: wrap; }
        .jd li .when { text-align: left; width: 100%; padding-left: 2.15rem; }
    }

    .stopped {
        display: flex; gap: .7rem; align-items: center;
        padding: 1rem; border-radius: 1rem; background: var(--disapproved-bg);
    }
    .stopped i { color: var(--disapproved); font-size: 1.3rem; }

    /* INFO */
    .grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem 1.5rem; }
    @media (max-width: 575.98px) { .grid { grid-template-columns: 1fr; } }
    .grid .full { grid-column: 1 / -1; }
    .k { font-size: .82rem; color: var(--muted); margin-bottom: .2rem; font-weight: 600; }
    .v { font-weight: 700; }
    .v small { display: block; font-weight: 500; color: var(--muted); font-size: .85rem; }
    .who { display: flex; gap: .75rem; align-items: center; }
    .avatar {
        width: 2.6rem; height: 2.6rem; border-radius: 50%;
        display: grid; place-items: center; flex: none;
        font-weight: 800; background: var(--brand-soft); color: var(--brand);
    }

    /* ACTION PANEL */
    .side { position: sticky; top: 1rem; }
    @media (max-width: 991.98px) { .side { position: static; } }
    .side .hint { color: var(--muted); font-size: .92rem; margin: 0 0 1rem; }
    .side .stage-now { font-size: 1.1rem; font-weight: 800; margin: 0 0 .35rem; }
    .act {
        display: flex; width: 100%; justify-content: center; align-items: center;
        gap: .5rem; min-height: 46px; border-radius: 999px; font-weight: 800;
        border: 1.5px solid transparent; text-decoration: none;
        padding: .55rem 1rem; transition: filter .15s, transform .15s;
    }
    .act:hover { transform: translateY(-1px); }
    .act-brand, .act-green {
        background: linear-gradient(135deg, #2f9e44, var(--brand));
        color: #fff; box-shadow: 0 8px 18px rgba(35,122,53,.28);
    }
    html[data-theme="dark"] .act-brand,
    html[data-theme="dark"] .act-green {
        color: #0b2314; background: linear-gradient(135deg, #8fe29a, var(--brand)); box-shadow: none;
    }
    .act-brand:hover, .act-green:hover { filter: brightness(1.08); color: #fff; }
    html[data-theme="dark"] .act-brand:hover,
    html[data-theme="dark"] .act-green:hover { color: #0b2314; }
    .act-line { background: transparent; border-color: var(--brand); color: var(--brand); }
    .act-line:hover { background: var(--brand-soft); color: var(--brand); }
    .act-danger { background: transparent; border-color: var(--disapproved); color: var(--disapproved); }
    .act-danger:hover { background: var(--disapproved-bg); }
    .stack { display: grid; gap: .6rem; }
    .stack form { margin: 0; }
    .stack form button:disabled { opacity: .65; cursor: wait; transform: none; }
    .divider { text-align: center; color: var(--muted); font-size: .8rem; margin: .4rem 0; }
    .state {
        display: flex; gap: .7rem; padding: .9rem 1rem;
        border-radius: 1rem; font-size: .92rem; background: var(--bg); color: var(--ink);
    }
    .state i { color: var(--c); font-size: 1.15rem; }
    .state.muted { --c: var(--muted); --bg: var(--wash); }

    /* FULL-PAGE PROCESSING OVERLAY */
    .doc-loading {
        position: fixed;
        inset: 0;
        z-index: 20000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
        background: rgba(253, 241, 243, .94);
        backdrop-filter: blur(7px);
        -webkit-backdrop-filter: blur(7px);
    }
    html[data-theme="dark"] .doc-loading {
        background: rgba(15, 26, 20, .94);
    }
    .doc-loading[hidden] { display: none !important; }
    .doc-loading-card {
        width: min(100%, 360px);
        padding: 2rem 1.5rem;
        text-align: center;
        border: 1px solid #f0d0d6;
        border-radius: 1.5rem;
        background: #fff;
        box-shadow: 0 18px 55px rgba(58, 42, 46, .14);
    }
    html[data-theme="dark"] .doc-loading-card {
        background: #17251d;
        border-color: #2b4033;
        box-shadow: 0 18px 55px rgba(0, 0, 0, .3);
    }
    .doc-loading-spinner {
        width: 58px;
        height: 58px;
        margin: 0 auto 1.2rem;
        border: 5px solid rgba(35, 122, 53, .14);
        border-top-color: #2f9e44;
        border-right-color: #237a35;
        border-radius: 50%;
        animation: doc-loading-spin .8s linear infinite;
    }
    @keyframes doc-loading-spin {
        to { transform: rotate(360deg); }
    }
    .doc-loading-title {
        margin: 0 0 .45rem;
        color: #237a35;
        font-size: 1.12rem;
        font-weight: 800;
    }
    .doc-loading-message {
        margin: 0;
        color: #8a6f75;
        font-size: .9rem;
        line-height: 1.5;
    }
    html[data-theme="dark"] .doc-loading-title { color: #8fe29a; }
    html[data-theme="dark"] .doc-loading-message { color: #a8b8ad; }

    @media (prefers-reduced-motion: reduce) {
        .docs *, .doc-loading-spinner {
            transition: none !important;
            scroll-behavior: auto !important;
            animation-duration: 2s !important;
        }
    }
</style>
@endpush

@section('content')

@php
    $resident = $document->resident;

    $steps = \App\Models\DocumentRequest::workflowSteps();

    $isDeclined = $document->isThirdPartyDeclined();
    $isReleased = $document->workflow_stage === \App\Models\DocumentRequest::STAGE_RELEASED;
    $stage = $document->workflow_stage;

    $journey = [
        'request' => [
            'label' => 'Request Created',
            'checks' => [['Request created', $document->requested_at, null]],
        ],
        'verified' => [
            'label' => 'Verified',
            'checks' => [
                ['Inspection', $document->inspection_at, $document->inspectionOfficer],
                ['ID verified', $document->id_verified_at, $document->idVerifiedOfficer],
                ['Marked eligible', $document->eligible_at, $document->eligibleOfficer],
            ],
        ],
        'approved' => [
            'label' => 'Approved',
            'checks' => [['Approved for release', $document->approved_at, $document->approvedOfficer]],
        ],
        'printed' => [
            'label' => 'Printed',
            'checks' => [['Printed', $document->printed_at, $document->printedOfficer]],
        ],
        'signed' => [
            'label' => 'Signed & Sealed',
            'checks' => [['Physical signature and seal', $document->signed_sealed_at, $document->signedSealedOfficer]],
        ],
        'released' => [
            'label' => 'Released',
            'checks' => [['Released', $document->released_at, $document->releasedOfficer]],
        ],
    ];

    $verifiedStages = [
        \App\Models\DocumentRequest::STAGE_INSPECTION,
        \App\Models\DocumentRequest::STAGE_ID_VERIFIED,
        \App\Models\DocumentRequest::STAGE_ELIGIBLE,
    ];

    $journeyCurrent = match (true) {
        $stage === \App\Models\DocumentRequest::STAGE_REQUESTED => 'request',
        in_array($stage, $verifiedStages, true) => 'verified',
        $stage === \App\Models\DocumentRequest::STAGE_APPROVED => 'approved',
        $stage === \App\Models\DocumentRequest::STAGE_PRINTED => 'printed',
        $stage === \App\Models\DocumentRequest::STAGE_SIGNED_SEALED => 'signed',
        $stage === \App\Models\DocumentRequest::STAGE_RELEASED => 'released',
        default => 'request',
    };

    $journeyKeys = array_keys($journey);
    $journeyIndex = array_search($journeyCurrent, $journeyKeys, true);
    $journeyIndex = $journeyIndex === false ? 0 : $journeyIndex;

    $stateOf = fn ($i) => ($i < $journeyIndex || $isReleased) ? 'done' : ($i === $journeyIndex ? 'now' : 'todo');

    $statusClass = match ($document->status) {
        'Released' => 'st-released',
        'Approved' => 'st-approved',
        'Completed' => 'st-completed',
        'Disapproved' => 'st-disapproved',
        'Processing' => 'st-processing',
        default => 'st-pending',
    };

    $name = $resident?->full_name ?? 'Resident not found';

    $nameParts = array_values(array_filter(preg_split('/\s+/', trim((string) $name))));
    $initials = $resident && $nameParts
        ? strtoupper(mb_substr($nameParts[0], 0, 1) . (count($nameParts) > 1 ? mb_substr(end($nameParts), 0, 1) : ''))
        : '?';

    $detailLabels = ['ctc_number' => 'CTC Number'];

    $actions = [
        \App\Models\DocumentRequest::STAGE_REQUESTED => [
            'Record the resident inspection and valid-ID verification, then advance this request to the Verified stage.',
            'process_request', 'Process & verify request', 'bi-person-vcard', 'brand',
        ],
        \App\Models\DocumentRequest::STAGE_ID_VERIFIED => [
            'Confirm the resident is eligible based on the official record. This records eligibility and advances the request to Approved.',
            'approve_release', 'Mark eligible & approve', 'bi-shield-check', 'green',
        ],
        \App\Models\DocumentRequest::STAGE_APPROVED => [
            'Printing is recorded under your account before the official copy opens.',
            'print_document', 'Preview and print document', 'bi-printer-fill', 'brand',
        ],
        \App\Models\DocumentRequest::STAGE_PRINTED => [
            'After the assigned official physically signs the printed copy and applies the barangay seal, confirm it here.',
            'confirm_signed_sealed', 'Confirm signature and seal', 'bi-patch-check', 'green',
        ],
        \App\Models\DocumentRequest::STAGE_SIGNED_SEALED => [
            'Give the completed document directly to the verified resident.',
            'release_document', 'Mark as released', 'bi-box-arrow-right', 'green',
            'Confirm that the document was released directly to the verified resident?',
        ],
    ];

    $next = $actions[$stage] ?? null;
@endphp

<div class="docs">

    {{-- Loading overlay shown while a document action is processing --}}
    <div class="doc-loading" id="documentProcessingOverlay" role="status" aria-live="polite" hidden>
        <div class="doc-loading-card">
            <div class="doc-loading-spinner" aria-hidden="true"></div>
            <h2 class="doc-loading-title" id="documentLoadingTitle">Processing request…</h2>
            <p class="doc-loading-message" id="documentLoadingMessage">
                Please wait while we update the document record. Do not close this page.
            </p>
        </div>
    </div>

    <a href="{{ route('documents.index') }}" class="back">
        <i class="bi bi-arrow-left"></i> Back to Barangay Documents
    </a>

    <div class="head">
        <div>
            <h1 class="display">Document processing</h1>
            <p>Request code: <b>{{ $document->request_code }}</b></p>
        </div>

        <span class="pill {{ $statusClass }}">{{ $document->status }}</span>
    </div>

    @if ($errors->has('workflow') || $errors->has('action'))
        <div class="note st-disapproved" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>{{ $errors->first('workflow') ?: $errors->first('action') }}</div>
            <button type="button" class="x" onclick="this.parentElement.remove()" aria-label="Dismiss">&times;</button>
        </div>
    @endif

    @if ($isDeclined)
        <div class="note st-disapproved" role="alert">
            <i class="bi bi-x-circle-fill"></i>
            <div>
                <b>Third-party requester: document not released</b>
                {{ $document->decline_reason }}
            </div>
        </div>
    @endif

    {{-- THE JOURNEY --}}
    <section class="card2">
        <div class="card2-head">
            <div>
                <h2 class="display">Processing journey</h2>
                <small>Click a step to see who handled it and when.</small>
            </div>

            @if ($isReleased)
                <span class="pill st-approved"><i class="bi bi-check-circle-fill"></i> Successfully released</span>
            @endif
        </div>

        <div class="card2-body">
            @if ($isDeclined)
                <div class="stopped">
                    <i class="bi bi-person-x-fill"></i>
                    <span>Processing stopped because the registered resident was not personally present.</span>
                </div>
            @else
                <div class="journey">
                    <ol role="tablist" aria-label="Processing journey">
                        @foreach ($journey as $key => $step)
                            @php
                                $i = array_search($key, $journeyKeys, true);
                                $state = $stateOf($i);
                                $label = ['done' => 'Done', 'now' => 'In progress', 'todo' => 'Not yet'][$state];
                            @endphp

                            <li @class(['jstep', $state, 'sel' => $i === $journeyIndex]) role="presentation" data-step="{{ $key }}">
                                <button type="button" class="jbtn" role="tab"
                                        aria-selected="{{ $i === $journeyIndex ? 'true' : 'false' }}"
                                        aria-controls="jd-{{ $key }}"
                                        @if ($state === 'now') aria-current="step" @endif>
                                    <span class="jdot">
                                        @if ($state === 'done')
                                            <i class="bi bi-check-lg"></i>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </span>
                                    <span class="jname">{{ $step['label'] }}</span>
                                    <span class="jstate">{{ $label }}</span>
                                </button>
                            </li>
                        @endforeach
                    </ol>
                </div>

                @foreach ($journey as $key => $step)
                    @php
                        $i = array_search($key, $journeyKeys, true);
                        $state = $stateOf($i);
                    @endphp

                    <div class="jd {{ $state }}" id="jd-{{ $key }}" role="tabpanel" @if ($i !== $journeyIndex) hidden @endif>
                        <div class="jd-head">
                            <b>{{ $i + 1 }}. {{ $step['label'] }}</b>
                            <span class="jd-tag">
                                @if ($state === 'done')
                                    <i class="bi bi-check-circle-fill"></i> Completed
                                @elseif ($state === 'now')
                                    <i class="bi bi-hourglass-split"></i> Current step
                                @else
                                    <i class="bi bi-clock"></i> Not started yet
                                @endif
                            </span>
                        </div>

                        <ul>
                            @foreach ($step['checks'] as [$checkLabel, $at, $officer])
                                <li @class(['ok' => (bool) $at])>
                                    <span class="ck"><i class="bi bi-check-lg"></i></span>

                                    <div class="tx">
                                        <b>{{ $checkLabel }}</b>
                                        <small>{{ $at ? ($officer?->name ?? 'Recorded by the system') : 'Not yet processed' }}</small>
                                    </div>

                                    <div class="when">{{ $at?->format('M d, Y, h:i A') ?? 'Waiting' }}</div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            @endif
        </div>
    </section>

    <div class="row g-4">

        {{-- LEFT --}}
        <div class="col-lg-8">
            <section class="card2">
                <div class="card2-head">
                    <h2 class="display">Request information</h2>
                </div>

                <div class="card2-body">
                    <div class="grid">
                        <div>
                            <div class="k">Resident</div>
                            <div class="who">
                                <span class="avatar" aria-hidden="true">{{ $initials }}</span>
                                <div class="v">
                                    {{ $name }}
                                    @if ($resident)
                                        <small>{{ $resident->address }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="k">Document type</div>
                            <div class="v">{{ $document->document_type }}</div>
                        </div>

                        <div>
                            <div class="k">Purpose</div>
                            <div class="v">{{ $document->purpose ?: 'Not specified' }}</div>
                        </div>

                        <div>
                            <div class="k">Requested</div>
                            <div class="v">
                                {{ $document->requested_at?->format('F d, Y') }}
                                <small>{{ $document->requested_at?->format('h:i A') }}</small>
                            </div>
                        </div>

                        @if ($document->remarks)
                            <div class="full">
                                <div class="k">Remarks</div>
                                <div>{{ $document->remarks }}</div>
                            </div>
                        @endif

                        @if (! empty($document->document_details))
                            <div class="full">
                                <div class="k">Document-specific details</div>

                                <div class="grid" style="margin-top:.75rem;">
                                    @foreach ($document->document_details as $detailKey => $detailValue)
                                        @if (! is_array($detailValue) && $detailValue !== null && $detailValue !== '')
                                            <div>
                                                <div class="k">{{ $detailLabels[$detailKey] ?? ucwords(str_replace('_', ' ', $detailKey)) }}</div>
                                                <div class="v">{{ $detailValue }}</div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        </div>

        {{-- RIGHT: ACTIONS --}}
        <div class="col-lg-4">
            <aside class="card2 side">
                <div class="card2-head">
                    <h2 class="display">Processing actions</h2>
                </div>

                <div class="card2-body">
                    @if (! auth()->user()->canProcessDocuments())
                        <div class="state muted">
                            <i class="bi bi-lock-fill"></i>
                            <span>You can view this request, but you are not authorized to process or release documents.</span>
                        </div>
                    @elseif ($isDeclined)
                        <div class="state st-disapproved">
                            <i class="bi bi-person-x-fill"></i>
                            <span>This request has been marked as not released.</span>
                        </div>
                    @elseif ($isReleased)
                        <div class="state st-approved">
                            <i class="bi bi-check-circle-fill"></i>
                            <span>This document was released to the resident.</span>
                        </div>
                    @elseif ($next)
                        @php
                            [$hint, $action, $btnLabel, $icon, $style] = $next;
                            $confirm = $next[5] ?? null;
                        @endphp

                        <p class="stage-now display">Current step: {{ $steps[$stage] ?? '' }}</p>
                        <p class="hint">{{ $hint }}</p>

                        <div class="stack">
                            <form method="POST"
                                  action="{{ route('documents.advance', $document) }}"
                                  class="js-document-process-form"
                                  data-loading-title="{{ $action === 'print_document' ? 'Preparing official document…' : 'Processing request…' }}"
                                  data-loading-message="{{ $action === 'print_document' ? 'Please wait while the official document is being prepared.' : 'Please wait while we update the document record. Do not close this page.' }}"
                                  @if ($action === 'print_document') target="docPrintWindow" onsubmit="return openDocPrintWindow();" @endif>
                                @csrf
                                <input type="hidden" name="action" value="{{ $action }}">

                                <button type="submit" class="act act-{{ $style }}"
                                        @if ($confirm) onclick="return confirm('{{ e($confirm) }}')" @endif>
                                    <i class="bi {{ $icon }}"></i> {{ $btnLabel }}
                                </button>
                            </form>

                            @if ($stage === \App\Models\DocumentRequest::STAGE_APPROVED)
                                <a href="{{ route('documents.preview', $document) }}" class="act act-line"
                                   target="docPrintWindow" onclick="return openDocPrintWindow();">
                                    <i class="bi bi-eye"></i> Preview Only
                                </a>
                            @endif

                            @if ($stage === \App\Models\DocumentRequest::STAGE_PRINTED)
                                <a href="{{ route('documents.print', $document) }}" class="act act-line"
                                   target="docPrintWindow" onclick="return openDocPrintWindow();">
                                    <i class="bi bi-eye"></i> Open Printable Copy
                                </a>
                            @endif

                            @if ($stage === \App\Models\DocumentRequest::STAGE_INSPECTION)
                                <div class="divider">or, if the requester is not the resident</div>

                                <form method="POST"
                                      action="{{ route('documents.advance', $document) }}"
                                      class="js-document-process-form"
                                      data-loading-title="Updating document request…"
                                      data-loading-message="Please wait while this request is being recorded.">
                                    @csrf
                                    <input type="hidden" name="action" value="decline_third_party">
                                    <input type="hidden" name="decline_reason" value="Resident must personally appear and present a valid ID. Third-party release is not allowed.">

                                    <button type="submit" class="act act-danger"
                                            onclick="return confirm('Confirm that the requester is not the registered resident?')">
                                        <i class="bi bi-person-x"></i> Third-party requester
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
    /* =========================================================
       JOURNEY: click a step to show only that step's details
       ========================================================= */
    (function () {
        const steps = Array.from(document.querySelectorAll('.jstep'));
        if (!steps.length) return;

        function select(step) {
            steps.forEach(function (s) {
                const on = s === step;
                s.classList.toggle('sel', on);

                const button = s.querySelector('.jbtn');
                if (button) {
                    button.setAttribute('aria-selected', on ? 'true' : 'false');
                }

                const panel = document.getElementById('jd-' + s.dataset.step);
                if (panel) panel.hidden = !on;
            });
        }

        steps.forEach(function (s) {
            const button = s.querySelector('.jbtn');
            if (button) {
                button.addEventListener('click', function () {
                    select(s);
                });
            }
        });

        const now = document.querySelector('.jstep.now');
        if (now) now.scrollIntoView({ inline: 'center', block: 'nearest' });
    })();


    /* =========================================================
       PRINT WINDOW
       Open a named window immediately to avoid popup blocking.
       ========================================================= */
    window.docPrintWindowRef = null;

    function openDocPrintWindow() {
        var win = window.open('', 'docPrintWindow');

        if (!win) {
            window.docPrintWindowRef = null;
            return true;
        }

        window.docPrintWindowRef = win;

        var dark = document.documentElement.getAttribute('data-theme') === 'dark';
        var bg = dark ? '#0f1a14' : '#f4d9df';
        var fg = dark ? '#f6eff1' : '#3a2a2e';
        var muted = dark ? '#a8b8ad' : '#8a6f75';
        var brand = dark ? '#5fc062' : '#237a35';
        var track = dark ? 'rgba(255,255,255,.12)' : 'rgba(58,42,46,.12)';

        win.document.open();
        win.document.write(
            '<!DOCTYPE html><html><head><meta charset="utf-8">' +
            '<meta name="viewport" content="width=device-width, initial-scale=1">' +
            '<title>Preparing document…</title><style>' +
            'html,body{height:100%;margin:0;}' +
            'body{display:flex;align-items:center;justify-content:center;background:' + bg + ';color:' + fg + ';' +
            'font-family:ui-rounded,"Nunito","Trebuchet MS",system-ui,sans-serif;}' +
            '.box{text-align:center;padding:1rem;}' +
            '.spinner{width:46px;height:46px;margin:0 auto 1.1rem;border-radius:50%;border:4px solid ' + track + ';' +
            'border-top-color:' + brand + ';animation:spin .8s linear infinite;}' +
            '@keyframes spin{to{transform:rotate(360deg);}}' +
            'p{margin:0;font-size:.95rem;font-weight:600;color:' + muted + ';}' +
            '</style></head><body><div class="box"><div class="spinner"></div>' +
            '<p>Preparing official document…</p></div></body></html>'
        );
        win.document.close();

        return true;
    }


    /* =========================================================
       DOCUMENT PROCESSING
       - Prevents the intermediate Laravel redirect page.
       - Uses the literal action attribute, not form.action.
       - Shows a full-screen loading overlay.
       - Follows Laravel redirects in the background.
       - Navigates directly to the final response URL.
       ========================================================= */
    (function () {
        const overlay = document.getElementById('documentProcessingOverlay');
        const loadingTitle = document.getElementById('documentLoadingTitle');
        const loadingMessage = document.getElementById('documentLoadingMessage');

        function showLoading(form) {
            if (!overlay) return;

            if (loadingTitle) {
                loadingTitle.textContent = form.dataset.loadingTitle || 'Processing request…';
            }

            if (loadingMessage) {
                loadingMessage.textContent = form.dataset.loadingMessage ||
                    'Please wait while we update the document record. Do not close this page.';
            }

            overlay.hidden = false;
            document.body.style.overflow = 'hidden';
        }

        function hideLoading() {
            if (!overlay) return;

            overlay.hidden = true;
            document.body.style.overflow = '';
        }

        function setButtonLoading(form) {
            const buttons = Array.from(
                form.querySelectorAll('button[type="submit"], button:not([type])')
            );

            buttons.forEach(function (button) {
                button.disabled = true;

                if (button.dataset.originalHtml === undefined) {
                    button.dataset.originalHtml = button.innerHTML;
                }

                button.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Processing…';
            });

            return buttons;
        }

        function restoreButtons(buttons) {
            buttons.forEach(function (button) {
                button.disabled = false;

                if (button.dataset.originalHtml !== undefined) {
                    button.innerHTML = button.dataset.originalHtml;
                    delete button.dataset.originalHtml;
                }
            });
        }

        document.addEventListener('submit', async function (event) {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) return;
            if (!form.classList.contains('js-document-process-form')) return;

            event.preventDefault();

            if (form.dataset.processing === '1') return;

            /*
             * IMPORTANT:
             * Do not use form.action here.
             * The hidden input named "action" can shadow that property.
             */
            const actionUrl = form.getAttribute('action');

            if (!actionUrl || actionUrl.includes('[object')) {
                console.error('Invalid document processing form action:', actionUrl);
                alert('Invalid document processing URL. Please reload the page and try again.');
                return;
            }

            const opensPrintWindow = form.getAttribute('target') === 'docPrintWindow';
            let printWindow = opensPrintWindow ? window.docPrintWindowRef : null;

            form.dataset.processing = '1';

            const submitButtons = setButtonLoading(form);

            /*
             * For print actions, the popup already has its own loading screen.
             * For all other actions, show the full-page overlay.
             */
            if (!opensPrintWindow) {
                showLoading(form);
            }

            /*
             * If the print popup was blocked or closed, use native submission
             * as a fallback. This avoids losing the actual document action.
             */
            if (opensPrintWindow && (!printWindow || printWindow.closed)) {
                form.dataset.processing = '0';
                HTMLFormElement.prototype.submit.call(form);
                return;
            }

            try {
                const response = await fetch(actionUrl, {
                    method: (form.getAttribute('method') || 'POST').toUpperCase(),
                    body: new FormData(form),
                    credentials: 'same-origin',
                    redirect: 'follow',
                    headers: {
                        'Accept': 'text/html',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                /*
                 * Laravel redirects are followed by fetch automatically.
                 * Navigate directly to the final URL instead of displaying
                 * Laravel's intermediate "Redirecting to..." HTML response.
                 */
                if (response.redirected) {
                    if (opensPrintWindow) {
                        if (printWindow && !printWindow.closed) {
                            printWindow.location.replace(response.url);
                        } else {
                            window.location.replace(response.url);
                        }
                    } else {
                        window.location.replace(response.url);
                    }

                    return;
                }

                const html = await response.text();

                if (!response.ok) {
                    throw new Error(
                        'The server returned HTTP ' + response.status +
                        '. Please reload the page and try again.'
                    );
                }

                if (opensPrintWindow) {
                    if (!printWindow || printWindow.closed) {
                        throw new Error('The document preview window was closed.');
                    }

                    printWindow.document.open();
                    printWindow.document.write(html);
                    printWindow.document.close();

                    return;
                }

                /*
                 * Some successful Laravel responses may return the final
                 * page directly without a redirect. Replace the current
                 * document with that response.
                 */
                document.open();
                document.write(html);
                document.close();

            } catch (error) {
                console.error('Document processing failed:', error);

                if (opensPrintWindow && printWindow && !printWindow.closed) {
                    printWindow.close();
                }

                form.dataset.processing = '0';

                hideLoading();
                restoreButtons(submitButtons);

                alert(
                    error && error.message
                        ? error.message
                        : 'Unable to process this document. Please try again.'
                );
            }
        });
    })();
</script>
@endpush