<?php
/**
 * PRESENTATION TIER - Form SKU LENGKAP
 * Pengganti: presentation_tier/masyarakat/permohonan/usaha.blade.php (Doc 11)
 */

$pageTitle = 'Pengajuan Surat Keterangan Usaha';
$extraCss  = [
    '/web-pengajuan/presentation_tier/css/masyarakat/usaha.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
];

if (session_status() === PHP_SESSION_NONE) session_start();
$oldInput = $_SESSION['old_input'] ?? [];
$errors   = $_SESSION['errors']    ?? [];
unset($_SESSION['old_input'], $_SESSION['errors']);

function skuOld(string $k, array $o=[]): string { return htmlspecialchars($o[$k]??''); }
function skuErr(string $k, array $e): string {
    $m=$e[$k]??null; if(!$m) return '';
    $m=is_array($m)?$m[0]:$m;
    return '<span class="error-message" style="color:#dc3545;font-size:12px;display:block;margin-top:4px;">'.htmlspecialchars($m).'</span>';
}

$lamaUsahaOptions = [
    'Kurang dari 6 bulan','6 bulan','1 tahun','2 tahun','3 tahun','4 tahun','5 tahun',
    '6 tahun','7 tahun','8 tahun','9 tahun','10 tahun','Lebih dari 10 tahun',
];

ob_start(); ?>
<main class="form-page-container">
<div class="form-wrapper">
    <div class="form-title">
        <h2>Formulir Permohonan</h2>
        <h1>Surat Keterangan Usaha (SKU)</h1>
    </div>
    <div style="margin-bottom:15px;">
        <a href="javascript:history.back()" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="alert alert-info" style="background:#d1ecf1;color:#0c5460;padding:15px;border-radius:5px;margin-bottom:20px;border-left:4px solid #17a2b8;">
        <i class="fas fa-info-circle" style="margin-right:8px;"></i>
        <strong>Informasi:</strong> Setelah formulir SKU ini dikirim, admin akan segera memproses pengajuan Anda. Pastikan data usaha dan dokumen pendukung sudah lengkap.
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="background:#f8d7da;color:#721c24;padding:10px;border-radius:5px;margin-bottom:15px;border-left:4px solid #dc3545;">
        <i class="fas fa-exclamation-triangle" style="margin-right:8px;"></i>
        <strong>Terdapat kesalahan pada input Anda:</strong>
        <ul style="list-style-type:disc;margin-left:20px;margin-top:10px;">
            <?php foreach($errors as $msgs): foreach((array)$msgs as $msg): ?>
                <li><?= htmlspecialchars($msg) ?></li>
            <?php endforeach; endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form action="/web-pengajuan/pengajuan/sku" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">

        <h3 class="form-section-title">Data Pemohon (Pemilik Usaha)</h3>
        <div class="form-group">
            <label for="nik">NIK</label>
            <input type="number" id="nik" name="nik" placeholder="Masukkan NIK Anda" value="<?= skuOld('nik',$oldInput) ?>" required>
            <?= skuErr('nik',$errors) ?>
        </div>
        <div class="form-group">
            <label for="nama">Nama Lengkap</label>
            <input type="text" id="nama" name="nama" placeholder="Masukkan Nama Sesuai KTP" value="<?= skuOld('nama',$oldInput) ?>" required>
            <?= skuErr('nama',$errors) ?>
        </div>
        <div class="form-group">
            <label for="alamat_ktp">Alamat Sesuai KTP</label>
            <input type="text" id="alamat_ktp" name="alamat_ktp" placeholder="Masukkan Alamat Lengkap Sesuai KTP" value="<?= skuOld('alamat_ktp',$oldInput) ?>" required>
            <?= skuErr('alamat_ktp',$errors) ?>
        </div>
        <div class="form-group">
            <label for="nomor_telp">Nomor Telp/Whatsapp Aktif</label>
            <input type="text" id="nomor_telp" name="nomor_telp" placeholder="Contoh: 08123456789 atau +628123456789" value="<?= skuOld('nomor_telp',$oldInput) ?>" required>
            <?= skuErr('nomor_telp',$errors) ?>
        </div>

        <h3 class="form-section-title">Data Usaha</h3>
        <div class="form-group">
            <label for="nama_usaha">Nama Usaha</label>
            <input type="text" id="nama_usaha" name="nama_usaha" placeholder="Contoh: Warung Berkah, Jaya Laundry" value="<?= skuOld('nama_usaha',$oldInput) ?>" required>
            <?= skuErr('nama_usaha',$errors) ?>
        </div>
        <div class="form-group">
            <label for="jenis_usaha">Jenis Usaha</label>
            <input type="text" id="jenis_usaha" name="jenis_usaha" placeholder="Contoh: Toko Kelontong, Jasa Jahit, Katering" value="<?= skuOld('jenis_usaha',$oldInput) ?>" required>
            <?= skuErr('jenis_usaha',$errors) ?>
        </div>
        <div class="form-group">
            <label for="alamat_usaha">Alamat Lengkap Tempat Usaha</label>
            <textarea id="alamat_usaha" name="alamat_usaha" rows="3" placeholder="Masukkan alamat lengkap lokasi usaha Anda" required><?= skuOld('alamat_usaha',$oldInput) ?></textarea>
            <?= skuErr('alamat_usaha',$errors) ?>
        </div>
        <div class="form-group">
            <label for="lama_usaha">Lama Usaha Berdiri</label>
            <div class="select-wrapper">
                <select id="lama_usaha" name="lama_usaha" required>
                    <option value="">-- Pilih Lama Usaha --</option>
                    <?php foreach ($lamaUsahaOptions as $opt): ?>
                        <option value="<?= htmlspecialchars($opt) ?>" <?= skuOld('lama_usaha',$oldInput)===$opt?'selected':'' ?>>
                            <?= htmlspecialchars($opt) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?= skuErr('lama_usaha',$errors) ?>
        </div>

        <h3 class="form-section-title">Dokumen Pendukung</h3>
        <small style="color:#666;display:block;margin-top:5px;margin-bottom:15px;">
            Format yang diterima: Foto (JPG/PNG), PDF, atau Word (DOC/DOCX)
        </small>
        <div class="form-row">
            <div class="form-group">
                <label for="ktp">Scan/Foto KTP</label>
                <div class="file-upload-wrapper">
                    <input type="file" id="ktp" name="ktp" class="file-input"
                        accept="image/jpeg,image/jpg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                    <button type="button" class="file-choose-btn">Choose File</button>
                    <span class="file-name-display">No File Chosen</span>
                </div>
                <?= skuErr('ktp',$errors) ?>
            </div>
            <div class="form-group">
                <label for="kk">Scan/Foto KK</label>
                <div class="file-upload-wrapper">
                    <input type="file" id="kk" name="kk" class="file-input"
                        accept="image/jpeg,image/jpg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                    <button type="button" class="file-choose-btn">Choose File</button>
                    <span class="file-name-display">No File Chosen</span>
                </div>
                <?= skuErr('kk',$errors) ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="surat_pengantar">Surat Pengantar RT/RW</label>
                <div class="file-upload-wrapper">
                    <input type="file" id="surat_pengantar" name="surat_pengantar" class="file-input"
                        accept="image/jpeg,image/jpg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                    <button type="button" class="file-choose-btn">Choose File</button>
                    <span class="file-name-display">No File Chosen</span>
                </div>
                <?= skuErr('surat_pengantar',$errors) ?>
            </div>
            <div class="form-group">
                <label for="foto_usaha">Foto Tempat Usaha (Opsional)</label>
                <div class="file-upload-wrapper">
                    <input type="file" id="foto_usaha" name="foto_usaha" class="file-input"
                        accept="image/jpeg,image/jpg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                    <button type="button" class="file-choose-btn">Choose File</button>
                    <span class="file-name-display">No File Chosen</span>
                </div>
            </div>
        </div>

        <button type="submit" class="submit-btn">Kirim Permohonan <i class="fas fa-arrow-right"></i></button>
    </form>
</div>
</main>
<?php $content = ob_get_clean();

ob_start(); ?>
<script>
function validateFileSize(input) {
    const maxSize = 100*1024*1024;
    const file = input.files[0];
    if (file && file.size > maxSize) {
        alert('Ukuran file terlalu besar! Maksimal 100 MB. File Anda: '+(file.size/(1024*1024)).toFixed(2)+' MB');
        input.value='';
        input.closest('.file-upload-wrapper').querySelector('.file-name-display').textContent='No File Chosen';
        return false;
    }
    return true;
}
document.querySelectorAll('.file-input').forEach(input=>{
    const wrapper=input.closest('.file-upload-wrapper');
    const display=wrapper.querySelector('.file-name-display');
    input.addEventListener('change',e=>{
        if(validateFileSize(e.target)) display.textContent=e.target.files[0]?.name||'No File Chosen';
    });
});
document.querySelectorAll('.file-choose-btn').forEach(btn=>{
    btn.addEventListener('click',()=>{
        const input=btn.closest('.file-upload-wrapper').querySelector('.file-input');
        input.click();
    });
});
</script>
<?php $scripts = ob_get_clean();
require __DIR__ . '/../layout.php';