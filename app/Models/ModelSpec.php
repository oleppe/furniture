<?php

namespace App\Models;

use Database\Factories\ModelSpecFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelSpec extends Model
{
    /** @use HasFactory<ModelSpecFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'model_specs';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'model_id',
        'input_cost_per_1m',
        'output_cost_per_1m',
        'input_cache_read_cost_per_1m',
        'input_cache_write_cost_per_1m',
        'input_cache_write_1h_cost_per_1m',
        'web_search_cost',
        'internal_reasoning_cost_per_1m',
        'image_input_cost',
        'image_output_cost',
        'audio_input_cost_per_1m',
        'audio_output_cost_per_1m',
        'pricing_overrides',
        'context_window',
        'max_output_tokens',
        'top_provider_context_length',
        'top_provider_max_completion_tokens',
        'is_moderated',
        'raw_pricing',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'input_cost_per_1m' => 'decimal:6',
            'output_cost_per_1m' => 'decimal:6',
            'input_cache_read_cost_per_1m' => 'decimal:6',
            'input_cache_write_cost_per_1m' => 'decimal:6',
            'input_cache_write_1h_cost_per_1m' => 'decimal:6',
            'web_search_cost' => 'decimal:4',
            'internal_reasoning_cost_per_1m' => 'decimal:6',
            'image_input_cost' => 'decimal:4',
            'image_output_cost' => 'decimal:4',
            'audio_input_cost_per_1m' => 'decimal:6',
            'audio_output_cost_per_1m' => 'decimal:6',
            'pricing_overrides' => 'array',
            'context_window' => 'integer',
            'max_output_tokens' => 'integer',
            'top_provider_context_length' => 'integer',
            'top_provider_max_completion_tokens' => 'integer',
            'is_moderated' => 'boolean',
            'raw_pricing' => 'array',
        ];
    }

    /**
     * Get the AI model associated with this spec.
     *
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'model_id');
    }
}
