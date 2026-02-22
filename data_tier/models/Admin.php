<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * DATA TIER - Model Admin
 * Setara App\DataTier\Models\Admin (extends Authenticatable) di Laravel.
 * Tabel: admins
 */

class Admin extends BaseModel
{
    protected string $table = 'admins';

    protected array $fillable = [
        'nama',
        'email',
        'password',
        'role',
    ];

    // Kolom yang tidak dikembalikan ke view (setara $hidden Laravel)
    protected array $hidden = [
        'password',
        'remember_token',
    ];

    // Default value kolom (setara $attributes Laravel)
    protected array $attributes = [
        'role' => 'admin',
    ];

    // =========================================================
    //  QUERY SPESIFIK ADMIN
    // =========================================================

    /**
     * Cari admin berdasarkan email (untuk login)
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Verifikasi password admin (setara Auth::attempt di Laravel)
     */
    public function verifyPassword(string $inputPassword, string $hashedPassword): bool
    {
        return password_verify($inputPassword, $hashedPassword);
    }

    /**
     * Hash password sebelum disimpan
     */
    public function hashPassword(string $plainPassword): string
    {
        return password_hash($plainPassword, PASSWORD_BCRYPT);
    }

    /**
     * Update remember_token (untuk "remember me")
     */
    public function updateRememberToken(int $id, string $token): bool
    {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET remember_token = ? WHERE id = ?");
        return $stmt->execute([$token, $id]);
    }
}