<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Journey;
use App\Models\Recommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecommendationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->journey = Journey::factory()->create([
            'user_id' => $this->user->id
        ]);
        $this->recommendation = Recommendation::factory()->create([
            'journey_id' => $this->journey->id,
            'current_location' => 'Tokyo',
            'recommendation' => 'Test recommendation',
            'generated_at' => now()
        ]);
    }

    public function test_index_displays_user_recommendations()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('recommendations.index'));

        $response->assertStatus(200);
        $response->assertViewIs('recommendations.index');
        $response->assertViewHas('recommendations');
    }

    public function test_destroy_deletes_recommendation()
    {
        $this->actingAs($this->user);

        $response = $this->delete(route('recommendations.destroy', $this->recommendation));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Recommendation deleted successfully'
        ]);

        $this->assertDatabaseMissing('recommendations', [
            'id' => $this->recommendation->id
        ]);
    }

    public function test_cannot_destroy_other_user_recommendation()
    {
        $otherUser = User::factory()->create();
        $this->actingAs($otherUser);

        $response = $this->delete(route('recommendations.destroy', $this->recommendation));

        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'Unauthorized action'
        ]);

        $this->assertDatabaseHas('recommendations', [
            'id' => $this->recommendation->id
        ]);
    }
} 