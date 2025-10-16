<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CompanyController extends Controller
{
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

        // Check if user already has a company
        if ($user->currentCompany) {
            return redirect()->route('dashboard')->with('toast', [
                'message' => 'You already have a company. You can only create one company per account.',
                'type' => 'warning',
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:companies,name',
        ]);

        // Use database transaction to ensure everything is committed together
        DB::transaction(function () use ($validated, $user, &$company) {
            // Create the company
            $company = Company::create([
                'name' => $validated['name'],
                'owner_id' => $user->id,
                'settings' => [
                    'timezone' => $user->timezone ?? 'UTC',
                    'locale' => $user->locale ?? 'en',
                ],
            ]);

            // Attach user to company as network (company admin)
            $company->users()->attach($user->id, ['role' => 'network']);

            // Set as current company
            $user->update(['current_company_id' => $company->id]);
        });

        // Refresh user relationships to ensure they're loaded
        $user->load('companies', 'currentCompany');

        return redirect()->route('dashboard')->with('toast', [
            'message' => "Company '{$company->name}' created successfully! You can now connect Instagram accounts and create posts.",
            'type' => 'success',
        ]);
    }
}
