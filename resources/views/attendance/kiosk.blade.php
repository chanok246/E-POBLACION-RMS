<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#f2f6f3">
    <title>Attendance Kiosk | e-Poblacion</title>

    {{-- Follow the system's saved theme (same key as the main layout) before first paint --}}
    <script>
        (function () {
            try {
                var t = localStorage.getItem('barangay-theme');
                if (t === 'dark' || t === 'light') {
                    document.documentElement.setAttribute('data-theme', t);
                }
            } catch (e) {}
        })();
    </script>

    <style>
        /* ------------------------------------------------------------
           Theme tokens
           ------------------------------------------------------------ */
        :root {
            color-scheme: light;
            /* Calm sage-green palette: soft paper background, no pure white/black */
            --brand:#23834e; --brand-dark:#1b6a3f; --brand-soft:#e3f1e8;
            --green:#2a8a55; --green-dark:#1b6a3f; --green-soft:#e3f1e8;
            --out-bg:#e6eff3; --out-fg:#2b5568;
            --ink:#1f2d26; --muted:#5a6c62; --faint:#6f8076;
            --line:#dce7e0; --line-strong:#c6d6cc;
            --paper:#f2f6f3; --surface:#fbfdfb; --surface-2:#f0f5f1;
            --err-bg:#fbefee; --err-fg:#8f2d2d; --err-line:#c4605b;
            --warn-bg:#fdf3dc; --warn-fg:#8a5a0a;
            --chip-bg:rgba(251,253,251,.9);
            --on-brand:#ffffff; --btn-a:#2a8f58; --btn-b:#1f7a48; --btn-shadow:rgba(35,131,78,.22);
            --shadow-sm:0 1px 2px rgba(31,60,45,.05);
            --shadow-lg:0 16px 36px rgba(31,60,45,.07);
            --glow-a:rgba(47,158,95,.07); --glow-b:rgba(130,190,155,.10);
            --font:ui-rounded,'Nunito','Quicksand','Trebuchet MS',system-ui,sans-serif;
        }

        :root[data-theme="dark"] {
            color-scheme: dark;
            --brand:#4fc27f; --brand-dark:#8fe0ae; --brand-soft:rgba(79,194,127,.15);
            --green:#4fc27f; --green-dark:#8fe0ae; --green-soft:rgba(79,194,127,.15);
            --out-bg:rgba(130,185,210,.14); --out-fg:#a8d5ea;
            --ink:#e8f1ec; --muted:#a9bbb1; --faint:#81978b;
            --line:#243b2e; --line-strong:#31503f;
            --paper:#0e1a14; --surface:#14261c; --surface-2:#1a2f24;
            --err-bg:rgba(255,154,154,.12); --err-fg:#ffb4b4; --err-line:#ff9a9a;
            --warn-bg:rgba(251,191,36,.15); --warn-fg:#fbbf24;
            --chip-bg:rgba(20,38,28,.92);
            --on-brand:#06261a; --btn-a:#5fd08e; --btn-b:#43b575; --btn-shadow:rgba(0,0,0,.35);
            --shadow-sm:0 2px 4px rgba(0,0,0,.3);
            --shadow-lg:0 22px 50px rgba(0,0,0,.45);
            --glow-a:rgba(79,194,127,.10); --glow-b:rgba(79,194,127,.06);
        }

        /* ------------------------------------------------------------
           Base
           ------------------------------------------------------------ */
        * { box-sizing:border-box; }
        body {
            margin:0; min-height:100vh; font-family:var(--font); color:var(--ink);
            line-height:1.5; -webkit-font-smoothing:antialiased;
            background:
                radial-gradient(55% 40% at 100% 0%,var(--glow-a),transparent 70%),
                radial-gradient(50% 40% at 0% 100%,var(--glow-b),transparent 70%),
                var(--paper);
            background-attachment:fixed;
        }
        button,input { font:inherit; }
        button:focus-visible,input:focus-visible,a:focus-visible {
            outline:3px solid rgba(35,131,78,.45); outline-offset:3px;
        }
        .hidden { display:none!important; }
        .page { width:min(100% - 28px,540px); margin:auto; padding:30px 0 20px; }

        /* ------------------------------------------------------------
           Header
           ------------------------------------------------------------ */
        .header { text-align:center; margin-bottom:20px; }
        .seal {
            width:64px;height:64px;margin:0 auto 14px;padding:3px;border-radius:50%;
            background:conic-gradient(from 210deg,#7fd07d,#e4f2d6,#9fdcb6,#2a8f58,#7fd07d);
            box-shadow:0 6px 16px rgba(35,131,78,.18);
        }
        .seal-in { position:relative;width:100%;height:100%;overflow:hidden;border-radius:50%;background:#fff; }
        .seal-in svg,.seal-in img { position:absolute;inset:0;width:100%;height:100%; }
        .seal-in img { object-fit:cover; }
        .brand { color:var(--brand-dark);font-size:11.5px;font-weight:800;letter-spacing:.14em;text-transform:uppercase; }
        h1 { margin:6px 0 2px;font-size:clamp(26px,5.5vw,34px);font-weight:800;letter-spacing:-.02em;color:var(--ink); }
        .subtitle { margin:0;color:var(--muted);font-size:14.5px; }

        .clock-wrap {
            display:inline-flex;align-items:center;gap:10px;margin-top:16px;padding:8px 18px;
            border:1px solid var(--line);border-radius:999px;background:var(--chip-bg);
            box-shadow:0 4px 14px rgba(31,60,45,.06);
        }
        .live-dot { width:9px;height:9px;border-radius:50%;background:var(--green);animation:pulse 2s ease-out infinite; }
        @keyframes pulse { 0% { box-shadow:0 0 0 0 rgba(79,174,78,.5); } 70% { box-shadow:0 0 0 8px rgba(79,174,78,0); } }
        .clock { font-size:20px;font-weight:800;font-variant-numeric:tabular-nums;color:var(--ink); }
        .clock-date { padding-left:10px;border-left:1px solid var(--line-strong);color:var(--muted);font-size:12.5px;font-weight:700; }

        /* ------------------------------------------------------------
           Progress steps
           ------------------------------------------------------------ */
        .progress { display:flex;align-items:center;justify-content:center;gap:10px;margin:0 0 16px; }
        .progress-item { display:flex;align-items:center;gap:8px;color:var(--faint);font-size:12.5px;font-weight:800; }
        .progress-num {
            display:grid;place-items:center;width:24px;height:24px;border-radius:50%;
            border:1.5px solid var(--line-strong);font-size:12px;background:var(--surface);
        }
        .progress-item.active { color:var(--brand-dark); }
        .progress-item.active .progress-num { background:var(--brand);border-color:var(--brand);color:var(--on-brand); }
        .progress-item.done { color:var(--green-dark); }
        .progress-item.done .progress-num { background:var(--green);border-color:var(--green);color:var(--on-brand); }
        .progress-bar { width:38px;height:2px;border-radius:2px;background:var(--line-strong); }
        .progress-bar.done { background:var(--green); }

        /* ------------------------------------------------------------
           Card
           ------------------------------------------------------------ */
        .card {
            position:relative;overflow:hidden;padding:clamp(22px,5vw,32px);
            border:1px solid var(--line);border-radius:24px;background:var(--surface);
            box-shadow:var(--shadow-sm),var(--shadow-lg);
        }
        .card::before {
            content:'';position:absolute;top:0;left:0;right:0;height:4px;
            background:linear-gradient(90deg,var(--brand),#7cc79b 50%,var(--green));
        }
        .step { display:none; }
        .step.active { display:block;animation:stepIn .28s ease-out; }
        @keyframes stepIn { from { opacity:0;transform:translateY(8px); } to { opacity:1;transform:none; } }
        .step-title { margin:0 0 6px;font-size:22px;font-weight:800;color:var(--ink); }
        .step-description { margin:0 0 20px;color:var(--muted);font-size:14.5px; }
        .stack { display:grid;gap:12px; }

        /* ------------------------------------------------------------
           Method switch
           ------------------------------------------------------------ */
        .modes {
            display:grid;grid-template-columns:1fr 1fr;gap:6px;padding:5px;margin-bottom:20px;
            border:1px solid var(--line);border-radius:18px;background:var(--surface-2);
        }
        .mode {
            display:flex;align-items:center;justify-content:center;gap:9px;min-height:50px;
            padding:10px 12px;border:0;border-radius:13px;background:transparent;
            color:var(--muted);font-size:14.5px;font-weight:800;cursor:pointer;transition:.18s;
        }
        .mode svg { width:21px;height:21px;flex-shrink:0; }
        .mode:hover { color:var(--ink); }
        .mode.active {
            background:var(--surface);color:var(--brand-dark);
            box-shadow:0 3px 10px rgba(31,60,45,.10),inset 0 0 0 1.5px var(--line-strong);
        }
        #scanButton.active { color:var(--green-dark); }

        /* ------------------------------------------------------------
           Form
           ------------------------------------------------------------ */
        .field-label { display:block;margin-bottom:8px;font-size:13.5px;font-weight:800;color:var(--ink); }
        .field {
            width:100%;min-height:54px;padding:12px 16px;border:1.5px solid var(--line-strong);
            border-radius:14px;background:var(--surface-2);color:var(--ink);
            font-size:16px;font-weight:700;letter-spacing:.02em;outline:none;transition:.15s;
        }
        .field::placeholder { color:var(--faint);font-weight:600; }
        .field:focus { border-color:var(--brand);background:var(--surface);box-shadow:0 0 0 4px rgba(35,131,78,.16); }

        .button {
            display:inline-flex;align-items:center;justify-content:center;gap:9px;width:100%;
            min-height:52px;padding:12px 18px;border:0;border-radius:14px;
            background:linear-gradient(135deg,var(--btn-a),var(--btn-b));color:var(--on-brand);
            font-size:15.5px;font-weight:800;cursor:pointer;
            box-shadow:0 8px 18px var(--btn-shadow);transition:.15s;
        }
        .button:hover { filter:brightness(1.06);transform:translateY(-1px); }
        .button:disabled { opacity:.6;cursor:not-allowed;transform:none; }
        .button.secondary {
            border:1.5px solid var(--line-strong);background:var(--surface);color:var(--ink);box-shadow:none;
        }
        .button.secondary:hover { background:var(--surface-2);filter:none; }
        .button .spin {
            display:inline-block;width:17px;height:17px;border:2.5px solid color-mix(in srgb,currentColor 35%,transparent);
            border-top-color:currentColor;border-radius:50%;animation:spin .7s linear infinite;
        }
        @keyframes spin { to { transform:rotate(360deg); } }

        .alert {
            display:none;padding:12px 14px;margin-bottom:18px;border-left:4px solid transparent;
            border-radius:12px;font-size:14px;font-weight:600;
        }
        .alert.show { display:block;animation:stepIn .25s ease-out; }
        .alert.error { background:var(--err-bg);color:var(--err-fg);border-left-color:var(--err-line); }
        .alert.success { background:var(--green-soft);color:var(--green-dark);border-left-color:var(--green); }
        .alert.info { background:var(--surface-2);color:var(--muted);border-left-color:var(--faint); }

        /* ------------------------------------------------------------
           QR scanner — square viewport, frame centered exactly
           ------------------------------------------------------------ */
        .scanner {
            position:relative;width:100%;max-width:420px;margin:0 auto;aspect-ratio:1/1;
            overflow:hidden;border-radius:22px;background:#0f1a14;isolation:isolate;
        }
        #qrReader {
            position:absolute;inset:0;width:100%;height:100%;border:0!important;
            padding:0!important;background:#0f1a14;z-index:0;
        }
        #qrReader > div,
        #qrReader__scan_region {
            width:100%!important;height:100%!important;min-height:0!important;
            border:0!important;padding:0!important;margin:0!important;
        }
        #qrReader video {
            display:block;position:absolute;inset:0;
            width:100%!important;height:100%!important;min-height:0!important;max-height:none!important;
            object-fit:cover!important;border-radius:0!important;
        }
        /* Hide the library's own overlay so only our centered frame shows */
        #qr-shaded-region,
        #qrReader__dashboard_section,
        #qrReader__header_message,
        #qrReader img { display:none!important; }

        .scan-frame {
            position:absolute;top:50%;left:50%;z-index:2;width:66%;aspect-ratio:1/1;
            transform:translate(-50%,-50%);border-radius:22px;pointer-events:none;
            box-shadow:0 0 0 999px rgba(10,18,14,.52);
        }
        .scan-frame .corner { position:absolute;width:34px;height:34px;border:0 solid #fff; }
        .scan-frame .tl { top:0;left:0;border-top-width:4px;border-left-width:4px;border-top-left-radius:22px; }
        .scan-frame .tr { top:0;right:0;border-top-width:4px;border-right-width:4px;border-top-right-radius:22px; }
        .scan-frame .bl { bottom:0;left:0;border-bottom-width:4px;border-left-width:4px;border-bottom-left-radius:22px; }
        .scan-frame .br { bottom:0;right:0;border-bottom-width:4px;border-right-width:4px;border-bottom-right-radius:22px; }
        .scan-frame .line {
            position:absolute;left:8%;right:8%;top:6%;height:3px;border-radius:3px;
            background:linear-gradient(90deg,transparent,#7fe0a8,transparent);
            animation:scanLine 2.2s ease-in-out infinite;
        }
        @keyframes scanLine { 50% { top:92%; } }
        .scanner-note {
            position:absolute;bottom:0;left:0;right:0;z-index:3;padding:26px 14px 14px;
            background:linear-gradient(0deg,rgba(10,18,14,.88),transparent);
            color:#fff;font-size:13px;font-weight:700;text-align:center;pointer-events:none;
        }

        /* ------------------------------------------------------------
           Result
           ------------------------------------------------------------ */
        .result { text-align:center;padding:4px 0 0; }
        .result-icon {
            display:grid;place-items:center;width:78px;height:78px;margin:0 auto 16px;
            border-radius:50%;background:var(--green-soft);color:var(--green-dark);
            font-size:42px;font-weight:800;box-shadow:0 0 0 8px rgba(35,131,78,.10);
            animation:pop .4s cubic-bezier(.2,1.4,.4,1);
        }
        @keyframes pop { from { transform:scale(.5);opacity:0; } to { transform:none;opacity:1; } }
        .result-icon.duplicate { background:var(--warn-bg);color:var(--warn-fg);box-shadow:0 0 0 8px rgba(220,165,55,.10); }

        .time-grid { display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:20px 0 14px; }
        .time-tile { padding:14px 10px;border-radius:16px;background:var(--green-soft);color:var(--green-dark); }
        .time-tile.timeout { background:var(--out-bg);color:var(--out-fg); }
        .time-tile small { display:block;font-size:11.5px;font-weight:800;letter-spacing:.08em;text-transform:uppercase; }
        .time-tile strong {
            display:block;margin-top:4px;font-size:clamp(20px,6vw,28px);font-weight:900;
            font-variant-numeric:tabular-nums;line-height:1.2;
        }
        .time-tile strong.pending { font-size:15px;font-weight:700;opacity:.8;padding:6px 0; }

        .result-details {
            margin:0 0 18px;padding:6px 16px;border:1px solid var(--line);
            border-radius:16px;background:var(--surface-2);text-align:left;
        }
        .detail-row { display:flex;justify-content:space-between;gap:14px;padding:10px 0;border-bottom:1px solid var(--line);font-size:13.5px; }
        .detail-row:last-child { border-bottom:0; }
        .detail-label { color:var(--muted);font-weight:600; }
        .detail-value { color:var(--ink);font-weight:800;text-align:right;overflow-wrap:anywhere; }

        .auto-return { margin:12px 0 0;color:var(--faint);font-size:12.5px;font-weight:700; }

        .footer { padding:20px 5px 6px;color:var(--faint);font-size:12px;font-weight:600;text-align:center; }

        @media (max-width:480px) {
            .page { padding-top:22px; }
            .card { border-radius:20px; }
            .clock-date { display:none; }
            .mode { font-size:13.5px; }
        }
        @media (prefers-reduced-motion:reduce) {
            *,*::before,*::after { animation:none!important;transition:none!important; }
        }
    </style>
</head>

<body>
<div class="page">
    <header class="header">
        <div class="seal">
            <div class="seal-in">
                <svg viewBox="0 0 40 40" aria-hidden="true">
                    <rect width="40" height="40" fill="#fff"/>
                    <path d="M4 17a16 16 0 0 0 32 0z" fill="#4fae4e"/>
                    <path d="M7.4 17a12.6 12.6 0 0 0 25.2 0z" fill="#e4f2d6"/>
                    <path d="M9.6 17a10.4 10.4 0 0 0 20.8 0z" fill="#8fd6a8"/>
                    <ellipse cx="15.5" cy="22" rx="1.1" ry="1.9" fill="#2a1a1d"/>
                    <ellipse cx="20" cy="25" rx="1.1" ry="1.9" fill="#2a1a1d"/>
                    <ellipse cx="24.5" cy="22" rx="1.1" ry="1.9" fill="#2a1a1d"/>
                </svg>
                <img src="{{ asset('icon.png') }}" alt="" onerror="this.remove()">
            </div>
        </div>

        <div class="brand">Barangay Poblacion · Bani, Pangasinan</div>
        <h1>Attendance Kiosk</h1>
        <p class="subtitle">Personnel Attendance Monitoring</p>

        <div class="clock-wrap" role="timer" aria-label="Current time">
            <span class="live-dot"></span>
            <span class="clock" id="clock">--:--:--</span>
            <span class="clock-date" id="clockDate"></span>
        </div>
    </header>

    <div class="progress" aria-hidden="true">
        <div class="progress-item active" id="progressIdentify">
            <span class="progress-num">1</span><span>Identify</span>
        </div>
        <span class="progress-bar" id="progressBar"></span>
        <div class="progress-item" id="progressDone">
            <span class="progress-num">2</span><span>Confirmation</span>
        </div>
    </div>

    <main class="card">
        <div id="message" class="alert" role="alert"></div>

        <section id="stepIdentify" class="step active">
            <h2 class="step-title">Identify Yourself</h2>
            <p class="step-description">
                Enter your Personnel ID or scan your QR code. The kiosk will
                automatically record Time In or Time Out based on your attendance.
            </p>

            <div class="modes" role="tablist" aria-label="Identification method">
                <button type="button" class="mode active" id="manualButton" role="tab" aria-selected="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect x="3" y="5" width="18" height="14" rx="3"/>
                        <circle cx="9" cy="11" r="2"/>
                        <path d="M6.5 16c.6-1.4 1.6-2 2.5-2s1.9.6 2.5 2M14 10h4M14 14h3"/>
                    </svg>
                    <span>Personnel ID</span>
                </button>

                <button type="button" class="mode" id="scanButton" role="tab" aria-selected="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M4 8V6a2 2 0 0 1 2-2h2M16 4h2a2 2 0 0 1 2 2v2M20 16v2a2 2 0 0 1-2 2h-2M8 20H6a2 2 0 0 1-2-2v-2"/>
                        <rect x="8" y="8" width="3" height="3" rx=".5"/>
                        <rect x="13" y="8" width="3" height="3" rx=".5"/>
                        <rect x="8" y="13" width="3" height="3" rx=".5"/>
                        <path d="M13 13h3v3"/>
                    </svg>
                    <span>Scan QR Code</span>
                </button>
            </div>

            <div id="manualPanel">
                <form id="manualForm" class="stack">
                    <div>
                        <label class="field-label" for="personnelId">Personnel ID</label>
                        <input class="field" id="personnelId" name="personnel_id"
                               type="text" maxlength="50" autocomplete="off"
                               placeholder="Example: KAWANI-001" required>
                    </div>
                    <button type="submit" class="button" id="identifyButton">
                        <span>Submit Personnel ID</span>
                        <span aria-hidden="true">→</span>
                    </button>
                </form>
            </div>

            <div id="scanPanel" class="hidden">
                <div id="scanner" class="scanner">
                    <div id="qrReader"></div>

                    <div class="scan-frame" aria-hidden="true">
                        <span class="corner tl"></span>
                        <span class="corner tr"></span>
                        <span class="corner bl"></span>
                        <span class="corner br"></span>
                        <span class="line"></span>
                    </div>

                    <div class="scanner-note" id="scannerNote">
                        Point the camera at your personnel QR code.
                    </div>
                </div>

                <button type="button" class="button secondary" id="stopScanButton" style="margin-top:14px">
                    Stop Camera
                </button>
            </div>
        </section>

        <section id="stepResult" class="step">
            <div class="result">
                <div class="result-icon" id="resultIcon">✓</div>
                <h2 class="step-title" id="resultTitle">Attendance Recorded!</h2>
                <p class="step-description" id="resultMessage" style="margin-bottom:0">
                    Your attendance has been recorded successfully.
                </p>

                <div class="time-grid">
                    <div class="time-tile">
                        <small>Time In</small>
                        <strong id="resultTimeIn">—</strong>
                    </div>
                    <div class="time-tile timeout">
                        <small>Time Out</small>
                        <strong id="resultTimeOut">Not yet recorded</strong>
                    </div>
                </div>

                <div class="result-details">
                    <div class="detail-row">
                        <span class="detail-label">Personnel Name</span>
                        <span class="detail-value" id="resultName">—</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Personnel ID</span>
                        <span class="detail-value" id="resultPersonnelId">—</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Position</span>
                        <span class="detail-value" id="resultPosition">—</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Date</span>
                        <span class="detail-value" id="resultDate">—</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Attendance Status</span>
                        <span class="detail-value" id="resultStatus">Present</span>
                    </div>
                </div>

                <button type="button" class="button" id="startAgain">Back to Kiosk</button>
                <p class="auto-return" id="autoReturn"></p>
            </div>
        </section>
    </main>

    <footer class="footer">e-Poblacion · Barangay Record Management System</footer>
</div>

<!-- Same QR scanner library used by the Relief Voucher scanner -->
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
(() => {
    'use strict';

    const endpoints = {
        identify: @json(url('/attendance/identify'))
    };

    const AUTO_RETURN_SECONDS = 15;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    const el = {
        clock: document.getElementById('clock'),
        clockDate: document.getElementById('clockDate'),
        message: document.getElementById('message'),
        manualButton: document.getElementById('manualButton'),
        scanButton: document.getElementById('scanButton'),
        manualPanel: document.getElementById('manualPanel'),
        scanPanel: document.getElementById('scanPanel'),
        manualForm: document.getElementById('manualForm'),
        personnelId: document.getElementById('personnelId'),
        identifyButton: document.getElementById('identifyButton'),
        scanner: document.getElementById('scanner'),
        stopScanButton: document.getElementById('stopScanButton'),
        scannerNote: document.getElementById('scannerNote'),
        progressIdentify: document.getElementById('progressIdentify'),
        progressDone: document.getElementById('progressDone'),
        progressBar: document.getElementById('progressBar'),
        resultIcon: document.getElementById('resultIcon'),
        resultTitle: document.getElementById('resultTitle'),
        resultMessage: document.getElementById('resultMessage'),
        resultName: document.getElementById('resultName'),
        resultPersonnelId: document.getElementById('resultPersonnelId'),
        resultPosition: document.getElementById('resultPosition'),
        resultDate: document.getElementById('resultDate'),
        resultTimeIn: document.getElementById('resultTimeIn'),
        resultTimeOut: document.getElementById('resultTimeOut'),
        resultStatus: document.getElementById('resultStatus'),
        autoReturn: document.getElementById('autoReturn')
    };

    let qrScanner = null;
    let cameraRunning = false;
    let busy = false;
    let lastScannedCode = '';
    let lastScannedAt = 0;
    let returnTimer = null;

    /* ---------------------------------------------------------------
       UI helpers
       --------------------------------------------------------------- */
    function showMessage(message, type = 'info') {
        el.message.textContent = message;
        el.message.className = `alert show ${type}`;
    }

    function clearMessage() {
        el.message.textContent = '';
        el.message.className = 'alert';
    }

    function showStep(stepId) {
        document.querySelectorAll('.step').forEach(step => {
            step.classList.toggle('active', step.id === stepId);
        });

        const onResult = stepId === 'stepResult';

        el.progressIdentify.classList.toggle('active', !onResult);
        el.progressIdentify.classList.toggle('done', onResult);
        el.progressDone.classList.toggle('active', onResult);
        el.progressBar.classList.toggle('done', onResult);
    }

    function setMode(mode) {
        const manual = mode === 'manual';

        el.manualButton.classList.toggle('active', manual);
        el.scanButton.classList.toggle('active', !manual);
        el.manualButton.setAttribute('aria-selected', manual ? 'true' : 'false');
        el.scanButton.setAttribute('aria-selected', manual ? 'false' : 'true');

        el.manualPanel.classList.toggle('hidden', !manual);
        el.scanPanel.classList.toggle('hidden', manual);
    }

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

    /* ---------------------------------------------------------------
       Camera
       --------------------------------------------------------------- */
    async function stopCamera() {
        if (qrScanner && cameraRunning) {
            try {
                await qrScanner.stop();
            } catch (error) {
                console.warn('Camera stop warning:', error);
            }
        }

        cameraRunning = false;

        if (qrScanner) {
            try {
                await qrScanner.clear();
            } catch (error) {
                // The scanner may already have been cleared.
            }
            qrScanner = null;
        }
    }

    function cameraError(error) {
        const message = String(error?.message || error || '').toLowerCase();

        if (message.includes('permission') || message.includes('notallowed')) {
            return 'Camera access was blocked. Allow camera permission in your browser settings.';
        }

        if (message.includes('notfound') || message.includes('no camera')) {
            return 'No camera was found on this device.';
        }

        if (message.includes('notreadable')) {
            return 'Another application or browser tab is using the camera. Close it and try again.';
        }

        if (message.includes('secure')) {
            return 'Camera access requires HTTPS or localhost.';
        }

        return error?.message || 'Unable to start the camera.';
    }

    async function startCamera() {
        clearMessage();

        if (typeof Html5Qrcode === 'undefined') {
            setMode('manual');
            showMessage(
                'The QR scanner library could not load. Check your internet connection and reload the kiosk.',
                'error'
            );
            return;
        }

        if (!navigator.mediaDevices?.getUserMedia) {
            setMode('manual');
            showMessage(
                'Camera access is unavailable. Open the kiosk using localhost or a secure HTTPS connection.',
                'error'
            );
            return;
        }

        if (cameraRunning || busy) return;

        el.scanButton.disabled = true;
        el.scannerNote.textContent = 'Starting camera...';

        try {
            qrScanner = new Html5Qrcode('qrReader', {
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                experimentalFeatures: {
                    useBarCodeDetectorIfSupported: true
                },
                verbose: false
            });

            // No qrbox: the whole square viewport is scanned, so the QR is
            // always detected wherever it sits inside the visible frame.
            await qrScanner.start(
                { facingMode: 'environment' },
                {
                    fps: 15,
                    aspectRatio: 1
                },
                async decodedText => {
                    const code = String(decodedText || '').trim();

                    if (!code || busy || !cameraRunning) return;

                    const now = Date.now();

                    // Prevent repeated detection of the same QR while it remains in view.
                    if (code === lastScannedCode && now - lastScannedAt < 4000) {
                        return;
                    }

                    lastScannedCode = code;
                    lastScannedAt = now;

                    el.scannerNote.textContent = 'QR detected. Processing attendance...';

                    // Stop the camera before submitting the QR to prevent duplicate scans.
                    cameraRunning = false;

                    try {
                        await qrScanner.stop();
                    } catch (error) {
                        console.warn('Camera stop after QR detection:', error);
                    }

                    const ok = await identifyPersonnel('qr', code);

                    if (!ok) {
                        // Camera is already stopped; return to a usable state with the error visible.
                        await stopCamera();
                        setMode('manual');
                    }
                },
                () => {
                    // No QR detected in this video frame. Continue scanning silently.
                }
            );

            cameraRunning = true;
            el.scannerNote.textContent = 'Point the camera at your personnel QR code.';

        } catch (error) {
            console.error('Attendance camera error:', error);

            try {
                if (qrScanner) {
                    if (cameraRunning) await qrScanner.stop();
                    await qrScanner.clear();
                }
            } catch (_) {}

            qrScanner = null;
            cameraRunning = false;

            setMode('manual');
            showMessage(cameraError(error), 'error');

        } finally {
            el.scanButton.disabled = false;
        }
    }

    /* ---------------------------------------------------------------
       Kiosk flow
       --------------------------------------------------------------- */
    function clearAutoReturn() {
        if (returnTimer) {
            clearInterval(returnTimer);
            returnTimer = null;
        }
        el.autoReturn.textContent = '';
    }

    function startAutoReturn() {
        clearAutoReturn();

        let remaining = AUTO_RETURN_SECONDS;
        el.autoReturn.textContent = `Returning to the kiosk in ${remaining}s`;

        returnTimer = setInterval(() => {
            remaining -= 1;

            if (remaining <= 0) {
                resetKiosk();
                return;
            }

            el.autoReturn.textContent = `Returning to the kiosk in ${remaining}s`;
        }, 1000);
    }

    function resetKiosk() {
        clearAutoReturn();
        stopCamera();
        busy = false;

        el.personnelId.value = '';
        setMode('manual');

        clearMessage();
        showStep('stepIdentify');

        el.personnelId.focus();
    }

    function displayAttendance(data) {
        const personnel = data.personnel || {};
        const attendance = data.attendance || {};
        const action = data.action || 'time_in';

        el.resultName.textContent = personnel.name || '—';
        el.resultPersonnelId.textContent = personnel.personnel_id || '—';
        el.resultPosition.textContent = personnel.position || '—';
        el.resultDate.textContent = attendance.date || '—';
        el.resultTimeIn.textContent = attendance.time_in || '—';
        el.resultStatus.textContent = attendance.status || 'Present';

        el.resultTimeOut.textContent = attendance.time_out || 'Not yet recorded';
        el.resultTimeOut.classList.toggle('pending', !attendance.time_out);

        el.resultIcon.classList.remove('duplicate');

        if (action === 'time_in') {
            el.resultIcon.textContent = '✓';
            el.resultTitle.textContent = 'Time In Recorded Successfully!';
        } else if (action === 'time_out') {
            el.resultIcon.textContent = '✓';
            el.resultTitle.textContent = 'Time Out Recorded Successfully!';
        } else if (action === 'completed') {
            el.resultIcon.textContent = '!';
            el.resultIcon.classList.add('duplicate');
            el.resultTitle.textContent = 'Attendance Already Completed';
        } else {
            el.resultIcon.textContent = '✓';
            el.resultTitle.textContent = 'Attendance Recorded!';
        }

        el.resultMessage.textContent =
            data.message || 'Your attendance details are shown below.';

        showStep('stepResult');
        startAutoReturn();
    }

    // Returns true when attendance was recorded, false otherwise.
    async function identifyPersonnel(method, value, button = null) {
        if (busy) return false;

        busy = true;

        if (button) {
            setBusy(button, true, 'Processing Attendance...');
        }

        clearMessage();

        try {
            const payload = { method };

            if (method === 'manual') {
                payload.personnel_id = value;
            } else {
                payload.qr_token = value;
            }

            const data = await post(endpoints.identify, payload);

            if (!data.personnel || !data.attendance || !data.action) {
                throw new Error(
                    'Attendance details were not returned by the server. Please contact the administrator.'
                );
            }

            await stopCamera();
            displayAttendance(data);

            return true;

        } catch (error) {
            if (error.status === 419) {
                showMessage(
                    'Your kiosk session expired. Please refresh the page and try again.',
                    'error'
                );
            } else {
                showMessage(error.message, 'error');
            }

            return false;

        } finally {
            busy = false;

            if (button) {
                setBusy(button, false);
            }
        }
    }

    /* ---------------------------------------------------------------
       Events
       --------------------------------------------------------------- */
    el.manualButton.addEventListener('click', async () => {
        await stopCamera();
        setMode('manual');
        clearMessage();
        el.personnelId.focus();
    });

    el.scanButton.addEventListener('click', () => {
        setMode('scan');
        startCamera();
    });

    el.stopScanButton.addEventListener('click', async () => {
        await stopCamera();
        setMode('manual');
        clearMessage();
        el.personnelId.focus();
    });

    el.manualForm.addEventListener('submit', async event => {
        event.preventDefault();

        const personnelId = el.personnelId.value.trim();

        if (!personnelId) {
            showMessage('Please enter your Personnel ID.', 'error');
            return;
        }

        await identifyPersonnel('manual', personnelId, el.identifyButton);
    });

    document.getElementById('startAgain').addEventListener('click', resetKiosk);

    /* ---------------------------------------------------------------
       Clock
       --------------------------------------------------------------- */
    function updateClock() {
        const now = new Date();

        el.clock.textContent = now.toLocaleTimeString('en-PH', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        });

        el.clockDate.textContent = now.toLocaleDateString('en-PH', {
            weekday: 'short',
            month: 'short',
            day: 'numeric'
        });
    }

    updateClock();
    setInterval(updateClock, 1000);

    window.addEventListener('beforeunload', () => {
        if (qrScanner && cameraRunning) {
            qrScanner.stop().catch(() => {});
        }
    });
})();
</script>
</body>
</html>
