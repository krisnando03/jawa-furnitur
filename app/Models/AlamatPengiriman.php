<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlamatPengiriman extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'alamat_pengiriman'; // Sesuaikan jika nama tabel berbeda

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_pelanggan',
        'nama_penerima',
        'nomor_telepon',
        'label_alamat', // Contoh: Rumah, Kantor
        'alamat_lengkap',
        'kota',
        'provinsi',
        'kode_pos',
        'latitude', // Tambahkan ini
        'longitude', // Tambahkan ini
        'is_utama', // boolean, menandakan alamat utama
    ];

    /**
     * Atribut yang harus di-cast ke tipe natif.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_utama' => 'boolean',
    ];

    /**
     * Mendapatkan pelanggan yang memiliki alamat ini.
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }
}
