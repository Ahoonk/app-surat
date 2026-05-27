@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Admin</p>
                <h1 class="section-title mt-2">Manajemen User</h1>
                <p class="mt-3 text-sm text-slate-600">Khusus superadmin untuk membuat, mengubah role, dan menghapus akun.</p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Total User</div>
                <div class="mt-1 text-lg font-semibold text-slate-900">{{ $users->count() }}</div>
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
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-slate-900">Tambah User</h2>
                <p class="mt-1 text-sm text-slate-500">Form ini langsung tersambung ke controller lama, hanya tampilannya yang baru.</p>
            </div>
        </div>

        <form action="{{ route('users.store') }}" method="POST" class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            @csrf
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Nama</span>
                <input type="text" name="name" value="{{ old('name') }}" required class="input">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Email</span>
                <input type="email" name="email" value="{{ old('email') }}" required class="input">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Password</span>
                <input type="password" name="password" required class="input">
            </label>
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Role</span>
                <select name="role" class="input">
                    <option value="admin" @selected(old('role', 'admin') === 'admin')>Admin</option>
                    <option value="superadmin" @selected(old('role') === 'superadmin')>Superadmin</option>
                </select>
            </label>
            <div class="flex items-end">
                <button type="submit" class="button-primary w-full">Simpan User</button>
            </div>
        </form>
    </section>

    <section class="grid gap-4 xl:grid-cols-2">
        @forelse ($users as $user)
            <article class="panel p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Akun</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ $user->name }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ $user->email }}</p>
                    </div>
                    <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Role</div>
                        <div class="mt-1 font-semibold capitalize text-slate-900">{{ $user->role }}</div>
                    </div>
                </div>

                <form action="{{ route('users.update', $user) }}" method="POST" class="mt-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid gap-4 md:grid-cols-2">
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Nama</span>
                            <input type="text" name="name" value="{{ $user->name }}" required class="input">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Email</span>
                            <input type="email" name="email" value="{{ $user->email }}" required class="input">
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Role</span>
                            <select name="role" class="input">
                                <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                <option value="superadmin" @selected($user->role === 'superadmin')>Superadmin</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="mb-2 block text-sm font-medium text-slate-700">Password Baru</span>
                            <input type="password" name="password" placeholder="Opsional" class="input">
                        </label>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit" class="button-primary">Simpan Perubahan</button>
                    </div>
                </form>

                <form action="{{ route('users.destroy', $user) }}" method="POST" class="mt-3" onsubmit="return confirm('Hapus user ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button-danger">Hapus User</button>
                </form>
            </article>
        @empty
            <section class="panel p-6">
                <p class="text-sm text-slate-500">Belum ada user yang terdaftar.</p>
            </section>
        @endforelse
    </section>
</div>
@endsection
