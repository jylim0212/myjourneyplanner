<?php

namespace App\Http\Controllers\Admin;

use App\Models\WeatherApiSetting;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WeatherController extends Controller
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    public function index()
    {
        return view('admin.weather.index');
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'api_key' => 'required|string'
            ]);

            WeatherApiSetting::updateOrCreate([], ['api_key' => $request->input('api_key')]);

            return redirect()->route('admin.weather.index')
                ->with('success', 'Weather API configuration updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update Weather API configuration: ' . $e->getMessage());
            return redirect()->route('admin.weather.index')
                ->with('error', 'Failed to update Weather API configuration. Please try again.');
        }
    }
} 