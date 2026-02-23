<?php

require_once __DIR__ . '/../../middleware/AdminAuthMiddleware.php';
require_once __DIR__ . '/../../services/NotificationService.php';

class NotificationController
{
    private NotificationService $service;

    public function __construct()
    {
        AdminAuthMiddleware::check();
        $this->service = new NotificationService();
    }

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

    public function markAsRead(): void
    {
        header('Content-Type: application/json');
        $adminId = $_SESSION['admin_id'] ?? null;

        if (!$adminId) {
            echo json_encode(['success' => false]);
            return;
        }

        try {
            $this->service->markAllReadAdmin((int)$adminId);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}