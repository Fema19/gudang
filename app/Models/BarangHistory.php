<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'barang_id',
        'type',
        'qty',
        'stok_before',
        'stok_after',
        'note',
    ];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
