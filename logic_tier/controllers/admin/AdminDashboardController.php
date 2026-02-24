<?php

require_once __DIR__ . '/../../keamanan/ValidasiLogin.php';
require_once __DIR__ . '/../../services/AdminDashboardService.php';

class AdminDashboardController
{
    private AdminDashboardService $dashboardService;

    public function __construct()
    {
        ValidasiLogin::cekSesi();
        $this->dashboardService = new AdminDashboardService();
    }

    public function index(): void
    {
        $admin          = ValidasiLogin::ambilDataAdmin();
        $summary        = $this->dashboardService->getDashboardSummary();
        $additionalData = $this->dashboardService->getDashboardAdditionalData();

        $data = array_merge(['admin' => $admin], $summary, $additionalData);

        extract($data);
        require_once __DIR__ . '/../../../presentation_tier/admin/dashboard.php';
    }
}