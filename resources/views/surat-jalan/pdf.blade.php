<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan - {{ $suratJalan->nomor }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 6px 8px; }
        th { text-align: center; }
    </style>
</head>
<body>
@php
    $snapshot = $suratJalan->snapshot_data ?? [];
    $invoice = $suratJalan->invoice;
    $penawaran = $invoice->penawaran;
    $tanggalCetakSource = data_get($snapshot, 'city_date_manual') ?: $suratJalan->kota_tanggal_manual ?: $suratJalan->tanggal;
    $tanggalCetak = $tanggalCetakSource
        ? \Illuminate\Support\Carbon::parse($tanggalCetakSource)->translatedFormat('d F Y')
        : '-';
    $toDataUri = static function (string $path): ?string {
        if (!file_exists($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            if (!class_exists(\Imagick::class)) {
                return null;
            }

            try {
                $imagick = new \Imagick();
                $imagick->setResolution(300, 300);
                $imagick->readImage($path . '[0]');
                $imagick->setImageFormat('png');

                return 'data:image/png;base64,' . base64_encode($imagick->getImageBlob());
            } catch (\Throwable $e) {
                report($e);
            }

            $gsBinary = trim((string) shell_exec('command -v gs 2>/dev/null'));
            if ($gsBinary !== '') {
                $tmpDir = storage_path('app/template-previews');
                if (!is_dir($tmpDir)) {
                    @mkdir($tmpDir, 0775, true);
                }

                $prefix = tempnam($tmpDir, 'pdf-');
                if ($prefix !== false) {
                    $pngPath = $prefix . '.png';
                    @unlink($prefix);

                    $cmd = escapeshellarg($gsBinary)
                        . ' -dSAFER -dBATCH -dNOPAUSE -sDEVICE=pngalpha -r300 -dFirstPage=1 -dLastPage=1 -sOutputFile='
                        . escapeshellarg($pngPath) . ' ' . escapeshellarg($path) . ' 2>&1';

                    $output = [];
                    $exitCode = 0;
                    @exec($cmd, $output, $exitCode);

                    if ($exitCode === 0 && file_exists($pngPath)) {
                        $binary = file_get_contents($pngPath);
                        @unlink($pngPath);

                        if ($binary !== false) {
                            return 'data:image/png;base64,' . base64_encode($binary);
                        }
                    }

                    @unlink($pngPath);
                }
            }

            return null;
        }

        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => null,
        };

        if (!$mime) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };
    $mitra = $penawaran->mitra;
    $mitraTemplatePath = $mitra?->template_surat_jalan_path
        ? public_path('storage/' . $mitra->template_surat_jalan_path)
        : null;
    $mitraTemplateAsset = $mitraTemplatePath ? $toDataUri($mitraTemplatePath) : null;
    $kopAtasAsset = $toDataUri(public_path('storage/logos/kopatas.png'));
    $kopBawahAsset = $toDataUri(public_path('storage/logos/kopbawah.png'));
    $bgAsset = $toDataUri(public_path('storage/logos/backgroud-template.png'))
        ?: $toDataUri(public_path('storage/logos/background-template.png'));
@endphp
    @if ($mitraTemplateAsset)
        <div style="position: fixed; inset: 0; background-image: url('{{ $mitraTemplateAsset }}'); background-repeat: no-repeat; background-position: top center; background-size: 100% 100%; z-index: 0;"></div>
    @else
        @if ($bgAsset)
            <div style="position: fixed; inset: 0; background-image: url('{{ $bgAsset }}'); background-repeat: no-repeat; background-position: center 36%; background-size: 50% auto; z-index: 0;"></div>
        @endif

        @if ($kopAtasAsset)
            <div style="position: fixed; top: -15mm; left: 0; right: 0; z-index: 1;">
                <img src="{{ $kopAtasAsset }}" alt="Kop Atas" style="width: 112%; margin-left: -6%; height: auto; display: block;">
            </div>
        @endif

        @if ($kopBawahAsset)
            <div style="position: fixed; bottom: 0; left: 0; right: 0; z-index: 1;">
                <img src="{{ $kopBawahAsset }}" alt="Kop Bawah" style="width: 100%; height: auto; display: block; transform: translateX(-3mm);">
            </div>
        @endif
    @endif

    <div style="padding-top: 145px; padding-bottom: 110px; position: relative; z-index: 2;">
    <div style="text-align:center;">
        <h2 style="margin:0;">SURAT JALAN</h2>
        <div style="margin-top:6px;">No: {{ $suratJalan->nomor }}</div>
    </div>

    <div style="margin-top:18px;">
        <div><strong>No Invoice:</strong> {{ data_get($snapshot, 'invoice_number', $invoice->nomor) }}</div>
        <div><strong>Customer:</strong> {{ data_get($snapshot, 'customer_name', $penawaran->to_company ?? $penawaran->customer_nama) }}</div>
        <div><strong>Alamat:</strong> {{ data_get($snapshot, 'customer_address', $penawaran->to_address ?? '-') }}</div>
    </div>

    <div style="margin-top:16px;">
        <div>Bersama ini, saya yang bertanda tangan dibawah:</div>
        <table style="margin-top:8px;">
            <tr><td style="width:25%; border:0; padding:2px 0;">Nama</td><td style="border:0; padding:2px 0;">: {{ data_get($snapshot, 'sender_name', $suratJalan->pemberi_nama ?? 'Bayu Suderajat') }}</td></tr>
            <tr><td style="border:0; padding:2px 0;">Jabatan</td><td style="border:0; padding:2px 0;">: {{ data_get($snapshot, 'sender_title', $suratJalan->pemberi_jabatan ?? 'Direktur') }}</td></tr>
            <tr><td style="border:0; padding:2px 0;">Alamat</td><td style="border:0; padding:2px 0;">: {{ data_get($snapshot, 'sender_address', $suratJalan->pemberi_alamat ?? 'Perum Bukit Cilegon Asri, Blok BK No.09, Rt/Rw. 014/006, Kelurahan Bagendung, Kecamatan Cilegon') }}</td></tr>
        </table>
        <div style="margin-top:8px;">Memberikan kuasa kepada sebagai berikut:</div>
        <table style="margin-top:6px;">
            <tr><td style="width:25%; border:0; padding:2px 0;">Nama</td><td style="border:0; padding:2px 0;">: {{ data_get($snapshot, 'receiver_name', $suratJalan->penerima_nama ?? '-') }}</td></tr>
            <tr><td style="border:0; padding:2px 0;">No. Handphone</td><td style="border:0; padding:2px 0;">: {{ data_get($snapshot, 'receiver_phone', $suratJalan->penerima_hp ?? '-') }}</td></tr>
        </table>
        <div style="margin-top:8px;">Untuk membawa barang milik "{{ data_get($snapshot, 'customer_name', $penawaran->to_company ?? $penawaran->customer_nama) }}", dengan rincian:</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:8%;">No</th>
                <th style="width:77%;">Description</th>
                <th style="width:15%;">Qty</th>
            </tr>
        </thead>
        <tbody>
            @foreach (data_get($snapshot, 'items', $penawaran->items) as $item)
                @php
                    $rincian = data_get($item, 'rincian');
                    $rincianLines = preg_split('/\r\n|\r|\n/', trim((string) $rincian));
                    $rincianLines = array_values(array_filter(array_map('trim', $rincianLines ?: []), fn ($line) => $line !== ''));
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $loop->iteration }}</td>
                    <td>
                        <div style="font-weight:700;">{{ data_get($item, 'nama') }}</div>
                        @if (!empty($rincianLines))
                            <div style="margin-top:6px; font-size:11px; line-height:1.5; color:#444;">
                                @foreach ($rincianLines as $line)
                                    <div style="display:flex; gap:6px; margin-bottom:2px;">
                                        <span style="flex:0 0 auto;">&#8226;</span>
                                        <span>{{ $line }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td style="text-align:center;">{{ rtrim(rtrim(number_format((float) data_get($item, 'qty', 0), 2, '.', ''), '0'), '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 28px;">
        <div>Demikian, surat jalan ini dibuat agar dilaksanakan sebaik-baiknya dan sebagaimana mestinya.</div>
        <div style="margin-top: 28px; width: 40%; margin-left: auto; margin-right: 0; text-align: center;">
            <div>Kota Cilegon, <span>{{ $tanggalCetak }}</span></div>
            <div>Direktur</div>
            <div style="height: 80px;"></div>
            <div><strong>Bayu Suderajat</strong></div>
        </div>
    </div>
    </div>
</body>
</html>
