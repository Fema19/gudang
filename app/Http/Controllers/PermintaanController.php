<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use App\Models\Barang;
use Illuminate\Http\Request;

class PermintaanController extends Controller
{
    // Menampilkan semua permintaan untuk operator
    public function index()
    {
        $permintaans = Permintaan::with('barang')->latest()->get();
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
            'barang_id' => 'required|exists:barangs,id',
            'nama_peminta' => 'required|string|max:100',
            'nama_ruangan' => 'required|string|max:100',
            'jumlah' => 'required|integer|min:1',
        ]);

        Permintaan::create($validated + ['status' => 'pending']);

        return redirect()->route('permintaan.create')->with('success', 'Permintaan berhasil dikirim dan menunggu konfirmasi operator.');
    }

    // Ubah status jadi selesai (operator)
    public function updateStatus($id)
    {
        $permintaan = Permintaan::findOrFail($id);
        $permintaan->update(['status' => 'selesai']);

        return redirect()->route('permintaan.index')->with('success', 'Permintaan telah diselesaikan.');
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
        $permintaans = Permintaan::onlyTrashed()->with('barang')->latest('deleted_at')->get();
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
