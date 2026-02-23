<?php
/**
 * PRESENTATION TIER - Admin Dashboard
 * Convert dari: dashboard.blade.php → PHP Native
 * Dipanggil dari: AdminDashboardController::index()
 */

$pageTitle = 'Dashboard';
$extraCss  = ['/presentation_tier/css/admin/dashboard-admin.css'];

ob_start(); ?>

<div class="dashboard-content">

    <div class="dashboard-header">
        <h2>Selamat Datang, <?= htmlspecialchars($admin['nama'] ?? 'Admin') ?>!</h2>
        <p>Berikut adalah ringkasan data di sistem Anda.</p>
    </div>

    <!-- Widget Cards -->
    <div class="widget-container">
        <div class="widget-card widget-warning">
            <div class="widget-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="widget-info">
                <h4>Permohonan Masuk</h4>
                <p class="widget-number"><?= (int)($totalDiproses ?? 0) ?></p>
                <small>Menunggu diproses</small>
            </div>
        </div>

        <div class="widget-card widget-success">
            <div class="widget-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="widget-info">
                <h4>Surat Disetujui</h4>
                <p class="widget-number"><?= (int)($totalSelesai ?? 0) ?></p>
                <small>Berhasil diselesaikan</small>
            </div>
        </div>

        <div class="widget-card widget-info">
            <div class="widget-icon">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="widget-info">
                <h4>Permohonan Ditolak</h4>
                <p class="widget-number"><?= (int)($totalDitolak ?? 0) ?></p>
                <small>Tidak disetujui</small>
            </div>
        </div>
    </div>

    <!-- Dashboard Grid -->
    <div class="dashboard-grid">

        <!-- Statistik Chart -->
        <div class="dashboard-card statistics-card">
            <h3><i class="fas fa-chart-pie"></i> Statistik Permohonan</h3>
            <div class="chart-container">
                <canvas id="statusChart"></canvas>
            </div>
            <div class="stats-legend">
                <div class="legend-item">
                    <span class="legend-dot bg-warning"></span>
                    <span>Diproses: <?= (int)($totalDiproses ?? 0) ?></span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot bg-success"></span>
                    <span>Selesai: <?= (int)($totalSelesai ?? 0) ?></span>
                </div>
                <div class="legend-item">
                    <span class="legend-dot bg-danger"></span>
                    <span>Ditolak: <?= (int)($totalDitolak ?? 0) ?></span>
                </div>
            </div>
        </div>

        <!-- Aktivitas Terkini -->
        <div class="dashboard-card recent-activity">
            <h3><i class="fas fa-history"></i> Aktivitas Terkini</h3>

            <?php if (!empty($recentPermohonan)): ?>
                <div class="activity-list">
                    <?php foreach ($recentPermohonan as $item): ?>
                        <?php
                        $status    = htmlspecialchars($item['status'] ?? '');
                        $statusLow = strtolower($status);
                        $waktu     = $item['created_at'] ?? '';
                        $diff      = time() - strtotime($waktu);
                        if ($diff < 60)        $diffStr = $diff . ' detik lalu';
                        elseif ($diff < 3600)  $diffStr = floor($diff / 60) . ' menit lalu';
                        elseif ($diff < 86400) $diffStr = floor($diff / 3600) . ' jam lalu';
                        else                   $diffStr = floor($diff / 86400) . ' hari lalu';
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-file"></i>
                            </div>
                            <div class="activity-details">
                                <p class="activity-title"><?= htmlspecialchars($item['jenis_surat'] ?? '') ?></p>
                                <p class="activity-user"><?= htmlspecialchars($item['user_name'] ?? 'User') ?></p>
                                <p class="activity-time"><?= $diffStr ?></p>
                            </div>
                            <div class="activity-status status-<?= $statusLow ?>">
                                <?= $status ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-data">Belum ada aktivitas terkini</p>
            <?php endif; ?>

            <a href="/web-pengajuan/admin/semua-permohonan" class="view-all">
                Lihat Semua <i class="fas fa-arrow-right"></i>
            </a>
        </div>

    </div><!-- /.dashboard-grid -->
</div><!-- /.dashboard-content -->

<?php $content = ob_get_clean();

// Scripts (setara @push('scripts') di Blade)
ob_start(); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
    const ctx = document.getElementById('statusChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Diproses', 'Selesai', 'Ditolak'],
                datasets: [{
                    data: [
                        <?= (int)($totalDiproses ?? 0) ?>,
                        <?= (int)($totalSelesai  ?? 0) ?>,
                        <?= (int)($totalDitolak  ?? 0) ?>
                    ],
                    backgroundColor: ['#f39c12', '#27ae60', '#e74c3c'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } }
            }
        });
    }
</script>
<?php $scripts = ob_get_clean();

require __DIR__ . '/partials/layout.php';