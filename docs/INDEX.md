# Autopost AI - Documentation Index

**Project:** Autopost AI - AI-Powered Instagram Content Platform  
**Last Updated:** October 9, 2025  
**Version:** 1.0

---

## 📚 Documentation Structure

This directory contains comprehensive documentation for the Autopost AI project. All documents are written in Markdown and are organized by topic.

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
- Frontend setup (Vue i18n with laravel-vue-i18n)
- Language detection priority (URL → User → Session → Browser → Default)
- Database schema (user locale preference)
- Translation file structure (JSON + PHP arrays)
- Language switcher component
- Complete code examples for:
    - Middleware (SetLocale)
    - Controller (LocaleController)
    - Vue components (LanguageSwitcher)
    - Translation files for all 3 languages
- SEO considerations (hreflang tags)
- Date and number formatting
- Testing strategy

**Status:** ✅ Complete  
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
- Testing requirements
- Complete code examples for every layer

**Status:** ✅ Active - Mandatory  
**Estimated Reading Time:** 30-40 minutes

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

## 🗺️ Implementation Roadmap

### Phase 0: Authentication Foundation (CURRENT)

**Documents:**

- [AUTH_FLOW_PLAN.md](./AUTH_FLOW_PLAN.md)
- [INTERNATIONALIZATION_PLAN.md](./INTERNATIONALIZATION_PLAN.md)

**Status:** 📋 Planning  
**Estimated Time:** 14-20 hours

**Deliverables:**

- Modern email-first login page
- Magic link authentication
- Inquiry tracking system
- Email verification
- Multi-language support (EN, RU, ES)
- Language switcher component
- User locale preferences

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

**Status:** 🔜 Not Started

**Deliverables:**

- All database migrations
- Eloquent models with relationships
- Company management features
- User invitation system
- Role-based access control

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

**Documents:** [PROJECT_PLAN.md](./PROJECT_PLAN.md#phase-4-instagram-graph-integration-week-4-5)  
**Status:** 🔜 Not Started

**Deliverables:**

- Instagram OAuth flow
- Token refresh system
- Post publishing
- Webhook handling

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
    "axios": "^1.11.0"
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

**Current Phase:** Phase 0 - Authentication Foundation  
**Overall Progress:** ~5% (Planning Complete)  
**Next Milestone:** Complete authentication flow implementation  
**Estimated Completion:** 8 weeks from start

---

**Last Updated:** October 9, 2025  
**Maintained By:** Development Team
