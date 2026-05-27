@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Master Data</p>
                <h1 class="section-title mt-2">Ubah Mitra</h1>
                <p class="mt-3 text-sm text-slate-600">{{ $mitra->nama }}</p>
            </div>
            <a href="{{ route('mitra.index') }}" class="button-ghost">Kembali</a>
        </div>
    </section>

    @if ($errors->any())
        <section class="panel p-6">
            <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                <div class="font-semibold">Ada data yang belum valid.</div>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    <section class="panel p-6">
        <form action="{{ route('mitra.update', $mitra) }}" method="POST" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-2">
            @csrf
            @method('PUT')

            <div class="grid gap-4">
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-slate-700">Nama Mitra</span>
                    <input type="text" name="nama" required class="input" value="{{ old('nama', $mitra->nama) }}">
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-slate-700">Email</span>
                    <input type="email" name="email" class="input" value="{{ old('email', $mitra->email) }}">
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-slate-700">Alamat</span>
                    <textarea name="alamat" rows="4" class="input">{{ old('alamat', $mitra->alamat) }}</textarea>
                </label>
            </div>

            <div class="grid gap-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Nomor Penawaran</span>
                        <input type="text" name="nomor_penawaran" class="input" value="{{ old('nomor_penawaran', $mitra->nomor_penawaran) }}">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Nomor Invoice</span>
                        <input type="text" name="nomor_invoice" class="input" value="{{ old('nomor_invoice', $mitra->nomor_invoice) }}">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Nomor Surat Jalan</span>
                        <input type="text" name="nomor_surat_jalan" class="input" value="{{ old('nomor_surat_jalan', $mitra->nomor_surat_jalan) }}">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Nomor Berita Acara</span>
                        <input type="text" name="nomor_berita_acara" class="input" value="{{ old('nomor_berita_acara', $mitra->nomor_berita_acara) }}">
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Template Penawaran</span>
                        <input type="file" name="template_penawaran" accept=".pdf,.png,.jpg,.jpeg" class="input">
                        <p class="mt-2 text-xs text-slate-500">Saat ini: {{ $mitra->template_penawaran_path ? 'Ada' : '-' }}</p>
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Template Invoice</span>
                        <input type="file" name="template_invoice" accept=".pdf,.png,.jpg,.jpeg" class="input">
                        <p class="mt-2 text-xs text-slate-500">Saat ini: {{ $mitra->template_invoice_path ? 'Ada' : '-' }}</p>
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Template Surat Jalan</span>
                        <input type="file" name="template_surat_jalan" accept=".pdf,.png,.jpg,.jpeg" class="input">
                        <p class="mt-2 text-xs text-slate-500">Saat ini: {{ $mitra->template_surat_jalan_path ? 'Ada' : '-' }}</p>
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Template Berita Acara</span>
                        <input type="file" name="template_berita_acara" accept=".pdf,.png,.jpg,.jpeg" class="input">
                        <p class="mt-2 text-xs text-slate-500">Saat ini: {{ $mitra->template_berita_acara_path ? 'Ada' : '-' }}</p>
                    </label>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700">
                    PDF akan disimpan sebagai template sumber. Agar bisa tampil sebagai background, server perlu dukungan rendering PDF.
                </div>
            </div>

            <div class="lg:col-span-2 flex justify-end gap-3">
                <a href="{{ route('mitra.index') }}" class="button-ghost">Batal</a>
                <button type="submit" class="button-primary">Simpan Perubahan</button>
            </div>
        </form>
    </section>
</div>
@endsection
