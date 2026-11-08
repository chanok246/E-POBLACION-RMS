
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Authenticator Recovery | e-Poblacion</title>

    <style>
        :root {
            --primary: #198754;
            --primary-dark: #146c43;
            --background: #f3f6f9;
            --text: #263445;
            --muted: #718096;
            --border: #e2e8f0;
            --danger: #dc3545;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--background);
            color: var(--text);
            display: flex;
            flex-direction: column;
        }

        .topbar {
            width: 100%;
            background: #fff;
            border-bottom: 1px solid var(--border);
            padding: 17px 24px;
        }

        .brand {
            max-width: 1050px;
            margin: auto;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            display: grid;
            place-items: center;
            background: #e8f5ed;
            color: var(--primary);
            border-radius: 12px;
            font-size: 21px;
            font-weight: 700;
        }

        .brand-name {
            font-size: 14px;
            font-weight: 800;
            letter-spacing: .5px;
        }

        .brand-subtitle {
            margin-top: 3px;
            font-size: 11px;
            color: var(--muted);
            letter-spacing: 1px;
        }

        main {
            width: 100%;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 42px 18px;
        }

        .recovery-card {
            width: 100%;
            max-width: 700px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 17px;
            box-shadow: 0 12px 35px rgba(15, 23, 42, .07);
            overflow: hidden;
        }

        .card-body {
            padding: 38px;
        }

        .heading {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 26px;
        }

        .heading-icon {
            width: 55px;
            height: 55px;
            flex-shrink: 0;
            display: grid;
            place-items: center;
            background: #e8f5ed;
            color: var(--primary);
            border-radius: 15px;
            font-size: 27px;
        }

        h1 {
            font-size: 23px;
            margin: 0 0 6px;
        }

        h2 {
            font-size: 18px;
            margin: 0 0 12px;
        }

        p {
            line-height: 1.65;
        }

        .muted {
            color: var(--muted);
            font-size: 14px;
            margin: 0;
        }

        .notice {
            padding: 15px 17px;
            border-radius: 10px;
            margin-bottom: 22px;
            font-size: 13px;
            line-height: 1.6;
        }

        .notice-warning {
            background: #fff8e5;
            border: 1px solid #f4dfaa;
            color: #795b13;
        }

        .notice-success {
            background: #eaf7ef;
            border: 1px solid #b9e4c9;
            color: #17653a;
        }

        .notice-danger {
            background: #fff0f0;
            border: 1px solid #f3c4c4;
            color: #a52834;
        }

        .account {
            font-size: 14px;
            margin: 22px 0;
            overflow-wrap: anywhere;
        }

        .divider {
            height: 1px;
            background: var(--border);
            margin: 25px 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        input {
            display: block;
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 9px;
            font-size: 14px;
            color: var(--text);
            background: #fff;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(25, 135, 84, .12);
        }

        input[readonly] {
            background: #f5f7f9;
        }

        .input-error {
            border-color: var(--danger);
        }

        .field-error {
            color: var(--danger);
            font-size: 12px;
            margin-top: 6px;
        }

        .help {
            display: block;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
            margin-top: 7px;
        }

        .btn-primary {
            width: 100%;
            display: block;
            padding: 13px 18px;
            background: var(--primary);
            color: #fff;
            border: 0;
            border-radius: 9px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .qr-wrapper {
            display: flex;
            justify-content: center;
            margin: 25px 0;
        }

        .qr-box {
            padding: 15px;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 12px;
            max-width: 100%;
            overflow: auto;
        }

        .qr-box svg {
            display: block;
            max-width: 100%;
            height: auto;
        }

        .security-note {
            font-size: 12px;
            line-height: 1.7;
            color: var(--muted);
            margin: 0;
        }

        footer {
            padding: 18px;
            text-align: center;
            color: var(--muted);
            font-size: 11px;
            border-top: 1px solid var(--border);
            background: #fff;
        }

        @media (max-width: 600px) {
            .topbar {
                padding: 14px 17px;
            }

            main {
                padding: 24px 13px;
                align-items: flex-start;
            }

            .card-body {
                padding: 24px 19px;
            }

            h1 {
                font-size: 19px;
            }

            .heading {
                gap: 12px;
            }

            .heading-icon {
                width: 47px;
                height: 47px;
                font-size: 23px;
            }
        }
    </style>
</head>

<body>

    <header class="topbar">
        <div class="brand">
            <div class="brand-icon" aria-hidden="true">✦</div>
            <div>
                <div class="brand-name">BARANGAY POBLACION</div>
                <div class="brand-subtitle">RECORD MANAGEMENT SYSTEM</div>
            </div>
        </div>
    </header>

    <main>
        <section class="recovery-card" aria-labelledby="recoveryTitle">
            <div class="card-body">

                <div class="heading">
                    <div class="heading-icon" aria-hidden="true">♙</div>
                    <div>
                        <h1 id="recoveryTitle">Authenticator Recovery</h1>
                        <p class="muted">Restore secure access to your account.</p>
                    </div>
                </div>

                <div class="notice notice-warning" role="alert">
                    <strong>Restricted recovery session</strong>
                    <p style="margin:5px 0 0;">
                        This page is only for replacing your lost authenticator.
                        Your normal system access must remain restricted until
                        the replacement is completed.
                    </p>
                </div>

                @if (session('success'))
                    <div class="notice notice-success" role="status">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="notice notice-danger" role="alert">
                        <strong>Please check the following:</strong>
                        <ul style="margin:8px 0 0;padding-left:20px;">
                            @foreach ($errors->all() as $message)
                                <li>{{ $message }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="account">
                    Account:
                    <strong>{{ $user->username ?: $user->email }}</strong>
                </div>

                @if (($replacementStage ?? null) === 'verify_new')

                    <div class="divider"></div>

                    <h2>Step 2 — Set up your replacement phone</h2>

                    <p class="muted">
                        Scan this QR code using your authenticator app on your
                        replacement phone. Enter the six-digit code generated
                        by the app to verify the new authenticator.
                    </p>

                    @if (!empty($replacementQrCodeSvg))
                        <div class="qr-wrapper">
                            <div class="qr-box">
                                {!! $replacementQrCodeSvg !!}
                            </div>
                        </div>
                    @endif

                    @if (!empty($replacementSecretKey))
                        <div class="form-group">
                            <label for="replacementSecretKey">
                                Manual setup key
                            </label>

                            <input
                                type="text"
                                id="replacementSecretKey"
                                value="{{ $replacementSecretKey }}"
                                readonly
                                autocomplete="off"
                            >

                            <small class="help">
                                Keep this key private. Do not share it or expose
                                it on an untrusted device.
                            </small>
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('settings.authenticator-replacement.verify') }}">
                        @csrf

                        <div class="form-group">
                            <label for="replacementNewCode">
                                Code from your new authenticator
                            </label>

                            <input
                                type="text"
                                id="replacementNewCode"
                                name="new_code"
                                class="@error('new_code') input-error @enderror"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                minlength="6"
                                maxlength="6"
                                autocomplete="one-time-code"
                                required
                            >

                            @error('new_code')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn-primary">
                            Verify and activate new authenticator
                        </button>
                    </form>

                @else

                    <div class="divider"></div>

                    <h2>Step 1 — Confirm your account</h2>

                    <p class="muted" style="margin-bottom:22px;">
                        Enter your current account password. For lost-phone
                        recovery, the server must also confirm that email
                        recovery was completed for this same account.
                    </p>

                    <form method="POST"
                          action="{{ route('settings.authenticator-replacement.start') }}">
                        @csrf

                        <div class="form-group">
                            <label for="currentPassword">
                                Current account password
                            </label>

                            <input
                                type="password"
                                id="currentPassword"
                                name="current_password"
                                class="@error('current_password') input-error @enderror"
                                autocomplete="current-password"
                                required
                            >

                            @error('current_password')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="oldCode">
                                Old authenticator code
                                <span style="font-weight:400;color:#718096;">
                                    (optional after email recovery)
                                </span>
                            </label>

                            <input
                                type="text"
                                id="oldCode"
                                name="old_code"
                                class="@error('old_code') input-error @enderror"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                minlength="6"
                                maxlength="6"
                                autocomplete="one-time-code"
                                placeholder="Leave blank only for authorized lost-phone recovery"
                            >

                            @error('old_code')
                                <div class="field-error">{{ $message }}</div>
                            @enderror

                            <small class="help">
                                Leaving this field blank must never grant
                                recovery permission by itself.
                            </small>
                        </div>

                        <button type="submit" class="btn-primary">
                            Continue to new authenticator setup
                        </button>
                    </form>

                @endif

                <div class="divider"></div>

                <p class="security-note">
                    Keep your replacement QR code and manual setup key private.
                    Your old authenticator must remain active until the server
                    successfully verifies and activates the replacement.
                </p>

            </div>
        </section>
    </main>

    <footer>
        &copy; {{ date('Y') }} Barangay Poblacion, Bani, Pangasinan.
        All Rights Reserved.
    </footer>

</body>
</html>
