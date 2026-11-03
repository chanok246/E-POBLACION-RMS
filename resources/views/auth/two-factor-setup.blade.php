
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Set Up Two-Factor Authentication | e-Poblacion</title>

    <style>
        :root {
            color-scheme: light;
            --page-bg: #f4f7f5;
            --surface: #ffffff;
            --text: #18352b;
            --muted: #65766d;
            --brand: #176b4d;
            --brand-dark: #10543c;
            --border: #dce7e0;
            --soft: #edf7f0;
            --danger: #a62828;
            --danger-bg: #fff0f0;
            --shadow: 0 18px 55px rgba(22, 61, 43, .10);
        }

        * {
            box-sizing: border-box;
        }

        html {
            min-height: 100%;
            background: var(--page-bg);
        }

        body {
            min-height: 100vh;
            margin: 0;
            background:
                radial-gradient(
                    circle at 15% 10%,
                    rgba(34, 139, 94, .09),
                    transparent 32%
                ),
                var(--page-bg);
            color: var(--text);
            font-family: Inter, "Segoe UI", Arial, sans-serif;
            line-height: 1.55;
        }

        button,
        input {
            font: inherit;
        }

        button,
        a,
        input {
            -webkit-tap-highlight-color: transparent;
        }

        .setup-page {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .setup-header {
            width: 100%;
            padding: 20px 28px;
            background: rgba(255, 255, 255, .94);
            border-bottom: 1px solid var(--border);
        }

        .header-inner {
            width: 100%;
            max-width: 1100px;
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 13px;
        }

        .brand-mark {
            width: 46px;
            height: 46px;
            flex: 0 0 46px;
            display: grid;
            place-items: center;
            border-radius: 13px;
            background: var(--soft);
            border: 1px solid #d5e9dc;
            overflow: hidden;
        }

        .brand-mark img {
            display: block;
            width: 35px;
            height: 35px;
            object-fit: contain;
        }

        .brand-name {
            margin: 0;
            font-size: 17px;
            font-weight: 800;
            letter-spacing: .025em;
        }

        .brand-subtitle {
            margin: 1px 0 0;
            color: var(--muted);
            font-size: 12px;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .setup-main {
            width: 100%;
            max-width: 900px;
            margin: 0 auto;
            padding: 42px 22px 48px;
            flex: 1;
        }

        .intro {
            margin-bottom: 27px;
            text-align: center;
        }

        .security-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 16px;
            display: grid;
            place-items: center;
            border-radius: 20px;
            background: #e4f3e9;
            color: var(--brand);
        }

        .security-icon svg {
            width: 34px;
            height: 34px;
        }

        .eyebrow {
            margin: 0 0 8px;
            color: var(--brand);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-size: clamp(27px, 4vw, 36px);
            line-height: 1.2;
            letter-spacing: -.035em;
        }

        .intro-description {
            max-width: 590px;
            margin: 13px auto 0;
            color: var(--muted);
            font-size: 15px;
        }

        .setup-card {
            width: 100%;
            overflow: hidden;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: var(--shadow);
        }

        .card-content {
            padding: 30px;
        }

        .alert {
            margin: 0 0 22px;
            padding: 14px 16px;
            border: 1px solid #f0c7c7;
            border-radius: 12px;
            background: var(--danger-bg);
            color: var(--danger);
            font-size: 14px;
        }

        .alert strong {
            display: block;
            margin-bottom: 5px;
        }

        .alert ul {
            margin: 5px 0 0;
            padding-left: 20px;
        }

        .steps {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-bottom: 30px;
        }

        .step {
            min-width: 0;
            padding: 13px 12px;
            background: #f8faf9;
            border: 1px solid var(--border);
            border-radius: 13px;
        }

        .step-number {
            width: 27px;
            height: 27px;
            margin-bottom: 8px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--brand);
            color: #fff;
            font-size: 12px;
            font-weight: 800;
        }

        .step strong {
            display: block;
            margin-bottom: 3px;
            font-size: 13px;
        }

        .step span {
            display: block;
            color: var(--muted);
            font-size: 12px;
        }

        .setup-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 30px;
            align-items: start;
        }

        .section-title {
            margin: 0 0 8px;
            font-size: 18px;
            font-weight: 800;
        }

        .section-description {
            margin: 0 0 18px;
            color: var(--muted);
            font-size: 14px;
        }

        .qr-panel {
            min-height: 260px;
            padding: 22px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px dashed #bdd7c6;
            border-radius: 16px;
            background: #fbfdfb;
            text-align: center;
        }

        .qr-code {
            width: 220px;
            max-width: 100%;
            min-height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #fff;
        }

        .qr-code svg {
            display: block;
            width: 220px;
            max-width: 100%;
            height: auto;
        }

        .qr-code img {
            display: block;
            width: 220px;
            max-width: 100%;
            height: auto;
        }

        .qr-caption {
            margin: 15px 0 0;
            color: var(--muted);
            font-size: 12px;
        }

        .manual-key {
            margin-top: 18px;
        }

        .manual-key-label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
        }

        .key-row {
            display: flex;
            align-items: stretch;
            gap: 8px;
        }

        .secret-key {
            min-width: 0;
            flex: 1;
            padding: 12px;
            border: 1px solid var(--border);
            border-radius: 10px;
            background: #f7faf8;
            color: var(--text);
            font-family: Consolas, "Courier New", monospace;
            font-size: 12px;
            overflow-wrap: anywhere;
            user-select: all;
        }

        .copy-button {
            flex: 0 0 auto;
            padding: 10px 12px;
            border: 1px solid #b9d8c5;
            border-radius: 10px;
            background: #edf7f0;
            color: var(--brand-dark);
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
        }

        .copy-button:hover {
            background: #e0f0e5;
        }

        .key-help {
            margin: 8px 0 0;
            color: var(--muted);
            font-size: 12px;
        }

        .verify-panel {
            padding: 22px;
            border: 1px solid var(--border);
            border-radius: 16px;
            background: #fff;
        }

        .verify-label {
            display: block;
            margin: 0 0 9px;
            font-size: 14px;
            font-weight: 800;
        }

        .code-input {
            display: block;
            width: 100%;
            min-height: 58px;
            padding: 12px;
            border: 1px solid #b9cfc1;
            border-radius: 12px;
            outline: none;
            background: #fff;
            color: var(--text);
            text-align: center;
            font-family: Consolas, "Courier New", monospace;
            font-size: 27px;
            font-weight: 800;
            letter-spacing: .32em;
            font-variant-numeric: tabular-nums;
        }

        .code-input:focus {
            border-color: var(--brand);
            box-shadow: 0 0 0 4px rgba(23, 107, 77, .12);
        }

        .code-input.is-invalid {
            border-color: #c43b3b;
        }

        .field-error {
            margin-top: 8px;
            color: var(--danger);
            font-size: 13px;
        }

        .verify-help {
            margin: 11px 0 20px;
            color: var(--muted);
            font-size: 13px;
        }

        .primary-button {
            width: 100%;
            min-height: 49px;
            padding: 13px 18px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            border: 0;
            border-radius: 12px;
            background: var(--brand);
            color: #fff;
            font-size: 14px;
            font-weight: 800;
            cursor: pointer;
            transition: background .15s ease, transform .15s ease;
        }

        .primary-button:hover {
            background: var(--brand-dark);
        }

        .primary-button:active {
            transform: translateY(1px);
        }

        .primary-button:focus-visible,
        .copy-button:focus-visible,
        .logout-button:focus-visible {
            outline: 3px solid #efc75e;
            outline-offset: 3px;
        }

        .security-note {
            margin-top: 18px;
            padding: 13px 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            border-radius: 12px;
            background: #f1f7f3;
            color: #395b49;
            font-size: 12px;
        }

        .security-note svg {
            width: 18px;
            height: 18px;
            flex: 0 0 18px;
            margin-top: 1px;
        }

        .card-footer {
            padding: 17px 30px;
            display: flex;
            justify-content: center;
            border-top: 1px solid var(--border);
            background: #fbfcfb;
        }

        .logout-form {
            margin: 0;
        }

        .logout-button {
            padding: 8px 14px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: #fff;
            color: #52675c;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }

        .logout-button:hover {
            background: #f4f7f5;
        }

        .page-footer {
            padding: 18px 20px 22px;
            color: var(--muted);
            text-align: center;
            font-size: 12px;
        }

        @media (max-width: 700px) {
            .setup-header {
                padding: 16px;
            }

            .setup-main {
                padding: 28px 14px 32px;
            }

            .card-content {
                padding: 20px;
            }

            .steps {
                gap: 7px;
            }

            .step {
                padding: 10px 8px;
            }

            .step strong {
                font-size: 12px;
            }

            .step span {
                font-size: 11px;
            }

            .setup-grid {
                grid-template-columns: minmax(0, 1fr);
                gap: 25px;
            }

            .card-footer {
                padding: 16px 20px;
            }
        }

        @media (max-width: 380px) {
            .steps {
                grid-template-columns: minmax(0, 1fr);
            }

            .step {
                display: flex;
                align-items: center;
                gap: 10px;
            }

            .step-number {
                flex: 0 0 27px;
                margin: 0;
            }

            .code-input {
                font-size: 23px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;
                transition: none !important;
            }
        }
    </style>
</head>

<body>
    @php
        /*
         * Keep the setup page independent of layouts.app so the
         * operational sidebar and dashboard navigation do not render.
         */
        $setupQrCode = $qrCodeSvg ?? $qrCode ?? null;
        $setupSecret = $secret ?? $manualKey ?? $manualSetupKey ?? null;
    @endphp

    <div class="setup-page">
        <header class="setup-header">
            <div class="header-inner">
                <div class="brand-mark">
                    <img
                        src="{{ asset('icon.png') }}"
                        alt="e-Poblacion logo"
                        onerror="this.style.display='none';"
                    >
                </div>

                <div>
                    <p class="brand-name">e-Poblacion</p>
                    <p class="brand-subtitle">Barangay Record Management System</p>
                </div>
            </div>
        </header>

        <main class="setup-main">
            <section class="intro">
                <div class="security-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path
                            d="M12 3 19 6v5c0 4.7-2.9 8-7 10-4.1-2-7-5.3-7-10V6l7-3Z"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linejoin="round"
                        />
                        <path
                            d="m9 12 2 2 4-4"
                            stroke="currentColor"
                            stroke-width="1.8"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>

                <p class="eyebrow">Account security</p>
                <h1>Set Up Two-Factor Authentication</h1>

                <p class="intro-description">
                    Secure your account by connecting an authenticator app.
                    Complete the setup below before continuing to the system.
                </p>
            </section>

            <section class="setup-card" aria-label="Authenticator setup">
                <div class="card-content">
                    @if ($errors->any())
                        <div class="alert" role="alert">
                            <strong>Setup needs your attention</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="steps" aria-label="Setup steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <strong>Open your app</strong>
                            <span>Use an authenticator app on your phone.</span>
                        </div>

                        <div class="step">
                            <div class="step-number">2</div>
                            <strong>Scan the QR code</strong>
                            <span>Add this account to the app.</span>
                        </div>

                        <div class="step">
                            <div class="step-number">3</div>
                            <strong>Verify your code</strong>
                            <span>Enter the current six-digit code.</span>
                        </div>
                    </div>

                    <div class="setup-grid">
                        <section>
                            <h2 class="section-title">1. Connect your authenticator</h2>

                            <p class="section-description">
                                Open your authenticator app and scan the QR code below.
                            </p>

                            <div class="qr-panel">
                                <div class="qr-code">
                                    @if (is_string($setupQrCode) && trim($setupQrCode) !== '')
                                        @if (str_contains($setupQrCode, '<svg'))
                                            {!! $setupQrCode !!}
                                        @else
                                            <img
                                                src="{{ $setupQrCode }}"
                                                alt="Authenticator setup QR code"
                                            >
                                        @endif
                                    @else
                                        <p class="section-description">
                                            The QR code is not available. Use the manual
                                            setup key below if it is provided.
                                        </p>
                                    @endif
                                </div>

                                <p class="qr-caption">
                                    Keep this QR code private. Do not share it with anyone.
                                </p>
                            </div>

                            @if (is_string($setupSecret) && trim($setupSecret) !== '')
                                <div class="manual-key">
                                    <label class="manual-key-label" for="manualSetupKey">
                                        Can't scan the QR code?
                                    </label>

                                    <div class="key-row">
                                        <div class="secret-key" id="manualSetupKey">{{ $setupSecret }}</div>

                                        <button
                                            type="button"
                                            class="copy-button"
                                            id="copySecretButton"
                                        >
                                            Copy key
                                        </button>
                                    </div>

                                    <p class="key-help">
                                        In your authenticator app, choose the option to
                                        enter a setup key manually.
                                    </p>
                                </div>
                            @endif
                        </section>

                        <section>
                            <h2 class="section-title">2. Verify your account</h2>

                            <p class="section-description">
                                Enter the six-digit code currently displayed by your
                                authenticator app.
                            </p>

                            <form
                                method="POST"
                                action="{{ route('two-factor.confirm-setup') }}"
                                id="twoFactorSetupForm"
                            >
                                @csrf

                                <label class="verify-label" for="code">
                                    Authenticator verification code
                                </label>

                                <input
                                    type="text"
                                    id="code"
                                    name="code"
                                    class="code-input @error('code') is-invalid @enderror"
                                    value="{{ old('code') }}"
                                    inputmode="numeric"
                                    pattern="[0-9]{6}"
                                    minlength="6"
                                    maxlength="6"
                                    autocomplete="one-time-code"
                                    placeholder="000000"
                                    aria-describedby="codeHelp"
                                    aria-invalid="@error('code') true @else false @enderror"
                                    required
                                    autofocus
                                >

                                @error('code')
                                    <p class="field-error" role="alert">{{ $message }}</p>
                                @enderror

                                <p class="verify-help" id="codeHelp">
                                    The code changes periodically. Enter the latest
                                    code shown in your authenticator app.
                                </p>

                                <button type="submit" class="primary-button">
                                    <svg
                                        width="18"
                                        height="18"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="m5 12 4 4L19 6"
                                            stroke="currentColor"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                    Verify and Enable
                                </button>
                            </form>

                            <div class="security-note">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path
                                        d="M12 3 19 6v5c0 4.7-2.9 8-7 10-4.1-2-7-5.3-7-10V6l7-3Z"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linejoin="round"
                                    />
                                    <path
                                        d="M12 8v5m0 3h.01"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                    />
                                </svg>

                                <div>
                                    <strong>Important:</strong>
                                    After verification, follow the recovery-code page
                                    and save your recovery codes in a secure place.
                                    Finish that step to complete setup.
                                </div>
                            </div>
                        </section>
                    </div>
                </div>

                <footer class="card-footer">
                    <form
                        method="POST"
                        action="{{ route('logout') }}"
                        class="logout-form"
                    >
                        @csrf
                        <button type="submit" class="logout-button">
                            Sign out
                        </button>
                    </form>
                </footer>
            </section>
        </main>

        <footer class="page-footer">
            e-Poblacion · Barangay Poblacion, Bani, Pangasinan
            <br>
            Secure access to authorized barangay records
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const codeInput = document.getElementById('code');
            const setupForm = document.getElementById('twoFactorSetupForm');
            const copyButton = document.getElementById('copySecretButton');
            const secretElement = document.getElementById('manualSetupKey');

            if (codeInput) {
                codeInput.addEventListener('input', function () {
                    this.value = this.value.replace(/\D/g, '').slice(0, 6);
                });
            }

            if (setupForm && codeInput) {
                setupForm.addEventListener('submit', function (event) {
                    codeInput.value = codeInput.value.replace(/\D/g, '');

                    if (!/^\d{6}$/.test(codeInput.value)) {
                        event.preventDefault();
                        codeInput.focus();
                        codeInput.setAttribute('aria-invalid', 'true');
                    }
                });
            }

            if (copyButton && secretElement) {
                copyButton.addEventListener('click', async function () {
                    const secret = secretElement.textContent.trim();

                    try {
                        await navigator.clipboard.writeText(secret);
                        copyButton.textContent = 'Copied';
                    } catch (error) {
                        const range = document.createRange();
                        range.selectNodeContents(secretElement);

                        const selection = window.getSelection();
                        selection.removeAllRanges();
                        selection.addRange(range);

                        copyButton.textContent = 'Select and copy';
                    }

                    window.setTimeout(function () {
                        copyButton.textContent = 'Copy key';
                    }, 1800);
                });
            }
        });
    </script>
</body>
</html>
