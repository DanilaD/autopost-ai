# Getting Started with Autopost AI

**Welcome to the team!** 🎉

This guide will help you get up and running in 30 minutes.

---

## 🚀 Quick Start (30 Minutes)

### Step 1: Clone & Install (5 minutes)

```bash
# Clone repository
git clone <repository-url> autopost-ai
cd autopost-ai

# Install dependencies
composer install
npm install
```

### Step 2: Environment Setup (5 minutes)

```bash
# Create environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create database
touch database/database.sqlite

# Run migrations
php artisan migrate
```

### Step 3: Setup Git Hooks (2 minutes)

```bash
# Initialize Husky
npx husky install

# Hooks are now active!
```

### Step 4: Build Assets (3 minutes)

```bash
# Build frontend
npm run build

# Or start dev server
npm run dev
```

### Step 5: Run Tests (2 minutes)

```bash
# Verify everything works
php artisan test

# Should see: ✓ Tests passed
```

### Step 6: Start Development Server (1 minute)

```bash
# Start all services (server + queue + logs + vite)
composer dev
```

Visit: http://localhost:8000

### Step 7: Read Core Documentation (15 minutes)

**MANDATORY:**

1. [CODING_STANDARDS.md](./CODING_STANDARDS.md) - **READ THIS FIRST!**
2. [TESTING_GUIDE.md](./TESTING_GUIDE.md) - Testing practices
3. [CONTRIBUTING.md](../CONTRIBUTING.md) - How to contribute

---

## 📚 Complete Documentation

### Core Architecture & Standards

1. **[CODING_STANDARDS.md](./CODING_STANDARDS.md)** ⚠️ **MANDATORY**
    - Clean architecture pattern
    - Layer responsibilities
    - Naming conventions
    - Best practices
    - Complete code examples

2. **[DATABASE_SCHEMA.md](./DATABASE_SCHEMA.md)**
    - Complete database design
    - All tables with relationships
    - Indexes and optimization
    - Sample queries

3. **[PROJECT_PLAN.md](./PROJECT_PLAN.md)**
    - Master implementation plan
    - 8 phases with roadmap
    - API integrations
    - MVP acceptance criteria

### Feature Implementation

4. **[AUTH_FLOW_PLAN.md](./AUTH_FLOW_PLAN.md)**
    - Email-first authentication
    - Magic link (passwordless)
    - Email verification
    - Inquiry tracking

5. **[INTERNATIONALIZATION_PLAN.md](./INTERNATIONALIZATION_PLAN.md)**
    - Multi-language support (EN, RU, ES)
    - URL structure
    - Language detection
    - Translation files

### Code Quality & Testing

6. **[CODE_QUALITY_SETUP.md](./CODE_QUALITY_SETUP.md)**
    - Git hooks (pre-commit, pre-push)
    - Linting (PHP, JavaScript, Vue)
    - Static analysis (PHPStan)
    - Editor integration

7. **[TESTING_GUIDE.md](./TESTING_GUIDE.md)**
    - Testing philosophy
    - Factory usage (mandatory)
    - Test coverage requirements
    - Mocking external services

### CI/CD & Release

8. **[GITHUB_PR_AUTOMATION.md](./GITHUB_PR_AUTOMATION.md)**
    - GitHub Actions workflows
    - Automated code review
    - Security scanning
    - Branch protection rules

9. **[RELEASE_MANAGEMENT.md](./RELEASE_MANAGEMENT.md)**
    - Git branching strategy
    - PR workflow
    - Semantic versioning
    - Deployment pipeline

### Setup Guides

10. **[PROJECT_SETUP_CHECKLIST.md](./PROJECT_SETUP_CHECKLIST.md)**
    - Complete setup checklist
    - Configuration files
    - External services
    - Team onboarding

11. **[INDEX.md](./INDEX.md)**
    - Documentation index
    - Implementation roadmap
    - Dependencies list

---

## 🎯 Your First Task

### Create Your First Feature

**1. Read the standards:**

```bash
# Open in your editor
code docs/CODING_STANDARDS.md
```

**2. Create a feature branch:**

```bash
git checkout -b feature/your-name-test-feature
```

**3. Make a small change:**

```bash
# For example, add a comment to User model
# app/Models/User.php

/**
 * Test comment by [Your Name]
 */
```

**4. Commit (tests git hooks):**

```bash
git add .
git commit -m "test: verify git hooks work"
```

**5. Push and create PR:**

```bash
git push origin feature/your-name-test-feature
```

Then create a Pull Request on GitHub.

**6. Observe:**

- ✅ Pre-commit hook formats code
- ✅ GitHub Actions run tests
- ✅ Automated checks complete
- ✅ Ready for review!

---

## 🛠️ Development Workflow

### Daily Workflow

```bash
# 1. Update main
git checkout main
git pull origin main

# 2. Create feature branch
git checkout -b feature/add-new-feature

# 3. Write test first (TDD)
# tests/Feature/NewFeatureTest.php

# 4. Run test (should fail)
php artisan test

# 5. Implement feature
# app/Services/NewFeatureService.php

# 6. Run test (should pass)
php artisan test

# 7. Format code
./vendor/bin/pint
npm run lint

# 8. Commit
git commit -m "feat(scope): description"

# 9. Push and create PR
git push origin feature/add-new-feature
```

### Architecture Pattern

**ALWAYS follow this flow:**

```
Route → Controller → Service → Repository → Model → Database
```

**Rules:**

- ✅ Controllers: HTTP only, NO business logic
- ✅ Services: ALL business logic
- ✅ Repositories: ALL database queries
- ✅ Models: Relationships ONLY

---

## 🧪 Testing Rules

### MANDATORY: Create Factory with Every Model

```bash
# When creating a model, ALWAYS create factory
php artisan make:model Post -mf

# This creates:
# - Model: app/Models/Post.php
# - Migration: database/migrations/xxx_create_posts_table.php
# - Factory: database/factories/PostFactory.php
```

### ALWAYS Use Factories in Tests

```php
// ✅ GOOD - Use factories
it('lists posts', function () {
    Post::factory()->count(10)->create();
    // test...
});

// ❌ BAD - Never use seeders in tests
it('lists posts', function () {
    $this->seed(PostSeeder::class);  // ❌ NO!
    // test...
});
```

---

## 📋 Pre-Commit Checklist

Before every commit, these run automatically:

- ✅ PHP code formatted (Pint)
- ✅ JavaScript/Vue code formatted (ESLint + Prettier)
- ✅ PHP syntax checked
- ✅ (On push) Tests run

**Manual checks:**

```bash
# Format code
./vendor/bin/pint
npm run lint

# Run tests
php artisan test --coverage

# Check coverage meets 80%
```

---

## 🚨 Common Issues

### "Class not found"

```bash
composer dump-autoload
```

### "Mix manifest not found"

```bash
npm run build
```

### "Tests failing"

```bash
php artisan config:clear
php artisan migrate:fresh --env=testing
php artisan test
```

### "Git hooks not running"

```bash
chmod +x .husky/pre-commit
chmod +x .husky/pre-push
```

---

## 💡 Useful Commands

### Development

```bash
# Start all services
composer dev

# Individual services
php artisan serve           # Laravel server
npm run dev                 # Vite dev server
php artisan queue:work      # Queue worker
php artisan pail            # Log viewer
```

### Testing

```bash
php artisan test                    # Run all tests
php artisan test --coverage         # With coverage
php artisan test --filter test_name # Specific test
```

### Code Quality

```bash
./vendor/bin/pint          # Format PHP
npm run lint               # Lint JavaScript/Vue
./vendor/bin/phpstan analyse # Static analysis
```

### Database

```bash
php artisan migrate                # Run migrations
php artisan migrate:fresh --seed   # Fresh DB with data
php artisan db:seed                # Run seeders
```

---

## 🤝 Getting Help

### Resources

- 📖 Read `/docs` - Complete documentation
- 💬 Ask in team chat - We're here to help!
- 🐛 Create issue - For bugs or questions
- 👥 Request review - On your PR

### Key People

- **Architecture questions** → Senior developers
- **Testing help** → QA team
- **Infrastructure** → DevOps team
- **Design** → UI/UX team

---

## 🎓 Learning Path

### Week 1: Foundations

- [ ] Complete environment setup
- [ ] Read CODING_STANDARDS.md
- [ ] Create first test PR
- [ ] Review existing code
- [ ] Understand architecture

### Week 2: First Feature

- [ ] Pick small task from backlog
- [ ] Write tests first (TDD)
- [ ] Implement feature
- [ ] Create PR
- [ ] Address review feedback

### Week 3: Advanced

- [ ] Work on complex feature
- [ ] Help review others' PRs
- [ ] Improve test coverage
- [ ] Optimize performance

---

## 🎉 You're Ready!

**Next steps:**

1. ✅ Environment setup complete
2. ✅ Documentation read
3. ✅ Git hooks working
4. ✅ Tests passing
5. 🚀 **Start building!**

**Remember:**

- Follow CODING_STANDARDS.md
- Write tests first (TDD)
- Ask questions early
- Code reviews are learning opportunities
- Have fun! 🎈

---

## 📞 Quick Links

- **Documentation:** [docs/INDEX.md](./INDEX.md)
- **Contributing:** [CONTRIBUTING.md](../CONTRIBUTING.md)
- **Project Plan:** [docs/PROJECT_PLAN.md](./PROJECT_PLAN.md)
- **Setup Checklist:** [docs/PROJECT_SETUP_CHECKLIST.md](./PROJECT_SETUP_CHECKLIST.md)

---

**Welcome aboard! Let's build something amazing together!** 🚀
