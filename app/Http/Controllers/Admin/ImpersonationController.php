<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImpersonationService;
use Illuminate\Http\Request;

/**
 * Controller for managing user impersonation (admin only).
 * 
 * Allows admins to impersonate users for support and debugging.
 * All operations are logged for audit purposes.
 */
class ImpersonationController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected ImpersonationService $impersonationService
    ) {}

    /**
     * Start impersonating a user.
     *
     * @param Request $request
     * @param int $id User ID to impersonate
     * @return \Illuminate\Http\RedirectResponse
     */
    public function start(Request $request, int $id)
    {
        try {
            $this->impersonationService->impersonate($request->user(), $id);

            return redirect()->route('dashboard')->with('toast', [
                'message' => 'You are now impersonating the user.',
                'type' => 'info',
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->with('toast', [
                'message' => $e->getMessage(),
                'type' => 'error',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'message' => 'Failed to start impersonation: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }

    /**
     * Stop impersonating and return to admin account.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function stop(Request $request)
    {
        try {
            $this->impersonationService->stopImpersonation();

            return redirect()->route('admin.users.index')->with('toast', [
                'message' => 'Impersonation ended. You are back to your account.',
                'type' => 'success',
            ]);
        } catch (\InvalidArgumentException $e) {
            return back()->with('toast', [
                'message' => $e->getMessage(),
                'type' => 'error',
            ]);
        } catch (\Exception $e) {
            return back()->with('toast', [
                'message' => 'Failed to stop impersonation: ' . $e->getMessage(),
                'type' => 'error',
            ]);
        }
    }
}

