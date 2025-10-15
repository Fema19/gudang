<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permintaans', function (Blueprint $table) {
            if (Schema::hasColumn('permintaans', 'barang_id')) {
                $table->dropColumn('barang_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('permintaans', function (Blueprint $table) {
            $table->unsignedBigInteger('barang_id')->nullable();
        });
    }
};

