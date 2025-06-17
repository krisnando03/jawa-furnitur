<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Kategori;
use App\Models\Produk;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class ProdukSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID'); // Menggunakan Faker untuk data acak
        $kategoriList = Kategori::all();

        // Daftar gambar dummy yang akan kita gunakan berulang
        $gambarDummy = [
            'assets/img/product/cabinet/2.png',
            'assets/img/product/cabinet/3.png',
            'assets/img/product/chair/2.png',
            'assets/img/product/chair/3.png',
            'assets/img/product/rak/1.png',
            'assets/img/product/rak/2.png',
            'assets/img/product/table/1.png',
            'assets/img/product/table/2.png',
            'assets/img/product/table/3.png',

        ];
        $gambarIndex = 0;

        $warnaPilihan = ['Merah', 'Biru', 'Hijau', 'Hitam', 'Putih', 'Coklat', 'Abu-abu', 'Kuning'];

        foreach ($kategoriList as $kategori) {
            for ($i = 1; $i <= 2; $i++) { // Membuat 2 produk per kategori
                $namaProduk = $kategori->nama_kategori . ' ' . $faker->words(2, true) . ' ' . $i;

                // Ambil gambar dari daftar dummy secara berurutan
                $gambarProdukPath = $gambarDummy[$gambarIndex % count($gambarDummy)];
                $gambarIndex++;

                Produk::create([
                    'id_kategori' => $kategori->id,
                    'nama_produk' => Str::title($namaProduk),
                    // Slug akan otomatis terisi oleh boot method di model Produk
                    'deskripsi_singkat' => $faker->sentence(10),
                    'deskripsi_lengkap' => $faker->paragraphs(3, true),
                    'harga' => $faker->numberBetween(100000, 5000000),
                    'stok' => $faker->numberBetween(5, 50),
                    'gambar_produk' => $gambarProdukPath,
                    'warna' => $faker->randomElement($warnaPilihan),
                    'berat' => $faker->randomFloat(2, 0.5, 5), // Berat acak dalam KG

                ]);
            }
        }
    }
}
