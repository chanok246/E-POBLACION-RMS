@extends('layouts.app')

@section('title', 'Relief Operation Details | e-Poblacion')

@section('content')

@php
    $operation = $operation ?? null;
    if (!$operation) {
        abort(404);
    }

    /* ---------- Identity & status ---------- */
    $operationCode = $operation->operation_code
        ?? ('RA-' . str_pad($operation->id, 4, '0', STR_PAD_LEFT));
    $operationName = $operation->operation_name ?? 'Relief Assistance Operation';
    $status        = strtolower((string) ($operation->status ?? 'draft'));

    $statusLabels = [
        'draft' => 'Draft', 'open' => 'Open', 'active' => 'Active',
        'verification' => 'For verification', 'ready' => 'Ready',
        'allocated' => 'Allocated', 'distribution' => 'For distribution',
        'completed' => 'Completed', 'cancelled' => 'Cancelled',
    ];
    $statusLabel = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status));

    $isCompleted = $status === 'completed';
    $isCancelled = $status === 'cancelled';

    /* ---------- Vouchers ---------- */
    $vouchers         = $operation->vouchers ?? collect();
    $total            = $operation->vouchers_count ?? $vouchers->count();
    $outCount         = $vouchers->whereIn('status', ['issued', 'distributed'])->count();
    $claimedCount     = $vouchers->where('status', 'claimed')->count();
    $cancelledCount   = $vouchers->where('status', 'cancelled')->count();
    $pct              = fn ($n) => $total > 0 ? round(($n / $total) * 100, 1) : 0;
    $claimedPct       = $pct($claimedCount);

    /* ---------- Quantity ---------- */
    $qtyReceived  = $operation->quantity_received ?? null;
    $qtyAllocated = (float) ($operation->quantity_allocated ?? 0);
    $qtyUnit      = $operation->quantity_unit ?? null;
    $qtyRemaining = is_numeric($qtyReceived)
        ? max(0, (float) $qtyReceived - $qtyAllocated) : null;
    $qtyPct       = (is_numeric($qtyReceived) && $qtyReceived > 0)
        ? min(100, round($qtyAllocated / (float) $qtyReceived * 100)) : 0;

    /* ---------- Helpers ---------- */
    $fmt = function ($value, $format) {
        if (empty($value)) return '—';
        try { return \Illuminate\Support\Carbon::parse($value)->format($format); }
        catch (\Throwable $e) { return '—'; }
    };

    /* ---------- Routes ---------- */
    $masterlistUrl   = route('relief-assistance.masterlist', ['operation_id' => $operation->id]);
    $previewUrl      = route('relief-assistance.masterlist.print', ['operation_id' => $operation->id]);
    $beneficiaryUrl  = route('relief-assistance.beneficiaries', $operation);
    $vouchersUrl     = route('relief-assistance.vouchers');
    $qrUrl           = route('relief-vouchers.qr-verification');
    $canEdit         = Route::has('relief-assistance.edit') && !$isCancelled && !$isCompleted;

    /* ---------- Current stage (1–4, 5 = all done) ---------- */
    $current = $isCompleted ? 5 : ($total > 0 ? 4 : 1);

    $nextAction = match (true) {
        $isCompleted => ['View voucher records', $vouchersUrl, 'bi-ticket-perforated'],
        $current === 4 => ['Verify & record claims', $qrUrl, 'bi-qr-code-scan'],
        default => ['Prepare masterlist', $masterlistUrl, 'bi-list-check'],
    };

    $stepState = fn ($n) => $n < $current ? 'done' : ($n === $current ? 'current' : 'upcoming');
@endphp

<div class="container-fluid py-4 op-page">

    {{-- ================= HERO ================= --}}
    <section class="op-hero mb-4">
        <div class="op-hero-top">
            <nav class="op-crumb">
                <a href="{{ route('relief-vouchers.index') }}"><i class="bi bi-arrow-left me-1"></i>Relief Assistance</a>
            </nav>

            @if ($canEdit)
                <a href="{{ route('relief-assistance.edit', $operation) }}" class="btn btn-sm op-btn-ghost">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
            @endif
        </div>

        <div class="op-hero-main">
            <div class="min-w-0">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="op-status op-status-{{ $status }}">{{ $statusLabel }}</span>
                    <span class="op-code">{{ $operationCode }}</span>
                </div>

                <h1 class="op-title">{{ $operationName }}</h1>

                <div class="op-meta">
                    @if (!empty($operation->assistance_type))
                        <span><i class="bi bi-box2-heart"></i>{{ $operation->assistance_type }}</span>
                    @endif
                    <span><i class="bi bi-calendar-event"></i>{{ $fmt($operation->distribution_date, 'M d, Y') }}</span>
                    @if (!empty($operation->distribution_location))
                        <span><i class="bi bi-geo-alt"></i>{{ $operation->distribution_location }}</span>
                    @endif
                </div>
            </div>

            @unless ($isCancelled)
                <a href="{{ $nextAction[1] }}" class="op-cta">
                    <span class="op-cta-label">{{ $isCompleted ? 'Operation finished' : 'Next up' }}</span>
                    <span class="op-cta-action"><i class="bi {{ $nextAction[2] }}"></i>{{ $nextAction[0] }}</span>
                </a>
            @endunless
        </div>

        {{-- Claim progress --}}
        <div class="op-progress-wrap">
            <div class="d-flex justify-content-between align-items-end mb-2">
                <div>
                    <span class="op-progress-big">{{ number_format($claimedCount) }}</span>
                    <span class="op-progress-of">of {{ number_format($total) }} vouchers claimed</span>
                </div>
                <span class="op-progress-pct">{{ $claimedPct }}%</span>
            </div>

            <div class="op-bar" role="img"
                 aria-label="{{ $claimedCount }} claimed, {{ $outCount }} out for distribution, {{ $cancelledCount }} cancelled of {{ $total }} vouchers">
                <span class="seg seg-claimed"   style="width: {{ $pct($claimedCount) }}%"></span>
                <span class="seg seg-out"       style="width: {{ $pct($outCount) }}%"></span>
                <span class="seg seg-cancelled" style="width: {{ $pct($cancelledCount) }}%"></span>
            </div>

            <div class="op-legend">
                <span><i class="dot dot-claimed"></i>Claimed <b>{{ number_format($claimedCount) }}</b></span>
                <span><i class="dot dot-out"></i>Issued / distributed <b>{{ number_format($outCount) }}</b></span>
                <span><i class="dot dot-cancelled"></i>Cancelled <b>{{ number_format($cancelledCount) }}</b></span>
            </div>
        </div>
    </section>


    @if ($isCancelled)
        <div class="op-alert op-alert-danger mb-4">
            <i class="bi bi-x-circle-fill"></i>
            <div>
                <strong>This operation was cancelled.</strong>
                <div>Existing records are kept for reference and audit.</div>
            </div>
        </div>
    @endif


    <div class="row g-4">

        {{-- ================= LEFT: WORKFLOW ================= --}}
        <div class="col-xl-8">

            @unless ($isCancelled)
                <section class="op-card">
                    <header class="op-card-head">
                        <div>
                            <h2>How this operation runs</h2>
                            <p>Four stages, in order. You can open any stage at any time.</p>
                        </div>
                        <span class="op-human" title="The system never approves beneficiaries on its own.">
                            <i class="bi bi-person-check-fill"></i>Staff decide who receives aid
                        </span>
                    </header>

                    <ol class="op-steps">

                        {{-- 1 --}}
                        <li class="op-step is-{{ $stepState(1) }}">
                            <span class="op-step-marker">
                                @if ($stepState(1) === 'done') <i class="bi bi-check-lg"></i> @else 1 @endif
                            </span>
                            <div class="op-step-body">
                                <h3>Prepare the masterlist</h3>
                                <p>Filter residents by target group, Purok, or keyword, check the full list on screen, then print it for the field team.</p>
                                <div class="op-step-actions">
                                    <a href="{{ $masterlistUrl }}" class="btn btn-sm {{ $stepState(1) === 'current' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                        <i class="bi bi-funnel me-1"></i>Open workspace
                                    </a>
                                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-printer me-1"></i>Preview &amp; print
                                    </a>
                                </div>
                            </div>
                        </li>

                        {{-- 2 --}}
                        <li class="op-step is-{{ $stepState(2) }}">
                            <span class="op-step-marker">
                                @if ($stepState(2) === 'done') <i class="bi bi-check-lg"></i> @else 2 @endif
                            </span>
                            <div class="op-step-body">
                                <h3>Field assessment</h3>
                                <p>BHW or staff take the printed list door to door and mark who actually qualifies. This step is done on paper, outside the system.</p>
                                <div class="op-step-note"><i class="bi bi-journal-check me-1"></i>Nothing to do here until the marked list comes back.</div>
                            </div>
                        </li>

                        {{-- 3 --}}
                        <li class="op-step is-{{ $stepState(3) }}">
                            <span class="op-step-marker">
                                @if ($stepState(3) === 'done') <i class="bi bi-check-lg"></i> @else 3 @endif
                            </span>
                            <div class="op-step-body">
                                <h3>Encode and review beneficiaries</h3>
                                <p>Using the marked list, select the actual recipients from the same masterlist and confirm the final selection before any voucher is made.</p>
                                <div class="op-step-actions">
                                    <a href="{{ $beneficiaryUrl }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-person-check me-1"></i>Open beneficiary encoding
                                    </a>
                                </div>
                            </div>
                        </li>

                        {{-- 4 --}}
                        <li class="op-step is-{{ $stepState(4) }}">
                            <span class="op-step-marker">
                                @if ($stepState(4) === 'done') <i class="bi bi-check-lg"></i> @else 4 @endif
                            </span>
                            <div class="op-step-body">
                                <h3>Print vouchers and distribute</h3>
                                <p>Print the bulk vouchers for the confirmed recipients. On distribution day, scan each QR code to record a one-time claim.</p>
                                <div class="op-step-actions">
                                    <a href="{{ $vouchersUrl }}" class="btn btn-sm {{ $stepState(4) === 'current' ? 'btn-primary' : 'btn-outline-secondary' }}">
                                        <i class="bi bi-ticket-perforated me-1"></i>Voucher records
                                    </a>
                                    <a href="{{ $qrUrl }}" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-qr-code-scan me-1"></i>QR verification
                                    </a>
                                </div>
                            </div>
                        </li>

                    </ol>

                    <footer class="op-note">
                        <i class="bi bi-info-circle"></i>
                        <span>
                            Filters such as Household Heads, Students, and Senior Citizens only suggest who may be relevant.
                            Being on the masterlist does not make someone a beneficiary.
                        </span>
                    </footer>
                </section>
            @endunless


            @if (!empty($operation->allocation_notes))
                <section class="op-card mt-4">
                    <header class="op-card-head"><div><h2>Notes</h2></div></header>
                    <div class="op-text">{!! nl2br(e($operation->allocation_notes)) !!}</div>
                </section>
            @endif

        </div>


        {{-- ================= RIGHT: DETAILS ================= --}}
        <div class="col-xl-4">

            {{-- Distribution --}}
            <section class="op-card mb-4">
                <header class="op-card-head"><div><h2>Distribution</h2></div></header>

                <dl class="op-dl">
                    <div><dt><i class="bi bi-calendar-event"></i>Date</dt><dd>{{ $fmt($operation->distribution_date, 'F d, Y') }}</dd></div>
                    <div><dt><i class="bi bi-clock"></i>Time</dt><dd>{{ $fmt($operation->distribution_time, 'h:i A') }}</dd></div>
                    <div><dt><i class="bi bi-geo-alt"></i>Location</dt><dd>{{ $operation->distribution_location ?? '—' }}</dd></div>
                </dl>

                <div class="op-qty">
                    <div class="d-flex justify-content-between align-items-baseline mb-1">
                        <span class="op-qty-title">Stock allocated</span>
                        <span class="op-qty-fig">
                            {{ number_format($qtyAllocated, 2) }}
                            <small>/ {{ is_numeric($qtyReceived) ? number_format((float) $qtyReceived, 2) : '—' }} {{ $qtyUnit }}</small>
                        </span>
                    </div>
                    <div class="op-mini-bar"><span style="width: {{ $qtyPct }}%"></span></div>
                    @if ($qtyRemaining !== null)
                        <div class="op-qty-left">{{ number_format($qtyRemaining, 2) }} {{ $qtyUnit }} remaining</div>
                    @endif
                </div>
            </section>

            {{-- Operation info --}}
            <section class="op-card">
                <header class="op-card-head"><div><h2>Operation details</h2></div></header>

                <dl class="op-dl">
                    <div><dt>Assistance type</dt><dd>{{ $operation->assistance_type ?? '—' }}</dd></div>
                    <div><dt>Source / donor</dt><dd>{{ $operation->source_name ?? '—' }}</dd></div>
                    <div><dt>Source reference</dt><dd>{{ $operation->source_reference ?? '—' }}</dd></div>
                    <div><dt>Received on</dt><dd>{{ $fmt($operation->assistance_received_date, 'F d, Y') }}</dd></div>
                    <div><dt>Created</dt><dd>{{ $fmt($operation->created_at, 'F d, Y h:i A') }}</dd></div>
                </dl>

                <details class="op-details" @if(!empty($operation->description)) open @endif>
                    <summary>Description</summary>
                    <div class="op-text">{!! nl2br(e($operation->description ?? 'No description provided.')) !!}</div>
                </details>

                <details class="op-details">
                    <summary>Assistance details</summary>
                    <div class="op-text">{!! nl2br(e($operation->assistance_description ?? 'No additional assistance details provided.')) !!}</div>
                </details>
            </section>

        </div>
    </div>
</div>


<style>
    /* =========================================================
       Relief operation details — balanced light & dark mode.
       Every text/background pair below keeps strong contrast
       in BOTH themes. Colors live in tokens so both modes stay
       in sync.
       ========================================================= */
    .op-page {
        /* surfaces */
        --op-card: #ffffff;
        --op-soft: #f1f6f5;
        --op-soft-2: #e3ecea;
        --op-line: #d3dedb;
        --op-line-strong: #98aca7;

        /* text */
        --op-text: #10302b;
        --op-text-2: #2c3f3b;
        --op-muted: #4a5d59;

        /* brand */
        --op-teal: #0b6b63;
        --op-teal-soft: #d6eee8;
        --op-fill: #0b6b63;
        --op-btn-bg: #0b6b63;
        --op-btn-fg: #ffffff;
        --op-btn-hover: #08544e;
        --op-current-bg: #eaf6f3;
        --op-current-line: #8fcdc2;

        /* notices */
        --op-amber-bg: #fdf0cf;
        --op-amber-ink: #5e4009;
        --op-danger-bg: #fde8ea;
        --op-danger-ink: #7d1f2b;
        --op-focus: #9a6700;

        /* hero (soft teal, white text) */
        --op-hero-from: #0b6b63;
        --op-hero-to: #118176;
        --op-hero-fg: #ffffff;
        --op-hero-muted: rgba(255,255,255,.9);
        --op-hero-track: rgba(255,255,255,.24);
        --op-hero-chip: rgba(255,255,255,.2);
        --op-hero-line: rgba(255,255,255,.3);
        --op-cta-bg: #ffffff;
        --op-cta-fg: #10302b;
        --op-cta-sub: #4a5d59;

        --op-shadow: 0 1px 2px rgba(16,48,43,.06), 0 10px 24px rgba(16,48,43,.07);

        color: var(--op-text);
        font-feature-settings: "tnum";
    }
    html[data-theme="dark"] .op-page {
        --op-card: #1b2b26;
        --op-soft: #15221e;
        --op-soft-2: #263a34;
        --op-line: #31493f;
        --op-line-strong: #5a7a6f;

        --op-text: #f0f7f4;
        --op-text-2: #d6e4df;
        --op-muted: #b3c6c0;

        --op-teal: #6fe0c8;
        --op-teal-soft: #1f3d36;
        --op-fill: #5fdcc2;
        --op-btn-bg: #5fdcc2;
        --op-btn-fg: #06231e;
        --op-btn-hover: #86e9d6;
        --op-current-bg: #1f3a33;
        --op-current-line: #3f7a6c;

        --op-amber-bg: #3a2f14;
        --op-amber-ink: #f6dc96;
        --op-danger-bg: #43202a;
        --op-danger-ink: #ffc0c6;
        --op-focus: #f2c14e;

        --op-hero-from: #1f4d45;
        --op-hero-to: #163a34;
        --op-hero-fg: #f2faf7;
        --op-hero-muted: #cfe3dd;
        --op-hero-track: rgba(255,255,255,.16);
        --op-hero-chip: rgba(255,255,255,.14);
        --op-hero-line: rgba(255,255,255,.22);
        --op-cta-bg: #eef7f4;
        --op-cta-fg: #0d2a25;
        --op-cta-sub: #4a5d59;

        --op-shadow: none;
    }
    .op-page, .op-page * { box-sizing: border-box; }
    .op-page .min-w-0 { min-width: 0; }

    /* ---------- Hero ---------- */
    .op-hero {
        position: relative; overflow: hidden;
        color: var(--op-hero-fg);
        border-radius: 1.25rem;
        padding: 1.25rem 1.5rem 1.5rem;
        background: linear-gradient(125deg, var(--op-hero-from), var(--op-hero-to));
        box-shadow: var(--op-shadow);
    }
    .op-hero::before {
        content: ""; position: absolute; width: 260px; height: 260px; right: -70px; top: -120px;
        border-radius: 50%; background: rgba(255,255,255,.1); pointer-events: none;
    }
    .op-hero > * { position: relative; z-index: 1; }
    .op-hero-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
    .op-crumb a { color: var(--op-hero-muted); text-decoration: none; font-size: .88rem; font-weight: 600; }
    .op-crumb a:hover { color: var(--op-hero-fg); text-decoration: underline; }
    .op-page .op-btn-ghost { color: var(--op-hero-fg); border: 1.5px solid var(--op-hero-line); background: var(--op-hero-chip); font-weight: 700; }
    .op-page .op-btn-ghost:hover { background: rgba(255,255,255,.28); color: var(--op-hero-fg); border-color: var(--op-hero-fg); }

    .op-hero-main { display: flex; flex-wrap: wrap; gap: 1.25rem; justify-content: space-between; align-items: flex-end; }
    .op-title { font-size: clamp(1.5rem, 2.6vw, 2.15rem); font-weight: 800; letter-spacing: -.02em; line-height: 1.15; margin: 0 0 .75rem; color: var(--op-hero-fg); }
    .op-code { font-size: .82rem; font-weight: 600; color: var(--op-hero-muted); }
    .op-meta { display: flex; flex-wrap: wrap; gap: .5rem 1.25rem; font-size: .9rem; color: var(--op-hero-muted); }
    .op-meta i { margin-right: .4rem; color: var(--op-hero-fg); opacity: .85; }

    .op-status { padding: .22rem .7rem; border-radius: 999px; font-size: .78rem; font-weight: 800; background: var(--op-hero-chip); color: var(--op-hero-fg); border: 1px solid var(--op-hero-line); }
    .op-status-completed { background: #167a50; border-color: #167a50; color: #fff; }
    .op-status-cancelled { background: #b4303c; border-color: #b4303c; color: #fff; }
    .op-status-open, .op-status-active { background: #ffffff; border-color: #ffffff; color: #0b5a53; }
    .op-status-distribution, .op-status-allocated { background: #f2c14e; border-color: #f2c14e; color: #3d2a05; }

    .op-cta {
        display: flex; flex-direction: column; gap: .15rem;
        background: var(--op-cta-bg); color: var(--op-cta-fg); text-decoration: none;
        padding: .8rem 1.15rem; border-radius: .9rem; min-width: 230px;
        box-shadow: 0 8px 18px rgba(0,0,0,.18);
        transition: transform .15s ease, box-shadow .15s ease;
    }
    .op-cta:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(0,0,0,.26); color: var(--op-cta-fg); }
    .op-cta-label { font-size: .75rem; font-weight: 700; color: var(--op-cta-sub); }
    .op-cta-action { font-weight: 800; display: flex; align-items: center; gap: .55rem; }

    /* ---------- Progress ---------- */
    .op-progress-wrap { margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--op-hero-line); }
    .op-progress-big { font-size: 2.1rem; font-weight: 800; line-height: 1; color: var(--op-hero-fg); }
    .op-progress-of { margin-left: .5rem; color: var(--op-hero-muted); font-size: .92rem; font-weight: 600; }
    .op-progress-pct { font-weight: 800; color: var(--op-hero-fg); }
    .op-bar { display: flex; height: 12px; border-radius: 999px; overflow: hidden; background: var(--op-hero-track); }
    .op-bar .seg { height: 100%; transition: width .5s ease; }
    .seg-claimed, .dot-claimed { background: #7ff0d2; }
    .seg-out, .dot-out { background: #ffd36b; }
    .seg-cancelled, .dot-cancelled { background: #ff9aa3; }
    .op-legend { display: flex; flex-wrap: wrap; gap: .4rem 1.25rem; margin-top: .75rem; font-size: .85rem; font-weight: 600; color: var(--op-hero-muted); }
    .op-legend b { color: var(--op-hero-fg); margin-left: .25rem; }
    .op-legend .dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; margin-right: .4rem; }

    /* ---------- Alerts ---------- */
    .op-alert { display: flex; gap: .9rem; padding: 1rem 1.15rem; border-radius: .9rem; font-size: .92rem; }
    .op-alert i { font-size: 1.3rem; }
    .op-alert-danger { background: var(--op-danger-bg); color: var(--op-danger-ink); border: 1px solid var(--op-danger-ink); }

    /* ---------- Cards ---------- */
    .op-card { background: var(--op-card); color: var(--op-text); border: 1px solid var(--op-line); border-radius: 1rem; padding: 1.35rem 1.5rem; box-shadow: var(--op-shadow); }
    .op-card-head { display: flex; flex-wrap: wrap; gap: .75rem; justify-content: space-between; align-items: flex-start; margin-bottom: 1.1rem; }
    .op-card-head h2 { font-size: 1.1rem; font-weight: 800; margin: 0; color: var(--op-text); }
    .op-card-head p { margin: .2rem 0 0; font-size: .88rem; color: var(--op-muted); }

    .op-human { display: inline-flex; align-items: center; gap: .4rem; padding: .35rem .75rem; border-radius: 999px; background: var(--op-amber-bg); color: var(--op-amber-ink); font-size: .8rem; font-weight: 800; border: 1px solid var(--op-amber-ink); }

    /* ---------- Buttons (explicit, so they are readable in both themes) ---------- */
    .op-page .btn { font-weight: 700; border-radius: .6rem; }
    .op-page .btn-primary { background: var(--op-btn-bg); border: 1.5px solid var(--op-btn-bg); color: var(--op-btn-fg); }
    .op-page .btn-primary:hover, .op-page .btn-primary:focus-visible { background: var(--op-btn-hover); border-color: var(--op-btn-hover); color: var(--op-btn-fg); }
    .op-page .btn-outline-secondary { background: var(--op-card); border: 1.5px solid var(--op-line-strong); color: var(--op-text); }
    .op-page .btn-outline-secondary:hover, .op-page .btn-outline-secondary:focus-visible { background: var(--op-soft-2); border-color: var(--op-teal); color: var(--op-text); }

    /* ---------- Steps ---------- */
    .op-steps { list-style: none; margin: 0; padding: 0; position: relative; }
    .op-step { position: relative; display: flex; gap: 1rem; padding-bottom: 1.5rem; }
    .op-step:last-child { padding-bottom: .25rem; }
    .op-step::before { content: ""; position: absolute; left: 17px; top: 38px; bottom: 2px; width: 2px; background: var(--op-line); }
    .op-step:last-child::before { display: none; }
    .op-step.is-done::before { background: var(--op-teal); }

    .op-step-marker {
        flex: 0 0 36px; width: 36px; height: 36px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: .9rem; background: var(--op-soft-2); color: var(--op-text-2);
        border: 2px solid var(--op-line-strong); z-index: 1;
    }
    .op-step.is-current .op-step-marker { background: var(--op-btn-bg); border-color: var(--op-btn-bg); color: var(--op-btn-fg); box-shadow: 0 0 0 4px var(--op-teal-soft); }
    .op-step.is-done .op-step-marker { background: var(--op-btn-bg); border-color: var(--op-btn-bg); color: var(--op-btn-fg); }

    .op-step-body { flex: 1; min-width: 0; padding-top: .3rem; }
    .op-step-body h3 { font-size: 1.02rem; font-weight: 800; margin: 0 0 .3rem; color: var(--op-text); }
    .op-step-body p { font-size: .9rem; line-height: 1.6; color: var(--op-text-2); margin: 0 0 .75rem; max-width: 62ch; }
    .op-step.is-current .op-step-body {
        background: var(--op-current-bg);
        border: 1px solid var(--op-current-line); border-radius: .85rem; padding: .9rem 1rem;
    }
    .op-step-actions { display: flex; flex-wrap: wrap; gap: .5rem; }
    .op-step-note { font-size: .84rem; font-weight: 600; color: var(--op-muted); }

    .op-note { display: flex; gap: .7rem; margin-top: 1.25rem; padding: .85rem 1rem; border-radius: .75rem; background: var(--op-amber-bg); color: var(--op-amber-ink); font-size: .88rem; line-height: 1.55; font-weight: 500; }
    .op-note i { margin-top: .1rem; }

    /* ---------- Details ---------- */
    .op-dl { margin: 0; }
    .op-dl > div { display: flex; justify-content: space-between; gap: 1rem; padding: .7rem 0; border-bottom: 1px solid var(--op-line); }
    .op-dl > div:last-child { border-bottom: 0; }
    .op-dl dt { font-weight: 600; font-size: .88rem; color: var(--op-muted); }
    .op-dl dt i { margin-right: .5rem; color: var(--op-teal); }
    .op-dl dd { margin: 0; font-weight: 700; font-size: .92rem; text-align: right; color: var(--op-text); overflow-wrap: anywhere; }

    .op-qty { margin-top: 1rem; padding: .9rem 1rem; background: var(--op-soft); border: 1px solid var(--op-line); border-radius: .8rem; }
    .op-qty-title { font-size: .88rem; font-weight: 600; color: var(--op-muted); }
    .op-qty-fig { font-weight: 800; color: var(--op-text); }
    .op-qty-fig small { font-weight: 600; color: var(--op-muted); }
    .op-mini-bar { height: 9px; border-radius: 999px; background: var(--op-soft-2); border: 1px solid var(--op-line); overflow: hidden; }
    .op-mini-bar span { display: block; height: 100%; background: var(--op-fill); border-radius: 999px; }
    .op-qty-left { margin-top: .5rem; font-size: .84rem; font-weight: 600; color: var(--op-muted); }

    .op-details { border-top: 1px solid var(--op-line); margin-top: .5rem; padding-top: .7rem; }
    .op-details summary { cursor: pointer; font-weight: 700; font-size: .9rem; color: var(--op-text); list-style: none; display: flex; justify-content: space-between; }
    .op-details summary::after { content: "+"; color: var(--op-muted); font-weight: 600; }
    .op-details[open] summary::after { content: "–"; }
    .op-details summary::-webkit-details-marker { display: none; }
    .op-text { margin-top: .6rem; font-size: .9rem; line-height: 1.65; color: var(--op-text-2); }

    /* ---------- Focus & motion ---------- */
    .op-page a:focus-visible, .op-page summary:focus-visible, .op-page .btn:focus-visible { outline: 3px solid var(--op-focus); outline-offset: 2px; border-radius: .4rem; }
    @media (prefers-reduced-motion: reduce) {
        .op-page * { transition: none !important; }
    }

    /* ---------- Responsive ---------- */
    @media (max-width: 767.98px) {
        .op-hero { padding: 1rem; }
        .op-cta { width: 100%; }
        .op-card { padding: 1.1rem; }
        .op-step-actions .btn { flex: 1 1 100%; }
    }
</style>

@endsection