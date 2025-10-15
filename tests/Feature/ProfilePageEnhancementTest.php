<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfilePageEnhancementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a company
        $this->company = Company::factory()->create([
            'name' => 'Test Company',
        ]);

        // Create a user with company
        $this->user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'current_company_id' => $this->company->id,
            'timezone' => 'America/New_York',
        ]);

        // Attach user to company with admin role
        $this->user->companies()->attach($this->company->id, [
            'role' => UserRole::ADMIN->value,
        ]);
    }

    /**
     * Test that profile page displays user avatar information correctly.
     */
    public function test_profile_page_displays_user_avatar_information(): void
    {
        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->has('company')
            ->where('company.name', 'Test Company')
            ->where('company.stats.user_role', 'admin')
            ->where('company.stats.team_members_count', 1)
        );
    }

    /**
     * Test that profile page shows company information when user has company.
     */
    public function test_profile_page_shows_company_information_when_user_has_company(): void
    {
        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->has('company')
            ->where('company.name', 'Test Company')
            ->has('company.stats')
            ->where('company.stats.user_role', 'admin')
            ->where('company.stats.team_members_count', 1)
            ->where('company.stats.instagram_accounts_count', 0)
        );
    }

    /**
     * Test that profile page shows no company message when user has no company.
     */
    public function test_profile_page_shows_no_company_message_when_user_has_no_company(): void
    {
        // Create user without company
        $userWithoutCompany = User::factory()->create([
            'current_company_id' => null,
        ]);

        $response = $this->actingAs($userWithoutCompany)->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->where('company', null)
        );
    }

    /**
     * Test that profile page displays correct company statistics.
     */
    public function test_profile_page_displays_correct_company_statistics(): void
    {
        // Add another user to the company
        $anotherUser = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $anotherUser->companies()->attach($this->company->id, [
            'role' => UserRole::USER->value,
        ]);

        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->has('company')
            ->where('company.stats.team_members_count', 2)
            ->where('company.stats.user_role', 'admin')
        );
    }

    /**
     * Test that profile page displays different roles correctly.
     */
    public function test_profile_page_displays_different_roles_correctly(): void
    {
        // Test with user role
        $regularUser = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $regularUser->companies()->attach($this->company->id, [
            'role' => UserRole::USER->value,
        ]);

        $response = $this->actingAs($regularUser)->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->where('company.stats.user_role', 'user')
        );

        // Test with network role
        $networkUser = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $networkUser->companies()->attach($this->company->id, [
            'role' => UserRole::NETWORK->value,
        ]);

        $response = $this->actingAs($networkUser)->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->where('company.stats.user_role', 'network')
        );
    }

    /**
     * Test that profile page includes timezone information in header.
     */
    public function test_profile_page_includes_timezone_information_in_header(): void
    {
        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->has('timezones')
            ->has('commonTimezones')
        );
    }

    /**
     * Test that profile page displays user information correctly.
     */
    public function test_profile_page_displays_user_information_correctly(): void
    {
        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->where('mustVerifyEmail', false)
        );
    }

    /**
     * Test that profile page handles users with multiple companies.
     */
    public function test_profile_page_handles_users_with_multiple_companies(): void
    {
        // Create another company
        $anotherCompany = Company::factory()->create([
            'name' => 'Another Company',
        ]);

        // Add user to second company
        $this->user->companies()->attach($anotherCompany->id, [
            'role' => UserRole::USER->value,
        ]);

        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->has('company')
            ->where('company.name', 'Test Company') // Should show current company
            ->where('company.stats.user_role', 'admin') // Role in current company
        );
    }

    /**
     * Test that profile page displays Instagram accounts count correctly.
     */
    public function test_profile_page_displays_instagram_accounts_count_correctly(): void
    {
        // Create Instagram accounts for the company
        $this->company->instagramAccounts()->create([
            'username' => 'test_account_1',
            'instagram_user_id' => '123456789',
            'account_type' => 'BUSINESS',
            'access_token' => 'test_token_1',
            'token_expires_at' => now()->addDays(60),
            'user_id' => $this->user->id,
        ]);

        $this->company->instagramAccounts()->create([
            'username' => 'test_account_2',
            'instagram_user_id' => '987654321',
            'account_type' => 'BUSINESS',
            'access_token' => 'test_token_2',
            'token_expires_at' => now()->addDays(60),
            'user_id' => $this->user->id,
        ]);

        $response = $this->actingAs($this->user)->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->where('company.stats.instagram_accounts_count', 2)
        );
    }

    /**
     * Test that profile page works with users who have UTC timezone.
     */
    public function test_profile_page_works_with_users_with_utc_timezone(): void
    {
        $userWithUtcTimezone = User::factory()->create([
            'timezone' => 'UTC',
            'current_company_id' => $this->company->id,
        ]);
        $userWithUtcTimezone->companies()->attach($this->company->id, [
            'role' => UserRole::USER->value,
        ]);

        $response = $this->actingAs($userWithUtcTimezone)->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Profile/Edit')
            ->has('timezones')
            ->has('commonTimezones')
        );
    }
}
