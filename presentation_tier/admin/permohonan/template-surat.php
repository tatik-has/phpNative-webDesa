<?php
/**
 * PRESENTATION TIER - Template Surat Admin (FITUR BARU)
 * Pengganti: tidak ada di Laravel (fitur baru)
 * Admin preview surat HTML → klik generate → status jadi Selesai
 */

$pageTitle = 'Template Surat - ' . htmlspecialchars($jenisSurat ?? '');
$extraCss  = ['/presentation_tier/css/admin/detail-surat.css'];

ob_start(); ?>

<div class="container-detail mt-4">
    <div class="mb-3">
        <a href="/web-pengajuan/admin/<?= htmlspecialchars($type ?? '') ?>/<?= (int)($permohonan['id'] ?? 0) ?>" class="btn-back">
            ← Kembali ke Detail
        </a>
    </div>

    <div class="card-detail shadow-sm border-0">
        <div class="card-detail-header">
            <div class="header-content">
                <div class="header-text">
                    <h5 class="mb-0">Preview Template Surat</h5>
                    <p class="header-subtitle"><?= htmlspecialchars($jenisSurat ?? '') ?> — <?= htmlspecialchars($permohonan['nama'] ?? '') ?></p>
                </div>
            </div>
        </div>
        <div class="card-detail-body px-5 py-4">

            <!-- Preview Surat -->
            <div id="preview-surat" style="border:1px solid #ddd;border-radius:8px;padding:20px;background:#fff;margin-bottom:24px;">
                <?= $templateHtml ?? '<p>Template tidak tersedia.</p>' ?>
            </div>

            <!-- Tombol Aksi -->
            <div style="display:flex;gap:12px;flex-wrap:wrap;justify-content:center;">
                <!-- Generate & Simpan -->
                <form action="/web-pengajuan/admin/surat/<?= htmlspecialchars($type ?? '') ?>/<?= (int)($permohonan['id'] ?? 0) ?>/generate-surat" method="POST">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">
                    <button type="submit" class="btn btn-selesai"
                        onclick="return confirm('Generate surat ini dan ubah status menjadi Selesai?')"
                        style="background:#27ae60;color:#fff;padding:10px 24px;border:none;border-radius:6px;cursor:pointer;font-size:14px;font-weight:600;">
                        <i class="fas fa-check-circle"></i> Generate &amp; Kirim ke Masyarakat
                    </button>
                </form>

                <!-- Cetak -->
                <a href="/web-pengajuan/admin/surat/<?= htmlspecialchars($type ?? '') ?>/<?= (int)($permohonan['id'] ?? 0) ?>/print"
                   target="_blank"
                   style="background:#3498db;color:#fff;padding:10px 24px;border-radius:6px;text-decoration:none;font-size:14px;font-weight:600;">
                    <i class="fas fa-print"></i> Cetak / Print
                </a>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();
require __DIR__ . '/../partials/layout.php';