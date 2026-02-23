<?php

/**
 * LOGIC TIER - Controller Permohonan SKTM
 * Pengganti App\LogicTier\Controllers\Masyarakat\SKTMController di Laravel.
 */

require_once __DIR__ . '/../../services/SuratKtmService.php';

class SKTMController
{
    private SuratKtmService $ktmService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->ktmService = new SuratKtmService();
    }

    public function create(): void
    {
        require_once __DIR__ . '/../../../presentation_tier/masyarakat/permohonan/ktm.php';
    }

    public function store(): void
    {
        $errors = $this->validateKtm($_POST, $_FILES);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: /web-pengajuan/pengajuan/sktm');
            exit;
        }

        try {
            $validated = $_POST;
            unset($validated['declaration']);

            $files = [
                'ktp'                   => $_FILES['ktp']                   ?? null,
                'kk'                    => $_FILES['kk']                    ?? null,
                'surat_pengantar_rt_rw' => $_FILES['surat_pengantar_rt_rw'] ?? null,
                'foto_rumah'            => $_FILES['foto_rumah']             ?? null,
            ];

            $this->ktmService->storeKtm($validated, $files);

            unset($_SESSION['errors'], $_SESSION['old_input']);

            // Simpan NIK ke session untuk cek notifikasi (tanpa login)
            $_SESSION['nik_pemohon'] = $_POST['nik'];

            $_SESSION['success'] = 'Pengajuan Surat Keterangan Tidak Mampu berhasil diajukan! Admin akan segera memproses permohonan Anda.';
            header('Location: /web-pengajuan/dashboard');
            exit;

        } catch (Exception $e) {
            $_SESSION['error']     = 'Terjadi kesalahan: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
            header('Location: /web-pengajuan/pengajuan/sktm');
            exit;
        }
    }

    private function validateKtm(array $post, array $files): array
    {
        $errors = [];

        if (empty($post['nik']) || strlen($post['nik']) !== 16) {
            $errors['nik'] = 'NIK wajib diisi dan harus 16 digit.';
        }
        if (empty($post['nama'])) {
            $errors['nama'] = 'Nama wajib diisi.';
        }
        if (empty($post['jenis_kelamin'])) {
            $errors['jenis_kelamin'] = 'Jenis kelamin wajib dipilih.';
        }
        if (empty($post['nomor_telp']) || !preg_match('/^(08|\+628)[0-9]{9,11}$/', $post['nomor_telp'])) {
            $errors['nomor_telp'] = 'Nomor telepon tidak valid.';
        }
        if (empty($post['alamat_lengkap'])) {
            $errors['alamat_lengkap'] = 'Alamat lengkap wajib diisi.';
        }
        if (empty($post['keperluan'])) {
            $errors['keperluan'] = 'Keperluan wajib diisi.';
        }
        if (!isset($post['penghasilan']) || $post['penghasilan'] === '') {
            $errors['penghasilan'] = 'Penghasilan wajib diisi.';
        }
        if (!isset($post['jumlah_tanggungan'])) {
            $errors['jumlah_tanggungan'] = 'Jumlah tanggungan wajib diisi.';
        }
        if (empty($post['declaration'])) {
            $errors['declaration'] = 'Anda harus menyetujui pernyataan kebenaran data.';
        }

        $requiredFiles = [
            'ktp'                   => 'KTP',
            'kk'                    => 'KK',
            'surat_pengantar_rt_rw' => 'Surat Pengantar RT/RW',
            'foto_rumah'            => 'Foto Rumah',
        ];
        foreach ($requiredFiles as $key => $label) {
            if (empty($files[$key]['name'])) {
                $errors[$key] = "File {$label} wajib diupload.";
            } elseif ($files[$key]['size'] > 100 * 1024 * 1024) {
                $errors[$key] = "File {$label} maksimal 100MB.";
            }
        }

        return $errors;
    }
}