# Admin Features Implementation Plan

**Version:** 1.0  
**Date:** October 10, 2025  
**Status:** Planning Phase

---

## 📋 Overview

Implementation plan for admin-only features including Inquiry Management and User Management with advanced capabilities like impersonation, password reset links, and user suspension.

---

## 🎯 Goals

1. **Add admin-only menu items** - Inquiry and Users sections
2. **Inquiry Management** - View all inquiries with pagination, sorting, and search
3. **User Management** - Comprehensive user administration with multiple actions

---

## 🏗️ Architecture Overview

**Tech Stack:**

- Backend: Laravel 11 + Inertia.js
- Frontend: Vue 3 + Tailwind CSS
- Architecture: Controller → Service → Repository → Model

**Role System:**

- Roles managed at company level via `company_user` pivot table
- Role enum: `ADMIN`, `USER`, `NETWORK`
- Admin checks via `User::isAdminInCurrentCompany()`

---

## 📦 Phase 1: Foundation & Infrastructure

### 1.1 Database Migrations

**✅ Already Exists:**

- `inquiries` table (email, ip_address, user_agent, created_at)
- `users` table (name, email, password, timestamps, etc.)

**🆕 New Migration Needed:**

```php
// Migration: add_suspended_fields_to_users_table
- suspended_at (timestamp, nullable)
- suspended_by (foreignId to users, nullable)
- suspension_reason (text, nullable)
```

**Why:** Track user suspension status and history for audit purposes.

---

### 1.2 Middleware Creation

**File:** `app/Http/Middleware/EnsureUserIsAdmin.php`

**Purpose:**

- Check if authenticated user has ADMIN role in current company
- Redirect non-admins with error message
- Reusable across all admin routes

**Implementation:**

```php
public function handle($request, Closure $next)
{
    if (!auth()->user()->isAdminInCurrentCompany()) {
        abort(403, 'This action requires administrator privileges.');
    }
    return $next($request);
}
```

**Register in:** `bootstrap/app.php` as route middleware alias `admin`

---

### 1.3 Service Layer Setup

**Services to Create:**

1. **`app/Services/InquiryService.php`**
    - `getInquiries($filters)` - Paginated, filtered, sorted list
    - `searchInquiries($query)` - Search by email
    - `deleteInquiry($id)` - Remove spam/test entries
    - `exportInquiries($filters)` - CSV export (optional)

2. **`app/Services/UserManagementService.php`**
    - `getUsers($filters)` - Paginated user list with company roles
    - `sendPasswordResetLink($userId)` - Generate and email reset link
    - `suspendUser($userId, $reason)` - Suspend user account
    - `unsuspendUser($userId)` - Restore user access
    - `searchUsers($query)` - Search by name/email
    - `getUserStats($userId)` - Get user activity statistics

3. **`app/Services/ImpersonationService.php`**
    - `impersonate($adminId, $targetUserId)` - Start impersonation session
    - `stopImpersonation()` - Return to admin account
    - `canImpersonate($admin, $target)` - Permission check
    - `logImpersonation($adminId, $targetUserId)` - Audit trail

**Why separate services:**

- Single Responsibility Principle
- Easier to test
- Reusable across controllers

---

## 📦 Phase 2: Backend Implementation

### 2.1 Model Enhancements

**User Model (`app/Models/User.php`):**

Add methods:

```php
// Suspension management
public function suspend(string $reason, User $suspendedBy): void
public function unsuspend(): void
public function isSuspended(): bool
public function scopeActive($query) // Not suspended

// User statistics
public function getStatsAttribute(): array // Posts, accounts, last activity
```

Add casts:

```php
'suspended_at' => 'datetime',
```

**Inquiry Model (`app/Models/Inquiry.php`):**

Add scopes:

```php
public function scopeRecent($query, $days = 30)
public function scopeByEmail($query, $email)
```

---

### 2.2 Controllers

**1. InquiryController** (`app/Http/Controllers/Admin/InquiryController.php`)

Routes:

- `GET /admin/inquiries` - List view (index)
- `DELETE /admin/inquiries/{id}` - Delete inquiry (destroy)
- `GET /admin/inquiries/export` - CSV export (export)

Methods:

```php
public function index(Request $request): Response
{
    $inquiries = $this->inquiryService->getInquiries([
        'search' => $request->search,
        'sort' => $request->sort ?? 'created_at',
        'direction' => $request->direction ?? 'desc',
        'per_page' => 15,
    ]);

    return Inertia::render('Admin/Inquiries/Index', [
        'inquiries' => $inquiries,
        'filters' => $request->only(['search', 'sort', 'direction']),
    ]);
}
```

**2. UserManagementController** (`app/Http/Controllers/Admin/UserManagementController.php`)

Routes:

- `GET /admin/users` - List view (index)
- `POST /admin/users/{id}/password-reset` - Send reset link (sendPasswordReset)
- `POST /admin/users/{id}/suspend` - Suspend user (suspend)
- `POST /admin/users/{id}/unsuspend` - Unsuspend user (unsuspend)
- `POST /admin/users/{id}/impersonate` - Start impersonation (impersonate)
- `POST /admin/impersonate/leave` - Stop impersonation (stopImpersonation)

**3. ImpersonationController** (`app/Http/Controllers/Admin/ImpersonationController.php`)

Separate controller for impersonation logic:

- Better separation of concerns
- Dedicated route group
- Easier to add audit logging

---

### 2.3 Routes Configuration

**File:** `routes/web.php`

```php
// Admin routes - requires admin role in current company
Route::middleware(['auth', 'verified', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Inquiry Management
        Route::get('/inquiries', [Admin\InquiryController::class, 'index'])
            ->name('inquiries.index');
        Route::delete('/inquiries/{inquiry}', [Admin\InquiryController::class, 'destroy'])
            ->name('inquiries.destroy');
        Route::get('/inquiries/export', [Admin\InquiryController::class, 'export'])
            ->name('inquiries.export');

        // User Management
        Route::get('/users', [Admin\UserManagementController::class, 'index'])
            ->name('users.index');
        Route::post('/users/{user}/password-reset', [Admin\UserManagementController::class, 'sendPasswordReset'])
            ->name('users.password-reset');
        Route::post('/users/{user}/suspend', [Admin\UserManagementController::class, 'suspend'])
            ->name('users.suspend');
        Route::post('/users/{user}/unsuspend', [Admin\UserManagementController::class, 'unsuspend'])
            ->name('users.unsuspend');

        // Impersonation
        Route::post('/users/{user}/impersonate', [Admin\ImpersonationController::class, 'start'])
            ->name('users.impersonate');
        Route::post('/impersonate/stop', [Admin\ImpersonationController::class, 'stop'])
            ->name('impersonate.stop');
    });
```

---

## 📦 Phase 3: Frontend Implementation

### 3.1 Navigation Updates

**File:** `resources/js/Layouts/AuthenticatedLayout.vue`

Add admin menu items (conditionally):

```vue
<!-- Admin Navigation (Desktop) -->
<NavLink
    v-if="$page.props.auth.user.is_admin"
    :href="route('admin.inquiries.index')"
    :active="route().current('admin.inquiries.*')"
>
    {{ t('menu.inquiries') }}
</NavLink>

<NavLink
    v-if="$page.props.auth.user.is_admin"
    :href="route('admin.users.index')"
    :active="route().current('admin.users.*')"
>
    {{ t('menu.users') }}
</NavLink>
```

**Data needed in Inertia props:**

```php
// HandleInertiaRequests middleware
'auth' => [
    'user' => $request->user() ? [
        'id' => $request->user()->id,
        'name' => $request->user()->name,
        'email' => $request->user()->email,
        'is_admin' => $request->user()->isAdminInCurrentCompany(), // ADD THIS
    ] : null,
],
```

---

### 3.2 Vue Components & Pages

#### 3.2.1 Inquiry Management Page

**File:** `resources/js/Pages/Admin/Inquiries/Index.vue`

**Features:**

- Data table with pagination
- Search by email
- Sort by email/created_at
- Delete action with confirmation
- Export to CSV button
- Empty state when no inquiries
- Loading states

**Key Components:**

```vue
<template>
    <AuthenticatedLayout>
        <Head title="Inquiries" />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header with Search & Export -->
                <div class="mb-6 flex justify-between">
                    <SearchInput v-model="filters.search" @search="search" />
                    <PrimaryButton @click="exportInquiries">
                        Export CSV
                    </PrimaryButton>
                </div>

                <!-- Inquiries Table -->
                <div
                    class="overflow-hidden bg-white shadow-sm dark:bg-gray-800"
                >
                    <table>
                        <thead>
                            <tr>
                                <SortableHeader
                                    field="email"
                                    :current="filters.sort"
                                >
                                    Email
                                </SortableHeader>
                                <th>IP Address</th>
                                <th>User Agent</th>
                                <SortableHeader
                                    field="created_at"
                                    :current="filters.sort"
                                >
                                    Created At
                                </SortableHeader>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="inquiry in inquiries.data"
                                :key="inquiry.id"
                            >
                                <td>{{ inquiry.email }}</td>
                                <td>{{ inquiry.ip_address }}</td>
                                <td class="truncate">
                                    {{ inquiry.user_agent }}
                                </td>
                                <td>{{ formatDate(inquiry.created_at) }}</td>
                                <td>
                                    <DangerButton
                                        @click="deleteInquiry(inquiry.id)"
                                    >
                                        Delete
                                    </DangerButton>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <Pagination :links="inquiries.links" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

---

#### 3.2.2 User Management Page

**File:** `resources/js/Pages/Admin/Users/Index.vue`

**Features:**

- User list with company roles
- Search by name/email
- Sort by various fields
- Multiple actions per user:
    - Send password reset link
    - Suspend/Unsuspend
    - Impersonate
- Status badges (active/suspended)
- Impersonation banner when active
- Confirmation modals for destructive actions

**Table Columns:**

- Name
- Email
- Role (in current company)
- Status (Active/Suspended)
- Last Login
- Companies Count
- Instagram Accounts
- Actions (dropdown)

**Key Components:**

```vue
<template>
    <AuthenticatedLayout>
        <Head title="User Management" />

        <!-- Impersonation Banner -->
        <ImpersonationBanner
            v-if="$page.props.impersonating"
            :original-user="$page.props.impersonating.original"
            @stop="stopImpersonation"
        />

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Header with Search -->
                <div class="mb-6">
                    <SearchInput
                        v-model="filters.search"
                        placeholder="Search by name or email..."
                        @search="search"
                    />
                </div>

                <!-- Users Table -->
                <div
                    class="overflow-hidden bg-white shadow-sm dark:bg-gray-800"
                >
                    <table
                        class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
                    >
                        <thead>
                            <tr>
                                <SortableHeader
                                    field="name"
                                    :current="filters.sort"
                                >
                                    Name
                                </SortableHeader>
                                <SortableHeader
                                    field="email"
                                    :current="filters.sort"
                                >
                                    Email
                                </SortableHeader>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Stats</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="user in users.data" :key="user.id">
                                <td>
                                    <div class="flex items-center">
                                        <UserAvatar :user="user" />
                                        <span>{{ user.name }}</span>
                                    </div>
                                </td>
                                <td>{{ user.email }}</td>
                                <td>
                                    <RoleBadge :role="user.role" />
                                </td>
                                <td>
                                    <StatusBadge
                                        :suspended="user.suspended_at"
                                    />
                                </td>
                                <td>
                                    {{ formatRelativeDate(user.last_login_at) }}
                                </td>
                                <td>
                                    <UserStats :user="user" />
                                </td>
                                <td>
                                    <UserActionsDropdown
                                        :user="user"
                                        @password-reset="sendPasswordReset"
                                        @suspend="confirmSuspend"
                                        @unsuspend="unsuspendUser"
                                        @impersonate="confirmImpersonate"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <Pagination :links="users.links" />
                </div>
            </div>
        </div>

        <!-- Modals -->
        <SuspendUserModal
            v-model="showSuspendModal"
            :user="selectedUser"
            @confirm="suspendUser"
        />

        <ConfirmationModal
            v-model="showImpersonateModal"
            title="Impersonate User"
            :message="`You are about to impersonate ${selectedUser?.name}. Continue?`"
            @confirm="impersonateUser"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from '@/composables/useToast'

const props = defineProps({
    users: Object,
    filters: Object,
})

const toast = useToast()
const selectedUser = ref(null)
const showSuspendModal = ref(false)
const showImpersonateModal = ref(false)

// Actions
const sendPasswordReset = (user) => {
    router.post(
        route('admin.users.password-reset', user.id),
        {},
        {
            onSuccess: () => {
                toast.addToast(
                    `Password reset link sent to ${user.email}`,
                    'success'
                )
            },
        }
    )
}

const confirmSuspend = (user) => {
    selectedUser.value = user
    showSuspendModal.value = true
}

const suspendUser = (reason) => {
    router.post(
        route('admin.users.suspend', selectedUser.value.id),
        {
            reason,
        },
        {
            onSuccess: () => {
                toast.addToast('User suspended successfully', 'success')
                showSuspendModal.value = false
            },
        }
    )
}

const unsuspendUser = (user) => {
    router.post(
        route('admin.users.unsuspend', user.id),
        {},
        {
            onSuccess: () => {
                toast.addToast('User access restored', 'success')
            },
        }
    )
}

const confirmImpersonate = (user) => {
    selectedUser.value = user
    showImpersonateModal.value = true
}

const impersonateUser = () => {
    router.post(route('admin.users.impersonate', selectedUser.value.id))
}

const stopImpersonation = () => {
    router.post(route('admin.impersonate.stop'))
}
</script>
```

---

### 3.3 Reusable Components

**Components to Create:**

1. **`SearchInput.vue`**
    - Debounced search input
    - Clear button
    - Loading indicator

2. **`SortableHeader.vue`**
    - Clickable table header
    - Sort direction indicator (↑/↓)
    - Active state styling

3. **`Pagination.vue`**
    - Previous/Next buttons
    - Page number links
    - "Showing X to Y of Z results"

4. **`RoleBadge.vue`**
    - Color-coded role display
    - Icon per role type
    - Tooltip with role description

5. **`StatusBadge.vue`**
    - Active (green) / Suspended (red)
    - Shows suspension date on hover

6. **`UserAvatar.vue`**
    - Initials-based avatar
    - Fallback for users without photos

7. **`UserStats.vue`**
    - Compact display of user metrics
    - Tooltip with detailed stats

8. **`UserActionsDropdown.vue`**
    - Three-dot menu
    - Conditional actions based on user status
    - Icon + label for each action

9. **`ImpersonationBanner.vue`**
    - Fixed top banner
    - Shows original admin info
    - "Stop Impersonation" button
    - Warning styling

10. **`SuspendUserModal.vue`**
    - Textarea for suspension reason
    - Confirm/Cancel buttons
    - Validation for required reason

11. **`ConfirmationModal.vue`**
    - Generic confirmation dialog
    - Reusable across features

---

### 3.4 Translation Keys

**Files to update:**

- `lang/en/menu.php`
- `lang/es/menu.php`
- `lang/ru/menu.php`

**New keys:**

```php
// menu.php
'inquiries' => 'Inquiries',
'users' => 'Users',
'admin' => 'Administration',

// admin.php (new file)
'inquiries' => [
    'title' => 'Inquiry Management',
    'email' => 'Email',
    'ip_address' => 'IP Address',
    'user_agent' => 'User Agent',
    'created_at' => 'Created At',
    'delete' => 'Delete',
    'export' => 'Export CSV',
    'search' => 'Search by email...',
    'no_results' => 'No inquiries found',
],

'users' => [
    'title' => 'User Management',
    'name' => 'Name',
    'email' => 'Email',
    'role' => 'Role',
    'status' => 'Status',
    'last_login' => 'Last Login',
    'stats' => 'Statistics',
    'actions' => 'Actions',
    'search' => 'Search users...',
    'no_results' => 'No users found',

    // Actions
    'send_password_reset' => 'Send Password Reset',
    'suspend' => 'Suspend User',
    'unsuspend' => 'Unsuspend User',
    'impersonate' => 'Impersonate',

    // Status
    'active' => 'Active',
    'suspended' => 'Suspended',

    // Impersonation
    'impersonating' => 'You are impersonating',
    'stop_impersonation' => 'Stop Impersonation',

    // Modals
    'suspend_modal_title' => 'Suspend User',
    'suspend_modal_message' => 'Please provide a reason for suspension:',
    'suspension_reason' => 'Reason',
    'confirm_suspend' => 'Suspend',
    'confirm_impersonate_title' => 'Impersonate User',
    'confirm_impersonate_message' => 'You are about to view the application as this user. Continue?',
],
```

---

## 📦 Phase 4: Advanced Features

### 4.1 Impersonation System

**Session Management:**

```php
// Store original admin ID in session
session(['impersonate' => [
    'admin_id' => auth()->id(),
    'started_at' => now(),
]]);

// Switch to target user
auth()->login($targetUser);
```

**Middleware for Impersonation Banner:**

```php
// Add to HandleInertiaRequests
'impersonating' => session('impersonate') ? [
    'original' => User::find(session('impersonate.admin_id')),
    'started_at' => session('impersonate.started_at'),
] : null,
```

**Security:**

- Prevent impersonating other admins
- Log all impersonation events
- Auto-expire after 60 minutes
- Require re-authentication for sensitive actions

**Audit Log:**
Create `impersonation_logs` table:

```php
- admin_id
- target_user_id
- started_at
- ended_at
- actions_taken (JSON)
```

---

### 4.2 Email Notifications

**Password Reset:**

- Use Laravel's built-in password reset notification
- Customize email template for admin-triggered resets
- Include note: "An administrator has requested this reset"

**User Suspension:**

- Email to suspended user explaining reason
- Include contact information for appeals
- CC to admin for record

**User Unsuspension:**

- Welcome back email
- Summary of suspension period

---

### 4.3 Export Functionality

**CSV Export for Inquiries:**

```php
// Headers
Email, IP Address, User Agent, Created At

// Stream large datasets
return response()->streamDownload(function () {
    $inquiries = Inquiry::orderBy('created_at', 'desc')->cursor();

    echo "Email,IP Address,User Agent,Created At\n";

    foreach ($inquiries as $inquiry) {
        echo sprintf(
            '"%s","%s","%s","%s"\n',
            $inquiry->email,
            $inquiry->ip_address,
            $inquiry->user_agent,
            $inquiry->created_at->format('Y-m-d H:i:s')
        );
    }
}, 'inquiries-' . now()->format('Y-m-d') . '.csv');
```

---

### 4.4 User Statistics

**Metrics to Display:**

- Total Instagram accounts (owned + shared)
- Total posts created
- Last login date
- Account age (days since registration)
- Companies count
- Storage used (if applicable)

**Implementation:**

```php
// UserService method
public function getUserStats(User $user): array
{
    return [
        'instagram_accounts' => $user->accessibleInstagramAccounts()->count(),
        'posts_count' => $user->instagramPosts()->count(),
        'last_login' => $user->sessions()->latest('last_activity')->first()?->last_activity,
        'account_age_days' => $user->created_at->diffInDays(now()),
        'companies_count' => $user->companies()->count(),
    ];
}
```

---

## 📦 Phase 5: Testing

### 5.1 Feature Tests

**Test Files:**

1. **`tests/Feature/Admin/InquiryManagementTest.php`**

    ```php
    - test_admin_can_view_inquiries()
    - test_non_admin_cannot_access_inquiries()
    - test_admin_can_search_inquiries()
    - test_admin_can_sort_inquiries()
    - test_admin_can_delete_inquiry()
    - test_admin_can_export_inquiries_csv()
    ```

2. **`tests/Feature/Admin/UserManagementTest.php`**

    ```php
    - test_admin_can_view_users()
    - test_non_admin_cannot_access_users()
    - test_admin_can_search_users()
    - test_admin_can_send_password_reset()
    - test_admin_can_suspend_user()
    - test_admin_can_unsuspend_user()
    - test_suspended_user_cannot_login()
    ```

3. **`tests/Feature/Admin/ImpersonationTest.php`**
    ```php
    - test_admin_can_impersonate_user()
    - test_admin_cannot_impersonate_another_admin()
    - test_admin_can_stop_impersonation()
    - test_impersonation_logged_to_audit()
    - test_non_admin_cannot_impersonate()
    ```

---

### 5.2 Unit Tests

**Test Files:**

1. **`tests/Unit/Services/UserManagementServiceTest.php`**
    - Test each service method in isolation
    - Mock dependencies
    - Test edge cases

2. **`tests/Unit/Middleware/EnsureUserIsAdminTest.php`**
    - Test admin access granted
    - Test non-admin access denied
    - Test unauthenticated access

---

## 📦 Phase 6: Documentation

### 6.1 Update Documentation Files

**Files to update:**

1. **`docs/DATABASE_SCHEMA.md`**
    - Add suspended fields to users table
    - Add impersonation_logs table (if implemented)
    - Update version and date

2. **`docs/INDEX.md`**
    - Add link to new admin features documentation

3. **Create:** `docs/ADMIN_FEATURES.md`
    - Complete guide to admin features
    - Screenshots/examples
    - Security considerations
    - Troubleshooting

---

## 🎨 Additional Features to Consider

### Priority 1 (Highly Recommended)

1. **Activity Logging**
    - Track all admin actions
    - Searchable audit log
    - Retention policy

2. **Bulk Actions**
    - Suspend multiple users
    - Delete multiple inquiries
    - Send bulk notifications

3. **Advanced Filters**
    - Filter users by role
    - Filter by suspension status
    - Date range filters
    - Multi-column search

4. **User Details Modal**
    - Click user row for detailed view
    - Activity timeline
    - Related records (posts, accounts)
    - Quick actions

### Priority 2 (Nice to Have)

5. **Dashboard Analytics**
    - Total users by role
    - New inquiries this week
    - User growth chart
    - Active vs suspended users

6. **Email Templates Management**
    - Customize notification emails
    - Preview before sending
    - Multi-language support

7. **Role Management**
    - Change user role from admin panel
    - Batch role updates
    - Role history

8. **Export Options**
    - PDF reports
    - Excel format
    - Scheduled exports

### Priority 3 (Future Enhancements)

9. **Advanced Impersonation**
    - Impersonation time limit
    - Restrict certain actions while impersonating
    - Watermark on pages during impersonation

10. **User Merge**
    - Combine duplicate accounts
    - Transfer ownership of resources

11. **Automated Suspension Rules**
    - Auto-suspend after X failed logins
    - Suspension on reported abuse
    - Scheduled suspension/unsuspension

12. **Communication Log**
    - Track all emails sent to users
    - Resend notifications
    - Email delivery status

---

## 🗓️ Implementation Timeline

### Week 1: Foundation

- ✅ Create middleware
- ✅ Database migration
- ✅ Service layer setup
- ✅ Basic routing

### Week 2: Backend

- ✅ Controllers implementation
- ✅ Service methods
- ✅ Model enhancements
- ✅ Email notifications

### Week 3: Frontend - Inquiries

- ✅ Navigation updates
- ✅ Inquiries page
- ✅ Reusable components
- ✅ Translations

### Week 4: Frontend - Users

- ✅ Users management page
- ✅ Action modals
- ✅ Impersonation UI
- ✅ Testing UI flows

### Week 5: Advanced Features

- ✅ Impersonation backend
- ✅ CSV export
- ✅ User statistics
- ✅ Audit logging

### Week 6: Testing & Polish

- ✅ Feature tests
- ✅ Unit tests
- ✅ Bug fixes
- ✅ Documentation
- ✅ Code review

---

## 🔒 Security Considerations

1. **Authorization**
    - Always check `isAdminInCurrentCompany()`
    - Never trust client-side role checks
    - Validate company context

2. **Impersonation**
    - Prevent admin-to-admin impersonation
    - Log all actions during impersonation
    - Require re-auth for destructive actions
    - Auto-expire sessions

3. **Data Privacy**
    - Mask sensitive user data
    - GDPR compliance for exports
    - Audit trail for data access

4. **Rate Limiting**
    - Limit password reset requests
    - Throttle search queries
    - Prevent brute force on admin routes

---

## 📝 Commit Strategy

Following conventional commits:

```
feat(admin): add admin middleware and route protection
feat(admin): implement inquiry management service
feat(admin): create inquiry management page with search and sort
feat(admin): implement user management service
feat(admin): add user suspension functionality
feat(admin): implement impersonation system
feat(admin): create user management UI with actions
feat(admin): add CSV export for inquiries
test(admin): add feature tests for inquiry management
test(admin): add feature tests for user management
docs(admin): update database schema and create admin features guide
```

---

## ✅ Success Criteria

- [x] Admin-only menu items visible to admins
- [x] Inquiries page with pagination, search, sort
- [x] Users page with comprehensive management
- [x] Password reset link sending works
- [x] User suspension/unsuspension works
- [x] Suspended users cannot log in
- [x] Impersonation works with proper session handling
- [x] All actions have proper authorization checks
- [x] Audit logging implemented
- [x] Email notifications sent appropriately
- [x] Test coverage >80%
- [x] Documentation complete and up-to-date
- [x] Code follows project standards
- [x] No linter errors

---

## 🚀 Getting Started

To begin implementation:

1. Review this plan with team
2. Create feature branch: `feature/admin-panel`
3. Start with Phase 1 (Foundation)
4. Commit frequently with conventional commit messages
5. Update this document as you progress
6. Create PRs for each major phase

---

**Questions? Concerns?**

This is a comprehensive plan. We can implement it in phases or adjust priorities based on your immediate needs. Let me know which features are most critical and we can start there!

---

**Last Updated:** October 10, 2025  
**Version:** 1.0  
**Author:** Cursor AI Assistant
