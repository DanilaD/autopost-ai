<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * Get the company that owns the Instagram account.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
}
