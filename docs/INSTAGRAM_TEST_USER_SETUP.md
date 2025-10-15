# Instagram Test User Setup

**Date:** October 10, 2025  
**Issue:** "User Not Authorized" error when connecting Instagram  
**Solution:** Add your Instagram account as a test user

---

## 🔍 The Problem

Instagram apps in **Development Mode** can only connect accounts that are added as **Test Users**.

### Error Message:

```
User Not Authorized
The user is not authorized to use this app.
```

---

## ✅ Solution: Add Test User

### Step 1: Go to Facebook App Dashboard

Visit: https://developers.facebook.com/apps/1539628577054406

### Step 2: Navigate to Roles

1. Click **"Instagram Basic Display"** in left sidebar
2. Click **"Roles"** tab (or find "Roles" in main menu)
3. Scroll to **"Instagram Testers"** section

### Step 3: Add Your Instagram Username

1. Click **"Add Instagram Testers"**
2. Enter your Instagram username (without @)
3. Click **"Submit"**

You'll see a message: "Invitation sent to @your_username"

### Step 4: Accept the Invitation on Instagram

#### Option A: Instagram Mobile App (Easier)

1. Open Instagram app on your phone
2. Go to: **Profile** → **☰ Menu** → **Settings**
3. Tap **"Apps and Websites"**
4. Tap **"Tester Invites"**
5. You'll see invitation from "Autopost AI"
6. Tap **"Accept"**

#### Option B: Instagram Web (Alternative)

1. Go to https://www.instagram.com/accounts/manage_access/
2. Look for **"Tester Invites"** section
3. Accept the invitation

### Step 5: Try Connecting Again

1. Go back to your Autopost AI app
2. Click **"Connect Instagram Account"**
3. Authorize the app
4. Success! ✅

---

## 🎯 If You Still See Errors

### Error: "Redirect URI Mismatch"

**Check your .env file:**

```env
INSTAGRAM_REDIRECT_URI=https://autopost-ai.test/instagram/callback
```

**Check Facebook App Settings:**

- Must match exactly: `https://autopost-ai.test/instagram/callback`
- No trailing slash
- Include the full path

### Error: "Invalid Client ID"

**Solution:**

```bash
# Clear cache
php artisan config:clear

# Restart Laravel
php artisan serve
```

### Error: "Invalid Client Secret"

**Double-check:**

1. Go to Facebook Developer Console
2. Click **"Instagram Basic Display"** → **"Basic Display"**
3. Click **"Show"** next to Instagram App Secret
4. Copy the **entire** secret (it's long)
5. Update `.env` file
6. Run: `php artisan config:clear`

---

## 📊 Development vs Production

### Development Mode (Current)

- ✅ Free to use
- ❌ Only works with test users (up to 25)
- ✅ Perfect for testing
- ❌ Cannot connect random Instagram accounts

### Live Mode (After App Review)

- ✅ Works with any Instagram account
- ❌ Requires Facebook App Review (2-5 days)
- ✅ Required for production
- ⏱️ Do this before launching to real users

---

## 🎓 Understanding the Flow

### Why Test Users?

Instagram/Facebook wants to prevent:

- Spam apps
- Phishing attempts
- Unauthorized data collection

**Solution:** Apps must be reviewed before they can access any Instagram account.

**During development:** Only "tester" accounts can connect.

### The Complete Authorization Flow

```
1. User clicks "Connect Instagram"
   ↓
2. Redirected to Instagram OAuth
   ↓
3. Instagram checks:
   - Is Client ID valid? ✅
   - Is Redirect URI registered? ✅
   - Is user a tester? (in dev mode) ✅
   ↓
4. User authorizes app
   ↓
5. Instagram redirects back with code
   ↓
6. Your app exchanges code for token
   ↓
7. Account connected! ✅
```

---

## ✅ Success Checklist

Before you can connect Instagram accounts, verify:

```
□ Instagram Basic Display product added
□ Client ID copied to .env
□ Client Secret copied to .env
□ Redirect URI added to Facebook app
□ Redirect URI in .env matches exactly
□ Instagram account added as tester
□ Tester invitation accepted on Instagram
□ Config cache cleared (php artisan config:clear)
```

---

## 🚀 Quick Commands

```bash
# Clear config cache
php artisan config:clear

# Check current config
php artisan tinker
>>> config('services.instagram.client_id')
>>> config('services.instagram.redirect_uri')
>>> exit

# View logs if errors occur
tail -f storage/logs/laravel.log
```

---

## 💡 Pro Tips

1. **Use your personal Instagram** as the first test user
2. **Add team members** as test users for collaboration
3. **Maximum 25 test users** in development mode
4. **Test the full flow** before submitting for review
5. **Screenshot everything** for app review submission

---

## 📞 Need Help?

**If you're stuck:**

1. Check `storage/logs/laravel.log` for detailed errors
2. Enable debug mode: `APP_DEBUG=true` in `.env`
3. Use browser DevTools Network tab to see the OAuth redirect
4. Verify all settings in Facebook Developer Console

**Common mistake:** Forgetting to accept the tester invite on Instagram!

---

**Last Updated:** October 10, 2025  
**Version:** 1.0
