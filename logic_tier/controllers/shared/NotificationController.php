<?php

/**
 * LOGIC TIER - Controller Notifikasi (Shared)
 * FILE BARU — direferensikan di routes.php tapi belum ada.
 *
 * Melayani 2 endpoint AJAX yang dipanggil oleh admin layout:
 *   GET  /admin/notifications/unread       → getUnread()
 *   POST /admin/notifications/mark-as-read → markAsRead()
 *
 * Response format: JSON
 */

require_once __DIR__ . '/../../middleware/AdminAuthMiddleware.php';
require_once __DIR__ . '/../../../data_tier/config/database.php';

class NotificationController
{
    private PDO $db;

    public function __construct()
    {
        AdminAuthMiddleware::check();
        $this->db = Database::getInstance();
    }

    /**
     * Ambil notifikasi yang belum dibaca untuk admin yang sedang login
     * Dipanggil via AJAX polling tiap 30 detik dari layout admin
     *
     * Response JSON:
     * {
     *   "count": 3,
     *   "notifications": [
     *     { "id": "uuid", "data": {...}, "created_at": "2026-..." },
     *     ...
     *   ]
     * }
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
            $stmt = $this->db->prepare("
                SELECT id, type, data, created_at
                FROM notifications
                WHERE notifiable_type = 'Admin'
                  AND notifiable_id   = ?
                  AND read_at IS NULL
                ORDER BY created_at DESC
                LIMIT 20
            ");
            $stmt->execute([$adminId]);
            $notifications = $stmt->fetchAll();

            // Decode JSON data agar langsung bisa dipakai di JS
            foreach ($notifications as &$notif) {
                if (is_string($notif['data'])) {
                    $notif['data'] = json_decode($notif['data'], true) ?? [];
                }
            }

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
     * Tandai semua notifikasi admin sebagai sudah dibaca
     * Dipanggil via AJAX saat admin membuka dropdown notifikasi
     *
     * Response JSON: { "success": true }
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
            $stmt = $this->db->prepare("
                UPDATE notifications
                SET read_at    = ?,
                    updated_at = ?
                WHERE notifiable_type = 'Admin'
                  AND notifiable_id   = ?
                  AND read_at IS NULL
            ");
            $now = date('Y-m-d H:i:s');
            $stmt->execute([$now, $now, $adminId]);

            echo json_encode(['success' => true]);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
