<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kategori; // Pastikan model Kategori di-import
use Illuminate\Support\Str; // Untuk membuat slug

class KategoriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $kategoriData = [
            'Cabinet',
            'Chair',
            'Rak',
            'Sofa',
            'Table',
        ];

        foreach ($kategoriData as $nama) {
            Kategori::create([
                'nama_kategori' => $nama,
                // Slug akan otomatis terisi oleh boot method di model Kategori
            ]);
        }
    }
}
