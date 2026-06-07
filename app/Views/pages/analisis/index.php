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
        <form action="<?= base_url() ?>analisis/proses-rfm-cluster" method="post" style="display:inline" class="js-long-form">
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
                Anda tetap dapat berpindah menu &mdash; proses akan tetap berjalan hingga selesai.
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
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pelanggan</th>
                    <th>Recency (hari)</th>
                    <th>Frequency</th>
                    <th>Monetary (Rp)</th>
                    <th>Segmentasi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $segmentLabel = [
                    'loyal'     => '<span class="badge badge-loyal">Loyal</span>',
                    'potential' => '<span class="badge badge-potential">Potential</span>',
                    'budget'    => '<span class="badge badge-budget">Budget Hunter</span>',
                    'seasonal'  => '<span class="badge badge-seasonal">Seasonal</span>',
                    'at_risk'   => '<span class="badge badge-risk">At Risk</span>',
                ];
                $no = 1;
                foreach ($rfm ?? [] as $r): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($r['nama_pelanggan']) ?></td>
                        <td><?= (int) $r['recency'] ?></td>
                        <td><?= (int) $r['frequency'] ?></td>
                        <td class="text-right"><?= number_format($r['monetary'], 0, ',', '.') ?></td>
                        <td><?= $segmentLabel[$r['segment']] ?? '<span class="badge badge-default">Belum</span>' ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rfm)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Belum ada data. Klik "Hitung RFM & Clustering" terlebih dahulu.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    (function() {
        'use strict';

        var statusBox = document.getElementById('clusteringStatus');
        var statusTitle = document.getElementById('clusteringTitle');
        var statusDetail = document.getElementById('clusteringDetail');
        var progressBar = document.getElementById('progressBarFill');
        var actionButtons = document.getElementById('actionButtons');
        var progressUrl = '<?= base_url() ?>analisis/progress';
        var confirmModal = document.getElementById('confirmModal');
        var pendingForm = null;

        function getCsrfMeta() {
            var meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) {
                return meta.getAttribute('content');
            }
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
            if (!actionButtons) return;
            actionButtons.querySelectorAll('button').forEach(function(b) {
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
                if (typeof confirmModal.close === 'function') {
                    confirmModal.close();
                } else {
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
                            (stage === 'error') ? 'Gagal' :
                            'Sedang memproses...';

                        showStatus(title, detail, percent);

                        if (percent >= 100) {
                            clearInterval(pollTimer);
                        }
                    })
                    .catch(function() {
                        /* abaikan error polling, request utama masih jalan */
                    });
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