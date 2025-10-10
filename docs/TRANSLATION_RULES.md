# Translation Rules - Autopost AI

## 🌍 Core Rule: ALWAYS 3 Languages

**EVERY text, message, label, button, or content MUST be translated to:**

1. **English** (en) - Default
2. **Russian** (ru)
3. **Spanish** (es)

**NO EXCEPTIONS!**

---

## 📋 What Needs Translation

### ✅ Everything User-Facing:

- **UI Elements:**
    - Buttons
    - Labels
    - Placeholders
    - Titles
    - Headers
    - Navigation menus
    - Breadcrumbs

- **Messages:**
    - Success notifications
    - Error messages
    - Warning alerts
    - Info toasts
    - Validation errors
    - Confirmation prompts

- **Content:**
    - Page titles
    - Descriptions
    - Help text
    - Empty states
    - Tooltips
    - Modal content

- **Forms:**
    - Field labels
    - Submit buttons
    - Validation messages
    - Hints
    - Required field markers

- **Emails:**
    - Subject lines
    - Body content
    - Greetings
    - Signatures
    - Action buttons

---

## 🏗️ Implementation Structure

### Backend (Laravel)

```php
// File: lang/en/example.php
<?php
return [
    'welcome' => 'Welcome',
    'logout' => 'Logout',
];

// File: lang/ru/example.php
<?php
return [
    'welcome' => 'Добро пожаловать',
    'logout' => 'Выйти',
];

// File: lang/es/example.php
<?php
return [
    'welcome' => 'Bienvenido',
    'logout' => 'Cerrar sesión',
];
```

**Usage:**

```php
__('example.welcome')
```

### Frontend (Vue.js + Vue I18n)

```javascript
// File: resources/js/app.js
const messages = {
    en: {
        example: {
            welcome: 'Welcome',
            logout: 'Logout',
        },
    },
    ru: {
        example: {
            welcome: 'Добро пожаловать',
            logout: 'Выйти',
        },
    },
    es: {
        example: {
            welcome: 'Bienvenido',
            logout: 'Cerrar sesión',
        },
    },
}
```

**Usage:**

```vue
<template>
    <button>{{ t('example.logout') }}</button>
</template>

<script setup>
import { useI18n } from 'vue-i18n'
const { t } = useI18n()
</script>
```

---

## 📁 File Organization

### Backend Translation Files:

```
lang/
├── en/
│   ├── auth.php          # Authentication messages
│   ├── dashboard.php     # Dashboard content
│   ├── validation.php    # Validation errors
│   └── ...
├── ru/
│   ├── auth.php
│   ├── dashboard.php
│   ├── validation.php
│   └── ...
└── es/
    ├── auth.php
    ├── dashboard.php
    ├── validation.php
    └── ...
```

### Frontend Translations:

- **Centralized in:** `resources/js/app.js`
- **Alternative:** Separate JSON files (if project grows large)

---

## ✍️ Translation Workflow

### When Adding New Feature:

1. **Identify all text**
    - Find every string that users will see
    - Include buttons, labels, messages, etc.

2. **Create translation keys**
    - Use descriptive, hierarchical keys
    - Example: `dashboard.stats.instagram_accounts`

3. **Add to ALL 3 languages**
    - English (write first)
    - Russian (translate)
    - Spanish (translate)

4. **Update both backend AND frontend**
    - Add to PHP files (`lang/`)
    - Add to JavaScript (`app.js`)

5. **Test in all languages**
    - Switch language selector
    - Verify all text displays correctly
    - Check for missing translations

---

## 🎯 Best Practices

### Key Naming Conventions:

```php
// ✅ GOOD - Descriptive and hierarchical
'dashboard.greeting.morning' => 'Good morning'
'form.validation.email_required' => 'Email is required'
'button.save' => 'Save'

// ❌ BAD - Too generic or unclear
'msg1' => 'Good morning'
'error' => 'Email is required'
'btn' => 'Save'
```

### Grouping by Feature:

```php
// Group related translations together
'dashboard' => [
    'title' => 'Dashboard',
    'greeting' => [...],
    'stats' => [...],
    'actions' => [...],
]
```

### Consistent Terminology:

| Feature   | English   | Russian           | Spanish          |
| --------- | --------- | ----------------- | ---------------- |
| Login     | Login     | Войти             | Iniciar sesión   |
| Logout    | Logout    | Выйти             | Cerrar sesión    |
| Dashboard | Dashboard | Панель управления | Panel de control |
| Settings  | Settings  | Настройки         | Configuración    |

---

## 🔍 Quality Checks

### Before Committing:

- [ ] All 3 languages have translations for new keys
- [ ] No hardcoded strings in templates
- [ ] Translations tested in UI
- [ ] Professional/natural language (not machine-translated feel)
- [ ] Consistent tone across all languages

### Testing Checklist:

```bash
# 1. Switch to English
- All text appears correctly
- No missing translations
- Proper grammar and spelling

# 2. Switch to Russian
- All text appears correctly
- Cyrillic characters display properly
- Natural Russian phrasing

# 3. Switch to Spanish
- All text appears correctly
- Accents display properly (á, é, í, ó, ú, ñ)
- Natural Spanish phrasing
```

---

## 🚨 Common Mistakes to Avoid

### ❌ Don't Do This:

```vue
<template>
    <!-- BAD: Hardcoded text -->
    <button>Login</button>

    <!-- BAD: Only English translation -->
    <button>{{ t('auth.login') }}</button>
    <!-- But missing 'auth.login' in RU and ES files -->
</template>
```

### ✅ Do This:

```vue
<template>
    <!-- GOOD: Using translation key -->
    <button>{{ t('auth.login') }}</button>
</template>
```

```php
// GOOD: All 3 languages defined
// en/auth.php
'login' => 'Login'

// ru/auth.php
'login' => 'Войти'

// es/auth.php
'login' => 'Iniciar sesión'
```

---

## 📧 Email Translations

### Special Rules for Emails:

```php
// Email notifications must be translated too!

// en/mail.php
'greeting' => 'Hello, :name!',
'action' => 'Click here',

// ru/mail.php
'greeting' => 'Здравствуйте, :name!',
'action' => 'Нажмите здесь',

// es/mail.php
'greeting' => '¡Hola, :name!',
'action' => 'Haga clic aquí',
```

### Email Templates:

- Subject line → Translated
- Body content → Translated
- Button text → Translated
- Footer → Translated

---

## 🔄 Migration Path

### When Updating Existing Features:

1. Find all hardcoded strings
2. Create translation keys
3. Add to all 3 language files
4. Replace hardcoded strings with translation calls
5. Test in all languages

---

## 📊 Translation Coverage Report

### How to Check Coverage:

```bash
# Count translation keys per language
grep -r "=>" lang/en/*.php | wc -l
grep -r "=>" lang/ru/*.php | wc -l
grep -r "=>" lang/es/*.php | wc -l

# All three should be equal!
```

---

## 🎓 Translation Guidelines

### Tone & Style:

- **Professional but friendly**
- **Clear and concise**
- **Action-oriented for buttons**
- **Helpful for error messages**

### Examples:

| Context | English              | Russian             | Spanish               |
| ------- | -------------------- | ------------------- | --------------------- |
| Welcome | Welcome back!        | С возвращением!     | ¡Bienvenido de nuevo! |
| Error   | Something went wrong | Что-то пошло не так | Algo salió mal        |
| Success | Saved successfully   | Успешно сохранено   | Guardado exitosamente |
| Action  | Create Post          | Создать публикацию  | Crear publicación     |

---

## 🛠️ Tools & Resources

### Translation Helpers:

- **DeepL** - High-quality translations
- **Google Translate** - Quick reference
- **Native speakers** - Final review (best!)

### Testing:

- **Browser DevTools** - Check i18n messages
- **Language switcher** - Manual testing
- **Automated tests** - Verify key existence

---

## 📝 Checklist for Every Feature

When adding ANY new feature:

- [ ] Identify all user-facing text
- [ ] Create descriptive translation keys
- [ ] Add English translations
- [ ] Add Russian translations
- [ ] Add Spanish translations
- [ ] Update frontend translations (if needed)
- [ ] Test in all 3 languages
- [ ] Verify no hardcoded strings remain
- [ ] Check for proper character rendering (Cyrillic, accents)
- [ ] Ensure natural phrasing in all languages

---

## 🎯 Summary

### The Golden Rule:

> **If a user can see it, it MUST be in 3 languages: EN, RU, ES**

### Zero Tolerance:

- No hardcoded strings
- No missing translations
- No "TODO: translate later"
- No shortcuts

### Quality Over Speed:

- Take time to translate properly
- Use natural language, not literal translations
- Get native speaker review when possible
- Professional terminology throughout

---

## 🚀 Current Translation Status

| Component      | EN  | RU  | ES  | Status   |
| -------------- | --- | --- | --- | -------- |
| Authentication | ✅  | ✅  | ✅  | Complete |
| Dashboard      | ✅  | ✅  | ✅  | Complete |
| Profile        | ⏳  | ⏳  | ⏳  | Pending  |
| Instagram      | ⏳  | ⏳  | ⏳  | Pending  |
| Emails         | ⏳  | ⏳  | ⏳  | Pending  |

---

**Remember: Every commit with user-facing text MUST include all 3 languages!** 🌍✨
