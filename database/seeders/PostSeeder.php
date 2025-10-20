<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ✅ SOLUCIÓN: Usar factory para crear posts de forma eficiente
        Post::factory()->count(5000)->create();

        // Asignar tags a posts de forma eficiente usando chunks
        $tagIds = Tag::pluck('id')->toArray();

        if (empty($tagIds)) {
            $this->command->warn('⚠️ No hay tags disponibles para asignar a los posts');
            return;
        }

        Post::chunk(500, function ($posts) use ($tagIds) {
            foreach ($posts as $post) {
                // Asignar 1 a 5 tags aleatorios a cada post
                $randomTagCount = rand(1, min(5, count($tagIds)));
                $randomTagIds = array_rand(array_flip($tagIds), $randomTagCount);
                $post->tags()->attach(is_array($randomTagIds) ? $randomTagIds : [$randomTagIds]);
            }
        });

        $this->command->info('✅ Creados 5,000 posts con tags para generar problemas de rendimiento');
    }
}