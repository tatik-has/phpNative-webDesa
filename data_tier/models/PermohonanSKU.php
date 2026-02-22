<?php

require_once __DIR__ . '/BaseModel.php';
require_once __DIR__ . '/../enums/StatusPermohonan.php';

/**
 * DATA TIER - Model PermohonanSKU
 * Setara App\DataTier\Models\PermohonanSKU di Laravel.
 * Tabel: permohonan_sku
 */

class PermohonanSKU extends BaseModel
{
    protected string $table = 'permohonan_sku';

    protected array $fillable = [
        'user_id',
        'nik',
        'nama',
        'alamat_ktp',
        'nomor_telp',
        'nama_usaha',
        'jenis_usaha',
        'alamat_usaha',
        'lama_usaha',
        'path_ktp',
        'path_kk',
        'path_surat_pengantar',
        'path_foto_usaha',
        'status',
        'keterangan_penolakan',
        'path_surat_jadi',
        'archived_at',
    ];

    // =========================================================
    //  QUERY SPESIFIK PERMOHONAN SKU
    // =========================================================

    /**
     * Ambil permohonan berdasarkan user_id
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