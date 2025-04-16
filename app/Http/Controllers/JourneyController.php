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
                $forecast = $this->weatherService->getWeatherForecast(
                    $location->location,
                    $journey->start_date,
                    $journey->end_date
                );

                if ($forecast) {
                    foreach ($forecast as $date => $data) {
                        // Check for severe weather conditions
                        if (isset($data['weather_id'])) {
                            // Weather condition codes:
                            // 2xx: Thunderstorm
                            // 5xx: Rain
                            // 6xx: Snow
                            // 7xx: Atmosphere (fog, dust, etc.)
                            // 8xx: Clear/Clouds
                            $weatherId = $data['weather_id'];
                            if ($weatherId < 800) { // Any severe weather
                                $isSafe = false;
                                $weatherWarnings[] = $data['description'] . ' at ' . $location->location . ' on ' . $date;
                            }
                        }
                    }
                }
            }

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
            
            // Always fetch weather data
            $weatherData = [];
            foreach ($journey->locations as $location) {
                Log::info('Fetching weather data for location in show', [
                    'location' => $location->location,
                    'start_date' => $journey->start_date,
                    'end_date' => $journey->end_date
                ]);

                $forecast = $this->weatherService->getWeatherForecast(
                    $location->location,
                    $journey->start_date,
                    $journey->end_date
                );

                if ($forecast) {
                    $weatherData[$location->location] = $forecast;
                    Log::info('Weather data fetched successfully', [
                        'location' => $location->location,
                        'data' => $forecast
                    ]);
                } else {
                    Log::warning('No weather data available for location', [
                        'location' => $location->location
                    ]);
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
                ->with('error', 'Failed to fetch weather data. Please try again later.');
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
                return response()->json(['error' => 'Failed to fetch weather data'], 500);
            }

            return response()->json($weatherData);
        } catch (\Exception $e) {
            Log::error('Weather data fetch error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch weather data'], 500);
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

            // Get weather data for each location
            $weatherData = [];
            foreach ($journey->locations as $location) {
                Log::info('Fetching weather data for location', [
                    'location' => $location->location,
                    'start_date' => $journey->start_date,
                    'end_date' => $journey->end_date
                ]);

                $weatherData[$location->location] = $this->weatherService->getWeatherForecast(
                    $location->location,
                    $journey->start_date,
                    $journey->end_date
                );

                Log::info('Weather data received', [
                    'location' => $location->location,
                    'has_data' => !empty($weatherData[$location->location]),
                    'data' => $weatherData[$location->location]
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
