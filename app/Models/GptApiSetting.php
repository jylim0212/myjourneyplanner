<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GptApiSetting extends Model
{
    protected $fillable = [
        'default_question',
        'api_key',
        'api_host',
        'api_url',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public static function getDefaultQuestion()
    {
        $setting = self::where('is_active', true)->first();
        return $setting ? $setting->default_question : 'Please analyze this journey and provide recommendations.';
    }

    public static function getApiConfig()
    {
        $setting = self::where('is_active', true)->first();
        return [
            'api_key' => $setting ? $setting->api_key : config('services.gpt.api_key'),
            'api_host' => $setting ? $setting->api_host : config('services.gpt.api_host'),
            'api_url' => $setting ? $setting->api_url : config('services.gpt.api_url'),
        ];
    }
}
