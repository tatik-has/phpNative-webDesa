<?php
/**
 * PRESENTATION TIER - Laporan Admin
 * Pengganti: presentation_tier/admin/permohonan/laporan.blade.php
 */

$pageTitle = 'Laporan Permohonan Surat';
$extraCss  = ['/presentation_tier/css/admin/admin-laporan.css'];

ob_start(); ?>

<div class="report-page">
    <div class="report-header">
        <h2>Laporan Permohonan Surat</h2>
        <p>Laporan data permohonan surat yang telah diproses dalam sistem</p>
    </div>

    <!-- Filter -->
    <div class="filter-card">
        <form action="/admin/laporan" method="GET" class="filter-form">
            <div class="filter-group">
                <label for="tanggal_mulai">Dari Tanggal</label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?= htmlspecialchars($tanggalMulai ?? '') ?>">
            </div>
            <div class="filter-group">
                <label for="tanggal_akhir">Sampai Tanggal</label>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir" value="<?= htmlspecialchars($tanggalAkhir ?? '') ?>">
            </div>
            <div class="filter-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="status-select">
                    <option value="semua" <?= ($statusFilter ?? '') === 'semua' ? 'selected' : '' ?>>Semua</option>
                    <option value="selesai" <?= ($statusFilter ?? '') === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                    <option value="ditolak" <?= ($statusFilter ?? '') === 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                </select>
            </div>
            <div class="filter-actions">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="/admin/laporan?tanggal_mulai=<?= htmlspecialchars($tanggalMulai ?? '') ?>&tanggal_akhir=<?= htmlspecialchars($tanggalAkhir ?? '') ?>&status=<?= htmlspecialchars($statusFilter ?? 'semua') ?>&export=word"
                   class="btn btn-success">
                    <i class="fa-solid fa-file-word"></i> Unduh Laporan
                </a>
            </div>
        </form>
    </div>

    <?php if (!empty($statusFilter) && $statusFilter !== 'semua'): ?>
    <div class="filter-info">
        <i class="fa-solid fa-info-circle"></i>
        Menampilkan data dengan status: <strong><?= htmlspecialchars(ucfirst($statusFilter)) ?></strong>
    </div>
    <?php endif; ?>

    <!-- Tabel -->
    <div class="report-table-wrapper">
        <table class="report-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Nama Pemohon</th>
                    <th>Jenis Surat</th>
                    <th>Status</th>
                    <th>Tanggal Selesai/Ditolak</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($allPermohonan)): ?>
                    <tr>
                        <td colspan="6" class="empty-state">
                            Tidak ada data permohonan dengan filter ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($allPermohonan as $i => $item): ?>
                        <?php
                        $statusVal = $item['status'] ?? '';
                        $statusNorm = strtolower($statusVal);
                        $badge = match($statusNorm) {
                            'selesai'  => 'status-selesai',
                            'diproses' => 'status-diproses',
                            'ditolak'  => 'status-ditolak',
                            default    => '',
                        };
                        $tglSelesai = (in_array($statusNorm, ['selesai','ditolak']) && !empty($item['updated_at']))
                            ? date('d M Y, H:i', strtotime($item['updated_at'])) . ' WIB'
                            : '-';
                        ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td><?= date('d M Y, H:i', strtotime($item['created_at'] ?? '')) ?></td>
                            <td><?= htmlspecialchars($item['user_name'] ?? $item['nama'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($item['jenis_surat_label'] ?? '') ?></td>
                            <td><span class="status <?= $badge ?>"><?= htmlspecialchars($statusVal) ?></span></td>
                            <td><?= $tglSelesai ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $content = ob_get_clean();
require __DIR__ . '/../partials/layout.php';