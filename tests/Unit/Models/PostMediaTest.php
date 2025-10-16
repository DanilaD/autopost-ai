<?php

namespace Tests\Unit\Models;

use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostMediaTest extends TestCase
{
    use RefreshDatabase;

    private Post $post;

    private PostMedia $media;

    protected function setUp(): void
    {
        parent::setUp();

        $this->post = Post::factory()->create();

        $this->media = PostMedia::factory()->create([
            'post_id' => $this->post->id,
            'type' => 'image',
            'filename' => 'test_image.jpg',
            'original_filename' => 'original_test.jpg',
            'mime_type' => 'image/jpeg',
            'file_size' => 1024000, // 1MB
            'storage_path' => 'posts/1/test_image.jpg',
            'url' => null,
            'order' => 1,
            'metadata' => [
                'dimensions' => ['width' => 1920, 'height' => 1080],
                'duration' => null,
            ],
        ]);
    }

    /** @test */
    public function it_can_be_created_with_factory()
    {
        $this->assertInstanceOf(PostMedia::class, $this->media);
        $this->assertDatabaseHas('post_media', [
            'id' => $this->media->id,
            'post_id' => $this->post->id,
            'type' => 'image',
        ]);
    }

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $fillable = [
            'post_id',
            'type',
            'filename',
            'original_filename',
            'mime_type',
            'file_size',
            'storage_path',
            'url',
            'order',
            'metadata',
        ];

        $this->assertEquals($fillable, $this->media->getFillable());
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $this->assertIsArray($this->media->metadata);
        $this->assertIsInt($this->media->file_size);
        $this->assertIsInt($this->media->order);
    }

    /** @test */
    public function it_belongs_to_post()
    {
        $this->assertInstanceOf(Post::class, $this->media->post);
        $this->assertEquals($this->post->id, $this->media->post->id);
    }

    /** @test */
    public function it_gets_full_storage_path()
    {
        Storage::fake('public');

        $this->assertStringContainsString('posts/1/test_image.jpg', $this->media->full_path);
    }

    /** @test */
    public function it_gets_public_url_from_storage_path()
    {
        Storage::fake('public');

        $url = $this->media->public_url;
        $this->assertStringContainsString('posts/1/test_image.jpg', $url);
    }

    /** @test */
    public function it_gets_public_url_from_custom_url()
    {
        $this->media->update(['url' => 'https://example.com/custom-image.jpg']);

        $this->assertEquals('https://example.com/custom-image.jpg', $this->media->fresh()->public_url);
    }

    /** @test */
    public function it_gets_human_readable_file_size()
    {
        // Test bytes
        $this->media->update(['file_size' => 512]);
        $this->assertEquals('512 B', $this->media->fresh()->human_file_size);

        // Test KB
        $this->media->update(['file_size' => 1536]); // 1.5KB
        $this->assertEquals('1.5 KB', $this->media->fresh()->human_file_size);

        // Test MB
        $this->media->update(['file_size' => 1048576]); // 1MB
        $this->assertEquals('1 MB', $this->media->fresh()->human_file_size);

        // Test GB
        $this->media->update(['file_size' => 1073741824]); // 1GB
        $this->assertEquals('1 GB', $this->media->fresh()->human_file_size);
    }

    /** @test */
    public function it_can_check_if_image()
    {
        $this->assertTrue($this->media->isImage());
        $this->assertFalse($this->media->isVideo());

        $this->media->update(['type' => 'video']);
        $this->assertFalse($this->media->fresh()->isImage());
        $this->assertTrue($this->media->fresh()->isVideo());
    }

    /** @test */
    public function it_can_check_if_video()
    {
        $videoMedia = PostMedia::factory()->create([
            'post_id' => $this->post->id,
            'type' => 'video',
            'filename' => 'test_video.mp4',
            'mime_type' => 'video/mp4',
        ]);

        $this->assertTrue($videoMedia->isVideo());
        $this->assertFalse($videoMedia->isImage());
    }

    /** @test */
    public function it_gets_image_dimensions_from_metadata()
    {
        $dimensions = $this->media->dimensions;

        $this->assertIsArray($dimensions);
        $this->assertEquals(1920, $dimensions['width']);
        $this->assertEquals(1080, $dimensions['height']);
    }

    /** @test */
    public function it_returns_null_dimensions_when_not_available()
    {
        $this->media->update(['metadata' => []]);

        $this->assertNull($this->media->fresh()->dimensions);
    }

    /** @test */
    public function it_gets_video_duration_from_metadata()
    {
        $videoMedia = PostMedia::factory()->create([
            'post_id' => $this->post->id,
            'type' => 'video',
            'metadata' => ['duration' => 120], // 2 minutes
        ]);

        $this->assertEquals(120, $videoMedia->duration);
    }

    /** @test */
    public function it_returns_null_duration_when_not_available()
    {
        $this->assertNull($this->media->duration);
    }

    /** @test */
    public function it_can_have_video_metadata()
    {
        $videoMedia = PostMedia::factory()->create([
            'post_id' => $this->post->id,
            'type' => 'video',
            'filename' => 'test_video.mp4',
            'original_filename' => 'original_video.mp4',
            'mime_type' => 'video/mp4',
            'file_size' => 5242880, // 5MB
            'storage_path' => 'posts/1/test_video.mp4',
            'metadata' => [
                'duration' => 300, // 5 minutes
                'dimensions' => ['width' => 1280, 'height' => 720],
                'bitrate' => 2000,
                'fps' => 30,
            ],
        ]);

        $this->assertEquals(300, $videoMedia->duration);
        $this->assertEquals(['width' => 1280, 'height' => 720], $videoMedia->dimensions);
        $this->assertEquals(2000, $videoMedia->metadata['bitrate']);
        $this->assertEquals(30, $videoMedia->metadata['fps']);
    }

    /** @test */
    public function it_can_have_complex_image_metadata()
    {
        $imageMedia = PostMedia::factory()->create([
            'post_id' => $this->post->id,
            'type' => 'image',
            'metadata' => [
                'dimensions' => ['width' => 2048, 'height' => 1536],
                'exif' => [
                    'camera' => 'iPhone 12',
                    'iso' => 100,
                    'aperture' => 'f/2.4',
                    'shutter_speed' => '1/60',
                ],
                'colors' => ['#FF0000', '#00FF00', '#0000FF'],
                'dominant_color' => '#FF0000',
            ],
        ]);

        $this->assertEquals(['width' => 2048, 'height' => 1536], $imageMedia->dimensions);
        $this->assertEquals('iPhone 12', $imageMedia->metadata['exif']['camera']);
        $this->assertEquals('#FF0000', $imageMedia->metadata['dominant_color']);
    }

    /** @test */
    public function it_can_have_custom_url()
    {
        $this->media->update(['url' => 'https://cdn.example.com/images/test.jpg']);

        $this->assertEquals('https://cdn.example.com/images/test.jpg', $this->media->fresh()->url);
    }

    /** @test */
    public function it_can_have_different_order_values()
    {
        $media1 = PostMedia::factory()->create(['post_id' => $this->post->id, 'order' => 1]);
        $media2 = PostMedia::factory()->create(['post_id' => $this->post->id, 'order' => 2]);
        $media3 = PostMedia::factory()->create(['post_id' => $this->post->id, 'order' => 0]);

        $this->assertEquals(1, $media1->order);
        $this->assertEquals(2, $media2->order);
        $this->assertEquals(0, $media3->order);
    }

    /** @test */
    public function it_can_have_large_file_sizes()
    {
        $largeFile = PostMedia::factory()->create([
            'post_id' => $this->post->id,
            'file_size' => 1073741824, // 1GB
        ]);

        $this->assertEquals('1 GB', $largeFile->human_file_size);
    }

    /** @test */
    public function it_can_have_small_file_sizes()
    {
        $smallFile = PostMedia::factory()->create([
            'post_id' => $this->post->id,
            'file_size' => 256, // 256 bytes
        ]);

        $this->assertEquals('256 B', $smallFile->human_file_size);
    }

    /** @test */
    public function it_can_have_different_mime_types()
    {
        $mimeTypes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo',
        ];

        foreach ($mimeTypes as $mimeType) {
            $media = PostMedia::factory()->create([
                'post_id' => $this->post->id,
                'mime_type' => $mimeType,
            ]);

            $this->assertEquals($mimeType, $media->mime_type);
        }
    }

    /** @test */
    public function it_can_have_empty_metadata()
    {
        $this->media->update(['metadata' => null]);

        $this->assertNull($this->media->fresh()->metadata);
        $this->assertNull($this->media->fresh()->dimensions);
        $this->assertNull($this->media->fresh()->duration);
    }

    /** @test */
    public function it_can_be_soft_deleted()
    {
        $this->media->delete();

        $this->assertSoftDeleted('post_media', ['id' => $this->media->id]);
    }

    /** @test */
    public function it_cascades_delete_when_post_is_deleted()
    {
        $mediaId = $this->media->id;

        $this->post->delete();

        $this->assertSoftDeleted('post_media', ['id' => $mediaId]);
    }

    /** @test */
    public function it_can_have_multiple_media_per_post()
    {
        $media1 = PostMedia::factory()->create(['post_id' => $this->post->id, 'order' => 2]);
        $media2 = PostMedia::factory()->create(['post_id' => $this->post->id, 'order' => 3]);
        $media3 = PostMedia::factory()->create(['post_id' => $this->post->id, 'order' => 4]);

        // Refresh the post model to get updated media relationships
        $this->post->refresh();

        $this->assertCount(4, $this->post->media); // Including the one from setUp
        $this->assertEquals($this->media->id, $this->post->media->first()->id); // First should be the one from setUp (order=1)
        $this->assertEquals($media3->id, $this->post->media->last()->id); // Last should be the one with order=4
    }

    /** @test */
    public function it_can_have_mixed_media_types_per_post()
    {
        $imageMedia = PostMedia::factory()->create([
            'post_id' => $this->post->id,
            'type' => 'image',
            'order' => 1,
        ]);

        $videoMedia = PostMedia::factory()->create([
            'post_id' => $this->post->id,
            'type' => 'video',
            'order' => 2,
        ]);

        $this->assertTrue($imageMedia->isImage());
        $this->assertTrue($videoMedia->isVideo());
        $this->assertFalse($imageMedia->isVideo());
        $this->assertFalse($videoMedia->isImage());
    }
}
