<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    Schema::table('permintaans', function (Blueprint $table) {
        $table->longText('tanda_tangan')->nullable()->after('jumlah');
    });
    }

    public function down(): void
    {
    Schema::table('permintaans', function (Blueprint $table) {
        $table->dropColumn('tanda_tangan');
    });
    }

};
