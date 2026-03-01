<!DOCTYPE html>
<html lang="id">
<base href="/web-pengajuan/">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Sistem Surat Desa - Pakning Asal') ?></title>

    <link rel="stylesheet" href="/web-pengajuan/presentation_tier/css/masyarakat/dashboard.css">
    <link rel="stylesheet" href="/web-pengajuan/presentation_tier/css/layout.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <?php foreach ($extraCss ?? [] as $css): ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($css) ?>">
    <?php endforeach; ?>

    <!-- [BARU] Style untuk tombol login/logout di navbar -->
    <style>
        .btn-nav-login {
            background: rgba(255,255,255,.15) !important;
            border: 1.5px solid rgba(255,255,255,.4) !important;
            border-radius: 8px !important;
            padding: 7px 14px !important;
            color: #fff !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            transition: background .2s !important;
            text-decoration: none !important;
        }
        .btn-nav-login:hover { background: rgba(255,255,255,.25) !important; }

        .nav-user-info {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(255,255,255,.1);
            border-radius: 8px;
            padding: 4px 6px 4px 12px;
            border: 1px solid rgba(255,255,255,.2);
        }
        .nav-user-name {
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
            max-width: 140px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .btn-nav-logout {
            background: rgba(231,76,60,.8);
            border: none;
            border-radius: 6px;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 10px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: background .2s;
            font-family: inherit;
        }
        .btn-nav-logout:hover { background: #e74c3c; }

        @media (max-width: 767px) {
            .nav-user-info {
                width: 100%;
                justify-content: space-between;
                border-radius: 10px;
                padding: 10px 14px;
                margin-top: 4px;
            }
            .nav-user-name { max-width: none; }
            .btn-nav-login {
                width: 100% !important;
                justify-content: center !important;
                margin-top: 4px !important;
                padding: 11px 14px !important;
            }
            .btn-nav-logout { padding: 8px 14px; font-size: 13px; }
        }
    </style>
</head>

<body>

    <?php
    if (session_status() === PHP_SESSION_NONE) session_start();

    // Flash messages
    $flashSuccess = $_SESSION['success'] ?? null;
    $flashError   = $_SESSION['error']   ?? null;
    $errors       = $_SESSION['errors']  ?? [];
    unset($_SESSION['success'], $_SESSION['error'], $_SESSION['errors']);

    // Active nav helper
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    function isActiveNav(string $pattern): string
    {
        global $currentPath;
        return str_starts_with($currentPath, $pattern) ? 'active' : '';
    }

    // Hitung unread badge:
    // - Jika controller sudah inject $unreadCount (halaman notifikasi), pakai itu
    // - Jika tidak, hitung langsung dari DB agar badge muncul di semua halaman
    if (!isset($unreadCount)) {
        $unreadCount = 0;
        $nik = $_SESSION['nik_pemohon'] ?? null;
        if ($nik) {
            try {
                $dbForBadge = Database::getInstance();
                $stmtBadge  = $dbForBadge->prepare("
                    SELECT COUNT(*) FROM notifications
                    WHERE notifiable_type = 'Masyarakat'
                      AND notifiable_id   = ?
                      AND read_at IS NULL
                ");
                $stmtBadge->execute([$nik]);
                $unreadCount = (int)$stmtBadge->fetchColumn();
            } catch (Exception $e) {
                $unreadCount = 0;
            }
        }
    }
    ?>

    <!-- Floating Alert -->
    <div class="alert-wrapper">
        <?php if ($flashSuccess): ?>
            <div class="custom-alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?= htmlspecialchars($flashSuccess) ?></span>
            </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
            <div class="custom-alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars($flashError) ?></span>
            </div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="custom-alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?= htmlspecialchars(
                    is_array($errors)
                        ? implode(' | ', array_map(fn($e) => is_array($e) ? $e[0] : $e, $errors))
                        : $errors
                ) ?></span>
            </div>
        <?php endif; ?>
    </div>

    <!-- NAVBAR -->
    <nav class="navbar">
        <div class="navbar-left">
            <img src="/web-pengajuan/images/logo.png" alt="Logo Desa Pakning Asal">
        </div>

        <div class="hamburger-menu" id="hamburgerMenu">
            <span></span><span></span><span></span>
        </div>
        <div class="mobile-menu-overlay" id="mobileOverlay"></div>

        <div class="navbar-right" id="navbarMenu">
            <a href="/web-pengajuan/dashboard" class="<?= isActiveNav('/web-pengajuan/dashboard') ?>">
                <i class="fas fa-home"></i><span class="menu-text">Home</span>
            </a>
            <a href="/web-pengajuan/pengajuan" class="<?= isActiveNav('/web-pengajuan/pengajuan') ?>">
                <i class="fas fa-file-alt"></i><span class="menu-text">Pengajuan</span>
            </a>
            <a href="/web-pengajuan/riwayat" class="<?= isActiveNav('/web-pengajuan/riwayat') ?>">
                <i class="fas fa-history"></i><span class="menu-text">Riwayat</span>
            </a>

            <a href="/web-pengajuan/notifications"
               class="<?= isActiveNav('/web-pengajuan/notifications') ?>"
               style="position:relative;">
                <i class="fas fa-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span class="notif-badge-nav">
                        <?= $unreadCount > 99 ? '99+' : $unreadCount ?>
                    </span>
                <?php endif; ?>
                <span class="menu-text">Notifikasi</span>
            </a>

            <a href="/web-pengajuan/faq"
               class="<?= str_contains($currentPath, '/faq') ? 'active' : '' ?>">
                <i class="fas fa-question-circle"></i><span class="menu-text">FAQ</span>
            </a>

            <!-- [BARU] LOGIN / PROFIL WARGA -->
            <?php if (!empty($_SESSION['nik_pemohon'])): ?>
                <div class="nav-user-info">
                    <span class="nav-user-name">
                        <i class="fas fa-user-circle"></i>
                        <span class="menu-text">
                            <?= htmlspecialchars($_SESSION['nama_pemohon'] ?? 'Warga') ?>
                        </span>
                    </span>
                    <form method="POST" action="/web-pengajuan/logout" style="display:inline;">
                        <button type="submit" class="btn-nav-logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="menu-text">Keluar</span>
                        </button>
                    </form>
                </div>
            <?php else: ?>
                <a href="/web-pengajuan/login"
                   class="btn-nav-login <?= isActiveNav('/web-pengajuan/login') ?>">
                    <i class="fas fa-sign-in-alt"></i>
                    <span class="menu-text">Masuk</span>
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- KONTEN -->
    <div class="container">
        <?= $content ?? '' ?>
    </div>

    <!-- FOOTER -->
    <footer class="main-footer">
        <p>&copy; <?= date('Y') ?> Hastita Sari. All Rights Reserved.</p>
        <small>Sistem Administrasi Surat-Menyurat Desa Pakning Asal</small>
    </footer>

    <script>
        // Auto dismiss alert setelah 5 detik
        setTimeout(function() {
            document.querySelectorAll('.custom-alert').forEach(a => {
                a.style.transition = 'opacity .6s ease, transform .6s ease';
                a.style.opacity    = '0';
                a.style.transform  = 'translateX(120%)';
                setTimeout(() => a.remove(), 600);
            });
        }, 5000);

        // Klik untuk dismiss alert
        document.querySelectorAll('.custom-alert').forEach(a => {
            a.style.cursor = 'pointer';
            a.addEventListener('click', function() {
                this.style.transition = 'opacity .3s ease, transform .3s ease';
                this.style.opacity    = '0';
                this.style.transform  = 'translateX(120%)';
                setTimeout(() => this.remove(), 300);
            });
        });

        // Hamburger menu mobile
        const hamburger = document.getElementById('hamburgerMenu');
        const navMenu   = document.getElementById('navbarMenu');
        const overlay   = document.getElementById('mobileOverlay');

        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            navMenu.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = navMenu.classList.contains('active') ? 'hidden' : '';
        });

        overlay.addEventListener('click', () => {
            [hamburger, navMenu, overlay].forEach(el => el.classList.remove('active'));
            document.body.style.overflow = '';
        });

        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 767) {
                    [hamburger, navMenu, overlay].forEach(el => el.classList.remove('active'));
                    document.body.style.overflow = '';
                }
            });
        });
    </script>

    <?php foreach ($extraJs ?? [] as $js): ?>
        <script src="<?= htmlspecialchars($js) ?>"></script>
    <?php endforeach; ?>

    <?= $scripts ?? '' ?>
</body>

</html>