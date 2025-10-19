<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ✅ SOLUCIÓN: Usar factory para crear posts de forma eficiente
        Post::factory()->count(10000)->create();
        
        // Asignar tags a posts de forma eficiente
        $posts = Post::all();
        $tags = Tag::all();
        
        foreach ($posts as $post) {
            $randomTags = $tags->random(rand(1, 5));
            $post->tags()->attach($randomTags);
        }
        
        $this->command->info('✅ Creados 10,000 posts con tags para generar problemas de rendimiento');
    }
}