<?php

namespace App\Repositories\Post;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Post Repository
 *
 * Handles all database operations for posts.
 */
class PostRepository extends BaseRepository
{
    /**
     * Constructor
     */
    public function __construct(Post $model)
    {
        $this->model = $model;
    }

    /**
     * Get posts by company ID
     */
    public function getByCompany(int $companyId, array $filters = []): Collection
    {
        $query = $this->model->query()
            ->where('company_id', $companyId)
            ->with(['creator', 'instagramAccount', 'media']);

        // Apply filters
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['instagram_account_id'])) {
            $query->where('instagram_account_id', $filters['instagram_account_id']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('caption', 'like', '%'.$filters['search'].'%');
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get posts by user ID (for individual users)
     */
    public function getByUser(int $userId, array $filters = []): Collection
    {
        $query = $this->model->query()
            ->where('created_by', $userId)
            ->whereNull('company_id')
            ->with(['creator', 'instagramAccount', 'media']);

        // Apply filters
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['instagram_account_id'])) {
            $query->where('instagram_account_id', $filters['instagram_account_id']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('caption', 'like', '%'.$filters['search'].'%');
            });
        }

        // Apply limit if specified
        if (! empty($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Paginate posts by company with filters and sorting
     *
     * @param  array{column:string,direction:string}  $sort
     */
    public function paginateByCompany(
        int $companyId,
        array $filters = [],
        array $sort = ['column' => 'created_at', 'direction' => 'desc'],
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = $this->model->query()
            ->where('company_id', $companyId)
            ->with(['creator', 'instagramAccount', 'media']);

        // Apply filters
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (! empty($filters['instagram_account_id'])) {
            $query->where('instagram_account_id', $filters['instagram_account_id']);
        }

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', '%'.$filters['search'].'%')
                    ->orWhere('caption', 'like', '%'.$filters['search'].'%');
            });
        }

        // Sorting (allowlisted)
        $allowedColumns = ['title', 'status', 'type', 'created_at', 'scheduled_at'];
        $column = in_array($sort['column'] ?? 'created_at', $allowedColumns, true)
            ? $sort['column']
            : 'created_at';
        $direction = strtolower($sort['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($column, $direction);

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get scheduled posts (ready to publish)
     */
    public function getDueForPublishing(): Collection
    {
        return $this->model->query()
            ->where('status', PostStatus::SCHEDULED)
            ->where('scheduled_at', '<=', now())
            ->with(['instagramAccount', 'media'])
            ->get();
    }

    /**
     * Paginate posts
     */
    public function paginate(int $companyId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()
            ->where('company_id', $companyId)
            ->with(['creator', 'instagramAccount', 'media'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get posts by Instagram account
     */
    public function getByAccount(int $accountId, array $filters = []): Collection
    {
        $query = $this->model->query()
            ->where('instagram_account_id', $accountId)
            ->with(['creator', 'media']);

        // Apply filters
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get post statistics for company
     */
    public function getStats(int $companyId): array
    {
        $stats = $this->model->query()
            ->where('company_id', $companyId)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as drafts,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as scheduled,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as publishing,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as published,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as failed
            ', [
                PostStatus::DRAFT->value,
                PostStatus::SCHEDULED->value,
                PostStatus::PUBLISHING->value,
                PostStatus::PUBLISHED->value,
                PostStatus::FAILED->value,
            ])
            ->first();

        return [
            'total' => $stats->total ?? 0,
            'drafts' => $stats->drafts ?? 0,
            'scheduled' => $stats->scheduled ?? 0,
            'publishing' => $stats->publishing ?? 0,
            'published' => $stats->published ?? 0,
            'failed' => $stats->failed ?? 0,
        ];
    }

    /**
     * Get recent posts
     */
    public function getRecent(int $companyId, int $limit = 5): Collection
    {
        return $this->model->query()
            ->where('company_id', $companyId)
            ->with(['instagramAccount', 'media'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
