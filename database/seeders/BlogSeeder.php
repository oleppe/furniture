<?php

namespace Database\Seeders;

use App\Http\Controllers\BlogController;
use App\Models\AiModel;
use App\Models\BlogArticle;
use App\Models\BlogCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class BlogSeeder extends Seeder
{
    /**
     * Seed default research blog categories and curated studies.
     */
    public function run(): void
    {
        $categoryMap = [
            'tokenomics' => [
                'name' => 'التوكن العربي',
                'description' => 'تحليلات معمقة لكفاءة تقطيع اللغة العربية وتكلفة التوكنات.',
                'color' => 'primary',
                'sort_order' => 1,
            ],
            'benchmarks' => [
                'name' => 'المعايير القياسية',
                'description' => 'نتائج الاختبارات المعيارية واللهجات وزمن الاستجابة.',
                'color' => 'secondary',
                'sort_order' => 2,
            ],
            'economics' => [
                'name' => 'اقتصاديات النماذج',
                'description' => 'استراتيجيات خفض فاتورة الـ API والتخزين المؤقت للسياق.',
                'color' => 'amber',
                'sort_order' => 3,
            ],
            'doc-ai' => [
                'name' => 'المستندات و Doc AI',
                'description' => 'أتمتة العقود والفواتير العربية وهندسة أنظمة RAG.',
                'color' => 'emerald',
                'sort_order' => 4,
            ],
        ];

        $categoryIds = [];

        foreach ($categoryMap as $slug => $meta) {
            $category = BlogCategory::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $meta['name'],
                    'description' => $meta['description'],
                    'color' => $meta['color'],
                    'sort_order' => $meta['sort_order'],
                    'is_active' => true,
                ]
            );
            $categoryIds[$slug] = $category->id;
        }

        foreach (BlogController::defaultPosts() as $post) {
            $spotlightModel = null;
            if (! empty($post['spotlight_model_slug'])) {
                $spotlightModel = AiModel::where('slug', $post['spotlight_model_slug'])->first();
            }

            $blocks = [
                'charts' => $post['charts'] ?? [],
                'tables' => $post['tables'] ?? [],
                'quoted_models' => $post['quoted_models'] ?? [],
            ];

            if ($post['slug'] === 'arabic-token-efficiency-analysis' && empty($blocks['charts'])) {
                $blocks['charts'] = [
                    [
                        'title' => 'مقارنة معامل التقطيع العربي (توكن / كلمة)',
                        'subtitle' => 'القيمة الأقل تعني كفاءة أعلى وتكلفة أقل لنفس النص العربي',
                        'unit' => 'T/Word',
                        'bars' => [
                            ['label' => 'DeepSeek-R1', 'value' => 1.28, 'display_value' => '1.28', 'color' => 'emerald', 'highlight' => true],
                            ['label' => 'Qwen 2.5 72B', 'value' => 1.35, 'display_value' => '1.35', 'color' => 'primary', 'highlight' => false],
                            ['label' => 'Llama 3.3 70B', 'value' => 1.44, 'display_value' => '1.44', 'color' => 'indigo', 'highlight' => false],
                            ['label' => 'GPT-4o', 'value' => 1.52, 'display_value' => '1.52', 'color' => 'amber', 'highlight' => false],
                            ['label' => 'Claude 3.5 Sonnet', 'value' => 1.68, 'display_value' => '1.68', 'color' => 'rose', 'highlight' => false],
                        ],
                    ],
                ];
            }

            BlogArticle::updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'blog_category_id' => $categoryIds[$post['category_key']] ?? null,
                    'spotlight_model_id' => $spotlightModel?->id,
                    'report_id' => $post['report_id'] ?? null,
                    'title' => $post['title'],
                    'excerpt' => $post['excerpt'],
                    'read_time' => $post['read_time'] ?? '8 دقائق قراءة',
                    'author_name' => $post['author'] ?? 'فريق أبحاث إدراك Lab',
                    'author_role' => $post['author_role'] ?? 'مختبر اقتصاديات النماذج العربية',
                    'key_metric_value' => $post['key_metric_value'] ?? null,
                    'key_metric_label' => $post['key_metric_label'] ?? null,
                    'hero_metrics' => $post['hero_metrics'] ?? [],
                    'takeaways' => $post['takeaways'] ?? [],
                    'sections' => $post['sections'] ?? [],
                    'comparison_rows' => $post['comparison_rows'] ?? [],
                    'blocks' => $blocks,
                    'code_snippet' => $post['code_snippet'] ?? null,
                    'is_featured' => (bool) ($post['featured'] ?? false),
                    'is_published' => true,
                    'published_at' => Carbon::parse($post['published_at'] ?? now()),
                ]
            );
        }
    }
}
