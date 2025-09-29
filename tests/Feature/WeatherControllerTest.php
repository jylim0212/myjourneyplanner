<?php

namespace Tests\Feature;

use App\Models\WeatherApiSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeatherControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_index_displays_weather_settings()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.weather.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.weather.index');
    }

    public function test_update_weather_settings()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.weather.update'), [
                'api_key' => 'new-api-key'
            ]);

        $response->assertRedirect(route('admin.weather.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('weather_api_settings', [
            'api_key' => 'new-api-key'
        ]);
    }

    public function test_unauthorized_users_cannot_access_weather_settings()
    {
        $user = User::factory()->create(['is_admin' => false]);
        $this->actingAs($user);

        $response = $this->get(route('admin.weather.index'));
        $response->assertStatus(403);

        $response = $this->put(route('admin.weather.update'), [
            'api_key' => 'new-api-key'
        ]);
        $response->assertStatus(403);
    }

    public function test_guests_cannot_access_weather_settings()
    {
        $response = $this->get(route('admin.weather.index'));
        $response->assertRedirect(route('login'));

        $response = $this->put(route('admin.weather.update'), [
            'api_key' => 'new-api-key'
        ]);
        $response->assertRedirect(route('login'));
    }
} 