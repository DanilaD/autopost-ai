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
     * 2. Session locale (current session)
     * 3. LocalStorage via cookie (persists across sessions for guests)
     * 4. URL segment (for SEO and shareable links)
     * 5. Browser Accept-Language header
     * 6. Default to English
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = null;

        // Priority 1: User's saved preference (database)
        if ($request->user() && $request->user()->locale) {
            $locale = $request->user()->locale;
        }

        // Priority 2: Session (current session)
        if (! $locale && session()->has('locale')) {
            $locale = session('locale');
        }

        // Priority 3: LocalStorage preference (sent via cookie for server-side detection)
        // The cookie is only used to pass LocalStorage value to server, not for storage
        if (! $locale && $request->cookie('preferred_locale')) {
            $cookieLocale = $request->cookie('preferred_locale');
            if (in_array($cookieLocale, $this->supportedLocales)) {
                $locale = $cookieLocale;
                // Store in session for this request
                session(['locale' => $locale]);
            }
        }

        // Priority 4: URL segment (for SEO and shareable links)
        if (! $locale) {
            $urlLocale = $request->segment(1);
            if (in_array($urlLocale, $this->supportedLocales)) {
                $locale = $urlLocale;
            }
        }

        // Priority 5: Browser language
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
