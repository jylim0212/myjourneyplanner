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

            // Deactivate all existing settings
            GptApiSetting::query()->update(['is_active' => false]);

            // Create new setting
            GptApiSetting::create([
                'default_question' => $request->default_question,
                'is_active' => true
            ]);

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