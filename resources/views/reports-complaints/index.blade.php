@extends('layouts.app')

@section('title', 'Reports & Complaints')

@push('styles')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

<style>
    /* =========================================================
       REPORTS & COMPLAINTS — case desk (modern redesign v2)
       ========================================================= */

    .cases {
        --ink: #16192b;
        --muted: #5b5f78;
        --faint: #8b8fa8;
        --paper: #ffffff;
        --wash: #f6f7fb;
        --wash-2: #eef0f7;
        --line: #e4e6f0;
        --line-soft: #edeef6;

        --brand: #4338ca;
        --brand-ink: #312496;
        --brand-soft: #eeecfd;

        --sun: #f0b458;
        --hover: #f8f8fd;

        --shadow: 0 1px 2px rgba(22, 25, 43, .04), 0 10px 26px -14px rgba(22, 25, 43, .12);
        --shadow-lift: 0 14px 30px -14px rgba(67, 56, 202, .22);

        --radius: 1.2rem;
        --radius-sm: .85rem;

        --community: #0f766e;
        --community-bg: #e4f5f3;

        --person: #be123c;
        --person-bg: #fde8ee;

        --s-filed: #5b5f78;
        --s-filed-bg: #eef0f7;

        --s-review: #b45309;
        --s-review-bg: #fef3e2;

        --s-captain: #a3410a;
        --s-captain-bg: #fdece0;

        --s-hearing: #6941c6;
        --s-hearing-bg: #efe9fc;

        --s-resolution: #1d63c9;
        --s-resolution-bg: #e5eefd;

        --s-resolved: #17845a;
        --s-resolved-bg: #e0f6ea;

        --s-closed: #454a62;
        --s-closed-bg: #eef0f7;

        --s-disapproved: #dc2626;
        --s-disapproved-bg: #fdecec;

        font-family: 'Inter', system-ui, sans-serif;
        color: var(--ink);
        max-width: 1500px;
        margin: 0 auto;
        padding-bottom: 2rem;
    }

    html[data-theme="dark"] .cases {
        --ink: #eef0fb;
        --muted: #a9adc9;
        --faint: #797ea0;
        --paper: #1a1c2e;
        --wash: #171928;
        --wash-2: #20233a;
        --line: #2b2e48;
        --line-soft: #262844;

        --brand: #8b81f0;
        --brand-ink: #c3bcff;
        --brand-soft: #2a2660;

        --hover: #1e2036;

        --shadow: 0 1px 2px rgba(0, 0, 0, .3), 0 12px 28px -16px rgba(0, 0, 0, .55);
        --shadow-lift: 0 16px 34px -16px rgba(139, 129, 240, .32);

        --community: #5fd6c9;
        --community-bg: #123330;

        --person: #fb7aa2;
        --person-bg: #3a1626;

        --s-filed: #a9adc9;
        --s-filed-bg: #20233a;

        --s-review: #f0b458;
        --s-review-bg: #3a2c12;

        --s-captain: #f2965a;
        --s-captain-bg: #3d2612;

        --s-hearing: #b7a0f5;
        --s-hearing-bg: #292050;

        --s-resolution: #7ab0f2;
        --s-resolution-bg: #17263f;

        --s-resolved: #6fd48a;
        --s-resolved-bg: #123222;

        --s-closed: #b0b8c5;
        --s-closed-bg: #20233a;

        --s-disapproved: #f47272;
        --s-disapproved-bg: #3a1717;
    }

    .cases * {
        box-sizing: border-box;
    }

    .cases .display {
        font-family: 'Sora', 'Inter', sans-serif;
        letter-spacing: -.015em;
    }

    .cases a {
        color: inherit;
    }

    .cases a:focus-visible,
    .cases button:focus-visible,
    .cases input:focus-visible,
    .cases select:focus-visible,
    .seg input:focus-visible + span {
        outline: 3px solid var(--sun);
        outline-offset: 2px;
    }

    /* ---------- Header ---------- */

    .head {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 1.5rem;
        padding-top: .25rem;
    }

    .head .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        color: var(--brand);
        margin-bottom: .5rem;
    }

    .head .eyebrow::before {
        content: '';
        width: .45rem;
        height: .45rem;
        border-radius: 50%;
        background: var(--brand);
    }

    .head h1 {
        font-size: clamp(1.7rem, 3.2vw, 2.35rem);
        font-weight: 800;
        margin: 0;
        line-height: 1.08;
    }

    .head p {
        margin: .5rem 0 0;
        color: var(--muted);
        font-size: .98rem;
        max-width: 46ch;
    }

    .cta {
        display: inline-flex;
        align-items: center;
        gap: .55rem;
        background: var(--brand);
        color: #fff;
        font-weight: 700;
        font-size: .93rem;
        text-decoration: none;
        padding: .8rem 1.3rem;
        border-radius: 999px;
        box-shadow: var(--shadow-lift);
        transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
    }

    .cta:hover {
        color: #fff;
        transform: translateY(-2px);
        background: var(--brand-ink);
    }

    /* ---------- Stat tiles ---------- */

    .tiles {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 1rem;
        margin-bottom: 1.25rem;
    }

    @media (max-width: 991.98px) {
        .tiles {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .tile {
        position: relative;
        background: var(--paper);
        border: 1px solid var(--line-soft);
        border-radius: var(--radius);
        padding: 1.2rem 1.3rem;
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    .tile::after {
        content: '';
        position: absolute;
        inset: 0 auto 0 0;
        width: 4px;
        background: var(--c);
    }

    .tile .icon-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 2.35rem;
        height: 2.35rem;
        border-radius: .75rem;
        background: var(--cbg, var(--wash-2));
        color: var(--c);
        font-size: 1.02rem;
        margin-bottom: .75rem;
    }

    .tile .n {
        font-family: 'Sora', sans-serif;
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
    }

    .tile .l {
        color: var(--muted);
        font-size: .84rem;
        font-weight: 600;
        margin-top: .4rem;
    }

    /* ---------- Workflow guide ---------- */

    .flow-strip {
        background: var(--paper);
        border: 1px solid var(--line-soft);
        border-radius: var(--radius);
        padding: 1rem 1.3rem;
        margin-bottom: 1.25rem;
        box-shadow: var(--shadow);
    }

    .flow-strip-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        cursor: pointer;
        user-select: none;
    }

    .flow-strip-title {
        display: flex;
        align-items: center;
        gap: .5rem;
        font-weight: 700;
        font-size: .88rem;
        color: var(--ink);
    }

    .flow-strip-title i { color: var(--brand); }

    .flow-toggle {
        display: inline-flex;
        align-items: center;
        gap: .3rem;
        font-size: .8rem;
        font-weight: 600;
        color: var(--muted);
        background: none;
        border: none;
        padding: .2rem .3rem;
    }

    .flow-toggle i {
        transition: transform .2s ease;
    }

    .flow-strip.is-open .flow-toggle i {
        transform: rotate(180deg);
    }

    .flow-track {
        display: none;
        align-items: center;
        gap: 0;
        margin-top: 1.1rem;
        overflow-x: auto;
        padding-bottom: .2rem;
    }

    .flow-strip.is-open .flow-track {
        display: flex;
    }

    .flow-node {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .45rem;
        min-width: 84px;
        flex-shrink: 0;
    }

    .flow-node .dot {
        width: .95rem;
        height: .95rem;
        border-radius: 50%;
        background: var(--fc);
        box-shadow: 0 0 0 4px var(--fcbg);
    }

    .flow-node .lbl {
        font-size: .74rem;
        font-weight: 600;
        color: var(--muted);
        text-align: center;
    }

    .flow-line {
        flex: 1 1 auto;
        height: 2px;
        background: var(--line);
        min-width: 22px;
        margin: 0 -2px;
        transform: translateY(-1.05rem);
    }

    .flow-note {
        font-size: .78rem;
        color: var(--faint);
        margin-top: .75rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    /* ---------- Toolbar ---------- */

    .toolbar {
        background: var(--paper);
        border: 1px solid var(--line-soft);
        border-radius: var(--radius);
        padding: 1.1rem;
        margin-bottom: 1rem;
        box-shadow: var(--shadow);
    }

    .tb-row {
        display: grid;
        grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr) minmax(0, 1fr) auto;
        gap: .85rem;
        align-items: end;
    }

    @media (max-width: 991.98px) {
        .tb-row {
            grid-template-columns: 1fr 1fr;
        }

        .tb-row .search-f {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 575.98px) {
        .tb-row {
            grid-template-columns: 1fr;
        }
    }

    .toolbar label.lbl {
        display: block;
        font-size: .76rem;
        font-weight: 700;
        color: var(--muted);
        margin-bottom: .4rem;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .search-f {
        position: relative;
    }

    .search-f i {
        position: absolute;
        left: 1rem;
        bottom: .95rem;
        color: var(--faint);
        pointer-events: none;
    }

    .toolbar input[type="search"],
    .toolbar select {
        width: 100%;
        min-height: 46px;
        padding: .6rem .95rem;
        border-radius: .8rem;
        font: inherit;
        font-size: .92rem;
        border: 1.5px solid var(--line);
        background: var(--wash);
        color: var(--ink);
        transition: border-color .15s, box-shadow .15s, background .15s;
    }

    .toolbar input[type="search"] {
        padding-left: 2.7rem;
    }

    .toolbar input::placeholder {
        color: var(--faint);
    }

    .toolbar input:focus,
    .toolbar select:focus {
        border-color: var(--brand);
        outline: none;
        background: var(--paper);
        box-shadow: 0 0 0 4px var(--brand-soft);
    }

    html[data-theme="dark"] .toolbar select {
        color-scheme: dark;
    }

    .reset {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        min-height: 46px;
        padding: 0 1.1rem;
        border-radius: 999px;
        border: 1.5px solid var(--line);
        color: var(--muted);
        text-decoration: none;
        font-weight: 700;
        font-size: .88rem;
        transition: .15s ease;
    }

    .reset:hover {
        color: var(--ink);
        border-color: var(--brand);
        background: var(--brand-soft);
    }

    .seg-wrap {
        display: flex;
        flex-wrap: wrap;
        gap: .85rem;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .seg {
        display: inline-flex;
        padding: .3rem;
        background: var(--wash-2);
        border-radius: 999px;
        gap: .2rem;
    }

    .seg label {
        position: relative;
        cursor: pointer;
        margin: 0;
    }

    .seg input {
        position: absolute;
        opacity: 0;
        inset: 0;
    }

    .seg span {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        padding: .52rem 1rem;
        border-radius: 999px;
        font-weight: 700;
        font-size: .86rem;
        color: var(--muted);
        transition: background .15s, color .15s, box-shadow .15s;
    }

    .seg label:hover span {
        color: var(--ink);
    }

    .seg input:checked + span {
        background: var(--paper);
        color: var(--ink);
        box-shadow: var(--shadow);
    }

    .seg .c-community input:checked + span i {
        color: var(--community);
    }

    .seg .c-person input:checked + span i {
        color: var(--person);
    }

    .result-line {
        color: var(--muted);
        font-size: .88rem;
        font-weight: 500;
    }

    .result-line b {
        color: var(--ink);
        font-weight: 800;
    }

    .chips {
        display: flex;
        flex-wrap: wrap;
        gap: .5rem;
        margin-bottom: 1rem;
    }

    .chip {
        display: inline-flex;
        align-items: center;
        gap: .5rem;
        padding: .3rem .5rem .3rem .85rem;
        border-radius: 999px;
        background: var(--brand-soft);
        color: var(--brand-ink);
        font-size: .85rem;
        font-weight: 600;
    }

    .chip a {
        display: grid;
        place-items: center;
        width: 1.3rem;
        height: 1.3rem;
        border-radius: 50%;
        color: var(--brand-ink);
        text-decoration: none;
        transition: .15s ease;
    }

    .chip a:hover {
        background: var(--brand);
        color: #fff;
    }

    /* ---------- Record cards ---------- */

    .board {
        display: flex;
        flex-direction: column;
        gap: .7rem;
        transition: opacity .15s;
    }

    .board.loading {
        opacity: .5;
        pointer-events: none;
    }

    .rec-card {
        --c: var(--brand);
        --cbg: var(--brand-soft);
        display: grid;
        grid-template-columns: minmax(0, 1.9fr) minmax(0, 1.05fr) auto;
        align-items: center;
        gap: 1.15rem;
        background: var(--paper);
        border: 1px solid var(--line-soft);
        border-radius: var(--radius-sm);
        padding: 1rem 1.2rem;
        cursor: pointer;
        box-shadow: var(--shadow);
        transition: transform .12s ease, box-shadow .12s ease, border-color .12s ease;
    }

    .rec-card:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lift);
        border-color: var(--line);
    }

    .rec-card.t-community {
        --c: var(--community);
        --cbg: var(--community-bg);
    }

    .rec-card.t-person {
        --c: var(--person);
        --cbg: var(--person-bg);
    }

    @media (max-width: 1199.98px) {
        .rec-card {
            grid-template-columns: minmax(0, 1fr) auto;
            row-gap: .8rem;
        }

        .rec-meta {
            grid-column: 1 / -1;
        }
    }

    /* main column */

    .rec-main {
        min-width: 0;
        display: flex;
        gap: 1rem;
    }

    .rec-icon {
        flex-shrink: 0;
        width: 2.55rem;
        height: 2.55rem;
        border-radius: .75rem;
        background: var(--cbg);
        color: var(--c);
        display: grid;
        place-items: center;
        font-size: 1.08rem;
    }

    .rec-text {
        min-width: 0;
    }

    .rec-top {
        display: flex;
        align-items: center;
        gap: .55rem;
        flex-wrap: wrap;
        margin-bottom: .2rem;
    }

    .code {
        font-weight: 800;
        color: var(--brand);
        text-decoration: none;
        white-space: nowrap;
        font-size: .82rem;
        letter-spacing: .01em;
    }

    .code:hover {
        text-decoration: underline;
    }

    .type-tag {
        font-size: .71rem;
        font-weight: 700;
        color: var(--c);
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .ttl {
        font-weight: 700;
        font-size: .97rem;
        line-height: 1.35;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .rec-sub {
        color: var(--muted);
        font-size: .83rem;
        margin-top: .2rem;
        display: flex;
        align-items: center;
        gap: .3rem;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .cases mark {
        background: var(--sun);
        color: #14213d;
        border-radius: 3px;
        padding: 0 2px;
    }

    /* meta column */

    .rec-meta {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: .35rem;
    }

    .rec-meta .who {
        font-size: .87rem;
        font-weight: 600;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .rec-meta .who.muted-who {
        color: var(--faint);
        font-weight: 500;
    }

    .rec-meta .who-label {
        font-size: .71rem;
        font-weight: 700;
        color: var(--faint);
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .cat {
        display: inline-block;
        align-self: flex-start;
        padding: .18rem .6rem;
        border-radius: .5rem;
        background: var(--wash-2);
        color: var(--ink);
        font-size: .77rem;
        font-weight: 600;
    }

    /* status + filed date column */

    .rec-side {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: .5rem;
        min-width: 140px;
    }

    @media (max-width: 1199.98px) {
        .rec-side {
            grid-column: 2 / 3;
            align-items: flex-end;
        }
    }

    .filed-when {
        text-align: right;
        font-size: .78rem;
        color: var(--muted);
        white-space: nowrap;
    }

    .filed-when b {
        display: block;
        color: var(--ink);
        font-weight: 600;
        font-size: .81rem;
    }

    /* status pills */

    .pill {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .32rem .72rem;
        border-radius: 999px;
        font-size: .78rem;
        font-weight: 700;
        color: var(--sc);
        background: var(--sb);
        white-space: nowrap;
    }

    .pill::before {
        content: '';
        width: .42rem;
        height: .42rem;
        border-radius: 50%;
        background: var(--sc);
    }

    .s-filed { --sc: var(--s-filed); --sb: var(--s-filed-bg); }
    .s-review { --sc: var(--s-review); --sb: var(--s-review-bg); }
    .s-captain { --sc: var(--s-captain); --sb: var(--s-captain-bg); }
    .s-hearing { --sc: var(--s-hearing); --sb: var(--s-hearing-bg); }
    .s-resolution { --sc: var(--s-resolution); --sb: var(--s-resolution-bg); }
    .s-resolved { --sc: var(--s-resolved); --sb: var(--s-resolved-bg); }
    .s-closed { --sc: var(--s-closed); --sb: var(--s-closed-bg); }
    .s-disapproved { --sc: var(--s-disapproved); --sb: var(--s-disapproved-bg); }

    /* ---------- Mini progress stepper (understand the flow at a glance) ---------- */

    .rec-progress {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        gap: 0;
        padding-top: .75rem;
        margin-top: .1rem;
        border-top: 1px dashed var(--line);
    }

    .rec-progress .step {
        width: .5rem;
        height: .5rem;
        border-radius: 50%;
        background: var(--line);
        flex-shrink: 0;
        transition: background .15s ease;
    }

    .rec-progress .step.done {
        background: var(--c);
    }

    .rec-progress .step.current {
        width: .65rem;
        height: .65rem;
        box-shadow: 0 0 0 3px var(--cbg);
    }

    .rec-progress .seg-line {
        flex: 1 1 auto;
        height: 2px;
        background: var(--line);
        margin: 0 .3rem;
    }

    .rec-progress .seg-line.done {
        background: var(--c);
    }

    .rec-progress .step-label {
        font-size: .72rem;
        font-weight: 700;
        color: var(--c);
        margin-left: .6rem;
        white-space: nowrap;
    }

    .rec-progress.is-disapproved {
        border-top-style: solid;
        border-color: var(--s-disapproved-bg);
    }

    .rec-progress .disapproved-note {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        font-size: .78rem;
        font-weight: 700;
        color: var(--s-disapproved);
    }

    /* ---------- Actions ---------- */

    .rec-actions {
        grid-column: 1 / -1;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: .35rem;
    }

    @media (max-width: 1199.98px) {
        .rec-actions {
            justify-content: stretch;
        }

        .rec-actions > * {
            flex: 1 1 auto;
        }
    }

    .view-btn,
    .resolve-btn,
    .close-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .4rem;
        text-decoration: none;
        padding: .5rem .9rem;
        border-radius: 999px;
        font-size: .82rem;
        font-weight: 700;
        white-space: nowrap;
        border: 1.5px solid transparent;
        transition: .15s ease;
    }

    .view-btn {
        border-color: var(--line);
        color: var(--ink);
        background: var(--wash);
    }

    .view-btn:hover {
        border-color: var(--brand);
        color: var(--brand);
        background: var(--brand-soft);
    }

    .resolve-btn {
        border-color: var(--s-resolved);
        background: var(--s-resolved-bg);
        color: var(--s-resolved);
    }

    .resolve-btn:hover {
        background: var(--s-resolved);
        color: #fff;
    }

    .close-btn {
        border-color: var(--s-closed);
        background: var(--s-closed-bg);
        color: var(--s-closed);
    }

    .close-btn:hover {
        background: var(--s-closed);
        color: #fff;
    }

    .resolved-label,
    .closed-label {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .45rem .8rem;
        border-radius: 999px;
        font-size: .82rem;
        font-weight: 700;
        white-space: nowrap;
    }

    .resolved-label {
        color: var(--s-resolved);
        background: var(--s-resolved-bg);
    }

    .closed-label {
        color: var(--s-closed);
        background: var(--s-closed-bg);
    }

    .completed-label {
        display: inline-flex;
        align-items: center;
        gap: .35rem;
        color: var(--muted);
        font-size: .8rem;
        font-weight: 600;
        white-space: nowrap;
        margin-right: auto;
    }

    @media (max-width: 1199.98px) {
        .completed-label {
            margin-right: 0;
            flex: 1 1 100%;
        }
    }

    /* ---------- Empty ---------- */

    .empty {
        text-align: center;
        padding: 4rem 1rem;
        background: var(--paper);
        border: 1px dashed var(--line);
        border-radius: var(--radius);
    }

    .empty .icon-badge {
        width: 3.4rem;
        height: 3.4rem;
        border-radius: 1rem;
        background: var(--wash-2);
        color: var(--faint);
        display: grid;
        place-items: center;
        font-size: 1.55rem;
        margin: 0 auto;
    }

    .empty h3 {
        font-size: 1.15rem;
        font-weight: 800;
        margin: 1rem 0 .3rem;
    }

    .empty p {
        color: var(--muted);
        margin: 0 auto 1.25rem;
        max-width: 38ch;
    }

    /* ---------- Pagination footer ---------- */

    .board-foot {
        padding: 1rem 0 0;
    }

    .board-foot .pagination {
        margin: 0;
        justify-content: center;
    }

    @media (prefers-reduced-motion: reduce) {
        .cases * {
            transition: none !important;
        }
    }
</style>

@endpush

@section('content')

@php

/*
|--------------------------------------------------------------------------
| Safe Filter Defaults
|--------------------------------------------------------------------------
|
| The controller may not explicitly pass every filter variable.
| These defaults prevent "Undefined variable" errors while still
| preserving values submitted through the request query string.
|
*/

$search = $search ?? request('search', '');
$reportType = $reportType ?? request('report_type', '');
$status = $status ?? request('status', '');
$category = $category ?? request('category', '');

$params = array_filter([
    'search'      => $search,
    'report_type' => $reportType,
    'status'      => $status,
    'category'    => $category,
], fn ($v) => filled($v));

$hasFilters = count($params) > 0;

$total = method_exists($reports, 'total')
    ? $reports->total()
    : $reports->count();

$tokens = array_values(
    array_filter(
        preg_split('/\s+/', trim((string) $search))
    )
);

/*
|--------------------------------------------------------------------------
| Safe Search Highlight
|--------------------------------------------------------------------------
*/

$hl = function ($text) use ($tokens) {

    $text = e((string) $text);

    if (! $tokens) {
        return $text;
    }

    $pattern = '/(' .
        implode(
            '|',
            array_map(
                fn ($t) => preg_quote(e($t), '/'),
                $tokens
            )
        ) .
        ')/iu';

    return preg_replace(
        $pattern,
        '<mark>$1</mark>',
        $text
    );
};

/*
|--------------------------------------------------------------------------
| Status Classes
|--------------------------------------------------------------------------
*/

$statusMap = [
    'Filed' => 's-filed',
    'Under Review' => 's-review',
    'Awaiting Captain Approval' => 's-captain',
    'Awaiting Captain Availability' => 's-captain',
    'Hearing Scheduled' => 's-hearing',
    'For Further Information' => 's-hearing',
    'Under Resolution' => 's-resolution',
    'Resolved' => 's-resolved',
    'Closed' => 's-closed',
    'Disapproved' => 's-disapproved',
];

$statuses = array_keys($statusMap);

$typeLabels = [
    'community' => 'Community reports',
    'person' => 'Person complaints',
];

/*
|--------------------------------------------------------------------------
| Workflow Status Groups
|--------------------------------------------------------------------------
|
| Only Resolved can proceed to Closed. Disapproved is a terminal
| off-ramp and is not part of the main pipeline below.
|
*/

$activeStatuses = [
    'Filed',
    'Under Review',
    'Awaiting Captain Approval',
    'Awaiting Captain Availability',
    'Hearing Scheduled',
    'For Further Information',
    'Under Resolution',
];

/*
|--------------------------------------------------------------------------
| Pipeline position (drives the mini progress stepper on each card
| and the workflow guide strip)
|--------------------------------------------------------------------------
*/

$pipelineLabels = ['Filed', 'Review', 'Captain', 'Hearing', 'Resolution', 'Resolved', 'Closed'];

$pipelineIndex = [
    'Filed' => 0,
    'Under Review' => 1,
    'Awaiting Captain Approval' => 2,
    'Awaiting Captain Availability' => 2,
    'Hearing Scheduled' => 3,
    'For Further Information' => 3,
    'Under Resolution' => 4,
    'Resolved' => 5,
    'Closed' => 6,
];

@endphp

<div class="cases">

{{-- =========================================================
     HEADER
     ========================================================== --}}

<div class="head">

    <div>

        <div class="eyebrow">
            Barangay case desk
        </div>

        <h1 class="display">
            Reports &amp; Complaints
        </h1>

        <p>
            Record, monitor, and resolve barangay concerns in one place.
        </p>

    </div>

    <a
        href="{{ route('reports-complaints.create') }}"
        class="cta"
    >
        <i class="bi bi-plus-lg"></i>
        New report or complaint
    </a>

</div>


{{-- =========================================================
     STATS
     ========================================================== --}}

<div class="tiles">

    <div class="tile" style="--c: var(--brand); --cbg: var(--brand-soft)">

        <div class="icon-badge">
            <i class="bi bi-clipboard-data"></i>
        </div>

        <div class="n">
            {{ number_format($totalReports) }}
        </div>

        <div class="l">
            Total records
        </div>

    </div>


    <div class="tile" style="--c: var(--s-review); --cbg: var(--s-review-bg)">

        <div class="icon-badge">
            <i class="bi bi-hourglass-split"></i>
        </div>

        <div class="n">
            {{ number_format($pendingReports) }}
        </div>

        <div class="l">
            Pending or in review
        </div>

    </div>


    <div class="tile" style="--c: var(--s-resolution); --cbg: var(--s-resolution-bg)">

        <div class="icon-badge">
            <i class="bi bi-arrow-repeat"></i>
        </div>

        <div class="n">
            {{ number_format($activeReports) }}
        </div>

        <div class="l">
            Active cases
        </div>

    </div>


    <div class="tile" style="--c: var(--s-resolved); --cbg: var(--s-resolved-bg)">

        <div class="icon-badge">
            <i class="bi bi-check2-circle"></i>
        </div>

        <div class="n">
            {{ number_format($resolvedReports) }}
        </div>

        <div class="l">
            Resolved
        </div>

    </div>

</div>


{{-- =========================================================
     WORKFLOW GUIDE — collapsible, explains the pipeline once
     ========================================================== --}}

<div class="flow-strip" id="flowStrip">

    <div class="flow-strip-head" id="flowStripHead">

        <span class="flow-strip-title">
            <i class="bi bi-signpost-split"></i>
            How a case moves through the workflow
        </span>

        <button type="button" class="flow-toggle" id="flowToggleBtn">
            <span id="flowToggleLabel">Show</span>
            <i class="bi bi-chevron-down"></i>
        </button>

    </div>

    <div class="flow-track">

        @php
            $pipelineStageClasses = ['s-filed', 's-review', 's-captain', 's-hearing', 's-resolution', 's-resolved', 's-closed'];
        @endphp

        @foreach ($pipelineLabels as $i => $label)

            <div class="flow-node" style="--fc: var(--{{ $pipelineStageClasses[$i] }}); --fcbg: var(--{{ $pipelineStageClasses[$i] }}-bg);">
                <div class="dot"></div>
                <div class="lbl">{{ $label }}</div>
            </div>

            @if (! $loop->last)
                <div class="flow-line"></div>
            @endif

        @endforeach

    </div>

    <div class="flow-note">
        <i class="bi bi-info-circle"></i>
        Disapproved cases exit the workflow early and are marked separately on each card.
    </div>

</div>


{{-- =========================================================
     FILTERS
     ========================================================== --}}

<form
    method="GET"
    action="{{ route('reports-complaints.index') }}"
    id="filterForm"
    role="search"
>

    <div class="seg-wrap">

        <div
            class="seg"
            role="radiogroup"
            aria-label="Report type"
        >

            <label>

                <input
                    type="radio"
                    name="report_type"
                    value=""
                    {{ $reportType ? '' : 'checked' }}
                >

                <span>
                    All
                </span>

            </label>


            <label class="c-community">

                <input
                    type="radio"
                    name="report_type"
                    value="community"
                    {{ $reportType === 'community' ? 'checked' : '' }}
                >

                <span>
                    <i class="bi bi-building"></i>
                    Community reports
                </span>

            </label>


            <label class="c-person">

                <input
                    type="radio"
                    name="report_type"
                    value="person"
                    {{ $reportType === 'person' ? 'checked' : '' }}
                >

                <span>
                    <i class="bi bi-person-exclamation"></i>
                    Person complaints
                </span>

            </label>

        </div>


        <div class="result-line">

            <b>
                {{ number_format($total) }}
            </b>

            {{ \Illuminate\Support\Str::plural('record', $total) }}

            {{ $hasFilters ? ' match' : '' }}

        </div>

    </div>


    <div class="toolbar">

        <div class="tb-row">

            <div class="search-f">

                <label
                    class="lbl"
                    for="search"
                >
                    Search
                </label>

                <i class="bi bi-search"></i>

                <input
                    type="search"
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    autocomplete="off"
                    placeholder="Code, title, resident, or location"
                >

            </div>


            <div>

                <label
                    class="lbl"
                    for="status"
                >
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                >

                    <option value="">
                        All statuses
                    </option>

                    @foreach ($statuses as $s)

                        <option
                            value="{{ $s }}"
                            @selected($status === $s)
                        >
                            {{ $s }}
                        </option>

                    @endforeach

                </select>

            </div>


            <div>

                <label
                    class="lbl"
                    for="category"
                >
                    Category
                </label>

                <select
                    id="category"
                    name="category"
                >

                    <option value="">
                        All categories
                    </option>

                    @foreach ($categories as $item)

                        <option
                            value="{{ $item }}"
                            @selected($category === $item)
                        >
                            {{ $item }}
                        </option>

                    @endforeach

                </select>

            </div>


            <a
                href="{{ route('reports-complaints.index') }}"
                class="reset"
                title="Clear all filters"
            >

                <i class="bi bi-arrow-counterclockwise"></i>

                Reset

            </a>

        </div>

    </div>

</form>


{{-- =========================================================
     ACTIVE FILTER CHIPS
     ========================================================== --}}

@if ($hasFilters)

    <div
        class="chips"
        aria-label="Active filters"
    >

        @foreach ($params as $key => $value)

            <span class="chip">

                @switch($key)

                    @case('search')

                        Search: “{{ $value }}”

                        @break

                    @case('report_type')

                        {{ $typeLabels[$value] ?? $value }}

                        @break

                    @case('status')

                        Status: {{ $value }}

                        @break

                    @case('category')

                        Category: {{ $value }}

                        @break

                @endswitch


                <a
                    href="{{ route(
                        'reports-complaints.index',
                        \Illuminate\Support\Arr::except($params, $key)
                    ) }}"
                    aria-label="Remove this filter"
                >

                    <i class="bi bi-x-lg"></i>

                </a>

            </span>

        @endforeach

    </div>

@endif


{{-- =========================================================
     RECORDS
     ========================================================== --}}

<div
    class="board"
    id="board"
>

    @if ($reports->count() > 0)

        @foreach ($reports as $report)

            @php

                $url = route(
                    'reports-complaints.show',
                    $report
                );

                $isCommunity =
                    $report->report_type === 'community';

                $statusClass =
                    $statusMap[$report->status]
                    ?? 's-filed';

                $complainant =
                    $report->complainant
                    ? trim(
                        $report->complainant->first_name .
                        ' ' .
                        $report->complainant->last_name
                    )
                    : null;

                $subject =
                    $report->subjectResident
                    ? trim(
                        $report->subjectResident->first_name .
                        ' ' .
                        $report->subjectResident->last_name
                    )
                    : null;

                $isActive =
                    in_array(
                        $report->status,
                        $activeStatuses,
                        true
                    );

                $isResolved =
                    $report->status === 'Resolved';

                $isClosed =
                    $report->status === 'Closed';

                $isDisapproved =
                    $report->status === 'Disapproved';

                $step = $pipelineIndex[$report->status] ?? null;

            @endphp


            <div
                class="rec-card {{ $isCommunity ? 't-community' : 't-person' }}"
                data-href="{{ $url }}"
            >


                {{-- =================================================
                     MAIN: icon, code, title, location/subject
                     ================================================== --}}

                <div class="rec-main">

                    <div class="rec-icon">
                        <i class="bi {{ $isCommunity ? 'bi-building' : 'bi-person-exclamation' }}"></i>
                    </div>

                    <div class="rec-text">

                        <div class="rec-top">

                            <a
                                href="{{ $url }}"
                                class="code"
                            >
                                {!! $hl($report->record_code) !!}
                            </a>

                            <span class="type-tag">
                                {{ $isCommunity ? 'Community report' : 'Person complaint' }}
                            </span>

                        </div>

                        <div
                            class="ttl"
                            title="{{ $report->title }}"
                        >
                            {!! $hl(
                                \Illuminate\Support\Str::limit(
                                    $report->title,
                                    70
                                )
                            ) !!}
                        </div>


                        @if (! $isCommunity && $subject)

                            <div class="rec-sub">
                                Against: {!! $hl($subject) !!}
                            </div>

                        @elseif ($report->location)

                            <div class="rec-sub">
                                <i class="bi bi-geo-alt"></i>
                                {!! $hl(
                                    \Illuminate\Support\Str::limit(
                                        $report->location,
                                        50
                                    )
                                ) !!}
                            </div>

                        @endif

                    </div>

                </div>


                {{-- =================================================
                     META: complainant + category
                     ================================================== --}}

                <div class="rec-meta">

                    <span class="who-label">
                        Complainant
                    </span>

                    @if ($complainant)

                        <span class="who">
                            {!! $hl($complainant) !!}
                        </span>

                    @else

                        <span class="who muted-who">
                            N/A
                        </span>

                    @endif

                    <span class="cat">
                        {{ $report->category }}
                    </span>

                </div>


                {{-- =================================================
                     SIDE: status + filed date
                     ================================================== --}}

                <div class="rec-side">

                    <span class="pill {{ $statusClass }}">
                        {{ $report->status }}
                    </span>

                    <div
                        class="filed-when"
                        title="{{ $report->created_at->format('M d, Y h:i A') }}"
                    >
                        <b>{{ $report->created_at->diffForHumans() }}</b>
                        {{ $report->created_at->format('M d, Y') }}
                    </div>

                </div>


                {{-- =================================================
                     MINI PROGRESS — where this case sits in the flow
                     ================================================== --}}

                @if ($isDisapproved)

                    <div class="rec-progress is-disapproved">
                        <span class="disapproved-note">
                            <i class="bi bi-x-octagon-fill"></i>
                            Disapproved — did not proceed through the standard workflow
                        </span>
                    </div>

                @elseif ($step !== null)

                    <div class="rec-progress">

                        @for ($i = 0; $i < count($pipelineLabels); $i++)

                            <div class="step {{ $i <= $step ? 'done' : '' }} {{ $i === $step ? 'current' : '' }}"
                                 title="{{ $pipelineLabels[$i] }}"></div>

                            @if ($i < count($pipelineLabels) - 1)
                                <div class="seg-line {{ $i < $step ? 'done' : '' }}"></div>
                            @endif

                        @endfor

                        <span class="step-label">
                            {{ $pipelineLabels[$step] }}
                        </span>

                    </div>

                @endif


                {{-- =================================================
                     ACTIONS
                     ================================================== --}}

                <div
                    class="rec-actions"
                    onclick="event.stopPropagation();"
                >


                    {{-- =========================================
                         ACTIVE CASE — Active → Resolved
                         ========================================= --}}

                    @if ($isActive)

                        <form
                            method="POST"
                            action="{{ route(
                                'reports-complaints.resolve',
                                $report
                            ) }}"
                            class="d-inline"
                        >

                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="resolve-btn"
                                title="Mark this case as Resolved"
                                onclick="return confirm('Mark {{ $report->record_code }} as Resolved?');"
                            >

                                <i class="bi bi-check2-circle"></i>

                                Resolve Case

                            </button>

                        </form>


                    {{-- =========================================
                         RESOLVED CASE — Resolved → Closed
                         ========================================= --}}

                    @elseif ($isResolved)

                        <span class="resolved-label">

                            <i class="bi bi-check2-circle"></i>

                            Resolved

                        </span>


                        <form
                            method="POST"
                            action="{{ route(
                                'reports-complaints.close',
                                $report
                            ) }}"
                            class="d-inline"
                        >

                            @csrf
                            @method('PATCH')

                            <button
                                type="submit"
                                class="close-btn"
                                title="Close this resolved case"
                                onclick="return confirm('Close {{ $report->record_code }}? This will mark the case as completed.');"
                            >

                                <i class="bi bi-lock-fill"></i>

                                Close Case

                            </button>

                        </form>


                    {{-- =========================================
                         CLOSED CASE — view only
                         ========================================= --}}

                    @elseif ($isClosed)

                        <span class="completed-label">

                            <i class="bi bi-check-circle"></i>

                            Case completed

                        </span>

                        <span class="closed-label">

                            <i class="bi bi-lock-fill"></i>

                            Closed

                        </span>


                    {{-- =========================================
                         OTHER STATUS (e.g. Disapproved) — view only
                         ========================================= --}}

                    @else

                        <a
                            href="{{ $url }}"
                            class="view-btn"
                        >

                            <i class="bi bi-eye"></i>

                            View

                        </a>

                    @endif


                    {{-- =========================================
                         Always available: View
                         ========================================= --}}

                    @if ($isActive || $isResolved || $isClosed)

                        <a
                            href="{{ $url }}"
                            class="view-btn"
                        >

                            <i class="bi bi-eye"></i>

                            View

                        </a>

                    @endif

                </div>

            </div>

        @endforeach


        @if (
            method_exists($reports, 'hasPages')
            && $reports->hasPages()
        )

            <div class="board-foot">

                {{ $reports->withQueryString()->links() }}

            </div>

        @endif


    @else

        <div class="empty">

            <div class="icon-badge">
                <i class="bi bi-clipboard-x"></i>
            </div>


            @if ($hasFilters)

                <h3 class="display">
                    Nothing matches these filters
                </h3>

                <p>
                    Try fewer words, check the spelling,
                    or remove a filter above.
                </p>

                <a
                    href="{{ route('reports-complaints.index') }}"
                    class="view-btn"
                >
                    Reset filters
                </a>

            @else

                <h3 class="display">
                    No reports or complaints yet
                </h3>

                <p>
                    Recorded barangay concerns will appear here.
                </p>

                <a
                    href="{{ route('reports-complaints.create') }}"
                    class="cta"
                >

                    <i class="bi bi-plus-lg"></i>

                    New report or complaint

                </a>

            @endif

        </div>

    @endif

</div>

</div>

@endsection

@push('scripts')

<script>
(function () {

    const form = document.getElementById('filterForm');
    const board = document.getElementById('board');
    const box = document.getElementById('search');

    if (!form) {
        return;
    }


    function apply(refocus) {

        try {

            if (refocus) {
                sessionStorage.setItem(
                    'rc_refocus',
                    '1'
                );
            }

        } catch (e) {}


        if (board) {
            board.classList.add('loading');
        }

        form.submit();

    }


    /*
    |--------------------------------------------------------------------------
    | Selects and report type
    |--------------------------------------------------------------------------
    */

    form.querySelectorAll(
        'select, input[type="radio"]'
    ).forEach(function (el) {

        el.addEventListener(
            'change',
            function () {
                apply(false);
            }
        );

    });


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    if (box) {

        let timer;

        box.addEventListener(
            'input',
            function () {

                clearTimeout(timer);

                timer = setTimeout(
                    function () {
                        apply(true);
                    },
                    400
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Restore search focus
    |--------------------------------------------------------------------------
    */

    try {

        if (
            box &&
            sessionStorage.getItem('rc_refocus')
        ) {

            sessionStorage.removeItem(
                'rc_refocus'
            );

            box.focus();

            box.setSelectionRange(
                box.value.length,
                box.value.length
            );

        }

    } catch (e) {}


    /*
    |--------------------------------------------------------------------------
    | Whole card is clickable
    |--------------------------------------------------------------------------
    |
    | Real links, forms, and buttons are excluded so that Resolve,
    | Close Case, and View continue to work normally.
    |
    */

    document
        .querySelectorAll('.rec-card')
        .forEach(function (card) {

            card.addEventListener(
                'click',
                function (e) {

                    if (
                        e.target.closest(
                            'a, button, form'
                        )
                    ) {
                        return;
                    }

                    window.location =
                        card.dataset.href;

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | Workflow guide — collapsible, remembers state per browser
    |--------------------------------------------------------------------------
    */

    const flowStrip = document.getElementById('flowStrip');
    const flowHead = document.getElementById('flowStripHead');
    const flowLabel = document.getElementById('flowToggleLabel');

    if (flowStrip && flowHead) {

        let open = false;

        try {
            open = sessionStorage.getItem('rc_flow_open') === '1';
        } catch (e) {}

        function setOpen(state) {

            open = state;
            flowStrip.classList.toggle('is-open', open);
            flowLabel.textContent = open ? 'Hide' : 'Show';

            try {
                sessionStorage.setItem('rc_flow_open', open ? '1' : '0');
            } catch (e) {}
        }

        flowHead.addEventListener('click', function () {
            setOpen(!open);
        });

        setOpen(open);
    }

})();
</script>

@endpush