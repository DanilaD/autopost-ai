# Contributing to Autopost AI

Thank you for contributing to Autopost AI! This guide will help you get started.

## Before You Start

### Required Reading

1. **[CODING_STANDARDS.md](./docs/CODING_STANDARDS.md)** - ⚠️ **MANDATORY**
2. [PROJECT_PLAN.md](./docs/PROJECT_PLAN.md) - Understand the architecture
3. [TESTING_GUIDE.md](./docs/TESTING_GUIDE.md) - Learn testing practices

### Setup Your Environment

Follow [PROJECT_SETUP_CHECKLIST.md](./docs/PROJECT_SETUP_CHECKLIST.md)

## Development Workflow

### 1. Create Feature Branch

```bash
git checkout main
git pull origin main
git checkout -b feature/your-feature-name
```

### 2. Follow TDD (Test-Driven Development)

```bash
# 1. Write test first
# tests/Feature/Post/CreatePostTest.php

# 2. Run test (should fail)
php artisan test

# 3. Implement feature
# app/Services/Post/PostService.php

# 4. Run test (should pass)
php artisan test
```

### 3. Commit Your Changes

```bash
# Format code
./vendor/bin/pint
npm run lint

# Run tests
php artisan test

# Commit with conventional commit message
git add .
git commit -m "feat(posts): add carousel post support"
```

**Commit Message Format:**

```
<type>(<scope>): <subject>

feat: New feature
fix: Bug fix
docs: Documentation
refactor: Code refactoring
test: Tests
chore: Build/tooling
```

### 4. Push and Create PR

```bash
git push origin feature/your-feature-name
```

Then create Pull Request on GitHub.

## Code Standards

### MUST Follow

- ✅ Controllers: HTTP only, NO business logic
- ✅ Services: ALL business logic
- ✅ Repositories: ALL database queries
- ✅ Models: Relationships only
- ✅ Always use type hints
- ✅ Always write PHPDoc
- ✅ Always write tests (80% coverage minimum)

### Architecture Pattern

```
Controller → Service → Repository → Model → Database
```

### Naming Conventions

- Controllers: `PostController`
- Services: `PostService`
- Repositories: `PostRepository`
- Models: `Post`
- Factories: `PostFactory`
- Tests: `CreatePostTest`

## Pull Request Process

### PR Checklist

- [ ] Tests pass locally
- [ ] Code formatted (`./vendor/bin/pint` + `npm run lint`)
- [ ] Coverage ≥ 80%
- [ ] Documentation updated
- [ ] No console.log() left in code
- [ ] Follows coding standards
- [ ] Descriptive PR title

### PR Requirements

- 2 approvals required
- All CI checks must pass
- No merge conflicts
- Branch up-to-date with main

### What Happens Next

1. Automated checks run (tests, linting, security)
2. Code review by team
3. Address feedback
4. Approval from 2+ reviewers
5. Squash and merge to main
6. Auto-deploy to staging

## Testing

### Always Use Factories

```php
// ✅ GOOD
$post = Post::factory()->create();

// ❌ BAD
$this->seed(PostSeeder::class);
```

### Test Structure

```php
it('creates post with valid data', function () {
    // Arrange
    $user = User::factory()->create();

    // Act
    $response = $this->actingAs($user)->post('/posts', $data);

    // Assert
    $response->assertCreated();
    expect(Post::count())->toBe(1);
});
```

## Database Changes

### When Creating Migration

**MUST also create:**

1. ✅ Model
2. ✅ Factory
3. ✅ (Optional) Seeder (for development only)

```bash
# Create all at once
php artisan make:model Post -mf

# Or separately
php artisan make:model Post
php artisan make:migration create_posts_table
php artisan make:factory PostFactory --model=Post
```

## Common Issues

### Hooks Not Running

```bash
chmod +x .husky/pre-commit
chmod +x .husky/pre-push
```

### Tests Failing

```bash
# Clear cache
php artisan config:clear
php artisan cache:clear

# Re-run migrations
php artisan migrate:fresh --env=testing
```

### Linting Errors

```bash
# Auto-fix
./vendor/bin/pint
npm run lint
```

## Getting Help

- 💬 Ask in team Slack/Discord
- 📖 Read documentation in `/docs`
- 🐛 Create issue on GitHub
- 👥 Request code review

## Code of Conduct

- Be respectful and inclusive
- Provide constructive feedback
- Help others learn
- Follow team guidelines
- Keep discussions professional

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
