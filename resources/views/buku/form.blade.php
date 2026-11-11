
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($buku) ? 'Edit Buku' : 'Tambah Buku' }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            background: #f4f4f4;
        }

        .container {
            max-width: 500px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 8px;
        }

        h1 {
            color: #333;
        }

        label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
        }

        button, a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            text-decoration: none;
            cursor: pointer;
        }

        button {
            background: #198754;
            color: white;
        }

        a {
            background: #6c757d;
            color: white;
        }

        .error {
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="container">

    <h1>{{ isset($buku) ? 'Edit Buku' : 'Tambah Buku' }}</h1>

    <form
        action="{{ isset($buku) ? route('buku.update', $buku->id) : route('buku.store') }}"
        method="POST"
    >
        @csrf

        @if (isset($buku))
            @method('PUT')
        @endif

        <label for="kode">Kode Buku</label>
        <input
            type="text"
            id="kode"
            name="kode"
            value="{{ old('kode', $buku->kode ?? '') }}"
            maxlength="10"
            required
        >

        @error('kode')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="judul">Judul Buku</label>
        <input
            type="text"
            id="judul"
            name="judul"
            value="{{ old('judul', $buku->judul ?? '') }}"
            required
        >

        @error('judul')
            <div class="error">{{ $message }}</div>
        @enderror

        <label for="kategori_id">Kategori</label>
        <select id="kategori_id" name="kategori_id" required>
            <option value="">-- Pilih Kategori --</option>

            @foreach ($kategoris as $kategori)
                <option
                    value="{{ $kategori->id }}"
                    {{ old('kategori_id', $buku->kategori_id ?? '') == $kategori->id ? 'selected' : '' }}
                >
                    {{ $kategori->nama }}
                </option>
            @endforeach
        </select>

        @error('kategori_id')
            <div class="error">{{ $message }}</div>
        @enderror

        <button type="submit">
            {{ isset($buku) ? 'Update Buku' : 'Simpan Buku' }}
        </button>

        <a href="{{ route('buku.index') }}">Kembali</a>

    </form>

</div>

</body>
</html>
