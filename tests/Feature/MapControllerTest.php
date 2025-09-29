<?php

namespace Tests\Feature;

use App\Models\MapApiSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MapControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['is_admin' => true]);
    }

    public function test_index_displays_map_settings()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.map.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.map.index');
    }

    public function test_update_map_settings()
    {
        $response = $this->actingAs($this->admin)
            ->put(route('admin.map.update'), [
                'api_key' => 'new-api-key'
            ]);

        $response->assertRedirect(route('admin.map.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('map_api_settings', [
            'api_key' => 'new-api-key'
        ]);
    }
} 