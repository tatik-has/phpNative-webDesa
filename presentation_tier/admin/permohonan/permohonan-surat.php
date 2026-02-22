<?php
/**
 * PRESENTATION TIER - Daftar Permohonan Surat (Tab per jenis)
 * Pengganti: presentation_tier/admin/permohonan/permohonan-surat.blade.php
 */

$pageTitle = 'Manajemen Permohonan Surat';
$extraCss  = ['/presentation_tier/css/admin/admin-permohonan.css'];

ob_start(); ?>

<div class="main-container">
    <div class="page-header">
        <h1>Manajemen Permohonan Surat</h1>
        <p>Pilih jenis surat untuk melihat dan mengelola permohonan yang masuk.</p>
    </div>

    <div class="tab-buttons">
        <button class="tab-btn active" data-tab="domisili">Keterangan Domisili</button>
        <button class="tab-btn" data-tab="sku">Keterangan Usaha (SKU)</button>
        <button class="tab-btn" data-tab="ktm">Permohonan KTM</button>
    </div>

    <div class="tab-content">
        <div id="domisili" class="tab-pane active">
            <?php foreach (['Diproses','Diterima','Selesai','Ditolak'] as $s):
                $permohonans = $domisiliGrouped[$s] ?? [];
                $title = match($s) { 'Diproses'=>'Permohonan Diproses','Diterima'=>'Permohonan Diterima (Sedang Dibuat)','Selesai'=>'Permohonan Selesai','Ditolak'=>'Permohonan Ditolak' };
                $type = 'domisili';
                require __DIR__ . '/../partials/_tabel_permohonan.php';
            endforeach; ?>
        </div>
        <div id="sku" class="tab-pane">
            <?php foreach (['Diproses','Diterima','Selesai','Ditolak'] as $s):
                $permohonans = $skuGrouped[$s] ?? [];
                $title = match($s) { 'Diproses'=>'Permohonan Diproses','Diterima'=>'Permohonan Diterima (Sedang Dibuat)','Selesai'=>'Permohonan Selesai','Ditolak'=>'Permohonan Ditolak' };
                $type = 'sku';
                require __DIR__ . '/../partials/_tabel_permohonan.php';
            endforeach; ?>
        </div>
        <div id="ktm" class="tab-pane">
            <?php foreach (['Diproses','Diterima','Selesai','Ditolak'] as $s):
                $permohonans = $ktmGrouped[$s] ?? [];
                $title = match($s) { 'Diproses'=>'Permohonan Diproses','Diterima'=>'Permohonan Diterima (Sedang Dibuat)','Selesai'=>'Permohonan Selesai','Ditolak'=>'Permohonan Ditolak' };
                $type = 'ktm';
                require __DIR__ . '/../partials/_tabel_permohonan.php';
            endforeach; ?>
        </div>
    </div>
</div>

<!-- MODAL PENOLAKAN -->
<div id="tolakModal" class="modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;justify-content:center;align-items:center;">
    <div class="modal-content" style="background:#fff;border-radius:10px;width:450px;max-width:90%;overflow:hidden;">
        <div class="modal-header" style="padding:16px 20px;background:#e74c3c;color:#fff;display:flex;justify-content:space-between;align-items:center;">
            <h3 style="margin:0;">Alasan Penolakan</h3>
            <span style="cursor:pointer;font-size:22px;" onclick="closeTolakModal()">&times;</span>
        </div>
        <div class="modal-body" style="padding:20px;">
            <form id="formTolak" action="#" method="POST">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">
                <input type="hidden" name="status" value="Ditolak">
                <textarea name="keterangan_penolakan" placeholder="Tuliskan alasan penolakan di sini..." required
                    style="width:100%;min-height:100px;padding:10px;border:1px solid #ddd;border-radius:6px;resize:vertical;"></textarea>
                <div style="text-align:right;margin-top:12px;">
                    <button type="submit" class="btn btn-submit-tolak" style="background:#e74c3c;color:#fff;padding:8px 20px;border:none;border-radius:6px;cursor:pointer;">
                        Kirim Penolakan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $content = ob_get_clean();

ob_start(); ?>
<script>
    // Tab switching
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(btn.dataset.tab)?.classList.add('active');
        });
    });
    // Modal tolak
    const tolakModal = document.getElementById('tolakModal');
    const formTolak  = document.getElementById('formTolak');
    function openTolakModal(url) { formTolak.action = url; tolakModal.style.display = 'flex'; }
    function closeTolakModal()   { tolakModal.style.display = 'none'; formTolak.reset(); }
    window.addEventListener('click', e => { if (e.target === tolakModal) closeTolakModal(); });
</script>
<?php $scripts = ob_get_clean();

require __DIR__ . '/../partials/layout.php';