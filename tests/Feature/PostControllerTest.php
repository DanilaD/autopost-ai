<?php

namespace Tests\Feature;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Company;
use App\Models\Post;
use App\Models\PostMedia;
use App\Models\User;
use App\Services\Post\PostMediaService;
use App\Services\Post\PostService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Company $company;

    private PostService $postService;

    private PostMediaService $mediaService;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->user = User::factory()->create();
        $this->company = Company::factory()->create();
        $this->user->companies()->attach($this->company->id, ['role' => 'admin']);
        $this->user->update(['current_company_id' => $this->company->id]);

        // Mock services
        $this->postService = Mockery::mock(PostService::class);
        $this->mediaService = Mockery::mock(PostMediaService::class);

        $this->app->instance(PostService::class, $this->postService);
        $this->app->instance(PostMediaService::class, $this->mediaService);

        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_display_posts_index()
    {
        // Arrange
        $posts = collect([
            Post::factory()->create(['company_id' => $this->company->id]),
            Post::factory()->create(['company_id' => $this->company->id]),
        ]);

        $this->postService
            ->shouldReceive('getCompanyPosts')
            ->with($this->company->id, [])
            ->once()
            ->andReturn($posts);

        // Act
        $response = $this->actingAs($this->user)->get(route('posts.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Posts/Index')
            ->has('posts')
            ->has('filters')
        );
    }

    /** @test */
    public function it_can_display_posts_index_with_filters()
    {
        // Arrange
        $filters = ['status' => 'draft', 'type' => 'feed'];
        $posts = collect([Post::factory()->create(['company_id' => $this->company->id])]);

        $this->postService
            ->shouldReceive('getCompanyPosts')
            ->with($this->company->id, $filters)
            ->once()
            ->andReturn($posts);

        // Act
        $response = $this->actingAs($this->user)->get(route('posts.index', $filters));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Posts/Index')
            ->where('filters.status', 'draft')
            ->where('filters.type', 'feed')
        );
    }

    /** @test */
    public function it_can_display_create_post_form()
    {
        // Act
        $response = $this->actingAs($this->user)->get(route('posts.create'));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Posts/Create')
            ->has('instagramAccounts')
            ->has('prefill')
        );
    }

    /** @test */
    public function it_can_display_create_post_form_with_duplicate_prefill()
    {
        // Arrange
        $originalPost = Post::factory()->create([
            'company_id' => $this->company->id,
            'title' => 'Original Post',
            'caption' => 'Original caption',
        ]);

        $media = PostMedia::factory()->create([
            'post_id' => $originalPost->id,
            'filename' => 'test.jpg',
            'type' => 'image',
        ]);

        $originalPost->setRelation('media', collect([$media]));

        // Act
        $response = $this->actingAs($this->user)->get(route('posts.create', ['duplicate' => $originalPost->id]));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Posts/Create')
            ->has('prefill')
            ->where('prefill.title', 'Copy of Original Post')
            ->where('prefill.caption', 'Original caption')
        );
    }

    /** @test */
    public function it_can_store_a_new_post()
    {
        // Arrange
        $postData = [
            'type' => PostType::FEED->value,
            'title' => 'Test Post',
            'caption' => 'Test caption',
        ];

        $createdPost = Post::factory()->create([
            'company_id' => $this->company->id,
            'title' => 'Test Post',
            'caption' => 'Test caption',
        ]);

        $this->postService
            ->shouldReceive('createPost')
            ->with($this->company->id, $postData)
            ->once()
            ->andReturn($createdPost);

        // Act
        $response = $this->actingAs($this->user)->post(route('posts.store'), $postData);

        // Assert
        $response->assertRedirect(route('posts.index'));
        $response->assertSessionHas('success', __('posts.created_successfully'));
    }

    /** @test */
    public function it_can_store_a_new_post_with_media()
    {
        // Arrange
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);

        $postData = [
            'type' => PostType::FEED->value,
            'title' => 'Test Post',
            'caption' => 'Test caption',
            'media' => [
                ['file' => $file],
            ],
        ];

        $createdPost = Post::factory()->create([
            'company_id' => $this->company->id,
            'title' => 'Test Post',
        ]);

        $this->postService
            ->shouldReceive('createPost')
            ->with($this->company->id, Mockery::type('array'))
            ->once()
            ->andReturn($createdPost);

        $this->mediaService
            ->shouldReceive('uploadMedia')
            ->once()
            ->andReturn(new PostMedia);

        // Act
        $response = $this->actingAs($this->user)->post(route('posts.store'), $postData);

        // Assert
        $response->assertRedirect(route('posts.index'));
        $response->assertSessionHas('success', __('posts.created_successfully'));
    }

    /** @test */
    public function it_handles_post_creation_failure()
    {
        // Arrange
        $postData = [
            'type' => PostType::FEED->value,
            'title' => 'Test Post',
        ];

        $this->postService
            ->shouldReceive('createPost')
            ->with($this->company->id, $postData)
            ->once()
            ->andThrow(new \Exception('Database error'));

        // Act
        $response = $this->actingAs($this->user)->post(route('posts.store'), $postData);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error', __('posts.create_failed'));
    }

    /** @test */
    public function it_can_display_a_specific_post()
    {
        // Arrange
        $post = Post::factory()->create(['company_id' => $this->company->id]);
        $post->setRelation('media', collect([]));
        $post->setRelation('instagramAccount', null);
        $post->setRelation('creator', $this->user);

        // Act
        $response = $this->actingAs($this->user)->get(route('posts.show', $post));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Posts/Show')
            ->has('post')
        );
    }

    /** @test */
    public function it_can_display_edit_post_form()
    {
        // Arrange
        $post = Post::factory()->create(['company_id' => $this->company->id]);
        $post->setRelation('media', collect([]));
        $post->setRelation('instagramAccount', null);

        // Act
        $response = $this->actingAs($this->user)->get(route('posts.edit', $post));

        // Assert
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Posts/Create')
            ->has('post')
            ->has('instagramAccounts')
        );
    }

    /** @test */
    public function it_can_update_a_post()
    {
        // Arrange
        $post = Post::factory()->create(['company_id' => $this->company->id]);

        $updateData = [
            'title' => 'Updated Title',
            'caption' => 'Updated caption',
        ];

        $this->postService
            ->shouldReceive('updatePost')
            ->with($post, $updateData)
            ->once()
            ->andReturn($post);

        // Act
        $response = $this->actingAs($this->user)->put(route('posts.update', $post), $updateData);

        // Assert
        $response->assertRedirect(route('posts.index'));
        $response->assertSessionHas('success', __('posts.updated_successfully'));
    }

    /** @test */
    public function it_can_update_a_post_with_media_changes()
    {
        // Arrange
        $post = Post::factory()->create(['company_id' => $this->company->id]);
        $file = UploadedFile::fake()->image('new.jpg', 100, 100);

        $updateData = [
            'title' => 'Updated Title',
            'media' => [
                ['file' => $file],
            ],
            'delete_media' => json_encode([1, 2]),
        ];

        $this->postService
            ->shouldReceive('updatePost')
            ->with($post, Mockery::type('array'))
            ->once()
            ->andReturn($post);

        $this->mediaService
            ->shouldReceive('deleteMedia')
            ->twice()
            ->andReturn(true);

        $this->mediaService
            ->shouldReceive('uploadMedia')
            ->once()
            ->andReturn(new PostMedia);

        // Act
        $response = $this->actingAs($this->user)->put(route('posts.update', $post), $updateData);

        // Assert
        $response->assertRedirect(route('posts.index'));
        $response->assertSessionHas('success', __('posts.updated_successfully'));
    }

    /** @test */
    public function it_handles_post_update_failure()
    {
        // Arrange
        $post = Post::factory()->create(['company_id' => $this->company->id]);

        $updateData = [
            'title' => 'Updated Title',
        ];

        $this->postService
            ->shouldReceive('updatePost')
            ->with($post, $updateData)
            ->once()
            ->andThrow(new \Exception('Update failed'));

        // Act
        $response = $this->actingAs($this->user)->put(route('posts.update', $post), $updateData);

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error', __('posts.update_failed'));
    }

    /** @test */
    public function it_can_delete_a_post()
    {
        // Arrange
        $post = Post::factory()->create(['company_id' => $this->company->id]);

        $this->postService
            ->shouldReceive('deletePost')
            ->with($post)
            ->once()
            ->andReturn(true);

        // Act
        $response = $this->actingAs($this->user)->delete(route('posts.destroy', $post));

        // Assert
        $response->assertRedirect(route('posts.index'));
        $response->assertSessionHas('success', __('posts.deleted_successfully'));
    }

    /** @test */
    public function it_handles_post_deletion_failure()
    {
        // Arrange
        $post = Post::factory()->create(['company_id' => $this->company->id]);

        $this->postService
            ->shouldReceive('deletePost')
            ->with($post)
            ->once()
            ->andThrow(new \Exception('Delete failed'));

        // Act
        $response = $this->actingAs($this->user)->delete(route('posts.destroy', $post));

        // Assert
        $response->assertRedirect();
        $response->assertSessionHas('error', __('posts.delete_failed'));
    }

    /** @test */
    public function it_can_schedule_a_post()
    {
        // Arrange
        $post = Post::factory()->create(['company_id' => $this->company->id]);
        $scheduledAt = now()->addHour();

        $this->postService
            ->shouldReceive('schedulePost')
            ->with($post, Mockery::type(\DateTime::class))
            ->once()
            ->andReturn($post);

        // Act
        $response = $this->actingAs($this->user)->post(route('posts.schedule', $post), [
            'scheduled_at' => $scheduledAt->toDateTimeString(),
        ]);

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'message' => __('posts.scheduled_successfully'),
            'post' => $post->toArray(),
        ]);
    }

    /** @test */
    public function it_handles_post_scheduling_failure()
    {
        // Arrange
        $post = Post::factory()->create(['company_id' => $this->company->id]);
        $scheduledAt = now()->addHour();

        $this->postService
            ->shouldReceive('schedulePost')
            ->with($post, Mockery::type(\DateTime::class))
            ->once()
            ->andThrow(new \Exception('Schedule failed'));

        // Act
        $response = $this->actingAs($this->user)->post(route('posts.schedule', $post), [
            'scheduled_at' => $scheduledAt->toDateTimeString(),
        ]);

        // Assert
        $response->assertStatus(500);
        $response->assertJson([
            'message' => __('posts.schedule_failed'),
            'error' => 'Schedule failed',
        ]);
    }

    /** @test */
    public function it_validates_schedule_request()
    {
        // Arrange
        $post = Post::factory()->create(['company_id' => $this->company->id]);

        // Act
        $response = $this->actingAs($this->user)->post(route('posts.schedule', $post), [
            'scheduled_at' => now()->subHour()->toDateTimeString(), // Past date
        ]);

        // Assert
        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_get_post_statistics()
    {
        // Arrange
        $posts = collect([
            Post::factory()->create(['company_id' => $this->company->id, 'status' => PostStatus::DRAFT]),
            Post::factory()->create(['company_id' => $this->company->id, 'status' => PostStatus::SCHEDULED]),
            Post::factory()->create(['company_id' => $this->company->id, 'status' => PostStatus::PUBLISHED]),
        ]);

        $this->postService
            ->shouldReceive('getCompanyPosts')
            ->with($this->company->id)
            ->once()
            ->andReturn($posts);

        // Act
        $response = $this->actingAs($this->user)->get(route('posts.stats'));

        // Assert
        $response->assertStatus(200);
        $response->assertJson([
            'total' => 3,
            'drafts' => 1,
            'scheduled' => 1,
            'published' => 1,
            'failed' => 0,
        ]);
    }

    /** @test */
    public function it_requires_authentication()
    {
        // Act & Assert
        $this->get(route('posts.index'))->assertRedirect(route('login'));
        $this->get(route('posts.create'))->assertRedirect(route('login'));
        $this->post(route('posts.store'), [])->assertRedirect(route('login'));
    }

    /** @test */
    public function it_requires_user_to_have_current_company()
    {
        // Arrange
        $userWithoutCompany = User::factory()->create(['current_company_id' => null]);

        // Act & Assert
        $response = $this->actingAs($userWithoutCompany)->get(route('posts.index'));

        // This should redirect to company creation or show an error
        $response->assertStatus(500); // Will fail because currentCompany is null
    }
}
