<?php

/**
 * LOGIC TIER - Controller Auth Masyarakat
 * Login menggunakan NIK yang telah didaftarkan oleh admin.
 * Tidak perlu registrasi — akun otomatis aktif setelah admin input NIK.
 */

require_once __DIR__ . '/../../keamanan/ValidasiLogin.php';

class MasyarakatAuthController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    /**
     * Tampilkan halaman login NIK masyarakat
     */
    public function showLogin(): void
    {
        // Kalau sudah login, redirect ke dashboard
        if (!empty($_SESSION['nik_pemohon'])) {
            header('Location: /web-pengajuan/dashboard');
            exit;
        }
        require_once __DIR__ . '/../../../presentation_tier/masyarakat/auth/login.php';
    }

    /**
     * Proses login dengan NIK
     */
    public function login(): void
    {
        $nik = trim($_POST['nik'] ?? '');

        // Validasi format
        if (empty($nik)) {
            $_SESSION['error'] = 'NIK wajib diisi.';
            header('Location: /web-pengajuan/login');
            exit;
        }

        if (!preg_match('/^\d{16}$/', $nik)) {
            $_SESSION['error'] = 'NIK harus 16 digit angka.';
            header('Location: /web-pengajuan/login');
            exit;
        }

        // Cek apakah NIK terdaftar di tabel users
        require_once __DIR__ . '/../../../data_tier/config/database.php';
        $db   = Database::getInstance();
        $stmt = $db->prepare("SELECT id, name, nik FROM users WHERE nik = ? LIMIT 1");
        $stmt->execute([$nik]);
        $warga = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$warga) {
            $_SESSION['error'] = 'NIK tidak terdaftar. Hubungi kantor desa untuk mendaftarkan NIK Anda.';
            header('Location: /web-pengajuan/login');
            exit;
        }

        // Set session masyarakat
        $_SESSION['nik_pemohon']   = $warga['nik'];
        $_SESSION['nama_pemohon']  = $warga['name'];
        $_SESSION['user_id']       = $warga['id'];

        $_SESSION['success'] = 'Selamat datang, ' . $warga['name'] . '!';
        header('Location: /web-pengajuan/dashboard');
        exit;
    }

    /**
     * Logout masyarakat — hanya hapus sesi masyarakat, bukan admin
     */
    public function logout(): void
    {
        unset(
            $_SESSION['nik_pemohon'],
            $_SESSION['nama_pemohon'],
            $_SESSION['user_id']
        );

        $_SESSION['success'] = 'Anda telah berhasil logout.';
        header('Location: /web-pengajuan/login');
        exit;
    }
}