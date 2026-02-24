<?php

/**
 * LOGIC TIER - AdminSuratService
 * Mengelola semua operasi permohonan surat oleh admin:
 * grouping, update status, detail, generate surat, arsip, auto-arsip.
 */

require_once __DIR__ . '/../../data_tier/config/database.php';
require_once __DIR__ . '/../../logic_tier/repositories/PermohonanDomisiliRepository.php';
require_once __DIR__ . '/../../logic_tier/repositories/PermohonanKTMRepository.php';
require_once __DIR__ . '/../../logic_tier/repositories/PermohonanSKURepository.php';
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
    //  GROUPING PERMOHONAN
    // =========================================================

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

    public function getAllPermohonan(): array
    {
        $domisili = $this->castStatusRows($this->domisiliRepo->getAllWithUser());
        $sku      = $this->castStatusRows($this->skuRepo->getAllWithUser());
        $ktm      = $this->castStatusRows($this->ktmRepo->getAllWithUser());

        $all = array_merge(
            array_map(fn($r) => array_merge($r, ['type' => 'domisili']), $domisili),
            array_map(fn($r) => array_merge($r, ['type' => 'sku']),      $sku),
            array_map(fn($r) => array_merge($r, ['type' => 'ktm']),      $ktm)
        );
        usort($all, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

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

    public function getPermohonanDetail(string $type, int $id): ?array
    {
        $repo = $this->getRepo($type);
        if (!$repo) return null;

        $permohonan = $repo->findWithUser($id);
        if (!$permohonan) return null;

        $permohonan = $this->castStatusRows([$permohonan])[0];

        [$jenisSurat, $title] = match ($type) {
            'domisili' => ['Keterangan Domisili',           'Detail Surat Keterangan Domisili'],
            'ktm'      => ['Keterangan Tidak Mampu (SKTM)', 'Detail Surat Keterangan Tidak Mampu'],
            'sku'      => ['Keterangan Usaha (SKU)',         'Detail Surat Keterangan Usaha'],
            default    => ['Surat', 'Detail Surat'],
        };

        return compact('permohonan', 'jenisSurat', 'title');
    }

    // =========================================================
    //  UPDATE STATUS
    // =========================================================

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
            $this->notifService->kirimNotifikasiUser(
                (int)($permohonan['user_id'] ?? 0),
                $type,
                $id,
                $data['status'],
                $data['keterangan_penolakan'] ?? null,
                null,
                $permohonan['nik'] ?? null
            );
        }

        return $result;
    }

    // =========================================================
    //  GENERATE SURAT
    // =========================================================

    public function generateAndSaveSurat(string $type, int $id): bool
    {
        $repo = $this->getRepo($type);
        if (!$repo) return false;

        $permohonan = $repo->findOrFail($id);
        $permohonan = $this->castStatusRows([$permohonan])[0];

        $html = $this->templateService->generateTemplate($type, $permohonan);

        // Embed semua gambar lokal ke Base64 agar bisa dibuka/didownload offline
        $html = $this->embedImagesToBase64($html);

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
            $this->notifService->kirimNotifikasiUser(
                (int)($permohonan['user_id'] ?? 0),
                $type,
                $id,
                'Selesai',
                null,
                $relativePath,
                $permohonan['nik'] ?? null
            );
        }

        return $result;
    }

    // =========================================================
    //  ARSIP
    // =========================================================

    public function archivePermohonan(string $type, int $id): bool
    {
        $repo = $this->getRepo($type);
        if (!$repo) return false;
        return $repo->archive($id);
    }

    public function autoArchiveOldPermohonan(): int
    {
        $tables = ['permohonan_domisili', 'permohonan_ktm', 'permohonan_sku'];
        $count  = 0;
        $cutoff = date('Y-m-d H:i:s', strtotime('-15 days'));

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

    private function getRepo(string $type): PermohonanDomisiliRepository|PermohonanKTMRepository|PermohonanSKURepository|null
    {
        return match ($type) {
            'domisili' => $this->domisiliRepo,
            'ktm'      => $this->ktmRepo,
            'sku'      => $this->skuRepo,
            default    => null,
        };
    }

    private function castStatusRows(array $rows): array
    {
        return array_map(function (array $row) {
            if (isset($row['status']) && $row['status'] instanceof \BackedEnum) {
                $row['status'] = $row['status']->value;
            }
            return $row;
        }, $rows);
    }

    /**
     * Embed semua gambar lokal dalam HTML menjadi Base64
     * agar file HTML bisa dibuka/didownload tanpa koneksi server
     */
    private function embedImagesToBase64(string $html): string
    {
        return preg_replace_callback(
            '/(<img[^>]+src=")[^"]*?(\/web-pengajuan\/|\/uploads\/|\/images\/)([^"]+)(")/i',
            function ($matches) {
                $relativePath = $matches[2] . $matches[3];
                $absolutePath = $_SERVER['DOCUMENT_ROOT'] . $relativePath;

                if (file_exists($absolutePath)) {
                    $mime    = mime_content_type($absolutePath);
                    $base64  = base64_encode(file_get_contents($absolutePath));
                    $dataUri = "data:{$mime};base64,{$base64}";
                    return $matches[1] . $dataUri . $matches[4];
                }

                return $matches[0];
            },
            $html
        );
    }
}