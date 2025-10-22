<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Barang extends Model
{
    use HasFactory;
    use SoftDeletes;

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
