@extends('layouts.app')

@section('content')
<div class="space-y-8">
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
        <h1 class="text-2xl font-semibold text-gray-800">Purchasing Order</h1>
        <p class="text-sm text-gray-500 mt-1">Upload dokumen PO (PDF/JPG/PNG). Setelah upload, cetak invoice melalui kolom status di daftar dokumen.</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Penawaran Approved (Siap Upload PO)</h2>
        @if ($approvedSatuan->isEmpty() && $approvedKontrak->isEmpty())
            <p class="text-gray-500 text-sm">Belum ada penawaran approved yang menunggu upload PO.</p>
        @else
            <div class="space-y-3">
                @foreach ($approvedSatuan->concat($approvedKontrak) as $penawaran)
                    <form action="{{ route('purchasing-order.store') }}" method="POST" enctype="multipart/form-data" class="po-upload-form grid grid-cols-1 md:grid-cols-8 gap-3 items-end border rounded-lg p-4">
                        @csrf
                        <input type="hidden" name="penawaran_id" value="{{ $penawaran->id }}">
                        <div class="md:col-span-2">
                            <p class="font-medium">{{ $penawaran->nomor }}</p>
                            <p class="text-sm text-gray-500">{{ $penawaran->to_company ?? $penawaran->customer_nama }}</p>
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Jenis</label>
                            <input type="text" value="{{ ucfirst($penawaran->jenis_kontrak) }}" disabled class="w-full border rounded-lg px-3 py-2 bg-gray-100">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Dokumen PO</label>
                            <input type="file" name="dokumen" accept=".pdf,.jpg,.jpeg,.png" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Nomor PO</label>
                            <input type="text" name="nomor_po" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Tanggal PO</label>
                            <input type="date" name="tanggal_po" required class="w-full border rounded-lg px-3 py-2">
                        </div>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                            <button type="submit" class="submit-po-btn w-full sm:w-auto px-4 py-2 bg-blue-600 text-white rounded-lg disabled:opacity-50 disabled:cursor-not-allowed" disabled>Submit</button>
                            <button type="submit"
                                    formaction="{{ route('purchasing-order.cancel', $penawaran) }}"
                                    formmethod="POST"
                                    formnovalidate
                                    onclick="return confirm('Batalkan approval dan kembalikan ke submitted?')"
                                    class="w-full sm:w-auto px-4 py-2 bg-red-100 text-red-700 rounded-lg">
                                Cancel
                            </button>
                        </div>
                    </form>
                @endforeach
            </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-semibold mb-4">Daftar Dokumen Purchasing Order</h2>
        @if ($existingData->isEmpty())
            <p class="text-gray-500 text-sm">Belum ada dokumen PO yang diupload.</p>
        @else
            <div class="space-y-3 md:hidden">
                @foreach ($existingData as $penawaran)
                    @php $latestInvoice = $penawaran->invoices->first(); @endphp
                    <div class="border rounded-lg p-4 text-sm">
                        <div class="font-semibold">{{ $penawaran->nomor }}</div>
                        <div class="capitalize text-gray-600">{{ $penawaran->jenis_kontrak }}</div>
                        <div class="mt-2">
                            <a href="{{ asset('storage/' . $penawaran->purchasingOrder->dokumen_path) }}" target="_blank" class="text-blue-600">
                                {{ $penawaran->purchasingOrder->dokumen_name }}
                            </a>
                        </div>
                        <div class="mt-1">Nomor PO: {{ $penawaran->purchasingOrder->nomor_po ?? '-' }}</div>
                        <div>Tanggal PO: {{ $penawaran->purchasingOrder->tanggal_po ?? '-' }}</div>
                        <div class="mt-3">
                            @if (! $latestInvoice)
                                <form action="{{ route('purchasing-order.create-invoice', $penawaran) }}" method="POST">@csrf
                                    <button type="submit" class="text-blue-600">Cetak Invoice</button>
                                </form>
                            @elseif ($penawaran->jenis_kontrak === 'kontrak')
                                <button type="button" class="text-blue-600 next-invoice-btn"
                                        data-action="{{ route('purchasing-order.next-invoice', $penawaran) }}"
                                        data-default-date="{{ now()->format('Y-m-d') }}">Cetak Invoice Berikutnya</button>
                            @else
                                <span class="text-emerald-600 font-medium">Selesai</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="py-3 pr-4">Nomor Penawaran</th>
                            <th class="py-3 pr-4">Jenis</th>
                            <th class="py-3 pr-4">Dokumen PO</th>
                            <th class="py-3 pr-4">Nomor PO</th>
                            <th class="py-3 pr-4">Tanggal PO</th>
                            <th class="py-3 pr-4">Invoice Terakhir</th>
                            <th class="py-3 pr-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($existingData as $penawaran)
                            <tr class="border-b">
                                <td class="py-3 pr-4">{{ $penawaran->nomor }}</td>
                                <td class="py-3 pr-4 capitalize">{{ $penawaran->jenis_kontrak }}</td>
                                <td class="py-3 pr-4">
                                    <a href="{{ asset('storage/' . $penawaran->purchasingOrder->dokumen_path) }}"
                                       target="_blank"
                                       class="text-blue-600 hover:text-blue-800">
                                        {{ $penawaran->purchasingOrder->dokumen_name }}
                                    </a>
                                </td>
                                <td class="py-3 pr-4">{{ $penawaran->purchasingOrder->nomor_po ?? '-' }}</td>
                                <td class="py-3 pr-4">{{ $penawaran->purchasingOrder->tanggal_po ?? '-' }}</td>
                                <td class="py-3 pr-4">
                                    @php
                                        $latestInvoice = $penawaran->invoices->first();
                                    @endphp
                                    @if ($latestInvoice)
                                        <div class="font-medium">{{ $latestInvoice->nomor }}</div>
                                        <div class="text-xs text-gray-500">{{ $latestInvoice->tanggal }}</div>
                                    @else
                                        <span class="text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="py-3 pr-4">
                                    @if (! $latestInvoice)
                                        <form action="{{ route('purchasing-order.create-invoice', $penawaran) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-blue-600 hover:text-blue-800">
                                                Cetak Invoice
                                            </button>
                                        </form>
                                    @elseif ($penawaran->jenis_kontrak === 'kontrak')
                                        <button type="button"
                                                class="text-blue-600 hover:text-blue-800 next-invoice-btn"
                                                data-action="{{ route('purchasing-order.next-invoice', $penawaran) }}"
                                                data-default-date="{{ now()->format('Y-m-d') }}">
                                            Cetak Invoice Berikutnya
                                        </button>
                                    @else
                                        <span class="text-emerald-600 font-medium">Selesai</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div id="next-invoice-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Cetak Invoice Berikutnya</h3>
        <p class="text-sm text-gray-600 mb-4">Invoice baru akan dibuat dari data Penawaran dan PO yang sama.</p>
        <form id="next-invoice-form" method="POST">
            @csrf
            <div>
                <label for="next_invoice_date" class="block text-sm font-medium mb-2">Tanggal Invoice</label>
                <input id="next_invoice_date" type="date" name="invoice_date" required
                       class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" id="cancel-next-invoice" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Submit & Cetak</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('.po-upload-form').forEach((form) => {
        const submitButton = form.querySelector('.submit-po-btn');
        const fileInput = form.querySelector('input[name="dokumen"]');
        const nomorPoInput = form.querySelector('input[name="nomor_po"]');
        const tanggalPoInput = form.querySelector('input[name="tanggal_po"]');

        const toggleSubmit = () => {
            const ready = fileInput?.files?.length > 0
                && nomorPoInput?.value?.trim() !== ''
                && tanggalPoInput?.value?.trim() !== '';
            submitButton.disabled = !ready;
        };

        fileInput?.addEventListener('change', toggleSubmit);
        nomorPoInput?.addEventListener('input', toggleSubmit);
        tanggalPoInput?.addEventListener('change', toggleSubmit);
    });

    const nextInvoiceModal = document.getElementById('next-invoice-modal');
    const nextInvoiceForm = document.getElementById('next-invoice-form');
    const nextInvoiceDateInput = document.getElementById('next_invoice_date');
    const cancelNextInvoiceButton = document.getElementById('cancel-next-invoice');

    document.querySelectorAll('.next-invoice-btn').forEach((button) => {
        button.addEventListener('click', () => {
            nextInvoiceForm.action = button.dataset.action;
            nextInvoiceDateInput.value = button.dataset.defaultDate || '';
            nextInvoiceModal.classList.remove('hidden');
            nextInvoiceModal.classList.add('flex');
        });
    });

    function closeNextInvoiceModal() {
        nextInvoiceModal.classList.add('hidden');
        nextInvoiceModal.classList.remove('flex');
    }

    cancelNextInvoiceButton.addEventListener('click', closeNextInvoiceModal);

    nextInvoiceModal.addEventListener('click', (event) => {
        if (event.target === nextInvoiceModal) {
            closeNextInvoiceModal();
        }
    });
</script>
@endsection
