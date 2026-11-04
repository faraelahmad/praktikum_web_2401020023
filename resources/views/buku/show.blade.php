
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Buku</title>
</head>
<body>
    <h1>Detail Buku</h1>

    <p>
        <strong>Kode Buku:</strong>
        {{ $buku['kode'] }}
    </p>

    <p>
        <strong>Judul Buku:</strong>
        {{ $buku['judul'] }}
    </p>

    <a href="{{ route('buku.index') }}">Kembali ke Daftar Buku</a>
</body>
</html>
