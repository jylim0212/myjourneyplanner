<?php

namespace App\Services;

class MapService
{
    public function updateApiKey($apiKey)
    {
        // Update the .env file
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        $envContent = preg_replace(
            '/GOOGLE_MAPS_API_KEY=.*/',
            'GOOGLE_MAPS_API_KEY=' . $apiKey,
            $envContent
        );
        file_put_contents($envFile, $envContent);
    }
} 