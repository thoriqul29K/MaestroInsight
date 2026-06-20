<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-person-gear"></i> Kelola Akun Pengguna</h2>
        <div class="card-header-actions">
            <a href="<?= base_url('user/create') ?>" class="btn btn-primary" style="background-color: #2563eb;">
                <i class="bi bi-plus-lg"></i> Tambah Akun
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
                    <th class="sortable" data-sort="id">ID <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="username">Username <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="nama_lengkap">Nama Lengkap <i class="bi bi-arrow-down-up sort-icon"></i></th>
                    <th class="sortable" data-sort="role">Role <i class="bi bi-arrow-down-up sort-icon"></i></th>
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

    .badge-role {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .badge-superadmin {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-admin {
        background: #dbeafe;
        color: #1e40af;
    }

    .action-cell {
        white-space: nowrap;
    }

    .action-cell .btn {
        margin-right: 4px;
    }
</style>
<script>
    const ROLE_LABELS = {
        superadmin: '<span class="badge-role badge-superadmin">Superadmin</span>',
        admin: '<span class="badge-role badge-admin">Admin</span>',
    };

    const currentUserId = <?= session()->get('user_id') ?>;

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
        page: 1,
        sort: 'id',
        dir: 'asc',
        perPage: document.getElementById('perPage').value || '100',
        total: 0,
        totalPages: 0,
    };

    const tbody = document.getElementById('tbody');
    const counterEl = document.getElementById('rowCounter');
    const pagerEl = document.getElementById('pagerNav');
    const perPageEl = document.getElementById('perPage');

    function updateCounter() {
        if (state.total === 0) {
            counterEl.textContent = '0 akun';
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
        counterEl.textContent = `Menampilkan ${from}–${to} dari ${state.total} akun`;
    }

    function renderRows(rows) {
        if (!rows.length) {
            tbody.innerHTML = '<tr class="empty-row"><td colspan="5">Tidak ada data akun.</td></tr>';
            return;
        }
        let html = '';
        rows.forEach(u => {
            const roleBadge = ROLE_LABELS[u.role] || '<span class="badge-role badge-admin">Admin</span>';
            const isCurrentUser = u.id == currentUserId;
            let actions = `
                <a href="<?= base_url('user/edit/') ?>${u.id}" class="btn btn-sm btn-edit" title="Edit">
                    <i class="bi bi-pencil"></i>
                </a>`;
            if (!isCurrentUser) {
                actions += `
                <a href="<?= base_url('user/delete/') ?>${u.id}" class="btn btn-sm btn-delete" title="Hapus"
                   onclick="return confirm('Hapus akun ini?')">
                    <i class="bi bi-trash"></i>
                </a>`;
            }
            html += `<tr>
                <td>${escapeHtml(u.id)}</td>
                <td>${escapeHtml(u.username)}</td>
                <td>${escapeHtml(u.nama_lengkap)}</td>
                <td>${roleBadge}</td>
                <td class="action-cell">${actions}</td>
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
        try {
            const res = await fetch(`<?= base_url('user/data') ?>?${params.toString()}`, {
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