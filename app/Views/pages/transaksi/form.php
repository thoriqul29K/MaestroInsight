<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-receipt-cutoff"></i> <?= $transaksi ? 'Edit' : 'Tambah' ?> Transaksi</h2>
        <a href="<?= base_url() ?>transaksi" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <form action="<?= $transaksi ? base_url() . 'transaksi/update/' . $transaksi['id'] : base_url() . 'transaksi/store' ?>" method="post" class="form">
        <?= csrf_field() ?>
        <div class="form-group">
            <label for="id_pelanggan">Pelanggan *</label>
            <select id="id_pelanggan" name="id_pelanggan" required>
                <option value="">-- Pilih Pelanggan --</option>
                <?php foreach ($pelanggan as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= old('id_pelanggan', $transaksi['id_pelanggan'] ?? '') == $p['id'] ? 'selected' : '' ?>>
                        <?= esc($p['nama_pelanggan']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="tanggal_transaksi">Tanggal Transaksi *</label>
                <input type="date" id="tanggal_transaksi" name="tanggal_transaksi"
                       value="<?= old('tanggal_transaksi', $transaksi['tanggal_transaksi'] ?? date('Y-m-d')) ?>" required>
            </div>
            <div class="form-group">
                <label for="layanan">Layanan *</label>
                <input type="text" id="layanan" name="layanan"
                       value="<?= old('layanan', $transaksi['layanan'] ?? 'Paket Wisata') ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="tujuan">Tujuan *</label>
                <input type="text" id="tujuan" name="tujuan"
                       value="<?= old('tujuan', $transaksi['tujuan'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="jumlah_transaksi">Jumlah (Rp) *</label>
                <input type="number" id="jumlah_transaksi" name="jumlah_transaksi" min="0"
                       value="<?= old('jumlah_transaksi', $transaksi['jumlah_transaksi'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save"></i> Simpan
            </button>
            <a href="<?= base_url() ?>transaksi" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
<?= $this->endSection() ?>
