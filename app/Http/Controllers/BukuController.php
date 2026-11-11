<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Kategori;
use Illuminate\Http\Request;

class BukuController extends Controller
{
    public function index()
    {
        $bukus = Buku::with('kategori')->latest()->get();

        return view('buku.index', compact('bukus'));
    }

    public function create()
    {
        $kategoris = Kategori::all();

        return view('buku.form', compact('kategoris'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:bukus,kode',
            'judul' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
        ]);

        Buku::create($validated);

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil ditambahkan.');
    }

    public function edit(string $id)
    {
        $buku = Buku::findOrFail($id);
        $kategoris = Kategori::all();

        return view('buku.form', compact('buku', 'kategoris'));
    }

    public function update(Request $request, string $id)
    {
        $buku = Buku::findOrFail($id);

        $validated = $request->validate([
            'kode' => 'required|string|max:10|unique:bukus,kode,' . $buku->id,
            'judul' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategoris,id',
        ]);

        $buku->update($validated);

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        $buku = Buku::findOrFail($id);
        $buku->delete();

        return redirect()->route('buku.index')
            ->with('success', 'Buku berhasil dihapus.');
    }
}
