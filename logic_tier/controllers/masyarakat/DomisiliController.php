<?php

/**
 * LOGIC TIER - Controller Permohonan Domisili
 * Pengganti App\LogicTier\Controllers\Masyarakat\DomisiliController di Laravel.
 * PERUBAHAN: Tidak ada middleware auth → akses publik.
 */

require_once __DIR__ . '/../../services/SuratDomisiliService.php';

class DomisiliController
{
    private SuratDomisiliService $domisiliService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->domisiliService = new SuratDomisiliService();
    }

    /**
     * Tampilkan form pengajuan domisili
     */
    public function showForm(): void
    {
        require_once __DIR__ . '/../../../presentation_tier/masyarakat/permohonan/domisili.php';
    }

    /**
     * Proses simpan permohonan domisili
     * Setara: store(StoreDomisiliRequest $request) di Laravel
     */
    public function store(): void
    {
        $errors = $this->validateDomisili($_POST, $_FILES);

        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: /web-pengajuan/pengajuan/domisili');
            exit;
        }

        try {
            $this->domisiliService->storeDomisili($_POST, $_FILES['ktp'] ?? null, $_FILES['kk'] ?? null);

            // TAMBAH INI — simpan NIK ke session untuk cek notifikasi
            $_SESSION['nik_pemohon'] = $_POST['nik'];

            $_SESSION['success'] = 'Pengajuan berhasil diajukan!';
            header('Location: /web-pengajuan/dashboard');
            exit;
        } catch (Exception $e) {
            $_SESSION['error']     = 'Terjadi kesalahan: ' . $e->getMessage();
            $_SESSION['old_input'] = $_POST;
            header('Location: /web-pengajuan/pengajuan/domisili');
            exit;
        }
    }

    /**
     * Validasi input form domisili
     * Pengganti StoreDomisiliRequest di Laravel
     */
    private function validateDomisili(array $post, array $files): array
    {
        $errors = [];

        if (empty($post['nik']) || strlen($post['nik']) !== 16) {
            $errors['nik'] = 'NIK wajib diisi dan harus 16 digit.';
        }
        if (empty($post['nama'])) {
            $errors['nama'] = 'Nama wajib diisi.';
        }
        if (empty($post['alamat_domisili'])) {
            $errors['alamat_domisili'] = 'Alamat domisili wajib diisi.';
        }
        if (empty($post['nomor_telp']) || !preg_match('/^(08|\+628)[0-9]{9,11}$/', $post['nomor_telp'])) {
            $errors['nomor_telp'] = 'Nomor telepon tidak valid. Gunakan format 08xxxxxxxxxx.';
        }
        if (empty($post['rt_domisili'])) {
            $errors['rt_domisili'] = 'RT wajib diisi.';
        }
        if (empty($post['rw_domisili'])) {
            $errors['rw_domisili'] = 'RW wajib diisi.';
        }
        if (empty($post['jenis_kelamin']) || !in_array($post['jenis_kelamin'], ['Laki-laki', 'Perempuan'])) {
            $errors['jenis_kelamin'] = 'Jenis kelamin wajib dipilih.';
        }
        if (empty($post['alamat_ktp'])) {
            $errors['alamat_ktp'] = 'Alamat KTP wajib diisi.';
        }
        if (empty($files['ktp']['name'])) {
            $errors['ktp'] = 'File KTP wajib diupload.';
        } elseif ($files['ktp']['size'] > 100 * 1024 * 1024) {
            $errors['ktp'] = 'File KTP maksimal 2MB.';
        }
        if (empty($files['kk']['name'])) {
            $errors['kk'] = 'File KK wajib diupload.';
        } elseif ($files['kk']['size'] > 100 * 1024 * 1024) {
            $errors['kk'] = 'File KK maksimal 2MB.';
        }

        return $errors;
    }
}
