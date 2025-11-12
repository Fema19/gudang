<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class InfoBarangController extends Controller
{
    /**
     * Halaman Info Barang Masuk (utama)
     */
    public function masuk(Request $request)
    {
        $query = Barang::with(['histories' => function ($q) {
            $q->latest();
        }])->withTrashed();

        // 🔍 filter berdasarkan nama barang (jika ada search)
        if ($request->has('search') && $request->search != '') {
            $query->where('nama_barang', 'like', '%' . $request->search . '%');
        }

        // 🗓️ filter berdasarkan bulan (jika dipilih)
        if ($request->has('month') && $request->month != '') {
            $query->whereMonth('created_at', $request->month);
        }

        $barangs = $query->orderBy('created_at', 'desc')->get();

        return view('operator.infobarang.masuk', compact('barangs'));
    }

    public function keluar(Request $request)
    {
        $query = BarangHistory::where('type', 'keluar')->with('barang');

        // 🔍 filter berdasarkan nama barang
        if ($request->has('search') && $request->search != '') {
            $query->whereHas('barang', function ($q) use ($request) {
                $q->where('nama_barang', 'like', '%' . $request->search . '%');
            });
        }

        // 🗓️ filter berdasarkan bulan
        if ($request->has('month') && $request->month != '') {
            $query->whereMonth('created_at', $request->month);
        }

        // 🗓️ filter berdasarkan tahun
        if ($request->has('year') && $request->year != '') {
            $query->whereYear('created_at', $request->year);
        }

        $keluarHistories = $query->orderBy('created_at', 'desc')->get();

        // ✅ view sesuai dengan struktur kamu (tidak pakai "operator.")
        return view('operator.infobarang.keluar', compact('keluarHistories'));
    }

    /**
     * Export daftar barang masuk ke PDF / print view
     */
    public function exportMasukPdf()
    {
        // Ambil semua barang dengan histories untuk data barang masuk
        $barangs = Barang::with('histories')->withTrashed()->orderBy('created_at', 'desc')->get();
        
        // Flatten dan filter histories
        $rows = collect();
        foreach($barangs as $b) {
            foreach($b->histories as $h) {
                if ($h->type === 'keluar') continue; // Skip keluar events
                $rows->push([
                    'nama' => $b->nama_barang,
                    'qty' => $h->qty ?? '-',
                    'stok_before' => $h->stok_before ?? '-',
                    'stok_after' => $h->stok_after ?? '-',
                    'type' => $h->type === 'created' ? 'Dibuat' : 'Perubahan Stok',
                    'created_at' => Carbon::parse($h->created_at)
                        ->setTimezone('Asia/Jakarta')
                        ->format('d M Y, H:i'),
                ]);
            }
        }
        
        $rows = $rows->sortByDesc('created_at')->values()->map(function ($r) {
            return [
                'nama' => $r['nama'],
                'qty' => $r['qty'],
                'stok_before' => $r['stok_before'],
                'stok_after' => $r['stok_after'],
                'type' => $r['type'],
                'created_at' => $r['created_at']
            ];
        })->toArray();

        // Load PDF library
        if (class_exists('\PDF')) {
            $pdf = \PDF::loadView('operator.infobarang.masuk_print', compact('rows'));
        } else {
            try {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('operator.infobarang.masuk_print', compact('rows'));
            } catch (\Throwable $e) {
                return view('operator.infobarang.masuk_print', compact('rows'));
            }
        }

        // Force download PDF
        return $pdf->download('info-barang-masuk-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Export daftar barang keluar (grouped) ke PDF / print view
     */
    public function exportKeluarPdf()
    {
        // Ambil data barang keluar yang sudah digabung per barang
        $keluarGrouped = BarangHistory::selectRaw('barang_id, SUM(qty) as total_qty, MAX(created_at) as latest_created_at')
            ->where('type', 'keluar')
            ->groupBy('barang_id')
            ->orderByRaw('MAX(created_at) desc')
            ->get();

        $rows = $keluarGrouped->map(function ($row) {
            $barang = \App\Models\Barang::withTrashed()->find($row->barang_id);
            $latestHistory = \App\Models\BarangHistory::where('barang_id', $row->barang_id)
                ->where('type', 'keluar')
                ->orderBy('created_at', 'desc')
                ->first();

            return [
                'nama' => $barang->nama_barang ?? '-',
                'qty' => (int) $row->total_qty,
                'stok_after' => $latestHistory->stok_after ?? ($barang->stok ?? 0),
                'created_at' => Carbon::parse($row->latest_created_at)
                    ->setTimezone('Asia/Jakarta')
                    ->format('d M Y, H:i'),
            ];
        })->toArray();

        // Load PDF library
        if (class_exists('\PDF')) {
            $pdf = \PDF::loadView('operator.infobarang.keluar_print', compact('rows'));
        } else {
            try {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('operator.infobarang.keluar_print', compact('rows'));
            } catch (\Throwable $e) {
                return view('operator.infobarang.keluar_print', compact('rows'));
            }
        }

        // Force download PDF
        return $pdf->download('info-barang-keluar-' . date('Y-m-d') . '.pdf');
    }

    /**
     * Hapus satu history (dipakai di halaman masuk) -- hapus berdasarkan id history
     */
    public function destroyHistory(BarangHistory $history)
    {
        // TODO: tambahkan authorization jika diperlukan
        $history->delete();
        return redirect()->back()->with('status', 'Riwayat berhasil dihapus');
    }

    /**
     * Clear semua history tipe 'keluar' untuk sebuah barang (dipakai di halaman keluar)
     */
    public function clearKeluarByBarang(Barang $barang)
    {
        // TODO: tambahkan authorization jika diperlukan
        $deleted = BarangHistory::where('barang_id', $barang->id)->where('type', 'keluar')->delete();
        return redirect()->back()->with('status', "Riwayat keluar untuk '{$barang->nama_barang}' berhasil dihapus ({$deleted} baris)");
    }

    /**
     * Hapus semua history masuk (type != 'keluar')
     */
    public function clearAllMasuk()
    {
        // TODO: tambahkan authorization jika diperlukan
        $deleted = BarangHistory::where('type', '!=', 'keluar')->delete();
        return redirect()->back()->with('status', "Semua riwayat masuk/perubahan stok berhasil dihapus ({$deleted} baris)");
    }

    /**
     * Hapus semua history keluar
     */
    public function clearAllKeluar()
    {
        // TODO: tambahkan authorization jika diperlukan
        $deleted = BarangHistory::where('type', 'keluar')->delete();
        return redirect()->back()->with('status', "Semua riwayat keluar berhasil dihapus ({$deleted} baris)");
    }
}
