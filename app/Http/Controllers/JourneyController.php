<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Journey;
use Illuminate\Support\Facades\Auth;

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
            'location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'preferred_events' => 'nullable|string|max:255',
        ]);

        Journey::create([
            'user_id' => Auth::id(),
            'journey_name' => $request->journey_name,
            'location' => $request->location,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'preferred_events' => $request->preferred_events,
        ]);

        return redirect()->route('journey.index')->with('success', 'Journey created successfully!');
    }

    // Show the edit form for a specific journey
    public function edit($id)
    {
        $journey = Journey::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return view('journey.edit', compact('journey'));
    }

    // Update the journey details
    public function update(Request $request, $id)
    {
        $request->validate([
            'journey_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'preferred_events' => 'nullable|string|max:255',
        ]);

        $journey = Journey::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $journey->update($request->all());

        return redirect()->route('journey.index')->with('success', 'Journey updated successfully!');
    }

    public function destroy($id)
    {
        $journey = Journey::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        $journey->delete();

        return redirect()->route('journey.index')->with('success', 'Journey deleted successfully!');
    }

}

