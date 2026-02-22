<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * DATA TIER - Model Surat
 * Setara App\DataTier\Models\Surat di Laravel.
 * Tabel: surats
 */

class Surat extends BaseModel
{
    protected string $table = 'surats';

    protected array $fillable = [
        'user_id',
        'jenis_surat',
        'keterangan',
    ];

    // =========================================================
    //  QUERY SPESIFIK SURAT
    // =========================================================

    /**
     * Ambil semua surat milik user tertentu beserta data user
     * Setara: Surat::with('user')->where('user_id', $userId)->get()
     */
    public function getByUserIdWithUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, u.name AS user_name, u.email AS user_email, u.nik AS user_nik
            FROM {$this->table} s
            LEFT JOIN users u ON u.id = s.user_id
            WHERE s.user_id = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Ambil berdasarkan jenis surat
     */
    public function getByJenisSurat(string $jenisSurat): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, u.name AS user_name, u.email AS user_email
            FROM {$this->table} s
            LEFT JOIN users u ON u.id = s.user_id
            WHERE s.jenis_surat = ?
            ORDER BY s.created_at DESC
        ");
        $stmt->execute([$jenisSurat]);
        return $stmt->fetchAll();
    }
}