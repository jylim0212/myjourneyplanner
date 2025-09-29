<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Journey;
use App\Models\JourneyLocation;
use App\Models\WeatherForecast;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JourneyControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->journey = Journey::factory()->create([
            'user_id' => $this->user->id,
            'journey_name' => 'Test Journey',
            'starting_location' => 'Tokyo',
            'start_date' => now(),
            'end_date' => now()->addDays(5)
        ]);
    }

    public function test_index_displays_user_journeys()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('journey.index'));

        $response->assertStatus(200);
        $response->assertViewIs('journey.index');
        $response->assertViewHas('journeys');
    }

    public function test_create_displays_form()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('journey.create'));

        $response->assertStatus(200);
        $response->assertViewIs('journey.create');
    }

    public function test_store_creates_new_journey()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('journey.store'), [
            'journey_name' => 'New Journey',
            'starting_location' => 'Osaka',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addDays(3)->format('Y-m-d'),
            'preferred_events' => 'Shopping, Food',
            'locations' => ['Kyoto', 'Nara']
        ]);

        $response->assertRedirect(route('journey.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('journeys', [
            'journey_name' => 'New Journey',
            'starting_location' => 'Osaka'
        ]);

        $this->assertDatabaseHas('journey_locations', [
            'location' => 'Kyoto'
        ]);

        $this->assertDatabaseHas('journey_locations', [
            'location' => 'Nara'
        ]);
    }

    public function test_edit_displays_form()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('journey.edit', $this->journey));

        $response->assertStatus(200);
        $response->assertViewIs('journey.edit');
        $response->assertViewHas('journey');
    }

    public function test_update_modifies_journey()
    {
        $response = $this->actingAs($this->user)
            ->post(route('journey.update', $this->journey), [
                'journey_name' => 'Updated Journey',
                'start_date' => now()->addDays(1)->format('Y-m-d'),
                'end_date' => now()->addDays(5)->format('Y-m-d'),
                'preferred_events' => 'Updated events',
                'locations' => ['New Location 1', 'New Location 2']
            ]);

        $response->assertRedirect(route('journey.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('journeys', [
            'id' => $this->journey->id,
            'journey_name' => 'Updated Journey'
        ]);

        $this->assertDatabaseHas('journey_locations', [
            'journey_id' => $this->journey->id,
            'location' => 'New Location 1'
        ]);

        $this->assertDatabaseHas('journey_locations', [
            'journey_id' => $this->journey->id,
            'location' => 'New Location 2'
        ]);
    }

    public function test_destroy_deletes_journey()
    {
        $this->actingAs($this->user);

        $response = $this->delete(route('journey.destroy', $this->journey));

        $response->assertRedirect(route('journey.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('journeys', [
            'id' => $this->journey->id
        ]);
    }

    public function test_show_displays_journey_details()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('journey.show', $this->journey));

        $response->assertStatus(200);
        $response->assertViewIs('journey.show');
        $response->assertViewHas(['journey', 'weatherData']);
    }

    public function test_analyze_generates_recommendations()
    {
        $this->actingAs($this->user);

        // Create weather forecast data
        WeatherForecast::create([
            'journey_id' => $this->journey->id,
            'location' => 'Tokyo',
            'forecast_date' => now(),
            'temperature' => 25,
            'description' => 'Sunny',
            'icon' => '01d',
            'humidity' => 60,
            'wind_speed' => 5,
            'raw_data' => json_encode(['weather_id' => 800])
        ]);

        $response = $this->post(route('journey.analyze', $this->journey), [
            'custom_question' => 'What should I do in Tokyo?'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'recommendation'
        ]);
    }
} 