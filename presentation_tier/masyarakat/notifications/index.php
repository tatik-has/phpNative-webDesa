<?php
/**
 * PRESENTATION TIER - Notifikasi Masyarakat
 * Pengganti: presentation_tier/masyarakat/notifications/index.blade.php
 * Perubahan:
 *   $notifications->count() → count($notifications)
 *   @forelse → foreach + empty check
 *   $notification->data['pesan'] → array sudah di-decode controller
 *   $notification->created_at->diffForHumans() → fungsi manual
 *   @method('DELETE') → <input type="hidden" name="_method" value="DELETE">
 */

$pageTitle = 'Notifikasi Anda';
$extraCss = ['/web-pengajuan/presentation_tier/css/shared/notifications.css'];

// Helper: hitung "x waktu lalu"
function diffForHumans(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)        return $diff . ' detik yang lalu';
    if ($diff < 3600)      return floor($diff / 60) . ' menit yang lalu';
    if ($diff < 86400)     return floor($diff / 3600) . ' jam yang lalu';
    if ($diff < 2592000)   return floor($diff / 86400) . ' hari yang lalu';
    return date('d M Y', strtotime($datetime));
}

ob_start();

$notifications = $notifications ?? [];
$totalNotif    = count($notifications);
?>

<main class="notif-container">
    <div class="notif-header">
        <h1>Notifikasi Anda</h1>
        <?php if ($totalNotif > 0): ?>
            <form action="/web-pengajuan/notifications/delete-all" method="POST"
                  onsubmit="return confirm('Yakin ingin menghapus semua notifikasi?')">
                <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit" class="btn-delete-all">
                    <i class="fas fa-trash-alt"></i> Hapus Semua
                </button>
            </form>
        <?php endif; ?>
    </div>

    <div class="notif-list">
        <?php if (empty($notifications)): ?>
            <div class="no-notif">
                <i class="fas fa-bell-slash"></i>
                <p>Tidak ada notifikasi saat ini.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification):
                // data bisa berupa JSON string atau array (sudah di-decode di controller)
                $data  = is_string($notification['data'] ?? null)
                       ? json_decode($notification['data'], true)
                       : ($notification['data'] ?? []);
                $pesan = htmlspecialchars($data['pesan'] ?? 'Tidak ada pesan');
                $waktu = diffForHumans($notification['created_at'] ?? '');
                $id    = $notification['id'] ?? '';
            ?>
            <div class="notif-item">
                <div class="notif-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>

                <div class="notif-content">
                    <p class="message"><?= $pesan ?></p>
                    <span class="time"><?= $waktu ?></span>
                </div>

                <div class="notif-action">
                    <form action="/web-pengajuan/notifications/<?= htmlspecialchars($id) ?>/delete" method="POST"
                          style="display:inline;">
                        <input type="hidden" name="_token" value="<?= htmlspecialchars(session_id()) ?>">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" class="btn-delete"
                            onclick="return confirm('Yakin ingin menghapus notifikasi ini?')">
                            <i class="fas fa-trash-alt"></i> Hapus
                        </button>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>

<?php $content = ob_get_clean();
require __DIR__ . '/../layout.php';