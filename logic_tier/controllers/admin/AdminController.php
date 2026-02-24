<?php

require_once __DIR__ . '/../../keamanan/ValidasiLogin.php';
require_once __DIR__ . '/../../services/AdminSuratService.php';
require_once __DIR__ . '/../../services/SuratTemplateService.php';

class AdminController
{
    private AdminSuratService $permohonanService;
    private SuratTemplateService $templateService;

    public function __construct()
    {
        // Menggunakan class keamanan baru
        ValidasiLogin::cekSesi();
        $this->permohonanService = new AdminSuratService();
        $this->templateService   = new SuratTemplateService();
    }

    /**
     * Daftar permohonan dikelompokkan berdasarkan status
     */
    public function showPermohonanSurat(): void
    {
        // Mengambil data admin dari Pelindung/ValidasiLogin
        $admin       = ValidasiLogin::ambilDataAdmin();
        $groupedData = $this->permohonanService->getGroupedPermohonan();
        extract(array_merge(['admin' => $admin], $groupedData));
        require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/permohonan-surat.php';
    }

    /**
     * Semua permohonan tanpa filter status
     */
    public function semuaPermohonan(): void
    {
        $admin   = ValidasiLogin::ambilDataAdmin();
        $allData = $this->permohonanService->getAllPermohonan();
        extract(array_merge(['admin' => $admin], $allData));
        require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/permohonan-surat.php';
    }

    /**
     * Update status permohonan (Diterima / Ditolak)
     */
    public function updateStatusPermohonan(string $type, int $id): void
    {
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
     * Tampilkan cetakan surat sementara sebelum di-generate
     */
    public function showTemplateSurat(string $type, int $id): void
    {
        $data = $this->permohonanService->getPermohonanDetail($type, $id);
        if (!$data) {
            http_response_code(404);
            echo "<h1>Data tidak ditemukan</h1>";
            return;
        }

        $admin      = ValidasiLogin::ambilDataAdmin();
        $permohonan = $data['permohonan']  ?? [];
        $jenisSurat = $data['jenis_surat'] ?? 'Surat';
        $title      = $data['title']       ?? 'Template Surat';

        $templateHtml = $this->templateService->generateTemplate($type, $permohonan);

        extract(compact('admin', 'permohonan', 'jenisSurat', 'title', 'templateHtml', 'type', 'id'));
        require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/template-surat.php';
    }

    /**
     * Simpan hasil surat jadi dan update status ke Selesai
     */
    public function generateSurat(string $type, int $id): void
    {
        $result = $this->permohonanService->generateAndSaveSurat($type, $id);

        if ($result) {
            $_SESSION['success'] = 'Surat berhasil dibuat dan dikirim! Status diperbarui menjadi Selesai.';
        } else {
            $_SESSION['error'] = 'Gagal membuat surat. Silakan coba lagi.';
        }

        header("Location: /web-pengajuan/admin/{$type}/{$id}");
        exit;
    }

    /**
     * Cetak langsung surat ke printer
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

        extract(compact('permohonan', 'templateHtml', 'type'));
        require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/print-surat.php';
    }

    /**
     * Detail lengkap permohonan
     */
    public function showDetailSurat(string $jenis, int $id): void
    {
        $data = $this->permohonanService->getPermohonanDetail($jenis, $id);
        if (!$data) {
            http_response_code(404);
            echo "<h1>Data tidak ditemukan.</h1>";
            return;
        }

        $admin = ValidasiLogin::ambilDataAdmin();
        extract(array_merge(['admin' => $admin], $data));
        require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/detail-surat.php';
    }

    public function showKtmDetail(int $id): void      { $this->showDetailSurat('ktm', $id); }
    public function showSkuDetail(int $id): void      { $this->showDetailSurat('sku', $id); }
    public function showDomisiliDetail(int $id): void { $this->showDetailSurat('domisili', $id); }

    /**
     * Memasukkan data ke tabel arsip
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
     * Proses otomatis pemindahan data lama ke arsip
     */
    public function runAutoArchive(): void
    {
        $count = $this->permohonanService->autoArchiveOldPermohonan();
        $_SESSION['success'] = "Berhasil mengarsipkan {$count} permohonan lama.";
        header('Location: /web-pengajuan/admin/laporan');
        exit;
    }
}