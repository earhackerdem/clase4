<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuarios masivos para generar problemas de rendimiento
        $users = [];
        
        for ($i = 1; $i <= 1000; $i++) {
            $users[] = [
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // ❌ PROBLEMA: Insertar todos los usuarios de una vez sin chunking
        User::insert($users);
        
        $this->command->info('✅ Creados 1,000 usuarios para generar problemas de rendimiento');
    }
}