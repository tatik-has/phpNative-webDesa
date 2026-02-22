<?php
/**
 * PRESENTATION TIER - Export Laporan Word
 * Pengganti: presentation_tier/admin/permohonan/laporan-word.blade.php
 * Dipanggil langsung (tanpa layout) karena output untuk download
 */
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Permohonan Surat</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; margin: 0; padding: 0; }
        .content-title { text-align: center; margin-top: 30px; margin-bottom: 25px; }
        .content-title h1 { font-size: 14pt; font-weight: bold; text-decoration: underline; margin: 0; }
        .content-title h2 { font-size: 12pt; font-weight: normal; margin: 5px 0 0 0; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 11pt; }
        .data-table th, .data-table td { border: 1px solid #000; padding: 7px; text-align: left; vertical-align: top; }
        .data-table th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .data-table tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <table style="width:100%;border-bottom:3px solid #000;padding-bottom:5px;">
        <tr>
            <td style="width:80%;text-align:center;vertical-align:middle;line-height:1.4;">
                <h4 style="margin:0;font-weight:bold;font-size:16pt;font-family:'Times New Roman',serif;">PEMERINTAH KABUPATEN BENGKALIS</h4>
                <h3 style="margin:0;font-weight:bold;font-size:18pt;font-family:'Times New Roman',serif;">KEPALA DESA PAKNING ASAL</h3>
                <h4 style="margin:0;font-weight:bold;font-size:16pt;font-family:'Times New Roman',serif;">KECAMATAN BUKIT BATU</h4>
                <p style="margin:0;font-size:11pt;font-family:'Times New Roman',serif;">JL. Sukajadi KODE POS : 28761</p>
            </td>
        </tr>
    </table>

    <div class="content-title">
        <h1>LAPORAN PERMOHONAN SURAT</h1>
        <h2>Periode <?= date('d M Y', strtotime($tanggalMulai ?? '')) ?> s.d. <?= date('d M Y', strtotime($tanggalAkhir ?? '')) ?></h2>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>No.</th>
                <th>Tanggal Pengajuan</th>
                <th>Nama Pemohon</th>
                <th>Jenis Surat</th>
                <th>Status</th>
                <th>Tanggal Selesai/Ditolak</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($allPermohonan)): ?>
                <tr><td colspan="6" style="text-align:center;padding:20px;">Tidak ada data permohonan pada periode ini.</td></tr>
            <?php else: ?>
                <?php foreach ($allPermohonan as $i => $item): ?>
                    <?php
                    $statusVal  = $item['status'] ?? '';
                    $statusNorm = strtolower($statusVal);
                    $tglSelesai = in_array($statusNorm, ['selesai','ditolak']) && !empty($item['updated_at'])
                        ? date('d M Y, H:i', strtotime($item['updated_at']))
                        : '-';
                    ?>
                    <tr>
                        <td style="text-align:center;"><?= $i + 1 ?></td>
                        <td><?= date('d M Y, H:i', strtotime($item['created_at'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($item['user_name'] ?? $item['nama'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($item['jenis_surat_label'] ?? '') ?></td>
                        <td style="text-align:center;"><?= htmlspecialchars($statusVal) ?></td>
                        <td><?= $tglSelesai ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>