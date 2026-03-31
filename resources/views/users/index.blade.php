@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if (session('status'))
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 text-sm text-yellow-700">
            {{ session('status') }}
        </div>
    @endif

    <div>
        <h1 class="text-2xl font-semibold text-gray-800">Manajemen User</h1>
        <p class="text-sm text-gray-500 mt-1">Khusus superadmin.</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Tambah User</h2>
        <form action="{{ route('users.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-sm mb-1">Nama</label>
                <input type="text" name="name" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Email</label>
                <input type="email" name="email" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Password</label>
                <input type="password" name="password" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Role</label>
                <select name="role" class="w-full border rounded-lg px-3 py-2">
                    <option value="admin">Admin</option>
                    <option value="superadmin">Superadmin</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow p-6 overflow-x-auto">
        <h2 class="text-lg font-semibold mb-4">Daftar User</h2>
        <div class="space-y-3 md:hidden">
            @foreach ($users as $user)
                <div class="border rounded-lg p-4 text-sm">
                    <div class="font-semibold">{{ $user->name }}</div>
                    <div class="text-gray-600">{{ $user->email }}</div>
                    <div class="capitalize mt-1">Role: {{ $user->role }}</div>
                    <form action="{{ route('users.update', $user) }}" method="POST" class="mt-3 space-y-2">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" value="{{ $user->name }}">
                        <input type="hidden" name="email" value="{{ $user->email }}">
                        <select name="role" class="w-full border rounded-lg px-3 py-2">
                            <option value="admin" @selected($user->role === 'admin')>Admin</option>
                            <option value="superadmin" @selected($user->role === 'superadmin')>Superadmin</option>
                        </select>
                        <input type="password" name="password" placeholder="Password baru (opsional)" class="w-full border rounded-lg px-3 py-2">
                        <button type="submit" title="Ubah" class="action-icon action-icon-emerald text-white" style="background-color:#059669;color:#fff;">✏️</button>
                    </form>
                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="mt-2" onsubmit="return confirm('Hapus user ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" title="Hapus" class="action-icon action-icon-red">🗑️</button>
                    </form>
                </div>
            @endforeach
        </div>

        <table class="hidden md:table w-full text-sm">
            <thead>
                <tr class="border-b">
                    <th class="py-3 pr-4 text-left">Nama</th>
                    <th class="py-3 pr-4 text-left">Email</th>
                    <th class="py-3 pr-4 text-left">Role</th>
                    <th class="py-3 pr-4 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr class="border-b">
                        <form action="{{ route('users.update', $user) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <td class="py-3 pr-4">
                                <input type="text" name="name" value="{{ $user->name }}" required class="w-full border rounded-lg px-3 py-2">
                            </td>
                            <td class="py-3 pr-4">
                                <input type="email" name="email" value="{{ $user->email }}" required class="w-full border rounded-lg px-3 py-2">
                            </td>
                            <td class="py-3 pr-4">
                                <select name="role" class="w-full border rounded-lg px-3 py-2">
                                    <option value="admin" @selected($user->role === 'admin')>Admin</option>
                                    <option value="superadmin" @selected($user->role === 'superadmin')>Superadmin</option>
                                </select>
                            </td>
                            <td class="py-3 pr-4">
                                <div class="flex gap-2 items-center">
                                    <input type="password" name="password" placeholder="Password baru (opsional)" class="w-44 border rounded-lg px-3 py-2">
                                    <button type="submit" title="Ubah" class="action-icon action-icon-emerald text-white" style="background-color:#059669;color:#fff;">✏️</button>
                                </div>
                        </form>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="mt-2" onsubmit="return confirm('Hapus user ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" title="Hapus" class="action-icon action-icon-red">🗑️</button>
                                </form>
                            </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
