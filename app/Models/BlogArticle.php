<?php

namespace App\Models;

use Database\Factories\BlogArticleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogArticle extends Model
{
    /** @use HasFactory<BlogArticleFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'blog_category_id',
        'spotlight_model_id',
        'report_id',
        'title',
        'slug',
        'excerpt',
        'content',
        'read_time',
        'author_name',
        'author_role',
        'key_metric_value',
        'key_metric_label',
        'hero_metrics',
        'takeaways',
        'sections',
        'comparison_rows',
        'blocks',
        'code_snippet',
        'is_featured',
        'is_published',
        'published_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'hero_metrics' => 'array',
            'takeaways' => 'array',
            'sections' => 'array',
            'comparison_rows' => 'array',
            'blocks' => 'array',
            'is_featured' => 'boolean',
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    /**
     * Category to which this research article belongs.
     *
     * @return BelongsTo<BlogCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    /**
     * Spotlight AI model linked to this article.
     *
     * @return BelongsTo<AiModel, $this>
     */
    public function spotlightModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'spotlight_model_id');
    }
}
