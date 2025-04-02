<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateGptApiSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('gpt_api_settings', function (Blueprint $table) {
            $table->id();
            $table->text('default_question');
            $table->string('api_key')->nullable();
            $table->string('api_host')->nullable();
            $table->string('api_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Insert default setting
        DB::table('gpt_api_settings')->insert([
            'default_question' => "Based on the journey details and weather forecast provided, please analyze this trip and provide recommendations in the following format:

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

Please format the response with clear headings, bullet points, and ensure it's easy to read.",
            'api_key' => config('services.gpt.api_key'),
            'api_host' => config('services.gpt.api_host'),
            'api_url' => config('services.gpt.api_url'),
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('gpt_api_settings');
    }
}
