@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Master Data</p>
                <h1 class="section-title mt-2">Customer</h1>
                <p class="mt-3 text-sm text-slate-600">Master data customer yang dipakai penawaran dan nota toko.</p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Total Customer</div>
                <div class="mt-1 text-lg font-semibold text-slate-900">{{ $customers->count() }}</div>
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
        <h2 class="text-lg font-semibold text-slate-900">Tambah Customer</h2>
        <p class="mt-1 text-sm text-slate-500">Data baru langsung dipakai oleh alur transaksi yang sudah ada.</p>

        <form action="{{ route('customers.store') }}" method="POST" class="mt-6 grid gap-4 xl:grid-cols-5">
            @csrf
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Nama Customer</span>
                <input type="text" name="nama" value="{{ old('nama') }}" required class="input">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Alamat Customer</span>
                <input type="text" name="alamat" value="{{ old('alamat') }}" required class="input">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Nomor Handphone</span>
                <input type="text" name="no_hp" value="{{ old('no_hp') }}" required class="input">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Email Customer</span>
                <input type="email" name="email" value="{{ old('email') }}" required class="input">
            </label>
            <div class="flex items-end">
                <button type="submit" class="button-primary w-full">Simpan Customer</button>
            </div>
        </form>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        @forelse ($customers as $customer)
            <article class="panel p-6">
                <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Customer</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ $customer->nama }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $customer->email }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-400">HP</div>
                        <div class="mt-1 font-semibold text-slate-900">{{ $customer->no_hp }}</div>
                    </div>
                </div>

                <form action="{{ route('customers.update', $customer) }}" method="POST" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block md:col-span-2">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Nama</span>
                            <input type="text" name="nama" value="{{ $customer->nama }}" required class="input">
                        </label>
                        <label class="block md:col-span-2">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Alamat</span>
                            <input type="text" name="alamat" value="{{ $customer->alamat }}" required class="input">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Nomor Handphone</span>
                            <input type="text" name="no_hp" value="{{ $customer->no_hp }}" required class="input">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Email</span>
                            <input type="email" name="email" value="{{ $customer->email }}" required class="input">
                        </label>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="button-primary">Simpan Perubahan</button>
                    </div>
                </form>

                <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="mt-3" onsubmit="return confirm('Hapus customer ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button-danger">Hapus Customer</button>
                </form>
            </article>
        @empty
            <section class="panel p-6">
                <p class="text-sm text-slate-500">Belum ada customer.</p>
            </section>
        @endforelse
    </section>
</div>
@endsection
