<?php

require_once __DIR__ . '/../config/database.php';

class NotificationRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getUnreadByAdmin(int $adminId, int $limit = 20): array
    {
        $stmt = $this->db->prepare("
            SELECT id, type, data, created_at
            FROM notifications
            WHERE notifiable_type = 'Admin'
              AND notifiable_id   = ?
              AND read_at IS NULL
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$adminId, $limit]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            if (is_string($row['data'])) {
                $row['data'] = json_decode($row['data'], true) ?? [];
            }
        }

        return $rows;
    }

    public function markAllReadByAdmin(int $adminId): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->prepare("
            UPDATE notifications
            SET read_at = ?, updated_at = ?
            WHERE notifiable_type = 'Admin'
              AND notifiable_id   = ?
              AND read_at IS NULL
        ")->execute([$now, $now, $adminId]);
    }

    public function getByNik(string $nik, int $limit = 50): array
    {
        $stmt = $this->db->prepare("
            SELECT id, type, data, created_at, read_at
            FROM notifications
            WHERE notifiable_type = 'Masyarakat'
              AND notifiable_id   = ?
            ORDER BY created_at DESC
            LIMIT ?
        ");
        $stmt->execute([$nik, $limit]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as &$row) {
            if (is_string($row['data'])) {
                $row['data'] = json_decode($row['data'], true) ?? [];
            }
        }

        return $rows;
    }

    public function markAllReadByNik(string $nik): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->prepare("
            UPDATE notifications SET read_at = ?
            WHERE notifiable_type = 'Masyarakat'
              AND notifiable_id   = ?
              AND read_at IS NULL
        ")->execute([$now, $nik]);
    }

    /**
     * Hitung notifikasi masyarakat yang belum dibaca berdasarkan NIK
     */
    public function countUnreadByNik(string $nik): int
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications
            WHERE notifiable_type = 'Masyarakat'
              AND notifiable_id   = ?
              AND read_at IS NULL
        ");
        $stmt->execute([$nik]);
        return (int)$stmt->fetchColumn();
    }

    public function deleteAllByNik(string $nik): void
    {
        $this->db->prepare("
            DELETE FROM notifications
            WHERE notifiable_type = 'Masyarakat' AND notifiable_id = ?
        ")->execute([$nik]);
    }

    public function deleteByIdAndNik(string $id, string $nik): void
    {
        $this->db->prepare("
            DELETE FROM notifications
            WHERE id = ?
              AND notifiable_type = 'Masyarakat'
              AND notifiable_id   = ?
        ")->execute([$id, $nik]);
    }
}