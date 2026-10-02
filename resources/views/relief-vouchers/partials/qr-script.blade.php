{{-- resources/views/relief-vouchers/partials/qr-script.blade.php
     Renders any element with data-qr="TEXT" as a scannable SVG QR code (black on white, quiet zone included).
     The text encoded is the voucher code, which is what the scanner page sends to the server. --}}
@once
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.js"></script>
<script>
(function () {
    window.rvQr = function (text) {
        if (typeof qrcode !== 'function' || !text) return '';
        var q = qrcode(0, 'M');
        q.addData(String(text));
        q.make();
        var n = q.getModuleCount(), m = 4, d = '';
        for (var r = 0; r < n; r++) {
            for (var c = 0; c < n; c++) {
                if (q.isDark(r, c)) d += 'M' + (c + m) + ',' + (r + m) + 'h1v1h-1z';
            }
        }
        var s = n + m * 2;
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + s + ' ' + s + '" width="100%" height="100%" shape-rendering="crispEdges" role="img" aria-label="QR code">' +
               '<rect width="' + s + '" height="' + s + '" fill="#fff"/><path d="' + d + '" fill="#000"/></svg>';
    };

    function render() {
        document.querySelectorAll('[data-qr]').forEach(function (el) {
            var svg = window.rvQr(el.getAttribute('data-qr'));
            el.innerHTML = svg || '<span style="font:700 11px monospace;word-break:break-all;color:#000">' + el.getAttribute('data-qr') + '</span>';
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', render); else render();
})();
</script>
@endonce