{{-- resources/views/relief-vouchers/show.blade.php --}}

@extends('layouts.app')

@section('title', 'Relief Voucher Details')

@section('content')

@php
    $status    = strtolower((string) $reliefVoucher->status) ?: 'pending';
    $operation = $reliefVoucher->operation;
    $household = $reliefVoucher->household;
    $head      = $household?->householdHead;

    $tones = [
        'claimed' => 'is-green', 'distributed' => 'is-mango', 'issued' => 'is-ube',
        'generated' => 'is-ube', 'cancelled' => 'is-red', 'pending' => 'is-grey',
    ];
    $tone  = $tones[$status] ?? 'is-grey';
    $label = ucfirst($status);

    $messages = [
        'claimed'     => 'This voucher was verified through QR scanning and claimed automatically.',
        'cancelled'   => 'This voucher was cancelled and can no longer be claimed.',
        'distributed' => 'Marked as distributed. It is waiting for final QR verification.',
        'issued'      => 'Issued and ready for QR verification. Printing does not lock it. You can still correct it until it is scanned.',
        'generated'   => 'Generated. Printing does not lock it. You can still correct it, then print and issue it.',
        'pending'     => 'This voucher is still pending.',
    ];

    /* Progress: 0 generated, 1 issued, 2 QR verified, 3 claimed */
    $stage = 0;
    if (in_array($status, ['issued', 'distributed'])) { $stage = 1; }
    if ($reliefVoucher->qr_verified_at) { $stage = 2; }
    if ($status === 'claimed' || $reliefVoucher->claimed_at) { $stage = 3; }

    $steps = [
        ['Generated',   'Voucher created with a unique code', 'bi-ticket-perforated'],
        ['Issued',      'Handed to the beneficiary',          'bi-send-check'],
        ['QR verified', 'Scanned at distribution',            'bi-qr-code-scan'],
        ['Claimed',     'Claim recorded by the system',       'bi-check2-circle'],
    ];

    $fmt = function ($v, $f) {
        if (empty($v)) { return null; }
        try { return \Carbon\Carbon::parse($v)->format($f); } catch (\Throwable $e) { return (string) $v; }
    };

    $releaseDate = $fmt($reliefVoucher->release_date ?? null, 'F d, Y');
    $releaseTime = $fmt($reliefVoucher->release_time ?? null, 'h:i A');
    $qty = $reliefVoucher->allocated_quantity ?? 1;
    $unit = $operation?->quantity_unit;

    /*
     * Locked / editable state comes from what actually happened to the
     * voucher (see ReliefVoucher model), NOT from whether it was printed.
     */
    $isFinal          = $reliefVoucher->isFinal();
    $canEdit          = $reliefVoucher->canBeEdited();
    $canIssue         = $reliefVoucher->canBeIssued();
    $canReturn        = $reliefVoucher->canBeReturnedToGenerated();
    $canCancel        = $reliefVoucher->canBeCancelled();
    $canPrint         = $reliefVoucher->canBePrinted();
    $waitingForScan   = in_array($status, ['issued', 'distributed']) && !$reliefVoucher->qr_verified_at;

    $beneficiaryName  = $reliefVoucher->beneficiary?->resident?->full_name;

    /*
     * "Back" follows the processing journey:
     * voucher details -> the operation's voucher list (bulk voucher preview).
     * Falls back to the overview when the voucher has no operation.
     */
    $backUrl   = $operation
        ? route('relief-assistance.beneficiaries.print', ['operation' => $operation->id])
        : route('relief-vouchers.index');
    $backLabel = $operation ? 'Back to voucher list' : 'Overview';
@endphp

<style>
    .rvs-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; flex-wrap: wrap; margin-bottom: 20px; }
    .rvs-crumb { font-size: 13px; color: var(--rv-ink2); margin-bottom: 8px; }
    .rvs-crumb a { color: var(--rv-ink2); text-decoration: none; }
    .rvs-crumb a:hover { color: var(--rv-ube); }
    .rvs-head h1 { font-size: 26px; font-weight: 800; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .rvs-head p { margin-top: 6px; font-size: 14px; color: var(--rv-ink2); }
    .rvs-actions { display: flex; gap: 8px; flex-wrap: wrap; }

    /* Status timeline */
    .rvs-flow { display: grid; grid-template-columns: repeat(4, 1fr); gap: 0; padding: 22px; }
    .rvs-flow li { position: relative; padding-right: 14px; }
    .rvs-flow li::before { content: ""; position: absolute; top: 19px; left: 48px; right: 0; height: 2px; background: var(--rv-line); }
    .rvs-flow li:last-child::before { display: none; }
    .rvs-flow li.is-done::before { background: var(--rv-green); }
    .rvs-dot { position: relative; z-index: 1; width: 38px; height: 38px; border-radius: 50%; display: grid; place-items: center; font-size: 17px;
        background: var(--rv-grey-soft); color: var(--rv-grey); border: 2px solid var(--rv-card); }
    .rvs-flow li.is-done .rvs-dot { background: var(--rv-green); color: #fff; }
    .rvs-flow li.is-now .rvs-dot { background: var(--rv-btn); color: #fff; box-shadow: 0 0 0 5px var(--rv-ube-soft); }
    .rvs-flow.is-cancelled .rvs-dot { background: var(--rv-red-soft); color: var(--rv-red); }
    .rvs-flow b { display: block; margin-top: 10px; font-size: 14px; }
    .rvs-flow span { display: block; margin-top: 2px; font-size: 12px; color: var(--rv-ink2); line-height: 1.45; }
    .rvs-flow small { display: block; margin-top: 4px; font-size: 11.5px; color: var(--rv-green); font-weight: 600; }
    .rvs-msg { display: flex; align-items: center; gap: 10px; padding: 13px 22px; border-top: 1px solid var(--rv-line); background: var(--rv-wash); font-size: 13.5px; color: var(--rv-ink2); }

    /* Detail cards */
    .rvs-card { margin-bottom: 18px; }
    .rvs-card > header { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 18px 22px 0; }
    .rvs-card h2 { font-size: 16px; font-weight: 700; }
    .rvs-card header p { font-size: 12.5px; color: var(--rv-ink2); margin-top: 2px; }
    .rvs-card header i { font-size: 20px; color: var(--rv-ube); }
    .rvs-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 18px 24px; padding: 18px 22px 22px; }
    .rvs-grid.three { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .rvs-f dt { font-size: 12px; color: var(--rv-ink2); margin-bottom: 3px; font-weight: 600; }
    .rvs-f dd { font-size: 14.5px; font-weight: 600; word-break: break-word; }
    .rvs-f.big dd { font-size: 19px; font-weight: 800; color: var(--rv-ube); letter-spacing: .02em; }
    .rvs-ok { color: var(--rv-green); } .rvs-wait { color: var(--rv-mango-ink); }

    /* Ticket */
    .rvs-ticket { text-align: center; padding: 24px 22px; position: sticky; top: 84px; }
    .rvs-qr-wrap { position: relative; width: 224px; height: 224px; margin: 0 auto 14px; }
    .rvs-qr { width: 100%; height: 100%; padding: 8px; background: #fff; border-radius: 16px; border: 1px solid var(--rv-line); }
    .rvs-qr-wrap.is-void .rvs-qr svg { opacity: .12; filter: grayscale(1); }
    .rvs-stamp { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; pointer-events: none; }
    .rvs-stamp span { transform: rotate(-14deg); padding: 6px 16px; border: 3px solid currentColor; border-radius: 10px; font: 800 20px 'Unbounded', sans-serif; letter-spacing: .08em; background: rgba(255,255,255,.9); }
    .rvs-code { display: inline-flex; align-items: center; gap: 4px; font: 700 15px ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing: .04em; word-break: break-all; }
    .rvs-ticket .hint { margin-top: 8px; font-size: 12.5px; color: var(--rv-ink2); }
    .rvs-actions-box { margin-top: 18px; padding-top: 18px; border-top: 1px dashed var(--rv-line); display: grid; gap: 10px; text-align: left; }
    .rvs-note { padding: 12px 14px; border-radius: 12px; font-size: 13px; line-height: 1.5; }
    .rvs-note.ok { background: var(--rv-green-soft); color: var(--rv-green); }
    .rvs-note.bad { background: var(--rv-red-soft); color: var(--rv-red); }

    .rvs-members th, .rvs-members td { padding: 10px 22px; }
    .rvs-members { min-width: 0; }

    /* Confirm dialog */
    dialog.rvs-dialog { border: 1px solid var(--rv-line); border-radius: 20px; background: var(--rv-card); color: var(--rv-ink); padding: 26px; width: calc(100% - 32px); max-width: 420px; box-shadow: 0 30px 80px rgba(0,0,0,.35); }
    dialog.rvs-dialog::backdrop { background: rgba(10, 10, 25, .55); backdrop-filter: blur(3px); }
    dialog.rvs-dialog h3 { font: 700 17px 'Figtree', sans-serif; margin: 0 0 8px; }
    dialog.rvs-dialog p { margin: 0 0 20px; font-size: 14px; line-height: 1.55; color: var(--rv-ink2); }
    .rvs-dialog-actions { display: flex; gap: 8px; justify-content: flex-end; }

    @media (max-width: 991.98px) { .rvs-ticket { position: static; } }
    @media (max-width: 767.98px) {
        .rvs-flow { grid-template-columns: 1fr; gap: 18px; }
        .rvs-flow li { padding: 0 0 0 52px; min-height: 40px; }
        .rvs-flow li::before { top: 40px; bottom: -18px; left: 18px; right: auto; width: 2px; height: auto; }
        .rvs-dot { position: absolute; left: 0; top: 0; }
        .rvs-flow b { margin-top: 0; }
        .rvs-grid, .rvs-grid.three { grid-template-columns: 1fr; }
    }
</style>

<div class="container-fluid py-4 rv-page">

    @include('relief-vouchers.partials.nav', ['active' => 'voucher'])

    {{-- ============ HEADER ============ --}}
    <header class="rvs-head">
        <div>
            <div class="rvs-crumb">
                <a href="{{ route('relief-vouchers.index') }}">Relief Assistance</a>
                @if ($operation)
                    / <a href="{{ route('relief-assistance.show', ['operation' => $operation->id]) }}">{{ $operation->operation_code ?: 'Operation' }}</a>
                @endif
                / Voucher
            </div>
            <h1 class="rv-display">
                {{ $reliefVoucher->voucher_code }}
                <span class="rv-badge {{ $tone }}" style="font-family:'Figtree',sans-serif;letter-spacing:0">{{ $label }}</span>
            </h1>
            <p>Voucher record, beneficiary, and QR verification status.</p>
        </div>

        <div class="rvs-actions">
            <a href="{{ $backUrl }}" class="rv-btn rv-btn-ghost"><i class="bi bi-arrow-left"></i> {{ $backLabel }}</a>
            @if ($canEdit)
                <a href="{{ route('relief-vouchers.edit', $reliefVoucher) }}" class="rv-btn rv-btn-ghost"><i class="bi bi-pencil-square"></i> Edit voucher</a>
            @endif
            @if ($canPrint)
                <a href="{{ route('relief-vouchers.print', $reliefVoucher) }}" target="_blank" rel="noopener" class="rv-btn"><i class="bi bi-printer"></i> Print voucher</a>
            @endif
        </div>
    </header>

    {{-- ============ PROGRESS ============ --}}
    <section class="rv-panel rvs-card" aria-label="Voucher progress">
        <ol class="rvs-flow {{ $status === 'cancelled' ? 'is-cancelled' : '' }}">
            @foreach ($steps as $i => $s)
                @php
                    $done = $status !== 'cancelled' && ($i < $stage || $stage === 3);
                    $now  = $status !== 'cancelled' && $i === $stage && $stage < 3;
                    $when = match ($i) {
                        2 => $fmt($reliefVoucher->qr_verified_at, 'M d, h:i A'),
                        3 => $fmt($reliefVoucher->claimed_at, 'M d, h:i A'),
                        default => null,
                    };
                @endphp
                <li class="{{ $done ? 'is-done' : '' }} {{ $now ? 'is-now' : '' }}">
                    <div class="rvs-dot"><i class="bi {{ $status === 'cancelled' ? 'bi-dash' : ($done ? 'bi-check-lg' : $s[2]) }}"></i></div>
                    <b>{{ $s[0] }}</b>
                    <span>{{ $s[1] }}</span>
                    @if ($when && $done) <small>{{ $when }}</small> @endif
                </li>
            @endforeach
        </ol>
        <div class="rvs-msg">
            <i class="bi {{ $status === 'cancelled' ? 'bi-x-circle-fill' : ($status === 'claimed' ? 'bi-check-circle-fill' : 'bi-info-circle') }}"
               style="color: var(--rv-{{ $status === 'cancelled' ? 'red' : ($status === 'claimed' ? 'green' : 'ube') }})"></i>
            {{ $messages[$status] ?? $messages['pending'] }}
        </div>
    </section>

    <div class="row g-4">

        {{-- ============ LEFT: DETAILS ============ --}}
        <div class="col-xl-8">

            <section class="rv-panel rvs-card">
                <header>
                    <div><h2>Voucher information</h2><p>Official relief assistance voucher record.</p></div>
                    <i class="bi bi-ticket-perforated"></i>
                </header>
                <dl class="rvs-grid">
                    <div class="rvs-f big"><dt>Voucher code</dt><dd>{{ $reliefVoucher->voucher_code }}</dd></div>
                    <div class="rvs-f"><dt>Status</dt><dd><span class="rv-badge {{ $tone }}">{{ $label }}</span></dd></div>
                    <div class="rvs-f"><dt>Beneficiary</dt><dd>{{ $beneficiaryName ?: '—' }}</dd></div>
                    <div class="rvs-f"><dt>Assistance type</dt><dd>{{ $reliefVoucher->assistance_type ?: ($operation?->assistance_type ?: 'Relief Assistance') }}</dd></div>
                    <div class="rvs-f"><dt>Allocated quantity</dt><dd>{{ rtrim(rtrim(number_format((float) $qty, 2), '0'), '.') }} {{ $unit }}</dd></div>
                </dl>
            </section>

            @if ($operation)
                <section class="rv-panel rvs-card">
                    <header>
                        <div><h2>Relief operation</h2><p>The operation this voucher belongs to.</p></div>
                        <i class="bi bi-collection"></i>
                    </header>
                    <dl class="rvs-grid">
                        <div class="rvs-f"><dt>Operation name</dt><dd>{{ $operation->operation_name }}</dd></div>
                        <div class="rvs-f"><dt>Operation code</dt><dd>{{ $operation->operation_code ?: '—' }}</dd></div>
                        <div class="rvs-f"><dt>Assistance type</dt><dd>{{ $operation->assistance_type ?: '—' }}</dd></div>
                        <div class="rvs-f"><dt>Operation status</dt><dd>{{ ucfirst($operation->status ?? '—') }}</dd></div>
                    </dl>
                    <div style="padding: 0 22px 20px">
                        <a href="{{ route('relief-assistance.show', ['operation' => $operation->id]) }}" class="rv-btn rv-btn-ghost rv-btn-sm">Open operation <i class="bi bi-arrow-right-short"></i></a>
                    </div>
                </section>
            @endif

            @if ($household)
                <section class="rv-panel rvs-card">
                    <header>
                        <div><h2>Beneficiary household</h2><p>Household record linked to this voucher.</p></div>
                        <i class="bi bi-house-heart"></i>
                    </header>
                    <dl class="rvs-grid">
                        <div class="rvs-f"><dt>Household number</dt><dd>{{ $household->household_number ?: '—' }}</dd></div>
                        <div class="rvs-f"><dt>Household head</dt><dd>{{ $head?->full_name ?: '—' }}</dd></div>
                        <div class="rvs-f"><dt>Purok</dt><dd>{{ $household->purok ?: '—' }}</dd></div>
                        <div class="rvs-f"><dt>Address</dt><dd>{{ $household->address ?: '—' }}</dd></div>
                    </dl>

                    @if ($household->members && $household->members->count())
                        <div style="border-top: 1px solid var(--rv-line)">
                            <div style="padding: 16px 22px 8px; font-weight: 700; font-size: 14px">
                                Household members <span class="rv-muted">({{ $household->members->count() }})</span>
                            </div>
                            <div class="rv-scroll">
                                <table class="rv-table rvs-members">
                                    <thead><tr><th style="width:60px">#</th><th>Resident</th><th>Relationship</th></tr></thead>
                                    <tbody>
                                        @foreach ($household->members as $index => $member)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td class="rv-strong">{{ $member->resident?->full_name ?: '—' }}</td>
                                                <td>{{ $member->relationship ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif
                </section>
            @endif

            <section class="rv-panel rvs-card">
                <header>
                    <div><h2>Distribution</h2><p>Scheduled release details.</p></div>
                    <i class="bi bi-geo-alt"></i>
                </header>
                <dl class="rvs-grid three">
                    <div class="rvs-f"><dt>Release date</dt><dd>{{ $releaseDate ?: '—' }}</dd></div>
                    <div class="rvs-f"><dt>Release time</dt><dd>{{ $releaseTime ?: '—' }}</dd></div>
                    <div class="rvs-f"><dt>Release location</dt><dd>{{ $reliefVoucher->release_location ?: '—' }}</dd></div>
                </dl>
            </section>

            <section class="rv-panel rvs-card">
                <header>
                    <div><h2>Verification and claim</h2><p>System record of QR verification and automatic claiming.</p></div>
                    <i class="bi bi-shield-check"></i>
                </header>
                <dl class="rvs-grid">
                    <div class="rvs-f">
                        <dt>QR verification</dt>
                        <dd>
                            @if ($reliefVoucher->qr_verified_at)
                                <span class="rvs-ok"><i class="bi bi-check-circle-fill me-1"></i>Verified</span>
                                <div class="rv-muted">{{ $fmt($reliefVoucher->qr_verified_at, 'F d, Y h:i A') }}</div>
                            @else
                                <span class="rvs-wait"><i class="bi bi-clock me-1"></i>Not yet verified</span>
                            @endif
                        </dd>
                    </div>
                    <div class="rvs-f">
                        <dt>Claim status</dt>
                        <dd>
                            @if ($reliefVoucher->claimed_at)
                                <span class="rvs-ok"><i class="bi bi-check-circle-fill me-1"></i>Claimed</span>
                                <div class="rv-muted">{{ $fmt($reliefVoucher->claimed_at, 'F d, Y h:i A') }}</div>
                            @else
                                <span class="rv-muted" style="font-size:14.5px;font-weight:600"><i class="bi bi-hourglass-split me-1"></i>Not yet claimed</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>
        </div>

        {{-- ============ RIGHT: TICKET + ACTIONS ============ --}}
        <div class="col-xl-4">
            <aside class="rv-panel rvs-ticket">

                <div class="rvs-qr-wrap {{ $isFinal ? 'is-void' : '' }}">
                    <div class="rvs-qr" data-qr="{{ $reliefVoucher->voucher_code }}"></div>
                    @if ($isFinal)
                        <div class="rvs-stamp" style="color: var(--rv-{{ $status === 'cancelled' ? 'red' : 'green' }})">
                            <span>{{ $status === 'cancelled' ? 'CANCELLED' : 'CLAIMED' }}</span>
                        </div>
                    @endif
                </div>

                <div class="rvs-code">
                    {{ $reliefVoucher->voucher_code }}
                    <button type="button" class="rv-copy" data-copy="{{ $reliefVoucher->voucher_code }}" aria-label="Copy voucher code"><i class="bi bi-clipboard"></i></button>
                </div>
                <div class="hint">
                    @if ($status === 'cancelled') This voucher is no longer valid.
                    @elseif ($isFinal) Verified and claimed.
                    @else Scan this QR at the distribution table. @endif
                </div>

                <div class="rvs-actions-box">
                    @if ($status === 'cancelled')
                        <div class="rvs-note bad"><b>Voucher cancelled.</b> It cannot be claimed or edited.</div>

                    @elseif ($isFinal)
                        <div class="rvs-note ok"><b>Voucher completed.</b> No further action is needed.</div>

                    @else
                        @if ($canPrint)
                            <a href="{{ route('relief-vouchers.print', $reliefVoucher) }}" target="_blank" rel="noopener" class="rv-btn rv-btn-block">
                                <i class="bi bi-printer"></i> Print voucher
                            </a>
                        @endif

                        @if ($canEdit)
                            <a href="{{ route('relief-vouchers.edit', $reliefVoucher) }}" class="rv-btn rv-btn-ghost rv-btn-block">
                                <i class="bi bi-pencil-square"></i> Edit / correct voucher
                            </a>
                        @endif

                        @if ($canIssue)
                            <form method="POST" action="{{ route('relief-vouchers.issue', $reliefVoucher) }}"
                                  data-confirm-title="Issue this voucher?"
                                  data-confirm-text="Once issued, it is ready for QR verification at distribution. You can still correct it until it is scanned."
                                  data-confirm-button="Issue voucher">
                                @csrf @method('PATCH')
                                <button type="submit" class="rv-btn rv-btn-warn rv-btn-block"><i class="bi bi-send-check"></i> Issue voucher</button>
                            </form>
                        @endif

                        @if ($canReturn)
                            <form method="POST" action="{{ route('relief-vouchers.return', $reliefVoucher) }}"
                                  data-confirm-title="Return to Generated?"
                                  data-confirm-text="The voucher goes back to Generated so you can correct it. Issue it again when it is ready."
                                  data-confirm-button="Return to Generated">
                                @csrf @method('PATCH')
                                <button type="submit" class="rv-btn rv-btn-ghost rv-btn-block"><i class="bi bi-arrow-counterclockwise"></i> Return to Generated</button>
                            </form>
                        @endif

                        @if ($waitingForScan)
                            <a href="{{ route('relief-vouchers.qr-verification') }}" class="rv-btn rv-btn-ghost rv-btn-block">
                                <i class="bi bi-qr-code-scan"></i> Open scanner
                            </a>
                        @elseif ($reliefVoucher->qr_verified_at)
                            <div class="rvs-note ok"><b>QR verified.</b> The system records the voucher as claimed automatically.</div>
                        @endif

                        @if ($canCancel)
                            <form method="POST" action="{{ route('relief-vouchers.cancel', $reliefVoucher) }}"
                                  data-confirm-title="Cancel this voucher?"
                                  data-confirm-text="A cancelled voucher can no longer be claimed. The beneficiary can be given a new voucher afterwards."
                                  data-confirm-button="Cancel voucher" data-confirm-danger="1">
                                @csrf @method('PATCH')
                                <button type="submit" class="rv-btn rv-btn-danger rv-btn-block"><i class="bi bi-x-circle"></i> Cancel voucher</button>
                            </form>
                        @endif
                    @endif
                </div>
            </aside>
        </div>
    </div>
</div>

{{-- Confirm dialog (no browser confirm() popups) --}}
<dialog class="rvs-dialog" id="rvsDialog" aria-labelledby="rvsDialogTitle">
    <h3 id="rvsDialogTitle"></h3>
    <p id="rvsDialogText"></p>
    <div class="rvs-dialog-actions">
        <button type="button" class="rv-btn rv-btn-ghost rv-btn-sm" id="rvsNo">Go back</button>
        <button type="button" class="rv-btn rv-btn-sm" id="rvsYes">Confirm</button>
    </div>
</dialog>

@include('relief-vouchers.partials.qr-script')

<script>
(function () {
    var dlg = document.getElementById('rvsDialog'), pending = null;
    if (!dlg || typeof dlg.showModal !== 'function') return; // old browsers: forms submit normally

    document.querySelectorAll('form[data-confirm-title]').forEach(function (f) {
        f.addEventListener('submit', function (e) {
            if (f.dataset.ok === '1') return;
            e.preventDefault();
            pending = f;
            document.getElementById('rvsDialogTitle').textContent = f.dataset.confirmTitle;
            document.getElementById('rvsDialogText').textContent  = f.dataset.confirmText || '';
            var yes = document.getElementById('rvsYes');
            yes.textContent = f.dataset.confirmButton || 'Confirm';
            yes.className = 'rv-btn rv-btn-sm' + (f.dataset.confirmDanger ? ' rv-btn-danger' : '');
            dlg.showModal();
        });
    });

    document.getElementById('rvsNo').addEventListener('click', function () { dlg.close(); pending = null; });
    document.getElementById('rvsYes').addEventListener('click', function () {
        if (!pending) return;
        pending.dataset.ok = '1';
        dlg.close();
        pending.submit();
    });
    dlg.addEventListener('click', function (e) { if (e.target === dlg) { dlg.close(); pending = null; } });
})();
</script>

@endsection