@extends('layouts.app')

@section('title', 'View User | e-Poblacion')

@push('styles')
<style>
    /* =====================================================================
       USER DETAILS — tokens
       Light is the default. Dark applies when <html data-theme="dark">, or
       when data-theme="system" and the OS prefers dark.
       .sv-dialog is a top-layer element, so it gets the same tokens.
       ===================================================================== */
    .sv, .sv-dialog {
        --sv-display: 'Segoe UI Variable Display', 'Segoe UI', system-ui, -apple-system, 'Helvetica Neue', Arial, sans-serif;
        --sv-body: 'Segoe UI Variable Text', 'Segoe UI', system-ui, -apple-system, 'Helvetica Neue', Arial, sans-serif;

        --sv-page: #f3f6f8;
        --sv-surface: #ffffff;
        --sv-sunken: #f1f5f7;
        --sv-line: #dde5ea;
        --sv-line-strong: #bccad2;
        --sv-ink: #0b1f2a;
        --sv-text: #2a404b;
        --sv-muted: #526a76;

        --sv-teal: #0b6b64;
        --sv-teal-soft: #daf1ee;
        --sv-indigo: #4350c4;
        --sv-indigo-soft: #e5e8fb;
        --sv-ok: #12794a;
        --sv-ok-soft: #dcf3e7;
        --sv-coral: #c53d22;
        --sv-coral-soft: #fbe4de;
        --sv-amber: #8a5a00;
        --sv-amber-soft: #fcefd0;

        --sv-on-solid: #ffffff;
        --sv-focus: rgba(11, 107, 100, .35);
        --sv-shadow: 0 1px 2px rgba(11, 31, 42, .06), 0 12px 32px -12px rgba(11, 31, 42, .14);

        --sv-av-bg: 90%;
        --sv-av-s: 55%;
        --sv-av-fg: 24%;

        color-scheme: light;
        font-family: var(--sv-body);
        color: var(--sv-text);
    }

    [data-theme="dark"] .sv,
    [data-theme="dark"] .sv-dialog {
        --sv-page: #0b161b;
        --sv-surface: #15272f;
        --sv-sunken: #0e1c22;
        --sv-line: #29444e;
        --sv-line-strong: #3b5964;
        --sv-ink: #f0f8f7;
        --sv-text: #cfe0e3;
        --sv-muted: #9bb4bb;

        --sv-teal: #4fd8c8;
        --sv-teal-soft: #12403d;
        --sv-indigo: #a3adff;
        --sv-indigo-soft: #232a55;
        --sv-ok: #5fdfa2;
        --sv-ok-soft: #123a29;
        --sv-coral: #ff9577;
        --sv-coral-soft: #432219;
        --sv-amber: #f3c46a;
        --sv-amber-soft: #3f3012;

        --sv-on-solid: #0b161b;
        --sv-focus: rgba(79, 216, 200, .45);
        --sv-shadow: 0 1px 2px rgba(0, 0, 0, .45), 0 14px 34px -12px rgba(0, 0, 0, .55);

        --sv-av-bg: 26%;
        --sv-av-s: 40%;
        --sv-av-fg: 84%;
        color-scheme: dark;
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .sv,
        [data-theme="system"] .sv-dialog {
            --sv-page: #0b161b;
            --sv-surface: #15272f;
            --sv-sunken: #0e1c22;
            --sv-line: #29444e;
            --sv-line-strong: #3b5964;
            --sv-ink: #f0f8f7;
            --sv-text: #cfe0e3;
            --sv-muted: #9bb4bb;

            --sv-teal: #4fd8c8;
            --sv-teal-soft: #12403d;
            --sv-indigo: #a3adff;
            --sv-indigo-soft: #232a55;
            --sv-ok: #5fdfa2;
            --sv-ok-soft: #123a29;
            --sv-coral: #ff9577;
            --sv-coral-soft: #432219;
            --sv-amber: #f3c46a;
            --sv-amber-soft: #3f3012;

            --sv-on-solid: #0b161b;
            --sv-focus: rgba(79, 216, 200, .45);
            --sv-shadow: 0 1px 2px rgba(0, 0, 0, .45), 0 14px 34px -12px rgba(0, 0, 0, .55);

            --sv-av-bg: 26%;
            --sv-av-s: 40%;
            --sv-av-fg: 84%;
            color-scheme: dark;
        }
    }

    /* ---------------------------------------------------------------------
       Base
       --------------------------------------------------------------------- */
    .sv *, .sv *::before, .sv *::after,
    .sv-dialog *, .sv-dialog *::before, .sv-dialog *::after { box-sizing: border-box; }

    .sv a { text-decoration: none; }
    .sv i.bi { line-height: 1; }

    .sv :focus-visible,
    .sv-dialog :focus-visible {
        outline: 3px solid var(--sv-focus);
        outline-offset: 2px;
        border-radius: 10px;
    }

    /* ---------------------------------------------------------------------
       Header
       --------------------------------------------------------------------- */
    .sv-head {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 26px;
    }

    .sv-crumbs {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        font-size: .85rem;
        color: var(--sv-muted);
    }

    .sv-crumbs a { color: var(--sv-muted); font-weight: 600; }
    .sv-crumbs a:hover { color: var(--sv-ink); text-decoration: underline; }
    .sv-crumbs span { color: var(--sv-ink); font-weight: 600; }

    .sv-title {
        margin: 0;
        font-family: var(--sv-display);
        font-weight: 800;
        font-size: clamp(1.7rem, 3.2vw, 2.4rem);
        letter-spacing: -.025em;
        line-height: 1.08;
        color: var(--sv-ink);
    }

    .sv-sub { margin: 8px 0 0; color: var(--sv-muted); font-size: .98rem; }

    /* ---------------------------------------------------------------------
       Buttons
       --------------------------------------------------------------------- */
    .sv-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 9px;
        min-height: 46px;
        padding: 0 20px;
        border-radius: 14px;
        cursor: pointer;
        font: 700 .92rem/1.2 var(--sv-body);
        text-align: center;
        background: var(--sv-ink);
        color: var(--sv-on-solid);
        border: 1.5px solid var(--sv-ink);
        transition: transform .12s ease, background-color .15s ease, border-color .15s ease, box-shadow .15s ease;
    }

    .sv-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 18px -10px rgba(0, 0, 0, .45); color: var(--sv-on-solid); }
    .sv-btn:active { transform: translateY(0); box-shadow: none; }

    .sv-btn--line { background: transparent; color: var(--sv-ink); border-color: var(--sv-line-strong); }
    .sv-btn--line:hover { background: var(--sv-sunken); color: var(--sv-ink); box-shadow: none; }

    .sv-btn--ok { background: var(--sv-ok-soft); color: var(--sv-ok); border-color: var(--sv-ok); }
    .sv-btn--ok:hover { background: var(--sv-ok); color: var(--sv-surface); box-shadow: none; }

    .sv-btn--bad { background: var(--sv-coral-soft); color: var(--sv-coral); border-color: var(--sv-coral); }
    .sv-btn--bad:hover { background: var(--sv-coral); color: var(--sv-surface); box-shadow: none; }

    .sv-btn--danger { background: var(--sv-coral); border-color: var(--sv-coral); color: var(--sv-surface); }
    .sv-btn--danger:hover { color: var(--sv-surface); filter: brightness(1.08); }

    .sv-btn--block { width: 100%; }

    /* ---------------------------------------------------------------------
       Alerts
       --------------------------------------------------------------------- */
    .sv-alert {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding: 14px 16px;
        border-radius: 16px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        font-size: .95rem;
        font-weight: 500;
    }

    .sv-alert > i { font-size: 1.15rem; margin-top: 1px; }
    .sv-alert ul { margin: 6px 0 0; padding-left: 18px; }
    .sv-alert strong { display: block; }

    .sv-alert--err { background: var(--sv-coral-soft); color: var(--sv-coral); border-color: var(--sv-coral); }
    .sv-alert--ok  { background: var(--sv-ok-soft);    color: var(--sv-ok);    border-color: var(--sv-ok); }

    .sv-alert-close {
        margin-left: auto;
        background: none;
        border: 0;
        color: inherit;
        cursor: pointer;
        font-size: 1.3rem;
        line-height: 1;
        padding: 0 4px;
    }

    /* ---------------------------------------------------------------------
       Layout
       --------------------------------------------------------------------- */
    .sv-grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 370px;
        gap: 24px;
        align-items: start;
    }

    .sv-col { display: grid; gap: 22px; min-width: 0; }

    /* ---------------------------------------------------------------------
       Profile card
       --------------------------------------------------------------------- */
    .sv-profile {
        --h: 190;
        overflow: hidden;
        background: var(--sv-surface);
        border: 1px solid var(--sv-line);
        border-radius: 28px;
        box-shadow: var(--sv-shadow);
    }

    .sv-cover {
        position: relative;
        height: 140px;
        background:
            radial-gradient(120% 140% at 100% 0%, hsl(calc(var(--h) + 50) 70% 52% / .55) 0%, transparent 60%),
            linear-gradient(125deg, hsl(var(--h) 58% 26%) 0%, hsl(calc(var(--h) + 28) 56% 36%) 100%);
    }

    .sv-cover::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: .55;
        background-image: radial-gradient(rgba(255, 255, 255, .22) 1px, transparent 1.5px);
        background-size: 18px 18px;
        -webkit-mask-image: linear-gradient(100deg, transparent 15%, #000 100%);
        mask-image: linear-gradient(100deg, transparent 15%, #000 100%);
    }

    .sv-who {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 18px 24px;
        padding: 0 32px;
        margin-top: -62px;
        position: relative;
    }

    .sv-avatar {
        position: relative;
        overflow: hidden;
        display: grid;
        place-items: center;
        width: 128px;
        height: 128px;
        flex: none;
        border-radius: 50%;
        font-family: var(--sv-display);
        font-weight: 800;
        font-size: 3.1rem;
        background: hsl(var(--h) var(--sv-av-s) var(--sv-av-bg));
        color: hsl(var(--h) 55% var(--sv-av-fg));
        box-shadow: 0 0 0 6px var(--sv-surface), 0 16px 30px -14px rgba(0, 0, 0, .6);
    }

    .sv-avatar img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sv-who-text { min-width: 0; padding-bottom: 8px; flex: 1 1 260px; }

    .sv-name {
        margin: 0;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        font-family: var(--sv-display);
        font-weight: 800;
        font-size: clamp(1.5rem, 3vw, 2.05rem);
        letter-spacing: -.02em;
        line-height: 1.1;
        color: var(--sv-ink);
        overflow-wrap: anywhere;
    }

    .sv-you {
        padding: 4px 11px;
        border-radius: 999px;
        font-family: var(--sv-body);
        font-size: .74rem;
        font-weight: 800;
        letter-spacing: .02em;
        background: var(--sv-teal-soft);
        color: var(--sv-teal);
    }

    .sv-handle {
        margin: 6px 0 0;
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--sv-muted);
        font-weight: 500;
        overflow-wrap: anywhere;
    }

    .sv-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        padding: 20px 32px 0;
    }

    .sv-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 14px;
        border-radius: 999px;
        font-size: .82rem;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .sv-chip.is-captain   { background: var(--sv-indigo-soft); color: var(--sv-indigo); }
    .sv-chip.is-secretary { background: var(--sv-teal-soft);   color: var(--sv-teal); }
    .sv-chip.is-other     { background: var(--sv-sunken);      color: var(--sv-text); border-color: var(--sv-line); }
    .sv-chip.is-on        { background: var(--sv-ok-soft);     color: var(--sv-ok); }
    .sv-chip.is-off       { background: var(--sv-sunken);      color: var(--sv-muted); border-color: var(--sv-line-strong); }

    .sv-facts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 14px;
        padding: 24px 32px 32px;
    }

    .sv-fact {
        display: flex;
        gap: 14px;
        align-items: flex-start;
        padding: 16px 18px;
        border-radius: 18px;
        background: var(--sv-sunken);
        border: 1px solid var(--sv-line);
        min-width: 0;
    }

    .sv-fact-icon {
        display: grid;
        place-items: center;
        flex: none;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        font-size: 1.05rem;
        background: var(--sv-teal-soft);
        color: var(--sv-teal);
    }

    .sv-fact-body { min-width: 0; }

    .sv-fact small {
        display: block;
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        color: var(--sv-muted);
    }

    .sv-fact b {
        display: block;
        margin-top: 4px;
        font-weight: 700;
        font-size: .98rem;
        line-height: 1.35;
        color: var(--sv-ink);
        overflow-wrap: anywhere;
    }

    .sv-fact em {
        display: block;
        margin-top: 2px;
        font-style: normal;
        font-size: .8rem;
        color: var(--sv-muted);
    }

    /* ---------------------------------------------------------------------
       Side cards
       --------------------------------------------------------------------- */
    .sv-card {
        background: var(--sv-surface);
        border: 1px solid var(--sv-line);
        border-radius: 24px;
        box-shadow: var(--sv-shadow);
        padding: 24px;
    }

    .sv-card h3 {
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0 0 16px;
        font-family: var(--sv-display);
        font-weight: 800;
        font-size: 1.08rem;
        letter-spacing: -.01em;
        color: var(--sv-ink);
    }

    .sv-card h3 i {
        display: grid;
        place-items: center;
        flex: none;
        width: 34px;
        height: 34px;
        border-radius: 11px;
        background: var(--sv-teal-soft);
        color: var(--sv-teal);
        font-size: 1rem;
    }

    .sv-card--warn { border-top: 4px solid var(--sv-amber); }
    .sv-card--warn h3 i { background: var(--sv-amber-soft); color: var(--sv-amber); }

    .sv-actions { display: grid; gap: 10px; }
    .sv-actions form { margin: 0; }
    .sv-sep { height: 1px; margin: 6px 0; background: var(--sv-line); border: 0; opacity: 1; }
    .sv-info { margin: 0; font-size: .92rem; line-height: 1.65; color: var(--sv-text); }

    .sv-note {
        display: flex;
        gap: 12px;
        padding: 13px 15px;
        border-radius: 16px;
        background: var(--sv-sunken);
        border: 1px solid var(--sv-line);
        font-size: .87rem;
        line-height: 1.55;
        color: var(--sv-text);
    }

    .sv-note > i { flex: none; margin-top: 2px; color: var(--sv-teal); font-size: 1.05rem; }
    .sv-note strong { color: var(--sv-ink); }
    .sv-note--amber { background: var(--sv-amber-soft); border-color: transparent; }
    .sv-note--amber > i { color: var(--sv-amber); }

    .sv-stack-sm { display: grid; gap: 10px; margin-bottom: 18px; }

    /* Bootstrap form controls inside cards — forced to theme tokens */
    .sv-card .form-label {
        color: var(--sv-ink);
        font-size: .9rem;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .sv-card .form-control {
        min-height: 46px;
        border: 1.5px solid var(--sv-line-strong);
        border-radius: 12px;
        background-color: var(--sv-sunken);
        color: var(--sv-ink);
    }

    .sv-card .form-control::placeholder { color: var(--sv-muted); opacity: 1; }

    .sv-card .form-control:focus {
        background-color: var(--sv-surface);
        color: var(--sv-ink);
        border-color: var(--sv-teal);
        box-shadow: 0 0 0 4px var(--sv-focus);
    }

    .sv-card .form-text { color: var(--sv-muted); font-size: .82rem; }

    .sv-card .form-check { display: flex; gap: 10px; align-items: flex-start; padding-left: 0; }

    .sv-card .form-check-input {
        flex: none;
        float: none;
        margin: 3px 0 0;
        width: 1.15rem;
        height: 1.15rem;
        border: 1.5px solid var(--sv-line-strong);
        background-color: var(--sv-sunken);
    }

    .sv-card .form-check-input:checked {
        background-color: var(--sv-teal);
        border-color: var(--sv-teal);
    }

    .sv-card .form-check-label {
        color: var(--sv-text);
        font-size: .88rem;
        line-height: 1.55;
    }

    /* ---------------------------------------------------------------------
       Access card
       --------------------------------------------------------------------- */
    .sv-access { display: flex; gap: 16px; align-items: flex-start; }

    .sv-access-icon {
        display: grid;
        place-items: center;
        flex: none;
        width: 48px;
        height: 48px;
        border-radius: 15px;
        font-size: 1.3rem;
        background: var(--sv-teal-soft);
        color: var(--sv-teal);
    }

    .sv-access-icon.is-captain { background: var(--sv-indigo-soft); color: var(--sv-indigo); }
    .sv-access-icon.is-other { background: var(--sv-sunken); color: var(--sv-muted); }

    .sv-access strong {
        display: block;
        margin-bottom: 4px;
        font-family: var(--sv-display);
        font-size: 1.02rem;
        color: var(--sv-ink);
    }

    /* ---------------------------------------------------------------------
       Confirm dialog
       --------------------------------------------------------------------- */
    .sv-dialog {
        width: min(460px, calc(100vw - 32px));
        padding: 0;
        border: 1px solid var(--sv-line);
        border-radius: 26px;
        background: var(--sv-surface);
        color: var(--sv-text);
        box-shadow: 0 28px 70px rgba(0, 0, 0, .4);
        font-family: var(--sv-body);
    }

    .sv-dialog::backdrop { background: rgba(6, 16, 21, .62); backdrop-filter: blur(4px); }
    .sv-dialog-body { padding: 30px 30px 18px; }

    .sv-dialog-icon {
        display: grid;
        place-items: center;
        width: 54px;
        height: 54px;
        border-radius: 50%;
        margin-bottom: 18px;
        font-size: 1.45rem;
        background: var(--dlg-soft, var(--sv-sunken));
        color: var(--dlg-tone, var(--sv-ink));
    }

    .sv-dialog h3 {
        margin: 0;
        font-family: var(--sv-display);
        font-weight: 800;
        font-size: 1.3rem;
        letter-spacing: -.01em;
        color: var(--sv-ink);
    }

    .sv-dialog p { margin: 10px 0 0; line-height: 1.6; color: var(--sv-text); }

    .sv-dialog-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 14px 30px 26px;
    }

    /* ---------------------------------------------------------------------
       Responsive
       --------------------------------------------------------------------- */
    @media (max-width: 1100px) {
        .sv-grid { grid-template-columns: minmax(0, 1fr) 340px; }
    }

    @media (max-width: 991.98px) {
        .sv-grid { grid-template-columns: 1fr; }
    }

    @media (max-width: 640px) {
        .sv-who { padding: 0 20px; justify-content: center; text-align: center; }
        .sv-who-text { text-align: center; flex-basis: 100%; }
        .sv-name, .sv-handle { justify-content: center; }
        .sv-tags { padding: 18px 20px 0; justify-content: center; }
        .sv-facts { padding: 20px; grid-template-columns: 1fr; }
        .sv-head .sv-btn { width: 100%; }
        .sv-card { padding: 20px; }
        .sv-dialog-actions { flex-direction: column-reverse; }
        .sv-dialog-actions .sv-btn { width: 100%; }
    }

    @media (prefers-reduced-motion: reduce) {
        .sv-btn { transition: none; }
        .sv-btn:hover { transform: none; }
    }
</style>
@endpush

@section('content')

@php
    $role = strtolower((string) $user->role);
    $isSelf = (int) $user->id === (int) auth()->id();
    $isCaptainViewer = auth()->user()?->isCaptain() ?? false;
    $isSecretaryAccount = $user->isSecretary();
    $isTreasurer = method_exists($user, 'isTreasurer') && $user->isTreasurer();
    $hue = crc32((string) ($user->name ?? 'U')) % 360;

    // The users table uses the "avatar" column for the profile photo.
    $photoUrl = $user->avatar
        ? asset('storage/' . $user->avatar) . '?v=' . optional($user->updated_at)->timestamp
        : null;
@endphp

<div class="container-fluid py-4 sv">

    {{-- HEADER --}}
    <header class="sv-head">
        <div>
            <nav class="sv-crumbs" aria-label="Breadcrumb">
                <a href="{{ route('users.index') }}">User Management</a>
                <i class="bi bi-chevron-right" style="font-size:.7rem" aria-hidden="true"></i>
                <span>User details</span>
            </nav>

            <h1 class="sv-title">User Details</h1>
            <p class="sv-sub">View system personnel account information.</p>
        </div>

        <a href="{{ route('users.index') }}" class="sv-btn sv-btn--line">
            <i class="bi bi-arrow-left"></i> Back to users
        </a>
    </header>

    {{-- Success --}}
    @if (session('success'))
        <div class="sv-alert sv-alert--ok" role="status">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="sv-alert-close" aria-label="Dismiss" onclick="this.closest('.sv-alert').remove()">&times;</button>
        </div>
    @endif

    {{-- Errors --}}
    @if ($errors->any())
        <div class="sv-alert sv-alert--err" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Please correct the following</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" class="sv-alert-close" aria-label="Dismiss" onclick="this.closest('.sv-alert').remove()">&times;</button>
        </div>
    @endif

    <div class="sv-grid">

        {{-- MAIN COLUMN --}}
        <div class="sv-col">

            {{-- PROFILE --}}
            <article class="sv-profile" style="--h: {{ $hue }}">
                <div class="sv-cover" aria-hidden="true"></div>

                <div class="sv-who">
                    <div class="sv-avatar" aria-hidden="true">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}

                        @if ($photoUrl)
                            <img src="{{ $photoUrl }}" alt="" onerror="this.remove()">
                        @endif
                    </div>

                    <div class="sv-who-text">
                        <h2 class="sv-name">
                            {{ $user->name }}

                            @if ($isSelf)
                                <span class="sv-you">You</span>
                            @endif
                        </h2>

                        <p class="sv-handle">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            {{ $user->email ?: 'Email not provided' }}
                        </p>
                    </div>
                </div>

                <div class="sv-tags">
                    @if ($user->isCaptain())
                        <span class="sv-chip is-captain"><i class="bi bi-shield-check"></i> Captain</span>
                    @elseif ($user->isSecretary())
                        <span class="sv-chip is-secretary"><i class="bi bi-person-badge"></i> Secretary</span>
                    @elseif ($isTreasurer)
                        <span class="sv-chip is-other"><i class="bi bi-cash-stack"></i> Treasurer</span>
                    @else
                        <span class="sv-chip is-other">{{ ucfirst($user->role) }}</span>
                    @endif

                    @if ($user->isActive())
                        <span class="sv-chip is-on"><i class="bi bi-check-circle-fill"></i> Active</span>
                    @else
                        <span class="sv-chip is-off"><i class="bi bi-x-circle-fill"></i> Inactive</span>
                    @endif

                    @if (isset($user->document_processor))
                        <span class="sv-chip {{ $user->document_processor ? 'is-on' : 'is-off' }}">
                            <i class="bi bi-file-earmark-text"></i>
                            Document processing {{ $user->document_processor ? 'enabled' : 'not enabled' }}
                        </span>
                    @endif
                </div>

                <div class="sv-facts">
                    <div class="sv-fact">
                        <span class="sv-fact-icon" aria-hidden="true"><i class="bi bi-person"></i></span>
                        <div class="sv-fact-body">
                            <small>Full name</small>
                            <b>{{ $user->name }}</b>
                        </div>
                    </div>

                    @if (! empty($user->username))
                        <div class="sv-fact">
                            <span class="sv-fact-icon" aria-hidden="true"><i class="bi bi-at"></i></span>
                            <div class="sv-fact-body">
                                <small>Username</small>
                                <b>{{ $user->username }}</b>
                            </div>
                        </div>
                    @endif

                    
                    <div class="sv-fact">
                        <span class="sv-fact-icon" aria-hidden="true"><i class="bi bi-envelope"></i></span>
                        <div class="sv-fact-body">
                            <small>Email address</small>
                            <b>{{ $user->email ?: 'Not provided' }}</b>

                            @if ($isSecretaryAccount)
                                <em>
                                    This displays the saved address only; it does not confirm email ownership.
                                    The Secretary must verify their personal email during initial password setup.
                                    Changing the address in Edit User does not verify it.
                                </em>
                            @endif
                        </div>
                    </div>


                    <div class="sv-fact">
                        <span class="sv-fact-icon" aria-hidden="true"><i class="bi bi-calendar-plus"></i></span>
                        <div class="sv-fact-body">
                            <small>Account created</small>
                            <b>{{ $user->created_at?->format('F d, Y h:i A') ?? 'N/A' }}</b>
                            @if ($user->created_at)
                                <em>{{ $user->created_at->diffForHumans() }}</em>
                            @endif
                        </div>
                    </div>

                    <div class="sv-fact">
                        <span class="sv-fact-icon" aria-hidden="true"><i class="bi bi-clock-history"></i></span>
                        <div class="sv-fact-body">
                            <small>Last updated</small>
                            <b>{{ $user->updated_at?->format('F d, Y h:i A') ?? 'N/A' }}</b>
                            @if ($user->updated_at)
                                <em>{{ $user->updated_at->diffForHumans() }}</em>
                            @endif
                        </div>
                    </div>
                </div>
            </article>

            {{-- ACCESS INFORMATION --}}
            <section class="sv-card" aria-label="Access information">
                <h3><i class="bi bi-shield-lock-fill"></i> Access information</h3>

                <div class="sv-access">
                    @if ($user->isCaptain())
                        <span class="sv-access-icon is-captain" aria-hidden="true"><i class="bi bi-shield-check"></i></span>
                        <div>
                            <strong>Captain access</strong>
                            <p class="sv-info">Captain has full access to all system features, including User Management, Settings and account photos.</p>
                        </div>
                    @elseif ($user->isSecretary())
                        <span class="sv-access-icon" aria-hidden="true"><i class="bi bi-person-badge"></i></span>
                        <div>
                            <strong>Secretary access</strong>
                            <p class="sv-info">Secretary has access to operational barangay management and service modules according to assigned responsibilities.</p>
                        </div>
                    @elseif ($isTreasurer)
                        <span class="sv-access-icon is-other" aria-hidden="true"><i class="bi bi-cash-stack"></i></span>
                        <div>
                            <strong>Treasurer access</strong>
                            <p class="sv-info">Treasurer has limited access focused on viewing and checking assigned information and relief voucher records.</p>
                        </div>
                    @else
                        <span class="sv-access-icon is-other" aria-hidden="true"><i class="bi bi-question-circle"></i></span>
                        <div>
                            <strong>No recognized role</strong>
                            <p class="sv-info">This account does not have a recognized system role.</p>
                        </div>
                    @endif
                </div>
            </section>

        </div>

        {{-- SIDE COLUMN --}}
        <aside class="sv-col">

            {{-- ACCOUNT ACTIONS --}}
            <section class="sv-card" aria-label="Account actions">
                <h3><i class="bi bi-gear-fill"></i> Account actions</h3>

                <div class="sv-actions">
                    <a href="{{ route('users.edit', $user) }}" class="sv-btn sv-btn--block">
                        <i class="bi bi-pencil-fill"></i> Edit account
                    </a>

                    <a href="{{ route('users.edit', $user) }}#photoDrop" class="sv-btn sv-btn--line sv-btn--block">
                        <i class="bi bi-camera-fill"></i> {{ $photoUrl ? 'Change photo' : 'Add photo' }}
                    </a>

                    <hr class="sv-sep">

                    @if (! $user->isActive())
                        <form id="activateForm" action="{{ route('users.activate', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <button type="button" class="sv-btn sv-btn--ok sv-btn--block" data-confirm
                                data-form="activateForm" data-tone="ok"
                                data-title="Activate this account?"
                                data-message="{{ $user->name }} will be able to sign in again."
                                data-button="Activate">
                                <i class="bi bi-person-check-fill"></i> Activate account
                            </button>
                        </form>
                    @endif

                    @if ($user->isActive() && ! $isSelf)
                        <form id="deactivateForm" action="{{ route('users.deactivate', $user) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <button type="button" class="sv-btn sv-btn--bad sv-btn--block" data-confirm
                                data-form="deactivateForm" data-tone="bad"
                                data-title="Deactivate this account?"
                                data-message="{{ $user->name }} will no longer be able to sign in until the account is activated again."
                                data-button="Deactivate">
                                <i class="bi bi-person-x-fill"></i> Deactivate account
                            </button>
                        </form>
                    @elseif ($isSelf)
                        <div class="sv-note">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>You cannot deactivate your own account.</span>
                        </div>
                    @endif
                </div>
            </section>

            {{-- ONSITE SECRETARY PASSWORD RECOVERY --}}
            @if ($isCaptainViewer && $isSecretaryAccount && $user->isActive() && ! $isSelf)
                <section class="sv-card sv-card--warn" aria-label="Onsite Secretary password recovery">
                    <h3>
                        <i class="bi bi-key-fill"></i>
                        Secretary Password Recovery
                    </h3>

                    <p class="sv-info" style="margin-bottom:14px">
                        Use this process only when the Secretary is physically present
                        at the barangay office and you have personally verified their identity.
                        This authorization does not allow you to view or choose their new password.
                    </p>

                    <div class="sv-stack-sm">
                        <div class="sv-note">
                            <i class="bi bi-shield-lock"></i>
                            <span>
                                The Secretary will set a new password privately. Their existing
                                two-factor authentication must remain enabled and will be required
                                at the next login.
                            </span>
                        </div>
                    </div>

                    <form
                        id="passwordRecoveryForm"
                        action="{{ route('users.password-reset.authorize', $user) }}"
                        method="POST"
                    >
                        @csrf

                        <div class="mb-3">
                            <label for="confirm_username" class="form-label">
                                Confirm the Secretary's username
                            </label>

                            <input
                                type="text"
                                name="confirm_username"
                                id="confirm_username"
                                class="form-control"
                                value="{{ old('confirm_username') }}"
                                autocomplete="off"
                                required
                                maxlength="255"
                                placeholder="Enter the Secretary's username"
                            >

                            <div class="form-text">
                                Enter the account username manually to confirm the intended account.
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input
                                type="checkbox"
                                name="identity_verified"
                                id="identity_verified"
                                value="1"
                                class="form-check-input"
                                required
                            >

                            <label for="identity_verified" class="form-check-label">
                                I personally verified the Secretary's identity in person
                                and authorize this password recovery.
                            </label>
                        </div>

                        <button
                            type="button"
                            class="sv-btn sv-btn--danger sv-btn--block"
                            data-confirm
                            data-validate
                            data-form="passwordRecoveryForm"
                            data-tone="bad"
                            data-icon="bi-shield-check"
                            data-title="Authorize password recovery?"
                            data-message="Confirm that the Secretary is physically present, their identity has been verified, and you authorize password recovery for this account. You will be signed out to allow the Secretary to set their password privately."
                            data-button="Authorize recovery"
                        >
                            <i class="bi bi-shield-check"></i>
                            Authorize Password Recovery
                        </button>
                    </form>
                </section>

                {{-- ONSITE SECRETARY AUTHENTICATOR RECOVERY --}}
                <section class="sv-card sv-card--warn" aria-label="Onsite Secretary authenticator recovery">
                    <h3>
                        <i class="bi bi-phone-fill"></i>
                        Secretary Authenticator Recovery
                    </h3>

                    <p class="sv-info" style="margin-bottom:14px">
                        Use this process when the Secretary needs to replace or recover
                        their authenticator because their phone is lost, stolen, damaged,
                        or being replaced. The Secretary must be physically present at
                        the barangay office, and you must personally verify their identity.
                    </p>

                    <div class="sv-stack-sm">
                        <div class="sv-note">
                            <i class="bi bi-shield-lock-fill"></i>
                            <span>
                                <strong>Privacy protection:</strong>
                                You authorize the recovery only. You must not request, view,
                                photograph, or record the Secretary's authenticator QR code,
                                authenticator secret, or recovery codes. You will be signed out
                                before the Secretary privately continues the process.
                            </span>
                        </div>

                        <div class="sv-note sv-note--amber">
                            <i class="bi bi-hourglass-split"></i>
                            <span>
                                The authorization is temporary and expires after 15 minutes.
                                The Secretary must complete the recovery within that period.
                            </span>
                        </div>
                    </div>

                    <form
                        id="authenticatorRecoveryForm"
                        action="{{ route('users.authenticator-recovery.authorize', $user) }}"
                        method="POST"
                    >
                        @csrf

                        <div class="mb-3">
                            <label
                                for="authenticator_confirm_username"
                                class="form-label"
                            >
                                Confirm the Secretary's username
                            </label>

                            <input
                                type="text"
                                name="confirm_username"
                                id="authenticator_confirm_username"
                                class="form-control"
                                value="{{ old('confirm_username') }}"
                                autocomplete="off"
                                required
                                maxlength="255"
                                placeholder="Enter the Secretary's username"
                            >

                            <div class="form-text">
                                Manually enter the username of the active Secretary account
                                whose authenticator needs recovery.
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input
                                type="checkbox"
                                name="identity_verified"
                                id="authenticator_identity_verified"
                                value="1"
                                class="form-check-input"
                                required
                            >

                            <label
                                for="authenticator_identity_verified"
                                class="form-check-label"
                            >
                                I personally verified the Secretary's identity in person
                                and authorize this authenticator recovery. I will not access
                                or record the Secretary's private authenticator information.
                            </label>
                        </div>

                        <button
                            type="button"
                            class="sv-btn sv-btn--danger sv-btn--block"
                            data-confirm
                            data-validate
                            data-form="authenticatorRecoveryForm"
                            data-tone="bad"
                            data-icon="bi-phone-fill"
                            data-title="Authorize authenticator recovery?"
                            data-message="Confirm that the Secretary is physically present, you personally verified their identity, and you authorize authenticator recovery. You will be signed out so the Secretary can privately verify their credentials and set up the new authenticator. This authorization expires after 15 minutes."
                            data-button="Authorize recovery"
                        >
                            <i class="bi bi-shield-check"></i>
                            Authorize Authenticator Recovery
                        </button>
                    </form>
                </section>
            @endif

        </aside>
    </div>

    {{-- CONFIRM DIALOG --}}
    <dialog class="sv-dialog" id="svConfirm" aria-labelledby="svConfirmTitle" aria-describedby="svConfirmMessage">
        <div class="sv-dialog-body">
            <div class="sv-dialog-icon" id="svConfirmIcon">
                <i class="bi bi-question-circle-fill"></i>
            </div>

            <h3 id="svConfirmTitle">Are you sure?</h3>
            <p id="svConfirmMessage"></p>
        </div>

        <div class="sv-dialog-actions">
            <button type="button" class="sv-btn sv-btn--line" id="svConfirmCancel">Cancel</button>
            <button type="button" class="sv-btn" id="svConfirmOk">Confirm</button>
        </div>
    </dialog>

</div>

<script>
(function () {
    const $ = (id) => document.getElementById(id);

    const dialog = $('svConfirm');
    const dlgTitle = $('svConfirmTitle');
    const dlgMsg = $('svConfirmMessage');
    const dlgIcon = $('svConfirmIcon');
    const dlgOk = $('svConfirmOk');
    const dlgCancel = $('svConfirmCancel');
    let pendingForm = null;

    document.querySelectorAll('[data-confirm]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const form = $(btn.dataset.form);
            if (!form) return;

            // Recovery forms have required fields: validate before confirming.
            if (btn.hasAttribute('data-validate') && !form.reportValidity()) {
                return;
            }

            // Fallback for browsers without <dialog>.
            if (!dialog || typeof dialog.showModal !== 'function') {
                if (window.confirm(btn.dataset.message || btn.dataset.title)) {
                    form.submit();
                }
                return;
            }

            const bad = btn.dataset.tone === 'bad';
            const iconClass = btn.dataset.icon || (bad ? 'bi-person-x-fill' : 'bi-person-check-fill');
            pendingForm = form;

            dlgTitle.textContent = btn.dataset.title;
            dlgMsg.textContent = btn.dataset.message;
            dlgOk.textContent = btn.dataset.button;
            dlgOk.className = 'sv-btn ' + (bad ? 'sv-btn--danger' : 'sv-btn--ok');

            dlgIcon.style.setProperty('--dlg-tone', bad ? 'var(--sv-coral)' : 'var(--sv-ok)');
            dlgIcon.style.setProperty('--dlg-soft', bad ? 'var(--sv-coral-soft)' : 'var(--sv-ok-soft)');
            dlgIcon.innerHTML = '<i class="bi ' + iconClass + '"></i>';

            dialog.showModal();
            dlgCancel.focus();
        });
    });

    if (dialog && dlgCancel && dlgOk) {
        dlgCancel.addEventListener('click', () => dialog.close());

        dlgOk.addEventListener('click', () => {
            const form = pendingForm;
            dialog.close();

            if (form) {
                form.submit();
            }
        });

        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    }
})();
</script>

@endsection