<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Nota Toko</title>
</head>
<body>
    <p>Yth. {{ $notaToko->customer_nama }},</p>
    <p>Berikut kami lampirkan Nota Toko dengan nomor <strong>{{ $notaToko->nomor }}</strong>.</p>
    <p>Silakan periksa lampiran PDF pada email ini.</p>
    <p>Terima kasih.</p>
</body>
</html>
