<?php

require_once __DIR__ . '/../../keamanan/ValidasiLogin.php';
require_once __DIR__ . '/../../services/NotificationService.php';

class NotificationController
{
    private NotificationService $service;

    public function __construct()
    {
        // Perbaikan panggilan method: check() -> cekSesi()
        ValidasiLogin::cekSesi();
        $this->service = new NotificationService();
    }

    /**
     * Mengambil daftar notifikasi yang belum dibaca (format JSON)
     */
    public function getUnread(): void
    {
        header('Content-Type: application/json');
        $adminId = $_SESSION['admin_id'] ?? null;

        if (!$adminId) {
            echo json_encode(['count' => 0, 'notifications' => []]);
            return;
        }

        try {
            $notifications = $this->service->getUnreadAdmin((int)$adminId);
            echo json_encode([
                'count'         => count($notifications),
                'notifications' => $notifications,
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['count' => 0, 'notifications' => [], 'error' => $e->getMessage()]);
        }
    }

    /**
     * Menandai semua notifikasi admin sebagai sudah dibaca
     */
    public function markAsRead(): void
    {
        header('Content-Type: application/json');
        $adminId = $_SESSION['admin_id'] ?? null;

        if (!$adminId) {
            echo json_encode(['success' => false]);
            return;
        }

        try {
            // Memanggil layanan untuk memperbarui status di database
            $this->service->markAllReadAdmin((int)$adminId);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}