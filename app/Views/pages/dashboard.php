<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="row">
    <div class="col-grid stat-grid-dashboard">
        <div class="stat-card stat-primary">
            <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $totalPelanggan ?></div>
                <div class="stat-label">Total Pelanggan</div>
            </div>
        </div>
        <div class="stat-card stat-info">
            <div class="stat-icon"><i class="bi bi-receipt-cutoff"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $totalTransaksi ?></div>
                <div class="stat-label">Total Transaksi</div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-half">
        <div class="card">
            <div class="card-header">
                <h2><i class="bi bi-pie-chart"></i> Distribusi Segmen Pelanggan</h2>
            </div>
            <div class="chart-wrapper">
                <canvas id="chartSegmen"></canvas>
            </div>
        </div>
    </div>
    <div class="col-half">
        <div class="card">
            <div class="card-header">
                <h2><i class="bi bi-clock-history"></i> Transaksi Terbaru</h2>
            </div>
            <ul class="recent-list">
                <?php foreach ($transaksiBaru as $t): ?>
                    <li>
                        <div class="recent-info">
                            <strong><?= esc($t['nama_pelanggan']) ?></strong>
                            <small><?= esc($t['tujuan']) ?> &middot; <?= esc($t['layanan']) ?></small>
                        </div>
                        <div class="recent-amount">
                            <span class="amount">Rp <?= number_format($t['jumlah_transaksi'], 0, ',', '.') ?></span>
                            <small><?= esc(date('d/m/Y', strtotime($t['tanggal_transaksi']))) ?></small>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-bar-chart"></i> Ringkasan Segmen Pelanggan</h2>
        <a href="<?= base_url('analisis') ?>" class="btn btn-sm btn-primary">Lihat Analisis</a>
    </div>
    <div class="segmen-grid">
        <div class="segmen-card segmen-loyal">
            <i class="bi bi-star-fill"></i>
            <h3>Loyal</h3>
            <div class="segmen-count"><?= $segmen['loyal'] ?? 0 ?></div>
            <p>Pelanggan setia dengan transaksi rutin dan nilai tinggi.</p>
        </div>
        <div class="segmen-card segmen-potential">
            <i class="bi bi-graph-up-arrow"></i>
            <h3>Potential</h3>
            <div class="segmen-count"><?= $segmen['potential'] ?? 0 ?></div>
            <p>Berpotensi menjadi pelanggan bernilai tinggi.</p>
        </div>
        <div class="segmen-card segmen-budget">
            <i class="bi bi-piggy-bank"></i>
            <h3>Budget Hunter</h3>
            <div class="segmen-count"><?= $segmen['budget'] ?? 0 ?></div>
            <p>Sensitif terhadap harga, suka promo & diskon.</p>
        </div>
        <div class="segmen-card segmen-seasonal">
            <i class="bi bi-calendar-event"></i>
            <h3>Seasonal</h3>
            <div class="segmen-count"><?= $segmen['seasonal'] ?? 0 ?></div>
            <p>Transaksi pada waktu-waktu tertentu (musim/libur).</p>
        </div>
        <div class="segmen-card segmen-risk">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <h3>At Risk</h3>
            <div class="segmen-count"><?= $segmen['at_risk'] ?? 0 ?></div>
            <p>Sudah lama tidak bertransaksi, perlu di-reaktivasi.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const segmenData = <?= json_encode([
                            'labels' => ['Loyal', 'Potential', 'Budget Hunter', 'Seasonal', 'At Risk', 'Belum'],
                            'data'   => [
                                $segmen['loyal'] ?? 0,
                                $segmen['potential'] ?? 0,
                                $segmen['budget'] ?? 0,
                                $segmen['seasonal'] ?? 0,
                                $segmen['at_risk'] ?? 0,
                                $segmen['belum'] ?? 0,
                            ],
                        ]) ?>;
    const colors = ['#10b981', '#3b82f6', '#f59e0b', '#8b5cf6', '#ef4444', '#9ca3af'];

    const ctx = document.getElementById('chartSegmen');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: segmenData.labels,
                datasets: [{
                    data: segmenData.data,
                    backgroundColor: colors,
                    borderWidth: 2,
                    borderColor: '#fff',
                }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                },
            },
        });
    }
</script>
<?= $this->endSection() ?>