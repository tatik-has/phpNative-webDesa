<?php

/**
 * LOGIC TIER - Controller Pengajuan Surat (Pilih Jenis)
 * FILE BARU — direferensikan di routes.php tapi belum ada.
 *
 * Fungsi: menampilkan halaman pilih jenis surat (3 kartu)
 * Route : GET /pengajuan
 * View  : presentation_tier/masyarakat/permohonan/pengajuan.php
 */

class PengajuanSuratController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    /**
     * Tampilkan halaman pilih jenis surat
     * Setara: showPengajuanForm() di Laravel
     */
    public function showPengajuanForm(): void
    {
        require_once __DIR__ . '/../../../presentation_tier/masyarakat/permohonan/pengajuan.php';
    }
}
