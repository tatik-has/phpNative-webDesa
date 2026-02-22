<?php

require_once __DIR__ . '/../../../data_tier/config/database.php';

class MasyarakatNotificationController
{
    private PDO $db;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->db = Database::getInstance();
    }

    public function index(): void
    {
        // UBAH: pakai NIK dari session, bukan user_id
        $nik = $_SESSION['nik_pemohon'] ?? null;
        $notifications = [];

        if ($nik) {
            try {
                $stmt = $this->db->prepare("
                SELECT id, type, data, created_at, read_at
                FROM notifications
                WHERE notifiable_type = 'Masyarakat'
                  AND notifiable_id = ?
                ORDER BY created_at DESC
                LIMIT 50
            ");
                $stmt->execute([$nik]);
                $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($notifications as &$notif) {
                    if (is_string($notif['data'])) {
                        $notif['data'] = json_decode($notif['data'], true) ?? [];
                    }
                }

                // Tandai sudah dibaca
                $now = date('Y-m-d H:i:s');
                $this->db->prepare("
                UPDATE notifications SET read_at = ?
                WHERE notifiable_type = 'Masyarakat'
                  AND notifiable_id = ?
                  AND read_at IS NULL
            ")->execute([$now, $nik]);
            } catch (Exception $e) {
                $notifications = [];
            }
        }

        require_once __DIR__ . '/../../../presentation_tier/masyarakat/notifications/index.php';
    }

    public function deleteAll(): void
    {
        $nik = $_SESSION['nik_pemohon'] ?? null;
        if ($nik) {
            $this->db->prepare("
            DELETE FROM notifications
            WHERE notifiable_type = 'Masyarakat' AND notifiable_id = ?
        ")->execute([$nik]);
        }
        header('Location: /web-pengajuan/notifications');
        exit;
    }

    public function delete(string $id): void
    {
        $nik = $_SESSION['nik_pemohon'] ?? null;
        if ($nik) {
            $this->db->prepare("
            DELETE FROM notifications
            WHERE id = ?
              AND notifiable_type = 'Masyarakat'
              AND notifiable_id = ?
        ")->execute([$id, $nik]);
        }
        header('Location: /web-pengajuan/notifications');
        exit;
    }
}
