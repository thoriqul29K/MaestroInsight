<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-receipt"></i> Daftar Transaksi</h2>
        <a href="<?= base_url() ?>transaksi/create" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Transaksi
        </a>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Layanan</th>
                    <th>Tujuan</th>
                    <th>Jumlah (Rp)</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($transaksi as $t): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc(date('d/m/Y', strtotime($t['tanggal_transaksi']))) ?></td>
                        <td><?= esc($t['nama_pelanggan']) ?></td>
                        <td><?= esc($t['layanan']) ?></td>
                        <td><?= esc($t['tujuan']) ?></td>
                        <td class="text-right"><?= number_format($t['jumlah_transaksi'], 0, ',', '.') ?></td>
                        <td class="action-cell">
                            <a href="<?= base_url() ?>transaksi/edit/<?= $t['id'] ?>" class="btn btn-sm btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="<?= base_url() ?>transaksi/delete/<?= $t['id'] ?>" class="btn btn-sm btn-delete" title="Hapus"
                               onclick="return confirm('Hapus transaksi ini?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
