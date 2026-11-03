@extends('layouts.app')

@section('title', 'User Management')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* USER MANAGEMENT: light / dark / system */
    .ux {
        --ux-display: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
        --ux-body: 'Figtree', 'Segoe UI', system-ui, sans-serif;
        --ux-surface: #ffffff;
        --ux-sunken: #f4f7f6;
        --ux-line: #e0e7e5;
        --ux-line-strong: #c8d3d0;
        --ux-ink: #0e2a33;
        --ux-text: #2b4149;
        --ux-muted: #66808a;
        --ux-teal: #0f766e;
        --ux-teal-soft: #dff3ef;
        --ux-indigo: #4f5fd1;
        --ux-indigo-soft: #e6e9fb;
        --ux-ok: #178a55;
        --ux-ok-soft: #e0f4ea;
        --ux-coral: #d4492c;
        --ux-coral-soft: #fbe6e0;
        --ux-sun: #b7790a;
        --ux-sun-soft: #fbf0d0;
        --ux-focus: rgba(15, 118, 110, .3);
        --ux-shadow: 0 1px 2px rgba(14,42,51,.05), 0 8px 24px rgba(14,42,51,.06);
        --ux-av-bg: 90%; --ux-av-s: 60%; --ux-av-fg: 26%;
        font-family: var(--ux-body);
        color: var(--ux-text);
    }

    [data-theme="dark"] .ux {
        --ux-surface: #14262d; --ux-sunken: #0f1f25; --ux-line: #25404a; --ux-line-strong: #33525d;
        --ux-ink: #eaf5f3; --ux-text: #c9dbde; --ux-muted: #8aa5ac;
        --ux-teal: #3dd0c0; --ux-teal-soft: #123a38; --ux-indigo: #8f9bff; --ux-indigo-soft: #1f2447;
        --ux-ok: #56d99b; --ux-ok-soft: #123626; --ux-coral: #ff8a6b; --ux-coral-soft: #3d2019;
        --ux-sun: #f3c14a; --ux-sun-soft: #3a3016; --ux-focus: rgba(61,208,192,.35);
        --ux-shadow: 0 1px 2px rgba(0,0,0,.4), 0 8px 24px rgba(0,0,0,.3);
        --ux-av-bg: 26%; --ux-av-s: 38%; --ux-av-fg: 82%;
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .ux {
            --ux-surface: #14262d; --ux-sunken: #0f1f25; --ux-line: #25404a; --ux-line-strong: #33525d;
            --ux-ink: #eaf5f3; --ux-text: #c9dbde; --ux-muted: #8aa5ac;
            --ux-teal: #3dd0c0; --ux-teal-soft: #123a38; --ux-indigo: #8f9bff; --ux-indigo-soft: #1f2447;
            --ux-ok: #56d99b; --ux-ok-soft: #123626; --ux-coral: #ff8a6b; --ux-coral-soft: #3d2019;
            --ux-sun: #f3c14a; --ux-sun-soft: #3a3016; --ux-focus: rgba(61,208,192,.35);
            --ux-shadow: 0 1px 2px rgba(0,0,0,.4), 0 8px 24px rgba(0,0,0,.3);
            --ux-av-bg: 26%; --ux-av-s: 38%; --ux-av-fg: 82%;
        }
    }

    .ux *, .ux *::before, .ux *::after { box-sizing: border-box; }
    .ux a { text-decoration: none; }
    .ux :focus-visible, .ux-dialog :focus-visible { outline: 3px solid var(--ux-focus); outline-offset: 2px; border-radius: 8px; }

    /* Header */
    .ux-head { display: flex; flex-wrap: wrap; gap: 16px 24px; align-items: center; justify-content: space-between; margin-bottom: 22px; }
    .ux-headline { display: flex; align-items: center; gap: 16px; }
    .ux-mark { display: grid; place-items: center; width: 54px; height: 54px; flex: none; border-radius: 17px; font-size: 1.4rem; color: var(--ux-teal); background: var(--ux-teal-soft); }
    .ux-title { margin: 0; font-family: var(--ux-display); font-weight: 800; font-size: clamp(1.7rem, 3.2vw, 2.4rem); letter-spacing: -.025em; line-height: 1.05; color: var(--ux-ink); }
    .ux-sub { margin: 6px 0 0; color: var(--ux-muted); max-width: 60ch; }

    .ux-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 46px; padding: 0 20px; border-radius: 13px; cursor: pointer;
        font: 600 .93rem var(--ux-body); background: var(--ux-ink); color: var(--ux-surface); border: 1px solid var(--ux-ink); transition: filter .15s, transform .1s, background .15s;
    }
    .ux-btn:hover { filter: brightness(1.15); color: var(--ux-surface); }
    .ux-btn:active { transform: translateY(1px); }
    .ux-btn--line { background: transparent; color: var(--ux-ink); border-color: var(--ux-line-strong); }
    .ux-btn--line:hover { background: var(--ux-sunken); color: var(--ux-ink); filter: none; }
    .ux-btn--danger { background: var(--ux-coral); border-color: var(--ux-coral); color: #fff; }
    .ux-btn--danger:hover { color: #fff; }

    /* Overview */
    .ux-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 14px; }
    .ux-stat { position: relative; padding: 18px 20px; border-radius: 20px; background: var(--ux-surface); border: 1px solid var(--ux-line); box-shadow: var(--ux-shadow); overflow: hidden; }
    .ux-stat::before { content: ''; position: absolute; left: 0; top: 16px; bottom: 16px; width: 4px; border-radius: 0 4px 4px 0; background: var(--tone); }
    .ux-stat span { display: block; font-size: .84rem; font-weight: 600; color: var(--ux-muted); }
    .ux-stat b { display: block; margin-top: 6px; font-family: var(--ux-display); font-weight: 800; font-size: 2.3rem; line-height: 1; letter-spacing: -.03em; color: var(--ux-ink); font-variant-numeric: tabular-nums; }
    .ux-stat small { display: block; margin-top: 6px; font-size: .8rem; color: var(--ux-muted); }
    .ux-stat.t-all { --tone: var(--ux-ink); }
    .ux-stat.t-captain { --tone: var(--ux-indigo); }
    .ux-stat.t-secretary { --tone: var(--ux-teal); }
    .ux-stat.t-active { --tone: var(--ux-ok); }

    .ux-rate { display: flex; align-items: center; gap: 14px; margin-bottom: 22px; padding: 14px 20px; border-radius: 16px; background: var(--ux-surface); border: 1px solid var(--ux-line); font-size: .86rem; font-weight: 600; color: var(--ux-muted); }
    .ux-rate .track { flex: 1; display: flex; gap: 3px; height: 10px; }
    .ux-rate .track span { border-radius: 6px; background: var(--seg); min-width: 6px; }
    .ux-rate .track.is-empty span { flex: 1; background: var(--ux-line); }
    .seg-captain { --seg: var(--ux-indigo); }
    .seg-secretary { --seg: var(--ux-teal); }
    .seg-other { --seg: var(--ux-sun); }
    .ux-rate b { color: var(--ux-ink); font-variant-numeric: tabular-nums; }

    /* Roster */
    .ux-panel { background: var(--ux-surface); border: 1px solid var(--ux-line); border-radius: 24px; box-shadow: var(--ux-shadow); overflow: hidden; }
    .ux-toolbar { display: flex; flex-wrap: wrap; gap: 14px; align-items: center; justify-content: space-between; padding: 22px 24px; }
    .ux-toolbar h2 { margin: 0; font-family: var(--ux-display); font-weight: 700; font-size: 1.2rem; color: var(--ux-ink); }
    .ux-toolbar p { margin: 3px 0 0; font-size: .86rem; color: var(--ux-muted); }
    .ux-tools { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }

    .ux-search { position: relative; margin: 0; }
    .ux-search i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--ux-muted); pointer-events: none; }
    .ux-search input {
        width: 250px; max-width: 100%; min-height: 44px; padding: 0 14px 0 40px; border-radius: 13px; border: 1px solid var(--ux-line-strong);
        background: var(--ux-sunken); color: var(--ux-ink); font: 500 .92rem var(--ux-body); transition: border-color .15s, box-shadow .15s, background .15s;
    }
    .ux-search input::placeholder { color: var(--ux-muted); opacity: .85; }
    .ux-search input:focus { outline: none; background: var(--ux-surface); border-color: var(--ux-teal); box-shadow: 0 0 0 4px var(--ux-focus); }

    .ux-filters, .ux-views { display: inline-flex; flex-wrap: wrap; gap: 4px; padding: 4px; border-radius: 14px; background: var(--ux-sunken); border: 1px solid var(--ux-line); }
    .ux-filter {
        display: inline-flex; align-items: center; gap: 8px; padding: 8px 13px; border: 0; border-radius: 10px; background: transparent; cursor: pointer;
        font: 600 .85rem var(--ux-body); color: var(--ux-muted); transition: background .15s, color .15s;
    }
    .ux-filter b { min-width: 22px; padding: 1px 7px; border-radius: 999px; text-align: center; font-size: .76rem; font-weight: 700; background: var(--ux-line); color: var(--ux-text); }
    .ux-filter:hover { color: var(--ux-ink); }
    .ux-filter[aria-pressed="true"] { background: var(--ux-surface); color: var(--ux-ink); box-shadow: 0 1px 3px rgba(14,42,51,.16); }
    .ux-filter[aria-pressed="true"] b { background: var(--ux-ink); color: var(--ux-surface); }
    .ux-view { padding: 8px 12px; }

    /* List rows */
    .ux-cols, .ux-row {
        display: grid; align-items: center; gap: 18px; padding-left: 24px; padding-right: 24px;
        grid-template-columns: minmax(230px, 2.2fr) minmax(120px, 1fr) minmax(110px, .8fr) minmax(120px, 1fr) 270px;
    }
    .ux-cols { padding-top: 12px; padding-bottom: 12px; background: var(--ux-sunken); border-top: 1px solid var(--ux-line); border-bottom: 1px solid var(--ux-line); font-size: .8rem; font-weight: 700; letter-spacing: .02em; color: var(--ux-muted); }
    .ux-cols[hidden] { display: none; }
    .ux-cols > div:nth-child(2), .ux-cols > div:nth-child(3), .ux-cols > div:nth-child(4) { text-align: center; }
    .ux-cols > div:nth-child(5) { text-align: right; }

    .ux-list[hidden] { display: none; }
    .ux-row { position: relative; padding-top: 16px; padding-bottom: 16px; border-bottom: 1px solid var(--ux-line); transition: background .15s, box-shadow .15s, transform .15s; }
    .ux-row::before { content: ''; position: absolute; left: 0; top: 14px; bottom: 14px; width: 4px; border-radius: 0 4px 4px 0; background: var(--rail, var(--ux-line-strong)); }
    .ux-row[data-role="captain"] { --rail: var(--ux-indigo); }
    .ux-row[data-role="secretary"] { --rail: var(--ux-teal); }
    .ux-row[data-status="inactive"]::before { opacity: .3; }
    .ux-row:last-child { border-bottom: 0; }
    .ux-row:hover { background: var(--ux-sunken); }
    .ux-row[hidden] { display: none; }
    .ux-cell-label { display: none; }
    .ux-row > div:nth-child(2), .ux-row > div:nth-child(3), .ux-row > div:nth-child(4) { display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; min-width: 0; }

    .ux-person { display: flex; align-items: center; gap: 14px; min-width: 0; }
    .ux-avatar {
        position: relative; overflow: hidden; display: grid; place-items: center; width: 48px; height: 48px; flex: none; border-radius: 50%;
        font-family: var(--ux-display); font-weight: 800; font-size: 1.1rem;
        background: hsl(var(--h) var(--ux-av-s) var(--ux-av-bg)); color: hsl(var(--h) 55% var(--ux-av-fg));
        box-shadow: 0 0 0 2px var(--ux-surface), 0 0 0 4px hsl(var(--h) var(--ux-av-s) calc(var(--ux-av-bg) - 6%));
    }
    .ux-avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .ux-who { min-width: 0; }
    .ux-name { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; font-weight: 700; color: var(--ux-ink); overflow-wrap: anywhere; }
    .ux-you { padding: 2px 9px; border-radius: 999px; font-size: .72rem; font-weight: 700; background: var(--ux-teal-soft); color: var(--ux-teal); }
    .ux-user { display: block; margin-top: 2px; font-size: .86rem; font-weight: 500; color: var(--ux-muted); overflow-wrap: anywhere; }
    .ux-user.is-none { font-style: italic; }

    .ux-chip { display: inline-flex; align-items: center; justify-content: center; gap: 7px; padding: 6px 13px; border-radius: 10px; font-size: .82rem; font-weight: 700; white-space: nowrap; }
    .ux-chip.is-captain { background: var(--ux-indigo-soft); color: var(--ux-indigo); }
    .ux-chip.is-secretary { background: var(--ux-teal-soft); color: var(--ux-teal); }
    .ux-chip.is-invalid { background: var(--ux-coral-soft); color: var(--ux-coral); }

    .ux-status { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 5px 12px 5px 10px; border-radius: 999px; font-weight: 600; font-size: .85rem; color: var(--ux-muted); background: var(--ux-sunken); border: 1px solid var(--ux-line); white-space: nowrap; }
    .ux-status::before { content: ''; width: 8px; height: 8px; flex: none; border-radius: 50%; background: var(--ux-line-strong); }
    .ux-status.is-active { color: var(--ux-ok); background: var(--ux-ok-soft); border-color: transparent; }
    .ux-status.is-active::before { background: var(--ux-ok); }

    .ux-updated { font-size: .86rem; color: var(--ux-muted); }
    .ux-updated b { display: block; font-weight: 600; color: var(--ux-text); }

    /* Actions */
    .ux-actions { display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: 8px; min-width: 0; }
    .ux-actions form { margin: 0; }
    .ux-act {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 40px; padding: 0 15px; border-radius: 11px; cursor: pointer; white-space: nowrap;
        font: 700 .86rem var(--ux-body); color: var(--tone, var(--ux-ink));
        background: color-mix(in srgb, var(--tone, var(--ux-ink)) 11%, transparent);
        border: 1.5px solid color-mix(in srgb, var(--tone, var(--ux-ink)) 36%, transparent);
        transition: background .15s, color .15s, border-color .15s, transform .1s;
    }
    .ux-act i { font-size: 1.1rem; line-height: 1; }
    .ux-act:hover { background: var(--tone, var(--ux-ink)); border-color: var(--tone, var(--ux-ink)); color: var(--ux-surface); transform: translateY(-1px); }
    .ux-act.is-view { --tone: var(--ux-teal); }
    .ux-act.is-edit { --tone: var(--ux-indigo); }
    .ux-act.is-ok { --tone: var(--ux-ok); }
    .ux-act.is-bad { --tone: var(--ux-coral); }

    /* Cards view */
    .ux-list.is-cards { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; padding: 4px 24px 24px; }
    .ux-list.is-cards .ux-row { display: flex; flex-direction: column; align-items: center; text-align: center; gap: 12px; padding: 28px 18px 20px; border: 1px solid var(--ux-line); border-radius: 22px; background: var(--ux-surface); }
    .ux-list.is-cards .ux-row[hidden] { display: none; }
    .ux-list.is-cards .ux-row::before { left: 0; right: 0; top: 0; bottom: auto; width: auto; height: 5px; border-radius: 22px 22px 0 0; }
    .ux-list.is-cards .ux-row:hover { transform: translateY(-3px); box-shadow: var(--ux-shadow); background: var(--ux-surface); }
    .ux-list.is-cards .ux-person { flex-direction: column; text-align: center; gap: 12px; }
    .ux-list.is-cards .ux-avatar { width: 84px; height: 84px; font-size: 2rem; }
    .ux-list.is-cards .ux-name { justify-content: center; font-size: 1.05rem; }
    .ux-list.is-cards .ux-updated { order: 5; text-align: center; }
    .ux-list.is-cards .ux-actions { justify-content: center; width: 100%; border-top: 1px dashed var(--ux-line-strong); margin-top: 4px; padding-top: 14px; }

    /* Empty + foot */
    .ux-empty { display: grid; justify-items: center; gap: 8px; padding: 56px 22px; text-align: center; color: var(--ux-muted); border-top: 1px solid var(--ux-line); }
    .ux-empty[hidden] { display: none; }
    .ux-empty i { font-size: 2.2rem; }
    .ux-empty h3 { margin: 4px 0 0; font-family: var(--ux-display); font-weight: 700; font-size: 1.15rem; color: var(--ux-ink); }
    .ux-empty p { margin: 0 0 8px; max-width: 42ch; }
    .ux-foot { padding: 14px 24px; border-top: 1px solid var(--ux-line); background: var(--ux-sunken); font-size: .85rem; color: var(--ux-muted); }

    /* Confirm dialog */
    .ux-dialog {
        width: min(440px, calc(100vw - 32px)); padding: 0; border: 1px solid var(--ux-line); border-radius: 24px;
        background: var(--ux-surface); color: var(--ux-text); box-shadow: 0 24px 60px rgba(0,0,0,.28); font-family: var(--ux-body);
        --ux-display: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
        --ux-body: 'Figtree', 'Segoe UI', system-ui, sans-serif;
        --ux-surface: #ffffff; --ux-line: #e0e7e5; --ux-ink: #0e2a33; --ux-text: #2b4149; --ux-muted: #66808a;
        --ux-line-strong: #c8d3d0; --ux-sunken: #f4f7f6; --ux-coral: #d4492c; --ux-ok: #178a55; --ux-focus: rgba(15,118,110,.3);
    }
    [data-theme="dark"] .ux-dialog { --ux-surface: #14262d; --ux-line: #25404a; --ux-ink: #eaf5f3; --ux-text: #c9dbde; --ux-muted: #8aa5ac; --ux-line-strong: #33525d; --ux-sunken: #0f1f25; --ux-coral: #ff8a6b; --ux-ok: #56d99b; --ux-focus: rgba(61,208,192,.35); }
    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .ux-dialog { --ux-surface: #14262d; --ux-line: #25404a; --ux-ink: #eaf5f3; --ux-text: #c9dbde; --ux-muted: #8aa5ac; --ux-line-strong: #33525d; --ux-sunken: #0f1f25; --ux-coral: #ff8a6b; --ux-ok: #56d99b; --ux-focus: rgba(61,208,192,.35); }
    }
    .ux-dialog::backdrop { background: rgba(8,20,26,.55); backdrop-filter: blur(3px); }
    .ux-dialog-body { padding: 28px 28px 20px; }
    .ux-dialog-icon { display: grid; place-items: center; width: 50px; height: 50px; border-radius: 50%; margin-bottom: 16px; font-size: 1.35rem; background: color-mix(in srgb, var(--dlg-tone) 14%, transparent); color: var(--dlg-tone); }
    .ux-dialog h3 { margin: 0; font-family: var(--ux-display); font-weight: 800; font-size: 1.3rem; color: var(--ux-ink); }
    .ux-dialog p { margin: 8px 0 0; line-height: 1.55; color: var(--ux-muted); }
    .ux-dialog-actions { display: flex; justify-content: flex-end; gap: 10px; padding: 16px 28px 24px; }
    .ux-dialog .ux-btn { display: inline-flex; align-items: center; justify-content: center; min-height: 46px; padding: 0 20px; border-radius: 13px; cursor: pointer; font: 600 .93rem var(--ux-body); background: var(--ux-ink); color: var(--ux-surface); border: 1px solid var(--ux-ink); }
    .ux-dialog .ux-btn--line { background: transparent; color: var(--ux-ink); border-color: var(--ux-line-strong); }
    .ux-dialog .ux-btn--danger { background: var(--ux-coral); border-color: var(--ux-coral); color: #fff; }

    /* Responsive */
    @media (max-width: 1279.98px) {
        .ux-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ux-cols { display: none; }
        .ux-list:not(.is-cards) .ux-row { grid-template-columns: 1fr 1fr; gap: 14px 18px; padding: 18px 20px 18px 24px; }
        .ux-list:not(.is-cards) .ux-row .ux-person, .ux-list:not(.is-cards) .ux-row .ux-actions { grid-column: 1 / -1; }
        .ux-list:not(.is-cards) .ux-actions { justify-content: flex-start; padding-top: 4px; }
        .ux-list:not(.is-cards) .ux-cell-label { display: block; margin-bottom: 5px; font-size: .74rem; font-weight: 600; color: var(--ux-muted); }
        .ux-list:not(.is-cards) .ux-row > div:nth-child(2),
        .ux-list:not(.is-cards) .ux-row > div:nth-child(3),
        .ux-list:not(.is-cards) .ux-row > div:nth-child(4) { align-items: flex-start; justify-content: flex-start; text-align: left; }
    }
    @media (max-width: 767.98px) {
        .ux-mark { width: 46px; height: 46px; font-size: 1.2rem; border-radius: 14px; }
        .ux-stat b { font-size: 1.9rem; }
        .ux-toolbar { padding: 18px; }
        .ux-tools, .ux-search, .ux-search input, .ux-head .ux-btn { width: 100%; }
        .ux-filters { width: 100%; }
        .ux-filter { flex: 1; justify-content: center; padding: 8px 6px; }
        .ux-list.is-cards { padding: 4px 16px 18px; grid-template-columns: 1fr; }
        .ux-actions .ux-act { flex: 1 1 auto; }
    }
    @media (prefers-reduced-motion: reduce) {
        .ux-btn, .ux-row, .ux-filter, .ux-act { transition: none; }
        .ux-act:hover, .ux-list.is-cards .ux-row:hover { transform: none; }
    }
</style>
@endpush


@section('content')

@php
    $captainCount   = $users->where('role', 'captain')->count();
    $secretaryCount = $users->where('role', 'secretary')->count();

    $activeCount = $users->filter(function ($user) {
        return strtolower((string) $user->status) === 'active';
    })->count();

    $inactiveCount = $users->count() - $activeCount;
    $otherCount    = max(0, $users->count() - $captainCount - $secretaryCount);

    $activePct = $users->count() > 0
        ? (int) round(($activeCount / $users->count()) * 100)
        : 0;
@endphp

<div class="container-fluid py-4 ux">

    {{-- HEADER --}}
    <header class="ux-head">
        <div class="ux-headline">
            <span class="ux-mark" aria-hidden="true"><i class="bi bi-shield-lock-fill"></i></span>
            <div>
                <h1 class="ux-title">User Management</h1>
                <p class="ux-sub">Manage authorized Captain and Secretary accounts, including their profile photos.</p>
            </div>
        </div>

        <a href="{{ route('users.create') }}" class="ux-btn">
            <i class="bi bi-person-plus-fill"></i> Add Secretary
        </a>
    </header>


    {{-- OVERVIEW --}}
    <section class="ux-stats" aria-label="Account summary">
        <div class="ux-stat t-all">
            <span>Total accounts</span>
            <b>{{ number_format($users->count()) }}</b>
            <small>{{ number_format($activeCount) }} active, {{ number_format($inactiveCount) }} inactive</small>
        </div>
        <div class="ux-stat t-captain">
            <span>Captain {{ Str::plural('account', $captainCount) }}</span>
            <b>{{ number_format($captainCount) }}</b>
            <small>Sole super-admin role</small>
        </div>
        <div class="ux-stat t-secretary">
            <span>Secretary {{ Str::plural('account', $secretaryCount) }}</span>
            <b>{{ number_format($secretaryCount) }}</b>
            <small>Operational access</small>
        </div>
        <div class="ux-stat t-active">
            <span>Active {{ Str::plural('account', $activeCount) }}</span>
            <b>{{ number_format($activeCount) }}</b>
            <small>{{ $activePct }}% of all accounts</small>
        </div>
    </section>

    <div class="ux-rate">
        <span>Account mix</span>
        <div class="track {{ $users->count() ? '' : 'is-empty' }}" role="img"
             aria-label="{{ $captainCount }} captain, {{ $secretaryCount }} secretary, {{ $otherCount }} other accounts">
            @if ($users->count())
                @if ($captainCount) <span class="seg-captain" style="flex: {{ $captainCount }}"></span> @endif
                @if ($secretaryCount) <span class="seg-secretary" style="flex: {{ $secretaryCount }}"></span> @endif
                @if ($otherCount) <span class="seg-other" style="flex: {{ $otherCount }}"></span> @endif
            @else
                <span></span>
            @endif
        </div>
        <b>{{ $activePct }}% active</b>
    </div>


    {{-- ROSTER --}}
    <section class="ux-panel" aria-label="System users">

        <div class="ux-toolbar">
            <div>
                <h2>System users</h2>
                <p>People who can sign in to the Barangay Poblacion Record Management System.</p>
            </div>

            @if ($users->count())
                <div class="ux-tools">
                    <label class="ux-search">
                        <i class="bi bi-search"></i>
                        <input type="search" id="uxSearch" placeholder="Search name or email"
                               aria-label="Search users by name or email" autocomplete="off">
                    </label>

                    <div class="ux-filters" role="group" aria-label="Filter users">
                        <button type="button" class="ux-filter" data-filter="all" aria-pressed="true">All <b>{{ $users->count() }}</b></button>
                        <button type="button" class="ux-filter" data-filter="captain" aria-pressed="false">Captain <b>{{ $captainCount }}</b></button>
                        <button type="button" class="ux-filter" data-filter="secretary" aria-pressed="false">Secretary <b>{{ $secretaryCount }}</b></button>
                        <button type="button" class="ux-filter" data-filter="inactive" aria-pressed="false">Inactive <b>{{ $inactiveCount }}</b></button>
                    </div>

                    <div class="ux-views" role="group" aria-label="Change view">
                        <button type="button" class="ux-filter ux-view" data-view="list" aria-pressed="true" title="List view" aria-label="List view"><i class="bi bi-list-ul"></i></button>
                        <button type="button" class="ux-filter ux-view" data-view="cards" aria-pressed="false" title="Cards view" aria-label="Cards view"><i class="bi bi-grid-fill"></i></button>
                    </div>
                </div>
            @endif
        </div>


        @if ($users->count())

            <div class="ux-cols" id="uxCols" aria-hidden="true">
                <div>User</div>
                <div>Role</div>
                <div>Status</div>
                <div>Last updated</div>
                <div>Actions</div>
            </div>


            <div id="uxList" class="ux-list" role="list">

                @foreach ($users as $user)

                    @php
                        $role   = strtolower((string) $user->role);
                        $status = strtolower((string) $user->status);
                        $isCurrentUser = auth()->id() === $user->id;
                        $hue = crc32((string) ($user->name ?? 'U')) % 360;

                        /* Only Secretary accounts and your own Captain account are editable here. */
                        $canEdit = $role === 'secretary' || ($role === 'captain' && $isCurrentUser);

                        $photoUrl = $user->avatar
                            ? asset('storage/' . $user->avatar) . '?v=' . optional($user->updated_at)->timestamp
                            : null;

                        $searchText = strtolower(
                            (string) ($user->name ?? '') . ' ' .
                            (string) ($user->email ?? '')
                        );
                    @endphp

                    <div class="ux-row" role="listitem"
                         data-role="{{ $role }}"
                         data-status="{{ $status === 'active' ? 'active' : 'inactive' }}"
                         data-search="{{ $searchText }}">

                        {{-- User + email --}}
                        <div class="ux-person">
                            <div class="ux-avatar" style="--h: {{ $hue }}" aria-hidden="true">
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                                @if ($photoUrl)
                                    <img src="{{ $photoUrl }}" alt="" loading="lazy" onerror="this.remove()">
                                @endif
                            </div>

                            <div class="ux-who">
                                <div class="ux-name">
                                    {{ $user->name }}
                                    @if ($isCurrentUser)
                                        <span class="ux-you">You</span>
                                    @endif
                                </div>

                                <span class="ux-user {{ filled($user->email) ? '' : 'is-none' }}">
                                    {{ $user->email ?: 'No email address' }}
                                </span>
                            </div>
                        </div>


                        {{-- Role --}}
                        <div>
                            <span class="ux-cell-label">Role</span>

                            @if ($role === 'captain')
                                <span class="ux-chip is-captain"><i class="bi bi-person-badge-fill"></i> Captain</span>
                            @elseif ($role === 'secretary')
                                <span class="ux-chip is-secretary"><i class="bi bi-person-vcard-fill"></i> Secretary</span>
                            @else
                                <span class="ux-chip is-invalid"><i class="bi bi-exclamation-triangle-fill"></i> Invalid role</span>
                            @endif
                        </div>


                        {{-- Status --}}
                        <div>
                            <span class="ux-cell-label">Status</span>
                            <span class="ux-status {{ $status === 'active' ? 'is-active' : '' }}">{{ $status === 'active' ? 'Active' : 'Inactive' }}</span>
                        </div>


                        {{-- Updated --}}
                        <div class="ux-updated">
                            <span class="ux-cell-label">Last updated</span>

                            @if ($user->updated_at)
                                <b title="{{ $user->updated_at->format('M d, Y h:i A') }}">{{ $user->updated_at->diffForHumans() }}</b>
                                {{ $user->updated_at->format('M d, Y') }}
                            @else
                                —
                            @endif
                        </div>


                        {{-- Actions --}}
                        <div class="ux-actions">

                            <a href="{{ route('users.show', $user) }}" class="ux-act is-view" title="View {{ $user->name }}" aria-label="View {{ $user->name }}">
                                <i class="bi bi-eye-fill"></i><span>View</span>
                            </a>

                            @if ($canEdit)
                                <a href="{{ route('users.edit', $user) }}" class="ux-act is-edit" title="Edit {{ $user->name }} and photo" aria-label="Edit {{ $user->name }}">
                                    <i class="bi bi-pencil-square"></i><span>Edit</span>
                                </a>
                            @endif

                            @if ($status !== 'active')
                                <form id="activate-{{ $user->id }}" method="POST" action="{{ route('users.activate', $user) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="button" class="ux-act is-ok" data-confirm
                                        data-form="activate-{{ $user->id }}" data-tone="ok"
                                        data-title="Activate this account?"
                                        data-message="{{ $user->name }} will be able to sign in again."
                                        data-button="Activate">
                                        <i class="bi bi-person-check-fill"></i><span>Activate</span>
                                    </button>
                                </form>
                            @endif

                            @if ($status === 'active' && ! $isCurrentUser)
                                <form id="deactivate-{{ $user->id }}" method="POST" action="{{ route('users.deactivate', $user) }}">
                                    @csrf
                                    @method('PATCH')

                                    <button type="button" class="ux-act is-bad" data-confirm
                                        data-form="deactivate-{{ $user->id }}" data-tone="bad"
                                        data-title="Deactivate this account?"
                                        data-message="{{ $user->name }} will no longer be able to sign in until the account is activated again."
                                        data-button="Deactivate">
                                        <i class="bi bi-person-x-fill"></i><span>Deactivate</span>
                                    </button>
                                </form>
                            @endif

                        </div>

                    </div>

                @endforeach

            </div>


            <div class="ux-empty" id="uxNoMatch" hidden>
                <i class="bi bi-search"></i>
                <h3>No matching accounts</h3>
                <p>Try a different name or email address, or clear the filter.</p>
                <button type="button" class="ux-btn ux-btn--line" id="uxClear">Clear search and filter</button>
            </div>


            <div class="ux-foot" id="uxCount" aria-live="polite">
                Showing {{ $users->count() }} of {{ $users->count() }} {{ Str::plural('user', $users->count()) }}
            </div>

        @else

            <div class="ux-empty">
                <i class="bi bi-people"></i>
                <h3>No user accounts yet</h3>
                <p>Add a Secretary account to give someone access to the system.</p>
                <a href="{{ route('users.create') }}" class="ux-btn">
                    <i class="bi bi-person-plus-fill"></i> Add Secretary
                </a>
            </div>

        @endif

    </section>

</div>


{{-- CONFIRM DIALOG --}}
<dialog class="ux-dialog" id="uxConfirm" aria-labelledby="uxConfirmTitle">
    <div class="ux-dialog-body">
        <div class="ux-dialog-icon" id="uxConfirmIcon"><i class="bi bi-question-circle-fill"></i></div>
        <h3 id="uxConfirmTitle">Are you sure?</h3>
        <p id="uxConfirmMessage"></p>
    </div>

    <div class="ux-dialog-actions">
        <button type="button" class="ux-btn ux-btn--line" id="uxConfirmCancel">Cancel</button>
        <button type="button" class="ux-btn" id="uxConfirmOk">Confirm</button>
    </div>
</dialog>


<script>
(function () {

    const list = document.getElementById('uxList');

    /* ---------------- Confirm dialog ---------------- */
    const dialog = document.getElementById('uxConfirm');
    const dlgTitle = document.getElementById('uxConfirmTitle');
    const dlgMsg = document.getElementById('uxConfirmMessage');
    const dlgIcon = document.getElementById('uxConfirmIcon');
    const dlgOk = document.getElementById('uxConfirmOk');
    const dlgCancel = document.getElementById('uxConfirmCancel');

    let pendingForm = null;

    document.querySelectorAll('[data-confirm]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const form = document.getElementById(btn.dataset.form);
            if (!form) return;

            if (!dialog || typeof dialog.showModal !== 'function') {
                if (window.confirm(btn.dataset.title)) form.submit();
                return;
            }

            const bad = btn.dataset.tone === 'bad';
            pendingForm = form;

            dlgTitle.textContent = btn.dataset.title;
            dlgMsg.textContent = btn.dataset.message;
            dlgOk.textContent = btn.dataset.button;
            dlgOk.className = 'ux-btn ' + (bad ? 'ux-btn--danger' : '');
            dlgIcon.style.setProperty('--dlg-tone', bad ? 'var(--ux-coral)' : 'var(--ux-ok)');
            dlgIcon.innerHTML = bad
                ? '<i class="bi bi-person-x-fill"></i>'
                : '<i class="bi bi-person-check-fill"></i>';

            dialog.showModal();
            dlgCancel.focus();
        });
    });

    if (dialog) {
        dlgCancel.addEventListener('click', () => dialog.close());

        dlgOk.addEventListener('click', () => {
            const form = pendingForm;
            dialog.close();

            if (form) form.submit();
        });

        dialog.addEventListener('click', (e) => {
            if (e.target === dialog) dialog.close();
        });
    }

    if (!list) return;

    /* ---------------- Search + filter ---------------- */
    const rows = Array.from(list.querySelectorAll('.ux-row'));
    const search = document.getElementById('uxSearch');
    const filters = Array.from(document.querySelectorAll('.ux-filter[data-filter]'));
    const noMatch = document.getElementById('uxNoMatch');
    const count = document.getElementById('uxCount');
    const clear = document.getElementById('uxClear');

    let active = 'all';

    function apply() {
        const q = search ? search.value.trim().toLowerCase() : '';
        let shown = 0;

        rows.forEach((row) => {
            const matchesText = !q || (row.dataset.search || '').includes(q);
            let matchesFilter = true;

            if (active === 'captain' || active === 'secretary') {
                matchesFilter = row.dataset.role === active;
            }

            if (active === 'inactive') {
                matchesFilter = row.dataset.status === 'inactive';
            }

            const visible = matchesText && matchesFilter;

            row.hidden = !visible;

            if (visible) shown++;
        });

        if (noMatch) noMatch.hidden = shown > 0;
        list.hidden = shown === 0;

        if (count) {
            count.textContent = 'Showing ' + shown + ' of ' + rows.length + ' ' +
                (rows.length === 1 ? 'user' : 'users');
        }
    }

    filters.forEach((btn) => {
        btn.addEventListener('click', () => {
            active = btn.dataset.filter;

            filters.forEach((b) => {
                b.setAttribute('aria-pressed', b === btn ? 'true' : 'false');
            });

            apply();
        });
    });

    if (search) {
        search.addEventListener('input', apply);
    }

    if (clear) {
        clear.addEventListener('click', () => {
            if (search) search.value = '';

            active = 'all';

            filters.forEach((b) => {
                b.setAttribute('aria-pressed', b.dataset.filter === 'all' ? 'true' : 'false');
            });

            apply();

            if (search) search.focus();
        });
    }

    /* ---------------- List / Cards view ---------------- */
    const cols = document.getElementById('uxCols');
    const views = Array.from(document.querySelectorAll('.ux-view'));

    function setView(mode, remember) {
        const cards = mode === 'cards';

        list.classList.toggle('is-cards', cards);

        if (cols) cols.hidden = cards;

        views.forEach((b) => {
            b.setAttribute('aria-pressed', b.dataset.view === mode ? 'true' : 'false');
        });

        if (remember) {
            try {
                localStorage.setItem('ux-users-view', mode);
            } catch (e) {
                /* Storage may be unavailable; view switching still works. */
            }
        }
    }

    views.forEach((b) => {
        b.addEventListener('click', () => setView(b.dataset.view, true));
    });

    let saved = 'list';

    try {
        saved = localStorage.getItem('ux-users-view') || 'list';
    } catch (e) {
        /* Use list view when storage is unavailable. */
    }

    setView(saved === 'cards' ? 'cards' : 'list', false);

})();
</script>

@endsection