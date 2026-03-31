<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Penawaran</title>
</head>
<body>
    <p>Yth. {{ $penawaran->to_company ?? $penawaran->customer_nama }},</p>
    <p>Berikut kami lampirkan Surat Penawaran dengan nomor <strong>{{ $penawaran->nomor }}</strong>.</p>
    <p>Silakan periksa lampiran PDF pada email ini.</p>
    <p>Terima kasih.</p>
</body>
</html>
