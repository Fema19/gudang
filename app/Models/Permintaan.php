<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permintaan extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_peminta',
        'nama_ruangan',
        'keterangan',
        'status',
    ];

    // Relasi ke tabel permintaan_items
    public function items()
{
    return $this->hasMany(\App\Models\PermintaanItem::class, 'permintaan_id');
}
}
