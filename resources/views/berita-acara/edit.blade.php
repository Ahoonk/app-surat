@extends('layouts.app')

@section('content')
@php
    $invoice = $beritaAcara->invoice;
    $penawaran = $invoice->penawaran;
    $tanggalTtd = $beritaAcara->kota_tanggal_manual
        ? \Illuminate\Support\Carbon::parse($beritaAcara->kota_tanggal_manual)->format('Y-m-d')
        : \Illuminate\Support\Carbon::parse($beritaAcara->tanggal)->format('Y-m-d');
@endphp
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Pengaturan Berita Acara</h1>
            <p class="text-sm text-gray-500 mt-1">Isi perihal dan keterangan dulu, lalu lanjut ke preview.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('berita-acara.show', $beritaAcara) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Lihat Preview</a>
            <a href="{{ route('berita-acara.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Kembali</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <form method="POST" action="{{ route('berita-acara.update', $beritaAcara) }}" class="grid grid-cols-1 md:grid-cols-2 gap-3 items-end">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm mb-1">Perihal</label>
                <input type="text" name="perihal" value="{{ old('perihal', $beritaAcara->perihal) }}" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm mb-1">Keterangan Kalimat Akhir</label>
                <input type="text" name="keterangan_akhir" value="{{ old('keterangan_akhir', $beritaAcara->keterangan_akhir) }}" class="w-full border rounded-lg px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm mb-1">Tanggal Cetak TTD</label>
                <input type="date" name="kota_tanggal_manual" value="{{ old('kota_tanggal_manual', $tanggalTtd) }}" class="w-full border rounded-lg px-3 py-2">
            </div>
            <div class="md:col-span-2 text-right">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
            </div>
        </form>

        <div class="mt-6 border-t pt-4 text-sm text-gray-600">
            <p><strong>Nomor:</strong> {{ $beritaAcara->nomor }}</p>
            <p><strong>No Invoice:</strong> {{ $invoice->nomor }}</p>
            <p><strong>Customer:</strong> {{ $penawaran->to_company ?? $penawaran->customer_nama }}</p>
        </div>
    </div>
</div>
@endsection
