# Language Persistence Strategy

## Overview

This document explains how language preferences are stored and retrieved in Autopost AI. The system uses a smart multi-layer approach that works for both authenticated users and guests.

---

## Storage Methods

### ❌ What We DON'T Use: Cookies

**Why NOT cookies?**

- Sent with every HTTP request (performance overhead)
- Limited to 4KB
- Privacy concerns
- Clutters request headers
- Harder to manage

### ✅ What We USE: LocalStorage + Database

**Primary Storage:**

1. **LocalStorage** (for guests)
2. **Database** (for authenticated users)

**Cookie Bridge:**

- Small cookie ONLY to pass LocalStorage value to server
- Not used for storage, just for communication
- Expires in 1 year

---

## Priority Order

The system checks language preference in this order:

```
1. User's Database Locale (users.locale column)
   └─ For authenticated users
   └─ Persists across all devices
   └─ Highest priority

2. Session Locale (Laravel session)
   └─ Current session only
   └─ Temporary storage

3. LocalStorage via Cookie Bridge
   └─ For guests
   └─ Persists across sessions
   └─ Survives browser restart

4. URL Segment (/en/, /ru/, /es/)
   └─ For SEO and shareable links
   └─ Legacy support

5. Browser Accept-Language Header
   └─ Auto-detect user's browser language
   └─ First visit only

6. Default to English
   └─ Fallback
```

---

## How It Works

### For Guest Users:

```
1. First Visit
   ├─ Browser language detected (Accept-Language header)
   ├─ Applied automatically
   └─ No storage yet

2. User Selects Language
   ├─ Saved to LocalStorage (browser)
   │  └─ Key: 'preferred_locale'
   │  └─ Value: 'en', 'ru', or 'es'
   ├─ Synced to cookie (for server detection)
   │  └─ Name: 'preferred_locale'
   │  └─ Max-Age: 1 year
   │  └─ SameSite: Lax
   └─ Saved to session (for current request)

3. Next Visit (same browser)
   ├─ LocalStorage read on page load
   ├─ Cookie synced automatically
   ├─ Server detects from cookie
   ├─ Language applied immediately
   └─ User sees their preferred language!

4. User Registers/Logs In
   ├─ LocalStorage language becomes initial preference
   └─ Saved to database (users.locale)
```

### For Authenticated Users:

```
1. Login
   ├─ Database locale loaded (users.locale)
   └─ Applied immediately

2. Change Language
   ├─ Updated in database
   ├─ Updated in LocalStorage (for consistency)
   └─ Applied immediately

3. Use Another Device
   ├─ Login
   ├─ Database locale loaded
   └─ Same language everywhere!
```

---

## Implementation Details

### Frontend (JavaScript)

**File:** `resources/js/app.js`

```javascript
// On page load, sync LocalStorage to cookie
const preferredLocale = window.localStorage.getItem('preferred_locale')
if (preferredLocale) {
    document.cookie = `preferred_locale=${preferredLocale}; path=/; max-age=31536000; SameSite=Lax`
}
```

**File:** `resources/js/Components/LanguageSelector.vue`

```javascript
const changeLanguage = (locale) => {
    // Save to LocalStorage (persists across sessions)
    window.localStorage.setItem('preferred_locale', locale)

    // Send to server (updates database for users, session for guests)
    router.post(route('locale.change'), { locale })
}
```

### Backend (PHP)

**File:** `app/Http/Middleware/SetLocale.php`

```php
public function handle(Request $request, Closure $next): Response
{
    $locale = null;

    // 1. Database (users)
    if ($request->user() && $request->user()->locale) {
        $locale = $request->user()->locale;
    }

    // 2. Session
    if (!$locale && session()->has('locale')) {
        $locale = session('locale');
    }

    // 3. Cookie (from LocalStorage)
    if (!$locale && $request->cookie('preferred_locale')) {
        $locale = $request->cookie('preferred_locale');
        session(['locale' => $locale]);
    }

    // 4. URL segment
    // 5. Browser header
    // 6. Default to 'en'

    App::setLocale($locale);
    return $next($request);
}
```

**File:** `app/Http/Controllers/LocaleController.php`

```php
public function __invoke(Request $request): RedirectResponse
{
    $locale = $request->input('locale');

    // Validate
    if (!in_array($locale, ['en', 'ru', 'es'])) {
        return back();
    }

    // Save to session
    session(['locale' => $locale]);

    // If user is authenticated, save to database
    if ($user = $request->user()) {
        $user->update(['locale' => $locale]);
    }

    return back();
}
```

---

## Benefits

### ✨ For Users:

1. **Guests:**
    - Language persists across sessions
    - No need to select every time
    - Works immediately on return visits
    - No account required

2. **Authenticated Users:**
    - Language synced across all devices
    - Saved permanently in profile
    - Automatic on login
    - Can change anytime

### 🚀 For Developers:

1. **Performance:**
    - LocalStorage doesn't hit server
    - Cookie only syncs value (minimal overhead)
    - No database queries for guests

2. **Privacy:**
    - LocalStorage stays in browser
    - Cookie only for communication
    - GDPR-friendly (functional cookie)
    - No tracking

3. **Reliability:**
    - Multiple fallbacks
    - Works offline (LocalStorage)
    - Survives cache clears (database for users)
    - Progressive enhancement

---

## Browser Support

| Feature      | Support                     |
| ------------ | --------------------------- |
| LocalStorage | All modern browsers (IE 8+) |
| Cookies      | All browsers                |
| Database     | Server-side (always works)  |

**Fallback chain:** If LocalStorage fails → Cookie → Session → Browser → Default

---

## Testing

### Test Guest Persistence:

```bash
# 1. Open browser (incognito for clean state)
http://localhost:8000/

# 2. Select Russian
Click: 🌐 English → Русский

# 3. Check LocalStorage
Open DevTools → Application → LocalStorage
Key: preferred_locale
Value: ru

# 4. Close browser completely

# 5. Open browser again
http://localhost:8000/

# Result: Should be in Russian automatically!
```

### Test User Persistence:

```bash
# 1. Login
admin@autopost.ai / password

# 2. Select Spanish
Click: 🌐 English → Español

# 3. Check database
php artisan tinker
> User::first()->locale
=> "es"

# 4. Logout and login from another browser/device

# Result: Should be in Spanish automatically!
```

---

## Migration Path

If users had cookies before, they will automatically migrate to this new system:

1. Old cookie detected (if exists)
2. Value moved to LocalStorage
3. Old cookie deleted
4. New system takes over

**No user action required!**

---

## Security

### Cookie Security:

```javascript
document.cookie =
    `preferred_locale=${locale}; ` +
    `path=/; ` + // Available site-wide
    `max-age=31536000; ` + // 1 year
    `SameSite=Lax` // CSRF protection
```

**Why SameSite=Lax?**

- Prevents CSRF attacks
- Allows navigation (clicking links)
- Modern security best practice

### Data Validation:

```php
// Server-side validation
if (!in_array($locale, ['en', 'ru', 'es'])) {
    // Reject invalid values
}
```

---

## Future Enhancements

Possible improvements:

1. **Sync across tabs:**
    - Use StorageEvent to sync LocalStorage changes
    - Real-time language updates

2. **User preference dashboard:**
    - Let users see/manage their language
    - Export/import preferences

3. **A/B testing:**
    - Test which language detection works best
    - Analytics on language usage

4. **More languages:**
    - Easy to add (just update the array)
    - Same system scales

---

## Summary

**Storage Strategy:**

- ✅ LocalStorage (guests) + Database (users)
- ✅ Cookie only as communication bridge
- ❌ NOT using cookies for storage

**Benefits:**

- ✅ Persists across sessions (guests)
- ✅ Syncs across devices (users)
- ✅ Privacy-friendly
- ✅ High performance
- ✅ Progressive enhancement

**Result:**
🎉 Best-in-class language persistence system!
