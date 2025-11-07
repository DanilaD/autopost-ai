# Instagram API Integration - Complete Setup Guide

**Status:** Step-by-Step Setup Instructions  
**Last Updated:** November 7, 2025  
**Version:** 2.0

---

## 📋 Overview

This guide will walk you through **completing the Instagram API integration** for your Autopost AI application. The infrastructure is already built - you just need to configure the API credentials and test the connection.

---

## ✅ What's Already Built

Your application already has:

- ✅ Complete Instagram Service (`app/Services/InstagramService.php`)
- ✅ OAuth Controllers (`InstagramOAuthController`, `InstagramAccountController`)
- ✅ Database schema (`instagram_accounts` table)
- ✅ UI Components (Connect button, account management)
- ✅ Token refresh mechanism
- ✅ Error handling

**You just need:** Facebook Developer App credentials

---

## 🎯 Step-by-Step Setup

### Step 1: Create Facebook Developer Account (5 minutes)

1. **Go to Facebook Developers:**
    - Visit: https://developers.facebook.com/
    - Click "Get Started" or "My Apps"

2. **Create Account (if needed):**
    - Use your Facebook account
    - Accept terms and conditions
    - Verify your email if prompted

3. **Access App Dashboard:**
    - Go to: https://developers.facebook.com/apps
    - You should see "Create App" button

---

### Step 2: Create Facebook App (10 minutes)

1. **Click "Create App"**
    - You'll see a modal with app types

2. **Select App Type:**
    - Choose **"Consumer"** or **"Business"**
    - Click "Next"

3. **Fill App Details:**

    ```
    App Name: Autopost AI (or your app name)
    App Contact Email: your-email@example.com
    Business Account: (optional, can skip)
    ```

    - Click "Create App"

4. **Skip Quick Start (for now):**
    - Click "Skip" or "Skip Quick Start"
    - You'll be taken to the app dashboard

---

### Step 3: Add Instagram Basic Display Product (10 minutes)

1. **In App Dashboard:**
    - Look for "Add Products" or "Products" in left sidebar
    - Find **"Instagram Basic Display"**
    - Click "Set Up" button

2. **Configure Instagram Basic Display:**
    - You'll see a setup screen
    - Click "Create New App" or "Use Existing App"
    - Follow the prompts

3. **Add OAuth Redirect URI:**
    - Go to "Basic Display" → "Settings"
    - Find "Valid OAuth Redirect URIs"
    - Add your callback URL:

        ```
        For local development:
        http://localhost:8000/instagram/callback

        For Valet (autopost-ai.test):
        https://autopost-ai.test/instagram/callback

        For production:
        https://yourdomain.com/instagram/callback
        ```

    - Click "Save Changes"

---

### Step 4: Get App Credentials (5 minutes)

1. **Get App ID:**
    - In app dashboard, go to "Settings" → "Basic"
    - Find **"App ID"** - copy this value

2. **Get App Secret:**
    - In same page, find **"App Secret"**
    - Click "Show" and copy the secret
    - ⚠️ **Keep this secret secure!**

3. **Note Your Credentials:**
    ```
    App ID: 1234567890123456
    App Secret: abc123def456ghi789jkl012mno345pqr
    ```

---

### Step 5: Configure Your Application (5 minutes)

1. **Open your `.env` file:**

    ```bash
    cd /Users/daniladolmatov/Sites/autopost-ai
    nano .env
    # or use your preferred editor
    ```

2. **Add Instagram Credentials:**

    ```env
    # Instagram API Configuration
    INSTAGRAM_CLIENT_ID=your_app_id_here
    INSTAGRAM_CLIENT_SECRET=your_app_secret_here
    INSTAGRAM_REDIRECT_URI=${APP_URL}/instagram/callback
    ```

    **Replace:**
    - `your_app_id_here` with your App ID from Step 4
    - `your_app_secret_here` with your App Secret from Step 4

3. **For Local Development (Valet):**

    ```env
    INSTAGRAM_REDIRECT_URI=https://autopost-ai.test/instagram/callback
    ```

4. **For Production:**

    ```env
    INSTAGRAM_REDIRECT_URI=https://yourdomain.com/instagram/callback
    ```

5. **Clear Config Cache:**
    ```bash
    php artisan config:clear
    php artisan cache:clear
    ```

---

### Step 6: Add Test Users (Development Mode) (10 minutes)

**Important:** In development mode, only test users can connect their Instagram accounts.

1. **In Facebook App Dashboard:**
    - Go to "Roles" → "Roles" in left sidebar
    - Or go to "Instagram Basic Display" → "User Token Generator"

2. **Add Instagram Testers:**
    - Go to "Roles" → "Instagram Testers"
    - Click "Add Instagram Testers"
    - Enter Instagram usernames of test accounts
    - Click "Submit"

3. **Accept Invitation on Instagram:**
    - Open Instagram app on your phone
    - Go to Settings → Apps and Websites → Tester Invites
    - Accept the invitation for your app
    - ⚠️ **This must be done on the Instagram mobile app**

4. **Verify Test Users:**
    - In Facebook dashboard, test users should show as "Accepted"
    - Only accepted testers can connect their accounts

---

### Step 7: Test the Integration (5 minutes)

1. **Start Your Application:**

    ```bash
    php artisan serve
    # or if using Valet, just visit https://autopost-ai.test
    ```

2. **Visit Instagram Page:**
    - Go to: `https://autopost-ai.test/instagram`
    - You should see the Instagram accounts page
    - No error messages should appear

3. **Click "Connect Instagram Account":**
    - You'll be redirected to Instagram OAuth page
    - Log in with your Instagram account (must be a test user)
    - Authorize the app

4. **Verify Connection:**
    - You'll be redirected back to your app
    - Should see success message
    - Instagram account should appear in the list

---

## 🔧 Configuration Files

### `.env` File

```env
# Instagram API Configuration
INSTAGRAM_CLIENT_ID=1234567890123456
INSTAGRAM_CLIENT_SECRET=abc123def456ghi789jkl012mno345pqr
INSTAGRAM_REDIRECT_URI=${APP_URL}/instagram/callback
```

### `config/services.php` (Already Configured)

The service configuration is already set up. It reads from `.env`:

```php
'instagram' => [
    'client_id' => env('INSTAGRAM_CLIENT_ID'),
    'client_secret' => env('INSTAGRAM_CLIENT_SECRET'),
    'redirect_uri' => env('INSTAGRAM_REDIRECT_URI'),
],
```

---

## 🧪 Testing Checklist

After setup, verify:

- [ ] `.env` file has Instagram credentials
- [ ] Config cache cleared (`php artisan config:clear`)
- [ ] Facebook app has Instagram Basic Display product
- [ ] OAuth redirect URI matches in Facebook app and `.env`
- [ ] Test users added and accepted invitation
- [ ] Can visit `/instagram` page without errors
- [ ] "Connect Instagram Account" button works
- [ ] OAuth flow redirects to Instagram
- [ ] Can authorize and connect account
- [ ] Account appears in list after connection
- [ ] Profile sync works (sync button)

---

## 🚨 Common Issues & Solutions

### Issue 1: "Instagram API credentials not configured"

**Solution:**

```bash
# Check .env file has credentials
grep INSTAGRAM .env

# Clear config cache
php artisan config:clear
```

### Issue 2: "Redirect URI mismatch"

**Error:** `redirect_uri_mismatch`

**Solution:**

1. Check `.env` file has correct redirect URI
2. Check Facebook app settings has **exact same** redirect URI
3. Must match exactly (including http/https, trailing slashes)
4. Clear config cache: `php artisan config:clear`

### Issue 3: "User not authorized"

**Error:** User can't connect their Instagram account

**Solution:**

1. Make sure user is added as Instagram tester in Facebook app
2. User must accept invitation on Instagram mobile app
3. Check "Roles" → "Instagram Testers" shows "Accepted" status

### Issue 4: "Invalid OAuth access token"

**Solution:**

1. Tokens expire after 60 days
2. Token refresh is automatic (runs daily)
3. Manual refresh: `php artisan instagram:refresh-tokens`
4. If still failing, disconnect and reconnect account

### Issue 5: "App not in development mode"

**Solution:**

- In development, only test users can connect
- Add users as Instagram testers
- For production, submit app for review

---

## 📱 Production Setup

### Before Going Live

1. **Submit App for Review:**
    - Go to "App Review" in Facebook dashboard
    - Request permissions:
        - `instagram_basic` - User profile and media
        - `user_media` - Access to user's media
    - Fill out required information
    - Submit for review (can take 1-7 days)

2. **Switch to Live Mode:**
    - After approval, switch app to "Live" mode
    - All users can now connect (not just testers)

3. **Update Production `.env`:**

    ```env
    INSTAGRAM_CLIENT_ID=your_production_app_id
    INSTAGRAM_CLIENT_SECRET=your_production_app_secret
    INSTAGRAM_REDIRECT_URI=https://yourdomain.com/instagram/callback
    ```

4. **Test Production Flow:**
    - Test with real users
    - Monitor error logs
    - Check token refresh is working

---

## 🔐 Security Best Practices

1. **Never commit `.env` file:**
    - Already in `.gitignore`
    - Use environment variables in production

2. **Rotate Secrets Regularly:**
    - Change App Secret if compromised
    - Update in `.env` and clear cache

3. **Use HTTPS in Production:**
    - OAuth requires HTTPS
    - Configure SSL certificate

4. **Monitor Token Expiry:**
    - Tokens expire after 60 days
    - Automatic refresh is configured
    - Check logs for refresh failures

---

## 📊 API Rate Limits

**Instagram Basic Display API:**

- **200 requests per hour** per user
- **Token refresh:** Unlimited (but use sparingly)
- **Profile sync:** 200/hour per account

**Your Implementation:**

- ✅ Automatic rate limit handling
- ✅ Error logging for rate limit errors
- ✅ Graceful degradation

---

## 🎯 Next Steps After Setup

Once Instagram is connected:

1. **Test Profile Sync:**
    - Click "Sync" button on connected account
    - Verify profile data updates

2. **Test Post Publishing:**
    - Create a post in your app
    - Schedule or publish to Instagram
    - Verify it appears on Instagram

3. **Monitor Token Refresh:**
    - Check scheduled job runs daily
    - Verify tokens refresh before expiry
    - Check logs for any errors

4. **Set Up Webhooks (Optional):**
    - For real-time updates
    - Instagram account changes
    - Post status updates

---

## 📚 Additional Resources

### Documentation

- **Your Project Docs:**
    - `docs/INSTAGRAM_SETUP.md` - Detailed setup guide
    - `docs/INSTAGRAM_INTEGRATION_SETUP_PLAN.md` - Integration plan
    - `docs/DATABASE_SCHEMA.md` - Database structure

### External Resources

- **Facebook Developers:**
    - https://developers.facebook.com/docs/instagram-basic-display-api
    - https://developers.facebook.com/apps

- **Instagram API:**
    - https://developers.facebook.com/docs/instagram-basic-display-api/overview
    - https://developers.facebook.com/docs/instagram-basic-display-api/guides/getting-access-tokens-and-permissions

---

## ✅ Quick Reference

### Essential Commands

```bash
# Clear config cache (after .env changes)
php artisan config:clear

# Clear all caches
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Test token refresh manually
php artisan instagram:refresh-tokens

# Check Instagram service status
php artisan tinker
>>> app(\App\Services\InstagramService::class)->isDummyCredentials()
```

### Environment Variables

```env
INSTAGRAM_CLIENT_ID=your_app_id
INSTAGRAM_CLIENT_SECRET=your_app_secret
INSTAGRAM_REDIRECT_URI=${APP_URL}/instagram/callback
```

### Facebook App Settings

- **App ID:** Settings → Basic → App ID
- **App Secret:** Settings → Basic → App Secret
- **Redirect URI:** Instagram Basic Display → Settings → Valid OAuth Redirect URIs

---

## 🎉 Success Criteria

You'll know setup is complete when:

- ✅ No error messages on `/instagram` page
- ✅ "Connect Instagram Account" button works
- ✅ OAuth flow redirects to Instagram
- ✅ Can authorize and connect account
- ✅ Account appears in list
- ✅ Profile sync works
- ✅ Token refresh runs automatically

---

**Last Updated:** November 7, 2025  
**Version:** 2.0  
**Status:** Complete Setup Guide
