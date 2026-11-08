@extends('layouts.app')

@section('title', 'New Document Request | e-Poblacion')

@push('styles')
<style>
    /* =========================================================
       NEW DOCUMENT REQUEST — watermelon theme
       SEMANTIC COLORS
         Green  = primary action / selected / success
         Blue   = info / guidance
         Amber  = pending / warning
         Red    = errors / restrictions / remove
       ========================================================= */
    .docs {
        --ink: #3a2a2e; --muted: #8a6f75; --paper: #ffffff; --wash: #fdf1f3; --line: #f0d0d6;
        --brand: #237a35; --brand-soft: rgba(35,122,53,.11); --green: #2f9e44; --green-d: #237a35; --sun: #ffc933;
        --info: #1c7ed6; --info-soft: rgba(28,126,214,.12);
        --hover: #fdf1f3; --pending: #b26a00; --pending-bg: #fff1d6; --bad: #c92a3b; --bad-bg: #fde3e3; --good: #237a35;
        font-family: var(--font); color: var(--ink);
    }
    html[data-theme="dark"] .docs {
        --ink: #f6eff1; --muted: #a8b8ad; --paper: #17251d; --wash: #1c2d23; --line: #2b4033;
        --brand: #5fc062; --brand-soft: rgba(95,192,98,.16); --green: #5fc062; --green-d: #8fe29a;
        --info: #6cb2ff; --info-soft: rgba(108,178,255,.15);
        --hover: #1f3126; --pending: #ffc15c; --pending-bg: #3a2c10; --bad: #ff7b87; --bad-bg: #401a1d; --good: #8fe29a;
    }
    .docs * { box-sizing: border-box; }
    .docs [hidden] { display: none !important; }
    .docs .display { letter-spacing: -.02em; }
    .docs a:focus-visible, .docs button:focus-visible, .docs input:focus-visible, .docs textarea:focus-visible, .docs .type input:focus-visible + .box { outline: 3px solid var(--info); outline-offset: 2px; }

    .back { display: inline-flex; gap: .4rem; align-items: center; color: var(--brand); text-decoration: none; font-weight: 700; font-size: .92rem; }
    .back:hover { text-decoration: underline; color: var(--brand); }
    .docs-head { margin: .9rem 0 1.5rem; }
    .docs-head h1 { font-size: clamp(1.7rem, 3.2vw, 2.3rem); font-weight: 800; margin: 0; line-height: 1.1; }
    .docs-head p { margin: .35rem 0 0; color: var(--muted); }
    .note { display: flex; gap: .75rem; padding: .85rem 1rem; border-radius: 1rem; margin-bottom: 1rem; border: 1px solid var(--bad); background: var(--bad-bg); color: var(--bad); }
    .note ul { margin: .35rem 0 0; padding-left: 1.1rem; }

    .layout { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 1.5rem; align-items: start; }
    @media (max-width: 991.98px) { .layout { grid-template-columns: 1fr; } }

    /* ---------- Steps ---------- */
    .step { background: var(--paper); border: 1px solid var(--line); border-radius: 1.4rem; padding: 1.3rem 1.4rem 1.45rem; margin-bottom: 1rem; box-shadow: 0 2px 4px rgba(120,40,55,.04), 0 10px 24px rgba(120,40,55,.05); }
    .step-title { display: flex; align-items: center; gap: .7rem; margin-bottom: .2rem; }
    .step-title .n { width: 1.95rem; height: 1.95rem; border-radius: 50%; display: grid; place-items: center; background: linear-gradient(135deg, #2f9e44, var(--brand)); color: #fff; font-weight: 800; font-size: .9rem; flex: none; box-shadow: 0 4px 10px rgba(35,122,53,.28); }
    html[data-theme="dark"] .step-title .n { color: #0b2314; background: linear-gradient(135deg, #8fe29a, var(--brand)); box-shadow: none; }
    .step-title h2 { font-size: 1.15rem; font-weight: 800; margin: 0; }
    .step-hint { color: var(--muted); font-size: .9rem; margin: 0 0 1rem 2.65rem; }
    .req-mark { color: var(--bad); }
    .err { color: var(--bad); font-size: .88rem; margin-top: .5rem; }

    /* ---------- Resident combobox ---------- */
    .combo { position: relative; }
    .combo .bi-search { position: absolute; left: 1.05rem; top: 1.05rem; color: var(--muted); pointer-events: none; }
    .combo input.q { width: 100%; padding: .85rem 2.6rem; border-radius: 999px; font-size: 1rem; border: 1.5px solid var(--line); background: var(--paper); color: var(--ink); }
    .combo input.q::placeholder { color: var(--muted); }
    .combo input.q:focus { border-color: var(--brand); outline: none; box-shadow: 0 0 0 4px var(--brand-soft); }
    .combo.invalid input.q { border-color: var(--bad); }
    .list, .solo-picker-list { position: absolute; z-index: 40; left: 0; right: 0; top: calc(100% + .35rem); margin: 0; padding: .3rem; list-style: none; max-height: 310px; overflow-y: auto; border-radius: 1.1rem; background: var(--paper); border: 1px solid var(--line); box-shadow: 0 14px 34px rgba(120,40,55,.2); }
    .opt { display: flex; align-items: center; gap: .75rem; padding: .55rem .65rem; border-radius: .8rem; cursor: pointer; }
    .opt[aria-selected="true"], .opt:hover { background: var(--brand-soft); }
    .opt b, .solo-result b { display: block; font-weight: 700; }
    .opt small, .solo-result small { color: var(--muted); }
    .opt mark { background: var(--sun); color: #3a2a2e; border-radius: 3px; padding: 0 1px; }
    .avatar { width: 2.4rem; height: 2.4rem; border-radius: 50%; display: grid; place-items: center; flex: none; font-weight: 800; font-size: .85rem; background: var(--brand-soft); color: var(--brand); }
    .list-empty, .solo-search-empty { padding: 1rem; text-align: center; color: var(--muted); font-size: .88rem; }
    .list-foot { padding: .45rem .65rem; color: var(--muted); font-size: .8rem; text-align: center; }
    .picked { display: flex; align-items: center; gap: .8rem; padding: .75rem .9rem; border-radius: 1.1rem; border: 1.5px solid var(--brand); background: var(--brand-soft); }
    .picked .grow { flex: 1; min-width: 0; }
    .picked b { display: block; }
    .picked small { color: var(--muted); }
    .link-btn { background: none; border: 0; color: var(--brand); font-weight: 700; padding: .25rem .6rem; border-radius: 999px; }
    .link-btn:hover { background: var(--paper); }

    /* ---------- Active case restriction modal (red = restriction) ---------- */
    .case-modal-backdrop {
        position: fixed; inset: 0; z-index: 2000;
        display: flex; align-items: center; justify-content: center;
        padding: 1rem; background: rgba(20, 12, 15, .65);
        overflow-y: auto;
    }
    .case-modal {
        width: min(100%, 620px); max-height: min(85vh, 760px);
        overflow-y: auto; background: var(--paper); color: var(--ink);
        border: 1px solid var(--line); border-radius: 1.35rem;
        box-shadow: 0 24px 70px rgba(0,0,0,.3);
    }
    .case-modal-head {
        display: flex; align-items: flex-start; gap: .85rem;
        padding: 1.2rem 1.3rem; border-bottom: 1px solid var(--line);
    }
    .case-modal-icon {
        width: 2.7rem; height: 2.7rem; flex: none; display: grid;
        place-items: center; border-radius: 50%;
        color: var(--bad); background: var(--bad-bg); font-size: 1.25rem;
    }
    .case-modal-head h3 { margin: 0; font-size: 1.12rem; font-weight: 800; }
    .case-modal-head p { margin: .3rem 0 0; color: var(--muted); font-size: .88rem; line-height: 1.45; }
    .case-modal-body { padding: 1.1rem 1.3rem; }
    .case-policy {
        padding: .85rem 1rem; margin-bottom: 1rem; border-radius: .9rem;
        border: 1px solid var(--bad); background: var(--bad-bg);
        color: var(--bad); font-size: .9rem; line-height: 1.5;
    }
    .case-list { display: grid; gap: .8rem; }
    .case-item {
        padding: .9rem 1rem; border: 1px solid var(--line);
        border-radius: 1rem; background: var(--wash);
    }
    .case-item-top {
        display: flex; justify-content: space-between; align-items: flex-start;
        flex-wrap: wrap; gap: .5rem; margin-bottom: .55rem;
    }
    .case-number { font-weight: 800; overflow-wrap: anywhere; }
    .case-status {
        display: inline-flex; align-items: center; padding: .25rem .65rem;
        border-radius: 999px; color: var(--pending); background: var(--pending-bg);
        font-size: .78rem; font-weight: 800;
    }
    .case-item h4 { margin: 0 0 .35rem; font-size: .96rem; font-weight: 800; }
    .case-description {
        margin: 0; color: var(--muted); font-size: .87rem;
        line-height: 1.5; white-space: pre-line; overflow-wrap: anywhere;
    }
    .case-date { display: block; margin-top: .65rem; color: var(--muted); font-size: .78rem; }
    .case-modal-foot {
        display: flex; justify-content: flex-end; padding: 1rem 1.3rem;
        border-top: 1px solid var(--line);
    }
    .case-modal-close {
        border: 0; border-radius: 999px; padding: .7rem 1.3rem;
        background: var(--bad); color: #fff; font-weight: 800;
    }
    html[data-theme="dark"] .case-modal-close { color: #2b0c12; }
    .case-modal-close:hover { filter: brightness(1.06); }

    /* ---------- Document type cards ---------- */
    .types { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .75rem; }
    @media (max-width: 575.98px) { .types { grid-template-columns: 1fr; } }
    .type { position: relative; display: block; cursor: pointer; }
    .type input { position: absolute; opacity: 0; inset: 0; }
    .type .box { display: flex; gap: .8rem; align-items: flex-start; height: 100%; padding: .95rem 1rem; border-radius: 1.1rem; border: 1.5px solid var(--line); background: var(--paper); transition: border-color .15s, background .15s, transform .15s; }
    .type:hover .box { border-color: var(--brand); transform: translateY(-1px); }
    .type i.ic { font-size: 1.35rem; color: var(--brand); }
    .type b { display: block; font-weight: 700; }
    .type small { color: var(--muted); }
    .type input:checked + .box { border-color: var(--brand); background: var(--brand-soft); box-shadow: inset 0 0 0 1px var(--brand); }
    .type .tick { margin-left: auto; color: var(--brand); opacity: 0; }
    .type input:checked + .box .tick { opacity: 1; }

    /* ---------- Fields ---------- */
    .field { margin-bottom: 1.2rem; }
    .field:last-child { margin-bottom: 0; }
    .field > label { display: flex; align-items: center; gap: .45rem; font-weight: 700; margin-bottom: .45rem; }
    .field > label > i { color: var(--brand); }
    .field > label span { color: var(--muted); font-weight: 500; }
    .field textarea, .field input[type="text"], .field input[type="date"], .field input[type="number"] { width: 100%; padding: .75rem 1rem; border-radius: 1.1rem; font: inherit; border: 1.5px solid var(--line); background: var(--paper); color: var(--ink); transition: border-color .15s, box-shadow .15s; }
    .field textarea { resize: vertical; }
    .field textarea::placeholder, .field input::placeholder { color: var(--muted); }
    .field textarea:focus, .field input[type="text"]:focus, .field input[type="date"]:focus, .field input[type="number"]:focus { border-color: var(--brand); outline: none; box-shadow: 0 0 0 4px var(--brand-soft); }
    .field textarea.bad, .field input.bad { border-color: var(--bad); }

    /* ---------- Purpose (modern) ---------- */
    .purpose-box { border: 1.5px solid var(--line); border-radius: 1.3rem; background: var(--paper); transition: border-color .15s, box-shadow .15s; overflow: hidden; }
    .purpose-box:focus-within { border-color: var(--brand); box-shadow: 0 0 0 4px var(--brand-soft); }
    .purpose-box textarea { display: block; width: 100%; border: 0 !important; box-shadow: none !important; border-radius: 0 !important; background: transparent !important; padding: .95rem 1.1rem .4rem; resize: none; min-height: 5.2rem; }
    .purpose-box textarea:focus { outline: none; }
    .purpose-foot { display: flex; justify-content: space-between; align-items: center; padding: .25rem 1.1rem .7rem; color: var(--muted); font-size: .8rem; }
    .purpose-foot b { color: var(--ink); font-weight: 700; }
    .chips { display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; margin-top: .75rem; }
    .chips-label { display: inline-flex; align-items: center; gap: .35rem; color: var(--muted); font-size: .82rem; font-weight: 700; margin-right: .15rem; }
    .chips-label i { color: var(--sun); }
    .chip { display: inline-flex; align-items: center; gap: .35rem; padding: .4rem .9rem; border-radius: 999px; border: 1.5px solid var(--line); background: var(--paper); color: var(--ink); font: 700 .85rem var(--font); cursor: pointer; transition: border-color .15s, background .15s, color .15s, transform .15s; }
    .chip i { font-size: .8rem; color: var(--muted); }
    .chip:hover { border-color: var(--brand); color: var(--brand); transform: translateY(-1px); }
    .chip.on { background: var(--brand-soft); border-color: var(--brand); color: var(--brand); }
    .chip.on i { color: var(--brand); }
    .chip.on i::before { content: "\f26e"; }

    /* ---------- Document-specific panels ---------- */
    .panel { margin: 0 0 1.2rem; padding: 1.05rem; border: 1px solid var(--line); border-radius: 1.2rem; background: var(--wash); }
    .panel-head { display: flex; align-items: flex-start; gap: .7rem; margin-bottom: 1rem; }
    .panel-head > i { color: var(--info); font-size: 1.2rem; margin-top: .1rem; }
    .panel-head b { display: block; font-weight: 800; }
    .panel-head small { display: block; margin-top: .15rem; color: var(--muted); line-height: 1.45; }
    .detail-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
    @media (max-width: 767.98px) { .detail-grid { grid-template-columns: 1fr; } }
    .detail-field { margin: 0 !important; }
    .detail-field.full { grid-column: 1 / -1; }
    .helper { display: block; margin-top: .35rem; color: var(--muted); font-size: .8rem; line-height: 1.4; }
    .required { color: var(--bad); }
    .solo-parent-note { display: flex; gap: .7rem; align-items: flex-start; margin-bottom: 1rem; padding: .8rem .9rem; border-radius: 1rem; background: var(--info-soft); font-size: .86rem; line-height: 1.45; }
    .solo-parent-note i { color: var(--info); font-size: 1.1rem; margin-top: .1rem; }
    .solo-parent-note p { margin: 0; }

    /* ---------- Solo Parent picker ---------- */
    .solo-picker { position: relative; }
    .solo-picker-search { position: relative; }
    .solo-picker-search .bi-search { position: absolute; left: 1.05rem; top: 1rem; color: var(--muted); pointer-events: none; }
    .solo-picker-search input { padding-left: 2.6rem !important; border-radius: 999px !important; }
    .solo-result { display: flex; align-items: center; gap: .7rem; padding: .65rem; border-radius: .8rem; }
    .solo-result:hover { background: var(--hover); }
    .solo-result .result-main { flex: 1; min-width: 0; }
    .solo-result .result-status { color: var(--muted); font-size: .78rem; }
    .solo-add-btn { flex: none; display: inline-flex; align-items: center; gap: .3rem; border: 0; background: var(--brand); color: #fff; border-radius: 999px; padding: .4rem .85rem; font-size: .82rem; font-weight: 700; }
    html[data-theme="dark"] .solo-add-btn { color: #0b2314; }
    .solo-add-btn:hover { filter: brightness(1.1); }
    .solo-result-added { color: var(--good); font-size: .8rem; font-weight: 700; white-space: nowrap; }
    .solo-household-warning { display: none; margin-top: .55rem; padding: .65rem .8rem; border-radius: .9rem; background: var(--bad-bg); color: var(--bad); font-size: .8rem; line-height: 1.4; }
    .solo-household-warning.show { display: block; }
    .selected-members { margin-top: .25rem; }
    .selected-members-head { display: flex; justify-content: space-between; align-items: center; gap: .75rem; margin-bottom: .6rem; }
    .selected-members-head strong { font-size: .9rem; }
    .selected-count { display: inline-flex; align-items: center; justify-content: center; min-width: 1.6rem; height: 1.6rem; padding: 0 .45rem; border-radius: 999px; background: var(--brand-soft); color: var(--brand); font-size: .78rem; font-weight: 800; }
    .selected-members-list { display: grid; gap: .6rem; }
    .selected-members-empty { padding: .9rem; border: 1px dashed var(--line); border-radius: 1rem; color: var(--muted); text-align: center; font-size: .84rem; background: var(--paper); }
    .selected-member { display: flex; align-items: center; gap: .7rem; padding: .7rem .8rem; border: 1px solid var(--line); border-radius: 1rem; background: var(--paper); }
    .selected-member .avatar { width: 2.25rem; height: 2.25rem; }
    .selected-member-main { flex: 1; min-width: 0; }
    .selected-member-name { display: block; font-weight: 700; word-break: break-word; }
    .selected-member-meta { display: flex; flex-wrap: wrap; gap: .35rem .8rem; margin-top: .15rem; color: var(--muted); font-size: .78rem; }
    .selected-member-meta span { display: inline-flex; align-items: center; gap: .25rem; }
    .remove-member { flex: none; border: 1px solid var(--line); background: transparent; color: var(--bad); border-radius: 50%; width: 2rem; height: 2rem; display: grid; place-items: center; }
    .remove-member:hover { background: var(--bad-bg); border-color: var(--bad); }
    .selected-members-error { margin-top: .55rem; color: var(--bad); font-size: .84rem; }

    /* ---------- Summary ---------- */
    .side { position: sticky; top: 1rem; }
    @media (max-width: 991.98px) { .side { position: static; } }
    .summary { background: var(--paper); border: 1px solid var(--line); border-radius: 1.4rem; overflow: hidden; margin-bottom: 1rem; box-shadow: 0 2px 4px rgba(120,40,55,.04), 0 10px 24px rgba(120,40,55,.05); }
    .summary-head { padding: .95rem 1.25rem; border-bottom: 1px solid var(--line); font-weight: 800; font-size: 1.05rem; color: var(--brand); }
    .summary dl { margin: 0; padding: 1rem 1.25rem; display: grid; gap: .9rem; }
    .summary dt { color: var(--muted); font-size: .82rem; font-weight: 600; }
    .summary dd { margin: .1rem 0 0; font-weight: 700; }
    .summary dd.empty { color: var(--muted); font-weight: 500; }
    .pill { display: inline-flex; align-items: center; gap: .4rem; padding: .28rem .75rem; border-radius: 999px; font-size: .82rem; font-weight: 700; color: var(--pending); background: var(--pending-bg); }
    .pill::before { content: ''; width: .5rem; height: .5rem; border-radius: 50%; background: var(--pending); }
    .summary-note { padding: 0 1.25rem 1rem; color: var(--muted); font-size: .85rem; margin: 0; }
    .actions { padding: 1rem 1.25rem; border-top: 1px solid var(--line); display: grid; gap: .6rem; }
    .btn-go { display: inline-flex; justify-content: center; align-items: center; gap: .5rem; border: 0; border-radius: 999px; padding: .85rem 1.2rem; font-weight: 800; background: linear-gradient(135deg, #2f9e44, var(--brand)); color: #fff; box-shadow: 0 8px 18px rgba(35,122,53,.3); transition: transform .15s, filter .15s; }
    html[data-theme="dark"] .btn-go { color: #0b2314; background: linear-gradient(135deg, #8fe29a, var(--brand)); box-shadow: none; }
    .btn-go:hover:not(:disabled) { transform: translateY(-1px); filter: brightness(1.05); }
    .btn-go:disabled { background: var(--wash); color: var(--muted); box-shadow: none; cursor: not-allowed; }
    .btn-cancel { text-align: center; padding: .6rem; border-radius: 999px; color: var(--muted); text-decoration: none; font-weight: 700; }
    .btn-cancel:hover { color: var(--ink); background: var(--wash); }
    .missing { color: var(--muted); font-size: .82rem; text-align: center; margin: 0; }
    .verify { display: flex; gap: .8rem; padding: 1rem 1.25rem; border-radius: 1.3rem; background: var(--info-soft); border: 1px dashed var(--info); font-size: .88rem; color: var(--muted); }
    .verify i { font-size: 1.3rem; color: var(--info); }
    .verify b { display: block; color: var(--ink); margin-bottom: .25rem; }
    .verify p { margin: 0; }
    @media (prefers-reduced-motion: reduce) { .docs * { transition: none !important; } }
</style>
@endpush


@section('content')

@php
    $residentData = $residents->map(fn ($r) => [
        'id'                 => $r->id,
        'name'               => (string) $r->full_name,
        'address'            => (string) ($r->address ?? ''),
        'sex'                => (string) ($r->sex ?? ''),
        'birth_date'         => $r->birth_date ? $r->birth_date->format('Y-m-d') : '',
        'birth_date_display' => $r->birth_date ? $r->birth_date->format('F j, Y') : '',
    ])->values();

    $types = [
        'Barangay Clearance'           => ['bi-shield-check',       'Proof of good standing in the barangay'],
        'Certificate of Residency'     => ['bi-house-door',         'Confirms the resident’s place of residence'],
        'Certificate of Indigency'     => ['bi-heart',              'For financial assistance, scholarships, or fee waivers'],
        'Certificate of Late Register' => ['bi-file-earmark-text',  'For certification related to late registration'],
        'Cutting Trees'                => ['bi-tree',               'Certificate related to tree cutting authorization'],
        'First Time Job Seeker'        => ['bi-briefcase',          'For qualified first-time job seekers'],
        'Oath of Undertaking'          => ['bi-pen',                'Official barangay oath or undertaking document'],
        'Solo Parent'                  => ['bi-person-heart',       'Certificate related to solo parent status'],
    ];

    $purposeChips = ['Employment', 'Scholarship', 'Travel', 'ID application', 'Business'];
@endphp


<div class="docs">
<div class="container-fluid py-4">

    <a href="{{ route('documents.index') }}" class="back">
        <i class="bi bi-arrow-left"></i> Back to Barangay Documents
    </a>

    <div class="docs-head">
        <h1 class="display">New document request</h1>
        <p>File a request for a resident who is already registered.</p>
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

    {{-- Active respondent case restriction popup --}}
    <div class="case-modal-backdrop" id="activeCaseModal" role="dialog"
         aria-modal="true" aria-labelledby="activeCaseModalTitle"
         aria-describedby="activeCaseModalDescription" hidden>
        <div class="case-modal">
            <div class="case-modal-head">
                <span class="case-modal-icon"><i class="bi bi-shield-exclamation"></i></span>
                <div>
                    <h3 id="activeCaseModalTitle">Document request restricted</h3>
                    <p id="activeCaseModalDescription">
                        This resident has one or more active cases recorded as a respondent.
                    </p>
                </div>
            </div>
            <div class="case-modal-body">
                <div class="case-policy">
                    <strong>Request cannot proceed.</strong>
                    A resident with an active or unresolved case as a respondent cannot request or obtain barangay documents under this policy until all applicable cases are resolved or closed.
                </div>
                <div class="case-list" id="activeCaseList"></div>
            </div>
            <div class="case-modal-foot">
                <button type="button" class="case-modal-close" id="closeActiveCaseModal">
                    <i class="bi bi-check-lg"></i> I Understand
                </button>
            </div>
        </div>
    </div>


    <form method="POST" action="{{ route('documents.store') }}" id="requestForm" novalidate>
        @csrf

        <div class="layout">

            {{-- ===================== LEFT ===================== --}}
            <div>

                {{-- STEP 1 · RESIDENT --}}
                <section class="step">
                    <div class="step-title">
                        <span class="n">1</span>
                        <h2 class="display">Who is this for? <span class="req-mark">*</span></h2>
                    </div>
                    <p class="step-hint">Type a name or address. Matches appear as you type.</p>

                    <div class="combo @error('resident_id') invalid @enderror" id="combo">
                        <input type="hidden" name="resident_id" id="resident_id" value="{{ old('resident_id') }}">

                        <div class="picked" id="picked" hidden>
                            <span class="avatar" id="pickedAvatar" aria-hidden="true"></span>
                            <div class="grow">
                                <b id="pickedName"></b>
                                <small id="pickedAddr"></small>
                            </div>
                            <button type="button" class="link-btn" id="change">Change</button>
                        </div>

                        <div id="searchWrap">
                            <i class="bi bi-search"></i>
                            <label for="resident_search" class="visually-hidden">Search residents</label>
                            <input type="text" class="q" id="resident_search" autocomplete="off"
                                   placeholder="Start typing a resident's name…"
                                   role="combobox" aria-expanded="false" aria-controls="resident_list" aria-autocomplete="list">
                            <ul class="list" id="resident_list" role="listbox" hidden></ul>
                        </div>
                    </div>

                    @error('resident_id') <div class="err">{{ $message }}</div> @enderror
                </section>


                {{-- STEP 2 · DOCUMENT TYPE --}}
                <section class="step">
                    <div class="step-title">
                        <span class="n">2</span>
                        <h2 class="display">Which document? <span class="req-mark">*</span></h2>
                    </div>
                    <p class="step-hint">Pick one.</p>

                    <div class="types" role="radiogroup" aria-label="Document type">
                        @foreach ($types as $value => [$icon, $desc])
                            <label class="type">
                                <input type="radio" name="document_type" value="{{ $value }}" {{ old('document_type') === $value ? 'checked' : '' }}>
                                <span class="box">
                                    <i class="bi {{ $icon }} ic"></i>
                                    <span>
                                        <b>{{ $value }}</b>
                                        <small>{{ $desc }}</small>
                                    </span>
                                    <i class="bi bi-check-circle-fill tick"></i>
                                </span>
                            </label>
                        @endforeach
                    </div>

                    @error('document_type') <div class="err">{{ $message }}</div> @enderror
                </section>


                {{-- STEP 3 · DETAILS --}}
                <section class="step">
                    <div class="step-title">
                        <span class="n">3</span>
                        <h2 class="display">Details</h2>
                    </div>
                    <p class="step-hint">Optional, but a clear purpose helps staff process the request faster.</p>

                    {{-- PURPOSE --}}
                    <div class="field">
                        <label for="purpose"><i class="bi bi-chat-left-text"></i> Purpose <span>(optional)</span></label>

                        <div class="purpose-box">
                            <textarea name="purpose" id="purpose" rows="3" maxlength="1000"
                                      class="@error('purpose') bad @enderror"
                                      placeholder="Why does the resident need this document?">{{ old('purpose') }}</textarea>
                            <div class="purpose-foot">
                                <span>Pick a quick fill below or write your own.</span>
                                <span><b id="purposeCount">0</b>/1000</span>
                            </div>
                        </div>

                        <div class="chips" id="purposeChips">
                            <span class="chips-label"><i class="bi bi-lightning-charge-fill"></i> Quick fill</span>
                            @foreach ($purposeChips as $p)
                                <button type="button" class="chip" data-purpose="{{ $p }}"><i class="bi bi-plus-lg"></i>{{ $p }}</button>
                            @endforeach
                        </div>

                        @error('purpose') <div class="err">{{ $message }}</div> @enderror
                    </div>


                    {{-- CTC NUMBER --}}
                    <div class="panel" id="ctcDetails" data-docs="Barangay Clearance" hidden>
                        <div class="panel-head">
                            <i class="bi bi-person-vcard"></i>
                            <div>
                                <b>Community Tax Certificate (CTC)</b>
                                <small>The CTC number you enter here is printed on the certificate automatically, so nobody has to write it by hand.</small>
                            </div>
                        </div>

                        <div class="field detail-field">
                            <label for="detail_ctc_number">CTC Number <span>(optional)</span></label>
                            <input type="text" name="document_details[ctc_number]" id="detail_ctc_number"
                                   value="{{ old('document_details.ctc_number') }}" maxlength="50"
                                   placeholder="Example: 12345678" disabled>
                            <small class="helper">Leave blank if the resident has no CTC yet. The line on the certificate will stay empty.</small>
                            @error('document_details.ctc_number') <div class="err">{{ $message }}</div> @enderror
                        </div>
                    </div>


                    {{-- LATE REGISTER --}}
                    <div class="panel" id="lateRegisterDetails" data-docs="Certificate of Late Register" hidden>
                        <div class="panel-head">
                            <i class="bi bi-file-earmark-text"></i>
                            <div>
                                <b>Certificate of Late Register details</b>
                                <small>Enter the information that belongs specifically to this resident's late registration record.</small>
                            </div>
                        </div>

                        <div class="detail-grid">
                            <div class="field detail-field full">
                                <label for="detail_birthplace">Place of Birth <span class="required">*</span></label>
                                <input type="text" name="document_details[birthplace]" id="detail_birthplace"
                                       value="{{ old('document_details.birthplace') }}" maxlength="1000"
                                       placeholder="Example: Candon, Ilocos Sur" disabled>
                                <small class="helper">Enter the resident's actual place of birth.</small>
                                @error('document_details.birthplace') <div class="err">{{ $message }}</div> @enderror
                            </div>

                            <div class="field detail-field">
                                <label for="detail_father_name">Father's Name <span class="required">*</span></label>
                                <input type="text" name="document_details[father_name]" id="detail_father_name"
                                       value="{{ old('document_details.father_name') }}" maxlength="1000"
                                       placeholder="Enter father's full name" disabled>
                                <small class="helper">Use the name shown in the resident's supporting record.</small>
                                @error('document_details.father_name') <div class="err">{{ $message }}</div> @enderror
                            </div>

                            <div class="field detail-field">
                                <label for="detail_mother_name">Mother's Name <span class="required">*</span></label>
                                <input type="text" name="document_details[mother_name]" id="detail_mother_name"
                                       value="{{ old('document_details.mother_name') }}" maxlength="1000"
                                       placeholder="Enter mother's full name" disabled>
                                <small class="helper">Use the name shown in the resident's supporting record.</small>
                                @error('document_details.mother_name') <div class="err">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>


                    {{-- CUTTING TREES --}}
                    <div class="panel" id="cuttingTreesDetails" data-docs="Cutting Trees" hidden>
                        <div class="panel-head">
                            <i class="bi bi-tree-fill"></i>
                            <div>
                                <b>Cutting Trees Permit details</b>
                                <small>Enter the permit and tree information exactly as stated in the supporting permit. Location context fields are free-text for the Captain or Secretary.</small>
                            </div>
                        </div>

                        <div class="detail-grid">
                            <div class="field detail-field">
                                <label for="detail_permit_to_cut_no">Permit to Cut No. <span class="required">*</span></label>
                                <input type="text" name="document_details[permit_to_cut_no]" id="detail_permit_to_cut_no" value="{{ old('document_details.permit_to_cut_no') }}" maxlength="100" placeholder="Example: 003204" disabled>
                                @error('document_details.permit_to_cut_no') <div class="err">{{ $message }}</div> @enderror
                            </div>

                            <div class="field detail-field">
                                <label for="detail_permit_to_cut_date">Permit to Cut Date <span class="required">*</span></label>
                                <input type="date" name="document_details[permit_to_cut_date]" id="detail_permit_to_cut_date" value="{{ old('document_details.permit_to_cut_date') }}" disabled>
                                @error('document_details.permit_to_cut_date') <div class="err">{{ $message }}</div> @enderror
                            </div>

                            <div class="field detail-field full">
                                <label for="detail_permit_issued_by">Permit Issued By <span class="required">*</span></label>
                                <input type="text" name="document_details[permit_issued_by]" id="detail_permit_issued_by" value="{{ old('document_details.permit_issued_by', 'DENR') }}" maxlength="255" placeholder="Example: DENR" disabled>
                                <small class="helper">Enter the issuing office shown on the permit.</small>
                                @error('document_details.permit_issued_by') <div class="err">{{ $message }}</div> @enderror
                            </div>

                            <div class="field detail-field">
                                <label for="detail_tree_count">Number of Trees <span class="required">*</span></label>
                                <input type="number" name="document_details[tree_count]" id="detail_tree_count" value="{{ old('document_details.tree_count', 1) }}" min="1" step="1" disabled>
                                @error('document_details.tree_count') <div class="err">{{ $message }}</div> @enderror
                            </div>

                            <div class="field detail-field">
                                <label for="detail_tree_type">Type of Tree / Trees <span class="required">*</span></label>
                                <input type="text" name="document_details[tree_type]" id="detail_tree_type" value="{{ old('document_details.tree_type') }}" maxlength="1000" placeholder="Example: Acacia Tree" disabled>
                                @error('document_details.tree_type') <div class="err">{{ $message }}</div> @enderror
                            </div>

                            <div class="field detail-field full">
                                <label for="detail_tree_location">Location <span class="required">*</span></label>
                                <input type="text" name="document_details[location]" id="detail_tree_location" value="{{ old('document_details.location') }}" maxlength="1000" placeholder="Example: Zone 7 Barangay Poblacion, Bani, Pangasinan" disabled>
                                @error('document_details.location') <div class="err">{{ $message }}</div> @enderror
                            </div>

                            <div class="field detail-field full">
                                <label for="detail_location_description">Location Description / Landmark <span class="required">*</span></label>
                                <input type="text" name="document_details[location_description]" id="detail_location_description" value="{{ old('document_details.location_description') }}" maxlength="1000" placeholder="Example: Along the barangay road near the covered court" disabled>
                                <small class="helper">Describe the exact location, landmark, or surrounding area. Do not assume the tree is inside a private compound.</small>
                                @error('document_details.location_description') <div class="err">{{ $message }}</div> @enderror
                            </div>

                            <div class="field detail-field">
                                <label for="detail_tree_owner">Tree Owner / Property Owner</label>
                                <input type="text" name="document_details[tree_owner]" id="detail_tree_owner" value="{{ old('document_details.tree_owner') }}" maxlength="1000" placeholder="Optional" disabled>
                                <small class="helper">Enter the owner only when applicable or stated in the supporting permit.</small>
                                @error('document_details.tree_owner') <div class="err">{{ $message }}</div> @enderror
                            </div>

                            <div class="field detail-field">
                                <label for="detail_cutter_name">Cutter Man <span class="required">*</span></label>
                                <input type="text" name="document_details[cutter_name]" id="detail_cutter_name" value="{{ old('document_details.cutter_name') }}" maxlength="1000" placeholder="Example: John Carl Devera" disabled>
                                @error('document_details.cutter_name') <div class="err">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>


                    {{-- SOLO PARENT --}}
                    <div class="panel" id="soloParentDetails" data-docs="Solo Parent" hidden>
                        <div class="panel-head">
                            <i class="bi bi-person-heart"></i>
                            <div>
                                <b>Solo Parent Certificate details</b>
                                <small>Search registered residents and add the child or children/dependents who belong to the selected Solo Parent's registered household.</small>
                            </div>
                        </div>

                        <div class="solo-parent-note">
                            <i class="bi bi-database-check"></i>
                            <p>
                                <strong>Automatic resident information:</strong>
                                Search the Residents record, then add the appropriate child or dependent. The system automatically displays the registered name, sex, and birth date. Multiple children can be added.
                            </p>
                        </div>

                        <div class="detail-grid">
                            <div class="field detail-field full">
                                <label for="solo_parent_resident_search">Child / Family Member <span class="required">*</span></label>

                                <div class="solo-picker">
                                    <div class="solo-picker-search">
                                        <i class="bi bi-search"></i>
                                        <input type="text" id="solo_parent_resident_search" autocomplete="off"
                                               placeholder="Search registered resident by name…"
                                               aria-expanded="false" aria-controls="solo_parent_resident_list">
                                    </div>
                                    <ul class="solo-picker-list" id="solo_parent_resident_list" hidden></ul>
                                </div>

                                <div class="solo-household-warning" id="soloHouseholdWarning"></div>

                                <small class="helper">Search all registered residents. A resident can only be added when the system confirms that the person belongs to the selected Solo Parent's registered household.</small>

                                @error('document_details.child_resident_ids') <div class="err">{{ $message }}</div> @enderror
                                @error('document_details.child_resident_ids.*') <div class="err">{{ $message }}</div> @enderror
                            </div>

                            <div class="field detail-field full">
                                <div class="selected-members">
                                    <div class="selected-members-head">
                                        <strong>Selected Children / Family Members</strong>
                                        <span class="selected-count" id="selectedMemberCount">0</span>
                                    </div>

                                    <div class="selected-members-list" id="selectedMembersList">
                                        <div class="selected-members-empty" id="selectedMembersEmpty">No child or family member has been added yet.</div>
                                    </div>

                                    <div class="selected-members-error" id="selectedMembersError" hidden>Please add at least one registered child or family member.</div>
                                </div>
                            </div>
                        </div>
                    </div>


                    {{-- REMARKS --}}
                    <div class="field">
                        <label for="remarks"><i class="bi bi-sticky"></i> Remarks <span>(optional)</span></label>
                        <textarea name="remarks" id="remarks" rows="3" maxlength="1000"
                                  class="@error('remarks') bad @enderror"
                                  placeholder="Notes for the staff, if any">{{ old('remarks') }}</textarea>
                        @error('remarks') <div class="err">{{ $message }}</div> @enderror
                    </div>
                </section>
            </div>


            {{-- ===================== RIGHT SUMMARY ===================== --}}
            <aside class="side">
                <div class="summary">
                    <div class="summary-head display">Request summary</div>

                    <dl>
                        <div>
                            <dt>Resident</dt>
                            <dd id="sumResident" class="empty">Not chosen yet</dd>
                        </div>
                        <div>
                            <dt>Document</dt>
                            <dd id="sumDoc" class="empty">Not chosen yet</dd>
                        </div>
                        <div>
                            <dt>Starting status</dt>
                            <dd><span class="pill">Pending</span></dd>
                        </div>
                    </dl>

                    <p class="summary-note">Staff update the status after reviewing the request.</p>

                    <div class="actions">
                        <button type="submit" class="btn-go" id="submitBtn" disabled>
                            <i class="bi bi-check-circle"></i> Create request
                        </button>
                        <p class="missing" id="missing">Choose a resident and a document to continue.</p>
                        <a href="{{ route('documents.index') }}" class="btn-cancel">Cancel</a>
                    </div>
                </div>

                <div class="verify">
                    <i class="bi bi-person-vcard"></i>
                    <div>
                        <b>Check the ID in person</b>
                        <p>Staff verify the resident's physical ID at the office. IDs are not uploaded or stored in this request.</p>
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
(function () {
    'use strict';

    /* =========================================================
       DATA FROM LARAVEL
       ========================================================= */
    const residents = @json($residentData);
    const householdMap = @json($soloParentHouseholdMembers ?? []);
    const oldChildIds = @json(old('document_details.child_resident_ids', []));
    const oldSingleChild = @json(old('document_details.child_resident_id'));

    // Keys are resident IDs; values are active cases where the resident is respondent.
    const activeRespondentCases = @json(
        $activeRespondentCasesByResident ?? [],
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    );

    const $ = (id) => document.getElementById(id);

    const form = $('requestForm');
    const hidden = $('resident_id');
    const input = $('resident_search');
    const list = $('resident_list');
    const picked = $('picked');
    const searchWrap = $('searchWrap');

    const caseModal = $('activeCaseModal');
    const caseList = $('activeCaseList');
    const closeCaseModalButton = $('closeActiveCaseModal');

    const checkedType = () =>
        (document.querySelector('input[name="document_type"]:checked') || {}).value || '';

    let matches = [];
    let active = -1;
    let selectedSolo = [];
    let caseModalPreviousFocus = null;

    /* =========================================================
       HELPERS
       ========================================================= */
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    const initials = (n) => (String(n ?? '').trim().split(/\s+/).filter(Boolean)
        .map((w, i, a) => (i === 0 || i === a.length - 1) ? w[0] : '').join('') || '?').toUpperCase();

    const words = (s) => String(s ?? '').toLowerCase().split(/[\s,.\-\/]+/).filter(Boolean);

    const normalizeSex = (sex) => {
        const v = String(sex ?? '').trim().toLowerCase();
        if (v === 'male') return 'Male';
        if (v === 'female') return 'Female';
        return v ? v.charAt(0).toUpperCase() + v.slice(1) : 'Sex not recorded';
    };

    const highlight = (text, tokens) => {
        let out = esc(text);
        tokens.forEach((t) => {
            const re = new RegExp('(^|[\\s,.\\-\\/])(' + t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
            out = out.replace(re, '$1<mark>$2</mark>');
        });
        return out;
    };

    function getActiveCases(residentId) {
        const cases = activeRespondentCases[String(residentId)] || [];
        return Array.isArray(cases) ? cases : [];
    }

    function clearResidentSelection() {
        hidden.value = '';
        picked.hidden = true;
        searchWrap.hidden = false;
        $('pickedName').textContent = '';
        $('pickedAddr').textContent = '';
        $('pickedAvatar').textContent = '';
        $('combo').classList.remove('invalid');
        resetSolo();
        updateSummary();
    }

    function showActiveCaseModal(resident, cases) {
        caseModalPreviousFocus = document.activeElement;

        caseList.innerHTML = cases.map((item) => {
            const caseNumber = item.case_number || 'Case number not recorded';
            const title = item.title || 'Untitled case';
            const description = item.description || 'No case description recorded.';
            const status = item.status || 'Status not recorded';
            const dateFiled = item.date_filed || 'Filing date not recorded';

            return `
                <article class="case-item">
                    <div class="case-item-top">
                        <span class="case-number">${esc(caseNumber)}</span>
                        <span class="case-status">${esc(status)}</span>
                    </div>
                    <h4>${esc(title)}</h4>
                    <p class="case-description">${esc(description)}</p>
                    <small class="case-date"><i class="bi bi-calendar3"></i> Date filed: ${esc(dateFiled)}</small>
                </article>
            `;
        }).join('');

        $('activeCaseModalDescription').textContent =
            (resident && resident.name ? resident.name : 'This resident') +
            ' has ' + cases.length + ' active or unresolved case' +
            (cases.length === 1 ? '' : 's') + ' recorded as a respondent.';

        caseModal.hidden = false;
        document.body.style.overflow = 'hidden';
        closeCaseModalButton.focus();
    }

    function closeActiveCaseModal() {
        caseModal.hidden = true;
        document.body.style.overflow = '';

        if (caseModalPreviousFocus && typeof caseModalPreviousFocus.focus === 'function') {
            caseModalPreviousFocus.focus();
        } else {
            input.focus();
        }
    }

    closeCaseModalButton.addEventListener('click', closeActiveCaseModal);

    caseModal.addEventListener('click', (e) => {
        if (e.target === caseModal) closeActiveCaseModal();
    });

    document.addEventListener('keydown', (e) => {
        if (!caseModal.hidden && e.key === 'Escape') {
            e.preventDefault();
            closeActiveCaseModal();
        }
    });

    /* =========================================================
       STEP 1 · RESIDENT SEARCH
       ========================================================= */
    function filterResidents(q) {
        const tokens = words(q);

        if (!tokens.length) {
            return { tokens, rows: residents.slice(0, 50), total: residents.length };
        }

        const rows = residents.filter((r) => {
            const w = words(r.name + ' ' + r.address);
            return tokens.every((t) => w.some((x) => x.startsWith(t)));
        });

        const first = q.trim().toLowerCase();
        rows.sort((a, b) =>
            (b.name.toLowerCase().startsWith(first) ? 1 : 0) - (a.name.toLowerCase().startsWith(first) ? 1 : 0));

        return { tokens, rows: rows.slice(0, 50), total: rows.length };
    }

    function openList(state) {
        list.hidden = !state;
        input.setAttribute('aria-expanded', String(state));
    }

    function renderList() {
        const { tokens, rows, total } = filterResidents(input.value);
        matches = rows;
        active = rows.length ? 0 : -1;

        if (!rows.length) {
            list.innerHTML = '<li class="list-empty">No resident found for “' + esc(input.value.trim()) +
                '”. Check the spelling or try the address.</li>';
        } else {
            list.innerHTML = rows.map((r, i) =>
                '<li class="opt" role="option" id="opt' + i + '" data-i="' + i + '" aria-selected="' + (i === 0) + '">' +
                    '<span class="avatar" aria-hidden="true">' + esc(initials(r.name)) + '</span>' +
                    '<span><b>' + highlight(r.name, tokens) + '</b>' +
                    (r.address ? '<small>' + highlight(r.address, tokens) + '</small>' : '') +
                    '</span></li>'
            ).join('') + (total > rows.length
                ? '<li class="list-foot">Showing ' + rows.length + ' of ' + total + '. Keep typing to narrow it down.</li>'
                : '');
        }
        openList(true);
    }

    function setActive(i) {
        const opts = list.querySelectorAll('.opt');
        if (!opts.length) return;
        active = (i + opts.length) % opts.length;
        opts.forEach((o, k) => o.setAttribute('aria-selected', String(k === active)));
        opts[active].scrollIntoView({ block: 'nearest' });
        input.setAttribute('aria-activedescendant', opts[active].id);
    }

    function chooseResident(r) {
        if (!r) return;

        // Always check the respondent-case restriction before selecting the resident.
        const activeCases = getActiveCases(r.id);

        if (activeCases.length > 0) {
            // Clear any previously selected resident to prevent submitting under the wrong name.
            clearResidentSelection();
            input.value = '';
            openList(false);
            showActiveCaseModal(r, activeCases);
            return;
        }

        hidden.value = String(r.id);
        $('pickedName').textContent = r.name;
        $('pickedAddr').textContent = r.address || 'No address on file';
        $('pickedAvatar').textContent = initials(r.name);
        picked.hidden = false;
        searchWrap.hidden = true;
        $('combo').classList.remove('invalid');
        openList(false);

        // A different parent means a different household list.
        resetSolo();
        updateSummary();
    }

    function clearResident() {
        clearResidentSelection();
        input.value = '';
        input.focus();
        renderList();
    }

    input.addEventListener('input', renderList);
    input.addEventListener('focus', renderList);
    input.addEventListener('blur', () => setTimeout(() => openList(false), 120));

    input.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            list.hidden ? renderList() : setActive(active + 1);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            setActive(active - 1);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (!list.hidden && matches[active]) chooseResident(matches[active]);
        } else if (e.key === 'Escape') {
            openList(false);
        }
    });

    list.addEventListener('mousedown', (e) => {
        const li = e.target.closest('.opt');
        if (!li) return;
        e.preventDefault();
        chooseResident(matches[Number(li.dataset.i)]);
    });

    $('change').addEventListener('click', clearResident);


    /* =========================================================
       STEP 3 · DOCUMENT PANELS
       ========================================================= */
    const REQUIRED = {
        ctcDetails: [],
        lateRegisterDetails: ['detail_birthplace', 'detail_father_name', 'detail_mother_name'],
        cuttingTreesDetails: [
            'detail_permit_to_cut_no', 'detail_permit_to_cut_date', 'detail_permit_issued_by', 'detail_tree_count',
            'detail_tree_type', 'detail_tree_location', 'detail_location_description', 'detail_cutter_name'
        ],
        soloParentDetails: []
    };

    const panels = Array.from(document.querySelectorAll('.panel[data-docs]'));

    function syncPanels() {
        const doc = checkedType();

        panels.forEach((panel) => {
            const on = panel.dataset.docs.split('|').includes(doc);
            const required = REQUIRED[panel.id] || [];

            panel.hidden = !on;

            panel.querySelectorAll('input[name^="document_details"]').forEach((field) => {
                field.disabled = !on;
                field.required = on && required.includes(field.id);
                if (!on) field.classList.remove('bad');
            });
        });

        if (doc === 'Solo Parent') {
            renderSelectedSolo();
            renderSoloSearch();
        } else {
            resetSolo();
        }
    }


    /* =========================================================
       PURPOSE (quick-fill chips)
       ========================================================= */
    const purpose = $('purpose');
    const chips = Array.from(document.querySelectorAll('#purposeChips [data-purpose]'));

    function paintPurpose() {
        $('purposeCount').textContent = purpose.value.length;
        const text = purpose.value.toLowerCase();
        chips.forEach((c) => c.classList.toggle('on', text.includes(c.dataset.purpose.toLowerCase())));
    }

    $('purposeChips').addEventListener('click', (e) => {
        const chip = e.target.closest('button[data-purpose]');
        if (!chip) return;

        const label = chip.dataset.purpose;
        const parts = purpose.value.split(',').map((s) => s.trim()).filter(Boolean);
        const at = parts.findIndex((s) => s.toLowerCase() === label.toLowerCase());

        if (at > -1) parts.splice(at, 1);
        else parts.push(parts.length ? label.toLowerCase() : label);

        purpose.value = parts.join(', ');
        purpose.classList.remove('bad');
        paintPurpose();
        purpose.focus();
    });

    purpose.addEventListener('input', paintPurpose);


    /* =========================================================
       SOLO PARENT
       ========================================================= */
    const soloSearch = $('solo_parent_resident_search');
    const soloList = $('solo_parent_resident_list');
    const soloWarning = $('soloHouseholdWarning');

    const householdMembers = () => {
        const members = householdMap[String(hidden.value || '')];
        return Array.isArray(members) ? members : [];
    };

    const memberById = (id) => householdMembers().find((m) => String(m.resident_id) === String(id)) || null;
    const isMember = (id) => !!memberById(id);
    const isSelected = (id) => selectedSolo.includes(String(id));

    function showWarning(message) {
        soloWarning.textContent = message;
        soloWarning.classList.add('show');
    }

    function hideWarning() {
        soloWarning.textContent = '';
        soloWarning.classList.remove('show');
    }

    function rebuildSoloInputs() {
        form.querySelectorAll('input[data-solo-child="true"]').forEach((el) => el.remove());

        if (checkedType() !== 'Solo Parent') return;

        selectedSolo.forEach((id) => {
            const el = document.createElement('input');
            el.type = 'hidden';
            el.name = 'document_details[child_resident_ids][]';
            el.value = String(id);
            el.dataset.soloChild = 'true';
            form.appendChild(el);
        });
    }

    function updateSoloError() {
        $('selectedMembersError').hidden = !(checkedType() === 'Solo Parent' && selectedSolo.length === 0);
        rebuildSoloInputs();
    }

    function renderSelectedSolo() {
        const box = $('selectedMembersList');

        box.querySelectorAll('.selected-member').forEach((el) => el.remove());
        $('selectedMemberCount').textContent = String(selectedSolo.length);
        $('selectedMembersEmpty').hidden = selectedSolo.length > 0;

        selectedSolo.forEach((id) => {
            const m = memberById(id);
            if (!m) return;

            const name = m.full_name || 'Unnamed resident';
            const sex = normalizeSex(m.sex);
            const birth = m.birth_date_display || 'Birth date not recorded';

            const card = document.createElement('div');
            card.className = 'selected-member';
            card.innerHTML =
                '<span class="avatar" aria-hidden="true">' + esc(initials(name)) + '</span>' +
                '<span class="selected-member-main">' +
                    '<span class="selected-member-name">' + esc(name) + '</span>' +
                    '<span class="selected-member-meta">' +
                        '<span><i class="bi bi-person"></i>' + esc(sex) + '</span>' +
                        '<span><i class="bi bi-calendar3"></i>' + esc(birth) + '</span>' +
                    '</span>' +
                '</span>' +
                '<button type="button" class="remove-member" data-remove-member="' + esc(id) + '" ' +
                    'title="Remove selected member" aria-label="Remove ' + esc(name) + '">' +
                    '<i class="bi bi-x-lg"></i></button>';

            box.appendChild(card);
        });

        updateSoloError();
    }

    function renderSoloSearch() {
        const query = soloSearch.value.trim().toLowerCase();

        const rows = residents
            .filter((r) => !query || (r.name + ' ' + r.address).toLowerCase().includes(query))
            .slice(0, 50);

        if (!rows.length) {
            soloList.innerHTML = '<li class="solo-search-empty">No registered resident found.</li>';
            soloList.hidden = false;
            return;
        }

        soloList.innerHTML = rows.map((r) => {
            const id = String(r.id);
            let action;

            if (id === String(hidden.value)) {
                action = '<span class="result-status">Selected Solo Parent</span>';
            } else if (isSelected(id)) {
                action = '<span class="solo-result-added"><i class="bi bi-check-circle-fill"></i> Added</span>';
            } else if (isMember(id)) {
                action = '<button type="button" class="solo-add-btn" data-add-member="' + esc(id) + '"><i class="bi bi-plus-lg"></i> Add</button>';
            } else {
                action = '<span class="result-status">Not in selected household</span>';
            }

            return '<li class="solo-result">' +
                '<span class="avatar" aria-hidden="true">' + esc(initials(r.name)) + '</span>' +
                '<span class="result-main">' +
                    '<b>' + esc(r.name) + '</b>' +
                    '<small>' + esc(normalizeSex(r.sex)) + ' • ' + esc(r.birth_date_display || 'Birth date not recorded') + '</small>' +
                    (r.address ? '<small><i class="bi bi-geo-alt"></i> ' + esc(r.address) + '</small>' : '') +
                '</span>' + action + '</li>';
        }).join('');

        soloList.hidden = false;
    }

    function addSolo(id) {
        id = String(id);

        if (!id || id === String(hidden.value)) {
            showWarning('The selected Solo Parent cannot be added as their own child or dependent.');
            return;
        }

        if (!isMember(id)) {
            showWarning("This resident is registered in the Residents database, but is not registered as a member of the selected Solo Parent's household.");
            return;
        }

        hideWarning();
        if (isSelected(id)) return;

        selectedSolo.push(id);
        renderSelectedSolo();
        renderSoloSearch();
        soloSearch.focus();
    }

    function removeSolo(id) {
        selectedSolo = selectedSolo.filter((x) => String(x) !== String(id));
        renderSelectedSolo();
        renderSoloSearch();
    }

    function resetSolo() {
        selectedSolo = [];
        soloSearch.value = '';
        soloList.innerHTML = '';
        soloList.hidden = true;
        soloSearch.setAttribute('aria-expanded', 'false');
        hideWarning();
        renderSelectedSolo();
    }

    function restoreOldSolo() {
        let ids = Array.isArray(oldChildIds) ? oldChildIds.map(String).filter(Boolean) : [];

        if (!ids.length && oldSingleChild !== null && oldSingleChild !== undefined && String(oldSingleChild).trim() !== '') {
            ids = [String(oldSingleChild)];
        }

        selectedSolo = [...new Set(ids)].filter((id) => isMember(id) && id !== String(hidden.value));
        renderSelectedSolo();
        renderSoloSearch();
    }

    soloSearch.addEventListener('focus', () => {
        renderSoloSearch();
        soloSearch.setAttribute('aria-expanded', 'true');
    });
    soloSearch.addEventListener('input', renderSoloSearch);
    soloSearch.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            soloList.hidden = true;
            soloSearch.setAttribute('aria-expanded', 'false');
        }
        if (e.key === 'Enter') e.preventDefault();
    });

    soloList.addEventListener('mousedown', (e) => {
        const btn = e.target.closest('[data-add-member]');
        if (!btn) return;
        e.preventDefault();
        addSolo(btn.dataset.addMember);
    });

    $('selectedMembersList').addEventListener('click', (e) => {
        const btn = e.target.closest('[data-remove-member]');
        if (btn) removeSolo(btn.dataset.removeMember);
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.solo-picker')) {
            soloList.hidden = true;
            soloSearch.setAttribute('aria-expanded', 'false');
        }
    });


    /* =========================================================
       SUMMARY
       ========================================================= */
    function updateSummary() {
        const r = residents.find((x) => String(x.id) === String(hidden.value));
        const t = checkedType();

        $('sumResident').textContent = r ? r.name : 'Not chosen yet';
        $('sumResident').classList.toggle('empty', !r);
        $('sumDoc').textContent = t || 'Not chosen yet';
        $('sumDoc').classList.toggle('empty', !t);

        const ready = !!(r && t);
        $('submitBtn').disabled = !ready;
        $('missing').hidden = ready;
        $('missing').textContent = !r && !t
            ? 'Choose a resident and a document to continue.'
            : (!r ? 'Choose a resident to continue.' : 'Choose a document to continue.');
    }

    document.querySelectorAll('input[name="document_type"]').forEach((el) =>
        el.addEventListener('change', () => {
            syncPanels();
            updateSummary();
        }));


    /* =========================================================
       SUBMIT VALIDATION
       ========================================================= */
    form.addEventListener('submit', (e) => {
        syncPanels();

        const resident = residents.find((x) => String(x.id) === String(hidden.value));
        const doc = checkedType();

        // Final client-side safeguard. The controller also enforces this server-side.
        if (!resident || !doc || getActiveCases(hidden.value).length > 0) {
            e.preventDefault();

            if (!resident) {
                $('combo').classList.add('invalid');
                input.focus();
            } else if (getActiveCases(hidden.value).length > 0) {
                showActiveCaseModal(resident, getActiveCases(hidden.value));
            } else {
                const firstType = document.querySelector('input[name="document_type"]');
                if (firstType) firstType.focus();
            }

            updateSummary();
            return;
        }

        /* Required fields of the selected document's panel */
        const panel = panels.find((p) => p.dataset.docs.split('|').includes(doc));
        const required = panel ? (REQUIRED[panel.id] || []) : [];

        let firstInvalid = null;

        required.forEach((id) => {
            const field = $(id);
            if (!field) return;

            field.classList.remove('bad');

            if (!String(field.value || '').trim()) {
                field.classList.add('bad');
                firstInvalid = firstInvalid || field;
            } else {
                field.value = String(field.value).trim();
            }
        });

        if (firstInvalid) {
            e.preventDefault();
            firstInvalid.focus();
            return;
        }

        /* Optional text fields: tidy whitespace */
        ['detail_ctc_number', 'detail_tree_owner'].forEach((id) => {
            const field = $(id);
            if (field && !field.disabled) field.value = String(field.value || '').trim();
        });

        /* Solo Parent: at least one valid household member */
        if (doc === 'Solo Parent') {
            selectedSolo = selectedSolo.filter(isMember);

            if (!selectedSolo.length) {
                e.preventDefault();
                renderSelectedSolo();
                soloSearch.focus();
                return;
            }

            rebuildSoloInputs();
        }
    });


    /* =========================================================
       INITIAL STATE
       ========================================================= */
    const oldResident = residents.find((x) => String(x.id) === String(hidden.value));

    if (oldResident) {
        // Do not restore an old resident selection if that resident is now restricted.
        chooseResident(oldResident);
    } else {
        hidden.value = '';
    }

    syncPanels();

    if (oldResident && hidden.value && checkedType() === 'Solo Parent') {
        restoreOldSolo();
    }

    paintPurpose();
    updateSummary();

})();
</script>
@endpush
