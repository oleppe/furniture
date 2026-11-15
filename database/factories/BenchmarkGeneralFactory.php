<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\BenchmarkGeneral;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BenchmarkGeneral>
 */
class BenchmarkGeneralFactory extends Factory
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
            'elo_rating' => fake()->randomFloat(2, 1100, 1400),
            'mmlu_score' => fake()->randomFloat(2, 60, 95),
            'code_eval_score' => fake()->randomFloat(2, 50, 96),
            'math_score' => fake()->randomFloat(2, 45, 98),
            'source_updated_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ];
    }
}
