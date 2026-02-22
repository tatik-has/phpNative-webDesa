<?php
/**
 * PRESENTATION TIER - Halaman Pilih Jenis Surat
 * Route: GET /pengajuan
 */

$pageTitle = 'Pengajuan Surat - Desa Pakning Asal';
$extraCss  = ['/web-pengajuan/presentation_tier/css/masyarakat/pengajuan.css'];

ob_start(); ?>

<main class="pengajuan-container">
    <h1 class="pengajuan-title">Surat Apa Yang Ingin Anda Ajukan?</h1>

    <div class="card-container">

        <a href="/web-pengajuan/pengajuan/sktm" class="surat-card">
            <div class="kartu-icon kartu-icon-sktm">
                <i class="fas fa-hand-holding-heart"></i>
            </div>
            <div class="kartu-body">
                <h3>Surat Keterangan Tidak Mampu</h3>
                <p>Untuk warga yang membutuhkan keterangan kondisi ekonomi guna keperluan tertentu.</p>
            </div>
            <div class="kartu-footer">
                <span>Ajukan Sekarang <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>

        <a href="/web-pengajuan/pengajuan/domisili" class="surat-card">
            <div class="kartu-icon kartu-icon-domisili">
                <i class="fas fa-home"></i>
            </div>
            <div class="kartu-body">
                <h3>Surat Keterangan Domisili</h3>
                <p>Untuk warga yang membutuhkan keterangan tempat tinggal/domisili saat ini.</p>
            </div>
            <div class="kartu-footer">
                <span>Ajukan Sekarang <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>

        <a href="/web-pengajuan/pengajuan/sku" class="surat-card">
            <div class="kartu-icon kartu-icon-sku">
                <i class="fas fa-briefcase"></i>
            </div>
            <div class="kartu-body">
                <h3>Surat Keterangan Usaha</h3>
                <p>Untuk pelaku usaha yang membutuhkan keterangan legalitas usaha di desa.</p>
            </div>
            <div class="kartu-footer">
                <span>Ajukan Sekarang <i class="fas fa-arrow-right"></i></span>
            </div>
        </a>

    </div>
</main>

<?php $content = ob_get_clean();
$scripts = '';
require __DIR__ . '/../layout.php';