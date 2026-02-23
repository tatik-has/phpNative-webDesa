<?php

class PermohonanApiController
{
    private PermohonanDomisiliRepository $domisiliRepo;
    private PermohonanKTMRepository      $ktmRepo;
    private PermohonanSKURepository      $skuRepo;

    public function __construct()
    {
        $this->domisiliRepo = new PermohonanDomisiliRepository();
        $this->ktmRepo      = new PermohonanKTMRepository();
        $this->skuRepo      = new PermohonanSKURepository();
    }

    /** GET /api/v1/permohonan — semua permohonan (admin) */
    public function index(): void
    {
        ApiAuth::requireAdmin();

        $status   = $_GET['status'] ?? null;
        $domisili = $this->getAllFromRepo($this->domisiliRepo, 'domisili', $status);
        $ktm      = $this->getAllFromRepo($this->ktmRepo, 'ktm', $status);
        $sku      = $this->getAllFromRepo($this->skuRepo, 'sku', $status);

        $all = array_merge($domisili, $ktm, $sku);
        usort($all, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        if ($jenis = ($_GET['jenis'] ?? null)) {
            $all = array_values(array_filter($all, fn($item) => $item['type'] === $jenis));
        }

        ApiResponse::list($all, count($all), 'Daftar semua permohonan berhasil diambil.');
    }

    /** GET /api/v1/permohonan/{type} — per jenis (admin) */
    public function indexByType(string $type): void
    {
        ApiAuth::requireAdmin();

        $repo   = $this->getRepo($type);
        $status = $_GET['status'] ?? null;
        $items  = $this->getAllFromRepo($repo, $type, $status);

        ApiResponse::list($items, count($items), "Daftar permohonan {$type} berhasil diambil.");
    }

    /** GET /api/v1/permohonan/{type}/{id} — detail (admin) */
    public function show(string $type, int $id): void
    {
        ApiAuth::requireAdmin();

        $item = $this->getRepo($type)->findWithUser($id);
        if (!$item) {
            ApiResponse::error(404, "Permohonan {$type} dengan ID {$id} tidak ditemukan.");
        }

        $item['type'] = $type;
        ApiResponse::success($item, 'Detail permohonan berhasil diambil.');
    }

    /** POST /api/v1/permohonan/{type} — ajukan permohonan (publik) */
    public function store(string $type): void
    {
        ApiAuth::rateLimit(10, 60);

        $body   = $this->getJsonBody();
        $errors = $this->validateRequired($body, ['nama', 'nik', 'alamat', 'no_hp']);
        $errors = array_merge($errors, $this->validateRequired($body, $this->getTypeRequiredFields($type)));

        if (!empty($body['nik']) && !preg_match('/^\d{16}$/', $body['nik'])) {
            $errors[] = 'NIK harus 16 digit angka.';
        }

        if (!empty($errors)) {
            ApiResponse::error(422, 'Validasi gagal.', $errors);
        }

        $data = array_merge($body, ['status' => 'Diproses']);
        unset($data['type'], $data['jenis_surat']);

        try {
            $repo    = $this->getRepo($type);
            $id      = $repo->create($data);
            $created = $repo->findOrFail($id);
            $created['type'] = $type;
            ApiResponse::success($created, "Permohonan {$type} berhasil diajukan.", 201);
        } catch (\Exception $e) {
            ApiResponse::error(500, 'Gagal menyimpan permohonan: ' . $e->getMessage());
        }
    }

    /** PATCH /api/v1/permohonan/{type}/{id}/status — update status (admin) */
    public function updateStatus(string $type, int $id): void
    {
        ApiAuth::requireAdmin();

        $repo = $this->getRepo($type);
        $body = $this->getJsonBody();

        $validStatuses = ['Diproses', 'Diterima', 'Selesai', 'Ditolak'];
        $status        = $body['status'] ?? null;

        if (!in_array($status, $validStatuses)) {
            ApiResponse::error(422, 'Status tidak valid.', [
                'status' => 'Harus salah satu dari: ' . implode(', ', $validStatuses),
            ]);
        }

        if ($status === 'Ditolak' && empty($body['keterangan_penolakan'])) {
            ApiResponse::error(422, 'Keterangan penolakan wajib diisi jika status Ditolak.');
        }

        $updateData = ['status' => $status];
        if ($status === 'Ditolak') {
            $updateData['keterangan_penolakan'] = $body['keterangan_penolakan'];
        }

        $repo->update($id, $updateData);

        $updated         = $repo->findOrFail($id);
        $updated['type'] = $type;
        ApiResponse::success($updated, "Status berhasil diperbarui menjadi '{$status}'.");
    }

    /** GET /api/v1/status/{type}/{id} — cek status (publik) */
    public function checkStatus(string $type, int $id): void
    {
        $item = $this->getRepo($type)->findOrFail($id);
        if (!$item) {
            ApiResponse::error(404, 'Permohonan tidak ditemukan.');
        }

        ApiResponse::success([
            'id'                   => $item['id'],
            'jenis_surat'          => $this->getJenisSurat($type),
            'status'               => $item['status'],
            'keterangan_penolakan' => $item['keterangan_penolakan'] ?? null,
            'created_at'           => $item['created_at'],
            'updated_at'           => $item['updated_at'],
        ], 'Status permohonan berhasil diambil.');
    }

    // ── Helpers ──

    private function getRepo(string $type): object
    {
        return match($type) {
            'domisili' => $this->domisiliRepo,
            'ktm'      => $this->ktmRepo,
            'sku'      => $this->skuRepo,
            default    => throw new \InvalidArgumentException("Jenis '{$type}' tidak valid."),
        };
    }

    private function getAllFromRepo(object $repo, string $type, ?string $status): array
    {
        $items = $status ? $repo->getByStatus($status) : $repo->getAllWithUser();
        return array_map(fn($item) => array_merge($item, ['type' => $type]), $items);
    }

    private function getJsonBody(): array
    {
        $body = json_decode(file_get_contents('php://input'), true);
        return (json_last_error() === JSON_ERROR_NONE && $body) ? $body : $_POST;
    }

    private function validateRequired(array $data, array $fields): array
    {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[] = "Field '{$field}' wajib diisi.";
            }
        }
        return $errors;
    }

    private function getTypeRequiredFields(string $type): array
    {
        return match($type) {
            'domisili' => ['keperluan'],
            'ktm'      => ['nama_ayah', 'penghasilan_ortu'],
            'sku'      => ['nama_usaha', 'jenis_usaha'],
            default    => [],
        };
    }

    private function getJenisSurat(string $type): string
    {
        return match($type) {
            'domisili' => 'Surat Keterangan Domisili',
            'ktm'      => 'Surat Keterangan Tidak Mampu (SKTM)',
            'sku'      => 'Surat Keterangan Usaha (SKU)',
            default    => 'Surat',
        };
    }
}