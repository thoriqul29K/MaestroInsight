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
                    <option value="failed"  <?= $filterStatus === 'failed'  ? 'selected' : '' ?>>Gagal</option>
                </select>
            </div>
            <div class="form-group">
                <label for="channel">Channel</label>
                <select id="channel" name="channel" onchange="this.form.submit()">
                    <option value="">-- Semua --</option>
                    <option value="email"     <?= $filterChannel === 'email'     ? 'selected' : '' ?>>Email</option>
                    <option value="whatsapp"  <?= $filterChannel === 'whatsapp'  ? 'selected' : '' ?>>WhatsApp</option>
                    <option value="telegram"  <?= $filterChannel === 'telegram'  ? 'selected' : '' ?>>Telegram</option>
                    <option value="sms"       <?= $filterChannel === 'sms'       ? 'selected' : '' ?>>SMS</option>
                </select>
            </div>
            <?php if ($filterStatus !== '' || $filterChannel !== ''): ?>
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
        <small class="hint">Maks. 50 entri terbaru</small>
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
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
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
                    <?php $no = 1; foreach ($logs as $log): ?>
                        <tr>
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
    <?php endif; ?>
</div>
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
<?= $this->endSection() ?>
