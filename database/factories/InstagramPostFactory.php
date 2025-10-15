<?php

namespace Database\Factories;

use App\Enums\InstagramPostStatus;
use App\Models\InstagramAccount;
use App\Models\InstagramPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Instagram Post Factory
 *
 * Creates test data for Instagram posts.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InstagramPost>
 */
class InstagramPostFactory extends Factory
{
    protected $model = InstagramPost::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'instagram_account_id' => InstagramAccount::factory(),
            'user_id' => User::factory(),
            'caption' => fake()->paragraph(3),
            'media_type' => 'image',
            'media_urls' => [
                'https://picsum.photos/800/800?random='.fake()->numberBetween(1, 1000),
            ],
            'status' => InstagramPostStatus::DRAFT,
            'retry_count' => 0,
            'metadata' => [
                'created_via' => 'web',
            ],
        ];
    }

    /**
     * Create a draft post.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InstagramPostStatus::DRAFT,
            'scheduled_at' => null,
            'published_at' => null,
        ]);
    }

    /**
     * Create a scheduled post.
     */
    public function scheduled(?\DateTime $scheduledAt = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InstagramPostStatus::SCHEDULED,
            'scheduled_at' => $scheduledAt ?? now()->addHours(2),
            'published_at' => null,
        ]);
    }

    /**
     * Create a published post.
     */
    public function published(): static
    {
        $instagramPostId = 'ig_'.fake()->numerify('##############');

        return $this->state(fn (array $attributes) => [
            'status' => InstagramPostStatus::PUBLISHED,
            'instagram_post_id' => $instagramPostId,
            'instagram_permalink' => "https://www.instagram.com/p/{$instagramPostId}",
            'published_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'scheduled_at' => null,
        ]);
    }

    /**
     * Create a failed post.
     */
    public function failed(?string $errorMessage = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InstagramPostStatus::FAILED,
            'error_message' => $errorMessage ?? 'Failed to publish: '.fake()->sentence(),
            'retry_count' => fake()->numberBetween(1, 3),
        ]);
    }

    /**
     * Create a cancelled post.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InstagramPostStatus::CANCELLED,
            'scheduled_at' => now()->addHours(2),
        ]);
    }

    /**
     * Create a post with video media.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'media_type' => 'video',
            'media_urls' => [
                'https://sample-videos.com/video123/mp4/720/big_buck_bunny_720p_1mb.mp4',
            ],
        ]);
    }

    /**
     * Create a post with carousel (multiple images).
     */
    public function carousel(int $imageCount = 3): static
    {
        $images = [];
        for ($i = 0; $i < $imageCount; $i++) {
            $images[] = 'https://picsum.photos/800/800?random='.fake()->unique()->numberBetween(1, 10000);
        }

        return $this->state(fn (array $attributes) => [
            'media_type' => 'carousel',
            'media_urls' => $images,
        ]);
    }

    /**
     * Create a post for a specific account.
     */
    public function forAccount(InstagramAccount $account): static
    {
        return $this->state(fn (array $attributes) => [
            'instagram_account_id' => $account->id,
        ]);
    }

    /**
     * Create a post by a specific user.
     */
    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    /**
     * Create a post due for publishing (scheduled in the past).
     */
    public function dueForPublishing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => InstagramPostStatus::SCHEDULED,
            'scheduled_at' => now()->subMinutes(5),
        ]);
    }
}
