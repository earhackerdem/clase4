<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Laravel', 'PHP', 'JavaScript', 'Vue.js', 'React', 'Angular',
            'Node.js', 'Python', 'Java', 'C#', 'Go', 'Rust',
            'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Elasticsearch',
            'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP',
            'DevOps', 'CI/CD', 'Testing', 'TDD', 'BDD',
            'API', 'REST', 'GraphQL', 'Microservices', 'Serverless',
            'Frontend', 'Backend', 'Full Stack', 'Mobile', 'Desktop',
            'Web Design', 'UI/UX', 'CSS', 'SASS', 'Tailwind',
            'Git', 'GitHub', 'GitLab', 'Bitbucket', 'Jira',
            'Agile', 'Scrum', 'Kanban', 'Project Management'
        ];
        
        $users = User::all();
        $categoriesData = [];
        
        foreach ($categories as $categoryName) {
            $categoriesData[] = [
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
                'description' => fake()->sentence(10),
                'color' => fake()->hexColor(),
                'user_id' => $users->random()->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // ❌ PROBLEMA: Insertar todas las categorías de una vez sin chunking
        Category::insert($categoriesData);
        
        $this->command->info('✅ Creadas 50 categorías para generar problemas de rendimiento');
    }
}