<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client; // Tambahkan ini

class GoogleMapsService
{
    protected string $apiKey;
    protected string $geocodingUrl = 'https://maps.googleapis.com/maps/api/geocode/json';
    protected string $distanceMatrixUrl = 'https://maps.googleapis.com/maps/api/distancematrix/json';
    protected Client $client; // Deklarasikan tipe untuk $client

    public function __construct()
    {
        $this->apiKey = config('services.google_maps.api_key');
        // if (empty($this->apiKey)) {
        //     // Log::error('Google Maps API Key is not configured.');
        //     // Melempar exception lebih baik untuk masalah konfigurasi krusial
        //     throw new \Exception('Google Maps API Key is not configured. Please check your .env file or services config.');
        // }
        // Tambahkan 'verify' => false HANYA untuk pengembangan lokal jika ada masalah SSL
        // JANGAN GUNAKAN INI DI PRODUKSI
        $guzzleOptions = [
            'base_uri' => 'https://maps.googleapis.com/maps/api/',
        ];
        if (app()->environment('local')) { // Hanya nonaktifkan SSL verify di lokal
            $guzzleOptions['verify'] = false;
        }
        $this->client = new Client($guzzleOptions);
    }

    /**
     * Mengubah alamat menjadi koordinat geografis.
     *
     * @param string $address Alamat lengkap.
     * @return array|null Array berisi ['lat' => float, 'lng' => float] atau null jika gagal.
     */
    public function geocodeAddress(string $address): ?array
    {
        if (empty($this->apiKey)) return null;

        try {
            $response = Http::get($this->geocodingUrl, [
                'address' => $address,
                'key' => $this->apiKey,
            ]);

            if ($response->successful() && $response->json('status') === 'OK') {
                $location = $response->json('results.0.geometry.location');
                if ($location) {
                    return [
                        'lat' => $location['lat'],
                        'lng' => $location['lng'],
                    ];
                }
            } else {
                Log::error('Google Geocoding API error: ' . $response->json('status') . ' - ' . $response->json('error_message'), ['address' => $address]);
            }
        } catch (\Exception $e) {
            Log::error('Exception during Google Geocoding API call: ' . $e->getMessage(), ['address' => $address]);
        }
        return null;
    }

    /**
     * Menghitung jarak dan durasi antara titik asal dan tujuan.
     *
     * @param string $originLatLatitude Asal (misal, "-6.175392,106.827153" atau alamat)
     * @param string $destinationLatLatitude Tujuan (misal, "-6.200000,106.800000" atau alamat)
     * @return array|null Array berisi ['distance_km' => float, 'duration_text' => string, 'distance_meters' => int] atau null.
     */
    public function getDistanceAndDuration(string $origin, string $destination): ?array
    {
        if (empty($this->apiKey)) return null;

        try {
            $response = Http::get($this->distanceMatrixUrl, [
                'origins' => $origin,
                'destinations' => $destination,
                'key' => $this->apiKey,
                'units' => 'metric', // Untuk mendapatkan jarak dalam meter/kilometer
            ]);

            if ($response->successful() && $response->json('status') === 'OK') {
                $element = $response->json('rows.0.elements.0');
                if ($element && $element['status'] === 'OK') {
                    return [
                        'distance_meters' => $element['distance']['value'], // Jarak dalam meter
                        'distance_km' => round($element['distance']['value'] / 1000, 2), // Jarak dalam km
                        'duration_text' => $element['duration']['text'], // Durasi dalam format teks (misal, "1 hour 15 mins")
                        'duration_seconds' => $element['duration']['value'], // Durasi dalam detik
                    ];
                } else {
                    Log::error('Google Distance Matrix API element error: ' . ($element['status'] ?? 'Unknown'), ['origin' => $origin, 'destination' => $destination]);
                }
            } else {
                Log::error('Google Distance Matrix API error: ' . $response->json('status') . ' - ' . $response->json('error_message'), ['origin' => $origin, 'destination' => $destination]);
            }
        } catch (\Exception $e) {
            Log::error('Exception during Google Distance Matrix API call: ' . $e->getMessage(), ['origin' => $origin, 'destination' => $destination]);
        }
        return null;
    }
}
