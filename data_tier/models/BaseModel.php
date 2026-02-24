<?php

require_once __DIR__ . '/../config/database.php';



abstract class BaseModel
{
    protected PDO $db;

    /** Nama tabel di database — wajib diisi di child class */
    protected string $table;

    /** Kolom yang boleh diisi (setara $fillable di Laravel) */
    protected array $fillable = [];

    /** Kolom yang disembunyikan saat toArray() (setara $hidden di Laravel) */
    protected array $hidden = [];

    /** Nilai default kolom (setara $attributes di Laravel) */
    protected array $attributes = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    
    public function all(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    /**
     * Cari berdasarkan ID (setara Model::find($id))
     */
    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cari berdasarkan ID atau lempar exception (setara findOrFail)
     */
    public function findOrFail(int $id): array
    {
        $result = $this->find($id);
        if (!$result) {
            throw new \RuntimeException("Record dengan ID {$id} tidak ditemukan di tabel {$this->table}.");
        }
        return $result;
    }

    /**
     * Ambil data dengan relasi user (JOIN ke tabel users)
     * Setara Model::with('user')->...
     */
    public function findWithUser(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, u.name AS user_name, u.email AS user_email, u.nik AS user_nik
            FROM {$this->table} p
            LEFT JOIN users u ON u.id = p.user_id
            WHERE p.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Ambil semua data non-arsip dengan relasi user
     * Setara Model::with('user')->whereNull('archived_at')->latest()->get()
     */
    public function getAllWithUser(): array
    {
        $stmt = $this->db->query("
            SELECT p.*, u.name AS user_name, u.email AS user_email, u.nik AS user_nik
            FROM {$this->table} p
            LEFT JOIN users u ON u.id = p.user_id
            WHERE p.archived_at IS NULL
            ORDER BY p.created_at DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Buat data baru (setara Model::create($data))
     */
    public function create(array $data): int
    {
        // Merge dengan default attributes
        $data = array_merge($this->attributes, $data);

        // Filter hanya kolom yang ada di fillable
        $data = $this->filterFillable($data);

        // Tambah timestamp
        $now  = date('Y-m-d H:i:s');
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        $columns      = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute(array_values($data));

        return (int) $this->db->lastInsertId();
    }

    /**
     * Update data (setara $model->update($data))
     */
    public function update(int $id, array $data): bool
    {
        $data = $this->filterFillable($data);
        $data['updated_at'] = date('Y-m-d H:i:s');

        $setParts = array_map(fn($col) => "{$col} = ?", array_keys($data));
        $setClause = implode(', ', $setParts);

        $values   = array_values($data);
        $values[] = $id;

        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$setClause} WHERE id = ?");
        return $stmt->execute($values);
    }

    /**
     * Arsipkan data (soft delete setara archived_at = now())
     */
    public function archive(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET archived_at = ? WHERE id = ?");
        return $stmt->execute([date('Y-m-d H:i:s'), $id]);
    }

    /**
     * Hapus permanen (setara $model->delete())
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // =========================================================
    //  UTILITY
    // =========================================================

    /**
     * Filter array hanya kolom yang ada di $fillable
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) return $data;
        return array_intersect_key($data, array_flip($this->fillable));
    }

    /**
     * Sembunyikan kolom $hidden dari hasil (setara $hidden di Laravel)
     */
    public function hideFields(array $row): array
    {
        foreach ($this->hidden as $field) {
            unset($row[$field]);
        }
        return $row;
    }

    /**
     * Cast status ke string aman
     * BUG FIX: Sebelumnya mengembalikan StatusPermohonan enum object.
     * View PHP native mengharapkan string biasa ('Diproses', dll),
     * sehingga perbandingan $item['status'] === 'Diproses' harus bekerja.
     */
    protected function castStatus(?string $value): ?string
    {
        if ($value === null) return null;
        // Validasi nilai enum, kembalikan string
        $enum = StatusPermohonan::tryFrom($value);
        return $enum?->value ?? $value;
    }

    /**
     * Cast row — pastikan 'status' tetap string
     */
    public function castRow(array $row): array
    {
        if (isset($row['status'])) {
            // Jika sudah enum object (dari castRow sebelumnya), ambil value-nya
            if ($row['status'] instanceof \BackedEnum) {
                $row['status'] = $row['status']->value;
            } else {
                $row['status'] = $this->castStatus((string)$row['status']);
            }
        }
        return $row;
    }
}