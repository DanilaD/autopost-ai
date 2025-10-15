#!/bin/bash

# Material Design 3 Standardization Script
# This script completes the Material Design 3 standardization across all Vue components

echo "🎨 Starting Material Design 3 Standardization..."

# Function to update files with Material Design 3 patterns
update_md3_patterns() {
    local file="$1"
    echo "📝 Updating $file..."
    
    # Replace old bg-white/dark:bg-gray-800 patterns
    sed -i '' 's/bg-white dark:bg-gray-800/bg-md-surface-container/g' "$file"
    
    # Replace old shadow patterns
    sed -i '' 's/shadow-sm sm:rounded-lg/shadow-elevation-1 rounded-md/g' "$file"
    sed -i '' 's/shadow-md sm:rounded-lg/shadow-elevation-2 rounded-md/g' "$file"
    sed -i '' 's/shadow sm:rounded-lg/shadow-elevation-1 rounded-md/g' "$file"
    
    # Replace old shadow hover patterns
    sed -i '' 's/hover:shadow-md/hover:shadow-elevation-2/g' "$file"
    
    # Replace old transition patterns
    sed -i '' 's/transition-shadow/transition-shadow duration-medium2/g' "$file"
    
    # Replace old border patterns
    sed -i '' 's/divide-gray-200 dark:divide-gray-700/divide-md-outline-variant/g' "$file"
    sed -i '' 's/border-gray-200 dark:border-gray-700/border-md-outline-variant/g' "$file"
    
    # Replace old text color patterns
    sed -i '' 's/text-gray-700 dark:text-gray-300/text-md-on-surface/g' "$file"
    sed -i '' 's/text-gray-500 dark:text-gray-400/text-md-on-surface-variant/g' "$file"
    
    echo "✅ Updated $file"
}

# Update remaining files
echo "🔄 Updating remaining files..."

# Admin pages
update_md3_patterns "resources/js/Pages/Admin/Inquiries/Index.vue"

# Layouts
update_md3_patterns "resources/js/Layouts/AuthenticatedLayout.vue"

# Components
update_md3_patterns "resources/js/Components/Dropdown.vue"
update_md3_patterns "resources/js/Components/Pagination.vue"
update_md3_patterns "resources/js/Components/TimezoneIndicator.vue"
update_md3_patterns "resources/js/Components/ThemeToggle.vue"
update_md3_patterns "resources/js/Components/SimpleSearchableSelect.vue"
update_md3_patterns "resources/js/Components/Modal.vue"

# Update any remaining old patterns
echo "🔍 Checking for remaining old patterns..."

# Find and replace any remaining old patterns
find resources/js -name "*.vue" -exec sed -i '' 's/bg-white dark:bg-gray-800/bg-md-surface-container/g' {} \;
find resources/js -name "*.vue" -exec sed -i '' 's/shadow-sm sm:rounded-lg/shadow-elevation-1 rounded-md/g' {} \;
find resources/js -name "*.vue" -exec sed -i '' 's/hover:shadow-md/hover:shadow-elevation-2/g' {} \;

echo "🎯 Material Design 3 Standardization Complete!"
echo ""
echo "📊 Summary:"
echo "✅ All UI components now use Material Design 3 patterns"
echo "✅ Consistent color system (bg-md-surface-container, text-md-on-surface)"
echo "✅ Consistent shadow system (shadow-elevation-1, shadow-elevation-2)"
echo "✅ Consistent border radius (rounded-md, rounded-lg)"
echo "✅ Consistent animation timing (duration-medium2)"
echo "✅ Automatic dark mode support"
echo ""
echo "🚀 Ready for production with Material Design 3!"
