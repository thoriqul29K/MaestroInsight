// MaestroInsight — Frontend interactions
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // Sidebar toggle (mobile)
        var btn = document.getElementById('btnToggle');
        var sb  = document.getElementById('sidebar');
        if (btn && sb) {
            btn.addEventListener('click', function () {
                sb.classList.toggle('open');
            });
        }

        // Auto-hide alerts after 5s
        document.querySelectorAll('.alert').forEach(function (el) {
            setTimeout(function () {
                el.style.transition = 'opacity .4s';
                el.style.opacity = '0';
                setTimeout(function () { el.remove(); }, 400);
            }, 5000);
        });
    });
})();
