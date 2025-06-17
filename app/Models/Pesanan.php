<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    use HasFactory;
    // Daftar konstanta status pesanan
    public const STATUS_MENUNGGU_PEMBAYARAN = 'menunggu_pembayaran';
    public const STATUS_DIPROSES            = 'diproses';
    public const STATUS_DIKIRIM             = 'dikirim';
    public const STATUS_SELESAI             = 'selesai';
    public const STATUS_DIBATALKAN          = 'dibatalkan';
    public const STATUS_GAGAL               = 'gagal';
    public const STATUS_VERIFIKASI          = 'verifikasi';

    // Jika ingin status khusus pembayaran gateway
    public const STATUS_PEMBAYARAN_BERHASIL_GATEWAY = 'diproses';
    public const STATUS_PEMBAYARAN_PENDING_GATEWAY  = 'menunggu_pembayaran';
    public const STATUS_PEMBAYARAN_GAGAL_GATEWAY    = 'gagal';

    /**
     * Nama tabel yang terkait dengan model.
     *
     * @var string
     */
    protected $table = 'pesanan'; // Sesuaikan jika nama tabel berbeda (konvensi Laravel: pesanans)

    /**
     * Atribut yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_pelanggan',
        'nomor_pesanan',
        'id_alamat_pengiriman',
        'id_voucher',
        'subtotal_produk',
        'diskon',
        'ongkos_kirim',
        'estimasi_pengiriman', // Tambahkan ini
        'total_pembayaran',
        'status_pesanan', // Contoh: menunggu_pembayaran, diproses, dikirim, selesai, dibatalkan
        'catatan_pembeli',
        'metode_pembayaran',
        'tanggal_pesanan',
        'tanggal_pengiriman',
        'nomor_resi',
        'snap_token',
        'payment_gateway_name',
        'payment_gateway_response',
        'waktu_pembayaran_gateway',
    ];

    /**
     * Atribut yang harus di-cast ke tipe natif.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'subtotal_produk' => 'decimal:2',
        'diskon' => 'decimal:2',
        'ongkos_kirim' => 'decimal:2',
        'total_pembayaran' => 'decimal:2',
        'tanggal_pesanan' => 'datetime',
        'tanggal_pengiriman' => 'datetime',
    ];

    /**
     * Mendapatkan pelanggan yang melakukan pesanan ini.
     */
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }

    /**
     * Mendapatkan alamat pengiriman untuk pesanan ini.
     */
    public function alamatPengiriman()
    {
        return $this->belongsTo(AlamatPengiriman::class, 'id_alamat_pengiriman');
    }

    /**
     * Mendapatkan voucher yang digunakan pada pesanan ini (jika ada).
     */
    public function voucher()
    {
        return $this->belongsTo(Voucher::class, 'id_voucher');
    }

    /**
     * Mendapatkan detail item untuk pesanan ini.
     */
    public function detailPesanan()
    {
        return $this->hasMany(DetailPesanan::class, 'id_pesanan');
    }

    public function kendaraanPengirim()
    {
        return $this->belongsTo(\App\Models\KendaraanPengirim::class, 'id_kendaraan_pengirim');
    }
}
