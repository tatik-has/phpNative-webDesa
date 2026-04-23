<?php

class SuratTemplateService
{
    private string $logoPath = '/web-pengajuan/images/logo.png';
    private string $ttdPath  = '/web-pengajuan/assets/img/ttd-kepala-desa.png';

    private function imgToBase64(string $webPath): string
    {
        $absPath = $_SERVER['DOCUMENT_ROOT'] . $webPath;
        if (!file_exists($absPath)) return '';
        $ext  = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));
        $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
        $data = base64_encode(file_get_contents($absPath));
        return "data:{$mime};base64,{$data}";
    }

    public function generateTemplate(string $type, array $permohonan): string
    {
        return match ($type) {
            'domisili' => $this->templateDomisili($permohonan),
            'ktm'      => $this->templateKtm($permohonan),
            'sku'      => $this->templateSku($permohonan),
            default    => '<p>Jenis surat tidak dikenali.</p>',
        };
    }

    private function templateDomisili(array $p): string
    {
        $tanggal    = $this->formatTanggal(date('Y-m-d'));
        $nomorSurat = $this->generateNomor('SKD', $p['id']);
        $nama       = htmlspecialchars($p['nama'] ?? '');
        $nik        = htmlspecialchars($p['nik'] ?? '');
        $alamat     = htmlspecialchars($p['alamat_domisili'] ?? '');
        $rtRw       = 'RT ' . htmlspecialchars($p['rt_domisili'] ?? '') . ' / RW ' . htmlspecialchars($p['rw_domisili'] ?? '');
        $jk         = htmlspecialchars($p['jenis_kelamin'] ?? '');
        $alamatKtp  = htmlspecialchars($p['alamat_ktp'] ?? '');

        return $this->wrapSurat("
            " . $this->headerKop() . "
            <div class='judul-surat'>
                <h2>SURAT KETERANGAN DOMISILI</h2>
                <p>NOMOR: {$nomorSurat}</p>
            </div>
            <div class='isi-surat'>
                <p class='paragraf'>Kepala Desa Pakning Asal Kecamatan Bukit Batu Kabupaten Bengkalis dengan ini menerangkan :</p>
                <table class='tabel-data'>
                    <tr><td>NAMA</td><td>: {$nama}</td></tr>
                    <tr><td>NIK</td><td>: {$nik}</td></tr>
                    <tr><td>JENIS KELAMIN</td><td>: {$jk}</td></tr>
                    <tr><td>ALAMAT KTP</td><td>: {$alamatKtp}</td></tr>
                    <tr><td>ALAMAT DOMISILI</td><td>: {$alamat}, {$rtRw}</td></tr>
                </table>
                <p class='paragraf' style='margin-top: 20px;'>Adalah benar-benar warga yang berdomisili di wilayah Desa Pakning Asal sebagaimana alamat yang tertera di atas.</p>
                <p class='paragraf'>Demikianlah Surat Keterangan Domisili ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>
            </div>
            " . $this->blockTtd($tanggal) . "
        ");
    }

    private function templateKtm(array $p): string
    {
        $tanggal     = $this->formatTanggal(date('Y-m-d'));
        $nomorSurat  = $this->generateNomor('SKTM', $p['id']);
        $nama        = htmlspecialchars($p['nama'] ?? '');
        $nik         = htmlspecialchars($p['nik'] ?? '');
        $jk          = htmlspecialchars($p['jenis_kelamin'] ?? '');
        $alamat      = htmlspecialchars($p['alamat_lengkap'] ?? '');
        $keperluan   = htmlspecialchars($p['keperluan'] ?? '');
        $penghasilan = 'Rp ' . number_format((float)($p['penghasilan'] ?? 0), 0, ',', '.');
        $tanggungan  = htmlspecialchars($p['jumlah_tanggungan'] ?? '0');

        return $this->wrapSurat("
            " . $this->headerKop() . "
            <div class='judul-surat'>
                <h2>SURAT KETERANGAN TIDAK MAMPU</h2>
                <p>NOMOR: {$nomorSurat}</p>
            </div>
            <div class='isi-surat'>
                <p class='paragraf'>Kepala Desa Pakning Asal Kecamatan Bukit Batu Kabupaten Bengkalis dengan ini menerangkan :</p>
                <table class='tabel-data'>
                    <tr><td>NAMA</td><td>: {$nama}</td></tr>
                    <tr><td>NIK</td><td>: {$nik}</td></tr>
                    <tr><td>JENIS KELAMIN</td><td>: {$jk}</td></tr>
                    <tr><td>ALAMAT</td><td>: {$alamat}</td></tr>
                    <tr><td>PENGHASILAN PER BULAN</td><td>: {$penghasilan}</td></tr>
                    <tr><td>JUMLAH TANGGUNGAN</td><td>: {$tanggungan} orang</td></tr>
                    <tr><td>KEPERLUAN</td><td>: {$keperluan}</td></tr>
                </table>
                <p class='paragraf' style='margin-top: 20px;'>Adalah benar-benar tergolong keluarga tidak mampu dan memerlukan bantuan untuk keperluan {$keperluan}.</p>
                <p class='paragraf'>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>
            </div>
            " . $this->blockTtd($tanggal) . "
        ");
    }

    private function templateSku(array $p): string
    {
        $tanggal     = $this->formatTanggal(date('Y-m-d'));
        $nomorSurat  = $this->generateNomor('SKU', $p['id']);
        $nama        = htmlspecialchars($p['nama'] ?? '');
        $nik         = htmlspecialchars($p['nik'] ?? '');
        $alamatKtp   = htmlspecialchars($p['alamat_ktp'] ?? '');
        $namaUsaha   = htmlspecialchars($p['nama_usaha'] ?? '');
        $jenisUsaha  = htmlspecialchars($p['jenis_usaha'] ?? '');
        $alamatUsaha = htmlspecialchars($p['alamat_usaha'] ?? '');
        $lamaUsaha   = htmlspecialchars($p['lama_usaha'] ?? '');

        return $this->wrapSurat("
            " . $this->headerKop() . "
            <div class='judul-surat'>
                <h2>SURAT KETERANGAN USAHA</h2>
                <p>NOMOR: {$nomorSurat}</p>
            </div>
            <div class='isi-surat'>
                <p class='paragraf'>Kepala Desa Pakning Asal Kecamatan Bukit Batu Kabupaten Bengkalis dengan ini menerangkan :</p>
                <table class='tabel-data'>
                    <tr><td>NAMA</td><td>: {$nama}</td></tr>
                    <tr><td>NIK</td><td>: {$nik}</td></tr>
                    <tr><td>ALAMAT</td><td>: {$alamatKtp}</td></tr>
                    <tr><td>NAMA USAHA</td><td>: {$namaUsaha}</td></tr>
                    <tr><td>JENIS USAHA</td><td>: {$jenisUsaha}</td></tr>
                    <tr><td>ALAMAT USAHA</td><td>: {$alamatUsaha}</td></tr>
                    <tr><td>LAMA USAHA</td><td>: {$lamaUsaha}</td></tr>
                </table>
                <p class='paragraf' style='margin-top: 20px;'>Adalah benar-benar menjalankan usaha {$jenisUsaha} dengan nama usaha {$namaUsaha} yang berlokasi di {$alamatUsaha} dan telah berjalan selama {$lamaUsaha}.</p>
                <p class='paragraf'>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dapat dipergunakan sebagaimana mestinya.</p>
            </div>
            " . $this->blockTtd($tanggal) . "
        ");
    }

    private function headerKop(): string
    {
        $logoBase64 = $this->imgToBase64($this->logoPath);
        $logoTag    = $logoBase64
            ? "<img src='{$logoBase64}' alt='Logo' class='logo-desa'>"
            : "<div style='width:80px;height:80px;'></div>";

        return "
    <div class='kop-surat'>
        <div class='kop-logo'>{$logoTag}</div>
        <div class='kop-teks'>
            <p class='kop-provinsi'>PEMERINTAH KABUPATEN BENGKALIS</p>
            <p class='kop-kecamatan'>KECAMATAN BUKIT BATU</p>
            <p class='kop-desa'>KANTOR DESA PAKNING ASAL</p>
            <div class='kop-alamat-row'>
                <span>Jl. Sukajadi</span>
                <span>Kode Pos 28761</span>
            </div>
            <p class='kop-alamat'>Email: Pakningasal36@gmail.com, website pemdespakningasal.com</p>
        </div>
    </div>
    <hr class='garis-kop-atas'>
    <hr class='garis-kop-bawah'>
    ";
    }

    private function blockTtd(string $tanggal): string
    {
        $config    = $this->getKadesConfig();
        $namaKades = $config['nama_kades'] ?? '';
        $nipKades  = $config['nip_kades']  ?? '';

        $ttdBase64 = $this->imgToBase64($this->ttdPath);
        $ttdImg    = $ttdBase64
            ? "<img src='{$ttdBase64}' alt='Tanda Tangan' class='ttd-img'>"
            : "<div class='ttd-placeholder'><small style='color:#aaa;font-size:9pt;'>[TTD Kepala Desa]</small></div>";

        $namaTampil = !empty($namaKades)
            ? "<p>" . htmlspecialchars($namaKades) . "</p>"
            : "<p>_______________________</p>";

        $nipTampil = !empty($nipKades)
            ? "<p>NIP. " . htmlspecialchars($nipKades) . "</p>"
            : '';

        return "
    <div class='ttd-surat'>
        <p>{$tanggal}</p>
        <p>Kepala Desa Pakning Asal,</p>
        <div class='spasi-ttd'>{$ttdImg}</div>
        {$namaTampil}
        {$nipTampil}
    </div>
    ";
    }

    private function getKadesConfig(): array
    {
        $configFile = $_SERVER['DOCUMENT_ROOT'] . '/web-pengajuan/data_tier/config/kades.json';
        if (!file_exists($configFile)) return [];
        $config = json_decode(file_get_contents($configFile), true);
        return is_array($config) ? $config : [];
    }

    private function wrapSurat(string $content): string
    {
        return '
        <style>
            .surat-container {
                font-family: "Times New Roman", Times, serif;
                font-size: 12pt;
                width: 210mm;
                min-height: 297mm;
                margin: 0 auto;
                padding: 20mm 25mm 20mm 30mm;
                background: white;
                color: #000;
                line-height: 1.5;
                box-sizing: border-box;
            }
            .kop-surat {
                display: flex;
                align-items: center;
                gap: 16px;
                margin-bottom: 4px;
            }
            .logo-desa {
                width: 80px;
                height: 80px;
                object-fit: contain;
            }
            .kop-teks {
                flex: 1;
                text-align: center;
            }
            .kop-provinsi {
                font-size: 13pt;
                font-weight: normal;
                margin: 0;
                letter-spacing: 1px;
            }
            .kop-kecamatan {
                font-size: 12pt;
                font-weight: normal;
                margin: 0;
            }
            .kop-desa {
                font-size: 16pt;
                font-weight: bold;
                text-transform: uppercase;
                margin: 2px 0;
            }
            .kop-alamat-row {
                display: flex;
                justify-content: center;
                gap: 40px;
                font-size: 9pt;
                margin: 1px 0;
            }
            .kop-alamat {
                font-size: 9pt;
                margin: 1px 0;
                text-align: center;
            }
            .garis-kop-atas {
                border: 2.5px solid #000;
                margin: 6px 0 1px 0;
            }
            .garis-kop-bawah {
                border: 1px solid #000;
                margin: 0 0 16px 0;
            }
            .judul-surat {
                text-align: center;
                margin: 20px 0 16px 0;
            }
            .judul-surat h2 {
                font-size: 13pt;
                font-weight: bold;
                text-decoration: underline;
                text-transform: uppercase;
                margin-bottom: 4px;
            }
            .judul-surat p {
                margin: 0;
                font-size: 12pt;
                font-weight: normal;
                letter-spacing: 1px;
            }
            .isi-surat {
                margin: 16px 0;
            }
            .paragraf {
                text-align: justify;
                text-indent: 40px;
                margin: 10px 0;
                font-weight: normal;
            }
            .tabel-data {
                width: 90%;
                margin: 12px 0 12px 40px;
                border-collapse: collapse;
            }
            .tabel-data td {
                padding: 3px 6px;
                vertical-align: top;
                font-size: 12pt;
                font-weight: normal;
            }
            .tabel-data td:first-child {
                width: 200px;
                text-transform: uppercase;
            }
            .ttd-surat {
                margin-top: 30px;
                text-align: center;
                float: right;
                width: 220px;
                margin-right: 10px;
                font-weight: normal;
            }
            .ttd-surat p {
                margin: 2px 0;
                font-weight: normal;
            }
            .spasi-ttd {
                height: 80px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .ttd-img {
                max-height: 75px;
                max-width: 180px;
                object-fit: contain;
            }
            .ttd-placeholder {
                height: 75px;
                width: 180px;
            }
            @media print {
                body { margin: 0; }
                .surat-container {
                    width: 210mm;
                    min-height: 297mm;
                    padding: 20mm 25mm 20mm 30mm;
                    box-shadow: none;
                }
                .no-print { display: none !important; }
            }
        </style>
        <div class="surat-container">
            ' . $content . '
        </div>';
    }

    private function formatTanggal(string $date): string
    {
        $bulan = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];
        [$y, $m, $d] = explode('-', $date);
        return "Pakning Asal, {$d} {$bulan[$m]} {$y}";
    }

    // =========================================================
    //  GENERATE NOMOR SURAT OTOMATIS
    //  Format: 004/SKD/PA/2026
    //  - 004  : nomor urut surat selesai tahun ini (per jenis)
    //  - SKD  : kode jenis surat
    //  - PA   : singkatan Pakning Asal
    //  - 2026 : tahun saat ini (otomatis)
    // =========================================================
    private function generateNomor(string $kode, int $id): string
    {
        $tahun = date('Y');

        // Tentukan tabel DB sesuai jenis surat
        $tabel = match ($kode) {
            'SKD'  => 'permohonan_domisili',
            'SKTM' => 'permohonan_ktm',
            'SKU'  => 'permohonan_sku',
            default => null,
        };

        if (!$tabel) {
            return sprintf('001/%s/PA/%s', $kode, $tahun);
        }

        $db = Database::getInstance();

        // Hitung surat yang sudah Selesai tahun ini (selain surat ini sendiri)
        // lalu +1 = nomor urut surat ini
        $stmt = $db->prepare("
            SELECT COUNT(*)
            FROM {$tabel}
            WHERE status = 'Selesai'
              AND YEAR(updated_at) = :tahun
              AND id != :id
        ");
        $stmt->execute([':tahun' => $tahun, ':id' => $id]);
        $sudahSelesai = (int) $stmt->fetchColumn();

        $urutan    = $sudahSelesai + 1;
        $nomorUrut = str_pad($urutan, 3, '0', STR_PAD_LEFT); // 001, 002, 003, dst

        return sprintf('%s/%s/PA/%s', $nomorUrut, $kode, $tahun);
        // Contoh: 004/SKD/PA/2026
    }
}