<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Crypt;

class InstagramAccount extends Model
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
        'username',
        'instagram_user_id',
        'access_token',
        'token_expires_at',
        'account_type',
        'profile_picture_url',
        'followers_count',
        'status',
        'last_synced_at',
        'metadata',
        'is_shared',
        'ownership_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'token_expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'metadata' => 'array',
        'followers_count' => 'integer',
        'is_shared' => 'boolean',
    ];

    /**
     * The attributes that should be hidden.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'access_token',
    ];

    /**
     * Get the company that owns the Instagram account (for company-owned accounts).
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user that owns the Instagram account (for user-owned accounts).
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all users who have access to this Instagram account through sharing.
     * Includes permission flags (can_post, can_manage).
     */
    public function sharedWithUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'instagram_account_user')
            ->withPivot(['can_post', 'can_manage', 'shared_at', 'shared_by_user_id'])
            ->withTimestamps()
            ->using(InstagramAccountUser::class);
    }

    /**
     * Get all posts made through this Instagram account.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(InstagramPost::class);
    }

    /**
     * Encrypt/decrypt access token automatically.
     */
    protected function accessToken(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
        );
    }

    /**
     * Check if the access token is expired.
     */
    public function isTokenExpired(): bool
    {
        return $this->token_expires_at->isPast();
    }

    /**
     * Check if the access token is expiring soon (within 7 days).
     */
    public function isTokenExpiringSoon(): bool
    {
        return $this->token_expires_at->diffInDays(now()) <= 7;
    }

    /**
     * Check if the account is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Mark the account as expired.
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Mark the account as disconnected.
     */
    public function markAsDisconnected(): void
    {
        $this->update(['status' => 'disconnected']);
    }

    /**
     * Mark the account as active.
     */
    public function markAsActive(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Update the last synced timestamp.
     */
    public function touchLastSynced(): void
    {
        $this->update(['last_synced_at' => now()]);
    }

    /**
     * Scope a query to only include active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include accounts with expired tokens.
     */
    public function scopeExpiredTokens($query)
    {
        return $query->where('token_expires_at', '<', now());
    }

    /**
     * Scope a query to only include accounts with tokens expiring soon.
     */
    public function scopeExpiringSoon($query)
    {
        return $query->whereBetween('token_expires_at', [now(), now()->addDays(7)]);
    }

    /**
     * Scope a query to only include user-owned accounts.
     */
    public function scopeUserOwned($query)
    {
        return $query->where('ownership_type', 'user');
    }

    /**
     * Scope a query to only include company-owned accounts.
     */
    public function scopeCompanyOwned($query)
    {
        return $query->where('ownership_type', 'company');
    }

    /**
     * Scope a query to only include accounts owned by a specific user.
     */
    public function scopeOwnedBy($query, User $user)
    {
        return $query->where('user_id', $user->id);
    }

    /**
     * Scope a query to only include accounts accessible by a specific user.
     * This includes owned accounts and shared accounts.
     */
    public function scopeAccessibleBy($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            // User-owned accounts
            $q->where('user_id', $user->id)
                // Or company accounts where user is a member
                ->orWhereHas('company', function ($companyQuery) use ($user) {
                    $companyQuery->whereHas('users', function ($userQuery) use ($user) {
                        $userQuery->where('users.id', $user->id);
                    });
                })
                // Or accounts shared with this user
                ->orWhereHas('sharedWithUsers', function ($sharedQuery) use ($user) {
                    $sharedQuery->where('users.id', $user->id);
                });
        });
    }

    /**
     * Check if this account is owned by a user.
     */
    public function isUserOwned(): bool
    {
        return $this->ownership_type === 'user' && $this->user_id !== null;
    }

    /**
     * Check if this account is owned by a company.
     */
    public function isCompanyOwned(): bool
    {
        return $this->ownership_type === 'company' && $this->company_id !== null;
    }

    /**
     * Check if a specific user owns this account.
     */
    public function isOwnedBy(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * Check if a specific user has access to this account.
     */
    public function isAccessibleBy(User $user): bool
    {
        // Owner has access
        if ($this->isOwnedBy($user)) {
            return true;
        }

        // Check if user is member of the company that owns this account
        if ($this->company_id && $this->company?->hasMember($user)) {
            return true;
        }

        // Check if account is shared with this user
        return $this->sharedWithUsers()->where('users.id', $user->id)->exists();
    }

    /**
     * Check if a user can post to this account.
     */
    public function canUserPost(User $user): bool
    {
        // Owner can always post
        if ($this->isOwnedBy($user)) {
            return true;
        }

        // Check company membership (admins and members can post)
        if ($this->company_id && $this->company?->hasMember($user)) {
            return true;
        }

        // Check shared permissions
        $sharedUser = $this->sharedWithUsers()
            ->where('users.id', $user->id)
            ->first();

        return $sharedUser?->pivot?->can_post ?? false;
    }

    /**
     * Check if a user can manage this account (settings, reconnect, disconnect).
     */
    public function canUserManage(User $user): bool
    {
        // Owner can always manage
        if ($this->isOwnedBy($user)) {
            return true;
        }

        // Check if user is admin in the company that owns this account
        if ($this->company_id && $this->company?->getUserRole($user) === 'admin') {
            return true;
        }

        // Check shared permissions
        $sharedUser = $this->sharedWithUsers()
            ->where('users.id', $user->id)
            ->first();

        return $sharedUser?->pivot?->can_manage ?? false;
    }

    /**
     * Share this account with a user.
     */
    public function shareWith(User $user, bool $canPost = true, bool $canManage = false, ?User $sharedBy = null): void
    {
        $this->sharedWithUsers()->syncWithoutDetaching([
            $user->id => [
                'can_post' => $canPost,
                'can_manage' => $canManage,
                'shared_by_user_id' => $sharedBy?->id,
                'shared_at' => now(),
            ],
        ]);

        // Mark account as shared
        if (! $this->is_shared) {
            $this->update(['is_shared' => true]);
        }
    }

    /**
     * Revoke access for a user.
     */
    public function revokeAccessFor(User $user): void
    {
        $this->sharedWithUsers()->detach($user->id);

        // Update is_shared flag if no one has access anymore
        if ($this->sharedWithUsers()->count() === 0) {
            $this->update(['is_shared' => false]);
        }
    }

    /**
     * Get the display name for this account (username with ownership context).
     */
    public function getDisplayNameAttribute(): string
    {
        $name = "@{$this->username}";

        if ($this->isUserOwned() && $this->owner) {
            $name .= " ({$this->owner->name})";
        } elseif ($this->isCompanyOwned() && $this->company) {
            $name .= " ({$this->company->name})";
        }

        return $name;
    }
}
