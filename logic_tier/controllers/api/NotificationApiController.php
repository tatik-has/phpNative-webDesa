<?php

require_once ROOT_PATH . '/logic_tier/keamanan/ApiAuth.php';
require_once ROOT_PATH . '/logic_tier/services/ApiResponse.php';

class NotificationApiController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** GET /api/v1/notifications — list notifikasi (admin) */
    public function index(): void
    {
        ApiAuth::requireAdmin();

        $onlyUnread = isset($_GET['unread']) && $_GET['unread'] === '1';
        $limit      = min((int)($_GET['limit'] ?? 20), 100);

        $sql    = "SELECT * FROM notifications";
        $params = [];

        if ($onlyUnread) {
            $sql .= " WHERE is_read = 0";
        }
        $sql .= " ORDER BY created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $unreadCount = (int) $this->db
            ->query("SELECT COUNT(*) FROM notifications WHERE is_read = 0")
            ->fetchColumn();

        ApiResponse::success([
            'notifications' => $notifications,
            'unread_count'  => $unreadCount,
        ], 'Notifikasi berhasil diambil.');
    }

    /** POST /api/v1/notifications/mark-as-read — tandai dibaca (admin) */
    public function markAsRead(): void
    {
        ApiAuth::requireAdmin();

        $body = json_decode(file_get_contents('php://input'), true) ?? [];
        $ids  = $body['ids'] ?? null;

        if ($ids === null) {
            $this->db->exec("UPDATE notifications SET is_read = 1");
            ApiResponse::success(null, 'Semua notifikasi telah ditandai sebagai dibaca.');
        }

        if (!is_array($ids) || empty($ids)) {
            ApiResponse::error(422, 'Field "ids" harus berupa array ID notifikasi.');
        }

        $ids          = array_map('intval', $ids);
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt         = $this->db->prepare("UPDATE notifications SET is_read = 1 WHERE id IN ({$placeholders})");
        $stmt->execute($ids);

        ApiResponse::success(
            ['updated_count' => $stmt->rowCount()],
            $stmt->rowCount() . ' notifikasi berhasil ditandai sebagai dibaca.'
        );
    }
}