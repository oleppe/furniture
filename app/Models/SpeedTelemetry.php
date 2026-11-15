<?php

namespace App\Models;

use Database\Factories\SpeedTelemetryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SpeedTelemetry extends Model
{
    /** @use HasFactory<SpeedTelemetryFactory> */
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'speed_telemetry';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'model_id',
        'ttft_ms',
        'tokens_per_sec',
        'tested_at',
        'region',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ttft_ms' => 'decimal:2',
            'tokens_per_sec' => 'decimal:2',
            'tested_at' => 'datetime',
        ];
    }

    /**
     * Get the AI model associated with this speed telemetry.
     *
     * @return BelongsTo<AiModel, $this>
     */
    public function aiModel(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'model_id');
    }
}
