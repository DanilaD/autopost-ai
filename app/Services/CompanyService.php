<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
     * Create a new company for a user
     *
     * Business rules:
     * - User can only create one company per account
     * - User becomes the network (admin) of the company
     * - Company inherits user's timezone and locale settings
     *
     * @throws \InvalidArgumentException
     */
    public function createCompany(User $user, array $data): Company
    {
        // Check if user already has a company
        if ($user->currentCompany) {
            throw new \InvalidArgumentException('You already have a company. You can only create one company per account.');
        }

        return DB::transaction(function () use ($user, $data) {
            // Create the company
            $company = Company::create([
                'name' => $data['name'],
                'owner_id' => $user->id,
                'settings' => [
                    'timezone' => $user->timezone ?? 'UTC',
                    'locale' => $user->locale ?? 'en',
                ],
            ]);

            // Attach user to company as network (company admin)
            $company->users()->attach($user->id, ['role' => 'network']);

            // Set as current company
            $user->update(['current_company_id' => $company->id]);

            return $company;
        });
    }

    /**
     * Get formatted team members for company management
     *
     * @return \Illuminate\Support\Collection
     */
    public function getTeamMembers(Company $company)
    {
        return $company->users()
            ->withPivot(['role', 'accepted_at'])
            ->orderBy('pivot_accepted_at')
            ->get()
            ->map(function ($user) use ($company) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->pivot->role,
                    'joined_at' => $user->pivot->accepted_at,
                    'is_owner' => $user->id === $company->owner_id,
                ];
            });
    }
}
