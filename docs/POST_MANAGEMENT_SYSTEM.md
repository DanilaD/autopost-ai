# Post Management System Documentation

**Project:** Autopost AI - Post Management System  
**Last Updated:** October 15, 2025  
**Version:** 1.0

---

## 📋 Overview

The Post Management System is a comprehensive content management solution that allows users to create, edit, schedule, and manage Instagram posts with media attachments. The system supports multiple post types, media management, and advanced scheduling capabilities.

## 🚀 Features

### Core Functionality

- **Create Posts** - Create new posts with title, caption, and media
- **Edit Posts** - Modify existing posts and their content
- **Copy/Duplicate Posts** - Duplicate posts with all media files
- **Delete Posts** - Remove posts and associated media
- **Schedule Posts** - Schedule posts for future publishing
- **Media Management** - Upload and manage images/videos
- **Statistics Dashboard** - View post counts by status

### Post Types

- **Feed Post** - Standard Instagram feed posts
- **Reel** - Short-form video content
- **Story** - 24-hour temporary content
- **Carousel** - Multiple images/videos in one post

### Post Statuses

- **Draft** - Work in progress, not scheduled
- **Scheduled** - Scheduled for future publishing
- **Publishing** - Currently being sent to Instagram API
- **Published** - Successfully published to Instagram
- **Failed** - Publishing failed, can be retried

## 🏗️ Architecture

### Database Schema

#### Posts Table

```sql
CREATE TABLE posts (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    company_id BIGINT NOT NULL,
    instagram_account_id BIGINT NOT NULL,
    type ENUM('feed', 'reel', 'story', 'carousel') NOT NULL,
    status ENUM('draft', 'scheduled', 'publishing', 'published', 'failed') NOT NULL,
    title VARCHAR(255) NULL,
    caption TEXT NULL,
    scheduled_at TIMESTAMP NULL,
    published_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (company_id) REFERENCES companies(id),
    FOREIGN KEY (instagram_account_id) REFERENCES instagram_accounts(id)
);
```

#### Post Media Table

```sql
CREATE TABLE post_media (
    id BIGINT PRIMARY KEY,
    post_id BIGINT NOT NULL,
    type ENUM('image', 'video') NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    mime_type VARCHAR(100) NOT NULL,
    file_size BIGINT NOT NULL,
    storage_path VARCHAR(500) NOT NULL,
    url VARCHAR(500) NULL,
    order INTEGER DEFAULT 0,
    metadata JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);
```

### Models

#### Post Model

```php
class Post extends Model
{
    protected $fillable = [
        'user_id', 'company_id', 'instagram_account_id',
        'type', 'status', 'title', 'caption',
        'scheduled_at', 'published_at'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function media()
    {
        return $this->hasMany(PostMedia::class)->orderBy('order');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function instagramAccount()
    {
        return $this->belongsTo(InstagramAccount::class);
    }
}
```

#### PostMedia Model

```php
class PostMedia extends Model
{
    protected $fillable = [
        'post_id', 'type', 'filename', 'original_filename',
        'mime_type', 'file_size', 'storage_path', 'url',
        'order', 'metadata'
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function isImage()
    {
        return $this->type === 'image';
    }

    public function isVideo()
    {
        return $this->type === 'video';
    }
}
```

### Services

#### PostService

Handles all business logic for post operations:

```php
class PostService
{
    public function createPost(array $data): Post
    public function updatePost(Post $post, array $data): Post
    public function deletePost(Post $post): bool
    public function schedulePost(Post $post, Carbon $scheduledAt): Post
    public function publishPost(Post $post): Post
    public function cancelScheduledPost(Post $post): Post
}
```

#### PostMediaService

Manages media file operations:

```php
class PostMediaService
{
    public function uploadMedia(UploadedFile $file, int $postId, int $order = 0): PostMedia
    public function deleteMedia(PostMedia $media): bool
    public function copyMedia(array $mediaToCopy, int $newPostId): void
    public function getPublicUrl(PostMedia $media): string
    public function generateThumbnail(PostMedia $media, int $width = 300, int $height = 300): ?string
}
```

### Controllers

#### PostController

RESTful API endpoints for post management:

```php
class PostController extends Controller
{
    public function index(Request $request) // List posts with filtering
    public function create(Request $request) // Show create form
    public function store(CreatePostRequest $request) // Create new post
    public function show(Post $post) // Show post details
    public function edit(Post $post) // Show edit form
    public function update(UpdatePostRequest $request, Post $post) // Update post
    public function destroy(Post $post) // Delete post
}
```

## 🎨 Frontend Components

### Vue Components

#### Posts/Index.vue

Main posts listing page with:

- Search and filtering
- Status and type filters
- Pagination
- Action buttons (edit, delete, duplicate)
- **Statistics Cards** - Real-time post counts by status

#### Posts/Create.vue

Post creation/editing form with:

- Post type selection
- Instagram account selection
- Title and caption inputs
- Media upload component
- Scheduling options

#### MediaUpload.vue

Media management component with:

- Drag and drop upload
- Image/video preview
- File validation
- Multiple file support

#### DateTimePicker.vue

Scheduling component with:

- Date selection
- Time selection
- Timezone support
- Validation

## 🔧 API Endpoints

### Posts

- `GET /posts` - List posts with filtering
- `GET /posts/create` - Show create form
- `POST /posts` - Create new post
- `GET /posts/{id}` - Show post details
- `GET /posts/{id}/edit` - Show edit form
- `PUT /posts/{id}` - Update post
- `DELETE /posts/{id}` - Delete post
- `GET /posts/stats/overview` - Get post statistics

### Media

- `GET /media/{path}` - Serve private media files

## 🌐 Internationalization

### Supported Languages

- **English (en)** - Complete translation
- **Russian (ru)** - Complete translation
- **Spanish (es)** - Complete translation

### Translation Keys

```php
// posts.php
'title' => 'Post Management',
'create_post' => 'Create Post',
'edit_post' => 'Edit Post',
'delete_post' => 'Delete Post',
'duplicate_post' => 'Duplicate Post',
'status' => [
    'draft' => 'Draft',
    'scheduled' => 'Scheduled',
    'publishing' => 'Publishing',
    'published' => 'Published',
    'failed' => 'Failed',
],
'stats' => [
    'total_posts' => 'Total Posts',
    'drafts' => 'Drafts',
    'scheduled' => 'Scheduled',
    'publishing' => 'Publishing',
    'published' => 'Published',
    'failed' => 'Failed',
],
'type' => [
    'feed' => 'Feed Post',
    'reel' => 'Reel',
    'story' => 'Story',
    'carousel' => 'Carousel',
],
```

## 📊 Statistics Dashboard

### Overview

The Post Management system includes a comprehensive statistics dashboard that displays real-time post counts by status. This feature provides users with immediate visibility into their content pipeline.

### Features

- **Real-time Statistics** - Live post counts updated automatically
- **Status Breakdown** - Counts for all post statuses (Draft, Scheduled, Publishing, Published, Failed)
- **Responsive Design** - Statistics cards adapt to different screen sizes
- **Color-coded Status** - Each status has distinct, accessible colors
- **Multi-language Support** - Statistics labels in English, Spanish, and Russian

### Statistics Display

The statistics are displayed as responsive cards:

```
┌─────────────┬─────────────┬─────────────┬─────────────┬─────────────┬─────────────┐
│ Total Posts │   Drafts   │ Scheduled  │ Publishing  │ Published  │   Failed    │
│     12      │     3      │     4      │     1       │     3      │     1       │
└─────────────┴─────────────┴─────────────┴─────────────┴─────────────┴─────────────┘
```

### API Endpoints

- `GET /posts/stats/overview` - Returns JSON statistics for company or individual user
- Statistics are also included in the main posts index response

### Implementation

The statistics are calculated using optimized SQL queries in the `PostRepository::getStats()` method and displayed using Vue.js components with proper accessibility attributes.

## 🔒 Security Features

### Authorization

- Users can only manage their own posts
- Company admins can manage company posts
- Instagram account access validation

### File Upload Security

- File type validation (images/videos only)
- File size limits
- MIME type verification
- Secure file storage

### Data Validation

- Input sanitization
- XSS prevention
- SQL injection prevention
- CSRF protection

## 📱 Responsive Design

### Mobile Support

- Touch-friendly interface
- Responsive grid layouts
- Mobile-optimized forms
- Swipe gestures for media

### Dark Mode

- System preference detection
- Manual toggle
- Consistent theming
- High contrast support

## 🧪 Testing

### Test Coverage

- **Unit Tests** - Service layer testing
- **Feature Tests** - End-to-end functionality
- **Integration Tests** - API endpoint testing

### Test Files

- `PostServiceTest` - Service layer tests
- `PostControllerTest` - Controller tests
- `PostMediaServiceTest` - Media handling tests
- `PostManagementTest` - Feature tests

## 🚀 Deployment

### Requirements

- PHP 8.3+
- Laravel 11+
- MySQL 8.0+
- Node.js 18+
- Vite for asset compilation

### Environment Variables

```env
# Post Management
POST_MAX_FILE_SIZE=10485760  # 10MB
POST_ALLOWED_TYPES=image/jpeg,image/png,image/gif,video/mp4,video/quicktime
POST_STORAGE_DISK=public
```

### Database Migration

```bash
php artisan migrate
```

### Asset Compilation

```bash
npm install
npm run build
```

## 📊 Performance

### Optimization Features

- Lazy loading for media
- Image compression
- Database indexing
- Query optimization
- Caching support

### File Storage

- **Public Storage** - Web-accessible media files
- **Private Storage** - Secure file serving via Laravel routes
- **CDN Ready** - Easy integration with CDN services

## 🔄 Workflow

### Post Creation Flow

1. User selects post type
2. User selects Instagram account
3. User adds title and caption
4. User uploads media files
5. User schedules or publishes immediately
6. System validates and saves post
7. System processes media files
8. Post is ready for publishing

### Post Editing Flow

1. User opens post for editing
2. System loads existing data and media
3. User modifies content
4. User saves changes
5. System validates and updates post
6. System updates media if needed

### Post Duplication Flow

1. User clicks duplicate button
2. System copies post data
3. System copies all media files
4. System creates new post with "Copy of" prefix
5. User can modify and save new post

## 🐛 Troubleshooting

### Common Issues

#### Images Not Displaying

- Check file permissions
- Verify storage disk configuration
- Check media route accessibility
- Validate file paths

#### Upload Failures

- Check file size limits
- Verify MIME type validation
- Check disk space
- Validate file permissions

#### Scheduling Issues

- Verify timezone settings
- Check scheduled_at validation
- Ensure future date/time
- Validate Instagram account access

## 📈 Future Enhancements

### Planned Features

- **Bulk Operations** - Multi-post management
- **Templates** - Reusable post templates
- **Analytics** - Post performance tracking
- **Auto-posting** - Instagram API integration
- **Content Calendar** - Visual scheduling interface
- **Team Collaboration** - Multi-user post management

### Technical Improvements

- **Queue Jobs** - Background processing
- **Real-time Updates** - WebSocket integration
- **Advanced Media** - Video editing tools
- **AI Integration** - Content suggestions
- **API Expansion** - Third-party integrations

---

**Last Updated:** October 15, 2025  
**Version:** 1.0  
**Status:** ✅ Production Ready
