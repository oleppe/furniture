<?php

namespace App\Models;

use Database\Factories\ProviderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Provider extends Model
{
    /** @use HasFactory<ProviderFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'slug',
        'website',
        'logo_url',
    ];

    /**
     * Get the AI models associated with this provider.
     *
     * @return HasMany<AiModel, $this>
     */
    public function models(): HasMany
    {
        return $this->hasMany(AiModel::class, 'provider_id');
    }

    /**
     * Get the model offerings hosted by this provider.
     *
     * @return HasMany<ModelOffering, $this>
     */
    public function offerings(): HasMany
    {
        return $this->hasMany(ModelOffering::class, 'provider_id');
    }
}
