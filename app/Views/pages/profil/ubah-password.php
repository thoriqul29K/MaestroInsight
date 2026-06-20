<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-key"></i> Ubah Password</h2>
    </div>

    <form action="<?= base_url('profil/simpan-password') ?>" method="post" class="form">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="password_lama">Password Lama *</label>
            <input type="password" id="password_lama" name="password_lama" required>
        </div>
        <div class="form-group">
            <label for="password_baru">Password Baru *</label>
            <input type="password" id="password_baru" name="password_baru" required minlength="3" maxlength="255">
        </div>
        <div class="form-group">
            <label for="konfirmasi_password">Konfirmasi Password Baru *</label>
            <input type="password" id="konfirmasi_password" name="konfirmasi_password" required minlength="3" maxlength="255">
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
