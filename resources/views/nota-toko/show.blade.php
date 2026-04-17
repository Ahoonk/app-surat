@extends('layouts.app')

@section('content')
@php
    $terbilang = function (int $angka) use (&$terbilang): string {
        $angka = abs($angka);
        $huruf = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($angka < 12) return $huruf[$angka];
        if ($angka < 20) return $terbilang($angka - 10) . ' Belas';
        if ($angka < 100) return $terbilang(intdiv($angka, 10)) . ' Puluh' . ($angka % 10 ? ' ' . $terbilang($angka % 10) : '');
        if ($angka < 200) return 'Seratus' . ($angka - 100 ? ' ' . $terbilang($angka - 100) : '');
        if ($angka < 1000) return $terbilang(intdiv($angka, 100)) . ' Ratus' . ($angka % 100 ? ' ' . $terbilang($angka % 100) : '');
        if ($angka < 2000) return 'Seribu' . ($angka - 1000 ? ' ' . $terbilang($angka - 1000) : '');
        if ($angka < 1000000) return $terbilang(intdiv($angka, 1000)) . ' Ribu' . ($angka % 1000 ? ' ' . $terbilang($angka % 1000) : '');
        if ($angka < 1000000000) return $terbilang(intdiv($angka, 1000000)) . ' Juta' . ($angka % 1000000 ? ' ' . $terbilang($angka % 1000000) : '');
        if ($angka < 1000000000000) return $terbilang(intdiv($angka, 1000000000)) . ' Miliar' . ($angka % 1000000000 ? ' ' . $terbilang($angka % 1000000000) : '');
        if ($angka < 1000000000000000) return $terbilang(intdiv($angka, 1000000000000)) . ' Triliun' . ($angka % 1000000000000 ? ' ' . $terbilang($angka % 1000000000000) : '');
        return (string) $angka;
    };
    $totalValue = (float) $notaToko->total;
    $totalInt = (int) floor($totalValue);
    $totalSen = (int) round(($totalValue - $totalInt) * 100);
    $terbilangTotal = trim($terbilang($totalInt)) ?: 'Nol';
    $terbilangTotal = $terbilangTotal . ' Rupiah' . ($totalSen > 0 ? ' ' . $terbilang($totalSen) . ' Sen' : '');
    $templateNotaPath = public_path('storage/logos/template-nota.png');
    $templateNotaAsset = file_exists($templateNotaPath)
        ? asset('storage/logos/template-nota.png') . '?v=' . filemtime($templateNotaPath)
        : null;
    $qrPath = public_path('storage/logos/qr-bayu-suderajat.png');
    $qrAsset = file_exists($qrPath)
        ? asset('storage/logos/qr-bayu-suderajat.png') . '?v=' . filemtime($qrPath)
        : null;
    $previewPaperStyle = 'width:100%;max-width:794px;min-height:567px;';
@endphp
<div>
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 mb-6">
        <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">Preview Nota Toko</h1>
        <div class="flex gap-2">
            <a href="{{ route('nota-toko.pdf', ['notaToko' => $notaToko, 'download' => 1]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Export PDF</a>
            <a href="{{ route('nota-toko.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Kembali</a>
        </div>
    </div>

    <div class="mb-4 rounded-xl border bg-white p-4 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <p class="text-sm text-gray-500">Status Pembayaran</p>
            @if (($notaToko->payment_status ?? 'unpaid') === 'paid')
                <p class="font-semibold text-emerald-600">Sudah Dibayar</p>
                <p class="text-xs text-gray-500">
                    {{ $notaToko->payment_date ? \Illuminate\Support\Carbon::parse($notaToko->payment_date)->translatedFormat('d F Y') : '-' }}
                </p>
            @else
                <p class="font-semibold text-amber-600">Belum Dibayar</p>
            @endif
        </div>
        @if (($notaToko->payment_status ?? 'unpaid') !== 'paid' && in_array(auth()->user()?->role, ['admin', 'superadmin'], true))
            <button type="button"
                    title="Verifikasi Pembayaran"
                    class="verify-nota-btn px-4 py-2 bg-emerald-600 text-white rounded-lg"
                    data-action="{{ route('nota-toko.verify-payment', $notaToko) }}"
                    data-default-date="{{ now()->format('Y-m-d') }}">
                Verifikasi Bayar
            </button>
        @endif
    </div>

    <div class="mx-auto bg-white rounded-2xl shadow-xl relative overflow-hidden" style="{{ $previewPaperStyle }}">
        @if ($templateNotaAsset)
            <div style="position:absolute;inset:0;background-image:url('{{ $templateNotaAsset }}');background-repeat:no-repeat;background-position:top center;background-size:100% auto;z-index:0;"></div>
        @endif
        <div class="relative z-10 px-6 pb-6 pt-32 sm:px-8 sm:pb-8 sm:pt-36">
        <div class="text-center mb-3 text-sm leading-4">
            <p>{{ $notaToko->nomor }}</p>
            <p>{{ \Illuminate\Support\Carbon::parse($notaToko->tanggal)->translatedFormat('d F Y') }}</p>
        </div>

        <div class="mb-3 text-sm leading-4">
            <div class="grid grid-cols-[5.5rem_0.5rem_1fr] gap-x-2">
                <div class="font-semibold">Customer</div>
                <div>:</div>
                <div>{{ $notaToko->customer_nama }}</div>
                <div class="font-semibold">Alamat</div>
                <div>:</div>
                <div>{{ $notaToko->alamat }}</div>
                <div class="font-semibold">Payment</div>
                <div>:</div>
                <div>2950701709 (BCA) - Aldera Saddatech Karya</div>
            </div>
        </div>

        <div class="overflow-x-auto mb-3">
            <table class="w-full border-collapse table-fixed text-sm">
                <thead>
                    <tr>
                        <th class="border px-3 py-2" style="width:6%;">No</th>
                        <th class="border px-3 py-2" style="width:36%;">Item</th>
                        <th class="border px-3 py-2" style="width:8%;">Qty</th>
                        <th class="border px-3 py-2" style="width:10%;">Satuan</th>
                        <th class="border px-3 py-2" style="width:20%;">Unit Price</th>
                        <th class="border px-3 py-2" style="width:20%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($notaToko->items as $item)
                        <tr>
                            <td class="border px-3 py-2 text-center">{{ $loop->iteration }}</td>
                            <td class="border px-3 py-2 break-words">{{ $item->nama }}</td>
                            <td class="border px-3 py-2 text-center">{{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}</td>
                            <td class="border px-3 py-2 text-center">{{ $item->satuan }}</td>
                            <td class="border px-3 py-2 text-right">Rp {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td class="border px-3 py-2 text-right">Rp {{ number_format($item->amount, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-2">
            <table class="w-full table-fixed border-collapse text-xs leading-4">
                <colgroup>
                    <col style="width:6%;">
                    <col style="width:36%;">
                    <col style="width:8%;">
                    <col style="width:10%;">
                    <col style="width:20%;">
                    <col style="width:20%;">
                </colgroup>
                <tbody>
                    <tr>
                        <td colspan="2" rowspan="2" class="align-top pr-3">
                            <div class="font-semibold">Terbilang:</div>
                            <div class="italic mt-1 break-words">{{ $terbilangTotal }}</div>
                            <div class="mt-3">
                                <p><strong>Keterangan:</strong></p>
                                <p class="whitespace-pre-line break-words">{{ $notaToko->keterangan ?: '-' }}</p>
                            </div>
                        </td>
                        <td></td>
                        <td></td>
                        <td class="px-2 py-0 text-sm border-b border-gray-200">Subtotal</td>
                        <td class="px-2 py-0 text-sm text-right tabular-nums border-b border-gray-200">Rp {{ number_format($notaToko->subtotal, 2, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td class="px-2 py-0 text-sm font-semibold">Total</td>
                        <td class="px-2 py-0 text-sm font-semibold text-right tabular-nums">Rp {{ number_format($notaToko->total, 2, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        </div>
        @if ($qrAsset)
            <div class="absolute bottom-6 right-9 sm:right-11 z-20 flex flex-col items-center gap-1 text-[10px] text-gray-600">
                <div class="font-semibold">Hormat Kami</div>
                <img src="{{ $qrAsset }}" alt="QR Sign" class="h-16 w-16">
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
