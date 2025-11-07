<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AI Generation Model
 *
 * This model represents AI-generated content including captions, images,
 * videos, and content plans. It tracks the generation details, costs,
 * and metadata for analytics and billing purposes.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */
class AiGeneration extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'type',
        'provider',
        'model',
        'prompt',
        'result',
        'tokens_used',
        'cost_credits',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'tokens_used' => 'integer',
        'cost_credits' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the company that owns the AI generation.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user that created the AI generation.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter by generation type.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

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
     * Scope to filter by company.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope to filter by user.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Get the cost in USD.
     */
    public function getCostUsdAttribute(): float
    {
        return $this->cost_credits / 1000000; // Convert from micro-dollars
    }

    /**
     * Set the cost in USD.
     */
    public function setCostUsdAttribute(float $value): void
    {
        $this->cost_credits = (int) ($value * 1000000); // Convert to micro-dollars
    }

    /**
     * Get the generation type display name.
     */
    public function getTypeDisplayNameAttribute(): string
    {
        return match ($this->type) {
            'caption' => 'Caption',
            'image' => 'Image',
            'video' => 'Video',
            'plan' => 'Content Plan',
            'hashtags' => 'Hashtags',
            'description' => 'Description',
            'chat' => 'Chat Response',
            default => ucfirst($this->type),
        };
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
}
