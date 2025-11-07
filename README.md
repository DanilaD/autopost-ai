# AI-Powered Instagram Content Platform

A modern SaaS application built with Laravel 12, Inertia.js, and Vue 3 for automated social media posting powered by AI.

## 🚀 Tech Stack

### Backend

- **Laravel 12** - Latest PHP framework with modern features
- **PHP 8.2+** - Latest PHP version with performance improvements
- **SQLite** - Lightweight database for development
- **Inertia.js** - Modern monolith (SPA without building an API)
- **Laravel Sanctum** - API authentication for SPAs and mobile apps
- **Laravel Cashier** - Stripe payment integration for subscriptions
- **Laravel Socialite** - OAuth authentication (Google, Facebook, etc.)
- **Spatie Webhooks** - Webhook client & server for integrations

### Frontend

- **Vue 3** - Progressive JavaScript framework with Composition API
- **Tailwind CSS 3** - Utility-first CSS framework
- **Vite 7** - Next-generation frontend tooling (fast HMR)
- **Axios** - Promise-based HTTP client
- **Ziggy** - Use Laravel routes in JavaScript

### Development Tools

- **Laravel Breeze** - Minimal authentication scaffolding with Inertia
- **Laravel Sail** - Docker development environment
- **Laravel Pint** - Opinionated PHP code style fixer
- **Laravel Pail** - Real-time log viewer
- **Pest** - Elegant PHP testing framework
- **Concurrently** - Run multiple dev processes simultaneously

## ✨ Features

### ✅ Implemented

- 🔐 **Email-First Authentication** - Beautiful single-page auth flow with inline register/login forms
- 🌍 **Multi-Language Support** - English, Russian, Spanish (i18n ready)
- 👥 **Role-Based Access Control** - Admin, User, Network roles with company management
- 📊 **Inquiry Tracking** - Marketing intelligence for non-existent email submissions
- 🎨 **Modern UI** - Beautiful gradient design with Tailwind CSS
- ⚡ **SPA Experience** - Smooth navigation without full page reloads (Inertia.js)
- 🧪 **Testing** - Comprehensive test suite (294/294 passing)
- 🔧 **Developer Tools** - Auto-formatting (ESLint, Prettier, Pint) with git hooks
- 🏢 **Company Management** - Multi-company support with owner/member roles

### 🚧 Planned

- 💳 **Payments** - Stripe integration for wallet top-ups
- 📸 **Instagram Integration** - Publish posts, reels, stories via Graph API
- 🤖 **AI Content Generation** - OpenAI for captions, Stability AI for images, Luma for videos
- 📅 **Content Calendar** - Schedule and manage posts
- 🪝 **Webhooks** - Instagram & Stripe webhook handlers
- 📱 **API** - REST API with Sanctum authentication

## 📋 Requirements

- **PHP** >= 8.2
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **npm** >= 9.x or **yarn** >= 1.22
- **SQLite** (or MySQL/PostgreSQL for production)

## 🛠️ Installation

### Quick Setup (Automated)

```bash
# Clone the repository
git clone <your-repo-url> autopost-ai
cd autopost-ai

# Run automated setup
composer setup
```

This will:

- Install PHP dependencies
- Create `.env` file from `.env.example`
- Generate application key
- Run database migrations
- Install Node.js dependencies
- Build frontend assets

### Manual Setup

```bash
# 1. Install PHP dependencies
composer install

# 2. Create environment file
cp .env.example .env

# 3. Generate application key
php artisan key:generate

# 4. Configure database in .env
# For SQLite (default):
# DB_CONNECTION=sqlite
# DB_DATABASE=/absolute/path/to/database/database.sqlite

# 5. Create SQLite database (if using SQLite)
touch database/database.sqlite

# 6. Run migrations
php artisan migrate

# 7. Install Node.js dependencies
npm install

# 8. Build frontend assets
npm run build
```

## 🚀 Development

### Running the Application

**Option 1: All-in-one development server**

```bash
# Runs Laravel server, queue worker, logs, and Vite simultaneously
composer dev
```

This starts:

- 🌐 **Laravel Server** - `http://localhost:8000`
- 🔄 **Queue Worker** - Processes background jobs
- 📝 **Log Viewer (Pail)** - Real-time log monitoring
- ⚡ **Vite Dev Server** - Hot module replacement

**Option 2: Individual processes**

```bash
# Terminal 1: Start Laravel development server
php artisan serve

# Terminal 2: Start Vite dev server (HMR)
npm run dev

# Terminal 3 (optional): Queue worker
php artisan queue:listen

# Terminal 4 (optional): Log viewer
php artisan pail
```

### Building for Production

```bash
# Build optimized assets
npm run build

# Optimize Laravel
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🧪 Testing

```bash
# Run all tests
composer test

# Or directly with Pest
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run with coverage
php artisan test --coverage
```

## 📁 Project Structure

**Architecture:** Clean layered architecture (see [CODING_STANDARDS.md](./docs/CODING_STANDARDS.md))

```
autopost-ai/
├── app/
│   ├── Http/
│   │   ├── Controllers/     # HTTP only, NO business logic
│   │   ├── Requests/        # Form validation
│   │   └── Middleware/      # HTTP middleware
│   ├── Models/              # Relationships & casts ONLY
│   ├── Enums/               # Constants & status values
│   ├── Services/            # ALL business logic
│   ├── Repositories/        # ALL database queries
│   └── Providers/           # Service bindings
├── resources/
│   ├── js/
│   │   ├── Components/      # Vue 3 components
│   │   ├── Pages/           # Inertia page components
│   │   ├── Layouts/         # Layout components
│   │   └── app.js           # JavaScript entry point
│   ├── css/
│   │   └── app.css          # Tailwind CSS styles
│   └── views/
│       └── app.blade.php    # Main Inertia view
├── routes/
│   ├── web.php              # Web routes
│   ├── api.php              # API routes
│   └── console.php          # Artisan commands
├── database/
│   ├── migrations/          # Database migrations
│   ├── factories/           # Model factories
│   └── seeders/             # Database seeders
├── tests/
│   ├── Feature/             # Feature tests
│   └── Unit/                # Unit tests
├── public/
│   └── build/               # Compiled assets (gitignored)
└── storage/                 # Application storage (gitignored)

## 🔧 Scripts

Reusable helper scripts live under `scripts/` and are also executed in CI:

- `scripts/update-ngrok-url.sh`: update local share URL in config
- `scripts/restore-local-config.sh`: restore local config from backups
- `scripts/share-app.sh`: helper to share app URL
- `scripts/check-instagram-config.sh`: validate required env/config for Instagram
- `scripts/install-git-hooks.sh`, `scripts/pre-commit-check.sh`: git hooks and comprehensive pre-commit checks (run locally and in CI)

Note: one-off cleanup scripts used during UI migration were removed after completion.

### CI Integration

- GitHub Actions workflow `.github/workflows/laravel.yml` runs `scripts/pre-commit-check.sh` on push/PR
- Environment: PHP 8.2, Node 20, SQLite test database
- Includes Pint, ESLint, tests, docs/i18n/timezone/architecture validations
```

## 🔧 Configuration

### Environment Variables

Key variables to configure in `.env`:

```env
# Application
APP_NAME="{{APP_NAME}}"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=sqlite

# Stripe (Cashier)
STRIPE_KEY=your-stripe-key
STRIPE_SECRET=your-stripe-secret
STRIPE_WEBHOOK_SECRET=your-webhook-secret

# OAuth (Socialite)
GOOGLE_CLIENT_ID=your-google-client-id
GOOGLE_CLIENT_SECRET=your-google-client-secret
GOOGLE_REDIRECT_URI="${APP_URL}/auth/google/callback"
```

### Webhooks

Configure webhook endpoints in `config/webhook-client.php` and `config/webhook-server.php`.

## 🏗️ Architecture Guidelines

**⚠️ MANDATORY:** Read [docs/CODING_STANDARDS.md](./docs/CODING_STANDARDS.md) before writing code!

### Clean Architecture Pattern

```
Request → Controller → Service → Repository → Model → Database
```

**Layer Rules:**

- **Controllers** - HTTP handling ONLY, NO business logic (< 50 lines/method)
- **Models** - Relationships & casts ONLY
- **Enums** - Use for ALL constants (PHP 8.2+ backed enums)
- **Services** - ALL business logic goes here
- **Repositories** - ALL database queries go here
- **Always** - Use dependency injection, type hints, documentation

### Naming Conventions

- **Controllers** - `PostController`
- **Models** - `WalletTransaction`
- **Services** - `PostPublishService`
- **Repositories** - `PostRepository`
- **Enums** - `PostStatus`
- **Methods** - `getCompanyPosts()`, `createPost()`, `isPublished()`

### Code Quality Requirements

- **PSR-12 Compliance** - Run `./vendor/bin/pint` before commits
- **Type Hints** - Always use on parameters and return types
- **Documentation** - PHPDoc on all public methods
- **Tests** - Minimum 80% coverage for services
- **Consistency** - Use same patterns everywhere

**See full guidelines:** [docs/CODING_STANDARDS.md](./docs/CODING_STANDARDS.md)

## 🤝 Development Workflow

### Before Starting a Feature

1. Create a new branch: `git checkout -b feature/your-feature-name`
2. Plan your approach (models, services, routes)
3. Write tests first (TDD approach)

### During Development

1. Keep commits atomic and focused
2. Run tests frequently: `composer test`
3. Format code: `./vendor/bin/pint`
4. Check for errors in browser console and Laravel logs

### Before Committing

```bash
# Format code
./vendor/bin/pint

# Run tests
php artisan test

# Check for issues
php artisan insights (if installed)

# Stage and commit
git add .
git commit -m "feat: descriptive commit message"
```

### Commit Message Format

Follow conventional commits:

- `feat:` - New feature
- `fix:` - Bug fix
- `refactor:` - Code refactoring
- `test:` - Adding tests
- `docs:` - Documentation updates
- `style:` - Code style changes
- `chore:` - Maintenance tasks

## 📚 Useful Commands

```bash
# Artisan commands
php artisan list                    # List all commands
php artisan make:model Post -mfs    # Model + migration, factory, seeder
php artisan make:controller PostController --resource
php artisan make:service PostService
php artisan make:test PostTest

# Database
php artisan migrate                 # Run migrations
php artisan migrate:fresh --seed    # Fresh database with seeders
php artisan db:seed                 # Run seeders

# Cache management
php artisan cache:clear             # Clear application cache
php artisan config:clear            # Clear config cache
php artisan route:clear             # Clear route cache
php artisan view:clear              # Clear compiled views

# Queue management
php artisan queue:work              # Process queue jobs
php artisan queue:failed            # List failed jobs
php artisan queue:retry all         # Retry all failed jobs

# Development
php artisan tinker                  # Interactive REPL
php artisan serve                   # Start dev server
php artisan pail                    # Watch logs in real-time

# Code quality
./vendor/bin/pint                   # Fix code style
./vendor/bin/pest                   # Run tests
```

## 🔐 Security

- Never commit `.env` files
- Use `php artisan key:generate` for new installations
- Keep dependencies updated: `composer update`, `npm update`
- Use HTTPS in production
- Enable CSRF protection (enabled by default)
- Validate all user input
- Use Sanctum for API authentication

## 📖 Documentation

### Project Documentation

**Start here for complete project information:**

- **[docs/CODING_STANDARDS.md](./docs/CODING_STANDARDS.md)** - ⚠️ **MANDATORY** Architecture & coding rules
- **[docs/CODE_QUALITY_SETUP.md](./docs/CODE_QUALITY_SETUP.md)** - 🔍 Linting & pre-push checks
- **[docs/GITHUB_PR_AUTOMATION.md](./docs/GITHUB_PR_AUTOMATION.md)** - 🤖 CI/CD & automated PR checks
- **[docs/INDEX.md](./docs/INDEX.md)** - 📚 Documentation index and roadmap
- **[docs/PROJECT_PLAN.md](./docs/PROJECT_PLAN.md)** - 🗺️ Master implementation plan (8 phases)
- **[docs/DATABASE_SCHEMA.md](./docs/DATABASE_SCHEMA.md)** - 🗄️ Complete database design
- **[docs/AUTH_FLOW_PLAN.md](./docs/AUTH_FLOW_PLAN.md)** - 🔐 Authentication system (Phase 0)
- **[docs/INTERNATIONALIZATION_PLAN.md](./docs/INTERNATIONALIZATION_PLAN.md)** - 🌍 Multi-language support (Phase 0)

### External Documentation

- [Laravel Documentation](https://laravel.com/docs/12.x)
- [Inertia.js Documentation](https://inertiajs.com)
- [Vue 3 Documentation](https://vuejs.org)
- [Tailwind CSS Documentation](https://tailwindcss.com)
- [Pest Documentation](https://pestphp.com)
- [Laravel Cashier Stripe](https://laravel.com/docs/12.x/cashier-stripe)
- [Spatie Webhooks](https://github.com/spatie/laravel-webhook-server)

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

Built with these amazing open-source projects:

- [Laravel](https://laravel.com)
- [Inertia.js](https://inertiajs.com)
- [Vue.js](https://vuejs.org)
- [Tailwind CSS](https://tailwindcss.com)
- [Vite](https://vitejs.dev)

---

**Made with ❤️ by your development team**
