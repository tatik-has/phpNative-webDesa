<?php

/**
 * DATA TIER - Database Configuration
 * Mengelola koneksi PDO ke database MySQL
 */

class Database
{
    private static ?PDO $instance = null;

    private static string $host     = 'localhost';
    private static string $dbname   = 'sistem_surat_desa'; // ganti sesuai db kamu
    private static string $username = 'root';
    private static string $password = '';
    private static string $charset  = 'utf8mb4';

    /**
     * Singleton - hanya buat satu koneksi
     */
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . self::$host
                 . ";dbname=" . self::$dbname
                 . ";charset=" . self::$charset;

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            self::$instance = new PDO($dsn, self::$username, self::$password, $options);
        }

        return self::$instance;
    }

    // Cegah instantiasi langsung
    private function __construct() {}
    private function __clone() {}
}