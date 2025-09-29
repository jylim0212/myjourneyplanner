<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\WeatherApiSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WeatherApiSettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_setting_with_valid_data()
    {
        $settingData = [
            'api_key' => 'test_key'
        ];

        $setting = WeatherApiSetting::create($settingData);

        $this->assertInstanceOf(WeatherApiSetting::class, $setting);
        $this->assertDatabaseHas('weather_api_settings', $settingData);
    }

    /** @test */
    public function it_can_update_setting_attributes()
    {
        $setting = WeatherApiSetting::create([
            'api_key' => 'test_key'
        ]);

        $updatedData = [
            'api_key' => 'updated_key'
        ];

        $setting->update($updatedData);
        $setting->refresh();

        $this->assertEquals($updatedData['api_key'], $setting->api_key);
    }
}
