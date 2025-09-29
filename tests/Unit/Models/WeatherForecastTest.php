<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Journey;
use App\Models\User;
use App\Models\WeatherForecast;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WeatherForecastTest extends TestCase
{
    use RefreshDatabase;

    private $weatherForecast;
    private $journey;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        $this->journey = Journey::create([
            'user_id' => $this->user->id,
            'journey_name' => 'Test Journey',
            'starting_location' => 'Test Location',
            'start_date' => '2025-05-01',
            'end_date' => '2025-05-05'
        ]);

        $this->weatherForecast = WeatherForecast::create([
            'journey_id' => $this->journey->id,
            'location' => 'Test Location',
            'forecast_date' => '2025-05-01',
            'description' => 'Clear',
            'icon' => '01d',
            'temperature' => 25,
            'humidity' => 60,
            'wind_speed' => 5,
            'raw_data' => ['weather' => ['id' => 800, 'main' => 'Clear']]
        ]);
    }

    /** @test */
    public function it_belongs_to_a_journey()
    {
        $this->assertInstanceOf(Journey::class, $this->weatherForecast->journey);
        $this->assertEquals($this->journey->id, $this->weatherForecast->journey->id);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $this->assertIsString($this->weatherForecast->forecast_date->toDateString());
        $this->assertIsArray($this->weatherForecast->raw_data);
    }

    /** @test */
    public function it_can_create_forecast_with_valid_data()
    {
        $forecastData = [
            'journey_id' => $this->journey->id,
            'location' => 'New Location',
            'forecast_date' => '2025-05-02',
            'description' => 'Cloudy',
            'icon' => '02d',
            'temperature' => 23,
            'humidity' => 65,
            'wind_speed' => 5,
            'raw_data' => ['weather' => ['id' => 801, 'main' => 'Clouds']]
        ];

        $newForecast = WeatherForecast::create($forecastData);

        $this->assertInstanceOf(WeatherForecast::class, $newForecast);
        $this->assertDatabaseHas('weather_forecasts', [
            'journey_id' => $forecastData['journey_id'],
            'location' => $forecastData['location'],
            'forecast_date' => $forecastData['forecast_date'] . ' 00:00:00',
            'description' => $forecastData['description'],
            'temperature' => $forecastData['temperature'],
            'humidity' => $forecastData['humidity'],
            'wind_speed' => $forecastData['wind_speed']
        ]);
    }

    /** @test */
    public function it_can_update_forecast_attributes()
    {
        $updatedData = [
            'description' => 'Rainy',
            'temperature' => 19,
            'humidity' => 80
        ];

        $this->weatherForecast->update($updatedData);
        $this->weatherForecast->refresh();

        foreach ($updatedData as $key => $value) {
            $this->assertEquals($value, $this->weatherForecast->$key);
        }
    }
}
