<?php

namespace Tests\Feature;

use App\Enums\InstagramPostStatus;
use App\Models\InstagramAccount;
use App\Models\InstagramPost;
use App\Models\User;
use App\Services\InstagramPostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test Instagram Post Management
 * 
 * Tests post creation, scheduling, publishing, and lifecycle management.
 */
class InstagramPostManagementTest extends TestCase
{
    use RefreshDatabase;

    protected InstagramPostService $postService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->postService = app(InstagramPostService::class);
    }

    /** @test */
    public function user_can_create_draft_post_for_owned_account()
    {
        $user = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($user)->create();

        $post = $this->postService->createDraft($user, $account, [
            'caption' => 'Test post caption',
            'media_urls' => ['https://example.com/image.jpg'],
            'media_type' => 'image',
        ]);

        $this->assertNotNull($post);
        $this->assertEquals(InstagramPostStatus::DRAFT, $post->status);
        $this->assertEquals('Test post caption', $post->caption);
        $this->assertEquals($user->id, $post->user_id);
        $this->assertEquals($account->id, $post->instagram_account_id);
    }

    /** @test */
    public function user_can_create_post_for_company_account()
    {
        $user = User::factory()->create();
        $company = \App\Models\Company::factory()->create();
        $company->users()->attach($user, ['role' => 'member']);
        
        $account = InstagramAccount::factory()->forCompany($company)->create();

        $post = $this->postService->createDraft($user, $account, [
            'caption' => 'Company post',
            'media_urls' => ['https://example.com/image.jpg'],
        ]);

        $this->assertNotNull($post);
        $this->assertEquals($account->id, $post->instagram_account_id);
    }

    /** @test */
    public function user_cannot_create_post_for_inaccessible_account()
    {
        $user = User::factory()->create();
        $stranger = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($stranger)->create();

        $post = $this->postService->createDraft($user, $account, [
            'caption' => 'Unauthorized post',
            'media_urls' => ['https://example.com/image.jpg'],
        ]);

        $this->assertNull($post);
    }

    /** @test */
    public function user_can_update_their_draft_post()
    {
        $user = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($user)->create();
        
        $post = InstagramPost::factory()
            ->forAccount($account)
            ->byUser($user)
            ->draft()
            ->create();

        $updated = $this->postService->updateDraft($user, $post, [
            'caption' => 'Updated caption',
        ]);

        $this->assertTrue($updated);
        $post->refresh();
        $this->assertEquals('Updated caption', $post->caption);
    }

    /** @test */
    public function user_cannot_update_published_post()
    {
        $user = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($user)->create();
        
        $post = InstagramPost::factory()
            ->forAccount($account)
            ->byUser($user)
            ->published()
            ->create(['caption' => 'Original caption']);

        $updated = $this->postService->updateDraft($user, $post, [
            'caption' => 'Updated caption',
        ]);

        $this->assertFalse($updated);
        $post->refresh();
        $this->assertEquals('Original caption', $post->caption);
    }

    /** @test */
    public function user_can_schedule_post()
    {
        $user = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($user)->create();
        
        $post = InstagramPost::factory()
            ->forAccount($account)
            ->byUser($user)
            ->draft()
            ->create(['media_urls' => ['https://example.com/image.jpg']]);

        $scheduledTime = now()->addHours(2);
        $scheduled = $this->postService->schedulePost($user, $post, $scheduledTime);

        $this->assertTrue($scheduled);
        $post->refresh();
        $this->assertEquals(InstagramPostStatus::SCHEDULED, $post->status);
        $this->assertEquals($scheduledTime->timestamp, $post->scheduled_at->timestamp);
    }

    /** @test */
    public function cannot_schedule_post_in_the_past()
    {
        $user = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($user)->create();
        
        $post = InstagramPost::factory()
            ->forAccount($account)
            ->byUser($user)
            ->draft()
            ->create(['media_urls' => ['https://example.com/image.jpg']]);

        $pastTime = now()->subHours(1);
        $scheduled = $this->postService->schedulePost($user, $post, $pastTime);

        $this->assertFalse($scheduled);
    }

    /** @test */
    public function cannot_schedule_post_without_media()
    {
        $user = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($user)->create();
        
        $post = InstagramPost::factory()
            ->forAccount($account)
            ->byUser($user)
            ->draft()
            ->create(['media_urls' => []]);

        $scheduledTime = now()->addHours(2);
        $scheduled = $this->postService->schedulePost($user, $post, $scheduledTime);

        $this->assertFalse($scheduled);
    }

    /** @test */
    public function user_can_cancel_scheduled_post()
    {
        $user = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($user)->create();
        
        $post = InstagramPost::factory()
            ->forAccount($account)
            ->byUser($user)
            ->scheduled()
            ->create();

        $cancelled = $this->postService->cancelPost($user, $post);

        $this->assertTrue($cancelled);
        $post->refresh();
        $this->assertEquals(InstagramPostStatus::CANCELLED, $post->status);
    }

    /** @test */
    public function user_can_delete_draft_post()
    {
        $user = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($user)->create();
        
        $post = InstagramPost::factory()
            ->forAccount($account)
            ->byUser($user)
            ->draft()
            ->create();

        $deleted = $this->postService->deletePost($user, $post);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted($post);
    }

    /** @test */
    public function cannot_delete_published_post()
    {
        $user = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($user)->create();
        
        $post = InstagramPost::factory()
            ->forAccount($account)
            ->byUser($user)
            ->published()
            ->create();

        $deleted = $this->postService->deletePost($user, $post);

        $this->assertFalse($deleted);
        $this->assertDatabaseHas('instagram_posts', ['id' => $post->id]);
    }

    /** @test */
    public function post_statuses_work_correctly()
    {
        $draft = InstagramPost::factory()->draft()->create();
        $scheduled = InstagramPost::factory()->scheduled()->create();
        $published = InstagramPost::factory()->published()->create();
        $failed = InstagramPost::factory()->failed()->create();

        $this->assertTrue($draft->isEditable());
        $this->assertTrue($scheduled->isEditable());
        $this->assertFalse($published->isEditable());

        $this->assertTrue($scheduled->isCancellable());
        $this->assertFalse($draft->isCancellable());
        $this->assertFalse($published->isCancellable());

        $this->assertTrue($published->isFinal());
        $this->assertTrue($failed->isFinal());
        $this->assertFalse($draft->isFinal());
    }

    /** @test */
    public function due_for_publishing_scope_works()
    {
        // Create various posts
        InstagramPost::factory()->draft()->create();
        InstagramPost::factory()->scheduled(now()->addHours(2))->create();
        
        $duePost1 = InstagramPost::factory()->dueForPublishing()->create();
        $duePost2 = InstagramPost::factory()->scheduled(now()->subMinutes(10))->create();

        $duePosts = InstagramPost::dueForPublishing()->get();

        $this->assertCount(2, $duePosts);
        $this->assertTrue($duePosts->contains($duePost1));
        $this->assertTrue($duePosts->contains($duePost2));
    }

    /** @test */
    public function post_scopes_filter_correctly()
    {
        $account = InstagramAccount::factory()->create();
        
        InstagramPost::factory()->forAccount($account)->draft()->create();
        InstagramPost::factory()->forAccount($account)->scheduled()->create();
        InstagramPost::factory()->forAccount($account)->published()->create();
        InstagramPost::factory()->forAccount($account)->failed()->create();

        $this->assertCount(1, InstagramPost::drafts()->get());
        $this->assertCount(1, InstagramPost::scheduled()->get());
        $this->assertCount(1, InstagramPost::published()->get());
        $this->assertCount(1, InstagramPost::failed()->get());
    }

    /** @test */
    public function user_can_view_their_posts()
    {
        $user = User::factory()->create();
        $account = InstagramAccount::factory()->forUser($user)->create();
        
        InstagramPost::factory()->count(3)->forAccount($account)->byUser($user)->create();
        
        // Create posts by another user
        $otherUser = User::factory()->create();
        $otherAccount = InstagramAccount::factory()->forUser($otherUser)->create();
        InstagramPost::factory()->count(2)->forAccount($otherAccount)->byUser($otherUser)->create();

        $userPosts = $this->postService->getPostsForUser($user);

        $this->assertCount(3, $userPosts);
    }

    /** @test */
    public function failed_post_can_be_retried()
    {
        $post = InstagramPost::factory()
            ->failed()
            ->create(['retry_count' => 1]);

        $this->assertTrue($post->canRetry());

        $post->retry();

        $post->refresh();
        $this->assertEquals(InstagramPostStatus::SCHEDULED, $post->status);
        $this->assertNull($post->error_message);
    }

    /** @test */
    public function post_cannot_be_retried_after_max_attempts()
    {
        $post = InstagramPost::factory()
            ->failed()
            ->create(['retry_count' => 3]);

        $this->assertFalse($post->canRetry());
    }

    /** @test */
    public function post_summary_truncates_long_captions()
    {
        $longCaption = str_repeat('This is a very long caption. ', 20);
        
        $post = InstagramPost::factory()->create([
            'caption' => $longCaption,
        ]);

        $summary = $post->getSummary();
        
        $this->assertLessThanOrEqual(53, strlen($summary)); // 50 + "..."
        $this->assertStringEndsWith('...', $summary);
    }
}

