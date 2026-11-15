<?php

namespace App\Models;

use Database\Factories\BenchmarkGeneralFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BenchmarkGeneral extends Model
{
    /** @use HasFactory<BenchmarkGeneralFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'benchmarks_general';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'model_id',
        'elo_rating',
        'intelligence_index',
        'coding_index',
        'agentic_index',
        'mmlu_score',
        'code_eval_score',
        'math_score',
        'design_arena',
        'raw_benchmarks',
        'source_updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'elo_rating' => 'decimal:2',
            'intelligence_index' => 'decimal:2',
            'coding_index' => 'decimal:2',
            'agentic_index' => 'decimal:2',
            'mmlu_score' => 'decimal:2',
            'code_eval_score' => 'decimal:2',
            'math_score' => 'decimal:2',
            'design_arena' => 'array',
            'raw_benchmarks' => 'array',
            'source_updated_at' => 'datetime',
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
