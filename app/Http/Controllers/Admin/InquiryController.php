<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\InquiryService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controller for managing inquiries (admin only).
 *
 * Handles viewing, searching, sorting, and deleting inquiries.
 * All operations require admin privileges.
 */
class InquiryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected InquiryService $inquiryService
    ) {}

    /**
     * Display paginated list of inquiries.
     */
    public function index(Request $request): Response
    {
        // Get inquiries with filters
        $inquiries = $this->inquiryService->getInquiries([
            'search' => $request->input('search'),
            'sort' => $request->input('sort', 'created_at'),
            'direction' => $request->input('direction', 'desc'),
            'per_page' => 15,
        ]);

        // Normalize created_at to user's timezone for consistent display
        $timezone = optional($request->user())->timezone ?: 'UTC';
        $inquiries->setCollection(
            $inquiries->getCollection()->transform(function ($inq) use ($timezone) {
                $inq->created_at_display = optional($inq->created_at)
                    ? $inq->created_at->copy()->timezone('UTC')->timezone($timezone)->format('Y-m-d H:i:s')
                    : null;

                return $inq;
            })
        );

        // Get inquiry statistics
        $stats = $this->inquiryService->getInquiryStats();

        return Inertia::render('Admin/Inquiries/Index', [
            'inquiries' => $inquiries,
            'filters' => [
                'search' => $request->input('search'),
                'sort' => $request->input('sort', 'created_at'),
                'direction' => $request->input('direction', 'desc'),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Delete an inquiry.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        try {
            $this->inquiryService->deleteInquiry($id);

            return back()->with('toast', [
                'message' => 'Inquiry deleted successfully.',
                'type' => 'success',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'message' => 'Failed to delete inquiry: '.$e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    /**
     * Export inquiries to CSV.
     *
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        return $this->inquiryService->exportInquiriesToCsv([
            'search' => $request->input('search'),
            'sort' => $request->input('sort', 'created_at'),
            'direction' => $request->input('direction', 'desc'),
        ]);
    }
}
