<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Supported locales
     */
    protected array $supportedLocales = ['en', 'ru', 'es'];

    /**
     * Handle an incoming request.
     *
     * Priority order:
     * 1. User's saved locale preference (database)
     * 2. Session locale (for guests)
     * 3. URL segment (legacy support)
     * 4. Browser Accept-Language header
     * 5. Default to English
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        // Priority 1: User's saved preference
        if ($request->user() && $request->user()->locale) {
            $locale = $request->user()->locale;
        }

        // Priority 2: Session (for guests who changed language)
        if (! $locale && session()->has('locale')) {
            $locale = session('locale');
        }

        // Priority 3: URL segment (for SEO and shareable links)
        if (! $locale) {
            $urlLocale = $request->segment(1);
            if (in_array($urlLocale, $this->supportedLocales)) {
                $locale = $urlLocale;
            }
        }

        // Priority 4: Browser language
        if (! $locale) {
            $browserLang = substr($request->getPreferredLanguage(), 0, 2);
            if (in_array($browserLang, $this->supportedLocales)) {
                $locale = $browserLang;
            }
        }

        // Default to English
        $locale = $locale ?? 'en';

        // Validate and set locale
        if (in_array($locale, $this->supportedLocales)) {
            App::setLocale($locale);
        } else {
            App::setLocale('en');
        }

        return $next($request);
    }
}
