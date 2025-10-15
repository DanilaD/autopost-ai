#!/bin/bash

###############################################################################
# Pre-Commit Quality & Documentation Check Script
#
# This script runs before every commit to ensure:
# 1. Code quality (linting, formatting)
# 2. Documentation is updated
# 3. Translations are complete for all languages
# 4. Tests pass
# 5. No linter errors
#
# Usage: ./scripts/pre-commit-check.sh
# Or: Automatically via git pre-commit hook
###############################################################################

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Emoji for better visual feedback
CHECK="✅"
CROSS="❌"
WARNING="⚠️"
INFO="ℹ️"
ROCKET="🚀"

# Counter for issues
ISSUES_FOUND=0

###############################################################################
# Helper Functions
###############################################################################

print_header() {
    echo ""
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}${CHECK} $1${NC}"
}

print_error() {
    echo -e "${RED}${CROSS} $1${NC}"
    ISSUES_FOUND=$((ISSUES_FOUND + 1))
}

print_warning() {
    echo -e "${YELLOW}${WARNING} $1${NC}"
}

print_info() {
    echo -e "${BLUE}${INFO} $1${NC}"
}

###############################################################################
# 1. Check Staged Files
###############################################################################

print_header "1. Analyzing Staged Files"

# Get list of staged files
STAGED_FILES=$(git diff --cached --name-only --diff-filter=ACMR || true)
STAGED_PHP_FILES=$(echo "$STAGED_FILES" | grep '\.php$' || true)
STAGED_VUE_FILES=$(git diff --cached --name-only --diff-filter=ACMR | grep '\.vue$' || true)
STAGED_JS_FILES=$(git diff --cached --name-only --diff-filter=ACMR | grep '\.js$' || true)
STAGED_MIGRATION_FILES=$(git diff --cached --name-only --diff-filter=ACMR | grep 'database/migrations' || true)
STAGED_MODEL_FILES=$(git diff --cached --name-only --diff-filter=ACMR | grep 'app/Models' || true)
STAGED_LANG_FILES=$(git diff --cached --name-only --diff-filter=ACMR | grep 'lang/' || true)
STAGED_DOC_FILES=$(git diff --cached --name-only --diff-filter=ACMR | grep 'docs/' || true)

PHP_COUNT=$(echo "$STAGED_PHP_FILES" | wc -l | tr -d ' ')
VUE_COUNT=$(echo "$STAGED_VUE_FILES" | wc -l | tr -d ' ')
JS_COUNT=$(echo "$STAGED_JS_FILES" | wc -l | tr -d ' ')
MIGRATION_COUNT=$(echo "$STAGED_MIGRATION_FILES" | wc -l | tr -d ' ')
MODEL_COUNT=$(echo "$STAGED_MODEL_FILES" | wc -l | tr -d ' ')
LANG_COUNT=$(echo "$STAGED_LANG_FILES" | wc -l | tr -d ' ')
DOC_COUNT=$(echo "$STAGED_DOC_FILES" | wc -l | tr -d ' ')

# Handle empty results (when no files match, wc -l returns 1)
if [ -z "$STAGED_PHP_FILES" ]; then PHP_COUNT=0; fi
if [ -z "$STAGED_VUE_FILES" ]; then VUE_COUNT=0; fi
if [ -z "$STAGED_JS_FILES" ]; then JS_COUNT=0; fi
if [ -z "$STAGED_MIGRATION_FILES" ]; then MIGRATION_COUNT=0; fi
if [ -z "$STAGED_MODEL_FILES" ]; then MODEL_COUNT=0; fi
if [ -z "$STAGED_LANG_FILES" ]; then LANG_COUNT=0; fi
if [ -z "$STAGED_DOC_FILES" ]; then DOC_COUNT=0; fi

print_info "Staged PHP files: $PHP_COUNT"
print_info "Staged Vue files: $VUE_COUNT"
print_info "Staged JS files: $JS_COUNT"
print_info "Staged Migrations: $MIGRATION_COUNT"
print_info "Staged Models: $MODEL_COUNT"
print_info "Staged Language files: $LANG_COUNT"
print_info "Staged Documentation: $DOC_COUNT"

if [ "$PHP_COUNT" -eq 0 ] && [ "$VUE_COUNT" -eq 0 ] && [ "$JS_COUNT" -eq 0 ]; then
    print_warning "No code files staged. Skipping code quality checks."
fi

###############################################################################
# 2. PHP Code Quality Checks
###############################################################################

if [ "$PHP_COUNT" -gt 0 ]; then
    print_header "2. PHP Code Quality (Pint)"
    
    print_info "Running Laravel Pint..."
    if ./vendor/bin/pint --test $STAGED_PHP_FILES 2>/dev/null; then
        print_success "PHP code formatting is correct"
    else
        print_error "PHP code formatting issues found"
        print_info "Run: ./vendor/bin/pint to fix automatically"
    fi
    
    # Run PHPStan if available
    if [ -f "./vendor/bin/phpstan" ]; then
        print_info "Running PHPStan static analysis..."
        if ./vendor/bin/phpstan analyse --memory-limit=1G $STAGED_PHP_FILES 2>/dev/null; then
            print_success "PHPStan analysis passed"
        else
            print_warning "PHPStan found potential issues"
        fi
    fi
fi

###############################################################################
# 3. JavaScript/Vue Code Quality
###############################################################################

if [ "$VUE_COUNT" -gt 0 ] || [ "$JS_COUNT" -gt 0 ]; then
    print_header "3. JavaScript/Vue Code Quality (ESLint)"
    
    ALL_JS_FILES="$STAGED_VUE_FILES $STAGED_JS_FILES"
    
    print_info "Running ESLint..."
    if npm run lint -- $ALL_JS_FILES 2>/dev/null; then
        print_success "JavaScript/Vue code is clean"
    else
        print_error "ESLint found issues"
        print_info "Run: npm run lint -- --fix to fix automatically"
    fi
fi

###############################################################################
# 4. Documentation Check (MANDATORY)
###############################################################################

print_header "4. Documentation Check ${WARNING} MANDATORY"

DOCS_NEED_UPDATE=false

# Check if database schema changed
if [ "$MIGRATION_COUNT" -gt 0 ]; then
    print_info "Database migrations detected"
    
    # Check if DATABASE_SCHEMA.md is staged
    if echo "$STAGED_DOC_FILES" | grep -q "DATABASE_SCHEMA.md"; then
        print_success "DATABASE_SCHEMA.md is updated"
    else
        print_error "DATABASE_SCHEMA.md needs to be updated!"
        print_info "  → docs/DATABASE_SCHEMA.md"
        DOCS_NEED_UPDATE=true
    fi
fi

# Check if models changed
if [ "$MODEL_COUNT" -gt 0 ]; then
    print_info "Model files changed: $MODEL_COUNT"
    
    # List changed models
    for model in $STAGED_MODEL_FILES; do
        MODEL_NAME=$(basename "$model" .php)
        print_info "  → $MODEL_NAME"
        
        # Check if it's Instagram related
        if echo "$model" | grep -q "Instagram"; then
            if echo "$STAGED_DOC_FILES" | grep -q "INSTAGRAM"; then
                print_success "Instagram documentation is being updated"
            else
                print_warning "Consider updating docs/INSTAGRAM_HYBRID_OWNERSHIP.md"
            fi
        fi
    done
fi

# Check if INDEX.md needs update
if [ "$DOC_COUNT" -gt 0 ]; then
    # Check if new doc files added (not just modified)
    NEW_DOCS=$(git diff --cached --name-only --diff-filter=A | grep 'docs/' | grep -v 'INDEX.md' || true)
    if [ -n "$NEW_DOCS" ]; then
        if echo "$STAGED_DOC_FILES" | grep -q "INDEX.md"; then
            print_success "INDEX.md is being updated with new docs"
        else
            print_error "New documentation added but INDEX.md not updated!"
            print_info "  → Update docs/INDEX.md to reference new docs"
            DOCS_NEED_UPDATE=true
        fi
    fi
fi

# Check if any code changed but no docs updated
if [ "$PHP_COUNT" -gt 0 ] || [ "$VUE_COUNT" -gt 0 ]; then
    if [ "$DOC_COUNT" -eq 0 ]; then
        print_warning "Code changes detected but no documentation updated"
        print_info "Please review if docs need updates:"
        print_info "  → docs/DATABASE_SCHEMA.md (if database changed)"
        print_info "  → docs/INSTAGRAM_HYBRID_OWNERSHIP.md (if Instagram features changed)"
        print_info "  → docs/INDEX.md (if major features added)"
    else
        print_success "Documentation is being updated with code changes"
    fi
fi

if [ "$DOCS_NEED_UPDATE" = true ]; then
    print_error "Documentation updates are REQUIRED before commit"
    echo ""
    echo -e "${YELLOW}📚 Please update the following:${NC}"
    echo "  1. Review /docs folder for related documentation"
    echo "  2. Update affected documentation files"
    echo "  3. Update version/date in modified docs"
    echo "  4. Stage updated docs: git add docs/"
    echo ""
fi

###############################################################################
# 5. Translation Validation (3 Languages)
###############################################################################

print_header "5. Translation Validation (EN, RU, ES)"

TRANSLATION_ISSUES=false

# Check if any language files changed
if [ "$LANG_COUNT" -gt 0 ]; then
    print_info "Language files changed detected"
    
    # List of required languages
    LANGUAGES=("en" "es" "ru")
    
    # Get all translation files being modified
    for lang_file in $STAGED_LANG_FILES; do
        # Extract the relative path after lang/XX/
        REL_PATH=$(echo "$lang_file" | sed 's|lang/[^/]*/||')
        FILE_NAME=$(basename "$lang_file")
        
        print_info "Checking translations for: $FILE_NAME"
        
        # Check if this file exists in all 3 languages
        for lang in "${LANGUAGES[@]}"; do
            CHECK_PATH="lang/$lang/$REL_PATH"
            
            if [ -f "$CHECK_PATH" ]; then
                # Check if this language version is also staged or already exists
                if echo "$STAGED_LANG_FILES" | grep -q "$CHECK_PATH" || git ls-files --error-unmatch "$CHECK_PATH" >/dev/null 2>&1; then
                    print_success "  [$lang] $FILE_NAME exists"
                else
                    print_error "  [$lang] $FILE_NAME is missing or not staged!"
                    TRANSLATION_ISSUES=true
                fi
            else
                print_error "  [$lang] $FILE_NAME does not exist!"
                print_info "     → Create: $CHECK_PATH"
                TRANSLATION_ISSUES=true
            fi
        done
        
        echo ""
    done
    
    # Check for translation key consistency
    print_info "Validating translation keys consistency..."
    
    # Get all PHP files that might have translation keys
    TRANSLATION_FILES="lang/en/*.php lang/es/*.php lang/ru/*.php"
    
    for file_type in "auth" "dashboard" "instagram" "menu" "pagination" "passwords" "validation"; do
        if [ -f "lang/en/$file_type.php" ]; then
            # Extract keys from English (base language)
            EN_KEYS=$(php -r "print_r(array_keys(include 'lang/en/$file_type.php'));" 2>/dev/null | grep '^\[' | wc -l || echo "0")
            
            # Check Spanish
            if [ -f "lang/es/$file_type.php" ]; then
                ES_KEYS=$(php -r "print_r(array_keys(include 'lang/es/$file_type.php'));" 2>/dev/null | grep '^\[' | wc -l || echo "0")
            else
                ES_KEYS=0
            fi
            
            # Check Russian
            if [ -f "lang/ru/$file_type.php" ]; then
                RU_KEYS=$(php -r "print_r(array_keys(include 'lang/ru/$file_type.php'));" 2>/dev/null | grep '^\[' | wc -l || echo "0")
            else
                RU_KEYS=0
            fi
            
            if [ "$EN_KEYS" -eq "$ES_KEYS" ] && [ "$EN_KEYS" -eq "$RU_KEYS" ]; then
                print_success "  $file_type.php: All languages have matching keys ($EN_KEYS keys)"
            else
                print_warning "  $file_type.php: Key count mismatch (EN: $EN_KEYS, ES: $ES_KEYS, RU: $RU_KEYS)"
            fi
        fi
    done
    
    if [ "$TRANSLATION_ISSUES" = true ]; then
        print_error "Translation validation failed!"
        echo ""
        echo -e "${YELLOW}🌍 Translation Requirements:${NC}"
        echo "  1. Every translation file must exist in all 3 languages (en, es, ru)"
        echo "  2. All translation files must have matching keys"
        echo "  3. Stage all language files together: git add lang/"
        echo ""
    else
        print_success "All translations are complete and consistent"
    fi
else
    print_info "No translation files changed"
fi

###############################################################################
# 6. Run Tests & Validate Test Updates
###############################################################################

print_header "6. Running Tests & Validating Test Updates"

# Check if tests need to be updated when code changes
TEST_UPDATE_REQUIRED=false

if [ "$PHP_COUNT" -gt 0 ]; then
    print_info "PHP code changes detected - checking for test updates..."
    
    # Check if any test files are being updated
    TEST_FILES_CHANGED=$(echo "$STAGED_FILES" | grep -E "^tests/" | wc -l)
    
    if [ "$TEST_FILES_CHANGED" -eq 0 ]; then
        print_warning "Code changes detected but NO test files updated!"
        print_info "Mandatory test update rule: Every code change must include test updates"
        TEST_UPDATE_REQUIRED=true
    else
        print_success "Test files are being updated with code changes"
    fi
    
# Check for specific patterns that require test updates
if echo "$STAGED_PHP_FILES" | grep -q "Services/" && ! echo "$STAGED_PHP_FILES" | grep -q "tests/"; then
    SERVICE_FILES=$(echo "$STAGED_PHP_FILES" | grep "Services/" | grep -v "tests/")
    for service_file in $SERVICE_FILES; do
        service_name=$(basename "$service_file" .php)
        test_file="tests/Unit/Services/${service_name}Test.php"
        if [ ! -f "$test_file" ]; then
            print_error "Service $service_name has no corresponding test file: $test_file"
            TEST_UPDATE_REQUIRED=true
        fi
    done
fi

if echo "$STAGED_PHP_FILES" | grep -q "Controllers/" && ! echo "$STAGED_PHP_FILES" | grep -q "tests/"; then
    CONTROLLER_FILES=$(echo "$STAGED_PHP_FILES" | grep "Controllers/" | grep -v "tests/")
    for controller_file in $CONTROLLER_FILES; do
        controller_name=$(basename "$controller_file" .php)
        test_file="tests/Feature/${controller_name}Test.php"
        if [ ! -f "$test_file" ]; then
            print_error "Controller $controller_name has no corresponding test file: $test_file"
            TEST_UPDATE_REQUIRED=true
        fi
    done
fi

if echo "$STAGED_PHP_FILES" | grep -q "Models/" && ! echo "$STAGED_PHP_FILES" | grep -q "tests/"; then
    MODEL_FILES=$(echo "$STAGED_PHP_FILES" | grep "Models/" | grep -v "tests/")
    for model_file in $MODEL_FILES; do
        model_name=$(basename "$model_file" .php)
        test_file="tests/Unit/Models/${model_name}Test.php"
        if [ ! -f "$test_file" ]; then
            print_error "Model $model_name has no corresponding test file: $test_file"
            TEST_UPDATE_REQUIRED=true
        fi
    done
fi

# Check for migration-model-factory consistency
MIGRATION_FILES=$(echo "$STAGED_FILES" | grep -E "^database/migrations/.*\.php$")
if [ -n "$MIGRATION_FILES" ]; then
    print_info "Migration files detected - checking model/factory consistency..."
    
    for migration_file in $MIGRATION_FILES; do
        migration_name=$(basename "$migration_file" .php)
        
        # Extract table name from migration (improved pattern matching)
        if echo "$migration_name" | grep -q "create_.*_table"; then
            # Extract table name from create_*_table pattern (remove timestamp prefix)
            table_name=$(echo "$migration_name" | sed 's/^[0-9_]*create_\(.*\)_table$/\1/')
            
            # Convert table name to model name (snake_case to PascalCase)
            model_name=$(echo "$table_name" | awk -F'_' '{for(i=1;i<=NF;i++) $i=toupper(substr($i,1,1)) substr($i,2)}1' | sed 's/ //g')
            
            model_file="app/Models/${model_name}.php"
            factory_file="database/factories/${model_name}Factory.php"
            
            # Check if model exists
            if [ ! -f "$model_file" ]; then
                print_error "Migration $migration_name creates table '$table_name' but no model found: $model_file"
                TEST_UPDATE_REQUIRED=true
            fi
            
            # Check if factory exists
            if [ ! -f "$factory_file" ]; then
                print_error "Migration $migration_name creates table '$table_name' but no factory found: $factory_file"
                TEST_UPDATE_REQUIRED=true
            fi
        fi
        
        # Check for modify migrations (skip these as they don't create new tables)
        if echo "$migration_name" | grep -q "modify_.*_table\|add_.*_to_.*_table\|update_.*_table"; then
            print_info "Skipping modify migration: $migration_name (no new table created)"
        fi
    done
fi

# Check for model changes without migration updates
MODEL_FILES=$(echo "$STAGED_PHP_FILES" | grep "Models/")
if [ -n "$MODEL_FILES" ] && [ -z "$MIGRATION_FILES" ]; then
    print_warning "Model files changed but no migration files detected"
    print_info "Please verify if model changes require migration updates"
fi

# Check for factory changes without model updates
FACTORY_FILES=$(echo "$STAGED_FILES" | grep -E "^database/factories/.*\.php$")
if [ -n "$FACTORY_FILES" ] && [ -z "$MODEL_FILES" ]; then
    print_warning "Factory files changed but no model files detected"
    print_info "Please verify if factory changes require model updates"
fi
fi

# Run tests
print_info "Running PHPUnit tests..."

# Run only affected tests based on changed files
if [ "$PHP_COUNT" -gt 0 ]; then
    # Check if any Instagram related files changed
    if echo "$STAGED_PHP_FILES" | grep -q "Instagram"; then
        print_info "Instagram files changed, running Instagram tests..."
        if php artisan test --filter=Instagram --stop-on-failure; then
            print_success "Instagram tests passed"
        else
            print_error "Instagram tests failed"
        fi
    else
        # Run all tests
        print_info "Running all tests..."
        if php artisan test --stop-on-failure --parallel; then
            print_success "All tests passed"
        else
            print_error "Tests failed"
        fi
    fi
else
    print_info "No PHP files changed, skipping tests"
fi

# Check test coverage for new/modified files
if [ "$PHP_COUNT" -gt 0 ]; then
    print_info "Checking test coverage for modified files..."
    
    # Count test methods in new test files
    NEW_TEST_FILES=$(echo "$STAGED_FILES" | grep -E "^tests/.*Test\.php$")
    if [ -n "$NEW_TEST_FILES" ]; then
        TOTAL_TEST_METHODS=0
        for test_file in $NEW_TEST_FILES; do
            if [ -f "$test_file" ]; then
                METHODS=$(grep -c "public function test_\|it(" "$test_file" 2>/dev/null || echo "0")
                TOTAL_TEST_METHODS=$((TOTAL_TEST_METHODS + METHODS))
                print_info "  $test_file: $METHODS test methods"
            fi
        done
        print_success "Total new test methods: $TOTAL_TEST_METHODS"
    fi
fi

if [ "$TEST_UPDATE_REQUIRED" = true ]; then
    print_error "Test updates are REQUIRED before commit"
    echo ""
    echo -e "${YELLOW}🧪 Please add/update tests:${NC}"
    echo "  1. Create test files for new Services/Controllers/Models"
    echo "  2. Update existing tests for modified functionality"
    echo "  3. Ensure all tests pass: php artisan test"
    echo "  4. Stage test files: git add tests/"
    echo ""
    echo -e "${YELLOW}📋 Test Coverage Requirements:${NC}"
    echo "  • Services: 90% coverage"
    echo "  • Repositories: 80% coverage"
    echo "  • Controllers: 70% coverage"
    echo "  • Models: 85% coverage"
    echo ""
    echo -e "${YELLOW}🗄️ Migration-Model-Factory Requirements:${NC}"
    echo "  • Every migration change must include model and factory updates"
    echo "  • Every new table must have corresponding model and factory"
    echo "  • Every model change must have corresponding tests"
    echo "  • Every factory must be documented and tested"
    echo ""
fi

###############################################################################
# 7. Check for Common Issues
###############################################################################

print_header "7. Additional Checks"

# Check for debugging statements
print_info "Checking for debug statements..."
DEBUG_FOUND=false

for file in $STAGED_PHP_FILES; do
    if grep -q "dd(" "$file" || grep -q "dump(" "$file" || grep -q "var_dump(" "$file"; then
        print_warning "Debug statement found in: $file"
        DEBUG_FOUND=true
    fi
done

for file in $STAGED_VUE_FILES $STAGED_JS_FILES; do
    if grep -q "console.log" "$file"; then
        print_warning "console.log found in: $file"
        DEBUG_FOUND=true
    fi
done

if [ "$DEBUG_FOUND" = false ]; then
    print_success "No debug statements found"
fi

# Check for hardcoded secrets
print_info "Checking for hardcoded secrets..."
SECRETS_FOUND=false

for file in $STAGED_PHP_FILES; do
    if grep -qi "password.*=.*['\"].*['\"]" "$file" || \
       grep -qi "api_key.*=.*['\"].*['\"]" "$file" || \
       grep -qi "secret.*=.*['\"].*['\"]" "$file"; then
        print_warning "Potential hardcoded secret found in: $file"
        SECRETS_FOUND=true
    fi
done

if [ "$SECRETS_FOUND" = false ]; then
    print_success "No hardcoded secrets found"
fi

# Check for N+1 query patterns
print_info "Checking for N+1 query patterns..."
N1_FOUND=false

for file in $STAGED_PHP_FILES; do
    if grep -q "foreach.*->" "$file" && grep -q "->" "$file" && ! grep -q "with(" "$file"; then
        print_warning "Potential N+1 query pattern in: $file"
        N1_FOUND=true
    fi
done

if [ "$N1_FOUND" = false ]; then
    print_success "No N+1 query patterns detected"
fi

# Check for TODO comments
print_info "Checking for TODO comments..."
TODO_FOUND=false

for file in $STAGED_PHP_FILES $STAGED_VUE_FILES $STAGED_JS_FILES; do
    if grep -q "TODO:" "$file" || grep -q "FIXME:" "$file"; then
        print_warning "TODO/FIXME found in: $file"
        TODO_FOUND=true
    fi
done

if [ "$TODO_FOUND" = false ]; then
    print_success "No TODO/FIXME comments found"
fi

# Check for large files
print_info "Checking for large files..."
LARGE_FILES_FOUND=false

for file in $(git diff --cached --name-only); do
    if [ -f "$file" ]; then
        SIZE=$(wc -c < "$file")
        if [ "$SIZE" -gt 500000 ]; then  # 500KB
            print_warning "Large file detected ($(($SIZE / 1024))KB): $file"
            LARGE_FILES_FOUND=true
        fi
    fi
done

if [ "$LARGE_FILES_FOUND" = false ]; then
    print_success "No unusually large files"
fi

###############################################################################
# 8. Site Pattern / Dark Mode / Timezone / i18n Audits
###############################################################################

print_header "8. Site Pattern / Dark Mode / Timezone / i18n Audits"

# i18n parity: ensure same files exist across en/es/ru when any lang files staged
if [ "$LANG_COUNT" -gt 0 ]; then
  print_info "Checking i18n file parity (en/es/ru) for staged files..."
  for lang_file in $STAGED_LANG_FILES; do
    REL_PATH=$(echo "$lang_file" | sed 's|lang/[^/]*/||')
    for lang in en es ru; do
      CHECK_PATH="lang/$lang/$REL_PATH"
      if [ ! -f "$CHECK_PATH" ]; then
        print_error "Missing translation file: $CHECK_PATH"
      fi
    done
  done
fi

# Gitignore audit: ensure required entries exist and auto-append missing ones
print_header "8.1 Gitignore Audit"

REQUIRED_GITIGNORE_ENTRIES=(
  "node_modules/"
  "vendor/"
  "storage/"
  "public/build/"
  "public/hot"
  ".DS_Store"
  "/.env"
  "/.env.*"
  "bootstrap/cache/"
)

GITIGNORE_UPDATED=false

# Create .gitignore if missing
if [ ! -f .gitignore ]; then
  print_warning ".gitignore not found. Creating a new one."
  touch .gitignore
fi

for entry in "${REQUIRED_GITIGNORE_ENTRIES[@]}"; do
  if ! grep -qxF "$entry" .gitignore; then
    echo "$entry" >> .gitignore
    print_info "Added to .gitignore: $entry"
    GITIGNORE_UPDATED=true
  fi
done

if [ "$GITIGNORE_UPDATED" = true ]; then
  git add .gitignore >/dev/null 2>&1 || true
  print_success ".gitignore updated and staged"
else
  print_success ".gitignore already contains required entries"
fi

# Dark-mode audit: flag lines adding bg-white or text-black without dark: fallback
if [ "$VUE_COUNT" -gt 0 ]; then
  print_info "Auditing staged Vue files for dark-mode unsafe classes..."
  for vf in $STAGED_VUE_FILES; do
    if grep -n "bg-white" "$vf" | grep -v "dark:" >/dev/null 2>&1; then
      print_warning "Dark-mode: bg-white without dark: fallback in $vf"
    fi
    if grep -n "text-black" "$vf" | grep -v "dark:" >/dev/null 2>&1; then
      print_warning "Dark-mode: text-black without dark: fallback in $vf"
    fi
    # Require dark mode pairing for headers/text commonly used
    if grep -n "text-gray-900" "$vf" | grep -v "dark:text-gray-100" >/dev/null 2>&1; then
      print_warning "Dark-mode: text-gray-900 missing dark:text-gray-100 in $vf"
    fi
    if grep -n "text-gray-700" "$vf" | grep -v "dark:text-gray-300" >/dev/null 2>&1; then
      print_warning "Dark-mode: text-gray-700 missing dark:text-gray-300 in $vf"
    fi
  done
fi

# Timezone audit: prefer server-normalized fields (e.g., created_at_display)
if [ "$VUE_COUNT" -gt 0 ]; then
  print_info "Auditing timezone usage in staged Vue pages..."
  for vf in $STAGED_VUE_FILES; do
    if echo "$vf" | grep -q "/Pages/"; then
      if grep -q "created_at[^_]" "$vf" && ! grep -q "created_at_display" "$vf"; then
        print_warning "Timezone: $vf uses created_at directly. Prefer server-normalized created_at_display or formatInTimezone()"
      fi
    fi
  done
fi

# Dead/unused code (JS): run eslint with stricter unused checks on staged JS/Vue
if [ "$VUE_COUNT" -gt 0 ] || [ "$JS_COUNT" -gt 0 ]; then
  print_info "Running ESLint unused checks (no-unused-vars/components)..."
  ALL_JS_FILES="$STAGED_VUE_FILES $STAGED_JS_FILES"
  if npm run lint -- $ALL_JS_FILES --rule "no-unused-vars:error" 2>/dev/null; then
    print_success "No unused variables detected in staged JS/Vue"
  else
    print_warning "ESLint reported unused variables/components"
  fi
fi

# Pattern conformance: block inline style attributes in Vue
if [ "$VUE_COUNT" -gt 0 ]; then
  for vf in $STAGED_VUE_FILES; do
    if grep -q "style=\"" "$vf"; then
      print_error "Inline style attribute found in $vf (use classes/components instead)"
      ISSUES_FOUND=$((ISSUES_FOUND+1))
    fi
  done
fi

# Block deprecated MD3 token classes and pattern-* classes
if [ "$VUE_COUNT" -gt 0 ]; then
  for vf in $STAGED_VUE_FILES; do
    if grep -Eq "(bg-md-|text-md-|shadow-elevation-)" "$vf"; then
      print_error "MD3 token classes found in $vf (use Tailwind equivalents)"
      ISSUES_FOUND=$((ISSUES_FOUND+1))
    fi
    if grep -q "pattern-" "$vf"; then
      print_error "Deprecated pattern-* class found in $vf"
      ISSUES_FOUND=$((ISSUES_FOUND+1))
    fi
  done
fi

# Frontend hard-code audit: discourage magic hex colors and hardcoded external URLs
if [ "$VUE_COUNT" -gt 0 ]; then
  for vf in $STAGED_VUE_FILES; do
    if grep -Eqi "#[0-9a-fA-F]{3,6}" "$vf"; then
      print_warning "Hardcoded hex color detected in $vf (prefer Tailwind classes or design tokens)"
    fi
    if grep -Eq "https?://" "$vf"; then
      if ! grep -Eq "route\(|this\\.\$inertia|this\\.\$router" "$vf"; then
        print_warning "Hardcoded external URL in $vf (prefer route()/config-driven URLs)"
      fi
    fi
  done
fi

# Reuse guidance: flag very long class attributes (consider extracting components/utilities)
if [ "$VUE_COUNT" -gt 0 ]; then
  for vf in $STAGED_VUE_FILES; do
    # Lines with class="..." longer than 200 chars
    if awk '/class="/ { if (length($0) > 200) { print; exit 0 } }' "$vf" >/dev/null; then
      print_warning "Very long class attribute in $vf (consider a reusable component or utility class)"
    fi
  done
fi

###############################################################################
# 9. Architecture Guards (Controllers/Services/Repositories/Models/Enums)
###############################################################################

print_header "9. Architecture Guards"

# Helper: iterate staged PHP files by path prefix
for file in $STAGED_PHP_FILES; do
  # Controllers should be thin: no DB calls or direct model persistence
  if echo "$file" | grep -q "^app/Http/Controllers/"; then
    if grep -qE "DB::|->save\(|->create\(|->update\(|->delete\(|::create\(|::update\(|::delete\(" "$file"; then
      print_warning "Controller uses DB/Model persistence directly: $file (move to Service/Repository)"
    fi
    if grep -q "\\$request->validate(" "$file"; then
      print_warning "Controller inline validation found: $file (prefer FormRequest)"
    fi
    if ! grep -q "use App\\Services\\" "$file"; then
      print_warning "Controller may not delegate to a Service: $file (ensure business logic in Service)"
    fi
  fi

  # Services should not query DB directly; require Repository usage
  if echo "$file" | grep -q "^app/Services/"; then
    if grep -qE "DB::|->save\(|::create\(|::update\(|::delete\(|::query\(" "$file"; then
      print_warning "Service uses DB/Model directly: $file (prefer Repository injection)"
    fi
    if ! grep -q "use App\\Repositories\\" "$file" 2>/dev/null; then
      print_warning "Service missing Repository dependency: $file"
    fi
  fi

  # Repositories should avoid business terms
  if echo "$file" | grep -q "^app/Repositories/"; then
    if grep -qiE "calculate|policy|rule|pricing|permission" "$file"; then
      print_warning "Repository contains business-domain terms: $file (move to Service)"
    fi
  fi

  # Models restricted to relationships/casts
  if echo "$file" | grep -q "^app/Models/"; then
    if grep -qE "DB::|::create\(|::update\(|::delete\(|->save\(" "$file"; then
      print_warning "Model performs persistence logic: $file (models should expose relationships/casts only)"
    fi
  fi

  # Enums: discourage magic role/status strings when Enum exists
  if echo "$file" | grep -qE "^app/Http/Controllers/|^app/Services/"; then
    if grep -qE "'admin'|'user'|'network'" "$file" && ! grep -qE "use App\\Enums\\UserRole|UserRole::" "$file"; then
      print_warning "Magic role strings found without enum usage: $file (use App\\Enums\\UserRole)"
    fi
  fi
done

###############################################################################
# 10. Summary
###############################################################################

print_header "Summary"

if [ "$ISSUES_FOUND" -gt 0 ]; then
    echo ""
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${RED}  ${CROSS} COMMIT BLOCKED - $ISSUES_FOUND ISSUE(S) FOUND${NC}"
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    echo -e "${YELLOW}Please fix the issues above and try again.${NC}"
    echo ""
    echo -e "${BLUE}Quick fixes:${NC}"
    echo "  → Format PHP: ./vendor/bin/pint"
    echo "  → Format JS/Vue: npm run lint -- --fix"
    echo "  → Update docs: Review /docs folder"
    echo "  → Add translations: Ensure all 3 languages (en, es, ru)"
    echo ""
    exit 1
else
    echo ""
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}  ${CHECK} ALL CHECKS PASSED ${ROCKET}${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    echo -e "${GREEN}Your commit is ready!${NC}"
    echo ""
    exit 0
fi

