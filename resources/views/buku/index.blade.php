
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Buku</title>
</head>
<body>
    <h1>Daftar Buku</h1>

    <form action="{{ route('buku.index') }}" method="GET">
        <input
            type="text"
            name="q"
            placeholder="Cari kode atau judul buku..."
            value="{{ $keyword }}"
        >
        <button type="submit">Cari</button>
        <a href="{{ route('buku.index') }}">Reset</a>
    </form>

    <br>

    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
            <tr>
                <th>Kode Buku</th>
                <th>Judul Buku</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($daftarBuku as $buku)
                <tr>
                    <td>{{ $buku['kode'] }}</td>
                    <td>{{ $buku['judul'] }}</td>
                    <td>
                        <a href="{{ route('buku.show', $buku['kode']) }}">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Buku tidak ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
