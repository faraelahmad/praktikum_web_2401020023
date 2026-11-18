
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Buku</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f4f4f4;
        }

        h1 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }

        th {
            background: #333;
            color: white;
        }

        a, button {
            padding: 7px 12px;
            text-decoration: none;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .tambah {
            background: #198754;
            color: white;
            display: inline-block;
            margin-bottom: 15px;
        }

        .edit {
            background: #ffc107;
            color: black;
        }

        .hapus {
            background: #dc3545;
            color: white;
        }

        .pesan {
            padding: 12px;
            background: #d1e7dd;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <h1>Daftar Buku</h1>

<p>Masuk sebagai {{ auth()->user()->name }}</p>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button type="submit">Keluar</button>
</form>

    @if (session('success'))
        <div class="pesan">
            {{ session('success') }}
        </div>
    @endif

    <a href="{{ route('buku.create') }}" class="tambah">
        + Tambah Buku
    </a>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Buku</th>
                <th>Judul Buku</th>
                <th>Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($bukus as $buku)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $buku->kode }}</td>
                    <td>{{ $buku->judul }}</td>
                    <td>{{ $buku->kategori->nama }}</td>
                    <td>
                        <a href="{{ route('buku.edit', $buku->id) }}"
                           class="edit">Edit</a>

                        <form action="{{ route('buku.destroy', $buku->id) }}"
                              method="POST"
                              style="display:inline;">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="hapus"
                                onclick="return confirm('Yakin ingin menghapus buku ini?')">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data buku.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
