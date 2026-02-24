<?php

/**
 * LOGIC TIER - Service Surat KTM (SKTM)
 */

require_once __DIR__ . '/../../logic_tier/repositories/PermohonanKTMRepository.php';
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