<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Toko - {{ $notaToko->nomor }}</title>
    <style>
        @page { size: 210mm 150mm; margin: 0; }
        body { font-family: Arial, sans-serif; font-size: 14px; margin: 0; padding: 35mm 8.5mm 8.5mm 8.5mm; box-sizing: border-box; line-height: 1.2; }
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        th, td { border: 1px solid #000; padding: 3px 6px; }
        th { text-align: center; }
        .content { position: relative; z-index: 1; }
        .no-break { page-break-inside: avoid; break-inside: avoid; }
    </style>
</head>
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
        $templatePath = public_path('storage/logos/template-nota.png');
        $templateNota = null;
        if (file_exists($templatePath)) {
            $data = base64_encode(file_get_contents($templatePath));
            $templateNota = 'data:image/png;base64,' . $data;
        }
        $qrPath = public_path('storage/logos/qr-bayu-suderajat.png');
        $qrData = null;
        if (file_exists($qrPath)) {
            $qrData = 'data:image/png;base64,' . base64_encode(file_get_contents($qrPath));
        }
@endphp
<body @if ($templateNota) style="background-image: url('{{ $templateNota }}'); background-size: 100% auto; background-repeat: no-repeat; background-position: top center;" @endif>
    <div class="content no-break">
    <div class="no-break" style="text-align:center; margin-bottom: 10px;">
        <div>{{ $notaToko->nomor }}</div>
        <div>{{ \Illuminate\Support\Carbon::parse($notaToko->tanggal)->translatedFormat('d F Y') }}</div>
    </div>

    <div class="no-break" style="margin-bottom: 10px;">
        <table style="border:0; border-collapse:collapse; width:100%; line-height:1.05;">
            <tr>
                <td style="border:0; width:60px; font-weight:700; padding:0; vertical-align:top;">Customer</td>
                <td style="border:0; width:8px; padding:0; vertical-align:top;">:</td>
                <td style="border:0; padding:0; vertical-align:top;">{{ $notaToko->customer_nama }}</td>
            </tr>
            <tr>
                <td style="border:0; font-weight:700; padding:0; vertical-align:top;">Alamat</td>
                <td style="border:0; padding:0; vertical-align:top;">:</td>
                <td style="border:0; padding:0; vertical-align:top;">{{ $notaToko->alamat }}</td>
            </tr>
            <tr>
                <td style="border:0; font-weight:700; padding:0; vertical-align:top;">Payment</td>
                <td style="border:0; padding:0; vertical-align:top;">:</td>
                <td style="border:0; padding:0; vertical-align:top;">2950701709 (BCA) - Aldera Saddatech Karya</td>
            </tr>
        </table>
    </div>

    <table class="no-break" style="margin-bottom: 10px;">
        <thead>
            <tr>
                <th style="width:6%;">No</th>
                <th style="width:36%;">Item</th>
                <th style="width:8%;">Qty</th>
                <th style="width:10%;">Satuan</th>
                <th style="width:20%;">Unit Price</th>
                <th style="width:20%;">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($notaToko->items as $item)
                <tr>
                    <td style="text-align:center;">{{ $loop->iteration }}</td>
                    <td>{{ $item->nama }}</td>
                    <td style="text-align:center;">{{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}</td>
                    <td style="text-align:center;">{{ $item->satuan }}</td>
                    <td>
                        <table style="width:100%; border:0; border-collapse:collapse;">
                            <tr>
                                <td style="border:0; width:12mm;">Rp</td>
                                <td style="border:0; text-align:right;">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                    <td>
                        <table style="width:100%; border:0; border-collapse:collapse;">
                            <tr>
                                <td style="border:0; width:12mm;">Rp</td>
                                <td style="border:0; text-align:right;">{{ number_format($item->amount, 2, ',', '.') }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="no-break" style="margin-top: 0; width: 100%; border:0; table-layout: fixed;">
        <colgroup>
            <col style="width:6%;">
            <col style="width:36%;">
            <col style="width:8%;">
            <col style="width:10%;">
            <col style="width:20%;">
            <col style="width:20%;">
        </colgroup>
        <tr>
            <td colspan="2" rowspan="2" style="border:0; vertical-align: top; word-break: break-word;">
                <div style="font-size: 11px;"><strong>Terbilang:</strong></div>
                <div style="font-style: italic; margin-top: 2px; font-size: 11px;">{{ $terbilangTotal }}</div>
                <div style="margin-top: 8px; font-size: 11px; line-height: 1.2;">
                    <strong>Keterangan:</strong><br>
                    {!! nl2br(e($notaToko->keterangan ?: '-')) !!}
                </div>
            </td>
            <td style="border:0;"></td>
            <td style="border:0;"></td>
            <td style="border:0; padding:3px 6px; border-bottom:1px solid #e5e7eb;">Subtotal</td>
            <td style="border:0; padding:3px 6px; border-bottom:1px solid #e5e7eb;">
                <table style="width:100%; border:0; border-collapse:collapse; table-layout: fixed;">
                    <colgroup>
                        <col style="width:12mm;">
                        <col>
                    </colgroup>
                    <tr>
                        <td style="border:0;">Rp</td>
                        <td style="border:0; text-align:right;">{{ number_format($notaToko->subtotal, 2, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td style="border:0;"></td>
            <td style="border:0;"></td>
            <td style="border:0; padding:3px 6px; font-weight:700;">Total</td>
            <td style="border:0; padding:3px 6px;">
                <table style="width:100%; border:0; border-collapse:collapse; table-layout: fixed;">
                    <colgroup>
                        <col style="width:12mm;">
                        <col>
                    </colgroup>
                    <tr>
                        <td style="border:0; font-weight:700;">Rp</td>
                        <td style="border:0; text-align:right; font-weight:700;">{{ number_format($notaToko->total, 2, ',', '.') }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    </div>
    @if ($qrData)
        <div style="position: fixed; right: 10mm; bottom: 8mm; text-align: center; font-size: 9px; color: #555;">
            <div style="font-weight: 600; margin-bottom: 2px;">Hormat Kami</div>
            <img src="{{ $qrData }}" alt="QR Sign" style="width: 20mm; height: 20mm;">
        </div>
    @endif
</body>
</html>
