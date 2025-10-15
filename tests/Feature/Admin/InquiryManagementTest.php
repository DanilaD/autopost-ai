<?php

namespace Tests\Feature\Admin;

use App\Models\Company;
use App\Models\Inquiry;
use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InquiryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $user;
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
        $this->user = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $this->user->companies()->attach($this->company->id, ['role' => UserRole::USER->value]);
    }

    public function test_admin_can_view_inquiries_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.inquiries.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Inquiries/Index')
            ->has('inquiries')
            ->has('stats')
        );
    }

    public function test_non_admin_cannot_access_inquiries(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('admin.inquiries.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_search_inquiries(): void
    {
        $this->actingAs($this->admin);

        Inquiry::factory()->create(['email' => 'test1@example.com']);
        Inquiry::factory()->create(['email' => 'test2@example.com']);
        Inquiry::factory()->create(['email' => 'other@example.com']);

        $response = $this->get(route('admin.inquiries.index', ['search' => 'test']));

        $response->assertOk();
    }

    public function test_admin_can_sort_inquiries(): void
    {
        $this->actingAs($this->admin);

        Inquiry::factory()->count(3)->create();

        $response = $this->get(route('admin.inquiries.index', [
            'sort' => 'email',
            'direction' => 'asc',
        ]));

        $response->assertOk();
    }

    public function test_admin_can_delete_inquiry(): void
    {
        $this->actingAs($this->admin);

        $inquiry = Inquiry::factory()->create();

        $response = $this->delete(route('admin.inquiries.destroy', $inquiry->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('inquiries', ['id' => $inquiry->id]);
    }

    public function test_non_admin_cannot_delete_inquiry(): void
    {
        $this->actingAs($this->user);

        $inquiry = Inquiry::factory()->create();

        $response = $this->delete(route('admin.inquiries.destroy', $inquiry->id));

        $response->assertForbidden();
        $this->assertDatabaseHas('inquiries', ['id' => $inquiry->id]);
    }

    public function test_admin_can_export_inquiries(): void
    {
        $this->actingAs($this->admin);

        Inquiry::factory()->count(5)->create();

        $response = $this->get(route('admin.inquiries.export'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_inquiries_are_paginated(): void
    {
        $this->actingAs($this->admin);

        Inquiry::factory()->count(20)->create();

        $response = $this->get(route('admin.inquiries.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->has('inquiries.data', 15) // Default per page is 15
            ->has('inquiries.links')
        );
    }
}

