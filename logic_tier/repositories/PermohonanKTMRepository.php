<?php

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../../data_tier/models/PermohonanKtm.php';

/**
 * LOGIC TIER - Repository PermohonanKTM
 * Setara App\DataTier\Repositories\PermohonanKTMRepository di Laravel.
 */

class PermohonanKTMRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new PermohonanKtm();
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