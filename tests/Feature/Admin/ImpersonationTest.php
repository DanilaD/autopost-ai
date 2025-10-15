<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $regularUser;

    protected Company $company;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_admin_can_impersonate_user(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.users.impersonate', $this->regularUser->id));

        $response->assertRedirect(route('dashboard'));
        $this->assertEquals($this->regularUser->id, auth()->id());
        $this->assertNotNull(session('impersonate'));
        $this->assertEquals($this->admin->id, session('impersonate.admin_id'));
    }

    public function test_admin_cannot_impersonate_themselves(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.users.impersonate', $this->admin->id));

        $response->assertRedirect();
        $this->assertEquals($this->admin->id, auth()->id());
    }

    public function test_admin_cannot_impersonate_another_admin(): void
    {
        $this->actingAs($this->admin);

        // Create another admin
        $anotherAdmin = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $anotherAdmin->companies()->attach($this->company->id, ['role' => UserRole::ADMIN->value]);

        $response = $this->post(route('admin.users.impersonate', $anotherAdmin->id));

        $response->assertRedirect();
        $this->assertEquals($this->admin->id, auth()->id());
    }

    public function test_admin_cannot_impersonate_suspended_user(): void
    {
        $this->actingAs($this->admin);

        // Suspend the regular user
        $this->regularUser->suspend('Test reason', $this->admin);

        $response = $this->post(route('admin.users.impersonate', $this->regularUser->id));

        $response->assertRedirect();
        $this->assertEquals($this->admin->id, auth()->id());
    }

    public function test_admin_can_stop_impersonation(): void
    {
        $this->actingAs($this->admin);

        // Start impersonation
        $this->post(route('admin.users.impersonate', $this->regularUser->id));
        $this->assertEquals($this->regularUser->id, auth()->id());

        // Stop impersonation
        $response = $this->post(route('admin.impersonate.stop'));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertEquals($this->admin->id, auth()->id());
        $this->assertNull(session('impersonate'));
    }

    public function test_non_admin_cannot_impersonate(): void
    {
        $this->actingAs($this->regularUser);

        $otherUser = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $otherUser->companies()->attach($this->company->id, ['role' => UserRole::USER->value]);

        $response = $this->post(route('admin.users.impersonate', $otherUser->id));

        $response->assertForbidden();
        $this->assertEquals($this->regularUser->id, auth()->id());
    }

    public function test_impersonation_session_data_is_stored(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('admin.users.impersonate', $this->regularUser->id));

        $this->assertNotNull(session('impersonate'));
        $this->assertEquals($this->admin->id, session('impersonate.admin_id'));
        $this->assertEquals($this->admin->name, session('impersonate.admin_name'));
        $this->assertEquals($this->admin->email, session('impersonate.admin_email'));
        $this->assertNotNull(session('impersonate.started_at'));
    }
}
