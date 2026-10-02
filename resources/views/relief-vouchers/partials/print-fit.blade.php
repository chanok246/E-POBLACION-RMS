{{-- resources/views/relief-vouchers/partials/print-fit.blade.php
     1) Moves #rvpPrint directly under <body> while printing, so the app layout (navbar, sidebar, wrappers)
        can never add blank pages. It is put back afterwards.
     2) Scales .rvp-fit down (never up) if its content is taller than its .rvp-content window,
        so a sheet can never spill onto a second page. --}}
@once
<script>
(function () {
    var root = null, holder = null;

    function fitAll() {
        document.querySelectorAll('.rvp-content').forEach(function (box) {
            var f = box.querySelector('.rvp-fit');
            if (!f) return;
            f.style.transform = 'none';
            f.style.width = '100%';
            var avail = box.clientHeight, natural = f.scrollHeight;
            if (avail > 0 && natural > avail) {
                var s = Math.max(0.6, (avail - 1) / natural);
                f.style.transform = 'scale(' + s + ')';
                f.style.width = (100 / s) + '%';
            }
        });
    }

    function before() {
        root = document.getElementById('rvpPrint');
        if (root && root.parentNode !== document.body) {
            holder = document.createComment('rvp');
            root.parentNode.insertBefore(holder, root);
            document.body.appendChild(root);
        }
        fitAll();
    }

    function after() {
        if (root && holder && holder.parentNode) {
            holder.parentNode.insertBefore(root, holder);
            holder.parentNode.removeChild(holder);
        }
        holder = null;
        fitAll();
    }

    window.addEventListener('load', fitAll);
    window.addEventListener('resize', fitAll);
    window.addEventListener('beforeprint', before);
    window.addEventListener('afterprint', after);
    if (window.matchMedia) {
        var mq = matchMedia('print');
        var h = function (e) { if (e.matches) before(); else after(); };
        if (mq.addEventListener) mq.addEventListener('change', h); else if (mq.addListener) mq.addListener(h);
    }
    document.addEventListener('DOMContentLoaded', function () {
        Array.prototype.forEach.call(document.images, function (i) { i.addEventListener('load', fitAll); });
        fitAll();
    });
})();
</script>
@endonce