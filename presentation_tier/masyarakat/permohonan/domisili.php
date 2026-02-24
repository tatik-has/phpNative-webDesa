<?php

$pageTitle = 'Pengajuan Surat Keterangan Domisili';
$extraCss  = ['/web-pengajuan/presentation_tier/css/masyarakat/domisili.css'];

if (session_status() === PHP_SESSION_NONE) session_start();

$oldInput = $_SESSION['old_input'] ?? [];

$allErrors      = $_SESSION['errors'] ?? [];
$domisiliFields = ['nik','nama','alamat_domisili','nomor_telp','rt_domisili','rw_domisili','jenis_kelamin','alamat_ktp','ktp','kk'];
$errors         = array_intersect_key($allErrors, array_flip($domisiliFields));

unset($_SESSION['old_input'], $_SESSION['errors']);

function domOld(string $key, array $old = []): string {
    return htmlspecialchars($old[$key] ?? '');
}
function domErr(string $key, array $errs): string {
    $msg = $errs[$key] ?? null;
    if (!$msg) return '';
    $msg = is_array($msg) ? $msg[0] : $msg;
    return '<span class="error-message" style="color:#dc3545;font-size:12px;display:block;margin-top:4px;">'.htmlspecialchars($msg).'</span>';
}

ob_start(); ?>
<main class="form-page-container">
<div class="form-wrapper">
    <div class="form-title">
        <h2>Formulir Permohonan</h2>
        <h1>Surat Keterangan Domisili</h1>
    </div>
    <div style="margin-bottom:15px;">
        <a href="javascript:history.back()" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
    <div class="alert alert-info" style="background:#d1ecf1;color:#0c5460;padding:15px;border-radius:5px;margin-bottom:20px;border-left:4px solid #17a2b8;">
        <i class="fas fa-info-circle" style="margin-right:8px;"></i>
        <strong>Informasi:</strong> Pastikan semua data yang Anda isi sudah benar dan lengkap. <strong>Maksimal ukuran file: 100 MB per dokumen.</strong>
    </div>

    <?php if (!empty($errors)): ?>
    <div class="alert alert-danger" style="background:#f8d7da;color:#721c24;padding:10px;border-radius:5px;margin-bottom:15px;border-left:4px solid #dc3545;">
        <strong>Terdapat kesalahan pada input Anda:</strong>
        <ul style="margin-left:20px;margin-top:10px;">
            <?php foreach ($errors as $msgs): foreach ((array)$msgs as $msg): ?>
                <li><?= htmlspecialchars($msg) ?></li>
            <?php endforeach; endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- PERBAIKAN: action menggunakan /web-pengajuan/ prefix -->
    <form action="/web-pengajuan/pengajuan/domisili" method="POST" enctype="multipart/form-data" id="domisiliForm">
        <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">
        <h3 class="form-section-title">Data Pemohon</h3>

        <div class="form-group">
            <label for="nik">NIK <span style="color:red;">*</span></label>
            <input type="number" id="nik" name="nik" placeholder="Masukkan 16 digit NIK" value="<?= domOld('nik',$oldInput) ?>" required>
            <span class="field-error" id="error-nik">NIK harus terdiri dari 16 digit angka</span>
            <?= domErr('nik',$errors) ?>
        </div>

        <div class="form-group">
            <label for="nama">Nama <span style="color:red;">*</span></label>
            <input type="text" id="nama" name="nama" placeholder="Masukkan Nama Lengkap" value="<?= domOld('nama',$oldInput) ?>" required>
            <span class="field-error" id="error-nama">Nama tidak boleh kosong</span>
            <?= domErr('nama',$errors) ?>
        </div>

        <div class="form-group">
            <label for="alamat-domisili">Alamat Domisili <span style="color:red;">*</span></label>
            <input type="text" id="alamat-domisili" name="alamat_domisili" placeholder="Masukkan Alamat Domisili" value="<?= domOld('alamat_domisili',$oldInput) ?>" required>
            <span class="field-error" id="error-alamat_domisili">Alamat domisili tidak boleh kosong</span>
            <?= domErr('alamat_domisili',$errors) ?>
        </div>

        <div class="form-group">
            <label for="nomor-telp">Nomor Telp/Whatsapp <span style="color:red;">*</span></label>
            <input type="text" id="nomor-telp" name="nomor_telp" placeholder="Contoh: 08123456789 atau +628123456789" value="<?= domOld('nomor_telp',$oldInput) ?>" required>
            <span class="field-error" id="error-nomor_telp">Format nomor telepon salah</span>
            <?= domErr('nomor_telp',$errors) ?>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="rt-domisili">RT Domisili <span style="color:red;">*</span></label>
                <input type="number" id="rt-domisili" name="rt_domisili" placeholder="Masukkan RT" value="<?= domOld('rt_domisili',$oldInput) ?>" required>
                <span class="field-error" id="error-rt_domisili">RT tidak boleh kosong</span>
                <?= domErr('rt_domisili',$errors) ?>
            </div>
            <div class="form-group">
                <label for="rw-domisili">RW Domisili <span style="color:red;">*</span></label>
                <input type="number" id="rw-domisili" name="rw_domisili" placeholder="Masukkan RW" value="<?= domOld('rw_domisili',$oldInput) ?>" required>
                <span class="field-error" id="error-rw_domisili">RW tidak boleh kosong</span>
                <?= domErr('rw_domisili',$errors) ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="jenis-kelamin">Jenis Kelamin <span style="color:red;">*</span></label>
                <div class="select-wrapper">
                    <select id="jenis-kelamin" name="jenis_kelamin" required>
                        <option value="">Pilih Jenis Kelamin</option>
                        <option value="Laki-laki"  <?= domOld('jenis_kelamin',$oldInput)==='Laki-laki' ?'selected':'' ?>>Laki-laki</option>
                        <option value="Perempuan"  <?= domOld('jenis_kelamin',$oldInput)==='Perempuan' ?'selected':'' ?>>Perempuan</option>
                    </select>
                </div>
                <span class="field-error" id="error-jenis_kelamin">Pilih jenis kelamin</span>
                <?= domErr('jenis_kelamin',$errors) ?>
            </div>
            <div class="form-group">
                <label for="alamat-ktp">Alamat KTP <span style="color:red;">*</span></label>
                <input type="text" id="alamat-ktp" name="alamat_ktp" placeholder="Masukkan Alamat KTP" value="<?= domOld('alamat_ktp',$oldInput) ?>" required>
                <span class="field-error" id="error-alamat_ktp">Alamat KTP tidak boleh kosong</span>
                <?= domErr('alamat_ktp',$errors) ?>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="ktp">Upload KTP (Foto/PDF/Word) <span style="color:red;">*</span></label>
                <small style="color:#666;display:block;margin-top:5px;">Maksimal: 100 MB</small>
                <div class="file-upload-wrapper">
                    <input type="file" id="ktp" name="ktp" class="file-input"
                        accept="image/jpeg,image/jpg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                    <button type="button" class="file-choose-btn">Choose File</button>
                    <span class="file-name-display">No File Chosen</span>
                </div>
                <span class="field-error" id="error-ktp">File KTP harus diupload</span>
                <?= domErr('ktp',$errors) ?>
            </div>
            <div class="form-group">
                <label for="kk">Upload KK (Foto/PDF/Word) <span style="color:red;">*</span></label>
                <small style="color:#666;display:block;margin-top:5px;">Maksimal: 100 MB</small>
                <div class="file-upload-wrapper">
                    <input type="file" id="kk" name="kk" class="file-input"
                        accept="image/jpeg,image/jpg,image/png,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required>
                    <button type="button" class="file-choose-btn">Choose File</button>
                    <span class="file-name-display">No File Chosen</span>
                </div>
                <span class="field-error" id="error-kk">File KK harus diupload</span>
                <?= domErr('kk',$errors) ?>
            </div>
        </div>

        <button type="submit" class="submit-btn">Kirim Permohonan <i class="fas fa-arrow-right"></i></button>
    </form>
</div>
</main>
<?php $content = ob_get_clean();

ob_start(); ?>
<style>
.field-error { color:#dc3545; font-size:12px; margin-top:5px; display:none; }
.field-error.show { display:block; }
.form-group input.error, .form-group select.error { border-color:#dc3545!important; background-color:#fff5f5; }
.form-group input.valid, .form-group select.valid  { border-color:#28a745!important; }
</style>
<script>
function validateFileSize(input) {
    const maxSize = 100 * 1024 * 1024;
    const file = input.files[0];
    if (file && file.size > maxSize) {
        alert('Ukuran file terlalu besar! Maksimal 100 MB. File Anda: ' + (file.size/(1024*1024)).toFixed(2) + ' MB');
        input.value = '';
        input.closest('.file-upload-wrapper').querySelector('.file-name-display').textContent = 'No File Chosen';
        return false;
    }
    return true;
}

const form = document.getElementById('domisiliForm');

document.getElementById('nik').addEventListener('blur', function () {
    const s = document.getElementById('error-nik');
    if (!this.value) { this.classList.add('error'); s.textContent='NIK tidak boleh kosong'; s.classList.add('show'); }
    else if (this.value.length !== 16) { this.classList.add('error'); s.textContent='NIK harus 16 digit'; s.classList.add('show'); }
    else { this.classList.remove('error'); this.classList.add('valid'); s.classList.remove('show'); }
});

['nama','alamat-domisili','rt-domisili','rw-domisili','alamat-ktp'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    const key = 'error-' + id.replace(/-/g, '_');
    el.addEventListener('blur', function () {
        const s = document.getElementById(key);
        if (!this.value.trim()) { this.classList.add('error'); if (s) s.classList.add('show'); }
        else { this.classList.remove('error'); this.classList.add('valid'); if (s) s.classList.remove('show'); }
    });
});

document.getElementById('nomor-telp').addEventListener('blur', function () {
    const s = document.getElementById('error-nomor_telp');
    const r = /^(08|\+628)[0-9]{9,11}$/;
    if (!this.value.trim()) { this.classList.add('error'); s.textContent='Nomor telepon tidak boleh kosong'; s.classList.add('show'); }
    else if (!r.test(this.value.trim())) { this.classList.add('error'); s.textContent='Format: 08123456789 atau +628123456789'; s.classList.add('show'); }
    else { this.classList.remove('error'); this.classList.add('valid'); s.classList.remove('show'); }
});

document.getElementById('jenis-kelamin').addEventListener('change', function () {
    const s = document.getElementById('error-jenis_kelamin');
    if (!this.value) { this.classList.add('error'); s.classList.add('show'); }
    else { this.classList.remove('error'); this.classList.add('valid'); s.classList.remove('show'); }
});

document.querySelectorAll('.file-choose-btn').forEach(btn =>
    btn.addEventListener('click', () => btn.closest('.file-upload-wrapper').querySelector('.file-input').click())
);

document.querySelectorAll('.file-input').forEach(input => {
    input.addEventListener('change', e => {
        const display = e.target.closest('.file-upload-wrapper').querySelector('.file-name-display');
        if (validateFileSize(e.target)) {
            display.textContent = e.target.files[0]?.name || 'No File Chosen';
        }
    });
});

form.addEventListener('submit', function (e) {
    let hasError = false;
    ['nik','nama','alamat-domisili','nomor-telp','rt-domisili','rw-domisili','jenis-kelamin','alamat-ktp'].forEach(id => {
        const f = document.getElementById(id);
        if (!f || !f.value.trim()) { if (f) f.classList.add('error'); hasError = true; }
    });
    ['ktp','kk'].forEach(id => { if (!document.getElementById(id).files.length) hasError = true; });
    if (hasError) {
        e.preventDefault();
        alert('Mohon lengkapi semua field yang wajib diisi!');
        const first = form.querySelector('.error');
        if (first) { first.scrollIntoView({ behavior:'smooth', block:'center' }); first.focus(); }
    }
});
</script>
<?php $scripts = ob_get_clean();

require __DIR__ . '/../../masyarakat/layout.php';