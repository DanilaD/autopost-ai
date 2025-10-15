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
     *
     * @param Request $request
     * @return Response
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
     * @param int $id
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
                'message' => 'Failed to delete inquiry: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    /**
     * Export inquiries to CSV.
     *
     * @param Request $request
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

