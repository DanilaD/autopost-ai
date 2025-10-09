# Autopost AI - Project Implementation Plan

**Version:** 1.0  
**Date:** October 9, 2025  
**Status:** Planning Phase

---

## Table of Contents

1. [Project Overview](#project-overview)
2. [Tech Stack](#tech-stack)
3. [Architecture Decisions](#architecture-decisions)
4. [Database Schema](#database-schema)
5. [Implementation Phases](#implementation-phases)
    - [Phase 0: Authentication Foundation](#phase-0-authentication-foundation-week-0-1) ← **CURRENT**
    - [Phase 1: Foundation & Core Stack](#phase-1-foundation--core-stack-week-1-2)
    - [Phase 2-8: See full plan](#phase-2-database--multi-company-week-2-3)
6. [API Integrations](#api-integrations)
7. [Development Workflow](#development-workflow)
8. [Security & Compliance](#security--compliance)
9. [Testing Strategy](#testing-strategy)
10. [Deployment & DevOps](#deployment--devops)

## Related Documentation

- **[INDEX.md](./INDEX.md)** - Complete documentation index
- **[DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md)** - Detailed database design
- **[AUTH_FLOW_PLAN.md](./AUTH_FLOW_PLAN.md)** - Phase 0: Authentication system
- **[INTERNATIONALIZATION_PLAN.md](./INTERNATIONALIZATION_PLAN.md)** - Phase 0: Multi-language support

---

## Project Overview

### Vision

AI-powered social media automation platform that enables users to generate, schedule, and publish Instagram content using artificial intelligence for captions, images, and videos.

### Core Features

- **AI Content Generation**: Captions, hashtags, images, and videos
- **Instagram Publishing**: Posts, Reels, Stories, Carousels
- **Team Collaboration**: Multi-user companies with role-based access
- **Wallet System**: Pay-as-you-go credits for AI generation
- **Smart Scheduling**: Calendar-based content planning

### Target Users

- Social media managers
- Content creators
- Marketing agencies
- Small businesses

---

## Tech Stack

### Backend (Laravel Ecosystem)

#### Already Installed

- **Laravel 12** - Core framework
- **Inertia.js 2** - Modern monolith (SPA without API)
- **Laravel Sanctum 4** - API authentication
- **Laravel Cashier 16** - Stripe payment integration
- **Laravel Socialite 5** - OAuth providers
- **Laravel Breeze 2** - Authentication scaffolding
- **Spatie Webhooks** - Client & server for webhooks
- **Ziggy 2** - Laravel routes in JavaScript
- **Pest 3** - Testing framework
- **Laravel Sail** - Docker development environment
- **Laravel Pail** - Real-time log viewer
- **Laravel Pint** - Code style fixer

#### To Be Installed

```json
{
    "predis/predis": "^2.0", // Redis client
    "laravel/horizon": "^5.0", // Queue monitoring UI
    "league/flysystem-aws-s3-v3": "^3.0", // S3 storage driver
    "facebook/graph-sdk": "^6.0", // Instagram Graph API
    "spatie/laravel-permission": "^6.0" // Role & permission management (optional)
}
```

### Frontend (Vue Ecosystem)

#### Already Installed

- **Vue 3.4** - Progressive JavaScript framework
- **Tailwind CSS 3** - Utility-first CSS
- **Vite 7** - Build tool with HMR
- **Axios 1.11** - HTTP client

#### To Be Installed

```json
{
    "@fullcalendar/vue3": "^6.1.0", // Calendar component
    "@headlessui/vue": "^1.7.0", // Unstyled UI components
    "@heroicons/vue": "^2.0.0", // Icon library
    "vue-toastification": "^2.0.0", // Toast notifications
    "@vueuse/core": "^10.0.0" // Vue composition utilities
}
```

### AI Services (Python Sidecar)

**Framework:** FastAPI  
**Location:** Separate microservice (future: `/ai-service` directory)

**APIs:**

- **OpenAI Chat Completions** - Caption & hashtag generation
- **Stability AI REST API** - Image generation
- **Luma Dream Machine** - Video generation

### Infrastructure

**Development:**

- **Local:** Laravel Sail (Docker) or Valet
- **Database:** SQLite (dev), PostgreSQL (production)
- **Queue:** Redis + Horizon
- **Storage:** Local (dev), S3 (production)

**Production:**

- **Hosting:** TBD (Laravel Forge, AWS, Vercel, etc.)
- **Database:** PostgreSQL (RDS or managed)
- **Queue:** Redis (ElastiCache or managed)
- **Storage:** AWS S3
- **CDN:** CloudFront (for S3 assets)

---

## Architecture Decisions

### 1. Multi-Tenancy Approach: **Company Ownership Model**

**Decision:** Use shared database with company-based access control (NO full multi-tenancy).

**Rationale:**

- ✅ Simpler than separate databases per tenant
- ✅ Easier wallet reconciliation and shared resources
- ✅ Standard approach for SaaS (Spark, Jetstream pattern)
- ✅ Can scale to thousands of companies
- ❌ Full isolation not needed for our use case

**Implementation:**

```php
// All queries scoped by company
Post::where('company_id', auth()->user()->currentCompany->id)->get();

// Middleware enforces company context
EnsureUserHasCompanyAccess::class
```

### 2. User-Company Relationship: **Many-to-Many**

**Decision:** Users can belong to multiple companies and switch between them.

**Implementation:**

- `company_user` pivot table with roles
- `current_company_id` on users table for context
- Company switcher in UI navigation

### 3. Role-Based Access Control

**Roles:**

| Role        | Permissions                                                                                        |
| ----------- | -------------------------------------------------------------------------------------------------- |
| **Admin**   | Full access: invite users, manage billing, connect Instagram, create/publish posts, delete company |
| **User**    | Create/edit/publish posts, connect Instagram (if admin allows), view analytics                     |
| **Network** | Read-only: view posts and analytics, can't publish or edit                                         |

**Implementation:** Enum on `company_user.role` pivot table (simple RBAC without Spatie package for MVP).

### 4. Wallet Ownership: **Company-Based Billing**

**Decision:** Each company has its own wallet (team pays, not individuals).

**Rationale:**

- ✅ Simpler billing model for teams
- ✅ Company admin manages budget
- ✅ Individual users don't need payment methods
- ✅ Standard for B2B SaaS

### 5. Instagram Account Ownership: **Company-Owned**

**Decision:** Instagram accounts belong to companies (shared by team).

**Rationale:**

- ✅ Team collaboration on same account
- ✅ Multiple users can create content for same Instagram
- ✅ Follows agency/team workflow

### 6. Wallet Ledger: **Immutable Transaction Log**

**Decision:** All wallet changes recorded as immutable transactions, balance computed from ledger.

**Pattern:**

```php
wallet_transactions
  - amount (always positive integer, cents)
  - type (credit|debit|refund|adjustment)
  - balance_after (snapshot for quick reconciliation)
  - idempotency_key (prevent duplicate webhooks)
  - created_at (immutable, never updated)
```

---

## Database Schema

### Core Tables

#### users

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->timestamp('email_verified_at')->nullable();
    $table->string('password');
    $table->foreignId('current_company_id')->nullable()
          ->constrained('companies')->nullOnDelete();
    $table->string('timezone')->default('UTC');
    $table->rememberToken();
    $table->timestamps();
});
```

#### companies

```php
Schema::create('companies', function (Blueprint $table) {
    $table->id();
    $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('stripe_customer_id')->nullable()->unique();
    $table->json('settings')->nullable(); // brand voice, guidelines, etc.
    $table->timestamps();

    $table->index('owner_id');
});
```

#### company_user (pivot)

```php
Schema::create('company_user', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('role', ['admin', 'user', 'network'])->default('user');
    $table->foreignId('invited_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('invited_at')->nullable();
    $table->timestamp('accepted_at')->nullable();
    $table->timestamps();

    $table->unique(['company_id', 'user_id']);
    $table->index('user_id');
});
```

#### wallets

```php
Schema::create('wallets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->string('currency', 3)->default('usd');
    $table->timestamps();

    $table->unique('company_id');
});
```

#### wallet_transactions

```php
Schema::create('wallet_transactions', function (Blueprint $table) {
    $table->ulid('id')->primary(); // Sortable unique ID
    $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['credit', 'debit', 'refund', 'adjustment']);
    $table->unsignedBigInteger('amount'); // cents, always positive
    $table->bigInteger('balance_after'); // snapshot
    $table->string('idempotency_key')->unique();
    $table->string('stripe_payment_intent_id')->nullable();
    $table->string('description');
    $table->json('metadata')->nullable();
    $table->timestamp('created_at')->useCurrent(); // immutable

    $table->index(['wallet_id', 'created_at']);
    $table->index('idempotency_key');
});
```

#### instagram_accounts

```php
Schema::create('instagram_accounts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->string('instagram_user_id')->unique();
    $table->string('username');
    $table->text('access_token'); // encrypted
    $table->string('token_type')->default('long_lived');
    $table->timestamp('token_expires_at');
    $table->string('profile_picture_url')->nullable();
    $table->unsignedInteger('followers_count')->default(0);
    $table->timestamp('last_synced_at')->nullable();
    $table->timestamps();

    $table->index('company_id');
    $table->index('token_expires_at'); // for refresh job
});
```

#### posts

```php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('created_by')->constrained('users')->nullOnDelete();
    $table->foreignId('instagram_account_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['feed', 'reel', 'story', 'carousel']);
    $table->text('caption')->nullable();
    $table->timestamp('scheduled_at')->nullable();
    $table->timestamp('published_at')->nullable();
    $table->enum('status', [
        'draft', 'scheduled', 'publishing', 'published', 'failed'
    ])->default('draft');
    $table->string('instagram_media_id')->nullable(); // from Graph API
    $table->json('metadata')->nullable(); // hashtags, mentions, location
    $table->text('error_message')->nullable();
    $table->timestamps();

    $table->index(['company_id', 'status']);
    $table->index('scheduled_at');
});
```

#### post_assets

```php
Schema::create('post_assets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('post_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['image', 'video']);
    $table->string('storage_path'); // S3 key
    $table->string('url'); // signed or public URL
    $table->unsignedTinyInteger('order')->default(0);
    $table->json('metadata')->nullable(); // dimensions, duration, size
    $table->timestamps();

    $table->index('post_id');
});
```

#### questionnaire_responses

```php
Schema::create('questionnaire_responses', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->json('responses'); // all onboarding answers
    $table->timestamp('completed_at')->nullable();
    $table->timestamps();

    $table->index('company_id');
});
```

#### content_plans

```php
Schema::create('content_plans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->string('name');
    $table->text('description')->nullable();
    $table->date('start_date');
    $table->date('end_date');
    $table->json('themes')->nullable(); // weekly themes, etc.
    $table->timestamps();

    $table->index('company_id');
});
```

#### ai_generations

```php
Schema::create('ai_generations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('type', ['caption', 'image', 'video']);
    $table->text('prompt');
    $table->text('result')->nullable(); // text or S3 URL
    $table->unsignedInteger('cost_credits'); // how many credits used
    $table->json('metadata')->nullable(); // model, settings, etc.
    $table->timestamps();

    $table->index(['company_id', 'type']);
});
```

---

## Implementation Phases

### Phase 0: Authentication Foundation (Week 0-1)

**Goal:** Implement modern authentication system before building core features.

**See detailed plan:** [AUTH_FLOW_PLAN.md](./AUTH_FLOW_PLAN.md)

#### Tasks:

1. **Create `inquiries` table**
    - Track email submissions from non-existent users
    - Log IP address, user agent, referrer
    - Marketing intelligence and security monitoring

2. **Email-first authentication entry**
    - Single email input on landing page
    - Check if user exists via AJAX
    - Route to login or registration accordingly

3. **Magic link authentication (passwordless)**
    - Install package or implement custom signed URLs
    - Send magic link via email
    - 1-hour expiration, one-time use
    - Rate limiting: 3 requests per 2 minutes

4. **Traditional password authentication**
    - Show password option for existing users
    - "Forgot password" flow (already in Breeze)
    - Login tracking (last_login_at, last_login_ip)

5. **Email verification**
    - Required on registration
    - Resend verification link
    - Block unverified users from dashboard

6. **Inquiry logging system**
    - Only log emails that don't exist in users table
    - Service layer: `InquiryService`
    - Cleanup job: Remove inquiries >90 days old

7. **Multi-language support**
    - 3 languages: English, Russian, Spanish
    - URL path prefix: `/en/`, `/ru/`, `/es/`
    - Language detection: URL → User → Session → Browser
    - User preference storage in database
    - Vue + Laravel translation integration
    - Language switcher component

**See detailed plans:**

- **Authentication:** [AUTH_FLOW_PLAN.md](./AUTH_FLOW_PLAN.md)
- **Internationalization:** [INTERNATIONALIZATION_PLAN.md](./INTERNATIONALIZATION_PLAN.md)

**Deliverables:**

- ✅ Modern landing page with email-first entry
- ✅ Magic link authentication working
- ✅ Password authentication working
- ✅ Email verification enforced
- ✅ Inquiry tracking with IP logging
- ✅ Multi-language support (EN, RU, ES)
- ✅ Language switcher with flags
- ✅ User locale preferences saved
- ✅ Beautiful UI with smooth transitions
- ✅ Comprehensive tests

---

### Phase 1: Foundation & Core Stack (Week 1-2)

**Goal:** Set up infrastructure and authentication.

#### Tasks:

1. **Install missing dependencies**

    ```bash
    composer require predis/predis laravel/horizon facebook/graph-sdk
    composer require league/flysystem-aws-s3-v3
    npm install @fullcalendar/vue3 @headlessui/vue @heroicons/vue vue-toastification
    ```

2. **Configure Redis + Horizon**
    - Update `.env` for Redis connection
    - Publish Horizon config: `php artisan horizon:install`
    - Configure queue connection in `config/queue.php`

3. **Configure S3 Storage**
    - Add AWS credentials to `.env`
    - Update `config/filesystems.php` with S3 disk
    - Create S3 bucket with proper CORS policy

4. **Social OAuth Setup**
    - Configure Google OAuth in `config/services.php`
    - Add OAuth routes and controllers
    - Update Breeze auth flow for social login

5. **Create `.env.example` Template**
    - Document all required environment variables
    - Include API keys for: Stripe, AWS, Instagram, OpenAI, Stability, Luma

**Deliverables:**

- ✅ Redis + Horizon running locally
- ✅ S3 storage configured and tested
- ✅ Google OAuth working
- ✅ Complete `.env.example` file

---

### Phase 2: Database & Multi-Company (Week 2-3)

**Goal:** Implement complete database schema and company management.

#### Tasks:

1. **Create all migrations** (listed in Database Schema section)
2. **Create Eloquent models** with relationships:
    - `Company`, `User`, `Wallet`, `WalletTransaction`
    - `InstagramAccount`, `Post`, `PostAsset`
    - `QuestionnaireResponse`, `ContentPlan`, `AiGeneration`

3. **Company management features**
    - Create company flow
    - Switch company UI component
    - Invite user flow (email + acceptance)
    - Role-based middleware: `EnsureUserHasCompanyAccess`

4. **Global company scope**
    - Create `BelongsToCompany` trait for models
    - Add global scope: `static::addGlobalScope('company', fn($query) => ...)`

5. **Seeders & factories**
    - Factory for each model
    - Seeder for development data

**Deliverables:**

- ✅ All migrations run successfully
- ✅ Company CRUD operations
- ✅ User invitation system
- ✅ Company switcher in UI

---

### Phase 3: Wallet & Stripe Integration (Week 3-4)

**Goal:** Implement wallet system with Stripe top-ups.

#### Tasks:

1. **Wallet Service Layer** (`app/Services/Wallet/`)
    - `WalletService.php` - Core wallet operations
    - `TransactionService.php` - Create transactions
    - `LedgerReconciliationService.php` - Balance verification

2. **Wallet Actions** (`app/Actions/Wallet/`)
    - `CreditWalletAction.php` - Add credits
    - `DebitWalletAction.php` - Deduct credits
    - `RefundTransactionAction.php` - Refund logic

3. **Stripe Integration**
    - Setup Intent: Save payment method
    - Payment Intent: Process top-up
    - Webhook handler for: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`
    - Idempotency key handling

4. **Wallet UI**
    - Balance display component
    - Top-up modal (amount selection + Stripe Elements)
    - Transaction history table
    - Invoice generation (optional)

5. **Cost Calculator**
    - Pre-flight cost estimation for AI operations
    - Insufficient balance checks before generation

**Deliverables:**

- ✅ Wallet top-up flow working end-to-end
- ✅ Stripe webhooks processing correctly
- ✅ Transaction ledger immutable and reconciled
- ✅ UI shows balance and history

---

### Phase 4: Instagram Graph Integration (Week 4-5)

**Goal:** Connect Instagram accounts and publish content.

#### Tasks:

1. **Instagram Auth Flow**
    - OAuth redirect to Instagram
    - Exchange short-lived token for long-lived (60-day)
    - Store account details and token

2. **Instagram Service Layer** (`app/Services/Instagram/`)
    - `InstagramGraphService.php` - API wrapper
    - `TokenRefreshService.php` - Refresh expiring tokens
    - `PublishService.php` - Publish posts to Instagram

3. **Publishing Logic**
    - Container upload (image/video)
    - Publish container
    - Status polling (for videos)
    - Handle API errors and retries

4. **Instagram Webhooks**
    - Subscribe to `feed` and `stories` webhooks
    - Verify webhook signature
    - Process status updates

5. **Scheduler Jobs**
    - `RefreshInstagramTokenJob` - Daily check for tokens <7 days from expiry
    - `PublishScheduledPostsJob` - Every 5 minutes, publish due posts

**Deliverables:**

- ✅ Instagram accounts can be connected
- ✅ Tokens refresh automatically
- ✅ Posts publish successfully
- ✅ Webhooks update post status

---

### Phase 5: AI Sidecar (Python FastAPI) (Week 5-6)

**Goal:** Build AI microservice for content generation.

#### Tasks:

1. **FastAPI Setup**
    - Create `/ai-service` directory (or separate repo)
    - Dependencies: `fastapi`, `openai`, `httpx`, `python-multipart`
    - Auto-generated docs at `/docs`

2. **AI Endpoints**

    ```python
    POST /api/generate/caption
      { prompt, tone, max_tokens } → { caption, hashtags, cost_credits }

    POST /api/generate/image
      { prompt, style, size } → { url, cost_credits }

    POST /api/generate/video
      { prompt, duration } → { job_id } (async)

    GET /api/generate/video/{job_id}
      → { status, url, cost_credits }
    ```

3. **API Client in Laravel** (`app/Services/AI/`)
    - `AISidecarClient.php` - HTTP client to FastAPI
    - `CaptionGenerator.php` - Caption generation
    - `ImageGenerator.php` - Image generation
    - `VideoGenerator.php` - Video generation (async)

4. **Cost Management**
    - Track credits per generation in `ai_generations` table
    - Deduct from wallet after successful generation
    - Store generated assets in S3

5. **Queue Jobs for Async**
    - `GenerateImageJob` - Queue image generation
    - `GenerateVideoJob` - Queue video generation
    - `PollVideoGenerationJob` - Poll for video completion

**Deliverables:**

- ✅ FastAPI service running locally
- ✅ All AI endpoints functional
- ✅ Laravel communicates with AI service
- ✅ Generated content stored in S3
- ✅ Credits deducted correctly

---

### Phase 6: MVP Pages (Week 6-7)

**Goal:** Build all user-facing pages.

#### Pages:

1. **Auth Pages** (already from Breeze, enhance with OAuth)
    - Login with Google button
    - Register with Google button

2. **Onboarding Wizard** (`/onboarding`)
    - Step 1: Create company
    - Step 2: Connect Instagram
    - Step 3: Brand questionnaire (tone, audience, goals)
    - Step 4: Top-up wallet

3. **Dashboard** (`/dashboard`)
    - Quick stats: balance, scheduled posts, Instagram accounts
    - Recent activity feed

4. **Chat + Composer** (`/posts/create`)
    - Chat interface for AI caption generation
    - Media upload or AI generation buttons
    - Caption editor with hashtags
    - Instagram account selector
    - Schedule picker

5. **Content Calendar** (`/calendar`)
    - FullCalendar integration
    - Drag-drop to reschedule
    - Filter by Instagram account
    - Quick edit modal

6. **Wallet Page** (`/wallet`)
    - Balance display
    - Top-up button
    - Transaction history table
    - Download invoices (optional)

7. **Settings** (`/settings`)
    - Company profile
    - Team members (invite, role management)
    - Connected Instagram accounts
    - Brand guidelines editor
    - Timezone, notifications

**Deliverables:**

- ✅ All pages functional
- ✅ Responsive design
- ✅ Intuitive UX

---

### Phase 7: Webhooks & Background Jobs (Week 7-8)

**Goal:** Wire up all webhooks and scheduler jobs.

#### Stripe Webhooks:

```php
Route::post('/webhooks/stripe', StripeWebhookController::class);

// Handle events:
- payment_intent.succeeded → Credit wallet
- payment_intent.payment_failed → Notify user
- charge.refunded → Refund wallet transaction
```

#### Instagram Webhooks:

```php
Route::post('/webhooks/instagram', InstagramWebhookController::class);

// Handle events:
- Feed update → Update post status
- Story expiration → Mark story as expired
```

#### Scheduler Jobs (`app/Console/Kernel.php`):

```php
$schedule->job(RefreshInstagramTokenJob::class)->daily();
$schedule->job(PublishScheduledPostsJob::class)->everyFiveMinutes();
$schedule->job(ReconcileWalletBalancesJob::class)->dailyAt('02:00');
```

**Deliverables:**

- ✅ All webhooks verified and tested
- ✅ Scheduler jobs running via cron
- ✅ Horizon monitoring all queues

---

### Phase 8: Testing & Polish (Week 8)

**Goal:** Comprehensive testing and bug fixes.

#### Testing:

1. **Feature Tests** (Pest)
    - Company creation and switching
    - Wallet top-up and deduction
    - Instagram connection
    - Post creation and publishing

2. **Unit Tests**
    - Service classes
    - Action classes
    - Wallet ledger math

3. **Browser Tests** (Dusk - optional)
    - End-to-end user flows

4. **Load Testing** (optional)
    - Concurrent post publishing
    - Webhook handling under load

**Deliverables:**

- ✅ >80% code coverage
- ✅ All critical paths tested
- ✅ No known bugs

---

## API Integrations

### 1. Stripe API

**Documentation:** https://stripe.com/docs/api

**Key Endpoints:**

- `POST /v1/setup_intents` - Save payment method
- `POST /v1/payment_intents` - Process payment
- `POST /v1/customers` - Create customer
- `GET /v1/payment_intents/:id` - Get payment status

**Webhooks:**

- `payment_intent.succeeded`
- `payment_intent.payment_failed`
- `charge.refunded`

**Security:**

- Verify webhook signature: `Stripe\Webhook::constructEvent()`
- Store Stripe IDs, never raw payment data
- Use idempotency keys for all POSTs

---

### 2. Instagram Graph API

**Documentation:** https://developers.facebook.com/docs/instagram-platform

**Key Endpoints:**

**Authentication:**

```
GET /oauth/authorize → Redirect user
POST /oauth/access_token → Get short-lived token
GET /access_token → Exchange for long-lived (60-day)
GET /refresh_access_token → Refresh token
```

**Publishing:**

```
POST /{ig-user-id}/media → Create container
  - image_url or video_url
  - caption
POST /{ig-user-id}/media_publish → Publish container
GET /{ig-media-id} → Get status
```

**Webhooks:**

- Subscribe to `feed` and `stories`
- Verify signature using app secret

**Rate Limits:**

- 200 calls per hour per user

---

### 3. OpenAI API

**Documentation:** https://platform.openai.com/docs/api-reference

**Key Endpoint:**

```
POST /v1/chat/completions
{
  "model": "gpt-4o-mini",
  "messages": [
    {"role": "system", "content": "You are a social media caption writer..."},
    {"role": "user", "content": "Generate a caption for..."}
  ],
  "max_tokens": 150
}
```

**Cost:** ~$0.01 per caption (gpt-4o-mini)

---

### 4. Stability AI REST API

**Documentation:** https://platform.stability.ai/docs/api-reference

**Key Endpoint:**

```
POST /v2beta/stable-image/generate/sd3
{
  "prompt": "A serene beach sunset...",
  "aspect_ratio": "1:1",
  "output_format": "jpeg"
}
```

**Cost:** ~$0.10-$1.00 per image (depending on model)

---

### 5. Luma Dream Machine API

**Documentation:** https://docs.lumalabs.ai/

**Key Endpoints:**

```
POST /v1/generations
{
  "prompt": "A coffee cup steaming...",
  "duration": 5
}
→ { "id": "gen_123" }

GET /v1/generations/{id}
→ { "status": "completed", "video_url": "..." }
```

**Cost:** ~$1-5 per video (5-10 seconds)

---

## Development Workflow

### Local Development

**Start all services:**

```bash
composer dev
```

This runs:

- Laravel dev server (`localhost:8000`)
- Queue worker
- Log viewer (Pail)
- Vite dev server (HMR)

**Alternative (individual terminals):**

```bash
# Terminal 1: Laravel
php artisan serve

# Terminal 2: Vite
npm run dev

# Terminal 3: Horizon (queue UI)
php artisan horizon

# Terminal 4: Logs
php artisan pail
```

### Code Quality

**Before every commit:**

```bash
# Format code
./vendor/bin/pint

# Run tests
php artisan test

# Check types (if using PHPStan)
./vendor/bin/phpstan analyse
```

### Git Workflow

**Branch naming:**

```
feature/company-switching
fix/wallet-deduction-bug
refactor/instagram-service
```

**Commit messages:**

```
feat(wallet): add Stripe top-up integration
fix(instagram): handle token refresh edge case
refactor(posts): extract publish logic to service
test(wallet): add ledger reconciliation tests
```

---

## Security & Compliance

### Data Protection

**Sensitive Data Storage:**

- ✅ Encrypt Instagram access tokens (cast to `encrypted`)
- ✅ Never log API keys or tokens
- ✅ Use signed URLs for S3 assets (private posts)

**Environment Variables:**

- ✅ Never commit `.env` to git
- ✅ Rotate secrets regularly
- ✅ Use different keys for dev/staging/prod

### Authentication & Authorization

**Middleware Stack:**

```php
Route::middleware(['auth', 'company.access:admin'])->group(function () {
    // Admin-only routes
});

Route::middleware(['auth', 'company.access:user|admin'])->group(function () {
    // User and admin routes
});
```

**CSRF Protection:**

- ✅ Enabled by default for web routes
- ✅ Disable for webhook routes (verify signature instead)

**Rate Limiting:**

```php
// API routes
RateLimiter::for('api', fn() => Limit::perMinute(60));

// Instagram publish (prevent spam)
RateLimiter::for('instagram.publish', fn() => Limit::perHour(50));
```

### Compliance

**GDPR:**

- User data export endpoint
- Account deletion cascade
- Privacy policy page

**PCI DSS:**

- Never store credit card numbers
- Use Stripe Elements (cards never touch our server)
- Stripe handles PCI compliance

---

## Testing Strategy

### Test Structure

```
tests/
├── Feature/
│   ├── Auth/
│   │   └── SocialLoginTest.php
│   ├── Company/
│   │   ├── CreateCompanyTest.php
│   │   ├── SwitchCompanyTest.php
│   │   └── InviteUserTest.php
│   ├── Wallet/
│   │   ├── TopUpTest.php
│   │   ├── DeductCreditsTest.php
│   │   └── WebhookTest.php
│   ├── Instagram/
│   │   ├── ConnectAccountTest.php
│   │   └── PublishPostTest.php
│   └── Post/
│       ├── CreatePostTest.php
│       └── SchedulePostTest.php
└── Unit/
    ├── Services/
    │   ├── WalletServiceTest.php
    │   └── InstagramGraphServiceTest.php
    └── Actions/
        ├── CreditWalletActionTest.php
        └── PublishPostActionTest.php
```

### Key Test Scenarios

**Wallet Tests:**

```php
it('credits wallet when Stripe payment succeeds', function () {
    $company = Company::factory()->create();
    $webhook = fakeStripeWebhook('payment_intent.succeeded', [
        'amount' => 5000, // $50
        'metadata' => ['company_id' => $company->id]
    ]);

    post('/webhooks/stripe', $webhook);

    expect($company->wallet->balance())->toBe(5000);
});
```

**Instagram Tests:**

```php
it('publishes post to Instagram', function () {
    $post = Post::factory()->scheduled()->create();

    InstagramGraph::fake([
        'media' => ['id' => 'container_123'],
        'media_publish' => ['id' => 'ig_media_456']
    ]);

    (new PublishService)->publish($post);

    expect($post->fresh()->status)->toBe('published');
    expect($post->instagram_media_id)->toBe('ig_media_456');
});
```

---

## Deployment & DevOps

### Environment Setup

**Staging:**

- Same config as production
- Use Stripe test mode
- Separate S3 bucket
- PostgreSQL database

**Production:**

- HTTPS required (Let's Encrypt or CloudFront)
- Redis for queues and cache
- S3 with CloudFront CDN
- PostgreSQL (RDS or managed)
- Horizon for queue monitoring
- Scheduler via cron

### Deployment Checklist

**Before deploy:**

- ✅ Run tests: `php artisan test`
- ✅ Format code: `./vendor/bin/pint`
- ✅ Build assets: `npm run build`
- ✅ Clear config cache: `php artisan config:clear`

**During deploy:**

```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan horizon:terminate # Restart workers
npm ci
npm run build
```

**After deploy:**

- ✅ Check Horizon is running
- ✅ Verify scheduler cron job
- ✅ Test webhook endpoints
- ✅ Monitor logs for errors

### Monitoring

**Laravel Telescope (dev only):**

```bash
composer require laravel/telescope --dev
php artisan telescope:install
```

**Laravel Pulse (production metrics):**

```bash
composer require laravel/pulse
php artisan pulse:install
```

**External Monitoring:**

- Sentry for error tracking
- New Relic or Scout APM for performance
- Uptime monitoring (Pingdom, Oh Dear)

---

## MVP Acceptance Criteria

**The MVP is complete when a user can:**

1. ✅ **Sign up/login** via email or Google OAuth
2. ✅ **Create a company** and name it
3. ✅ **Complete onboarding** questionnaire
4. ✅ **Connect Instagram account** via OAuth
5. ✅ **Save payment method** and **top-up wallet**
6. ✅ **Webhook credits wallet** (ledger shows transaction)
7. ✅ **Generate AI caption** using chat interface
8. ✅ **Generate AI image or video** (stored in S3)
9. ✅ **Create post** with generated content
10. ✅ **Schedule post** on calendar
11. ✅ **Post publishes** to Instagram at scheduled time
12. ✅ **Webhook updates** post status
13. ✅ **View transaction history** in wallet
14. ✅ **Invite team member** and assign role
15. ✅ **Switch between companies** if member of multiple

**Edge cases handled:**

- ❌ Insufficient wallet balance → show error before generation
- ❌ Instagram token expired → auto-refresh or prompt reconnect
- ❌ Stripe payment fails → show error, don't credit wallet
- ❌ Post publish fails → mark as failed, allow retry

---

## Next Steps

**Immediate:**

1. Answer clarifying questions (see main discussion)
2. Create `.env.example` with all required keys
3. Install missing dependencies
4. Begin Phase 1 implementation

**Future Enhancements (Post-MVP):**

- Analytics dashboard (post performance, engagement)
- AI content library (reuse generated assets)
- Content approval workflow (for agencies)
- Multi-platform support (TikTok, Facebook, LinkedIn)
- White-label solution for agencies
- Mobile app (React Native + Sanctum API)

---

## Appendix

### Useful Commands

```bash
# Development
composer dev                      # Start all services
php artisan horizon               # Queue monitoring UI
php artisan pail                  # Live logs
php artisan tinker                # REPL

# Database
php artisan migrate:fresh --seed  # Reset database
php artisan db:seed               # Run seeders only

# Queue
php artisan queue:work            # Process jobs
php artisan queue:failed          # Show failed jobs
php artisan queue:retry all       # Retry all failed

# Cache
php artisan optimize              # Cache everything
php artisan optimize:clear        # Clear all caches

# Testing
php artisan test                  # Run Pest tests
php artisan test --coverage       # With coverage
./vendor/bin/pint                 # Fix code style
```

### Reference Links

- [Laravel 12 Docs](https://laravel.com/docs/12.x)
- [Inertia.js Docs](https://inertiajs.com)
- [Vue 3 Docs](https://vuejs.org)
- [Tailwind CSS Docs](https://tailwindcss.com)
- [Stripe API Docs](https://stripe.com/docs/api)
- [Instagram Graph API Docs](https://developers.facebook.com/docs/instagram-platform)
- [OpenAI API Docs](https://platform.openai.com/docs)
- [Stability AI Docs](https://platform.stability.ai/docs)
- [Luma API Docs](https://docs.lumalabs.ai)

---

**Document Version:** 1.0  
**Last Updated:** October 9, 2025  
**Status:** Ready for implementation
