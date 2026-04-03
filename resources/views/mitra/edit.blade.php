@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Ubah Mitra</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $mitra->nama }}</p>
        </div>
        <a href="{{ route('mitra.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Kembali</a>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <form action="{{ route('mitra.update', $mitra) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm mb-1">Nama Mitra</label>
                    <input type="text" name="nama" required class="w-full border rounded-lg px-3 py-2" value="{{ old('nama', $mitra->nama) }}">
                </div>
                <div>
                    <label class="block text-sm mb-1">Email</label>
                    <input type="email" name="email" class="w-full border rounded-lg px-3 py-2" value="{{ old('email', $mitra->email) }}">
                </div>
                <div>
                    <label class="block text-sm mb-1">Alamat</label>
                    <input type="text" name="alamat" class="w-full border rounded-lg px-3 py-2" value="{{ old('alamat', $mitra->alamat) }}">
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Nomor Penawaran (Manual)</label>
                        <input type="text" name="nomor_penawaran" class="w-full border rounded-lg px-3 py-2" value="{{ old('nomor_penawaran', $mitra->nomor_penawaran) }}">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Nomor Invoice (Manual)</label>
                        <input type="text" name="nomor_invoice" class="w-full border rounded-lg px-3 py-2" value="{{ old('nomor_invoice', $mitra->nomor_invoice) }}">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Nomor Surat Jalan (Manual)</label>
                        <input type="text" name="nomor_surat_jalan" class="w-full border rounded-lg px-3 py-2" value="{{ old('nomor_surat_jalan', $mitra->nomor_surat_jalan) }}">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Nomor Berita Acara (Manual)</label>
                        <input type="text" name="nomor_berita_acara" class="w-full border rounded-lg px-3 py-2" value="{{ old('nomor_berita_acara', $mitra->nomor_berita_acara) }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Template Penawaran (PDF/PNG/JPG)</label>
                        <input type="file" name="template_penawaran" accept=".pdf,.png,.jpg,.jpeg" class="w-full border rounded-lg px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Saat ini: {{ $mitra->template_penawaran_path ? 'Ada' : '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Template Invoice (PDF/PNG/JPG)</label>
                        <input type="file" name="template_invoice" accept=".pdf,.png,.jpg,.jpeg" class="w-full border rounded-lg px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Saat ini: {{ $mitra->template_invoice_path ? 'Ada' : '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Template Surat Jalan (PDF/PNG/JPG)</label>
                        <input type="file" name="template_surat_jalan" accept=".pdf,.png,.jpg,.jpeg" class="w-full border rounded-lg px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Saat ini: {{ $mitra->template_surat_jalan_path ? 'Ada' : '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Template Berita Acara (PDF/PNG/JPG)</label>
                        <input type="file" name="template_berita_acara" accept=".pdf,.png,.jpg,.jpeg" class="w-full border rounded-lg px-3 py-2">
                        <p class="text-xs text-gray-500 mt-1">Saat ini: {{ $mitra->template_berita_acara_path ? 'Ada' : '-' }}</p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 flex justify-end gap-2">
                <a href="{{ route('mitra.index') }}" class="px-5 py-2 bg-gray-200 rounded-lg">Batal</a>
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection
