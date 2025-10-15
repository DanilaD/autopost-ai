# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added

#### UI Consistency & Dark Mode Improvements (2025-10-15)

- Fixed dark mode contrast issues across all components
- Standardized UI components to use consistent Tailwind classes
- Replaced Material Design 3 tokens with standard Tailwind equivalents
- Improved welcome card dark mode visibility with proper text contrast
- Updated all form components (buttons, inputs, checkboxes) for consistent styling
- Enhanced layout components (AuthLayout, GuestLayout) with proper dark mode support

#### Code Quality & Testing Improvements (2025-10-15)

- Fixed Husky deprecation warnings by removing deprecated lines from pre-commit/pre-push hooks
- Converted PHPUnit doc-comment metadata to PHP 8 attributes in all test files
- Resolved "integer expression expected" warnings in pre-commit-check.sh script
- Improved script reliability with proper empty result handling

#### CI Alignment (2025-10-15)

- GitHub Actions workflow `Laravel CI` updated to run local quality gates in CI
- Runs `scripts/pre-commit-check.sh` (Pint, ESLint, tests, docs/i18n/timezone/architecture)
- PHP 8.2 + Node 20 + SQLite test DB; Composer and npm caching enabled

#### Development Tools (2025-10-09)

- Git hooks with Husky for code quality enforcement
- Pre-commit hook with lint-staged for automatic code formatting
- Pre-push hook that runs full test suite
- ESLint 9 with Vue plugin for JavaScript/Vue linting
- Prettier for consistent code formatting
- Integration with Laravel Pint for PHP formatting
- VS Code recommended extensions and settings

#### Multi-Language Support (2025-10-09)

- Laravel localization package integration
- Support for 3 languages: English (en), Russian (ru), Spanish (es)
- Backend translation files for authentication
- SetLocale middleware for URL-based language detection
- Vue I18n integration with Composition API
- Frontend translation support
- Language detection from URL path (e.g., `/en/`, `/ru/`, `/es/`)
- Fallback to English for invalid locales

#### Documentation (2025-10-09)

- Comprehensive project documentation (73,000+ words)
- Coding standards with clean architecture guidelines
- Testing guide with factory usage patterns
- Code quality setup instructions
- GitHub PR automation workflows
- Implementation log for tracking progress

#### Configuration (2025-10-09)

- `.env.testing` file for test environment
- ESLint flat config for modern linting
- Prettier configuration
- EditorConfig for team consistency
- Updated `.gitignore` with linting cache entries

### Changed

- Updated `bootstrap/app.php` to register SetLocale middleware
- Enhanced `resources/js/app.js` with Vue I18n configuration
- Modified `.gitignore` to allow `.env.testing` and VS Code configs
- Updated `package.json` with new scripts and lint-staged configuration

### Fixed

- Added missing `vue-eslint-parser` dependency
- Generated APP_KEY for `.env.testing` to fix failing tests
- Configured ESLint 9 with flat config format

### Dependencies Added

#### PHP

- `mcamara/laravel-localization: ^2.3`

#### Node.js

- `husky: ^9.1.7`
- `lint-staged: ^16.2.3`
- `eslint: ^9.37.0`
- `eslint-plugin-vue: ^10.5.0`
- `@vue/eslint-config-prettier: ^10.2.0`
- `prettier: ^3.6.2`
- `vue-eslint-parser` (peer dependency)
- `vue-i18n: ^9.x`

## [0.1.0] - 2025-10-09

### Added

- Initial project setup with Laravel 12 + Vue 3 + Inertia.js
- Laravel Breeze authentication scaffolding
- Tailwind CSS integration
- Basic test suite (25 tests)

---

## Version History

- **[Unreleased]** - Phase 0 development (Authentication Foundation)
- **[0.1.0]** - 2025-10-09 - Initial project structure

## Migration Notes

### From Scratch to Current Version

If you're setting up the project:

1. Install all dependencies: `composer install && npm install`
2. Copy `.env.example` to `.env` and generate key
3. Run migrations: `php artisan migrate`
4. Build assets: `npm run build`
5. Initialize git hooks: `npx husky install`

### Breaking Changes

None yet - project in initial development.

## Upcoming Changes

See [IMPLEMENTATION_LOG.md](docs/IMPLEMENTATION_LOG.md) for planned features in Phase 0:

- Role-based access control (admin, user, network)
- Company management with multi-user support
- Email-first authentication with magic links
- Inquiry tracking for marketing
- Test seeders for development

---

**Note:** This project follows semantic versioning. Until version 1.0.0, breaking changes may occur in minor versions.
