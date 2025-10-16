# Database Schema Documentation

**Project:** Autopost AI  
**Version:** 1.6
**Date:** October 15, 2025
**Recent Update:** Phase 2 Analysis - Current vs Planned Database Schema

---

## Overview

This document describes the complete database schema for Autopost AI, a multi-company SaaS platform for AI-powered Instagram content generation and scheduling.

### Key Design Decisions

1. **Multi-Tenancy:** Shared database with company-based scoping (no separate databases per tenant)
2. **Wallet System:** Immutable transaction ledger for audit trail and reconciliation
3. **Team Collaboration:** Many-to-many user-company relationship with roles
4. **Instagram Tokens:** Long-lived tokens (60-day) with automatic refresh
5. **Soft Deletes:** Not used initially (can add later if needed)

---

## Entity Relationship Diagram

```
┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│    users    │────────▶│ company_user │◀────────│  companies  │
│             │  many   │   (pivot)    │  many   │             │
│  - id       │         │  - role      │         │  - id       │
│  - name     │         │  - invited_by│         │  - owner_id │
│  - email    │         └──────────────┘         │  - name     │
│  - current_ │                                   │  - stripe_  │
│    company  │                                   │    customer │
└─────────────┘                                   └─────────────┘
                                                         │
                                                         │ has one
                                                         ▼
                                                   ┌─────────────┐
                                                   │   wallets   │
                                                   │             │
                                                   │  - company  │
                                                   │    _id      │
                                                   └─────────────┘
                                                         │
                                                         │ has many
                                                         ▼
                                                   ┌─────────────┐
                                                   │   wallet_   │
                                                   │ transactions│
                                                   │             │
                                                   │  - type     │
                                                   │  - amount   │
                                                   │  - balance_ │
                                                   │    after    │
                                                   └─────────────┘

┌─────────────┐         ┌──────────────┐         ┌─────────────┐
│  companies  │────────▶│  instagram_  │◀────────│    posts    │
│             │  owns   │   accounts   │  uses   │             │
└─────────────┘         │              │         │  - caption  │
                        │  - username  │         │  - status   │
                        │  - token     │         │  - scheduled│
                        │  - expires   │         │    _at      │
                        └──────────────┘         └─────────────┘
                                                         │
                                                         │ has many
                                                         ▼
                                                   ┌─────────────┐
                                                   │    post_    │
                                                   │   assets    │
                                                   │             │
                                                   │  - storage_ │
                                                   │    path     │
                                                   │  - type     │
                                                   └─────────────┘
```

---

## Tables

### Core Tables

#### `users`

**Purpose:** Application users who can belong to multiple companies.

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    current_company_id BIGINT UNSIGNED NULL,
    locale VARCHAR(10) DEFAULT 'en',
    timezone VARCHAR(255) DEFAULT 'UTC',
    suspended_at TIMESTAMP NULL,
    suspended_by BIGINT UNSIGNED NULL,
    suspension_reason TEXT NULL,
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (current_company_id) REFERENCES companies(id) ON DELETE SET NULL,
    INDEX idx_current_company (current_company_id)
);
```

**Relationships:**

- `belongsToMany(Company)` via `company_user` pivot
- `hasOne(Company)` as `currentCompany`
- `hasMany(Post)` as `createdBy`

**Notes:**

- `current_company_id`: Tracks which company user is currently viewing
- `locale`: User's preferred language (en, es, ru) - see [Internationalization](./INTERNATIONALIZATION_PLAN.md)
- `timezone`: User's preferred timezone for displaying dates/times (default: UTC) - see [Timezone Feature](./TIMEZONE_FEATURE.md)
- `suspended_at`: Timestamp when user was suspended (NULL if active)
- `suspended_by`: ID of admin who suspended the user
- `suspension_reason`: Text explanation for why user was suspended
- `last_login_at`: Tracks user's most recent login for admin analytics
- Password can be NULL if OAuth-only user
- Suspended users cannot log in or access the system

---

#### `companies`

**Purpose:** Team/organization container for shared resources (Instagram accounts, posts, wallet).

```sql
CREATE TABLE companies (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    owner_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    stripe_customer_id VARCHAR(255) UNIQUE NULL,
    settings JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_owner (owner_id)
);
```

**Relationships:**

- `belongsTo(User)` as `owner`
- `belongsToMany(User)` via `company_user` pivot
- `hasOne(Wallet)`
- `hasMany(InstagramAccount)`
- `hasMany(Post)`
- `hasMany(AiGeneration)`

**Settings JSON Example:**

```json
{
    "brand_voice": "casual and friendly",
    "target_audience": "millennials interested in fitness",
    "content_themes": ["wellness", "nutrition", "workouts"],
    "brand_colors": ["#FF5733", "#33FF57"],
    "logo_url": "https://...",
    "default_hashtags": ["#fitness", "#wellness"]
}
```

---

#### `company_user` (pivot)

**Purpose:** Many-to-many relationship between users and companies with roles.

```sql
CREATE TABLE company_user (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role ENUM('admin', 'user', 'network') DEFAULT 'user',
    invited_by BIGINT UNSIGNED NULL,
    invited_at TIMESTAMP NULL,
    accepted_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_company_user (company_id, user_id),
    INDEX idx_user (user_id)
);
```

**Roles:**

- `admin`: Full access (invite, billing, delete company)
- `user`: Create/publish posts, connect Instagram
- `network`: Read-only (view posts/analytics)

**Invitation Flow:**

1. Admin invites → `invited_at` set, `accepted_at` NULL
2. User accepts → `accepted_at` set
3. User has access once `accepted_at` is not NULL

---

### Phase 2: Database & Multi-Company Tables

#### `wallets` 🔜 **PLANNED**

**Purpose:** Store wallet metadata for each company with balance tracking.

```sql
CREATE TABLE wallets (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED UNIQUE NOT NULL,
    currency CHAR(3) DEFAULT 'usd',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);
```

**Relationships:**

- `belongsTo(Company)` - Company that owns the wallet
- `hasMany(WalletTransaction)` - All wallet transactions

**Methods:**

```php
// Computed balance from transactions
public function balance(): int
{
    return $this->transactions()
                ->sum(DB::raw("CASE WHEN type = 'credit' THEN amount ELSE -amount END"));
}

// With snapshot for performance
public function balanceFromSnapshot(): int
{
    return $this->transactions()
                ->latest()
                ->value('balance_after') ?? 0;
}
```

---

#### `wallet_transactions` 🔜 **PLANNED**

**Purpose:** Immutable ledger of all wallet changes with balance snapshots.

```sql
CREATE TABLE wallet_transactions (
    id CHAR(26) PRIMARY KEY, -- ULID for sortable uniqueness
    wallet_id BIGINT UNSIGNED NOT NULL,
    type ENUM('credit', 'debit', 'refund', 'adjustment') NOT NULL,
    amount BIGINT UNSIGNED NOT NULL, -- cents, always positive
    balance_after BIGINT NOT NULL, -- snapshot after this transaction
    idempotency_key VARCHAR(255) UNIQUE NOT NULL,
    stripe_payment_intent_id VARCHAR(255) NULL,
    description VARCHAR(255) NOT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (wallet_id) REFERENCES wallets(id) ON DELETE CASCADE,
    INDEX idx_wallet_date (wallet_id, created_at),
    INDEX idx_idempotency (idempotency_key)
);
```

**Transaction Types:**

- `credit`: Add money (Stripe top-up)
- `debit`: Spend money (AI generation)
- `refund`: Return money (failed generation, Stripe refund)
- `adjustment`: Manual correction (admin only)

**Key Fields:**

- `amount`: Always positive integer in cents
- `balance_after`: Snapshot for quick reconciliation
- `idempotency_key`: Prevents duplicate webhooks
- `stripe_payment_intent_id`: Links to Stripe transaction

---

#### `ai_generations` 🔜 **PLANNED**

**Purpose:** Track all AI-generated content and associated costs.

```sql
CREATE TABLE ai_generations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('caption', 'image', 'video') NOT NULL,
    prompt TEXT NOT NULL,
    result TEXT NULL, -- caption text or S3 URL
    cost_credits INT UNSIGNED NOT NULL, -- in cents
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_company_type (company_id, type),
    INDEX idx_created (created_at)
);
```

**Relationships:**

- `belongsTo(Company)` - Company that generated content
- `belongsTo(User)` - User who initiated generation

**Generation Types:**

- `caption`: AI-generated post captions
- `image`: AI-generated images
- `video`: AI-generated videos

---

#### `questionnaire_responses` 🔜 **PLANNED**

**Purpose:** Store onboarding questionnaire answers for brand setup.

```sql
CREATE TABLE questionnaire_responses (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    responses JSON NOT NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_company (company_id)
);
```

**Relationships:**

- `belongsTo(Company)` - Company being onboarded
- `belongsTo(User)` - User completing questionnaire

---

#### `content_plans` 🔜 **PLANNED**

**Purpose:** Long-term content planning and theme management.

```sql
CREATE TABLE content_plans (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    themes JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company (company_id),
    INDEX idx_dates (start_date, end_date)
);
```

**Relationships:**

- `belongsTo(Company)` - Company that owns the plan

**Themes JSON Example:**

```json
{
    "brand_voice": "professional",
    "content_types": ["educational", "inspirational"],
    "posting_frequency": "daily",
    "target_audience": "professionals"
}
```

---

### Enhanced Company Management

#### `company_user` Table Updates 🔜 **PLANNED**

**Purpose:** Add invitation tracking to existing company-user relationships.

```sql
-- Add invitation fields to existing company_user table
ALTER TABLE company_user ADD COLUMN invited_by BIGINT UNSIGNED NULL;
ALTER TABLE company_user ADD COLUMN invited_at TIMESTAMP NULL;
ALTER TABLE company_user ADD COLUMN accepted_at TIMESTAMP NULL;

-- Add foreign key constraint
ALTER TABLE company_user ADD FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL;

-- Add indexes for performance
ALTER TABLE company_user ADD INDEX idx_invited_by (invited_by);
ALTER TABLE company_user ADD INDEX idx_invitation_status (invited_at, accepted_at);
```

**Invitation Flow:**

1. Admin invites → `invited_at` set, `accepted_at` NULL
2. User accepts → `accepted_at` set
3. User has access once `accepted_at` is not NULL

---

"type": "credit",
"amount": 5000,
"balance_after": 5000,
"idempotency_key": "pi_3abc123_succeeded",
"stripe_payment_intent_id": "pi_3abc123",
"description": "Wallet top-up via Stripe",
"metadata": {
"payment_method": "card",
"last4": "4242"
}
}

// Debit (AI generation)
{
"type": "debit",
"amount": 150,
"balance_after": 4850,
"idempotency_key": "ai_gen_456",
"description": "Image generation",
"metadata": {
"ai_generation_id": 456,
"type": "image",
"model": "stable-diffusion-3"
}
}

````

---

### Instagram Tables

#### `instagram_accounts` ⚠️ **UPDATED - Hybrid Ownership Model**

**Purpose:** Connected Instagram accounts with hybrid ownership (user OR company).

```sql
CREATE TABLE instagram_accounts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NULL, -- Now nullable for user-owned accounts
    user_id BIGINT UNSIGNED NULL, -- NEW: For user-owned accounts
    instagram_user_id VARCHAR(255) UNIQUE NOT NULL,
    username VARCHAR(255) NOT NULL,
    access_token TEXT NOT NULL, -- encrypted
    token_expires_at TIMESTAMP NOT NULL,
    account_type VARCHAR(50) DEFAULT 'personal', -- personal, business
    profile_picture_url TEXT NULL,
    followers_count INT UNSIGNED DEFAULT 0,
    status VARCHAR(50) DEFAULT 'active', -- active, expired, error, disconnected
    last_synced_at TIMESTAMP NULL,
    is_shared BOOLEAN DEFAULT FALSE, -- NEW: If shared with other users
    ownership_type ENUM('user', 'company') DEFAULT 'company', -- NEW: Explicit ownership
    metadata JSON NULL, -- NEW: Additional Instagram data
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, -- NEW
    INDEX idx_company (company_id),
    INDEX idx_user (user_id), -- NEW
    INDEX idx_token_expiry (token_expires_at),
    INDEX idx_user_status (user_id, status), -- NEW
    INDEX idx_company_status (company_id, status), -- NEW
    INDEX idx_ownership_type (ownership_type) -- NEW
);
````

**Relationships:**

- `belongsTo(Company)` - For company-owned accounts
- `belongsTo(User)` as `owner` - For user-owned accounts
- `belongsToMany(User)` via `instagram_account_user` - Shared access
- `hasMany(InstagramPost)` - Posts made to this account

**Ownership Types:**

- `user`: Personal account owned by a user
- `company`: Team account owned by a company

**Token Management:**

- Initial token: 60-day expiry
- Refresh before expiry: Extends by another 60 days
- Job checks daily: `RefreshInstagramTokenJob`
- Alert if <7 days until expiry

**Encrypted Fields:**

```php
protected $casts = [
    'access_token' => 'encrypted',
    'token_expires_at' => 'datetime',
    'last_synced_at' => 'datetime',
    'metadata' => 'array',
    'is_shared' => 'boolean',
];
```

**See Also:** [INSTAGRAM_HYBRID_OWNERSHIP.md](./INSTAGRAM_HYBRID_OWNERSHIP.md) for complete implementation guide

---

#### `instagram_account_user` (pivot) ✨ **NEW**

**Purpose:** Manages sharing permissions for Instagram accounts.

```sql
CREATE TABLE instagram_account_user (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    instagram_account_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    can_post BOOLEAN DEFAULT TRUE,
    can_manage BOOLEAN DEFAULT FALSE,
    shared_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    shared_by_user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (instagram_account_id) REFERENCES instagram_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (shared_by_user_id) REFERENCES users(id) ON DELETE SET NULL,
    UNIQUE KEY unique_account_user (instagram_account_id, user_id),
    INDEX idx_user (user_id),
    INDEX idx_user_post (user_id, can_post),
    INDEX idx_account_manage (instagram_account_id, can_manage)
);
```

**Permission Flags:**

- `can_post`: User can create and publish posts to this account
- `can_manage`: User can modify account settings, reconnect, disconnect

**Audit Fields:**

- `shared_at`: When access was granted
- `shared_by_user_id`: Who shared the account

---

#### `instagram_posts` ✨ **NEW**

**Purpose:** Complete Instagram post lifecycle management.

```sql
CREATE TABLE instagram_posts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    instagram_account_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    caption TEXT NULL,
    media_type VARCHAR(50) DEFAULT 'image', -- image, video, carousel
    media_urls JSON NULL, -- Array of media file paths
    instagram_post_id VARCHAR(255) UNIQUE NULL,
    instagram_permalink VARCHAR(255) NULL,
    scheduled_at TIMESTAMP NULL,
    published_at TIMESTAMP NULL,
    status ENUM('draft', 'scheduled', 'publishing', 'published', 'failed', 'cancelled') DEFAULT 'draft',
    error_message TEXT NULL,
    retry_count INTEGER DEFAULT 0,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL, -- Soft deletes for audit trail

    FOREIGN KEY (instagram_account_id) REFERENCES instagram_accounts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_account_status (instagram_account_id, status),
    INDEX idx_user_status (user_id, status),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_published (published_at),
    INDEX idx_status (status)
);
```

**Status Flow:**

```
draft → scheduled → publishing → published
                        ↓
                     failed (can retry up to 3 times)
```

**Relationships:**

- `belongsTo(InstagramAccount)`
- `belongsTo(User)` as `creator`

**See Also:** [INSTAGRAM_HYBRID_OWNERSHIP.md](./INSTAGRAM_HYBRID_OWNERSHIP.md) for post management guide

---

### Content Tables

#### `posts`

**Purpose:** Instagram posts (feed, reels, stories, carousels).

```sql
CREATE TABLE posts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    instagram_account_id BIGINT UNSIGNED NOT NULL,
    type ENUM('feed', 'reel', 'story', 'carousel') NOT NULL,
    caption TEXT NULL,
    scheduled_at TIMESTAMP NULL,
    published_at TIMESTAMP NULL,
    status ENUM('draft', 'scheduled', 'publishing', 'published', 'failed') DEFAULT 'draft',
    instagram_media_id VARCHAR(255) NULL,
    metadata JSON NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (instagram_account_id) REFERENCES instagram_accounts(id) ON DELETE CASCADE,
    INDEX idx_company_status (company_id, status),
    INDEX idx_scheduled (scheduled_at)
);
```

**Relationships:**

- `belongsTo(Company)`
- `belongsTo(User)` as `creator`
- `belongsTo(InstagramAccount)`
- `hasMany(PostAsset)`

**Status Flow:**

```
draft → scheduled → publishing → published
                        ↓
                     failed (can retry)
```

**Metadata JSON Example:**

```json
{
    "hashtags": ["#fitness", "#wellness"],
    "mentions": ["@brandname"],
    "location": {
        "id": "123456",
        "name": "New York, NY"
    },
    "cover_frame_offset": 0 // for videos
}
```

---

#### `post_assets`

**Purpose:** Media files (images/videos) for posts.

```sql
CREATE TABLE post_assets (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT UNSIGNED NOT NULL,
    type ENUM('image', 'video') NOT NULL,
    storage_path VARCHAR(255) NOT NULL, -- S3 key
    url TEXT NOT NULL, -- signed or public URL
    `order` TINYINT UNSIGNED DEFAULT 0, -- for carousels
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    INDEX idx_post (post_id)
);
```

**Relationships:**

- `belongsTo(Post)`

**Metadata JSON Example:**

```json
{
    "width": 1080,
    "height": 1080,
    "size_bytes": 245678,
    "duration_seconds": 15, // for videos
    "mime_type": "video/mp4",
    "ai_generated": true,
    "ai_generation_id": 789
}
```

---

### AI & Onboarding Tables

#### `ai_generations`

**Purpose:** Track all AI-generated content and costs.

```sql
CREATE TABLE ai_generations (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    type ENUM('caption', 'image', 'video') NOT NULL,
    prompt TEXT NOT NULL,
    result TEXT NULL, -- caption text or S3 URL
    cost_credits INT UNSIGNED NOT NULL, -- in cents
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_company_type (company_id, type),
    INDEX idx_created (created_at)
);
```

**Relationships:**

- `belongsTo(Company)`
- `belongsTo(User)`

**Metadata JSON Example:**

```json
{
    "model": "gpt-4o-mini",
    "max_tokens": 150,
    "temperature": 0.7,
    "response_time_ms": 1234,
    "ai_service": "openai"
}
```

---

#### `questionnaire_responses`

**Purpose:** Onboarding questionnaire answers for brand setup.

```sql
CREATE TABLE questionnaire_responses (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    responses JSON NOT NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_company (company_id)
);
```

**Relationships:**

- `belongsTo(Company)`
- `belongsTo(User)`

**Responses JSON Example:**

```json
{
    "brand_name": "FitLife Co",
    "industry": "health_fitness",
    "target_audience": "millennials interested in wellness",
    "brand_voice": "casual_friendly",
    "content_goals": ["engagement", "brand_awareness"],
    "posting_frequency": "3_per_week",
    "preferred_times": ["09:00", "15:00", "20:00"]
}
```

---

#### `content_plans`

**Purpose:** Long-term content planning and themes.

```sql
CREATE TABLE content_plans (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    company_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    themes JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    INDEX idx_company (company_id),
    INDEX idx_dates (start_date, end_date)
);
```

**Relationships:**

- `belongsTo(Company)`

**Themes JSON Example:**

```json
{
    "weekly_themes": [
        {
            "week": 1,
            "theme": "Monday Motivation",
            "topics": ["goal setting", "mindset"]
        },
        {
            "week": 2,
            "theme": "Workout Wednesday",
            "topics": ["exercises", "fitness tips"]
        }
    ]
}
```

---

### Post Management Tables

#### `posts` ✨ **NEW**

**Purpose:** Complete post management system with media support and scheduling.

```sql
CREATE TABLE posts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    company_id BIGINT UNSIGNED NOT NULL,
    instagram_account_id BIGINT UNSIGNED NOT NULL,
    type ENUM('feed', 'reel', 'story', 'carousel') NOT NULL,
    status ENUM('draft', 'scheduled', 'publishing', 'published', 'failed') NOT NULL DEFAULT 'draft',
    title VARCHAR(255) NULL,
    caption TEXT NULL,
    scheduled_at TIMESTAMP NULL,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (instagram_account_id) REFERENCES instagram_accounts(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_company (company_id),
    INDEX idx_instagram_account (instagram_account_id),
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_scheduled_at (scheduled_at),
    INDEX idx_published_at (published_at),
    INDEX idx_user_status (user_id, status),
    INDEX idx_company_status (company_id, status)
);
```

**Relationships:**

- `belongsTo(User)` - Post creator
- `belongsTo(Company)` - Company context
- `belongsTo(InstagramAccount)` - Target Instagram account
- `hasMany(PostMedia)` - Associated media files

**Post Types:**

- `feed` - Standard Instagram feed posts
- `reel` - Short-form video content
- `story` - 24-hour temporary content
- `carousel` - Multiple images/videos in one post

**Post Statuses:**

- `draft` - Work in progress, not scheduled
- `scheduled` - Scheduled for future publishing
- `publishing` - Currently being sent to Instagram API
- `published` - Successfully published to Instagram
- `failed` - Publishing failed, can be retried

---

#### `post_media` ✨ **NEW**

**Purpose:** Media file management for posts with metadata and ordering.

```sql
CREATE TABLE post_media (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    post_id BIGINT UNSIGNED NOT NULL,
    type ENUM('image', 'video') NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size BIGINT NOT NULL,
    storage_path VARCHAR(500) NOT NULL,
    url VARCHAR(500) NULL,
    order INTEGER DEFAULT 0,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    INDEX idx_post (post_id),
    INDEX idx_type (type),
    INDEX idx_post_order (post_id, order)
);
```

**Relationships:**

- `belongsTo(Post)` - Parent post
- `morphToMany()` - Media relationships

**Metadata JSON Example:**

```json
{
    "dimensions": {
        "width": 1080,
        "height": 1080
    },
    "mime_type": "image/jpeg",
    "duration": null,
    "thumbnail": "posts/123/thumbnails/300x300_image.jpg"
}
```

**File Storage:**

- **Public Storage:** `/storage/posts/{post_id}/{filename}` - Web-accessible
- **Private Storage:** `/storage/app/private/posts/{post_id}/{filename}` - Secure
- **Serving:** Private files served via `/media/{path}` route with authentication

---

## Indexes

### Performance-Critical Indexes

**Frequently Queried:**

```sql
-- Posts by company and status (dashboard, list views)
CREATE INDEX idx_posts_company_status ON posts(company_id, status);

-- Posts scheduled for publishing (job query)
CREATE INDEX idx_posts_scheduled ON posts(scheduled_at)
WHERE status = 'scheduled';

-- Wallet transactions for ledger calculation
CREATE INDEX idx_wallet_transactions_calc ON wallet_transactions(wallet_id, created_at);

-- Instagram token refresh (job query)
CREATE INDEX idx_instagram_token_expiry ON instagram_accounts(token_expires_at)
WHERE token_expires_at < DATE_ADD(NOW(), INTERVAL 7 DAY);

-- Company users for authorization
CREATE INDEX idx_company_user_lookup ON company_user(user_id, company_id);
```

**Unique Constraints:**

```sql
-- Prevent duplicate company-user relationships
ALTER TABLE company_user ADD UNIQUE KEY unique_company_user (company_id, user_id);

-- Prevent duplicate Instagram accounts
ALTER TABLE instagram_accounts ADD UNIQUE KEY unique_instagram_user (instagram_user_id);

-- Prevent duplicate wallet transactions (idempotency)
ALTER TABLE wallet_transactions ADD UNIQUE KEY unique_idempotency (idempotency_key);
```

---

## Data Integrity Rules

### Cascade Behaviors

**ON DELETE CASCADE:**

- Company deleted → Delete all posts, Instagram accounts, wallet
- Post deleted → Delete all assets
- User leaves company → Remove from `company_user`

**ON DELETE SET NULL:**

- User deleted → Posts keep `created_by` as NULL (audit trail)
- Company owner deleted → Assign to another admin (business logic)

### Immutable Fields

**Never Update After Insert:**

- `wallet_transactions.created_at`
- `wallet_transactions.amount`
- `wallet_transactions.type`

**Only Append:**

- Wallet transactions (never delete)
- AI generations (audit trail)

---

## Sample Queries

### Get Current Wallet Balance

```sql
SELECT
    SUM(CASE
        WHEN type IN ('credit', 'refund') THEN amount
        WHEN type IN ('debit', 'adjustment') THEN -amount
    END) as balance
FROM wallet_transactions
WHERE wallet_id = ?;
```

**Optimized with Snapshot:**

```sql
SELECT balance_after
FROM wallet_transactions
WHERE wallet_id = ?
ORDER BY created_at DESC
LIMIT 1;
```

---

### Get Scheduled Posts Due for Publishing

```sql
SELECT *
FROM posts
WHERE status = 'scheduled'
  AND scheduled_at <= NOW()
  AND scheduled_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
ORDER BY scheduled_at ASC;
```

---

### Get Instagram Accounts Needing Token Refresh

```sql
SELECT *
FROM instagram_accounts
WHERE token_expires_at < DATE_ADD(NOW(), INTERVAL 7 DAY)
  AND token_expires_at > NOW()
ORDER BY token_expires_at ASC;
```

---

### Get Company Members with Roles

```sql
SELECT
    u.id, u.name, u.email,
    cu.role, cu.accepted_at
FROM users u
JOIN company_user cu ON u.id = cu.user_id
WHERE cu.company_id = ?
  AND cu.accepted_at IS NOT NULL
ORDER BY
    CASE cu.role
        WHEN 'admin' THEN 1
        WHEN 'user' THEN 2
        WHEN 'network' THEN 3
    END,
    u.name;
```

---

## Migration Order

**Critical:** Migrations must run in dependency order.

```
1. users
2. companies
3. company_user
4. wallets
5. wallet_transactions
6. instagram_accounts
7. posts
8. post_assets
9. questionnaire_responses
10. content_plans
11. ai_generations
```

---

## Seeding Strategy

### Development Seeders

```php
DatabaseSeeder::class
├── UserSeeder (5 users)
├── CompanySeeder (3 companies)
├── CompanyUserSeeder (assign users to companies with roles)
├── WalletSeeder (create wallet for each company)
├── WalletTransactionSeeder (seed some transactions)
├── InstagramAccountSeeder (2-3 accounts)
└── PostSeeder (10-20 posts in various statuses)
```

**Example:**

```bash
php artisan db:seed
# or
php artisan migrate:fresh --seed
```

---

## Backup & Recovery

### Critical Tables (Priority 1)

**Must backup frequently:**

- `wallet_transactions` (immutable, financial)
- `companies`, `users`, `company_user`
- `posts`, `post_assets`

### Reference Data (Priority 2)

**Backup daily:**

- `instagram_accounts` (contains tokens)
- `ai_generations` (cost tracking)
- `questionnaire_responses`

### Regenerable Data (Priority 3)

**Can recreate from external sources:**

- Cache tables
- Session tables
- Job tables

---

## Database Maintenance

### Weekly Tasks

```sql
-- Reconcile wallet balances
SELECT wallet_id,
       COUNT(*) as transaction_count,
       MAX(balance_after) as snapshot_balance,
       SUM(CASE
           WHEN type IN ('credit', 'refund') THEN amount
           WHEN type IN ('debit', 'adjustment') THEN -amount
       END) as computed_balance
FROM wallet_transactions
GROUP BY wallet_id
HAVING snapshot_balance != computed_balance;

-- Find orphaned assets
SELECT * FROM post_assets
WHERE post_id NOT IN (SELECT id FROM posts);

-- Clean old failed jobs (>30 days)
DELETE FROM failed_jobs
WHERE failed_at < DATE_SUB(NOW(), INTERVAL 30 DAY);
```

---

## Schema Versioning

**Current Version:** 1.1  
**Last Updated:** October 10, 2025

**Change Log:**

- `v1.1` - **Instagram Hybrid Ownership Model**
    - Modified `instagram_accounts` table (added `user_id`, `is_shared`, `ownership_type`)
    - Added `instagram_account_user` pivot table for sharing
    - Added `instagram_posts` table for post management
    - See: [INSTAGRAM_HYBRID_OWNERSHIP.md](./INSTAGRAM_HYBRID_OWNERSHIP.md)
- `v1.0` - Initial schema design

- Future: Add analytics tables, notification preferences, audit logs

---

**End of Schema Documentation**
