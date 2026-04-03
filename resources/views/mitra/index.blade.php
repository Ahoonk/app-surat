@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <h1 class="text-2xl font-semibold text-gray-800">Daftar Mitra</h1>
        <p class="text-sm text-gray-500 mt-1">Kelola mitra dan template surat yang digunakan.</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Tambah Mitra</h2>
        <form action="{{ route('mitra.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm mb-1">Nama Mitra</label>
                    <input type="text" name="nama" required class="w-full border rounded-lg px-3 py-2" value="{{ old('nama') }}">
                </div>
                <div>
                    <label class="block text-sm mb-1">Email</label>
                    <input type="email" name="email" class="w-full border rounded-lg px-3 py-2" value="{{ old('email') }}">
                </div>
                <div>
                    <label class="block text-sm mb-1">Alamat</label>
                    <input type="text" name="alamat" class="w-full border rounded-lg px-3 py-2" value="{{ old('alamat') }}">
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Nomor Penawaran (Manual)</label>
                        <input type="text" name="nomor_penawaran" class="w-full border rounded-lg px-3 py-2" value="{{ old('nomor_penawaran') }}">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Nomor Invoice (Manual)</label>
                        <input type="text" name="nomor_invoice" class="w-full border rounded-lg px-3 py-2" value="{{ old('nomor_invoice') }}">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Nomor Surat Jalan (Manual)</label>
                        <input type="text" name="nomor_surat_jalan" class="w-full border rounded-lg px-3 py-2" value="{{ old('nomor_surat_jalan') }}">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Nomor Berita Acara (Manual)</label>
                        <input type="text" name="nomor_berita_acara" class="w-full border rounded-lg px-3 py-2" value="{{ old('nomor_berita_acara') }}">
                    </div>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm mb-1">Template Penawaran (PDF/PNG/JPG)</label>
                        <input type="file" name="template_penawaran" accept=".pdf,.png,.jpg,.jpeg" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Template Invoice (PDF/PNG/JPG)</label>
                        <input type="file" name="template_invoice" accept=".pdf,.png,.jpg,.jpeg" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Template Surat Jalan (PDF/PNG/JPG)</label>
                        <input type="file" name="template_surat_jalan" accept=".pdf,.png,.jpg,.jpeg" class="w-full border rounded-lg px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Template Berita Acara (PDF/PNG/JPG)</label>
                        <input type="file" name="template_berita_acara" accept=".pdf,.png,.jpg,.jpeg" class="w-full border rounded-lg px-3 py-2">
                    </div>
                </div>
                <div class="text-xs text-gray-500">
                    Template PDF membutuhkan ekstensi <strong>php-imagick</strong> agar bisa dipakai sebagai background.
                </div>
            </div>

            <div class="lg:col-span-2 flex justify-end">
                <button type="submit" class="px-5 py-2 bg-blue-600 text-white rounded-lg">Simpan Mitra</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow p-6 overflow-x-auto">
        <h2 class="text-lg font-semibold mb-4">Daftar Mitra</h2>
        @if ($mitras->isEmpty())
            <p class="text-sm text-gray-500">Belum ada mitra.</p>
        @else
            <table class="w-full text-sm text-left">
                <thead>
                    <tr class="border-b">
                        <th class="py-3 pr-4">Nama</th>
                        <th class="py-3 pr-4">Kontak</th>
                        <th class="py-3 pr-4">Nomor Surat</th>
                        <th class="py-3 pr-4">Template</th>
                        <th class="py-3 pr-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mitras as $mitra)
                        <tr class="border-b">
                            <td class="py-3 pr-4">
                                <div class="font-medium">{{ $mitra->nama }}</div>
                                <div class="text-xs text-gray-500">{{ $mitra->alamat ?: '-' }}</div>
                            </td>
                            <td class="py-3 pr-4">
                                <div>{{ $mitra->email ?: '-' }}</div>
                            </td>
                            <td class="py-3 pr-4 text-xs">
                                <div>Penawaran: {{ $mitra->nomor_penawaran ?: '-' }}</div>
                                <div>Invoice: {{ $mitra->nomor_invoice ?: '-' }}</div>
                                <div>Surat Jalan: {{ $mitra->nomor_surat_jalan ?: '-' }}</div>
                                <div>Berita Acara: {{ $mitra->nomor_berita_acara ?: '-' }}</div>
                            </td>
                            <td class="py-3 pr-4 text-xs">
                                <div>Penawaran: {{ $mitra->template_penawaran_path ? 'Ada' : '-' }}</div>
                                <div>Invoice: {{ $mitra->template_invoice_path ? 'Ada' : '-' }}</div>
                                <div>Surat Jalan: {{ $mitra->template_surat_jalan_path ? 'Ada' : '-' }}</div>
                                <div>Berita Acara: {{ $mitra->template_berita_acara_path ? 'Ada' : '-' }}</div>
                            </td>
                            <td class="py-3 pr-4">
                                <div class="action-buttons">
                                    <a href="{{ route('mitra.edit', $mitra) }}" class="action-icon action-icon-blue" title="Ubah">
                                        &#9998;
                                    </a>
                                    <form action="{{ route('mitra.destroy', $mitra) }}" method="POST" onsubmit="return confirm('Hapus mitra ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="action-icon action-icon-red" title="Hapus">
                                            &#128465;
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
