<?php

return [
    'origin' => [
        'name' => 'Gudang Utama JawaFurnitur',
        'address' => 'Jl. Industri Raya No. 1, Semarang, Jawa Tengah, 50118', // Ganti dengan alamat gudang Anda
        'latitude' => env('SHIPPING_ORIGIN_LATITUDE',  	-6.966667), // Ganti dengan Latitude gudang Anda (Contoh: Semarang)
        'longitude' => env('SHIPPING_ORIGIN_LONGITUDE', 110.416664), // Ganti dengan Longitude gudang Anda (Contoh: Semarang)
        'google_maps_api_key' => env('GOOGLE_MAPS_API_KEY', 'AIzaSyCHd9XCVcAs9VCll89ZbXXwTlWek3gLwEU'),

    ],

    // Aturan sederhana untuk ongkos kirim dan estimasi
    'cost_per_km' => 1500, // Contoh: Rp 1.500 per kilometer

    'delivery_estimation' => [
        'base_days' => 1, // Hari dasar pengiriman
        'km_per_extra_day' => 150, // Tambah 1 hari setiap 150km
        'max_days' => 10, // Estimasi maksimal pengiriman
    ],
];
