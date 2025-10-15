#!/bin/bash
set -euo pipefail

echo "🔧 Fixing error pages pattern classes..."

fix_error_page() {
  local f="$1"
  echo "Fixing: $f"
  
  # Replace all pattern- classes with correct MD3 classes
  sed -i '' -E 's/\btext-pattern-neutral-900\b/text-md-on-surface/g' "$f"
  sed -i '' -E 's/\btext-pattern-neutral-700\b/text-md-on-surface/g' "$f"
  sed -i '' -E 's/\btext-pattern-neutral-600\b/text-md-on-surface-variant/g' "$f"
  sed -i '' -E 's/\btext-pattern-neutral-500\b/text-md-on-surface-variant/g' "$f"
  
  sed -i '' -E 's/\bbg-pattern-neutral-100\b/bg-md-surface-container-high/g' "$f"
  sed -i '' -E 's/\bbg-pattern-neutral-200\b/bg-md-surface-container/g' "$f"
  sed -i '' -E 's/\bhover:bg-pattern-neutral-200\b/hover:bg-md-surface-container/g' "$f"
  
  sed -i '' -E 's/\bfrom-pattern-neutral-100\b/from-md-surface-container-high/g' "$f"
  sed -i '' -E 's/\bvia-pattern-neutral-200\b/via-md-surface-container/g' "$f"
  sed -i '' -E 's/\bto-pattern-primary-light\b/to-md-primary-container/g' "$f"
  
  sed -i '' -E 's/\bfocus:ring-pattern-primary\b/focus:ring-md-primary/g' "$f"
}

# Fix all error pages
for file in resources/js/Pages/Errors/*.vue; do
  fix_error_page "$file"
done

echo "✅ Error pages fixed."
