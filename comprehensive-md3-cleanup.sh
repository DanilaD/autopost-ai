#!/bin/bash
set -euo pipefail

echo "🔧 Comprehensive MD3 cleanup starting..."

fix_file() {
  local f="$1"
  # Remove any remaining dark: utilities (MD3 tokens handle themes)
  sed -i '' -E 's/\sdark:[^\s"]+//g' "$f"

  # Replace legacy surface/text colors
  sed -i '' -E 's/\bbg-white\b/bg-md-surface-container/g' "$f"
  sed -i '' -E 's/\bbg-gray-50\b/bg-md-surface-container-high/g' "$f"
  sed -i '' -E 's/\bbg-gray-1(00|50)\b/bg-md-surface-container/g' "$f"
  sed -i '' -E 's/\bbg-gray-([789]00)\b/bg-md-surface-container/g' "$f"
  sed -i '' -E 's/\btext-gray-900\b/text-md-on-surface/g' "$f"
  sed -i '' -E 's/\btext-gray-(800|700)\b/text-md-on-surface/g' "$f"
  sed -i '' -E 's/\btext-gray-(600|500|400)\b/text-md-on-surface-variant/g' "$f"

  # Borders/dividers
  sed -i '' -E 's/\bborder-gray-200\b/border-md-outline-variant/g' "$f"
  sed -i '' -E 's/\bborder-gray-300\b/border-md-outline/g' "$f"
  sed -i '' -E 's/\bdivide-gray-200\b/divide-md-outline-variant/g' "$f"

  # Primary/secondary/tertiary/status colors
  sed -i '' -E 's/\btext-blue-600\b/text-md-primary/g' "$f"
  sed -i '' -E 's/\btext-blue-800\b/text-md-on-primary-container/g' "$f"
  sed -i '' -E 's/\bbg-blue-100\b/bg-md-primary-container/g' "$f"
  sed -i '' -E 's/\bborder-blue-200\b/border-md-primary/g' "$f"

  sed -i '' -E 's/\btext-purple-600\b/text-md-secondary/g' "$f"
  sed -i '' -E 's/\btext-purple-800\b/text-md-on-secondary-container/g' "$f"
  sed -i '' -E 's/\bbg-purple-100\b/bg-md-secondary-container/g' "$f"

  sed -i '' -E 's/\btext-pink-600\b/text-md-tertiary/g' "$f"
  sed -i '' -E 's/\bbg-pink-100\b/bg-md-tertiary-container/g' "$f"

  sed -i '' -E 's/\btext-green-600\b/text-md-success/g' "$f"
  sed -i '' -E 's/\bbg-green-100\b/bg-md-success-container/g' "$f"
  sed -i '' -E 's/\bborder-green-200\b/border-md-success/g' "$f"

  sed -i '' -E 's/\btext-red-600\b/text-md-error/g' "$f"
  sed -i '' -E 's/\bborder-red-(200|300|700)\b/border-md-error/g' "$f"
  sed -i '' -E 's/\bbg-red-100\b/bg-md-error-container/g' "$f"

  sed -i '' -E 's/\btext-yellow-(400|500|700)\b/text-md-warning/g' "$f"
  sed -i '' -E 's/\bbg-yellow-50\b/bg-md-warning-container/g' "$f"
  sed -i '' -E 's/\bborder-yellow-(400|500|800|200)\b/border-md-warning/g' "$f"

  # Focus rings
  sed -i '' -E 's/\bfocus:ring-indigo-(500|600)\b/focus:ring-md-primary/g' "$f"
  sed -i '' -E 's/\bfocus:ring-offset-gray-900\b/focus:ring-offset-md-background/g' "$f"

  # Hovers
  sed -i '' -E 's/\bhover:bg-gray-50\b/hover:bg-md-surface-container-high/g' "$f"
  sed -i '' -E 's/\bhover:bg-gray-100\b/hover:bg-md-surface-container/g' "$f"
  sed -i '' -E 's/\bhover:text-gray-700\b/hover:text-md-on-surface-variant/g' "$f"

  # Tables
  sed -i '' -E 's/\bbg-gray-50\b/bg-md-surface-container-high/g' "$f"
  sed -i '' -E 's/\btext-gray-700\b/text-md-on-surface/g' "$f"
}

export -f fix_file

find resources/js -type f -name "*.vue" -exec bash -c 'fix_file "$0"' {} \;

echo "✅ Cleanup done."
