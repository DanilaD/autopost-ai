# Quick Reference Guide

**One-page cheat sheet for daily development**

---

## 🚀 Common Commands

### Development

```bash
# Start all services (recommended)
composer dev

# Or individually:
php artisan serve          # Server (http://localhost:8000)
npm run dev                # Vite (hot reload)
php artisan queue:work     # Queue worker
php artisan pail           # Log viewer

# Build for production
npm run build
```

### Testing

```bash
php artisan test                    # All tests
php artisan test --coverage         # With coverage
php artisan test --filter TestName  # Specific test
php artisan test --parallel         # Faster (parallel)
```

### Code Quality

```bash
./vendor/bin/pint          # Format PHP code
npm run lint               # Lint JS/Vue
./vendor/bin/phpstan       # Static analysis (if installed)
```

### Database

```bash
php artisan migrate                  # Run migrations
php artisan migrate:fresh --seed     # Fresh DB with data
php artisan db:seed                  # Run seeders only
php artisan migrate:rollback         # Undo last migration
php artisan migrate:status           # Check migration status
```

### Cache

```bash
php artisan cache:clear      # Clear application cache
php artisan config:clear     # Clear config cache
php artisan route:clear      # Clear route cache
php artisan view:clear       # Clear compiled views
php artisan optimize:clear   # Clear all caches
```

### Optimization (Production)

```bash
php artisan config:cache     # Cache config
php artisan route:cache      # Cache routes
php artisan view:cache       # Cache views
php artisan optimize         # All optimizations
```

---

## 📐 Architecture Pattern

```
HTTP Request
    ↓
Route
    ↓
Controller (HTTP only - NO business logic)
    ↓
Service (ALL business logic)
    ↓
Repository (ALL database queries)
    ↓
Model (relationships ONLY)
    ↓
Database
```

### File Structure

```
app/
├── Http/Controllers/          # HTTP handling only
│   └── Post/
│       └── PostController.php
├── Services/                  # Business logic
│   └── Post/
│       └── PostService.php
├── Repositories/              # Database queries
│   └── Post/
│       └── PostRepository.php
├── Models/                    # Eloquent models
│   └── Post.php
├── Enums/                     # Enumerations
│   └── PostStatus.php
└── Providers/                 # Service providers
    └── RepositoryServiceProvider.php
```

---

## 🎯 Naming Conventions

| Type           | Example                  | Notes                            |
| -------------- | ------------------------ | -------------------------------- |
| **Controller** | `PostController`         | Singular, ends with `Controller` |
| **Service**    | `PostService`            | Singular, ends with `Service`    |
| **Repository** | `PostRepository`         | Singular, ends with `Repository` |
| **Model**      | `Post`                   | Singular, PascalCase             |
| **Factory**    | `PostFactory`            | Singular, ends with `Factory`    |
| **Seeder**     | `PostSeeder`             | Singular, ends with `Seeder`     |
| **Request**    | `CreatePostRequest`      | Action + Model + `Request`       |
| **Resource**   | `PostResource`           | Singular, ends with `Resource`   |
| **Job**        | `PublishPost`            | Action + Model                   |
| **Event**      | `PostPublished`          | Model + Action (past tense)      |
| **Listener**   | `SendPostNotification`   | Action description               |
| **Test**       | `CreatePostTest`         | Action + `Test`                  |
| **Migration**  | `create_posts_table`     | Snake_case, plural               |
| **Route**      | `/posts`                 | Plural, kebab-case               |
| **View**       | `posts/create.blade.php` | Plural folder, kebab-case        |
| **Component**  | `PostCard.vue`           | PascalCase                       |

---

## 📝 Commit Message Format

```
<type>(<scope>): <subject>

[optional body]

[optional footer]
```

### Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation only
- `style`: Formatting (no code change)
- `refactor`: Code refactoring
- `test`: Adding tests
- `chore`: Build/tooling

### Examples

```bash
git commit -m "feat(posts): add carousel post support"
git commit -m "fix(auth): resolve magic link expiration issue"
git commit -m "docs(readme): update installation instructions"
git commit -m "refactor(services): extract common logic to base service"
git commit -m "test(posts): add coverage for draft posts"
git commit -m "chore(deps): update laravel to 12.1"
```

---

## 🧪 Testing Patterns

### Feature Test (HTTP)

```php
it('creates post with valid data', function () {
    // Arrange
    $user = User::factory()->create();
    $data = ['caption' => 'Test'];

    // Act
    $response = $this->actingAs($user)->post('/posts', $data);

    // Assert
    $response->assertCreated();
    expect(Post::count())->toBe(1);
});
```

### Unit Test (Service)

```php
it('calculates wallet balance correctly', function () {
    // Arrange
    $wallet = Wallet::factory()->create(['balance' => 100]);
    $service = app(WalletService::class);

    // Act
    $service->debit($wallet, 30);

    // Assert
    expect($wallet->fresh()->balance)->toBe(70);
});
```

### Always Use Factories

```php
// ✅ GOOD
$post = Post::factory()->create();
$posts = Post::factory()->count(10)->create();

// ❌ BAD
$this->seed(PostSeeder::class);
```

---

## 🔧 Artisan Commands

### Generate Code

```bash
# Model + Migration + Factory
php artisan make:model Post -mf

# Controller
php artisan make:controller Post/PostController

# Service (custom)
php artisan make:service Post/PostService

# Repository (custom)
php artisan make:repository Post/PostRepository

# Request
php artisan make:request CreatePostRequest

# Resource
php artisan make:resource PostResource

# Test
php artisan make:test Post/CreatePostTest

# Factory
php artisan make:factory PostFactory --model=Post

# Seeder
php artisan make:seeder PostSeeder

# Job
php artisan make:job PublishPost

# Event
php artisan make:event PostPublished

# Listener
php artisan make:listener SendPostNotification
```

---

## 🎨 Blade/Vue Components

### Blade Component

```bash
php artisan make:component PostCard
```

```blade
{{-- resources/views/components/post-card.blade.php --}}
<div class="post-card">
    <h3>{{ $title }}</h3>
    <p>{{ $caption }}</p>
</div>

{{-- Usage --}}
<x-post-card :title="$post->title" :caption="$post->caption" />
```

### Vue Component

```vue
<!-- resources/js/components/PostCard.vue -->
<script setup>
defineProps({
    title: String,
    caption: String,
})
</script>

<template>
    <div class="post-card">
        <h3>{{ title }}</h3>
        <p>{{ caption }}</p>
    </div>
</template>

<!-- Usage -->
<PostCard :title="post.title" :caption="post.caption" />
```

---

## 🗄️ Database Queries

### Common Patterns

```php
// Eager loading (avoid N+1)
$posts = Post::with('company', 'creator')->get();

// Pagination
$posts = Post::paginate(15);

// Filtering
$posts = Post::where('status', PostStatus::PUBLISHED)
    ->whereDate('published_at', '>=', now()->subDays(7))
    ->latest()
    ->get();

// Transactions
DB::transaction(function () {
    $post = Post::create($data);
    $post->assets()->createMany($assetData);
});

// Raw queries (use sparingly)
$results = DB::select('SELECT * FROM posts WHERE status = ?', ['published']);
```

---

## 🔐 Authentication

### Check Auth

```php
// In controller
if (auth()->check()) {
    $user = auth()->user();
}

// In Blade
@auth
    <p>Welcome, {{ auth()->user()->name }}!</p>
@endauth

// In Vue (Inertia)
import { usePage } from '@inertiajs/vue3'
const { auth } = usePage().props
```

### Protect Routes

```php
// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});

// Or in controller
public function __construct()
{
    $this->middleware('auth');
}
```

---

## 📧 Notifications

### Send Notification

```php
// To user
$user->notify(new PostPublishedNotification($post));

// To multiple users
Notification::send($users, new PostPublishedNotification($post));

// Via specific channel
$user->notify(new PostPublishedNotification($post));
```

---

## 🔄 Queues & Jobs

### Dispatch Job

```php
// Dispatch immediately
PublishPost::dispatch($post);

// Delay
PublishPost::dispatch($post)->delay(now()->addMinutes(10));

// Chain jobs
PublishPost::withChain([
    new SendNotification($post),
    new UpdateAnalytics($post),
])->dispatch($post);
```

### Process Queue

```bash
# Start worker
php artisan queue:work

# Process one job
php artisan queue:work --once

# With timeout
php artisan queue:work --timeout=60
```

---

## 🐛 Debugging

### Dump & Die

```php
dd($variable);           // Dump and die
dump($variable);         // Dump and continue
ray($variable);          // Ray debug tool (if installed)
```

### Log

```php
Log::info('Post created', ['post_id' => $post->id]);
Log::warning('Slow query detected');
Log::error('Failed to publish', ['error' => $e->getMessage()]);
```

### View Logs

```bash
# Real-time log viewer (Laravel Pail)
php artisan pail

# Or tail log file
tail -f storage/logs/laravel.log
```

---

## 🚦 HTTP Status Codes

| Code | Method            | Description                |
| ---- | ----------------- | -------------------------- |
| 200  | OK                | Successful GET, PUT, PATCH |
| 201  | Created           | Successful POST            |
| 204  | No Content        | Successful DELETE          |
| 400  | Bad Request       | Invalid request            |
| 401  | Unauthorized      | Not authenticated          |
| 403  | Forbidden         | No permission              |
| 404  | Not Found         | Resource not found         |
| 422  | Unprocessable     | Validation failed          |
| 429  | Too Many Requests | Rate limit exceeded        |
| 500  | Server Error      | Something broke            |

---

## 🎯 Git Workflow

```bash
# Update main
git checkout main
git pull origin main

# Create feature branch
git checkout -b feature/add-new-feature

# Work and commit
git add .
git commit -m "feat(scope): description"

# Push and create PR
git push origin feature/add-new-feature

# After PR approved and merged
git checkout main
git pull origin main
git branch -d feature/add-new-feature
```

---

## 📚 Documentation Links

- **Start Here:** [GETTING_STARTED.md](./GETTING_STARTED.md)
- **Standards:** [CODING_STANDARDS.md](./CODING_STANDARDS.md)
- **Testing:** [TESTING_GUIDE.md](./TESTING_GUIDE.md)
- **Contributing:** [CONTRIBUTING.md](../CONTRIBUTING.md)
- **All Docs:** [INDEX.md](./INDEX.md)

---

## 🆘 Need Help?

```bash
# Laravel help
php artisan help <command>

# List all commands
php artisan list

# Tinker (REPL)
php artisan tinker
```

**Remember:** Read the docs, ask questions, write tests! 🚀
