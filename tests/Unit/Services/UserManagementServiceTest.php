<?php

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class UserManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    protected UserManagementService $service;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserManagementService;
        $this->company = Company::factory()->create();
    }

    public function test_get_users_returns_paginated_results(): void
    {
        User::factory()->count(19)->create()->each(function ($user) {
            $user->companies()->attach($this->company->id, ['role' => UserRole::USER->value]);
        });

        // Total will be 20 (19 + 1 from setUp)
        $result = $this->service->getUsers(['per_page' => 10]);

        $this->assertCount(10, $result->items());
        $this->assertGreaterThanOrEqual(19, $result->total());
    }

    public function test_get_users_filters_by_search(): void
    {
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
        User::factory()->create(['name' => 'Bob Wilson', 'email' => 'bob@other.com']);

        $result = $this->service->getUsers(['search' => 'John']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('John Doe', $result->first()->name);
    }

    public function test_get_users_filters_by_status(): void
    {
        $activeUser = User::factory()->create();
        $suspendedUser = User::factory()->create();
        $admin = User::factory()->create();

        $admin->companies()->attach($this->company->id, ['role' => UserRole::ADMIN->value]);
        $suspendedUser->suspend('Test reason', $admin);

        $result = $this->service->getUsers(['status' => 'suspended']);

        $this->assertCount(1, $result->items());
        $this->assertTrue($result->first()->isSuspended());
    }

    public function test_suspend_user_updates_database(): void
    {
        $admin = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $admin->companies()->attach($this->company->id, ['role' => UserRole::ADMIN->value]);

        $user = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);

        $result = $this->service->suspendUser($user->id, 'Test reason', $admin);

        $this->assertTrue($result);
        $user->refresh();
        $this->assertTrue($user->isSuspended());
        $this->assertEquals('Test reason', $user->suspension_reason);
        $this->assertEquals($admin->id, $user->suspended_by);
    }

    public function test_suspend_user_throws_exception_if_already_suspended(): void
    {
        $admin = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $admin->companies()->attach($this->company->id, ['role' => UserRole::ADMIN->value]);

        $user = User::factory()->create();
        $user->suspend('First reason', $admin);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->suspendUser($user->id, 'Second reason', $admin);
    }

    public function test_suspend_user_throws_exception_if_suspending_self(): void
    {
        $admin = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $admin->companies()->attach($this->company->id, ['role' => UserRole::ADMIN->value]);

        $this->expectException(\InvalidArgumentException::class);
        $this->service->suspendUser($admin->id, 'Cannot suspend self', $admin);
    }

    public function test_unsuspend_user_clears_suspension(): void
    {
        $admin = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $admin->companies()->attach($this->company->id, ['role' => UserRole::ADMIN->value]);

        $user = User::factory()->create();
        $user->suspend('Test reason', $admin);

        $result = $this->service->unsuspendUser($user->id);

        $this->assertTrue($result);
        $user->refresh();
        $this->assertFalse($user->isSuspended());
        $this->assertNull($user->suspension_reason);
    }

    public function test_unsuspend_user_throws_exception_if_not_suspended(): void
    {
        $user = User::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->service->unsuspendUser($user->id);
    }

    public function test_send_password_reset_link_returns_success_message(): void
    {
        $user = User::factory()->create();

        Password::shouldReceive('sendResetLink')
            ->once()
            ->with(['email' => $user->email])
            ->andReturn(Password::RESET_LINK_SENT);

        $message = $this->service->sendPasswordResetLink($user->id);

        $this->assertEquals('Password reset link sent successfully.', $message);
    }

    public function test_get_user_stats_returns_correct_data(): void
    {
        $user = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $user->companies()->attach($this->company->id, ['role' => UserRole::USER->value]);

        $stats = $this->service->getUserStats($user->id);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('instagram_accounts', $stats);
        $this->assertArrayHasKey('posts_count', $stats);
        $this->assertArrayHasKey('companies_count', $stats);
        $this->assertArrayHasKey('account_age_days', $stats);
    }

    public function test_get_user_management_stats_returns_counts(): void
    {
        $admin = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $admin->companies()->attach($this->company->id, ['role' => UserRole::ADMIN->value]);

        User::factory()->count(5)->create();
        $suspendedUser = User::factory()->create();
        $suspendedUser->suspend('Test', $admin);

        $stats = $this->service->getUserManagementStats();

        $this->assertArrayHasKey('total_users', $stats);
        $this->assertArrayHasKey('active_users', $stats);
        $this->assertArrayHasKey('suspended_users', $stats);
        $this->assertArrayHasKey('new_this_month', $stats);
        $this->assertGreaterThanOrEqual(7, $stats['total_users']); // At least 7 (may have 1 from setUp)
        $this->assertEquals(1, $stats['suspended_users']);
    }

    public function test_update_last_login_updates_timestamp(): void
    {
        $user = User::factory()->create(['last_login_at' => null]);

        $result = $this->service->updateLastLogin($user->id);

        $this->assertTrue($result);
        $user->refresh();
        $this->assertNotNull($user->last_login_at);
    }
}
