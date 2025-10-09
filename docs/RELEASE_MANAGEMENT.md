# Release Management & Deployment Strategy

**Version:** 1.0  
**Date:** October 9, 2025  
**Status:** Implementation Guide

---

## Table of Contents

1. [Git Branching Strategy](#git-branching-strategy)
2. [PR Workflow](#pr-workflow)
3. [Versioning Strategy](#versioning-strategy)
4. [Automated Changelog](#automated-changelog)
5. [Release Process](#release-process)
6. [Deployment Pipeline](#deployment-pipeline)
7. [Rollback Strategy](#rollback-strategy)
8. [Hotfix Process](#hotfix-process)

---

## Git Branching Strategy

### GitHub Flow (Recommended for SaaS)

**Simple, fast, production-ready approach:**

```
main (production-ready)
  ↑
  │ PR + Review + Tests
  │
feature/new-auth-flow
feature/add-instagram-api
fix/wallet-calculation-bug
```

**Branch Types:**

- `main` - Always production-ready, deployed to production
- `feature/*` - New features
- `fix/*` - Bug fixes
- `refactor/*` - Code refactoring
- `docs/*` - Documentation updates
- `test/*` - Test improvements

### Branch Naming Convention

```bash
# Features
feature/add-magic-link-auth
feature/instagram-carousel-support

# Bug Fixes
fix/wallet-balance-calculation
fix/token-refresh-timing

# Improvements
refactor/extract-instagram-service
perf/optimize-post-queries

# Documentation
docs/update-api-endpoints
docs/add-deployment-guide

# Tests
test/add-wallet-service-tests
test/improve-coverage
```

---

## PR Workflow

### Step 1: Create Feature Branch

```bash
# Update main
git checkout main
git pull origin main

# Create feature branch
git checkout -b feature/add-magic-link-auth

# Make changes...
git add .
git commit -m "feat(auth): add magic link authentication"

# Push to remote
git push origin feature/add-magic-link-auth
```

### Step 2: Open Pull Request

**On GitHub:**

1. Click "New Pull Request"
2. Select: `feature/add-magic-link-auth` → `main`
3. Fill PR template (auto-populated)
4. Add reviewers (minimum 2)
5. Add labels: `enhancement`, `authentication`
6. Link related issues: `Closes #123`

**PR Title Format:**

```
feat(auth): add magic link authentication
fix(wallet): correct balance calculation
refactor(posts): extract publish logic to service
docs(api): update Instagram endpoints
```

### Step 3: Automated Checks Run

**GitHub Actions automatically:**

- ✅ Runs code style checks (Pint, ESLint)
- ✅ Runs static analysis (PHPStan)
- ✅ Runs all tests with coverage
- ✅ Checks security vulnerabilities
- ✅ Tests database migrations
- ✅ Checks bundle size
- ✅ SonarCloud quality gate

**Bots comment with:**

- Coverage changes
- Code quality score
- Security issues (if any)
- Performance impact

### Step 4: Code Review

**Reviewers check:**

- [ ] Code follows [CODING_STANDARDS.md](./CODING_STANDARDS.md)
- [ ] Business logic in Services, not Controllers
- [ ] Database queries in Repositories
- [ ] Tests are comprehensive
- [ ] Documentation is updated
- [ ] No security issues
- [ ] Performance is acceptable

**Review comments format:**

- 🟢 **Approve** - Ready to merge
- 🟡 **Request Changes** - Issues must be fixed
- 💬 **Comment** - Suggestions, questions

### Step 5: Address Feedback

```bash
# Make requested changes
git add .
git commit -m "fix: address review comments"
git push origin feature/add-magic-link-auth

# Automated checks run again
```

### Step 6: Merge to Main

**After approval and all checks pass:**

**Option A: Squash and Merge** (Recommended)

```
Combines all commits into one clean commit
Result: feat(auth): add magic link authentication (#45)
```

**Option B: Rebase and Merge**

```
Maintains individual commits
Result: Linear history with all commits
```

**Option C: Merge Commit**

```
Creates merge commit
Result: Preserves branch history
```

**We recommend: Squash and Merge** ✅

- Clean linear history
- One commit per feature
- Easy to revert
- Clear changelog

### Step 7: Delete Branch

**After merge:**

```bash
# On GitHub: Click "Delete branch" button

# Locally:
git checkout main
git pull origin main
git branch -d feature/add-magic-link-auth
```

---

## Versioning Strategy

### Semantic Versioning (SemVer)

**Format:** `MAJOR.MINOR.PATCH` (e.g., `1.4.2`)

**Rules:**

- **MAJOR** (1.0.0) - Breaking changes, API changes
- **MINOR** (0.1.0) - New features, backward compatible
- **PATCH** (0.0.1) - Bug fixes, backward compatible

**Examples:**

```
v1.0.0 - Initial release
v1.1.0 - Added Instagram carousel support (new feature)
v1.1.1 - Fixed wallet calculation bug (patch)
v1.2.0 - Added magic link authentication (new feature)
v2.0.0 - Changed API authentication method (breaking)
```

### Pre-release Versions

**For testing:**

```
v1.0.0-alpha.1 - Early testing
v1.0.0-beta.1  - Feature complete, testing
v1.0.0-rc.1    - Release candidate
v1.0.0         - Production release
```

---

## Automated Changelog

### Using Conventional Commits

**Commit Message Format:**

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**

- `feat` - New feature (MINOR version bump)
- `fix` - Bug fix (PATCH version bump)
- `refactor` - Code refactoring (no version bump)
- `perf` - Performance improvement (PATCH version bump)
- `docs` - Documentation changes (no version bump)
- `test` - Test changes (no version bump)
- `chore` - Build/tooling changes (no version bump)
- `breaking` - Breaking change (MAJOR version bump)

**Examples:**

```bash
feat(auth): add magic link authentication
fix(wallet): correct balance calculation for refunds
perf(posts): optimize query to reduce N+1 queries
docs(api): update Instagram API documentation
refactor(services): extract publish logic to separate service
test(wallet): add ledger reconciliation tests
chore(deps): update Laravel to 12.1
```

### Setup Automated Changelog

**Install tools:**

```bash
npm install --save-dev \
  @commitlint/cli \
  @commitlint/config-conventional \
  standard-version
```

**Configure commitlint:**

**Create: `.commitlintrc.json`**

```json
{
    "extends": ["@commitlint/config-conventional"],
    "rules": {
        "type-enum": [
            2,
            "always",
            [
                "feat",
                "fix",
                "docs",
                "style",
                "refactor",
                "perf",
                "test",
                "chore",
                "revert"
            ]
        ],
        "scope-enum": [
            2,
            "always",
            [
                "auth",
                "wallet",
                "posts",
                "instagram",
                "ai",
                "companies",
                "users",
                "deps"
            ]
        ]
    }
}
```

**Add Husky hook:**

**Create: `.husky/commit-msg`**

```bash
#!/usr/bin/env sh
. "$(dirname -- "$0")/_/husky.sh"

npx --no-install commitlint --edit "$1"
```

**Make executable:**

```bash
chmod +x .husky/commit-msg
```

**Add to `package.json` scripts:**

```json
{
    "scripts": {
        "release": "standard-version",
        "release:minor": "standard-version --release-as minor",
        "release:major": "standard-version --release-as major",
        "release:patch": "standard-version --release-as patch"
    }
}
```

---

## Release Process

### Automated Release Workflow

**Create: `.github/workflows/release.yml`**

```yaml
name: Release

on:
    push:
        branches: [main]

jobs:
    # Check if release is needed
    check-version:
        runs-on: ubuntu-latest
        outputs:
            should_release: ${{ steps.check.outputs.should_release }}

        steps:
            - name: Checkout code
              uses: actions/checkout@v4
              with:
                  fetch-depth: 0

            - name: Check if version changed
              id: check
              run: |
                  # Check if version in composer.json changed
                  if git diff HEAD^ HEAD -- composer.json | grep -q '"version"'; then
                    echo "should_release=true" >> $GITHUB_OUTPUT
                  else
                    echo "should_release=false" >> $GITHUB_OUTPUT
                  fi

    # Create release
    create-release:
        needs: check-version
        if: needs.check-version.outputs.should_release == 'true'
        runs-on: ubuntu-latest

        permissions:
            contents: write
            pull-requests: write

        steps:
            - name: Checkout code
              uses: actions/checkout@v4
              with:
                  fetch-depth: 0

            - name: Setup Node.js
              uses: actions/setup-node@v4
              with:
                  node-version: '18'

            - name: Install dependencies
              run: npm ci

            - name: Configure Git
              run: |
                  git config user.name "github-actions[bot]"
                  git config user.email "github-actions[bot]@users.noreply.github.com"

            - name: Generate changelog and bump version
              run: npm run release

            - name: Get version
              id: version
              run: |
                  VERSION=$(node -p "require('./package.json').version")
                  echo "version=$VERSION" >> $GITHUB_OUTPUT

            - name: Push changes
              run: |
                  git push --follow-tags origin main

            - name: Create GitHub Release
              uses: actions/create-release@v1
              env:
                  GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
              with:
                  tag_name: v${{ steps.version.outputs.version }}
                  release_name: Release v${{ steps.version.outputs.version }}
                  body_path: CHANGELOG.md
                  draft: false
                  prerelease: false

    # Deploy to production
    deploy:
        needs: create-release
        runs-on: ubuntu-latest
        environment: production

        steps:
            - name: Checkout code
              uses: actions/checkout@v4

            - name: Deploy to production
              run: |
                  echo "Deploying to production..."
                  # Add your deployment commands here
```

---

### Manual Release Process

**When you're ready to release:**

**Step 1: Ensure main is stable**

```bash
git checkout main
git pull origin main

# Run tests locally
composer test
npm run lint:check

# Everything passes? Continue...
```

**Step 2: Generate changelog and bump version**

```bash
# Automatically determines version bump based on commits
npm run release

# Or specify version type
npm run release:minor  # For new features
npm run release:major  # For breaking changes
npm run release:patch  # For bug fixes only
```

**This automatically:**

- ✅ Analyzes commit messages since last release
- ✅ Determines version bump (major/minor/patch)
- ✅ Updates `package.json` version
- ✅ Updates `composer.json` version
- ✅ Generates `CHANGELOG.md`
- ✅ Creates git tag (e.g., `v1.2.0`)
- ✅ Commits changes

**Step 3: Review generated changelog**

```bash
# Check CHANGELOG.md
cat CHANGELOG.md

# Check version
git log --oneline -1
# Should show: chore(release): 1.2.0
```

**Step 4: Push to GitHub**

```bash
git push --follow-tags origin main
```

**Step 5: GitHub Actions automatically:**

- ✅ Runs all tests
- ✅ Creates GitHub Release
- ✅ Deploys to production
- ✅ Sends notifications

---

## CHANGELOG.md Example

**Auto-generated format:**

```markdown
# Changelog

All notable changes to this project will be documented in this file.

## [1.2.0] - 2025-10-09

### Features

- **auth:** add magic link authentication ([#45](https://github.com/org/repo/pull/45))
- **posts:** add carousel post type support ([#48](https://github.com/org/repo/pull/48))
- **instagram:** add story publishing ([#52](https://github.com/org/repo/pull/52))

### Bug Fixes

- **wallet:** correct balance calculation for refunds ([#46](https://github.com/org/repo/pull/46))
- **auth:** fix email verification link expiration ([#49](https://github.com/org/repo/pull/49))

### Performance

- **posts:** optimize query to reduce N+1 queries ([#50](https://github.com/org/repo/pull/50))

### Documentation

- **api:** update Instagram API endpoints ([#51](https://github.com/org/repo/pull/51))

## [1.1.1] - 2025-09-25

### Bug Fixes

- **wallet:** fix transaction ledger race condition ([#44](https://github.com/org/repo/pull/44))

## [1.1.0] - 2025-09-20

### Features

- **companies:** add team member invitation system ([#40](https://github.com/org/repo/pull/40))
- **posts:** add scheduled publishing ([#42](https://github.com/org/repo/pull/42))

### Bug Fixes

- **auth:** fix password reset email delivery ([#41](https://github.com/org/repo/pull/41))
```

---

## Deployment Pipeline

### Environments

**Development:**

- Branch: Any feature branch
- Auto-deploy: No
- Purpose: Local development
- Database: SQLite (local)

**Staging:**

- Branch: `main` after PR merge
- Auto-deploy: Yes
- Purpose: QA testing, client preview
- Database: PostgreSQL (staging)
- URL: `https://staging.autopost.ai`

**Production:**

- Branch: `main` after release tag
- Auto-deploy: Yes (after manual approval)
- Purpose: Live customers
- Database: PostgreSQL (production)
- URL: `https://autopost.ai`

### Deployment Workflow

**Create: `.github/workflows/deploy.yml`**

```yaml
name: Deploy

on:
    push:
        branches: [main]
        tags:
            - 'v*'

jobs:
    # Deploy to staging (on every push to main)
    deploy-staging:
        if: github.ref == 'refs/heads/main' && !startsWith(github.ref, 'refs/tags/')
        runs-on: ubuntu-latest
        environment: staging

        steps:
            - name: Checkout code
              uses: actions/checkout@v4

            - name: Deploy to staging
              uses: deployphp/action@v1
              with:
                  private-key: ${{ secrets.DEPLOY_PRIVATE_KEY }}
                  dep: deploy staging

    # Deploy to production (on release tag)
    deploy-production:
        if: startsWith(github.ref, 'refs/tags/v')
        runs-on: ubuntu-latest
        environment: production

        steps:
            - name: Checkout code
              uses: actions/checkout@v4

            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: '8.2'

            - name: Install dependencies
              run: |
                  composer install --no-dev --optimize-autoloader
                  npm ci
                  npm run build

            - name: Run migrations
              run: php artisan migrate --force
              env:
                  DB_CONNECTION: pgsql
                  DB_HOST: ${{ secrets.DB_HOST }}
                  DB_DATABASE: ${{ secrets.DB_DATABASE }}
                  DB_USERNAME: ${{ secrets.DB_USERNAME }}
                  DB_PASSWORD: ${{ secrets.DB_PASSWORD }}

            - name: Cache application
              run: |
                  php artisan config:cache
                  php artisan route:cache
                  php artisan view:cache

            - name: Deploy to production
              uses: deployphp/action@v1
              with:
                  private-key: ${{ secrets.DEPLOY_PRIVATE_KEY }}
                  dep: deploy production

            - name: Notify team
              uses: 8398a7/action-slack@v3
              with:
                  status: ${{ job.status }}
                  text: '🚀 Deployed ${{ github.ref }} to production!'
                  webhook_url: ${{ secrets.SLACK_WEBHOOK }}
```

---

## Rollback Strategy

### Quick Rollback (Production Issues)

**Option 1: Revert via GitHub**

```bash
# Find the problematic release
git log --oneline

# Revert the release commit
git revert <commit-hash>

# Push
git push origin main

# Deployment will auto-trigger with reverted code
```

**Option 2: Deploy Previous Release**

```bash
# List recent tags
git tag -l --sort=-version:refname | head -5

# Checkout previous version
git checkout v1.1.0

# Deploy previous version
# (deployment method depends on your setup)
```

**Option 3: Database Rollback (if needed)**

```bash
# SSH to production server
ssh production-server

# Rollback last migration
php artisan migrate:rollback --step=1

# Or rollback to specific batch
php artisan migrate:rollback --batch=5
```

### Rollback Checklist

- [ ] Identify the issue (error logs, monitoring)
- [ ] Communicate to team (Slack, Discord)
- [ ] Revert code to previous version
- [ ] Rollback database if schema changed
- [ ] Clear caches (`php artisan cache:clear`)
- [ ] Restart queue workers
- [ ] Verify functionality
- [ ] Notify team of resolution
- [ ] Post-mortem: What went wrong?

---

## Hotfix Process

### For Critical Production Bugs

**When to use hotfix:**

- 🔴 Production is broken
- 🔴 Security vulnerability
- 🔴 Data loss risk
- 🔴 Payment processing broken

**Process:**

**Step 1: Create hotfix branch from main**

```bash
git checkout main
git pull origin main
git checkout -b hotfix/critical-wallet-bug
```

**Step 2: Fix the issue**

```bash
# Make minimal changes to fix the issue
git add .
git commit -m "fix(wallet): critical - prevent negative balance"
```

**Step 3: Open PR with "HOTFIX" label**

- Title: `[HOTFIX] fix(wallet): prevent negative balance`
- Requires: 1 approval (expedited)
- CI/CD still runs (no shortcuts on quality)

**Step 4: Merge and deploy ASAP**

```bash
# After approval and tests pass
# Merge to main
# Auto-deploys to production

# Create patch release
npm run release:patch
git push --follow-tags origin main
```

**Step 5: Post-deployment verification**

- ✅ Monitor error rates
- ✅ Check key metrics
- ✅ Verify fix works
- ✅ Document incident

---

## Release Checklist

### Before Creating Release

**Code:**

- [ ] All PRs merged to main
- [ ] All tests passing
- [ ] No linter errors
- [ ] Security scans clean
- [ ] Code reviewed and approved

**Documentation:**

- [ ] README updated (if needed)
- [ ] API docs updated (if changed)
- [ ] Migration guide (if breaking changes)
- [ ] CHANGELOG reviewed

**Testing:**

- [ ] Manual testing on staging
- [ ] E2E tests passed
- [ ] Performance tested
- [ ] Mobile responsive checked

**Infrastructure:**

- [ ] Database backups current
- [ ] Monitoring configured
- [ ] Rollback plan prepared
- [ ] Team notified of deployment

### After Release

**Verification:**

- [ ] Production deployment successful
- [ ] Application accessible
- [ ] Key features working
- [ ] No error spikes in logs
- [ ] Database migrations applied
- [ ] Caches cleared

**Communication:**

- [ ] Team notified in Slack
- [ ] Customers notified (if breaking changes)
- [ ] Status page updated
- [ ] Release notes published

**Monitoring:**

- [ ] Watch error rates (1 hour)
- [ ] Monitor performance metrics
- [ ] Check user feedback
- [ ] Review logs for issues

---

## Release Cadence

### Recommended Schedule

**Weekly Releases (Recommended):**

```
Monday: Feature freeze, final testing
Tuesday: Create release, deploy to staging
Wednesday: QA testing on staging
Thursday: Deploy to production
Friday: Monitor, fix issues
```

**Bi-weekly Releases:**

```
Week 1: Development, PRs merged
Week 2 Monday: Feature freeze
Week 2 Wednesday: Release & deploy
```

**Continuous Deployment (Advanced):**

```
Every merge to main → auto-deploy to production
Requires: High test coverage, mature CI/CD
```

**We recommend: Weekly releases** ✅

---

## Emergency Procedures

### Production Down

**1. Immediate Response:**

```bash
# Check status
curl https://autopost.ai/up

# Check logs
ssh production-server
tail -f /var/www/storage/logs/laravel.log

# Restart services if needed
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

**2. Identify Issue:**

- Database connection?
- PHP error?
- Server resources?
- Recent deployment?

**3. Quick Fix or Rollback:**

- If recent deployment caused it → Rollback
- If database issue → Check connections
- If server resources → Scale up

**4. Communication:**

```
1. Post to status page immediately
2. Notify team in Slack
3. Update customers if prolonged
4. Post-incident report after resolution
```

---

## Version Tags & GitHub Releases

### Tag Format

```bash
# Production releases
v1.0.0, v1.1.0, v1.2.0

# Pre-releases
v1.0.0-alpha.1
v1.0.0-beta.1
v1.0.0-rc.1
```

### Creating Tags Manually

```bash
# Create annotated tag
git tag -a v1.2.0 -m "Release version 1.2.0"

# Push tag
git push origin v1.2.0

# Or push all tags
git push origin --tags
```

### GitHub Release Notes Template

**Title:** `Release v1.2.0 - Magic Link Authentication`

**Description:**

````markdown
## 🎉 What's New

### Features

- ✨ Magic link authentication for passwordless login (#45)
- ✨ Instagram carousel post support (#48)
- ✨ Story publishing with expiration tracking (#52)

### Improvements

- ⚡ Optimized post list query (50% faster) (#50)
- 📝 Improved error messages for wallet operations

### Bug Fixes

- 🐛 Fixed wallet balance calculation for refunds (#46)
- 🐛 Fixed email verification link expiration (#49)

### Documentation

- 📚 Updated API documentation for Instagram endpoints (#51)

## 🔄 Database Changes

This release includes database migrations. Run:

```bash
php artisan migrate
```
````

## ⚠️ Breaking Changes

None in this release.

## 📦 Installation

```bash
git pull origin main
composer install
npm install && npm run build
php artisan migrate
php artisan cache:clear
```

## 🙏 Contributors

Thanks to @user1, @user2, @user3 for their contributions!

## 📝 Full Changelog

See [CHANGELOG.md](./CHANGELOG.md) for complete details.

```

---

## Monitoring & Metrics

### Post-Release Monitoring

**Key Metrics to Watch:**
- Error rate (should not spike)
- Response time (should not increase)
- Database query time
- Queue processing time
- Memory usage
- CPU usage

**Tools:**
- Laravel Pulse (built-in monitoring)
- Laravel Telescope (development debugging)
- Sentry (error tracking)
- New Relic (APM)

**Alert Thresholds:**
- 🟢 Error rate < 0.1%
- 🟡 Error rate 0.1% - 1%
- 🔴 Error rate > 1% (investigate immediately)

---

## Summary

### Complete Release Flow

```

1. Developer creates feature branch
   ↓
2. Developer opens PR
   ↓
3. Automated checks run (tests, linting, security)
   ↓
4. Code review by team (2 approvals)
   ↓
5. PR merged to main (squash and merge)
   ↓
6. Auto-deploy to staging
   ↓
7. QA testing on staging
   ↓
8. Create release (npm run release)
   ↓
9. Push tag (git push --follow-tags)
   ↓
10. GitHub Actions creates release
    ↓
11. Auto-deploy to production (with approval)
    ↓
12. Monitor metrics for 1 hour
    ↓
13. Release complete! 🎉

````

### Key Commands

```bash
# Daily Development
git checkout -b feature/new-feature
git commit -m "feat(scope): description"
git push origin feature/new-feature

# Create Release
npm run release          # Auto version
npm run release:minor    # Force minor
npm run release:major    # Force major

# Deploy
git push --follow-tags origin main

# Rollback
git revert <commit>
git push origin main

# Hotfix
git checkout -b hotfix/critical-bug
git commit -m "fix: critical bug"
# Open PR with [HOTFIX] label
````

---

**Document Status:** Implementation Guide  
**Estimated Setup Time:** 3-4 hours  
**Maintenance:** Low (mostly automated)
