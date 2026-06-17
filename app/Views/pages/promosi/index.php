<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<?php $pelanggan ??= [];
$selected ??= ''; ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-funnel"></i> Filter Pelanggan</h2>
    </div>
    <div class="filter-form">
        <div class="form-row">
            <div class="form-group">
                <label>Segmentasi</label>
                <div class="checkbox-group" id="segmentGroup">
                    <?php
                    $segments = [
                        'loyal'     => 'Loyal',
                        'potential' => 'Potential',
                        'budget'    => 'Budget Hunter',
                        'seasonal'  => 'Seasonal',
                        'at_risk'   => 'At Risk',
                    ];
                    foreach ($segments as $val => $label):
                    ?>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="segment[]" value="<?= $val ?>" checked>
                            <i class="bi bi-check-circle-fill check-icon"></i>
                            <?= $label ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="form-group">
                <label>Agama</label>
                <div class="checkbox-group" id="agamaGroup">
                    <?php
                    $agamaList = ['Islam', 'Kristen', 'Katolik', 'Buddha', 'Hindu', 'Lainnya'];
                    foreach ($agamaList as $a):
                    ?>
                        <label class="checkbox-inline">
                            <input type="checkbox" name="agama[]" value="<?= $a ?>" checked>
                            <i class="bi bi-check-circle-fill check-icon"></i>
                            <?= $a ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-megaphone"></i> Kirim Promosi</h2>
        <a href="<?= base_url('promosi/riwayat') ?>" class="btn btn-secondary">
            <i class="bi bi-clock-history"></i> Riwayat
        </a>
    </div>
    <form action="<?= base_url('promosi/kirim') ?>" method="post" id="formPromosi" class="form" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="channel" value="email">
        <div class="form-group">
            <label for="subject">Subjek Email *</label>
            <input type="text" id="subject" name="subject" required maxlength="255"
                value="<?= old('subject') ?>"
                placeholder="cth: Diskon 25% Akhir Bulan untuk Anda">
        </div>
        <div class="form-group">
            <label for="pesan">Pesan Promosi *</label>
            <textarea id="pesan" name="pesan" rows="5" required placeholder="Halo {nama}, dapatkan diskon spesial 25% untuk Anda {segmen}..."><?= old('pesan') ?: "Halo {nama}, dapatkan diskon spesial 25% untuk Anda {segmen} di Maestro Tour & Travel. Hubungi kami sekarang juga!" ?></textarea>
            <small class="hint">Gunakan <code>{nama}</code> (nama lengkap) dan <code>{segmen}</code> untuk personalisasi otomatis.</small>
        </div>
        <div class="form-group">
            <label for="gambar">Lampiran Gambar</label>
            <input type="file" id="gambar" name="gambar" accept="image/jpeg,image/png,image/gif,image/webp">
            <div id="imagePreview" class="image-preview" hidden>
                <img id="previewImg" src="" alt="Preview">
                <button type="button" class="btn btn-sm btn-secondary" id="removeImage">
                    <i class="bi bi-x-circle"></i> Hapus
                </button>
            </div>
        </div>

        <h3>Daftar Pelanggan</h3>
        <div class="table-toolbar">
            <div class="per-page-selector">
                <label>Baris per halaman:</label>
                <select id="perPage" class="form-select per-page-select">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100" selected>100</option>
                    <option value="200">200</option>
                    <option value="500">500</option>
                    <option value="all">Semua</option>
                </select>
            </div>
            <div class="pagination-info">
                <span id="rowCounter" class="row-counter">Memuat…</span>
            </div>
        </div>
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="checkAll"></th>
                        <th class="sortable" data-sort="no">ID Pelanggan <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th class="sortable" data-sort="nama">Nama <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th class="sortable" data-sort="kontak">Kontak <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th class="sortable" data-sort="agama">Agama <i class="bi bi-arrow-down-up sort-icon"></i></th>
                        <th class="sortable" data-sort="segment">Segmentasi <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    </tr>
                </thead>
                <tbody id="pelangganTbody">
                </tbody>
            </table>
        </div>
        <nav id="pagerNav" class="pager" aria-label="Pagination"></nav>

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

    .row-counter {
        color: #6b7280;
        font-size: 0.9rem;
    }

    .data-table th.sortable {
        cursor: pointer;
        user-select: none;
    }

    .data-table th.sortable:hover {
        background: #f3f4f6;
    }

    .data-table th .sort-icon {
        margin-left: 4px;
        opacity: 0.5;
        font-size: 0.85em;
    }

    .data-table th.sort-asc .sort-icon,
    .data-table th.sort-desc .sort-icon {
        opacity: 1;
    }

    .pager {
        display: flex;
        gap: 4px;
        justify-content: center;
        margin: 12px 0;
        flex-wrap: wrap;
    }

    .pager .page-btn {
        min-width: 36px;
        height: 36px;
        padding: 0 10px;
        border: 1px solid #e5e7eb;
        background: #fff;
        border-radius: 6px;
        cursor: pointer;
        color: #374151;
    }

    .pager .page-btn:hover:not(:disabled) {
        background: #f3f4f6;
    }

    .pager .page-btn.active {
        background: #2563eb;
        color: #fff;
        border-color: #2563eb;
    }

    .pager .page-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .pager .ellipsis {
        display: inline-flex;
        align-items: center;
        padding: 0 6px;
        color: #6b7280;
    }

    .empty-row td {
        text-align: center;
        color: #6b7280;
        padding: 24px 8px;

    }
</style>
<script>
    const SEGMENT_LABELS = {
        loyal: '<span class="badge badge-loyal">Loyal</span>',
        potential: '<span class="badge badge-potential">Potential</span>',
        budget: '<span class="badge badge-budget">Budget Hunter</span>',
        seasonal: '<span class="badge badge-seasonal">Seasonal</span>',
        at_risk: '<span class="badge badge-risk">At Risk</span>',
    };

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[c]);
    }

    const state = {
        segment: new Set(['loyal', 'potential', 'budget', 'seasonal', 'at_risk']),
        agama: new Set(['Islam', 'Kristen', 'Katolik', 'Buddha', 'Hindu', 'Lainnya']),
        perPage: document.getElementById('perPage').value || '50',
        page: 1,
        sort: 'no',
        dir: 'asc',
        total: 0,
        totalPages: 0,
    };

    const checkedIds = new Set();

    const tbody = document.getElementById('pelangganTbody');
    const counterEl = document.getElementById('rowCounter');
    const pagerEl = document.getElementById('pagerNav');
    const checkAllEl = document.getElementById('checkAll');
    const perPageEl = document.getElementById('perPage');

    function collectChecked() {
        document.querySelectorAll('.chk-pelanggan').forEach(cb => {
            if (cb.checked) checkedIds.add(cb.value);
        });
    }

    function updateCounter() {
        if (state.total === 0) {
            counterEl.textContent = '0 pelanggan';
            return;
        }
        let from, to;
        if (state.perPage === 'all') {
            from = 1;
            to = state.total;
        } else {
            const pp = parseInt(state.perPage, 10);
            from = (state.page - 1) * pp + 1;
            to = Math.min(state.page * pp, state.total);
        }
        counterEl.textContent = `Menampilkan ${from}–${to} dari ${state.total} pelanggan`;
    }

    function renderRows(rows) {
        if (!rows.length) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="6">Tidak ada pelanggan pada filter ini.</td></tr>';
            checkAllEl.checked = false;
            checkAllEl.disabled = true;
            return;
        }
        checkAllEl.disabled = false;
        let html = '';
        rows.forEach((p, i) => {
            const segBadge = SEGMENT_LABELS[p.segment] || '<span class="badge badge-default">Belum</span>';
            const isChecked = checkedIds.has(String(p.id)) ? ' checked' : '';
            html += `<tr>
                <td><input type="checkbox" name="id_pelanggan[]" value="${p.id}" class="chk-pelanggan"${isChecked}></td>
                <td>${escapeHtml(p.id)}</td>
                <td>${escapeHtml(p.nama_pelanggan)}</td>
                <td>
                    <i class="bi bi-telephone"></i> ${escapeHtml(p.telepon)}<br>
                    <i class="bi bi-envelope"></i> ${escapeHtml(p.email)}
                </td>
                <td>${escapeHtml(p.agama) || '-'}</td>
                <td>${segBadge}</td>
            </tr>`;
        });
        tbody.innerHTML = html;
        syncCheckAll();
    }

    function syncCheckAll() {
        const boxes = document.querySelectorAll('.chk-pelanggan');
        if (boxes.length === 0) {
            checkAllEl.checked = false;
            return;
        }
        const checked = document.querySelectorAll('.chk-pelanggan:checked').length;
        checkAllEl.checked = checked === boxes.length;
    }

    function renderPager() {
        if (state.totalPages <= 1) {
            pagerEl.innerHTML = '';
            return;
        }
        const tp = state.totalPages;
        const p = state.page;
        const pages = new Set([1, tp, p, p - 1, p + 1]);
        const list = [...pages].filter(x => x >= 1 && x <= tp).sort((a, b) => a - b);
        let html = '';
        html += `<button type="button" class="page-btn" data-page="${p - 1}" ${p === 1 ? 'disabled' : ''}>&lsaquo;</button>`;
        let prev = 0;
        list.forEach(n => {
            if (prev && n - prev > 1) html += '<span class="ellipsis">…</span>';
            html += `<button type="button" class="page-btn ${n === p ? 'active' : ''}" data-page="${n}">${n}</button>`;
            prev = n;
        });
        html += `<button type="button" class="page-btn" data-page="${p + 1}" ${p === tp ? 'disabled' : ''}>&rsaquo;</button>`;
        pagerEl.innerHTML = html;
    }

    function updateSortIcons() {
        document.querySelectorAll('th.sortable').forEach(th => {
            th.classList.remove('sort-asc', 'sort-desc');
            const icon = th.querySelector('.sort-icon');
            if (icon) icon.className = 'bi bi-arrow-down-up sort-icon';
        });
        const active = document.querySelector(`th.sortable[data-sort="${state.sort}"]`);
        if (active) {
            active.classList.add(state.dir === 'asc' ? 'sort-asc' : 'sort-desc');
            const icon = active.querySelector('.sort-icon');
            if (icon) {
                const arrowClass = state.dir === 'asc' ? 'bi-arrow-up' : 'bi-arrow-down';
                icon.className = `bi ${arrowClass} sort-icon`;
            }
        }
    }

    async function fetchPelanggan() {
        if (state.segment.size === 0 || state.agama.size === 0) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="6">Tidak ada pelanggan pada filter ini.</td></tr>';
            counterEl.textContent = '0 pelanggan';
            pagerEl.innerHTML = '';
            checkAllEl.checked = false;
            checkAllEl.disabled = true;
            state.total = 0;
            state.totalPages = 0;
            return;
        }
        const params = new URLSearchParams({
            per_page: state.perPage,
            page: state.page,
            sort: state.sort,
            dir: state.dir,
        });
        state.segment.forEach(s => params.append('segment[]', s));
        state.agama.forEach(a => params.append('agama[]', a));
        try {
            const res = await fetch(`<?= base_url('promosi/data') ?>?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            if (!res.ok) throw new Error('Gagal memuat data');
            const data = await res.json();
            state.total = data.total;
            state.totalPages = data.total_pages;
            state.page = data.page;
            renderRows(data.rows);
            renderPager();
            updateCounter();
            updateSortIcons();
        } catch (e) {
            tbody.innerHTML = `<tr class="empty-row"><td colspan="6">Gagal memuat data: ${escapeHtml(e.message)}</td></tr>`;
            counterEl.textContent = '';
            pagerEl.innerHTML = '';
        }
    }

    document.querySelectorAll('#segmentGroup .checkbox-inline input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => {
            if (cb.checked) state.segment.add(cb.value);
            else state.segment.delete(cb.value);
            state.page = 1;
            fetchPelanggan();
        });
    });

    document.querySelectorAll('#agamaGroup .checkbox-inline input[type="checkbox"]').forEach(cb => {
        cb.addEventListener('change', () => {
            if (cb.checked) state.agama.add(cb.value);
            else state.agama.delete(cb.value);
            state.page = 1;
            fetchPelanggan();
        });
    });

    perPageEl.addEventListener('change', () => {
        state.perPage = perPageEl.value;
        state.page = 1;
        fetchPelanggan();
    });

    document.querySelectorAll('th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const key = th.dataset.sort;
            if (state.sort === key) {
                state.dir = state.dir === 'asc' ? 'desc' : 'asc';
            } else {
                state.sort = key;
                state.dir = 'asc';
            }
            state.page = 1;
            fetchPelanggan();
        });
    });

    pagerEl.addEventListener('click', e => {
        const btn = e.target.closest('.page-btn');
        if (!btn || btn.disabled) return;
        const p = parseInt(btn.dataset.page, 10);
        if (!p || p < 1 || p > state.totalPages || p === state.page) return;
        state.page = p;
        fetchPelanggan();
    });

    checkAllEl?.addEventListener('change', function(e) {
        document.querySelectorAll('.chk-pelanggan').forEach(cb => {
            cb.checked = e.target.checked;
            if (cb.checked) checkedIds.add(cb.value);
            else checkedIds.delete(cb.value);
        });
    });

    tbody.addEventListener('change', e => {
        if (!e.target.classList.contains('chk-pelanggan')) return;
        if (e.target.checked) checkedIds.add(e.target.value);
        else checkedIds.delete(e.target.value);
        syncCheckAll();
    });

    (function() {
        const form = document.getElementById('formPromosi');
        const modal = document.getElementById('confirmModal');
        if (!form || !modal) return;
        const countEl = document.getElementById('confirmCount');

        form.addEventListener('submit', function(e) {
            collectChecked();
            const total = checkedIds.size;
            if (total === 0) {
                e.preventDefault();
                alert('Pilih minimal satu pelanggan.');
                return;
            }
            e.preventDefault();
            countEl.textContent = total;
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

    const fileInput = document.getElementById('gambar');
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const removeBtn = document.getElementById('removeImage');

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

    fetchPelanggan();
</script>
<?= $this->endSection() ?>