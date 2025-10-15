# 🚀 Share Your Local App with the Internet

**Super Quick Guide - 3 Steps!**

---

## Step 1: Start Sharing (30 seconds)

**Open a terminal:**

```bash
cd /Users/daniladolmatov/Sites/autopost-ai
valet share
```

**You'll see:**

```
Forwarding   https://abc123def456.ngrok-free.app -> https://autopost-ai.test
```

**✅ Copy that URL!** (The `https://abc123...` one)

**⚠️ Keep this terminal open!**

---

## Step 2: Update Configuration (1 minute)

**Open a NEW terminal:**

```bash
cd /Users/daniladolmatov/Sites/autopost-ai

# Replace with YOUR actual ngrok URL
./update-ngrok-url.sh https://abc123def456.ngrok-free.app

# Clear cache
php artisan config:clear
```

**✅ Done!** Your app is now configured.

---

## Step 3: Update Facebook Settings (2 minutes)

**Go to:** https://developers.facebook.com/apps/1539628577054406

1. Click **"Instagram Basic Display"** (left sidebar)
2. Scroll to **"Valid OAuth Redirect URIs"**
3. **Add** your callback URL (keep existing URLs too):

```
https://abc123def456.ngrok-free.app/instagram/callback
```

4. Click **"Save Changes"**

**✅ All set!**

---

## 🎉 Test It!

1. Visit your public URL: `https://abc123def456.ngrok-free.app`
2. Login to your app
3. Click "Connect Instagram Account"
4. Authorize on Instagram
5. Success! 🎊

---

## 🔄 When You're Done

**To go back to local development:**

```bash
# Stop sharing (in the valet share terminal)
Press Ctrl+C

# Restore local config
./restore-local-config.sh

# Clear cache
php artisan config:clear
```

---

## 💡 Pro Tips

### View Requests

While `valet share` is running, visit:

```
http://127.0.0.1:4040
```

See all requests in real-time!

### Keep URL Forever

Upgrade to ngrok paid ($10/month):

- URL never changes
- No setup needed each time
- Visit: https://dashboard.ngrok.com/billing/plan

---

## 📝 Helpful Scripts

All in your project root:

| Script                      | Purpose                             |
| --------------------------- | ----------------------------------- |
| `./share-app.sh`            | Start valet share with instructions |
| `./update-ngrok-url.sh URL` | Update .env with ngrok URL          |
| `./restore-local-config.sh` | Restore local Valet config          |

---

## 🐛 Troubleshooting

### "valet: command not found"

```bash
composer global require laravel/valet
valet install
```

### "ngrok: command not found"

```bash
brew install ngrok
```

### URL Changed After Restart

Free ngrok changes URL each time. Either:

1. Update .env and Facebook settings again
2. Upgrade to paid plan for static URL

### Can't Access Public URL

1. Check `valet share` is still running
2. Try visiting `http://127.0.0.1:4040` to see if ngrok is active
3. Make sure you're using HTTPS (not HTTP)

---

## 📚 Full Documentation

For detailed info, see:

- **`VALET_SHARE_GUIDE.md`** - Complete guide
- **`INSTAGRAM_NGROK_SETUP.md`** - Alternative ngrok setup
- **`INSTAGRAM_TEST_USER_SETUP.md`** - Add Instagram test users

---

## 🎯 Quick Command Reference

```bash
# Start sharing
valet share

# Update config with ngrok URL
./update-ngrok-url.sh https://your-url.ngrok-free.app
php artisan config:clear

# Go back to local
# (Stop valet share with Ctrl+C, then:)
./restore-local-config.sh
php artisan config:clear

# View ngrok dashboard
open http://127.0.0.1:4040
```

---

**That's it! Happy testing! 🚀**

---

**Last Updated:** October 10, 2025
