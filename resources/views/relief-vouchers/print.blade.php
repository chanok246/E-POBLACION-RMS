{{-- resources/views/relief-vouchers/print.blade.php --}}

@extends('layouts.app')

@section('title', 'Print Relief Voucher | e-Poblacion')

@section('content')

@php
    $reliefVoucher = $reliefVoucher ?? null;
    if (!$reliefVoucher) { abort(404); }

    $operation = $reliefVoucher->operation ?? null;
    $household = $reliefVoucher->household ?? null;
    $head      = $household?->householdHead ?? null;

    $code       = $reliefVoucher->voucher_code ?? '—';
    $opName     = $operation?->operation_name ?? 'Relief Assistance Operation';
    $opCode     = $operation?->operation_code ?? '—';
    $assistance = $reliefVoucher->assistance_type ?? $operation?->assistance_type ?? 'Relief Assistance';
    $qty        = $reliefVoucher->allocated_quantity;
    $status     = strtolower((string) ($reliefVoucher->status ?? 'pending'));

    $hhNo    = $household?->household_number ?? '—';
    $purok   = $household?->purok ?? $household?->purok_name ?? '—';
    $address = $household?->address ?? '—';
    $headName = $head?->full_name ?? '—';

    $fmt = function ($v, $f) {
        if (empty($v)) { return null; }
        try { return \Illuminate\Support\Carbon::parse($v)->format($f); } catch (\Throwable $e) { return (string) $v; }
    };

    $dateRaw = $reliefVoucher->release_date ?? $reliefVoucher->distribution_schedule ?? $operation?->distribution_date;
    $timeRaw = $reliefVoucher->release_time ?? $operation?->distribution_time;
    $relDate = $fmt($dateRaw, 'F d, Y') ?? '—';
    $relTime = $fmt($timeRaw, 'h:i A') ?? '—';
    $relPlace = $reliefVoucher->release_location ?? $operation?->distribution_location ?? 'Barangay Hall';

    $qtyText = ($qty !== null && $qty !== '')
        ? rtrim(rtrim(number_format((float) $qty, 2), '0'), '.') . ($operation?->quantity_unit ? ' ' . $operation->quantity_unit : '')
        : '—';

    $statusLabel = ucfirst(str_replace('_', ' ', $status));

    /*
     * Logos are embedded as base64 from /public so they always render
     * on screen, in Chrome print, and in PDF - no URL / path issues.
     */
    $logoData = function (string $file) {
        $path = public_path($file);

        if (!is_file($path)) {
            return asset($file);
        }

        $mime = @mime_content_type($path) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };

    $sealSrc = $logoData('icon.png');
    $baniSrc = $logoData('Bani.png');
@endphp

<style>
    @page { size: 210mm 297mm; margin: 0; }

    /* ---------- Screen chrome ---------- */
    .rvp-toolbar { max-width: 210mm; margin: 0 auto 16px; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; }
    .rvp-toolbar h1 { font-size: 19px; font-weight: 800; margin: 0; }
    .rvp-toolbar p { margin: 2px 0 0; font-size: 13px; color: var(--rv-ink2); }
    .rvp-toolbar .row { display: flex; gap: 8px; flex-wrap: wrap; }
    .rvp-stage { overflow-x: auto; padding: 4px 0 28px; }
    #rvpPrint { width: 210mm; margin: 0 auto; }

    /* ---------- Paper (always light, whatever the app theme) ---------- */
    .rvp-sheet {
        --ink: #14181f; --muted: #4f5666; --navy: #1d2a44; --hair: #b9bfca; --tint: #f3f5f8;
        position: relative; width: 210mm; height: 296.5mm; padding: 10mm 14mm 0; overflow: hidden;
        background: #fff; color: var(--ink); font-family: Cambria, Georgia, 'Times New Roman', serif; font-size: 11px; line-height: 1.35;
        box-shadow: 0 6px 30px rgba(0, 0, 0, .25); box-sizing: border-box;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .rvp-sheet *, .rvp-sheet *::before, .rvp-sheet *::after { box-sizing: border-box; }
    .rvp-content { height: calc(296.5mm - 10mm - 13mm); overflow: hidden; }
    .rvp-fit { transform-origin: top left; width: 100%; }

    .lh { display: grid; grid-template-columns: 28mm 1fr 28mm; align-items: center; column-gap: 4mm; }
    /* Perfect circle: fixed square box + 50% radius + clip, so any square
       background/halo baked into the PNG is cropped away. */
    .lh .seal {
        width: 28mm; height: 28mm; aspect-ratio: 1 / 1; flex: none;
        display: block; position: relative; overflow: hidden;
        border-radius: 50%; background: transparent;
        justify-self: center;
    }
    .lh .seal img {
        position: absolute; inset: 0; display: block; width: 100%; height: 100%;
        border-radius: 50%; object-fit: cover; object-position: center;
        transform: scale(1.04);
        /* keep the seals sharp: no filters/shadows, best-quality scaling */
        filter: none; box-shadow: none; background: transparent;
        image-rendering: -webkit-optimize-contrast; image-rendering: high-quality;
        -webkit-print-color-adjust: exact; print-color-adjust: exact;
    }
    .lh .mid { text-align: center; line-height: 1.2; }
    .lh .a { font-size: 10px; } .lh .b { font-size: 11px; font-weight: 700; }
    .lh .c { font-size: 15px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .lh .d { font-size: 10px; font-style: italic; color: var(--muted); margin-top: 1px; }
    .rule { margin: 3mm 0 0; height: 4px; border-top: 2.2px solid var(--navy); border-bottom: .7px solid var(--navy); }

    .title { text-align: center; margin: 5mm 0 4mm; }
    .title h2 { margin: 0; font-size: 21px; font-weight: 700; letter-spacing: .2em; padding-left: .2em; text-transform: uppercase; color: var(--navy); }
    .title p { margin: 1mm 0 0; font-size: 10.5px; font-style: italic; color: var(--muted); }

    /* Main ticket */
    .ticket { display: grid; grid-template-columns: 1fr 56mm; border: 1.4px solid var(--navy); }
    .t-left { padding: 4mm 5mm; border-right: 1px dashed var(--navy); display: flex; flex-direction: column; justify-content: space-between; gap: 3mm; }
    .t-code small, .f dt { display: block; font-size: 9px; font-weight: 700; color: var(--muted); }
    .t-code strong { display: block; font: 700 21px ui-monospace, Menlo, Consolas, monospace; letter-spacing: .05em; color: var(--navy); word-break: break-all; }
    .t-code .st { display: inline-block; margin-top: 1.5mm; padding: .6mm 3mm; border: 1px solid var(--navy); border-radius: 99px; font: 700 9.5px Arial, sans-serif; letter-spacing: .05em; text-transform: uppercase; }
    .t-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2.4mm 5mm; margin: 0; }
    .f { margin: 0; min-width: 0; } .f dd { margin: 0; font-size: 11.5px; font-weight: 700; overflow-wrap: anywhere; }
    .t-right { padding: 4mm; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; background: var(--tint); }
    .qrbox { width: 44mm; height: 44mm; padding: 1.5mm; background: #fff; border: 1px solid var(--hair); }
    .qrbox svg { display: block; }
    .t-right .cap { margin-top: 2mm; font: 700 9.5px Arial, sans-serif; letter-spacing: .08em; text-transform: uppercase; }
    .t-right .sub { margin-top: .8mm; font-size: 9px; color: var(--muted); line-height: 1.3; }

    .block { margin-top: 4mm; border: 1px solid var(--hair); break-inside: avoid; }
    .block > .bt { background: var(--navy); color: #fff; padding: 1.2mm 4mm; font: 700 9.5px Arial, sans-serif; letter-spacing: .12em; text-transform: uppercase; }
    .block .bg { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2.4mm 5mm; padding: 3mm 4mm; margin: 0; }
    .block .bg .wide { grid-column: span 3; }
    .block .bg .two { grid-column: span 2; }

    .notice { margin-top: 4mm; padding: 2.4mm 3.5mm; border-left: 3px solid var(--navy); background: var(--tint); font-size: 10px; line-height: 1.4; text-align: justify; }

    .sign { display: grid; grid-template-columns: 1fr 1fr; column-gap: 14mm; margin-top: 3mm; }
    .sig { text-align: center; padding-top: 11mm; }
    .sig .ln { border-top: 1px solid var(--ink); padding-top: 1mm; }
    .sig b { display: block; font-size: 11px; } .sig i { font-size: 9.5px; color: var(--muted); }

    /* Barangay stub */
    .cut { display: flex; align-items: center; gap: 3mm; margin: 5mm 0 3mm; font: 700 9px Arial, sans-serif; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }
    .cut::before, .cut::after { content: ""; flex: 1; border-top: 1.4px dashed var(--muted); }
    .stub { display: grid; grid-template-columns: 30mm 1fr 58mm; gap: 5mm; align-items: center; border: 1px solid var(--hair); padding: 3mm 4mm; break-inside: avoid; }
    .stub .qrbox { width: 28mm; height: 28mm; padding: 1mm; }
    .stub .mono { font: 700 13px ui-monospace, Menlo, Consolas, monospace; color: var(--navy); word-break: break-all; }
    .stub .line { border-bottom: 1px solid var(--ink); height: 8mm; }
    .stub .cap { font-size: 9px; color: var(--muted); text-align: center; margin-top: .8mm; }

    .foot { position: absolute; left: 14mm; right: 14mm; bottom: 6mm; display: flex; justify-content: space-between; gap: 6mm; padding-top: 1.6mm; border-top: .7px solid var(--hair); font: 7.5px Arial, sans-serif; color: var(--muted); }

    /* ---------- Print: exactly one A4 page ---------- */
    @media print {
        html, body { margin: 0 !important; padding: 0 !important; background: #fff !important; height: 296.5mm !important; overflow: hidden !important; }
        body > *:not(#rvpPrint) { display: none !important; }
        #rvpPrint { width: 210mm !important; margin: 0 !important; }
        .rvp-sheet { box-shadow: none !important; break-after: avoid; page-break-after: avoid; }
    }
</style>

<div class="container-fluid py-4 rv-page">

    <div class="rvp-toolbar no-print">
        <div>
            <h1 class="rv-display">Print voucher</h1>
            <p>Prints on one A4 page. The QR code holds the voucher code and works with the scanner.</p>
        </div>
        <div class="row">
            <a href="{{ route('relief-vouchers.show', $reliefVoucher) }}" class="rv-btn rv-btn-ghost"><i class="bi bi-arrow-left"></i> Back to voucher</a>
            <button type="button" class="rv-btn" onclick="window.print()"><i class="bi bi-printer"></i> Print voucher</button>
        </div>
    </div>

    <div class="rvp-stage no-print-scroll">
        <div id="rvpPrint">
            <div class="rvp-sheet">
                <div class="rvp-content">
                    <div class="rvp-fit">

                        <header class="lh">
                            <div class="seal"><img src="{{ $sealSrc }}" alt="Barangay Poblacion seal"></div>
                            <div class="mid">
                                <div class="a">Republic of the Philippines</div>
                                <div class="a">Province of Pangasinan</div>
                                <div class="b">Municipality of Bani</div>
                                <div class="c">Barangay Poblacion</div>
                                <div class="d">Office of the Punong Barangay</div>
                            </div>
                            <div class="seal"><img src="{{ $baniSrc }}" alt="Municipality of Bani seal"></div>
                        </header>
                        <div class="rule"></div>

                        <div class="title">
                            <h2>Relief Assistance Voucher</h2>
                            <p>Official Barangay Distribution Record</p>
                        </div>

                        {{-- Main ticket --}}
                        <section class="ticket">
                            <div class="t-left">
                                <div class="t-code">
                                    <small>Voucher code</small>
                                    <strong>{{ $code }}</strong>
                                    <span class="st">{{ $statusLabel }}</span>
                                </div>
                                <dl class="t-grid">
                                    <div class="f"><dt>Relief operation</dt><dd>{{ $opName }}</dd></div>
                                    <div class="f"><dt>Operation code</dt><dd>{{ $opCode }}</dd></div>
                                    <div class="f"><dt>Assistance type</dt><dd>{{ $assistance }}</dd></div>
                                    <div class="f"><dt>Allocated quantity</dt><dd>{{ $qtyText }}</dd></div>
                                </dl>
                            </div>
                            <div class="t-right">
                                <div class="qrbox" data-qr="{{ $code }}"></div>
                                <div class="cap">Scan to verify</div>
                                <div class="sub">Present this voucher at the distribution table.</div>
                            </div>
                        </section>

                        {{-- Household --}}
                        <section class="block">
                            <div class="bt">Beneficiary household</div>
                            <dl class="bg">
                                <div class="f"><dt>Household number</dt><dd>{{ $hhNo }}</dd></div>
                                <div class="f two"><dt>Household head</dt><dd>{{ $headName }}</dd></div>
                                <div class="f"><dt>Purok</dt><dd>{{ $purok }}</dd></div>
                                <div class="f two"><dt>Address</dt><dd>{{ $address }}</dd></div>
                            </dl>
                        </section>

                        {{-- Distribution --}}
                        <section class="block">
                            <div class="bt">Distribution</div>
                            <dl class="bg">
                                <div class="f"><dt>Date</dt><dd>{{ $relDate }}</dd></div>
                                <div class="f"><dt>Time</dt><dd>{{ $relTime }}</dd></div>
                                <div class="f"><dt>Location</dt><dd>{{ $relPlace }}</dd></div>
                            </dl>
                        </section>

                        <div class="notice">
                            <b>Important:</b> This voucher is issued for the relief operation named above. Claims are recorded by authorized Barangay personnel
                            after the QR code is verified. A voucher can be claimed once. Lost, altered, or copied vouchers will not be honored.
                        </div>

                        <div class="sign">
                            <div class="sig"><div class="ln"><b>Authorized Barangay Personnel</b><i>Issued / prepared by</i></div></div>
                            <div class="sig"><div class="ln"><b>Beneficiary / Recipient</b><i>Received by</i></div></div>
                        </div>

                        {{-- Stub --}}
                        <div class="cut">Barangay claim stub &middot; detach and keep</div>
                        <section class="stub">
                            <div class="qrbox" data-qr="{{ $code }}"></div>
                            <div>
                                <div style="font-size:9px;font-weight:700;color:var(--muted)">Voucher code</div>
                                <div class="mono">{{ $code }}</div>
                                <div style="margin-top:1.6mm;font-size:10.5px"><b>{{ $headName }}</b><br>Household {{ $hhNo }} &middot; Purok {{ $purok }}</div>
                                <div style="margin-top:1mm;font-size:10px;color:var(--muted)">{{ $assistance }} &middot; {{ $qtyText }}</div>
                            </div>
                            <div>
                                <div class="line"></div><div class="cap">Signature of recipient</div>
                                <div class="line" style="margin-top:2mm"></div><div class="cap">Date and time claimed</div>
                            </div>
                        </section>

                    </div>
                </div>

                <footer class="foot">
                    <span>Operation: <b>{{ $opCode }}</b></span>
                    <span>Voucher: <b>{{ $code }}</b></span>
                    <span>Printed: <b>{{ now()->format('F d, Y h:i A') }}</b></span>
                </footer>
            </div>
        </div>
    </div>
</div>

@include('relief-vouchers.partials.qr-script')
@include('relief-vouchers.partials.print-fit')

@endsection