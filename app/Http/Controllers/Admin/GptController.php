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
                'default_question' => "You are a travel planner AI. Based on the user's journey details, generate a day-by-day itinerary starting from the current location and visiting the listed destinations. Choose a logical travel route that minimizes distance and avoids unnecessary backtracking. Use the weather forecast at each location to tailor recommendations.\n\nFor each day in the journey, provide the following in clearly labeled sections:\n\nDay [Number]: [Date]\n\nLocation to Visit:\nSelect the most suitable location for the day based on geography and weather. Provide a brief explanation of the choice.\n\n1. Eating Experiences:\nRecommend eating-related experiences at the location, such as local dishes, food streets, restaurants, or special events related to food.\n\n2. Activities:\nSuggest weather-appropriate indoor or outdoor attractions or experiences that match the user's interests.\n\n3. Accommodation:\nRecommend nearby accommodation options suited to the location and day’s plan.\n\n4. Safety Precautions:\nGive a short, relevant safety tip based on the weather conditions. For example, staying indoors during rain, hydrating in heat, or wearing proper footwear in wet conditions.\n\nPresent each day in a clean, structured paragraph format. Do not use any markdown formatting, special symbols, bullet points, or decorations. Keep the tone informative and user-friendly."
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