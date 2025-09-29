<?php

namespace Tests\Feature;

use App\Models\GptApiSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GptControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_index_displays_gpt_settings()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.gpt.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.gpt.index');
    }

    public function test_update_gpt_settings()
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('admin.gpt.update'), [
            'api_key' => 'new-api-key',
            'api_host' => 'api.openai.com',
            'api_url' => 'https://api.openai.com/v1/chat/completions'
        ]);

        $response->assertRedirect(route('admin.gpt.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('gpt_api_settings', [
            'api_key' => 'new-api-key',
            'api_host' => 'api.openai.com',
            'api_url' => 'https://api.openai.com/v1/chat/completions'
        ]);
    }

    public function test_update_questions()
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('admin.gpt.questions'), [
            'default_question' => 'New default question for GPT'
        ]);

        $response->assertRedirect(route('admin.gpt.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('gpt_api_settings', [
            'default_question' => 'New default question for GPT'
        ]);
    }
} 