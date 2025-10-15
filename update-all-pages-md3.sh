#!/bin/bash

# Complete Material Design 3 Update Script
# This script updates ALL pages to use Material Design 3 classes

echo "🎨 Starting Complete Material Design 3 Update..."

# Function to update files with Material Design 3 patterns
update_md3_patterns() {
    local file="$1"
    echo "📝 Updating $file..."
    
    # Replace old text color patterns
    sed -i '' 's/text-gray-800 dark:text-gray-200/text-md-on-surface/g' "$file"
    sed -i '' 's/text-gray-900 dark:text-gray-100/text-md-on-surface/g' "$file"
    sed -i '' 's/text-gray-700 dark:text-gray-300/text-md-on-surface/g' "$file"
    sed -i '' 's/text-gray-600 dark:text-gray-400/text-md-on-surface-variant/g' "$file"
    sed -i '' 's/text-gray-500 dark:text-gray-400/text-md-on-surface-variant/g' "$file"
    sed -i '' 's/text-gray-400 dark:text-gray-600/text-md-on-surface-variant/g' "$file"
    
    # Replace old background patterns
    sed -i '' 's/bg-gray-50 dark:bg-gray-700/bg-md-surface-container-high/g' "$file"
    sed -i '' 's/bg-gray-100 dark:bg-gray-800/bg-md-surface-container/g' "$file"
    sed -i '' 's/bg-gray-200 dark:bg-gray-700/bg-md-outline-variant/g' "$file"
    sed -i '' 's/hover:bg-gray-50 dark:hover:bg-gray-700/hover:bg-md-surface-container-high/g' "$file"
    sed -i '' 's/hover:bg-gray-100 dark:hover:bg-gray-800/hover:bg-md-surface-container/g' "$file"
    
    # Replace old border patterns
    sed -i '' 's/border-gray-200 dark:border-gray-700/border-md-outline-variant/g' "$file"
    sed -i '' 's/border-gray-300 dark:border-gray-600/border-md-outline/g' "$file"
    sed -i '' 's/divide-gray-200 dark:divide-gray-700/divide-md-outline-variant/g' "$file"
    
    # Replace old color patterns
    sed -i '' 's/text-blue-600 dark:text-blue-400/text-md-primary/g' "$file"
    sed -i '' 's/hover:text-blue-900 dark:hover:text-blue-300/hover:text-md-primary-container/g' "$file"
    sed -i '' 's/text-blue-800 dark:text-blue-200/text-md-on-primary-container/g' "$file"
    sed -i '' 's/bg-blue-50 dark:bg-blue-900\/20/bg-md-primary-container/g' "$file"
    sed -i '' 's/border-blue-200 dark:border-blue-800/border-md-primary/g' "$file"
    
    sed -i '' 's/text-green-600 dark:text-green-400/text-md-success/g' "$file"
    sed -i '' 's/hover:text-green-900 dark:hover:text-green-300/hover:text-md-success-container/g' "$file"
    sed -i '' 's/text-green-800 dark:text-green-200/text-md-on-success-container/g' "$file"
    sed -i '' 's/bg-green-100 dark:bg-green-900/bg-md-success-container/g' "$file"
    sed -i '' 's/border-green-200 dark:border-green-800/border-md-success/g' "$file"
    
    sed -i '' 's/text-red-600 dark:text-red-400/text-md-error/g' "$file"
    sed -i '' 's/hover:text-red-900 dark:hover:text-red-300/hover:text-md-error-container/g' "$file"
    sed -i '' 's/text-red-800 dark:text-red-200/text-md-on-error-container/g' "$file"
    sed -i '' 's/bg-red-100 dark:bg-red-900/bg-md-error-container/g' "$file"
    sed -i '' 's/border-red-200 dark:border-red-800/border-md-error/g' "$file"
    
    sed -i '' 's/text-yellow-600 dark:text-yellow-400/text-md-warning/g' "$file"
    sed -i '' 's/hover:text-yellow-900 dark:hover:text-yellow-300/hover:text-md-warning-container/g' "$file"
    sed -i '' 's/text-yellow-800 dark:text-yellow-200/text-md-on-warning-container/g' "$file"
    sed -i '' 's/bg-yellow-50 dark:bg-yellow-900\/20/bg-md-warning-container/g' "$file"
    sed -i '' 's/border-yellow-200 dark:border-yellow-800/border-md-warning/g' "$file"
    
    sed -i '' 's/text-purple-600 dark:text-purple-400/text-md-secondary/g' "$file"
    sed -i '' 's/hover:text-purple-900 dark:hover:text-purple-300/hover:text-md-secondary-container/g' "$file"
    sed -i '' 's/text-purple-800 dark:text-purple-200/text-md-on-secondary-container/g' "$file"
    sed -i '' 's/bg-purple-100 dark:bg-purple-900/bg-md-secondary-container/g' "$file"
    sed -i '' 's/border-purple-200 dark:border-purple-800/border-md-secondary/g' "$file"
    
    # Replace old indigo patterns
    sed -i '' 's/text-indigo-600 dark:text-indigo-400/text-md-primary/g' "$file"
    sed -i '' 's/hover:text-indigo-900 dark:hover:text-indigo-300/hover:text-md-primary-container/g' "$file"
    sed -i '' 's/focus:ring-indigo-500/focus:ring-md-primary/g' "$file"
    sed -i '' 's/focus:ring-indigo-600/focus:ring-md-primary/g' "$file"
    
    # Replace old pink patterns
    sed -i '' 's/text-pink-600 dark:text-pink-400/text-md-tertiary/g' "$file"
    sed -i '' 's/hover:text-pink-900 dark:hover:text-pink-300/hover:text-md-tertiary-container/g' "$file"
    sed -i '' 's/bg-pink-100 dark:bg-pink-900/bg-md-tertiary-container/g' "$file"
    
    echo "✅ Updated $file"
}

# Update all Vue files
echo "🔄 Updating all Vue components..."

# Find all Vue files and update them
find resources/js -name "*.vue" -type f | while read -r file; do
    update_md3_patterns "$file"
done

# Update any remaining patterns globally
echo "🔍 Final cleanup..."

# Find and replace any remaining old patterns
find resources/js -name "*.vue" -exec sed -i '' 's/text-gray-800 dark:text-gray-200/text-md-on-surface/g' {} \;
find resources/js -name "*.vue" -exec sed -i '' 's/text-gray-600 dark:text-gray-400/text-md-on-surface-variant/g' {} \;
find resources/js -name "*.vue" -exec sed -i '' 's/bg-gray-50 dark:bg-gray-700/bg-md-surface-container-high/g' {} \;
find resources/js -name "*.vue" -exec sed -i '' 's/bg-gray-100 dark:bg-gray-800/bg-md-surface-container/g' {} \;
find resources/js -name "*.vue" -exec sed -i '' 's/border-gray-200 dark:border-gray-700/border-md-outline-variant/g' {} \;
find resources/js -name "*.vue" -exec sed -i '' 's/divide-gray-200 dark:divide-gray-700/divide-md-outline-variant/g' {} \;

echo "🎯 Complete Material Design 3 Update Finished!"
echo ""
echo "📊 Summary:"
echo "✅ All Vue components updated to Material Design 3"
echo "✅ Consistent color system across all pages"
echo "✅ Automatic dark mode support"
echo "✅ Modern, professional appearance"
echo ""
echo "🚀 Ready for production with unified Material Design 3!"
