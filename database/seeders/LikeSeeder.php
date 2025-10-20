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
        $this->command->info('👍 Creando 25,000 likes...');

        $userIds = User::pluck('id')->toArray();
        $postIds = Post::pluck('id')->toArray();
        $commentIds = Comment::pluck('id')->toArray();

        $likes = [];
        $batchSize = 1000;
        $createdLikes = 0;
        $maxLikes = 25000;
        $attempts = 0;
        $maxAttempts = $maxLikes * 3; // Límite de intentos para evitar bucle infinito

        // ✅ SOLUCIÓN: Usar un conjunto para evitar duplicados
        $existingLikes = [];

        while ($createdLikes < $maxLikes && $attempts < $maxAttempts) {
            $attempts++;
            
            $likeableType = fake()->randomElement([Post::class, Comment::class]);
            $likeableId = $likeableType === Post::class
                ? $postIds[array_rand($postIds)]
                : $commentIds[array_rand($commentIds)];
            
            $userId = $userIds[array_rand($userIds)];
            
            // ✅ SOLUCIÓN: Crear clave única para verificar duplicados
            $likeKey = "{$userId}-{$likeableType}-{$likeableId}";
            
            // ✅ SOLUCIÓN: Solo agregar si no existe
            if (!isset($existingLikes[$likeKey])) {
                $existingLikes[$likeKey] = true;
                
                $likes[] = [
                    'user_id' => $userId,
                    'likeable_type' => $likeableType,
                    'likeable_id' => $likeableId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                $createdLikes++;

                if (count($likes) >= $batchSize) {
                    Like::insert($likes);
                    $likes = [];
                }
            }
        }

        if (!empty($likes)) {
            Like::insert($likes);
        }

        $this->command->info("✅ Creados {$createdLikes} likes únicos para generar problemas de rendimiento");
    }
}