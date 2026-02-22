<?php

enum StatusPermohonan: string
{
    case DIPROSES  = 'Diproses';
    case DITERIMA  = 'Diterima';
    case SELESAI   = 'Selesai';
    case DITOLAK   = 'Ditolak';

    /**
     * Label human-readable untuk ditampilkan di view
     */
    public function label(): string
    {
        return match($this) {
            self::DIPROSES => 'Sedang Diproses',
            self::DITERIMA => 'Diterima (Sedang Dibuat)',
            self::SELESAI  => 'Selesai',
            self::DITOLAK  => 'Ditolak',
        };
    }

    /**
     * Warna badge Bootstrap untuk presentation tier
     */
    public function badgeColor(): string
    {
        return match($this) {
            self::DIPROSES => 'warning',
            self::DITERIMA => 'info',
            self::SELESAI  => 'success',
            self::DITOLAK  => 'danger',
        };
    }

    /**
     * CSS class untuk tampilan di view
     */
    public function cssClass(): string
    {
        return match($this) {
            self::DIPROSES => 'status-diproses',
            self::DITERIMA => 'status-diterima',
            self::SELESAI  => 'status-selesai',
            self::DITOLAK  => 'status-ditolak',
        };
    }

    /**
     * Buat dari string (pengganti casting Laravel)
     * Aman: tidak throw exception jika nilai tidak dikenal
     */
    public static function fromString(string $value): self
    {
        return self::from($value);
    }

    /**
     * Coba buat dari string, kembalikan null jika tidak valid
     */
    public static function tryFromString(string $value): ?self
    {
        return self::tryFrom($value);
    }
}
