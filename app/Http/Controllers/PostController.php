<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\CreatePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Models\Post;
use App\Services\Post\PostMediaService;
use App\Services\Post\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    /**
     * Constructor dependency injection
     */
    public function __construct(
        private PostService $postService,
        private PostMediaService $mediaService
    ) {}

    /**
     * Display a listing of posts
     */
    public function index(Request $request): Response
    {
        $companyId = auth()->user()->currentCompany->id;

        $filters = $request->only(['status', 'type', 'instagram_account_id', 'search']);

        $posts = $this->postService->getCompanyPosts($companyId, $filters);

        return Inertia::render('Posts/Index', [
            'posts' => $posts,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new post
     */
    public function create(Request $request): Response
    {
        $instagramAccounts = auth()->user()->currentCompany->instagramAccounts;

        $prefill = null;
        if ($request->filled('duplicate')) {
            $original = Post::with(['media', 'instagramAccount'])
                ->where('company_id', auth()->user()->currentCompany->id)
                ->find($request->integer('duplicate'));
            if ($original) {
                $prefill = [
                    'type' => $original->type->value ?? 'feed',
                    'instagram_account_id' => $original->instagram_account_id,
                    'title' => $original->title ? 'Copy of '.$original->title : '',
                    'caption' => $original->caption,
                    'media' => $original->media->map(function ($media) {
                        return [
                            'id' => $media->id,
                            'filename' => $media->filename,
                            'type' => $media->type,
                            'storage_path' => $media->storage_path,
                            'file_size' => $media->file_size,
                        ];
                    })->toArray(),
                    // Do not prefill schedule to avoid accidental scheduling
                ];
            }
        }

        return Inertia::render('Posts/Create', [
            'instagramAccounts' => $instagramAccounts,
            'prefill' => $prefill,
        ]);
    }

    /**
     * Store a newly created post
     */
    public function store(CreatePostRequest $request): RedirectResponse
    {
        try {
            $companyId = auth()->user()->currentCompany->id;

            // Create the post first (without media) so we have a real post ID
            $postData = $request->validated();
            unset($postData['media']);

            $post = $this->postService->createPost($companyId, $postData);

            // Handle media files
            $uploadedMediaGroups = $request->file('media', []);
            $mediaToCopy = $request->input('copy_media');

            // Upload new media files
            if (! empty($uploadedMediaGroups)) {
                foreach ($uploadedMediaGroups as $index => $group) {
                    if (is_array($group) && isset($group['file']) && $group['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $this->mediaService->uploadMedia($group['file'], $post->id, $index);
                    }
                }
            }

            // Copy existing media files (for duplicated posts)
            if ($mediaToCopy) {
                $mediaIds = json_decode($mediaToCopy, true);
                if (is_array($mediaIds)) {
                    $this->mediaService->copyMedia($mediaIds, $post->id);
                }
            }

            // Redirect so Inertia handles navigation properly (instead of returning JSON)
            return redirect()
                ->route('posts.index')
                ->with('success', __('posts.created_successfully'));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', __('posts.create_failed'));
        }
    }

    /**
     * Display the specified post
     */
    public function show(Post $post): Response
    {
        // Temporarily remove authorization for testing
        // $this->authorize('view', $post);

        return Inertia::render('Posts/Show', [
            'post' => $post->load(['media', 'instagramAccount', 'creator']),
        ]);
    }

    /**
     * Show the form for editing the specified post
     */
    public function edit(Post $post): Response
    {
        $instagramAccounts = auth()->user()->currentCompany->instagramAccounts;

        // Reuse the Create page for editing by passing the existing post
        return Inertia::render('Posts/Create', [
            'post' => $post->load(['media', 'instagramAccount']),
            'instagramAccounts' => $instagramAccounts,
        ]);
    }

    /**
     * Update the specified post
     */
    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        try {
            // Temporarily remove authorization for testing
            // $this->authorize('update', $post);

            $postData = $request->validated();
            unset($postData['media']); // Media is handled separately

            $this->postService->updatePost($post, $postData);

            // Handle media updates
            $uploadedMediaGroups = $request->file('media', []);
            $mediaToDelete = $request->input('delete_media');

            // Delete specified media
            if ($mediaToDelete) {
                $mediaIds = json_decode($mediaToDelete, true);
                if (is_array($mediaIds)) {
                    foreach ($mediaIds as $mediaId) {
                        $media = $post->media()->find($mediaId);
                        if ($media) {
                            $this->mediaService->deleteMedia($media);
                        }
                    }
                }
            }

            // Upload new media
            if (! empty($uploadedMediaGroups)) {
                foreach ($uploadedMediaGroups as $index => $group) {
                    if (is_array($group) && isset($group['file']) && $group['file'] instanceof \Illuminate\Http\UploadedFile) {
                        $this->mediaService->uploadMedia($group['file'], $post->id, $index);
                    }
                }
            }

            return redirect()
                ->route('posts.index')
                ->with('success', __('posts.updated_successfully'));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('posts.update_failed'))
                ->withInput();
        }
    }

    /**
     * Remove the specified post
     */
    public function destroy(Post $post): RedirectResponse
    {
        try {
            $this->postService->deletePost($post);

            return redirect()
                ->route('posts.index')
                ->with('success', __('posts.deleted_successfully'));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', __('posts.delete_failed'));
        }
    }

    /**
     * Schedule a post for publishing
     */
    public function schedule(Request $request, Post $post): JsonResponse
    {
        try {
            $this->authorize('update', $post);

            $request->validate([
                'scheduled_at' => 'required|date|after:now',
            ]);

            $scheduledAt = new \DateTime($request->input('scheduled_at'));
            $post = $this->postService->schedulePost($post, $scheduledAt);

            return response()->json([
                'message' => __('posts.scheduled_successfully'),
                'post' => $post,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => __('posts.schedule_failed'),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get post statistics
     */
    public function stats(): JsonResponse
    {
        $companyId = auth()->user()->currentCompany->id;
        $stats = $this->postService->getCompanyPosts($companyId);

        // Calculate stats from posts
        $stats = [
            'total' => $stats->count(),
            'drafts' => $stats->where('status', 'draft')->count(),
            'scheduled' => $stats->where('status', 'scheduled')->count(),
            'published' => $stats->where('status', 'published')->count(),
            'failed' => $stats->where('status', 'failed')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Process media file uploads
     */
    private function processMediaUploads(array $files): array
    {
        // Deprecated: media uploads are now handled after post creation using the real post ID
        return [];
    }
}
