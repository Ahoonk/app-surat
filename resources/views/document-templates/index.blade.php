@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Konfigurasi</p>
                <h1 class="section-title mt-2">Template Dokumen</h1>
                <p class="mt-3 text-sm text-slate-600">Pilih view Blade default per jenis dokumen. Resolver tetap fallback ke template lama bila belum ada override.</p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Total Template</div>
                <div class="mt-1 text-lg font-semibold text-slate-900">{{ $templates->count() }}</div>
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
        <h2 class="text-lg font-semibold text-slate-900">Tambah Template</h2>
        <p class="mt-1 text-sm text-slate-500">`file_path` diisi nama view Blade, misalnya `invoice.pdf` atau `surat-jalan.pdf`.</p>

        <form action="{{ route('document-templates.store') }}" method="POST" class="mt-6 grid gap-4 xl:grid-cols-2">
            @csrf
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Jenis Dokumen</span>
                <select name="document_type" required class="input">
                    @foreach ($documentTypes as $key => $label)
                        <option value="{{ $key }}" @selected(old('document_type') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Nama Template</span>
                <input type="text" name="name" value="{{ old('name') }}" required class="input">
            </label>

            <label class="block xl:col-span-2">
                <span class="mb-2 block text-sm font-medium text-slate-700">View Blade / Path</span>
                <input list="document-template-views" type="text" name="file_path" value="{{ old('file_path') }}" required class="input" placeholder="invoice.pdf">
                <datalist id="document-template-views">
                    @foreach ($availableViews as $view)
                        <option value="{{ $view }}"></option>
                    @endforeach
                </datalist>
            </label>

            <label class="inline-flex items-center gap-3 xl:col-span-2">
                <input type="checkbox" name="is_default" value="1" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" @checked(old('is_default'))>
                <span class="text-sm text-slate-700">Jadikan default untuk jenis dokumen ini</span>
            </label>

            <div class="xl:col-span-2 flex justify-end">
                <button type="submit" class="button-primary">Simpan Template</button>
            </div>
        </form>
    </section>

    <section class="panel p-6">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Daftar Template</h2>
                <p class="mt-1 text-sm text-slate-500">Setiap kartu bisa diedit langsung tanpa pindah halaman.</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 xl:grid-cols-2">
            @forelse ($templates as $template)
                <article class="rounded-3xl border border-slate-200 bg-slate-50/70 p-5 shadow-sm">
                    <form action="{{ route('document-templates.update', $template) }}" method="POST" class="space-y-4">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="document_type" value="{{ $template->document_type }}">

                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Jenis</p>
                                <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ $documentTypes[$template->document_type] ?? $template->document_type }}</h3>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $template->is_default ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-200 text-slate-600' }}">
                                {{ $template->is_default ? 'Default' : 'Cadangan' }}
                            </span>
                        </div>

                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Nama Template</span>
                            <input type="text" name="name" value="{{ $template->name }}" class="input">
                        </label>

                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">View Blade / Path</span>
                            <input list="document-template-views" type="text" name="file_path" value="{{ $template->file_path }}" class="input">
                        </label>

                        <label class="inline-flex items-center gap-3">
                            <input type="checkbox" name="is_default" value="1" class="h-4 w-4 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" @checked($template->is_default)>
                            <span class="text-sm text-slate-700">Jadikan default untuk jenis dokumen ini</span>
                        </label>

                        <div class="flex flex-wrap gap-2">
                            <button type="submit" class="button-primary">Simpan</button>
                        </div>
                    </form>

                    <form action="{{ route('document-templates.destroy', $template) }}" method="POST" class="mt-3" onsubmit="return confirm('Hapus template ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="button-danger">Hapus</button>
                    </form>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-10 text-center text-slate-500 xl:col-span-2">
                    Belum ada template dokumen.
                </div>
            @endforelse
        </div>
    </section>

    <section class="panel p-6">
        <h2 class="text-lg font-semibold text-slate-900">Referensi View Default</h2>
        <div class="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            @foreach ($defaultViews as $type => $view)
                <div class="rounded-2xl bg-slate-50 p-4 text-sm">
                    <div class="text-xs uppercase tracking-[0.25em] text-slate-400">{{ $documentTypes[$type] ?? $type }}</div>
                    <div class="mt-2 font-medium text-slate-900">{{ $view }}</div>
                </div>
            @endforeach
        </div>
    </section>
</div>
@endsection
