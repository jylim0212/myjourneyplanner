<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Journey;
use App\Models\User;
use App\Models\Recommendation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RecommendationTest extends TestCase
{
    use RefreshDatabase;

    private $recommendation;
    private $journey;

    protected function setUp(): void
    {
        parent::setUp();
        
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => bcrypt('password')
        ]);

        $this->journey = Journey::create([
            'user_id' => $user->id,
            'journey_name' => 'Test Journey',
            'starting_location' => 'Test Location',
            'start_date' => '2025-05-01',
            'end_date' => '2025-05-05'
        ]);

        $this->recommendation = Recommendation::create([
            'journey_id' => $this->journey->id,
            'current_location' => 'Test Location',
            'recommendation' => 'Test recommendation'
        ]);
    }

    /** @test */
    public function it_belongs_to_a_journey()
    {
        $this->assertInstanceOf(Journey::class, $this->recommendation->journey);
        $this->assertEquals($this->journey->id, $this->recommendation->journey->id);
    }

    /** @test */
    public function it_can_create_recommendation_with_valid_data()
    {
        $recommendationData = [
            'journey_id' => $this->journey->id,
            'current_location' => 'New Test Location',
            'recommendation' => 'New test recommendation'
        ];

        $newRecommendation = Recommendation::create($recommendationData);

        $this->assertInstanceOf(Recommendation::class, $newRecommendation);
        $this->assertDatabaseHas('recommendations', $recommendationData);
    }

    /** @test */
    public function it_can_update_recommendation_attributes()
    {
        $updatedData = [
            'current_location' => 'Updated Location',
            'recommendation' => 'Updated recommendation'
        ];

        $this->recommendation->update($updatedData);
        $this->recommendation->refresh();

        foreach ($updatedData as $key => $value) {
            $this->assertEquals($value, $this->recommendation->$key);
        }
    }
}
