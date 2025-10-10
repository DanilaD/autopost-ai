<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    /**
     * Supported locales
     */
    protected array $supportedLocales = ['en', 'ru', 'es'];

    /**
     * Handle the incoming request to change locale
     */
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in($this->supportedLocales)],
        ]);

        $locale = $validated['locale'];

        // Update user's locale preference if authenticated
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        // Set application locale
        App::setLocale($locale);

        // Store in session for guests
        session(['locale' => $locale]);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => __('Language changed successfully!'),
        ]);
    }
}
