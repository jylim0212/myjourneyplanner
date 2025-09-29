<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Journey;
use App\Models\User;
use App\Models\JourneyLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JourneyLocationTest extends TestCase
{
    use RefreshDatabase;

    private $journeyLocation;
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

        $this->journeyLocation = JourneyLocation::create([
            'journey_id' => $this->journey->id,
            'location' => 'Test Location'
        ]);
    }

    /** @test */
    public function it_belongs_to_a_journey()
    {
        $this->assertInstanceOf(Journey::class, $this->journeyLocation->journey);
        $this->assertEquals($this->journey->id, $this->journeyLocation->journey->id);
    }

    /** @test */
    public function it_can_create_location_with_valid_data()
    {
        $locationData = [
            'journey_id' => $this->journey->id,
            'location' => 'New Test Location'
        ];

        $newLocation = JourneyLocation::create($locationData);

        $this->assertInstanceOf(JourneyLocation::class, $newLocation);
        $this->assertDatabaseHas('journey_locations', $locationData);
    }

    /** @test */
    public function it_can_update_location_attributes()
    {
        $updatedLocation = 'Updated Location';
        
        $this->journeyLocation->update([
            'location' => $updatedLocation
        ]);
        
        $this->journeyLocation->refresh();
        $this->assertEquals($updatedLocation, $this->journeyLocation->location);
    }
}
