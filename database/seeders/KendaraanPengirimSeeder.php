<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KendaraanPengirim;

class KendaraanPengirimSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        KendaraanPengirim::insert([
            [
                'type' => 'Mobil',
                'plate_number' => 'B 1234 XYZ',
                'driver_name' => 'Pak Budi',
                'driver_phone' => '081234567890',
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'Motor',
                'plate_number' => 'D 5678 ABC',
                'driver_name' => 'Pak Joko',
                'driver_phone' => '082345678901',
                'status' => 'available',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
