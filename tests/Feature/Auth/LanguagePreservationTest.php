<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguagePreservationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_preserves_selected_language(): void
    {
        // Simulate user selecting Russian on main page
        session(['locale' => 'ru']);
        app()->setLocale('ru');

        // Register new user
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        // Check that user was created with Russian locale
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('ru', $user->locale);
    }

    public function test_registration_defaults_to_english_if_no_locale_set(): void
    {
        // No locale in session

        // Register new user
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        // Check that user was created with English locale (default)
        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('en', $user->locale);
    }

    public function test_login_saves_current_locale_if_user_has_no_preference(): void
    {
        // Create user without locale preference
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'locale' => null,
        ]);

        // Simulate user selecting Spanish on login page
        session(['locale' => 'es']);
        app()->setLocale('es');

        // Login
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));

        // Check that user's locale was updated to Spanish
        $user->refresh();
        $this->assertEquals('es', $user->locale);
    }

    public function test_login_preserves_existing_user_locale_preference(): void
    {
        // Create user with Russian preference
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'locale' => 'ru',
        ]);

        // Simulate different locale in session (maybe browser language)
        session(['locale' => 'en']);
        app()->setLocale('en');

        // Login
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));

        // User's preference should NOT change (stays Russian)
        $user->refresh();
        $this->assertEquals('ru', $user->locale);
    }

    public function test_registration_with_spanish_locale(): void
    {
        // Simulate user selecting Spanish
        session(['locale' => 'es']);
        app()->setLocale('es');

        // Register
        $response = $this->post('/register', [
            'name' => 'Usuario Español',
            'email' => 'espanol@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        // Check Spanish was saved
        $user = User::where('email', 'espanol@example.com')->first();
        $this->assertEquals('es', $user->locale);
    }

    public function test_invalid_locale_falls_back_to_english_on_registration(): void
    {
        // Set invalid locale
        session(['locale' => 'invalid']);
        app()->setLocale('invalid');

        // Register
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('dashboard'));

        // Should default to English
        $user = User::where('email', 'test@example.com')->first();
        $this->assertEquals('en', $user->locale);
    }
}
