<?php

class PengajuanSuratController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    // [BARU] Cek login NIK — redirect ke halaman login jika belum login
    private function cekLogin(): void
    {
        if (empty($_SESSION['nik_pemohon'])) {
            $_SESSION['error'] = 'Anda harus login terlebih dahulu untuk mengajukan surat.';
            header('Location: /web-pengajuan/login');
            exit;
        }
    }

    public function showPengajuanForm(): void
    {
        $this->cekLogin(); // [BARU]
        require_once __DIR__ . '/../../../presentation_tier/masyarakat/permohonan/pengajuan.php';
    }
}