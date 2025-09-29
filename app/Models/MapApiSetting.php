<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MapApiSetting extends Model
{
    protected $fillable = ['api_key'];

    public static function getApiConfig()
    {
        $setting = self::first();
        return [
            'api_key' => $setting ? $setting->api_key : config('services.map.api_key'),
            'api_host' => config('services.map.api_host'),
            'api_url' => config('services.map.api_url')
        ];
    }
}
