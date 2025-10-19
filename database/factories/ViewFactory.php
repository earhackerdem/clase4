<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\View>
 */
class ViewFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Usar IDs existentes en lugar de crear nuevas relaciones
        $postId = Post::inRandomOrder()->value('id') ?? Post::factory()->create()->id;
        $userId = fake()->boolean(30)
            ? (User::inRandomOrder()->value('id') ?? User::factory()->create()->id)
            : null;

        return [
            'post_id' => $postId,
            'user_id' => $userId,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'referer' => fake()->boolean(60) ? fake()->url() : null,
            'viewed_at' => fake()->dateTimeBetween('-2 years', 'now'),
        ];
    }

    /**
     * Indicate that the view is from an authenticated user.
     */
    public function authenticated(): static
    {
        return $this->state(function (array $attributes) {
            $userId = User::inRandomOrder()->value('id') ?? User::factory()->create()->id;

            return [
                'user_id' => $userId,
            ];
        });
    }

    /**
     * Indicate that the view is from an anonymous user.
     */
    public function anonymous(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }
}