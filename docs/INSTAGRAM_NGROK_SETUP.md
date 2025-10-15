# Instagram OAuth with ngrok (Optional Setup)

**Date:** October 10, 2025  
**Use Case:** When you need external access to your local app  
**Status:** Optional - only needed in specific scenarios

---

## ⚠️ Do You Need This?

**You DON'T need ngrok if:**

- ✅ You're using Laravel Valet (already have HTTPS)
- ✅ Testing only on your own computer
- ✅ Your `.test` domain works fine
- ✅ You're the only developer

**You NEED ngrok if:**

- 📱 Testing from mobile device on different network
- 👥 Sharing with remote team members
- 🎤 Client demo from your local machine
- 🔔 Testing Instagram webhooks

---

## 🚀 Quick ngrok Setup

### Step 1: Install ngrok

**macOS (Homebrew):**

```bash
brew install ngrok
```

**Manual Download:**
Visit: https://ngrok.com/download

### Step 2: Sign Up & Get Auth Token

1. Go to: https://dashboard.ngrok.com/signup
2. Copy your auth token
3. Run:

```bash
ngrok config add-authtoken YOUR_AUTH_TOKEN_HERE
```

### Step 3: Start Your Laravel App

```bash
cd /Users/daniladolmatov/Sites/autopost-ai
php artisan serve
npm run dev
```

**Note:** Must use `php artisan serve` (port 8000), not Valet

### Step 4: Start ngrok Tunnel

**In a new terminal:**

```bash
ngrok http 8000
```

**Output:**

```
ngrok

Session Status                online
Account                       Your Name (Plan: Free)
Version                       3.x.x
Region                        United States (us)
Latency                       50ms
Web Interface                 http://127.0.0.1:4040
Forwarding                    https://abc123def456.ngrok-free.app -> http://localhost:8000

Connections                   ttl     opn     rt1     rt5     p50     p90
                              0       0       0.00    0.00    0.00    0.00
```

**Your public URL:** `https://abc123def456.ngrok-free.app`

⚠️ **This URL changes every time you restart ngrok (free plan)**

### Step 5: Update .env

**While ngrok is running:**

```env
APP_URL=https://abc123def456.ngrok-free.app
INSTAGRAM_REDIRECT_URI=https://abc123def456.ngrok-free.app/instagram/callback
```

**Clear cache:**

```bash
php artisan config:clear
```

### Step 6: Update Facebook App Settings

1. Go to: https://developers.facebook.com/apps/1539628577054406
2. Click **"Instagram Basic Display"**
3. **Add** to OAuth Redirect URIs:

```
https://abc123def456.ngrok-free.app/instagram/callback
```

4. Click **"Save Changes"**

### Step 7: Test the Connection

1. Visit: `https://abc123def456.ngrok-free.app`
2. Login to your app
3. Click "Connect Instagram Account"
4. Authorize the app
5. Success! ✅

---

## 💰 ngrok Plans Comparison

### Free Plan

- ✅ 1 online process
- ✅ HTTPS support
- ❌ **URL changes each restart**
- ❌ Rate limited
- ⏱️ 2-hour sessions

**Best for:** Quick testing, one-off demos

### Personal Plan ($10/month)

- ✅ 3 online processes
- ✅ **Static domains** (URL never changes!)
- ✅ No rate limits
- ✅ Custom branded domains
- ✅ Unlimited sessions

**Best for:** Frequent remote testing, team collaboration

---

## 🎯 Recommended Workflow

### For Daily Development (Use Valet)

```bash
# Start Valet (if not already)
valet start

# Work as normal
npm run dev
```

**Access:** `https://autopost-ai.test`

**.env:**

```env
APP_URL=https://autopost-ai.test
INSTAGRAM_REDIRECT_URI=https://autopost-ai.test/instagram/callback
```

---

### For Mobile Testing (Use ngrok)

**Terminal 1:**

```bash
php artisan serve
```

**Terminal 2:**

```bash
npm run dev
```

**Terminal 3:**

```bash
ngrok http 8000
```

**Update .env with ngrok URL → Clear cache → Test**

---

### For Client Demo (Use ngrok)

Same as mobile testing, but remember:

1. Update `.env` with ngrok URL
2. Update Facebook app settings
3. Clear cache
4. Share the ngrok URL with client

---

## 🔧 Static Domain Setup (Paid Plan)

If you use ngrok frequently, get a static domain:

### Step 1: Subscribe to ngrok Personal Plan

Visit: https://dashboard.ngrok.com/billing/plan

### Step 2: Reserve a Domain

1. Go to: https://dashboard.ngrok.com/domains
2. Click **"New Domain"**
3. Choose: `yourapp.ngrok.app` (or custom)
4. Click **"Reserve"**

### Step 3: Use Static Domain

```bash
ngrok http 8000 --domain=yourapp.ngrok.app
```

**Your URL will ALWAYS be:**

```
https://yourapp.ngrok.app
```

### Step 4: One-Time Facebook Setup

Add to Facebook app (only once):

```
https://yourapp.ngrok.app/instagram/callback
```

### Step 5: One-Time .env Setup

```env
# Keep both configs for switching
APP_URL=https://autopost-ai.test  # For Valet
# APP_URL=https://yourapp.ngrok.app  # Uncomment for ngrok

INSTAGRAM_REDIRECT_URI=https://yourapp.ngrok.app/instagram/callback
```

---

## 🚦 Switching Between Valet and ngrok

### Use Valet (Default)

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

---

### Use ngrok (When Needed)

**.env:**

```env
APP_URL=https://abc123def456.ngrok-free.app
INSTAGRAM_REDIRECT_URI=https://abc123def456.ngrok-free.app/instagram/callback
```

**Run:**

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev

# Terminal 3
ngrok http 8000

# Terminal 4
php artisan config:clear
```

---

## 🐛 Troubleshooting

### Error: "Invalid Host Header"

**Solution:** Add to `vite.config.js`:

```js
server: {
    hmr: {
        host: 'abc123def456.ngrok-free.app',
    },
},
```

### Error: "ngrok: command not found"

**Solution:**

```bash
brew install ngrok
# or download from ngrok.com
```

### Error: "Session Expired" (Free Plan)

**Solution:**

- Free plan has 2-hour sessions
- Just restart ngrok
- Update .env with new URL
- Update Facebook app settings
- Clear cache

**Better Solution:**

- Upgrade to paid plan for unlimited sessions
- Or use Valet for daily development

### Error: "Redirect URI Mismatch"

**Solution:**

- Check `.env` matches ngrok URL exactly
- Check Facebook app has ngrok URL added
- Run `php artisan config:clear`
- Restart browser

---

## 📊 When to Use What

| Scenario                    | Tool           | Why                     |
| --------------------------- | -------------- | ----------------------- |
| Daily development           | **Valet**      | Fast, stable, no hassle |
| Testing on your computer    | **Valet**      | Already works           |
| Quick mobile test           | **ngrok Free** | External access         |
| Regular remote testing      | **ngrok Paid** | Static domain           |
| Client demo                 | **ngrok**      | Share easily            |
| Production deployment       | **VPS/Cloud**  | Real hosting            |
| Team collaboration (local)  | **Valet**      | Fast local network      |
| Team collaboration (remote) | **ngrok Paid** | Everyone can access     |

---

## 💡 Pro Tips

1. **Keep Valet as default** - faster and more stable
2. **Use ngrok only when needed** - external access scenarios
3. **Pay for static domain** - if you use ngrok regularly
4. **Keep both configs** - comment/uncomment in `.env`
5. **Add both URLs to Facebook** - switch easily
6. **Clear cache always** - after changing `.env`

---

## ✅ Recommended Setup

**For 90% of development:**

```
Use Valet → https://autopost-ai.test
```

**When you need to:**

- Show to client remotely
- Test from phone on different network
- Share with remote team member

```
Use ngrok → https://yourapp.ngrok.app
```

---

## 🔗 Resources

- **ngrok Docs:** https://ngrok.com/docs
- **ngrok Dashboard:** https://dashboard.ngrok.com
- **Laravel Valet Docs:** https://laravel.com/docs/valet
- **Instagram API Docs:** https://developers.facebook.com/docs/instagram-basic-display-api

---

**Last Updated:** October 10, 2025  
**Version:** 1.0
