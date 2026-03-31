@extends('layouts.app')

@section('content')
<div>
    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4 sm:mb-6">Edit Surat Penawaran</h1>

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

        <form method="POST" action="{{ route('penawaran.update', $penawaran) }}" id="penawaran-form">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div>
                    <label class="block text-sm font-medium mb-2">Nomor Surat</label>
                    <input type="text" value="{{ $penawaran->nomor }}" disabled class="w-full bg-gray-100 border rounded-lg px-4 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Tanggal</label>
                    <input type="date" name="tanggal" value="{{ old('tanggal', \Illuminate\Support\Carbon::parse($penawaran->tanggal)->format('Y-m-d')) }}" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">To</label>
                    @php
                        $selectedCompany = old('to_company', $penawaran->to_company ?? $penawaran->customer_nama);
                        $selectedAddress = old('to_address', $penawaran->to_address);
                        $customerNames = $customers->pluck('nama');
                    @endphp
                    <select name="to_company" id="to-company-select" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih customer</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->nama }}"
                                    data-address="{{ $customer->alamat }}"
                                    data-email="{{ $customer->email }}"
                                    @selected($selectedCompany === $customer->nama)>
                                {{ $customer->nama }}
                            </option>
                        @endforeach
                        @if ($selectedCompany && ! $customerNames->contains($selectedCompany))
                            <option value="{{ $selectedCompany }}"
                                    data-address="{{ $selectedAddress }}"
                                    data-email="{{ old('customer_email') }}"
                                    selected>
                                {{ $selectedCompany }}
                            </option>
                        @endif
                    </select>
                    <p id="customer-email-warning" class="text-xs text-red-600 mt-1 hidden">Email customer belum diisi di master data.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">At</label>
                    <div id="to-address-display" class="w-full border rounded-lg px-4 py-2 bg-gray-50 text-gray-700 min-h-[42px]"></div>
                    <input type="hidden" name="to_address" id="to-address-input" value="{{ $selectedAddress }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Status</label>
                    <select name="jenis_kontrak" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="kontrak" @selected(old('jenis_kontrak', $penawaran->jenis_kontrak) === 'kontrak')>Kontrak</option>
                        <option value="satuan" @selected(old('jenis_kontrak', $penawaran->jenis_kontrak) === 'satuan')>Satuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Authorized Signature</label>
                    <select name="signature_role" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Jabatan</option>
                        <option value="Direktur" @selected(old('signature_role', $penawaran->signature_role) === 'Direktur')>Direktur</option>
                        <option value="Manager" @selected(old('signature_role', $penawaran->signature_role) === 'Manager')>Manager</option>
                        <option value="Sales" @selected(old('signature_role', $penawaran->signature_role) === 'Sales')>Sales</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Status Dokumen</label>
                    <select name="status" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="draft" @selected(old('status', $penawaran->status) === 'draft')>Draft</option>
                        <option value="submitted" @selected(old('status', $penawaran->status) === 'submitted')>Submitted</option>
                        <option value="approved" @selected(old('status', $penawaran->status) === 'approved')>Approved</option>
                        <option value="rejected" @selected(old('status', $penawaran->status) === 'rejected')>Rejected</option>
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
                    <textarea name="keterangan" rows="3" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">{{ old('keterangan', $penawaran->keterangan ?: $defaultKeterangan) }}</textarea>
                </div>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium mb-2">Pajak (%)</label>
                        <input type="number" min="0" max="100" step="0.01" name="tax_percent" id="tax-percent" value="{{ old('tax_percent', $penawaran->tax_percent) }}" class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
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
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Update Penawaran</button>
            </div>
        </form>
    </div>
</div>

@php
    $oldItems = old('items');
    $initialItems = is_array($oldItems) && count($oldItems) > 0
        ? $oldItems
        : $penawaran->items->map(fn ($item) => [
            'nama' => $item->nama,
            'rincian' => $item->rincian,
            'qty' => $item->qty,
            'satuan' => $item->satuan,
            'unit_price' => $item->unit_price,
        ])->values()->all();
@endphp

<script>
    const toCompanySelect = document.getElementById('to-company-select');
    const toAddressDisplay = document.getElementById('to-address-display');
    const toAddressInput = document.getElementById('to-address-input');
    const customerEmailWarning = document.getElementById('customer-email-warning');
    const itemRows = document.getElementById('item-rows');
    const addItemButton = document.getElementById('add-item');
    const taxInput = document.getElementById('tax-percent');
    const initialItems = @json($initialItems);
    let rowIndex = 0;

    function formatRupiah(value) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value || 0);
    }

    function rowTemplate(index, item = {}) {
        const nama = item.nama ?? '';
        const rincian = item.rincian ?? '';
        const qty = item.qty ?? 1;
        const unitPrice = item.unit_price ?? 0;
        const satuan = item.satuan ?? 'pcs';

        return `
            <tr class="border-b item-row">
                <td class="py-2 pr-3">
                    <input type="text" name="items[${index}][nama]" value="${String(nama).replace(/"/g, '&quot;')}" required class="w-full border rounded-lg px-3 py-2">
                    <textarea name="items[${index}][rincian]" rows="2"
                              placeholder="Rincian item (opsional)"
                              class="w-full border rounded-lg px-3 py-2 mt-2">${String(rincian ?? '')}</textarea>
                </td>
                <td class="py-2 pr-3">
                    <input type="number" name="items[${index}][qty]" step="0.01" min="0.01" value="${qty}" required class="w-full border rounded-lg px-3 py-2 qty-input">
                </td>
                <td class="py-2 pr-3">
                    <select name="items[${index}][satuan]" class="w-full border rounded-lg px-3 py-2">
                        <option value="month" ${satuan === 'month' ? 'selected' : ''}>month</option>
                        <option value="pcs" ${satuan === 'pcs' ? 'selected' : ''}>pcs</option>
                        <option value="item" ${satuan === 'item' ? 'selected' : ''}>item</option>
                        <option value="unit" ${satuan === 'unit' ? 'selected' : ''}>unit</option>
                    </select>
                </td>
                <td class="py-2 pr-3">
                    <input type="number" name="items[${index}][unit_price]" step="0.01" min="0" value="${unitPrice}" required class="w-full border rounded-lg px-3 py-2 price-input">
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

    function addRow(item = {}) {
        itemRows.insertAdjacentHTML('beforeend', rowTemplate(rowIndex, item));
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
    addItemButton.addEventListener('click', () => addRow());

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

    if (initialItems.length > 0) {
        initialItems.forEach((item) => addRow(item));
    } else {
        addRow();
    }

    fillCustomerAddress();
</script>
@endsection
