@extends('layouts.app')

@section('content')
<div>
    <h1 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-4 sm:mb-6">Buat Surat Penawaran Mitra</h1>

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
                    <label class="block text-sm font-medium mb-2">Pilih Mitra</label>
                    <select name="mitra_id" id="mitra-select" required class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih mitra</option>
                        @foreach ($mitras as $mitra)
                            <option value="{{ $mitra->id }}"
                                    data-nomor="{{ $mitra->nomor_penawaran }}"
                                    @selected(old('mitra_id') == $mitra->id)>
                                {{ $mitra->nama }}
                            </option>
                        @endforeach
                    </select>
                    @if ($mitras->isEmpty())
                        <p class="text-xs text-red-600 mt-1">Belum ada mitra. Tambahkan mitra terlebih dahulu.</p>
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Nomor Surat</label>
                    <input id="nomor-preview" type="text" value="{{ $nomorPreview }}" disabled class="w-full bg-gray-100 border rounded-lg px-4 py-2">
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
                <div class="mb-3 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Item Penawaran</h2>
                        <p class="text-sm text-gray-500">Tampilan kartu agar nyaman di mobile.</p>
                    </div>
                    <button type="button" id="add-item" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-white shadow-sm transition hover:bg-blue-700">+ Tambah Item</button>
                </div>
                <div id="item-rows" class="space-y-4"></div>
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
                    <div class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        <div class="flex items-center justify-between rounded-lg border bg-gray-50 p-3">
                            <span>Subtotal</span>
                            <span id="subtotal-display" class="font-medium">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border bg-gray-50 p-3">
                            <span>Pajak</span>
                            <span id="tax-display" class="font-medium">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border bg-gray-50 p-3">
                            <span>PPh23 (2%)</span>
                            <span id="pph23-display" class="font-medium">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border bg-blue-50 p-3 font-semibold">
                            <span>Total</span>
                            <span id="total-display">Rp 0</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border bg-blue-50 p-3 font-semibold sm:col-span-2">
                            <span>Amount (Net)</span>
                            <span id="net-display">Rp 0</span>
                        </div>
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
    const mitraSelect = document.getElementById('mitra-select');
    const nomorPreviewInput = document.getElementById('nomor-preview');
    const defaultNomorPreview = @json($nomorPreview);
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
            <div class="item-row rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="mb-4 flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold text-slate-800">Detail Item</p>
                        <p class="text-xs text-slate-500">Isi nama, rincian, qty, satuan, dan harga satuannya.</p>
                    </div>
                    <button type="button" class="remove-item inline-flex items-center rounded-lg border border-red-200 bg-white px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50">
                        Hapus
                    </button>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-12">
                    <label class="block xl:col-span-4">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Item</span>
                        <input type="text" name="items[${index}][nama]" required class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                    </label>
                    <label class="block xl:col-span-4">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Rincian</span>
                        <textarea name="items[${index}][rincian]" rows="2" placeholder="Rincian item (opsional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </label>
                    <label class="block sm:col-span-1 xl:col-span-1">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Qty</span>
                        <input type="number" name="items[${index}][qty]" step="0.01" min="0.01" value="1" required class="qty-input w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                    </label>
                    <label class="block sm:col-span-1 xl:col-span-1">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Satuan</span>
                        <select name="items[${index}][satuan]" class="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                            <option value="month">month</option>
                            <option value="pcs">pcs</option>
                            <option value="item">item</option>
                            <option value="unit">unit</option>
                        </select>
                    </label>
                    <label class="block xl:col-span-1">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Unit Price</span>
                        <input type="number" name="items[${index}][unit_price]" step="0.01" min="0" value="0" required class="price-input w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-blue-500 focus:ring-blue-500">
                    </label>
                    <div class="block xl:col-span-1">
                        <span class="mb-1 block text-sm font-medium text-slate-700">Amount</span>
                        <div class="amount-display flex min-h-[42px] items-center rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-slate-800">Rp 0</div>
                    </div>
                </div>
            </div>
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
        const divisor = 1 + (taxPercent / 100 || 0);
        const pph23 = divisor > 0 ? (total / divisor) * 0.02 : 0;
        const netAmount = total - pph23;

        document.getElementById('subtotal-display').textContent = formatRupiah(subtotal);
        document.getElementById('tax-display').textContent = formatRupiah(tax);
        document.getElementById('pph23-display').textContent = formatRupiah(pph23);
        document.getElementById('total-display').textContent = formatRupiah(total);
        document.getElementById('net-display').textContent = formatRupiah(netAmount);
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

    function updateNomorPreview() {
        if (!mitraSelect || !nomorPreviewInput) return;
        const selectedOption = mitraSelect.options[mitraSelect.selectedIndex];
        const nomor = selectedOption?.dataset?.nomor || '';
        nomorPreviewInput.value = nomor || defaultNomorPreview;
    }

    mitraSelect?.addEventListener('change', updateNomorPreview);
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

        event.target.closest('.item-row')?.remove();
        recalculate();
    });

    taxInput.addEventListener('input', recalculate);

    addRow();
    fillCustomerAddress();
    updateNomorPreview();
</script>
@endsection
