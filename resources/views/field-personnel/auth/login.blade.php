
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Field Personnel Login | e-Poblacion RMS</title>

    <style>
        :root {
            --green: #237a57;
            --green-dark: #185b40;
            --green-light: #e8f4ed;
            --pink: #f5a7b8;
            --pink-light: #fff0f3;
            --background: #fff7f8;
            --text: #26352e;
            --muted: #718078;
            --border: #e5e9e6;
            --danger: #b42335;
            --white: #ffffff;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: var(--text);
            background:
                radial-gradient(circle at 10% 10%, rgba(245, 167, 184, .20), transparent 30%),
                radial-gradient(circle at 90% 90%, rgba(35, 122, 87, .12), transparent 32%),
                var(--background);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .page-wrapper {
            width: 100%;
            max-width: 440px;
        }

        .brand {
            text-align: center;
            margin-bottom: 24px;
        }

        .brand-logo {
            width: 76px;
            height: 76px;
            object-fit: contain;
            display: block;
            margin: 0 auto 14px;
        }

        .brand-name {
            margin: 0;
            color: var(--green-dark);
            font-size: 22px;
            font-weight: 800;
            letter-spacing: .4px;
        }

        .brand-subtitle {
            margin: 7px 0 0;
            color: var(--muted);
            font-size: 12px;
            letter-spacing: 1.3px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .login-card {
            background: var(--white);
            border: 1px solid rgba(35, 122, 87, .10);
            border-radius: 20px;
            padding: 34px;
            box-shadow: 0 16px 45px rgba(39, 75, 57, .10);
        }

        .card-heading {
            text-align: center;
            margin-bottom: 28px;
        }

        .card-heading h1 {
            margin: 0;
            color: var(--green-dark);
            font-size: 24px;
            font-weight: 800;
        }

        .card-heading p {
            margin: 9px 0 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 700;
            color: #34483d;
        }

        .form-control {
            width: 100%;
            min-height: 48px;
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 15px;
            color: var(--text);
            background: #fff;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .form-control:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(35, 122, 87, .12);
        }

        .form-control::placeholder {
            color: #a1aaa5;
        }

        .passcode-wrapper {
            position: relative;
        }

        .passcode-input {
            padding-right: 76px;
            letter-spacing: 5px;
            font-size: 18px;
            font-weight: 700;
        }

        .toggle-passcode {
            position: absolute;
            top: 50%;
            right: 12px;
            transform: translateY(-50%);
            border: 0;
            background: transparent;
            color: var(--green);
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            padding: 8px 4px;
        }

        .toggle-passcode:hover {
            color: var(--green-dark);
        }

        .field-hint {
            display: block;
            margin-top: 7px;
            color: var(--muted);
            font-size: 12px;
        }

        .error-message {
            display: block;
            margin-top: 7px;
            color: var(--danger);
            font-size: 12px;
            line-height: 1.5;
        }

        .alert {
            padding: 12px 14px;
            margin-bottom: 20px;
            border-radius: 10px;
            font-size: 13px;
            line-height: 1.5;
        }

        .alert-success {
            color: #17613e;
            background: #e9f7ef;
            border: 1px solid #c8ead5;
        }

        .alert-error {
            color: #9f2332;
            background: #fff0f1;
            border: 1px solid #f3c8ce;
        }

        .login-button {
            width: 100%;
            min-height: 49px;
            margin-top: 5px;
            border: 0;
            border-radius: 10px;
            background: var(--green);
            color: #fff;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: background .2s, transform .2s, box-shadow .2s;
        }

        .login-button:hover {
            background: var(--green-dark);
            box-shadow: 0 6px 15px rgba(35, 122, 87, .18);
        }

        .login-button:active {
            transform: translateY(1px);
        }

        .security-note {
            display: flex;
            align-items: flex-start;
            gap: 9px;
            margin-top: 22px;
            padding: 12px;
            border-radius: 10px;
            background: var(--green-light);
            color: #426451;
            font-size: 12px;
            line-height: 1.6;
        }

        .security-icon {
            flex-shrink: 0;
            font-size: 15px;
        }

        .footer {
            text-align: center;
            margin-top: 22px;
            color: var(--muted);
            font-size: 11px;
            line-height: 1.7;
        }

        .footer strong {
            color: var(--green-dark);
        }

        @media (max-width: 480px) {
            body {
                padding: 18px;
                align-items: center;
            }

            .brand {
                margin-bottom: 19px;
            }

            .brand-logo {
                width: 66px;
                height: 66px;
            }

            .brand-name {
                font-size: 19px;
            }

            .login-card {
                padding: 26px 21px;
                border-radius: 16px;
            }

            .card-heading h1 {
                font-size: 22px;
            }
        }
    </style>
</head>

<body>
    <main class="page-wrapper">

        <header class="brand">
            <img
                src="{{ asset('icon.png') }}"
                alt="e-Poblacion RMS Logo"
                class="brand-logo"
            >

            <h2 class="brand-name">BARANGAY POBLACION</h2>
            <p class="brand-subtitle">Record Management System</p>
        </header>

        <section class="login-card">

            <div class="card-heading">
                <h1>Field Personnel Login</h1>
                <p>
                    Sign in using your assigned username and
                    six-digit system passcode.
                </p>
            </div>

            @if (session('success'))
                <div class="alert alert-success" role="status">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-error" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            @if ($errors->any() && ! $errors->has('username') && ! $errors->has('passcode'))
                <div class="alert alert-error" role="alert">
                    Please check your submitted information and try again.
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('field-personnel.login.authenticate') }}"
                autocomplete="on"
            >
                @csrf

                <div class="form-group">
                    <label for="username" class="form-label">
                        Username
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        value="{{ old('username') }}"
                        placeholder="Enter your assigned username"
                        maxlength="100"
                        autocomplete="username"
                        autocapitalize="none"
                        spellcheck="false"
                        required
                        autofocus
                        aria-describedby="username-error"
                    >

                    @error('username')
                        <span class="error-message" id="username-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="passcode" class="form-label">
                        Six-Digit Passcode
                    </label>

                    <div class="passcode-wrapper">
                        <input
                            type="password"
                            id="passcode"
                            name="passcode"
                            class="form-control passcode-input"
                            placeholder="••••••"
                            inputmode="numeric"
                            pattern="[0-9]{6}"
                            minlength="6"
                            maxlength="6"
                            autocomplete="current-password"
                            required
                            aria-describedby="passcode-hint passcode-error"
                        >

                        <button
                            type="button"
                            class="toggle-passcode"
                            id="togglePasscode"
                            aria-label="Show passcode"
                            aria-controls="passcode"
                            aria-pressed="false"
                        >
                            SHOW
                        </button>
                    </div>

                    <small class="field-hint" id="passcode-hint">
                        Enter the six-digit passcode issued by the Secretary.
                    </small>

                    @error('passcode')
                        <span class="error-message" id="passcode-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <button type="submit" class="login-button">
                    Sign In
                </button>
            </form>

            <div class="security-note">
                <span class="security-icon" aria-hidden="true">🔒</span>
                <span>
                    Your account is intended for authorized barangay
                    field operations only. Keep your passcode private
                    and do not share it with anyone.
                </span>
            </div>

        </section>

        <footer class="footer">
            <strong>e-Poblacion RMS</strong><br>
            Barangay Poblacion, Bani, Pangasinan<br>
            All Rights Reserved.
        </footer>

    </main>

    <script>
        (function () {
            const passcodeInput = document.getElementById('passcode');
            const toggleButton = document.getElementById('togglePasscode');

            if (!passcodeInput || !toggleButton) {
                return;
            }

            toggleButton.addEventListener('click', function () {
                const isPassword = passcodeInput.type === 'password';

                passcodeInput.type = isPassword ? 'text' : 'password';
                toggleButton.textContent = isPassword ? 'HIDE' : 'SHOW';
                toggleButton.setAttribute('aria-label', isPassword ? 'Hide passcode' : 'Show passcode');
                toggleButton.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
            });

            passcodeInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 6);
            });
        })();
    </script>
</body>
</html>
