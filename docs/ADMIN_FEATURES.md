# Admin Features Documentation

**Project:** Autopost AI  
**Version:** 1.0  
**Date:** October 10, 2025  
**Status:** Production Ready

---

## Overview

Complete administration system for managing inquiries and users. Admin features are role-based and only accessible to users with the `ADMIN` role in their current company.

---

## Features Summary

### 1. Inquiry Management
- View all inquiries with pagination (15 per page)
- Search inquiries by email
- Sort by email or creation date
- Delete spam/test inquiries
- Export inquiries to CSV
- Real-time statistics dashboard

### 2. User Management  
- View all users with pagination
- Search users by name or email
- Sort by multiple fields
- **Send password reset links** to users
- **Suspend/Unsuspend** user accounts
- **Impersonate** users for support
- View user statistics (posts, accounts, companies)
- Filter by status (active/suspended)

### 3. Impersonation System
- Admin can log in as any regular user
- Impersonation banner shown while active
- Automatic session logging for audit
- One-click return to admin account
- Time-limited sessions (60 minutes)

---

## Access Control

### Admin Role Requirements

Admin features are protected by the `EnsureUserIsAdmin` middleware which checks:

1. User is authenticated
2. User has `ADMIN` role in their current company
3. Company membership is active

**Permission Checks:**
- `User::isAdminInCurrentCompany()` - Main authorization method
- Role checked via `company_user` pivot table
- Non-admins receive HTTP 403 Forbidden

### Menu Visibility

Admin menu items only appear when:
```javascript
$page.props.auth.user?.is_admin === true
```

The `HandleInertiaRequests` middleware injects this property for all authenticated users.

---

## Inquiry Management

### Accessing Inquiries

**Route:** `/admin/inquiries`  
**Component:** `Admin/Inquiries/Index.vue`

### Features

#### View Inquiries Table
- Displays: email, IP address, user agent, creation date
- 15 results per page with Laravel pagination
- Empty state when no inquiries exist

#### Search
- Real-time search by email
- Debounced input (300ms delay)
- Preserves pagination state
- Clear search button

#### Sorting
- Sort by: email, created_at
- Directions: ascending, descending
- Click column headers to toggle sort
- Visual indicators (↑/↓) show current sort

#### Statistics Dashboard
Four stat cards showing:
- **Total Inquiries:** All-time count
- **Today:** Inquiries from current day
- **This Week:** Inquiries from current week
- **This Month:** Inquiries from current month

#### Delete Inquiry
- Confirmation dialog before deletion
- Immediate removal from database
- Toast notification on success/error
- Cannot be undone

#### Export to CSV
- Downloads all inquiries matching current filters
- Includes: email, IP, user agent, timestamp
- Filename: `inquiries-YYYY-MM-DD.csv`
- Streams output for large datasets

### API Endpoints

```php
// View inquiries
GET /admin/inquiries
Parameters: search, sort, direction, page

// Delete inquiry
DELETE /admin/inquiries/{id}

// Export to CSV
GET /admin/inquiries/export
Parameters: search, sort, direction
```

---

## User Management

### Accessing Users

**Route:** `/admin/users`  
**Component:** `Admin/Users/Index.vue`

### Features

#### User Table
Displays:
- **Name & Email:** User identity
- **Role Badge:** Color-coded role (admin/user/network)
- **Status Badge:** Active (green) or Suspended (red)
- **Last Login:** Relative time ("2 days ago")
- **Statistics:** Companies, Instagram accounts, posts

#### Search & Filter
- Search by name or email
- Real-time filtering
- Preserves sort and pagination

#### Statistics Dashboard
Four stat cards:
- **Total Users:** All users count
- **Active Users:** Non-suspended users
- **Suspended Users:** Currently suspended count
- **New This Month:** Recent registrations

#### User Actions

Each user row has 4 action buttons:

**1. Send Password Reset (🔑)**
- Sends Laravel password reset email to user
- Includes note that admin requested it
- Confirmation dialog before sending
- Toast notification on success

**2. Suspend User (🚫)**
- Opens modal requiring suspension reason
- Reason is mandatory (max 1000 chars)
- Updates `suspended_at`, `suspended_by`, `suspension_reason`
- Suspended users cannot log in
- Cannot suspend yourself
- Cannot suspend other admins

**3. Unsuspend User (✅)**
- Confirmation dialog
- Clears all suspension fields
- User can immediately log in again
- Toast notification on success

**4. Impersonate User (🎭)**
- Opens confirmation modal
- Starts impersonation session
- Redirects to dashboard as that user
- Disabled for suspended users
- Cannot impersonate other admins

### Suspension System

#### How It Works

When a user is suspended:
1. `suspended_at` set to current timestamp
2. `suspended_by` set to admin's user ID
3. `suspension_reason` stores the reason
4. User Model: `isSuspended()` returns true
5. Login attempts are blocked
6. User sessions are invalidated

#### Database Fields

```sql
suspended_at TIMESTAMP NULL       -- When suspended
suspended_by BIGINT UNSIGNED NULL -- Admin who suspended
suspension_reason TEXT NULL        -- Why suspended
```

#### Checking Suspension Status

```php
// In User model
public function isSuspended(): bool
{
    return $this->suspended_at !== null;
}

// Usage
if ($user->isSuspended()) {
    return 'User account is suspended';
}
```

### API Endpoints

```php
// View users
GET /admin/users
Parameters: search, sort, direction, status, page

// Send password reset
POST /admin/users/{id}/password-reset

// Suspend user
POST /admin/users/{id}/suspend
Body: { reason: string }

// Unsuspend user
POST /admin/users/{id}/unsuspend

// Impersonate user
POST /admin/users/{id}/impersonate
```

---

## Impersonation System

### Overview

Allows admins to view the application as any regular user for support and debugging purposes.

### How It Works

#### Starting Impersonation

1. Admin clicks impersonate button (🎭) for user
2. Confirmation modal appears
3. On confirmation:
   - Current admin ID saved in session
   - User logs in as target user
   - Redirected to dashboard
   - Yellow banner appears at top

#### Session Storage

```php
session(['impersonate' => [
    'admin_id' => 123,
    'admin_name' => 'John Admin',
    'admin_email' => 'admin@example.com',
    'started_at' => '2025-10-10 14:30:00',
    'target_user_id' => 456
]]);
```

#### Impersonation Banner

Appears at top of all pages while impersonating:
```
🎭 You are impersonating: Jane Doe (jane@example.com)
[Stop Impersonation Button]
```

#### Stopping Impersonation

1. Click "Stop Impersonation" button in banner
2. Session data cleared
3. Logs back in as original admin
4. Redirects to user management page
5. Toast: "Impersonation ended"

### Security Measures

#### Restrictions
- ❌ Cannot impersonate yourself
- ❌ Cannot impersonate another admin
- ❌ Cannot impersonate suspended users
- ✅ Only regular users can be impersonated

#### Audit Logging

All impersonation actions are logged to Laravel logs:

```php
[2025-10-10 14:30:00] production.INFO: User Impersonation {
    "action": "started",
    "admin_id": 123,
    "admin_email": "admin@example.com",
    "target_user_id": 456,
    "target_email": "user@example.com",
    "timestamp": "2025-10-10 14:30:00",
    "ip_address": "192.168.1.1",
    "user_agent": "Mozilla/5.0..."
}
```

#### Auto-Expiration

Impersonation sessions automatically expire after 60 minutes. The `ImpersonationService::validateImpersonationSession()` method can be called to check expiration.

### API Endpoints

```php
// Start impersonation
POST /admin/users/{id}/impersonate

// Stop impersonation
POST /admin/impersonate/stop
```

---

## Frontend Components

### Reusable Components

#### SearchInput.vue
- Debounced search (300ms)
- Clear button (X)
- Search icon indicator
- Preserves query params

**Props:**
- `modelValue`: Current search term
- `placeholder`: Placeholder text
- `routeName`: Route to navigate on search
- `debounce`: Debounce delay (default: 300)

#### SortableHeader.vue
- Clickable table header
- Visual sort indicators (↑/↓)
- Active state styling
- Toggles between asc/desc

**Props:**
- `field`: Field name to sort by
- `label`: Display label
- `currentSort`: Current sort field
- `currentDirection`: Current sort direction

#### Pagination.vue
- Laravel pagination links
- Previous/Next buttons
- Page number buttons
- Responsive design

**Props:**
- `links`: Laravel pagination links array

### Admin Pages

#### Admin/Inquiries/Index.vue
- Full-featured inquiry management
- Stats dashboard
- Search, sort, export
- Delete with confirmation

#### Admin/Users/Index.vue
- Comprehensive user management
- All 4 user actions
- Multiple modals (suspend, impersonate)
- Real-time statistics

---

## Translations

Full i18n support in English, Spanish, and Russian.

### Translation Files

- `lang/en/admin.php` - English admin translations
- `lang/es/admin.php` - Spanish admin translations
- `lang/ru/admin.php` - Russian admin translations
- `lang/*/menu.php` - Menu item translations

### Key Translation Sections

```php
'inquiries' => [
    'title', 'email', 'delete', 'export', 'search', ...
],

'users' => [
    'title', 'suspend', 'unsuspend', 'impersonate', ...
],

'impersonating' => 'You are impersonating',
'stop_impersonation' => 'Stop Impersonation',
```

### Usage in Vue

```javascript
import { useI18n } from 'vue-i18n'
const { t } = useI18n()

<h2>{{ t('admin.users.title') }}</h2>
<button>{{ t('admin.users.suspend') }}</button>
```

---

## Services Architecture

### InquiryService

**Location:** `app/Services/InquiryService.php`

**Methods:**
- `getInquiries($filters)` - Paginated inquiry list
- `searchInquiries($query)` - Search by email
- `deleteInquiry($id)` - Remove inquiry
- `getInquiryStats()` - Statistics
- `exportInquiriesToCsv($filters)` - CSV export

### UserManagementService

**Location:** `app/Services/UserManagementService.php`

**Methods:**
- `getUsers($filters)` - Paginated user list
- `sendPasswordResetLink($userId)` - Send reset email
- `suspendUser($userId, $reason, $admin)` - Suspend account
- `unsuspendUser($userId)` - Restore access
- `getUserStats($userId)` - User statistics
- `getUserManagementStats()` - Dashboard stats

### ImpersonationService

**Location:** `app/Services/ImpersonationService.php`

**Methods:**
- `impersonate($admin, $targetUserId)` - Start impersonation
- `stopImpersonation()` - End impersonation
- `canImpersonate($admin, $target)` - Permission check
- `isImpersonating()` - Check if currently impersonating
- `getImpersonationData()` - Get session data
- `validateImpersonationSession()` - Check expiration

---

## Testing

### Feature Tests

**Location:** `tests/Feature/Admin/`

**Test Files:**
- `InquiryManagementTest.php` - 8 tests
- `UserManagementTest.php` - 11 tests
- `ImpersonationTest.php` - 7 tests

**Total:** 26 feature tests

### Unit Tests

**Location:** `tests/Unit/Services/`

**Test Files:**
- `InquiryServiceTest.php` - 7 tests
- `UserManagementServiceTest.php` - 12 tests

**Total:** 19 unit tests

### Running Tests

```bash
# All admin tests
php artisan test --filter=Admin

# Service tests
php artisan test tests/Unit/Services

# Specific test
php artisan test --filter=admin_can_impersonate_user
```

---

## Security Best Practices

### Authorization
- ✅ Middleware on all admin routes
- ✅ Double-check permissions in controllers
- ✅ Prevent self-suspension
- ✅ Prevent admin-to-admin actions

### Audit Trail
- ✅ All impersonation logged
- ✅ Suspension tracked with reason
- ✅ Admin ID stored for accountability

### Input Validation
- ✅ Suspension reason required
- ✅ SQL injection prevention in sorting
- ✅ CSRF protection on all forms

### Session Management
- ✅ Impersonation auto-expires
- ✅ Session data properly cleared
- ✅ No sensitive data in client

---

## Troubleshooting

### Common Issues

#### "403 Forbidden" When Accessing Admin Pages
- Verify user has ADMIN role: `User::isAdminInCurrentCompany()`
- Check `current_company_id` is set
- Verify company membership exists

#### Admin Menu Not Appearing
- Check `$page.props.auth.user.is_admin` in browser console
- Verify `HandleInertiaRequests` is injecting is_admin prop
- Clear browser cache

#### Impersonation Not Working
- Check route is `/admin/impersonate/stop` (not protected by admin middleware)
- Verify session driver is working
- Check logs for impersonation events

#### Tests Failing
- Run migrations: `php artisan migrate:fresh`
- Clear config cache: `php artisan config:clear`
- Check database connection

---

## Future Enhancements

### Planned Features
1. **Activity Logging** - Detailed audit log UI
2. **Bulk Actions** - Suspend multiple users at once
3. **Advanced Filters** - More filter options
4. **Email Templates** - Customize notification emails
5. **Role Management** - Change user roles from admin panel
6. **User Merge** - Combine duplicate accounts
7. **Dashboard Analytics** - Visual charts and graphs

---

## Related Documentation

- [Database Schema](./DATABASE_SCHEMA.md) - Database structure
- [User Roles](./PROJECT_PLAN.md) - Role system details
- [Testing Guide](./TESTING_GUIDE.md) - How to test
- [Coding Standards](./CODING_STANDARDS.md) - Code quality rules

---

**Last Updated:** October 10, 2025  
**Version:** 1.0  
**Implemented By:** Cursor AI Assistant

