<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara</title>
</head>
<body>
    <p>Yth. {{ $beritaAcara->invoice->penawaran->to_company ?? $beritaAcara->invoice->penawaran->customer_nama }},</p>
    <p>Berikut kami lampirkan Berita Acara dengan nomor <strong>{{ $beritaAcara->nomor }}</strong>.</p>
    <p>Silakan periksa lampiran PDF pada email ini.</p>
    <p>Terima kasih.</p>
</body>
</html>
