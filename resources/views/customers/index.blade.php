@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div>
        <h1 class="text-2xl font-semibold text-gray-800">Customer</h1>
        <p class="text-sm text-gray-500 mt-1">Master data customer.</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Tambah Customer</h2>
        <form action="{{ route('customers.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
            @csrf
            <div>
                <label class="block text-sm mb-1">Nama Customer</label>
                <input type="text" name="nama" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Alamat Customer</label>
                <input type="text" name="alamat" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Nomor Handphone</label>
                <input type="text" name="no_hp" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="block text-sm mb-1">Email Customer</label>
                <input type="email" name="email" required class="w-full border rounded-lg px-3 py-2">
            </div>
            <div>
                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow p-6 overflow-x-auto">
        <h2 class="text-lg font-semibold mb-4">Daftar Customer</h2>
        @if ($customers->isEmpty())
            <p class="text-sm text-gray-500">Belum ada customer.</p>
        @else
            <div class="space-y-3 md:hidden">
                @foreach ($customers as $customer)
                    <div class="border rounded-lg p-4 text-sm space-y-2" data-customer-row>
                        <div class="font-semibold">{{ $customer->nama }}</div>
                        <div>{{ $customer->alamat }}</div>
                        <div>{{ $customer->no_hp }}</div>
                        <div>{{ $customer->email }}</div>
                        <form id="customer-update-mobile-{{ $customer->id }}" action="{{ route('customers.update', $customer) }}" method="POST" class="space-y-2 customer-update-form">
                            @csrf
                            @method('PUT')
                            <input type="text" name="nama" value="{{ $customer->nama }}" data-original="{{ $customer->nama }}" required class="w-full border rounded-lg px-3 py-2 customer-input bg-gray-100 cursor-not-allowed" disabled>
                            <input type="text" name="alamat" value="{{ $customer->alamat }}" data-original="{{ $customer->alamat }}" required class="w-full border rounded-lg px-3 py-2 customer-input bg-gray-100 cursor-not-allowed" disabled>
                            <input type="text" name="no_hp" value="{{ $customer->no_hp }}" data-original="{{ $customer->no_hp }}" required class="w-full border rounded-lg px-3 py-2 customer-input bg-gray-100 cursor-not-allowed" disabled>
                            <input type="email" name="email" value="{{ $customer->email }}" data-original="{{ $customer->email }}" required class="w-full border rounded-lg px-3 py-2 customer-input bg-gray-100 cursor-not-allowed" disabled>
                        </form>
                        <div class="action-buttons justify-start gap-2">
                            <button type="button" title="Edit" class="action-icon action-icon-emerald edit-customer" style="background-color:#059669;color:#fff;">&#9998;</button>
                            <button type="submit" title="Simpan" class="action-icon action-icon-blue save-customer hidden" form="customer-update-mobile-{{ $customer->id }}">&#128190;</button>
                            <button type="button" title="Batal" class="action-icon action-icon-gray cancel-customer hidden">&#10006;</button>
                            <form action="{{ route('customers.destroy', $customer) }}" method="POST" onsubmit="return confirm('Hapus customer ini?')" class="inline-flex">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus" class="action-icon action-icon-red">&#128465;</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <table class="hidden md:table w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="py-3 pr-4">Nama</th>
                        <th class="py-3 pr-4">Alamat</th>
                        <th class="py-3 pr-4">No. HP</th>
                        <th class="py-3 pr-4">Email</th>
                        <th class="py-3 pr-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($customers as $customer)
                        <tr class="border-b" data-customer-row>
                            <td class="py-3 pr-4">
                                <input type="text" name="nama" value="{{ $customer->nama }}" data-original="{{ $customer->nama }}" required class="w-full border rounded-lg px-3 py-2 customer-input bg-gray-100 cursor-not-allowed" disabled form="customer-update-{{ $customer->id }}">
                            </td>
                            <td class="py-3 pr-4">
                                <input type="text" name="alamat" value="{{ $customer->alamat }}" data-original="{{ $customer->alamat }}" required class="w-full border rounded-lg px-3 py-2 customer-input bg-gray-100 cursor-not-allowed" disabled form="customer-update-{{ $customer->id }}">
                            </td>
                            <td class="py-3 pr-4">
                                <input type="text" name="no_hp" value="{{ $customer->no_hp }}" data-original="{{ $customer->no_hp }}" required class="w-full border rounded-lg px-3 py-2 customer-input bg-gray-100 cursor-not-allowed" disabled form="customer-update-{{ $customer->id }}">
                            </td>
                            <td class="py-3 pr-4">
                                <input type="email" name="email" value="{{ $customer->email }}" data-original="{{ $customer->email }}" required class="w-full border rounded-lg px-3 py-2 customer-input bg-gray-100 cursor-not-allowed" disabled form="customer-update-{{ $customer->id }}">
                            </td>
                            <td class="py-3 pr-4">
                                <div class="action-buttons justify-start gap-2">
                                    <button type="button" title="Edit" class="action-icon action-icon-emerald edit-customer" style="background-color:#059669;color:#fff;">&#9998;</button>
                                    <button type="submit" title="Simpan" class="action-icon action-icon-blue save-customer hidden" form="customer-update-{{ $customer->id }}">&#128190;</button>
                                    <button type="button" title="Batal" class="action-icon action-icon-gray cancel-customer hidden">&#10006;</button>
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline-flex" onsubmit="return confirm('Hapus customer ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus" class="action-icon action-icon-red">&#128465;</button>
                                    </form>
                                </div>
                                <form id="customer-update-{{ $customer->id }}" action="{{ route('customers.update', $customer) }}" method="POST" class="hidden">
                                    @csrf
                                    @method('PUT')
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<script>
    document.querySelectorAll('[data-customer-row]').forEach((row) => {
        const editButton = row.querySelector('.edit-customer');
        const saveButton = row.querySelector('.save-customer');
        const cancelButton = row.querySelector('.cancel-customer');
        const inputs = row.querySelectorAll('.customer-input');
        let isEditing = false;

        if (!editButton || !saveButton || !cancelButton || inputs.length === 0) {
            return;
        }

        const hasChanges = () => Array.from(inputs).some((input) => {
            return (input.value ?? '') !== (input.dataset.original ?? '');
        });

        const updateActionState = () => {
            if (!isEditing) {
                saveButton.classList.add('hidden');
                cancelButton.classList.add('hidden');
                return;
            }

            const changed = hasChanges();
            saveButton.classList.toggle('hidden', !changed);
            cancelButton.classList.toggle('hidden', !changed);
        };

        const setEditing = (editing) => {
            isEditing = editing;
            inputs.forEach((input) => {
                input.disabled = !editing;
                input.classList.toggle('bg-gray-100', !isEditing);
                input.classList.toggle('cursor-not-allowed', !isEditing);
            });
            updateActionState();
        };

        editButton.addEventListener('click', () => {
            if (isEditing && !hasChanges()) {
                setEditing(false);
                return;
            }
            setEditing(true);
        });

        cancelButton.addEventListener('click', () => {
            inputs.forEach((input) => {
                input.value = input.dataset.original || '';
            });
            setEditing(false);
        });

        inputs.forEach((input) => {
            input.addEventListener('input', updateActionState);
        });

        updateActionState();
    });
</script>
@endsection
