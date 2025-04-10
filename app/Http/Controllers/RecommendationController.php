<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class RecommendationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        try {
            $recommendations = Recommendation::with('journey')
                ->whereHas('journey', function($query) {
                    $query->where('user_id', Auth::id());
                })
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Retrieved recommendations', ['count' => $recommendations->count()]);
            return view('recommendations.index', compact('recommendations'));

        } catch (\Exception $e) {
            Log::error('Error retrieving recommendations', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            return redirect()->route('journey.index')
                ->with('error', 'Unable to load recommendations. Please try again later.');
        }
    }

    public function destroy(Recommendation $recommendation)
    {
        try {
            // Check if the recommendation exists and belongs to the current user's journey
            if (!$recommendation->exists) {
                Log::warning('Attempt to delete non-existent recommendation', [
                    'user_id' => Auth::id(),
                    'recommendation_id' => $recommendation->id
                ]);
                return response()->json(['error' => 'Recommendation not found'], 404);
            }

            if ($recommendation->journey->user_id !== Auth::id()) {
                Log::warning('Unauthorized attempt to delete recommendation', [
                    'user_id' => Auth::id(),
                    'recommendation_id' => $recommendation->id,
                    'journey_user_id' => $recommendation->journey->user_id
                ]);
                return response()->json(['error' => 'Unauthorized action'], 403);
            }

            DB::beginTransaction();
            try {
                $recommendation->delete();
                DB::commit();

                Log::info('Recommendation deleted successfully', [
                    'recommendation_id' => $recommendation->id,
                    'user_id' => Auth::id()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Recommendation deleted successfully'
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error deleting recommendation', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'recommendation_id' => $recommendation->id ?? null
            ]);

            return response()->json([
                'error' => 'Failed to delete recommendation. Please try again later.'
            ], 500);
        }
    }
}
