<?php

class ApiAuth
{
    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            ApiResponse::error(401, 'Unauthorized. API key admin tidak valid atau tidak ditemukan.');
        }
    }

    public static function isAdmin(): bool
    {
        $providedKey = self::extractBearerToken();
        if ($providedKey === null) return false;

        $validKey = self::getAdminApiKey();
        return hash_equals($validKey, $providedKey);
    }

    private static function extractBearerToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION']
               ?? apache_request_headers()['Authorization']
               ?? null;

        if ($header === null) return null;

        if (str_starts_with($header, 'Bearer ')) {
            return trim(substr($header, 7));
        }

        return null;
    }

    private static function getAdminApiKey(): string
    {
        // 1. Dari environment variable (rekomendasi production)
        $envKey = getenv('API_ADMIN_KEY');
        if ($envKey !== false && $envKey !== '') {
            return $envKey;
        }

        // 2. Dari file config (fallback development)
        $configFile = ROOT_PATH . '/data_tier/config/api_keys.php';
        if (file_exists($configFile)) {
            $keys = require $configFile;
            if (!empty($keys['admin'])) return $keys['admin'];
        }

        // 3. Default key development — GANTI DI PRODUCTION!
        return 'dev-api-key-12345-GANTI-INI';
    }

    public static function rateLimit(int $maxRequests = 60, int $windowSeconds = 60): void
    {
        $ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'rate_limit_' . md5($ip);

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $now    = time();
        $window = $_SESSION[$key] ?? ['count' => 0, 'start' => $now];

        if ($now - $window['start'] > $windowSeconds) {
            $window = ['count' => 0, 'start' => $now];
        }

        $window['count']++;
        $_SESSION[$key] = $window;

        if ($window['count'] > $maxRequests) {
            http_response_code(429);
            header('Retry-After: ' . ($windowSeconds - ($now - $window['start'])));
            ApiResponse::error(429, 'Terlalu banyak request. Coba lagi nanti.');
        }
    }
}