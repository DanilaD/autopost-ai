# Project Setup Checklist

**Version:** 1.0  
**Date:** October 9, 2025  
**Status:** Pre-Development Setup Guide

---

## Overview

Complete checklist for setting up Autopost AI project before starting development. Follow in order.

---

## Phase 0: Prerequisites

### Local Environment

- [ ] **PHP 8.2+** installed

    ```bash
    php -v  # Should show 8.2 or higher
    ```

- [ ] **Composer** installed

    ```bash
    composer --version
    ```

- [ ] **Node.js 18+** and npm installed

    ```bash
    node -v  # Should show v18 or higher
    npm -v
    ```

- [ ] **Git** installed and configured

    ```bash
    git --version
    git config --global user.name "Your Name"
    git config --global user.email "your.email@example.com"
    ```

- [ ] **Database** (choose one):
    - SQLite (for development) ✅ Recommended
    - PostgreSQL (for production)
    - MySQL

- [ ] **Code Editor**:
    - VS Code (recommended) with extensions
    - PHPStorm
    - Other

---

## Phase 1: Repository & Environment Setup

### 1.1 Clone & Install

- [ ] **Clone repository**

    ```bash
    git clone <repository-url> autopost-ai
    cd autopost-ai
    ```

- [ ] **Install PHP dependencies**

    ```bash
    composer install
    ```

- [ ] **Install Node dependencies**
    ```bash
    npm install
    ```

### 1.2 Environment Configuration

- [ ] **Create `.env` file**

    ```bash
    cp .env.example .env
    ```

- [ ] **Generate application key**

    ```bash
    php artisan key:generate
    ```

- [ ] **Configure database in `.env`**

    ```env
    DB_CONNECTION=sqlite
    DB_DATABASE=/absolute/path/to/database/database.sqlite
    ```

- [ ] **Create SQLite database**

    ```bash
    touch database/database.sqlite
    ```

- [ ] **Run migrations**
    ```bash
    php artisan migrate
    ```

### 1.3 Testing Environment

- [ ] **Create `.env.testing` file**

    ```bash
    # Copy the template from docs
    # File should be tracked in git
    ```

- [ ] **Run tests to verify setup**
    ```bash
    php artisan test
    ```

### 1.4 Build Assets

- [ ] **Build frontend assets**

    ```bash
    npm run build
    ```

- [ ] **Start dev server (optional)**
    ```bash
    npm run dev
    ```

---

## Phase 2: Git Hooks & Code Quality

### 2.1 Git Hooks Setup

- [ ] **Install Husky**

    ```bash
    npm install --save-dev husky lint-staged
    npx husky install
    ```

- [ ] **Create pre-commit hook**

    ```bash
    npx husky add .husky/pre-commit "npx lint-staged"
    chmod +x .husky/pre-commit
    ```

- [ ] **Create pre-push hook**

    ```bash
    npx husky add .husky/pre-push "php artisan test"
    chmod +x .husky/pre-push
    ```

- [ ] **Test hooks work**
    ```bash
    git commit --allow-empty -m "test: verify hooks"
    ```

### 2.2 Linting Tools

- [ ] **Install ESLint & Prettier**

    ```bash
    npm install --save-dev eslint prettier eslint-plugin-vue \
      eslint-config-prettier @vue/eslint-config-prettier
    ```

- [ ] **Create `.eslintrc.json`** (see CODE_QUALITY_SETUP.md)

- [ ] **Create `.prettierrc.json`** (see CODE_QUALITY_SETUP.md)

- [ ] **Create `.prettierignore`** (see CODE_QUALITY_SETUP.md)

- [ ] **Test linting**
    ```bash
    npm run lint
    ./vendor/bin/pint
    ```

### 2.3 Static Analysis (Optional but Recommended)

- [ ] **Install PHPStan**

    ```bash
    composer require --dev phpstan/phpstan larastan/larastan
    ```

- [ ] **Create `phpstan.neon`** (see CODE_QUALITY_SETUP.md)

- [ ] **Run PHPStan**
    ```bash
    ./vendor/bin/phpstan analyse
    ```

---

## Phase 3: Editor Configuration

### 3.1 VS Code Setup

- [ ] **Install recommended extensions**:
    - PHP Intelephense
    - Laravel Extra Intellisense
    - Laravel Blade Snippets
    - Vue Language Features (Volar)
    - ESLint
    - Prettier
    - EditorConfig

- [ ] **Create `.vscode/settings.json`**

    ```json
    {
        "editor.formatOnSave": true,
        "editor.codeActionsOnSave": {
            "source.fixAll.eslint": true
        },
        "eslint.validate": ["javascript", "vue"],
        "[php]": {
            "editor.defaultFormatter": "open-southeners.laravel-pint"
        },
        "[javascript]": {
            "editor.defaultFormatter": "esbenp.prettier-vscode"
        },
        "[vue]": {
            "editor.defaultFormatter": "esbenp.prettier-vscode"
        }
    }
    ```

- [ ] **Create `.vscode/extensions.json`**
    ```json
    {
        "recommendations": [
            "bmewburn.vscode-intelephense-client",
            "amiralizadeh9480.laravel-extra-intellisense",
            "onecentlin.laravel-blade",
            "vue.volar",
            "dbaeumer.vscode-eslint",
            "esbenp.prettier-vscode",
            "editorconfig.editorconfig",
            "open-southeners.laravel-pint"
        ]
    }
    ```

### 3.2 EditorConfig

- [ ] **Create `.editorconfig`**

    ```ini
    root = true

    [*]
    charset = utf-8
    end_of_line = lf
    indent_size = 4
    indent_style = space
    insert_final_newline = true
    trim_trailing_whitespace = true

    [*.md]
    trim_trailing_whitespace = false

    [*.{yml,yaml,json}]
    indent_size = 2

    [*.{js,vue}]
    indent_size = 4
    ```

---

## Phase 4: GitHub Configuration

### 4.1 Repository Settings

- [ ] **Enable branch protection for `main`**:
    - Settings → Branches → Add rule
    - Require pull request reviews (2 approvals)
    - Require status checks to pass
    - Require branches to be up to date
    - Include administrators

- [ ] **Enable Dependabot**:
    - Settings → Security → Dependabot
    - Enable alerts
    - Enable security updates

- [ ] **Add branch protection rules**:
    - Require tests to pass
    - Require linting to pass
    - Require 2 approvals
    - No force push
    - Linear history

### 4.2 GitHub Actions

- [ ] **Create `.github/workflows/code-quality.yml`**
      (See GITHUB_PR_AUTOMATION.md)

- [ ] **Create `.github/workflows/tests.yml`**
      (See GITHUB_PR_AUTOMATION.md)

- [ ] **Create `.github/workflows/security.yml`**
      (See GITHUB_PR_AUTOMATION.md)

- [ ] **Test GitHub Actions**:
    - Push to test branch
    - Verify workflows run

### 4.3 GitHub Integrations

- [ ] **Connect to SonarCloud** (optional):
    - Sign up at sonarcloud.io
    - Connect repository
    - Add `SONAR_TOKEN` to secrets

- [ ] **Connect to Codecov** (optional):
    - Sign up at codecov.io
    - Connect repository
    - Add `CODECOV_TOKEN` to secrets

- [ ] **Install GitHub Apps**:
    - [ ] Dependabot (built-in)
    - [ ] Snyk (security scanning)
    - [ ] GitGuardian (secret detection)

---

## Phase 5: Documentation

### 5.1 Read Core Documentation

- [ ] **Read CODING_STANDARDS.md** ⚠️ MANDATORY
- [ ] **Read PROJECT_PLAN.md**
- [ ] **Read DATABASE_SCHEMA.md**
- [ ] **Read AUTH_FLOW_PLAN.md**
- [ ] **Read TESTING_GUIDE.md**

### 5.2 Team Documentation

- [ ] **Create CONTRIBUTING.md**

    ```markdown
    # Contributing Guide

    ## Before Starting

    1. Read CODING_STANDARDS.md
    2. Setup local environment
    3. Install git hooks

    ## Development Workflow

    1. Create feature branch
    2. Write tests first
    3. Implement feature
    4. Run linters
    5. Create PR

    ## Code Review

    - 2 approvals required
    - All checks must pass
    - Follow coding standards
    ```

- [ ] **Update README.md** with:
    - [ ] Current project status
    - [ ] Setup instructions
    - [ ] Architecture diagram
    - [ ] Links to documentation

---

## Phase 6: Additional Tools & Services

### 6.1 Monitoring & Error Tracking (Optional)

- [ ] **Set up Sentry** (error tracking):

    ```bash
    composer require sentry/sentry-laravel
    php artisan sentry:publish --dsn=your-dsn
    ```

- [ ] **Set up Laravel Telescope** (dev debugging):

    ```bash
    composer require laravel/telescope --dev
    php artisan telescope:install
    php artisan migrate
    ```

- [ ] **Set up Laravel Pulse** (production monitoring):
    ```bash
    composer require laravel/pulse
    php artisan pulse:install
    php artisan vendor:publish --tag=pulse-dashboard
    ```

### 6.2 Performance Tools (Optional)

- [ ] **Install Laravel Debugbar** (dev only):

    ```bash
    composer require barryvdh/laravel-debugbar --dev
    ```

- [ ] **Install Laravel IDE Helper**:
    ```bash
    composer require --dev barryvdh/laravel-ide-helper
    php artisan ide-helper:generate
    php artisan ide-helper:models
    php artisan ide-helper:meta
    ```

### 6.3 Queue & Scheduling

- [ ] **Set up Redis** (for queues - production):

    ```bash
    composer require predis/predis
    ```

- [ ] **Install Horizon** (queue monitoring):

    ```bash
    composer require laravel/horizon
    php artisan horizon:install
    ```

- [ ] **Configure cron** (for scheduler):
    ```bash
    * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
    ```

---

## Phase 7: External Services Setup

### 7.1 Stripe (Payments)

- [ ] **Create Stripe account**
- [ ] **Get API keys** (test mode)
- [ ] **Add to `.env`**:
    ```env
    STRIPE_KEY=pk_test_...
    STRIPE_SECRET=sk_test_...
    STRIPE_WEBHOOK_SECRET=whsec_...
    ```
- [ ] **Install Stripe CLI** (for webhook testing):
    ```bash
    stripe listen --forward-to localhost:8000/webhooks/stripe
    ```

### 7.2 Instagram Graph API

- [ ] **Create Facebook Developer account**
- [ ] **Create app**
- [ ] **Get credentials**
- [ ] **Add to `.env`**:
    ```env
    INSTAGRAM_CLIENT_ID=...
    INSTAGRAM_CLIENT_SECRET=...
    ```

### 7.3 OpenAI (AI Services)

- [ ] **Create OpenAI account**
- [ ] **Get API key**
- [ ] **Add to `.env`**:
    ```env
    OPENAI_API_KEY=sk-...
    ```

### 7.4 AWS S3 (File Storage)

- [ ] **Create AWS account**
- [ ] **Create S3 bucket**
- [ ] **Get credentials**
- [ ] **Add to `.env`**:
    ```env
    AWS_ACCESS_KEY_ID=...
    AWS_SECRET_ACCESS_KEY=...
    AWS_DEFAULT_REGION=us-east-1
    AWS_BUCKET=autopost-ai-dev
    ```
- [ ] **Install S3 driver**:
    ```bash
    composer require league/flysystem-aws-s3-v3
    ```

### 7.5 Email Service

- [ ] **Choose email provider**:
    - Resend (recommended)
    - Mailgun
    - Postmark
    - AWS SES

- [ ] **Add to `.env`**:
    ```env
    MAIL_MAILER=smtp
    MAIL_HOST=smtp.resend.com
    MAIL_PORT=587
    MAIL_USERNAME=...
    MAIL_PASSWORD=...
    MAIL_FROM_ADDRESS=noreply@autopost.ai
    MAIL_FROM_NAME="Autopost AI"
    ```

---

## Phase 8: Database Seeding (Development)

### 8.1 Create Seeders

- [ ] **User seeder**:

    ```bash
    php artisan make:seeder UserSeeder
    ```

- [ ] **Company seeder**:

    ```bash
    php artisan make:seeder CompanySeeder
    ```

- [ ] **Run seeders**:
    ```bash
    php artisan db:seed
    ```

### 8.2 Create Demo Data

- [ ] **Create test user**:

    ```
    Email: admin@autopost.ai
    Password: password
    ```

- [ ] **Create test company**
- [ ] **Add test posts**

---

## Phase 9: First Deployment

### 9.1 Choose Hosting

- [ ] **Select hosting provider**:
    - Laravel Forge + DigitalOcean
    - AWS (EC2, RDS, S3)
    - Vercel (frontend) + Planetscale (database)
    - Heroku
    - Laravel Cloud

### 9.2 Set Up Staging

- [ ] **Create staging environment**
- [ ] **Deploy to staging**
- [ ] **Test all features**
- [ ] **Configure domain**: staging.autopost.ai

### 9.3 Set Up Production

- [ ] **Create production environment**
- [ ] **Configure domain**: autopost.ai
- [ ] **Set up SSL certificate**
- [ ] **Configure CDN**
- [ ] **Set up backups**

---

## Phase 10: Verification & Testing

### 10.1 Local Testing

- [ ] **Application loads**: http://localhost:8000
- [ ] **Assets compile**: `npm run build`
- [ ] **Tests pass**: `php artisan test`
- [ ] **Linting passes**: `npm run lint && ./vendor/bin/pint`
- [ ] **Git hooks work**: Make test commit

### 10.2 Feature Testing

- [ ] **User registration works**
- [ ] **Login works**
- [ ] **Email verification works**
- [ ] **Password reset works**
- [ ] **Dashboard loads**

### 10.3 CI/CD Testing

- [ ] **GitHub Actions run**
- [ ] **All checks pass**
- [ ] **Can create PR**
- [ ] **Can merge PR**

---

## Configuration Files Checklist

### Core Files

- [ ] `.env` - Main environment config
- [ ] `.env.testing` - Test environment config
- [ ] `.env.example` - Template for team
- [ ] `composer.json` - PHP dependencies
- [ ] `package.json` - Node dependencies

### Code Quality

- [ ] `.eslintrc.json` - JavaScript linting
- [ ] `.prettierrc.json` - Code formatting
- [ ] `.prettierignore` - Prettier exclusions
- [ ] `pint.json` - PHP code style (optional)
- [ ] `phpstan.neon` - Static analysis
- [ ] `.editorconfig` - Editor consistency

### Git

- [ ] `.gitignore` - Git exclusions
- [ ] `.gitattributes` - Git attributes
- [ ] `.husky/pre-commit` - Pre-commit hook
- [ ] `.husky/pre-push` - Pre-push hook
- [ ] `.husky/commit-msg` - Commit message validation

### GitHub

- [ ] `.github/workflows/code-quality.yml`
- [ ] `.github/workflows/tests.yml`
- [ ] `.github/workflows/security.yml`
- [ ] `.github/workflows/release.yml`
- [ ] `.github/workflows/deploy.yml`
- [ ] `.github/dependabot.yml`
- [ ] `.github/pull_request_template.md`
- [ ] `.github/ISSUE_TEMPLATE/bug_report.yml`
- [ ] `.github/ISSUE_TEMPLATE/feature_request.yml`

### VS Code

- [ ] `.vscode/settings.json`
- [ ] `.vscode/extensions.json`

### Documentation

- [ ] `README.md`
- [ ] `CONTRIBUTING.md`
- [ ] `CHANGELOG.md`
- [ ] `docs/INDEX.md`
- [ ] `docs/CODING_STANDARDS.md`
- [ ] `docs/PROJECT_PLAN.md`
- [ ] `docs/DATABASE_SCHEMA.md`
- [ ] `docs/AUTH_FLOW_PLAN.md`
- [ ] `docs/INTERNATIONALIZATION_PLAN.md`
- [ ] `docs/CODE_QUALITY_SETUP.md`
- [ ] `docs/GITHUB_PR_AUTOMATION.md`
- [ ] `docs/RELEASE_MANAGEMENT.md`
- [ ] `docs/TESTING_GUIDE.md`

---

## Quick Start Command Sequence

```bash
# 1. Clone and install
git clone <repo> autopost-ai && cd autopost-ai
composer install && npm install

# 2. Environment setup
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate

# 3. Build assets
npm run build

# 4. Setup hooks
npx husky install
npm run prepare

# 5. Run tests
php artisan test

# 6. Start development
composer dev  # Runs server + queue + logs + vite
```

---

## Team Onboarding Checklist

### New Developer Setup (30 minutes)

- [ ] Clone repository
- [ ] Run `composer install && npm install`
- [ ] Create `.env` from `.env.example`
- [ ] Generate app key
- [ ] Create database
- [ ] Run migrations
- [ ] Install git hooks
- [ ] Read CODING_STANDARDS.md
- [ ] Make first commit (test hooks)
- [ ] Create first PR

---

## Common Issues & Solutions

### Issue: "Class not found"

```bash
composer dump-autoload
```

### Issue: "Mix manifest not found"

```bash
npm run build
```

### Issue: "Database does not exist"

```bash
touch database/database.sqlite
php artisan migrate
```

### Issue: "Permission denied"

```bash
chmod -R 775 storage bootstrap/cache
```

### Issue: "Hooks not running"

```bash
rm -rf .git/hooks
npx husky install
chmod +x .husky/pre-commit
chmod +x .husky/pre-push
```

---

## Next Steps After Setup

1. **Read all documentation** in `/docs`
2. **Pick first task** from Phase 0 (Authentication)
3. **Create feature branch**
4. **Write tests first** (TDD)
5. **Implement feature**
6. **Create PR**
7. **Get code review**
8. **Merge to main**

---

## Summary

### Estimated Time

- **Core setup**: 1-2 hours
- **Git hooks & linting**: 30 minutes
- **GitHub configuration**: 1 hour
- **External services**: 2-3 hours
- **First deployment**: 2-4 hours
- **Total**: 6-10 hours

### Priority Order

**Must Do (Day 1):**

1. ✅ Install dependencies
2. ✅ Configure environment
3. ✅ Run migrations
4. ✅ Setup git hooks
5. ✅ Read CODING_STANDARDS.md

**Should Do (Week 1):**

1. ✅ Configure GitHub Actions
2. ✅ Setup external services
3. ✅ Deploy to staging
4. ✅ Complete team onboarding

**Nice to Have (Week 2):**

1. ✅ Monitoring tools
2. ✅ Performance optimization
3. ✅ Advanced integrations

---

**Document Status:** Project Setup Guide  
**Last Updated:** October 9, 2025  
**Next Review:** After initial setup complete
