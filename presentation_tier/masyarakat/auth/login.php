<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Desa Pakning Asal</title>
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

        /* Background pattern */
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

        /* Header desa */
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

        /* Card */
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

        /* Alert error */
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

        /* Form */
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: #374151; margin-bottom: 7px;
        }

        .input-wrap {
            position: relative;
        }
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
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            color: #1a2e45;
            letter-spacing: 1px;
            transition: border-color .2s, box-shadow .2s;
        }
        .input-wrap input:focus {
            outline: none;
            border-color: #2d6a9f;
            box-shadow: 0 0 0 3px rgba(45,106,159,.12);
        }
        .input-wrap input::placeholder {
            color: #c4c9d4; letter-spacing: 0;
        }

        .input-hint {
            font-size: 11px; color: #aaa;
            margin-top: 5px;
        }

        /* NIK counter */
        .nik-counter {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            font-size: 11px; color: #aaa;
            pointer-events: none;
            font-variant-numeric: tabular-nums;
        }
        .nik-counter.done { color: #27ae60; }

        /* Submit button */
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
        }
        .btn-login:hover  { opacity: .92; }
        .btn-login:active { transform: scale(.98); }

        /* Info box */
        .info-box {
            background: #f0f7ff;
            border: 1px solid #bcd7f5;
            border-radius: 10px;
            padding: 14px 16px;
            margin-top: 20px;
            font-size: 12px;
            color: #2c5f8a;
            line-height: 1.6;
        }
        .info-box i { color: #2d6a9f; margin-right: 4px; }

        /* Link kembali */
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

<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>

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
        <h2>Masuk dengan NIK</h2>
        <p class="subtitle">
            Gunakan Nomor Induk Kependudukan (NIK) yang terdapat pada KTP Anda untuk mengakses layanan.
        </p>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($_SESSION['error']) ?></span>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="/web-pengajuan/login" method="POST" id="formLogin">

            <div class="form-group">
                <label for="nik">NIK (Nomor Induk Kependudukan)</label>
                <div class="input-wrap">
                    <i class="fas fa-id-card icon-left"></i>
                    <input type="text"
                           id="nik"
                           name="nik"
                           maxlength="16"
                           inputmode="numeric"
                           placeholder="Masukkan 16 digit NIK Anda"
                           autocomplete="off"
                           required>
                    <span class="nik-counter" id="nikCounter">0/16</span>
                </div>
                <div class="input-hint">Contoh: 1401234567890001</div>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                <i class="fas fa-sign-in-alt"></i>
                Masuk ke Sistem
            </button>

        </form>

        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <strong>Belum bisa login?</strong><br>
            NIK Anda perlu didaftarkan terlebih dahulu oleh petugas desa.
            Silakan kunjungi <strong>Kantor Desa Pakning Asal</strong> atau hubungi petugas untuk mendaftarkan NIK Anda.
        </div>
    </div>

    <div class="back-link">
        <a href="/web-pengajuan/dashboard">
            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
        </a>
    </div>

</div>

<script>
    const nikInput   = document.getElementById('nik');
    const nikCounter = document.getElementById('nikCounter');
    const btnLogin   = document.getElementById('btnLogin');

    // Hanya izinkan angka + update counter
    nikInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').substring(0, 16);
        const len  = this.value.length;
        nikCounter.textContent   = len + '/16';
        nikCounter.className     = 'nik-counter' + (len === 16 ? ' done' : '');
    });

    // Cegah paste non-angka
    nikInput.addEventListener('paste', function (e) {
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text');
        const digits = text.replace(/\D/g, '').substring(0, 16);
        this.value = digits;
        this.dispatchEvent(new Event('input'));
    });

    // Disable tombol saat submit untuk cegah double-click
    document.getElementById('formLogin').addEventListener('submit', function () {
        btnLogin.disabled    = true;
        btnLogin.innerHTML   = '<i class="fas fa-spinner fa-spin"></i> Memverifikasi...';
    });
</script>

</body>
</html>