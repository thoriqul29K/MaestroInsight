<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-person-plus"></i> <?= $pelanggan ? 'Edit' : 'Tambah' ?> Pelanggan</h2>
        <a href="<?= base_url() ?>pelanggan" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <form action="<?= $pelanggan ? base_url() . 'pelanggan/update/' . $pelanggan['id'] : base_url() . 'pelanggan/store' ?>" method="post" class="form">
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
        <div class="form-group">
            <label for="alamat">Alamat</label>
            <textarea id="alamat" name="alamat" rows="3"><?= old('alamat', $pelanggan['alamat'] ?? '') ?></textarea>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="<?= base_url() ?>pelanggan" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
