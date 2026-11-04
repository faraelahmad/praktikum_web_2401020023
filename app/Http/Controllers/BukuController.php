<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    // Menampilkan daftar buku dan pencarian
    public function index(Request $request)
    {
        $daftarBuku = Buku::contoh();
        $keyword = $request->query('q');

        if ($keyword) {
            $daftarBuku = array_filter($daftarBuku, function ($buku) use ($keyword) {
                return stripos($buku['kode'], $keyword) !== false ||
                       stripos($buku['judul'], $keyword) !== false;
            });
        }

        return view('buku.index', [
            'daftarBuku' => $daftarBuku,
            'keyword' => $keyword
        ]);
    }

    // Menampilkan detail buku
    public function show($kode)
    {
        $daftarBuku = Buku::contoh();
        $buku = null;

        foreach ($daftarBuku as $item) {
            if ($item['kode'] === $kode) {
                $buku = $item;
                break;
            }
        }

        if (!$buku) {
            abort(404);
        }

        return view('buku.show', compact('buku'));
    }
}

