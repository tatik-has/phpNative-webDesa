<?php

require_once __DIR__ . '/../../services/RiwayatSuratService.php';

class RiwayatSuratController
{
    private RiwayatSuratService $riwayatService;

    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->riwayatService = new RiwayatSuratService();
    }

    public function index(): void
    {
        // FIX: pakai NIK dari session, bukan user_id
        $nik     = $_SESSION['nik_pemohon'] ?? null;
        $riwayat = $nik ? $this->riwayatService->getRiwayatByNik($nik) : [];

        require_once __DIR__ . '/../../../presentation_tier/masyarakat/riwayat/index.php';
    }
}