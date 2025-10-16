<?php

namespace App\Services;

use App\Models\Inquiry;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

/**
 * Service for managing inquiry operations.
 *
 * Handles inquiry listing, search, filtering, and deletion.
 * All business logic for inquiry management is centralized here.
 */
class InquiryService
{
    /**
     * Get paginated list of inquiries with optional filtering and sorting.
     *
     * @param array{
     *     search?: string,
     *     sort?: string,
     *     direction?: string,
     *     per_page?: int
     * } $filters
     */
    public function getInquiries(array $filters = []): LengthAwarePaginator
    {
        $query = Inquiry::query();

        // Apply search filter
        if (! empty($filters['search'])) {
            $query->where('email', 'like', '%'.$filters['search'].'%');
        }

        // Apply sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        // Validate sort field to prevent SQL injection
        $allowedSortFields = ['email', 'ip_address', 'created_at'];
        if (! in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }

        // Validate sort direction
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

        $query->orderBy($sortField, $sortDirection);

        // Paginate results
        $perPage = $filters['per_page'] ?? 15;

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Search inquiries by email.
     */
    public function searchInquiries(string $searchTerm, int $perPage = 15): LengthAwarePaginator
    {
        return Inquiry::where('email', 'like', '%'.$searchTerm.'%')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Delete an inquiry by ID.
     */
    public function deleteInquiry(int $inquiryId): bool
    {
        $inquiry = Inquiry::findOrFail($inquiryId);

        return $inquiry->delete();
    }

    /**
     * Get recent inquiries within specified days.
     */
    public function getRecentInquiries(int $days = 30): Builder
    {
        return Inquiry::where('created_at', '>=', now()->subDays($days))
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get inquiry statistics.
     *
     * @return array{
     *     total: int,
     *     today: int,
     *     this_week: int,
     *     this_month: int
     * }
     */
    public function getInquiryStats(): array
    {
        return [
            'total' => Inquiry::count(),
            'today' => Inquiry::whereDate('created_at', today())->count(),
            'this_week' => Inquiry::where('created_at', '>=', now()->startOfWeek())->count(),
            'this_month' => Inquiry::where('created_at', '>=', now()->startOfMonth())->count(),
        ];
    }

    /**
     * Export inquiries to CSV format.
     *
     * @return \Illuminate\Http\Response
     */
    public function exportInquiriesToCsv(array $filters = [])
    {
        $query = Inquiry::query();

        // Apply search filter if provided
        if (! empty($filters['search'])) {
            $query->where('email', 'like', '%'.$filters['search'].'%');
        }

        // Apply sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';

        $allowedSortFields = ['email', 'ip_address', 'created_at'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        // Stream the CSV to avoid memory issues with large datasets
        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, ['Email', 'IP Address', 'User Agent', 'Created At']);

            // Stream each inquiry
            $query->chunk(100, function ($inquiries) use ($handle) {
                foreach ($inquiries as $inquiry) {
                    fputcsv($handle, [
                        $inquiry->email,
                        $inquiry->ip_address ?? 'N/A',
                        $inquiry->user_agent ?? 'N/A',
                        $inquiry->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });

            fclose($handle);
        }, 'inquiries-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="inquiries-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    /**
     * Create a new inquiry
     *
     * @param  array  $data  Inquiry data
     */
    public function createInquiry(array $data): Inquiry
    {
        return Inquiry::create([
            'email' => $data['email'],
            'ip_address' => $data['ip_address'],
            'user_agent' => $data['user_agent'],
            'created_at' => $data['created_at'] ?? now(),
        ]);
    }
}
