<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Admin Panel') ?> - Desa Pakning Asal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/web-pengajuan/presentation_tier/css/admin/admin.css">
    <?php if (!empty($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="/web-pengajuan<?= htmlspecialchars($css) ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>

    <div class="admin-container">

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="/web-pengajuan/images/logo.png" alt="Logo Desa"
                    onerror="this.style.display='none'">
                <h3>Desa Pakning Asal</h3>
            </div>

            <nav class="sidebar-nav">
                <a href="/web-pengajuan/admin/dashboard"
                    class="<?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Dashboard
                </a>
                <a href="/web-pengajuan/admin/surat"
                    class="<?= (str_contains($_SERVER['REQUEST_URI'], '/surat') || str_contains($_SERVER['REQUEST_URI'], 'domisili') || str_contains($_SERVER['REQUEST_URI'], 'sku') || str_contains($_SERVER['REQUEST_URI'], 'ktm')) ? 'active' : '' ?>">
                    <i class="fas fa-inbox"></i> Permohonan Surat
                </a>
                <a href="/web-pengajuan/admin/laporan"
                    class="<?= str_contains($_SERVER['REQUEST_URI'], 'laporan') ? 'active' : '' ?>">
                    <i class="fas fa-chart-line"></i> Laporan
                </a>
                <a href="/web-pengajuan/admin/arsip"
                    class="<?= str_contains($_SERVER['REQUEST_URI'], 'arsip') ? 'active' : '' ?>">
                    <i class="fas fa-archive"></i> Arsip
                </a>
                <a href="/web-pengajuan/admin/profile"
                    class="<?= str_contains($_SERVER['REQUEST_URI'], 'profile') ? 'active' : '' ?>">
                    <i class="fas fa-user"></i> Profil
                </a>
                <a href="/web-pengajuan/admin/pengaturan/ttd"
                    class="<?= str_contains($_SERVER['REQUEST_URI'], 'pengaturan') ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Pengaturan TTD
                </a>
                <a href="/web-pengajuan/admin/nik"
                    class="<?= str_contains($_SERVER['REQUEST_URI'], '/admin/nik') ? 'active' : '' ?>">
                    <i class="fas fa-id-card"></i> Kelola NIK Warga
                </a>
            </nav>

            <div class="sidebar-logout">
                <form method="POST" action="/web-pengajuan/admin/logout">
                    <button type="submit" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            </div>
        </aside>

        <!-- MAIN CONTENT -->
        <div class="main-content">

            <!-- Top Bar -->
            <div class="top-bar">
                <div class="top-bar-left">
                    <h2><?= htmlspecialchars($pageTitle ?? 'Admin Panel') ?></h2>
                </div>
                <div class="top-bar-right">

                    <!-- Notifikasi -->
                    <div class="notification-wrapper">
                        <span class="icon-btn" id="notifBtn">
                            <i class="fas fa-bell"></i>
                            <span class="notification-count" id="notifBadge">0</span>
                        </span>
                        <div class="notification-dropdown" id="notifDropdown">
                            <div class="notification-header">Notifikasi</div>
                            <div class="notification-list" id="notifList">
                                <p style="padding:15px;color:#999;text-align:center;">Tidak ada notifikasi</p>
                            </div>
                        </div>
                    </div>

                    <span class="divider">|</span>

                    <a href="/web-pengajuan/admin/profile" class="profile-link">
                        <i class="fas fa-user-circle"></i>
                        <span><?= htmlspecialchars($admin['nama'] ?? 'Admin') ?></span>
                    </a>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success" style="margin: 16px 24px 0;">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-error" style="margin: 16px 24px 0;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Page Content -->
            <div class="page-content">
                <?= $content ?? '' ?>
            </div>

            <!-- Footer -->
            <footer class="admin-footer">
                &copy; <?= date('Y') ?> Sistem Informasi Desa Pakning Asal. All rights reserved.
            </footer>

        </div><!-- /.main-content -->
    </div><!-- /.admin-container -->

    <script>
        // ── Toggle dropdown notifikasi ──
        document.getElementById('notifBtn')?.addEventListener('click', function(e) {
            e.stopPropagation();
            const dd = document.getElementById('notifDropdown');
            const isOpen = dd.style.display === 'block';
            dd.style.display = isOpen ? 'none' : 'block';

            // Jika dibuka, mark as read
            if (!isOpen) {
                fetch('/web-pengajuan/admin/notifications/mark-as-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: '_token=<?= session_id() ?>'
                }).then(() => {
                    document.getElementById('notifBadge').style.display = 'none';
                }).catch(() => {});
            }
        });

        document.addEventListener('click', function() {
            const dd = document.getElementById('notifDropdown');
            if (dd) dd.style.display = 'none';
        });

        document.getElementById('notifDropdown')?.addEventListener('click', function(e) {
            e.stopPropagation();
        });

        // ── Fetch notifikasi — sesuai format {count, notifications} dari NotificationController ──
        async function fetchNotifikasi() {
            try {
                const res = await fetch('/web-pengajuan/admin/notifications/unread');
                const json = await res.json();

                const badge = document.getElementById('notifBadge');
                const list = document.getElementById('notifList');

                // Format dari NotificationController: { count: N, notifications: [...] }
                const count = json.count ?? 0;
                const notifications = json.notifications ?? [];

                if (count > 0) {
                    badge.style.display = 'flex';
                    badge.textContent = count;

                    list.innerHTML = notifications.map(n => {
                        const data = n.data ?? {};
                        const pesan = data.pesan ?? 'Notifikasi baru';
                        const waktu = n.created_at ?? '';
                        return `
                        <div class="notification-item">
                            <p class="message">${pesan}</p>
                            <p class="timestamp">${waktu}</p>
                        </div>`;
                    }).join('');

                } else {
                    badge.style.display = 'none';
                    list.innerHTML = '<p style="padding:15px;color:#999;text-align:center;">Tidak ada notifikasi</p>';
                }

            } catch (e) {
                // Silent fail — jangan tampilkan error ke user
            }
        }

        fetchNotifikasi();
        setInterval(fetchNotifikasi, 30000); // Polling tiap 30 detik
    </script>

    <?= $scripts ?? '' ?>

</body>

</html>