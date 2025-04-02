@extends('layouts.admin')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h2>GPT API Management</h2>
            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">API Configuration</h5>
                    <form action="{{ route('admin.gpt.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="api_key" class="form-label">GPT API Key</label>
                            <input type="text" class="form-control" id="api_key" name="api_key" value="{{ old('api_key', $setting->api_key) }}">
                        </div>
                        <div class="mb-3">
                            <label for="api_host" class="form-label">GPT API Host</label>
                            <input type="text" class="form-control" id="api_host" name="api_host" value="{{ old('api_host', $setting->api_host) }}">
                        </div>
                        <div class="mb-3">
                            <label for="api_url" class="form-label">GPT API URL</label>
                            <input type="text" class="form-control" id="api_url" name="api_url" value="{{ old('api_url', $setting->api_url) }}">
                        </div>
                        <button type="submit" class="btn btn-primary">Save API Configuration</button>
                    </form>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-body">
                    <h5 class="card-title">Analysis Question Template</h5>
                    <form action="{{ route('admin.gpt.questions') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label for="default_question" class="form-label">Question Template</label>
                            <p class="text-muted small">This template will be used to analyze all journeys. Structure it to get consistent, well-formatted responses.</p>
                            <textarea class="form-control" id="default_question" name="default_question" rows="15">{{ old('default_question', $setting->default_question ?? "Based on the journey details and weather forecast provided, please analyze this trip and provide recommendations in the following format:

1. Weather Overview:
   - Summarize the weather conditions for each day
   - Highlight any weather-related concerns

2. Daily Itinerary Suggestions:
   - Break down by date
   - Recommend indoor/outdoor activities based on weather
   - Suggest local attractions and dining options
   - Consider travel time between locations

3. Essential Preparations:
   - What to pack based on weather and activities
   - Transportation recommendations
   - Health and safety tips

4. Local Tips:
   - Cultural considerations
   - Best times for various activities
   - Alternative plans for weather changes

Please format the response with clear headings, bullet points, and ensure it's easy to read.") }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Question Template</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection