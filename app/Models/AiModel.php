<?php

namespace App\Models;

use Database\Factories\AiModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AiModel extends Model
{
    /** @use HasFactory<AiModelFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'models';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'provider_id',
        'name',
        'slug',
        'canonical_slug',
        'hugging_face_id',
        'architecture',
        'architecture_details',
        'release_date',
        'is_open_source',
        'is_active',
        'is_featured',
        'openrouter_id',
        'litellm_id',
        'description',
        'capabilities',
        'supported_parameters',
        'default_parameters',
        'supported_voices',
        'reasoning_spec',
        'per_request_limits',
        'knowledge_cutoff',
        'expiration_date',
        'openrouter_details_url',
        'alias_target',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'release_date' => 'date',
            'is_open_source' => 'boolean',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'capabilities' => 'array',
            'architecture_details' => 'array',
            'supported_parameters' => 'array',
            'default_parameters' => 'array',
            'supported_voices' => 'array',
            'reasoning_spec' => 'array',
            'per_request_limits' => 'array',
            'alias_target' => 'array',
        ];
    }

    /**
     * Get the provider that owns this model.
     *
     * @return BelongsTo<Provider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'provider_id');
    }

    /**
     * Get all specs records for this model.
     *
     * @return HasMany<ModelSpec, $this>
     */
    public function specs(): HasMany
    {
        return $this->hasMany(ModelSpec::class, 'model_id');
    }

    /**
     * Get the latest spec for this model.
     *
     * @return HasOne<ModelSpec, $this>
     */
    public function latestSpec(): HasOne
    {
        return $this->hasOne(ModelSpec::class, 'model_id')->latestOfMany();
    }

    /**
     * Get the general benchmark score for this model.
     *
     * @return HasOne<BenchmarkGeneral, $this>
     */
    public function benchmarkGeneral(): HasOne
    {
        return $this->hasOne(BenchmarkGeneral::class, 'model_id')->latestOfMany();
    }

    /**
     * Get the Arabic benchmark score for this model.
     *
     * @return HasOne<BenchmarkArabic, $this>
     */
    public function benchmarkArabic(): HasOne
    {
        return $this->hasOne(BenchmarkArabic::class, 'model_id')->latestOfMany();
    }

    /**
     * Get all Arabic benchmark run records for this model.
     *
     * @return HasMany<ArabicBenchmarkRun, $this>
     */
    public function benchmarkRuns(): HasMany
    {
        return $this->hasMany(ArabicBenchmarkRun::class, 'model_id')->latest();
    }

    /**
     * Get the latest Arabic benchmark run for this model.
     *
     * @return HasOne<ArabicBenchmarkRun, $this>
     */
    public function latestBenchmarkRun(): HasOne
    {
        return $this->hasOne(ArabicBenchmarkRun::class, 'model_id')->latestOfMany();
    }

    /**
     * Get all speed telemetry measurements for this model.
     *
     * @return HasMany<SpeedTelemetry, $this>
     */
    public function speedTelemetries(): HasMany
    {
        return $this->hasMany(SpeedTelemetry::class, 'model_id');
    }

    /**
     * Get the latest speed telemetry measurement.
     *
     * @return HasOne<SpeedTelemetry, $this>
     */
    public function latestSpeed(): HasOne
    {
        return $this->hasOne(SpeedTelemetry::class, 'model_id')->latestOfMany();
    }

    /**
     * Get all hosting offerings/endpoints for this model across cloud & inference providers.
     *
     * @return HasMany<ModelOffering, $this>
     */
    public function offerings(): HasMany
    {
        return $this->hasMany(ModelOffering::class, 'model_id');
    }

    /**
     * Get the official direct provider offering for this model.
     *
     * @return HasOne<ModelOffering, $this>
     */
    public function officialOffering(): HasOne
    {
        return $this->hasOne(ModelOffering::class, 'model_id')->where('is_official_direct', true);
    }

    /**
     * Get the cheapest hosting offering available for this model.
     *
     * @return HasOne<ModelOffering, $this>
     */
    public function cheapestOffering(): HasOne
    {
        return $this->hasOne(ModelOffering::class, 'model_id')->where('is_cheapest', true);
    }
}
