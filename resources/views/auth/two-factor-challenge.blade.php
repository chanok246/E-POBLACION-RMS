@php
    $account = auth()->user();
    $theme = $account?->theme ?? 'system';

    // Keep the email-recovery step open when the user is already in that flow.
    $emailStepOpen = session('status') || $errors->has('email_code');
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="{{ $theme }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>Two-Factor Verification | e-Poblacion</title>

    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&family=Public+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <style>
        /* Same watermelon x tech-green theme as the login page. */
        :root {
            color-scheme: light;
            --page: #f4f8f6;
            --card: #ffffff;
            --text: #12201a;
            --muted: #566a60;
            --faint: #8a9d92;
            --border: #dce5df;
            --input: #ffffff;
            --brand: #e5505c;
            --brand-dark: #c93a47;
            --brand-soft: #fde8ea;
            --leaf: #2f9e5b;
            --leaf-dark: #1f7a43;
            --leaf-soft: #e3f4ea;
            --pink: #ff7b88;
            --sun: #ffc933;
            --error: #b42318;
            --error-soft: #fce7e6;
            --success: #1f7a43;
            --success-soft: #e3f4ea;
            --font-head: 'Space Grotesk', 'Public Sans', system-ui, sans-serif;
            --font-body: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-mono: 'JetBrains Mono', ui-monospace, Menlo, Consolas, monospace;
        }

        html[data-theme="dark"] {
            color-scheme: dark;
            --page: #08140f;
            --card: #10201a;
            --text: #eef6f1;
            --muted: #a9bdb3;
            --faint: #7f958a;
            --border: #274034;
            --input: #0a1812;
            --brand-soft: #3a1c21;
            --brand-dark: #ff8e98;
            --leaf-soft: #12301f;
            --leaf-dark: #7fe0a6;
            --error: #ffaaa3;
            --error-soft: #3a1b1a;
            --success: #8de0b2;
            --success-soft: #12301f;
        }

        @media (prefers-color-scheme: dark) {
            html[data-theme="system"] {
                color-scheme: dark;
                --page: #08140f;
                --card: #10201a;
                --text: #eef6f1;
                --muted: #a9bdb3;
                --faint: #7f958a;
                --border: #274034;
                --input: #0a1812;
                --brand-soft: #3a1c21;
                --brand-dark: #ff8e98;
                --leaf-soft: #12301f;
                --leaf-dark: #7fe0a6;
                --error: #ffaaa3;
                --error-soft: #3a1b1a;
                --success: #8de0b2;
                --success-soft: #12301f;
            }
        }

        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100svh;
            padding: 24px 16px;
            display: grid;
            place-items: center;
            background:
                radial-gradient(60% 40% at 100% 0%, rgba(229, 80, 92, .10), transparent 70%),
                radial-gradient(50% 40% at 0% 100%, rgba(47, 158, 91, .10), transparent 70%),
                var(--page);
            color: var(--text);
            font-family: var(--font-body);
            font-size: 15px;
            line-height: 1.55;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3 { font-family: var(--font-head); letter-spacing: -0.02em; margin: 0; line-height: 1.2; }
        p { margin: 0; }

        .container { width: 100%; max-width: 480px; }

        /* Brand */
        .brand { margin-bottom: 20px; text-align: center; }
        .brand-logo {
            width: 56px; height: 56px; border-radius: 16px; object-fit: cover; background: var(--brand-soft);
            box-shadow: 0 0 0 4px var(--brand-soft); display: inline-block;
        }
        .brand h1 { margin-top: 14px; font-size: 26px; font-weight: 700; }
        .brand p { color: var(--muted); font-size: 14px; margin-top: 2px; }

        /* Card */
        .card {
            position: relative; overflow: hidden;
            padding: 30px 26px 26px;
            border: 1px solid var(--border);
            border-radius: 22px;
            background: var(--card);
            box-shadow: 0 2px 4px rgba(6, 35, 26, .04), 0 24px 50px rgba(6, 35, 26, .10);
        }
        .card::before {
            content: ''; position: absolute; left: 0; right: 0; top: 0; height: 5px;
            background: linear-gradient(90deg, var(--brand), var(--pink) 45%, var(--sun) 70%, var(--leaf));
        }
        @media (min-width: 480px) { .card { padding: 36px 34px 30px; } }

        /* Progress steps */
        .steps {
            list-style: none; margin: 0 0 22px; padding: 0; display: flex; align-items: center; gap: 10px;
            font-family: var(--font-mono); font-size: 11.5px; font-weight: 700; letter-spacing: .03em; text-transform: uppercase;
        }
        .steps li { display: inline-flex; align-items: center; gap: 7px; color: var(--faint); }
        .steps .dot {
            width: 22px; height: 22px; border-radius: 50%; display: grid; place-items: center;
            font-size: 12px; background: var(--border); color: var(--muted);
        }
        .steps .is-done { color: var(--leaf-dark); }
        .steps .is-done .dot { background: var(--leaf); color: #fff; }
        .steps .is-current { color: var(--brand-dark); }
        .steps .is-current .dot { background: var(--brand); color: #fff; }
        .steps .bar { flex: 1; height: 2px; border-radius: 2px; background: linear-gradient(90deg, var(--leaf), var(--brand)); opacity: .5; }

        h2.title { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
        .lead { color: var(--muted); font-size: 14.5px; }

        /* Alerts */
        .message {
            display: flex; gap: 10px; align-items: flex-start;
            margin: 18px 0 0; padding: 12px 14px; border-radius: 10px; font-size: 14px; overflow-wrap: anywhere;
        }
        .message i { margin-top: 2px; }
        .message.error { background: var(--error-soft); border-left: 5px solid var(--error); color: var(--error); }
        .message.success { background: var(--success-soft); border-left: 5px solid var(--leaf); color: var(--success); }
        .message ul { margin: 0; padding-left: 18px; }

        /* Method blocks */
        .method {
            margin-top: 22px; padding: 20px; border: 1px solid var(--border); border-radius: 16px;
            background: color-mix(in srgb, var(--page) 55%, var(--card));
        }
        .method-head { display: flex; gap: 12px; align-items: flex-start; margin-bottom: 14px; }
        .method-icon {
            flex: none; width: 40px; height: 40px; border-radius: 12px; display: grid; place-items: center; font-size: 18px;
            background: var(--brand-soft); color: var(--brand-dark);
        }
        .method-icon.leaf { background: var(--leaf-soft); color: var(--leaf-dark); }
        .method-head h3 { font-size: 16.5px; font-weight: 700; }
        .method-head p { color: var(--muted); font-size: 13.5px; margin-top: 2px; }

        /* Collapsible email-recovery block */
        details.method { padding: 0; }
        details.method > summary {
            list-style: none; cursor: pointer; padding: 18px 20px; display: flex; align-items: center; gap: 12px;
            border-radius: 16px;
        }
        details.method > summary::-webkit-details-marker { display: none; }
        details.method > summary .method-head { margin: 0; flex: 1; }
        details.method > summary .chev { color: var(--muted); transition: transform .2s; }
        details.method[open] > summary .chev { transform: rotate(180deg); }
        details.method .method-body { padding: 0 20px 20px; }
        details.method .method-body > * + * { margin-top: 14px; }

        /* Form controls */
        label { display: block; margin: 0 0 7px; font-size: 13.5px; font-weight: 600; }
        .input-wrap { position: relative; }
        .input-wrap i {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: var(--faint); font-size: 17px; pointer-events: none;
        }
        input[type="text"] {
            display: block; width: 100%; min-height: 48px; padding: 11px 14px 11px 42px;
            border: 1.5px solid var(--border); border-radius: 12px; background: var(--input); color: var(--text);
            font: inherit; transition: border-color .15s, box-shadow .15s;
        }
        input[type="text"]::placeholder { color: var(--faint); }
        input[type="text"]:hover { border-color: var(--faint); }
        input[type="text"]:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 4px rgba(229, 80, 92, .15); }
        .input-wrap:focus-within i { color: var(--brand); }
        .code-input { letter-spacing: .08em; font-family: var(--font-mono); font-weight: 500; }

        /* ---------- 6-box code entry ---------- */
        .otp { display: grid; grid-template-columns: repeat(6, minmax(0, 1fr)); gap: 10px; }
        .otp .otp-box {
            width: 100%; min-width: 0; height: 58px; padding: 0; text-align: center;
            font-family: var(--font-mono); font-size: 24px; font-weight: 700; letter-spacing: 0;
            border: 1.5px solid var(--border); border-radius: 12px; background: var(--input); color: var(--text);
            caret-color: var(--brand); transition: border-color .15s, box-shadow .15s, transform .15s, background .15s;
        }
        .otp .otp-box:hover { border-color: var(--faint); }
        .otp .otp-box:focus {
            outline: none; border-color: var(--brand); box-shadow: 0 0 0 4px rgba(229, 80, 92, .15); transform: translateY(-1px);
        }
        .otp .otp-box.filled { border-color: var(--leaf); background: color-mix(in srgb, var(--leaf-soft) 55%, var(--input)); }
        .otp.leaf .otp-box:focus { border-color: var(--leaf); box-shadow: 0 0 0 4px rgba(47, 158, 91, .18); }
        .otp.is-invalid .otp-box { border-color: var(--error); animation: otp-shake .35s; }
        @keyframes otp-shake { 25% { transform: translateX(-4px); } 75% { transform: translateX(4px); } }
        .otp-hint { margin-top: 10px; text-align: center; color: var(--faint); font-size: 12.5px; }
        .link-btn {
            width: auto; min-height: 0; margin: 14px auto 0; padding: 4px 6px; background: none; color: var(--brand-dark);
            font-size: 13.5px; font-weight: 600; text-decoration: underline; text-underline-offset: 3px; box-shadow: none;
        }
        .link-btn:hover { filter: brightness(1.1); }
        .center { display: flex; }
        [hidden] { display: none !important; }

        :focus-visible { outline: 3px solid var(--brand); outline-offset: 3px; }

        button {
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; min-height: 48px; padding: 10px 16px; border: 0; border-radius: 12px;
            font: inherit; font-weight: 700; cursor: pointer;
            transition: filter .15s, transform .15s, box-shadow .15s, background .15s;
        }
        button:active { transform: scale(.99); }
        .btn-primary {
            background: linear-gradient(135deg, #f27984, var(--brand)); color: #fff;
            box-shadow: 0 10px 22px rgba(229, 80, 92, .30);
        }
        .btn-primary:hover { filter: brightness(1.06); box-shadow: 0 12px 28px rgba(229, 80, 92, .40); }
        .btn-secondary { background: transparent; color: var(--text); border: 1.5px solid var(--border); }
        .btn-secondary:hover { background: var(--page); border-color: var(--faint); }
        .btn-leaf { background: var(--leaf); color: #fff; }
        .btn-leaf:hover { filter: brightness(1.08); }

        .divider {
            display: flex; align-items: center; gap: 12px; margin: 22px 0 0;
            color: var(--faint); font-family: var(--font-mono); font-size: 11.5px; text-transform: uppercase; letter-spacing: .06em;
        }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }

        .security-note {
            margin: 18px 0 0; display: flex; gap: 8px; justify-content: center; align-items: flex-start;
            color: var(--muted); font-size: 12.5px; text-align: center;
        }
        .security-note i { color: var(--leaf); margin-top: 2px; }

        .footer { margin-top: 18px; text-align: center; }
        .footer button { max-width: 200px; margin: 0 auto; min-height: 44px; }

        @media (max-width: 420px) {
            .otp { gap: 7px; }
            .otp .otp-box { height: 50px; font-size: 20px; border-radius: 10px; }
            .card { padding: 26px 18px 22px; }
            .method { padding: 16px; }
            details.method > summary { padding: 16px; }
            details.method .method-body { padding: 0 16px 16px; }
            .steps .label-text { display: none; }
        }

        @media (prefers-reduced-motion: reduce) {
            * { transition: none !important; }
        }
    </style>
</head>
<body>
    <main class="container">

        <header class="brand">
            <img class="brand-logo" src="{{ asset('icon.png') }}" alt="e-Poblacion logo">
            <h1>e-Poblacion</h1>
            <p>Barangay Poblacion Record Management System</p>
        </header>

        <section class="card" aria-labelledby="page-title">

            {{-- Progress: password already accepted, now verifying identity --}}
            <ol class="steps" aria-label="Sign-in progress">
                <li class="is-done">
                    <span class="dot"><i class="bi bi-check-lg" aria-hidden="true"></i></span>
                    <span class="label-text">Password</span>
                </li>
                <li class="bar" aria-hidden="true"></li>
                <li class="is-current" aria-current="step">
                    <span class="dot">2</span>
                    <span class="label-text">Verify identity</span>
                </li>
            </ol>

            <h2 class="title" id="page-title">Two-Factor Verification</h2>
            <p class="lead">
                Your password has been accepted. Verify your identity to
                continue to the records system.
            </p>

            @if ($errors->any())
                <div class="message error" role="alert">
                    <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('status'))
                <div class="message success" role="status">
                    <i class="bi bi-check-circle" aria-hidden="true"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            @if (session('success'))
                <div class="message success" role="status">
                    <i class="bi bi-check-circle" aria-hidden="true"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            {{-- Option 1: authenticator code or saved recovery code --}}
            <div class="method">
                <div class="method-head">
                    <div class="method-icon"><i class="bi bi-phone" aria-hidden="true"></i></div>
                    <div>
                        <h3>Authenticator or recovery code</h3>
                        <p>
                            Enter the current six-digit code from your authenticator app,
                            or one of your saved recovery codes. Each recovery code works only once.
                        </p>
                    </div>
                </div>

                <form method="POST" action="{{ route('two-factor.verify') }}" id="mainForm" novalidate>
                    @csrf

                    {{-- Mode A: six boxes (authenticator app code) --}}
                    <div id="otpMode">
                        <label id="otpLabel">Authenticator code</label>
                        <div class="otp" data-otp role="group" aria-labelledby="otpLabel">
                            @for ($i = 1; $i <= 6; $i++)
                                <input
                                    class="otp-box"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="1"
                                    autocomplete="{{ $i === 1 ? 'one-time-code' : 'off' }}"
                                    aria-label="Digit {{ $i }} of 6"
                                    @if ($i === 1 && ! $emailStepOpen) autofocus @endif
                                >
                            @endfor
                            <input type="hidden" name="code" data-otp-value>
                        </div>
                        <p class="otp-hint">Type or paste the 6-digit code.</p>
                    </div>

                    {{-- Mode B: saved recovery code (longer, free text) --}}
                    <div id="recoveryMode" hidden>
                        <label for="recovery_code">Recovery code</label>
                        <div class="input-wrap">
                            <i class="bi bi-key" aria-hidden="true"></i>
                            <input
                                id="recovery_code"
                                class="code-input"
                                name="code"
                                type="text"
                                maxlength="64"
                                autocomplete="off"
                                autocapitalize="off"
                                spellcheck="false"
                                placeholder="Enter a saved recovery code"
                                disabled
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top:16px;">
                        <span>Verify and Continue</span>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>

                    <div class="center" style="justify-content:center;">
                        <button type="button" class="link-btn" id="modeToggle">Use a recovery code instead</button>
                    </div>
                </form>
            </div>

            <div class="divider" aria-hidden="true">or</div>

            {{-- Option 2: email recovery (only after the password was verified) --}}
            <details class="method" id="emailRecovery" @if ($emailStepOpen) open @endif>
                <summary>
                    <div class="method-head">
                        <div class="method-icon leaf"><i class="bi bi-envelope-check" aria-hidden="true"></i></div>
                        <div>
                            <h3>Can't use your authenticator?</h3>
                            <p>Verify with a one-time code sent to your registered email.</p>
                        </div>
                    </div>
                    <i class="bi bi-chevron-down chev" aria-hidden="true"></i>
                </summary>

                <div class="method-body">
                    <p class="lead" style="font-size:13.5px;">
                        Request a one-time code sent to the email address already
                        registered to your account. The code expires in 10 minutes.
                    </p>

                    <form
                        method="POST"
                        action="{{ route('two-factor.email-recovery.send') }}"
                    >
                        @csrf

                        <button type="submit" class="btn-secondary">
                            <i class="bi bi-envelope-arrow-up" aria-hidden="true"></i>
                            <span>Send Code to Registered Email</span>
                        </button>
                    </form>

                    <form
                        method="POST"
                        action="{{ route('two-factor.email-recovery.verify') }}"
                        novalidate
                    >
                        @csrf

                        <label id="emailOtpLabel">Email verification code</label>
                        <div class="otp leaf" data-otp role="group" aria-labelledby="emailOtpLabel">
                            @for ($i = 1; $i <= 6; $i++)
                                <input
                                    class="otp-box"
                                    type="text"
                                    inputmode="numeric"
                                    pattern="[0-9]*"
                                    maxlength="1"
                                    autocomplete="off"
                                    aria-label="Email code digit {{ $i }} of 6"
                                    @if ($i === 1 && $emailStepOpen) autofocus @endif
                                >
                            @endfor
                            <input type="hidden" name="email_code" data-otp-value>
                        </div>

                        <button type="submit" class="btn-leaf" style="margin-top:16px;">
                            <span>Verify Email Code</span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            </details>

            <p class="security-note">
                <i class="bi bi-shield-check" aria-hidden="true"></i>
                <span>
                    Never share your verification codes with anyone.
                    Email recovery is available only after your account
                    password has been verified.
                </span>
            </p>
        </section>

        <footer class="footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-secondary">
                    <i class="bi bi-box-arrow-left" aria-hidden="true"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </footer>
    </main>

    <script>
    (function () {
        /* ---- six-box code inputs ---- */
        function setupOtp(group) {
            var boxes = Array.prototype.slice.call(group.querySelectorAll('.otp-box'));
            var hidden = group.querySelector('[data-otp-value]');

            function sync() {
                var v = boxes.map(function (b) { return b.value; }).join('');
                hidden.value = v;
                boxes.forEach(function (b) { b.classList.toggle('filled', b.value !== ''); });
                group.classList.remove('is-invalid');
            }

            function fill(start, text) {
                var digits = text.replace(/\D/g, '').split('');
                for (var i = 0; i < digits.length && start + i < boxes.length; i++) {
                    boxes[start + i].value = digits[i];
                }
                sync();
                boxes[Math.min(start + digits.length, boxes.length - 1)].focus();
            }

            boxes.forEach(function (box, i) {
                box.addEventListener('input', function () {
                    var d = box.value.replace(/\D/g, '');
                    box.value = '';
                    if (d.length > 1) { fill(i, d); return; }   /* autofill / SMS suggestion */
                    box.value = d;
                    sync();
                    if (d && i < boxes.length - 1) boxes[i + 1].focus();
                });

                box.addEventListener('keydown', function (e) {
                    if (e.key === 'Backspace' && !box.value && i > 0) {
                        boxes[i - 1].value = ''; sync(); boxes[i - 1].focus(); e.preventDefault();
                    } else if (e.key === 'ArrowLeft' && i > 0) {
                        boxes[i - 1].focus(); e.preventDefault();
                    } else if (e.key === 'ArrowRight' && i < boxes.length - 1) {
                        boxes[i + 1].focus(); e.preventDefault();
                    }
                });

                box.addEventListener('paste', function (e) {
                    e.preventDefault();
                    var text = (e.clipboardData || window.clipboardData).getData('text');
                    fill(0, text);
                });

                box.addEventListener('focus', function () { box.select(); });
            });

            /* block submit until all six boxes are filled */
            var form = group.closest('form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    if (group.offsetParent === null || hidden.disabled) return;   /* hidden mode */
                    if (hidden.value.length !== 6) {
                        e.preventDefault();
                        group.classList.remove('is-invalid'); void group.offsetWidth;
                        group.classList.add('is-invalid');
                        var empty = boxes.filter(function (b) { return !b.value; })[0] || boxes[0];
                        empty.focus();
                    }
                });
            }
        }

        document.querySelectorAll('[data-otp]').forEach(setupOtp);

        /* ---- authenticator <-> recovery code toggle ---- */
        var toggle = document.getElementById('modeToggle');
        var otpMode = document.getElementById('otpMode');
        var recMode = document.getElementById('recoveryMode');
        var recInput = document.getElementById('recovery_code');
        var otpHidden = otpMode.querySelector('[data-otp-value]');
        var useRecovery = false;

        toggle.addEventListener('click', function () {
            useRecovery = !useRecovery;
            otpMode.hidden = useRecovery;
            recMode.hidden = !useRecovery;
            recInput.disabled = !useRecovery;
            otpHidden.disabled = useRecovery;
            toggle.textContent = useRecovery ? 'Use authenticator code instead' : 'Use a recovery code instead';
            (useRecovery ? recInput : otpMode.querySelector('.otp-box')).focus();
        });

        document.getElementById('mainForm').addEventListener('submit', function (e) {
            if (useRecovery && !recInput.value.trim()) { e.preventDefault(); recInput.focus(); }
        });

        /* Reopen the recovery mode if the server sent the user back with an error on a long code */
    })();
    </script>
</body>
</html>