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
        // ✅ SOLUCIÓN: Usar factory para crear likes de forma eficiente
        Like::factory()->count(100000)->create();
        
        $this->command->info('✅ Creados 100,000 likes para generar problemas de rendimiento');
    }
}