<?php

/**
 * LOGIC TIER - AdminSuratService
 * Mengelola semua operasi permohonan surat oleh admin:
 * grouping, update status, detail, generate surat, arsip, auto-arsip.
 *
 * BUG FIX: File ini sebelumnya berisi class AdminDashboardService (salah copy).
 * Sekarang berisi class AdminSuratService yang benar.
 */

require_once __DIR__ . '/../../data_tier/config/database.php';
require_once __DIR__ . '/../../data_tier/repositories/PermohonanDomisiliRepository.php';
require_once __DIR__ . '/../../data_tier/repositories/PermohonanKTMRepository.php';
require_once __DIR__ . '/../../data_tier/repositories/PermohonanSKURepository.php';
require_once __DIR__ . '/NotificationService.php';
require_once __DIR__ . '/SuratTemplateService.php';
require_once __DIR__ . '/FileUploadService.php';

class AdminSuratService
{
    private PDO $db;
    private PermohonanDomisiliRepository $domisiliRepo;
    private PermohonanKTMRepository      $ktmRepo;
    private PermohonanSKURepository      $skuRepo;
    private NotificationService          $notifService;
    private SuratTemplateService         $templateService;

    public function __construct()
    {
        $this->db              = Database::getInstance();
        $this->domisiliRepo    = new PermohonanDomisiliRepository();
        $this->ktmRepo         = new PermohonanKTMRepository();
        $this->skuRepo         = new PermohonanSKURepository();
        $this->notifService    = new NotificationService();
        $this->templateService = new SuratTemplateService();
    }

    // =========================================================
    //  GROUPING PERMOHONAN (untuk halaman admin/permohonan-surat)
    // =========================================================

    /**
     * Ambil semua permohonan dikelompokkan per jenis & status
     * Dipanggil: AdminController::showPermohonanSurat()
     */
    public function getGroupedPermohonan(): array
    {
        $statuses = ['Diproses', 'Diterima', 'Selesai', 'Ditolak'];

        $domisiliGrouped = [];
        $skuGrouped      = [];
        $ktmGrouped      = [];

        foreach ($statuses as $status) {
            $domisiliGrouped[$status] = $this->castStatusRows($this->domisiliRepo->getByStatus($status));
            $skuGrouped[$status]      = $this->castStatusRows($this->skuRepo->getByStatus($status));
            $ktmGrouped[$status]      = $this->castStatusRows($this->ktmRepo->getByStatus($status));
        }

        return compact('domisiliGrouped', 'skuGrouped', 'ktmGrouped');
    }

    /**
     * Ambil semua permohonan tanpa filter status (untuk semuaPermohonan)
     */
    public function getAllPermohonan(): array
    {
        $domisili = $this->castStatusRows($this->domisiliRepo->getAllWithUser());
        $sku      = $this->castStatusRows($this->skuRepo->getAllWithUser());
        $ktm      = $this->castStatusRows($this->ktmRepo->getAllWithUser());

        // Gabungkan dan group by status
        $all = array_merge(
            array_map(fn($r) => array_merge($r, ['type' => 'domisili']), $domisili),
            array_map(fn($r) => array_merge($r, ['type' => 'sku']),      $sku),
            array_map(fn($r) => array_merge($r, ['type' => 'ktm']),      $ktm)
        );
        usort($all, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        // Group by status (sama dengan getGroupedPermohonan tapi dari semua data)
        $statuses = ['Diproses', 'Diterima', 'Selesai', 'Ditolak'];
        $domisiliGrouped = $skuGrouped = $ktmGrouped = [];
        foreach ($statuses as $s) {
            $domisiliGrouped[$s] = array_values(array_filter($domisili, fn($r) => $r['status'] === $s));
            $skuGrouped[$s]      = array_values(array_filter($sku,      fn($r) => $r['status'] === $s));
            $ktmGrouped[$s]      = array_values(array_filter($ktm,      fn($r) => $r['status'] === $s));
        }

        return compact('domisiliGrouped', 'skuGrouped', 'ktmGrouped');
    }

    // =========================================================
    //  DETAIL PERMOHONAN
    // =========================================================

    /**
     * Ambil detail satu permohonan dengan label jenis & title
     * Dipanggil: AdminController::showTemplateSurat(), showDetailSurat(), printSurat()
     */
    public function getPermohonanDetail(string $type, int $id): ?array
    {
        $repo = $this->getRepo($type);
        if (!$repo) return null;

        $permohonan = $repo->findWithUser($id);
        if (!$permohonan) return null;

        // Pastikan status adalah string (bukan enum object)
        $permohonan = $this->castStatusRows([$permohonan])[0];

        [$jenisSurat, $title] = match ($type) {
            'domisili' => ['Keterangan Domisili',          'Detail Surat Keterangan Domisili'],
            'ktm'      => ['Keterangan Tidak Mampu (SKTM)', 'Detail Surat Keterangan Tidak Mampu'],
            'sku'      => ['Keterangan Usaha (SKU)',        'Detail Surat Keterangan Usaha'],
            default    => ['Surat', 'Detail Surat'],
        };

        return compact('permohonan', 'jenisSurat', 'title');
    }

    // =========================================================
    //  UPDATE STATUS
    // =========================================================

    /**
     * Update status permohonan (Diterima / Ditolak)
     * Dipanggil: AdminController::updateStatusPermohonan()
     */
    public function updateStatus(string $type, int $id, array $data): bool
    {
        $repo = $this->getRepo($type);
        if (!$repo) return false;

        $updateData = ['status' => $data['status']];
        if (!empty($data['keterangan_penolakan'])) {
            $updateData['keterangan_penolakan'] = $data['keterangan_penolakan'];
        }

        $result = $repo->update($id, $updateData);

        if ($result) {
            $permohonan = $repo->findOrFail($id);
            // FIX: passing NIK ke notifikasi
            $this->notifService->kirimNotifikasiUser(
                (int)($permohonan['user_id'] ?? 0),
                $type,
                $id,
                $data['status'],
                $data['keterangan_penolakan'] ?? null,
                null,
                $permohonan['nik'] ?? null  // ← tambah NIK
            );
        }

        return $result;
    }

    public function generateAndSaveSurat(string $type, int $id): bool
    {
        $repo = $this->getRepo($type);
        if (!$repo) return false;

        $permohonan = $repo->findOrFail($id);
        $permohonan = $this->castStatusRows([$permohonan])[0];

        $html = $this->templateService->generateTemplate($type, $permohonan);

        $uploadDir = __DIR__ . '/../../uploads/surat_selesai/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $filename = "surat_{$type}_{$id}_" . time() . ".html";
        file_put_contents($uploadDir . $filename, $html);

        $relativePath = "uploads/surat_selesai/{$filename}";

        $result = $repo->update($id, [
            'status'          => 'Selesai',
            'path_surat_jadi' => $relativePath,
        ]);

        if ($result) {
            // FIX: passing NIK ke notifikasi
            $this->notifService->kirimNotifikasiUser(
                (int)($permohonan['user_id'] ?? 0),
                $type,
                $id,
                'Selesai',
                null,
                $relativePath,
                $permohonan['nik'] ?? null  // ← tambah NIK
            );
        }

        return $result;
    }

    // =========================================================
    //  ARSIP
    // =========================================================

    /**
     * Arsipkan satu permohonan secara manual
     * Dipanggil: AdminController::archivePermohonan()
     */
    public function archivePermohonan(string $type, int $id): bool
    {
        $repo = $this->getRepo($type);
        if (!$repo) return false;
        return $repo->archive($id);
    }

    /**
     * Auto-arsip permohonan Selesai/Ditolak yang lebih dari 15 hari
     * Dipanggil: AdminController::runAutoArchive() & AdminLaporanController::runAutoArchive()
     */
    public function autoArchiveOldPermohonan(): int
    {
        $tables  = ['permohonan_domisili', 'permohonan_ktm', 'permohonan_sku'];
        $count   = 0;
        $cutoff  = date('Y-m-d H:i:s', strtotime('-15 days'));

        foreach ($tables as $table) {
            $stmt = $this->db->prepare("
                UPDATE {$table}
                SET archived_at = NOW()
                WHERE status IN ('Selesai', 'Ditolak')
                  AND archived_at IS NULL
                  AND updated_at < ?
            ");
            $stmt->execute([$cutoff]);
            $count += $stmt->rowCount();
        }

        return $count;
    }

    // =========================================================
    //  HELPER
    // =========================================================

    /**
     * Ambil repository yang sesuai dengan type
     */
    private function getRepo(string $type): PermohonanDomisiliRepository|PermohonanKTMRepository|PermohonanSKURepository|null
    {
        return match ($type) {
            'domisili' => $this->domisiliRepo,
            'ktm'      => $this->ktmRepo,
            'sku'      => $this->skuRepo,
            default    => null,
        };
    }

    /**
     * Pastikan kolom 'status' adalah string (bukan StatusPermohonan enum object)
     * karena view memakai === 'Diproses', bukan ->value
     * BUG FIX: castRow() di BaseModel mengubah status jadi enum object,
     * tapi view PHP native butuh string biasa.
     */
    private function castStatusRows(array $rows): array
    {
        return array_map(function (array $row) {
            if (isset($row['status']) && $row['status'] instanceof \BackedEnum) {
                $row['status'] = $row['status']->value;
            }
            return $row;
        }, $rows);
    }
}
