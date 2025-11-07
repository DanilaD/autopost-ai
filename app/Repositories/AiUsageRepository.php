<?php

namespace App\Repositories;

use App\Models\AiUsage;
use Illuminate\Database\Eloquent\Collection;

/**
 * AI Usage Repository
 *
 * Handles all database operations for AI usage statistics.
 * Follows the repository pattern for clean separation of concerns.
 *
 * @version 1.0
 *
 * @since 2025-10-17
 */
class AiUsageRepository
{
    /**
     * Create or update daily usage record
     */
    public function updateOrCreateDailyUsage(array $data): AiUsage
    {
        return AiUsage::updateOrCreate(
            [
                'company_id' => $data['company_id'],
                'user_id' => $data['user_id'],
                'provider' => $data['provider'],
                'model' => $data['model'],
                'type' => $data['type'],
                'usage_date' => $data['usage_date'],
            ],
            [
                'tokens_used' => $data['tokens_used'],
                'cost_usd' => $data['cost_usd'],
                'requests_count' => $data['requests_count'],
            ]
        );
    }

    /**
     * Get usage for company
     */
    public function getForCompany(int $companyId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = AiUsage::forCompany($companyId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->orderBy('usage_date', 'desc')->get();
    }

    /**
     * Get usage for user
     */
    public function getForUser(int $userId, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = AiUsage::forUser($userId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->orderBy('usage_date', 'desc')->get();
    }

    /**
     * Get usage by provider
     */
    public function getByProvider(string $provider, ?int $companyId = null, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = AiUsage::byProvider($provider);

        if ($companyId) {
            $query->forCompany($companyId);
        }

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->orderBy('usage_date', 'desc')->get();
    }

    /**
     * Get usage by type
     */
    public function getByType(string $type, ?int $companyId = null, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = AiUsage::ofType($type);

        if ($companyId) {
            $query->forCompany($companyId);
        }

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->orderBy('usage_date', 'desc')->get();
    }

    /**
     * Get total usage statistics
     */
    public function getTotalUsage(int $companyId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = AiUsage::forCompany($companyId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        $usage = $query->selectRaw('SUM(tokens_used) as total_tokens, SUM(cost_usd) as total_cost, SUM(requests_count) as total_requests')
            ->first();

        return [
            'total_tokens' => $usage->total_tokens ?? 0,
            'total_cost' => $usage->total_cost ?? 0,
            'total_requests' => $usage->total_requests ?? 0,
        ];
    }

    /**
     * Get usage grouped by provider
     */
    public function getUsageByProvider(int $companyId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = AiUsage::forCompany($companyId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->selectRaw('provider, SUM(tokens_used) as total_tokens, SUM(cost_usd) as total_cost, SUM(requests_count) as total_requests')
            ->groupBy('provider')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->provider => [
                        'tokens' => $item->total_tokens,
                        'cost' => $item->total_cost,
                        'requests' => $item->total_requests,
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * Get usage grouped by type
     */
    public function getUsageByType(int $companyId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = AiUsage::forCompany($companyId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->selectRaw('type, SUM(tokens_used) as total_tokens, SUM(cost_usd) as total_cost, SUM(requests_count) as total_requests')
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->type => [
                        'tokens' => $item->total_tokens,
                        'cost' => $item->total_cost,
                        'requests' => $item->total_requests,
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * Get daily usage breakdown
     */
    public function getDailyUsage(int $companyId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = AiUsage::forCompany($companyId);

        if ($startDate && $endDate) {
            $query->dateRange($startDate, $endDate);
        }

        return $query->selectRaw('usage_date, SUM(tokens_used) as total_tokens, SUM(cost_usd) as total_cost, SUM(requests_count) as total_requests')
            ->groupBy('usage_date')
            ->orderBy('usage_date', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->usage_date => [
                        'tokens' => $item->total_tokens,
                        'cost' => $item->total_cost,
                        'requests' => $item->total_requests,
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * Get monthly usage breakdown
     */
    public function getMonthlyUsage(int $companyId, int $months = 12): array
    {
        $startDate = now()->subMonths($months)->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();

        $query = AiUsage::forCompany($companyId)
            ->dateRange($startDate, $endDate);

        return $query->selectRaw('DATE_FORMAT(usage_date, "%Y-%m") as month, SUM(tokens_used) as total_tokens, SUM(cost_usd) as total_cost, SUM(requests_count) as total_requests')
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [
                    $item->month => [
                        'tokens' => $item->total_tokens,
                        'cost' => $item->total_cost,
                        'requests' => $item->total_requests,
                    ],
                ];
            })
            ->toArray();
    }

    /**
     * Check if company has exceeded daily limit
     */
    public function hasExceededDailyLimit(int $companyId, float $dailyLimit): bool
    {
        $todayUsage = AiUsage::forCompany($companyId)
            ->where('usage_date', now()->toDateString())
            ->sum('cost_usd');

        return $todayUsage > $dailyLimit;
    }

    /**
     * Check if company has exceeded monthly limit
     */
    public function hasExceededMonthlyLimit(int $companyId, float $monthlyLimit): bool
    {
        $monthStart = now()->startOfMonth()->toDateString();
        $monthEnd = now()->endOfMonth()->toDateString();

        $monthlyUsage = AiUsage::forCompany($companyId)
            ->dateRange($monthStart, $monthEnd)
            ->sum('cost_usd');

        return $monthlyUsage > $monthlyLimit;
    }

    /**
     * Delete old usage records (cleanup)
     */
    public function deleteOldUsage(int $daysOld = 365): int
    {
        $cutoffDate = now()->subDays($daysOld);

        return AiUsage::where('usage_date', '<', $cutoffDate)->delete();
    }
}
