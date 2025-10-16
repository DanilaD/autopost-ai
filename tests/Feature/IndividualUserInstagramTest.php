<?php

namespace Tests\Feature;

use App\Models\InstagramAccount;
use App\Models\User;
use App\Services\InstagramService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndividualUserInstagramTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Instagram service to avoid configuration issues in tests
        $this->mock(InstagramService::class, function ($mock) {
            $mock->shouldReceive('connectAccountForUser')->andReturn(new InstagramAccount);
            $mock->shouldReceive('disconnectAccount')->andReturn(true);
            $mock->shouldReceive('syncProfile')->andReturn(true);
        });
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function individual_user_can_view_instagram_accounts_page()
    {
        // Create a user without a company
        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user)->get(route('instagram.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Instagram/Index')
            ->has('accounts', 0) // No accounts yet
            ->where('hasCompany', false)
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function individual_user_can_see_their_instagram_accounts()
    {
        // Create a user without a company
        $user = User::factory()->create();

        // Create Instagram accounts for the user
        $accounts = InstagramAccount::factory()->count(2)->create([
            'user_id' => $user->id,
            'company_id' => null,
        ]);

        // Act
        $response = $this->actingAs($user)->get(route('instagram.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Instagram/Index')
            ->has('accounts', 2)
            ->where('hasCompany', false)
        );
    }
}
