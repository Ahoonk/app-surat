@extends('layouts.app')

@section('content')
<div class="space-y-6">
    <section class="panel p-6">
        <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-slate-500">Dokumen</p>
                <h1 class="section-title mt-2">Faktur Pajak</h1>
                <p class="mt-3 text-sm text-slate-600">Invoice yang sudah tercetak akan muncul di sini untuk upload dokumen faktur pajak.</p>
            </div>
            <div class="rounded-2xl bg-slate-50 px-4 py-3 text-sm text-slate-600">
                <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Total Invoice</div>
                <div class="mt-1 text-lg font-semibold text-slate-900">{{ $invoices->count() }}</div>
            </div>
        </div>
    </section>

    @if (session('success'))
        <section class="panel p-6">
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('success') }}
            </div>
        </section>
    @endif

    <section class="grid gap-4">
        @forelse ($invoices as $invoice)
            <article class="panel p-6">
                <div class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr_0.9fr_0.8fr_auto] xl:items-end">
                    <div>
                        <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Invoice</p>
                        <h3 class="mt-2 text-xl font-semibold text-slate-900">{{ $invoice->nomor }}</h3>
                        <p class="mt-1 text-sm text-slate-500">{{ \Illuminate\Support\Carbon::parse($invoice->tanggal)->translatedFormat('d F Y') }}</p>
                        <p class="mt-2 text-sm text-slate-600">{{ $invoice->penawaran->to_company ?? $invoice->penawaran->customer_nama }}</p>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Upload</div>
                        <div class="mt-2 flex flex-wrap items-center gap-2">
                            <form method="POST" action="{{ route('faktur-pajak.store', $invoice) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                                @csrf
                                <label class="button-ghost cursor-pointer">
                                    <input id="upload-faktur-{{ $invoice->id }}" type="file" name="dokumen" accept=".pdf,.jpg,.jpeg,.png" class="hidden" onchange="this.form.submit()">
                                    {{ $invoice->fakturPajak ? 'Ubah Dokumen' : 'Upload Dokumen' }}
                                </label>
                            </form>
                        </div>
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Status Dokumen</div>
                        <div class="mt-2 font-semibold {{ $invoice->fakturPajak ? 'text-emerald-600' : 'text-amber-600' }}">
                            {{ $invoice->fakturPajak ? 'Sudah Upload' : 'Belum Upload' }}
                        </div>
                        @if ($invoice->fakturPajak)
                            <div class="mt-1 text-xs text-slate-500">{{ $invoice->fakturPajak->dokumen_name }}</div>
                        @endif
                    </div>

                    <div class="rounded-2xl bg-slate-50 p-4 text-sm text-slate-600">
                        <div class="text-xs uppercase tracking-[0.25em] text-slate-400">Pembayaran</div>
                        @if (! $invoice->fakturPajak)
                            <div class="mt-2 text-slate-500">-</div>
                        @elseif (($invoice->fakturPajak->payment_status ?? 'unpaid') === 'paid')
                            <div class="mt-2 font-semibold text-emerald-600">Sudah Dibayarkan</div>
                            <div class="mt-1 text-xs text-slate-500">
                                {{ $invoice->fakturPajak->payment_date ? \Illuminate\Support\Carbon::parse($invoice->fakturPajak->payment_date)->translatedFormat('d F Y') : '-' }}
                            </div>
                        @elseif (auth()->user()?->isSuperAdmin())
                            <button
                                type="button"
                                class="verify-payment-btn button-primary mt-2"
                                data-action="{{ route('faktur-pajak.verify-payment', $invoice) }}"
                                data-default-date="{{ now()->format('Y-m-d') }}"
                            >
                                Verifikasi
                            </button>
                        @else
                            <div class="mt-2 font-semibold text-amber-600">Belum Dibayarkan</div>
                        @endif
                    </div>

                    <div class="flex flex-wrap gap-2">
                        @if ($invoice->fakturPajak)
                            <a href="{{ asset('storage/' . $invoice->fakturPajak->dokumen_path) }}" target="_blank" class="button-ghost">Preview</a>
                        @endif

                        @php
                            $showDelete = ($invoice->penawaran->status ?? null) === 'draft';
                        @endphp

                        @if ($showDelete)
                            <form method="POST" action="{{ route('invoice.destroy', $invoice) }}" onsubmit="return confirm('Hapus transaksi yang dibatalkan ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="button-danger">Hapus</button>
                            </form>
                        @endif
                    </div>
                </div>
            </article>
        @empty
            <section class="panel p-6">
                <p class="text-sm text-slate-500">Belum ada invoice tercetak.</p>
            </section>
        @endforelse
    </section>
</div>

<div id="verify-payment-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-950/40 p-4 backdrop-blur-sm">
    <div class="w-full max-w-md rounded-[24px] bg-white p-6 shadow-2xl">
        <h3 class="text-lg font-semibold text-slate-900">Verifikasi Pembayaran Faktur Pajak</h3>
        <p class="mt-2 text-sm text-slate-500">Pilih tanggal pembayaran untuk mengubah status menjadi sudah dibayarkan.</p>

        <form id="verify-payment-form" method="POST" class="mt-5 space-y-4">
            @csrf
            <label class="block">
                <span class="mb-2 block text-sm font-medium text-slate-700">Tanggal Pembayaran</span>
                <input id="payment_date" type="date" name="payment_date" required class="input">
            </label>

            <div class="flex justify-end gap-3">
                <button type="button" id="cancel-verify-payment" class="button-ghost">Batal</button>
                <button type="submit" class="button-primary">Submit</button>
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
