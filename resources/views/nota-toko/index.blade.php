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

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-800">Nota Toko</h1>
        <a href="{{ route('nota-toko.create') }}" class="inline-flex items-center justify-center bg-blue-600 text-white px-5 py-2 rounded-lg shadow hover:bg-blue-700 transition">
            + Buat Nota Toko
        </a>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        @if ($notaTokos->isEmpty())
            <p class="text-gray-500">Belum ada data nota toko.</p>
        @else
            <div class="space-y-3 md:hidden">
                @foreach ($notaTokos as $notaToko)
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-base font-semibold text-gray-900">{{ $notaToko->nomor }}</div>
                                <div class="text-sm text-gray-500">{{ \Illuminate\Support\Carbon::parse($notaToko->tanggal)->translatedFormat('d F Y') }}</div>
                            </div>
                            @if (($notaToko->payment_status ?? 'unpaid') === 'paid')
                                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">Lunas</span>
                            @else
                                <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-700">Belum Lunas</span>
                            @endif
                        </div>

                        <div class="mt-3 space-y-1 text-sm text-gray-700">
                            <div><span class="font-medium text-gray-500">Customer:</span> {{ $notaToko->customer_nama }}</div>
                            <div><span class="font-medium text-gray-500">Total:</span> Rp {{ number_format($notaToko->total, 2, ',', '.') }}</div>
                            @if (($notaToko->payment_status ?? 'unpaid') === 'paid')
                                <div><span class="font-medium text-gray-500">Dibayar:</span> {{ $notaToko->payment_date ? \Illuminate\Support\Carbon::parse($notaToko->payment_date)->translatedFormat('d F Y') : '-' }}</div>
                            @endif
                        </div>

                        <div class="mt-4 action-buttons flex-wrap justify-start">
                            <a href="{{ route('nota-toko.show', $notaToko) }}" title="Preview" class="action-icon action-icon-blue">&#128065;</a>
                            <a href="{{ route('nota-toko.edit', $notaToko) }}" title="Ubah" class="action-icon action-icon-emerald">&#9998;</a>
                            <form method="POST" action="{{ route('nota-toko.send', $notaToko) }}" onsubmit="return confirm('Kirim nota toko ke email customer?')">
                                @csrf
                                <button type="submit" title="Kirim" class="action-icon action-icon-gray">&#9993;</button>
                            </form>
                            @if (($notaToko->payment_status ?? 'unpaid') !== 'paid' && in_array(auth()->user()?->role, ['admin', 'superadmin'], true))
                                <button type="button"
                                        title="Verifikasi Pembayaran"
                                        class="verify-nota-btn action-icon action-icon-emerald"
                                        data-action="{{ route('nota-toko.verify-payment', $notaToko) }}"
                                        data-default-date="{{ now()->format('Y-m-d') }}">
                                    &#10004;
                                </button>
                            @endif
                            <form method="POST" action="{{ route('nota-toko.destroy', $notaToko) }}" onsubmit="return confirm('Hapus nota toko ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Hapus" class="action-icon action-icon-red">&#128465;</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead>
                        <tr class="border-b">
                            <th class="py-3 pr-4">Nomor</th>
                            <th class="py-3 pr-4">Tanggal</th>
                            <th class="py-3 pr-4">Customer</th>
                            <th class="py-3 pr-4">Total</th>
                            <th class="py-3 pr-4">Status Bayar</th>
                            <th class="py-3 pr-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notaTokos as $notaToko)
                            <tr class="border-b">
                                <td class="py-3 pr-4">{{ $notaToko->nomor }}</td>
                                <td class="py-3 pr-4">{{ \Illuminate\Support\Carbon::parse($notaToko->tanggal)->translatedFormat('d F Y') }}</td>
                                <td class="py-3 pr-4">{{ $notaToko->customer_nama }}</td>
                                <td class="py-3 pr-4">Rp {{ number_format($notaToko->total, 2, ',', '.') }}</td>
                                <td class="py-3 pr-4">
                                    @if (($notaToko->payment_status ?? 'unpaid') === 'paid')
                                        <div class="text-emerald-600 font-medium">Sudah Dibayar</div>
                                        <div class="text-xs text-gray-500">
                                            {{ $notaToko->payment_date ? \Illuminate\Support\Carbon::parse($notaToko->payment_date)->translatedFormat('d F Y') : '-' }}
                                        </div>
                                    @else
                                        <div class="text-amber-600 font-medium">Belum Dibayar</div>
                                    @endif
                                </td>
                                <td class="py-3 pr-4">
                                    <div class="action-buttons">
                                        <a href="{{ route('nota-toko.show', $notaToko) }}" title="Preview" class="action-icon action-icon-blue">&#128065;</a>
                                        <a href="{{ route('nota-toko.edit', $notaToko) }}" title="Ubah" class="action-icon action-icon-emerald">&#9998;</a>
                                        <form method="POST" action="{{ route('nota-toko.send', $notaToko) }}" onsubmit="return confirm('Kirim nota toko ke email customer?')">
                                            @csrf
                                            <button type="submit" title="Kirim" class="action-icon action-icon-gray">&#9993;</button>
                                        </form>
                                        @if (($notaToko->payment_status ?? 'unpaid') !== 'paid' && in_array(auth()->user()?->role, ['admin', 'superadmin'], true))
                                            <button type="button"
                                                    title="Verifikasi Pembayaran"
                                                    class="verify-nota-btn action-icon action-icon-emerald"
                                                    data-action="{{ route('nota-toko.verify-payment', $notaToko) }}"
                                                    data-default-date="{{ now()->format('Y-m-d') }}">
                                                &#10004;
                                            </button>
                                        @endif
                                        <form method="POST" action="{{ route('nota-toko.destroy', $notaToko) }}" onsubmit="return confirm('Hapus nota toko ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Hapus" class="action-icon action-icon-red">&#128465;</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

<div id="verify-nota-modal" class="fixed inset-0 bg-black/40 hidden items-center justify-center p-4 z-50">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Verifikasi Pembayaran Nota Toko</h3>
        <p class="text-sm text-gray-600 mb-4">Pilih tanggal pembayaran untuk mengubah status menjadi sudah dibayar.</p>
        <form id="verify-nota-form" method="POST">
            @csrf
            <div>
                <label for="nota_payment_date" class="block text-sm font-medium mb-2">Tanggal Pembayaran</label>
                <input id="nota_payment_date" type="date" name="payment_date" required
                       class="w-full border rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" id="cancel-verify-nota" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Submit</button>
            </div>
        </form>
    </div>
</div>

<script>
    const verifyNotaModal = document.getElementById('verify-nota-modal');
    const verifyNotaForm = document.getElementById('verify-nota-form');
    const notaPaymentDateInput = document.getElementById('nota_payment_date');
    const cancelVerifyNotaButton = document.getElementById('cancel-verify-nota');

    document.querySelectorAll('.verify-nota-btn').forEach((button) => {
        button.addEventListener('click', () => {
            verifyNotaForm.action = button.dataset.action;
            notaPaymentDateInput.value = button.dataset.defaultDate || '';
            verifyNotaModal.classList.remove('hidden');
            verifyNotaModal.classList.add('flex');
        });
    });

    function closeVerifyNotaModal() {
        verifyNotaModal.classList.add('hidden');
        verifyNotaModal.classList.remove('flex');
    }

    cancelVerifyNotaButton?.addEventListener('click', closeVerifyNotaModal);

    verifyNotaModal?.addEventListener('click', (event) => {
        if (event.target === verifyNotaModal) {
            closeVerifyNotaModal();
        }
    });
</script>
@endsection
