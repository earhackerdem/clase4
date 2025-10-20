<?php

namespace Database\Seeders;

use App\Models\View;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class ViewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('👀 Creando 100,000 vistas...');

        $userIds = User::pluck('id')->toArray();
        $postIds = Post::pluck('id')->toArray();

        $views = [];
        $batchSize = 1000;

        for ($i = 0; $i < 100000; $i++) {
            $views[] = [
                'post_id' => $postIds[array_rand($postIds)],
                'user_id' => fake()->boolean(30) ? $userIds[array_rand($userIds)] : null,
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'referer' => fake()->boolean(60) ? fake()->url() : null,
                'viewed_at' => fake()->dateTimeBetween('-2 years', 'now'),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($views) >= $batchSize) {
                View::insert($views);
                $views = [];
            }
        }

        if (!empty($views)) {
            View::insert($views);
        }

        $this->command->info('✅ Creadas 100,000 vistas para generar problemas de rendimiento');
    }
}