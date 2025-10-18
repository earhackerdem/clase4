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
        $users = User::all();
        $posts = Post::where('status', 'published')->get();
        
        $commentsData = [];
        
        // Crear 50,000 comentarios para generar problemas de rendimiento
        for ($i = 1; $i <= 50000; $i++) {
            $post = $posts->random();
            $user = $users->random();
            
            $commentsData[] = [
                'content' => fake()->paragraphs(rand(1, 3), true),
                'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
                'user_id' => $user->id,
                'post_id' => $post->id,
                'parent_id' => null, // Comentarios principales
                'likes_count' => fake()->numberBetween(0, 50),
                'created_at' => fake()->dateTimeBetween($post->created_at, 'now'),
                'updated_at' => now(),
            ];
        }
        
        // ❌ PROBLEMA: Insertar todos los comentarios de una vez sin chunking
        Comment::insert($commentsData);
        
        // Crear respuestas a comentarios (comentarios anidados)
        $mainComments = Comment::whereNull('parent_id')->get();
        $repliesData = [];
        
        foreach ($mainComments->take(10000) as $comment) {
            $replyCount = rand(0, 3);
            for ($j = 0; $j < $replyCount; $j++) {
                $repliesData[] = [
                    'content' => fake()->paragraphs(rand(1, 2), true),
                    'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
                    'user_id' => $users->random()->id,
                    'post_id' => $comment->post_id,
                    'parent_id' => $comment->id,
                    'likes_count' => fake()->numberBetween(0, 20),
                    'created_at' => fake()->dateTimeBetween($comment->created_at, 'now'),
                    'updated_at' => now(),
                ];
            }
        }
        
        // ❌ PROBLEMA: Insertar todas las respuestas de una vez sin chunking
        if (!empty($repliesData)) {
            Comment::insert($repliesData);
        }
        
        $this->command->info('✅ Creados 50,000+ comentarios para generar problemas de rendimiento');
    }
}