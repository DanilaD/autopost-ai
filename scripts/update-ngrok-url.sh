#!/bin/bash

# Helper script to update .env with ngrok URL
# Usage: ./update-ngrok-url.sh https://abc123.ngrok-free.app

echo "🔧 Instagram OAuth URL Updater"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Check if URL provided
if [ -z "$1" ]; then
    echo "❌ Error: Please provide your ngrok URL"
    echo ""
    echo "Usage:"
    echo "  ./update-ngrok-url.sh https://abc123.ngrok-free.app"
    echo ""
    echo "To get your ngrok URL:"
    echo "  1. Run: valet share"
    echo "  2. Copy the Forwarding URL"
    echo "  3. Run this script with that URL"
    echo ""
    exit 1
fi

NGROK_URL="$1"

# Remove trailing slash if present
NGROK_URL="${NGROK_URL%/}"

# Validate URL format
if [[ ! "$NGROK_URL" =~ ^https:// ]]; then
    echo "❌ Error: URL must start with https://"
    echo "Example: https://abc123.ngrok-free.app"
    exit 1
fi

echo "📝 Updating configuration with:"
echo "   Public URL: $NGROK_URL"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ Error: .env file not found!"
    echo "   Are you in the project directory?"
    exit 1
fi

# Backup .env
cp .env .env.backup
echo "✅ Backed up .env to .env.backup"

# Update APP_URL
if grep -q "^APP_URL=" .env; then
    sed -i '' "s|^APP_URL=.*|APP_URL=$NGROK_URL|" .env
    echo "✅ Updated APP_URL"
else
    echo "APP_URL=$NGROK_URL" >> .env
    echo "✅ Added APP_URL"
fi

# Update INSTAGRAM_REDIRECT_URI
CALLBACK_URL="$NGROK_URL/instagram/callback"
if grep -q "^INSTAGRAM_REDIRECT_URI=" .env; then
    sed -i '' "s|^INSTAGRAM_REDIRECT_URI=.*|INSTAGRAM_REDIRECT_URI=$CALLBACK_URL|" .env
    echo "✅ Updated INSTAGRAM_REDIRECT_URI"
else
    echo "INSTAGRAM_REDIRECT_URI=$CALLBACK_URL" >> .env
    echo "✅ Added INSTAGRAM_REDIRECT_URI"
fi

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ Configuration Updated!"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📋 Next Steps:"
echo ""
echo "1. Clear Laravel config cache:"
echo "   php artisan config:clear"
echo ""
echo "2. Add this URL to Facebook Developer Console:"
echo "   https://developers.facebook.com/apps/1539628577054406"
echo ""
echo "   Under 'Instagram Basic Display' → 'Valid OAuth Redirect URIs':"
echo "   $CALLBACK_URL"
echo ""
echo "3. Access your app at:"
echo "   $NGROK_URL"
echo ""
echo "4. To restore local config later:"
echo "   ./restore-local-config.sh"
echo ""

