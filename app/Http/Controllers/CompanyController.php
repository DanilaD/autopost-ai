<?php

namespace App\Http\Controllers;

use App\Services\CompanyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
    /**
     * Constructor dependency injection
     */
    public function __construct(
        private CompanyService $companyService
    ) {}

    /**
     * Display the company creation form.
     */
    public function create(): Response
    {
        return Inertia::render('Company/Create');
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Check if user is authenticated
        if (! $user) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
        ]);

        try {
            $company = $this->companyService->createCompany($user, $validated);

            // Refresh user relationships to ensure they're loaded
            $user->load('companies', 'currentCompany');

            return redirect()->route('dashboard')->with('toast', [
                'message' => "Company '{$company->name}' created successfully! You can now connect Instagram accounts and create posts.",
                'type' => 'success',
            ]);
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('dashboard')->with('toast', [
                'message' => $e->getMessage(),
                'type' => 'warning',
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('toast', [
                'message' => 'Failed to create company: '.$e->getMessage(),
                'type' => 'error',
            ]);
        }
    }
}
