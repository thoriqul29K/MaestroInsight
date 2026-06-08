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
                <label>Layanan</label>
                <?php $_layanan = old('layanan', isset($transaksi['layanan']) ? explode(',', $transaksi['layanan']) : []); ?>
                <div class="checkbox-group">
                    <?php foreach (['Dokumen', 'Cruise', 'Tour', 'Hotel', 'Transport', 'Ticket'] as $_opt): ?>
                    <label class="checkbox-inline">
                        <input type="checkbox" name="layanan[]" value="<?= $_opt ?>"
                            <?= in_array($_opt, is_array($_layanan) ? $_layanan : []) ? 'checked' : '' ?>>
                        <i class="bi bi-check-circle-fill check-icon"></i>
                        <?= $_opt ?>
                    </label>
                    <?php endforeach; ?>
                </div>
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
        <div class="form-group">
            <label for="detail_transaksi">Detail Transaksi</label>
            <textarea id="detail_transaksi" name="detail_transaksi" rows="3" maxlength="200"><?= old('detail_transaksi', $transaksi['detail_transaksi'] ?? '') ?></textarea>
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
