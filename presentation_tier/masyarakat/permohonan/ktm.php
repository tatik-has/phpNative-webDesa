<?php
/**
 * PRESENTATION TIER - Form SKTM LENGKAP
 * Pengganti: presentation_tier/masyarakat/permohonan/ktm.blade.php (Doc 10)
 */

$pageTitle = 'Pengajuan Surat Keterangan Tidak Mampu';
$extraCss  = [
    '/web-pengajuan/presentation_tier/css/masyarakat/ktm.css',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
];

if (session_status() === PHP_SESSION_NONE) session_start();
$oldInput = $_SESSION['old_input'] ?? [];
$errors   = $_SESSION['errors']    ?? [];
unset($_SESSION['old_input'], $_SESSION['errors']);

function ktmOld(string $k, array $o=[]): string { return htmlspecialchars($o[$k]??''); }
function ktmErr(string $k, array $e): string {
    $m=$e[$k]??null; if(!$m) return '';
    $m=is_array($m)?$m[0]:$m;
    return '<span class="error-message" style="color:#dc3545;font-size:12px;display:block;margin-top:4px;">'.htmlspecialchars($m).'</span>';
}

ob_start(); ?>
<main class="form-page-container">
<div class="form-wrapper">
    <div class="form-title">
        <h2>Formulir Permohonan</h2>
        <h1>Surat Keterangan Tidak Mampu (SKTM)</h1>
    </div>
    <div style="margin-bottom:15px;">
        <a href="javascript:history.back()" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="alert alert-info" style="background:#d1ecf1;color:#0c5460;padding:15px;border-radius:5px;margin-bottom:20px;border-left:4px solid #17a2b8;">
        <i class="fas fa-info-circle" style="margin-right:8px;"></i>
        <strong>Informasi:</strong> Pengajuan SKTM akan diproses oleh admin setelah Anda melengkapi dan mengirim formulir ini. Harap pastikan semua dokumen yang diunggah sudah sesuai.
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="background:#f8d7da;color:#721c24;padding:10px;border-radius:5px;margin-bottom:15px;border-left:4px solid #dc3545;">
        <strong>Terdapat kesalahan pada input Anda:</strong>
        <ul style="list-style-type:disc;margin-left:20px;margin-top:10px;">
            <?php foreach($errors as $msgs): foreach((array)$msgs as $msg): ?>
                <li><?= htmlspecialchars($msg) ?></li>
            <?php endforeach; endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <form action="/web-pengajuan/pengajuan/sktm" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">

        <h3 class="form-section-title">Data Diri Pemohon</h3>
        <div class="form-group">
            <label for="nik">NIK</label>
            <input type="number" id="nik" name="nik" placeholder="Masukkan 16 digit NIK Anda" value="<?= ktmOld('nik',$oldInput) ?>" required>
            <?= ktmErr('nik',$errors) ?>
        </div>
        <div class="form-group">
            <label for="nama">Nama Lengkap (sesuai KTP)</label>
            <input type="text" id="nama" name="nama" placeholder="Masukkan Nama Lengkap Anda" value="<?= ktmOld('nama',$oldInput) ?>" required>
            <?= ktmErr('nama',$errors) ?>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="jenis-kelamin">Jenis Kelamin</label>
                <div class="select-wrapper">
                    <select id="jenis-kelamin" name="jenis_kelamin" required>
                        <option value="" disabled selected>Pilih Jenis Kelamin</option>
                        <option value="Laki-laki" <?= ktmOld('jenis_kelamin',$oldInput)==='Laki-laki'?'selected':'' ?>>Laki-laki</option>
                        <option value="Perempuan"  <?= ktmOld('jenis_kelamin',$oldInput)==='Perempuan' ?'selected':'' ?>>Perempuan</option>
                    </select>
                </div>
                <?= ktmErr('jenis_kelamin',$errors) ?>
            </div>
            <div class="form-group">
                <label for="nomor-telp">Nomor Telp/Whatsapp Aktif</label>
                <input type="text" id="nomor-telp" name="nomor_telp"
                    placeholder="Contoh: 081234567890 atau +6281234567890"
                    value="<?= ktmOld('nomor_telp',$oldInput) ?>" required maxlength="14">
                <small style="color:#666;font-size:12px;">Format: 08xxxxxxxxxx (11-13 digit) atau +62xxxxxxxxxx</small>
                <?= ktmErr('nomor_telp',$errors) ?>
            </div>
        </div>
        <div class="form-group">
            <label for="alamat-lengkap">Alamat Lengkap (sesuai KK)</label>
            <textarea id="alamat-lengkap" name="alamat_lengkap" rows="3"
                placeholder="Masukkan alamat lengkap sesuai Kartu Keluarga"
                required><?= ktmOld('alamat_lengkap',$oldInput) ?></textarea>
            <?= ktmErr('alamat_lengkap',$errors) ?>
        </div>

        <h3 class="form-section-title">Data Pendukung &amp; Keperluan</h3>
        <div class="form-group">
            <label for="keperluan">Keperluan Pembuatan SKTM</label>
            <textarea id="keperluan" name="keperluan" rows="3"
                placeholder="Contoh: Pengajuan Beasiswa KIP Kuliah, Keringanan Biaya Rumah Sakit, dll."
                required><?= ktmOld('keperluan',$oldInput) ?></textarea>
            <?= ktmErr('keperluan',$errors) ?>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="penghasilan_display">Penghasilan Rata-rata / Bulan (Rp)</label>
                <?php
                $penghasilanRaw = $oldInput['penghasilan'] ?? '';
                $penghasilanFmt = $penghasilanRaw ? number_format((float)$penghasilanRaw, 0, ',', '.') : '';
                ?>
                <input type="text" id="penghasilan_display" placeholder="Contoh: 800.000"
                    value="<?= htmlspecialchars($penghasilanFmt) ?>" required>
                <input type="hidden" id="penghasilan" name="penghasilan" value="<?= htmlspecialchars($penghasilanRaw) ?>">
                <?= ktmErr('penghasilan',$errors) ?>
            </div>
            <div class="form-group">
                <label for="jumlah-tanggungan">Jumlah Anggota Keluarga yg Ditanggung</label>
                <input type="number" id="jumlah-tanggungan" name="jumlah_tanggungan"
                    placeholder="Contoh: 4" value="<?= ktmOld('jumlah_tanggungan',$oldInput) ?>" required>
                <?= ktmErr('jumlah_tanggungan',$errors) ?>
            </div>
        </div>

        <h3 class="form-section-title">Unggah Dokumen Persyaratan</h3>
        <p class="upload-note">Mohon unggah dokumen dalam format .JPG, .JPEG, .PNG, .PDF, .DOC, atau .DOCX.</p>

        <div class="form-row">
            <div class="form-group">
                <label for="ktp">Scan/Foto KTP</label>
                <div class="file-upload-wrapper">
                    <input type="file" id="ktp" name="ktp" class="file-input" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
                    <button type="button" class="file-choose-btn">Pilih File</button>
                    <span class="file-name-display">Belum ada file</span>
                </div>
                <?= ktmErr('ktp',$errors) ?>
            </div>
            <div class="form-group">
                <label for="kk">Scan/Foto Kartu Keluarga (KK)</label>
                <div class="file-upload-wrapper">
                    <input type="file" id="kk" name="kk" class="file-input" accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
                    <button type="button" class="file-choose-btn">Pilih File</button>
                    <span class="file-name-display">Belum ada file</span>
                </div>
                <?= ktmErr('kk',$errors) ?>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="surat-pengantar">Surat Pengantar RT/RW</label>
                <div class="file-upload-wrapper">
                    <input type="file" id="surat-pengantar" name="surat_pengantar_rt_rw" class="file-input"
                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
                    <button type="button" class="file-choose-btn">Pilih File</button>
                    <span class="file-name-display">Belum ada file</span>
                </div>
                <?= ktmErr('surat_pengantar_rt_rw',$errors) ?>
            </div>
            <div class="form-group">
                <label for="foto-rumah">Foto Rumah Tampak Depan</label>
                <div class="file-upload-wrapper">
                    <input type="file" id="foto-rumah" name="foto_rumah" class="file-input"
                        accept=".jpg,.jpeg,.png,.pdf,.doc,.docx" required>
                    <button type="button" class="file-choose-btn">Pilih File</button>
                    <span class="file-name-display">Belum ada file</span>
                </div>
                <?= ktmErr('foto_rumah',$errors) ?>
            </div>
        </div>

        <div class="form-group declaration">
            <div class="declaration-wrapper">
                <input type="checkbox" id="declaration" name="declaration" required>
                <label for="declaration">
                    Saya menyatakan bahwa seluruh data dan dokumen yang saya kirimkan adalah benar
                    dan dapat dipertanggungjawabkan. Jika ditemukan ketidaksesuaian, saya bersedia menerima
                    sanksi sesuai hukum yang berlaku.
                </label>
            </div>
            <?= ktmErr('declaration',$errors) ?>
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
        input.closest('.file-upload-wrapper').querySelector('.file-name-display').textContent='Belum ada file';
        return false;
    }
    return true;
}
// Format rupiah
const penghasilanDisplay = document.getElementById('penghasilan_display');
const penghasilanHidden  = document.getElementById('penghasilan');
if (penghasilanDisplay) {
    penghasilanDisplay.addEventListener('input', function(e) {
        let raw = e.target.value.replace(/[^0-9]/g,'');
        penghasilanHidden.value = raw;
        e.target.value = raw==='' ? '' : raw.replace(/\B(?=(\d{3})+(?!\d))/g,'.');
    });
}
document.querySelectorAll('.file-choose-btn').forEach(btn=>btn.addEventListener('click',()=>btn.closest('.file-upload-wrapper').querySelector('.file-input').click()));
document.querySelectorAll('.file-input').forEach(input=>{
    const display=input.closest('.file-upload-wrapper').querySelector('.file-name-display');
    input.addEventListener('change',e=>{
        if(validateFileSize(e.target)) display.textContent=e.target.files[0]?.name||'Belum ada file';
    });
});
</script>
<?php $scripts = ob_get_clean();
require __DIR__ . '/../layout.php';