
@extends('layouts.app')

@section('title', 'Edit Relief Assistance Operation')

@section('content')

@php
    /* ---------- Status ---------- */
    $status = strtolower((string) ($operation->status ?? 'draft'));

    $tone = match ($status) {
        'draft'        => 'amber',
        'verification' => 'blue',
        'ready'        => 'pine',
        'allocated'    => 'pine',
        'distribution' => 'teal',
        'completed'    => 'slate',
        'cancelled'    => 'red',
        default        => 'slate',
    };

    $statusLabel = match ($status) {
        'draft'        => 'Draft',
        'verification' => 'For verification',
        'ready'        => 'Ready',
        'allocated'    => 'Allocated',
        'distribution' => 'For distribution',
        'completed'    => 'Completed',
        'cancelled'    => 'Cancelled',
        default        => ucfirst($status),
    };

    $statusNote = match ($status) {
        'verification' => 'The printed field assessment may already have been returned and encoded. Review the operation details before making changes.',
        'allocated'    => 'Beneficiary allocation or voucher processing has already started. Avoid unnecessary changes to received quantities or allocation information.',
        'distribution' => 'This operation is already in the distribution stage. Avoid changing information that may affect issued vouchers or distribution records.',
        'completed'    => 'This operation is completed. Edit only when an authorized correction is necessary.',
        'cancelled'    => 'This operation is cancelled. Do not reactivate it through this form unless the authorized workflow allows it.',
        default        => null,
    };

    // Quantity fields are locked by default once allocation / voucher processing has begun.
    $guarded = in_array($status, ['allocated', 'distribution', 'completed'], true);

    /* ---------- Workflow position ---------- */
    $stepIndex = match ($status) {
        'draft'        => 1,
        'verification' => 3,
        'ready'        => 4,
        'allocated'    => 5,
        'distribution' => 5,
        'completed'    => 5,
        'cancelled'    => 0,
        default        => 1,
    };

    $steps = [
        ['Operation details',              'Review the relief operation information, assistance type, source, stock, and schedule.'],
        ['Preview & print masterlist',     'Filter the existing records, preview the full masterlist in the system, then print it for field use.'],
        ['Field assessment',               'BHWs or authorized staff visit households and record the actual findings on the printed masterlist.'],
        ['Encode & review beneficiaries',  'After the field visit, the Secretary or authorized staff encode the actual selected beneficiaries and review the records.'],
        ['Bulk vouchers & distribution',   'Generate vouchers for the selected beneficiaries, print the vouchers, then proceed with distribution and QR verification.'],
    ];

    /* ---------- Routes ---------- */
    $backUrl = Route::has('relief-assistance.show')
        ? route('relief-assistance.show', $operation)
        : (Route::has('assistance-campaigns.show')
            ? route('assistance-campaigns.show', $operation)
            : route('relief-vouchers.index'));

    $updateUrl = Route::has('relief-assistance.update')
        ? route('relief-assistance.update', $operation)
        : (Route::has('assistance-campaigns.update')
            ? route('assistance-campaigns.update', $operation)
            : null);

    /* ---------- Assistance types ---------- */
    $types = [
        'Disaster Relief'           => 'bi-cloud-lightning-rain',
        'AICS Medical Assistance'   => 'bi-heart-pulse',
        'Food Assistance'           => 'bi-basket',
        'Cash Assistance'           => 'bi-cash-coin',
        'Farmer Assistance'         => 'bi-flower2',
        'Senior Citizen Assistance' => 'bi-people',
        'Other Assistance'          => 'bi-three-dots',
    ];

    $selectedType = old('assistance_type', $operation->assistance_type);

    $unitSuggestions = ['packs', 'boxes', 'sets', 'sacks', 'kg', 'pcs'];
@endphp

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .rx {
        --rx-ink: #10201e;
        --rx-muted: #5b6b68;
        --rx-faint: #8a9794;
        --rx-line: #dbe3e0;
        --rx-line-strong: #c3cfcb;
        --rx-paper: #f3f6f5;
        --rx-sheet: #ffffff;

        --rx-pine: #0e6b5e;
        --rx-pine-dark: #0a4f45;
        --rx-pine-soft: #e2f1ed;

        --rx-amber: #b77a00;
        --rx-amber-soft: #fff3d1;
        --rx-blue: #2b62b0;
        --rx-blue-soft: #e5eefb;
        --rx-teal: #12797f;
        --rx-teal-soft: #dff2f3;
        --rx-slate: #4d5b6b;
        --rx-slate-soft: #e8ecf0;
        --rx-red: #c0302d;
        --rx-red-soft: #fce7e6;

        --rx-sticky-top: 12px;

        font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        color: var(--rx-ink);
        font-size: 15px;
        line-height: 1.5;
    }

    .rx *, .rx *::before, .rx *::after { box-sizing: border-box; }

    @media (prefers-reduced-motion: no-preference) {
        html { scroll-behavior: smooth; }
    }

    /* tone helpers: --t = text/accent, --ts = soft background */
    .rx [data-tone="amber"] { --t: var(--rx-amber); --ts: var(--rx-amber-soft); }
    .rx [data-tone="blue"]  { --t: var(--rx-blue);  --ts: var(--rx-blue-soft); }
    .rx [data-tone="pine"]  { --t: var(--rx-pine);  --ts: var(--rx-pine-soft); }
    .rx [data-tone="teal"]  { --t: var(--rx-teal);  --ts: var(--rx-teal-soft); }
    .rx [data-tone="slate"] { --t: var(--rx-slate); --ts: var(--rx-slate-soft); }
    .rx [data-tone="red"]   { --t: var(--rx-red);   --ts: var(--rx-red-soft); }

    /* ---------- Header ---------- */
    .rx-head { display: flex; flex-wrap: wrap; gap: 16px; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; }
    .rx-back {
        display: inline-flex; align-items: center; gap: 6px;
        color: var(--rx-muted); font-weight: 600; font-size: 14px; text-decoration: none; margin-bottom: 10px;
    }
    .rx-back:hover { color: var(--rx-pine); }
    .rx-title {
        font-family: 'Bricolage Grotesque', 'Public Sans', sans-serif;
        font-weight: 800; font-size: clamp(26px, 3.2vw, 36px); line-height: 1.1;
        letter-spacing: -0.025em; margin: 0 0 6px; color: var(--rx-ink);
    }
    .rx-sub { color: var(--rx-muted); margin: 0; max-width: 62ch; }

    .rx-id { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .rx-code {
        font-family: ui-monospace, 'SFMono-Regular', Menlo, Consolas, monospace;
        font-size: 13px; font-weight: 600; padding: 6px 10px; border-radius: 6px;
        background: var(--rx-sheet); border: 1.5px dashed var(--rx-line-strong); color: var(--rx-ink);
    }
    .rx-pill {
        display: inline-flex; align-items: center; gap: 7px; font-weight: 700; font-size: 13px;
        padding: 6px 12px 6px 10px; border-radius: 999px; background: var(--ts); color: var(--t);
    }
    .rx-pill::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--t); }

    /* ---------- Banners ---------- */
    .rx-banner {
        display: flex; gap: 12px; padding: 14px 16px; border-radius: 10px; margin-bottom: 16px;
        background: var(--ts); color: var(--rx-ink); border-left: 5px solid var(--t);
    }
    .rx-banner i { color: var(--t); font-size: 18px; line-height: 1.4; }
    .rx-banner strong { display: block; }
    .rx-banner span { color: var(--rx-muted); font-size: 14px; }
    .rx-errors ul { margin: 6px 0 0; padding-left: 18px; }
    .rx-errors li { font-size: 14px; }

    /* ---------- Layout ---------- */
    .rx-layout { display: grid; grid-template-columns: minmax(0, 1fr); gap: 24px; align-items: start; }
    @media (min-width: 1200px) { .rx-layout { grid-template-columns: minmax(0, 1fr) 340px; } }
    .rx-rail { display: grid; gap: 16px; }
    @media (min-width: 1200px) { .rx-rail { position: sticky; top: var(--rx-sticky-top); } }

    /* ---------- Section tabs ---------- */
    .rx-tabs {
        position: sticky; top: var(--rx-sticky-top); z-index: 20;
        display: flex; gap: 4px; padding: 5px; margin-bottom: 16px; overflow-x: auto;
        background: rgba(255, 255, 255, .92); backdrop-filter: blur(8px);
        border: 1px solid var(--rx-line); border-radius: 12px; scrollbar-width: none;
    }
    .rx-tabs::-webkit-scrollbar { display: none; }
    .rx-tab {
        flex: 1 0 auto; display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        padding: 9px 14px; border-radius: 8px; font-weight: 600; font-size: 14px; white-space: nowrap;
        color: var(--rx-muted); text-decoration: none; transition: background .15s, color .15s;
    }
    .rx-tab:hover { color: var(--rx-ink); background: var(--rx-paper); }
    .rx-tab.is-active { background: var(--rx-pine); color: #fff; }
    .rx-tab .dot { width: 7px; height: 7px; border-radius: 50%; background: var(--rx-amber); display: none; }
    .rx-tab.has-changes .dot { display: inline-block; }
    .rx-tab.is-active .dot { background: #ffd66b; }

    /* ---------- Sheet + sections ---------- */
    .rx-sheet { background: var(--rx-sheet); border: 1px solid var(--rx-line); border-radius: 14px; }
    .rx-section {
        display: grid; grid-template-columns: minmax(0, 1fr); gap: 18px;
        padding: 26px 24px; scroll-margin-top: 80px;
    }
    .rx-section + .rx-section { border-top: 1px solid var(--rx-line); }
    @media (min-width: 900px) {
        .rx-section { grid-template-columns: 220px minmax(0, 1fr); gap: 32px; padding: 30px 32px; }
    }
    .rx-section h2 {
        font-family: 'Bricolage Grotesque', 'Public Sans', sans-serif;
        font-size: 19px; font-weight: 700; letter-spacing: -0.015em; margin: 0 0 6px;
    }
    .rx-section-intro p { margin: 0; color: var(--rx-muted); font-size: 14px; }
    .rx-section-intro .ico {
        width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center;
        background: var(--rx-pine-soft); color: var(--rx-pine); font-size: 18px; margin-bottom: 12px;
    }

    .rx-grid { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 18px 16px; }
    .rx-c12 { grid-column: span 12; }
    .rx-c8, .rx-c7, .rx-c6, .rx-c5, .rx-c4 { grid-column: span 12; }
    @media (min-width: 640px) {
        .rx-c8 { grid-column: span 8; } .rx-c7 { grid-column: span 7; }
        .rx-c6 { grid-column: span 6; } .rx-c5 { grid-column: span 5; } .rx-c4 { grid-column: span 4; }
    }

    /* ---------- Fields ---------- */
    .rx-label { display: flex; justify-content: space-between; align-items: baseline; gap: 8px; font-weight: 600; font-size: 14px; margin-bottom: 6px; }
    .rx-label .req { color: var(--rx-red); margin-left: 2px; }
    .rx-label .count { font-weight: 500; font-size: 12px; color: var(--rx-faint); font-variant-numeric: tabular-nums; }
    .rx-input, .rx-select, .rx-textarea {
        width: 100%; min-height: 44px; padding: 10px 12px; font: inherit; color: var(--rx-ink);
        background: #fff; border: 1.5px solid var(--rx-line-strong); border-radius: 9px;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .rx-textarea { resize: vertical; min-height: 96px; }
    .rx-input::placeholder, .rx-textarea::placeholder { color: var(--rx-faint); }
    .rx-input:hover, .rx-select:hover, .rx-textarea:hover { border-color: var(--rx-faint); }
    .rx-input:focus, .rx-select:focus, .rx-textarea:focus {
        outline: none; border-color: var(--rx-pine); box-shadow: 0 0 0 4px var(--rx-pine-soft);
    }
    .rx-input.is-invalid, .rx-select.is-invalid, .rx-textarea.is-invalid,
    .rx-input.is-warn { border-color: var(--rx-red); }
    .rx-input.is-invalid:focus, .rx-input.is-warn:focus { box-shadow: 0 0 0 4px var(--rx-red-soft); }
    .rx-input[readonly] { background: var(--rx-paper); color: var(--rx-muted); cursor: not-allowed; }
    .rx-help { margin-top: 6px; font-size: 13px; color: var(--rx-muted); }
    .rx-error { margin-top: 6px; font-size: 13px; font-weight: 600; color: var(--rx-red); display: flex; gap: 6px; align-items: flex-start; }
    .rx-error[hidden] { display: none; }

    /* Input with suffix */
    .rx-affix { position: relative; }
    .rx-affix .suffix { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: var(--rx-faint); font-size: 13px; pointer-events: none; }

    /* ---------- Assistance type picker ---------- */
    .rx-types { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
    .rx-type { position: relative; }
    .rx-type input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
    .rx-type label {
        display: flex; align-items: center; gap: 10px; min-height: 48px; padding: 10px 12px;
        border: 1.5px solid var(--rx-line-strong); border-radius: 10px; font-weight: 600; font-size: 13.5px;
        line-height: 1.25; color: var(--rx-muted); background: #fff; cursor: pointer;
        transition: border-color .15s, background .15s, color .15s;
    }
    .rx-type label i { font-size: 18px; color: var(--rx-faint); flex: none; }
    .rx-type:hover label { border-color: var(--rx-faint); }
    .rx-type input:checked + label { border-color: var(--rx-pine); background: var(--rx-pine-soft); color: var(--rx-pine-dark); box-shadow: inset 0 0 0 1px var(--rx-pine); }
    .rx-type input:checked + label i { color: var(--rx-pine); }
    .rx-type input:focus-visible + label { outline: 3px solid var(--rx-pine); outline-offset: 2px; }

    /* ---------- Unit chips ---------- */
    .rx-chips { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 8px; }
    .rx-chip {
        font: inherit; font-size: 12.5px; font-weight: 600; padding: 3px 10px; border-radius: 999px;
        border: 1.5px solid var(--rx-line); background: #fff; color: var(--rx-muted); cursor: pointer;
    }
    .rx-chip:hover { border-color: var(--rx-pine); color: var(--rx-pine); }
    .rx-chip.is-on { background: var(--rx-pine); border-color: var(--rx-pine); color: #fff; }

    /* ---------- Guard (locked quantities) ---------- */
    .rx-guard {
        grid-column: span 12; display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;
        padding: 12px 14px; border-radius: 10px; background: var(--rx-amber-soft); border: 1.5px dashed #e3b64d;
        font-size: 14px;
    }
    .rx-guard i { color: var(--rx-amber); margin-right: 8px; }
    .rx-guard button {
        font: inherit; font-weight: 700; font-size: 13px; padding: 6px 12px; border-radius: 8px; cursor: pointer;
        background: #fff; border: 1.5px solid var(--rx-amber); color: #7a5200;
    }
    .rx-guard button:hover { background: #fff8e3; }

    /* ---------- Meter (live allocation) ---------- */
    .rx-card { background: var(--rx-sheet); border: 1px solid var(--rx-line); border-radius: 14px; padding: 18px; }
    .rx-card h3 {
        font-family: 'Bricolage Grotesque', 'Public Sans', sans-serif;
        font-size: 16px; font-weight: 700; letter-spacing: -0.01em; margin: 0 0 4px;
    }
    .rx-card .note { font-size: 12.5px; color: var(--rx-faint); margin: 0 0 14px; }

    .rx-remaining { display: flex; align-items: baseline; gap: 8px; margin-bottom: 10px; }
    .rx-remaining .num {
        font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 40px; line-height: 1;
        letter-spacing: -0.03em; font-variant-numeric: tabular-nums; color: var(--rx-pine);
    }
    .rx-remaining .lbl { color: var(--rx-muted); font-size: 14px; }
    .rx-remaining.is-over .num { color: var(--rx-red); }

    .rx-meter { height: 12px; border-radius: 999px; background: var(--rx-slate-soft); overflow: hidden; }
    .rx-meter > span { display: block; height: 100%; width: 0; border-radius: 999px; background: var(--rx-pine); transition: width .3s ease; }
    .rx-meter.is-over > span { background: var(--rx-red); }
    .rx-meter-legend { display: flex; justify-content: space-between; margin-top: 8px; font-size: 13px; color: var(--rx-muted); font-variant-numeric: tabular-nums; }
    .rx-meter-legend b { color: var(--rx-ink); }
    .rx-overmsg { margin-top: 10px; font-size: 13px; font-weight: 600; color: var(--rx-red); }
    .rx-overmsg[hidden] { display: none; }

    .rx-facts { margin: 16px 0 0; padding: 14px 0 0; border-top: 1px solid var(--rx-line); display: grid; gap: 8px; font-size: 13.5px; }
    .rx-facts div { display: flex; justify-content: space-between; gap: 12px; }
    .rx-facts dt { color: var(--rx-muted); font-weight: 500; }
    .rx-facts dd { margin: 0; font-weight: 600; text-align: right; }

    /* ---------- Workflow ---------- */
    .rx-flow { list-style: none; margin: 0; padding: 0; }
    .rx-flow li { position: relative; display: grid; grid-template-columns: 26px 1fr; gap: 12px; padding-bottom: 14px; }
    .rx-flow li:last-child { padding-bottom: 0; }
    .rx-flow li::before {
        content: ''; position: absolute; left: 12px; top: 26px; bottom: 0; width: 2px; background: var(--rx-line);
    }
    .rx-flow li:last-child::before { display: none; }
    .rx-flow li.is-done::before { background: var(--rx-pine); }
    .rx-flow .n {
        width: 26px; height: 26px; border-radius: 50%; display: grid; place-items: center;
        font-size: 12px; font-weight: 700; background: #fff; border: 2px solid var(--rx-line-strong); color: var(--rx-faint);
    }
    .rx-flow li.is-done .n { background: var(--rx-pine); border-color: var(--rx-pine); color: #fff; }
    .rx-flow li.is-current .n { border-color: var(--rx-pine); color: var(--rx-pine); box-shadow: 0 0 0 4px var(--rx-pine-soft); }
    .rx-flow .t { font-weight: 600; font-size: 14px; line-height: 1.3; padding-top: 3px; }
    .rx-flow li:not(.is-done):not(.is-current) .t { color: var(--rx-muted); }
    .rx-flow .d { display: none; font-size: 12.5px; color: var(--rx-muted); margin-top: 2px; font-weight: 400; }
    .rx-flow li.is-current .d { display: block; }
    .rx-flow-here { font-size: 11.5px; font-weight: 700; color: var(--rx-pine); margin-left: 6px; }

    /* ---------- Reminder list ---------- */
    .rx-remind { margin: 0; padding: 0; list-style: none; display: grid; gap: 10px; font-size: 13.5px; color: var(--rx-muted); }
    .rx-remind li { display: grid; grid-template-columns: 18px 1fr; gap: 8px; }
    .rx-remind i { color: var(--rx-amber); margin-top: 2px; }
    .rx-human { display: grid; grid-template-columns: 38px 1fr; gap: 12px; }
    .rx-human .ico { width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; background: var(--rx-pine-soft); color: var(--rx-pine); font-size: 18px; }
    .rx-human p { margin: 0; font-size: 13.5px; color: var(--rx-muted); }

    /* ---------- Save bar ---------- */
    .rx-savebar {
        position: sticky; bottom: 12px; z-index: 30; margin-top: 18px;
        display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between;
        padding: 12px 14px 12px 18px; border-radius: 14px; background: var(--rx-ink); color: #fff;
        box-shadow: 0 10px 30px rgba(16, 32, 30, .28);
    }
    .rx-savestate { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500; }
    .rx-savestate .led { width: 9px; height: 9px; border-radius: 50%; background: #5fd4a8; }
    .rx-savebar.is-dirty .led { background: #ffc94d; animation: rx-pulse 1.6s ease-in-out infinite; }
    @keyframes rx-pulse { 50% { opacity: .35; } }
    @media (prefers-reduced-motion: reduce) { .rx-savebar.is-dirty .led { animation: none; } }
    .rx-actions { display: flex; gap: 8px; margin-left: auto; }
    .rx-btn {
        font: inherit; font-weight: 700; font-size: 14px; min-height: 42px; padding: 0 18px; border-radius: 9px;
        display: inline-flex; align-items: center; gap: 8px; cursor: pointer; text-decoration: none; border: 1.5px solid transparent;
    }
    .rx-btn-ghost { background: transparent; color: #fff; border-color: rgba(255, 255, 255, .3); }
    .rx-btn-ghost:hover { background: rgba(255, 255, 255, .1); color: #fff; }
    .rx-btn-save { background: #5fd4a8; color: #06251e; }
    .rx-btn-save:hover { background: #7ee0bb; }
    .rx-btn-save:disabled { background: #3c4b48; color: #8a9794; cursor: not-allowed; }
    .rx-btn:focus-visible, .rx-chip:focus-visible, .rx-tab:focus-visible, .rx-guard button:focus-visible { outline: 3px solid #ffc94d; outline-offset: 2px; }
    [hidden] { display: none !important; }
</style>

<div class="container-fluid py-4 rx">

    {{-- ================= HEADER ================= --}}
    <div class="rx-head">
        <div>
            <a href="{{ $backUrl }}" class="rx-back">
                <i class="bi bi-arrow-left"></i> Back to operation
            </a>
            <h1 class="rx-title">Edit relief operation</h1>
            <p class="rx-sub">
                Update the details of this operation. The masterlist is previewed and printed
                before the field visit, then the returned assessment is encoded by authorized staff.
            </p>
        </div>

        <div class="rx-id" data-tone="{{ $tone }}">
            <span class="rx-code" title="Operation code (cannot be edited)">{{ $operation->operation_code ?? 'No code' }}</span>
            <span class="rx-pill">{{ $statusLabel }}</span>
        </div>
    </div>

    {{-- ================= ERRORS ================= --}}
    @if($errors->any())
        <div class="rx-banner rx-errors" data-tone="red" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>
                    {{ $errors->count() }}
                    {{ $errors->count() === 1 ? 'problem needs' : 'problems need' }}
                    fixing before this can be saved
                </strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- ================= STATUS NOTE ================= --}}
    @if($statusNote)
        <div class="rx-banner" data-tone="{{ $tone }}">
            <i class="bi bi-info-circle-fill"></i>
            <div>
                <strong>This operation is {{ strtolower($statusLabel) }}</strong>
                <span>{{ $statusNote }}</span>
            </div>
        </div>
    @endif

    <div class="rx-layout">

        {{-- ========================================================
             MAIN FORM
        ========================================================= --}}
        <div>

            {{-- Section tabs --}}
            <nav class="rx-tabs" id="rxTabs" aria-label="Form sections">
                <a href="#rx-basics" class="rx-tab is-active" data-target="rx-basics">Basics <span class="dot"></span></a>
                <a href="#rx-stock" class="rx-tab" data-target="rx-stock">Source &amp; stock <span class="dot"></span></a>
                <a href="#rx-distribution" class="rx-tab" data-target="rx-distribution">Distribution <span class="dot"></span></a>
                <a href="#rx-allocation" class="rx-tab" data-target="rx-allocation">Allocation <span class="dot"></span></a>
            </nav>

            @if($updateUrl)
                <form method="POST" action="{{ $updateUrl }}" id="reliefEditForm" novalidate>
            @else
                <form method="POST" action="#" id="reliefEditForm" onsubmit="return false;" novalidate>
            @endif

                @csrf
                @method('PUT')

                <div class="rx-sheet">

                    {{-- ============ BASICS ============ --}}
                    <section class="rx-section" id="rx-basics" data-section>
                        <div class="rx-section-intro">
                            <div class="ico"><i class="bi bi-pencil-square"></i></div>
                            <h2>Basics</h2>
                            <p>What this operation is called and what kind of help it gives.</p>
                        </div>

                        <div class="rx-grid">

                            <div class="rx-c12">
                                <label class="rx-label" for="operation_name">
                                    <span>Operation name<span class="req">*</span></span>
                                </label>
                                <input type="text" id="operation_name" name="operation_name"
                                       class="rx-input @error('operation_name') is-invalid @enderror"
                                       value="{{ old('operation_name', $operation->operation_name) }}"
                                       maxlength="255" required>
                                @error('operation_name')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                                <div class="rx-error" data-error-for="operation_name" hidden></div>
                            </div>

                            <div class="rx-c12">
                                <div class="rx-label">
                                    <span id="typeLabel">Assistance type<span class="req">*</span></span>
                                </div>
                                <div class="rx-types" role="radiogroup" aria-labelledby="typeLabel">
                                    @foreach($types as $typeName => $typeIcon)
                                        <div class="rx-type">
                                            <input type="radio" name="assistance_type" id="type_{{ $loop->index }}"
                                                   value="{{ $typeName }}"
                                                   {{ $selectedType === $typeName ? 'checked' : '' }}
                                                   {{ $loop->first ? 'required' : '' }}>
                                            <label for="type_{{ $loop->index }}">
                                                <i class="bi {{ $typeIcon }}"></i>{{ $typeName }}
                                            </label>
                                        </div>
                                    @endforeach

                                    {{-- Keep a legacy / custom saved value selectable --}}
                                    @if($selectedType && ! array_key_exists($selectedType, $types))
                                        <div class="rx-type">
                                            <input type="radio" name="assistance_type" id="type_custom"
                                                   value="{{ $selectedType }}" checked>
                                            <label for="type_custom"><i class="bi bi-tag"></i>{{ $selectedType }}</label>
                                        </div>
                                    @endif
                                </div>
                                @error('assistance_type')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="rx-c12">
                                <label class="rx-label" for="assistance_description">
                                    <span>What's in the assistance package?</span>
                                    <span class="count" data-count-for="assistance_description"></span>
                                </label>
                                <textarea id="assistance_description" name="assistance_description" rows="3" maxlength="2000"
                                          class="rx-textarea @error('assistance_description') is-invalid @enderror"
                                          placeholder="Describe the package, its purpose, or its contents.">{{ old('assistance_description', $operation->assistance_description) }}</textarea>
                                @error('assistance_description')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="rx-c12">
                                <label class="rx-label" for="description">
                                    <span>Internal notes</span>
                                    <span class="count" data-count-for="description"></span>
                                </label>
                                <textarea id="description" name="description" rows="3" maxlength="2000"
                                          class="rx-textarea @error('description') is-invalid @enderror"
                                          placeholder="Optional notes about this relief operation.">{{ old('description', $operation->description) }}</textarea>
                                @error('description')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </section>


                    {{-- ============ SOURCE & STOCK ============ --}}
                    <section class="rx-section" id="rx-stock" data-section>
                        <div class="rx-section-intro">
                            <div class="ico"><i class="bi bi-box-seam"></i></div>
                            <h2>Source &amp; stock</h2>
                            <p>Where the assistance came from and how much was received.</p>
                        </div>

                        <div class="rx-grid">

                            <div class="rx-c6">
                                <label class="rx-label" for="source_name"><span>Source / donor</span></label>
                                <input type="text" id="source_name" name="source_name"
                                       class="rx-input @error('source_name') is-invalid @enderror"
                                       value="{{ old('source_name', $operation->source_name) }}"
                                       maxlength="255" placeholder="e.g. Barangay, LGU, DSWD, NGO">
                                @error('source_name')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="rx-c6">
                                <label class="rx-label" for="source_reference"><span>Source reference</span></label>
                                <input type="text" id="source_reference" name="source_reference"
                                       class="rx-input @error('source_reference') is-invalid @enderror"
                                       value="{{ old('source_reference', $operation->source_reference) }}"
                                       maxlength="255" placeholder="Reference number or document">
                                @error('source_reference')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            @if($guarded)
                                <div class="rx-guard" id="rxStockGuard" data-guard="quantity_received">
                                    <span><i class="bi bi-lock-fill"></i>Received quantity is locked because allocation has already started.</span>
                                    <button type="button" data-unlock>Unlock to correct</button>
                                </div>
                            @endif

                            <div class="rx-c5">
                                <label class="rx-label" for="quantity_received"><span>Quantity received</span></label>
                                <input type="number" id="quantity_received" name="quantity_received" inputmode="numeric"
                                       class="rx-input @error('quantity_received') is-invalid @enderror"
                                       value="{{ old('quantity_received', $operation->quantity_received) }}"
                                       min="0" step="1" {{ $guarded ? 'readonly' : '' }}>
                                @error('quantity_received')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="rx-c4">
                                <label class="rx-label" for="quantity_unit"><span>Unit</span></label>
                                <input type="text" id="quantity_unit" name="quantity_unit"
                                       class="rx-input @error('quantity_unit') is-invalid @enderror"
                                       value="{{ old('quantity_unit', $operation->quantity_unit) }}"
                                       maxlength="100" placeholder="e.g. packs" autocomplete="off">
                                @error('quantity_unit')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                                <div class="rx-chips" aria-label="Common units">
                                    @foreach($unitSuggestions as $u)
                                        <button type="button" class="rx-chip" data-unit="{{ $u }}">{{ $u }}</button>
                                    @endforeach
                                </div>
                            </div>

                            <div class="rx-c4">
                                <label class="rx-label" for="assistance_received_date"><span>Date received</span></label>
                                <input type="date" id="assistance_received_date" name="assistance_received_date"
                                       class="rx-input @error('assistance_received_date') is-invalid @enderror"
                                       value="{{ old('assistance_received_date', optional($operation->assistance_received_date)->format('Y-m-d')) }}">
                                @error('assistance_received_date')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </section>


                    {{-- ============ DISTRIBUTION ============ --}}
                    <section class="rx-section" id="rx-distribution" data-section>
                        <div class="rx-section-intro">
                            <div class="ico"><i class="bi bi-calendar-check"></i></div>
                            <h2>Distribution</h2>
                            <p>When and where households will claim their assistance.</p>
                        </div>

                        <div class="rx-grid">

                            <div class="rx-c4">
                                <label class="rx-label" for="distribution_date"><span>Date</span></label>
                                <input type="date" id="distribution_date" name="distribution_date"
                                       class="rx-input @error('distribution_date') is-invalid @enderror"
                                       value="{{ old('distribution_date', optional($operation->distribution_date)->format('Y-m-d')) }}">
                                @error('distribution_date')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                                <div class="rx-error" data-error-for="distribution_date" hidden></div>
                                <div class="rx-help" id="rxDateHint" hidden></div>
                            </div>

                            <div class="rx-c4">
                                <label class="rx-label" for="distribution_time"><span>Time</span></label>
                                <input type="time" id="distribution_time" name="distribution_time"
                                       class="rx-input @error('distribution_time') is-invalid @enderror"
                                       value="{{ old('distribution_time', $operation->distribution_time ? substr((string) $operation->distribution_time, 0, 5) : '') }}">
                                @error('distribution_time')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="rx-c4">
                                <label class="rx-label" for="distribution_location"><span>Location</span></label>
                                <input type="text" id="distribution_location" name="distribution_location"
                                       class="rx-input @error('distribution_location') is-invalid @enderror"
                                       value="{{ old('distribution_location', $operation->distribution_location) }}"
                                       maxlength="255" placeholder="e.g. Barangay Hall">
                                @error('distribution_location')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </section>


                    {{-- ============ ALLOCATION ============ --}}
                    <section class="rx-section" id="rx-allocation" data-section>
                        <div class="rx-section-intro">
                            <div class="ico"><i class="bi bi-diagram-3"></i></div>
                            <h2>Allocation</h2>
                            <p>How much of the stock is set aside, and the basis for it.</p>
                        </div>

                        <div class="rx-grid">

                            @if($guarded)
                                <div class="rx-guard" id="rxAllocGuard" data-guard="quantity_allocated">
                                    <span><i class="bi bi-lock-fill"></i>Allocated quantity is locked. Only change it for an authorized correction.</span>
                                    <button type="button" data-unlock>Unlock to correct</button>
                                </div>
                            @endif

                            <div class="rx-c5">
                                <label class="rx-label" for="quantity_allocated"><span>Quantity allocated</span></label>
                                <input type="number" id="quantity_allocated" name="quantity_allocated" inputmode="numeric"
                                       class="rx-input @error('quantity_allocated') is-invalid @enderror"
                                       value="{{ old('quantity_allocated', $operation->quantity_allocated) }}"
                                       min="0" step="1" {{ $guarded ? 'readonly' : '' }}>
                                @error('quantity_allocated')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                                <div class="rx-error" data-error-for="quantity_allocated" hidden></div>
                            </div>

                            <div class="rx-c7">
                                <label class="rx-label" for="allocation_basis"><span>Allocation basis</span></label>
                                <input type="text" id="allocation_basis" name="allocation_basis"
                                       class="rx-input @error('allocation_basis') is-invalid @enderror"
                                       value="{{ old('allocation_basis', $operation->allocation_basis) }}"
                                       maxlength="255" placeholder="e.g. One package per verified household">
                                @error('allocation_basis')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="rx-c12">
                                <label class="rx-label" for="allocation_notes">
                                    <span>Allocation notes</span>
                                    <span class="count" data-count-for="allocation_notes"></span>
                                </label>
                                <textarea id="allocation_notes" name="allocation_notes" rows="3" maxlength="2000"
                                          class="rx-textarea @error('allocation_notes') is-invalid @enderror"
                                          placeholder="Optional notes about this allocation.">{{ old('allocation_notes', $operation->allocation_notes) }}</textarea>
                                @error('allocation_notes')
                                    <div class="rx-error"><i class="bi bi-exclamation-circle"></i>{{ $message }}</div>
                                @enderror
                            </div>

                        </div>
                    </section>

                </div>{{-- /rx-sheet --}}


                {{-- ============ SAVE BAR ============ --}}
                <div class="rx-savebar" id="rxSaveBar">
                    <div class="rx-savestate" aria-live="polite">
                        <span class="led"></span>
                        <span id="rxSaveText">No changes yet</span>
                    </div>

                    <div class="rx-actions">
                        <button type="button" class="rx-btn rx-btn-ghost" id="rxDiscard" hidden>
                            <i class="bi bi-arrow-counterclockwise"></i> Discard
                        </button>
                        <a href="{{ $backUrl }}" class="rx-btn rx-btn-ghost" id="rxCancel">Cancel</a>

                        @if($updateUrl)
                            <button type="submit" class="rx-btn rx-btn-save">
                                <i class="bi bi-check2-circle"></i> Save changes
                            </button>
                        @else
                            <button type="button" class="rx-btn rx-btn-save" disabled>
                                <i class="bi bi-slash-circle"></i> Update route not available
                            </button>
                        @endif
                    </div>
                </div>

            </form>
        </div>


        {{-- ========================================================
             SIDE RAIL
        ========================================================= --}}
        <aside class="rx-rail">

            {{-- Live allocation --}}
            <div class="rx-card">
                <h3>Allocation at a glance</h3>
                <p class="note">Updates as you type. Nothing is saved until you press Save.</p>

                <div class="rx-remaining" id="rxRemainingWrap">
                    <span class="num" id="rxRemaining">0</span>
                    <span class="lbl"><span id="rxUnitA">units</span> left to allocate</span>
                </div>

                <div class="rx-meter" id="rxMeter" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0" aria-label="Share of received quantity that is allocated">
                    <span id="rxMeterBar"></span>
                </div>
                <div class="rx-meter-legend">
                    <span><b id="rxAllocated">0</b> allocated</span>
                    <span><b id="rxReceived">0</b> received</span>
                </div>
                <div class="rx-overmsg" id="rxOverMsg" hidden>
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    Allocated is higher than received by <span id="rxOverBy">0</span>.
                </div>

                <dl class="rx-facts">
                    <div><dt>Status</dt><dd data-tone="{{ $tone }}"><span class="rx-pill">{{ $statusLabel }}</span></dd></div>
                    <div><dt>Distribution</dt><dd id="rxFactWhen">Not set</dd></div>
                    <div><dt>Location</dt><dd id="rxFactWhere">Not set</dd></div>
                </dl>
            </div>

            {{-- Workflow --}}
            <div class="rx-card">
                <h3>Where this operation is</h3>
                <p class="note">
                    @if($stepIndex === 0)
                        This operation was cancelled and is no longer moving through the workflow.
                    @else
                        Six steps from masterlist to vouchers.
                    @endif
                </p>

                <ol class="rx-flow">
                    @foreach($steps as $i => $step)
                        @php
                            $n = $i + 1;
                            $cls = $stepIndex === 0 ? '' : ($n < $stepIndex ? 'is-done' : ($n === $stepIndex ? 'is-current' : ''));
                        @endphp
                        <li class="{{ $cls }}">
                            <span class="n">
                                @if($cls === 'is-done')
                                    <i class="bi bi-check-lg"></i>
                                @else
                                    {{ $n }}
                                @endif
                            </span>
                            <div>
                                <div class="t">
                                    {{ $step[0] }}
                                    @if($cls === 'is-current')<span class="rx-flow-here">You are here</span>@endif
                                </div>
                                <div class="d">{{ $step[1] }}</div>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            {{-- Human verification --}}
            <div class="rx-card rx-human">
                <div class="ico"><i class="bi bi-person-check"></i></div>
                <div>
                    <h3>People decide, not the system</h3>
                    <p>
                        The system does not decide who receives assistance. BHWs or authorized staff
                        record the actual field findings on the printed masterlist, then the Secretary or
                        authorized staff encode the selected beneficiaries. Staff remain responsible for the decision.
                    </p>
                </div>
            </div>

            {{-- Reminders --}}
            <div class="rx-card">
                <h3>Before you save</h3>
                <ul class="rx-remind" style="margin-top: 12px;">
                    <li><i class="bi bi-shield-exclamation"></i><span>The operation code cannot be changed here.</span></li>
                    <li><i class="bi bi-shield-exclamation"></i><span>Do not use this form to decide or approve beneficiaries; field assessment and beneficiary selection are handled in the appropriate workflow.</span></li>
                    <li><i class="bi bi-shield-exclamation"></i><span>After allocation or voucher processing, change quantities only for an authorized correction.</span></li>
                    <li><i class="bi bi-shield-exclamation"></i><span>Saving does not approve or reject any household.</span></li>
                </ul>
            </div>

        </aside>
    </div>
</div>


{{-- ================================================================
     INTERACTIONS
================================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    var form = document.getElementById('reliefEditForm');
    if (!form) { return; }

    var $ = function (id) { return document.getElementById(id); };

    var received   = $('quantity_received');
    var allocated  = $('quantity_allocated');
    var unit       = $('quantity_unit');
    var recDate    = $('assistance_received_date');
    var distDate   = $('distribution_date');
    var distTime   = $('distribution_time');
    var distPlace  = $('distribution_location');

    var submitting = false;

    /* ---------- helpers ---------- */
    function num(el) {
        var n = parseInt((el && el.value) || '0', 10);
        return isNaN(n) || n < 0 ? 0 : n;
    }
    function fmt(n) { return n.toLocaleString('en-US'); }

    function showError(field, message) {
        var box = form.querySelector('[data-error-for="' + field.id + '"]');
        field.setCustomValidity(message || '');
        field.classList.toggle('is-warn', !!message);
        if (!box) { return; }
        if (message) {
            box.innerHTML = '<i class="bi bi-exclamation-circle"></i>' + message;
            box.hidden = false;
        } else {
            box.hidden = true;
            box.innerHTML = '';
        }
    }

    /* ---------- live allocation summary ---------- */
    function updateSummary() {
        var r = num(received), a = num(allocated);
        var left = Math.max(0, r - a);
        var over = a > r;
        var pct = r > 0 ? Math.min(100, Math.round((a / r) * 100)) : (a > 0 ? 100 : 0);
        var u = (unit && unit.value.trim()) || 'units';

        $('rxRemaining').textContent = fmt(left);
        $('rxAllocated').textContent = fmt(a);
        $('rxReceived').textContent  = fmt(r);
        $('rxUnitA').textContent = u;
        $('rxMeterBar').style.width = pct + '%';
        $('rxMeter').setAttribute('aria-valuenow', pct);
        $('rxMeter').classList.toggle('is-over', over);
        $('rxRemainingWrap').classList.toggle('is-over', over);
        $('rxOverMsg').hidden = !over;
        $('rxOverBy').textContent = fmt(Math.max(0, a - r));

        showError(allocated, over ? 'Quantity allocated cannot exceed quantity received (' + fmt(r) + ').' : '');
    }

    function updateDates() {
        var bad = recDate.value && distDate.value && distDate.value < recDate.value;
        showError(distDate, bad ? 'Distribution date cannot be earlier than the date received.' : '');

        var hint = $('rxDateHint');
        var when = $('rxFactWhen');
        if (distDate.value && !bad) {
            var today = new Date(); today.setHours(0, 0, 0, 0);
            var d = new Date(distDate.value + 'T00:00:00');
            var diff = Math.round((d - today) / 86400000);
            var rel = diff === 0 ? 'Today'
                    : diff === 1 ? 'Tomorrow'
                    : diff > 1 ? 'In ' + diff + ' days'
                    : Math.abs(diff) === 1 ? 'Yesterday'
                    : Math.abs(diff) + ' days ago';
            hint.textContent = rel;
            hint.hidden = false;
            when.textContent = d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
                             + (distTime.value ? ', ' + distTime.value : '');
        } else {
            hint.hidden = true;
            when.textContent = 'Not set';
        }
        $('rxFactWhere').textContent = (distPlace.value.trim() || 'Not set');
    }

    function syncUnitChips() {
        var v = unit.value.trim().toLowerCase();
        document.querySelectorAll('.rx-chip').forEach(function (c) {
            c.classList.toggle('is-on', c.dataset.unit === v);
        });
    }

    /* ---------- unit chips ---------- */
    document.querySelectorAll('.rx-chip').forEach(function (chip) {
        chip.addEventListener('click', function () {
            unit.value = chip.dataset.unit;
            unit.dispatchEvent(new Event('input', { bubbles: true }));
            unit.focus();
        });
    });

    /* ---------- char counters ---------- */
    document.querySelectorAll('[data-count-for]').forEach(function (el) {
        var field = $(el.dataset.countFor);
        if (!field) { return; }
        var max = field.getAttribute('maxlength');
        function paint() { el.textContent = field.value.length + (max ? ' / ' + max : ''); }
        field.addEventListener('input', paint);
        paint();
    });

    /* ---------- locked quantity fields ---------- */
    document.querySelectorAll('[data-guard]').forEach(function (guard) {
        var field = $(guard.dataset.guard);
        var btn = guard.querySelector('[data-unlock]');
        if (!field || !btn) { return; }
        btn.addEventListener('click', function () {
            var locked = field.hasAttribute('readonly');
            if (locked) {
                field.removeAttribute('readonly');
                field.focus();
                btn.textContent = 'Lock again';
            } else {
                field.setAttribute('readonly', 'readonly');
                btn.textContent = 'Unlock to correct';
            }
        });
    });

    /* ---------- unsaved-change tracking ---------- */
    function snapshot() {
        var o = {};
        new FormData(form).forEach(function (v, k) {
            if (k === '_token' || k === '_method') { return; }
            o[k] = String(v);
        });
        return o;
    }

    var initial = snapshot();
    var bar = $('rxSaveBar');
    var text = $('rxSaveText');
    var discard = $('rxDiscard');
    var fieldSection = {};
    document.querySelectorAll('[data-section]').forEach(function (sec) {
        sec.querySelectorAll('input, select, textarea').forEach(function (f) {
            if (f.name) { fieldSection[f.name] = sec.id; }
        });
    });

    function trackChanges() {
        var now = snapshot();
        var changed = [];
        var keys = Object.keys(Object.assign({}, initial, now));
        keys.forEach(function (k) {
            if ((initial[k] || '') !== (now[k] || '')) { changed.push(k); }
        });

        var dirty = changed.length > 0;
        bar.classList.toggle('is-dirty', dirty);
        discard.hidden = !dirty;
        text.textContent = dirty
            ? changed.length + (changed.length === 1 ? ' unsaved change' : ' unsaved changes')
            : 'No changes yet';

        var dirtySections = {};
        changed.forEach(function (k) { if (fieldSection[k]) { dirtySections[fieldSection[k]] = true; } });
        document.querySelectorAll('.rx-tab').forEach(function (tab) {
            tab.classList.toggle('has-changes', !!dirtySections[tab.dataset.target]);
        });
        return dirty;
    }

    function refresh() {
        updateSummary();
        updateDates();
        syncUnitChips();
        trackChanges();
    }

    form.addEventListener('input', refresh);
    form.addEventListener('change', refresh);

    discard.addEventListener('click', function () {
        form.reset();
        setTimeout(function () {
            document.querySelectorAll('[data-count-for]').forEach(function (el) {
                var f = $(el.dataset.countFor);
                if (f) { f.dispatchEvent(new Event('input')); }
            });
            refresh();
        }, 0);
    });

    form.addEventListener('submit', function (e) {
        refresh();
        if (!form.checkValidity()) {
            e.preventDefault();
            form.reportValidity();
            return;
        }
        submitting = true;
    });

    window.addEventListener('beforeunload', function (e) {
        if (!submitting && trackChanges()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    /* ---------- section tabs: highlight the one in view ---------- */
    var tabs = Array.prototype.slice.call(document.querySelectorAll('.rx-tab'));
    function setActive(id) {
        tabs.forEach(function (t) { t.classList.toggle('is-active', t.dataset.target === id); });
        var active = tabs.filter(function (t) { return t.dataset.target === id; })[0];
        if (active && active.scrollIntoView) {
            var wrap = $('rxTabs');
            if (wrap.scrollWidth > wrap.clientWidth) {
                wrap.scrollTo({ left: active.offsetLeft - 16, behavior: 'smooth' });
            }
        }
    }

    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) { if (en.isIntersecting) { setActive(en.target.id); } });
        }, { rootMargin: '-25% 0px -65% 0px' });
        document.querySelectorAll('[data-section]').forEach(function (s) { io.observe(s); });
    }

    refresh();
});
</script>

@endsection
