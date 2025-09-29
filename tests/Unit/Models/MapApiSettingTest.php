<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\MapApiSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MapApiSettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_api_config()
    {
        $setting = MapApiSetting::create([
            'api_key' => 'test_key'
        ]);

        $config = MapApiSetting::getApiConfig();
        
        $this->assertEquals([
            'api_key' => 'test_key',
            'api_host' => config('services.map.api_host'),
            'api_url' => config('services.map.api_url')
        ], $config);
    }

    /** @test */
    public function it_returns_config_fallback_when_no_setting()
    {
        // Delete any existing settings
        MapApiSetting::query()->delete();

        $config = MapApiSetting::getApiConfig();
        
        $this->assertEquals(config('services.map.api_key'), $config['api_key']);
        $this->assertEquals(config('services.map.api_host'), $config['api_host']);
        $this->assertEquals(config('services.map.api_url'), $config['api_url']);
    }

    /** @test */
    public function it_can_get_api_config_with_setting()
    {
        // Delete any existing settings
        MapApiSetting::query()->delete();

        $setting = MapApiSetting::create([
            'api_key' => 'test_key'
        ]);

        $config = MapApiSetting::getApiConfig();
        
        $this->assertEquals([
            'api_key' => 'test_key',
            'api_host' => config('services.map.api_host'),
            'api_url' => config('services.map.api_url')
        ], $config);
    }

    /** @test */
    public function it_can_create_setting_with_valid_data()
    {
        $settingData = [
            'api_key' => 'test_key'
        ];

        $setting = MapApiSetting::create($settingData);

        $this->assertInstanceOf(MapApiSetting::class, $setting);
        $this->assertDatabaseHas('map_api_settings', $settingData);
    }

    /** @test */
    public function it_can_update_setting_attributes()
    {
        $setting = MapApiSetting::create([
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
