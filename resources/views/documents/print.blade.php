<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Ctext y='13' font-size='14'%3E🖨️%3C/text%3E%3C/svg%3E">
    <title>{{ $mode === 'preview' ? 'Preview' : 'Print' }} — {{ $document->document_type }} | e-Poblacion</title>

    <style>
        :root {
            --bg: #eef2f7;
            --surface: #ffffff;
            --ink: #14213d;
            --muted: #64748b;
            --brand: #1f4e9c;
            --brand-soft: #e4edfb;
            --line: #dbe2ec;
            --danger: #b42318;
            --danger-bg: #fdeceb;
            --success: #1a7f37;
        }

        * { box-sizing: border-box; }

        html, body { margin: 0; min-height: 100%; }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: Arial, Helvetica, sans-serif;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 20px;
            background: rgba(255,255,255,.96);
            border-bottom: 1px solid var(--line);
            box-shadow: 0 2px 12px rgba(20,33,61,.08);
            backdrop-filter: blur(6px);
        }

        .toolbar-title { min-width: 0; }
        .toolbar-title strong { display: block; font-size: 15px; }
        .toolbar-title span { color: var(--muted); font-size: 12px; }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-height: 42px;
            padding: 9px 15px;
            border-radius: 8px;
            border: 1px solid transparent;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            cursor: pointer;
            font-family: inherit;
            transition: filter .12s ease, transform .05s ease;
        }

        .btn:active { transform: translateY(1px); }

        .btn-primary { background: var(--brand); color: #fff; }
        .btn-secondary { background: var(--surface); color: var(--ink); border-color: var(--line); }
        .btn:hover { filter: brightness(.97); }

        .btn:disabled {
            opacity: .55;
            cursor: progress;
            pointer-events: none;
        }

        .btn-spinner {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,.5);
            border-top-color: #fff;
            display: inline-block;
            animation: spin .7s linear infinite;
            flex: none;
        }

        .btn-secondary .btn-spinner {
            border-color: rgba(31,78,156,.25);
            border-top-color: var(--brand);
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .viewer-wrap {
            padding: 20px;
        }

        .viewer-card {
            position: relative;
            width: min(1200px, 100%);
            height: calc(100vh - 110px);
            min-height: 620px;
            margin: 0 auto;
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(20,33,61,.10);
        }

        #pdfViewer {
            display: block;
            width: 100%;
            height: 100%;
            border: 0;
            background: #fff;
            opacity: 0;
            transition: opacity .25s ease;
        }

        #pdfViewer.is-ready { opacity: 1; }

        .viewer-status {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            background: var(--surface);
            text-align: center;
            padding: 24px;
        }

        .viewer-status.is-hidden { display: none; }

        .viewer-spinner {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 4px solid var(--brand-soft);
            border-top-color: var(--brand);
            animation: spin .8s linear infinite;
        }

        .viewer-status p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }

        .viewer-status .fallback-actions {
            display: none;
            gap: 8px;
            margin-top: 4px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .viewer-status.is-slow .fallback-actions { display: flex; }

        .viewer-status.is-error .viewer-spinner { display: none; }

        .viewer-status.is-error {
            color: var(--danger);
        }

        .viewer-status.is-error p { color: var(--ink); }

        .error-icon {
            font-size: 30px;
        }

        .notice {
            width: min(1200px, 100%);
            margin: 0 auto 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
            color: var(--muted);
            font-size: 13px;
        }

        .notice strong { color: var(--ink); }

        @media (max-width: 700px) {
            .toolbar { align-items: stretch; flex-direction: column; }
            .actions { justify-content: stretch; }
            .btn { flex: 1 1 0; }
            .viewer-wrap { padding: 10px; }
            .viewer-card { height: calc(100vh - 150px); min-height: 520px; border-radius: 8px; }
        }

        @media print {
            .toolbar, .notice { display: none !important; }
            .viewer-wrap { padding: 0; }
            .viewer-card { width: 100%; height: 100vh; min-height: 0; border: 0; box-shadow: none; border-radius: 0; }
        }
    </style>
</head>
<body>
    <header class="toolbar">
        <div class="toolbar-title">
            <strong>{{ $mode === 'preview' ? 'Official Document Preview' : 'Official Document — Ready to Print' }}</strong>
            <span>{{ $document->document_type }} · Request {{ $document->request_code }}</span>
        </div>

        <div class="actions">
            <a href="{{ route('documents.show', $document) }}"
               class="btn btn-secondary"
               onclick="return returnToSystem(event)">
                ← Back to System
            </a>

            <button type="button" id="downloadBtn" class="btn btn-secondary" disabled>
                ⬇ Download
            </button>

            <button type="button" id="printBtn" class="btn btn-primary" disabled>
                🖨 {{ $mode === 'preview' ? 'Print Document' : 'Print Again' }}
            </button>
        </div>
    </header>

    <main class="viewer-wrap">
        <div class="notice">
            <span>
                <strong>{{ $mode === 'preview' ? 'Preview mode:' : 'Print mode:' }}</strong>
                The official PDF generated from the registered barangay document template is displayed below.
            </span>
        </div>

        <div class="viewer-card">
            @if (empty($pdfData))

                <div class="viewer-status is-error">
                    <div class="error-icon">⚠️</div>
                    <p>
                        <strong>The official document could not be loaded.</strong><br>
                        Please go back and try generating it again.
                    </p>
                    <div class="fallback-actions" style="display:flex;">
                        <a href="{{ route('documents.show', $document) }}" class="btn btn-primary">
                            ← Back to System
                        </a>
                    </div>
                </div>

            @else

                <div class="viewer-status" id="viewerStatus">
                    <div class="viewer-spinner"></div>
                    <p id="viewerStatusText">Preparing the official document…</p>
                    <div class="fallback-actions" id="viewerFallback">
                        <button type="button" class="btn btn-secondary" id="openNewTabBtn">
                            Open in new tab
                        </button>
                        <button type="button" class="btn btn-secondary" id="downloadFallbackBtn">
                            Download instead
                        </button>
                    </div>
                </div>

                <iframe
                    id="pdfViewer"
                    title="Official document PDF preview"
                ></iframe>

            @endif
        </div>
    </main>

    @if (! empty($pdfData))
        <script>
            (function () {
                const base64Data = @json($pdfData);

                const fileName =
                    @json($document->request_code) + '-' +
                    @json(\Illuminate\Support\Str::slug($document->document_type)) +
                    '.pdf';

                const frame = document.getElementById('pdfViewer');
                const status = document.getElementById('viewerStatus');
                const statusText = document.getElementById('viewerStatusText');
                const fallback = document.getElementById('viewerFallback');

                const printBtn = document.getElementById('printBtn');
                const downloadBtn = document.getElementById('downloadBtn');
                const openNewTabBtn = document.getElementById('openNewTabBtn');
                const downloadFallbackBtn = document.getElementById('downloadFallbackBtn');

                let blobUrl = null;
                let slowTimer = null;

                function base64ToBlob(base64, contentType) {
                    const byteChars = atob(base64);
                    const chunkSize = 8192;
                    const chunks = [];

                    for (let offset = 0; offset < byteChars.length; offset += chunkSize) {
                        const slice = byteChars.slice(offset, offset + chunkSize);
                        const bytes = new Uint8Array(slice.length);

                        for (let i = 0; i < slice.length; i++) {
                            bytes[i] = slice.charCodeAt(i);
                        }

                        chunks.push(bytes);
                    }

                    return new Blob(chunks, { type: contentType });
                }

                function downloadPdf() {
                    if (! blobUrl) return;

                    const link = document.createElement('a');
                    link.href = blobUrl;
                    link.download = fileName;
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                }

                function markReady() {
                    if (status) {
                        status.classList.add('is-hidden');
                    }

                    frame.classList.add('is-ready');
                    printBtn.disabled = false;
                    downloadBtn.disabled = false;

                    if (slowTimer) {
                        clearTimeout(slowTimer);
                    }
                }

                function showFatalError() {
                    if (! status) return;

                    status.classList.remove('is-hidden', 'is-slow');
                    status.classList.add('is-error');
                    status.querySelector('.viewer-spinner')?.remove();

                    if (statusText) {
                        statusText.innerHTML =
                            '<strong>The document could not be displayed here.</strong><br>' +
                            'Try opening it in a new tab or downloading it instead.';
                    }

                    fallback.style.display = 'flex';
                }

                try {
                    const blob = base64ToBlob(base64Data, 'application/pdf');
                    blobUrl = URL.createObjectURL(blob);

                    frame.addEventListener('load', markReady);
                    frame.addEventListener('error', showFatalError);
                    frame.src = blobUrl;

                    // If the embedded viewer hasn't finished after a few
                    // seconds (some mobile browsers cannot render a PDF
                    // inside an iframe at all), offer a fallback instead
                    // of leaving the user staring at a spinner forever.
                    slowTimer = setTimeout(function () {
                        if (status && ! status.classList.contains('is-hidden')) {
                            statusText.textContent =
                                'Still preparing… this is taking longer than usual.';
                            status.classList.add('is-slow');
                        }
                    }, 4000);
                } catch (error) {
                    showFatalError();
                }

                printBtn.addEventListener('click', function () {
                    try {
                        frame.contentWindow.focus();
                        frame.contentWindow.print();
                    } catch (error) {
                        if (blobUrl) {
                            const printWindow = window.open(blobUrl, '_blank');
                            printWindow?.addEventListener?.('load', function () {
                                printWindow.print();
                            });
                        } else {
                            window.print();
                        }
                    }
                });

                downloadBtn.addEventListener('click', downloadPdf);
                downloadFallbackBtn.addEventListener('click', downloadPdf);

                openNewTabBtn.addEventListener('click', function () {
                    if (blobUrl) {
                        window.open(blobUrl, '_blank');
                    }
                });

                window.addEventListener('beforeunload', function () {
                    if (blobUrl) {
                        URL.revokeObjectURL(blobUrl);
                    }
                });
            })();
        </script>
    @endif

    <script>
        function returnToSystem(event) {
            if (event) {
                event.preventDefault();
            }

            const systemUrl = @json(route('documents.show', $document));

            // This page is opened from the Process Journey with target="_blank".
            // Refresh the original system tab so the next workflow action is
            // immediately available, then close this preview/print tab.
            if (window.opener && !window.opener.closed) {
                try {
                    window.opener.location.href = systemUrl;
                    window.opener.focus();
                    window.close();
                    return false;
                } catch (error) {
                    // If the browser blocks the opener, use normal navigation.
                }
            }

            window.location.href = systemUrl;
            return false;
        }
    </script>
</body>
</html>