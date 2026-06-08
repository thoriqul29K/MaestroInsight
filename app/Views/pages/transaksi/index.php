<?php
$sortLink = function ($col, $label) use ($sort, $order, $perPage) {
    $newOrder = ($sort === $col && $order === 'ASC') ? 'DESC' : 'ASC';
    $arrow    = '';
    if ($sort === $col) {
        $arrow = ' <i class="bi bi-arrow-' . ($order === 'ASC' ? 'up' : 'down') . '"></i>';
    }
    $qs = http_build_query(['sort' => $col, 'order' => $newOrder, 'per_page' => $perPage]);
    return '<a href="?' . $qs . '">' . $label . $arrow . '</a>';
};
$request = service('request');
$page = (int) ($request->getGet('page') ?: 1);
$startNum = ($perPage === 'all') ? 1 : (($page - 1) * (int) $perPage + 1);
?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-receipt"></i> Daftar Transaksi</h2>
        <a href="<?= base_url() ?>transaksi/create" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Transaksi
        </a>
    </div>

    <div class="table-toolbar">
        <div class="per-page-selector">
            <label>Baris per halaman:</label>
            <select onchange="location.href='?'+this.value+'&sort=<?= $sort ?>&order=<?= $order ?>'"
                    class="form-select per-page-select">
                <option value="per_page=25"  <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                <option value="per_page=50"  <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                <option value="per_page=100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                <option value="per_page=200" <?= $perPage == 200 ? 'selected' : '' ?>>200</option>
                <option value="per_page=500" <?= $perPage == 500 ? 'selected' : '' ?>>500</option>
                <option value="per_page=all" <?= $perPage == 'all' ? 'selected' : '' ?>>Semua</option>
            </select>
        </div>
        <?php if ($perPage !== 'all' && $pager): ?>
            <div class="pagination-info">
                Menampilkan <?= $startNum ?>–<?= min($startNum + count($transaksi) - 1, $pager->getTotal()) ?> dari <?= $pager->getTotal() ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th><?= $sortLink('tb_transaksi.id', 'ID') ?></th>
                    <th><?= $sortLink('tb_transaksi.tanggal_transaksi', 'Tanggal') ?></th>
                    <th><?= $sortLink('tb_pelanggan.nama_pelanggan', 'Pelanggan') ?></th>
                    <th class="text-center"><?= $sortLink('tb_transaksi.jumlah_transaksi', 'Jumlah (Rp)') ?></th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transaksi as $t): ?>
                    <tr>
                        <td><?= $t['id'] ?></td>
                        <td><?= esc(date('d/m/Y', strtotime($t['tanggal_transaksi']))) ?></td>
                        <td><?= esc($t['nama_pelanggan']) ?></td>
                        <td class="text-center"><?= number_format($t['jumlah_transaksi'], 0, ',', '.') ?></td>
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

    <?php if ($perPage !== 'all' && $pager): ?>
        <div class="pagination-wrapper">
            <?= $pager->links('default', 'default_full') ?>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>
