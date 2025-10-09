# Code Quality & Pre-Push Checks Setup

**Version:** 1.0  
**Date:** October 9, 2025  
**Status:** Implementation Guide

---

## Overview

Automated code quality checks that run before every commit and push to ensure:

- ✅ PHP code follows PSR-12 standards (Laravel Pint)
- ✅ JavaScript/Vue code follows ESLint rules
- ✅ No syntax errors
- ✅ Tests pass before push
- ✅ No console.log() in production code

---

## Tools Setup

### 1. PHP Tools (Already Installed)

**Laravel Pint** - Opinionated PHP code style fixer

- Already installed: `laravel/pint: ^1.24`
- Config: Uses Laravel preset by default

**PHPStan** - PHP Static Analysis (Optional but recommended)

- Will install for type checking and bug detection

### 2. JavaScript/Vue Tools (Need to Install)

**ESLint** - JavaScript linter
**Prettier** - Code formatter
**Vue ESLint Plugin** - Vue-specific linting rules

### 3. Git Hooks

**Husky** - Modern Git hooks manager

- Runs checks automatically before commit/push
- Easy to configure and maintain

---

## Installation Steps

### Step 1: Install JavaScript Linting Tools

```bash
npm install --save-dev \
  eslint \
  prettier \
  eslint-plugin-vue \
  eslint-config-prettier \
  @vue/eslint-config-prettier \
  eslint-plugin-prettier
```

### Step 2: Install PHPStan (Optional but Recommended)

```bash
composer require --dev phpstan/phpstan larastan/larastan
```

### Step 3: Install Husky for Git Hooks

```bash
npm install --save-dev husky lint-staged
```

### Step 4: Initialize Husky

```bash
npx husky install
npm pkg set scripts.prepare="husky install"
```

---

## Configuration Files

### 1. ESLint Configuration

**Create: `.eslintrc.json`**

```json
{
    "root": true,
    "env": {
        "browser": true,
        "es2021": true,
        "node": true
    },
    "extends": [
        "eslint:recommended",
        "plugin:vue/vue3-recommended",
        "prettier"
    ],
    "parserOptions": {
        "ecmaVersion": "latest",
        "sourceType": "module"
    },
    "plugins": ["vue", "prettier"],
    "rules": {
        "prettier/prettier": "error",
        "vue/multi-word-component-names": "off",
        "vue/require-default-prop": "warn",
        "no-console": ["warn", { "allow": ["warn", "error"] }],
        "no-debugger": "error",
        "no-unused-vars": ["error", { "argsIgnorePattern": "^_" }],
        "vue/html-indent": ["error", 4],
        "vue/max-attributes-per-line": [
            "error",
            {
                "singleline": 3,
                "multiline": 1
            }
        ]
    },
    "ignorePatterns": [
        "vendor/**",
        "node_modules/**",
        "public/build/**",
        "bootstrap/cache/**"
    ]
}
```

### 2. Prettier Configuration

**Create: `.prettierrc.json`**

```json
{
    "semi": true,
    "singleQuote": true,
    "tabWidth": 4,
    "trailingComma": "es5",
    "printWidth": 100,
    "arrowParens": "always",
    "endOfLine": "lf"
}
```

**Create: `.prettierignore`**

```
vendor/
node_modules/
public/build/
bootstrap/cache/
storage/
*.blade.php
```

### 3. Laravel Pint Configuration (Optional Override)

**Create: `pint.json`** (only if you need custom rules)

```json
{
    "preset": "laravel",
    "rules": {
        "no_unused_imports": true,
        "ordered_imports": {
            "sort_algorithm": "alpha"
        },
        "phpdoc_align": {
            "align": "left"
        }
    },
    "exclude": ["vendor", "storage", "bootstrap/cache"]
}
```

### 4. PHPStan Configuration

**Create: `phpstan.neon`**

```neon
includes:
    - ./vendor/larastan/larastan/extension.neon

parameters:
    paths:
        - app/
    level: 5
    ignoreErrors:
        - '#Unsafe usage of new static#'
    excludePaths:
        - ./*/*/FileToBeExcluded.php
    checkMissingIterableValueType: false
```

### 5. Lint-Staged Configuration

**Add to `package.json`:**

```json
{
    "lint-staged": {
        "*.php": ["./vendor/bin/pint", "git add"],
        "*.{js,vue}": ["eslint --fix", "prettier --write", "git add"],
        "*.{css,scss}": ["prettier --write", "git add"]
    }
}
```

---

## Git Hooks Setup

### Hook 1: Pre-Commit (Format & Lint)

**Create: `.husky/pre-commit`**

```bash
#!/usr/bin/env sh
. "$(dirname -- "$0")/_/husky.sh"

echo "🔍 Running pre-commit checks..."

# Run lint-staged (formats PHP, JS, Vue)
npx lint-staged

# Check for syntax errors
echo "📝 Checking PHP syntax..."
find app tests -name "*.php" -print0 | xargs -0 -n1 php -l

if [ $? -ne 0 ]; then
    echo "❌ PHP syntax errors found. Please fix them before committing."
    exit 1
fi

echo "✅ Pre-commit checks passed!"
```

**Make it executable:**

```bash
chmod +x .husky/pre-commit
```

### Hook 2: Pre-Push (Run Tests)

**Create: `.husky/pre-push`**

```bash
#!/usr/bin/env sh
. "$(dirname -- "$0")/_/husky.sh"

echo "🧪 Running pre-push checks..."

# Run PHP tests
echo "📝 Running PHP tests..."
php artisan test

if [ $? -ne 0 ]; then
    echo "❌ Tests failed. Please fix them before pushing."
    exit 1
fi

# Run ESLint
echo "📝 Running ESLint..."
npm run lint

if [ $? -ne 0 ]; then
    echo "❌ ESLint errors found. Please fix them before pushing."
    exit 1
fi

# Optional: Run PHPStan
# echo "📝 Running PHPStan..."
# ./vendor/bin/phpstan analyse

echo "✅ All pre-push checks passed! 🚀"
```

**Make it executable:**

```bash
chmod +x .husky/pre-push
```

---

## NPM Scripts

**Add to `package.json` scripts:**

```json
{
    "scripts": {
        "dev": "vite",
        "build": "vite build",
        "lint": "eslint resources/js --ext .js,.vue --fix",
        "lint:check": "eslint resources/js --ext .js,.vue",
        "format": "prettier --write \"resources/**/*.{js,vue,css}\"",
        "format:check": "prettier --check \"resources/**/*.{js,vue,css}\"",
        "prepare": "husky install"
    }
}
```

---

## Composer Scripts

**Add to `composer.json` scripts:**

```json
{
    "scripts": {
        "lint": "./vendor/bin/pint",
        "lint:test": "./vendor/bin/pint --test",
        "stan": "./vendor/bin/phpstan analyse",
        "test": "@php artisan test",
        "check": ["@lint:test", "@stan", "@test"]
    }
}
```

---

## Usage

### Manual Commands

**Format PHP code:**

```bash
composer lint
# or
./vendor/bin/pint
```

**Check PHP code without changes:**

```bash
composer lint:test
```

**Run PHPStan analysis:**

```bash
composer stan
# or
./vendor/bin/phpstan analyse
```

**Lint JavaScript/Vue:**

```bash
npm run lint
```

**Check JavaScript/Vue without changes:**

```bash
npm run lint:check
```

**Format JavaScript/Vue:**

```bash
npm run format
```

**Run all checks:**

```bash
composer check  # PHP checks + tests
npm run lint:check  # JS checks
```

### Automatic (Git Hooks)

**On commit:**

- ✅ Auto-formats PHP files (Pint)
- ✅ Auto-formats JS/Vue files (ESLint + Prettier)
- ✅ Checks for syntax errors
- ✅ Stages formatted files

**On push:**

- ✅ Runs all tests
- ✅ Runs ESLint
- ✅ (Optional) Runs PHPStan

### Bypass Hooks (Emergency Only)

```bash
# Skip pre-commit hook
git commit --no-verify -m "emergency fix"

# Skip pre-push hook
git push --no-verify
```

**⚠️ Use sparingly! Only for emergencies.**

---

## CI/CD Integration (Optional)

### GitHub Actions Example

**Create: `.github/workflows/code-quality.yml`**

```yaml
name: Code Quality

on:
    pull_request:
        branches: [main, develop]

jobs:
    php-tests:
        runs-on: ubuntu-latest

        steps:
            - uses: actions/checkout@v3

            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: '8.2'
                  extensions: mbstring, pdo_sqlite

            - name: Install Dependencies
              run: composer install --prefer-dist --no-progress

            - name: Check Code Style
              run: composer lint:test

            - name: Run PHPStan
              run: composer stan

            - name: Run Tests
              run: composer test

    js-tests:
        runs-on: ubuntu-latest

        steps:
            - uses: actions/checkout@v3

            - name: Setup Node
              uses: actions/setup-node@v3
              with:
                  node-version: '18'

            - name: Install Dependencies
              run: npm install

            - name: Run ESLint
              run: npm run lint:check

            - name: Check Format
              run: npm run format:check
```

---

## Editor Integration

### VS Code Settings

**Create/Update: `.vscode/settings.json`**

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
    },
    "files.associations": {
        "*.blade.php": "blade"
    }
}
```

**Recommended Extensions:**

- Laravel Pint (open-southeners.laravel-pint)
- ESLint (dbaeumer.vscode-eslint)
- Prettier (esbenp.prettier-vscode)
- Vue Language Features (Vue.volar)

### PHPStorm Settings

**File → Settings → Tools → Actions on Save:**

- ✅ Reformat code
- ✅ Optimize imports
- ✅ Run eslint --fix

**File → Settings → PHP → Quality Tools:**

- Configure Laravel Pint path: `vendor/bin/pint`
- Configure PHPStan path: `vendor/bin/phpstan`

---

## Troubleshooting

### Issue: Husky hooks not running

**Solution:**

```bash
rm -rf .git/hooks
npx husky install
```

### Issue: Pint not found

**Solution:**

```bash
composer install
# or
composer require --dev laravel/pint
```

### Issue: ESLint errors in IDE

**Solution:**

```bash
npm install
# Restart IDE
```

### Issue: Permission denied on hooks

**Solution:**

```bash
chmod +x .husky/pre-commit
chmod +x .husky/pre-push
```

---

## Team Workflow

### First Time Setup (New Developer)

```bash
# 1. Clone repo
git clone <repo-url>
cd autopost-ai

# 2. Install dependencies
composer install
npm install

# 3. Setup hooks
npx husky install

# 4. Test hooks
git commit --allow-empty -m "test hooks"
```

### Daily Workflow

```bash
# 1. Make changes
# ... edit files ...

# 2. Commit (auto-formats and checks)
git add .
git commit -m "feat: add new feature"

# 3. Push (runs tests)
git push origin feature-branch
```

---

## Custom Rules

### Adding New PHP Rules

Edit `pint.json`:

```json
{
    "rules": {
        "new_rule_name": true
    }
}
```

### Adding New ESLint Rules

Edit `.eslintrc.json`:

```json
{
    "rules": {
        "new-rule-name": "error"
    }
}
```

---

## Summary

### What Gets Checked

**Before Commit:**

- ✅ PHP code auto-formatted with Pint
- ✅ JS/Vue code auto-formatted with ESLint + Prettier
- ✅ PHP syntax validation
- ✅ CSS/SCSS formatting

**Before Push:**

- ✅ All PHP tests pass
- ✅ ESLint passes (no errors)
- ✅ (Optional) PHPStan passes

### Benefits

- ✅ **Consistent code style** across team
- ✅ **Catch errors early** before they reach CI/CD
- ✅ **Automatic formatting** - no manual work
- ✅ **Failed builds prevented** - tests run before push
- ✅ **Faster code reviews** - no style discussions

---

**Document Status:** Implementation Guide  
**Estimated Setup Time:** 30-45 minutes  
**Maintenance:** Low (set and forget)
