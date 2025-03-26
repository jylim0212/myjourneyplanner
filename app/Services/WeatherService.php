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

        // Get coordinates for the location
        $coordinates = $this->getCoordinates($location);
        if (!$coordinates) {
            return null;
        }

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

        return $forecasts;
    }

    protected function getCoordinates($location)
    {
        $response = Http::withOptions([
            'verify' => false, // Disable SSL verification
            'timeout' => 30
        ])->get("{$this->baseUrl}/weather", [
            'q' => $location . ',MY',
            'appid' => $this->apiKey,
        ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();
        return [
            'lat' => $data['coord']['lat'],
            'lon' => $data['coord']['lon'],
        ];
    }
} 