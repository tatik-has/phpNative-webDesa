<?php

/**
 * LOGIC TIER - Service Admin Dashboard
 * Pengganti App\LogicTier\Services\AdminDashboardService di Laravel.
 */

if (!class_exists('AdminDashboardService')) {

    require_once __DIR__ . '/../../data_tier/config/database.php';

    class AdminDashboardService
    {
        private PDO $db;

        public function __construct()
        {
            $this->db = Database::getInstance();
        }

        /**
         * Ambil ringkasan statistik untuk dashboard
         */
        public function getDashboardSummary(): array
        {
            $totalDiproses = $this->countByStatus('permohonan_domisili', 'Diproses')
                + $this->countByStatus('permohonan_ktm', 'Diproses')
                + $this->countByStatus('permohonan_sku', 'Diproses');

            $totalSelesai  = $this->countByStatus('permohonan_domisili', 'Selesai')
                + $this->countByStatus('permohonan_ktm', 'Selesai')
                + $this->countByStatus('permohonan_sku', 'Selesai');

            $totalDitolak  = $this->countByStatus('permohonan_domisili', 'Ditolak')
                + $this->countByStatus('permohonan_ktm', 'Ditolak')
                + $this->countByStatus('permohonan_sku', 'Ditolak');

            return compact('totalDiproses', 'totalSelesai', 'totalDitolak');
        }

        /**
         * Ambil data tambahan dashboard
         */
        public function getDashboardAdditionalData(): array
        {
            $today = date('Y-m-d');

            $domisili = $this->getRecentWithLabel('permohonan_domisili', 'Surat Domisili', 5);
            $ktm      = $this->getRecentWithLabel('permohonan_ktm', 'Surat Keterangan Tidak Mampu', 5);
            $sku      = $this->getRecentWithLabel('permohonan_sku', 'Surat Keterangan Usaha', 5);

            $recentPermohonan = array_merge($domisili, $ktm, $sku);
            usort($recentPermohonan, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
            $recentPermohonan = array_slice($recentPermohonan, 0, 5);

            $totalUsers  = $this->countTable('users');
            $totalAdmins = $this->countTable('admins');

            $todayPermohonan = $this->countByDate('permohonan_domisili', $today)
                + $this->countByDate('permohonan_ktm', $today)
                + $this->countByDate('permohonan_sku', $today);

            $totalArsip = $this->countArchived('permohonan_domisili')
                + $this->countArchived('permohonan_ktm')
                + $this->countArchived('permohonan_sku');

            return compact('recentPermohonan', 'totalUsers', 'totalAdmins', 'todayPermohonan', 'totalArsip');
        }

        // =========================================================
        //  HELPER QUERY
        // =========================================================

        private function countByStatus(string $table, string $status): int
        {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$table} WHERE status = ?");
            $stmt->execute([$status]);
            return (int)$stmt->fetchColumn();
        }

        private function countTable(string $table): int
        {
            return (int)$this->db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
        }

        private function countByDate(string $table, string $date): int
        {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$table} WHERE DATE(created_at) = ?");
            $stmt->execute([$date]);
            return (int)$stmt->fetchColumn();
        }

        private function countArchived(string $table): int
        {
            return (int)$this->db->query(
                "SELECT COUNT(*) FROM {$table} WHERE archived_at IS NOT NULL"
            )->fetchColumn();
        }

        private function getRecentWithLabel(string $table, string $label, int $limit): array
        {
            $stmt = $this->db->prepare("
            SELECT p.*, u.name AS user_name, '{$label}' AS jenis_surat
            FROM {$table} p
            LEFT JOIN users u ON u.id = p.user_id
            ORDER BY p.created_at DESC
            LIMIT {$limit}
        ");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
