<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;

/**
 * Company Service
 *
 * Handles all business logic related to company management.
 * Extracted from Company model to follow the "no updates, only relationships" pattern.
 */
class CompanyService
{
    /**
     * Check if user is a member of this company
     */
    public function hasMember(Company $company, User $user): bool
    {
        return $company->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Get user's role in this company
     */
    public function getUserRole(Company $company, User $user): ?string
    {
        $pivot = $company->users()->where('user_id', $user->id)->first()?->pivot;

        return $pivot?->role;
    }

    /**
     * Check if company has any Instagram accounts connected.
     */
    public function hasInstagramAccounts(Company $company): bool
    {
        return $company->instagramAccounts()->exists();
    }

    /**
     * Get the primary/default Instagram account for this company.
     */
    public function getPrimaryInstagramAccount(Company $company): ?\App\Models\InstagramAccount
    {
        return $company->activeInstagramAccounts()->first();
    }

    /**
     * Switch user to a different company
     */
    public function switchUserCompany(User $user, Company $company): bool
    {
        if (! $user->companies->contains($company)) {
            return false;
        }

        $user->update(['current_company_id' => $company->id]);

        return true;
    }
}
