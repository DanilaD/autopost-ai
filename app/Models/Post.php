<?php

namespace App\Models;

use App\Enums\PostStatus;
use App\Enums\PostType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Post Model
 *
 * Represents Instagram posts scheduled or published by users.
 *
 * @property int $id
 * @property int $company_id
 * @property int $created_by
 * @property int $instagram_account_id
 * @property PostType $type
 * @property string|null $title
 * @property string|null $caption
 * @property array|null $hashtags
 * @property array|null $mentions
 * @property \Carbon\Carbon|null $scheduled_at
 * @property \Carbon\Carbon|null $published_at
 * @property PostStatus $status
 * @property string|null $failure_reason
 * @property int $publish_attempts
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Post extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'created_by',
        'instagram_account_id',
        'type',
        'title',
        'caption',
        'hashtags',
        'mentions',
        'scheduled_at',
        'published_at',
        'status',
        'failure_reason',
        'publish_attempts',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'hashtags' => 'array',
        'mentions' => 'array',
        'metadata' => 'array',
        'status' => PostStatus::class,
        'type' => PostType::class,
    ];

    /**
     * Get the company that owns the post
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the post
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the Instagram account
     */
    public function instagramAccount(): BelongsTo
    {
        return $this->belongsTo(InstagramAccount::class);
    }

    /**
     * Get the post media (images/videos)
     */
    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('order');
    }

    /**
     * Scope: Filter by company
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, PostStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeOfType($query, PostType $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Due for publishing (scheduled and ready)
     */
    public function scopeDueForPublishing($query)
    {
        return $query->where('status', PostStatus::SCHEDULED)
            ->where('scheduled_at', '<=', now());
    }

    /**
     * Scope: Filter by Instagram account
     */
    public function scopeForAccount($query, int $accountId)
    {
        return $query->where('instagram_account_id', $accountId);
    }

    /**
     * Check if post can be edited
     */
    public function canBeEdited(): bool
    {
        return $this->status->isEditable();
    }

    /**
     * Check if post is ready to publish
     */
    public function isReadyToPublish(): bool
    {
        return $this->status->isReadyToPublish();
    }

    /**
     * Get formatted caption with hashtags
     */
    public function getFormattedCaptionAttribute(): string
    {
        $caption = $this->caption ?? '';

        if ($this->hashtags) {
            $hashtags = collect($this->hashtags)
                ->map(fn ($tag) => str_starts_with($tag, '#') ? $tag : '#'.$tag)
                ->join(' ');
            $caption .= ' '.$hashtags;
        }

        return trim($caption);
    }

    /**
     * Get post summary for display
     */
    public function getSummaryAttribute(): string
    {
        $caption = $this->caption ?? '';

        return strlen($caption) > 100 ? substr($caption, 0, 100).'...' : $caption;
    }
}
