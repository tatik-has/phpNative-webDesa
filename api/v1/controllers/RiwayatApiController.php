<?php

class RiwayatApiController
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /** GET /api/v1/riwayat?nik={nik} — riwayat permohonan (publik) */
    public function index(): void
    {
        ApiAuth::rateLimit(30, 60);

        $nik = trim($_GET['nik'] ?? '');

        if (empty($nik)) {
            ApiResponse::error(422, 'Parameter NIK wajib diisi.', [
                'nik' => 'Query parameter ?nik={nik} diperlukan.',
            ]);
        }

        if (!preg_match('/^\d{16}$/', $nik)) {
            ApiResponse::error(422, 'Format NIK tidak valid.', [
                'nik' => 'NIK harus terdiri dari 16 digit angka.',
            ]);
        }

        $riwayat = $this->getRiwayatByNik($nik);
        $riwayat = array_map([$this, 'sanitizeItem'], $riwayat);

        ApiResponse::list(
            $riwayat,
            count($riwayat),
            count($riwayat) > 0 ? 'Riwayat permohonan ditemukan.' : 'Belum ada permohonan dengan NIK tersebut.'
        );
    }

    private function getRiwayatByNik(string $nik): array
    {
        $tables = [
            'permohonan_domisili' => ['label' => 'Surat Keterangan Domisili', 'type' => 'domisili'],
            'permohonan_ktm'      => ['label' => 'Surat Keterangan Tidak Mampu (SKTM)', 'type' => 'ktm'],
            'permohonan_sku'      => ['label' => 'Surat Keterangan Usaha (SKU)', 'type' => 'sku'],
        ];

        $all = [];
        foreach ($tables as $table => $meta) {
            $stmt = $this->db->prepare("
                SELECT id, nama, nik, status, keterangan_penolakan, created_at, updated_at
                FROM {$table} WHERE nik = ? ORDER BY created_at DESC
            ");
            $stmt->execute([$nik]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows as $row) {
                $row['jenis_surat'] = $meta['label'];
                $row['type']        = $meta['type'];
                $all[]              = $row;
            }
        }

        usort($all, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
        return $all;
    }

    private function sanitizeItem(array $item): array
    {
        $sensitiveFields = [
            'path_ktp', 'path_kk', 'path_surat_pengantar',
            'path_surat_pengantar_rt_rw', 'path_foto_rumah',
            'path_foto_usaha', 'user_id', 'archived_at',
        ];
        foreach ($sensitiveFields as $field) {
            unset($item[$field]);
        }
        return $item;
    }
}