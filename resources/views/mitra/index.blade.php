@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Master Data</p>
                <h1 class="section-title mt-2">Mitra</h1>
                <p class="mt-3 text-sm text-slate-600">Kelola partner, nomor dokumen default, dan template surat per mitra.</p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Total Mitra</div>
                <div class="mt-1 text-lg font-semibold text-slate-900">{{ $mitras->count() }}</div>
            </div>
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
        <h2 class="text-lg font-semibold text-slate-900">Tambah Mitra</h2>
        <p class="mt-1 text-sm text-slate-500">Template PDF atau gambar tetap bisa diupload. Jika PDF belum dirender di server, file tetap disimpan sebagai sumber template.</p>

        <form action="{{ route('mitra.store') }}" method="POST" enctype="multipart/form-data" class="mt-6 grid gap-4 lg:grid-cols-2">
            @csrf

            <div class="grid gap-4">
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-slate-700">Nama Mitra</span>
                    <input type="text" name="nama" value="{{ old('nama') }}" required class="input">
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-slate-700">Email</span>
                    <input type="email" name="email" value="{{ old('email') }}" class="input">
                </label>
                <label class="block">
                    <span class="mb-2 block text-sm font-medium text-slate-700">Alamat</span>
                    <textarea name="alamat" rows="4" class="input">{{ old('alamat') }}</textarea>
                </label>
            </div>

            <div class="grid gap-4">
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Nomor Penawaran</span>
                        <input type="text" name="nomor_penawaran" value="{{ old('nomor_penawaran') }}" class="input">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Nomor Invoice</span>
                        <input type="text" name="nomor_invoice" value="{{ old('nomor_invoice') }}" class="input">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Nomor Surat Jalan</span>
                        <input type="text" name="nomor_surat_jalan" value="{{ old('nomor_surat_jalan') }}" class="input">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Nomor Berita Acara</span>
                        <input type="text" name="nomor_berita_acara" value="{{ old('nomor_berita_acara') }}" class="input">
                    </label>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Template Penawaran</span>
                        <input type="file" name="template_penawaran" accept=".pdf,.png,.jpg,.jpeg" class="input">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Template Invoice</span>
                        <input type="file" name="template_invoice" accept=".pdf,.png,.jpg,.jpeg" class="input">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Template Surat Jalan</span>
                        <input type="file" name="template_surat_jalan" accept=".pdf,.png,.jpg,.jpeg" class="input">
                    </label>
                    <label class="block">
                        <span class="mb-2 block text-sm font-medium text-slate-700">Template Berita Acara</span>
                        <input type="file" name="template_berita_acara" accept=".pdf,.png,.jpg,.jpeg" class="input">
                    </label>
                </div>

                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-xs text-amber-700">
                    PDF akan disimpan sebagai template sumber. Agar bisa tampil sebagai background, server perlu dukungan rendering PDF.
                </div>
            </div>

            <div class="lg:col-span-2 flex justify-end">
                <button type="submit" class="button-primary">Simpan Mitra</button>
            </div>
        </form>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        @forelse ($mitras as $mitra)
            <article class="panel p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Mitra</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ $mitra->nama }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $mitra->email ?: '-' }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Alamat</div>
                        <div class="mt-1 max-w-xs font-semibold text-slate-900">{{ $mitra->alamat ?: '-' }}</div>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Nomor Dokumen</div>
                        <div class="mt-3 space-y-1">
                            <div>Penawaran: <span class="font-medium text-slate-900">{{ $mitra->nomor_penawaran ?: '-' }}</span></div>
                            <div>Invoice: <span class="font-medium text-slate-900">{{ $mitra->nomor_invoice ?: '-' }}</span></div>
                            <div>Surat Jalan: <span class="font-medium text-slate-900">{{ $mitra->nomor_surat_jalan ?: '-' }}</span></div>
                            <div>Berita Acara: <span class="font-medium text-slate-900">{{ $mitra->nomor_berita_acara ?: '-' }}</span></div>
                        </div>
                    </div>
                    <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Template</div>
                        <div class="mt-3 space-y-1">
                            <div>Penawaran: <span class="font-medium text-slate-900">{{ $mitra->template_penawaran_path ? 'Ada' : '-' }}</span></div>
                            <div>Invoice: <span class="font-medium text-slate-900">{{ $mitra->template_invoice_path ? 'Ada' : '-' }}</span></div>
                            <div>Surat Jalan: <span class="font-medium text-slate-900">{{ $mitra->template_surat_jalan_path ? 'Ada' : '-' }}</span></div>
                            <div>Berita Acara: <span class="font-medium text-slate-900">{{ $mitra->template_berita_acara_path ? 'Ada' : '-' }}</span></div>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('mitra.edit', $mitra) }}" class="button-ghost">Ubah</a>
                    <form action="{{ route('mitra.destroy', $mitra) }}" method="POST" onsubmit="return confirm('Hapus mitra ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="button-danger">Hapus Mitra</button>
                    </form>
                </div>
            </article>
        @empty
            <section class="panel p-6">
                <p class="text-sm text-slate-500">Belum ada mitra.</p>
            </section>
        @endforelse
    </section>
</div>
@endsection
