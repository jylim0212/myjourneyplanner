<?php

namespace Database\Factories;

use App\Models\Recommendation;
use App\Models\Journey;
use Illuminate\Database\Eloquent\Factories\Factory;

class RecommendationFactory extends Factory
{
    protected $model = Recommendation::class;

    public function definition()
    {
        return [
            'journey_id' => Journey::factory(),
            'current_location' => $this->faker->city(),
            'recommendation' => $this->faker->paragraph(),
            'generated_at' => now(),
        ];
    }
} 