<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <title>Authenticator Recovery | e-Poblacion</title>

    <style>
        :root {
            color-scheme: light;
            font-family: Arial, Helvetica, sans-serif;
            background: #f3f5f8;
            color: #172033;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            padding: 28px 16px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .container {
            width: 100%;
            max-width: 580px;
        }

        .brand {
            text-align: center;
            margin-bottom: 20px;
        }

        .brand h1 {
            margin: 0;
            font-size: 25px;
            letter-spacing: .4px;
        }

        .brand p {
            color: #667085;
            margin: 8px 0 0;
            font-size: 14px;
        }

        .card {
            background: #fff;
            border: 1px solid #e1e6ed;
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 10px 32px rgba(16, 24, 40, .07);
        }

        h2 {
            margin: 0 0 12px;
            font-size: 22px;
        }

        p {
            line-height: 1.6;
        }

        .muted {
            color: #667085;
            font-size: 14px;
        }

        .notice {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.55;
            margin: 16px 0;
        }

        .warning {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.55;
            margin: 16px 0;
        }

        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 14px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .error ul {
            margin: 6px 0 0;
            padding-left: 20px;
        }

        .field {
            margin: 18px 0;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 700;
            font-size: 14px;
        }

        input[type="password"],
        input[type="text"],
        input[type="tel"] {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font: inherit;
            font-size: 16px;
            background: #fff;
            color: #172033;
        }

        input:focus {
            outline: 3px solid rgba(37, 99, 235, .16);
            border-color: #2563eb;
        }

        button {
            width: 100%;
            border: 0;
            border-radius: 8px;
            background: #1d4ed8;
            color: white;
            font-size: 15px;
            font-weight: 700;
            padding: 13px 16px;
            cursor: pointer;
        }

        button:hover {
            background: #1e40af;
        }

        .qr {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }

        .qr svg {
            max-width: 100%;
            height: auto;
        }

        .secret {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 13px;
            overflow-wrap: anywhere;
            font-family: Consolas, monospace;
            font-size: 15px;
            user-select: all;
        }

        .footer {
            text-align: center;
            color: #667085;
            font-size: 12px;
            margin-top: 18px;
        }

        @media (max-width: 480px) {
            .card {
                padding: 21px 17px;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <header class="brand">
        <h1>e-Poblacion</h1>
        <p>Barangay Poblacion Record Management System</p>
    </header>

    <main class="card">
        <h2>Authenticator Recovery</h2>

        <p class="muted">
            Account:
            <strong>{{ $username }}</strong>
        </p>

        <div class="notice">
            This private recovery session was authorized by the Captain
            after an in-person identity check. It expires within 15 minutes
            of authorization. The Captain must not view your password,
            authenticator setup key, or QR code.
        </div>

        @if ($errors->any())
            <div class="error" role="alert">
                <strong>Please check the following:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($stage === 'verify_credentials')
            <p>
                Enter your own account password to continue. You do not
                need the lost phone or an old recovery code for this
                Captain-authorized process.
            </p>

            <div class="warning">
                Make sure the Captain has finished the authorization step
                and handed the workstation to you privately before
                entering your password.
            </div>

            <form
                method="POST"
                action="{{ route('secretary-authenticator-recovery.verify') }}"
                autocomplete="off"
            >
                @csrf

                <div class="field">
                    <label for="password">Your account password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        required
                        maxlength="255"
                        autocomplete="current-password"
                        autofocus
                    >
                </div>

                <button type="submit">
                    Verify Password and Continue
                </button>
            </form>

        @elseif ($stage === 'verify_new')
            <p>
                Scan the QR code below using the authenticator app on your
                replacement phone. If scanning is unavailable, enter the
                setup key manually in the app.
            </p>

            <div class="warning">
                Keep this page private. Do not photograph, send, or show
                the QR code or setup key to another person.
            </div>

            <div class="qr">
                {!! $qrCodeSvg !!}
            </div>

            <div class="field">
                <label for="secretKey">Manual setup key</label>
                <div id="secretKey" class="secret">{{ $secretKey }}</div>
                <p class="muted">
                    Enter the key exactly as displayed in your authenticator
                    app if you cannot scan the QR code.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('secretary-authenticator-recovery.confirm') }}"
                autocomplete="off"
            >
                @csrf

                <div class="field">
                    <label for="new_code">
                        Six-digit code from your NEW authenticator
                    </label>
                    <input
                        id="new_code"
                        name="new_code"
                        type="tel"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        minlength="6"
                        maxlength="6"
                        required
                        autocomplete="one-time-code"
                        autofocus
                    >
                </div>

                <button type="submit">
                    Verify and Activate New Authenticator
                </button>
            </form>

            <p class="muted">
                After the new authenticator code is verified, the new
                authenticator will be activated immediately. You will
                then be returned to the login page to sign in again.
            </p>

        @else
            <div class="error" role="alert">
                The recovery stage is invalid or expired. Do not continue
                using this page. Ask the Captain to authorize recovery again.
            </div>
        @endif
    </main>

    <footer class="footer">
        Private account recovery · Do not leave this page open unattended
    </footer>
</div>
</body>
</html>
