<?php

/**
 * PRESENTATION TIER - Riwayat Pengajuan Masyarakat
 * Pengganti: presentation_tier/masyarakat/riwayat/index.blade.php (Doc 12)
 * Perubahan: $item->status->value ?? $item->status → $item['status']
 *            $item->created_at->format() → date('', strtotime())
 *            Storage::url() → '/' . ltrim($path, '/')
 */

$pageTitle = 'Riwayat Pengajuan';
$extraCss  = [
    '/web-pengajuan/presentation_tier/css/masyarakat/riwayat.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
];

ob_start();

$riwayat = $riwayat ?? [];
$total   = count($riwayat);
?>

<div class="riwayat-wrapper">
    <div class="container riwayat-container">

        <div class="riwayat-header">
            <h1 class="riwayat-title">Riwayat Pengajuan</h1>
            <p class="riwayat-subtitle">Pantau dan kelola semua pengajuan surat Anda di sini</p>
        </div>

        <div class="riwayat-card">
            <div class="riwayat-toolbar">
                <div class="toolbar-filters">
                    <button class="filter-btn active" data-filter="semua">Semua</button>
                    <button class="filter-btn" data-filter="Diproses">Diproses</button>
                    <button class="filter-btn" data-filter="Selesai">Selesai</button>
                    <button class="filter-btn" data-filter="Ditolak">Ditolak</button>
                </div>
                <div class="toolbar-info">
                    <span class="count-label">Total Pengajuan: <strong><?= $total ?></strong></span>
                </div>
            </div>

            <div class="riwayat-body">
                <?php if (empty($riwayat)): ?>
                    <div class="no-data" style="text-align:center;padding:40px;color:#888;">
                        <i class="fas fa-folder-open" style="font-size:48px;margin-bottom:16px;display:block;"></i>
                        <p>Anda belum pernah mengajukan surat.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($riwayat as $item):
                        $statusValue = $item['status'] ?? '';
                        $statusLow   = strtolower($statusValue);
                        $jenisSurat  = $item['jenis_surat'] ?? '';
                        $type        = $item['type'] ?? 'domisili';

                        // Icon per jenis
                        $icon = match ($type) {
                            'domisili' => 'fa-home',
                            'ktm'      => 'fa-hand-holding-heart',
                            'sku'      => 'fa-briefcase',
                            default    => 'fa-file-alt',
                        };

                        $tglPengajuan = date('d M Y', strtotime($item['created_at'] ?? ''));
                        $jamPengajuan = date('H:i', strtotime($item['created_at'] ?? ''));
                        $nama         = htmlspecialchars($item['nama'] ?? '-');

                        // Unduh surat jika selesai
                        $pathSurat = $item['path_surat_jadi'] ?? null;
                        $downloadUrl = $pathSurat ? '/web-pengajuan/' . ltrim($pathSurat, '/') : null;
                    ?>
                        <div class="riwayat-item" data-status="<?= htmlspecialchars($statusValue) ?>">

                            <div class="item-left-content">
                                <div class="item-icon">
                                    <i class="fas <?= $icon ?>"></i>
                                </div>
                                <div class="item-details">
                                    <div class="item-title-wrapper">
                                        <h5 class="item-title"><?= htmlspecialchars($jenisSurat) ?></h5>
                                        <span class="status-badge status-<?= $statusLow ?>">
                                            <?= htmlspecialchars($statusValue) ?>
                                        </span>
                                    </div>
                                    <div class="item-meta">
                                        <span><i class="far fa-calendar-alt"></i> <?= $tglPengajuan ?></span>
                                        <span class="dot">•</span>
                                        <span><i class="far fa-clock"></i> <?= $jamPengajuan ?> WIB</span>
                                        <span class="dot">•</span>
                                        <span><i class="far fa-user"></i> <?= $nama ?></span>
                                    </div>

                                    <?php if ($statusValue === 'Ditolak' && !empty($item['keterangan_penolakan'])): ?>
                                        <p class="alasan-penolakan" style="color:#dc3545;margin-top:6px;font-size:13px;">
                                            <strong>Alasan Penolakan:</strong> <?= htmlspecialchars($item['keterangan_penolakan']) ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="item-right-actions">
                                <?php if ($statusValue === 'Selesai' && $downloadUrl): ?>
                                    <a href="<?= htmlspecialchars($downloadUrl) ?>"
                                        download
                                        target="_blank"
                                        class="btn-download">
                                        <i class="fas fa-download"></i> Unduh
                                    </a>
                                <?php endif; ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();

ob_start(); ?>
<script>
    document.querySelectorAll('.filter-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;
            document.querySelectorAll('.riwayat-item').forEach(item => {
                if (filter === 'semua' || item.dataset.status === filter) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
</script>
<?php $scripts = ob_get_clean();

require __DIR__ . '/../layout.php';
