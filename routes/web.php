<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\PermintaanController;

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

});
