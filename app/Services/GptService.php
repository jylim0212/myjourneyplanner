<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GptService
{
    protected $apiKey;
    protected $apiHost;
    protected $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.gpt.api_key');
        $this->apiHost = config('services.gpt.api_host');
        $this->apiUrl = config('services.gpt.api_url');
    }

    public function analyzeJourney($journey, $currentLocation)
    {
        try {
            $prompt = $this->buildPrompt($journey, $currentLocation);
            
            // Log the request for debugging
            \Log::info('GPT API Request:', [
                'url' => $this->apiUrl,
                'prompt' => $prompt
            ]);
            
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 60, // Increase timeout to 60 seconds
                'connect_timeout' => 30, // Add connection timeout
                'retry' => 2, // Add retry attempts
                'retry_delay' => 1000 // 1 second delay between retries
            ])
            ->withHeaders([
                'X-RapidAPI-Key' => $this->apiKey,
                'X-RapidAPI-Host' => $this->apiHost,
                'Content-Type' => 'application/json'
            ])
            ->post($this->apiUrl, [
                'messages' => [
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'web_access' => false
            ]);

            if (!$response->successful()) {
                \Log::error('GPT API Error Response:', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Failed to get GPT response: ' . $response->body());
            }

            $data = $response->json();
            
            // Log the response for debugging
            \Log::info('GPT API Response:', $data);
            
            // Return the raw response for now
            return $data;
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('GPT API Connection Error: ' . $e->getMessage());
            throw new \Exception('Connection to GPT API failed. Please try again later.');
        } catch (\Exception $e) {
            \Log::error('GPT API Error: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function buildPrompt($journey, $currentLocation)
    {
        $locations = $journey->locations->pluck('location')->toArray();
        $weatherService = new WeatherService();
        
        // Get weather data for each location
        $weatherData = [];
        foreach ($locations as $location) {
            try {
                $weatherData[$location] = $weatherService->getWeatherForecast(
                    $location,
                    $journey->start_date,
                    $journey->end_date
                );
            } catch (\Exception $e) {
                \Log::error("Weather data fetch failed for {$location}: " . $e->getMessage());
                $weatherData[$location] = "Weather data unavailable";
            }
        }

        return "I am planning a journey with the following details:
Journey Name: {$journey->journey_name}
Start Date: {$journey->start_date}
End Date: {$journey->end_date}
Current Location: {$currentLocation}
Preferred Events: {$journey->preferred_events}

Locations to Visit:
" . implode("\n", array_map(function($location) use ($weatherData) {
            $weather = isset($weatherData[$location]) ? json_encode($weatherData[$location]) : "Weather data unavailable";
            return "- {$location}\n  Weather Forecast: {$weather}";
        }, $locations)) . "

Please provide a user-friendly recommendation in this exact format:

🚗 TRAVEL SUMMARY
----------------
• Total Time: [time]
• Route: [route]
• Weather: [key weather points]

📅 ITINERARY
-----------
Day 1: [date]
------------
• Places: [top 2 places]
• Food: [best food spot]
• Tip: [key tip]

Day 2: [date]
------------
• Places: [top 2 places]
• Food: [best food spot]
• Tip: [key tip]

Day 3: [date]
------------
• Places: [top 2 places]
• Food: [best food spot]
• Tip: [key tip]

⚠️ IMPORTANT NOTES
-----------------
• Weather: [weather warning]
• Travel: [travel tip]
• Food: [food tip]

Keep it brief and easy to read. Use simple language and clear formatting.";
    }

    protected function getWeatherData($journey)
    {
        $weatherService = new WeatherService();
        $weatherData = [];
        
        foreach ($journey->locations as $location) {
            $forecast = $weatherService->getWeatherForecast(
                $location->location,
                $journey->start_date,
                $journey->end_date
            );
            
            if ($forecast) {
                $weatherData[] = "{$location->location}: " . json_encode($forecast);
            }
        }
        
        return implode("\n", $weatherData);
    }
} 