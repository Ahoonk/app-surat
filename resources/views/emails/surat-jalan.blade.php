<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Jalan</title>
</head>
<body>
    <p>Yth. {{ $suratJalan->invoice->penawaran->to_company ?? $suratJalan->invoice->penawaran->customer_nama }},</p>
    <p>Berikut kami lampirkan Surat Jalan dengan nomor <strong>{{ $suratJalan->nomor }}</strong>.</p>
    <p>Silakan periksa lampiran PDF pada email ini.</p>
    <p>Terima kasih.</p>
</body>
</html>
