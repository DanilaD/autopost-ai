# MailHog/Mailpit Setup Guide

**Local email testing with MailHog or Mailpit**

---

## ✅ Current Status

**You're all set up!** ✨

- ✅ MailHog/Mailpit configured
- ✅ Emails sending successfully
- ✅ Web UI accessible at http://localhost:8025
- ✅ Password reset tested and working

---

## 🎯 What is MailHog/Mailpit?

**MailHog** and **Mailpit** are local email testing tools that:

- Catch all emails sent from your app
- Show them in a beautiful web UI
- Don't send real emails (perfect for development!)
- Let you test email templates visually

**Think of it as:** A fake email server that captures everything

---

## 📧 Your Configuration

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025              # SMTP port
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@autopost-ai.test"
MAIL_FROM_NAME="${APP_NAME}"
```

**Ports:**

- **1025** = SMTP port (for sending emails)
- **8025** = Web UI port (for viewing emails)

---

## 🚀 How to Use

### Start MailHog/Mailpit:

**If using MailHog:**

```bash
mailhog
```

**If using Mailpit:**

```bash
mailpit
```

**If installed via Docker:**

```bash
docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog
```

### View Emails:

Open your browser: **http://localhost:8025**

---

## 🧪 Test Email Features

### 1. Test Password Reset:

```bash
# Visit your app
open http://localhost:8000

# Click "Forgot password?"
# Enter: admin@autopost.ai
# Submit

# Check MailHog
open http://localhost:8025
```

**You'll see:**

- Beautiful HTML email
- Reset password link
- Exact same email users will receive in production!

### 2. Test from Command Line:

```bash
php artisan tinker
```

```php
use Illuminate\Support\Facades\Password;
use App\Models\User;

$user = User::first();
Password::sendResetLink(['email' => $user->email]);
```

Check http://localhost:8025 - Email is there!

### 3. Test Registration Email:

```bash
# Register a new user
# Check MailHog for welcome email (when you add it)
```

---

## 💡 Features

### MailHog Web UI:

- **Inbox** - See all captured emails
- **Preview** - View HTML and plain text versions
- **Source** - See raw email content
- **Delete** - Clear individual emails
- **Clear All** - Start fresh

### Search & Filter:

- Search by recipient
- Filter by sender
- View by date

---

## 🔧 Installation (If Not Installed)

### MailHog:

**macOS (Homebrew):**

```bash
brew install mailhog
mailhog
```

**Linux:**

```bash
# Download binary
wget https://github.com/mailhog/MailHog/releases/download/v1.0.1/MailHog_linux_amd64
chmod +x MailHog_linux_amd64
./MailHog_linux_amd64
```

**Windows:**
Download from: https://github.com/mailhog/MailHog/releases

---

### Mailpit (Modern Alternative):

**macOS (Homebrew):**

```bash
brew install mailpit
mailpit
```

**Or download from:**
https://github.com/axllent/mailpit/releases

---

## 🎨 Screenshot of What You'll See

When you open http://localhost:8025:

```
┌──────────────────────────────────────────┐
│  MailHog                                  │
├──────────────────────────────────────────┤
│  Inbox (1)                                │
│                                           │
│  📧 Password Reset Link                   │
│  From: hello@autopost-ai.test            │
│  To: admin@autopost.ai                   │
│  Subject: Reset Password Notification    │
│                                           │
│  [Click to view full email]               │
│                                           │
│  ─────────────────────────────────────   │
│                                           │
│  [Beautiful HTML preview appears here]   │
│                                           │
└──────────────────────────────────────────┘
```

---

## 🐛 Troubleshooting

### Error: "Connection refused"

**Problem:** MailHog/Mailpit not running

**Solution:**

```bash
# Check if running
lsof -i :1025
lsof -i :8025

# Start it
mailhog  # or mailpit
```

### Web UI Not Loading

**Problem:** Port 8025 in use

**Solution:**

```bash
# Check what's using port 8025
lsof -i :8025

# Kill it or use different port
mailhog -ui-bind-addr=:8026  # Use port 8026 instead
```

### Emails Not Appearing

**Problem:** Wrong SMTP port or config cache

**Solution:**

```bash
# Clear config cache
php artisan config:clear

# Check .env
cat .env | grep MAIL_

# Should be:
# MAIL_PORT=1025  (not 8025!)
```

---

## 🔄 Switch Between Environments

### Development (MailHog):

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
```

### Production (Real Email Service):

```env
MAIL_MAILER=resend  # or mailgun, sendgrid, etc.
MAIL_HOST=smtp.resend.com
MAIL_PORT=587
# ... other credentials
```

---

## ✅ Verification Checklist

- [ ] MailHog/Mailpit installed and running
- [ ] `.env` configured with MAIL_PORT=1025
- [ ] Config cache cleared
- [ ] Web UI accessible at http://localhost:8025
- [ ] Test email sent successfully
- [ ] Email visible in MailHog UI

---

## 📊 Comparison: MailHog vs Mailpit

| Feature     | MailHog | Mailpit    |
| ----------- | ------- | ---------- |
| **Speed**   | Good    | Faster     |
| **UI**      | Classic | Modern     |
| **Search**  | Basic   | Advanced   |
| **Storage** | Memory  | SQLite     |
| **Active**  | Stable  | Maintained |

**Recommendation:** Either works great! Mailpit is more modern.

---

## 🎯 Next Steps

**For Development:**

1. ✅ Keep using MailHog (you're set up!)
2. Test all email features
3. Perfect your email templates

**For Production:**

1. Follow `docs/EMAIL_SETUP.md`
2. Choose Resend or SendGrid
3. Update production `.env`
4. Test in staging first

---

## 💪 Pro Tips

1. **Keep MailHog Running:** Start it in background

    ```bash
    mailhog > /dev/null 2>&1 &
    ```

2. **Bookmark the UI:** http://localhost:8025

3. **Test Visually:** See exactly how emails look

4. **Share with Team:** Everyone can see test emails

5. **No Spam Filters:** Test without worrying about deliverability

---

## 🔗 Resources

- [MailHog GitHub](https://github.com/mailhog/MailHog)
- [Mailpit GitHub](https://github.com/axllent/mailpit)
- [Laravel Mail Docs](https://laravel.com/docs/mail)

---

## 📝 Summary

**What You Have:**

- ✅ MailHog/Mailpit running locally
- ✅ All emails captured and displayed
- ✅ Perfect for development testing
- ✅ Zero cost, works offline

**What You Can Do:**

- Test password resets
- View email templates
- Debug email issues
- Share previews with team

**Perfect for:** Development and testing!

---

**Last Updated:** October 10, 2025  
**Status:** ✅ Working Perfectly!
