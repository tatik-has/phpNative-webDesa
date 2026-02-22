<?php

/**
 * LOGIC TIER - Controller Dashboard Masyarakat
 * Pengganti App\LogicTier\Controllers\Masyarakat\MasyarakatDashboardController di Laravel.
 *
 * PERUBAHAN: Tidak ada middleware auth → akses publik langsung.
 * Auth::id() diganti dengan session opsional (jika masyarakat punya NIK di session).
 */

require_once __DIR__ . '/../../../data_tier/models/Surat.php';

class MasyarakatDashboardController
{
    /**
     * Tampilkan dashboard masyarakat
     * Setara: index() di Laravel
     * PERUBAHAN: Tidak perlu login, tampilkan info umum
     */
    public function index(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $surats = [];

        // Jika ada session user_id (masyarakat yang sudah mengisi form sebelumnya),
        // tampilkan riwayat mereka. Jika tidak, tampilkan dashboard kosong.
        if (!empty($_SESSION['user_id'])) {
            $suratModel = new Surat();
            $surats     = $suratModel->getByUserIdWithUser((int)$_SESSION['user_id']);
        }

        require_once __DIR__ . '/../../../presentation_tier/masyarakat/dashboard.php';
    }
}