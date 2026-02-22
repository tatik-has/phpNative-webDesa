<?php

require_once __DIR__ . '/../../middleware/AdminAuthMiddleware.php';

class AdminPengaturanController
{
    // FIX: gunakan DOCUMENT_ROOT agar path selalu tepat
    private function ttdAbsPath(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/web-pengajuan/assets/img/ttd-kepala-desa.png';
    }

    private function ttdUrl(): string
    {
        return '/web-pengajuan/assets/img/ttd-kepala-desa.png';
    }

    private function configFile(): string
    {
        return $_SERVER['DOCUMENT_ROOT'] . '/web-pengajuan/data_tier/config/kades.json';
    }

    /**
     * Halaman pengaturan TTD
     * Route: GET /admin/pengaturan/ttd
     */
    public function showTtdPage(): void
    {
        AdminAuthMiddleware::check();
        $admin = AdminAuthMiddleware::getAdmin();

        $ttdPath   = $this->ttdAbsPath();
        $ttdUrl    = $this->ttdUrl();
        $ttdAda    = file_exists($ttdPath);
        $namaKades = $this->getNamaKades();

        extract(compact('admin', 'ttdAda', 'ttdUrl', 'namaKades'));
        require_once __DIR__ . '/../../../presentation_tier/admin/pengaturan/ttd-upload.php';
    }

    /**
     * Proses upload TTD
     * Route: POST /admin/pengaturan/ttd/upload
     */
    public function uploadTtd(): void
    {
        AdminAuthMiddleware::check();

        // Hapus TTD jika dicentang
        if (!empty($_POST['hapus_ttd'])) {
            $ttdPath = $this->ttdAbsPath();
            if (file_exists($ttdPath)) unlink($ttdPath);
            $_SESSION['success'] = 'Tanda tangan berhasil dihapus.';
            header('Location: /web-pengajuan/admin/pengaturan/ttd');
            exit;
        }

        // Validasi file
        if (empty($_FILES['ttd_file']['tmp_name'])) {
            $_SESSION['error'] = 'File tanda tangan wajib dipilih.';
            header('Location: /web-pengajuan/admin/pengaturan/ttd');
            exit;
        }

        $file    = $_FILES['ttd_file'];
        $allowed = ['image/png', 'image/jpeg'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        if (!in_array($file['type'], $allowed)) {
            $_SESSION['error'] = 'Format file harus PNG atau JPG.';
            header('Location: /web-pengajuan/admin/pengaturan/ttd');
            exit;
        }

        if ($file['size'] > $maxSize) {
            $_SESSION['error'] = 'Ukuran file maksimal 2MB.';
            header('Location: /web-pengajuan/admin/pengaturan/ttd');
            exit;
        }

        // FIX: pakai DOCUMENT_ROOT untuk path simpan file
        $destDir  = $_SERVER['DOCUMENT_ROOT'] . '/web-pengajuan/assets/img/';
        $destFile = $destDir . 'ttd-kepala-desa.png';

        if (!is_dir($destDir)) mkdir($destDir, 0755, true);

        move_uploaded_file($file['tmp_name'], $destFile);

        $_SESSION['success'] = 'Tanda tangan berhasil diupload! Akan muncul di semua surat.';
        header('Location: /web-pengajuan/admin/pengaturan/ttd');
        exit;
    }

    /**
     * Simpan nama kepala desa
     * Route: POST /admin/pengaturan/ttd/nama
     */
    public function simpanNamaKades(): void
    {
        AdminAuthMiddleware::check();

        $nama = trim($_POST['nama_kades'] ?? '');

        if (empty($nama)) {
            $_SESSION['error'] = 'Nama kepala desa tidak boleh kosong.';
            header('Location: /web-pengajuan/admin/pengaturan/ttd');
            exit;
        }

        $configFile = $this->configFile();
        $configDir  = dirname($configFile);

        if (!is_dir($configDir)) mkdir($configDir, 0755, true);

        $config = ['nama_kades' => $nama, 'updated_at' => date('Y-m-d H:i:s')];
        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));

        $_SESSION['success'] = 'Nama kepala desa berhasil disimpan!';
        header('Location: /web-pengajuan/admin/pengaturan/ttd');
        exit;
    }

    /**
     * Helper: ambil nama kades dari config JSON
     */
    private function getNamaKades(): string
    {
        $configFile = $this->configFile();
        if (!file_exists($configFile)) return '';
        $config = json_decode(file_get_contents($configFile), true);
        return $config['nama_kades'] ?? '';
    }
}