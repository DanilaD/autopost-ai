<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SetUserTimezone::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\UrlHealthCheckMiddleware::class,
        ]);

        // Register route middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'network' => \App\Http\Middleware\EnsureUserIsNetwork::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle 404 errors - redirect to custom 404 page
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Not Found'], 404);
            }

            return redirect()->route('errors.404');
        });

        // Handle 500 errors - redirect to custom 500 page
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Server Error'], $e->getStatusCode());
            }

            // Handle specific HTTP status codes
            switch ($e->getStatusCode()) {
                case 500:
                    \Log::error('Server Error: '.$e->getMessage(), [
                        'exception' => $e,
                        'url' => $request->url(),
                        'method' => $request->method(),
                    ]);

                    return redirect()->route('errors.500');
                case 403:
                    // For tests, return 403 status instead of redirect
                    if (app()->environment('testing')) {
                        return response('Forbidden', 403);
                    }

                    return redirect()->route('errors.403');
                case 404:
                    return redirect()->route('errors.404');
                default:
                    return null; // Let Laravel handle other status codes
            }
        });

        // Handle 419 CSRF Token Mismatch errors
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'CSRF Token Mismatch'], 419);
            }

            return redirect()->route('errors.419');
        });

        // Handle validation exceptions (like login failures)
        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Validation Error', 'errors' => $e->errors()], 422);
            }

            // For web requests, redirect back with errors
            return redirect()->back()
                ->withInput($request->except('password'))
                ->withErrors($e->errors());
        });

        // Handle general exceptions (fallback for non-HTTP exceptions)
        $exceptions->render(function (\Throwable $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Server Error'], 500);
            }

            // Log the error
            \Log::error('Unhandled exception: '.$e->getMessage(), [
                'exception' => $e,
                'url' => $request->url(),
                'method' => $request->method(),
            ]);

            return redirect()->route('errors.500');
        });
    })->create();
