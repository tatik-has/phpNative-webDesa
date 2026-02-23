<?php

require_once __DIR__ . '/../../services/NotificationService.php';

class MasyarakatNotificationController
{
    private NotificationService $service;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->service = new NotificationService();
    }

    public function index(): void
    {
        $nik           = $_SESSION['nik_pemohon'] ?? null;
        $notifications = [];

        if ($nik) {
            try {
                $notifications = $this->service->getByNik($nik);
                $this->service->markAllReadMasyarakat($nik);
            } catch (Exception $e) {
                $notifications = [];
            }
        }

        require_once __DIR__ . '/../../../presentation_tier/masyarakat/notifications/index.php';
    }

    public function deleteAll(): void
    {
        $nik = $_SESSION['nik_pemohon'] ?? null;
        if ($nik) $this->service->deleteAll($nik);
        header('Location: /web-pengajuan/notifications');
        exit;
    }

    public function delete(string $id): void
    {
        $nik = $_SESSION['nik_pemohon'] ?? null;
        if ($nik) $this->service->deleteOne($id, $nik);
        header('Location: /web-pengajuan/notifications');
        exit;
    }
}