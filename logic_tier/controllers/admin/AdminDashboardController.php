<?php

/**
 * LOGIC TIER - Controller Admin Dashboard
 * Pengganti App\LogicTier\Controllers\Admin\AdminDashboardController di Laravel.
 */

require_once __DIR__ . '/../../middleware/AdminAuthMiddleware.php';
require_once __DIR__ . '/../../services/AdminDashboardService.php';

class AdminDashboardController
{
    private AdminDashboardService $dashboardService;

    public function __construct()
    {
        AdminAuthMiddleware::check(); // Wajib login
        $this->dashboardService = new AdminDashboardService();
    }

    /**
     * Tampilkan dashboard admin dengan summary data
     * Setara: index() di Laravel
     */
    public function index(): void
    {
        $admin          = AdminAuthMiddleware::getAdmin();
        $summary        = $this->dashboardService->getDashboardSummary();
        $additionalData = $this->dashboardService->getDashboardAdditionalData();

        $data = array_merge(['admin' => $admin], $summary, $additionalData);

        extract($data);
        require_once __DIR__ . '/../../../presentation_tier/admin/dashboard.php';
    }
}