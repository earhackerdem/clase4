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

        // Usar IDs existentes en lugar de crear nuevas relaciones
        $userId = User::inRandomOrder()->value('id') ?? User::factory()->create()->id;

        if ($likeableType === Post::class) {
            $likeableId = Post::inRandomOrder()->value('id') ?? Post::factory()->create()->id;
        } else {
            $likeableId = Comment::inRandomOrder()->value('id') ?? Comment::factory()->create()->id;
        }

        return [
            'user_id' => $userId,
            'likeable_type' => $likeableType,
            'likeable_id' => $likeableId,
        ];
    }

    /**
     * Indicate that the like is for a post.
     */
    public function forPost(): static
    {
        return $this->state(function (array $attributes) {
            $postId = Post::inRandomOrder()->value('id') ?? Post::factory()->create()->id;

            return [
                'likeable_type' => Post::class,
                'likeable_id' => $postId,
            ];
        });
    }

    /**
     * Indicate that the like is for a comment.
     */
    public function forComment(): static
    {
        return $this->state(function (array $attributes) {
            $commentId = Comment::inRandomOrder()->value('id') ?? Comment::factory()->create()->id;

            return [
                'likeable_type' => Comment::class,
                'likeable_id' => $commentId,
            ];
        });
    }
}