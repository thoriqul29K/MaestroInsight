<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-funnel"></i> Filter Pelanggan</h2>
    </div>
    <form method="get" class="filter-form">
        <div class="form-row">
            <div class="form-group">
                <label for="segment">Segmentasi</label>
                <select id="segment" name="segment" onchange="this.form.submit()">
                    <option value="">-- Semua Segment --</option>
                    <?php
                    $segments = [
                        ''          => 'Semua',
                        'loyal'     => 'Loyal',
                        'potential' => 'Potential',
                        'budget'    => 'Budget Hunter',
                        'seasonal'  => 'Seasonal',
                        'at_risk'   => 'At Risk',
                    ];
                    foreach ($segments as $val => $label):
                        if ($val === '') continue;
                    ?>
                        <option value="<?= $val ?>" <?= $selected === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-megaphone"></i> Kirim Promosi</h2>
    </div>
    <form action="<?= base_url() ?>promosi/kirim" method="post" id="formPromosi">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label for="channel">Kanal Pengiriman *</label>
                <select id="channel" name="channel" required>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="email">Email</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="pesan">Pesan Promosi *</label>
            <textarea id="pesan" name="pesan" rows="5" required placeholder="Halo {nama}, dapatkan diskon spesial 25% untuk Anda {segmen}..."><?= old('pesan') ?: "Halo {nama}, dapatkan diskon spesial 25% untuk Anda {segmen} di PT. Maestro Wisata Raya. Hubungi kami sekarang juga!" ?></textarea>
            <small class="hint">Gunakan <code>{nama}</code> dan <code>{segmen}</code> untuk personalisasi otomatis.</small>
        </div>

        <h3>Daftar Pelanggan</h3>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Kontak</th>
                        <th>Segmentasi</th>
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
                    foreach ($pelanggan as $p):
                        if ($selected !== '' && ($p['segment'] ?? '') !== $selected) continue;
                    ?>
                        <tr>
                            <td><input type="checkbox" name="id_pelanggan[]" value="<?= $p['id'] ?>" class="chk-pelanggan"></td>
                            <td><?= $no++ ?></td>
                            <td><?= esc($p['nama_pelanggan']) ?></td>
                            <td>
                                <i class="bi bi-telephone"></i> <?= esc($p['telepon']) ?><br>
                                <i class="bi bi-envelope"></i> <?= esc($p['email']) ?>
                            </td>
                            <td><?= $segmentLabel[$p['segment']] ?? '<span class="badge badge-default">Belum</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-send"></i> Buat Tautan Promosi
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('checkAll')?.addEventListener('change', function(e) {
    document.querySelectorAll('.chk-pelanggan').forEach(cb => cb.checked = e.target.checked);
});
</script>
<?= $this->endSection() ?>
