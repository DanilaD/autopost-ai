#!/bin/bash

# Helper script to restore local Valet configuration
# Usage: ./restore-local-config.sh

echo "🔧 Restoring Local Valet Configuration"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ Error: .env file not found!"
    exit 1
fi

# Backup current .env
cp .env .env.ngrok.backup
echo "✅ Backed up current .env to .env.ngrok.backup"

# Restore local URLs
LOCAL_URL="https://autopost-ai.test"
CALLBACK_URL="$LOCAL_URL/instagram/callback"

# Update APP_URL
if grep -q "^APP_URL=" .env; then
    sed -i '' "s|^APP_URL=.*|APP_URL=$LOCAL_URL|" .env
    echo "✅ Restored APP_URL to: $LOCAL_URL"
else
    echo "APP_URL=$LOCAL_URL" >> .env
    echo "✅ Added APP_URL: $LOCAL_URL"
fi

# Update INSTAGRAM_REDIRECT_URI
if grep -q "^INSTAGRAM_REDIRECT_URI=" .env; then
    sed -i '' "s|^INSTAGRAM_REDIRECT_URI=.*|INSTAGRAM_REDIRECT_URI=$CALLBACK_URL|" .env
    echo "✅ Restored INSTAGRAM_REDIRECT_URI to: $CALLBACK_URL"
else
    echo "INSTAGRAM_REDIRECT_URI=$CALLBACK_URL" >> .env
    echo "✅ Added INSTAGRAM_REDIRECT_URI: $CALLBACK_URL"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Local Configuration Restored!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📋 Next Steps:"
echo ""
echo "1. Clear Laravel config cache:"
echo "   php artisan config:clear"
echo ""
echo "2. Access your app locally at:"
echo "   $LOCAL_URL"
echo ""
echo "3. Stop valet share if running:"
echo "   Press Ctrl+C in the terminal running valet share"
echo ""

