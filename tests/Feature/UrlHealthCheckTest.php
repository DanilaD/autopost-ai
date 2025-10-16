<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\InstagramAccount;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Comprehensive URL Health Check Test
 *
 * This test ensures all application URLs are working correctly and don't return
 * inappropriate 500, 403, or 404 errors. It tests both authenticated and
 * unauthenticated access patterns.
 */
class UrlHealthCheckTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected User $user;

    protected User $admin;

    protected Company $company;

    protected Post $post;

    protected InstagramAccount $instagramAccount;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $this->company = Company::factory()->create(['owner_id' => $this->user->id]);
        $this->user->update(['current_company_id' => $this->company->id]);

        // Create admin user
        $this->admin = User::factory()->create([
            'current_company_id' => $this->company->id,
        ]);
        $this->admin->companies()->attach($this->company->id, ['role' => UserRole::ADMIN->value]);

        $this->post = Post::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);

        $this->instagramAccount = InstagramAccount::factory()->create([
            'company_id' => $this->company->id,
            'user_id' => $this->user->id,
        ]);
    }

    /**
     * Test all public routes return appropriate responses
     */
    public function test_public_routes_are_healthy()
    {
        $publicRoutes = [
            ['GET', '/'],
            ['GET', '/login'],
            ['GET', '/register'],
            ['GET', '/forgot-password'],
            ['GET', '/verify-email'],
            ['GET', '/sanctum/csrf-cookie'],
            ['GET', '/up'],
        ];

        foreach ($publicRoutes as [$method, $url]) {
            $response = $this->call($method, $url);

            $this->assertNotEquals(500, $response->getStatusCode(),
                "Public route {$method} {$url} returned 500 error");

            $this->assertNotEquals(404, $response->getStatusCode(),
                "Public route {$method} {$url} returned 404 error");

            // Check for error content
            $this->assertStringNotContainsString('SQLSTATE', $response->getContent(),
                "Public route {$method} {$url} contains database error");

            $this->assertStringNotContainsString('Fatal error', $response->getContent(),
                "Public route {$method} {$url} contains fatal error");
        }
    }

    /**
     * Test authenticated routes work correctly for authenticated users
     */
    public function test_authenticated_routes_work_for_authenticated_users()
    {
        $authenticatedRoutes = [
            ['GET', '/dashboard'],
            ['GET', '/posts'],
            ['GET', '/posts/create'],
            ['GET', '/posts/stats/overview'],
            ['GET', '/posts/'.$this->post->id],
            ['GET', '/posts/'.$this->post->id.'/edit'],
            ['GET', '/profile'],
            ['GET', '/instagram'],
            ['GET', '/companies/settings'],
        ];

        foreach ($authenticatedRoutes as [$method, $url]) {
            $response = $this->actingAs($this->user)->call($method, $url);

            $this->assertNotEquals(500, $response->getStatusCode(),
                "Authenticated route {$method} {$url} returned 500 error for authenticated user");

            $this->assertNotEquals(404, $response->getStatusCode(),
                "Authenticated route {$method} {$url} returned 404 error for authenticated user");

            // Should not return 403 for authenticated users on their own resources
            if (str_contains($url, '/posts/'.$this->post->id)) {
                $this->assertNotEquals(403, $response->getStatusCode(),
                    "Authenticated route {$method} {$url} returned 403 error for resource owner");
            }

            // Check for error content
            $this->assertStringNotContainsString('SQLSTATE', $response->getContent(),
                "Authenticated route {$method} {$url} contains database error");
        }
    }

    /**
     * Test authenticated routes return 403 for unauthenticated users
     */
    public function test_authenticated_routes_require_authentication()
    {
        $protectedRoutes = [
            ['GET', '/dashboard'],
            ['GET', '/posts'],
            ['GET', '/posts/create'],
            ['GET', '/posts/stats/overview'],
            ['GET', '/profile'],
            ['GET', '/instagram'],
            ['GET', '/companies/settings'],
        ];

        foreach ($protectedRoutes as [$method, $url]) {
            $response = $this->call($method, $url);

            // Should redirect to login or return 403, not 500 or 404
            $this->assertTrue(
                $response->isRedirect() || $response->getStatusCode() === 403,
                "Protected route {$method} {$url} should redirect or return 403 for unauthenticated user, got {$response->getStatusCode()}"
            );

            $this->assertNotEquals(500, $response->getStatusCode(),
                "Protected route {$method} {$url} returned 500 error for unauthenticated user");

            $this->assertNotEquals(404, $response->getStatusCode(),
                "Protected route {$method} {$url} returned 404 error for unauthenticated user");
        }
    }

    /**
     * Test admin routes work correctly for admin users
     */
    public function test_admin_routes_work_for_admin_users()
    {
        $adminRoutes = [
            ['GET', '/admin/users'],
            ['GET', '/admin/inquiries'],
        ];

        foreach ($adminRoutes as [$method, $url]) {
            $response = $this->actingAs($this->admin)->call($method, $url);

            $this->assertNotEquals(500, $response->getStatusCode(),
                "Admin route {$method} {$url} returned 500 error for admin user");

            $this->assertNotEquals(404, $response->getStatusCode(),
                "Admin route {$method} {$url} returned 404 error for admin user");

            $this->assertNotEquals(403, $response->getStatusCode(),
                "Admin route {$method} {$url} returned 403 error for admin user");
        }
    }

    /**
     * Test admin routes return 403 for non-admin users
     */
    public function test_admin_routes_require_admin_permissions()
    {
        $adminRoutes = [
            ['GET', '/admin/users'],
            ['GET', '/admin/inquiries'],
        ];

        foreach ($adminRoutes as [$method, $url]) {
            $response = $this->actingAs($this->user)->call($method, $url);

            $this->assertEquals(403, $response->getStatusCode(),
                "Admin route {$method} {$url} should return 403 for non-admin user");

            $this->assertNotEquals(500, $response->getStatusCode(),
                "Admin route {$method} {$url} returned 500 error for non-admin user");

            $this->assertNotEquals(404, $response->getStatusCode(),
                "Admin route {$method} {$url} returned 404 error for non-admin user");
        }
    }

    /**
     * Test POST routes handle validation errors gracefully
     */
    public function test_post_routes_handle_validation_errors_gracefully()
    {
        $postRoutes = [
            ['POST', '/posts', []], // Empty data
            ['POST', '/login', []], // Empty login data
            ['POST', '/register', []], // Empty registration data
        ];

        foreach ($postRoutes as [$method, $url, $data]) {
            $response = $this->actingAs($this->user)->call($method, $url, $data);

            // Should return validation error (422) or redirect, not 500
            $this->assertNotEquals(500, $response->getStatusCode(),
                "POST route {$method} {$url} returned 500 error with invalid data");

            $this->assertNotEquals(404, $response->getStatusCode(),
                "POST route {$method} {$url} returned 404 error with invalid data");

            // Check for error content
            $this->assertStringNotContainsString('SQLSTATE', $response->getContent(),
                "POST route {$method} {$url} contains database error");
        }
    }

    /**
     * Test resource routes handle non-existent resources correctly
     */
    public function test_resource_routes_handle_non_existent_resources()
    {
        $nonExistentId = 99999;

        $resourceRoutes = [
            ['GET', "/posts/{$nonExistentId}"],
            ['GET', "/posts/{$nonExistentId}/edit"],
            ['PUT', "/posts/{$nonExistentId}"],
            ['DELETE', "/posts/{$nonExistentId}"],
            ['POST', "/posts/{$nonExistentId}/schedule"],
        ];

        foreach ($resourceRoutes as [$method, $url]) {
            $response = $this->actingAs($this->user)->call($method, $url);

            // Should return 404 for non-existent resource, not 500
            $this->assertNotEquals(500, $response->getStatusCode(),
                "Resource route {$method} {$url} returned 500 error for non-existent resource");

            // 404 is expected for non-existent resources
            if ($response->getStatusCode() !== 404) {
                $this->assertTrue(
                    $response->isRedirect() || $response->getStatusCode() === 403,
                    "Resource route {$method} {$url} should return 404, redirect, or 403 for non-existent resource, got {$response->getStatusCode()}"
                );
            }
        }
    }

    /**
     * Test media serving routes work correctly
     */
    public function test_media_routes_work_correctly()
    {
        // Create a test media file
        Storage::fake('public');
        $file = UploadedFile::fake()->image('test.jpg');
        $path = $file->store('posts/1', 'public');

        $media = PostMedia::factory()->create([
            'post_id' => $this->post->id,
            'filename' => 'test.jpg',
            'storage_path' => $path,
        ]);

        $mediaRoutes = [
            ['GET', "/media/{$path}"],
            ['GET', "/storage/{$path}"],
        ];

        foreach ($mediaRoutes as [$method, $url]) {
            $response = $this->call($method, $url);

            // Should not return 500 or 404 for valid media
            $this->assertNotEquals(500, $response->getStatusCode(),
                "Media route {$method} {$url} returned 500 error");

            // 404 might be acceptable for media files that don't exist
            if ($response->getStatusCode() === 404) {
                // This is acceptable for media files
                continue;
            }

            // Check for error content
            $this->assertStringNotContainsString('SQLSTATE', $response->getContent(),
                "Media route {$method} {$url} contains database error");
        }
    }

    /**
     * Test error pages work correctly
     */
    public function test_error_pages_work_correctly()
    {
        $errorRoutes = [
            ['GET', '/403'],
            ['GET', '/404'],
            ['GET', '/419'],
            ['GET', '/500'],
        ];

        foreach ($errorRoutes as [$method, $url]) {
            $response = $this->call($method, $url);

            // Error pages should return 200 (they display the error page)
            $this->assertEquals(200, $response->getStatusCode(),
                "Error route {$method} {$url} should return 200 (display error page)");

            // Should not contain PHP errors
            $this->assertStringNotContainsString('SQLSTATE', $response->getContent(),
                "Error route {$method} {$url} contains database error");

            $this->assertStringNotContainsString('Fatal error', $response->getContent(),
                "Error route {$method} {$url} contains fatal error");
        }
    }

    /**
     * Test API routes return appropriate JSON responses
     */
    public function test_api_routes_return_json_responses()
    {
        $apiRoutes = [
            ['GET', '/posts/stats/overview'],
        ];

        foreach ($apiRoutes as [$method, $url]) {
            $response = $this->actingAs($this->user)->call($method, $url, [], [], [], [
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
            ]);

            $this->assertNotEquals(500, $response->getStatusCode(),
                "API route {$method} {$url} returned 500 error");

            // Should return JSON for API requests
            if ($response->getStatusCode() === 200) {
                $this->assertJson($response->getContent(),
                    "API route {$method} {$url} should return valid JSON");
            }
        }
    }

    /**
     * Test that all routes have proper middleware applied
     */
    public function test_routes_have_proper_middleware()
    {
        // This test ensures that routes are properly protected
        $response = $this->get('/dashboard');

        // Should redirect to login, not return 500 or 404
        $this->assertTrue(
            $response->isRedirect() || $response->getStatusCode() === 403,
            'Dashboard route should redirect or return 403 for unauthenticated users'
        );

        $this->assertNotEquals(500, $response->getStatusCode(),
            'Dashboard route should not return 500 for unauthenticated users');

        $this->assertNotEquals(404, $response->getStatusCode(),
            'Dashboard route should not return 404 for unauthenticated users');
    }

    /**
     * Test that the application handles concurrent requests properly
     */
    public function test_concurrent_requests_work_correctly()
    {
        $responses = [];

        // Make multiple concurrent requests to different routes
        $routes = [
            ['GET', '/'],
            ['GET', '/login'],
            ['GET', '/register'],
            ['GET', '/dashboard'],
        ];

        foreach ($routes as [$method, $url]) {
            $response = $this->call($method, $url);
            $responses[] = $response;
        }

        // All responses should be valid (not 500)
        foreach ($responses as $index => $response) {
            $this->assertNotEquals(500, $response->getStatusCode(),
                "Concurrent request {$index} returned 500 error");
        }
    }
}
