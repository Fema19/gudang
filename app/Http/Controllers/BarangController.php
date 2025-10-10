<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;

class BarangController extends Controller
{
    /**
     * Tampilkan daftar barang.
     */
    public function index()
    {
        $barangs = Barang::latest()->get();
        return view('operator.barang.index', compact('barangs'));
    }

    /**
     * Form tambah barang.
     */
    public function create()
    {
        return view('operator.barang.create');
    }

    /**
     * Simpan barang baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'satuan' => 'nullable|string|max:50',
            'kategori' => 'nullable|string|max:100',
        ]);

        // Generate kode otomatis: BRG001, BRG002, dst
        $lastBarang = Barang::latest('id')->first();
        $nextNumber = $lastBarang ? ((int) substr($lastBarang->kode_barang, 3)) + 1 : 1;
        $kode_barang = 'BRG' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        Barang::create([
            'kode_barang' => $kode_barang,
            'nama_barang' => $request->nama_barang,
            'stok' => $request->stok,
            'satuan' => $request->satuan,
            'kategori' => $request->kategori,
        ]);

        return redirect()->route('barang.index')->with('success', 'Barang berhasil ditambahkan.');
    }

    /**
     * Form edit barang.
     */
    public function edit(Barang $barang)
    {
        return view('operator.barang.edit', compact('barang'));
    }

    /**
     * Update barang.
     */
    public function update(Request $request, Barang $barang)
    {
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'stok' => 'required|integer|min:0',
            'satuan' => 'nullable|string|max:50',
            'kategori' => 'nullable|string|max:100',
        ]);

        $barang->update($request->only(['nama_barang', 'stok', 'satuan', 'kategori']));

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

    /**
     * Hapus barang.
     */
    public function destroy(Barang $barang)
    {
        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus.');
    }
}
