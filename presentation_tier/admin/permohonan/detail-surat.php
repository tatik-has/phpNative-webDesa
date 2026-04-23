<?php
/**
 * PRESENTATION TIER - Detail Surat Admin
 * PERBAIKAN:
 * 1. fileUrl() tambah /web-pengajuan/ prefix agar gambar muncul
 * 2. Semua URL tombol tambah /web-pengajuan/ prefix
 * 3. $jenis check pakai str_contains agar lebih fleksibel
 */

$pageTitle = 'Detail Surat - ' . ($title ?? 'Permohonan');
$extraCss  = ['/presentation_tier/css/admin/detail-surat.css'];

// PERBAIKAN 1: tambah /web-pengajuan/ agar browser bisa akses file upload
function fileUrl(string $path): string {
    return '/web-pengajuan/' . ltrim($path, '/');
}
function fileExt(string $path): string {
    return strtolower(pathinfo($path, PATHINFO_EXTENSION));
}
function renderDocItem(string $url, string $ext, string $label): string {
    $imgs = ['jpg','jpeg','png','gif'];
    if (in_array($ext, $imgs)) {
        $preview = "<img src=\"{$url}\" alt=\"{$label}\" style=\"width:100%;height:100%;object-fit:cover;\">";
    } elseif ($ext === 'pdf') {
        $preview = '<div class="file-icon-preview pdf-preview"><i class="fas fa-file-pdf"></i><span>PDF</span></div>';
    } elseif (in_array($ext, ['doc','docx'])) {
        $preview = '<div class="file-icon-preview doc-preview"><i class="fas fa-file-word"></i><span>Word</span></div>';
    } else {
        $preview = '<div class="file-icon-preview"><i class="fas fa-file"></i><span>' . strtoupper($ext) . '</span></div>';
    }
    return "
    <div class=\"document-item\">
        <div class=\"document-preview\" data-url=\"{$url}\">
            {$preview}
            <div class=\"document-overlay\">
                <a href=\"{$url}\" target=\"_blank\" class=\"btn-view\">
                    <svg width=\"20\" height=\"20\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\">
                        <path d=\"M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z\"/><circle cx=\"12\" cy=\"12\" r=\"3\"/>
                    </svg> Lihat
                </a>
            </div>
        </div>
        <p class=\"document-label\">{$label}</p>
    </div>";
}

ob_start();


$p         = $permohonan ?? [];
$jenis     = $jenis_surat ?? '';
$statusVal = $p['status'] ?? '';
$statusLow = strtolower($statusVal);

$statusBadge = match($statusLow) {
    'diproses' => 'status-diproses',
    'diterima' => 'status-diterima',
    'selesai'  => 'status-selesai',
    'ditolak'  => 'status-ditolak',
    default    => 'status-default',
};

// PERBAIKAN 2: URL pakai /web-pengajuan/ prefix
$typeVal = htmlspecialchars($type ?? '');
$idVal   = $p['id'] ?? '';

$typeCheck  = strtolower(trim($type ?? ''));
$isDomisili = $typeCheck === 'domisili' || str_contains($jenis, 'Domisili');
$isKTM      = $typeCheck === 'ktm'      || str_contains($jenis, 'Tidak Mampu') || str_contains(strtolower($jenis), 'sktm');
$isSKU      = $typeCheck === 'sku'      || str_contains($jenis, 'Usaha')       || str_contains($jenis, 'SKU');
?>

<div class="container-detail mt-4">
    <div class="mb-3">
        <a href="/web-pengajuan/admin/surat" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                 fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M19 12H5M12 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Permohonan
        </a>
    </div>

    <div class="card-detail shadow-sm border-0">
        <div class="card-detail-header">
            <div class="header-content">
                <div class="header-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                </div>
                <div class="header-text">
                    <h5 class="mb-0">Detail Surat <?= strtoupper(htmlspecialchars($title ?? 'SURAT')) ?></h5>
                    <p class="header-subtitle">Informasi lengkap permohonan surat</p>
                </div>
            </div>
        </div>

        <div class="card-detail-body px-5 py-4">

            <!-- DATA PEMOHON -->
            <h4 class="section-title">Data Pemohon</h4>
            <div class="info-grid">
                <div class="info-item-modern">
                    <label>Nama Lengkap</label>
                    <p><?= htmlspecialchars($p['nama'] ?? '-') ?></p>
                </div>
                <div class="info-item-modern">
                    <label>NIK</label>
                    <p><?= htmlspecialchars($p['nik'] ?? '-') ?></p>
                </div>
                <?php if (!empty($p['jenis_kelamin'])): ?>
                <div class="info-item-modern">
                    <label>Jenis Kelamin</label>
                    <p><?= htmlspecialchars($p['jenis_kelamin']) ?></p>
                </div>
                <?php endif; ?>
                <?php if (!empty($p['nomor_telp'])): ?>
                <div class="info-item-modern">
                    <label>Nomor Telepon</label>
                    <p><?= htmlspecialchars($p['nomor_telp']) ?></p>
                </div>
                <?php endif; ?>
                <div class="info-item-modern">
                    <label>Tanggal Pengajuan</label>
                    <p><?= !empty($p['created_at']) ? date('d-m-Y H:i', strtotime($p['created_at'])) . ' WIB' : '-' ?></p>
                </div>
                <div class="info-item-modern">
                    <label>Status Permohonan</label>
                    <p>
                        <span class="status-badge <?= $statusBadge ?>">
                            <span class="status-dot"></span>
                            <?= htmlspecialchars($statusVal) ?>
                        </span>
                    </p>
                </div>
                <?php if (!empty($p['rt_domisili']) && !empty($p['rw_domisili'])): ?>
                <div class="info-item-modern">
                    <label>RT / RW Domisili</label>
                    <p>RT <?= htmlspecialchars($p['rt_domisili']) ?> / RW <?= htmlspecialchars($p['rw_domisili']) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- DATA KHUSUS DOMISILI -->
            <?php if ($isDomisili): ?>
            <hr class="divider-modern">
            <h4 class="section-title">Data Domisili</h4>
            <div class="info-grid">
                <div class="info-item-modern">
                    <label>Alamat Domisili</label>
                    <p><?= htmlspecialchars($p['alamat_domisili'] ?? '-') ?></p>
                </div>
                <div class="info-item-modern">
                    <label>Alamat KTP</label>
                    <p><?= htmlspecialchars($p['alamat_ktp'] ?? '-') ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- DATA KHUSUS SKTM -->
            <?php if ($isKTM): ?>
            <hr class="divider-modern">
            <h4 class="section-title">Data Ekonomi &amp; Keperluan</h4>
            <div class="info-grid">
                <div class="info-item-modern">
                    <label>Alamat Lengkap</label>
                    <p><?= htmlspecialchars($p['alamat_lengkap'] ?? '-') ?></p>
                </div>
                <div class="info-item-modern">
                    <label>Keperluan</label>
                    <p><?= htmlspecialchars($p['keperluan'] ?? '-') ?></p>
                </div>
                <div class="info-item-modern">
                    <label>Penghasilan / Bulan</label>
                    <p>Rp <?= number_format((float)($p['penghasilan'] ?? 0), 0, ',', '.') ?></p>
                </div>
                <div class="info-item-modern">
                    <label>Jumlah Tanggungan</label>
                    <p><?= htmlspecialchars($p['jumlah_tanggungan'] ?? '-') ?> orang</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- DATA KHUSUS SKU -->
            <?php if ($isSKU): ?>
            <hr class="divider-modern">
            <h4 class="section-title">Data Usaha</h4>
            <div class="info-grid">
                <div class="info-item-modern">
                    <label>Alamat KTP</label>
                    <p><?= htmlspecialchars($p['alamat_ktp'] ?? '-') ?></p>
                </div>
                <div class="info-item-modern">
                    <label>Nama Usaha</label>
                    <p><?= htmlspecialchars($p['nama_usaha'] ?? '-') ?></p>
                </div>
                <div class="info-item-modern">
                    <label>Jenis Usaha</label>
                    <p><?= htmlspecialchars($p['jenis_usaha'] ?? '-') ?></p>
                </div>
                <div class="info-item-modern">
                    <label>Alamat Usaha</label>
                    <p><?= htmlspecialchars($p['alamat_usaha'] ?? '-') ?></p>
                </div>
                <div class="info-item-modern">
                    <label>Lama Usaha</label>
                    <p><?= htmlspecialchars($p['lama_usaha'] ?? '-') ?></p>
                </div>
            </div>
            <?php endif; ?>

            <!-- DOKUMEN PENDUKUNG -->
            <hr class="divider-modern">
            <h4 class="section-title">📎 Dokumen Pendukung</h4>
            <div class="document-grid">
                <?php if (!empty($p['path_ktp'])): ?>
                    <?= renderDocItem(fileUrl($p['path_ktp']), fileExt($p['path_ktp']), 'Scan/Foto KTP') ?>
                <?php endif; ?>
                <?php if (!empty($p['path_kk'])): ?>
                    <?= renderDocItem(fileUrl($p['path_kk']), fileExt($p['path_kk']), 'Kartu Keluarga') ?>
                <?php endif; ?>
                <!-- Disesuaikan: SKTM menyimpan di path_surat_pengantar_rt_rw (dari SuratKtmService::storeKtm) -->
                <?php if (!empty($p['path_surat_pengantar_rt_rw'])): ?>
                    <?= renderDocItem(fileUrl($p['path_surat_pengantar_rt_rw']), fileExt($p['path_surat_pengantar_rt_rw']), 'Surat Pengantar RT/RW') ?>
                <?php endif; ?>
                <!-- Disesuaikan: SKTM menyimpan di path_foto_rumah (dari SuratKtmService::storeKtm) -->
                <?php if (!empty($p['path_foto_rumah'])): ?>
                    <?= renderDocItem(fileUrl($p['path_foto_rumah']), fileExt($p['path_foto_rumah']), 'Foto Rumah Tampak Depan') ?>
                <?php endif; ?>
                <?php if (!empty($p['path_surat_pengantar'])): ?>
                    <?= renderDocItem(fileUrl($p['path_surat_pengantar']), fileExt($p['path_surat_pengantar']), 'Surat Pengantar RT/RW') ?>
                <?php endif; ?>
                <?php if (!empty($p['path_foto_usaha'])): ?>
                    <?= renderDocItem(fileUrl($p['path_foto_usaha']), fileExt($p['path_foto_usaha']), 'Foto Tempat Usaha') ?>
                <?php endif; ?>
            </div>

            <!-- TOMBOL AKSI -->
            <hr class="divider-modern">
            <div class="text-center mt-4">
                <?php if ($statusVal === 'Selesai'): ?>
                    <a href="/web-pengajuan/admin/surat/<?= $typeVal ?>/<?= $idVal ?>/print"
                       class="btn-print" target="_blank">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                             fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 6 2 18 2 18 9"/>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                            <rect x="6" y="14" width="12" height="8"/>
                        </svg>
                        Cetak Surat
                    </a>
                <?php elseif ($statusVal === 'Diterima'): ?>
                    <a href="/web-pengajuan/admin/surat/<?= $typeVal ?>/<?= $idVal ?>/template"
                       class="btn-print" style="background:#3498db;">
                        <i class="fas fa-file-alt"></i> Buat Template Surat
                    </a>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php $content = ob_get_clean();

ob_start(); ?>
<script>
document.querySelectorAll('.document-preview').forEach(function(preview) {
    preview.style.cursor = 'pointer';
    preview.addEventListener('click', function(e) {
        if (!e.target.closest('.btn-view')) {
            const url = this.getAttribute('data-url');
            if (url) window.open(url, '_blank');
        }
    });
});
</script>
<?php $scripts = ob_get_clean();

require __DIR__ . '/../partials/layout.php';