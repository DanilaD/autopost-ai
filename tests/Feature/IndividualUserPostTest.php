<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Models\InstagramAccount;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndividualUserPostTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function individual_user_can_view_their_posts()
    {
        // Create a user without a company
        $user = User::factory()->create();

        // Create an Instagram account for the user
        $instagramAccount = InstagramAccount::factory()->create([
            'user_id' => $user->id,
            'company_id' => null,
        ]);

        // Create posts for the user
        $posts = Post::factory()->count(3)->create([
            'created_by' => $user->id,
            'company_id' => null,
            'instagram_account_id' => $instagramAccount->id,
        ]);

        // Act
        $response = $this->actingAs($user)->get(route('posts.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Posts/Index')
            ->has('posts', 3)
        );
    }

    /** @test */
    public function individual_user_can_create_posts()
    {
        // Create a user without a company
        $user = User::factory()->create();

        // Create an Instagram account for the user
        $instagramAccount = InstagramAccount::factory()->create([
            'user_id' => $user->id,
            'company_id' => null,
        ]);

        // Act
        $response = $this->actingAs($user)->get(route('posts.create'));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Posts/Create')
            ->has('instagramAccounts', 1)
        );
    }

    /** @test */
    public function individual_user_can_get_post_stats()
    {
        // Create a user without a company
        $user = User::factory()->create();

        // Create an Instagram account for the user
        $instagramAccount = InstagramAccount::factory()->create([
            'user_id' => $user->id,
            'company_id' => null,
        ]);

        // Create posts for the user
        Post::factory()->create([
            'created_by' => $user->id,
            'company_id' => null,
            'instagram_account_id' => $instagramAccount->id,
            'status' => PostStatus::DRAFT,
        ]);

        Post::factory()->create([
            'created_by' => $user->id,
            'company_id' => null,
            'instagram_account_id' => $instagramAccount->id,
            'status' => PostStatus::PUBLISHED,
        ]);

        // Act
        $response = $this->actingAs($user)->get(route('posts.stats'));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'total' => 2,
            'drafts' => 1,
            'scheduled' => 0,
            'publishing' => 0,
            'published' => 1,
            'failed' => 0,
        ]);
    }
}
