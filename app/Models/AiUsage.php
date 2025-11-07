<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AI Usage Tracking Model
 *
 * This model tracks AI usage statistics for analytics, billing,
 * and cost management purposes. It aggregates usage data by
 * company, user, provider, and date.
 *
 * @version 1.0
 *
 * @since 2025-10-16
 */
class AiUsage extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ai_usage';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'company_id',
        'user_id',
        'provider',
        'model',
        'type',
        'tokens_used',
        'cost_usd',
        'requests_count',
        'usage_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tokens_used' => 'integer',
        'cost_usd' => 'decimal:6',
        'requests_count' => 'integer',
        'usage_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the company that owns the AI usage record.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user that generated the AI usage.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
     * Scope to filter by date range.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('usage_date', [$startDate, $endDate]);
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
     * Get the average cost per request.
     */
    public function getAverageCostPerRequestAttribute(): float
    {
        return $this->requests_count > 0 ? $this->cost_usd / $this->requests_count : 0;
    }

    /**
     * Get the average tokens per request.
     */
    public function getAverageTokensPerRequestAttribute(): float
    {
        return $this->requests_count > 0 ? $this->tokens_used / $this->requests_count : 0;
    }

    /**
     * Increment usage statistics.
     */
    public function incrementUsage(int $tokens = 0, float $cost = 0, int $requests = 1): void
    {
        $this->increment('tokens_used', $tokens);
        $this->increment('cost_usd', $cost);
        $this->increment('requests_count', $requests);
    }
}
