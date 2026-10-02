@extends('layouts.app')

@section('title', 'QR Verification | Relief Assistance')

@section('content')
<div class="container-fluid py-4">

    {{-- HEADER --}}
    <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-2">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('relief-vouchers.index') }}">Relief Vouchers</a></li>
                    <li class="breadcrumb-item active" aria-current="page">QR Verification</li>
                </ol>
            </nav>
            <h1 class="h3 fw-bold mb-1"><i class="bi bi-qr-code-scan me-2 text-primary"></i>Voucher Scanner</h1>
            <div class="text-muted">Hold a voucher up to the camera. It is verified and claimed automatically.</div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <div class="vq-stat"><b id="statClaimed" class="text-success">0</b><span>Claimed</span></div>
            <div class="vq-stat"><b id="statRejected" class="text-danger">0</b><span>Rejected</span></div>
            <a href="{{ route('relief-vouchers.index') }}" class="btn btn-outline-secondary ms-1">
                <i class="bi bi-arrow-left me-1"></i>Relief Vouchers
            </a>
        </div>
    </div>

    <div class="row g-4">

        {{-- SCANNER --}}
        <div class="col-12 col-xl-7">
            <div class="vq-stage" id="stage">
                <div id="qrReader"></div>
                <div class="vq-dots"></div>

                {{-- Spotlight frame: the area outside dims, the frame locks on when a QR is found --}}
                <div class="vq-frame" id="scanFrame">
                    <i class="c1"></i><i class="c2"></i><i class="c3"></i><i class="c4"></i>
                    <div class="vq-beam"></div>
                </div>

                <div class="vq-top">
                    <span class="vq-pill" id="livePill" data-state="off"><span class="vq-dot"></span><span id="liveText">Camera off</span></span>
                    <span class="vq-pill d-none d-sm-inline-flex"><i class="bi bi-aspect-ratio"></i>Keep the QR inside the frame</span>
                </div>

                <div class="vq-idle" id="idleCover">
                    <i class="bi bi-camera-video-off fs-1 mb-2"></i>
                    <div class="fw-semibold fs-5" id="idleTitle">Camera is off</div>
                    <div class="small opacity-75 mb-3 px-4 text-center" id="idleText">Start the camera to begin scanning vouchers.</div>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="idleStart">
                        <i class="bi bi-camera-video me-1"></i>Start camera
                    </button>
                </div>

                <div class="vq-result" id="vqResult" role="status" aria-live="polite">
                    <i id="vqIcon" class="bi bi-qr-code-scan"></i>
                    <div class="min-w-0">
                        <div class="fw-bold text-truncate" id="vqTitle">Title</div>
                        <div class="small opacity-75" id="vqMsg"></div>
                    </div>
                </div>

                <div class="vq-dock">
                    <select id="cameraSelect" class="form-select form-select-sm" aria-label="Choose camera" disabled>
                        <option value="">Cameras</option>
                    </select>
                    <button type="button" class="vq-btn" id="soundBtn" title="Sound on/off" aria-label="Toggle sound"><i class="bi bi-volume-up"></i></button>
                    <button type="button" class="vq-btn d-none" id="torchBtn" title="Flashlight" aria-label="Toggle flashlight"><i class="bi bi-lightning-charge"></i></button>
                    <button type="button" class="vq-btn main" id="cameraToggle"><i class="bi bi-camera-video me-1"></i>Start</button>
                </div>
            </div>
            <div id="scanStatus" class="small text-muted mt-2 px-1">Preparing camera…</div>
        </div>

        {{-- SIDE PANEL --}}
        <div class="col-12 col-xl-5 d-flex flex-column gap-4">

            {{-- Manual entry --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-1"><i class="bi bi-keyboard me-2"></i>Type a code instead</h5>
                    <div class="small text-muted mb-3">Use this when the QR is damaged or the camera is unavailable.</div>
                    <form method="POST" action="{{ route('relief-vouchers.verify-qr') }}" id="qrVerificationForm" novalidate>
                        @csrf
                        <div class="input-group input-group-lg">
                            <input type="text" name="voucher_code" id="voucher_code" class="form-control font-monospace"
                                   placeholder="RV-P1-XXXXXXXX" autocomplete="off" maxlength="255" aria-label="Voucher code">
                            <button type="submit" id="verifyButton" class="btn btn-primary px-4">Verify</button>
                        </div>
                        <div id="manualMsg" class="form-text" aria-live="polite">Example: RV-P1-XXXXXXXX</div>
                    </form>
                </div>
            </div>

            {{-- Activity --}}
            <div class="card border-0 shadow-sm flex-grow-1">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold mb-1"><i class="bi bi-people me-2"></i>This session</h5>
                            <div class="small text-muted">Newest scan first.</div>
                        </div>
                        <span class="badge rounded-pill text-bg-primary" id="claimedCountBadge">0 claimed</span>
                    </div>

                    <div id="noClaimsMessage" class="text-center text-muted py-5 my-auto">
                        <i class="bi bi-qr-code fs-1 d-block mb-2 opacity-50"></i>
                        <div class="fw-semibold">Nothing scanned yet</div>
                        <div class="small">Claimed vouchers will show up here.</div>
                    </div>
                    <div id="feed" class="vq-feed"></div>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
.vq-stat{background:var(--bs-tertiary-bg);border:1px solid var(--bs-border-color);border-radius:14px;padding:.4rem 1rem;text-align:center;min-width:86px}
.vq-stat b{display:block;font-size:1.3rem;line-height:1.2}
.vq-stat span{font-size:.75rem;opacity:.65}

/* ---------- Stage ---------- */
.vq-stage{position:relative;container-type:size;height:min(74vh,620px);min-height:420px;border-radius:28px;overflow:hidden;background:#04060b;
    box-shadow:0 24px 60px -28px rgba(13,110,253,.55),0 0 0 1px rgba(255,255,255,.07)}
#qrReader,#qrReader__scan_region{width:100%!important;height:100%!important;border:0!important;position:absolute;inset:0}
#qrReader video{width:100%!important;height:100%!important;object-fit:cover!important;display:block}
#qrReader img,#qr-shaded-region{display:none!important}
.vq-dots{position:absolute;inset:0;pointer-events:none;background-image:radial-gradient(rgba(255,255,255,.14) 1px,transparent 1.4px);background-size:22px 22px}

/* ---------- Spotlight frame ---------- */
.vq-frame{position:absolute;inset:0;margin:auto;width:min(70cqmin,320px);aspect-ratio:1;border-radius:22px;pointer-events:none;
    box-shadow:0 0 0 200vmax rgba(3,6,12,.58);transition:transform .35s cubic-bezier(.2,.9,.3,1.3)}
.vq-frame i{position:absolute;width:40px;height:40px;border:4px solid #fff;transition:border-color .25s}
.vq-frame .c1{top:-2px;left:-2px;border-right:0;border-bottom:0;border-radius:22px 0 0 0}
.vq-frame .c2{top:-2px;right:-2px;border-left:0;border-bottom:0;border-radius:0 22px 0 0}
.vq-frame .c3{bottom:-2px;left:-2px;border-right:0;border-top:0;border-radius:0 0 0 22px}
.vq-frame .c4{bottom:-2px;right:-2px;border-left:0;border-top:0;border-radius:0 0 22px 0}
.vq-beam{position:absolute;left:5%;right:5%;top:6%;height:2px;background:linear-gradient(90deg,transparent,#7cb4ff,transparent);
    box-shadow:0 0 18px 4px rgba(124,180,255,.55);animation:vqBeam 2.2s ease-in-out infinite}
@keyframes vqBeam{0%,100%{top:6%;opacity:.25}50%{top:94%;opacity:1}}
.vq-frame::after{content:"";position:absolute;inset:0;border-radius:22px;border:3px solid transparent;opacity:0}
.vq-frame.lock{transform:scale(.9)}
.vq-frame.lock .vq-beam{animation:none;opacity:0}
.vq-frame.lock::after{animation:vqRing .8s ease-out}
.vq-frame.lock.info i{border-color:#7cb4ff}
.vq-frame.lock.ok i{border-color:#22c55e}
.vq-frame.lock.warn i{border-color:#f59e0b}
.vq-frame.lock.err i{border-color:#ef4444}
.vq-frame.lock.ok::after{border-color:#22c55e}
.vq-frame.lock.err::after{border-color:#ef4444}
.vq-frame.lock.warn::after,.vq-frame.lock.info::after{border-color:#7cb4ff}
@keyframes vqRing{0%{opacity:.9;transform:scale(1)}100%{opacity:0;transform:scale(1.35)}}

/* ---------- Glass HUD ---------- */
.vq-pill,.vq-dock,.vq-result{background:rgba(12,18,32,.6);backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
    border:1px solid rgba(255,255,255,.14);color:#fff}
.vq-top{position:absolute;top:14px;left:14px;right:14px;display:flex;justify-content:space-between;gap:8px;z-index:6}
.vq-pill{display:inline-flex;align-items:center;gap:.45rem;border-radius:999px;padding:.35rem .85rem;font-size:.8rem}
.vq-dot{width:8px;height:8px;border-radius:50%;background:#94a3b8}
#livePill[data-state=live] .vq-dot{background:#22c55e;animation:vqPulse 1.6s infinite}
#livePill[data-state=err] .vq-dot{background:#ef4444}
@keyframes vqPulse{0%{box-shadow:0 0 0 0 rgba(34,197,94,.7)}100%{box-shadow:0 0 0 9px rgba(34,197,94,0)}}

.vq-idle{position:absolute;inset:0;z-index:5;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#fff;background:rgba(4,6,11,.94)}
.vq-idle.d-none{display:none!important}

.vq-result{position:absolute;z-index:7;left:16px;right:16px;bottom:88px;max-width:440px;margin:0 auto;border-radius:18px;padding:.8rem 1rem;
    display:flex;align-items:center;gap:.8rem;border-left:5px solid #7cb4ff;opacity:0;transform:translateY(16px);pointer-events:none;transition:.3s}
.vq-result.show{opacity:1;transform:none}
.vq-result>i{font-size:1.8rem}
.vq-result.ok{border-left-color:#22c55e}.vq-result.ok>i{color:#22c55e}
.vq-result.err{border-left-color:#ef4444}.vq-result.err>i{color:#ef4444}
.vq-result.warn{border-left-color:#f59e0b}.vq-result.warn>i{color:#f59e0b}

.vq-dock{position:absolute;z-index:8;bottom:16px;left:50%;transform:translateX(-50%);display:flex;align-items:center;gap:.5rem;
    border-radius:999px;padding:.4rem;max-width:calc(100% - 24px)}
.vq-dock select{background-color:transparent;color:#fff;border:0;box-shadow:none;max-width:170px;min-width:0;font-size:.8rem}
.vq-dock select option{color:#111}
.vq-btn{width:42px;height:42px;flex:none;border-radius:50%;border:0;background:rgba(255,255,255,.14);color:#fff;display:inline-flex;align-items:center;justify-content:center}
.vq-btn:hover{background:rgba(255,255,255,.26)}
.vq-btn.on{background:#f59e0b;color:#111}
.vq-btn.main{width:auto;padding:0 1.1rem;border-radius:999px;font-weight:600;background:#0d6efd}
.vq-btn.main.stop{background:#dc3545}

/* ---------- Feed ---------- */
.vq-feed{display:flex;flex-direction:column;gap:.5rem;max-height:420px;overflow:auto}
.vq-item{display:flex;align-items:center;gap:.75rem;padding:.65rem .75rem;border-radius:14px;background:var(--bs-tertiary-bg);border-left:4px solid #198754;animation:vqIn .3s ease-out}
.vq-item.err{border-left-color:#dc3545}
.vq-av{width:38px;height:38px;flex:none;border-radius:50%;display:grid;place-items:center;font-weight:700;font-size:.85rem;background:rgba(25,135,84,.15);color:#198754}
.vq-item.err .vq-av{background:rgba(220,53,69,.15);color:#dc3545}
.min-w-0{min-width:0}
@keyframes vqIn{from{opacity:0;transform:translateY(-8px)}}

@media (max-width:767.98px){.vq-stage{min-height:380px}.vq-dock select{max-width:110px}}
@media (prefers-reduced-motion:reduce){.vq-beam,.vq-dot,.vq-frame::after,.vq-item{animation:none!important}}
</style>

<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const $ = id => document.getElementById(id);
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    const form = $('qrVerificationForm'), input = $('voucher_code'), verifyBtn = $('verifyButton'),
          sel = $('cameraSelect'), toggle = $('cameraToggle'), torchBtn = $('torchBtn'), soundBtn = $('soundBtn'),
          frame = $('scanFrame'), pill = $('livePill'), idle = $('idleCover'), feed = $('feed');

    if (typeof Html5Qrcode === 'undefined') {
        setIdle('Scanner library failed to load', 'Check your connection and reload. You can still type the code manually.', true);
        toggle.disabled = $('idleStart').disabled = true;
        return;
    }

    const qr = new Html5Qrcode('qrReader', {
        formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
        experimentalFeatures: { useBarCodeDetectorIfSupported: true }, // native, faster detection when the browser supports it
        verbose: false
    });

    let running = false, busy = false, soundOn = true, torchOn = false, resumeOnShow = false;
    let lastCode = null, lastSeen = 0, claimed = 0, rejected = 0, flashTimer = null;
    const done = new Set();

    /* ---------- UI helpers ---------- */
    function setStatus(text, cls = 'secondary') { const s = $('scanStatus'); s.textContent = text; s.className = 'small mt-2 px-1 text-' + cls; }

    function setIdle(title, text, isError) {
        $('idleTitle').textContent = title; $('idleText').textContent = text;
        idle.querySelector('.bi').className = 'bi fs-1 mb-2 ' + (isError ? 'bi-exclamation-triangle text-warning' : 'bi-camera-video-off');
    }

    function setRunning(on, error) {
        running = on;
        idle.classList.toggle('d-none', on);
        pill.dataset.state = on ? 'live' : (error ? 'err' : 'off');
        $('liveText').textContent = on ? 'Scanning' : (error ? 'Camera error' : 'Camera off');
        toggle.classList.toggle('stop', on);
        toggle.innerHTML = on ? '<i class="bi bi-stop-circle me-1"></i>Stop' : '<i class="bi bi-camera-video me-1"></i>Start';
        toggle.disabled = false;
        if (!on) torchBtn.classList.add('d-none');
    }

    function flash(type, title, msg) {
        const r = $('vqResult');
        r.className = 'vq-result show ' + type;
        $('vqIcon').className = 'bi ' + ({ok:'bi-check-circle-fill', err:'bi-x-circle-fill', warn:'bi-exclamation-triangle-fill', info:'bi-qr-code-scan'}[type]);
        $('vqTitle').textContent = title; $('vqMsg').textContent = msg || '';
        clearTimeout(flashTimer);
        flashTimer = setTimeout(() => r.classList.remove('show'), type === 'info' ? 1800 : 3200);
    }

    function lock(type) { frame.className = 'vq-frame lock ' + type; }
    function unlock() { frame.className = 'vq-frame'; }

    function beep(ok) {
        if (soundOn) {
            try {
                const a = beep.ctx || (beep.ctx = new (window.AudioContext || window.webkitAudioContext)());
                const o = a.createOscillator(), g = a.createGain();
                o.type = ok ? 'sine' : 'square'; o.frequency.value = ok ? 880 : 220; g.gain.value = .08;
                o.connect(g); g.connect(a.destination); o.start(); o.stop(a.currentTime + (ok ? .15 : .3));
            } catch (e) {}
        }
        if (navigator.vibrate) navigator.vibrate(ok ? 80 : [120, 60, 120]);
    }

    function cameraError(e) {
        const m = (e && e.message ? e.message : String(e || '')).toLowerCase();
        if (m.includes('permission') || m.includes('notallowed')) return 'Camera access was blocked. Allow it in your browser settings, then reload.';
        if (m.includes('notfound') || m.includes('no camera')) return 'No camera was found on this device.';
        if (m.includes('notreadable')) return 'Another app or tab is using the camera. Close it and try again.';
        if (m.includes('secure')) return 'Camera access needs HTTPS or localhost.';
        return e && e.message ? e.message : 'Unable to start the camera.';
    }

    /* ---------- Camera ---------- */
    async function fillCameras() {
        try {
            const cams = await Html5Qrcode.getCameras();
            let active = null;
            try { active = qr.getRunningTrackSettings().deviceId; } catch (e) {}
            sel.innerHTML = cams.map((c, i) => `<option value="${esc(c.id)}">${esc(c.label || 'Camera ' + (i + 1))}</option>`).join('');
            if (active) sel.value = active;
            sel.disabled = cams.length < 2;
        } catch (e) {}
    }

    function checkTorch() {
        try { torchBtn.classList.toggle('d-none', !qr.getRunningTrackCapabilities().torch); } catch (e) {}
        torchOn = false; torchBtn.classList.remove('on');
    }

    async function start(id) {
        if (running) return;
        toggle.disabled = true;
        setStatus('Starting camera…', 'warning');
        try {
            await qr.start(
                id ? { deviceId: { exact: id } } : { facingMode: 'environment' },
                {
                    fps: 15,
                    qrbox: (w, h) => { const s = Math.min(360, Math.floor(Math.min(w, h) * .75)); return { width: s, height: s }; }
                },
                onScan,
                () => {} // fires on every frame without a QR, intentionally silent
            );
            setRunning(true);
            setStatus('Scanning. Vouchers are claimed as soon as they are detected.', 'success');
            await fillCameras();
            checkTorch();
        } catch (e) {
            console.error(e);
            setRunning(false, true);
            setIdle('Camera unavailable', cameraError(e), true);
            setStatus(cameraError(e), 'danger');
        }
    }

    async function stop() {
        if (!running) return;
        try { await qr.stop(); } catch (e) {}
        setRunning(false);
        unlock();
        setIdle('Camera is off', 'Start the camera to begin scanning vouchers.');
        setStatus('Camera stopped.');
    }

    /* ---------- Auto detect ---------- */
    async function onScan(text) {
        const code = String(text || '').trim();
        if (!code || busy) return;

        const now = Date.now();
        if (code === lastCode && now - lastSeen < 4000) { lastSeen = now; return; } // same QR still in view
        lastCode = code; lastSeen = now;

        input.value = code;
        if (done.has(code)) {
            lock('warn'); beep(false);
            flash('warn', 'Already claimed', code + ' was claimed earlier in this session.');
            setTimeout(unlock, 1600);
            return;
        }
        await verify(code, 'scan');
    }

    /* ---------- Verify (shared by scanner + manual) ---------- */
    async function verify(code, source) {
        if (busy) return false;
        busy = true;
        lock('info');
        flash('info', 'QR detected', 'Verifying ' + code + '…');
        setStatus('Verifying ' + code + '…', 'warning');
        let ok = false;

        try {
            const token = document.querySelector('meta[name="csrf-token"]')?.content || form.querySelector('input[name="_token"]')?.value;
            if (!token) throw new Error('CSRF token could not be found. Reload the page.');

            const fd = new FormData();
            fd.append('voucher_code', code); fd.append('_token', token);
            const res = await fetch(form.action, {
                method: 'POST', body: fd, credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            const data = await res.json().catch(() => null);
            if (!data) throw new Error('The server returned an invalid response.');
            if (!res.ok || !data.success) throw new Error(data.message || 'Voucher verification failed.');

            ok = true;
            done.add(code);
            const v = data.voucher || {};
            claimed++;
            addFeed(true, v.claimant_name || 'Unknown claimant', v.voucher_code || code,
                [v.purok, v.assistance_type || 'Relief assistance'].filter(Boolean).join(' · '), v.claimed_at, v.details_url);
            lock('ok'); beep(true);
            flash('ok', v.claimant_name || 'Voucher claimed', 'Claim recorded. Ready for the next voucher.');
            setStatus('Claim recorded. Ready for the next voucher.', 'success');
            if (source === 'manual') { input.value = ''; manualMsg('Claim recorded for ' + (v.claimant_name || code) + '.', 'success'); }
        } catch (e) {
            rejected++;
            addFeed(false, 'Rejected', code, e.message);
            lock('err'); beep(false);
            flash('err', 'Not claimed', e.message);
            setStatus(e.message, 'danger');
            if (source === 'manual') manualMsg(e.message, 'danger');
        } finally {
            $('statClaimed').textContent = claimed;
            $('statRejected').textContent = rejected;
            setTimeout(() => { busy = false; unlock(); }, 2200); // brief pause so results are readable
        }
        return ok;
    }

    function manualMsg(text, cls) { const m = $('manualMsg'); m.textContent = text; m.className = 'form-text text-' + cls; }

    function addFeed(ok, title, code, sub, time, url) {
        $('noClaimsMessage').classList.add('d-none');
        $('claimedCountBadge').textContent = claimed + ' claimed';
        const initials = ok ? title.split(/\s+/).slice(0, 2).map(w => w[0] || '').join('').toUpperCase() : '!';
        const el = document.createElement('div');
        el.className = 'vq-item' + (ok ? '' : ' err');
        el.innerHTML = `
            <div class="vq-av">${esc(initials)}</div>
            <div class="flex-grow-1 min-w-0">
                <div class="fw-semibold text-truncate">${esc(title)}</div>
                <div class="small text-muted text-truncate"><span class="font-monospace">${esc(code)}</span>${sub ? ' · ' + esc(sub) : ''}</div>
            </div>
            <div class="text-end small text-muted flex-none">
                <div>${esc(time || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }))}</div>
                ${url ? `<a href="${esc(url)}" class="link-primary text-decoration-none">Details</a>` : ''}
            </div>`;
        feed.prepend(el);
    }

    /* ---------- Events ---------- */
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const code = input.value.trim();
        if (!code) { manualMsg('Enter a voucher code first.', 'warning'); input.focus(); return; }
        verifyBtn.disabled = true;
        verifyBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        await verify(code, 'manual');
        verifyBtn.disabled = false;
        verifyBtn.textContent = 'Verify';
    });

    const toggleCamera = () => running ? stop() : start(sel.value || null);
    toggle.addEventListener('click', toggleCamera);
    $('idleStart').addEventListener('click', () => start(sel.value || null));

    sel.addEventListener('change', async () => {
        if (!sel.value || !running) return;
        const id = sel.value;
        await stop();
        await new Promise(r => setTimeout(r, 250)); // let the old camera release
        start(id);
    });

    soundBtn.addEventListener('click', () => {
        soundOn = !soundOn;
        soundBtn.innerHTML = `<i class="bi ${soundOn ? 'bi-volume-up' : 'bi-volume-mute'}"></i>`;
    });

    torchBtn.addEventListener('click', async () => {
        try {
            torchOn = !torchOn;
            await qr.applyVideoConstraints({ advanced: [{ torch: torchOn }] });
            torchBtn.classList.toggle('on', torchOn);
        } catch (e) { torchOn = false; }
    });

    // Release the camera when the tab is hidden, resume when it returns
    document.addEventListener('visibilitychange', async () => {
        if (document.hidden && running) { resumeOnShow = true; await stop(); }
        else if (!document.hidden && resumeOnShow) { resumeOnShow = false; start(sel.value || null); }
    });

    /* ---------- Go ---------- */
    start(); // camera starts automatically
});
</script>
@endsection
