<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->text('content'); // ❌ PROBLEMA: Sin índice para búsquedas
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // ❌ PROBLEMA: Sin índice
            $table->foreignId('user_id'); // ❌ PROBLEMA: Sin índice foreign key
            $table->foreignId('post_id'); // ❌ PROBLEMA: Sin índice foreign key
            $table->foreignId('parent_id')->nullable(); // Para respuestas anidadas
            $table->integer('likes_count')->default(0); // ❌ PROBLEMA: Sin índice para ordenamiento
            $table->timestamps();
            
            // ❌ PROBLEMA: Sin índices para optimizar consultas
            // No hay índices en user_id, post_id, parent_id, status
            // No hay índices compuestos para consultas complejas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};