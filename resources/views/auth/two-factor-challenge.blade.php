@php
    // Open the Email tab when the user is already in the email-code flow.
    $emailStepOpen = session('status') || $errors->has('email_code');
@endphp
<!DOCTYPE html>
<html lang="en" data-theme="light">
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
        /*
         * Color coding (UX rationale)
         *  - Green (brand)  = primary action / authenticator app
         *  - Leaf green     = email verification + success / verified states
         *  - Crimson + icon = errors only (never used for buttons)
         */
        :root {
            color-scheme: light;

            --page: #f4f8f6;
            --card: #ffffff;
            --aside: #fbfdfc;
            --text: #12201a;
            --muted: #566a60;
            --faint: #8a9d92;
            --border: #dce5df;
            --input: #ffffff;

            --brand: #0a911a;
            --brand-dark: #087514;
            --brand-soft: #e1f5e3;

            --leaf: #2f9e5b;
            --leaf-dark: #1f7a43;
            --leaf-soft: #e3f4ea;


            --sun: #ffc933;

            --error: #b42318;
            --error-soft: #fce7e6;
            --success: #1f7a43;
            --success-soft: #e3f4ea;


            --accent: var(--brand);
            --accent-dark: var(--brand-dark);
            --accent-soft: var(--brand-soft);
            --accent-ring: rgba(10, 145, 26, .20);

            --font-head: 'Space Grotesk', 'Public Sans', system-ui, sans-serif;
            --font-body: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-mono: 'JetBrains Mono', ui-monospace, Menlo, Consolas, monospace;
        }

        html[data-theme="dark"] {
            color-scheme: dark;
            --page: #08140f;
            --card: #10201a;
            --aside: #0c1914;
            --text: #eef6f1;
            --muted: #a9bdb3;
            --faint: #7f958a;
            --border: #274034;
            --input: #0a1812;

            --brand: #1fae30;
            --brand-dark: #7fe68a;
            --brand-soft: #12301a;
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
                --aside: #0c1914;
                --text: #eef6f1;
                --muted: #a9bdb3;
                --faint: #7f958a;
                --border: #274034;
                --input: #0a1812;

                --brand: #1fae30;
                --brand-dark: #7fe68a;
                --brand-soft: #12301a;
                --leaf-soft: #12301f;
                --leaf-dark: #7fe0a6;

                --error: #ffaaa3;
                --error-soft: #3a1b1a;
                --success: #8de0b2;
                --success-soft: #12301f;

            }
        }

        /* Each verification method has its own accent color. */
        [data-method="auth"] {
            --accent: var(--brand);
            --accent-dark: var(--brand-dark);
            --accent-soft: var(--brand-soft);
            --accent-ring: rgba(10, 145, 26, .20);
        }

        [data-method="email"] {
            --accent: var(--leaf);
            --accent-dark: var(--leaf-dark);
            --accent-soft: var(--leaf-soft);
            --accent-ring: rgba(47, 158, 91, .22);
        }

        *, *::before, *::after {
            box-sizing: border-box;
        }

        html, body {
            min-height: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            min-height: 100svh;
            padding: 20px;

            display: grid;
            place-items: center;

            background:
                radial-gradient(55% 45% at 100% 0%, rgba(10, 145, 26, .10), transparent 70%),
                radial-gradient(50% 45% at 0% 100%, rgba(255, 201, 51, .10), transparent 70%),
                var(--page);

            color: var(--text);
            font-family: var(--font-body);
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        h1, h2, h3 {
            font-family: var(--font-head);
            letter-spacing: -0.02em;
            line-height: 1.2;
        }

        [hidden] {
            display: none !important;
        }

        /* ==============================
           MAIN LANDSCAPE CONTAINER
        ============================== */

        .shell {
            position: relative;

            width: 100%;
            max-width: 1040px;

            display: grid;
            grid-template-columns: 5fr 7fr;

            border: 1px solid var(--border);
            border-radius: 24px;
            overflow: hidden;

            background: var(--card);

            box-shadow:
                0 2px 4px rgba(6, 35, 26, .04),
                0 28px 60px rgba(6, 35, 26, .12);
        }

        .shell::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            height: 4px;
            z-index: 2;

            background: linear-gradient(90deg, var(--brand), var(--leaf) 55%, var(--sun));
        }

        /* ==============================
           LEFT PANEL
           BRANDING
        ============================== */

        .side {
            display: flex;
            flex-direction: column;
            gap: 24px;

            padding: 32px 30px 26px;

            background:
                radial-gradient(90% 60% at 0% 0%, rgba(10, 145, 26, .08), transparent 70%),
                radial-gradient(80% 60% at 100% 100%, rgba(255, 201, 51, .07), transparent 70%),
                var(--aside);

            border-right: 1px solid var(--border);
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Large official seal that fills the left panel */

        .seal-stage {
            position: relative;
            flex: 1;
            min-height: 200px;
        }

        .seal-stage::before {
            content: '';
            position: absolute;
            inset: 6%;

            border-radius: 50%;
            background:
                radial-gradient(closest-side, rgba(10, 145, 26, .16), rgba(255, 201, 51, .10) 62%, transparent 100%);
        }

        .seal {
            position: absolute;
            inset: 0;

            width: 100%;
            height: 100%;
            padding: 6px;

            object-fit: contain;
            object-position: center;

            filter: drop-shadow(0 14px 22px rgba(6, 35, 26, .22));
            -webkit-user-drag: none;
            user-select: none;
        }

        .brand h1 {
            font-size: 19px;
            font-weight: 700;
        }

        .brand p {
            margin-top: 2px;
            color: var(--muted);
            font-size: 11px;
        }

        .side-divider {
            height: 1px;
            background: var(--border);
        }

        .side-foot {
            display: grid;
            gap: 13px;
            margin-top: auto;
        }

        .security-note {
            display: flex;
            align-items: flex-start;
            gap: 8px;

            color: var(--muted);
            font-size: 11px;
            line-height: 1.5;
        }

        .security-note i {
            margin-top: 2px;
            color: var(--leaf);
        }

        .btn-signout {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            width: 100%;
            min-height: 42px;
            padding: 8px 14px;

            border: 1.5px solid var(--border);
            border-radius: 12px;

            background: transparent;
            color: var(--text);

            font: inherit;
            font-size: 13px;
            font-weight: 600;

            cursor: pointer;
            transition: background .15s, border-color .15s;
        }

        .btn-signout:hover {
            background: var(--page);
            border-color: var(--faint);
        }

        /* ==============================
           RIGHT PANEL
           AUTHENTICATOR + EMAIL
        ============================== */

        .main {
            min-width: 0;

            display: flex;
            flex-direction: column;
            gap: 17px;

            padding: 34px 34px 30px;
        }

        .steps {
            list-style: none;
            margin: 0;
            padding: 0;

            display: flex;
            align-items: center;
            gap: 8px;

            font-family: var(--font-mono);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .steps li {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--faint);
        }

        .steps .dot {
            width: 20px;
            height: 20px;

            display: grid;
            place-items: center;

            border-radius: 50%;
            background: var(--border);
            color: var(--muted);
            font-size: 11px;
        }

        .steps .is-done {
            color: var(--leaf-dark);
        }

        .steps .is-done .dot {
            background: var(--leaf);
            color: #ffffff;
        }

        .steps .is-current {
            color: var(--brand-dark);
        }

        .steps .is-current .dot {
            background: var(--brand);
            color: #ffffff;
        }

        .steps .bar {
            flex: 1;
            min-width: 18px;
            height: 2px;
            border-radius: 2px;
            background: linear-gradient(90deg, var(--leaf), var(--brand));
            opacity: .5;
        }

        .intro h2 {
            font-size: 27px;
            font-weight: 700;
        }

        .intro p {
            margin-top: 7px;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.6;
        }

        /* Verification method tabs */

        .tabs {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 6px;

            padding: 5px;
            border: 1px solid var(--border);
            border-radius: 14px;

            background: var(--page);
        }

        .tab {
            min-height: 43px;
            padding: 8px 12px;

            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;

            border: 0;
            border-radius: 10px;

            background: transparent;
            color: var(--muted);

            font: inherit;
            font-size: 13px;
            font-weight: 600;

            cursor: pointer;
            transition: background .15s, color .15s, box-shadow .15s;
        }

        .tab i {
            font-size: 16px;
        }

        .tab:hover {
            color: var(--text);
        }

        .tab[aria-selected="true"] {
            background: var(--card);
            color: var(--accent-dark);

            box-shadow:
                0 1px 2px rgba(6, 35, 26, .08),
                inset 0 -2px 0 var(--accent);
        }

        /* Alerts */

        .message {
            display: flex;
            align-items: flex-start;
            gap: 10px;

            padding: 10px 13px;
            border-radius: 10px;

            font-size: 12.5px;
            overflow-wrap: anywhere;
        }

        .message i {
            margin-top: 2px;
        }

        .message.error {
            background: var(--error-soft);
            border-left: 4px solid var(--error);
            color: var(--error);
        }

        .message.success {
            background: var(--success-soft);
            border-left: 4px solid var(--leaf);
            color: var(--success);
        }

        .message ul {
            margin: 0;
            padding-left: 16px;
        }

        /* Verification panels */

        .panel {
            flex: 1;
            min-height: 250px;

            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 18px;
        }

        .panel-head {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .panel-icon {
            width: 44px;
            height: 44px;
            flex: none;

            display: grid;
            place-items: center;

            border-radius: 12px;
            background: var(--accent-soft);
            color: var(--accent-dark);
            font-size: 19px;
        }

        .panel-head h3 {
            font-size: 17px;
            font-weight: 700;
        }

        .panel-head p {
            margin-top: 4px;
            color: var(--muted);
            font-size: 12.5px;
            line-height: 1.5;
        }

        label {
            display: block;
            margin: 0 0 8px;
            font-size: 12.5px;
            font-weight: 600;
        }

        /* Six-digit authenticator input */

        .otp {
            display: grid;
            grid-template-columns: repeat(6, minmax(0, 1fr));
            gap: 10px;
        }

        .otp .otp-box {
            width: 100%;
            min-width: 0;
            height: 56px;
            padding: 0;

            text-align: center;
            font-family: var(--font-mono);
            font-size: 23px;
            font-weight: 700;

            border: 1.5px solid var(--border);
            border-radius: 12px;

            background: var(--input);
            color: var(--text);

            caret-color: var(--accent);

            transition:
                border-color .15s,
                box-shadow .15s,
                transform .15s,
                background .15s;
        }

        .otp .otp-box:hover {
            border-color: var(--faint);
        }

        .otp .otp-box:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 4px var(--accent-ring);
            transform: translateY(-1px);
        }

        .otp .otp-box.filled {
            border-color: var(--accent);
            background: var(--accent-soft);
        }

        .otp.is-invalid .otp-box {
            border-color: var(--error);
            animation: otp-shake .35s;
        }

        @keyframes otp-shake {
            25% { transform: translateX(-4px); }
            75% { transform: translateX(4px); }
        }

        .hint {
            margin-top: 9px;
            color: var(--faint);
            font-size: 11.5px;
        }

        /* Email verification panel */

        .email-flow {
            display: grid;
            gap: 14px;
        }

        .email-step {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;

            padding: 12px 14px;

            border: 1px solid var(--border);
            border-radius: 14px;
            background: var(--page);
            transition: background .2s, border-color .2s;
        }

        .email-step.is-done {
            border-color: var(--leaf);
            background: var(--leaf-soft);
        }

        .email-step-num {
            width: 26px;
            height: 26px;
            flex: none;

            display: grid;
            place-items: center;

            border-radius: 50%;
            background: var(--accent);
            color: #ffffff;

            font-family: var(--font-mono);
            font-size: 12px;
            font-weight: 700;
        }

        .email-step-text {
            flex: 1;
            min-width: 160px;
        }

        .email-step-text strong {
            display: block;
            font-size: 13px;
            font-weight: 700;
        }

        .email-step-text span {
            display: block;
            margin-top: 2px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.45;
        }

        .email-step form {
            flex: none;
        }

        .email-step .btn {
            width: auto;
            min-height: 40px;
            padding: 8px 14px;
        }

        .email-verify-head {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }

        .email-verify-head p {
            font-size: 13px;
            font-weight: 700;
        }

        /* Buttons */

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;

            width: 100%;
            min-height: 46px;
            padding: 10px 14px;

            border: 0;
            border-radius: 11px;

            font: inherit;
            font-size: 12.5px;
            font-weight: 700;

            cursor: pointer;

            transition:
                filter .15s,
                transform .15s,
                box-shadow .15s,
                background .15s;
        }

        .btn:active {
            transform: scale(.99);
        }

        .btn-primary {
            background: var(--accent);
            color: #ffffff;
            box-shadow: 0 8px 18px var(--accent-ring);
        }

        .btn-primary:hover {
            filter: brightness(1.07);
        }

        .btn-outline {
            background: var(--card);
            color: var(--leaf-dark);
            border: 1.5px solid var(--leaf);
        }

        .btn-outline:hover {
            background: var(--leaf-soft);
        }

        /* "Lost your code?" text link under the main button */

        .link-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;

            width: auto;
            margin: 14px auto 0;
            padding: 4px 6px;

            border: 0;
            background: none;
            color: var(--leaf-dark);

            font: inherit;
            font-size: 12.5px;
            font-weight: 600;

            text-decoration: underline;
            text-underline-offset: 3px;
            cursor: pointer;
        }

        .link-btn:hover {
            filter: brightness(1.15);
        }

        .link-row {
            display: flex;
            justify-content: center;
        }

        :focus-visible {
            outline: 3px solid var(--accent);
            outline-offset: 3px;
        }

        /* ==============================
           RESPONSIVE LAYOUT
        ============================== */

        @media (max-height: 760px) and (min-width: 821px) {
            body {
                padding: 12px;
            }

            .side {
                padding: 25px 26px 20px;
                gap: 17px;
            }

            .main {
                padding: 25px 28px 22px;
                gap: 13px;
            }

            .intro h2 {
                font-size: 24px;
            }

            .panel {
                min-height: 210px;
                gap: 13px;
            }

            .otp .otp-box {
                height: 50px;
            }
        }

        @media (max-width: 820px) {
            body {
                padding: 14px;
                place-items: start center;
            }

            .shell {
                max-width: 560px;
                grid-template-columns: 1fr;
            }

            /* On phones the verification comes first, branding/recovery below. */
            .main {
                order: -1;
                padding: 24px 22px;
            }

            .side {
                padding: 25px 22px 20px;
                gap: 17px;

                border-right: 0;
                border-top: 1px solid var(--border);
            }

            .panel {
                min-height: 0;
            }

            .side-foot {
                margin-top: 0;
            }

            .seal-stage {
                flex: none;
                width: min(100%, 240px);
                height: 240px;
                min-height: 0;
                margin: 0 auto;
            }

            .intro h2 {
                font-size: 23px;
            }
        }

        @media (max-width: 480px) {
            body {
                padding: 10px;
            }

            .shell {
                border-radius: 18px;
            }

            .side {
                padding: 23px 17px 18px;
            }

            .main {
                padding: 21px 17px;
            }

            .brand h1 {
                font-size: 17px;
            }

            .brand p {
                font-size: 10px;
            }

            .otp {
                gap: 7px;
            }

            .otp .otp-box {
                height: 48px;
                font-size: 20px;
                border-radius: 10px;
            }

            .email-step form,
            .email-step .btn {
                width: 100%;
            }

            .steps .label-text {
                display: none;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * {
                transition: none !important;
                animation: none !important;
            }
        }
    </style>
</head>

<body>
    <main class="shell">

        {{-- ==========================================
             LEFT PANEL: BRANDING
        =========================================== --}}
        <aside class="side">

            <header class="brand">
                <div>
                    <h1>e-Poblacion</h1>
                    <p>Barangay Poblacion Record Management System</p>
                </div>
            </header>

            <div class="side-divider"></div>

            {{-- Large official seal: fills the space between the header and the footer. --}}
            <div class="seal-stage">
                <img
                    class="seal"
                    src="{{ asset('icon.png') }}"
                    alt="Barangay Poblacion official seal"
                    width="640"
                    height="640"
                    decoding="async"
                    fetchpriority="high"
                    draggable="false"
                >
            </div>

            <div class="side-foot">

                <p class="security-note">
                    <i class="bi bi-shield-check" aria-hidden="true"></i>

                    <span>
                        Never share your verification codes with anyone.
                        Email recovery is available only after your account
                        password has been verified.
                    </span>
                </p>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="btn-signout">
                        <i class="bi bi-box-arrow-left" aria-hidden="true"></i>
                        <span>Sign Out</span>
                    </button>
                </form>

            </div>
        </aside>


        {{-- ==========================================
             RIGHT PANEL: AUTHENTICATOR + EMAIL
        =========================================== --}}
        <section
            class="main"
            id="mainSection"
            data-method="{{ $emailStepOpen ? 'email' : 'auth' }}"
            aria-labelledby="page-title"
        >

            {{-- Sign-in progress --}}
            <ol class="steps" aria-label="Sign-in progress">
                <li class="is-done">
                    <span class="dot">
                        <i class="bi bi-check-lg" aria-hidden="true"></i>
                    </span>
                    <span class="label-text">Password</span>
                </li>

                <li class="bar" aria-hidden="true"></li>

                <li class="is-current" aria-current="step">
                    <span class="dot">2</span>
                    <span class="label-text">Verify identity</span>
                </li>
            </ol>

            {{-- Page heading --}}
            <div class="intro">
                <h2 id="page-title">Two-Factor Verification</h2>

                <p>
                    Your password has been accepted. Verify your identity
                    to continue to the records system.
                </p>
            </div>


            {{-- Validation errors --}}
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


            {{-- Status messages --}}
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


            {{-- Verification method tabs --}}
            <div class="tabs" role="tablist" aria-label="Verification method">

                <button
                    type="button"
                    class="tab"
                    role="tab"
                    id="tab-auth"
                    data-target="auth"
                    aria-controls="panel-auth"
                    aria-selected="{{ $emailStepOpen ? 'false' : 'true' }}"
                >
                    <i class="bi bi-phone" aria-hidden="true"></i>
                    <span>Authenticator</span>
                </button>

                <button
                    type="button"
                    class="tab"
                    role="tab"
                    id="tab-email"
                    data-target="email"
                    aria-controls="panel-email"
                    aria-selected="{{ $emailStepOpen ? 'true' : 'false' }}"
                >
                    <i class="bi bi-envelope" aria-hidden="true"></i>
                    <span>Email</span>
                </button>

            </div>


            {{-- ======================================
                 METHOD 1: AUTHENTICATOR APP
            ======================================= --}}
            <div
                class="panel"
                id="panel-auth"
                data-method="auth"
                role="tabpanel"
                aria-labelledby="tab-auth"
                @if ($emailStepOpen) hidden @endif
            >

                <div class="panel-head">
                    <div class="panel-icon">
                        <i class="bi bi-phone" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h3>Authenticator app</h3>
                        <p>
                            Enter the current six-digit code from your
                            authenticator app.
                        </p>
                    </div>
                </div>

                <form
                    method="POST"
                    action="{{ route('two-factor.verify') }}"
                    id="authForm"
                    novalidate
                >
                    @csrf

                    <label id="otpLabel">
                        Authenticator code
                    </label>

                    <div
                        class="otp"
                        data-otp
                        role="group"
                        aria-labelledby="otpLabel"
                    >
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

                        <input
                            type="hidden"
                            name="code"
                            data-otp-value
                        >
                    </div>

                    <p class="hint">
                        Type or paste the six-digit code.
                    </p>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        style="margin-top: 16px;"
                    >
                        <span>Verify and Continue</span>
                        <i class="bi bi-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                {{-- Shortcut to the email recovery in the left panel --}}
                <div class="link-row">
                    <button type="button" class="link-btn" data-open-email style="margin-top:0;">
                        <i class="bi bi-envelope" aria-hidden="true"></i>
                        <span>Forgot your authenticator code? Use email instead</span>
                    </button>
                </div>

            </div>


            {{-- ======================================
                 METHOD 2: EMAIL CODE
            ======================================= --}}
            <div
                class="panel"
                id="panel-email"
                data-method="email"
                role="tabpanel"
                aria-labelledby="tab-email"
                @unless ($emailStepOpen) hidden @endunless
            >

                <div class="panel-head">
                    <div class="panel-icon">
                        <i class="bi bi-envelope-check" aria-hidden="true"></i>
                    </div>

                    <div>
                        <h3>Email verification</h3>
                        <p>
                            Can't use your authenticator? Get a one-time code
                            at your registered email address.
                        </p>
                    </div>
                </div>

                <div class="email-flow">

                    {{-- Step 1: send the code to the registered email --}}
                    <div class="email-step @if ($emailStepOpen) is-done @endif">
                        <span class="email-step-num" aria-hidden="true">
                            @if ($emailStepOpen)
                                <i class="bi bi-check-lg"></i>
                            @else
                                1
                            @endif
                        </span>

                        <div class="email-step-text">
                            <strong>{{ $emailStepOpen ? 'Code sent' : 'Send the code' }}</strong>
                            <span>
                                @if ($emailStepOpen)
                                    Check your inbox, then enter it below. Need another one?
                                @else
                                    We'll email it to the address registered to your account.
                                @endif
                            </span>
                        </div>

                        <form
                            method="POST"
                            action="{{ route('two-factor.email-recovery.send') }}"
                        >
                            @csrf

                            <button type="submit" class="btn btn-outline" id="sendEmailCode">
                                <i class="bi bi-envelope-arrow-up" aria-hidden="true"></i>
                                <span>{{ $emailStepOpen ? 'Send again' : 'Send code' }}</span>
                            </button>
                        </form>
                    </div>

                    {{-- Step 2: verify the email code --}}
                    <form
                        method="POST"
                        action="{{ route('two-factor.email-recovery.verify') }}"
                        id="emailRecoveryForm"
                        novalidate
                    >
                        @csrf

                        <div class="email-verify-head">
                            <span class="email-step-num" aria-hidden="true">2</span>
                            <p id="emailOtpLabel">Enter the six-digit email code</p>
                        </div>

                        <div
                            class="otp"
                            data-otp
                            role="group"
                            aria-labelledby="emailOtpLabel"
                        >
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

                            <input
                                type="hidden"
                                name="email_code"
                                data-otp-value
                            >
                        </div>

                        <p class="hint">
                            The code expires after 10 minutes. Check your spam
                            folder if it doesn't arrive.
                        </p>

                        <button
                            type="submit"
                            class="btn btn-primary"
                            style="margin-top: 16px;"
                        >
                            <span>Verify Email Code</span>
                            <i class="bi bi-arrow-right" aria-hidden="true"></i>
                        </button>
                    </form>

                </div>

            </div>

        </section>
    </main>


    <script>
    (function () {

        /* ======================================
           SIX-DIGIT OTP INPUT HANDLER
        ======================================= */

        function setupOtp(group) {
            var boxes = Array.prototype.slice.call(
                group.querySelectorAll('.otp-box')
            );

            var hidden = group.querySelector('[data-otp-value]');

            function sync() {
                hidden.value = boxes.map(function (box) {
                    return box.value;
                }).join('');

                boxes.forEach(function (box) {
                    box.classList.toggle('filled', box.value !== '');
                });

                group.classList.remove('is-invalid');
            }

            function fill(start, text) {
                var digits = text.replace(/\D/g, '').split('');

                for (
                    var i = 0;
                    i < digits.length && start + i < boxes.length;
                    i++
                ) {
                    boxes[start + i].value = digits[i];
                }

                sync();

                var nextIndex = Math.min(
                    start + digits.length,
                    boxes.length - 1
                );

                boxes[nextIndex].focus();
            }

            boxes.forEach(function (box, i) {

                box.addEventListener('input', function () {
                    var digits = box.value.replace(/\D/g, '');

                    box.value = '';

                    if (digits.length > 1) {
                        fill(i, digits);
                        return;
                    }

                    box.value = digits;
                    sync();

                    if (digits && i < boxes.length - 1) {
                        boxes[i + 1].focus();
                    }
                });

                box.addEventListener('keydown', function (e) {

                    if (e.key === 'Backspace' && !box.value && i > 0) {
                        boxes[i - 1].value = '';
                        sync();
                        boxes[i - 1].focus();
                        e.preventDefault();

                    } else if (e.key === 'ArrowLeft' && i > 0) {
                        boxes[i - 1].focus();
                        e.preventDefault();

                    } else if (
                        e.key === 'ArrowRight' &&
                        i < boxes.length - 1
                    ) {
                        boxes[i + 1].focus();
                        e.preventDefault();
                    }

                });

                box.addEventListener('paste', function (e) {
                    e.preventDefault();

                    var text = (
                        e.clipboardData || window.clipboardData
                    ).getData('text');

                    fill(0, text);
                });

                box.addEventListener('focus', function () {
                    box.select();
                });

            });

            /* Require all six digits before submitting. */
            var form = group.closest('form');

            if (form) {
                form.addEventListener('submit', function (e) {

                    if (hidden.value.length !== 6) {
                        e.preventDefault();

                        group.classList.remove('is-invalid');
                        void group.offsetWidth;
                        group.classList.add('is-invalid');

                        var empty = boxes.filter(function (box) {
                            return !box.value;
                        })[0] || boxes[0];

                        empty.focus();
                    }

                });
            }
        }


        /* Initialize authenticator and email OTP groups. */
        document.querySelectorAll('[data-otp]').forEach(setupOtp);


        /* ======================================
           AUTHENTICATOR / EMAIL TABS
        ======================================= */

        var section = document.getElementById('mainSection');

        var tabs = Array.prototype.slice.call(
            document.querySelectorAll('.tab')
        );

        var panels = Array.prototype.slice.call(
            document.querySelectorAll('.panel')
        );

        function activate(method, focusInput) {

            section.setAttribute('data-method', method);

            tabs.forEach(function (tab) {
                tab.setAttribute(
                    'aria-selected',
                    tab.getAttribute('data-target') === method
                        ? 'true'
                        : 'false'
                );
            });

            panels.forEach(function (panel) {
                panel.hidden =
                    panel.getAttribute('data-method') !== method;
            });

            if (focusInput) {
                var panel = document.getElementById('panel-' + method);

                var target = method === 'email' &&
                    !panel.querySelector('.email-step.is-done')
                    ? document.getElementById('sendEmailCode')
                    : panel.querySelector('.otp-box');

                if (target) {
                    target.focus();
                }
            }
        }

        tabs.forEach(function (tab, i) {

            tab.addEventListener('click', function () {
                activate(tab.getAttribute('data-target'), true);
            });

            tab.addEventListener('keydown', function (e) {

                var next = null;

                if (e.key === 'ArrowRight') {
                    next = tabs[(i + 1) % tabs.length];
                }

                if (e.key === 'ArrowLeft') {
                    next = tabs[
                        (i - 1 + tabs.length) % tabs.length
                    ];
                }

                if (next) {
                    e.preventDefault();
                    next.focus();
                    activate(next.getAttribute('data-target'), false);
                }

            });

        });


        /* ======================================
           SHORTCUT: "USE EMAIL INSTEAD" LINK
        ======================================= */

        document.querySelectorAll('[data-open-email]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                activate('email', true);
            });
        });

    })();
    </script>

</body>
</html>