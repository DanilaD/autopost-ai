<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use App\Services\CompanyService;

class PostPolicy
{
    public function __construct(
        private CompanyService $companyService
    ) {}

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Users can view posts if they have a company or are individual users
        return $user->currentCompany !== null || $user->posts()->exists();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Post $post): bool
    {
        // Users can view posts they created or posts from their company
        if ($post->created_by === $user->id) {
            return true;
        }

        if ($user->currentCompany && $post->company_id === $user->currentCompany->id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Users can create posts if they have a company or are individual users
        return $user->currentCompany !== null || true; // Individual users can always create posts
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Post $post): bool
    {
        // Users can update posts they created or posts from their company
        if ($post->created_by === $user->id) {
            return true;
        }

        if ($user->currentCompany && $post->company_id === $user->currentCompany->id) {
            // Check if user has permission to manage posts in the company
            $role = $this->companyService->getUserRole($user->currentCompany, $user);

            return in_array($role, ['admin', 'network']);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Post $post): bool
    {
        // Users can delete posts they created or posts from their company
        if ($post->created_by === $user->id) {
            return true;
        }

        if ($user->currentCompany && $post->company_id === $user->currentCompany->id) {
            // Check if user has permission to manage posts in the company
            $role = $this->companyService->getUserRole($user->currentCompany, $user);

            return in_array($role, ['admin', 'network']);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Post $post): bool
    {
        return $this->update($user, $post);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $this->delete($user, $post);
    }
}
