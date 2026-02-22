<?php

/**
 * LOGIC TIER - Controller Admin Profile
 * Pengganti App\LogicTier\Controllers\Admin\AdminProfileController di Laravel.
 */

require_once __DIR__ . '/../../middleware/AdminAuthMiddleware.php';
require_once __DIR__ . '/../../../data_tier/models/Admin.php';

class AdminProfileController
{
    public function __construct()
    {
        AdminAuthMiddleware::check();
    }

    /**
     * Tampilkan profil admin (read-only)
     * Setara: show() di Laravel
     */
    public function show(): void
    {
        $adminSession = AdminAuthMiddleware::getAdmin();

        // Ambil data lengkap dari database
        $adminModel = new Admin();
        $admin      = $adminModel->find((int)$adminSession['id']);

        // Sembunyikan password
        if ($admin) {
            $admin = $adminModel->hideFields($admin);
        }

        require_once __DIR__ . '/../../../presentation_tier/admin/profile/show.php';
    }
}