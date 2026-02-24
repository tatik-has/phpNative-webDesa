<?php

/**
 * SISTEM LOGIKA - Controller Laporan Admin
 */

require_once __DIR__ . '/../../keamanan/ValidasiLogin.php';
require_once __DIR__ . '/../../services/AdminLaporanService.php';
require_once __DIR__ . '/../../services/AdminSuratService.php';
require_once __DIR__ . '/../../repositories/PermohonanDomisiliRepository.php';
require_once __DIR__ . '/../../repositories/PermohonanKTMRepository.php';
require_once __DIR__ . '/../../repositories/PermohonanSKURepository.php';

class AdminLaporanController
{
    private AdminLaporanService $laporanService;
    private AdminSuratService $suratService;

    public function __construct()
    {
        ValidasiLogin::cekSesi();
        $this->laporanService = new AdminLaporanService();
        $this->suratService   = new AdminSuratService();
    }

    public function showLaporan(): void
    {
        $admin        = ValidasiLogin::ambilDataAdmin();
        $tanggalMulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
        $tanggalAkhir = $_GET['tanggal_akhir'] ?? date('Y-m-d');
        $statusFilter = $_GET['status'] ?? 'semua';
        $export       = $_GET['export'] ?? null;

        if ($tanggalMulai > $tanggalAkhir) {
            $_SESSION['error'] = 'Tanggal mulai tidak boleh lebih dari tanggal akhir.';
            $tanggalMulai = date('Y-m-01');
            $tanggalAkhir = date('Y-m-d');
        }

        $allPermohonan = $this->laporanService->getLaporanData($tanggalMulai, $tanggalAkhir, $statusFilter);

        if ($export === 'word') {
            $statusLabel = $statusFilter === 'semua' ? 'Semua' : ucfirst($statusFilter);
            $fileName    = "Laporan_Surat_{$statusLabel}_{$tanggalMulai}_sd_{$tanggalAkhir}.doc";

            header('Content-Type: application/vnd.ms-word');
            header("Content-Disposition: attachment; filename=\"{$fileName}\"");
            header('Cache-Control: max-age=0');

            extract(compact('allPermohonan', 'tanggalMulai', 'tanggalAkhir', 'statusFilter'));
            require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/laporan-word.php';
            exit;
        }

        extract(compact('admin', 'allPermohonan', 'tanggalMulai', 'tanggalAkhir', 'statusFilter'));
        require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/laporan.php';
    }

    public function archivePermohonan(string $type, int $id): void
    {
        if (empty($type) || $id === 0) {
            $_SESSION['error'] = 'Data tidak valid.';
            header('Location: /web-pengajuan/admin/laporan');
            exit;
        }

        try {
            $success = $this->laporanService->archiveData($type, $id);
            $_SESSION[$success ? 'success' : 'error'] = $success
                ? 'Data permohonan berhasil diarsipkan.'
                : 'Jenis surat tidak valid.';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Terjadi kesalahan: ' . $e->getMessage();
        }

        header('Location: /web-pengajuan/admin/surat');
        exit;
    }

    public function showArsip(): void
    {
        $admin        = ValidasiLogin::ambilDataAdmin();
        $archivedData = $this->laporanService->getArchivedPermohonan();
        extract(array_merge(['admin' => $admin], $archivedData));
        require_once __DIR__ . '/../../../presentation_tier/admin/permohonan/arsip.php';
    }

    public function destroyPermanently(string $type, int $id): void
    {
        try {
            $repo = match ($type) {
                'domisili' => new PermohonanDomisiliRepository(),
                'sku'      => new PermohonanSKURepository(),
                'ktm'      => new PermohonanKTMRepository(),
                default    => null,
            };

            if ($repo) {
                $repo->delete($id);
                $_SESSION['success'] = 'Data arsip berhasil dihapus secara permanen.';
            } else {
                $_SESSION['error'] = 'Jenis data tidak valid.';
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'Gagal menghapus data: ' . $e->getMessage();
        }

        header('Location: /web-pengajuan/admin/arsip');
        exit;
    }

    public function runAutoArchive(): void
    {
        $count = $this->suratService->autoArchiveOldPermohonan();
        $_SESSION['success'] = "Berhasil mengarsipkan {$count} permohonan lama.";
        header('Location: /web-pengajuan/admin/laporan');
        exit;
    }
}