<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'vouchers'; // Sesuaikan jika nama tabel berbeda

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'kode',
        'nama_voucher',
        'deskripsi',
        'tipe_diskon', // 'persen' atau 'tetap'
        'nilai_diskon',
        'min_pembelian',
        'maks_diskon', // Untuk tipe persen, batas maksimal diskon
        'kuota',
        'digunakan', // Jumlah voucher yang sudah digunakan
        'tanggal_mulai',
        'tanggal_berakhir',
        'aktif', // boolean
    ];

    /**
     * Atribut yang harus di-cast ke tipe natif.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'nilai_diskon' => 'decimal:2',
        'min_pembelian' => 'decimal:2',
        'maks_diskon' => 'decimal:2',
        'tanggal_mulai' => 'datetime',
        'tanggal_berakhir' => 'datetime',
        'aktif' => 'boolean',
    ];
}
