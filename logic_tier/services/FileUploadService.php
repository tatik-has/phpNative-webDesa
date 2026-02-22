<?php

/**
 * LOGIC TIER - File Upload Service
 * Pengganti Storage::store() dan storeAs() di Laravel.
 */

class FileUploadService
{
    private static string $baseDir = __DIR__ . '/../../uploads/';

    /**
     * Upload file dari $_FILES array ke folder tertentu
     * Setara: $file->store('public/...') di Laravel
     *
     * @param array|null $file    — elemen dari $_FILES['field']
     * @param string     $folder  — subfolder di uploads/
     * @return string|null        — path relatif yang disimpan ke DB
     */
    public static function upload(?array $file, string $folder): ?string
    {
        if (!$file || empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $uploadDir = self::$baseDir . $folder . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid('', true) . '.' . $ext;
        $fullPath = $uploadDir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new RuntimeException("Gagal mengupload file ke {$fullPath}");
        }

        return "uploads/{$folder}/{$filename}";
    }

    /**
     * Hapus file dari path relatif
     * Setara: Storage::delete($path) di Laravel
     */
    public static function delete(?string $relativePath): void
    {
        if (!$relativePath) return;
        $fullPath = __DIR__ . '/../../' . $relativePath;
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
    }
}