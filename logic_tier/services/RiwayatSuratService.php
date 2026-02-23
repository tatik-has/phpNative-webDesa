<?php

/**
 * LOGIC TIER - Service Riwayat Surat
 */

require_once __DIR__ . '/../../data_tier/config/database.php';

class RiwayatSuratService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getRiwayatByUserId(int $userId): array
    {
        $domisili = $this->fetchWithLabel('permohonan_domisili', 'user_id', $userId, 'Surat Keterangan Domisili', 'domisili');
        $ktm      = $this->fetchWithLabel('permohonan_ktm', 'user_id', $userId, 'Surat Keterangan Tidak Mampu (SKTM)', 'ktm');
        $sku      = $this->fetchWithLabel('permohonan_sku', 'user_id', $userId, 'Surat Keterangan Usaha (SKU)', 'sku');

        $all = array_merge($domisili, $ktm, $sku);
        usort($all, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $all;
    }

    public function getRiwayatByNik(string $nik): array
    {
        $domisili = $this->fetchWithLabel('permohonan_domisili', 'nik', $nik, 'Surat Keterangan Domisili', 'domisili');
        $ktm      = $this->fetchWithLabel('permohonan_ktm', 'nik', $nik, 'Surat Keterangan Tidak Mampu (SKTM)', 'ktm');
        $sku      = $this->fetchWithLabel('permohonan_sku', 'nik', $nik, 'Surat Keterangan Usaha (SKU)', 'sku');

        $all = array_merge($domisili, $ktm, $sku);
        usort($all, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $all;
    }

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