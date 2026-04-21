<?php

/**
 * SISTEM LOGIKA - Controller Kelola NIK Warga
 */

require_once __DIR__ . '/../../keamanan/ValidasiLogin.php';
require_once __DIR__ . '/../../services/AdminNikService.php';

class AdminNikController
{
    private AdminNikService $nikService;

    public function __construct()
    {
        ValidasiLogin::cekSesi();
        $this->nikService = new AdminNikService();
    }

    public function index(): void
    {
        $admin     = ValidasiLogin::ambilDataAdmin();
        $daftarNik = $this->nikService->getDaftarWarga();

        extract(compact('admin', 'daftarNik'));
        require_once __DIR__ . '/../../../presentation_tier/admin/nik/kelola-nik.php';
    }

    public function store(): void
    {
        $nik  = trim($_POST['nik']  ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $errors = [];

        if (empty($nik)) {
            $errors[] = 'NIK wajib diisi.';
        } elseif (!preg_match('/^\d{16}$/', $nik)) {
            $errors[] = 'NIK harus 16 digit angka.';
        }

        if (empty($nama)) {
            $errors[] = 'Nama wajib diisi.';
        }

        if (!empty($errors)) {
            $_SESSION['error'] = implode(' ', $errors);
            header('Location: /web-pengajuan/admin/nik');
            exit;
        }

        $result = $this->nikService->tambahWarga($nik, $nama);

        if ($result === 'duplicate') {
            $_SESSION['error'] = "NIK {$nik} sudah terdaftar di sistem.";
        } elseif ($result === true) {
            $_SESSION['success'] = "Warga {$nama} (NIK: {$nik}) berhasil ditambahkan.";
        } else {
            $_SESSION['error'] = 'Gagal menambahkan warga. Silakan coba lagi.';
        }

        header('Location: /web-pengajuan/admin/nik');
        exit;
    }

    public function importExcel(): void
    {
        // Naikkan limit untuk file besar
        ini_set('memory_limit', '1G');
        ini_set('max_execution_time', '300');

        // Cek error upload dari PHP
        $errCode = $_FILES['file_excel']['error'] ?? UPLOAD_ERR_NO_FILE;

        if ($errCode !== UPLOAD_ERR_OK) {
            $uploadErrors = [
                UPLOAD_ERR_INI_SIZE   => 'File terlalu besar. Sesuaikan upload_max_filesize di php.ini menjadi 1G.',
                UPLOAD_ERR_FORM_SIZE  => 'File terlalu besar.',
                UPLOAD_ERR_PARTIAL    => 'File hanya terupload sebagian. Coba lagi.',
                UPLOAD_ERR_NO_FILE    => 'File Excel wajib dipilih.',
                UPLOAD_ERR_NO_TMP_DIR => 'Folder temporary tidak ditemukan.',
                UPLOAD_ERR_CANT_WRITE => 'Gagal menyimpan file sementara.',
            ];
            $_SESSION['error'] = $uploadErrors[$errCode] ?? 'Upload gagal (kode: ' . $errCode . ').';
            header('Location: /web-pengajuan/admin/nik');
            exit;
        }

        $file    = $_FILES['file_excel'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['xlsx', 'xls', 'csv'];

        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = 'Format file harus .xlsx, .xls, atau .csv.';
            header('Location: /web-pengajuan/admin/nik');
            exit;
        }

        // Maksimal 1GB
        if ($file['size'] > 1024 * 1024 * 1024) {
            $_SESSION['error'] = 'Ukuran file maksimal 1GB.';
            header('Location: /web-pengajuan/admin/nik');
            exit;
        }

        $result = $this->nikService->importDariExcel($file['tmp_name'], $ext);

        if (isset($result['error'])) {
            $_SESSION['error'] = $result['error'];
        } else {
            $msg = "Import selesai: {$result['berhasil']} data berhasil ditambahkan.";
            if ($result['duplikat'] > 0) $msg .= " {$result['duplikat']} data dilewati (NIK sudah terdaftar).";
            if ($result['gagal']    > 0) $msg .= " {$result['gagal']} data gagal (format tidak valid).";
            $_SESSION['success'] = $msg;
        }

        header('Location: /web-pengajuan/admin/nik');
        exit;
    }

    public function destroy(int $id): void
    {
        $result = $this->nikService->hapusWarga($id);

        $_SESSION[$result ? 'success' : 'error'] = $result
            ? 'Data warga berhasil dihapus.'
            : 'Gagal menghapus data warga.';

        header('Location: /web-pengajuan/admin/nik');
        exit;
    }

    public function downloadTemplate(): void
    {
        $filename = 'template_import_nik.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['NIK', 'Nama']);
        fputcsv($output, ['1234567890123456', 'Contoh Nama Warga']);
        fputcsv($output, ['6789012345678901', 'Contoh Nama Warga 2']);
        fclose($output);
        exit;
    }
}