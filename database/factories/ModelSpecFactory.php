<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\ModelSpec;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModelSpec>
 */
class ModelSpecFactory extends Factory
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
            'input_cost_per_1m' => fake()->randomFloat(4, 0.05, 15.00),
            'output_cost_per_1m' => fake()->randomFloat(4, 0.20, 60.00),
            'context_window' => fake()->randomElement([8192, 16384, 32768, 65536, 128000, 200000, 1000000, 2000000]),
            'max_output_tokens' => fake()->randomElement([4096, 8192, 16384, 32768, 65536, 128000]),
        ];
    }
}
