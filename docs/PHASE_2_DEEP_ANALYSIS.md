# Phase 2: Database & Multi-Company - Deep Research Analysis

**Project:** Autopost AI - Phase 2 Implementation Analysis  
**Last Updated:** October 15, 2025  
**Version:** 1.0  
**Status:** 🔍 **RESEARCH COMPLETE**

---

## 📊 **CURRENT DATABASE STATE ANALYSIS**

### ✅ **WHAT WE ALREADY HAVE (EXCELLENT FOUNDATION)**

#### **Core Tables - IMPLEMENTED**

1. **`users`** ✅ **COMPLETE**
    - Basic user fields (name, email, password, email_verified_at)
    - Multi-language support (locale)
    - Timezone management (timezone)
    - User suspension system (suspended_at, suspended_by, suspension_reason)
    - Last login tracking (last_login_at)
    - Current company tracking (current_company_id)

2. **`companies`** ✅ **COMPLETE**
    - Basic company structure (name, owner_id, settings)
    - Owner relationship established
    - JSON settings field for flexible configuration

3. **`company_user`** ✅ **COMPLETE**
    - Many-to-many relationship between users and companies
    - Role-based access (admin, user, network)
    - Proper indexes and constraints

4. **`posts`** ✅ **COMPLETE**
    - Post management system with company scoping
    - Instagram account integration
    - Scheduling and status management
    - Metadata support

5. **`post_media`** ✅ **COMPLETE**
    - Media file management
    - Order support for carousels
    - Metadata and file information

6. **`instagram_accounts`** ✅ **COMPLETE WITH HYBRID OWNERSHIP**
    - Hybrid ownership model (user + company accounts)
    - Account sharing capabilities
    - Token management and expiration
    - Status tracking

7. **`instagram_account_user`** ✅ **COMPLETE**
    - Account sharing permissions
    - Granular access control (can_post, can_manage)

8. **`instagram_posts`** ✅ **COMPLETE**
    - Instagram-specific post management
    - Publishing status and error handling

9. **`inquiries`** ✅ **COMPLETE**
    - Marketing inquiry tracking
    - IP and user agent logging

#### **Models - IMPLEMENTED**

1. **`User`** ✅ **COMPLETE**
    - Company relationships (belongsToMany, currentCompany)
    - Role management (hasRole, isAdmin, getRoleIn)
    - Company switching (switchCompany)
    - Instagram account access (ownedInstagramAccounts, sharedInstagramAccounts)
    - User suspension system (suspend, unsuspend, isSuspended)
    - Statistics and analytics

2. **`Company`** ✅ **COMPLETE**
    - Owner relationship (belongsTo)
    - User management (belongsToMany with roles)
    - Instagram account management
    - Member checking and role management

3. **`Post`** ✅ **COMPLETE**
    - Company scoping
    - Instagram account integration
    - Media relationships
    - Status management

4. **`PostMedia`** ✅ **COMPLETE**
    - File management
    - Order support
    - Metadata handling

5. **`InstagramAccount`** ✅ **COMPLETE**
    - Hybrid ownership support
    - Account sharing
    - Token management

6. **`InstagramPost`** ✅ **COMPLETE**
    - Publishing lifecycle
    - Error handling

#### **Business Logic - IMPLEMENTED**

1. **Multi-tenancy** ✅ **COMPLETE**
    - Company-based data scoping
    - User-company relationships
    - Current company tracking

2. **Role-based Access Control** ✅ **COMPLETE**
    - Admin, User, Network roles
    - Permission checking methods
    - Company-level authorization

3. **Instagram Integration** ✅ **COMPLETE**
    - Hybrid ownership model
    - Account sharing with permissions
    - Post lifecycle management

4. **User Management** ✅ **COMPLETE**
    - User suspension system
    - Admin panel integration
    - Statistics and analytics

---

## ❌ **WHAT WE NEED TO IMPLEMENT (MISSING COMPONENTS)**

### **1. Wallet System Tables - MISSING**

#### **`wallets` Table - NEEDS IMPLEMENTATION**

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

**Purpose:** Store wallet metadata for each company
**Relationships:**

- `belongsTo(Company)`
- `hasMany(WalletTransaction)`

#### **`wallet_transactions` Table - NEEDS IMPLEMENTATION**

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

**Purpose:** Immutable ledger of all wallet changes
**Key Features:**

- Immutable transaction log
- Balance snapshots for performance
- Idempotency key for webhook safety
- Stripe integration support

### **2. AI Generation Tables - MISSING**

#### **`ai_generations` Table - NEEDS IMPLEMENTATION**

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

**Purpose:** Track all AI-generated content and costs
**Relationships:**

- `belongsTo(Company)`
- `belongsTo(User)`

### **3. Onboarding Tables - MISSING**

#### **`questionnaire_responses` Table - NEEDS IMPLEMENTATION**

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

**Purpose:** Store onboarding questionnaire answers for brand setup

#### **`content_plans` Table - NEEDS IMPLEMENTATION**

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

**Purpose:** Long-term content planning and themes

### **4. Enhanced Company Management - MISSING**

#### **Company Invitation System - NEEDS IMPLEMENTATION**

```sql
-- Add to company_user table
ALTER TABLE company_user ADD COLUMN invited_by BIGINT UNSIGNED NULL;
ALTER TABLE company_user ADD COLUMN invited_at TIMESTAMP NULL;
ALTER TABLE company_user ADD COLUMN accepted_at TIMESTAMP NULL;

-- Add foreign key
ALTER TABLE company_user ADD FOREIGN KEY (invited_by) REFERENCES users(id) ON DELETE SET NULL;
```

**Purpose:** Track invitation flow and acceptance

### **5. Missing Models - NEEDS IMPLEMENTATION**

#### **`Wallet` Model - NEEDS IMPLEMENTATION**

```php
class Wallet extends Model
{
    protected $fillable = ['company_id', 'currency'];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function transactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function balance(): int
    {
        return $this->transactions()
            ->sum(DB::raw("CASE WHEN type = 'credit' THEN amount ELSE -amount END"));
    }
}
```

#### **`WalletTransaction` Model - NEEDS IMPLEMENTATION**

```php
class WalletTransaction extends Model
{
    protected $fillable = [
        'wallet_id', 'type', 'amount', 'balance_after',
        'idempotency_key', 'stripe_payment_intent_id',
        'description', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
```

#### **`AiGeneration` Model - NEEDS IMPLEMENTATION**

```php
class AiGeneration extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'type', 'prompt',
        'result', 'cost_credits', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### **6. Missing Services - NEEDS IMPLEMENTATION**

#### **Company Management Services**

- `CompanyService` - Company CRUD operations
- `CompanyInvitationService` - User invitation flow
- `CompanySwitchingService` - Company switching logic

#### **Wallet Services**

- `WalletService` - Core wallet operations
- `TransactionService` - Transaction creation and management
- `LedgerReconciliationService` - Balance verification

### **7. Missing Middleware - NEEDS IMPLEMENTATION**

#### **`EnsureUserHasCompanyAccess` Middleware**

```php
class EnsureUserHasCompanyAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->user()->current_company_id) {
            return redirect()->route('companies.create');
        }

        return $next($request);
    }
}
```

### **8. Missing UI Components - NEEDS IMPLEMENTATION**

#### **Company Management UI**

- Company creation form
- Company switcher component
- User invitation flow
- Company settings page

#### **Wallet UI**

- Balance display component
- Transaction history table
- Top-up modal (for Phase 3)

---

## 🎯 **IMPLEMENTATION PRIORITY MATRIX**

### **HIGH PRIORITY (Core Business Logic)**

1. **Wallet System Tables** 🔥
    - `wallets` table
    - `wallet_transactions` table
    - `Wallet` and `WalletTransaction` models
    - Basic wallet service

2. **Enhanced Company Management** 🔥
    - Company invitation system
    - Company switching UI
    - Company creation flow
    - `EnsureUserHasCompanyAccess` middleware

3. **Global Company Scope** 🔥
    - `BelongsToCompany` trait
    - Global scope implementation
    - Company-scoped queries

### **MEDIUM PRIORITY (Enhanced Features)**

4. **AI Generation Tables** 🟡
    - `ai_generations` table
    - `AiGeneration` model
    - Basic AI service structure

5. **Onboarding Tables** 🟡
    - `questionnaire_responses` table
    - `content_plans` table
    - Onboarding flow models

### **LOW PRIORITY (Future Enhancements)**

6. **Advanced Features** 🟢
    - Content planning UI
    - Advanced analytics
    - Team collaboration features

---

## 📋 **DETAILED IMPLEMENTATION PLAN**

### **Week 1: Wallet System Foundation**

#### **Day 1-2: Database Schema**

- Create `wallets` migration
- Create `wallet_transactions` migration
- Add proper indexes and constraints
- Test migrations

#### **Day 3-4: Models & Relationships**

- Create `Wallet` model
- Create `WalletTransaction` model
- Add relationships to `Company` model
- Implement balance calculation methods

#### **Day 5: Basic Services**

- Create `WalletService`
- Create `TransactionService`
- Implement basic wallet operations
- Add tests

### **Week 2: Enhanced Company Management**

#### **Day 1-2: Company Invitation System**

- Add invitation fields to `company_user` table
- Update `Company` and `User` models
- Create `CompanyInvitationService`

#### **Day 3-4: Company Switching & UI**

- Create company switcher component
- Implement company creation flow
- Add company settings page
- Create `EnsureUserHasCompanyAccess` middleware

#### **Day 5: Global Company Scope**

- Create `BelongsToCompany` trait
- Implement global company scope
- Update existing models to use trait
- Test company scoping

### **Week 3: AI Generation & Onboarding**

#### **Day 1-2: AI Generation Tables**

- Create `ai_generations` migration
- Create `AiGeneration` model
- Add relationships and methods

#### **Day 3-4: Onboarding Tables**

- Create `questionnaire_responses` migration
- Create `content_plans` migration
- Create corresponding models

#### **Day 5: Testing & Documentation**

- Write comprehensive tests
- Update documentation
- Create seeders and factories

---

## 🔍 **TECHNICAL CONSIDERATIONS**

### **Database Design Decisions**

1. **Wallet System Architecture**
    - **Immutable Transaction Ledger**: All wallet changes are recorded as transactions
    - **Balance Snapshots**: Store `balance_after` for performance
    - **Idempotency Keys**: Prevent duplicate webhook processing
    - **ULID Primary Keys**: Sortable, unique identifiers

2. **Company Scoping Strategy**
    - **Global Scopes**: Automatically filter queries by current company
    - **Trait-Based**: Reusable `BelongsToCompany` trait
    - **Middleware Protection**: Ensure user has company access

3. **Invitation Flow Design**
    - **Pivot Table Enhancement**: Add invitation fields to `company_user`
    - **Status Tracking**: `invited_at`, `accepted_at` timestamps
    - **Audit Trail**: Track who invited whom

### **Performance Considerations**

1. **Indexing Strategy**
    - Company-based queries: `idx_company_status`
    - Wallet transactions: `idx_wallet_date`
    - User invitations: `idx_user_invited`

2. **Query Optimization**
    - Eager loading for company relationships
    - Balance calculation optimization
    - Pagination for large datasets

### **Security Considerations**

1. **Access Control**
    - Company-level data isolation
    - Role-based permissions
    - Invitation validation

2. **Data Integrity**
    - Foreign key constraints
    - Transaction atomicity
    - Balance reconciliation

---

## 📊 **SUCCESS METRICS**

### **Technical Metrics**

- ✅ All migrations run successfully
- ✅ All models have proper relationships
- ✅ Global company scope working
- ✅ Wallet transactions immutable
- ✅ Balance calculations accurate

### **Functional Metrics**

- ✅ Users can create companies
- ✅ Users can invite team members
- ✅ Users can switch between companies
- ✅ Data is properly scoped by company
- ✅ Wallet system tracks all transactions

### **Quality Metrics**

- ✅ Test coverage > 90%
- ✅ No N+1 query issues
- ✅ Proper error handling
- ✅ Documentation complete

---

## 🚀 **NEXT STEPS**

1. **Start with Wallet System** - Core business logic
2. **Implement Company Management** - User experience
3. **Add Global Company Scope** - Data isolation
4. **Create AI Generation Tables** - Future AI features
5. **Build Onboarding System** - User onboarding

**Estimated Timeline:** 2-3 weeks for complete Phase 2 implementation

**Status:** 🔍 **RESEARCH COMPLETE - READY FOR IMPLEMENTATION**

---

**Last Updated:** October 15, 2025  
**Version:** 1.0  
**Next Phase:** Phase 2 Implementation
