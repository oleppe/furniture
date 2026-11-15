<?php

namespace Tests\Feature;

use App\Models\AiModel;
use App\Models\BenchmarkArabic;
use App\Models\BenchmarkGeneral;
use App\Services\ModelRankingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModelRankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_renders_ranked_models(): void
    {
        $model = $this->modelWithIntelligence('Alpha Model', 42.5);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Alpha Model');
        $response->assertSee('intelligence');
    }

    public function test_general_ranking_is_ordered_by_intelligence_index(): void
    {
        $lower = $this->modelWithIntelligence('Lower Model', 20.0);
        $higher = $this->modelWithIntelligence('Higher Model', 80.0);
        $middle = $this->modelWithIntelligence('Middle Model', 50.0);

        $general = app(ModelRankingService::class)->leaderboards()['general'];

        $this->assertSame(
            [$higher->id, $middle->id, $lower->id],
            $general->pluck('id')->all()
        );
    }

    public function test_arabic_ranking_is_ordered_by_arabic_score(): void
    {
        $lower = $this->modelWithArabicScore('Lower Arabic Model', 120);
        $higher = $this->modelWithArabicScore('Higher Arabic Model', 156);

        $arabic = app(ModelRankingService::class)->leaderboards()['arabic'];

        $this->assertSame(
            [$higher->id, $lower->id],
            $arabic->pluck('id')->all()
        );
    }

    public function test_arabic_tested_models_keep_their_benchmark_fields(): void
    {
        $model = AiModel::factory()->create(['name' => 'Arabic Model']);

        BenchmarkArabic::factory()->create([
            'model_id' => $model->id,
            'tested_at' => now(),
            'total_score' => 150,
            'tokens_per_word' => 2.5,
            'tokens_per_second' => 90,
            'latency_seconds' => 0.8,
        ]);

        $ranking = app(ModelRankingService::class);
        $row = collect($ranking->rows($ranking->leaderboards()['pool']))
            ->firstWhere('name', 'Arabic Model');

        $this->assertTrue($row['has_arabic']);
        $this->assertSame(150, $row['score']);
        $this->assertSame(2.5, $row['compression']);
        $this->assertSame(90.0, $row['tps']);
        $this->assertSame(156, $row['score_max']);
    }

    public function test_models_without_any_benchmark_are_excluded(): void
    {
        AiModel::factory()->create(['name' => 'Unranked Model']);
        $ranked = $this->modelWithIntelligence('Ranked Model', 10.0);

        $pool = app(ModelRankingService::class)->leaderboards()['pool'];

        $this->assertTrue($pool->contains($ranked));
        $this->assertFalse($pool->contains('name', 'Unranked Model'));
    }

    public function test_inactive_models_are_excluded(): void
    {
        $model = AiModel::factory()->create(['is_active' => false]);

        BenchmarkGeneral::factory()->create([
            'model_id' => $model->id,
            'intelligence_index' => 99.0,
        ]);

        $pool = app(ModelRankingService::class)->leaderboards()['pool'];

        $this->assertTrue($pool->isEmpty());
    }

    private function modelWithIntelligence(string $name, float $intelligence): AiModel
    {
        $model = AiModel::factory()->create(['name' => $name]);

        BenchmarkGeneral::factory()->create([
            'model_id' => $model->id,
            'intelligence_index' => $intelligence,
        ]);

        return $model;
    }

    private function modelWithArabicScore(string $name, int $score): AiModel
    {
        $model = AiModel::factory()->create(['name' => $name]);

        BenchmarkArabic::factory()->create([
            'model_id' => $model->id,
            'tested_at' => now(),
            'total_score' => $score,
            'tokens_per_word' => 2.0,
            'tokens_per_second' => 50,
            'latency_seconds' => 1.0,
        ]);

        return $model;
    }
}
