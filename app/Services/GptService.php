<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GptService
{
    public function updateApiKey($apiKey)
    {
        // Update the .env file
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        $envContent = preg_replace(
            '/GPT_API_KEY=.*/',
            'GPT_API_KEY=' . $apiKey,
            $envContent
        );
        file_put_contents($envFile, $envContent);
    }

    public function updateApiConfig($apiHost, $apiUrl)
    {
        // Update the .env file
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        // Update API Host
        $envContent = preg_replace(
            '/GPT_API_HOST=.*/',
            'GPT_API_HOST=' . $apiHost,
            $envContent
        );
        
        // Update API URL
        $envContent = preg_replace(
            '/GPT_API_URL=.*/',
            'GPT_API_URL=' . $apiUrl,
            $envContent
        );
        
        file_put_contents($envFile, $envContent);
    }

    public function updateQuestions($defaultQuestion, $followUpQuestions)
    {
        // Update the .env file
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        // Update default question
        $envContent = preg_replace(
            '/GPT_DEFAULT_QUESTION=.*/',
            'GPT_DEFAULT_QUESTION=' . str_replace('"', '\\"', $defaultQuestion),
            $envContent
        );
        
        // Update follow-up questions
        $followUpQuestionsStr = implode('|', array_map(function($q) {
            return str_replace('"', '\\"', $q);
        }, $followUpQuestions));
        
        $envContent = preg_replace(
            '/GPT_FOLLOW_UP_QUESTIONS=.*/',
            'GPT_FOLLOW_UP_QUESTIONS=' . $followUpQuestionsStr,
            $envContent
        );
        
        file_put_contents($envFile, $envContent);
    }

    public function analyzeJourney($journey, $currentLocation, $customQuestion = null)
    {
        try {
            $question = $customQuestion ?? config('services.gpt.default_question');
            
            // Prepare the prompt
            $prompt = $this->preparePrompt($journey, $question);
            
            // Make the API call
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.gpt.api_key'),
                'Content-Type' => 'application/json'
            ])->post(config('services.gpt.api_url'), [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant that analyzes travel journeys and provides recommendations.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'temperature' => 0.7
            ]);

            if (!$response->successful()) {
                Log::error('GPT API Error: ' . $response->body());
                throw new \Exception('Failed to get response from GPT API');
            }

            $result = $response->json();
            $analysis = $result['choices'][0]['message']['content'];

            // Add follow-up questions if configured
            $followUpQuestions = config('services.gpt.follow_up_questions', []);
            if (!empty($followUpQuestions)) {
                $analysis .= "\n\nFollow-up Questions:\n";
                foreach ($followUpQuestions as $index => $question) {
                    $analysis .= ($index + 1) . ". " . $question . "\n";
                }
            }

            return $analysis;
        } catch (\Exception $e) {
            Log::error('GPT Analysis Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function preparePrompt($journey, $question)
    {
        $prompt = "Journey Details:\n";
        $prompt .= "Title: " . $journey->title . "\n";
        $prompt .= "Description: " . $journey->description . "\n";
        $prompt .= "Start Date: " . $journey->start_date . "\n";
        $prompt .= "End Date: " . $journey->end_date . "\n\n";
        $prompt .= "Locations:\n";
        
        foreach ($journey->locations as $location) {
            $prompt .= "- " . $location->name . " (" . $location->latitude . ", " . $location->longitude . ")\n";
        }
        
        $prompt .= "\nQuestion: " . $question;
        
        return $prompt;
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