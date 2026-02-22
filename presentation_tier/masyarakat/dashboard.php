<?php

/**
 * PRESENTATION TIER - Dashboard Masyarakat (Publik)
 * Pengganti: presentation_tier/dashboard.blade.php
 * Perubahan: @if(session()) → PHP session, SweetAlert flash via session PHP, tanpa @auth
 */

$pageTitle = 'Beranda - Desa Pakning Asal';
$extraCss = ['/web-pengajuan/presentation_tier/css/masyarakat/dashboard.css'];
$extraJs   = ['https://cdn.jsdelivr.net/npm/sweetalert2@11'];

if (session_status() === PHP_SESSION_NONE) session_start();
// Tangkap sebelum layout menghapus session
$swSuccess = $_SESSION['success'] ?? null;
$swError   = $_SESSION['error']   ?? null;

ob_start(); ?>

<main class="hero-container">
    <div class="hero-content">
        <div class="hero-text">
            <p class="hero-subtitle">Administrasi Surat-Menyurat</p>
            <h1 class="hero-title">Desa Pakning Asal</h1>
        </div>
        <a href="/web-pengajuan/pengajuan" class="hero-button">
            <i class="fas fa-file-alt"></i>
            <span>Pengajuan Surat</span>
        </a>
    </div>
    <p class="hero-tagline">Mempermudah Setiap Proses, Mempercepat Setiap Langkah.</p>
</main>

<?php $content = ob_get_clean();

ob_start(); ?>
<script>
    const swalConfig = {
        confirmButtonColor: '#2c3e50',
        timer: 5000,
        timerProgressBar: true,
        showClass: {
            popup: 'animate__animated animate__fadeInDown animate__faster'
        },
        hideClass: {
            popup: 'animate__animated animate__fadeOutUp animate__faster'
        },
        width: window.innerWidth < 576 ? '90%' : '32em',
        padding: window.innerWidth < 576 ? '1.5em' : '3em'
    };

    <?php if ($swSuccess): ?>
        Swal.fire({
            ...swalConfig,
            title: 'Terima Kasih!',
            text: <?= json_encode($swSuccess) ?>,
            icon: 'success',
            confirmButtonText: 'Siap!'
        });
    <?php endif; ?>

    <?php if ($swError): ?>
        Swal.fire({
            ...swalConfig,
            title: 'Oops!',
            text: <?= json_encode($swError) ?>,
            icon: 'error',
            confirmButtonText: 'Tutup',
            confirmButtonColor: '#e74c3c',
            timer: null
        });
    <?php endif; ?>

    window.addEventListener('resize', function() {
        document.documentElement.style.setProperty(
            '--swal-width', window.innerWidth < 576 ? '90%' : '32em'
        );
    });
</script>
<?php $scripts = ob_get_clean();

require __DIR__ . '/layout.php';
