<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Journey;
use App\Models\JourneyLocation;
use App\Models\Recommendation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\WeatherService;
use App\Services\GptService;

class JourneyController extends Controller
{
    protected $weatherService;
    protected $gptService;

    public function __construct(WeatherService $weatherService, GptService $gptService)
    {
        $this->weatherService = $weatherService;
        $this->gptService = $gptService;
    }

    // Show the list of journeys for the logged-in user
    public function index()
    {
        $journeys = Journey::where('user_id', Auth::id())->with('locations')->get();
        
        // Get weather data and safety status for each journey
        foreach ($journeys as $journey) {
            $weatherData = [];
            $isSafe = true;
            $weatherWarnings = [];

            foreach ($journey->locations as $location) {
                // Retrieve forecasts from DB within journey's date range
                $forecasts = \App\Models\WeatherForecast::where('journey_id', $journey->id)
                    ->where('location', $location->location)
                    ->whereBetween('forecast_date', [$journey->start_date, $journey->end_date])
                    ->orderBy('forecast_date')
                    ->get();

                foreach ($forecasts as $forecast) {
                    // Check for severe weather conditions
                    if (isset($forecast->raw_data['weather_id'])) {
                        $weatherId = $forecast->raw_data['weather_id'];
                        if ($weatherId < 800) {
                            $isSafe = false;
                            $weatherWarnings[] = $forecast->description . ' at ' . $location->location . ' on ' . $forecast->forecast_date->format('Y-m-d');
                        }
                    }
                }
                $weatherData[$location->location] = $forecasts;
            }
            $journey->weather_data = $weatherData;
            $journey->weather_safety = [
                'is_safe' => $isSafe,
                'warnings' => $weatherWarnings
            ];
        }

        return view('journey.index', compact('journeys'));
    }

    // Show the form to create a journey
    public function create()
    {
        return view('journey.create');
    }

    // Store a new journey
    public function store(Request $request)
    {
        $request->validate([
            'journey_name' => 'required|string|max:255',
            'starting_location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'preferred_events' => 'nullable|string',
            'locations' => 'required|array|min:1', // Ensure at least one location
            'locations.*' => 'required|string|max:255' // Validate each location
        ]);

        // Create the journey
        $journey = Journey::create([
            'user_id' => auth()->id(),
            'journey_name' => $request->journey_name,
            'starting_location' => $request->starting_location,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'preferred_events' => $request->preferred_events,
        ]);

        // Store locations
        foreach ($request->locations as $location) {
            JourneyLocation::create([
                'journey_id' => $journey->id,
                'location' => $location,
            ]);
        }

        // Fetch and save weather forecasts for each location
        foreach ($request->locations as $location) {
            // Purge old forecasts for this journey/location outside the new date range
            \App\Models\WeatherForecast::where('journey_id', $journey->id)
                ->where('location', $location)
                ->where(function ($query) use ($journey) {
                    $query->where('forecast_date', '<', $journey->start_date)
                          ->orWhere('forecast_date', '>', $journey->end_date);
                })
                ->delete();
            // Fetch and save new forecasts for the current date range
            $this->weatherService->getWeatherForecast($location, $journey->start_date, $journey->end_date, $journey);
        }

        return redirect()->route('journey.index')->with('success', 'Journey created successfully!');
    }


    // Show the edit form for a specific journey
    public function edit($id)
    {
        $journey = Journey::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return view('journey.edit', compact('journey'));
    }

    // Update the journey details
    public function update(Request $request, $id)  // Use explicit $id instead of Journey model binding
    {
        \Log::info('Update method called', [
            'method' => $request->method(),
            'all_data' => $request->all(),
            'is_method_put' => $request->isMethod('put'),
            'is_method_post' => $request->isMethod('post'),
            'has_method_field' => $request->has('_method'),
            'method_field_value' => $request->input('_method')
        ]);

        $request->validate([
            'journey_name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'preferred_events' => 'nullable|string',
            'locations' => 'required|array|min:1',
            'locations.*' => 'required|string|max:255'
        ]);

        DB::beginTransaction();

        try {
            // Explicitly find journey
            $journey = Journey::findOrFail($id);

            // Update the journey details
            $journey->update([
                'journey_name' => $request->journey_name,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'preferred_events' => $request->preferred_events,
            ]);

            // Delete old locations
            JourneyLocation::where('journey_id', $journey->id)->delete();

            // Insert new locations
            foreach ($request->locations as $location) {
                JourneyLocation::create([
                    'journey_id' => $journey->id, 
                    'location' => $location
                ]);
            }

            DB::commit();

            // Fetch and save weather forecasts for each location
            foreach ($request->locations as $location) {
                // Delete ALL old forecasts for this journey/location
                \App\Models\WeatherForecast::where('journey_id', $journey->id)
                    ->where('location', $location)
                    ->delete();
                // Clear the weather cache for this location and date range
                $cacheKey = "weather_{$location}_{$journey->start_date}_{$journey->end_date}";
                \Illuminate\Support\Facades\Cache::forget($cacheKey);
                // Fetch and save new forecasts for the full current date range
                $this->weatherService->getWeatherForecast($location, $journey->start_date, $journey->end_date, $journey);
            }

            return redirect()->route('journey.index')->with('success', 'Journey updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error updating journey: ' . $e->getMessage());
        }
    }


    public function destroy(Journey $journey)
    {
        if ($journey->user_id != Auth::id()) {
            return redirect()->route('journey.index')->with('error', 'Unauthorized action.');
        }

        try {
            $journey->delete();
            return redirect()->route('journey.index')->with('success', 'Journey deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete the journey. Please try again.');
        }
    }

    public function show(Journey $journey)
    {
        if ($journey->user_id != Auth::id()) {
            return redirect()->route('journey.index')->with('error', 'Unauthorized action.');
        }

        try {
            // Always load locations
            $journey->load('locations');
            
            // Always fetch weather data from database
            $weatherData = [];
            foreach ($journey->locations as $location) {
                $forecasts = \App\Models\WeatherForecast::where('journey_id', $journey->id)
                    ->where('location', $location->location)
                    ->orderBy('forecast_date')
                    ->get();
                if ($forecasts->count() > 0) {
                    foreach ($forecasts as $forecast) {
                        $weatherData[$location->location][$forecast->forecast_date->format('Y-m-d')] = [
                            'temperature' => $forecast->temperature,
                            'description' => $forecast->description,
                            'icon' => $forecast->icon,
                            'humidity' => $forecast->humidity,
                            'wind_speed' => $forecast->wind_speed,
                        ];
                    }
                }
            }

            return view('journey.show', compact('journey', 'weatherData'));
        } catch (\Exception $e) {
            Log::error('Error fetching weather data: ' . $e->getMessage(), [
                'journey_id' => $journey->id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return redirect()->route('journey.index')
                ->with('error', 'No weather data available.');
        }
    }

    public function getWeatherData(Request $request, Journey $journey)
    {
        try {
            $location = $request->input('location');
            if (!$location) {
                return response()->json(['error' => 'Location is required'], 400);
            }

            $weatherData = $this->weatherService->getWeatherForecast(
                $location,
                $journey->start_date,
                $journey->end_date
            );

            if (!$weatherData) {
                return response()->json(['error' => 'No weather data available.'], 404);
            }

            return response()->json($weatherData);
        } catch (\Exception $e) {
            Log::error('Weather data fetch error: ' . $e->getMessage());
            return response()->json(['error' => 'No weather data available.'], 404);
        }
    }

    public function analyze(Request $request, Journey $journey)
    {
        try {
            // Ensure journey is loaded with locations
            $journey->load(['locations']);
            
            // Use starting location from journey
            $currentLocation = $journey->starting_location;
            $customQuestion = $request->input('custom_question');
            
            if (!$currentLocation) {
                return response()->json([
                    'error' => 'Starting location is not set for this journey'
                ], 400);
            }

            // Get weather data for each location from weather_forecasts table (not API)
            $weatherData = [];
            foreach ($journey->locations as $location) {
                $forecasts = \App\Models\WeatherForecast::where('journey_id', $journey->id)
                    ->where('location', $location->location)
                    ->whereBetween('forecast_date', [$journey->start_date, $journey->end_date])
                    ->orderBy('forecast_date')
                    ->get();
                // Convert to associative array indexed by date
                $weatherArray = [];
                foreach ($forecasts as $forecast) {
                    $weatherArray[$forecast->forecast_date->format('Y-m-d')] = [
                        'description' => $forecast->description,
                        'temperature' => $forecast->temperature,
                        'humidity' => $forecast->humidity,
                        'wind_speed' => $forecast->wind_speed,
                    ];
                }
                $weatherData[$location->location] = $weatherArray;
                Log::info('Weather forecast data from DB for GPT', [
                    'location' => $location->location,
                    'count' => count($weatherArray)
                ]);
            }

            // Store weather data in journey for GPT analysis
            foreach ($journey->locations as $location) {
                $location->weather_data = $weatherData[$location->location] ?? null;
                Log::info('Weather data assigned to location', [
                    'location' => $location->location,
                    'has_weather_data' => !empty($location->weather_data),
                    'weather_data' => $location->weather_data
                ]);
            }

            // Use the injected GPT service
            $response = $this->gptService->analyzeJourney($journey, $currentLocation, $customQuestion);
            
            // Log the response for debugging
            Log::info('GPT Analysis Response', ['response' => $response]);
            
            // Create recommendation record
            $recommendation = new Recommendation();
            $recommendation->journey_id = $journey->id;
            $recommendation->current_location = $currentLocation;
            $recommendation->recommendation = $response; // Direct string from GPT service
            $recommendation->generated_at = now();
            $recommendation->save();

            return response()->json([
                'success' => true,
                'message' => 'Journey analyzed successfully',
                'recommendation' => $recommendation
            ]);
        } catch (\Exception $e) {
            $errorMessage = mb_convert_encoding($e->getMessage(), 'UTF-8', 'UTF-8');
            
            Log::error('Journey Analysis Error: ' . $errorMessage, [
                'journey_id' => $journey->id,
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => array_map(function($trace) {
                    return mb_convert_encoding(json_encode($trace), 'UTF-8', 'UTF-8');
                }, $e->getTrace())
            ]);
            
            // Log to a separate file for debugging
            file_put_contents(
                storage_path('logs/journey_analysis_debug.log'),
                date('Y-m-d H:i:s') . ' Error: ' . $errorMessage . "\n",
                FILE_APPEND
            );
            
            return response()->json([
                'error' => 'Error analyzing journey: ' . $errorMessage
            ], 500);
        }
    }
}
