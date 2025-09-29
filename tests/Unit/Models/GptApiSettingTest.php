<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\GptApiSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GptApiSettingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_get_default_question()
    {
        // Delete any existing settings
        GptApiSetting::query()->delete();

        // Create an active setting
        GptApiSetting::create([
            'default_question' => 'Test question',
            'api_key' => 'test_key',
            'api_host' => 'test_host',
            'api_url' => 'test_url',
            'is_active' => true
        ]);

        $question = GptApiSetting::getDefaultQuestion();
        $this->assertEquals('Test question', $question);
    }

    /** @test */
    public function it_returns_fallback_question_when_no_active_setting()
    {
        // Delete any existing settings
        GptApiSetting::query()->delete();

        // Create an inactive setting
        GptApiSetting::create([
            'default_question' => 'Test question',
            'api_key' => 'test_key',
            'api_host' => 'test_host',
            'api_url' => 'test_url',
            'is_active' => false
        ]);

        $question = GptApiSetting::getDefaultQuestion();
        $this->assertEquals('Please analyze this journey and provide recommendations.', $question);
    }

    /** @test */
    public function it_can_get_api_config()
    {
        // Delete any existing settings
        GptApiSetting::query()->delete();

        $setting = GptApiSetting::create([
            'default_question' => 'Test question',
            'api_key' => 'test_key',
            'api_host' => 'test_host',
            'api_url' => 'test_url',
            'is_active' => true
        ]);

        $config = GptApiSetting::getApiConfig();
        
        $this->assertEquals([
            'api_key' => 'test_key',
            'api_host' => 'test_host',
            'api_url' => 'test_url'
        ], $config);
    }

    /** @test */
    public function it_returns_config_fallback_when_no_active_setting()
    {
        // Delete any existing settings
        GptApiSetting::query()->delete();

        $config = GptApiSetting::getApiConfig();
        
        $this->assertEquals([
            'api_key' => config('services.gpt.api_key'),
            'api_host' => config('services.gpt.api_host'),
            'api_url' => config('services.gpt.api_url')
        ], $config);
    }

    /** @test */
    public function it_can_create_setting_with_valid_data()
    {
        $settingData = [
            'default_question' => 'Test question',
            'api_key' => 'test_key',
            'api_host' => 'test_host',
            'api_url' => 'test_url',
            'is_active' => true
        ];

        $setting = GptApiSetting::create($settingData);

        $this->assertInstanceOf(GptApiSetting::class, $setting);
        $this->assertDatabaseHas('gpt_api_settings', $settingData);
    }

    /** @test */
    public function it_can_update_setting_attributes()
    {
        $setting = GptApiSetting::create([
            'default_question' => 'Test question',
            'api_key' => 'test_key',
            'api_host' => 'test_host',
            'api_url' => 'test_url',
            'is_active' => true
        ]);

        $updatedData = [
            'default_question' => 'Updated question',
            'api_key' => 'updated_key'
        ];

        $setting->update($updatedData);
        $setting->refresh();

        foreach ($updatedData as $key => $value) {
            $this->assertEquals($value, $setting->$key);
        }
    }
}
