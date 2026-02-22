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
</head>

<body>

    <?php
    if (session_status() === PHP_SESSION_NONE) session_start();
    $flashSuccess = $_SESSION['success'] ?? null;
    $flashError   = $_SESSION['error']   ?? null;
    $errors       = $_SESSION['errors']  ?? [];
    unset($_SESSION['success'], $_SESSION['error'], $_SESSION['errors']);

    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    function isActiveNav(string $pattern): string
    {
        global $currentPath;
        return str_starts_with($currentPath, $pattern) ? 'active' : '';
    }

    // FIX: Hitung notifikasi belum dibaca berdasarkan NIK session
    $unreadCount = 0;
    $nikPemohon  = $_SESSION['nik_pemohon'] ?? null;
    if ($nikPemohon) {
        try {
            require_once $_SERVER['DOCUMENT_ROOT'] . '/web-pengajuan/data_tier/config/database.php';
            $db   = Database::getInstance();
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM notifications
                WHERE notifiable_type = 'Masyarakat'
                  AND notifiable_id   = ?
                  AND read_at IS NULL
            ");
            $stmt->execute([$nikPemohon]);
            $unreadCount = (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            $unreadCount = 0;
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
                <span><?= htmlspecialchars(is_array($errors) ? implode(' | ', array_map(fn($e) => is_array($e) ? $e[0] : $e, $errors)) : $errors) ?></span>
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

            <!-- FIX: Bell dengan badge unread count -->
            <a href="/web-pengajuan/notifications" class="<?= isActiveNav('/web-pengajuan/notifications') ?>" style="position:relative;">
                <i class="fas fa-bell"></i>
                <?php if ($unreadCount > 0): ?>
                    <span style="
                        position:absolute;
                        top:-6px;
                        right:-6px;
                        background:#e74c3c;
                        color:#fff;
                        font-size:10px;
                        font-weight:700;
                        min-width:18px;
                        height:18px;
                        border-radius:50%;
                        display:flex;
                        align-items:center;
                        justify-content:center;
                        padding:0 4px;
                        line-height:1;
                    "><?= $unreadCount > 99 ? '99+' : $unreadCount ?></span>
                <?php endif; ?>
                <span class="menu-text">Notifikasi</span>
            </a>

            <a href="/web-pengajuan/faq" class="<?= str_contains($currentPath, '/faq') ? 'active' : '' ?>">
                <i class="fas fa-question-circle"></i><span class="menu-text">FAQ</span>
            </a>
        </div>
    </nav>

    <!-- KONTEN -->
    <div class="container">
        <?= $content ?? '' ?>
    </div>

    <!-- FOOTER -->
    <footer class="main-footer">
        <p>&copy; 2026 Hastita Sari. All Rights Reserved.</p>
        <small>Sistem Administrasi Surat-Menyurat Desa Pakning Asal</small>
    </footer>

    <script>
        setTimeout(function() {
            document.querySelectorAll('.custom-alert').forEach(a => {
                a.style.transition = 'opacity .6s ease, transform .6s ease';
                a.style.opacity = '0';
                a.style.transform = 'translateX(120%)';
                setTimeout(() => a.remove(), 600);
            });
        }, 5000);
        document.querySelectorAll('.custom-alert').forEach(a => {
            a.style.cursor = 'pointer';
            a.addEventListener('click', function() {
                this.style.transition = 'opacity .3s ease, transform .3s ease';
                this.style.opacity = '0';
                this.style.transform = 'translateX(120%)';
                setTimeout(() => this.remove(), 300);
            });
        });
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