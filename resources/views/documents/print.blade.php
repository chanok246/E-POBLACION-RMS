
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
        }

        * { box-sizing: border-box; }

        html, body {
            margin: 0;
            min-height: 100%;
        }

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

        .toolbar-title strong {
            display: block;
            font-size: 15px;
        }

        .toolbar-title span {
            color: var(--muted);
            font-size: 12px;
        }

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

        .btn-primary {
            background: var(--brand);
            color: #fff;
        }

        .btn-secondary {
            background: var(--surface);
            color: var(--ink);
            border-color: var(--line);
        }

        .btn:hover { filter: brightness(.97); }

        .btn:disabled {
            opacity: .55;
            cursor: not-allowed;
        }

        .viewer-wrap { padding: 20px; }

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

        .viewer-card {
            position: relative;
            width: min(1200px, 100%);
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
            height: calc(100vh - 110px);
            min-height: 620px;
            border: 0;
            background: #fff;
            opacity: 0;
            transition: opacity .25s ease;
        }

        #pdfViewer.is-ready { opacity: 1; }

        #docxViewer {
            display: none;
            width: 100%;
            min-height: 620px;
            padding: 24px 12px;
            background: #e5eaf1;
        }

        #docxViewer.is-ready { display: block; }

        #docxViewer .docx-wrapper {
            padding: 0 !important;
            background: transparent !important;
        }

        #docxViewer .docx {
            margin: 0 auto 24px !important;
            box-shadow: 0 3px 15px rgba(20,33,61,.16);
            background: #fff;
        }

        .viewer-status {
            position: absolute;
            inset: 0;
            z-index: 5;
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

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .viewer-status p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .viewer-status.is-error .viewer-spinner { display: none; }
        .viewer-status.is-error p { color: var(--ink); }

        .error-icon { font-size: 30px; }

        .fallback-actions {
            display: flex;
            gap: 8px;
            margin-top: 4px;
            flex-wrap: wrap;
            justify-content: center;
        }

        @media (max-width: 700px) {
            .toolbar {
                align-items: stretch;
                flex-direction: column;
            }

            .actions { justify-content: stretch; }
            .btn { flex: 1 1 0; }
            .viewer-wrap { padding: 10px; }

            .viewer-card {
                min-height: 520px;
                border-radius: 8px;
            }

            #pdfViewer {
                height: calc(100vh - 150px);
                min-height: 520px;
            }

            #docxViewer { padding: 10px 4px; }
        }

        @media print {
            @page {
                margin: 0;
            }

            html, body {
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                min-height: 0 !important;
            }

            .toolbar,
            .notice,
            .viewer-status {
                display: none !important;
            }

            .viewer-wrap {
                margin: 0 !important;
                padding: 0 !important;
            }

            .viewer-card {
                width: 100% !important;
                min-height: 0 !important;
                margin: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                overflow: visible !important;
            }

            #pdfViewer {
                display: block !important;
                width: 100% !important;
                height: 100vh !important;
                min-height: 0 !important;
                opacity: 1 !important;
            }

            #docxViewer {
                display: block !important;
                min-height: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }

            #docxViewer .docx-wrapper {
                padding: 0 !important;
                margin: 0 !important;
                background: #fff !important;
            }

            #docxViewer .docx {
                margin: 0 auto !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body>
    <header class="toolbar">
        <div class="toolbar-title">
            <strong>
                {{ $mode === 'preview' ? 'Official Document Preview' : 'Official Document — Ready to Print' }}
            </strong>
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
                The official document generated from the registered barangay template is displayed below.
            </span>
        </div>

        <div class="viewer-card">
            @if (!empty($pdfData) || !empty($docxData))

                <div class="viewer-status" id="viewerStatus">
                    <div class="viewer-spinner"></div>
                    <p id="viewerStatusText">Preparing the official document…</p>
                </div>

                @if (!empty($pdfData))
                    <iframe
                        id="pdfViewer"
                        title="Official document PDF preview">
                    </iframe>
                @else
                    <div id="docxViewer" aria-label="Official DOCX document preview"></div>
                @endif

            @else

                <div class="viewer-status is-error">
                    <div class="error-icon">⚠️</div>
                    <p>
                        <strong>The official document could not be loaded.</strong><br>
                        Please go back and try generating it again.
                    </p>

                    <div class="fallback-actions">
                        <a href="{{ route('documents.show', $document) }}"
                           class="btn btn-primary">
                            ← Back to System
                        </a>
                    </div>
                </div>

            @endif
        </div>
    </main>

    @if (!empty($pdfData) || !empty($docxData))
        <script>
            (function () {
                'use strict';

                const mode = @json(!empty($docxData) ? 'docx' : 'pdf');

                const base64Data = @json(
                    !empty($docxData) ? $docxData : $pdfData
                );

                const fileName =
                    @json($document->request_code) + '-' +
                    @json(\Illuminate\Support\Str::slug($document->document_type)) +
                    (mode === 'docx' ? '.docx' : '.pdf');

                const mimeType = mode === 'docx'
                    ? 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    : 'application/pdf';

                const status = document.getElementById('viewerStatus');
                const statusText = document.getElementById('viewerStatusText');
                const printBtn = document.getElementById('printBtn');
                const downloadBtn = document.getElementById('downloadBtn');

                const pdfFrame = document.getElementById('pdfViewer');
                const docxViewer = document.getElementById('docxViewer');

                let fileBlob = null;
                let fileUrl = null;
                let ready = false;

                function base64ToBlob(base64, contentType) {
                    const binary = atob(base64);
                    const chunkSize = 8192;
                    const chunks = [];

                    for (let offset = 0; offset < binary.length; offset += chunkSize) {
                        const slice = binary.slice(offset, offset + chunkSize);
                        const bytes = new Uint8Array(slice.length);

                        for (let i = 0; i < slice.length; i++) {
                            bytes[i] = slice.charCodeAt(i);
                        }

                        chunks.push(bytes);
                    }

                    return new Blob(chunks, { type: contentType });
                }

                function markReady() {
                    ready = true;

                    if (status) {
                        status.classList.add('is-hidden');
                    }

                    printBtn.disabled = false;
                    downloadBtn.disabled = false;
                }

                function showError(message) {
                    if (status) {
                        status.classList.remove('is-hidden');
                        status.classList.add('is-error');

                        const spinner = status.querySelector('.viewer-spinner');

                        if (spinner) {
                            spinner.remove();
                        }

                        if (statusText) {
                            statusText.textContent = message;
                        }
                    }

                    printBtn.disabled = true;
                    downloadBtn.disabled = !fileBlob;
                }

                function downloadOriginal() {
                    if (!fileBlob || !fileUrl) {
                        return;
                    }

                    const link = document.createElement('a');

                    link.href = fileUrl;
                    link.download = fileName;

                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                }

                async function renderDocx() {
                    try {
                        if (!docxViewer) {
                            throw new Error('DOCX preview container is missing.');
                        }

                        if (statusText) {
                            statusText.textContent = 'Loading DOCX preview engine…';
                        }

                        const docxModule = await import(
                            'https://cdn.jsdelivr.net/npm/docx-preview@0.3.6/+esm'
                        );

                        if (
                            !docxModule ||
                            typeof docxModule.renderAsync !== 'function'
                        ) {
                            throw new Error('The DOCX preview library is unavailable.');
                        }

                        if (statusText) {
                            statusText.textContent = 'Rendering official document…';
                        }

                        await docxModule.renderAsync(
                            fileBlob,
                            docxViewer,
                            null,
                            {
                                className: 'docx',
                                inWrapper: true,
                                ignoreWidth: false,
                                ignoreHeight: false,
                                ignoreFonts: false,
                                breakPages: true,
                                renderHeaders: true,
                                renderFooters: true,
                                renderFootnotes: true,
                                renderEndnotes: true,
                                useBase64URL: true,
                                experimental: false
                            }
                        );

                        docxViewer.classList.add('is-ready');
                        markReady();

                    } catch (error) {
                        console.error('DOCX rendering failed:', error);

                        showError(
                            'The DOCX preview could not be displayed. ' +
                            'You can still download the original DOCX file.'
                        );
                    }
                }

                function renderPdf() {
                    if (!pdfFrame) {
                        showError('The PDF preview frame is missing.');
                        return;
                    }

                    pdfFrame.addEventListener('load', function () {
                        pdfFrame.classList.add('is-ready');
                        markReady();
                    }, { once: true });

                    pdfFrame.addEventListener('error', function () {
                        showError(
                            'The PDF preview could not be displayed. ' +
                            'You can still download the PDF file.'
                        );
                    }, { once: true });

                    pdfFrame.src = fileUrl;
                }

                async function initialize() {
                    try {
                        fileBlob = base64ToBlob(base64Data, mimeType);
                        fileUrl = URL.createObjectURL(fileBlob);

                        downloadBtn.disabled = false;
                        downloadBtn.addEventListener('click', downloadOriginal);

                        if (mode === 'pdf') {
                            renderPdf();
                            return;
                        }

                        await renderDocx();

                    } catch (error) {
                        console.error('Document initialization failed:', error);

                        showError(
                            'The official document could not be prepared. ' +
                            'Please try again or download the original file.'
                        );
                    }
                }

                printBtn.addEventListener('click', function () {
                    if (!ready) {
                        return;
                    }

                    if (mode === 'pdf') {
                        try {
                            pdfFrame.contentWindow.focus();
                            pdfFrame.contentWindow.print();
                        } catch (error) {
                            window.focus();
                            window.print();
                        }

                        return;
                    }

                    window.focus();
                    window.print();
                });

                window.addEventListener('beforeunload', function () {
                    if (fileUrl) {
                        URL.revokeObjectURL(fileUrl);
                    }
                });

                initialize();
            })();
        </script>
    @endif

    <script>
        function returnToSystem(event) {
            if (event) {
                event.preventDefault();
            }

            const systemUrl = @json(route('documents.show', $document));

            if (window.opener && !window.opener.closed) {
                try {
                    window.opener.location.href = systemUrl;
                    window.opener.focus();
                    window.close();

                    return false;
                } catch (error) {
                    // Fall back to normal navigation.
                }
            }

            window.location.href = systemUrl;

            return false;
        }
    </script>
</body>
</html>
