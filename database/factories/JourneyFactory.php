<?php

namespace Database\Factories;

use App\Models\Journey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JourneyFactory extends Factory
{
    protected $model = Journey::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'journey_name' => $this->faker->sentence(3),
            'starting_location' => $this->faker->city(),
            'start_date' => now(),
            'end_date' => now()->addDays(5),
            'preferred_events' => $this->faker->words(3, true),
        ];
    }
} 