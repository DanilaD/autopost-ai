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
Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

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

require __DIR__.'/auth.php';
