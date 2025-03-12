<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Journey;
use App\Models\JourneyLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JourneyController extends Controller
{
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
}

