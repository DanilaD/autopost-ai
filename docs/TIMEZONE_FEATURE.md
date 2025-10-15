# Timezone Feature Documentation

**Version:** 1.0  
**Last Updated:** October 10, 2025  
**Status:** ✅ Implemented & Tested

---

## 📋 Overview

The timezone feature allows users to set their preferred timezone for displaying dates and times throughout the application. All dates are stored in UTC in the database (Laravel default), but are automatically converted to the user's timezone for display.

### Key Features

- ✅ **Automatic Timezone Detection** - Browser timezone is detected during registration
- ✅ **Timezone Management** - Users can change their timezone in profile settings
- ✅ **Application-wide Support** - All dates/times displayed in user's timezone
- ✅ **Full Timezone List** - Support for all PHP timezones
- ✅ **Default to UTC** - Sensible fallback if detection fails

---

## 🏗️ Architecture

### Flow

```
Registration/Login → Timezone Detection → Store in DB → Middleware Sets App Timezone → Display in User's Timezone
```

### Components

1. **Database**
   - `users.timezone` column (string, default: 'UTC')

2. **Backend Services**
   - `TimezoneService` - Timezone utilities and list management
   - `SetUserTimezone` middleware - Sets app timezone per request

3. **Frontend**
   - `useTimezone.js` composable - Browser timezone detection
   - Profile form with timezone selector

---

## 📁 File Structure

```
app/
├── Http/
│   ├── Controllers/
│   │   └── ProfileController.php        # Updated to pass timezones
│   ├── Middleware/
│   │   └── SetUserTimezone.php          # NEW: Sets user's timezone
│   └── Requests/
│       └── ProfileUpdateRequest.php     # Updated to validate timezone
├── Models/
│   └── User.php                         # Added timezone to fillable
└── Services/
    └── TimezoneService.php              # NEW: Timezone utilities

resources/js/
├── composables/
│   └── useTimezone.js                   # NEW: Timezone detection
└── Pages/
    ├── Auth/
    │   └── Register.vue                 # Updated to detect timezone
    └── Profile/
        └── Partials/
            └── UpdateProfileInformationForm.vue  # Added timezone selector

database/migrations/
└── 2025_10_10_203726_add_timezone_to_users_table.php  # NEW: Adds timezone column

tests/
├── Feature/
│   └── TimezoneTest.php                 # NEW: 13 feature tests
└── Unit/
    └── TimezoneServiceTest.php          # NEW: 11 unit tests
```

---

## 🔧 Implementation Details

### 1. Database Schema

```sql
ALTER TABLE users 
ADD COLUMN timezone VARCHAR(50) DEFAULT 'UTC' AFTER locale;
```

### 2. Timezone Detection

**On Registration:**
```javascript
// Frontend (Register.vue)
import { detectBrowserTimezone } from '@/composables/useTimezone.js'

onMounted(() => {
    form.timezone = detectBrowserTimezone() // e.g., "America/New_York"
})
```

**Detection Method:**
```javascript
// Uses Intl API
const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone
```

### 3. Middleware

```php
// app/Http/Middleware/SetUserTimezone.php
public function handle(Request $request, Closure $next): Response
{
    if (Auth::check() && Auth::user()->timezone) {
        config(['app.timezone' => Auth::user()->timezone]);
        date_default_timezone_set(Auth::user()->timezone);
    }
    
    return $next($request);
}
```

Registered in `bootstrap/app.php`:
```php
$middleware->web(append: [
    \App\Http\Middleware\SetLocale::class,
    \App\Http\Middleware\SetUserTimezone::class,  // Added
    \App\Http\Middleware\HandleInertiaRequests::class,
]);
```

### 4. TimezoneService

Provides utilities for:

- **`getFlatTimezones()`** - Returns flat array of all timezones
- **`getGroupedTimezones()`** - Returns timezones grouped by region
- **`getCommonTimezones()`** - Returns list of frequently used timezones
- **`isValid($timezone)`** - Validates timezone string
- **`getOffsetHours($timezone)`** - Gets UTC offset in hours
- **`formatTimezoneLabel($timezone)`** - Formats timezone with offset (e.g., "London (GMT+0)")

### 5. Profile Settings

Users can change timezone at `/profile`:

```vue
<select v-model="form.timezone">
    <option v-for="(label, value) in timezones" :value="value">
        {{ label }}
    </option>
</select>
```

Timezones are formatted for better UX:
- `UTC` → "Utc (GMT+0)"
- `America/New_York` → "New York (GMT-5)"
- `Europe/London` → "London (GMT+0)"

---

## 🔒 Validation

### Registration
```php
'timezone' => ['nullable', 'string', 'timezone'],
```

### Profile Update
```php
'timezone' => ['required', 'string', 'timezone'],
```

Laravel's built-in `timezone` validation rule ensures only valid timezones are accepted.

---

## 🧪 Testing

### Feature Tests (`tests/Feature/TimezoneTest.php`)

✅ 13 tests covering:
- Registration with/without timezone
- Invalid timezone rejection
- Profile timezone updates
- TimezoneService methods
- Middleware behavior
- Profile page timezone data

### Unit Tests (`tests/Unit/TimezoneServiceTest.php`)

✅ 11 tests covering:
- Flat timezone list
- Grouped timezones
- Timezone validation
- Offset calculations
- Common timezones
- Edge cases

**Run tests:**
```bash
php artisan test --filter TimezoneTest
php artisan test --filter TimezoneServiceTest
```

---

## 🎨 Timezone Indicator in Header

**NEW:** A timezone indicator badge has been added to the application header!

### What It Shows:
- **Badge:** Displays timezone abbreviation (EST, GMT+3, PST, etc.)
- **Hover:** Shows full timezone name, current time, and GMT offset
- **Click:** Takes user to profile settings to change timezone

### Location:
- **Desktop:** Header navigation (between language selector and user dropdown)
- **Mobile:** Mobile menu below user info

### Features:
- ✅ Live time updates every minute
- ✅ Automatic timezone detection
- ✅ Click to change timezone
- ✅ Dark mode compatible
- ✅ Multi-language support
- ✅ Responsive design

**See:** `/TIMEZONE_INDICATOR_IMPLEMENTATION.md` for full details.

---

## 💡 Usage Examples

### Backend: Display Dates in User's Timezone

```php
// In controllers or views
$post->created_at->timezone(Auth::user()->timezone)->format('Y-m-d H:i:s')

// Or let Carbon handle it automatically (middleware sets timezone)
$post->created_at->format('Y-m-d H:i:s')
```

### Frontend: Format Dates

```javascript
import { formatInTimezone } from '@/composables/useTimezone.js'

const formattedDate = formatInTimezone(
    post.created_at, 
    user.timezone,
    { dateStyle: 'medium', timeStyle: 'short' }
)
```

### Check if Timezone is Valid

```php
$service = new TimezoneService();
if ($service->isValid($timezone)) {
    // Valid timezone
}
```

---

## 🌍 Supported Timezones

All PHP `DateTimeZone` identifiers are supported, including:

**Americas:**
- `America/New_York`
- `America/Chicago`
- `America/Los_Angeles`
- `America/Mexico_City`
- etc.

**Europe:**
- `Europe/London`
- `Europe/Paris`
- `Europe/Berlin`
- `Europe/Moscow`
- etc.

**Asia:**
- `Asia/Tokyo`
- `Asia/Shanghai`
- `Asia/Dubai`
- `Asia/Kolkata`
- etc.

**Others:**
- `UTC`
- `Pacific/Auckland`
- `Australia/Sydney`
- `Africa/Cairo`
- etc.

**Excluded:**
- `Etc/*` timezones (deprecated)
- `SystemV/*` timezones (deprecated)

---

## 🔄 Migration Guide

If you need to run the migration:

```bash
php artisan migrate
```

This adds the `timezone` column to the `users` table. Existing users will have `UTC` as default.

---

## 🐛 Troubleshooting

### Issue: Dates still showing in UTC

**Solution:** Check that:
1. User has timezone set: `Auth::user()->timezone`
2. Middleware is registered in `bootstrap/app.php`
3. Middleware is in correct order (after SetLocale)

### Issue: Invalid timezone error

**Solution:** Ensure timezone string is valid PHP timezone identifier:
```php
$service = new TimezoneService();
if (!$service->isValid($timezone)) {
    // Use UTC as fallback
    $timezone = 'UTC';
}
```

### Issue: Timezone not detected on registration

**Solution:** Browser must support Intl API (all modern browsers do). Falls back to UTC if not supported.

---

## 🎯 Best Practices

1. **Always Store in UTC** - Database dates should always be UTC
2. **Convert on Display** - Convert to user timezone only when displaying
3. **Validate Input** - Use Laravel's `timezone` validation rule
4. **Provide Fallback** - Default to UTC if timezone is invalid/missing
5. **Test Across Timezones** - Test with different timezones including edge cases

---

## 🔮 Future Enhancements

Potential improvements:

- 🌟 Display user's current local time in profile
- 🌟 Show timezone offset in user badge/dropdown
- 🌟 Allow format preferences (12h vs 24h)
- 🌟 Automatic DST adjustment notices
- 🌟 Timezone-aware scheduling for posts

---

## 📚 Related Documentation

- [Database Schema](./DATABASE_SCHEMA.md) - `users` table structure
- [Internationalization](./INTERNATIONALIZATION_PLAN.md) - Language features
- [Testing Guide](./TESTING_GUIDE.md) - How to write tests

---

## 🤝 Contributing

When making timezone-related changes:

1. ✅ Update tests if changing behavior
2. ✅ Update this documentation
3. ✅ Test with multiple timezones
4. ✅ Consider DST edge cases
5. ✅ Update version and date at top of this file

---

**Last Updated:** October 10, 2025  
**Version:** 1.0

