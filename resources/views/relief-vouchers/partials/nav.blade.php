
{{-- resources/views/relief-vouchers/partials/nav.blade.php
     Usage: @include('relief-vouchers.partials.nav', ['active' => 'overview'|'masterlist'|'scan'|'voucher'])
     Provides: theme-aware design tokens, base components, section navigation, and copy buttons.
     Theme: Follows the global html[data-theme="dark"] setting from layouts.app. --}}

@php $active = $active ?? ''; @endphp

@once
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&family=Unbounded:wght@600;700;800&display=swap" rel="stylesheet">

<style>
    /* =====================================================
       LIGHT THEME — DEFAULT
    ===================================================== */
    .rv-page {
        --rv-page-bg: #f1f3f5;
        --rv-ink: #26352d;
        --rv-ink2: #69776e;
        --rv-line: #dce3de;
        --rv-wash: #f3f6f4;
        --rv-card: #ffffff;
        --rv-hover: #f0f5f1;

        --rv-btn: #35ad68;
        --rv-btn-hover: #279657;

        --rv-ube: #d94e79;
        --rv-ube-soft: rgba(217, 78, 121, .10);

        --rv-mango: #e5a900;
        --rv-mango-soft: rgba(229, 169, 0, .12);
        --rv-mango-ink: #946900;

        --rv-green: #23985a;
        --rv-green-soft: rgba(35, 152, 90, .10);

        --rv-red: #d65b43;
        --rv-red-soft: rgba(214, 91, 67, .10);

        --rv-grey: #77847b;
        --rv-grey-soft: #edf0ee;

        --rv-shadow: 0 8px 24px rgba(30, 50, 38, .06);

        max-width: none;
        margin: 0 auto;
        padding: 0;
        color: var(--rv-ink);
        color-scheme: light;
        font-family: 'Figtree', system-ui, -apple-system, 'Segoe UI', sans-serif;
        background: var(--rv-page-bg);
        border-radius: 18px;
    }

    /* =====================================================
       DARK THEME — FOLLOWS GLOBAL APPLICATION SETTING
    ===================================================== */
    html[data-theme="dark"] .rv-page {
        --rv-page-bg: #17231c;
        --rv-ink: #e6eee9;
        --rv-ink2: #a9b8af;
        --rv-line: #2a372f;
        --rv-wash: #1d2822;
        --rv-card: #202d25;
        --rv-hover: #29392f;

        --rv-btn: #5fd07f;
        --rv-btn-hover: #80e49a;

        --rv-ube: #ff8fb0;
        --rv-ube-soft: rgba(255, 143, 176, .14);

        --rv-mango: #f0b400;
        --rv-mango-soft: rgba(240, 180, 0, .14);
        --rv-mango-ink: #ffd166;

        --rv-green: #5fd07f;
        --rv-green-soft: rgba(95, 208, 127, .14);

        --rv-red: #ff9b73;
        --rv-red-soft: rgba(255, 155, 115, .14);

        --rv-grey: #b3c0b7;
        --rv-grey-soft: #29352e;

        --rv-shadow: none;

        color-scheme: dark;
    }

    /* ================= BASE ================= */
    .rv-page *,
    .rv-page *::before,
    .rv-page *::after {
        box-sizing: border-box;
    }

    .rv-page h1,
    .rv-page h2,
    .rv-page h3,
    .rv-page p,
    .rv-page ol,
    .rv-page ul,
    .rv-page dl,
    .rv-page dd {
        margin: 0;
        padding: 0;
    }

    .rv-page ol,
    .rv-page ul {
        list-style: none;
    }

    .rv-page a:focus-visible,
    .rv-page button:focus-visible,
    .rv-page summary:focus-visible,
    .rv-page input:focus-visible,
    .rv-page select:focus-visible,
    .rv-page textarea:focus-visible {
        outline: 3px solid var(--rv-mango);
        outline-offset: 2px;
    }

    .rv-display {
        font-family: 'Unbounded', 'Figtree', system-ui, sans-serif;
        letter-spacing: -.02em;
    }

    .rv-muted {
        color: var(--rv-ink2);
        font-size: 12.5px;
    }

    .rv-strong {
        font-weight: 700;
    }

    .rv-page [hidden] {
        display: none !important;
    }

    /* ================= SECTION NAV ================= */
    .rvn {
        position: sticky;
        top: 0;
        z-index: 20;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        margin: 0 0 22px;
        padding: 8px;
        background: color-mix(in srgb, var(--rv-card) 92%, transparent);
        backdrop-filter: blur(10px);
        border: 1px solid var(--rv-line);
        border-radius: 999px;
        box-shadow: var(--rv-shadow);
    }

    .rvn-links,
    .rvn-right {
        display: flex;
        align-items: center;
        gap: 4px;
        flex-wrap: wrap;
    }

    .rvn-links a {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 16px;
        border-radius: 999px;
        color: var(--rv-ink2);
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        transition: background .15s, color .15s;
    }

    .rvn-links a:hover {
        background: var(--rv-wash);
        color: var(--rv-ink);
    }

    .rvn-links a.is-on {
        background: var(--rv-green-soft);
        color: var(--rv-green);
    }

    /* ================= BUTTONS ================= */
    .rv-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px 20px;
        border: 0;
        border-radius: 999px;
        background: var(--rv-btn);
        color: #ffffff;
        font: 700 14px 'Figtree', sans-serif;
        text-decoration: none;
        cursor: pointer;
        transition: background .15s, transform .15s;
    }

    .rv-btn:hover {
        background: var(--rv-btn-hover);
        color: #ffffff;
    }

    .rv-btn:active {
        transform: scale(.98);
    }

    .rv-btn-sm {
        padding: 9px 16px;
        font-size: 13px;
    }

    .rv-btn-ghost {
        background: var(--rv-card);
        color: var(--rv-ink);
        border: 1px solid var(--rv-line);
    }

    .rv-btn-ghost:hover {
        background: var(--rv-wash);
        color: var(--rv-ink);
    }

    .rv-btn-warn {
        background: var(--rv-mango);
        color: #2a1d00;
    }

    .rv-btn-warn:hover {
        background: var(--rv-mango);
        filter: brightness(.94);
        color: #2a1d00;
    }

    .rv-btn-danger {
        background: transparent;
        color: var(--rv-red);
        border: 1px solid var(--rv-red);
    }

    .rv-btn-danger:hover {
        background: var(--rv-red-soft);
        color: var(--rv-red);
    }

    .rv-btn-block {
        width: 100%;
    }

    /* ================= PANELS / TABLES ================= */
    .rv-panel {
        border: 1px solid var(--rv-line);
        border-radius: 18px;
        background: var(--rv-card);
        color: var(--rv-ink);
        box-shadow: var(--rv-shadow);
        overflow: hidden;
    }

    .rv-pad {
        padding: 22px;
    }

    .rv-head {
        margin-bottom: 14px;
    }

    .rv-head h2 {
        font-size: 18px;
        font-weight: 700;
        color: var(--rv-ink);
    }

    .rv-head p {
        margin-top: 5px;
        font-size: 13.5px;
        color: var(--rv-ink2);
        line-height: 1.55;
    }

    .rv-scroll {
        overflow-x: auto;
    }

    .rv-table {
        width: 100%;
        min-width: 720px;
        border-collapse: collapse;
    }

    .rv-table th {
        padding: 12px 20px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--rv-ink2);
        text-align: left;
        background: var(--rv-wash);
        border-bottom: 1px solid var(--rv-line);
        white-space: nowrap;
    }

    .rv-table td {
        padding: 15px 20px;
        border-bottom: 1px solid var(--rv-line);
        font-size: 13.5px;
        vertical-align: middle;
        color: var(--rv-ink);
    }

    .rv-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .rv-table tbody tr:hover td {
        background: var(--rv-hover);
    }

    .rv-table .r {
        text-align: right;
    }

    a.rv-link {
        color: var(--rv-ink);
        font-weight: 700;
        text-decoration: none;
    }

    a.rv-link:hover {
        color: var(--rv-ube);
        text-decoration: underline;
        text-underline-offset: 3px;
    }

    /* ================= BADGES ================= */
    .rv-badge {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 4px 11px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .rv-badge::before {
        content: "";
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
    }

    .rv-badge.is-grey {
        background: var(--rv-grey-soft);
        color: var(--rv-grey);
    }

    .rv-badge.is-ube {
        background: var(--rv-ube-soft);
        color: var(--rv-ube);
    }

    .rv-badge.is-mango {
        background: var(--rv-mango-soft);
        color: var(--rv-mango-ink);
    }

    .rv-badge.is-green {
        background: var(--rv-green-soft);
        color: var(--rv-green);
    }

    .rv-badge.is-red {
        background: var(--rv-red-soft);
        color: var(--rv-red);
    }

    /* ================= INPUTS ================= */
    .rv-page input,
    .rv-page select,
    .rv-page textarea {
        color: var(--rv-ink);
        border-color: var(--rv-line);
    }

    .rv-page input::placeholder,
    .rv-page textarea::placeholder {
        color: var(--rv-ink2);
        opacity: .8;
    }

    .rv-search {
        position: relative;
        flex: 1 1 220px;
        min-width: 200px;
    }

    .rv-search i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--rv-grey);
    }

    .rv-search input {
        width: 100%;
        padding: 10px 14px 10px 38px;
        border-radius: 999px;
        border: 1px solid var(--rv-line);
        background: var(--rv-card);
        color: var(--rv-ink);
        font: 500 13.5px 'Figtree', sans-serif;
    }

    .rv-chips {
        display: flex;
        gap: 6px;
        flex-wrap: wrap;
    }

    .rv-chip {
        padding: 8px 14px;
        border-radius: 999px;
        border: 1px solid var(--rv-line);
        background: var(--rv-card);
        color: var(--rv-ink2);
        font: 600 12.5px 'Figtree', sans-serif;
        cursor: pointer;
        transition: all .15s;
    }

    .rv-chip:hover {
        border-color: var(--rv-ube);
        color: var(--rv-ink);
    }

    .rv-chip.is-on {
        background: var(--rv-ink);
        border-color: var(--rv-ink);
        color: var(--rv-card);
    }

    .rv-copy {
        border: 0;
        background: transparent;
        color: var(--rv-grey);
        padding: 2px 6px;
        border-radius: 6px;
        cursor: pointer;
    }

    .rv-copy:hover {
        color: var(--rv-ube);
        background: var(--rv-ube-soft);
    }

    @media (max-width: 640px) {
        .rvn {
            border-radius: 22px;
        }

        .rvn-links a span {
            display: none;
        }

        .rvn-links a {
            padding: 10px 14px;
        }

        .rv-pad {
            padding: 16px;
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .rv-page *,
        .rv-page *::before,
        .rv-page *::after {
            transition: none !important;
            animation: none !important;
        }
    }
</style>
@endonce

<nav class="rvn" aria-label="Relief assistance sections">
    <div class="rvn-links">
        <a href="{{ route('relief-vouchers.index') }}"
           class="{{ $active === 'overview' ? 'is-on' : '' }}"
           @if($active === 'overview') aria-current="page" @endif>
            <i class="bi bi-grid-1x2"></i>
            <span>Overview</span>
        </a>

        {{-- <a href="{{ route('relief-assistance.masterlist') }}" class="{{ $active === 'masterlist' ? 'is-on' : '' }}">
            <i class="bi bi-list-check"></i><span>Masterlist</span>
        </a> --}}

        <a href="{{ route('relief-vouchers.qr-verification') }}"
           class="{{ $active === 'scan' ? 'is-on' : '' }}"
           @if($active === 'scan') aria-current="page" @endif>
            <i class="bi bi-qr-code-scan"></i>
            <span>Scan QR</span>
        </a>
    </div>

    <div class="rvn-right">
        <a href="{{ route('relief-assistance.create') }}" class="rv-btn rv-btn-sm">
            <i class="bi bi-plus-lg"></i>
            New operation
        </a>
    </div>
</nav>

@once
<script>
(function () {
    if (window.__rvCopyHandlerInstalled) {
        return;
    }

    window.__rvCopyHandlerInstalled = true;

    document.addEventListener('click', function (e) {
        var c = e.target.closest('[data-copy]');

        if (!c) {
            return;
        }

        var text = c.getAttribute('data-copy');
        var icon = c.querySelector('i');
        var old = icon ? icon.className : '';

        var done = function () {
            if (icon) {
                icon.className = 'bi bi-check2';

                setTimeout(function () {
                    icon.className = old;
                }, 1400);
            }
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(done).catch(function () {});
        } else {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';

            document.body.appendChild(ta);
            ta.select();

            try {
                if (document.execCommand('copy')) {
                    done();
                }
            } catch (x) {
                // Copy fallback is unavailable.
            }

            ta.remove();
        }
    });
})();
</script>
@endonce
