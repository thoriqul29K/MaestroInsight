<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-funnel"></i> Filter Riwayat</h2>
        <a href="<?= base_url() ?>promosi" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>
    <form method="get" class="filter-form">
        <div class="form-row">
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" onchange="this.form.submit()">
                    <option value="">-- Semua --</option>
                    <option value="success" <?= $filterStatus === 'success' ? 'selected' : '' ?>>Berhasil</option>
                    <option value="failed" <?= $filterStatus === 'failed'  ? 'selected' : '' ?>>Gagal</option>
                </select>
            </div>
            <div class="form-group">
                <label for="channel">Channel</label>
                <select id="channel" name="channel" onchange="this.form.submit()">
                    <option value="">-- Semua --</option>
                    <option value="email" <?= $filterChannel === 'email'     ? 'selected' : '' ?>>Email</option>
                    <option value="whatsapp" <?= $filterChannel === 'whatsapp'  ? 'selected' : '' ?>>WhatsApp</option>
                    <option value="telegram" <?= $filterChannel === 'telegram'  ? 'selected' : '' ?>>Telegram</option>
                    <option value="sms" <?= $filterChannel === 'sms'       ? 'selected' : '' ?>>SMS</option>
                </select>
            </div>
            <?php if ($hasFilter): ?>
                <div class="form-group" style="align-self: end;">
                    <a href="<?= base_url() ?>promosi/riwayat" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Reset Filter
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </form>

</div>

<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-clock-history"></i> Riwayat Pengiriman Promosi</h2>
        <?php if (! empty($logs)): ?>
            <div class="page-actions" style="display: flex; gap: 8px; align-items: center;">
                <small class="hint">Maks. 50 entri terbaru</small>
                <button type="button" class="btn btn-danger" id="btnHapusSelected"
                    style="background: var(--danger, #dc2626); color: #fff; border: none;">
                    <i class="bi bi-trash"></i> Hapus yang Dipilih
                </button>
            </div>
        <?php else: ?>
            <small class="hint">Maks. 50 entri terbaru</small>
        <?php endif; ?>
    </div>

    <?php if (empty($logs)): ?>
        <div class="empty-state">
            <i class="bi bi-inbox"></i>
            <p>Belum ada riwayat pengiriman promosi.</p>
            <a href="<?= base_url() ?>promosi" class="btn btn-primary">
                <i class="bi bi-megaphone"></i> Buat Promosi Pertama
            </a>
        </div>
    <?php else: ?>
        <form method="post" action="<?= base_url() ?>promosi/riwayat/hapus" id="formHapusSelected">
            <?= csrf_field() ?>
            <input type="hidden" name="mode" value="selected">
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">
                                <input type="checkbox" id="checkAllLog" aria-label="Pilih semua">
                            </th>
                            <th>No</th>
                            <th>Waktu</th>
                            <th>Pelanggan</th>
                            <th>Email Tujuan</th>
                            <th>Channel</th>
                            <th>Subjek</th>
                            <th>Status</th>
                            <th>Pesan Error</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1;
                        foreach ($logs as $log): ?>
                            <tr>
                                <td><input type="checkbox" name="id_log[]" value="<?= $log['id'] ?>" class="chk-log" aria-label="Pilih baris <?= $no ?>"></td>
                                <td><?= $no++ ?></td>
                                <td><?= esc($log['created_at'] ? date('d/m/Y H:i', strtotime($log['created_at'])) : '-') ?></td>
                                <td><?= esc($log['nama_pelanggan'] ?? '(pelanggan dihapus)') ?></td>
                                <td><?= esc($log['email_target'] ?? '-') ?></td>
                                <td><span class="badge badge-default"><?= esc(strtoupper($log['channel'])) ?></span></td>
                                <td><?= esc($log['subject'] ?? '-') ?></td>
                                <td>
                                    <?php if ($log['status'] === 'success'): ?>
                                        <span class="badge badge-success"><i class="bi bi-check-circle"></i> Berhasil</span>
                                    <?php else: ?>
                                        <span class="badge badge-failed"><i class="bi bi-x-circle"></i> Gagal</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['status'] === 'failed' && ! empty($log['error_message'])): ?>
                                        <small class="error-text"><?= esc($log['error_message']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </form>
    <?php endif; ?>
</div>

<dialog id="hapusModal" class="confirm-modal" aria-labelledby="hapusModalTitle">
    <div class="confirm-modal-form">
        <div class="confirm-modal-icon" style="background: rgba(220, 38, 38, .1); color: var(--danger, #dc2626);">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <h3 id="hapusModalTitle" class="confirm-modal-title">Konfirmasi Penghapusan</h3>
        <p class="confirm-modal-body">
            Anda akan menghapus <strong id="hapusCount">0</strong> riwayat promosi <span id="hapusModeLabel"></span>.
            Tindakan ini tidak dapat dibatalkan.
        </p>
        <div class="confirm-modal-actions">
            <button type="button" class="btn btn-secondary" data-action="cancel">Batal</button>
            <button type="button" class="btn btn-danger" data-action="confirm"
                style="background: var(--danger, #dc2626); color: #fff; border: none;">Hapus Sekarang</button>
        </div>
    </div>
</dialog>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
    .empty-state {
        text-align: center;
        padding: 40px 20px;
        color: var(--text-muted, #6b7280);
    }

    .empty-state i {
        font-size: 48px;
        display: block;
        margin-bottom: 12px;
        opacity: 0.5;
    }

    .empty-state p {
        margin-bottom: 16px;
    }

    .error-text {
        color: #991b1b;
        font-size: 12px;
        line-height: 1.4;
        display: block;
        max-width: 280px;
    }
</style>
<script>
    (function() {
        const checkAll = document.getElementById('checkAllLog');
        const btnSelected = document.getElementById('btnHapusSelected');
        const btnFiltered = document.getElementById('btnHapusFiltered');
        const formSel = document.getElementById('formHapusSelected');
        const formFil = document.getElementById('formHapusFiltered');
        const modal = document.getElementById('hapusModal');
        const countEl = document.getElementById('hapusCount');
        const modeLabel = document.getElementById('hapusModeLabel');

        if (!modal) return;

        let formTarget = null;

        // Select-all checkbox (hanya baris yang visible)
        checkAll?.addEventListener('change', function(e) {
            document.querySelectorAll('.chk-log').forEach(cb => cb.checked = e.target.checked);
        });

        function openModal(count, modeText, target) {
            countEl.textContent = count;
            modeLabel.textContent = modeText;
            formTarget = target;
            modal.showModal();
        }

        // Tombol "Hapus yang Dipilih"
        btnSelected?.addEventListener('click', function() {
            const checked = document.querySelectorAll('.chk-log:checked').length;
            if (checked === 0) {
                alert('Pilih minimal satu riwayat untuk dihapus.');
                return;
            }
            openModal(checked, 'yang dipilih', formSel);
        });

        // Tombol "Hapus Semua (filter)"
        btnFiltered?.addEventListener('click', function() {
            const count = parseInt('<?= (int) ($filterCount ?? 0) ?>', 10) || 0;
            if (count === 0) {
                alert('Tidak ada data untuk dihapus.');
                return;
            }
            openModal(count, 'sesuai filter aktif', formFil);
        });

        // Handler di dalam modal
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.close();
                return;
            }
            const action = e.target.dataset.action;
            if (action === 'cancel') {
                modal.close();
            } else if (action === 'confirm') {
                modal.close();
                if (formTarget) {
                    formTarget.submit();
                }
            }
        });
    })();
</script>
<?= $this->endSection() ?>