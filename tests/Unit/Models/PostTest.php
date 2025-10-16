<?php

namespace Tests\Unit\Models;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Company;
use App\Models\InstagramAccount;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Company $company;

    private InstagramAccount $instagramAccount;

    private Post $post;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();
        $this->instagramAccount = InstagramAccount::factory()->create([
            'company_id' => $this->company->id,
        ]);

        $this->post = Post::factory()->create([
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
            'instagram_account_id' => $this->instagramAccount->id,
            'type' => PostType::FEED,
            'status' => PostStatus::DRAFT,
            'title' => 'Test Post',
            'caption' => 'This is a test post #test @user',
            'hashtags' => ['test'],
            'mentions' => ['user'],
            'metadata' => ['key' => 'value'],
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_be_created_with_factory()
    {
        $this->assertInstanceOf(Post::class, $this->post);
        $this->assertDatabaseHas('posts', [
            'id' => $this->post->id,
            'company_id' => $this->company->id,
            'created_by' => $this->user->id,
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'company_id',
            'created_by',
            'instagram_account_id',
            'type',
            'title',
            'caption',
            'hashtags',
            'mentions',
            'scheduled_at',
            'published_at',
            'status',
            'failure_reason',
            'publish_attempts',
            'metadata',
        ];

        $this->assertEquals($fillable, $this->post->getFillable());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_casts_attributes_correctly()
    {
        $this->assertInstanceOf(PostStatus::class, $this->post->status);
        $this->assertInstanceOf(PostType::class, $this->post->type);
        $this->assertIsArray($this->post->hashtags);
        $this->assertIsArray($this->post->mentions);
        $this->assertIsArray($this->post->metadata);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_belongs_to_company()
    {
        $this->assertInstanceOf(Company::class, $this->post->company);
        $this->assertEquals($this->company->id, $this->post->company->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_belongs_to_creator()
    {
        $this->assertInstanceOf(User::class, $this->post->creator);
        $this->assertEquals($this->user->id, $this->post->creator->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_belongs_to_instagram_account()
    {
        $this->assertInstanceOf(InstagramAccount::class, $this->post->instagramAccount);
        $this->assertEquals($this->instagramAccount->id, $this->post->instagramAccount->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_has_many_media()
    {
        $media1 = PostMedia::factory()->create(['post_id' => $this->post->id, 'order' => 1]);
        $media2 = PostMedia::factory()->create(['post_id' => $this->post->id, 'order' => 2]);

        $this->assertCount(2, $this->post->media);
        $this->assertInstanceOf(PostMedia::class, $this->post->media->first());
        $this->assertEquals($media1->id, $this->post->media->first()->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_orders_media_by_order_column()
    {
        $media2 = PostMedia::factory()->create(['post_id' => $this->post->id, 'order' => 2]);
        $media1 = PostMedia::factory()->create(['post_id' => $this->post->id, 'order' => 1]);

        $media = $this->post->media;
        $this->assertEquals($media1->id, $media->first()->id);
        $this->assertEquals($media2->id, $media->last()->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_by_company()
    {
        $otherCompany = Company::factory()->create();
        $otherPost = Post::factory()->create(['company_id' => $otherCompany->id]);

        $companyPosts = Post::forCompany($this->company->id)->get();

        $this->assertCount(1, $companyPosts);
        $this->assertEquals($this->post->id, $companyPosts->first()->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_by_status()
    {
        $scheduledPost = Post::factory()->create([
            'company_id' => $this->company->id,
            'status' => PostStatus::SCHEDULED,
        ]);

        $draftPosts = Post::withStatus(PostStatus::DRAFT)->get();
        $scheduledPosts = Post::withStatus(PostStatus::SCHEDULED)->get();

        $this->assertCount(1, $draftPosts);
        $this->assertCount(1, $scheduledPosts);
        $this->assertEquals($this->post->id, $draftPosts->first()->id);
        $this->assertEquals($scheduledPost->id, $scheduledPosts->first()->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_by_type()
    {
        $reelPost = Post::factory()->create([
            'company_id' => $this->company->id,
            'type' => PostType::REEL,
        ]);

        $feedPosts = Post::ofType(PostType::FEED)->get();
        $reelPosts = Post::ofType(PostType::REEL)->get();

        $this->assertCount(1, $feedPosts);
        $this->assertCount(1, $reelPosts);
        $this->assertEquals($this->post->id, $feedPosts->first()->id);
        $this->assertEquals($reelPost->id, $reelPosts->first()->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_due_for_publishing()
    {
        $duePost = Post::factory()->create([
            'company_id' => $this->company->id,
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => now()->subMinute(),
        ]);

        $futurePost = Post::factory()->create([
            'company_id' => $this->company->id,
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => now()->addHour(),
        ]);

        $duePosts = Post::dueForPublishing()->get();

        $this->assertCount(1, $duePosts);
        $this->assertEquals($duePost->id, $duePosts->first()->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_scope_by_instagram_account()
    {
        $otherAccount = InstagramAccount::factory()->create(['company_id' => $this->company->id]);
        $otherPost = Post::factory()->create([
            'company_id' => $this->company->id,
            'instagram_account_id' => $otherAccount->id,
        ]);

        $accountPosts = Post::forAccount($this->instagramAccount->id)->get();

        $this->assertCount(1, $accountPosts);
        $this->assertEquals($this->post->id, $accountPosts->first()->id);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_check_if_editable()
    {
        $this->assertTrue($this->post->canBeEdited());

        $this->post->update(['status' => PostStatus::PUBLISHED]);
        $this->assertFalse($this->post->fresh()->canBeEdited());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_check_if_ready_to_publish()
    {
        $this->assertFalse($this->post->isReadyToPublish());

        $this->post->update(['status' => PostStatus::SCHEDULED]);
        $this->assertTrue($this->post->fresh()->isReadyToPublish());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_caption_with_hashtags()
    {
        $this->assertEquals(
            'This is a test post #test @user #test',
            $this->post->formatted_caption
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_caption_without_hashtags()
    {
        $this->post->update(['hashtags' => null]);

        $this->assertEquals(
            'This is a test post #test @user',
            $this->post->fresh()->formatted_caption
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_formats_caption_with_empty_caption()
    {
        $this->post->update(['caption' => null, 'hashtags' => ['test']]);

        $this->assertEquals(
            '#test',
            $this->post->fresh()->formatted_caption
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_gets_summary_attribute()
    {
        $this->assertEquals(
            'This is a test post #test @user',
            $this->post->summary
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_truncates_long_summary()
    {
        $longCaption = str_repeat('a', 150);
        $this->post->update(['caption' => $longCaption]);

        $summary = $this->post->fresh()->summary;
        $this->assertStringEndsWith('...', $summary);
        $this->assertEquals(103, strlen($summary)); // 100 + '...'
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_handles_null_caption_in_summary()
    {
        $this->post->update(['caption' => null]);

        $this->assertEquals('', $this->post->fresh()->summary);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_be_created_with_all_post_types()
    {
        foreach (PostType::cases() as $type) {
            $post = Post::factory()->create([
                'company_id' => $this->company->id,
                'type' => $type,
            ]);

            $this->assertEquals($type, $post->type);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_be_created_with_all_post_statuses()
    {
        foreach (PostStatus::cases() as $status) {
            $post = Post::factory()->create([
                'company_id' => $this->company->id,
                'status' => $status,
            ]);

            $this->assertEquals($status, $post->status);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_have_scheduled_at_set()
    {
        $scheduledAt = now()->addHour();
        $this->post->update(['scheduled_at' => $scheduledAt]);

        $this->assertEquals($scheduledAt->format('Y-m-d H:i:s'), $this->post->fresh()->scheduled_at->format('Y-m-d H:i:s'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_have_published_at_set()
    {
        $publishedAt = now();
        $this->post->update(['published_at' => $publishedAt]);

        $this->assertEquals($publishedAt->format('Y-m-d H:i:s'), $this->post->fresh()->published_at->format('Y-m-d H:i:s'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_have_failure_reason_set()
    {
        $failureReason = 'Instagram API error';
        $this->post->update(['failure_reason' => $failureReason]);

        $this->assertEquals($failureReason, $this->post->fresh()->failure_reason);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_have_publish_attempts_incremented()
    {
        $this->post->update(['publish_attempts' => 3]);

        $this->assertEquals(3, $this->post->fresh()->publish_attempts);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_have_complex_metadata()
    {
        $metadata = [
            'instagram_post_id' => '123456789',
            'likes_count' => 42,
            'comments_count' => 5,
            'engagement_rate' => 0.15,
            'tags' => ['photography', 'nature'],
        ];

        $this->post->update(['metadata' => $metadata]);

        $this->assertEquals($metadata, $this->post->fresh()->metadata);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_have_multiple_hashtags()
    {
        $hashtags = ['test', 'instagram', 'social', 'media'];
        $this->post->update(['hashtags' => $hashtags]);

        $this->assertEquals($hashtags, $this->post->fresh()->hashtags);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_have_multiple_mentions()
    {
        $mentions = ['user1', 'user2', 'brand'];
        $this->post->update(['mentions' => $mentions]);

        $this->assertEquals($mentions, $this->post->fresh()->mentions);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_be_soft_deleted()
    {
        $this->post->delete();

        $this->assertSoftDeleted('posts', ['id' => $this->post->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_cascades_delete_to_media()
    {
        $media = PostMedia::factory()->create(['post_id' => $this->post->id]);

        $this->post->delete();

        $this->assertSoftDeleted('post_media', ['id' => $media->id]);
    }
}
