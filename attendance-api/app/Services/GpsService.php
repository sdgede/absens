<?php

namespace App\Services;

class GpsService
{
    private const EARTH_RADIUS_METERS = 6371000;

    /**
     * Hitung jarak antara dua titik koordinat menggunakan formula Haversine.
     * Return jarak dalam meter.
     */
    public function haversineDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos($lat1Rad) * cos($lat2Rad) * sin($deltaLng / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Cek apakah user berada dalam radius yang diizinkan dari lokasi branch.
     */
    public function isWithinRadius(
        float $userLat,
        float $userLng,
        float $branchLat,
        float $branchLng,
        int $radius
    ): bool {
        $distance = $this->haversineDistance($userLat, $userLng, $branchLat, $branchLng);

        return $distance <= $radius;
    }

    /**
     * Kembalikan jarak aktual dalam meter (untuk pesan error).
     */
    public function distanceTo(
        float $userLat,
        float $userLng,
        float $branchLat,
        float $branchLng
    ): int {
        return (int) round($this->haversineDistance($userLat, $userLng, $branchLat, $branchLng));
    }
}
