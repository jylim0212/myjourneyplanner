<?php

namespace App\Http\Controllers;

use App\Models\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RecommendationsController extends Controller
{
    public function index()
    {
        $recommendations = Recommendation::whereHas('journey', function($query) {
            $query->where('user_id', Auth::id());
        })->with('journey')->orderBy('created_at', 'desc')->get();

        return view('recommendations.index', compact('recommendations'));
    }
} 