<?php

namespace Tests\Feature;

use App\Models\AiModel;
use App\Models\BenchmarkArabic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_renders_featured_study_articles_and_services_spotlight(): void
    {
        $model = AiModel::factory()->create(['name' => 'Claude 3.5 Sonnet', 'slug' => 'claude-3-5-sonnet']);
        BenchmarkArabic::factory()->create([
            'model_id' => $model->id,
            'tested_at' => now(),
            'total_score' => 148,
            'tokens_per_word' => 1.68,
            'tokens_per_second' => 82,
        ]);

        $response = $this->get(route('blog.index'));

        $response->assertOk();
        $response->assertSee('المدونة والأبحاث التقنية');
        $response->assertSee('تحليل كفاءة التوكن العربي');
        $response->assertSee('خدمات وإضافة إدراك Lab');
        $response->assertSee('Claude 3.5 Sonnet');
        $response->assertSee('"@type":"Blog"', false);
    }

    public function test_blog_article_page_renders_takeaways_benchmarks_and_spotlight(): void
    {
        $model = AiModel::factory()->create(['name' => 'Claude 3.5 Sonnet', 'slug' => 'claude-3-5-sonnet']);
        BenchmarkArabic::factory()->create([
            'model_id' => $model->id,
            'tested_at' => now(),
            'total_score' => 148,
            'tokens_per_word' => 1.68,
        ]);

        $response = $this->get(route('blog.show', 'arabic-token-efficiency-analysis'));

        $response->assertOk();
        $response->assertSee('ملخص تنفيذي للمطورين وقادة التقنية');
        $response->assertSee('DeepSeek-R1');
        $response->assertSee('Claude 3.5 Sonnet');
        $response->assertSee('MODEL SPOTLIGHT');
        $response->assertSee('PLUGIN & SERVICES', false);
        $response->assertSee('"@type":"TechArticle"', false);
    }

    public function test_blog_returns_404_for_unknown_slug(): void
    {
        $this->get('/blog/non-existent-slug')->assertNotFound();
    }
}
