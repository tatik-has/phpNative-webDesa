<?php

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../enums/StatusPermohonan.php';

/**
 * DATA TIER - Model PermohonanDomisili
 * Setara App\DataTier\Models\PermohonanDomisili di Laravel.
 * Tabel: permohonan_domisili
 */

class PermohonanDomisili extends BaseModel
{
    protected string $table = 'permohonan_domisili';

    protected array $fillable = [
        'user_id',
        'nik',
        'nama',
        'alamat_domisili',
        'nomor_telp',
        'rt_domisili',
        'rw_domisili',
        'jenis_kelamin',
        'alamat_ktp',
        'path_ktp',
        'path_kk',
        'status',
        'keterangan_penolakan',
        'path_surat_jadi',
        'archived_at',
    ];

    // =========================================================
    //  QUERY SPESIFIK PERMOHONAN DOMISILI
    // =========================================================

    /**
     * Ambil permohonan berdasarkan user_id (untuk halaman user)
     * Setara: where('user_id', $userId)->whereNull('archived_at')->latest()->get()
     */
    public function getByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE user_id = ? AND archived_at IS NULL
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll();
        return array_map([$this, 'castRow'], $rows);
    }

    /**
     * Ambil permohonan berdasarkan status
     * Setara: where('status', $status)->whereNull('archived_at')->latest()->get()
     */
    public function getByStatus(string $status): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM {$this->table}
            WHERE status = ? AND archived_at IS NULL
            ORDER BY created_at DESC
        ");
        $stmt->execute([$status]);
        $rows = $stmt->fetchAll();
        return array_map([$this, 'castRow'], $rows);
    }

    /**
     * Override getAllWithUser agar status di-cast ke enum
     */
    public function getAllWithUser(): array
    {
        $rows = parent::getAllWithUser();
        return array_map([$this, 'castRow'], $rows);
    }

    /**
     * Override findWithUser agar status di-cast ke enum
     */
    public function findWithUser(int $id): ?array
    {
        $row = parent::findWithUser($id);
        return $row ? $this->castRow($row) : null;
    }
}