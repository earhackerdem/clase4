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
        // ✅ SOLUCIÓN: Usar factory para crear categorías de forma eficiente
        Category::factory()->count(50)->create();
        
        $this->command->info('✅ Creadas 50 categorías para generar problemas de rendimiento');
    }
}