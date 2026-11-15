<?php

namespace Database\Factories;

use App\Models\BlogArticle;
use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<BlogArticle>
 */
class BlogArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'blog_category_id' => BlogCategory::factory(),
            'spotlight_model_id' => null,
            'report_id' => 'EL-TR-'.fake()->numberBetween(100, 999),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->paragraph(),
            'content' => null,
            'read_time' => '8 دقائق قراءة',
            'author_name' => 'فريق أبحاث إدراك Lab',
            'author_role' => 'مختبر اقتصاديات النماذج العربية',
            'key_metric_value' => '1.35 T/Word',
            'key_metric_label' => 'معامل التقطيع',
            'hero_metrics' => [
                ['value' => '1.35 T/Word', 'label' => 'معامل التقطيع', 'tone' => 'efficiency'],
            ],
            'takeaways' => [
                ['num' => '01', 'title' => 'ملاحظة رئيسية', 'body' => fake()->sentence()],
            ],
            'sections' => [
                ['id' => 'section-1', 'title' => '1. نظرة عامة', 'paragraphs' => [fake()->paragraph()]],
            ],
            'comparison_rows' => [],
            'blocks' => [
                'charts' => [],
                'tables' => [],
                'quoted_models' => [],
            ],
            'code_snippet' => null,
            'is_featured' => false,
            'is_published' => true,
            'published_at' => now(),
        ];
    }
}
