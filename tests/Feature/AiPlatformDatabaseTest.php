<?php

namespace Tests\Feature;

use App\Models\AiModel;
use App\Models\BenchmarkArabic;
use App\Models\BenchmarkGeneral;
use App\Models\ModelOffering;
use App\Models\ModelSpec;
use App\Models\Provider;
use App\Models\SpeedTelemetry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiPlatformDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_provider_with_models(): void
    {
        $provider = Provider::factory()->create([
            'name' => 'OpenAI',
            'slug' => 'openai',
            'website' => 'https://openai.com',
            'logo_url' => 'https://example.com/openai.png',
        ]);

        $this->assertDatabaseHas('providers', [
            'name' => 'OpenAI',
            'slug' => 'openai',
        ]);

        $model = AiModel::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'GPT-4o',
            'slug' => 'gpt-4o',
            'is_open_source' => false,
        ]);

        $this->assertCount(1, $provider->fresh()->models);
        $this->assertEquals('GPT-4o', $provider->models->first()->name);
        $this->assertEquals($provider->id, $model->provider->id);
    }

    public function test_can_create_model_specs_and_retrieve_via_relationship(): void
    {
        $model = AiModel::factory()->create([
            'name' => 'Claude 3.5 Sonnet',
            'slug' => 'claude-3-5-sonnet',
        ]);

        $spec = ModelSpec::factory()->create([
            'model_id' => $model->id,
            'input_cost_per_1m' => 3.00,
            'output_cost_per_1m' => 15.00,
            'context_window' => 200000,
            'max_output_tokens' => 8192,
        ]);

        $this->assertDatabaseHas('model_specs', [
            'model_id' => $model->id,
            'input_cost_per_1m' => '3.000000',
            'output_cost_per_1m' => '15.000000',
            'context_window' => 200000,
            'max_output_tokens' => 8192,
        ]);

        $this->assertNotNull($model->latestSpec);
        $this->assertEquals('3.000000', $model->latestSpec->input_cost_per_1m);
        $this->assertEquals($model->id, $spec->aiModel->id);
    }

    public function test_can_record_general_benchmarks(): void
    {
        $model = AiModel::factory()->create();

        $benchmark = BenchmarkGeneral::factory()->create([
            'model_id' => $model->id,
            'elo_rating' => 1285.50,
            'mmlu_score' => 88.70,
            'code_eval_score' => 90.20,
            'math_score' => 85.00,
        ]);

        $this->assertDatabaseHas('benchmarks_general', [
            'model_id' => $model->id,
            'elo_rating' => 1285.50,
            'mmlu_score' => 88.70,
        ]);

        $this->assertEquals(1285.50, (float) $model->fresh()->benchmarkGeneral->elo_rating);
    }

    public function test_can_record_arabic_benchmarks(): void
    {
        $model = AiModel::factory()->create();

        $arabicBenchmark = BenchmarkArabic::factory()->create([
            'model_id' => $model->id,
            'arabic_tokens_per_1k_words' => 1550.00,
            'token_inflation_ratio' => 1.5500,
            'effective_arabic_cost_per_1k_words' => 0.023250,
        ]);

        $this->assertDatabaseHas('benchmarks_arabic', [
            'model_id' => $model->id,
            'arabic_tokens_per_1k_words' => '1550.00',
            'token_inflation_ratio' => '1.5500',
            'effective_arabic_cost_per_1k_words' => '0.023250',
        ]);

        $this->assertEquals('1550.00', $model->fresh()->benchmarkArabic->arabic_tokens_per_1k_words);
    }

    public function test_can_record_speed_telemetry(): void
    {
        $model = AiModel::factory()->create();

        $telemetry = SpeedTelemetry::factory()->create([
            'model_id' => $model->id,
            'ttft_ms' => 245.50,
            'tokens_per_sec' => 85.20,
            'region' => 'me-central-1',
        ]);

        $this->assertDatabaseHas('speed_telemetry', [
            'model_id' => $model->id,
            'ttft_ms' => 245.50,
            'tokens_per_sec' => 85.20,
            'region' => 'me-central-1',
        ]);

        $this->assertCount(1, $model->fresh()->speedTelemetries);
        $this->assertEquals(245.50, (float) $model->fresh()->latestSpeed->ttft_ms);
    }

    public function test_can_create_model_offerings_and_query_relationships_and_scopes(): void
    {
        $creator = Provider::factory()->create(['name' => 'OpenAI', 'slug' => 'openai']);
        $azure = Provider::factory()->create(['name' => 'Microsoft Azure', 'slug' => 'azure']);
        $groq = Provider::factory()->create(['name' => 'Groq', 'slug' => 'groq']);

        $model = AiModel::factory()->create([
            'provider_id' => $creator->id,
            'name' => 'GPT-4o',
            'slug' => 'gpt-4o',
            'capabilities' => [
                'supports_vision' => true,
                'supports_function_calling' => true,
                'supports_prompt_caching' => true,
            ],
        ]);

        // Direct offering
        $direct = ModelOffering::factory()->create([
            'model_id' => $model->id,
            'provider_id' => $creator->id,
            'endpoint_key' => 'gpt-4o',
            'host_type' => 'direct',
            'region' => 'global',
            'input_cost_per_1m' => 2.50,
            'output_cost_per_1m' => 10.00,
            'is_official_direct' => true,
            'is_cheapest' => false,
        ]);

        // Azure EU offering
        $azureOffering = ModelOffering::factory()->create([
            'model_id' => $model->id,
            'provider_id' => $azure->id,
            'endpoint_key' => 'azure/eu/gpt-4o-2024-08-06',
            'host_type' => 'enterprise_cloud',
            'region' => 'eu',
            'input_cost_per_1m' => 2.75,
            'output_cost_per_1m' => 11.00,
            'regional_processing_uplift_eu' => 1.100,
            'is_official_direct' => false,
            'is_cheapest' => false,
        ]);

        // Groq offering (cheapest)
        $groqOffering = ModelOffering::factory()->create([
            'model_id' => $model->id,
            'provider_id' => $groq->id,
            'endpoint_key' => 'groq/gpt-4o',
            'host_type' => 'fast_inference',
            'region' => 'global',
            'input_cost_per_1m' => 1.90,
            'output_cost_per_1m' => 8.00,
            'is_official_direct' => false,
            'is_cheapest' => true,
        ]);

        // Assert relationships
        $this->assertCount(3, $model->fresh()->offerings);
        $this->assertEquals($direct->id, $model->officialOffering->id);
        $this->assertEquals($groqOffering->id, $model->cheapestOffering->id);
        $this->assertEquals($model->id, $direct->aiModel->id);
        $this->assertEquals($creator->id, $direct->provider->id);

        // Assert scopes
        $this->assertEquals(1, ModelOffering::direct()->count());
        $this->assertEquals(1, ModelOffering::enterpriseCloud()->count());
        $this->assertEquals(1, ModelOffering::fastInference()->count());
        $this->assertEquals(1, ModelOffering::officialDirect()->count());
        $this->assertEquals(1, ModelOffering::cheapest()->count());

        // Assert capabilities cast
        $this->assertTrue($model->capabilities['supports_vision']);
        $this->assertTrue($model->capabilities['supports_function_calling']);
    }

    public function test_cascade_delete_removes_associated_model_records(): void
    {
        $provider = Provider::factory()->create();
        $model = AiModel::factory()->create(['provider_id' => $provider->id]);

        ModelSpec::factory()->create(['model_id' => $model->id]);
        BenchmarkGeneral::factory()->create(['model_id' => $model->id]);
        BenchmarkArabic::factory()->create(['model_id' => $model->id]);
        SpeedTelemetry::factory()->create(['model_id' => $model->id]);
        ModelOffering::factory()->create(['model_id' => $model->id, 'provider_id' => $provider->id]);

        $this->assertDatabaseCount('models', 1);
        $this->assertDatabaseCount('model_specs', 1);
        $this->assertDatabaseCount('benchmarks_general', 1);
        $this->assertDatabaseCount('benchmarks_arabic', 1);
        $this->assertDatabaseCount('speed_telemetry', 1);
        $this->assertDatabaseCount('model_offerings', 1);

        $provider->delete();

        $this->assertDatabaseCount('providers', 0);
        $this->assertDatabaseCount('models', 0);
        $this->assertDatabaseCount('model_specs', 0);
        $this->assertDatabaseCount('benchmarks_general', 0);
        $this->assertDatabaseCount('benchmarks_arabic', 0);
        $this->assertDatabaseCount('speed_telemetry', 0);
        $this->assertDatabaseCount('model_offerings', 0);
    }
}
