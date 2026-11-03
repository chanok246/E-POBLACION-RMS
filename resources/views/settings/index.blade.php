@extends('layouts.app')

@section('title', 'Settings | e-Poblacion')

@push('styles')
<style>
    .sp{--surface:#fff;--sunken:#f4f7f6;--line:#e0e7e5;--line-strong:#c8d3d0;--ink:#0e2a33;--text:#2b4149;--muted:#66808a;--teal:#0f766e;--on-teal:#fff;--teal-soft:#dff3ef;--ok:#178a55;--ok-soft:#e0f4ea;--warn:#b7790a;--warn-soft:#fbf0d0;--coral:#d4492c;--coral-soft:#fbe6e0;--focus:rgba(15,118,110,.3);--shadow:0 1px 2px rgba(14,42,51,.05),0 8px 24px rgba(14,42,51,.06);font-family:system-ui,-apple-system,'Segoe UI',Roboto,sans-serif;color:var(--text)}
    [data-theme="dark"] .sp{--surface:#14262d;--sunken:#0f1f25;--line:#25404a;--line-strong:#33525d;--ink:#eaf5f3;--text:#c9dbde;--muted:#8aa5ac;--teal:#3dd0c0;--on-teal:#062a27;--teal-soft:#123a38;--ok:#56d99b;--ok-soft:#123626;--warn:#f3c14a;--warn-soft:#3a3016;--coral:#ff8a6b;--coral-soft:#3d2019;--focus:rgba(61,208,192,.35);--shadow:0 1px 2px rgba(0,0,0,.4),0 8px 24px rgba(0,0,0,.3)}
    @media(prefers-color-scheme:dark){[data-theme="system"] .sp{--surface:#14262d;--sunken:#0f1f25;--line:#25404a;--line-strong:#33525d;--ink:#eaf5f3;--text:#c9dbde;--muted:#8aa5ac;--teal:#3dd0c0;--on-teal:#062a27;--teal-soft:#123a38;--ok:#56d99b;--ok-soft:#123626;--warn:#f3c14a;--warn-soft:#3a3016;--coral:#ff8a6b;--coral-soft:#3d2019;--focus:rgba(61,208,192,.35);--shadow:0 1px 2px rgba(0,0,0,.4),0 8px 24px rgba(0,0,0,.3)}}
    .sp *,.sp *::before,.sp *::after{box-sizing:border-box}.sp :focus-visible{outline:3px solid var(--focus);outline-offset:2px}.sp .min-w-0{min-width:0}
    .sp-title{margin:0;font-weight:800;font-size:clamp(1.7rem,3vw,2.2rem);letter-spacing:-.025em;color:var(--ink)}.sp-sub{margin:6px 0 0;color:var(--muted)}.sp-side{position:sticky;top:1rem;display:grid;gap:16px}
    .sp-id{position:relative;overflow:hidden;padding:20px;border-radius:22px;color:#eaf5f3;background:linear-gradient(135deg,#082229 0%,#0b3a3e 58%,#0f5a54 100%);box-shadow:var(--shadow)}
    .sp-id::before{content:'';position:absolute;inset:0;opacity:.45;pointer-events:none;background-image:radial-gradient(rgba(255,255,255,.14) 1px,transparent 1.4px);background-size:16px 16px;-webkit-mask-image:linear-gradient(120deg,transparent 20%,#000 100%);mask-image:linear-gradient(120deg,transparent 20%,#000 100%)}
    .sp-id>*{position:relative}.sp-id-top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:18px;font-size:.78rem;font-weight:600;color:rgba(234,245,243,.75)}.sp-id-role{padding:3px 11px;border-radius:999px;border:1px solid rgba(255,255,255,.3);color:#fff;font-weight:700}
    .sp-id-body{display:flex;align-items:center;gap:14px}.sp-id-name{font-weight:800;font-size:1.25rem;line-height:1.2;color:#fff;overflow-wrap:anywhere}.sp-id-sub{margin-top:2px;font-size:.84rem;color:rgba(234,245,243,.72);overflow-wrap:anywhere}.sp-id-foot{margin-top:18px}
    .sp-pill{display:inline-flex;align-items:center;gap:7px;padding:4px 12px;border-radius:999px;font-size:.78rem;font-weight:700;background:rgba(255,255,255,.14);color:#eaf5f3}.sp-pill-ok{background:rgba(86,217,155,.2);color:#8ff0bd}
    .sp-avatar{position:relative;flex:0 0 64px;width:64px;height:64px;display:grid;place-items:center;overflow:hidden;border-radius:50%;font-weight:800;font-size:1.6rem;background:rgba(255,255,255,.16);border:2px solid rgba(255,255,255,.5)}.sp-avatar img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
    .sp-avatar-lg{flex-basis:96px;width:96px;height:96px;font-size:2.4rem;background:var(--teal-soft);color:var(--teal);border-color:var(--line-strong)}
    .sp-nav{display:grid;gap:4px;padding:6px;border-radius:18px;background:var(--surface);border:1px solid var(--line);box-shadow:var(--shadow)}
    .sp-nav button{display:flex;align-items:center;gap:12px;padding:11px 12px;border:0;border-radius:13px;background:transparent;text-align:left;color:var(--ink);cursor:pointer;transition:background .15s}
    .sp-nav button i{display:grid;place-items:center;width:36px;height:36px;flex:none;border-radius:11px;background:var(--sunken);color:var(--muted);font-size:1.05rem}
    .sp-nav button span{display:flex;flex-direction:column;font-weight:700;line-height:1.2}.sp-nav small{margin-top:2px;font-weight:500;font-size:.78rem;color:var(--muted)}.sp-nav button:hover{background:var(--sunken)}.sp-nav button[aria-selected="true"]{background:var(--teal-soft)}.sp-nav button[aria-selected="true"] i{background:var(--teal);color:var(--on-teal)}
    @media(max-width:991.98px){.sp-side{position:static}.sp-nav{display:flex;overflow-x:auto}.sp-nav button{flex:0 0 auto}.sp-nav small{display:none}}
    .sp-block{margin-bottom:16px;padding:26px 28px;border-radius:22px;background:var(--surface);border:1px solid var(--line);box-shadow:var(--shadow)}
    .sp-h{margin:0 0 4px;font-weight:800;font-size:1.1rem;color:var(--ink)}.sp-hnote{margin:0 0 20px;font-size:.9rem;color:var(--muted)}.sp .text-muted{color:var(--muted)!important}
    .sp .form-label{margin-bottom:7px;font-weight:700;font-size:.9rem;color:var(--ink)}.sp .form-control{min-height:48px;padding:0 14px;border-radius:12px;border:1px solid var(--line-strong);background:var(--sunken);color:var(--ink);font-weight:500;box-shadow:none}
    .sp .form-control::placeholder{color:var(--muted);opacity:.75}.sp .form-control:focus{background:var(--surface);border-color:var(--teal);box-shadow:0 0 0 4px var(--focus);color:var(--ink)}.sp .form-control.is-invalid{border-color:var(--coral);background-image:none}
    .sp .invalid-feedback,.sp .text-danger{color:var(--coral)!important;font-weight:500}.sp .form-text{color:var(--muted)}.sp .btn{border-radius:12px;font-weight:700}.sp .btn-primary{background:var(--teal);border-color:var(--teal);color:var(--on-teal)}
    .sp .btn-primary:hover,.sp .btn-primary:focus{background:var(--teal);border-color:var(--teal);color:var(--on-teal);filter:brightness(1.1)}.sp .btn-outline-danger{color:var(--coral);border-color:color-mix(in srgb,var(--coral) 45%,transparent);background:transparent}.sp .btn-outline-danger:hover{background:var(--coral);border-color:var(--coral);color:#fff}
    .sp-photo{display:flex;flex-wrap:wrap;align-items:center;gap:20px;padding:18px;border-radius:18px;background:var(--sunken);border:1px solid var(--line)}.sp-photo .sp-avatar-lg{box-shadow:0 0 0 4px var(--surface),0 0 0 5px var(--line-strong)}
    .sp-greet,.sp-note{display:flex;align-items:center;gap:12px;padding:13px 15px;border-radius:14px;background:var(--sunken);border:1px solid var(--line);font-size:.9rem;line-height:1.5;color:var(--text)}.sp-greet i,.sp-note i{flex:none;color:var(--teal);font-size:1.15rem}
    .sp-savebar{position:sticky;bottom:1rem;z-index:5;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:10px;padding:11px 16px;border-radius:16px;background:var(--ink);color:var(--surface);box-shadow:0 12px 30px rgba(0,0,0,.28)}.sp-savebar[hidden]{display:none}.sp-savebar .btn-link{color:inherit;opacity:.8}.sp-savebar .btn-primary{background:var(--teal);color:var(--on-teal)}
    .sp-themes{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:16px}.sp-theme{position:relative;padding:12px;border:2px solid var(--line);border-radius:18px;background:var(--sunken);text-align:left;color:var(--ink);cursor:pointer;transition:border-color .15s,transform .15s}.sp-theme:hover{transform:translateY(-2px)}.sp-theme.is-active{border-color:var(--teal);background:var(--teal-soft)}
    .sp-theme-label{display:flex;align-items:center;gap:8px;margin-top:12px;font-weight:700}.sp-check{position:absolute;top:18px;right:18px;font-size:1.2rem;color:var(--teal);opacity:0;transform:scale(.6);transition:.2s}.sp-theme.is-active .sp-check{opacity:1;transform:none}
    .sp-mini{display:grid;grid-template-columns:28% 1fr;grid-template-rows:14px 1fr 1fr 1fr;gap:5px;height:108px;padding:7px;border-radius:11px}.sp-mini i{grid-row:1/5;border-radius:6px}.sp-mini b{border-radius:5px}.sp-mini-light{background:#eef3f2}.sp-mini-light i{background:#cfdcd9}.sp-mini-light b{background:#fff}.sp-mini-dark{background:#0b171c}.sp-mini-dark i{background:#1f3841}.sp-mini-dark b{background:#14262d}
    .sp-google-status{display:flex;align-items:flex-start;gap:14px;padding:16px;border-radius:16px;background:var(--sunken);border:1px solid var(--line);color:var(--ink)}.sp-google-status>i{flex:none;font-size:1.4rem;color:var(--teal)}.sp-google-status strong{display:block;margin-bottom:3px}.sp-google-status p{color:var(--muted)}.sp-google-warning{background:var(--warn-soft);border-color:transparent}.sp-google-warning>i{color:var(--warn)}.sp-google-connected{background:var(--ok-soft);border-color:transparent}.sp-google-connected>i{color:var(--ok)}
    .sp .alert{border-radius:14px;border:1px solid transparent;font-weight:600}.sp .alert-success{background:var(--ok-soft);color:var(--ok)}.sp .alert-danger{background:var(--coral-soft);color:var(--coral)}.sp hr{border-color:var(--line);opacity:1}
    .sp-toast{position:fixed;left:50%;bottom:1.5rem;z-index:1080;transform:translate(-50%,20px);padding:10px 18px;border-radius:999px;background:#0e2a33;color:#fff;font-size:.9rem;font-weight:600;opacity:0;pointer-events:none;transition:.25s}[data-theme="dark"] .sp-toast{background:#eaf5f3;color:#0e2a33}.sp-toast.show{opacity:1;transform:translate(-50%,0)}
    @media(max-width:575.98px){.sp-block{padding:20px 16px}}@media(prefers-reduced-motion:reduce){.sp *,.sp-toast{transition:none!important}}
</style>
@endpush

@section('content')
@php
    $displayName = $user->nickname ?: $user->name;
    $initial = mb_strtoupper(mb_substr($displayName, 0, 1));
    $theme = $user->theme === 'dark' ? 'dark' : 'light';
    $submittedTab = old('settings_tab');

    // Only the email-verification form on the Profile tab still uses this.
    $currentPasswordErrorEmail = $errors->has('current_password') && $submittedTab === 'profile';
@endphp

<div class="sp">
    <div class="mb-4">
        <h1 class="sp-title">Your account</h1>
        <p class="sp-sub">Manage your profile and appearance.</p>
    </div>

    <div class="row g-4">
        <aside class="col-12 col-lg-4 col-xxl-3">
            <div class="sp-side">
                <div class="sp-id">
                    <div class="sp-id-top">
                        <span><i class="bi bi-buildings me-1"></i>Barangay Poblacion</span>
                        <span class="sp-id-role text-capitalize">{{ $user->role }}</span>
                    </div>
                    <div class="sp-id-body">
                        <div class="sp-avatar" data-avatar>
                            @if ($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="Profile photo">
                            @else
                                <span>{{ $initial }}</span>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <div class="sp-id-name" id="idName">{{ $displayName }}</div>
                            <div class="sp-id-sub" id="idFull">{{ $user->name }}</div>
                            <div class="sp-id-sub text-break" id="idMail">{{ $user->email ?: 'No email on file' }}</div>
                        </div>
                    </div>
                    <div class="sp-id-foot">
                        @if ($user->isActive())
                            <span class="sp-pill sp-pill-ok"><i class="bi bi-check-circle-fill"></i>Active account</span>
                        @else
                            <span class="sp-pill"><i class="bi bi-dash-circle"></i>Inactive account</span>
                        @endif
                    </div>
                </div>

                <nav class="sp-nav" role="tablist" aria-label="Settings sections">
                    <button type="button" role="tab" id="tab-profile" data-tab="profile" aria-controls="pane-profile">
                        <i class="bi bi-person-vcard"></i><span>Profile<small>Name, photo, email</small></span>
                    </button>
                    <button type="button" role="tab" id="tab-appearance" data-tab="appearance" aria-controls="pane-appearance">
                        <i class="bi bi-circle-half"></i><span>Appearance<small>Light or dark</small></span>
                    </button>
                </nav>
            </div>
        </aside>

        <main class="col-12 col-lg-8 col-xxl-9">
            <section class="sp-pane" id="pane-profile" role="tabpanel" aria-labelledby="tab-profile">
                <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data" id="profileForm" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="sp-block">
                        <h2 class="sp-h">Profile photo</h2>
                        <p class="sp-hnote">Shown on your ID card and in the top bar.</p>
                        <div class="sp-photo">
                            <div class="sp-avatar sp-avatar-lg" data-avatar>
                                @if ($user->avatar)
                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="Profile photo">
                                @else
                                    <span>{{ $initial }}</span>
                                @endif
                            </div>
                            <div>
                                <div class="d-flex flex-wrap gap-2">
                                    <label for="avatar" class="btn btn-primary btn-sm mb-0"><i class="bi bi-upload me-1"></i>Choose photo</label>
                                    @if ($user->avatar)
                                        <button type="submit" form="removeAvatarForm" class="btn btn-outline-danger btn-sm" onclick="return confirm('Remove your current profile photo?');">
                                            <i class="bi bi-trash me-1"></i>Remove photo
                                        </button>
                                    @endif
                                </div>
                                <input type="file" class="visually-hidden @error('avatar') is-invalid @enderror" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.webp">
                                <div class="small text-muted mt-2" id="avatarHint">JPG, PNG or WEBP, up to 2 MB.</div>
                                @error('avatar')<div class="small text-danger">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="sp-block">
                        <h2 class="sp-h">Your details</h2>
                        <p class="sp-hnote">Update your name and display name. Manage your email in Email Verification below.</p>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label">Full name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="nickname" class="form-label">Display name <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="text" class="form-control @error('nickname') is-invalid @enderror" id="nickname" name="nickname" value="{{ old('nickname', $user->nickname) }}" maxlength="100" placeholder="What should we call you?">
                                @error('nickname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12"><div class="sp-greet"><i class="bi bi-chat-heart"></i><span id="greeting"></span></div></div>
                            <div class="col-12">
                                <label for="email" class="form-label">Registered email address</label>
                                <input type="email" class="form-control" id="email" value="{{ $user->email }}" maxlength="255" autocomplete="email" readonly>
                                <div class="form-text">To verify or change your email, use Email Verification below.</div>
                            </div>
                            <div class="col-12">
                                <div class="sp-note"><i class="bi bi-lock"></i><span>Your role is <strong class="text-capitalize">{{ $user->role }}</strong>. Only an authorized administrator can change it in User Management.</span></div>
                            </div>
                        </div>
                    </div>

                    <div class="sp-savebar" id="saveBar" hidden>
                        <span><i class="bi bi-pencil-square me-2"></i>You have unsaved changes</span>
                        <span class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-link text-decoration-none" id="discardBtn">Discard</button>
                            <button type="submit" class="btn btn-sm btn-primary px-3"><i class="bi bi-check-lg me-1"></i>Save changes</button>
                        </span>
                    </div>
                </form>

                <form id="removeAvatarForm" method="POST" action="{{ route('settings.avatar.remove') }}" class="d-none">
                    @csrf
                    @method('DELETE')
                </form>

                <div class="sp-block">
                    <h2 class="sp-h">Email verification</h2>
                    <p class="sp-hnote">Verify ownership of an email address before it can be used for account recovery. Internet access is required to send email.</p>

                    @if ($user->email_verified_at)
                        <div class="sp-google-status sp-google-connected mb-4">
                            <i class="bi bi-patch-check-fill"></i><div><strong>Email address verified</strong><p class="mb-0 small">Your registered email has been verified and may be used for supported account recovery procedures.</p></div>
                        </div>
                    @else
                        <div class="sp-google-status sp-google-warning mb-4">
                            <i class="bi bi-exclamation-triangle-fill"></i><div><strong>Email address not verified</strong><p class="mb-0 small">Verify ownership before using this address for account recovery.</p></div>
                        </div>
                    @endif

                    @if (session('email_verification_status'))
                        <div class="alert alert-success" role="status">{{ session('email_verification_status') }}</div>
                    @endif
                    @if ($errors->has('email_verification'))
                        <div class="alert alert-danger" role="alert">{{ $errors->first('email_verification') }}</div>
                    @endif

                    <form method="POST" action="{{ route('settings.email-verification.send') }}" class="mb-4">
                        @csrf
                        <input type="hidden" name="settings_tab" value="profile">
                        <div class="mb-3">
                            <label for="verification_email" class="form-label">Email address to verify</label>
                            <input type="email" id="verification_email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">A code will be sent to this address. If changing your email, enter the new address. The change should take effect only after successful verification.</div>
                        </div>
                        <div class="mb-3">
                            <label for="email_verification_current_password" class="form-label">Confirm your current password</label>
                            <input type="password" id="email_verification_current_password" name="current_password" class="form-control {{ $currentPasswordErrorEmail ? 'is-invalid' : '' }}" autocomplete="current-password" required>
                            @if ($currentPasswordErrorEmail)<div class="invalid-feedback">{{ $errors->first('current_password') }}</div>@endif
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-envelope-check me-2"></i>Send verification code</button>
                    </form>

                    <hr class="my-4">
                    <h3 class="sp-h">Enter your verification code</h3>
                    <p class="sp-hnote">Enter the code sent to the address you requested to verify.</p>
                    <form method="POST" action="{{ route('settings.email-verification.verify') }}">
                        @csrf
                        <input type="hidden" name="settings_tab" value="profile">
                        <div class="mb-3">
                            <label for="verification_code" class="form-label">Six-digit verification code</label>
                            <input type="text" id="verification_code" name="verification_code" class="form-control @error('verification_code') is-invalid @enderror" inputmode="numeric" pattern="[0-9]{6}" minlength="6" maxlength="6" autocomplete="one-time-code" placeholder="Enter 6-digit code" required>
                            @error('verification_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-shield-check me-2"></i>Verify email address</button>
                    </form>
                </div>
            </section>

            <section class="sp-pane" id="pane-appearance" role="tabpanel" aria-labelledby="tab-appearance" hidden>
                <div class="sp-block">
                    <h2 class="sp-h">Choose how the system looks</h2>
                    <p class="sp-hnote">Applies right away and is saved to your account.</p>
                    <div class="sp-themes" role="radiogroup" aria-label="Theme">
                        <button type="button" class="sp-theme" data-theme-choice="light" role="radio"><span class="sp-mini sp-mini-light"><i></i><b></b><b></b><b></b></span><span class="sp-theme-label"><i class="bi bi-sun-fill"></i>Light</span><i class="bi bi-check-circle-fill sp-check"></i></button>
                        <button type="button" class="sp-theme" data-theme-choice="dark" role="radio"><span class="sp-mini sp-mini-dark"><i></i><b></b><b></b><b></b></span><span class="sp-theme-label"><i class="bi bi-moon-stars-fill"></i>Dark</span><i class="bi bi-check-circle-fill sp-check"></i></button>
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<div class="sp-toast" id="toast" role="status" aria-live="polite"></div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const $ = (id) => document.getElementById(id);
    let toastTimer;

    const toast = (message) => {
        const element = $('toast');
        if (!element) return;
        element.textContent = message;
        element.classList.add('show');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(() => element.classList.remove('show'), 2200);
    };

    /* ---------------- Tabs: Profile and Appearance only ----------------
       Old links such as #authenticator, #google or #captain-recovery
       fall back to Profile. */
    const tabs = document.querySelectorAll('.sp-nav [data-tab]');
    const validTabs = ['profile', 'appearance'];

    const showTab = (name) => {
        const selected = validTabs.includes(name) ? name : 'profile';

        tabs.forEach((button) => {
            const active = button.dataset.tab === selected;
            button.setAttribute('aria-selected', String(active));
            button.setAttribute('tabindex', active ? '0' : '-1');
        });

        document.querySelectorAll('.sp-pane').forEach((pane) => {
            pane.hidden = pane.id !== 'pane-' + selected;
        });

        if (location.hash !== '#' + selected) history.replaceState(null, '', '#' + selected);
    };

    tabs.forEach((button) => button.addEventListener('click', () => showTab(button.dataset.tab)));

    const submittedTab = @json($submittedTab);
    const fromHash = location.hash.slice(1);

    if (validTabs.includes(submittedTab)) {
        showTab(submittedTab);
    } else {
        showTab(validTabs.includes(fromHash) ? fromHash : 'profile');
    }

    /* ---------------- Profile form ---------------- */
    const name = $('name');
    const nickname = $('nickname');
    const email = $('email');
    const avatar = $('avatar');
    const form = $('profileForm');
    const saveBar = $('saveBar');

    if (name && nickname && email && avatar && form && saveBar) {
        const startingValues = { name: name.value, nickname: nickname.value, email: email.value };

        const greetingWord = () => {
            const hour = new Date().getHours();
            if (hour < 12) return 'Good morning';
            if (hour < 18) return 'Good afternoon';
            return 'Good evening';
        };

        const refreshProfile = () => {
            const displayName = nickname.value.trim() || name.value.trim() || 'Your name';
            $('idName').textContent = displayName;
            $('idFull').textContent = name.value.trim();
            $('idMail').textContent = email.value.trim() || 'No email on file';
            $('greeting').textContent = greetingWord() + ', ' + displayName + '! This is how the system will greet you.';
            saveBar.hidden = !(name.value !== startingValues.name || nickname.value !== startingValues.nickname || (avatar.files && avatar.files.length > 0));
        };

        [name, nickname].forEach((element) => element.addEventListener('input', refreshProfile));
        document.querySelectorAll('[data-avatar]').forEach((element) => element.dataset.original = element.innerHTML);
        refreshProfile();

        $('discardBtn')?.addEventListener('click', () => {
            form.reset();
            name.value = startingValues.name;
            nickname.value = startingValues.nickname;
            email.value = startingValues.email;
            avatar.value = '';
            document.querySelectorAll('[data-avatar]').forEach((element) => {
                if (element.dataset.original !== undefined) element.innerHTML = element.dataset.original;
            });
            $('avatarHint').textContent = 'JPG, PNG or WEBP, up to 2 MB.';
            refreshProfile();
        });

        avatar.addEventListener('change', (event) => {
            const file = event.target.files[0];
            if (!file) {
                refreshProfile();
                return;
            }
            if (!file.type.startsWith('image/')) {
                avatar.value = '';
                toast('Please choose a valid image file.');
                refreshProfile();
                return;
            }
            if (file.size > 2 * 1024 * 1024) {
                avatar.value = '';
                toast('That photo is over 2 MB. Choose a smaller one.');
                refreshProfile();
                return;
            }

            const reader = new FileReader();
            reader.onload = (readerEvent) => {
                document.querySelectorAll('[data-avatar]').forEach((element) => {
                    const image = document.createElement('img');
                    image.alt = 'Profile photo';
                    image.src = readerEvent.target.result;
                    element.replaceChildren(image);
                });
                $('avatarHint').textContent = file.name + ' is ready. Save changes to keep it.';
                refreshProfile();
            };
            reader.readAsDataURL(file);
        });
    }

    /* ---------------- Appearance / theme ---------------- */
    if (!window.EPTheme) {
        const root = document.documentElement;
        const applyTheme = (theme) => {
            root.setAttribute('data-theme', theme);
            try { localStorage.setItem('barangay-theme', theme); } catch (error) {}
            $('topbarThemeToggle')?.setAttribute('aria-pressed', String(theme === 'dark'));
            if ($('topbarThemeLabel')) $('topbarThemeLabel').textContent = theme === 'dark' ? 'Light Mode' : 'Dark Mode';
            if ($('topbarThemeIcon')) $('topbarThemeIcon').className = theme === 'dark' ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
            document.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
        };

        window.EPTheme = {
            current() { return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'; },
            async set(theme) {
                const previousTheme = this.current();
                applyTheme(theme);
                try {
                    const response = await fetch(@json(route('settings.theme.update')), {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ theme })
                    });
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return true;
                } catch (error) {
                    console.error('Theme could not be saved:', error);
                    applyTheme(previousTheme);
                    return false;
                }
            }
        };
    }

    const themeCards = document.querySelectorAll('[data-theme-choice]');
    const paintTheme = (theme) => {
        themeCards.forEach((card) => {
            const active = card.dataset.themeChoice === theme;
            card.classList.toggle('is-active', active);
            card.setAttribute('aria-checked', String(active));
        });
    };

    paintTheme(document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light');
    document.addEventListener('themechange', (event) => paintTheme(event.detail.theme));

    themeCards.forEach((card) => {
        card.addEventListener('click', async () => {
            const nextTheme = card.dataset.themeChoice;
            if (window.EPTheme.current() === nextTheme) return;
            const saved = await window.EPTheme.set(nextTheme);
            toast(saved
                ? (nextTheme === 'dark' ? 'Dark' : 'Light') + ' mode saved to your account'
                : "Couldn't save your theme. Try again.");
        });
    });
});
</script>
@endsection