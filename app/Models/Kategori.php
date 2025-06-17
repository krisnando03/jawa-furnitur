<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // Untuk membuat slug

class Kategori extends Model
{
    use HasFactory;

    protected $table = 'kategori'; // Mendefinisikan nama tabel secara eksplisit

    protected $fillable = [
        'nama_kategori',
        'slug',
    ];

    // Boot method untuk otomatis membuat slug saat menyimpan atau memperbarui nama_kategori
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($kategori) {
            if (empty($kategori->slug)) {
                $kategori->slug = Str::slug($kategori->nama_kategori);
            }
        });

        static::updating(function ($kategori) {
            if ($kategori->isDirty('nama_kategori') && empty($kategori->getOriginal('slug'))) {
                // Hanya update slug jika nama_kategori berubah DAN slug sebelumnya kosong (atau tidak diisi manual)
                $kategori->slug = Str::slug($kategori->nama_kategori);
            } else if ($kategori->isDirty('nama_kategori') && $kategori->slug === Str::slug($kategori->getOriginal('nama_kategori'))) {
                // Jika slug sebelumnya adalah hasil generate otomatis dari nama_kategori lama, update slug
                $kategori->slug = Str::slug($kategori->nama_kategori);
            }
        });
    }

    /**
     * Mendefinisikan relasi one-to-many ke model Produk.
     * Satu kategori bisa memiliki banyak produk.
     */
    public function produk()
    {
        return $this->hasMany(Produk::class, 'id_kategori');
    }
}
