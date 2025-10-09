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
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->segment(1);

        if (in_array($locale, $this->supportedLocales)) {
            App::setLocale($locale);
        } else {
            // Default to English if no locale or invalid locale
            App::setLocale('en');
        }

        return $next($request);
    }
}
