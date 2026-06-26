<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-receipt"></i> Daftar Transaksi</h2>
        <a href="<?= base_url('transaksi/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Tambah Transaksi
        </a>
    </div>

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
        <div class="filter-bar">
            <div class="filter-group">
                <label class="filter-toggle">
                    <input type="checkbox" id="enableTanggal" checked>
                    <span>Tanggal</span>
                </label>
                <input type="text" id="tanggalDari" class="filter-input datepicker" placeholder="Dari" readonly>
                <span class="filter-sep">–</span>
                <input type="text" id="tanggalSampai" class="filter-input datepicker" placeholder="Sampai" readonly>
            </div>
            <div class="filter-group">
                <label class="filter-toggle">
                    <input type="checkbox" id="enableJumlah" checked>
                    <span>Jumlah (Rp)</span>
                </label>
                <input type="number" id="jumlahMin" class="filter-input" placeholder="Min" min="0">
                <span class="filter-sep">–</span>
                <input type="number" id="jumlahMax" class="filter-input" placeholder="Max" min="0">
            </div>
        </div>
        <div class="pagination-info">
            <span id="rowCounter" class="row-counter">Memuat…</span>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="sortable" data-sort="id">ID Transaksi <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="tanggal">Tanggal Transaksi <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="pelanggan">Nama Pelanggan <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable text-center" data-sort="jumlah">Jumlah (Rp) <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="tbody"></tbody>
        </table>
    </div>

    <nav id="pagerNav" class="pager" aria-label="Pagination"></nav>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
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

    .filter-bar {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
        padding: 0 16px 12px;
    }

    .filter-group {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .filter-toggle {
        display: flex;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.85rem;
        color: #374151;
        white-space: nowrap;
        user-select: none;
    }

    .filter-toggle input[type="checkbox"] {
        width: 15px;
        height: 15px;
        accent-color: #2563eb;
        cursor: pointer;
    }

    .filter-input {
        padding: 6px 10px;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 0.85rem;
        background: #fff;
        color: #374151;
        outline: none;
        transition: border-color 0.2s, opacity 0.2s, background 0.2s;
    }

    .filter-input:focus {
        border-color: #2563eb;
    }

    .filter-input:disabled {
        background: #f3f4f6;
        color: #9ca3af;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .datepicker {
        width: 130px;
        cursor: pointer;
    }

    .datepicker:disabled {
        cursor: not-allowed;
    }

    .filter-sep {
        color: #9ca3af;
        font-size: 0.85rem;
    }

    input[type="number"].filter-input {
        width: 110px;
    }

    input[type="number"].filter-input:disabled {
        -webkit-appearance: none;
        -moz-appearance: textfield;
    }
</style>
<script>
    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        })[c]);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return '-';
        const dd = String(d.getDate()).padStart(2, '0');
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const yyyy = d.getFullYear();
        return `${dd}/${mm}/${yyyy}`;
    }

    function formatNumber(n) {
        return Number(n).toLocaleString('id-ID');
    }

    const state = {
        page: 1,
        sort: 'id',
        dir: 'asc',
        perPage: document.getElementById('perPage').value || '100',
        total: 0,
        totalPages: 0,
        tanggalDari: '',
        tanggalSampai: '',
        jumlahMin: '',
        jumlahMax: '',
        enableTanggal: true,
        enableJumlah: true,
    };

    const tbody = document.getElementById('tbody');
    const counterEl = document.getElementById('rowCounter');
    const pagerEl = document.getElementById('pagerNav');
    const perPageEl = document.getElementById('perPage');
    const enableTanggalEl = document.getElementById('enableTanggal');
    const enableJumlahEl = document.getElementById('enableJumlah');
    const tanggalDariEl = document.getElementById('tanggalDari');
    const tanggalSampaiEl = document.getElementById('tanggalSampai');
    const jumlahMinEl = document.getElementById('jumlahMin');
    const jumlahMaxEl = document.getElementById('jumlahMax');

    flatpickr('.datepicker', {
        dateFormat: 'd/m/Y',
        locale: 'default',
        disableMobile: true,
        onChange: function (selectedDates, dateStr, instance) {
            const isDari = instance.input.id === 'tanggalDari';
            if (isDari) {
                state.tanggalDari = dateStr;
                if (selectedDates[0] && !state.tanggalSampai) {
                    tanggalSampaiEl._flatpickr.set('minDate', selectedDates[0]);
                }
            } else {
                state.tanggalSampai = dateStr;
                if (selectedDates[0]) {
                    tanggalDariEl._flatpickr.set('maxDate', selectedDates[0]);
                }
            }
            state.page = 1;
            fetchData();
        },
        onClear: function (instance) {
            if (instance.input.id === 'tanggalDari') {
                state.tanggalDari = '';
                tanggalSampaiEl._flatpickr.set('minDate', null);
            } else {
                state.tanggalSampai = '';
                tanggalDariEl._flatpickr.set('maxDate', null);
            }
            state.page = 1;
            fetchData();
        }
    });

    function setTanggalEnabled(enabled) {
        state.enableTanggal = enabled;
        tanggalDariEl.disabled = !enabled;
        tanggalSampaiEl.disabled = !enabled;
        if (!enabled) {
            state.tanggalDari = '';
            state.tanggalSampai = '';
            tanggalDariEl._flatpickr.clear();
            tanggalSampaiEl._flatpickr.clear();
        }
        state.page = 1;
        fetchData();
    }

    function setJumlahEnabled(enabled) {
        state.enableJumlah = enabled;
        jumlahMinEl.disabled = !enabled;
        jumlahMaxEl.disabled = !enabled;
        if (!enabled) {
            state.jumlahMin = '';
            state.jumlahMax = '';
            jumlahMinEl.value = '';
            jumlahMaxEl.value = '';
        }
        state.page = 1;
        fetchData();
    }

    enableTanggalEl.addEventListener('change', () => setTanggalEnabled(enableTanggalEl.checked));
    enableJumlahEl.addEventListener('change', () => setJumlahEnabled(enableJumlahEl.checked));

    let jumlahDebounce = null;
    function onJumlahInput() {
        clearTimeout(jumlahDebounce);
        jumlahDebounce = setTimeout(() => {
            state.jumlahMin = jumlahMinEl.value;
            state.jumlahMax = jumlahMaxEl.value;
            state.page = 1;
            fetchData();
        }, 300);
    }
    jumlahMinEl.addEventListener('input', onJumlahInput);
    jumlahMaxEl.addEventListener('input', onJumlahInput);

    function updateCounter() {
        if (state.total === 0) {
            counterEl.textContent = '0 transaksi';
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
        counterEl.textContent = `Menampilkan ${from}–${to} dari ${state.total} transaksi`;
    }

    function renderRows(rows) {
        if (!rows.length) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="5">Tidak ada data transaksi.</td></tr>';
            return;
        }
        let html = '';
        rows.forEach(t => {
            html += `<tr>
                <td>${escapeHtml(t.id)}</td>
                <td>${formatDate(t.tanggal_transaksi)}</td>
                <td>${escapeHtml(t.nama_pelanggan)}</td>
                <td class="text-center">${formatNumber(t.jumlah_transaksi)}</td>
                <td class="action-cell">
                    <a href="<?= base_url('transaksi/edit/') ?>${t.id}" class="btn btn-sm btn-edit" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="<?= base_url('transaksi/delete/') ?>${t.id}" class="btn btn-sm btn-delete" title="Hapus"
                       onclick="return confirm('Hapus transaksi ini?')">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
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
        let html = `<button type="button" class="page-btn" data-page="${p - 1}" ${p === 1 ? 'disabled' : ''}>&lsaquo;</button>`;
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

    async function fetchData() {
        const params = new URLSearchParams({
            sort: state.sort,
            dir: state.dir,
            per_page: state.perPage,
            page: state.page,
        });
        if (state.enableTanggal) {
            if (state.tanggalDari) params.set('tanggal_dari', state.tanggalDari);
            if (state.tanggalSampai) params.set('tanggal_sampai', state.tanggalSampai);
        }
        if (state.enableJumlah) {
            if (state.jumlahMin) params.set('jumlah_min', state.jumlahMin);
            if (state.jumlahMax) params.set('jumlah_max', state.jumlahMax);
        }
        try {
            const res = await fetch(`<?= base_url('transaksi/data') ?>?${params.toString()}`, {
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
            tbody.innerHTML = `<tr class="empty-row"><td colspan="5">Gagal memuat data: ${escapeHtml(e.message)}</td></tr>`;
            counterEl.textContent = '';
            pagerEl.innerHTML = '';
        }
    }

    perPageEl.addEventListener('change', () => {
        state.perPage = perPageEl.value;
        state.page = 1;
        fetchData();
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
            fetchData();
        });
    });

    pagerEl.addEventListener('click', e => {
        const btn = e.target.closest('.page-btn');
        if (!btn || btn.disabled) return;
        const p = parseInt(btn.dataset.page, 10);
        if (!p || p < 1 || p > state.totalPages || p === state.page) return;
        state.page = p;
        fetchData();
    });

    fetchData();
</script>
<?= $this->endSection() ?>