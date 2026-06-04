<?= $this->extend('layouts/main') ?>

<?= $this->section('main') ?>
<div class="row">
    <div class="col-grid">
        <div class="stat-card stat-loyal">
            <div class="stat-icon"><i class="bi bi-star-fill"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $ringkasan['loyal'] ?? 0 ?></div>
                <div class="stat-label">Loyal</div>
            </div>
        </div>
        <div class="stat-card stat-potential">
            <div class="stat-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $ringkasan['potential'] ?? 0 ?></div>
                <div class="stat-label">Potential</div>
            </div>
        </div>
        <div class="stat-card stat-budget">
            <div class="stat-icon"><i class="bi bi-piggy-bank"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $ringkasan['budget'] ?? 0 ?></div>
                <div class="stat-label">Budget Hunter</div>
            </div>
        </div>
        <div class="stat-card stat-seasonal">
            <div class="stat-icon"><i class="bi bi-calendar-event"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $ringkasan['seasonal'] ?? 0 ?></div>
                <div class="stat-label">Seasonal</div>
            </div>
        </div>
        <div class="stat-card stat-risk">
            <div class="stat-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $ringkasan['at_risk'] ?? 0 ?></div>
                <div class="stat-label">At Risk</div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-cpu"></i> Tahapan Segmentasi</h2>
    </div>
    <div class="action-buttons">
        <form action="<?= base_url() ?>analisis/proses-rfm" method="post" style="display:inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-calculator"></i> 1. Hitung Nilai RFM
            </button>
        </form>
        <form action="<?= base_url() ?>analisis/proses-segmentasi" method="post" style="display:inline">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-success">
                <i class="bi bi-diagram-3"></i> 2. Proses Hierarchical Clustering
            </button>
        </form>
    </div>
    <p class="hint">
        <i class="bi bi-info-circle"></i>
        Langkah 1: Menghitung Recency, Frequency, Monetary dari data transaksi (dengan normalisasi Min-Max).<br>
        Langkah 2: Menjalankan Agglomerative Hierarchical Clustering (Ward Linkage, Euclidean) dengan k = 5 cluster.
    </p>
</div>

<div class="card">
    <div class="card-header">
        <h2><i class="bi bi-table"></i> Data RFM Pelanggan</h2>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Pelanggan</th>
                    <th>Recency (hari)</th>
                    <th>Frequency</th>
                    <th>Monetary (Rp)</th>
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
                foreach ($rfm as $r): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= esc($r['nama_pelanggan']) ?></td>
                        <td><?= (int) $r['recency'] ?></td>
                        <td><?= (int) $r['frequency'] ?></td>
                        <td class="text-right"><?= number_format($r['monetary'], 0, ',', '.') ?></td>
                        <td><?= $segmentLabel[$r['segment']] ?? '<span class="badge badge-default">Belum</span>' ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($rfm)): ?>
                    <tr><td colspan="6" class="text-center">Belum ada data. Klik "Hitung Nilai RFM" terlebih dahulu.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
