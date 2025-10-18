<?php

namespace Database\Seeders;

use App\Models\Like;
use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Seeder;

class LikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $posts = Post::where('status', 'published')->get();
        $comments = Comment::where('status', 'approved')->get();
        
        $likesData = [];
        
        // Crear 100,000 likes para generar problemas de rendimiento
        for ($i = 1; $i <= 100000; $i++) {
            $user = $users->random();
            $likeableType = fake()->randomElement([Post::class, Comment::class]);
            
            if ($likeableType === Post::class) {
                $likeableId = $posts->random()->id;
            } else {
                $likeableId = $comments->random()->id;
            }
            
            $likesData[] = [
                'user_id' => $user->id,
                'likeable_type' => $likeableType,
                'likeable_id' => $likeableId,
                'created_at' => fake()->dateTimeBetween('-2 years', 'now'),
                'updated_at' => now(),
            ];
        }
        
        // ❌ PROBLEMA: Insertar todos los likes de una vez sin chunking
        Like::insert($likesData);
        
        $this->command->info('✅ Creados 100,000 likes para generar problemas de rendimiento');
    }
}