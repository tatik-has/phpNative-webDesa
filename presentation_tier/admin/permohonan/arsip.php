<?php
/**
 * PRESENTATION TIER - Halaman Arsip Admin
 * Pengganti: presentation_tier/admin/permohonan/arsip.blade.php
 */

$pageTitle = 'Arsip Permohonan Surat';
$extraCss  = ['/presentation_tier/css/admin/admin-permohonan.css'];

ob_start(); ?>

<div class="main-container">
    <div class="page-header">
        <h1>Arsip Permohonan Surat</h1>
        <p>Permohonan yang telah diarsipkan (Selesai/Ditolak &gt; 15 hari)</p>
    </div>

    <div style="margin-bottom:20px;">
        <form action="/web-pengajuan/admin/run-auto-archive" method="POST" style="display:inline;">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">
            <button type="submit" class="btn btn-primary"
                onclick="return confirm('Jalankan arsip otomatis?')"
                style="background:linear-gradient(135deg,#007bff,#0056b3);color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-size:14px;font-weight:500;">
                Jalankan Arsip Otomatis
            </button>
        </form>
    </div>

    <div class="tab-buttons">
        <button class="tab-btn active" data-tab="domisili">Keterangan Domisili</button>
        <button class="tab-btn" data-tab="sku">Keterangan Usaha (SKU)</button>
        <button class="tab-btn" data-tab="ktm">Permohonan KTM</button>
    </div>

    <div class="tab-content">
        <div id="domisili" class="tab-pane active">
            <?php $permohonans = $domisili ?? []; $type = 'domisili';
            require __DIR__ . '/../partials/_tabel_arsip.php'; ?>
        </div>
        <div id="sku" class="tab-pane">
            <?php $permohonans = $sku ?? []; $type = 'sku';
            require __DIR__ . '/../partials/_tabel_arsip.php'; ?>
        </div>
        <div id="ktm" class="tab-pane">
            <?php $permohonans = $ktm ?? []; $type = 'ktm';
            require __DIR__ . '/../partials/_tabel_arsip.php'; ?>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();

ob_start(); ?>
<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        document.getElementById(btn.dataset.tab)?.classList.add('active');
    });
});
</script>
<?php $scripts = ob_get_clean();

require __DIR__ . '/../partials/layout.php';