<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesan extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'pesan';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_pelanggan',
        'pengirim_adalah_admin',
        'isi_pesan',
        'id_produk_konteks',
        'nomor_pesanan_konteks',
        'sudah_dibaca_oleh_pelanggan',
        'sudah_dibaca_oleh_admin',
    ];

    /**
     * Atribut yang harus di-cast ke tipe native.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'pengirim_adalah_admin' => 'boolean',
        'sudah_dibaca_oleh_pelanggan' => 'boolean',
        'sudah_dibaca_oleh_admin' => 'boolean',
    ];

    /**
     * Mendapatkan pelanggan yang memiliki pesan ini.
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    /**
     * Mendapatkan produk yang terkait dengan pesan ini (jika ada).
     */
    public function produkKonteks()
    {
        return $this->belongsTo(Produk::class, 'id_produk_konteks');
    }
}
