@extends('layouts.app')

@section('content')
<div>
    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4 sm:mb-6">Ubah Nota Toko</h1>

    <div class="bg-white rounded-xl shadow p-4 sm:p-6">
        <form action="{{ route('nota-toko.update', $notaToko) }}" method="POST" class="space-y-6" id="nota-form">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Nomor Nota</label>
                    <input type="text" value="{{ $notaToko->nomor }}" class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', $notaToko->tanggal) }}" required class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Customer</label>
                    @php
                        $selectedCustomer = old('customer_nama', $notaToko->customer_nama);
                        $selectedAddress = old('alamat', $notaToko->alamat);
                        $selectedEmail = old('customer_email', $notaToko->customer_email);
                        $customerNames = $customers->pluck('nama');
                    @endphp
                    <select id="customer-select" name="customer_nama" required class="w-full border rounded-lg px-4 py-2">
                        <option value="">Pilih customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->nama }}"
                                    data-address="{{ $customer->alamat }}"
                                    data-email="{{ $customer->email }}"
                                    @selected($selectedCustomer === $customer->nama)>
                                {{ $customer->nama }}
                            </option>
                        @endforeach
                        @if ($selectedCustomer && ! $customerNames->contains($selectedCustomer))
                            <option value="{{ $selectedCustomer }}"
                                    data-address="{{ $selectedAddress }}"
                                    data-email="{{ $selectedEmail }}"
                                    selected>
                                {{ $selectedCustomer }}
                            </option>
                        @endif
                    </select>
                    <p id="customer-email-warning" class="text-xs text-red-600 mt-1 hidden">Email customer belum diisi di master data.</p>
                </div>
                <div>
                    <label class="block text-sm mb-1">Alamat</label>
                    <div id="customer-address-display" class="w-full border rounded-lg px-4 py-2 bg-gray-50 text-gray-700 min-h-[42px]"></div>
                    <input type="hidden" id="customer-alamat" name="alamat" value="{{ $selectedAddress }}">
                    <input type="hidden" id="customer-email" name="customer_email" value="{{ $selectedEmail }}">
                </div>
            </div>

            <div>
                <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Item Nota</h2>
                        <p class="text-sm text-gray-500">Tampilan dibuat kartu agar tetap nyaman di mobile.</p>
                    </div>
                    <button type="button" id="add-item" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-white shadow-sm transition hover:bg-blue-700">+ Tambah Item</button>
                </div>
                <div id="item-rows" class="space-y-4"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="3" class="w-full border rounded-lg px-4 py-2">{{ old('keterangan', $notaToko->keterangan) }}</textarea>
                </div>
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 text-sm space-y-2 self-end">
                    <div class="flex items-center justify-between gap-4"><span class="text-gray-600">Subtotal</span><span id="subtotal-display" class="font-medium text-gray-900">Rp 0,00</span></div>
                    <div class="flex items-center justify-between gap-4 border-t border-gray-200 pt-2 font-semibold"><span class="text-gray-700">Total</span><span id="total-display" class="text-gray-900">Rp 0,00</span></div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('nota-toko.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@php
$oldItems = old('items');
$initialItems = is_array($oldItems)
    ? $oldItems
    : $notaToko->items->map(fn ($item) => [
        'nama' => $item->nama,
        'qty' => $item->qty,
        'satuan' => $item->satuan,
        'unit_price' => $item->unit_price,
    ])->values()->all();
@endphp

<script>
(() => {
    const customerSelect = document.getElementById('customer-select');
    const customerAddressDisplay = document.getElementById('customer-address-display');
    const customerEmailWarning = document.getElementById('customer-email-warning');
    const customerAlamatInput = document.getElementById('customer-alamat');
    const customerEmailInput = document.getElementById('customer-email');

    const itemRows = document.getElementById('item-rows');
    const addItemButton = document.getElementById('add-item');
    const subtotalDisplay = document.getElementById('subtotal-display');
    const totalDisplay = document.getElementById('total-display');
    const initialItems = @json($initialItems);
    let rowIndex = 0;

    const formatRupiah = (number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number || 0);
    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const rowTemplate = (index, item = {}) => {
        const nama = escapeHtml(item.nama ?? '');
        const qty = item.qty ?? 1;
        const satuan = item.satuan ?? 'pcs';
        const unitPrice = item.unit_price ?? 0;

        return `
        <div class="item-row rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="mb-4 flex items-start justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold text-slate-800">Detail Item</p>
                    <p class="text-xs text-slate-500">Isi nama, qty, satuan, dan harga satuannya.</p>
                </div>
                <button type="button" class="remove-item inline-flex items-center rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50">
                    Hapus
                </button>
            </div>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12">
                <label class="block xl:col-span-4">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Item</span>
                    <input type="text" name="items[${index}][nama]" value="${nama}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="block sm:col-span-1 xl:col-span-2">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Qty</span>
                    <input type="number" name="items[${index}][qty]" step="0.01" min="0.01" value="${qty}" required class="qty-input w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                </label>
                <label class="block sm:col-span-1 xl:col-span-2">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Satuan</span>
                    <select name="items[${index}][satuan]" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                        <option value="month" ${satuan === 'month' ? 'selected' : ''}>month</option>
                        <option value="pcs" ${satuan === 'pcs' ? 'selected' : ''}>pcs</option>
                        <option value="item" ${satuan === 'item' ? 'selected' : ''}>item</option>
                        <option value="unit" ${satuan === 'unit' ? 'selected' : ''}>unit</option>
                    </select>
                </label>
                <label class="block xl:col-span-2">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Unit Price</span>
                    <input type="number" name="items[${index}][unit_price]" step="0.01" min="0" value="${unitPrice}" required class="price-input w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                </label>
                <div class="block xl:col-span-2">
                    <span class="mb-1 block text-sm font-medium text-slate-700">Amount</span>
                    <div class="amount-display flex min-h-[42px] items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-800">Rp 0,00</div>
                </div>
            </div>
        </div>`;
    };

    function recalc() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach((row) => {
            const qty = parseFloat(row.querySelector('.qty-input').value || 0);
            const price = parseFloat(row.querySelector('.price-input').value || 0);
            const amount = qty * price;
            subtotal += amount;
            row.querySelector('.amount-display').textContent = formatRupiah(amount);
        });

        subtotalDisplay.textContent = formatRupiah(subtotal);
        totalDisplay.textContent = formatRupiah(subtotal);
    }

    function addRow(item = {}) {
        itemRows.insertAdjacentHTML('beforeend', rowTemplate(rowIndex, item));
        rowIndex += 1;
        recalc();
    }

    function fillCustomerData() {
        if (!customerSelect) return;
        const option = customerSelect.options[customerSelect.selectedIndex];
        const address = option?.dataset?.address || customerAlamatInput.value || '';
        const email = option?.dataset?.email || customerEmailInput.value || '';
        customerAlamatInput.value = address;
        customerEmailInput.value = email;
        customerAddressDisplay.textContent = address || '-';
        const showWarning = customerSelect.value && !email;
        customerEmailWarning?.classList.toggle('hidden', !showWarning);
    }

    customerSelect?.addEventListener('change', fillCustomerData);
    addItemButton.addEventListener('click', () => addRow());
    itemRows.addEventListener('input', recalc);
    itemRows.addEventListener('click', (event) => {
        if (!event.target.classList.contains('remove-item')) return;
        if (document.querySelectorAll('.item-row').length <= 1) return;
        event.target.closest('.item-row')?.remove();
        recalc();
    });
    if (initialItems.length) {
        initialItems.forEach((item) => addRow(item));
    } else {
        addRow();
    }

    fillCustomerData();
})();
</script>
@endsection
