<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\SpeedTelemetry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SpeedTelemetry>
 */
class SpeedTelemetryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'model_id' => AiModel::factory(),
            'ttft_ms' => fake()->randomFloat(2, 120, 1500),
            'tokens_per_sec' => fake()->randomFloat(2, 25, 220),
            'tested_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'region' => fake()->randomElement(['us-east-1', 'eu-west-1', 'ap-southeast-1', 'me-central-1']),
        ];
    }
}
