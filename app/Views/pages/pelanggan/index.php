<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-people"></i> Daftar Pelanggan</h2>
        <div class="card-header-actions">
            <a href="<?= base_url('pelanggan/create') ?>" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Tambah Pelanggan
            </a>
        </div>
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
        <div class="pagination-info">
            <span id="rowCounter" class="row-counter">Memuat…</span>
        </div>
    </div>

    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th class="sortable" data-sort="id">ID Pelanggan <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="nama_pelanggan">Nama Pelanggan <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="email">Email <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="telepon">Telepon <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="agama">Agama <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="segment">Segmentasi <i class="bi bi-arrow-down-up sort-icon"></i></th>
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
    .row-counter { color: #6b7280; font-size: 0.9rem; }

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
        loyal:     '<span class="badge badge-loyal">Loyal</span>',
        potential: '<span class="badge badge-potential">Potential</span>',
        budget:    '<span class="badge badge-budget">Budget Hunter</span>',
        seasonal:  '<span class="badge badge-seasonal">Seasonal</span>',
        at_risk:   '<span class="badge badge-risk">At Risk</span>',
    };

    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[c]);
    }

    const state = {
        page:     1,
        sort:     'id',
        dir:      'asc',
        perPage:  document.getElementById('perPage').value || '100',
        total:    0,
        totalPages: 0,
    };

    const tbody     = document.getElementById('tbody');
    const counterEl = document.getElementById('rowCounter');
    const pagerEl   = document.getElementById('pagerNav');
    const perPageEl = document.getElementById('perPage');

    function updateCounter() {
        if (state.total === 0) { counterEl.textContent = '0 pelanggan'; return; }
        let from, to;
        if (state.perPage === 'all') { from = 1; to = state.total; }
        else {
            const pp = parseInt(state.perPage, 10);
            from = (state.page - 1) * pp + 1;
            to   = Math.min(state.page * pp, state.total);
        }
        counterEl.textContent = `Menampilkan ${from}–${to} dari ${state.total} pelanggan`;
    }

    function renderRows(rows) {
        if (!rows.length) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="7">Tidak ada data pelanggan.</td></tr>';
            return;
        }
        let html = '';
        rows.forEach(p => {
            const segBadge = SEGMENT_LABELS[p.segment] || '<span class="badge badge-default">Belum</span>';
            html += `<tr>
                <td>${escapeHtml(p.id)}</td>
                <td>${escapeHtml(p.nama_pelanggan)}</td>
                <td>${escapeHtml(p.email)}</td>
                <td>${escapeHtml(p.telepon)}</td>
                <td>${escapeHtml(p.agama) || '-'}</td>
                <td>${segBadge}</td>
                <td class="action-cell">
                    <a href="<?= base_url('pelanggan/edit/') ?>${p.id}" class="btn btn-sm btn-edit" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="<?= base_url('pelanggan/delete/') ?>${p.id}" class="btn btn-sm btn-delete" title="Hapus"
                       onclick="return confirm('Hapus pelanggan ini?')">
                        <i class="bi bi-trash"></i>
                    </a>
                </td>
            </tr>`;
        });
        tbody.innerHTML = html;
    }

    function renderPager() {
        if (state.totalPages <= 1) { pagerEl.innerHTML = ''; return; }
        const tp = state.totalPages;
        const p  = state.page;
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
            sort:     state.sort,
            dir:      state.dir,
            per_page: state.perPage,
            page:     state.page,
        });
        try {
            const res = await fetch(`<?= base_url('pelanggan/data') ?>?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!res.ok) throw new Error('Gagal memuat data');
            const data = await res.json();
            state.total      = data.total;
            state.totalPages = data.total_pages;
            state.page       = data.page;
            renderRows(data.rows);
            renderPager();
            updateCounter();
            updateSortIcons();
        } catch (e) {
            tbody.innerHTML = `<tr class="empty-row"><td colspan="7">Gagal memuat data: ${escapeHtml(e.message)}</td></tr>`;
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
