# Phase 2: Database & Multi-Company - Implementation Todo List

**Project:** Autopost AI - Phase 2 Implementation  
**Last Updated:** October 15, 2025  
**Version:** 1.0  
**Status:** 🚧 **60% Complete - Ready for Implementation**

---

## 📋 **PHASE 2 IMPLEMENTATION TODO LIST**

### 🎯 **HIGH PRIORITY TASKS (Week 1-2)**

#### **1. Wallet System Implementation** 🔥

##### **Database Schema**

- [ ] Create `wallets` migration
    - [ ] Add `company_id` foreign key (unique)
    - [ ] Add `currency` field (default 'usd')
    - [ ] Add proper indexes and constraints
    - [ ] Test migration

- [ ] Create `wallet_transactions` migration
    - [ ] Add `id` field (ULID primary key)
    - [ ] Add `wallet_id` foreign key
    - [ ] Add `type` enum (credit, debit, refund, adjustment)
    - [ ] Add `amount` field (bigint, always positive)
    - [ ] Add `balance_after` field (balance snapshot)
    - [ ] Add `idempotency_key` field (unique)
    - [ ] Add `stripe_payment_intent_id` field (nullable)
    - [ ] Add `description` field
    - [ ] Add `metadata` JSON field
    - [ ] Add proper indexes (wallet_date, idempotency)
    - [ ] Test migration

##### **Models & Relationships**

- [ ] Create `Wallet` model
    - [ ] Add fillable fields
    - [ ] Add casts for JSON fields
    - [ ] Add `company()` relationship
    - [ ] Add `transactions()` relationship
    - [ ] Add `balance()` method (computed from transactions)
    - [ ] Add `balanceFromSnapshot()` method (performance optimization)

- [ ] Create `WalletTransaction` model
    - [ ] Add fillable fields
    - [ ] Add casts for JSON and datetime fields
    - [ ] Add `wallet()` relationship
    - [ ] Add transaction type constants
    - [ ] Add validation rules

- [ ] Update `Company` model
    - [ ] Add `wallet()` relationship
    - [ ] Add `hasWallet()` method
    - [ ] Add `createWallet()` method

##### **Services**

- [ ] Create `WalletService`
    - [ ] Add `createWallet()` method
    - [ ] Add `getBalance()` method
    - [ ] Add `canAfford()` method
    - [ ] Add `getTransactions()` method

- [ ] Create `TransactionService`
    - [ ] Add `createTransaction()` method
    - [ ] Add `creditWallet()` method
    - [ ] Add `debitWallet()` method
    - [ ] Add `refundTransaction()` method
    - [ ] Add `adjustBalance()` method (admin only)

##### **Testing**

- [ ] Create `WalletTest` feature test
- [ ] Create `WalletTransactionTest` feature test
- [ ] Create `WalletServiceTest` unit test
- [ ] Create `TransactionServiceTest` unit test
- [ ] Test balance calculations
- [ ] Test transaction immutability
- [ ] Test idempotency key handling

#### **2. Enhanced Company Management** 🔥

##### **Database Schema Updates**

- [ ] Update `company_user` table
    - [ ] Add `invited_by` foreign key (nullable)
    - [ ] Add `invited_at` timestamp (nullable)
    - [ ] Add `accepted_at` timestamp (nullable)
    - [ ] Add foreign key constraint for `invited_by`
    - [ ] Add indexes for invitation queries
    - [ ] Test migration

##### **Models & Relationships**

- [ ] Update `User` model
    - [ ] Add `invitedBy()` relationship
    - [ ] Add `invitationsSent()` relationship
    - [ ] Add `pendingInvitations()` scope
    - [ ] Add `acceptedInvitations()` scope

- [ ] Update `Company` model
    - [ ] Add `invitations()` relationship
    - [ ] Add `pendingInvitations()` method
    - [ ] Add `inviteUser()` method
    - [ ] Add `acceptInvitation()` method
    - [ ] Add `revokeInvitation()` method

##### **Services**

- [ ] Create `CompanyInvitationService`
    - [ ] Add `sendInvitation()` method
    - [ ] Add `acceptInvitation()` method
    - [ ] Add `revokeInvitation()` method
    - [ ] Add `getPendingInvitations()` method
    - [ ] Add email notification logic

##### **UI Components**

- [ ] Create company invitation form
- [ ] Create invitation acceptance page
- [ ] Create pending invitations list
- [ ] Create company switcher component
- [ ] Create company creation flow
- [ ] Add invitation status indicators

##### **Testing**

- [ ] Create `CompanyInvitationTest` feature test
- [ ] Create `CompanyInvitationServiceTest` unit test
- [ ] Test invitation flow
- [ ] Test invitation acceptance
- [ ] Test invitation revocation

#### **3. Global Company Scope** 🔥

##### **Trait Creation**

- [ ] Create `BelongsToCompany` trait
    - [ ] Add `bootBelongsToCompany()` method
    - [ ] Add global scope for company filtering
    - [ ] Add `company()` relationship method
    - [ ] Add `scopeForCompany()` method

##### **Model Updates**

- [ ] Update `Post` model
    - [ ] Add `BelongsToCompany` trait
    - [ ] Ensure company scoping works
    - [ ] Test company-scoped queries

- [ ] Update `PostMedia` model
    - [ ] Add `BelongsToCompany` trait
    - [ ] Ensure company scoping works
    - [ ] Test company-scoped queries

- [ ] Update `InstagramAccount` model
    - [ ] Add `BelongsToCompany` trait
    - [ ] Handle hybrid ownership in scope
    - [ ] Test company-scoped queries

##### **Middleware**

- [ ] Create `EnsureUserHasCompanyAccess` middleware
    - [ ] Check if user has current company
    - [ ] Redirect to company creation if needed
    - [ ] Apply to protected routes

##### **Testing**

- [ ] Create `BelongsToCompanyTest` unit test
- [ ] Create `CompanyScopeTest` feature test
- [ ] Test global scope functionality
- [ ] Test middleware behavior

---

### 🟡 **MEDIUM PRIORITY TASKS (Week 3)**

#### **4. AI Generation Tracking** 🟡

##### **Database Schema**

- [ ] Create `ai_generations` migration
    - [ ] Add `company_id` foreign key
    - [ ] Add `user_id` foreign key
    - [ ] Add `type` enum (caption, image, video)
    - [ ] Add `prompt` text field
    - [ ] Add `result` text field (nullable)
    - [ ] Add `cost_credits` integer field
    - [ ] Add `metadata` JSON field
    - [ ] Add proper indexes
    - [ ] Test migration

##### **Models & Relationships**

- [ ] Create `AiGeneration` model
    - [ ] Add fillable fields
    - [ ] Add casts for JSON fields
    - [ ] Add `company()` relationship
    - [ ] Add `user()` relationship
    - [ ] Add generation type constants
    - [ ] Add cost calculation methods

- [ ] Update `Company` model
    - [ ] Add `aiGenerations()` relationship
    - [ ] Add `getTotalAiCost()` method
    - [ ] Add `getAiGenerationStats()` method

- [ ] Update `User` model
    - [ ] Add `aiGenerations()` relationship
    - [ ] Add `getAiGenerationStats()` method

##### **Services**

- [ ] Create `AiGenerationService`
    - [ ] Add `createGeneration()` method
    - [ ] Add `getGenerationStats()` method
    - [ ] Add `getCostBreakdown()` method
    - [ ] Add `getRecentGenerations()` method

##### **Testing**

- [ ] Create `AiGenerationTest` feature test
- [ ] Create `AiGenerationServiceTest` unit test
- [ ] Test generation tracking
- [ ] Test cost calculations

#### **5. Onboarding Tables** 🟡

##### **Database Schema**

- [ ] Create `questionnaire_responses` migration
    - [ ] Add `company_id` foreign key
    - [ ] Add `user_id` foreign key
    - [ ] Add `responses` JSON field
    - [ ] Add `completed_at` timestamp
    - [ ] Add proper indexes
    - [ ] Test migration

- [ ] Create `content_plans` migration
    - [ ] Add `company_id` foreign key
    - [ ] Add `name` varchar field
    - [ ] Add `description` text field
    - [ ] Add `start_date` date field
    - [ ] Add `end_date` date field
    - [ ] Add `themes` JSON field
    - [ ] Add proper indexes
    - [ ] Test migration

##### **Models & Relationships**

- [ ] Create `QuestionnaireResponse` model
    - [ ] Add fillable fields
    - [ ] Add casts for JSON and datetime fields
    - [ ] Add `company()` relationship
    - [ ] Add `user()` relationship

- [ ] Create `ContentPlan` model
    - [ ] Add fillable fields
    - [ ] Add casts for JSON and date fields
    - [ ] Add `company()` relationship
    - [ ] Add `isActive()` method
    - [ ] Add `getDuration()` method

##### **Testing**

- [ ] Create `QuestionnaireResponseTest` feature test
- [ ] Create `ContentPlanTest` feature test
- [ ] Test onboarding flow
- [ ] Test content planning

---

### 🟢 **LOW PRIORITY TASKS (Future)**

#### **6. Advanced Features** 🟢

##### **UI Enhancements**

- [ ] Create company dashboard
- [ ] Create team management page
- [ ] Create wallet management UI
- [ ] Create AI generation history
- [ ] Create content planning interface

##### **Analytics & Reporting**

- [ ] Add company analytics
- [ ] Add wallet transaction reports
- [ ] Add AI generation reports
- [ ] Add user activity tracking

##### **Performance Optimization**

- [ ] Add database query optimization
- [ ] Add caching for wallet balances
- [ ] Add background job processing
- [ ] Add API rate limiting

---

## 📊 **PROGRESS TRACKING**

### **Current Status: 60% Complete**

| Component                  | Status         | Progress | Priority  |
| -------------------------- | -------------- | -------- | --------- |
| **Core Database Schema**   | ✅ Complete    | 100%     | ✅ Done   |
| **Multi-Tenancy**          | ✅ Complete    | 100%     | ✅ Done   |
| **Role-Based Access**      | ✅ Complete    | 100%     | ✅ Done   |
| **Company Management**     | ✅ Complete    | 100%     | ✅ Done   |
| **Instagram Integration**  | ✅ Complete    | 100%     | ✅ Done   |
| **User Management**        | ✅ Complete    | 100%     | ✅ Done   |
| **Wallet System**          | 🔜 Not Started | 0%       | 🔥 High   |
| **Company Invitations**    | 🔜 Not Started | 0%       | 🔥 High   |
| **Global Company Scope**   | 🔜 Not Started | 0%       | 🔥 High   |
| **AI Generation Tracking** | 🔜 Not Started | 0%       | 🟡 Medium |
| **Onboarding Tables**      | 🔜 Not Started | 0%       | 🟡 Medium |

### **Estimated Timeline**

- **Week 1:** Wallet System Implementation
- **Week 2:** Enhanced Company Management + Global Scope
- **Week 3:** AI Generation + Onboarding Tables
- **Total:** 3 weeks to complete Phase 2

---

## 🎯 **SUCCESS CRITERIA**

### **Technical Requirements**

- [ ] All migrations run successfully
- [ ] All models have proper relationships
- [ ] Global company scope working
- [ ] Wallet transactions immutable
- [ ] Balance calculations accurate
- [ ] Test coverage > 90%

### **Functional Requirements**

- [ ] Users can create companies
- [ ] Users can invite team members
- [ ] Users can switch between companies
- [ ] Data is properly scoped by company
- [ ] Wallet system tracks all transactions
- [ ] AI generation costs tracked

### **Quality Requirements**

- [ ] No N+1 query issues
- [ ] Proper error handling
- [ ] Documentation complete
- [ ] Security validated
- [ ] Performance optimized

---

## 🚀 **NEXT STEPS**

1. **Start with Wallet System** - Core business logic
2. **Implement Company Invitations** - User experience
3. **Add Global Company Scope** - Data isolation
4. **Create AI Generation Tables** - Future AI features
5. **Build Onboarding System** - User onboarding

**Status:** 🔍 **RESEARCH COMPLETE - READY FOR IMPLEMENTATION**

---

**Last Updated:** October 15, 2025  
**Version:** 1.0  
**Next Action:** Begin Wallet System Implementation
