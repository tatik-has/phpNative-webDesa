<?php

/**
 * PRESENTATION TIER - Admin Profile
 * Pengganti: presentation_tier/admin/profile/show.blade.php
 */

$pageTitle = 'Profil Admin';
$extraCss  = ['/presentation_tier/css/admin/admin-profile.css'];

ob_start(); ?>

<div class="profile-container">
    <div class="profile-header">
        <div class="header-content">
            <div class="profile-avatar">
                <div class="avatar-circle"><i class="fa-solid fa-user"></i></div>
            </div>
            <div class="header-text">
                <h2>Profil Saya</h2>
                <p class="subtitle">Informasi akun Anda</p>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="card-header">
            <i class="fa-solid fa-user-circle"></i>
            <h3>Informasi Dasar</h3>
        </div>
        <div class="card-body">
            <div class="info-item">
                <div class="info-label"><i class="fa-solid fa-user"></i><span>Nama Lengkap</span></div>
                <div class="info-value"><?= htmlspecialchars($admin['nama'] ?? '-') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fa-solid fa-envelope"></i><span>Email</span></div>
                <div class="info-value"><?= htmlspecialchars($admin['email'] ?? '-') ?></div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fa-solid fa-shield-halved"></i><span>Role</span></div>
                <div class="info-value">
                    <span class="badge badge-role"><?= htmlspecialchars(ucfirst($admin['role'] ?? 'admin')) ?></span>
                </div>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fa-solid fa-calendar-days"></i><span>Terdaftar Sejak</span></div>
                <div class="info-value">
                    <?= !empty($admin['created_at']) ? date('d F Y', strtotime($admin['created_at'])) : '-' ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-card">
        <div class="card-header"><i class="fa-solid fa-lock"></i>
            <h3>Keamanan</h3>
        </div>
        <div class="card-body">
            <div class="info-box">
                <i class="fa-solid fa-info-circle"></i>
                <p>Untuk mengubah password atau informasi akun, silakan hubungi superadmin sistem.</p>
            </div>
            <div class="info-item">
                <div class="info-label"><i class="fa-solid fa-key"></i><span>Password</span></div>
                <div class="info-value">••••••••</div>
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="/web-pengajuan/admin/dashboard" class="btn btn-primary">
            <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<?php $content = ob_get_clean();
require __DIR__ . '/../../admin/partials/layout.php';
