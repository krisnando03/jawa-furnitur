<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Untuk membuat slug
use Illuminate\Support\Facades\Storage; // Untuk URL gambar

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk'; // Mendefinisikan nama tabel secara eksplisit

    protected $fillable = [
        'id_kategori',
        'nama_produk',
        'slug',
        'deskripsi_singkat',
        'deskripsi_lengkap',
        'harga',
        'stok',
        'gambar_produk', // Akan menyimpan path relatif ke file gambar di storage
        'warna',
        'berat',
    ];

    // Boot method untuk otomatis membuat slug
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($produk) {
            if (empty($produk->slug)) {
                $produk->slug = Str::slug($produk->nama_produk);
            }
        });

        static::updating(function ($produk) {
            if ($produk->isDirty('nama_produk') && empty($produk->getOriginal('slug'))) {
                $produk->slug = Str::slug($produk->nama_produk);
            } else if ($produk->isDirty('nama_produk') && $produk->slug === Str::slug($produk->getOriginal('nama_produk'))) {
                $produk->slug = Str::slug($produk->nama_produk);
            }
        });
    }

    /**
     * Mendefinisikan relasi many-to-one ke model Kategori.
     * Satu produk dimiliki oleh satu kategori.
     */
    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori');
    }

    /**
     * Accessor untuk mendapatkan URL lengkap gambar produk.
     * Pastikan Anda sudah menjalankan `php artisan storage:link`.
     * Gambar diasumsikan disimpan di `storage/app/public/nama_folder_gambar/nama_file.jpg`.
     * Field `gambar_produk` di database akan menyimpan `nama_folder_gambar/nama_file.jpg`.
     */
    public function getGambarProdukUrlAttribute()
    {
        if ($this->gambar_produk) {
            // Cek apakah path adalah path relatif untuk storage public (untuk gambar yang diupload admin)
            if (Str::startsWith($this->gambar_produk, 'produk/') && Storage::disk('public')->exists($this->gambar_produk)) {
                return Storage::disk('public')->url($this->gambar_produk);
            }
            // Cek apakah path adalah path yang bisa diakses langsung via asset() (untuk seeder atau gambar default di public)
            // Asumsi path dari seeder akan seperti 'assets/img/portfolio/namafile.jpg'
            if (file_exists(public_path($this->gambar_produk))) {
                return asset($this->gambar_produk);
            }
        }
        // Ganti dengan path ke gambar placeholder default Anda
        return asset('assets/img/placeholder_produk.jpg');
    }
}
