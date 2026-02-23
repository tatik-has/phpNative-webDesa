<?php

define('ROOT_PATH', dirname(__DIR__, 2)); // naik ke /web-pengajuan/
require_once ROOT_PATH . '/data_tier/config/database.php';
require_once ROOT_PATH . '/data_tier/enums/StatusPermohonan.php';
require_once ROOT_PATH . '/data_tier/models/BaseModel.php';
require_once ROOT_PATH . '/data_tier/models/PermohonanDomisili.php';
require_once ROOT_PATH . '/data_tier/models/PermohonanKtm.php';
require_once ROOT_PATH . '/data_tier/models/PermohonanSKU.php';
require_once ROOT_PATH . '/data_tier/repositories/BaseRepository.php';
require_once ROOT_PATH . '/data_tier/repositories/PermohonanDomisiliRepository.php';
require_once ROOT_PATH . '/data_tier/repositories/PermohonanKTMRepository.php';
require_once ROOT_PATH . '/data_tier/repositories/PermohonanSKURepository.php';
require_once __DIR__ . '/ApiResponse.php';
require_once __DIR__ . '/ApiAuth.php';
require_once __DIR__ . '/controllers/PermohonanApiController.php';
require_once __DIR__ . '/controllers/RiwayatApiController.php';
require_once __DIR__ . '/controllers/NotificationApiController.php';

// ── CORS & Headers ──
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ── Routing ──
$method = $_SERVER['REQUEST_METHOD'];
$uri    = strtok($_SERVER['REQUEST_URI'], '?');

$basePath = '/web-pengajuan/api/v1';
if (str_starts_with($uri, $basePath)) {
    $uri = substr($uri, strlen($basePath));
}
$uri      = rtrim($uri ?: '/', '/') ?: '/';
$segments = array_values(array_filter(explode('/', $uri)));

try {
    route($method, $segments);
} catch (Throwable $e) {
    ApiResponse::error(500, 'Internal server error: ' . $e->getMessage());
}

function route(string $method, array $seg): void
{
    $s0    = $seg[0] ?? null;
    $s1    = $seg[1] ?? null;
    $s2    = $seg[2] ?? null;
    $s3    = $seg[3] ?? null;
    $types = ['domisili', 'ktm', 'sku'];

    // GET /health
    if ($method === 'GET' && $s0 === 'health') {
        ApiResponse::success(['status' => 'ok', 'timestamp' => date('c')], 'API berjalan normal.');
        return;
    }

    // GET /permohonan
    if ($method === 'GET' && $s0 === 'permohonan' && $s1 === null) {
        (new PermohonanApiController())->index();
        return;
    }

    // GET /permohonan/{type}
    if ($method === 'GET' && $s0 === 'permohonan' && in_array($s1, $types) && $s2 === null) {
        (new PermohonanApiController())->indexByType($s1);
        return;
    }

    // POST /permohonan/{type}
    if ($method === 'POST' && $s0 === 'permohonan' && in_array($s1, $types) && $s2 === null) {
        (new PermohonanApiController())->store($s1);
        return;
    }

    // GET /permohonan/{type}/{id}
    if ($method === 'GET' && $s0 === 'permohonan' && in_array($s1, $types) && is_numeric($s2) && $s3 === null) {
        (new PermohonanApiController())->show($s1, (int)$s2);
        return;
    }

    // PATCH /permohonan/{type}/{id}/status
    if ($method === 'PATCH' && $s0 === 'permohonan' && in_array($s1, $types) && is_numeric($s2) && $s3 === 'status') {
        (new PermohonanApiController())->updateStatus($s1, (int)$s2);
        return;
    }

    // GET /riwayat?nik=xxx
    if ($method === 'GET' && $s0 === 'riwayat') {
        (new RiwayatApiController())->index();
        return;
    }

    // GET /status/{type}/{id}
    if ($method === 'GET' && $s0 === 'status' && in_array($s1, $types) && is_numeric($s2)) {
        (new PermohonanApiController())->checkStatus($s1, (int)$s2);
        return;
    }

    // GET /notifications
    if ($method === 'GET' && $s0 === 'notifications') {
        (new NotificationApiController())->index();
        return;
    }

    // POST /notifications/mark-as-read
    if ($method === 'POST' && $s0 === 'notifications' && $s1 === 'mark-as-read') {
        (new NotificationApiController())->markAsRead();
        return;
    }

    ApiResponse::error(404, 'Endpoint tidak ditemukan.');
}