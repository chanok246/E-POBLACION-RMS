@php

    /*

     \* ---------------------------------------------------------

     \* DATA PREPARATION (keeps the markup below clean)

     \* ---------------------------------------------------------

     */

    $rc = $reportsComplaint;



    $formatDate = function ($value) {

        if (empty($value)) {

            return null;

        }

        try {

            return \Carbon\Carbon::parse($value)->format('F j, Y');

        } catch (\Throwable $e) {

            return (string) $value;

        }

    };



    $formatTime = function ($value) {

        if (empty($value)) {

            return null;

        }

        try {

            return \Carbon\Carbon::parse($value)->format('g:i A');

        } catch (\Throwable $e) {

            return (string) $value;

        }

    };



    $issuedDate = $formatDate($rc->summons_printed_at ?? null)
        ?? $formatDate($rc->created_at ?? null)
        ?? now()->format('F j, Y');



    $complainantName = $rc->complainant_name ?: 'N/A';



    $respondentName = $rc->subject_name ?: 'N/A';



    $hearingDate  = $formatDate($rc->hearing_date ?? null);

    $hearingTime  = $formatTime($rc->hearing_time ?? null);

    $hearingPlace = $rc->hearing_location ?? 'Barangay Hall, Barangay Poblacion, Bani, Pangasinan';



    $subject = $rc->title
        ?: $rc->category
        ?: $rc->report_type
        ?: 'Barangay Complaint';



    $description = !empty($rc->description)

        ? \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', $rc->description)), 260)

        : null;



    /* Witnesses: text (one per line) or array/collection */

    $witnessList = [];

    $rawWitnesses = $rc->witnesses ?? null;



    if (is_string($rawWitnesses)) {

        $witnessList = preg_split('/\r\n|\r|\n/', $rawWitnesses, -1, PREG_SPLIT_NO_EMPTY);

    } elseif (is_iterable($rawWitnesses)) {

        foreach ($rawWitnesses as $w) {

            $witnessList[] = is_object($w)

                ? ($w->full_name ?? $w->name ?? 'Unnamed Witness')

                : (string) $w;

        }

    }



    $witnessList = array_values(array_filter(array_map('trim', $witnessList)));

    $witnessExtra = max(0, count($witnessList) - 6);

    $witnessList  = array_slice($witnessList, 0, 6);



    $captainName   = isset($captain) ? $captain->name : (isset($punongBarangay) ? $punongBarangay->name : null);

    $secretaryName = isset($secretary) ? $secretary->name : null;

@endphp

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Subpoena - {{ $rc->record_code }}</title>



    <style>

        /* =========================================================

           PAGE SETUP – selected paper size, one page only

           ========================================================= */

        @page {
            /* Follow the paper selected in the browser/printer dialog. */
            size: auto;
            margin: 0;
        }



        :root {

            --ink: #14181f;

            --muted: #4a5260;

            --rule: #1d2a44;      /* deep navy for rules */

            --hair: #b9bfca;

            --paper: #ffffff;

            --page-w: 210mm;

            --page-h: 297mm;

            --pad-x: 15mm;

            --pad-top: 10mm;

            --foot-h: 14mm;       /* space reserved for the footer */

        }



        * { box-sizing: border-box; }



        html, body {

            margin: 0;

            padding: 0;

            background: #dfe3e8;

            color: var(--ink);

            font-family: Cambria, Georgia, "Times New Roman", Times, serif;

            -webkit-print-color-adjust: exact;

            print-color-adjust: exact;

        }



        body {

            font-size: 11px;

            line-height: 1.35;

        }



        /* =========================================================

           SCREEN TOOLBAR

           ========================================================= */

        .toolbar {

            width: var(--page-w);

            max-width: 100%;

            margin: 14px auto 10px;

            display: flex;

            justify-content: flex-end;

            gap: 8px;

            font-family: Arial, Helvetica, sans-serif;

        }



        .toolbar a,

        .toolbar button {

            border: 0;

            border-radius: 4px;

            padding: 8px 16px;

            font-size: 13px;

            text-decoration: none;

            cursor: pointer;

            color: #fff;

        }



        .btn-back  { background: #6b7280; }

        .btn-print { background: #1d2a44; }



        .toolbar a:focus-visible,

        .toolbar button:focus-visible {

            outline: 2px solid #0d6efd;

            outline-offset: 2px;

        }



        /* =========================================================

           SHEET

           ========================================================= */

        .sheet {

            position: relative;

            width: var(--page-w);

            height: var(--page-h);

            margin: 0 auto 24px;

            padding: var(--pad-top) var(--pad-x) 0;

            background: var(--paper);

            overflow: hidden;

            box-shadow: 0 2px 12px rgba(0, 0, 0, .2);

        }



        /* Fixed-height content window. The script scales .fit down

           if (and only if) the content is taller than this window. */

        .content {

            position: relative;

            height: calc(var(--page-h) - var(--pad-top) - var(--foot-h));

            overflow: hidden;

        }



        .fit {

            transform-origin: top left;

            width: 100%;

        }



        /* =========================================================

           LETTERHEAD

           ========================================================= */

        .letterhead {

            display: grid;

            grid-template-columns: 24mm 1fr 24mm;

            align-items: center;

            column-gap: 4mm;

        }



        .seal {

            width: 24mm;

            height: 24mm;

            display: flex;

            align-items: center;

            justify-content: center;

        }



        .seal img {

            width: 100%;

            height: 100%;

            object-fit: contain;

            display: block;

        }



        .lh-center { text-align: center; line-height: 1.2; }

        .lh-republic { font-size: 10.5px; }

        .lh-province { font-size: 10.5px; }

        .lh-town     { font-size: 12px; font-weight: 700; }

        .lh-brgy     { font-size: 15px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; margin-top: 1px; }

        .lh-office   { font-size: 10px; font-style: italic; margin-top: 2px; color: var(--muted); }



        .lh-rule {

            margin: 3.5mm 0 0;

            border-top: 2.2px solid var(--rule);

            border-bottom: .7px solid var(--rule);

            height: 4px;

        }



        /* =========================================================

           TITLE + CASE BLOCK

           ========================================================= */

        .title-block {

            text-align: center;

            margin: 5mm 0 4mm;

        }



        .doc-title {

            font-size: 24px;

            font-weight: 700;

            letter-spacing: .28em;

            padding-left: .28em;          /* optical centering */

            text-transform: uppercase;

            color: var(--rule);

            margin: 0;

        }



        .doc-sub {

            margin-top: 1mm;

            font-size: 10.5px;

            font-style: italic;

            color: var(--muted);

        }



        .case-head {

            display: grid;

            grid-template-columns: 1fr 58mm;

            column-gap: 6mm;

            align-items: stretch;

            margin-bottom: 4mm;

        }



        /* Caption of parties, formatted like a case caption */

        .caption {

            border-right: 1px solid var(--ink);

            padding-right: 5mm;

        }



        .caption .party {

            font-weight: 700;

            text-transform: uppercase;

            font-size: 12px;

            line-height: 1.25;

            overflow-wrap: anywhere;

        }



        .caption .role {

            font-style: italic;

            font-size: 10px;

            text-align: right;

            color: var(--muted);

            margin: 0 0 1.5mm;

        }



        .caption .vs {

            text-align: center;

            font-style: italic;

            font-size: 10.5px;

            margin: 1.5mm 0;

        }



        .case-meta { font-size: 10.5px; }



        .case-meta dl { margin: 0; }

        .case-meta dt { font-weight: 700; font-size: 9.5px; color: var(--muted); margin-top: 2mm; }

        .case-meta dt:first-child { margin-top: 0; }

        .case-meta dd { margin: 0; font-size: 11px; overflow-wrap: anywhere; }



        /* =========================================================

           BODY TEXT

           ========================================================= */

        .body p {

            margin: 0 0 2.6mm;

            text-align: justify;

            text-indent: 10mm;

            line-height: 1.45;

            hyphens: auto;

        }



        .body p.to {

            text-indent: 0;

        }



        /* =========================================================

           HEARING DETAILS

           ========================================================= */

        .hearing {

            border: 1.2px solid var(--rule);

            margin: 3.5mm 0;

            break-inside: avoid;

        }



        .hearing-title {

            background: var(--rule);

            color: #fff;

            text-align: center;

            font-weight: 700;

            font-size: 10.5px;

            letter-spacing: .14em;

            text-transform: uppercase;

            padding: 1.4mm 0;

        }



        .hearing-grid {

            display: grid;

            grid-template-columns: 1fr 1fr;

        }



        .hcell {

            padding: 2mm 3.5mm;

            border-top: .7px solid var(--hair);

            min-width: 0;

        }



        .hcell:nth-child(-n+2) { border-top: 0; }

        .hcell:nth-child(odd)  { border-right: .7px solid var(--hair); }

        .hcell.wide { grid-column: 1 / -1; border-right: 0; }



        .hlabel { font-size: 9px; color: var(--muted); font-weight: 700; }

        .hvalue { font-size: 12px; font-weight: 700; overflow-wrap: anywhere; }

        .blank  { display: inline-block; min-width: 45mm; border-bottom: 1px solid var(--ink); height: 1em; }



        /* =========================================================

           SUBJECT / WITNESSES

           ========================================================= */

        .subject { margin: 0 0 3mm; }

        .subject .row { margin: 0 0 1.2mm; text-align: justify; }

        .subject b { font-weight: 700; }



        .witnesses { margin: 0 0 3mm; }

        .witnesses .w-title { font-weight: 700; margin-bottom: .8mm; }

        .witnesses ol {

            margin: 0;

            padding-left: 6mm;

            columns: 2;

            column-gap: 8mm;

        }

        .witnesses li { margin-bottom: .4mm; break-inside: avoid; }

        .witnesses .more { font-size: 9.5px; font-style: italic; color: var(--muted); margin-top: .6mm; }



        /* =========================================================

           NOTICE

           ========================================================= */

        .notice {

            border-left: 3px solid var(--rule);

            background: #f3f5f8;

            padding: 2.4mm 3.5mm;

            font-size: 10.3px;

            line-height: 1.4;

            text-align: justify;

            margin: 0 0 3.5mm;

            break-inside: avoid;

        }



        .notice b { text-transform: uppercase; letter-spacing: .06em; }



        .issued { margin: 0 0 1mm; text-align: left; }



        /* =========================================================

           SIGNATURES

           ========================================================= */

        .sign-row {

            display: grid;

            grid-template-columns: 1fr 1fr;

            column-gap: 14mm;

            margin-top: 2mm;

            break-inside: avoid;

        }



        .sig { text-align: center; padding-top: 11mm; }

        .sig .line { border-top: 1px solid var(--ink); padding-top: 1mm; }

        .sig .name { font-weight: 700; text-transform: uppercase; font-size: 11px; }

        .sig .pos  { font-size: 10px; font-style: italic; }



        /* =========================================================

           RETURN OF SERVICE

           ========================================================= */

        .return {

            margin-top: 5mm;

            border: 1px solid var(--ink);

            break-inside: avoid;

        }



        .return-title {

            text-align: center;

            font-weight: 700;

            font-size: 10px;

            letter-spacing: .14em;

            text-transform: uppercase;

            padding: 1.1mm 0;

            border-bottom: 1px solid var(--ink);

            background: #eceff3;

        }



        .return-grid {

            display: grid;

            grid-template-columns: 1fr 1fr 1fr;

            padding: 2mm 3.5mm 2.5mm;

            column-gap: 5mm;

            font-size: 10px;

        }



        .rfield .rl { display: block; color: var(--muted); font-size: 9px; font-weight: 700; }

        .rfield .rline { display: block; height: 6.5mm; border-bottom: 1px solid var(--ink); }



        /* =========================================================

           FOOTER

           ========================================================= */

        .foot {

            position: absolute;

            left: var(--pad-x);

            right: var(--pad-x);

            bottom: 7mm;

            padding-top: 1.6mm;

            border-top: .7px solid var(--hair);

            display: flex;

            justify-content: space-between;

            font-family: Arial, Helvetica, sans-serif;

            font-size: 7.5px;

            color: var(--muted);

        }



        /* =========================================================

           SMALL SCREENS (preview only)

           ========================================================= */

        @media screen and (max-width: 820px) {

            .sheet { transform-origin: top left; }

        }



        /* =========================================================

           PRINT – uses the selected paper size and fits to one page

           ========================================================= */

        @media print {
            html, body {
                width: auto !important;
                height: 100% !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                overflow: hidden !important;
            }

            .toolbar { display: none !important; }

            .sheet {
                width: 100% !important;
                max-width: none !important;
                height: 100vh !important;
                min-height: 0 !important;
                margin: 0 !important;
                padding: 7mm 8mm 0 !important;
                box-shadow: none !important;
                overflow: hidden !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                page-break-inside: avoid !important;
                break-after: avoid-page !important;
                break-before: avoid-page !important;
                break-inside: avoid-page !important;
            }

            .content {
                width: 100% !important;
                height: calc(100vh - 23mm) !important;
                max-height: calc(100vh - 23mm) !important;
                overflow: hidden !important;
            }

            .foot {
                left: 8mm !important;
                right: 8mm !important;
                bottom: 4mm !important;
            }

            .fit { transform-origin: top left !important; }
        }

    </style>

</head>



<body>



    {{-- ===================== SCREEN-ONLY TOOLBAR ===================== --}}

    <div class="toolbar">

        <a href="{{ url()->previous() }}" class="btn-back">&larr; Back</a>

        <button type="button" class="btn-print" onclick="window.print()">Print Subpoena</button>

    </div>





    {{-- ========================== A4 SUBPOENA ========================== --}}

    <div class="sheet" id="sheet">



        <div class="content" id="content">

            <div class="fit" id="fit">



                {{-- ---------------- LETTERHEAD ---------------- --}}

                <header class="letterhead">

                    <div class="seal">

                        <img src="{{ asset('icon.png') }}" alt="Barangay Poblacion Seal">

                    </div>



                    <div class="lh-center">

                        <div class="lh-republic">Republic of the Philippines</div>

                        <div class="lh-province">Province of Pangasinan</div>

                        <div class="lh-town">Municipality of Bani</div>

                        <div class="lh-brgy">Barangay Poblacion</div>

                        <div class="lh-office">Office of the Punong Barangay</div>

                    </div>



                    <div class="seal">

                        <img src="{{ asset('bani.png') }}" alt="Municipality of Bani Seal">

                    </div>

                </header>



                <div class="lh-rule"></div>





                {{-- ---------------- TITLE ---------------- --}}

                <div class="title-block">

                    <h1 class="doc-title">Subpoena</h1>

                    <div class="doc-sub">Barangay Case / Complaint Proceeding</div>

                </div>





                {{-- ---------------- CASE CAPTION ---------------- --}}

                <section class="case-head">



                    <div class="caption">

                        <div class="party">{{ $complainantName }}</div>

                        <div class="role">Complainant,</div>



                        <div class="vs">&ndash; versus &ndash;</div>



                        <div class="party">{{ $respondentName }}</div>

                        <div class="role">Respondent.</div>

                    </div>



                    <div class="case-meta">

                        <dl>

                            <dt>Record Code</dt>

                            <dd>{{ $rc->record_code ?? 'N/A' }}</dd>



                            <dt>Date Issued</dt>

                            <dd>{{ $issuedDate }}</dd>

                        </dl>

                    </div>



                </section>





                {{-- ---------------- BODY ---------------- --}}

                <section class="body">

                    <p class="to">

                        <strong>TO:</strong>

                        <strong>{{ $respondentName }}</strong>, and all other persons concerned:

                    </p>



                    <p>

                        You are hereby required to appear before the Office of the Punong

                        Barangay of Barangay Poblacion, Municipality of Bani, Province of

                        Pangasinan, in connection with the barangay complaint stated below.

                    </p>



                    <p>

                        Your attendance is required on the date, at the time, and in the

                        place specified. You are expected to appear in person and to give

                        any information, explanation, or testimony relevant to the matter.

                    </p>

                </section>





                {{-- ---------------- HEARING DETAILS ---------------- --}}

                <section class="hearing">

                    <div class="hearing-title">Hearing Details</div>



                    <div class="hearing-grid">

                        <div class="hcell">

                            <div class="hlabel">Date</div>

                            <div class="hvalue">

                                @if($hearingDate) {{ $hearingDate }} @else <span class="blank"></span> @endif

                            </div>

                        </div>



                        <div class="hcell">

                            <div class="hlabel">Time</div>

                            <div class="hvalue">

                                @if($hearingTime) {{ $hearingTime }} @else <span class="blank"></span> @endif

                            </div>

                        </div>



                        <div class="hcell wide">

                            <div class="hlabel">Venue</div>

                            <div class="hvalue">{{ $hearingPlace }}</div>

                        </div>



                        <div class="hcell wide">

                            <div class="hlabel">Purpose</div>

                            <div class="hvalue">Barangay Complaint / Case Hearing</div>

                        </div>

                    </div>

                </section>





                {{-- ---------------- SUBJECT MATTER ---------------- --}}

                <section class="subject">

                    <div class="row"><b>Subject Matter:</b> {{ $subject }}</div>



                    @if($description)

                        <div class="row"><b>Brief Description:</b> {{ $description }}</div>

                    @endif

                </section>





                {{-- ---------------- WITNESSES ---------------- --}}

                @if(count($witnessList) > 0)

                    <section class="witnesses">

                        <div class="w-title">Witnesses / Other Persons Concerned:</div>



                        <ol>

                            @foreach($witnessList as $witness)

                                <li>{{ $witness }}</li>

                            @endforeach

                        </ol>



                        @if($witnessExtra > 0)

                            <div class="more">and {{ $witnessExtra }} other witness{{ $witnessExtra > 1 ? 'es' : '' }}.</div>

                        @endif

                    </section>

                @endif





                {{-- ---------------- NOTICE ---------------- --}}

                <section class="notice">

                    <b>Notice:</b>

                    Failure to appear at the scheduled hearing without a valid reason may

                    result in appropriate action in accordance with applicable barangay

                    proceedings and regulations. Please bring any relevant documents or

                    evidence that may help resolve the matter.

                </section>





                {{-- ---------------- ISSUED ---------------- --}}

                <p class="issued">

                    Issued this <strong>{{ $issuedDate }}</strong> at Barangay Poblacion,

                    Municipality of Bani, Province of Pangasinan.

                </p>





                {{-- ---------------- SIGNATURES ---------------- --}}

                <section class="sign-row">

                    <div class="sig">

                        <div class="line">

                            <div class="name">{{ $captainName ?? 'Punong Barangay' }}</div>

                            <div class="pos">Punong Barangay</div>

                        </div>

                    </div>



                    <div class="sig">

                        <div class="line">

                            <div class="name">{{ $secretaryName ?? 'Barangay Secretary' }}</div>

                            <div class="pos">Barangay Secretary</div>

                        </div>

                    </div>

                </section>





                {{-- ---------------- RETURN OF SERVICE ---------------- --}}

                <section class="return">

                    <div class="return-title">Return of Service</div>



                    <div class="return-grid">

                        <div class="rfield">

                            <span class="rl">Date and time served</span>

                            <span class="rline"></span>

                        </div>



                        <div class="rfield">

                            <span class="rl">Place of service</span>

                            <span class="rline"></span>

                        </div>



                        <div class="rfield">

                            <span class="rl">Served by (name and signature)</span>

                            <span class="rline"></span>

                        </div>

                    </div>

                </section>



            </div>{{-- /.fit --}}

        </div>{{-- /.content --}}





        {{-- ---------------- FOOTER ---------------- --}}

        <footer class="foot">

            <span>Barangay Poblacion Record Management System</span>

            <span>Subpoena Record: {{ $rc->record_code ?? 'N/A' }}</span>

        </footer>



    </div>





    {{-- =========================================================

         ONE-PAGE GUARANTEE

         If the content is ever taller than the page window

         (long names, long description, etc.), it is scaled

         down to fit instead of spilling onto a second page.

         ========================================================= --}}

    <script>

        (function () {

            var content = document.getElementById('content');

            var fit     = document.getElementById('fit');



            function fitToPage() {

                // reset first so we measure natural size

                fit.style.transform = 'none';

                fit.style.width = '100%';



                var available = content.clientHeight;

                var natural   = fit.scrollHeight;



                if (natural > available && available > 0) {
                    // Scale the whole document to fit the selected paper's printable height.
                    var scale = Math.max(0.1, (available - 2) / natural);
                    fit.style.transformOrigin = 'top left';
                    fit.style.transform = 'scale(' + scale + ')';
                    fit.style.width = (100 / scale) + '%';
                }

            }



            window.addEventListener('load', fitToPage);

            window.addEventListener('resize', fitToPage);

            window.addEventListener('beforeprint', function () { requestAnimationFrame(fitToPage); });

            window.addEventListener('afterprint', fitToPage);



            // Images (seals) can change layout height after load

            Array.prototype.forEach.call(document.images, function (img) {

                img.addEventListener('load', fitToPage);

            });



            // Chrome/Edge print via matchMedia as well

            if (window.matchMedia) {

                var mq = window.matchMedia('print');

                if (mq.addEventListener) { mq.addEventListener('change', fitToPage); }

                else if (mq.addListener) { mq.addListener(fitToPage); }

            }

        })();

    </script>



</body>

</html>
