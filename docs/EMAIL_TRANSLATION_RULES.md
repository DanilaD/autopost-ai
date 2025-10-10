# 📧 Email Translation Rules

**Version:** 1.0  
**Date:** October 10, 2025  
**Status:** ⚠️ MANDATORY FOR ALL EMAILS

---

## ⚠️ CRITICAL RULE: All Emails Must Be Translated

**Every email sent by the application MUST respect the user's language preference.**

No exceptions. No hardcoded English text. Every word must use Laravel's localization system.

---

## 📁 Translation File Structure

### Directory Layout

```
lang/
├── en/
│   ├── emails.php          # General email translations
│   ├── auth_emails.php     # Auth-related emails
│   ├── notifications.php   # Notification emails
│   └── instagram.php       # Instagram-specific emails
├── ru/
│   ├── emails.php
│   ├── auth_emails.php
│   ├── notifications.php
│   └── instagram.php
└── es/
    ├── emails.php
    ├── auth_emails.php
    ├── notifications.php
    └── instagram.php
```

---

## 📝 Translation File Examples

### `lang/en/emails.php`

```php
<?php

return [
    // Common
    'greeting' => 'Hello, :name!',
    'closing' => 'Best regards,',
    'team' => 'The Autopost AI Team',
    'footer' => 'If you did not request this action, please ignore this email.',
    'copyright' => '© :year Autopost AI. All rights reserved.',

    // Buttons
    'button' => [
        'action' => 'Click here',
        'verify' => 'Verify Email',
        'reset_password' => 'Reset Password',
        'view' => 'View Details',
        'login' => 'Login Now',
        'get_started' => 'Get Started',
    ],
];
```

### `lang/en/auth_emails.php`

```php
<?php

return [
    'verify_email' => [
        'subject' => 'Verify Your Email Address',
        'intro' => 'Thank you for registering with Autopost AI!',
        'body' => 'Please click the button below to verify your email address.',
        'expire' => 'This verification link will expire in :minutes minutes.',
        'outro' => 'Once verified, you can start connecting your Instagram accounts.',
    ],

    'reset_password' => [
        'subject' => 'Reset Your Password',
        'intro' => 'You are receiving this email because we received a password reset request.',
        'body' => 'Click the button below to reset your password.',
        'expire' => 'This password reset link will expire in :minutes minutes.',
        'no_action' => 'If you did not request a password reset, no further action is required.',
    ],

    'magic_link' => [
        'subject' => 'Your Login Link',
        'intro' => 'Click the button below to login to your account.',
        'body' => 'For your security, this link can only be used once.',
        'expire' => 'This link will expire in :minutes minutes.',
    ],

    'welcome' => [
        'subject' => 'Welcome to Autopost AI!',
        'intro' => 'Welcome aboard! We're excited to have you.',
        'body' => 'Get started by connecting your Instagram account and scheduling your first post.',
        'outro' => 'If you have any questions, feel free to contact our support team.',
    ],
];
```

### `lang/en/instagram.php`

```php
<?php

return [
    'connected' => [
        'subject' => 'Instagram Account Connected',
        'intro' => 'Great news!',
        'body' => 'Your Instagram account @:username has been successfully connected.',
        'outro' => 'You can now schedule posts and manage your content.',
    ],

    'disconnected' => [
        'subject' => 'Instagram Account Disconnected',
        'intro' => 'Your Instagram account @:username has been disconnected.',
        'body' => 'You can reconnect it anytime from your dashboard.',
    ],

    'token_expiring' => [
        'subject' => 'Instagram Token Expiring Soon',
        'intro' => 'Action Required',
        'body' => 'Your Instagram access token for @:username will expire in :days days.',
        'action' => 'Please reconnect your account to continue posting.',
        'outro' => 'This will only take a moment.',
    ],

    'token_expired' => [
        'subject' => 'Instagram Token Expired',
        'intro' => 'Your Instagram connection has expired.',
        'body' => 'Your access token for @:username has expired.',
        'action' => 'Please reconnect your account to resume posting.',
    ],
];
```

---

## 🔧 Implementation Examples

### 1. Using in Mailables

```php
<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user
    ) {}

    /**
     * Build the message.
     */
    public function build()
    {
        // Get user's locale (critical!)
        $locale = $this->user->locale ?? config('app.locale');

        return $this->locale($locale) // ← Set locale first!
            ->subject(__('auth_emails.welcome.subject'))
            ->markdown('emails.welcome', [
                'user' => $this->user,
                'url' => route('dashboard'),
            ]);
    }
}
```

### 2. Using in Notifications

```php
<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InstagramAccountConnected extends Notification
{
    public function __construct(
        public string $username
    ) {}

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        // Get user's locale
        $locale = $notifiable->locale ?? config('app.locale');

        return (new MailMessage)
            ->locale($locale) // ← IMPORTANT!
            ->subject(__('instagram.connected.subject'))
            ->greeting(__('emails.greeting', ['name' => $notifiable->name]))
            ->line(__('instagram.connected.intro'))
            ->line(__('instagram.connected.body', ['username' => $this->username]))
            ->action(__('emails.button.view'), url('/instagram'))
            ->line(__('instagram.connected.outro'))
            ->salutation(__('emails.closing') . "\n" . __('emails.team'));
    }
}
```

### 3. Email Blade Templates

**`resources/views/emails/welcome.blade.php`:**

```blade
@component('mail::message')
# {{ __('emails.greeting', ['name' => $user->name]) }}

{{ __('auth_emails.welcome.intro') }}

{{ __('auth_emails.welcome.body') }}

@component('mail::button', ['url' => $url])
{{ __('emails.button.get_started') }}
@endcomponent

{{ __('auth_emails.welcome.outro') }}

{{ __('emails.closing') }}<br>
{{ __('emails.team') }}

@component('mail::subcopy')
{{ __('emails.footer') }}
@endcomponent
@endcomponent
```

**`resources/views/emails/instagram/token-expiring.blade.php`:**

```blade
@component('mail::message')
# {{ __('emails.greeting', ['name' => $user->name]) }}

{{ __('instagram.token_expiring.intro') }}

{{ __('instagram.token_expiring.body', [
    'username' => $account->username,
    'days' => $daysUntilExpiry
]) }}

{{ __('instagram.token_expiring.action') }}

@component('mail::button', ['url' => route('instagram.connect')])
{{ __('emails.button.reconnect', ['default' => 'Reconnect Account']) }}
@endcomponent

{{ __('instagram.token_expiring.outro') }}

{{ __('emails.closing') }}<br>
{{ __('emails.team') }}
@endcomponent
```

---

## ✅ Email Translation Checklist

Before sending ANY email, verify:

- [ ] **Translation files exist** for all 3 languages (en, ru, es)
- [ ] **`->locale()`** is set in Mailable or Notification
- [ ] **Subject line** uses `__()`
- [ ] **All body text** uses `__()`
- [ ] **All buttons** use `__()`
- [ ] **Footer text** uses `__()`
- [ ] **Dynamic content** uses placeholders (`:name`, `:url`, etc.)
- [ ] **Tested in all 3 languages**
- [ ] **No hardcoded English text** anywhere

---

## 🎯 Getting User's Locale

### From User Model

```php
$locale = $user->locale ?? config('app.locale');
```

### From Company Settings

```php
$locale = $user->currentCompany->settings['locale'] ?? 'en';
```

### From Current Request

```php
$locale = app()->getLocale();
```

---

## 🧪 Testing Emails

### In Tinker

```php
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

$user = User::first();

// Test English
$user->locale = 'en';
Mail::to($user)->send(new WelcomeEmail($user));

// Test Russian
$user->locale = 'ru';
Mail::to($user)->send(new WelcomeEmail($user));

// Test Spanish
$user->locale = 'es';
Mail::to($user)->send(new WelcomeEmail($user));
```

### Email Preview Route (Development Only)

Add to `routes/web.php`:

```php
if (app()->environment('local')) {
    Route::get('/email-preview/{email}/{locale}', function ($email, $locale) {
        App::setLocale($locale);
        $user = User::first();

        return match($email) {
            'welcome' => new App\Mail\WelcomeEmail($user),
            'verify' => new App\Mail\VerifyEmail($user),
            'instagram-connected' => (new App\Notifications\InstagramAccountConnected('test_user'))
                ->toMail($user),
            default => abort(404),
        };
    })->name('email.preview');
}
```

**Usage:**

- `/email-preview/welcome/en`
- `/email-preview/welcome/ru`
- `/email-preview/welcome/es`

---

## ❌ Anti-Patterns (NEVER DO THIS)

### Bad Example 1: Hardcoded Text

```php
public function toMail($notifiable)
{
    return (new MailMessage)
        ->subject('Welcome to Autopost AI') // ← BAD!
        ->line('Thank you for registering!') // ← BAD!
        ->action('Get Started', url('/dashboard')); // ← BAD!
}
```

### Bad Example 2: No Locale Set

```php
public function build()
{
    return $this
        // Missing ->locale()! ← BAD!
        ->subject(__('emails.welcome'))
        ->markdown('emails.welcome');
}
```

### Bad Example 3: Mixing Translated and Hardcoded

```php
public function toMail($notifiable)
{
    return (new MailMessage)
        ->locale($notifiable->locale)
        ->subject(__('emails.welcome.subject')) // ← Good
        ->line('Your account @' . $this->username . ' is ready!') // ← BAD!
        ->action(__('emails.button.view'), url('/dashboard')); // ← Good
}
```

---

## ✅ Good Examples

### Example 1: Complete Translation

```php
public function toMail($notifiable)
{
    $locale = $notifiable->locale ?? 'en';

    return (new MailMessage)
        ->locale($locale) // ← Set locale
        ->subject(__('instagram.connected.subject')) // ← Translated
        ->greeting(__('emails.greeting', ['name' => $notifiable->name])) // ← Translated with placeholder
        ->line(__('instagram.connected.body', ['username' => $this->username])) // ← Translated with placeholder
        ->action(__('emails.button.view'), url('/instagram')) // ← Translated
        ->salutation(__('emails.team')); // ← Translated
}
```

### Example 2: Complex Email with Multiple Sections

```php
public function build()
{
    $locale = $this->user->locale ?? config('app.locale');

    return $this->locale($locale)
        ->subject(__('instagram.token_expiring.subject'))
        ->markdown('emails.instagram.token-expiring', [
            'user' => $this->user,
            'account' => $this->account,
            'daysUntilExpiry' => $this->account->token_expires_at->diffInDays(now()),
            'reconnectUrl' => route('instagram.connect'),
        ]);
}
```

---

## 📋 Common Emails That Need Translation

### Authentication

- [ ] Email verification
- [ ] Password reset request
- [ ] Password changed confirmation
- [ ] Magic link login
- [ ] Welcome email (new user)
- [ ] Account suspended
- [ ] Account deleted

### Instagram

- [ ] Account connected
- [ ] Account disconnected
- [ ] Token expiring soon (7 days warning)
- [ ] Token expired
- [ ] Sync failed
- [ ] Reconnection required

### Content

- [ ] Post published successfully
- [ ] Post failed to publish
- [ ] Post scheduled
- [ ] Post reminder (upcoming)
- [ ] Draft saved

### Billing (Future)

- [ ] Payment successful
- [ ] Payment failed
- [ ] Wallet topped up
- [ ] Low balance warning
- [ ] Invoice receipt

### Company/Team (Future)

- [ ] Invited to company
- [ ] Role changed
- [ ] Removed from company
- [ ] Company deleted

---

## 🔍 Code Review Checklist

When reviewing PRs that add emails:

- [ ] All text uses `__()`
- [ ] Locale is set via `->locale()`
- [ ] Translation files exist for all languages
- [ ] Placeholders are used correctly
- [ ] Email has been tested in all languages
- [ ] No hardcoded strings
- [ ] Subject line is translated
- [ ] Button text is translated

---

## 📊 Email Translation Coverage Tool

Create a command to audit email translations:

```php
php artisan make:command AuditEmailTranslations
```

This should check:

- All Mailable classes
- All Notification classes that send emails
- Verify `->locale()` is used
- Check for hardcoded strings
- Verify translation keys exist in all languages

---

## 🌍 Adding New Languages

When adding a new language (e.g., French):

1. Create `lang/fr/emails.php`
2. Create `lang/fr/auth_emails.php`
3. Create `lang/fr/instagram.php`
4. Create `lang/fr/notifications.php`
5. Test all emails in new language
6. Update `SetLocale` middleware to support new locale
7. Add to supported locales list

---

## 📝 Summary

**Golden Rules:**

1. ✅ **ALWAYS** set locale with `->locale()`
2. ✅ **ALWAYS** use `__()` for all text
3. ✅ **ALWAYS** create translations for all languages
4. ✅ **ALWAYS** test in all languages
5. ❌ **NEVER** hardcode English text
6. ❌ **NEVER** send emails without locale set

**Remember:** Your users could be anywhere in the world. Respect their language preference!

---

**Last Updated:** October 10, 2025  
**Status:** Active and Enforced  
**Compliance:** Mandatory for all emails
