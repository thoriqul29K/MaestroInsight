<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-people"></i> Daftar Pelanggan</h2>
        <a href="<?= base_url() ?>pelanggan/create" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Pelanggan
        </a>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pelanggan</th>
                    <th>Email</th>
                    <th>Telepon</th>
                    <th>Segmentasi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $segmentLabel = [
                    'loyal'     => '<span class="badge badge-loyal">Loyal</span>',
                    'potential' => '<span class="badge badge-potential">Potential</span>',
                    'budget'    => '<span class="badge badge-budget">Budget Hunter</span>',
                    'seasonal'  => '<span class="badge badge-seasonal">Seasonal</span>',
                    'at_risk'   => '<span class="badge badge-risk">At Risk</span>',
                ];
                $no = 1;
                foreach ($pelanggan as $p): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($p['nama_pelanggan']) ?></td>
                        <td><?= esc($p['email']) ?></td>
                        <td><?= esc($p['telepon']) ?></td>
                        <td><?= $segmentLabel[$p['segment']] ?? '<span class="badge badge-default">Belum</span>' ?></td>
                        <td class="action-cell">
                            <a href="<?= base_url() ?>pelanggan/edit/<?= $p['id'] ?>" class="btn btn-sm btn-edit" title="Edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="<?= base_url() ?>pelanggan/delete/<?= $p['id'] ?>" class="btn btn-sm btn-delete" title="Hapus"
                               onclick="return confirm('Hapus pelanggan ini?')">
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
