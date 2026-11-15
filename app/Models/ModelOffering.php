<?php

namespace App\Models;

use Database\Factories\ModelOfferingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModelOffering extends Model
{
    /** @use HasFactory<ModelOfferingFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'model_offerings';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'model_id',
        'provider_id',
        'endpoint_key',
        'host_type',
        'region',
        'input_cost_per_1m',
        'output_cost_per_1m',
        'batch_input_cost_per_1m',
        'batch_output_cost_per_1m',
        'flex_input_cost_per_1m',
        'flex_output_cost_per_1m',
        'priority_input_cost_per_1m',
        'priority_output_cost_per_1m',
        'context_tier_above_272k_input_cost_per_1m',
        'context_tier_above_272k_output_cost_per_1m',
        'cache_read_input_token_cost_per_1m',
        'cache_creation_input_token_cost_per_1m',
        'regional_processing_uplift_eu',
        'regional_processing_uplift_us',
        'tokens_per_sec',
        'ttft_ms',
        'is_official_direct',
        'is_cheapest',
        'raw_offering_spec',
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
            'batch_input_cost_per_1m' => 'decimal:6',
            'batch_output_cost_per_1m' => 'decimal:6',
            'flex_input_cost_per_1m' => 'decimal:6',
            'flex_output_cost_per_1m' => 'decimal:6',
            'priority_input_cost_per_1m' => 'decimal:6',
            'priority_output_cost_per_1m' => 'decimal:6',
            'context_tier_above_272k_input_cost_per_1m' => 'decimal:6',
            'context_tier_above_272k_output_cost_per_1m' => 'decimal:6',
            'cache_read_input_token_cost_per_1m' => 'decimal:6',
            'cache_creation_input_token_cost_per_1m' => 'decimal:6',
            'regional_processing_uplift_eu' => 'decimal:3',
            'regional_processing_uplift_us' => 'decimal:3',
            'tokens_per_sec' => 'decimal:2',
            'ttft_ms' => 'decimal:2',
            'is_official_direct' => 'boolean',
            'is_cheapest' => 'boolean',
            'raw_offering_spec' => 'array',
        ];
    }

    /**
     * Get the canonical AI model for this offering.
     *
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'model_id');
    }

    /**
     * Get the hosting provider for this offering (e.g. Azure, AWS Bedrock, OpenAI Direct, Groq).
     *
     * @return BelongsTo<Provider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    /**
     * Scope offerings to official direct provider endpoints.
     */
    public function scopeDirect(Builder $query): Builder
    {
        return $query->where('host_type', 'direct');
    }

    /**
     * Scope offerings to enterprise cloud platforms (Azure, AWS Bedrock, Google Cloud Vertex).
     */
    public function scopeEnterpriseCloud(Builder $query): Builder
    {
        return $query->where('host_type', 'enterprise_cloud');
    }

    /**
     * Scope offerings to fast inference accelerators (Groq, Together AI, Fireworks, Cerebras).
     */
    public function scopeFastInference(Builder $query): Builder
    {
        return $query->where('host_type', 'fast_inference');
    }

    /**
     * Scope offerings to aggregators and gateways (OpenRouter).
     */
    public function scopeAggregator(Builder $query): Builder
    {
        return $query->where('host_type', 'aggregator');
    }

    /**
     * Scope offerings to the official direct offering.
     */
    public function scopeOfficialDirect(Builder $query): Builder
    {
        return $query->where('is_official_direct', true);
    }

    /**
     * Scope offerings to the cheapest available offering.
     */
    public function scopeCheapest(Builder $query): Builder
    {
        return $query->where('is_cheapest', true);
    }
}
