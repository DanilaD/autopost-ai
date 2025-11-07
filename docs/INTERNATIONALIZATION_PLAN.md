# Internationalization (i18n) Implementation Plan

**Version:** 2.1  
**Date:** November 7, 2025  
**Status:** ✅ Complete & Implemented

---

## Overview

Implement multi-language support for Autopost AI starting with 3 languages:

- **English (en)** - Default
- **Russian (ru)**
- **Spanish (es)**

The system will support both backend (Laravel) and frontend (Vue) translations with automatic language detection and user preference persistence.

---

## Table of Contents

1. [Architecture Decisions](#architecture-decisions)
2. [Backend Setup (Laravel)](#backend-setup-laravel)
3. [Frontend Setup (Vue)](#frontend-setup-vue)
4. [Database Schema](#database-schema)
5. [Language Detection](#language-detection)
6. [Translation Files Structure](#translation-files-structure)
7. [Implementation Steps](#implementation-steps)
8. [Best Practices](#best-practices)
9. [Testing](#testing)

---

## Architecture Decisions

### URL Structure: **Path Prefix** (Recommended)

**Selected Approach:** `/{locale}/route`

```
https://autopost.ai/en/dashboard
https://autopost.ai/ru/dashboard
https://autopost.ai/es/dashboard
```

**Rationale:**

- ✅ SEO friendly (separate URLs per language)
- ✅ Clear user experience
- ✅ Easy to share localized links
- ✅ Standard approach for SaaS applications

**Alternatives Considered:**

- ❌ Subdomain (`en.autopost.ai`) - More complex DNS setup
- ❌ Query parameter (`?lang=en`) - Poor SEO, not user-friendly
- ❌ Browser detection only - Can't share specific language links

---

### Translation Storage

**Backend (Laravel):**

- Language files in `lang/{locale}/`
- JSON format for simple key-value pairs
- PHP arrays for complex structures

**Frontend (Vue):**

- Use `laravel-vue-i18n` package (bridges Laravel and Vue)
- Shares Laravel's language files with Vue components
- No duplicate translation files needed

---

## Backend Setup (Laravel)

### 1. Install Package

```bash
composer require laravel-lang/common
```

This package provides translations for Laravel's built-in validation messages, pagination, etc.

### 2. Configuration

**File: `config/app.php`**

```php
<?php

return [
    // Default language
    'locale' => 'en',

    // Fallback language (if translation missing)
    'fallback_locale' => 'en',

    // Available locales
    'available_locales' => [
        'en' => ['name' => 'English', 'flag' => '🇺🇸', 'dir' => 'ltr'],
        'ru' => ['name' => 'Русский', 'flag' => '🇷🇺', 'dir' => 'ltr'],
        'es' => ['name' => 'Español', 'flag' => '🇪🇸', 'dir' => 'ltr'],
    ],
];
```

### 3. Middleware for Locale Detection

**File: `app/Http/Middleware/SetLocale.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set Application Locale Middleware
 *
 * Priority order:
 * 1. URL prefix (/ru/dashboard)
 * 2. User preference (authenticated user's setting)
 * 3. Session (last selected)
 * 4. Browser Accept-Language header
 * 5. Default (en)
 */
class SetLocale
{
    /**
     * Available locales
     */
    private array $availableLocales = ['en', 'ru', 'es'];

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->determineLocale($request);

        // Set application locale
        App::setLocale($locale);

        // Store in session for next request
        Session::put('locale', $locale);

        // Share with views
        view()->share('currentLocale', $locale);
        view()->share('availableLocales', config('app.available_locales'));

        return $next($request);
    }

    /**
     * Determine the locale for this request
     */
    private function determineLocale(Request $request): string
    {
        // 1. URL prefix (from route parameter)
        $routeLocale = $request->route('locale');
        if ($routeLocale && $this->isValidLocale($routeLocale)) {
            return $routeLocale;
        }

        // 2. Authenticated user's preference
        if ($request->user() && $request->user()->locale) {
            if ($this->isValidLocale($request->user()->locale)) {
                return $request->user()->locale;
            }
        }

        // 3. Session (user previously selected)
        $sessionLocale = Session::get('locale');
        if ($sessionLocale && $this->isValidLocale($sessionLocale)) {
            return $sessionLocale;
        }

        // 4. Browser Accept-Language header
        $browserLocale = $this->getBrowserLocale($request);
        if ($browserLocale) {
            return $browserLocale;
        }

        // 5. Default
        return config('app.fallback_locale', 'en');
    }

    /**
     * Get locale from browser Accept-Language header
     */
    private function getBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');

        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header
        // Example: "en-US,en;q=0.9,ru;q=0.8"
        preg_match_all('/([a-z]{2})(-[A-Z]{2})?/', $acceptLanguage, $matches);

        foreach ($matches[1] as $lang) {
            if ($this->isValidLocale($lang)) {
                return $lang;
            }
        }

        return null;
    }

    /**
     * Check if locale is valid
     */
    private function isValidLocale(string $locale): bool
    {
        return in_array($locale, $this->availableLocales);
    }
}
```

### 4. Register Middleware

**File: `bootstrap/app.php`**

```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
        ]);
    })
    ->create();
```

### 5. Update Routes for Locale Prefix

**File: `routes/web.php`**

```php
<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Redirect root to default locale
Route::get('/', function () {
    $locale = app()->getLocale();
    return redirect("/{$locale}");
});

// Localized routes
Route::prefix('{locale}')
    ->where(['locale' => 'en|ru|es'])
    ->group(function () {

        // Landing page
        Route::get('/', function () {
            if (Auth::check()) {
                return redirect()->route('dashboard', ['locale' => app()->getLocale()]);
            }

            return Inertia::render('Auth/EmailEntry');
        })->name('home');

        // Dashboard (requires auth + verified email)
        Route::get('/dashboard', function () {
            return Inertia::render('Dashboard');
        })->middleware(['auth', 'verified'])->name('dashboard');

        // Auth routes (will be in auth.php)
        require __DIR__.'/auth.php';
    });
```

**File: `routes/auth.php`**

```php
<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

// All auth routes are automatically prefixed with /{locale}
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    // ... other auth routes
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
```

---

## Frontend Setup (Vue)

### 1. Install Package

```bash
npm install laravel-vue-i18n
```

This package allows Vue components to use Laravel's language files directly.

### 2. Configure Vue App

**File: `resources/js/app.js`**

```javascript
import '../css/app.css'
import './bootstrap'

import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { createApp, h } from 'vue'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import { i18nVue } from 'laravel-vue-i18n'

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue')
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(i18nVue, {
                resolve: async (lang) => {
                    const langs = import.meta.glob('../../lang/*.json')
                    return await langs[`../../lang/${lang}.json`]()
                },
            })
            .mount(el)
    },
    progress: {
        color: '#4B5563',
    },
})
```

### 3. Configure Vite

**File: `vite.config.js`**

```javascript
import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import vue from '@vitejs/plugin-vue'
import i18n from 'laravel-vue-i18n/vite'

export default defineConfig({
    plugins: [
        laravel({
            input: 'resources/js/app.js',
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        i18n(), // Laravel Vue i18n plugin
    ],
})
```

### 4. Using Translations in Vue Components

**Example: `resources/js/Pages/Auth/EmailEntry.vue`**

```vue
<script setup>
import { trans, wTrans } from 'laravel-vue-i18n'
import { usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

// Get current locale from Inertia shared props
const page = usePage()
const currentLocale = computed(() => page.props.locale || 'en')

// Translation helper (can also use $t() in template)
const t = trans
</script>

<template>
    <GuestLayout>
        <Head :title="$t('auth.login')" />

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                {{ $t('welcome.title') }}
            </h1>
            <p class="mt-2 text-gray-600">
                {{ $t('welcome.subtitle') }}
            </p>
        </div>

        <!-- Language Switcher -->
        <LanguageSwitcher :current="currentLocale" />

        <!-- Email Input -->
        <form @submit.prevent="checkEmail">
            <div>
                <label class="block text-sm font-medium text-gray-700">
                    {{ $t('auth.email') }}
                </label>
                <TextInput
                    v-model="email"
                    type="email"
                    :placeholder="$t('auth.email_placeholder')"
                    required
                />
            </div>

            <PrimaryButton class="w-full mt-4">
                {{ $t('auth.continue') }}
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
```

### 5. Language Switcher Component

**File: `resources/js/Components/LanguageSwitcher.vue`**

```vue
<script setup>
import { router } from '@inertiajs/vue3'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { GlobeAltIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
    current: {
        type: String,
        default: 'en',
    },
})

const languages = {
    en: { name: 'English', flag: '🇺🇸' },
    ru: { name: 'Русский', flag: '🇷🇺' },
    es: { name: 'Español', flag: '🇪🇸' },
}

/**
 * Switch language
 * Preserves current path but changes locale prefix
 */
const switchLanguage = (locale) => {
    const currentPath = window.location.pathname
    const pathWithoutLocale = currentPath.replace(/^\/(en|ru|es)/, '')
    const newPath = `/${locale}${pathWithoutLocale || '/'}`

    router.visit(newPath)
}
</script>

<template>
    <Menu as="div" class="relative inline-block text-left">
        <MenuButton
            class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        >
            <GlobeAltIcon class="w-5 h-5" />
            <span>{{ languages[current].flag }}</span>
            <span>{{ languages[current].name }}</span>
        </MenuButton>

        <transition
            enter-active-class="transition duration-100 ease-out"
            enter-from-class="transform scale-95 opacity-0"
            enter-to-class="transform scale-100 opacity-100"
            leave-active-class="transition duration-75 ease-in"
            leave-from-class="transform scale-100 opacity-100"
            leave-to-class="transform scale-95 opacity-0"
        >
            <MenuItems
                class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
            >
                <div class="py-1">
                    <MenuItem
                        v-for="(lang, code) in languages"
                        :key="code"
                        v-slot="{ active }"
                    >
                        <button
                            @click="switchLanguage(code)"
                            :class="[
                                active ? 'bg-gray-100' : '',
                                code === current
                                    ? 'bg-indigo-50 text-indigo-700'
                                    : 'text-gray-900',
                                'group flex w-full items-center gap-3 px-4 py-2 text-sm',
                            ]"
                        >
                            <span class="text-xl">{{ lang.flag }}</span>
                            <span>{{ lang.name }}</span>
                            <span
                                v-if="code === current"
                                class="ml-auto text-indigo-600"
                                >✓</span
                            >
                        </button>
                    </MenuItem>
                </div>
            </MenuItems>
        </transition>
    </Menu>
</template>
```

---

## Database Schema

### Update `users` Table

Add locale column to store user's preferred language.

**Migration: `database/migrations/YYYY_MM_DD_add_locale_to_users_table.php`**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('locale', 5)->default('en')->after('timezone');
            $table->index('locale');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
```

**Update Model: `app/Models/User.php`**

```php
protected $fillable = [
    'name',
    'email',
    'password',
    'timezone',
    'locale', // Add this
];
```

---

## Language Detection

### Priority Order

1. **URL Prefix** - `/ru/dashboard` → Russian
2. **User Preference** - Authenticated user's `locale` column
3. **Session** - Last selected language in session
4. **Browser** - `Accept-Language` HTTP header
5. **Default** - English (en)

### User Preference Controller

**File: `app/Http/Controllers/LocaleController.php`**

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Locale Controller
 *
 * Handles language switching and preference saving.
 */
class LocaleController extends Controller
{
    /**
     * Available locales
     */
    private array $availableLocales = ['en', 'ru', 'es'];

    /**
     * Switch locale
     *
     * POST /{locale}/locale/switch
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function switch(Request $request): RedirectResponse
    {
        $request->validate([
            'locale' => 'required|string|in:en,ru,es',
        ]);

        $locale = $request->locale;

        // Save to user preferences if authenticated
        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        // Save to session
        session(['locale' => $locale]);

        // Redirect to same page with new locale
        $currentPath = $request->header('referer');
        $pathWithoutLocale = preg_replace('/\/(en|ru|es)/', '', parse_url($currentPath, PHP_URL_PATH));

        return redirect("/{$locale}{$pathWithoutLocale}");
    }
}
```

**Add Route: `routes/web.php`**

```php
Route::prefix('{locale}')
    ->where(['locale' => 'en|ru|es'])
    ->group(function () {
        // Locale switching
        Route::post('/locale/switch', [LocaleController::class, 'switch'])
            ->name('locale.switch');

        // ... other routes
    });
```

---

## Translation Files Structure

### Directory Structure

```
lang/
├── en.json          # English translations (key-value)
├── ru.json          # Russian translations
├── es.json          # Spanish translations
├── en/
│   ├── auth.php     # Authentication messages
│   ├── validation.php
│   └── passwords.php
├── ru/
│   ├── auth.php
│   ├── validation.php
│   └── passwords.php
└── es/
    ├── auth.php
    ├── validation.php
    └── passwords.php
```

### JSON Translation Files (Simple Key-Value)

**File: `lang/en.json`**

```json
{
    "Welcome to Autopost AI": "Welcome to Autopost AI",
    "AI-powered Instagram content, made simple": "AI-powered Instagram content, made simple",
    "Email address": "Email address",
    "Continue with Email": "Continue with Email",
    "We'll email you a magic link for a password-free sign in.": "We'll email you a magic link for a password-free sign in.",
    "Welcome back, :name!": "Welcome back, :name!",
    "Email me a login link": "Email me a login link",
    "Sign in with password": "Sign in with password",
    "Forgot password?": "Forgot password?",
    "Use a different email": "Use a different email",
    "Create Account": "Create Account",
    "Full Name": "Full Name",
    "Password": "Password",
    "Confirm Password": "Confirm Password",
    "Already have an account?": "Already have an account?",
    "Sign in": "Sign in",
    "Dashboard": "Dashboard",
    "Posts": "Posts",
    "Calendar": "Calendar",
    "Wallet": "Wallet",
    "Settings": "Settings",
    "Log out": "Log out"
}
```

**File: `lang/ru.json`**

```json
{
    "Welcome to Autopost AI": "Добро пожаловать в Autopost AI",
    "AI-powered Instagram content, made simple": "Instagram контент на основе ИИ — просто и быстро",
    "Email address": "Адрес электронной почты",
    "Continue with Email": "Продолжить с Email",
    "We'll email you a magic link for a password-free sign in.": "Мы отправим вам волшебную ссылку для входа без пароля.",
    "Welcome back, :name!": "С возвращением, :name!",
    "Email me a login link": "Отправить ссылку для входа",
    "Sign in with password": "Войти с паролем",
    "Forgot password?": "Забыли пароль?",
    "Use a different email": "Использовать другой email",
    "Create Account": "Создать аккаунт",
    "Full Name": "Полное имя",
    "Password": "Пароль",
    "Confirm Password": "Подтвердите пароль",
    "Already have an account?": "Уже есть аккаунт?",
    "Sign in": "Войти",
    "Dashboard": "Панель управления",
    "Posts": "Посты",
    "Calendar": "Календарь",
    "Wallet": "Кошелек",
    "Settings": "Настройки",
    "Log out": "Выйти"
}
```

**File: `lang/es.json`**

```json
{
    "Welcome to Autopost AI": "Bienvenido a Autopost AI",
    "AI-powered Instagram content, made simple": "Contenido de Instagram con IA, simplificado",
    "Email address": "Dirección de correo electrónico",
    "Continue with Email": "Continuar con Email",
    "We'll email you a magic link for a password-free sign in.": "Te enviaremos un enlace mágico para iniciar sesión sin contraseña.",
    "Welcome back, :name!": "¡Bienvenido de nuevo, :name!",
    "Email me a login link": "Enviarme enlace de inicio de sesión",
    "Sign in with password": "Iniciar sesión con contraseña",
    "Forgot password?": "¿Olvidaste tu contraseña?",
    "Use a different email": "Usar otro email",
    "Create Account": "Crear cuenta",
    "Full Name": "Nombre completo",
    "Password": "Contraseña",
    "Confirm Password": "Confirmar contraseña",
    "Already have an account?": "¿Ya tienes una cuenta?",
    "Sign in": "Iniciar sesión",
    "Dashboard": "Panel de control",
    "Posts": "Publicaciones",
    "Calendar": "Calendario",
    "Wallet": "Billetera",
    "Settings": "Configuración",
    "Log out": "Cerrar sesión"
}
```

### PHP Translation Files (Complex Structures)

**File: `lang/en/auth.php`**

```php
<?php

return [
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'login' => 'Log in',
    'register' => 'Register',
    'logout' => 'Log out',
    'email' => 'Email',
    'password' => 'Password',
    'remember_me' => 'Remember me',
    'forgot_password' => 'Forgot your password?',
    'reset_password' => 'Reset Password',
    'send_reset_link' => 'Send Password Reset Link',
    'verify_email' => 'Verify Email Address',
];
```

**File: `lang/ru/auth.php`**

```php
<?php

return [
    'failed' => 'Неверные учетные данные.',
    'password' => 'Неверный пароль.',
    'throttle' => 'Слишком много попыток входа. Попробуйте через :seconds секунд.',

    'login' => 'Войти',
    'register' => 'Регистрация',
    'logout' => 'Выйти',
    'email' => 'Email',
    'password' => 'Пароль',
    'remember_me' => 'Запомнить меня',
    'forgot_password' => 'Забыли пароль?',
    'reset_password' => 'Сброс пароля',
    'send_reset_link' => 'Отправить ссылку для сброса',
    'verify_email' => 'Подтвердите Email',
];
```

**File: `lang/es/auth.php`**

```php
<?php

return [
    'failed' => 'Estas credenciales no coinciden con nuestros registros.',
    'password' => 'La contraseña proporcionada es incorrecta.',
    'throttle' => 'Demasiados intentos de inicio de sesión. Inténtalo de nuevo en :seconds segundos.',

    'login' => 'Iniciar sesión',
    'register' => 'Registrarse',
    'logout' => 'Cerrar sesión',
    'email' => 'Correo electrónico',
    'password' => 'Contraseña',
    'remember_me' => 'Recuérdame',
    'forgot_password' => '¿Olvidaste tu contraseña?',
    'reset_password' => 'Restablecer contraseña',
    'send_reset_link' => 'Enviar enlace de restablecimiento',
    'verify_email' => 'Verificar dirección de correo',
];
```

---

## Implementation Steps

### Step 1: Install Packages

```bash
# Backend
composer require laravel-lang/common

# Frontend
npm install laravel-vue-i18n
npm install @headlessui/vue @heroicons/vue
```

### Step 2: Create Translation Files

```bash
# Create language directories
mkdir -p lang/en lang/ru lang/es

# Create JSON translation files
touch lang/en.json lang/ru.json lang/es.json

# Copy validation and auth translations
php artisan lang:add ru
php artisan lang:add es
```

### Step 3: Database Migration

```bash
php artisan make:migration add_locale_to_users_table
# Edit migration file
php artisan migrate
```

### Step 4: Create Middleware

```bash
php artisan make:middleware SetLocale
# Edit middleware file (see code above)
```

### Step 5: Create Controllers

```bash
php artisan make:controller LocaleController
# Edit controller file (see code above)
```

### Step 6: Update Configuration

- Edit `config/app.php` (add available_locales)
- Edit `vite.config.js` (add i18n plugin)
- Edit `resources/js/app.js` (add i18nVue)

### Step 7: Update Routes

- Add locale prefix to all routes
- Add locale switching route
- Update route definitions

### Step 8: Create Vue Components

- Create `LanguageSwitcher.vue`
- Update existing pages with translations
- Test language switching

### Step 9: Share Locale with Inertia

**File: `app/Http/Middleware/HandleInertiaRequests.php`**

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'locale' => app()->getLocale(),
        'locales' => config('app.available_locales'),
        'translations' => function () {
            $locale = app()->getLocale();
            $jsonFile = lang_path("{$locale}.json");

            return file_exists($jsonFile)
                ? json_decode(file_get_contents($jsonFile), true)
                : [];
        },
    ];
}
```

---

## Best Practices

### 1. Translation Keys

**Use descriptive keys:**

```vue
<!-- ❌ Bad -->
{{ $t('w1') }}

<!-- ✅ Good -->
{{ $t('welcome.title') }}
{{ $t('auth.email_placeholder') }}
```

### 2. Parameterized Translations

**English:**

```json
{
    "Welcome back, :name!": "Welcome back, :name!"
}
```

**Usage:**

```vue
{{ $t('Welcome back, :name!', { name: userName }) }}
```

### 3. Pluralization

**English (lang/en/messages.php):**

```php
'posts_count' => '{0} No posts|{1} 1 post|[2,*] :count posts',
```

**Usage:**

```vue
{{ $t('messages.posts_count', postsCount) }}
```

### 4. Date & Number Formatting

Use browser's Intl API:

```javascript
// In your component
const formatDate = (date) => {
    const locale = page.props.locale
    return new Intl.DateTimeFormat(locale, {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    }).format(new Date(date))
}

const formatCurrency = (amount) => {
    const locale = page.props.locale
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: 'USD',
    }).format(amount / 100)
}
```

### 5. RTL Languages (Future)

If adding Arabic or Hebrew later:

**CSS:**

```css
[dir='rtl'] {
    direction: rtl;
    text-align: right;
}

[dir='rtl'] .ml-4 {
    margin-left: 0;
    margin-right: 1rem;
}
```

---

## Testing

### Manual Testing Checklist

- [ ] Landing page shows in correct language based on URL
- [ ] Language switcher changes URL and content
- [ ] User's language preference persists after login
- [ ] Browser language detection works for new visitors
- [ ] All form validation messages translated
- [ ] Email templates translated (password reset, verification)
- [ ] Date and number formatting matches locale
- [ ] Navigation menu items translated
- [ ] Error messages translated

### Automated Tests

**Feature Test: `tests/Feature/LocaleTest.php`**

```php
<?php

use App\Models\User;

it('redirects root to default locale', function () {
    $response = $this->get('/');

    $response->assertRedirect('/en');
});

it('sets locale based on URL prefix', function () {
    $response = $this->get('/ru/dashboard');

    expect(app()->getLocale())->toBe('ru');
});

it('saves user locale preference', function () {
    $user = User::factory()->create(['locale' => 'en']);

    $this->actingAs($user)
        ->post('/en/locale/switch', ['locale' => 'ru']);

    expect($user->fresh()->locale)->toBe('ru');
});

it('detects browser language', function () {
    $response = $this->withHeaders([
        'Accept-Language' => 'es-ES,es;q=0.9,en;q=0.8'
    ])->get('/');

    $response->assertRedirect('/es');
});
```

---

## SEO Considerations

### Hreflang Tags

Add to `<head>` in `resources/views/app.blade.php`:

```blade
<link rel="alternate" hreflang="en" href="{{ config('app.url') }}/en{{ request()->path() }}" />
<link rel="alternate" hreflang="ru" href="{{ config('app.url') }}/ru{{ request()->path() }}" />
<link rel="alternate" hreflang="es" href="{{ config('app.url') }}/es{{ request()->path() }}" />
<link rel="alternate" hreflang="x-default" href="{{ config('app.url') }}/en{{ request()->path() }}" />
```

### Language-Specific Sitemaps

Generate separate sitemaps for each language:

- `sitemap-en.xml`
- `sitemap-ru.xml`
- `sitemap-es.xml`

---

## Recent Updates (November 7, 2025)

### Fixes and Improvements

✅ **Fixed CSRF token issue** - Language selector now uses Inertia router for automatic CSRF handling  
✅ **Immediate language updates** - Language changes now reflect immediately without manual page reload  
✅ **Fixed duplicate auth translations** - Merged duplicate auth sections in messages object  
✅ **Added missing translations** - Added all missing auth translations (enter_email, continue, forgot_password_title, etc.)  
✅ **Fixed password reset flow** - Auto-login after password reset with redirect to dashboard  
✅ **Improved ResetPassword page** - Matches Login page design with glass card styling  
✅ **Event listeners** - Added Inertia event listeners to sync locale on all page navigations

### Technical Changes

- **LanguageSelector.vue**: Now uses `router.post()` with immediate i18n locale update
- **app.js**: Added `inertia:success` event listener to sync locale on page updates
- **NewPasswordController**: Auto-login after password reset, redirect to dashboard
- **ResetPassword.vue**: Updated to match Login/ForgotPassword page styling
- **Translation files**: Added missing auth translations to JSON files (en.json, ru.json, es.json)

---

## Summary

### What This Achieves

✅ **Multi-language support** for 3 languages (English, Russian, Spanish)  
✅ **Smart detection** - URL, user preference, session, browser  
✅ **SEO friendly** - Path prefix approach  
✅ **User preferences** - Saved in database  
✅ **Shared translations** - Laravel and Vue use same files  
✅ **Beautiful language switcher** - With flags and names  
✅ **Fallback system** - Missing translations fall back to English  
✅ **Easy to extend** - Add new languages by creating new files  
✅ **Immediate updates** - Language changes reflect instantly without page reload  
✅ **CSRF protection** - Automatic CSRF token handling via Inertia router

### Files to Create/Modify

**New Files (15):**

- Middleware: `SetLocale.php`
- Controller: `LocaleController.php`
- Migration: `add_locale_to_users_table.php`
- Component: `LanguageSwitcher.vue`
- Translation files: `en.json`, `ru.json`, `es.json`
- Translation files: `en/auth.php`, `ru/auth.php`, `es/auth.php`
- Test: `LocaleTest.php`

**Modified Files (7):**

- `config/app.php`
- `vite.config.js`
- `resources/js/app.js`
- `routes/web.php`
- `routes/auth.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Models/User.php`

---

## Next Steps

1. Install packages
2. Create translation files for all 3 languages
3. Implement middleware and routes
4. Create language switcher component
5. Update all existing pages with translations
6. Test thoroughly
7. Add to Phase 0 implementation

---

**Document Status:** Ready for implementation  
**Estimated Time:** 8-12 hours  
**Dependencies:** None (can be implemented independently)
