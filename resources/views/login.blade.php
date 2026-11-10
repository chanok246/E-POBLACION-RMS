<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#06231a">
    <meta name="description" content="e-Poblacion — Record Management System for Barangay Poblacion, Bani, Pangasinan.">

    <title>e-Poblacion | Record Management System</title>

    {{-- Favicon: Barangay Poblacion logo --}}
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Fonts: Space Grotesk (headings), Public Sans (body), JetBrains Mono (tech labels) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700&family=Public+Sans:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    {{-- Lets CSS hide .reveal items only when JS is running --}}
    <script>document.documentElement.classList.add('js');</script>

    <style>
        /* ============================================================
           THEME: semi-watermelon x modern digital
           Watermelon red + rind green, on deep tech-green with a grid.
        ============================================================ */
        :root {
            --ink: #12201a;
            --muted: #566a60;
            --faint: #66796e;
            --line: #e2e8e4;
            --line-strong: #c9d4cd;
            --paper: #f4f8f6;
            --sheet: #ffffff;

            /* Watermelon (primary) */
            --brand: #c92a43;
            --brand-dark: #a31f3a;
            --brand-soft: #fde8ea;
            --pink: #ff7b88;
            --brand-grad: linear-gradient(135deg, #d63a50, #b81f3a); /* white text >= 4.5:1 across the whole gradient */
            --field-border: #7a8e83;      /* input outline >= 3:1 on white */

            /* Rind / leaf (secondary) */
            --leaf: #2f9e5b;
            --leaf-dark: #1f7a43;
            --leaf-soft: #e3f4ea;

            /* Deep tech green */
            --deep: #06231a;
            --deep-2: #0a3326;

            --mint: #5ef0a8;
            --sun: #ffc933;
            --red: #b3261e;
            --red-soft: #fce7e6;

            --font-head: 'Space Grotesk', 'Public Sans', system-ui, sans-serif;
            --font-body: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            --font-mono: 'JetBrains Mono', ui-monospace, 'SFMono-Regular', Menlo, Consolas, monospace;
            --slide-ms: 7000ms;
        }

        *, *::before, *::after { box-sizing: border-box; }
        html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }

        body {
            margin: 0; font-family: var(--font-body); font-size: 15px; line-height: 1.55;
            color: var(--ink); background: var(--paper); -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, h4 { font-family: var(--font-head); letter-spacing: -0.02em; margin: 0; line-height: 1.15; }
        p { margin: 0; }
        a { color: var(--brand-dark); }
        button { font: inherit; }
        img { max-width: 100%; }

        :focus-visible { outline: 3px solid var(--brand); outline-offset: 2px; }

        .skip-link {
            position: absolute; left: 12px; top: -60px; z-index: 100; background: var(--ink); color: #fff;
            padding: 10px 14px; border-radius: 8px; text-decoration: none; font-weight: 600;
        }
        .skip-link:focus { top: 12px; outline-color: var(--mint); }

        /* ============================================================
           STAGE: carousel + login side by side
        ============================================================ */
        .stage {
            display: grid; grid-template-columns: minmax(0, 1fr); gap: 14px; padding: 14px;
            min-height: 100vh; min-height: 100svh;
            background:
                radial-gradient(60% 40% at 100% 0%, rgba(201, 42, 67, .08), transparent 70%),
                radial-gradient(50% 40% at 0% 100%, rgba(47, 158, 91, .08), transparent 70%),
                var(--paper);
        }
        @media (min-width: 960px) {
            .stage { grid-template-columns: minmax(0, 1fr) minmax(440px, 520px); }
        }

        /* ---------- Hero / carousel ---------- */
        .hero {
            position: relative; overflow: hidden; border-radius: 22px; background: var(--deep);
            min-height: 58svh; color: #fff; isolation: isolate;
            border: 1px solid rgba(94, 240, 168, .18);
            box-shadow: 0 0 0 1px rgba(255, 255, 255, .03), 0 24px 60px rgba(6, 35, 26, .35);
        }
        @media (min-width: 960px) { .hero { min-height: calc(100svh - 28px); } }

        /* Digital grid overlay */
        .hero::after {
            content: ''; position: absolute; inset: 0; z-index: 3; pointer-events: none;
            background-image:
                linear-gradient(rgba(255, 255, 255, .06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .06) 1px, transparent 1px);
            background-size: 44px 44px;
            -webkit-mask-image: radial-gradient(120% 90% at 100% 0%, #000 0%, rgba(0, 0, 0, .45) 45%, transparent 75%);
            mask-image: radial-gradient(120% 90% at 100% 0%, #000 0%, rgba(0, 0, 0, .45) 45%, transparent 75%);
        }

        .carousel { position: absolute; inset: 0; }
        .slide { position: absolute; inset: 0; opacity: 0; visibility: hidden; transition: opacity .9s ease, visibility 0s .9s; }
        .slide.is-active { opacity: 1; visibility: visible; transition: opacity .9s ease; z-index: 1; }
        .slide-media { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transform: scale(1.08); }
        .slide.is-active .slide-media { animation: kenburns 14s ease-out forwards; }
        @keyframes kenburns { to { transform: scale(1); } }
        .slide::after {
            content: ''; position: absolute; inset: 0;
            background:
                radial-gradient(55% 45% at 88% 8%, rgba(201, 42, 67, .38), transparent 70%),
                linear-gradient(0deg, rgba(6, 35, 26, .95) 0%, rgba(6, 35, 26, .6) 42%, rgba(6, 35, 26, .18) 75%),
                linear-gradient(180deg, rgba(6, 35, 26, .55) 0%, rgba(6, 35, 26, 0) 24%);
        }

        .slide-content {
            position: absolute; left: 0; right: 0; bottom: 0; z-index: 2;
            padding: 28px 28px 96px; max-width: 760px;
        }
        @media (min-width: 960px) { .slide-content { padding: 48px 48px 116px; } }

        .slide-eyebrow {
            display: inline-flex; align-items: center; gap: 9px; font-family: var(--font-mono); font-size: 12px; font-weight: 500;
            letter-spacing: .04em; text-transform: uppercase;
            padding: 7px 13px; border-radius: 999px; background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .18);
            backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); margin-bottom: 16px;
        }
        .slide-eyebrow::before {
            content: ''; width: 8px; height: 8px; border-radius: 50%; background: var(--mint);
            box-shadow: 0 0 0 0 rgba(94, 240, 168, .6); animation: pulse 2s ease-out infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(94, 240, 168, .6); }
            70% { box-shadow: 0 0 0 9px rgba(94, 240, 168, 0); }
            100% { box-shadow: 0 0 0 0 rgba(94, 240, 168, 0); }
        }
        .slide-title { font-weight: 700; font-size: clamp(32px, 4.6vw, 60px); margin-bottom: 14px; color: #fff; }
        .gradient-e {
            font-style: normal;
            background: linear-gradient(90deg, var(--pink), var(--sun));
            -webkit-background-clip: text; background-clip: text;
            -webkit-text-fill-color: transparent; color: transparent;
        }
        .slide-subtitle { font-size: clamp(15px, 1.35vw, 17px); color: #d3e6de; max-width: 54ch; }

        .slide-content > * { opacity: 0; transform: translateY(14px); }
        .slide.is-active .slide-content > * { animation: rise .7s ease forwards; }
        .slide.is-active .slide-content > *:nth-child(2) { animation-delay: .12s; }
        .slide.is-active .slide-content > *:nth-child(3) { animation-delay: .24s; }
        @keyframes rise { to { opacity: 1; transform: none; } }

        .hero-brand {
            position: absolute; z-index: 5; top: 20px; left: 20px; display: flex; align-items: center; gap: 10px;
            padding: 6px 14px 6px 6px; border-radius: 999px; background: rgba(6, 35, 26, .55);
            border: 1px solid rgba(255, 255, 255, .14);
            backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: #fff; text-decoration: none;
        }
        .hero-brand img { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; background: #fff; }
        .hero-brand span { font-family: var(--font-head); font-weight: 700; font-size: 15px; letter-spacing: -0.01em; }
        @media (min-width: 960px) { .hero-brand { top: 28px; left: 28px; } }

        .carousel-counter {
            position: absolute; z-index: 5; top: 26px; right: 24px; font-family: var(--font-mono); font-weight: 500;
            font-size: 13px; letter-spacing: .06em; color: rgba(255, 255, 255, .75); font-variant-numeric: tabular-nums;
        }
        .carousel-counter b { color: var(--pink); font-size: 18px; font-weight: 700; }

        .hero-controls {
            position: absolute; z-index: 5; left: 0; right: 0; bottom: 0; display: flex; align-items: center; gap: 16px;
            padding: 0 20px 22px;
        }
        @media (min-width: 960px) { .hero-controls { padding: 0 48px 40px; } }

        .carousel-indicators { list-style: none; margin: 0; padding: 0; display: flex; gap: 8px; flex: 1; max-width: 420px; }
        .carousel-indicators li { flex: 1; }
        .carousel-indicators button {
            position: relative; display: block; width: 100%; height: 24px; padding: 0; border: 0; background: none; cursor: pointer;
        }
        .carousel-indicators button::before, .carousel-indicators button::after {
            content: ''; position: absolute; left: 0; top: 10px; height: 4px; border-radius: 999px;
        }
        .carousel-indicators button::before { width: 100%; background: rgba(255, 255, 255, .25); }
        .carousel-indicators button::after { width: 0; background: linear-gradient(90deg, var(--pink), var(--brand)); }
        .carousel-indicators button.is-done::after { width: 100%; }
        .carousel-indicators button.is-active::after { animation: fill var(--slide-ms) linear forwards; }
        .carousel.is-paused ~ .hero-controls .carousel-indicators button.is-active::after { animation-play-state: paused; }
        .hero.no-autoplay .carousel-indicators button.is-active::after { animation: none; width: 100%; }
        @keyframes fill { to { width: 100%; } }

        .carousel-arrows { display: flex; gap: 8px; margin-left: auto; }
        .carousel-nav {
            width: 42px; height: 42px; border-radius: 12px; border: 1.5px solid rgba(255, 255, 255, .3);
            background: rgba(255, 255, 255, .08); color: #fff; display: grid; place-items: center; cursor: pointer;
            backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); transition: background .15s, border-color .15s;
        }
        .carousel-nav:hover { background: rgba(201, 42, 67, .35); border-color: var(--pink); }
        .carousel-nav:focus-visible, .carousel-indicators button:focus-visible, .scroll-indicator:focus-visible { outline-color: var(--mint); }

        .scroll-indicator {
            position: absolute; z-index: 6; right: 16px; bottom: 74px; display: inline-flex; align-items: center; gap: 8px;
            background: var(--brand-grad); color: #fff; border: 0; border-radius: 999px; padding: 9px 14px 9px 16px;
            font-weight: 700; font-size: 13.5px; cursor: pointer; box-shadow: 0 8px 20px rgba(201, 42, 67, .4);
        }
        .scroll-indicator .mouse { display: inline-block; width: 8px; height: 8px; border-right: 2px solid currentColor; border-bottom: 2px solid currentColor; transform: rotate(45deg) translateY(-2px); }
        @media (min-width: 960px) { .scroll-indicator { display: none; } }

        /* ============================================================
           LOGIN
        ============================================================ */
        .login-section { display: grid; place-items: center; padding: 8px 4px 28px; scroll-margin-top: 8px; }
        @media (min-width: 960px) { .login-section { padding: 24px; } }

        .login-card {
            position: relative; overflow: hidden;
            width: 100%; max-width: 440px; background: var(--sheet); border: 1px solid var(--line);
            border-radius: 22px; padding: 34px 28px 26px;
            box-shadow: 0 2px 4px rgba(6, 35, 26, .04), 0 24px 50px rgba(6, 35, 26, .1);
        }
        .login-card::before {
            content: ''; position: absolute; left: 0; right: 0; top: 0; height: 5px;
            background: linear-gradient(90deg, var(--brand), var(--pink) 45%, var(--sun) 70%, var(--leaf));
        }
        @media (min-width: 480px) { .login-card { padding: 40px 36px 30px; } }

        .login-brand { margin-bottom: 24px; }
        .login-logo { width: 54px; height: 54px; border-radius: 14px; object-fit: cover; background: var(--brand-soft); margin-bottom: 16px; display: block; box-shadow: 0 0 0 4px var(--brand-soft); }
        .login-brand h1 { font-size: 30px; font-weight: 700; }
        .tagline { color: var(--brand-dark); font-weight: 700; font-size: 14px; margin-top: 2px; }
        .welcome { color: var(--muted); font-size: 14.5px; margin-top: 10px; }
        .portal-badge {
            display: inline-flex; align-items: center; gap: 8px; margin-top: 14px; padding: 6px 12px; border-radius: 999px;
            background: var(--leaf-soft); color: var(--leaf-dark); font-family: var(--font-mono); font-size: 11.5px; font-weight: 700;
            letter-spacing: .02em; text-transform: uppercase;
        }

        /* Role segmented control */
        .role-selector { margin-bottom: 20px; }
        .role-selector-label { display: block; font-size: 13.5px; font-weight: 600; margin-bottom: 8px; }
        .role-pills { position: relative; display: grid; grid-template-columns: 1fr 1fr; padding: 4px; border-radius: 12px; background: var(--paper); border: 1px solid var(--line); }
        .role-thumb {
            position: absolute; top: 4px; bottom: 4px; left: 4px; width: calc(50% - 4px); border-radius: 9px;
            background: var(--brand-grad); box-shadow: 0 6px 16px rgba(201, 42, 67, .35); transition: transform .28s cubic-bezier(.4, 0, .2, 1);
        }
        .role-pills[data-active="1"] .role-thumb { transform: translateX(100%); }
        .role-pill {
            position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
            min-height: 42px; border: 0; background: none; border-radius: 9px; font-weight: 700; font-size: 14.5px;
            color: var(--muted); cursor: pointer; transition: color .2s;
        }
        .role-pill.is-active { color: #fff; }
        .role-hint { margin-top: 8px; font-size: 13px; color: var(--muted); }
        .role-hint b { color: var(--ink); }

        /* Alerts */
        .alert-error { padding: 12px 14px; border-radius: 10px; background: var(--red-soft); border-left: 5px solid var(--red); margin-bottom: 18px; font-size: 14px; }
        .alert-error strong { display: flex; gap: 8px; align-items: flex-start; color: #8f1f1c; }
        .alert-error ul { margin: 6px 0 0; padding-left: 26px; }
        .alert-error li { color: #7a2523; }
        .alert-success { padding: 12px 14px; border-radius: 10px; background: var(--leaf-soft); border-left: 5px solid var(--leaf); margin-bottom: 18px; font-size: 14px; }
        .alert-success strong { display: flex; gap: 8px; align-items: flex-start; color: var(--leaf-dark); }

        /* Fields */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 13.5px; font-weight: 600; margin-bottom: 6px; }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; top: 50%; transform: translateY(-50%); color: var(--faint); font-size: 17px; }
        .input-icon.left { left: 14px; pointer-events: none; }
        .input-icon.right {
            right: 6px; width: 38px; height: 38px; display: grid; place-items: center; border: 0; background: none;
            border-radius: 8px; cursor: pointer; color: var(--muted);
        }
        .input-icon.right:hover { background: var(--paper); color: var(--ink); }
        .form-input {
            width: 100%; min-height: 48px; padding: 11px 14px; font: inherit; color: var(--ink); background: #fff;
            border: 1.5px solid var(--field-border); border-radius: 12px; transition: border-color .15s, box-shadow .15s;
        }
        .form-input.has-icon-left { padding-left: 42px; }
        .form-input.has-icon-right { padding-right: 48px; }
        .form-input::placeholder { color: var(--faint); }
        .form-input:hover { border-color: var(--ink); }
        .form-input:focus { outline: none; border-color: var(--brand); box-shadow: 0 0 0 4px rgba(201, 42, 67, .15); }
        .input-wrap:focus-within .input-icon.left { color: var(--brand); }
        .form-input.is-invalid { border-color: var(--red); }
        .form-input.is-invalid:focus { box-shadow: 0 0 0 4px var(--red-soft); }
        .field-error { display: block; margin-top: 6px; font-size: 13px; font-weight: 600; color: var(--red); }
        .field-tip { display: none; margin-top: 6px; font-size: 13px; font-weight: 600; color: #8a5a00; }
        .field-tip.is-on { display: flex; gap: 6px; align-items: center; }

        .form-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin: 4px 0 14px; }
        .checkbox-wrap { display: inline-flex; align-items: center; gap: 9px; cursor: pointer; font-size: 14px; font-weight: 500; }
        .checkbox-wrap input { width: 18px; height: 18px; accent-color: var(--brand); cursor: pointer; }

        /* Forgot password: clear, visible, goes straight to the recovery page */
        .forgot-link {
            display: inline-flex; align-items: center; gap: 7px; padding: 7px 12px; border-radius: 999px;
            background: var(--brand-soft); color: var(--brand-dark);
            font-size: 14px; font-weight: 700; text-decoration: none; transition: background .15s, color .15s;
        }
        .forgot-link:hover { background: var(--brand); color: #fff; }
        .forgot-link:focus-visible { outline-offset: 3px; }

        /* Username assistance: separate from password reset and 2FA */
        .assist-line { margin: 0 0 14px; font-size: 13.5px; color: var(--muted); }
        .assist-link {
            border: 0; background: none; padding: 0; font: inherit; font-weight: 700; cursor: pointer;
            color: var(--leaf-dark); text-decoration: underline; text-underline-offset: 3px;
        }
        .assist-link:hover { color: var(--leaf); }

        .forgot-note { display: none; margin: 0 0 16px; padding: 12px 14px; border-radius: 10px; background: var(--paper); border: 1px solid var(--line); font-size: 13.5px; color: var(--muted); line-height: 1.5; }
        .forgot-note.is-on, .forgot-note:target { display: block; }
        .forgot-note h2 { font-size: 14px; margin: 0 0 6px; color: var(--ink); display: flex; align-items: center; gap: 8px; }
        .forgot-note h2 i { color: var(--leaf); }

        /* Recovery panel (email password reset) */
        .recovery-mode-hidden { display: none !important; }
        .recovery-panel { margin: 0 0 22px; }
        .recovery-panel h2 { font-size: 21px; margin-bottom: 8px; }
        .recovery-panel .recovery-intro { color: var(--muted); font-size: 14px; margin-bottom: 18px; }
        .recovery-panel .recovery-back { display: inline-flex; align-items: center; gap: 7px; margin-top: 16px; font-size: 14px; font-weight: 600; text-decoration: none; }
        .recovery-panel .recovery-back:hover { text-decoration: underline; }
        .recovery-panel .recovery-security { margin-top: 16px; color: var(--muted); font-size: 12.5px; }

        .btn-primary {
            width: 100%; min-height: 50px; display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            border: 0; border-radius: 12px; background: var(--brand-grad); color: #fff; font-weight: 700; font-size: 16px; cursor: pointer;
            box-shadow: 0 10px 22px rgba(201, 42, 67, .32);
            transition: filter .15s, transform .15s, box-shadow .15s;
        }
        .btn-primary:hover { filter: brightness(.92); box-shadow: 0 12px 28px rgba(201, 42, 67, .42); }
        .btn-primary:active { transform: scale(.99); }
        .btn-primary i { transition: transform .2s; }
        .btn-primary:hover i { transform: translateX(3px); }
        .btn-primary[disabled] { background: #7f8f86; box-shadow: none; cursor: progress; }
        .btn-primary .spin { display: none; width: 18px; height: 18px; border-radius: 50%; border: 2.5px solid rgba(255, 255, 255, .4); border-top-color: #fff; animation: spin .7s linear infinite; }
        .btn-primary.is-loading .spin { display: inline-block; }
        .btn-primary.is-loading i { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .signup-link { margin-top: 16px; text-align: center; font-size: 12.5px; color: var(--muted); display: flex; gap: 6px; justify-content: center; align-items: center; font-family: var(--font-mono); }
        .signup-link i { color: var(--leaf); }

        /* ============================================================
           TERMS & PRIVACY (one unified section)
        ============================================================ */
        .privacy-section { padding: 72px 20px; scroll-margin-top: 12px; }
        .privacy-inner { max-width: 1180px; margin: 0 auto; }
        .privacy-head { max-width: 640px; margin-bottom: 36px; }
        .section-eyebrow { display: inline-flex; align-items: center; gap: 8px; font-family: var(--font-mono); font-weight: 700; font-size: 12.5px; letter-spacing: .04em; text-transform: uppercase; color: var(--brand-dark); margin-bottom: 10px; }
        .section-eyebrow::before { content: ''; width: 22px; height: 3px; border-radius: 3px; background: linear-gradient(90deg, var(--brand), var(--leaf)); }
        .section-title { font-size: clamp(28px, 3.6vw, 42px); font-weight: 700; margin-bottom: 10px; }
        .section-subtitle { color: var(--muted); font-size: 16px; }

        .privacy-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 14px; margin-bottom: 28px; }
        @media (min-width: 640px) { .privacy-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1000px) { .privacy-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .privacy-card {
            display: flex; flex-direction: column; background: var(--sheet); border: 1px solid var(--line);
            border-radius: 16px; padding: 22px; transition: border-color .15s, transform .15s, box-shadow .15s;
        }
        .privacy-card:hover { border-color: var(--brand); transform: translateY(-3px); box-shadow: 0 14px 30px rgba(201, 42, 67, .12); }
        .pc-icon { width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; background: var(--brand-soft); color: var(--brand-dark); font-size: 20px; margin-bottom: 16px; }
        .privacy-card:nth-child(even) .pc-icon { background: var(--leaf-soft); color: var(--leaf-dark); }
        .privacy-card:nth-child(even):hover { border-color: var(--leaf); box-shadow: 0 14px 30px rgba(47, 158, 91, .12); }
        .privacy-card h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
        .privacy-card p { color: var(--muted); font-size: 14.5px; margin-bottom: 16px; }
        .privacy-card a { margin-top: auto; font-weight: 700; font-size: 14px; text-decoration: none; }
        .privacy-card a:hover { text-decoration: underline; }

        /* Pop-up (modal) for Terms, Privacy Policy, Data Privacy Notice, Contact */
        html.modal-open { overflow: hidden; }
        .legal-modal {
            padding: 0; border: 1px solid var(--line); border-radius: 20px; background: var(--sheet); color: var(--ink);
            width: min(720px, calc(100% - 28px)); max-height: min(86vh, 860px);
            box-shadow: 0 30px 80px rgba(6, 35, 26, .35);
        }
        .legal-modal[open] { display: flex; flex-direction: column; animation: modal-in .18s ease-out; }
        .legal-modal::backdrop { background: rgba(6, 35, 26, .62); backdrop-filter: blur(3px); -webkit-backdrop-filter: blur(3px); }
        @keyframes modal-in { from { opacity: 0; transform: translateY(10px) scale(.985); } to { opacity: 1; transform: none; } }
        .lm-head {
            display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;
            padding: 24px 28px 18px; border-bottom: 1px solid var(--line);
        }
        .lm-head h2 { font-size: 24px; font-weight: 700; }
        .lm-sub { color: var(--brand-dark); font-weight: 700; font-size: 13.5px; margin-top: 4px; }
        .lm-close {
            flex: none; width: 38px; height: 38px; display: grid; place-items: center; border: 1px solid var(--line);
            border-radius: 10px; background: var(--paper); color: var(--muted); cursor: pointer; font-size: 15px;
            transition: background .15s, color .15s, border-color .15s;
        }
        .lm-close:hover { background: var(--brand-soft); color: var(--brand-dark); border-color: var(--brand); }
        .lm-body { padding: 22px 28px; overflow-y: auto; flex: 1; max-width: none; overscroll-behavior: contain; align-content: start; }
        .lm-foot { padding: 14px 28px 20px; border-top: 1px solid var(--line); display: flex; justify-content: flex-end; }
        .lm-done {
            min-height: 42px; padding: 0 22px; border: 0; border-radius: 10px; cursor: pointer; font-weight: 700; font-size: 14.5px;
            color: #fff; background: var(--brand-grad);
        }
        .lm-done:hover { filter: brightness(.92); }
        .lm-label { font-size: 14px; color: var(--muted); }
        .lm-chips { list-style: none; margin: -4px 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: 8px; }
        .lm-chips li { padding: 6px 12px; border-radius: 999px; background: var(--paper); border: 1px solid var(--line); font-size: 13.5px; font-weight: 500; }
        @media (max-width: 480px) {
            .lm-head { padding: 18px 18px 14px; }
            .lm-body { padding: 18px; }
            .lm-foot { padding: 12px 18px 16px; }
            .lm-head h2 { font-size: 20px; }
        }
        @media (prefers-reduced-motion: reduce) { .legal-modal[open] { animation: none; } }

        .pn-body { display: grid; gap: 14px; color: #2c3c36; max-width: 70ch; align-content: start; }
        .pn-body .pn-lead { font-size: 16.5px; color: var(--ink); }
        .pn-body h3 { font-size: 16px; font-weight: 700; color: var(--ink); margin-top: 6px; }
        .pn-body ul.pn-list { list-style: disc; margin: 0; padding-left: 22px; display: grid; gap: 8px; }
        .pn-body ul.pn-list li::marker { color: var(--brand); }
        .pn-disclaimer { padding: 12px 14px; border-radius: 10px; background: #fff3d1; border-left: 5px solid var(--sun); font-size: 13.5px; color: #6b4a00; }

        /* Contact details inside the Contact Barangay panel */
        .contact-list { list-style: none; margin: 0; padding: 0; display: grid; gap: 12px; }
        .contact-list li { display: flex; gap: 12px; align-items: flex-start; padding: 14px; border-radius: 12px; background: var(--paper); border: 1px solid var(--line); }
        .contact-list i { flex: none; width: 36px; height: 36px; display: grid; place-items: center; border-radius: 10px; background: var(--leaf-soft); color: var(--leaf-dark); font-size: 17px; }
        .contact-list b { display: block; color: var(--ink); font-size: 14.5px; }
        .contact-list span { color: var(--muted); font-size: 14px; }

        /* ============================================================
           FOOTER
        ============================================================ */
        .site-footer {
            position: relative; overflow: hidden; background: var(--deep); color: #c5d8d0; padding: 56px 20px 24px;
            border-top: 3px solid transparent;
            border-image: linear-gradient(90deg, var(--brand), var(--pink), var(--sun), var(--leaf)) 1;
        }
        .site-footer::before {
            content: ''; position: absolute; inset: 0; pointer-events: none;
            background-image:
                linear-gradient(rgba(255, 255, 255, .04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255, 255, 255, .04) 1px, transparent 1px);
            background-size: 44px 44px;
            -webkit-mask-image: radial-gradient(90% 100% at 100% 0%, #000, transparent 70%);
            mask-image: radial-gradient(90% 100% at 100% 0%, #000, transparent 70%);
        }
        .footer-grid, .footer-bottom { position: relative; }
        .footer-grid { max-width: 1180px; margin: 0 auto; display: grid; gap: 32px; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 760px) { .footer-grid { grid-template-columns: 1.6fr 1fr 1fr; } }
        .footer-brand h3 { color: #fff; font-size: 26px; font-weight: 700; margin-bottom: 4px; }
        .footer-brand p { color: #9fbbb1; }
        .footer-brand .locale { margin-top: 14px; color: #c5d8d0; }
        .footer-col h4 { color: var(--pink); font-family: var(--font-mono); font-size: 12.5px; letter-spacing: .06em; text-transform: uppercase; margin-bottom: 12px; }
        .footer-col ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
        .footer-col a { color: #c5d8d0; text-decoration: none; font-size: 14.5px; }
        .footer-col a:hover { color: #fff; text-decoration: underline; }
        .site-footer a:focus-visible { outline-color: var(--mint); }
        /* Copyright is centered across the whole footer container (desktop, tablet, mobile). */
        .footer-bottom {
            max-width: 1180px; margin: 36px auto 0; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, .14);
            font-family: var(--font-mono); font-size: 12.5px; color: #9fbbb1;
            text-align: center;
        }

        /* ============================================================
           SCROLL-TO-TOP + REVEAL
        ============================================================ */
        .scroll-top {
            position: fixed; right: 18px; bottom: 18px; z-index: 50; width: 46px; height: 46px; border-radius: 14px; border: 0;
            background: var(--brand-grad); color: #fff; font-size: 18px; display: grid; place-items: center; cursor: pointer;
            box-shadow: 0 10px 24px rgba(201, 42, 67, .4); opacity: 0; visibility: hidden; transform: translateY(10px);
            transition: opacity .2s, transform .2s, visibility .2s;
        }
        .scroll-top.is-visible { opacity: 1; visibility: visible; transform: none; }

        .js .reveal { opacity: 0; transform: translateY(18px); transition: opacity .6s ease, transform .6s ease; }
        .js .reveal.is-in { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) {
            .js .reveal { opacity: 1; transform: none; transition: none; }
            .slide.is-active .slide-media { animation: none; transform: none; }
            .slide-content > * { opacity: 1; transform: none; animation: none !important; }
            .slide, .slide.is-active { transition: none; }
            .slide-eyebrow::before { animation: none; }
        }


        /* Attendance kiosk launcher and full-screen overlay (watermelon theme) */
        .attendance-kiosk-launcher {
            width:100%; display:flex; align-items:center; justify-content:center; gap:9px;
            margin-top:12px; padding:12px 16px; border:1.5px solid var(--line-strong); border-radius:12px;
            background:#fff; color:var(--leaf-dark); font:inherit; font-size:13.5px; font-weight:700;
            cursor:pointer; transition:background .2s ease, border-color .2s ease, color .2s ease, transform .2s ease;
        }
        .attendance-kiosk-launcher:hover { background:var(--brand-soft); border-color:var(--pink); color:var(--brand-dark); transform:translateY(-1px); }
        .attendance-kiosk-launcher:focus-visible, .kiosk-window button:focus-visible { outline:3px solid var(--brand); outline-offset:2px; }
        .attendance-kiosk-overlay {
            position:fixed; inset:0; z-index:10000; display:none; flex-direction:column;
            background:#fdf7f8;
        }
        .attendance-kiosk-overlay.is-open { display:flex; }
        .kiosk-window-bar {
            position:relative; min-height:56px; display:flex; align-items:center; justify-content:space-between; gap:12px;
            padding:8px 18px; background:#fff; color:var(--ink); border-bottom:1px solid var(--line);
            box-shadow:0 6px 18px rgba(120,40,55,.07);
        }
        .kiosk-window-bar::after {
            content:''; position:absolute; left:0; right:0; bottom:-1px; height:3px;
            background:linear-gradient(90deg, var(--brand), var(--pink) 50%, var(--leaf));
        }
        .kiosk-window-title { display:flex; align-items:center; gap:10px; font-size:14px; font-weight:800; letter-spacing:.1px; }
        .kiosk-window-title i {
            width:32px; height:32px; display:grid; place-items:center; border-radius:10px;
            background:var(--brand-soft); color:var(--brand-dark); font-size:15px;
        }
        .kiosk-window-actions { display:flex; align-items:center; gap:8px; }
        .kiosk-window button {
            display:inline-flex; align-items:center; gap:7px; min-height:38px; padding:7px 14px;
            border:1.5px solid var(--line-strong); border-radius:999px; background:#fff; color:var(--ink);
            font:inherit; font-size:12.5px; font-weight:700; cursor:pointer;
            transition:background .15s, border-color .15s, color .15s, filter .15s;
        }
        .kiosk-window button:hover { background:var(--paper); border-color:var(--faint); }
        .kiosk-window button.kiosk-close {
            border-color:transparent; color:#fff;
            background:var(--brand-grad);
            box-shadow:0 6px 14px rgba(201,42,67,.3);
        }
        .kiosk-window button.kiosk-close:hover { filter:brightness(.92); }
        .kiosk-frame { display:block; width:100%; flex:1; min-height:0; border:0; background:#fdf7f8; }
        body.kiosk-overlay-open { overflow:hidden; }
        @media (max-width:480px) {
            .kiosk-window-bar { padding:8px 10px; }
            .kiosk-window-title { font-size:12.5px; }
            .kiosk-window-title i { width:28px; height:28px; }
            .kiosk-window button { padding:6px 11px; font-size:11.5px; }
        }
    </style>
</head>

<body>

<a href="#login" class="skip-link">Skip to sign in</a>

<main class="stage">

    {{-- ============================================================
         HERO / CAROUSEL
    ============================================================= --}}
    <section class="hero" id="top" aria-label="e-Poblacion welcome carousel">

        <a href="#top" class="hero-brand" aria-label="e-Poblacion home">
            <img src="{{ asset('icon.png') }}" alt="">
            <span>e-Poblacion</span>
        </a>

        <div class="carousel-counter" aria-hidden="true"><b id="carouselNow">01</b> / 05</div>

        <div class="carousel" id="carousel" aria-live="off">

            {{-- SLIDE 1 --}}
            <div class="slide is-active" aria-hidden="false" data-slide="0">
                <img class="slide-media" src="{{ asset('images/brgy.png') }}" alt="Barangay Poblacion Hall">
                <div class="slide-content">
                    <span class="slide-eyebrow">Barangay Poblacion · Bani, Pangasinan</span>
                    <h2 class="slide-title">Welcome to <span class="gradient-e">e</span>-Poblacion</h2>
                    <p class="slide-subtitle">
                        A digital Record Management System designed to help Barangay Poblacion
                        organize, manage, and securely maintain official barangay records.
                    </p>
                </div>
            </div>

            {{-- SLIDE 2 --}}
            <div class="slide" aria-hidden="true" data-slide="1">
                <img class="slide-media" src="{{ asset('images/barangay-personnel.jpg') }}" alt="Barangay personnel">
                <div class="slide-content">
                    <span class="slide-eyebrow">Barangay Personnel</span>
                    <h2 class="slide-title">Organized Record Management</h2>
                    <p class="slide-subtitle">
                        Support authorized barangay personnel with organized resident, household,
                        document, complaint, and other official records.
                    </p>
                </div>
            </div>

            {{-- SLIDE 3 --}}
            <div class="slide" aria-hidden="true" data-slide="2">
                <img class="slide-media" src="{{ asset('images/sk-personnel.jpg') }}" alt="Sangguniang Kabataan personnel">
                <div class="slide-content">
                    <span class="slide-eyebrow">Community Records</span>
                    <h2 class="slide-title">Supporting Community Records</h2>
                    <p class="slide-subtitle">
                        Maintain organized records related to community activities, programs,
                        and barangay documentation.
                    </p>
                </div>
            </div>

            {{-- SLIDE 4 --}}
            <div class="slide" aria-hidden="true" data-slide="3">
                <img class="slide-media" src="{{ asset('images/community-event.jpg') }}" alt="Barangay community activity">
                <div class="slide-content">
                    <span class="slide-eyebrow">Digital Records</span>
                    <h2 class="slide-title">Organized. Accessible. Efficient.</h2>
                    <p class="slide-subtitle">
                        Manage residents, households, clearances, certificates, reports, complaints,
                    and relief records through one centralized Record Management System.
                    </p>
                </div>
            </div>

            {{-- SLIDE 5 --}}
            <div class="slide" aria-hidden="true" data-slide="4">
                <img class="slide-media" src="{{ asset('images/local-scenery.jpg') }}" alt="Barangay Poblacion community">
                <div class="slide-content">
                    <span class="slide-eyebrow">Barangay Record Management</span>
                    <h2 class="slide-title">Better Records, Better Service</h2>
                    <p class="slide-subtitle">
                        e-Poblacion helps Barangay Poblacion maintain accurate, organized,
                        accessible, and secure official records.
                    </p>
                </div>
            </div>

        </div>

        <div class="hero-controls">
            {{-- INDICATORS --}}
            <ul class="carousel-indicators" id="carouselIndicators" role="tablist" aria-label="Carousel navigation">
                @for ($i = 0; $i < 5; $i++)
                    <li role="presentation">
                        <button type="button" role="tab"
                                class="{{ $i === 0 ? 'is-active' : '' }}"
                                aria-label="Go to slide {{ $i + 1 }}"
                                aria-selected="{{ $i === 0 ? 'true' : 'false' }}"
                                data-slide-to="{{ $i }}"></button>
                    </li>
                @endfor
            </ul>

            <div class="carousel-arrows">
                <button class="carousel-nav prev" id="carouselPrev" type="button" aria-label="Previous slide">
                    <i class="bi bi-arrow-left"></i>
                </button>
                <button class="carousel-nav next" id="carouselNext" type="button" aria-label="Next slide">
                    <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>

        {{-- SCROLL TO LOGIN (small screens) --}}
        <button class="scroll-indicator" id="scrollIndicator" type="button" aria-label="Scroll to login section">
            <span>Sign in</span>
            <span class="mouse" aria-hidden="true"></span>
        </button>

    </section>


    {{-- ============================================================
         LOGIN SECTION
    ============================================================= --}}
    <section class="login-section" id="login" aria-label="Personnel sign in">

        <div class="login-card reveal">

            {{-- Brand --}}
            <div class="login-brand">
                <img class="login-logo" src="{{ asset('icon.png') }}" alt="e-Poblacion logo">
                <h1>e-Poblacion</h1>
                <p class="tagline">Barangay Record Management System</p>
                <p class="welcome">Official portal for authorized Barangay Poblacion personnel.</p>
                <span class="portal-badge">
                    <i class="bi bi-shield-lock" aria-hidden="true"></i>
                    <span>Authorized Personnel Access Only</span>
                </span>
            </div>

            {{-- Email password recovery: uses the existing login view to avoid a new Blade file. --}}
            @if ($showEmailPasswordRecoveryForm ?? false)
                @php
                    $emailRecoveryStage = $emailPasswordRecoveryStage ?? 'request';
                    $emailRecoveryAddress = $emailPasswordRecoveryEmail ?? old('email', '');
                @endphp

                <div class="recovery-panel" id="emailPasswordRecoveryPanel">
                    <h2>Email Password Recovery</h2>
                    <p class="recovery-intro">
                        Enter the verified email address linked to your existing active Captain or Secretary account.
                        After a password reset, you must sign in normally and complete authenticator-app verification.
                    </p>

                    @if (session('status'))
                        <div class="alert-success" role="status">
                            <strong>
                                <i class="bi bi-info-circle" aria-hidden="true"></i>
                                <span>{{ session('status') }}</span>
                            </strong>
                        </div>
                    @endif

                    @if ($errors->has('email') || $errors->has('verification_code') || $errors->has('password') || $errors->has('password_confirmation'))
                        <div class="alert-error" role="alert">
                            <strong>
                                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                                <span>We could not complete email password recovery.</span>
                            </strong>
                            <ul>
                                @error('email')
                                    <li>{{ $message }}</li>
                                @enderror
                                @error('verification_code')
                                    <li>{{ $message }}</li>
                                @enderror
                                @error('password')
                                    <li>{{ $message }}</li>
                                @enderror
                                @error('password_confirmation')
                                    <li>{{ $message }}</li>
                                @enderror
                            </ul>
                        </div>
                    @endif

                    @if ($emailRecoveryStage === 'verify')
                        <form method="POST" action="{{ route('password-recovery.reset', [], false) }}" id="emailPasswordRecoveryForm">
                            @csrf

                            <div class="form-group">
                                <label class="form-label" for="recovery_email">Verified account email</label>
                                <div class="input-wrap">
                                    <i class="bi bi-envelope input-icon left" aria-hidden="true"></i>
                                    <input type="email"
                                           id="recovery_email"
                                           name="email"
                                           class="form-input has-icon-left {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                           value="{{ $emailRecoveryAddress }}"
                                           autocomplete="email"
                                           maxlength="255"
                                           required
                                           readonly>
                                </div>
                                @error('email')
                                    <span class="field-error" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="verification_code">Six-digit email recovery code</label>
                                <div class="input-wrap">
                                    <i class="bi bi-shield-lock input-icon left" aria-hidden="true"></i>
                                    <input type="text"
                                           id="verification_code"
                                           name="verification_code"
                                           class="form-input has-icon-left {{ $errors->has('verification_code') ? 'is-invalid' : '' }}"
                                           value="{{ old('verification_code') }}"
                                           inputmode="numeric"
                                           pattern="[0-9]{6}"
                                           autocomplete="one-time-code"
                                           minlength="6"
                                           maxlength="6"
                                           required>
                                </div>
                                @error('verification_code')
                                    <span class="field-error" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="email_recovery_password">New password</label>
                                <div class="input-wrap">
                                    <i class="bi bi-lock input-icon left" aria-hidden="true"></i>
                                    <input type="password"
                                           id="email_recovery_password"
                                           name="password"
                                           class="form-input has-icon-left {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                           placeholder="Enter your new password"
                                           autocomplete="new-password"
                                           required>
                                </div>
                                @error('password')
                                    <span class="field-error" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="form-label" for="email_recovery_password_confirmation">Confirm new password</label>
                                <div class="input-wrap">
                                    <i class="bi bi-lock-fill input-icon left" aria-hidden="true"></i>
                                    <input type="password"
                                           id="email_recovery_password_confirmation"
                                           name="password_confirmation"
                                           class="form-input has-icon-left {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                                           placeholder="Re-enter your new password"
                                           autocomplete="new-password"
                                           required>
                                </div>
                                @error('password_confirmation')
                                    <span class="field-error" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn-primary" id="emailPasswordRecoverySubmit">
                                <span>Verify code and reset password</span>
                                <i class="bi bi-arrow-right" aria-hidden="true"></i>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('password-recovery.send-code', [], false) }}" style="margin-top:12px;">
                            @csrf
                            <input type="hidden" name="email" value="{{ $emailRecoveryAddress }}">
                            <button type="submit" class="assist-link">
                                Send a new recovery code
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('password-recovery.send-code', [], false) }}" id="emailPasswordRecoveryForm">
                            @csrf

                            <div class="form-group">
                                <label class="form-label" for="recovery_email">Account email address</label>
                                <div class="input-wrap">
                                    <i class="bi bi-envelope input-icon left" aria-hidden="true"></i>
                                    <input type="email"
                                           id="recovery_email"
                                           name="email"
                                           class="form-input has-icon-left {{ $errors->has('email') ? 'is-invalid' : '' }}"
                                           value="{{ $emailRecoveryAddress }}"
                                           placeholder="Enter the verified email linked to your account"
                                           autocomplete="email"
                                           autocapitalize="none"
                                           maxlength="255"
                                           required>
                                </div>
                                @error('email')
                                    <span class="field-error" role="alert">{{ $message }}</span>
                                @enderror
                            </div>

                            <button type="submit" class="btn-primary" id="emailPasswordRecoverySubmit">
                                <span>Send recovery code</span>
                                <i class="bi bi-envelope-arrow-up" aria-hidden="true"></i>
                            </button>
                        </form>
                    @endif

                    <p class="recovery-security">
                        <i class="bi bi-shield-check" aria-hidden="true"></i>
                        Recovery codes expire. Password recovery does not sign you in or disable two-factor authentication.
                    </p>

                    <a class="recovery-back" href="{{ route('login') }}">
                        <i class="bi bi-arrow-left" aria-hidden="true"></i>
                        Return to normal sign in
                    </a>
                </div>
            @endif

            {{-- Normal login form remains in the page so existing login scripts stay intact. --}}
            <form method="POST" action="{{ route('login.authenticate', [], false) }}" id="loginForm" novalidate
                  class="{{ ($showEmailPasswordRecoveryForm ?? false) ? 'recovery-mode-hidden' : '' }}">
                @csrf

                @php
                    $selectedRole = old('role');

                    if (! in_array($selectedRole, ['captain', 'secretary'], true)) {
                        $selectedRole = 'captain';
                    }
                @endphp

                {{-- Role selector --}}
                <div class="role-selector" role="radiogroup" aria-label="Select your role">
                    <span class="role-selector-label" id="roleLabel">Sign in as</span>

                    <div class="role-pills" id="rolePills" data-active="{{ $selectedRole === 'secretary' ? 1 : 0 }}">
                        <span class="role-thumb" aria-hidden="true"></span>

                        <button type="button"
                                class="role-pill {{ $selectedRole === 'captain' ? 'is-active' : '' }}"
                                data-role="captain" role="radio"
                                aria-checked="{{ $selectedRole === 'captain' ? 'true' : 'false' }}"
                                tabindex="{{ $selectedRole === 'captain' ? '0' : '-1' }}">
                            <i class="bi bi-person-badge" aria-hidden="true"></i>
                            <span>Captain</span>
                        </button>

                        <button type="button"
                                class="role-pill {{ $selectedRole === 'secretary' ? 'is-active' : '' }}"
                                data-role="secretary" role="radio"
                                aria-checked="{{ $selectedRole === 'secretary' ? 'true' : 'false' }}"
                                tabindex="{{ $selectedRole === 'secretary' ? '0' : '-1' }}">
                            <i class="bi bi-journal-text" aria-hidden="true"></i>
                            <span>Secretary</span>
                        </button>
                    </div>

                    <div class="role-hint" aria-live="polite">
                        Signing in as <b id="roleHint">{{ ucfirst($selectedRole) }}</b>
                    </div>

                    <input type="hidden" name="role" id="role" value="{{ $selectedRole }}">
                </div>

                {{-- Success / status messages (e.g. after a completed recovery) --}}
                @if (session('success') || session('status'))
                    <div class="alert-success" role="status">
                        <strong>
                            <i class="bi bi-check-circle" aria-hidden="true"></i>
                            <span>{{ session('success') ?? session('status') }}</span>
                        </strong>
                    </div>
                @endif

                {{-- Validation errors --}}
                @if ($errors->any())
                    <div class="alert-error" role="alert">
                        <strong>
                            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                            <span>Please check your login details.</span>
                        </strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- General login error --}}
                @if (session('login_error'))
                    <div class="alert-error" role="alert">
                        <strong>
                            <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                            <span>{{ session('login_error') }}</span>
                        </strong>
                    </div>
                @endif

                {{-- Username --}}
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <div class="input-wrap">
                        <i class="bi bi-person input-icon left" aria-hidden="true"></i>
                        <input type="text" id="username" name="username"
                               class="form-input has-icon-left {{ $errors->has('username') ? 'is-invalid' : '' }}"
                               value="{{ old('username') }}"
                               placeholder="Enter your username"
                               autocomplete="username"
                               autocapitalize="none"
                               spellcheck="false"
                               maxlength="50"
                               required>
                    </div>
                    @error('username')
                        <span class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap">
                        <i class="bi bi-lock input-icon left" aria-hidden="true"></i>
                        <input type="password" id="password" name="password"
                               class="form-input has-icon-left has-icon-right {{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Enter your password" autocomplete="current-password" required>
                        <button type="button" id="togglePassword" class="input-icon right"
                                aria-label="Show password" aria-pressed="false">
                            <i id="togglePasswordIcon" class="bi bi-eye" aria-hidden="true"></i>
                        </button>
                    </div>
                    <span class="field-tip" id="capsTip" role="status">
                        <i class="bi bi-capslock-fill" aria-hidden="true"></i> Caps Lock is on
                    </span>
                    @error('password')
                        <span class="field-error" role="alert">{{ $message }}</span>
                    @enderror
                </div>

                {{-- Remember / Forgot password --}}
                <div class="form-row">
                    <label class="checkbox-wrap">
                        <input type="checkbox" name="remember" id="remember" value="1">
                        <span>Remember me</span>
                    </label>

                    {{-- Password reset only: goes straight to the existing email password-recovery page. --}}
                    <a class="forgot-link" href="{{ route('password-recovery.show') }}" id="forgotPasswordLink">
                        <i class="bi bi-key" aria-hidden="true"></i>
                        <span>Forgot password?</span>
                    </a>
                </div>

                {{-- Username assistance: separate from password reset and 2FA recovery. --}}
                <p class="assist-line">
                    Forgot your username?
                    <a class="assist-link" href="#username-help" id="usernameHelpLink"
                       aria-expanded="false" aria-controls="username-help">Get help</a>
                </p>

                <div class="forgot-note" id="username-help" role="region" aria-label="Username assistance">
                    <h2><i class="bi bi-person-vcard" aria-hidden="true"></i> Username assistance</h2>
                    <p>Usernames are not shown on this page. Ask the Barangay Poblacion office to confirm your username in person.</p>
                </div>

                {{-- Sign in --}}
                <button type="submit" class="btn-primary" id="signInBtn">
                    <span class="spin" aria-hidden="true"></span>
                    <span id="signInText">Sign in</span>
                    <i class="bi bi-arrow-right" aria-hidden="true"></i>
                </button>

                {{-- Security message --}}
                <p class="signup-link">
                    <i class="bi bi-lock-fill" aria-hidden="true"></i>
                    Authorized Captain and Secretary only.
                </p>
            </form>

            {{-- Separate attendance access; does not submit or alter the RMS login form. --}}
            <button type="button" class="attendance-kiosk-launcher" id="openAttendanceKiosk"
                    aria-haspopup="dialog" aria-controls="attendanceKioskOverlay">
                <i class="bi bi-qr-code-scan" aria-hidden="true"></i>
                <span>Open Attendance Kiosk</span>
                <i class="bi bi-arrow-up-right" aria-hidden="true"></i>
            </button>

        </div>

    </section>

</main>




{{-- Full-screen attendance kiosk overlay. The iframe keeps the kiosk separate from RMS login. --}}
<div class="attendance-kiosk-overlay" id="attendanceKioskOverlay" role="dialog"
     aria-modal="true" aria-label="Attendance Kiosk" aria-hidden="true">
    <div class="kiosk-window-bar">
        <div class="kiosk-window-title"><i class="bi bi-qr-code-scan" aria-hidden="true"></i> Attendance Kiosk</div>
        <div class="kiosk-window-actions">
            <button type="button" id="minimizeAttendanceKiosk" aria-label="Minimize attendance kiosk">
                <i class="bi bi-dash-lg" aria-hidden="true"></i> Minimize
            </button>
            <button type="button" class="kiosk-close" id="closeAttendanceKiosk">
                <i class="bi bi-arrow-left" aria-hidden="true"></i> Back to Login
            </button>
        </div>
    </div>
    <iframe class="kiosk-frame" id="attendanceKioskFrame"
            title="Personnel Attendance Kiosk" loading="lazy"></iframe>
</div>

{{-- ============================================================
     TERMS & PRIVACY (one unified section: 4 cards, each opens a pop-up)
     Pop-ups: #legal-terms, #legal-policy, #legal-notice, #legal-contact
============================================================= --}}
<section class="privacy-section" id="privacy" aria-label="Terms and privacy">
    <div class="privacy-inner">

        <div class="privacy-head">
            <p class="section-eyebrow reveal">Your Privacy Matters</p>
            <h2 class="section-title reveal">Terms &amp; Privacy</h2>
            <p class="section-subtitle reveal">
                We respect your data. Review the policies and notices that govern
                how your information is handled.
            </p>
        </div>

        <div class="privacy-grid">

            <div class="privacy-card reveal">
                <div class="pc-icon"><i class="bi bi-file-text" aria-hidden="true"></i></div>
                <h3>Terms &amp; Conditions</h3>
                <p>The rules and guidelines for using e-Poblacion services and managing official barangay records.</p>
                <a href="#legal-terms" data-legal="terms">Read terms &rarr;</a>
            </div>

            <div class="privacy-card reveal">
                <div class="pc-icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></div>
                <h3>Privacy Policy</h3>
                <p>How e-Poblacion handles and protects personal information contained in barangay records.</p>
                <a href="#legal-policy" data-legal="policy">Read policy &rarr;</a>
            </div>

            <div class="privacy-card reveal">
                <div class="pc-icon"><i class="bi bi-info-circle" aria-hidden="true"></i></div>
                <h3>Data Privacy Notice</h3>
                <p>A plain-language summary of data privacy rights and how personal information is handled within the system.</p>
                <a href="#legal-notice" data-legal="notice">Read notice &rarr;</a>
            </div>

            <div class="privacy-card reveal">
                <div class="pc-icon"><i class="bi bi-telephone" aria-hidden="true"></i></div>
                <h3>Contact Barangay</h3>
                <p>Reach the Barangay Poblacion office for inquiries and assistance.</p>
                <a href="#legal-contact" data-legal="contact">Contact us &rarr;</a>
            </div>

        </div>


        {{-- Pop-up (modal) content: opened by [data-legal] links, cards and footer links. --}}
        <dialog class="legal-modal" id="legal-terms" aria-labelledby="legal-terms-title">
            <div class="lm-head">
                <div>
                    <h2 id="legal-terms-title">Terms &amp; Conditions</h2>
                    <p class="lm-sub">Barangay Poblacion, Bani, Pangasinan</p>
                </div>
                <button type="button" class="lm-close" data-close aria-label="Close Terms &amp; Conditions">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="lm-body pn-body">
                <p class="pn-lead">
                    By signing in to e-Poblacion, you agree to use the system only for official
                    Barangay Poblacion record-management work and to follow these terms.
                </p>

                <ul class="pn-list">
                    <li><strong>Authorized use only.</strong> Access is limited to Captain and Secretary
                        accounts issued by the barangay. Do not use another person's account.</li>
                    <li><strong>Protect your account.</strong> Keep your username, password, and
                        authenticator access confidential, and never share them with anyone.</li>
                    <li><strong>Keep records accurate.</strong> Enter and update resident, household,
                        document, complaint,and relief records accurately and only for
                        legitimate barangay purposes.</li>
                    <li><strong>Respect access limits.</strong> Do not try to view or change records beyond
                        your assigned permissions, and do not copy or disclose records without authority.</li>
                    <li><strong>Keep the system safe.</strong> Do not disrupt the system, bypass its
                        security features, or use it for personal or unlawful purposes.</li>
                    <li><strong>Activity may be recorded.</strong> System activity may be logged to support
                        accountability and the protection of barangay records.</li>
                    <li><strong>Changes to these terms.</strong> The barangay may update these terms when
                        needed. Continued use of the system means you accept the updated terms.</li>
                </ul>

                <p class="pn-disclaimer">
                    These terms are provided for general information and do not constitute a certified
                    legal document. Questions may be raised with the Barangay Poblacion office.
                </p>
            </div>
            <div class="lm-foot">
                <button type="button" class="lm-done" data-close>Close</button>
            </div>
        </dialog>

        <dialog class="legal-modal" id="legal-policy" aria-labelledby="legal-policy-title">
            <div class="lm-head">
                <div>
                    <h2 id="legal-policy-title">Privacy Policy</h2>
                    <p class="lm-sub">Barangay Poblacion, Bani, Pangasinan</p>
                </div>
                <button type="button" class="lm-close" data-close aria-label="Close Privacy Policy">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="lm-body pn-body">
                <p class="pn-lead">
                    e-Poblacion is an internal Barangay Record Management System. It is used by authorized
                    barangay personnel to maintain official records, and it is not an online portal for residents.
                </p>

                <h3>What is handled</h3>
                <p>
                    Personal information needed for barangay record management, such as resident and household
                    details, document requests, reports and complaints, and relief records. The
                    full list of information categories is in the
                    <a href="#legal-notice" data-legal="notice">Data Privacy Notice</a>.
                </p>

                <h3>Why it is handled</h3>
                <p>
                    Information is used only for legitimate barangay purposes: keeping accurate records,
                    processing documents, handling reports and complaints, and supporting disaster response
                    and relief programs.
                </p>

                <h3>How it is protected</h3>
                <ul class="pn-list">
                    <li>Access is restricted to authorized barangay personnel based on their assigned system permissions.</li>
                    <li>Accounts are protected by credentials and authenticator-app verification.</li>
                    <li>Authorized personnel must keep their credentials private and must not share their passwords.</li>
                </ul>

                <h3>Use and disclosure</h3>
                <p>
                    Records are used for official barangay functions and are disclosed only to authorized
                    personnel or where required or permitted by applicable law.
                </p>

                <h3>Your rights</h3>
                <p>
                    You may ask the Barangay Poblacion office about the personal data held about you,
                    including requests for access or correction. See the
                    <a href="#legal-notice" data-legal="notice">Data Privacy Notice</a> and
                    <a href="#legal-contact" data-legal="contact">Contact Barangay</a>.
                </p>

                <p class="pn-disclaimer">
                    This policy is provided for general information and does not constitute a certified legal
                    compliance statement. Formal compliance is verified separately by the barangay.
                </p>
            </div>
            <div class="lm-foot">
                <button type="button" class="lm-done" data-close>Close</button>
            </div>
        </dialog>

        <dialog class="legal-modal" id="legal-notice" aria-labelledby="legal-notice-title">
            <div class="lm-head">
                <div>
                    <h2 id="legal-notice-title">Data Privacy Notice</h2>
                    <p class="lm-sub">Barangay Poblacion, Bani, Pangasinan</p>
                </div>
                <button type="button" class="lm-close" data-close aria-label="Close Data Privacy Notice">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="lm-body pn-body">
                <p class="pn-lead">
                    e-Poblacion collects and processes personal information for legitimate barangay
                    record-management purposes, including resident records, household records, document
                    processing, reports and complaints, and relief-related records.
                </p>

                <p class="lm-label">The information that may be maintained in the system may include:</p>
                <ul class="lm-chips">
                    <li>Full name</li>
                    <li>Address and household details</li>
                    <li>Contact information</li>
                    <li>Records related to barangay services</li>
                    <li>Documents and other supporting information</li>
                    <li>Disaster assessment and relief-related records</li>
                </ul>

                <p>
                    Personal information is collected only for legitimate barangay purposes and is handled in
                    accordance with applicable Philippine data privacy requirements, including the Data Privacy
                    Act of 2012. Access to records is restricted to authorized barangay personnel according to
                    their assigned system permissions.
                </p>

                <p>
                    Authorized personnel should protect their account credentials and must not share their
                    passwords with other individuals.
                </p>

                <p>
                    You have the right to be informed, to access, and to request the correction of your personal
                    data. For privacy concerns, you may contact the Barangay Poblacion office directly.
                </p>

                <p class="pn-disclaimer">
                    This notice is provided for general information and does not constitute a certified legal
                    compliance statement. Formal compliance is verified separately by the barangay.
                </p>
            </div>
            <div class="lm-foot">
                <button type="button" class="lm-done" data-close>Close</button>
            </div>
        </dialog>

        <dialog class="legal-modal" id="legal-contact" aria-labelledby="legal-contact-title">
            <div class="lm-head">
                <div>
                    <h2 id="legal-contact-title">Contact Barangay</h2>
                    <p class="lm-sub">Barangay Poblacion, Bani, Pangasinan</p>
                </div>
                <button type="button" class="lm-close" data-close aria-label="Close Contact Barangay">
                    <i class="bi bi-x-lg" aria-hidden="true"></i>
                </button>
            </div>
            <div class="lm-body pn-body">
                <p class="pn-lead">
                    For questions about e-Poblacion, privacy concerns, or account assistance,
                    please contact the Barangay Poblacion office directly.
                </p>

                <ul class="contact-list">
                    <li>
                        <i class="bi bi-geo-alt" aria-hidden="true"></i>
                        <div>
                            <b>Barangay Poblacion Office</b>
                            <span>Barangay Poblacion, Bani, Pangasinan, Philippines</span>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-person-vcard" aria-hidden="true"></i>
                        <div>
                            <b>Account and username help</b>
                            <span>Ask the office to confirm your username in person. Passwords can be reset with the
                                <a href="{{ route('password-recovery.show') }}">Forgot password?</a> option on the sign-in form.</span>
                        </div>
                    </li>
                    <li>
                        <i class="bi bi-shield-check" aria-hidden="true"></i>
                        <div>
                            <b>Privacy concerns</b>
                            <span>To ask about, access, or correct your personal data, contact the barangay office directly.</span>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="lm-foot">
                <button type="button" class="lm-done" data-close>Close</button>
            </div>
        </dialog>

    </div>
</section>


{{-- ============================================================
     FOOTER
============================================================= --}}
<footer class="site-footer">
    <div class="footer-grid">

        <div class="footer-brand">
            <h3>e-Poblacion</h3>
            <p>Barangay Record Management System</p>
            <p class="locale">
                Barangay Poblacion<br>
                Bani, Pangasinan, Philippines
            </p>
        </div>

        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="#top">Home</a></li>
                <li><a href="#login">Login</a></li>
                <li><a href="#legal-contact" data-legal="contact">Contact</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Legal</h4>
            <ul>
                <li><a href="#legal-terms" data-legal="terms">Terms &amp; Conditions</a></li>
                <li><a href="#legal-policy" data-legal="policy">Privacy Policy</a></li>
                <li><a href="#legal-notice" data-legal="notice">Data Privacy</a></li>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        &copy; {{ date('Y') }} e-Poblacion. All rights reserved.
    </div>
</footer>


{{-- SCROLL TO TOP --}}
<button class="scroll-top" id="scrollTop" type="button" aria-label="Scroll to top">
    <i class="bi bi-arrow-up" aria-hidden="true"></i>
</button>


{{-- ============================================================
     PAGE JAVASCRIPT (self-contained)
============================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    var $ = function (id) { return document.getElementById(id); };
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------------- Attendance kiosk overlay ---------------- */
    var kioskOverlay = $('attendanceKioskOverlay');
    var kioskFrame = $('attendanceKioskFrame');
    var kioskOpenButton = $('openAttendanceKiosk');
    var kioskMinimizeButton = $('minimizeAttendanceKiosk');
    var kioskCloseButton = $('closeAttendanceKiosk');
    var previousKioskFocus = null;
    var kioskUrl = @json(route('attendance.kiosk', [], false));

    function openAttendanceKiosk() {
        if (!kioskOverlay || !kioskFrame) return;
        previousKioskFocus = document.activeElement;
        if (!kioskFrame.getAttribute('src')) kioskFrame.setAttribute('src', kioskUrl);
        kioskOverlay.classList.add('is-open');
        kioskOverlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('kiosk-overlay-open');
        if (kioskMinimizeButton) kioskMinimizeButton.focus();
    }

    function minimizeAttendanceKiosk() {
        if (!kioskOverlay) return;
        kioskOverlay.classList.remove('is-open');
        kioskOverlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('kiosk-overlay-open');
        if (previousKioskFocus && typeof previousKioskFocus.focus === 'function') previousKioskFocus.focus();
    }

    function closeAttendanceKiosk() {
        minimizeAttendanceKiosk();
        // Unload the kiosk document to clear its temporary browser session state on Back.
        if (kioskFrame) kioskFrame.setAttribute('src', 'about:blank');
        if (previousKioskFocus && typeof previousKioskFocus.focus === 'function') previousKioskFocus.focus();
    }

    if (kioskOpenButton) kioskOpenButton.addEventListener('click', openAttendanceKiosk);
    if (kioskMinimizeButton) kioskMinimizeButton.addEventListener('click', minimizeAttendanceKiosk);
    if (kioskCloseButton) kioskCloseButton.addEventListener('click', closeAttendanceKiosk);
    if (kioskOverlay) {
        kioskOverlay.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                event.preventDefault();
                minimizeAttendanceKiosk();
            }
        });
    }

    /* ---------------- Carousel ---------------- */
    var hero = $('top');
    var carousel = $('carousel');
    var slides = Array.prototype.slice.call(carousel.querySelectorAll('.slide'));
    var dots = Array.prototype.slice.call(document.querySelectorAll('#carouselIndicators button'));
    var counter = $('carouselNow');
    var SLIDE_MS = 7000;
    var index = 0;
    var timer = null;

    function show(next) {
        index = (next + slides.length) % slides.length;

        slides.forEach(function (s, i) {
            var on = i === index;
            s.classList.toggle('is-active', on);
            s.setAttribute('aria-hidden', on ? 'false' : 'true');
        });

        dots.forEach(function (d, i) {
            d.classList.remove('is-active', 'is-done');
            d.setAttribute('aria-selected', i === index ? 'true' : 'false');

            if (i < index) {
                d.classList.add('is-done');
            }
        });

        // Force the progress animation to restart on the active dot.
        void dots[index].offsetWidth;
        dots[index].classList.add('is-active');

        counter.textContent = String(index + 1).padStart(2, '0');
        restart();
    }

    function restart() {
        clearTimeout(timer);

        if (reduceMotion || document.hidden || carousel.classList.contains('is-paused')) {
            return;
        }

        timer = setTimeout(function () {
            show(index + 1);
        }, SLIDE_MS);
    }

    if (reduceMotion) {
        hero.classList.add('no-autoplay');
    }

    $('carouselPrev').addEventListener('click', function () {
        show(index - 1);
    });

    $('carouselNext').addEventListener('click', function () {
        show(index + 1);
    });

    dots.forEach(function (d) {
        d.addEventListener('click', function () {
            show(parseInt(d.dataset.slideTo, 10));
        });
    });

    // Pause while the visitor hovers or focuses the carousel.
    function pause() {
        carousel.classList.add('is-paused');
        clearTimeout(timer);
    }

    function resume() {
        carousel.classList.remove('is-paused');
        restart();
    }

    hero.addEventListener('mouseenter', function () {
        if (!reduceMotion) {
            pause();
        }
    });

    hero.addEventListener('mouseleave', function () {
        if (!reduceMotion) {
            resume();
        }
    });

    // Swipe on touch screens.
    var startX = null;

    hero.addEventListener('touchstart', function (e) {
        startX = e.touches[0].clientX;
    }, { passive: true });

    hero.addEventListener('touchend', function (e) {
        if (startX === null) {
            return;
        }

        var dx = e.changedTouches[0].clientX - startX;

        if (Math.abs(dx) > 50) {
            show(index + (dx < 0 ? 1 : -1));
        }

        startX = null;
    }, { passive: true });

    // Stop the timer when the tab is hidden.
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            clearTimeout(timer);
        } else {
            restart();
        }
    });

    show(0);

    /* ---------------- Jump to login ---------------- */
    $('scrollIndicator').addEventListener('click', function () {
        $('login').scrollIntoView({
            behavior: reduceMotion ? 'auto' : 'smooth',
            block: 'start'
        });
    });

    /* ---------------- Role selector ---------------- */
    var pills = $('rolePills');
    var roleInput = $('role');
    var roleHint = $('roleHint');
    var roleButtons = Array.prototype.slice.call(pills.querySelectorAll('.role-pill'));

    function setRole(btn, focus) {
        roleButtons.forEach(function (b, i) {
            var on = b === btn;

            b.classList.toggle('is-active', on);
            b.setAttribute('aria-checked', on ? 'true' : 'false');
            b.tabIndex = on ? 0 : -1;

            if (on) {
                pills.dataset.active = String(i);
            }
        });

        roleInput.value = btn.dataset.role;
        roleHint.textContent = btn.dataset.role.charAt(0).toUpperCase()
            + btn.dataset.role.slice(1);

        if (focus) {
            btn.focus();
        }
    }

    roleButtons.forEach(function (btn, i) {
        btn.addEventListener('click', function () {
            setRole(btn, false);
        });

        btn.addEventListener('keydown', function (e) {
            var next = null;

            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                next = roleButtons[(i + 1) % roleButtons.length];
            }

            if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                next = roleButtons[(i - 1 + roleButtons.length) % roleButtons.length];
            }

            if (next) {
                e.preventDefault();
                setRole(next, true);
            }
        });
    });

    /* ---------------- Password show / hide + caps lock ---------------- */
    var pw = $('password');
    var toggle = $('togglePassword');
    var toggleIcon = $('togglePasswordIcon');

    toggle.addEventListener('click', function () {
        var showPassword = pw.type === 'password';

        pw.type = showPassword ? 'text' : 'password';
        toggleIcon.className = showPassword ? 'bi bi-eye-slash' : 'bi bi-eye';
        toggle.setAttribute('aria-pressed', showPassword ? 'true' : 'false');
        toggle.setAttribute('aria-label', showPassword ? 'Hide password' : 'Show password');

        pw.focus();
    });

    var capsTip = $('capsTip');

    function checkCaps(e) {
        if (!e.getModifierState) {
            return;
        }

        capsTip.classList.toggle('is-on', e.getModifierState('CapsLock'));
    }

    pw.addEventListener('keydown', checkCaps);
    pw.addEventListener('keyup', checkCaps);

    pw.addEventListener('blur', function () {
        capsTip.classList.remove('is-on');
    });

    /* ---------------- Username assistance (separate from password reset) ----------------
       "Forgot password?" is a normal link to route('password-recovery.show'),
       so it needs no JavaScript. Only the username help panel toggles here. */
    var usernameHelpLink = $('usernameHelpLink');
    var usernameHelp = $('username-help');

    if (usernameHelpLink && usernameHelp) {
        usernameHelpLink.addEventListener('click', function (e) {
            e.preventDefault();

            var open = usernameHelp.classList.toggle('is-on');

            usernameHelpLink.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    /* ---------------- Username/password validation + loading state ---------------- */
    var form = $('loginForm');
    var username = $('username');
    var btn = $('signInBtn');
    var btnText = $('signInText');

    function setFieldError(input, message) {
        var group = input.closest('.form-group');
        var old = group.querySelector('.field-error[data-js]');

        if (old) {
            old.remove();
        }

        input.classList.toggle('is-invalid', !!message);

        if (message) {
            var span = document.createElement('span');

            span.className = 'field-error';
            span.setAttribute('role', 'alert');
            span.setAttribute('data-js', '1');
            span.textContent = message;

            group.appendChild(span);
        }
    }

    username.addEventListener('input', function () {
        if (username.value.trim()) {
            setFieldError(username, '');
        }
    });

    pw.addEventListener('input', function () {
        if (pw.value) {
            setFieldError(pw, '');
        }
    });

    form.addEventListener('submit', function (e) {
        var problems = [];
        var usernameValue = username.value.trim();

        if (!usernameValue) {
            setFieldError(username, 'Please enter your username.');
            problems.push(username);
        } else if (usernameValue.length > 50) {
            setFieldError(username, 'Your username must not exceed 50 characters.');
            problems.push(username);
        }

        if (!pw.value) {
            setFieldError(pw, 'Please enter your password.');
            problems.push(pw);
        }

        if (problems.length) {
            e.preventDefault();
            problems[0].focus();
            return;
        }

        btn.classList.add('is-loading');
        btnText.textContent = 'Signing in…';
        btn.disabled = true;
    });

    // Restore the button if the visitor comes back with the browser's Back button.
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            btn.disabled = false;
            btn.classList.remove('is-loading');
            btnText.textContent = 'Sign in';
        }
    });

    // Jump to the first problem after a server-side error.
    var firstInvalid = form.querySelector('.form-input.is-invalid');

    if (firstInvalid && !form.classList.contains('recovery-mode-hidden')) {
        firstInvalid.focus();
    }

    // When the recovery form is active, focus its first invalid field instead.
    if (document.getElementById('emailPasswordRecoveryPanel')) {
        var emailRecoveryInvalid = document.querySelector('#emailPasswordRecoveryPanel .form-input.is-invalid');

        if (emailRecoveryInvalid) {
            emailRecoveryInvalid.focus();
        } else {
            var firstEmailRecoveryInput = document.querySelector('#emailPasswordRecoveryPanel input:not([type="hidden"])');

            if (firstEmailRecoveryInput) {
                firstEmailRecoveryInput.focus();
            }
        }
    }

    /* ---------------- Terms & Privacy pop-ups ---------------- */
    var legalModals = Array.prototype.slice.call(document.querySelectorAll('dialog.legal-modal'));

    function closeLegal(dlg) {
        if (dlg.open) {
            dlg.close();
        }
    }

    function openLegal(name) {
        var dlg = document.getElementById('legal-' + name);

        if (!dlg || typeof dlg.showModal !== 'function') {
            return false;
        }

        legalModals.forEach(function (m) {
            if (m !== dlg) {
                closeLegal(m);
            }
        });

        if (!dlg.open) {
            dlg.showModal();
            document.documentElement.classList.add('modal-open');

            var body = dlg.querySelector('.lm-body');

            if (body) {
                body.scrollTop = 0;
            }
        }

        return true;
    }

    // Cards, footer links, and links inside the pop-ups.
    document.addEventListener('click', function (e) {
        var link = e.target.closest('[data-legal]');

        if (link && openLegal(link.getAttribute('data-legal'))) {
            e.preventDefault();
        }
    });

    legalModals.forEach(function (dlg) {
        // Close buttons.
        dlg.querySelectorAll('[data-close]').forEach(function (b) {
            b.addEventListener('click', function () {
                closeLegal(dlg);
            });
        });

        // Click on the dark backdrop closes it.
        dlg.addEventListener('click', function (e) {
            if (e.target === dlg) {
                closeLegal(dlg);
            }
        });

        // Esc, buttons, and backdrop all end up here.
        dlg.addEventListener('close', function () {
            var anyOpen = legalModals.some(function (m) { return m.open; });

            if (!anyOpen) {
                document.documentElement.classList.remove('modal-open');
            }
        });
    });

    // Direct links such as /login#legal-terms open the pop-up on load.
    if (location.hash.indexOf('#legal-') === 0) {
        openLegal(location.hash.slice(7));
    }

    /* ---------------- Scroll to top ---------------- */
    var scrollTopBtn = $('scrollTop');

    window.addEventListener('scroll', function () {
        scrollTopBtn.classList.toggle('is-visible', window.scrollY > 600);
    }, { passive: true });

    scrollTopBtn.addEventListener('click', function () {
        window.scrollTo({
            top: 0,
            behavior: reduceMotion ? 'auto' : 'smooth'
        });
    });

    /* ---------------- Reveal on scroll ---------------- */
    var items = document.querySelectorAll('.reveal');

    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) {
                    en.target.classList.add('is-in');
                    io.unobserve(en.target);
                }
            });
        }, { threshold: 0.12 });

        items.forEach(function (el, i) {
            el.style.transitionDelay = (i % 4) * 60 + 'ms';
            io.observe(el);
        });
    } else {
        items.forEach(function (el) {
            el.classList.add('is-in');
        });
    }
});
</script>

</body>

</html>