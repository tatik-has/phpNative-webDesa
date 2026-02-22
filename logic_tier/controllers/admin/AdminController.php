<?php

/**
 * LOGIC TIER - Controller Admin Surat
 * Pengganti App\LogicTier\Controllers\Admin\AdminController di Laravel.
 *
 * FITUR BARU: showTemplateSurat() + generateSurat() untuk template surat HTML
 * yang otomatis terisi dari data permohonan.
 */

require_once __DIR__ . '/../../middleware/AdminAuthMiddleware.php';
require_once __DIR__ . '/../../services/AdminSuratService.php';
require_once __DIR__ . '/../../services/SuratTemplateService.php';

class AdminController
{
    private AdminSuratService $permohonanService;
    private SuratTemplateService $templateService;

    public function __construct()
    {
        AdminAuthMiddleware::check();
        $this->permohonanService = new AdminSuratService();
        $this->templateService   = new SuratTemplateService();
    }

    /**
     * Daftar permohonan dikelompokkan berdasarkan status
     * Setara: showPermohonanSurat() di Laravel
     */
    public function showPermohonanSurat(): void
    {
        $admin       = AdminAuthMiddleware::getAdmin();
        $groupedData = $this->permohonanService->getGroupedPermohonan();
        extract(array_merge(['admin' => $admin], $groupedData));
        require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/permohonan-surat.php';
    }

    /**
     * Semua permohonan tanpa filter status
     */
    public function semuaPermohonan(): void
    {
        $admin   = AdminAuthMiddleware::getAdmin();
        $allData = $this->permohonanService->getAllPermohonan();
        extract(array_merge(['admin' => $admin], $allData));
        require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/permohonan-surat.php';
    }

    /**
     * Update status permohonan (Diterima / Ditolak)
     * Setara: updateStatusPermohonan() di Laravel
     */
    public function updateStatusPermohonan(string $type, int $id): void
    {
        // Validasi input
        $status  = trim($_POST['status'] ?? '');
        $errors  = [];

        if (empty($status)) {
            $errors[] = 'Status wajib dipilih.';
        }
        if ($status === 'Ditolak' && empty($_POST['keterangan_penolakan'])) {
            $errors[] = 'Keterangan penolakan wajib diisi jika status Ditolak.';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', $errors);
            header('Location: /web-pengajuan/admin/surat');
            exit;
        }

        $this->permohonanService->updateStatus($type, $id, [
            'status'               => $status,
            'keterangan_penolakan' => $_POST['keterangan_penolakan'] ?? null,
        ]);

        $_SESSION['success'] = 'Status permohonan berhasil diperbarui!';
        header('Location: /web-pengajuan/admin/surat');
        exit;
    }

    /**
     * =====================================================
     * FITUR BARU: Tampilkan template surat HTML
     * yang sudah terisi otomatis dari data permohonan
     * =====================================================
     * Setara: kirimSurat() di Laravel (diganti jadi template-based)
     * Route: GET /admin/surat/{type}/{id}/template
     */
    public function showTemplateSurat(string $type, int $id): void
    {
        $data = $this->permohonanService->getPermohonanDetail($type, $id);
        if (!$data) {
            http_response_code(404);
            echo "<h1>Data tidak ditemukan</h1>";
            return;
        }

        $admin      = AdminAuthMiddleware::getAdmin();
        $permohonan = $data['permohonan']  ?? [];
        $jenisSurat = $data['jenis_surat'] ?? 'Surat';        // FIX: null coalescing
        $title      = $data['title']       ?? 'Template Surat'; // FIX: null coalescing

        // Generate HTML template surat yang sudah terisi data
        $templateHtml = $this->templateService->generateTemplate($type, $permohonan);

        extract(compact('admin', 'permohonan', 'jenisSurat', 'title', 'templateHtml', 'type', 'id'));
        require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/template-surat.php';
    }

    /**
     * =====================================================
     * FITUR BARU: Simpan surat yang sudah di-generate
     * dan update status menjadi Selesai
     * =====================================================
     * Route: POST /admin/surat/{type}/{id}/generate-surat
     */
    public function generateSurat(string $type, int $id): void
    {
        $result = $this->permohonanService->generateAndSaveSurat($type, $id);

        if ($result) {
            $_SESSION['success'] = 'Surat berhasil dibuat dan dikirim ke masyarakat! Status diperbarui menjadi Selesai.';
        } else {
            $_SESSION['error'] = 'Gagal membuat surat. Silakan coba lagi.';
        }

        header("Location: /web-pengajuan/admin/{$type}/{$id}");
        exit;
    }

    /**
     * Cetak / preview surat (print view)
     * Route: GET /admin/surat/{type}/{id}/print
     */
    public function printSurat(string $type, int $id): void
    {
        $data = $this->permohonanService->getPermohonanDetail($type, $id);
        if (!$data) {
            http_response_code(404);
            echo "<h1>Data tidak ditemukan</h1>";
            return;
        }

        $permohonan   = $data['permohonan'];
        $templateHtml = $this->templateService->generateTemplate($type, $permohonan);

        // Print view — tanpa layout admin, hanya surat saja
        extract(compact('permohonan', 'templateHtml', 'type'));
        require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/print-surat.php';
    }

    /**
     * Detail permohonan (KTM / SKU / Domisili)
     */
    public function showDetailSurat(string $jenis, int $id): void
    {
        $data = $this->permohonanService->getPermohonanDetail($jenis, $id);
        if (!$data) {
            http_response_code(404);
            echo "<h1>Data tidak ditemukan.</h1>";
            return;
        }

        $admin = AdminAuthMiddleware::getAdmin();
        extract(array_merge(['admin' => $admin], $data));
        require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/detail-surat.php';
    }

    public function showKtmDetail(int $id): void      { $this->showDetailSurat('ktm', $id); }
    public function showSkuDetail(int $id): void      { $this->showDetailSurat('sku', $id); }
    public function showDomisiliDetail(int $id): void { $this->showDetailSurat('domisili', $id); }

    /**
     * Arsipkan permohonan manual
     * BUG FIX: Parameter $type dan $id berasal dari URL (route param), bukan $_POST
     * Route: POST /admin/surat/{type}/{id}/archive → dipanggil AdminLaporanController
     * Method ini tidak dipanggil lagi lewat route, tapi tetap tersedia jika dibutuhkan
     */
    public function archivePermohonan(string $type, int $id): void
    {
        if (empty($type) || $id === 0) {
            $_SESSION['error'] = 'Data tidak valid.';
            header('Location: /web-pengajuan/admin/surat');
            exit;
        }

        $result = $this->permohonanService->archivePermohonan($type, $id);

        $_SESSION[$result ? 'success' : 'error'] = $result
            ? 'Permohonan berhasil diarsipkan!'
            : 'Gagal mengarsipkan permohonan.';

        header('Location: /web-pengajuan/admin/surat');
        exit;
    }

    /**
     * Auto archive permohonan lama (>15 hari, status Selesai/Ditolak)
     * BUG FIX: Method ini dipanggil route POST /admin/run-auto-archive
     * bukan dari AdminLaporanController (yang punya sendiri)
     */
    public function runAutoArchive(): void
    {
        $count = $this->permohonanService->autoArchiveOldPermohonan();
        $_SESSION['success'] = "Berhasil mengarsipkan {$count} permohonan lama.";
        header('Location: /admin/laporan');
        exit;
    }
}