<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Save Recovery Codes | e-Poblacion</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Unbounded:wght@700;800&display=swap" rel="stylesheet">

    <script>
        /* Apply the saved e-Poblacion theme before first paint (falls back to the OS setting). */
        (function () {
            try {
                var saved = localStorage.getItem('theme') || localStorage.getItem('epoblacion-theme') || localStorage.getItem('e-poblacion-theme');
                var theme = (saved === 'dark' || saved === 'light')
                    ? saved
                    : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                if (!document.documentElement.getAttribute('data-theme')) {
                    document.documentElement.setAttribute('data-theme', theme);
                }
            } catch (e) {
                if (!document.documentElement.getAttribute('data-theme')) {
                    document.documentElement.setAttribute('data-theme', 'light');
                }
            }
        })();
    </script>

    <style>
        :root {
            color-scheme: light;
            --bg: #fdf1f3;
            --bg-2: #fbe3e7;
            --card: #ffffff;
            --field: #ffffff;
            --ink: #3a2a2e;
            --ink-2: #6f565c;
            --faint: #a58f95;
            --line: #f0d0d6;
            --line-strong: #e3b8c0;
            --wash: #fdf1f3;
            --ube: #d63f4c;
            --ube-soft: rgba(229, 80, 92, .11);
            --on-brand: #ffffff;
            --mango: #ffc933;
            --mango-soft: #fff1d6;
            --mango-line: #ecd08a;
            --mango-ink: #7a5000;
            --red: #c9302c;
            --red-soft: #fde3e3;
            --green: #3b8a40;
            --green-soft: #e6f4e4;
            --shadow: 0 2px 4px rgba(120, 40, 55, .05), 0 22px 50px rgba(120, 40, 55, .12);
        }

        html[data-theme="dark"] {
            color-scheme: dark;
            --bg: #171216;
            --bg-2: #211a20;
            --card: #211a20;
            --field: #191419;
            --ink: #f6eff1;
            --ink-2: #c2b3b8;
            --faint: #a18b92;
            --line: #49343b;
            --line-strong: #62444d;
            --wash: #292027;
            --ube: #ff8b96;
            --ube-soft: rgba(255, 127, 139, .14);
            --on-brand: #2b0c12;
            --mango-soft: #3a2c10;
            --mango-line: #6b5420;
            --mango-ink: #ffc15c;
            --red: #ff8a95;
            --red-soft: #401a1d;
            --green: #8fe29a;
            --green-soft: #1f3a27;
            --shadow: none;
        }

        * { box-sizing: border-box; }
        h1, h2, p, ul { margin: 0; padding: 0; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background:
                radial-gradient(900px 500px at 100% -10%, var(--ube-soft), transparent 60%),
                radial-gradient(700px 480px at -10% 110%, var(--ube-soft), transparent 60%),
                var(--bg);
            color: var(--ink);
        }

        button, input { font: inherit; }
        .display { font-family: 'Unbounded', 'Figtree', system-ui, sans-serif; letter-spacing: -.02em; }

        .shell {
            width: 100%;
            max-width: 1180px;
            min-height: 100vh;
            margin: 0 auto;
            padding: 32px 20px;
            display: grid;
            grid-template-columns: minmax(0, 5fr) minmax(0, 7fr);
            gap: 0;
            align-items: stretch;
            align-content: center;
        }

        /* ---------- Left brand panel ---------- */
        .side {
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 28px;
            padding: 36px 32px;
            border-radius: 28px 0 0 28px;
            background: linear-gradient(155deg, #f27984 0%, #e5505c 55%, #c9303f 100%);
            color: #dbcbcb;
            overflow: hidden;
        }
/*Container to ng codes*/
        html[data-theme="dark"] .side {
            background: linear-gradient(155deg, #d5164f 0%, #8c506d 60%, #2a1a22 100%);
            border: 1px solid var(--line);
            border-right: 0;
        }

        .side::before,
        .side::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, .10);
            pointer-events: none;
        }
        .side::before { width: 280px; height: 280px; right: -110px; top: -90px; }
        .side::after { width: 200px; height: 200px; left: -70px; bottom: -70px; }

        .brand { position: relative; z-index: 1; display: flex; align-items: center; gap: 12px; }
        .brand-mark {
            display: grid; place-items: center;
            width: 46px; height: 46px; border-radius: 14px;
            background: rgba(255, 255, 255, .20);
            border: 1px solid rgba(255, 255, 255, .35);
            font-weight: 800; font-size: 22px;
        }
        .brand b { display: block; font-size: 17px; font-weight: 800; }
        .brand span { display: block; margin-top: 1px; font-size: 12px; opacity: .85; }

        .side-copy { position: relative; z-index: 1; }
        .side-copy .eyebrow {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 5px 12px; border-radius: 999px;
            background: rgba(255, 255, 255, .18);
            font-size: 11.5px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase;
        }
        .side-copy h1 { margin-top: 16px; font-size: clamp(24px, 2.6vw, 32px); line-height: 1.2; font-weight: 800; }
        .side-copy p { margin-top: 12px; font-size: 14px; line-height: 1.65; opacity: .92; }

        .progress { position: relative; z-index: 1; display: grid; gap: 12px; list-style: none; }
        .progress li { display: flex; align-items: center; gap: 12px; font-size: 13.5px; font-weight: 600; }
        .progress .dot {
            flex: none; display: grid; place-items: center;
            width: 26px; height: 26px; border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, .55);
            font-size: 12px; font-weight: 800;
        }
        .progress li.done .dot { background: #fff; border-color: #fff; color: #d63f4c; }
        html[data-theme="dark"] .progress li.done .dot { color: #35202a; }
        .progress li.now .dot { border-color: var(--mango); background: var(--mango); color: #4a3200; }
        .progress li.now { font-weight: 800; }

        /* ---------- Right content panel ---------- */
        .main {
            padding: 34px 36px 32px;
            background: var(--card);
            border: 1px solid var(--line);
            border-left: 0;
            border-radius: 0 28px 28px 0;
            box-shadow: var(--shadow);
            min-width: 0;
        }

        .main-top { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
        .main-top h2 { font-size: 19px; font-weight: 800; }
        .theme-toggle {
            display: grid; place-items: center;
            width: 42px; height: 42px; border-radius: 12px;
            border: 1.5px solid var(--line); background: var(--field); color: var(--ink-2);
            cursor: pointer; transition: border-color .15s, color .15s;
        }
        .theme-toggle:hover { border-color: var(--ube); color: var(--ube); }
        .theme-toggle svg { width: 19px; height: 19px; }
        .theme-toggle .moon { display: none; }
        html[data-theme="dark"] .theme-toggle .sun { display: none; }
        html[data-theme="dark"] .theme-toggle .moon { display: block; }

        .warning {
            display: flex; gap: 13px;
            margin-bottom: 20px; padding: 15px 17px;
            background: var(--mango-soft);
            border: 1px solid var(--mango-line);
            border-radius: 16px;
            color: var(--ink);
            font-size: 13.5px; line-height: 1.55;
        }
        .warning svg { flex: none; width: 22px; height: 22px; margin-top: 1px; color: var(--mango-ink); }
        .warning strong { display: block; margin-bottom: 5px; font-size: 14px; }
        .warning ul { padding-left: 18px; display: grid; gap: 2px; color: var(--ink-2); }

        .codes-box {
            padding: 18px;
            background: var(--wash);
            border: 1.5px dashed var(--line-strong);
            border-radius: 20px;
            margin-bottom: 16px;
        }

        .codes-heading {
            display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
            gap: 10px; margin-bottom: 14px;
        }
        .codes-heading b { font-size: 14px; font-weight: 800; }
        .codes-meta { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
        .chip {
            padding: 4px 11px; border-radius: 999px;
            background: var(--ube-soft); color: var(--ube);
            font-size: 11.5px; font-weight: 700;
        }
        .peek {
            display: inline-flex; align-items: center; gap: 6px;
            min-height: 34px; padding: 5px 12px;
            border: 1.5px solid var(--line); border-radius: 999px;
            background: var(--field); color: var(--ink-2);
            font-size: 12.5px; font-weight: 700; cursor: pointer;
        }
        .peek:hover { border-color: var(--ube); color: var(--ube); }

        .codes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(190px, 1fr));
            gap: 10px;
        }

        .code {
            position: relative;
            display: flex; align-items: center; gap: 11px;
            width: 100%; min-width: 0; min-height: 52px;
            padding: 10px 12px;
            background: var(--card);
            border: 1.5px solid var(--line);
            border-radius: 14px;
            color: var(--ink);
            text-align: left;
            cursor: pointer;
            transition: border-color .15s, transform .15s, background .15s;
        }
        .code:hover { border-color: var(--ube); transform: translateY(-1px); }
        .code.copied { border-color: var(--green); background: var(--green-soft); }

        .number {
            display: grid; place-items: center;
            flex: 0 0 26px; height: 26px; border-radius: 8px;
            background: var(--ube-soft); color: var(--ube);
            font-size: 12px; font-weight: 800;
        }
        .code code {
            min-width: 0; flex: 1;
            overflow-wrap: anywhere;
            font-family: ui-monospace, 'Cascadia Mono', Consolas, monospace;
            font-size: 14px; font-weight: 700; letter-spacing: .04em;
            user-select: all;
            transition: filter .15s;
        }
        .code .tag {
            flex: none; font-size: 10.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase;
            color: var(--faint); opacity: 0; transition: opacity .15s;
        }
        .code:hover .tag, .code:focus-visible .tag { opacity: 1; }
        .code.copied .tag { opacity: 1; color: var(--green); }

        .codes-box.is-hidden .code code { filter: blur(7px); user-select: none; }

        .actions {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 22px;
        }
        .action {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            min-height: 48px; padding: 11px 8px;
            border: 1.5px solid var(--line-strong); border-radius: 14px;
            background: var(--card); color: var(--ink);
            font-size: 13.5px; font-weight: 700; cursor: pointer;
            transition: background .15s, border-color .15s, color .15s;
        }
        .action svg { width: 17px; height: 17px; flex: none; }
        .action:hover { background: var(--ube-soft); border-color: var(--ube); color: var(--ube); }
        .action.done { border-color: var(--green); color: var(--green); background: var(--green-soft); }

        .error {
            display: flex; gap: 9px;
            margin-bottom: 14px; padding: 12px 14px;
            border-radius: 12px; border: 1px solid var(--red);
            background: var(--red-soft); color: var(--red);
            font-size: 13.5px; font-weight: 600;
        }

        .confirmation {
            display: flex; align-items: flex-start; gap: 13px;
            padding: 15px 16px; margin-bottom: 14px;
            border: 1.5px solid var(--line); border-radius: 16px;
            cursor: pointer;
            transition: border-color .15s, background .15s;
        }
        .confirmation:hover { border-color: var(--ube); }
        .confirmation:has(input:checked) { border-color: var(--ube); background: var(--ube-soft); }
        .confirmation input { width: 20px; height: 20px; margin: 2px 0 0; flex: 0 0 auto; accent-color: var(--ube); cursor: pointer; }
        .confirmation strong { display: block; font-size: 14px; line-height: 1.5; }
        .confirmation small { display: block; margin-top: 4px; color: var(--ink-2); font-size: 12.5px; line-height: 1.5; }

        .continue {
            display: flex; align-items: center; justify-content: center; gap: 9px;
            width: 100%; min-height: 52px; padding: 13px 18px;
            border: 0; border-radius: 999px;
            background: linear-gradient(135deg, #f27984, #e5505c);
            color: #fff; font-size: 15px; font-weight: 700; cursor: pointer;
            box-shadow: 0 8px 18px rgba(229, 80, 92, .28);
            transition: filter .15s, transform .15s, opacity .15s;
        }
        html[data-theme="dark"] .continue { background: linear-gradient(135deg, #ff8f9a, #ff7f8b); color: #2b0c12; box-shadow: none; }
        .continue:hover:not(:disabled) { filter: brightness(1.06); }
        .continue:active:not(:disabled) { transform: scale(.98); }
        .continue:disabled { opacity: .45; cursor: not-allowed; box-shadow: none; }

        .hint { min-height: 18px; margin-top: 11px; text-align: center; color: var(--ink-2); font-size: 12.5px; }

        .footer { grid-column: 1 / -1; margin-top: 18px; text-align: center; color: var(--ink-2); font-size: 12px; }

        .action:focus-visible, .continue:focus-visible, .code:focus-visible,
        .peek:focus-visible, .theme-toggle:focus-visible, input:focus-visible {
            outline: 3px solid var(--mango);
            outline-offset: 3px;
        }

        @media (max-width: 991.98px) {
            .shell { grid-template-columns: minmax(0, 1fr); max-width: 680px; padding: 20px 14px; }
            .side { border-radius: 24px 24px 0 0; padding: 26px 24px; gap: 20px; }
            html[data-theme="dark"] .side { border-right: 1px solid var(--line); border-bottom: 0; }
            .progress { grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
            .progress li { flex-direction: column; align-items: flex-start; gap: 6px; font-size: 12px; }
            .main { border-left: 1px solid var(--line); border-top: 0; border-radius: 0 0 24px 24px; padding: 26px 22px; }
        }

        @media (max-width: 520px) {
            .main { padding: 22px 15px; }
            .codes-box { padding: 13px; }
            .codes-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
            .code { padding: 9px 8px; gap: 7px; }
            .code code { font-size: 12px; letter-spacing: 0; }
            .code .tag { display: none; }
            .actions { grid-template-columns: minmax(0, 1fr); }
        }

        @media (prefers-reduced-motion: reduce) { *, *::before, *::after { transition: none !important; } }

        @media print {
            body { background: #fff; color: #000; }
            .side, .main-top, .warning, .actions, #finishForm, .footer, .peek, .chip, .code .tag { display: none !important; }
            .shell { display: block; padding: 0; min-height: 0; max-width: none; }
            .main { border: 0; box-shadow: none; padding: 0; background: #fff; }
            .codes-box { background: #fff; border: 0; padding: 0; }
            .codes-box::before {
                content: "e-Poblacion Recovery Codes \2014  Keep private. Each code can be used once.";
                display: block; margin-bottom: 14px; font-size: 14px; font-weight: bold; color: #000;
            }
            .codes-box.is-hidden .code code { filter: none !important; }
            .code { break-inside: avoid; border: 1px solid #777; background: #fff; color: #000; }
            .number { background: #eee; color: #000; }
        }
    </style>
</head>
<body>
    <main class="shell">
        <aside class="side" aria-label="Setup progress">
            <div class="brand">
                <div class="brand-mark display" aria-hidden="true">E</div>
                <div><b class="display">e-Poblacion</b><span>Barangay Record Management System</span></div>
            </div>

            <div class="side-copy">
                <span class="eyebrow">Account Security &middot; Final Step</span>
                <h1 class="display">Keep your way back in.</h1>
                <p>Your authenticator code was verified. Recovery codes let you sign in if you ever lose access to your authenticator app, so save them somewhere safe before finishing.</p>
            </div>

            <ol class="progress">
                <li class="done"><span class="dot">&#10003;</span> Scan QR code</li>
                <li class="done"><span class="dot">&#10003;</span> Verify code</li>
                <li class="now"><span class="dot">3</span> Save recovery codes</li>
            </ol>
        </aside>

        <section class="main" aria-labelledby="pageTitle">
            <div class="main-top">
                <h2 id="pageTitle">Save Your Recovery Codes</h2>
                <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle light or dark theme">
                    <svg class="sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    <svg class="moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>
                </button>
            </div>

            <div class="warning" role="alert">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3 2 20h20L12 3z"/><path d="M12 10v4M12 17h.01"/></svg>
                <div>
                    <strong>Important security notice</strong>
                    <ul>
                        <li>Each recovery code is intended for one-time use.</li>
                        <li>Keep the codes private and store them securely.</li>
                        <li>Do not share them with anyone.</li>
                        <li>Save a copy before continuing.</li>
                    </ul>
                </div>
            </div>

            <section class="codes-box" id="recoveryCodes" aria-label="Your recovery codes">
                <div class="codes-heading">
                    <b>Your recovery codes</b>
                    <div class="codes-meta">
                        <span class="chip">{{ count($recoveryCodes) }} codes &middot; One use each</span>
                        <button type="button" class="peek" id="peekBtn" aria-pressed="false">Hide codes</button>
                    </div>
                </div>

                <div class="codes-grid">
                    @foreach ($recoveryCodes as $code)
                        <button type="button" class="code" title="Click to copy this code">
                            <span class="number">{{ $loop->iteration }}</span>
                            <code>{{ $code }}</code>
                            <span class="tag">Copy</span>
                        </button>
                    @endforeach
                </div>
            </section>

            <div class="actions">
                <button type="button" class="action" id="copyBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="9" y="9" width="12" height="12" rx="2"/><path d="M5 15H4a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v1"/></svg>
                    <span>Copy codes</span>
                </button>
                <button type="button" class="action" id="downloadBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3v12M7 10l5 5 5-5M4 21h16"/></svg>
                    <span>Download</span>
                </button>
                <button type="button" class="action" id="printBtn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 9V3h12v6M6 18H4a1 1 0 0 1-1-1v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a1 1 0 0 1-1 1h-2"/><rect x="6" y="14" width="12" height="7" rx="1"/></svg>
                    <span>Print codes</span>
                </button>
            </div>

            <form method="POST" action="{{ route('two-factor.finish-setup') }}" id="finishForm">
                @csrf

                @if ($errors->any())
                    <div class="error" role="alert">{{ $errors->first() }}</div>
                @endif

                <label class="confirmation" for="savedConfirm">
                    <input type="checkbox" name="saved_confirmation" value="1" id="savedConfirm" required>
                    <span>
                        <strong>I have saved my recovery codes.</strong>
                        <small>I understand that I should keep them secure and that this page may not show them again.</small>
                    </span>
                </label>

                <button type="submit" class="continue" id="finishBtn" disabled>Continue to e-Poblacion &rarr;</button>

                <p class="hint" id="hintLine" aria-live="polite">Save your codes and tick the checkbox to continue.</p>
            </form>
        </section>

        <footer class="footer">e-Poblacion &middot; Secure Barangay Record Management</footer>
    </main>

    <script>
    (function () {
        'use strict';

        const codeElements = document.querySelectorAll('#recoveryCodes code');
        const codeButtons = document.querySelectorAll('#recoveryCodes .code');
        const codesBox = document.getElementById('recoveryCodes');
        const copyBtn = document.getElementById('copyBtn');
        const downloadBtn = document.getElementById('downloadBtn');
        const printBtn = document.getElementById('printBtn');
        const peekBtn = document.getElementById('peekBtn');
        const themeToggle = document.getElementById('themeToggle');
        const checkbox = document.getElementById('savedConfirm');
        const form = document.getElementById('finishForm');
        const finishBtn = document.getElementById('finishBtn');
        const hint = document.getElementById('hintLine');

        const FINISH_LABEL = 'Continue to e-Poblacion →';
        const HINT_IDLE = 'Save your codes and tick the checkbox to continue.';
        let sending = false;

        function getCodes() {
            return Array.from(codeElements, function (element) { return element.textContent.trim(); });
        }

        async function copyText(text) {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(text);
                return;
            }

            const area = document.createElement('textarea');
            area.value = text;
            area.setAttribute('readonly', '');
            area.style.position = 'fixed';
            area.style.opacity = '0';
            document.body.appendChild(area);
            area.select();

            let success = false;
            try {
                success = document.execCommand('copy');
            } finally {
                area.remove();
            }

            if (!success) throw new Error('Copy was not successful.');
        }

        function flash(button, label, restore) {
            const text = button.querySelector('span');
            const original = restore;
            button.classList.add('done');
            if (text) text.textContent = label;
            setTimeout(function () {
                button.classList.remove('done');
                if (text) text.textContent = original;
            }, 2000);
        }

        /* Copy one code by clicking it. */
        codeButtons.forEach(function (button) {
            button.addEventListener('click', async function () {
                const value = button.querySelector('code').textContent.trim();
                const tag = button.querySelector('.tag');
                try {
                    await copyText(value);
                    button.classList.add('copied');
                    if (tag) tag.textContent = 'Copied';
                    setTimeout(function () {
                        button.classList.remove('copied');
                        if (tag) tag.textContent = 'Copy';
                    }, 1500);
                } catch (error) {
                    alert('Copy failed. Please select and copy the code manually.');
                }
            });
        });

        copyBtn.addEventListener('click', async function () {
            try {
                await copyText(getCodes().join('\n'));
                flash(copyBtn, 'Copied!', 'Copy codes');
            } catch (error) {
                alert('Copy failed. Please select and copy the codes manually.');
            }
        });

        downloadBtn.addEventListener('click', function () {
            const lines = [
                'e-Poblacion Recovery Codes',
                'Keep this file private. Each code is intended for one-time use.',
                ''
            ].concat(getCodes().map(function (code, index) { return (index + 1) + '. ' + code; }));

            const blob = new Blob([lines.join('\r\n') + '\r\n'], { type: 'text/plain;charset=utf-8' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'e-poblacion-recovery-codes.txt';
            document.body.appendChild(link);
            link.click();
            link.remove();
            setTimeout(function () { URL.revokeObjectURL(url); }, 1000);

            flash(downloadBtn, 'Downloaded!', 'Download');
        });

        printBtn.addEventListener('click', function () {
            window.print();
        });

        /* Hide / show codes (blur) for shoulder-surfing protection. */
        peekBtn.addEventListener('click', function () {
            const hidden = codesBox.classList.toggle('is-hidden');
            peekBtn.textContent = hidden ? 'Show codes' : 'Hide codes';
            peekBtn.setAttribute('aria-pressed', hidden ? 'true' : 'false');
        });

        /* Theme toggle (persists using the same key the app is likely to read). */
        themeToggle.addEventListener('click', function () {
            const root = document.documentElement;
            const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) { /* storage unavailable */ }
        });

        checkbox.addEventListener('change', function () {
            finishBtn.disabled = !checkbox.checked || sending;
            hint.textContent = checkbox.checked ? 'You may now complete the setup.' : HINT_IDLE;
        });

        form.addEventListener('submit', function (event) {
            if (!checkbox.checked || sending) {
                event.preventDefault();
                return;
            }

            sending = true;
            finishBtn.disabled = true;
            finishBtn.textContent = 'Completing setup…';
        });

        window.addEventListener('pageshow', function () {
            if (!form.contains(document.activeElement)) {
                sending = false;
                finishBtn.textContent = FINISH_LABEL;
                finishBtn.disabled = !checkbox.checked;
            }
        });
    })();
    </script>
</body>
</html>