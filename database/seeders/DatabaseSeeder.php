<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🚀 Iniciando seeders para generar problemas de rendimiento...');
        
        // Crear usuario de prueba
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        
        // Ejecutar seeders en orden para generar problemas de rendimiento
        $this->call([
            UserSeeder::class,        // 1,000 usuarios
            CategorySeeder::class,    // 50 categorías
            TagSeeder::class,         // 200 tags
            PostSeeder::class,        // 10,000 posts
            CommentSeeder::class,     // 50,000+ comentarios
            LikeSeeder::class,        // 100,000 likes
            ViewSeeder::class,        // 200,000 vistas
        ]);
        
        $this->command->info('✅ Seeders completados. Base de datos lista para demostrar problemas de rendimiento.');
        $this->command->info('📊 Total de registros creados:');
        $this->command->info('   - Usuarios: 1,000');
        $this->command->info('   - Categorías: 50');
        $this->command->info('   - Tags: 200');
        $this->command->info('   - Posts: 10,000');
        $this->command->info('   - Comentarios: 50,000+');
        $this->command->info('   - Likes: 100,000');
        $this->command->info('   - Vistas: 200,000');
    }
}
