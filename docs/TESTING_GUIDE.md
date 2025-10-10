# Testing Guide

**Project:** Autopost AI  
**Created:** October 10, 2025

---

## Quick Start

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Run Single Test File

```bash
php artisan test tests/Feature/Auth/AuthenticationTest.php
```

---

## Test Users

The following test users are available after running `php artisan db:seed --class=DevelopmentSeeder`:

| Role    | Email               | Password | Company Access |
| ------- | ------------------- | -------- | -------------- |
| Admin   | admin@autopost.ai   | password | Test Company   |
| User    | user@autopost.ai    | password | Test Company   |
| Network | network@autopost.ai | password | Test Company   |

### Using Test Users in Browser

1. Start development server:

    ```bash
    php artisan serve
    # or if using Valet: visit https://autopost-ai.test
    ```

2. Visit homepage
3. Enter one of the test emails above
4. Click "Continue"
5. Enter password: `password`
6. You'll be redirected to dashboard

---

## Manual Testing Scenarios

### 1. Email-First Authentication Flow

#### Test Case: New User Registration

1. Visit `https://autopost-ai.test`
2. Enter new email: `newuser@example.com`
3. Click "Continue"
4. **Expected:** Registration form appears with:
    - Name field (empty)
    - Email field (pre-filled, read-only)
    - Password field
    - Confirm Password field
    - Back button
    - Register button
5. Fill out all fields
6. Click "Register"
7. **Expected:** Redirected to dashboard, user logged in

#### Test Case: Existing User Login

1. Visit `https://autopost-ai.test`
2. Enter existing email: `admin@autopost.ai`
3. Click "Continue"
4. **Expected:** Login form appears with:
    - Email field (pre-filled, read-only)
    - Password field
    - Remember Me checkbox
    - Back button
    - Login button
5. Enter password: `password`
6. Click "Login"
7. **Expected:** Redirected to dashboard, user logged in

#### Test Case: Back Button

1. Enter any email and click Continue
2. See register or login form
3. Click "Back" button
4. **Expected:** Returns to email input screen
5. Form is reset and ready for new email

#### Test Case: Rate Limiting

1. Enter email and click Continue rapidly 6+ times
2. **Expected:** After 5 attempts, see error: "Too many attempts. Please try again in a minute."

---

## Database Testing

### Reset Database

```bash
php artisan migrate:fresh
```

### Seed Test Data

```bash
php artisan db:seed --class=DevelopmentSeeder
```

### Quick Database Check (Tinker)

```bash
php artisan tinker

# Check test users
User::all()->pluck('email', 'id');

# Check companies
Company::with('users')->first();

# Check inquiries
Inquiry::latest()->take(5)->get();
```

---

## Frontend Testing

### Build for Production

```bash
npm run build
```

### Development Mode (Hot Reload)

```bash
npm run dev
```

### Lint JavaScript/Vue

```bash
npm run lint
```

### Format Code

```bash
npm run format
```

---

## Common Issues & Solutions

### Issue: "Page doesn't update after submitting form"

**Cause:** Vite dev server not running or APP_URL mismatch

**Solution:**

1. Check `.env` has correct `APP_URL`:
    ```bash
    APP_URL=https://autopost-ai.test
    ```
2. Clear caches:
    ```bash
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    ```
3. Rebuild assets:
    ```bash
    npm run build
    ```
4. Hard refresh browser (Cmd+Shift+R)

### Issue: "Vue component doesn't react to prop changes"

**Cause:** Refs initialized from props don't auto-update with Inertia redirects

**Solution:** Use Vue `watch()` for reactive prop updates (see IMPLEMENTATION_LOG.md)

### Issue: "CSRF token mismatch"

**Cause:** Session not working or cookie domain mismatch

**Solution:**

1. Check `SESSION_DOMAIN` in `.env`
2. Clear browser cookies
3. Restart browser
4. Try incognito mode

---

## Browser Console Debugging

### Check Inertia Page Data

Open browser console (F12) and type:

```javascript
$page.props
```

This shows all data passed from Laravel to Vue.

### Check Current Route

```javascript
route().current()
```

### Check Authentication State

```javascript
$page.props.auth.user
```

---

## Test Coverage

Current test coverage:

- **Total Tests:** 25
- **Passing:** 25 (100%)
- **Assertions:** 61

### Coverage by Feature

- ✅ Authentication (Login, Register, Logout)
- ✅ Password Reset
- ✅ Email Verification
- ✅ Profile Management
- ✅ Registration Validation
- 🔲 Email Check Flow (TODO: Add feature tests)
- 🔲 Inquiry Tracking (TODO: Add feature tests)
- 🔲 Role-Based Access (TODO: Add feature tests)

---

## Next Steps

1. Add feature tests for email-first authentication
2. Add tests for inquiry tracking
3. Add tests for role-based access control
4. Add tests for company management
5. Set up CI/CD with GitHub Actions
