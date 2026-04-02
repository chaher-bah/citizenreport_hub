<?php
/**
 * Geo Service
 * Handles geolocation and coordinate validation
 */

class GeoService
{
    /**
     * Validate latitude
     */
    public function validateLatitude($latitude): bool
    {
        if (!is_numeric($latitude)) {
            return false;
        }
        
        $lat = (float) $latitude;
        return $lat >= -90 && $lat <= 90;
    }

    /**
     * Validate longitude
     */
    public function validateLongitude($longitude): bool
    {
        if (!is_numeric($longitude)) {
            return false;
        }
        
        $lng = (float) $longitude;
        return $lng >= -180 && $lng <= 180;
    }

    /**
     * Validate coordinates
     */
    public function validateCoordinates($latitude, $longitude): bool
    {
        return $this->validateLatitude($latitude) && $this->validateLongitude($longitude);
    }

    /**
     * Sanitize coordinates
     */
    public function sanitizeCoordinates($latitude, $longitude): array
    {
        $lat = (float) $latitude;
        $lng = (float) $longitude;
        
        // Clamp to valid ranges
        $lat = max(-90, min(90, $lat));
        $lng = max(-180, min(180, $lng));
        
        return [
            'latitude' => $lat,
            'longitude' => $lng
        ];
    }

    /**
     * Format coordinates for display
     */
    public function formatCoordinates(float $latitude, float $longitude): string
    {
        $latDir = $latitude >= 0 ? 'N' : 'S';
        $lngDir = $longitude >= 0 ? 'E' : 'W';
        
        return sprintf(
            '%.6f° %s, %.6f° %s',
            abs($latitude),
            $latDir,
            abs($longitude),
            $lngDir
        );
    }

    /**
     * Generate Google Maps URL
     */
    public function getGoogleMapsUrl(float $latitude, float $longitude): string
    {
        return sprintf(
            'https://www.google.com/maps?q=%.6f,%.6f',
            $latitude,
            $longitude
        );
    }

    /**
     * Generate OpenStreetMap URL
     */
    public function getOpenStreetMapUrl(float $latitude, float $longitude): string
    {
        return sprintf(
            'https://www.openstreetmap.org/?mlat=%.6f&mlon=%.6f#map=16/%.6f/%.6f',
            $latitude,
            $longitude,
            $latitude,
            $longitude
        );
    }

    /**
     * Calculate distance between two points (Haversine formula)
     * Returns distance in kilometers
     */
    public function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6371; // Earth's radius in kilometers
        
        $latFrom = deg2rad($lat1);
        $latTo = deg2rad($lat2);
        $lngFrom = deg2rad($lng1);
        $lngTo = deg2rad($lng2);
        
        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;
        
        $a = sin($latDelta / 2) ** 2 +
             cos($latFrom) * cos($latTo) *
             sin($lngDelta / 2) ** 2;
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return $earthRadius * $c;
    }

    /**
     * Check if coordinates are within a radius of a point
     */
    public function isWithinRadius(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2,
        float $radiusKm
    ): bool {
        return $this->calculateDistance($lat1, $lng1, $lat2, $lng2) <= $radiusKm;
    }

    /**
     * Get coordinates from browser geolocation (client-side)
     * This returns JavaScript code to be executed in the browser
     */
    public function getGeolocationScript(): string
    {
        return <<<JS
        function getCurrentLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('Geolocation is not supported by your browser'));
                    return;
                }
                
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        resolve({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: position.coords.accuracy
                        });
                    },
                    (error) => {
                        reject(error);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }
        JS;
    }
}
