#!/bin/bash

###############################################################################
# Pre-Push Quality & Final Check Script
#
# This script runs before every push to ensure:
# 1. Pre-commit checks pass (code quality, documentation, translations)
# 2. Final project validation passes (complete project check)
# 3. All tests pass
# 4. Project is production-ready
#
# Usage: Automatically via git pre-push hook
# Manual: ./scripts/pre-push-check.sh
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

echo "🚀 AUTOPOST AI - PRE-PUSH VALIDATION"
echo "===================================="
echo "Date: $(date)"
echo ""

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
}

print_warning() {
    echo -e "${YELLOW}${WARNING} $1${NC}"
}

print_info() {
    echo -e "${BLUE}${INFO} $1${NC}"
}

###############################################################################
# 1. Pre-Commit Check
###############################################################################

print_header "1. Running Pre-Commit Quality Checks"

print_info "Running pre-commit validation..."
if ./scripts/pre-commit-check.sh; then
    print_success "Pre-commit checks passed!"
else
    print_error "Pre-commit checks failed!"
    print_warning "Please fix the issues above before pushing."
    exit 1
fi

###############################################################################
# 2. Final Project Check
###############################################################################

print_header "2. Running Final Project Validation"

print_info "Running comprehensive project validation..."
if ./scripts/final-check.sh; then
    print_success "Final project validation passed!"
else
    print_error "Final project validation failed!"
    print_warning "Please fix the issues above before pushing."
    exit 1
fi

###############################################################################
# 3. Final Summary
###############################################################################

print_header "3. Pre-Push Summary"

echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}  ✅ ALL PRE-PUSH CHECKS PASSED 🚀${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${GREEN}Your code is ready to be pushed!${NC}"
echo ""
echo "📊 Validation Results:"
echo "✅ Pre-commit checks: Passed"
echo "✅ Final project validation: Passed"
echo "✅ Code quality: Verified"
echo "✅ Documentation: Up-to-date"
echo "✅ Translations: Complete"
echo "✅ Tests: All passing"
echo "✅ Architecture: Validated"
echo ""
echo "🚀 Safe to push to remote repository!"

echo ""
echo "Last Updated: $(date)"
echo "Status: ✅ READY FOR PUSH"
echo ""
echo "=============================================="
echo "🎉 PRE-PUSH VALIDATION COMPLETE! 🎉"
echo "=============================================="
