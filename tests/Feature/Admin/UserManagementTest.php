<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $regularUser;

    protected Company $company;

    protected UserService $userService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userService = app(UserService::class);

        // Create a company
        $this->company = Company::factory()->create();

        // Create an admin user
        $this->admin = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $this->admin->companies()->attach($this->company->id, ['role' => UserRole::ADMIN->value]);

        // Create a regular user
        $this->regularUser = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $this->regularUser->companies()->attach($this->company->id, ['role' => UserRole::USER->value]);
    }

    public function test_admin_can_view_users_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Users/Index')
            ->has('users')
            ->has('stats')
        );
    }

    public function test_non_admin_cannot_access_users_page(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_search_users(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.users.index', ['search' => 'test']));

        $response->assertOk();
    }

    public function test_admin_can_send_password_reset(): void
    {
        $this->actingAs($this->admin);

        Password::shouldReceive('sendResetLink')
            ->once()
            ->andReturn(Password::RESET_LINK_SENT);

        $response = $this->post(route('admin.users.password-reset', $this->regularUser->id));

        $response->assertRedirect();
    }

    public function test_admin_can_suspend_user(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.users.suspend', $this->regularUser->id), [
            'reason' => 'Violation of terms of service',
        ]);

        $response->assertRedirect();
        $this->regularUser->refresh();
        $this->assertTrue($this->regularUser->isSuspended());
        $this->assertEquals('Violation of terms of service', $this->regularUser->suspension_reason);
    }

    public function test_admin_cannot_suspend_themselves(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.users.suspend', $this->admin->id), [
            'reason' => 'Testing self-suspension',
        ]);

        $response->assertRedirect();
        $this->admin->refresh();
        $this->assertFalse($this->admin->isSuspended());
    }

    public function test_suspension_requires_reason(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.users.suspend', $this->regularUser->id), [
            'reason' => '',
        ]);

        $response->assertSessionHasErrors('reason');
        $this->regularUser->refresh();
        $this->assertFalse($this->regularUser->isSuspended());
    }

    public function test_admin_can_unsuspend_user(): void
    {
        $this->actingAs($this->admin);

        // First suspend the user
        $this->userService->suspend($this->regularUser, 'Test reason', $this->admin);
        $this->assertTrue($this->regularUser->isSuspended());

        // Now unsuspend
        $response = $this->post(route('admin.users.unsuspend', $this->regularUser->id));

        $response->assertRedirect();
        $this->regularUser->refresh();
        $this->assertFalse($this->regularUser->isSuspended());
        $this->assertNull($this->regularUser->suspension_reason);
    }

    public function test_non_admin_cannot_suspend_users(): void
    {
        $this->actingAs($this->regularUser);

        $otherUser = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $otherUser->companies()->attach($this->company->id, ['role' => UserRole::USER->value]);

        $response = $this->post(route('admin.users.suspend', $otherUser->id), [
            'reason' => 'Test',
        ]);

        $response->assertForbidden();
        $otherUser->refresh();
        $this->assertFalse($otherUser->isSuspended());
    }

    public function test_users_are_paginated(): void
    {
        $this->actingAs($this->admin);

        // Create additional users
        User::factory()->count(20)->create()->each(function ($user) {
            $user->companies()->attach($this->company->id, ['role' => UserRole::USER->value]);
        });

        $response = $this->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('users.data', 15) // Default per page is 15
            ->has('users.links')
        );
    }

    public function test_user_stats_are_included(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('stats.total_users')
            ->has('stats.active_users')
            ->has('stats.suspended_users')
            ->has('stats.new_this_month')
        );
    }
}
