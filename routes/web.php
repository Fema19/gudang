<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PermintaanController;
use App\Http\Controllers\OperatorAuthController;
use App\Http\Controllers\InfoBarangController;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Gudang Kantor
|--------------------------------------------------------------------------
| - Karyawan bisa membuat permintaan barang
| - Operator bisa mengelola stok dan memproses permintaan
|--------------------------------------------------------------------------
*/

// ============================
// 👩‍💼 Karyawan
// ============================

// Halaman utama langsung ke form permintaan barang
Route::get('/', [PermintaanController::class, 'create'])->name('permintaan.create');
Route::post('/permintaan', [PermintaanController::class, 'store'])->name('permintaan.store');

//user


// ============================
// ⚙️ Operator (Manajemen Gudang)
// ============================

// 🔐 Login / Logout Operator
    Route::get('/login', [OperatorAuthController::class, 'showLoginForm'])->name('operator.login');
    Route::post('/login', [OperatorAuthController::class, 'login'])->name('operator.login.post'); // HARUS SAMA
    Route::post('/logout', [OperatorAuthController::class, 'logout'])->name('operator.logout');

Route::prefix('operator')->group(function () {

    // CRUD Barang
    Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
    Route::get('/barang/create', [BarangController::class, 'create'])->name('barang.create');
    Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
    Route::get('/barang/{barang}/edit', [BarangController::class, 'edit'])->name('barang.edit');
    Route::put('/barang/{barang}', [BarangController::class, 'update'])->name('barang.update');
    Route::delete('/barang/{barang}', [BarangController::class, 'destroy'])->name('barang.destroy');

    // Permintaan (operator)
    Route::get('/permintaan', [PermintaanController::class, 'index'])->name('permintaan.index');
    Route::patch('/permintaan/{permintaan}/selesai', [PermintaanController::class, 'updateStatus'])->name('permintaan.selesai');
    Route::post('/permintaan/{permintaan}/reject', [PermintaanController::class, 'reject'])->name('permintaan.reject');
    Route::delete('/permintaan/{permintaan}', [PermintaanController::class, 'destroy'])->name('permintaan.destroy');
    Route::post('/permintaan/clear', [PermintaanController::class, 'clear'])->name('permintaan.clear');
    // Trash & restore
    Route::get('/permintaan/trash', [PermintaanController::class, 'trash'])->name('permintaan.trash');
    Route::post('/permintaan/{id}/restore', [PermintaanController::class, 'restore'])->name('permintaan.restore');
    Route::post('/permintaan/restore-all', [PermintaanController::class, 'restoreAll'])->name('permintaan.restoreAll');


    // Statistik permintaan per bulan
    Route::get('/permintaan/stats', [PermintaanController::class, 'stats'])->name('permintaan.stats');
    // Export statistik (PDF/HTML)
    Route::get('/permintaan/stats/export', [PermintaanController::class, 'exportStatsPdf'])->name('permintaan.stats.export');

    // Info Masuk/Keluar Barang
    Route::get('/infobarang', [InfoBarangController::class, 'masuk'])->name('infobarang.index');
    Route::get('/infobarang/keluar', [InfoBarangController::class, 'keluar'])->name('infobarang.keluar');
});

