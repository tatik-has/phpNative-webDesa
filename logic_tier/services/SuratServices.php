<?php
// =====================================================
// SuratKtmService.php
// =====================================================
require_once __DIR__ . '/../../data_tier/repositories/PermohonanKTMRepository.php';
require_once __DIR__ . '/NotificationService.php';
require_once __DIR__ . '/FileUploadService.php';

class SuratKtmService
{
    private PermohonanKTMRepository $repository;
    private NotificationService $notifService;

    public function __construct()
    {
        $this->repository   = new PermohonanKTMRepository();
        $this->notifService = new NotificationService();
    }

    public function storeKtm(array $validated, array $files): int
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $fileMap = [
            'ktp'                   => 'path_ktp',
            'kk'                    => 'path_kk',
            'surat_pengantar_rt_rw' => 'path_surat_pengantar_rt_rw',
            'foto_rumah'            => 'path_foto_rumah',
        ];

        $paths = [];
        foreach ($fileMap as $inputKey => $dbKey) {
            if (!empty($files[$inputKey])) {
                $paths[$dbKey] = FileUploadService::upload($files[$inputKey], 'dokumen_sktm');
            }
        }

        $id = $this->repository->create(array_merge([
            'user_id' => $_SESSION['user_id'] ?? null,
            'status'  => 'Diproses',
        ], $validated, $paths));

        $this->notifService->notifikasiAdminPermohonanBaru('SKTM', $validated['nama'], $id);

        return $id;
    }

    public function getHistory(int $userId): array
    {
        return array_map(function ($item) {
            $item['jenis_surat'] = 'Surat Keterangan Tidak Mampu (SKTM)';
            $item['type']        = 'ktm';
            return $item;
        }, $this->repository->getByUserId($userId));
    }
}

// =====================================================
// SuratSkuService.php
// =====================================================
require_once __DIR__ . '/../../data_tier/repositories/PermohonanSKURepository.php';

class SuratSkuService
{
    private PermohonanSKURepository $repository;
    private NotificationService $notifService;

    public function __construct()
    {
        $this->repository   = new PermohonanSKURepository();
        $this->notifService = new NotificationService();
    }

    public function storeSku(array $validated, array $files): int
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $fileMap = [
            'ktp'             => 'path_ktp',
            'kk'              => 'path_kk',
            'surat_pengantar' => 'path_surat_pengantar',
            'foto_usaha'      => 'path_foto_usaha',
        ];

        $paths = [];
        foreach ($fileMap as $inputKey => $dbKey) {
            if (!empty($files[$inputKey])) {
                $paths[$dbKey] = FileUploadService::upload($files[$inputKey], 'dokumen_sku');
            }
        }

        $id = $this->repository->create(array_merge([
            'user_id' => $_SESSION['user_id'] ?? null,
            'status'  => 'Diproses',
        ], $validated, $paths));

        $this->notifService->notifikasiAdminPermohonanBaru('SKU', $validated['nama'], $id);

        return $id;
    }

    public function getHistory(int $userId): array
    {
        return array_map(function ($item) {
            $item['jenis_surat'] = 'Surat Keterangan Usaha (SKU)';
            $item['type']        = 'sku';
            return $item;
        }, $this->repository->getByUserId($userId));
    }
}

// =====================================================
// RiwayatSuratService.php
// =====================================================
require_once __DIR__ . '/../../data_tier/config/database.php';

class RiwayatSuratService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Ambil riwayat berdasarkan user_id (fallback lama)
     */
    public function getRiwayatByUserId(int $userId): array
    {
        $domisili = $this->fetchWithLabel('permohonan_domisili', 'user_id', $userId, 'Surat Keterangan Domisili', 'domisili');
        $ktm      = $this->fetchWithLabel('permohonan_ktm',      'user_id', $userId, 'Surat Keterangan Tidak Mampu (SKTM)', 'ktm');
        $sku      = $this->fetchWithLabel('permohonan_sku',       'user_id', $userId, 'Surat Keterangan Usaha (SKU)', 'sku');

        $all = array_merge($domisili, $ktm, $sku);
        usort($all, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $all;
    }

    /**
     * FIX: Ambil riwayat berdasarkan NIK (untuk masyarakat tanpa login)
     * Dipanggil RiwayatSuratController::index()
     */
    public function getRiwayatByNik(string $nik): array
    {
        $domisili = $this->fetchWithLabel('permohonan_domisili', 'nik', $nik, 'Surat Keterangan Domisili', 'domisili');
        $ktm      = $this->fetchWithLabel('permohonan_ktm',      'nik', $nik, 'Surat Keterangan Tidak Mampu (SKTM)', 'ktm');
        $sku      = $this->fetchWithLabel('permohonan_sku',       'nik', $nik, 'Surat Keterangan Usaha (SKU)', 'sku');

        $all = array_merge($domisili, $ktm, $sku);
        usort($all, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $all;
    }

    /**
     * FIX: fetchWithLabel sekarang support kolom identifier berbeda (user_id atau nik)
     */
    private function fetchWithLabel(string $table, string $column, mixed $value, string $label, string $type): array
    {
        $stmt = $this->db->prepare("
            SELECT *, '{$label}' AS jenis_surat, '{$type}' AS type
            FROM {$table}
            WHERE {$column} = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$value]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}