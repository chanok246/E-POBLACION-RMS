<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'Household Masterlist' }}</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #f2f2f2;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .screen-toolbar {
            width: 100%;
            padding: 15px 20px;
            background: #1f2937;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }

        .screen-toolbar .toolbar-title {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
        }

        .toolbar-buttons {
            display: flex;
            gap: 8px;
        }

        .toolbar-buttons button,
        .toolbar-buttons a {
            display: inline-block;
            border: none;
            border-radius: 5px;
            padding: 9px 14px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-print {
            background: #0d6efd;
            color: #fff;
        }

        .btn-back {
            background: #6c757d;
            color: #fff;
        }

        .print-page {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            padding: 15mm 12mm;
            background: #fff;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.15);
        }

        .barangay-header {
            text-align: center;
            line-height: 1.25;
            margin-bottom: 10px;
        }

        .barangay-header .republic {
            font-size: 12px;
            font-weight: 400;
        }

        .barangay-header .province {
            font-size: 12px;
            font-weight: 600;
        }

        .barangay-header .municipality {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .barangay-header .barangay {
            font-size: 17px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-line {
            width: 100%;
            border-top: 2px solid #000;
            margin: 8px 0 12px;
        }

        .document-title {
            text-align: center;
            margin: 4px 0 2px;
            font-size: 18px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }

        .document-subtitle {
            text-align: center;
            margin-bottom: 15px;
            font-size: 11px;
        }

        .report-information {
            width: 100%;
            margin-bottom: 12px;
            border: 1px solid #000;
            border-collapse: collapse;
        }

        .report-information td {
            padding: 6px 8px;
            border: 1px solid #000;
            vertical-align: middle;
        }

        .report-information .label {
            width: 17%;
            font-weight: 700;
        }

        .report-information .value {
            width: 33%;
        }

        .summary-box {
            display: flex;
            width: 100%;
            margin-bottom: 12px;
        }

        .summary-item {
            flex: 1;
            border: 1px solid #000;
            padding: 7px 8px;
            text-align: center;
        }

        .summary-item + .summary-item {
            border-left: none;
        }

        .summary-label {
            display: block;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .summary-value {
            display: block;
            font-size: 14px;
            font-weight: 800;
        }

        .masterlist-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .masterlist-table th,
        .masterlist-table td {
            border: 1px solid #000;
            padding: 6px 5px;
            vertical-align: middle;
        }

        .masterlist-table th {
            background: #e9ecef;
            text-align: center;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .masterlist-table td {
            font-size: 10px;
        }

        .masterlist-table .number {
            width: 6%;
            text-align: center;
        }

        .masterlist-table .household-number {
            width: 13%;
            text-align: center;
        }

        .masterlist-table .head {
            width: 22%;
        }

        .masterlist-table .address {
            width: 27%;
        }

        .masterlist-table .purok {
            width: 10%;
            text-align: center;
        }

        .masterlist-table .members {
            width: 9%;
            text-align: center;
        }

        .masterlist-table .status {
            width: 13%;
            text-align: center;
        }

        .masterlist-table tbody tr {
            page-break-inside: avoid;
        }

        .empty-row {
            text-align: center;
            padding: 20px !important;
            font-style: italic;
        }

        .footer-section {
            margin-top: 22px;
        }

        .certification {
            font-size: 10px;
            line-height: 1.5;
            margin-bottom: 30px;
        }

        .signature-area {
            width: 100%;
            display: flex;
            justify-content: flex-end;
        }

        .signature-block {
            width: 230px;
            text-align: center;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 35px;
            padding-top: 4px;
            font-weight: 700;
        }

        .signature-position {
            font-size: 10px;
            margin-top: 2px;
        }

        .document-footer {
            margin-top: 25px;
            padding-top: 6px;
            border-top: 1px solid #000;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
        }

        .page-break {
            page-break-before: always;
        }

        @page {
            size: A4 portrait;
            margin: 0;
        }

        @media print {
            html,
            body {
                background: #fff;
                width: 210mm;
            }

            .screen-toolbar {
                display: none !important;
            }

            .print-page {
                width: 210mm;
                min-height: 297mm;
                margin: 0;
                padding: 15mm 12mm;
                box-shadow: none;
            }

            .masterlist-table thead {
                display: table-header-group;
            }

            .masterlist-table tfoot {
                display: table-footer-group;
            }

            .masterlist-table tr,
            .masterlist-table td,
            .masterlist-table th {
                page-break-inside: avoid;
            }
        }
    </style>
</head>

<body>

    {{-- SCREEN-ONLY TOOLBAR --}}
    <div class="screen-toolbar">
        <div class="toolbar-title">
            Household Masterlist
        </div>

        <div class="toolbar-buttons">
            <a href="{{ route('households.index') }}" class="btn-back">
                Back to Households
            </a>

            <button type="button" class="btn-print" onclick="window.print()">
                Print Masterlist
            </button>
        </div>
    </div>


    {{-- PRINTABLE A4 DOCUMENT --}}
    <main class="print-page">

        {{-- OFFICIAL BARANGAY HEADER --}}
        <header class="barangay-header">

            <div class="republic">
                REPUBLIC OF THE PHILIPPINES
            </div>

            <div class="province">
                PROVINCE OF PANGASINAN
            </div>

            <div class="municipality">
                MUNICIPALITY OF BANI
            </div>

            <div class="barangay">
                BARANGAY POBLACION
            </div>

        </header>

        <div class="header-line"></div>


        {{-- DOCUMENT TITLE --}}
        <h1 class="document-title">
            {{ $title ?? 'Household Masterlist' }}
        </h1>

        <div class="document-subtitle">
            Official Household Record of Barangay Poblacion
        </div>


        {{-- REPORT INFORMATION --}}
        <table class="report-information">
            <tbody>
                <tr>
                    <td class="label">
                        Purok
                    </td>

                    <td class="value">
                        @if(!empty($selectedPurok))
                            {{ $selectedPurok }}
                        @else
                            All Puroks
                        @endif
                    </td>

                    <td class="label">
                        Date Generated
                    </td>

                    <td class="value">
                        {{ now()->format('F d, Y') }}
                    </td>
                </tr>

                <tr>
                    <td class="label">
                        Record Status
                    </td>

                    <td class="value">
                        @if(!empty($selectedStatus))
                            {{ ucfirst($selectedStatus) }}
                        @else
                            All Statuses
                        @endif
                    </td>

                    <td class="label">
                        Municipality
                    </td>

                    <td class="value">
                        Bani, Pangasinan
                    </td>
                </tr>
            </tbody>
        </table>


        {{-- SUMMARY --}}
        <div class="summary-box">

            <div class="summary-item">
                <span class="summary-label">
                    Total Households
                </span>

                <span class="summary-value">
                    {{ number_format($totalHouseholds ?? $households->count()) }}
                </span>
            </div>

            <div class="summary-item">
                <span class="summary-label">
                    Total Members
                </span>

                <span class="summary-value">
                    {{ number_format($totalMembers ?? 0) }}
                </span>
            </div>

            <div class="summary-item">
                <span class="summary-label">
                    Purok
                </span>

                <span class="summary-value">
                    {{ $selectedPurok ?: '1–7' }}
                </span>
            </div>

        </div>


        {{-- HOUSEHOLD MASTERLIST TABLE --}}
        <table class="masterlist-table">

            <thead>
                <tr>
                    <th class="number">
                        No.
                    </th>

                    <th class="household-number">
                        Household No.
                    </th>

                    <th class="head">
                        Household Head
                    </th>

                    <th class="address">
                        Address
                    </th>

                    <th class="purok">
                        Purok
                    </th>

                    <th class="members">
                        Members
                    </th>

                    <th class="status">
                        Status
                    </th>
                </tr>
            </thead>

            <tbody>

                @forelse($households as $household)

                    <tr>

                        <td class="number">
                            {{ $loop->iteration }}
                        </td>

                        <td class="household-number">
                            {{ $household->household_number }}
                        </td>

                        <td class="head">
                            @if($household->householdHead)
                                {{ $household->householdHead->full_name }}
                            @else
                                —
                            @endif
                        </td>

                        <td class="address">
                            {{ $household->address ?: '—' }}
                        </td>

                        <td class="purok">
                            {{ $household->purok ?: 'Unassigned' }}
                        </td>

                        <td class="members">
                            {{ $household->member_count }}
                        </td>

                        <td class="status">
                            {{ ucfirst($household->status ?? 'Unknown') }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="empty-row">
                            No household records found for the selected criteria.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>


        {{-- FOOTER / CERTIFICATION --}}
        <section class="footer-section">

            <div class="certification">
                <strong>Certification:</strong>
                This masterlist contains the household records currently
                registered in the Barangay Poblacion Record Management System.
                This document is intended for official barangay record
                management, reference, and administrative purposes.
            </div>


            {{-- SIGNATURE --}}
            <div class="signature-area">

                <div class="signature-block">

                    <div class="signature-line">
                        BARANGAY CAPTAIN
                    </div>

                    <div class="signature-position">
                        Punong Barangay
                    </div>

                </div>

            </div>


            {{-- DOCUMENT FOOTER --}}
            <div class="document-footer">

                <span>
                    Barangay Poblacion, Bani, Pangasinan
                </span>

                <span>
                    Generated: {{ now()->format('m/d/Y h:i A') }}
                </span>

            </div>

        </section>

    </main>


    <script>
        /*
         * Automatically open the browser print dialog when this page
         * is opened with ?print=1.
         *
         * Normal access does not automatically print.
         */
        document.addEventListener('DOMContentLoaded', function () {
            const params = new URLSearchParams(window.location.search);

            if (params.get('print') === '1') {
                setTimeout(function () {
                    window.print();
                }, 400);
            }
        });
    </script>

</body>
</html>
