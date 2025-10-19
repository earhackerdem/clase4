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
        // ✅ SOLUCIÓN: Usar factory para crear vistas de forma eficiente
        View::factory()->count(200000)->create();
        
        $this->command->info('✅ Creadas 200,000 vistas para generar problemas de rendimiento');
    }
}