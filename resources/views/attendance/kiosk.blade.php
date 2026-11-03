<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#e5505c">
    <title>Attendance Kiosk | e-Poblacion</title>

    <style>
        /* =========================================================
           ATTENDANCE KIOSK — watermelon theme (pink + rind green)
           Modern, semi-minimalist. System fonts + inline SVG only,
           so it still looks right when the kiosk is offline.
           ========================================================= */
        :root {
            color-scheme: light;
            --brand: #e5505c;
            --brand-d: #c93a47;
            --brand-soft: #fdecee;
            --pink: #f2808a;
            --green: #4fae4e;
            --green-d: #3b8a40;
            --green-soft: #e8f5e4;
            --ink: #2a1f23;
            --muted: #7d6a70;
            --faint: #a8979c;
            --line: #f1dde1;
            --line-strong: #e6c9cf;
            --paper: #fdf7f8;
            --sheet: #ffffff;
            --font: ui-rounded, 'Nunito', 'Quicksand', 'Trebuchet MS', system-ui, -apple-system, 'Segoe UI', sans-serif;
        }

        * { box-sizing: border-box; }

        html { -webkit-text-size-adjust: 100%; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: var(--font);
            color: var(--ink);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            background:
                radial-gradient(55% 40% at 100% 0%, rgba(229, 80, 92, .10), transparent 70%),
                radial-gradient(50% 40% at 0% 100%, rgba(79, 174, 78, .11), transparent 70%),
                var(--paper);
            background-attachment: fixed;
        }

        button, input { font: inherit; }

        :focus-visible { outline: 3px solid rgba(229, 80, 92, .4); outline-offset: 2px; }

        .i {
            width: 20px; height: 20px; flex-shrink: 0;
            fill: none; stroke: currentColor; stroke-width: 2;
            stroke-linecap: round; stroke-linejoin: round;
        }

        .hidden { display: none !important; }

        .page {
            width: min(100% - 28px, 520px);
            margin: 0 auto;
            padding: 34px 0 20px;
        }

        /* ---------------- Header ---------------- */
        .header { text-align: center; margin-bottom: 22px; }

        .seal {
            position: relative;
            width: 64px; height: 64px;
            margin: 0 auto 14px;
            padding: 3px;
            border-radius: 50%;
            background: conic-gradient(from 210deg, #7fd07d, #e4f2d6, #ff8e9a, #e5505c, #7fd07d);
            box-shadow: 0 8px 20px rgba(229, 80, 92, .22);
        }

        .seal-in {
            position: relative;
            width: 100%; height: 100%;
            overflow: hidden;
            border-radius: 50%;
            background: #fff;
        }

        .seal-in svg, .seal-in img {
            position: absolute; inset: 0;
            width: 100%; height: 100%;
        }

        .seal-in img { object-fit: cover; }

        .brand {
            font-size: 11.5px;
            font-weight: 800;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--brand-d);
        }

        h1 {
            margin: 6px 0 2px;
            font-size: clamp(26px, 5.5vw, 34px);
            font-weight: 800;
            letter-spacing: -.02em;
            color: var(--ink);
        }

        .subtitle { margin: 0; color: var(--muted); font-size: 14.5px; }

        .clock-wrap {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 16px;
            padding: 8px 16px 8px 14px;
            border: 1px solid var(--line);
            border-radius: 999px;
            background: rgba(255, 255, 255, .85);
            box-shadow: 0 6px 18px rgba(120, 40, 55, .07);
        }

        .live-dot {
            width: 9px; height: 9px;
            border-radius: 50%;
            background: var(--green);
            animation: pulse 2s ease-out infinite;
        }

        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(79, 174, 78, .55); }
            70%  { box-shadow: 0 0 0 8px rgba(79, 174, 78, 0); }
            100% { box-shadow: 0 0 0 0 rgba(79, 174, 78, 0); }
        }

        .clock {
            font-size: 20px;
            font-weight: 800;
            font-variant-numeric: tabular-nums;
            letter-spacing: .01em;
            color: var(--ink);
        }

        .clock-date {
            padding-left: 10px;
            border-left: 1px solid var(--line-strong);
            font-size: 12.5px;
            font-weight: 700;
            color: var(--muted);
        }

        /* ---------------- Card ---------------- */
        .card {
            position: relative;
            overflow: hidden;
            background: var(--sheet);
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: clamp(22px, 5vw, 32px);
            box-shadow: 0 2px 4px rgba(120, 40, 55, .04), 0 22px 50px rgba(120, 40, 55, .09);
        }

        .card::before {
            content: '';
            position: absolute; left: 0; right: 0; top: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--brand), var(--pink) 50%, var(--green));
        }

        /* ---------------- Stepper ---------------- */
        .stepper {
            display: flex;
            align-items: center;
            list-style: none;
            margin: 0 0 24px;
            padding: 0;
        }

        .stepper li {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--faint);
            font-size: 12.5px;
            font-weight: 800;
            white-space: nowrap;
        }

        .stepper li + li { flex: 1; }

        .stepper li + li::before {
            content: '';
            flex: 1;
            min-width: 14px;
            height: 2px;
            margin-right: 8px;
            border-radius: 2px;
            background: var(--line);
            transition: background .3s;
        }

        .stepper .num {
            display: grid; place-items: center;
            width: 26px; height: 26px;
            border-radius: 50%;
            background: var(--paper);
            border: 1.5px solid var(--line-strong);
            font-size: 12px;
            transition: background .25s, border-color .25s, color .25s;
        }

        .stepper li.is-current { color: var(--ink); }
        .stepper li.is-current .num { background: var(--brand); border-color: var(--brand); color: #fff; box-shadow: 0 0 0 4px var(--brand-soft); }

        .stepper li.is-done { color: var(--green-d); }
        .stepper li.is-done .num { background: var(--green); border-color: var(--green); color: #fff; }
        .stepper li.is-done + li::before { background: var(--green); }

        /* ---------------- Steps ---------------- */
        .step { display: none; }
        .step.active { display: block; animation: stepIn .28s ease-out; }

        @keyframes stepIn {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: none; }
        }

        .step-title {
            margin: 0 0 6px;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -.01em;
        }

        .step-description {
            margin: 0 0 22px;
            color: var(--muted);
            font-size: 14.5px;
        }

        .stack { display: grid; gap: 12px; }

        /* ---------------- Identify option tiles ---------------- */
        .option {
            display: flex;
            align-items: center;
            gap: 14px;
            width: 100%;
            padding: 15px 16px;
            border: 1.5px solid var(--line);
            border-radius: 16px;
            background: #fff;
            color: var(--ink);
            text-align: left;
            cursor: pointer;
            transition: border-color .15s, box-shadow .15s, transform .15s, background-color .15s;
        }

        .option:hover {
            border-color: var(--pink);
            background: #fffafb;
            transform: translateY(-1px);
            box-shadow: 0 10px 22px rgba(229, 80, 92, .12);
        }

        .option:active { transform: scale(.99); }

        .option-icon {
            display: grid; place-items: center;
            width: 46px; height: 46px;
            flex-shrink: 0;
            border-radius: 14px;
            background: var(--brand-soft);
            color: var(--brand-d);
        }

        .option-icon .i { width: 22px; height: 22px; }

        .option.is-green:hover { border-color: var(--green); box-shadow: 0 10px 22px rgba(79, 174, 78, .14); }
        .option.is-green .option-icon { background: var(--green-soft); color: var(--green-d); }

        .option-text { flex: 1; min-width: 0; display: flex; flex-direction: column; }
        .option-text strong { font-size: 15.5px; font-weight: 800; line-height: 1.3; }
        .option-text small { margin-top: 1px; color: var(--muted); font-size: 12.5px; font-weight: 600; }

        .option-arrow { color: var(--faint); transition: transform .15s, color .15s; }
        .option:hover .option-arrow { color: var(--brand); transform: translateX(3px); }
        .option.is-green:hover .option-arrow { color: var(--green-d); }

        /* ---------------- Fields ---------------- */
        .field-label {
            display: block;
            margin-bottom: 8px;
            font-size: 13.5px;
            font-weight: 800;
        }

        .field-wrap { position: relative; }

        .field-wrap > .i {
            position: absolute; left: 15px; top: 50%;
            transform: translateY(-50%);
            color: var(--faint);
            pointer-events: none;
            transition: color .15s;
        }

        .field-wrap:focus-within > .i { color: var(--brand); }

        .field {
            width: 100%;
            min-height: 52px;
            padding: 12px 14px;
            border: 1.5px solid var(--line-strong);
            border-radius: 14px;
            background: #fff;
            color: var(--ink);
            font-weight: 600;
            outline: none;
            transition: border-color .15s, box-shadow .15s;
        }

        .field-wrap .field { padding-left: 46px; }
        .field::placeholder { color: var(--faint); font-weight: 500; }
        .field:hover { border-color: var(--faint); }
        .field:focus { border-color: var(--brand); box-shadow: 0 0 0 4px rgba(229, 80, 92, .14); }

        .pin-input {
            padding: 12px 14px 12px 28px;
            text-align: center;
            font-size: 26px;
            letter-spacing: 14px;
            font-weight: 800;
        }

        .hint {
            display: flex; align-items: center; gap: 7px;
            margin-top: 2px;
            color: var(--muted);
            font-size: 12.5px;
            font-weight: 600;
        }

        .hint .i { width: 15px; height: 15px; color: var(--green-d); }

        /* ---------------- Buttons ---------------- */
        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            min-height: 50px;
            padding: 12px 18px;
            border: 0;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--pink), var(--brand));
            color: #fff;
            font-weight: 800;
            font-size: 15.5px;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(229, 80, 92, .28);
            transition: filter .15s, transform .15s, box-shadow .15s;
        }

        .button:hover { filter: brightness(1.06); box-shadow: 0 12px 26px rgba(229, 80, 92, .38); }
        .button:active { transform: scale(.99); }
        .button:disabled { opacity: .6; cursor: not-allowed; filter: none; }

        .button.secondary {
            background: #fff;
            color: var(--ink);
            border: 1.5px solid var(--line-strong);
            box-shadow: none;
        }

        .button.secondary:hover { background: var(--paper); border-color: var(--faint); filter: none; box-shadow: none; }

        .button.success {
            background: linear-gradient(135deg, #67c466, var(--green-d));
            box-shadow: 0 10px 22px rgba(79, 174, 78, .3);
        }

        .button.success:hover { box-shadow: 0 12px 26px rgba(79, 174, 78, .42); }

        .button.danger {
            background: linear-gradient(135deg, var(--brand), var(--brand-d));
        }

        .button .spin {
            display: inline-block;
            width: 17px; height: 17px;
            border-radius: 50%;
            border: 2.5px solid rgba(255, 255, 255, .4);
            border-top-color: #fff;
            animation: spin .7s linear infinite;
        }

        .button.secondary .spin { border-color: var(--line-strong); border-top-color: var(--brand); }

        @keyframes spin { to { transform: rotate(360deg); } }

        .button-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .button-big { min-height: 84px; flex-direction: column; gap: 6px; font-size: 16px; }
        .button-big .i { width: 24px; height: 24px; }

        /* ---------------- Person card ---------------- */
        .person {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            margin-bottom: 20px;
            border-radius: 16px;
            background: var(--paper);
            border: 1px solid var(--line);
        }

        .avatar {
            display: grid; place-items: center;
            width: 48px; height: 48px;
            flex-shrink: 0;
            border-radius: 50%;
            background: linear-gradient(135deg, #67c466, var(--green-d));
            color: #fff;
            font-size: 17px;
            font-weight: 800;
            box-shadow: 0 0 0 3px #fff, 0 0 0 4px var(--line-strong);
        }

        .person-info { min-width: 0; }

        .person-name {
            font-size: 17px;
            font-weight: 800;
            line-height: 1.3;
            overflow-wrap: anywhere;
        }

        .person-position { margin-top: 1px; color: var(--muted); font-size: 13.5px; font-weight: 600; }

        /* ---------------- Alerts ---------------- */
        .alert {
            display: none;
            padding: 12px 14px;
            margin-bottom: 18px;
            border-radius: 12px;
            border-left: 4px solid transparent;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.5;
        }

        .alert.show { display: block; animation: stepIn .25s ease-out; }
        .alert.error { background: #fff1f2; color: #a8242f; border-left-color: var(--brand); }
        .alert.success { background: var(--green-soft); color: var(--green-d); border-left-color: var(--green); }
        .alert.info { background: var(--paper); color: var(--muted); border-left-color: var(--faint); }

        /* ---------------- QR scanner ---------------- */
        .scanner {
            display: none;
            position: relative;
            margin-top: 14px;
            overflow: hidden;
            border-radius: 18px;
            background: #0f1a14;
        }

        .scanner.active { display: block; }

        #cameraVideo {
            display: block;
            width: 100%;
            max-height: 360px;
            object-fit: cover;
        }

        .scan-frame {
            position: absolute;
            top: 50%; left: 50%;
            width: 58%; aspect-ratio: 1;
            transform: translate(-50%, -62%);
            border: 2px solid rgba(255, 255, 255, .9);
            border-radius: 20px;
            box-shadow: 0 0 0 999px rgba(15, 26, 20, .42);
            overflow: hidden;
            pointer-events: none;
        }

        .scan-frame::after {
            content: '';
            position: absolute; left: 0; right: 0; top: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, var(--pink), transparent);
            animation: scanLine 2.2s ease-in-out infinite;
        }

        @keyframes scanLine {
            0%   { top: 4%; }
            50%  { top: 94%; }
            100% { top: 4%; }
        }

        .scanner-note {
            position: absolute; left: 0; right: 0; bottom: 0;
            padding: 22px 14px 12px;
            background: linear-gradient(0deg, rgba(15, 26, 20, .85), transparent);
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            text-align: center;
        }

        /* ---------------- Result ---------------- */
        .result { text-align: center; padding: 10px 0 4px; }

        .result-icon {
            position: relative;
            display: grid; place-items: center;
            width: 78px; height: 78px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: var(--green-soft);
            color: var(--green-d);
        }

        .result-icon::after {
            content: '';
            position: absolute; inset: -9px;
            border-radius: 50%;
            border: 2px solid rgba(79, 174, 78, .3);
            animation: ring 1.8s ease-out infinite;
        }

        .result-icon .i { width: 38px; height: 38px; stroke-width: 2.6; }

        @keyframes ring {
            0%   { transform: scale(.85); opacity: 1; }
            100% { transform: scale(1.25); opacity: 0; }
        }

        .footer {
            padding: 20px 5px 6px;
            color: var(--faint);
            font-size: 12px;
            font-weight: 600;
            text-align: center;
        }

        @media (max-width: 480px) {
            .page { padding-top: 22px; }
            .button-row { grid-template-columns: 1fr; }
            .card { border-radius: 20px; }
            .clock-date { display: none; }
            .stepper li .label { display: none; }
            .stepper li.is-current .label { display: inline; }
        }

        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { animation: none !important; transition: none !important; }
        }
    </style>
</head>

<body>
<div class="page">
    <header class="header">
        <div class="seal">
            <div class="seal-in">
                {{-- Watermelon fallback: shows if the logo cannot load offline --}}
                <svg viewBox="0 0 40 40" aria-hidden="true">
                    <rect width="40" height="40" fill="#fff"/>
                    <path d="M4 17a16 16 0 0 0 32 0z" fill="#4fae4e"/>
                    <path d="M7.4 17a12.6 12.6 0 0 0 25.2 0z" fill="#e4f2d6"/>
                    <path d="M9.6 17a10.4 10.4 0 0 0 20.8 0z" fill="#ef6b78"/>
                    <ellipse cx="15.5" cy="22" rx="1.1" ry="1.9" transform="rotate(-20 15.5 22)" fill="#2a1a1d"/>
                    <ellipse cx="20" cy="25" rx="1.1" ry="1.9" fill="#2a1a1d"/>
                    <ellipse cx="24.5" cy="22" rx="1.1" ry="1.9" transform="rotate(20 24.5 22)" fill="#2a1a1d"/>
                </svg>
                <img src="{{ asset('icon.png') }}" alt="" width="64" height="64" onerror="this.remove()">
            </div>
        </div>

        <div class="brand">Barangay Poblacion · Bani, Pangasinan</div>
        <h1>Attendance Kiosk</h1>
        <p class="subtitle">Personnel Attendance Monitoring</p>

        <div class="clock-wrap" role="timer" aria-label="Current time">
            <span class="live-dot" aria-hidden="true"></span>
            <span class="clock" id="clock">--:--:--</span>
            <span class="clock-date" id="clockDate"></span>
        </div>
    </header>

    <main class="card">

        {{-- Progress --}}
        <ol class="stepper" id="stepper" aria-label="Attendance steps">
            <li data-step="1" class="is-current"><span class="num">1</span><span class="label">Identify</span></li>
            <li data-step="2"><span class="num">2</span><span class="label">Verify</span></li>
            <li data-step="3"><span class="num">3</span><span class="label">Record</span></li>
        </ol>

        <div id="message" class="alert" role="alert"></div>

        <!-- STEP 1: IDENTIFICATION -->
        <section id="stepIdentify" class="step active">
            <h2 class="step-title">Identify Yourself</h2>
            <p class="step-description">
                Choose how you want to identify yourself. Your attendance will not
                be recorded until you verify your PIN and select an attendance action.
            </p>

            <div class="stack">
                <button type="button" class="option" id="manualButton">
                    <span class="option-icon">
                        <svg class="i" aria-hidden="true" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2.5"/><circle cx="9" cy="11" r="2"/><path d="M6 16a3.2 3.2 0 0 1 6 0M14.5 10h4M14.5 14h3"/></svg>
                    </span>
                    <span class="option-text">
                        <strong>Enter Personnel ID</strong>
                        <small>Type your ID number</small>
                    </span>
                    <svg class="i option-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="m9 6 6 6-6 6"/></svg>
                </button>

                <button type="button" class="option is-green" id="scanButton">
                    <span class="option-icon">
                        <svg class="i" aria-hidden="true" viewBox="0 0 24 24"><path d="M4 8V6a2 2 0 0 1 2-2h2M16 4h2a2 2 0 0 1 2 2v2M20 16v2a2 2 0 0 1-2 2h-2M8 20H6a2 2 0 0 1-2-2v-2"/><path d="M8 12h8"/></svg>
                    </span>
                    <span class="option-text">
                        <strong>Scan QR Code</strong>
                        <small>Use the camera</small>
                    </span>
                    <svg class="i option-arrow" aria-hidden="true" viewBox="0 0 24 24"><path d="m9 6 6 6-6 6"/></svg>
                </button>
            </div>

            <div id="manualPanel" class="hidden" style="margin-top:22px">
                <label class="field-label" for="personnelId">Personnel ID</label>
                <form id="manualForm" class="stack">
                    <div class="field-wrap">
                        <svg class="i" aria-hidden="true" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/></svg>
                        <input
                            class="field"
                            id="personnelId"
                            name="personnel_id"
                            type="text"
                            maxlength="50"
                            autocomplete="off"
                            placeholder="Example: KAWANI-001"
                            required
                        >
                    </div>
                    <button type="submit" class="button" id="identifyButton">
                        <span>Continue</span>
                        <svg class="i" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </button>
                </form>
            </div>

            <div id="scanner" class="scanner">
                <video id="cameraVideo" autoplay muted playsinline></video>
                <div class="scan-frame" aria-hidden="true"></div>
                <div class="scanner-note" id="scannerNote">
                    Point the camera at your personnel QR code.
                </div>
            </div>

            <button type="button" class="button secondary hidden"
                    id="stopScanButton" style="margin-top:12px">
                Stop Camera
            </button>
        </section>

        <!-- STEP 2: PIN -->
        <section id="stepPin" class="step">
            <h2 class="step-title">Verify Your PIN</h2>
            <p class="step-description">
                Enter your personal six-digit PIN to continue.
            </p>

            <div class="person">
                <div class="avatar" id="pinAvatar" aria-hidden="true"></div>
                <div class="person-info">
                    <div class="person-name" id="pinPersonName"></div>
                    <div class="person-position" id="pinPersonPosition"></div>
                </div>
            </div>

            <form id="pinForm" class="stack">
                <label class="field-label" for="pin">Six-digit PIN</label>
                <input
                    class="field pin-input"
                    id="pin"
                    name="pin"
                    type="password"
                    inputmode="numeric"
                    pattern="[0-9]{6}"
                    minlength="6"
                    maxlength="6"
                    autocomplete="off"
                    placeholder="••••••"
                    required
                >
                <p class="hint">
                    <svg class="i" aria-hidden="true" viewBox="0 0 24 24"><rect x="5" y="11" width="14" height="9" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                    Never share your PIN with anyone.
                </p>
                <button type="submit" class="button" id="verifyPinButton">
                    <span>Verify PIN</span>
                    <svg class="i" aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
                <button type="button" class="button secondary" id="backToIdentify">
                    Back
                </button>
            </form>
        </section>

        <!-- STEP 3: ATTENDANCE ACTION -->
        <section id="stepAttendance" class="step">
            <h2 class="step-title">Record Attendance</h2>
            <p class="step-description">
                Select the correct action. Attendance is recorded only after
                you press one of the buttons below.
            </p>

            <div class="person">
                <div class="avatar" id="attendanceAvatar" aria-hidden="true"></div>
                <div class="person-info">
                    <div class="person-name" id="attendancePersonName"></div>
                    <div class="person-position" id="attendancePersonPosition"></div>
                </div>
            </div>

            <div class="button-row">
                <button type="button" class="button success button-big" id="timeInButton">
                    <svg class="i" aria-hidden="true" viewBox="0 0 24 24"><path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5M14 8l4 4-4 4M18 12H8"/></svg>
                    <span>Time In</span>
                </button>
                <button type="button" class="button danger button-big" id="timeOutButton">
                    <svg class="i" aria-hidden="true" viewBox="0 0 24 24"><path d="M10 4H5a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h5M16 8l4 4-4 4M20 12H9"/></svg>
                    <span>Time Out</span>
                </button>
            </div>

            <button type="button" class="button secondary"
                    id="cancelAttendance" style="margin-top:13px">
                Cancel
            </button>
        </section>

        <!-- STEP 4: RESULT -->
        <section id="stepResult" class="step">
            <div class="result">
                <div class="result-icon">
                    <svg class="i" aria-hidden="true" viewBox="0 0 24 24"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>
                </div>
                <h2 class="step-title" id="resultTitle">Attendance Recorded</h2>
                <p class="step-description" id="resultMessage"></p>
                <button type="button" class="button" id="startAgain">
                    Back to Kiosk
                </button>
            </div>
        </section>
    </main>

    <footer class="footer">
        e-Poblacion · Barangay Record Management System
    </footer>
</div>

<script>
(() => {
    'use strict';

    const endpoints = {
        identify: @json(url('/attendance/identify')),
        verifyPin: @json(url('/attendance/verify-pin')),
        record: @json(url('/attendance/record'))
    };

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const elements = {
        clock: document.getElementById('clock'),
        clockDate: document.getElementById('clockDate'),
        message: document.getElementById('message'),
        manualButton: document.getElementById('manualButton'),
        scanButton: document.getElementById('scanButton'),
        manualPanel: document.getElementById('manualPanel'),
        manualForm: document.getElementById('manualForm'),
        personnelId: document.getElementById('personnelId'),
        identifyButton: document.getElementById('identifyButton'),
        scanner: document.getElementById('scanner'),
        cameraVideo: document.getElementById('cameraVideo'),
        scannerNote: document.getElementById('scannerNote'),
        stopScanButton: document.getElementById('stopScanButton'),
        pinForm: document.getElementById('pinForm'),
        pin: document.getElementById('pin'),
        verifyPinButton: document.getElementById('verifyPinButton'),
        pinAvatar: document.getElementById('pinAvatar'),
        pinPersonName: document.getElementById('pinPersonName'),
        pinPersonPosition: document.getElementById('pinPersonPosition'),
        attendanceAvatar: document.getElementById('attendanceAvatar'),
        attendancePersonName: document.getElementById('attendancePersonName'),
        attendancePersonPosition: document.getElementById('attendancePersonPosition'),
        timeInButton: document.getElementById('timeInButton'),
        timeOutButton: document.getElementById('timeOutButton'),
        resultTitle: document.getElementById('resultTitle'),
        resultMessage: document.getElementById('resultMessage')
    };

    const stepNumbers = { stepIdentify: 1, stepPin: 2, stepAttendance: 3, stepResult: 4 };

    let cameraStream = null;
    let scanActive = false;
    let currentPersonnel = null;
    let busy = false;

    function initials(name) {
        const letters = String(name || '')
            .trim()
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map(part => part.charAt(0).toUpperCase())
            .join('');

        return letters || '?';
    }

    function showStep(stepId) {
        document.querySelectorAll('.step').forEach(step => {
            step.classList.toggle('active', step.id === stepId);
        });

        // Progress indicator: the result screen marks every step as done.
        const now = stepNumbers[stepId] || 1;

        document.querySelectorAll('#stepper li').forEach(item => {
            const n = parseInt(item.dataset.step, 10);

            item.classList.toggle('is-current', now <= 3 && n === now);
            item.classList.toggle('is-done', now === 4 || n < now);
        });
    }

    function showMessage(message, type = 'info') {
        elements.message.textContent = message;
        elements.message.className = `alert show ${type}`;
    }

    function clearMessage() {
        elements.message.textContent = '';
        elements.message.className = 'alert';
    }

    // Keeps the button's icon: the original markup is saved and restored.
    function setBusy(button, state, label) {
        if (!button) return;

        button.disabled = state;

        if (state) {
            button.dataset.originalHtml = button.innerHTML;

            const spinner = document.createElement('span');
            spinner.className = 'spin';
            spinner.setAttribute('aria-hidden', 'true');

            const text = document.createElement('span');
            text.textContent = label || 'Please wait...';

            button.replaceChildren(spinner, text);
        } else if (button.dataset.originalHtml !== undefined) {
            button.innerHTML = button.dataset.originalHtml;
            delete button.dataset.originalHtml;
        }
    }

    async function post(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(payload)
        });

        let data = {};
        try {
            data = await response.json();
        } catch (_) {
            data = {};
        }

        if (!response.ok) {
            const error = new Error(
                data.message || `Request failed (${response.status}).`
            );
            error.status = response.status;
            throw error;
        }

        return data;
    }

    function stopCamera() {
        scanActive = false;

        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }

        elements.cameraVideo.srcObject = null;
        elements.scanner.classList.remove('active');
        elements.stopScanButton.classList.add('hidden');
    }

    function resetKiosk() {
        stopCamera();
        currentPersonnel = null;
        busy = false;

        elements.personnelId.value = '';
        elements.pin.value = '';
        elements.manualPanel.classList.add('hidden');
        elements.manualButton.classList.remove('hidden');
        elements.scanButton.classList.remove('hidden');

        clearMessage();
        showStep('stepIdentify');
    }

    async function identifyPersonnel(method, value) {
        if (busy) return;

        busy = true;
        setBusy(elements.identifyButton, true, 'Checking...');

        try {
            const payload = { method };

            if (method === 'id') {
                payload.personnel_id = value;
            } else {
                payload.qr_token = value;
            }

            const data = await post(endpoints.identify, payload);

            currentPersonnel = data.personnel;

            if (!currentPersonnel) {
                throw new Error('Personnel information was not returned.');
            }

            elements.pinPersonName.textContent = currentPersonnel.name || '';
            elements.pinPersonPosition.textContent = currentPersonnel.position || '';
            elements.pinAvatar.textContent = initials(currentPersonnel.name);
            elements.pin.value = '';

            stopCamera();
            elements.manualPanel.classList.add('hidden');
            elements.manualButton.classList.add('hidden');
            elements.scanButton.classList.add('hidden');

            clearMessage();
            showStep('stepPin');
            elements.pin.focus();
        } catch (error) {
            showMessage(error.message, 'error');

            if (error.status === 419) {
                showMessage('Your kiosk session expired. Please refresh the page and try again.', 'error');
            }
        } finally {
            busy = false;
            setBusy(elements.identifyButton, false);
        }
    }

    async function startCamera() {
        clearMessage();

        if (!('BarcodeDetector' in window)) {
            showMessage(
                'QR scanning is not supported by this browser. Please use Manual Personnel ID instead.',
                'error'
            );
            return;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            showMessage(
                'Camera access is unavailable. Open the kiosk through localhost or a secure HTTPS connection, or use Manual Personnel ID.',
                'error'
            );
            return;
        }

        try {
            const detector = new BarcodeDetector({ formats: ['qr_code'] });

            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: 'environment' },
                audio: false
            });

            elements.cameraVideo.srcObject = cameraStream;
            await elements.cameraVideo.play();

            elements.scanner.classList.add('active');
            elements.stopScanButton.classList.remove('hidden');
            elements.scannerNote.textContent = 'Point the camera at your personnel QR code.';

            scanActive = true;

            const scanFrame = async () => {
                if (!scanActive) return;

                try {
                    if (elements.cameraVideo.readyState >= 2) {
                        const codes = await detector.detect(elements.cameraVideo);

                        if (codes.length && codes[0].rawValue) {
                            const token = codes[0].rawValue.trim();

                            stopCamera();
                            await identifyPersonnel('qr', token);
                            return;
                        }
                    }
                } catch (error) {
                    console.error('QR scan error:', error);
                }

                if (scanActive) {
                    requestAnimationFrame(scanFrame);
                }
            };

            requestAnimationFrame(scanFrame);
        } catch (error) {
            stopCamera();
            showMessage(
                'Unable to start the camera. Check browser camera permission and try again.',
                'error'
            );
        }
    }

    elements.manualButton.addEventListener('click', () => {
        elements.manualPanel.classList.remove('hidden');
        elements.manualButton.classList.add('hidden');
        elements.scanButton.classList.add('hidden');
        elements.personnelId.focus();
        clearMessage();
    });

    elements.scanButton.addEventListener('click', startCamera);
    elements.stopScanButton.addEventListener('click', () => {
        stopCamera();
        elements.manualButton.classList.remove('hidden');
        elements.scanButton.classList.remove('hidden');
    });

    elements.manualForm.addEventListener('submit', async event => {
        event.preventDefault();

        const personnelId = elements.personnelId.value.trim();

        if (!personnelId) {
            showMessage('Please enter your Personnel ID.', 'error');
            return;
        }

        await identifyPersonnel('id', personnelId);
    });

    elements.pinForm.addEventListener('submit', async event => {
        event.preventDefault();

        if (busy) return;

        const pin = elements.pin.value.trim();

        if (!/^\d{6}$/.test(pin)) {
            showMessage('Please enter your complete six-digit PIN.', 'error');
            return;
        }

        busy = true;
        setBusy(elements.verifyPinButton, true, 'Verifying...');

        try {
            const data = await post(endpoints.verifyPin, { pin });

            if (data.personnel) {
                currentPersonnel = data.personnel;
            }

            const name = currentPersonnel?.name || '';
            const position = currentPersonnel?.position || '';

            elements.attendancePersonName.textContent = name;
            elements.attendancePersonPosition.textContent = position;
            elements.attendanceAvatar.textContent = initials(name);

            clearMessage();
            showStep('stepAttendance');
        } catch (error) {
            showMessage(error.message, 'error');

            if (error.status === 419) {
                resetKiosk();
                showMessage('Your kiosk session expired. Please identify yourself again.', 'error');
            }
        } finally {
            busy = false;
            setBusy(elements.verifyPinButton, false);
        }
    });

    async function recordAttendance(action, button) {
        if (busy) return;

        busy = true;
        setBusy(button, true, 'Recording...');

        elements.timeInButton.disabled = true;
        elements.timeOutButton.disabled = true;

        try {
            const data = await post(endpoints.record, { action });

            stopCamera();

            elements.resultTitle.textContent =
                action === 'time_in' ? 'Time In Recorded' : 'Time Out Recorded';

            elements.resultMessage.textContent =
                data.message || 'Your attendance has been recorded successfully.';

            showStep('stepResult');
        } catch (error) {
            showMessage(error.message, 'error');

            if (error.status === 419 || error.status === 401) {
                resetKiosk();
                showMessage('Your kiosk session expired. Please identify yourself again.', 'error');
            }
        } finally {
            busy = false;
            setBusy(button, false);
            elements.timeInButton.disabled = false;
            elements.timeOutButton.disabled = false;
        }
    }

    elements.timeInButton.addEventListener('click', () => {
        recordAttendance('time_in', elements.timeInButton);
    });

    elements.timeOutButton.addEventListener('click', () => {
        recordAttendance('time_out', elements.timeOutButton);
    });

    document.getElementById('backToIdentify').addEventListener('click', resetKiosk);
    document.getElementById('cancelAttendance').addEventListener('click', resetKiosk);
    document.getElementById('startAgain').addEventListener('click', resetKiosk);

    function updateClock() {
        const now = new Date();

        elements.clock.textContent = now.toLocaleTimeString('en-PH', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });

        elements.clockDate.textContent = now.toLocaleDateString('en-PH', {
            weekday: 'short',
            month: 'short',
            day: 'numeric'
        });
    }

    updateClock();
    setInterval(updateClock, 1000);

    window.addEventListener('beforeunload', stopCamera);
})();
</script>
</body>
</html>
