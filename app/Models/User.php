<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
}
