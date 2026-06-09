<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="row">
    <div class="col-grid stat-grid-analisis">
        <div class="stat-card stat-loyal">
            <div class="stat-icon"><i class="bi bi-star-fill"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $ringkasan['loyal'] ?? 0 ?></div>
                <div class="stat-label">Loyal</div>
            </div>
        </div>
        <div class="stat-card stat-potential">
            <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $ringkasan['potential'] ?? 0 ?></div>
                <div class="stat-label">Potential</div>
            </div>
        </div>
        <div class="stat-card stat-budget">
            <div class="stat-icon"><i class="bi bi-piggy-bank"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $ringkasan['budget'] ?? 0 ?></div>
                <div class="stat-label">Budget Hunter</div>
            </div>
        </div>
        <div class="stat-card stat-seasonal">
            <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $ringkasan['seasonal'] ?? 0 ?></div>
                <div class="stat-label">Seasonal</div>
            </div>
        </div>
        <div class="stat-card stat-risk stat-card-wide">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $ringkasan['at_risk'] ?? 0 ?></div>
                <div class="stat-label">At Risk</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-cpu"></i> Lakukan Segmentasi</h2>
    </div>
    <div class="action-buttons" id="actionButtons">
        <form action="<?= base_url('analisis/proses-rfm-cluster') ?>" method="post" style="display:inline" class="js-long-form">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-cpu"></i> Mulai Proses
            </button>
        </form>
    </div>
    <dialog id="confirmModal" class="confirm-modal" aria-labelledby="confirmModalTitle">
        <div class="confirm-modal-form">
            <div class="confirm-modal-icon"><i class="bi bi-cpu"></i></div>
            <h3 id="confirmModalTitle" class="confirm-modal-title">Konfirmasi Proses</h3>
            <p class="confirm-modal-body">
                Proses segmentasi pelanggan dapat memakan waktu beberapa menit.
                Anda tetap dapat berpindah menu, proses akan tetap berjalan hingga selesai.
            </p>
            <div class="confirm-modal-actions">
                <button type="button" class="btn btn-secondary" data-action="cancel">Batal</button>
                <button type="button" class="btn btn-primary" data-action="confirm">Ya, Mulai Proses</button>
            </div>
        </div>
    </dialog>
    <div id="clusteringStatus" class="clustering-status" hidden>
        <div class="clustering-spinner">
            <div class="spinner" aria-hidden="true"></div>
            <div class="clustering-status-text">
                <strong id="clusteringTitle">Sedang memproses...</strong>
                <div class="progress-bar-wrap" aria-hidden="true">
                    <div class="progress-bar-fill" id="progressBarFill"></div>
                </div>
                <small id="clusteringDetail">Mohon jangan tutup halaman ini. Anda boleh berpindah menu, proses tetap berjalan di server.</small>
            </div>
        </div>
    </div>
    <p class="hint">
        <i class="bi bi-info-circle"></i>
        Tombol di atas akan menghitung recency (jumlah hari sejak terakhir kali pelanggan melakukan transaksi), frequency (jumlah transaksi), dan monetary (total nilai transaksi dalam bentuk rupiah). Setelah itu, akan dilakukan proses segmentasi menggunakan metode hierarchical clustering untuk mengelompokkan pelanggan ke dalam segmen-segmen seperti Loyal, Potential, Budget Hunter, Seasonal, dan At Risk berdasarkan nilai tersebut yang akan memakan waktu beberapa menit.
    </p>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-table"></i> Data RFM Pelanggan</h2>
    </div>

    <div class="table-toolbar">
        <div class="per-page-selector">
            <label>Baris per halaman:</label>
            <select id="perPage" class="form-select per-page-select">
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100" selected>100</option>
                <option value="200">200</option>
                <option value="500">500</option>
                <option value="all">Semua</option>
            </select>
        </div>
        <div class="pagination-info">
            <span id="rowCounter" class="row-counter">Memuat…</span>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="sortable" data-sort="id">ID Pelanggan <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="nama_pelanggan">Nama Pelanggan <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="recency">Transaksi Terakhir Dilakukan <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="frequency">Jumlah Transaksi Dilakukan <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable text-center" data-sort="monetary">Total Pengeluaran (Rp) <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="segment">Segmen <i class="bi bi-arrow-down-up sort-icon"></i></th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>

    <nav id="pagerNav" class="pager" aria-label="Pagination"></nav>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
    .row-counter {
        color: #6b7280;
        font-size: 0.9rem;
    }

    .data-table th.sortable {
        cursor: pointer;
        user-select: none;
    }

    .data-table th.sortable:hover {
        background: #f3f4f6;
    }

    .data-table th .sort-icon {
        margin-left: 4px;
        opacity: 0.5;
        font-size: 0.85em;
    }

    .data-table th.sort-asc .sort-icon,
    .data-table th.sort-desc .sort-icon {
        opacity: 1;
    }

    .pager {
        display: flex;
        gap: 4px;
        justify-content: center;
        margin: 12px 0;
        flex-wrap: wrap;
    }

    .pager .page-btn {
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 6px;
        cursor: pointer;
        color: #374151;
    }

    .pager .page-btn:hover:not(:disabled) {
        background: #f3f4f6;
    }

    .pager .page-btn.active {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }

    .pager .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pager .ellipsis {
        display: inline-flex;
        align-items: center;
        padding: 0 6px;
        color: #6b7280;
    }

    .empty-row td {
        text-align: center;
        color: #6b7280;
        padding: 24px 8px;
    }
</style>
<script>
    (function() {
        'use strict';

        const SEGMENT_LABELS = {
            loyal: '<span class="badge badge-loyal">Loyal</span>',
            potential: '<span class="badge badge-potential">Potential</span>',
            budget: '<span class="badge badge-budget">Budget Hunter</span>',
            seasonal: '<span class="badge badge-seasonal">Seasonal</span>',
            at_risk: '<span class="badge badge-risk">At Risk</span>',
        };

        function escapeHtml(str) {
            return String(str ?? '').replace(/[&<>"']/g, c => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            })[c]);
        }

        function formatNumber(n) {
            return Number(n).toLocaleString('id-ID');
        }

        function recencyToDate(days) {
            const d = new Date();
            d.setDate(d.getDate() - parseInt(days, 10));
            const dd = String(d.getDate()).padStart(2, '0');
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const yyyy = d.getFullYear();
            return `${dd}/${mm}/${yyyy}`;
        }

        /* ===== Table AJAX state ===== */
        const tableState = {
            page: 1,
            sort: 'id',
            dir: 'asc',
            perPage: document.getElementById('perPage').value || '100',
            total: 0,
            totalPages: 0,
        };

        const tbody = document.getElementById('tbody');
        const counterEl = document.getElementById('rowCounter');
        const pagerEl = document.getElementById('pagerNav');
        const perPageEl = document.getElementById('perPage');

        function updateCounter() {
            if (tableState.total === 0) {
                counterEl.textContent = '0 data RFM';
                return;
            }
            let from, to;
            if (tableState.perPage === 'all') {
                from = 1;
                to = tableState.total;
            } else {
                const pp = parseInt(tableState.perPage, 10);
                from = (tableState.page - 1) * pp + 1;
                to = Math.min(tableState.page * pp, tableState.total);
            }
            counterEl.textContent = `Menampilkan ${from}–${to} dari ${tableState.total} data RFM`;
        }

        function renderRows(rows) {
            if (!rows.length) {
                tbody.innerHTML = '<tr class="empty-row"><td colspan="6">Belum ada data. Klik "Mulai Proses" terlebih dahulu.</td></tr>';
                return;
            }
            let html = '';
            rows.forEach(r => {
                const segBadge = SEGMENT_LABELS[r.segment] || '<span class="badge badge-default">Belum</span>';
                html += `<tr>
                    <td>${escapeHtml(r.id_pelanggan)}</td>
                    <td>${escapeHtml(r.nama_pelanggan)}</td>
                    <td>${recencyToDate(r.recency)}</td>
                    <td>${parseInt(r.frequency, 10)}</td>
                    <td class="text-center">${formatNumber(r.monetary)}</td>
                    <td>${segBadge}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        function renderPager() {
            if (tableState.totalPages <= 1) {
                pagerEl.innerHTML = '';
                return;
            }
            const tp = tableState.totalPages;
            const p = tableState.page;
            const pages = new Set([1, tp, p, p - 1, p + 1]);
            const list = [...pages].filter(x => x >= 1 && x <= tp).sort((a, b) => a - b);
            let html = `<button type="button" class="page-btn" data-page="${p - 1}" ${p === 1 ? 'disabled' : ''}>&lsaquo;</button>`;
            let prev = 0;
            list.forEach(n => {
                if (prev && n - prev > 1) html += '<span class="ellipsis">…</span>';
                html += `<button type="button" class="page-btn ${n === p ? 'active' : ''}" data-page="${n}">${n}</button>`;
                prev = n;
            });
            html += `<button type="button" class="page-btn" data-page="${p + 1}" ${p === tp ? 'disabled' : ''}>&rsaquo;</button>`;
            pagerEl.innerHTML = html;
        }

        function updateSortIcons() {
            document.querySelectorAll('th.sortable').forEach(th => {
                th.classList.remove('sort-asc', 'sort-desc');
                const icon = th.querySelector('.sort-icon');
                if (icon) icon.className = 'bi bi-arrow-down-up sort-icon';
            });
            const active = document.querySelector(`th.sortable[data-sort="${tableState.sort}"]`);
            if (active) {
                active.classList.add(tableState.dir === 'asc' ? 'sort-asc' : 'sort-desc');
                const icon = active.querySelector('.sort-icon');
                if (icon) {
                    const arrowClass = tableState.dir === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down';
                    icon.className = `bi ${arrowClass} sort-icon`;
                }
            }
        }

        async function fetchRfm() {
            const params = new URLSearchParams({
                sort: tableState.sort,
                dir: tableState.dir,
                per_page: tableState.perPage,
                page: tableState.page,
            });
            try {
                const res = await fetch('<?= base_url('analisis/data') ?>?' + params.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!res.ok) throw new Error('Gagal memuat data');
                const data = await res.json();
                tableState.total = data.total;
                tableState.totalPages = data.total_pages;
                tableState.page = data.page;
                renderRows(data.rows);
                renderPager();
                updateCounter();
                updateSortIcons();
            } catch (e) {
                tbody.innerHTML = `<tr class="empty-row"><td colspan="6">Gagal memuat data: ${escapeHtml(e.message)}</td></tr>`;
                counterEl.textContent = '';
                pagerEl.innerHTML = '';
            }
        }

        perPageEl.addEventListener('change', () => {
            tableState.perPage = perPageEl.value;
            tableState.page = 1;
            fetchRfm();
        });

        document.querySelectorAll('th.sortable').forEach(th => {
            th.addEventListener('click', () => {
                const key = th.dataset.sort;
                if (tableState.sort === key) {
                    tableState.dir = tableState.dir === 'asc' ? 'desc' : 'asc';
                } else {
                    tableState.sort = key;
                    tableState.dir = 'asc';
                }
                tableState.page = 1;
                fetchRfm();
            });
        });

        pagerEl.addEventListener('click', e => {
            const btn = e.target.closest('.page-btn');
            if (!btn || btn.disabled) return;
            const p = parseInt(btn.dataset.page, 10);
            if (!p || p < 1 || p > tableState.totalPages || p === tableState.page) return;
            tableState.page = p;
            fetchRfm();
        });

        fetchRfm();

        /* ===== Clustering progress ===== */
        var statusBox = document.getElementById('clusteringStatus');
        var statusTitle = document.getElementById('clusteringTitle');
        var statusDetail = document.getElementById('clusteringDetail');
        var progressBar = document.getElementById('progressBarFill');
        var actionBtns = document.getElementById('actionButtons');
        var progressUrl = '<?= base_url('analisis/progress') ?>';
        var confirmModal = document.getElementById('confirmModal');
        var pendingForm = null;

        function getCsrfMeta() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) return meta.getAttribute('content');
            var input = document.querySelector('input[name="<?= csrf_token() ?>"]');
            return input ? input.value : '';
        }

        function getCsrfHeader() {
            return '<?= csrf_header() ?>';
        }

        function showStatus(title, detail, percent) {
            if (!statusBox) return;
            if (title) statusTitle.textContent = title;
            if (detail !== undefined && detail !== null) statusDetail.textContent = detail;
            if (typeof percent === 'number' && progressBar) {
                progressBar.style.width = Math.max(0, Math.min(100, percent)) + '%';
            }
            statusBox.hidden = false;
        }

        function setButtonsDisabled(disabled) {
            if (!actionBtns) return;
            actionBtns.querySelectorAll('button').forEach(function(b) {
                b.disabled = disabled;
            });
        }

        function openConfirm() {
            if (confirmModal && typeof confirmModal.showModal === 'function') {
                confirmModal.showModal();
            } else if (confirmModal) {
                confirmModal.setAttribute('open', '');
            }
        }

        function closeConfirm() {
            if (confirmModal) {
                if (typeof confirmModal.close === 'function') confirmModal.close();
                else {
                    confirmModal.open = false;
                    confirmModal.removeAttribute('open');
                }
            }
            pendingForm = null;
        }

        if (confirmModal) {
            confirmModal.addEventListener('click', function(e) {
                if (e.target === confirmModal) closeConfirm();
            });
            confirmModal.addEventListener('close', function() {
                pendingForm = null;
            });
            var cancelBtn = confirmModal.querySelector('[data-action="cancel"]');
            var confirmBtn = confirmModal.querySelector('[data-action="confirm"]');
            if (cancelBtn) cancelBtn.addEventListener('click', closeConfirm);
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    var f = pendingForm;
                    closeConfirm();
                    if (f) executeProcess(f);
                });
            }
        }

        function executeProcess(form) {
            showStatus(
                'Mempersiapkan proses...',
                'Menghitung nilai RFM lalu menjalankan hierarchical clustering. Halaman akan dimuat ulang otomatis ketika selesai.',
                0
            );
            setButtonsDisabled(true);

            var formData = new FormData(form);
            var csrfValue = getCsrfMeta();

            var pollTimer = setInterval(function() {
                fetch(progressUrl, {
                        credentials: 'same-origin',
                        cache: 'no-store'
                    })
                    .then(function(r) {
                        return r.ok ? r.json() : null;
                    })
                    .then(function(p) {
                        if (!p) return;
                        var percent = parseInt(p.percent, 10) || 0;
                        var stage = p.stage || '';
                        var detail = p.detail || '';
                        var title = (stage === 'rfm') ? 'Menghitung nilai RFM...' :
                            (stage === 'clustering') ? 'Menjalankan Hierarchical Clustering...' :
                            (stage === 'done') ? 'Selesai' :
                            (stage === 'error') ? 'Gagal' : 'Sedang memproses...';
                        showStatus(title, detail, percent);
                        if (percent >= 100) clearInterval(pollTimer);
                    })
                    .catch(function() {});
            }, 800);

            fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin',
                    cache: 'no-store',
                    headers: csrfValue ? (function() {
                        var h = {};
                        h[getCsrfHeader()] = csrfValue;
                        return h;
                    })() : {},
                })
                .then(function(res) {
                    if (res.redirected) {
                        clearInterval(pollTimer);
                        window.location.href = res.url;
                        return null;
                    }
                    if (res.ok) {
                        clearInterval(pollTimer);
                        showStatus('Selesai', 'Memuat ulang halaman...', 100);
                        window.location.reload();
                        return null;
                    }
                    return res.text().then(function(text) {
                        clearInterval(pollTimer);
                        var snippet = (text || 'Respons tidak dikenal').replace(/\s+/g, ' ').substring(0, 300);
                        showStatus('Gagal (' + res.status + ')', snippet, 100);
                        setButtonsDisabled(false);
                    });
                })
                .catch(function(err) {
                    clearInterval(pollTimer);
                    showStatus('Gagal memproses', err && err.message ? err.message : 'Terjadi kesalahan jaringan.', 100);
                    setButtonsDisabled(false);
                });
        }

        document.querySelectorAll('form.js-long-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                pendingForm = form;
                openConfirm();
            });
        });
    })();
</script>
<?= $this->endSection() ?>