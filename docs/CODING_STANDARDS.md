# Coding Standards & Architecture Guidelines

**Version:** 1.1  
**Date:** October 10, 2025  
**Status:** Active - All developers must follow  
**Recent Update:** Added mandatory pre-commit documentation check rule

---

## Table of Contents

1. [Architecture Overview](#architecture-overview)
2. [Layer Responsibilities](#layer-responsibilities)
3. [Naming Conventions](#naming-conventions)
4. [Code Structure](#code-structure)
5. [Best Practices](#best-practices)
6. [Documentation Rules](#documentation-rules)
7. [Testing Requirements](#testing-requirements)
8. [Code Examples](#code-examples)

---

## Architecture Overview

### Clean Architecture Pattern

This project follows a **clean, layered architecture** to ensure:

- ✅ **Separation of concerns** - Each layer has one job
- ✅ **Testability** - Easy to mock and test
- ✅ **Maintainability** - Easy to find and modify code
- ✅ **Scalability** - Easy to add new features

### Layer Flow

```
Request → Route → Controller → Service → Repository → Model → Database
                      ↓
                  Response
```

**Never skip layers!** Always go through the proper flow.

---

## Layer Responsibilities

### 1. Controllers (`app/Http/Controllers/`)

**Purpose:** Handle HTTP requests and responses ONLY.

**Rules:**

- ❌ **NO business logic**
- ❌ **NO database queries**
- ❌ **NO data manipulation**
- ✅ **ONLY** validate input, call services, return responses

**Responsibilities:**

- Validate request input
- Call appropriate service method
- Return JSON or Inertia response
- Handle exceptions (with try-catch)

**Example Structure:**

```php
<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\CreatePostRequest;
use App\Services\Post\PostService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Inject service via constructor
     */
    public function __construct(
        private PostService $postService
    ) {}

    /**
     * Display posts list
     */
    public function index(): Response
    {
        $posts = $this->postService->getCompanyPosts(
            companyId: auth()->user()->currentCompany->id
        );

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Store new post
     */
    public function store(CreatePostRequest $request): JsonResponse
    {
        try {
            $post = $this->postService->createPost(
                companyId: auth()->user()->currentCompany->id,
                data: $request->validated()
            );

            return response()->json([
                'message' => __('posts.created_successfully'),
                'post' => $post,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => __('posts.create_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
```

---

### 2. Models (`app/Models/`)

**Purpose:** Define database structure, relationships, and attribute casting ONLY.

**Rules:**

- ❌ **NO business logic**
- ❌ **NO complex calculations**
- ❌ **NO data fetching logic**
- ✅ **ONLY** relationships, casts, accessors, mutators

**Responsibilities:**

- Define fillable/guarded attributes
- Define relationships (hasMany, belongsTo, etc.)
- Define attribute casts
- Simple accessors/mutators (formatting only)
- Define model events (if needed)

**Example Structure:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Post Model
 *
 * Represents Instagram posts scheduled or published by users.
 *
 * @property int $id
 * @property int $company_id
 * @property int $created_by
 * @property string $caption
 * @property string $status
 * @property \Carbon\Carbon $scheduled_at
 */
class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'company_id',
        'created_by',
        'instagram_account_id',
        'type',
        'caption',
        'scheduled_at',
        'status',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
        'metadata' => 'array',
        'status' => \App\Enums\PostStatus::class,
        'type' => \App\Enums\PostType::class,
    ];

    /**
     * Get the company that owns the post
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who created the post
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the Instagram account
     */
    public function instagramAccount(): BelongsTo
    {
        return $this->belongsTo(InstagramAccount::class);
    }

    /**
     * Get the post assets (images/videos)
     */
    public function assets(): HasMany
    {
        return $this->hasMany(PostAsset::class);
    }

    /**
     * Scope: Filter by company
     */
    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }
}
```

---

### 3. Enums (`app/Enums/`)

**Purpose:** Define constants and status values.

**Rules:**

- ✅ Use PHP 8.2+ backed enums
- ✅ Define all possible values
- ✅ Add helper methods if needed (labels, colors, etc.)

**Example Structure:**

```php
<?php

namespace App\Enums;

/**
 * Post Status Enum
 *
 * Represents the lifecycle status of a post.
 */
enum PostStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHING = 'publishing';
    case PUBLISHED = 'published';
    case FAILED = 'failed';

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::DRAFT => __('posts.status.draft'),
            self::SCHEDULED => __('posts.status.scheduled'),
            self::PUBLISHING => __('posts.status.publishing'),
            self::PUBLISHED => __('posts.status.published'),
            self::FAILED => __('posts.status.failed'),
        };
    }

    /**
     * Get color for UI display
     */
    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::SCHEDULED => 'blue',
            self::PUBLISHING => 'yellow',
            self::PUBLISHED => 'green',
            self::FAILED => 'red',
        };
    }

    /**
     * Check if post can be edited
     */
    public function isEditable(): bool
    {
        return in_array($this, [self::DRAFT, self::SCHEDULED, self::FAILED]);
    }
}
```

**Example: PostType**

```php
<?php

namespace App\Enums;

enum PostType: string
{
    case FEED = 'feed';
    case REEL = 'reel';
    case STORY = 'story';
    case CAROUSEL = 'carousel';

    public function label(): string
    {
        return match($this) {
            self::FEED => __('posts.type.feed'),
            self::REEL => __('posts.type.reel'),
            self::STORY => __('posts.type.story'),
            self::CAROUSEL => __('posts.type.carousel'),
        };
    }
}
```

---

### 4. Services (`app/Services/`)

**Purpose:** Contain ALL business logic.

**Rules:**

- ✅ **All business logic goes here**
- ✅ **Orchestrate multiple repositories**
- ✅ **Handle transactions**
- ✅ **Dispatch jobs/events**
- ✅ **Validate business rules**
- ❌ **NO direct database queries** (use repositories)

**Responsibilities:**

- Execute business logic
- Coordinate between repositories
- Handle complex operations
- Dispatch jobs and events
- Return DTOs or Models

**Directory Structure:**

```
app/Services/
├── Post/
│   ├── PostService.php
│   └── PostPublishService.php
├── Wallet/
│   ├── WalletService.php
│   ├── TransactionService.php
│   └── LedgerReconciliationService.php
├── Instagram/
│   ├── InstagramGraphService.php
│   ├── TokenRefreshService.php
│   └── PublishService.php
└── AI/
    ├── CaptionGeneratorService.php
    ├── ImageGeneratorService.php
    └── VideoGeneratorService.php
```

**Example Structure:**

```php
<?php

namespace App\Services\Post;

use App\Enums\PostStatus;
use App\Models\Post;
use App\Repositories\Post\PostRepository;
use App\Repositories\PostAsset\PostAssetRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Post Service
 *
 * Handles all business logic related to posts.
 */
class PostService
{
    /**
     * Constructor dependency injection
     */
    public function __construct(
        private PostRepository $postRepository,
        private PostAssetRepository $assetRepository
    ) {}

    /**
     * Get all posts for a company
     *
     * @param int $companyId
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getCompanyPosts(int $companyId, array $filters = [])
    {
        Log::info('Fetching posts for company', ['company_id' => $companyId]);

        return $this->postRepository->getByCompany($companyId, $filters);
    }

    /**
     * Create a new post
     *
     * Business rules:
     * - User must belong to company
     * - Caption max 2200 characters
     * - Scheduled time must be in future
     *
     * @param int $companyId
     * @param array $data
     * @return Post
     * @throws \Exception
     */
    public function createPost(int $companyId, array $data): Post
    {
        // Validate business rules
        $this->validatePostData($data);

        DB::beginTransaction();

        try {
            // Create post
            $post = $this->postRepository->create([
                'company_id' => $companyId,
                'created_by' => auth()->id(),
                'instagram_account_id' => $data['instagram_account_id'],
                'type' => $data['type'],
                'caption' => $data['caption'] ?? null,
                'scheduled_at' => $data['scheduled_at'] ?? null,
                'status' => PostStatus::DRAFT,
                'metadata' => $data['metadata'] ?? [],
            ]);

            // Create assets if provided
            if (!empty($data['assets'])) {
                foreach ($data['assets'] as $asset) {
                    $this->assetRepository->create([
                        'post_id' => $post->id,
                        'type' => $asset['type'],
                        'storage_path' => $asset['storage_path'],
                        'url' => $asset['url'],
                        'order' => $asset['order'] ?? 0,
                        'metadata' => $asset['metadata'] ?? [],
                    ]);
                }
            }

            DB::commit();

            Log::info('Post created successfully', ['post_id' => $post->id]);

            return $post->load('assets');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to create post', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Validate post data against business rules
     */
    private function validatePostData(array $data): void
    {
        if (!empty($data['caption']) && mb_strlen($data['caption']) > 2200) {
            throw new \InvalidArgumentException(__('posts.caption_too_long'));
        }

        if (!empty($data['scheduled_at']) && strtotime($data['scheduled_at']) < time()) {
            throw new \InvalidArgumentException(__('posts.scheduled_time_must_be_future'));
        }
    }
}
```

---

### 5. Repositories (`app/Repositories/`)

**Purpose:** Handle ALL database interactions.

**Rules:**

- ✅ **All database queries go here**
- ✅ **Return Models or Collections**
- ✅ **Simple, focused methods**
- ❌ **NO business logic**
- ❌ **NO complex calculations**

**Responsibilities:**

- CRUD operations
- Query filtering and sorting
- Eager loading relationships
- Pagination

**Directory Structure:**

```
app/Repositories/
├── BaseRepository.php
├── Post/
│   └── PostRepository.php
├── Wallet/
│   ├── WalletRepository.php
│   └── WalletTransactionRepository.php
└── Instagram/
    └── InstagramAccountRepository.php
```

**Example: BaseRepository**

```php
<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

/**
 * Base Repository
 *
 * All repositories should extend this base class.
 */
abstract class BaseRepository
{
    /**
     * Model instance
     */
    protected Model $model;

    /**
     * Get all records
     */
    public function all(): Collection
    {
        return $this->model->all();
    }

    /**
     * Find record by ID
     */
    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    /**
     * Find or fail
     */
    public function findOrFail(int $id): Model
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create new record
     */
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update record
     */
    public function update(Model $model, array $data): Model
    {
        $model->update($data);
        return $model->fresh();
    }

    /**
     * Delete record
     */
    public function delete(Model $model): bool
    {
        return $model->delete();
    }
}
```

**Example: PostRepository**

```php
<?php

namespace App\Repositories\Post;

use App\Models\Post;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Post Repository
 *
 * Handles all database operations for posts.
 */
class PostRepository extends BaseRepository
{
    /**
     * Constructor
     */
    public function __construct(Post $model)
    {
        $this->model = $model;
    }

    /**
     * Get posts by company ID
     *
     * @param int $companyId
     * @param array $filters
     * @return Collection
     */
    public function getByCompany(int $companyId, array $filters = []): Collection
    {
        $query = $this->model->query()
            ->where('company_id', $companyId)
            ->with(['creator', 'instagramAccount', 'assets']);

        // Apply filters
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['instagram_account_id'])) {
            $query->where('instagram_account_id', $filters['instagram_account_id']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get scheduled posts (ready to publish)
     *
     * @return Collection
     */
    public function getScheduledPosts(): Collection
    {
        return $this->model->query()
            ->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now())
            ->with(['instagramAccount', 'assets'])
            ->get();
    }

    /**
     * Paginate posts
     *
     * @param int $companyId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $companyId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->query()
            ->where('company_id', $companyId)
            ->with(['creator', 'instagramAccount', 'assets'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }
}
```

---

### 6. Service Providers (`app/Providers/`)

**Purpose:** Register and bind services to the container.

**Rules:**

- ✅ Bind interfaces to implementations
- ✅ Register singletons for heavy services
- ✅ Keep providers focused (one per domain)

**Example: RepositoryServiceProvider**

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

/**
 * Repository Service Provider
 *
 * Binds all repositories to the service container.
 */
class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        // Posts
        $this->app->bind(
            \App\Repositories\Post\PostRepositoryInterface::class,
            \App\Repositories\Post\PostRepository::class
        );

        // Wallet
        $this->app->bind(
            \App\Repositories\Wallet\WalletRepositoryInterface::class,
            \App\Repositories\Wallet\WalletRepository::class
        );

        $this->app->bind(
            \App\Repositories\Wallet\WalletTransactionRepositoryInterface::class,
            \App\Repositories\Wallet\WalletTransactionRepository::class
        );
    }
}
```

**Example: AppServiceProvider**

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register service providers
        $this->app->register(RepositoryServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
```

---

## Naming Conventions

### PHP Classes

| Type           | Convention            | Example                              |
| -------------- | --------------------- | ------------------------------------ |
| **Controller** | Singular + Controller | `PostController`                     |
| **Model**      | Singular, PascalCase  | `WalletTransaction`                  |
| **Service**    | Domain + Service      | `PostPublishService`                 |
| **Repository** | Model + Repository    | `PostRepository`                     |
| **Enum**       | Singular, PascalCase  | `PostStatus`                         |
| **Request**    | Action + ModelRequest | `CreatePostRequest`                  |
| **Resource**   | Model + Resource      | `PostResource`                       |
| **Job**        | Verb + Noun + Job     | `PublishScheduledPostsJob`           |
| **Event**      | Past tense            | `PostPublished`                      |
| **Listener**   | Verb + Noun           | `SendPostNotification`               |
| **Middleware** | Verb + Noun           | `EnsureUserHasCompanyAccess`         |
| **Exception**  | Noun + Exception      | `InsufficientWalletBalanceException` |

### Database Tables

| Type            | Convention                   | Example                              |
| --------------- | ---------------------------- | ------------------------------------ |
| **Table**       | Plural, snake_case           | `wallet_transactions`                |
| **Pivot**       | Singular models, alpha order | `company_user`                       |
| **Column**      | snake_case                   | `created_at`, `instagram_account_id` |
| **Foreign Key** | singular_id                  | `company_id`, `user_id`              |
| **Index**       | idx_columns                  | `idx_company_status`                 |

### Vue Components

| Type           | Convention            | Example                 |
| -------------- | --------------------- | ----------------------- |
| **Component**  | PascalCase            | `PostCard.vue`          |
| **Page**       | PascalCase            | `CreatePost.vue`        |
| **Composable** | camelCase, use prefix | `usePostFilters.js`     |
| **Props**      | camelCase             | `currentUser`, `postId` |
| **Events**     | kebab-case            | `@post-created`         |

### Methods & Functions

| Type         | Convention             | Example                           |
| ------------ | ---------------------- | --------------------------------- |
| **Get data** | get + Noun             | `getCompanyPosts()`               |
| **Create**   | create + Noun          | `createPost()`                    |
| **Update**   | update + Noun          | `updatePost()`                    |
| **Delete**   | delete + Noun          | `deletePost()`                    |
| **Boolean**  | is/has/can + Adjective | `isPublished()`, `hasAccess()`    |
| **Action**   | verb + Noun            | `publishPost()`, `creditWallet()` |

---

## Code Structure

### File Organization

**Every class should have:**

1. PHPDoc block with description
2. Properties (grouped by visibility)
3. Constructor (if needed)
4. Public methods
5. Protected methods
6. Private methods

**Example:**

```php
<?php

namespace App\Services\Post;

/**
 * Post Service
 *
 * Handles all business logic related to posts.
 *
 * @author Your Name
 * @package App\Services\Post
 */
class PostService
{
    // 1. Properties
    private PostRepository $postRepository;
    private PostAssetRepository $assetRepository;

    // 2. Constructor
    public function __construct(
        PostRepository $postRepository,
        PostAssetRepository $assetRepository
    ) {
        $this->postRepository = $postRepository;
        $this->assetRepository = $assetRepository;
    }

    // 3. Public methods
    public function createPost(int $companyId, array $data): Post
    {
        // ...
    }

    // 4. Protected methods
    protected function validatePostData(array $data): void
    {
        // ...
    }

    // 5. Private methods
    private function formatCaption(string $caption): string
    {
        // ...
    }
}
```

### Method Documentation

**Every method must have:**

```php
/**
 * Brief description of what the method does
 *
 * Longer explanation if needed, including:
 * - Business rules
 * - Side effects
 * - Important notes
 *
 * @param int $companyId The company ID
 * @param array $data Post data
 * @return Post Created post instance
 * @throws \InvalidArgumentException If data is invalid
 * @throws \Exception If database operation fails
 */
public function createPost(int $companyId, array $data): Post
{
    // ...
}
```

---

## Best Practices

### 1. Dependency Injection

**✅ DO:**

```php
public function __construct(
    private PostService $postService,
    private WalletService $walletService
) {}
```

**❌ DON'T:**

```php
public function store(Request $request)
{
    $service = new PostService(); // Never do this!
}
```

---

### 2. Type Hints

**✅ DO:**

```php
public function createPost(int $companyId, array $data): Post
{
    // ...
}
```

**❌ DON'T:**

```php
public function createPost($companyId, $data) // No type hints
{
    // ...
}
```

---

### 3. Return Types

**Always specify return types:**

```php
public function getPost(int $id): ?Post // Nullable
public function getPosts(): Collection
public function deletePost(int $id): bool
public function render(): Response
```

---

### 4. Database Transactions

**Always use transactions for multi-step operations:**

```php
use Illuminate\Support\Facades\DB;

public function createPostWithAssets(array $data): Post
{
    DB::beginTransaction();

    try {
        $post = $this->postRepository->create($data);
        $this->createAssets($post->id, $data['assets']);

        DB::commit();
        return $post;

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

---

### 5. Logging

**Log important operations:**

```php
use Illuminate\Support\Facades\Log;

public function publishPost(int $postId): void
{
    Log::info('Publishing post', ['post_id' => $postId]);

    try {
        // ... publish logic

        Log::info('Post published successfully', ['post_id' => $postId]);

    } catch (\Exception $e) {
        Log::error('Failed to publish post', [
            'post_id' => $postId,
            'error' => $e->getMessage(),
        ]);

        throw $e;
    }
}
```

---

### 6. Validation

**Use Form Requests for validation:**

```php
<?php

namespace App\Http\Requests\Post;

use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Post::class);
    }

    public function rules(): array
    {
        return [
            'instagram_account_id' => 'required|exists:instagram_accounts,id',
            'type' => 'required|in:feed,reel,story,carousel',
            'caption' => 'nullable|string|max:2200',
            'scheduled_at' => 'nullable|date|after:now',
            'assets' => 'required|array|min:1',
            'assets.*.type' => 'required|in:image,video',
            'assets.*.storage_path' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'caption.max' => __('posts.caption_too_long'),
            'scheduled_at.after' => __('posts.scheduled_time_must_be_future'),
        ];
    }
}
```

---

### 7. Enums Over Constants

**✅ DO:**

```php
enum PostStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
}

// Usage
$post->status = PostStatus::DRAFT;
```

**❌ DON'T:**

```php
const STATUS_DRAFT = 'draft';
const STATUS_PUBLISHED = 'published';
```

---

### 8. Single Responsibility Principle

**Each class should have ONE job:**

**✅ DO:**

```php
// PostService - General post operations
// PostPublishService - Publishing logic only
// PostSchedulerService - Scheduling logic only
```

**❌ DON'T:**

```php
// PostService - Everything (creating, publishing, scheduling, analytics, etc.)
```

---

### 9. Avoid Magic Numbers

**✅ DO:**

```php
const MAX_CAPTION_LENGTH = 2200;
const MAX_ASSETS_PER_POST = 10;

if (strlen($caption) > self::MAX_CAPTION_LENGTH) {
    throw new \InvalidArgumentException('Caption too long');
}
```

**❌ DON'T:**

```php
if (strlen($caption) > 2200) { // What is 2200?
    throw new \InvalidArgumentException('Caption too long');
}
```

---

### 10. Early Returns

**✅ DO:**

```php
public function processPost(Post $post): void
{
    if (!$post->isPublishable()) {
        return;
    }

    if (!$this->hasAssets($post)) {
        return;
    }

    // Main logic here
}
```

**❌ DON'T:**

```php
public function processPost(Post $post): void
{
    if ($post->isPublishable()) {
        if ($this->hasAssets($post)) {
            // Main logic nested deeply
        }
    }
}
```

---

## Documentation Rules

### 1. Code Changes

**When you change code, update:**

- ✅ Related documentation
- ✅ PHPDoc blocks
- ✅ README if public API changes
- ✅ CHANGELOG

### 2. New Features

**When adding new features, create:**

- ✅ Feature documentation in `/docs`
- ✅ Update INDEX.md
- ✅ Add to PROJECT_PLAN.md
- ✅ Write tests

### 3. Documentation Format

**All docs use Markdown with:**

- Table of contents for >1000 words
- Code examples with syntax highlighting
- Clear section headers
- Status indicators (✅, ❌, ⚠️)

### 4. Commit Messages

**Follow conventional commits:**

```
feat(posts): add carousel post type support
fix(wallet): correct balance calculation for refunds
refactor(services): extract publish logic to separate service
docs(auth): update magic link documentation
test(wallet): add transaction ledger reconciliation tests
```

### 5. Pre-Commit Documentation Check ⚠️ **MANDATORY**

**ALWAYS before committing code:**

1. ✅ **Review related documentation** - Check if any docs need updates
2. ✅ **Update affected docs** - Modify relevant documentation files
3. ✅ **Update INDEX.md** - If you created/renamed docs
4. ✅ **Update DATABASE_SCHEMA.md** - If you modified database tables
5. ✅ **Update version/date** - In updated documentation files
6. ✅ **Then commit** - Include doc updates in the same commit

**Examples:**

```bash
# ❌ BAD - Committing code without checking docs
git add app/Models/InstagramAccount.php
git commit -m "feat: add user ownership to Instagram accounts"

# ✅ GOOD - Check and update docs first
# 1. Review docs/DATABASE_SCHEMA.md - needs update
# 2. Update instagram_accounts table documentation
# 3. Update docs/INDEX.md with version bump
# 4. Commit everything together
git add app/Models/InstagramAccount.php
git add docs/DATABASE_SCHEMA.md
git add docs/INDEX.md
git commit -m "feat: add user ownership to Instagram accounts

- Added user_id column to instagram_accounts table
- Updated database schema documentation
- Bumped schema version to 1.1"
```

**Pre-Commit Checklist:**

```
Before running git commit:

□ Are there any related docs in /docs folder?
□ Does DATABASE_SCHEMA.md need updates?
□ Does API documentation need updates?
□ Does INDEX.md need updates?
□ Have I updated version/date in modified docs?
□ Are new features documented?
□ Are breaking changes documented?
```

**Why This Matters:**

- 📚 **Documentation stays current** - Never outdated
- 🤝 **Team stays informed** - Everyone knows about changes
- 🔍 **Code reviews are easier** - Reviewers see full context
- 📝 **Audit trail** - Documentation changes tracked with code
- ⚡ **No follow-up PRs** - One complete change

**When to Skip:**

Only skip documentation updates for:
- Pure refactoring (no behavior change)
- Internal implementation details
- Typo fixes in code comments

---

## Testing Requirements

### 1. Test Coverage

**Minimum requirements:**

- ✅ Services: 90% coverage
- ✅ Repositories: 80% coverage
- ✅ Controllers: 70% coverage

### 2. Test Structure

**Organize tests to mirror code:**

```
tests/
├── Feature/
│   ├── Post/
│   │   ├── CreatePostTest.php
│   │   └── PublishPostTest.php
│   └── Wallet/
│       └── TopUpTest.php
└── Unit/
    ├── Services/
    │   └── PostServiceTest.php
    └── Repositories/
        └── PostRepositoryTest.php
```

### 3. Test Naming

**Use descriptive names:**

```php
it('creates post with valid data', function () {
    // ...
});

it('throws exception when caption exceeds max length', function () {
    // ...
});

it('credits wallet when stripe payment succeeds', function () {
    // ...
});
```

---

## Code Examples

### Complete Feature Example

**Goal:** Create a new post with assets

#### 1. Migration

```php
// database/migrations/YYYY_MM_DD_create_posts_table.php
Schema::create('posts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('company_id')->constrained()->cascadeOnDelete();
    $table->foreignId('created_by')->constrained('users')->nullOnDelete();
    $table->enum('status', ['draft', 'scheduled', 'published', 'failed']);
    $table->text('caption')->nullable();
    $table->timestamps();
});
```

#### 2. Model

```php
// app/Models/Post.php
class Post extends Model
{
    protected $fillable = ['company_id', 'created_by', 'status', 'caption'];

    protected $casts = [
        'status' => PostStatus::class,
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
```

#### 3. Enum

```php
// app/Enums/PostStatus.php
enum PostStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case PUBLISHED = 'published';
    case FAILED = 'failed';
}
```

#### 4. Repository

```php
// app/Repositories/Post/PostRepository.php
class PostRepository extends BaseRepository
{
    public function __construct(Post $model)
    {
        $this->model = $model;
    }

    public function getByCompany(int $companyId): Collection
    {
        return $this->model->where('company_id', $companyId)->get();
    }
}
```

#### 5. Service

```php
// app/Services/Post/PostService.php
class PostService
{
    public function __construct(
        private PostRepository $postRepository
    ) {}

    public function createPost(int $companyId, array $data): Post
    {
        return $this->postRepository->create([
            'company_id' => $companyId,
            'created_by' => auth()->id(),
            'status' => PostStatus::DRAFT,
            'caption' => $data['caption'] ?? null,
        ]);
    }
}
```

#### 6. Request

```php
// app/Http/Requests/Post/CreatePostRequest.php
class CreatePostRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'caption' => 'nullable|string|max:2200',
            'assets' => 'required|array',
        ];
    }
}
```

#### 7. Controller

```php
// app/Http/Controllers/PostController.php
class PostController extends Controller
{
    public function __construct(
        private PostService $postService
    ) {}

    public function store(CreatePostRequest $request): JsonResponse
    {
        $post = $this->postService->createPost(
            companyId: auth()->user()->currentCompany->id,
            data: $request->validated()
        );

        return response()->json(['post' => $post], 201);
    }
}
```

#### 8. Route

```php
// routes/web.php
Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
```

#### 9. Test

```php
// tests/Feature/Post/CreatePostTest.php
it('creates post with valid data', function () {
    $user = User::factory()->create();
    $company = Company::factory()->create(['owner_id' => $user->id]);

    $this->actingAs($user)
        ->post('/posts', [
            'caption' => 'Test caption',
            'assets' => [['type' => 'image', 'url' => 'test.jpg']],
        ])
        ->assertCreated();

    expect(Post::count())->toBe(1);
});
```

---

## Summary

### Golden Rules

1. **Controllers** - No business logic, only HTTP handling
2. **Models** - Only relationships and casts
3. **Enums** - Use for all constants
4. **Services** - All business logic lives here
5. **Repositories** - All database queries go here
6. **Always** - Use dependency injection
7. **Always** - Add type hints
8. **Always** - Document your code
9. **Always** - Write tests
10. **Always** - Update docs when you change code

### File Checklist

Before submitting code, ensure:

- [ ] Controller is thin (< 50 lines per method)
- [ ] Business logic in Service
- [ ] Database queries in Repository
- [ ] Type hints on all methods
- [ ] PHPDoc on all public methods
- [ ] Tests written (min 80% coverage)
- [ ] Documentation updated
- [ ] Code formatted with Pint
- [ ] No linter errors

---

**Remember:** Consistency is key! Use the same patterns everywhere.

---

**Document Status:** Active - Mandatory for all developers  
**Last Updated:** October 9, 2025  
**Maintained By:** Development Team
