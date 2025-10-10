<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permintaan extends Model
{
    use HasFactory;

    protected $fillable = [
    'barang_id',
    'nama_peminta',
    'nama_ruangan',
    'jumlah',
    'status',
    'keterangan',
];


    // Relasi ke Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
