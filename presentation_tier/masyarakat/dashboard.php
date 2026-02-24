<?php

$pageTitle = 'Beranda - Desa Pakning Asal';
$extraCss  = ['/web-pengajuan/presentation_tier/css/masyarakat/dashboard.css'];
$extraJs   = ['https://cdn.jsdelivr.net/npm/sweetalert2@11'];

if (session_status() === PHP_SESSION_NONE) session_start();
$swSuccess = $_SESSION['success'] ?? null;
$swError   = $_SESSION['error']   ?? null;
unset($_SESSION['success'], $_SESSION['error']);

ob_start(); ?>

<!-- HERO -->
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

<!-- CARA MENGAJUKAN SURAT -->
<section class="panduan-section">
    <div class="panduan-inner">

        <div class="panduan-head">
            <span class="panduan-badge">Panduan</span>
            <h2 class="panduan-title">Cara Mengajukan Surat</h2>
            <p class="panduan-sub">Proses pengajuan surat keterangan kini lebih mudah dan cepat secara online</p>
        </div>

        <div class="panduan-steps">

            <!-- Langkah 1 -->
            <div class="step-item">
                <div class="step-left">
                    <div class="step-circle step-blue">
                        <i class="fas fa-list-ul"></i>
                    </div>
                    <div class="step-line"></div>
                </div>
                <div class="step-body">
                    <span class="step-num">Langkah 1</span>
                    <h3 class="step-title">Pilih Jenis Surat</h3>
                    <p class="step-desc">Klik tombol <strong>"Pengajuan Surat"</strong> lalu pilih salah satu jenis surat yang Anda butuhkan.</p>
                    <div class="step-tags">
                        <span class="tag"> Keterangan Tidak Mampu</span>
                        <span class="tag"> Keterangan Domisili</span>
                        <span class="tag"> Keterangan Usaha</span>
                    </div>
                </div>
            </div>

            <!-- Langkah 2 -->
            <div class="step-item">
                <div class="step-left">
                    <div class="step-circle step-green">
                        <i class="fas fa-pen-to-square"></i>
                    </div>
                    <div class="step-line"></div>
                </div>
                <div class="step-body">
                    <span class="step-num">Langkah 2</span>
                    <h3 class="step-title">Isi Formulir & Upload Dokumen</h3>
                    <p class="step-desc">Lengkapi data diri dengan benar dan upload dokumen pendukung yang diminta.</p>
                    <div class="step-tags">
                        <span class="tag"> KTP</span>
                        <span class="tag"> Kartu Keluarga</span>
                        <span class="tag"> Surat Pengantar RT/RW</span>
                    </div>
                </div>
            </div>

            <!-- Langkah 3 -->
            <div class="step-item">
                <div class="step-left">
                    <div class="step-circle step-yellow">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div class="step-line"></div>
                </div>
                <div class="step-body">
                    <span class="step-num">Langkah 3</span>
                    <h3 class="step-title">Pantau Status Permohonan</h3>
                    <p class="step-desc">Admin akan memverifikasi dalam <strong>1–2 hari kerja</strong>. Pantau perkembangan melalui:</p>
                    <div class="step-tags">
                        <span class="tag"><a href="/web-pengajuan/notifications" class="tag-link"> Notifikasi di menu atas</a></span>
                        <span class="tag"><a href="/web-pengajuan/riwayat" class="tag-link"> Halaman Riwayat</a></span>
                    </div>
                </div>
            </div>

            <!-- Langkah 4 -->
            <div class="step-item step-last">
                <div class="step-left">
                    <div class="step-circle step-purple">
                        <i class="fas fa-download"></i>
                    </div>
                </div>
                <div class="step-body">
                    <span class="step-num">Langkah 4</span>
                    <h3 class="step-title">Unduh Surat Anda</h3>
                    <p class="step-desc">
                        Jika status sudah <span class="badge-selesai">Selesai</span>,
                        buka halaman <a href="/web-pengajuan/riwayat" class="inline-link">Riwayat</a>
                        lalu klik tombol Unduh untuk menyimpan surat Anda.
                    </p>
                    <div class="preview-btn-unduh">
                        <i class="fas fa-download"></i> Unduh Surat
                    </div>
                </div>
            </div>

        </div>

        <!-- CTA -->
        <div class="panduan-cta">
            <a href="/web-pengajuan/pengajuan" class="panduan-cta-btn">
                <i class="fas fa-rocket"></i>
                Mulai Pengajuan Sekarang
            </a>
            <p class="panduan-cta-note">
                Ada pertanyaan? Baca <a href="/web-pengajuan/faq" class="inline-link">FAQ</a> kami
            </p>
        </div>

    </div>
</section>

<?php $content = ob_get_clean();

ob_start(); ?>
<script>
    const swalConfig = {
        confirmButtonColor: '#2c3e50',
        timer: 5000,
        timerProgressBar: true,
        showClass: { popup: 'animate__animated animate__fadeInDown animate__faster' },
        hideClass: { popup: 'animate__animated animate__fadeOutUp animate__faster' },
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