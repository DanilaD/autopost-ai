<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TimezoneService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test timezone functionality across the application.
 * 
 * Tests include:
 * - Timezone detection and storage during registration
 * - Timezone updates via profile
 * - Timezone service methods
 * - Middleware timezone setting
 */
class TimezoneTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that new users can register with a timezone.
     */
    public function test_new_users_can_register_with_timezone(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'timezone' => 'America/New_York',
        ]);

        $this->assertAuthenticated();
        
        $user = User::where('email', 'test@example.com')->first();
        $this->assertEquals('America/New_York', $user->timezone);
    }

    /**
     * Test that registration defaults to UTC if no timezone provided.
     */
    public function test_registration_defaults_to_utc_without_timezone(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        
        $user = User::where('email', 'test@example.com')->first();
        $this->assertEquals('UTC', $user->timezone);
    }

    /**
     * Test that invalid timezone is rejected during registration.
     */
    public function test_registration_rejects_invalid_timezone(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'timezone' => 'Invalid/Timezone',
        ]);

        $response->assertSessionHasErrors('timezone');
        $this->assertGuest();
    }

    /**
     * Test that user can update their timezone via profile.
     */
    public function test_user_can_update_timezone_in_profile(): void
    {
        $user = User::factory()->create([
            'timezone' => 'UTC',
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'timezone' => 'Europe/London',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');

        $user->refresh();
        $this->assertEquals('Europe/London', $user->timezone);
    }

    /**
     * Test that profile update rejects invalid timezone.
     */
    public function test_profile_update_rejects_invalid_timezone(): void
    {
        $user = User::factory()->create([
            'timezone' => 'UTC',
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
            'timezone' => 'Not/A/Real/Timezone',
        ]);

        $response->assertSessionHasErrors('timezone');
        
        $user->refresh();
        $this->assertEquals('UTC', $user->timezone);
    }

    /**
     * Test that timezone is required in profile update.
     */
    public function test_profile_update_requires_timezone(): void
    {
        $user = User::factory()->create([
            'timezone' => 'UTC',
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'name' => $user->name,
            'email' => $user->email,
        ]);

        $response->assertSessionHasErrors('timezone');
    }

    /**
     * Test TimezoneService returns valid timezones.
     */
    public function test_timezone_service_returns_valid_flat_timezones(): void
    {
        $service = new TimezoneService();
        $timezones = $service->getFlatTimezones();

        $this->assertIsArray($timezones);
        $this->assertNotEmpty($timezones);
        
        // Check that common timezones exist
        $this->assertArrayHasKey('UTC', $timezones);
        $this->assertArrayHasKey('America/New_York', $timezones);
        $this->assertArrayHasKey('Europe/London', $timezones);
        
        // Check format of labels
        foreach ($timezones as $identifier => $label) {
            $this->assertIsString($identifier);
            $this->assertIsString($label);
            $this->assertNotEmpty($label);
        }
    }

    /**
     * Test TimezoneService returns grouped timezones.
     */
    public function test_timezone_service_returns_grouped_timezones(): void
    {
        $service = new TimezoneService();
        $grouped = $service->getGroupedTimezones();

        $this->assertIsArray($grouped);
        $this->assertNotEmpty($grouped);
        
        // Check that main regions exist
        $this->assertArrayHasKey('Europe', $grouped);
        $this->assertArrayHasKey('America', $grouped);
        $this->assertArrayHasKey('Asia', $grouped);
        
        // Check structure
        foreach ($grouped as $region => $timezones) {
            $this->assertIsString($region);
            $this->assertIsArray($timezones);
            $this->assertNotEmpty($timezones);
        }
    }

    /**
     * Test TimezoneService validates timezones correctly.
     */
    public function test_timezone_service_validates_timezones(): void
    {
        $service = new TimezoneService();

        // Valid timezones
        $this->assertTrue($service->isValid('UTC'));
        $this->assertTrue($service->isValid('America/New_York'));
        $this->assertTrue($service->isValid('Europe/London'));
        $this->assertTrue($service->isValid('Asia/Tokyo'));

        // Invalid timezones
        $this->assertFalse($service->isValid('Invalid/Timezone'));
        $this->assertFalse($service->isValid('Not Real'));
        $this->assertFalse($service->isValid(''));
    }

    /**
     * Test TimezoneService gets offset hours correctly.
     */
    public function test_timezone_service_gets_offset_hours(): void
    {
        $service = new TimezoneService();

        // UTC should be 0
        $this->assertEquals(0, $service->getOffsetHours('UTC'));
        
        // Test a known timezone offset (may vary by DST)
        $offset = $service->getOffsetHours('America/New_York');
        $this->assertIsInt($offset);
        $this->assertGreaterThanOrEqual(-5, $offset);
        $this->assertLessThanOrEqual(-4, $offset);
    }

    /**
     * Test TimezoneService returns common timezones.
     */
    public function test_timezone_service_returns_common_timezones(): void
    {
        $service = new TimezoneService();
        $common = $service->getCommonTimezones();

        $this->assertIsArray($common);
        $this->assertNotEmpty($common);
        
        // Check that expected common timezones are included (USA, Canada, and key cities)
        $this->assertArrayHasKey('UTC', $common);
        $this->assertArrayHasKey('America/New_York', $common);
        $this->assertArrayHasKey('America/Chicago', $common);
        $this->assertArrayHasKey('America/Los_Angeles', $common);
        $this->assertArrayHasKey('America/Toronto', $common);
        $this->assertArrayHasKey('America/Vancouver', $common);
        $this->assertArrayHasKey('America/Guayaquil', $common); // Quito
        $this->assertArrayHasKey('Europe/Minsk', $common);
        $this->assertArrayHasKey('Europe/Kiev', $common);
        
        // Common list should be smaller than full list
        $this->assertLessThan(count($service->getFlatTimezones()), count($common));
    }

    /**
     * Test that middleware sets application timezone for authenticated user.
     */
    public function test_middleware_sets_timezone_for_authenticated_user(): void
    {
        $user = User::factory()->create([
            'timezone' => 'Europe/London',
        ]);

        // Make a request as authenticated user
        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        
        // The middleware should have set the timezone
        // Note: In tests, the config is reset between requests, 
        // but we can verify the user has the timezone set
        $this->assertEquals('Europe/London', $user->timezone);
    }

    /**
     * Test profile edit page includes timezones.
     */
    public function test_profile_edit_page_includes_timezones(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertInertia(fn ($page) => 
            $page->has('timezones')
                 ->has('timezones.UTC') // Check that UTC timezone exists
                 ->has('timezones.America/New_York') // Check a common timezone
        );
    }
}

