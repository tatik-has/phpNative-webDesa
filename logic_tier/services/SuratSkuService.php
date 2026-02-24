<?php

/**
 * LOGIC TIER - Service Surat SKU
 */

require_once __DIR__ . '/../../logic_tier/repositories/PermohonanSKURepository.php';
require_once __DIR__ . '/NotificationService.php';
require_once __DIR__ . '/FileUploadService.php';

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