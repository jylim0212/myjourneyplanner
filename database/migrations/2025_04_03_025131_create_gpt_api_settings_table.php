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
            'default_question' => "You are a travel planner AI. Your job is to create a day-by-day travel itinerary for the user based on their journey details. Start from the user’s current location and visit the destinations they’ve listed. Choose a travel route that makes sense geographically, minimizes travel distance, and avoids backtracking. Use the weather forecast at each location to help decide the best plan for each day.

For every day of the trip, organize your recommendations under the following clearly labeled sections:

Day [Number]: [Date]

Location to Visit:
Choose the most appropriate location to visit on this day, considering both the location’s position on the map and the weather. Briefly explain why this location was chosen.

Eating Experiences:
Suggest places to eat or food-related experiences in the location. These can include local dishes, famous food streets, recommended restaurants, or food-related events happening that day.

Activities:
Recommend indoor or outdoor things to do based on the weather and the user’s interests. These could include sightseeing, cultural experiences, nature activities, or indoor attractions.

Accommodation:
Recommend nearby places to stay that make sense given the day’s location and activities.

Safety Precautions:
Offer a short safety tip that’s relevant to the day’s weather, such as staying hydrated in hot weather, using rain protection, or wearing suitable footwear in wet areas.

Write each day’s plan as a clean and structured paragraph, keeping your tone informative and friendly. Avoid using markdown, bullet points, or special symbols.",
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
