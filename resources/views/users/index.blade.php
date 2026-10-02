@extends('layouts.app')

@section('title', 'User Management')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* ============================================================
       USER MANAGEMENT - "Access desk" layout
       Supports data-theme: light / dark / system
       ============================================================ */

    .ux {
        --ux-display: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
        --ux-body: 'Figtree', 'Segoe UI', system-ui, sans-serif;

        --ux-surface: #ffffff;
        --ux-sunken: #f3f6f6;
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
        --ux-shadow: 0 1px 2px rgba(14, 42, 51, .05), 0 10px 30px rgba(14, 42, 51, .07);
        --ux-av-bg: 90%;
        --ux-av-s: 60%;
        --ux-av-fg: 26%;

        font-family: var(--ux-body);
        color: var(--ux-text);
    }

    [data-theme="dark"] .ux {
        --ux-surface: #14262d;
        --ux-sunken: #0f1f25;
        --ux-line: #25404a;
        --ux-line-strong: #33525d;
        --ux-ink: #eaf5f3;
        --ux-text: #c9dbde;
        --ux-muted: #8aa5ac;
        --ux-teal: #3dd0c0;
        --ux-teal-soft: #123a38;
        --ux-indigo: #8f9bff;
        --ux-indigo-soft: #1f2447;
        --ux-ok: #56d99b;
        --ux-ok-soft: #123626;
        --ux-coral: #ff8a6b;
        --ux-coral-soft: #3d2019;
        --ux-sun: #f3c14a;
        --ux-sun-soft: #3a3016;
        --ux-focus: rgba(61, 208, 192, .35);
        --ux-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 10px 30px rgba(0, 0, 0, .32);
        --ux-av-bg: 26%;
        --ux-av-s: 38%;
        --ux-av-fg: 82%;
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .ux {
            --ux-surface: #14262d;
            --ux-sunken: #0f1f25;
            --ux-line: #25404a;
            --ux-line-strong: #33525d;
            --ux-ink: #eaf5f3;
            --ux-text: #c9dbde;
            --ux-muted: #8aa5ac;
            --ux-teal: #3dd0c0;
            --ux-teal-soft: #123a38;
            --ux-indigo: #8f9bff;
            --ux-indigo-soft: #1f2447;
            --ux-ok: #56d99b;
            --ux-ok-soft: #123626;
            --ux-coral: #ff8a6b;
            --ux-coral-soft: #3d2019;
            --ux-sun: #f3c14a;
            --ux-sun-soft: #3a3016;
            --ux-focus: rgba(61, 208, 192, .35);
            --ux-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 10px 30px rgba(0, 0, 0, .32);
            --ux-av-bg: 26%;
            --ux-av-s: 38%;
            --ux-av-fg: 82%;
        }
    }

    .ux *, .ux *::before, .ux *::after {
        box-sizing: border-box;
    }

    .ux a {
        text-decoration: none;
    }

    .ux :focus-visible,
    .ux-dialog :focus-visible {
        outline: 3px solid var(--ux-focus);
        outline-offset: 2px;
        border-radius: 8px;
    }

    /* ---------- Header ---------- */

    .ux-head {
        display: flex;
        flex-wrap: wrap;
        gap: 18px 24px;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 22px;
    }

    .ux-headline {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .ux-mark {
        display: grid;
        place-items: center;
        width: 56px;
        height: 56px;
        flex: none;
        border-radius: 18px;
        font-size: 1.5rem;
        color: var(--ux-teal);
        background: var(--ux-teal-soft);
        box-shadow: inset 0 0 0 1px color-mix(in srgb, var(--ux-teal) 25%, transparent);
    }

    .ux-title {
        margin: 0;
        font-family: var(--ux-display);
        font-weight: 800;
        font-size: clamp(1.7rem, 3.2vw, 2.4rem);
        letter-spacing: -.025em;
        line-height: 1.05;
        color: var(--ux-ink);
    }

    .ux-sub {
        margin: 6px 0 0;
        color: var(--ux-muted);
        max-width: 58ch;
    }

    .ux-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 46px;
        padding: 0 20px;
        border-radius: 14px;
        cursor: pointer;
        font: 600 .93rem var(--ux-body);
        background: var(--ux-ink);
        color: var(--ux-surface);
        border: 1px solid var(--ux-ink);
        transition: filter .15s, transform .1s, background .15s;
    }

    .ux-btn:hover {
        filter: brightness(1.18);
        color: var(--ux-surface);
    }

    .ux-btn:active {
        transform: translateY(1px);
    }

    .ux-btn--line {
        background: transparent;
        color: var(--ux-ink);
        border-color: var(--ux-line-strong);
    }

    .ux-btn--line:hover {
        background: var(--ux-sunken);
        color: var(--ux-ink);
        filter: none;
    }

    .ux-btn--danger {
        background: var(--ux-coral);
        border-color: var(--ux-coral);
        color: #fff;
    }

    .ux-btn--danger:hover {
        color: #fff;
    }

    /* ---------- Alerts ---------- */

    .ux-alert {
        display: flex;
        gap: 12px;
        padding: 14px 16px;
        border-radius: 14px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        font-size: .95rem;
    }

    .ux-alert i {
        font-size: 1.1rem;
        margin-top: 1px;
    }

    .ux-alert ul {
        margin: 6px 0 0;
        padding-left: 18px;
    }

    .ux-alert strong {
        display: block;
    }

    .ux-alert--err {
        background: var(--ux-coral-soft);
        color: var(--ux-coral);
        border-color: color-mix(in srgb, var(--ux-coral) 30%, transparent);
    }

    .ux-alert--ok {
        background: var(--ux-ok-soft);
        color: var(--ux-ok);
        border-color: color-mix(in srgb, var(--ux-ok) 30%, transparent);
    }

    .ux-alert-close {
        margin-left: auto;
        background: none;
        border: 0;
        color: inherit;
        cursor: pointer;
        font-size: 1.2rem;
        line-height: 1;
    }

    /* ---------- Overview ---------- */

    .ux-hero {
        position: relative;
        overflow: hidden;
        margin-bottom: 24px;
        border-radius: 26px;
        padding: 30px 32px;
        display: grid;
        grid-template-columns: minmax(200px, .8fr) minmax(0, 1.6fr);
        gap: 32px 48px;
        align-items: center;
        color: #eaf5f3;
        background: linear-gradient(125deg, #082229 0%, #0b3a3e 55%, #0f5a54 100%);
        box-shadow: var(--ux-shadow);
    }

    .ux-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        opacity: .5;
        background-image: radial-gradient(rgba(255, 255, 255, .13) 1px, transparent 1.4px);
        background-size: 18px 18px;
        -webkit-mask-image: linear-gradient(100deg, transparent 25%, #000 100%);
        mask-image: linear-gradient(100deg, transparent 25%, #000 100%);
    }

    .ux-hero > * {
        position: relative;
    }

    .ux-total-label {
        font-size: .92rem;
        font-weight: 600;
        color: rgba(234, 245, 243, .72);
    }

    .ux-total {
        margin-top: 6px;
        font-family: var(--ux-display);
        font-weight: 800;
        font-size: clamp(3rem, 6vw, 4.4rem);
        letter-spacing: -.04em;
        line-height: .95;
        font-variant-numeric: tabular-nums;
    }

    .ux-total-note {
        margin-top: 12px;
        font-size: .9rem;
        color: rgba(234, 245, 243, .72);
    }

    .ux-mix-bar {
        display: flex;
        gap: 4px;
        height: 16px;
    }

    .ux-mix-bar span {
        min-width: 8px;
        border-radius: 6px;
        background: var(--seg);
    }

    .ux-mix-bar.is-empty span {
        flex: 1;
        background: rgba(255, 255, 255, .15);
    }

    .ux-legend {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 20px;
    }

    .ux-leg {
        padding-left: 14px;
        border-left: 3px solid var(--seg);
    }

    .ux-leg b {
        display: block;
        font-family: var(--ux-display);
        font-weight: 800;
        font-size: 1.7rem;
        line-height: 1.05;
        font-variant-numeric: tabular-nums;
    }

    .ux-leg span {
        display: block;
        margin-top: 3px;
        font-size: .84rem;
        color: rgba(234, 245, 243, .72);
    }

    .seg-captain {
        --seg: #8f9bff;
    }

    .seg-secretary {
        --seg: #3dd0c0;
    }

    .seg-active {
        --seg: #56d99b;
    }

    .seg-other {
        --seg: #f3c14a;
    }

    .ux-live {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-top: 22px;
        font-size: .86rem;
        color: rgba(234, 245, 243, .78);
    }

    .ux-live .track {
        flex: 1;
        height: 6px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .16);
        overflow: hidden;
    }

    .ux-live .track span {
        display: block;
        height: 100%;
        border-radius: 999px;
        background: #56d99b;
    }

    .ux-live b {
        color: #fff;
        font-variant-numeric: tabular-nums;
    }

    /* ---------- Roster panel ---------- */

    .ux-panel {
        background: var(--ux-surface);
        border: 1px solid var(--ux-line);
        border-radius: 24px;
        box-shadow: var(--ux-shadow);
        overflow: hidden;
    }

    .ux-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        align-items: center;
        justify-content: space-between;
        padding: 22px 24px;
    }

    .ux-toolbar h2 {
        margin: 0;
        font-family: var(--ux-display);
        font-weight: 700;
        font-size: 1.2rem;
        letter-spacing: -.01em;
        color: var(--ux-ink);
    }

    .ux-toolbar p {
        margin: 3px 0 0;
        font-size: .86rem;
        color: var(--ux-muted);
    }

    .ux-tools {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
    }

    .ux-search {
        position: relative;
        margin: 0;
    }

    .ux-search i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--ux-muted);
        pointer-events: none;
    }

    .ux-search input {
        width: 250px;
        max-width: 100%;
        min-height: 44px;
        padding: 0 14px 0 40px;
        border-radius: 14px;
        border: 1px solid var(--ux-line-strong);
        background: var(--ux-sunken);
        color: var(--ux-ink);
        font: 500 .92rem var(--ux-body);
        transition: border-color .15s, box-shadow .15s, background .15s;
    }

    .ux-search input::placeholder {
        color: var(--ux-muted);
        opacity: .85;
    }

    .ux-search input:focus {
        outline: none;
        background: var(--ux-surface);
        border-color: var(--ux-teal);
        box-shadow: 0 0 0 4px var(--ux-focus);
    }

    .ux-filters {
        display: inline-flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 4px;
        border-radius: 15px;
        background: var(--ux-sunken);
        border: 1px solid var(--ux-line);
    }

    .ux-filter {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 13px;
        border: 0;
        border-radius: 11px;
        background: transparent;
        cursor: pointer;
        font: 600 .85rem var(--ux-body);
        color: var(--ux-muted);
        transition: background .15s, color .15s;
    }

    .ux-filter b {
        min-width: 22px;
        padding: 1px 7px;
        border-radius: 999px;
        text-align: center;
        font-size: .76rem;
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        background: var(--ux-line);
        color: var(--ux-text);
    }

    .ux-filter:hover {
        color: var(--ux-ink);
    }

    .ux-filter[aria-pressed="true"] {
        background: var(--ux-surface);
        color: var(--ux-ink);
        box-shadow: 0 1px 3px rgba(14, 42, 51, .16);
    }

    .ux-filter[aria-pressed="true"] b {
        background: var(--ux-ink);
        color: var(--ux-surface);
    }

    /* ---------- Rows ---------- */

    /*
     * IMPORTANT ALIGNMENT FIX
     *
     * The header and every row now use the exact same five-column
     * grid definition. The Role, Status, and Last Updated columns
     * are centered as complete grid cells, so the heading sits
     * directly above the corresponding value.
     */
    .ux-cols,
    .ux-row {
        display: grid;
        align-items: center;
        gap: 18px;
        padding-left: 24px;
        padding-right: 24px;

        grid-template-columns:
            minmax(250px, 2.4fr)
            minmax(130px, 1fr)
            minmax(110px, .8fr)
            minmax(130px, 1fr)
            150px;
    }

    .ux-cols {
        padding-top: 12px;
        padding-bottom: 12px;
        background: var(--ux-sunken);
        border-top: 1px solid var(--ux-line);
        border-bottom: 1px solid var(--ux-line);
        font-size: .82rem;
        font-weight: 700;
        color: var(--ux-muted);
    }

    /*
     * Header cells for Role, Status and Last updated.
     * These are centered within their exact grid columns.
     */
    .ux-cols > div:nth-child(2),
    .ux-cols > div:nth-child(3),
    .ux-cols > div:nth-child(4) {
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    /*
     * Actions header stays right aligned.
     */
    .ux-cols > div:nth-child(5) {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        text-align: right;
    }

    .ux-row {
        position: relative;
        padding-top: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid var(--ux-line);
        transition: background .15s;
    }

    .ux-row::before {
        content: '';
        position: absolute;
        left: 0;
        top: 14px;
        bottom: 14px;
        width: 4px;
        border-radius: 0 4px 4px 0;
        background: var(--rail, var(--ux-line-strong));
        opacity: .85;
    }

    .ux-row[data-role="captain"] {
        --rail: var(--ux-indigo);
    }

    .ux-row[data-role="secretary"] {
        --rail: var(--ux-teal);
    }

    .ux-row[data-status="inactive"]::before {
        opacity: .3;
    }

    .ux-row:last-child {
        border-bottom: 0;
    }

    .ux-row:hover {
        background: var(--ux-sunken);
    }

    .ux-row[hidden] {
        display: none;
    }

    .ux-cell-label {
        display: none;
    }

    /*
     * Exact same cell alignment as the header.
     */
    .ux-row > div:nth-child(2),
    .ux-row > div:nth-child(3),
    .ux-row > div:nth-child(4) {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        min-width: 0;
    }

    .ux-person {
        display: flex;
        align-items: center;
        gap: 14px;
        min-width: 0;
    }

    .ux-avatar {
        position: relative;
        display: grid;
        place-items: center;
        width: 46px;
        height: 46px;
        flex: none;
        border-radius: 50%;
        font-family: var(--ux-display);
        font-weight: 800;
        font-size: 1.1rem;
        background: hsl(var(--h) var(--ux-av-s) var(--ux-av-bg));
        color: hsl(var(--h) 55% var(--ux-av-fg));
        box-shadow:
            0 0 0 2px var(--ux-surface),
            0 0 0 4px hsl(
                var(--h)
                var(--ux-av-s)
                calc(var(--ux-av-bg) - 6%)
            );
    }

    .ux-who {
        min-width: 0;
    }

    .ux-name {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: wrap;
        font-weight: 700;
        color: var(--ux-ink);
        overflow-wrap: anywhere;
    }

    .ux-you {
        padding: 2px 9px;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
        background: var(--ux-teal-soft);
        color: var(--ux-teal);
    }

    .ux-user {
        display: block;
        margin-top: 2px;
        font-size: .86rem;
        font-weight: 500;
        color: var(--ux-muted);
        overflow-wrap: anywhere;
    }

    .ux-user::before {
        content: '@';
        opacity: .7;
    }

    .ux-user.is-none::before {
        content: '';
    }

    .ux-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 6px 13px;
        border-radius: 10px;
        font-size: .82rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .ux-chip.is-captain {
        background: var(--ux-indigo-soft);
        color: var(--ux-indigo);
    }

    .ux-chip.is-secretary {
        background: var(--ux-teal-soft);
        color: var(--ux-teal);
    }

    .ux-chip.is-invalid {
        background: var(--ux-coral-soft);
        color: var(--ux-coral);
    }

    .ux-status {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 5px 12px 5px 10px;
        border-radius: 999px;
        font-weight: 600;
        font-size: .85rem;
        color: var(--ux-muted);
        background: var(--ux-sunken);
        border: 1px solid var(--ux-line);
        white-space: nowrap;
    }

    .ux-status::before {
        content: '';
        width: 8px;
        height: 8px;
        flex: none;
        border-radius: 50%;
        background: var(--ux-line-strong);
    }

    .ux-status.is-active {
        color: var(--ux-ok);
        background: var(--ux-ok-soft);
        border-color: transparent;
    }

    .ux-status.is-active::before {
        background: var(--ux-ok);
    }

    .ux-updated {
        font-size: .86rem;
        color: var(--ux-muted);
        text-align: center;
    }

    .ux-updated b {
        display: block;
        font-weight: 600;
        color: var(--ux-text);
    }

    .ux-actions {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 6px;
        min-width: 0;
    }

    .ux-actions form {
        margin: 0;
    }

    .ux-icon,
    .ux-act {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        cursor: pointer;
        border: 1px solid var(--ux-line-strong);
        background: var(--ux-surface);
        color: var(--ux-ink);
        border-radius: 11px;
        min-height: 38px;
        font: 600 .82rem var(--ux-body);
        transition: background .15s, border-color .15s, color .15s;
    }

    .ux-icon {
        width: 38px;
    }

    .ux-act {
        padding: 0 13px;
    }

    .ux-icon:hover,
    .ux-act:hover {
        background: var(--ux-sunken);
        border-color: var(--ux-muted);
        color: var(--ux-ink);
    }

    .ux-act.is-ok {
        color: var(--ux-ok);
        border-color: color-mix(in srgb, var(--ux-ok) 45%, transparent);
    }

    .ux-act.is-ok:hover {
        background: var(--ux-ok-soft);
        border-color: var(--ux-ok);
        color: var(--ux-ok);
    }

    .ux-act.is-bad {
        color: var(--ux-coral);
        border-color: color-mix(in srgb, var(--ux-coral) 45%, transparent);
    }

    .ux-act.is-bad:hover {
        background: var(--ux-coral-soft);
        border-color: var(--ux-coral);
        color: var(--ux-coral);
    }

    /* ---------- Empty states ---------- */

    .ux-empty {
        display: grid;
        justify-items: center;
        gap: 8px;
        padding: 56px 22px;
        text-align: center;
        color: var(--ux-muted);
        border-top: 1px solid var(--ux-line);
    }

    .ux-empty[hidden] {
        display: none;
    }

    .ux-empty i {
        font-size: 2.2rem;
    }

    .ux-empty h3 {
        margin: 4px 0 0;
        font-family: var(--ux-display);
        font-weight: 700;
        font-size: 1.15rem;
        color: var(--ux-ink);
    }

    .ux-empty p {
        margin: 0 0 8px;
        max-width: 42ch;
    }

    .ux-foot {
        padding: 14px 24px;
        border-top: 1px solid var(--ux-line);
        background: var(--ux-sunken);
        font-size: .85rem;
        color: var(--ux-muted);
    }

    /* ---------- Confirm dialog ---------- */

    .ux-dialog {
        width: min(440px, calc(100vw - 32px));
        padding: 0;
        border: 1px solid var(--ux-line);
        border-radius: 24px;
        background: var(--ux-surface);
        color: var(--ux-text);
        box-shadow: 0 24px 60px rgba(0, 0, 0, .28);
        font-family: var(--ux-body);

        --ux-display: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
        --ux-body: 'Figtree', 'Segoe UI', system-ui, sans-serif;
        --ux-surface: #ffffff;
        --ux-line: #e0e7e5;
        --ux-ink: #0e2a33;
        --ux-text: #2b4149;
        --ux-muted: #66808a;
        --ux-line-strong: #c8d3d0;
        --ux-sunken: #f3f6f6;
        --ux-coral: #d4492c;
        --ux-ok: #178a55;
        --ux-focus: rgba(15,118,110,.3);
    }

    [data-theme="dark"] .ux-dialog {
        --ux-surface: #14262d;
        --ux-line: #25404a;
        --ux-ink: #eaf5f3;
        --ux-text: #c9dbde;
        --ux-muted: #8aa5ac;
        --ux-line-strong: #33525d;
        --ux-sunken: #0f1f25;
        --ux-coral: #ff8a6b;
        --ux-ok: #56d99b;
        --ux-focus: rgba(61,208,192,.35);
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .ux-dialog {
            --ux-surface: #14262d;
            --ux-line: #25404a;
            --ux-ink: #eaf5f3;
            --ux-text: #c9dbde;
            --ux-muted: #8aa5ac;
            --ux-line-strong: #33525d;
            --ux-sunken: #0f1f25;
            --ux-coral: #ff8a6b;
            --ux-ok: #56d99b;
            --ux-focus: rgba(61,208,192,.35);
        }
    }

    .ux-dialog::backdrop {
        background: rgba(8, 20, 26, .55);
        backdrop-filter: blur(3px);
    }

    .ux-dialog-body {
        padding: 28px 28px 20px;
    }

    .ux-dialog-icon {
        display: grid;
        place-items: center;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin-bottom: 16px;
        font-size: 1.35rem;
        background: color-mix(in srgb, var(--dlg-tone) 14%, transparent);
        color: var(--dlg-tone);
    }

    .ux-dialog h3 {
        margin: 0;
        font-family: var(--ux-display);
        font-weight: 800;
        font-size: 1.3rem;
        color: var(--ux-ink);
    }

    .ux-dialog p {
        margin: 8px 0 0;
        line-height: 1.55;
        color: var(--ux-muted);
    }

    .ux-dialog-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding: 16px 28px 24px;
    }

    .ux-dialog .ux-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 46px;
        padding: 0 20px;
        border-radius: 14px;
        cursor: pointer;
        font: 600 .93rem var(--ux-body);
        background: var(--ux-ink);
        color: var(--ux-surface);
        border: 1px solid var(--ux-ink);
    }

    .ux-dialog .ux-btn--line {
        background: transparent;
        color: var(--ux-ink);
        border-color: var(--ux-line-strong);
    }

    .ux-dialog .ux-btn--danger {
        background: var(--ux-coral);
        border-color: var(--ux-coral);
        color: #fff;
    }

    /* ---------- Responsive ---------- */

    @media (max-width: 1099.98px) {

        .ux-hero {
            grid-template-columns: 1fr;
            padding: 26px 24px;
        }

        .ux-cols {
            display: none;
        }

        .ux-row {
            grid-template-columns: 1fr 1fr;
            gap: 14px 18px;
            padding: 18px 20px 18px 24px;
        }

        .ux-row .ux-person,
        .ux-row .ux-actions {
            grid-column: 1 / -1;
        }

        .ux-actions {
            justify-content: flex-start;
            padding-top: 4px;
        }

        .ux-cell-label {
            display: block;
            margin-bottom: 5px;
            font-size: .74rem;
            font-weight: 600;
            color: var(--ux-muted);
        }

        /*
         * On stacked/tablet layout, use left-aligned
         * label/value pairs.
         */
        .ux-row > div:nth-child(2),
        .ux-row > div:nth-child(3),
        .ux-row > div:nth-child(4) {
            align-items: flex-start;
            justify-content: flex-start;
            text-align: left;
        }

        .ux-updated {
            text-align: left;
        }
    }

    @media (max-width: 767.98px) {

        .ux-mark {
            width: 48px;
            height: 48px;
            font-size: 1.25rem;
            border-radius: 15px;
        }

        .ux-legend {
            grid-template-columns: 1fr;
            gap: 12px;
        }

        .ux-toolbar {
            padding: 18px;
        }

        .ux-tools,
        .ux-search,
        .ux-search input,
        .ux-head .ux-btn {
            width: 100%;
        }

        .ux-filters {
            width: 100%;
        }

        .ux-filter {
            flex: 1;
            justify-content: center;
            padding: 8px 6px;
        }
    }

    @media (prefers-reduced-motion: reduce) {

        .ux-btn,
        .ux-row,
        .ux-filter,
        .ux-icon,
        .ux-act {
            transition: none;
        }
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

    {{-- =========================================================
         HEADER
    ========================================================== --}}

    <header class="ux-head">

        <div class="ux-headline">

            <span class="ux-mark" aria-hidden="true">
                <i class="bi bi-shield-lock-fill"></i>
            </span>

            <div>
                <h1 class="ux-title">User Management</h1>

                <p class="ux-sub">
                    Manage authorized Captain and Secretary accounts.
                </p>
            </div>

        </div>

        <a href="{{ route('users.create') }}" class="ux-btn">
            <i class="bi bi-person-plus-fill"></i>
            Add Secretary
        </a>

    </header>


    {{-- =========================================================
         OVERVIEW
    ========================================================== --}}

    <section class="ux-hero" aria-label="Account summary">

        <div>

            <div class="ux-total-label">
                Total accounts
            </div>

            <div class="ux-total">
                {{ number_format($users->count()) }}
            </div>

            <div class="ux-total-note">
                {{ number_format($activeCount) }} active,
                {{ number_format($inactiveCount) }} inactive
            </div>

        </div>


        <div>

            <div
                class="ux-mix-bar {{ $users->count() ? '' : 'is-empty' }}"
                role="img"
                aria-label="{{ $captainCount }} captain, {{ $secretaryCount }} secretary, {{ $otherCount }} other accounts"
            >

                @if ($users->count())

                    @if ($captainCount)
                        <span
                            class="seg-captain"
                            style="flex: {{ $captainCount }}"
                        ></span>
                    @endif

                    @if ($secretaryCount)
                        <span
                            class="seg-secretary"
                            style="flex: {{ $secretaryCount }}"
                        ></span>
                    @endif

                    @if ($otherCount)
                        <span
                            class="seg-other"
                            style="flex: {{ $otherCount }}"
                        ></span>
                    @endif

                @else

                    <span></span>

                @endif

            </div>


            <div class="ux-legend">

                <div class="ux-leg seg-captain">

                    <b>
                        {{ number_format($captainCount) }}
                    </b>

                    <span>
                        Captain {{ Str::plural('account', $captainCount) }}
                    </span>

                </div>


                <div class="ux-leg seg-secretary">

                    <b>
                        {{ number_format($secretaryCount) }}
                    </b>

                    <span>
                        Secretary {{ Str::plural('account', $secretaryCount) }}
                    </span>

                </div>


                <div class="ux-leg seg-active">

                    <b>
                        {{ number_format($activeCount) }}
                    </b>

                    <span>
                        Active {{ Str::plural('account', $activeCount) }}
                    </span>

                </div>

            </div>


            <div class="ux-live">

                <span>
                    Active rate
                </span>

                <div
                    class="track"
                    role="img"
                    aria-label="{{ $activePct }} percent of accounts active"
                >
                    <span style="width: {{ $activePct }}%"></span>
                </div>

                <b>
                    {{ $activePct }}%
                </b>

            </div>

        </div>

    </section>


    {{-- =========================================================
         ROSTER
    ========================================================== --}}

    <section class="ux-panel" aria-label="System users">

        <div class="ux-toolbar">

            <div>

                <h2>
                    System users
                </h2>

                <p>
                    People who can sign in to the Barangay Poblacion
                    Record Management System.
                </p>

            </div>


            @if ($users->count())

                <div class="ux-tools">

                    <label class="ux-search">

                        <i class="bi bi-search"></i>

                        <input
                            type="search"
                            id="uxSearch"
                            placeholder="Search name or username"
                            aria-label="Search users"
                            autocomplete="off"
                        >

                    </label>


                    <div
                        class="ux-filters"
                        role="group"
                        aria-label="Filter users"
                    >

                        <button
                            type="button"
                            class="ux-filter"
                            data-filter="all"
                            aria-pressed="true"
                        >
                            All
                            <b>{{ $users->count() }}</b>
                        </button>


                        <button
                            type="button"
                            class="ux-filter"
                            data-filter="captain"
                            aria-pressed="false"
                        >
                            Captain
                            <b>{{ $captainCount }}</b>
                        </button>


                        <button
                            type="button"
                            class="ux-filter"
                            data-filter="secretary"
                            aria-pressed="false"
                        >
                            Secretary
                            <b>{{ $secretaryCount }}</b>
                        </button>


                        <button
                            type="button"
                            class="ux-filter"
                            data-filter="inactive"
                            aria-pressed="false"
                        >
                            Inactive
                            <b>{{ $inactiveCount }}</b>
                        </button>

                    </div>

                </div>

            @endif

        </div>


        @if ($users->count())

            {{-- =================================================
                 DESKTOP TABLE HEADER
            ================================================== --}}

            <div class="ux-cols" aria-hidden="true">

                <div>User</div>

                <div>Role</div>

                <div>Status</div>

                <div>Last updated</div>

                <div>
                    Actions
                </div>

            </div>


            <div id="uxList" role="list">

                @foreach ($users as $user)

                    @php
                        $role   = strtolower((string) $user->role);
                        $status = strtolower((string) $user->status);
                        $isCurrentUser = auth()->id() === $user->id;
                        $hue = crc32((string) ($user->name ?? 'U')) % 360;
                    @endphp


                    <div
                        class="ux-row"
                        role="listitem"
                        data-role="{{ $role }}"
                        data-status="{{ $status === 'active' ? 'active' : 'inactive' }}"
                        data-search="{{ strtolower(($user->name ?? '') . ' ' . ($user->username ?? '')) }}"
                    >

                        {{-- User + username --}}

                        <div class="ux-person">

                            <div
                                class="ux-avatar"
                                style="--h: {{ $hue }}"
                                aria-hidden="true"
                            >
                                {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                            </div>


                            <div class="ux-who">

                                <div class="ux-name">

                                    {{ $user->name }}

                                    @if ($isCurrentUser)

                                        <span class="ux-you">
                                            You
                                        </span>

                                    @endif

                                </div>


                                <span class="ux-user {{ $user->username ? '' : 'is-none' }}">
                                    {{ $user->username ?? '—' }}
                                </span>

                            </div>

                        </div>


                        {{-- Role --}}

                        <div>

                            <span class="ux-cell-label">
                                Role
                            </span>

                            @if ($role === 'captain')

                                <span class="ux-chip is-captain">
                                    <i class="bi bi-person-badge-fill"></i>
                                    Captain
                                </span>

                            @elseif ($role === 'secretary')

                                <span class="ux-chip is-secretary">
                                    <i class="bi bi-person-vcard-fill"></i>
                                    Secretary
                                </span>

                            @else

                                <span class="ux-chip is-invalid">
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                    Invalid role
                                </span>

                            @endif

                        </div>


                        {{-- Status --}}

                        <div>

                            <span class="ux-cell-label">
                                Status
                            </span>

                            <span class="ux-status {{ $status === 'active' ? 'is-active' : '' }}">

                                {{ $status === 'active' ? 'Active' : 'Inactive' }}

                            </span>

                        </div>


                        {{-- Updated --}}

                        <div class="ux-updated">

                            <span class="ux-cell-label">
                                Last updated
                            </span>

                            @if ($user->updated_at)

                                <b title="{{ $user->updated_at->format('M d, Y h:i A') }}">
                                    {{ $user->updated_at->diffForHumans() }}
                                </b>

                                {{ $user->updated_at->format('M d, Y') }}

                            @else

                                —

                            @endif

                        </div>


                        {{-- Actions --}}

                        <div class="ux-actions">

                            <a
                                href="{{ route('users.show', $user) }}"
                                class="ux-icon"
                                title="View user"
                                aria-label="View {{ $user->name }}"
                            >
                                <i class="bi bi-eye-fill"></i>
                            </a>


                            <a
                                href="{{ route('users.edit', $user) }}"
                                class="ux-icon"
                                title="Edit user"
                                aria-label="Edit {{ $user->name }}"
                            >
                                <i class="bi bi-pencil-fill"></i>
                            </a>


                            {{-- Activate --}}

                            @if ($status !== 'active')

                                <form
                                    id="activate-{{ $user->id }}"
                                    method="POST"
                                    action="{{ route('users.activate', $user) }}"
                                >

                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="button"
                                        class="ux-act is-ok"
                                        data-confirm
                                        data-form="activate-{{ $user->id }}"
                                        data-tone="ok"
                                        data-title="Activate this account?"
                                        data-message="{{ $user->name }} will be able to sign in again."
                                        data-button="Activate"
                                    >
                                        <i class="bi bi-person-check-fill"></i>
                                        Activate
                                    </button>

                                </form>

                            @endif


                            {{-- Deactivate --}}

                            @if ($status === 'active' && ! $isCurrentUser)

                                <form
                                    id="deactivate-{{ $user->id }}"
                                    method="POST"
                                    action="{{ route('users.deactivate', $user) }}"
                                >

                                    @csrf
                                    @method('PATCH')

                                    <button
                                        type="button"
                                        class="ux-act is-bad"
                                        data-confirm
                                        data-form="deactivate-{{ $user->id }}"
                                        data-tone="bad"
                                        data-title="Deactivate this account?"
                                        data-message="{{ $user->name }} will no longer be able to sign in until the account is activated again."
                                        data-button="Deactivate"
                                    >
                                        <i class="bi bi-person-x-fill"></i>
                                        Deactivate
                                    </button>

                                </form>

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>


            {{-- Shown when search / filter hides every row --}}

            <div
                class="ux-empty"
                id="uxNoMatch"
                hidden
            >

                <i class="bi bi-search"></i>

                <h3>
                    No matching accounts
                </h3>

                <p>
                    Try a different name or username,
                    or clear the filter.
                </p>

                <button
                    type="button"
                    class="ux-btn ux-btn--line"
                    id="uxClear"
                >
                    Clear search and filter
                </button>

            </div>


            <div
                class="ux-foot"
                id="uxCount"
                aria-live="polite"
            >
                Showing {{ $users->count() }}
                of {{ $users->count() }}
                {{ Str::plural('user', $users->count()) }}
            </div>


        @else

            <div class="ux-empty">

                <i class="bi bi-people"></i>

                <h3>
                    No user accounts yet
                </h3>

                <p>
                    Add a Secretary account to give someone
                    access to the system.
                </p>

                <a
                    href="{{ route('users.create') }}"
                    class="ux-btn"
                >
                    <i class="bi bi-person-plus-fill"></i>
                    Add Secretary
                </a>

            </div>

        @endif

    </section>

</div>


{{-- =============================================================
     CONFIRM DIALOG
============================================================= --}}

<dialog
    class="ux-dialog"
    id="uxConfirm"
    aria-labelledby="uxConfirmTitle"
>

    <div class="ux-dialog-body">

        <div
            class="ux-dialog-icon"
            id="uxConfirmIcon"
        >
            <i class="bi bi-question-circle-fill"></i>
        </div>

        <h3 id="uxConfirmTitle">
            Are you sure?
        </h3>

        <p id="uxConfirmMessage"></p>

    </div>


    <div class="ux-dialog-actions">

        <button
            type="button"
            class="ux-btn ux-btn--line"
            id="uxConfirmCancel"
        >
            Cancel
        </button>

        <button
            type="button"
            class="ux-btn"
            id="uxConfirmOk"
        >
            Confirm
        </button>

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

                if (window.confirm(btn.dataset.title)) {
                    form.submit();
                }

                return;
            }


            const bad = btn.dataset.tone === 'bad';

            pendingForm = form;

            dlgTitle.textContent = btn.dataset.title;
            dlgMsg.textContent = btn.dataset.message;
            dlgOk.textContent = btn.dataset.button;

            dlgOk.className =
                'ux-btn ' + (bad ? 'ux-btn--danger' : '');

            dlgIcon.style.setProperty(
                '--dlg-tone',
                bad ? 'var(--ux-coral)' : 'var(--ux-ok)'
            );

            dlgIcon.innerHTML = bad
                ? '<i class="bi bi-person-x-fill"></i>'
                : '<i class="bi bi-person-check-fill"></i>';

            dialog.showModal();

            dlgCancel.focus();

        });

    });


    if (dialog) {

        dlgCancel.addEventListener('click', () => {
            dialog.close();
        });


        dlgOk.addEventListener('click', () => {

            const form = pendingForm;

            dialog.close();

            if (form) {
                form.submit();
            }

        });


        dialog.addEventListener('click', (e) => {

            if (e.target === dialog) {
                dialog.close();
            }

        });

    }


    /* ---------------- Search + filter ---------------- */

    if (!list) return;


    const rows = Array.from(
        list.querySelectorAll('.ux-row')
    );

    const search = document.getElementById('uxSearch');

    const filters = Array.from(
        document.querySelectorAll('.ux-filter')
    );

    const noMatch = document.getElementById('uxNoMatch');

    const count = document.getElementById('uxCount');

    const clear = document.getElementById('uxClear');

    let active = 'all';


    function apply() {

        const q = (search.value || '')
            .trim()
            .toLowerCase();

        let shown = 0;


        rows.forEach((row) => {

            const matchesText =
                !q || row.dataset.search.includes(q);

            let matchesFilter = true;


            if (
                active === 'captain' ||
                active === 'secretary'
            ) {
                matchesFilter =
                    row.dataset.role === active;
            }


            if (active === 'inactive') {
                matchesFilter =
                    row.dataset.status === 'inactive';
            }


            const visible =
                matchesText && matchesFilter;

            row.hidden = !visible;


            if (visible) {
                shown++;
            }

        });


        noMatch.hidden = shown > 0;

        list.hidden = shown === 0;


        count.textContent =
            'Showing ' +
            shown +
            ' of ' +
            rows.length +
            ' ' +
            (rows.length === 1 ? 'user' : 'users');

    }


    filters.forEach((btn) => {

        btn.addEventListener('click', () => {

            active = btn.dataset.filter;

            filters.forEach((b) => {

                b.setAttribute(
                    'aria-pressed',
                    b === btn ? 'true' : 'false'
                );

            });

            apply();

        });

    });


    if (search) {
        search.addEventListener('input', apply);
    }


    if (clear) {

        clear.addEventListener('click', () => {

            if (search) {
                search.value = '';
            }

            active = 'all';


            filters.forEach((b) => {

                b.setAttribute(
                    'aria-pressed',
                    b.dataset.filter === 'all'
                        ? 'true'
                        : 'false'
                );

            });


            apply();

            if (search) {
                search.focus();
            }

        });

    }

})();
</script>

@endsection
