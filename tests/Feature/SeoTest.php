<?php

namespace Tests\Feature;

use App\Models\AiModel;
use App\Models\BenchmarkArabic;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_exposes_canonical_and_social_metadata(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('<title>مقارنة نماذج الذكاء الاصطناعي بالعربية: أسعار وسرعة ودقة | إدراك Lab</title>', false);
        $response->assertSee('<link rel="canonical" href="'.route('home').'">', false);
        $response->assertSee('<meta name="robots" content="index, follow', false);
        $response->assertSee('<meta property="og:title"', false);
        $response->assertSee('<meta property="og:url" content="'.route('home').'">', false);
        $response->assertSee('<meta property="og:locale" content="ar_AR">', false);
        $response->assertSee('<meta property="og:description" content="قارن أسعار وسرعة ودقة', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image">', false);
        $response->assertSee('<meta name="application-name" content="إدراك Lab">', false);
        $response->assertSee('<link rel="alternate" hreflang="ar"', false);
    }

    public function test_home_page_emits_organization_and_leaderboard_structured_data(): void
    {
        $model = AiModel::factory()->create(['name' => 'Alpha Model']);

        BenchmarkArabic::factory()->create([
            'model_id' => $model->id,
            'tested_at' => now(),
            'total_score' => 150,
            'tokens_per_word' => 2.5,
            'tokens_per_second' => 90,
            'latency_seconds' => 0.8,
        ]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('application/ld+json', false);
        $response->assertSee('"@type":"Organization"', false);
        $response->assertSee('"@type":"WebSite"', false);
        $response->assertSee('"@type":"WebPage"', false);
        $response->assertSee('"@type":"WebApplication"', false);
        $response->assertSee('"@type":"ItemList"', false);
        $response->assertSee('"@type":"Dataset"', false);
        $response->assertSee('"@type":"FAQPage"', false);
        $response->assertSee('"name":"Alpha Model"', false);
    }

    public function test_home_page_renders_crawlable_faq_content_matching_the_schema(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('id="faq"', false);
        $response->assertSee('ما هو إدراك Lab؟', false);
        $response->assertSee('"@type":"Question"', false);
        $response->assertSee('"@type":"Answer"', false);
    }

    public function test_robots_txt_allows_public_pages_and_blocks_the_admin_area(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
        $response->assertSee('Disallow: /admin');
        $response->assertSee('Sitemap: '.route('seo.sitemap'));
    }

    public function test_sitemap_lists_the_home_page_and_blog_pages(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $response->assertSee('<loc>'.route('home').'</loc>', false);
        $response->assertSee('<loc>'.route('blog.index').'</loc>', false);
        $response->assertSee('<loc>'.route('blog.show', 'arabic-token-efficiency-analysis').'</loc>', false);
        $response->assertSee('<changefreq>daily</changefreq>', false);
        $response->assertSee('xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"', false);
    }

    public function test_model_profile_renders_tokenizer_probe_and_service_spotlight(): void
    {
        $model = AiModel::factory()->create(['name' => 'Beta Arabic Model', 'slug' => 'beta-arabic-model']);
        BenchmarkArabic::factory()->create([
            'model_id' => $model->id,
            'tested_at' => now(),
            'total_score' => 144,
            'tokens_per_word' => 1.45,
        ]);

        $response = $this->get(route('models.show', $model));

        $response->assertOk();
        $response->assertSee('خلاصة تقييم المهندسين العرب');
        $response->assertSee('مختبر تقطيع النصوص العربية التفاعلي');
        $response->assertSee('خدمات وإضافات متوافقة مع Beta Arabic Model');
        $response->assertSee('أبحاث مرتبطة');
    }
}
