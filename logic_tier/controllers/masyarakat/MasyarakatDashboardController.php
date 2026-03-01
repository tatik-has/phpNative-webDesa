<?php

/**
 * LOGIC TIER - Controller Dashboard Masyarakat
 * DIPERBARUI: Cek sesi NIK untuk warga yang sudah login.
 */

require_once __DIR__ . '/../../../data_tier/models/Surat.php';

class MasyarakatDashboardController
{
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $surats      = [];
        $nikPemohon  = $_SESSION['nik_pemohon'] ?? null;
        $namaPemohon = $_SESSION['nama_pemohon'] ?? null;

        if (!empty($_SESSION['user_id'])) {
            $suratModel = new Surat();
            $surats     = $suratModel->getByUserIdWithUser((int)$_SESSION['user_id']);
        }

        // Inject ke view agar dashboard bisa tampilkan nama + tombol logout
        extract(compact('surats', 'nikPemohon', 'namaPemohon'));
        require_once __DIR__ . '/../../../presentation_tier/masyarakat/dashboard.php';
    }
}