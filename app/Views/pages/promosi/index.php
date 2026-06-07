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
        <a href="<?= base_url() ?>promosi/riwayat" class="btn btn-secondary">
            <i class="bi bi-clock-history"></i> Riwayat
        </a>
    </div>
    <form action="<?= base_url() ?>promosi/kirim" method="post" id="formPromosi" class="form" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-row">
            <div class="form-group">
                <label for="channel">Kanal Pengiriman *</label>
                <select id="channel" name="channel" required>
                    <option value="email" selected>Email</option>
                    <option value="whatsapp" disabled>WhatsApp (Dalam Pengembangan)</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label for="subject">Subjek Email *</label>
            <input type="text" id="subject" name="subject" required maxlength="255"
                value="<?= old('subject') ?>"
                placeholder="cth: Diskon 25% Akhir Bulan untuk Anda">
        </div>
        <div class="form-group">
            <label for="pesan">Pesan Promosi *</label>
            <textarea id="pesan" name="pesan" rows="5" required placeholder="Halo {nama}, dapatkan diskon spesial 25% untuk Anda {segmen}..."><?= old('pesan') ?: "Halo {nama}, dapatkan diskon spesial 25% untuk Anda {segmen} di PT. Maestro Wisata Raya. Hubungi kami sekarang juga!" ?></textarea>
            <small class="hint">Gunakan <code>{nama}</code> (nama lengkap) dan <code>{segmen}</code> untuk personalisasi otomatis.</small>
        </div>
        <div class="form-group">
            <label for="gambar">Lampiran Gambar (Opsional)</label>
            <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/gif,image/webp">
            <small class="hint">Format: JPG, PNG, GIF, WebP. Maks. 5MB.</small>
            <div id="imagePreview" class="image-preview" hidden>
                <img id="previewImg" src="" alt="Preview">
                <button type="button" class="btn btn-sm btn-secondary" id="removeImage">
                    <i class="bi bi-x-circle"></i> Hapus
                </button>
            </div>
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
                <i class="bi bi-send"></i> Kirim Promosi
            </button>
        </div>
    </form>

    <dialog id="confirmModal" class="confirm-modal" aria-labelledby="confirmModalTitle">
        <div class="confirm-modal-form">
            <div class="confirm-modal-icon"><i class="bi bi-megaphone"></i></div>
            <h3 id="confirmModalTitle" class="confirm-modal-title">Konfirmasi Pengiriman</h3>
            <p class="confirm-modal-body">
                Anda akan mengirim email promosi ke <strong id="confirmCount">0</strong> pelanggan.
                Proses ini akan langsung mengirim dan tidak dapat dibatalkan.
            </p>
            <div class="confirm-modal-actions">
                <button type="button" class="btn btn-secondary" data-action="cancel">Batal</button>
                <button type="button" class="btn btn-primary" data-action="confirm">Ya, Kirim Sekarang</button>
            </div>
        </div>
    </dialog>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
.image-preview {
    margin-top: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 10px;
    display: inline-block;
    max-width: 300px;
}
.image-preview img {
    max-width: 100%;
    max-height: 200px;
    border-radius: 4px;
    display: block;
    margin-bottom: 8px;
}
.image-preview[hidden] {
    display: none;
}
</style>
<script>
    document.getElementById('checkAll')?.addEventListener('change', function(e) {
        document.querySelectorAll('.chk-pelanggan').forEach(cb => cb.checked = e.target.checked);
    });

    (function() {
        const form = document.getElementById('formPromosi');
        const modal = document.getElementById('confirmModal');
        if (!form || !modal) return;

        const countEl = document.getElementById('confirmCount');

        form.addEventListener('submit', function(e) {
            const checked = form.querySelectorAll('.chk-pelanggan:checked').length;
            if (checked === 0) {
                e.preventDefault();
                alert('Pilih minimal satu pelanggan.');
                return;
            }
            e.preventDefault();
            countEl.textContent = checked;
            modal.showModal();
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.close();
                return;
            }
            const action = e.target.dataset.action;
            if (action === 'cancel') {
                modal.close();
            } else if (action === 'confirm') {
                modal.close();
                form.submit();
            }
        });
    })();

    const fileInput  = document.getElementById('gambar');
    const preview    = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const removeBtn  = document.getElementById('removeImage');

    fileInput?.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file && file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                previewImg.src = ev.target.result;
                preview.hidden = false;
            };
            reader.readAsDataURL(file);
        } else {
            preview.hidden = true;
            previewImg.src = '';
        }
    });

    removeBtn?.addEventListener('click', function() {
        fileInput.value = '';
        preview.hidden = true;
        previewImg.src = '';
    });
</script>
<?= $this->endSection() ?>
