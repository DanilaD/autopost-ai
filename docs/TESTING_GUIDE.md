# Testing Guide

**Version:** 1.0  
**Date:** October 9, 2025  
**Status:** Active - All developers must follow

---

## Table of Contents

1. [Testing Philosophy](#testing-philosophy)
2. [Test Environment Setup](#test-environment-setup)
3. [Database Seeding for Tests](#database-seeding-for-tests)
4. [Factories](#factories)
5. [Writing Tests](#writing-tests)
6. [Test Coverage Requirements](#test-coverage-requirements)
7. [Testing Best Practices](#testing-best-practices)
8. [Mocking External Services](#mocking-external-services)

---

## Testing Philosophy

### Our Approach

- ✅ **Test behavior, not implementation**
- ✅ **Use factories for test data**
- ✅ **Mock external APIs**
- ✅ **Fast tests (< 5 seconds total)**
- ✅ **Reliable tests (no flaky tests)**
- ✅ **Readable tests (clear test names)**

### Test Pyramid

```
         /\
        /  \
       / UI \       10% - End-to-End (browser tests)
      /______\
     /        \
    /  API     \    30% - Feature (HTTP tests)
   /____________\
  /              \
 /   Unit Tests   \ 60% - Unit (service/repository tests)
/__________________\
```

---

## Test Environment Setup

### 1. Test Environment File

**File:** `.env.testing`

**Key configurations:**

- Database: SQLite in-memory (`:memory:`)
- Cache: Array driver (no Redis needed)
- Queue: Sync (immediate execution)
- Mail: Array driver (emails stored in memory)
- Sessions: Array driver

**Why?**

- ✅ **Fast** - In-memory database
- ✅ **Isolated** - No external dependencies
- ✅ **Clean** - Fresh state for each test
- ✅ **Reliable** - No network calls

### 2. PHPUnit Configuration

**File:** `phpunit.xml`

Ensure these settings:

```xml
<phpunit>
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>

    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="DB_CONNECTION" value="sqlite"/>
        <env name="DB_DATABASE" value=":memory:"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="QUEUE_CONNECTION" value="sync"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="MAIL_MAILER" value="array"/>
    </php>
</phpunit>
```

### 3. Running Tests

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/Post/CreatePostTest.php

# Run specific test
php artisan test --filter test_user_can_create_post

# Run tests in parallel (faster)
php artisan test --parallel
```

---

## Database Seeding for Tests

### IMPORTANT RULE: Always Use Factories in Tests

**❌ DON'T use seeders in tests:**

```php
// BAD - Don't do this
it('lists posts', function () {
    $this->seed(PostSeeder::class);  // ❌ No!
    // ...
});
```

**✅ DO use factories:**

```php
// GOOD - Use factories
it('lists posts', function () {
    Post::factory()->count(10)->create();  // ✅ Yes!
    // ...
});
```

### Why Use Factories, Not Seeders?

**Factories:**

- ✅ Fast (no extra queries)
- ✅ Flexible (customize per test)
- ✅ Isolated (each test independent)
- ✅ Clear (what data is created)

**Seeders:**

- ❌ Slow (many queries)
- ❌ Rigid (fixed data)
- ❌ Coupled (tests depend on seeder)
- ❌ Unclear (what data exists?)

### When to Use Seeders

**Use seeders ONLY for:**

- ✅ Development database setup
- ✅ Staging environment data
- ✅ Production initial data (roles, permissions)

**Command:**

```bash
# Development only
php artisan db:seed

# Or specific seeder
php artisan db:seed --class=UserSeeder
```

---

## Factories

### MANDATORY RULE: Create Factory with Every Model

**When you create a migration, you MUST also create:**

1. ✅ Model
2. ✅ Factory
3. ✅ (Optional) Seeder (for development only)

### Creating Factory

```bash
# Create model, migration, and factory together
php artisan make:model Post -mf

# Or separately
php artisan make:factory PostFactory --model=Post
```

### Factory Structure

**File:** `database/factories/PostFactory.php`

```php
<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\InstagramAccount;
use App\Models\User;
use App\Enums\PostStatus;
use App\Enums\PostType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Post Factory
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'created_by' => User::factory(),
            'instagram_account_id' => InstagramAccount::factory(),
            'type' => fake()->randomElement([
                PostType::FEED,
                PostType::REEL,
                PostType::STORY,
            ]),
            'caption' => fake()->paragraph(),
            'scheduled_at' => fake()->dateTimeBetween('now', '+7 days'),
            'status' => PostStatus::DRAFT,
            'metadata' => [
                'hashtags' => ['#autopost', '#instagram'],
                'mentions' => [],
            ],
        ];
    }

    /**
     * Indicate the post is scheduled
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => now()->addHours(2),
        ]);
    }

    /**
     * Indicate the post is published
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::PUBLISHED,
            'published_at' => now()->subHours(2),
            'instagram_media_id' => fake()->numerify('###############'),
        ]);
    }

    /**
     * Indicate the post is a draft
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::DRAFT,
            'scheduled_at' => null,
        ]);
    }

    /**
     * With specific caption
     */
    public function withCaption(string $caption): static
    {
        return $this->state(fn (array $attributes) => [
            'caption' => $caption,
        ]);
    }
}
```

### Using Factories in Tests

**Basic usage:**

```php
// Create one post
$post = Post::factory()->create();

// Create multiple posts
$posts = Post::factory()->count(10)->create();

// Create with custom attributes
$post = Post::factory()->create([
    'caption' => 'Custom caption',
    'status' => PostStatus::PUBLISHED,
]);

// Use factory states
$scheduledPost = Post::factory()->scheduled()->create();
$publishedPost = Post::factory()->published()->create();
$draftPost = Post::factory()->draft()->create();
```

**With relationships:**

```php
// Create post with related models
$company = Company::factory()->create();
$user = User::factory()->create();
$instagramAccount = InstagramAccount::factory()->create([
    'company_id' => $company->id,
]);

$post = Post::factory()->create([
    'company_id' => $company->id,
    'created_by' => $user->id,
    'instagram_account_id' => $instagramAccount->id,
]);

// Or use factory relationships
$post = Post::factory()
    ->for(Company::factory())
    ->for(User::factory(), 'creator')
    ->for(InstagramAccount::factory())
    ->create();
```

**With nested relationships:**

```php
// Create post with assets
$post = Post::factory()
    ->has(PostAsset::factory()->count(3))
    ->create();

// Create company with posts
$company = Company::factory()
    ->has(Post::factory()->count(10))
    ->create();
```

### Factory Best Practices

1. **Use realistic fake data:**

```php
'email' => fake()->safeEmail(),  // ✅ user@example.com
'name' => fake()->name(),         // ✅ John Doe
'caption' => fake()->paragraph(), // ✅ Realistic text
```

2. **Create factory states for common scenarios:**

```php
// States for different post statuses
public function draft(): static { }
public function scheduled(): static { }
public function published(): static { }
public function failed(): static { }
```

3. **Use sequences for varied data:**

```php
$posts = Post::factory()
    ->count(5)
    ->sequence(
        ['type' => PostType::FEED],
        ['type' => PostType::REEL],
        ['type' => PostType::STORY],
    )
    ->create();
```

4. **Create helper methods:**

```php
public function forCompany(Company $company): static
{
    return $this->state(fn (array $attributes) => [
        'company_id' => $company->id,
    ]);
}

// Usage
$post = Post::factory()->forCompany($company)->create();
```

---

## Writing Tests

### Test Structure

```
tests/
├── Feature/          # HTTP tests (controllers, routes)
│   ├── Auth/
│   │   ├── LoginTest.php
│   │   ├── RegisterTest.php
│   │   └── MagicLinkTest.php
│   ├── Post/
│   │   ├── CreatePostTest.php
│   │   ├── UpdatePostTest.php
│   │   └── DeletePostTest.php
│   └── Wallet/
│       ├── TopUpTest.php
│       └── TransactionTest.php
└── Unit/             # Service/Repository tests
    ├── Services/
    │   ├── PostServiceTest.php
    │   ├── WalletServiceTest.php
    │   └── InstagramGraphServiceTest.php
    └── Repositories/
        ├── PostRepositoryTest.php
        └── WalletRepositoryTest.php
```

### Feature Test Example

**File:** `tests/Feature/Post/CreatePostTest.php`

```php
<?php

use App\Models\Company;
use App\Models\InstagramAccount;
use App\Models\User;
use App\Models\Post;

beforeEach(function () {
    // Setup runs before each test
    $this->user = User::factory()->create();
    $this->company = Company::factory()->create(['owner_id' => $this->user->id]);
    $this->instagramAccount = InstagramAccount::factory()->create([
        'company_id' => $this->company->id,
    ]);

    // Set current company for user
    $this->user->update(['current_company_id' => $this->company->id]);
});

it('creates a post with valid data', function () {
    $this->actingAs($this->user);

    $data = [
        'instagram_account_id' => $this->instagramAccount->id,
        'type' => 'feed',
        'caption' => 'Test caption',
        'scheduled_at' => now()->addDay()->toDateTimeString(),
    ];

    $response = $this->post('/posts', $data);

    $response->assertCreated();

    $this->assertDatabaseHas('posts', [
        'company_id' => $this->company->id,
        'created_by' => $this->user->id,
        'caption' => 'Test caption',
    ]);

    expect(Post::count())->toBe(1);
});

it('requires authentication to create post', function () {
    $response = $this->post('/posts', []);

    $response->assertRedirect('/login');
});

it('validates required fields', function () {
    $this->actingAs($this->user);

    $response = $this->post('/posts', []);

    $response->assertSessionHasErrors(['instagram_account_id', 'type']);
});

it('validates caption length', function () {
    $this->actingAs($this->user);

    $data = [
        'instagram_account_id' => $this->instagramAccount->id,
        'type' => 'feed',
        'caption' => str_repeat('a', 2201), // Too long
    ];

    $response = $this->post('/posts', $data);

    $response->assertSessionHasErrors(['caption']);
});

it('prevents creating post for other company', function () {
    $otherCompany = Company::factory()->create();
    $otherInstagram = InstagramAccount::factory()->create([
        'company_id' => $otherCompany->id,
    ]);

    $this->actingAs($this->user);

    $response = $this->post('/posts', [
        'instagram_account_id' => $otherInstagram->id,
        'type' => 'feed',
        'caption' => 'Test',
    ]);

    $response->assertForbidden();
});
```

### Unit Test Example

**File:** `tests/Unit/Services/PostServiceTest.php`

```php
<?php

use App\Models\Company;
use App\Models\Post;
use App\Models\User;
use App\Services\Post\PostService;
use App\Repositories\Post\PostRepository;

beforeEach(function () {
    $this->repository = Mockery::mock(PostRepository::class);
    $this->service = new PostService($this->repository);
});

it('creates post with valid data', function () {
    $company = Company::factory()->create();
    $user = User::factory()->create();

    $data = [
        'instagram_account_id' => 1,
        'type' => 'feed',
        'caption' => 'Test caption',
    ];

    $expectedPost = Post::factory()->make([
        'company_id' => $company->id,
        'created_by' => $user->id,
        ...$data,
    ]);

    $this->repository
        ->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($arg) use ($company, $user) {
            return $arg['company_id'] === $company->id
                && $arg['created_by'] === $user->id;
        }))
        ->andReturn($expectedPost);

    $this->actingAs($user);

    $result = $this->service->createPost($company->id, $data);

    expect($result)->toBeInstanceOf(Post::class);
    expect($result->caption)->toBe('Test caption');
});

it('throws exception for invalid caption length', function () {
    $company = Company::factory()->create();

    $data = [
        'caption' => str_repeat('a', 2201),
    ];

    $this->service->createPost($company->id, $data);
})->throws(InvalidArgumentException::class);
```

---

## Test Coverage Requirements

### Minimum Coverage

- **Services:** 90%
- **Repositories:** 80%
- **Controllers:** 70%
- **Models:** 60% (mostly relationships)
- **Overall:** 80%

### Running Coverage

```bash
# Generate coverage report
php artisan test --coverage

# With minimum threshold
php artisan test --coverage --min=80

# HTML report (opens in browser)
php artisan test --coverage-html=coverage

# Check specific path
php artisan test tests/Unit/Services --coverage --min=90
```

### Coverage Report Example

```
  PASS  Tests\Unit\Services\PostServiceTest
  ✓ creates post with valid data
  ✓ throws exception for invalid caption length
  ✓ creates post assets

  PASS  Tests\Feature\Post\CreatePostTest
  ✓ creates a post with valid data
  ✓ requires authentication to create post
  ✓ validates required fields

  Tests:    6 passed
  Duration: 1.23s

  Coverage:
    PostService     ......... 95%
    PostRepository  ......... 85%
    PostController  ......... 75%
    Total           ......... 82%
```

---

## Testing Best Practices

### 1. Test Names Should Be Descriptive

**✅ GOOD:**

```php
it('creates post with valid data', function () { });
it('requires authentication to create post', function () { });
it('validates caption does not exceed 2200 characters', function () { });
```

**❌ BAD:**

```php
it('test 1', function () { });
it('works', function () { });
it('post test', function () { });
```

### 2. Use Arrange-Act-Assert Pattern

```php
it('creates post', function () {
    // Arrange - Setup test data
    $user = User::factory()->create();
    $company = Company::factory()->create();

    // Act - Perform action
    $response = $this->actingAs($user)->post('/posts', $data);

    // Assert - Verify result
    $response->assertCreated();
    expect(Post::count())->toBe(1);
});
```

### 3. One Assertion Per Test (Prefer)

**✅ GOOD:**

```php
it('creates post with correct company id', function () {
    // ...
    expect($post->company_id)->toBe($company->id);
});

it('creates post with correct user id', function () {
    // ...
    expect($post->created_by)->toBe($user->id);
});
```

**⚠️ ACCEPTABLE:**

```php
it('creates post with correct data', function () {
    // ...
    expect($post->company_id)->toBe($company->id);
    expect($post->created_by)->toBe($user->id);
    expect($post->caption)->toBe('Test');
});
```

### 4. Use Dataset for Multiple Cases

```php
it('validates post type', function (string $type, bool $valid) {
    $data = ['type' => $type];

    $response = $this->post('/posts', $data);

    if ($valid) {
        $response->assertCreated();
    } else {
        $response->assertSessionHasErrors(['type']);
    }
})->with([
    ['feed', true],
    ['reel', true],
    ['story', true],
    ['invalid', false],
]);
```

### 5. Clean Up After Tests

```php
afterEach(function () {
    Mockery::close();
});

// Or use RefreshDatabase trait
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);
```

---

## Mocking External Services

### Mock Stripe

```php
use Stripe\StripeClient;

it('processes payment with Stripe', function () {
    $stripeMock = Mockery::mock(StripeClient::class);

    $stripeMock->paymentIntents = Mockery::mock();
    $stripeMock->paymentIntents
        ->shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($arg) {
            return $arg['amount'] === 5000
                && $arg['currency'] === 'usd';
        }))
        ->andReturn((object) [
            'id' => 'pi_test_123',
            'status' => 'succeeded',
        ]);

    $this->app->instance(StripeClient::class, $stripeMock);

    // Test code that uses Stripe
});
```

### Mock Instagram API

```php
use App\Services\Instagram\InstagramGraphService;

it('publishes post to Instagram', function () {
    $instagramMock = Mockery::mock(InstagramGraphService::class);

    $instagramMock
        ->shouldReceive('publishPost')
        ->once()
        ->with(Mockery::type(Post::class))
        ->andReturn([
            'id' => 'ig_123456',
            'permalink' => 'https://instagram.com/p/abc123',
        ]);

    $this->app->instance(InstagramGraphService::class, $instagramMock);

    // Test code that publishes to Instagram
});
```

### Mock AI Services

```php
use App\Services\AI\CaptionGeneratorService;

it('generates caption with AI', function () {
    $aiMock = Mockery::mock(CaptionGeneratorService::class);

    $aiMock
        ->shouldReceive('generate')
        ->once()
        ->with('fitness motivation')
        ->andReturn([
            'caption' => 'Start your day with energy! 💪',
            'hashtags' => ['#fitness', '#motivation'],
        ]);

    $this->app->instance(CaptionGeneratorService::class, $aiMock);

    // Test code that generates captions
});
```

### Mock HTTP Responses

```php
use Illuminate\Support\Facades\Http;

it('fetches data from external API', function () {
    Http::fake([
        'api.example.com/*' => Http::response([
            'data' => ['id' => 1, 'name' => 'Test'],
        ], 200),
    ]);

    $response = Http::get('https://api.example.com/data');

    expect($response->json()['data']['name'])->toBe('Test');
});
```

---

## Continuous Integration

### Running Tests in CI/CD

**GitHub Actions** (see `GITHUB_PR_AUTOMATION.md`):

```yaml
- name: Run tests
  run: php artisan test --coverage --min=80
```

**Before Push** (see `CODE_QUALITY_SETUP.md`):

```bash
# Pre-push hook runs tests
php artisan test
```

---

## Test Examples Checklist

### Before Submitting PR

- [ ] All tests pass locally
- [ ] Coverage meets minimum (80%)
- [ ] No skipped tests (unless documented)
- [ ] Test names are descriptive
- [ ] External services are mocked
- [ ] Database is cleaned (RefreshDatabase)
- [ ] Tests run fast (< 5 seconds)

---

## Summary

### Golden Rules

1. **Always use factories** - Never seeders in tests
2. **Create factory with every model** - Mandatory rule
3. **Mock external services** - No real API calls in tests
4. **Fast tests** - Use in-memory database
5. **Descriptive names** - Clear what test does
6. **Minimum 80% coverage** - Required for merge

### Quick Reference

```bash
# Create model with factory
php artisan make:model Post -mf

# Run tests
php artisan test

# Run with coverage
php artisan test --coverage --min=80

# Run specific test
php artisan test --filter test_user_can_create_post
```

---

**Document Status:** Active - Mandatory for all developers  
**Last Updated:** October 9, 2025  
**Related Docs:**

- [CODING_STANDARDS.md](./CODING_STANDARDS.md)
- [CODE_QUALITY_SETUP.md](./CODE_QUALITY_SETUP.md)
