
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnel QR ID Card | e-Poblacion</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 30px;
            background: #f1f5f9;
            color: #1e293b;
            font-family: Arial, Helvetica, sans-serif;
        }

        .page {
            max-width: 900px;
            margin: auto;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 24px;
        }

        .toolbar-title {
            font-size: 18px;
            font-weight: 700;
        }

        .toolbar-actions {
            display: flex;
            gap: 8px;
        }

        .btn {
            display: inline-block;
            padding: 10px 16px;
            border: 0;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-secondary {
            background: #64748b;
            color: white;
        }

        .card-wrapper {
            display: flex;
            justify-content: center;
        }

        .id-card {
            width: 420px;
            min-height: 265px;
            overflow: hidden;
            border: 1px solid #cbd5e1;
            border-radius: 14px;
            background: white;
            box-shadow: 0 10px 30px rgba(15, 23, 42, .12);
        }

        .card-header {
            padding: 18px 20px;
            background: #166534;
            color: white;
            text-align: center;
        }

        .organization {
            font-size: 17px;
            font-weight: 800;
            letter-spacing: .5px;
            text-transform: uppercase;
        }

        .subtitle {
            margin-top: 5px;
            font-size: 11px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .card-body {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 24px 20px;
        }

        .qr-container {
            display: flex;
            flex: 0 0 145px;
            width: 145px;
            height: 145px;
            align-items: center;
            justify-content: center;
            padding: 7px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: white;
        }

        .qr-container svg {
            display: block;
            width: 100%;
            height: 100%;
        }

        .person-details {
            min-width: 0;
            flex: 1;
        }

        .person-label {
            margin-bottom: 5px;
            color: #64748b;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .person-name {
            margin-bottom: 15px;
            color: #0f172a;
            font-size: 19px;
            font-weight: 800;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .person-id {
            margin-bottom: 12px;
            color: #166534;
            font-size: 16px;
            font-weight: 800;
            overflow-wrap: anywhere;
        }

        .person-position {
            color: #334155;
            font-size: 13px;
            font-weight: 600;
            overflow-wrap: anywhere;
        }

        .card-footer {
            padding: 10px 18px;
            border-top: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 10px;
            text-align: center;
        }

        .notice {
            max-width: 420px;
            margin: 18px auto 0;
            padding: 12px 14px;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            background: #eff6ff;
            color: #1e40af;
            font-size: 12px;
            line-height: 1.6;
        }

        @media (max-width: 520px) {
            body {
                padding: 15px;
            }

            .id-card {
                width: 100%;
            }

            .card-body {
                gap: 12px;
                padding: 18px 12px;
            }

            .qr-container {
                flex-basis: 115px;
                width: 115px;
                height: 115px;
            }

            .person-name {
                font-size: 16px;
            }
        }

        @media print {
            @page {
                margin: 8mm;
            }

            body {
                padding: 0;
                background: white;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .toolbar,
            .notice {
                display: none !important;
            }

            .id-card {
                box-shadow: none;
                break-inside: avoid;
            }
        }
    </style>
</head>

<body>
@php
    $displayName = trim(implode(' ', array_filter([
        $personnel->first_name,
        $personnel->middle_name,
        $personnel->last_name,
        $personnel->suffix,
    ])));
@endphp

<div class="page">

    <div class="toolbar">
        <div class="toolbar-title">
            Personnel QR ID Card
        </div>

        <div class="toolbar-actions">
            <a href="{{ route('attendance.personnel.show', $personnel) }}"
               class="btn btn-secondary">
                Back
            </a>

            <button type="button"
                    class="btn btn-primary"
                    onclick="window.print()">
                Print ID Card
            </button>
        </div>
    </div>

    <div class="card-wrapper">
        <div class="id-card">

            <div class="card-header">
                <div class="organization">
                    Barangay Poblacion
                </div>

                <div class="subtitle">
                    Bani, Pangasinan
                </div>
            </div>

            <div class="card-body">

                <div class="qr-container">
                    {!! $qrSvg !!}
                </div>

                <div class="person-details">

                    <div class="person-label">
                        Personnel Name
                    </div>

                    <div class="person-name">
                        {{ $displayName ?: 'Unnamed Personnel' }}
                    </div>

                    <div class="person-label">
                        Personnel ID
                    </div>

                    <div class="person-id">
                        {{ $personnel->personnel_id }}
                    </div>

                    <div class="person-position">
                        {{ $personnel->position }}
                    </div>

                </div>

            </div>

            <div class="card-footer">
                OFFICIAL PERSONNEL ATTENDANCE IDENTIFICATION
            </div>

        </div>
    </div>

    <div class="notice">
        <strong>Security reminder:</strong>
        This QR code is a private attendance credential. Do not share it
        with unauthorized persons. A registered 6-digit PIN is still
        required before recording attendance.
    </div>

</div>

</body>
</html>
