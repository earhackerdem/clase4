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
        $users = User::all();
        $categories = Category::all();
        $tags = Tag::all();
        
        $postsData = [];
        
        // Crear 10,000 posts para generar problemas de rendimiento
        for ($i = 1; $i <= 10000; $i++) {
            $title = fake()->sentence(rand(3, 8));
            $content = fake()->paragraphs(rand(5, 15), true);
            
            $postsData[] = [
                'title' => $title,
                'slug' => Str::slug($title) . '-' . $i,
                'excerpt' => Str::limit($content, 200),
                'content' => $content,
                'featured_image' => fake()->imageUrl(800, 600, 'technology'),
                'status' => fake()->randomElement(['draft', 'published', 'archived']),
                'published_at' => fake()->dateTimeBetween('-2 years', 'now'),
                'user_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
                'views_count' => fake()->numberBetween(0, 10000),
                'likes_count' => fake()->numberBetween(0, 500),
                'comments_count' => fake()->numberBetween(0, 100),
                'meta_data' => json_encode([
                    'seo_title' => $title,
                    'seo_description' => Str::limit($content, 160),
                    'keywords' => fake()->words(5),
                ]),
                'created_at' => fake()->dateTimeBetween('-2 years', 'now'),
                'updated_at' => now(),
            ];
        }
        
        // ❌ PROBLEMA: Insertar todos los posts de una vez sin chunking
        Post::insert($postsData);
        
        // ❌ PROBLEMA: Asignar tags a posts de forma ineficiente
        $posts = Post::all();
        foreach ($posts as $post) {
            $randomTags = $tags->random(rand(1, 5));
            $post->tags()->attach($randomTags->pluck('id'));
        }
        
        $this->command->info('✅ Creados 10,000 posts con tags para generar problemas de rendimiento');
    }
}