<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermintaanController extends Controller
{
    // Menampilkan semua permintaan (untuk operator)
    public function index()
    {
        $permintaans = Permintaan::with('items.barang')->latest()->get();
        return view('operator.permintaan.index', compact('permintaans'));
    }

    // Form tambah permintaan (untuk user)
    public function create()
    {
        $barangs = Barang::all();
        return view('permintaan.create', compact('barangs'));
    }

    // Simpan permintaan baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_peminta' => 'required|string|max:255',
            'nama_ruangan' => 'required|string|max:255',
            'barangs' => 'required|array',
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
                'status' => 'pending', // tambahkan default status biar aman
            ]);

            // Simpan item barang
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

    // Operator menerima permintaan & kurangi stok
    public function updateStatus($id)
    {
        $permintaan = Permintaan::with('items.barang')->findOrFail($id);

        if ($permintaan->status !== 'pending') {
            return redirect()->route('permintaan.index')->with('error', 'Permintaan sudah diproses.');
        }

        try {
            DB::transaction(function () use ($permintaan) {
                foreach ($permintaan->items as $item) {
                    $barang = Barang::lockForUpdate()->find($item->barang_id);

                    if (!$barang) {
                        throw new \Exception("Data barang (ID {$item->barang_id}) tidak ditemukan");
                    }

                    if ($barang->stok < $item->jumlah) {
                        throw new \Exception("Stok untuk {$barang->nama_barang} tidak mencukupi");
                    }

                    $barang->stok -= $item->jumlah;
                    $barang->save();
                }

                $permintaan->update(['status' => 'selesai']);
            });
        } catch (\Exception $e) {
            return redirect()->route('permintaan.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->route('permintaan.index')->with('success', 'Permintaan telah diterima dan stok diperbarui.');
    }

    // Menolak permintaan
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

    // Hapus riwayat yang sudah diproses
    public function clear()
    {
        $deletedCount = Permintaan::where('status', '!=', 'pending')->delete();

        return redirect()->route('permintaan.index')->with(
            'success',
            "Berhasil memindahkan {$deletedCount} permintaan ke trash. Anda dapat memulihkannya lewat fitur restore."
        );
    }

    // Tampilkan trash
    public function trash()
    {
        $permintaans = Permintaan::onlyTrashed()->with('items.barang')->latest('deleted_at')->get();
        return view('operator.permintaan.trash', compact('permintaans'));
    }

    // Restore satu permintaan
    public function restore($id)
    {
        $permintaan = Permintaan::withTrashed()->findOrFail($id);
        $permintaan->restore();

        return redirect()->route('permintaan.trash')->with('success', 'Permintaan berhasil dipulihkan.');
    }

    // Restore semua permintaan
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
