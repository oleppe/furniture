<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\BenchmarkArabic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BenchmarkArabic>
 */
class BenchmarkArabicFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tokensPer1k = fake()->randomFloat(2, 1300, 2600);
        $inflationRatio = $tokensPer1k / 1000;
        $costPer1k = ($tokensPer1k / 1000000) * fake()->randomFloat(4, 0.50, 15.00);

        return [
            'model_id' => AiModel::factory(),
            'arabic_tokens_per_1k_words' => $tokensPer1k,
            'token_inflation_ratio' => $inflationRatio,
            'effective_arabic_cost_per_1k_words' => $costPer1k,
        ];
    }
}
