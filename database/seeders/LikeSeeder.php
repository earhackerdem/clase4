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
        $this->command->info('👍 Creando 100,000 likes...');

        $userIds = User::pluck('id')->toArray();
        $postIds = Post::pluck('id')->toArray();
        $commentIds = Comment::pluck('id')->toArray();

        $likes = [];
        $batchSize = 1000;

        for ($i = 0; $i < 100000; $i++) {
            $likeableType = fake()->randomElement([Post::class, Comment::class]);
            $likeableId = $likeableType === Post::class
                ? $postIds[array_rand($postIds)]
                : $commentIds[array_rand($commentIds)];

            $likes[] = [
                'user_id' => $userIds[array_rand($userIds)],
                'likeable_type' => $likeableType,
                'likeable_id' => $likeableId,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($likes) >= $batchSize) {
                Like::insert($likes);
                $likes = [];
            }
        }

        if (!empty($likes)) {
            Like::insert($likes);
        }

        $this->command->info('✅ Creados 100,000 likes para generar problemas de rendimiento');
    }
}