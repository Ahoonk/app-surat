<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $invoice->nomor }}</title>
    <style>
        @page { size: A4 portrait; margin: 4mm 16mm 4mm 4mm; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; margin: 0; }
        .paper { width: 100%; position: relative; z-index: 2; padding: 44mm 18mm 6mm 10mm; box-sizing: border-box; }
        .bg-layer { position: fixed; inset: -4mm 0 0 0; background-size: 100% auto; background-repeat: no-repeat; background-position: top 4mm center; transform: translateX(7mm); z-index: 0; opacity: 1; }
        .head { display: table; width: 100%; border-bottom: 1px solid #000; padding-bottom: 12px; }
        .block-90 { width: 90%; margin-left: auto; margin-right: auto; }
        .left, .right { display: table-cell; vertical-align: top; }
        .right { text-align: right; }
        .inv-meta { font-size: 11px; line-height: 1.35; }
        .content-wrap { position: relative; left: -4mm; width: calc(100% + 4mm); box-sizing: border-box; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; table-layout: fixed; }
        .main-table { width: 90%; margin-left: auto; margin-right: auto; }
        th, td { border: 1px solid #000; padding: 6px 8px; }
        th { text-align: center; }
        .center { text-align: center; }
        .right-text { text-align: right; }
        .nowrap { white-space: nowrap; }
        .summary-wrap { width: 90%; margin: 12px auto 0; }
        .summary { width: 40%; margin-left: auto; margin-top: 0; }
        .summary td { border-left: 0; border-right: 0; }
        .summary tr:last-child td { font-weight: 700; font-size: 11px; }
        .notes { margin-top: 28px; }
        .po-meta { width: 90%; margin: 10px auto 6px; }
        .signoff { width: 260px; margin: 14px 0 0 auto; text-align: center; }
        .footer-layer { position: fixed; left: 0; right: 0; bottom: -5mm; height: 34mm; background-size: 100% 100%; background-repeat: no-repeat; background-position: bottom center; transform: translateX(7mm); z-index: 1; }
    </style>
</head>
<body>
@php
    $toDataUri = static function (string $path): ?string {
        if (!file_exists($path)) {
            return null;
        }

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    };

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
    $mitraTemplateInvoice = $mitraTemplatePath ? $toDataUri($mitraTemplatePath) : null;

    $templatePath = public_path('storage/logos/template-invoice.png');
    $footerPath = public_path('storage/logos/kopbawah-invoice.png');
    $templateInvoice = $toDataUri($templatePath);
    $footerInvoice = $toDataUri($footerPath);
@endphp

@if ($mitraTemplateInvoice)
    <div class="bg-layer" style="background-image: url('{{ $mitraTemplateInvoice }}'); background-size: 100% 100%;"></div>
@else
    @if ($templateInvoice)
        <div class="bg-layer" style="background-image: url('{{ $templateInvoice }}');"></div>
    @endif
    @if ($footerInvoice)
        <div class="footer-layer" style="background-image: url('{{ $footerInvoice }}');"></div>
    @endif
@endif

<div class="paper">
    <div class="content-wrap">
    <div class="head block-90">
        <div class="left">
            <div style="font-size:11px; color:#555; font-weight:700;">Bill To</div>
            <div><strong>{{ $penawaran->to_company ?? $penawaran->customer_nama }}</strong></div>
            <div>{{ $penawaran->to_address ?? '-' }}</div>
        </div>
        <div class="right">
            <div class="inv-meta">
                <div style="color:#555; font-weight:700;">No Invoice</div>
                <div style="margin-top:4px;"><strong>{{ $invoice->nomor }}</strong></div>
                <div><strong>Date:</strong> {{ \Illuminate\Support\Carbon::parse($invoice->tanggal)->translatedFormat('d F Y') }}</div>
            </div>
        </div>
    </div>

    @unless($isMitra)
        <div class="po-meta">
            <div><strong>Nomor PO:</strong> {{ $invoice->purchasingOrder->nomor_po ?? '-' }}</div>
            <div><strong>Tanggal PO:</strong> {{ $invoice->purchasingOrder->tanggal_po ? \Illuminate\Support\Carbon::parse($invoice->purchasingOrder->tanggal_po)->translatedFormat('d F Y') : '-' }}</div>
        </div>
    @endunless

    <table class="main-table">
        <thead>
        <tr>
            <th style="width:6%;">No</th>
            <th style="width:40%;">Description</th>
            <th style="width:9%;">Qty</th>
            <th style="width:9%;">Unit</th>
            <th style="width:21%;">Unit Price</th>
            <th style="width:21%;">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($penawaran->items as $item)
            <tr>
                <td class="center">{{ $loop->iteration }}</td>
                <td style="text-align:left;">
                    <div>{{ $item->nama }}</div>
                    @if (!empty($item->rincian))
                        <div style="font-size:11px; margin-top:4px; white-space: pre-line;">{{ $item->rincian }}</div>
                    @endif
                </td>
                <td class="center">{{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}</td>
                <td class="center">{{ strtoupper($item->satuan) }}</td>
                <td class="right-text nowrap" style="font-size:11px;">Rp {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                <td class="right-text nowrap" style="font-size:11px;">Rp {{ number_format($item->amount, 2, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="summary-wrap">
        <table class="summary">
            <tr>
                <td>Subtotal</td>
                <td class="right-text">Rp {{ number_format($penawaran->subtotal, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Tax ({{ number_format($penawaran->tax_percent, 2, ',', '.') }}%)</td>
                <td class="right-text">Rp {{ number_format($penawaran->tax_amount, 2, ',', '.') }}</td>
            </tr>
            @if ($isMitra)
                <tr>
                    <td>PPh23 (2%)</td>
                    <td class="right-text">Rp {{ number_format($pph23, 2, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td>{{ $isMitra ? 'Amount (Net)' : 'Grand Total' }}</td>
                <td class="right-text">Rp {{ number_format($isMitra ? ($penawaran->total - $pph23) : $penawaran->total, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="notes block-90">
        <strong>Payment To :</strong><br>
        @if ($isMitra)
            <span>Bank : Mandiri</span><br>
            <span>No : 1630010438169</span><br>
            <span>a.n : {{ $issuerName }}</span>
        @else
            <span>2950701709 (BCA)</span><br>
            <span>a.n Aldera Saddatech Karya</span>
        @endif
        <div class="signoff">
            <div>Hormat kami,</div>
            <div style="margin-top:4px;"><strong>{{ $issuerName }}</strong></div>
            <div style="height:96px;"></div>
            <div><strong><u>{{ $penawaran->user->name ?? auth()->user()->name }}</u></strong></div>
            <div>{{ $penawaran->signature_role ?? 'Authorized Signature' }}</div>
        </div>
    </div>
    </div>
</div>
</body>
</html>
