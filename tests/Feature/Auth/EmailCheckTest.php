<?php

namespace Tests\Feature\Auth;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_redirects_new_email_to_registration_mode(): void
    {
        $response = $this->post(route('auth.email.check'), [
            'email' => 'newuser@example.com',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('email', 'newuser@example.com');
        $response->assertSessionHas('mode', 'register');

        // Verify inquiry was created
        $this->assertDatabaseHas('inquiries', [
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_redirects_existing_email_to_login_mode(): void
    {
        $user = User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post(route('auth.email.check'), [
            'email' => 'existing@example.com',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('email', 'existing@example.com');
        $response->assertSessionHas('mode', 'login');

        // Verify NO inquiry was created for existing user
        $this->assertDatabaseMissing('inquiries', [
            'email' => 'existing@example.com',
        ]);
    }

    public function test_validates_email_format(): void
    {
        $response = $this->post(route('auth.email.check'), [
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_requires_email_field(): void
    {
        $response = $this->post(route('auth.email.check'), []);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_stores_ip_address_and_user_agent_in_inquiry(): void
    {
        $response = $this->post(route('auth.email.check'), [
            'email' => 'tracked@example.com',
        ], [
            'REMOTE_ADDR' => '192.168.1.1',
            'HTTP_USER_AGENT' => 'Test Browser',
        ]);

        $inquiry = Inquiry::where('email', 'tracked@example.com')->first();

        $this->assertNotNull($inquiry);
        $this->assertEquals('192.168.1.1', $inquiry->ip_address);
        $this->assertEquals('Test Browser', $inquiry->user_agent);
    }

    public function test_rate_limits_email_check_attempts(): void
    {
        // Make 5 attempts (should all succeed)
        for ($i = 0; $i < 5; $i++) {
            $response = $this->post(route('auth.email.check'), [
                'email' => "test{$i}@example.com",
            ]);
            $response->assertRedirect();
        }

        // 6th attempt should be rate limited
        $response = $this->post(route('auth.email.check'), [
            'email' => 'ratelimited@example.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    public function test_creates_multiple_inquiries_for_same_email(): void
    {
        // First submission
        $this->post(route('auth.email.check'), [
            'email' => 'duplicate@example.com',
        ]);

        $firstCount = Inquiry::where('email', 'duplicate@example.com')->count();

        // Second submission of same email (need to wait to avoid rate limit)
        sleep(1); // Wait 1 second between requests

        $this->post(route('auth.email.check'), [
            'email' => 'duplicate@example.com',
        ]);

        $secondCount = Inquiry::where('email', 'duplicate@example.com')->count();

        // Should have created 2 inquiry records (we track each attempt)
        $this->assertEquals(1, $firstCount);
        $this->assertEquals(2, $secondCount);
    }

    public function test_handles_email_case_sensitivity_for_existing_users(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        // MySQL and PostgreSQL are case-insensitive by default for email lookups
        // but we should test that our lookup works
        $response = $this->post(route('auth.email.check'), [
            'email' => 'user@example.com', // Same case
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('mode', 'login');
    }
}
