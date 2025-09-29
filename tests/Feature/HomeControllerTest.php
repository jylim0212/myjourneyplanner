<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_requires_authentication()
    {
        $response = $this->get(route('journey.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_index_displays_dashboard_for_authenticated_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('journey.index'));
        $response->assertStatus(200);
        $response->assertViewIs('journey.index');
    }
} 