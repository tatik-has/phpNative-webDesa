<?php
$pageTitle = 'Pengaturan Tanda Tangan';
$extraCss  = [];
ob_start();
?>

<div class="container-detail mt-4">
    <div class="card-detail shadow-sm border-0">
        <div class="card-detail-header">
            <div class="header-text">
                <h5 class="mb-0">Pengaturan Tanda Tangan Kepala Desa</h5>
                <p class="header-subtitle">Upload tanda tangan akan otomatis muncul di semua surat</p>
            </div>
        </div>
        <div class="card-detail-body px-5 py-4">

            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?><?php unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['error']) ?><?php unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <!-- Preview TTD saat ini -->
            <div style="margin-bottom:30px;">
                <h6>Tanda Tangan Saat Ini:</h6>
                <?php if (!empty($ttdAda)): ?>
                    <div style="border:1px solid #ddd;padding:16px;display:inline-block;border-radius:8px;background:#fafafa;">
                        <img src="<?= htmlspecialchars($ttdUrl) ?>?v=<?= time() ?>"
                            alt="TTD Kepala Desa"
                            style="max-height:100px;max-width:250px;object-fit:contain;">
                    </div>
                    <p style="margin-top:8px;color:#27ae60;font-size:13px;">
                        ✅ Tanda tangan aktif — akan muncul di semua surat
                    </p>
                <?php else: ?>
                    <div style="border:2px dashed #ddd;padding:24px 40px;display:inline-block;border-radius:8px;color:#aaa;">
                        Belum ada tanda tangan
                    </div>
                    <p style="margin-top:8px;color:#e74c3c;font-size:13px;">
                        ⚠️ Surat akan tampil tanpa tanda tangan sampai file diupload
                    </p>
                <?php endif; ?>
            </div>

            <!-- Form Upload TTD Baru -->
            <div style="border-top:1px solid #eee;padding-top:24px;">
                <h6>Upload Tanda Tangan Baru:</h6>
                <p style="font-size:12px;color:#888;margin-bottom:16px;">
                    Format: PNG dengan background transparan (.png) — maks 2MB<br>
                    Tips: Scan atau foto tanda tangan di kertas putih, lalu hapus background menggunakan
                    <a href="https://remove.bg" target="_blank">remove.bg</a> (gratis)
                </p>

                <form action="/web-pengajuan/admin/pengaturan/ttd/upload" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">

                    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;">
                        <input type="file"
                            name="ttd_file"
                            id="ttd_file"
                            accept="image/png,image/jpeg"
                            required
                            style="border:1px solid #ddd;padding:8px;border-radius:6px;">

                        <!-- Preview sebelum upload -->
                        <div id="preview-box" style="display:none;border:1px solid #ddd;padding:8px;border-radius:6px;background:#fafafa;">
                            <img id="preview-img" style="max-height:80px;max-width:200px;object-fit:contain;" alt="Preview">
                        </div>

                        <button type="submit"
                            style="background:#27ae60;color:#fff;padding:10px 24px;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                            Upload Tanda Tangan
                        </button>
                    </div>

                    <?php if (!empty($ttdAda)): ?>
                        <div style="margin-top:16px;">
                            <label style="display:flex;align-items:center;gap:8px;font-size:13px;color:#e74c3c;cursor:pointer;">
                                <input type="checkbox" name="hapus_ttd" value="1">
                                Hapus tanda tangan yang ada (surat akan tanpa TTD sampai upload baru)
                            </label>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Nama Kepala Desa & NIP -->
            <div style="border-top:1px solid #eee;padding-top:24px;margin-top:24px;">
                <h6>Nama & NIP Kepala Desa (muncul di bawah TTD):</h6>
                <form action="/web-pengajuan/admin/pengaturan/ttd/nama" method="POST">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">
                    <div style="display:flex;flex-direction:column;gap:12px;max-width:400px;">
                        <div>
                            <label style="font-size:13px;color:#555;margin-bottom:4px;display:block;">Nama Kepala Desa</label>
                            <input type="text" name="nama_kades"
                                value="<?= htmlspecialchars($namaKades ?? '') ?>"
                                placeholder="Contoh: H. Ahmad Fauzi, S.Sos"
                                style="border:1px solid #ddd;padding:9px 14px;border-radius:6px;width:100%;font-size:13px;">
                        </div>
                        <div>
                            <label style="font-size:13px;color:#555;margin-bottom:4px;display:block;">NIP</label>
                            <input type="text" name="nip_kades"
                                value="<?= htmlspecialchars($nipKades ?? '') ?>"
                                placeholder="Contoh: 19800101 200604 1 001 (kosongkan jika tidak ada)"
                                style="border:1px solid #ddd;padding:9px 14px;border-radius:6px;width:100%;font-size:13px;">
                        </div>
                        <div>
                            <button type="submit"
                                style="background:#3498db;color:#fff;padding:10px 24px;border:none;border-radius:6px;cursor:pointer;font-weight:600;width:fit-content;">
                                Simpan
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script>
    // Preview gambar sebelum diupload
    document.getElementById('ttd_file').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = function(ev) {
            document.getElementById('preview-img').src = ev.target.result;
            document.getElementById('preview-box').style.display = 'block';
        };
        reader.readAsDataURL(file);
    });
</script>

<?php $content = ob_get_clean();
require __DIR__ . '/../partials/layout.php';
