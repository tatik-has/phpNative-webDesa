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
     * Otomatis deteksi posisi kolom NIK dan Nama dari header.
     * Mendukung urutan: (NIK, Nama) ATAU (Nama, NIK) — tidak perlu ubah file Excel.
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
            return ['error' => 'File kosong atau format tidak dapat dibaca.'];
        }

        // ── Deteksi posisi kolom dari baris header ──────────────────
        $nikCol  = 0; // default kolom A = NIK
        $namaCol = 1; // default kolom B = Nama
        $mulaiDari = 0;

        $headerRow = $rows[0];
        $header0   = strtolower(trim((string)($headerRow[0] ?? '')));
        $header1   = strtolower(trim((string)($headerRow[1] ?? '')));

        $nikKeywords  = ['nik', 'no_nik', 'nomor_nik', 'no nik', 'nomor nik', 'no. nik'];
        $namaKeywords = ['nama', 'name', 'nama lengkap', 'full name'];

        $isHeader = in_array($header0, $nikKeywords)  || in_array($header0, $namaKeywords)
                 || in_array($header1, $nikKeywords)  || in_array($header1, $namaKeywords);

        if ($isHeader) {
            // Baris pertama adalah header, skip saat proses data
            $mulaiDari = 1;

            // Cek apakah urutan terbalik: kolom A = Nama, kolom B = NIK
            if (in_array($header0, $namaKeywords) && in_array($header1, $nikKeywords)) {
                $namaCol = 0;
                $nikCol  = 1;
            }
            // Urutan normal: kolom A = NIK, kolom B = Nama (default sudah benar)
        }
        // ────────────────────────────────────────────────────────────

        $berhasil = 0;
        $duplikat = 0;
        $gagal    = 0;

        foreach ($rows as $index => $row) {
            if ($index < $mulaiDari) continue;

            $nik  = trim((string)($row[$nikCol]  ?? ''));
            $nama = trim((string)($row[$namaCol] ?? ''));

            // Skip baris kosong
            if (empty($nik) && empty($nama)) continue;

            // Bersihkan NIK dari karakter non-angka (misal spasi atau titik)
            $nik = preg_replace('/\D/', '', $nik);

            // Validasi: NIK harus 16 digit angka dan nama tidak boleh kosong
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
     * Perbaikan: limit baris 0 = unlimited (sebelumnya 1000 karakter)
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
            // 0 = unlimited panjang per baris
            while (($data = fgetcsv($handle, 0, ',')) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }
        return $rows;
    }

    private function parseExcel(string $path): array
    {
        $rows = $this->parseXlsx($path);
        if (!empty($rows)) return $rows;
        // Fallback: coba baca sebagai CSV
        return $this->parseCsv($path);
    }

    /**
     * Parse .xlsx menggunakan ZipArchive + SimpleXML (built-in PHP)
     * Perbaikan utama: pakai referensi kolom asli (A1, B1) bukan posisi urut
     * sehingga sel kosong tidak menyebabkan kolom geser.
     */
    private function parseXlsx(string $path): array
    {
        if (!class_exists('ZipArchive')) return [];

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) return [];

        // Ambil shared strings
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml) {
            $ss = simplexml_load_string($ssXml);
            if ($ss) {
                foreach ($ss->si as $si) {
                    $val = '';
                    // Teks langsung di <t>
                    if (isset($si->t)) {
                        $val = (string)$si->t;
                    }
                    // Rich text dalam <r><t>
                    foreach ($si->r as $r) {
                        $val .= (string)$r->t;
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
                // Ambil referensi sel, contoh: A1, B2, AA3
                $cellRef = (string)$cell['r'];
                preg_match('/^([A-Z]+)/', $cellRef, $colMatch);
                // Konversi huruf kolom ke index (A=0, B=1, ...)
                $colIndex = $this->colLetterToIndex($colMatch[1] ?? 'A');

                $type  = (string)$cell['t'];
                $value = (string)$cell->v;

                if ($type === 's') {
                    // Shared string
                    $value = $sharedStrings[(int)$value] ?? '';
                } elseif ($type === 'str') {
                    // Formula yang menghasilkan string
                    $value = (string)($cell->v ?? '');
                } elseif ($type === 'inlineStr') {
                    $value = (string)($cell->is->t ?? '');
                }

                // Taruh di index kolom yang benar — ini yang mencegah kolom geser
                $rowData[$colIndex] = $value;
            }

            if (!empty($rowData)) {
                // Normalisasi: pastikan kolom 0 (NIK) dan 1 (Nama) selalu ada
                $rows[] = [
                    0 => $rowData[0] ?? '',
                    1 => $rowData[1] ?? '',
                ];
            }
        }

        return $rows;
    }

    /**
     * Konversi huruf kolom Excel ke index angka
     * A=0, B=1, Z=25, AA=26, dst.
     */
    private function colLetterToIndex(string $col): int
    {
        $col   = strtoupper($col);
        $index = 0;
        $len   = strlen($col);
        for ($i = 0; $i < $len; $i++) {
            $index = $index * 26 + (ord($col[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }
}