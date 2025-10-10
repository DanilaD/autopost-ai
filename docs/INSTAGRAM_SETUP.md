# Instagram Integration Setup Guide

**Complete guide to setting up Instagram OAuth integration for Autopost AI**

---

## 📋 Prerequisites

- Facebook Developer Account
- Instagram Business or Creator Account
- Autopost AI application running

---

## 🚀 Step 1: Create Facebook App

### 1.1 Go to Facebook Developers

Visit: [https://developers.facebook.com/apps](https://developers.facebook.com/apps)

Click **"Create App"**

### 1.2 Choose App Type

- Select: **"Consumer"**
- Click **"Next"**

### 1.3 App Details

- **App Name:** Autopost AI (or your choice)
- **App Contact Email:** your-email@domain.com
- Click **"Create App"**

---

## 🔧 Step 2: Add Instagram Basic Display

### 2.1 Add Product

1. In your App Dashboard, find **"Instagram Basic Display"**
2. Click **"Set Up"**

### 2.2 Basic Settings

1. Scroll to **"Basic Display"** section
2. Click **"Create New App"**
3. **Display Name:** Autopost AI
4. Click **"Create App"**

---

## ⚙️ Step 3: Configure Instagram App

### 3.1 OAuth Redirect URIs

Add your callback URLs (one per line):

```
http://localhost:8000/instagram/callback
https://your-domain.com/instagram/callback
https://autopost-ai.test/instagram/callback  # If using Valet
```

### 3.2 Deauthorize Callback URL

```
https://your-domain.com/instagram/webhook/deauthorize
```

### 3.3 Data Deletion Request URL

```
https://your-domain.com/instagram/webhook/delete
```

### 3.4 Save Changes

Click **"Save Changes"** at the bottom

---

## 🔑 Step 4: Get Your Credentials

### 4.1 Instagram App ID & Secret

In the **"Basic Display"** tab, you'll find:

- **Instagram App ID** (this is your Client ID)
- **Instagram App Secret** (this is your Client Secret)

### 4.2 Add to `.env`

Update your `.env` file:

```env
# Instagram OAuth
INSTAGRAM_CLIENT_ID=your_instagram_app_id_here
INSTAGRAM_CLIENT_SECRET=your_instagram_app_secret_here
INSTAGRAM_REDIRECT_URI=${APP_URL}/instagram/callback
```

**Example:**

```env
INSTAGRAM_CLIENT_ID=123456789012345
INSTAGRAM_CLIENT_SECRET=abcdef1234567890abcdef1234567890
INSTAGRAM_REDIRECT_URI=https://autopost-ai.test/instagram/callback
```

---

## 👤 Step 5: Add Test Users (Development)

### 5.1 Add Instagram Testers

1. In App Dashboard → **Instagram Basic Display** → **Roles** → **Roles**
2. Click **"Add Instagram Testers"**
3. Enter Instagram username
4. Click **"Submit"**

### 5.2 Accept Invitation

1. Login to Instagram
2. Go to **Settings** → **Apps and Websites** → **Tester Invites**
3. Accept the invitation

---

## ✅ Step 6: Test the Integration

### 6.1 Start Your Application

```bash
php artisan serve
npm run dev
```

### 6.2 Visit Dashboard

```
http://localhost:8000/dashboard
```

### 6.3 Connect Instagram

1. Click **"Connect Instagram"** button
2. You'll be redirected to Instagram
3. Login if needed
4. Click **"Allow"** to authorize
5. You'll be redirected back to your app
6. Success! Your Instagram account is now connected

---

## 🧪 Step 7: Verify Token Refresh

### 7.1 Check Scheduled Jobs

```bash
php artisan schedule:list
```

You should see: `instagram:refresh-tokens` scheduled daily

### 7.2 Test Token Refresh Manually

```bash
php artisan instagram:refresh-tokens
```

---

## 🎯 Step 8: Production Setup

### 8.1 Submit for App Review

Before going live, you need to submit your app for review:

1. Complete **"App Review"** in Facebook Dashboard
2. Request permissions:
    - `instagram_basic`
    - `user_media` (if you plan to post)
3. Provide: Privacy Policy URL, Terms of Service URL
4. Wait for approval (usually 2-5 business days)

### 8.2 Production Environment Variables

Update your production `.env`:

```env
APP_URL=https://your-production-domain.com
INSTAGRAM_CLIENT_ID=your_production_app_id
INSTAGRAM_CLIENT_SECRET=your_production_app_secret
INSTAGRAM_REDIRECT_URI=https://your-production-domain.com/instagram/callback
```

### 8.3 Update Facebook App Settings

1. Add production domain to **"App Domains"**
2. Add production callback URL to **"OAuth Redirect URIs"**
3. Switch app from **"Development"** to **"Live"** mode

---

## 📊 Token Lifecycle

### Token Types

1. **Short-Lived Token:** Expires in 1 hour
    - Automatically exchanged for long-lived token

2. **Long-Lived Token:** Expires in 60 days
    - Stored encrypted in database
    - Auto-refreshed by scheduled job

### Token Refresh Schedule

```php
// Runs daily at midnight
Schedule::command('instagram:refresh-tokens')->daily();
```

Tokens expiring within 7 days are automatically refreshed.

---

## 🔍 Troubleshooting

### Error: "Redirect URI mismatch"

**Solution:** Make sure your callback URL in `.env` matches exactly what's in Facebook App settings.

```env
# ✅ Correct
INSTAGRAM_REDIRECT_URI=https://autopost-ai.test/instagram/callback

# ❌ Wrong (missing /instagram/callback)
INSTAGRAM_REDIRECT_URI=https://autopost-ai.test
```

### Error: "Invalid Client ID"

**Solution:**

1. Check `INSTAGRAM_CLIENT_ID` in `.env`
2. Clear config cache: `php artisan config:clear`
3. Verify App ID in Facebook Developer Dashboard

### Error: "User not authorized"

**Solution:** If in development mode, make sure the Instagram user is added as a tester (Step 5).

### Token Expired

**Solution:** Reconnect the account:

1. Go to Instagram accounts page
2. Click **"Disconnect"**
3. Click **"Connect Instagram"** again

---

## 📝 Important Notes

### Development vs Production

- **Development Mode:** Only works with test users
- **Live Mode:** Works with any Instagram account (after app review)

### Rate Limits

Instagram API has rate limits:

- **200 calls per hour** per user
- Store data locally to minimize API calls

### Permissions

Basic Display API provides:

- User profile (username, account type)
- Media (posts, photos, videos)
- **Cannot:** Post to Instagram (requires Content Publishing API)

---

## 🔗 Useful Links

- [Instagram Basic Display API Docs](https://developers.facebook.com/docs/instagram-basic-display-api)
- [Facebook App Dashboard](https://developers.facebook.com/apps)
- [Instagram API Rate Limits](https://developers.facebook.com/docs/graph-api/overview/rate-limiting)
- [Token Refresh Guide](https://developers.facebook.com/docs/instagram-basic-display-api/guides/long-lived-access-tokens)

---

## ✅ Checklist

- [ ] Facebook App created
- [ ] Instagram Basic Display added
- [ ] Callback URLs configured
- [ ] Credentials added to `.env`
- [ ] Test users added (development)
- [ ] Test connection successful
- [ ] Token refresh tested
- [ ] Production app submitted for review
- [ ] Production credentials configured

---

**Last Updated:** October 10, 2025  
**Version:** 1.0
