<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'notifikasi';

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_pelanggan',
        'tipe_notifikasi',
        'judul',
        'pesan',
        'link_aksi',
        'id_pesanan_terkait',
        'sudah_dibaca',
        'dibaca_pada',
    ];

    /**
     * Atribut yang harus di-cast ke tipe native.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'sudah_dibaca' => 'boolean',
        'dibaca_pada' => 'datetime',
    ];

    /**
     * Mendapatkan pelanggan yang memiliki notifikasi ini.
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    /**
     * Mendapatkan pesanan yang terkait dengan notifikasi ini (jika ada).
     */
    public function pesananTerkait()
    {
        return $this->belongsTo(Pesanan::class, 'id_pesanan_terkait');
    }
}
