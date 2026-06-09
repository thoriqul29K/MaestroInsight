<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<?php $pelanggan ??= null; ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-person-plus"></i> <?= $pelanggan ? 'Edit' : 'Tambah' ?> Pelanggan</h2>
        <a href="<?= base_url('pelanggan') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <form action="<?= $pelanggan ? base_url('pelanggan/update/') . $pelanggan['id'] : base_url('pelanggan/store') ?>" method="post" class="form">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="nama_pelanggan">Nama Pelanggan *</label>
            <input type="text" id="nama_pelanggan" name="nama_pelanggan"
                value="<?= old('nama_pelanggan', $pelanggan['nama_pelanggan'] ?? '') ?>" required>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email"
                    value="<?= old('email', $pelanggan['email'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="telepon">Telepon *</label>
                <input type="text" id="telepon" name="telepon"
                    value="<?= old('telepon', $pelanggan['telepon'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="agama">Agama *</label>
                <select id="agama" name="agama" required>
                    <option value="">-- Pilih Agama --</option>
                    <option value="Islam" <?= old('agama', $pelanggan['agama'] ?? '') === 'Islam' ? 'selected' : '' ?>>Islam</option>
                    <option value="Kristen" <?= old('agama', $pelanggan['agama'] ?? '') === 'Kristen' ? 'selected' : '' ?>>Kristen</option>
                    <option value="Katolik" <?= old('agama', $pelanggan['agama'] ?? '') === 'Katolik' ? 'selected' : '' ?>>Katolik</option>
                    <option value="Buddha" <?= old('agama', $pelanggan['agama'] ?? '') === 'Buddha' ? 'selected' : '' ?>>Buddha</option>
                    <option value="Hindu" <?= old('agama', $pelanggan['agama'] ?? '') === 'Hindu' ? 'selected' : '' ?>>Hindu</option>
                    <option value="Lainnya" <?= old('agama', $pelanggan['agama'] ?? '') === 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </div>
            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir *</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir"
                    value="<?= old('tanggal_lahir', $pelanggan['tanggal_lahir'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-group">
            <label for="profesi">Profesi</label>
            <input type="text" id="profesi" name="profesi"
                value="<?= old('profesi', $pelanggan['profesi'] ?? '') ?>">
        </div>
        <div class="form-group">
            <label for="alamat">Alamat</label>
            <textarea id="alamat" name="alamat" rows="3"><?= old('alamat', $pelanggan['alamat'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="<?= base_url('pelanggan') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>