@extends('layouts.app')

@section('title', 'Edit Emergency Hotline | e-Poblacion')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    /* ============================================================
       EMERGENCY HOTLINES — EDIT PAGE (Dispatch Desk layout)
       Form on the left, live "resident view" call card on the right.
       Supports data-theme: light / dark / system
       ============================================================ */

    .hx {
        --hx-display: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
        --hx-body: 'Figtree', 'Segoe UI', system-ui, sans-serif;

        /* light (default) */
        --hx-bg: #f2f4f7;
        --hx-surface: #ffffff;
        --hx-sunken: #f3f7f4;
        --hx-line: #dce7e0;
        --hx-line-strong: #c2d3c8;
        --hx-ink: #16291f;
        --hx-text: #2b3f33;
        --hx-muted: #5a6c62;
        --hx-signal: #1f7a4d;
        --hx-signal-soft: #e3f1e8;
        --hx-ok: #16805a;
        --hx-ok-soft: #e3f5ed;
        --hx-warn: #a86a08;
        --hx-warn-soft: #fbf0dc;
        --hx-err: #b42330;
        --hx-err-soft: #fbeceb;
        --hx-cta: #1f7a4d;
        --hx-cta-text: #ffffff;
        --hx-focus: rgba(31, 122, 77, .28);
        --hx-shadow: 0 1px 2px rgba(22, 41, 31, .05), 0 8px 24px rgba(22, 41, 31, .06);

        font-family: var(--hx-body);
        color: var(--hx-text);
    }

    [data-theme="dark"] .hx {
        --hx-bg: #0f1a14;
        --hx-surface: #17251d;
        --hx-sunken: #12201a;
        --hx-line: #2a3d32;
        --hx-line-strong: #3a5244;
        --hx-ink: #f2f8f4;
        --hx-text: #d3e2d9;
        --hx-muted: #9bb3a5;
        --hx-signal: #62dca0;
        --hx-signal-soft: rgba(98, 220, 160, .14);
        --hx-ok: #4fd1a0;
        --hx-ok-soft: #12352b;
        --hx-warn: #f2b955;
        --hx-warn-soft: #382b12;
        --hx-err: #ff9a9a;
        --hx-err-soft: rgba(255, 154, 154, .12);
        --hx-cta: #62dca0;
        --hx-cta-text: #04241a;
        --hx-focus: rgba(98, 220, 160, .30);
        --hx-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 8px 24px rgba(0, 0, 0, .35);
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .hx {
            --hx-bg: #0f1a14;
            --hx-surface: #17251d;
            --hx-sunken: #12201a;
            --hx-line: #2a3d32;
            --hx-line-strong: #3a5244;
            --hx-ink: #f2f8f4;
            --hx-text: #d3e2d9;
            --hx-muted: #9bb3a5;
            --hx-signal: #62dca0;
            --hx-signal-soft: rgba(98, 220, 160, .14);
            --hx-ok: #4fd1a0;
            --hx-ok-soft: #12352b;
            --hx-warn: #f2b955;
            --hx-warn-soft: #382b12;
        --hx-err: #ff9a9a;
        --hx-err-soft: rgba(255, 154, 154, .12);
            --hx-cta: #62dca0;
            --hx-cta-text: #04241a;
            --hx-focus: rgba(98, 220, 160, .30);
            --hx-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 8px 24px rgba(0, 0, 0, .35);
        }
    }

    .hx *, .hx *::before, .hx *::after { box-sizing: border-box; }
    .hx { padding: 8px 0 0; }

    /* ---------- Header ---------- */
    .hx-head {
        display: flex; flex-wrap: wrap; gap: 16px;
        align-items: flex-end; justify-content: space-between;
        margin-bottom: 26px;
    }
    .hx-crumbs {
        display: flex; align-items: center; gap: 8px;
        font-size: .85rem; color: var(--hx-muted); margin-bottom: 10px;
    }
    .hx-crumbs a { color: var(--hx-muted); text-decoration: none; }
    .hx-crumbs a:hover { color: var(--hx-ink); text-decoration: underline; }
    .hx-title {
        font-family: var(--hx-display);
        font-size: clamp(1.7rem, 3.2vw, 2.4rem);
        font-weight: 800; letter-spacing: -.02em; line-height: 1.05;
        color: var(--hx-ink); margin: 0;
    }
    .hx-sub { margin: 8px 0 0; color: var(--hx-muted); max-width: 52ch; }
    .hx-head-side { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .hx-chip {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 7px 12px; border-radius: 999px;
        background: var(--hx-surface); border: 1px solid var(--hx-line);
        font-size: .82rem; color: var(--hx-text); font-variant-numeric: tabular-nums;
    }
    .hx-chip b { color: var(--hx-ink); font-weight: 600; }

    /* ---------- Buttons ---------- */
    .hx-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        min-height: 44px; padding: 0 18px; border-radius: 12px;
        font: 600 .95rem var(--hx-body); text-decoration: none; cursor: pointer;
        border: 1px solid var(--hx-line-strong);
        background: var(--hx-surface); color: var(--hx-ink);
        transition: background .15s, border-color .15s, transform .1s;
    }
    .hx-btn:hover { background: var(--hx-sunken); border-color: var(--hx-muted); color: var(--hx-ink); }
    .hx-btn:active { transform: translateY(1px); }
    .hx-btn:focus-visible, .hx-swap:focus-visible, .hx-seg label:focus-visible {
        outline: 3px solid var(--hx-focus); outline-offset: 2px;
    }
    .hx-btn--primary { background: var(--hx-cta); color: var(--hx-cta-text); border-color: var(--hx-cta); }
    .hx-btn--primary:hover { background: var(--hx-cta); color: var(--hx-cta-text); border-color: var(--hx-cta); filter: brightness(1.12); }
    .hx-btn--ghost { border-color: transparent; background: transparent; color: var(--hx-muted); }

    /* ---------- Alerts ---------- */
    .hx-alert {
        display: flex; gap: 12px; padding: 14px 16px; border-radius: 14px;
        margin-bottom: 20px; border: 1px solid transparent; font-size: .95rem;
    }
    .hx-alert i { font-size: 1.1rem; margin-top: 1px; }
    .hx-alert ul { margin: 6px 0 0; padding-left: 18px; }
    .hx-alert--ok { background: var(--hx-ok-soft); color: var(--hx-ok); border-color: color-mix(in srgb, var(--hx-ok) 30%, transparent); }
    .hx-alert--err { background: var(--hx-err-soft); color: var(--hx-err); border-color: color-mix(in srgb, var(--hx-err) 30%, transparent); }
    .hx-alert strong { display: block; }
    .hx-alert-close { margin-left: auto; background: none; border: 0; color: inherit; cursor: pointer; font-size: 1.1rem; }

    /* ---------- Two-column layout ---------- */
    .hx-grid {
        display: grid; gap: 28px; align-items: start;
        grid-template-columns: minmax(0, 1fr) 360px;
    }

    /* ---------- Form panel ---------- */
    .hx-panel {
        background: var(--hx-surface); border: 1px solid var(--hx-line);
        border-radius: 20px; box-shadow: var(--hx-shadow); overflow: hidden;
    }
    .hx-block { padding: 28px 30px; }
    .hx-block + .hx-block { border-top: 1px solid var(--hx-line); }
    .hx-block-title {
        display: flex; align-items: center; gap: 12px; margin: 0 0 4px;
        font-family: var(--hx-display); font-size: 1.15rem; font-weight: 700; color: var(--hx-ink);
    }
    .hx-block-title i {
        display: grid; place-items: center; width: 34px; height: 34px;
        border-radius: 10px; background: var(--hx-sunken); border: 1px solid var(--hx-line);
        color: var(--hx-signal); font-size: 1rem;
    }
    .hx-block-note { margin: 0 0 22px 46px; color: var(--hx-muted); font-size: .9rem; }

    .hx-fields { display: grid; grid-template-columns: repeat(12, 1fr); gap: 20px 18px; }
    .hx-col-12 { grid-column: span 12; }
    .hx-col-8 { grid-column: span 8; }
    .hx-col-6 { grid-column: span 6; }
    .hx-col-4 { grid-column: span 4; }

    .hx-label {
        display: flex; align-items: baseline; justify-content: space-between; gap: 8px;
        margin-bottom: 7px; font-weight: 600; font-size: .92rem; color: var(--hx-ink);
    }
    .hx-req { color: var(--hx-signal); margin-left: 3px; }
    .hx-opt { font-weight: 500; font-size: .78rem; color: var(--hx-muted); }
    .hx-count { font-weight: 500; font-size: .78rem; color: var(--hx-muted); font-variant-numeric: tabular-nums; }

    .hx-input, .hx-select, .hx-textarea {
        width: 100%; min-height: 48px; padding: 0 14px;
        border-radius: 12px; border: 1px solid var(--hx-line-strong);
        background: var(--hx-sunken); color: var(--hx-ink);
        font: 500 .98rem var(--hx-body);
        transition: border-color .15s, box-shadow .15s, background .15s;
        appearance: none; -webkit-appearance: none;
    }
    .hx-select {
        padding-right: 40px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 16 16' fill='%235a6c62'%3E%3Cpath d='M1.5 5.5l6.5 6.5 6.5-6.5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 15px center;
    }
    .hx-textarea { padding: 12px 14px; min-height: 130px; resize: vertical; line-height: 1.55; }
    .hx-input::placeholder, .hx-textarea::placeholder { color: var(--hx-muted); opacity: .75; font-weight: 400; }
    .hx-input:focus, .hx-select:focus, .hx-textarea:focus {
        outline: none; background: var(--hx-surface);
        border-color: var(--hx-signal); box-shadow: 0 0 0 4px var(--hx-focus);
    }
    .hx-select option { background: var(--hx-surface); color: var(--hx-ink); }
    .is-invalid { border-color: var(--hx-err) !important; }
    .hx-error { margin-top: 6px; font-size: .84rem; font-weight: 500; color: var(--hx-err); }
    .hx-help { margin-top: 6px; font-size: .82rem; color: var(--hx-muted); line-height: 1.45; }

    /* Phone field */
    .hx-phone { display: flex; align-items: stretch; }
    .hx-phone-icon {
        display: grid; place-items: center; width: 48px; flex: none;
        border: 1px solid var(--hx-line-strong); border-right: 0;
        border-radius: 12px 0 0 12px; background: var(--hx-surface); color: var(--hx-muted);
    }
    .hx-phone .hx-input {
        border-radius: 0 12px 12px 0;
        font-variant-numeric: tabular-nums; letter-spacing: .02em;
    }
    .hx-phone:focus-within .hx-phone-icon { border-color: var(--hx-signal); color: var(--hx-signal); }

    .hx-swap-row { display: flex; justify-content: center; margin: -6px 0; }
    .hx-swap {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 6px 14px; border-radius: 999px; cursor: pointer;
        background: var(--hx-surface); border: 1px dashed var(--hx-line-strong);
        color: var(--hx-muted); font: 500 .82rem var(--hx-body);
    }
    .hx-swap:hover { color: var(--hx-ink); border-color: var(--hx-muted); }

    /* Segmented status */
    .hx-seg {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(110px, 1fr));
        gap: 6px; padding: 5px; border-radius: 14px;
        background: var(--hx-sunken); border: 1px solid var(--hx-line-strong);
    }
    .hx-seg input { position: absolute; opacity: 0; pointer-events: none; }
    .hx-seg label {
        display: flex; align-items: center; justify-content: center; gap: 8px;
        min-height: 40px; border-radius: 10px; cursor: pointer;
        font-weight: 600; font-size: .92rem; color: var(--hx-muted);
        transition: background .15s, color .15s;
    }
    .hx-seg label::before {
        content: ''; width: 9px; height: 9px; border-radius: 50%;
        background: var(--hx-line-strong);
    }
    .hx-seg input:checked + label { background: var(--hx-surface); color: var(--hx-ink); box-shadow: var(--hx-shadow); }
    .hx-seg input:checked + label.is-live::before { background: var(--hx-ok); box-shadow: 0 0 0 4px var(--hx-ok-soft); }
    .hx-seg input:checked + label.is-off::before { background: var(--hx-warn); box-shadow: 0 0 0 4px var(--hx-warn-soft); }
    .hx-seg input:focus-visible + label { outline: 3px solid var(--hx-focus); outline-offset: 2px; }

    /* ---------- Sticky action bar ---------- */
    .hx-bar {
        position: sticky; bottom: 0; z-index: 5;
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        padding: 14px 30px; border-top: 1px solid var(--hx-line);
        background: color-mix(in srgb, var(--hx-surface) 92%, transparent);
        backdrop-filter: blur(8px);
    }
    .hx-state { display: flex; align-items: center; gap: 9px; margin-right: auto; font-size: .88rem; color: var(--hx-muted); }
    .hx-state-dot { width: 9px; height: 9px; border-radius: 50%; background: var(--hx-line-strong); }
    .hx-state.is-dirty { color: var(--hx-ink); }
    .hx-state.is-dirty .hx-state-dot { background: var(--hx-signal); box-shadow: 0 0 0 4px var(--hx-signal-soft); }

    /* ---------- Live preview (resident view) ---------- */
    .hx-side { position: sticky; top: 20px; display: grid; gap: 14px; }
    .hx-side-label {
        display: flex; align-items: center; justify-content: space-between;
        font-size: .85rem; font-weight: 600; color: var(--hx-muted);
    }
    .hx-live { display: inline-flex; align-items: center; gap: 7px; }
    .hx-live::before {
        content: ''; width: 8px; height: 8px; border-radius: 50%;
        background: var(--hx-signal); animation: hx-pulse 1.8s ease-out infinite;
    }
    @keyframes hx-pulse {
        0% { box-shadow: 0 0 0 0 var(--hx-focus); }
        100% { box-shadow: 0 0 0 9px transparent; }
    }

    .hx-card {
        --tone: 210;
        position: relative; overflow: hidden;
        background: var(--hx-surface); border: 1px solid var(--hx-line);
        border-radius: 22px; box-shadow: var(--hx-shadow);
    }
    .hx-card-top {
        padding: 22px 22px 18px;
        background: linear-gradient(180deg, hsl(var(--tone) 70% 94%) 0%, var(--hx-surface) 100%);
    }
    [data-theme="dark"] .hx-card-top { background: linear-gradient(180deg, hsl(var(--tone) 40% 18%) 0%, var(--hx-surface) 100%); }
    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .hx-card-top { background: linear-gradient(180deg, hsl(var(--tone) 40% 18%) 0%, var(--hx-surface) 100%); }
    }
    .hx-card-row { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
    .hx-tag {
        padding: 5px 11px; border-radius: 999px; font-size: .78rem; font-weight: 600;
        background: hsl(var(--tone) 65% 88%); color: hsl(var(--tone) 60% 22%);
    }
    [data-theme="dark"] .hx-tag { background: hsl(var(--tone) 40% 28%); color: hsl(var(--tone) 80% 88%); }
    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .hx-tag { background: hsl(var(--tone) 40% 28%); color: hsl(var(--tone) 80% 88%); }
    }
    .hx-pill { display: inline-flex; align-items: center; gap: 6px; font-size: .78rem; font-weight: 600; }
    .hx-pill::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: currentColor; }
    .hx-pill.is-live { color: var(--hx-ok); }
    .hx-pill.is-off { color: var(--hx-warn); }
    .hx-card-name {
        margin: 0; font-family: var(--hx-display); font-weight: 700; font-size: 1.35rem;
        line-height: 1.15; letter-spacing: -.01em; color: var(--hx-ink); overflow-wrap: anywhere;
    }
    .hx-card-name.is-empty, .hx-empty { color: var(--hx-muted); font-weight: 500; }
    .hx-card-person { margin: 6px 0 0; font-size: .9rem; color: var(--hx-muted); }

    .hx-dial { padding: 6px 22px 20px; }
    .hx-number {
        font-family: var(--hx-display); font-weight: 800; font-size: 1.9rem; letter-spacing: -.01em;
        font-variant-numeric: tabular-nums; color: var(--hx-ink); overflow-wrap: anywhere; line-height: 1.1;
    }
    .hx-number.is-empty { color: var(--hx-line-strong); }
    .hx-alt { margin-top: 10px; font-size: .88rem; color: var(--hx-muted); display: flex; gap: 8px; align-items: center; }
    .hx-alt b { color: var(--hx-ink); font-weight: 600; font-variant-numeric: tabular-nums; }

    .hx-info { border-top: 1px dashed var(--hx-line-strong); padding: 16px 22px 20px; display: grid; gap: 14px; }
    .hx-info-item { display: grid; grid-template-columns: 22px 1fr; gap: 10px; font-size: .9rem; line-height: 1.5; }
    .hx-info-item i { color: var(--hx-muted); margin-top: 2px; }
    .hx-info-item span { overflow-wrap: anywhere; color: var(--hx-text); }
    .hx-info-item span.hx-empty { font-style: italic; }

    .hx-side-note {
        padding: 14px 16px; border-radius: 14px; font-size: .84rem; line-height: 1.5;
        background: var(--hx-sunken); border: 1px solid var(--hx-line); color: var(--hx-muted);
    }
    .hx-side-note i { color: var(--hx-signal); margin-right: 6px; }

    /* ---------- Responsive ---------- */
    @media (max-width: 1080px) {
        .hx-grid { grid-template-columns: 1fr; }
        .hx-side { position: static; order: -1; }
    }
    @media (max-width: 640px) {
        .hx-block { padding: 22px 18px; }
        .hx-block-note { margin-left: 0; }
        .hx-col-8, .hx-col-6, .hx-col-4 { grid-column: span 12; }
        .hx-bar { padding: 12px 18px; }
        .hx-bar .hx-btn { flex: 1; }
        .hx-state { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) {
        .hx-live::before { animation: none; }
        .hx-btn, .hx-input, .hx-select, .hx-textarea { transition: none; }
    }
</style>
@endpush


@section('content')

<div class="container-fluid py-4 hx">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <header class="hx-head">
        <div>
            <nav class="hx-crumbs" aria-label="Breadcrumb">
                <a href="{{ route('emergency-hotlines.index') }}">Emergency Hotlines</a>
                <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
                <span>Edit</span>
            </nav>

            <h1 class="hx-title">Edit {{ $emergencyHotline->name }}</h1>
            <p class="hx-sub">
                Keep this contact accurate. Residents rely on it when they need help fast.
            </p>
        </div>

        <div class="hx-head-side">
            <span class="hx-chip">
                <i class="bi bi-hash"></i> Record <b>{{ $emergencyHotline->id }}</b>
            </span>

            @if ($emergencyHotline->updated_at)
                <span class="hx-chip">
                    <i class="bi bi-clock-history"></i>
                    Updated <b>{{ $emergencyHotline->updated_at->diffForHumans() }}</b>
                </span>
            @endif

            <a href="{{ route('emergency-hotlines.index') }}" class="hx-btn">
                <i class="bi bi-arrow-left"></i> Back to hotlines
            </a>
        </div>
    </header>


    {{-- =========================================================
         MESSAGES
    ========================================================== --}}
    @if (session('success'))
        <div class="hx-alert hx-alert--ok" role="status">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="hx-alert-close" aria-label="Dismiss"
                    onclick="this.closest('.hx-alert').remove()">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="hx-alert hx-alert--err" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Fix {{ $errors->count() }} {{ Str::plural('issue', $errors->count()) }} before saving</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif


    <div class="hx-grid">

        {{-- =====================================================
             FORM
        ====================================================== --}}
        <form
            id="hotlineForm"
            method="POST"
            action="{{ route('emergency-hotlines.update', $emergencyHotline) }}"
            class="hx-panel"
            novalidate
        >
            @csrf
            @method('PUT')

            {{-- ---------- Who to call ---------- --}}
            <section class="hx-block">
                <h2 class="hx-block-title"><i class="bi bi-person-badge"></i> Who to call</h2>
                <p class="hx-block-note">The name and type of service residents will see.</p>

                <div class="hx-fields">

                    <div class="hx-col-8">
                        <label for="name" class="hx-label">
                            <span>Hotline or organization name<span class="hx-req">*</span></span>
                            <span class="hx-count" data-count-for="name" data-max="150"></span>
                        </label>
                        <input
                            type="text" id="name" name="name"
                            class="hx-input @error('name') is-invalid @enderror"
                            value="{{ old('name', $emergencyHotline->name) }}"
                            placeholder="e.g. Bani Municipal Police Station"
                            maxlength="150" required autofocus
                        >
                        @error('name') <div class="hx-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="hx-col-4">
                        <label for="category" class="hx-label">
                            <span>Category<span class="hx-req">*</span></span>
                        </label>
                        <select
                            id="category" name="category"
                            class="hx-select @error('category') is-invalid @enderror"
                            required
                        >
                            <option value="">Select category</option>
                            @foreach ($categories as $categoryOption)
                                <option
                                    value="{{ $categoryOption }}"
                                    @selected(old('category', $emergencyHotline->category) === $categoryOption)
                                >{{ $categoryOption }}</option>
                            @endforeach
                        </select>
                        @error('category') <div class="hx-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="hx-col-6">
                        <label for="contact_person" class="hx-label">
                            <span>Contact person</span>
                            <span class="hx-opt">Optional</span>
                        </label>
                        <input
                            type="text" id="contact_person" name="contact_person"
                            class="hx-input @error('contact_person') is-invalid @enderror"
                            value="{{ old('contact_person', $emergencyHotline->contact_person) }}"
                            placeholder="e.g. Officer Juan Dela Cruz"
                            maxlength="150"
                        >
                        <div class="hx-help">The person responsible for this hotline.</div>
                        @error('contact_person') <div class="hx-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="hx-col-6">
                        <span class="hx-label" id="statusLabel">
                            <span>Availability<span class="hx-req">*</span></span>
                        </span>
                        <div class="hx-seg" role="radiogroup" aria-labelledby="statusLabel">
                            @foreach ($statuses as $statusOption)
                                @php $isLive = strtolower($statusOption) === 'active'; @endphp
                                <input
                                    type="radio" name="status"
                                    id="status_{{ $loop->index }}"
                                    value="{{ $statusOption }}"
                                    data-live="{{ $isLive ? '1' : '0' }}"
                                    @checked(old('status', $emergencyHotline->status) === $statusOption)
                                    required
                                >
                                <label for="status_{{ $loop->index }}" class="{{ $isLive ? 'is-live' : 'is-off' }}">
                                    {{ ucfirst($statusOption) }}
                                </label>
                            @endforeach
                        </div>
                        <div class="hx-help">Only active hotlines are offered for emergency use.</div>
                        @error('status') <div class="hx-error">{{ $message }}</div> @enderror
                    </div>

                </div>
            </section>


            {{-- ---------- Numbers ---------- --}}
            <section class="hx-block">
                <h2 class="hx-block-title"><i class="bi bi-telephone-fill"></i> Phone numbers</h2>
                <p class="hx-block-note">The primary number is shown as the main contact number.</p>

                <div class="hx-fields">

                    <div class="hx-col-12">
                        <label for="phone_number" class="hx-label">
                            <span>Primary number<span class="hx-req">*</span></span>
                        </label>
                        <div class="hx-phone">
                            <span class="hx-phone-icon"><i class="bi bi-telephone-fill"></i></span>
                            <input
                                type="text" id="phone_number" name="phone_number"
                                class="hx-input @error('phone_number') is-invalid @enderror"
                                value="{{ old('phone_number', $emergencyHotline->phone_number) }}"
                                placeholder="0912 345 6789"
                                maxlength="50" inputmode="tel" required
                            >
                        </div>
                        @error('phone_number') <div class="hx-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="hx-col-12 hx-swap-row">
                        <button type="button" class="hx-swap" id="swapNumbers">
                            <i class="bi bi-arrow-down-up"></i> Swap primary and alternate
                        </button>
                    </div>

                    <div class="hx-col-12">
                        <label for="alternate_number" class="hx-label">
                            <span>Alternate number</span>
                            <span class="hx-opt">Optional</span>
                        </label>
                        <div class="hx-phone">
                            <span class="hx-phone-icon"><i class="bi bi-telephone"></i></span>
                            <input
                                type="text" id="alternate_number" name="alternate_number"
                                class="hx-input @error('alternate_number') is-invalid @enderror"
                                value="{{ old('alternate_number', $emergencyHotline->alternate_number) }}"
                                placeholder="Backup number"
                                maxlength="50" inputmode="tel"
                            >
                        </div>
                        <div class="hx-help">A backup line in case the primary number is busy.</div>
                        @error('alternate_number') <div class="hx-error">{{ $message }}</div> @enderror
                    </div>

                </div>
            </section>


            {{-- ---------- Where & when ---------- --}}
            <section class="hx-block">
                <h2 class="hx-block-title"><i class="bi bi-geo-alt-fill"></i> Location and instructions</h2>
                <p class="hx-block-note">Help residents find the office and know when to call.</p>

                <div class="hx-fields">

                    <div class="hx-col-12">
                        <label for="address" class="hx-label">
                            <span>Office or station address</span>
                            <span class="hx-opt">Optional</span>
                        </label>
                        <input
                            type="text" id="address" name="address"
                            class="hx-input @error('address') is-invalid @enderror"
                            value="{{ old('address', $emergencyHotline->address) }}"
                            placeholder="e.g. Barangay Poblacion, Bani, Pangasinan"
                            maxlength="255"
                        >
                        @error('address') <div class="hx-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="hx-col-12">
                        <label for="description" class="hx-label">
                            <span>When to call</span>
                            <span class="hx-count" data-count-for="description"></span>
                        </label>
                        <textarea
                            id="description" name="description"
                            class="hx-textarea @error('description') is-invalid @enderror"
                            placeholder="e.g. Call for police assistance and urgent security incidents."
                        >{{ old('description', $emergencyHotline->description) }}</textarea>
                        <div class="hx-help">Optional. Short instructions on when this hotline should be used.</div>
                        @error('description') <div class="hx-error">{{ $message }}</div> @enderror
                    </div>

                </div>
            </section>


            {{-- ---------- Sticky save bar ---------- --}}
            <div class="hx-bar">
                <div class="hx-state" id="formState" aria-live="polite">
                    <span class="hx-state-dot"></span>
                    <span id="formStateText">No changes yet</span>
                </div>

                <button type="button" class="hx-btn hx-btn--ghost" id="resetForm">
                    <i class="bi bi-arrow-counterclockwise"></i> Undo changes
                </button>

                <a href="{{ route('emergency-hotlines.index') }}" class="hx-btn">Cancel</a>

                <button type="submit" class="hx-btn hx-btn--primary">
                    <i class="bi bi-check2-circle"></i> Save changes
                </button>
            </div>
        </form>


        {{-- =====================================================
             LIVE PREVIEW
        ====================================================== --}}
        <aside class="hx-side" aria-label="Resident view preview">

            <div class="hx-side-label">
                <span>What residents see</span>
                <span class="hx-live">Live preview</span>
            </div>

            <article class="hx-card" id="pvCard">

                <div class="hx-card-top">
                    <div class="hx-card-row">
                        <span class="hx-tag" id="pvCategory">Category</span>
                        <span class="hx-pill is-live" id="pvStatus">Active</span>
                    </div>
                    <h3 class="hx-card-name" id="pvName">Hotline name</h3>
                    <p class="hx-card-person" id="pvPerson" hidden></p>
                </div>

                <div class="hx-dial">
                    <div class="hx-number" id="pvNumber">Phone number</div>
                    <div class="hx-alt" id="pvAltWrap" hidden>
                        <i class="bi bi-telephone"></i> Backup <b id="pvAlt"></b>
                    </div>
                </div>

                <div class="hx-info">
                    <div class="hx-info-item">
                        <i class="bi bi-geo-alt"></i>
                        <span id="pvAddress" class="hx-empty">No address added</span>
                    </div>
                    <div class="hx-info-item">
                        <i class="bi bi-info-circle"></i>
                        <span id="pvDesc" class="hx-empty">No instructions added</span>
                    </div>
                </div>

            </article>

            <div class="hx-side-note">
                <i class="bi bi-lightbulb"></i>
                Changes appear here as you type and are only saved when you press Save changes.
            </div>

        </aside>

    </div>
</div>


<script>
(function () {
    const form = document.getElementById('hotlineForm');
    if (!form) return;

    const $ = (id) => document.getElementById(id);
    const f = {
        name: $('name'), category: $('category'), person: $('contact_person'),
        phone: $('phone_number'), alt: $('alternate_number'),
        address: $('address'), desc: $('description'),
    };
    const pv = {
        card: $('pvCard'), name: $('pvName'), category: $('pvCategory'), status: $('pvStatus'),
        person: $('pvPerson'), number: $('pvNumber'), alt: $('pvAlt'), altWrap: $('pvAltWrap'),
        address: $('pvAddress'), desc: $('pvDesc'),
    };

    /* Category -> stable hue so each type of service keeps its own tone */
    function hueFor(text) {
        let h = 0;
        for (let i = 0; i < text.length; i++) h = (h * 31 + text.charCodeAt(i)) % 360;
        return 150 + (h % 110); /* green to blue only: no red/pink tones */
    }

    function setText(el, value, emptyText) {
        const v = (value || '').trim();
        el.textContent = v || emptyText;
        el.classList.toggle('is-empty', !v);
        if (el.classList.contains('hx-empty') || emptyText) {
            el.classList.toggle('hx-empty', !v && el !== pv.name && el !== pv.number);
        }
    }

    function render() {
        setText(pv.name, f.name.value, 'Hotline name');
        setText(pv.number, f.phone.value, 'Phone number');
        setText(pv.address, f.address.value, 'No address added');
        setText(pv.desc, f.desc.value, 'No instructions added');

        const catText = f.category.value ? f.category.value : 'Category';
        pv.category.textContent = catText;
        pv.card.style.setProperty('--tone', f.category.value ? hueFor(f.category.value) : 210);

        const person = f.person.value.trim();
        pv.person.hidden = !person;
        pv.person.textContent = person ? 'Contact: ' + person : '';

        const alt = f.alt.value.trim();
        pv.altWrap.hidden = !alt;
        pv.alt.textContent = alt;

        const checked = form.querySelector('input[name="status"]:checked');
        if (checked) {
            const live = checked.dataset.live === '1';
            pv.status.textContent = checked.nextElementSibling.textContent.trim();
            pv.status.className = 'hx-pill ' + (live ? 'is-live' : 'is-off');
        }
    }

    /* Character counters */
    function updateCounts() {
        document.querySelectorAll('[data-count-for]').forEach((el) => {
            const input = $(el.dataset.countFor);
            const max = el.dataset.max;
            el.textContent = input.value.length + (max ? ' / ' + max : ' characters');
        });
    }

    /* Unsaved-change tracking */
    const snapshot = () => JSON.stringify(Array.from(new FormData(form).entries()).filter(([k]) => k !== '_token'));
    const initial = snapshot();
    const state = $('formState');
    const stateText = $('formStateText');
    let submitting = false;

    function checkDirty() {
        const dirty = snapshot() !== initial;
        state.classList.toggle('is-dirty', dirty);
        stateText.textContent = dirty ? 'You have unsaved changes' : 'No changes yet';
        return dirty;
    }

    function refresh() { render(); updateCounts(); checkDirty(); }

    form.addEventListener('input', refresh);
    form.addEventListener('change', refresh);

    /* Swap numbers */
    $('swapNumbers').addEventListener('click', () => {
        const a = f.phone.value;
        f.phone.value = f.alt.value;
        f.alt.value = a;
        refresh();
    });

    /* Undo changes */
    $('resetForm').addEventListener('click', () => {
        form.reset();
        refresh();
    });

    /* Warn before leaving with unsaved edits */
    form.addEventListener('submit', () => { submitting = true; });
    window.addEventListener('beforeunload', (e) => {
        if (!submitting && checkDirty()) { e.preventDefault(); e.returnValue = ''; }
    });

    refresh();
})();
</script>

@endsection
