<?php

/**
 * LOGIC TIER - Middleware Admin Auth
 * Pengganti middleware('auth:admin') di Laravel.
 * Dipanggil di awal setiap controller admin yang protected.
 */

class AdminAuthMiddleware
{
    /**
     * Cek apakah admin sudah login via session.
     * Jika belum, redirect ke halaman login admin.
     */
    public static function check(): void
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
     * Ambil data admin yang sedang login dari session
     */
    public static function getAdmin(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        return [
            'id'   => $_SESSION['admin_id']   ?? null,
            'nama' => $_SESSION['admin_nama']  ?? '',
            'role' => $_SESSION['admin_role']  ?? 'admin',
            'email'=> $_SESSION['admin_email'] ?? '',
        ];
    }
}