<?php

namespace Database\Factories;

use App\Models\AiModel;
use App\Models\ModelOffering;
use App\Models\Provider;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ModelOffering>
 */
class ModelOfferingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $input = fake()->randomFloat(4, 0.50, 15.00);
        $output = fake()->randomFloat(4, 2.00, 60.00);

        return [
            'model_id' => AiModel::factory(),
            'provider_id' => Provider::factory(),
            'endpoint_key' => fake()->unique()->word().'/'.fake()->slug(),
            'host_type' => fake()->randomElement(['direct', 'enterprise_cloud', 'fast_inference', 'aggregator']),
            'region' => fake()->randomElement(['global', 'us', 'eu', 'apac']),
            'input_cost_per_1m' => $input,
            'output_cost_per_1m' => $output,
            'batch_input_cost_per_1m' => round($input * 0.5, 6),
            'batch_output_cost_per_1m' => round($output * 0.5, 6),
            'flex_input_cost_per_1m' => round($input * 0.4, 6),
            'flex_output_cost_per_1m' => round($output * 0.4, 6),
            'priority_input_cost_per_1m' => round($input * 1.5, 6),
            'priority_output_cost_per_1m' => round($output * 1.5, 6),
            'context_tier_above_272k_input_cost_per_1m' => round($input * 2.0, 6),
            'context_tier_above_272k_output_cost_per_1m' => round($output * 1.5, 6),
            'cache_read_input_token_cost_per_1m' => round($input * 0.1, 6),
            'cache_creation_input_token_cost_per_1m' => round($input * 1.25, 6),
            'regional_processing_uplift_eu' => 1.100,
            'regional_processing_uplift_us' => 1.000,
            'tokens_per_sec' => fake()->randomFloat(2, 40, 250),
            'ttft_ms' => fake()->randomFloat(2, 100, 800),
            'is_official_direct' => false,
            'is_cheapest' => false,
            'raw_offering_spec' => [],
        ];
    }
}
