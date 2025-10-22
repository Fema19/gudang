<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\BarangKeluar;
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
        // Ambil semua data barang keluar beserta relasi barangnya
        $barangKeluars = BarangKeluar::with('barang')
            ->orderBy('tanggal_keluar', 'desc')
            ->get();

        return view('operator.infobarang.keluar', compact('barangKeluars'));
    }
}
