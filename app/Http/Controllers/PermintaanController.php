<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermintaanController extends Controller
{
    // Menampilkan semua permintaan untuk operator
    public function index()
    {
    $permintaans = Permintaan::with('items.barang')->latest()->get();
    return view('operator.permintaan.index', compact('permintaans'));
    }


    // Form tambah permintaan (untuk user) sekaligus mengirim data barang untuk modal
    public function create()
    {
        $barangs = Barang::all(); // ambil semua stok
        return view('permintaan.create', compact('barangs'));
    }

    // Simpan permintaan baru
    public function store(Request $request)
{
    $validated = $request->validate([
        'nama_peminta' => 'required|string',
        'nama_ruangan' => 'required|string',
        'barangs' => 'required|array', // array barang dan jumlah
        'barangs.*.barang_id' => 'required|exists:barangs,id',
        'barangs.*.jumlah' => 'required|integer|min:1',
        'keterangan' => 'nullable|string',
    ]);

    DB::transaction(function () use ($validated) {
        // Simpan data utama permintaan
        $permintaan = Permintaan::create([
            'nama_peminta' => $validated['nama_peminta'],
            'nama_ruangan' => $validated['nama_ruangan'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        // Simpan setiap barang yang diminta ke tabel permintaan_items
        foreach ($validated['barangs'] as $item) {
            DB::table('permintaan_items')->insert([
                'permintaan_id' => $permintaan->id,
                'barang_id' => $item['barang_id'],
                'jumlah' => $item['jumlah'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    });

    return redirect()->back()->with('success', 'Permintaan berhasil ditambahkan!');
}


    // Ubah status jadi selesai (operator)
public function updateStatus($id)
{
    $permintaan = Permintaan::with('items.barang')->findOrFail($id);

    if ($permintaan->status !== 'pending') {
        return redirect()->route('permintaan.index')->with('error', 'Permintaan sudah diproses.');
    }

    try {
        DB::transaction(function () use ($permintaan) {
            // Loop setiap item permintaan
            foreach ($permintaan->items as $item) {
                $barang = Barang::where('id', $item->barang_id)->lockForUpdate()->first();

                if (!$barang) {
                    throw new \Exception("Data barang (ID {$item->barang_id}) tidak ditemukan");
                }

                if ($barang->stok < $item->jumlah) {
                    throw new \Exception("Stok untuk {$barang->nama_barang} tidak mencukupi");
                }

                // Kurangi stok
                $barang->stok -= $item->jumlah;
                $barang->save();
            }

            // Update status permintaan jadi selesai
            $permintaan->status = 'selesai';
            $permintaan->save();
        });
    } catch (\Exception $e) {
        return redirect()->route('permintaan.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }

    return redirect()->route('permintaan.index')->with('success', 'Permintaan telah diterima dan stok diperbarui.');
}


    // Menolak permintaan (operator)
    public function reject(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'required|string|max:255',
        ]);

        $permintaan = Permintaan::findOrFail($id);
        $permintaan->update([
            'status' => 'rejected',
            'keterangan' => $request->keterangan,
        ]);

        session()->flash('notif', "Permintaan Anda ditolak: {$request->keterangan}");

        return redirect()->route('permintaan.index')->with('error', 'Permintaan ditolak.');
    }

    // Hapus semua riwayat permintaan yang sudah diproses (selesai atau ditolak), tapi jangan hapus yang masih pending
    public function clear()
    {
        // Pastikan hanya menghapus yang statusnya bukan 'pending'
    $deletedCount = Permintaan::where('status', '!=', 'pending')->delete();

    return redirect()->route('permintaan.index')->with('success', "Berhasil memindahkan {$deletedCount} riwayat permintaan yang sudah diproses ke trash (soft deleted). Anda dapat memulihkannya lewat fitur restore jika diperlukan.");
    }

    // Tampilkan daftar permintaan yang sudah di-soft-delete (trash)
    public function trash()
    {
        $permintaans = Permintaan::onlyTrashed()->with('items.barang')->latest('deleted_at')->get();
        return view('operator.permintaan.trash', compact('permintaans'));
    }

    // Restore satu permintaan dari trash
    public function restore($id)
    {
        $permintaan = Permintaan::withTrashed()->findOrFail($id);
        $permintaan->restore();

        return redirect()->route('permintaan.trash')->with('success', 'Permintaan berhasil dipulihkan.');
    }

    // Restore semua permintaan di trash
    public function restoreAll()
    {
        $restored = Permintaan::onlyTrashed()->restore();
        return redirect()->route('permintaan.trash')->with('success', "Berhasil memulihkan {$restored} permintaan.");
    }

    // Halaman notifikasi user
    public function notif()
    {
        return view('user.notif');
    }
}
