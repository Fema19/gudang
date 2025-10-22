<?php

namespace App\Http\Controllers;

use App\Models\Permintaan;
use App\Models\Barang;
use App\Models\BarangHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PermintaanController extends Controller
{
    // Menampilkan semua permintaan untuk operator
    public function index()
    {
        $permintaans = Permintaan::with('barang')->latest()->get();
        return view('operator.permintaan.index', compact('permintaans'));
    }

    // Form tambah permintaan (untuk user)
    public function create()
    {
        $barangs = Barang::all();
        return view('permintaan.create', compact('barangs'));
    }

    // 🔹 Simpan permintaan baru + tanda tangan (OPSIONAL)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_peminta' => 'required|string|max:255',
            'nama_ruangan' => 'required|string|max:255',
            'barangs' => 'required|array',
            'barangs.*.barang_id' => 'required|exists:barangs,id',
            'barangs.*.jumlah' => 'required|integer|min:1',
            'barangs.*.catatan' => 'nullable|string|max:255',
            'tanda_tangan' => 'nullable|string', // ← pakai tanda_tangan, bukan signature
        ]);

        // 1️⃣ Buat permintaan utama (sementara tanpa jumlah)
        $permintaan = Permintaan::create([
            'nama_peminta' => $validated['nama_peminta'],
            'nama_ruangan' => $validated['nama_ruangan'],
            'status' => 'pending',
            'jumlah' => 0,
        ]);

        $totalJumlah = 0;

        // 2️⃣ Simpan item barang satu per satu
        foreach ($validated['barangs'] as $item) {
            $permintaan->items()->create([
                'barang_id' => $item['barang_id'],
                'jumlah' => $item['jumlah'],
                'catatan' => $item['catatan'] ?? null,
            ]);
            $totalJumlah += $item['jumlah'];
        }

        // 3️⃣ Simpan tanda tangan (jika ada)
        if (!empty($validated['tanda_tangan'])) {
            $imageData = $validated['tanda_tangan'];

            if (preg_match('/^data:image\/png;base64,/', $imageData)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $imageData = str_replace(' ', '+', $imageData);
                $imageName = 'tanda_tangan_' . $permintaan->id . '_' . time() . '.png';

                // Simpan ke storage/app/public/tanda_tangan/
                Storage::disk('public')->put('tanda_tangan/' . $imageName, base64_decode($imageData));

                // Simpan path di database
                $permintaan->update([
                    'tanda_tangan' => 'tanda_tangan/' . $imageName,
                ]);
            }
        }

        // 4️⃣ Update total jumlah ke tabel permintaans
        $permintaan->update(['jumlah' => $totalJumlah]);

        return redirect()->route('permintaan.create')
            ->with('success', 'Permintaan berhasil dikirim ke operator.');
    }

    // ------------------------ Fungsi lainnya tetap sama ------------------------

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

                    $stokBefore = $barang->stok;

                    $barang->stok -= $item->jumlah;
                    $barang->save();

                    // record history pengeluaran untuk setiap item
                    BarangHistory::create([
                        'barang_id' => $barang->id,
                        'type' => 'keluar',
                        'qty' => $item->jumlah,
                        'stok_before' => $stokBefore,
                        'stok_after' => $barang->stok,
                        'note' => "Pengeluaran untuk permintaan #{$permintaan->id} - {$permintaan->nama_peminta}",
                    ]);
                }

                $permintaan->update(['status' => 'selesai']);
            });
        } catch (\Exception $e) {
            return redirect()->route('permintaan.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }

        return redirect()->route('permintaan.index')->with('success', 'Permintaan telah diselesaikan.');
    }

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

    public function clear()
    {
        $deletedCount = Permintaan::where('status', '!=', 'pending')->delete();
        return redirect()->route('permintaan.index')->with('success', "Berhasil memindahkan {$deletedCount} riwayat permintaan yang sudah diproses ke trash (soft deleted). Anda dapat memulihkannya lewat fitur restore jika diperlukan.");
    }

    public function trash()
    {
        $permintaans = Permintaan::onlyTrashed()->with('barang')->latest('deleted_at')->get();
        return view('operator.permintaan.trash', compact('permintaans'));
    }

    public function restore($id)
    {
        $permintaan = Permintaan::withTrashed()->findOrFail($id);
        $permintaan->restore();
        return redirect()->route('permintaan.trash')->with('success', 'Permintaan berhasil dipulihkan.');
    }

    public function restoreAll()
    {
        $restored = Permintaan::onlyTrashed()->restore();
        return redirect()->route('permintaan.trash')->with('success', "Berhasil memulihkan {$restored} permintaan.");
    }

    public function notif()
    {
        return view('user.notif');
    }

    public function stats(Request $request)
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $rows = DB::table('permintaans')
            ->leftJoin('permintaan_items', 'permintaan_items.permintaan_id', '=', 'permintaans.id')
            ->leftJoin('barangs', 'permintaan_items.barang_id', '=', 'barangs.id')
            ->select(
                'permintaans.id as permintaan_id',
                'permintaans.nama_peminta',
                'permintaans.nama_ruangan',
                'permintaans.status',
                'permintaans.tanda_tangan',
                'permintaans.created_at',
                DB::raw('GROUP_CONCAT(CONCAT(barangs.nama_barang, " (", permintaan_items.jumlah, ")") SEPARATOR ", ") as daftar_barang'),
                DB::raw('SUM(permintaan_items.jumlah) as total_barang')
            )
            ->whereYear('permintaans.created_at', $year)
            ->whereMonth('permintaans.created_at', $month)
            ->groupBy(
                'permintaans.id',
                'permintaans.nama_peminta',
                'permintaans.nama_ruangan',
                'permintaans.status',
                'permintaans.tanda_tangan',
                'permintaans.created_at'
            )
            ->orderByDesc('permintaans.created_at')
            ->get();

        $stats = $rows->map(function ($r) {
            return [
                'barang' => $r->daftar_barang ?? '-',
                'nama_peminta' => $r->nama_peminta,
                'ruangan' => $r->nama_ruangan,
                'total' => (int) $r->total_barang,
                'status' => $r->status,
                'tanda_tangan' => $r->tanda_tangan,
                'tanggal' => \Carbon\Carbon::parse($r->created_at)->isoFormat('D MMMM YYYY'),
                'jam' => \Carbon\Carbon::parse($r->created_at)->format('H:i'),
            ];
        })->toArray();

        $selectedMonthName = \Carbon\Carbon::create($year, $month, 1)->locale('id')->isoFormat('MMMM');

        return view('operator.permintaan.stats', compact('stats', 'month', 'year'))
            ->with([
                'selectedMonth' => $month,
                'selectedYear' => $year,
                'selectedMonthName' => $selectedMonthName
        ]);
    }



    public function exportStatsPdf(Request $request)
    {
        $month = (int) $request->query('month', Carbon::now()->month);
        $year = (int) $request->query('year', Carbon::now()->year);

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
                'permintaans.tanda_tangan',
                'permintaans.created_at'
            )
            ->whereYear('permintaans.created_at', $year)
            ->whereMonth('permintaans.created_at', $month)
            ->orderByDesc('permintaans.created_at')
            ->get();

        $stats = $rows->map(function($r){
            $dt = Carbon::parse($r->created_at)->setTimezone('Asia/Jakarta');
            return [
                'barang' => $r->barang,
                'nama_peminta' => $r->nama_peminta,
                'ruangan' => $r->nama_ruangan,
                'total' => (int) $r->total,
                'status' => $r->status,
                'tanda_tangan' => $r->tanda_tangan,
                'tanggal' => $dt->locale('id')->isoFormat('D MMMM YYYY'),
                'jam' => $dt->format('d M Y, H:i'),
            ];
        })->toArray();

        $selectedMonthName = Carbon::create($year, $month, 1)->locale('id')->isoFormat('MMMM');

        if (class_exists(\PDF::class) || class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
            try {
                $pdf = \PDF::loadView('operator.permintaan.stats_print', compact('stats', 'selectedMonthName', 'month', 'year'));
            } catch (\Throwable $e) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('operator.permintaan.stats_print', compact('stats', 'selectedMonthName', 'month', 'year'));
            }

            $fileName = "statistik-{$year}-{$month}.pdf";
            return $pdf->download($fileName);
        }

        return view('operator.permintaan.stats_print', compact('stats'))
            ->with(['selectedMonth' => $month, 'selectedYear' => $year, 'selectedMonthName' => $selectedMonthName]);
    }
}