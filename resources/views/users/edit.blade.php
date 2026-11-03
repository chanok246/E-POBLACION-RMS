
@extends('layouts.app')

@section('title', 'Edit User | e-Poblacion')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    .eu {
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

    [data-theme="dark"] .eu {
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
        [data-theme="system"] .eu {
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

    .eu *, .eu *::before, .eu *::after { box-sizing: border-box; }
    .eu a { text-decoration: none; }
    .eu :focus-visible { outline: 3px solid var(--focus); outline-offset: 2px; }

    .eu-head { display: flex; flex-wrap: wrap; gap: 16px; align-items: flex-end; justify-content: space-between; margin-bottom: 26px; }
    .eu-crumbs { display: flex; align-items: center; gap: 8px; margin-bottom: 10px; font-size: .85rem; color: var(--muted); }
    .eu-crumbs a { color: var(--muted); }
    .eu-crumbs a:hover { color: var(--ink); text-decoration: underline; }
    .eu-title { margin: 0; font-family: 'Bricolage Grotesque', 'Segoe UI', sans-serif; font-weight: 800; font-size: clamp(1.8rem, 3.2vw, 2.4rem); letter-spacing: -.02em; line-height: 1.1; color: var(--ink); }
    .eu-sub { margin: 8px 0 0; color: var(--muted); max-width: 60ch; }

    .eu-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        min-height: 44px; padding: 0 18px; border-radius: 12px; cursor: pointer;
        font: 600 .95rem 'Figtree', sans-serif;
        background: var(--ink); color: var(--surface); border: 1px solid var(--ink);
        transition: filter .15s, transform .1s;
    }
    .eu-btn:hover { filter: brightness(1.12); color: var(--surface); }
    .eu-btn:active { transform: translateY(1px); }
    .eu-btn--line { background: transparent; color: var(--ink); border-color: var(--line-strong); }
    .eu-btn--line:hover { background: var(--sunken); color: var(--ink); filter: none; }
    .eu-btn--sm { min-height: 38px; padding: 0 14px; font-size: .86rem; }

    .eu-alert {
        display: flex; gap: 12px; padding: 14px 16px; border-radius: 14px; margin-bottom: 20px; font-size: .95rem;
        background: var(--coral-soft); color: var(--coral);
        border: 1px solid color-mix(in srgb, var(--coral) 30%, transparent);
    }
    .eu-alert i { font-size: 1.1rem; margin-top: 1px; }
    .eu-alert strong { display: block; }
    .eu-alert ul { margin: 6px 0 0; padding-left: 18px; }
    .eu-alert-close { margin-left: auto; background: none; border: 0; color: inherit; cursor: pointer; font-size: 1.2rem; line-height: 1; }

    .eu-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 26px; align-items: start; }
    .eu-panel, .eu-card { background: var(--surface); border: 1px solid var(--line); border-radius: 20px; box-shadow: var(--shadow); overflow: hidden; }

    .eu-block { padding: 26px 28px; }
    .eu-block + .eu-block { border-top: 1px solid var(--line); }
    .eu-block-title { display: flex; align-items: center; gap: 12px; margin: 0 0 4px; font-family: 'Bricolage Grotesque', 'Segoe UI', sans-serif; font-weight: 700; font-size: 1.15rem; color: var(--ink); }
    .eu-block-title i { display: grid; place-items: center; width: 34px; height: 34px; border-radius: 10px; background: var(--teal-soft); color: var(--teal); }
    .eu-block-note { margin: 0 0 22px 46px; color: var(--muted); font-size: .9rem; }

    .eu-fields { display: grid; gap: 20px; }
    .eu-label { display: block; margin-bottom: 7px; font-weight: 600; font-size: .92rem; color: var(--ink); }
    .eu-req { color: var(--coral); margin-left: 3px; }

    .eu-input, .eu-select {
        width: 100%; min-height: 48px; padding: 0 14px; border-radius: 12px;
        border: 1px solid var(--line-strong); background: var(--sunken); color: var(--ink);
        font: 500 .98rem 'Figtree', sans-serif; transition: border-color .15s, box-shadow .15s;
    }
    .eu-select { padding-right: 40px; }
    .eu-input:focus, .eu-select:focus { outline: none; background: var(--surface); border-color: var(--teal); box-shadow: 0 0 0 4px var(--focus); }
    .eu-select option { background: var(--surface); color: var(--ink); }
    .is-invalid { border-color: var(--coral) !important; }
    .eu-error { margin-top: 6px; font-size: .84rem; font-weight: 500; color: var(--coral); }
    .eu-help { margin-top: 6px; font-size: .82rem; color: var(--muted); line-height: 1.5; }

    .eu-photo { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; padding: 18px; border-radius: 18px; border: 1px solid var(--line); background: var(--sunken); }
    .eu-photo-img {
        position: relative; flex: none; display: grid; place-items: center; width: 84px; height: 84px; border-radius: 50%; overflow: hidden;
        background: var(--teal-soft); color: var(--teal); font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 2.2rem;
        box-shadow: 0 0 0 4px var(--surface), 0 0 0 5px var(--line-strong);
    }
    .eu-photo-img img, .eu-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .eu-photo-img img[hidden], .eu-avatar img[hidden] { display: none; }
    .eu-photo-body { min-width: 0; display: grid; gap: 10px; }
    .eu-photo-name { font-size: .85rem; font-weight: 700; color: var(--ok); overflow-wrap: anywhere; }
    .eu-photo-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; }
    .eu-check { display: inline-flex; align-items: center; gap: 8px; font-size: .88rem; color: var(--ink); cursor: pointer; }
    .eu-check input { width: 18px; height: 18px; accent-color: var(--coral); }

    .eu-note { display: flex; gap: 12px; padding: 16px; border-radius: 16px; background: var(--sunken); border: 1px solid var(--line); font-size: .86rem; line-height: 1.55; color: var(--muted); }
    .eu-note i { color: var(--teal); font-size: 1.2rem; }
    .eu-note b { color: var(--ink); }
    .eu-note a { color: var(--teal); text-decoration: underline; text-underline-offset: 3px; }

    .eu-bar { position: sticky; bottom: 0; z-index: 5; display: flex; align-items: center; justify-content: flex-end; gap: 12px; flex-wrap: wrap; padding: 14px 28px; border-top: 1px solid var(--line); background: var(--surface); }

    .eu-side { position: sticky; top: 20px; display: grid; gap: 14px; }
    .eu-side-label { display: flex; align-items: center; justify-content: space-between; font-size: .85rem; font-weight: 600; color: var(--muted); }
    .eu-live { display: inline-flex; align-items: center; gap: 7px; }
    .eu-live::before { content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--teal); }
    .eu-card-top { padding: 26px 22px 22px; text-align: center; background: var(--sunken); }
    .eu-avatar {
        position: relative; overflow: hidden; display: grid; place-items: center; width: 88px; height: 88px; margin: 0 auto 14px; border-radius: 50%;
        font-family: 'Bricolage Grotesque', sans-serif; font-weight: 800; font-size: 2.3rem; background: var(--teal-soft); color: var(--teal);
        box-shadow: 0 0 0 4px var(--surface);
    }
    .eu-card-name { margin: 0; font-family: 'Bricolage Grotesque', sans-serif; font-weight: 700; font-size: 1.25rem; color: var(--ink); overflow-wrap: anywhere; }
    .eu-card-user { margin: 5px 0 0; color: var(--text); font-weight: 500; overflow-wrap: anywhere; }
    .eu-card-tags { display: flex; justify-content: center; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
    .eu-chip { display: inline-flex; align-items: center; gap: 7px; padding: 5px 12px; border-radius: 999px; font-size: .8rem; font-weight: 700; }
    .eu-chip.is-role { background: var(--teal-soft); color: var(--teal); }
    .eu-chip.is-on { background: var(--ok-soft); color: var(--ok); }
    .eu-chip.is-off { background: var(--sunken); color: var(--muted); border: 1px solid var(--line-strong); }

    @media (max-width: 1080px) {
        .eu-grid { grid-template-columns: 1fr; }
        .eu-side { position: static; order: -1; }
    }
    @media (max-width: 640px) {
        .eu-block { padding: 22px 18px; }
        .eu-block-note { margin-left: 0; }
        .eu-bar { padding: 12px 18px; }
        .eu-bar .eu-btn { flex: 1; }
        .eu-head > .eu-btn { width: 100%; }
    }
    @media (prefers-reduced-motion: reduce) {
        .eu *, .eu *::before, .eu *::after { animation: none !important; transition: none !important; }
    }
</style>
@endpush

@section('content')
@php
    $isCaptainTarget = $user->isCaptain();
    $roleLabel = $isCaptainTarget ? 'Captain' : 'Secretary';
    $existingAvatar = $user->hasAvatar() ? asset('storage/' . $user->avatar) : null;
    $currentStatus = old('status', $user->status);
    $displayName = old('name', $user->name);
@endphp

<div class="container-fluid py-4 eu">

    <header class="eu-head">
        <div>
            <nav class="eu-crumbs" aria-label="Breadcrumb">
                <a href="{{ route('users.index') }}">User Management</a>
                <i class="bi bi-chevron-right" style="font-size:.7rem"></i>
                <span>Edit {{ $roleLabel }}</span>
            </nav>

            <h1 class="eu-title">{{ $isCaptainTarget ? 'Edit Captain profile' : 'Edit Secretary account' }}</h1>
            <p class="eu-sub">
                @if ($isCaptainTarget)
                    Update your name, username, email, and photo. Your role, password, and two-factor settings are not changed here.
                @else
                    Update the Secretary's details and account status. The role and password cannot be changed here.
                @endif
            </p>
        </div>

        <a href="{{ route('users.index') }}" class="eu-btn eu-btn--line">
            <i class="bi bi-arrow-left"></i> Back to users
        </a>
    </header>

    @if ($errors->any())
        <div class="eu-alert" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Fix {{ $errors->count() }} {{ \Illuminate\Support\Str::plural('issue', $errors->count()) }} before saving</strong>
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

        <form
            id="editForm"
            action="{{ route('users.update', $user) }}"
            method="POST"
            enctype="multipart/form-data"
            class="eu-panel"
            autocomplete="off"
        >
            @csrf
            @method('PUT')

            <section class="eu-block">
                <h2 class="eu-block-title"><i class="bi bi-camera-fill"></i> Profile photo</h2>
                <p class="eu-block-note">Optional. JPG, PNG or WebP, up to 2 MB.</p>

                <div class="eu-photo">
                    <div class="eu-photo-img" aria-hidden="true">
                        <span id="photoInitial">{{ strtoupper(mb_substr($displayName, 0, 1)) ?: '?' }}</span>
                        <img id="photoPreview" alt="" @if ($existingAvatar) src="{{ $existingAvatar }}" @else hidden @endif>
                    </div>

                    <div class="eu-photo-body">
                        <div class="eu-photo-actions">
                            <button type="button" class="eu-btn eu-btn--line eu-btn--sm" id="photoChoose">
                                <i class="bi bi-upload"></i> Choose photo
                            </button>

                            @if ($existingAvatar)
                                <label class="eu-check">
                                    <input type="hidden" name="remove_photo" value="0">
                                    <input type="checkbox" name="remove_photo" value="1" id="removePhoto" @checked(old('remove_photo'))>
                                    Remove current photo
                                </label>
                            @endif
                        </div>

                        <span class="eu-photo-name" id="photoName" hidden></span>
                        <input type="file" name="photo" id="photo" accept="image/png,image/jpeg,image/webp" hidden>
                    </div>
                </div>

                <div class="eu-error" id="photoError" role="alert">@error('photo'){{ $message }}@enderror</div>
            </section>

            <section class="eu-block">
                <h2 class="eu-block-title"><i class="bi bi-person-fill"></i> Account details</h2>
                <p class="eu-block-note">
                    @if ($isCaptainTarget)
                        Update your name, username, and email address.
                    @else
                        Update the Secretary's name, username, and personal email address.
                    @endif
                </p>

                <div class="eu-fields">
                    <div>
                        <label for="name" class="eu-label">Full name<span class="eu-req">*</span></label>
                        <input type="text" name="name" id="name" class="eu-input @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required>
                        @error('name')<div class="eu-error">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label for="username" class="eu-label">Username<span class="eu-req">*</span></label>
                        <input type="text" name="username" id="username" class="eu-input @error('username') is-invalid @enderror"
                               value="{{ old('username', $user->username) }}" maxlength="50" autocomplete="username"
                               autocapitalize="none" spellcheck="false" pattern="[A-Za-z0-9_-]{1,50}"
                               title="Use 1–50 letters, numbers, dashes, or underscores." required>
                        <div class="eu-help">Letters, numbers, dashes, or underscores. Used to sign in.</div>
                        @error('username')<div class="eu-error">{{ $message }}</div>@enderror
                    </div>

                    <div>
                        <label for="email" class="eu-label">
                            @if ($isCaptainTarget)
                                Email address
                            @else
                                Secretary's personal email address
                            @endif
                            <span class="eu-req">*</span>
                        </label>
                        <input type="email" name="email" id="email" class="eu-input @error('email') is-invalid @enderror"
                               value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required>

                        @if ($isCaptainTarget)
                            <div class="eu-help">
                                Use an email address you can access. Changing this field does not itself verify email ownership.
                            </div>
                        @else
                            <div class="eu-help">
                                Use the Secretary's own email address that they can access. Saving a new address here does not send a verification code or confirm ownership; the address must be verified through the system's email-verification flow before it is relied on for recovery.
                            </div>
                        @endif

                        @error('email')<div class="eu-error">{{ $message }}</div>@enderror
                    </div>
                </div>
            </section>

            <section class="eu-block">
                <h2 class="eu-block-title"><i class="bi bi-shield-check"></i> Access</h2>
                <p class="eu-block-note">Role and status are protected fields.</p>

                <div class="eu-fields">
                    {{-- Display only: no role field is submitted. --}}
                    <div class="eu-note">
                        <i class="bi bi-person-badge-fill"></i>
                        <div>
                            <b>{{ $roleLabel }}</b><br>
                            @if ($isCaptainTarget)
                                The Captain role is permanent and cannot be changed from this page.
                            @else
                                The Secretary role is fixed and cannot be changed from this page.
                            @endif
                        </div>
                    </div>

                    @if ($canChangeStatus)
                        <div>
                            <label for="status" class="eu-label">Account status<span class="eu-req">*</span></label>
                            <select name="status" id="status" class="eu-select @error('status') is-invalid @enderror" required>
                                <option value="active" @selected($currentStatus === 'active')>Active</option>
                                <option value="inactive" @selected($currentStatus === 'inactive')>Inactive</option>
                            </select>
                            <div class="eu-help">Inactive accounts cannot sign in. Deactivate a replaced Secretary account rather than deleting its record.</div>
                            @error('status')<div class="eu-error">{{ $message }}</div>@enderror
                        </div>
                    @else
                        <div class="eu-note">
                            <i class="bi bi-lock-fill"></i>
                            <div>
                                <b>Status: {{ ucfirst($user->status) }}</b><br>
                                The Captain's account status cannot be changed from this form.
                            </div>
                        </div>
                    @endif
                </div>
            </section>

            <section class="eu-block">
                <h2 class="eu-block-title"><i class="bi bi-key-fill"></i> Password and two-factor</h2>
                <p class="eu-block-note">Managed separately and not editable here.</p>

                <div class="eu-note">
                    <i class="bi bi-shield-lock"></i>
                    <div>
                        @if ($isCaptainTarget)
                            <b>Your password and two-factor authentication</b><br>
                            Change your own password in <a href="{{ route('settings.index') }}">Settings</a>. Mandatory two-factor authentication stays in effect.
                        @else
                            <b>The Secretary controls their own password</b><br>
                            The Captain cannot view, set, or reset a Secretary's password. Two-factor authentication remains mandatory at sign-in. Initial password setup requires the Secretary to verify their own email address first.
                        @endif
                    </div>
                </div>
            </section>

            <div class="eu-bar">
                <a href="{{ route('users.index') }}" class="eu-btn eu-btn--line">Cancel</a>
                <button type="submit" class="eu-btn">
                    <i class="bi bi-check2-circle"></i> Save changes
                </button>
            </div>
        </form>

        <aside class="eu-side" aria-label="Account preview">
            <div class="eu-side-label">
                <span>Account</span>
                <span class="eu-live">Live preview</span>
            </div>

            <article class="eu-card">
                <div class="eu-card-top">
                    <div class="eu-avatar" aria-hidden="true">
                        <span id="pvInitial">{{ strtoupper(mb_substr($displayName, 0, 1)) ?: '?' }}</span>
                        <img id="pvImg" alt="" @if ($existingAvatar) src="{{ $existingAvatar }}" @else hidden @endif>
                    </div>

                    <h3 class="eu-card-name" id="pvName">{{ $displayName }}</h3>
                    <p class="eu-card-user" id="pvUsername">@ {{ old('username', $user->username) }}</p>
                    <p class="eu-card-user" id="pvEmail">{{ old('email', $user->email) }}</p>

                    <div class="eu-card-tags">
                        <span class="eu-chip is-role">{{ $roleLabel }}</span>
                        <span class="eu-chip {{ $currentStatus === 'active' ? 'is-on' : 'is-off' }}" id="pvStatus">{{ ucfirst($currentStatus) }}</span>
                    </div>
                </div>
            </article>

            <div class="eu-note">
                <i class="bi bi-shield-lock"></i>
                <div>
                    <b>Protected fields</b><br>
                    Role, password, and two-factor settings are never changed by this form. The Captain's account status is also protected. Email verification is a separate process and is not completed by saving this form.
                </div>
            </div>
        </aside>
    </div>
</div>

<script>
(function () {
    const $ = (id) => document.getElementById(id);
    const form = $('editForm');
    if (!form) return;

    const existingSrc = $('photoPreview').getAttribute('src');
    const input = $('photo');
    const remove = $('removePhoto');
    let newUrl = null;

    function currentSrc() {
        if (newUrl) return newUrl;
        if (remove && remove.checked) return null;
        return existingSrc;
    }

    function syncAvatar() {
        const src = currentSrc();
        ['photoPreview', 'pvImg'].forEach((id) => {
            const img = $(id);
            if (src) { img.src = src; img.hidden = false; }
            else { img.removeAttribute('src'); img.hidden = true; }
        });
        $('photoInitial').hidden = Boolean(src);
        $('pvInitial').hidden = Boolean(src);
    }

    function syncText() {
        const name = $('name').value.trim();
        const initial = name ? name.charAt(0).toUpperCase() : '?';
        $('pvName').textContent = name || 'Full name';
        $('pvInitial').textContent = initial;
        $('photoInitial').textContent = initial;
        $('pvUsername').textContent = '@' + $('username').value.trim();
        $('pvEmail').textContent = $('email').value.trim();

        const status = $('status');
        if (status) {
            const active = status.value === 'active';
            $('pvStatus').textContent = active ? 'Active' : 'Inactive';
            $('pvStatus').className = 'eu-chip ' + (active ? 'is-on' : 'is-off');
        }
    }

    $('photoChoose').addEventListener('click', () => input.click());

    input.addEventListener('change', () => {
        const file = input.files[0];
        const error = $('photoError');
        error.textContent = '';
        if (!file) return;

        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            input.value = '';
            error.textContent = 'Choose a JPG, PNG, or WebP image.';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            input.value = '';
            error.textContent = 'The image must be 2 MB or smaller.';
            return;
        }

        if (newUrl) URL.revokeObjectURL(newUrl);
        newUrl = URL.createObjectURL(file);
        if (remove) remove.checked = false;
        $('photoName').textContent = file.name;
        $('photoName').hidden = false;
        syncAvatar();
    });

    if (remove) {
        remove.addEventListener('change', () => {
            if (remove.checked && newUrl) {
                URL.revokeObjectURL(newUrl);
                newUrl = null;
                input.value = '';
                $('photoName').hidden = true;
            }
            syncAvatar();
        });
    }

    form.addEventListener('input', syncText);
    form.addEventListener('change', syncText);

    syncAvatar();
    syncText();
})();
</script>
@endsection
