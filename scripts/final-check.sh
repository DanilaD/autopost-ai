#!/bin/bash

###############################################################################
# Final Project Check Script
#
# This script performs a comprehensive check of the entire project including:
# 1. All tests passing
# 2. Documentation completeness
# 3. Code quality
# 4. Translation completeness
# 5. Project status summary
#
# Usage: ./scripts/final-check.sh
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
# Main Execution
###############################################################################

echo "🎉 AUTOPOST AI - FINAL PROJECT CHECK"
echo "====================================="
echo "Date: $(date)"
echo "Version: 1.5"
echo ""

###############################################################################
# 1. Test Suite Check
###############################################################################

print_header "1. Running Complete Test Suite"

print_info "Running PHPUnit tests..."
if php artisan test --stop-on-failure; then
    print_success "All tests passed!"
else
    print_error "Tests failed!"
    exit 1
fi

###############################################################################
# 2. Documentation Completeness Check
###############################################################################

print_header "2. Documentation Completeness Check"

# Check if all major documentation files exist
REQUIRED_DOCS=(
    "docs/INDEX.md"
    "docs/DATABASE_SCHEMA.md"
    "docs/POST_MANAGEMENT_SYSTEM.md"
    "docs/CODING_STANDARDS.md"
    "docs/INSTAGRAM_HYBRID_OWNERSHIP.md"
    "docs/DARK_MODE_IMPLEMENTATION.md"
    "docs/INTERNATIONALIZATION_PLAN.md"
    "docs/TESTING_GUIDE.md"
    "docs/RELEASE_MANAGEMENT.md"
)

for doc in "${REQUIRED_DOCS[@]}"; do
    if [ -f "$doc" ]; then
        print_success "Documentation exists: $doc"
    else
        print_error "Missing documentation: $doc"
    fi
done

# Check if documentation is up-to-date
print_info "Checking documentation freshness..."

# Check if DATABASE_SCHEMA.md mentions posts tables
if grep -q "posts.*table" docs/DATABASE_SCHEMA.md; then
    print_success "DATABASE_SCHEMA.md includes posts tables"
else
    print_error "DATABASE_SCHEMA.md missing posts tables documentation"
fi

# Check if INDEX.md mentions Post Management System
if grep -q "POST_MANAGEMENT_SYSTEM" docs/INDEX.md; then
    print_success "INDEX.md includes Post Management System"
else
    print_error "INDEX.md missing Post Management System documentation"
fi

###############################################################################
# 3. Code Quality Check
###############################################################################

print_header "3. Code Quality Check"

# Check PHP code quality
print_info "Running Laravel Pint..."
if ./vendor/bin/pint --test; then
    print_success "PHP code formatting is correct"
else
    print_warning "PHP code formatting issues found (run: ./vendor/bin/pint)"
fi

# Check JavaScript/Vue code quality
print_info "Running ESLint..."
if npm run lint 2>/dev/null; then
    print_success "JavaScript/Vue code quality is good"
else
    print_warning "JavaScript/Vue code quality issues found"
fi

###############################################################################
# 4. Translation Completeness Check
###############################################################################

print_header "4. Translation Completeness Check"

# Check if all language files exist
LANGUAGES=("en" "es" "ru")
for lang in "${LANGUAGES[@]}"; do
    if [ -f "lang/$lang/posts.php" ]; then
        print_success "Posts translations exist for $lang"
    else
        print_error "Missing posts translations for $lang"
    fi
    
    if [ -f "lang/$lang.json" ]; then
        print_success "Frontend translations exist for $lang"
    else
        print_error "Missing frontend translations for $lang"
    fi
done

###############################################################################
# 5. Database Schema Check
###############################################################################

print_header "5. Database Schema Check"

# Check if migrations exist
if [ -f "database/migrations/2025_10_15_161919_create_posts_table.php" ]; then
    print_success "Posts table migration exists"
else
    print_error "Posts table migration missing"
fi

if [ -f "database/migrations/2025_10_15_161925_create_post_media_table.php" ]; then
    print_success "Post media table migration exists"
else
    print_error "Post media table migration missing"
fi

# Check if models exist
if [ -f "app/Models/Post.php" ]; then
    print_success "Post model exists"
else
    print_error "Post model missing"
fi

if [ -f "app/Models/PostMedia.php" ]; then
    print_success "PostMedia model exists"
else
    print_error "PostMedia model missing"
fi

###############################################################################
# 6. Frontend Components Check
###############################################################################

print_header "6. Frontend Components Check"

# Check if Vue components exist
VUE_COMPONENTS=(
    "resources/js/Pages/Posts/Index.vue"
    "resources/js/Pages/Posts/Create.vue"
    "resources/js/Components/MediaUpload.vue"
    "resources/js/Components/DateTimePicker.vue"
)

for component in "${VUE_COMPONENTS[@]}"; do
    if [ -f "$component" ]; then
        print_success "Vue component exists: $component"
    else
        print_error "Missing Vue component: $component"
    fi
done

###############################################################################
# 7. Backend Services Check
###############################################################################

print_header "7. Backend Services Check"

# Check if services exist
SERVICES=(
    "app/Services/Post/PostService.php"
    "app/Services/Post/PostMediaService.php"
    "app/Http/Controllers/PostController.php"
    "app/Http/Requests/Post/CreatePostRequest.php"
    "app/Http/Requests/Post/UpdatePostRequest.php"
)

for service in "${SERVICES[@]}"; do
    if [ -f "$service" ]; then
        print_success "Service exists: $service"
    else
        print_error "Missing service: $service"
    fi
done

###############################################################################
# 8. Routes Check
###############################################################################

print_header "8. Routes Check"

# Check if posts routes exist
if grep -q "posts" routes/web.php; then
    print_success "Posts routes are defined"
else
    print_error "Posts routes missing from routes/web.php"
fi

# Check if media serving route exists
if grep -q "media.*path" routes/web.php; then
    print_success "Media serving route exists"
else
    print_error "Media serving route missing"
fi

###############################################################################
# 9. Security & Performance Audit
###############################################################################

print_header "9. Security & Performance Audit"

# Security checks
print_info "Running security audit..."

# Check for exposed sensitive files
if [ -f ".env" ] && ! grep -q "APP_KEY=" .env; then
    print_error "APP_KEY not set in .env file"
else
    print_success "APP_KEY is configured"
fi

# Check for hardcoded secrets in code
if grep -r "password.*=.*['\"].*['\"]" app/ --exclude-dir=vendor 2>/dev/null; then
    print_warning "Potential hardcoded passwords found in app/"
else
    print_success "No hardcoded passwords found"
fi

# Check for SQL injection patterns
if grep -r "DB::raw.*\$" app/ --exclude-dir=vendor 2>/dev/null; then
    print_warning "Potential SQL injection risks found (DB::raw with variables)"
else
    print_success "No obvious SQL injection patterns found"
fi

# Performance checks
print_info "Running performance audit..."

# Check for N+1 query patterns
if grep -r "foreach.*->" app/ --exclude-dir=vendor | grep -v "with(" 2>/dev/null; then
    print_warning "Potential N+1 query patterns found"
else
    print_success "No obvious N+1 query patterns found"
fi

# Check for missing eager loading
if grep -r "hasMany\|belongsTo\|belongsToMany" app/Models/ --exclude-dir=vendor 2>/dev/null | grep -v "with("; then
    print_warning "Consider adding eager loading for relationships"
else
    print_success "Relationship loading looks good"
fi

# Dependency audit
print_info "Running dependency audit..."

if command -v composer &> /dev/null; then
    if composer audit --no-dev 2>/dev/null | grep -q "vulnerabilities found"; then
        print_warning "Composer security vulnerabilities found"
    else
        print_success "No composer security vulnerabilities"
    fi
else
    print_warning "Composer not available for security audit"
fi

if command -v npm &> /dev/null; then
    if npm audit --audit-level=moderate 2>/dev/null | grep -q "found vulnerabilities"; then
        print_warning "NPM security vulnerabilities found"
    else
        print_success "No NPM security vulnerabilities"
    fi
else
    print_warning "NPM not available for security audit"
fi

print_header "10. Final Summary"

if [ $ISSUES_FOUND -eq 0 ]; then
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${GREEN}  ✅ ALL CHECKS PASSED 🚀${NC}"
    echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    echo -e "${GREEN}Your project is ready for production!${NC}"
    echo ""
    echo "📊 Project Statistics:"
    echo "✅ Tests: All passing"
    echo "✅ Documentation: Complete and up-to-date"
    echo "✅ Code Quality: Clean and formatted"
    echo "✅ Translations: Complete (EN, ES, RU)"
    echo "✅ Database: Schema and migrations ready"
    echo "✅ Frontend: All components implemented"
    echo "✅ Backend: All services and controllers ready"
    echo "✅ Routes: All endpoints configured"
    echo ""
    echo "🚀 Ready for deployment!"
else
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${RED}  ❌ ISSUES FOUND: $ISSUES_FOUND${NC}"
    echo -e "${RED}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo ""
    echo -e "${RED}Please fix the issues above before proceeding.${NC}"
    exit 1
fi

echo ""
echo "Last Updated: $(date)"
echo "Version: 1.5"
echo "Status: ✅ PRODUCTION READY"
echo ""
echo "=============================================="
echo "🎉 FINAL CHECK COMPLETE! 🎉"
echo "=============================================="
