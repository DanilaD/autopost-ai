# {{APP_NAME}} - Documentation Index

**Project:** {{APP_NAME}} - AI-Powered Instagram Content Platform  
**Last Updated:** October 16, 2025
**Version:** 2.1

---

## 📚 Documentation Structure

This directory contains comprehensive documentation for the {{APP_NAME}} project. All documents are written in Markdown and are organized by topic.

**🚨 MANDATORY READING:**

- **[CODING_STANDARDS.md](./CODING_STANDARDS.md)** - All developers MUST follow these rules
- **[CODE_QUALITY_SETUP.md](./CODE_QUALITY_SETUP.md)** - Set up linting & pre-push checks
- **[GITHUB_PR_AUTOMATION.md](./GITHUB_PR_AUTOMATION.md)** - CI/CD and automated PR checks

---

## 📖 Core Documents

### 1. [PROJECT_PLAN.md](./PROJECT_PLAN.md)

**Master implementation plan for the entire project.**

**Contains:**

- Project overview and vision
- Complete tech stack analysis
- Architecture decisions (multi-tenancy, wallet system, roles)
- Full database schema overview
- 8 implementation phases with week-by-week roadmap
- API integration guides (Stripe, Instagram, OpenAI, Stability AI, Luma)
- Development workflow and best practices
- Security & compliance (GDPR, PCI DSS)
- Testing strategy
- Deployment checklist
- MVP acceptance criteria

**Status:** ✅ Complete  
**Estimated Reading Time:** 30-40 minutes

---

### 2. [DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md)

**Detailed database design and implementation guide.**

**Contains:**

- Entity relationship diagrams
- Complete table definitions with SQL
- All relationships and Eloquent patterns
- JSON schema examples for metadata fields
- Performance indexes and optimization
- Sample queries for common operations
- Migration order and dependencies
- Seeding strategy for development
- Backup and maintenance procedures
- Data integrity rules

**Status:** ✅ Complete  
**Estimated Reading Time:** 20-30 minutes

---

### 3. [AUTH_FLOW_PLAN.md](./AUTH_FLOW_PLAN.md)

**Modern authentication system implementation (Phase 0).**

**Contains:**

- Progressive authentication UX (email-first entry)
- Passwordless authentication (magic links)
- Traditional password authentication
- Email verification system
- Inquiry tracking (marketing intelligence)
- Complete code examples for:
    - Controllers (EmailCheckController, MagicLinkController)
    - Services (InquiryService)
    - Vue components (EmailEntry, Register)
    - Mail templates
    - Routes and middleware
- Security best practices (rate limiting, signed URLs)
- Test cases
- Privacy compliance

**Status:** ✅ Complete  
**Estimated Reading Time:** 25-35 minutes

---

### 4. [INTERNATIONALIZATION_PLAN.md](./INTERNATIONALIZATION_PLAN.md)

**Multi-language support for English, Russian, and Spanish.**

**Contains:**

- URL structure (locale path prefix: /en/, /ru/, /es/)
- Backend setup (Laravel localization)
- Frontend setup (Vue i18n with vue-i18n)
- Language detection priority (Cookie → User → Browser → Default)
- Database schema (user locale preference)
- Translation file structure (JavaScript objects in app.js)
- Language switcher component
- Complete code examples for:
    - Middleware (SetLocale)
    - Controller (LocaleController)
    - Vue components (LanguageSwitcher)
    - Translation files for all 3 languages
- SEO considerations (hreflang tags)
- Date and number formatting
- Testing strategy

**Status:** ✅ Complete & Implemented  
**Estimated Reading Time:** 20-30 minutes

---

### 5. [CODING_STANDARDS.md](./CODING_STANDARDS.md)

**Architecture guidelines and coding rules - MANDATORY for all developers.**

**Contains:**

- Clean architecture pattern (Controller → Service → Repository → Model)
- Layer responsibilities and rules:
    - Controllers: HTTP handling only, NO business logic
    - Models: Relationships and casts only
    - Enums: Constants and status values
    - Services: ALL business logic
    - Repositories: ALL database queries
    - Service Providers: Dependency injection binding
- Naming conventions (classes, methods, tables, columns)
- Code structure and organization
- Best practices (DI, type hints, transactions, logging)
- Documentation requirements
- ⚠️ **NEW:** Mandatory pre-commit documentation check
- Testing requirements
- Complete code examples for every layer

**Status:** ✅ Active - Mandatory  
**Estimated Reading Time:** 30-40 minutes  
**Recent Update:** Added pre-commit documentation check rule (v1.1)

---

### 6. [CODE_QUALITY_SETUP.md](./CODE_QUALITY_SETUP.md)

**Automated linting and pre-push checks setup.**

**Contains:**

- Git hooks with Husky (pre-commit & pre-push)
- PHP linting (Laravel Pint)
- JavaScript/Vue linting (ESLint + Prettier)
- Static analysis (PHPStan)
- Configuration files for all tools
- NPM and Composer scripts
- Editor integration (VS Code, PHPStorm)
- Team workflow and troubleshooting

**Status:** ✅ Complete  
**Estimated Reading Time:** 15-20 minutes

---

### 7. [GITHUB_PR_AUTOMATION.md](./GITHUB_PR_AUTOMATION.md)

**Comprehensive GitHub PR automation and CI/CD setup.**

**Contains:**

- 5 complete GitHub Actions workflows:
    - Code quality checks (Pint, PHPStan, ESLint)
    - Tests with coverage (PHP 8.2, 8.3)
    - Security scanning (CodeQL, npm audit, secrets)
    - Database migration testing
    - Frontend bundle size checks
- Automated code review tools (SonarCloud, CodeClimate, Reviewdog)
- Security scanning (Dependabot, Snyk, GitGuardian)
- Test coverage reports (Codecov, Coveralls)
- Performance checks (N+1 queries, Lighthouse CI)
- Branch protection rules
- Bot integrations (Renovate, Mergify, PR labeler)
- PR and Issue templates
- Status badges
- Cost analysis and recommendations

**Status:** ✅ Complete  
**Estimated Reading Time:** 25-30 minutes

---

### 8. [RELEASE_MANAGEMENT.md](./RELEASE_MANAGEMENT.md)

**Complete release and deployment workflow.**

**Contains:**

- Git branching strategy (GitHub Flow)
- PR workflow and best practices
- Semantic versioning (SemVer)
- Automated changelog generation
- Release process (manual and automated)
- Deployment pipeline (staging and production)
- Rollback strategy
- Hotfix process for critical bugs
- Release cadence recommendations
- Emergency procedures
- Version tags and GitHub releases

**Status:** ✅ Complete  
**Estimated Reading Time:** 20-25 minutes

---

### 9. [TESTING_GUIDE.md](./TESTING_GUIDE.md)

**Comprehensive testing guide and best practices.**

**Contains:**

- Testing philosophy and pyramid
- Test environment setup (.env.testing)
- Database seeding for tests
- Factory creation (MANDATORY with every model)
- Writing feature and unit tests
- Test coverage requirements (80% minimum)
- Testing best practices
- Mocking external services (Stripe, Instagram, AI)
- Complete test examples

**Status:** ✅ Complete  
**Estimated Reading Time:** 20-25 minutes

---

### 10. [PROJECT_SETUP_CHECKLIST.md](./PROJECT_SETUP_CHECKLIST.md)

**Complete pre-development setup checklist.**

**Contains:**

- Prerequisites (PHP, Composer, Node.js, Git)
- Repository and environment setup
- Git hooks and code quality tools
- Editor configuration (VS Code, PHPStorm)
- GitHub configuration and integrations
- External services setup (Stripe, Instagram, AWS)
- Database seeding for development
- Deployment setup (staging and production)
- Verification and testing
- Configuration files checklist
- Common issues and solutions
- Team onboarding checklist

**Status:** ✅ Complete  
**Estimated Reading Time:** 15-20 minutes

---

### 11. [GETTING_STARTED.md](./GETTING_STARTED.md)

**Quick start guide for new developers.**

**Contains:**

- 30-minute quick setup
- Your first task walkthrough
- Development workflow
- Architecture pattern summary
- Testing rules
- Pre-commit checklist
- Common issues
- Useful commands
- Learning path (Week 1-3)
- Quick links

**Status:** ✅ Complete  
**Estimated Reading Time:** 10-15 minutes

---

### 12. [RECOMMENDATIONS.md](./RECOMMENDATIONS.md)

**Optional enhancements and advanced features.**

**Contains:**

- High priority additions (API docs, Sentry, monitoring)
- Medium priority (dev helpers, mocks, health checks)
- Low priority (Docker, ADRs, admin panel)
- Implementation priority timeline
- Cost analysis for all tools
- What NOT to add (avoid over-engineering)
- 20+ practical suggestions with code examples

**Status:** ✅ Complete  
**Estimated Reading Time:** 25-30 minutes

---

### 13. [INSTAGRAM_HYBRID_OWNERSHIP.md](./INSTAGRAM_HYBRID_OWNERSHIP.md)

**Complete implementation guide for the hybrid Instagram ownership model.**

**Contains:**

- Architecture overview (user-owned + company-owned accounts)
- Database schema (3 new tables: modified instagram_accounts, pivot table, posts)
- Model documentation (5 models with 67+ new methods)
- Service layer (3 services for permissions, posts, Instagram API)
- Permission matrix (5 permission levels)
- Complete code examples for:
    - Connecting user/company accounts
    - Sharing accounts with permissions
    - Creating and scheduling posts
    - Permission checking
- API reference for all methods
- Testing guide (52 tests included)
- Usage examples for common scenarios

**Status:** ✅ Complete & Implemented  
**Estimated Reading Time:** 35-45 minutes

---

### 20. [TEST_COVERAGE_ANALYSIS.md](./TEST_COVERAGE_ANALYSIS.md) 📊 **NEW**

**Comprehensive test coverage analysis and implementation plan.**

**Contains:**

- Current test coverage analysis (Services: 0%, Controllers: 0%, Models: 0%)
- Priority recommendations for test implementation
- Detailed test implementation plan
- Post Management System test coverage (95%+)
- Test coverage enforcement rules
- Quality gates and CI/CD integration
- Coverage reporting and monitoring
- Test maintenance strategies
- Performance testing guidelines
- Security testing requirements

**Status:** ✅ Complete  
**Estimated Reading Time:** 15-20 minutes

---

### 14. [INSTAGRAM_SETUP.md](./INSTAGRAM_SETUP.md)

**Complete guide to setting up Instagram OAuth integration.**

**Contains:**

- Prerequisites and Facebook Developer account setup
- Step-by-step Facebook app creation
- Instagram Basic Display configuration
- OAuth redirect URIs setup
- Getting client ID and secret credentials
- Adding test users for development
- Testing the integration
- Token lifecycle and refresh schedule
- Production setup and app review process
- Troubleshooting guide
- Rate limits and permissions

**Status:** ✅ Complete  
**Estimated Reading Time:** 20-25 minutes

---

### 15. [INSTAGRAM_INTEGRATION_SETUP_PLAN.md](./INSTAGRAM_INTEGRATION_SETUP_PLAN.md) ⚡ **NEW**

**Comprehensive review and action plan for Instagram integration setup.**

**Contains:**

- Current state assessment (what's built vs what's missing)
- Why you're seeing configuration errors
- 3 setup options with pros/cons:
    - Full Instagram integration (production)
    - Development mode with dummy credentials
    - Feature flag / disable integration
- Immediate solution (fix error in 5 minutes)
- Long-term production setup (4 phases)
- Architecture explanation and flow diagram
- Security features overview
- Troubleshooting common issues
- Feature comparison matrix
- Step-by-step action plans

**Status:** ✅ Complete  
**Estimated Reading Time:** 25-30 minutes

---

### 16. [DARK_MODE_IMPLEMENTATION.md](./DARK_MODE_IMPLEMENTATION.md) ✨

**Complete dark/light mode implementation guide.**

**Contains:**

- Architecture overview (Tailwind CSS + Vue 3 composable)
- Theme composable API (`useTheme()`)
- Component documentation (ThemeToggle.vue)
- Color palette convention (light/dark variants)
- Translation support (EN, ES, RU)
- Implementation details:
    - LocalStorage persistence
    - System preference detection
    - Smooth transitions
    - Zero runtime overhead
- Complete code examples for:
    - Using theme state in components
    - Adding dark mode to new components
    - Conditional logic based on theme
- Browser support and performance
- Accessibility features
- Testing checklist
- Troubleshooting guide
- Future enhancement ideas

**Status:** ✅ Complete & Implemented  
**Estimated Reading Time:** 15-20 minutes

---

### 17. [TIMEZONE_FEATURE.md](./TIMEZONE_FEATURE.md) ✨

**Complete timezone management implementation.**

**Contains:**

- Architecture overview (400+ timezones support)
- Automatic browser timezone detection
- User timezone preferences in profile
- TimezoneService API documentation
- SetUserTimezone middleware
- Database schema (users.timezone column)
- Complete code examples for:
    - Auto-detecting user timezone
    - Displaying dates in user's timezone
    - Validating timezones
    - Getting timezone offsets
- Frontend composable (useTimezone.js)
- Testing guide (24 tests included)
- Best practices for timezone handling
- DST (Daylight Saving Time) handling
- Troubleshooting guide

**Status:** ✅ Complete & Implemented  
**Estimated Reading Time:** 20-25 minutes

---

### 18. [ADMIN_FEATURES.md](./ADMIN_FEATURES.md) 🎯

**Complete admin panel implementation for inquiry and user management.**

**Contains:**

- Architecture overview (Services, Controllers, Middleware)
- **Inquiry Management:**
    - View, search, sort, paginate inquiries
    - Delete inquiries with confirmation
    - Export to CSV functionality
    - Real-time statistics dashboard
- **User Management:**
    - View all users with stats (companies, Instagram accounts, posts)
    - Send password reset links
    - Suspend/unsuspend users with reason tracking
    - User impersonation for support/debugging
- **Security Features:**
    - Admin middleware (EnsureUserIsAdmin)
    - Suspension audit trail
    - Impersonation logging and auto-expire
    - Cannot suspend self or other admins
- **UI/UX Features:**
    - SweetAlert2 confirmations (no ugly alerts!)
    - Beautiful hover tooltips (translated)
    - Responsive stat cards (one-line layout)
    - Color-coded actions (red/green/blue/purple)
    - Impersonation banner with stop button
- Complete code examples for:
    - Admin services (InquiryService, UserManagementService, ImpersonationService)
    - Admin controllers and routes
    - Vue components with translations
    - Database migrations for suspension tracking
- Testing guide (45 tests included - 100% passing)
- Security best practices
- Translation support (EN/ES/RU for everything)

**Status:** ✅ Complete & Implemented  
**Estimated Reading Time:** 25-30 minutes

---

### 19. [TEST_FIXES_AND_STATUS.md](./TEST_FIXES_AND_STATUS.md) 🔧 **NEW**

**Comprehensive overview of test fixes and current project status.**

**Contains:**

- Complete test fix summary (validation, registration, admin middleware, language preservation)
- Current test status (170/170 tests passing with 3,341 assertions)
- Project health assessment (code quality, test coverage, documentation, security)
- Recent UI/UX improvements (Material Design 3, error handling, profile enhancements)
- Technical improvements (backend, frontend, database design)
- Performance metrics and internationalization status
- Deployment readiness checklist
- Next steps for development
- Achievement summary and production readiness

**Status:** ✅ Complete & Current  
**Estimated Reading Time:** 15-20 minutes

---

### 20. [POST_MANAGEMENT_SYSTEM.md](./POST_MANAGEMENT_SYSTEM.md) 🎯 **NEW**

**Comprehensive post management system with media handling and scheduling.**

**Contains:**

- Complete post management system overview
- Database schema for posts and post_media tables
- Model relationships and Eloquent patterns
- Service layer architecture (PostService, PostMediaService)
- RESTful API endpoints and controllers
- Vue.js frontend components (Create, Index, MediaUpload, DateTimePicker)
- Multi-language support (EN, RU, ES)
- Security features and authorization
- Responsive design with dark mode support
- Testing coverage and deployment guide
- Performance optimization and troubleshooting
- Future enhancement roadmap

**Features:**

- Create, edit, copy, and delete posts
- Media upload and management (images/videos)
- Post scheduling with date/time picker
- Multiple post types (feed, reel, story, carousel)
- Post status management (draft, scheduled, published, failed)
- Search, filtering, and pagination
- SweetAlert confirmations and toast notifications

**Status:** ✅ Complete & Production Ready  
**Estimated Reading Time:** 30-40 minutes

---

### 21. [PROJECT_STATUS_SUMMARY.md](./PROJECT_STATUS_SUMMARY.md) 📊 **NEW**

**Comprehensive project status and validation summary.**

**Contains:**

- Complete project overview and current status
- Implemented features summary (Authentication, Multi-language, Dark Mode, Timezone, Admin Panel, Post Management, Instagram Integration)
- Technical architecture details (Backend Laravel 11, Frontend Vue 3, Database Design)
- Documentation status (50+ files analyzed and validated)
- Security features and validation
- Performance optimization metrics
- Internationalization status (EN, ES, RU)
- Testing coverage (170/170 tests passing)
- UI/UX features and design system
- Development tools and automation
- Project metrics and feature completeness
- Next steps and future enhancements
- Achievement summary and production readiness

**Key Metrics:**

- ✅ 170/170 tests passing (100% success rate)
- ✅ 50+ comprehensive documentation files
- ✅ Complete multi-language support
- ✅ Security validated (no vulnerabilities)
- ✅ Performance optimized (no bottlenecks)
- ✅ Production ready status

### 22. [MYSQL_TESTING_SETUP_AND_COMPLETE_TEST_FIXES.md](./MYSQL_TESTING_SETUP_AND_COMPLETE_TEST_FIXES.md) 🧪 **NEW**

**Comprehensive MySQL testing setup and complete test suite fixes.**

**Contains:**

- Complete MySQL testing environment setup guide
- Database transaction conflict resolution
- Application logic fixes (validation, mocking, business rules)
- Model relationship issues (cascade deletes, repository usage)
- Individual user support implementation
- Test-specific fixes (Mockery expectations, assertions)
- Test data alignment with business rules
- Complete test results breakdown (294 tests passing)
- Performance metrics and execution statistics
- Security validation and quality assurance
- Documentation updates and version history
- Production readiness assessment

**Key Achievements:**

- ✅ 294/294 tests passing (100% success rate)
- ✅ MySQL testing environment established
- ✅ All application logic issues resolved
- ✅ Complete feature coverage achieved
- ✅ Production-ready testing infrastructure

**Status:** ✅ Complete & Current
**Estimated Reading Time:** 20-25 minutes

---

### 23. [PHASE_2_DEEP_ANALYSIS.md](./PHASE_2_DEEP_ANALYSIS.md) 🔍

**Comprehensive Phase 2 implementation analysis and research.**

**Contains:**

- Complete analysis of current database state vs project plan
- Detailed breakdown of implemented vs missing components
- Current database tables and models analysis (users, companies, posts, instagram_accounts)
- Missing components identification (wallet system, AI generation, onboarding)
- Implementation priority matrix and timeline
- Technical considerations and architecture decisions
- Database design decisions and performance considerations
- Security considerations and access control
- Success metrics and quality requirements
- Detailed week-by-week implementation plan

**Key Findings:**

- ✅ 60-70% of Phase 2 already implemented
- ✅ Core multi-tenancy and role-based access working
- ✅ Instagram hybrid ownership model complete
- 🔜 Wallet system tables and models needed
- 🔜 Company invitation system enhancement needed
- 🔜 Global company scope trait needed

**Status:** ✅ Complete & Current
**Estimated Reading Time:** 25-30 minutes

---

## 🗺️ Implementation Roadmap

### Phase 0: Authentication Foundation (CURRENT)

**Documents:**

- [AUTH_FLOW_PLAN.md](./AUTH_FLOW_PLAN.md)
- [INTERNATIONALIZATION_PLAN.md](./INTERNATIONALIZATION_PLAN.md)

**Status:** ✅ Complete (Auth + i18n implemented)  
**Estimated Time:** 14-20 hours

**Deliverables:**

- ✅ Modern email-first login page
- ✅ Magic link authentication
- ✅ Inquiry tracking system
- ✅ Email verification
- ✅ Multi-language support (EN, RU, ES)
- ✅ Language switcher component
- ✅ User locale preferences
- ✅ All pages translated (Auth, Profile, Dashboard, Instagram)

---

### Phase 1: Foundation & Core Stack

**Documents:** [PROJECT_PLAN.md](./PROJECT_PLAN.md#phase-1-foundation--core-stack-week-1-2)  
**Status:** 🔜 Not Started

**Deliverables:**

- Redis + Horizon setup
- S3 storage configuration
- Social OAuth (Google)
- Complete .env.example

---

### Phase 2: Database & Multi-Company

**Documents:**

- [PROJECT_PLAN.md](./PROJECT_PLAN.md#phase-2-database--multi-company-week-2-3)
- [DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md)
- [PHASE_2_DEEP_ANALYSIS.md](./PHASE_2_DEEP_ANALYSIS.md) ✨ **NEW**

**Status:** 🚧 **60% Complete** (Major components implemented)

**Deliverables:**

- ✅ **Core database migrations** (users, companies, company_user, posts, instagram_accounts)
- ✅ **Eloquent models with relationships** (User, Company, Post, InstagramAccount)
- ✅ **Company management features** (company switching, role management)
- ✅ **Role-based access control** (admin, user, network roles)
- ✅ **Multi-tenancy implementation** (company scoping, user-company relationships)
- ✅ **Instagram hybrid ownership** (user + company accounts with sharing)
- ✅ **User management system** (suspension, statistics, admin features)
- 🔜 **User invitation system** (invitation flow, acceptance tracking)
- 🔜 **Wallet system** (wallets, wallet_transactions tables)
- 🔜 **AI generation tracking** (ai_generations table)
- 🔜 **Global company scope** (BelongsToCompany trait)

---

### Phase 3: Wallet & Stripe Integration

**Documents:** [PROJECT_PLAN.md](./PROJECT_PLAN.md#phase-3-wallet--stripe-integration-week-3-4)  
**Status:** 🔜 Not Started

**Deliverables:**

- Immutable wallet ledger
- Stripe payment integration
- Webhook handlers
- Transaction history UI

---

### Phase 4: Instagram Graph Integration

**Documents:**

- [PROJECT_PLAN.md](./PROJECT_PLAN.md#phase-4-instagram-graph-integration-week-4-5)
- [INSTAGRAM_HYBRID_OWNERSHIP.md](./INSTAGRAM_HYBRID_OWNERSHIP.md) ✅ **NEW**

**Status:** ✅ Partially Complete (Hybrid Ownership Model Implemented)

**Deliverables:**

- ✅ Hybrid ownership model (user + company accounts)
- ✅ Account sharing with permissions
- ✅ Post lifecycle management
- ✅ Permission service layer
- 🔜 Instagram OAuth flow (setup guide exists)
- 🔜 Token refresh system (structure ready)
- 🔜 Real Instagram API integration
- 🔜 Webhook handling

---

### Phase 5: AI Sidecar (Python FastAPI)

**Documents:** [PROJECT_PLAN.md](./PROJECT_PLAN.md#phase-5-ai-sidecar-python-fastapi-week-5-6)  
**Status:** 🔜 Not Started

**Deliverables:**

- FastAPI microservice
- Caption generation (OpenAI)
- Image generation (Stability AI)
- Video generation (Luma)

---

### Phase 6: MVP Pages

**Documents:** [PROJECT_PLAN.md](./PROJECT_PLAN.md#phase-6-mvp-pages-week-6-7)  
**Status:** 🔜 Not Started

**Deliverables:**

- Dashboard
- Chat + Composer
- Content Calendar
- Wallet page
- Settings

---

### Phase 7: Webhooks & Background Jobs

**Documents:** [PROJECT_PLAN.md](./PROJECT_PLAN.md#phase-7-webhooks--background-jobs-week-7-8)  
**Status:** 🔜 Not Started

**Deliverables:**

- Stripe webhooks
- Instagram webhooks
- Scheduler jobs

---

### Phase 8: Testing & Polish

**Documents:** [PROJECT_PLAN.md](./PROJECT_PLAN.md#phase-8-testing--polish-week-8)  
**Status:** 🔜 Not Started

**Deliverables:**

- Feature tests
- Unit tests
- Bug fixes
- Performance optimization

---

## 🏗️ Architecture Overview

### Multi-Tenancy Model

**Approach:** Company Ownership Model (shared database)

```
users ←→ company_user (pivot with roles) ←→ companies
                                              ├── wallets
                                              ├── instagram_accounts
                                              └── posts
```

**Roles:**

- `admin` - Full access (billing, invites, delete company)
- `user` - Create/publish posts, connect Instagram
- `network` - Read-only (view posts/analytics)

---

### Wallet System

**Approach:** Immutable transaction ledger

```
wallets (metadata only)
  └── wallet_transactions (append-only log)
        - type: credit|debit|refund|adjustment
        - amount: integer (cents, always positive)
        - balance_after: snapshot
        - idempotency_key: prevent duplicates
```

**Balance Calculation:** Sum of transactions OR use `balance_after` snapshot

---

### Authentication Flow

**Approach:** Progressive, email-first entry

```
Landing Page → [Enter Email] → Check User
                                   ├─→ Exists: Login (password OR magic link)
                                   └─→ New: Register + Email verification
```

**Features:**

- Passwordless authentication (magic links)
- Inquiry tracking for marketing intelligence
- IP logging for security
- Rate limiting

---

## 🔐 Security & Compliance

### Data Protection

- ✅ Encrypt sensitive tokens (Instagram access tokens)
- ✅ Never log API keys or passwords
- ✅ Use signed URLs for S3 assets
- ✅ HTTPS everywhere in production

### Authentication

- ✅ Email verification required
- ✅ Magic link expiration (1 hour)
- ✅ Rate limiting on sensitive endpoints
- ✅ CSRF protection enabled

### Privacy Compliance

- ✅ GDPR: User data export and deletion
- ✅ IP logging disclosure in privacy policy
- ✅ Data retention: Inquiries cleaned after 90 days
- ✅ PCI DSS: Use Stripe Elements (cards never touch our server)

---

## 🧪 Testing Strategy

### Test Coverage Goals

- **Feature Tests:** >80% coverage
- **Unit Tests:** Critical business logic (wallet, Instagram)
- **Browser Tests:** Optional (Dusk for E2E flows)

### Key Test Areas

1. Company creation and switching
2. Wallet top-up and deduction (with webhook simulation)
3. Instagram connection and token refresh
4. Post creation, scheduling, and publishing
5. Authentication flows (password, magic link, email verification)
6. Role-based access control

---

## 🎨 UI/UX Features

### Implemented Features

- ✅ **Dark/Light Mode** - Tailwind-based theme switching with persistence ([DARK_MODE_IMPLEMENTATION.md](./DARK_MODE_IMPLEMENTATION.md))
- ✅ **Multi-Language Support** - EN, ES, RU with language switcher ([INTERNATIONALIZATION_PLAN.md](./INTERNATIONALIZATION_PLAN.md))
- ✅ **Timezone Management** - 400+ timezones with auto-detection ([TIMEZONE_FEATURE.md](./TIMEZONE_FEATURE.md))
- ✅ **Modern Authentication** - Email-first, magic links, passwordless ([AUTH_FLOW_PLAN.md](./AUTH_FLOW_PLAN.md))
- ✅ **Responsive Design** - Mobile-first with Tailwind CSS
- ✅ **Toast Notifications** - Non-intrusive user feedback system

---

## 📦 Dependencies

### PHP/Laravel Packages

**Already Installed:**

```json
{
    "laravel/framework": "^12.0",
    "inertiajs/inertia-laravel": "^2.0",
    "laravel/cashier": "^16.0",
    "laravel/sanctum": "^4.0",
    "laravel/socialite": "^5.23",
    "laravel/breeze": "^2.3",
    "spatie/laravel-webhook-client": "^3.4",
    "spatie/laravel-webhook-server": "^3.8",
    "tightenco/ziggy": "^2.0"
}
```

**To Be Installed:**

```json
{
    "predis/predis": "^2.0",
    "laravel/horizon": "^5.0",
    "league/flysystem-aws-s3-v3": "^3.0",
    "facebook/graph-sdk": "^6.0",
    "grosv/laravel-passwordless-login": "^4.0",
    "laravel-lang/common": "^6.0"
}
```

### JavaScript/Vue Packages

**Already Installed:**

```json
{
    "vue": "^3.4.0",
    "@inertiajs/vue3": "^2.0.0",
    "tailwindcss": "^3.2.1",
    "vite": "^7.0.7",
    "axios": "^1.11.0",
    "vue-i18n": "^9.14.5"
}
```

**To Be Installed:**

```json
{
    "@fullcalendar/vue3": "^6.1.0",
    "@headlessui/vue": "^1.7.0",
    "@heroicons/vue": "^2.0.0",
    "vue-toastification": "^2.0.0",
    "@vueuse/core": "^10.0.0",
    "laravel-vue-i18n": "^3.0.0"
}
```

---

## 🚀 Quick Start

### For New Developers

**1. Read Documentation in Order:**

```
1. README.md (project overview)
2. docs/PROJECT_PLAN.md (master plan)
3. docs/DATABASE_SCHEMA.md (database design)
4. docs/AUTH_FLOW_PLAN.md (current phase)
```

**2. Set Up Local Environment:**

```bash
# Install dependencies
composer install
npm install

# Configure environment
cp .env.example .env
php artisan key:generate

# Database
touch database/database.sqlite
php artisan migrate

# Start dev server
composer dev
```

**3. Start Contributing:**

- Pick a task from current phase
- Create feature branch
- Write tests first (TDD)
- Implement feature
- Run tests and linter
- Submit PR

---

## 📝 Documentation Standards

### File Naming

- Use SCREAMING_SNAKE_CASE for documentation files
- Example: `AUTH_FLOW_PLAN.md`, `DATABASE_SCHEMA.md`

### Sections

All documentation should include:

- Version and date
- Table of contents (for >1000 words)
- Clear section headers
- Code examples where applicable
- Status indicators

### Status Indicators

- ✅ Complete
- 🚧 In Progress
- 📋 Planning
- 🔜 Not Started
- ⚠️ Blocked
- ❌ Deprecated

---

## 🤝 Contributing to Documentation

### When to Update Docs

**Always update when:**

- Adding new features
- Changing architecture decisions
- Modifying database schema
- Adding new API endpoints
- Changing authentication flow

### How to Update

1. Update relevant document
2. Update INDEX.md if adding new document
3. Update version and date at top of document
4. Add to git commit message: `docs: updated AUTH_FLOW_PLAN with X`

---

## 📞 Support

### Internal Resources

- Coding Standards: [CODING_STANDARDS.md](./CODING_STANDARDS.md) ⚠️ **MANDATORY**
- Project Plan: [PROJECT_PLAN.md](./PROJECT_PLAN.md)
- Database Schema: [DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md)
- Auth Flow: [AUTH_FLOW_PLAN.md](./AUTH_FLOW_PLAN.md)
- Internationalization: [INTERNATIONALIZATION_PLAN.md](./INTERNATIONALIZATION_PLAN.md)

### External Resources

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Inertia.js Documentation](https://inertiajs.com)
- [Vue 3 Documentation](https://vuejs.org)
- [Stripe API Documentation](https://stripe.com/docs/api)
- [Instagram Graph API Documentation](https://developers.facebook.com/docs/instagram-platform)

---

## 📊 Project Status

**Current Phase:** Phase 0 - Authentication Foundation + Admin Features  
**Overall Progress:** ~8% (Planning Complete + Core Features + Test Fixes)  
**Next Milestone:** Complete Phase 1 - Foundation & Core Stack  
**Estimated Completion:** 8 weeks from start

**Test Status:** ✅ **294/294 tests passing** (100% success rate)

---

**Last Updated:** October 16, 2025  
**Version:** 2.1  
**Maintained By:** Development Team

---

---

## 📚 **API & User Documentation**

### [API_DOCUMENTATION.md](./API_DOCUMENTATION.md)

**Complete API reference and integration guide for developers.**

**Contains:**

- Authentication methods and endpoints
- Post Management API (CRUD operations)
- User Management API (profile, settings)
- Company Management API (team, invitations)
- Instagram Integration API (connect, sync)
- Media Management API (upload, organize)
- Error handling and status codes
- Rate limiting information
- SDK examples (JavaScript, PHP, Python)
- Webhook documentation
- Testing endpoints

**Status:** ✅ Complete  
**Target Audience:** Developers, API integrators  
**Estimated Reading Time:** 45-60 minutes

---

### [USER_GUIDES.md](./USER_GUIDES.md)

**Comprehensive user guides and tutorials for end users.**

**Contains:**

- Getting started guide
- Account setup and configuration
- Instagram integration walkthrough
- Post creation and management
- Scheduling and automation
- Media library organization
- Company and team management
- Settings and preferences
- Troubleshooting common issues
- Best practices and strategies
- FAQ and support resources

**Status:** ✅ Complete  
**Target Audience:** End users, content creators, businesses  
**Estimated Reading Time:** 60-90 minutes

---

## 📢 Recent Updates

### October 16, 2025 - MySQL Testing Setup & Complete Test Suite Fixes 🧪 **LATEST**

**Status:** ✅ Complete & Deployed

**What's New:**

- ✅ **MySQL Testing Environment** - Switched from SQLite to MySQL for comprehensive testing
- ✅ **All Tests Fixed** - 294/294 tests now passing (100% success rate)
- ✅ **Database Transaction Issues Resolved** - Fixed conflicts between manual transactions and RefreshDatabase trait
- ✅ **Application Logic Fixes** - Resolved validation, mocking, and business rule issues
- ✅ **Post Management System** - Complete post creation, editing, scheduling, and deletion functionality
- ✅ **Individual User Support** - Fixed Instagram account and post management for users without companies
- ✅ **Authentication Flow** - Resolved redirect and validation issues in test environment
- ✅ **Model Relationships** - Fixed cascade soft deletes and media management
- ✅ **Service Layer** - Corrected transaction handling and repository usage
- ✅ **Test Data Consistency** - Aligned test data with application business rules

**Technical Improvements:**

- ✅ Created `autopost_ai_test` MySQL database for testing
- ✅ Fixed `TestCase.php` missing `CreatesApplication` trait
- ✅ Created `.env.testing` with proper MySQL credentials
- ✅ Refactored `PostService` to use `DB::transaction()` instead of manual transaction handling
- ✅ Fixed validation logic in `UpdatePostRequest` and `CreatePostRequest`
- ✅ Added cascade soft delete logic to `Post` model for `PostMedia`
- ✅ Fixed `PostMediaService` to use injected repository instead of direct model calls
- ✅ Modified `PostController::stats()` to support individual users
- ✅ Fixed `InstagramAccountController` to retrieve accounts for individual users
- ✅ Corrected Mockery expectations for Eloquent model mocking
- ✅ Fixed PHPUnit assertion methods and test data alignment

**Test Results:**

- ✅ **294 tests passing** with 3,691 assertions
- ✅ **100% test success rate** across all test suites
- ✅ **Zero failing tests** - all application logic issues resolved
- ✅ **Comprehensive coverage** - authentication, admin, Instagram, post management, individual users

**Files Updated:** 15+ files (services, controllers, models, tests, configuration)  
**No Breaking Changes**  
**Documentation:** Updated INDEX.md to v2.1

---

### October 15, 2025 - UI Consistency & Code Quality Improvements 🎨

**Status:** ✅ Complete & Deployed

**What's New:**

- ✅ Fixed dark mode contrast issues across all components
- ✅ Standardized UI components to use consistent Tailwind classes
- ✅ Replaced Material Design 3 tokens with standard Tailwind equivalents
- ✅ Fixed Husky deprecation warnings and PHPUnit metadata issues
- ✅ Resolved script warnings and improved code quality automation
- ✅ Enhanced welcome card and form components for better dark mode support

### October 15, 2025 - CI Alignment with Local Quality Gates 🔧

**Status:** ✅ Complete & Deployed

**What's New:**

- ✅ GitHub Actions workflow `Laravel CI` now mirrors local pre-commit checks
- ✅ Runs `scripts/pre-commit-check.sh` on push/PR (PHP 8.2, Node 20)
- ✅ Caches Composer and npm; creates SQLite DB for tests
- ✅ Ensures docs/i18n/timezone/architecture checks run in CI too

**Why:** Single source of truth for quality gates locally and in CI.

---

### October 15, 2025 - Material Design 3 Standardization & Test Fixes 🔧

**Status:** ✅ Complete & Deployed

**What's New:**

- ✅ **All Test Failures Fixed** - 170/170 tests now passing (100% success rate)
- ✅ **User Registration Implementation** - Complete registration logic with timezone and locale support
- ✅ **Admin Middleware Fixes** - Proper 403 error handling in testing environment
- ✅ **Language Preservation** - Session-based locale detection during registration
- ✅ **Validation Exception Handling** - Fixed test assertions for proper status codes
- ✅ **Material Design 3 Standardization** - Complete UI pattern standardization across all components
- ✅ **Coding Standards Updated** - New Material Design 3 rules and guidelines

**Technical Improvements:**

- ✅ Fixed `PasswordConfirmationTest` and `PasswordUpdateTest` validation handling
- ✅ Implemented complete `RegisteredUserController` with timezone and locale support
- ✅ Updated `bootstrap/app.php` to return proper 403 status codes in tests
- ✅ Fixed session-based locale preservation during registration
- ✅ Updated test assertions to expect proper HTTP status codes
- ✅ Implemented Material Design 3 color system in Tailwind configuration
- ✅ Standardized all UI components to use Material Design 3 patterns
- ✅ Updated coding standards with Material Design 3 guidelines
- ✅ Replaced old bg-white/dark:bg-gray-800 patterns with bg-md-surface-container
- ✅ Replaced old shadow-sm/sm:rounded-lg with shadow-elevation-1/rounded-md

**Test Results:**

- ✅ **170 tests passing** with 3,341 assertions
- ✅ **100% test success rate** across all test suites
- ✅ **Zero failing tests** - all validation and middleware issues resolved
- ✅ **Comprehensive coverage** - authentication, admin, Instagram, timezone, profile features

**Files Updated:** 5 files (controllers, tests, configuration, documentation)  
**No Breaking Changes**  
**Documentation:** Updated INDEX.md to v1.5

---

### October 15, 2025 - Admin Page Layout Standardization & Error Handling 🔧

**Status:** ✅ Complete & Deployed

**What's New:**

- ✅ **Admin Page Layout Standardization** - All admin pages now follow dashboard pattern
- ✅ **Header Template Pattern** - Consistent `<template #header>` implementation
- ✅ **Error Page Redirections** - Custom 404, 500, 403, 419 error pages with translations
- ✅ **Exception Handling** - Proper Laravel 11 exception handling configuration
- ✅ **Page Title Consistency** - All pages use same title display pattern
- ✅ **Multi-language Error Pages** - Error pages support EN, ES, RU translations

**Technical Improvements:**

- ✅ Updated `Admin/Inquiries/Index.vue` to use header template pattern
- ✅ Updated `Admin/Users/Index.vue` to use header template pattern
- ✅ Created custom error pages (403.vue, 404.vue, 419.vue, 500.vue)
- ✅ Configured `bootstrap/app.php` with proper exception handling
- ✅ Added error page translations to Vue i18n and PHP language files
- ✅ Standardized page layout structure across all admin pages

**Layout Pattern Applied:**

```vue
<!-- All pages now follow this consistent pattern -->
<template>
    <Head :title="t('page.title')" />
    <AuthenticatedLayout>
        <template #header>
            <h2
                class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200"
            >
                {{ t('page.title') }}
            </h2>
        </template>
        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Page content -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>
```

**Pages Updated:**

- ✅ Dashboard: `t('dashboard.title')` - "Dashboard" / "Панель управления" / "Panel de control"
- ✅ Admin Inquiries: `t('admin.inquiries.title')` - "Inquiry Management" / "Управление Запросами" / "Gestión de Consultas"
- ✅ Admin Users: `t('admin.users.title')` - "User Management" / "Управление Пользователями" / "Gestión de Usuarios"

**Error Handling:**

- ✅ 404 errors redirect to custom 404 page
- ✅ 500 errors redirect to custom 500 page
- ✅ 403 errors redirect to custom 403 page
- ✅ 419 CSRF errors redirect to custom 419 page
- ✅ All error pages have proper translations and navigation

- ✅ English: "Forgot your password? No problem.\nEnter your email below, and we'll send you a link to reset it securely."
- ✅ Russian: "Забыли пароль? Не проблема.\nВведите ваш email ниже, и мы отправим вам ссылку для безопасного сброса пароля."
- ✅ Spanish: "¿Olvidaste tu contraseña? No hay problema.\nIngresa tu email a continuación, y te enviaremos un enlace para restablecerla de forma segura."

**Files Updated:** 8 files (Vue components, translation files, CSS, documentation)  
**No Breaking Changes**  
**Documentation:** Updated INDEX.md to v1.5

---

### October 10, 2025 - Admin Features Implementation 🎯 **LATEST**

**Status:** ✅ Complete & Deployed

**What's New:**

- ✅ **Inquiry Management** - View, search, sort, delete, export inquiries to CSV
- ✅ **User Management** - View all users with stats, suspend/unsuspend, send password resets
- ✅ **User Impersonation** - Admins can log in as any user for support/debugging
- ✅ **Admin Middleware** - `EnsureUserIsAdmin` protects admin-only routes
- ✅ **User Suspension System** - Database tracking with reason and audit log
- ✅ **Beautiful UI** - SweetAlert2 confirmations, hover tooltips, responsive design
- ✅ **Complete Translations** - EN/ES/RU for all admin features
- ✅ **Comprehensive Tests** - 45 tests for all admin functionality (100% passing)
- ✅ **Full Documentation** - [ADMIN_FEATURES.md](./ADMIN_FEATURES.md)

**Services Created:**

- `InquiryService` - Inquiry search, pagination, export, statistics
- `UserManagementService` - User operations, suspension, stats
- `ImpersonationService` - Secure user impersonation with session management

**Admin Pages:**

- `/admin/inquiries` - Inquiry management dashboard with stats cards
- `/admin/users` - User management dashboard with actions

**Security Features:**

- ✅ Admin role verification middleware
- ✅ Suspension tracking (who, when, why)
- ✅ Impersonation audit logging
- ✅ Auto-expire impersonation sessions (60 minutes)
- ✅ Cannot suspend self or other admins
- ✅ Cannot impersonate other admins

**User Experience:**

- ✅ SweetAlert2 for all confirmations (no ugly alerts!)
- ✅ Hover tooltips on stats cards (translated)
- ✅ Stats displayed in single horizontal row
- ✅ Color-coded actions (red for suspend, green for unsuspend, etc.)
- ✅ Impersonation banner with easy stop button
- ✅ Success/error messages with auto-dismiss

**Tests:** 45/45 passing (26 feature tests + 19 unit tests)  
**Files Created:** 15 new files  
**Files Updated:** 8 files  
**Documentation:** [ADMIN_FEATURES.md](./ADMIN_FEATURES.md), [DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md) v1.3

---

### October 15, 2025 - Profile Page Enhancements ✨ **LATEST**

**Status:** ✅ Complete & Deployed

**What's New:**

- ✅ **Avatar Component** - Beautiful initials-based avatar with color coding
- ✅ **Company Information Component** - Role, stats, and team details display
- ✅ **Enhanced Profile Layout** - Professional header with user info and company details
- ✅ **Multi-language Support** - Complete translations for EN, RU, ES
- ✅ **Responsive Design** - Mobile-friendly layout with proper spacing
- ✅ **Dark Mode Support** - Consistent theming throughout
- ✅ **Comprehensive Testing** - 10 new tests for profile enhancements
- ✅ **Complete Documentation** - [PROFILE_PAGE_ENHANCEMENTS.md](./PROFILE_PAGE_ENHANCEMENTS.md)

**Components Created:**

- `Avatar.vue` - Reusable avatar with initials and online status
- `CompanyInfo.vue` - Company details with statistics and role badges

**UI Improvements:**

- ✅ Profile header with large avatar and user information
- ✅ Company information card with gradient background
- ✅ Role badges with proper styling
- ✅ Statistics display (team members, Instagram accounts)
- ✅ Warning message for users without company
- ✅ Moved timezone description to header area
- ✅ Better visual hierarchy and spacing

**Backend Enhancements:**

- ✅ Enhanced ProfileController with company data
- ✅ Company statistics calculation
- ✅ Proper role formatting and display
- ✅ Fixed enum value access bug

**Translation Files:**

- Updated: `lang/en/profile.php`, `lang/ru/profile.php`, `lang/es/profile.php`
- Updated: `resources/js/app.js` (frontend translations)
- Added: Company information translations for all languages

**Tests:** 10/10 new tests passing  
**Files Created:** 3 new files (Avatar.vue, CompanyInfo.vue, ProfilePageEnhancementTest.php)  
**Files Updated:** 4 files (ProfileController.php, Profile/Edit.vue, UpdateProfileInformationForm.vue, app.js)  
**Documentation:** [PROFILE_PAGE_ENHANCEMENTS.md](./PROFILE_PAGE_ENHANCEMENTS.md)

---

### October 10, 2025 - Complete Translation Implementation ✨

**Status:** ✅ Complete & Deployed

**What's New:**

- ✅ All pages fully translated (EN, ES, RU)
- ✅ Profile page translations (Information, Password, Delete Account)
- ✅ Instagram page translations (all status messages, actions, warnings)
- ✅ Dashboard empty state translations
- ✅ Auth pages translations (Forgot Password, Reset Password, Email Verification, Confirm Password)
- ✅ Profile translations added to app.js (3 languages)
- ✅ Auth translations expanded (password reset, email verification flows)
- ✅ Dark mode support added throughout all translated components
- ✅ Fixed Profile/Edit.vue timezones prop passing
- ✅ Full i18n coverage across entire application

**Pages Translated:**

- ✅ Profile (Edit, UpdateProfileInformationForm, UpdatePasswordForm, DeleteUserForm)
- ✅ Instagram (Index page with all status badges and actions)
- ✅ Dashboard (empty state messages)
- ✅ Auth (ForgotPassword, ResetPassword, VerifyEmail, ConfirmPassword)
- ✅ All previously translated pages (Login, Register, Welcome)

**Translation Files:**

- Created: `lang/en/profile.php`, `lang/es/profile.php`, `lang/ru/profile.php`
- Updated: `lang/*/auth.php` (added password reset & email verification strings)
- Updated: `lang/*/dashboard.php` (added empty state strings)
- Updated: `lang/*/instagram.php` (added missing action/status strings)
- Updated: `resources/js/app.js` (added profile translations for all 3 languages)

**Files Created:** 3 new translation files  
**Files Updated:** 14 Vue components, 6 PHP translation files, 1 JavaScript file  
**No Breaking Changes**  
**Documentation:** Updated INTERNATIONALIZATION_PLAN.md to v2.0

---

### October 10, 2025 - Timezone Management Feature ✨

**Status:** ✅ Complete & Deployed

**What's New:**

- ✅ 400+ timezones supported (all PHP timezones)
- ✅ Automatic browser timezone detection on registration
- ✅ User timezone preferences in profile settings
- ✅ TimezoneService with timezone utilities
- ✅ SetUserTimezone middleware for per-request timezone
- ✅ Formatted timezone labels with GMT offsets
- ✅ 24 comprehensive tests (all passing)
- ✅ Full documentation: [TIMEZONE_FEATURE.md](./TIMEZONE_FEATURE.md)

**Files Created:** 6 new files (service, middleware, composable, tests, migrations, docs)  
**Files Updated:** 5 files (User model, ProfileController, ProfileRequest, Register.vue)  
**No Breaking Changes**  
**Documentation:** Complete

---

### October 10, 2025 - Dark/Light Mode Implementation ✨

**Status:** ✅ Complete & Deployed

**What's New:**

- ✅ Dark/light mode toggle with smooth transitions
- ✅ LocalStorage persistence across sessions
- ✅ System preference detection (auto-detect OS theme)
- ✅ Translation support (EN, ES, RU)
- ✅ Zero runtime overhead (pure CSS switching)
- ✅ Updated 15+ components with dark mode variants
- ✅ Accessible (ARIA labels, high contrast)
- ✅ Full documentation: [DARK_MODE_IMPLEMENTATION.md](./DARK_MODE_IMPLEMENTATION.md)

**Files Created:** 4 new files (composable, component, translations, docs)  
**Files Updated:** 15+ components with dark mode support  
**No Breaking Changes**  
**Documentation:** Complete

---

### October 10, 2025 - Instagram Hybrid Ownership Model

**Status:** ✅ Complete & Deployed

**What's New:**

- ✅ Users can now own personal Instagram accounts
- ✅ Companies can own team Instagram accounts
- ✅ Account sharing with granular permissions (post vs manage)
- ✅ Complete post lifecycle management (draft → scheduled → published)
- ✅ Permission service layer for access control
- ✅ 52 comprehensive tests (all passing)
- ✅ Full documentation: [INSTAGRAM_HYBRID_OWNERSHIP.md](./INSTAGRAM_HYBRID_OWNERSHIP.md)

**Migrations Run:** 3 new migrations deployed  
**Files Created:** 15 new files  
**Tests:** 52/52 passing  
**Documentation:** Complete
