<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\MapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MapController extends Controller
{
    protected $mapService;

    public function __construct(MapService $mapService)
    {
        $this->mapService = $mapService;
    }

    public function index()
    {
        return view('admin.map.index');
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'api_key' => 'required|string'
            ]);

            // Update the API key in the .env file
            $this->mapService->updateApiKey($request->api_key);

            return redirect()->route('admin.map.index')
                ->with('success', 'Google Maps API key updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update Google Maps API key: ' . $e->getMessage());
            return redirect()->route('admin.map.index')
                ->with('error', 'Failed to update Google Maps API key. Please try again.');
        }
    }
} 