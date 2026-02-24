<?php

if (session_status() === PHP_SESSION_NONE) session_start();

$errors       = $_SESSION['errors']    ?? [];
$oldEmail     = $_SESSION['old_input']['email'] ?? '';
$flashError   = $_SESSION['error']     ?? null;
unset($_SESSION['errors'], $_SESSION['old_input'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Desa Pakning Asal</title>
    <link rel="stylesheet" href="/web-pengajuan/presentation_tier/css/admin/admin.css">
    <link rel="stylesheet" href="/web-pengajuan/presentation_tier/css/shared/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>

<div class="main-wrapper">
    <!-- Sisi Kiri: Informasi Sistem -->
    <div class="info-section">
        <div class="info-content">
            <img src="/web-pengajuan/images/logo.png" alt="Logo Desa" class="info-logo">
            <h2>Sistem Informasi Surat Menyurat</h2>
            <p>Panel administrasi untuk petugas Desa Pakning Asal.</p>
            <ul class="feature-list">
                <li>Kelola Permohonan Surat</li>
                <li>Buat & Kirim Surat Keterangan</li>
                <li>Laporan & Arsip Digital</li>
            </ul>
        </div>
    </div>

    <!-- Sisi Kanan: Form Login -->
    <div class="login-section">
        <div class="login-box">
            <div class="login-welcome">
                <h1>Login Admin</h1>
                <p>Masukkan kredensial Anda untuk melanjutkan</p>
            </div>

            <?php if ($flashError): ?>
                <div class="login-message-error"><?= htmlspecialchars($flashError) ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="login-message-error">
                    <ul style="margin:0;padding-left:20px;">
                        <?php foreach ($errors as $e): ?>
                            <li><?= htmlspecialchars(is_array($e) ? $e[0] : $e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="/web-pengajuan/admin/login">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">

                <input
                    type="email"
                    name="email"
                    placeholder="Email Admin"
                    value="<?= htmlspecialchars($oldEmail) ?>"
                    class="login-input"
                    required
                >
                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    class="login-input"
                    required
                >
                <button class="login-button" type="submit">Masuk</button>
            </form>
        </div>
    </div>
</div>

</body>
</html>