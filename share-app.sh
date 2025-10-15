#!/bin/bash

# Script to share your Valet app with the internet
# Run this to get a public URL for your local app

echo "🚀 Starting Valet Share..."
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "⏳ This will create a public URL for: autopost-ai.test"
echo ""
echo "📝 IMPORTANT: After you see the ngrok URL below:"
echo "   1. Copy the HTTPS URL (looks like: https://abc123.ngrok-free.app)"
echo "   2. Update your .env file with this URL"
echo "   3. Run: php artisan config:clear"
echo "   4. Add the URL to Facebook Developer Console"
echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Run valet share
cd /Users/daniladolmatov/Sites/autopost-ai
valet share

