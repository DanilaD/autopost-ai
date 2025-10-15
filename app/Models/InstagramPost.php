<?php

namespace App\Models;

use App\Enums\InstagramPostStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Instagram Post Model
 *
 * Represents a post (draft, scheduled, or published) to an Instagram account.
 * Tracks the full lifecycle from creation to publication.
 */
class InstagramPost extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'instagram_account_id',
        'user_id',
        'caption',
        'media_type',
        'media_urls',
        'instagram_post_id',
        'instagram_permalink',
        'scheduled_at',
        'published_at',
        'status',
        'error_message',
        'retry_count',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'media_urls' => 'array',
        'metadata' => 'array',
        'status' => InstagramPostStatus::class,
        'retry_count' => 'integer',
    ];

    /**
     * Get the Instagram account this post belongs to.
     */
    public function instagramAccount(): BelongsTo
    {
        return $this->belongsTo(InstagramAccount::class);
    }

    /**
     * Get the user who created this post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include posts by a specific user.
     */
    public function scopeByUser($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope a query to only include posts for a specific account.
     */
    public function scopeForAccount($query, InstagramAccount $account)
    {
        return $query->where('instagram_account_id', $account->id);
    }

    /**
     * Scope a query to only include posts with a specific status.
     */
    public function scopeWithStatus($query, InstagramPostStatus $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include draft posts.
     */
    public function scopeDrafts($query)
    {
        return $query->where('status', InstagramPostStatus::DRAFT);
    }

    /**
     * Scope a query to only include scheduled posts.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', InstagramPostStatus::SCHEDULED);
    }

    /**
     * Scope a query to only include published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', InstagramPostStatus::PUBLISHED);
    }

    /**
     * Scope a query to only include failed posts.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', InstagramPostStatus::FAILED);
    }

    /**
     * Scope a query to only include posts due for publishing.
     */
    public function scopeDueForPublishing($query)
    {
        return $query->where('status', InstagramPostStatus::SCHEDULED)
            ->where('scheduled_at', '<=', now());
    }

    /**
     * Check if this post is editable.
     */
    public function isEditable(): bool
    {
        return $this->status->isEditable();
    }

    /**
     * Check if this post can be cancelled.
     */
    public function isCancellable(): bool
    {
        return $this->status->isCancellable();
    }

    /**
     * Check if this post is in a final state.
     */
    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    /**
     * Mark this post as scheduled.
     */
    public function markAsScheduled(\DateTime $scheduledAt): void
    {
        $this->update([
            'status' => InstagramPostStatus::SCHEDULED,
            'scheduled_at' => $scheduledAt,
        ]);
    }

    /**
     * Mark this post as publishing.
     */
    public function markAsPublishing(): void
    {
        $this->update(['status' => InstagramPostStatus::PUBLISHING]);
    }

    /**
     * Mark this post as published.
     */
    public function markAsPublished(string $instagramPostId, string $permalink): void
    {
        $this->update([
            'status' => InstagramPostStatus::PUBLISHED,
            'instagram_post_id' => $instagramPostId,
            'instagram_permalink' => $permalink,
            'published_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Mark this post as failed.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => InstagramPostStatus::FAILED,
            'error_message' => $errorMessage,
            'retry_count' => $this->retry_count + 1,
        ]);
    }

    /**
     * Mark this post as cancelled.
     */
    public function markAsCancelled(): void
    {
        $this->update(['status' => InstagramPostStatus::CANCELLED]);
    }

    /**
     * Check if this post can be retried.
     */
    public function canRetry(): bool
    {
        return $this->status === InstagramPostStatus::FAILED && $this->retry_count < 3;
    }

    /**
     * Retry publishing this post.
     */
    public function retry(): void
    {
        if (! $this->canRetry()) {
            throw new \RuntimeException('Post cannot be retried');
        }

        $this->update([
            'status' => InstagramPostStatus::SCHEDULED,
            'scheduled_at' => now(),
            'error_message' => null,
        ]);
    }

    /**
     * Get a human-readable summary of this post.
     */
    public function getSummary(): string
    {
        $summary = $this->caption ? substr($this->caption, 0, 50) : 'No caption';

        if (strlen($this->caption ?? '') > 50) {
            $summary .= '...';
        }

        return $summary;
    }
}
