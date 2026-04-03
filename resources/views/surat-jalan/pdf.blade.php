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
    $invoice = $suratJalan->invoice;
    $penawaran = $invoice->penawaran;
    $tanggalCetakSource = $suratJalan->kota_tanggal_manual ?: $suratJalan->tanggal;
    $tanggalCetak = $tanggalCetakSource
        ? \Illuminate\Support\Carbon::parse($tanggalCetakSource)->translatedFormat('d F Y')
        : '-';
    $toDataUri = static function (string $path): ?string {
        if (!file_exists($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
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
        <div><strong>No Invoice:</strong> {{ $invoice->nomor }}</div>
        <div><strong>Customer:</strong> {{ $penawaran->to_company ?? $penawaran->customer_nama }}</div>
        <div><strong>Alamat:</strong> {{ $penawaran->to_address ?? '-' }}</div>
    </div>

    <div style="margin-top:16px;">
        <div>Bersama ini, saya yang bertanda tangan dibawah:</div>
        <table style="margin-top:8px;">
            <tr><td style="width:25%; border:0; padding:2px 0;">Nama</td><td style="border:0; padding:2px 0;">: {{ $suratJalan->pemberi_nama ?? 'Bayu Suderajat' }}</td></tr>
            <tr><td style="border:0; padding:2px 0;">Jabatan</td><td style="border:0; padding:2px 0;">: {{ $suratJalan->pemberi_jabatan ?? 'Direktur' }}</td></tr>
            <tr><td style="border:0; padding:2px 0;">Alamat</td><td style="border:0; padding:2px 0;">: {{ $suratJalan->pemberi_alamat ?? 'Perum Bukit Cilegon Asri, Blok BK No.09, Rt/Rw. 014/006, Kelurahan Bagendung, Kecamatan Cilegon' }}</td></tr>
        </table>
        <div style="margin-top:8px;">Memberikan kuasa kepada sebagai berikut:</div>
        <table style="margin-top:6px;">
            <tr><td style="width:25%; border:0; padding:2px 0;">Nama</td><td style="border:0; padding:2px 0;">: {{ $suratJalan->penerima_nama ?? '-' }}</td></tr>
            <tr><td style="border:0; padding:2px 0;">No. Handphone</td><td style="border:0; padding:2px 0;">: {{ $suratJalan->penerima_hp ?? '-' }}</td></tr>
        </table>
        <div style="margin-top:8px;">Untuk membawa barang milik "{{ $penawaran->to_company ?? $penawaran->customer_nama }}", dengan rincian:</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:8%;">No</th>
                <th style="width:62%;">Item</th>
                <th style="width:15%;">Qty</th>
                <th style="width:15%;">Satuan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penawaran->items as $item)
                <tr>
                    <td style="text-align:center;">{{ $loop->iteration }}</td>
                    <td>{{ $item->nama }}</td>
                    <td style="text-align:center;">{{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}</td>
                    <td style="text-align:center;">{{ strtoupper($item->satuan) }}</td>
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
