<?php
/**
 * PRESENTATION TIER - Partial: Tabel Arsip
 * Pengganti: presentation_tier/admin/partials/_tabel_arsip.blade.php
 *
 * Variabel: $permohonans (array), $type (string)
 */

$detailBase = [
    'domisili' => '/web-pengajuan/admin/domisili/',
    'sku'      => '/web-pengajuan/admin/sku/',
    'ktm'      => '/web-pengajuan/admin/ktm/',
];
$base = $detailBase[$type] ?? '#';
?>

<?php if (empty($permohonans)): ?>
    <div class="no-data-container" style="text-align:center;padding:40px 0;">
        <p style="color:#888;margin-top:10px;">Tidak ada data arsip.</p>
    </div>
<?php else: ?>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Pemohon</th>
                <th>NIK</th>
                <th>Tanggal Pengajuan</th>
                <th>Tanggal Diarsipkan</th>
                <th>Status Akhir</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($permohonans as $i => $item): ?>
                <?php
                $statusValue = $item['status'] ?? '';
                $detailUrl   = $base . ($item['id'] ?? '');
                $deleteUrl   = '/web-pengajuan/admin/surat/' . $type . '/' . ($item['id'] ?? '') . '/delete';

                // FIX: hitung $archived di dalam loop, bukan di luar
                $archived = !empty($item['archived_at'])
                    ? date('d M Y, H:i', strtotime($item['archived_at'])) . ' WIB'
                    : '-';
                ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td>
                        <a href="<?= htmlspecialchars($detailUrl) ?>" style="text-decoration:none;color:#007bff;">
                            <?= htmlspecialchars($item['user_name'] ?? $item['nama'] ?? '-') ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($item['nik'] ?? '-') ?></td>
                    <td><?= date('d M Y, H:i', strtotime($item['created_at'] ?? '')) ?> WIB</td>
                    <td><?= $archived ?></td>
                    <td>
                        <?php if ($statusValue === 'Selesai'): ?>
                            <span class="status" style="background:#d4edda;color:#155724;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:bold;">Selesai</span>
                        <?php elseif ($statusValue === 'Ditolak'): ?>
                            <span class="status" style="background:#f8d7da;color:#721c24;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:bold;">Ditolak</span>
                        <?php else: ?>
                            <span class="status" style="background:#eee;padding:4px 8px;border-radius:4px;font-size:12px;"><?= htmlspecialchars($statusValue) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="action-buttons" style="display:flex;gap:5px;">
                        <a href="<?= htmlspecialchars($detailUrl) ?>" class="btn btn-detail">Lihat Detail</a>
                        <form action="<?= htmlspecialchars($deleteUrl) ?>" method="POST"
                            onsubmit="return confirm('Hapus permanen? Tindakan ini tidak dapat dibatalkan.');">
                            <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-danger"
                                style="background:#dc3545;color:white;border:none;padding:5px 10px;border-radius:4px;cursor:pointer;">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>