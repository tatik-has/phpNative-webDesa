<?php

require_once __DIR__ . '/Router.php';
require_once __DIR__ . '/../keamanan/ValidasiLogin.php';

// ── Controllers Admin ──
require_once __DIR__ . '/../controllers/admin/AdminAuthController.php';
require_once __DIR__ . '/../controllers/admin/AdminController.php';
require_once __DIR__ . '/../controllers/admin/AdminDashboardController.php';
require_once __DIR__ . '/../controllers/admin/AdminLaporanController.php';
require_once __DIR__ . '/../controllers/admin/AdminProfileController.php';
require_once __DIR__ . '/../controllers/admin/AdminPengaturanController.php'; 
require_once __DIR__ . '/../controllers/admin/AdminNikController.php';

// ── Controllers Masyarakat ──
require_once __DIR__ . '/../controllers/masyarakat/MasyarakatDashboardController.php';
require_once __DIR__ . '/../controllers/masyarakat/PengajuanSuratController.php';
require_once __DIR__ . '/../controllers/masyarakat/DomisiliController.php';
require_once __DIR__ . '/../controllers/masyarakat/SKTMController.php';
require_once __DIR__ . '/../controllers/masyarakat/SKUController.php';
require_once __DIR__ . '/../controllers/masyarakat/RiwayatSuratController.php';
require_once __DIR__ . '/../controllers/masyarakat/MasyarakatNotificationController.php';
require_once __DIR__ . '/../controllers/masyarakat/MasyarakatAuthController.php';


// ── Controllers Shared ──
require_once __DIR__ . '/../controllers/shared/NotificationController.php';

// ============================================================
// DEFAULT REDIRECT
// ============================================================
Router::get('/', function () {
    header('Location: /web-pengajuan/dashboard');
    exit;
});

// ============================================================
// MASYARAKAT — AKSES PUBLIK (tanpa login)
// ============================================================
Router::get('/dashboard', [MasyarakatDashboardController::class, 'index']);
Router::get('/riwayat',   [RiwayatSuratController::class, 'index']);
Router::get('/faq', function () {
    require_once __DIR__ . '/../../presentation_tier/masyarakat/faq/faq.php';
});

Router::get('/login',  [MasyarakatAuthController::class, 'showLogin']);
Router::post('/login', [MasyarakatAuthController::class, 'login']);
Router::post('/logout',[MasyarakatAuthController::class, 'logout']);


// Pengajuan surat
Router::get('/pengajuan',          [PengajuanSuratController::class, 'showPengajuanForm']);
Router::get('/pengajuan/domisili', [DomisiliController::class, 'showForm']);
Router::post('/pengajuan/domisili',[DomisiliController::class, 'store']);
Router::get('/pengajuan/sktm',    [SKTMController::class, 'create']);
Router::post('/pengajuan/sktm',   [SKTMController::class, 'store']);
Router::get('/pengajuan/sku',     [SKUController::class, 'create']);
Router::post('/pengajuan/sku',    [SKUController::class, 'store']);

// Notifikasi Masyarakat
Router::get('/notifications',                [MasyarakatNotificationController::class, 'index']);
Router::delete('/notifications/delete-all',  [MasyarakatNotificationController::class, 'deleteAll']);
Router::delete('/notifications/{id}/delete', [MasyarakatNotificationController::class, 'delete']);


// ============================================================
// ADMIN — LOGIN (publik)
// ============================================================
Router::get('/admin/login',   [AdminAuthController::class, 'showLogin']);
Router::post('/admin/login',  [AdminAuthController::class, 'login']);
Router::post('/admin/logout', [AdminAuthController::class, 'logout']);

// ============================================================
// ADMIN — PROTECTED (wajib login, dicek di dalam controller)
// ============================================================
Router::get('/admin/dashboard',        [AdminDashboardController::class, 'index']);
Router::get('/admin/surat',            [AdminController::class, 'showPermohonanSurat']);
Router::get('/admin/semua-permohonan', [AdminController::class, 'semuaPermohonan']);

// Update status permohonan
Router::post('/admin/surat/{type}/{id}/update-status', [AdminController::class, 'updateStatusPermohonan']);

// Template & generate surat (FITUR BARU — menggantikan kirim-surat lama)
Router::get('/admin/surat/{type}/{id}/template',        [AdminController::class, 'showTemplateSurat']);
Router::post('/admin/surat/{type}/{id}/generate-surat', [AdminController::class, 'generateSurat']);
Router::get('/admin/surat/{type}/{id}/print',           [AdminController::class, 'printSurat']);

// Detail surat per jenis
Router::get('/admin/domisili/{id}', [AdminController::class, 'showDomisiliDetail']);
Router::get('/admin/sku/{id}',      [AdminController::class, 'showSkuDetail']);
Router::get('/admin/ktm/{id}',      [AdminController::class, 'showKtmDetail']);

// Laporan & Arsip
Router::get('/admin/laporan',                             [AdminLaporanController::class, 'showLaporan']);
Router::get('/admin/arsip',                               [AdminLaporanController::class, 'showArsip']);
Router::post('/admin/surat/{type}/{id}/archive',          [AdminLaporanController::class, 'archivePermohonan']);
Router::delete('/admin/surat/{type}/{id}/delete',         [AdminLaporanController::class, 'destroyPermanently']);
Router::post('/admin/run-auto-archive',                   [AdminLaporanController::class, 'runAutoArchive']);

// Profile admin
Router::get('/admin/profile', [AdminProfileController::class, 'show']);

// Notifikasi admin (JSON API — dipanggil AJAX dari layout)
Router::get('/admin/notifications/unread',        [NotificationController::class, 'getUnread']);
Router::post('/admin/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);

// Pengaturan TTD Kepala Desa — FIX: ganti $router-> menjadi Router::
Router::get('/admin/pengaturan/ttd',          [AdminPengaturanController::class, 'showTtdPage']);
Router::post('/admin/pengaturan/ttd/upload',  [AdminPengaturanController::class, 'uploadTtd']);
Router::post('/admin/pengaturan/ttd/nama',    [AdminPengaturanController::class, 'simpanNamaKades']);

// Kelola NIK Warga
Router::get('/admin/nik',                        [AdminNikController::class, 'index']);
Router::post('/admin/nik/store',                 [AdminNikController::class, 'store']);
Router::post('/admin/nik/import',                [AdminNikController::class, 'importExcel']);
Router::get('/admin/nik/template',               [AdminNikController::class, 'downloadTemplate']);
Router::delete('/admin/nik/{id}/delete',         [AdminNikController::class, 'destroy']);
