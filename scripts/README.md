# Quality & Validation Scripts

## 📋 Overview

This directory contains automated scripts to ensure code quality, documentation consistency, and translation completeness before committing code.

---

## 🚀 Quick Start

### 1. Install Git Hooks (Recommended)

```bash
./scripts/install-git-hooks.sh
# or
npm run install-hooks
```

This will automatically run all checks before every commit.

### 2. Run Manual Checks

```bash
# Run all pre-commit checks
./scripts/pre-commit-check.sh
# or
npm run precommit
# or
composer precommit

# Validate translations only
php scripts/translation-validator.php
# or  
npm run validate-translations
# or
composer validate-translations

# Format code
composer format          # PHP (Pint)
npm run format          # JS/Vue (Prettier)

# Check formatting without fixing
composer format-check   # PHP
```

---

## 📁 Scripts

### 1. `pre-commit-check.sh` 🔍

**Comprehensive pre-commit validation script**

**Checks:**
- ✅ PHP code quality (Laravel Pint)
- ✅ JavaScript/Vue code quality (ESLint)
- ✅ Static analysis (PHPStan if available)
- ✅ **Documentation updates** (MANDATORY)
- ✅ **Translation completeness** (EN, ES, RU)
- ✅ Test execution
- ✅ Debug statements detection
- ✅ TODO comments detection
- ✅ Large file detection

**Blocks commit if:**
- Code formatting issues
- Documentation not updated when required
- Translations missing for any language
- Tests fail
- Critical issues found

**Usage:**
```bash
./scripts/pre-commit-check.sh
```

**Output Example:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  1. Analyzing Staged Files
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

ℹ️  Staged PHP files: 3
ℹ️  Staged Vue files: 2
ℹ️  Staged Migrations: 1
✅ All checks passed
```

---

### 2. `translation-validator.php` 🌍

**Validates translation completeness across all languages**

**Checks:**
- All translation files exist in EN, ES, RU
- All translation keys match across languages
- No missing keys
- No empty translations
- Warns about extra keys

**Usage:**
```bash
php scripts/translation-validator.php
```

**Output Example:**
```
🌍 Translation Validator
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📝 Checking: auth.php
  ✅ [en] Loaded (33 keys)
  ✅ [es] Loaded (33 keys)
  ✅ [ru] Loaded (33 keys)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Summary
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✅ All translations are complete and consistent!
```

---

### 3. `install-git-hooks.sh` ⚙️

**Installs git hooks for automatic validation**

**What it does:**
- Creates `.git/hooks/pre-commit`
- Links to `pre-commit-check.sh`
- Makes hooks executable

**Usage:**
```bash
./scripts/install-git-hooks.sh
```

**To bypass once:**
```bash
git commit --no-verify
```

---

## 📋 Pre-Commit Checklist

When the script runs, it checks:

### ✅ Code Quality
- [ ] PHP code formatted with Pint
- [ ] JavaScript/Vue formatted with ESLint
- [ ] No linter errors
- [ ] Static analysis passes

### ✅ Documentation
- [ ] DATABASE_SCHEMA.md updated if migrations changed
- [ ] Instagram docs updated if Instagram features changed
- [ ] INDEX.md updated if new docs added
- [ ] Version/date updated in modified docs

### ✅ Translations
- [ ] All translation files exist in EN, ES, RU
- [ ] All translation keys match
- [ ] No missing translations
- [ ] No empty translations

### ✅ Testing
- [ ] All tests pass
- [ ] Affected feature tests run

### ✅ Clean Code
- [ ] No debug statements (dd, dump, console.log)
- [ ] No unresolved TODO/FIXME comments
- [ ] No unusually large files

---

## 🎯 When Checks Fail

### PHP Formatting Issues
```bash
❌ PHP code formatting issues found
→ Run: ./vendor/bin/pint
```

**Fix:**
```bash
composer format
git add .
git commit
```

### Documentation Missing
```bash
❌ DATABASE_SCHEMA.md needs to be updated!
```

**Fix:**
1. Update `docs/DATABASE_SCHEMA.md`
2. Update version and date at top
3. Stage the doc file:
```bash
git add docs/DATABASE_SCHEMA.md
git commit
```

### Translation Missing
```bash
❌ Missing file: lang/es/auth.php
```

**Fix:**
1. Create missing translation file
2. Copy structure from `lang/en/auth.php`
3. Translate all keys
4. Stage all language files:
```bash
git add lang/
git commit
```

### Tests Failing
```bash
❌ Tests failed
```

**Fix:**
1. Run tests locally: `php artisan test`
2. Fix failing tests
3. Verify: `php artisan test --filter=FailingTest`
4. Commit when green

---

## 🔧 Configuration

### Disable Checks (Not Recommended)

**Skip once:**
```bash
git commit --no-verify
```

**Remove hooks:**
```bash
rm .git/hooks/pre-commit
```

### Customize Checks

Edit `scripts/pre-commit-check.sh` to:
- Change which files trigger checks
- Adjust severity of warnings
- Add custom validations

---

## 📚 Integration with Workflow

### With Git Hooks (Automatic)
```bash
# Install once
./scripts/install-git-hooks.sh

# Now every commit will:
# 1. Run all checks automatically
# 2. Block if issues found
# 3. Show clear error messages
git add .
git commit -m "feat: add new feature"  # Checks run here
```

### Manual (CI/CD)
```bash
# In GitHub Actions / GitLab CI
- name: Run pre-commit checks
  run: ./scripts/pre-commit-check.sh
```

### With Husky (Alternative)
```json
// .husky/pre-commit
#!/bin/sh
./scripts/pre-commit-check.sh
```

---

## ⚡ Quick Commands Reference

```bash
# Install hooks
npm run install-hooks

# Run all checks
npm run precommit
composer precommit
./scripts/pre-commit-check.sh

# Validate translations
npm run validate-translations
composer validate-translations
php scripts/translation-validator.php

# Format code
composer format           # PHP
npm run format           # JS/Vue

# Check formatting
composer format-check    # PHP

# Run tests
php artisan test
composer test

# Skip checks (emergency only)
git commit --no-verify
```

---

## 🐛 Troubleshooting

### "Permission denied"
```bash
chmod +x scripts/*.sh
chmod +x scripts/*.php
```

### "PHPStan not found"
```bash
composer require --dev phpstan/phpstan
```

### "ESLint not found"
```bash
npm install
```

### "Git hooks not running"
```bash
./scripts/install-git-hooks.sh
chmod +x .git/hooks/pre-commit
```

---

## 📊 Exit Codes

| Code | Meaning |
|------|---------|
| 0    | All checks passed ✅ |
| 1    | Checks failed ❌ |

---

## 🎓 Best Practices

1. **Run checks before pushing**
   ```bash
   npm run precommit && git push
   ```

2. **Fix issues immediately**
   - Don't accumulate formatting issues
   - Update docs as you code
   - Add translations for new UI text

3. **Review check output**
   - Read error messages carefully
   - Fix root cause, not just symptoms

4. **Keep scripts updated**
   - Adjust checks as project evolves
   - Add new validations when needed

---

## 🔗 Related Documentation

- [CODING_STANDARDS.md](/docs/CODING_STANDARDS.md) - Pre-commit documentation rule
- [DATABASE_SCHEMA.md](/docs/DATABASE_SCHEMA.md) - Database documentation
- [INTERNATIONALIZATION_PLAN.md](/docs/INTERNATIONALIZATION_PLAN.md) - Translation guide
- [INDEX.md](/docs/INDEX.md) - Documentation index

---

**Last Updated:** October 10, 2025  
**Version:** 1.0  
**Author:** Development Team

