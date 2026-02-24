<?php

class ValidasiLogin
{
    
    public static function cekSesi(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['admin_id'])) {
            header('Location: /web-pengajuan/admin/login');
            exit;
        }
    }

    /**
     * Ambil data akun yang sedang aktif dari session
     */
    public static function ambilDataAdmin(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return [
            'id'    => $_SESSION['admin_id']   ?? null,
            'nama'  => $_SESSION['admin_nama']  ?? '',
            'role'  => $_SESSION['admin_role']  ?? 'admin',
            'email' => $_SESSION['admin_email'] ?? '',
        ];
    }
}