<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Journey;
use App\Models\JourneyLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\WeatherService;
use App\Services\GptService;
use App\Models\Recommendation;

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
        $journeys = Journey::where('user_id', Auth::id())->get();
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
        $weatherData = [];
        foreach ($journey->locations as $location) {
            $weatherData[$location->location] = $this->weatherService->getWeatherForecast(
                $location->location,
                $journey->start_date,
                $journey->end_date
            );
        }

        return view('journey.show', compact('journey', 'weatherData'));
    }

    public function analyze(Request $request, Journey $journey)
    {
        try {
            $currentLocation = $request->input('current_location');
            
            if (!$currentLocation) {
                return response()->json([
                    'error' => 'Current location is required'
                ], 400);
            }

            $gptService = new GptService();
            $response = $gptService->analyzeJourney($journey, $currentLocation);
            
            // Log the response for debugging
            \Log::info('GPT Analysis Response:', $response);
            
            // Format the recommendation
            $recommendationText = $response['result'] ?? json_encode($response);
            
            // Create recommendation record
            $recommendation = new Recommendation([
                'journey_id' => $journey->id,
                'current_location' => $currentLocation,
                'recommendation' => $recommendationText,
                'generated_at' => now()
            ]);
            $recommendation->save();

            return response()->json([
                'success' => true,
                'message' => 'Journey analyzed successfully',
                'recommendation' => $recommendation
            ]);
        } catch (\Exception $e) {
            \Log::error('Journey Analysis Error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error analyzing journey: ' . $e->getMessage()
            ], 500);
        }
    }
}

