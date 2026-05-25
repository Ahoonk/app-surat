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
    $snapshot = $invoice->snapshot_data ?? [];
    $mitra = $penawaran->mitra;
    $isMitra = (bool) data_get($snapshot, 'is_mitra', $mitra);
    $issuerName = data_get($snapshot, 'issuer_name', $mitra?->nama ?? 'PT Aldera Saddatech Karya');
    $taxPercent = (float) data_get($snapshot, 'tax_percent', $penawaran->tax_percent ?? 0);
    $divisor = 1 + ($taxPercent / 100);
    $baseTotal = (float) data_get($snapshot, 'total', $penawaran->total);
    $pph23 = $isMitra && $divisor > 0
        ? ($baseTotal / $divisor) * 0.02
        : 0;
    $toDataUri = static function (string $path): ?string {
        if (!file_exists($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'pdf') {
            if (!class_exists(\Imagick::class)) {
                return null;
            }

            try {
                $imagick = new \Imagick();
                $imagick->setResolution(300, 300);
                $imagick->readImage($path . '[0]');
                $imagick->setImageFormat('png');

                return 'data:image/png;base64,' . base64_encode($imagick->getImageBlob());
            } catch (\Throwable $e) {
                report($e);
            }

            $gsBinary = trim((string) shell_exec('command -v gs 2>/dev/null'));
            if ($gsBinary !== '') {
                $tmpDir = storage_path('app/template-previews');
                if (!is_dir($tmpDir)) {
                    @mkdir($tmpDir, 0775, true);
                }

                $prefix = tempnam($tmpDir, 'pdf-');
                if ($prefix !== false) {
                    $pngPath = $prefix . '.png';
                    @unlink($prefix);

                    $cmd = escapeshellarg($gsBinary)
                        . ' -dSAFER -dBATCH -dNOPAUSE -sDEVICE=pngalpha -r300 -dFirstPage=1 -dLastPage=1 -sOutputFile='
                        . escapeshellarg($pngPath) . ' ' . escapeshellarg($path) . ' 2>&1';

                    $output = [];
                    $exitCode = 0;
                    @exec($cmd, $output, $exitCode);

                    if ($exitCode === 0 && file_exists($pngPath)) {
                        $binary = file_get_contents($pngPath);
                        @unlink($pngPath);

                        if ($binary !== false) {
                            return 'data:image/png;base64,' . base64_encode($binary);
                        }
                    }

                    @unlink($pngPath);
                }
            }

            return null;
        }

        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => null,
        };

        if (!$mime) {
            return null;
        }

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };
    $mitraTemplatePath = $mitra?->template_invoice_path
        ? public_path('storage/' . $mitra->template_invoice_path)
        : null;
    $mitraTemplate = $mitraTemplatePath ? $toDataUri($mitraTemplatePath) : null;
    $companyTemplatePath = app(\App\Services\DocumentTemplateResolver::class)->resolveTemplatePath($penawaran->company_id, 'invoice');
    $companyTemplate = $companyTemplatePath
        ? $toDataUri(public_path('storage/' . $companyTemplatePath))
        : null;
    $documentTemplate = $mitraTemplate ?: $companyTemplate;
    $invoiceTemplatePath = public_path('storage/logos/template-invoice.png');
    $invoiceFooterPath = public_path('storage/logos/kopbawah-invoice.png');
    $invoiceTemplate = file_exists($invoiceTemplatePath)
        ? asset('storage/logos/template-invoice.png') . '?v=' . filemtime($invoiceTemplatePath)
        : null;
    $invoiceFooter = file_exists($invoiceFooterPath)
        ? asset('storage/logos/kopbawah-invoice.png') . '?v=' . filemtime($invoiceFooterPath)
        : null;
    $previewStyle = 'width: 100%; max-width: 794px; min-height: 1123px; padding: 50mm 26mm 6mm 2mm; background-size: 100% auto; background-repeat: no-repeat; background-position: top center;';
    if ($documentTemplate) {
        $previewStyle .= " background-image: url('{$documentTemplate}'); background-size: 100% 100%; background-position: top center;";
    } elseif ($invoiceTemplate) {
        $previewStyle .= " background-image: url('{$invoiceTemplate}');";
    }
    @endphp

    <div class="bg-white rounded-2xl shadow-xl mx-auto text-[9px] leading-5 bg-no-repeat relative overflow-hidden"
         style="{{ $previewStyle }}">
        <div style="position: relative; width: 100%; box-sizing: border-box;">
        <div class="flex justify-between items-start border-b pb-4">
            <div>
                <p class="text-[9px] text-gray-600 font-semibold">Bill To</p>
                <p class="font-semibold">{{ data_get($snapshot, 'customer_name', $penawaran->to_company ?? $penawaran->customer_nama) }}</p>
                <p>{{ data_get($snapshot, 'customer_address', $penawaran->to_address ?? '-') }}</p>
            </div>
            <div class="text-right">
                <p class="text-[9px] text-gray-600 font-semibold">No Invoice</p>
                <p class="mt-1"><strong>{{ data_get($snapshot, 'invoice_number', $invoice->nomor) }}</strong></p>
                <p><strong>Date:</strong> {{ \Illuminate\Support\Carbon::parse(data_get($snapshot, 'invoice_date', $invoice->tanggal))->translatedFormat('d F Y') }}</p>
            </div>
        </div>

        @unless($isMitra)
            <div class="mt-3 mb-2 text-[9px]">
                <p><strong>Nomor PO:</strong> {{ data_get($snapshot, 'po_number', $invoice->purchasingOrder->nomor_po ?? '-') }}</p>
                <p><strong>Tanggal PO:</strong> {{ data_get($snapshot, 'po_date', $invoice->purchasingOrder->tanggal_po) ? \Illuminate\Support\Carbon::parse(data_get($snapshot, 'po_date', $invoice->purchasingOrder->tanggal_po))->translatedFormat('d F Y') : '-' }}</p>
            </div>
        @endunless

        <div class="mt-6 overflow-x-auto">
            <table class="border-collapse" style="width: 94%; margin: 0 auto;">
                <thead>
                    <tr>
                        <th class="border px-3 py-2 text-center" style="width:4%;">No</th>
                        <th class="border px-3 py-2 text-center" style="width:36%;">Description</th>
                        <th class="border px-3 py-2 text-center" style="width:6%;">Qty</th>
                        <th class="border px-3 py-2 text-center" style="width:8%;">Unit</th>
                        <th class="border px-3 py-2 text-center" style="width:20%;">Unit Price</th>
                        <th class="border px-3 py-2 text-center" style="width:16%;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (data_get($snapshot, 'items', $penawaran->items) as $item)
                        <tr>
                            <td class="border px-3 py-2 text-center" style="width:4%;">{{ $loop->iteration }}</td>
                            <td class="border px-3 py-2 text-left" style="width:36%;">
                                <div>{{ data_get($item, 'nama') }}</div>
                                @if (!empty(data_get($item, 'rincian')))
                                    <div class="text-[9px] text-gray-600 whitespace-pre-line mt-1">{!! e(data_get($item, 'rincian')) !!}</div>
                                @endif
                            </td>
                            <td class="border px-3 py-2 text-center" style="width:6%;">{{ rtrim(rtrim(number_format((float) data_get($item, 'qty', 0), 2, '.', ''), '0'), '.') }}</td>
                            <td class="border px-3 py-2 text-center" style="width:8%;">{{ strtoupper((string) data_get($item, 'satuan', '-')) }}</td>
                            <td class="border px-3 py-2 text-center" style="width:20%;">Rp {{ number_format((float) data_get($item, 'unit_price', 0), 2, ',', '.') }}</td>
                            <td class="border px-3 py-2 text-right" style="width:16%;">Rp {{ number_format((float) data_get($item, 'amount', 0), 2, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4" style="width: 94%; margin: 0 auto;">
            <div class="flex justify-between border-b py-2">
                <span>Subtotal</span>
                <span>Rp {{ number_format((float) data_get($snapshot, 'subtotal', $penawaran->subtotal), 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between border-b py-2">
                <span>Tax ({{ number_format($taxPercent, 2, ',', '.') }}%)</span>
                <span>Rp {{ number_format((float) data_get($snapshot, 'tax_amount', $penawaran->tax_amount), 2, ',', '.') }}</span>
            </div>
            @if($isMitra)
                <div class="flex justify-between border-b py-2">
                    <span>PPh23 (2%)</span>
                    <span>Rp {{ number_format($pph23, 2, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between py-2 font-semibold text-base">
                <span>{{ $isMitra ? 'Amount' : 'Grand Total' }}</span>
                <span>Rp {{ number_format($isMitra ? ($baseTotal - $pph23) : $baseTotal, 2, ',', '.') }}</span>
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
                <p class="font-semibold underline">{{ data_get($snapshot, 'creator_name', $penawaran->user->name ?? auth()->user()->name) }}</p>
                <p>{{ data_get($snapshot, 'signature_role', $penawaran->signature_role ?? 'Authorized Signature') }}</p>
            </div>
        </div>
        @if(!$documentTemplate && $invoiceFooter)
            <img src="{{ $invoiceFooter }}" alt="Footer Invoice" style="position:absolute; left:0; right:0; bottom:-140mm; width:100%; height:34mm; object-fit:fill; transform:translateX(-0.5mm);">
        @endif
        </div>
    </div>
</div>
@endsection
