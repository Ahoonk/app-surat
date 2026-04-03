<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Penawaran - {{ $penawaran->nomor }}</title>
    <style>
        @page { size: A4 portrait; margin: 0; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #000; margin: 0; }
        .paper { width: 100%; max-width: 180mm; margin: 0 auto; position: relative; z-index: 2; }
        .bg-layer { position: fixed; inset: 0; background-size: 50% auto; background-repeat: no-repeat; background-position: center; z-index: 0; opacity: .2; }
        .head-layer { position: fixed; top: 0; left: 0; right: 0; z-index: 1; }
        .foot-layer { position: fixed; bottom: 0; left: 0; right: 0; z-index: 1; }
        .kop-top { width: 100%; height: auto; display: block; }
        .kop-bottom { width: 100%; height: auto; display: block; }
        .content-space { padding-top: 170px; padding-bottom: 70px; }
        .center { text-align: center; }
        .between { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; }
        .text-right { text-align: right; }
        .mt-1 { margin-top: 4px; }
        .mt-2 { margin-top: 8px; }
        .mt-3 { margin-top: 12px; }
        .mt-4 { margin-top: 16px; }
        .mt-6 { margin-top: 24px; }
        .title { font-size: 22px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; }
        .main-table { width: 100%; margin: 0; table-layout: fixed; }
        th, td { border: 1px solid #000; padding: 6px 8px; }
        th { text-align: center; }
        .right { text-align: right; }
        .summary { width: 42%; margin-left: auto; }
        .summary td { border-left: 0; border-right: 0; }
        .summary tr:last-child td { font-weight: 700; }
        .ttd-wrap { width: 280px; margin-left: auto; text-align: center; }
    </style>
</head>
<body>
    @php
        $toDataUri = function ($path) {
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

            $data = base64_encode(file_get_contents($path));

            return 'data:' . $mime . ';base64,' . $data;
        };

        $mitra = $penawaran->mitra;
        $mitraTemplatePath = $mitra?->template_penawaran_path
            ? public_path('storage/' . $mitra->template_penawaran_path)
            : null;
        $mitraTemplateAsset = $mitraTemplatePath ? $toDataUri($mitraTemplatePath) : null;

        $bgPrimary = public_path('storage/logos/background-template.png');
        $bgFallback = public_path('storage/logos/background-tempplate.png');
        $bgPath = file_exists($bgPrimary) ? $bgPrimary : (file_exists($bgFallback) ? $bgFallback : null);
        $bgAsset = $bgPath ? $toDataUri($bgPath) : null;
        $kopAtasAsset = $toDataUri(public_path('storage/logos/kopatas-penawaran.png'));
        $kopBawahAsset = $toDataUri(public_path('storage/logos/kopbawah-penawaran.png'));
    @endphp

    @if ($mitraTemplateAsset)
        <div class="bg-layer" style="background-image: url('{{ $mitraTemplateAsset }}'); background-size: 100% 100%; background-position: top center; opacity: 1;"></div>
    @else
        @if ($bgAsset)
            <div class="bg-layer" style="background-image: url('{{ $bgAsset }}');"></div>
        @endif

        @if ($kopAtasAsset)
            <div class="head-layer">
                <img src="{{ $kopAtasAsset }}" alt="Kop Atas" class="kop-top">
            </div>
        @endif

        @if ($kopBawahAsset)
            <div class="foot-layer">
                <img src="{{ $kopBawahAsset }}" alt="Kop Bawah" class="kop-bottom">
            </div>
        @endif
    @endif

    <div class="paper content-space">
        <div class="center">
            <div class="title">Surat Penawaran</div>
            <div class="mt-1">No: {{ $penawaran->nomor }}</div>
        </div>

        <div class="mt-4 between">
            <div>
                <div class="mt-1"><strong>To:</strong> <strong>{{ $penawaran->to_company ?? $penawaran->customer_nama }}</strong></div>
                <div class="mt-1"><strong>At:</strong> {{ $penawaran->to_address ?? '-' }}</div>
            </div>
            <div class="text-right">
                <div><strong>Tanggal:</strong> {{ \Illuminate\Support\Carbon::parse($penawaran->tanggal)->translatedFormat('d F Y') }}</div>
            </div>
        </div>

        <div class="mt-4">
            <table class="main-table">
                <thead>
                    <tr>
                        <th style="width:6%;">No</th>
                        <th style="width:34%;">Item</th>
                        <th style="width:10%;">Qty</th>
                        <th style="width:12%;">Satuan</th>
                        <th style="width:19%;">Unit Price</th>
                        <th style="width:19%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($penawaran->items as $item)
                        <tr>
                            <td style="text-align:center;">{{ $loop->iteration }}</td>
                            <td style="text-align:left;">
                                <div>{{ $item->nama }}</div>
                                @if (!empty($item->rincian))
                                    <div style="font-size:10px; margin-top:4px; white-space: pre-line;">{{ $item->rincian }}</div>
                                @endif
                            </td>
                            <td style="text-align:center;">{{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}</td>
                            <td style="text-align:center;">{{ $item->satuan }}</td>
                            <td style="text-align:center;">Rp {{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            <td style="text-align:center;">Rp {{ number_format($item->amount, 2, ',', '.') }}</td>
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

        <table class="summary mt-3">
            <tr>
                <td>Subtotal</td>
                <td class="right">Rp {{ number_format($penawaran->subtotal, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Pajak ({{ number_format($penawaran->tax_percent, 2, ',', '.') }}%)</td>
                <td class="right">Rp {{ number_format($penawaran->tax_amount, 2, ',', '.') }}</td>
            </tr>
            @if ($penawaran->mitra_id)
                <tr>
                    <td>PPh23 (2%)</td>
                    <td class="right">Rp {{ number_format($pph23, 2, ',', '.') }}</td>
                </tr>
            @endif
            <tr>
                <td>{{ $penawaran->mitra_id ? 'Amount (Net)' : 'Total' }}</td>
                <td class="right">Rp {{ number_format($penawaran->mitra_id ? $netAmount : $penawaran->total, 2, ',', '.') }}</td>
            </tr>
        </table>

        <div class="mt-4">
            <strong>Keterangan:</strong>
            <div class="mt-1" style="white-space: pre-line;">{{ $penawaran->keterangan ?: '-' }}</div>
        </div>

        @php
            $issuerName = $penawaran->mitra?->nama ?? 'PT Aldera Saddatech Karya';
        @endphp

        <div class="ttd-wrap mt-6">
            <div>Hormat kami,</div>
            <div class="mt-1"><strong>{{ $issuerName }}</strong></div>
            <div style="height:70px;"></div>
            <div style="margin-top:2px;"><strong><u>{{ auth()->user()->name }}</u></strong></div>
            <div>{{ $penawaran->signature_role ?? 'Authorized Signature' }}</div>
        </div>
    </div>
</body>
</html>
