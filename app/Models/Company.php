<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'owner_id',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * Get the owner of the company
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all users that belong to this company
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get admin users (including network managers)
     */
    public function admins(): BelongsToMany
    {
        return $this->users()->wherePivotIn('role', ['admin', 'network']);
    }

    /**
     * Check if user is a member of this company
     */
    public function hasMember(User $user): bool
    {
        return $this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Get user's role in this company
     */
    public function getUserRole(User $user): ?string
    {
        $pivot = $this->users()->where('user_id', $user->id)->first()?->pivot;

        return $pivot?->role;
    }

    /**
     * Get all Instagram accounts for the company.
     */
    public function instagramAccounts(): HasMany
    {
        return $this->hasMany(InstagramAccount::class);
    }

    /**
     * Get all invitations for this company
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(CompanyInvitation::class);
    }

    /**
     * Get active Instagram accounts for the company.
     */
    public function activeInstagramAccounts(): HasMany
    {
        return $this->hasMany(InstagramAccount::class)->where('status', 'active');
    }

    /**
     * Get all posts made by company members to company accounts.
     */
    public function instagramPosts()
    {
        // Get all posts made to company-owned Instagram accounts
        return InstagramPost::whereHas('instagramAccount', function ($query) {
            $query->where('company_id', $this->id);
        });
    }

    /**
     * Check if company has any Instagram accounts connected.
     */
    public function hasInstagramAccounts(): bool
    {
        return $this->instagramAccounts()->exists();
    }

    /**
     * Get the primary/default Instagram account for this company.
     */
    public function getPrimaryInstagramAccount(): ?InstagramAccount
    {
        return $this->activeInstagramAccounts()->first();
    }
}
