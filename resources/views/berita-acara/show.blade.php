@extends('layouts.app')

@section('content')
@php
    $invoice = $beritaAcara->invoice;
    $penawaran = $invoice->penawaran;
    $po = $invoice->purchasingOrder;
    $mitra = $penawaran->mitra;
    $mitraTemplatePath = $mitra?->template_berita_acara_path
        ? public_path('storage/' . $mitra->template_berita_acara_path)
        : null;
    $mitraTemplateAsset = $mitraTemplatePath && file_exists($mitraTemplatePath)
        ? asset('storage/' . $mitra->template_berita_acara_path) . '?v=' . filemtime($mitraTemplatePath)
        : null;
    $kopAtasAsset = file_exists(public_path('storage/logos/kopatas.png')) ? asset('storage/logos/kopatas.png') : null;
    $kopBawahAsset = file_exists(public_path('storage/logos/kopbawah.png')) ? asset('storage/logos/kopbawah.png') : null;
    $bgPrimaryPath = public_path('storage/logos/backgroud-template.png');
    $bgFallbackPath = public_path('storage/logos/background-template.png');
    $bgAsset = null;
    if (file_exists($bgPrimaryPath)) {
        $bgAsset = asset('storage/logos/backgroud-template.png') . '?v=' . filemtime($bgPrimaryPath);
    } elseif (file_exists($bgFallbackPath)) {
        $bgAsset = asset('storage/logos/background-template.png') . '?v=' . filemtime($bgFallbackPath);
    }
    $previewPaperStyle = 'width:100%;max-width:794px;min-height:1123px;';
    $previewContentStyle = 'padding:170px 15mm 110px 15mm;position:relative;z-index:2;';
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
@endphp
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
        <h1 class="text-2xl font-semibold text-gray-800">Preview Berita Acara</h1>
        <div class="flex gap-2">
            <a href="{{ route('berita-acara.edit', $beritaAcara) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg">Ubah Data</a>
            <a href="{{ route('berita-acara.pdf', ['beritaAcara' => $beritaAcara, 'download' => 1]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Export PDF</a>
            <a href="{{ route('berita-acara.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Kembali</a>
        </div>
    </div>

    <div class="mx-auto bg-white rounded-2xl shadow-xl relative overflow-hidden" style="{{ $previewPaperStyle }}">
        @if ($mitraTemplateAsset)
            <div style="position:absolute;inset:0;background-image:url('{{ $mitraTemplateAsset }}');background-repeat:no-repeat;background-position:center;background-size:100% 100%;z-index:0;"></div>
        @else
            @if ($bgAsset)
                <div style="position:absolute;inset:0;background-image:url('{{ $bgAsset }}');background-repeat:no-repeat;background-position:center 36%;background-size:50% auto;z-index:0;"></div>
            @endif
            @if ($kopAtasAsset)
                <img src="{{ $kopAtasAsset }}" alt="Kop Atas" style="position:absolute;top:-8mm;left:0;right:0;width:106%;margin-left:-1%;height:auto;display:block;z-index:1;">
            @endif
            @if ($kopBawahAsset)
                <img src="{{ $kopBawahAsset }}" alt="Kop Bawah" style="position:absolute;bottom:0;left:0;right:0;width:100%;height:auto;display:block;z-index:1;">
            @endif
        @endif

        <div style="{{ $previewContentStyle }}">
        <div class="max-w-4xl mx-auto text-[16px] leading-7">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold underline mb-1">Berita Acara</h2>
                <p>Nomor : {{ $beritaAcara->nomor }}</p>
                <p>Perihal : {{ $beritaAcara->perihal ?: '-' }}</p>
            </div>

            <p>Pada hari ini, {{ $tanggalDeskriptif }},&nbsp;&nbsp;yang bertanda tangan dibawah ini</p>

            <div class="mt-4 ml-8">
                <p><span class="inline-block w-10 align-top">I.</span><span class="inline-block w-20 align-top">Nama</span><span class="inline-block w-3 align-top">:</span><span class="inline-block align-top w-[calc(100%-8.5rem)]">{{ $penawaran->to_company ?? $penawaran->customer_nama }}</span></p>
                <p><span class="inline-block w-10 align-top"></span><span class="inline-block w-20 align-top">Alamat</span><span class="inline-block w-3 align-top">:</span><span class="inline-block align-top w-[calc(100%-8.5rem)]">{{ $penawaran->to_address ?? '-' }}</span></p>
                <p class="mt-1">Yang selanjutnya disebut <strong>PIHAK PERTAMA</strong></p>
            </div>

            <div class="mt-4 ml-8">
                <p><span class="inline-block w-10 align-top">II.</span><span class="inline-block w-20 align-top">Nama</span><span class="inline-block w-3 align-top">:</span><span class="inline-block align-top w-[calc(100%-8.5rem)]">PT Aldera Saddatech Karya</span></p>
                <p><span class="inline-block w-10 align-top"></span><span class="inline-block w-20 align-top">Alamat</span><span class="inline-block w-3 align-top">:</span><span class="inline-block align-top w-[calc(100%-8.5rem)]">Link. Acing Baru RT 001 RW 007, Kelurahan Masigit, Kecamatan Jombang, Kota Cilegon, Provinsi Banten.</span></p>
                <p class="mt-1">Yang selanjutnya disebut <strong>PIHAK KEDUA</strong></p>
            </div>

            <p class="mt-6">
                Berdasarkan Surat Perjanjian Kerjasama Nomor : {{ $po->nomor_po ?? '-' }}, PIHAK KEDUA telah
                melaksanakan pekerjaan untuk PIHAK PERTAMA {{ $beritaAcara->keterangan_akhir ?: 'sesuai kesepakatan para pihak.' }}
            </p>

            <p class="mt-3">Demikian Berita Acara ini dibuat dan dapat digunakan sebagai mana mestinya.</p>

            <div class="mt-16 flex justify-between">
                <div class="text-center w-1/2 font-semibold">PIHAK PERTAMA</div>
                <div class="text-center w-1/2">
                    <p class="font-semibold">PIHAK KEDUA</p>
                    <div class="h-20"></div>
                    <p class="font-semibold underline">Bayu Suderajat</p>
                    <p>Direktur</p>
                </div>
            </div>
        </div>
        </div>
    </div>
</div>
@endsection
