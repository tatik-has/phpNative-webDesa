<?php
/**
 * PRESENTATION TIER - Admin Kelola NIK Warga
 * Dipanggil dari: AdminNikController::index()
 */

$pageTitle = 'Kelola NIK Warga';
$extraCss  = [];
ob_start();
?>

<style>
    .nik-page { padding: 0 0 40px; }
    .nik-page .page-header { margin-bottom: 24px; }
    .nik-page .page-header h1 { font-size: 22px; font-weight: 700; color: #2d3436; margin: 0 0 4px; }
    .nik-page .page-header p  { font-size: 13px; color: #888; margin: 0; }

    .nik-grid { display: grid; grid-template-columns: 380px 1fr; gap: 24px; align-items: start; }
    @media (max-width: 900px) { .nik-grid { grid-template-columns: 1fr; } }

    .card-box {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        overflow: hidden;
    }
    .card-box-header {
        padding: 16px 20px;
        border-bottom: 1px solid #f0f0f0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .card-box-header .icon {
        width: 34px; height: 34px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
    }
    .icon-blue  { background: #e8f4fd; color: #3498db; }
    .icon-green { background: #eafaf1; color: #27ae60; }
    .icon-purple{ background: #f3eafd; color: #8e44ad; }

    .card-box-header h5 { font-size: 14px; font-weight: 700; color: #2d3436; margin: 0; }
    .card-box-header p  { font-size: 11px; color: #aaa; margin: 0; }
    .card-box-body { padding: 20px; }

    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 12px; font-weight: 600; color: #555; margin-bottom: 5px; }
    .form-group input[type="text"],
    .form-group input[type="file"] {
        width: 100%; padding: 9px 12px;
        border: 1px solid #dde1e7;
        border-radius: 8px;
        font-size: 13px;
        color: #2d3436;
        transition: border-color .2s;
        box-sizing: border-box;
    }
    .form-group input[type="text"]:focus { border-color: #3498db; outline: none; box-shadow: 0 0 0 3px rgba(52,152,219,.1); }
    .form-group .hint { font-size: 11px; color: #aaa; margin-top: 4px; }

    .btn { padding: 10px 18px; border: none; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; transition: opacity .2s; }
    .btn:hover { opacity: .88; }
    .btn-primary { background: #3498db; color: #fff; }
    .btn-success { background: #27ae60; color: #fff; }
    .btn-danger  { background: #e74c3c; color: #fff; padding: 6px 12px; font-size: 12px; }
    .btn-outline { background: transparent; color: #3498db; border: 1px solid #3498db; }
    .btn-sm      { padding: 5px 10px; font-size: 11px; }
    .btn-full    { width: 100%; justify-content: center; }

    .divider { border: none; border-top: 1px solid #f0f0f0; margin: 18px 0; }

    .import-area {
        border: 2px dashed #dde1e7;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        background: #fafbfc;
        transition: border-color .2s;
        cursor: pointer;
    }
    .import-area:hover, .import-area.drag-over { border-color: #27ae60; background: #f0faf4; }
    .import-area .icon-upload { font-size: 28px; color: #bbb; margin-bottom: 8px; }
    .import-area p  { font-size: 12px; color: #888; margin: 4px 0; }
    .import-area strong { font-size: 13px; color: #555; }

    /* Tabel daftar NIK */
    .search-bar { position: relative; margin-bottom: 14px; }
    .search-bar input { width: 100%; padding: 9px 12px 9px 36px; border: 1px solid #dde1e7; border-radius: 8px; font-size: 13px; box-sizing: border-box; }
    .search-bar i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 13px; }

    .nik-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .nik-table thead th { background: #f8f9fa; padding: 10px 14px; text-align: left; font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid #eee; }
    .nik-table tbody tr { border-bottom: 1px solid #f5f5f5; transition: background .15s; }
    .nik-table tbody tr:hover { background: #fafbfc; }
    .nik-table td { padding: 10px 14px; color: #2d3436; }
    .nik-table .nik-badge { font-family: monospace; font-size: 12px; background: #f0f4ff; color: #3d5af1; padding: 3px 8px; border-radius: 4px; letter-spacing: .5px; }
    .nik-table .no-data { text-align: center; padding: 40px; color: #aaa; }

    .badge-count { background: #3498db; color: #fff; font-size: 11px; padding: 2px 8px; border-radius: 20px; font-weight: 700; margin-left: 6px; }

    .alert { padding: 12px 16px; border-radius: 8px; font-size: 13px; margin-bottom: 18px; }
    .alert-success { background: #eafaf1; color: #1e8449; border: 1px solid #a9dfbf; }
    .alert-danger   { background: #fdedec; color: #c0392b; border: 1px solid #f5b7b1; }

    .file-selected-name { font-size: 12px; color: #27ae60; margin-top: 6px; font-weight: 600; }
    
    .table-wrap { overflow-x: auto; max-height: 500px; overflow-y: auto; }
    .table-wrap::-webkit-scrollbar { width: 6px; height: 6px; }
    .table-wrap::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 3px; }
    .table-wrap::-webkit-scrollbar-thumb { background: #ddd; border-radius: 3px; }
</style>

<div class="nik-page">

    <div class="page-header">
        <h1><i class="fas fa-id-card" style="color:#3498db;margin-right:8px;"></i> Kelola NIK Warga</h1>
        <p>Daftarkan NIK warga agar dapat login ke sistem pengajuan surat tanpa perlu registrasi.</p>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="nik-grid">

        <!-- ==============================
             PANEL KIRI: Form Tambah + Import
        =============================== -->
        <div style="display:flex;flex-direction:column;gap:20px;">

            <!-- Tambah Manual -->
            <div class="card-box">
                <div class="card-box-header">
                    <div class="icon icon-blue"><i class="fas fa-user-plus"></i></div>
                    <div>
                        <h5>Tambah NIK Manual</h5>
                        <p>Input satu per satu</p>
                    </div>
                </div>
                <div class="card-box-body">
                    <form action="/web-pengajuan/admin/nik/store" method="POST">
                        <div class="form-group">
                            <label>NIK (16 digit) <span style="color:#e74c3c">*</span></label>
                            <input type="text"
                                   name="nik"
                                   maxlength="16"
                                   pattern="\d{16}"
                                   placeholder="Contoh: 1401234567890001"
                                   inputmode="numeric"
                                   required>
                            <div class="hint">Nomor Induk Kependudukan sesuai KTP</div>
                        </div>
                        <div class="form-group">
                            <label>Nama Lengkap <span style="color:#e74c3c">*</span></label>
                            <input type="text"
                                   name="nama"
                                   placeholder="Contoh: Ahmad Fauzi"
                                   required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-full">
                            <i class="fas fa-plus"></i> Tambahkan Warga
                        </button>
                    </form>
                </div>
            </div>

            <!-- Import Excel -->
            <div class="card-box">
                <div class="card-box-header">
                    <div class="icon icon-green"><i class="fas fa-file-excel"></i></div>
                    <div>
                        <h5>Import dari Excel / CSV</h5>
                        <p>Upload banyak data sekaligus</p>
                    </div>
                </div>
                <div class="card-box-body">

                    <div style="margin-bottom:14px;padding:12px;background:#fffbea;border:1px solid #f9e4a0;border-radius:8px;font-size:12px;color:#7d6608;">
                        <strong><i class="fas fa-info-circle"></i> Format File:</strong><br>
                        Kolom A = NIK (16 digit), Kolom B = Nama Lengkap<br>
                        Baris pertama boleh berisi header (akan dilewati otomatis).
                        <div style="margin-top:8px;">
                            <a href="/web-pengajuan/admin/nik/template" class="btn btn-outline btn-sm">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </div>
                    </div>

                    <form action="/web-pengajuan/admin/nik/import" method="POST" enctype="multipart/form-data" id="formImport">
                        <div class="import-area" id="importArea" onclick="document.getElementById('file_excel').click()">
                            <div class="icon-upload"><i class="fas fa-cloud-upload-alt"></i></div>
                            <strong>Klik atau drag & drop file di sini</strong>
                            <p>Format: .xlsx, .xls, atau .csv — Maks 5MB</p>
                            <p id="namaFile" class="file-selected-name" style="display:none;"></p>
                        </div>
                        <input type="file"
                               name="file_excel"
                               id="file_excel"
                               accept=".xlsx,.xls,.csv"
                               style="display:none;"
                               required>
                        <button type="submit" class="btn btn-success btn-full" style="margin-top:12px;" id="btnImport" disabled>
                            <i class="fas fa-upload"></i> Import Sekarang
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <!-- ==============================
             PANEL KANAN: Daftar NIK
        =============================== -->
        <div class="card-box">
            <div class="card-box-header">
                <div class="icon icon-purple"><i class="fas fa-list-ul"></i></div>
                <div>
                    <h5>
                        Daftar NIK Terdaftar
                        <span class="badge-count"><?= count($daftarNik) ?></span>
                    </h5>
                    <p>Warga dapat login menggunakan NIK di bawah ini</p>
                </div>
            </div>
            <div class="card-box-body">

                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchNik" placeholder="Cari NIK atau Nama...">
                </div>

                <div class="table-wrap">
                    <table class="nik-table" id="nikTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>NIK</th>
                                <th>Nama Warga</th>
                                <th>Terdaftar</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($daftarNik)): ?>
                                <tr>
                                    <td colspan="5" class="no-data">
                                        <i class="fas fa-inbox" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                                        Belum ada NIK yang terdaftar.<br>
                                        <small>Tambahkan NIK warga menggunakan form di sebelah kiri.</small>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($daftarNik as $i => $warga): ?>
                                    <tr>
                                        <td style="color:#aaa;font-size:12px;"><?= $i + 1 ?></td>
                                        <td><span class="nik-badge"><?= htmlspecialchars($warga['nik']) ?></span></td>
                                        <td style="font-weight:600;"><?= htmlspecialchars($warga['name']) ?></td>
                                        <td style="font-size:12px;color:#aaa;">
                                            <?= date('d M Y', strtotime($warga['created_at'])) ?>
                                        </td>
                                        <td>
                                            <form action="/web-pengajuan/admin/nik/<?= (int)$warga['id'] ?>/delete"
                                                  method="POST"
                                                  onsubmit="return confirm('Hapus NIK <?= htmlspecialchars($warga['nik']) ?> (<?= htmlspecialchars($warga['name']) ?>)?')">
                                                <input type="hidden" name="_method" value="DELETE">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div><!-- /.nik-grid -->

</div>

<script>
    // ── Drag & Drop + Preview nama file ──────────────────────────────
    const fileInput  = document.getElementById('file_excel');
    const namaFile   = document.getElementById('namaFile');
    const btnImport  = document.getElementById('btnImport');
    const importArea = document.getElementById('importArea');

    fileInput.addEventListener('change', function () {
        if (this.files[0]) {
            namaFile.textContent = '✅ ' + this.files[0].name;
            namaFile.style.display = 'block';
            btnImport.disabled = false;
        }
    });

    importArea.addEventListener('dragover', e => { e.preventDefault(); importArea.classList.add('drag-over'); });
    importArea.addEventListener('dragleave', () => importArea.classList.remove('drag-over'));
    importArea.addEventListener('drop', e => {
        e.preventDefault();
        importArea.classList.remove('drag-over');
        const dt = e.dataTransfer;
        if (dt.files[0]) {
            fileInput.files = dt.files;
            namaFile.textContent = '✅ ' + dt.files[0].name;
            namaFile.style.display = 'block';
            btnImport.disabled = false;
        }
    });

    // ── Pencarian real-time ───────────────────────────────────────────
    document.getElementById('searchNik').addEventListener('input', function () {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#nikTable tbody tr').forEach(tr => {
            const text = tr.innerText.toLowerCase();
            tr.style.display = text.includes(q) ? '' : 'none';
        });
    });

    // ── Validasi NIK: hanya angka ─────────────────────────────────────
    document.querySelector('input[name="nik"]').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').substring(0, 16);
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../partials/layout.php';