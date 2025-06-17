<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'detail_pesanan'; // Sesuaikan jika nama tabel berbeda (konvensi Laravel: detail_pesanans)

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_pesanan',
        'id_produk',
        'nama_produk_saat_order',
        'harga_satuan_saat_order',
        'jumlah',
        'subtotal', // subtotal untuk item ini (harga_satuan * jumlah)
    ];

    /**
     * Atribut yang harus di-cast ke tipe natif.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'harga_satuan_saat_order' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pesanan()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'id_produk');
    }
}
