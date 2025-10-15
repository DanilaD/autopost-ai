<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostMedia>
 */
class PostMediaFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = PostMedia::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['image', 'video']);
        $filename = $this->faker->uuid().'.'.($type === 'image' ? 'jpg' : 'mp4');

        return [
            'post_id' => Post::factory(),
            'type' => $type,
            'filename' => $filename,
            'original_filename' => $this->faker->word().'.'.($type === 'image' ? 'jpg' : 'mp4'),
            'mime_type' => $type === 'image' ? 'image/jpeg' : 'video/mp4',
            'file_size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'storage_path' => 'posts/'.$this->faker->numberBetween(1, 100).'/'.$filename,
            'url' => $this->faker->optional(0.1)->url(),
            'order' => $this->faker->numberBetween(0, 10),
            'metadata' => $this->getMetadataForType($type),
        ];
    }

    /**
     * Indicate that the media is an image.
     */
    public function image(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'image',
            'filename' => $this->faker->uuid().'.jpg',
            'original_filename' => $this->faker->word().'.jpg',
            'mime_type' => 'image/jpeg',
            'metadata' => $this->getMetadataForType('image'),
        ]);
    }

    /**
     * Indicate that the media is a video.
     */
    public function video(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'video',
            'filename' => $this->faker->uuid().'.mp4',
            'original_filename' => $this->faker->word().'.mp4',
            'mime_type' => 'video/mp4',
            'metadata' => $this->getMetadataForType('video'),
        ]);
    }

    /**
     * Indicate that the media has a custom URL.
     */
    public function withUrl(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => $this->faker->url(),
        ]);
    }

    /**
     * Indicate that the media has no custom URL.
     */
    public function withoutUrl(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => null,
        ]);
    }

    /**
     * Indicate that the media has a specific order.
     */
    public function order(int $order): static
    {
        return $this->state(fn (array $attributes) => [
            'order' => $order,
        ]);
    }

    /**
     * Indicate that the media has a large file size.
     */
    public function large(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => $this->faker->numberBetween(10485760, 104857600), // 10MB to 100MB
        ]);
    }

    /**
     * Indicate that the media has a small file size.
     */
    public function small(): static
    {
        return $this->state(fn (array $attributes) => [
            'file_size' => $this->faker->numberBetween(1024, 102400), // 1KB to 100KB
        ]);
    }

    /**
     * Get metadata based on media type.
     */
    private function getMetadataForType(string $type): array
    {
        if ($type === 'image') {
            return [
                'dimensions' => [
                    'width' => $this->faker->numberBetween(800, 2048),
                    'height' => $this->faker->numberBetween(600, 1536),
                ],
                'mime_type' => 'image/jpeg',
                'exif' => [
                    'camera' => $this->faker->optional(0.7)->randomElement(['iPhone 12', 'Samsung Galaxy S21', 'Canon EOS R5']),
                    'iso' => $this->faker->optional(0.5)->numberBetween(100, 3200),
                    'aperture' => $this->faker->optional(0.5)->randomElement(['f/1.8', 'f/2.4', 'f/4.0']),
                ],
            ];
        }

        if ($type === 'video') {
            return [
                'duration' => $this->faker->numberBetween(15, 300), // 15 seconds to 5 minutes
                'dimensions' => [
                    'width' => $this->faker->randomElement([720, 1080, 1920]),
                    'height' => $this->faker->randomElement([480, 720, 1080]),
                ],
                'mime_type' => 'video/mp4',
                'bitrate' => $this->faker->numberBetween(1000, 5000),
                'fps' => $this->faker->randomElement([24, 30, 60]),
            ];
        }

        return [];
    }
}
