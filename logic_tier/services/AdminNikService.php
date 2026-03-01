<?php

/**
 * LOGIC TIER - AdminNikService
 * Mengelola data NIK warga di tabel users.
 * Warga yang terdaftar bisa login hanya dengan NIK.
 */

require_once __DIR__ . '/../../data_tier/config/database.php';

class AdminNikService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Ambil semua data warga yang terdaftar NIK-nya
     */
    public function getDaftarWarga(): array
    {
        $stmt = $this->db->query("
            SELECT id, name, nik, email, created_at
            FROM users
            WHERE nik IS NOT NULL AND nik != ''
            ORDER BY created_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Tambah satu warga (NIK + nama)
     * Return: true | 'duplicate' | false
     */
    public function tambahWarga(string $nik, string $nama): bool|string
    {
        // Cek apakah NIK sudah ada
        $cek = $this->db->prepare("SELECT id FROM users WHERE nik = ? LIMIT 1");
        $cek->execute([$nik]);
        if ($cek->fetch()) {
            return 'duplicate';
        }

        $stmt = $this->db->prepare("
            INSERT INTO users (name, nik, created_at, updated_at)
            VALUES (?, ?, NOW(), NOW())
        ");

        return $stmt->execute([$nama, $nik]);
    }

    /**
     * Import NIK dari file Excel/CSV
     * Format: kolom pertama = NIK, kolom kedua = Nama
     *
     * Return array: ['berhasil' => int, 'duplikat' => int, 'gagal' => int]
     */
    public function importDariExcel(string $tmpPath, string $ext): array
    {
        $rows = [];

        if ($ext === 'csv') {
            $rows = $this->parseCsv($tmpPath);
        } elseif (in_array($ext, ['xlsx', 'xls'])) {
            $rows = $this->parseExcel($tmpPath);
        }

        if (empty($rows)) {
            return ['error' => 'File kosong atau format tidak dapat dibaca. Gunakan format CSV jika Excel gagal.'];
        }

        $berhasil = 0;
        $duplikat = 0;
        $gagal    = 0;

        foreach ($rows as $index => $row) {
            // Skip baris header
            if ($index === 0) {
                $nikVal = strtolower(trim((string)($row[0] ?? '')));
                if ($nikVal === 'nik' || $nikVal === 'no_nik' || $nikVal === 'nomor_nik') {
                    continue;
                }
            }

            $nik  = trim((string)($row[0] ?? ''));
            $nama = trim((string)($row[1] ?? ''));

            // Validasi
            if (empty($nik) && empty($nama)) continue; // baris kosong
            if (!preg_match('/^\d{16}$/', $nik) || empty($nama)) {
                $gagal++;
                continue;
            }

            $result = $this->tambahWarga($nik, $nama);
            if ($result === 'duplicate') {
                $duplikat++;
            } elseif ($result === true) {
                $berhasil++;
            } else {
                $gagal++;
            }
        }

        return compact('berhasil', 'duplikat', 'gagal');
    }

    /**
     * Hapus data warga berdasarkan ID
     */
    public function hapusWarga(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // =========================================================
    //  PARSER HELPER
    // =========================================================

    /**
     * Parse file CSV → array of rows
     */
    private function parseCsv(string $path): array
    {
        $rows = [];
        if (($handle = fopen($path, 'r')) !== false) {
            // Deteksi dan buang BOM jika ada
            $bom = fread($handle, 3);
            if ($bom !== "\xEF\xBB\xBF") {
                rewind($handle);
            }

            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        return $rows;
    }

    /**
     * Parse file Excel (.xlsx/.xls) tanpa library eksternal
     * Menggunakan metode ZIP+XML untuk .xlsx
     * Fallback ke CSV parser untuk .xls (tidak support binary)
     */
    private function parseExcel(string $path): array
    {
        // Coba parse .xlsx (format ZIP+XML/OpenDocument)
        $rows = $this->parseXlsx($path);
        if (!empty($rows)) {
            return $rows;
        }

        // Fallback: coba baca sebagai CSV (kadang .xls disimpan sebagai CSV)
        return $this->parseCsv($path);
    }

    /**
     * Parse .xlsx menggunakan ZipArchive + SimpleXML (built-in PHP)
     */
    private function parseXlsx(string $path): array
    {
        if (!class_exists('ZipArchive')) return [];

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) return [];

        // Ambil shared strings (teks sel dalam xlsx)
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $ss = simplexml_load_string($ssXml);
            if ($ss) {
                foreach ($ss->si as $si) {
                    // Gabungkan semua <t> dalam satu <si>
                    $val = '';
                    foreach ($si->r as $r) {
                        $val .= (string)$r->t;
                    }
                    if (empty($val) && isset($si->t)) {
                        $val = (string)$si->t;
                    }
                    $sharedStrings[] = $val;
                }
            }
        }

        // Ambil sheet pertama
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();

        if (!$sheetXml) return [];

        $sheet = simplexml_load_string($sheetXml);
        if (!$sheet) return [];

        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $rowData = [];
            foreach ($row->c as $cell) {
                $type  = (string)$cell['t'];
                $value = (string)$cell->v;

                if ($type === 's') {
                    // shared string
                    $value = $sharedStrings[(int)$value] ?? '';
                } elseif ($type === 'str') {
                    // formula string
                    $value = (string)$cell->is->t ?? $value;
                }
                $rowData[] = $value;
            }
            if (!empty($rowData)) {
                $rows[] = $rowData;
            }
        }

        return $rows;
    }
}