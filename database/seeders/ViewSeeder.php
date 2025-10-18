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
        $users = User::all();
        $posts = Post::where('status', 'published')->get();
        
        $viewsData = [];
        
        // Crear 200,000 vistas para generar problemas de rendimiento
        for ($i = 1; $i <= 200000; $i++) {
            $post = $posts->random();
            $user = fake()->boolean(30) ? $users->random() : null; // 30% usuarios autenticados
            
            $viewsData[] = [
                'post_id' => $post->id,
                'user_id' => $user?->id,
                'ip_address' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'referer' => fake()->boolean(60) ? fake()->url() : null,
                'viewed_at' => fake()->dateTimeBetween($post->created_at, 'now'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // ❌ PROBLEMA: Insertar todas las vistas de una vez sin chunking
        View::insert($viewsData);
        
        $this->command->info('✅ Creadas 200,000 vistas para generar problemas de rendimiento');
    }
}