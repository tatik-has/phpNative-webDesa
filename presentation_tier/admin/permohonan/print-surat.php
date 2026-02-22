<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Surat</title>
    <style>
        body { margin: 0; padding: 20px; background: #fff; }
        .no-print { display: none; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

<div class="no-print" style="margin-bottom:20px;display:block;">
    <button onclick="window.print()"
        style="background:#3498db;color:#fff;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;margin-right:10px;">
        <i class="fas fa-print"></i> Cetak
    </button>
    <button onclick="window.close()"
        style="background:#95a5a6;color:#fff;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;">
        Tutup
    </button>
</div>

<?= $templateHtml ?? '<p>Template surat tidak tersedia.</p>' ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script>
    // Auto trigger print jika dibuka langsung
    window.addEventListener('load', function() {
        setTimeout(() => window.print(), 500);
    });
</script>
</body>
</html>