<?php

namespace App\Services;

use App\Services\GoogleMapsService; // Import GoogleMapsService

class ShippingService
{
    protected $originLat;
    protected $originLon;
    protected $costPerKm;
    protected GoogleMapsService $googleMapsService;
    protected $deliveryBaseDays;
    protected $deliveryKmPerExtraDay;
    protected $deliveryMaxDays;

    public function __construct()
    {
        $this->originLat = config('shipping.origin.latitude');
        $this->originLon = config('shipping.origin.longitude');
        $this->costPerKm = config('shipping.cost_per_km');
        $this->googleMapsService = app(GoogleMapsService::class); // Resolve service
        $this->deliveryBaseDays = config('shipping.delivery_estimation.base_days');
        $this->deliveryKmPerExtraDay = config('shipping.delivery_estimation.km_per_extra_day');
        $this->deliveryMaxDays = config('shipping.delivery_estimation.max_days');
    }

    /**
     * Calculate distance between two points using Haversine formula.
     * Now uses Google Distance Matrix API via GoogleMapsService.
     * @return float Distance in kilometers.
     */
    public function calculateDistance(float $destLat, float $destLon): ?float
    {
        \Illuminate\Support\Facades\Log::info('ShippingService: Attempting to calculate distance.', [
            'originLat' => $this->originLat,
            'originLon' => $this->originLon,
            'destLat' => $destLat,
            'destLon' => $destLon,
        ]);

        if (empty($this->originLat) || empty($this->originLon) || empty($destLat) || empty($destLon)) {
            \Illuminate\Support\Facades\Log::warning('ShippingService: Koordinat tidak lengkap untuk kalkulasi jarak.');
            return null; // Tidak bisa menghitung jika koordinat tidak lengkap
        }

        $originString = $this->originLat . ',' . $this->originLon;
        $destinationString = $destLat . ',' . $destLon;

        \Illuminate\Support\Facades\Log::info('ShippingService: Calling GoogleMapsService->getDistanceAndDuration with:', ['origin' => $originString, 'destination' => $destinationString]);
        $distanceData = $this->googleMapsService->getDistanceAndDuration($originString, $destinationString);

        if ($distanceData && isset($distanceData['distance_km'])) {
            return (float) $distanceData['distance_km'];
        }
        return null; // Gagal mendapatkan jarak
        // Convert miles to kilometers
    }

    /**
     * Calculate shipping cost based on distance.
     * @param float $distance in kilometers.
     * @return float Shipping cost.
     */
    public function calculateShippingCost(?float $distance): float
    {
        if ($distance === null || $distance < 0) return 0;
        $cost = round($distance * $this->costPerKm);
        // \Illuminate\Support\Facades\Log::info('ShippingService - Calculated Cost:', ['distance' => $distance, 'cost_per_km' => $this->costPerKm, 'cost' => $cost]); // Untuk debug
        return $cost;
    }

    /**
     * Estimate delivery time based on distance.
     * @param float $distance in kilometers.
     * @return string Estimated delivery time (e.g., "1-2 hari").
     */
    public function estimateDeliveryTime(?float $distance): string
    {
        if ($distance === null || $distance < 0) return "Estimasi tidak tersedia";
        if ($distance == 0) return "1-2 hari"; // Misal untuk jarak sangat dekat
        $days = $this->deliveryBaseDays;
        if ($this->deliveryKmPerExtraDay > 0 && $distance > 0) {
            $extraDays = ceil($distance / $this->deliveryKmPerExtraDay);
            $days += $extraDays - 1; // -1 karena base day sudah dihitung
        }

        $days = min($days, $this->deliveryMaxDays); // Batasi estimasi maksimal
        $days = max($days, $this->deliveryBaseDays); // Pastikan minimal base days

        if ($days == $this->deliveryBaseDays) {
            return "{$days}-" . ($days + 1) . " hari";
        }
        return "{$days}-" . ($days + 2) . " hari"; // Beri rentang lebih jika lebih lama
    }
}
