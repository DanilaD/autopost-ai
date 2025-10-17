#!/bin/bash

###############################################################################
# AI Validation Example Script
#
# This script demonstrates how to use AI for validating documentation and tests
# before committing code changes.
#
# Usage: ./scripts/ai-validation-example.sh
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

print_info() {
    echo -e "${BLUE}${INFO} $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}${WARNING} $1${NC}"
}

echo "🤖 AI VALIDATION EXAMPLE"
echo "========================"
echo "This script shows how to use AI for validating documentation and tests"
echo ""

print_header "1. Documentation Validation Commands"

echo "Use these commands to validate documentation updates:"
echo ""

echo -e "${GREEN}# Basic documentation validation:${NC}"
echo "\"Please review my documentation updates for completeness and accuracy."
echo "I changed [describe your changes]. Are there any missing pieces or inaccuracies?\""
echo ""

echo -e "${GREEN}# Comprehensive validation:${NC}"
echo "\"Please validate both my code changes and documentation updates."
echo "I modified [describe changes]. Are the docs complete and tests comprehensive?\""
echo ""

print_header "2. Test Validation Commands"

echo "Use these commands to validate test updates:"
echo ""

echo -e "${GREEN}# Test coverage validation:${NC}"
echo "\"Please review my test updates. I modified [describe changes]."
echo "Do my tests adequately cover the changes and are they accurate?\""
echo ""

echo -e "${GREEN}# PHPUnit modernization validation:${NC}"
echo "\"Please check if my test files use modern PHPUnit syntax."
echo "I converted /** @test */ annotations to PHP 8 attributes."
echo "Are all test methods properly updated and PHPUnit 12 compatible?\""
echo ""

print_header "3. Architecture Compliance Validation"

echo "Use these commands to validate architecture compliance:"
echo ""

echo -e "${GREEN}# Architecture validation:${NC}"
echo "\"Please review my code changes for architecture compliance."
echo "I modified [describe changes]. Does the code follow the Controller → Service → Repository pattern?\""
echo ""

print_header "4. AI Validation Checklist"

echo "Before committing, ask AI to verify:"
echo ""

echo -e "${GREEN}□ Documentation completeness - All changes documented?${NC}"
echo -e "${GREEN}□ Documentation accuracy - Technical details correct?${NC}"
echo -e "${GREEN}□ Test coverage - New functionality tested?${NC}"
echo -e "${GREEN}□ Test accuracy - Tests match actual behavior?${NC}"
echo -e "${GREEN}□ Format compliance - Proper Markdown/test structure?${NC}"
echo -e "${GREEN}□ Examples updated - Code examples current?${NC}"
echo -e "${GREEN}□ Breaking changes documented - If any?${NC}"
echo -e "${GREEN}□ Version numbers updated - In relevant docs?${NC}"
echo -e "${GREEN}□ PHPUnit modernization - Tests use PHP 8 attributes?${NC}"
echo -e "${GREEN}□ Architecture compliance - Controller → Service → Repository pattern?${NC}"
echo -e "${GREEN}□ No deprecation warnings - Clean test output?${NC}"
echo ""

print_header "5. Example Workflow"

echo "Here's how to use AI validation in your workflow:"
echo ""

echo -e "${YELLOW}1. Make your code changes${NC}"
echo -e "${YELLOW}2. Update documentation${NC}"
echo -e "${YELLOW}3. Update tests${NC}"
echo -e "${YELLOW}4. Run AI validation commands${NC}"
echo -e "${YELLOW}5. Fix any issues AI identifies${NC}"
echo -e "${YELLOW}6. Commit everything together${NC}"
echo ""

print_header "6. Benefits of AI Validation"

echo -e "${GREEN}🎯 Catch missing pieces - AI spots gaps in documentation${NC}"
echo -e "${GREEN}🔍 Verify accuracy - AI checks technical correctness${NC}"
echo -e "${GREEN}📊 Ensure completeness - AI validates comprehensive coverage${NC}"
echo -e "${GREEN}⚡ Save time - Catch issues before code review${NC}"
echo -e "${GREEN}🛡️ Prevent regressions - AI validates test coverage${NC}"
echo -e "${GREEN}📚 Maintain quality - Consistent documentation standards${NC}"
echo ""

print_header "7. Integration with Git Hooks"

echo "AI validation is integrated into our development workflow:"
echo ""

echo -e "${GREEN}✅ Pre-commit hooks check for documentation updates${NC}"
echo -e "${GREEN}✅ Pre-push hooks validate test coverage${NC}"
echo -e "${GREEN}✅ Final-check.sh includes AI validation checks${NC}"
echo -e "${GREEN}✅ CODING_STANDARDS.md mandates AI validation${NC}"
echo ""

print_success "AI Validation Example Complete!"
echo ""
echo "Remember: Always validate your changes with AI before committing!"
echo ""
echo "For more details, see: docs/CODING_STANDARDS.md"
echo "Last Updated: $(date)"
echo "Version: 1.0"
