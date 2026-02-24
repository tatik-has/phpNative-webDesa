<?php

class PengajuanSuratController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
    }

    public function showPengajuanForm(): void
    {
        require_once __DIR__ . '/../../../presentation_tier/masyarakat/permohonan/pengajuan.php';
    }
}
