<?php

/**
 * SISTEM LOGIKA - Controller Profil Admin
 */

require_once __DIR__ . '/../../keamanan/ValidasiLogin.php';
require_once __DIR__ . '/../../../data_tier/models/Admin.php';

class AdminProfileController
{
    public function __construct()
    {
        ValidasiLogin::cekSesi();
    }

    public function show(): void
    {
        $adminSession = ValidasiLogin::ambilDataAdmin();

        // Ambil data lengkap dari database
        $adminModel = new Admin();
        $admin      = $adminModel->find((int)$adminSession['id']);

        // Sembunyikan password agar aman
        if ($admin) {
            $admin = $adminModel->hideFields($admin);
        }

        require_once __DIR__ . '/../../../presentation_tier/admin/profile/show.php';
    }
}