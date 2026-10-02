@extends('layouts.app')

@section('title', 'Settings | e-Poblacion')

@section('content')
@php
    $displayName = $user->nickname ?: $user->name;
    $initial = mb_strtoupper(mb_substr($displayName, 0, 1));
    $startTab = $errors->hasAny(['current_password', 'password']) ? 'security' : 'profile';
    $theme = $user->theme === 'dark' ? 'dark' : 'light';
@endphp

<div class="sp">

    <div class="mb-4">
        <h1 class="h3 fw-bold mb-1">Your account</h1>
        <p class="text-muted mb-0">Changes to your name and photo show on your ID card as you make them.</p>
    </div>

    <div class="row g-4">

        {{-- ============ LEFT: LIVE ID CARD + NAV ============ --}}
        <aside class="col-12 col-lg-4 col-xxl-3">
            <div class="sp-side">

                <div class="sp-id">
                    <div class="sp-id-top">
                        <span class="sp-id-brand"><i class="bi bi-buildings me-1"></i>Barangay Poblacion</span>
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
                    <button type="button" role="tab" data-tab="profile"><i class="bi bi-person-vcard"></i><span>Profile<small>Name, photo, email</small></span></button>
                    <button type="button" role="tab" data-tab="appearance"><i class="bi bi-circle-half"></i><span>Appearance<small>Light or dark</small></span></button>
                    <button type="button" role="tab" data-tab="security"><i class="bi bi-shield-lock"></i><span>Password<small>Keep your account safe</small></span></button>
                </nav>
            </div>
        </aside>

        {{-- ============ RIGHT: PANES ============ --}}
        <main class="col-12 col-lg-8 col-xxl-9">

            {{-- PROFILE --}}
            <section class="sp-pane" id="pane-profile" role="tabpanel">
                <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data" id="profileForm" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="theme" name="theme" value="{{ $theme }}">

                    <div class="sp-block">
                        <h2 class="sp-h">Profile photo</h2>
                        <div class="d-flex flex-wrap align-items-center gap-3">
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
                                        <button type="submit" form="removeAvatarForm" class="btn btn-outline-danger btn-sm"
                                            onclick="return confirm('Remove your current profile photo?');">
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
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="name" class="form-label">Full name</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name"
                                    value="{{ old('name', $user->name) }}" maxlength="255" autocomplete="name" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="nickname" class="form-label">Display name <span class="text-muted fw-normal">(optional)</span></label>
                                <input type="text" class="form-control @error('nickname') is-invalid @enderror" id="nickname" name="nickname"
                                    value="{{ old('nickname', $user->nickname) }}" maxlength="100" placeholder="What should we call you?">
                                @error('nickname')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <div class="sp-greet"><i class="bi bi-chat-heart"></i><span id="greeting"></span></div>
                            </div>
                            <div class="col-12">
                                <label for="email" class="form-label">Email address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email"
                                    value="{{ old('email', $user->email) }}" maxlength="255" autocomplete="email" required>
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <div class="sp-note"><i class="bi bi-lock"></i>
                                    Your role is <strong class="text-capitalize">{{ $user->role }}</strong>. Only an administrator can change it in User Management.
                                </div>
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
            </section>

            {{-- APPEARANCE --}}
            <section class="sp-pane" id="pane-appearance" role="tabpanel" hidden>
                <div class="sp-block">
                    <h2 class="sp-h">Choose how the system looks</h2>
                    <p class="text-muted small">Applies right away and is saved to your account, so it follows you on any device.</p>
                    <div class="sp-themes" role="radiogroup" aria-label="Theme">
                        <button type="button" class="sp-theme" data-theme-choice="light" role="radio">
                            <span class="sp-mini sp-mini-light"><i></i><b></b><b></b><b></b></span>
                            <span class="sp-theme-label"><i class="bi bi-sun-fill"></i>Light</span>
                            <i class="bi bi-check-circle-fill sp-check"></i>
                        </button>
                        <button type="button" class="sp-theme" data-theme-choice="dark" role="radio">
                            <span class="sp-mini sp-mini-dark"><i></i><b></b><b></b><b></b></span>
                            <span class="sp-theme-label"><i class="bi bi-moon-stars-fill"></i>Dark</span>
                            <i class="bi bi-check-circle-fill sp-check"></i>
                        </button>
                    </div>
                </div>
            </section>

            {{-- SECURITY --}}
            <section class="sp-pane" id="pane-security" role="tabpanel" hidden>
                <form method="POST" action="{{ route('settings.password.update') }}" id="passwordForm">
                    @csrf
                    @method('PUT')
                    <div class="sp-block">
                        <h2 class="sp-h">Change password</h2>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="current_password" class="form-label">Current password</label>
                                <div class="sp-eye">
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" id="current_password"
                                        name="current_password" autocomplete="current-password" required>
                                    <button type="button" data-eye="current_password" aria-label="Show or hide password"><i class="bi bi-eye"></i></button>
                                </div>
                                @error('current_password')<div class="small text-danger mt-1">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="password" class="form-label">New password</label>
                                <div class="sp-eye">
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password"
                                        autocomplete="new-password" minlength="8" required>
                                    <button type="button" data-eye="password" aria-label="Show or hide password"><i class="bi bi-eye"></i></button>
                                </div>
                                <div class="sp-meter mt-2"><div id="meterBar"></div></div>
                                <div class="small text-muted mt-1" id="meterText">Use at least 8 characters.</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm new password</label>
                                <div class="sp-eye">
                                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                                        autocomplete="new-password" minlength="8" required>
                                    <button type="button" data-eye="password_confirmation" aria-label="Show or hide password"><i class="bi bi-eye"></i></button>
                                </div>
                                <div class="small mt-2" id="matchText">&nbsp;</div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary mt-4" id="pwSubmit" disabled><i class="bi bi-lock-fill me-1"></i>Update password</button>
                    </div>
                </form>
            </section>

        </main>
    </div>
</div>

<div class="sp-toast" id="toast" role="status" aria-live="polite"></div>

<style>
.sp{--line:var(--border-color);--ink:var(--text-dark);--muted:var(--muted-text);--field:var(--input-bg);--accent:var(--primary);--soft:var(--accent-soft)}
.sp .min-w-0{min-width:0}
.sp-side{position:sticky;top:1rem;display:flex;flex-direction:column;gap:1rem}
@media(max-width:991.98px){.sp-side{position:static}}

/* ID card: the one memorable element */
.sp-id{color:#fff;border-radius:18px;padding:1.1rem 1.2rem;background:
  repeating-linear-gradient(115deg,rgba(255,255,255,.045) 0 2px,transparent 2px 9px),
  linear-gradient(140deg,#5b3df0,#2a1a8f 70%);box-shadow:0 10px 28px rgba(42,26,143,.3);position:relative;overflow:hidden}
.sp-id::after{content:"";position:absolute;inset:auto -30px -30px auto;width:120px;height:120px;border-radius:50%;border:18px solid rgba(255,255,255,.06)}
.sp-id-top{display:flex;justify-content:space-between;align-items:center;font-size:.8rem;opacity:.85;margin-bottom:1rem}
.sp-id-role{border:1px solid rgba(255,255,255,.35);border-radius:99px;padding:.05rem .6rem}
.sp-id-body{display:flex;gap:.9rem;align-items:center}
.sp-id-name{font-size:1.25rem;font-weight:700;line-height:1.2;overflow-wrap:anywhere}
.sp-id-sub{font-size:.82rem;opacity:.75;overflow-wrap:anywhere}
.sp-id-foot{margin-top:1rem;position:relative;z-index:1}
.sp-pill{display:inline-flex;gap:.35rem;align-items:center;font-size:.78rem;padding:.15rem .6rem;border-radius:99px;background:rgba(255,255,255,.14)}
.sp-pill-ok{background:rgba(64,200,140,.22);color:#a6f3cf}
.sp-avatar{width:64px;height:64px;flex:0 0 64px;border-radius:50%;overflow:hidden;display:grid;place-items:center;
  background:rgba(255,255,255,.16);border:2px solid rgba(255,255,255,.5);font-size:1.6rem;font-weight:700}
.sp-avatar img{width:100%;height:100%;object-fit:cover}
.sp-avatar-lg{width:96px;height:96px;flex-basis:96px;font-size:2.4rem;background:var(--soft);color:var(--accent);border-color:var(--line)}

/* Nav */
.sp-nav{display:flex;flex-direction:column;gap:.35rem}
.sp-nav button{display:flex;gap:.8rem;align-items:center;text-align:left;border:1px solid transparent;background:transparent;color:var(--ink);
  padding:.65rem .8rem;border-radius:12px;transition:background .15s}
.sp-nav button i{font-size:1.2rem;color:var(--muted)}
.sp-nav button span{display:flex;flex-direction:column;font-weight:600;line-height:1.2}
.sp-nav small{font-weight:400;color:var(--muted)}
.sp-nav button:hover{background:var(--soft)}
.sp-nav button[aria-selected="true"]{background:var(--surface);border-color:var(--line);box-shadow:inset 3px 0 0 var(--accent)}
.sp-nav button[aria-selected="true"] i{color:var(--accent)}
@media(max-width:991.98px){.sp-nav{flex-direction:row;overflow-x:auto}.sp-nav button{flex:0 0 auto}.sp-nav small{display:none}}

/* Panes */
.sp-block{background:var(--surface);border:1px solid var(--line);border-radius:14px;padding:1.4rem;margin-bottom:1rem}
.sp-h{font-size:1.05rem;font-weight:700;margin-bottom:1rem;color:var(--ink)}
.sp .form-label{font-weight:600;font-size:.9rem;color:var(--ink)}
.sp .form-control{background:var(--field);border-color:var(--line);color:var(--ink);padding:.6rem .8rem;border-radius:10px}
.sp .form-control:focus{border-color:var(--accent);box-shadow:0 0 0 3px color-mix(in srgb,var(--accent) 25%,transparent)}
.sp-greet,.sp-note{display:flex;gap:.6rem;align-items:center;background:var(--soft);color:var(--ink);border-radius:10px;padding:.6rem .8rem;font-size:.9rem}
.sp-greet i,.sp-note i{color:var(--accent)}
.sp-savebar{position:sticky;bottom:1rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;
  background:var(--ink);color:var(--surface);border-radius:14px;padding:.6rem .9rem;box-shadow:0 8px 24px rgba(0,0,0,.25);z-index:5}
.sp-savebar[hidden]{display:none}
.sp-savebar .btn-link{color:inherit;opacity:.8}

/* Theme cards */
.sp-themes{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem}
.sp-theme{position:relative;border:2px solid var(--line);background:var(--field);border-radius:14px;padding:.8rem;text-align:left;color:var(--ink);transition:border-color .15s,transform .15s}
.sp-theme:hover{transform:translateY(-2px)}
.sp-theme.is-active{border-color:var(--accent)}
.sp-theme-label{display:flex;gap:.5rem;align-items:center;font-weight:600;margin-top:.7rem}
.sp-check{position:absolute;top:.6rem;right:.6rem;font-size:1.2rem;color:var(--accent);opacity:0;transform:scale(.6);transition:.2s}
.sp-theme.is-active .sp-check{opacity:1;transform:none}
.sp-mini{display:grid;grid-template-columns:28% 1fr;grid-template-rows:14px 1fr 1fr 1fr;gap:5px;height:104px;border-radius:8px;padding:6px}
.sp-mini i{grid-row:1/5;border-radius:5px}
.sp-mini b{border-radius:4px}
.sp-mini-light{background:#f1f4f8}.sp-mini-light i{background:#d5deea}.sp-mini-light b{background:#fff}
.sp-mini-dark{background:#0f1218}.sp-mini-dark i{background:#232a36}.sp-mini-dark b{background:#1c222c}

/* Password */
.sp-eye{position:relative}
.sp-eye .form-control{padding-right:2.6rem}
.sp-eye button{position:absolute;right:.4rem;top:50%;transform:translateY(-50%);border:0;background:transparent;color:var(--muted);padding:.3rem .5rem}
.sp-meter{height:6px;background:var(--line);border-radius:9px;overflow:hidden}
.sp-meter div{height:100%;width:0;border-radius:9px;transition:width .25s,background .25s}

.sp-toast{position:fixed;left:50%;bottom:1.5rem;transform:translate(-50%,20px);background:#14213d;color:#fff;padding:.55rem 1rem;border-radius:99px;
  font-size:.9rem;opacity:0;pointer-events:none;transition:.25s;z-index:1080}
.sp-toast.show{opacity:1;transform:translate(-50%,0)}
@media(prefers-reduced-motion:reduce){.sp *,.sp-toast{transition:none!important}}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const $ = (id) => document.getElementById(id);
    const initial = @json($initial);

    /* Toast */
    let toastTimer;
    const toast = (msg) => {
        const t = $('toast'); t.textContent = msg; t.classList.add('show');
        clearTimeout(toastTimer); toastTimer = setTimeout(() => t.classList.remove('show'), 2200);
    };

    /* Tabs */
    const tabs = document.querySelectorAll('.sp-nav [data-tab]');
    const showTab = (name) => {
        tabs.forEach(b => b.setAttribute('aria-selected', b.dataset.tab === name));
        document.querySelectorAll('.sp-pane').forEach(p => p.hidden = p.id !== 'pane-' + name);
        history.replaceState(null, '', '#' + name);
    };
    tabs.forEach(b => b.addEventListener('click', () => showTab(b.dataset.tab)));
    const fromHash = location.hash.slice(1);
    showTab(['profile', 'appearance', 'security'].includes(fromHash) && !{{ $errors->any() ? 'true' : 'false' }} ? fromHash : @json($startTab));

    /* Live ID card + greeting + unsaved bar */
    const name = $('name'), nick = $('nickname'), email = $('email'), avatar = $('avatar');
    const form = $('profileForm'), bar = $('saveBar');
    const start = { name: name.value, nick: nick.value, email: email.value };
    const greetWord = () => { const h = new Date().getHours(); return h < 12 ? 'Good morning' : h < 18 ? 'Good afternoon' : 'Good evening'; };

    const refresh = () => {
        const display = nick.value.trim() || name.value.trim() || 'Your name';
        $('idName').textContent = display;
        $('idFull').textContent = name.value.trim();
        $('idMail').textContent = email.value.trim() || 'No email on file';
        $('greeting').textContent = greetWord() + ', ' + display + '! This is how the system will greet you.';
        const dirty = name.value !== start.name || nick.value !== start.nick || email.value !== start.email || (avatar.files && avatar.files.length);
        bar.hidden = !dirty;
    };
    [name, nick, email].forEach(el => el.addEventListener('input', refresh));
    refresh();

    $('discardBtn').addEventListener('click', () => {
        form.reset(); name.value = start.name; nick.value = start.nick; email.value = start.email;
        document.querySelectorAll('[data-avatar]').forEach(a => { if (a.dataset.original !== undefined) a.innerHTML = a.dataset.original; });
        $('avatarHint').textContent = 'JPG, PNG or WEBP, up to 2 MB.';
        refresh();
    });

    /* Avatar preview (updates the ID card too) */
    document.querySelectorAll('[data-avatar]').forEach(a => a.dataset.original = a.innerHTML);
    avatar.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file || !file.type.startsWith('image/')) return;
        if (file.size > 2 * 1024 * 1024) { avatar.value = ''; toast('That photo is over 2 MB. Choose a smaller one.'); return; }
        const reader = new FileReader();
        reader.onload = (ev) => {
            document.querySelectorAll('[data-avatar]').forEach(a => {
                a.innerHTML = '<img alt="Profile photo" src="' + ev.target.result + '">';
            });
            $('avatarHint').textContent = file.name + ' is ready. Save changes to keep it.';
            refresh();
        };
        reader.readAsDataURL(file);
    });

    /* Safety net: if the layout hasn't defined EPTheme yet, this page handles the theme itself */
    if (!window.EPTheme) {
        const root = document.documentElement;
        const apply = (theme) => {
            const dark = theme === 'dark';
            root.setAttribute('data-theme', theme);
            try { localStorage.setItem('barangay-theme', theme); } catch (e) {}
            $('topbarThemeToggle')?.setAttribute('aria-pressed', dark);
            if ($('topbarThemeLabel')) $('topbarThemeLabel').textContent = dark ? 'Light Mode' : 'Dark Mode';
            if ($('topbarThemeIcon')) $('topbarThemeIcon').className = dark ? 'bi bi-sun-fill' : 'bi bi-moon-stars-fill';
            document.dispatchEvent(new CustomEvent('themechange', { detail: { theme } }));
        };
        window.EPTheme = {
            current: () => root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light',
            async set(theme) {
                const previous = this.current();
                apply(theme);
                try {
                    const res = await fetch("{{ route('settings.theme.update') }}", {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json', 'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        },
                        body: JSON.stringify({ theme })
                    });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    return true;
                } catch (err) {
                    console.error('Theme could not be saved:', err);
                    apply(previous);
                    return false;
                }
            }
        };
    }

    /* Theme cards: the layout's EPTheme applies the theme to the whole system and saves it to the account */
    const themeInput = $('theme');
    const cards = document.querySelectorAll('[data-theme-choice]');
    const paint = (theme) => {
        themeInput.value = theme;
        cards.forEach(c => { const on = c.dataset.themeChoice === theme; c.classList.toggle('is-active', on); c.setAttribute('aria-checked', on); });
    };
    paint(document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light');
    document.addEventListener('themechange', (e) => paint(e.detail.theme));

    cards.forEach(card => card.addEventListener('click', async () => {
        const next = card.dataset.themeChoice;
        if (window.EPTheme.current() === next) return;
        const ok = await window.EPTheme.set(next);
        toast(ok ? (next === 'dark' ? 'Dark' : 'Light') + ' mode saved to your account' : "Couldn't save your theme. Try again.");
    }));

    /* Password helpers */
    document.querySelectorAll('[data-eye]').forEach(btn => btn.addEventListener('click', () => {
        const input = $(btn.dataset.eye), show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.firstElementChild.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    }));

    const pw = $('password'), pc = $('password_confirmation'), current = $('current_password');
    const levels = [['Too weak', '#dc3545', 15], ['Weak', '#fd7e14', 35], ['Okay', '#ffc107', 60], ['Strong', '#28a745', 85], ['Very strong', '#198754', 100]];
    const checkPw = () => {
        const v = pw.value;
        let s = 0;
        if (v.length >= 8) s++;
        if (v.length >= 12) s++;
        if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
        if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
        const [label, color, width] = v ? levels[s] : ['Use at least 8 characters.', '', 0];
        $('meterBar').style.width = width + '%'; $('meterBar').style.background = color;
        $('meterText').textContent = v ? label : label;
        const match = pc.value && pw.value === pc.value;
        $('matchText').innerHTML = !pc.value ? '&nbsp;' : match
            ? '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Passwords match</span>'
            : '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>Passwords don\'t match yet</span>';
        $('pwSubmit').disabled = !(current.value && v.length >= 8 && match);
    };
    [pw, pc, current].forEach(el => el.addEventListener('input', checkPw));
    checkPw();
});
</script>
@endsection