
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ !empty($isPasswordRecovery) ? 'Reset Your Password' : 'Secretary Account Setup' }}
        | e-Poblacion
    </title>

    <style>
        :root {
            color-scheme: light dark;
            --bg: #f3f6fb;
            --surface: #ffffff;
            --text: #172033;
            --muted: #64748b;
            --border: #dce3ed;
            --primary: #3157c8;
            --primary-hover: #2445a9;
            --error-bg: #fff0f0;
            --error-text: #a51d2d;
            --success-bg: #eaf8ef;
            --success-text: #176b38;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 24px;
            display: grid;
            place-items: center;
            background: var(--bg);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        .setup-card {
            width: 100%;
            max-width: 460px;
            padding: 32px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 18px;
            box-shadow: 0 16px 45px rgba(23, 32, 51, .08);
        }

        .brand {
            margin-bottom: 28px;
            color: var(--primary);
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        h1 {
            margin: 0 0 10px;
            font-size: 27px;
            line-height: 1.25;
        }

        h2 {
            margin: 0 0 10px;
            font-size: 19px;
            line-height: 1.4;
        }

        .intro {
            margin: 0 0 25px;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.65;
        }

        .field {
            margin-bottom: 19px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 700;
        }

        input {
            width: 100%;
            min-height: 46px;
            padding: 12px 13px;
            border: 1px solid var(--border);
            border-radius: 9px;
            background: var(--surface);
            color: var(--text);
            font: inherit;
        }

        input:focus {
            outline: 3px solid rgba(49, 87, 200, .17);
            border-color: var(--primary);
        }

        .help {
            margin-top: 7px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.5;
        }

        .error-summary {
            margin-bottom: 20px;
            padding: 13px 15px;
            border-radius: 9px;
            background: var(--error-bg);
            color: var(--error-text);
            font-size: 13px;
            line-height: 1.6;
        }

        .error-summary ul {
            margin: 8px 0 0;
            padding-left: 20px;
        }

        .success-message {
            margin-bottom: 20px;
            padding: 13px 15px;
            border-radius: 9px;
            background: var(--success-bg);
            color: var(--success-text);
            font-size: 13px;
            line-height: 1.6;
        }

        .notice {
            margin-bottom: 20px;
            padding: 14px 15px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-size: 13px;
            line-height: 1.65;
        }

        .notice p {
            margin: 7px 0 0;
            color: var(--muted);
        }

        button {
            width: 100%;
            min-height: 47px;
            margin-top: 5px;
            padding: 12px 16px;
            border: 0;
            border-radius: 9px;
            background: var(--primary);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
        }

        button:hover {
            background: var(--primary-hover);
        }

        button:focus-visible {
            outline: 3px solid rgba(49, 87, 200, .35);
            outline-offset: 3px;
        }

        .secondary-button {
            margin-top: 10px;
            border: 1px solid var(--border);
            background: transparent;
            color: var(--text);
        }

        .secondary-button:hover {
            background: var(--bg);
        }

        .divider {
            height: 1px;
            margin: 25px 0;
            background: var(--border);
        }

        .footer {
            margin-top: 22px;
            color: var(--muted);
            font-size: 12px;
            line-height: 1.6;
            text-align: center;
        }

        @media (max-width: 480px) {
            body {
                padding: 14px;
            }

            .setup-card {
                padding: 24px 20px;
            }

            h1 {
                font-size: 24px;
            }
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #101522;
                --surface: #191f2e;
                --text: #eef2fa;
                --muted: #a5b0c3;
                --border: #343d50;
                --primary: #6f8df4;
                --primary-hover: #8ba3ff;
                --error-bg: #3a2028;
                --error-text: #ffb8c0;
                --success-bg: #173727;
                --success-text: #a8efbd;
            }

            button:not(.secondary-button) {
                color: #101522;
            }
        }
    </style>
</head>
<body>
    <main class="setup-card">
        <div class="brand">e-Poblacion · Record Management System</div>

        @if (!empty($isPasswordRecovery))
            <h1>Reset your password</h1>

            <p class="intro">
                @if (!empty($secretaryName))
                    Hello, {{ $secretaryName }}.
                @endif

                Your password reset was authorized through the barangay's
                onsite verification process. Create your new private
                password below. The Captain does not need to know or enter
                your new password.
            </p>
        @else
            <h1>Set up your Secretary account</h1>

            <p class="intro">
                @if (!empty($secretaryName))
                    Welcome, {{ $secretaryName }}.
                @endif

                Verify the email address registered for your account first.
                After successful verification, you can create your own
                permanent password.
            </p>
        @endif

        @if (session('success'))
            <div class="success-message" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="error-summary" role="alert">
                <strong>Please correct the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Verify the registered email before initial password setup. --}}
        @if (empty($isPasswordRecovery) && empty($emailVerified))
            <section aria-labelledby="email-verification-heading">
                <h2 id="email-verification-heading">Step 1: Verify your email</h2>

                <div class="notice">
                    <strong>Verification required</strong>
                    <p>
                        A six-digit verification code is sent to the email
                        address registered by the Captain. Enter the code
                        below. It expires after 10 minutes.
                    </p>

                    @if (!empty($email))
                        <p>
                            Registered email:
                            <strong>{{ $email }}</strong>
                        </p>
                    @endif
                </div>

                <form
                    method="POST"
                    action="{{ route('secretary-email-verification.verify') }}"
                >
                    @csrf

                    <div class="field">
                        <label for="verification_code">
                            Six-digit verification code
                        </label>

                        <input
                            id="verification_code"
                            name="verification_code"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            minlength="6"
                            maxlength="6"
                            autocomplete="one-time-code"
                            placeholder="Enter 6-digit code"
                            value="{{ old('verification_code') }}"
                            required
                            autofocus
                            aria-describedby="verification-code-help"
                        >

                        <div class="help" id="verification-code-help">
                            Enter the code from your email. Do not share
                            this code with anyone.
                        </div>
                    </div>

                    <button type="submit">
                        Verify email address
                    </button>
                </form>

                <div class="divider"></div>

                <form
                    method="POST"
                    action="{{ route('secretary-email-verification.resend') }}"
                >
                    @csrf

                    <button type="submit" class="secondary-button">
                        Resend verification code
                    </button>

                    <div class="help">
                        Request a new code if the previous code has expired
                        or you did not receive it. Resending is subject to
                        a security limit.
                    </div>
                </form>
            </section>
        @else
            {{-- Password setup or authorized recovery. --}}
            @if (empty($isPasswordRecovery))
                <div class="notice">
                    <strong>Email verification complete</strong>
                    <p>
                        You may now create your permanent password.
                        Keep it private and do not share it with the Captain
                        or anyone else.
                    </p>
                </div>
            @endif

            <section aria-labelledby="password-heading">
                <h2 id="password-heading">
                    {{ !empty($isPasswordRecovery) ? 'Choose a new password' : 'Step 2: Set your password' }}
                </h2>

                <form
                    method="POST"
                    action="{{ route('secretary-password-setup.store') }}"
                >
                    @csrf

                    <div class="field">
                        <label for="password">
                            {{ !empty($isPasswordRecovery) ? 'New password' : 'New permanent password' }}
                        </label>

                        <input
                            id="password"
                            name="password"
                            type="password"
                            minlength="12"
                            maxlength="255"
                            autocomplete="new-password"
                            required
                            autofocus
                        >

                        <div class="help">
                            Use at least 12 characters. Choose a strong
                            password that other people cannot easily guess.
                            Never share it with anyone.
                        </div>
                    </div>

                    <div class="field">
                        <label for="password_confirmation">
                            Confirm new password
                        </label>

                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            minlength="12"
                            maxlength="255"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button type="submit">
                        {{ !empty($isPasswordRecovery) ? 'Reset my password' : 'Set my permanent password' }}
                    </button>
                </form>
            </section>
        @endif

        @if (!empty($isPasswordRecovery))
            <div class="footer">
                After resetting your password, return to the normal login
                page. You must sign in with your new password and complete
                the required two-factor authentication challenge.
                Your existing two-factor settings are not reset by this
                password recovery process.
            </div>
        @else
            <div class="footer">
                This page is available only through your authorized,
                temporary setup session. Email verification is required
                before initial password setup. Setting your password does
                not replace the system's mandatory two-factor
                authentication. Never share your password or verification
                code.
            </div>
        @endif
    </main>
</body>
</html>
