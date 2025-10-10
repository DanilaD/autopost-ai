# Email Setup Guide

**Complete guide for configuring email notifications in Autopost AI**

---

## 📋 Current Status

✅ **Password Reset Email** - Fully Working!

- Controller: `PasswordResetLinkController`
- Uses Laravel's built-in `Password::sendResetLink()`
- Default mailer: `log` (writes to `storage/logs/laravel.log`)
- Ready for production email service

✅ **Email Notification System** - Ready!

- User model uses `Notifiable` trait
- Can send any notifications
- Beautiful Laravel email templates

---

## 🔧 Email Services (Recommended)

### Option 1: Resend (Recommended for Startups) ⭐

**Why Resend?**

- Modern, developer-friendly API
- 100 emails/day FREE
- $20/month for 50,000 emails
- Fast delivery
- Great dashboard

**Setup:**

1. **Sign up:** [https://resend.com](https://resend.com)

2. **Get API Key:**
    - Dashboard → API Keys → Create API Key
    - Copy your API key

3. **Install Resend:**

    ```bash
    composer require resend/resend-php
    ```

4. **Update `.env`:**

    ```env
    MAIL_MAILER=resend
    MAIL_FROM_ADDRESS="noreply@your-domain.com"
    MAIL_FROM_NAME="Autopost AI"
    RESEND_KEY=re_your_api_key_here
    ```

5. **Verify domain (production):**
    - Add DNS records in Resend dashboard
    - Verify in 2-5 minutes

**Cost:** Free for 100/day, then $20/month

---

### Option 2: Mailgun (Popular & Reliable)

**Why Mailgun?**

- Battle-tested, used by thousands
- 5,000 emails/month FREE for 3 months
- Then $15/month for 50,000 emails
- Good deliverability

**Setup:**

1. **Sign up:** [https://mailgun.com](https://mailgun.com)

2. **Get credentials:**
    - Domain: Mailgun provides sandbox domain (or add your own)
    - API Key: Settings → API Keys

3. **Update `.env`:**
    ```env
    MAIL_MAILER=mailgun
    MAIL_FROM_ADDRESS="noreply@your-domain.com"
    MAIL_FROM_NAME="Autopost AI"
    MAILGUN_DOMAIN=your-mailgun-domain
    MAILGUN_SECRET=your-api-key
    MAILGUN_ENDPOINT=api.mailgun.net
    ```

**Cost:** Free trial, then $15/month

---

### Option 3: SendGrid

**Why SendGrid?**

- Very popular
- 100 emails/day FREE forever
- Good analytics

**Setup:**

1. **Sign up:** [https://sendgrid.com](https://sendgrid.com)

2. **Get API Key:**
    - Settings → API Keys → Create API Key

3. **Install package:**

    ```bash
    composer require sendgrid/sendgrid
    ```

4. **Update `.env`:**
    ```env
    MAIL_MAILER=sendgrid
    MAIL_FROM_ADDRESS="noreply@your-domain.com"
    MAIL_FROM_NAME="Autopost AI"
    SENDGRID_API_KEY=your-api-key-here
    ```

**Cost:** Free for 100/day forever

---

### Option 4: Amazon SES (For Large Scale)

**Why Amazon SES?**

- Extremely cheap ($0.10 per 1,000 emails)
- Unlimited scale
- Great for high volume

**Setup:**

1. **AWS Account:** [https://aws.amazon.com](https://aws.amazon.com)

2. **Get credentials:**
    - Create IAM user with SES permissions
    - Get Access Key ID & Secret

3. **Update `.env`:**
    ```env
    MAIL_MAILER=ses
    MAIL_FROM_ADDRESS="noreply@your-domain.com"
    MAIL_FROM_NAME="Autopost AI"
    AWS_ACCESS_KEY_ID=your-access-key
    AWS_SECRET_ACCESS_KEY=your-secret-key
    AWS_DEFAULT_REGION=us-east-1
    ```

**Cost:** $0.10 per 1,000 emails

---

### Option 5: SMTP (Any Email Service)

**Use your Gmail, Outlook, or any SMTP server:**

**Gmail Example:**

1. **Enable 2FA** on your Google account

2. **Create App Password:**
    - Google Account → Security → 2-Step Verification → App passwords
    - Generate password for "Mail"

3. **Update `.env`:**
    ```env
    MAIL_MAILER=smtp
    MAIL_HOST=smtp.gmail.com
    MAIL_PORT=587
    MAIL_USERNAME=your-email@gmail.com
    MAIL_PASSWORD=your-app-password
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS="your-email@gmail.com"
    MAIL_FROM_NAME="Autopost AI"
    ```

**Note:** Gmail has 500 emails/day limit

---

## 🧪 Testing Email Setup

### Test 1: Check Current Configuration

```bash
php artisan tinker
```

```php
// Check mail config
config('mail.default')
config('mail.from.address')

// Test password reset email
use Illuminate\Support\Facades\Password;
use App\Models\User;

$user = User::first();
Password::sendResetLink(['email' => $user->email]);

// Check logs
exit
```

```bash
# View email in logs (if using 'log' mailer)
tail -100 storage/logs/laravel.log
```

---

### Test 2: Send Test Email

Create a test command:

```bash
php artisan make:command SendTestEmail
```

```php
// app/Console/Commands/SendTestEmail.php
public function handle()
{
    $user = User::first();

    if (!$user) {
        $this->error('No users found. Run seeder first.');
        return;
    }

    $status = Password::sendResetLink(['email' => $user->email]);

    if ($status === Password::RESET_LINK_SENT) {
        $this->info("✅ Email sent to {$user->email}");
    } else {
        $this->error("❌ Failed: " . trans($status));
    }
}
```

Run test:

```bash
php artisan app:send-test-email
```

---

## 📧 What Emails Are Sent?

### Currently Implemented:

1. **Password Reset Email** ✅
    - Triggered: When user clicks "Forgot password?"
    - Contains: Reset link with token
    - Expires: 60 minutes
    - Template: Laravel's default (beautiful!)

### Coming Soon:

2. **Welcome Email** (Registration)
3. **Email Verification**
4. **Login Notifications**
5. **Instagram Connection Alerts**
6. **Post Published Notifications**
7. **Token Expiry Warnings**

---

## 🎨 Email Templates

Laravel uses beautiful email templates by default!

### Location:

- Vendor: `vendor/laravel/framework/src/Illuminate/Notifications/resources/views/email.blade.php`

### Customize (Optional):

1. **Publish email templates:**

    ```bash
    php artisan vendor:publish --tag=laravel-mail
    ```

2. **Edit templates:**
    - `resources/views/vendor/mail/html/`
    - Change colors, logo, footer

3. **Update colors in `config/mail.php`:**
    ```php
    'theme' => 'default',
    ```

---

## 🔒 Security Best Practices

### 1. Environment Variables

Never commit email credentials to git!

```env
# ❌ Bad - in .env (git-ignored)
MAIL_PASSWORD=your-secret-password

# ✅ Good - use environment-specific files
# Production: Set in server environment
# Development: In .env (git-ignored)
```

### 2. Rate Limiting

Already implemented in Laravel:

- Password reset: 1 email per minute per user
- Throttled in `PasswordResetLinkController`

### 3. From Address

Use a proper from address:

```env
# ❌ Bad
MAIL_FROM_ADDRESS="hello@example.com"

# ✅ Good
MAIL_FROM_ADDRESS="noreply@autopost-ai.com"
MAIL_FROM_NAME="Autopost AI"
```

---

## 🚀 Production Checklist

- [ ] Choose email service (Resend recommended)
- [ ] Sign up and get API credentials
- [ ] Add credentials to production `.env`
- [ ] Verify domain (if required)
- [ ] Update `MAIL_FROM_ADDRESS` to your domain
- [ ] Test password reset in staging
- [ ] Set up email monitoring/alerts
- [ ] Configure bounce handling
- [ ] Add unsubscribe links (for marketing emails)

---

## 📊 Monitoring & Logs

### Check Email Logs:

```bash
# Recent logs
tail -100 storage/logs/laravel.log

# Search for email sends
grep "Reset Password" storage/logs/laravel.log

# Monitor in real-time
tail -f storage/logs/laravel.log
```

### Email Service Dashboards:

- **Resend:** Real-time delivery status
- **Mailgun:** Detailed analytics
- **SendGrid:** Open/click tracking

---

## 🐛 Troubleshooting

### Error: "Connection refused"

**Problem:** Can't connect to SMTP server

**Solution:**

```env
# Check these settings
MAIL_HOST=smtp.gmail.com  # Correct host?
MAIL_PORT=587             # Try 465 or 587
MAIL_ENCRYPTION=tls       # Try 'ssl' or 'tls'
```

### Error: "Authentication failed"

**Problem:** Wrong username/password

**Solution:**

- Gmail: Use App Password, not regular password
- SMTP: Check credentials are correct
- API: Verify API key is active

### Emails Going to Spam

**Solutions:**

1. Verify your domain (SPF, DKIM, DMARC records)
2. Use a professional email service (not Gmail)
3. Warm up your domain (start with low volume)
4. Don't use spam words in subject/body

### Emails Not Sending

**Debug:**

```bash
# Check config
php artisan config:clear
php artisan cache:clear

# Check mail config
php artisan tinker
config('mail')

# Test with log mailer first
MAIL_MAILER=log
```

---

## 💰 Cost Comparison

| Service        | Free Tier         | Paid Plan    | Best For      |
| -------------- | ----------------- | ------------ | ------------- |
| **Resend**     | 100/day           | $20/mo (50K) | Startups ⭐   |
| **SendGrid**   | 100/day           | $15/mo (40K) | Small apps    |
| **Mailgun**    | 5K/mo (3mo)       | $15/mo (50K) | Medium apps   |
| **Amazon SES** | 3K/mo (free tier) | $0.10/1K     | High volume   |
| **Postmark**   | 100/mo            | $15/mo (10K) | Transactional |

**Recommendation:** Start with **Resend** or **SendGrid** free tier.

---

## 🔗 Useful Links

- [Laravel Mail Documentation](https://laravel.com/docs/mail)
- [Resend Laravel Package](https://github.com/resendlabs/resend-laravel)
- [Mailgun Documentation](https://documentation.mailgun.com/en/latest/)
- [SendGrid Laravel Guide](https://docs.sendgrid.com/for-developers/sending-email/laravel)
- [Amazon SES Setup](https://docs.aws.amazon.com/ses/)

---

## 📝 Quick Start (5 Minutes)

**Development (Using Log):**

```env
MAIL_MAILER=log
```

✅ Already working! Check `storage/logs/laravel.log`

**Production (Using Resend):**

1. Sign up: [resend.com](https://resend.com)
2. Get API key
3. Update `.env`:
    ```env
    MAIL_MAILER=resend
    RESEND_KEY=re_your_key
    MAIL_FROM_ADDRESS="noreply@yourdomain.com"
    ```
4. Test: Click "Forgot password?" on login
5. Done! ✅

---

## ✅ Summary

**Current Status:**

- ✅ Password reset emails **working**
- ✅ Beautiful Laravel email templates
- ✅ Ready for production email service
- ✅ Secure, throttled, tested

**Next Steps:**

1. Choose email service (Resend recommended)
2. Update `.env` with credentials
3. Test in production
4. Monitor delivery rates

**You're all set!** 🎉

---

**Last Updated:** October 10, 2025  
**Version:** 1.0
