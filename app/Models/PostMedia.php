<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * Post Media Model
 *
 * Represents media files (images/videos) attached to posts.
 *
 * @property int $id
 * @property int $post_id
 * @property string $type
 * @property string $filename
 * @property string $original_filename
 * @property string $mime_type
 * @property int $file_size
 * @property string $storage_path
 * @property string|null $url
 * @property int $order
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PostMedia extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
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

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'metadata' => 'array',
        'file_size' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the post that owns the media
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the full storage path
     */
    public function getFullPathAttribute(): string
    {
        return Storage::path($this->storage_path);
    }

    /**
     * Get the public URL for the media
     */
    public function getPublicUrlAttribute(): string
    {
        return $this->url ?? Storage::url($this->storage_path);
    }

    /**
     * Get human readable file size
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Check if media is an image
     */
    public function isImage(): bool
    {
        return $this->type === 'image';
    }

    /**
     * Check if media is a video
     */
    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    /**
     * Get image dimensions from metadata
     */
    public function getDimensionsAttribute(): ?array
    {
        return $this->metadata['dimensions'] ?? null;
    }

    /**
     * Get video duration from metadata
     */
    public function getDurationAttribute(): ?int
    {
        return $this->metadata['duration'] ?? null;
    }
}
