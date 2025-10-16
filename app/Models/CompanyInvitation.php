<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CompanyInvitation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'email',
        'role',
        'invited_by',
        'invited_at',
        'accepted_at',
        'expires_at',
        'token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invitation) {
            if (! $invitation->token) {
                $invitation->token = Str::random(64);
            }
            if (! $invitation->invited_at) {
                $invitation->invited_at = now();
            }
            if (! $invitation->expires_at) {
                $invitation->expires_at = now()->addDays(7); // 7 days expiry
            }
        });
    }

    /**
     * Get the company that owns the invitation
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who sent the invitation
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Check if the invitation is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if the invitation is accepted
     */
    public function isAccepted(): bool
    {
        return ! is_null($this->accepted_at);
    }

    /**
     * Check if the invitation is pending
     */
    public function isPending(): bool
    {
        return ! $this->isAccepted() && ! $this->isExpired();
    }

    /**
     * Scope: Get pending invitations
     */
    public function scopePending($query)
    {
        return $query->whereNull('accepted_at')
            ->where('expires_at', '>', now());
    }

    /**
     * Scope: Get expired invitations
     */
    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now())
            ->whereNull('accepted_at');
    }

    /**
     * Scope: Get accepted invitations
     */
    public function scopeAccepted($query)
    {
        return $query->whereNotNull('accepted_at');
    }
}
