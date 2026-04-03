@extends('layouts.app')

@section('content')
<div>
    @if (session('success'))
        <div class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Invoice</h1>
    </div>

    <div class="bg-white rounded-xl shadow p-6 overflow-x-auto">
        @if ($invoices->isEmpty())
            <p class="text-gray-500">Belum ada data invoice.</p>
        @else
            <div class="space-y-3 md:hidden">
                @foreach ($invoices as $invoice)
                    <div class="border rounded-lg p-4">
                        <div class="font-semibold">{{ $invoice->nomor }}</div>
                        <div class="text-sm text-gray-600">{{ $invoice->tanggal }}</div>
                        <div class="text-sm mt-1">{{ $invoice->penawaran->to_company ?? $invoice->penawaran->customer_nama }}</div>
                        <div class="text-sm">Total: Rp {{ number_format($invoice->total, 2, ',', '.') }}</div>
                        <div class="mt-2">
                            @if (($invoice->payment_status ?? 'unpaid') === 'paid')
                                <div class="text-emerald-600 font-medium text-sm">Sudah Dibayarkan</div>
                            @elseif (auth()->user()?->isSuperAdmin())
                                <button type="button"
                                        title="Verifikasi Pembayaran"
                                        class="verify-payment-btn action-icon action-icon-emerald text-sm"
                                        data-action="{{ route('invoice.verify-payment', $invoice) }}"
                                        data-default-date="{{ now()->format('Y-m-d') }}">
                                    &#10004;
                                </button>
                            @else
                                <div class="text-amber-600 font-medium text-sm">Belum Dibayarkan</div>
                            @endif
                        </div>
                        <div class="mt-3">
                            <div class="action-buttons">
                                <a href="{{ route('invoice.show', $invoice) }}" title="Preview" class="action-icon action-icon-blue text-sm">&#128065;</a>
                                <form method="POST" action="{{ route('invoice.send', $invoice) }}" onsubmit="return confirm('Kirim invoice ke email customer?')">
                                    @csrf
                                    <button type="submit" title="Kirim" class="action-icon action-icon-gray text-sm">&#9993;</button>
                                </form>
                                <button type="button"
                                        title="Ubah Tanggal Cetak"
                                        class="print-date-btn action-icon action-icon-emerald text-sm"
                                        data-action="{{ route('invoice.update-print-date', $invoice) }}"
                                        data-default-date="{{ $invoice->tanggal }}">
                                    &#128197;
                                </button>
                                @if (($invoice->penawaran->status ?? null) === 'draft')
                                    <form method="POST" action="{{ route('invoice.destroy', $invoice) }}" onsubmit="return confirm('Hapus transaksi yang dibatalkan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Hapus Transaksi" class="action-icon action-icon-red text-sm">
                                            &#128465;
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <table class="hidden md:table w-full text-sm text-left">
                <thead>
                    <tr class="border-b">
                        <th class="py-3 pr-4">No Invoice</th>
                        <th class="py-3 pr-4">Tanggal</th>
                        <th class="py-3 pr-4">Customer</th>
                        <th class="py-3 pr-4">Total</th>
                        <th class="py-3 pr-4 text-center">Status Pembayaran</th>
                        <th class="py-3 pr-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoices as $invoice)
                        <tr class="border-b">
                            <td class="py-3 pr-4">{{ $invoice->nomor }}</td>
                            <td class="py-3 pr-4">{{ $invoice->tanggal }}</td>
                            <td class="py-3 pr-4">{{ $invoice->penawaran->to_company ?? $invoice->penawaran->customer_nama }}</td>
                            <td class="py-3 pr-4">Rp {{ number_format($invoice->total, 2, ',', '.') }}</td>
                            <td class="py-3 pr-4 text-center">
                                @if (($invoice->payment_status ?? 'unpaid') === 'paid')
                                    <div class="text-emerald-600 font-medium">Sudah Dibayarkan</div>
                                    <div class="text-xs text-gray-500">
                                        {{ $invoice->payment_date ? \Illuminate\Support\Carbon::parse($invoice->payment_date)->translatedFormat('d F Y') : '-' }}
                                    </div>
                                @elseif (auth()->user()?->isSuperAdmin())
                                    <button type="button"
                                            title="Verifikasi Pembayaran"
                                            class="verify-payment-btn action-icon action-icon-emerald hover:text-emerald-800"
                                            data-action="{{ route('invoice.verify-payment', $invoice) }}"
                                            data-default-date="{{ now()->format('Y-m-d') }}">
                                        &#10004;
                                    </button>
                                @else
                                    <div class="text-amber-600 font-medium">Belum Dibayarkan</div>
                                @endif
                            </td>
                            <td class="py-3 pr-4 text-center">
                                <div class="action-buttons">
                                    <a href="{{ route('invoice.show', $invoice) }}" title="Preview" class="action-icon action-icon-blue hover:text-blue-800">
                                        &#128065;
                                    </a>
                                    <form method="POST" action="{{ route('invoice.send', $invoice) }}" onsubmit="return confirm('Kirim invoice ke email customer?')">
                                        @csrf
                                        <button type="submit" title="Kirim" class="action-icon action-icon-gray hover:text-gray-800">
                                            &#9993;
                                        </button>
                                    </form>
                                    <button type="button"
                                            title="Ubah Tanggal Cetak"
                                            class="print-date-btn action-icon action-icon-emerald hover:text-emerald-800"
                                            data-action="{{ route('invoice.update-print-date', $invoice) }}"
                                            data-default-date="{{ $invoice->tanggal }}">
                                        &#128197;
                                    </button>
                                    @if (($invoice->penawaran->status ?? null) === 'draft')
                                        <form method="POST" action="{{ route('invoice.destroy', $invoice) }}" onsubmit="return confirm('Hapus transaksi yang dibatalkan ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus Transaksi" class="action-icon action-icon-red hover:text-red-800">
                                                &#128465;
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<div id="print-date-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Ubah Tanggal Cetak Invoice</h3>
        <p class="text-sm text-gray-600 mb-4">Pilih tanggal cetak baru jika invoice perlu dicetak di bulan berikutnya.</p>
        <form id="print-date-form" method="POST">
            @csrf
            <div>
                <label for="print_date" class="block text-sm font-medium mb-2">Tanggal Cetak</label>
                <input id="print_date" type="date" name="tanggal" required
                       class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" id="cancel-print-date" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Simpan</button>
            </div>
        </form>
    </div>
</div>

<div id="verify-payment-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Verifikasi Pembayaran</h3>
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
    const printDateModal = document.getElementById('print-date-modal');
    const printDateForm = document.getElementById('print-date-form');
    const printDateInput = document.getElementById('print_date');
    const cancelPrintDateButton = document.getElementById('cancel-print-date');

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

    document.querySelectorAll('.print-date-btn').forEach((button) => {
        button.addEventListener('click', () => {
            printDateForm.action = button.dataset.action;
            printDateInput.value = button.dataset.defaultDate || '';
            printDateModal.classList.remove('hidden');
            printDateModal.classList.add('flex');
        });
    });

    function closePrintDateModal() {
        printDateModal.classList.add('hidden');
        printDateModal.classList.remove('flex');
    }

    cancelPrintDateButton?.addEventListener('click', closePrintDateModal);

    printDateModal?.addEventListener('click', (event) => {
        if (event.target === printDateModal) {
            closePrintDateModal();
        }
    });
</script>
@endsection
