#!/bin/bash

echo "🔍 Instagram Configuration Checker"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Read current values
CLIENT_ID=$(grep "^INSTAGRAM_CLIENT_ID=" .env | cut -d '=' -f2)
CLIENT_SECRET=$(grep "^INSTAGRAM_CLIENT_SECRET=" .env | cut -d '=' -f2)
REDIRECT_URI=$(grep "^INSTAGRAM_REDIRECT_URI=" .env | cut -d '=' -f2)

echo "📋 Current Configuration:"
echo ""
echo "Client ID:     $CLIENT_ID"
echo "Client Secret: ${CLIENT_SECRET:0:10}... (hidden)"
echo "Redirect URI:  $REDIRECT_URI"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Check if it looks like Instagram App ID or Facebook App ID
if [[ ${#CLIENT_ID} -eq 16 ]]; then
    echo "✅ Client ID length looks correct for Instagram (16 digits)"
else
    echo "⚠️  Client ID is ${#CLIENT_ID} digits"
    echo "   Instagram App IDs are usually 16 digits"
fi

echo ""
echo "🎯 Where to Find Correct Credentials:"
echo ""
echo "1. Go to: https://developers.facebook.com/apps/1539628577054406"
echo "2. Click 'Instagram Basic Display' (left sidebar)"
echo "3. Look for:"
echo "   • Instagram App ID (NOT Facebook App ID)"
echo "   • Instagram App Secret (click 'Show')"
echo ""
echo "4. Make sure these match your .env file!"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "📝 Current Redirect URI Configuration:"
echo ""
echo "Your .env has:"
echo "  $REDIRECT_URI"
echo ""
echo "Make sure Facebook app has EXACTLY:"
echo "  $REDIRECT_URI"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

