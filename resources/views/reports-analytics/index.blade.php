@extends('layouts.app')

@section('title', 'Reports & Analytics | e-Poblacion')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* ============================================================
       REPORTS & ANALYTICS
       Normal screen UI + dedicated professional print report
       ============================================================ */

    .ax {
        --ax-display: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
        --ax-body: 'Figtree', 'Segoe UI', system-ui, sans-serif;

        --ax-surface: #ffffff;
        --ax-sunken: #f4f7f6;
        --ax-line: #e0e7e5;
        --ax-line-strong: #c8d3d0;
        --ax-ink: #0e2a33;
        --ax-text: #2b4149;
        --ax-muted: #66808a;

        --ax-teal: #0f766e;
        --ax-teal-soft: #dff3ef;
        --ax-sun: #e9a80c;
        --ax-sun-soft: #fbf0d0;
        --ax-coral: #e0573a;
        --ax-coral-soft: #fbe6e0;
        --ax-indigo: #4f5fd1;
        --ax-ok: #178a55;
        --ax-ok-soft: #e0f4ea;

        --ax-hero: #0e2a33;
        --ax-hero-2: #143c47;
        --ax-hero-text: #eaf5f3;
        --ax-hero-muted: #9dbcc0;
        --ax-focus: rgba(15, 118, 110, .3);
        --ax-shadow: 0 1px 2px rgba(14, 42, 51, .05), 0 8px 24px rgba(14, 42, 51, .06);

        font-family: var(--ax-body);
        color: var(--ax-text);
    }

    [data-theme="dark"] .ax {
        --ax-surface: #14262d;
        --ax-sunken: #0f1f25;
        --ax-line: #25404a;
        --ax-line-strong: #33525d;
        --ax-ink: #eaf5f3;
        --ax-text: #c9dbde;
        --ax-muted: #8aa5ac;
        --ax-teal: #3dd0c0;
        --ax-teal-soft: #123a38;
        --ax-sun: #f3c14a;
        --ax-sun-soft: #3a3016;
        --ax-coral: #ff8a6b;
        --ax-coral-soft: #3d2019;
        --ax-indigo: #8f9bff;
        --ax-ok: #56d99b;
        --ax-ok-soft: #123626;
        --ax-hero: #0b1a20;
        --ax-hero-2: #12303a;
        --ax-focus: rgba(61, 208, 192, .35);
        --ax-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 8px 24px rgba(0, 0, 0, .3);
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .ax {
            --ax-surface: #14262d;
            --ax-sunken: #0f1f25;
            --ax-line: #25404a;
            --ax-line-strong: #33525d;
            --ax-ink: #eaf5f3;
            --ax-text: #c9dbde;
            --ax-muted: #8aa5ac;
            --ax-teal: #3dd0c0;
            --ax-teal-soft: #123a38;
            --ax-sun: #f3c14a;
            --ax-sun-soft: #3a3016;
            --ax-coral: #ff8a6b;
            --ax-coral-soft: #3d2019;
            --ax-indigo: #8f9bff;
            --ax-ok: #56d99b;
            --ax-ok-soft: #123626;
            --ax-hero: #0b1a20;
            --ax-hero-2: #12303a;
            --ax-focus: rgba(61, 208, 192, .35);
            --ax-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 8px 24px rgba(0, 0, 0, .3);
        }
    }

    .ax *, .ax *::before, .ax *::after { box-sizing: border-box; }
    .ax a { text-decoration: none; }
    .ax :focus-visible { outline: 3px solid var(--ax-focus); outline-offset: 2px; border-radius: 8px; }

    /* Print-only blocks: hidden on screen */
    .ax-print-header,
    .ax-print-titleband,
    .ax-print-summary { display: none; }

    /* ---------- Header ---------- */

    .ax-head { display: flex; flex-wrap: wrap; gap: 18px 24px; align-items: flex-end; justify-content: space-between; margin-bottom: 26px; }
    .ax-title { margin: 0; font-family: var(--ax-display); font-weight: 800; font-size: clamp(1.8rem, 3.4vw, 2.6rem); letter-spacing: -.02em; line-height: 1.05; color: var(--ax-ink); }
    .ax-sub { margin: 8px 0 0; color: var(--ax-muted); max-width: 56ch; }
    .ax-head-side { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }

    .ax-modules { display: inline-flex; gap: 4px; padding: 4px; border-radius: 14px; background: var(--ax-sunken); border: 1px solid var(--ax-line); }
    .ax-module { display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; border-radius: 10px; font-weight: 600; font-size: .88rem; color: var(--ax-muted); transition: background .15s, color .15s; }
    .ax-module:hover { color: var(--ax-ink); }
    .ax-module.is-active { background: var(--ax-surface); color: var(--ax-ink); box-shadow: var(--ax-shadow); }
    .ax-module.is-disabled { opacity: .5; cursor: not-allowed; }

    .ax-btn { display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 0 18px; border-radius: 12px; font: 600 .92rem var(--ax-body); cursor: pointer; background: var(--ax-ink); color: var(--ax-surface); border: 1px solid var(--ax-ink); transition: filter .15s, transform .1s; }
    .ax-btn:hover { filter: brightness(1.15); color: var(--ax-surface); }
    .ax-btn:active { transform: translateY(1px); }
    .ax-btn--line { background: transparent; color: var(--ax-ink); border-color: var(--ax-line-strong); }
    .ax-btn--line:hover { background: var(--ax-sunken); color: var(--ax-ink); filter: none; }
    .ax-btn.is-disabled { opacity: .5; pointer-events: none; }

    .ax-filter-card {
        margin: 0 0 26px;
        padding: 16px 18px;
        border: 1px solid var(--ax-line);
        border-radius: 18px;
        background: var(--ax-surface);
        box-shadow: var(--ax-shadow);
    }
    .ax-filter-form {
        display: flex;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
    }
    .ax-filter-field {
        display: grid;
        gap: 6px;
        min-width: 170px;
    }
    .ax-filter-field label {
        font-size: .78rem;
        font-weight: 700;
        color: var(--ax-muted);
    }
    .ax-filter-field input {
        min-height: 42px;
        padding: 0 12px;
        border: 1px solid var(--ax-line-strong);
        border-radius: 10px;
        background: var(--ax-surface);
        color: var(--ax-ink);
        font: 500 .9rem var(--ax-body);
    }
    .ax-filter-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    .ax-filter-note {
        margin: 10px 0 0;
        font-size: .8rem;
        color: var(--ax-muted);
    }
    .ax-filter-note strong { color: var(--ax-ink); }


    /* ---------- Shell ---------- */

    .ax-shell { display: grid; grid-template-columns: 190px minmax(0, 1fr); gap: 34px; align-items: start; }
    .ax-rail { position: sticky; top: 20px; }
    .ax-rail-title { margin: 0 0 10px 14px; font-size: .8rem; font-weight: 600; color: var(--ax-muted); }
    .ax-rail nav { display: grid; gap: 2px; border-left: 2px solid var(--ax-line); }
    .ax-rail a { display: flex; align-items: center; gap: 10px; padding: 8px 14px; margin-left: -2px; border-left: 2px solid transparent; color: var(--ax-muted); font-weight: 500; font-size: .9rem; transition: color .15s, border-color .15s; }
    .ax-rail a i { font-size: .95rem; }
    .ax-rail a:hover { color: var(--ax-ink); }
    .ax-rail a.is-current { color: var(--ax-ink); font-weight: 700; border-left-color: var(--ax-teal); }
    .ax-rail a.is-current i { color: var(--ax-teal); }

    .ax-main { display: grid; gap: 54px; min-width: 0; }
    .ax-chapter { scroll-margin-top: 22px; }
    .ax-chapter-head { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }
    .ax-h2 { display: flex; align-items: center; gap: 12px; margin: 0; font-family: var(--ax-display); font-weight: 800; font-size: 1.5rem; letter-spacing: -.015em; color: var(--ax-ink); }
    .ax-h2 i { display: grid; place-items: center; width: 36px; height: 36px; border-radius: 11px; background: var(--ax-teal-soft); color: var(--ax-teal); font-size: 1.05rem; }
    .ax-lede { margin: 0; color: var(--ax-muted); font-size: .92rem; max-width: 52ch; }

    /* ---------- Hero ---------- */

    .ax-hero { position: relative; overflow: hidden; border-radius: 26px; padding: 30px; color: var(--ax-hero-text); background: radial-gradient(120% 140% at 100% 0%, var(--ax-hero-2) 0%, transparent 60%), var(--ax-hero); border: 1px solid color-mix(in srgb, var(--ax-hero-text) 10%, transparent); box-shadow: var(--ax-shadow); }
    .ax-hero-grid { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, 1fr); gap: 34px; align-items: center; }
    .ax-hero-kicker { font-size: .9rem; color: var(--ax-hero-muted); font-weight: 500; }
    .ax-hero-number { margin: 6px 0 4px; font-family: var(--ax-display); font-weight: 800; font-size: clamp(3.2rem, 7vw, 5.2rem); letter-spacing: -.035em; line-height: .95; font-variant-numeric: tabular-nums; color: #fff; }
    .ax-hero-note { margin: 0; color: var(--ax-hero-muted); font-size: .95rem; }

    .ax-split { margin-top: 24px; }
    .ax-split-bar { display: flex; height: 14px; border-radius: 999px; overflow: hidden; background: rgba(255,255,255,.12); gap: 3px; }
    .ax-split-bar span { display: block; height: 100%; border-radius: 999px; min-width: 6px; }
    .ax-split-male { background: #5fd3c6; }
    .ax-split-female { background: #ff9c7f; }
    .ax-split-other { background: #f3c14a; }
    .ax-split-legend { display: flex; flex-wrap: wrap; gap: 8px 22px; margin-top: 12px; font-size: .88rem; color: var(--ax-hero-muted); }
    .ax-split-legend b { color: #fff; font-weight: 700; font-variant-numeric: tabular-nums; }
    .ax-dot { display: inline-block; width: 9px; height: 9px; border-radius: 50%; margin-right: 7px; }

    .ax-gauges { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
    .ax-gauge { display: flex; align-items: center; gap: 14px; padding: 14px; border-radius: 18px; background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.09); }
    .ax-ring { position: relative; width: 66px; height: 66px; flex: none; }
    .ax-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
    .ax-ring circle { fill: none; stroke-width: 9; stroke-linecap: round; }
    .ax-ring .track { stroke: rgba(255,255,255,.14); }
    .ax-ring .fill { stroke: var(--c, #5fd3c6); stroke-dasharray: var(--p) 100; animation: ax-fill 1.1s cubic-bezier(.2,.7,.2,1) both; }
    @keyframes ax-fill { from { stroke-dasharray: 0 100; } }
    .ax-ring-num { position: absolute; inset: 0; display: grid; place-items: center; font-family: var(--ax-display); font-weight: 800; font-size: .95rem; color: #fff; font-variant-numeric: tabular-nums; }
    .ax-gauge-label { font-weight: 700; color: #fff; font-size: .92rem; line-height: 1.25; }
    .ax-gauge-sub { margin-top: 3px; font-size: .8rem; color: var(--ax-hero-muted); font-variant-numeric: tabular-nums; }

    .ax-about { display: flex; gap: 12px; margin-top: 18px; padding: 14px 16px; border-radius: 14px; background: var(--ax-sunken); border: 1px solid var(--ax-line); color: var(--ax-muted); font-size: .88rem; line-height: 1.55; }
    .ax-about i { color: var(--ax-teal); font-size: 1.05rem; margin-top: 2px; }
    .ax-about b { color: var(--ax-ink); }

    /* ---------- Figure strips ---------- */

    .ax-strip { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); background: var(--ax-surface); border: 1px solid var(--ax-line); border-radius: 20px; box-shadow: var(--ax-shadow); overflow: hidden; }
    .ax-fig { padding: 20px 22px; position: relative; }
    .ax-fig + .ax-fig { border-left: 1px solid var(--ax-line); }
    .ax-fig-label { display: flex; align-items: center; gap: 8px; font-size: .86rem; font-weight: 600; color: var(--ax-muted); }
    .ax-fig-label i { color: var(--tone, var(--ax-teal)); font-size: 1rem; }
    .ax-fig-value { margin-top: 8px; font-family: var(--ax-display); font-weight: 800; font-size: 2rem; letter-spacing: -.02em; line-height: 1; color: var(--ax-ink); font-variant-numeric: tabular-nums; }
    .ax-fig-desc { margin-top: 7px; font-size: .8rem; color: var(--ax-muted); }

    .t-teal { --tone: var(--ax-teal); }
    .t-sun { --tone: var(--ax-sun); }
    .t-coral { --tone: var(--ax-coral); }
    .t-indigo { --tone: var(--ax-indigo); }
    .t-ok { --tone: var(--ax-ok); }

    /* ---------- Cards ---------- */

    .ax-grid { display: grid; gap: 18px; margin-top: 18px; }
    .ax-g-5-7 { grid-template-columns: minmax(0, 5fr) minmax(0, 7fr); }
    .ax-g-6-6 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .ax-card { padding: 22px; background: var(--ax-surface); border: 1px solid var(--ax-line); border-radius: 20px; box-shadow: var(--ax-shadow); min-width: 0; }
    .ax-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; margin-bottom: 14px; flex-wrap: wrap; }
    .ax-card h3 { margin: 0; font-family: var(--ax-display); font-weight: 700; font-size: 1.08rem; color: var(--ax-ink); }
    .ax-card p.ax-card-sub { margin: 4px 0 0; font-size: .84rem; color: var(--ax-muted); }
    .ax-canvas { position: relative; height: 300px; }
    .ax-canvas.is-round { height: 300px; }
    .ax-nodata { display: grid; place-items: center; gap: 6px; height: 100%; min-height: 160px; border: 1px dashed var(--ax-line-strong); border-radius: 14px; color: var(--ax-muted); text-align: center; font-size: .9rem; }
    .ax-nodata i { font-size: 1.6rem; }

    /* ---------- Coverage / stacked bars ---------- */

    .ax-meter { margin-top: 18px; padding: 18px 20px; background: var(--ax-surface); border: 1px solid var(--ax-line); border-radius: 18px; }
    .ax-meter-top { display: flex; justify-content: space-between; gap: 12px; flex-wrap: wrap; font-size: .9rem; font-weight: 600; color: var(--ax-ink); }
    .ax-meter-top span:last-child { color: var(--ax-muted); font-weight: 500; }
    .ax-meter-bar { display: flex; gap: 3px; height: 12px; margin-top: 12px; border-radius: 999px; overflow: hidden; background: var(--ax-sunken); }
    .ax-meter-bar span { display: block; border-radius: 999px; min-width: 4px; }
    .ax-meter-legend { display: flex; flex-wrap: wrap; gap: 6px 20px; margin-top: 12px; font-size: .84rem; color: var(--ax-muted); }
    .ax-meter-legend b { color: var(--ax-ink); font-variant-numeric: tabular-nums; }
    .ax-warn { margin-top: 10px; font-size: .84rem; color: var(--ax-coral); display: flex; gap: 8px; align-items: center; }

    /* ---------- Tabs ---------- */

    .ax-tabs { display: inline-flex; padding: 4px; gap: 4px; border-radius: 12px; background: var(--ax-sunken); border: 1px solid var(--ax-line); }
    .ax-tab { padding: 7px 14px; border: 0; border-radius: 9px; background: transparent; cursor: pointer; font: 600 .86rem var(--ax-body); color: var(--ax-muted); transition: background .15s, color .15s; }
    .ax-tab[aria-selected="true"] { background: var(--ax-surface); color: var(--ax-ink); box-shadow: var(--ax-shadow); }
    .ax-pane[hidden] { display: none; }
    .ax-callout { display: flex; align-items: center; gap: 12px; margin-top: 16px; padding: 12px 14px; border-radius: 14px; background: var(--ax-teal-soft); color: var(--ax-ink); font-size: .9rem; }
    .ax-callout i { color: var(--ax-teal); font-size: 1.1rem; }

    /* ---------- Table ---------- */

    .ax-table-card { background: var(--ax-surface); border: 1px solid var(--ax-line); border-radius: 20px; box-shadow: var(--ax-shadow); overflow: hidden; }
    .ax-table { width: 100%; border-collapse: collapse; }
    .ax-table th { padding: 13px 18px; text-align: left; font-size: .82rem; font-weight: 700; white-space: nowrap; color: var(--ax-muted); background: var(--ax-sunken); border-bottom: 1px solid var(--ax-line); }
    .ax-table td { padding: 15px 18px; border-bottom: 1px solid var(--ax-line); color: var(--ax-text); font-size: .92rem; }
    .ax-table tbody tr:last-child td { border-bottom: 0; }
    .ax-table tbody tr:hover td { background: var(--ax-sunken); }
    .ax-code { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-weight: 700; color: var(--ax-ink); letter-spacing: .02em; }
    .ax-badge { display: inline-flex; align-items: center; gap: 7px; padding: 4px 11px; border-radius: 999px; font-size: .8rem; font-weight: 700; }
    .ax-badge::before { content: ''; width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
    .ax-badge.is-ok { background: var(--ax-ok-soft); color: var(--ax-ok); }
    .ax-badge.is-info { background: var(--ax-teal-soft); color: var(--ax-teal); }
    .ax-badge.is-warn { background: var(--ax-sun-soft); color: var(--ax-sun); }
    .ax-badge.is-bad { background: var(--ax-coral-soft); color: var(--ax-coral); }
    .ax-empty { padding: 44px 20px; text-align: center; color: var(--ax-muted); }
    .ax-empty i { display: block; font-size: 1.9rem; margin-bottom: 8px; }

    /* ---------- Summary reports CTA ---------- */

    .ax-cta { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 24px; align-items: center; padding: 28px; border-radius: 24px; color: var(--ax-hero-text); background: radial-gradient(120% 140% at 0% 100%, var(--ax-hero-2) 0%, transparent 60%), var(--ax-hero); border: 1px solid color-mix(in srgb, var(--ax-hero-text) 10%, transparent); }
    .ax-cta h3 { margin: 0; font-family: var(--ax-display); font-weight: 800; font-size: 1.4rem; color: #fff; }
    .ax-cta p { margin: 8px 0 0; max-width: 58ch; color: var(--ax-hero-muted); line-height: 1.55; }
    .ax-steps { display: flex; flex-wrap: wrap; gap: 8px; margin: 18px 0 0; padding: 0; list-style: none; counter-reset: step; }
    .ax-steps li { counter-increment: step; display: inline-flex; align-items: center; gap: 9px; padding: 7px 13px 7px 8px; border-radius: 999px; font-size: .86rem; font-weight: 600; background: rgba(255,255,255,.08); color: #fff; }
    .ax-steps li::before { content: counter(step); display: grid; place-items: center; width: 22px; height: 22px; border-radius: 50%; background: #5fd3c6; color: #0b1a20; font-size: .76rem; font-weight: 800; }
    .ax-cta .ax-btn { background: #fff; color: #0b1a20; border-color: #fff; }

    /* ---------- Responsive ---------- */

    @media (max-width: 1199.98px) {
        .ax-filter-form {
            align-items: stretch;
        }
        .ax-filter-field {
            min-width: min(100%, 220px);
            flex: 1 1 180px;
        }
        .ax-filter-actions {
            width: 100%;
        }
        .ax-filter-actions .ax-btn {
            flex: 1 1 auto;
            justify-content: center;
        }

        .ax-shell { grid-template-columns: 1fr; gap: 22px; }
        .ax-rail { position: sticky; top: 0; z-index: 20; margin: 0 -12px; padding: 10px 12px; background: color-mix(in srgb, var(--ax-sunken) 92%, transparent); backdrop-filter: blur(8px); border-bottom: 1px solid var(--ax-line); }
        .ax-rail-title { display: none; }
        .ax-rail nav { display: flex; overflow-x: auto; border-left: 0; gap: 6px; scrollbar-width: none; }
        .ax-rail a { border-left: 0; margin: 0; border-radius: 999px; padding: 8px 14px; white-space: nowrap; background: var(--ax-surface); border: 1px solid var(--ax-line); }
        .ax-rail a.is-current { border-color: var(--ax-teal); }
    }

    @media (max-width: 991.98px) {
        .ax-hero-grid, .ax-g-5-7, .ax-g-6-6, .ax-cta { grid-template-columns: 1fr; }
        .ax-strip { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ax-fig:nth-child(3) { border-left: 0; }
        .ax-fig:nth-child(n+3) { border-top: 1px solid var(--ax-line); }
    }

    @media (max-width: 575.98px) {
        .ax-hero { padding: 22px 18px; }
        .ax-gauges { grid-template-columns: 1fr; }
        .ax-strip { grid-template-columns: 1fr; }
        .ax-fig + .ax-fig { border-left: 0; border-top: 1px solid var(--ax-line); }
        .ax-fig:nth-child(3) { border-top: 1px solid var(--ax-line); }
        .ax-btn, .ax-modules { width: 100%; }
        .ax-module { flex: 1; justify-content: center; padding: 8px 6px; font-size: .8rem; }
        .ax-canvas, .ax-canvas.is-round { height: 260px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .ax-ring .fill { animation: none; }
        .ax-module, .ax-rail a, .ax-tab, .ax-btn { transition: none; }
    }

        /* ============================================================
           PRINT REPORT (A4 / Short / Long / Legal)
           ============================================================ */

    /* ---------- SCREEN ONLY: paper picker + print-only bits ---------- */

    .ax-printer {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .ax-paper {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        min-height: 44px;
        padding: 0 10px 0 14px;
        border-radius: 12px;
        border: 1px solid var(--ax-line-strong);
        background: var(--ax-surface);
        color: var(--ax-muted);
    }

    .ax-paper select {
        border: 0;
        background: transparent;
        color: var(--ax-ink);
        font: 600 .88rem var(--ax-body);
        cursor: pointer;
        padding: 0 4px;
        outline: none;
    }

    .ax-paper select option {
        color: #0e2a33;
        background: #fff;
    }

    .ax-print-pane-title,
    .ax-print-sign,
    .ax-print-img {
        display: none;
    }


    /* ---------- PAGE (size is set by the paper picker via JS) ---------- */

    @page {
        margin: 13mm 13mm 17mm 13mm;
    }

    @page {
        @bottom-left {
            content: "e-Poblacion RMS  \00B7  Barangay Poblacion, Bani, Pangasinan";
            font-family: Arial, sans-serif;
            font-size: 6.8pt;
            color: #6b7c82;
            vertical-align: top;
            padding-top: 4mm;
        }

        @bottom-right {
            content: "Page " counter(page) " of " counter(pages);
            font-family: Arial, sans-serif;
            font-size: 6.8pt;
            font-weight: 700;
            color: #0c3b44;
            vertical-align: top;
            padding-top: 4mm;
        }
    }


    @media print {

        /* ---------- Reset the app shell (sidebar, topbar, wrappers) ---------- */

        html,
        body {
            display: block !important;
            height: auto !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            background: #fff !important;
            color: #2b4149 !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* added by JS: siblings of the report (sidebar, topbar, footer...) */
        .ax-print-hide {
            display: none !important;
        }

        /* added by JS: wrappers that hold the report (margin-left for sidebar etc.) */
        .ax-print-ancestor {
            display: block !important;
            position: static !important;
            float: none !important;
            width: auto !important;
            max-width: none !important;
            height: auto !important;
            min-height: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            transform: none !important;
            background: transparent !important;
            border: 0 !important;
            box-shadow: none !important;
        }

        * {
            box-shadow: none !important;
            text-shadow: none !important;
        }


        /* ---------- Always light tokens on paper ---------- */

        html .ax,
        html[data-theme] .ax {
            --ax-surface: #ffffff;
            --ax-sunken: #f5f8f8;
            --ax-line: #dde5e6;
            --ax-line-strong: #c3cfd1;
            --ax-ink: #0c3b44;
            --ax-text: #2b4149;
            --ax-muted: #62777e;
            --ax-teal: #0f766e;
            --ax-teal-soft: #e2f3f0;
            --ax-sun: #c98c05;
            --ax-sun-soft: #fbf1d3;
            --ax-coral: #d9533a;
            --ax-coral-soft: #fbe7e1;
            --ax-indigo: #4f5fd1;
            --ax-ok: #178a55;
            --ax-ok-soft: #e0f4ea;
            --ax-shadow: none;

            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
            font-size: 9pt;
            line-height: 1.4;
            color: #2b4149 !important;
        }


        /* ---------- Hide interactive / screen-only parts ---------- */

        .ax-rail,
        .ax-head,
        .ax-tabs,
        .ax-cta,
        .ax-about,
        .ax-warn,
        .ax-canvas canvas,
        #ax-population .ax-strip,
        #ax-puroks .ax-card-head {
            display: none !important;
        }

        .ax-shell,
        .ax-main {
            display: block !important;
            width: 100% !important;
            gap: 0 !important;
        }

        .ax-main {
            counter-reset: axch;
        }


        /* ==========================================================
           MASTHEAD
           ========================================================== */

        .ax-print-header {
            display: flex !important;
            align-items: center;
            gap: 4.5mm;
            margin: 0 0 3.5mm;
            padding: 0 0 3.5mm;
            text-align: left;
            border: 0;
            border-bottom: .25mm solid #dde5e6;
        }

        .ax-print-seal {
            display: block;
            flex: none;
            width: 19mm;
            height: 19mm;
            object-fit: contain;
        }

        .ax-print-agency {
            flex: 1;
            min-width: 0;
        }

        .ax-print-republic,
        .ax-print-province,
        .ax-print-municipality {
            margin: 0;
            font: 400 8pt/1.35 Georgia, 'Times New Roman', serif;
            color: #3a4e55 !important;
        }

        .ax-print-republic {
            font-size: 6.8pt;
            letter-spacing: .12em;
            text-transform: uppercase;
        }

        .ax-print-barangay {
            margin: 1mm 0 0;
            font: 700 14pt/1.1 Georgia, 'Times New Roman', serif;
            letter-spacing: .02em;
            text-transform: uppercase;
            color: #0c3b44 !important;
        }

        .ax-print-rms {
            margin: 1mm 0 0;
            font: 700 6.3pt Arial, sans-serif;
            letter-spacing: .22em;
            color: #0f766e !important;
        }

        .ax-print-stamp {
            flex: none;
            display: grid;
            gap: .6mm;
            padding: 2.2mm 3.5mm;
            text-align: right;
            border-right: 1mm solid #0f766e;
            background: #f5f8f8 !important;
        }

        .ax-print-stamp span {
            font: 700 6pt Arial, sans-serif;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: #62777e !important;
        }

        .ax-print-stamp strong {
            font: 800 11pt/1.1 'Bricolage Grotesque', Arial, sans-serif;
            color: #0c3b44 !important;
        }

        .ax-print-stamp em {
            font: 600 6.5pt Arial, sans-serif;
            font-style: normal;
            color: #62777e !important;
        }

        .ax-print-titleband {
            display: block !important;
            position: relative;
            margin: 0 0 4.5mm;
            padding: 4.2mm 0 0;
        }

        .ax-print-titleband::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1.3mm;
            background: linear-gradient(90deg,
                #0f766e 0, #0f766e 34%,
                #e9a80c 34%, #e9a80c 67%,
                #e0573a 67%, #e0573a 100%) !important;
        }

        .ax-print-title {
            margin: 0;
            font: 800 19pt/1.08 'Bricolage Grotesque', Arial, sans-serif;
            letter-spacing: -.012em;
            color: #0c3b44 !important;
        }

        .ax-print-subtitle {
            margin: 1.2mm 0 0;
            font: 400 8pt/1.4 'Figtree', Arial, sans-serif;
            color: #62777e !important;
        }

        .ax-print-meta {
            display: flex !important;
            justify-content: space-between;
            gap: 6mm;
            margin-top: 3mm;
            padding-top: 2mm;
            border-top: .25mm solid #dde5e6;
            font: 400 7pt Arial, sans-serif;
            color: #62777e !important;
        }

        .ax-print-meta strong {
            color: #0c3b44 !important;
        }


        /* ==========================================================
           KPI SUMMARY
           ========================================================== */

        .ax-print-summary {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 3mm;
            margin: 0 0 5mm;
        }

        .ax-print-summary-item {
            position: relative;
            padding: 3.6mm 3.6mm 3mm;
            border: .25mm solid #dde5e6;
            border-radius: 1.5mm;
            background: #fff !important;
            overflow: hidden;
        }

        .ax-print-summary-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1mm;
            background: var(--k, #0f766e) !important;
        }

        .ax-print-summary-item:nth-child(1) { --k: #0f766e; }
        .ax-print-summary-item:nth-child(2) { --k: #e9a80c; }
        .ax-print-summary-item:nth-child(3) { --k: #e0573a; }
        .ax-print-summary-item:nth-child(4) { --k: #4f5fd1; }

        .ax-print-summary-label {
            margin: 0;
            font: 700 6.5pt Arial, sans-serif;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: #62777e !important;
        }

        .ax-print-summary-value {
            margin: 1.6mm 0 0;
            font: 800 21pt/1 'Bricolage Grotesque', Arial, sans-serif;
            letter-spacing: -.02em;
            color: #0c3b44 !important;
        }

        .ax-print-summary-note {
            margin: 1.4mm 0 0;
            font: 400 6.8pt Arial, sans-serif;
            color: #62777e !important;
        }


        /* ==========================================================
           CHAPTERS
           ========================================================== */

        .ax-chapter {
            counter-increment: axch;
            display: block !important;
            margin: 0 0 6mm !important;
            padding: 0 !important;
        }

        #ax-overview {
            counter-increment: none;
        }

        #ax-households,
        #ax-complaints,
        #ax-activity {
            break-before: page;
            page-break-before: always;
        }

        .ax-chapter-head {
            position: relative;
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 6mm;
            margin: 0 0 3.6mm !important;
            padding: 0 0 2.2mm !important;
            border-bottom: .25mm solid #dde5e6;
            break-after: avoid;
            page-break-after: avoid;
        }

        .ax-chapter-head::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -.25mm;
            width: 24mm;
            height: .7mm;
            background: #0f766e !important;
        }

        .ax-h2 {
            display: flex !important;
            align-items: center;
            gap: 2.6mm;
            margin: 0 !important;
            font: 800 12pt/1.1 'Bricolage Grotesque', Arial, sans-serif !important;
            letter-spacing: -.005em;
            color: #0c3b44 !important;
        }

        .ax-h2 i {
            display: none !important;
        }

        .ax-h2::before {
            content: counter(axch, decimal-leading-zero);
            display: inline-block;
            padding: .9mm 2mm;
            border-radius: 1mm;
            background: #0c3b44 !important;
            color: #fff !important;
            font: 700 7.5pt ui-monospace, Consolas, monospace;
            letter-spacing: .06em;
        }

        .ax-lede {
            max-width: 60% !important;
            margin: 0 !important;
            text-align: right;
            font-size: 7.2pt !important;
            color: #62777e !important;
        }


        /* ==========================================================
           OVERVIEW (hero)
           ========================================================== */

        .ax-hero {
            padding: 4.5mm !important;
            border-radius: 1.5mm !important;
            border: .25mm solid #dde5e6 !important;
            border-left: 1.4mm solid #0c3b44 !important;
            background: #fff !important;
            color: #2b4149 !important;
            break-inside: avoid;
        }

        .ax-hero-grid {
            display: grid !important;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.5fr) !important;
            gap: 6mm !important;
            align-items: center;
        }

        .ax-hero-kicker {
            font: 700 6.5pt Arial, sans-serif !important;
            letter-spacing: .09em;
            text-transform: uppercase;
            color: #62777e !important;
        }

        .ax-hero-number {
            margin: 1mm 0 .6mm !important;
            font-size: 32pt !important;
            color: #0c3b44 !important;
        }

        .ax-hero-note {
            font-size: 7.6pt !important;
            color: #62777e !important;
        }

        .ax-split {
            margin-top: 3.4mm !important;
        }

        .ax-split-bar {
            height: 2.6mm !important;
            gap: .6mm !important;
            background: #e8eeee !important;
        }

        .ax-split-male   { background: #0f766e !important; }
        .ax-split-female { background: #e0573a !important; }
        .ax-split-other  { background: #e9a80c !important; }

        .ax-split-legend {
            margin-top: 2mm !important;
            gap: 1.5mm 6mm !important;
            font-size: 7.4pt !important;
            color: #62777e !important;
        }

        .ax-split-legend b {
            color: #0c3b44 !important;
        }

        .ax-gauges {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 2.6mm !important;
        }

        .ax-gauge {
            gap: 3mm !important;
            padding: 2.8mm !important;
            border-radius: 1.5mm !important;
            background: #f9fbfb !important;
            border: .25mm solid #dde5e6 !important;
        }

        .ax-gauge:nth-child(1) { --pc: #0f766e; }
        .ax-gauge:nth-child(2) { --pc: #d99a06; }
        .ax-gauge:nth-child(3) { --pc: #e0573a; }
        .ax-gauge:nth-child(4) { --pc: #4f5fd1; }

        .ax-ring {
            width: 14mm !important;
            height: 14mm !important;
        }

        .ax-ring .track {
            stroke: #dde5e6 !important;
        }

        .ax-ring .fill {
            stroke: var(--pc) !important;
            animation: none !important;
        }

        .ax-ring-num {
            font-size: 7.2pt !important;
            color: #0c3b44 !important;
        }

        .ax-gauge-label {
            font-size: 7.6pt !important;
            color: #0c3b44 !important;
        }

        .ax-gauge-sub {
            font-size: 6.6pt !important;
            color: #62777e !important;
        }


        /* ==========================================================
           STAT STRIPS, METERS, CARDS
           ========================================================== */

        .ax-strip {
            display: grid !important;
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            border-radius: 1.5mm !important;
            border: .25mm solid #dde5e6 !important;
            background: #fff !important;
            overflow: hidden;
            break-inside: avoid;
        }

        .ax-fig {
            position: relative;
            padding: 3.6mm 3.6mm 3mm !important;
            border-top: 0 !important;
        }

        .ax-fig + .ax-fig {
            border-left: .25mm solid #dde5e6 !important;
        }

        .ax-fig::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: .9mm;
            background: var(--tone, #0f766e) !important;
        }

        .ax-fig-label {
            font: 700 6.5pt Arial, sans-serif !important;
            letter-spacing: .07em;
            text-transform: uppercase;
            color: #62777e !important;
        }

        .ax-fig-label i {
            display: none !important;
        }

        .ax-fig-value {
            margin-top: 1.6mm !important;
            font-size: 18pt !important;
            color: #0c3b44 !important;
        }

        .ax-fig-desc {
            margin-top: 1.2mm !important;
            font-size: 6.6pt !important;
            color: #62777e !important;
        }

        .ax-meter {
            margin-top: 4mm !important;
            padding: 3.6mm 4mm !important;
            border-radius: 1.5mm !important;
            border: .25mm solid #dde5e6 !important;
            background: #fff !important;
            break-inside: avoid;
        }

        .ax-meter-top {
            font-size: 7.6pt !important;
            color: #0c3b44 !important;
        }

        .ax-meter-bar {
            height: 3mm !important;
            margin-top: 2.2mm !important;
            background: #eef2f2 !important;
        }

        .ax-meter-legend {
            margin-top: 2.2mm !important;
            font-size: 7pt !important;
            color: #62777e !important;
        }

        .ax-grid {
            display: grid !important;
            gap: 4mm !important;
            margin-top: 4mm !important;
        }

        .ax-g-5-7 {
            grid-template-columns: minmax(0, 5fr) minmax(0, 7fr) !important;
        }

        .ax-g-6-6 {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        }

        .ax-card {
            padding: 3.5mm !important;
            border-radius: 1.5mm !important;
            border: .25mm solid #dde5e6 !important;
            background: #fff !important;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .ax-card-head {
            margin-bottom: 2.4mm !important;
        }

        .ax-card h3 {
            font-size: 9pt !important;
            color: #0c3b44 !important;
        }

        .ax-card p.ax-card-sub {
            margin-top: .8mm !important;
            font-size: 6.8pt !important;
            color: #62777e !important;
        }


        /* ---------- Charts (JS swaps each canvas for a crisp image) ---------- */

        .ax-canvas,
        .ax-canvas.is-round {
            height: 52mm !important;
            min-height: 0 !important;
        }

        #ax-puroks .ax-canvas {
            height: 58mm !important;
        }

        #ax-relief .ax-canvas {
            height: 42mm !important;
        }

        .ax-print-img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: contain;
        }


        /* ---------- Purok: show BOTH views side by side ---------- */

        #ax-puroks > .ax-card {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 5mm;
        }

        #ax-puroks .ax-pane,
        #ax-puroks .ax-pane[hidden] {
            display: block !important;
            min-width: 0;
        }

        .ax-print-pane-title {
            display: block !important;
            margin: 0 0 2mm;
            font: 700 8.6pt 'Bricolage Grotesque', Arial, sans-serif;
            color: #0c3b44 !important;
        }

        .ax-callout {
            margin-top: 2.4mm !important;
            padding: 2mm 3mm !important;
            border-radius: 1mm !important;
            background: #e2f3f0 !important;
            font-size: 7.4pt !important;
            color: #0c3b44 !important;
        }

        .ax-callout i {
            font-size: .9rem;
            color: #0f766e !important;
        }


        /* ==========================================================
           TABLE
           ========================================================== */

        .ax-table-card {
            border-radius: 1.5mm !important;
            border: .25mm solid #dde5e6 !important;
            background: #fff !important;
            overflow: visible !important;
        }

        .ax-table-card .table-responsive {
            overflow: visible !important;
        }

        .ax-table {
            width: 100%;
        }

        .ax-table thead {
            display: table-header-group;
        }

        .ax-table tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .ax-table th {
            padding: 2.4mm 3mm !important;
            background: #eef3f3 !important;
            border-bottom: .3mm solid #c3cfd1 !important;
            font: 700 6.6pt Arial, sans-serif !important;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #0c3b44 !important;
        }

        .ax-table td {
            padding: 2.3mm 3mm !important;
            border-bottom: .2mm solid #e3e9ea !important;
            font-size: 7.6pt !important;
            color: #2b4149 !important;
        }

        .ax-table tbody tr:nth-child(even) td {
            background: #fafcfc !important;
        }

        .ax-code {
            font-size: 7.2pt !important;
            color: #0c3b44 !important;
        }

        .ax-badge {
            padding: .8mm 2.4mm !important;
            font-size: 6.6pt !important;
        }

        .ax-badge::before {
            width: 1.4mm !important;
            height: 1.4mm !important;
        }

        .ax-empty {
            padding: 10mm 0 !important;
            font-size: 8pt;
        }

        .ax-empty i {
            display: none !important;
        }


        /* ==========================================================
           SIGN-OFF
           ========================================================== */

        .ax-print-sign {
            display: block !important;
            margin-top: 12mm;
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .ax-print-sign-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18mm;
        }

        .ax-print-sign-box {
            padding-top: 11mm;
            text-align: center;
        }

        .ax-sign-line {
            border-top: .3mm solid #0c3b44;
            margin-bottom: 1.6mm;
        }

        .ax-print-sign-box strong {
            display: block;
            font: 700 7.6pt Arial, sans-serif;
            color: #0c3b44 !important;
        }

        .ax-print-sign-box span {
            font: 400 7pt Arial, sans-serif;
            color: #62777e !important;
        }

        .ax-print-note {
            margin: 9mm 0 0;
            padding-top: 2.4mm;
            border-top: .25mm solid #dde5e6;
            font: 400 6.6pt/1.5 Arial, sans-serif;
            color: #62777e !important;
            text-align: center;
        }
    }

</style>

<style>
/* ==========================================================================
   MODERN REDESIGN (screen only). Everything below is inside @media screen,
   so the print report above is untouched.
   ========================================================================== */
@media screen {

    /* ---------- Tokens (the charts read these too) ---------- */
    .ax {
        --ax-surface: #ffffff;
        --ax-sunken: #f4f7f7;
        --ax-line: #e3eaea;
        --ax-line-strong: #cbd8d8;
        --ax-ink: #0a2430;
        --ax-text: #35525c;
        --ax-muted: #6a858d;

        --ax-teal: #0f766e;
        --ax-teal-soft: #dcf3ef;
        --ax-sun: #e8a30b;
        --ax-sun-soft: #fcf0cf;
        --ax-coral: #e8553d;
        --ax-coral-soft: #fde5df;
        --ax-indigo: #5565e8;
        --ax-ok: #16995f;
        --ax-ok-soft: #dcf5e8;

        --ax-focus: rgba(15, 118, 110, .28);
        --ax-shadow: 0 1px 2px rgba(10, 36, 48, .04), 0 14px 34px -14px rgba(10, 36, 48, .16);
        --ax-shadow-lg: 0 2px 4px rgba(10, 36, 48, .05), 0 26px 50px -18px rgba(10, 36, 48, .28);
    }

    [data-theme="dark"] .ax {
        --ax-surface: #12242b;
        --ax-sunken: #0d1c22;
        --ax-line: #22404a;
        --ax-line-strong: #30535e;
        --ax-ink: #eef7f6;
        --ax-text: #c6dadd;
        --ax-muted: #8ba6ad;
        --ax-teal: #3fd6c4;
        --ax-teal-soft: #11393a;
        --ax-sun: #f5c451;
        --ax-sun-soft: #3a3116;
        --ax-coral: #ff8b70;
        --ax-coral-soft: #3d211a;
        --ax-indigo: #98a4ff;
        --ax-ok: #5fe0a3;
        --ax-ok-soft: #123a28;
        --ax-focus: rgba(63, 214, 196, .35);
        --ax-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 14px 34px -14px rgba(0, 0, 0, .55);
        --ax-shadow-lg: 0 2px 4px rgba(0, 0, 0, .45), 0 26px 50px -18px rgba(0, 0, 0, .7);
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .ax {
            --ax-surface: #12242b;
            --ax-sunken: #0d1c22;
            --ax-line: #22404a;
            --ax-line-strong: #30535e;
            --ax-ink: #eef7f6;
            --ax-text: #c6dadd;
            --ax-muted: #8ba6ad;
            --ax-teal: #3fd6c4;
            --ax-teal-soft: #11393a;
            --ax-sun: #f5c451;
            --ax-sun-soft: #3a3116;
            --ax-coral: #ff8b70;
            --ax-coral-soft: #3d211a;
            --ax-indigo: #98a4ff;
            --ax-ok: #5fe0a3;
            --ax-ok-soft: #123a28;
            --ax-focus: rgba(63, 214, 196, .35);
            --ax-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 14px 34px -14px rgba(0, 0, 0, .55);
            --ax-shadow-lg: 0 2px 4px rgba(0, 0, 0, .45), 0 26px 50px -18px rgba(0, 0, 0, .7);
        }
    }

    /* ---------- Entrance motion ---------- */
    @keyframes ax-rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
    .ax .ax-head, .ax .ax-filter-card, .ax .ax-chapter, .ax .ax-cta {
        animation: ax-rise .6s cubic-bezier(.2, .7, .2, 1) both;
    }
    .ax .ax-filter-card { animation-delay: .05s; }
    .ax .ax-main > .ax-chapter:nth-child(2) { animation-delay: .06s; }
    .ax .ax-main > .ax-chapter:nth-child(3) { animation-delay: .1s; }

    /* ---------- Header ---------- */
    .ax .ax-head { margin-bottom: 22px; align-items: flex-end; }
    .ax .ax-head > div:first-child::before {
        content: 'Barangay Poblacion  \00B7  Insights';
        display: inline-flex; align-items: center; margin-bottom: 12px; padding: 5px 12px; border-radius: 999px;
        font: 700 .72rem var(--ax-body); letter-spacing: .08em; text-transform: uppercase;
        color: var(--ax-teal); background: var(--ax-teal-soft);
        box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--ax-teal) 22%, transparent);
    }
    .ax .ax-title { font-size: clamp(2rem, 3.8vw, 3rem); letter-spacing: -.03em; }
    .ax .ax-sub { font-size: .97rem; line-height: 1.55; }

    .ax .ax-modules {
        padding: 5px; gap: 4px; border-radius: 16px;
        background: var(--ax-surface); border: 1px solid var(--ax-line); box-shadow: var(--ax-shadow);
    }
    .ax .ax-module { padding: 9px 16px; border-radius: 12px; font-weight: 700; }
    .ax .ax-module:hover { background: var(--ax-sunken); }
    .ax .ax-module.is-active { background: var(--ax-ink); color: var(--ax-surface); box-shadow: none; }
    .ax .ax-module.is-active:hover { background: var(--ax-ink); }

    .ax .ax-paper {
        min-height: 46px; border-radius: 14px; background: var(--ax-surface); border: 1px solid var(--ax-line);
        box-shadow: var(--ax-shadow); color: var(--ax-teal);
    }
    .ax .ax-btn { min-height: 46px; padding: 0 20px; border-radius: 14px; font-weight: 700; }
    .ax #axPrintBtn {
        color: #fff; border-color: transparent; background: linear-gradient(135deg, #0f9488, #0b6b63);
        box-shadow: 0 10px 22px -10px rgba(15, 118, 110, .7);
    }
    .ax #axPrintBtn:hover { color: #fff; filter: brightness(1.08); transform: translateY(-1px); }

    /* ---------- Filter bar ---------- */
    .ax .ax-filter-card { margin-bottom: 30px; padding: 16px 18px; border-radius: 22px; box-shadow: var(--ax-shadow); }
    .ax .ax-filter-field input {
        min-height: 46px; border-radius: 12px; background: var(--ax-sunken); border: 1px solid var(--ax-line-strong);
        font-weight: 600; transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .ax .ax-filter-field input:focus { outline: none; background: var(--ax-surface); border-color: var(--ax-teal); box-shadow: 0 0 0 4px var(--ax-focus); }
    .ax .ax-filter-note { display: flex; align-items: center; gap: 8px; margin-top: 12px; padding-top: 12px; border-top: 1px dashed var(--ax-line-strong); }
    .ax .ax-filter-note i { color: var(--ax-teal); }

    /* ---------- Side index (desktop) ---------- */
    @media (min-width: 1200px) {
        .ax .ax-shell { grid-template-columns: 210px minmax(0, 1fr); gap: 36px; }
        .ax .ax-rail-title { margin-left: 8px; letter-spacing: .06em; text-transform: uppercase; font-size: .72rem; font-weight: 700; }
        .ax .ax-rail nav {
            gap: 3px; padding: 8px; border-left: 0; border-radius: 20px;
            background: var(--ax-surface); border: 1px solid var(--ax-line); box-shadow: var(--ax-shadow);
        }
        .ax .ax-rail a { margin: 0; padding: 10px 12px; border: 0; border-radius: 13px; font-weight: 600; }
        .ax .ax-rail a:hover { background: var(--ax-sunken); }
        .ax .ax-rail a.is-current { background: var(--ax-teal-soft); color: var(--ax-teal); }
        .ax .ax-rail a.is-current i { color: var(--ax-teal); }
    }

    /* ---------- Chapter headings ---------- */
    .ax .ax-main { gap: 64px; }
    .ax .ax-chapter-head { margin-bottom: 22px; }
    .ax .ax-h2 { font-size: 1.65rem; letter-spacing: -.02em; }
    .ax .ax-h2 i {
        width: 42px; height: 42px; border-radius: 14px; font-size: 1.15rem;
        background: var(--ax-teal-soft); color: var(--ax-teal);
        box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--ax-teal) 22%, transparent);
    }
    .ax .ax-lede { font-size: .93rem; line-height: 1.5; }

    /* ---------- Hero ---------- */
    .ax .ax-hero {
        padding: 36px; border-radius: 30px; border: 1px solid rgba(255, 255, 255, .08); box-shadow: var(--ax-shadow-lg);
        background:
            radial-gradient(60% 90% at 100% 0%, rgba(20, 184, 166, .38), transparent 62%),
            radial-gradient(50% 80% at 0% 100%, rgba(232, 85, 61, .24), transparent 62%),
            linear-gradient(135deg, #082029, #0e3340);
    }
    .ax .ax-hero::before {
        content: ''; position: absolute; inset: 0; pointer-events: none; opacity: .55;
        background-image: radial-gradient(rgba(255, 255, 255, .14) 1px, transparent 1.4px); background-size: 20px 20px;
        -webkit-mask-image: linear-gradient(110deg, transparent 30%, #000 100%); mask-image: linear-gradient(110deg, transparent 30%, #000 100%);
    }
    .ax .ax-hero > * { position: relative; }
    .ax .ax-hero-kicker { font-weight: 700; letter-spacing: .08em; text-transform: uppercase; font-size: .78rem; color: #8fc9c4; }
    .ax .ax-hero-number {
        font-size: clamp(3.6rem, 8vw, 6rem); letter-spacing: -.045em;
        background: linear-gradient(180deg, #ffffff 20%, #a4ece3); -webkit-background-clip: text; background-clip: text;
        -webkit-text-fill-color: transparent; color: transparent;
    }
    .ax .ax-hero-note { color: #a9c6ca; font-size: 1rem; max-width: 40ch; line-height: 1.5; }
    .ax .ax-split-bar { height: 14px; gap: 4px; background: rgba(255, 255, 255, .1); }
    .ax .ax-split-male { background: linear-gradient(90deg, #2dd4bf, #5fe0cf); }
    .ax .ax-split-female { background: linear-gradient(90deg, #ff8b70, #ffab96); }
    .ax .ax-split-legend { color: #a9c6ca; }

    .ax .ax-gauges { gap: 14px; }
    .ax .ax-gauge {
        padding: 16px; border-radius: 22px; background: rgba(255, 255, 255, .07); border: 1px solid rgba(255, 255, 255, .13);
        -webkit-backdrop-filter: blur(8px); backdrop-filter: blur(8px); transition: transform .2s, background .2s, border-color .2s;
    }
    .ax .ax-gauge:hover { transform: translateY(-3px); background: rgba(255, 255, 255, .11); border-color: rgba(255, 255, 255, .22); }
    .ax .ax-ring { width: 72px; height: 72px; }
    .ax .ax-ring circle { stroke-width: 8; }
    .ax .ax-ring-num { font-size: 1rem; }
    .ax .ax-gauge-sub { color: #a9c6ca; }

    .ax .ax-about {
        margin-top: 20px; padding: 16px 18px; border-radius: 18px; background: var(--ax-surface);
        border: 1px solid var(--ax-line); box-shadow: var(--ax-shadow);
    }
    .ax .ax-about i { color: var(--ax-teal); font-size: 1.15rem; }

    /* ---------- KPI cards (each figure is its own card) ---------- */
    .ax .ax-strip { gap: 16px; background: transparent; border: 0; border-radius: 0; box-shadow: none; overflow: visible; }
    .ax .ax-fig, .ax .ax-fig + .ax-fig, .ax .ax-fig:nth-child(n) {
        position: relative; overflow: hidden; padding: 22px; border: 1px solid var(--ax-line); border-radius: 22px;
        background: var(--ax-surface); box-shadow: var(--ax-shadow); transition: transform .2s, box-shadow .2s, border-color .2s;
    }
    .ax .ax-fig:hover { transform: translateY(-4px); box-shadow: var(--ax-shadow-lg); border-color: color-mix(in srgb, var(--tone, var(--ax-teal)) 40%, var(--ax-line)); }
    .ax .ax-fig::before {
        content: ''; position: absolute; left: 22px; top: 0; width: 44px; height: 4px; border-radius: 0 0 6px 6px;
        background: var(--tone, var(--ax-teal));
    }
    .ax .ax-fig::after {
        content: ''; position: absolute; right: -34px; bottom: -42px; width: 130px; height: 130px; border-radius: 50%;
        background: var(--tone, var(--ax-teal)); opacity: .08; pointer-events: none;
    }
    .ax .ax-fig-label { flex-direction: row-reverse; justify-content: space-between; align-items: flex-start; font-size: .86rem; font-weight: 700; }
    .ax .ax-fig-label i {
        display: grid; place-items: center; width: 40px; height: 40px; border-radius: 13px; font-size: 1.15rem;
        color: var(--tone, var(--ax-teal)); background: color-mix(in srgb, var(--tone, var(--ax-teal)) 14%, transparent);
    }
    .ax .ax-fig-value { margin-top: 14px; font-size: 2.5rem; letter-spacing: -.035em; }
    .ax .ax-fig-desc { margin-top: 8px; }

    /* ---------- Meters ---------- */
    .ax .ax-meter { padding: 22px 24px; border-radius: 22px; box-shadow: var(--ax-shadow); }
    .ax .ax-meter-top { font-size: .95rem; }
    .ax .ax-meter-bar { height: 14px; gap: 4px; margin-top: 14px; background: var(--ax-sunken); }
    .ax .ax-warn { padding: 10px 14px; border-radius: 12px; background: var(--ax-coral-soft); }

    /* ---------- Chart cards ---------- */
    .ax .ax-grid { gap: 20px; margin-top: 20px; }
    .ax .ax-card { padding: 24px; border-radius: 24px; box-shadow: var(--ax-shadow); transition: box-shadow .2s, transform .2s; }
    .ax .ax-card:hover { box-shadow: var(--ax-shadow-lg); }
    .ax .ax-card h3 { font-size: 1.12rem; letter-spacing: -.01em; }
    .ax .ax-nodata { border-radius: 18px; background: var(--ax-sunken); }

    /* ---------- Tabs ---------- */
    .ax .ax-tabs { padding: 4px; border-radius: 14px; background: var(--ax-sunken); }
    .ax .ax-tab { padding: 8px 16px; border-radius: 10px; font-weight: 700; }
    .ax .ax-tab:hover { color: var(--ax-ink); }
    .ax .ax-tab[aria-selected="true"] { background: var(--ax-ink); color: var(--ax-surface); box-shadow: none; }
    .ax .ax-callout {
        margin-top: 18px; padding: 14px 16px; border-radius: 16px;
        background: linear-gradient(90deg, var(--ax-teal-soft), transparent 90%);
        box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--ax-teal) 18%, transparent);
    }

    /* ---------- Table ---------- */
    .ax .ax-table-card { border-radius: 24px; box-shadow: var(--ax-shadow); }
    .ax .ax-table th {
        padding: 15px 20px; font-size: .74rem; letter-spacing: .07em; text-transform: uppercase; font-weight: 800;
    }
    .ax .ax-table td { padding: 16px 20px; }
    .ax .ax-table tbody tr { transition: background .15s; }
    .ax .ax-code {
        display: inline-block; padding: 5px 11px; border-radius: 9px; font-size: .84rem;
        background: var(--ax-sunken); border: 1px solid var(--ax-line);
    }
    .ax .ax-badge { padding: 5px 12px; font-size: .78rem; }
    .ax .ax-empty { padding: 56px 20px; }
    .ax .ax-empty i { font-size: 2.2rem; color: var(--ax-teal); opacity: .8; }

    /* ---------- Call to action ---------- */
    .ax .ax-cta {
        padding: 34px; border-radius: 30px; border: 1px solid rgba(255, 255, 255, .08); box-shadow: var(--ax-shadow-lg);
        background:
            radial-gradient(55% 90% at 0% 100%, rgba(20, 184, 166, .34), transparent 62%),
            radial-gradient(45% 80% at 100% 0%, rgba(232, 85, 61, .22), transparent 62%),
            linear-gradient(135deg, #082029, #0e3340);
    }
    .ax .ax-cta h3 { font-size: 1.6rem; letter-spacing: -.02em; }
    .ax .ax-steps li { background: rgba(255, 255, 255, .09); border: 1px solid rgba(255, 255, 255, .12); }
    .ax .ax-cta .ax-btn { min-height: 50px; padding: 0 24px; box-shadow: 0 14px 28px -12px rgba(0, 0, 0, .55); }
    .ax .ax-cta .ax-btn::after { content: '\2192'; font-weight: 800; transition: transform .2s; }
    .ax .ax-cta .ax-btn:hover { color: #0b1a20; filter: none; transform: translateY(-2px); }
    .ax .ax-cta .ax-btn:hover::after { transform: translateX(4px); }

    /* ---------- Small screens ---------- */
    @media (max-width: 575.98px) {
        .ax .ax-hero { padding: 24px 18px; border-radius: 24px; }
        .ax .ax-cta { padding: 24px 20px; border-radius: 24px; }
        .ax .ax-fig-value { font-size: 2.1rem; }
        .ax .ax-main { gap: 48px; }
    }

    @media (prefers-reduced-motion: reduce) {
        .ax .ax-head, .ax .ax-filter-card, .ax .ax-chapter, .ax .ax-cta { animation: none; }
        .ax .ax-fig, .ax .ax-gauge, .ax .ax-card, .ax .ax-cta .ax-btn::after { transition: none; }
        .ax .ax-fig:hover, .ax .ax-gauge:hover { transform: none; }
    }
}
</style>
@endpush


@section('content')

@php
    /* Safe percentage helper: avoids division by zero */
    $pct = fn ($part, $whole) => $whole > 0 ? (int) round(($part / $whole) * 100) : 0;

    $totalResidents = (int) $residentStats['total'];
    $maleCount      = (int) $residentStats['male'];
    $femaleCount    = (int) $residentStats['female'];
    $otherCount     = max(0, $totalResidents - $maleCount - $femaleCount);

    $mappedPct    = $pct($householdStats['mapped'], $householdStats['total']);
    $activeResPct = $pct($residentStats['active'], $residentStats['total']);
    $handledPct   = $pct($complaintStats['resolved'] + $complaintStats['closed'], $complaintStats['total']);
    $claimedPct   = $pct($voucherStats['claimed'], $voucherStats['total']);

    $topHouseholdPurok = collect($householdsByPurok)->sortByDesc('total')->first();
    $topPopulationPurok = collect($populationByPurok)->sortByDesc('total')->first();

    $filterFrom = $from ?? null;
    $filterTo   = $to ?? null;

    $hasAnalyticsFilter = $filterFrom && $filterTo;

    $analyticsCoverage = $hasAnalyticsFilter
        ? $filterFrom->format('M j, Y') . ' – ' . $filterTo->format('M j, Y')
        : 'Current system records';
@endphp


<div class="container-fluid py-4 ax">

    {{-- PRINT-ONLY: official barangay report header --}}
    <div class="ax-print-header">

        <img class="ax-print-seal"
             src="{{ asset('icon.png') }}"
             alt=""
             onerror="this.style.display='none'">

        <div class="ax-print-agency">
            <p class="ax-print-republic">Republic of the Philippines</p>
            <p class="ax-print-province">Province of Pangasinan</p>
            <p class="ax-print-municipality">Municipality of Bani</p>
            <p class="ax-print-barangay">Barangay Poblacion</p>
            <p class="ax-print-rms">RECORD MANAGEMENT SYSTEM</p>
        </div>

        <div class="ax-print-stamp">
            <span>Report date</span>
            <strong>{{ now()->format('M j, Y') }}</strong>
            <em>System-generated</em>
        </div>

    </div>

    <div class="ax-print-titleband">

        <h1 class="ax-print-title">
            Barangay Monitoring &amp; Analytics Report
        </h1>

        <p class="ax-print-subtitle">
            Consolidated statistical overview of records maintained in the
            e-Poblacion Record Management System
        </p>

        <div class="ax-print-meta">
            <span><strong>Generated:</strong> {{ now()->format('F j, Y h:i A') }}</span>
            <span><strong>Coverage:</strong> {{ $analyticsCoverage }}</span>
            <span><strong>Prepared from:</strong> e-Poblacion RMS</span>
        </div>

    </div>

    {{-- PRINT-ONLY: executive summary --}}
    <div class="ax-print-summary">

        <div class="ax-print-summary-item">
            <p class="ax-print-summary-label">Residents</p>
            <div class="ax-print-summary-value">{{ number_format($residentStats['total']) }}</div>
            <p class="ax-print-summary-note">{{ number_format($residentStats['active']) }} active records</p>
        </div>

        <div class="ax-print-summary-item">
            <p class="ax-print-summary-label">Households</p>
            <div class="ax-print-summary-value">{{ number_format($householdStats['total']) }}</div>
            <p class="ax-print-summary-note">{{ number_format($householdStats['mapped']) }} mapped households</p>
        </div>

        <div class="ax-print-summary-item">
            <p class="ax-print-summary-label">Reports &amp; Complaints</p>
            <div class="ax-print-summary-value">{{ number_format($complaintStats['total']) }}</div>
            <p class="ax-print-summary-note">{{ number_format($complaintStats['resolved'] + $complaintStats['closed']) }} resolved or closed</p>
        </div>

        <div class="ax-print-summary-item">
            <p class="ax-print-summary-label">Relief Vouchers</p>
            <div class="ax-print-summary-value">{{ number_format($voucherStats['total']) }}</div>
            <p class="ax-print-summary-note">{{ number_format($voucherStats['claimed']) }} claimed</p>
        </div>

    </div>


    {{-- NORMAL SCREEN HEADER --}}
    <header class="ax-head">

        <div>
            <h1 class="ax-title">Reports &amp; Analytics</h1>

            <p class="ax-sub">
                A snapshot of Barangay Poblacion as of {{ now()->format('F j, Y') }}:
                residents, households, complaints, and relief assistance.
                @if ($hasAnalyticsFilter)
                    Activity filter: {{ $analyticsCoverage }}.
                @endif
            </p>
        </div>

        <div class="ax-head-side">

            <nav class="ax-modules" aria-label="Reports modules">

                <a href="{{ route('reports-analytics.index') }}"
                   class="ax-module is-active"
                   aria-current="page">
                    <i class="bi bi-graph-up-arrow"></i>
                    Analytics
                </a>

                @if (Route::has('summary-reports.index'))
                    <a href="{{ route('summary-reports.index') }}" class="ax-module">
                        <i class="bi bi-file-earmark-text"></i>
                        Summary Reports
                    </a>
                @else
                    <a href="#"
                       class="ax-module is-disabled"
                       onclick="return false;"
                       title="Summary Reports route is currently being configured.">
                        <i class="bi bi-file-earmark-text"></i>
                        Summary Reports
                    </a>
                @endif

                @if (Route::has('audit-logs.index') &&
                    strtolower(trim((string) auth()->user()->role)) === 'captain')
                    <a href="{{ route('audit-logs.index') }}" class="ax-module">
                        <i class="bi bi-shield-check"></i>
                        Audit Trail
                    </a>
                @endif

            </nav>

            <div class="ax-printer">

                <label class="ax-paper" title="Paper size">
                    <i class="bi bi-file-earmark-ruled"></i>
                    <select id="axPaper" aria-label="Paper size">
                        <option value="a4">A4 &middot; 210 &times; 297 mm</option>
                        <option value="short">Short &middot; 8.5 &times; 11 in</option>
                        <option value="long">Long &middot; 8.5 &times; 13 in</option>
                        <option value="legal">Legal &middot; 8.5 &times; 14 in</option>
                    </select>
                </label>

                <a href="#" class="ax-btn" id="axPrintBtn">
                    <i class="bi bi-printer"></i>
                    Print analytics
                </a>

            </div>

        </div>
    </header>


    {{-- ANALYTICS DATE FILTER --}}
    <section class="ax-filter-card" aria-label="Analytics date filter">

        <form method="GET" action="{{ route('reports-analytics.index') }}" class="ax-filter-form">

            <div class="ax-filter-field">
                <label for="analyticsFrom">From</label>
                <input
                    type="date"
                    id="analyticsFrom"
                    name="from"
                    value="{{ $filterFrom?->format('Y-m-d') }}"
                >
            </div>

            <div class="ax-filter-field">
                <label for="analyticsTo">To</label>
                <input
                    type="date"
                    id="analyticsTo"
                    name="to"
                    value="{{ $filterTo?->format('Y-m-d') }}"
                >
            </div>

            <div class="ax-filter-actions">
                <button type="submit" class="ax-btn">
                    <i class="bi bi-funnel"></i>
                    Apply Filter
                </button>

                @if ($hasAnalyticsFilter)
                    <a href="{{ route('reports-analytics.index') }}" class="ax-btn ax-btn--line">
                        <i class="bi bi-x-circle"></i>
                        Clear Filter
                    </a>
                @endif
            </div>

        </form>

        <p class="ax-filter-note">
            <i class="bi bi-info-circle"></i>
            <strong>Coverage:</strong> {{ $analyticsCoverage }}.
            Resident and household profile figures remain current; the date filter
            applies to reports/complaints and relief voucher activity.
        </p>

    </section>


    <div class="ax-shell">

        {{-- SIDE INDEX --}}
        <aside class="ax-rail" aria-label="On this page">

            <p class="ax-rail-title">On this page</p>

            <nav id="axRail">
                <a href="#ax-overview"><i class="bi bi-speedometer2"></i> Overview</a>
                <a href="#ax-population"><i class="bi bi-people"></i> Population</a>
                <a href="#ax-households"><i class="bi bi-houses"></i> Households</a>
                <a href="#ax-puroks"><i class="bi bi-geo-alt"></i> Puroks</a>
                <a href="#ax-complaints"><i class="bi bi-clipboard-data"></i> Complaints</a>
                <a href="#ax-relief"><i class="bi bi-qr-code"></i> Relief</a>
                <a href="#ax-activity"><i class="bi bi-clock-history"></i> Recent activity</a>
            </nav>

        </aside>


        <main class="ax-main">

            {{-- OVERVIEW --}}
            <section id="ax-overview" class="ax-chapter">

                <div class="ax-hero">

                    <div class="ax-hero-grid">

                        <div>

                            <div class="ax-hero-kicker">Total residents on record</div>

                            <div class="ax-hero-number">{{ number_format($totalResidents) }}</div>

                            <p class="ax-hero-note">
                                {{ number_format($residentStats['active']) }}
                                are currently active across
                                {{ number_format($householdStats['total']) }}
                                registered households.
                            </p>

                            <div class="ax-split"
                                 role="img"
                                 aria-label="Residents by sex: {{ $maleCount }} male, {{ $femaleCount }} female{{ $otherCount ? ', ' . $otherCount . ' other' : '' }}">

                                <div class="ax-split-bar">
                                    @if ($maleCount > 0)
                                        <span class="ax-split-male" style="flex: {{ $maleCount }}"></span>
                                    @endif
                                    @if ($femaleCount > 0)
                                        <span class="ax-split-female" style="flex: {{ $femaleCount }}"></span>
                                    @endif
                                    @if ($otherCount > 0)
                                        <span class="ax-split-other" style="flex: {{ $otherCount }}"></span>
                                    @endif
                                </div>

                                <div class="ax-split-legend">
                                    <span>
                                        <i class="ax-dot ax-split-male"></i>
                                        Male <b>{{ number_format($maleCount) }}</b>
                                    </span>
                                    <span>
                                        <i class="ax-dot ax-split-female"></i>
                                        Female <b>{{ number_format($femaleCount) }}</b>
                                    </span>
                                    @if ($otherCount > 0)
                                        <span>
                                            <i class="ax-dot ax-split-other"></i>
                                            Other <b>{{ number_format($otherCount) }}</b>
                                        </span>
                                    @endif
                                </div>

                            </div>

                        </div>


                        <div class="ax-gauges">

                            <div class="ax-gauge">
                                <div class="ax-ring" role="img" aria-label="{{ $activeResPct }} percent of residents active">
                                    <svg viewBox="0 0 40 40" aria-hidden="true">
                                        <circle class="track" cx="20" cy="20" r="15.9155" pathLength="100"/>
                                        <circle class="fill" cx="20" cy="20" r="15.9155" pathLength="100" style="--p: {{ $activeResPct }}; --c: #5fd3c6"/>
                                    </svg>
                                    <div class="ax-ring-num">{{ $activeResPct }}%</div>
                                </div>
                                <div>
                                    <div class="ax-gauge-label">Active residents</div>
                                    <div class="ax-gauge-sub">
                                        {{ number_format($residentStats['active']) }} of {{ number_format($residentStats['total']) }}
                                    </div>
                                </div>
                            </div>

                            <div class="ax-gauge">
                                <div class="ax-ring" role="img" aria-label="{{ $mappedPct }} percent of households mapped">
                                    <svg viewBox="0 0 40 40" aria-hidden="true">
                                        <circle class="track" cx="20" cy="20" r="15.9155" pathLength="100"/>
                                        <circle class="fill" cx="20" cy="20" r="15.9155" pathLength="100" style="--p: {{ $mappedPct }}; --c: #f3c14a"/>
                                    </svg>
                                    <div class="ax-ring-num">{{ $mappedPct }}%</div>
                                </div>
                                <div>
                                    <div class="ax-gauge-label">Households mapped</div>
                                    <div class="ax-gauge-sub">
                                        {{ number_format($householdStats['mapped']) }} of {{ number_format($householdStats['total']) }}
                                    </div>
                                </div>
                            </div>

                            <div class="ax-gauge">
                                <div class="ax-ring" role="img" aria-label="{{ $handledPct }} percent of complaints resolved or closed">
                                    <svg viewBox="0 0 40 40" aria-hidden="true">
                                        <circle class="track" cx="20" cy="20" r="15.9155" pathLength="100"/>
                                        <circle class="fill" cx="20" cy="20" r="15.9155" pathLength="100" style="--p: {{ $handledPct }}; --c: #ff9c7f"/>
                                    </svg>
                                    <div class="ax-ring-num">{{ $handledPct }}%</div>
                                </div>
                                <div>
                                    <div class="ax-gauge-label">Complaints handled</div>
                                    <div class="ax-gauge-sub">
                                        {{ number_format($complaintStats['resolved'] + $complaintStats['closed']) }} of {{ number_format($complaintStats['total']) }}
                                    </div>
                                </div>
                            </div>

                            <div class="ax-gauge">
                                <div class="ax-ring" role="img" aria-label="{{ $claimedPct }} percent of vouchers claimed">
                                    <svg viewBox="0 0 40 40" aria-hidden="true">
                                        <circle class="track" cx="20" cy="20" r="15.9155" pathLength="100"/>
                                        <circle class="fill" cx="20" cy="20" r="15.9155" pathLength="100" style="--p: {{ $claimedPct }}; --c: #8f9bff"/>
                                    </svg>
                                    <div class="ax-ring-num">{{ $claimedPct }}%</div>
                                </div>
                                <div>
                                    <div class="ax-gauge-label">Vouchers claimed</div>
                                    <div class="ax-gauge-sub">
                                        {{ number_format($voucherStats['claimed']) }} of {{ number_format($voucherStats['total']) }}
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

                <div class="ax-about">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <b>About these numbers.</b>
                        They summarize the records currently maintained in the
                        e-Poblacion Record Management System and are meant for
                        monitoring, planning, and preparing official barangay reports.
                    </div>
                </div>

            </section>


            {{-- POPULATION --}}
            <section id="ax-population" class="ax-chapter">

                <div class="ax-chapter-head">
                    <h2 class="ax-h2"><i class="bi bi-people-fill"></i> Population</h2>
                    <p class="ax-lede">Who lives in the barangay, split by sex and by age group.</p>
                </div>

                <div class="ax-strip">

                    <div class="ax-fig t-teal">
                        <div class="ax-fig-label"><i class="bi bi-people"></i> Total residents</div>
                        <div class="ax-fig-value">{{ number_format($residentStats['total']) }}</div>
                        <div class="ax-fig-desc">All resident records</div>
                    </div>

                    <div class="ax-fig t-ok">
                        <div class="ax-fig-label"><i class="bi bi-person-check"></i> Active residents</div>
                        <div class="ax-fig-value">{{ number_format($residentStats['active']) }}</div>
                        <div class="ax-fig-desc">Currently active records</div>
                    </div>

                    <div class="ax-fig t-indigo">
                        <div class="ax-fig-label"><i class="bi bi-gender-male"></i> Male residents</div>
                        <div class="ax-fig-value">{{ number_format($residentStats['male']) }}</div>
                        <div class="ax-fig-desc">Recorded male residents</div>
                    </div>

                    <div class="ax-fig t-coral">
                        <div class="ax-fig-label"><i class="bi bi-gender-female"></i> Female residents</div>
                        <div class="ax-fig-value">{{ number_format($residentStats['female']) }}</div>
                        <div class="ax-fig-desc">Recorded female residents</div>
                    </div>

                </div>

                <div class="ax-grid ax-g-5-7">

                    <div class="ax-card">
                        <div class="ax-card-head">
                            <div>
                                <h3>Population by sex</h3>
                                <p class="ax-card-sub">Distribution of recorded residents.</p>
                            </div>
                        </div>
                        <div class="ax-canvas is-round">
                            <canvas id="sexChart" role="img" aria-label="Doughnut chart of population by sex"></canvas>
                        </div>
                    </div>

                    <div class="ax-card">
                        <div class="ax-card-head">
                            <div>
                                <h3>Population by age group</h3>
                                <p class="ax-card-sub">Age distribution based on birth dates.</p>
                            </div>
                        </div>
                        <div class="ax-canvas">
                            <canvas id="ageChart" role="img" aria-label="Bar chart of population by age group"></canvas>
                        </div>
                    </div>

                </div>

            </section>


            {{-- HOUSEHOLDS --}}
            <section id="ax-households" class="ax-chapter">

                <div class="ax-chapter-head">
                    <h2 class="ax-h2"><i class="bi bi-house-fill"></i> Households</h2>
                    <p class="ax-lede">Registered households and how many have a location on the map.</p>
                </div>

                <div class="ax-strip">

                    <div class="ax-fig t-teal">
                        <div class="ax-fig-label"><i class="bi bi-houses"></i> Total households</div>
                        <div class="ax-fig-value">{{ number_format($householdStats['total']) }}</div>
                        <div class="ax-fig-desc">Registered household records</div>
                    </div>

                    <div class="ax-fig t-ok">
                        <div class="ax-fig-label"><i class="bi bi-house-check"></i> Active households</div>
                        <div class="ax-fig-value">{{ number_format($householdStats['active']) }}</div>
                        <div class="ax-fig-desc">Currently active households</div>
                    </div>

                    <div class="ax-fig t-indigo">
                        <div class="ax-fig-label"><i class="bi bi-geo-alt"></i> Mapped households</div>
                        <div class="ax-fig-value">{{ number_format($householdStats['mapped']) }}</div>
                        <div class="ax-fig-desc">With recorded map coordinates</div>
                    </div>

                    <div class="ax-fig t-sun">
                        <div class="ax-fig-label"><i class="bi bi-geo"></i> Unmapped households</div>
                        <div class="ax-fig-value">{{ number_format($householdStats['unmapped']) }}</div>
                        <div class="ax-fig-desc">Without map coordinates</div>
                    </div>

                </div>

                <div class="ax-meter">

                    <div class="ax-meter-top">
                        <span>Map coverage</span>
                        <span>{{ $mappedPct }}% of households have coordinates</span>
                    </div>

                    <div class="ax-meter-bar"
                         role="img"
                         aria-label="{{ $householdStats['mapped'] }} mapped, {{ $householdStats['unmapped'] }} unmapped">
                        @if ($householdStats['mapped'] > 0)
                            <span style="flex: {{ $householdStats['mapped'] }}; background: var(--ax-teal)"></span>
                        @endif
                        @if ($householdStats['unmapped'] > 0)
                            <span style="flex: {{ $householdStats['unmapped'] }}; background: var(--ax-sun)"></span>
                        @endif
                    </div>

                    <div class="ax-meter-legend">
                        <span>
                            <i class="ax-dot" style="background: var(--ax-teal)"></i>
                            Mapped <b>{{ number_format($householdStats['mapped']) }}</b>
                        </span>
                        <span>
                            <i class="ax-dot" style="background: var(--ax-sun)"></i>
                            Unmapped <b>{{ number_format($householdStats['unmapped']) }}</b>
                        </span>
                    </div>

                    @if ($householdStats['unmapped'] > 0)
                        <div class="ax-warn">
                            <i class="bi bi-exclamation-circle"></i>
                            {{ number_format($householdStats['unmapped']) }}
                            {{ Str::plural('household', $householdStats['unmapped']) }}
                            still need map coordinates.
                        </div>
                    @endif

                </div>

            </section>


            {{-- PUROKS --}}
            <section id="ax-puroks" class="ax-chapter">

                <div class="ax-chapter-head">
                    <h2 class="ax-h2"><i class="bi bi-geo-alt-fill"></i> Puroks</h2>
                    <p class="ax-lede">Compare puroks by number of households or by residents.</p>
                </div>

                <div class="ax-card">

                    <div class="ax-card-head">

                        <div>
                            <h3 id="purokTitle">Households by purok</h3>
                            <p class="ax-card-sub" id="purokSub">Number of registered households per purok.</p>
                        </div>

                        <div class="ax-tabs" role="tablist" aria-label="Purok view">

                            <button type="button"
                                    class="ax-tab"
                                    role="tab"
                                    id="tabHouseholds"
                                    aria-selected="true"
                                    aria-controls="paneHouseholds"
                                    data-title="Households by purok"
                                    data-sub="Number of registered households per purok.">
                                Households
                            </button>

                            <button type="button"
                                    class="ax-tab"
                                    role="tab"
                                    id="tabResidents"
                                    aria-selected="false"
                                    aria-controls="paneResidents"
                                    data-title="Population by purok"
                                    data-sub="Residents associated with each purok.">
                                Residents
                            </button>

                        </div>

                    </div>


                    <div class="ax-pane" id="paneHouseholds" role="tabpanel" aria-labelledby="tabHouseholds">

                        <h4 class="ax-print-pane-title">Households by purok</h4>

                        <div class="ax-canvas">
                            <canvas id="householdsPurokChart" role="img" aria-label="Bar chart of households by purok"></canvas>
                        </div>

                        @if ($topHouseholdPurok)
                            <div class="ax-callout">
                                <i class="bi bi-trophy"></i>
                                <span>
                                    Most households:
                                    <strong>{{ $topHouseholdPurok->purok ?? $topHouseholdPurok['purok'] ?? '—' }}</strong>
                                    with
                                    {{ number_format($topHouseholdPurok->total ?? $topHouseholdPurok['total'] ?? 0) }}.
                                </span>
                            </div>
                        @endif

                    </div>


                    <div class="ax-pane" id="paneResidents" role="tabpanel" aria-labelledby="tabResidents" hidden>

                        <h4 class="ax-print-pane-title">Residents by purok</h4>

                        <div class="ax-canvas">
                            <canvas id="populationPurokChart" role="img" aria-label="Bar chart of population by purok"></canvas>
                        </div>

                        @if ($topPopulationPurok)
                            <div class="ax-callout">
                                <i class="bi bi-trophy"></i>
                                <span>
                                    Most residents:
                                    <strong>{{ $topPopulationPurok->purok ?? $topPopulationPurok['purok'] ?? '—' }}</strong>
                                    with
                                    {{ number_format($topPopulationPurok->total ?? $topPopulationPurok['total'] ?? 0) }}.
                                </span>
                            </div>
                        @endif

                    </div>

                </div>

            </section>


            {{-- COMPLAINTS --}}
            <section id="ax-complaints" class="ax-chapter">

                <div class="ax-chapter-head">
                    <h2 class="ax-h2"><i class="bi bi-clipboard-data-fill"></i> Reports &amp; complaints</h2>
                    <p class="ax-lede">Community and person-related records, and how many have been handled.</p>
                </div>

                <div class="ax-strip">

                    <div class="ax-fig t-teal">
                        <div class="ax-fig-label"><i class="bi bi-collection"></i> Total</div>
                        <div class="ax-fig-value">{{ number_format($complaintStats['total']) }}</div>
                        <div class="ax-fig-desc">All recorded complaints</div>
                    </div>

                    <div class="ax-fig t-sun">
                        <div class="ax-fig-label"><i class="bi bi-hourglass-split"></i> Active</div>
                        <div class="ax-fig-value">{{ number_format($complaintStats['active']) }}</div>
                        <div class="ax-fig-desc">Still being handled</div>
                    </div>

                    <div class="ax-fig t-ok">
                        <div class="ax-fig-label"><i class="bi bi-check-circle"></i> Resolved</div>
                        <div class="ax-fig-value">{{ number_format($complaintStats['resolved']) }}</div>
                        <div class="ax-fig-desc">Settled complaints</div>
                    </div>

                    <div class="ax-fig t-indigo">
                        <div class="ax-fig-label"><i class="bi bi-archive"></i> Closed</div>
                        <div class="ax-fig-value">{{ number_format($complaintStats['closed']) }}</div>
                        <div class="ax-fig-desc">Closed records</div>
                    </div>

                </div>

                <div class="ax-meter">

                    <div class="ax-meter-top">
                        <span>Complaint status</span>
                        <span>{{ $handledPct }}% resolved or closed</span>
                    </div>

                    <div class="ax-meter-bar"
                         role="img"
                         aria-label="{{ $complaintStats['active'] }} active, {{ $complaintStats['resolved'] }} resolved, {{ $complaintStats['closed'] }} closed">
                        @if ($complaintStats['active'] > 0)
                            <span style="flex: {{ $complaintStats['active'] }}; background: var(--ax-sun)"></span>
                        @endif
                        @if ($complaintStats['resolved'] > 0)
                            <span style="flex: {{ $complaintStats['resolved'] }}; background: var(--ax-ok)"></span>
                        @endif
                        @if ($complaintStats['closed'] > 0)
                            <span style="flex: {{ $complaintStats['closed'] }}; background: var(--ax-indigo)"></span>
                        @endif
                    </div>

                    <div class="ax-meter-legend">
                        <span>
                            <i class="ax-dot" style="background: var(--ax-sun)"></i>
                            Active <b>{{ number_format($complaintStats['active']) }}</b>
                        </span>
                        <span>
                            <i class="ax-dot" style="background: var(--ax-ok)"></i>
                            Resolved <b>{{ number_format($complaintStats['resolved']) }}</b>
                        </span>
                        <span>
                            <i class="ax-dot" style="background: var(--ax-indigo)"></i>
                            Closed <b>{{ number_format($complaintStats['closed']) }}</b>
                        </span>
                    </div>

                </div>

                <div class="ax-grid ax-g-6-6">

                    <div class="ax-card">
                        <div class="ax-card-head">
                            <div>
                                <h3>Reports &amp; complaints by type</h3>
                                <p class="ax-card-sub">Community and person-related records.</p>
                            </div>
                        </div>
                        <div class="ax-canvas is-round">
                            <canvas id="complaintsTypeChart" role="img" aria-label="Doughnut chart of complaints by type"></canvas>
                        </div>
                    </div>

                    <div class="ax-card">
                        <div class="ax-card-head">
                            <div>
                                <h3>Top complaint categories</h3>
                                <p class="ax-card-sub">Most frequently recorded categories.</p>
                            </div>
                        </div>
                        <div class="ax-canvas">
                            <canvas id="complaintsCategoryChart" role="img" aria-label="Bar chart of top complaint categories"></canvas>
                        </div>
                    </div>

                </div>

            </section>


            {{-- RELIEF --}}
            <section id="ax-relief" class="ax-chapter">

                <div class="ax-chapter-head">
                    <h2 class="ax-h2"><i class="bi bi-qr-code"></i> Relief assistance</h2>
                    <p class="ax-lede">Voucher records, from issued to claimed{{ $hasAnalyticsFilter ? ' within the selected period' : '' }}.</p>
                </div>

                <div class="ax-strip">

                    <div class="ax-fig t-teal">
                        <div class="ax-fig-label"><i class="bi bi-ticket-perforated"></i> Total vouchers</div>
                        <div class="ax-fig-value">{{ number_format($voucherStats['total']) }}</div>
                        <div class="ax-fig-desc">Relief voucher records</div>
                    </div>

                    <div class="ax-fig t-indigo">
                        <div class="ax-fig-label"><i class="bi bi-send"></i> Issued</div>
                        <div class="ax-fig-value">{{ number_format($voucherStats['issued']) }}</div>
                        <div class="ax-fig-desc">Vouchers issued</div>
                    </div>

                    <div class="ax-fig t-ok">
                        <div class="ax-fig-label"><i class="bi bi-check-circle"></i> Claimed</div>
                        <div class="ax-fig-value">{{ number_format($voucherStats['claimed']) }}</div>
                        <div class="ax-fig-desc">Successfully claimed</div>
                    </div>

                    <div class="ax-fig t-coral">
                        <div class="ax-fig-label"><i class="bi bi-x-circle"></i> Cancelled</div>
                        <div class="ax-fig-value">{{ number_format($voucherStats['cancelled']) }}</div>
                        <div class="ax-fig-desc">Cancelled vouchers</div>
                    </div>

                </div>

                <div class="ax-card" style="margin-top: 18px">

                    <div class="ax-card-head">
                        <div>
                            <h3>Relief vouchers by status</h3>
                            <p class="ax-card-sub">Current distribution of voucher records.</p>
                        </div>
                    </div>

                    <div class="ax-canvas">
                        <canvas id="voucherStatusChart" role="img" aria-label="Bar chart of relief vouchers by status"></canvas>
                    </div>

                </div>

            </section>


            {{-- RECENT ACTIVITY --}}
            <section id="ax-activity" class="ax-chapter">

                <div class="ax-chapter-head">
                    <h2 class="ax-h2"><i class="bi bi-clock-history"></i> Recent voucher activity</h2>
                    <p class="ax-lede">The latest relief vouchers{{ $hasAnalyticsFilter ? ' within the selected period' : '' }}.</p>
                </div>

                <div class="ax-table-card">

                    <div class="table-responsive">

                        <table class="ax-table">

                            <thead>
                                <tr>
                                    <th>Voucher code</th>
                                    <th>Household</th>
                                    <th>Purok</th>
                                    <th>Assistance</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($recentVouchers as $voucher)

                                    @php
                                        $voucherStatus = strtolower((string) $voucher->status);

                                        $voucherBadge = match ($voucherStatus) {
                                            'claimed'   => 'is-ok',
                                            'issued'    => 'is-info',
                                            'cancelled' => 'is-bad',
                                            default     => 'is-warn',
                                        };
                                    @endphp

                                    <tr>
                                        <td><span class="ax-code">{{ $voucher->voucher_code }}</span></td>
                                        <td>{{ $voucher->household?->household_number ?? '—' }}</td>
                                        <td>{{ $voucher->purok_name ?? '—' }}</td>
                                        <td>{{ $voucher->assistance_type ?? 'Relief Assistance' }}</td>
                                        <td>
                                            <span class="ax-badge {{ $voucherBadge }}">
                                                {{ ucfirst($voucher->status ?? 'Pending') }}
                                            </span>
                                        </td>
                                        <td>{{ optional($voucher->created_at)->format('M d, Y') }}</td>
                                    </tr>

                                @empty

                                    <tr>
                                        <td colspan="6" class="ax-empty">
                                            <i class="bi bi-inbox"></i>
                                            No relief vouchers yet.
                                            New vouchers will show up here.
                                        </td>
                                    </tr>

                                @endforelse

                            </tbody>

                        </table>

                    </div>

                </div>

            </section>


            {{-- SUMMARY REPORTS CTA --}}
            <section class="ax-cta">

                <div>

                    <h3>Ready to prepare an official report?</h3>

                    <p>
                        Use the Summary Reports module to prepare official barangay
                        reports for review, printing, and physical submission to the
                        appropriate government office or agency.
                    </p>

                    <ol class="ax-steps">
                        <li>Generate</li>
                        <li>Review</li>
                        <li>Print</li>
                        <li>Submit in person</li>
                    </ol>

                </div>

                <div>

                    @if (Route::has('summary-reports.index'))
                        <a href="{{ route('summary-reports.index') }}" class="ax-btn">
                            <i class="bi bi-file-earmark-text"></i>
                            Open Summary Reports
                        </a>
                    @else
                        <a href="#"
                           class="ax-btn is-disabled"
                           onclick="return false;"
                           title="Summary Reports route is currently being configured.">
                            <i class="bi bi-file-earmark-text"></i>
                            Summary Reports
                        </a>
                    @endif

                </div>

            </section>


            {{-- PRINT-ONLY: sign-off --}}
            <section class="ax-print-sign">

                <div class="ax-print-sign-grid">

                    <div class="ax-print-sign-box">
                        <div class="ax-sign-line"></div>
                        <strong>Prepared by</strong>
                        <span>Barangay Secretary</span>
                    </div>

                    <div class="ax-print-sign-box">
                        <div class="ax-sign-line"></div>
                        <strong>Noted by</strong>
                        <span>Punong Barangay</span>
                    </div>

                </div>

                <p class="ax-print-note">
                    Generated from the e-Poblacion Record Management System on
                    {{ now()->format('F j, Y h:i A') }}. Figures reflect records in the
                    system at the time of printing.
                </p>

            </section>

        </main>

    </div>

</div>


{{-- CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const root = document.querySelector('.ax');

    if (!root) {
        return;
    }

    const css = getComputedStyle(root);

    const token = (name) => css.getPropertyValue(name).trim();

    const reduceMotion =
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;


    const C = {
        text: token('--ax-text'),
        muted: token('--ax-muted'),
        ink: token('--ax-ink'),
        surface: token('--ax-surface'),
        grid: token('--ax-line'),
        teal: token('--ax-teal'),
        sun: token('--ax-sun'),
        coral: token('--ax-coral'),
        indigo: token('--ax-indigo'),
        ok: token('--ax-ok'),
    };

    const palette = [C.teal, C.sun, C.coral, C.indigo, C.ok, '#8a9aa0'];

    Chart.defaults.font.family = "'Figtree', system-ui, 'Segoe UI', sans-serif";
    Chart.defaults.font.size = 12.5;
    Chart.defaults.color = C.muted;

    if (reduceMotion) {
        Chart.defaults.animation = false;
    }

    Chart.defaults.plugins.tooltip.backgroundColor = C.ink;
    Chart.defaults.plugins.tooltip.titleColor = C.surface;
    Chart.defaults.plugins.tooltip.bodyColor = C.surface;
    Chart.defaults.plugins.tooltip.padding = 12;
    Chart.defaults.plugins.tooltip.cornerRadius = 10;
    Chart.defaults.plugins.tooltip.displayColors = true;
    Chart.defaults.plugins.tooltip.boxPadding = 4;

    const charts = {};


    /* =========================================================
       Chart data from controller
    ========================================================== */

    const sexLabels = @json($sexDistribution->pluck('sex')->values());
    const sexData = @json($sexDistribution->pluck('total')->values());

    const ageLabels = @json(collect($ageDistribution)->pluck('label')->values());
    const ageData = @json(collect($ageDistribution)->pluck('total')->values());

    const householdPurokLabels = @json($householdsByPurok->pluck('purok')->values());
    const householdPurokData = @json($householdsByPurok->pluck('total')->values());

    const populationPurokLabels = @json($populationByPurok->pluck('purok')->values());
    const populationPurokData = @json($populationByPurok->pluck('total')->values());

    const complaintTypeLabels = @json($complaintsByType->pluck('report_type')->values());
    const complaintTypeData = @json($complaintsByType->pluck('total')->values());

    const complaintCategoryLabels = @json($complaintsByCategory->pluck('category')->values());
    const complaintCategoryData = @json($complaintsByCategory->pluck('total')->values());

    const voucherLabels = @json($vouchersByStatus->pluck('status')->values());
    const voucherData = @json($vouchersByStatus->pluck('total')->values());


    /* =========================================================
       Helpers
    ========================================================== */

    const hasData = (data) =>
        Array.isArray(data) && data.some((n) => Number(n) > 0);

    function showEmpty(canvas) {

        const box = canvas.parentElement;

        box.innerHTML =
            '<div class="ax-nodata">' +
                '<div>' +
                    '<i class="bi bi-bar-chart"></i>' +
                    '<div>No data to chart yet.</div>' +
                '</div>' +
            '</div>';
    }

    const sexColor = (label) => {

        const l = String(label).toLowerCase();

        if (l.startsWith('m')) { return C.teal; }
        if (l.startsWith('f')) { return C.coral; }

        return C.sun;
    };

    const voucherColor = (label) => {

        switch (String(label).toLowerCase()) {
            case 'claimed':   return C.ok;
            case 'issued':    return C.indigo;
            case 'cancelled': return C.coral;
            default:          return C.sun;
        }
    };


    function doughnut(id, labels, data, colors) {

        const canvas = document.getElementById(id);

        if (!canvas) { return; }
        if (!hasData(data)) { return showEmpty(canvas); }

        charts[id] = new Chart(canvas, {

            type: 'doughnut',

            data: {
                labels,
                datasets: [{
                    data,
                    backgroundColor: colors || labels.map((_, i) => palette[i % palette.length]),
                    borderColor: C.surface,
                    borderWidth: 3,
                    hoverOffset: 6,
                }],
            },

            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                animation: reduceMotion ? false : undefined,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: C.text,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            padding: 18,
                        },
                    },
                },
            },
        });
    }


    function bars(id, labels, data, opts = {}) {

        const canvas = document.getElementById(id);

        if (!canvas) { return; }
        if (!hasData(data)) { return showEmpty(canvas); }

        const horizontal = !!opts.horizontal;

        const valueAxis = {
            beginAtZero: true,
            ticks: { color: C.muted, precision: 0 },
            grid: { color: C.grid },
            border: { display: false },
        };

        const labelAxis = {
            ticks: { color: C.text },
            grid: { display: false },
            border: { display: false },
        };

        charts[id] = new Chart(canvas, {

            type: 'bar',

            data: {
                labels,
                datasets: [{
                    label: opts.label || 'Total',
                    data,
                    backgroundColor: opts.colors || C.teal,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 46,
                }],
            },

            options: {
                indexAxis: horizontal ? 'y' : 'x',
                responsive: true,
                maintainAspectRatio: false,
                animation: reduceMotion ? false : undefined,
                plugins: { legend: { display: false } },
                scales: horizontal
                    ? { x: valueAxis, y: labelAxis }
                    : { x: labelAxis, y: valueAxis },
            },
        });
    }


    /* =========================================================
       Draw charts
    ========================================================== */

    doughnut('sexChart', sexLabels, sexData, sexLabels.map(sexColor));

    bars('ageChart', ageLabels, ageData, { label: 'Residents' });

    bars('householdsPurokChart', householdPurokLabels, householdPurokData, { label: 'Households' });

    bars('populationPurokChart', populationPurokLabels, populationPurokData, { label: 'Residents', colors: C.indigo });

    doughnut('complaintsTypeChart', complaintTypeLabels, complaintTypeData);

    bars('complaintsCategoryChart', complaintCategoryLabels, complaintCategoryData, {
        horizontal: true,
        label: 'Records',
        colors: C.coral
    });

    bars('voucherStatusChart', voucherLabels, voucherData, {
        label: 'Vouchers',
        colors: voucherLabels.map(voucherColor)
    });


    /* =========================================================
       Purok tabs
    ========================================================== */

    const tabs = [
        {
            tab: document.getElementById('tabHouseholds'),
            pane: document.getElementById('paneHouseholds'),
            chart: 'householdsPurokChart'
        },
        {
            tab: document.getElementById('tabResidents'),
            pane: document.getElementById('paneResidents'),
            chart: 'populationPurokChart'
        },
    ];

    const purokTitle = document.getElementById('purokTitle');
    const purokSub = document.getElementById('purokSub');

    function selectTab(active) {

        tabs.forEach((t) => {
            const on = t === active;
            t.tab.setAttribute('aria-selected', on ? 'true' : 'false');
            t.pane.hidden = !on;
        });

        purokTitle.textContent = active.tab.dataset.title;
        purokSub.textContent = active.tab.dataset.sub;

        if (charts[active.chart]) {
            charts[active.chart].resize();
        }
    }

    tabs.forEach((t) => {
        t.tab.addEventListener('click', () => selectTab(t));
    });


    /* =========================================================
       Side index
    ========================================================== */

    const links = Array.from(document.querySelectorAll('#axRail a'));

    const targets = links
        .map((a) => document.querySelector(a.getAttribute('href')))
        .filter(Boolean);

    function setCurrent(id) {
        links.forEach((a) => {
            a.classList.toggle('is-current', a.getAttribute('href') === '#' + id);
        });
    }

    if ('IntersectionObserver' in window) {

        const seen = new Map();

        const io = new IntersectionObserver(
            (entries) => {

                entries.forEach((e) => {
                    seen.set(e.target.id, e.isIntersecting ? e.boundingClientRect.top : null);
                });

                const visible = [...seen.entries()]
                    .filter(([, top]) => top !== null)
                    .sort((a, b) => a[1] - b[1]);

                if (visible.length) {
                    setCurrent(visible[0][0]);
                }
            },
            { rootMargin: '-15% 0px -65% 0px' }
        );

        targets.forEach((t) => io.observe(t));
    }

    setCurrent('ax-overview');


    /* =========================================================
       PRINT
    ========================================================== */

    /* ---------- Paper sizes ---------- */

    const PAPERS = {
        a4:    { size: 'A4 portrait', w: 210   },   // 210 x 297 mm
        short: { size: '8.5in 11in',  w: 215.9 },   // Letter
        long:  { size: '8.5in 13in',  w: 215.9 },   // Long bond / Folio
        legal: { size: '8.5in 14in',  w: 215.9 },
    };

    const PAGE_MARGIN_X = 13;            // mm  (keep equal to @page left/right margin)
    const MM = 96 / 25.4;                // CSS px per mm

    const paperSelect = document.getElementById('axPaper');
    const printBtn    = document.getElementById('axPrintBtn');

    const pageStyle = document.createElement('style');
    document.head.appendChild(pageStyle);

    let paperKey = 'a4';

    try {
        const saved = localStorage.getItem('ax-paper');
        if (saved && PAPERS[saved]) {
            paperKey = saved;
        }
    } catch (e) { /* storage unavailable */ }

    function applyPaper(key) {
        paperKey = PAPERS[key] ? key : 'a4';
        pageStyle.textContent = '@page { size: ' + PAPERS[paperKey].size + '; }';

        try { localStorage.setItem('ax-paper', paperKey); } catch (e) {}

        if (paperSelect) {
            paperSelect.value = paperKey;
        }
    }

    applyPaper(paperKey);

    if (paperSelect) {
        paperSelect.addEventListener('change', () => applyPaper(paperSelect.value));
    }


    /* ---------- Chart sizes on paper (height must match the CSS) ---------- */

    const PRINT_H = {          // mm
        sexChart: 52,
        ageChart: 52,
        householdsPurokChart: 58,
        populationPurokChart: 58,
        complaintsTypeChart: 52,
        complaintsCategoryChart: 52,
        voucherStatusChart: 42,
    };

    function printWidthMm(id) {

        const W   = PAPERS[paperKey].w - PAGE_MARGIN_X * 2;   // content width
        const PAD = 3.5 * 2 + 0.5;                            // card padding + border
        const GAP = 4;

        switch (id) {
            case 'sexChart':
                return (W - GAP) * 5 / 12 - PAD;
            case 'ageChart':
                return (W - GAP) * 7 / 12 - PAD;
            case 'householdsPurokChart':
            case 'populationPurokChart':
                return (W - PAD - 5) / 2;
            case 'voucherStatusChart':
                return W - PAD;
            default:
                return (W - GAP) / 2 - PAD;
        }
    }


    /* ---------- Light colours for paper ---------- */

    const LIGHT = {
        text:   '#2b4149',
        grid:   '#e3e9ea',
        teal:   '#0f766e',
        sun:    '#e9a80c',
        coral:  '#e0573a',
        indigo: '#4f5fd1',
        ok:     '#178a55',
    };

    function toLight(color) {

        for (const key of ['teal', 'sun', 'coral', 'indigo', 'ok']) {
            if (C[key] === color) {
                return LIGHT[key];
            }
        }

        return color;
    }

    const screenTheme = new Map();

    function themeChart(id, chart, forPrint) {

        if (!screenTheme.has(id)) {

            screenTheme.set(id, {

                ds: chart.data.datasets.map((d) => ({
                    bg: d.backgroundColor,
                    border: d.borderColor,
                })),

                scales: Object.fromEntries(
                    Object.entries(chart.options.scales || {}).map(([k, s]) => [
                        k,
                        {
                            tick: s.ticks && s.ticks.color,
                            grid: s.grid && s.grid.color,
                        },
                    ])
                ),

                legend: chart.options.plugins &&
                        chart.options.plugins.legend &&
                        chart.options.plugins.legend.labels &&
                        chart.options.plugins.legend.labels.color,
            });
        }

        const s = screenTheme.get(id);

        chart.data.datasets.forEach((d, i) => {

            const bg = s.ds[i].bg;

            d.backgroundColor = forPrint
                ? (Array.isArray(bg) ? bg.map(toLight) : toLight(bg))
                : bg;

            d.borderColor = forPrint ? '#ffffff' : s.ds[i].border;
        });

        Object.entries(chart.options.scales || {}).forEach(([k, sc]) => {

            if (sc.ticks) {
                sc.ticks.color = forPrint ? LIGHT.text : s.scales[k].tick;
            }

            if (sc.grid && s.scales[k].grid !== undefined) {
                sc.grid.color = forPrint ? LIGHT.grid : s.scales[k].grid;
            }
        });

        const lg = chart.options.plugins &&
                   chart.options.plugins.legend &&
                   chart.options.plugins.legend.labels;

        if (lg) {
            lg.color = forPrint ? LIGHT.text : s.legend;
        }
    }


    /* ---------- Charts -> crisp images (so print never squishes them) ---------- */

    function buildPrintImages() {

        Object.entries(charts).forEach(([id, chart]) => {

            const wrap = chart.canvas && chart.canvas.parentElement;

            if (!wrap || !PRINT_H[id]) {
                return;
            }

            try {

                chart.stop();

                themeChart(id, chart, true);

                chart.options.devicePixelRatio = 3;

                chart.resize(
                    Math.round(printWidthMm(id) * MM),
                    Math.round(PRINT_H[id] * MM)
                );

                chart.update('none');

                const img = new Image();

                img.className = 'ax-print-img';
                img.decoding  = 'sync';
                img.alt       = chart.canvas.getAttribute('aria-label') || '';
                img.src       = chart.toBase64Image('image/png', 1);

                wrap.appendChild(img);

            } catch (err) {
                /* keep printing even if one chart fails */
            }

            /* put the on-screen chart back right away */
            try {

                themeChart(id, chart, false);

                chart.options.devicePixelRatio = window.devicePixelRatio || 1;

                chart.resize();
                chart.update('none');

            } catch (err) {}
        });
    }


    /* ---------- Isolate the report from the app shell ---------- */

    function prepareShell() {

        let node = root;

        while (node && node !== document.body) {

            const parent = node.parentElement;

            if (!parent) {
                break;
            }

            Array.from(parent.children).forEach((sibling) => {
                if (sibling !== node) {
                    sibling.classList.add('ax-print-hide');
                }
            });

            parent.classList.add('ax-print-ancestor');

            node = parent;
        }
    }

    function restoreShell() {

        document.querySelectorAll('.ax-print-hide').forEach((el) =>
            el.classList.remove('ax-print-hide')
        );

        document.querySelectorAll('.ax-print-ancestor').forEach((el) =>
            el.classList.remove('ax-print-ancestor')
        );
    }


    /* ---------- Print lifecycle ---------- */

    let printReady = false;

    function preparePrint() {

        if (printReady) {
            return;
        }

        prepareShell();
        buildPrintImages();

        printReady = true;
    }

    function finishPrint() {

        document.querySelectorAll('.ax-print-img').forEach((el) => el.remove());

        restoreShell();

        printReady = false;
    }

    if (printBtn) {

        printBtn.addEventListener('click', (e) => {

            e.preventDefault();

            preparePrint();

            /* small delay so the images are ready before the dialog opens */
            setTimeout(() => window.print(), 150);
        });
    }

    /* Ctrl+P / browser menu */
    window.addEventListener('beforeprint', preparePrint);
    window.addEventListener('afterprint', finishPrint);

});
</script>

@endsection