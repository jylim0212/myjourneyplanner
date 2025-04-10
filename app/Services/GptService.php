<?php

namespace App\Services;

use App\Models\GptApiSetting;
use App\Models\Journey;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GptService
{
    protected function getApiConfig()
    {
        return GptApiSetting::getApiConfig();
    }

    protected function getHttpClient()
    {
        $sslCertPath = base_path('cacert.pem');
        
        if (file_exists($sslCertPath)) {
            return Http::withOptions([
                'verify' => $sslCertPath,
                'curl' => [
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2
                ]
            ]);
        }
        
        // If no local cert, try using system default
        return Http::withOptions([
            'verify' => true
        ]);
    }

    public function updateApiConfig($host, $url)
    {
        $setting = GptApiSetting::where('is_active', true)->first();
        if (!$setting) {
            $setting = new GptApiSetting();
            $setting->is_active = true;
        }
        
        $setting->api_host = $host;
        $setting->api_url = $url;
        $setting->save();
    }

    public function updateApiKey($apiKey)
    {
        $setting = GptApiSetting::where('is_active', true)->first();
        if (!$setting) {
            $setting = new GptApiSetting();
            $setting->is_active = true;
        }
        
        $setting->api_key = $apiKey;
        $setting->save();
    }

    public function analyzeJourney($journey, $currentLocation, $customQuestion = null)
    {
        try {
            Log::debug('Starting journey analysis', [
                'journey_id' => $journey->id,
                'journey_name' => $journey->journey_name,
                'start_date' => $journey->start_date,
                'end_date' => $journey->end_date,
                'timestamp' => now()
            ]);

            // Validate required journey data
            if (empty($journey->journey_name)) {
                Log::error('Journey Analysis Error: Missing journey name', [
                    'journey_id' => $journey->id
                ]);
                throw new \Exception('Journey name is required');
            }

            if (empty($journey->locations)) {
                Log::error('Journey Analysis Error: No locations provided');
                throw new \Exception('At least one location is required for journey analysis');
            }

            // Get API configuration
            $config = $this->getApiConfig();
            Log::debug('Retrieved API configuration', [
                'has_api_key' => !empty($config['api_key']),
                'has_api_host' => !empty($config['api_host']),
                'has_api_url' => !empty($config['api_url'])
            ]);

            if (empty($config['api_key'])) {
                throw new \Exception('GPT API key not configured');
            }

            $prompt = $this->buildPrompt($journey, $currentLocation, $customQuestion);
            Log::debug('Built prompt for journey analysis', [
                'prompt_length' => strlen($prompt),
                'has_weather_data' => strpos($prompt, 'Weather Forecast:') !== false,
                'has_local_events' => strpos($prompt, 'Local Events:') !== false
            ]);
            
            // Log the request data (excluding sensitive info)
            Log::info('Journey Analysis Request', [
                'journey_id' => $journey->id,
                'current_location' => $currentLocation,
                'timestamp' => now(),
                'request_url' => $config['api_url']
            ]);

            // Make the API request using our configured HTTP client
            Log::debug('Sending request to GPT API', ['timestamp' => now()]);
            $response = $this->getHttpClient()
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'X-RapidAPI-Key' => $config['api_key'],
                    'X-RapidAPI-Host' => $config['api_host']
                ])
                ->post($config['api_url'], [
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => $prompt
                        ]
                    ]
                ]);

            Log::debug('Received response from GPT API', [
                'status_code' => $response->status(),
                'response_length' => strlen($response->body()),
                'timestamp' => now()
            ]);

            // Log the raw response for debugging
            Log::debug('GPT API Raw Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                Log::error('GPT API request failed', [
                    'status_code' => $response->status(),
                    'error_message' => $response->body(),
                    'timestamp' => now()
                ]);
                throw new \Exception('Failed to get response from GPT API: ' . $response->status());
            }

            $data = $response->json();
            Log::debug('Parsed JSON response', [
                'has_result' => isset($data['result']),
                'has_text' => isset($data['text']),
                'has_bot' => isset($data['bot']),
                'has_message' => isset($data['message']),
                'timestamp' => now()
            ]);
            
            // Extract the result from the RapidAPI response
            if (isset($data['bot'])) {
                return $data['bot'];
            } elseif (isset($data['text'])) {
                return $data['text'];
            } elseif (isset($data['result'])) {
                return $data['result'];
            } elseif (isset($data['message'])) {
                return $data['message'];
            }

            Log::error('Invalid response format from RapidAPI', [
                'available_keys' => array_keys($data),
                'timestamp' => now()
            ]);
            throw new \Exception('Invalid response format from RapidAPI');

        } catch (\Exception $e) {
            Log::error('Journey Analysis Error', [
                'message' => $e->getMessage(),
                'journey_id' => $journey->id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'timestamp' => now()
            ]);
            throw $e;
        }
    }

    private function buildPrompt($journey, $currentLocation, $customQuestion = null)
    {
        $prompt = "Journey Details:\n";
        $prompt .= "Title: " . $journey->journey_name . "\n";
        $prompt .= "Start Date: " . $journey->start_date . "\n";
        $prompt .= "End Date: " . $journey->end_date . "\n";
        $prompt .= "Current Location: " . $currentLocation . "\n";
        
        if ($journey->preferred_events) {
            $prompt .= "Preferred Events: " . $journey->preferred_events . "\n";
        }
        $prompt .= "\n";
        
        // Add locations with weather data
        $prompt .= "Locations and Weather Forecast:\n";
        foreach ($journey->locations as $location) {
            $prompt .= "\nLocation: " . $location->location;
            
            // Log weather data for debugging
            Log::debug('Weather data for location', [
                'location' => $location->location,
                'has_weather_data' => !empty($location->weather_data),
                'weather_data' => $location->weather_data ?? null
            ]);
            
            // Add weather data if available
            if (!empty($location->weather_data)) {
                $prompt .= "\nWeather Forecast:";
                foreach ($location->weather_data as $date => $data) {
                    $prompt .= "\n- " . $date . ": ";
                    $prompt .= $data['description'];
                    $prompt .= ", Temperature: " . $data['temperature'] . "°C";
                    $prompt .= ", Humidity: " . $data['humidity'] . "%";
                    $prompt .= ", Wind Speed: " . $data['wind_speed'] . " m/s";
                    
                    // Log each day's weather data
                    Log::debug('Daily weather data', [
                        'location' => $location->location,
                        'date' => $date,
                        'data' => $data
                    ]);
                }
            } else {
                Log::warning('No weather data available for location', [
                    'location' => $location->location,
                    'journey_id' => $journey->id
                ]);
            }
            $prompt .= "\n";
        }

        // Use custom question if provided, otherwise use default from settings
        $question = $customQuestion ?: GptApiSetting::getDefaultQuestion();
        $prompt .= "\nQuestion: " . $question;

        return $prompt;
    }

    private function getWeatherData($journey)
    {
        $weatherData = [];
        foreach ($journey->locations as $location) {
            if (!empty($location->weather_data)) {
                $weatherData[] = $location->location . ": " . json_encode($location->weather_data);
            }
        }
        
        return implode("\n", $weatherData);
    }
}