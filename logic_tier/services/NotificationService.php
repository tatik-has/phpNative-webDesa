<?php

/**
 * LOGIC TIER - Notification Service
 * PERBAIKAN: kirimNotifikasiUser() kini support NIK sebagai identifier
 * karena masyarakat tidak login (tidak punya user_id).
 * Notifikasi admin TIDAK DIUBAH.
 *
 * UPDATE: tambah method baca/hapus via NotificationRepository
 * agar controller tidak akses PDO langsung.
 */

require_once __DIR__ . '/../../data_tier/config/database.php';
require_once __DIR__ . '/../../data_tier/repositories/NotificationRepository.php';

class NotificationService
{
    private PDO $db;
    private NotificationRepository $repo;

    public function __construct()
    {
        $this->db   = Database::getInstance();
        $this->repo = new NotificationRepository();
    }

    /**
     * Kirim notifikasi ke semua admin saat ada permohonan baru masuk
     * TIDAK DIUBAH - sudah berjalan dengan baik
     */
    public function notifikasiAdminPermohonanBaru(string $jenisSurat, string $namaPemohon, int $permohonanId): void
    {
        try {
            $admins = $this->db->query("SELECT id FROM admins")->fetchAll(PDO::FETCH_ASSOC);
            if (empty($admins)) return;

            $pesan = "Pengajuan {$jenisSurat} baru dari {$namaPemohon} telah masuk.";

            foreach ($admins as $admin) {
                $this->simpanNotifikasi(
                    (int)$admin['id'],
                    'Admin',
                    json_encode([
                        'pesan'         => $pesan,
                        'jenis_surat'   => $jenisSurat,
                        'nama_pemohon'  => $namaPemohon,
                        'permohonan_id' => $permohonanId,
                    ])
                );
            }
        } catch (Exception $e) {
            error_log('[NotificationService] notifikasiAdminPermohonanBaru error: ' . $e->getMessage());
        }
    }

    /**
     * Kirim notifikasi ke masyarakat saat status permohonan berubah.
     *
     * PERBAIKAN: Karena masyarakat tidak login, gunakan NIK sebagai identifier.
     * Parameter $nik diambil dari data permohonan di AdminSuratService.
     */
    public function kirimNotifikasiUser(
        int $userId,
        string $type,
        int $permohonanId,
        string $status,
        ?string $keteranganPenolakan = null,
        ?string $pathSurat = null,
        ?string $nik = null
    ): void {
        try {
            $jenisSurat = match ($type) {
                'domisili' => 'Keterangan Domisili',
                'ktm'      => 'Keterangan Tidak Mampu (SKTM)',
                'sku'      => 'Keterangan Usaha (SKU)',
                default    => 'Surat',
            };

            $pesan = match (strtolower($status)) {
                'selesai'  => "Selamat! Surat {$jenisSurat} Anda telah selesai dan dapat diambil di kantor desa.",
                'diterima' => "Permohonan {$jenisSurat} Anda telah diterima dan sedang diproses oleh admin.",
                'ditolak'  => "Permohonan {$jenisSurat} Anda ditolak. Alasan: " . ($keteranganPenolakan ?? 'Tidak ada detail.'),
                'diproses' => "Permohonan {$jenisSurat} Anda sedang diproses oleh admin.",
                default    => "Status permohonan {$jenisSurat} Anda telah diperbarui menjadi {$status}.",
            };

            $data = json_encode([
                'pesan'         => $pesan,
                'jenis_surat'   => $jenisSurat,
                'status'        => $status,
                'permohonan_id' => $permohonanId,
                'nik'           => $nik,
                'file_path'     => $pathSurat,
            ]);

            if ($nik) {
                // Simpan dengan NIK sebagai identifier (masyarakat tanpa login)
                $this->simpanNotifikasiMasyarakat($nik, $data);
            } elseif ($userId) {
                // Fallback jika ada user_id
                $this->simpanNotifikasi($userId, 'User', $data);
            }

        } catch (Exception $e) {
            error_log('[NotificationService] kirimNotifikasiUser error: ' . $e->getMessage());
        }
    }

    /**
     * Simpan notifikasi masyarakat berdasarkan NIK
     */
    private function simpanNotifikasiMasyarakat(string $nik, string $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO notifications
                (id, type, notifiable_type, notifiable_id, data, read_at, created_at, updated_at)
            VALUES
                (?, 'App\\\\Notifications\\\\GeneralNotification', 'Masyarakat', ?, ?, NULL, ?, ?)
        ");
        $now = date('Y-m-d H:i:s');
        $stmt->execute([$this->generateUuid(), $nik, $data, $now, $now]);
    }

    /**
     * Simpan notifikasi untuk Admin atau User - TIDAK DIUBAH
     */
    private function simpanNotifikasi(int $notifiableId, string $notifiableType, string $data): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO notifications
                (id, type, notifiable_type, notifiable_id, data, read_at, created_at, updated_at)
            VALUES
                (?, 'App\\\\Notifications\\\\GeneralNotification', ?, ?, ?, NULL, ?, ?)
        ");
        $now = date('Y-m-d H:i:s');
        $stmt->execute([$this->generateUuid(), $notifiableType, $notifiableId, $data, $now, $now]);
    }

    /**
     * Generate UUID v4 - TIDAK DIUBAH
     */
    private function generateUuid(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    // =========================================================
    // METHOD BARU — delegasi ke NotificationRepository
    // =========================================================

    /**
     * Ambil notifikasi admin yang belum dibaca
     * Dipakai oleh NotificationController (admin)
     */
    public function getUnreadAdmin(int $adminId): array
    {
        return $this->repo->getUnreadByAdmin($adminId);
    }

    /**
     * Tandai semua notifikasi admin sebagai sudah dibaca
     * Dipakai oleh NotificationController (admin)
     */
    public function markAllReadAdmin(int $adminId): void
    {
        $this->repo->markAllReadByAdmin($adminId);
    }

    /**
     * Ambil semua notifikasi masyarakat berdasarkan NIK
     * Dipakai oleh MasyarakatNotificationController
     */
    public function getByNik(string $nik): array
    {
        return $this->repo->getByNik($nik);
    }

    /**
     * Tandai semua notifikasi masyarakat sebagai sudah dibaca
     * Dipakai oleh MasyarakatNotificationController
     */
    public function markAllReadMasyarakat(string $nik): void
    {
        $this->repo->markAllReadByNik($nik);
    }

    /**
     * Hapus semua notifikasi masyarakat berdasarkan NIK
     * Dipakai oleh MasyarakatNotificationController
     */
    public function deleteAll(string $nik): void
    {
        $this->repo->deleteAllByNik($nik);
    }

    /**
     * Hapus satu notifikasi masyarakat berdasarkan ID dan NIK
     * Dipakai oleh MasyarakatNotificationController
     */
    public function deleteOne(string $id, string $nik): void
    {
        $this->repo->deleteByIdAndNik($id, $nik);
    }
}