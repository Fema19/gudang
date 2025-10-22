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
    public function masuk()
    {
        // Ambil semua barang (termasuk yang soft deleted) dengan histories (data barang terbaru duluan)
        $barangs = Barang::with('histories')->withTrashed()->orderBy('created_at', 'desc')->get();

        return view('operator.infobarang.masuk', compact('barangs'));
    }

    /**
     * Halaman Info Barang Keluar
     */
    public function keluar(Request $request)
    {
        // Ambil semua riwayat pengeluaran dari barang_histories
        $keluarHistories = BarangHistory::with('barang')
            ->where('type', 'keluar')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('operator.infobarang.keluar', compact('keluarHistories'));
    }
}
