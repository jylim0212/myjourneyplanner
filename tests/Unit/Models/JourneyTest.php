<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Journey;
use App\Models\JourneyLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;

class JourneyTest extends TestCase
{
    use RefreshDatabase;

    private $journey;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => bcrypt('password')
        ]);

        $this->journey = Journey::create([
            'user_id' => $this->user->id,
            'journey_name' => 'Test Journey',
            'starting_location' => 'Test Location',
            'start_date' => '2025-05-01',
            'end_date' => '2025-05-05',
            'preferred_events' => 'Test Events'
        ]);
    }

    /** @test */
    public function it_belongs_to_a_user()
    {
        $this->assertInstanceOf(User::class, $this->journey->user);
        $this->assertEquals($this->user->id, $this->journey->user->id);
    }

    /** @test */
    public function it_has_many_locations()
    {
        // Create test locations
        for ($i = 0; $i < 3; $i++) {
            JourneyLocation::create([
                'journey_id' => $this->journey->id,
                'location' => "Test Location {$i}"
            ]);
        }

        $this->assertCount(3, $this->journey->locations);
        $this->assertInstanceOf(JourneyLocation::class, $this->journey->locations->first());
    }

    /** @test */
    public function it_deletes_related_locations_when_deleted()
    {
        // Create test locations
        $locations = [];
        for ($i = 0; $i < 3; $i++) {
            $locations[] = JourneyLocation::create([
                'journey_id' => $this->journey->id,
                'location' => "Test Location {$i}"
            ]);
        }

        $locationIds = collect($locations)->pluck('id')->toArray();

        // Verify locations exist
        foreach ($locationIds as $id) {
            $this->assertDatabaseHas('journey_locations', ['id' => $id]);
        }

        $this->journey->delete();

        // Verify journey is deleted
        $this->assertDatabaseMissing('journeys', ['id' => $this->journey->id]);

        // Verify all related locations are deleted
        foreach ($locationIds as $id) {
            $this->assertDatabaseMissing('journey_locations', ['id' => $id]);
        }
    }

    /** @test */
    public function it_can_create_journey_with_valid_data()
    {
        $journeyData = [
            'user_id' => $this->user->id,
            'journey_name' => 'New Test Journey',
            'starting_location' => 'New Test Location',
            'start_date' => '2025-06-01',
            'end_date' => '2025-06-05',
            'preferred_events' => 'New Test Events'
        ];

        $newJourney = Journey::create($journeyData);

        $this->assertInstanceOf(Journey::class, $newJourney);
        $this->assertDatabaseHas('journeys', $journeyData);
    }

    /** @test */
    public function it_can_update_journey_attributes()
    {
        $updatedData = [
            'journey_name' => 'Updated Journey',
            'starting_location' => 'Updated Location'
        ];

        $this->journey->update($updatedData);
        $this->journey->refresh();

        foreach ($updatedData as $key => $value) {
            $this->assertEquals($value, $this->journey->$key);
        }
    }
}
