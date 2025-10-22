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
        // include trashed records to avoid duplicating kode when soft-deleted items exist
        $lastBarang = Barang::withTrashed()->orderBy('id', 'desc')->first();
        $nextNumber = 1;
        if ($lastBarang && !empty($lastBarang->kode_barang) && preg_match('/BRG(\d+)/', $lastBarang->kode_barang, $m)) {
            $nextNumber = ((int) $m[1]) + 1;
        }
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

        // create inside transaction with small retry loop to avoid kode_barang duplicate in race conditions
        $attempt = 0;
        $barang = null;
        while ($attempt < 3) {
            try {
                \DB::beginTransaction();
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

                \DB::commit();
                break;
            } catch (\Illuminate\Database\QueryException $e) {
                \DB::rollBack();
                $attempt++;
                // if duplicate kode_barang, try regenerate kode and retry
                if ($e->getCode() == '23000') {
                    // regenerate next kode using latest id
                    $lastBarang = Barang::withTrashed()->orderBy('id', 'desc')->first();
                    $nextNumber = 1;
                    if ($lastBarang && !empty($lastBarang->kode_barang) && preg_match('/BRG(\d+)/', $lastBarang->kode_barang, $m)) {
                        $nextNumber = ((int) $m[1]) + 1 + $attempt; // bump by attempt to reduce collision
                    }
                    $data['kode_barang'] = 'BRG' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
                    usleep(200000); // wait 200ms before retry
                    continue;
                }

                throw $e;
            }
        }

        if (!$barang) {
            throw new \Exception('Gagal membuat barang setelah beberapa percobaan.');
        }

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
        // only remove foto file, but keep the database record (soft delete)
        if (!empty($barang->foto) && \Storage::disk('public')->exists($barang->foto)) {
            \Storage::disk('public')->delete($barang->foto);
        }

        // record deletion history
        BarangHistory::create([
            'barang_id' => $barang->id,
            'type' => 'deleted',
            'qty' => null,
            'stok_before' => $barang->stok,
            'stok_after' => null,
            'note' => 'Barang dihapus',
        ]);

        // soft delete the barang so it won't be visible in stok list but still exists for info
        $barang->delete();

        return redirect()->route('barang.index')->with('success', 'Barang berhasil dihapus (soft delete).');
    }
}
