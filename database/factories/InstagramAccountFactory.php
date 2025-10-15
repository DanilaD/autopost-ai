<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\InstagramAccount;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Instagram Account Factory
 *
 * Creates test data for Instagram accounts.
 * Supports both user-owned and company-owned accounts.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstagramAccount>
 */
class InstagramAccountFactory extends Factory
{
    protected $model = InstagramAccount::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $username = fake()->unique()->userName();

        return [
            'username' => $username,
            'instagram_user_id' => fake()->unique()->numerify('##############'),
            'access_token' => fake()->sha256(),
            'token_expires_at' => now()->addDays(60),
            'account_type' => fake()->randomElement(['personal', 'business']),
            'profile_picture_url' => "https://via.placeholder.com/150?text={$username}",
            'followers_count' => fake()->numberBetween(100, 100000),
            'status' => 'active',
            'last_synced_at' => now(),
            'is_shared' => false,
            'metadata' => [
                'media_count' => fake()->numberBetween(0, 500),
                'follows_count' => fake()->numberBetween(50, 1000),
            ],
        ];
    }

    /**
     * Create a user-owned Instagram account.
     */
    public function forUser(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory(),
            'company_id' => null,
            'ownership_type' => 'user',
        ]);
    }

    /**
     * Create a company-owned Instagram account.
     */
    public function forCompany(?Company $company = null): static
    {
        return $this->state(fn (array $attributes) => [
            'company_id' => $company?->id ?? Company::factory(),
            'user_id' => null,
            'ownership_type' => 'company',
        ]);
    }

    /**
     * Create a shared account.
     */
    public function shared(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_shared' => true,
        ]);
    }

    /**
     * Create an account with an expired token.
     */
    public function withExpiredToken(): static
    {
        return $this->state(fn (array $attributes) => [
            'token_expires_at' => now()->subDays(1),
            'status' => 'expired',
        ]);
    }

    /**
     * Create an account with a token expiring soon.
     */
    public function withExpiringSoonToken(): static
    {
        return $this->state(fn (array $attributes) => [
            'token_expires_at' => now()->addDays(5),
        ]);
    }

    /**
     * Create an inactive account.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'disconnected',
        ]);
    }

    /**
     * Create a business account.
     */
    public function business(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => 'business',
        ]);
    }

    /**
     * Create a personal account.
     */
    public function personal(): static
    {
        return $this->state(fn (array $attributes) => [
            'account_type' => 'personal',
        ]);
    }
}
