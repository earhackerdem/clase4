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
        // ✅ SOLUCIÓN: Usar factory para crear comentarios de forma eficiente
        Comment::factory()->count(50000)->create();
        
        // Crear respuestas a comentarios (comentarios anidados)
        $mainComments = Comment::whereNull('parent_id')->take(10000)->get();
        
        foreach ($mainComments as $comment) {
            $replyCount = rand(0, 3);
            Comment::factory()->count($replyCount)->reply()->create([
                'post_id' => $comment->post_id,
                'created_at' => fake()->dateTimeBetween($comment->created_at, 'now'),
            ]);
        }
        
        $this->command->info('✅ Creados 50,000+ comentarios para generar problemas de rendimiento');
    }
}