<?php

namespace Tests\Feature;

use App\Models\AiModel;
use App\Models\ArabicBenchmarkRun;
use App\Models\BenchmarkArabic;
use App\Models\BenchmarkGeneral;
use App\Models\ModelSpec;
use App\Models\Provider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_renders_model_metrics_and_seo_metadata(): void
    {
        $model = $this->benchmarkedModel();

        $response = $this->get(route('models.show', $model));

        $response->assertOk();
        $response->assertSee('<h1 id="model-heading"', false);
        $response->assertSee($model->name);
        $response->assertSee('2.35', false);
        $response->assertSee('148', false);
        $response->assertSee('<link rel="canonical" href="'.route('models.show', $model).'">', false);
        $response->assertSee('<meta property="og:type" content="website">', false);
        $response->assertSee('"@type":"BreadcrumbList"', false);
        $response->assertSee('"@type":"SoftwareApplication"', false);
    }

    public function test_inactive_models_are_not_publicly_accessible(): void
    {
        $model = $this->benchmarkedModel(['is_active' => false]);

        $this->get(route('models.show', $model))->assertNotFound();
    }

    public function test_unknown_model_slug_returns_not_found(): void
    {
        $this->get('/models/does-not-exist')->assertNotFound();
    }

    public function test_home_leaderboard_links_to_model_profiles(): void
    {
        $model = $this->benchmarkedModel();

        $this->get('/')
            ->assertOk()
            ->assertSee(route('models.show', $model), false);
    }

    public function test_sitemap_includes_model_profile_pages(): void
    {
        $model = $this->benchmarkedModel();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>'.route('models.show', $model).'</loc>', false);
    }

    /**
     * Create a fully benchmarked, active model with a profile-ready dataset.
     *
     * @param  array<string, mixed>  $attributes
     */
    private function benchmarkedModel(array $attributes = []): AiModel
    {
        $provider = Provider::factory()->create(['name' => 'Anthropic']);

        $model = AiModel::factory()->create([
            'provider_id' => $provider->id,
            'name' => 'Claude Test Sonnet',
            'slug' => 'claude-test-sonnet',
            'is_active' => true,
            'capabilities' => [
                'supports_vision' => true,
                'supports_reasoning' => true,
                'supports_function_calling' => true,
            ],
            ...$attributes,
        ]);

        ModelSpec::create([
            'model_id' => $model->id,
            'input_cost_per_1m' => 3.0,
            'output_cost_per_1m' => 15.0,
            'context_window' => 200000,
            'max_output_tokens' => 8192,
        ]);

        BenchmarkGeneral::factory()->create([
            'model_id' => $model->id,
            'intelligence_index' => 72.5,
            'coding_index' => 68.0,
        ]);

        BenchmarkArabic::factory()->create([
            'model_id' => $model->id,
            'tested_at' => now(),
            'total_score' => 148,
            'tokens_per_word' => 2.35,
            'tokens_per_second' => 92.0,
            'latency_seconds' => 0.9,
            'effective_arabic_cost_per_1k_words' => 0.005,
        ]);

        ArabicBenchmarkRun::create([
            'model_id' => $model->id,
            'openrouter_model_id' => $model->openrouter_id,
            'status' => 'completed',
            'total_score' => 148,
            'scores_breakdown' => [
                'categories' => [
                    'math' => ['name' => 'الحساب والرياضيات', 'earned' => 17, 'points' => 18, 'percentage' => 94.4, 'status' => 'passed'],
                    'dialects' => ['name' => 'فهم اللهجات والسياق التداولي', 'earned' => 38, 'points' => 44, 'percentage' => 86.4, 'status' => 'passed'],
                ],
            ],
        ]);

        return $model;
    }
}
