<?php

namespace App\Models;

use Database\Factories\BenchmarkArabicFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenchmarkArabic extends Model
{
    /** @use HasFactory<BenchmarkArabicFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'benchmarks_arabic';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'model_id',
        'arabic_tokens_per_1k_words',
        'tokens_per_word',
        'latency_seconds',
        'tokens_per_second',
        'total_score',
        'is_valid_json',
        'actual_cost',
        'scores_breakdown',
        'thinking_mode',
        'tested_at',
        'token_inflation_ratio',
        'effective_arabic_cost_per_1k_words',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'arabic_tokens_per_1k_words' => 'decimal:2',
            'tokens_per_word' => 'decimal:4',
            'latency_seconds' => 'decimal:3',
            'tokens_per_second' => 'decimal:2',
            'total_score' => 'integer',
            'is_valid_json' => 'boolean',
            'actual_cost' => 'decimal:8',
            'scores_breakdown' => 'array',
            'tested_at' => 'datetime',
            'token_inflation_ratio' => 'decimal:4',
            'effective_arabic_cost_per_1k_words' => 'decimal:6',
        ];
    }

    /**
     * Get the AI model associated with this benchmark.
     *
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'model_id');
    }
}
