<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class WeatherService
{
    protected $apiKey;
    protected $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openweather.api_key');
        $this->baseUrl = config('services.openweather.base_url');
    }

    public function getWeatherForecast($location, $startDate, $endDate)
    {
        $cacheKey = "weather_{$location}_{$startDate}_{$endDate}";
        
        // Check if we have cached data
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Get coordinates for the location
            $coordinates = $this->getCoordinates($location);
            if (!$coordinates) {
                \Log::error("Failed to get coordinates for location: {$location}");
                return null;
            }

            \Log::info("Getting weather forecast for location: {$location}", [
                'coordinates' => $coordinates,
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);

            // Get weather data
            $response = Http::withOptions([
                'verify' => false, // Disable SSL verification
                'timeout' => 30
            ])->get("{$this->baseUrl}/forecast", [
                'lat' => $coordinates['lat'],
                'lon' => $coordinates['lon'],
                'appid' => $this->apiKey,
                'units' => 'metric',
            ]);

            if (!$response->successful()) {
                \Log::error("Weather API request failed", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'location' => $location
                ]);
                return null;
            }

            $weatherData = $response->json();
            $forecasts = [];
            
            // Convert dates to timestamps for comparison
            $startTimestamp = strtotime($startDate);
            $endTimestamp = strtotime($endDate);
            
            // Group forecasts by date
            foreach ($weatherData['list'] as $item) {
                $itemDate = strtotime(date('Y-m-d', $item['dt']));
                
                // Only include forecasts between start and end date
                if ($itemDate >= $startTimestamp && $itemDate <= $endTimestamp) {
                    $date = date('Y-m-d', $itemDate);
                    if (!isset($forecasts[$date])) {
                        $forecasts[$date] = [
                            'temperature' => round($item['main']['temp']),
                            'description' => $item['weather'][0]['description'],
                            'icon' => $item['weather'][0]['icon'],
                            'humidity' => $item['main']['humidity'],
                            'wind_speed' => $item['wind']['speed'],
                            'date' => $date
                        ];
                    }
                }
            }

            // Sort forecasts by date
            ksort($forecasts);

            // Cache the result for 1 hour
            Cache::put($cacheKey, $forecasts, now()->addHour());

            \Log::info("Successfully retrieved weather forecast for {$location}", [
                'forecast_count' => count($forecasts)
            ]);

            return $forecasts;
        } catch (\Exception $e) {
            \Log::error("Error getting weather forecast for {$location}: " . $e->getMessage());
            return null;
        }
    }

    protected function getCoordinates($location)
    {
        try {
            \Log::info("Getting coordinates for location: {$location}");

            $response = Http::withOptions([
                'verify' => false, // Disable SSL verification
                'timeout' => 30
            ])->get("{$this->baseUrl}/weather", [
                'q' => $location . ',MY',
                'appid' => $this->apiKey,
            ]);

            if (!$response->successful()) {
                \Log::error("Coordinates API request failed", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'location' => $location
                ]);
                return null;
            }

            $data = $response->json();
            \Log::info("Successfully retrieved coordinates for {$location}", [
                'coordinates' => [
                    'lat' => $data['coord']['lat'],
                    'lon' => $data['coord']['lon']
                ]
            ]);

            return [
                'lat' => $data['coord']['lat'],
                'lon' => $data['coord']['lon'],
            ];
        } catch (\Exception $e) {
            \Log::error("Error getting coordinates for {$location}: " . $e->getMessage());
            return null;
        }
    }

    public function updateApiKey($apiKey)
    {
        config(['services.openweather.api_key' => $apiKey]);
    }
} 