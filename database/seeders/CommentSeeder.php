<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📝 Creando 25,000 comentarios principales...');

        // ✅ SOLUCIÓN: Usar factory para crear comentarios de forma eficiente
        Comment::factory()->count(25000)->create();

        $this->command->info('💬 Creando comentarios anidados (respuestas)...');

        // Crear respuestas a comentarios (comentarios anidados)
        $mainCommentIds = Comment::whereNull('parent_id')
            ->take(5000)
            ->pluck('id', 'post_id')
            ->toArray();

        $userIds = User::pluck('id')->toArray();
        $replies = [];
        $batchSize = 1000;

        foreach ($mainCommentIds as $postId => $commentId) {
            $replyCount = rand(0, 3);

            for ($i = 0; $i < $replyCount; $i++) {
                $replies[] = [
                    'content' => fake()->paragraphs(rand(1, 2), true),
                    'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
                    'user_id' => $userIds[array_rand($userIds)],
                    'post_id' => $postId,
                    'parent_id' => $commentId,
                    'likes_count' => fake()->numberBetween(0, 50),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (count($replies) >= $batchSize) {
                    Comment::insert($replies);
                    $replies = [];
                }
            }
        }

        // Insertar los comentarios restantes
        if (!empty($replies)) {
            Comment::insert($replies);
        }

        $this->command->info('✅ Creados 25,000+ comentarios para generar problemas de rendimiento');
    }
}