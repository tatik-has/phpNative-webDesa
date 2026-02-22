<?php

/**
 * LOGIC TIER - Service Surat Domisili
 * Pengganti App\LogicTier\Services\SuratDomisiliService di Laravel.
 * PERUBAHAN: Auth::id() → session user_id (karena tidak ada login masyarakat)
 */

require_once __DIR__ . '/../../data_tier/repositories/PermohonanDomisiliRepository.php';
require_once __DIR__ . '/NotificationService.php';
require_once __DIR__ . '/FileUploadService.php';

class SuratDomisiliService
{
    private PermohonanDomisiliRepository $repository;
    private NotificationService $notifService;

    public function __construct()
    {
        $this->repository   = new PermohonanDomisiliRepository();
        $this->notifService = new NotificationService();
    }

    /**
     * Simpan permohonan domisili
     * PERUBAHAN: user_id dari session (bukan Auth::id())
     */
    public function storeDomisili(array $data, ?array $ktpFile, ?array $kkFile): int
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $pathKtp = FileUploadService::upload($ktpFile, 'dokumen/ktp');
        $pathKk  = FileUploadService::upload($kkFile, 'dokumen/kk');

        $id = $this->repository->create([
            'user_id'         => $_SESSION['user_id'] ?? null,
            'nik'             => $data['nik'],
            'nama'            => $data['nama'],
            'alamat_domisili' => $data['alamat_domisili'],
            'nomor_telp'      => $data['nomor_telp'],
            'rt_domisili'     => $data['rt_domisili'],
            'rw_domisili'     => $data['rw_domisili'],
            'jenis_kelamin'   => $data['jenis_kelamin'],
            'alamat_ktp'      => $data['alamat_ktp'],
            'path_ktp'        => $pathKtp,
            'path_kk'         => $pathKk,
            'status'          => 'Diproses',
        ]);

        // Notifikasi ke admin
        $this->notifService->notifikasiAdminPermohonanBaru('Keterangan Domisili', $data['nama'], $id);

        return $id;
    }

    public function getHistory(int $userId): array
    {
        return array_map(function ($item) {
            $item['jenis_surat'] = 'Surat Keterangan Domisili';
            $item['type']        = 'domisili';
            return $item;
        }, $this->repository->getByUserId($userId));
    }
}