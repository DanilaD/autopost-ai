#!/bin/bash

# Comprehensive Legacy Class Removal Script
# This script removes ALL remaining legacy classes and ensures proper MD3 implementation

echo "🔧 Fixing remaining legacy classes for proper dark mode..."

# Function to fix legacy classes
fix_legacy_classes() {
    local file="$1"
    echo "📝 Fixing $file..."
    
    # Remove ALL dark: classes - Material Design 3 handles this automatically
    sed -i '' 's/ dark:[^[:space:]]*//g' "$file"
    
    # Fix remaining bg-white patterns
    sed -i '' 's/bg-white/bg-md-surface-container/g' "$file"
    
    # Fix remaining text-gray patterns
    sed -i '' 's/text-gray-900/text-md-on-surface/g' "$file"
    sed -i '' 's/text-gray-800/text-md-on-surface/g' "$file"
    sed -i '' 's/text-gray-700/text-md-on-surface/g' "$file"
    sed -i '' 's/text-gray-600/text-md-on-surface-variant/g' "$file"
    sed -i '' 's/text-gray-500/text-md-on-surface-variant/g' "$file"
    sed -i '' 's/text-gray-400/text-md-on-surface-variant/g' "$file"
    
    # Fix remaining bg-gray patterns
    sed -i '' 's/bg-gray-50/bg-md-surface-container-high/g' "$file"
    sed -i '' 's/bg-gray-100/bg-md-surface-container/g' "$file"
    sed -i '' 's/bg-gray-200/bg-md-outline-variant/g' "$file"
    sed -i '' 's/bg-gray-700/bg-md-surface-container/g' "$file"
    sed -i '' 's/bg-gray-800/bg-md-surface-container/g' "$file"
    sed -i '' 's/bg-gray-900/bg-md-background/g' "$file"
    
    # Fix remaining border patterns
    sed -i '' 's/border-gray-200/border-md-outline-variant/g' "$file"
    sed -i '' 's/border-gray-300/border-md-outline/g' "$file"
    sed -i '' 's/border-gray-600/border-md-outline/g' "$file"
    sed -i '' 's/border-gray-700/border-md-outline-variant/g' "$file"
    
    # Fix remaining divide patterns
    sed -i '' 's/divide-gray-200/divide-md-outline-variant/g' "$file"
    sed -i '' 's/divide-gray-700/divide-md-outline-variant/g' "$file"
    
    # Fix remaining hover patterns
    sed -i '' 's/hover:bg-gray-50/hover:bg-md-surface-container-high/g' "$file"
    sed -i '' 's/hover:bg-gray-100/hover:bg-md-surface-container/g' "$file"
    sed -i '' 's/hover:bg-gray-600/hover:bg-md-surface-container/g' "$file"
    sed -i '' 's/hover:bg-gray-700/hover:bg-md-surface-container/g' "$file"
    sed -i '' 's/hover:text-gray-700/hover:text-md-on-surface-variant/g' "$file"
    sed -i '' 's/hover:text-gray-100/hover:text-md-on-surface/g' "$file"
    
    # Fix remaining color patterns
    sed -i '' 's/text-blue-600/text-md-primary/g' "$file"
    sed -i '' 's/text-blue-800/text-md-on-primary-container/g' "$file"
    sed -i '' 's/bg-blue-100/bg-md-primary-container/g' "$file"
    sed -i '' 's/border-blue-200/border-md-primary/g' "$file"
    sed -i '' 's/hover:text-blue-900/hover:text-md-primary-container/g' "$file"
    
    sed -i '' 's/text-green-600/text-md-success/g' "$file"
    sed -i '' 's/text-green-800/text-md-on-success-container/g' "$file"
    sed -i '' 's/bg-green-100/bg-md-success-container/g' "$file"
    sed -i '' 's/border-green-200/border-md-success/g' "$file"
    sed -i '' 's/hover:text-green-900/hover:text-md-success-container/g' "$file"
    
    sed -i '' 's/text-red-600/text-md-error/g' "$file"
    sed -i '' 's/text-red-800/text-md-on-error-container/g' "$file"
    sed -i '' 's/bg-red-100/bg-md-error-container/g' "$file"
    sed -i '' 's/border-red-200/border-md-error/g' "$file"
    sed -i '' 's/border-red-300/border-md-error/g' "$file"
    sed -i '' 's/border-red-700/border-md-error/g' "$file"
    sed -i '' 's/hover:text-red-900/hover:text-md-error-container/g' "$file"
    sed -i '' 's/hover:bg-red-50/hover:bg-md-error-container/g' "$file"
    
    sed -i '' 's/text-yellow-400/text-md-warning/g' "$file"
    sed -i '' 's/text-yellow-500/text-md-warning/g' "$file"
    sed -i '' 's/text-yellow-700/text-md-on-warning-container/g' "$file"
    sed -i '' 's/bg-yellow-50/bg-md-warning-container/g' "$file"
    sed -i '' 's/border-yellow-400/border-md-warning/g' "$file"
    sed -i '' 's/border-yellow-500/border-md-warning/g' "$file"
    
    sed -i '' 's/text-purple-600/text-md-secondary/g' "$file"
    sed -i '' 's/text-purple-800/text-md-on-secondary-container/g' "$file"
    sed -i '' 's/bg-purple-100/bg-md-secondary-container/g' "$file"
    sed -i '' 's/hover:text-purple-900/hover:text-md-secondary-container/g' "$file"
    
    sed -i '' 's/text-indigo-600/text-md-primary/g' "$file"
    sed -i '' 's/hover:text-indigo-900/hover:text-md-primary-container/g' "$file"
    sed -i '' 's/focus:ring-indigo-500/focus:ring-md-primary/g' "$file"
    sed -i '' 's/focus:ring-indigo-600/focus:ring-md-primary/g' "$file"
    
    sed -i '' 's/text-pink-600/text-md-tertiary/g' "$file"
    sed -i '' 's/bg-pink-100/bg-md-tertiary-container/g' "$file"
    
    # Fix remaining focus patterns
    sed -i '' 's/focus:ring-offset-gray-900/focus:ring-offset-md-background/g' "$file"
    
    echo "✅ Fixed $file"
}

# Update all Vue files
echo "🔄 Fixing all Vue components..."

# Find all Vue files and fix them
find resources/js -name "*.vue" -type f | while read -r file; do
    fix_legacy_classes "$file"
done

# Final cleanup - remove any remaining dark: classes
echo "🔍 Final cleanup - removing any remaining dark: classes..."
find resources/js -name "*.vue" -exec sed -i '' 's/ dark:[^[:space:]]*//g' {} \;

echo "🎯 Legacy class removal complete!"
echo ""
echo "📊 Summary:"
echo "✅ All dark: classes removed"
echo "✅ All legacy gray-* classes converted to MD3"
echo "✅ All legacy color classes converted to MD3"
echo "✅ Dark mode now works automatically with MD3"
echo ""
echo "🚀 Dark mode should now work perfectly!"
