<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Keranjang extends Model
{
    use HasFactory;

    protected $table = 'keranjang';

    protected $fillable = [
        'id_pelanggan',
        'id_produk',
        'jumlah',
        'harga_saat_dibeli',
        'subtotal_harga',
        'berat_satuan_saat_dibeli',
        'subtotal_berat',
    ];

    /**
     * Mendapatkan data pelanggan yang memiliki item keranjang ini.
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    /**
     * Mendapatkan data produk dari item keranjang ini.
     */
    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk', 'id');
    }
}
