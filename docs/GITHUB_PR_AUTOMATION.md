# GitHub Pull Request Automation & Checks

**Version:** 1.0  
**Date:** October 9, 2025  
**Status:** Implementation Guide

---

## Overview

Comprehensive automated checks for Pull Requests to ensure code quality, security, and reliability before merging to main branch.

### What Gets Checked on Every PR

1. ✅ **Code Style** - PHP (Pint) & JavaScript (ESLint)
2. ✅ **Static Analysis** - PHPStan for type safety
3. ✅ **Tests** - Unit & Feature tests with coverage
4. ✅ **Security** - Dependency vulnerabilities
5. ✅ **Performance** - Check for N+1 queries, slow code
6. ✅ **Code Quality** - Complexity, duplication, maintainability
7. ✅ **Documentation** - Ensure docs are updated
8. ✅ **Database** - Test migrations
9. ✅ **Bundle Size** - Frontend assets size check
10. ✅ **Accessibility** - A11y checks for frontend

---

## Table of Contents

1. [GitHub Actions Workflows](#github-actions-workflows)
2. [Automated Code Review Tools](#automated-code-review-tools)
3. [Security Scanning](#security-scanning)
4. [Test Coverage Reports](#test-coverage-reports)
5. [Performance Checks](#performance-checks)
6. [Branch Protection Rules](#branch-protection-rules)
7. [Bot Integrations](#bot-integrations)
8. [Status Badges](#status-badges)

---

## GitHub Actions Workflows

### Workflow 1: Code Quality Check

**Create: `.github/workflows/code-quality.yml`**

```yaml
name: Code Quality

on:
    pull_request:
        branches: [main, develop]
    push:
        branches: [main, develop]

jobs:
    # PHP Code Style Check
    php-lint:
        name: PHP Code Style (Pint)
        runs-on: ubuntu-latest

        steps:
            - name: Checkout code
              uses: actions/checkout@v4

            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: '8.2'
                  extensions: mbstring, xml, ctype, json, pdo, sqlite
                  coverage: xdebug

            - name: Cache Composer dependencies
              uses: actions/cache@v3
              with:
                  path: vendor
                  key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
                  restore-keys: ${{ runner.os }}-composer-

            - name: Install dependencies
              run: composer install --prefer-dist --no-progress --no-interaction

            - name: Run Laravel Pint
              run: ./vendor/bin/pint --test

    # PHP Static Analysis
    phpstan:
        name: PHP Static Analysis (PHPStan)
        runs-on: ubuntu-latest

        steps:
            - name: Checkout code
              uses: actions/checkout@v4

            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: '8.2'
                  extensions: mbstring, xml, ctype, json

            - name: Cache Composer dependencies
              uses: actions/cache@v3
              with:
                  path: vendor
                  key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}

            - name: Install dependencies
              run: composer install --prefer-dist --no-progress

            - name: Run PHPStan
              run: ./vendor/bin/phpstan analyse --error-format=github

    # JavaScript/Vue Linting
    eslint:
        name: JavaScript Linting (ESLint)
        runs-on: ubuntu-latest

        steps:
            - name: Checkout code
              uses: actions/checkout@v4

            - name: Setup Node.js
              uses: actions/setup-node@v4
              with:
                  node-version: '18'
                  cache: 'npm'

            - name: Install dependencies
              run: npm ci

            - name: Run ESLint
              run: npm run lint:check

            - name: Run Prettier Check
              run: npm run format:check
```

---

### Workflow 2: Tests with Coverage

**Create: `.github/workflows/tests.yml`**

```yaml
name: Tests

on:
    pull_request:
        branches: [main, develop]
    push:
        branches: [main, develop]

jobs:
    tests:
        name: PHP ${{ matrix.php }} - Laravel ${{ matrix.laravel }}
        runs-on: ubuntu-latest

        strategy:
            fail-fast: false
            matrix:
                php: ['8.2', '8.3']
                laravel: ['12.*']

        steps:
            - name: Checkout code
              uses: actions/checkout@v4

            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: ${{ matrix.php }}
                  extensions: mbstring, xml, ctype, json, pdo, sqlite
                  coverage: xdebug

            - name: Cache Composer dependencies
              uses: actions/cache@v3
              with:
                  path: vendor
                  key: ${{ runner.os }}-php-${{ matrix.php }}-composer-${{ hashFiles('**/composer.lock') }}

            - name: Install dependencies
              run: |
                  composer require "laravel/framework:${{ matrix.laravel }}" --no-interaction --no-update
                  composer install --prefer-dist --no-progress

            - name: Create SQLite database
              run: |
                  mkdir -p database
                  touch database/database.sqlite

            - name: Setup environment
              run: |
                  cp .env.example .env
                  php artisan key:generate

            - name: Run migrations
              run: php artisan migrate --force

            - name: Run tests with coverage
              run: php artisan test --coverage --min=80

            - name: Upload coverage to Codecov
              uses: codecov/codecov-action@v3
              with:
                  files: ./coverage.xml
                  flags: unittests
                  name: codecov-umbrella
                  fail_ci_if_error: false
```

---

### Workflow 3: Security Scanning

**Create: `.github/workflows/security.yml`**

```yaml
name: Security

on:
    pull_request:
        branches: [main, develop]
    push:
        branches: [main, develop]
    schedule:
        - cron: '0 10 * * 1' # Weekly on Mondays

jobs:
    # PHP Dependency Scanning
    php-security:
        name: PHP Security Check
        runs-on: ubuntu-latest

        steps:
            - name: Checkout code
              uses: actions/checkout@v4

            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: '8.2'

            - name: Install dependencies
              run: composer install --prefer-dist --no-progress

            - name: Check for security vulnerabilities
              run: composer audit

    # JavaScript Dependency Scanning
    npm-security:
        name: NPM Security Check
        runs-on: ubuntu-latest

        steps:
            - name: Checkout code
              uses: actions/checkout@v4

            - name: Setup Node.js
              uses: actions/setup-node@v4
              with:
                  node-version: '18'

            - name: Install dependencies
              run: npm ci

            - name: Run npm audit
              run: npm audit --audit-level=high

    # Code Security Scanning
    codeql:
        name: CodeQL Analysis
        runs-on: ubuntu-latest
        permissions:
            actions: read
            contents: read
            security-events: write

        strategy:
            fail-fast: false
            matrix:
                language: ['javascript', 'php']

        steps:
            - name: Checkout code
              uses: actions/checkout@v4

            - name: Initialize CodeQL
              uses: github/codeql-action/init@v2
              with:
                  languages: ${{ matrix.language }}

            - name: Autobuild
              uses: github/codeql-action/autobuild@v2

            - name: Perform CodeQL Analysis
              uses: github/codeql-action/analyze@v2

    # Secrets Scanning
    trufflehog:
        name: Secrets Scanning
        runs-on: ubuntu-latest

        steps:
            - name: Checkout code
              uses: actions/checkout@v4
              with:
                  fetch-depth: 0

            - name: TruffleHog OSS
              uses: trufflesecurity/trufflehog@main
              with:
                  path: ./
                  base: ${{ github.event.repository.default_branch }}
                  head: HEAD
```

---

### Workflow 4: Database Checks

**Create: `.github/workflows/database.yml`**

```yaml
name: Database

on:
    pull_request:
        branches: [main, develop]

jobs:
    migrations:
        name: Test Migrations
        runs-on: ubuntu-latest

        services:
            postgres:
                image: postgres:15
                env:
                    POSTGRES_USER: postgres
                    POSTGRES_PASSWORD: postgres
                    POSTGRES_DB: autopost_test
                options: >-
                    --health-cmd pg_isready
                    --health-interval 10s
                    --health-timeout 5s
                    --health-retries 5
                ports:
                    - 5432:5432

        steps:
            - name: Checkout code
              uses: actions/checkout@v4

            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: '8.2'
                  extensions: pgsql, pdo_pgsql

            - name: Install dependencies
              run: composer install --prefer-dist --no-progress

            - name: Setup environment
              run: |
                  cp .env.example .env
                  php artisan key:generate
              env:
                  DB_CONNECTION: pgsql
                  DB_HOST: localhost
                  DB_PORT: 5432
                  DB_DATABASE: autopost_test
                  DB_USERNAME: postgres
                  DB_PASSWORD: postgres

            - name: Run migrations (fresh)
              run: php artisan migrate:fresh --force

            - name: Run migrations (rollback)
              run: php artisan migrate:rollback --force

            - name: Run migrations (again)
              run: php artisan migrate --force

            - name: Seed database
              run: php artisan db:seed --force
```

---

### Workflow 5: Frontend Bundle Size Check

**Create: `.github/workflows/bundle-size.yml`**

```yaml
name: Bundle Size

on:
    pull_request:
        branches: [main, develop]

jobs:
    size:
        name: Check Bundle Size
        runs-on: ubuntu-latest

        steps:
            - name: Checkout code
              uses: actions/checkout@v4

            - name: Setup Node.js
              uses: actions/setup-node@v4
              with:
                  node-version: '18'
                  cache: 'npm'

            - name: Install dependencies
              run: npm ci

            - name: Build production bundle
              run: npm run build

            - name: Analyze bundle size
              uses: andresz1/size-limit-action@v1
              with:
                  github_token: ${{ secrets.GITHUB_TOKEN }}
                  build_script: build
                  # Warn if bundle grows by >10%
                  limit: '10%'
```

---

## Automated Code Review Tools

### 1. SonarCloud (Recommended)

**Best for:** Code quality, bugs, code smells, security hotspots

**Setup:**

1. Sign up at [sonarcloud.io](https://sonarcloud.io)
2. Connect GitHub repository
3. Add workflow:

**Create: `.github/workflows/sonarcloud.yml`**

```yaml
name: SonarCloud

on:
    pull_request:
        branches: [main, develop]
    push:
        branches: [main, develop]

jobs:
    sonarcloud:
        name: SonarCloud Analysis
        runs-on: ubuntu-latest

        steps:
            - name: Checkout code
              uses: actions/checkout@v4
              with:
                  fetch-depth: 0

            - name: SonarCloud Scan
              uses: SonarSource/sonarcloud-github-action@master
              env:
                  GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
                  SONAR_TOKEN: ${{ secrets.SONAR_TOKEN }}
```

**Create: `sonar-project.properties`**

```properties
sonar.projectKey=your-org_autopost-ai
sonar.organization=your-org

sonar.sources=app,resources/js
sonar.tests=tests
sonar.exclusions=vendor/**,node_modules/**,public/**,storage/**

# PHP specific
sonar.php.coverage.reportPaths=coverage.xml
sonar.php.tests.reportPath=test-report.xml

# JavaScript specific
sonar.javascript.lcov.reportPaths=coverage/lcov.info
```

**Features:**

- ✅ Code quality metrics
- ✅ Security vulnerabilities
- ✅ Code duplication detection
- ✅ Technical debt calculation
- ✅ Coverage tracking
- ✅ PR decoration with inline comments

---

### 2. CodeClimate

**Best for:** Maintainability, test coverage

**Setup:**

1. Sign up at [codeclimate.com](https://codeclimate.com)
2. Add repository
3. Add to workflow:

```yaml
- name: Upload coverage to Code Climate
  uses: paambaati/codeclimate-action@v5
  env:
      CC_TEST_REPORTER_ID: ${{ secrets.CC_TEST_REPORTER_ID }}
  with:
      coverageLocations: |
          ${{github.workspace}}/coverage.xml:clover
```

---

### 3. Reviewdog

**Best for:** Automated inline PR comments from linters

**Add to existing workflow:**

```yaml
- name: Run reviewdog
  uses: reviewdog/action-phpstan@v1
  with:
      github_token: ${{ secrets.GITHUB_TOKEN }}
      reporter: github-pr-review
      level: error
      phpstan_level: 5
```

---

## Security Scanning

### 1. Dependabot (Built-in GitHub)

**Enable in repository settings:**

- Settings → Security → Dependabot
- Enable Dependabot alerts
- Enable Dependabot security updates

**Create: `.github/dependabot.yml`**

```yaml
version: 2
updates:
    # PHP dependencies
    - package-ecosystem: 'composer'
      directory: '/'
      schedule:
          interval: 'weekly'
          day: 'monday'
      open-pull-requests-limit: 5
      reviewers:
          - 'your-team'
      labels:
          - 'dependencies'
          - 'php'

    # JavaScript dependencies
    - package-ecosystem: 'npm'
      directory: '/'
      schedule:
          interval: 'weekly'
          day: 'monday'
      open-pull-requests-limit: 5
      reviewers:
          - 'your-team'
      labels:
          - 'dependencies'
          - 'javascript'

    # GitHub Actions
    - package-ecosystem: 'github-actions'
      directory: '/'
      schedule:
          interval: 'weekly'
      labels:
          - 'ci/cd'
```

---

### 2. Snyk

**Best for:** Vulnerability scanning with auto-fix PRs

**Setup:**

1. Install Snyk app from GitHub Marketplace
2. Configure `.snyk` file:

```yaml
# Snyk (https://snyk.io) policy file
version: v1.19.0

# Ignore specific vulnerabilities (with justification)
ignore:
    'SNYK-PHP-EXAMPLE-123456':
        - '*':
              reason: 'Not exploitable in our context'
              expires: '2025-12-31'
```

---

### 3. GitGuardian

**Best for:** Secret detection in commits

**Setup:**

1. Install GitGuardian app from GitHub Marketplace
2. Automatically scans all commits for secrets
3. Blocks PRs with detected secrets

---

## Test Coverage Reports

### 1. Codecov

**Best for:** Beautiful coverage reports with trends

**Setup:**

```yaml
- name: Generate coverage
  run: php artisan test --coverage-clover=coverage.xml

- name: Upload to Codecov
  uses: codecov/codecov-action@v3
  with:
      files: ./coverage.xml
      flags: unittests
      fail_ci_if_error: true
      verbose: true
```

**Create: `codecov.yml`**

```yaml
coverage:
    status:
        project:
            default:
                target: 80%
                threshold: 2%
        patch:
            default:
                target: 80%

comment:
    layout: 'reach, diff, flags, files'
    behavior: default
```

---

### 2. Coveralls

**Alternative to Codecov:**

```yaml
- name: Upload to Coveralls
  uses: coverallsapp/github-action@master
  with:
      github-token: ${{ secrets.GITHUB_TOKEN }}
      path-to-lcov: ./coverage/lcov.info
```

---

## Performance Checks

### 1. Laravel Telescope in CI

**Check for N+1 queries:**

```php
// tests/Feature/PerformanceTest.php
use Laravel\Telescope\Telescope;

it('does not have N+1 queries on post list', function () {
    Telescope::start(app());

    $company = Company::factory()->create();
    Post::factory()->count(20)->create(['company_id' => $company->id]);

    DB::enableQueryLog();

    $this->get('/posts');

    $queries = DB::getQueryLog();
    expect(count($queries))->toBeLessThan(5);
});
```

---

### 2. Lighthouse CI (Frontend Performance)

**Check bundle size and performance:**

```yaml
- name: Run Lighthouse CI
  uses: treosh/lighthouse-ci-action@v9
  with:
      urls: |
          http://localhost:8000
          http://localhost:8000/dashboard
      uploadArtifacts: true
```

---

## Branch Protection Rules

### Enable in Repository Settings

**Settings → Branches → Add rule for `main`:**

**Required:**

- ✅ Require pull request reviews before merging (2 approvals)
- ✅ Require status checks to pass before merging:
    - PHP Code Style (Pint)
    - PHPStan
    - ESLint
    - Tests (PHP 8.2, PHP 8.3)
    - Security Scan
    - SonarCloud Quality Gate
- ✅ Require branches to be up to date before merging
- ✅ Require linear history
- ✅ Include administrators
- ✅ Restrict who can push to matching branches

**Optional but Recommended:**

- ✅ Require signed commits
- ✅ Require deployments to succeed before merging
- ✅ Lock branch (for releases)

---

## Bot Integrations

### 1. Renovate Bot

**Better alternative to Dependabot with more features**

**Create: `renovate.json`**

```json
{
    "extends": ["config:base"],
    "schedule": ["before 3am on Monday"],
    "labels": ["dependencies"],
    "packageRules": [
        {
            "matchUpdateTypes": ["minor", "patch"],
            "automerge": true,
            "automergeType": "pr",
            "requiredStatusChecks": null
        }
    ],
    "php": {
        "enabled": true
    },
    "npm": {
        "enabled": true
    }
}
```

---

### 2. Mergify

**Auto-merge PRs that pass all checks**

**Create: `.mergify.yml`**

```yaml
pull_request_rules:
    # Auto-merge dependency updates
    - name: Automatic merge for Dependabot PRs
      conditions:
          - author=dependabot[bot]
          - check-success=Tests
          - check-success=Code Quality
          - check-success=Security
      actions:
          merge:
              method: squash

    # Auto-update PR when base branch changes
    - name: Automatic update
      conditions:
          - -conflict
          - base=main
      actions:
          update: {}

    # Label PRs by size
    - name: Label small PRs
      conditions:
          - files-changed<=10
      actions:
          label:
              add: ['size/small']

    - name: Label large PRs
      conditions:
          - files-changed>100
      actions:
          label:
              add: ['size/large']
          comment:
              message: 'This PR is quite large. Consider breaking it into smaller PRs.'
```

---

### 3. PR Size Labeler

**Automatically label PRs by size**

**Create: `.github/workflows/pr-labeler.yml`**

```yaml
name: PR Labeler

on:
    pull_request:
        types: [opened, synchronize]

jobs:
    size-label:
        runs-on: ubuntu-latest
        steps:
            - uses: codelytv/pr-size-labeler@v1
              with:
                  GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
                  xs_label: 'size/xs'
                  xs_max_size: '10'
                  s_label: 'size/s'
                  s_max_size: '100'
                  m_label: 'size/m'
                  m_max_size: '500'
                  l_label: 'size/l'
                  l_max_size: '1000'
                  xl_label: 'size/xl'
                  fail_if_xl: 'false'
```

---

### 4. TODO to Issue

**Convert TODO comments to GitHub Issues**

**Create: `.github/workflows/todo.yml`**

```yaml
name: TODO to Issue

on:
    push:
        branches: [main]

jobs:
    create-issue:
        runs-on: ubuntu-latest
        steps:
            - uses: actions/checkout@v4
            - uses: alstr/todo-to-issue-action@v4
              with:
                  TOKEN: ${{ secrets.GITHUB_TOKEN }}
                  CLOSE_ISSUES: true
```

**Usage in code:**

```php
// TODO: Refactor this method to use repository pattern
// Issue will be auto-created on merge to main
```

---

## Status Badges

### Add to README.md

```markdown
# Autopost AI

![Tests](https://github.com/your-org/autopost-ai/workflows/Tests/badge.svg)
![Code Quality](https://github.com/your-org/autopost-ai/workflows/Code%20Quality/badge.svg)
![Security](https://github.com/your-org/autopost-ai/workflows/Security/badge.svg)
[![codecov](https://codecov.io/gh/your-org/autopost-ai/branch/main/graph/badge.svg)](https://codecov.io/gh/your-org/autopost-ai)
[![Maintainability](https://api.codeclimate.com/v1/badges/xxx/maintainability)](https://codeclimate.com/github/your-org/autopost-ai/maintainability)
[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=xxx&metric=alert_status)](https://sonarcloud.io/dashboard?id=xxx)
```

---

## PR Template

**Create: `.github/pull_request_template.md`**

```markdown
## Description

<!-- Describe your changes in detail -->

## Type of Change

- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update
- [ ] Code refactoring
- [ ] Performance improvement
- [ ] Test coverage improvement

## Related Issues

<!-- Link to related issues -->

Closes #

## Checklist

- [ ] My code follows the [coding standards](../docs/CODING_STANDARDS.md)
- [ ] I have performed a self-review of my own code
- [ ] I have commented my code in hard-to-understand areas
- [ ] I have updated the documentation accordingly
- [ ] My changes generate no new warnings or errors
- [ ] I have added tests that prove my fix/feature works
- [ ] New and existing tests pass locally
- [ ] I have updated the database schema documentation (if applicable)
- [ ] I have run `./vendor/bin/pint` to format PHP code
- [ ] I have run `npm run lint` to check JavaScript code

## Testing Instructions

<!-- How can reviewers test your changes? -->

## Screenshots (if applicable)

<!-- Add screenshots to help explain your changes -->

## Performance Impact

<!-- Describe any performance implications -->

- [ ] No performance impact
- [ ] Improved performance
- [ ] May impact performance (explain below)

## Database Changes

- [ ] No database changes
- [ ] Migration included
- [ ] Seeders updated
- [ ] Database documentation updated

## Additional Notes

<!-- Any additional information for reviewers -->
```

---

## Issue Templates

**Create: `.github/ISSUE_TEMPLATE/bug_report.yml`**

```yaml
name: Bug Report
description: File a bug report
title: '[Bug]: '
labels: ['bug', 'triage']
body:
    - type: markdown
      attributes:
          value: |
              Thanks for taking the time to fill out this bug report!

    - type: textarea
      id: what-happened
      attributes:
          label: What happened?
          description: Also tell us, what did you expect to happen?
          placeholder: Tell us what you see!
      validations:
          required: true

    - type: textarea
      id: steps
      attributes:
          label: Steps to Reproduce
          description: Steps to reproduce the behavior
          placeholder: |
              1. Go to '...'
              2. Click on '....'
              3. Scroll down to '....'
              4. See error
      validations:
          required: true

    - type: dropdown
      id: browsers
      attributes:
          label: What browsers are you seeing the problem on?
          multiple: true
          options:
              - Chrome
              - Firefox
              - Safari
              - Edge
              - Other

    - type: input
      id: version
      attributes:
          label: Version
          description: What version of our software are you running?
      validations:
          required: false
```

**Create: `.github/ISSUE_TEMPLATE/feature_request.yml`**

```yaml
name: Feature Request
description: Suggest an idea for this project
title: '[Feature]: '
labels: ['enhancement']
body:
    - type: markdown
      attributes:
          value: |
              Thanks for suggesting a new feature!

    - type: textarea
      id: problem
      attributes:
          label: Is your feature request related to a problem?
          description: A clear description of what the problem is
          placeholder: I'm always frustrated when...
      validations:
          required: true

    - type: textarea
      id: solution
      attributes:
          label: Describe the solution you'd like
          description: A clear description of what you want to happen
      validations:
          required: true

    - type: textarea
      id: alternatives
      attributes:
          label: Describe alternatives you've considered
          description: Any alternative solutions or features you've considered

    - type: textarea
      id: context
      attributes:
          label: Additional context
          description: Add any other context or screenshots
```

---

## Complete Setup Checklist

### Initial Setup (One-time)

- [ ] Enable Dependabot in repository settings
- [ ] Set up branch protection rules for `main`
- [ ] Add required secrets to repository:
    - `CODECOV_TOKEN`
    - `SONAR_TOKEN`
    - `CC_TEST_REPORTER_ID`
- [ ] Configure SonarCloud project
- [ ] Configure Codecov project
- [ ] Install GitHub Apps:
    - Dependabot (built-in)
    - Snyk
    - GitGuardian
    - Mergify (optional)
- [ ] Add status badges to README
- [ ] Create PR template
- [ ] Create issue templates

### Per Developer Setup

- [ ] Install Husky hooks: `npx husky install`
- [ ] Configure IDE for auto-format on save
- [ ] Test local git hooks work

---

## Cost Analysis

| Tool               | Cost                  | Worth It?      |
| ------------------ | --------------------- | -------------- |
| **GitHub Actions** | Free (2000 min/month) | ✅ Essential   |
| **Dependabot**     | Free                  | ✅ Essential   |
| **CodeQL**         | Free for public repos | ✅ Essential   |
| **Codecov**        | Free for open source  | ✅ Recommended |
| **SonarCloud**     | Free for open source  | ✅ Recommended |
| **Snyk**           | Free tier available   | ✅ Recommended |
| **Code Climate**   | $50/mo (paid only)    | ⚠️ Optional    |
| **Mergify**        | Free for open source  | ⚠️ Optional    |
| **GitGuardian**    | $18/dev/mo            | ⚠️ Optional    |

**Recommended Stack (Free):**

- ✅ GitHub Actions
- ✅ Dependabot
- ✅ CodeQL
- ✅ Codecov
- ✅ SonarCloud
- ✅ Snyk

---

## Summary

### What Happens on Every PR

1. **Automated Checks Run:**
    - Code style (Pint, ESLint)
    - Static analysis (PHPStan)
    - Tests with coverage
    - Security scanning
    - Bundle size check
    - Database migrations test

2. **Bots Comment:**
    - Coverage changes
    - Code quality issues
    - Security vulnerabilities
    - Performance regressions

3. **Status Checks:**
    - All must pass before merge
    - PR can't be merged if failing
    - Automatic labels added

4. **Auto-Actions:**
    - Dependabot creates update PRs
    - Small safe changes can auto-merge
    - Large PRs get flagged for review

### Benefits

- ✅ **Catch bugs early** - Before they reach production
- ✅ **Consistent quality** - All code meets standards
- ✅ **Security** - Vulnerabilities detected automatically
- ✅ **Fast reviews** - Automated checks reduce manual review time
- ✅ **Documentation** - Forces docs updates
- ✅ **Confidence** - Merge with confidence

---

**Document Status:** Implementation Guide  
**Estimated Setup Time:** 2-3 hours  
**Maintenance:** Low (automated)
