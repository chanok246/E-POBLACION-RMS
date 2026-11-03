
@extends('layouts.app')

@section('title', 'Add User | e-Poblacion')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .au {
        --surface: #fff;
        --sunken: #f4f7f6;
        --line: #e0e7e5;
        --line-strong: #c8d3d0;
        --ink: #0e2a33;
        --text: #2b4149;
        --muted: #66808a;
        --teal: #0f766e;
        --teal-soft: #dff3ef;
        --ok: #178a55;
        --ok-soft: #e0f4ea;
        --coral: #d4492c;
        --coral-soft: #fbe6e0;
        --focus: rgba(15, 118, 110, .3);
        --shadow: 0 1px 2px rgba(14,42,51,.05), 0 8px 24px rgba(14,42,51,.06);
        font-family: 'Figtree', 'Segoe UI', system-ui, sans-serif;
        color: var(--text);
    }

    [data-theme="dark"] .au {
        --surface: #14262d;
        --sunken: #0f1f25;
        --line: #25404a;
        --line-strong: #33525d;
        --ink: #eaf5f3;
        --text: #c9dbde;
        --muted: #8aa5ac;
        --teal: #3dd0c0;
        --teal-soft: #123a38;
        --ok: #56d99b;
        --ok-soft: #123626;
        --coral: #ff8a6b;
        --coral-soft: #3d2019;
        --focus: rgba(61,208,192,.35);
        --shadow: 0 1px 2px rgba(0,0,0,.4), 0 8px 24px rgba(0,0,0,.3);
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .au {
            --surface: #14262d;
            --sunken: #0f1f25;
            --line: #25404a;
            --line-strong: #33525d;
            --ink: #eaf5f3;
            --text: #c9dbde;
            --muted: #8aa5ac;
            --teal: #3dd0c0;
            --teal-soft: #123a38;
            --ok: #56d99b;
            --ok-soft: #123626;
            --coral: #ff8a6b;
            --coral-soft: #3d2019;
            --focus: rgba(61,208,192,.35);
            --shadow: 0 1px 2px rgba(0,0,0,.4), 0 8px 24px rgba(0,0,0,.3);
        }
    }

    .au *, .au *::before, .au *::after { box-sizing: border-box; }
    .au a { text-decoration: none; }
    .au :focus-visible { outline: 3px solid var(--focus); outline-offset: 2px; }

    .au-head { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; justify-content: space-between; margin-bottom: 26px; }
    .au-crumbs { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: .85rem; color: var(--muted); }
    .au-crumbs a { color: var(--muted); }
    .au-crumbs a:hover { color: var(--ink); text-decoration: underline; }

    .au-title {
        margin: 0;
        font-family: 'Bricolage Grotesque', 'Segoe UI', sans-serif;
        font-weight: 800;
        font-size: clamp(1.8rem, 3.2vw, 2.4rem);
        letter-spacing: -.02em;
        line-height: 1.1;
        color: var(--ink);
    }

    .au-sub { margin: 8px 0 0; color: var(--muted); max-width: 58ch; }

    .au-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        min-height: 44px; padding: 0 18px; border-radius: 12px; cursor: pointer;
        font: 600 .95rem 'Figtree', sans-serif;
        background: var(--ink); color: var(--surface); border: 1px solid var(--ink);
        transition: filter .15s, transform .1s;
    }
    .au-btn:hover { filter: brightness(1.12); color: var(--surface); }
    .au-btn:active { transform: translateY(1px); }
    .au-btn--line { background: transparent; color: var(--ink); border-color: var(--line-strong); }
    .au-btn--line:hover { background: var(--sunken); color: var(--ink); filter: none; }
    .au-btn--sm { min-height: 38px; padding: 0 14px; font-size: .86rem; }

    .au-alert {
        display: flex; gap: 12px; padding: 14px 16px; border-radius: 14px; margin-bottom: 20px;
        font-size: .95rem; background: var(--coral-soft); color: var(--coral);
        border: 1px solid color-mix(in srgb, var(--coral) 30%, transparent);
    }
    .au-alert--success { background: var(--ok-soft); color: var(--ok); border-color: color-mix(in srgb, var(--ok) 30%, transparent); }
    .au-alert i { font-size: 1.1rem; margin-top: 1px; }
    .au-alert strong { display: block; }
    .au-alert ul { margin: 6px 0 0; padding-left: 18px; }
    .au-alert-close { margin-left: auto; background: none; border: 0; color: inherit; cursor: pointer; font-size: 1.2rem; line-height: 1; }

    .au-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 26px; align-items: start; }

    .au-panel, .au-card {
        background: var(--surface); border: 1px solid var(--line); border-radius: 20px;
        box-shadow: var(--shadow); overflow: hidden;
    }

    .au-block { padding: 26px 28px; }
    .au-block + .au-block { border-top: 1px solid var(--line); }

    .au-block-title {
        display: flex; align-items: center; gap: 12px; margin: 0 0 4px;
        font-family: 'Bricolage Grotesque', 'Segoe UI', sans-serif; font-weight: 700; font-size: 1.15rem; color: var(--ink);
    }
    .au-block-title i {
        display: grid; place-items: center; width: 34px; height: 34px; border-radius: 10px;
        background: var(--teal-soft); color: var(--teal);
    }
    .au-block-note { margin: 0 0 22px 46px; color: var(--muted); font-size: .9rem; }

    .au-fields { display: grid; grid-template-columns: repeat(12, minmax(0, 1fr)); gap: 20px 18px; }
    .au-c12 { grid-column: span 12; }

    .au-label {
        display: flex; align-items: baseline; justify-content: space-between; gap: 8px;
        margin-bottom: 7px; font-weight: 600; font-size: .92rem; color: var(--ink);
    }
    .au-req { color: var(--coral); margin-left: 3px; }

    .au-input {
        width: 100%; min-height: 48px; padding: 0 14px; border-radius: 12px;
        border: 1px solid var(--line-strong); background: var(--sunken); color: var(--ink);
        font: 500 .98rem 'Figtree', sans-serif; transition: border-color .15s, box-shadow .15s;
    }
    .au-input::placeholder { color: var(--muted); opacity: .75; }
    .au-input:focus { outline: none; background: var(--surface); border-color: var(--teal); box-shadow: 0 0 0 4px var(--focus); }
    .is-invalid { border-color: var(--coral) !important; }

    .au-error { margin-top: 6px; font-size: .84rem; font-weight: 500; color: var(--coral); }
    .au-help { margin-top: 6px; font-size: .82rem; color: var(--muted); line-height: 1.5; }

    .au-photo { display: grid; gap: 12px; }
    .au-drop {
        display: flex; align-items: center; gap: 20px; padding: 18px; border-radius: 18px; cursor: pointer;
        border: 2px dashed var(--line-strong); background: var(--sunken); transition: border-color .15s, background .15s;
    }
    .au-drop:hover, .au-drop.is-over { border-color: var(--teal); background: var(--teal-soft); }
    .au-drop.has-photo { border-style: solid; border-color: var(--ok); background: var(--ok-soft); }
    .au-drop.is-bad { border-color: var(--coral); background: var(--coral-soft); }

    .au-drop-img {
        position: relative; flex: none; display: grid; place-items: center; width: 84px; height: 84px;
        border-radius: 50%; overflow: hidden; background: var(--surface); color: var(--muted); font-size: 2.2rem;
        box-shadow: 0 0 0 4px var(--surface), 0 0 0 5px var(--line-strong);
    }
    .au-drop-img img, .au-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .au-drop-img img[hidden], .au-avatar img[hidden] { display: none; }

    .au-drop-text { min-width: 0; color: var(--ink); font-weight: 600; line-height: 1.4; }
    .au-drop-text u { color: var(--teal); text-underline-offset: 3px; }
    .au-drop-text small { display: block; margin-top: 4px; font-size: .82rem; color: var(--muted); font-weight: 400; }
    .au-drop-name { display: block; margin-top: 4px; font-weight: 700; font-size: .85rem; color: var(--ok); overflow-wrap: anywhere; }

    .au-photo-actions { display: flex; flex-wrap: wrap; gap: 8px; }
    .au-photo-actions[hidden] { display: none; }

    .au-bar {
        position: sticky; bottom: 0; z-index: 5; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
        padding: 14px 28px; border-top: 1px solid var(--line); background: var(--surface);
    }
    .au-bar-state { margin-right: auto; font-size: .88rem; color: var(--muted); }
    .au-bar-state b { color: var(--ink); }

    .au-side { position: sticky; top: 20px; display: grid; gap: 14px; }
    .au-side-label { display: flex; align-items: center; justify-content: space-between; font-size: .85rem; font-weight: 600; color: var(--muted); }
    .au-live { display: inline-flex; align-items: center; gap: 7px; }
    .au-live::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--teal); }

    .au-card-top { padding: 26px 22px 20px; text-align: center; background: var(--sunken); }
    .au-avatar {
        position: relative; overflow: hidden; display: grid; place-items: center; width: 88px; height: 88px;
        margin: 0 auto 14px; border-radius: 50%; font-family: 'Bricolage Grotesque', sans-serif;
        font-weight: 800; font-size: 2.3rem; background: var(--teal-soft); color: var(--teal);
        box-shadow: 0 0 0 4px var(--surface);
    }
    .au-card-name { margin: 0; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 1.25rem; color: var(--ink); overflow-wrap: anywhere; }
    .au-card-name.is-empty, .au-card-user.is-empty { color: var(--muted); font-weight: 500; }
    .au-card-user { margin: 5px 0 0; color: var(--text); font-weight: 500; overflow-wrap: anywhere; }
    .au-card-tags { display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin-top: 16px; }

    .au-chip { display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px; border-radius: 999px; font-size: .8rem; font-weight: 700; }
    .au-chip.is-role { background: var(--teal-soft); color: var(--teal); }
    .au-chip.is-on { background: var(--ok-soft); color: var(--ok); }

    .au-checks { margin: 0; padding: 16px 22px 20px; list-style: none; border-top: 1px dashed var(--line-strong); display: grid; gap: 11px; }
    .au-checks li { display: flex; align-items: center; gap: 10px; font-size: .9rem; color: var(--muted); }
    .au-checks li i {
        display: grid; place-items: center; width: 20px; height: 20px; border-radius: 50%;
        border: 1.5px solid var(--line-strong); font-size: .7rem; color: transparent; flex: none;
    }
    .au-checks li.is-done { color: var(--ink); }
    .au-checks li.is-done i { background: var(--ok); border-color: var(--ok); color: #fff; }

    .au-note {
        display: flex; gap: 12px; padding: 16px; border-radius: 16px; background: var(--sunken);
        border: 1px solid var(--line); font-size: .86rem; line-height: 1.55; color: var(--muted);
    }
    .au-note i { color: var(--teal); font-size: 1.2rem; }
    .au-note b { color: var(--ink); }

    @media (max-width: 1080px) {
        .au-grid { grid-template-columns: 1fr; }
        .au-side { position: static; order: -1; }
    }

    @media (max-width: 640px) {
        .au-block { padding: 22px 18px; }
        .au-block-note { margin-left: 0; }
        .au-bar { padding: 12px 18px; }
        .au-bar .au-btn { flex: 1; }
        .au-bar-state { width: 100%; }
        .au-head > .au-btn { width: 100%; }
        .au-drop { flex-direction: column; text-align: center; }
    }

    @media (prefers-reduced-motion: reduce) {
        .au *, .au *::before, .au *::after { animation: none !important; transition: none !important; }
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4 au">

    <header class="au-head">
        <div>
            <nav class="au-crumbs" aria-label="Breadcrumb">
                <a href="{{ route('users.index') }}">User Management</a>
                <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
                <span>Add user</span>
            </nav>

            <h1 class="au-title">Add a Secretary</h1>
            <p class="au-sub">Create an authorized Secretary account. The Secretary must verify their own email address and set their own permanent password.</p>
        </div>

        <a href="{{ route('users.index') }}" class="au-btn au-btn--line">
            <i class="bi bi-arrow-left"></i> Back to users
        </a>
    </header>

    @if (session('success'))
        <div class="au-alert au-alert--success" role="status">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="au-alert-close" aria-label="Dismiss" onclick="this.closest('.au-alert').remove()">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="au-alert" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Fix {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('issue', $errors->count()) }} before creating the account</strong>
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

        <form
            id="userForm"
            action="{{ route('users.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="au-panel"
            autocomplete="off"
        >
            @csrf

            <section class="au-block">
                <h2 class="au-block-title"><i class="bi bi-camera-fill"></i> Profile photo</h2>
                <p class="au-block-note">Optional. JPG, PNG or WebP, up to 2 MB.</p>

                <div class="au-photo">
                    <div class="au-drop" id="photoDrop" tabindex="0" role="button" aria-label="Choose a profile photo">
                        <div class="au-drop-img" aria-hidden="true">
                            <i class="bi bi-person-fill" id="photoIcon"></i>
                            <img id="photoPreview" alt="" hidden>
                        </div>
                        <div class="au-drop-text">
                            <span id="photoPrompt"><u>Choose a photo</u> or drag it here</span>
                            <small>A square photo works best.</small>
                            <span class="au-drop-name" id="photoName" hidden></span>
                        </div>
                    </div>

                    <input type="file" name="photo" id="photo" accept="image/png,image/jpeg,image/webp" hidden>

                    <div class="au-photo-actions" id="photoActions" hidden>
                        <button type="button" class="au-btn au-btn--line au-btn--sm" id="photoChange">
                            <i class="bi bi-arrow-repeat"></i> Choose another
                        </button>
                        <button type="button" class="au-btn au-btn--line au-btn--sm" id="photoRemove">
                            <i class="bi bi-trash3"></i> Remove photo
                        </button>
                    </div>

                    <div class="au-error" id="photoError" role="alert">@error('photo'){{ $message }}@enderror</div>
                </div>
            </section>

            <section class="au-block">
                <h2 class="au-block-title"><i class="bi bi-person-fill"></i> Account details</h2>
                <p class="au-block-note">Enter the Secretary's name, username, and personal email address that they can access.</p>

                <div class="au-fields">
                    <div class="au-c12">
                        <label for="name" class="au-label">Full name<span class="au-req">*</span></label>
                        <input
                            type="text" name="name" id="name"
                            class="au-input @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="e.g. Maria Santos"
                            maxlength="255" autocomplete="name" required autofocus
                        >
                        @error('name')<div class="au-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="au-c12">
                        <label for="username" class="au-label">Username<span class="au-req">*</span></label>
                        <input
                            type="text" name="username" id="username"
                            class="au-input @error('username') is-invalid @enderror"
                            value="{{ old('username') }}" placeholder="e.g. maria_santos"
                            maxlength="50" autocomplete="username" autocapitalize="none" spellcheck="false"
                            pattern="[A-Za-z0-9_-]{1,50}"
                            title="Use 1–50 letters, numbers, dashes, or underscores." required
                        >
                        <div class="au-help">Use a unique username containing letters, numbers, dashes, or underscores. The Secretary will use it to sign in.</div>
                        @error('username')<div class="au-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="au-c12">
                        <label for="email" class="au-label">Secretary's personal email address<span class="au-req">*</span></label>
                        <input
                            type="email" name="email" id="email"
                            class="au-input @error('email') is-invalid @enderror"
                            value="{{ old('email') }}" placeholder="name@example.com"
                            maxlength="255" autocomplete="email" autocapitalize="none" required
                        >
                        <div class="au-help">
                            Enter an email address the Secretary can personally access. A six-digit verification code will be sent to this address after the account is created. The Secretary must verify the address before setting their initial password.
                        </div>
                        @error('email')<div class="au-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            <section class="au-block">
                <h2 class="au-block-title"><i class="bi bi-shield-check"></i> Access</h2>
                <p class="au-block-note">This form creates Secretary accounts only. No role needs to be chosen.</p>

                <div class="au-fields">
                    <div class="au-c12">
                        <span class="au-label">System role</span>

                        {{-- Display only: no role field is submitted. The server always creates a Secretary. --}}
                        <div class="au-note">
                            <i class="bi bi-person-badge-fill"></i>
                            <div>
                                <b>Secretary</b><br>
                                The role is fixed by the system. Captain accounts cannot be created or assigned from this form.
                            </div>
                        </div>

                        @error('role')<div class="au-error">{{ $message }}</div>@enderror
                    </div>

                    <div class="au-c12">
                        <span class="au-label">Initial account status</span>
                        <input type="hidden" name="status" value="active">

                        <div class="au-note">
                            <i class="bi bi-check-circle-fill"></i>
                            <div>
                                <b>Active</b><br>
                                The Secretary account is created as active. Email verification and initial password setup must still be completed before the Secretary can use the account as intended.
                            </div>
                        </div>

                        @error('status')<div class="au-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            <section class="au-block">
                <h2 class="au-block-title"><i class="bi bi-key-fill"></i> Private password setup</h2>
                <p class="au-block-note">The Captain must not set or manage the Secretary's permanent password.</p>

                <div class="au-note">
                    <i class="bi bi-shield-lock"></i>
                    <div>
                        <b>How initial password setup works</b><br>
                        After the account is created, the Captain is signed out and the password-setup page opens on this device. The Secretary must enter the six-digit verification code sent to their personal email address. After successful verification, the Secretary can choose their own permanent password (at least 12 characters) in private.
                        <br><br>
                        <b>Protect the Secretary's privacy:</b> Hand the device to the Secretary after creating the account. Do not ask for or view the verification code or password. If the verification email does not arrive, use the resend option on the setup page. The verification code is separate from the password and must not be shared with anyone else.
                    </div>
                </div>
            </section>

            <div class="au-bar">
                <div class="au-bar-state" aria-live="polite">
                    <b id="checkCount">0</b> of 3 checks done
                </div>

                <a href="{{ route('users.index') }}" class="au-btn au-btn--line">Cancel</a>

                <button type="submit" class="au-btn" id="createUserButton">
                    <i class="bi bi-person-plus"></i> Create Secretary account
                </button>
            </div>
        </form>

        <aside class="au-side" aria-label="Account preview">
            <div class="au-side-label">
                <span>New account</span>
                <span class="au-live">Live preview</span>
            </div>

            <article class="au-card">
                <div class="au-card-top">
                    <div class="au-avatar" id="pvAvatar" aria-hidden="true">
                        <span id="pvInitial">?</span>
                        <img id="pvImg" alt="" hidden>
                    </div>

                    <h3 class="au-card-name is-empty" id="pvName">Full name</h3>
                    <p class="au-card-user is-empty" id="pvUsername">Username</p>
                    <p class="au-card-user is-empty" id="pvEmail">Email address</p>

                    <div class="au-card-tags">
                        <span class="au-chip is-role">Secretary</span>
                        <span class="au-chip is-on">Active</span>
                    </div>
                </div>

                <ul class="au-checks" aria-label="Account checklist">
                    <li id="ckName"><i class="bi bi-check-lg"></i> Full name entered</li>
                    <li id="ckUsername"><i class="bi bi-check-lg"></i> Valid username entered</li>
                    <li id="ckEmail"><i class="bi bi-check-lg"></i> Valid email entered</li>
                </ul>
            </article>

            <div class="au-note">
                <i class="bi bi-shield-lock"></i>
                <div>
                    <b>Account security</b><br>
                    The Captain manages account authorization and status. The Secretary verifies their personal email and chooses their own permanent password. Mandatory two-factor authentication remains part of the login process.
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

    const fields = {
        name: $('name'),
        username: $('username'),
        email: $('email')
    };

    const checks = {
        name: $('ckName'),
        username: $('ckUsername'),
        email: $('ckEmail')
    };

    const photo = {
        input: $('photo'),
        drop: $('photoDrop'),
        preview: $('photoPreview'),
        icon: $('photoIcon'),
        prompt: $('photoPrompt'),
        name: $('photoName'),
        actions: $('photoActions'),
        error: $('photoError')
    };

    let photoUrl = null;

    function showPhoto(file) {
        if (!file) return;

        const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
        const maxBytes = 2 * 1024 * 1024;

        if (!allowedTypes.includes(file.type)) {
            clearPhoto('Choose a JPG, PNG, or WebP image.');
            return;
        }

        if (file.size > maxBytes) {
            clearPhoto('The image must be 2 MB or smaller.');
            return;
        }

        if (photoUrl) {
            URL.revokeObjectURL(photoUrl);
        }

        photoUrl = URL.createObjectURL(file);

        photo.preview.src = photoUrl;
        photo.preview.hidden = false;
        photo.icon.hidden = true;
        photo.drop.classList.add('has-photo');
        photo.drop.classList.remove('is-bad');
        photo.prompt.innerHTML = '<u>Photo selected.</u> Click to change';
        photo.name.textContent = file.name;
        photo.name.hidden = false;
        photo.actions.hidden = false;
        photo.error.textContent = '';

        $('pvImg').src = photoUrl;
        $('pvImg').hidden = false;
        $('pvInitial').hidden = true;
    }

    function clearPhoto(message = '') {
        photo.input.value = '';

        if (photoUrl) {
            URL.revokeObjectURL(photoUrl);
            photoUrl = null;
        }

        photo.preview.removeAttribute('src');
        photo.preview.hidden = true;
        photo.icon.hidden = false;
        photo.drop.classList.remove('has-photo');
        photo.drop.classList.toggle('is-bad', Boolean(message));
        photo.prompt.innerHTML = '<u>Choose a photo</u> or drag it here';
        photo.name.textContent = '';
        photo.name.hidden = true;
        photo.actions.hidden = true;
        photo.error.textContent = message;

        $('pvImg').removeAttribute('src');
        $('pvImg').hidden = true;
        $('pvInitial').hidden = false;
    }

    photo.drop.addEventListener('click', () => photo.input.click());

    photo.drop.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            photo.input.click();
        }
    });

    photo.input.addEventListener('change', () => {
        showPhoto(photo.input.files[0]);
    });

    $('photoChange').addEventListener('click', () => photo.input.click());
    $('photoRemove').addEventListener('click', () => clearPhoto());

    ['dragenter', 'dragover'].forEach((name) => {
        photo.drop.addEventListener(name, (event) => {
            event.preventDefault();
            photo.drop.classList.add('is-over');
        });
    });

    ['dragleave', 'drop'].forEach((name) => {
        photo.drop.addEventListener(name, (event) => {
            event.preventDefault();
            photo.drop.classList.remove('is-over');
        });
    });

    photo.drop.addEventListener('drop', (event) => {
        const file = event.dataTransfer?.files?.[0];

        if (!file) return;

        try {
            const transfer = new DataTransfer();
            transfer.items.add(file);
            photo.input.files = transfer.files;
        } catch (error) {
            // Preview remains available if the browser blocks assigning files.
        }

        showPhoto(file);
    });

    function render() {
        const name = fields.name.value.trim();
        const username = fields.username.value.trim();
        const email = fields.email.value.trim();

        $('pvName').textContent = name || 'Full name';
        $('pvName').classList.toggle('is-empty', !name);
        $('pvInitial').textContent = name ? name.charAt(0).toUpperCase() : '?';

        $('pvUsername').textContent = username ? '@' + username : 'Username';
        $('pvUsername').classList.toggle('is-empty', !username);

        $('pvEmail').textContent = email || 'Email address';
        $('pvEmail').classList.toggle('is-empty', !email);

        const validUsername = /^[A-Za-z0-9_-]{1,50}$/.test(username);

        const done = {
            name: name.length > 0,
            username: validUsername,
            email: email.length > 0 && fields.email.validity.valid
        };

        Object.entries(done).forEach(([key, value]) => {
            checks[key].classList.toggle('is-done', value);
        });

        $('checkCount').textContent = Object.values(done).filter(Boolean).length;
    }

    form.addEventListener('input', render);
    form.addEventListener('change', render);

    fields.username.addEventListener('input', () => {
        fields.username.setCustomValidity('');
    });

    form.addEventListener('submit', (event) => {
        const username = fields.username.value.trim();

        if (!/^[A-Za-z0-9_-]{1,50}$/.test(username)) {
            event.preventDefault();

            fields.username.setCustomValidity(
                'Use 1–50 letters, numbers, dashes, or underscores.'
            );

            fields.username.reportValidity();
            fields.username.focus();
            return;
        }

        fields.username.setCustomValidity('');
    });

    render();
})();
</script>
@endsection
