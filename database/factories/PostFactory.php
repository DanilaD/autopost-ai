<?php

namespace Database\Factories;

use App\Enums\PostStatus;
use App\Enums\PostType;
use App\Models\Company;
use App\Models\InstagramAccount;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;

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
            'type' => $this->faker->randomElement(PostType::cases()),
            'title' => $this->faker->sentence(3),
            'caption' => $this->faker->paragraph(2).' #'.$this->faker->word().' @'.$this->faker->userName(),
            'hashtags' => [$this->faker->word(), $this->faker->word()],
            'mentions' => [$this->faker->userName()],
            'scheduled_at' => $this->faker->optional(0.3)->dateTimeBetween('now', '+1 month'),
            'published_at' => $this->faker->optional(0.2)->dateTimeBetween('-1 month', 'now'),
            'status' => $this->faker->randomElement(PostStatus::cases()),
            'failure_reason' => $this->faker->optional(0.1)->sentence(),
            'publish_attempts' => $this->faker->numberBetween(0, 3),
            'metadata' => [
                'instagram_post_id' => $this->faker->optional(0.2)->numerify('##########'),
                'likes_count' => $this->faker->optional(0.3)->numberBetween(0, 1000),
                'comments_count' => $this->faker->optional(0.3)->numberBetween(0, 100),
            ],
        ];
    }

    /**
     * Indicate that the post is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::DRAFT,
            'scheduled_at' => null,
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the post is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::SCHEDULED,
            'scheduled_at' => $this->faker->dateTimeBetween('now', '+1 month'),
            'published_at' => null,
        ]);
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::PUBLISHED,
            'scheduled_at' => null,
            'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    /**
     * Indicate that the post failed to publish.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PostStatus::FAILED,
            'failure_reason' => $this->faker->sentence(),
            'publish_attempts' => $this->faker->numberBetween(1, 3),
        ]);
    }

    /**
     * Indicate that the post is a feed post.
     */
    public function feed(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PostType::FEED,
        ]);
    }

    /**
     * Indicate that the post is a reel.
     */
    public function reel(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PostType::REEL,
        ]);
    }

    /**
     * Indicate that the post is a story.
     */
    public function story(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PostType::STORY,
        ]);
    }

    /**
     * Indicate that the post is a carousel.
     */
    public function carousel(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => PostType::CAROUSEL,
        ]);
    }
}
