@extends('layouts.app')

@section('content')
<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    <div>
        <h1 class="text-2xl font-semibold text-gray-800">Faktur Pajak</h1>
        <p class="text-sm text-gray-500 mt-1">Invoice yang sudah tercetak akan otomatis muncul di sini untuk upload dokumen faktur pajak.</p>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        @if ($invoices->isEmpty())
            <p class="text-gray-500 text-sm">Belum ada invoice tercetak.</p>
        @else
            <div class="space-y-4">
                @foreach ($invoices as $invoice)
                    <div class="border rounded-lg p-4 space-y-3">
                        <div class="grid grid-cols-1 md:grid-cols-7 gap-3 items-end">
                            <div>
                                <p class="text-xs text-gray-500">Nomor Invoice</p>
                                <p class="font-medium">{{ $invoice->nomor }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Tanggal Invoice</p>
                                <p>{{ \Illuminate\Support\Carbon::parse($invoice->tanggal)->translatedFormat('d F Y') }}</p>
                            </div>
                            <div class="md:col-span-2">
                                <p class="text-xs text-gray-500">Tujuan</p>
                                <p>{{ $invoice->penawaran->to_company ?? $invoice->penawaran->customer_nama }}</p>
                            </div>
                            <div class="text-center">
                                <p class="text-xs text-gray-500">Aksi</p>
                                @php
                                    $showPreview = (bool) $invoice->fakturPajak;
                                    $showDelete = ($invoice->penawaran->status ?? null) === 'draft';
                                @endphp
                                <div class="action-buttons justify-center mt-1">
                                    <form method="POST" action="{{ route('faktur-pajak.store', $invoice) }}" enctype="multipart/form-data">
                                        @csrf
                                        <input id="upload-faktur-{{ $invoice->id }}" type="file" name="dokumen" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="this.form.submit()">
                                        <label for="upload-faktur-{{ $invoice->id }}"
                                               title="{{ $invoice->fakturPajak ? 'Ubah Upload Dokumen' : 'Upload Dokumen' }}"
                                               class="action-icon action-icon-emerald hover:text-emerald-800 cursor-pointer">
                                            &#8682;
                                        </label>
                                    </form>

                                    @if ($showPreview)
                                        <a href="{{ asset('storage/' . $invoice->fakturPajak->dokumen_path) }}"
                                           target="_blank"
                                           title="Preview Dokumen"
                                           class="action-icon action-icon-blue hover:text-blue-800">
                                            &#128065;
                                        </a>
                                    @endif

                                    @if ($showDelete)
                                        <form method="POST" action="{{ route('invoice.destroy', $invoice) }}" onsubmit="return confirm('Hapus transaksi yang dibatalkan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Transaksi" class="action-icon action-icon-red hover:text-red-800">
                                                &#128465;
                                            </button>
                                        </form>
                                    @endif

                                    @if (! $showPreview && ! $showDelete)
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Status</p>
                                @if ($invoice->fakturPajak)
                                    <p class="text-emerald-600 font-medium">Sudah Upload</p>
                                @else
                                    <p class="text-amber-600 font-medium">Belum Upload</p>
                                @endif
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Status Pembayaran</p>
                                @if (! $invoice->fakturPajak)
                                    <p class="text-gray-500 font-medium">-</p>
                                @elseif (($invoice->fakturPajak->payment_status ?? 'unpaid') === 'paid')
                                    <p class="text-emerald-600 font-medium">Sudah Dibayarkan</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $invoice->fakturPajak->payment_date ? \Illuminate\Support\Carbon::parse($invoice->fakturPajak->payment_date)->translatedFormat('d F Y') : '-' }}
                                    </p>
                                @elseif (auth()->user()?->isSuperAdmin())
                                    <button type="button"
                                            title="Verifikasi Pembayaran"
                                            class="verify-payment-btn action-icon action-icon-emerald hover:text-emerald-800"
                                            data-action="{{ route('faktur-pajak.verify-payment', $invoice) }}"
                                            data-default-date="{{ now()->format('Y-m-d') }}">
                                        &#10004;
                                    </button>
                                @else
                                    <p class="text-amber-600 font-medium">Belum Dibayarkan</p>
                                @endif
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div id="verify-payment-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Verifikasi Pembayaran Faktur Pajak</h3>
        <p class="text-sm text-gray-600 mb-4">Pilih tanggal pembayaran untuk mengubah status menjadi sudah dibayarkan.</p>
        <form id="verify-payment-form" method="POST">
            @csrf
            <div>
                <label for="payment_date" class="block text-sm font-medium mb-2">Tanggal Pembayaran</label>
                <input id="payment_date" type="date" name="payment_date" required
                       class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" id="cancel-verify-payment" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
    const verifyPaymentModal = document.getElementById('verify-payment-modal');
    const verifyPaymentForm = document.getElementById('verify-payment-form');
    const paymentDateInput = document.getElementById('payment_date');
    const cancelVerifyPaymentButton = document.getElementById('cancel-verify-payment');

    document.querySelectorAll('.verify-payment-btn').forEach((button) => {
        button.addEventListener('click', () => {
            verifyPaymentForm.action = button.dataset.action;
            paymentDateInput.value = button.dataset.defaultDate || '';
            verifyPaymentModal.classList.remove('hidden');
            verifyPaymentModal.classList.add('flex');
        });
    });

    function closeVerifyPaymentModal() {
        verifyPaymentModal.classList.add('hidden');
        verifyPaymentModal.classList.remove('flex');
    }

    cancelVerifyPaymentButton?.addEventListener('click', closeVerifyPaymentModal);

    verifyPaymentModal?.addEventListener('click', (event) => {
        if (event.target === verifyPaymentModal) {
            closeVerifyPaymentModal();
        }
    });
</script>
@endsection
