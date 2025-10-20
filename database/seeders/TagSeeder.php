<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tags = [
            'tutorial', 'beginner', 'advanced', 'expert', 'tips', 'tricks',
            'performance', 'optimization', 'security', 'best-practices',
            'framework', 'library', 'package', 'composer', 'npm',
            'database', 'query', 'migration', 'seeder', 'factory',
            'authentication', 'authorization', 'middleware', 'validation',
            'api', 'endpoint', 'request', 'response', 'json',
            'frontend', 'backend', 'fullstack', 'spa', 'pwa',
            'mobile', 'responsive', 'design', 'ui', 'ux',
            'testing', 'unit', 'integration', 'e2e', 'mock',
            'deployment', 'production', 'staging', 'development',
            'docker', 'container', 'kubernetes', 'orchestration',
            'cloud', 'aws', 'azure', 'gcp', 'serverless',
            'monitoring', 'logging', 'debugging', 'profiling',
            'caching', 'redis', 'memcached', 'session', 'cookie',
            'queue', 'job', 'worker', 'cron', 'schedule',
            'event', 'listener', 'observer', 'notification',
            'file', 'upload', 'storage', 'cdn', 'image',
            'search', 'filter', 'pagination', 'sorting',
            'export', 'import', 'csv', 'excel', 'pdf',
            'email', 'sms', 'push', 'webhook'
        ];
        
        $users = User::all();
        $tagsData = [];
        
        foreach ($tags as $tagName) {
            $tagsData[] = [
                'name' => $tagName,
                'slug' => Str::slug($tagName),
                'description' => fake()->sentence(8),
                'color' => fake()->hexColor(),
                'user_id' => $users->random()->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // ❌ PROBLEMA: Insertar todos los tags de una vez sin chunking
        Tag::insert($tagsData);
        
        $this->command->info('✅ Creados ' . count($tags) . ' tags para generar problemas de rendimiento');
    }
}