@extends('layouts.app')

@section('content')
@php
    $invoice = $suratJalan->invoice;
    $penawaran = $invoice->penawaran;
    $tanggalTtd = $suratJalan->kota_tanggal_manual
        ? \Illuminate\Support\Carbon::parse($suratJalan->kota_tanggal_manual)->format('Y-m-d')
        : \Illuminate\Support\Carbon::parse($suratJalan->tanggal)->format('Y-m-d');
@endphp
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Pengaturan Surat Jalan</h1>
            <p class="text-sm text-gray-500 mt-1">Isi data dulu, lalu lanjut ke preview.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('surat-jalan.show', $suratJalan) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Lihat Preview</a>
            <a href="{{ route('surat-jalan.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Kembali</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <form method="POST" action="{{ route('surat-jalan.update', $suratJalan) }}" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                <div>
                    <label class="block mb-1">Nama Pihak Penerima</label>
                    <input type="text" name="penerima_nama" value="{{ old('penerima_nama', $suratJalan->penerima_nama) }}" class="w-full border rounded-lg px-3 py-2 text-black">
                </div>
                <div>
                    <label class="block mb-1">No. Handphone</label>
                    <input type="text" name="penerima_hp" value="{{ old('penerima_hp', $suratJalan->penerima_hp) }}" class="w-full border rounded-lg px-3 py-2 text-black">
                </div>
                <div>
                    <label class="block mb-1">Nama Penandatangan</label>
                    <input type="text" name="pemberi_nama" value="{{ old('pemberi_nama', $suratJalan->pemberi_nama ?? 'Bayu Suderajat') }}" class="w-full border rounded-lg px-3 py-2 text-black">
                </div>
                <div>
                    <label class="block mb-1">Jabatan Penandatangan</label>
                    <input type="text" name="pemberi_jabatan" value="{{ old('pemberi_jabatan', $suratJalan->pemberi_jabatan ?? 'Direktur') }}" class="w-full border rounded-lg px-3 py-2 text-black">
                </div>
                <div class="md:col-span-2">
                    <label class="block mb-1">Alamat Penandatangan</label>
                    <input type="text" name="pemberi_alamat" value="{{ old('pemberi_alamat', $suratJalan->pemberi_alamat ?? 'Perum Bukit Cilegon Asri, Blok BK No.09, Rt/Rw. 014/006, Kelurahan Bagendung, Kecamatan Cilegon') }}" class="w-full border rounded-lg px-3 py-2 text-black">
                </div>
                <div>
                    <label class="block mb-1">Tanggal Cetak TTD</label>
                    <input type="date" name="kota_tanggal_manual" value="{{ old('kota_tanggal_manual', $tanggalTtd) }}" class="w-full border rounded-lg px-3 py-2 text-black">
                </div>
            </div>

            <div class="pt-2 flex justify-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan Keterangan</button>
            </div>
        </form>

        <div class="mt-6 border-t pt-4 text-sm text-gray-600">
            <p><strong>Nomor Surat Jalan:</strong> {{ $suratJalan->nomor }}</p>
            <p><strong>No Invoice:</strong> {{ $invoice->nomor }}</p>
            <p><strong>Customer:</strong> {{ $penawaran->to_company ?? $penawaran->customer_nama }}</p>
        </div>
    </div>
</div>
@endsection
