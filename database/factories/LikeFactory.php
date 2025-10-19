<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Like>
 */
class LikeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $likeableType = fake()->randomElement([Post::class, Comment::class]);
        
        return [
            'user_id' => User::factory(),
            'likeable_type' => $likeableType,
            'likeable_id' => $likeableType === Post::class ? Post::factory() : Comment::factory(),
        ];
    }

    /**
     * Indicate that the like is for a post.
     */
    public function forPost(): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_type' => Post::class,
            'likeable_id' => Post::factory(),
        ]);
    }

    /**
     * Indicate that the like is for a comment.
     */
    public function forComment(): static
    {
        return $this->state(fn (array $attributes) => [
            'likeable_type' => Comment::class,
            'likeable_id' => Comment::factory(),
        ]);
    }
}