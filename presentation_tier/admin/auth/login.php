<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$errors     = $_SESSION['errors']    ?? [];
$oldEmail   = $_SESSION['old_input']['email'] ?? '';
$flashError = $_SESSION['error'] ?? null;
unset($_SESSION['errors'], $_SESSION['old_input'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Desa Pakning Asal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a3c5e 0%, #2d6a9f 50%, #1a3c5e 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background-image:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,.04) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,.04) 0%, transparent 50%);
            pointer-events: none;
        }

        .login-wrapper {
            width: 100%;
            max-width: 420px;
            animation: fadeInUp .5s ease;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }
        .login-header .logo-wrap {
            width: 72px; height: 72px;
            background: rgba(255,255,255,.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
            border: 2px solid rgba(255,255,255,.25);
        }
        .login-header .logo-wrap img {
            width: 48px; height: 48px; object-fit: contain;
        }
        .login-header .logo-wrap i {
            font-size: 28px; color: rgba(255,255,255,.9);
        }
        .login-header h1 {
            font-size: 20px; font-weight: 700;
            color: #fff; margin-bottom: 4px;
        }
        .login-header p {
            font-size: 13px; color: rgba(255,255,255,.7);
        }

        .login-card {
            background: #fff;
            border-radius: 16px;
            padding: 36px 32px;
            box-shadow: 0 20px 60px rgba(0,0,0,.25);
        }

        .login-card h2 {
            font-size: 18px; font-weight: 700;
            color: #1a2e45; margin-bottom: 6px;
        }
        .login-card .subtitle {
            font-size: 13px; color: #888;
            margin-bottom: 28px;
            line-height: 1.5;
        }

        /* Badge Admin */
        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eef4fb;
            color: #1a3c5e;
            font-size: 11px;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 20px;
            margin-bottom: 16px;
            border: 1px solid #bcd7f5;
        }
        .admin-badge i { font-size: 10px; }

        .alert-error {
            background: #fdedec;
            border: 1px solid #f5b7b1;
            color: #c0392b;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-error i { margin-top: 1px; flex-shrink: 0; }

        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 7px;
        }

        .input-wrap { position: relative; }
        .input-wrap i.icon-left {
            position: absolute; left: 14px; top: 50%;
            transform: translateY(-50%);
            color: #9ca3af; font-size: 15px;
            pointer-events: none;
        }
        .input-wrap input {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Inter', sans-serif;
            color: #1a2e45;
            transition: border-color .2s, box-shadow .2s;
        }
        .input-wrap input:focus {
            outline: none;
            border-color: #2d6a9f;
            box-shadow: 0 0 0 3px rgba(45,106,159,.12);
        }
        .input-wrap input::placeholder { color: #c4c9d4; }

        /* Toggle password */
        .toggle-pw {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none;
            cursor: pointer; color: #9ca3af;
            font-size: 15px; padding: 0;
        }
        .toggle-pw:hover { color: #2d6a9f; }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #1a3c5e, #2d6a9f);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: opacity .2s, transform .1s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 4px;
        }
        .btn-login:hover  { opacity: .92; }
        .btn-login:active { transform: scale(.98); }

        .back-link {
            text-align: center;
            margin-top: 22px;
        }
        .back-link a {
            color: rgba(255,255,255,.8);
            font-size: 13px;
            text-decoration: none;
            display: inline-flex; align-items: center; gap: 5px;
            transition: color .2s;
        }
        .back-link a:hover { color: #fff; }
    </style>
</head>
<body>

<div class="login-wrapper">

    <!-- Header -->
    <div class="login-header">
        <div class="logo-wrap">
            <img src="/web-pengajuan/images/logo.png"
                 alt="Logo"
                 onerror="this.style.display='none';this.nextElementSibling.style.display='block'">
            <i class="fas fa-landmark" style="display:none;"></i>
        </div>
        <h1>Desa Pakning Asal</h1>
        <p>Sistem Administrasi Surat-Menyurat</p>
    </div>

    <!-- Card -->
    <div class="login-card">

        <span class="admin-badge">
            <i class="fas fa-shield-alt"></i> Panel Admin
        </span>

        <h2>Login Admin</h2>
        <p class="subtitle">Masukkan kredensial Anda untuk mengakses panel administrasi.</p>

        <?php if ($flashError): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <ul style="margin:0;padding-left:18px;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars(is_array($e) ? $e[0] : $e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="/web-pengajuan/admin/login" id="formLogin">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">

            <div class="form-group">
                <label for="email">Email Admin</label>
                <div class="input-wrap">
                    <i class="fas fa-envelope icon-left"></i>
                    <input type="email"
                           id="email"
                           name="email"
                           placeholder="Masukkan email admin"
                           value="<?= htmlspecialchars($oldEmail) ?>"
                           autocomplete="email"
                           required>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <i class="fas fa-lock icon-left"></i>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Masukkan password"
                           autocomplete="current-password"
                           required>
                    <button type="button" class="toggle-pw" id="togglePw" title="Tampilkan password">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                <i class="fas fa-sign-in-alt"></i>
                Masuk ke Panel Admin
            </button>
        </form>
    </div>

    <div class="back-link">
        <a href="/web-pengajuan/dashboard">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>
    </div>

</div>

<script>
    // Toggle show/hide password
    const togglePw = document.getElementById('togglePw');
    const pwInput  = document.getElementById('password');
    const eyeIcon  = document.getElementById('eyeIcon');

    togglePw.addEventListener('click', function () {
        const isHidden = pwInput.type === 'password';
        pwInput.type   = isHidden ? 'text' : 'password';
        eyeIcon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
    });

    // Disable tombol saat submit
    document.getElementById('formLogin').addEventListener('submit', function () {
        const btn = document.getElementById('btnLogin');
        btn.disabled  = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memverifikasi...';
    });
</script>

</body>
</html>