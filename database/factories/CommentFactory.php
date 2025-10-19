<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Usar IDs existentes en lugar de crear nuevas relaciones
        $userId = User::inRandomOrder()->value('id') ?? User::factory()->create()->id;
        $postId = Post::inRandomOrder()->value('id') ?? Post::factory()->create()->id;

        return [
            'content' => fake()->paragraphs(rand(1, 3), true),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'user_id' => $userId,
            'post_id' => $postId,
            'parent_id' => null,
            'likes_count' => fake()->numberBetween(0, 50),
        ];
    }

    /**
     * Indicate that the comment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
        ]);
    }

    /**
     * Indicate that the comment is a reply.
     */
    public function reply(): static
    {
        return $this->state(function (array $attributes) {
            $parentId = \App\Models\Comment::whereNull('parent_id')
                ->inRandomOrder()
                ->value('id');

            return [
                'parent_id' => $parentId,
            ];
        });
    }
}