<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice</title>
</head>
<body>
    <p>Yth. {{ $invoice->penawaran->to_company ?? $invoice->penawaran->customer_nama }},</p>
    <p>Berikut kami lampirkan Invoice dengan nomor <strong>{{ $invoice->nomor }}</strong>.</p>
    <p>Silakan periksa lampiran PDF pada email ini.</p>
    <p>Terima kasih.</p>
</body>
</html>
