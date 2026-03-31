@extends('layouts.app')

@section('content')
<div>
    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4 sm:mb-6">Buat Nota Toko</h1>

    <div class="bg-white rounded-xl shadow p-4 sm:p-6">
        <form action="{{ route('nota-toko.store') }}" method="POST" class="space-y-6" id="nota-form">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm mb-1">Nomor Nota</label>
                    <input type="text" value="{{ $nomorPreview }}" class="w-full border rounded-lg px-4 py-2 bg-gray-100" readonly>
                </div>
                <div>
                    <label class="block text-sm mb-1">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', now()->toDateString()) }}" required class="w-full border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm mb-1">Customer</label>
                    <select id="customer-select" name="customer_nama" required class="w-full border rounded-lg px-4 py-2">
                        <option value="">Pilih customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->nama }}"
                                    data-address="{{ $customer->alamat }}"
                                    data-email="{{ $customer->email }}"
                                    @selected(old('customer_nama') === $customer->nama)>
                                {{ $customer->nama }}
                            </option>
                        @endforeach
                        @if (old('customer_nama') && ! $customers->contains('nama', old('customer_nama')))
                            <option value="{{ old('customer_nama') }}"
                                    data-address="{{ old('alamat') }}"
                                    data-email="{{ old('customer_email') }}"
                                    selected>
                                {{ old('customer_nama') }}
                            </option>
                        @endif
                    </select>
                    <p id="customer-email-warning" class="text-xs text-red-600 mt-1 hidden">Email customer belum diisi di master data.</p>
                </div>
                <div>
                    <label class="block text-sm mb-1">Alamat</label>
                    <div id="customer-address-display" class="w-full border rounded-lg px-4 py-2 bg-gray-50 text-gray-700 min-h-[42px]"></div>
                    <input type="hidden" id="customer-alamat" name="alamat" value="{{ old('alamat') }}">
                    <input type="hidden" id="customer-email" name="customer_email" value="{{ old('customer_email') }}">
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
                    <textarea name="keterangan" rows="3" class="w-full border rounded-lg px-4 py-2">{{ old('keterangan') }}</textarea>
                </div>
                <div class="text-sm space-y-1 self-end">
                    <div class="flex justify-between"><span>Subtotal</span><span id="subtotal-display">Rp 0,00</span></div>
                    <div class="flex justify-between font-semibold"><span>Total</span><span id="total-display">Rp 0,00</span></div>
                </div>
            </div>

            <div class="flex justify-end gap-2">
                <a href="{{ route('nota-toko.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</a>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

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
    let rowIndex = 0;

    const formatRupiah = (number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(number || 0);

    const rowTemplate = (index) => `
        <tr class="border-b item-row">
            <td class="py-2 pr-3"><input type="text" name="items[${index}][nama]" required class="w-full border rounded-lg px-3 py-2"></td>
            <td class="py-2 pr-3"><input type="number" name="items[${index}][qty]" step="0.01" min="0.01" value="1" required class="w-full border rounded-lg px-3 py-2 qty-input"></td>
            <td class="py-2 pr-3">
                <select name="items[${index}][satuan]" class="w-full border rounded-lg px-3 py-2">
                    <option value="month">month</option>
                    <option value="pcs">pcs</option>
                    <option value="item">item</option>
                    <option value="unit">unit</option>
                </select>
            </td>
            <td class="py-2 pr-3"><input type="number" name="items[${index}][unit_price]" step="0.01" min="0" value="0" required class="w-full border rounded-lg px-3 py-2 price-input"></td>
            <td class="py-2 pr-3 amount-display">Rp 0,00</td>
            <td class="py-2 pr-3"><button type="button" class="text-red-600 remove-item">Hapus</button></td>
        </tr>`;

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

    function addRow() {
        itemRows.insertAdjacentHTML('beforeend', rowTemplate(rowIndex));
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
    addItemButton.addEventListener('click', addRow);
    itemRows.addEventListener('input', recalc);
    itemRows.addEventListener('click', (event) => {
        if (!event.target.classList.contains('remove-item')) return;
        if (document.querySelectorAll('.item-row').length <= 1) return;
        event.target.closest('.item-row').remove();
        recalc();
    });
    addRow();
    fillCustomerData();
})();
</script>
@endsection
