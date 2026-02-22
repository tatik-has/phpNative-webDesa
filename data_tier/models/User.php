<?php

require_once __DIR__ . '/BaseModel.php';

/**
 * DATA TIER - Model User
 * Setara App\DataTier\Models\User (extends Authenticatable) di Laravel.
 * Tabel: users (default Laravel)
 */

class User extends BaseModel
{
    protected string $table = 'users';

    protected array $fillable = [
        'name',
        'nik',
        'desa',
        'alamat',
        'email',
        'password',
        'verification_code',
        'is_verified',
    ];

    protected array $hidden = [
        'password',
        'remember_token',
    ];

    // =========================================================
    //  QUERY SPESIFIK USER
    // =========================================================

    /**
     * Cari user berdasarkan email (untuk login)
     */
    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cari user berdasarkan NIK
     */
    public function findByNik(string $nik): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE nik = ? LIMIT 1");
        $stmt->execute([$nik]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Verifikasi password user
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
     * Verifikasi akun user dengan kode verifikasi
     */
    public function verifyAccount(string $verificationCode): bool
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET is_verified = 1, verification_code = NULL, updated_at = ?
            WHERE verification_code = ?
        ");
        return $stmt->execute([date('Y-m-d H:i:s'), $verificationCode]);
    }
}