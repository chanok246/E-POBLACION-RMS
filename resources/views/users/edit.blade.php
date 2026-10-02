@extends('layouts.app')

@section('title', 'Edit User | e-Poblacion')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    /* ============================================================
       EDIT USER — "Account desk" layout
       Form on the left, live account card + pending changes on the right.
       Supports data-theme: light / dark / system
       ============================================================ */

    .eu {
        --eu-display: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
        --eu-body: 'Figtree', 'Segoe UI', system-ui, sans-serif;

        --eu-surface: #ffffff;
        --eu-sunken: #f4f7f6;
        --eu-line: #e0e7e5;
        --eu-line-strong: #c8d3d0;
        --eu-ink: #0e2a33;
        --eu-text: #2b4149;
        --eu-muted: #66808a;

        --eu-teal: #0f766e;
        --eu-teal-soft: #dff3ef;
        --eu-indigo: #4f5fd1;
        --eu-indigo-soft: #e6e9fb;
        --eu-ok: #178a55;
        --eu-ok-soft: #e0f4ea;
        --eu-coral: #d4492c;
        --eu-coral-soft: #fbe6e0;
        --eu-sun: #b7790a;
        --eu-sun-bar: #e9a80c;
        --eu-sun-soft: #fbf0d0;

        --eu-focus: rgba(15, 118, 110, .3);
        --eu-shadow: 0 1px 2px rgba(14, 42, 51, .05), 0 8px 24px rgba(14, 42, 51, .06);
        --eu-av-bg: 88%;
        --eu-av-s: 60%;
        --eu-av-fg: 26%;

        font-family: var(--eu-body);
        color: var(--eu-text);
    }

    [data-theme="dark"] .eu {
        --eu-surface: #14262d;
        --eu-sunken: #0f1f25;
        --eu-line: #25404a;
        --eu-line-strong: #33525d;
        --eu-ink: #eaf5f3;
        --eu-text: #c9dbde;
        --eu-muted: #8aa5ac;
        --eu-teal: #3dd0c0;
        --eu-teal-soft: #123a38;
        --eu-indigo: #8f9bff;
        --eu-indigo-soft: #1f2447;
        --eu-ok: #56d99b;
        --eu-ok-soft: #123626;
        --eu-coral: #ff8a6b;
        --eu-coral-soft: #3d2019;
        --eu-sun: #f3c14a;
        --eu-sun-bar: #f3c14a;
        --eu-sun-soft: #3a3016;
        --eu-focus: rgba(61, 208, 192, .35);
        --eu-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 8px 24px rgba(0, 0, 0, .3);
        --eu-av-bg: 26%;
        --eu-av-s: 38%;
        --eu-av-fg: 82%;
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .eu {
            --eu-surface: #14262d;
            --eu-sunken: #0f1f25;
            --eu-line: #25404a;
            --eu-line-strong: #33525d;
            --eu-ink: #eaf5f3;
            --eu-text: #c9dbde;
            --eu-muted: #8aa5ac;
            --eu-teal: #3dd0c0;
            --eu-teal-soft: #123a38;
            --eu-indigo: #8f9bff;
            --eu-indigo-soft: #1f2447;
            --eu-ok: #56d99b;
            --eu-ok-soft: #123626;
            --eu-coral: #ff8a6b;
            --eu-coral-soft: #3d2019;
            --eu-sun: #f3c14a;
            --eu-sun-bar: #f3c14a;
            --eu-sun-soft: #3a3016;
            --eu-focus: rgba(61, 208, 192, .35);
            --eu-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 8px 24px rgba(0, 0, 0, .3);
            --eu-av-bg: 26%;
            --eu-av-s: 38%;
            --eu-av-fg: 82%;
        }
    }

    .eu *, .eu *::before, .eu *::after { box-sizing: border-box; }
    .eu a { text-decoration: none; }
    .eu :focus-visible { outline: 3px solid var(--eu-focus); outline-offset: 2px; border-radius: 8px; }

    /* ---------- Header ---------- */
    .eu-head { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; justify-content: space-between; margin-bottom: 26px; }
    .eu-crumbs { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 10px; font-size: .85rem; color: var(--eu-muted); }
    .eu-crumbs a { color: var(--eu-muted); }
    .eu-crumbs a:hover { color: var(--eu-ink); text-decoration: underline; }
    .eu-title {
        margin: 0; font-family: var(--eu-display); font-weight: 800;
        font-size: clamp(1.7rem, 3.2vw, 2.4rem); letter-spacing: -.02em; line-height: 1.05; color: var(--eu-ink); overflow-wrap: anywhere;
    }
    .eu-sub { margin: 8px 0 0; color: var(--eu-muted); max-width: 54ch; }
    .eu-head-side { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    .eu-meta {
        display: inline-flex; align-items: center; gap: 6px; padding: 7px 12px; border-radius: 999px;
        background: var(--eu-surface); border: 1px solid var(--eu-line); font-size: .82rem; color: var(--eu-text);
    }
    .eu-meta b { color: var(--eu-ink); font-weight: 600; }

    .eu-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        min-height: 44px; padding: 0 18px; border-radius: 12px; cursor: pointer;
        font: 600 .95rem var(--eu-body); background: var(--eu-ink); color: var(--eu-surface);
        border: 1px solid var(--eu-ink); transition: filter .15s, transform .1s, background .15s;
    }
    .eu-btn:hover { filter: brightness(1.15); color: var(--eu-surface); }
    .eu-btn:active { transform: translateY(1px); }
    .eu-btn--line { background: transparent; color: var(--eu-ink); border-color: var(--eu-line-strong); }
    .eu-btn--line:hover { background: var(--eu-sunken); color: var(--eu-ink); filter: none; }
    .eu-btn--ghost { background: transparent; border-color: transparent; color: var(--eu-muted); }
    .eu-btn--ghost:hover { background: var(--eu-sunken); color: var(--eu-ink); filter: none; }

    /* ---------- Alert ---------- */
    .eu-alert {
        display: flex; gap: 12px; padding: 14px 16px; border-radius: 14px; margin-bottom: 20px; font-size: .95rem;
        background: var(--eu-coral-soft); color: var(--eu-coral);
        border: 1px solid color-mix(in srgb, var(--eu-coral) 30%, transparent);
    }
    .eu-alert i { font-size: 1.1rem; margin-top: 1px; }
    .eu-alert strong { display: block; }
    .eu-alert ul { margin: 6px 0 0; padding-left: 18px; }
    .eu-alert-close { margin-left: auto; background: none; border: 0; color: inherit; cursor: pointer; font-size: 1.2rem; line-height: 1; }

    /* ---------- Layout ---------- */
    .eu-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 28px; align-items: start; }
    .eu-panel { background: var(--eu-surface); border: 1px solid var(--eu-line); border-radius: 22px; box-shadow: var(--eu-shadow); overflow: hidden; }
    .eu-block { padding: 28px 30px; }
    .eu-block + .eu-block { border-top: 1px solid var(--eu-line); }
    .eu-block-title { display: flex; align-items: center; gap: 12px; margin: 0 0 4px; font-family: var(--eu-display); font-weight: 700; font-size: 1.15rem; color: var(--eu-ink); }
    .eu-block-title i {
        display: grid; place-items: center; width: 34px; height: 34px; border-radius: 10px;
        background: var(--eu-teal-soft); color: var(--eu-teal); font-size: 1rem;
    }
    .eu-block-note { margin: 0 0 22px 46px; color: var(--eu-muted); font-size: .9rem; }

    .eu-fields { display: grid; grid-template-columns: repeat(12, 1fr); gap: 20px 18px; }
    .eu-c12 { grid-column: span 12; }
    .eu-c6 { grid-column: span 6; }

    .eu-label { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; margin-bottom: 7px; font-weight: 600; font-size: .92rem; color: var(--eu-ink); }
    .eu-req { color: var(--eu-coral); margin-left: 3px; }
    .eu-opt { font-weight: 500; font-size: .78rem; color: var(--eu-muted); }

    .eu-input, .eu-select {
        width: 100%; min-height: 48px; padding: 0 14px; border-radius: 12px;
        border: 1px solid var(--eu-line-strong); background: var(--eu-sunken); color: var(--eu-ink);
        font: 500 .98rem var(--eu-body); appearance: none; -webkit-appearance: none;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .eu-select {
        padding-right: 40px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 16 16' fill='%2366808a'%3E%3Cpath d='M1.5 5.5l6.5 6.5 6.5-6.5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 15px center;
    }
    .eu-input::placeholder { color: var(--eu-muted); opacity: .75; font-weight: 400; }
    .eu-input:focus, .eu-select:focus { outline: none; background: var(--eu-surface); border-color: var(--eu-teal); box-shadow: 0 0 0 4px var(--eu-focus); }
    .eu-select option { background: var(--eu-surface); color: var(--eu-ink); }
    .is-invalid { border-color: var(--eu-coral) !important; }
    .eu-error { margin-top: 6px; font-size: .84rem; font-weight: 500; color: var(--eu-coral); }
    .eu-help { margin-top: 6px; font-size: .82rem; color: var(--eu-muted); line-height: 1.45; }
    .eu-help.is-warn { display: flex; gap: 7px; align-items: flex-start; color: var(--eu-sun); }

    /* Segmented status */
    .eu-seg { display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px; padding: 5px; border-radius: 14px; background: var(--eu-sunken); border: 1px solid var(--eu-line-strong); }
    .eu-seg input { position: absolute; opacity: 0; pointer-events: none; }
    .eu-seg label {
        display: flex; align-items: center; justify-content: center; gap: 8px; min-height: 40px; border-radius: 10px;
        cursor: pointer; font-weight: 600; font-size: .92rem; color: var(--eu-muted); transition: background .15s, color .15s;
    }
    .eu-seg label::before { content: ''; width: 9px; height: 9px; border-radius: 50%; background: var(--eu-line-strong); }
    .eu-seg input:checked + label { background: var(--eu-surface); color: var(--eu-ink); box-shadow: var(--eu-shadow); }
    .eu-seg input:checked + label.is-on::before { background: var(--eu-ok); box-shadow: 0 0 0 4px var(--eu-ok-soft); }
    .eu-seg input:checked + label.is-off::before { background: var(--eu-sun-bar); }
    .eu-seg input:focus-visible + label { outline: 3px solid var(--eu-focus); outline-offset: 2px; }
    .eu-seg input:disabled + label { opacity: .45; cursor: not-allowed; }

    /* Password */
    .eu-pw { position: relative; }
    .eu-pw .eu-input { padding-right: 52px; }
    .eu-eye {
        position: absolute; top: 50%; right: 6px; transform: translateY(-50%); width: 38px; height: 38px;
        display: grid; place-items: center; border: 0; border-radius: 10px; background: transparent; color: var(--eu-muted); cursor: pointer;
    }
    .eu-eye:hover { background: var(--eu-sunken); color: var(--eu-ink); }
    .eu-pw-tools { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; }
    .eu-gen {
        display: inline-flex; align-items: center; gap: 8px; padding: 7px 14px; border-radius: 999px; cursor: pointer;
        background: var(--eu-surface); border: 1px dashed var(--eu-line-strong); color: var(--eu-muted); font: 500 .84rem var(--eu-body);
    }
    .eu-gen:hover { color: var(--eu-ink); border-color: var(--eu-muted); }
    .eu-strength { display: flex; align-items: center; gap: 10px; font-size: .82rem; color: var(--eu-muted); }
    .eu-strength-bars { display: flex; gap: 4px; }
    .eu-strength-bars i { display: block; width: 26px; height: 6px; border-radius: 999px; background: var(--eu-line); transition: background .2s; }
    .eu-strength[data-level="1"] i:nth-child(-n+1) { background: var(--eu-coral); }
    .eu-strength[data-level="2"] i:nth-child(-n+2) { background: var(--eu-sun-bar); }
    .eu-strength[data-level="3"] i:nth-child(-n+3) { background: var(--eu-teal); }
    .eu-strength[data-level="4"] i { background: var(--eu-ok); }
    .eu-match { margin-top: 6px; font-size: .82rem; font-weight: 500; min-height: 1.2em; }
    .eu-match.is-ok { color: var(--eu-ok); }
    .eu-match.is-bad { color: var(--eu-coral); }

    /* ---------- Action bar ---------- */
    .eu-bar {
        position: sticky; bottom: 0; z-index: 5; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        padding: 14px 30px; border-top: 1px solid var(--eu-line);
        background: color-mix(in srgb, var(--eu-surface) 92%, transparent); backdrop-filter: blur(8px);
    }
    .eu-state { display: flex; align-items: center; gap: 9px; margin-right: auto; font-size: .88rem; color: var(--eu-muted); }
    .eu-state-dot { width: 9px; height: 9px; border-radius: 50%; background: var(--eu-line-strong); }
    .eu-state.is-dirty { color: var(--eu-ink); }
    .eu-state.is-dirty .eu-state-dot { background: var(--eu-teal); box-shadow: 0 0 0 4px var(--eu-teal-soft); }

    /* ---------- Side ---------- */
    .eu-side { position: sticky; top: 20px; display: grid; gap: 14px; }
    .eu-side-label { display: flex; align-items: center; justify-content: space-between; font-size: .85rem; font-weight: 600; color: var(--eu-muted); }
    .eu-live { display: inline-flex; align-items: center; gap: 7px; }
    .eu-live::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--eu-teal); animation: eu-pulse 1.8s ease-out infinite; }
    @keyframes eu-pulse { 0% { box-shadow: 0 0 0 0 var(--eu-focus); } 100% { box-shadow: 0 0 0 9px transparent; } }

    .eu-card { --h: 190; overflow: hidden; background: var(--eu-surface); border: 1px solid var(--eu-line); border-radius: 22px; box-shadow: var(--eu-shadow); }
    .eu-card-top { padding: 26px 22px 20px; text-align: center; background: linear-gradient(180deg, hsl(var(--h) 65% 93%) 0%, var(--eu-surface) 100%); }
    [data-theme="dark"] .eu-card-top { background: linear-gradient(180deg, hsl(var(--h) 38% 18%) 0%, var(--eu-surface) 100%); }
    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .eu-card-top { background: linear-gradient(180deg, hsl(var(--h) 38% 18%) 0%, var(--eu-surface) 100%); }
    }
    .eu-avatar {
        display: grid; place-items: center; width: 72px; height: 72px; margin: 0 auto 14px; border-radius: 22px;
        font-family: var(--eu-display); font-weight: 800; font-size: 1.9rem;
        background: hsl(var(--h) var(--eu-av-s) var(--eu-av-bg)); color: hsl(var(--h) 55% var(--eu-av-fg));
        box-shadow: 0 0 0 4px var(--eu-surface);
    }
    .eu-card-name { margin: 0; font-family: var(--eu-display); font-weight: 700; font-size: 1.3rem; color: var(--eu-ink); overflow-wrap: anywhere; line-height: 1.15; }
    .eu-card-name.is-empty { color: var(--eu-muted); font-weight: 500; }
    .eu-card-mail { margin: 5px 0 0; font-size: .88rem; color: var(--eu-text); overflow-wrap: anywhere; }
    .eu-card-tags { display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
    .eu-chip { display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px; border-radius: 999px; font-size: .8rem; font-weight: 700; }
    .eu-chip.is-role { background: var(--eu-teal-soft); color: var(--eu-teal); }
    .eu-chip.is-role.is-captain { background: var(--eu-indigo-soft); color: var(--eu-indigo); }
    .eu-chip.is-on { background: var(--eu-ok-soft); color: var(--eu-ok); }
    .eu-chip.is-off { background: var(--eu-sunken); color: var(--eu-muted); }

    .eu-changes { padding: 16px 22px 20px; border-top: 1px dashed var(--eu-line-strong); }
    .eu-changes h4 { margin: 0 0 12px; font-family: var(--eu-display); font-weight: 700; font-size: .95rem; color: var(--eu-ink); }
    .eu-changes ul { margin: 0; padding: 0; list-style: none; display: grid; gap: 10px; }
    .eu-change { display: grid; gap: 2px; font-size: .86rem; }
    .eu-change-label { color: var(--eu-muted); font-weight: 600; font-size: .78rem; }
    .eu-change-values { color: var(--eu-ink); overflow-wrap: anywhere; }
    .eu-change-values s { color: var(--eu-muted); }
    .eu-change-values i { font-size: .75rem; margin: 0 6px; color: var(--eu-teal); }
    .eu-none { margin: 0; font-size: .86rem; color: var(--eu-muted); }

    .eu-warn {
        display: none; gap: 12px; padding: 14px 16px; border-radius: 16px; font-size: .86rem; line-height: 1.5;
        background: var(--eu-sun-soft); color: var(--eu-sun); border: 1px solid color-mix(in srgb, var(--eu-sun-bar) 40%, transparent);
    }
    .eu-warn.is-shown { display: flex; }
    .eu-warn i { font-size: 1.15rem; margin-top: 1px; }
    .eu-warn b { color: var(--eu-ink); }

    .eu-note { display: flex; gap: 12px; padding: 16px; border-radius: 16px; background: var(--eu-sunken); border: 1px solid var(--eu-line); font-size: .86rem; line-height: 1.55; color: var(--eu-muted); }
    .eu-note i { color: var(--eu-teal); font-size: 1.2rem; margin-top: 1px; }
    .eu-note b { color: var(--eu-ink); }

    /* ---------- Responsive ---------- */
    @media (max-width: 1080px) {
        .eu-grid { grid-template-columns: 1fr; }
        .eu-side { position: static; order: -1; }
    }
    @media (max-width: 640px) {
        .eu-block { padding: 22px 18px; }
        .eu-block-note { margin-left: 0; }
        .eu-c6 { grid-column: span 12; }
        .eu-bar { padding: 12px 18px; }
        .eu-bar .eu-btn { flex: 1; }
        .eu-state { width: 100%; }
        .eu-head-side, .eu-head-side .eu-btn { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) {
        .eu-live::before { animation: none; }
        .eu-btn, .eu-input, .eu-select, .eu-strength-bars i { transition: none; }
    }
</style>
@endpush


@section('content')

@php
    $isSelf = $user->id === auth()->id();
    $currentStatus = old('status', $user->status);
@endphp

<div class="container-fluid py-4 eu">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <header class="eu-head">
        <div>
            <nav class="eu-crumbs" aria-label="Breadcrumb">
                <a href="{{ route('users.index') }}">User Management</a>
                <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
                <a href="{{ route('users.show', $user) }}">{{ $user->name }}</a>
                <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
                <span>Edit</span>
            </nav>

            <h1 class="eu-title">Edit {{ $user->name }}</h1>
            <p class="eu-sub">Update personnel account information and access status.</p>
        </div>

        <div class="eu-head-side">
            @if ($user->updated_at)
                <span class="eu-meta"><i class="bi bi-clock-history"></i> Updated <b>{{ $user->updated_at->diffForHumans() }}</b></span>
            @endif

            <a href="{{ route('users.show', $user) }}" class="eu-btn eu-btn--line">
                <i class="bi bi-arrow-left"></i> Back to user
            </a>
        </div>
    </header>


    {{-- =========================================================
         VALIDATION ERRORS
    ========================================================== --}}
    @if ($errors->any())
        <div class="eu-alert" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Fix {{ $errors->count() }} {{ Str::plural('issue', $errors->count()) }} before saving</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="eu-alert-close" aria-label="Dismiss" onclick="this.closest('.eu-alert').remove()">&times;</button>
        </div>
    @endif


    <div class="eu-grid">

        {{-- =====================================================
             FORM
        ====================================================== --}}
        <form id="userForm" action="{{ route('users.update', $user) }}" method="POST" class="eu-panel" autocomplete="off">
            @csrf
            @method('PUT')

            {{-- ---------- Details ---------- --}}
            <section class="eu-block">
                <h2 class="eu-block-title"><i class="bi bi-person-fill"></i> Account details</h2>
                <p class="eu-block-note">The name and email this person is known by in the system.</p>

                <div class="eu-fields">

                    <div class="eu-c6">
                        <label for="name" class="eu-label">
                            <span>Full name<span class="eu-req">*</span></span>
                        </label>
                        <input
                            type="text" name="name" id="name"
                            class="eu-input @error('name') is-invalid @enderror"
                            value="{{ old('name', $user->name) }}"
                            maxlength="255" required autofocus
                        >
                        @error('name') <div class="eu-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="eu-c6">
                        <label for="email" class="eu-label">
                            <span>Email address<span class="eu-req">*</span></span>
                        </label>
                        <input
                            type="email" name="email" id="email"
                            class="eu-input @error('email') is-invalid @enderror"
                            value="{{ old('email', $user->email) }}"
                            maxlength="255" required
                        >
                        @error('email') <div class="eu-error">{{ $message }}</div> @enderror
                    </div>

                </div>
            </section>


            {{-- ---------- Access ---------- --}}
            <section class="eu-block">
                <h2 class="eu-block-title"><i class="bi bi-shield-check"></i> Access</h2>
                <p class="eu-block-note">Their role and whether the account can sign in.</p>

                <div class="eu-fields">

                    <div class="eu-c6">
                        <label for="role" class="eu-label">
                            <span>System role<span class="eu-req">*</span></span>
                        </label>
                        <select name="role" id="role" class="eu-select @error('role') is-invalid @enderror" required>
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role', $user->role) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="eu-help">The role decides what this person can access in the system.</div>
                        @error('role') <div class="eu-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="eu-c6">
                        <span class="eu-label" id="statusLabel">
                            <span>Account status<span class="eu-req">*</span></span>
                        </span>
                        <div class="eu-seg" role="radiogroup" aria-labelledby="statusLabel">
                            <input type="radio" name="status" id="status_active" value="active"
                                   {{ $currentStatus === 'active' ? 'checked' : '' }} required>
                            <label for="status_active" class="is-on">Active</label>

                            <input type="radio" name="status" id="status_inactive" value="inactive"
                                   {{ $currentStatus === 'inactive' ? 'checked' : '' }}
                                   {{ $isSelf && $currentStatus === 'active' ? 'disabled' : '' }}>
                            <label for="status_inactive" class="is-off">Inactive</label>
                        </div>

                        @if ($isSelf)
                            <div class="eu-help is-warn">
                                <i class="bi bi-exclamation-triangle"></i>
                                <span>You cannot deactivate your own account.</span>
                            </div>
                        @else
                            <div class="eu-help">Inactive accounts cannot sign in until they are activated.</div>
                        @endif

                        @error('status') <div class="eu-error">{{ $message }}</div> @enderror
                    </div>

                </div>
            </section>


            {{-- ---------- Password ---------- --}}
            <section class="eu-block">
                <h2 class="eu-block-title"><i class="bi bi-key-fill"></i> Password</h2>
                <p class="eu-block-note">Leave both fields blank to keep the current password.</p>

                <div class="eu-fields">

                    <div class="eu-c6">
                        <label for="password" class="eu-label">
                            <span>New password</span>
                            <span class="eu-opt">Optional</span>
                        </label>
                        <div class="eu-pw">
                            <input
                                type="password" name="password" id="password"
                                class="eu-input @error('password') is-invalid @enderror"
                                placeholder="Leave blank to keep current"
                                minlength="8" autocomplete="new-password"
                            >
                            <button type="button" class="eu-eye" data-toggle="password" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password') <div class="eu-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="eu-c6">
                        <label for="password_confirmation" class="eu-label">
                            <span>Confirm new password</span>
                        </label>
                        <div class="eu-pw">
                            <input
                                type="password" name="password_confirmation" id="password_confirmation"
                                class="eu-input"
                                placeholder="Re-enter new password"
                                minlength="8" autocomplete="new-password"
                            >
                            <button type="button" class="eu-eye" data-toggle="password_confirmation" aria-label="Show confirmation">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="eu-match" id="pwMatch" aria-live="polite"></div>
                    </div>

                    <div class="eu-c12 eu-pw-tools" style="margin-top:-6px">
                        <button type="button" class="eu-gen" id="pwGenerate">
                            <i class="bi bi-magic"></i> Suggest a strong password
                        </button>

                        <div class="eu-strength" id="pwStrength" data-level="0">
                            <span class="eu-strength-bars"><i></i><i></i><i></i><i></i></span>
                            <span id="pwStrengthText">Strength</span>
                        </div>
                    </div>

                </div>
            </section>


            {{-- ---------- Action bar ---------- --}}
            <div class="eu-bar">
                <div class="eu-state" id="formState" aria-live="polite">
                    <span class="eu-state-dot"></span>
                    <span id="formStateText">No changes yet</span>
                </div>

                <button type="button" class="eu-btn eu-btn--ghost" id="resetForm">
                    <i class="bi bi-arrow-counterclockwise"></i> Undo changes
                </button>

                <a href="{{ route('users.show', $user) }}" class="eu-btn eu-btn--line">Cancel</a>

                <button type="submit" class="eu-btn">
                    <i class="bi bi-check-lg"></i> Save changes
                </button>
            </div>
        </form>


        {{-- =====================================================
             LIVE ACCOUNT CARD + PENDING CHANGES
        ====================================================== --}}
        <aside class="eu-side" aria-label="Account preview">

            <div class="eu-side-label">
                <span>This account</span>
                <span class="eu-live">Live preview</span>
            </div>

            <article class="eu-card" id="pvCard">
                <div class="eu-card-top">
                    <div class="eu-avatar" id="pvAvatar" aria-hidden="true">?</div>
                    <h3 class="eu-card-name" id="pvName">{{ $user->name }}</h3>
                    <p class="eu-card-mail" id="pvMail">{{ $user->email }}</p>

                    <div class="eu-card-tags">
                        <span class="eu-chip is-role" id="pvRole">{{ ucfirst($user->role) }}</span>
                        <span class="eu-chip is-on" id="pvStatus">Active</span>
                    </div>
                </div>

                <div class="eu-changes">
                    <h4>Pending changes</h4>
                    <p class="eu-none" id="changesNone">Nothing changed yet. Edit a field to see it here.</p>
                    <ul id="changesList" hidden></ul>
                </div>
            </article>

            <div class="eu-warn" id="warnDeactivate" role="status">
                <i class="bi bi-person-x-fill"></i>
                <div><b>Heads up:</b> once saved, this person will no longer be able to sign in.</div>
            </div>

            <div class="eu-note">
                <i class="bi bi-info-circle"></i>
                <div>
                    <b>Current account</b><br>
                    {{ $user->name }}, {{ ucfirst($user->role) }},
                    {{ $user->isActive() ? 'Active' : 'Inactive' }}.
                    Changes only apply when you press Save changes.
                </div>
            </div>

        </aside>

    </div>
</div>


<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const form = $('userForm');
    if (!form) return;

    const f = {
        name: $('name'), mail: $('email'), role: $('role'),
        pw: $('password'), pw2: $('password_confirmation'),
    };
    const active = $('status_active');
    const inactive = $('status_inactive');

    const pv = {
        card: $('pvCard'), avatar: $('pvAvatar'), name: $('pvName'),
        mail: $('pvMail'), role: $('pvRole'), status: $('pvStatus'),
    };

    /* Original values, from the saved record on load */
    const original = {
        name: f.name.value,
        mail: f.mail.value,
        role: f.role.value,
        status: active.checked ? 'active' : 'inactive',
    };
    const roleText = (el) => el.options[el.selectedIndex] ? el.options[el.selectedIndex].textContent.trim() : '';
    const originalRoleText = roleText(f.role);

    function hueFor(text) {
        let h = 0;
        for (let i = 0; i < text.length; i++) h = (h * 31 + text.charCodeAt(i)) % 360;
        return h;
    }

    function passwordScore(v) {
        let s = 0;
        if (v.length >= 8) s++;
        if (v.length >= 12) s++;
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
        if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
        return v.length === 0 ? 0 : Math.max(1, s);
    }
    const strengthWords = ['Strength', 'Weak', 'Fair', 'Good', 'Strong'];

    const changesList = $('changesList');
    const changesNone = $('changesNone');
    const state = $('formState');
    const stateText = $('formStateText');
    let submitting = false;

    function change(label, from, to) {
        const li = document.createElement('li');
        li.className = 'eu-change';

        const l = document.createElement('span');
        l.className = 'eu-change-label';
        l.textContent = label;

        const v = document.createElement('span');
        v.className = 'eu-change-values';

        if (from !== null) {
            const s = document.createElement('s');
            s.textContent = from || 'empty';
            const arrow = document.createElement('i');
            arrow.className = 'bi bi-arrow-right';
            v.appendChild(s);
            v.appendChild(arrow);
        }
        v.appendChild(document.createTextNode(to));

        li.appendChild(l);
        li.appendChild(v);
        return li;
    }

    function render() {
        const name = f.name.value.trim();
        const mail = f.mail.value.trim();
        const status = inactive.checked ? 'inactive' : 'active';

        /* Card */
        pv.name.textContent = name || 'Full name';
        pv.name.classList.toggle('is-empty', !name);
        pv.avatar.textContent = name ? name.charAt(0).toUpperCase() : '?';
        pv.card.style.setProperty('--h', name ? hueFor(name) : 190);
        pv.mail.textContent = mail;

        pv.role.textContent = roleText(f.role);
        pv.role.className = 'eu-chip is-role' + (f.role.value.toLowerCase() === 'captain' ? ' is-captain' : '');

        pv.status.textContent = status === 'active' ? 'Active' : 'Inactive';
        pv.status.className = 'eu-chip ' + (status === 'active' ? 'is-on' : 'is-off');

        $('warnDeactivate').classList.toggle('is-shown', status === 'inactive' && original.status === 'active');

        /* Password feedback */
        const pw = f.pw.value;
        const pw2 = f.pw2.value;
        const score = passwordScore(pw);
        $('pwStrength').dataset.level = score;
        $('pwStrengthText').textContent = strengthWords[score];

        const match = $('pwMatch');
        if (!pw2) {
            match.textContent = '';
            match.className = 'eu-match';
        } else if (pw === pw2) {
            match.textContent = 'Passwords match';
            match.className = 'eu-match is-ok';
        } else {
            match.textContent = 'Passwords do not match yet';
            match.className = 'eu-match is-bad';
        }

        /* Pending changes */
        const items = [];
        if (f.name.value.trim() !== original.name.trim()) items.push(change('Full name', original.name, name));
        if (f.mail.value.trim() !== original.mail.trim()) items.push(change('Email address', original.mail, mail));
        if (f.role.value !== original.role) items.push(change('System role', originalRoleText, roleText(f.role)));
        if (status !== original.status) items.push(change('Account status', original.status === 'active' ? 'Active' : 'Inactive', status === 'active' ? 'Active' : 'Inactive'));
        if (pw.length > 0) items.push(change('Password', null, 'Will be changed'));

        changesList.replaceChildren(...items);
        changesList.hidden = items.length === 0;
        changesNone.hidden = items.length > 0;

        state.classList.toggle('is-dirty', items.length > 0);
        stateText.textContent = items.length === 0
            ? 'No changes yet'
            : items.length + ' unsaved ' + (items.length === 1 ? 'change' : 'changes');

        return items.length;
    }

    form.addEventListener('input', render);
    form.addEventListener('change', render);

    /* Show / hide password */
    document.querySelectorAll('[data-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const input = $(btn.dataset.toggle);
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-label', (show ? 'Hide ' : 'Show ') + (btn.dataset.toggle === 'password' ? 'password' : 'confirmation'));
            btn.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
        });
    });

    /* Suggest a strong password (revealed so it can be shared) */
    $('pwGenerate').addEventListener('click', () => {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*?';
        const bytes = new Uint32Array(14);
        crypto.getRandomValues(bytes);
        const value = Array.from(bytes, (n) => chars[n % chars.length]).join('');

        f.pw.value = value;
        f.pw2.value = value;
        [f.pw, f.pw2].forEach((input) => { input.type = 'text'; });
        document.querySelectorAll('[data-toggle]').forEach((btn) => { btn.innerHTML = '<i class="bi bi-eye-slash"></i>'; });
        render();
    });

    /* Undo changes back to the saved values */
    $('resetForm').addEventListener('click', () => {
        f.name.value = original.name;
        f.mail.value = original.mail;
        f.role.value = original.role;
        (original.status === 'active' ? active : inactive).checked = true;
        f.pw.value = '';
        f.pw2.value = '';
        [f.pw, f.pw2].forEach((input) => { input.type = 'password'; });
        document.querySelectorAll('[data-toggle]').forEach((btn) => { btn.innerHTML = '<i class="bi bi-eye"></i>'; });
        render();
    });

    /* Warn before leaving with unsaved edits */
    form.addEventListener('submit', () => { submitting = true; });
    window.addEventListener('beforeunload', (e) => {
        if (!submitting && render() > 0) { e.preventDefault(); e.returnValue = ''; }
    });

    render();
})();
</script>

@endsection
