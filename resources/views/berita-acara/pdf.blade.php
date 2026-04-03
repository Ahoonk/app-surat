<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara - {{ $beritaAcara->nomor }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; line-height: 1.7; }
        .center { text-align: center; }
        .w-no { display: inline-block; width: 28px; vertical-align: top; }
        .w-label { display: inline-block; width: 62px; vertical-align: top; }
        .w-colon { display: inline-block; width: 10px; vertical-align: top; }
        .w-value { display: inline-block; width: calc(100% - 120px); vertical-align: top; }
    </style>
</head>
<body>
@php
    $invoice = $beritaAcara->invoice;
    $penawaran = $invoice->penawaran;
    $po = $invoice->purchasingOrder;
    $tanggalSource = $beritaAcara->kota_tanggal_manual ?: $beritaAcara->tanggal;
    $tanggalObj = \Illuminate\Support\Carbon::parse($tanggalSource);
    $hariMap = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu',
    ];
    $bulanMap = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];
    $terbilang = function (int $angka) use (&$terbilang): string {
        $angka = abs($angka);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($angka < 12) return $huruf[$angka];
        if ($angka < 20) return $terbilang($angka - 10) . ' Belas';
        if ($angka < 100) return $terbilang(intdiv($angka, 10)) . ' Puluh' . ($angka % 10 ? ' ' . $terbilang($angka % 10) : '');
        if ($angka < 200) return 'Seratus' . ($angka - 100 ? ' ' . $terbilang($angka - 100) : '');
        if ($angka < 1000) return $terbilang(intdiv($angka, 100)) . ' Ratus' . ($angka % 100 ? ' ' . $terbilang($angka % 100) : '');
        if ($angka < 2000) return 'Seribu' . ($angka - 1000 ? ' ' . $terbilang($angka - 1000) : '');
        if ($angka < 1000000) return $terbilang(intdiv($angka, 1000)) . ' Ribu' . ($angka % 1000 ? ' ' . $terbilang($angka % 1000) : '');
        return (string) $angka;
    };
    $tanggalDeskriptif = sprintf(
        '%s, %s %s %s',
        $hariMap[$tanggalObj->englishDayOfWeek] ?? $tanggalObj->translatedFormat('l'),
        $terbilang((int) $tanggalObj->day),
        $bulanMap[(int) $tanggalObj->month],
        $terbilang((int) $tanggalObj->year)
    );
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
    $mitraTemplatePath = $mitra?->template_berita_acara_path
        ? public_path('storage/' . $mitra->template_berita_acara_path)
        : null;
    $mitraTemplateAsset = $mitraTemplatePath ? $toDataUri($mitraTemplatePath) : null;
    $kopAtasAsset = $toDataUri(public_path('storage/logos/kopatas.png'));
    $kopBawahAsset = $toDataUri(public_path('storage/logos/kopbawah.png'));
    $bgAsset = $toDataUri(public_path('storage/logos/backgroud-template.png'))
        ?: $toDataUri(public_path('storage/logos/background-template.png'));
@endphp

@if ($mitraTemplateAsset)
    <div style="position: fixed; inset: 0; background-image: url('{{ $mitraTemplateAsset }}'); background-repeat: no-repeat; background-position: center; background-size: 100% 100%; z-index: 0;"></div>
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
    <div class="center" style="margin-bottom: 30px;">
        <h2 style="margin: 0 0 4px; text-decoration: underline;">Berita Acara</h2>
        <div>Nomor : {{ $beritaAcara->nomor }}</div>
        <div>Perihal : {{ $beritaAcara->perihal ?: '-' }}</div>
    </div>

    <p>Pada hari ini, {{ $tanggalDeskriptif }},&nbsp;&nbsp;yang bertanda tangan dibawah ini</p>

    <div style="margin-left: 22px; margin-top: 8px;">
        <div><span class="w-no">I.</span><span class="w-label">Nama</span><span class="w-colon">:</span><span class="w-value">{{ $penawaran->to_company ?? $penawaran->customer_nama }}</span></div>
        <div><span class="w-no"></span><span class="w-label">Alamat</span><span class="w-colon">:</span><span class="w-value">{{ $penawaran->to_address ?? '-' }}</span></div>
        <div style="margin-top: 4px;">Yang selanjutnya disebut <strong>PIHAK PERTAMA</strong></div>
    </div>

    <div style="margin-left: 22px; margin-top: 12px;">
        <div><span class="w-no">II.</span><span class="w-label">Nama</span><span class="w-colon">:</span><span class="w-value">PT Aldera Saddatech Karya</span></div>
        <div><span class="w-no"></span><span class="w-label">Alamat</span><span class="w-colon">:</span><span class="w-value">Link. Acing Baru RT 001 RW 007, Kelurahan Masigit, Kecamatan Jombang, Kota Cilegon, Provinsi Banten.</span></div>
        <div style="margin-top: 4px;">Yang selanjutnya disebut <strong>PIHAK KEDUA</strong></div>
    </div>

    <p style="margin-top: 16px;">
        Berdasarkan Surat Perjanjian Kerjasama Nomor : {{ $po->nomor_po ?? '-' }}, PIHAK KEDUA telah
        melaksanakan pekerjaan untuk PIHAK PERTAMA {{ $beritaAcara->keterangan_akhir ?: 'sesuai kesepakatan para pihak.' }}
    </p>

    <p>Demikian Berita Acara ini dibuat dan dapat digunakan sebagai mana mestinya.</p>

    <div style="margin-top: 70px; display: table; width: 100%;">
        <div style="display: table-cell; width: 50%; text-align: center;"><strong>PIHAK PERTAMA</strong></div>
        <div style="display: table-cell; width: 50%; text-align: center;">
            <strong>PIHAK KEDUA</strong>
            <div style="height: 85px;"></div>
            <div><strong><u>Bayu Suderajat</u></strong></div>
            <div>Direktur</div>
        </div>
    </div>
</div>
</body>
</html>
