<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('permintaans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('barang_id')->constrained('barangs')->onDelete('cascade');
    $table->string('nama_peminta');
    $table->string('nama_ruangan');
    $table->integer('jumlah');
    $table->string('status')->default('pending'); // pending, selesai, rejected
    $table->text('keterangan')->nullable(); // alasan jika ditolak
    $table->timestamps();
});

}


    public function down(): void
    {
        Schema::dropIfExists('permintaans');
    }
};
