@extends('layouts.app')

@section('content')
<div>
    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4 sm:mb-6">Buat Surat Penawaran</h1>

    <div class="bg-white rounded-2xl shadow-xl p-4 sm:p-6 lg:p-8">
        @if ($errors->any())
            <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                <ul class="list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('penawaran.store') }}" id="penawaran-form">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium mb-2">Nomor Surat</label>
                    <input type="text" value="{{ $nomorPreview }}" disabled class="w-full bg-gray-100 border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', now()->format('Y-m-d')) }}" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">To</label>
                    <select name="to_company" id="to-company-select" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->nama }}"
                                    data-address="{{ $customer->alamat }}"
                                    data-email="{{ $customer->email }}"
                                    @selected(old('to_company') === $customer->nama)>
                                {{ $customer->nama }}
                            </option>
                        @endforeach
                        @if (old('to_company') && ! $customers->contains('nama', old('to_company')))
                            <option value="{{ old('to_company') }}"
                                    data-address="{{ old('to_address') }}"
                                    data-email="{{ old('customer_email') }}"
                                    selected>
                                {{ old('to_company') }}
                            </option>
                        @endif
                    </select>
                    <p id="customer-email-warning" class="text-xs text-red-600 mt-1 hidden">Email customer belum diisi di master data.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">At</label>
                    <div id="to-address-display" class="w-full border rounded-lg px-4 py-2 bg-gray-50 text-gray-700 min-h-[42px]"></div>
                    <input type="hidden" name="to_address" id="to-address-input" value="{{ old('to_address') }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Status</label>
                    <select name="jenis_kontrak" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="kontrak" @selected(old('jenis_kontrak') === 'kontrak')>Kontrak</option>
                        <option value="satuan" @selected(old('jenis_kontrak', 'satuan') === 'satuan')>Satuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Authorized Signature</label>
                    <select name="signature_role" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Jabatan</option>
                        <option value="Direktur" @selected(old('signature_role') === 'Direktur')>Direktur</option>
                        <option value="Manager" @selected(old('signature_role') === 'Manager')>Manager</option>
                        <option value="Sales" @selected(old('signature_role') === 'Sales')>Sales</option>
                    </select>
                </div>
            </div>

            <div class="mb-8">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-lg font-semibold text-gray-800">Item Penawaran</h2>
                    <button type="button" id="add-item" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">+ Tambah Item</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm" id="items-table">
                        <thead>
                            <tr class="border-b text-left">
                                <th class="py-3 pr-3">Item</th>
                                <th class="py-3 pr-3 w-28">Qty</th>
                                <th class="py-3 pr-3 w-32">Satuan</th>
                                <th class="py-3 pr-3 w-48">Unit Price (Rp)</th>
                                <th class="py-3 pr-3 w-48">Amount (Rp)</th>
                                <th class="py-3 pr-0 w-20">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="item-rows"></tbody>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    @php
                        $defaultKeterangan = "1. Masa berlaku penawaran 7 Hari\n2. Garansi produk selama 1 Tahun\n3. Harga sudah termasuk pajak 11%";
                    @endphp
                    <label class="block text-sm font-medium mb-2">Keterangan</label>
                    <textarea name="keterangan" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">{{ old('keterangan', $defaultKeterangan) }}</textarea>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-2">Pajak (%)</label>
                        <input type="number" min="0" max="100" step="0.01" name="tax_percent" id="tax-percent" value="{{ old('tax_percent', 11) }}" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div class="p-3 bg-gray-50 rounded-lg border">Subtotal</div>
                        <div class="p-3 bg-gray-50 rounded-lg border text-right" id="subtotal-display">Rp 0</div>
                        <div class="p-3 bg-gray-50 rounded-lg border">Pajak</div>
                        <div class="p-3 bg-gray-50 rounded-lg border text-right" id="tax-display">Rp 0</div>
                        <div class="p-3 bg-blue-50 rounded-lg border font-semibold">Total</div>
                        <div class="p-3 bg-blue-50 rounded-lg border text-right font-semibold" id="total-display">Rp 0</div>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3">
                <a href="{{ route('penawaran.index') }}" class="px-6 py-2 bg-gray-200 rounded-lg text-center">Batal</a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Simpan Penawaran</button>
            </div>
        </form>
    </div>
</div>

<script>
    const toCompanySelect = document.getElementById('to-company-select');
    const toAddressDisplay = document.getElementById('to-address-display');
    const toAddressInput = document.getElementById('to-address-input');
    const customerEmailWarning = document.getElementById('customer-email-warning');
    const itemRows = document.getElementById('item-rows');
    const addItemButton = document.getElementById('add-item');
    const taxInput = document.getElementById('tax-percent');
    let rowIndex = 0;

    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value || 0);
    }

    function rowTemplate(index) {
        return `
            <tr class="border-b item-row">
                <td class="py-2 pr-3">
                    <input type="text" name="items[${index}][nama]" required class="w-full border rounded-lg px-3 py-2">
                    <textarea name="items[${index}][rincian]" rows="2"
                              placeholder="Rincian item (opsional)"
                              class="w-full border rounded-lg px-3 py-2 mt-2"></textarea>
                </td>
                <td class="py-2 pr-3">
                    <input type="number" name="items[${index}][qty]" step="0.01" min="0.01" value="1" required class="w-full border rounded-lg px-3 py-2 qty-input">
                </td>
                <td class="py-2 pr-3">
                    <select name="items[${index}][satuan]" class="w-full border rounded-lg px-3 py-2">
                        <option value="month">month</option>
                        <option value="pcs">pcs</option>
                        <option value="item">item</option>
                        <option value="unit">unit</option>
                    </select>
                </td>
                <td class="py-2 pr-3">
                    <input type="number" name="items[${index}][unit_price]" step="0.01" min="0" value="0" required class="w-full border rounded-lg px-3 py-2 price-input">
                </td>
                <td class="py-2 pr-3">
                    <div class="w-full border rounded-lg px-3 py-2 bg-gray-50 text-right amount-display">Rp 0</div>
                </td>
                <td class="py-2 pr-0 text-right">
                    <button type="button" class="text-red-600 hover:text-red-800 remove-item">Hapus</button>
                </td>
            </tr>
        `;
    }

    function recalculate() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach((row) => {
            const qty = parseFloat(row.querySelector('.qty-input').value || 0);
            const price = parseFloat(row.querySelector('.price-input').value || 0);
            const amount = qty * price;
            subtotal += amount;
            row.querySelector('.amount-display').textContent = formatRupiah(amount);
        });

        const taxPercent = parseFloat(taxInput.value || 0);
        const tax = subtotal * (taxPercent / 100);
        const total = subtotal + tax;

        document.getElementById('subtotal-display').textContent = formatRupiah(subtotal);
        document.getElementById('tax-display').textContent = formatRupiah(tax);
        document.getElementById('total-display').textContent = formatRupiah(total);
    }

    function addRow() {
        itemRows.insertAdjacentHTML('beforeend', rowTemplate(rowIndex));
        rowIndex++;
        recalculate();
    }

    function fillCustomerAddress() {
        if (!toCompanySelect) return;
        const selectedOption = toCompanySelect.options[toCompanySelect.selectedIndex];
        const address = selectedOption?.dataset?.address || toAddressInput.value || '';
        const email = selectedOption?.dataset?.email || '';
        toAddressDisplay.textContent = address || '-';
        toAddressInput.value = address;
        const showWarning = toCompanySelect.value && !email;
        customerEmailWarning?.classList.toggle('hidden', !showWarning);
    }

    toCompanySelect?.addEventListener('change', fillCustomerAddress);
    addItemButton.addEventListener('click', addRow);

    itemRows.addEventListener('input', (event) => {
        if (event.target.classList.contains('qty-input') || event.target.classList.contains('price-input')) {
            recalculate();
        }
    });

    itemRows.addEventListener('click', (event) => {
        if (!event.target.classList.contains('remove-item')) {
            return;
        }

        const rows = document.querySelectorAll('.item-row');
        if (rows.length === 1) {
            return;
        }

        event.target.closest('.item-row').remove();
        recalculate();
    });

    taxInput.addEventListener('input', recalculate);

    addRow();
    fillCustomerAddress();
</script>
@endsection
