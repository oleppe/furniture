<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AiModel>
 */
class AiModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'provider_id' => Provider::factory(),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'architecture' => fake()->randomElement(['Transformer Decoder-Only', 'Mixture of Experts (MoE)', 'Dense Transformer', 'Hybrid SSM-Transformer']),
            'release_date' => fake()->date(),
            'is_open_source' => fake()->boolean(40),
            'openrouter_id' => 'provider/'.Str::slug($name),
            'litellm_id' => Str::slug($name),
            'description' => fake()->paragraph(),
        ];
    }
}
