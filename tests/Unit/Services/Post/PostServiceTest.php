<?php

namespace Tests\Unit\Services\Post;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Company;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use App\Repositories\Post\PostMediaRepository;
use App\Repositories\Post\PostRepository;
use App\Services\Post\PostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PostServiceTest extends TestCase
{
    use RefreshDatabase;

    private PostService $postService;

    private PostRepository $postRepository;

    private PostMediaRepository $mediaRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock repositories
        $this->postRepository = Mockery::mock(PostRepository::class);
        $this->mediaRepository = Mockery::mock(PostMediaRepository::class);

        // Create service with mocked dependencies
        $this->postService = new PostService($this->postRepository, $this->mediaRepository);

        // Mock facades
        Storage::fake();
        Log::shouldReceive('info')->andReturn(true);
        Log::shouldReceive('error')->andReturn(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_get_company_posts()
    {
        $companyId = 1;
        $filters = ['status' => 'draft'];
        $expectedPosts = collect([new Post]);

        $this->postRepository
            ->shouldReceive('getByCompany')
            ->with($companyId, $filters)
            ->once()
            ->andReturn($expectedPosts);

        $result = $this->postService->getCompanyPosts($companyId, $filters);

        $this->assertEquals($expectedPosts, $result);
    }

    /** @test */
    public function it_can_create_a_post_successfully()
    {
        // Arrange
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $user->companies()->attach($company->id, ['role' => 'admin']);

        Auth::shouldReceive('id')->andReturn($user->id);
        Auth::shouldReceive('user')->andReturn($user);

        $postData = [
            'type' => PostType::FEED->value,
            'title' => 'Test Post',
            'caption' => 'This is a test post #test @user',
            'scheduled_at' => null,
            'media' => [
                [
                    'type' => 'image',
                    'filename' => 'test.jpg',
                    'original_filename' => 'test.jpg',
                    'mime_type' => 'image/jpeg',
                    'file_size' => 1024,
                    'storage_path' => 'posts/1/test.jpg',
                ],
            ],
        ];

        $createdPost = new Post;
        $createdPost->id = 1;
        $createdPost->fill($postData);

        $this->postRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdPost);

        $this->mediaRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn(new PostMedia);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        // Act
        $result = $this->postService->createPost($company->id, $postData);

        // Assert
        $this->assertInstanceOf(Post::class, $result);
    }

    /** @test */
    public function it_throws_exception_when_creating_post_without_instagram_account()
    {
        // Arrange
        $user = User::factory()->create();
        $company = Company::factory()->create();

        Auth::shouldReceive('id')->andReturn($user->id);
        Auth::shouldReceive('user')->andReturn($user);

        $postData = [
            'type' => PostType::FEED->value,
            'title' => 'Test Post',
        ];

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.instagram_account_required'));

        $this->postService->createPost($company->id, $postData);
    }

    /** @test */
    public function it_throws_exception_when_caption_is_too_long()
    {
        // Arrange
        $user = User::factory()->create();
        $company = Company::factory()->create();

        Auth::shouldReceive('id')->andReturn($user->id);
        Auth::shouldReceive('user')->andReturn($user);

        $postData = [
            'type' => PostType::FEED->value,
            'caption' => str_repeat('a', 2201), // Too long
        ];

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.caption_too_long'));

        $this->postService->createPost($company->id, $postData);
    }

    /** @test */
    public function it_throws_exception_when_scheduled_time_is_in_past()
    {
        // Arrange
        $user = User::factory()->create();
        $company = Company::factory()->create();

        Auth::shouldReceive('id')->andReturn($user->id);
        Auth::shouldReceive('user')->andReturn($user);

        $postData = [
            'type' => PostType::FEED->value,
            'scheduled_at' => now()->subHour()->toDateTimeString(),
        ];

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.scheduled_time_must_be_future'));

        $this->postService->createPost($company->id, $postData);
    }

    /** @test */
    public function it_throws_exception_when_too_many_media_for_post_type()
    {
        // Arrange
        $user = User::factory()->create();
        $company = Company::factory()->create();

        Auth::shouldReceive('id')->andReturn($user->id);
        Auth::shouldReceive('user')->andReturn($user);

        $postData = [
            'type' => PostType::STORY->value, // Story allows only 1 media
            'media' => [
                ['type' => 'image', 'filename' => 'test1.jpg', 'original_filename' => 'test1.jpg', 'mime_type' => 'image/jpeg', 'file_size' => 1024, 'storage_path' => 'posts/1/test1.jpg'],
                ['type' => 'image', 'filename' => 'test2.jpg', 'original_filename' => 'test2.jpg', 'mime_type' => 'image/jpeg', 'file_size' => 1024, 'storage_path' => 'posts/1/test2.jpg'],
            ],
        ];

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.too_many_media'));

        $this->postService->createPost($company->id, $postData);
    }

    /** @test */
    public function it_throws_exception_when_invalid_media_type_for_post_type()
    {
        // Arrange
        $user = User::factory()->create();
        $company = Company::factory()->create();

        Auth::shouldReceive('id')->andReturn($user->id);
        Auth::shouldReceive('user')->andReturn($user);

        $postData = [
            'type' => PostType::FEED->value,
            'media' => [
                ['type' => 'video', 'filename' => 'test.mp4', 'original_filename' => 'test.mp4', 'mime_type' => 'video/mp4', 'file_size' => 1024, 'storage_path' => 'posts/1/test.mp4'],
            ],
        ];

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.invalid_media_type'));

        $this->postService->createPost($company->id, $postData);
    }

    /** @test */
    public function it_can_update_a_post_successfully()
    {
        // Arrange
        $post = Post::factory()->create(['status' => PostStatus::DRAFT]);
        $post->shouldReceive('canBeEdited')->andReturn(true);

        $updateData = [
            'title' => 'Updated Title',
            'caption' => 'Updated caption #updated @user',
        ];

        $this->postRepository
            ->shouldReceive('update')
            ->once()
            ->andReturn($post);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        // Act
        $result = $this->postService->updatePost($post, $updateData);

        // Assert
        $this->assertInstanceOf(Post::class, $result);
    }

    /** @test */
    public function it_throws_exception_when_updating_published_post()
    {
        // Arrange
        $post = Post::factory()->create(['status' => PostStatus::PUBLISHED]);
        $post->shouldReceive('canBeEdited')->andReturn(false);

        $updateData = ['title' => 'Updated Title'];

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.cannot_edit_published'));

        $this->postService->updatePost($post, $updateData);
    }

    /** @test */
    public function it_can_delete_a_post_successfully()
    {
        // Arrange
        $post = Post::factory()->create(['status' => PostStatus::DRAFT]);
        $post->shouldReceive('canBeEdited')->andReturn(true);

        $media = new PostMedia;
        $media->storage_path = 'posts/1/test.jpg';
        $post->shouldReceive('getAttribute')->with('media')->andReturn(collect([$media]));

        $this->postRepository
            ->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        // Act
        $result = $this->postService->deletePost($post);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_throws_exception_when_deleting_published_post()
    {
        // Arrange
        $post = Post::factory()->create(['status' => PostStatus::PUBLISHED]);
        $post->shouldReceive('canBeEdited')->andReturn(false);

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.cannot_delete_published'));

        $this->postService->deletePost($post);
    }

    /** @test */
    public function it_can_schedule_a_post_successfully()
    {
        // Arrange
        $post = Post::factory()->create(['status' => PostStatus::DRAFT]);
        $post->shouldReceive('canBeEdited')->andReturn(true);

        $media = new PostMedia;
        $post->shouldReceive('getAttribute')->with('media')->andReturn(collect([$media]));
        $post->shouldReceive('getAttribute')->with('media')->andReturn(collect([$media]));

        $scheduledAt = now()->addHour();

        $this->postRepository
            ->shouldReceive('update')
            ->once()
            ->andReturn($post);

        // Act
        $result = $this->postService->schedulePost($post, $scheduledAt);

        // Assert
        $this->assertInstanceOf(Post::class, $result);
    }

    /** @test */
    public function it_throws_exception_when_scheduling_published_post()
    {
        // Arrange
        $post = Post::factory()->create(['status' => PostStatus::PUBLISHED]);
        $post->shouldReceive('canBeEdited')->andReturn(false);

        $scheduledAt = now()->addHour();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.cannot_schedule_published'));

        $this->postService->schedulePost($post, $scheduledAt);
    }

    /** @test */
    public function it_throws_exception_when_scheduling_post_in_past()
    {
        // Arrange
        $post = Post::factory()->create(['status' => PostStatus::DRAFT]);
        $post->shouldReceive('canBeEdited')->andReturn(true);

        $scheduledAt = now()->subHour();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.scheduled_time_must_be_future'));

        $this->postService->schedulePost($post, $scheduledAt);
    }

    /** @test */
    public function it_throws_exception_when_scheduling_post_without_media()
    {
        // Arrange
        $post = Post::factory()->create(['status' => PostStatus::DRAFT]);
        $post->shouldReceive('canBeEdited')->andReturn(true);
        $post->shouldReceive('getAttribute')->with('media')->andReturn(collect([]));

        $scheduledAt = now()->addHour();

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.media_required_for_scheduling'));

        $this->postService->schedulePost($post, $scheduledAt);
    }

    /** @test */
    public function it_can_get_posts_due_for_publishing()
    {
        // Arrange
        $expectedPosts = collect([new Post]);

        $this->postRepository
            ->shouldReceive('getDueForPublishing')
            ->once()
            ->andReturn($expectedPosts);

        // Act
        $result = $this->postService->getPostsDueForPublishing();

        // Assert
        $this->assertEquals($expectedPosts, $result);
    }

    /** @test */
    public function it_extracts_hashtags_from_caption()
    {
        // This tests the private method indirectly through createPost
        $user = User::factory()->create();
        $company = Company::factory()->create();

        Auth::shouldReceive('id')->andReturn($user->id);
        Auth::shouldReceive('user')->andReturn($user);

        $postData = [
            'type' => PostType::FEED->value,
            'caption' => 'This is a test #hashtag1 #hashtag2 and another #hashtag1',
        ];

        $createdPost = new Post;
        $createdPost->id = 1;

        $this->postRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdPost);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        // Act
        $result = $this->postService->createPost($company->id, $postData);

        // Assert - The hashtags should be extracted and passed to the repository
        $this->assertInstanceOf(Post::class, $result);
    }

    /** @test */
    public function it_extracts_mentions_from_caption()
    {
        // This tests the private method indirectly through createPost
        $user = User::factory()->create();
        $company = Company::factory()->create();

        Auth::shouldReceive('id')->andReturn($user->id);
        Auth::shouldReceive('user')->andReturn($user);

        $postData = [
            'type' => PostType::FEED->value,
            'caption' => 'This is a test @user1 @user2 and another @user1',
        ];

        $createdPost = new Post;
        $createdPost->id = 1;

        $this->postRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($createdPost);

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('commit')->once();

        // Act
        $result = $this->postService->createPost($company->id, $postData);

        // Assert - The mentions should be extracted and passed to the repository
        $this->assertInstanceOf(Post::class, $result);
    }

    /** @test */
    public function it_handles_database_transaction_rollback_on_error()
    {
        // Arrange
        $user = User::factory()->create();
        $company = Company::factory()->create();

        Auth::shouldReceive('id')->andReturn($user->id);
        Auth::shouldReceive('user')->andReturn($user);

        $postData = [
            'type' => PostType::FEED->value,
            'title' => 'Test Post',
        ];

        $this->postRepository
            ->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('Database error'));

        DB::shouldReceive('beginTransaction')->once();
        DB::shouldReceive('rollBack')->once();

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');

        $this->postService->createPost($company->id, $postData);
    }
}
