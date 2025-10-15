# Valet Share - Expose Your Local App to Internet

**Date:** October 10, 2025  
**Purpose:** Share your local Valet app with the internet for Instagram OAuth testing

---

## 🚀 Quick Start

### Step 1: Start Valet Share

**Open a new terminal and run:**

```bash
cd /Users/daniladolmatov/Sites/autopost-ai
./share-app.sh
```

Or directly:

```bash
valet share
```

### Step 2: Get Your Public URL

You'll see output like:

```
ngrok

Session Status                online
Account                       Your Name
Version                       3.x.x
Region                        United States (us)
Latency                       45ms
Web Interface                 http://127.0.0.1:4040
Forwarding                    https://abc123def456.ngrok-free.app -> https://autopost-ai.test

Connections                   ttl     opn     rt1     rt5     p50     p90
                              0       0       0.00    0.00    0.00    0.00
```

**Your public URL:** `https://abc123def456.ngrok-free.app`

⚠️ **Copy this URL - you'll need it!**

---

## 📝 Step 3: Update Your Configuration

### A. Update `.env` File

Open `/Users/daniladolmatov/Sites/autopost-ai/.env` and change:

```env
# Comment out the local URL
# APP_URL=https://autopost-ai.test

# Add your ngrok URL (replace with your actual URL)
APP_URL=https://abc123def456.ngrok-free.app

# Update Instagram redirect URI
INSTAGRAM_REDIRECT_URI=https://abc123def456.ngrok-free.app/instagram/callback
```

### B. Clear Laravel Cache

**In a new terminal (keep valet share running):**

```bash
cd /Users/daniladolmatov/Sites/autopost-ai
php artisan config:clear
```

---

## 🔧 Step 4: Update Facebook App Settings

### A. Go to Facebook Developer Console

Visit: https://developers.facebook.com/apps/1539628577054406

### B. Add Your ngrok URL

1. Click **"Instagram Basic Display"** in left sidebar
2. Scroll to **"Valid OAuth Redirect URIs"**
3. **Add** (keep the existing .test URL too):

```
https://abc123def456.ngrok-free.app/instagram/callback
```

**Important:** Replace `abc123def456` with YOUR actual ngrok subdomain!

4. Click **"Save Changes"**

---

## ✅ Step 5: Test Instagram Connection

### A. Access Your App via Public URL

Open in browser: `https://abc123def456.ngrok-free.app`

**Note:** You might see an ngrok warning page first - click "Visit Site"

### B. Login to Your App

Use your normal credentials

### C. Connect Instagram

1. Click **"Connect Instagram Account"**
2. Authorize on Instagram
3. Get redirected back
4. Success! ✅

---

## 🔄 Managing Valet Share

### View Active Tunnels

Visit: http://127.0.0.1:4040

This shows:

- Current requests
- Request/response details
- Replay requests (useful for debugging)

### Stop Valet Share

**In the terminal where `valet share` is running:**

Press `Ctrl + C`

### Restart Valet Share

```bash
cd /Users/daniladolmatov/Sites/autopost-ai
valet share
```

⚠️ **Free ngrok:** URL changes each time you restart!

---

## 💰 Free vs Paid ngrok

### Free Plan (Current)

- ✅ Works great for testing
- ❌ URL changes every restart
- ❌ 40 connections/minute limit
- ⚠️ Shows ngrok warning page to visitors

### Paid Plan ($10/month)

- ✅ **Static domain** (never changes!)
- ✅ No connection limits
- ✅ No warning page
- ✅ Custom branded domains

**Get paid plan:** https://dashboard.ngrok.com/billing/plan

---

## 🎯 Using Static Domain (Paid Plan)

If you upgrade to ngrok paid plan:

### Step 1: Get Your Static Domain

1. Visit: https://dashboard.ngrok.com/domains
2. Click "New Domain"
3. You'll get something like: `autopost-ai.ngrok.app`

### Step 2: Use Static Domain with Valet

```bash
valet share --subdomain=autopost-ai
```

### Step 3: One-Time Setup

**Update .env (only once):**

```env
APP_URL=https://autopost-ai.ngrok.app
INSTAGRAM_REDIRECT_URI=https://autopost-ai.ngrok.app/instagram/callback
```

**Update Facebook (only once):**

```
Valid OAuth Redirect URIs:
https://autopost-ai.ngrok.app/instagram/callback
```

**Now your URL never changes!** 🎉

---

## 🚦 Switching Between Local and Public

### Development (Local Only)

**.env:**

```env
APP_URL=https://autopost-ai.test
INSTAGRAM_REDIRECT_URI=https://autopost-ai.test/instagram/callback
```

**Run:**

```bash
php artisan config:clear
npm run dev
```

**Access:** https://autopost-ai.test

---

### Testing (Public URL)

**Terminal 1 - Start Valet Share:**

```bash
valet share
# Copy the ngrok URL
```

**Terminal 2 - Update Config:**

```bash
# Edit .env with ngrok URL
php artisan config:clear
npm run dev
```

**Access:** https://abc123def456.ngrok-free.app

---

## 🐛 Troubleshooting

### Error: "ngrok: command not found"

**Solution:**

```bash
brew install ngrok
```

### Error: "valet: command not found"

**Solution:**

```bash
composer global require laravel/valet
valet install
```

### Error: "Session Expired" (Free Plan)

**Cause:** Free ngrok sessions expire after 2 hours

**Solution:**

1. Stop valet share (Ctrl+C)
2. Restart: `valet share`
3. Update `.env` with new URL
4. Run: `php artisan config:clear`
5. Update Facebook app with new URL

**Better Solution:** Upgrade to paid plan for unlimited sessions

### Error: "Redirect URI Mismatch"

**Check:**

1. `.env` file has correct ngrok URL
2. Facebook app has correct ngrok URL
3. URLs match exactly (including `/instagram/callback`)
4. Cache cleared: `php artisan config:clear`

### HMR/Vite Not Working with Public URL

**Solution:** Update `vite.config.js`:

```js
export default defineConfig({
    // ... existing config
    server: {
        hmr: {
            host: 'abc123def456.ngrok-free.app', // Your ngrok domain
        },
    },
})
```

Then restart: `npm run dev`

---

## 📊 Complete Workflow

### Daily Development (Local)

```bash
# Just use Valet
npm run dev

# Access
https://autopost-ai.test
```

---

### When You Need Public Access

**Terminal 1:**

```bash
valet share
# Copy the URL
```

**Terminal 2:**

```bash
# Edit .env with ngrok URL
nano .env

# Clear cache
php artisan config:clear

# Start Vite
npm run dev
```

**Browser:**

1. Update Facebook app with ngrok URL
2. Visit ngrok URL
3. Test Instagram OAuth
4. Done!

---

### Returning to Local Development

```bash
# Stop valet share (Ctrl+C in Terminal 1)

# Restore .env
APP_URL=https://autopost-ai.test
INSTAGRAM_REDIRECT_URI=https://autopost-ai.test/instagram/callback

# Clear cache
php artisan config:clear

# Continue working
npm run dev
```

---

## 💡 Pro Tips

1. **Keep valet share running** while testing - don't stop/start repeatedly
2. **Bookmark the ngrok dashboard** - http://127.0.0.1:4040
3. **Add both URLs to Facebook** - switch easily without updating
4. **Use paid ngrok for frequent sharing** - saves time
5. **Test locally first** - then expose when needed
6. **Comment config in .env** - easy to switch back

---

## 📋 Quick Reference

### Commands

```bash
# Start sharing
valet share

# Start with custom subdomain (paid plan)
valet share --subdomain=autopost-ai

# View tunnel dashboard
open http://127.0.0.1:4040

# Clear config cache
php artisan config:clear

# Check current config
php artisan tinker
>>> config('app.url')
>>> exit
```

### URLs You Need

- **Local:** https://autopost-ai.test
- **ngrok:** https://[random].ngrok-free.app (changes each time)
- **ngrok Dashboard:** http://127.0.0.1:4040
- **Facebook App:** https://developers.facebook.com/apps/1539628577054406

---

## ✅ Success Checklist

Before testing Instagram OAuth with public URL:

```
□ valet share running
□ Public URL copied
□ .env updated with public URL
□ Config cache cleared (php artisan config:clear)
□ npm run dev running
□ Facebook app has public URL added
□ Can access app via public URL
□ Instagram test user added and accepted
```

---

## 🎓 Understanding Valet Share

### What It Does

```
Your Computer              ngrok Cloud              Internet
┌─────────────┐           ┌───────────┐           ┌─────────┐
│             │           │           │           │         │
│  Valet App  │  ←────→   │   ngrok   │  ←────→   │  World  │
│  :443       │  tunnel   │   proxy   │  HTTPS    │         │
│             │           │           │           │         │
└─────────────┘           └───────────┘           └─────────┘
```

### How It Works

1. Valet serves your app locally at `https://autopost-ai.test`
2. `valet share` creates an ngrok tunnel
3. ngrok gives you a public URL
4. Requests to public URL → ngrok → your local Valet
5. Responses: your Valet → ngrok → visitor

**Benefits:**

- ✅ No deployment needed
- ✅ Instant updates (changes immediately visible)
- ✅ Full debugging capabilities
- ✅ Works with any external service (Instagram, webhooks, etc.)

---

## 🔗 Resources

- **Valet Share Docs:** https://laravel.com/docs/valet#sharing-sites
- **ngrok Dashboard:** https://dashboard.ngrok.com
- **ngrok Docs:** https://ngrok.com/docs
- **Instagram API:** https://developers.facebook.com/docs/instagram-basic-display-api

---

**Last Updated:** October 10, 2025  
**Version:** 1.0
