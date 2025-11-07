<?php

namespace App\Repositories;

use App\Models\AiGeneration;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * AI Generation Repository
 *
 * Handles all database operations for AI generations.
 * Follows the repository pattern for clean separation of concerns.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class AiGenerationRepository
{
    /**
     * Create a new AI generation record
     */
    public function create(array $data): AiGeneration
    {
        return AiGeneration::create($data);
    }

    /**
     * Find AI generation by ID
     */
    public function findById(int $id): ?AiGeneration
    {
        return AiGeneration::find($id);
    }

    /**
     * Get generations for a company
     */
    public function getForCompany(int $companyId, int $limit = 50): Collection
    {
        return AiGeneration::forCompany($companyId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get generations for a user
     */
    public function getForUser(int $userId, int $limit = 50): Collection
    {
        return AiGeneration::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get generations by type
     */
    public function getByType(string $type, ?int $companyId = null, int $limit = 50): Collection
    {
        $query = AiGeneration::ofType($type)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($companyId) {
            $query->forCompany($companyId);
        }

        return $query->get();
    }

    /**
     * Get generations by provider
     */
    public function getByProvider(string $provider, ?int $companyId = null, int $limit = 50): Collection
    {
        $query = AiGeneration::byProvider($provider)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($companyId) {
            $query->forCompany($companyId);
        }

        return $query->get();
    }

    /**
     * Get paginated generations
     */
    public function getPaginated(int $companyId, int $perPage = 20): LengthAwarePaginator
    {
        return AiGeneration::forCompany($companyId)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get generations within date range
     */
    public function getByDateRange(int $companyId, string $startDate, string $endDate): Collection
    {
        return AiGeneration::forCompany($companyId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get total cost for company
     */
    public function getTotalCostForCompany(int $companyId, ?string $startDate = null, ?string $endDate = null): float
    {
        $query = AiGeneration::forCompany($companyId);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->sum('cost_credits') / 1000000; // Convert from micro-dollars
    }

    /**
     * Get total tokens used for company
     */
    public function getTotalTokensForCompany(int $companyId, ?string $startDate = null, ?string $endDate = null): int
    {
        $query = AiGeneration::forCompany($companyId);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->sum('tokens_used');
    }

    /**
     * Get generation statistics by provider
     */
    public function getStatsByProvider(int $companyId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = AiGeneration::forCompany($companyId);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->selectRaw('provider, COUNT(*) as count, SUM(tokens_used) as total_tokens, SUM(cost_credits) as total_cost')
            ->groupBy('provider')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->provider => [
                        'count' => $item->count,
                        'total_tokens' => $item->total_tokens,
                        'total_cost' => $item->total_cost / 1000000, // Convert from micro-dollars
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * Get generation statistics by type
     */
    public function getStatsByType(int $companyId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = AiGeneration::forCompany($companyId);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        return $query->selectRaw('type, COUNT(*) as count, SUM(tokens_used) as total_tokens, SUM(cost_credits) as total_cost')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->type => [
                        'count' => $item->count,
                        'total_tokens' => $item->total_tokens,
                        'total_cost' => $item->total_cost / 1000000, // Convert from micro-dollars
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * Delete old generations (cleanup)
     */
    public function deleteOldGenerations(int $daysOld = 90): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return AiGeneration::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Update generation metadata
     */
    public function updateMetadata(int $id, array $metadata): bool
    {
        $generation = $this->findById($id);

        if (! $generation) {
            return false;
        }

        $generation->metadata = array_merge($generation->metadata ?? [], $metadata);

        return $generation->save();
    }
}
