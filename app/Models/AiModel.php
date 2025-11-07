<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * AI Model Configuration
 *
 * This model represents AI model configurations including pricing,
 * capabilities, and availability for different providers.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */
class AiModel extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'provider',
        'type',
        'cost_per_token',
        'cost_per_image',
        'is_active',
        'capabilities',
        'description',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cost_per_token' => 'decimal:6',
        'cost_per_image' => 'decimal:6',
        'is_active' => 'boolean',
        'capabilities' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scope to filter by provider.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByProvider($query, string $provider)
    {
        return $query->where('provider', $provider);
    }

    /**
     * Scope to filter by type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter active models.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by capability.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithCapability($query, string $capability)
    {
        return $query->whereJsonContains('capabilities', $capability);
    }

    /**
     * Get the provider display name.
     */
    public function getProviderDisplayNameAttribute(): string
    {
        return match ($this->provider) {
            'openai' => 'OpenAI',
            'anthropic' => 'Anthropic',
            'google' => 'Google AI',
            'local' => 'Local AI',
            default => ucfirst($this->provider),
        };
    }

    /**
     * Get the type display name.
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match ($this->type) {
            'text' => 'Text Generation',
            'image' => 'Image Generation',
            'video' => 'Video Generation',
            'multimodal' => 'Multimodal',
            default => ucfirst($this->type),
        };
    }

    /**
     * Check if the model supports a specific capability.
     */
    public function supportsCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? []);
    }

    /**
     * Get the cost for a specific number of tokens.
     */
    public function getCostForTokens(int $tokens): float
    {
        return $this->cost_per_token * $tokens;
    }

    /**
     * Get the cost for a specific number of images.
     */
    public function getCostForImages(int $images): float
    {
        return $this->cost_per_image * $images;
    }

    /**
     * Check if the model is free to use.
     */
    public function isFree(): bool
    {
        return $this->cost_per_token == 0 && $this->cost_per_image == 0;
    }
}
