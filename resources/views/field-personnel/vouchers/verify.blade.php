
@extends('field-personnel.layouts.app')

@section('title', 'Relief Voucher Verification | e-Poblacion RMS')

@section('page-heading')
    <div class="fc-page-heading">
        <div>
            <span class="fc-eyebrow">FIELD COLLECTION</span>
            <h1>Relief Voucher Verification</h1>
            <p>Scan or enter a voucher code to check its current status.</p>
        </div>
    </div>
@endsection

@section('content')

<style>
    .fv-page {
        display: grid;
        gap: 18px;
    }

    .fv-notice {
        padding: 14px 16px;
        color: #53685a;
        background: #f0f8f2;
        border: 1px solid #dcefe0;
        border-left: 4px solid #29945b;
        border-radius: 10px;
        font-size: 13px;
        line-height: 1.6;
    }

    .fv-card {
        padding: 20px;
        background: #fff;
        border: 1px solid #e5ece7;
        border-radius: 15px;
    }

    .fv-card h2 {
        margin: 0 0 7px;
        color: #26382d;
        font-size: 17px;
        font-weight: 800;
    }

    .fv-description {
        margin: 0 0 17px;
        color: #78847d;
        font-size: 13px;
        line-height: 1.6;
    }

    .fv-scanner {
        overflow: hidden;
        width: 100%;
        max-width: 520px;
        min-height: 250px;
        margin: 0 auto;
        background: #101713;
        border-radius: 13px;
    }

    .fv-scanner video {
        object-fit: cover;
    }

    .fv-scanner-placeholder {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 250px;
        padding: 25px;
        color: #d9e7de;
        text-align: center;
    }

    .fv-scanner-placeholder .fv-camera-icon {
        margin-bottom: 12px;
        font-size: 42px;
    }

    .fv-scanner-placeholder strong {
        margin-bottom: 6px;
        font-size: 14px;
    }

    .fv-scanner-placeholder span {
        max-width: 300px;
        color: #aabbb0;
        font-size: 12px;
        line-height: 1.5;
    }

    .fv-camera-controls {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 9px;
        margin-top: 14px;
    }

    .fv-button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        min-height: 43px;
        padding: 10px 16px;
        border: 0;
        border-radius: 9px;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
    }

    .fv-button:disabled {
        cursor: not-allowed;
        opacity: .55;
    }

    .fv-primary {
        color: #fff;
        background: #21834f;
    }

    .fv-primary:hover {
        background: #176b45;
    }

    .fv-light {
        color: #365344;
        background: #eaf2ec;
    }

    .fv-danger {
        color: #a83232;
        background: #fff0f0;
    }

    .fv-field label {
        display: block;
        margin-bottom: 7px;
        color: #34483b;
        font-size: 13px;
        font-weight: 700;
    }

    .fv-input-group {
        display: flex;
        gap: 9px;
    }

    .fv-input-group input {
        flex: 1;
        min-width: 0;
        min-height: 45px;
        padding: 10px 12px;
        color: #26352d;
        background: #fff;
        border: 1px solid #dce5df;
        border-radius: 9px;
        outline: none;
        font-size: 14px;
        text-transform: uppercase;
    }

    .fv-input-group input:focus {
        border-color: #29945b;
        box-shadow: 0 0 0 3px rgba(41,148,91,.10);
    }

    .fv-help {
        display: block;
        margin-top: 7px;
        color: #829087;
        font-size: 11px;
        line-height: 1.5;
    }

    .fv-status-message {
        display: none;
        padding: 13px 15px;
        border-radius: 10px;
        font-size: 13px;
        line-height: 1.5;
    }

    .fv-status-message.visible {
        display: block;
    }

    .fv-status-message.info {
        color: #365d78;
        background: #edf6fc;
        border: 1px solid #d4e8f5;
    }

    .fv-status-message.error {
        color: #942f2f;
        background: #fff1f1;
        border: 1px solid #f0d0d0;
    }

    .fv-status-message.success {
        color: #24643e;
        background: #eff9f1;
        border: 1px solid #d5ecd9;
    }

    .fv-result {
        display: none;
        overflow: hidden;
        border: 1px solid #e2eae4;
        border-radius: 13px;
    }

    .fv-result.visible {
        display: block;
    }

    .fv-result-header {
        padding: 16px;
        background: #f7faf8;
        border-bottom: 1px solid #e2eae4;
    }

    .fv-result-header h3 {
        margin: 0 0 5px;
        color: #26382d;
        font-size: 16px;
        font-weight: 800;
    }

    .fv-result-header p {
        margin: 0;
        color: #77847c;
        font-size: 12px;
    }

    .fv-result-body {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 15px;
        padding: 17px;
    }

    .fv-detail {
        min-width: 0;
    }

    .fv-detail.full {
        grid-column: 1 / -1;
    }

    .fv-detail-label {
        display: block;
        margin-bottom: 4px;
        color: #829087;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .fv-detail-value {
        overflow-wrap: anywhere;
        color: #2c3c32;
        font-size: 13px;
        font-weight: 700;
    }

    .fv-status-pill {
        display: inline-flex;
        align-items: center;
        padding: 6px 10px;
        color: #365344;
        background: #eaf2ec;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 800;
    }

    .fv-readonly {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        padding: 13px 15px;
        color: #5e6870;
        background: #f5f7f8;
        border-radius: 10px;
        font-size: 12px;
        line-height: 1.6;
    }

    @media (max-width: 520px) {
        .fv-card {
            padding: 16px;
        }

        .fv-input-group {
            flex-direction: column;
        }

        .fv-input-group .fv-button {
            width: 100%;
        }

        .fv-camera-controls {
            flex-direction: column;
        }

        .fv-camera-controls .fv-button {
            width: 100%;
        }

        .fv-result-body {
            grid-template-columns: 1fr;
        }

        .fv-detail.full {
            grid-column: auto;
        }
    }
</style>

<div class="fv-page">

    <div class="fv-notice">
        <strong>Read-only verification:</strong>
        This tool checks voucher information only. It does not claim,
        release, distribute, or change the status of a relief voucher.
        Actual voucher claiming remains under the authorized RMS process.
    </div>

    {{-- QR SCANNER --}}
    <section class="fv-card">
        <h2>Scan Voucher QR Code</h2>
        <p class="fv-description">
            Allow camera access and position the voucher QR code inside
            the scanner area.
        </p>

        <div class="fv-scanner">
            <div id="scannerPlaceholder" class="fv-scanner-placeholder">
                <div class="fv-camera-icon">▦</div>
                <strong>QR Scanner Ready</strong>
                <span>
                    Press Start Camera to begin scanning a relief voucher.
                </span>
            </div>

            <div id="reader" style="width:100%;"></div>
        </div>

        <div class="fv-camera-controls">
            <button type="button"
                    class="fv-button fv-primary"
                    id="startScannerButton">
                Start Camera
            </button>

            <button type="button"
                    class="fv-button fv-light"
                    id="stopScannerButton"
                    disabled>
                Stop Camera
            </button>

            <button type="button"
                    class="fv-button fv-light"
                    id="switchCameraButton"
                    disabled>
                Switch Camera
            </button>
        </div>

        <div style="height:12px;"></div>

        <div id="scannerStatus"
             class="fv-status-message"
             role="status"
             aria-live="polite"></div>
    </section>

    {{-- MANUAL LOOKUP --}}
    <section class="fv-card">
        <h2>Manual Voucher Lookup</h2>
        <p class="fv-description">
            You may also enter the voucher code printed on the voucher.
        </p>

        <form id="voucherLookupForm">
            <div class="fv-field">
                <label for="voucherCode">Voucher Code</label>

                <div class="fv-input-group">
                    <input type="text"
                           id="voucherCode"
                           name="voucher_code"
                           placeholder="Enter voucher code"
                           maxlength="100"
                           autocomplete="off"
                           autocapitalize="characters"
                           required>

                    <button type="submit"
                            class="fv-button fv-primary"
                            id="lookupButton">
                        Verify Code
                    </button>
                </div>

                <span class="fv-help">
                    Enter the complete voucher code. Avoid extra spaces.
                </span>
            </div>
        </form>
    </section>

    {{-- LOOKUP FEEDBACK --}}
    <div id="lookupMessage"
         class="fv-status-message"
         role="status"
         aria-live="polite"></div>

    {{-- LOOKUP RESULT --}}
    <section class="fv-card">
        <h2>Verification Result</h2>
        <p class="fv-description">
            Voucher details will appear here after a successful lookup.
        </p>

        <div id="voucherResult" class="fv-result">

            <div class="fv-result-header">
                <h3 id="resultTitle">Voucher Information</h3>
                <p id="resultCode"></p>
            </div>

            <div class="fv-result-body">

                <div class="fv-detail">
                    <span class="fv-detail-label">Voucher Status</span>
                    <span class="fv-detail-value">
                        <span id="resultStatus" class="fv-status-pill">—</span>
                    </span>
                </div>

                <div class="fv-detail">
                    <span class="fv-detail-label">Assistance Type</span>
                    <span class="fv-detail-value" id="resultAssistance">—</span>
                </div>

                <div class="fv-detail">
                    <span class="fv-detail-label">Household / Beneficiary</span>
                    <span class="fv-detail-value" id="resultBeneficiary">—</span>
                </div>

                <div class="fv-detail">
                    <span class="fv-detail-label">Purok</span>
                    <span class="fv-detail-value" id="resultPurok">—</span>
                </div>

                <div class="fv-detail">
                    <span class="fv-detail-label">Distribution Schedule</span>
                    <span class="fv-detail-value" id="resultSchedule">—</span>
                </div>

                <div class="fv-detail">
                    <span class="fv-detail-label">Allocated Quantity</span>
                    <span class="fv-detail-value" id="resultQuantity">—</span>
                </div>

            </div>
        </div>

        <div id="emptyResult" class="fv-readonly">
            <span>ⓘ</span>
            <span>
                No voucher has been checked yet. Scan a QR code or enter
                a voucher code to display its verification result.
            </span>
        </div>
    </section>

    <div class="fv-readonly">
        <span>🔒</span>
        <span>
            Verification is informational only. If the voucher needs to be
            claimed or released, refer the beneficiary to the authorized
            Barangay personnel and the official voucher distribution process.
        </span>
    </div>

    <div>
        <a href="{{ url('/field-personnel') }}"
           class="fv-button fv-light"
           style="width:100%;">
            ← Back to Field Collection Dashboard
        </a>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const lookupUrl = @json(url('/field-personnel/vouchers/lookup'));
    const csrfToken = @json(csrf_token());

    const form = document.getElementById('voucherLookupForm');
    const codeInput = document.getElementById('voucherCode');
    const lookupButton = document.getElementById('lookupButton');

    const startButton = document.getElementById('startScannerButton');
    const stopButton = document.getElementById('stopScannerButton');
    const switchButton = document.getElementById('switchCameraButton');

    const scannerStatus = document.getElementById('scannerStatus');
    const lookupMessage = document.getElementById('lookupMessage');

    const resultPanel = document.getElementById('voucherResult');
    const emptyResult = document.getElementById('emptyResult');

    const scannerPlaceholder = document.getElementById('scannerPlaceholder');

    let scanner = null;
    let scannerRunning = false;
    let cameras = [];
    let currentCameraIndex = 0;
    let lookupInProgress = false;
    let lastScannedCode = '';

    function showMessage(element, message, type) {
        element.textContent = message;
        element.className = 'fv-status-message visible ' + type;
    }

    function hideMessage(element) {
        element.textContent = '';
        element.className = 'fv-status-message';
    }

    function setText(id, value) {
        document.getElementById(id).textContent =
            value === null || value === undefined || value === ''
                ? '—'
                : String(value);
    }

    function clearResult() {
        resultPanel.classList.remove('visible');
        emptyResult.style.display = 'flex';
    }

    function displayResult(voucher) {
        if (!voucher || typeof voucher !== 'object') {
            showMessage(
                lookupMessage,
                'The server returned an invalid voucher response.',
                'error'
            );
            return;
        }

        setText('resultTitle', 'Voucher Information');
        setText('resultCode', voucher.voucher_code);
        setText('resultStatus', voucher.status);
        setText('resultAssistance', voucher.assistance_type);
        setText('resultBeneficiary', voucher.beneficiary_name);
        setText('resultPurok', voucher.purok);
        setText('resultSchedule', voucher.distribution_schedule);
        setText('resultQuantity', voucher.allocated_quantity);

        resultPanel.classList.add('visible');
        emptyResult.style.display = 'none';

        showMessage(
            lookupMessage,
            'Voucher information retrieved. No voucher status or distribution record was changed.',
            'success'
        );
    }

    async function lookupVoucher(code) {
        const normalizedCode = String(code || '').trim();

        if (!normalizedCode) {
            showMessage(
                lookupMessage,
                'Please enter or scan a voucher code.',
                'error'
            );
            return;
        }

        if (lookupInProgress) {
            return;
        }

        lookupInProgress = true;
        lookupButton.disabled = true;
        startButton.disabled = true;

        hideMessage(scannerStatus);

        showMessage(
            lookupMessage,
            'Checking voucher information...',
            'info'
        );

        try {
            const response = await fetch(lookupUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    voucher_code: normalizedCode
                })
            });

            const data = await response.json().catch(function () {
                return {};
            });

            if (response.status === 401 || response.status === 419) {
                throw new Error(
                    'Your session may have expired. Please log in again.'
                );
            }

            if (response.status === 403) {
                throw new Error(
                    'You are not authorized to verify this voucher.'
                );
            }

            if (response.status === 404) {
                throw new Error(
                    'Voucher not found. Check the code and try again.'
                );
            }

            if (!response.ok || data.success !== true) {
                throw new Error(
                    data.message || 'Unable to retrieve voucher information.'
                );
            }

            displayResult(data.voucher);

        } catch (error) {
            showMessage(
                lookupMessage,
                error.message || 'An unexpected error occurred.',
                'error'
            );
        } finally {
            lookupInProgress = false;
            lookupButton.disabled = false;
            startButton.disabled = false;
        }
    }

    form.addEventListener('submit', function (event) {
        event.preventDefault();

        const code = codeInput.value.trim();

        if (!code) {
            codeInput.focus();
            return;
        }

        lookupVoucher(code);
    });

    codeInput.addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });

    function onQrCodeDetected(decodedText) {
        const code = String(decodedText || '').trim();

        if (!code || lookupInProgress || code === lastScannedCode) {
            return;
        }

        lastScannedCode = code;
        codeInput.value = code;

        showMessage(
            scannerStatus,
            'QR code detected. Retrieving voucher information...',
            'success'
        );

        lookupVoucher(code).finally(function () {
            window.setTimeout(function () {
                lastScannedCode = '';
            }, 2500);
        });
    }

    async function loadCameras() {
        if (typeof Html5Qrcode === 'undefined') {
            throw new Error(
                'QR scanner library could not be loaded. Check your internet connection.'
            );
        }

        cameras = await Html5Qrcode.getCameras();

        if (!cameras || cameras.length === 0) {
            throw new Error('No available camera was detected.');
        }
    }

    async function startScanner(cameraId) {
        if (typeof Html5Qrcode === 'undefined') {
            showMessage(
                scannerStatus,
                'QR scanner library is unavailable.',
                'error'
            );
            return;
        }

        try {
            if (!cameras.length) {
                await loadCameras();
            }

            if (!scanner) {
                scanner = new Html5Qrcode('reader');
            }

            scannerPlaceholder.style.display = 'none';

            const selectedCamera = cameraId || cameras[currentCameraIndex].id;

            await scanner.start(
                selectedCamera,
                {
                    fps: 10,
                    qrbox: function (width, height) {
                        const size = Math.min(
                            Math.floor(width * 0.75),
                            Math.floor(height * 0.75),
                            260
                        );

                        return {
                            width: size,
                            height: size
                        };
                    },
                    aspectRatio: 1
                },
                onQrCodeDetected,
                function () {
                    // Ignore individual frames without a detected QR code.
                }
            );

            scannerRunning = true;

            startButton.disabled = true;
            stopButton.disabled = false;
            switchButton.disabled = cameras.length < 2;

            showMessage(
                scannerStatus,
                'Camera active. Position the voucher QR code inside the scanning frame.',
                'success'
            );

        } catch (error) {
            scannerRunning = false;

            scannerPlaceholder.style.display = 'flex';

            startButton.disabled = false;
            stopButton.disabled = true;
            switchButton.disabled = true;

            showMessage(
                scannerStatus,
                error.message || 'Unable to start the camera. Check browser permissions.',
                'error'
            );
        }
    }

    async function stopScanner() {
        if (!scanner || !scannerRunning) {
            return;
        }

        try {
            await scanner.stop();
            scannerRunning = false;

            scannerPlaceholder.style.display = 'flex';

            startButton.disabled = false;
            stopButton.disabled = true;
            switchButton.disabled = true;

            showMessage(
                scannerStatus,
                'Camera stopped.',
                'info'
            );

        } catch (error) {
            showMessage(
                scannerStatus,
                'Unable to stop the camera cleanly. Please refresh the page if it remains active.',
                'error'
            );
        }
    }

    startButton.addEventListener('click', async function () {
        hideMessage(scannerStatus);
        await startScanner();
    });

    stopButton.addEventListener('click', stopScanner);

    switchButton.addEventListener('click', async function () {
        if (!cameras.length || cameras.length < 2) {
            return;
        }

        const wasRunning = scannerRunning;

        if (wasRunning) {
            await stopScanner();
        }

        currentCameraIndex =
            (currentCameraIndex + 1) % cameras.length;

        await startScanner(cameras[currentCameraIndex].id);
    });

    window.addEventListener('beforeunload', function () {
        if (scanner && scannerRunning) {
            scanner.stop().catch(function () {});
        }
    });

    clearResult();
});
</script>
@endpush
