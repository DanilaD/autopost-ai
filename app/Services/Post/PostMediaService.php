<?php

namespace App\Services\Post;

use App\Models\PostMedia;
use App\Repositories\Post\PostMediaRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Post Media Service
 *
 * Handles media upload and processing for posts.
 */
class PostMediaService
{
    /**
     * Constructor dependency injection
     */
    public function __construct(
        private PostMediaRepository $mediaRepository
    ) {}

    /**
     * Upload and process media file
     *
     * @throws \Exception
     */
    public function uploadMedia(UploadedFile $file, int $postId, int $order = 0): PostMedia
    {
        // Validate file
        $this->validateFile($file);

        // Generate unique filename
        $filename = $this->generateFilename($file);
        $storagePath = "posts/{$postId}/{$filename}";

        try {
            // Store file
            $path = $file->storeAs("posts/{$postId}", $filename, 'public');

            // Get file metadata
            $metadata = $this->extractMetadata($file, $path);

            // Create media record
            $media = $this->mediaRepository->create([
                'post_id' => $postId,
                'type' => $this->getMediaType($file),
                'filename' => $filename,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'storage_path' => $path,
                'order' => $order,
                'metadata' => $metadata,
            ]);

            Log::info('Media uploaded successfully', [
                'post_id' => $postId,
                'media_id' => $media->id,
                'filename' => $filename,
            ]);

            return $media;

        } catch (\Exception $e) {
            // Clean up file if database operation fails
            if (Storage::exists($path)) {
                Storage::delete($path);
            }

            Log::error('Failed to upload media', [
                'post_id' => $postId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Delete media file and record
     */
    public function deleteMedia(PostMedia $media): bool
    {
        try {
            // Delete file from storage
            if (Storage::disk('public')->exists($media->storage_path)) {
                Storage::disk('public')->delete($media->storage_path);
            }

            // Delete database record
            $deleted = $this->mediaRepository->delete($media);

            Log::info('Media deleted successfully', [
                'media_id' => $media->id,
                'filename' => $media->filename,
            ]);

            return $deleted;

        } catch (\Exception $e) {
            Log::error('Failed to delete media', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get public URL for media
     */
    public function getPublicUrl(PostMedia $media): string
    {
        if ($media->url) {
            return $media->url;
        }

        return Storage::url($media->storage_path);
    }

    /**
     * Generate thumbnail for image
     */
    public function generateThumbnail(PostMedia $media, int $width = 300, int $height = 300): ?string
    {
        if (! $media->isImage()) {
            return null;
        }

        try {
            $thumbnailPath = "posts/{$media->post_id}/thumbnails/{$width}x{$height}_{$media->filename}";

            // Use Intervention Image or similar library here
            // For now, return the original path
            return Storage::url($media->storage_path);

        } catch (\Exception $e) {
            Log::error('Failed to generate thumbnail', [
                'media_id' => $media->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file): void
    {
        // Check file size (max 100MB)
        if ($file->getSize() > 100 * 1024 * 1024) {
            throw new \InvalidArgumentException(__('posts.file_too_large'));
        }

        // Check file type
        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'video/mp4',
            'video/quicktime',
            'video/x-msvideo',
        ];

        if (! in_array($file->getMimeType(), $allowedMimes)) {
            throw new \InvalidArgumentException(__('posts.invalid_file_type'));
        }

        // Check if file is actually an image or video
        $mediaType = $this->getMediaType($file);
        if ($mediaType === 'image' && ! $this->isValidImage($file)) {
            throw new \InvalidArgumentException(__('posts.invalid_image_file'));
        }

        if ($mediaType === 'video' && ! $this->isValidVideo($file)) {
            throw new \InvalidArgumentException(__('posts.invalid_video_file'));
        }
    }

    /**
     * Generate unique filename
     */
    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y_m_d_H_i_s');
        $random = Str::random(8);

        return "{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get media type from file
     */
    private function getMediaType(UploadedFile $file): string
    {
        $mimeType = $file->getMimeType();

        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        throw new \InvalidArgumentException(__('posts.unsupported_file_type'));
    }

    /**
     * Extract metadata from file
     */
    private function extractMetadata(UploadedFile $file, string $path): array
    {
        $metadata = [];

        if ($this->getMediaType($file) === 'image') {
            $metadata = $this->extractImageMetadata($file, $path);
        } elseif ($this->getMediaType($file) === 'video') {
            $metadata = $this->extractVideoMetadata($file, $path);
        }

        return $metadata;
    }

    /**
     * Extract image metadata
     */
    private function extractImageMetadata(UploadedFile $file, string $path): array
    {
        try {
            $imageInfo = getimagesize(Storage::path($path));

            return [
                'dimensions' => [
                    'width' => $imageInfo[0] ?? null,
                    'height' => $imageInfo[1] ?? null,
                ],
                'mime_type' => $imageInfo['mime'] ?? $file->getMimeType(),
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to extract image metadata', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Extract video metadata
     */
    private function extractVideoMetadata(UploadedFile $file, string $path): array
    {
        // For now, return basic metadata
        // In production, you might want to use FFmpeg or similar
        return [
            'duration' => null, // Would need FFmpeg to extract
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Check if file is a valid image
     */
    private function isValidImage(UploadedFile $file): bool
    {
        try {
            $imageInfo = getimagesize($file->getPathname());

            return $imageInfo !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Check if file is a valid video
     */
    private function isValidVideo(UploadedFile $file): bool
    {
        // Basic validation - in production you might want more sophisticated checks
        $videoMimes = ['video/mp4', 'video/quicktime', 'video/x-msvideo'];

        return in_array($file->getMimeType(), $videoMimes);
    }

    /**
     * Sync media for a post (delete existing and upload new)
     */
    public function syncMedia(\App\Models\Post $post, array $mediaGroups): void
    {
        // Delete existing media
        $this->clearMedia($post);

        // Upload new media
        foreach ($mediaGroups as $index => $group) {
            if (is_array($group) && isset($group['file']) && $group['file'] instanceof UploadedFile) {
                $this->uploadMedia($group['file'], $post->id, $index);
            }
        }
    }

    /**
     * Clear all media for a post
     */
    public function clearMedia(\App\Models\Post $post): void
    {
        $mediaItems = $post->media;

        foreach ($mediaItems as $media) {
            $this->deleteMedia($media);
        }
    }

    /**
     * Copy media files from one post to another
     *
     * @param  array  $mediaToCopy  Array of media IDs to copy
     * @param  int  $newPostId  The new post ID
     *
     * @throws \Exception
     */
    public function copyMedia(array $mediaToCopy, int $newPostId): void
    {
        foreach ($mediaToCopy as $mediaData) {
            $originalMedia = PostMedia::find($mediaData['id']);

            if (! $originalMedia) {
                Log::warning("Media not found for copying: {$mediaData['id']}");

                continue;
            }

            // Generate new filename
            $extension = pathinfo($originalMedia->filename, PATHINFO_EXTENSION);
            $newFilename = Str::uuid().'.'.$extension;
            $newStoragePath = "posts/{$newPostId}/{$newFilename}";

            try {
                // Copy the file - check both private and public storage
                $originalPath = "posts/{$originalMedia->post_id}/{$originalMedia->filename}";
                $privatePath = "posts/{$originalMedia->post_id}/{$originalMedia->filename}";

                // Try to copy from private storage first (old files), then public storage (new files)
                if (Storage::disk('local')->exists($privatePath)) {
                    $newPath = Storage::disk('local')->copy($privatePath, "private/{$newStoragePath}");
                    $newStoragePath = "private/{$newStoragePath}";
                } elseif (Storage::disk('public')->exists($originalPath)) {
                    $newPath = Storage::disk('public')->copy($originalPath, $newStoragePath);
                } else {
                    throw new \Exception("Original media file not found: {$originalMedia->filename}");
                }

                if (! $newPath) {
                    throw new \Exception("Failed to copy media file: {$originalMedia->filename}");
                }

                // Create new media record
                $this->mediaRepository->create([
                    'post_id' => $newPostId,
                    'filename' => $newFilename,
                    'original_filename' => $originalMedia->original_filename,
                    'storage_path' => $newStoragePath,
                    'file_size' => $originalMedia->file_size,
                    'mime_type' => $originalMedia->mime_type,
                    'type' => $originalMedia->type,
                    'order' => $mediaData['order'] ?? 0,
                    'metadata' => $originalMedia->metadata,
                ]);

                Log::info("Successfully copied media: {$originalMedia->filename} to post {$newPostId}");

            } catch (\Exception $e) {
                Log::error("Failed to copy media {$originalMedia->filename}: ".$e->getMessage());
                throw $e;
            }
        }
    }
}
