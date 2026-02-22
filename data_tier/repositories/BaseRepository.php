<?php

/**
 * LOGIC TIER - Base Repository
 * Setara App\DataTier\Repositories\BaseRepository di Laravel.
 * 
 * Repository adalah jembatan antara Logic Tier dan Data Tier.
 * Semua akses ke Model dilakukan lewat Repository — bukan langsung dari Controller.
 *
 * Perbedaan dengan Model:
 *   - Model  → tahu cara query ke database
 *   - Repository → tahu KAPAN dan DATA APA yang diambil sesuai kebutuhan bisnis
 */

abstract class BaseRepository
{
    /** Instance model yang digunakan oleh repository ini */
    protected object $model;

    // =========================================================
    //  OPERASI UMUM (diwarisi semua child repository)
    // =========================================================

    /**
     * Ambil semua data dengan relasi user (non-arsip)
     * Setara: $this->model::with('user')->whereNull('archived_at')->latest()->get()
     */
    public function getAllWithUser(): array
    {
        return $this->model->getAllWithUser();
    }

    /**
     * Cari data berdasarkan ID dengan relasi user
     * Setara: $this->model::with('user')->find($id)
     */
    public function findWithUser(int $id): ?array
    {
        return $this->model->findWithUser($id);
    }

    /**
     * Cari berdasarkan ID atau lempar exception
     * Setara: $this->model::with('user')->findOrFail($id)
     */
    public function findOrFail(int $id): array
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Buat data baru
     * Setara: $this->model::create($data) — mengembalikan ID baru
     */
    public function create(array $data): int
    {
        return $this->model->create($data);
    }

    /**
     * Update data berdasarkan ID
     * Setara: $model->update($data)
     */
    public function update(int $id, array $data): bool
    {
        return $this->model->update($id, $data);
    }

    /**
     * Arsipkan data (soft delete dengan archived_at)
     * Setara: $model->archived_at = now(); $model->save()
     */
    public function archive(int $id): bool
    {
        return $this->model->archive($id);
    }

    /**
     * Hapus permanen dari database
     * Setara: $model->delete()
     */
    public function delete(int $id): bool
    {
        return $this->model->delete($id);
    }
}