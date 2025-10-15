<?php

use App\Http\Controllers\Auth\EmailCheckController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Welcome page - email-first entry
Route::get('/', function () {
    return Inertia::render('WelcomeSimple', [
        'email' => session('email'),
        'mode' => session('mode'), // 'register' or 'login'
        'message' => session('message'),
    ]);
})->middleware('guest')->name('home');

// Email check route
Route::post('/auth/email/check', EmailCheckController::class)
    ->middleware('guest')
    ->name('auth.email.check');

// Dashboard
Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// Profile routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Locale switcher (available to everyone)
Route::post('/locale', \App\Http\Controllers\LocaleController::class)->name('locale.change');

// Instagram routes
Route::middleware(['auth', 'verified'])->prefix('instagram')->name('instagram.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Instagram\InstagramAccountController::class, 'index'])->name('index');
    Route::get('/connect', [\App\Http\Controllers\Instagram\InstagramOAuthController::class, 'redirect'])->name('connect');
    Route::get('/callback', [\App\Http\Controllers\Instagram\InstagramOAuthController::class, 'callback'])->name('callback');
    Route::post('/{account}/disconnect', [\App\Http\Controllers\Instagram\InstagramAccountController::class, 'disconnect'])->name('disconnect');
    Route::post('/{account}/sync', [\App\Http\Controllers\Instagram\InstagramAccountController::class, 'sync'])->name('sync');
});

// Admin routes - requires admin role in current company
Route::middleware(['auth', 'verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Stop impersonation - accessible to anyone who's impersonating (no admin check)
        Route::post('/impersonate/stop', [\App\Http\Controllers\Admin\ImpersonationController::class, 'stop'])
            ->name('impersonate.stop');

        // Admin-only routes
        Route::middleware('admin')->group(function () {
            // Inquiry Management
            Route::get('/inquiries', [\App\Http\Controllers\Admin\InquiryController::class, 'index'])
                ->name('inquiries.index');
            Route::delete('/inquiries/{inquiry}', [\App\Http\Controllers\Admin\InquiryController::class, 'destroy'])
                ->name('inquiries.destroy');
            Route::get('/inquiries/export', [\App\Http\Controllers\Admin\InquiryController::class, 'export'])
                ->name('inquiries.export');

            // User Management
            Route::get('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'index'])
                ->name('users.index');
            Route::post('/users/{user}/password-reset', [\App\Http\Controllers\Admin\UserManagementController::class, 'sendPasswordReset'])
                ->name('users.password-reset');
            Route::post('/users/{user}/suspend', [\App\Http\Controllers\Admin\UserManagementController::class, 'suspend'])
                ->name('users.suspend');
            Route::post('/users/{user}/unsuspend', [\App\Http\Controllers\Admin\UserManagementController::class, 'unsuspend'])
                ->name('users.unsuspend');

            // Impersonation Start
            Route::post('/users/{user}/impersonate', [\App\Http\Controllers\Admin\ImpersonationController::class, 'start'])
                ->name('users.impersonate');
        });
    });

// Error pages
Route::get('/404', function () {
    return Inertia::render('Errors/404');
})->name('errors.404');

Route::get('/500', function () {
    return Inertia::render('Errors/500');
})->name('errors.500');

Route::get('/403', function () {
    return Inertia::render('Errors/403');
})->name('errors.403');

Route::get('/419', function () {
    return Inertia::render('Errors/419');
})->name('errors.419');

// Test error routes (remove in production)
Route::get('/test-404', function () {
    abort(404);
})->name('test.404');

Route::get('/test-500', function () {
    abort(500);
})->name('test.500');

Route::get('/test-403', function () {
    abort(403);
})->name('test.403');

require __DIR__.'/auth.php';
