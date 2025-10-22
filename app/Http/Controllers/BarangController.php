<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use App\Models\BarangHistory;

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
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        // Generate kode otomatis: BRG001, BRG002, dst
        $lastBarang = Barang::latest('id')->first();
        $nextNumber = $lastBarang ? ((int) substr($lastBarang->kode_barang, 3)) + 1 : 1;
        $kode_barang = 'BRG' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        $data = [
            'kode_barang' => $kode_barang,
            'nama_barang' => $request->nama_barang,
            'stok' => $request->stok,
            'satuan' => $request->satuan,
            'kategori' => $request->kategori,
        ];

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('barangs', 'public');
            $data['foto'] = $path;
        }

        $barang = Barang::create($data);

        // record creation history
        BarangHistory::create([
            'barang_id' => $barang->id,
            'type' => 'created',
            'qty' => $barang->stok,
            'stok_before' => null,
            'stok_after' => $barang->stok,
            'note' => 'Barang dibuat',
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
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048',
        ]);

        $data = $request->only(['nama_barang', 'stok', 'satuan', 'kategori']);

        if ($request->hasFile('foto')) {
            // remove old foto if exists
            if (!empty($barang->foto) && \Storage::disk('public')->exists($barang->foto)) {
                \Storage::disk('public')->delete($barang->foto);
            }

            $path = $request->file('foto')->store('barangs', 'public');
            $data['foto'] = $path;
        }

        $stokBefore = $barang->stok;

        $barang->update($data);

        // if stok changed, record history
        if (array_key_exists('stok', $data) && $data['stok'] != $stokBefore) {
            BarangHistory::create([
                'barang_id' => $barang->id,
                'type' => 'stock_changed',
                'qty' => $data['stok'] - $stokBefore,
                'stok_before' => $stokBefore,
                'stok_after' => $data['stok'],
                'note' => 'Perubahan stok melalui edit barang',
            ]);
        }

        return redirect()->route('barang.index')->with('success', 'Barang berhasil diperbarui.');
    }

    /**
     * Hapus barang.
     */
    public function destroy(Barang $barang)
    {
        // delete foto file if exists
        if (!empty($barang->foto) && \Storage::disk('public')->exists($barang->foto)) {
            \Storage::disk('public')->delete($barang->foto);
        }

        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus.');
    }
}
