<?php

require_once __DIR__ . '/../../services/SuratSkuService.php';

class SKUController
{
    private SuratSkuService $skuService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->skuService = new SuratSkuService();
    }

    // [BARU] Cek login NIK — redirect ke halaman login jika belum login
    private function cekLogin(): void
    {
        if (empty($_SESSION['nik_pemohon'])) {
            $_SESSION['error'] = 'Anda harus login terlebih dahulu untuk mengajukan surat.';
            header('Location: /web-pengajuan/login');
            exit;
        }
    }

    public function create(): void
    {
        $this->cekLogin(); // [BARU]
        require_once __DIR__ . '/../../../presentation_tier/masyarakat/permohonan/usaha.php';
    }

    public function store(): void
    {
        $this->cekLogin(); // [BARU]

        $errors = $this->validateSku($_POST, $_FILES);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: /web-pengajuan/pengajuan/sku');
            exit;
        }

        try {
            $files = [
                'ktp'             => $_FILES['ktp']             ?? null,
                'kk'              => $_FILES['kk']              ?? null,
                'surat_pengantar' => $_FILES['surat_pengantar'] ?? null,
                'foto_usaha'      => $_FILES['foto_usaha']      ?? null,
            ];

            $this->skuService->storeSku($_POST, $files);

            unset($_SESSION['errors'], $_SESSION['old_input']);

            $_SESSION['success'] = 'Pengajuan Surat Keterangan Usaha berhasil diajukan! Admin akan segera memproses permohonan Anda.';
            header('Location: /web-pengajuan/dashboard');
            exit;

        } catch (Exception $e) {
            $_SESSION['error']     = 'Terjadi kesalahan: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
            header('Location: /web-pengajuan/pengajuan/sku');
            exit;
        }
    }

    private function validateSku(array $post, array $files): array
    {
        $errors = [];

        if (empty($post['nik']) || strlen($post['nik']) !== 16) {
            $errors['nik'] = 'NIK wajib diisi dan harus 16 digit.';
        }
        if (empty($post['nama'])) {
            $errors['nama'] = 'Nama wajib diisi.';
        }
        if (empty($post['alamat_ktp'])) {
            $errors['alamat_ktp'] = 'Alamat KTP wajib diisi.';
        }
        if (empty($post['nomor_telp']) || !preg_match('/^(08|\+628)[0-9]{9,11}$/', $post['nomor_telp'])) {
            $errors['nomor_telp'] = 'Nomor telepon tidak valid.';
        }
        if (empty($post['nama_usaha'])) {
            $errors['nama_usaha'] = 'Nama usaha wajib diisi.';
        }
        if (empty($post['jenis_usaha'])) {
            $errors['jenis_usaha'] = 'Jenis usaha wajib diisi.';
        }
        if (empty($post['alamat_usaha'])) {
            $errors['alamat_usaha'] = 'Alamat usaha wajib diisi.';
        }
        if (empty($post['lama_usaha'])) {
            $errors['lama_usaha'] = 'Lama usaha wajib diisi.';
        }

        $requiredFiles = [
            'ktp'             => 'KTP',
            'kk'              => 'KK',
            'surat_pengantar' => 'Surat Pengantar',
        ];
        foreach ($requiredFiles as $key => $label) {
            if (empty($files[$key]['name'])) {
                $errors[$key] = "File {$label} wajib diupload.";
            } elseif ($files[$key]['size'] > 100 * 1024 * 1024) {
                $errors[$key] = "File {$label} maksimal 100MB.";
            }
        }
        if (!empty($files['foto_usaha']['name']) && $files['foto_usaha']['size'] > 100 * 1024 * 1024) {
            $errors['foto_usaha'] = 'File Foto Usaha maksimal 100MB.';
        }

        return $errors;
    }
}