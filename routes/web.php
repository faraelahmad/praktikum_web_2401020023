
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;



Route::get('/mahasiswa', function (Request $request) {
    $filterData = $request->validate([
        'q' => ['nullable', 'string', 'max:100'],
        'program_studi_id' => [
            'nullable',
            'integer',
            'exists:program_studi,id',
        ],
    ]);

    $kataKunci = trim($filterData['q'] ?? '');
    $filterProdi = $filterData['program_studi_id'] ?? '';

    try {
        $pdo = DB::connection()->getPdo();

        $sql = 'SELECT m.id, m.nim, m.nama, m.email, m.usia,
                       m.program_studi_id, p.nama_prodi
                FROM mahasiswa AS m
                JOIN program_studi AS p
                    ON p.id = m.program_studi_id
                WHERE 1=1';

        $parameter = [];

        if ($kataKunci !== '') {
            $sql .= ' AND (
                m.nim LIKE :nim
                OR m.nama LIKE :nama
                OR m.email LIKE :email
            )';

            $cari = '%' . $kataKunci . '%';

            $parameter['nim'] = $cari;
            $parameter['nama'] = $cari;
            $parameter['email'] = $cari;
        }

        if ($filterProdi !== '') {
            $sql .= ' AND m.program_studi_id = :program_studi_id';
            $parameter['program_studi_id'] = $filterProdi;
        }

        $sql .= ' ORDER BY m.nim';

        $pernyataanMahasiswa = $pdo->prepare($sql);
        $pernyataanMahasiswa->execute($parameter);

        $daftarMahasiswa = $pernyataanMahasiswa
            ->fetchAll(\PDO::FETCH_ASSOC);

        $pernyataanProdi = $pdo->prepare(
            'SELECT id, nama_prodi
             FROM program_studi
             ORDER BY nama_prodi'
        );

        $pernyataanProdi->execute();

        $daftarProgramStudi = $pernyataanProdi
            ->fetchAll(\PDO::FETCH_ASSOC);

        return view('mahasiswa', compact(
            'daftarMahasiswa',
            'daftarProgramStudi',
            'kataKunci',
            'filterProdi'
        ));
    } catch (\Throwable $error) {
        report($error);

        return response(
            'Data mahasiswa tidak dapat ditampilkan. Periksa database.',
            500
        );
    }
})->name('mahasiswa.index');



Route::post('/mahasiswa', function (Request $request) {
    $data = $request->validate([
        'nim' => ['required', 'string', 'max:20'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => ['required', 'integer'],
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        $cek = $pdo->prepare(
            'SELECT COUNT(*) FROM mahasiswa
             WHERE nim = :nim OR email = :email'
        );
        $cek->execute([
            'nim' => $data['nim'],
            'email' => $data['email'],
        ]);

        if ((int) $cek->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'NIM atau email sudah digunakan.'
            );
        }

        $simpan = $pdo->prepare(
            'INSERT INTO mahasiswa
                (nim, nama, email, usia, program_studi_id)
             VALUES (:nim, :nama, :email, :usia, :program_studi_id)'
        );
        $simpan->execute($data);

        return redirect()->route('mahasiswa.index')->with(
            'berhasil',
            'Data mahasiswa berhasil ditambahkan.'
        );
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data mahasiswa gagal disimpan.'
        );
    }
})->name('mahasiswa.store');


Route::get('/mahasiswa/{id}/edit', function ($id) {
    try {
        $pdo = DB::connection()->getPdo();

        $cari = $pdo->prepare(
            'SELECT id, nim, nama, email, usia, program_studi_id
             FROM mahasiswa
             WHERE id = :id'
        );

        $cari->execute(['id' => $id]);
        $mahasiswa = $cari->fetch(\PDO::FETCH_ASSOC);

        if (!$mahasiswa) {
            abort(404, 'Data mahasiswa tidak ditemukan.');
        }

        $pernyataanProdi = $pdo->prepare(
            'SELECT id, nama_prodi
             FROM program_studi
             ORDER BY nama_prodi'
        );

        $pernyataanProdi->execute();

        $daftarProgramStudi = $pernyataanProdi
            ->fetchAll(\PDO::FETCH_ASSOC);

        return view('mahasiswa-edit', compact(
            'mahasiswa',
            'daftarProgramStudi'
        ));
    } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $error) {
        throw $error;
    } catch (\Throwable $error) {
        report($error);

        return response('Form edit tidak dapat ditampilkan.', 500);
    }
})->whereNumber('id')->name('mahasiswa.edit');


Route::put('/mahasiswa/{id}', function (Request $request, $id) {
    $data = $request->validate([
        'nim' => ['required', 'string', 'size:10'],
        'nama' => ['required', 'string', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:15', 'max:100'],
        'program_studi_id' => [
            'required',
            'integer',
            'exists:program_studi,id',
        ],
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        $cek = $pdo->prepare(
            'SELECT COUNT(*) FROM mahasiswa
             WHERE id = :id'
        );
        $cek->execute(['id' => $id]);

        if ((int) $cek->fetchColumn() === 0) {
            return redirect()->route('mahasiswa.index')
                ->with('gagal', 'Data mahasiswa tidak ditemukan.');
        }

        $cekDuplikat = $pdo->prepare(
            'SELECT COUNT(*) FROM mahasiswa
             WHERE (nim = :nim OR email = :email)
             AND id <> :id'
        );

        $cekDuplikat->execute([
            'nim' => $data['nim'],
            'email' => $data['email'],
            'id' => $id,
        ]);

        if ((int) $cekDuplikat->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'NIM atau email sudah digunakan mahasiswa lain.'
            );
        }

        $ubah = $pdo->prepare(
            'UPDATE mahasiswa
             SET nim = :nim,
                 nama = :nama,
                 email = :email,
                 usia = :usia,
                 program_studi_id = :program_studi_id,
                 updated_at = NOW()
             WHERE id = :id'
        );

        $ubah->execute([
            'nim' => $data['nim'],
            'nama' => $data['nama'],
            'email' => $data['email'],
            'usia' => $data['usia'],
            'program_studi_id' => $data['program_studi_id'],
            'id' => $id,
        ]);

        return redirect()->route('mahasiswa.index')
            ->with('berhasil', 'Data mahasiswa berhasil diperbarui.');
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal diperbarui. Periksa koneksi atau data.'
        );
    }
})->whereNumber('id')->name('mahasiswa.update');


Route::delete('/mahasiswa/{id}', function ($id) {
    try {
        $pdo = DB::connection()->getPdo();

        $hapus = $pdo->prepare(
            'DELETE FROM mahasiswa WHERE id = :id'
        );

        $hapus->execute(['id' => $id]);

        if ($hapus->rowCount() === 0) {
            return redirect()->route('mahasiswa.index')
                ->with('gagal', 'Data mahasiswa tidak ditemukan.');
        }

        return redirect()->route('mahasiswa.index')
            ->with('berhasil', 'Data mahasiswa berhasil dihapus.');
    } catch (\Throwable $error) {
        report($error);

        return redirect()->route('mahasiswa.index')
            ->with('gagal', 'Data gagal dihapus. Periksa apakah data masih digunakan.');
    }
})->whereNumber('id')->name('mahasiswa.destroy');


/*
|--------------------------------------------------------------------------
| ROUTE INVENTARIS - READ, SEARCH, FILTER
|--------------------------------------------------------------------------
*/

Route::get('/inventaris', function (Request $request) {
    $filterData = $request->validate([
        'cari' => ['nullable', 'string', 'max:100'],
        'ruang_id' => ['nullable', 'integer', 'exists:ruang,id'],
    ]);

    $kataKunci = trim(strip_tags($filterData['cari'] ?? ''));
    $filterRuang = $filterData['ruang_id'] ?? '';

    try {
        $pdo = DB::connection()->getPdo();

        $sql = 'SELECT i.id, i.kode_barang, i.nama_barang,
                       i.nomor_aset, i.tahun_perolehan,
                       i.ruang_id, r.nama_ruang
                FROM inventaris AS i
                JOIN ruang AS r ON r.id = i.ruang_id
                WHERE (
                    i.kode_barang LIKE :kode
                    OR i.nama_barang LIKE :nama
                    OR i.nomor_aset LIKE :aset
                )';

        $parameter = [
            'kode' => '%' . $kataKunci . '%',
            'nama' => '%' . $kataKunci . '%',
            'aset' => '%' . $kataKunci . '%',
        ];

        if ($filterRuang !== '') {
            $sql .= ' AND i.ruang_id = :ruang_id';
            $parameter['ruang_id'] = $filterRuang;
        }

        $sql .= ' ORDER BY i.kode_barang';

        $pernyataan = $pdo->prepare($sql);
        $pernyataan->execute($parameter);
        $daftarInventaris = $pernyataan->fetchAll(\PDO::FETCH_ASSOC);

        $pernyataanRuang = $pdo->prepare(
            'SELECT id, nama_ruang FROM ruang ORDER BY nama_ruang'
        );
        $pernyataanRuang->execute();
        $daftarRuang = $pernyataanRuang->fetchAll(\PDO::FETCH_ASSOC);

        return view('inventaris', compact(
            'daftarInventaris',
            'daftarRuang',
            'kataKunci',
            'filterRuang'
        ));
    } catch (\Throwable $error) {
        report($error);

        return response(
            'Data inventaris tidak dapat ditampilkan. Periksa database.',
            500
        );
    }
})->name('inventaris.index');


/*
|--------------------------------------------------------------------------
| ROUTE INVENTARIS - CREATE
|--------------------------------------------------------------------------
*/

Route::post('/inventaris', function (Request $request) {
    $request->merge([
        'kode_barang' => trim(strip_tags(
            (string) $request->input('kode_barang', '')
        )),
        'nama_barang' => trim(strip_tags(
            (string) $request->input('nama_barang', '')
        )),
        'nomor_aset' => trim(strip_tags(
            (string) $request->input('nomor_aset', '')
        )),
    ]);

    $data = $request->validate([
        'kode_barang' => [
            'required', 'string', 'size:10', 'regex:/^[A-Za-z0-9]+$/',
        ],
        'nama_barang' => ['required', 'string', 'min:3', 'max:100'],
        'nomor_aset' => ['required', 'string', 'min:5', 'max:30'],
        'tahun_perolehan' => [
            'required', 'integer', 'min:2000', 'max:2026',
        ],
        'ruang_id' => ['required', 'integer', 'exists:ruang,id'],
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        $cek = $pdo->prepare(
            'SELECT COUNT(*) FROM inventaris
             WHERE kode_barang = :kode OR nomor_aset = :nomor'
        );
        $cek->execute([
            'kode' => $data['kode_barang'],
            'nomor' => $data['nomor_aset'],
        ]);

        if ((int) $cek->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'Kode barang atau nomor aset sudah digunakan.'
            );
        }

        $simpan = $pdo->prepare(
            'INSERT INTO inventaris
                (kode_barang, nama_barang, nomor_aset,
                 tahun_perolehan, ruang_id, created_at, updated_at)
             VALUES
                (:kode, :nama, :nomor, :tahun, :ruang, NOW(), NOW())'
        );

        $simpan->execute([
            'kode' => $data['kode_barang'],
            'nama' => $data['nama_barang'],
            'nomor' => $data['nomor_aset'],
            'tahun' => $data['tahun_perolehan'],
            'ruang' => $data['ruang_id'],
        ]);

        return redirect()->route('inventaris.index')->with(
            'berhasil',
            'Data inventaris berhasil ditambahkan.'
        );
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal disimpan. Periksa koneksi atau data.'
        );
    }
})->name('inventaris.store');


/*
|--------------------------------------------------------------------------
| ROUTE INVENTARIS - FORM EDIT
|--------------------------------------------------------------------------
*/

Route::get('/inventaris/{id}/edit', function ($id) {
    try {
        $pdo = DB::connection()->getPdo();

        $cari = $pdo->prepare(
            'SELECT id, kode_barang, nama_barang, nomor_aset,
                    tahun_perolehan, ruang_id
             FROM inventaris WHERE id = :id'
        );
        $cari->execute(['id' => $id]);
        $barang = $cari->fetch(\PDO::FETCH_ASSOC);

        if (!$barang) {
            abort(404, 'Data inventaris tidak ditemukan.');
        }

        $pernyataanRuang = $pdo->prepare(
            'SELECT id, nama_ruang FROM ruang ORDER BY nama_ruang'
        );
        $pernyataanRuang->execute();
        $daftarRuang = $pernyataanRuang->fetchAll(\PDO::FETCH_ASSOC);

        return view('edit-inventaris', compact('barang', 'daftarRuang'));
    } catch (\Symfony\Component\HttpKernel\Exception\HttpExceptionInterface $error) {
        throw $error;
    } catch (\Throwable $error) {
        report($error);

        return response('Form edit tidak dapat ditampilkan.', 500);
    }
})->whereNumber('id')->name('inventaris.edit');


/*
|--------------------------------------------------------------------------
| ROUTE INVENTARIS - UPDATE
|--------------------------------------------------------------------------
*/

Route::put('/inventaris/{id}', function (Request $request, $id) {
    $request->merge([
        'kode_barang' => trim(strip_tags(
            (string) $request->input('kode_barang', '')
        )),
        'nama_barang' => trim(strip_tags(
            (string) $request->input('nama_barang', '')
        )),
        'nomor_aset' => trim(strip_tags(
            (string) $request->input('nomor_aset', '')
        )),
    ]);

    $data = $request->validate([
        'kode_barang' => [
            'required', 'string', 'size:10', 'regex:/^[A-Za-z0-9]+$/',
        ],
        'nama_barang' => ['required', 'string', 'min:3', 'max:100'],
        'nomor_aset' => ['required', 'string', 'min:5', 'max:30'],
        'tahun_perolehan' => [
            'required', 'integer', 'min:2000', 'max:2026',
        ],
        'ruang_id' => ['required', 'integer', 'exists:ruang,id'],
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        $cariBarang = $pdo->prepare(
            'SELECT COUNT(*) FROM inventaris WHERE id = :id'
        );
        $cariBarang->execute(['id' => $id]);

        if ((int) $cariBarang->fetchColumn() === 0) {
            return redirect()->route('inventaris.index')->with(
                'gagal',
                'Data inventaris tidak ditemukan.'
            );
        }

        $cek = $pdo->prepare(
            'SELECT COUNT(*) FROM inventaris
             WHERE (kode_barang = :kode OR nomor_aset = :nomor)
             AND id <> :id'
        );
        $cek->execute([
            'kode' => $data['kode_barang'],
            'nomor' => $data['nomor_aset'],
            'id' => $id,
        ]);

        if ((int) $cek->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'Kode barang atau nomor aset sudah dipakai barang lain.'
            );
        }

        $ubah = $pdo->prepare(
            'UPDATE inventaris
             SET kode_barang = :kode,
                 nama_barang = :nama,
                 nomor_aset = :nomor,
                 tahun_perolehan = :tahun,
                 ruang_id = :ruang,
                 updated_at = NOW()
             WHERE id = :id'
        );

        $ubah->execute([
            'kode' => $data['kode_barang'],
            'nama' => $data['nama_barang'],
            'nomor' => $data['nomor_aset'],
            'tahun' => $data['tahun_perolehan'],
            'ruang' => $data['ruang_id'],
            'id' => $id,
        ]);

        return redirect()->route('inventaris.index')->with(
            'berhasil',
            'Data inventaris berhasil diperbarui.'
        );
    } catch (\Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal diperbarui. Periksa koneksi atau data.'
        );
    }
})->whereNumber('id')->name('inventaris.update');


/*
|--------------------------------------------------------------------------
| ROUTE INVENTARIS - DELETE
|--------------------------------------------------------------------------
*/

Route::delete('/inventaris/{id}', function ($id) {
    try {
        $pdo = DB::connection()->getPdo();

        $hapus = $pdo->prepare(
            'DELETE FROM inventaris WHERE id = :id'
        );
        $hapus->execute(['id' => $id]);

        if ($hapus->rowCount() === 0) {
            return redirect()->route('inventaris.index')->with(
                'gagal',
                'Data inventaris tidak ditemukan.'
            );
        }

        return redirect()->route('inventaris.index')->with(
            'berhasil',
            'Data inventaris berhasil dihapus.'
        );
    } catch (\Throwable $error) {
        report($error);

        return redirect()->route('inventaris.index')->with(
            'gagal',
            'Data gagal dihapus. Periksa apakah data masih digunakan.'
        );
    }
})->whereNumber('id')->name('inventaris.destroy');
