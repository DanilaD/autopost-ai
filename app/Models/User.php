<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_company_id',
        'locale',
        'timezone',
        'suspended_at',
        'suspended_by',
        'suspension_reason',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'suspended_at' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Get all companies this user belongs to
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get the current active company
     */
    public function currentCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }

    /**
     * Get companies where user is owner
     */
    public function ownedCompanies(): BelongsToMany
    {
        return $this->companies()->wherePivotIn('role', [UserRole::ADMIN->value, UserRole::NETWORK->value]);
    }

    /**
     * Get all Instagram accounts owned by this user.
     */
    public function ownedInstagramAccounts(): HasMany
    {
        return $this->hasMany(InstagramAccount::class, 'user_id');
    }

    /**
     * Alias for ownedInstagramAccounts for backward compatibility.
     */
    public function instagramAccounts(): HasMany
    {
        return $this->ownedInstagramAccounts();
    }

    /**
     * Get all Instagram accounts shared with this user.
     */
    public function sharedInstagramAccounts(): BelongsToMany
    {
        return $this->belongsToMany(InstagramAccount::class, 'instagram_account_user')
            ->withPivot(['can_post', 'can_manage', 'shared_at', 'shared_by_user_id'])
            ->withTimestamps()
            ->using(InstagramAccountUser::class);
    }

    /**
     * Get all Instagram accounts accessible by this user.
     * Includes owned accounts, company accounts, and shared accounts.
     */
    public function accessibleInstagramAccounts()
    {
        return InstagramAccount::accessibleBy($this);
    }

    /**
     * Get all posts created by this user.
     */
    public function instagramPosts(): HasMany
    {
        return $this->hasMany(InstagramPost::class);
    }

    /**
     * Get active Instagram accounts owned by this user.
     */
    public function activeInstagramAccounts(): HasMany
    {
        return $this->ownedInstagramAccounts()->where('status', 'active');
    }

    /**
     * Get the admin who suspended this user.
     */
    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    /**
     * Check if user account is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    /**
     * Scope to filter only active (non-suspended) users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereNull('suspended_at');
    }

    /**
     * Scope to filter only suspended users.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSuspended($query)
    {
        return $query->whereNotNull('suspended_at');
    }
}
