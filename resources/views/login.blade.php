<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a4f45">
    <meta name="description" content="e-Poblacion — Record Management System for Barangay Poblacion, Bani, Pangasinan.">

    <title>e-Poblacion | Record Management System</title>

    {{-- Favicon: Barangay Poblacion logo --}}
    <link rel="icon" type="image/png" href="{{ asset('icon.png') }}">

    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    {{-- Lets CSS hide .reveal items only when JS is running --}}
    <script>document.documentElement.classList.add('js');</script>

    <style>
        :root {
            --ink: #10201e;
            --muted: #5b6b68;
            --faint: #8a9794;
            --line: #dbe3e0;
            --line-strong: #c3cfcb;
            --paper: #f3f6f5;
            --sheet: #ffffff;
            --pine: #0e6b5e;
            --pine-dark: #0a4f45;
            --pine-deep: #072f2a;
            --pine-soft: #e2f1ed;
            --mint: #5fd4a8;
            --sun: #f2b134;
            --red: #c0302d;
            --red-soft: #fce7e6;
            --font-head: 'Bricolage Grotesque', 'Public Sans', system-ui, sans-serif;
            --font-body: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
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
        a { color: var(--pine); }
        button { font: inherit; }
        img { max-width: 100%; }

        :focus-visible { outline: 3px solid var(--pine); outline-offset: 2px; }

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
        }
        @media (min-width: 960px) {
            .stage { grid-template-columns: minmax(0, 1fr) minmax(440px, 520px); }
        }

        /* ---------- Hero / carousel ---------- */
        .hero {
            position: relative; overflow: hidden; border-radius: 22px; background: var(--pine-deep);
            min-height: 58svh; color: #fff; isolation: isolate;
        }
        @media (min-width: 960px) { .hero { min-height: calc(100svh - 28px); } }

        .carousel { position: absolute; inset: 0; }
        .slide { position: absolute; inset: 0; opacity: 0; visibility: hidden; transition: opacity .9s ease, visibility 0s .9s; }
        .slide.is-active { opacity: 1; visibility: visible; transition: opacity .9s ease; z-index: 1; }
        .slide-media { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transform: scale(1.08); }
        .slide.is-active .slide-media { animation: kenburns 14s ease-out forwards; }
        @keyframes kenburns { to { transform: scale(1); } }
        .slide::after {
            content: ''; position: absolute; inset: 0;
            background:
                linear-gradient(0deg, rgba(7, 47, 42, .94) 0%, rgba(7, 47, 42, .55) 42%, rgba(7, 47, 42, .12) 75%),
                linear-gradient(180deg, rgba(7, 47, 42, .5) 0%, rgba(7, 47, 42, 0) 22%);
        }

        .slide-content {
            position: absolute; left: 0; right: 0; bottom: 0; z-index: 2;
            padding: 28px 28px 96px; max-width: 760px;
        }
        @media (min-width: 960px) { .slide-content { padding: 48px 48px 116px; } }

        .slide-eyebrow {
            display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 600;
            padding: 6px 12px; border-radius: 999px; background: rgba(255, 255, 255, .14);
            backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); margin-bottom: 16px;
        }
        .slide-eyebrow::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: var(--sun); }
        .slide-title { font-weight: 800; font-size: clamp(32px, 4.6vw, 60px); margin-bottom: 14px; color: #fff; }
        .gradient-e { color: var(--sun); font-style: italic; }
        .slide-subtitle { font-size: clamp(15px, 1.35vw, 17px); color: #d3e2de; max-width: 54ch; }

        .slide-content > * { opacity: 0; transform: translateY(14px); }
        .slide.is-active .slide-content > * { animation: rise .7s ease forwards; }
        .slide.is-active .slide-content > *:nth-child(2) { animation-delay: .12s; }
        .slide.is-active .slide-content > *:nth-child(3) { animation-delay: .24s; }
        @keyframes rise { to { opacity: 1; transform: none; } }

        .hero-brand {
            position: absolute; z-index: 5; top: 20px; left: 20px; display: flex; align-items: center; gap: 10px;
            padding: 6px 14px 6px 6px; border-radius: 999px; background: rgba(7, 47, 42, .45);
            backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); color: #fff; text-decoration: none;
        }
        .hero-brand img { width: 34px; height: 34px; border-radius: 50%; object-fit: cover; background: #fff; }
        .hero-brand span { font-family: var(--font-head); font-weight: 700; font-size: 15px; letter-spacing: -0.01em; }
        @media (min-width: 960px) { .hero-brand { top: 28px; left: 28px; } }

        .carousel-counter {
            position: absolute; z-index: 5; top: 26px; right: 24px; font-family: var(--font-head); font-weight: 700;
            font-size: 14px; letter-spacing: .04em; color: rgba(255, 255, 255, .8); font-variant-numeric: tabular-nums;
        }
        .carousel-counter b { color: #fff; font-size: 18px; }

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
        .carousel-indicators button::before { width: 100%; background: rgba(255, 255, 255, .28); }
        .carousel-indicators button::after { width: 0; background: #fff; }
        .carousel-indicators button.is-done::after { width: 100%; }
        .carousel-indicators button.is-active::after { animation: fill var(--slide-ms) linear forwards; }
        .carousel.is-paused ~ .hero-controls .carousel-indicators button.is-active::after { animation-play-state: paused; }
        .hero.no-autoplay .carousel-indicators button.is-active::after { animation: none; width: 100%; }
        @keyframes fill { to { width: 100%; } }

        .carousel-arrows { display: flex; gap: 8px; margin-left: auto; }
        .carousel-nav {
            width: 42px; height: 42px; border-radius: 50%; border: 1.5px solid rgba(255, 255, 255, .4);
            background: rgba(255, 255, 255, .08); color: #fff; display: grid; place-items: center; cursor: pointer;
            backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); transition: background .15s, border-color .15s;
        }
        .carousel-nav:hover { background: rgba(255, 255, 255, .22); border-color: #fff; }
        .carousel-nav:focus-visible, .carousel-indicators button:focus-visible, .scroll-indicator:focus-visible { outline-color: var(--mint); }

        .scroll-indicator {
            position: absolute; z-index: 6; right: 16px; bottom: 74px; display: inline-flex; align-items: center; gap: 8px;
            background: var(--mint); color: #06251e; border: 0; border-radius: 999px; padding: 9px 14px 9px 16px;
            font-weight: 700; font-size: 13.5px; cursor: pointer;
        }
        .scroll-indicator .mouse { display: inline-block; width: 8px; height: 8px; border-right: 2px solid currentColor; border-bottom: 2px solid currentColor; transform: rotate(45deg) translateY(-2px); }
        @media (min-width: 960px) { .scroll-indicator { display: none; } }

        /* ============================================================
           LOGIN
        ============================================================ */
        .login-section { display: grid; place-items: center; padding: 8px 4px 28px; scroll-margin-top: 8px; }
        @media (min-width: 960px) { .login-section { padding: 24px; } }

        .login-card {
            width: 100%; max-width: 440px; background: var(--sheet); border: 1px solid var(--line);
            border-radius: 22px; padding: 30px 28px 26px;
        }
        @media (min-width: 480px) { .login-card { padding: 36px 36px 30px; } }

        .login-brand { margin-bottom: 24px; }
        .login-logo { width: 54px; height: 54px; border-radius: 14px; object-fit: cover; background: var(--pine-soft); margin-bottom: 16px; display: block; }
        .login-brand h1 { font-size: 30px; font-weight: 800; }
        .tagline { color: var(--pine); font-weight: 700; font-size: 14px; margin-top: 2px; }
        .welcome { color: var(--muted); font-size: 14.5px; margin-top: 10px; }
        .portal-badge {
            display: inline-flex; align-items: center; gap: 8px; margin-top: 14px; padding: 6px 12px; border-radius: 999px;
            background: var(--pine-soft); color: var(--pine-dark); font-size: 12.5px; font-weight: 700;
        }

        /* Role segmented control */
        .role-selector { margin-bottom: 20px; }
        .role-selector-label { display: block; font-size: 13.5px; font-weight: 600; margin-bottom: 8px; }
        .role-pills { position: relative; display: grid; grid-template-columns: 1fr 1fr; padding: 4px; border-radius: 12px; background: var(--paper); border: 1px solid var(--line); }
        .role-thumb {
            position: absolute; top: 4px; bottom: 4px; left: 4px; width: calc(50% - 4px); border-radius: 9px;
            background: var(--pine); box-shadow: 0 4px 12px rgba(14, 107, 94, .3); transition: transform .28s cubic-bezier(.4, 0, .2, 1);
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
            border: 1.5px solid var(--line-strong); border-radius: 10px; transition: border-color .15s, box-shadow .15s;
        }
        .form-input.has-icon-left { padding-left: 42px; }
        .form-input.has-icon-right { padding-right: 48px; }
        .form-input::placeholder { color: var(--faint); }
        .form-input:hover { border-color: var(--faint); }
        .form-input:focus { outline: none; border-color: var(--pine); box-shadow: 0 0 0 4px var(--pine-soft); }
        .input-wrap:focus-within .input-icon.left { color: var(--pine); }
        .form-input.is-invalid { border-color: var(--red); }
        .form-input.is-invalid:focus { box-shadow: 0 0 0 4px var(--red-soft); }
        .field-error { display: block; margin-top: 6px; font-size: 13px; font-weight: 600; color: var(--red); }
        .field-tip { display: none; margin-top: 6px; font-size: 13px; font-weight: 600; color: #8a5a00; }
        .field-tip.is-on { display: flex; gap: 6px; align-items: center; }

        .form-row { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; justify-content: space-between; margin: 4px 0 18px; }
        .checkbox-wrap { display: inline-flex; align-items: center; gap: 9px; cursor: pointer; font-size: 14px; font-weight: 500; }
        .checkbox-wrap input { width: 18px; height: 18px; accent-color: var(--pine); cursor: pointer; }
        .forgot-link { font-size: 14px; font-weight: 600; text-decoration: none; }
        .forgot-link:hover { text-decoration: underline; }
        .forgot-note { display: none; margin: -6px 0 16px; padding: 10px 12px; border-radius: 10px; background: var(--paper); font-size: 13.5px; color: var(--muted); }
        .forgot-note.is-on { display: block; }

        .btn-primary {
            width: 100%; min-height: 50px; display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            border: 0; border-radius: 12px; background: var(--pine); color: #fff; font-weight: 700; font-size: 16px; cursor: pointer;
            transition: background .15s, transform .15s;
        }
        .btn-primary:hover { background: var(--pine-dark); }
        .btn-primary:active { transform: scale(.99); }
        .btn-primary i { transition: transform .2s; }
        .btn-primary:hover i { transform: translateX(3px); }
        .btn-primary[disabled] { background: var(--faint); cursor: progress; }
        .btn-primary .spin { display: none; width: 18px; height: 18px; border-radius: 50%; border: 2.5px solid rgba(255, 255, 255, .4); border-top-color: #fff; animation: spin .7s linear infinite; }
        .btn-primary.is-loading .spin { display: inline-block; }
        .btn-primary.is-loading i { display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .signup-link { margin-top: 16px; text-align: center; font-size: 13px; color: var(--muted); display: flex; gap: 6px; justify-content: center; align-items: center; }

        /* ============================================================
           PRIVACY
        ============================================================ */
        .privacy-section { padding: 72px 20px; scroll-margin-top: 12px; }
        .privacy-inner { max-width: 1180px; margin: 0 auto; }
        .privacy-head { max-width: 640px; margin-bottom: 36px; }
        .section-eyebrow { display: inline-flex; align-items: center; gap: 8px; font-weight: 700; font-size: 14px; color: var(--pine); margin-bottom: 10px; }
        .section-eyebrow::before { content: ''; width: 22px; height: 3px; border-radius: 3px; background: var(--sun); }
        .section-title { font-size: clamp(28px, 3.6vw, 42px); font-weight: 800; margin-bottom: 10px; }
        .section-subtitle { color: var(--muted); font-size: 16px; }

        .privacy-grid { display: grid; grid-template-columns: minmax(0, 1fr); gap: 14px; margin-bottom: 28px; }
        @media (min-width: 640px) { .privacy-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (min-width: 1000px) { .privacy-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); } }

        .privacy-card {
            display: flex; flex-direction: column; background: var(--sheet); border: 1px solid var(--line);
            border-radius: 16px; padding: 22px; transition: border-color .15s, transform .15s;
        }
        .privacy-card:hover { border-color: var(--pine); transform: translateY(-3px); }
        .pc-icon { width: 44px; height: 44px; border-radius: 12px; display: grid; place-items: center; background: var(--pine-soft); color: var(--pine); font-size: 20px; margin-bottom: 16px; }
        .privacy-card h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
        .privacy-card p { color: var(--muted); font-size: 14.5px; margin-bottom: 16px; }
        .privacy-card a { margin-top: auto; font-weight: 700; font-size: 14px; text-decoration: none; }
        .privacy-card a:hover { text-decoration: underline; }

        .privacy-notice {
            display: grid; grid-template-columns: minmax(0, 1fr); gap: 28px; background: var(--sheet);
            border: 1px solid var(--line); border-radius: 20px; padding: 28px; scroll-margin-top: 20px;
        }
        @media (min-width: 900px) { .privacy-notice { grid-template-columns: 300px minmax(0, 1fr); gap: 48px; padding: 44px; } }
        .privacy-notice h2 { font-size: 28px; font-weight: 800; }
        .pn-sub { color: var(--pine); font-weight: 700; font-size: 14px; margin-top: 6px; }
        .pn-side ul { list-style: none; margin: 20px 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: 8px; }
        .pn-side li { padding: 6px 12px; border-radius: 999px; background: var(--paper); border: 1px solid var(--line); font-size: 13.5px; font-weight: 500; }
        .pn-body { display: grid; gap: 14px; color: #2c3c39; max-width: 70ch; }
        .pn-body .pn-lead { font-size: 16.5px; color: var(--ink); }
        .pn-disclaimer { padding: 12px 14px; border-radius: 10px; background: #fff3d1; border-left: 5px solid var(--sun); font-size: 13.5px; color: #6b4a00; }

        /* ============================================================
           FOOTER
        ============================================================ */
        .site-footer { background: var(--pine-deep); color: #c5d5d1; padding: 56px 20px 24px; }
        .footer-grid { max-width: 1180px; margin: 0 auto; display: grid; gap: 32px; grid-template-columns: minmax(0, 1fr); }
        @media (min-width: 760px) { .footer-grid { grid-template-columns: 1.6fr 1fr 1fr; } }
        .footer-brand h3 { color: #fff; font-size: 26px; font-weight: 800; margin-bottom: 4px; }
        .footer-brand p { color: #9fb7b1; }
        .footer-brand .locale { margin-top: 14px; color: #c5d5d1; }
        .footer-col h4 { color: #fff; font-size: 15px; margin-bottom: 12px; }
        .footer-col ul { list-style: none; margin: 0; padding: 0; display: grid; gap: 8px; }
        .footer-col a { color: #c5d5d1; text-decoration: none; font-size: 14.5px; }
        .footer-col a:hover { color: #fff; text-decoration: underline; }
        .site-footer a:focus-visible { outline-color: var(--mint); }
        .footer-bottom { max-width: 1180px; margin: 36px auto 0; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, .14); font-size: 13.5px; color: #9fb7b1; }

        /* ============================================================
           SCROLL-TO-TOP + REVEAL
        ============================================================ */
        .scroll-top {
            position: fixed; right: 18px; bottom: 18px; z-index: 50; width: 46px; height: 46px; border-radius: 50%; border: 0;
            background: var(--ink); color: #fff; font-size: 18px; display: grid; place-items: center; cursor: pointer;
            box-shadow: 0 8px 22px rgba(16, 32, 30, .3); opacity: 0; visibility: hidden; transform: translateY(10px);
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
                        disaster records, and relief records through one centralized Record Management System.
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

            {{-- Form --}}
            <form method="POST" action="{{ route('login.authenticate') }}" id="loginForm" novalidate>
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

                {{-- Email --}}
                <div class="form-group">
                    <label class="form-label" for="email">Email address</label>
                    <div class="input-wrap">
                        <i class="bi bi-envelope input-icon left" aria-hidden="true"></i>
                        <input type="email" id="email" name="email"
                               class="form-input has-icon-left {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               value="{{ old('email') }}" placeholder="you@example.com"
                               autocomplete="username" required>
                    </div>
                    @error('email')
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

                {{-- Remember / forgot --}}
                <div class="form-row">
                    <label class="checkbox-wrap">
                        <input type="checkbox" name="remember" id="remember" value="1">
                        <span>Remember me</span>
                    </label>

                    {{-- Actual password reset will be implemented later --}}
                    <a class="forgot-link" href="#" id="forgotPasswordLink">Forgot password?</a>
                </div>

                <div class="forgot-note" id="forgotNote" role="status">
                    Password reset is not available yet. Please contact the Barangay Poblacion office for help.
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

        </div>

    </section>

</main>


{{-- ============================================================
     TERMS & PRIVACY
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
                <a href="#">Read terms &rarr;</a>
            </div>

            <div class="privacy-card reveal">
                <div class="pc-icon"><i class="bi bi-shield-lock" aria-hidden="true"></i></div>
                <h3>Privacy Policy</h3>
                <p>How e-Poblacion handles and protects personal information contained in barangay records.</p>
                <a href="#">Read policy &rarr;</a>
            </div>

            <div class="privacy-card reveal">
                <div class="pc-icon"><i class="bi bi-info-circle" aria-hidden="true"></i></div>
                <h3>Data Privacy Notice</h3>
                <p>A plain-language summary of data privacy rights and how personal information is handled within the system.</p>
                <a href="#privacy-notice">Read notice &rarr;</a>
            </div>

            <div class="privacy-card reveal">
                <div class="pc-icon"><i class="bi bi-telephone" aria-hidden="true"></i></div>
                <h3>Contact Barangay</h3>
                <p>Reach the Barangay Poblacion office for inquiries and assistance.</p>
                <a href="#">Contact us &rarr;</a>
            </div>

        </div>


        {{-- Data privacy notice --}}
        <div class="privacy-notice reveal" id="privacy-notice">

            <div class="pn-side">
                <h2>Data Privacy Notice</h2>
                <p class="pn-sub">Barangay Poblacion, Bani, Pangasinan</p>

                <p style="margin-top:18px; font-size:14px; color:var(--muted);">
                    The information that may be maintained in the system may include:
                </p>
                <ul>
                    <li>Full name</li>
                    <li>Address and household details</li>
                    <li>Contact information</li>
                    <li>Records related to barangay services</li>
                    <li>Documents and other supporting information</li>
                    <li>Disaster assessment and relief-related records</li>
                </ul>
            </div>

            <div class="pn-body">
                <p class="pn-lead">
                    e-Poblacion collects and processes personal information for legitimate barangay
                    record-management purposes, including resident records, household records, document
                    processing, reports and complaints, disaster-related records, and relief-related records.
                </p>

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

        </div>

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
                <li><a href="#">Resident Portal</a></li>
            </ul>
        </div>

        <div class="footer-col">
            <h4>Legal</h4>
            <ul>
                <li><a href="#">Terms &amp; Conditions</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#privacy">Data Privacy</a></li>
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
     PAGE JAVASCRIPT (self-contained; replaces js/login.js)
============================================================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    var $ = function (id) { return document.getElementById(id); };
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

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
            if (i < index) { d.classList.add('is-done'); }
        });
        // force the progress animation to restart on the active dot
        void dots[index].offsetWidth;
        dots[index].classList.add('is-active');

        counter.textContent = String(index + 1).padStart(2, '0');
        restart();
    }

    function restart() {
        clearTimeout(timer);
        if (reduceMotion) { return; }
        timer = setTimeout(function () { show(index + 1); }, SLIDE_MS);
    }

    if (reduceMotion) { hero.classList.add('no-autoplay'); }

    $('carouselPrev').addEventListener('click', function () { show(index - 1); });
    $('carouselNext').addEventListener('click', function () { show(index + 1); });
    dots.forEach(function (d) {
        d.addEventListener('click', function () { show(parseInt(d.dataset.slideTo, 10)); });
    });

    // Pause while the visitor hovers or focuses the carousel
    var remaining = SLIDE_MS;
    function pause() {
        carousel.classList.add('is-paused');
        clearTimeout(timer);
    }
    function resume() {
        carousel.classList.remove('is-paused');
        restart();
    }
    hero.addEventListener('mouseenter', function () { if (!reduceMotion) { pause(); } });
    hero.addEventListener('mouseleave', function () { if (!reduceMotion) { resume(); } });

    // Swipe on touch screens
    var startX = null;
    hero.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
    hero.addEventListener('touchend', function (e) {
        if (startX === null) { return; }
        var dx = e.changedTouches[0].clientX - startX;
        if (Math.abs(dx) > 50) { show(index + (dx < 0 ? 1 : -1)); }
        startX = null;
    }, { passive: true });

    // Stop the timer when the tab is hidden
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) { clearTimeout(timer); } else { restart(); }
    });

    show(0);

    /* ---------------- Jump to login ---------------- */
    $('scrollIndicator').addEventListener('click', function () {
        $('login').scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
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
            if (on) { pills.dataset.active = String(i); }
        });
        roleInput.value = btn.dataset.role;
        roleHint.textContent = btn.dataset.role.charAt(0).toUpperCase() + btn.dataset.role.slice(1);
        if (focus) { btn.focus(); }
    }

    roleButtons.forEach(function (btn, i) {
        btn.addEventListener('click', function () { setRole(btn, false); });
        btn.addEventListener('keydown', function (e) {
            var next = null;
            if (e.key === 'ArrowRight' || e.key === 'ArrowDown') { next = roleButtons[(i + 1) % roleButtons.length]; }
            if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') { next = roleButtons[(i - 1 + roleButtons.length) % roleButtons.length]; }
            if (next) { e.preventDefault(); setRole(next, true); }
        });
    });

    /* ---------------- Password show / hide + caps lock ---------------- */
    var pw = $('password');
    var toggle = $('togglePassword');
    var toggleIcon = $('togglePasswordIcon');

    toggle.addEventListener('click', function () {
        var show = pw.type === 'password';
        pw.type = show ? 'text' : 'password';
        toggleIcon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        toggle.setAttribute('aria-pressed', show ? 'true' : 'false');
        toggle.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        pw.focus();
    });

    var capsTip = $('capsTip');
    function checkCaps(e) {
        if (!e.getModifierState) { return; }
        capsTip.classList.toggle('is-on', e.getModifierState('CapsLock'));
    }
    pw.addEventListener('keydown', checkCaps);
    pw.addEventListener('keyup', checkCaps);
    pw.addEventListener('blur', function () { capsTip.classList.remove('is-on'); });

    /* ---------------- Forgot password (placeholder) ---------------- */
    $('forgotPasswordLink').addEventListener('click', function (e) {
        e.preventDefault();
        $('forgotNote').classList.toggle('is-on');
    });

    /* ---------------- Form validation + loading state ---------------- */
    var form = $('loginForm');
    var email = $('email');
    var btn = $('signInBtn');
    var btnText = $('signInText');

    function setFieldError(input, message) {
        var group = input.closest('.form-group');
        var old = group.querySelector('.field-error[data-js]');
        if (old) { old.remove(); }
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

    email.addEventListener('input', function () { if (email.value) { setFieldError(email, ''); } });
    pw.addEventListener('input', function () { if (pw.value) { setFieldError(pw, ''); } });

    form.addEventListener('submit', function (e) {
        var problems = [];
        var mail = email.value.trim();

        if (!mail) {
            setFieldError(email, 'Please enter your email address.');
            problems.push(email);
        } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(mail)) {
            setFieldError(email, 'That does not look like a valid email address.');
            problems.push(email);
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

    // Restore the button if the visitor comes back with the browser's Back button
    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            btn.disabled = false;
            btn.classList.remove('is-loading');
            btnText.textContent = 'Sign in';
        }
    });

    // Jump to the first problem after a server-side error
    var firstInvalid = form.querySelector('.form-input.is-invalid');
    if (firstInvalid) { firstInvalid.focus(); }

    /* ---------------- Scroll to top ---------------- */
    var top = $('scrollTop');
    window.addEventListener('scroll', function () {
        top.classList.toggle('is-visible', window.scrollY > 600);
    }, { passive: true });
    top.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
    });

    /* ---------------- Reveal on scroll ---------------- */
    var items = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
            });
        }, { threshold: 0.12 });
        items.forEach(function (el, i) {
            el.style.transitionDelay = (i % 4) * 60 + 'ms';
            io.observe(el);
        });
    } else {
        items.forEach(function (el) { el.classList.add('is-in'); });
    }
});
</script>

</body>

</html>
