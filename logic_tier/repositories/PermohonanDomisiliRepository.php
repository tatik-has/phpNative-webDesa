<?php

require_once __DIR__ . '/BaseRepository.php';
require_once __DIR__ . '/../../data_tier/models/PermohonanDomisili.php';

/**
 * LOGIC TIER - Repository PermohonanDomisili
 * Setara App\DataTier\Repositories\PermohonanDomisiliRepository di Laravel.
 */

class PermohonanDomisiliRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new PermohonanDomisili();
    }

    /**
     * Ambil permohonan milik user tertentu (non-arsip)
     * Setara: where('user_id', $userId)->whereNull('archived_at')->latest()->get()
     */
    public function getByUserId(int $userId): array
    {
        return $this->model->getByUserId($userId);
    }

    /**
     * Ambil permohonan berdasarkan status
     * Setara: where('status', $status)->whereNull('archived_at')->latest()->get()
     */
    public function getByStatus(string $status): array
    {
        return $this->model->getByStatus($status);
    }
}