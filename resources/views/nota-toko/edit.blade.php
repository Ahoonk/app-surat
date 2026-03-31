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

            <div class="overflow-x-auto">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold text-gray-800">Item Nota</h2>
                    <button type="button" id="add-item" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Tambah Item</button>
                </div>
                <table class="w-full text-sm" id="items-table">
                    <thead>
                        <tr>
                            <th class="py-3 pr-3">Item</th>
                            <th class="py-3 pr-3 w-28">Qty</th>
                            <th class="py-3 pr-3 w-32">Satuan</th>
                            <th class="py-3 pr-3 w-40">Unit Price</th>
                            <th class="py-3 pr-3 w-40">Amount</th>
                            <th class="py-3 pr-3 w-20">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="item-rows"></tbody>
                </table>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Keterangan</label>
                    <textarea name="keterangan" rows="3" class="w-full border rounded-lg px-4 py-2">{{ old('keterangan', $notaToko->keterangan) }}</textarea>
                </div>
                <div class="text-sm space-y-1 self-end">
                    <div class="flex justify-between"><span>Subtotal</span><span id="subtotal-display">Rp 0,00</span></div>
                    <div class="flex justify-between font-semibold"><span>Total</span><span id="total-display">Rp 0,00</span></div>
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

    const rowTemplate = (index, item = {}) => {
        const nama = item.nama ?? '';
        const qty = item.qty ?? 1;
        const satuan = item.satuan ?? 'pcs';
        const unitPrice = item.unit_price ?? 0;

        return `
        <tr class="border-b item-row">
            <td class="py-2 pr-3"><input type="text" name="items[${index}][nama]" value="${String(nama).replace(/"/g, '&quot;')}" required class="w-full border rounded-lg px-3 py-2"></td>
            <td class="py-2 pr-3"><input type="number" name="items[${index}][qty]" step="0.01" min="0.01" value="${qty}" required class="w-full border rounded-lg px-3 py-2 qty-input"></td>
            <td class="py-2 pr-3">
                <select name="items[${index}][satuan]" class="w-full border rounded-lg px-3 py-2">
                    <option value="month" ${satuan === 'month' ? 'selected' : ''}>month</option>
                    <option value="pcs" ${satuan === 'pcs' ? 'selected' : ''}>pcs</option>
                    <option value="item" ${satuan === 'item' ? 'selected' : ''}>item</option>
                    <option value="unit" ${satuan === 'unit' ? 'selected' : ''}>unit</option>
                </select>
            </td>
            <td class="py-2 pr-3"><input type="number" name="items[${index}][unit_price]" step="0.01" min="0" value="${unitPrice}" required class="w-full border rounded-lg px-3 py-2 price-input"></td>
            <td class="py-2 pr-3 amount-display">Rp 0,00</td>
            <td class="py-2 pr-3"><button type="button" class="text-red-600 remove-item">Hapus</button></td>
        </tr>`;
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
        event.target.closest('.item-row').remove();
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
