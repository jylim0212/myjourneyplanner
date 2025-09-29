<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\User;
use App\Models\Journey;
use App\Models\JourneyLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class UserTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'is_admin' => false
        ]);
    }

    /** @test */
    public function it_has_many_journeys()
    {
        // Create test journeys
        for ($i = 0; $i < 3; $i++) {
            Journey::create([
                'user_id' => $this->user->id,
                'journey_name' => "Test Journey {$i}",
                'starting_location' => "Test Location {$i}",
                'start_date' => '2025-05-01',
                'end_date' => '2025-05-05'
            ]);
        }

        $this->assertCount(3, $this->user->journeys);
        $this->assertInstanceOf(Journey::class, $this->user->journeys->first());
    }

    /** @test */
    public function it_can_check_if_user_is_admin()
    {
        $this->assertFalse($this->user->isAdmin());

        $this->user->update(['is_admin' => true]);
        $this->user->refresh();

        $this->assertTrue($this->user->isAdmin());
    }

    /** @test */
    public function it_deletes_related_journeys_when_deleted()
    {
        // Create journeys with locations
        $journeys = [];
        for ($i = 0; $i < 3; $i++) {
            $journey = Journey::create([
                'user_id' => $this->user->id,
                'journey_name' => "Test Journey {$i}",
                'starting_location' => "Test Location {$i}",
                'start_date' => '2025-05-01',
                'end_date' => '2025-05-05'
            ]);

            JourneyLocation::create([
                'journey_id' => $journey->id,
                'location' => "Test Location {$i}"
            ]);

            $journeys[] = $journey;
        }

        $journeyIds = collect($journeys)->pluck('id')->toArray();

        // Verify journeys exist
        foreach ($journeyIds as $id) {
            $this->assertDatabaseHas('journeys', ['id' => $id]);
        }

        $this->user->delete();

        // Verify user is deleted
        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);

        // Verify all related journeys and their locations are deleted
        foreach ($journeyIds as $id) {
            $this->assertDatabaseMissing('journeys', ['id' => $id]);
            $this->assertDatabaseMissing('journey_locations', ['journey_id' => $id]);
        }
    }

    /** @test */
    public function it_can_create_user_with_valid_data()
    {
        $userData = [
            'name' => 'New Test User',
            'email' => 'newtest_' . uniqid() . '@example.com',
            'password' => Hash::make('newpassword123'),
            'is_admin' => true
        ];

        $newUser = User::create($userData);

        $this->assertInstanceOf(User::class, $newUser);
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
            'is_admin' => true
        ]);
    }

    /** @test */
    public function it_can_update_user_attributes()
    {
        $updatedData = [
            'name' => 'Updated User',
            'email' => 'updated_' . uniqid() . '@example.com'
        ];

        $this->user->update($updatedData);
        $this->user->refresh();

        foreach ($updatedData as $key => $value) {
            $this->assertEquals($value, $this->user->$key);
        }
    }
}
