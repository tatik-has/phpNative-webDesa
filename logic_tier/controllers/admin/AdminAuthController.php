<?php

require_once __DIR__ . '/../../../data_tier/models/Admin.php';
require_once __DIR__ . '/../../keamanan/ValidasiLogin.php';

class AdminAuthController
{
    private Admin $adminModel;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->adminModel = new Admin();
    }

    public function showLogin(): void
    {
        if (!empty($_SESSION['admin_id'])) {

            header('Location: /web-pengajuan/admin/dashboard');
            exit;
        }
        require_once __DIR__ . '/../../../presentation_tier/admin/auth/login.php';
    }

    public function login(): void
    {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $errors   = [];

        if (empty($email)) $errors['email'] = 'Email wajib diisi.';
        if (empty($password)) $errors['password'] = 'Password wajib diisi.';
        if (!empty($errors)) {
            $_SESSION['errors']    = $errors;
            $_SESSION['old_input'] = ['email' => $email];
 
            header('Location: /web-pengajuan/admin/login');
            exit;
        }

        $admin = $this->adminModel->findByEmail($email);

        if ($admin && $this->adminModel->verifyPassword($password, $admin['password'])) {
            $_SESSION['admin_id']    = $admin['id'];
            $_SESSION['admin_nama']  = $admin['nama'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['admin_role']  = $admin['role'];

            header('Location: /web-pengajuan/admin/dashboard');
            exit;
        }

        $_SESSION['errors']    = ['email' => 'Email atau password salah.'];
        $_SESSION['old_input'] = ['email' => $email];
        // SEBELUM: header('Location: /admin/login');
        // SESUDAH:
        header('Location: /web-pengajuan/admin/login');
        exit;
    }

    public function logout(): void
    {
        session_destroy();
    
        header('Location: /web-pengajuan/admin/login');
        exit;
    }
}
