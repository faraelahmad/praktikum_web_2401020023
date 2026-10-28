
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Mahasiswa</title>
</head>
<body>
    <h1>Edit Data Mahasiswa</h1>

    @if (session('gagal'))
        <p>{{ session('gagal') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ url('/mahasiswa/' . $mahasiswa['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <p>NIM<br>
            <input type="text" name="nim"
                value="{{ old('nim', $mahasiswa['nim']) }}" required>
        </p>

        <p>Nama<br>
            <input type="text" name="nama"
                value="{{ old('nama', $mahasiswa['nama']) }}" required>
        </p>

        <p>Email<br>
            <input type="email" name="email"
                value="{{ old('email', $mahasiswa['email']) }}" required>
        </p>

        <p>Usia<br>
            <input type="number" name="usia"
                value="{{ old('usia', $mahasiswa['usia']) }}"
                min="15" max="100" required>
        </p>

        <p>Program Studi<br>
            <select name="program_studi_id" required>
                @foreach ($daftarProgramStudi as $prodi)
                    <option value="{{ $prodi['id'] }}"
                        @selected(old('program_studi_id', $mahasiswa['program_studi_id']) == $prodi['id'])>
                        {{ $prodi['nama_prodi'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Simpan Perubahan</button>
        <a href="{{ route('mahasiswa.index') }}">Kembali</a>
    </form>
</body>
</html>
