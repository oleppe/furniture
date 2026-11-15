<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArabicBenchmarkRun extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'arabic_benchmark_runs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'model_id',
        'openrouter_model_id',
        'thinking_mode',
        'thinking_level',
        'reasoning_tokens',
        'tokens_per_word',
        'arabic_words_count',
        'latency_seconds',
        'tokens_per_second',
        'is_valid_json',
        'total_score',
        'score_percentage',
        'math_score',
        'syntax_score',
        'dialect_score',
        'extraction_score',
        'constraints_score',
        'scores_breakdown',
        'actual_cost',
        'input_cost_per_1m',
        'output_cost_per_1m',
        'prompt_tokens',
        'completion_tokens',
        'total_tokens',
        'raw_response',
        'reasoning_content',
        'parsed_json',
        'status',
        'error_message',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_valid_json' => 'boolean',
            'math_score' => 'boolean',
            'syntax_score' => 'boolean',
            'dialect_score' => 'boolean',
            'extraction_score' => 'boolean',
            'constraints_score' => 'boolean',
            'scores_breakdown' => 'array',
            'parsed_json' => 'array',
            'tokens_per_word' => 'decimal:4',
            'latency_seconds' => 'decimal:3',
            'tokens_per_second' => 'decimal:2',
            'score_percentage' => 'decimal:2',
            'actual_cost' => 'decimal:8',
            'input_cost_per_1m' => 'decimal:6',
            'output_cost_per_1m' => 'decimal:6',
        ];
    }

    /**
     * Get the AI model associated with this benchmark run.
     *
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'model_id');
    }
}
