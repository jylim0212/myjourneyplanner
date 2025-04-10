<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GptApiSetting;
use App\Services\GptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GptController extends Controller
{
    protected $gptService;

    public function __construct(GptService $gptService)
    {
        $this->gptService = $gptService;
    }

    public function index()
    {
        $setting = GptApiSetting::where('is_active', true)->first();
        
        if (!$setting) {
            // Create a default setting if none exists
            $setting = GptApiSetting::create([
                'api_key' => env('GPT_API_KEY', ''),
                'api_host' => env('GPT_API_HOST', 'api.openai.com'),
                'api_url' => env('GPT_API_URL', 'https://api.openai.com/v1/chat/completions'),
                'is_active' => true,
                'default_question' => "Based on the journey details and weather forecast provided, please analyze this trip and provide recommendations in the following format:\n\n1. Weather Overview:\n   - Summarize the weather conditions for each day\n   - Highlight any weather-related concerns\n\n2. Daily Itinerary Suggestions:\n   - Break down by date\n   - Recommend indoor/outdoor activities based on weather\n   - Suggest local attractions and dining options\n   - Consider travel time between locations\n\n3. Essential Preparations:\n   - What to pack based on weather and activities\n   - Transportation recommendations\n   - Health and safety tips\n\n4. Local Tips:\n   - Cultural considerations\n   - Best times for various activities\n   - Alternative plans for weather changes\n\nPlease format the response with clear headings, bullet points, and ensure it's easy to read."
            ]);
        }
        
        // Check if API key is not set
        if (empty($setting->api_key)) {
            session()->flash('warning', 'GPT API key is not configured. Journey analysis will not work until you set up the API key.');
        }
        
        return view('admin.gpt.index', compact('setting'));
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'api_key' => 'required|string',
                'api_host' => 'required|string',
                'api_url' => 'required|url'
            ]);

            // Update the API key
            $this->gptService->updateApiKey($request->api_key);
            
            // Update API host and URL
            $this->gptService->updateApiConfig($request->api_host, $request->api_url);

            return redirect()->route('admin.gpt.index')
                ->with('success', 'GPT API configuration updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update GPT API configuration: ' . $e->getMessage());
            return redirect()->route('admin.gpt.index')
                ->with('error', 'Failed to update GPT API configuration. Please try again.');
        }
    }

    public function updateQuestions(Request $request)
    {
        try {
            $request->validate([
                'default_question' => 'required|string|min:10',
            ]);

            // Get current active setting
            $setting = GptApiSetting::where('is_active', true)->first();
            
            if ($setting) {
                // Update existing setting
                $setting->default_question = $request->default_question;
                $setting->save();
            } else {
                // Create new setting with defaults
                GptApiSetting::create([
                    'default_question' => $request->default_question,
                    'api_key' => env('GPT_API_KEY', ''),
                    'api_host' => env('GPT_API_HOST', 'api.openai.com'),
                    'api_url' => env('GPT_API_URL', 'https://api.openai.com/v1/chat/completions'),
                    'is_active' => true
                ]);
            }

            return redirect()->route('admin.gpt.index')
                ->with('success', 'GPT questions updated successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to update GPT questions: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            return redirect()->route('admin.gpt.index')
                ->with('error', 'Failed to update GPT questions. Please try again.');
        }
    }
}