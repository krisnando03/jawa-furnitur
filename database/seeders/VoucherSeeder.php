<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Voucher; // Pastikan model Voucher di-import
use Carbon\Carbon; // Untuk manipulasi tanggal

class VoucherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Voucher::create([
            'kode' => 'HEMAT10',
            'nama_voucher' => 'Diskon Hemat 10%',
            'deskripsi' => 'Dapatkan potongan harga 10% untuk semua produk tanpa minimum pembelian.',
            'tipe_diskon' => 'persen',
            'nilai_diskon' => 10,
            'min_pembelian' => 0,
            'maks_diskon' => 50000, // Contoh batas maksimal diskon Rp 50.000
            'kuota' => 100,
            'digunakan' => 0,
            'tanggal_mulai' => Carbon::now(),
            'tanggal_berakhir' => Carbon::now()->addMonths(3),
            'aktif' => true,
        ]);

        Voucher::create([
            'kode' => 'POTONG50K',
            'nama_voucher' => 'Potongan Langsung Rp 50.000',
            'deskripsi' => 'Potongan Rp 50.000 untuk minimal pembelian Rp 200.000.',
            'tipe_diskon' => 'tetap',
            'nilai_diskon' => 50000,
            'min_pembelian' => 200000,
            'maks_diskon' => null, // Tidak ada batas maksimal untuk tipe tetap
            'kuota' => 50,
            'digunakan' => 0,
            'tanggal_mulai' => Carbon::now(),
            'tanggal_berakhir' => Carbon::now()->addMonths(2),
            'aktif' => true,
        ]);

        Voucher::create([
            'kode' => 'ONGKIRGRATIS',
            'nama_voucher' => 'Gratis Ongkir',
            'deskripsi' => 'Gratis ongkos kirim dengan minimal pembelian Rp 150.000 (Maks. Potongan Ongkir Rp 20.000).',
            'tipe_diskon' => 'tetap', // Anggap sebagai potongan tetap untuk ongkir
            'nilai_diskon' => 20000, // Maksimal potongan ongkir
            'min_pembelian' => 150000,
            'maks_diskon' => 20000, // Sama dengan nilai diskon karena ini untuk ongkir
            'kuota' => 200,
            'digunakan' => 0,
            'tanggal_mulai' => Carbon::now()->subDays(10), // Voucher yang sudah dimulai
            'tanggal_berakhir' => Carbon::now()->addDays(20),
            'aktif' => true,
        ]);

        // Tambahkan voucher lain sesuai kebutuhan
        // Contoh voucher tidak aktif atau sudah kadaluarsa untuk testing
        // ...
    }
}
