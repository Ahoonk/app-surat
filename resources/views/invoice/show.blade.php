@extends('layouts.app')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 mb-6">
        <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">Preview Invoice</h1>
        <div class="flex gap-2">
            <a href="{{ route('invoice.pdf', ['invoice' => $invoice, 'download' => 1]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                Export PDF
            </a>
            <a href="{{ route('invoice.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Kembali</a>
        </div>
    </div>

    @php
        $mitra = $penawaran->mitra;
        $isMitra = (bool) $mitra;
        $issuerName = $mitra?->nama ?? 'PT Aldera Saddatech Karya';
        $taxPercent = (float) ($penawaran->tax_percent ?? 0);
        $divisor = 1 + ($taxPercent / 100);
        $pph23 = $isMitra && $divisor > 0
            ? ($penawaran->total / $divisor) * 0.02
            : 0;
        $mitraTemplatePath = $mitra?->template_invoice_path
            ? public_path('storage/' . $mitra->template_invoice_path)
            : null;
        $mitraTemplate = $mitraTemplatePath && file_exists($mitraTemplatePath)
            ? asset('storage/' . $mitra->template_invoice_path) . '?v=' . filemtime($mitraTemplatePath)
            : null;
        $invoiceTemplatePath = public_path('storage/logos/template-invoice.png');
        $invoiceFooterPath = public_path('storage/logos/kopbawah-invoice.png');
        $invoiceTemplate = file_exists($invoiceTemplatePath)
            ? asset('storage/logos/template-invoice.png') . '?v=' . filemtime($invoiceTemplatePath)
            : null;
        $invoiceFooter = file_exists($invoiceFooterPath)
            ? asset('storage/logos/kopbawah-invoice.png') . '?v=' . filemtime($invoiceFooterPath)
            : null;
        $previewStyle = 'width: 100%; max-width: 794px; min-height: 1123px; padding: 50mm 18mm 6mm 10mm; background-size: 100% auto; background-repeat: no-repeat; background-position: top 4mm center;';
        if ($mitraTemplate) {
            $previewStyle .= " background-image: url('{$mitraTemplate}'); background-size: 100% 100%; transform: translateX(6mm);";
        } elseif ($invoiceTemplate) {
            $previewStyle .= " background-image: url('{$invoiceTemplate}'); transform: translateX(6mm);";
        }
    @endphp

    <div class="bg-white rounded-2xl shadow-xl mx-auto text-[11px] leading-6 bg-no-repeat relative overflow-hidden"
         style="{{ $previewStyle }}">
        <div style="position: relative; left: 0; width: calc(100% + 10mm); box-sizing: border-box;">
        <div class="flex justify-between items-start border-b pb-4">
            <div>
                <p class="text-[11px] text-gray-600 font-semibold">Bill To</p>
                <p class="font-semibold">{{ $penawaran->to_company ?? $penawaran->customer_nama }}</p>
                <p>{{ $penawaran->to_address ?? '-' }}</p>
            </div>
            <div class="text-right">
                <p class="text-[11px] text-gray-600 font-semibold">No Invoice</p>
                <p class="mt-1"><strong>{{ $invoice->nomor }}</strong></p>
                <p><strong>Date:</strong> {{ \Illuminate\Support\Carbon::parse($invoice->tanggal)->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        @unless($isMitra)
            <div class="mt-3 mb-2 text-[11px]">
                <p><strong>Nomor PO:</strong> {{ $invoice->purchasingOrder->nomor_po ?? '-' }}</p>
                <p><strong>Tanggal PO:</strong> {{ $invoice->purchasingOrder->tanggal_po ? \Illuminate\Support\Carbon::parse($invoice->purchasingOrder->tanggal_po)->translatedFormat('d F Y') : '-' }}</p>
            </div>
        @endunless

        <div class="mt-6 overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="border px-3 py-2 text-center">No</th>
                        <th class="border px-3 py-2 text-center">Description</th>
                        <th class="border px-3 py-2 text-center">Qty</th>
                        <th class="border px-3 py-2 text-center">Unit</th>
                        <th class="border px-3 py-2 text-center">Unit Price</th>
                        <th class="border px-3 py-2 text-center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penawaran->items as $item)
                        <tr>
                            <td class="border px-3 py-2 text-center">{{ $loop->iteration }}</td>
                            <td class="border px-3 py-2 text-left">
                                <div>{{ $item->nama }}</div>
                                @if (!empty($item->rincian))
                                    <div class="text-[11px] text-gray-600 whitespace-pre-line mt-1">{!! e($item->rincian) !!}</div>
                                @endif
                            </td>
                            <td class="border px-3 py-2 text-center">{{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}</td>
                            <td class="border px-3 py-2 text-center">{{ strtoupper($item->satuan) }}</td>
                            <td class="border px-3 py-2 text-right">Rp {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td class="border px-3 py-2 text-right">Rp {{ number_format($item->amount, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="ml-auto mt-4 w-full max-w-sm">
            <div class="flex justify-between border-b py-2">
                <span>Subtotal</span>
                <span>Rp {{ number_format($penawaran->subtotal, 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between border-b py-2">
                <span>Tax ({{ number_format($penawaran->tax_percent, 2, ',', '.') }}%)</span>
                <span>Rp {{ number_format($penawaran->tax_amount, 2, ',', '.') }}</span>
            </div>
            @if($isMitra)
                <div class="flex justify-between border-b py-2">
                    <span>PPh23 (2%)</span>
                    <span>Rp {{ number_format($pph23, 2, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between py-2 font-semibold text-base">
                <span>{{ $isMitra ? 'Amount (Net)' : 'Grand Total' }}</span>
                <span>Rp {{ number_format($isMitra ? ($penawaran->total - $pph23) : $penawaran->total, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="mt-10 text-[11px]">
            <p><strong>Payment To :</strong></p>
            @if($isMitra)
                <p>Bank : Mandiri</p>
                <p>No : 1630010438169</p>
                <p>a.n : {{ $issuerName }}</p>
            @else
                <p>2950701709 (BCA)</p>
                <p>a.n Aldera Saddatech Karya</p>
            @endif
            <div class="w-[260px] ml-auto mt-4 text-center">
                <p>Hormat kami,</p>
                <p class="font-semibold mt-1">{{ $issuerName }}</p>
                <div class="h-20"></div>
                <p class="font-semibold underline">{{ $penawaran->user->name ?? auth()->user()->name }}</p>
                <p>{{ $penawaran->signature_role ?? 'Authorized Signature' }}</p>
            </div>
        </div>
        @if(!$mitraTemplate && $invoiceFooter)
            <img src="{{ $invoiceFooter }}" alt="Footer Invoice" style="position:absolute; left:0; right:0; bottom:-140mm; width:100%; height:34mm; object-fit:fill; transform:translateX(-0.5mm);">
        @endif
        </div>
    </div>
</div>
@endsection
