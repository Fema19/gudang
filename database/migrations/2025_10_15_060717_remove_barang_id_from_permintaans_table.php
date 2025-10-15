<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaans', function (Blueprint $table) {
            // Cek dulu kalau foreign key-nya ada
            if (Schema::hasColumn('permintaans', 'barang_id')) {
                // Lepas dulu foreign key constraint-nya
                $table->dropForeign(['barang_id']);
                // Baru hapus kolomnya
                $table->dropColumn('barang_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permintaans', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable();

            // Kalau perlu, tambahkan lagi foreign key-nya
            $table->foreign('barang_id')->references('id')->on('barangs')->onDelete('cascade');
        });
    }
};


