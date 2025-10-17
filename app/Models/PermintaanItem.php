<?php

// app/Models/PermintaanItem.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermintaanItem extends Model
{
   protected $fillable = [
    'permintaan_id',
    'barang_id',
    'jumlah',
    'catatan',
];


    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function permintaan()
    {
        return $this->belongsTo(Permintaan::class);
    }
}
