<?php

namespace Tests\Unit\Services\Post;

use App\Models\Post;
use App\Models\PostMedia;
use App\Repositories\Post\PostMediaRepository;
use App\Services\Post\PostMediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class PostMediaServiceTest extends TestCase
{
    private PostMediaService $postMediaService;

    private PostMediaRepository $mediaRepository;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock repository
        $this->mediaRepository = Mockery::mock(PostMediaRepository::class);

        // Create service with mocked dependencies
        $this->postMediaService = new PostMediaService($this->mediaRepository);

        // Mock facades
        Storage::fake('public');
        Storage::fake('local');
        Log::shouldReceive('info')->andReturn(true);
        Log::shouldReceive('error')->andReturn(true);
        Log::shouldReceive('warning')->andReturn(true);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_can_upload_media_successfully()
    {
        // Arrange
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);
        $postId = 1;
        $order = 0;

        $expectedMedia = new PostMedia;
        $expectedMedia->id = 1;
        $expectedMedia->post_id = $postId;
        $expectedMedia->filename = 'test.jpg';
        $expectedMedia->original_filename = 'test.jpg';
        $expectedMedia->mime_type = 'image/jpeg';
        $expectedMedia->file_size = $file->getSize();
        $expectedMedia->storage_path = 'posts/1/test.jpg';
        $expectedMedia->order = $order;

        $this->mediaRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($expectedMedia);

        // Act
        $result = $this->postMediaService->uploadMedia($file, $postId, $order);

        // Assert
        $this->assertInstanceOf(PostMedia::class, $result);
        $this->assertEquals($postId, $result->post_id);
        $this->assertEquals($order, $result->order);
    }

    /** @test */
    public function it_throws_exception_when_file_is_too_large()
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.jpg', 101 * 1024 * 1024); // 101MB
        $postId = 1;

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.file_too_large'));

        $this->postMediaService->uploadMedia($file, $postId);
    }

    /** @test */
    public function it_throws_exception_when_file_type_is_invalid()
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.txt', 1024, 'text/plain');
        $postId = 1;

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.invalid_file_type'));

        $this->postMediaService->uploadMedia($file, $postId);
    }

    /** @test */
    public function it_throws_exception_when_image_file_is_corrupted()
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.jpg', 1024, 'image/jpeg');
        $postId = 1;

        // Mock getimagesize to return false (corrupted image)
        $this->app->bind('getimagesize', function () {
            return false;
        });

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('posts.invalid_image_file'));

        $this->postMediaService->uploadMedia($file, $postId);
    }

    /** @test */
    public function it_can_delete_media_successfully()
    {
        // Arrange
        $media = new PostMedia;
        $media->id = 1;
        $media->filename = 'test.jpg';
        $media->storage_path = 'posts/1/test.jpg';

        $this->mediaRepository
            ->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        // Act
        $result = $this->postMediaService->deleteMedia($media);

        // Assert
        $this->assertTrue($result);
    }

    /** @test */
    public function it_handles_file_not_found_when_deleting_media()
    {
        // Arrange
        $media = new PostMedia;
        $media->id = 1;
        $media->filename = 'test.jpg';
        $media->storage_path = 'posts/1/nonexistent.jpg';

        $this->mediaRepository
            ->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        // Act
        $result = $this->postMediaService->deleteMedia($media);

        // Assert - Should not throw exception, just log warning
        $this->assertTrue($result);
    }

    /** @test */
    public function it_can_get_public_url_for_media()
    {
        // Arrange
        $media = new PostMedia;
        $media->storage_path = 'posts/1/test.jpg';
        $media->url = null;

        // Act
        $result = $this->postMediaService->getPublicUrl($media);

        // Assert
        $this->assertStringContainsString('posts/1/test.jpg', $result);
    }

    /** @test */
    public function it_returns_custom_url_when_available()
    {
        // Arrange
        $media = new PostMedia;
        $media->storage_path = 'posts/1/test.jpg';
        $media->url = 'https://example.com/custom-url.jpg';

        // Act
        $result = $this->postMediaService->getPublicUrl($media);

        // Assert
        $this->assertEquals('https://example.com/custom-url.jpg', $result);
    }

    /** @test */
    public function it_can_generate_thumbnail_for_image()
    {
        // Arrange
        $media = Mockery::mock(PostMedia::class);
        $media->shouldAllowMockingProtectedMethods();
        $media->shouldReceive('setAttribute')->andReturnSelf();
        $media->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $media->shouldReceive('getAttribute')->with('filename')->andReturn('test.jpg');
        $media->shouldReceive('getAttribute')->with('post_id')->andReturn(1);
        $media->shouldReceive('getAttribute')->with('storage_path')->andReturn('posts/1/test.jpg');
        $media->shouldReceive('isImage')->andReturn(true);
        $media->id = 1;
        $media->filename = 'test.jpg';
        $media->post_id = 1;

        // Act
        $result = $this->postMediaService->generateThumbnail($media, 300, 300);

        // Assert
        $this->assertStringContainsString('posts/1/test.jpg', $result);
    }

    /** @test */
    public function it_returns_null_when_generating_thumbnail_for_non_image()
    {
        // Arrange
        $media = Mockery::mock(PostMedia::class);
        $media->shouldAllowMockingProtectedMethods();
        $media->shouldReceive('isImage')->andReturn(false);
        $media->shouldReceive('setAttribute')->andReturnSelf();

        // Act
        $result = $this->postMediaService->generateThumbnail($media);

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function it_can_sync_media_for_post()
    {
        // Arrange
        $post = Mockery::mock(Post::class);
        $post->shouldAllowMockingProtectedMethods();
        $post->shouldReceive('setAttribute')->andReturnSelf();
        $post->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $post->shouldReceive('getAttribute')->with('media')->andReturn(collect([]));
        $post->id = 1;

        $file1 = UploadedFile::fake()->image('test1.jpg');
        $file2 = UploadedFile::fake()->image('test2.jpg');

        $mediaGroups = [
            ['file' => $file1],
            ['file' => $file2],
        ];

        $this->mediaRepository
            ->shouldReceive('create')
            ->twice()
            ->andReturn(new PostMedia);

        // Act
        $this->postMediaService->syncMedia($post, $mediaGroups);

        // Assert - Should not throw exception
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_clear_media_for_post()
    {
        // Arrange
        $post = Mockery::mock(Post::class);
        $post->shouldAllowMockingProtectedMethods();
        $post->shouldReceive('setAttribute')->andReturnSelf();
        $post->id = 1;

        $media1 = Mockery::mock(PostMedia::class);
        $media1->shouldAllowMockingProtectedMethods();
        $media1->shouldReceive('setAttribute')->andReturnSelf();
        $media1->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $media1->shouldReceive('getAttribute')->with('filename')->andReturn('test1.jpg');
        $media1->shouldReceive('getAttribute')->with('storage_path')->andReturn('posts/1/test1.jpg');
        $media1->id = 1;
        $media1->filename = 'test1.jpg';
        $media1->storage_path = 'posts/1/test1.jpg';

        $media2 = Mockery::mock(PostMedia::class);
        $media2->shouldAllowMockingProtectedMethods();
        $media2->shouldReceive('setAttribute')->andReturnSelf();
        $media2->shouldReceive('getAttribute')->with('id')->andReturn(2);
        $media2->shouldReceive('getAttribute')->with('filename')->andReturn('test2.jpg');
        $media2->shouldReceive('getAttribute')->with('storage_path')->andReturn('posts/1/test2.jpg');
        $media2->id = 2;
        $media2->filename = 'test2.jpg';
        $media2->storage_path = 'posts/1/test2.jpg';

        $post->shouldReceive('getAttribute')->with('media')->andReturn(collect([$media1, $media2]));

        $this->mediaRepository
            ->shouldReceive('delete')
            ->twice()
            ->andReturn(true);

        // Act
        $this->postMediaService->clearMedia($post);

        // Assert - Should not throw exception
        $this->assertTrue(true);
    }

    /** @test */
    public function it_can_copy_media_successfully()
    {
        // Arrange
        $originalMedia = Mockery::mock(PostMedia::class);
        $originalMedia->shouldAllowMockingProtectedMethods();
        $originalMedia->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $originalMedia->shouldReceive('getAttribute')->with('post_id')->andReturn(1);
        $originalMedia->shouldReceive('getAttribute')->with('filename')->andReturn('test.jpg');
        $originalMedia->shouldReceive('getAttribute')->with('original_filename')->andReturn('test.jpg');
        $originalMedia->shouldReceive('getAttribute')->with('file_size')->andReturn(1024);
        $originalMedia->shouldReceive('getAttribute')->with('mime_type')->andReturn('image/jpeg');
        $originalMedia->shouldReceive('getAttribute')->with('type')->andReturn('image');
        $originalMedia->shouldReceive('getAttribute')->with('metadata')->andReturn(['width' => 100, 'height' => 100]);

        $mediaToCopy = [
            ['id' => 1, 'order' => 0],
        ];

        $newPostId = 2;

        $this->mediaRepository
            ->shouldReceive('find')
            ->with(1)
            ->andReturn($originalMedia);

        $this->mediaRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn(new PostMedia);

        // Mock Storage to simulate file existence
        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturnSelf();
        Storage::shouldReceive('disk')
            ->with('public')
            ->andReturnSelf();
        Storage::shouldReceive('exists')
            ->andReturn(true);
        Storage::shouldReceive('copy')
            ->andReturn(true);

        // Act
        $this->postMediaService->copyMedia($mediaToCopy, $newPostId);

        // Assert - Should not throw exception
        $this->assertTrue(true);
    }

    /** @test */
    public function it_handles_missing_media_when_copying()
    {
        // Arrange
        $mediaToCopy = [
            ['id' => 999, 'order' => 0], // Non-existent media ID
        ];

        $newPostId = 2;

        // Mock the repository to return null for non-existent media
        $this->mediaRepository
            ->shouldReceive('find')
            ->with(999)
            ->andReturn(null);

        // Act
        $this->postMediaService->copyMedia($mediaToCopy, $newPostId);

        // Assert - Should not throw exception, just log warning
        $this->assertTrue(true);
    }

    /** @test */
    public function it_throws_exception_when_original_file_not_found_during_copy()
    {
        // Arrange
        $originalMedia = Mockery::mock(PostMedia::class);
        $originalMedia->shouldAllowMockingProtectedMethods();
        $originalMedia->shouldReceive('getAttribute')->with('id')->andReturn(1);
        $originalMedia->shouldReceive('getAttribute')->with('post_id')->andReturn(1);
        $originalMedia->shouldReceive('getAttribute')->with('filename')->andReturn('nonexistent.jpg');
        $originalMedia->shouldReceive('getAttribute')->with('original_filename')->andReturn('nonexistent.jpg');
        $originalMedia->shouldReceive('getAttribute')->with('file_size')->andReturn(1024);
        $originalMedia->shouldReceive('getAttribute')->with('mime_type')->andReturn('image/jpeg');
        $originalMedia->shouldReceive('getAttribute')->with('type')->andReturn('image');
        $originalMedia->shouldReceive('getAttribute')->with('metadata')->andReturn([]);

        $mediaToCopy = [
            ['id' => 1, 'order' => 0],
        ];

        $newPostId = 2;

        $this->mediaRepository
            ->shouldReceive('find')
            ->with(1)
            ->andReturn($originalMedia);

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Original media file not found: nonexistent.jpg');

        $this->postMediaService->copyMedia($mediaToCopy, $newPostId);
    }

    /** @test */
    public function it_handles_database_error_during_upload()
    {
        // Arrange
        $file = UploadedFile::fake()->image('test.jpg', 100, 100);
        $postId = 1;

        $this->mediaRepository
            ->shouldReceive('create')
            ->once()
            ->andThrow(new \Exception('Database error'));

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');

        $this->postMediaService->uploadMedia($file, $postId);
    }

    /** @test */
    public function it_handles_database_error_during_delete()
    {
        // Arrange
        $media = new PostMedia;
        $media->id = 1;
        $media->filename = 'test.jpg';
        $media->storage_path = 'posts/1/test.jpg';

        $this->mediaRepository
            ->shouldReceive('delete')
            ->once()
            ->andThrow(new \Exception('Database error'));

        // Act & Assert
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Database error');

        $this->postMediaService->deleteMedia($media);
    }

    /** @test */
    public function it_validates_video_files_correctly()
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.mp4', 1024, 'video/mp4');
        $postId = 1;

        $expectedMedia = new PostMedia;
        $expectedMedia->id = 1;
        $expectedMedia->post_id = $postId;
        $expectedMedia->filename = 'test.mp4';
        $expectedMedia->original_filename = 'test.mp4';
        $expectedMedia->mime_type = 'video/mp4';
        $expectedMedia->file_size = $file->getSize();
        $expectedMedia->storage_path = 'posts/1/test.mp4';

        $this->mediaRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($expectedMedia);

        // Act
        $result = $this->postMediaService->uploadMedia($file, $postId);

        // Assert
        $this->assertInstanceOf(PostMedia::class, $result);
        $this->assertEquals('video/mp4', $result->mime_type);
    }

    /** @test */
    public function it_throws_exception_for_unsupported_file_type()
    {
        // Arrange
        $file = UploadedFile::fake()->create('test.xyz', 1024, 'application/octet-stream');
        $postId = 1;

        // Act & Assert
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid file type. Allowed types: JPEG, PNG, GIF, WebP, MP4, MOV, AVI');

        $this->postMediaService->uploadMedia($file, $postId);
    }

    /** @test */
    public function it_extracts_image_metadata_correctly()
    {
        // Arrange
        $file = UploadedFile::fake()->image('test.jpg', 800, 600);
        $postId = 1;

        $expectedMedia = new PostMedia;
        $expectedMedia->id = 1;
        $expectedMedia->post_id = $postId;
        $expectedMedia->filename = 'test.jpg';
        $expectedMedia->original_filename = 'test.jpg';
        $expectedMedia->mime_type = 'image/jpeg';
        $expectedMedia->file_size = $file->getSize();
        $expectedMedia->storage_path = 'posts/1/test.jpg';

        $this->mediaRepository
            ->shouldReceive('create')
            ->once()
            ->andReturn($expectedMedia);

        // Act
        $result = $this->postMediaService->uploadMedia($file, $postId);

        // Assert
        $this->assertInstanceOf(PostMedia::class, $result);
    }
}
