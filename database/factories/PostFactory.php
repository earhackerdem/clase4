<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
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
        $title = fake()->sentence(rand(3, 8));
        $content = fake()->paragraphs(rand(5, 15), true);
        
        return [
            'title' => $title,
            'slug' => Str::slug($title) . '-' . fake()->unique()->numberBetween(1, 99999),
            'excerpt' => Str::limit($content, 200),
            'content' => $content,
            'featured_image' => fake()->imageUrl(800, 600, 'technology'),
            'status' => fake()->randomElement(['draft', 'published', 'archived']),
            'published_at' => fake()->dateTimeBetween('-2 years', 'now'),
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'views_count' => fake()->numberBetween(0, 10000),
            'likes_count' => fake()->numberBetween(0, 500),
            'comments_count' => fake()->numberBetween(0, 100),
            'meta_data' => json_encode([
                'seo_title' => $title,
                'seo_description' => Str::limit($content, 160),
                'keywords' => fake()->words(5),
            ]),
        ];
    }

    /**
     * Indicate that the post is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-2 years', 'now'),
        ]);
    }
}