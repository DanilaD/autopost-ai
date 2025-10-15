#!/bin/bash
set -euo pipefail

echo "🔧 Fixing all remaining pattern classes..."

fix_file() {
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
  
  sed -i '' -E 's/\bborder-pattern-neutral-300\b/border-md-outline/g' "$f"
  sed -i '' -E 's/\bplaceholder-pattern-neutral-500\b/placeholder-md-on-surface-variant/g' "$f"
  
  sed -i '' -E 's/\bfrom-pattern-neutral-100\b/from-md-surface-container-high/g' "$f"
  sed -i '' -E 's/\bvia-pattern-neutral-200\b/via-md-surface-container/g' "$f"
  sed -i '' -E 's/\bto-pattern-primary-light\b/to-md-primary-container/g' "$f"
  
  sed -i '' -E 's/\bfocus:ring-pattern-primary\b/focus:ring-md-primary/g' "$f"
  sed -i '' -E 's/\btext-pattern-primary\b/text-md-primary/g' "$f"
  sed -i '' -E 's/\bhover:text-pattern-primary-light\b/hover:text-md-primary-container/g' "$f"
  
  sed -i '' -E 's/\bbg-pattern-success-container\b/bg-md-success-container/g' "$f"
  sed -i '' -E 's/\btext-pattern-on-success-container\b/text-md-on-success-container/g' "$f"
  
  sed -i '' -E 's/\btext-pattern-error\b/text-md-error/g' "$f"
}

export -f fix_file

find resources/js -type f -name "*.vue" -exec bash -c 'fix_file "$0"' {} \;

echo "✅ All pattern classes fixed."
