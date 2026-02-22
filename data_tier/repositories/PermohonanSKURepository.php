<?php

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../models/PermohonanSKU.php';

/**
 * LOGIC TIER - Repository PermohonanSKU
 * Setara App\DataTier\Repositories\PermohonanSKURepository di Laravel.
 */

class PermohonanSKURepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new PermohonanSKU();
    }

    /**
     * Ambil permohonan milik user tertentu (non-arsip)
     */
    public function getByUserId(int $userId): array
    {
        return $this->model->getByUserId($userId);
    }

    /**
     * Ambil permohonan berdasarkan status
     */
    public function getByStatus(string $status): array
    {
        return $this->model->getByStatus($status);
    }
}