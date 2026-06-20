<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<?php $user ??= null; ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-person-<?= $user ? 'gear' : 'plus' ?>"></i> <?= $user ? 'Edit' : 'Tambah' ?> Akun</h2>
        <a href="<?= base_url('user') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <form action="<?= $user ? base_url('user/update/') . $user['id'] : base_url('user/store') ?>" method="post" class="form">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="username">Username *</label>
            <input type="text" id="username" name="username"
                value="<?= old('username', $user['username'] ?? '') ?>" required minlength="3" maxlength="50">
        </div>
        <div class="form-group">
            <label for="nama_lengkap">Nama Lengkap *</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap"
                value="<?= old('nama_lengkap', $user['nama_lengkap'] ?? '') ?>" required minlength="3" maxlength="100">
        </div>
        <div class="form-group">
            <label for="password">Password <?= $user ? '(kosongkan jika tidak diubah)' : '*' ?></label>
            <input type="password" id="password" name="password"
                <?= $user ? '' : 'required' ?> minlength="3" maxlength="255">
        </div>
        <div class="form-group">
            <label for="role">Role *</label>
            <select id="role" name="role" required>
                <option value="">-- Pilih Role --</option>
                <option value="admin" <?= old('role', $user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="superadmin" <?= old('role', $user['role'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
            </select>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="<?= base_url('user') ?>" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
