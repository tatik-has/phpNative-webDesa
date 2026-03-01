<?php

/**
 * SISTEM LOGIKA - Controller Kelola NIK Warga
 * Admin dapat menambah NIK + nama warga satu per satu atau via import Excel.
 * Warga yang NIK-nya terdaftar bisa langsung login tanpa registrasi.
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

    /**
     * Tampilkan halaman kelola NIK warga
     */
    public function index(): void
    {
        $admin    = ValidasiLogin::ambilDataAdmin();
        $daftarNik = $this->nikService->getDaftarWarga();

        extract(compact('admin', 'daftarNik'));
        require_once __DIR__ . '/../../../presentation_tier/admin/nik/kelola-nik.php';
    }

    /**
     * Tambah satu NIK + nama warga secara manual
     */
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

    /**
     * Import NIK warga dari file Excel (.xlsx/.xls/.csv)
     * Format kolom: NIK (kolom A), Nama (kolom B)
     */
    public function importExcel(): void
    {
        if (empty($_FILES['file_excel']['tmp_name'])) {
            $_SESSION['error'] = 'File Excel wajib dipilih.';
            header('Location: /web-pengajuan/admin/nik');
            exit;
        }

        $file     = $_FILES['file_excel'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['xlsx', 'xls', 'csv'];

        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = 'Format file harus .xlsx, .xls, atau .csv.';
            header('Location: /web-pengajuan/admin/nik');
            exit;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $_SESSION['error'] = 'Ukuran file maksimal 5MB.';
            header('Location: /web-pengajuan/admin/nik');
            exit;
        }

        $result = $this->nikService->importDariExcel($file['tmp_name'], $ext);

        if (isset($result['error'])) {
            $_SESSION['error'] = $result['error'];
        } else {
            $msg = "Import selesai: {$result['berhasil']} data berhasil ditambahkan.";
            if ($result['duplikat'] > 0) {
                $msg .= " {$result['duplikat']} data dilewati (NIK sudah terdaftar).";
            }
            if ($result['gagal'] > 0) {
                $msg .= " {$result['gagal']} data gagal (format tidak valid).";
            }
            $_SESSION['success'] = $msg;
        }

        header('Location: /web-pengajuan/admin/nik');
        exit;
    }

    /**
     * Hapus satu data warga berdasarkan ID
     */
    public function destroy(int $id): void
    {
        $result = $this->nikService->hapusWarga($id);

        $_SESSION[$result ? 'success' : 'error'] = $result
            ? 'Data warga berhasil dihapus.'
            : 'Gagal menghapus data warga.';

        header('Location: /web-pengajuan/admin/nik');
        exit;
    }

    /**
     * Download template Excel untuk panduan import
     */
    public function downloadTemplate(): void
    {
        $filename = 'template_import_nik.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header("Content-Disposition: attachment; filename=\"{$filename}\"");
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        // BOM untuk Excel agar karakter Indonesia terbaca
        fputs($output, "\xEF\xBB\xBF");
        fputcsv($output, ['NIK', 'Nama']);
        fputcsv($output, ['1234567890123456', 'Contoh Nama Warga']);
        fputcsv($output, ['6789012345678901', 'Contoh Nama Warga 2']);
        fclose($output);
        exit;
    }
}