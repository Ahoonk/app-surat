@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Konfigurasi</p>
                <h1 class="section-title mt-2">Template Dokumen</h1>
                <p class="mt-3 text-sm text-slate-600">
                    Unggah template PDF/PNG/JPG untuk perusahaan Aldera. Menu ini tidak memengaruhi sistem penomoran yang sudah berjalan,
                    dan Nota Toko tetap memakai template lama.
                </p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Total Template Aktif</div>
                <div class="mt-1 text-lg font-semibold text-slate-900">{{ $templatePaths->filter()->count() }}</div>
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
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Upload Template</h2>
                <p class="mt-1 text-sm text-slate-500">Satu file untuk satu jenis dokumen. File baru akan menggantikan template aktif.</p>
            </div>
        </div>

        <form action="{{ route('document-templates.store') }}" method="POST" enctype="multipart/form-data" class="mt-6">
            @csrf

            <div class="grid gap-4 xl:grid-cols-2">
                @foreach ($documentTypes as $documentType => $meta)
                    @php $currentPath = $templatePaths[$documentType] ?? null; @endphp

                    <label class="block rounded-3xl border border-slate-200 bg-slate-50/80 p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Jenis</p>
                                <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ $meta['label'] }}</h3>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $currentPath ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                {{ $currentPath ? 'Aktif' : 'Belum Ada' }}
                            </span>
                        </div>

                        <div class="mt-4">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Upload Template</span>
                            <input type="file" name="{{ $meta['field'] }}" accept=".pdf,.png,.jpg,.jpeg" class="input">
                            <p class="mt-2 text-xs text-slate-500">
                                Format PDF, PNG, atau JPG. Template perusahaan akan dipakai di preview dan PDF cetak.
                            </p>
                        </div>

                        <div class="mt-4 rounded-2xl bg-white px-4 py-3 text-xs text-slate-600">
                            <div class="uppercase tracking-[0.25em] text-slate-400">Saat ini</div>
                            <div class="mt-1 font-medium text-slate-900">{{ $currentPath ? basename($currentPath) : '-' }}</div>
                        </div>
                    </label>
                @endforeach
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="button-primary">Simpan Template</button>
            </div>
        </form>
    </section>

    <section class="panel p-6">
        <h2 class="text-lg font-semibold text-slate-900">Catatan</h2>
        <div class="mt-4 grid gap-3 md:grid-cols-2">
            <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                Template perusahaan menjadi fallback default untuk dokumen penawaran, invoice, surat jalan, dan berita acara.
            </div>
            <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                Template Mitra tetap lebih prioritas jika ada template khusus pada partner tertentu.
            </div>
        </div>
    </section>
</div>
@endsection
