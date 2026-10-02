@extends('layouts.app')

@section('title', 'Reports & Analytics | e-Poblacion')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">

<style>
    /* ============================================================
       REPORTS & ANALYTICS — DOCUMENT PROCESSING ("Pipeline" layout)
       Supports data-theme: light / dark / system
       ============================================================ */

    .dx {
        --dx-display: 'Bricolage Grotesque', 'Segoe UI', system-ui, sans-serif;
        --dx-body: 'Figtree', 'Segoe UI', system-ui, sans-serif;

        --dx-surface: #ffffff;
        --dx-sunken: #f4f7f6;
        --dx-line: #e0e7e5;
        --dx-line-strong: #c8d3d0;
        --dx-ink: #0e2a33;
        --dx-text: #2b4149;
        --dx-muted: #66808a;

        --dx-teal: #0f766e;
        --dx-teal-soft: #dff3ef;
        --dx-sun: #b7790a;
        --dx-sun-bar: #e9a80c;
        --dx-sun-soft: #fbf0d0;
        --dx-coral: #d4492c;
        --dx-coral-bar: #e0573a;
        --dx-coral-soft: #fbe6e0;
        --dx-indigo: #4f5fd1;
        --dx-indigo-soft: #e6e9fb;
        --dx-ok: #178a55;
        --dx-ok-soft: #e0f4ea;

        --dx-hero: #0e2a33;
        --dx-hero-2: #143c47;
        --dx-hero-text: #eaf5f3;
        --dx-hero-muted: #9dbcc0;
        --dx-focus: rgba(15, 118, 110, .3);
        --dx-shadow: 0 1px 2px rgba(14, 42, 51, .05), 0 8px 24px rgba(14, 42, 51, .06);

        font-family: var(--dx-body);
        color: var(--dx-text);
    }

    [data-theme="dark"] .dx {
        --dx-surface: #14262d;
        --dx-sunken: #0f1f25;
        --dx-line: #25404a;
        --dx-line-strong: #33525d;
        --dx-ink: #eaf5f3;
        --dx-text: #c9dbde;
        --dx-muted: #8aa5ac;
        --dx-teal: #3dd0c0;
        --dx-teal-soft: #123a38;
        --dx-sun: #f3c14a;
        --dx-sun-bar: #f3c14a;
        --dx-sun-soft: #3a3016;
        --dx-coral: #ff8a6b;
        --dx-coral-bar: #ff8a6b;
        --dx-coral-soft: #3d2019;
        --dx-indigo: #8f9bff;
        --dx-indigo-soft: #1f2447;
        --dx-ok: #56d99b;
        --dx-ok-soft: #123626;
        --dx-hero: #0b1a20;
        --dx-hero-2: #12303a;
        --dx-focus: rgba(61, 208, 192, .35);
        --dx-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 8px 24px rgba(0, 0, 0, .3);
    }

    @media (prefers-color-scheme: dark) {
        [data-theme="system"] .dx {
            --dx-surface: #14262d;
            --dx-sunken: #0f1f25;
            --dx-line: #25404a;
            --dx-line-strong: #33525d;
            --dx-ink: #eaf5f3;
            --dx-text: #c9dbde;
            --dx-muted: #8aa5ac;
            --dx-teal: #3dd0c0;
            --dx-teal-soft: #123a38;
            --dx-sun: #f3c14a;
            --dx-sun-bar: #f3c14a;
            --dx-sun-soft: #3a3016;
            --dx-coral: #ff8a6b;
            --dx-coral-bar: #ff8a6b;
            --dx-coral-soft: #3d2019;
            --dx-indigo: #8f9bff;
            --dx-indigo-soft: #1f2447;
            --dx-ok: #56d99b;
            --dx-ok-soft: #123626;
            --dx-hero: #0b1a20;
            --dx-hero-2: #12303a;
            --dx-focus: rgba(61, 208, 192, .35);
            --dx-shadow: 0 1px 2px rgba(0, 0, 0, .4), 0 8px 24px rgba(0, 0, 0, .3);
        }
    }

    .dx *, .dx *::before, .dx *::after { box-sizing: border-box; }
    .dx a { text-decoration: none; }
    .dx :focus-visible { outline: 3px solid var(--dx-focus); outline-offset: 2px; border-radius: 8px; }
    .dx-main { display: grid; gap: 44px; }

    /* ---------- Header ---------- */
    .dx-head {
        display: flex; flex-wrap: wrap; gap: 18px 24px;
        align-items: flex-end; justify-content: space-between; margin-bottom: 26px;
    }
    .dx-title {
        margin: 0; font-family: var(--dx-display); font-weight: 800;
        font-size: clamp(1.8rem, 3.4vw, 2.6rem); letter-spacing: -.02em; line-height: 1.05; color: var(--dx-ink);
    }
    .dx-sub { margin: 8px 0 0; color: var(--dx-muted); max-width: 56ch; }
    .dx-head-side { display: flex; flex-wrap: wrap; gap: 10px; }

    .dx-btn {
        display: inline-flex; align-items: center; gap: 8px; min-height: 44px; padding: 0 18px;
        border-radius: 12px; font: 600 .92rem var(--dx-body); cursor: pointer;
        background: var(--dx-ink); color: var(--dx-surface); border: 1px solid var(--dx-ink);
        transition: filter .15s, transform .1s, background .15s;
    }
    .dx-btn:hover { filter: brightness(1.15); color: var(--dx-surface); }
    .dx-btn:active { transform: translateY(1px); }
    .dx-btn--line { background: transparent; color: var(--dx-ink); border-color: var(--dx-line-strong); }
    .dx-btn--line:hover { background: var(--dx-sunken); color: var(--dx-ink); filter: none; }

    /* ---------- Chapter headings ---------- */
    .dx-chapter-head { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; flex-wrap: wrap; margin-bottom: 18px; }
    .dx-h2 {
        display: flex; align-items: center; gap: 12px; margin: 0;
        font-family: var(--dx-display); font-weight: 800; font-size: 1.5rem; letter-spacing: -.015em; color: var(--dx-ink);
    }
    .dx-h2 i {
        display: grid; place-items: center; width: 36px; height: 36px; border-radius: 11px;
        background: var(--dx-teal-soft); color: var(--dx-teal); font-size: 1.05rem;
    }
    .dx-lede { margin: 0; color: var(--dx-muted); font-size: .92rem; max-width: 54ch; }

    /* ---------- Hero ---------- */
    .dx-hero {
        position: relative; overflow: hidden; border-radius: 26px; padding: 32px; color: var(--dx-hero-text);
        background: radial-gradient(120% 140% at 0% 0%, var(--dx-hero-2) 0%, transparent 60%), var(--dx-hero);
        border: 1px solid color-mix(in srgb, var(--dx-hero-text) 10%, transparent);
        box-shadow: var(--dx-shadow);
    }
    .dx-hero-grid { display: grid; grid-template-columns: 220px minmax(0, 1fr); gap: 36px; align-items: center; }

    .dx-ring { position: relative; width: 200px; height: 200px; margin: 0 auto; }
    .dx-ring svg { width: 100%; height: 100%; transform: rotate(-90deg); }
    .dx-ring circle { fill: none; stroke-width: 7; stroke-linecap: round; }
    .dx-ring .track { stroke: rgba(255,255,255,.14); }
    .dx-ring .fill { stroke: #5fd3c6; stroke-dasharray: var(--p) 100; animation: dx-fill 1.2s cubic-bezier(.2,.7,.2,1) both; }
    @keyframes dx-fill { from { stroke-dasharray: 0 100; } }
    .dx-ring-body { position: absolute; inset: 0; display: grid; place-content: center; text-align: center; }
    .dx-ring-num {
        font-family: var(--dx-display); font-weight: 800; font-size: 3rem; letter-spacing: -.03em;
        line-height: 1; color: #fff; font-variant-numeric: tabular-nums;
    }
    .dx-ring-cap { margin-top: 6px; font-size: .84rem; color: var(--dx-hero-muted); }

    .dx-hero-kicker { font-size: .92rem; color: var(--dx-hero-muted); font-weight: 500; }
    .dx-hero-title {
        margin: 6px 0 10px; font-family: var(--dx-display); font-weight: 800;
        font-size: clamp(1.5rem, 3vw, 2.15rem); letter-spacing: -.02em; line-height: 1.1; color: #fff;
    }
    .dx-hero-note { margin: 0; color: var(--dx-hero-muted); max-width: 52ch; line-height: 1.55; }
    .dx-chips { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
    .dx-chip {
        display: inline-flex; align-items: center; gap: 9px; padding: 9px 14px; border-radius: 14px;
        background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
        font-size: .88rem; color: var(--dx-hero-muted);
    }
    .dx-chip b { color: #fff; font-weight: 700; font-variant-numeric: tabular-nums; }
    .dx-chip i { color: #5fd3c6; }

    /* ---------- Pipeline (a real sequence -> numbered) ---------- */
    .dx-pipe { display: grid; grid-template-columns: repeat(5, minmax(0, 1fr)); gap: 14px; margin: 0; padding: 0; list-style: none; }
    .dx-stage {
        --tone: var(--dx-teal); --tone-soft: var(--dx-teal-soft); --tone-bar: var(--dx-teal);
        position: relative; display: flex; flex-direction: column; gap: 10px; padding: 20px 18px 18px;
        background: var(--dx-surface); border: 1px solid var(--dx-line); border-radius: 20px; box-shadow: var(--dx-shadow);
    }
    .dx-stage:not(:last-child)::after {
        content: ''; position: absolute; top: 38px; right: -15px; width: 16px; height: 2px;
        background: var(--dx-line-strong); z-index: 1;
    }
    .dx-stage.is-sun { --tone: var(--dx-sun); --tone-soft: var(--dx-sun-soft); --tone-bar: var(--dx-sun-bar); }
    .dx-stage.is-indigo { --tone: var(--dx-indigo); --tone-soft: var(--dx-indigo-soft); --tone-bar: var(--dx-indigo); }
    .dx-stage.is-teal { --tone: var(--dx-teal); --tone-soft: var(--dx-teal-soft); --tone-bar: var(--dx-teal); }
    .dx-stage.is-ok { --tone: var(--dx-ok); --tone-soft: var(--dx-ok-soft); --tone-bar: var(--dx-ok); }

    .dx-stage-top { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
    .dx-step {
        display: grid; place-items: center; width: 30px; height: 30px; border-radius: 50%;
        background: var(--tone-soft); color: var(--tone); font-family: var(--dx-display); font-weight: 800; font-size: .9rem;
    }
    .dx-stage.is-end .dx-step { background: var(--tone); color: var(--dx-surface); }
    .dx-flag {
        padding: 3px 9px; border-radius: 999px; font-size: .72rem; font-weight: 700;
        background: var(--dx-coral-soft); color: var(--dx-coral);
    }
    .dx-stage-name { margin: 4px 0 0; font-family: var(--dx-display); font-weight: 700; font-size: 1.02rem; color: var(--dx-ink); line-height: 1.2; }
    .dx-stage-desc { margin: 0; font-size: .82rem; color: var(--dx-muted); line-height: 1.45; min-height: 2.4em; }
    .dx-stage-count {
        margin-top: auto; font-family: var(--dx-display); font-weight: 800; font-size: 2.3rem; letter-spacing: -.03em;
        line-height: 1; color: var(--dx-ink); font-variant-numeric: tabular-nums;
    }
    .dx-stage-count small { display: block; margin-top: 4px; font-family: var(--dx-body); font-size: .78rem; font-weight: 500; letter-spacing: 0; color: var(--dx-muted); }
    .dx-bar { height: 8px; border-radius: 999px; background: var(--dx-sunken); overflow: hidden; }
    .dx-bar span { display: block; height: 100%; border-radius: 999px; background: var(--tone-bar); min-width: 4px; }

    /* ---------- Outcomes ---------- */
    .dx-card {
        padding: 24px; background: var(--dx-surface); border: 1px solid var(--dx-line);
        border-radius: 20px; box-shadow: var(--dx-shadow);
    }
    .dx-stack { display: flex; gap: 4px; height: 18px; border-radius: 999px; overflow: hidden; background: var(--dx-sunken); }
    .dx-stack span { display: block; height: 100%; border-radius: 999px; min-width: 6px; }
    .dx-outcomes { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); margin-top: 20px; }
    .dx-outcome { padding: 4px 22px; }
    .dx-outcome:first-child { padding-left: 0; }
    .dx-outcome + .dx-outcome { border-left: 1px solid var(--dx-line); }
    .dx-outcome-label { display: flex; align-items: center; gap: 8px; font-weight: 600; font-size: .9rem; color: var(--dx-muted); }
    .dx-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
    .dx-outcome-value {
        margin-top: 8px; font-family: var(--dx-display); font-weight: 800; font-size: 2rem; letter-spacing: -.02em;
        line-height: 1; color: var(--dx-ink); font-variant-numeric: tabular-nums;
    }
    .dx-outcome-desc { margin-top: 7px; font-size: .84rem; color: var(--dx-muted); }
    .dx-empty-note { margin: 0; padding: 26px; text-align: center; color: var(--dx-muted); border: 1px dashed var(--dx-line-strong); border-radius: 14px; }

    /* ---------- Records strip ---------- */
    .dx-strip {
        display: grid; grid-template-columns: repeat(3, minmax(0, 1fr));
        background: var(--dx-surface); border: 1px solid var(--dx-line); border-radius: 20px;
        box-shadow: var(--dx-shadow); overflow: hidden;
    }
    .dx-fig { padding: 22px 24px; }
    .dx-fig + .dx-fig { border-left: 1px solid var(--dx-line); }
    .dx-fig-label { display: flex; align-items: center; gap: 8px; font-size: .88rem; font-weight: 600; color: var(--dx-muted); }
    .dx-fig-label i { color: var(--tone, var(--dx-teal)); font-size: 1.05rem; }
    .dx-fig-value {
        margin-top: 8px; font-family: var(--dx-display); font-weight: 800; font-size: 2.1rem; letter-spacing: -.02em;
        line-height: 1; color: var(--dx-ink); font-variant-numeric: tabular-nums;
    }
    .dx-fig-desc { margin-top: 7px; font-size: .82rem; color: var(--dx-muted); }
    .t-teal { --tone: var(--dx-teal); }
    .t-ok { --tone: var(--dx-ok); }
    .t-indigo { --tone: var(--dx-indigo); }

    /* ---------- Table ---------- */
    .dx-table-card { background: var(--dx-surface); border: 1px solid var(--dx-line); border-radius: 20px; box-shadow: var(--dx-shadow); overflow: hidden; }
    .dx-table { width: 100%; border-collapse: collapse; }
    .dx-table th {
        padding: 13px 20px; text-align: left; white-space: nowrap; font-size: .82rem; font-weight: 700;
        color: var(--dx-muted); background: var(--dx-sunken); border-bottom: 1px solid var(--dx-line);
    }
    .dx-table td { padding: 17px 20px; border-bottom: 1px solid var(--dx-line); color: var(--dx-text); font-size: .93rem; vertical-align: middle; }
    .dx-table tbody tr:last-child td { border-bottom: 0; }
    .dx-table tbody tr:hover td { background: var(--dx-sunken); }
    .dx-cat { display: flex; align-items: center; gap: 12px; font-weight: 700; color: var(--dx-ink); white-space: nowrap; }
    .dx-cat i {
        display: grid; place-items: center; width: 34px; height: 34px; border-radius: 10px; font-size: .95rem;
        background: var(--tone-soft, var(--dx-teal-soft)); color: var(--tone, var(--dx-teal));
    }
    .dx-num { font-family: var(--dx-display); font-weight: 800; font-size: 1.1rem; color: var(--dx-ink); font-variant-numeric: tabular-nums; }
    .dx-share { display: flex; align-items: center; gap: 12px; min-width: 150px; }
    .dx-share .dx-bar { flex: 1; }
    .dx-share b { width: 42px; text-align: right; font-variant-numeric: tabular-nums; color: var(--dx-ink); font-size: .9rem; }
    .r-teal { --tone: var(--dx-teal); --tone-soft: var(--dx-teal-soft); --tone-bar: var(--dx-teal); }
    .r-ok { --tone: var(--dx-ok); --tone-soft: var(--dx-ok-soft); --tone-bar: var(--dx-ok); }
    .r-sun { --tone: var(--dx-sun); --tone-soft: var(--dx-sun-soft); --tone-bar: var(--dx-sun-bar); }
    .r-coral { --tone: var(--dx-coral); --tone-soft: var(--dx-coral-soft); --tone-bar: var(--dx-coral-bar); }

    /* ---------- Responsive ---------- */
    @media (max-width: 1199.98px) {
        .dx-pipe { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .dx-stage:not(:last-child)::after { display: none; }
        .dx-stage:last-child { grid-column: 1 / -1; }
    }
    @media (max-width: 767.98px) {
        .dx-hero { padding: 24px 18px; }
        .dx-hero-grid { grid-template-columns: 1fr; gap: 24px; }
        .dx-pipe { grid-template-columns: 1fr; }
        .dx-outcomes { grid-template-columns: 1fr; gap: 16px; }
        .dx-outcome, .dx-outcome:first-child { padding: 0; }
        .dx-outcome + .dx-outcome { border-left: 0; border-top: 1px solid var(--dx-line); padding-top: 16px; }
        .dx-strip { grid-template-columns: 1fr; }
        .dx-fig + .dx-fig { border-left: 0; border-top: 1px solid var(--dx-line); }
        .dx-head-side, .dx-head-side .dx-btn { width: 100%; }
        .dx-head-side .dx-btn { justify-content: center; }
    }
    @media (prefers-reduced-motion: reduce) {
        .dx-ring .fill { animation: none; }
        .dx-btn { transition: none; }
    }

    /* ---------- Print ---------- */
    @media print {
        .dx-head-side, .dx-lede a { display: none !important; }
        .dx-hero, .dx-card, .dx-stage, .dx-strip, .dx-table-card { box-shadow: none !important; break-inside: avoid; border: 1px solid #ccc !important; }
        .dx-hero { background: #fff !important; color: #000 !important; }
        .dx-hero * { color: #000 !important; }
        .dx-ring .track { stroke: #ddd; }
        .dx-ring .fill { stroke: #0f766e; }
    }
</style>
@endpush


@section('content')

@php
    /* Safe percentage helper: avoids division by zero */
    $pct = fn ($part, $whole) => $whole > 0 ? (int) round(($part / $whole) * 100) : 0;

    $releasedPercentage = $pct($releasedDocuments, $totalDocuments);
    $pendingPercentage  = $pct($pendingDocuments, $totalDocuments);
    $declinedPercentage = $pct($declinedDocuments, $totalDocuments);

    $ringValue = max(0, min(100, (int) round($monthlyCompletionRate)));

    $stages = [
        [
            'key'   => 'forInspection',
            'title' => 'For inspection',
            'desc'  => 'Physical appearance and valid ID',
            'tone'  => 'is-sun',
        ],
        [
            'key'   => 'forApproval',
            'title' => 'For approval',
            'desc'  => 'ID verified, eligibility review',
            'tone'  => 'is-indigo',
        ],
        [
            'key'   => 'readyForPrinting',
            'title' => 'Ready for printing',
            'desc'  => 'Approved for release',
            'tone'  => 'is-teal',
        ],
        [
            'key'   => 'readyForRelease',
            'title' => 'Ready for release',
            'desc'  => 'Printed, signed, and sealed',
            'tone'  => 'is-ok',
        ],
    ];

    $queueValues = collect($stages)->map(fn ($s) => (int) ($workflowCounts[$s['key']] ?? 0));
    $queueMax    = max(1, $queueValues->max());
    $queueTotal  = $queueValues->sum();
    $bottleneck  = $queueValues->max() > 0 ? $queueValues->search($queueValues->max()) : null;
@endphp

<div class="container-fluid py-4 dx">

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <header class="dx-head">
        <div>
            <h1 class="dx-title">Reports &amp; Analytics</h1>
            <p class="dx-sub">
                How document requests are moving, as of {{ now()->format('F j, Y') }},
                plus the resident and household records behind them.
            </p>
        </div>

        <div class="dx-head-side">
            <a href="{{ route('documents.index') }}" class="dx-btn dx-btn--line">
                <i class="bi bi-file-earmark-text"></i> Open documents
            </a>
            <a href="{{ route('dashboard') }}" class="dx-btn">
                <i class="bi bi-speedometer2"></i> Back to dashboard
            </a>
        </div>
    </header>


    <div class="dx-main">

        {{-- =====================================================
             HERO: this month at a glance
        ====================================================== --}}
        <section class="dx-hero" aria-label="This month at a glance">
            <div class="dx-hero-grid">

                <div class="dx-ring" role="img" aria-label="{{ $ringValue }} percent of this month's requests released">
                    <svg viewBox="0 0 40 40" aria-hidden="true">
                        <circle class="track" cx="20" cy="20" r="15.9155" pathLength="100"/>
                        <circle class="fill" cx="20" cy="20" r="15.9155" pathLength="100" style="--p: {{ $ringValue }}"/>
                    </svg>
                    <div class="dx-ring-body">
                        <div class="dx-ring-num">{{ $ringValue }}%</div>
                        <div class="dx-ring-cap">released this month</div>
                    </div>
                </div>

                <div>
                    <div class="dx-hero-kicker">{{ now()->format('F Y') }}</div>
                    <h2 class="dx-hero-title">
                        {{ number_format($totalDocuments) }} document {{ Str::plural('request', $totalDocuments) }} on record
                    </h2>
                    <p class="dx-hero-note">
                        The ring shows requests created this month that have already been released.
                        {{ number_format($releasedDocuments) }} have been released overall and
                        {{ number_format($pendingDocuments) }} are still being processed.
                    </p>

                    <div class="dx-chips">
                        <span class="dx-chip">
                            <i class="bi bi-stopwatch"></i>
                            @if ($averageProcessingHours !== null)
                                Average processing time <b>{{ $averageProcessingHours }} {{ Str::plural('hour', $averageProcessingHours) }}</b>
                            @else
                                No released documents this month yet
                            @endif
                        </span>

                        <span class="dx-chip">
                            <i class="bi bi-inbox"></i>
                            In workflow queues <b>{{ number_format($queueTotal) }}</b>
                        </span>
                    </div>
                </div>

            </div>
        </section>


        {{-- =====================================================
             WORKFLOW PIPELINE
        ====================================================== --}}
        <section aria-labelledby="pipeHeading">

            <div class="dx-chapter-head">
                <h2 class="dx-h2" id="pipeHeading"><i class="bi bi-diagram-3-fill"></i> Where requests are now</h2>
                <p class="dx-lede">
                    Requests waiting for action at each step. The stage with the biggest queue is flagged.
                </p>
            </div>

            <ol class="dx-pipe">

                @foreach ($stages as $stage)
                    @php
                        $count = (int) ($workflowCounts[$stage['key']] ?? 0);
                        $width = $pct($count, $queueMax);
                    @endphp

                    <li class="dx-stage {{ $stage['tone'] }}">
                        <div class="dx-stage-top">
                            <span class="dx-step">{{ $loop->iteration }}</span>
                            @if ($bottleneck === $loop->index)
                                <span class="dx-flag">Biggest queue</span>
                            @endif
                        </div>

                        <h3 class="dx-stage-name">{{ $stage['title'] }}</h3>
                        <p class="dx-stage-desc">{{ $stage['desc'] }}</p>

                        <div class="dx-stage-count">
                            {{ number_format($count) }}
                            <small>{{ Str::plural('request', $count) }} waiting</small>
                        </div>

                        <div class="dx-bar" role="img" aria-label="{{ $count }} of the largest queue of {{ $queueMax }}">
                            <span style="width: {{ $width }}%"></span>
                        </div>
                    </li>
                @endforeach

                {{-- Final step: released --}}
                <li class="dx-stage is-ok is-end">
                    <div class="dx-stage-top">
                        <span class="dx-step"><i class="bi bi-check-lg"></i></span>
                    </div>

                    <h3 class="dx-stage-name">Released</h3>
                    <p class="dx-stage-desc">Completed the release process</p>

                    <div class="dx-stage-count">
                        {{ number_format($releasedDocuments) }}
                        <small>{{ $releasedPercentage }}% of all requests</small>
                    </div>

                    <div class="dx-bar" role="img" aria-label="{{ $releasedPercentage }} percent released">
                        <span style="width: {{ $releasedPercentage }}%"></span>
                    </div>
                </li>

            </ol>

        </section>


        {{-- =====================================================
             OUTCOMES
        ====================================================== --}}
        <section aria-labelledby="outHeading">

            <div class="dx-chapter-head">
                <h2 class="dx-h2" id="outHeading"><i class="bi bi-bar-chart-fill"></i> How requests ended up</h2>
                <p class="dx-lede">Overall split of every document request recorded.</p>
            </div>

            <div class="dx-card">
                @if ($totalDocuments > 0)

                    <div class="dx-stack" role="img"
                         aria-label="{{ $releasedDocuments }} released, {{ $pendingDocuments }} pending, {{ $declinedDocuments }} declined">
                        @if ($releasedDocuments > 0)
                            <span style="flex: {{ $releasedDocuments }}; background: var(--dx-ok)"></span>
                        @endif
                        @if ($pendingDocuments > 0)
                            <span style="flex: {{ $pendingDocuments }}; background: var(--dx-sun-bar)"></span>
                        @endif
                        @if ($declinedDocuments > 0)
                            <span style="flex: {{ $declinedDocuments }}; background: var(--dx-coral-bar)"></span>
                        @endif
                    </div>

                    <div class="dx-outcomes">
                        <div class="dx-outcome">
                            <div class="dx-outcome-label"><i class="dx-dot" style="background: var(--dx-ok)"></i> Released</div>
                            <div class="dx-outcome-value">{{ number_format($releasedDocuments) }}</div>
                            <div class="dx-outcome-desc">{{ $releasedPercentage }}% successfully released</div>
                        </div>

                        <div class="dx-outcome">
                            <div class="dx-outcome-label"><i class="dx-dot" style="background: var(--dx-sun-bar)"></i> Pending</div>
                            <div class="dx-outcome-value">{{ number_format($pendingDocuments) }}</div>
                            <div class="dx-outcome-desc">{{ $pendingPercentage }}% still being processed</div>
                        </div>

                        <div class="dx-outcome">
                            <div class="dx-outcome-label"><i class="dx-dot" style="background: var(--dx-coral-bar)"></i> Declined</div>
                            <div class="dx-outcome-value">{{ number_format($declinedDocuments) }}</div>
                            <div class="dx-outcome-desc">{{ $declinedPercentage }}% not released</div>
                        </div>
                    </div>

                @else

                    <p class="dx-empty-note">No document requests yet. Results will show here once requests come in.</p>

                @endif
            </div>

        </section>


        {{-- =====================================================
             RECORDS
        ====================================================== --}}
        <section aria-labelledby="recHeading">

            <div class="dx-chapter-head">
                <h2 class="dx-h2" id="recHeading"><i class="bi bi-database-fill"></i> Records on file</h2>
                <p class="dx-lede">Currently registered residents and households.</p>
            </div>

            <div class="dx-strip">
                <div class="dx-fig t-teal">
                    <div class="dx-fig-label"><i class="bi bi-people-fill"></i> Residents</div>
                    <div class="dx-fig-value">{{ number_format($residentCount) }}</div>
                    <div class="dx-fig-desc">All resident records</div>
                </div>

                <div class="dx-fig t-ok">
                    <div class="dx-fig-label"><i class="bi bi-person-check-fill"></i> Active residents</div>
                    <div class="dx-fig-value">{{ number_format($activeResidentCount) }}</div>
                    <div class="dx-fig-desc">{{ $pct($activeResidentCount, $residentCount) }}% of all residents</div>
                </div>

                <div class="dx-fig t-indigo">
                    <div class="dx-fig-label"><i class="bi bi-house-fill"></i> Households</div>
                    <div class="dx-fig-value">{{ number_format($householdCount) }}</div>
                    <div class="dx-fig-desc">Registered households</div>
                </div>
            </div>

        </section>


        {{-- =====================================================
             SUMMARY TABLE
        ====================================================== --}}
        <section aria-labelledby="sumHeading">

            <div class="dx-chapter-head">
                <h2 class="dx-h2" id="sumHeading"><i class="bi bi-table"></i> Document processing summary</h2>
                <p class="dx-lede">Document requests currently recorded in the Record Management System.</p>
            </div>

            <div class="dx-table-card">
                <div class="table-responsive">
                    <table class="dx-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Count</th>
                                <th>Description</th>
                                <th>Share of all requests</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr class="r-teal">
                                <td><div class="dx-cat"><i class="bi bi-file-earmark-text"></i> Total documents</div></td>
                                <td><span class="dx-num">{{ number_format($totalDocuments) }}</span></td>
                                <td>All document requests recorded.</td>
                                <td>
                                    <div class="dx-share">
                                        <div class="dx-bar"><span style="width: 100%"></span></div>
                                        <b>100%</b>
                                    </div>
                                </td>
                            </tr>

                            <tr class="r-ok">
                                <td><div class="dx-cat"><i class="bi bi-check-circle"></i> Released</div></td>
                                <td><span class="dx-num">{{ number_format($releasedDocuments) }}</span></td>
                                <td>Documents that completed the release process.</td>
                                <td>
                                    <div class="dx-share">
                                        <div class="dx-bar"><span style="width: {{ $releasedPercentage }}%"></span></div>
                                        <b>{{ $releasedPercentage }}%</b>
                                    </div>
                                </td>
                            </tr>

                            <tr class="r-sun">
                                <td><div class="dx-cat"><i class="bi bi-hourglass-split"></i> Pending</div></td>
                                <td><span class="dx-num">{{ number_format($pendingDocuments) }}</span></td>
                                <td>Documents still undergoing processing.</td>
                                <td>
                                    <div class="dx-share">
                                        <div class="dx-bar"><span style="width: {{ $pendingPercentage }}%"></span></div>
                                        <b>{{ $pendingPercentage }}%</b>
                                    </div>
                                </td>
                            </tr>

                            <tr class="r-coral">
                                <td><div class="dx-cat"><i class="bi bi-x-circle"></i> Declined</div></td>
                                <td><span class="dx-num">{{ number_format($declinedDocuments) }}</span></td>
                                <td>Requests that were not released.</td>
                                <td>
                                    <div class="dx-share">
                                        <div class="dx-bar"><span style="width: {{ $declinedPercentage }}%"></span></div>
                                        <b>{{ $declinedPercentage }}%</b>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </section>

    </div>
</div>

@endsection
