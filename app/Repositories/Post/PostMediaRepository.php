<?php

namespace App\Repositories\Post;

use App\Models\PostMedia;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

/**
 * Post Media Repository
 *
 * Handles all database operations for post media.
 */
class PostMediaRepository extends BaseRepository
{
    /**
     * Constructor
     */
    public function __construct(PostMedia $model)
    {
        $this->model = $model;
    }

    /**
     * Get media by post ID
     */
    public function getByPost(int $postId): Collection
    {
        return $this->model->query()
            ->where('post_id', $postId)
            ->orderBy('order')
            ->get();
    }

    /**
     * Get media by type
     */
    public function getByType(string $type): Collection
    {
        return $this->model->query()
            ->where('type', $type)
            ->with('post')
            ->get();
    }

    /**
     * Get media statistics
     */
    public function getStats(): array
    {
        $stats = $this->model->query()
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) as images,
                SUM(CASE WHEN type = ? THEN 1 ELSE 0 END) as videos,
                SUM(file_size) as total_size
            ', ['image', 'video'])
            ->first();

        return [
            'total' => $stats->total ?? 0,
            'images' => $stats->images ?? 0,
            'videos' => $stats->videos ?? 0,
            'total_size' => $stats->total_size ?? 0,
        ];
    }

    /**
     * Get media by post with pagination
     *
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginateByPost(int $postId, int $perPage = 10)
    {
        return $this->model->query()
            ->where('post_id', $postId)
            ->orderBy('order')
            ->paginate($perPage);
    }
}
