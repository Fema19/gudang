<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'stok',
        'satuan',
        'kategori',
        'foto',
    ];

    public function histories()
    {
        return $this->hasMany(BarangHistory::class)->orderBy('created_at', 'desc');
    }
}
