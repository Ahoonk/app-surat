@extends('layouts.app')

@section('content')
<div>
    <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3 mb-6">
        <h1 class="text-xl sm:text-2xl font-semibold text-gray-800">Preview Surat Penawaran</h1>
        <div class="flex gap-2">
            <a href="{{ route('penawaran.pdf', ['penawaran' => $penawaran, 'download' => 1]) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">
                Export PDF
            </a>
            <a href="{{ route('penawaran.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Kembali</a>
        </div>
    </div>

    @php
        $mitra = $penawaran->mitra;
        $mitraTemplatePath = $mitra?->template_penawaran_path
            ? public_path('storage/' . $mitra->template_penawaran_path)
            : null;
        $mitraTemplateAsset = $mitraTemplatePath && file_exists($mitraTemplatePath)
            ? asset('storage/' . $mitra->template_penawaran_path) . '?v=' . filemtime($mitraTemplatePath)
            : null;
        $bgPrimary = public_path('storage/logos/background-template.png');
        $bgFallback = public_path('storage/logos/background-tempplate.png');
        $bgAsset = file_exists($bgPrimary)
            ? asset('storage/logos/background-template.png')
            : (file_exists($bgFallback) ? asset('storage/logos/background-tempplate.png') : null);
        $kopAtasAsset = file_exists(public_path('storage/logos/kopatas-penawaran.png')) ? asset('storage/logos/kopatas-penawaran.png') : null;
        $kopBawahAsset = file_exists(public_path('storage/logos/kopbawah-penawaran.png')) ? asset('storage/logos/kopbawah-penawaran.png') : null;
    @endphp

    <div class="bg-white rounded-2xl shadow-xl px-4 sm:px-6 lg:px-10 pb-6 sm:pb-10 pt-0 max-w-5xl text-[12px] sm:text-[13px] leading-6 bg-no-repeat bg-center"
         @if($mitraTemplateAsset)
             style="background-image: url('{{ $mitraTemplateAsset }}'); background-size: 100% 100%; background-position: top center;"
         @elseif($bgAsset)
             style="background-image: url('{{ $bgAsset }}'); background-size: 50% auto;"
         @endif>
        @if (!$mitraTemplateAsset && $kopAtasAsset)
            <div class="mb-4 -mx-10">
                <img src="{{ $kopAtasAsset }}" alt="Kop Atas" class="w-full h-auto block">
            </div>
        @endif

        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold uppercase tracking-wide">Surat Penawaran</h2>
            <p class="mt-1">No: {{ $penawaran->nomor }}</p>
        </div>

        <div class="mb-6 flex items-start justify-between gap-6">
            <div class="space-y-1">
                <p><strong>To:</strong> <strong>{{ $penawaran->to_company ?? $penawaran->customer_nama }}</strong></p>
                <p><strong>At:</strong> {{ $penawaran->to_address ?? '-' }}</p>
            </div>
            <div class="text-right">
                <p><strong>Tanggal:</strong> {{ \Illuminate\Support\Carbon::parse($penawaran->tanggal)->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        <div class="mb-8 overflow-x-auto">
            <table class="w-full border-collapse">
                <thead>
                    <tr>
                        <th class="border px-3 py-2 text-center font-semibold">No</th>
                        <th class="border px-3 py-2 text-center font-semibold">Item</th>
                        <th class="border px-3 py-2 text-center font-semibold">Qty</th>
                        <th class="border px-3 py-2 text-center font-semibold">Satuan</th>
                        <th class="border px-3 py-2 text-center font-semibold">Unit Price</th>
                        <th class="border px-3 py-2 text-center font-semibold">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penawaran->items as $item)
                        <tr>
                            <td class="border px-3 py-2 text-center">{{ $loop->iteration }}</td>
                            <td class="border px-3 py-2 text-left">
                                <div>{{ $item->nama }}</div>
                                @if (!empty($item->rincian))
                                    <div class="text-xs text-gray-600 whitespace-pre-line mt-1">{!! e($item->rincian) !!}</div>
                                @endif
                            </td>
                            <td class="border px-3 py-2 text-center">{{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}</td>
                            <td class="border px-3 py-2 text-center">{{ $item->satuan }}</td>
                            <td class="border px-3 py-2 text-center">Rp {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td class="border px-3 py-2 text-center">Rp {{ number_format($item->amount, 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php
            $taxPercent = (float) ($penawaran->tax_percent ?? 0);
            $divisor = 1 + ($taxPercent / 100);
            $pph23 = $penawaran->mitra_id
                ? ($divisor > 0 ? ($penawaran->total / $divisor) * 0.02 : 0)
                : 0;
            $netAmount = $penawaran->total - $pph23;
        @endphp

        <div class="ml-auto w-full max-w-sm">
            <div class="flex justify-between border-b py-2">
                <span>Subtotal</span>
                <span>Rp {{ number_format($penawaran->subtotal, 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between border-b py-2">
                <span>Pajak ({{ number_format($penawaran->tax_percent, 2, ',', '.') }}%)</span>
                <span>Rp {{ number_format($penawaran->tax_amount, 2, ',', '.') }}</span>
            </div>
            @if ($penawaran->mitra_id)
                <div class="flex justify-between border-b py-2">
                    <span>PPh23 (2%)</span>
                    <span>Rp {{ number_format($pph23, 2, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between py-2 font-semibold">
                <span>{{ $penawaran->mitra_id ? 'Amount (Net)' : 'Total' }}</span>
                <span>Rp {{ number_format($penawaran->mitra_id ? $netAmount : $penawaran->total, 2, ',', '.') }}</span>
            </div>
        </div>

        <div class="mt-8">
            <p><strong>Keterangan:</strong></p>
            <p class="whitespace-pre-line">{!! e($penawaran->keterangan ?: '-') !!}</p>
        </div>

        @php
            $issuerName = $penawaran->mitra?->nama ?? 'PT Aldera Saddatech Karya';
        @endphp

        <div class="mt-12 flex justify-end">
            <div class="w-72 text-center">
                <p>Hormat kami,</p>
                <p class="font-semibold">{{ $issuerName }}</p>

                <div class="h-24"></div>

                <p class="font-semibold underline">{{ auth()->user()->name }}</p>
                <p>{{ $penawaran->signature_role ?? 'Authorized Signature' }}</p>
            </div>
        </div>

        @if (!$mitraTemplateAsset && $kopBawahAsset)
            <div class="mt-8 -mx-10 -mb-10">
                <img src="{{ $kopBawahAsset }}" alt="Kop Bawah" class="w-full h-auto block">
            </div>
        @endif
    </div>
</div>
@endsection
