@extends('layouts.app')

@section('title', 'Add User | e-Poblacion')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    /* ============================================================
       ADD USER — "New account" layout
       Form on the left, live account card + checklist on the right.
       Supports data-theme: light / dark / system
       ============================================================ */

    .au {
        --au-display: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
        --au-body: 'Figtree', 'Segoe UI', system-ui, sans-serif;

        --au-surface: #ffffff;
        --au-sunken: #f4f7f6;
        --au-line: #e0e7e5;
        --au-line-strong: #c8d3d0;
        --au-ink: #0e2a33;
        --au-text: #2b4149;
        --au-muted: #66808a;

        --au-teal: #0f766e;
        --au-teal-soft: #dff3ef;
        --au-indigo: #4f5fd1;
        --au-indigo-soft: #e6e9fb;
        --au-ok: #178a55;
        --au-ok-soft: #e0f4ea;
        --au-coral: #d4492c;
        --au-coral-soft: #fbe6e0;
        --au-sun: #e9a80c;

        --au-focus: rgba(15, 118, 110, .3);
        --au-shadow: 0 1px 2px rgba(14, 42, 51, .05), 0 8px 24px rgba(14, 42, 51, .06);
        --au-av-bg: 88%;
        --au-av-s: 60%;
        --au-av-fg: 26%;

        font-family: var(--au-body);
        color: var(--au-text);
    }

    [data-theme="dark"] .au {
        --au-surface: #14262d;
        --au-sunken: #0f1f25;
        --au-line: #25404a;
        --au-line-strong: #33525d;
        --au-ink: #eaf5f3;
        --au-text: #c9dbde;
        --au-muted: #8aa5ac;
        --au-teal: #3dd0c0;
        --au-teal-soft: #123a38;
        --au-indigo: #8f9bff;
        --au-indigo-soft: #1f2447;
        --au-ok: #56d99b;
        --au-ok-soft: #123626;
        --au-coral: #ff8a6b;
        --au-coral-soft: #3d2019;
        --au-sun: #f3c14a;
        --au-focus: rgba(61, 208, 192, .35);
        --au-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 8px 24px rgba(0, 0, 0, .3);
        --au-av-bg: 26%;
        --au-av-s: 38%;
        --au-av-fg: 82%;
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .au {
            --au-surface: #14262d;
            --au-sunken: #0f1f25;
            --au-line: #25404a;
            --au-line-strong: #33525d;
            --au-ink: #eaf5f3;
            --au-text: #c9dbde;
            --au-muted: #8aa5ac;
            --au-teal: #3dd0c0;
            --au-teal-soft: #123a38;
            --au-indigo: #8f9bff;
            --au-indigo-soft: #1f2447;
            --au-ok: #56d99b;
            --au-ok-soft: #123626;
            --au-coral: #ff8a6b;
            --au-coral-soft: #3d2019;
            --au-sun: #f3c14a;
            --au-focus: rgba(61, 208, 192, .35);
            --au-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 8px 24px rgba(0, 0, 0, .3);
            --au-av-bg: 26%;
            --au-av-s: 38%;
            --au-av-fg: 82%;
        }
    }

    .au *, .au *::before, .au *::after { box-sizing: border-box; }
    .au a { text-decoration: none; }
    .au :focus-visible { outline: 3px solid var(--au-focus); outline-offset: 2px; border-radius: 8px; }

    /* ---------- Header ---------- */
    .au-head {
        display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end;
        justify-content: space-between; margin-bottom: 26px;
    }
    .au-crumbs { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: .85rem; color: var(--au-muted); }
    .au-crumbs a { color: var(--au-muted); }
    .au-crumbs a:hover { color: var(--au-ink); text-decoration: underline; }
    .au-title {
        margin: 0; font-family: var(--au-display); font-weight: 800;
        font-size: clamp(1.8rem, 3.2vw, 2.4rem); letter-spacing: -.02em; line-height: 1.05; color: var(--au-ink);
    }
    .au-sub { margin: 8px 0 0; color: var(--au-muted); max-width: 54ch; }

    .au-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        min-height: 44px; padding: 0 18px; border-radius: 12px; cursor: pointer;
        font: 600 .95rem var(--au-body); background: var(--au-ink); color: var(--au-surface);
        border: 1px solid var(--au-ink); transition: filter .15s, transform .1s, background .15s;
    }
    .au-btn:hover { filter: brightness(1.15); color: var(--au-surface); }
    .au-btn:active { transform: translateY(1px); }
    .au-btn--line { background: transparent; color: var(--au-ink); border-color: var(--au-line-strong); }
    .au-btn--line:hover { background: var(--au-sunken); color: var(--au-ink); filter: none; }

    /* ---------- Alert ---------- */
    .au-alert {
        display: flex; gap: 12px; padding: 14px 16px; border-radius: 14px; margin-bottom: 20px; font-size: .95rem;
        background: var(--au-coral-soft); color: var(--au-coral);
        border: 1px solid color-mix(in srgb, var(--au-coral) 30%, transparent);
    }
    .au-alert i { font-size: 1.1rem; margin-top: 1px; }
    .au-alert strong { display: block; }
    .au-alert ul { margin: 6px 0 0; padding-left: 18px; }
    .au-alert-close { margin-left: auto; background: none; border: 0; color: inherit; cursor: pointer; font-size: 1.2rem; line-height: 1; }

    /* ---------- Layout ---------- */
    .au-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 28px; align-items: start; }

    .au-panel { background: var(--au-surface); border: 1px solid var(--au-line); border-radius: 22px; box-shadow: var(--au-shadow); overflow: hidden; }
    .au-block { padding: 28px 30px; }
    .au-block + .au-block { border-top: 1px solid var(--au-line); }
    .au-block-title { display: flex; align-items: center; gap: 12px; margin: 0 0 4px; font-family: var(--au-display); font-weight: 700; font-size: 1.15rem; color: var(--au-ink); }
    .au-block-title i {
        display: grid; place-items: center; width: 34px; height: 34px; border-radius: 10px;
        background: var(--au-teal-soft); color: var(--au-teal); font-size: 1rem;
    }
    .au-block-note { margin: 0 0 22px 46px; color: var(--au-muted); font-size: .9rem; }

    .au-fields { display: grid; grid-template-columns: repeat(12, 1fr); gap: 20px 18px; }
    .au-c12 { grid-column: span 12; }
    .au-c6 { grid-column: span 6; }

    .au-label { display: flex; align-items: baseline; justify-content: space-between; gap: 8px; margin-bottom: 7px; font-weight: 600; font-size: .92rem; color: var(--au-ink); }
    .au-req { color: var(--au-coral); margin-left: 3px; }
    .au-opt { font-weight: 500; font-size: .78rem; color: var(--au-muted); }

    .au-input, .au-select {
        width: 100%; min-height: 48px; padding: 0 14px; border-radius: 12px;
        border: 1px solid var(--au-line-strong); background: var(--au-sunken); color: var(--au-ink);
        font: 500 .98rem var(--au-body); appearance: none; -webkit-appearance: none;
        transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .au-select {
        padding-right: 40px;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 16 16' fill='%2366808a'%3E%3Cpath d='M1.5 5.5l6.5 6.5 6.5-6.5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: right 15px center;
    }
    .au-input::placeholder { color: var(--au-muted); opacity: .75; font-weight: 400; }
    .au-input:focus, .au-select:focus { outline: none; background: var(--au-surface); border-color: var(--au-teal); box-shadow: 0 0 0 4px var(--au-focus); }
    .au-select option { background: var(--au-surface); color: var(--au-ink); }
    .is-invalid { border-color: var(--au-coral) !important; }
    .au-error { margin-top: 6px; font-size: .84rem; font-weight: 500; color: var(--au-coral); }
    .au-help { margin-top: 6px; font-size: .82rem; color: var(--au-muted); line-height: 1.45; }

    /* Username prefix */
    .au-affix { display: flex; align-items: stretch; }
    .au-affix > span {
        display: grid; place-items: center; width: 44px; flex: none; border: 1px solid var(--au-line-strong); border-right: 0;
        border-radius: 12px 0 0 12px; background: var(--au-surface); color: var(--au-muted); font-weight: 700;
    }
    .au-affix .au-input { border-radius: 0 12px 12px 0; }
    .au-affix:focus-within > span { border-color: var(--au-teal); color: var(--au-teal); }

    /* Segmented status */
    .au-seg { display: grid; grid-template-columns: repeat(2, 1fr); gap: 6px; padding: 5px; border-radius: 14px; background: var(--au-sunken); border: 1px solid var(--au-line-strong); }
    .au-seg input { position: absolute; opacity: 0; pointer-events: none; }
    .au-seg label {
        display: flex; align-items: center; justify-content: center; gap: 8px; min-height: 40px; border-radius: 10px;
        cursor: pointer; font-weight: 600; font-size: .92rem; color: var(--au-muted); transition: background .15s, color .15s;
    }
    .au-seg label::before { content: ''; width: 9px; height: 9px; border-radius: 50%; background: var(--au-line-strong); }
    .au-seg input:checked + label { background: var(--au-surface); color: var(--au-ink); box-shadow: var(--au-shadow); }
    .au-seg input:checked + label.is-on::before { background: var(--au-ok); box-shadow: 0 0 0 4px var(--au-ok-soft); }
    .au-seg input:checked + label.is-off::before { background: var(--au-sun); }
    .au-seg input:focus-visible + label { outline: 3px solid var(--au-focus); outline-offset: 2px; }

    /* Password field */
    .au-pw { position: relative; }
    .au-pw .au-input { padding-right: 52px; }
    .au-eye {
        position: absolute; top: 50%; right: 6px; transform: translateY(-50%); width: 38px; height: 38px;
        display: grid; place-items: center; border: 0; border-radius: 10px; background: transparent;
        color: var(--au-muted); cursor: pointer;
    }
    .au-eye:hover { background: var(--au-sunken); color: var(--au-ink); }
    .au-pw-tools { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-top: 10px; }
    .au-gen {
        display: inline-flex; align-items: center; gap: 8px; padding: 7px 14px; border-radius: 999px; cursor: pointer;
        background: var(--au-surface); border: 1px dashed var(--au-line-strong); color: var(--au-muted); font: 500 .84rem var(--au-body);
    }
    .au-gen:hover { color: var(--au-ink); border-color: var(--au-muted); }
    .au-strength { display: flex; align-items: center; gap: 10px; font-size: .82rem; color: var(--au-muted); }
    .au-strength-bars { display: flex; gap: 4px; }
    .au-strength-bars i { display: block; width: 26px; height: 6px; border-radius: 999px; background: var(--au-line); transition: background .2s; }
    .au-strength[data-level="1"] i:nth-child(-n+1) { background: var(--au-coral); }
    .au-strength[data-level="2"] i:nth-child(-n+2) { background: var(--au-sun); }
    .au-strength[data-level="3"] i:nth-child(-n+3) { background: var(--au-teal); }
    .au-strength[data-level="4"] i { background: var(--au-ok); }
    .au-match { margin-top: 6px; font-size: .82rem; font-weight: 500; min-height: 1.2em; }
    .au-match.is-ok { color: var(--au-ok); }
    .au-match.is-bad { color: var(--au-coral); }

    /* ---------- Action bar ---------- */
    .au-bar {
        position: sticky; bottom: 0; z-index: 5; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        padding: 14px 30px; border-top: 1px solid var(--au-line);
        background: color-mix(in srgb, var(--au-surface) 92%, transparent); backdrop-filter: blur(8px);
    }
    .au-bar-state { margin-right: auto; font-size: .88rem; color: var(--au-muted); }
    .au-bar-state b { color: var(--au-ink); font-variant-numeric: tabular-nums; }

    /* ---------- Side ---------- */
    .au-side { position: sticky; top: 20px; display: grid; gap: 14px; }
    .au-side-label { display: flex; align-items: center; justify-content: space-between; font-size: .85rem; font-weight: 600; color: var(--au-muted); }
    .au-live { display: inline-flex; align-items: center; gap: 7px; }
    .au-live::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--au-teal); animation: au-pulse 1.8s ease-out infinite; }
    @keyframes au-pulse { 0% { box-shadow: 0 0 0 0 var(--au-focus); } 100% { box-shadow: 0 0 0 9px transparent; } }

    .au-card { --h: 190; overflow: hidden; background: var(--au-surface); border: 1px solid var(--au-line); border-radius: 22px; box-shadow: var(--au-shadow); }
    .au-card-top {
        padding: 26px 22px 20px; text-align: center;
        background: linear-gradient(180deg, hsl(var(--h) 65% 93%) 0%, var(--au-surface) 100%);
    }
    [data-theme="dark"] .au-card-top { background: linear-gradient(180deg, hsl(var(--h) 38% 18%) 0%, var(--au-surface) 100%); }
    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .au-card-top { background: linear-gradient(180deg, hsl(var(--h) 38% 18%) 0%, var(--au-surface) 100%); }
    }
    .au-avatar {
        display: grid; place-items: center; width: 72px; height: 72px; margin: 0 auto 14px; border-radius: 22px;
        font-family: var(--au-display); font-weight: 800; font-size: 1.9rem;
        background: hsl(var(--h) var(--au-av-s) var(--au-av-bg)); color: hsl(var(--h) 55% var(--au-av-fg));
        box-shadow: 0 0 0 4px var(--au-surface);
    }
    .au-card-name { margin: 0; font-family: var(--au-display); font-weight: 700; font-size: 1.3rem; color: var(--au-ink); overflow-wrap: anywhere; line-height: 1.15; }
    .au-card-name.is-empty, .au-card-user.is-empty, .au-card-mail.is-empty { color: var(--au-muted); font-weight: 500; }
    .au-card-user { margin: 5px 0 0; color: var(--au-text); font-weight: 500; overflow-wrap: anywhere; }
    .au-card-mail { margin: 3px 0 0; font-size: .85rem; color: var(--au-muted); overflow-wrap: anywhere; }
    .au-card-mail[hidden] { display: none; }
    .au-card-tags { display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
    .au-chip { display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px; border-radius: 999px; font-size: .8rem; font-weight: 700; }
    .au-chip.is-role { background: var(--au-teal-soft); color: var(--au-teal); }
    .au-chip.is-role.is-captain { background: var(--au-indigo-soft); color: var(--au-indigo); }
    .au-chip.is-role.is-empty { background: var(--au-sunken); color: var(--au-muted); }
    .au-chip.is-on { background: var(--au-ok-soft); color: var(--au-ok); }
    .au-chip.is-off { background: var(--au-sunken); color: var(--au-muted); }

    .au-checks { margin: 0; padding: 16px 22px 20px; list-style: none; border-top: 1px dashed var(--au-line-strong); display: grid; gap: 11px; }
    .au-checks li { display: flex; align-items: center; gap: 10px; font-size: .9rem; color: var(--au-muted); }
    .au-checks li i { display: grid; place-items: center; width: 20px; height: 20px; border-radius: 50%; border: 1.5px solid var(--au-line-strong); font-size: .7rem; color: transparent; flex: none; }
    .au-checks li.is-done { color: var(--au-ink); }
    .au-checks li.is-done i { background: var(--au-ok); border-color: var(--au-ok); color: #fff; }

    .au-note { display: flex; gap: 12px; padding: 16px; border-radius: 16px; background: var(--au-sunken); border: 1px solid var(--au-line); font-size: .86rem; line-height: 1.55; color: var(--au-muted); }
    .au-note i { color: var(--au-teal); font-size: 1.2rem; margin-top: 1px; }
    .au-note b { color: var(--au-ink); }

    /* ---------- Responsive ---------- */
    @media (max-width: 1080px) {
        .au-grid { grid-template-columns: 1fr; }
        .au-side { position: static; order: -1; }
    }
    @media (max-width: 640px) {
        .au-block { padding: 22px 18px; }
        .au-block-note { margin-left: 0; }
        .au-c6 { grid-column: span 12; }
        .au-bar { padding: 12px 18px; }
        .au-bar .au-btn { flex: 1; }
        .au-bar-state { width: 100%; }
        .au-head .au-btn { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) {
        .au-live::before { animation: none; }
        .au-btn, .au-input, .au-select, .au-strength-bars i { transition: none; }
    }
</style>
@endpush


@section('content')

<div class="container-fluid py-4 au">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <header class="au-head">
        <div>
            <nav class="au-crumbs" aria-label="Breadcrumb">
                <a href="{{ route('users.index') }}">User Management</a>
                <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
                <span>Add user</span>
            </nav>

            <h1 class="au-title">Add a Secretary</h1>
            <p class="au-sub">Create an authorized Secretary account for the Record Management System.</p>
        </div>

        <a href="{{ route('users.index') }}" class="au-btn au-btn--line">
            <i class="bi bi-arrow-left"></i> Back to users
        </a>
    </header>


    {{-- =========================================================
         VALIDATION ERRORS
    ========================================================== --}}
    @if ($errors->any())
        <div class="au-alert" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Fix {{ $errors->count() }} {{ Str::plural('issue', $errors->count()) }} before creating the account</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="au-alert-close" aria-label="Dismiss" onclick="this.closest('.au-alert').remove()">&times;</button>
        </div>
    @endif


    <div class="au-grid">

        {{-- =====================================================
             FORM
        ====================================================== --}}
        <form id="userForm" action="{{ route('users.store') }}" method="POST" class="au-panel" autocomplete="off">
            @csrf

            {{-- ---------- Who ---------- --}}
            <section class="au-block">
                <h2 class="au-block-title"><i class="bi bi-person-fill"></i> Who is this for?</h2>
                <p class="au-block-note">Their name and how they will sign in.</p>

                <div class="au-fields">

                    <div class="au-c12">
                        <label for="name" class="au-label">
                            <span>Full name<span class="au-req">*</span></span>
                        </label>
                        <input
                            type="text" name="name" id="name"
                            class="au-input @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="e.g. Maria Santos"
                            maxlength="255" required autofocus
                        >
                        @error('name') <div class="au-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="au-c6">
                        <label for="username" class="au-label">
                            <span>Username<span class="au-req">*</span></span>
                        </label>
                        <div class="au-affix">
                            <span>@</span>
                            <input
                                type="text" name="username" id="username"
                                class="au-input @error('username') is-invalid @enderror"
                                value="{{ old('username') }}"
                                placeholder="e.g. msantos"
                                maxlength="255" required autocapitalize="none" spellcheck="false"
                            >
                        </div>
                        @error('username') <div class="au-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="au-c6">
                        <label for="email" class="au-label">
                            <span>Email address</span>
                            <span class="au-opt">Optional</span>
                        </label>
                        <input
                            type="email" name="email" id="email"
                            class="au-input @error('email') is-invalid @enderror"
                            value="{{ old('email') }}"
                            placeholder="name@example.com"
                            maxlength="255"
                        >
                        @error('email') <div class="au-error">{{ $message }}</div> @enderror
                    </div>

                </div>
            </section>


            {{-- ---------- Access ---------- --}}
            <section class="au-block">
                <h2 class="au-block-title"><i class="bi bi-shield-check"></i> Access</h2>
                <p class="au-block-note">What role they hold and whether the account can sign in right away.</p>

                <div class="au-fields">

                    <div class="au-c6">
                        <label for="role" class="au-label">
                            <span>System role<span class="au-req">*</span></span>
                        </label>
                        <select name="role" id="role" class="au-select @error('role') is-invalid @enderror" required>
                            <option value="" disabled {{ old('role') ? '' : 'selected' }}>Select system role</option>

                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}" {{ old('role') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        <div class="au-help">Only the Secretary role can be created from this form.</div>
                        @error('role') <div class="au-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="au-c6">
                        <span class="au-label" id="statusLabel">
                            <span>Account status<span class="au-req">*</span></span>
                        </span>
                        <div class="au-seg" role="radiogroup" aria-labelledby="statusLabel">
                            <input type="radio" name="status" id="status_active" value="active"
                                   {{ old('status', 'active') === 'active' ? 'checked' : '' }} required>
                            <label for="status_active" class="is-on">Active</label>

                            <input type="radio" name="status" id="status_inactive" value="inactive"
                                   {{ old('status') === 'inactive' ? 'checked' : '' }}>
                            <label for="status_inactive" class="is-off">Inactive</label>
                        </div>
                        <div class="au-help">Inactive accounts cannot sign in until they are activated.</div>
                        @error('status') <div class="au-error">{{ $message }}</div> @enderror
                    </div>

                </div>
            </section>


            {{-- ---------- Password ---------- --}}
            <section class="au-block">
                <h2 class="au-block-title"><i class="bi bi-key-fill"></i> Password</h2>
                <p class="au-block-note">Use at least 8 characters. Share it with the user privately.</p>

                <div class="au-fields">

                    <div class="au-c6">
                        <label for="password" class="au-label">
                            <span>Password<span class="au-req">*</span></span>
                        </label>
                        <div class="au-pw">
                            <input
                                type="password" name="password" id="password"
                                class="au-input @error('password') is-invalid @enderror"
                                placeholder="At least 8 characters"
                                minlength="8" required autocomplete="new-password"
                            >
                            <button type="button" class="au-eye" data-toggle="password" aria-label="Show password">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        @error('password') <div class="au-error">{{ $message }}</div> @enderror
                    </div>

                    <div class="au-c6">
                        <label for="password_confirmation" class="au-label">
                            <span>Confirm password<span class="au-req">*</span></span>
                        </label>
                        <div class="au-pw">
                            <input
                                type="password" name="password_confirmation" id="password_confirmation"
                                class="au-input"
                                placeholder="Re-enter password"
                                minlength="8" required autocomplete="new-password"
                            >
                            <button type="button" class="au-eye" data-toggle="password_confirmation" aria-label="Show confirmation">
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                        <div class="au-match" id="pwMatch" aria-live="polite"></div>
                    </div>

                    <div class="au-c12 au-pw-tools" style="margin-top:-6px">
                        <button type="button" class="au-gen" id="pwGenerate">
                            <i class="bi bi-magic"></i> Suggest a strong password
                        </button>

                        <div class="au-strength" id="pwStrength" data-level="0">
                            <span class="au-strength-bars"><i></i><i></i><i></i><i></i></span>
                            <span id="pwStrengthText">Strength</span>
                        </div>
                    </div>

                </div>
            </section>


            {{-- ---------- Action bar ---------- --}}
            <div class="au-bar">
                <div class="au-bar-state" aria-live="polite">
                    <b id="checkCount">0</b> of 4 checks done
                </div>

                <a href="{{ route('users.index') }}" class="au-btn au-btn--line">Cancel</a>

                <button type="submit" class="au-btn">
                    <i class="bi bi-person-plus"></i> Create Secretary account
                </button>
            </div>
        </form>


        {{-- =====================================================
             LIVE ACCOUNT CARD
        ====================================================== --}}
        <aside class="au-side" aria-label="Account preview">

            <div class="au-side-label">
                <span>New account</span>
                <span class="au-live">Live preview</span>
            </div>

            <article class="au-card" id="pvCard">
                <div class="au-card-top">
                    <div class="au-avatar" id="pvAvatar" aria-hidden="true">?</div>
                    <h3 class="au-card-name is-empty" id="pvName">Full name</h3>
                    <p class="au-card-user is-empty" id="pvUser">@username</p>
                    <p class="au-card-mail" id="pvMail" hidden></p>

                    <div class="au-card-tags">
                        <span class="au-chip is-role is-empty" id="pvRole">Role</span>
                        <span class="au-chip is-on" id="pvStatus">Active</span>
                    </div>
                </div>

                <ul class="au-checks" aria-label="Checklist">
                    <li id="ckName"><i class="bi bi-check-lg"></i> Full name entered</li>
                    <li id="ckUser"><i class="bi bi-check-lg"></i> Username entered</li>
                    <li id="ckLen"><i class="bi bi-check-lg"></i> Password has 8 or more characters</li>
                    <li id="ckMatch"><i class="bi bi-check-lg"></i> Passwords match</li>
                </ul>
            </article>

            <div class="au-note">
                <i class="bi bi-shield-lock"></i>
                <div>
                    <b>Access control</b><br>
                    The system has two authorized roles, Captain and Secretary. The Captain has full system access,
                    while the Secretary has access according to assigned responsibilities. The Captain account is managed separately.
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
        name: $('name'), user: $('username'), mail: $('email'),
        role: $('role'), pw: $('password'), pw2: $('password_confirmation'),
    };
    const pv = {
        card: $('pvCard'), avatar: $('pvAvatar'), name: $('pvName'), user: $('pvUser'),
        mail: $('pvMail'), role: $('pvRole'), status: $('pvStatus'),
    };
    const ck = { name: $('ckName'), user: $('ckUser'), len: $('ckLen'), match: $('ckMatch') };

    /* Stable hue from the name so each person gets their own color */
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

    function render() {
        const name = f.name.value.trim();
        const user = f.user.value.trim();
        const mail = f.mail.value.trim();

        pv.name.textContent = name || 'Full name';
        pv.name.classList.toggle('is-empty', !name);
        pv.avatar.textContent = name ? name.charAt(0).toUpperCase() : '?';
        pv.card.style.setProperty('--h', name ? hueFor(name) : 190);

        pv.user.textContent = user ? '@' + user : '@username';
        pv.user.classList.toggle('is-empty', !user);

        pv.mail.hidden = !mail;
        pv.mail.textContent = mail;

        const opt = f.role.options[f.role.selectedIndex];
        const hasRole = f.role.value !== '';
        pv.role.textContent = hasRole ? opt.textContent.trim() : 'Role';
        pv.role.className = 'au-chip is-role' + (hasRole ? '' : ' is-empty') +
            (hasRole && f.role.value.toLowerCase() === 'captain' ? ' is-captain' : '');

        const inactive = $('status_inactive').checked;
        pv.status.textContent = inactive ? 'Inactive' : 'Active';
        pv.status.className = 'au-chip ' + (inactive ? 'is-off' : 'is-on');

        /* Password feedback */
        const pw = f.pw.value;
        const pw2 = f.pw2.value;
        const score = passwordScore(pw);
        $('pwStrength').dataset.level = score;
        $('pwStrengthText').textContent = strengthWords[score];

        const match = $('pwMatch');
        if (!pw2) {
            match.textContent = '';
            match.className = 'au-match';
        } else if (pw === pw2) {
            match.textContent = 'Passwords match';
            match.className = 'au-match is-ok';
        } else {
            match.textContent = 'Passwords do not match yet';
            match.className = 'au-match is-bad';
        }

        /* Checklist */
        const done = {
            name: name.length > 0,
            user: user.length > 0,
            len: pw.length >= 8,
            match: pw.length > 0 && pw === pw2,
        };
        Object.keys(done).forEach((k) => ck[k].classList.toggle('is-done', done[k]));
        $('checkCount').textContent = Object.values(done).filter(Boolean).length;
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

    /* Suggest a strong password (reveals it so it can be shared) */
    $('pwGenerate').addEventListener('click', () => {
        const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*?';
        const bytes = new Uint32Array(14);
        crypto.getRandomValues(bytes);
        const value = Array.from(bytes, (n) => chars[n % chars.length]).join('');

        f.pw.value = value;
        f.pw2.value = value;

        [f.pw, f.pw2].forEach((input) => { input.type = 'text'; });
        document.querySelectorAll('[data-toggle]').forEach((btn) => {
            btn.innerHTML = '<i class="bi bi-eye-slash"></i>';
        });
        render();
    });

    render();
})();
</script>

@endsection
