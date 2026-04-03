@extends('layouts.app')

@section('content')
@php
    $invoice = $suratJalan->invoice;
    $penawaran = $invoice->penawaran;
    $mitra = $penawaran->mitra;
    $mitraTemplatePath = $mitra?->template_surat_jalan_path
        ? public_path('storage/' . $mitra->template_surat_jalan_path)
        : null;
    $mitraTemplateAsset = $mitraTemplatePath && file_exists($mitraTemplatePath)
        ? asset('storage/' . $mitra->template_surat_jalan_path) . '?v=' . filemtime($mitraTemplatePath)
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
    $tanggalCetakSource = $suratJalan->kota_tanggal_manual ?: $suratJalan->tanggal;
    $tanggalCetak = $tanggalCetakSource
        ? \Illuminate\Support\Carbon::parse($tanggalCetakSource)->translatedFormat('d F Y')
        : '-';
    $previewPaperStyle = 'width:100%;max-width:794px;min-height:1123px;';
    $previewContentStyle = 'padding:170px 15mm 110px 15mm;position:relative;z-index:2;';
@endphp
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
        <h1 class="text-2xl font-semibold text-gray-800">Preview Surat Jalan</h1>
        <div class="flex gap-2">
            <a href="{{ route('surat-jalan.edit', $suratJalan) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-lg">Ubah Data</a>
            <a href="{{ route('surat-jalan.pdf', ['suratJalan' => $suratJalan, 'download' => 1]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Export PDF</a>
            <a href="{{ route('surat-jalan.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Kembali</a>
        </div>
    </div>

    <div class="mx-auto bg-white rounded-2xl shadow-xl relative overflow-hidden" style="{{ $previewPaperStyle }}">
        @if ($mitraTemplateAsset)
            <div style="position:absolute;inset:0;background-image:url('{{ $mitraTemplateAsset }}');background-repeat:no-repeat;background-position:top center;background-size:100% 100%;z-index:0;"></div>
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
            <div class="text-center mb-6">
                <h2 class="text-xl font-bold uppercase">Surat Jalan</h2>
                <p>No: {{ $suratJalan->nomor }}</p>
            </div>

            <div class="mb-4 text-sm">
                <p><strong>No Invoice:</strong> {{ $invoice->nomor }}</p>
                <p><strong>Customer:</strong> {{ $penawaran->to_company ?? $penawaran->customer_nama }}</p>
                <p><strong>Alamat:</strong> {{ $penawaran->to_address ?? '-' }}</p>
            </div>

            <div class="space-y-4 text-sm">
                <div>
                    <p>Bersama ini, saya yang bertanda tangan dibawah:</p>
                    <table class="mt-2">
                        <tr>
                            <td class="w-40 align-top">Nama</td>
                            <td class="w-3 align-top">:</td>
                            <td class="align-top">{{ $suratJalan->pemberi_nama ?? 'Bayu Suderajat' }}</td>
                        </tr>
                        <tr>
                            <td class="w-40 align-top">Jabatan</td>
                            <td class="w-3 align-top">:</td>
                            <td class="align-top">{{ $suratJalan->pemberi_jabatan ?? 'Direktur' }}</td>
                        </tr>
                        <tr>
                            <td class="w-40 align-top">Alamat</td>
                            <td class="w-3 align-top">:</td>
                            <td class="align-top">{{ $suratJalan->pemberi_alamat ?? 'Perum Bukit Cilegon Asri, Blok BK No.09, Rt/Rw. 014/006, Kelurahan Bagendung, Kecamatan Cilegon' }}</td>
                        </tr>
                    </table>
                </div>

                <div>
                    <p>Memberikan kuasa kepada sebagai berikut:</p>
                    <table class="mt-2">
                        <tr>
                            <td class="w-40 align-top">Nama</td>
                            <td class="w-3 align-top">:</td>
                            <td class="align-top">{{ $suratJalan->penerima_nama ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="w-40 align-top">No. Handphone</td>
                            <td class="w-3 align-top">:</td>
                            <td class="align-top">{{ $suratJalan->penerima_hp ?? '-' }}</td>
                        </tr>
                    </table>
                </div>

                <p>Untuk membawa barang milik "{{ $penawaran->to_company ?? $penawaran->customer_nama }}", dengan rincian:</p>
            </div>

            <div class="overflow-x-auto mt-4">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-2 pr-3">No</th>
                            <th class="py-2 pr-3">Item</th>
                            <th class="py-2 pr-3">Qty</th>
                            <th class="py-2 pr-3">Satuan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($penawaran->items as $item)
                            <tr class="border-b">
                                <td class="py-2 pr-3">{{ $loop->iteration }}</td>
                                <td class="py-2 pr-3">{{ $item->nama }}</td>
                                <td class="py-2 pr-3">{{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}</td>
                                <td class="py-2 pr-3">{{ strtoupper($item->satuan) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-8 text-sm">
                <p>Demikian, surat jalan ini dibuat agar dilaksanakan sebaik-baiknya dan sebagaimana mestinya.</p>
                <div class="mt-8 flex justify-end">
                    <div class="text-center">
                        <p>Kota Cilegon, {{ $tanggalCetak }}</p>
                        <p>Direktur</p>
                        <div class="h-16"></div>
                        <p class="font-semibold">{{ $suratJalan->pemberi_nama ?? 'Bayu Suderajat' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
