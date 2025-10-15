#!/bin/bash

###############################################################################
# Install Git Hooks Script
#
# This script installs git hooks for quality checks
###############################################################################

echo "🔧 Installing Git Hooks..."

# Create .git/hooks directory if it doesn't exist
mkdir -p .git/hooks

# Create pre-commit hook
cat > .git/hooks/pre-commit << 'EOF'
#!/bin/bash

# Run the pre-commit check script
./scripts/pre-commit-check.sh

# Exit with the same code as the check script
exit $?
EOF

# Make the hook executable
chmod +x .git/hooks/pre-commit

echo "✅ Git hooks installed successfully!"
echo ""
echo "Pre-commit hook will now run automatically before every commit."
echo ""
echo "To run checks manually: ./scripts/pre-commit-check.sh"
echo "To skip checks once: git commit --no-verify"
echo ""

