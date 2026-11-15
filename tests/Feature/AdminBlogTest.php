<?php

namespace Tests\Feature;

use App\Models\AiModel;
use App\Models\BenchmarkArabic;
use App\Models\BlogArticle;
use App\Models\BlogCategory;
use App\Models\ModelSpec;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminBlogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Blog Admin',
            'email' => 'admin@idraak.test',
            'password' => Hash::make('SecretPass123!'),
            'role' => 'admin',
        ]);

        $this->regularUser = User::create([
            'name' => 'Standard User',
            'email' => 'user@idraak.test',
            'password' => Hash::make('SecretPass123!'),
            'role' => 'user',
        ]);
    }

    public function test_non_admin_cannot_access_blog_management_routes(): void
    {
        $this->actingAs($this->regularUser)
            ->get(route('admin.blog.categories.index'))
            ->assertForbidden();

        $this->actingAs($this->regularUser)
            ->get(route('admin.blog.articles.index'))
            ->assertForbidden();
    }

    public function test_admin_can_crud_blog_categories(): void
    {
        $this->actingAs($this->admin)
            ->get(route('admin.blog.categories.index'))
            ->assertOk();

        $store = $this->actingAs($this->admin)->post(route('admin.blog.categories.store'), [
            'name' => 'ذكاء الأعمال العربي',
            'slug' => 'arabic-enterprise-ai',
            'description' => 'دراسات تطبيقية في قطاع الأعمال.',
            'color' => 'emerald',
            'sort_order' => 1,
            'is_active' => '1',
        ]);

        $store->assertRedirect(route('admin.blog.categories.index'));
        $category = BlogCategory::where('slug', 'arabic-enterprise-ai')->firstOrFail();

        $update = $this->actingAs($this->admin)->put(route('admin.blog.categories.update', $category), [
            'name' => 'الذكاء المؤسسي العربي',
            'slug' => 'arabic-enterprise-ai',
            'description' => 'وصف محدث.',
            'color' => 'primary',
            'sort_order' => 2,
            'is_active' => '1',
        ]);

        $update->assertRedirect(route('admin.blog.categories.index'));
        $this->assertSame('الذكاء المؤسسي العربي', $category->fresh()->name);

        $delete = $this->actingAs($this->admin)->delete(route('admin.blog.categories.destroy', $category));
        $delete->assertRedirect(route('admin.blog.categories.index'));
        $this->assertDatabaseMissing('blog_categories', ['id' => $category->id]);
    }

    public function test_admin_can_create_article_with_charts_tables_and_quoted_models_and_render_publicly(): void
    {
        $provider = Provider::create(['name' => 'DeepSeek', 'slug' => 'deepseek']);
        $model = AiModel::create([
            'provider_id' => $provider->id,
            'name' => 'DeepSeek R1',
            'slug' => 'deepseek-r1',
            'is_active' => true,
        ]);
        ModelSpec::create([
            'model_id' => $model->id,
            'input_cost_per_1m' => 0.55,
            'output_cost_per_1m' => 2.19,
        ]);
        BenchmarkArabic::factory()->create([
            'model_id' => $model->id,
            'tested_at' => now(),
            'total_score' => 142,
            'tokens_per_word' => 1.28,
            'tokens_per_second' => 95,
        ]);

        $category = BlogCategory::create([
            'name' => 'أبحاث الإنتاج',
            'slug' => 'production-studies',
            'color' => 'primary',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $createPage = $this->actingAs($this->admin)->get(route('admin.blog.articles.create'));
        $createPage->assertOk();
        $createPage->assertSee('DeepSeek R1');
        $createPage->assertSee('Chart Builder');
        $createPage->assertSee('Table Builder');
        $createPage->assertSee('Quote Models');

        $response = $this->actingAs($this->admin)->post(route('admin.blog.articles.store'), [
            'blog_category_id' => $category->id,
            'spotlight_model_id' => $model->id,
            'report_id' => 'EL-TR-2026-01',
            'title' => 'دراسة كفاءة النماذج العربية في الإنتاج',
            'slug' => 'arabic-production-efficiency-study',
            'excerpt' => 'ملخص هندسي لنتائج الاختبارات الحية.',
            'read_time' => '7 دقائق قراءة',
            'author_name' => 'فريق إدراك Lab',
            'key_metric_value' => '1.28 T/Word',
            'key_metric_label' => 'كفاءة التقطيع',
            'is_published' => '1',
            'is_featured' => '1',
            'hero_metrics_json' => json_encode([
                ['value' => '1.28 T/Word', 'label' => 'معامل التقطيع', 'tone' => 'efficiency'],
            ], JSON_UNESCAPED_UNICODE),
            'takeaways_json' => json_encode([
                ['num' => '01', 'title' => 'خفض التكلفة', 'body' => 'تحسن ملموس في التوكنات العربية.'],
            ], JSON_UNESCAPED_UNICODE),
            'sections_json' => json_encode([
                [
                    'id' => 'intro',
                    'title' => '1. منهجية الدراسة',
                    'paragraphs' => [
                        'هذه فقرة تمهيدية تشرح الاختبار العربي.',
                        '[model:deepseek-r1|quote="سجل أقل معامل تقطيع في اختبار العقود العربية"]',
                    ],
                ],
                [
                    'id' => 'results',
                    'title' => '2. النتائج المقارنة',
                    'paragraphs' => ['توضح الأرقام التالية الفارق العملي.'],
                ],
            ], JSON_UNESCAPED_UNICODE),
            'comparison_rows_json' => json_encode([
                ['model' => 'DeepSeek R1', 'tpw' => '1.28', 'arabic_score' => '142 / 156', 'cost_usd' => '$1.23', 'cost_sar' => '4.61 ر.س', 'highlight' => true],
            ], JSON_UNESCAPED_UNICODE),
            'charts_json' => json_encode([
                [
                    'title' => 'رسم بياني لسرعة المعالجة العربية',
                    'subtitle' => 'توكن في الثانية',
                    'unit' => 't/s',
                    'bars' => [
                        ['label' => 'DeepSeek R1', 'value' => 95, 'display_value' => '95', 'color' => 'emerald', 'highlight' => true],
                    ],
                ],
            ], JSON_UNESCAPED_UNICODE),
            'tables_json' => json_encode([
                [
                    'title' => 'جدول تفاصيل الاختبار المخصص',
                    'headers' => ['المعيار', 'النتيجة'],
                    'rows' => [['دقة العقود', '96.5%']],
                ],
            ], JSON_UNESCAPED_UNICODE),
            'quoted_models_json' => json_encode([
                [
                    'model_id' => $model->id,
                    'slug' => 'deepseek-r1',
                    'name' => 'DeepSeek R1',
                    'provider' => 'DeepSeek',
                    'badge' => 'توصية المختبر',
                    'quote' => 'خيار ممتاز لتقليص تكاليف المعالجة الكثيفة.',
                    'placement' => 'showcase',
                ],
            ], JSON_UNESCAPED_UNICODE),
        ]);

        $response->assertSessionHasNoErrors();
        $article = BlogArticle::where('slug', 'arabic-production-efficiency-study')->firstOrFail();
        $response->assertRedirect(route('admin.blog.articles.edit', $article));

        // Verify public blog index and article show render all blocks + live model metrics
        $indexRes = $this->get(route('blog.index'));
        $indexRes->assertOk();
        $indexRes->assertSee('دراسة كفاءة النماذج العربية في الإنتاج');
        $indexRes->assertSee('أبحاث الإنتاج');

        $showRes = $this->get(route('blog.show', 'arabic-production-efficiency-study'));
        $showRes->assertOk();
        $showRes->assertSee('سجل أقل معامل تقطيع في اختبار العقود العربية');
        $showRes->assertSee('رسم بياني لسرعة المعالجة العربية');
        $showRes->assertSee('جدول تفاصيل الاختبار المخصص');
        $showRes->assertSee('96.5%');
        $showRes->assertSee('توصية المختبر');
        $showRes->assertSee('142/156');
        $showRes->assertSee(route('models.show', $model));

        // Verify sitemap includes the new published article
        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>'.route('blog.show', 'arabic-production-efficiency-study').'</loc>', false);
    }

    public function test_admin_can_seed_defaults_toggle_status_and_delete_article(): void
    {
        $this->actingAs($this->admin)
            ->post(route('admin.blog.articles.seed'))
            ->assertRedirect(route('admin.blog.articles.index'));

        $this->assertGreaterThanOrEqual(6, BlogArticle::count());
        $this->assertGreaterThanOrEqual(4, BlogCategory::count());

        $article = BlogArticle::firstOrFail();
        $wasPublished = $article->is_published;

        $this->actingAs($this->admin)
            ->post(route('admin.blog.articles.toggle-published', $article))
            ->assertRedirect();
        $this->assertSame(! $wasPublished, $article->fresh()->is_published);

        $this->actingAs($this->admin)
            ->delete(route('admin.blog.articles.destroy', $article))
            ->assertRedirect(route('admin.blog.articles.index'));
        $this->assertDatabaseMissing('blog_articles', ['id' => $article->id]);
    }
}
