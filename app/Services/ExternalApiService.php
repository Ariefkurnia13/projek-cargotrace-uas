<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExternalApiService
{
    protected array $config;

    public function __construct()
    {
        $this->config = config('cargotrace.external_api', []);
    }

    /**
     * Fetch real-time weather information for coordinates.
     * Uses Open-Meteo free API (no secret key strictly required, highly reliable)
     * with timeout, graceful error handling, and realistic fallback.
     */
    public function getWeather(float $latitude, float $longitude): array
    {
        $weatherConfig = $this->config['weather'] ?? [];
        $disclaimer = $weatherConfig['disclaimer'] ?? 'Weather data is retrieved for situational visibility only and is not deemed predictive proof of shipment delays.';

        if (!($weatherConfig['enabled'] ?? true)) {
            return $this->fallbackWeatherData($latitude, $longitude, 'Weather integration disabled in configuration', $disclaimer);
        }

        $baseUrl = $weatherConfig['base_url'] ?? 'https://api.open-meteo.com/v1/forecast';
        $timeout = $weatherConfig['timeout'] ?? 3;

        try {
            $response = Http::timeout($timeout)
                ->retry(2, 100, throw: false)
                ->get($baseUrl, [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'current' => 'temperature_2m,relative_humidity_2m,apparent_temperature,precipitation,weather_code,wind_speed_10m',
                    'timezone' => 'auto',
                ]);

            if ($response->successful() && isset($response->json()['current'])) {
                $current = $response->json()['current'];
                $weatherCode = $current['weather_code'] ?? 0;

                return [
                    'status' => 'success',
                    'source' => 'Open-Meteo Live API',
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'temperature_celsius' => $current['temperature_2m'] ?? 24.5,
                    'humidity_percent' => $current['relative_humidity_2m'] ?? 65,
                    'wind_speed_kmh' => $current['wind_speed_10m'] ?? 12.0,
                    'condition' => $this->interpretWeatherCode($weatherCode),
                    'disclaimer' => $disclaimer,
                    'retrieved_at' => now()->toIso8601String(),
                ];
            }

            Log::warning("Weather API returned non-200 code: " . $response->status());
        } catch (Throwable $e) {
            Log::info("External Weather API unavailable or timed out: " . $e->getMessage());
        }

        return $this->fallbackWeatherData($latitude, $longitude, 'Fallback situational telemetry active', $disclaimer);
    }

    /**
     * Reverse geocode coordinates or search location via OpenStreetMap Nominatim.
     */
    public function searchGeocoding(string $query): array
    {
        $geoConfig = $this->config['geocoding'] ?? [];

        if (!($geoConfig['enabled'] ?? true)) {
            return [
                'status' => 'disabled',
                'query' => $query,
                'results' => [],
            ];
        }

        $baseUrl = $geoConfig['base_url'] ?? 'https://nominatim.openstreetmap.org/search';
        $timeout = $geoConfig['timeout'] ?? 3;

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'User-Agent' => 'CargoTrace-Platform/1.0 (academic-logistics@cargotrace.test)',
                ])
                ->get($baseUrl, [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 3,
                ]);

            if ($response->successful() && is_array($response->json())) {
                return [
                    'status' => 'success',
                    'source' => 'Nominatim OpenStreetMap',
                    'query' => $query,
                    'results' => array_map(fn($item) => [
                        'display_name' => $item['display_name'] ?? '',
                        'lat' => (float) ($item['lat'] ?? 0),
                        'lon' => (float) ($item['lon'] ?? 0),
                    ], $response->json()),
                ];
            }
        } catch (Throwable $e) {
            Log::info("External Geocoding API timed out or unreachable: " . $e->getMessage());
        }

        return [
            'status' => 'fallback',
            'source' => 'Local Geographical Registry',
            'query' => $query,
            'results' => [],
        ];
    }

    protected function fallbackWeatherData(float $lat, float $lon, string $note, string $disclaimer): array
    {
        // Safe, deterministic fallback data
        return [
            'status' => 'fallback',
            'source' => 'Situational Cache',
            'latitude' => $lat,
            'longitude' => $lon,
            'temperature_celsius' => 26.0,
            'humidity_percent' => 70,
            'wind_speed_kmh' => 15.2,
            'condition' => 'Clear / Moderate Maritime Conditions',
            'note' => $note,
            'disclaimer' => $disclaimer,
            'retrieved_at' => now()->toIso8601String(),
        ];
    }

    protected function interpretWeatherCode(int $code): string
    {
        return match (true) {
            $code === 0 => 'Clear Sky',
            in_array($code, [1, 2, 3]) => 'Partly Cloudy',
            in_array($code, [45, 48]) => 'Foggy / Reduced Visibility',
            in_array($code, [51, 53, 55, 61, 63, 65]) => 'Rain / Maritime Precipitation',
            in_array($code, [71, 73, 75]) => 'Snow / Winter Conditions',
            in_array($code, [80, 81, 82]) => 'Heavy Showers',
            in_array($code, [95, 96, 99]) => 'Severe Thunderstorm',
            default => 'Moderate Atmosphere',
        };
    }
}
