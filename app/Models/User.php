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
        return $this->companies()->wherePivot('role', UserRole::ADMIN->value);
    }

    /**
     * Check if user has a specific role in a company
     */
    public function hasRole(Company $company, UserRole $role): bool
    {
        return $this->companies()
            ->where('company_id', $company->id)
            ->wherePivot('role', $role->value)
            ->exists();
    }

    /**
     * Check if user is admin in a company
     */
    public function isAdmin(Company $company): bool
    {
        return $this->hasRole($company, UserRole::ADMIN);
    }

    /**
     * Check if user is admin in current company
     */
    public function isAdminInCurrentCompany(): bool
    {
        if (! $this->current_company_id) {
            return false;
        }

        return $this->companies()
            ->where('company_id', $this->current_company_id)
            ->wherePivot('role', UserRole::ADMIN->value)
            ->exists();
    }

    /**
     * Get user's role in a specific company
     */
    public function getRoleIn(Company $company): ?UserRole
    {
        $pivot = $this->companies()
            ->where('company_id', $company->id)
            ->first()?->pivot;

        return $pivot ? UserRole::from($pivot->role) : null;
    }

    /**
     * Switch to a different company
     */
    public function switchCompany(Company $company): bool
    {
        if (! $this->companies->contains($company)) {
            return false;
        }

        $this->update(['current_company_id' => $company->id]);

        return true;
    }

    /**
     * Get all Instagram accounts owned by this user.
     */
    public function ownedInstagramAccounts(): HasMany
    {
        return $this->hasMany(InstagramAccount::class, 'user_id');
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
     * Check if user has any Instagram accounts.
     */
    public function hasInstagramAccounts(): bool
    {
        return $this->ownedInstagramAccounts()->exists()
            || $this->sharedInstagramAccounts()->exists()
            || $this->currentCompany?->instagramAccounts()->exists();
    }

    /**
     * Get the default Instagram account for this user.
     * Priority: User's first active account > Company's first active account > Any account
     */
    public function getDefaultInstagramAccount(): ?InstagramAccount
    {
        // Try user's own active accounts first
        $account = $this->ownedInstagramAccounts()
            ->where('status', 'active')
            ->first();

        if ($account) {
            return $account;
        }

        // Try company's active accounts
        if ($this->currentCompany) {
            $account = $this->currentCompany->instagramAccounts()
                ->where('status', 'active')
                ->first();

            if ($account) {
                return $account;
            }
        }

        // Fall back to any accessible account
        return $this->accessibleInstagramAccounts()->first();
    }

    /**
     * Get the admin who suspended this user.
     */
    public function suspendedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    /**
     * Suspend this user account.
     *
     * @param  string  $reason  Reason for suspension
     * @param  User  $suspendedBy  Admin user performing the suspension
     */
    public function suspend(string $reason, User $suspendedBy): bool
    {
        return $this->update([
            'suspended_at' => now(),
            'suspended_by' => $suspendedBy->id,
            'suspension_reason' => $reason,
        ]);
    }

    /**
     * Unsuspend this user account.
     */
    public function unsuspend(): bool
    {
        return $this->update([
            'suspended_at' => null,
            'suspended_by' => null,
            'suspension_reason' => null,
        ]);
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

    /**
     * Get user statistics for admin panel.
     */
    public function getStatsAttribute(): array
    {
        return [
            'companies_count' => $this->companies()->count(),
            'instagram_accounts_count' => $this->accessibleInstagramAccounts()->count(),
            'posts_count' => $this->instagramPosts()->count(),
            'last_login' => $this->last_login_at?->diffForHumans(),
            'account_age_days' => $this->created_at->diffInDays(now()),
        ];
    }
}
