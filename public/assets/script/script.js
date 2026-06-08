// MaestroInsight — Frontend interactions
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // Auto-hide alerts after 5s
        document.querySelectorAll('.alert').forEach(function (el) {
            setTimeout(function () {
                el.style.transition = 'opacity .4s';
                el.style.opacity = '0';
                setTimeout(function () { el.remove(); }, 400);
            }, 5000);
        });

        // Date pickers with dd/mm/yyyy display
        flatpickr("#tanggal_lahir, #tanggal_transaksi", {
            altInput: true,
            altFormat: "d/m/Y",
            dateFormat: "Y-m-d"
        });

        // Resizable columns with localStorage persistence
        initResizableTables();

        // Mobile sidebar drawer
        initSidebarDrawer();
    });

    function initSidebarDrawer() {
        var btn = document.getElementById('hamburgerBtn');
        var drawer = document.getElementById('sidebarDrawer');
        var overlay = document.getElementById('sidebarOverlay');
        var closeBtn = document.getElementById('drawerClose');
        if (!btn || !drawer || !overlay) return;

        function open() {
            drawer.classList.add('open');
            overlay.classList.add('open');
            document.body.style.overflow = 'hidden';
        }

        function close() {
            drawer.classList.remove('open');
            overlay.classList.remove('open');
            document.body.style.overflow = '';
        }

        btn.addEventListener('click', open);
        if (closeBtn) closeBtn.addEventListener('click', close);
        overlay.addEventListener('click', close);

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && drawer.classList.contains('open')) close();
        });

        drawer.querySelectorAll('.drawer-nav a').forEach(function (link) {
            link.addEventListener('click', close);
        });
    }

    function initResizableTables() {
        var tables = document.querySelectorAll('.data-table');
        var pagePath = window.location.pathname;

        tables.forEach(function (table, tableIdx) {
            var storageKey = 'tableWidths:' + pagePath + ':' + tableIdx;
            var headers = table.querySelectorAll('th');
            if (headers.length < 2) return;

            // Restore saved widths
            var savedWidths = (function () {
                try { return JSON.parse(localStorage.getItem(storageKey)); }
                catch (e) { return null; }
            })();

            headers.forEach(function (th, i) {
                if (savedWidths && savedWidths[i]) {
                    th.style.width = savedWidths[i] + 'px';
                }
                th.style.position = 'relative';

                // Skip handle on last column (Aksi)
                if (i === headers.length - 1) return;

                var handle = document.createElement('div');
                handle.className = 'resize-handle';
                th.appendChild(handle);

                var startX, startWidth;

                function onStart(e) {
                    startX = e.clientX;
                    startWidth = th.offsetWidth;
                    handle.classList.add('active');
                    document.documentElement.style.cursor = 'col-resize';
                    document.documentElement.style.userSelect = 'none';

                    document.addEventListener('mousemove', onMove);
                    document.addEventListener('mouseup', onEnd);
                }

                function onMove(e) {
                    var diff = e.clientX - startX;
                    th.style.width = Math.max(40, startWidth + diff) + 'px';
                }

                function onEnd() {
                    handle.classList.remove('active');
                    document.documentElement.style.cursor = '';
                    document.documentElement.style.userSelect = '';
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onEnd);
                    saveWidths();
                }

                function saveWidths() {
                    var widths = [];
                    headers.forEach(function (h) { widths.push(h.offsetWidth); });
                    try { localStorage.setItem(storageKey, JSON.stringify(widths)); }
                    catch (e) {}
                }

                handle.addEventListener('mousedown', onStart);
            });
        });
    }
})();
