<?php
/**
 * PRESENTATION TIER - Partial: Tabel Permohonan
 * Pengganti: presentation_tier/admin/partials/_tabel_permohonan.blade.php
 *
 * Variabel yang dibutuhkan:
 *   $title        — string judul tabel
 *   $permohonans  — array data permohonan
 *   $type         — 'domisili' | 'ktm' | 'sku'
 */

$detailBase = [
    'domisili' => '/web-pengajuan/admin/domisili/',
    'sku'      => '/web-pengajuan/admin/sku/',
    'ktm'      => '/web-pengajuan/admin/ktm/',
];

$base = $detailBase[$type] ?? '#';
?>

<div class="table-container">
    <div class="table-header">
        <h3><?= htmlspecialchars($title) ?></h3>
    </div>

    <?php if (empty($permohonans)): ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Akun</th>
                    <th>NIK</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="6" style="text-align:center;color:#888;padding:20px;">Tidak ada data.</td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Akun</th>
                    <th>NIK</th>
                    <th>Tanggal Pengajuan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($permohonans as $i => $item): ?>
                    <?php
                    $id          = $item['id'] ?? '';
                    $statusValue = $item['status'] ?? 'Diproses';
                    $detailUrl   = $base . $id;
                    $updateUrl   = '/web-pengajuan/admin/surat/' . $type . '/' . $id . '/update-status';
                    $templateUrl = '/web-pengajuan/admin/surat/' . $type . '/' . $id . '/template';
                    $archiveUrl  = '/web-pengajuan/admin/surat/' . $type . '/' . $id . '/archive';
                    $token       = htmlspecialchars(session_id());
                    ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td>
                            <a href="<?= htmlspecialchars($detailUrl) ?>" style="text-decoration:none;">
                                <?= htmlspecialchars($item['user_name'] ?? $item['nama'] ?? '-') ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($item['nik'] ?? '-') ?></td>
                        <td><?= date('d M Y, H:i', strtotime($item['created_at'] ?? 'now')) ?></td>
                        <td>
                            <?php
                            $badge = match($statusValue) {
                                'Diproses' => 'status-diproses',
                                'Diterima' => 'status-diterima',
                                'Selesai'  => 'status-selesai',
                                'Ditolak'  => 'status-ditolak',
                                default    => '',
                            };
                            ?>
                            <span class="status <?= $badge ?>"><?= htmlspecialchars($statusValue) ?></span>
                        </td>
                        <td class="action-buttons">

                            <!-- Tombol Lihat Detail -->
                            <a href="<?= htmlspecialchars($detailUrl) ?>" class="btn btn-detail">
                                <i class="fas fa-eye"></i> Lihat Detail
                            </a>

                            <?php if ($statusValue === 'Diproses'): ?>
                                <!-- Tombol Terima -->
                                <form action="<?= htmlspecialchars($updateUrl) ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="_token" value="<?= $token ?>">
                                    <input type="hidden" name="status" value="Diterima">
                                    <button type="submit" class="btn btn-selesai"
                                        onclick="return confirm('Terima permohonan ini?')">
                                        <i class="fas fa-check"></i> Terima
                                    </button>
                                </form>
                                <!-- Tombol Tolak (buka modal) -->
                                <button type="button" class="btn btn-tolak"
                                    onclick="openTolakModal('<?= htmlspecialchars($updateUrl) ?>')">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                            <?php endif; ?>

                            <?php if ($statusValue === 'Diterima'): ?>
                                <!-- Tombol Buat Surat -->
                                <a href="<?= htmlspecialchars($templateUrl) ?>" class="btn btn-kirim-surat">
                                    <i class="fas fa-file-alt"></i> Buat Surat
                                </a>
                            <?php endif; ?>

                            <?php if (in_array($statusValue, ['Selesai', 'Ditolak'])): ?>
                                <!-- Tombol Arsipkan -->
                                <form action="<?= htmlspecialchars($archiveUrl) ?>" method="POST" style="display:inline;">
                                    <input type="hidden" name="_token" value="<?= $token ?>">
                                    <button type="submit" class="btn btn-arsip"
                                        onclick="return confirm('Arsipkan permohonan ini?')">
                                        <i class="fas fa-archive"></i> Arsipkan
                                    </button>
                                </form>
                            <?php endif; ?>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>