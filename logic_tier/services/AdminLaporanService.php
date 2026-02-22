<?php

/**
 * LOGIC TIER - Service Admin Laporan
 * Pengganti App\LogicTier\Services\AdminLaporanService di Laravel.
 */

require_once __DIR__ . '/../../data_tier/config/database.php';

class AdminLaporanService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Ambil data laporan dengan filter tanggal dan status
     * Setara: getLaporanData() di Laravel
     */
    public function getLaporanData(string $tanggalMulai, string $tanggalAkhir, string $statusFilter): array
    {
        $start = $tanggalMulai . ' 00:00:00';
        $end   = $tanggalAkhir . ' 23:59:59';

        $domisili = $this->queryLaporan('permohonan_domisili', 'Keterangan Domisili', $start, $end, $statusFilter);
        $ktm      = $this->queryLaporan('permohonan_ktm', 'Keterangan Tidak Mampu', $start, $end, $statusFilter);
        $sku      = $this->queryLaporan('permohonan_sku', 'Keterangan Usaha', $start, $end, $statusFilter);

        $all = array_merge($domisili, $ktm, $sku);
        usort($all, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return $all;
    }

    /**
     * Archive data berdasarkan type dan id
     * Setara: archiveData() di Laravel
     */
    public function archiveData(string $type, int $id): bool
    {
        $table = match ($type) {
            'domisili' => 'permohonan_domisili',
            'ktm'      => 'permohonan_ktm',
            'sku'      => 'permohonan_sku',
            default    => null,
        };

        if (!$table) return false;

        $stmt = $this->db->prepare("UPDATE {$table} SET archived_at = ? WHERE id = ?");
        return $stmt->execute([date('Y-m-d H:i:s'), $id]);
    }

    /**
     * Ambil semua data yang sudah diarsipkan
     * Setara: getArchivedPermohonan() di Laravel
     */
    public function getArchivedPermohonan(): array
    {
        $domisili = $this->queryArsip('permohonan_domisili');
        $ktm      = $this->queryArsip('permohonan_ktm');
        $sku      = $this->queryArsip('permohonan_sku');

        return compact('domisili', 'ktm', 'sku');
    }

    // =========================================================
    //  HELPER
    // =========================================================

    private function queryLaporan(string $table, string $label, string $start, string $end, string $statusFilter): array
    {
        $sql    = "SELECT p.*, u.name AS user_name, '{$label}' AS jenis_surat_label
                   FROM {$table} p
                   LEFT JOIN users u ON u.id = p.user_id
                   WHERE p.created_at BETWEEN ? AND ?";
        $params = [$start, $end];

        if ($statusFilter !== 'semua') {
            $sql     .= " AND p.status = ?";
            $params[] = ucfirst(strtolower($statusFilter));
        }

        $sql .= " ORDER BY p.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    private function queryArsip(string $table): array
    {
        $stmt = $this->db->query("
            SELECT p.*, u.name AS user_name
            FROM {$table} p
            LEFT JOIN users u ON u.id = p.user_id
            WHERE p.archived_at IS NOT NULL
            ORDER BY p.archived_at DESC
        ");
        return $stmt->fetchAll();
    }
}