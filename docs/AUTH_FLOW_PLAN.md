# Authentication Flow Implementation Plan

**Version:** 1.0  
**Date:** October 9, 2025  
**Status:** Ready for Implementation

---

## Overview

Implement a modern, progressive authentication flow inspired by Slack, Notion, and Linear. The system starts with email-only entry and intelligently routes users based on their existence in the database.

### User Experience Goals

- **Frictionless:** Single email field as entry point
- **Smart:** Automatically detect existing vs. new users
- **Flexible:** Support both password and passwordless (magic link) authentication
- **Secure:** Log suspicious attempts, verify emails, track IPs
- **Modern:** Beautiful UI with smooth transitions

---

## User Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    Landing Page (/)                          │
│                                                               │
│  "Welcome to Autopost AI"                                    │
│  ┌───────────────────────────────────────────┐              │
│  │  Email: [____________________________]    │              │
│  └───────────────────────────────────────────┘              │
│                [Continue →]                                  │
└─────────────────────────────────────────────────────────────┘
                          ↓
                    [Check Email]
                          ↓
        ┌─────────────────┴─────────────────┐
        ↓                                     ↓
┌──────────────────┐              ┌──────────────────┐
│  User EXISTS     │              │  User NOT EXISTS │
│                  │              │                  │
│  → Login Flow    │              │  → Sign Up Flow  │
└──────────────────┘              └──────────────────┘
        ↓                                     ↓
┌──────────────────┐              ┌──────────────────┐
│  "Welcome back,  │              │  "Create Account"│
│   John!"         │              │                  │
│                  │              │  Name: [______]  │
│  Choose method:  │              │  Email: john@... │
│                  │              │  (verified)      │
│  [Enter Password]│              │                  │
│       OR         │              │  Password:       │
│  [Email me link] │              │  [____________]  │
│                  │              │                  │
│  [Forgot pass?]  │              │  [Create Acc →]  │
└──────────────────┘              └──────────────────┘
        ↓                                     ↓
        ↓                         ┌──────────────────┐
        ↓                         │  Verify Email    │
        ↓                         │                  │
        ↓                         │  "Check inbox"   │
        ↓                         │  Resend link     │
        ↓                         └──────────────────┘
        ↓                                     ↓
        └─────────────────┬─────────────────┘
                          ↓
                  ┌──────────────────┐
                  │    Dashboard     │
                  └──────────────────┘
```

---

## Implementation Steps

### Step 1: Database Migration

**Create `inquiries` table:**

```php
// database/migrations/YYYY_MM_DD_create_inquiries_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index();
            $table->string('ip_address', 45)->nullable()->index(); // IPv6 compatible
            $table->text('user_agent')->nullable();
            $table->text('referrer')->nullable();
            $table->json('metadata')->nullable(); // utm params, etc.
            $table->timestamp('created_at')->useCurrent();

            // Composite index for analytics
            $table->index(['email', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
```

**Modify `users` table (if needed):**

```php
// Check if users table has email_verified_at
// Laravel Breeze already includes this, but verify:

Schema::table('users', function (Blueprint $table) {
    if (!Schema::hasColumn('users', 'email_verified_at')) {
        $table->timestamp('email_verified_at')->nullable()->after('email');
    }

    // Add last_login_at for analytics
    $table->timestamp('last_login_at')->nullable()->after('email_verified_at');
    $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
});
```

---

### Step 2: Install Magic Link Package

**Option A: Use Package (Recommended)**

```bash
composer require grosv/laravel-passwordless-login
php artisan vendor:publish --provider="Grosv\LaravelPasswordlessLogin\ServiceProvider"
```

**Configuration (`config/laravel-passwordless-login.php`):**

```php
return [
    'login_route' => 'auth.magic-link.login',
    'redirect_route' => 'dashboard',
    'link_expires_seconds' => 3600, // 1 hour
    'throttle' => [
        'max_attempts' => 3,
        'decay_minutes' => 2,
    ],
];
```

**Option B: Custom Implementation (if package doesn't fit)**

Create custom signed URL magic link system (detailed in Step 6).

---

### Step 3: Create Inquiry Model & Service

**Model: `app/Models/Inquiry.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Inquiry Model
 *
 * Tracks email submission attempts from users who don't exist in the system.
 * Used for marketing intelligence and security monitoring.
 */
class Inquiry extends Model
{
    /**
     * No updated_at column
     */
    public const UPDATED_AT = null;

    /**
     * Mass assignable attributes
     */
    protected $fillable = [
        'email',
        'ip_address',
        'user_agent',
        'referrer',
        'metadata',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    /**
     * Scope: Recent inquiries (last 24 hours)
     */
    public function scopeRecent($query)
    {
        return $query->where('created_at', '>=', now()->subDay());
    }

    /**
     * Scope: By email
     */
    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }
}
```

**Service: `app/Services/Auth/InquiryService.php`**

```php
<?php

namespace App\Services\Auth;

use App\Models\Inquiry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Inquiry Service
 *
 * Handles logging of email attempts from unknown users.
 */
class InquiryService
{
    /**
     * Log email inquiry if user doesn't exist
     *
     * @param string $email
     * @param Request $request
     * @return Inquiry|null Returns Inquiry if logged, null if user exists
     */
    public function logIfNewUser(string $email, Request $request): ?Inquiry
    {
        // Check if user already exists
        if (User::where('email', $email)->exists()) {
            return null;
        }

        // Log the inquiry
        $inquiry = Inquiry::create([
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer' => $request->header('referer'),
            'metadata' => [
                'utm_source' => $request->get('utm_source'),
                'utm_medium' => $request->get('utm_medium'),
                'utm_campaign' => $request->get('utm_campaign'),
                'timestamp' => now()->toIso8601String(),
            ],
        ]);

        Log::info('New inquiry logged', [
            'email' => $email,
            'ip' => $request->ip(),
        ]);

        return $inquiry;
    }

    /**
     * Get inquiry count for email (last 24 hours)
     *
     * Used for rate limiting or spam detection
     */
    public function getRecentAttempts(string $email): int
    {
        return Inquiry::byEmail($email)->recent()->count();
    }
}
```

---

### Step 4: Create Auth Controllers

**Controller: `app/Http/Controllers/Auth/EmailCheckController.php`**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\InquiryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Email Check Controller
 *
 * Handles the initial email submission and determines
 * whether user exists or needs to register.
 */
class EmailCheckController extends Controller
{
    public function __construct(
        private InquiryService $inquiryService
    ) {}

    /**
     * Check if email exists in system
     *
     * POST /auth/check-email
     *
     * @return JsonResponse
     */
    public function check(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'exists' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if ($user) {
            // User exists - return login options
            return response()->json([
                'exists' => true,
                'name' => $user->name,
                'email' => $email,
                'verified' => $user->hasVerifiedEmail(),
            ]);
        }

        // User doesn't exist - log inquiry and return registration flow
        $this->inquiryService->logIfNewUser($email, $request);

        return response()->json([
            'exists' => false,
            'email' => $email,
        ]);
    }
}
```

**Controller: `app/Http/Controllers/Auth/MagicLinkController.php`**

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\MagicLinkMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;

/**
 * Magic Link Authentication Controller
 *
 * Handles passwordless authentication via email links.
 */
class MagicLinkController extends Controller
{
    /**
     * Send magic link to user's email
     *
     * POST /auth/magic-link/send
     */
    public function send(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Rate limiting: 3 attempts per 2 minutes
        $key = 'magic-link:' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'email' => "Too many attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        RateLimiter::hit($key, 120); // 2 minutes decay

        $user = User::where('email', $request->email)->first();

        // Generate signed URL (valid for 1 hour)
        $loginUrl = URL::temporarySignedRoute(
            'auth.magic-link.login',
            now()->addHour(),
            ['user' => $user->id]
        );

        // Send email
        Mail::to($user)->send(new MagicLinkMail($loginUrl, $user));

        return response()->json([
            'message' => 'Magic link sent! Check your email.',
        ]);
    }

    /**
     * Authenticate user via magic link
     *
     * GET /auth/magic-link/login?user={id}&signature={hash}&expires={timestamp}
     */
    public function login(Request $request): RedirectResponse
    {
        // Verify signed URL
        if (!$request->hasValidSignature()) {
            abort(403, 'Invalid or expired magic link.');
        }

        $user = User::findOrFail($request->user);

        // Log the user in
        Auth::login($user);
        $request->session()->regenerate();

        // Update last login
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        return redirect()->intended(route('dashboard'))
            ->with('status', 'Successfully logged in via magic link!');
    }
}
```

---

### Step 5: Create Mail Template

**Mail: `app/Mail/MagicLinkMail.php`**

```php
<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MagicLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $loginUrl,
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Login Link for Autopost AI',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.magic-link',
        );
    }
}
```

**Blade Template: `resources/views/emails/auth/magic-link.blade.php`**

```blade
@component('mail::message')
# Welcome back, {{ $user->name }}!

Click the button below to securely log in to your Autopost AI account.

@component('mail::button', ['url' => $loginUrl])
Log In to Autopost AI
@endcomponent

This link will expire in 1 hour and can only be used once.

If you didn't request this login link, you can safely ignore this email.

Thanks,<br>
{{ config('app.name') }}

---

<small>
Having trouble clicking the button? Copy and paste this URL into your browser:<br>
{{ $loginUrl }}
</small>
@endcomponent
```

---

### Step 6: Update Routes

**File: `routes/auth.php`**

```php
<?php

use App\Http\Controllers\Auth\EmailCheckController;
use App\Http\Controllers\Auth\MagicLinkController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
// ... other auth imports

// Email check endpoint (step 1 of auth flow)
Route::post('/auth/check-email', [EmailCheckController::class, 'check'])
    ->middleware('guest')
    ->name('auth.check-email');

// Magic link routes
Route::post('/auth/magic-link/send', [MagicLinkController::class, 'send'])
    ->middleware(['guest', 'throttle:3,2'])
    ->name('auth.magic-link.send');

Route::get('/auth/magic-link/login', [MagicLinkController::class, 'login'])
    ->middleware('guest')
    ->name('auth.magic-link.login');

// Keep existing Breeze routes but modify login flow
Route::get('/login', [AuthenticatedSessionController::class, 'create'])
    ->middleware('guest')
    ->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest');

// ... rest of auth routes
```

---

### Step 7: Create Vue Components

**Page: `resources/js/Pages/Auth/EmailEntry.vue`**

```vue
<script setup>
import { ref } from 'vue'
import { useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'

const email = ref('')
const loading = ref(false)
const error = ref('')
const showPasswordForm = ref(false)
const showMagicLinkOption = ref(false)
const userName = ref('')
const isRegistration = ref(false)

/**
 * Handle email submission
 * Checks if user exists and routes accordingly
 */
const checkEmail = async () => {
    if (!email.value) return

    loading.value = true
    error.value = ''

    try {
        const response = await axios.post('/auth/check-email', {
            email: email.value,
        })

        if (response.data.exists) {
            // User exists - show login options
            userName.value = response.data.name
            showPasswordForm.value = true
            showMagicLinkOption.value = true
        } else {
            // User doesn't exist - redirect to registration
            isRegistration.value = true
            window.location.href = `/register?email=${encodeURIComponent(email.value)}`
        }
    } catch (err) {
        error.value = err.response?.data?.message || 'Something went wrong'
    } finally {
        loading.value = false
    }
}

/**
 * Handle password login
 */
const loginForm = useForm({
    email: email,
    password: '',
    remember: false,
})

const submitPassword = () => {
    loginForm.post(route('login'), {
        onFinish: () => loginForm.reset('password'),
    })
}

/**
 * Request magic link
 */
const requestMagicLink = async () => {
    loading.value = true

    try {
        await axios.post('/auth/magic-link/send', {
            email: email.value,
        })

        alert('Check your email! We sent you a magic link to log in.')
    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to send magic link'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <GuestLayout>
        <Head title="Welcome" />

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                Welcome to Autopost AI
            </h1>
            <p class="mt-2 text-gray-600">
                AI-powered Instagram content, made simple
            </p>
        </div>

        <!-- Step 1: Email Entry -->
        <div v-if="!showPasswordForm">
            <form @submit.prevent="checkEmail">
                <div>
                    <label
                        for="email"
                        class="block text-sm font-medium text-gray-700"
                    >
                        Email address
                    </label>
                    <TextInput
                        id="email"
                        v-model="email"
                        type="email"
                        class="mt-1 block w-full"
                        placeholder="you@example.com"
                        required
                        autofocus
                    />
                    <InputError :message="error" class="mt-2" />
                </div>

                <PrimaryButton
                    type="submit"
                    class="w-full mt-4"
                    :disabled="loading"
                >
                    {{ loading ? 'Checking...' : 'Continue with Email' }}
                </PrimaryButton>
            </form>

            <p class="mt-4 text-center text-sm text-gray-600">
                We'll email you a magic link for a password-free sign in.
            </p>
        </div>

        <!-- Step 2: Login Options (if user exists) -->
        <div v-else>
            <div class="mb-6 text-center">
                <p class="text-lg text-gray-900">
                    Welcome back, <strong>{{ userName }}</strong
                    >! 👋
                </p>
                <p class="text-sm text-gray-600 mt-1">
                    {{ email }}
                </p>
            </div>

            <!-- Option 1: Magic Link -->
            <div class="space-y-3">
                <button
                    @click="requestMagicLink"
                    class="w-full px-4 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition"
                    :disabled="loading"
                >
                    📧 Email me a login link
                </button>

                <!-- Divider -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">Or</span>
                    </div>
                </div>

                <!-- Option 2: Password -->
                <form @submit.prevent="submitPassword" class="space-y-3">
                    <TextInput
                        id="password"
                        v-model="loginForm.password"
                        type="password"
                        placeholder="Enter your password"
                        class="w-full"
                        required
                    />

                    <InputError :message="loginForm.errors.password" />

                    <PrimaryButton
                        type="submit"
                        class="w-full"
                        :disabled="loginForm.processing"
                    >
                        Sign in with password
                    </PrimaryButton>
                </form>

                <div class="text-center">
                    <a
                        href="/password-reset"
                        class="text-sm text-indigo-600 hover:underline"
                    >
                        Forgot password?
                    </a>
                </div>
            </div>

            <!-- Back button -->
            <button
                @click="showPasswordForm = false"
                class="mt-4 text-sm text-gray-600 hover:text-gray-900"
            >
                ← Use a different email
            </button>
        </div>
    </GuestLayout>
</template>
```

---

### Step 8: Update Landing Page Route

**File: `routes/web.php`**

```php
Route::get('/', function () {
    // Redirect authenticated users to dashboard
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }

    // Show email entry page for guests
    return Inertia::render('Auth/EmailEntry');
})->name('home');
```

---

### Step 9: Modify Registration Controller

**Update: `app/Http/Controllers/Auth/RegisteredUserController.php`**

```php
public function create(): Response
{
    // Get pre-filled email from query string
    $email = request('email');

    return Inertia::render('Auth/Register', [
        'email' => $email,
    ]);
}

public function store(Request $request): RedirectResponse
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);

    // Send email verification
    event(new Registered($user));

    Auth::login($user);

    // Redirect to email verification notice
    return redirect()->route('verification.notice');
}
```

---

### Step 10: Update Registration Page

**File: `resources/js/Pages/Auth/Register.vue`**

```vue
<script setup>
import { useForm } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head } from '@inertiajs/vue3'

const props = defineProps({
    email: {
        type: String,
        default: '',
    },
})

const form = useForm({
    name: '',
    email: props.email,
    password: '',
    password_confirmation: '',
})

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    })
}
</script>

<template>
    <GuestLayout>
        <Head title="Create Account" />

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                Create your account
            </h1>
            <p class="mt-2 text-gray-600">Get started with Autopost AI</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div>
                <InputLabel for="name" value="Full Name" />
                <TextInput
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-1 block w-full"
                    required
                    autofocus
                    autocomplete="name"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />
                <TextInput
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-full"
                    required
                    autocomplete="username"
                    :disabled="!!email"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <InputLabel for="password" value="Password" />
                <TextInput
                    id="password"
                    v-model="form.password"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div>
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />
                <TextInput
                    id="password_confirmation"
                    v-model="form.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    required
                    autocomplete="new-password"
                />
                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <PrimaryButton
                class="w-full mt-6"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                Create Account
            </PrimaryButton>
        </form>

        <p class="mt-4 text-center text-sm text-gray-600">
            Already have an account?
            <a href="/" class="text-indigo-600 hover:underline">Sign in</a>
        </p>
    </GuestLayout>
</template>
```

---

## Testing Checklist

### Manual Testing

- [ ] Enter email → User exists → Shows "Welcome back" with options
- [ ] Enter email → User doesn't exist → Redirects to registration
- [ ] Click "Email me login link" → Receives email with magic link
- [ ] Click magic link → Successfully logs in
- [ ] Magic link expires after 1 hour
- [ ] Enter password → Successfully logs in
- [ ] Click "Forgot password" → Password reset flow works
- [ ] Register new user → Email verification sent
- [ ] Verify email → Can access dashboard
- [ ] Check `inquiries` table → Logs non-existent emails with IP
- [ ] Check `inquiries` table → Does NOT log existing user emails
- [ ] Rate limiting → 3 magic link requests per 2 minutes enforced

### Automated Tests

**Feature Test: `tests/Feature/Auth/EmailCheckTest.php`**

```php
<?php

use App\Models\Inquiry;
use App\Models\User;

it('returns exists true for existing user', function () {
    $user = User::factory()->create(['email' => 'john@example.com']);

    $response = $this->postJson('/auth/check-email', [
        'email' => 'john@example.com'
    ]);

    $response->assertOk()
        ->assertJson([
            'exists' => true,
            'name' => $user->name,
        ]);
});

it('returns exists false for new user', function () {
    $response = $this->postJson('/auth/check-email', [
        'email' => 'newuser@example.com'
    ]);

    $response->assertOk()
        ->assertJson([
            'exists' => false,
        ]);
});

it('logs inquiry for non-existent user', function () {
    expect(Inquiry::count())->toBe(0);

    $this->postJson('/auth/check-email', [
        'email' => 'stranger@example.com'
    ]);

    expect(Inquiry::count())->toBe(1);
    expect(Inquiry::first()->email)->toBe('stranger@example.com');
});

it('does not log inquiry for existing user', function () {
    User::factory()->create(['email' => 'john@example.com']);

    $this->postJson('/auth/check-email', [
        'email' => 'john@example.com'
    ]);

    expect(Inquiry::count())->toBe(0);
});
```

---

## Security Considerations

### Rate Limiting

```php
// In routes/auth.php
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/auth/check-email', [EmailCheckController::class, 'check']);
});

Route::middleware('throttle:3,2')->group(function () {
    Route::post('/auth/magic-link/send', [MagicLinkController::class, 'send']);
});
```

### IP Logging Privacy

Add to privacy policy:

> "We log IP addresses for security purposes when you attempt to access the platform. This data is retained for 90 days."

**Cleanup Job: `app/Console/Commands/CleanupOldInquiries.php`**

```php
<?php

namespace App\Console\Commands;

use App\Models\Inquiry;
use Illuminate\Console\Command;

class CleanupOldInquiries extends Command
{
    protected $signature = 'inquiries:cleanup';
    protected $description = 'Delete inquiries older than 90 days';

    public function handle()
    {
        $deleted = Inquiry::where('created_at', '<', now()->subDays(90))->delete();

        $this->info("Deleted {$deleted} old inquiries.");
    }
}
```

**Schedule in `app/Console/Kernel.php`:**

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('inquiries:cleanup')->weekly();
}
```

---

## Analytics Dashboard (Optional)

**Query Example: Top Inquiry Emails (Marketing Intelligence)**

```php
// In an admin dashboard
$topInquiries = Inquiry::select('email', DB::raw('count(*) as attempts'))
    ->where('created_at', '>=', now()->subDays(30))
    ->groupBy('email')
    ->orderByDesc('attempts')
    ->limit(50)
    ->get();
```

---

## Summary

### What This Achieves

✅ **Modern UX** - Single email entry point like Slack/Notion  
✅ **Passwordless Option** - Magic link authentication  
✅ **Smart Routing** - Detects existing vs. new users  
✅ **Email Verification** - Enforced on registration  
✅ **Security Logging** - Track inquiries with IP addresses  
✅ **Rate Limiting** - Prevent abuse  
✅ **Marketing Intelligence** - Know who's interested but hasn't signed up

### Files to Create/Modify

**New Files (17):**

- Migration: `create_inquiries_table.php`
- Migration: `add_last_login_to_users_table.php`
- Model: `Inquiry.php`
- Service: `InquiryService.php`
- Controller: `EmailCheckController.php`
- Controller: `MagicLinkController.php`
- Mail: `MagicLinkMail.php`
- View: `emails/auth/magic-link.blade.php`
- Vue Page: `Auth/EmailEntry.vue`
- Test: `EmailCheckTest.php`
- Test: `MagicLinkTest.php`
- Command: `CleanupOldInquiries.php`

**Modified Files (4):**

- `routes/web.php`
- `routes/auth.php`
- `RegisteredUserController.php`
- `Auth/Register.vue`

---

## Next Steps

1. Review this plan
2. Confirm package choice (use package or custom implementation)
3. Begin implementation in order of steps
4. Test thoroughly
5. Deploy to staging

---

**Document Status:** Ready for implementation  
**Estimated Time:** 6-8 hours for experienced developer  
**Dependencies:** grosv/laravel-passwordless-login (optional)
