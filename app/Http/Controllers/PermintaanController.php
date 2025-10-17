<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;



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
                    'nama_peminta' => 'required|string|max:255',
                    'nama_ruangan' => 'required|string|max:255',
                    'barangs' => 'required|array',
                    'barangs.*.barang_id' => 'required|exists:barangs,id',
                    'barangs.*.jumlah' => 'required|integer|min:1',
                    'barangs.*.catatan' => 'nullable|string|max:255',
                ]);

                // 1️⃣ Buat permintaan utama dulu (tanpa jumlah)
                $permintaan = Permintaan::create([
                    'nama_peminta' => $validated['nama_peminta'],
                    'nama_ruangan' => $validated['nama_ruangan'],
                    'status' => 'pending',
                    'jumlah' => 0, // sementara diisi 0, nanti diupdate
                ]);

                $totalJumlah = 0;

                // 2️⃣ Simpan item barang satu per satu
                foreach ($validated['barangs'] as $item) {
                    $permintaan->items()->create([
                        'barang_id' => $item['barang_id'],
                        'jumlah' => $item['jumlah'],
                        'catatan' => $item['catatan'] ?? null,
                    ]);

                    $totalJumlah += $item['jumlah']; // hitung total
                }

                // 3️⃣ Update total jumlah ke tabel permintaans
                $permintaan->update(['jumlah' => $totalJumlah]);

                return redirect()->route('permintaan.create')
                    ->with('success', 'Permintaan berhasil dikirim ke operator.');
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

    // Statistik permintaan per bulan (operator)
    public function stats(Request $request)
    {
        $month = (int) $request->query('month', Carbon::now()->month);
        $year = (int) $request->query('year', Carbon::now()->year);

        // Ambil daftar per-item permintaan untuk bulan/tahun terpilih
        $rows = DB::table('permintaan_items')
            ->join('permintaans', 'permintaan_items.permintaan_id', '=', 'permintaans.id')
            ->join('barangs', 'permintaan_items.barang_id', '=', 'barangs.id')
            ->select(
                'permintaans.id as permintaan_id',
                'barangs.nama_barang as barang',
                'permintaans.nama_peminta',
                'permintaans.nama_ruangan',
                'permintaan_items.jumlah as total',
                'permintaans.status',
                'permintaans.created_at'
            )
            ->whereYear('permintaans.created_at', $year)
            ->whereMonth('permintaans.created_at', $month)
            ->orderByDesc('permintaans.created_at')
            ->get();

        $stats = $rows->map(function($r){
            return [
                'barang' => $r->barang,
                'nama_peminta' => $r->nama_peminta,
                'ruangan' => $r->nama_ruangan,
                'total' => (int) $r->total,
                'status' => $r->status,
                'tanggal' => Carbon::parse($r->created_at)->isoFormat('D MMMM YYYY'),
            ];
        })->toArray();

        $selectedMonthName = Carbon::create($year, $month, 1)->locale('id')->isoFormat('MMMM');

        return view('operator.permintaan.stats', compact('stats', 'month', 'year'))
            ->with(['selectedMonth' => $month, 'selectedYear' => $year, 'selectedMonthName' => $selectedMonthName]);
    }

    // Export statistik (PDF/HTML). If you have a PDF lib (dompdf), you can render to PDF here.
    public function exportStatsPdf(Request $request)
    {
        $month = (int) $request->query('month', Carbon::now()->month);
        $year = (int) $request->query('year', Carbon::now()->year);

        // Ambil daftar item permintaan sama seperti stats()
        $rows = DB::table('permintaan_items')
            ->join('permintaans', 'permintaan_items.permintaan_id', '=', 'permintaans.id')
            ->join('barangs', 'permintaan_items.barang_id', '=', 'barangs.id')
            ->select(
                'permintaans.id as permintaan_id',
                'barangs.nama_barang as barang',
                'permintaans.nama_peminta',
                'permintaans.nama_ruangan',
                'permintaan_items.jumlah as total',
                'permintaans.status',
                'permintaans.created_at'
            )
            ->whereYear('permintaans.created_at', $year)
            ->whereMonth('permintaans.created_at', $month)
            ->orderByDesc('permintaans.created_at')
            ->get();

        $stats = $rows->map(function($r){
            return [
                'barang' => $r->barang,
                'nama_peminta' => $r->nama_peminta,
                'ruangan' => $r->nama_ruangan,
                'total' => (int) $r->total,
                'status' => $r->status,
                'tanggal' => Carbon::parse($r->created_at)->isoFormat('D MMMM YYYY'),
            ];
        })->toArray();

        $selectedMonthName = Carbon::create($year, $month, 1)->locale('id')->isoFormat('MMMM');

        // Generate PDF using barryvdh/laravel-dompdf if available
        if (class_exists(\PDF::class) || class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            // Support both facade names depending on package version
            $pdf = null;
            try {
                $pdf = \PDF::loadView('operator.permintaan.stats_print', compact('stats', 'selectedMonthName', 'month', 'year'));
            } catch (\Throwable $e) {
                // fallback: try namespaced facade
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('operator.permintaan.stats_print', compact('stats', 'selectedMonthName', 'month', 'year'));
            }

            $fileName = "statistik-{$year}-{$month}.pdf";
            return $pdf->download($fileName);
        }

        // Jika paket PDF tidak terpasang, kembalikan HTML
        return view('operator.permintaan.stats_print', compact('stats'))
            ->with(['selectedMonth' => $month, 'selectedYear' => $year, 'selectedMonthName' => $selectedMonthName]);
    }
}
